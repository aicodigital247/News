<?php
/**
 * NeuralPress - Search view template
 */
require_once NP_DIR . '/includes/header.php';
require_once NP_DIR . '/includes/navbar.php';

use NeuralPress\Core\Database;

$query = trim($_GET['q'] ?? '');
$db = Database::getInstance();

$posts = [];
if (!empty($query)) {
    $searchWild = "%" . $query . "%";
    $res = $db->query("SELECT * FROM posts WHERE status = 'published' AND (title LIKE ? OR content LIKE ?) ORDER BY id DESC LIMIT 20", "ss", [$searchWild, $searchWild]);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $posts[] = $row;
        }
    }
}
?>

<main class="max-w-7xl mx-auto px-6 py-8">
    <h1 class="sidebar-title mb-8">Search Results for: "<?php echo htmlspecialchars($query); ?>"</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Main Results Grid -->
        <div class="lg:col-span-8">
            <form action="/search" method="GET" class="mb-6 flex gap-2">
                <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" class="bg-white border border-slate-200 px-4 py-2 w-full rounded focus:outline-none focus:border-[#bb1919] font-sans text-sm" placeholder="Search investigations..." required>
                <button type="submit" class="bg-[#bb1919] text-white px-6 py-2 text-sm font-bold uppercase hover:bg-[#801111] transition cursor-pointer">Search</button>
            </form>

            <?php if (empty($posts)): ?>
                <div class="bg-white border border-slate-200 p-8 text-center rounded">
                    <p class="text-slate-500 font-light">No investigative records matched your parameters. Try broad corporate or geographic keywords.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <?php
                    foreach ($posts as $post) {
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
