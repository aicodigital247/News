<?php
/**
 * NeuralPress - Trending Articles Endpoint
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/rate_limit.php';

use NeuralPress\Core\Database;
use NeuralPress\API\Response;

$db = Database::getInstance();
$res = $db->query("SELECT id, title, slug, category, views, trust_score FROM posts WHERE status = 'published' ORDER BY views DESC, trust_score DESC LIMIT 5");
$posts = [];

if ($res) {
    while ($post = $res->fetch_assoc()) {
        $posts[] = $post;
    }
}

Response::json($posts);
