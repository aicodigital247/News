<?php
/**
 * NeuralPress - SEO Intelligence Optimization
 */

namespace NeuralPress\Core;

class SEOEngine {
    public static function compileMetadata(string $title, string $content, string $category): array {
        $cleanTitle = mb_substr(strip_tags($title), 0, 60) . " - NeuralPress AI";
        $desc = mb_substr(strip_tags($content), 0, 155) . "...";
        
        $words = str_word_count(strtolower(strip_tags($content)), 1);
        $stops = ['the', 'and', 'with', 'for', 'this', 'that', 'your', 'from', 'have', 'were', 'about'];
        $filtered = array_diff($words, $stops);
        $counts = array_count_values($filtered);
        arsort($counts);
        $keywords = array_slice(array_keys($counts), 0, 5);
        $keywords[] = strtolower($category);
        $keywords[] = 'news';

        return [
            'seo_title' => $cleanTitle,
            'seo_description' => $desc,
            'seo_keywords' => implode(', ', $keywords)
        ];
    }
}
