<?php
/**
 * NeuralPress - Article Detail Template
 */
use NeuralPress\Core\Database;

$slug = $_GET['slug'] ?? '';
$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE slug = ? LIMIT 1", "s", [$slug]);
$post = ($res) ? $res->fetch_assoc() : null;

// Assign SEO parameters ahead of the header rendering
if ($post) {
    $pageTitle = !empty($post['seo_title']) ? $post['seo_title'] : $post['title'];
    $pageDescription = !empty($post['seo_description']) ? $post['seo_description'] : $post['summary'];
    $pageKeywords = !empty($post['seo_keywords']) ? $post['seo_keywords'] : (strtolower($post['category']) . ', neuralpress');
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
    // Update views count
    $db->query("UPDATE posts SET views = views + 1 WHERE id = ?", "i", [$post['id']]);
?>
<main class="max-w-7xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Body Content -->
        <div class="lg:col-span-8 bg-white border border-[#e2e8f0] p-6 rounded shadow-sm space-y-4">
            <span class="bg-[#bb1919] text-white text-[11px] font-bold uppercase px-2.5 py-1 rounded-xs">
                <?php echo htmlspecialchars($post['category']); ?>
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 leading-tight">
                <?php echo htmlspecialchars($post['title']); ?>
            </h1>
            <p class="text-slate-400 text-xs font-mono">Published: <?php echo date('j M Y H:i', strtotime($post['created_at'])); ?> | Node Reads: <?php echo intval($post['views']); ?></p>
            
            <div class="border-l-4 border-[#bb1919] pl-4 italic text-slate-600 my-4 text-sm font-light">
                <?php echo htmlspecialchars($post['summary']); ?>
            </div>

            <img class="w-full h-80 object-cover rounded my-4" src="<?php echo htmlspecialchars($post['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=800&q=80'); ?>" alt="NeuralPress Verification Graphic" referrerPolicy="no-referrer">

            <div class="text-slate-800 text-sm leading-relaxed space-y-4 font-light">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>

            <!-- Heuristics & AI Verification Badge -->
            <div class="bg-slate-50 border border-slate-200 p-4 rounded mt-8 flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                <div class="space-y-1">
                    <span class="inline-flex items-center gap-1.5 text-[#bb1919] font-mono text-xs uppercase font-extrabold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Trust Score Audit Log
                    </span>
                    <p class="text-xs text-slate-600 leading-relaxed font-light"><?php echo htmlspecialchars($post['verification_reason']); ?></p>
                </div>
                <div class="bg-white border border-slate-200 py-3 px-5 text-center shrink-0 rounded">
                    <span class="text-[9px] text-slate-400 font-mono block">INTEGRITY RATE</span>
                    <span class="text-2xl font-black text-emerald-600 font-mono"><?php echo intval($post['trust_score']); ?>%</span>
                </div>
            </div>
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
