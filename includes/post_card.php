<?php
/**
 * NeuralPress - Post Card Partial Widget
 */
if (isset($post)):
?>
<article class="bg-white border border-[#e2e8f0] rounded overflow-hidden shadow-sm hover:shadow transition flex flex-col justify-between">
    <div>
        <div class="relative h-48 bg-slate-900 overflow-hidden">
            <img class="w-full h-full object-cover hover:scale-105 transition duration-300" src="<?php echo htmlspecialchars($post['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1546074177-3e14fb30c515?auto=format&fit=crop&w=800&q=80'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" referrerPolicy="no-referrer">
            <span class="absolute top-3 left-3 bg-[#bb1919] text-white text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-xs">
                <?php echo htmlspecialchars($post['category']); ?>
            </span>
        </div>
        <div class="p-4 space-y-2">
            <div class="flex items-center justify-between text-[11px] font-mono text-gray-400">
                <span>By Journalist Node</span>
                <span><?php echo \NeuralPress\Core\Helpers::formatRelativeTime($post['created_at']); ?></span>
            </div>
            <h3 class="font-bold text-base leading-tight hover:text-[#bb1919] transition">
                <a href="/news/<?php echo htmlspecialchars($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a>
            </h3>
            <p class="text-xs text-gray-500 leading-relaxed font-light">
                <?php echo htmlspecialchars(\NeuralPress\Core\Helpers::truncate($post['summary'], 100)); ?>
            </p>
        </div>
    </div>
    <div class="px-4 pb-4 pt-2 border-t border-gray-100 flex items-center justify-between">
        <span class="inline-flex items-center gap-1.5 text-xs font-mono font-medium <?php echo $post['trust_score'] >= 80 ? 'text-emerald-600' : 'text-amber-500'; ?>">
            <span class="w-2 h-2 rounded-full <?php echo $post['trust_score'] >= 80 ? 'bg-emerald-500' : 'bg-amber-400'; ?>"></span>
            Trust Rated: <?php echo intval($post['trust_score']); ?>%
        </span>
    </div>
</article>
<?php endif; ?>
