<?php
/**
 * NeuralPress - Admin sign out procedure
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;

Auth::logout();
header('Location: /');
exit;
