<?php
/**
 * NeuralPress - Daily Cache and Log Pruner
 */

namespace NeuralPress\Cron;

echo "[-] Sweeping expired file caches...\n";
$dir = __DIR__ . '/../cache/';
if (is_dir($dir)) {
    foreach (glob($dir . "*.cache") as $file) {
        if (time() - filemtime($file) > 86400 * 3) { // 3 days
            @unlink($file);
            echo " - Pruned expired cache file: " . basename($file) . "\n";
        }
    }
}
echo "[✓] Core sweep completed successfully.\n";
