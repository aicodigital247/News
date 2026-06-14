<?php
/**
 * NeuralPress - Unified Admin Layout Template (BBC/Advanced SaaS Style)
 */
namespace NeuralPress\Admin;

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

class Layout {
    public static function renderHeader($title = 'Admin Portal', $activeTab = 'Overview') {
        Auth::startSession();
        $user = Auth::getCurrentUser();
        
        $db = Database::getInstance();
        $pendingReview = 0;
        $flaggedCount = 0;
        
        try {
            $pendingRes = $db->query("SELECT COUNT(*) as cnt FROM posts WHERE status = 'pending_review'");
            if ($pendingRes) {
                $pendingReview = $pendingRes->fetch_assoc()['cnt'] ?? 0;
            }
            $flaggedRes = $db->query("SELECT COUNT(*) as cnt FROM posts WHERE status = 'flagged'");
            if ($flaggedRes) {
                $flaggedCount = $flaggedRes->fetch_assoc()['cnt'] ?? 0;
            }
        } catch (\Exception $e) {
            // Decouple fallback
        }
        
        $username = $user ? $user['username'] : 'Operator';
        $userRole = $user ? $user['role'] : 'staff';
        ?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - NeuralPress Admin</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .font-display {
            font-family: 'Space+Grotesk', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
</head>
<body class="h-full text-slate-100 flex flex-col antialiased">
    <!-- Top Global Service Control Strip -->
    <header class="bg-slate-950 border-b border-slate-900 h-16 flex items-center justify-between px-6 shrink-0 relative z-30">
        <div class="flex items-center gap-6">
            <span class="text-white font-black tracking-tighter text-lg flex items-center gap-2 select-none">
                <span class="bg-[#bb1919] text-white px-2 py-0.5 leading-none font-bold rounded-sm shadow-sm transition hover:scale-105">N</span> 
                <span class="font-display tracking-[0.1em] font-extrabold text-[#bb1919]">NEURAL</span>
                <span class="text-xs font-mono font-normal text-slate-500 uppercase border-l border-slate-800 pl-3">PRESS CMS / CORE-X</span>
            </span>
        </div>
        
        <div class="flex items-center gap-6 text-xs">
            <!-- Systems Active Log -->
            <div class="hidden lg:flex items-center gap-2 bg-slate-900/60 border border-slate-850 px-3 py-1.5 rounded-md text-slate-400 font-mono">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>VERIFICATION AGENT: <strong class="text-emerald-400 font-bold uppercase">ACTIVE</strong></span>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <span class="block text-slate-200 font-bold leading-tight font-display"><?php echo htmlspecialchars($username); ?></span>
                    <span class="block text-[10px] font-mono text-slate-400 uppercase tracking-widest"><?php echo htmlspecialchars($userRole); ?> SECURE NODES</span>
                </div>
                <div class="h-8 w-8 bg-gradient-to-tr from-[#bb1919]/80 to bg-black border border-[#bb1919]/50 rounded-full flex items-center justify-center font-display font-black text-sm text-white shadow-sm shadow-[#bb1919]/20">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <a href="/admin/logout" class="bg-slate-900 hover:bg-[#bb1919]/10 text-slate-300 hover:text-[#bb1919] border border-slate-800 hover:border-[#bb1919]/50 font-mono text-[10px] uppercase font-bold px-3 py-1.5 rounded-md transition duration-150">Sign Out</a>
            </div>
        </div>
    </header>

    <div class="flex-grow flex flex-col lg:flex-row overflow-hidden">
        <!-- Persistent Left Admin Sidebar -->
        <aside class="w-full lg:w-64 bg-slate-950 border-r border-slate-900 flex flex-col shrink-0 overflow-y-auto max-h-screen">
            <div class="p-6 border-b border-slate-900/60 bg-slate-950 flex flex-col gap-1.5">
                <span class="text-[9px] font-mono uppercase tracking-widest text-[#bb1919] font-bold">// SYSTEM CONTROL INTERACTIVE</span>
                <h2 class="text-sm font-display font-medium text-slate-300">Management Panel</h2>
            </div>
            
            <nav class="p-4 space-y-1.5 flex-grow">
                <!-- Nav Action Block 1: Main Overview -->
                <a href="/admin/dashboard" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Overview' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Overview' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Overview Metrics
                    </span>
                    <span class="text-[9px] opacity-40 font-bold font-mono">M01</span>
                </a>

                <!-- Nav Action Block 2: Posts -->
                <a href="/admin/posts" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Posts' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Posts' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Post Ledger
                    </span>
                    <span class="text-[9px] opacity-40 font-bold font-mono">M02</span>
                </a>

                <!-- Nav Action Block 3: Categories -->
                <a href="/admin/categories" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Categories' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Categories' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Topic Categories
                    </span>
                    <span class="text-[9px] opacity-40 font-bold font-mono">M03</span>
                </a>

                <!-- Nav Action Block 4: Review Queue -->
                <a href="/admin/review_queue" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Review' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Review' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Review Queue
                    </span>
                    <?php if ($pendingReview > 0): ?>
                        <span class="bg-[#bb1919] text-white text-[9px] font-bold font-mono px-2 py-0.5 rounded-full animate-pulse shadow-sm "><?php echo $pendingReview; ?></span>
                    <?php else: ?>
                        <span class="text-[9px] opacity-20 font-mono">0</span>
                    <?php endif; ?>
                </a>

                <!-- Nav Action Block 5: Flagged Posts -->
                <a href="/admin/flagged_posts" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Flagged' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Flagged' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Flagged Risks
                    </span>
                    <?php if ($flaggedCount > 0): ?>
                        <span class="bg-[#bb1919] text-white text-[9px] font-bold font-mono px-2 py-0.5 rounded-full shadow-sm"><?php echo $flaggedCount; ?></span>
                    <?php else: ?>
                        <span class="text-[9px] opacity-20 font-mono">0</span>
                    <?php endif; ?>
                </a>

                <?php if ($userRole === 'admin'): ?>
                <!-- Nav Action Block 6: Users & Roles (Admin Only) -->
                <a href="/admin/users" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Users' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Users' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Operators
                    </span>
                    <span class="text-[9px] opacity-40 font-bold font-mono">M06</span>
                </a>
                <?php endif; ?>

                <!-- Nav Action Block 7: Creator Payouts -->
                <a href="/admin/withdrawals" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Withdrawals' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Withdrawals' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Payout Logs
                    </span>
                    <span class="text-[9px] opacity-40 font-bold font-mono">M07</span>
                </a>

                <!-- Nav Action Block 8: Ad Monetisation -->
                <a href="/admin/ads" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Ads' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Ads' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Monetisation
                    </span>
                    <span class="text-[9px] opacity-40 font-bold font-mono">M08</span>
                </a>

                <?php if ($userRole === 'admin'): ?>
                <!-- Nav Action Block 9: AI Control Portal (Admin Only) -->
                <a href="/admin/ai_control" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'AI' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'AI' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        AI Steering
                    </span>
                    <span class="text-[9px] font-bold text-[#bb1919] font-mono px-1 rounded bg-[#bb1919]/5">LIVE</span>
                </a>
                <?php endif; ?>

                <!-- Nav Action Block 10: Global Settings -->
                <a href="/admin/settings" class="flex items-center justify-between py-2.5 px-4 rounded-lg font-mono text-xs uppercase tracking-wider transition-all duration-150 group <?php echo $activeTab === 'Settings' ? 'bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 font-bold shadow-sm' : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'; ?>">
                    <span class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo $activeTab === 'Settings' ? 'bg-[#bb1919]' : 'bg-slate-700 group-hover:bg-slate-400'; ?>"></span>
                        Settings
                    </span>
                    <span class="text-[9px] opacity-40 font-bold font-mono">M10</span>
                </a>
            </nav>

            <div class="p-4 border-t border-slate-900 bg-slate-950">
                <a href="/" target="_blank" class="block w-full text-center bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-100 py-2.5 select-none rounded-lg text-[10px] tracking-widest font-extrabold uppercase font-mono transition duration-150">
                    PUBLIC MAIN SITE
                </a>
            </div>
        </aside>

        <!-- Main Workspace stage -->
        <main class="flex-1 overflow-y-auto bg-slate-950 p-6 lg:p-10 space-y-8 select-text">
            <!-- Breadcrumbs / Action strip -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between border-b border-slate-900 pb-6 gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-[10px] font-mono text-slate-500 uppercase tracking-widest">
                        <span>Portal System</span>
                        <span>/</span>
                        <span class="text-slate-400"><?php echo htmlspecialchars($activeTab); ?></span>
                    </div>
                    <h1 class="text-2xl font-display font-bold bg-clip-text text-transparent bg-gradient-to-r from-white via-white to-slate-400 tracking-tight flex items-center gap-2">
                        <?php echo htmlspecialchars($title); ?>
                    </h1>
                </div>
                
                <div class="flex items-center gap-2.5 text-xs">
                    <div class="text-right hidden sm:block">
                        <span class="block text-slate-400 font-mono text-[10px]" id="liveGmtClock">UTC DATE CONTROLLER</span>
                        <span class="block font-bold text-slate-200 font-mono text-[11px]"><?php echo date('D d M Y, H:i'); ?> GMT</span>
                    </div>
                </div>
            </div>
        <?php
    }

    public static function renderFooter() {
        ?>
        </main>
    </div>
    
    <footer class="bg-black/45 border-t border-slate-900 text-center py-4 text-[10px] font-mono text-slate-500 relative z-20 shrink-0">
        © 2026 NeuralPress AI BBC Model CMS Engine • High Speed MySQLi Bindings Secure Architecture • All rights reserved
    </footer>
</body>
</html>
        <?php
    }
}
