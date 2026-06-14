<?php
/**
 * NeuralPress - Admin Post Archives
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$countRes = $db->query("SELECT COUNT(*) as total FROM posts");
$total = $countRes ? ($countRes->fetch_assoc()['total'] ?? 0) : 0;
$totalPages = max(1, ceil($total / $limit));

$res = $db->query("SELECT * FROM posts ORDER BY id DESC LIMIT ? OFFSET ?", "ii", [$limit, $offset]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Archives</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5"><span class="bg-white text-black px-1 leading-none font-bold">N</span> NeuralPress CMS</span>
        <a href="/admin/dashboard" class="text-xs text-red-400 hover:underline">Back to Overview</a>
    </header>
    <main class="max-w-7xl mx-auto px-6 py-8 w-full space-y-6 flex-grow">
        <div class="flex items-center justify-between">
            <h1 class="sidebar-title font-bold text-lg">Committed Article Ledger</h1>
            <a href="/admin/write.php" class="bg-[#bb1919] hover:bg-[#801111] text-white font-bold text-xs px-4 py-2 rounded shadow-sm inline-flex items-center gap-1.5 transition">
                + Compose New Article
            </a>
        </div>

        <?php
        $message = '';
        $messageType = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            $messageType = $_SESSION['message_type'] ?? 'success';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        if (!empty($message)):
        ?>
            <div class="p-4 mb-4 rounded text-xs <?php echo $messageType === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border p-6 rounded shadow-sm">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b text-left text-slate-400 uppercase font-mono text-[10px]">
                        <th class="pb-2">Headline</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Trust %</th>
                        <th class="pb-2">Reads</th>
                        <th class="pb-2 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()): ?>
                        <tr class="border-b last:border-0 hover:bg-slate-50">
                            <td class="py-3 font-semibold"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="py-3 font-mono text-[10px] uppercase font-bold text-gray-500"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td class="py-3 font-mono font-bold text-emerald-600"><?php echo intval($row['trust_score']); ?>%</td>
                            <td class="py-3 font-mono"><?php echo number_format($row['views']); ?></td>
                            <td class="py-3 text-right">
                                <a href="/admin/write.php?id=<?php echo $row['id']; ?>" class="text-indigo-600 hover:text-indigo-900 font-bold hover:underline">Edit Natively</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
                <div class="flex flex-col sm:flex-row items-center justify-between border-t border-gray-100 pt-4 mt-4 gap-2">
                    <span class="text-xs text-gray-500">Showing page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $total; ?> total committed publications)</span>
                    <div class="inline-flex gap-1.5">
                        <a href="?page=<?php echo max(1, $page - 1); ?>" class="px-3 py-1.5 border rounded font-mono text-[11px] font-bold tracking-wider <?php echo ($page <= 1) ? 'text-gray-300 border-gray-150 cursor-not-allowed pointer-events-none' : 'text-slate-800 hover:bg-slate-50 bg-white'; ?>">
                            &larr; PREV
                        </a>
                        <a href="?page=<?php echo min($totalPages, $page + 1); ?>" class="px-3 py-1.5 border rounded font-mono text-[11px] font-bold tracking-wider <?php echo ($page >= $totalPages) ? 'text-gray-300 border-gray-150 cursor-not-allowed pointer-events-none' : 'text-slate-800 hover:bg-slate-50 bg-white'; ?>">
                            NEXT &rarr;
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
