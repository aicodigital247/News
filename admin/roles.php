<?php
/**
 * NeuralPress - Permissions Matrix Mapping Configuration
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin']);

Layout::renderHeader('Journalism Role Access Configurations', 'Users');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Define the permissions, access barriers, and security clearance heights for the editorial staff roles.
            </p>
        </div>

        <div class="bg-slate-900/40 border border-slate-900 rounded-xl p-6 space-y-4">
            <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919] border-b border-slate-900 pb-2">// SYSTEM PERMISSION SPECIFICATION Matrix</h3>
            
            <div class="space-y-4 font-mono text-xs text-slate-350 leading-relaxed">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 border-b border-slate-900/60 pb-3">
                    <span class="text-slate-500 uppercase font-mono text-[9px] tracking-wider font-bold">ADMIN ROLE</span>
                    <p class="col-span-3 text-slate-200">Unlimited write, delete, ad control, schema updates, AI system prompt steering parameters, and financial settlements.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 border-b border-slate-900/60 pb-3">
                    <span class="text-slate-500 uppercase font-mono text-[9px] tracking-wider font-bold">EDITOR ROLE</span>
                    <p class="col-span-3 text-slate-200">Fact-check override operations, review queue sorting, manual verifications, draft approval, and publication releases.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 pb-1">
                    <span class="text-slate-500 uppercase font-mono text-[9px] tracking-wider font-bold">JOURNALIST / CREATOR</span>
                    <p class="col-span-3 text-slate-200">Generate drafts from Google search hotscopes, document core articles, list creator stats, and withdraw accumulated earnings.</p>
                </div>
            </div>
        </div>

<?php
Layout::renderFooter();
?>
