<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - NeuralPress' : 'NeuralPress - AI automated global news network'; ?></title>
    <?php if (isset($pageDescription)): ?>
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <?php endif; ?>
    <?php if (isset($pageKeywords)): ?>
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="/assets/css/tailwind.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Helvetica+Neue:wght@400;500;700;900&family=JetBrains+Mono:wght@400;500&display=swap');

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
            box-shadow: none !important;
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
            // Unset current translating cookie to return to native English
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
        });
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit" defer></script>
</head>
<body class="bg-[#f3f4f6] text-[#1a202c] flex flex-col min-h-screen font-sans">
