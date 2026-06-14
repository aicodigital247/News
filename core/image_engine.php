<?php
/**
 * NeuralPress - Image Intelligence System
 * Extracts keywords, pulls live headers via Unsplash API,
 * and draws stunning PNG/JPEG gradient graphics as beautiful editorial backups.
 */

namespace NeuralPress\Core;

class ImageEngine {
    private string $unsplashClientId = '';

    public function __construct(string $clientId = '') {
        $this->unsplashClientId = $clientId ?: (defined('UNSPLASH_CLIENT_ID') ? UNSPLASH_CLIENT_ID : '');
    }

    /**
     * Extracts keyword tokens from article metadata to fetch imagery
     */
    public function extractKeywords(string $title, string $content): string {
        $badWords = ['the', 'and', 'with', 'this', 'that', 'from', 'your', 'about', 'their', 'under', 'where', 'after', 'before'];
        $clean = preg_replace('/[^\w\s]/', '', $title . ' ' . $content);
        $words = str_word_count(strtolower($clean), 1);
        $filtered = array_diff($words, $badWords);
        $frequencies = array_count_values($filtered);
        arsort($frequencies);

        $keys = array_slice(array_keys($frequencies), 0, 3);
        return implode(',', $keys) ?: 'news,global';
    }

    /**
     * Tries to fetch a primary image from Unsplash. Fallback to PHP GD.
     */
    public function procureImage(string $title, string $content, string $category): string {
        $keywords = $this->extractKeywords($title, $content);
        
        if (!empty($this->unsplashClientId)) {
            $url = "https://api.unsplash.com/photos/random?query=" . urlencode($keywords) . "&orientation=landscape&client_id=" . $this->unsplashClientId;
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept-Version: v1"]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                $imageUrl = $data['urls']['regular'] ?? null;
                if ($imageUrl) {
                    return $imageUrl; // Success! Return high quality online reference url
                }
            }
        }

        // Direct Fallback: Trigger Dynamic Server-side PHP GD Gradient generation
        return "/dynamic_hero.php?title=" . urlencode(substr($title, 0, 45)) . "&cat=" . urlencode($category);
    }

    /**
     * Generates a stunning fluid gradient fallback using GD library
     * Outputs PNG directly to output stream or saves to disk.
     */
    public function drawGradientBanner(string $title, string $category, bool $outputToBrowser = true, string $savePath = ''): bool {
        // Create 1200x630 pixel canvas (Facebook/OpenGraph benchmark dimensions)
        $im = imagecreatetruecolor(1200, 630);
        if (!$im) return false;

        // Choose palette based on Category mood (BBC standards)
        switch (strtolower($category)) {
            case 'technology':
                $c1 = [15, 32, 67];   // Midnight Blue
                $c2 = [10, 100, 200];  // Deep Blue
                break;
            case 'business':
                $c1 = [36, 11, 54];   // Royal Purple
                $c2 = [120, 10, 80];  // Velvet Violet
                break;
            case 'sports':
                $c1 = [2, 48, 32];    // Dark Forest Green
                $c2 = [79, 111, 82];  // Grass accent
                break;
            case 'world':
            default:
                $c1 = [139, 0, 0];    // BBC Dark Cherry Red
                $c2 = [40, 0, 0];     // Warm Charcoal Red
                break;
        }

        // Output linear gradient blocks
        for ($y = 0; $y < 630; $y++) {
            $r = (int)($c1[0] + ($y / 630) * ($c2[0] - $c1[0]));
            $g = (int)($c1[1] + ($y / 630) * ($c2[1] - $c1[1]));
            $b = (int)($c1[2] + ($y / 630) * ($c2[2] - $c1[2]));
            $color = imagecolorallocate($im, $r, $g, $b);
            imageline($im, 0, $y, 1200, $y, $color);
        }

        // Allocate visual text pigments
        $white = imagecolorallocate($im, 255, 255, 255);
        $redAccent = imagecolorallocate($im, 224, 40, 40);
        $grayText = imagecolorallocate($im, 210, 210, 210);

        // Overlay brand labels and header blocks
        imagestring($im, 5, 50, 50, strtoupper("NEURALPRESS - AI TRUST VERIFIED"), $redAccent);
        imagestring($im, 4, 50, 80, strtoupper("NEWS CATEGORY: " . $category), $grayText);
        
        // Draw elegant Title string. GD without TTF uses standard integer fonts:
        // font type 5 is biggest available built-in font
        $cleanTitle = iconv("UTF-8", "ASCII//TRANSLIT", $title);
        imagestring($im, 5, 50, 200, substr($cleanTitle, 0, 45), $white);
        imagestring($im, 5, 50, 230, substr($cleanTitle, 45, 45), $white);

        imagestring($im, 3, 50, 550, "Global Automated AI Feed | " . date('Y-m-d H:i:s'), $grayText);

        if ($outputToBrowser) {
            header("Content-Type: image/png");
            imagepng($im);
        }

        if (!empty($savePath)) {
            imagepng($im, $savePath);
        }

        imagedestroy($im);
        return true;
    }
}
