<?php
/**
 * NeuralPress - Flagged Post Alerts
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE status = 'flagged' ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NeuralPress Security Flag alerts</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
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
        <h1 class="sidebar-title border-l-4 border-black pl-2 font-bold text-lg text-red-700">Flagged Risk Alerts</h1>
        <div class="bg-white border p-6 rounded shadow-sm">
            <?php if (!$res || $res->num_rows === 0): ?>
                <p class="text-xs text-slate-500 font-light">No risk alerts triggered on active broadcast nodes. System sanitization nominal.</p>
            <?php else: ?>
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b text-left text-slate-400 uppercase font-mono text-[10px]">
                            <th class="pb-2">Headline</th>
                            <th class="pb-2">Trust Rating</th>
                            <th class="pb-2 font-light">Verification Trigger</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr class="border-b last:border-0 hover:bg-slate-50 hover:text-red-750 transition">
                                <td class="py-3 font-semibold text-[#bb1919]"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td class="py-3 font-mono font-bold text-red-650"><?php echo intval($row['trust_score']); ?>%</td>
                                <td class="py-3 text-slate-500 font-light"><?php echo htmlspecialchars($row['verification_reason']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
