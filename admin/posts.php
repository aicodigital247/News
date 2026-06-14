<?php
/**
 * NeuralPress - Admin Post Archives
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$countRes = $db->query("SELECT COUNT(*) as total FROM posts");
$total = $countRes ? ($countRes->fetch_assoc()['total'] ?? 0) : 0;
$totalPages = max(1, ceil($total / $limit));

$res = $db->query("SELECT * FROM posts ORDER BY id DESC LIMIT ? OFFSET ?", "ii", [$limit, $offset]);

Layout::renderHeader('Committed Article Ledger', 'Posts');
?>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <p class="text-xs text-slate-400 max-w-xl">
                Browse, search, and edit full-fidelity automated and human-curated press records archived within the core database.
            </p>
            <a href="/admin/write.php" class="bg-[#bb1919] hover:bg-[#801111] text-white font-mono font-bold uppercase text-xs tracking-wider px-4 py-2.5 rounded-lg shadow-md inline-flex items-center justify-center gap-2 transition duration-150">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Compose New Article
            </a>
        </div>

        <?php
        $message = '';
        $messageType = '';
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            $messageType = $_SESSION['message_type'] ?? 'success';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        if (!empty($message)):
        ?>
            <div class="p-4 rounded-lg text-xs font-mono <?php echo $messageType === 'error' ? 'bg-red-950/40 border border-red-500/30 text-red-200' : 'bg-emerald-950/40 border border-emerald-500/30 text-emerald-200'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="bg-slate-900/40 border border-slate-900 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-900 text-slate-400 uppercase font-mono text-[10px] tracking-wider select-none bg-slate-950/40">
                            <th class="py-4 px-6">Headline</th>
                            <th class="py-4 px-6 text-center">Status</th>
                            <th class="py-4 px-6 text-center">Trust Rating</th>
                            <th class="py-4 px-6 text-center font-mono">Views</th>
                            <th class="py-4 px-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/60 font-sans">
                        <?php if ($total === 0): ?>
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500 font-mono">No articles found in archives.</td>
                            </tr>
                        <?php endif; ?>
                        <?php while ($row = $res->fetch_assoc()): 
                            $status = $row['status'] ?? 'draft';
                            $statusBg = 'bg-slate-800 text-slate-400 border border-slate-700/50';
                            if ($status === 'published') {
                                $statusBg = 'bg-emerald-950/30 text-emerald-400 border border-emerald-500/20';
                            } elseif ($status === 'pending_review') {
                                $statusBg = 'bg-amber-950/30 text-amber-400 border border-amber-500/20';
                            } elseif ($status === 'flagged') {
                                $statusBg = 'bg-red-950/30 text-red-400 border border-red-500/20';
                            }
                        ?>
                            <tr class="hover:bg-slate-900/20 transition">
                                <td class="py-4 px-6 font-medium text-slate-200">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full font-mono text-[9px] uppercase font-bold tracking-wider <?php echo $statusBg; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center font-mono">
                                    <span class="font-bold <?php echo intval($row['trust_score']) >= 80 ? 'text-emerald-400' : 'text-amber-500'; ?>">
                                        <?php echo intval($row['trust_score']); ?>%
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center font-mono text-slate-350">
                                    <?php echo number_format($row['views']); ?>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <a href="/admin/write.php?id=<?php echo $row['id']; ?>" class="inline-flex items-center gap-1 bg-slate-900 hover:bg-[#bb1919]/10 hover:text-[#bb1919] text-slate-300 font-mono text-[10px] font-bold uppercase tracking-wider px-2.5 py-1.5 rounded-md border border-slate-800 hover:border-[#bb1919]/30 transition duration-150">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($totalPages > 1): ?>
                <div class="flex flex-col sm:flex-row items-center justify-between border-t border-slate-900 bg-slate-950/20 px-6 py-5 gap-4">
                    <span class="text-xs text-slate-500 font-mono">Page <strong><?php echo $page; ?></strong> of <strong><?php echo $totalPages; ?></strong> (<?php echo $total; ?> active bulletins)</span>
                    <div class="inline-flex gap-2">
                        <a href="?page=<?php echo max(1, $page - 1); ?>" class="px-3 py-1.5 border border-slate-800 rounded-md font-mono text-[10px] font-bold uppercase tracking-wider <?php echo ($page <= 1) ? 'text-slate-700 border-slate-900 cursor-not-allowed pointer-events-none' : 'text-slate-300 hover:text-white hover:bg-slate-900 bg-slate-950'; ?>">
                            &larr; Prev
                        </a>
                        <a href="?page=<?php echo min($totalPages, $page + 1); ?>" class="px-3 py-1.5 border border-slate-800 rounded-md font-mono text-[10px] font-bold uppercase tracking-wider <?php echo ($page >= $totalPages) ? 'text-slate-700 border-slate-900 cursor-not-allowed pointer-events-none' : 'text-slate-300 hover:text-white hover:bg-slate-900 bg-slate-950'; ?>">
                            Next &rarr;
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

<?php
Layout::renderFooter();
?>
