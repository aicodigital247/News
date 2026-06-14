<?php
/**
 * NeuralPress - Admin Creator Payout Management Portal
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Core\CSRF;
use NeuralPress\Core\MonetizationEngine;
use NeuralPress\Admin\Layout;

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

Layout::renderHeader('Creator Disbursements & Payouts', 'Withdrawals');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Moderate independent writer withdrawals, verify PayPal routing, and settle platform clearance ledgers securely.
            </p>
        </div>

        <!-- Messages feedback -->
        <?php if (!empty($error)): ?>
            <div class="p-4 rounded-lg text-xs font-mono bg-red-955/40 border border-red-500/30 text-red-200">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="p-4 rounded-lg text-xs font-mono bg-emerald-955/40 border border-emerald-500/30 text-emerald-250 font-bold font-mono">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl hover:border-slate-800/80 transition duration-150">
                <span class="text-[10px] text-slate-400 font-mono uppercase block">AWAITING REVIEW REQUESTS</span>
                <span class="text-3xl font-extrabold text-amber-500 font-mono block mt-2"><?php echo $pendingCount; ?></span>
            </div>
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl hover:border-slate-800/80 transition duration-150">
                <span class="text-[10px] text-slate-400 font-mono uppercase block">TOTAL OUTSTANDING VOLUME</span>
                <span class="text-3xl font-extrabold text-slate-100 font-mono block mt-2">$<?php echo number_format($pendingVolume, 2); ?></span>
            </div>
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl hover:border-slate-800/80 transition duration-150">
                <span class="text-[10px] text-slate-400 font-mono uppercase block font-medium">TOTAL LIFE CLEARANCES</span>
                <span class="text-3xl font-extrabold text-emerald-400 font-mono block mt-2">$<?php echo number_format($totalCleared, 2); ?></span>
            </div>
        </div>

        <!-- Pending requests section -->
        <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl space-y-4">
            <h2 class="font-bold text-sm text-slate-200 border-b border-slate-900 pb-2">Pending Creator Withdrawals Queue</h2>

            <?php if (empty($pendingWithdrawals)): ?>
                <div class="text-center py-8 font-mono text-xs text-slate-500">
                    THE PENDING CREATOR PAYOUT QUEUE IS CURRENTLY FULLY SETTLED AND EMPTY.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-900 text-slate-400 font-mono text-[10px] uppercase tracking-wider bg-slate-950/25">
                                <th class="py-3 px-4">ID</th>
                                <th class="py-3 px-4">Writer</th>
                                <th class="py-3 px-4">Method</th>
                                <th class="py-3 px-4">Routing Details</th>
                                <th class="py-3 px-4">Disbursement</th>
                                <th class="py-3 px-4">Requested On</th>
                                <th class="py-3 px-4 text-right">Moderator Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 font-sans">
                            <?php foreach ($pendingWithdrawals as $p): ?>
                                <tr class="hover:bg-slate-900/20 transition">
                                    <td class="py-4 px-4 font-mono text-slate-500">np_w_<?php echo $p['id']; ?></td>
                                    <td class="py-4 px-4">
                                        <span class="font-bold text-slate-200 block"><?php echo htmlspecialchars($p['username']); ?></span>
                                        <span class="text-[10px] text-slate-500 block mt-0.5"><?php echo htmlspecialchars($p['email']); ?></span>
                                    </td>
                                    <td class="py-4 px-4 font-medium text-slate-300 font-mono text-[10px] uppercase"><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                    <td class="py-4 px-4 text-xs font-mono text-slate-400 max-w-xs break-all"><?php echo htmlspecialchars($p['payment_details']); ?></td>
                                    <td class="py-4 px-4 font-mono font-extrabold text-[#bb1919]">$<?php echo number_format($p['amount'], 2); ?></td>
                                    <td class="py-4 px-4 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i', strtotime($p['created_at'])); ?></td>
                                    <td class="py-4 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <form method="POST" onsubmit="return confirm('Settle and issue payout transfer of $<?php echo number_format($p['amount'], 2); ?> now?');" class="inline">
                                                <?php echo CSRF::renderField(); ?>
                                                <input type="hidden" name="withdrawal_id" value="<?php echo $p['id']; ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-mono text-[10px] font-bold px-2.5 py-1.5 rounded-md transition duration-150 cursor-pointer">Settle & Wire</button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Reject request and return funds to writer wallet balance?');" class="inline">
                                                <?php echo CSRF::renderField(); ?>
                                                <input type="hidden" name="withdrawal_id" value="<?php echo $p['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="bg-[#bb1919] hover:bg-[#801111] text-white font-mono text-[10px] font-bold px-2.5 py-1.5 rounded-md transition duration-150 cursor-pointer">Deny</button>
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
        <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl space-y-4">
            <h3 class="font-bold text-sm text-slate-200 border-b border-slate-900 pb-2">Settlement Activity Logs</h3>

            <?php if (empty($clearedWithdrawals)): ?>
                <div class="text-center py-6 font-mono text-xs text-slate-500">
                    NO CLEARED CREATOR SETTLEMENTS ARE CURRENTLY FINALIZED.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-900 text-slate-400 font-mono text-[10px] uppercase tracking-wider bg-slate-950/25">
                                <th class="py-3 px-4">Reference ID</th>
                                <th class="py-3 px-4">Creator</th>
                                <th class="py-3 px-4">Method</th>
                                <th class="py-3 px-4">Amount</th>
                                <th class="py-3 px-4">Execution Date</th>
                                <th class="py-3 px-4 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/40 font-sans">
                            <?php foreach ($clearedWithdrawals as $c): ?>
                                <tr class="hover:bg-slate-900/20 transition">
                                    <td class="py-4 px-4 font-mono text-slate-550">np_w_<?php echo $c['id']; ?></td>
                                    <td class="py-4 px-4 font-bold text-slate-300"><?php echo htmlspecialchars($c['username']); ?></td>
                                    <td class="py-4 px-4 font-mono text-slate-400 text-[11px] uppercase"><?php echo htmlspecialchars($c['payment_method']); ?></td>
                                    <td class="py-4 px-4 font-mono font-bold text-slate-100">$<?php echo number_format($c['amount'], 2); ?></td>
                                    <td class="py-4 px-4 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i', strtotime($c['updated_at'])); ?></td>
                                    <td class="py-4 px-4 text-right">
                                        <?php if ($c['status'] === 'approved'): ?>
                                            <span class="bg-emerald-950/30 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded font-bold text-[9px] uppercase font-mono tracking-wider">Cleared</span>
                                        <?php else: ?>
                                            <span class="bg-red-950/30 text-red-400 border border-red-500/20 px-2 py-0.5 rounded font-bold text-[9px] uppercase font-mono tracking-wider">Rejected</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

<?php
Layout::renderFooter();
?>
