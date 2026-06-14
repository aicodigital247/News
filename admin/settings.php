<?php
/**
 * NeuralPress - Main CMS configuration panel
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;

Auth::checkRole(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global CMS Settings</title>
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
    <main class="max-w-7xl mx-auto px-6 py-8 w-full space-y-6 flex-grow font-sans">
        <h1 class="sidebar-title font-bold text-lg">System Configurations</h1>
        <div class="bg-white border p-6 rounded shadow-sm space-y-4">
            <p class="text-xs text-slate-500 font-light">Audit configurations, logging intervals, and platform cache directories.</p>
            <div class="space-y-3 font-mono text-xs text-slate-600">
                <p><strong>PLATFORM NAME:</strong> NeuralPress Global Newsroom CMS</p>
                <p><strong>LOCALE CONTEXT:</strong> UTC (+00:00 GMT - British Standard Time)</p>
                <p><strong>ROUTING ENGINE:</strong> Pure PHP 8 Front-Controller with Apache HTACCESS bypass</p>
            </div>
        </div>
    </main>
</body>
</html>
