<?php
/**
 * NeuralPress - Clean & Advanced SEO Engine
 * Outputs standard meta tags, OpenGraph headers, Twitter Cards, and canonical mappings dynamically.
 */

$canonicalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
$seoTitle = isset($pageTitle) ? htmlspecialchars($pageTitle) : 'NeuralPress - Factual Global Newsroom CMS';
$seoDesc = isset($pageDescription) ? htmlspecialchars($pageDescription) : 'Unbiased global investigative press powered by factual heuristic validations, real-time Google trending intelligence, and strict editorial standards.';
$seoKeywords = isset($pageKeywords) ? htmlspecialchars($pageKeywords) : 'news, journalism, truth, factual intelligence, bbc style CMS, world politics, tech trends';
$seoImage = isset($pageImage) ? htmlspecialchars($pageImage) : '/assets/img/default_meta.jpg';

// Build OG & Twitter Meta Tags
?>
<!-- SEO Meta Tags -->
<meta name="description" content="<?php echo $seoDesc; ?>">
<meta name="keywords" content="<?php echo $seoKeywords; ?>">
<link rel="canonical" href="<?php echo $canonicalUrl; ?>">

<!-- OpenGraph (Facebook) Meta Tags -->
<meta property="og:title" content="<?php echo $seoTitle; ?>">
<meta property="og:description" content="<?php echo $seoDesc; ?>">
<meta property="og:image" content="<?php echo $seoImage; ?>">
<meta property="og:url" content="<?php echo $canonicalUrl; ?>">
<meta property="og:type" content="<?php echo isset($isArticlePage) && $isArticlePage ? 'article' : 'website'; ?>">
<meta property="og:site_name" content="NeuralPress">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?php echo $seoTitle; ?>">
<meta name="twitter:description" content="<?php echo $seoDesc; ?>">
<meta name="twitter:image" content="<?php echo $seoImage; ?>">
