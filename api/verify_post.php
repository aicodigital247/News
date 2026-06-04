<?php
/**
 * NeuralPress - Run Post Fact and Clickbait Verifications
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/rate_limit.php';
require_once __DIR__ . '/../core/trust_score_engine.php';
require_once __DIR__ . '/../core/fake_news_detector.php';

use NeuralPress\Core\Database;
use NeuralPress\Core\TrustScoreEngine;
use NeuralPress\Core\FakeNewsDetector;
use NeuralPress\API\Response;

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$postId = intval($input['id'] ?? 0);

if (!$postId) {
    Response::error("Post ID parameter is required.");
}

$db = Database::getInstance();
$res = $db->query("SELECT title, content FROM posts WHERE id = ? LIMIT 1", "i", [$postId]);

if ($res && $post = $res->fetch_assoc()) {
    $trustRes = TrustScoreEngine::evaluate($post['title'], $post['content']);
    $clickRes = FakeNewsDetector::scan($post['title'], $post['content']);

    $trustScore = intval($trustRes['trust_score'] ?? 100);
    $riskLevel = $trustRes['risk_level'] ?? 'low';
    $reason = $trustRes['reason'] ?? 'Passed local heuristics audits.';

    if ($clickRes['is_clickbait']) {
        $trustScore = max(10, $trustScore - 15);
        $reason .= " (Clickbait Penalty Applied: " . implode(', ', $clickRes['flagged_words']) . ")";
    }

    $status = 'approved';
    if ($trustScore < 60) {
        $riskLevel = 'high';
        $status = 'flagged';
    }

    $db->query("UPDATE posts SET trust_score = ?, risk_level = ?, verification_reason = ?, status = ? WHERE id = ?", "isssi", [
        $trustScore, $riskLevel, $reason, $status, $postId
    ]);

    Response::success([
        'message' => 'Linguistic facts processed successfully.',
        'post' => [
            'id' => $postId,
            'status' => $status,
            'trust_score' => $trustScore,
            'risk_level' => $riskLevel,
            'verification_reason' => $reason
        ]
    ]);
} else {
    Response::error("Target post was not found on active query nodes.", 404);
}
