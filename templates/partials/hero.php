<?php
/**
 * NeuralPress - Spotlight/Hero visual deck partial
 */
use NeuralPress\Core\Database;

$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY views DESC LIMIT 1");
$heroPost = ($res) ? $res->fetch_assoc() : null;

if ($heroPost):
    $isHighTrust = ($heroPost['trust_score'] >= 80);
    $trustTextColor = $isHighTrust ? 'text-emerald-400' : 'text-amber-400';
    $trustBgColor = $isHighTrust ? 'bg-emerald-500' : 'bg-amber-500';
?>
<div class="group bg-slate-950 text-white rounded-2xl overflow-hidden grid grid-cols-1 md:grid-cols-12 shadow-xl mb-10 border border-slate-900 transition-all duration-300 hover:border-[#bb1919]/50">
    <div class="relative h-64 md:h-[380px] md:col-span-7 bg-slate-900 overflow-hidden">
        <img class="w-full h-full object-cover group-hover:scale-[1.015] transition-transform duration-700" src="<?php echo htmlspecialchars($heroPost['thumbnail_url'] ?: 'https://images.unsplash.com/photo-1546074177-3e14fb30c515?auto=format&fit=crop&w=1200&q=80'); ?>" alt="Spotlight Asset" referrerPolicy="no-referrer">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
        <span class="absolute top-5 left-5 bg-[#bb1919] text-white text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-md shadow-lg">SPOTLIGHT</span>
    </div>
    
    <div class="p-6 sm:p-10 md:col-span-5 flex flex-col justify-between space-y-6 bg-slate-950">
        <div class="space-y-4">
            <span class="text-[9px] font-mono uppercase tracking-widest text-[#bb1919] font-black block">// SPARK AUDITED BULLETIN REPORT</span>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight group-hover:text-[#bb1919] transition-colors duration-300">
                <a href="/news/<?php echo htmlspecialchars($heroPost['slug']); ?>"><?php echo htmlspecialchars($heroPost['title']); ?></a>
            </h1>
            <p class="text-xs text-slate-350 leading-relaxed font-light">
                <?php echo htmlspecialchars(\NeuralPress\Core\Helpers::truncate($heroPost['summary'] ?: $heroPost['content'], 210)); ?>
            </p>
        </div>
        
        <div class="pt-5 border-t border-slate-900 flex items-center justify-between text-xs font-mono text-slate-500">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-[#bb1919] flex items-center justify-center font-bold text-[10px] text-white">N</div>
                <span class="font-bold text-slate-400">Global Editor Desk</span>
            </div>
            
            <div class="flex items-center gap-1.5 px-2.5 py-1 rounded border border-slate-800 bg-slate-900/30">
                <span class="w-1.5 h-1.5 rounded-full <?php echo $trustBgColor; ?> animate-pulse"></span>
                <span class="<?php echo $trustTextColor; ?> font-bold text-[10px]">VERIFIED <?php echo intval($heroPost['trust_score']); ?>%</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

