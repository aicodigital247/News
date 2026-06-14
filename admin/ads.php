<?php
/**
 * NeuralPress - Ad Monetization and optimization control
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

Auth::checkRole(['admin']);
$db = Database::getInstance();
$res = $db->query("SELECT * FROM ads ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monetization Console</title>
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
        <h1 class="sidebar-title font-bold text-lg">Ad Monetisation Dashboard</h1>
        <div class="bg-white border p-6 rounded shadow-sm">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b text-left text-slate-400 uppercase font-mono text-[10px]">
                        <th class="pb-2">Campaign Name</th>
                        <th class="pb-2">Slot Position</th>
                        <th class="pb-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()): ?>
                        <tr class="border-b last:border-0 hover:bg-slate-50">
                            <td class="py-3 font-semibold text-slate-700"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td class="py-3 font-mono text-[11px] text-slate-500"><?php echo htmlspecialchars($row['slot_position']); ?></td>
                            <td class="py-3">
                                <span class="bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($row['status']); ?></span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
