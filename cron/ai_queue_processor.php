<?php
/**
 * NeuralPress - Background AI Queue Processor
 */

namespace NeuralPress\Cron;

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/gemini.php';

use NeuralPress\Core\Database;

$db = Database::getInstance();
echo "[-] Executing background AI Queue Process...\n";

$jobs = $db->query("SELECT * FROM ai_queue WHERE status = 'queued' LIMIT 3");
if ($jobs) {
    while ($job = $jobs->fetch_assoc()) {
        $db->query("UPDATE ai_queue SET status = 'processing' WHERE id = ?", "i", [$job['id']]);
        echo " - Executing Action ID: {$job['id']} [{$job['action_type']}]\n";
        $db->query("UPDATE ai_queue SET status = 'completed' WHERE id = ?", "i", [$job['id']]);
    }
}
echo "[✓] BI Queue Process run completed.\n";
