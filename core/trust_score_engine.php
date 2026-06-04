<?php
/**
 * NeuralPress - Trust Score calculation engine
 */

namespace NeuralPress\Core;

class TrustScoreEngine {
    public static function evaluate(string $title, string $content): array {
        require_once __DIR__ . '/ai_verifier.php';
        return analyze_content($title, $content);
    }
}
