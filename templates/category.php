<?php
/**
 * NeuralPress - Category view template
 */
require_once NP_DIR . '/includes/header.php';
require_once NP_DIR . '/includes/navbar.php';

use NeuralPress\Core\Database;

$category = $_GET['category'] ?? 'World';
$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE status = 'published' AND category = ? ORDER BY id DESC LIMIT 12", "s", [$category]);
?>

<main class="max-w-7xl mx-auto px-6 py-8">
    <h1 class="text-xs font-mono uppercase tracking-widest text-slate-400 dark:text-slate-500 font-black mb-8 flex items-center gap-2">
        <span class="inline-block w-2.5 h-2.5 bg-[#bb1919] rounded-sm animate-pulse"></span>
        DIRECTORY ARCHIVE: <?php echo htmlspecialchars(strtoupper($category)); ?> INVESTIGATIVE COVERAGES
    </h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Category Grid -->
        <div class="lg:col-span-8">
            <?php if (!$res || $res->num_rows === 0): ?>
                <div class="bg-white dark:bg-slate-900/40 border border-slate-205 dark:border-slate-800 p-10 text-center rounded-2xl shadow-xs">
                    <p class="text-slate-500 dark:text-slate-400 font-light">No active investigative archives in the <?php echo htmlspecialchars($category); ?> namespace.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php
                    while ($post = $res->fetch_assoc()) {
                        require NP_DIR . '/includes/post_card.php';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="lg:col-span-4">
            <?php require_once NP_DIR . '/includes/sidebar.php'; ?>
        </div>
    </div>
</main>

<?php require_once NP_DIR . '/includes/footer.php'; ?>
