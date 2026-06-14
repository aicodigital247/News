<?php
/**
 * NeuralPress - Article Share Buttons Partial
 */

$shareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
$shareTitle = $post['title'] ?? 'NeuralPress Factual Article';
?>
<div class="flex flex-wrap items-center gap-2 pt-4 border-t border-slate-100 dark:border-slate-800 my-6 font-mono text-xs">
    <span class="text-slate-400 dark:text-slate-500 font-bold uppercase tracking-wider text-[10px] pr-2">Share Bulletin:</span>

    <!-- Facebook Share -->
    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($shareUrl); ?>" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-800 rounded text-[10px] font-bold transition-colors">
        Facebook
    </a>

    <!-- Twitter (X) Share -->
    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($shareUrl); ?>&text=<?php echo urlencode($shareTitle); ?>" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-slate-900 dark:bg-slate-950 text-white hover:bg-black rounded text-[10px] font-bold transition-colors">
        Twitter
    </a>

    <!-- LinkedIn Share -->
    <a href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode($shareUrl); ?>&title=<?php echo urlencode($shareTitle); ?>" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-sky-700 dark:bg-sky-800 text-white hover:bg-sky-800 dark:hover:bg-sky-900 rounded text-[10px] font-bold transition-colors">
        LinkedIn
    </a>

    <!-- Copy URL -->
    <button onclick="navigator.clipboard.writeText('<?php echo $shareUrl; ?>').then(() => alert('Link copied to clipboard!'));" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 rounded text-[10px] font-bold transition-colors cursor-pointer">
        Copy Link
    </button>
</div>
