<?php
/**
 * NeuralPress - Author Profile Template (Google E-E-A-T Compliant)
 */

use NeuralPress\Core\Database;

$authorId = intval($_GET['id'] ?? 0);
$db = Database::getInstance();

$authorRes = $db->query("SELECT * FROM authors WHERE id = ? LIMIT 1", "i", [$authorId]);
$author = $authorRes ? $authorRes->fetch_assoc() : null;

if (!$author && $authorId > 0) {
    // Attempt fallback from Users to auto-bootstrap author details
    $userRes = $db->query("SELECT * FROM users WHERE id = ? LIMIT 1", "i", [$authorId]);
    if ($userRes && $uItem = $userRes->fetch_assoc()) {
        $authorName = ucwords($uItem['username']);
        $authorBio = "Senior Investigative Journalist covering deep-dive global bulletins at NeuralPress.";
        $authorImg = "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80"; // default Professional Avatar
        
        $db->query("INSERT INTO authors (id, name, image, bio, facebook, twitter, linkedin, followers, total_posts, verified) 
                    VALUES (?, ?, ?, ?, 'neuralpress', 'neuralpress', 'neuralpress', 1230, 18, 1)", 
                    "isss", [$authorId, $authorName, $authorImg, $authorBio]);
                    
        $authorRes = $db->query("SELECT * FROM authors WHERE id = ? LIMIT 1", "i", [$authorId]);
        $author = $authorRes ? $authorRes->fetch_assoc() : null;
    }
}

if (!$author) {
    // Fallback if completely missing
    $pageTitle = "Author Node Unresolved";
    require_once NP_DIR . '/includes/header.php';
    require_once NP_DIR . '/includes/navbar.php';
    ?>
    <main class="max-w-3xl mx-auto px-6 py-16 text-center">
        <h1 class="text-3xl font-extrabold text-slate-800">Author Node Not Found</h1>
        <p class="text-slate-500 mt-2 font-light">The requested journalist profile was not active on our public directories.</p>
        <a href="/" class="inline-block mt-4 text-[#bb1919] font-bold hover:underline font-mono text-xs">Return to Home</a>
    </main>
    <?php
    require_once NP_DIR . '/includes/footer.php';
    exit;
}

// Update total posts count for this author before loading list
$countPostsRes = $db->query("SELECT COUNT(*) as cnt FROM posts WHERE author_id = ? AND status = 'published'", "i", [$authorId]);
if ($countPostsRes) {
    $pCount = intval($countPostsRes->fetch_assoc()['cnt'] ?? 0);
    $db->query("UPDATE authors SET total_posts = ? WHERE id = ?", "ii", [$pCount, $authorId]);
    $author['total_posts'] = $pCount;
}

// Fetch published bulletins authored by this journalist
$articlesRes = $db->query("SELECT * FROM posts WHERE author_id = ? AND status = 'published' ORDER BY created_at DESC", "i", [$authorId]);
$authorPosts = [];
if ($articlesRes) {
    while ($postItem = $articlesRes->fetch_assoc()) {
        $authorPosts[] = $postItem;
    }
}

// SEO variables
$pageTitle = $author['name'] . " - Journalist Profile";
$pageDescription = "Read the latest investigative journalism, factual publications, and curated reports written by " . $author['name'] . " at NeuralPress.";
$pageImage = $author['image'];

$isAuthorPage = true;

require_once NP_DIR . '/includes/header.php';
require_once NP_DIR . '/includes/navbar.php';
?>
<main class="max-w-7xl mx-auto px-6 py-8">
    <!-- Breadcrumb -->
    <?php require_once NP_DIR . '/includes/breadcrumbs.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Body Content -->
        <div class="lg:col-span-8 space-y-8">
            
            <!-- E-E-A-T Author Showcase Profile Box -->
            <div class="bg-gradient-to-br from-slate-50 to-white dark:from-slate-900/60 dark:to-slate-900/20 border border-slate-200 dark:border-slate-800 p-8 rounded-2xl shadow-sm transition-colors duration-200">
                <div class="flex flex-col sm:flex-row items-center gap-8 text-center sm:text-left">
                    <img class="w-32 h-32 rounded-full object-cover border-4 border-white dark:border-slate-800 shadow-md shrink-0 focus-ring" src="<?php echo htmlspecialchars($author['image'] ?: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80'); ?>" alt="Author Avatar" referrerPolicy="no-referrer">
                    
                    <div class="space-y-4 flex-1">
                        <div>
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                                    <?php echo htmlspecialchars($author['name']); ?>
                                </h1>
                                <?php if ($author['verified']): ?>
                                    <span class="bg-emerald-55 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 text-[10px] font-mono uppercase tracking-wider font-extrabold px-2.5 py-1 rounded border border-emerald-200 dark:border-emerald-800/80" title="NeuralPress Verified Journalist">
                                        Verified Expert ✓
                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-[11px] font-mono uppercase tracking-widest text-[#bb1919] mt-1 font-bold">Investigative Journalist</p>
                        </div>

                        <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed font-light">
                            <?php echo htmlspecialchars($author['bio'] ?: 'A veteran contributor and researcher specializing in verifying global bulletins and drafting key analytical intelligence stories.'); ?>
                        </p>

                        <!-- Share / Follow action -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4">
                            <form action="/templates/author_follow_action.php" method="POST">
                                <input type="hidden" name="author_id" value="<?php echo $authorId; ?>">
                                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                                <?php 
                                $isFollowing = false;
                                if (isset($_SESSION['user_id'])) {
                                    $followRes = $db->query("SELECT id FROM author_followers WHERE user_id = ? AND author_id = ? LIMIT 1", "ii", [$_SESSION['user_id'], $authorId]);
                                    if ($followRes && $followRes->num_rows > 0) {
                                        $isFollowing = true;
                                    }
                                }
                                if ($isFollowing): 
                                ?>
                                    <button type="submit" name="action" value="unfollow" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-705 dark:text-slate-300 text-xs font-bold py-2.5 px-6 rounded-md border border-slate-300 dark:border-slate-700 transition-colors duration-150 cursor-pointer">
                                        Unfollow Node
                                    </button>
                                <?php else: ?>
                                    <button type="submit" name="action" value="follow" class="bg-[#bb1919] hover:bg-[#801111] text-white text-xs font-bold py-2.5 px-6 rounded-md transition-colors duration-150 cursor-pointer shadow-sm">
                                        Follow Expert
                                    </button>
                                <?php endif; ?>
                            </form>

                            <!-- Social Handles -->
                            <div class="flex items-center gap-3 font-mono text-xs text-slate-400">
                                <?php if (!empty($author['facebook'])): ?>
                                    <a href="https://facebook.com/<?php echo htmlspecialchars($author['facebook']); ?>" target="_blank" rel="noopener noreferrer" class="hover:text-[#bb1919] transition-colors">FB</a>
                                <?php endif; ?>
                                <?php if (!empty($author['twitter'])): ?>
                                    <a href="https://twitter.com/<?php echo htmlspecialchars($author['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="hover:text-[#bb1919] transition-colors">TW</a>
                                <?php endif; ?>
                                <?php if (!empty($author['linkedin'])): ?>
                                    <a href="https://linkedin.com/in/<?php echo htmlspecialchars($author['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="hover:text-[#bb1919] transition-colors">LN</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4 border-t border-slate-200 dark:border-slate-800 mt-8 pt-6 text-center">
                    <div>
                        <span class="text-[9px] font-mono text-slate-400 dark:text-slate-500 uppercase tracking-widest block">Followers</span>
                        <strong class="text-slate-900 dark:text-white text-2xl font-black font-mono"><?php echo number_format($author['followers']); ?></strong>
                    </div>
                    <div class="border-x border-slate-200 dark:border-slate-800">
                        <span class="text-[9px] font-mono text-slate-400 dark:text-slate-500 uppercase tracking-widest block">Articles</span>
                        <strong class="text-slate-900 dark:text-white text-2xl font-black font-mono"><?php echo number_format($author['total_posts']); ?></strong>
                    </div>
                    <div>
                        <span class="text-[9px] font-mono text-slate-400 dark:text-slate-500 uppercase tracking-widest block">Status</span>
                        <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 block pt-1.5 uppercase font-mono">ACTIVE</span>
                    </div>
                </div>
            </div>

            <!-- Published Bulletins section -->
            <div class="space-y-6">
                <h2 class="text-xs font-mono uppercase tracking-widest text-slate-400 dark:text-slate-350 font-black flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="inline-block w-2.5 h-4 bg-[#bb1919] rounded-sm animate-pulse"></span> Publications Log
                </h2>

                <?php if (empty($authorPosts)): ?>
                    <div class="text-center py-12 bg-white border border-slate-200 rounded text-slate-400 text-sm font-light">
                        No articles published yet by this journalist.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <?php foreach ($authorPosts as $postItem): 
                            $artUrl = \UrlManager::getArticleUrl($postItem['slug']);
                            $artThumb = $postItem['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=400&q=80';
                        ?>
                        <div class="flex flex-col bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800/80 rounded-xl hover:shadow-md transition-all duration-200 group">
                            <a href="<?php echo htmlspecialchars($artUrl); ?>" class="relative block aspect-video w-full overflow-hidden shrink-0 rounded-t-xl bg-slate-50 dark:bg-slate-950">
                                <img class="w-full h-full object-cover group-hover:scale-102 transition-transform duration-300" src="<?php echo htmlspecialchars($artThumb); ?>" alt="Preview" referrerPolicy="no-referrer">
                            </a>
                            <div class="p-5 flex flex-col justify-between flex-grow space-y-4">
                                <div class="space-y-2">
                                    <span class="text-[9px] font-bold text-[#bb1919] uppercase tracking-wider block bg-[#bb1919]/5 px-2 py-0.5 rounded-xs w-max"><?php echo htmlspecialchars($postItem['category']); ?></span>
                                    <a href="<?php echo htmlspecialchars($artUrl); ?>" class="text-slate-900 dark:text-slate-100 font-extrabold text-base tracking-tight leading-snug hover:text-[#bb1919] dark:hover:text-[#bb1919] transition-colors block line-clamp-2 pt-1 font-sans">
                                        <?php echo htmlspecialchars($postItem['title']); ?>
                                    </a>
                                </div>
                                <p class="text-slate-500 dark:text-slate-400 text-xs font-light line-clamp-2"><?php echo htmlspecialchars($postItem['summary']); ?></p>
                                <div class="flex items-center justify-between text-[10px] font-mono text-slate-400 dark:text-slate-500 pt-2 border-t border-slate-100 dark:border-slate-800">
                                    <span><?php echo date('M j, Y', strtotime($postItem['created_at'])); ?></span>
                                    <span><?php echo intval($postItem['views']); ?> node reads</span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
        </div>

        <!-- Sidebar Stream -->
        <div class="lg:col-span-4">
            <?php require_once NP_DIR . '/includes/sidebar.php'; ?>
        </div>
    </div>
</main>
<?php
require_once NP_DIR . '/includes/footer.php';
?>
