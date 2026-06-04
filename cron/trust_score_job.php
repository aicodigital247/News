<?php
/**
 * NeuralPress - Trust Score recalculation crontab
 */

namespace NeuralPress\Cron;

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/ai_verifier.php';

use NeuralPress\Core\Database;

$db = Database::getInstance();
echo "[-] Recalculating Trust Scores...\n";

// Apply passive penalty models to flagged contents to lock reviews
$db->query("UPDATE posts SET trust_score = IF(trust_score >= 15, trust_score - 5, 10) WHERE status = 'flagged'");
echo "[✓] Trust scores indexed.\n";
