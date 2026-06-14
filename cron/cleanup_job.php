<?php
/**
 * NeuralPress - Daily Cache and Log Pruner
 */

namespace NeuralPress\Cron;

// Ensure configuration is loaded if executed directly
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Database;
use NeuralPress\Core\Logger;

echo "[-] Sweeping expired file caches...\n";
$dir = __DIR__ . '/../cache/';
if (is_dir($dir)) {
    foreach (glob($dir . "*.cache") as $file) {
        if (time() - filemtime($file) > 86400 * 3) { // 3 days
            @unlink($file);
            echo " - Pruned expired cache file: " . basename($file) . "\n";
        }
    }
}

echo "[-] Pruning low-reach outdated bulletin publications...\n";
try {
    $db = Database::getInstance();
    $daysThreshold = 30;
    $maxViews = 10;
    $dateLimit = date('Y-m-d H:i:s', strtotime("-{$daysThreshold} days"));

    // Fetch matching posts to delete
    $postsRes = $db->query(
        "SELECT id, title, views, created_at FROM posts WHERE created_at < ? AND views < ?",
        "si",
        [$dateLimit, $maxViews]
    );

    $purgedCount = 0;
    if ($postsRes) {
        while ($post = $postsRes->fetch_assoc()) {
            $postId = intval($post['id']);
            $title = $post['title'];
            $views = $post['views'];

            // 1. Delete associated promotions to maintain schema integrity
            $db->query("DELETE FROM promotions WHERE post_id = ?", "i", [$postId]);

            // 2. Delete the actual post
            $db->query("DELETE FROM posts WHERE id = ?", "i", [$postId]);

            echo " - Purged low-reach post ID {$postId}: '{$title}' ({$views} views, created {$post['created_at']})\n";
            $purgedCount++;
        }
    }

    if ($purgedCount > 0) {
        Logger::log("Daily purge cron: Removed {$purgedCount} posts older than 30 days with < 10 views.", "SYSTEM");
    }
    echo " - Successfully pruned {$purgedCount} low-views posts.\n";

} catch (\Exception $e) {
    echo " [!] Purge failed: " . $e->getMessage() . "\n";
    Logger::log("Purge failure inside cleanup_job.php: " . $e->getMessage(), "ERROR");
}

echo "[✓] Core sweep completed successfully.\n";

