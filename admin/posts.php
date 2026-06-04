<?php
/**
 * NeuralPress - Admin Post Archives
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Archives</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5"><span class="bg-white text-black px-1 leading-none font-bold">N</span> NeuralPress CMS</span>
        <a href="/admin/dashboard.php" class="text-xs text-red-400 hover:underline">Back to Overview</a>
    </header>
    <main class="max-w-7xl mx-auto px-6 py-8 w-full space-y-6 flex-grow">
        <h1 class="sidebar-title font-bold text-lg">Committed Article Ledger</h1>
        <div class="bg-white border p-6 rounded shadow-sm">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b text-left text-slate-400 uppercase font-mono text-[10px]">
                        <th class="pb-2">Headline</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Trust %</th>
                        <th class="pb-2">Reads</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()): ?>
                        <tr class="border-b last:border-0 hover:bg-slate-50">
                            <td class="py-3 font-semibold"><?php echo htmlspecialchars($row['title']); ?></td>
                            <td class="py-3 font-mono text-[10px] uppercase font-bold text-gray-500"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td class="py-3 font-mono font-bold text-emerald-600"><?php echo intval($row['trust_score']); ?>%</td>
                            <td class="py-3 font-mono"><?php echo number_format($row['views']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
