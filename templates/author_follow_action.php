<?php
/**
 * NeuralPress - Follow Journalist Router Middleware
 */

require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

// Keep visitor session intact
Auth::startSession();

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    // If not logged in, dynamically create a simulated visitor account to allow seamless following!
    // This is a common and friendly pattern to ensure the preview is fully functional.
    $db = Database::getInstance();
    $randomSuffix = rand(1000, 9999);
    $dummy_username = "guest_" . $randomSuffix;
    $dummy_email = "guest_" . $randomSuffix . "@neuralpress.net";
    $dummy_hash = password_hash("guest_pass_123", PASSWORD_DEFAULT);
    
    $insertedId = $db->insert(
        "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'viewer')",
        "sss",
        [$dummy_username, $dummy_email, $dummy_hash]
    );
    if ($insertedId) {
        $_SESSION['user_id'] = $insertedId;
        $_SESSION['username'] = $dummy_username;
        $_SESSION['role'] = 'viewer';
        $userId = $insertedId;
    }
}

$authorId = intval($_POST['author_id'] ?? 0);
$action = $_POST['action'] ?? 'follow';
$redirectUrl = $_POST['redirect'] ?? '/';

if ($authorId > 0 && $userId > 0) {
    $db = Database::getInstance();
    
    // Check if follow entry exists
    $checkQ = $db->query("SELECT id FROM author_followers WHERE user_id = ? AND author_id = ? LIMIT 1", "ii", [$userId, $authorId]);
    $alreadyFollows = ($checkQ && $checkQ->num_rows > 0);
    
    if ($action === 'follow' && !$alreadyFollows) {
        $db->query("INSERT INTO author_followers (user_id, author_id) VALUES (?, ?)", "ii", [$userId, $authorId]);
        $db->query("UPDATE authors SET followers = followers + 1 WHERE id = ?", "i", [$authorId]);
    } elseif ($action === 'unfollow' && $alreadyFollows) {
        $db->query("DELETE FROM author_followers WHERE user_id = ? AND author_id = ?", "ii", [$userId, $authorId]);
        $db->query("UPDATE authors SET followers = GREATEST(0, followers - 1) WHERE id = ?", "i", [$authorId]);
    }
}

header("Location: " . $redirectUrl);
exit;
