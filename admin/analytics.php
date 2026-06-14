<?php
/**
 * NeuralPress - Performance and monetization analytics dashboards
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();

$viewsRow = $db->query("SELECT SUM(views) as total_views FROM posts")->fetch_assoc();
$totalViews = $viewsRow['total_views'] ?? 0;

$impsRow = $db->query("SELECT COUNT(*) as total_imps FROM ad_events WHERE event_type = 'impression'")->fetch_assoc();
$totalImps = $impsRow['total_imps'] ?? 0;

$clicksRow = $db->query("SELECT COUNT(*) as total_clicks FROM ad_events WHERE event_type = 'click'")->fetch_assoc();
$totalClicks = $clicksRow['total_clicks'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Traffic Analytics</title>
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
        <h1 class="sidebar-title font-bold text-lg">System Traffic Analytics</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white border p-5 rounded shadow-sm">
                <span class="text-[9px] text-[#bb1919] font-mono uppercase block font-extrabold">Accumulated Node Reads</span>
                <span class="text-2xl font-black text-slate-800 font-mono mt-2 block"><?php echo number_format($totalViews); ?> reads</span>
            </div>
            <div class="bg-white border p-5 rounded shadow-sm">
                <span class="text-[9px] text-[#bb1919] font-mono uppercase block font-extrabold">Sponsored Impressions</span>
                <span class="text-2xl font-black text-slate-800 font-mono mt-2 block"><?php echo number_format($totalImps); ?> impressions</span>
            </div>
            <div class="bg-white border p-5 rounded shadow-sm">
                <span class="text-[9px] text-[#bb1919] font-mono uppercase block font-extrabold">Sponsored Clicks</span>
                <span class="text-2xl font-black text-slate-800 font-mono mt-2 block"><?php echo number_format($totalClicks); ?> clicks</span>
            </div>
        </div>
    </main>
</body>
</html>
