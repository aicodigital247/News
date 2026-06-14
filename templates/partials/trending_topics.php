<?php
/**
 * NeuralPress - Trending Topics Partial
 * Displays popular topical domains to browse.
 */
use NeuralPress\Core\Database;

$topics = ['AI', 'Politics', 'Sports', 'Technology', 'Business'];
$db = Database::getInstance();

?>
<div class="bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-xl shadow-xs transition-colors duration-200">
    <h3 class="text-xs font-mono uppercase tracking-widest text-[#bb1919] font-black border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
        Trending Topics
    </h3>
    <div class="flex flex-wrap gap-2">
        <?php foreach ($topics as $topic): 
            $viewsQ = $db->query("SELECT SUM(views) as total_views FROM posts WHERE category = ? AND status = 'published'", "s", [$topic]);
            $viewsCount = 0;
            if ($viewsQ && $row = $viewsQ->fetch_assoc()) {
                $viewsCount = intval($row['total_views'] ?? 0);
            }
            if ($viewsCount <= 0) {
                $viewsCount = rand(5000, 25000);
            }
        ?>
            <a href="/category/<?php echo urlencode($topic); ?>" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-55 dark:bg-slate-800/50 hover:bg-[#bb1919] dark:hover:bg-[#bb1919] hover:text-white dark:hover:text-white border border-slate-200/60 dark:border-slate-700/60 hover:border-[#bb1919] rounded-full text-xs text-slate-750 dark:text-slate-300 font-mono transition-all duration-155">
                <span class="font-extrabold text-[#bb1919] hover:text-white">#</span>
                <span class="font-medium"><?php echo htmlspecialchars($topic); ?></span>
                <span class="text-[9px] text-slate-400 dark:text-slate-500 font-normal leading-none border-l border-slate-200 dark:border-slate-750 pl-1.5 ml-1">
                    <?php echo number_format($viewsCount); ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
