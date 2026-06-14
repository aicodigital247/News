<?php
/**
 * NeuralPress - Creator Withdrawal Records Page
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

// Handle withdrawal submission from this page as well
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }
    $amount = floatval($_POST['amount'] ?? 0);
    $method = trim($_POST['payment_method'] ?? '');
    $details = trim($_POST['payment_details'] ?? '');

    $res = $monetization->requestWithdrawal($userId, $amount, $method, $details);
    if ($res === true) {
        $success = "Your withdrawal request of $" . number_format($amount, 2) . " has been submitted to editor review pipeline.";
    } else {
        $error = $res;
    }
}

// Fetch current creator finances
$balanceInfo = $monetization->getBalance($userId);

// Fetch all historical withdrawal requests
$withdrawalsRes = $db->query("SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC", "i", [$userId]);
$myWithdrawals = [];
if ($withdrawalsRes) {
    while ($w = $withdrawalsRes->fetch_assoc()) {
        $myWithdrawals[] = $w;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Withdrawal Operations - Creator Hub</title>
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
            <a href="/creator/withdrawals" class="block py-2 px-3 bg-red-50 text-[#bb1919] rounded">Withdrawals Log</a>
            <a href="/creator/promotions" class="block py-2 px-3 text-slate-600 hover:bg-slate-50 hover:text-red-700 rounded">My Promotions</a>
            <div class="pt-6 border-t border-dashed mt-4">
                <a href="/" class="block text-center bg-[#bb1919] text-white py-2 select-none text-[10px] tracking-widest font-extrabold hover:bg-[#801111]">VIEW PUBLIC SITE</a>
            </div>
        </nav>

        <!-- Main Workspace -->
        <main class="flex-1 space-y-6">
            <div class="space-y-1.5 border-b pb-4">
                <h1 class="font-black text-xl text-slate-900 uppercase font-sans">FINANCIAL SETTLEMENT RECORDS</h1>
                <p class="text-xs text-slate-500 font-light font-sans">View your completed, pending, or rejected earnings withdrawals.</p>
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
                <!-- Left Column: Quick Request Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white border border-gray-200 p-5 rounded shadow-sm space-y-4">
                        <div class="border-b pb-2">
                            <span class="text-[10px] text-gray-400 font-mono uppercase block font-bold leading-none">CURRENT BALANCE</span>
                            <span class="text-2xl font-black text-emerald-600 font-mono block mt-1">
                                $<?php echo number_format($balanceInfo['balance'], 2); ?>
                            </span>
                        </div>
                        
                        <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919]">// NEW DISBURSEMENT REQUEST</h3>
                        
                        <form method="POST" class="space-y-3">
                            <?php echo CSRF::renderField(); ?>
                            
                            <div class="space-y-1">
                                <label class="block text-[10px] font-mono uppercase text-slate-600">Disburse Amount ($)</label>
                                <input 
                                    type="number" 
                                    name="amount" 
                                    step="0.01" 
                                    min="1.00" 
                                    max="<?php echo floatval($balanceInfo['balance']); ?>"
                                    placeholder="0.00"
                                    required 
                                    class="bg-white border border-slate-350 text-xs px-3 py-1.5 w-full rounded focus:outline-none focus:border-[#bb1919]"
                                >
                            </div>
                            
                            <div class="space-y-1">
                                <label class="block text-[10px] font-mono uppercase text-slate-600">Disbursement Channel</label>
                                <select 
                                    name="payment_method" 
                                    required 
                                    class="bg-white border border-slate-350 text-xs px-3 py-1.5 w-full rounded focus:outline-none focus:border-[#bb1919]"
                                >
                                    <option value="PayPal">PayPal Invoice Gate</option>
                                    <option value="Direct Debit">Direct Bank Wire</option>
                                    <option value="Stripe">Stripe Connect Payout</option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[10px] font-mono uppercase text-slate-600">Payment Routing Info</label>
                                <textarea 
                                    name="payment_details" 
                                    placeholder="Enter your email account or bank wire parameters cleanly..."
                                    required 
                                    rows="3"
                                    class="bg-white border border-slate-350 text-xs px-3 py-1.5 w-full rounded focus:outline-none focus:border-[#bb1919]"
                                ></textarea>
                            </div>

                            <button 
                                type="submit" 
                                class="w-full bg-[#bb1919] hover:bg-[#801111] text-white text-[11px] font-bold uppercase py-2 tracking-wide font-mono transition cursor-pointer"
                                <?php echo (floatval($balanceInfo['balance']) < 1.00) ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''; ?>
                            >
                                Submit Request
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right Column: Disbursements History Table -->
                <div class="lg:col-span-2">
                    <div class="bg-white border border-gray-200 p-6 rounded shadow-sm space-y-4">
                        <h3 class="font-bold text-sm text-slate-800">Clearance Transactions</h3>

                        <?php if (empty($myWithdrawals)): ?>
                            <p class="text-xs text-slate-500 italic py-6">No payouts requested or processed on this account yet. Accumulate balance via reading traffic to authorize payouts.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-100 text-slate-400 font-mono pb-2">
                                            <th class="pb-2">Reference ID</th>
                                            <th class="pb-2">Method</th>
                                            <th class="pb-2">Details</th>
                                            <th class="pb-2">Amount</th>
                                            <th class="pb-2">Execution Date</th>
                                            <th class="pb-2 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <?php foreach ($myWithdrawals as $draw): ?>
                                            <tr class="hover:bg-slate-50/50">
                                                <td class="py-3 font-mono text-slate-500">np_w_<?php echo $draw['id']; ?></td>
                                                <td class="py-3 font-medium text-slate-800"><?php echo htmlspecialchars($draw['payment_method']); ?></td>
                                                <td class="py-3 max-w-xs truncate text-[11px] text-gray-500 px-1" title="<?php echo htmlspecialchars($draw['payment_details']); ?>">
                                                    <?php echo htmlspecialchars($draw['payment_details']); ?>
                                                </td>
                                                <td class="py-3 font-mono font-extrabold text-slate-930">$<?php echo number_format($draw['amount'], 2); ?></td>
                                                <td class="py-3 text-slate-500 font-mono text-[10px]"><?php echo date('j M Y, H:i:s', strtotime($draw['created_at'])); ?></td>
                                                <td class="py-3 text-right">
                                                    <?php if ($draw['status'] === 'approved'): ?>
                                                        <span class="bg-emerald-55 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded font-bold text-[9px] uppercase">Approved</span>
                                                    <?php elseif ($draw['status'] === 'rejected'): ?>
                                                        <span class="bg-red-55 text-red-700 border border-red-200 px-2 py-0.5 rounded font-bold text-[9px] uppercase">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="bg-amber-55 text-amber-700 border border-amber-200 px-2 py-0.5 rounded font-bold text-[9px] uppercase">Awaiting review</span>
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
