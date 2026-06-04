<?php
/**
 * NeuralPress - Slug Generator Utility
 */

namespace NeuralPress\Core;

class SlugGenerator {
    public static function create(string $title, ?\mysqli $db = null): string {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $title);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);
        $slug = preg_replace('~[^-\w]+~', '', $slug);
        $slug = trim($slug, '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug);

        if (empty($slug)) {
            $slug = 'untitled-' . rand(1000, 9999);
        }

        if ($db) {
            $originalSlug = $slug;
            $count = 1;
            while (true) {
                $stmt = $db->prepare("SELECT id FROM posts WHERE slug = ? LIMIT 1");
                $stmt->bind_param("s", $slug);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows === 0) {
                    $stmt->close();
                    break;
                }
                $stmt->close();
                $slug = $originalSlug . '-' . $count;
                $count++;
            }
        }

        return $slug;
    }
}
