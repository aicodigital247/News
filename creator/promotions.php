<?php
/**
 * NeuralPress - Creator Promotions Campaign Center
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Core\CSRF;
use NeuralPress\Core\MonetizationEngine;

Auth::checkRole(['admin', 'editor', 'journalist']);
$user = Auth::getCurrentUser();
$userId = intval($user['id']);

$db = Database::getInstance();
$monetization = MonetizationEngine::getInstance();

$error = '';
$success = $_GET['success'] ?? '';

// Handle budget boost campaign activation from this view
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }
    $postId = intval($_POST['post_id'] ?? 0);
    $campaignName = trim($_POST['campaign_name'] ?? '');
    $budget = floatval($_POST['budget'] ?? 0);

    $balanceInfo = $monetization->getBalance($userId);

    if ($budget > floatval($balanceInfo['balance'])) {
        $error = "Insufficient funds in your current creator balance to fund this promo campaign budget. Earn more reads first!";
    } else {
        $res = $monetization->createPromotion($userId, $postId, $campaignName, $budget);
        if ($res === true) {
            // Deduct immediately
            $monetization->debitBalance($userId, $budget);
            $success = "Promotion campaign '" . htmlspecialchars($campaignName) . "' ($" . number_format($budget, 2) . ") was successfully launched!";
        } else {
            $error = $res;
        }
    }
}

// Fetch current creator finances
$balanceInfo = $monetization->getBalance($userId);

// Fetch all promotional campaigns created by this user
$promoRes = $db->query(
    "SELECT pr.*, p.title as post_title, p.category as post_category 
     FROM promotions pr 
     JOIN posts p ON pr.post_id = p.id 
     WHERE pr.user_id = ? 
     ORDER BY pr.created_at DESC", 
    "i", 
    [$userId]
);

$myPromotions = [];
if ($promoRes) {
    while ($r = $promoRes->fetch_assoc()) {
        $myPromotions[] = $r;
    }
}

// Fetch owned posts for the selector options
$postsRes = $db->query("SELECT id, title FROM posts WHERE author_id = ? ORDER BY created_at DESC", "i", [$userId]);
$myPosts = [];
if ($postsRes) {
    while ($p = $postsRes->fetch_assoc()) {
        $myPosts[] = $p;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Promotion Center - Creator Hub</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5 select-none font-sans">
            <span class="bg-[#bb1919] text-white px-1 leading-none font-bold">C</span> Creator Hub
        </span>
        <div class="flex items-center gap-4 text-xs">
            <span>Logged in as: <strong><?php echo htmlspecialchars($user['username']); ?></strong></span>
            <span class="text-gray-700">|</span>
            <a href="/admin/logout" class="text-red-400 hover:underline">Sign Out</a>
        </div>
    </header>

    <div class="flex-grow flex flex-col md:flex-row max-w-7xl mx-auto w-full px-6 py-8 gap-8">
        <!-- Sidebar Navigation -->
        <nav class="w-full md:w-56 shrink-0 space-y-1 bg-white border border-gray-200 p-4 rounded text-xs font-bold uppercase tracking-wider h-fit">
            <a href="/creator/dashboard" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Dashboard</a>
            <a href="/creator/write" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Compose Bulletin</a>
            <a href="/creator/withdrawals" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Withdrawals Log</a>
            <a href="/creator/promotions" class="block py-2 px-3 bg-red-50 text-[#bb1919] rounded">My Promotions</a>
            <div class="pt-6 border-t border-dashed mt-4">
                <a href="/" class="block text-center bg-[#bb1919] text-white py-2 select-none text-[10px] tracking-widest font-extrabold hover:bg-[#801111]">VIEW PUBLIC SITE</a>
            </div>
        </nav>

        <!-- Main Workspace -->
        <main class="flex-1 space-y-6">
            <div class="space-y-1.5 border-b pb-4">
                <h1 class="font-black text-xl text-slate-900 uppercase font-sans">NATIVE BULLETIN PROMOTIONS</h1>
                <p class="text-xs text-slate-500 font-light font-sans">Boost article impression flow on feeds. Active campaigns gain a **2.5x view multiplier ($15.00/CPM balance credit)**.</p>
            </div>

            <!-- Feedback -->
            <?php if (!empty($error)): ?>
                <div class="p-4 rounded text-xs bg-red-50 text-red-700 border border-red-200 font-medium">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="p-4 rounded text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 font-medium">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Quick Promote Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-200 p-5 rounded shadow-sm space-y-4">
                        <div class="border-b pb-2">
                            <span class="text-[10px] text-gray-400 font-mono uppercase block font-bold leading-none">CREATOR WALLET BALANCE</span>
                            <span class="text-2xl font-black text-emerald-600 font-mono block mt-1">
                                $<?php echo number_format($balanceInfo['balance'], 2); ?>
                            </span>
                        </div>
                        
                        <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-indigo-700">// LAUNCH PROMOTIONAL CAMPAIGN</h3>
                        
                        <form method="POST" class="space-y-3">
                            <?php echo CSRF::renderField(); ?>
                            
                            <div class="space-y-1">
                                <label class="block text-[10px] font-mono uppercase text-slate-600">Select Post to Boost</label>
                                <select 
                                    name="post_id" 
                                    required 
                                    class="bg-white border border-slate-350 text-xs px-3 py-1.5 w-full rounded focus:outline-none"
                                >
                                    <?php if (empty($myPosts)): ?>
                                        <option value="">No publications found</option>
                                    <?php else: ?>
                                        <?php foreach ($myPosts as $post): ?>
                                            <option value="<?php echo $post['id']; ?>">
                                                <?php echo htmlspecialchars(mb_substring($post['title'] ?? '', 0, 45)); ?>...
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="space-y-1">
                                <label class="block text-[10px] font-mono uppercase text-slate-600">Campaign Identifer Tag</label>
                                <input 
                                    type="text" 
                                    name="campaign_name" 
                                    placeholder="E.g. Business Boost Q3"
                                    required 
                                    class="bg-white border border-slate-350 text-xs px-3 py-1.5 w-full rounded focus:outline-none"
                                >
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] font-mono uppercase text-slate-600">Lock Funding Budget ($)</label>
                                <input 
                                    type="number" 
                                    name="budget" 
                                    step="1.00" 
                                    min="5.00"
                                    max="<?php echo floatval($balanceInfo['balance']); ?>"
                                    placeholder="Minimum $5.00"
                                    required 
                                    class="bg-white border border-slate-350 text-xs px-3 py-1.5 w-full rounded focus:outline-none"
                                >
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-indigo-700 hover:bg-indigo-900 text-white text-[11px] font-bold uppercase py-2 tracking-wide font-mono transition cursor-pointer"
                                <?php echo (floatval($balanceInfo['balance']) < 5.00) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>
                            >
                                Fund & Launch Campaign
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Campaigns List -->
                <div class="lg:col-span-2">
                    <div class="bg-white border border-gray-200 p-6 rounded shadow-sm space-y-4">
                        <h3 class="font-bold text-sm text-slate-800">My Promotion Campaigns</h3>

                        <?php if (empty($myPromotions)): ?>
                            <p class="text-xs text-slate-500 italic py-6">You have not launched any target promotion campaigns. Select any of your authored articles on the left to fund high-exposure delivery streams!</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-slate-400 font-mono pb-2">
                                            <th class="pb-2">Campaign Name</th>
                                            <th class="pb-2">Target Article</th>
                                            <th class="pb-2">Budget</th>
                                            <th class="pb-2">Spent Progress</th>
                                            <th class="pb-2">Launched</th>
                                            <th class="pb-2 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php foreach ($myPromotions as $promo): ?>
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="py-3 font-bold text-indigo-700"><?php echo htmlspecialchars($promo['campaign_name']); ?></td>
                                                <td class="py-3">
                                                    <span class="font-medium text-slate-800 block truncate max-w-xs"><?php echo htmlspecialchars($promo['post_title']); ?></span>
                                                    <span class="text-[9px] uppercase font-mono text-slate-400"><?php echo htmlspecialchars($promo['post_category']); ?></span>
                                                </td>
                                                <td class="py-3 font-mono font-bold">$<?php echo number_format($promo['budget'], 2); ?></td>
                                                <td class="py-3">
                                                    <div class="space-y-1">
                                                        <div class="flex justify-between items-center text-[10px] font-mono text-gray-500">
                                                            <span>$<?php echo number_format($promo['spent'], 2); ?> spent</span>
                                                            <span><?php echo intval(($promo['spent'] / $promo['budget']) * 100); ?>%</span>
                                                        </div>
                                                        <div class="w-24 bg-slate-100 h-1 rounded-full overflow-hidden">
                                                            <div class="bg-indigo-600 h-1" style="width: <?php echo min(100, ($promo['spent'] / $promo['budget']) * 100); ?>%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i', strtotime($promo['created_at'])); ?></td>
                                                <td class="py-3 text-right">
                                                    <?php if ($promo['status'] === 'active'): ?>
                                                        <span class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Active</span>
                                                    <?php elseif ($promo['status'] === 'paused'): ?>
                                                        <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Paused</span>
                                                    <?php else: ?>
                                                        <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Completed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
