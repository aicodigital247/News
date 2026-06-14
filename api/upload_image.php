<?php
/**
 * NeuralPress - Image Upload Endpoint for Summernote Editor
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/response.php';

use NeuralPress\Core\Auth;
use NeuralPress\API\Response;

Auth::startSession();
$user = Auth::getCurrentUser();
if (!$user) {
    Response::error("Unauthorized access, active newsroom session required.", 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error("Only POST requests are permitted for file uploads.", 405);
}

if (!isset($_FILES['image']) && !isset($_FILES['file'])) {
    Response::error("No image file detected in payload (key 'image' or 'file' is required).", 400);
}

$file = $_FILES['image'] ?? $_FILES['file'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowedTypes)) {
    // Basic extension check fallback as well
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($ext, $allowedExts)) {
        Response::error("Unsupported file type. Only JPEG, PNG, GIF, and WEBP are permitted.", 400);
    }
}

// 5MB limit
if ($file['size'] > 5 * 1024 * 1024) {
    Response::error("File size exceeds 5MB limit.", 400);
}

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (empty($ext)) {
    $ext = 'png';
}
$filename = 'upload_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $fileUrl = '/uploads/' . $filename;
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'url' => $fileUrl,
        'link' => $fileUrl
    ]);
    exit;
} else {
    Response::error("Failed to move uploaded file to destination directory.", 500);
}
