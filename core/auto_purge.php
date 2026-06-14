<?php
/**
 * NeuralPress - Low Views Article Auto-Purge Engine
 */

namespace NeuralPress\Core;

class AutoPurge {

    /**
     * Lazy-cron entry point representing background check
     */
    public static function trigger(): void {
        $lockFile = sys_get_temp_dir() . '/neural_press_purge_v3.lock';
        $now = time();

        if (file_exists($lockFile)) {
            $lastRun = intval(@file_get_contents($lockFile));
            if ($now - $lastRun < 43200) { // Throttle execution to twice daily (12hr window)
                return;
            }
        }

        // Lock file update
        @file_put_contents($lockFile, $now);

        // Run deletion
        self::execute();
    }

    /**
     * Purges content older than 30 days that has had insufficient reach (low views)
     */
    public static function execute(): int {
        try {
            $db = Database::getInstance();
            
            // Standard parameters: older than 30 days and has less than 10 views
            $thresholdDays = 30;
            $maxViews = 10;
            $dateLimit = date('Y-m-d H:i:s', strtotime("-{$thresholdDays} days"));

            // Gather elements for audit trails
            $res = $db->query("SELECT id, title, views FROM posts WHERE created_at < ? AND views < ?", "si", [$dateLimit, $maxViews]);
            $purgedCount = 0;
            $purgedTitles = [];

            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $postId = intval($row['id']);
                    $purgedTitles[] = "'" . $row['title'] . "' (" . $row['views'] . " views)";

                    // 1. Delete associated promotions to prevent foreign keys or orphaned rows
                    $db->query("DELETE FROM promotions WHERE post_id = ?", "i", [$postId]);

                    // 2. Delete the actual post
                    $db->query("DELETE FROM posts WHERE id = ?", "i", [$postId]);

                    $purgedCount++;
                }
            }

            if ($purgedCount > 0) {
                $logMsg = sprintf("Auto-Purge Cron executed: Cleaned %d low-views posts older than 30 days: %s", $purgedCount, implode(', ', $purgedTitles));
                Logger::log($logMsg, "SYSTEM");
            }
            return $purgedCount;

        } catch (\Exception $e) {
            error_log("[AutoPurge Failure] " . $e->getMessage());
            Logger::log("Error running auto-purge logic: " . $e->getMessage(), "ERROR");
            return 0;
        }
    }
}
