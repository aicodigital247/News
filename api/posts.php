<?php
/**
 * NeuralPress - List Articles Endpoint
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/rate_limit.php';

use NeuralPress\Core\Database;
use NeuralPress\API\Response;

$db = Database::getInstance();
$category = $_GET['category'] ?? null;
$language = $_GET['lang'] ?? 'en';
$status = $_GET['status'] ?? 'published';

// Standard binding checks
$sql = "SELECT * FROM posts WHERE status = ? AND language = ?";
$types = "ss";
$params = [$status, $language];

if ($category && $category !== 'all') {
    $sql .= " AND category = ?";
    $types .= "s";
    $params[] = $category;
}

$sql .= " ORDER BY id DESC LIMIT 20";
$res = $db->query($sql, $types, $params);
$posts = [];

if ($res) {
    while ($post = $res->fetch_assoc()) {
        $posts[] = $post;
    }
}

Response::json($posts);
