<?php
/**
 * NeuralPress - Popular Articles Widget
 */
use NeuralPress\Core\Database;

$db = Database::getInstance();
$popularRes = $db->query(
    "SELECT * FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 5"
);

$popularPosts = [];
if ($popularRes) {
    while ($p = $popularRes->fetch_assoc()) {
        $popularPosts[] = $p;
    }
}
?>
<div class="bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-xl shadow-xs transition-colors duration-200">
    <h3 class="text-xs font-mono uppercase tracking-widest text-[#bb1919] font-black border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
        Popular Bulletins
    </h3>
    <?php if (empty($popularPosts)): ?>
        <p class="text-xs text-slate-400 dark:text-slate-500 font-light italic">No public bulletins found.</p>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($popularPosts as $index => $item): 
                $itemUrl = \UrlManager::getArticleUrl($item['slug']);
                $itemThumb = $item['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=150&q=85';
            ?>
                <div class="flex items-start gap-3 group">
                    <span class="text-2xl font-black text-slate-200 dark:text-slate-750 font-mono leading-none pt-0.5 shrink-0 select-none">
                        <?php echo sprintf("%02d", $index + 1); ?>
                    </span>
                    <div class="space-y-1 min-w-0 flex-1">
                        <span class="text-[9px] font-bold text-[#bb1919] uppercase tracking-wider block">
                            <?php echo htmlspecialchars($item['category']); ?>
                        </span>
                        <a href="<?php echo htmlspecialchars($itemUrl); ?>" class="text-slate-900 dark:text-slate-100 hover:text-[#bb1919] dark:hover:text-[#bb1919] font-bold text-xs leading-snug tracking-tight block line-clamp-2 transition-colors duration-150 font-sans">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                        <div class="flex items-center gap-2 text-[9px] font-mono text-slate-400 dark:text-slate-500">
                            <span><?php echo intval($item['views']); ?> reads</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
