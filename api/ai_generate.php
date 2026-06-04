<?php
/**
 * NeuralPress - AI Content Generation API Wrapper
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/../core/ai_content_engine.php';
require_once __DIR__ . '/../core/slug_generator.php';
require_once __DIR__ . '/../core/seo_engine.php';

use NeuralPress\Core\Database;
use NeuralPress\Core\AIContentEngine;
use NeuralPress\Core\SlugGenerator;
use NeuralPress\Core\SEOEngine;
use NeuralPress\API\Response;

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$topic = trim($input['topic'] ?? '');
$category = trim($input['category'] ?? 'Technology');

if (empty($topic)) {
    Response::error("Generation topic parameter is required.");
}

$db = Database::getInstance();
$aiEngine = new AIContentEngine();

try {
    $res = $aiEngine->draftInvestigativePiece($topic, $category);
    
    $title = $res['title'] ?? "Factual analysis on " . $topic;
    $summary = $res['summary'] ?? "Factual bulletin outline surrounding the industrial developments of " . $topic;
    $content = $res['content'] ?? "Factual investigative body detailing modern progress in " . $topic;
    
    $slug = SlugGenerator::create($title, $db->getConnection());
    $seo = SEOEngine::compileMetadata($title, $content, $category);

    $sql = "INSERT INTO posts (author_id, title, slug, summary, content, category, seo_title, seo_description, seo_keywords, status) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')";
    $success = $db->query($sql, "sssssssss", [
        1, $title, $slug, $summary, $content, $category, $seo['seo_title'], $seo['seo_description'], $seo['seo_keywords']
    ]);

    if ($success) {
        Response::json([
            'success' => true,
            'slug' => $slug,
            'title' => $title,
            'summary' => $summary,
            'content' => $content
        ]);
    } else {
        Response::error("AI content was generated, but failed to write record into the relational store.");
    }
} catch (\Exception $e) {
    Response::error("Gemini content generation sequence faulted: " . $e->getMessage());
}
