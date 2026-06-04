<?php
/**
 * NeuralPress - API Authentication Lock
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';

use NeuralPress\Core\Auth;
use NeuralPress\API\Response;

Auth::startSession();
$user = Auth::getCurrentUser();
if (!$user) {
    Response::error("Unauthorized access, valid active newsroom session required.", 401);
}
