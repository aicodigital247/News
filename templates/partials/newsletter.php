<?php
/**
 * NeuralPress - Newsletter Signup Partial
 */
?>
<div class="bg-[#0f172a] dark:bg-slate-950/60 border border-slate-950/80 dark:border-slate-800 text-white p-6 rounded-xl shadow-xs space-y-4">
    <div class="space-y-1.5">
        <span class="inline-block bg-[#bb1919] text-white text-[9px] font-mono uppercase tracking-widest px-2 py-0.5 leading-none font-black rounded-sm">MONITORING</span>
        <h3 class="text-base font-extrabold tracking-tight font-sans">
            Weekly Investigative Briefings
        </h3>
        <p class="text-[11px] text-slate-400 leading-relaxed font-light">
            Receive advanced, factual reports, deep-dive investigations, and trust score metrics straight to your email.
        </p>
    </div>

    <!-- Interactive Form -->
    <form action="#" method="POST" onsubmit="alert('Thank you for subscribing to NeuralPress analytical intelligence updates!'); return false;" class="space-y-2">
        <div class="relative">
            <input type="email" placeholder="Enter security-vetted email" required class="w-full bg-slate-900/60 text-white border border-slate-800 rounded-md py-2.5 px-3.5 text-xs focus:outline-none focus:border-[#bb1919] placeholder-slate-500 font-mono">
        </div>
        <button type="submit" class="w-full bg-[#bb1919] hover:bg-[#801111] text-white font-extrabold py-2.5 px-4 rounded-md text-xs tracking-wider uppercase font-mono transition-colors duration-150 cursor-pointer text-center">
            Subscribe Now
        </button>
    </form>
</div>
