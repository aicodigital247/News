<?php
/**
 * NeuralPress - Performance File Caching System
 */

namespace NeuralPress\Core;

class Cache {
    private static string $cacheDir = __DIR__ . '/../cache/';

    private static function ensureCacheDir(): void {
        if (!is_dir(self::$cacheDir)) {
            @mkdir(self::$cacheDir, 0777, true);
        }
    }

    public static function set(string $key, $data, int $ttl = 300): void {
        self::ensureCacheDir();
        $file = self::$cacheDir . md5($key) . '.cache';
        $payload = [
            'expires' => time() + $ttl,
            'data' => serialize($data)
        ];
        @file_put_contents($file, json_encode($payload));
    }

    public static function get(string $key) {
        $file = self::$cacheDir . md5($key) . '.cache';
        if (!file_exists($file)) return null;

        $raw = @file_get_contents($file);
        if (!$raw) return null;

        $payload = json_decode($raw, true);
        if (!$payload || time() > $payload['expires']) {
            @unlink($file);
            return null;
        }

        return unserialize($payload['data']);
    }

    public static function invalidate(string $key): void {
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
