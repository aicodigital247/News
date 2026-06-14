<?php
/**
 * NeuralPress - Performance and monetization analytics dashboards
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Core\Database;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin', 'editor']);
$db = Database::getInstance();

$viewsRow = $db->query("SELECT SUM(views) as total_views FROM posts")->fetch_assoc();
$totalViews = $viewsRow['total_views'] ?? 0;

$impsRow = $db->query("SELECT COUNT(*) as total_imps FROM ad_events WHERE event_type = 'impression'")->fetch_assoc();
$totalImps = $impsRow['total_imps'] ?? 0;

$clicksRow = $db->query("SELECT COUNT(*) as total_clicks FROM ad_events WHERE event_type = 'click'")->fetch_assoc();
$totalClicks = $clicksRow['total_clicks'] ?? 0;

Layout::renderHeader('System Traffic Analytics', 'Overview');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Global performance index tracking total node views, ad monetization metrics, and reader engagement levels.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl hover:border-slate-800 transition">
                <span class="text-[9px] text-[#bb1919] font-mono uppercase block font-extrabold">// Accumulated Node Reads</span>
                <span class="text-3xl font-extrabold text-slate-100 font-mono mt-3 block"><?php echo number_format($totalViews); ?> <span class="text-xs text-slate-400">reads</span></span>
            </div>
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl hover:border-slate-800 transition">
                <span class="text-[9px] text-[#bb1919] font-mono uppercase block font-extrabold">// Sponsored Impressions</span>
                <span class="text-3xl font-extrabold text-slate-100 font-mono mt-3 block"><?php echo number_format($totalImps); ?> <span class="text-xs text-slate-400">imps</span></span>
            </div>
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl hover:border-slate-800 transition">
                <span class="text-[9px] text-[#bb1919] font-mono uppercase block font-extrabold">// Sponsored Clicks</span>
                <span class="text-3xl font-extrabold text-slate-100 font-mono mt-3 block"><?php echo number_format($totalClicks); ?> <span class="text-xs text-slate-400">clicks</span></span>
            </div>
        </div>

<?php
Layout::renderFooter();
?>
