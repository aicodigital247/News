<?php
/**
 * NeuralPress - Ad Slots renderer
 */
use NeuralPress\Core\Database;

function render_ad(string $position): void {
    $db = Database::getInstance();
    $res = $db->query("SELECT code_snippet, id FROM ads WHERE slot_position = ? AND status = 'active' LIMIT 1", "s", [$position]);
    if ($res && $row = $res->fetch_assoc()) {
        $db->query("INSERT INTO ad_events (ad_id, event_type, ip_address) VALUES (?, 'impression', ?)", "is", [$row['id'], $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
        echo '<div class="ad-container border border-gray-200 bg-white p-3 text-center rounded text-xs select-none shadow-xs my-4">';
        echo '<span class="text-[9px] font-mono text-gray-400 block mb-1">SPONSORED ADVERTISEMENT</span>';
        echo $row['code_snippet'];
        echo '</div>';
    }
}
