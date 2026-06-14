<?php
/**
 * NeuralPress - Review Queue Dashboard
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();

$message = '';
$messageType = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'] ?? 'success';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify') {
    if (!\NeuralPress\Core\CSRF::checkToken($_POST['csrf_token'] ?? '')) {
        die("CSRF verification failed.");
    }
    $postId = intval($_POST['id'] ?? 0);
    if ($postId > 0) {
        $resPost = $db->query("SELECT title, content FROM posts WHERE id = ?", "i", [$postId]);
        if ($resPost && $rowPost = $resPost->fetch_assoc()) {
            require_once NP_DIR . '/core/ai_verifier.php';
            $analysis = \NeuralPress\Core\analyze_content($rowPost['title'], $rowPost['content']);
            
            // Adjust status to published or flagged depending on risk level
            $status = ($analysis['risk_level'] === 'high' || $analysis['risk_level'] === 'fake_risk') ? 'flagged' : 'published';
            
            $db->query(
                "UPDATE posts SET trust_score = ?, risk_level = ?, verification_reason = ?, status = ? WHERE id = ?",
                "isssi",
                [$analysis['trust_score'], $analysis['risk_level'], $analysis['reason'], $status, $postId]
            );
            $_SESSION['message'] = "Post successfully processed natively! Trust Score: " . $analysis['trust_score'] . "% (" . strtoupper($analysis['risk_level']) . "). Status set to: " . strtoupper($status);
            $_SESSION['message_type'] = ($status === 'flagged') ? 'error' : 'success';
        }
    }
    header('Location: /admin/review_queue');
    exit;
}

$res = $db->query("SELECT * FROM posts WHERE status = 'pending_review' ORDER BY id DESC");

Layout::renderHeader('Awaiting Editorial Verification', 'Review');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Manual gatekeeping terminal. Review content queue before broadcasting to public news feeds. Press verification uses AI and semantic checks.
            </p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="p-4 rounded-lg text-xs font-mono <?php echo $messageType === 'error' ? 'bg-red-950/40 border border-red-500/30 text-red-200' : 'bg-emerald-950/40 border border-emerald-500/30 text-emerald-200'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-slate-900/40 border border-slate-900 rounded-xl overflow-hidden p-6 space-y-4">
            <?php if (!$res || $res->num_rows === 0): ?>
                <div class="text-center py-10 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-950 flex items-center justify-center text-slate-600 mx-auto border border-slate-800">
                        ✓
                    </div>
                    <p class="text-xs text-slate-400 font-mono">ALL PENDING DRAFTS PROCESSED. BROADCAST STREAM REMAINS STEADY.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-900 text-slate-400 uppercase font-mono text-[10px] tracking-wider select-none bg-slate-950/20">
                                <th class="py-3 px-4">Title</th>
                                <th class="py-3 px-4">Category</th>
                                <th class="py-3 px-4 text-center">Initial Trust</th>
                                <th class="py-3 px-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/30 font-sans">
                            <?php while ($row = $res->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-900/20 transition">
                                    <td class="py-4 px-4 font-semibold text-slate-200 max-w-xs md:max-w-md truncate"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="py-4 px-4 text-slate-400 font-mono text-[11px]"><?php echo htmlspecialchars($row['category']); ?></td>
                                    <td class="py-4 px-4 text-center font-mono font-bold text-emerald-400"><?php echo intval($row['trust_score']); ?>%</td>
                                    <td class="py-4 px-4 text-right">
                                        <form method="POST" action="" class="inline">
                                            <?php echo \NeuralPress\Core\CSRF::renderField(); ?>
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="action" value="verify">
                                            <button type="submit" class="bg-[#bb1919] text-white px-3.5 py-2 text-[10px] font-bold font-mono uppercase hover:bg-[#801111] rounded-md transition duration-150 cursor-pointer shadow-sm tracking-wider">Verify Natively</button>
                                        </form>
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
