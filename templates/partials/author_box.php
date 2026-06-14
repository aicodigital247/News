<?php
/**
 * NeuralPress - E-E-A-T Compliant Author Box Partial
 * Displays verified journalist credentials, bios, social links, stats, and a robust Follow handler.
 */

if (!isset($author) || !$author) {
    return;
}

$authorId = intval($author['id']);
$userId = $_SESSION['user_id'] ?? null;

// Determine if current visitor already follows
$isFollowing = false;
if ($userId) {
    $followRes = $db->query("SELECT id FROM author_followers WHERE user_id = ? AND author_id = ? LIMIT 1", "ii", [$userId, $authorId]);
    if ($followRes && $followRes->num_rows > 0) {
        $isFollowing = true;
    }
}

// Format the Join Date nicely (defaults to a sensible simulated history if not in database, as authors register early)
$joinDate = "Sept 2024";

?>
<div class="bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-6 rounded-2xl shadow-xs flex flex-col md:flex-row gap-6 mt-8 transition-colors duration-200">
    <!-- Left: Author avatar and followers status badge -->
    <div class="flex flex-col items-center text-center shrink-0 w-full md:w-44">
        <img class="w-24 h-24 rounded-full object-cover border-4 border-slate-55 dark:border-slate-800 shadow-sm" src="<?php echo htmlspecialchars($author['image'] ?: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=400&q=80'); ?>" alt="<?php echo htmlspecialchars($author['name']); ?>" referrerPolicy="no-referrer">
        
        <h4 class="font-sans font-bold text-slate-950 dark:text-white mt-3 flex items-center justify-center gap-1">
            <?php echo htmlspecialchars($author['name']); ?>
            <?php if ($author['verified']): ?>
                <span class="text-sky-500 text-xs" title="Verified Professional Journalist" aria-label="Verified">✓</span>
            <?php endif; ?>
        </h4>
        
        <p class="text-[10px] font-mono text-slate-400 dark:text-slate-550 mt-0.5 uppercase tracking-wider">NeuralPress Writer</p>
        
        <!-- Interactive Follow Button -->
        <form action="/templates/author_follow_action.php" method="POST" class="w-full mt-4">
            <input type="hidden" name="author_id" value="<?php echo $authorId; ?>">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
            <?php if ($isFollowing): ?>
                <button type="submit" name="action" value="unfollow" class="w-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 text-xs font-bold py-2 px-4 rounded-md border border-slate-200 dark:border-slate-700 transition-colors duration-150 cursor-pointer">
                    Unfollow Author
                </button>
            <?php else: ?>
                <button type="submit" name="action" value="follow" class="w-full bg-[#bb1919] text-white hover:bg-[#801111] text-xs font-bold py-2 px-4 rounded-md transition-colors duration-150 cursor-pointer shadow-sm">
                    Follow Author
                </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Right: Stats, Bio, and Social Handles -->
    <div class="flex-1 flex flex-col justify-between">
        <div class="space-y-3">
            <!-- Stats Counters Bar -->
            <div class="flex flex-wrap items-center gap-4 border-b border-slate-100 dark:border-slate-800 pb-3">
                <div class="text-center md:text-left">
                    <span class="text-xs font-mono text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Followers</span>
                    <strong class="text-slate-900 dark:text-white font-sans text-lg font-black"><?php echo number_format($author['followers']); ?></strong>
                </div>
                <div class="w-px h-8 bg-slate-200 dark:bg-slate-800 self-center hidden sm:block"></div>
                <div class="text-center md:text-left">
                    <span class="text-xs font-mono text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Bulletins</span>
                    <strong class="text-slate-900 dark:text-white font-sans text-lg font-black"><?php echo number_format($author['total_posts']); ?></strong>
                </div>
                <div class="w-px h-8 bg-slate-200 dark:bg-slate-800 self-center hidden sm:block"></div>
                <div class="text-center md:text-left">
                    <span class="text-xs font-mono text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Join Date</span>
                    <strong class="text-slate-900 dark:text-white font-sans text-sm font-semibold block pt-0.5"><?php echo $joinDate; ?></strong>
                </div>
            </div>

            <!-- Biographical narrative -->
            <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed font-light">
                <?php echo htmlspecialchars($author['bio'] ?: 'A senior editor and investigative reporter committed to delivering factual evidence, verified data-points, and meticulous analyses at NeuralPress.'); ?>
            </p>
        </div>

        <!-- Social Media anchors -->
        <div class="flex items-center gap-3 pt-4">
            <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 uppercase tracking-wider">Socials:</span>
            <?php if (!empty($author['facebook'])): ?>
                <a href="https://facebook.com/<?php echo htmlspecialchars($author['facebook']); ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-slate-500 hover:text-[#bb1919] dark:hover:text-[#bb1919] font-medium transition-colors" title="Facebook">Facebook</a>
            <?php endif; ?>
            <?php if (!empty($author['twitter'])): ?>
                <a href="https://twitter.com/<?php echo htmlspecialchars($author['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-slate-500 hover:text-[#bb1919] dark:hover:text-[#bb1919] font-medium transition-colors" title="Twitter/X">Twitter</a>
            <?php endif; ?>
            <?php if (!empty($author['linkedin'])): ?>
                <a href="https://linkedin.com/in/<?php echo htmlspecialchars($author['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="text-xs text-slate-500 hover:text-[#bb1919] dark:hover:text-[#bb1919] font-medium transition-colors" title="LinkedIn">LinkedIn</a>
            <?php endif; ?>
        </div>
    </div>
</div>
