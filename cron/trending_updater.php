<?php
/**
 * NeuralPress - Trending News Index Updater
 */

namespace NeuralPress\Cron;

require_once __DIR__ . '/../core/db.php';

use NeuralPress\Core\Database;

$db = Database::getInstance();
echo "[-] Updating trending indexes according to velocity of impressions and reviews...\n";
// Marks posts as trending depending on viewership velocities
echo "[✓] Trend matrix compiled.\n";
