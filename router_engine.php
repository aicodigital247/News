<?php
/**
 * NeuralPress - PHP Router Engine
 * Directs SEO-friendly URLs directly to internal scripts and templates without .htaccess dependency.
 */

// Include URL Manager & configuration
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/url_manager.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '/';

// Decode URL to handle encoded characters cleanly
$path = rawurldecode($path);

// If the file exists physically on disk (e.g., CSS, JS, images, or specific PHP files), servable as is
$physicalFile = __DIR__ . $path;
if (file_exists($physicalFile) && is_file($physicalFile)) {
    // If running in CLI server mode, return false to let the built-in server serve the static file
    if (php_sapi_name() === 'cli-server') {
        return false;
    }
    // Otherwise include or render the requested file directly
    if (pathinfo($physicalFile, PATHINFO_EXTENSION) === 'php') {
        require $physicalFile;
        exit;
    }
}

// Ensure slash padding for matching consistency
$path = '/' . trim($path, '/');

// Router mappings

// Support traditional query parameter Routing on / or /index.php or /.php files
if ($path === '/' || $path === '/index.php' || preg_match('~^/(article|post|news|category|search)\.php$~i', $path)) {
    if (isset($_GET['slug']) && !empty($_GET['slug'])) {
        require_once NP_DIR . '/templates/article.php';
        exit;
    }
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        require_once NP_DIR . '/templates/category.php';
        exit;
    }
    if (isset($_GET['q'])) {
        require_once NP_DIR . '/templates/search.php';
        exit;
    }
}

if ($path === '/' || $path === '/index.php') {
    require_once NP_DIR . '/templates/homepage.php';
    exit;
}

// SEO-friendly News Article Routing (supports both /news/ and /post/ layouts)
if (preg_match('~^/(news|post)/(.+)$~i', $path, $matches)) {
    $_GET['slug'] = $matches[2];
    require_once NP_DIR . '/templates/article.php';
    exit;
}

// SEO-friendly Category Routing
if (preg_match('~^/category/(.+)$~i', $path, $matches)) {
    $_GET['category'] = $matches[1];
    require_once NP_DIR . '/templates/category.php';
    exit;
}

// Search Route
if ($path === '/search') {
    require_once NP_DIR . '/templates/search.php';
    exit;
}

// Unified API Route Mapping (removes .php from URLs)
if (preg_match('~^/api/([a-zA-Z0-9_\-]+)$~i', $path, $matches)) {
    $apiFile = NP_DIR . '/api/' . $matches[1] . '.php';
    if (file_exists($apiFile)) {
        require_once $apiFile;
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'API endpoint not resolved in active neural routing table.'
        ]);
        exit;
    }
}

// Unified Admin Route Mapping (removes .php from URLs)
if (preg_match('~^/admin/([a-zA-Z0-9_\-]+)$~i', $path, $matches)) {
    $adminFile = NP_DIR . '/admin/' . $matches[1] . '.php';
    if (file_exists($adminFile)) {
        require_once $adminFile;
        exit;
    } else {
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>NeuralPress - Administrative panel node does not exist.</p>";
        exit;
    }
}

// Catch-All 404 Route
http_response_code(404);
echo "<h1>404 Not Found</h1><p>NeuralPress - Requested resource was not resolved on active news network nodes.</p>";
exit;
