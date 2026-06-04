<?php
/**
 * NeuralPress - API Rate Limiting Gatekeep
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';

use NeuralPress\Core\Security;
use NeuralPress\API\Response;

$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!Security::checkRateLimit("api_limiter_" . $ip, 60, 60)) {
    Response::error("Too many requests on this node. Rate limit throttled.", 429);
}
