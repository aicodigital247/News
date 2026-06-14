<?php
/**
 * NeuralPress - URL Manager Helper
 * Manages clean, SEO-friendly URL patterns across the system.
 */

class UrlManager {
    /**
     * Check if pretty URLs are enabled. Defaults to true, but can be overridden in config.php.
     */
    public static function isPretty() {
        return defined('NP_PRETTY_URLS') ? NP_PRETTY_URLS : true;
    }

    /**
     * Get absolute or relative root base URL
     */
    public static function getBaseUrl() {
        if (defined('NP_URL')) {
            return NP_URL;
        }
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host . '/';
    }

    /**
     * Get clean URL for homepage
     */
    public static function getHomeUrl() {
        return self::getBaseUrl();
    }

    /**
     * Get clean or parameter URL for a news article slug
     */
    public static function getArticleUrl($slug) {
        if (self::isPretty()) {
            return self::getBaseUrl() . 'post/' . urlencode($slug);
        } else {
            return self::getBaseUrl() . 'index.php?slug=' . urlencode($slug);
        }
    }

    /**
     * Get clean or parameter URL for a category profile
     */
    public static function getCategoryUrl($category) {
        if (self::isPretty()) {
            return self::getBaseUrl() . 'category/' . urlencode($category);
        } else {
            return self::getBaseUrl() . 'index.php?category=' . urlencode($category);
        }
    }

    /**
     * Get clean or parameter URL for search query
     */
    public static function getSearchUrl($query = '') {
        if (self::isPretty()) {
            $url = self::getBaseUrl() . 'search';
            if (!empty($query)) {
                $url .= '?q=' . urlencode($query);
            }
            return $url;
        } else {
            $url = self::getBaseUrl() . 'index.php';
            $params = [];
            if (!empty($query)) {
                $params['q'] = $query;
            }
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            return $url;
        }
    }

    /**
     * Get clean URL for static assets
     */
    public static function getAssetUrl($path) {
        return self::getBaseUrl() . 'assets/' . ltrim($path, '/');
    }

    /**
     * Get clean URL for administrative panel views
     */
    public static function getAdminUrl($view) {
        // Strip out trailing .php if requested cleanly
        $view = preg_replace('/\.php$/', '', $view);
        return self::getBaseUrl() . 'admin/' . $view;
    }
}
