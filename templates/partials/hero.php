<?php
/**
 * NeuralPress - Spotlight/Hero visual deck partial
 */
use NeuralPress\Core\Database;

$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 1");
$heroPost = ($res) ? $res->fetch_assoc() : null;

if ($heroPost):
?>
<div class="bg-black text-white rounded-lg overflow-hidden grid grid-cols-1 md:grid-cols-2 shadow-md mb-8">
    <div class="relative h-64 md:h-full bg-slate-900">
        <img class="w-full h-full object-cover" src="<?php echo htmlspecialchars($heroPost['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1546074177-3e14fb30c515?auto=format&fit=crop&w=800&q=80'); ?>" alt="Spotlight Asset" referrerPolicy="no-referrer">
        <span class="absolute top-4 left-4 bg-[#bb1919] text-white text-xs font-bold uppercase tracking-widest px-3 py-1 rounded-xs">SPOTLIGHT</span>
    </div>
    <div class="p-6 md:p-10 flex flex-col justify-between space-y-4">
        <div class="space-y-3">
            <span class="text-[10px] font-mono uppercase tracking-widest text-[#bb1919] font-extrabold">// SPOTLIGHT VERIFIED INVESTIGATION</span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight hover:text-gray-300 transition">
                <a href="/news/<?php echo htmlspecialchars($heroPost['slug']); ?>"><?php echo htmlspecialchars($heroPost['title']); ?></a>
            </h1>
            <p class="text-xs text-gray-400 leading-relaxed font-light">
                <?php echo htmlspecialchars(\NeuralPress\Core\Helpers::truncate($heroPost['summary'] ?: $heroPost['content'], 180)); ?>
            </p>
        </div>
        <div class="pt-4 border-t border-gray-800 flex items-center justify-between text-xs font-mono text-gray-500">
            <span>By Global News Desk</span>
            <span class="text-emerald-500 font-bold">✓ Fact verified: <?php echo intval($heroPost['trust_score']); ?>%</span>
        </div>
    </div>
</div>
<?php endif; ?>
