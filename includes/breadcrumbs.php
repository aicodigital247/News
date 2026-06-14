<?php
/**
 * NeuralPress - Responsive Breadcrumbs Helper
 * Renders a pixel-perfect, accessible breadcrumb trail based on current page state.
 */

$breadcrumbs = [];
$breadcrumbs[] = ['title' => 'Home', 'url' => '/'];

if (isset($isArticlePage) && $isArticlePage && isset($post)) {
    $categoryUrl = '/category/' . urlencode($post['category']);
    $breadcrumbs[] = ['title' => htmlspecialchars($post['category']), 'url' => $categoryUrl];
    $breadcrumbs[] = ['title' => htmlspecialchars($post['title']), 'url' => null];
} elseif (isset($isCategoryPage) && $isCategoryPage && isset($categoryName)) {
    $breadcrumbs[] = ['title' => htmlspecialchars($categoryName), 'url' => null];
} elseif (isset($isSearchPage) && $isSearchPage) {
    $breadcrumbs[] = ['title' => 'Search Results', 'url' => null];
} elseif (isset($isAuthorPage) && $isAuthorPage && isset($author)) {
    $breadcrumbs[] = ['title' => 'Authors', 'url' => '#'];
    $breadcrumbs[] = ['title' => htmlspecialchars($author['name']), 'url' => null];
}
?>

<nav aria-label="Breadcrumb" class="mb-4 text-[10px] font-mono uppercase tracking-wider text-slate-400">
    <ol class="flex flex-wrap items-center gap-1.5 list-none p-0 m-0">
        <?php foreach ($breadcrumbs as $idx => $crumb): ?>
            <?php if ($idx > 0): ?>
                <li class="select-none text-slate-300">/</li>
            <?php endif; ?>
            
            <li>
                <?php if ($crumb['url'] && $idx < count($breadcrumbs) - 1): ?>
                    <a href="<?php echo $crumb['url']; ?>" class="hover:text-[#bb1919] transition-colors duration-150">
                        <?php echo $crumb['title']; ?>
                    </a>
                <?php else: ?>
                    <span class="text-slate-600 font-semibold" aria-current="page">
                        <?php echo $crumb['title']; ?>
                    </span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
