<?php
/**
 * NeuralPress - Create Article Command Endpoint
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/../core/slug_generator.php';
require_once __DIR__ . '/../core/seo_engine.php';

use NeuralPress\Core\Database;
use NeuralPress\Core\SlugGenerator;
use NeuralPress\Core\SEOEngine;
use NeuralPress\API\Response;

$input = json_decode(file_get_contents('php://input'), true) ?? [];
if (empty($input['title']) || empty($input['content']) || empty($input['category'])) {
    Response::error("Missing essential post fields (title, content, category).");
}

$db = Database::getInstance();
$title = trim($input['title']);
$summary = trim($input['summary'] ?? '');
$content = trim($input['content']);
$category = trim($input['category']);
$authorId = intval($input['author_id'] ?? 1);

$slug = SlugGenerator::create($title, $db->getConnection());
$seo = SEOEngine::compileMetadata($title, $content, $category);

// Parse the first image src if present from content as thumbnail_url
$thumbnailUrl = null;
if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
    $thumbnailUrl = $matches[1];
}

$sql = "INSERT INTO posts (author_id, title, slug, summary, content, category, seo_title, seo_description, seo_keywords, thumbnail_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')";
$success = $db->query($sql, "issssssssss", [
    $authorId, $title, $slug, $summary ?: mb_substr(strip_tags($content), 0, 150), $content, $category,
    $seo['seo_title'], $seo['seo_description'], $seo['seo_keywords'], $thumbnailUrl
]);

if ($success) {
    Response::success([
        'message' => 'Article draft successfully registered.',
        'slug' => $slug
    ]);
} else {
    Response::error("Failed to commit post record to database connection.");
}
