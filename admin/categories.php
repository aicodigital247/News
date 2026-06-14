<?php
/**
 * NeuralPress - Admin Category Management
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Core\CSRF;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage News Categories - NeuralPress Admin</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5 select-none font-sans">
            <span class="bg-white text-black px-1 leading-none font-bold">N</span> NeuralPress CMS Admin
        </span>
        <div class="flex items-center gap-4 text-xs">
            <span>Logged in as: <strong><?php echo htmlspecialchars($adminUser['username']); ?></strong> (<?php echo htmlspecialchars($adminUser['role']); ?>)</span>
            <span class="text-gray-700">|</span>
            <a href="/admin/logout" class="text-red-400 hover:underline">Sign Out</a>
        </div>
    </header>

    <div class="flex-grow flex flex-col md:flex-row max-w-7xl mx-auto w-full px-6 py-8 gap-8">
        <!-- Dashboard Sidebar Navigation -->
        <nav class="w-full md:w-56 shrink-0 space-y-1 bg-white border border-gray-200 p-4 rounded text-xs font-bold uppercase tracking-wider h-fit">
            <a href="/admin/dashboard" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Overview</a>
            <a href="/admin/posts" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Post Archives</a>
            <a href="/admin/categories" class="block py-2 px-3 bg-red-50 text-[#bb1919] rounded">Manage Categories</a>
            <a href="/admin/review_queue" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded flex items-center justify-between">Review Queue</a>
            <a href="/admin/flagged_posts" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded flex items-center justify-between">Flagged Risks</a>
            <a href="/admin/users" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Users & Roles</a>
            <a href="/admin/withdrawals" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Creator Payouts</a>
            <a href="/admin/ads" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Ad Monetisation</a>
            <a href="/admin/ai_control" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">AI Control Portal</a>
            <a href="/admin/settings" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Global Settings</a>
            <div class="pt-6 border-t border-dashed mt-4">
                <a href="/creator/dashboard" class="block text-center bg-emerald-600 text-white-10 text-emerald-100 hover:bg-emerald-700 py-1.5 text-[9px] tracking-widest font-extrabold rounded">GO TO CREATOR HUB</a>
            </div>
        </nav>

        <!-- Main Workspace -->
        <main class="flex-1 space-y-6">
            <div class="space-y-1">
                <h1 class="sidebar-title font-black text-lg uppercase">System News Categories</h1>
                <p class="text-xs text-slate-500 font-light">Create, organize, and administer target news sections dynamically. Dynamic categories instantly populate composing editors.</p>
            </div>

            <!-- Messages feedback -->
            <?php if (!empty($error)): ?>
                <div class="p-4 rounded text-xs bg-red-50 text-red-750 border border-red-200">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="p-4 rounded text-xs bg-emerald-50 text-emerald-700 border border-emerald-250 font-bold">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Create Category Card -->
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm h-fit">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919] border-b pb-1.5 mb-4">// CREATE NEW CATEGORY</h3>
                    
                    <?php if ($adminUser['role'] === 'admin'): ?>
                        <form method="POST" class="space-y-4">
                            <?php echo CSRF::renderField(); ?>
                            <input type="hidden" name="action" value="create_category">
                            
                            <div class="space-y-1">
                                <label class="block text-[10px] font-mono uppercase text-slate-600">Category Identifier</label>
                                <input 
                                    type="text" 
                                    name="category_name" 
                                    placeholder="e.g. Science, Economy, Entertainment"
                                    required 
                                    class="bg-white border border-slate-350 text-xs px-3 py-1.5 w-full rounded focus:outline-none focus:border-red-700"
                                >
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-[#bb1919] hover:bg-[#801111] text-white text-[11px] font-bold uppercase py-2 tracking-wide font-mono transition cursor-pointer"
                            >
                                Publish Category
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="p-3 bg-amber-50 border border-amber-205 rounded text-xs text-amber-800 leading-relaxed font-sans">
                            <strong>Role Limitation:</strong> Your account is registered as <em><?php echo htmlspecialchars($adminUser['role']); ?></em>. Per system constraints, <strong>only administrators</strong> can create or manage categories.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Category Ledger list -->
                <div class="lg:col-span-2 bg-white border border-gray-200 p-6 rounded shadow-sm space-y-4">
                    <h2 class="font-bold text-sm text-slate-800 border-b pb-2">Active Categories Repository</h2>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-slate-400 font-mono pb-2">
                                    <th class="pb-2">ID</th>
                                    <th class="pb-2">Name / Namespace</th>
                                    <th class="pb-2">Associated Articles</th>
                                    <th class="pb-2">Created On</th>
                                    <th class="pb-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-150">
                                <?php foreach ($categories as $c): ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3 font-mono text-slate-500">c_<?php echo $c['id']; ?></td>
                                        <td class="py-3 font-bold text-slate-900"><?php echo htmlspecialchars($c['name']); ?></td>
                                        <td class="py-3 font-mono text-slate-700 font-semibold"><?php echo intval($c['post_count']); ?> publications</td>
                                        <td class="py-3 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i', strtotime($c['created_at'])); ?></td>
                                        <td class="py-3 text-right">
                                            <?php if (in_array($c['name'], ['World', 'Business', 'Technology', 'Sports'])): ?>
                                                <span class="text-[9px] uppercase font-bold text-slate-400 font-mono px-2 py-0.5 border border-slate-200 bg-slate-50 rounded">Core Default</span>
                                            <?php else: ?>
                                                <?php if ($adminUser['role'] === 'admin'): ?>
                                                    <form method="POST" onsubmit="return confirm('Prune custom category &quot;<?php echo htmlspecialchars($c['name']); ?>&quot; from system ledgers?');" class="inline">
                                                        <?php echo CSRF::renderField(); ?>
                                                        <input type="hidden" name="category_id" value="<?php echo $c['id']; ?>">
                                                        <input type="hidden" name="action" value="delete_category">
                                                        <button type="submit" class="text-red-650 hover:text-red-800 font-bold hover:underline">Delete</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-gray-400 italic">Locked</span>
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
        </main>
    </div>
</body>
</html>
