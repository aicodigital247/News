<?php
/**
 * NeuralPress - Admin Category Management
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Core\CSRF;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin', 'editor']);
$adminUser = Auth::getCurrentUser();

$db = Database::getInstance();
$error = '';
$success = '';

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_category') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }

    if ($adminUser['role'] !== 'admin') {
        $error = "Access denied: Only full system Administrators can create new content categories.";
    } else {
        $name = trim($_POST['category_name'] ?? '');
        // Sanitize & validate
        if (empty($name)) {
            $error = "Category name cannot be empty.";
        } elseif (strlen($name) < 2 || strlen($name) > 50) {
            $error = "Category name must be between 2 and 50 characters.";
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $name)) {
            $error = "Category name can only contain letters, numbers, spaces, and hyphens.";
        } else {
            // Check for existence
            $existsQuery = $db->query("SELECT id FROM categories WHERE name = ?", "s", [$name]);
            if ($existsQuery && $existsQuery->num_rows > 0) {
                $error = "The category '" . htmlspecialchars($name) . "' already exists.";
            } else {
                $insertedId = $db->insert("INSERT INTO categories (name) VALUES (?)", "s", [$name]);
                if ($insertedId > 0) {
                    $success = "New category '" . htmlspecialchars($name) . "' was added successfully!";
                } else {
                    $error = "Platform failure: Unable to write to categories table.";
                }
            }
        }
    }
}

// Handle delete (optional, but good practice for full management)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_category') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }

    if ($adminUser['role'] !== 'admin') {
        $error = "Access denied: Only administrators can modify category listings.";
    } else {
        $catId = intval($_POST['category_id'] ?? 0);
        // Protect default categories from deletion to prevent blanking system out
        $catRes = $db->query("SELECT name FROM categories WHERE id = ?", "i", [$catId]);
        if ($catRes && $row = $catRes->fetch_assoc()) {
            $catName = $row['name'];
            if (in_array($catName, ['World', 'Business', 'Technology', 'Sports'])) {
                $error = "Core default category '" . htmlspecialchars($catName) . "' cannot be deleted to ensure system stability.";
            } else {
                // Check if any posts are using it
                $postsCountQuery = $db->query("SELECT COUNT(*) as cnt FROM posts WHERE category = ?", "s", [$catName]);
                $postCount = $postsCountQuery ? ($postsCountQuery->fetch_assoc()['cnt'] ?? 0) : 0;
                
                if ($postCount > 0) {
                    $error = "Cannot delete category '" . htmlspecialchars($catName) . "' as it is currently associated with " . $postCount . " bulletin posts. Re-assign those articles first.";
                } else {
                    $db->query("DELETE FROM categories WHERE id = ?", "i", [$catId]);
                    $success = "Category '" . htmlspecialchars($catName) . "' has been pruned successfully.";
                }
            }
        }
    }
}

// Fetch categories
$categoriesRes = $db->query("SELECT c.*, (SELECT COUNT(*) FROM posts WHERE category = c.name) as post_count FROM categories c ORDER BY c.name ASC");
$categories = [];
if ($categoriesRes) {
    while ($c = $categoriesRes->fetch_assoc()) {
        $categories[] = $c;
    }
}

Layout::renderHeader('System News Categories', 'Categories');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Create, organize, and administer target news sections dynamically. Dynamic categories instantly populate composing editors.
            </p>
        </div>

        <!-- Messages feedback -->
        <?php if (!empty($error)): ?>
            <div class="p-4 rounded-lg text-xs font-mono bg-red-955/40 border border-red-500/30 text-red-200">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="p-4 rounded-lg text-xs font-mono bg-emerald-955/40 border border-emerald-500/30 text-emerald-250 font-bold">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Create Category Card -->
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl h-fit space-y-4">
                <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919] border-b border-slate-900 pb-2">// CREATE NEW CATEGORY</h3>
                
                <?php if ($adminUser['role'] === 'admin'): ?>
                    <form method="POST" class="space-y-4">
                        <?php echo CSRF::renderField(); ?>
                        <input type="hidden" name="action" value="create_category">
                        
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-mono uppercase text-slate-400">Category Identifier</label>
                            <input 
                                type="text" 
                                name="category_name" 
                                placeholder="e.g. Science, Economy, Gaming"
                                required 
                                class="bg-slate-950 border border-slate-800 text-xs px-3.5 py-2 w-full rounded-md focus:outline-none focus:border-[#bb1919] text-white placeholder-slate-700"
                            >
                        </div>

                        <button 
                            type="submit" 
                            class="w-full bg-[#bb1919] hover:bg-[#801111] text-white text-[10px] font-bold uppercase py-2.5 tracking-wider font-mono rounded-md transition duration-150 cursor-pointer"
                        >
                            Publish Category
                        </button>
                    </form>
                <?php else: ?>
                    <div class="p-4 bg-amber-950/20 border border-amber-550/30 rounded-lg text-xs text-amber-200 leading-relaxed font-sans space-y-1.5">
                        <strong class="text-amber-400 font-bold block uppercase text-[10px] tracking-wide font-mono">Role Limitation</strong>
                        <p>Your account is registered as <em><?php echo htmlspecialchars($adminUser['role']); ?></em>. Per system constraints, <strong>only administrators</strong> can create or manage categories.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Category Ledger list -->
            <div class="lg:col-span-2 bg-slate-900/40 border border-slate-900 p-6 rounded-xl space-y-4">
                <h2 class="font-bold text-sm text-slate-200 border-b border-slate-900 pb-2">Active Categories Repository</h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-900 text-slate-400 font-mono text-[10px] uppercase tracking-wider bg-slate-950/20">
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3 px-4">Name / Namespace</th>
                                <th class="py-3 px-4">Associated Articles</th>
                                <th class="py-3 px-4">Created On</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/40 font-sans">
                            <?php foreach ($categories as $c): ?>
                                <tr class="hover:bg-slate-900/20 transition">
                                    <td class="py-4 px-4 font-mono text-slate-500">c_<?php echo $c['id']; ?></td>
                                    <td class="py-4 px-4 font-bold text-slate-200"><?php echo htmlspecialchars($c['name']); ?></td>
                                    <td class="py-4 px-4 font-mono text-slate-400 font-semibold"><?php echo intval($c['post_count']); ?> publications</td>
                                    <td class="py-4 px-4 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i', strtotime($c['created_at'])); ?></td>
                                    <td class="py-4 px-4 text-right">
                                        <?php if (in_array($c['name'], ['World', 'Business', 'Technology', 'Sports'])): ?>
                                            <span class="text-[9px] uppercase font-bold text-slate-500 font-mono px-2 py-0.5 border border-slate-800/80 bg-slate-950/40 rounded-md select-none">Core Default</span>
                                        <?php else: ?>
                                            <?php if ($adminUser['role'] === 'admin'): ?>
                                                <form method="POST" onsubmit="return confirm('Prune custom category &quot;<?php echo htmlspecialchars($c['name']); ?>&quot; from system ledgers?');" class="inline">
                                                    <?php echo CSRF::renderField(); ?>
                                                    <input type="hidden" name="category_id" value="<?php echo $c['id']; ?>">
                                                    <input type="hidden" name="action" value="delete_category">
                                                    <button type="submit" class="text-red-400 hover:text-red-500 font-bold hover:underline transition">Delete</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-slate-600 font-mono text-[10px] uppercase">Locked</span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

<?php
Layout::renderFooter();
?>
