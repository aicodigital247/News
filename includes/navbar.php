<div class="bg-black text-[13px] text-white flex items-center h-10 px-6 shrink-0 z-20">
    <span class="bg-[#bb1919] text-white font-bold px-2.5 py-0.5 text-xs mr-4 shrink-0 flex items-center gap-1.5 uppercase tracking-wide animate-pulse">BREAKING</span>
    <div class="text-[11px] text-gray-300 truncate">
        <?php require_once __DIR__ . '/breaking_news.php'; ?>
    </div>
</div>

<header class="bg-[#bb1919] text-white h-16 flex items-center px-6 shrink-0 shadow-md">
    <div class="max-w-7xl mx-auto w-full flex items-center justify-between gap-6">
        <a href="/" class="text-2xl font-black tracking-tighter flex items-center gap-2 select-none">
            <div class="bg-white text-[#bb1919] px-1.5 py-0.5 leading-none font-bold">N</div>
            <div class="bg-white text-[#bb1919] px-1.5 py-0.5 leading-none font-bold">P</div>
            <span>NEURALPRESS</span>
        </a>
        <nav class="flex gap-6 text-sm font-bold uppercase tracking-wide">
            <a href="<?php echo UrlManager::getHomeUrl(); ?>" class="hover:opacity-100 transition">Home</a>
            <a href="<?php echo UrlManager::getCategoryUrl('World'); ?>" class="hover:opacity-100 transition">World</a>
            <a href="<?php echo UrlManager::getCategoryUrl('Business'); ?>" class="hover:opacity-100 transition">Business</a>
            <a href="<?php echo UrlManager::getCategoryUrl('Technology'); ?>" class="hover:opacity-100 transition">Tech</a>
            <a href="<?php echo UrlManager::getCategoryUrl('Sports'); ?>" class="hover:opacity-100 transition">Sports</a>
        </nav>
        <div class="ml-auto flex items-center gap-4">
            <div class="flex items-center gap-1.5 bg-white/5 border border-white/10 px-2 py-1 rounded shadow-inner">
                <span class="text-[10px] uppercase font-mono tracking-wider opacity-60 text-white">Translate:</span>
                <select id="np_lang_selector" onchange="translateLanguage(this.value)" class="bg-transparent border-0 px-1 py-0.5 text-xs text-white outline-none focus:outline-none focus:ring-0 cursor-pointer font-bold font-sans">
                    <option value="en" class="bg-slate-900 text-white font-bold">English</option>
                    <option value="fr" class="bg-slate-900 text-white font-bold">Français</option>
                    <option value="es" class="bg-slate-900 text-white font-bold">Español</option>
                    <option value="ar" class="bg-slate-900 text-white font-bold">العربية</option>
                    <option value="ha" class="bg-slate-900 text-white font-bold">Hausa</option>
                    <option value="yo" class="bg-slate-900 text-white font-bold">Yoruba</option>
                    <option value="ig" class="bg-slate-900 text-white font-bold">Igbo</option>
                </select>
            </div>
            <span class="bg-white/10 px-3 py-1 rounded-full text-xs font-mono border border-white/20 hidden md:inline">PHP v8.2</span>
        </div>
    </div>
</header>
