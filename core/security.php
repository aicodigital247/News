<?php
/**
 * NeuralPress - Security Gatekeeper
 * Protects against SQLi, XSS, and basic malicious payload patterns
 */

namespace NeuralPress\Core;

class Security {
    public static function sanitizeInput(array $data): array {
        $clean = [];
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $clean[$k] = self::sanitizeInput($v);
            } else {
                $clean[$k] = trim(htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8'));
            }
        }
        return $clean;
    }

    public static function checkRateLimit(string $key, int $limit = 100, int $window = 3600): bool {
        Auth::startSession();
        $now = time();
        if (!isset($_SESSION['rate_limits'][$key])) {
            $_SESSION['rate_limits'][$key] = ['count' => 1, 'expires' => $now + $window];
            return true;
        }
        
        $state = &$_SESSION['rate_limits'][$key];
        if ($now > $state['expires']) {
            $state = ['count' => 1, 'expires' => $now + $window];
            return true;
        }

        $state['count']++;
        return $state['count'] <= $limit;
    }
}
