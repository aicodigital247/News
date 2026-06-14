<?php
/**
 * NeuralPress - Unified BBC-Style Sidebar Widgets Stream
 * Houses search panels, followers trackers, trending counters, reads logs, editor picks, categories, monetisations and subscriptions.
 */

use NeuralPress\Core\Database;

$db = Database::getInstance();

// 1. Fetch Latest Posts
$latestRes = $db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 5");
$sidebarLatest = [];
if ($latestRes) {
    while ($p = $latestRes->fetch_assoc()) {
        $sidebarLatest[] = $p;
    }
}

// 2. Fetch Editor Picks (Articles with elite trust scores >= 85)
$picksRes = $db->query("SELECT * FROM posts WHERE status = 'published' AND trust_score >= 85 ORDER BY created_at DESC LIMIT 5");
$sidebarPicks = [];
if ($picksRes) {
    while ($p = $picksRes->fetch_assoc()) {
        $sidebarPicks[] = $p;
    }
}

// 3. Fetch Categories with counts
$catCountsRes = $db->query("SELECT category, COUNT(*) as cnt FROM posts WHERE status = 'published' GROUP BY category LIMIT 8");
$sidebarCategories = [];
if ($catCountsRes) {
    while ($row = $catCountsRes->fetch_assoc()) {
        $sidebarCategories[] = $row;
    }
}
if (empty($sidebarCategories)) {
    $sidebarCategories = [
        ['category' => 'World', 'cnt' => 12],
        ['category' => 'Business', 'cnt' => 8],
        ['category' => 'Technology', 'cnt' => 15],
        ['category' => 'Sports', 'cnt' => 6]
    ];
}
?>

<aside class="space-y-6">
    <!-- 1. Search Box -->
    <div class="bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-xl shadow-xs transition-colors duration-200">
        <h3 class="text-xs font-mono uppercase tracking-widest text-[#bb1919] font-black border-b border-slate-100 dark:border-slate-800 pb-2 mb-3">
            Search Bulletins
        </h3>
        <form action="/search" method="GET" class="relative flex items-center">
            <input type="text" name="q" placeholder="Type keywords, topics..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 focus:border-[#bb1919] focus:outline-none rounded-md text-xs py-2.5 pl-3 pr-10 placeholder-slate-400 dark:placeholder-slate-500 font-mono text-slate-900 dark:text-white">
            <button type="submit" class="absolute right-3.5 text-slate-400 hover:text-[#bb1919] transition-colors cursor-pointer">
                🔍
            </button>
        </form>
    </div>

    <!-- 2. Follow Us -->
    <?php require NP_DIR . '/templates/partials/follow_us.php'; ?>

    <!-- 3. Trending Topics -->
    <?php require NP_DIR . '/templates/partials/trending_topics.php'; ?>

    <!-- 4. Popular Posts -->
    <?php require NP_DIR . '/templates/partials/popular_posts.php'; ?>

    <!-- 5. Latest Posts -->
    <div class="bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-xl shadow-xs transition-colors duration-200">
        <h3 class="text-xs font-mono uppercase tracking-widest text-[#bb1919] font-black border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
            Latest Reports
        </h3>
        <?php if (empty($sidebarLatest)): ?>
            <p class="text-xs text-slate-400 dark:text-slate-500 font-light italic">No reports found.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($sidebarLatest as $item): 
                    $itemUrl = \UrlManager::getArticleUrl($item['slug']);
                ?>
                    <div class="space-y-1">
                        <span class="text-[9px] font-bold text-slate-400 dark:text-slate-500 font-mono uppercase">
                            <?php echo date('M j, Y H:i', strtotime($item['created_at'])); ?>
                        </span>
                        <a href="<?php echo htmlspecialchars($itemUrl); ?>" class="text-slate-900 dark:text-slate-100 hover:text-[#bb1919] dark:hover:text-[#bb1919] font-bold text-xs leading-snug tracking-tight block transition-colors duration-150 font-sans">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 6. Editor Picks -->
    <div class="bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-xl shadow-xs transition-colors duration-200">
        <h3 class="text-xs font-mono uppercase tracking-widest text-[#bb1919] font-black border-b border-slate-100 dark:border-slate-800 pb-2 mb-4">
            Editor Picks (Verified)
        </h3>
        <?php if (empty($sidebarPicks)): ?>
            <p class="text-xs text-slate-400 dark:text-slate-500 font-light italic">No elite badges current.</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($sidebarPicks as $item): 
                    $itemUrl = \UrlManager::getArticleUrl($item['slug']);
                ?>
                    <div class="flex items-start gap-2.5">
                        <span class="bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800 rounded text-[9px] font-mono font-bold py-0.5 px-1 pb-1 tracking-tight select-none">
                            <?php echo intval($item['trust_score']); ?>%
                        </span>
                        <div class="space-y-0.5 min-w-0">
                            <a href="<?php echo htmlspecialchars($itemUrl); ?>" class="text-slate-900 dark:text-slate-100 hover:text-[#bb1919] dark:hover:text-[#bb1919] font-bold text-xs leading-snug tracking-tight block line-clamp-2 transition-colors duration-150 font-sans">
                                <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 7. Categories -->
    <div class="bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 p-5 rounded-xl shadow-xs transition-colors duration-200">
        <h3 class="text-xs font-mono uppercase tracking-widest text-[#bb1919] font-black border-b border-slate-100 dark:border-slate-800 pb-2 mb-3">
            Top Categories
        </h3>
        <div class="divide-y divide-slate-100 dark:divide-slate-800 text-xs font-mono">
            <?php foreach ($sidebarCategories as $cat): ?>
                <a href="/category/<?php echo urlencode($cat['category']); ?>" class="flex items-center justify-between py-2 text-slate-650 dark:text-slate-300 hover:text-[#bb1919] dark:hover:text-[#bb1919] transition-colors font-medium">
                    <span><?php echo htmlspecialchars($cat['category']); ?></span>
                    <span class="bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-normal px-2 py-0.5 rounded text-[10px]">
                        <?php echo intval($cat['cnt']); ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 8. Advertisement Slots (Monetization Injection) -->
    <div class="space-y-2">
        <span class="text-[9px] font-mono text-slate-400 dark:text-slate-500 uppercase tracking-widest block text-center">SPONSORED LINKS</span>
        <?php 
        require_once NP_DIR . '/includes/ad_slots.php'; 
        render_ad('sidebar'); 
        ?>
    </div>

    <!-- 9. Newsletter Signup -->
    <?php require NP_DIR . '/templates/partials/newsletter.php'; ?>
</aside>
