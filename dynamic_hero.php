<?php
/**
 * NeuralPress - Dynamic Fallback Image Generator
 * Native PHP 7+ implementation
 */
require_once __DIR__ . '/config.php';

use NeuralPress\Core\ImageEngine;

$title = $_GET['title'] ?? 'NeuralPress News Update';
$category = $_GET['cat'] ?? 'World';

$engine = new ImageEngine();
$engine->drawGradientBanner($title, $category, true);
exit;
