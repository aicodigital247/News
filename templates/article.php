<?php
/**
 * NeuralPress - Article Detail Template
 */
use NeuralPress\Core\Database;

$slug = $_GET['slug'] ?? '';
$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE slug = ? LIMIT 1", "s", [$slug]);
$post = ($res) ? $res->fetch_assoc() : null;

$isArticlePage = true;
$author = null;

if ($post) {
    $authorId = intval($post['author_id']);
    $authorRes = $db->query("SELECT * FROM authors WHERE id = ? LIMIT 1", "i", [$authorId]);
    $author = $authorRes ? $authorRes->fetch_assoc() : null;

    if (!$author) {
        // Fallback auto-bootstrap of author profiles
        $userRes = $db->query("SELECT * FROM users WHERE id = ? LIMIT 1", "i", [$authorId]);
        if ($userRes && $uItem = $userRes->fetch_assoc()) {
            $authorName = ucwords($uItem['username']);
            $authorBio = "Senior Investigative Journalist covering deep-dive global bulletins at NeuralPress.";
            $authorImg = "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80";
            
            $db->query("INSERT INTO authors (id, name, image, bio, facebook, twitter, linkedin, followers, total_posts, verified) 
                        VALUES (?, ?, ?, ?, 'neuralpress', 'neuralpress', 'neuralpress', 1650, 24, 1)", 
                        "isss", [$authorId, $authorName, $authorImg, $authorBio]);
                        
            $authorRes = $db->query("SELECT * FROM authors WHERE id = ? LIMIT 1", "i", [$authorId]);
            $author = $authorRes ? $authorRes->fetch_assoc() : null;
        }
    }

    // Assign SEO parameters ahead of the header rendering
    $pageTitle = !empty($post['seo_title']) ? $post['seo_title'] : $post['title'];
    $pageDescription = !empty($post['seo_description']) ? $post['seo_description'] : $post['summary'];
    $pageKeywords = !empty($post['seo_keywords']) ? $post['seo_keywords'] : (strtolower($post['category']) . ', neuralpress');
    if ($author) {
        $pageImage = $author['image'];
    }
}

require_once NP_DIR . '/includes/header.php';
require_once NP_DIR . '/includes/navbar.php';

if (!$post):
?>
<main class="max-w-3xl mx-auto px-6 py-16 text-center">
    <h1 class="text-3xl font-extrabold text-slate-800">Bulletin Retracted or Unparsed</h1>
    <p class="text-slate-500 mt-2">The requested article slug is unverified or has been retracted from global publication streams.</p>
    <a href="/" class="inline-block mt-4 text-[#bb1919] font-bold hover:underline">Return to Home</a>
</main>
<?php
else:
    // Update views count & trigger views table trace log
    $db->query("UPDATE posts SET views = views + 1 WHERE id = ?", "i", [$post['id']]);
    $visitorIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $db->query("INSERT INTO post_views (post_id, ip) VALUES (?, ?)", "is", [$post['id'], $visitorIp]);
    
    // Process monetization rates
    \NeuralPress\Core\MonetizationEngine::getInstance()->trackView(intval($post['id']));
?>
<main class="max-w-7xl mx-auto px-6 py-8">
    <!-- Breadcrumbs -->
    <?php require_once NP_DIR . '/includes/breadcrumbs.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Body Content -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 p-6 sm:p-8 rounded-2xl shadow-xs space-y-5 transition-colors duration-200">
            <span class="bg-[#bb1919] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded">
                <?php echo htmlspecialchars($post['category']); ?>
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>

            <!-- Author & Reading Stats header bar -->
            <div class="flex items-center gap-2.5 text-xs text-slate-500 dark:text-slate-400 pb-2 border-b border-slate-100 dark:border-slate-800/80">
                <?php if ($author): ?>
                    <span>By <a href="/author/<?php echo $author['id']; ?>" class="font-extrabold text-[#bb1919] hover:underline"><?php echo htmlspecialchars($author['name']); ?></a></span>
                    <span>•</span>
                <?php endif; ?>
                <span class="font-mono">Published: <?php echo date('j M Y H:i', strtotime($post['created_at'])); ?></span>
                <span>•</span>
                <span class="font-mono"><?php echo intval($post['views']); ?> Reads</span>
            </div>
            
            <div class="border-l-4 border-[#bb1919] pl-4 italic text-slate-600 dark:text-slate-300 my-4 text-sm font-light leading-relaxed">
                <?php echo htmlspecialchars($post['summary']); ?>
            </div>

            <img class="w-full h-80 object-cover rounded-xl my-4 animate-fade-in" src="<?php echo htmlspecialchars($post['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=800&q=80'); ?>" alt="NeuralPress Verification Graphic" referrerPolicy="no-referrer">

            <div class="text-slate-800 dark:text-slate-200 text-sm leading-relaxed space-y-4 font-light">
                <?php 
                // Allow WYSIWYG HTML layout elements but strip any scripting blocks for protection
                $cleanContent = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $post['content']);
                
                // Instantiate the InternalLinker
                $linker = new \NeuralPress\Core\InternalLinker();
                $cleanContent = $linker->autoLink($cleanContent, intval($post['id']));
                $cleanContent = $linker->injectInlineRelatedCallout($cleanContent, intval($post['id']), $post['category']);
                
                echo $cleanContent; 
                ?>
            </div>

            <!-- Share Buttons Partial -->
            <?php require_once NP_DIR . '/templates/partials/share_buttons.php'; ?>

            <!-- Heuristics & AI Verification Badge -->
            <div class="bg-slate-50 dark:bg-slate-900/30 border border-slate-200/80 dark:border-slate-800 p-5 rounded-xl mt-8 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 text-[#bb1919] font-mono text-xs uppercase font-extrabold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Trust Score Audit Log
                    </span>
                    <p class="text-xs text-slate-600 dark:text-slate-350 leading-relaxed font-light"><?php echo htmlspecialchars($post['verification_reason']); ?></p>
                </div>
                <div class="bg-white dark:bg-slate-950 border border-slate-200/60 dark:border-slate-800 py-3 px-5 text-center shrink-0 rounded-lg">
                    <span class="text-[9px] text-slate-400 dark:text-slate-500 font-mono block">INTEGRITY RATE</span>
                    <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono"><?php echo intval($post['trust_score']); ?>%</span>
                </div>
            </div>

            <!-- E-E-A-T Author Box Partial -->
            <?php require_once NP_DIR . '/templates/partials/author_box.php'; ?>

            <!-- Professional Related Articles Showcase Grid -->
            <?php 
            $curatedArticles = $linker->getRelatedPosts($post['id'], $post['category'], 3);
            if (!empty($curatedArticles)):
            ?>
            <div class="border-t border-slate-100 dark:border-slate-800 pt-8 mt-10">
                <h3 class="text-lg font-black tracking-tight text-slate-900 dark:text-white uppercase font-mono mb-6 flex items-center gap-2">
                    <span class="inline-block w-2.5 h-4 bg-[#bb1919]"></span> Further Curated Bulletins
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <?php foreach ($curatedArticles as $curItem): 
                        $curUrl = \UrlManager::getArticleUrl($curItem['slug']);
                        $curThum = $curItem['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=400&q=80';
                    ?>
                    <div class="flex flex-col bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-805/80 rounded-xl hover:shadow-md transition-all duration-200 group border-slate-205 dark:border-slate-800">
                        <a href="<?php echo htmlspecialchars($curUrl); ?>" class="relative block aspect-video w-full overflow-hidden shrink-0 rounded-t-xl bg-slate-50 dark:bg-slate-950">
                            <img class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300" src="<?php echo htmlspecialchars($curThum); ?>" alt="<?php echo htmlspecialchars($curItem['title']); ?>" referrerPolicy="no-referrer">
                        </a>
                        <div class="p-4 flex flex-col flex-grow justify-between space-y-3">
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-[#bb1919] uppercase tracking-wider block bg-[#bb1919]/5 px-2 py-0.5 rounded-xs w-max"><?php echo htmlspecialchars($curItem['category']); ?></span>
                                <a href="<?php echo htmlspecialchars($curUrl); ?>" class="text-slate-900 dark:text-slate-100 font-bold text-sm tracking-tight leading-snug hover:text-[#bb1919] dark:hover:text-[#bb1919] transition-colors block line-clamp-2 pt-1 font-sans">
                                    <?php echo htmlspecialchars($curItem['title']); ?>
                                </a>
                            </div>
                            <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 dark:text-slate-500">
                                <span><?php echo \NeuralPress\Core\Helpers::formatRelativeTime($curItem['created_at']); ?></span>
                                <span><?php echo intval($curItem['views']); ?> reads</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comments Component Partial -->
            <?php require_once NP_DIR . '/templates/partials/comments.php'; ?>

        </div>

        <!-- Sidebar Stream -->
        <div class="lg:col-span-4">
            <?php require_once NP_DIR . '/includes/sidebar.php'; ?>
        </div>
    </div>
</main>
<?php
endif;
require_once NP_DIR . '/includes/footer.php';
?>
