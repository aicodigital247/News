<?php
/**
 * NeuralPress - Auth Engine
 * Enterprise Session & Role Authorization Controls
 */

namespace NeuralPress\Core;

class Auth {
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
            session_start();
        }
    }

    public static function login(string $usernameOrEmail, string $password): bool {
        self::startSession();
        $db = Database::getInstance();
        
        $sql = "SELECT id, username, email, password_hash, role FROM users WHERE username = ? OR email = ? LIMIT 1";
        $res = $db->query($sql, "ss", [$usernameOrEmail, $usernameOrEmail]);
        
        if ($res && $user = $res->fetch_assoc()) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_regen'] = time();
                return true;
            }
        }
        return false;
    }

    public static function logout(): void {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function checkRole(array $allowedRoles): void {
        self::startSession();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /admin/login.php');
            exit;
        }
        if (!in_array($_SESSION['role'], $allowedRoles)) {
            http_response_code(403);
            die("Access Denied: Insufficient Role Permissions.");
        }
    }

    public static function getCurrentUser(): ?array {
        self::startSession();
        if (!isset($_SESSION['user_id'])) return null;
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role']
        ];
    }
}
