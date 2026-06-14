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
        echo '<div class="ad-container border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/60 p-4 text-center rounded-xl text-xs select-none shadow-xs my-6 max-w-full overflow-hidden transition-all hover:border-[#bb1919]/25">';
        echo '<span class="text-[9px] font-mono text-slate-400 dark:text-slate-500 block mb-1.5 uppercase tracking-widest font-black">SPONSORED AUDITED DISCLOSURE</span>';
        echo '<div class="text-slate-800 dark:text-slate-200">' . $row['code_snippet'] . '</div>';
        echo '</div>';
    }
}
