<?php
/**
 * NeuralPress - Ticker news provider
 */
use NeuralPress\Core\Database;
$db = Database::getInstance();
$res = $db->query("SELECT title, slug FROM posts WHERE status = 'published' ORDER BY id DESC LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    echo '<a href="/news/' . htmlspecialchars($row['slug']) . '" class="hover:underline text-white font-medium">' . htmlspecialchars($row['title']) . '</a>';
} else {
    echo '<span>No breaking news bulletins at this moment. Standard system logs running clean.</span>';
}
