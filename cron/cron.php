<?php
/**
 * NeuralPress - Main Cron Job Hub
 */

require_once __DIR__ . '/../config.php';

echo "NeuralPress - Initiating Automated Newsroom Background Tasks...\n";

// Execute sequentially
require_once __DIR__ . '/ai_queue_processor.php';
require_once __DIR__ . '/trust_score_job.php';
require_once __DIR__ . '/trending_updater.php';
require_once __DIR__ . '/sitemap_generator.php';
require_once __DIR__ . '/cleanup_job.php';

echo "All automated background tasks executed successfully.\n";
