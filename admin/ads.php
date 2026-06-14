<?php
/**
 * NeuralPress - Ad Monetization and optimization control
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin']);
$db = Database::getInstance();
$res = $db->query("SELECT * FROM ads ORDER BY id ASC");

Layout::renderHeader('Ad Monetisation Status', 'Ads');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Active ad unit telemetry and injection points across web publications. Monitor dynamic slot mapping coordinates.
            </p>
        </div>

        <div class="bg-slate-900/40 border border-slate-900 rounded-xl overflow-hidden p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-900 text-slate-400 uppercase font-mono text-[10px] tracking-wider select-none bg-slate-950/20">
                            <th class="py-3 px-4">Campaign Name</th>
                            <th class="py-3 px-4">Slot Position Coordinate</th>
                            <th class="py-3 px-4 text-center">Injection Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/30 font-sans">
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-900/20 transition">
                                <td class="py-4 px-4 font-semibold text-slate-200"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="py-4 px-4 font-mono text-[11px] text-[#bb1919] uppercase"><?php echo htmlspecialchars($row['slot_position']); ?></td>
                                <td class="py-4 px-4 text-center">
                                    <span class="bg-emerald-950/30 text-emerald-400 border border-emerald-500/20 text-[9px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full font-mono">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php
Layout::renderFooter();
?>
