    <footer class="bg-slate-950 text-slate-400 mt-16 py-12 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <span class="text-white font-black tracking-tighter text-lg flex items-center gap-1.5">
                    <span class="bg-white text-black px-1.5 py-0.5 leading-none font-bold rounded-xs">N</span> NeuralPress
                </span>
                <p class="text-xs mt-3 leading-relaxed text-slate-400">
                    Automated, trust-rated natural news broadcast network utilizing secure, auditable LLM processing and fact verification pipelines.
                </p>
            </div>
            <div>
                <h4 class="text-white text-xs font-mono uppercase tracking-widest mb-4 font-bold">Sections</h4>
                <ul class="text-xs space-y-2">
                    <li><a href="<?php echo UrlManager::getCategoryUrl('World'); ?>" class="hover:text-white transition">World News</a></li>
                    <li><a href="<?php echo UrlManager::getCategoryUrl('Business'); ?>" class="hover:text-white transition">Business & Finance</a></li>
                    <li><a href="<?php echo UrlManager::getCategoryUrl('Technology'); ?>" class="hover:text-white transition">Science & Technology</a></li>
                    <li><a href="<?php echo UrlManager::getCategoryUrl('Sports'); ?>" class="hover:text-white transition">Global Sports</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-xs font-mono uppercase tracking-widest mb-4 font-bold">Enterprise Access</h4>
                <p class="text-xs leading-relaxed text-slate-400">
                    Fully optimized MySQLi schema. 100% prepared bindings. Engineered for strict corporate auditing systems.
                </p>
                <div class="mt-4">
                    <a href="/admin/login" class="inline-block bg-[#bb1919] hover:bg-[#801111] text-white text-[10px] font-mono font-bold uppercase tracking-wider px-4 py-2.5 transition duration-150 rounded-md shadow-sm">CMS Dashboard Login</a>
                </div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 mt-10 pt-6 border-t border-slate-900 text-center text-[10px] font-mono text-slate-500">
            © 2026 NeuralPress AI Automated Newsroom CMS. BBC Pixel-Perfect Frontend Model layer. All rights reserved.
        </div>
    </footer>
    <!-- Client Scripts for NeuralPress Node Streams -->
</body>
</html>
