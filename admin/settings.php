<?php
/**
 * NeuralPress - Main CMS configuration panel
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin']);

Layout::renderHeader('System Configurations', 'Settings');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Audit configurations, logging intervals, and platform cache directories. Manage global CMS states.
            </p>
        </div>

        <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-6 space-y-4">
            <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919] border-b border-slate-900 pb-2">// HARDWARE & ENVIRONMENT SPECIFICATION</h3>
            
            <div class="space-y-4 font-mono text-xs text-slate-405 leading-relaxed">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-slate-900/60 pb-3">
                    <span class="text-slate-500 uppercase font-mono text-[10px] tracking-wider">Platform Name</span>
                    <strong class="col-span-2 text-slate-200">NeuralPress Global Newsroom CMS</strong>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-b border-slate-900/60 pb-3">
                    <span class="text-slate-500 uppercase font-mono text-[10px] tracking-wider">Locale Context</span>
                    <strong class="col-span-2 text-slate-200">UTC (+00:00 GMT - British Standard Time)</strong>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-1">
                    <span class="text-slate-500 uppercase font-mono text-[10px] tracking-wider">Routing Engine</span>
                    <strong class="col-span-2 text-slate-200">Pure PHP 8.2 FastCGI with Apache Native Rewrites</strong>
                </div>
            </div>
        </div>

<?php
Layout::renderFooter();
?>
