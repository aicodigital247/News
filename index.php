<?php
/**
 * NeuralPress - Front Controller & Router Entrypoint
 */

require_once __DIR__ . '/config.php';

$request = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request, PHP_URL_PATH);

if ($path === '/' || $path === '/index.php') {
    require_once NP_DIR . '/templates/homepage.php';
} elseif (preg_match('~^/news/(.+)$~', $path, $matches)) {
    $_GET['slug'] = $matches[1];
    require_once NP_DIR . '/templates/article.php';
} elseif (preg_match('~^/category/(.+)$~', $path, $matches)) {
    $_GET['category'] = $matches[1];
    require_once NP_DIR . '/templates/category.php';
} elseif ($path === '/search') {
    require_once NP_DIR . '/templates/search.php';
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>NeuralPress - Requested resource was not resolved on active news network nodes.</p>";
}
