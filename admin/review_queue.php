<?php
/**
 * NeuralPress - Review Queue Dashboard
 */
require_once __DIR__ . '/../config.php';
use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>NeuralPress Editorial Review Queue</title>
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
        <h1 class="sidebar-title font-bold text-lg">Awaiting Editorial Verification</h1>
        
        <?php if (!empty($message)): ?>
            <div class="p-4 mb-4 rounded text-xs <?php echo $messageType === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border p-6 rounded shadow-sm">
            <?php if (!$res || $res->num_rows === 0): ?>
                <p class="text-xs text-slate-500 font-light">All pending drafts processed. Broadcast stream remains steady.</p>
            <?php else: ?>
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b text-left text-slate-400 uppercase font-mono text-[10px]">
                            <th class="pb-2">Title</th>
                            <th class="pb-2">Category</th>
                            <th class="pb-2">Trust %</th>
                            <th class="pb-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr class="border-b last:border-0 hover:bg-slate-50">
                                <td class="py-3 font-semibold"><?php echo htmlspecialchars($row['title']); ?></td>
                                <td class="py-3 text-slate-500"><?php echo htmlspecialchars($row['category']); ?></td>
                                <td class="py-3 font-mono font-bold text-emerald-600"><?php echo intval($row['trust_score']); ?>%</td>
                                <td class="py-3">
                                    <form method="POST" action="" class="inline">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="action" value="verify">
                                        <button type="submit" class="bg-[#bb1919] text-white px-2.5 py-1 text-[10px] font-bold uppercase hover:bg-[#801111] cursor-pointer">Verify Natively</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
