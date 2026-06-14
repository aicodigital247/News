<?php
/**
 * NeuralPress - Flagged Post Alerts
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE status = 'flagged' ORDER BY id DESC");

Layout::renderHeader('Flagged Risk Alerts', 'Flagged');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Quarantined bulletins flagged by the security verifier agent. These posts failed the semantic alignment tests or triggered trust score warnings.
            </p>
        </div>

        <div class="bg-slate-900/40 border border-slate-900 rounded-xl overflow-hidden p-6 space-y-4">
            <?php if (!$res || $res->num_rows === 0): ?>
                <div class="text-center py-10 space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-950 flex items-center justify-center text-emerald-500 mx-auto border border-slate-800">
                        ✓
                    </div>
                    <p class="text-xs text-slate-400 font-mono">NO RISK ALERTS TRIGGERED ON ACTIVE BROADCAST NODES. SYSTEM SANITIZATION NOMINAL.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead>
                            <tr class="border-b border-slate-900 text-slate-400 uppercase font-mono text-[10px] tracking-wider select-none bg-slate-950/20">
                                <th class="py-3 px-4">Headline</th>
                                <th class="py-3 px-4 text-center">Trust Rating</th>
                                <th class="py-3 px-4 font-light">Verification Trigger</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/30 font-sans">
                            <?php while ($row = $res->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-900/20 transition text-red-100">
                                    <td class="py-4 px-4 font-semibold text-red-400 max-w-xs md:max-w-md truncate"><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td class="py-4 px-4 text-center font-mono font-bold text-[#bb1919]"><?php echo intval($row['trust_score']); ?>%</td>
                                    <td class="py-4 px-4 text-slate-400"><?php echo htmlspecialchars($row['verification_reason']); ?></td>
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
