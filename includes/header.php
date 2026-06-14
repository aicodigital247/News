<!DOCTYPE html>
<html lang="en" class="transition-colors duration-200">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - NeuralPress' : 'NeuralPress - AI automated global news network'; ?></title>
    
    <!-- PWA declarations -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#bb1919">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="NeuralPress">
    <link rel="apple-touch-icon" href="https://images.unsplash.com/photo-1504711434969-e33886168f5c?auto=format&fit=crop&w=192&h=192&q=80">

    <?php require_once NP_DIR . '/includes/seo.php'; ?>
    <?php require_once NP_DIR . '/includes/schema.php'; ?>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        // Support dark mode with Tailwind CDN
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#bb1919',
                            darkred: '#801111'
                        }
                    }
                }
            }
        }

        // Inline dark-mode hydration handler (prevents flashing)
        if (localStorage.getItem('np_theme') === 'dark' || (!('np_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap');

        /* Scroll progress bar style */
        #reading_progress_bar {
            height: 3px;
            background: linear-gradient(to right, #bb1919, #e53e3e);
            width: 0%;
            transition: width 0.1s ease-out;
        }

        /* Complete suppression of native Google Translate banner and overlays */
        .goog-te-banner-frame, 
        .goog-te-banner-frame.skiptranslate, 
        .goog-te-banner, 
        iframe.goog-te-banner-frame,
        #goog-gt-tt, 
        .goog-te-balloon-frame {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            opacity: 0 !important;
        }
        body {
            top: 0px !important;
            position: static !important;
        }
        .goog-tooltip, .goog-tooltip:hover {
            display: none !important;
            visibility: hidden !important;
        }
        .goog-text-highlight {
            background-color: transparent !important;
            box-shadow: none !important;
        }

        /* Fluid scrolling and layout resets */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .dark ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #475569;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #bb1919;
        }
    </style>
    
    <!-- Programmatic Google Translate Bootstrap and Cookie Handler -->
    <div id="google_translate_element" style="display:none !important;"></div>
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,fr,es,ar,ha,yo,ig',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
        }

        function getTranslationCookie() {
            var match = document.cookie.match(new RegExp('(^| )googtrans=([^;]+)'));
            if (match) return match[2];
            return null;
        }

        function translateLanguage(langCode) {
            var domain = window.location.hostname;
            if (langCode === 'en') {
                document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + domain;
                document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=." + domain;
            } else {
                var value = "/en/" + langCode;
                document.cookie = "googtrans=" + value + "; path=/;";
                document.cookie = "googtrans=" + value + "; path=/; domain=" + domain;
                document.cookie = "googtrans=" + value + "; path=/; domain=." + domain;
            }
            window.location.reload();
        }

        document.addEventListener("DOMContentLoaded", function() {
            var langCookie = getTranslationCookie();
            if (langCookie) {
                var parts = langCookie.split('/');
                var lang = parts[parts.length - 1];
                var select = document.getElementById('np_lang_selector');
                if (select && lang) {
                    select.value = lang;
                }
            }

            // Register PWA service worker
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js')
                    .then(function(reg) {
                        console.log('NeuralPress PWA Service Worker Registered', reg.scope);
                    })
                    .catch(function(err) {
                        console.warn('NeuralPress PWA Service Worker Registration Failed', err);
                    });
            }
        });
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
</head>
<body class="bg-[#f8fafc] text-[#0f172a] dark:bg-[#0f172a] dark:text-[#f8fafc] flex flex-col min-h-screen font-sans antialiased text-sm">

