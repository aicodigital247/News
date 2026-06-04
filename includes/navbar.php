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
            <a href="/" class="hover:opacity-100 transition">Home</a>
            <a href="/category/World" class="hover:opacity-100 transition">World</a>
            <a href="/category/Business" class="hover:opacity-100 transition">Business</a>
            <a href="/category/Technology" class="hover:opacity-100 transition">Tech</a>
            <a href="/category/Sports" class="hover:opacity-100 transition">Sports</a>
        </nav>
        <div class="ml-auto flex items-center gap-4 hidden md:flex">
            <span class="bg-white/10 px-3 py-1 rounded-full text-xs font-mono border border-white/20">PHP v8.2</span>
        </div>
    </div>
</header>
