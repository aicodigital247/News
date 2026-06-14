<?php
/**
 * NeuralPress - Admin CMS Dashboard
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

Auth::checkRole(['admin', 'editor']);
$user = Auth::getCurrentUser();

$db = Database::getInstance();
// Compute stats
$totalPosts = $db->query("SELECT COUNT(*) as cnt FROM posts")->fetch_assoc()['cnt'] ?? 0;
$pendingReview = $db->query("SELECT COUNT(*) as cnt FROM posts WHERE status = 'pending_review'")->fetch_assoc()['cnt'] ?? 0;
$flaggedCount = $db->query("SELECT COUNT(*) as cnt FROM posts WHERE status = 'flagged'")->fetch_assoc()['cnt'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NeuralPress Administrative Dashboard</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5 select-none">
            <span class="bg-white text-black px-1 leading-none font-bold">N</span> NeuralPress CMS
        </span>
        <div class="flex items-center gap-4 text-xs">
            <span>Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong> (<?php echo htmlspecialchars($user['role']); ?>)</span>
            <span class="text-gray-700">|</span>
            <a href="/admin/logout" class="text-red-400 hover:underline">Sign Out</a>
        </div>
    </header>

    <div class="flex-grow flex flex-col md:flex-row max-w-7xl mx-auto w-full px-6 py-8 gap-8">
        <!-- Dashboard Sidebar Navigation -->
        <nav class="w-full md:w-56 shrink-0 space-y-1 bg-white border border-gray-200 p-4 rounded text-xs font-bold uppercase tracking-wider">
            <a href="/admin/dashboard" class="block py-2 px-3 bg-red-50 text-[#bb1919] rounded">Overview</a>
            <a href="/admin/posts" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Post Archives</a>
            <a href="/admin/review_queue" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded flex items-center justify-between">Review Queue <span class="bg-red-700 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full"><?php echo $pendingReview; ?></span></a>
            <a href="/admin/flagged_posts" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded flex items-center justify-between">Flagged Risks <span class="bg-black text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full"><?php echo $flaggedCount; ?></span></a>
            <a href="/admin/users" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Users & Roles</a>
            <a href="/admin/ads" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Ad Monetisation</a>
            <a href="/admin/ai_control" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded text-[#bb1919]">AI Control Portal</a>
            <a href="/admin/settings" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Global Settings</a>
            <div class="pt-6">
                <a href="/" class="block text-center bg-[#bb1919] text-white py-2 select-none text-[10px] tracking-widest font-extrabold hover:bg-[#801111]">VIEW PUBLIC SITE</a>
            </div>
        </nav>

        <!-- Main Workspace -->
        <main class="flex-1 space-y-6">
            <h1 class="sidebar-title font-bold text-lg">System Metrics Overview</h1>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm relative">
                    <span class="text-[10px] text-gray-400 font-mono uppercase block">TOTAL COMMITTED ARTICLES</span>
                    <span class="text-3xl font-black text-slate-800 font-mono block mt-1"><?php echo $totalPosts; ?></span>
                </div>
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm relative">
                    <span class="text-[10px] text-gray-400 font-mono uppercase block">AWAITING EDITORIAL REVIEW</span>
                    <span class="text-3xl font-black text-amber-500 font-mono block mt-1"><?php echo $pendingReview; ?></span>
                </div>
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm relative">
                    <span class="text-[10px] text-gray-400 font-mono uppercase block">AI RISK FLAGGED BULLETINS</span>
                    <span class="text-3xl font-black text-red-600 font-mono block mt-1"><?php echo $flaggedCount; ?></span>
                </div>
            </div>

            <div class="bg-white border border-gray-200 p-6 rounded shadow-sm space-y-4">
                <h3 class="font-bold text-sm border-b pb-2 text-slate-800">Recent Security Audit Logs</h3>
                <div class="text-xs font-mono space-y-2 text-slate-500">
                    <p>[2027-06-04 17:01] INFO: Database initialized correctly using Prepared parameters.</p>
                    <p>[2027-06-04 17:03] WARNING: Translation cache query bypass throttled from IP: 127.0.0.1 via API Key lock.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
