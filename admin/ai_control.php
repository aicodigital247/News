<?php
/**
 * NeuralPress - AI System prompt steering controllers
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/admin_layout.php';

use NeuralPress\Core\Auth;
use NeuralPress\Admin\Layout;

Auth::checkRole(['admin']);

Layout::renderHeader('Neural AI Steering Portal', 'AI');
?>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400 max-w-xl">
                Configure rules governing Gemini models steering parameters, context thresholds, and heuristic clickbait severity penalties.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Parameters configuration card -->
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl space-y-4">
                <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919] border-b border-slate-900 pb-2">// CRITICAL MODEL BIASES</h3>
                
                <div class="space-y-4">
                    <div class="space-y-1">
                        <span class="block text-[10px] font-mono text-slate-500 uppercase">Editorial Strictness Index</span>
                        <div class="flex items-center gap-4">
                            <div class="flex-1 bg-slate-950 h-2 rounded overflow-hidden border border-slate-800">
                                <div class="bg-[#bb1919] h-full w-[95%]"></div>
                            </div>
                            <span class="text-xs font-mono font-bold text-white">0.95 (MAX)</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-[10px] font-mono text-slate-500 uppercase">Clickbait Penalty Mitigation</span>
                        <div class="flex items-center gap-4">
                            <div class="flex-1 bg-slate-950 h-2 rounded overflow-hidden border border-slate-800">
                                <div class="bg-[#bb1919] h-full w-[80%]"></div>
                            </div>
                            <span class="text-xs font-mono font-bold text-white">0.80 (HIGH)</span>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <span class="block text-[10px] font-mono text-slate-500 uppercase">Neutral Factuality Alignment</span>
                        <div class="flex items-center gap-4">
                            <div class="flex-1 bg-slate-950 h-2 rounded overflow-hidden border border-slate-800">
                                <div class="bg-[#bb1919] h-full w-[90%]"></div>
                            </div>
                            <span class="text-xs font-mono font-bold text-white">0.90 (STRICT)</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active prompt rules card -->
            <div class="bg-slate-900/40 border border-slate-900 p-6 rounded-xl space-y-4 font-mono text-xs">
                <h3 class="text-xs font-mono font-bold uppercase tracking-widest text-[#bb1919] border-b border-slate-900 pb-2">// STEERING PARAMETERS LOGS</h3>
                
                <div class="space-y-3.5 text-slate-350 bg-slate-950 p-4 rounded-lg border border-slate-900 leading-relaxed">
                    <div>
                        <strong class="text-white block uppercase text-[10px] tracking-wider mb-0.5">BBC Journalistic guidelines enforcement:</strong>
                        No opinions or sensational adjectives are permitted. Sentences must contain active, declarative evidence and objective facts.
                    </div>
                    <div class="border-t border-slate-900 pt-3">
                        <strong class="text-white block uppercase text-[10px] tracking-wider mb-0.5">Active LLM Model Node:</strong>
                        gemini-2.5-flash with automated temperature fallback logic.
                    </div>
                </div>
            </div>
        </div>

<?php
Layout::renderFooter();
?>
