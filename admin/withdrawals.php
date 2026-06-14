<?php
/**
 * NeuralPress - Admin Creator Payout Management Portal
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Core\CSRF;
use NeuralPress\Core\MonetizationEngine;

Auth::checkRole(['admin', 'editor']);
$adminUser = Auth::getCurrentUser();

$db = Database::getInstance();
$monetization = MonetizationEngine::getInstance();

$error = '';
$success = '';

// Handle Approved / Rejected decisions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }

    $withdrawalId = intval($_POST['withdrawal_id'] ?? 0);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $ok = $monetization->updateWithdrawalStatus($withdrawalId, 'approved');
        if ($ok) {
            $success = "Payout request cleared and balance deducted successfully!";
        } else {
            $error = "Authorization failed. Payout request was already processed or balance was insufficient.";
        }
    } elseif ($action === 'reject') {
        $ok = $monetization->updateWithdrawalStatus($withdrawalId, 'rejected');
        if ($ok) {
            $success = "Payout request rejected and returned to creator pool.";
        } else {
            $error = "Database rejected operation. Request status could be finalized already.";
        }
    }
}

// Compute aggregate metrics
$pendingCount = $db->query("SELECT COUNT(*) as cnt FROM withdrawals WHERE status = 'pending'")->fetch_assoc()['cnt'] ?? 0;
$pendingVolume = $db->query("SELECT SUM(amount) as sum FROM withdrawals WHERE status = 'pending'")->fetch_assoc()['sum'] ?? 0.00;
$totalCleared = $db->query("SELECT SUM(amount) as sum FROM withdrawals WHERE status = 'approved'")->fetch_assoc()['sum'] ?? 0.00;

// Fetch pending withdrawals
$pendingWithdrawalsRes = $db->query("
    SELECT w.*, u.username, u.email 
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    WHERE w.status = 'pending' 
    ORDER BY w.created_at ASC
");
$pendingWithdrawals = [];
if ($pendingWithdrawalsRes) {
    while ($r = $pendingWithdrawalsRes->fetch_assoc()) {
        $pendingWithdrawals[] = $r;
    }
}

// Fetch cleared withdrawals
$clearedWithdrawalsRes = $db->query("
    SELECT w.*, u.username, u.email 
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    WHERE w.status IN ('approved', 'rejected') 
    ORDER BY w.updated_at DESC 
    LIMIT 25
");
$clearedWithdrawals = [];
if ($clearedWithdrawalsRes) {
    while ($r = $clearedWithdrawalsRes->fetch_assoc()) {
        $clearedWithdrawals[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Creator Payout Settlements - NeuralPress Admin</title>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-900 flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-black text-white h-14 flex items-center justify-between px-6 shrink-0 shadow-md">
        <span class="font-black tracking-tighter text-sm flex items-center gap-1.5 select-none font-sans">
            <span class="bg-white text-black px-1 leading-none font-bold">N</span> NeuralPress CMS Admin
        </span>
        <div class="flex items-center gap-4 text-xs">
            <span>Logged in as: <strong><?php echo htmlspecialchars($adminUser['username']); ?></strong> (<?php echo htmlspecialchars($adminUser['role']); ?>)</span>
            <span class="text-gray-700">|</span>
            <a href="/admin/logout" class="text-red-400 hover:underline">Sign Out</a>
        </div>
    </header>

    <div class="flex-grow flex flex-col md:flex-row max-w-7xl mx-auto w-full px-6 py-8 gap-8">
        <!-- Dashboard Sidebar Navigation -->
        <nav class="w-full md:w-56 shrink-0 space-y-1 bg-white border border-gray-200 p-4 rounded text-xs font-bold uppercase tracking-wider h-fit">
            <a href="/admin/dashboard" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Overview</a>
            <a href="/admin/posts" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Post Archives</a>
            <a href="/admin/review_queue" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded flex items-center justify-between">Review Queue</a>
            <a href="/admin/flagged_posts" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded flex items-center justify-between">Flagged Risks</a>
            <a href="/admin/users" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Users & Roles</a>
            <a href="/admin/withdrawals" class="block py-2 px-3 bg-red-50 text-[#bb1919] rounded">Creator Payouts</a>
            <a href="/admin/ads" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Ad Monetisation</a>
            <a href="/admin/ai_control" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">AI Control Portal</a>
            <a href="/admin/settings" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">Global Settings</a>
            <div class="pt-6 border-t border-dashed mt-4">
                <a href="/creator/dashboard" class="block text-center bg-emerald-600 text-white-10 text-emerald-100 hover:bg-emerald-700 py-1.5 text-[9px] tracking-widest font-extrabold rounded">GO TO CREATOR HUB</a>
            </div>
        </nav>

        <!-- Main Workspace -->
        <main class="flex-1 space-y-6">
            <div class="space-y-1">
                <h1 class="sidebar-title font-black text-lg uppercase">Creator Disbursements & Payouts Panel</h1>
                <p class="text-xs text-slate-500 font-light">Moderate independent writer withdrawals, verify PayPal routing, and settle platform clearance ledgers securely.</p>
            </div>

            <!-- Messages feedback -->
            <?php if (!empty($error)): ?>
                <div class="p-4 rounded text-xs bg-red-50 text-red-750 border border-red-200">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="p-4 rounded text-xs bg-emerald-50 text-emerald-700 border border-emerald-250 font-bold">
                    ✓ <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm relative">
                    <span class="text-[10px] text-gray-400 font-mono uppercase block font-medium">AWAITING REVIEW REQUESTS</span>
                    <span class="text-3xl font-black text-amber-500 font-mono block mt-1"><?php echo $pendingCount; ?></span>
                </div>
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm relative">
                    <span class="text-[10px] text-gray-400 font-mono uppercase block font-medium">TOTAL OUTSTANDING VOLUME</span>
                    <span class="text-3xl font-black text-slate-800 font-mono block mt-1">$<?php echo number_format($pendingVolume, 2); ?></span>
                </div>
                <div class="bg-white border border-gray-200 p-5 rounded shadow-sm relative">
                    <span class="text-[10px] text-gray-400 font-mono uppercase block font-medium">TOTAL LIFE CLEARANCES</span>
                    <span class="text-3xl font-black text-emerald-600 font-mono block mt-1">$<?php echo number_format($totalCleared, 2); ?></span>
                </div>
            </div>

            <!-- Pending requests section -->
            <div class="bg-white border border-gray-200 p-6 rounded shadow-sm space-y-4">
                <h2 class="font-bold text-sm text-slate-800 border-b pb-2">Pending Creator Withdrawals Queue</h2>

                <?php if (empty($pendingWithdrawals)): ?>
                    <p class="text-xs text-slate-500 italic py-6">The pending creator payout queue is currently fully settled and empty.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-slate-400 font-mono pb-2">
                                    <th class="pb-2">ID</th>
                                    <th class="pb-2">Writer</th>
                                    <th class="pb-2">Method</th>
                                    <th class="pb-2">Routing Details</th>
                                    <th class="pb-2">Disbursement</th>
                                    <th class="pb-2">Requested On</th>
                                    <th class="pb-2 text-right">Moderator Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-150">
                                <?php foreach ($pendingWithdrawals as $p): ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-4 font-mono text-slate-500">np_w_<?php echo $p['id']; ?></td>
                                        <td class="py-4">
                                            <span class="font-bold text-slate-900 block"><?php echo htmlspecialchars($p['username']); ?></span>
                                            <span class="text-[10px] text-slate-400 block mt-0.5"><?php echo htmlspecialchars($p['email']); ?></span>
                                        </td>
                                        <td class="py-4 font-medium text-slate-800"><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                        <td class="py-4 text-xs font-mono text-gray-600 max-w-xs break-all"><?php echo htmlspecialchars($p['payment_details']); ?></td>
                                        <td class="py-4 font-mono font-extrabold text-slate-900">$<?php echo number_format($p['amount'], 2); ?></td>
                                        <td class="py-4 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i', strtotime($p['created_at'])); ?></td>
                                        <td class="py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <form method="POST" onsubmit="return confirm('Settle and issue payout transfer of $<?php echo number_format($p['amount'], 2); ?> now?');">
                                                    <?php echo CSRF::renderField(); ?>
                                                    <input type="hidden" name="withdrawal_id" value="<?php echo $p['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-mono text-[10px] font-bold px-2 py-1 rounded">Approve & Wire</button>
                                                </form>
                                                <form method="POST" onsubmit="return confirm('Reject request and return funds to writer wallet balance?');">
                                                    <?php echo CSRF::renderField(); ?>
                                                    <input type="hidden" name="withdrawal_id" value="<?php echo $p['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="bg-red-650 hover:bg-red-800 text-white font-mono text-[10px] font-bold px-2 py-1 rounded">Deny</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Cleared transaction logs -->
            <div class="bg-white border border-gray-200 p-6 rounded shadow-sm space-y-4">
                <h3 class="font-bold text-sm text-slate-800 border-b pb-2">Settlement Activity Logs</h3>

                <?php if (empty($clearedWithdrawals)): ?>
                    <p class="text-xs text-slate-400 italic">No cleared creator settlements are currently finalized.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 text-slate-400 font-mono pb-2">
                                    <th class="pb-2">Reference ID</th>
                                    <th class="pb-2">Creator</th>
                                    <th class="pb-2">Method</th>
                                    <th class="pb-2">Amount</th>
                                    <th class="pb-2">Execution Date</th>
                                    <th class="pb-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($clearedWithdrawals as $c): ?>
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3 font-mono text-slate-400">np_w_<?php echo $c['id']; ?></td>
                                        <td class="py-3 font-bold text-slate-800"><?php echo htmlspecialchars($c['username']); ?></td>
                                        <td class="py-3 font-mono text-slate-500 text-[11px]"><?php echo htmlspecialchars($c['payment_method']); ?></td>
                                        <td class="py-3 font-mono font-bold">$<?php echo number_format($c['amount'], 2); ?></td>
                                        <td class="py-3 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i', strtotime($c['updated_at'])); ?></td>
                                        <td class="py-3 text-right">
                                            <?php if ($c['status'] === 'approved'): ?>
                                                <span class="bg-emerald-55 text-emerald-700 border border-emerald-200 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Cleared</span>
                                            <?php else: ?>
                                                <span class="bg-red-55 text-red-700 border border-red-200 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase font-mono">Rejected</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
