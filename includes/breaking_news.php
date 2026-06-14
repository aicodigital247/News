<?php
/**
 * NeuralPress - Unified Conveyor Ticker
 * Loads up to 5 breaking stories to represent continuous coverage.
 */
use NeuralPress\Core\Database;

$db = Database::getInstance();
$res = $db->query("SELECT title, slug, category FROM posts WHERE status = 'published' ORDER BY id DESC LIMIT 5");

$tickerItems = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $tickerItems[] = $row;
    }
}

if (!empty($tickerItems)) {
    echo '<div class="overflow-hidden whitespace-nowrap w-full inline-block">';
    echo '<div class="inline-flex gap-8 animate-[marquee_25s_linear_infinite] hover:[animation-play-state:paused] cursor-pointer">';
    
    // Output twice to create visual infinite loops
    for ($i = 0; $i < 2; $i++) {
        foreach ($tickerItems as $index => $row) {
            $catLabel = !empty($row['category']) ? '[' . htmlspecialchars(strtoupper($row['category'])) . '] ' : '';
            echo '<span class="inline-flex items-center text-xs font-semibold text-slate-100 hover:text-[#bb1919] transition-colors shrink-0">';
            echo '• &nbsp;';
            echo '<a href="/news/' . htmlspecialchars($row['slug']) . '" class="hover:underline font-mono tracking-tight">' . $catLabel . htmlspecialchars($row['title']) . '</a>';
            echo '</span>';
        }
    }
    
    echo '</div>';
    echo '</div>';
    
    // Inject custom continuous marquee keyframes directly if not defined globally
    echo '
    <style>
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
    </style>';
} else {
    echo '<span class="font-mono text-[10px] text-slate-400">STATUS: ACTIVE // ALL HEURISTIC NEWSROOM DATA CHANNELS IN NOMINAL INTEGRITY SYNC.</span>';
}
?>
