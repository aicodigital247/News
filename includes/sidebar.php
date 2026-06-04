<?php
/**
 * NeuralPress - Sidebar Widget Inclusion list
 */
?>
<aside class="space-y-6">
    <!-- Inline Ad Monetisation -->
    <?php require_once __DIR__ . '/ad_slots.php'; render_ad('sidebar'); ?>

    <!-- Trending widget -->
    <div class="bg-white border border-[#e2e8f0] p-4 rounded shadow-sm">
        <h3 class="sidebar-title">Trending Now</h3>
        <div class="mt-3">
            <?php require_once __DIR__ . '/trending_box.php'; ?>
        </div>
    </div>
</aside>
