<?php
/**
 * NeuralPress - Sitemap Generator
 */

namespace NeuralPress\Cron;

require_once __DIR__ . '/../core/db.php';

use NeuralPress\Core\Database;

$db = Database::getInstance();
echo "[-] Compiling sitemaps...\n";

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$res = $db->query("SELECT slug, created_at FROM posts WHERE status = 'published' ORDER BY id DESC LIMIT 100");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $xml .= "  <url>\n";
        $xml .= "    <loc>https://neuralpress.ai/news/" . htmlspecialchars($row['slug']) . "</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d', strtotime($row['created_at'])) . "</lastmod>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "  </url>\n";
    }
}
$xml .= "</urlset>\n";

@file_put_contents(__DIR__ . '/../sitemap.xml', $xml);
echo "[✓] Sitemap written to disk.\n";
