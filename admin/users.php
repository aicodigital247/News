<?php
/**
 * NeuralPress - User administration
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

Auth::checkRole(['admin']);
$db = Database::getInstance();
$res = $db->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users Administrative List</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5"><span class="bg-white text-black px-1 leading-none font-bold">N</span> NeuralPress CMS</span>
        <a href="/admin/dashboard" class="text-xs text-red-300 hover:underline">Back to Overview</a>
    </header>
    <main class="max-w-7xl mx-auto px-6 py-8 w-full space-y-6 flex-grow font-sans">
        <h1 class="sidebar-title font-bold text-lg">System Operator Directory</h1>
        <div class="bg-white border p-6 rounded shadow-sm">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b text-left text-slate-400 uppercase font-mono text-[10px]">
                        <th class="pb-2">Username</th>
                        <th class="pb-2">Email</th>
                        <th class="pb-2">Authorized Role</th>
                        <th class="pb-2">Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $res->fetch_assoc()): ?>
                        <tr class="border-b last:border-0 hover:bg-slate-50">
                            <td class="py-3 font-semibold"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td class="py-3 text-slate-500"><?php echo htmlspecialchars($row['email']); ?></td>
                            <td class="py-3 font-mono font-bold uppercase text-[#bb1919]"><?php echo htmlspecialchars($row['role']); ?></td>
                            <td class="py-3 font-mono text-slate-400"><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
