<?php
/**
 * NeuralPress - Structured Schema Engine
 * Embeds JSON-LD schemas (Organization, NewsArticle, Breadcrumb) to boost search trust.
 */

$currentDomain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
$schemaData = [];

// 1. Base Organization Schema
$schemaData[] = [
    '@context' => 'https://schema.org',
    '@type' => 'NewsMediaOrganization',
    '@id' => $currentDomain . '/#organization',
    'name' => 'NeuralPress',
    'url' => $currentDomain,
    'logo' => [
        '@type' => 'ImageObject',
        'url' => $currentDomain . '/assets/img/logo.jpg'
    ],
    'sameAs' => [
        'https://facebook.com/neuralpress',
        'https://twitter.com/neuralpress',
        'https://linkedin.com/company/neuralpress'
    ],
    'publishingPrinciples' => $currentDomain . '/principles'
];

// 2. BreadcrumbList Schema
if (isset($breadcrumbs) && !empty($breadcrumbs)) {
    $itemListElement = [];
    foreach ($breadcrumbs as $idx => $crumb) {
        if ($crumb['url']) {
            $crumbUrl = $crumb['url'];
            if (strpos($crumbUrl, 'http') !== 0) {
                $crumbUrl = $currentDomain . $crumbUrl;
            }
        } else {
            $crumbUrl = $currentDomain . ($_SERVER['REQUEST_URI'] ?? '/');
        }
        
        $itemListElement[] = [
            '@type' => 'ListItem',
            'position' => $idx + 1,
            'name' => $crumb['title'],
            'item' => $crumbUrl
        ];
    }
    
    $schemaData[] = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $itemListElement
    ];
}

// 3. NewsArticle Schema
if (isset($isArticlePage) && $isArticlePage && isset($post)) {
    $articleUrl = $currentDomain . ($_SERVER['REQUEST_URI'] ?? '/');
    $authorName = isset($author['name']) ? $author['name'] : 'NeuralPress Reporter';
    $authorImg = isset($author['image']) ? $author['image'] : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80';
    
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'NewsArticle',
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => $articleUrl
        ],
        'headline' => $post['title'],
        'description' => $post['summary'],
        'image' => [
            $post['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=800&q=80'
        ],
        'datePublished' => date('c', strtotime($post['created_at'])),
        'dateModified' => date('c', strtotime($post['updated_at'])),
        'author' => [
            '@type' => 'Person',
            'name' => $authorName,
            'jobTitle' => isset($author['bio']) ? substr($author['bio'], 0, 100) : 'Journalist',
            'image' => $authorImg,
            'sameAs' => array_filter([
                isset($author['facebook']) && $author['facebook'] ? 'https://facebook.com/' . $author['facebook'] : null,
                isset($author['twitter']) ? 'https://twitter.com/' . $author['twitter'] : null,
                isset($author['linkedin']) ? 'https://linkedin.com/in/' . $author['linkedin'] : null
            ])
        ],
        'publisher' => [
            '@type' => 'NewsMediaOrganization',
            'name' => 'NeuralPress',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $currentDomain . '/assets/img/logo.jpg'
            ]
        ],
        'pageEnd' => 1200
    ];
    
    $schemaData[] = $articleSchema;
}

// Output schema blocks
foreach ($schemaData as $schema):
?>
<script type="application/ld+json">
<?php echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
</script>
<?php 
endforeach; 
?>
