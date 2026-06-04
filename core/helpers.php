<?php
/**
 * NeuralPress - Helpers
 * Global utility helpers for layout formatting and translations
 */

namespace NeuralPress\Core;

class Helpers {
    public static function escape(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function truncate(string $text, int $limit = 150, string $end = '...'): string {
        if (mb_strlen($text) <= $limit) return $text;
        return mb_substr($text, 0, $limit) . $end;
    }

    public static function formatRelativeTime(string $datetime): string {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) return "Just now";
        $mins = round($diff / 60);
        if ($mins < 60) return "{$mins}m ago";
        $hours = round($mins / 60);
        if ($hours < 24) return "{$hours}h ago";
        return date('j M Y', $timestamp);
    }
}
