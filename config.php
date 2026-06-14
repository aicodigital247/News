<?php
/**
 * NeuralPress - Main Configuration Bootstrap
 */

define('NP_DIR', __DIR__);
define('NP_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/');

// Include DB and essentials
require_once NP_DIR . '/core/db.php';
require_once NP_DIR . '/core/auth.php';
require_once NP_DIR . '/core/helpers.php';
require_once NP_DIR . '/core/csrf.php';
require_once NP_DIR . '/core/security.php';
require_once NP_DIR . '/core/cache.php';
require_once NP_DIR . '/core/logger.php';
require_once NP_DIR . '/core/gemini.php';
require_once NP_DIR . '/core/google_trends.php';
require_once NP_DIR . '/core/auto_purge.php';
require_once NP_DIR . '/core/internal_linker.php';
require_once NP_DIR . '/core/monetization_engine.php';

// Initialize session
\NeuralPress\Core\Auth::startSession();

// Execute Lazy Cron for 30-day low reach article automatic purge
\NeuralPress\Core\AutoPurge::trigger();
