<?php
/**
 * NeuralPress - List high interaction titles
 */
use NeuralPress\Core\Database;
$db = Database::getInstance();
$res = $db->query("SELECT title, slug, views FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 5");
if ($res) {
    echo '<ul class="space-y-3">';
    $rank = 1;
    while ($row = $res->fetch_assoc()) {
        echo '<li class="flex gap-2 items-start text-xs font-sans">';
        echo '<span class="font-black text-lg text-gray-300 leading-none">#' . $rank . '</span>';
        echo '<div class="space-y-0.5">';
        echo '<a href="/news/' . htmlspecialchars($row['slug']) . '" class="font-bold text-slate-800 hover:text-[#bb1919] transition leading-snug block">' . htmlspecialchars($row['title']) . '</a>';
        echo '<span class="text-[10px] font-mono text-gray-400">' . number_format($row['views']) . ' reads</span>';
        echo '</div>';
        echo '</li>';
        $rank++;
    }
    echo '</ul>';
} else {
    echo '<p class="text-[11px] text-gray-400">Loading trend indexes...</p>';
}
