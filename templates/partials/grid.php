<?php
/**
 * NeuralPress - Feed grid listing partial
 */
use NeuralPress\Core\Database;

$db = Database::getInstance();
$res = $db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY id DESC LIMIT 10");

if (!$res || $res->num_rows === 0):
?>
<div class="bg-white border border-slate-200 p-8 text-center rounded">
    <p class="text-slate-500 font-light">No verified investigative reports have been broadcasted yet.</p>
</div>
<?php
else:
?>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <?php
    while ($post = $res->fetch_assoc()) {
        require NP_DIR . '/includes/post_card.php';
    }
    ?>
</div>
<?php
endif;
?>
