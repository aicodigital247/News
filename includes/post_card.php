<?php
/**
 * NeuralPress - Enterprise Responsive Post Card Widget
 */
if (isset($post)):
    $isHighTrust = ($post['trust_score'] >= 80);
    $trustTextColor = $isHighTrust ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400';
    $trustBgColor = $isHighTrust ? 'bg-emerald-500' : 'bg-amber-500';
    $trustBadgeBorder = $isHighTrust ? 'border-emerald-500/20 bg-emerald-500/5' : 'border-amber-500/20 bg-amber-500/5';
?>
<article class="group bg-white dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800/80 rounded-xl overflow-hidden shadow-xs hover:shadow-lg hover:border-[#bb1919] dark:hover:border-[#bb1919] transition-all duration-300 flex flex-col justify-between transform hover:-translate-y-0.5">
    <div>
        <!-- Card Header Image Thumbnail -->
        <div class="relative h-48 bg-slate-100 dark:bg-slate-950 overflow-hidden shrink-0">
            <img class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500" src="<?php echo htmlspecialchars($post['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1546074177-3e14fb30c515?auto=format&fit=crop&w=800&q=80'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" referrerPolicy="no-referrer">
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-350"></div>
            
            <!-- Category Badge -->
            <span class="absolute top-4 left-4 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xs text-slate-800 dark:text-slate-100 text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded shadow-xs border border-slate-200/50 dark:border-slate-700/50">
                <a href="/category/<?php echo urlencode($post['category']); ?>" class="hover:text-[#bb1919] dark:hover:text-[#bb1919] transition-colors"><?php echo htmlspecialchars($post['category']); ?></a>
            </span>
        </div>

        <!-- Card Content Block -->
        <div class="p-5 space-y-3">
            <div class="flex items-center justify-between text-[10px] font-mono tracking-wider text-slate-400 dark:text-slate-500 uppercase">
                <span>By Journalist Node</span>
                <span><?php echo \NeuralPress\Core\Helpers::formatRelativeTime($post['created_at']); ?></span>
            </div>
            
            <h3 class="font-extrabold text-base tracking-tight leading-tight text-slate-900 dark:text-white group-hover:text-[#bb1919] dark:group-hover:text-[#bb1919] transition-colors line-clamp-2">
                <a href="/news/<?php echo htmlspecialchars($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a>
            </h3>
            
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed font-light line-clamp-3">
                <?php echo htmlspecialchars(\NeuralPress\Core\Helpers::truncate($post['summary'], 115)); ?>
            </p>
        </div>
    </div>

    <!-- Card Bottom Audit Ratings Deck -->
    <div class="px-5 pb-5 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/10">
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] font-mono font-bold tracking-tight <?php echo $trustBadgeBorder . ' ' . $trustTextColor; ?>">
            <span class="w-1.5 h-1.5 rounded-full <?php echo $trustBgColor; ?> animate-pulse"></span>
            Trust rated: <?php echo intval($post['trust_score']); ?>%
        </span>
        <a href="/news/<?php echo htmlspecialchars($post['slug']); ?>" class="text-[10px] font-mono font-bold tracking-wider uppercase text-slate-400 group-hover:text-[#bb1919] dark:group-hover:text-[#bb1919] transition-colors inline-flex items-center gap-1">
            Read bulletin ➔
        </a>
    </div>
</article>
<?php endif; ?>

