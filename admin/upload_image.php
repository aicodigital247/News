<?php
/**
 * NeuralPress - Image Upload Endpoint for Summernote Editor
 * Native PHP 7+ implementation
 */
require_once __DIR__ . '/../config.php';

use NeuralPress\Core\Auth;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = Auth::getCurrentUser();
if (!$user) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access, active newsroom session required.'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Only POST requests are permitted for file uploads.'
    ]);
    exit;
}

if (!isset($_FILES['image']) && !isset($_FILES['file'])) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'No image file detected in payload (key \'image\' or \'file\' is required).'
    ]);
    exit;
}

$file = $_FILES['image'] ?? $_FILES['file'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Validate file type
$isValidType = false;
if (isset($file['type']) && in_array($file['type'], $allowedTypes)) {
    $isValidType = true;
} else {
    // Fallback file extension check
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowedExts)) {
        $isValidType = true;
    }
}

if (!$isValidType) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Unsupported file type. Only JPEG, PNG, GIF, and WEBP are permitted.'
    ]);
    exit;
}

// 5MB limit
if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'File size exceeds 5MB limit.'
    ]);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
if (empty($ext)) {
    $ext = 'png';
}

$filename = 'upload_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'] ?? '', $targetPath)) {
    $fileUrl = '/uploads/' . $filename;
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'url' => $fileUrl,
        'link' => $fileUrl
    ]);
    exit;
} else {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => 'Failed to move uploaded file to destination directory.'
    ]);
    exit;
}
