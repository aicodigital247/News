<?php
/**
 * NeuralPress - Homepage Template
 */
require_once NP_DIR . '/includes/header.php';
require_once NP_DIR . '/includes/navbar.php';
?>

<main class="max-w-7xl mx-auto px-6 py-8">
    <!-- Inline Ad Banner -->
    <?php render_ad('header_banner'); ?>

    <!-- Hero investigation deck -->
    <?php require_once NP_DIR . '/templates/partials/hero.php'; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-8">
        <!-- Primary Stream -->
        <div class="lg:col-span-8">
            <h2 class="sidebar-title mb-6">Latest Investigations</h2>
            <?php require_once NP_DIR . '/templates/partials/grid.php'; ?>
        </div>

        <!-- Right Hand Deck -->
        <div class="lg:col-span-4">
            <?php require_once NP_DIR . '/includes/sidebar.php'; ?>
        </div>
    </div>
</main>

<?php require_once NP_DIR . '/includes/footer.php'; ?>
