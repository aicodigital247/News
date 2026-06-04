<?php
/**
 * NeuralPress - Fake News & Clickbait Detector
 */

namespace NeuralPress\Core;

class FakeNewsDetector {
    public static function scan(string $title, string $content): array {
        $sensationalWords = ['shocking', 'unbelievable', 'you won\'t believe', 'conspiracy', 'magic', 'secret', 'miracle'];
        $titleLower = strtolower($title);
        $found = [];

        foreach ($sensationalWords as $word) {
            if (str_contains($titleLower, $word)) {
                $found[] = $word;
            }
        }

        $clickbaitFactor = count($found) * 20;
        $score = max(0, 100 - $clickbaitFactor);

        return [
            'is_clickbait' => !empty($found),
            'clickbait_score' => $score,
            'flagged_words' => $found,
            'verdict' => $score < 60 ? 'High Clickbait Risk Detected' : 'Passed Clickbait Heuristics'
        ];
    }
}
