<?php
/**
 * NeuralPress - Single Article Detail Endpoint
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/rate_limit.php';

use NeuralPress\Core\Database;
use NeuralPress\API\Response;

$slug = $_GET['slug'] ?? '';
if (empty($slug)) {
    Response::error("Slug identifier required.");
}

$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE slug = ? LIMIT 1", "s", [$slug]);

if ($res && $post = $res->fetch_assoc()) {
    $db->query("UPDATE posts SET views = views + 1 WHERE id = ?", "i", [$post['id']]);
    Response::json($post);
} else {
    Response::error("Article not found.", 404);
}
