<?php
/**
 * NeuralPress - Enterprise Sticky Navbar & Mega Menu Layout
 * Complete with Live Client-Side Controllers, Search/Notification Bells, PWA Trigger, and Mobile Sliding Drawer.
 */

use NeuralPress\Core\Database;

$db = Database::getInstance();

// Retrieve top categories with count of published articles
$navbarCatCountRes = $db->query("SELECT category, COUNT(*) as cnt FROM posts WHERE status = 'published' GROUP BY category LIMIT 5");
$navbarCats = [];
if ($navbarCatCountRes) {
    while ($row = $navbarCatCountRes->fetch_assoc()) {
        $navbarCats[] = $row;
    }
}
if (empty($navbarCats)) {
    $navbarCats = [
        ['category' => 'AI', 'cnt' => 14],
        ['category' => 'Technology', 'cnt' => 21],
        ['category' => 'Sports', 'cnt' => 9],
        ['category' => 'Politics', 'cnt' => 12],
        ['category' => 'Business', 'cnt' => 15]
    ];
}

// Fetch some popular items for the mega-menu block dynamically
$megaPopularRes = $db->query("SELECT title, slug, thumbnail_url, category FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 3");
$megaPopular = [];
if ($megaPopularRes) {
    while ($p = $megaPopularRes->fetch_assoc()) {
        $megaPopular[] = $p;
    }
}
?>
<!-- Top Tickers and Utilities Bar (Non-Sticky, scrolls away) -->
<div class="bg-slate-950 text-white border-b border-slate-900 text-[11px] flex items-center justify-between h-9 px-4 sm:px-6 shrink-0 relative z-30">
    <div class="flex items-center min-w-0 flex-1">
        <span class="bg-[#bb1919] text-white font-mono font-black px-2 py-0.5 text-[9px] mr-3 rounded-xs uppercase tracking-wider shrink-0 animate-pulse">BREAKING ALERT</span>
        <div class="text-slate-300 truncate font-sans font-medium text-[11px]">
            <?php require_once NP_DIR . '/includes/breaking_news.php'; ?>
        </div>
    </div>
    
    <!-- Translation, Saved, and Stats Info (Desktop) -->
    <div class="hidden md:flex items-center gap-4 shrink-0 font-mono text-[10px] text-slate-400 pl-4">
        <span>UTC 2026</span>
        <div class="flex items-center gap-1 bg-slate-900 border border-slate-800 rounded px-1.5 py-0.5">
            <span class="opacity-60">Lang:</span>
            <select id="np_lang_selector" onchange="translateLanguage(this.value)" class="bg-transparent border-0 text-[10px] text-white outline-none cursor-pointer font-bold font-sans">
                <option value="en" class="bg-slate-950 text-white">English</option>
                <option value="fr" class="bg-slate-950 text-white">Français</option>
                <option value="es" class="bg-slate-950 text-white">Español</option>
                <option value="ar" class="bg-slate-950 text-white">العربية</option>
                <option value="ha" class="bg-slate-950 text-white font-bold">Hausa</option>
                <option value="yo" class="bg-slate-950 text-white font-bold">Yoruba</option>
                <option value="ig" class="bg-slate-950 text-white font-bold">Igbo</option>
            </select>
        </div>
    </div>
</div>

<!-- Main Enterprise Sticky Navigation Header -->
<header id="np_main_header" class="sticky top-0 z-50 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 shadow-sm transition-all duration-300">
    <!-- Active Reading Progress Bar (for Articles) -->
    <div id="reading_progress_container" class="absolute bottom-0 left-0 w-full h-[3px] bg-transparent">
        <div id="reading_progress_bar"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
        
        <!-- Left: Branding Logo -->
        <a href="/" class="flex items-center gap-2 select-none group font-sans">
            <div class="flex gap-1">
                <span class="bg-[#bb1919] text-white text-base font-black px-2 py-0.5 rounded-sm shadow-sm group-hover:scale-105 transition-transform">N</span>
                <span class="bg-slate-900 dark:bg-slate-800 text-white text-base font-black px-2 py-0.5 rounded-sm shadow-sm group-hover:scale-105 transition-transform">P</span>
            </div>
            <span class="font-black text-lg tracking-tighter text-slate-950 dark:text-white hidden sm:inline">NEURAL<span class="text-[#bb1919]">PRESS</span></span>
        </a>

        <!-- Center: Desktop Menu with Dropdowns & Mega Menu capabilities -->
        <nav class="hidden lg:flex items-center gap-1 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
            <a href="/" class="px-3.5 py-5 hover:text-[#bb1919] transition-colors">Home</a>
            
            <!-- Category Mega Menu Hover Box -->
            <div class="group/mega relative py-5">
                <button class="px-3.5 flex items-center gap-1 hover:text-[#bb1919] transition-colors focus:outline-none cursor-pointer">
                    Categories ▾
                </button>
                <!-- Mega Dropdown Panel -->
                <div class="absolute left-1/2 -translate-x-[45%] top-full w-[640px] bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg p-6 grid grid-cols-12 gap-6 opacity-0 pointer-events-none group-hover/mega:opacity-100 group-hover/mega:pointer-events-auto transition-all duration-200 mt-1 transform translate-y-2 group-hover/mega:translate-y-0 text-left">
                    
                    <!-- Left: Subcategories list with counts -->
                    <div class="col-span-5 space-y-3">
                        <h4 class="text-[10px] font-mono tracking-widest text-[#bb1919] font-black uppercase border-b border-slate-100 dark:border-slate-800 pb-1.5">Top Channels</h4>
                        <div class="flex flex-col gap-1 text-xs font-semibold">
                            <?php foreach ($navbarCats as $cat): ?>
                                <a href="/category/<?php echo urlencode($cat['category']); ?>" class="flex items-center justify-between p-2 rounded hover:bg-slate-50 dark:hover:bg-slate-900 transition text-slate-700 dark:text-slate-400 hover:text-[#bb1919] dark:hover:text-[#bb1919]">
                                    <span><?php echo htmlspecialchars($cat['category']); ?></span>
                                    <span class="text-[10px] bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 font-normal px-1.5 py-0.5 rounded"><?php echo $cat['cnt']; ?> posts</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Right: Featured popular stories inside the dropdown -->
                    <div class="col-span-7 space-y-3 border-l border-slate-100 dark:border-slate-800 pl-6">
                        <h4 class="text-[10px] font-mono tracking-widest text-slate-400 font-black uppercase border-b border-slate-100 dark:border-slate-800 pb-1.5">Enterprise Picks</h4>
                        <div class="space-y-3.5">
                            <?php foreach ($megaPopular as $item): ?>
                                <a href="/news/<?php echo htmlspecialchars($item['slug']); ?>" class="flex gap-2.5 group/item items-start">
                                    <img class="w-14 h-10 object-cover rounded bg-slate-100 shrink-0 border border-slate-200 dark:border-slate-800" src="<?php echo htmlspecialchars($item['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=150&q=80'); ?>" alt="thumb" referrerPolicy="no-referrer">
                                    <div class="min-w-0">
                                        <span class="text-[9px] font-bold text-[#bb1919] uppercase block mb-0.5"><?php echo htmlspecialchars($item['category']); ?></span>
                                        <h5 class="text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-300 group-hover/item:text-[#bb1919] line-clamp-2 leading-tight normal-case"><?php echo htmlspecialchars($item['title']); ?></h5>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <a href="/category/AI" class="px-3.5 py-5 hover:text-[#bb1919] transition-colors flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-[#bb1919] animate-ping"></span> AI
            </a>
            <a href="/category/Politics" class="px-3.5 py-5 hover:text-[#bb1919] transition-colors">Politics</a>
            <a href="/category/Sports" class="px-3.5 py-5 hover:text-[#bb1919] transition-colors">Sports</a>
            <a href="/category/Business" class="px-3.5 py-5 hover:text-[#bb1919] transition-colors">Finance</a>
        </nav>

        <!-- Right Side Controls (Dark Mode, Notifications, Search Panel, Mobile Drawer Hamburger) -->
        <div class="flex items-center gap-2.5 sm:gap-4 shrink-0 font-sans">
            
            <!-- PWA install indicator badge -->
            <button id="pwa_install_btn" class="hidden md:flex items-center gap-1 bg-[#bb1919]/10 text-[#bb1919] border border-[#bb1919]/20 hover:bg-[#bb1919]/20 text-[10px] font-mono tracking-wider uppercase font-black px-2.5 py-1.5 rounded transition cursor-pointer" title="Install Web App Service">
                📥 Install App
            </button>

            <!-- Search Button (toggles local inline input dropdown) -->
            <button onclick="toggleHeaderSearch()" class="p-2 text-slate-500 hover:text-[#bb1919] dark:text-slate-400 dark:hover:text-white transition cursor-pointer relative" aria-label="Search">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>

            <!-- Notification Bell Icon and Dropdown Counter -->
            <div class="relative">
                <button onclick="toggleHeaderNotifications()" class="p-2 text-slate-500 hover:text-[#bb1919] dark:text-slate-400 dark:hover:text-white transition cursor-pointer relative" aria-label="Notifications Alerts">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-[#bb1919] border-2 border-white dark:border-slate-900 rounded-full"></span>
                </button>
                <!-- Notifications Popup Menu -->
                <div id="np_notifications_popup" class="hidden absolute right-0 top-11 w-76 bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-xl rounded-lg p-4 text-xs font-sans space-y-3 z-50">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-2 mb-1">
                        <strong class="font-extrabold uppercase tracking-widest text-[#bb1919] font-mono text-[10px]">Security Signals</strong>
                        <button onclick="resetNotifications()" class="text-slate-400 hover:text-[#bb1919] text-[10px] font-mono cursor-pointer">Clear</button>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-60 overflow-y-auto">
                        <div class="py-2.5 space-y-1">
                            <span class="bg-[#bb1919]/5 text-[#bb1919] font-mono text-[9px] font-bold px-1.5 py-0.5 rounded">BREAKING</span>
                            <p class="text-slate-600 dark:text-slate-300 font-bold tracking-tight">E-E-A-T Journalist Node followed successfully.</p>
                            <span class="text-[9px] font-mono text-slate-400">Just now</span>
                        </div>
                        <div class="py-2.5 space-y-1">
                            <span class="bg-blue-500/5 text-blue-500 font-mono text-[9px] font-bold px-1.5 py-0.5 rounded">DIGEST</span>
                            <p class="text-slate-500 dark:text-slate-400">Daily verification heuristics scanning completed. System integrity checks score: 100%.</p>
                            <span class="text-[9px] font-mono text-slate-400">12 mins ago</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dark Mode Toggle Button (Solar/Lunar vanilla JS state) -->
            <button onclick="toggleDarkMode()" class="p-2 text-slate-500 hover:text-[#bb1919] dark:text-slate-400 dark:hover:text-white transition cursor-pointer rounded-full" aria-label="Toggle Night Mode">
                <!-- Sun Icon (visible in Dark Mode) -->
                <svg id="theme_sun" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <!-- Moon Icon (visible in Light Mode) -->
                <svg id="theme_moon" class="w-5 h-5 text-slate-600 dark:text-slate-200" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>

            <!-- Mobile sliding drawer trigger (Hamburger) -->
            <button onclick="toggleMobileDrawer(true)" class="lg:hidden p-2 text-slate-700 dark:text-white hover:text-[#bb1919] transition cursor-pointer" aria-label="Menu Mobile">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>

        </div>
    </div>

    <!-- Active Search Overlay Dropdown (Animated) -->
    <div id="np_header_search_bar" class="hidden bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 transition-all duration-300">
        <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between gap-4">
            <form action="/search" method="GET" class="flex-1 flex items-center relative">
                <span class="absolute left-3.5 text-slate-400">🔍</span>
                <input type="text" name="q" placeholder="Type keywords, topics or slugs..." id="header_search_input" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-md py-2.5 pl-10 pr-4 text-xs font-mono text-slate-950 dark:text-white focus:outline-none focus:border-[#bb1919] placeholder-slate-450">
            </form>
            <button onclick="toggleHeaderSearch()" class="text-xs font-mono font-bold tracking-tight text-slate-400 hover:text-[#bb1919] cursor-pointer">CLOSE</button>
        </div>
    </div>
</header>

<!-- Mobile Slide-In Nav Drawer Menu -->
<div id="np_mobile_drawer_backdrop" class="fixed inset-0 bg-black/60 backdrop-blur-xs opacity-0 pointer-events-none transition-opacity duration-300 z-50" onclick="toggleMobileDrawer(false)"></div>
<div id="np_mobile_drawer" class="fixed top-0 right-0 h-full w-80 bg-white dark:bg-slate-950 border-l border-slate-200 dark:border-slate-800 shadow-2xl p-6 flex flex-col justify-between transform translate-x-full transition-transform duration-300 ease-out z-50 text-slate-900 dark:text-white">
    <div class="space-y-6">
        <!-- Close button & header logo -->
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
            <span class="text-white font-black tracking-tighter text-md flex items-center gap-1.5 select-none">
                <span class="bg-[#bb1919] text-white px-2 py-0.5 rounded leading-none">N</span>
                <span class="text-slate-900 dark:text-white font-extrabold uppercase">NeuralPress</span>
            </span>
            <button onclick="toggleMobileDrawer(false)" class="text-slate-400 hover:text-[#bb1919] cursor-pointer" aria-label="Close Mobile Drawer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Search Bar Inside Drawer -->
        <div>
            <form action="/search" method="GET" class="relative flex items-center">
                <input type="text" name="q" placeholder="Search news feeds..." class="w-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded py-2 pl-3 pr-8 text-xs font-mono placeholder-slate-400 text-slate-950 dark:text-white focus:outline-none">
                <button type="submit" class="absolute right-3 text-slate-400">🔍</button>
            </form>
        </div>

        <!-- Dynamic Category Navigation Logs inside slide layout -->
        <div class="space-y-4">
            <h4 class="text-[10px] font-mono tracking-widest text-[#bb1919] font-bold uppercase">Main Sections</h4>
            <div class="flex flex-col gap-2.5 font-bold uppercase tracking-wide text-sm">
                <a href="/" class="hover:text-[#bb1919]">Home</a>
                <a href="/category/World" class="hover:text-[#bb1919]">World news</a>
                <a href="/category/Business" class="hover:text-[#bb1919]">Business & Corporate</a>
                <a href="/category/Technology" class="hover:text-[#bb1919]">Technology & AI</a>
                <a href="/category/Sports" class="hover:text-[#bb1919]">Sports Network</a>
            </div>
        </div>

        <!-- System controls and configuration flags inside drawer -->
        <div class="space-y-4 pt-4 border-t border-slate-100 dark:border-slate-800">
            <h4 class="text-[10px] font-mono tracking-widest text-slate-400 font-bold uppercase">Preferences</h4>
            <!-- Mobile Translation Widget -->
            <div class="flex items-center justify-between text-xs">
                <span>Select Language</span>
                <select id="np_lang_selector_mobile" onchange="translateLanguage(this.value)" class="bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 px-2 py-1 rounded text-xs outline-none text-slate-950 dark:text-white cursor-pointer font-sans font-bold">
                    <option value="en">English</option>
                    <option value="fr">Français</option>
                    <option value="es">Español</option>
                    <option value="ar">العربية</option>
                    <option value="ha">Hausa</option>
                    <option value="yo">Yoruba</option>
                    <option value="ig">Igbo</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Foot credits -->
    <div class="text-[10px] font-mono text-slate-400 mt-6 pt-3 border-t border-slate-100 dark:border-slate-800">
        © 2026 NeuralPress Multi-Channel Feed. Built with modern, compliant E-E-A-T framework protocols.
    </div>
</div>

<!-- Header Client Interaction & State Script -->
<script>
    // Theme Initializer and Sync
    function updateThemeIcons() {
        const isDark = document.documentElement.classList.contains('dark');
        const sun = document.getElementById('theme_sun');
        const moon = document.getElementById('theme_moon');
        if (isDark) {
            sun?.classList.remove('hidden');
            moon?.classList.add('hidden');
        } else {
            sun?.classList.add('hidden');
            moon?.classList.remove('hidden');
        }
    }

    function toggleDarkMode() {
        const root = document.documentElement;
        if (root.classList.contains('dark')) {
            root.classList.remove('dark');
            localStorage.setItem('np_theme', 'light');
        } else {
            root.classList.add('dark');
            localStorage.setItem('np_theme', 'dark');
        }
        updateThemeIcons();
    }

    // Call updates on load immediately to matches DOM setup
    document.addEventListener("DOMContentLoaded", function() {
        updateThemeIcons();
    });

    // Mobile slide out drawer toggle action
    function toggleMobileDrawer(show) {
        const drawer = document.getElementById('np_mobile_drawer');
        const backdrop = document.getElementById('np_mobile_drawer_backdrop');
        if (show) {
            drawer.classList.remove('translate-x-full');
            backdrop.classList.remove('opacity-0', 'pointer-events-none');
        } else {
            drawer.classList.add('translate-x-full');
            backdrop.classList.add('opacity-0', 'pointer-events-none');
        }
    }

    // Header Search box controller
    function toggleHeaderSearch() {
        const searchBar = document.getElementById('np_header_search_bar');
        const searchInput = document.getElementById('header_search_input');
        if (searchBar.classList.contains('hidden')) {
            searchBar.classList.remove('hidden');
            searchInput.focus();
        } else {
            searchBar.classList.add('hidden');
        }
    }

    // Notification Menu popup toggler
    function toggleHeaderNotifications() {
        const popup = document.getElementById('np_notifications_popup');
        popup.classList.toggle('hidden');
    }

    function resetNotifications() {
        const notifyBlock = document.getElementById('np_notifications_popup');
        alert("Notifications cleared successfully!");
        notifyBlock.classList.add('hidden');
    }

    // Programmatic PWA beforeinstallprompt trigger interceptor
    let deferredPrompt;
    const pwaBtn = document.getElementById('pwa_install_btn');
    
    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevent default browser install banner
        e.preventDefault();
        deferredPrompt = e;
        // Show our clean install button in high visibility space
        if (pwaBtn) {
            pwaBtn.classList.remove('hidden');
        }
    });

    if (pwaBtn) {
        pwaBtn.addEventListener('click', async () => {
            if (!deferredPrompt) {
                alert("The web app is already running under active service caching or PWA isn't fully promptable on your device.");
                return;
            }
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            console.log(`User response to install prompt: ${outcome}`);
            deferredPrompt = null;
            pwaBtn.classList.add('hidden');
        });
    }

    // Dynamic sticky header style changes on scroll
    window.addEventListener('scroll', () => {
        const header = document.getElementById('np_main_header');
        if (window.scrollY > 40) {
            header.classList.add('py-1', 'shadow-md');
        } else {
            header.classList.remove('py-1', 'shadow-md');
        }
    });
</script>

