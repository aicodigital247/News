<?php
/**
 * NeuralPress - User administration
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin']);
$db = Database::getInstance();
$res = $db->query("SELECT id, username, email, role, created_at FROM users ORDER BY id ASC");

Layout::renderHeader('System Operator Directory', 'Users');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Cryptographic operator directory. Listing all authorized credentials permitted to write or authorize publications.
            </p>
        </div>

        <div class="bg-slate-900/40 border border-slate-900 rounded-xl overflow-hidden p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead>
                        <tr class="border-b border-slate-900 text-slate-400 uppercase font-mono text-[10px] tracking-wider select-none bg-slate-950/20">
                            <th class="py-3 px-4">Username</th>
                            <th class="py-3 px-4">Email Address</th>
                            <th class="py-3 px-4 text-center">Authorized Role</th>
                            <th class="py-3 px-4 text-right">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/30 font-sans">
                        <?php while ($row = $res->fetch_assoc()): ?>
                            <tr class="hover:bg-slate-900/20 transition">
                                <td class="py-4 px-4 font-semibold text-slate-200"><?php echo htmlspecialchars($row['username']); ?></td>
                                <td class="py-4 px-4 text-slate-400"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-md font-mono text-[9px] uppercase font-bold tracking-widest border border-red-900/45 bg-[#bb1919]/10 text-[#bb1919] shadow-sm">
                                        <?php echo htmlspecialchars($row['role']); ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right font-mono text-slate-500"><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php
Layout::renderFooter();
?>
