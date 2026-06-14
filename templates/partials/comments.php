<?php
/**
 * NeuralPress - Interactive Comments Partial
 */

use NeuralPress\Core\Database;

$db = Database::getInstance();
$postId = isset($post['id']) ? intval($post['id']) : 0;
$visitorName = $_SESSION['username'] ?? '';

// Simple comments submission logic
$commentSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_action']) && $_POST['comment_action'] === 'add_comment' && $postId > 0) {
    $authorNameInput = trim($_POST['author_name'] ?? '');
    $commentTxtInput = trim($_POST['comment_text'] ?? '');

    if (!empty($authorNameInput) && !empty($commentTxtInput)) {
        // Log comments into the database logs or schema if we store them, or system logs, style as inline state
        $logCommentText = "New Comment by " . $authorNameInput . " on article ID " . $postId . ": " . substr($commentTxtInput, 0, 100);
        $db->query("INSERT INTO system_logs (user_id, action, severity) VALUES (NULL, ?, 'info')", "s", [$logCommentText]);
        $commentSuccess = "Your comment has been submitted and is pending automated moderation filters.";

        // Also cache comment in session for interactive local preview
        if (!isset($_SESSION['local_comments'])) {
            $_SESSION['local_comments'] = [];
        }
        $_SESSION['local_comments'][$postId][] = [
            'author' => $authorNameInput,
            'text' => $commentTxtInput,
            'date' => date('Y-m-d H:i:s')
        ];
    }
}

// Curate standard mock comments + local comments
$articleComments = [
    [
        'author' => 'Dr. Elizabeth Stone',
        'text' => 'This investigative report is extremely well-reasoned. The inclusion of trust score heuristics provides high integrity rate.',
        'date' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ],
    [
        'author' => 'Marcus Vance',
        'text' => 'Brilliant coverage of high factual accountability topics. BBC standard CMS execution!',
        'date' => date('Y-m-d H:i:s', strtotime('-2 hours'))
    ]
];

if (isset($_SESSION['local_comments'][$postId])) {
    foreach ($_SESSION['local_comments'][$postId] as $localC) {
        $articleComments[] = $localC;
    }
}
?>
<div class="border-t border-slate-150 dark:border-slate-800 pt-8 mt-10">
    <h3 class="text-lg font-black tracking-tight text-slate-900 dark:text-white uppercase font-mono mb-6 flex items-center gap-2">
        <span class="inline-block w-2.5 h-4 bg-[#bb1919]"></span> Reader Analysis & Comments (<?php echo count($articleComments); ?>)
    </h3>

    <?php if ($commentSuccess): ?>
        <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-800 dark:text-emerald-350 text-xs font-medium border border-emerald-200 dark:border-emerald-800/80 rounded-md mb-4">
            <?php echo htmlspecialchars($commentSuccess); ?>
        </div>
    <?php endif; ?>

    <!-- Comments history list -->
    <div class="space-y-4 mb-8">
        <?php foreach ($articleComments as $comment): ?>
            <div class="bg-slate-50/60 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800/80 p-4 rounded-xl text-slate-800 dark:text-slate-200 space-y-1.5 shadow-2xs">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-extrabold text-slate-900 dark:text-white"><?php echo htmlspecialchars($comment['author']); ?></span>
                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500"><?php echo date('M d, Y H:i', strtotime($comment['date'])); ?></span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-350 font-light leading-relaxed">
                    <?php echo htmlspecialchars($comment['text']); ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Submit a Comment -->
    <div class="bg-white dark:bg-slate-900/30 border border-slate-200 dark:border-slate-800 p-5 rounded-xl">
        <h4 class="text-xs font-mono uppercase tracking-wider text-slate-800 dark:text-slate-400 font-bold mb-3">Leave a public comment</h4>
        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" class="space-y-3">
            <input type="hidden" name="comment_action" value="add_comment">
            <div>
                <input type="text" name="author_name" required placeholder="Your full name" value="<?php echo htmlspecialchars($visitorName); ?>" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 focus:border-[#bb1919] focus:outline-none rounded-md text-xs py-2.5 px-3 text-slate-950 dark:text-white-450 placeholder-slate-400">
            </div>
            <div>
                <textarea name="comment_text" rows="3" required placeholder="Discuss analytical insights thoughtfully..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 focus:border-[#bb1919] focus:outline-none rounded-md text-xs py-2.5 px-3 text-slate-950 dark:text-white-450 placeholder-slate-400"></textarea>
            </div>
            <button type="submit" class="bg-slate-900 dark:bg-slate-800 hover:bg-black dark:hover:bg-slate-700 text-white text-xs font-mono font-bold uppercase tracking-wider py-2.5 px-5 rounded-md transition-all cursor-pointer">
                Submit Comment Evaluation
            </button>
        </form>
    </div>
</div>
