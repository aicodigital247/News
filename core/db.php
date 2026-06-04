<?php
/**
 * NeuralPress - AI News Network
 * Senior Enterprise DB Adapter (MySQLi OOP Prepared-Statements)
 *
 * @package Core
 */

namespace NeuralPress\Core;

class Database {
    private static ?Database $instance = null;
    private ?\mysqli $connection = null;

    private string $host = '127.0.0.1';
    private string $user = 'db_user';
    private string $pass = 'db_secure_password_9031';
    private string $name = 'neuralpress_db';

    private function __construct() {
        // Safe configuration loading
        if (defined('DB_HOST')) $this->host = DB_HOST;
        if (defined('DB_USER')) $this->user = DB_USER;
        if (defined('DB_PASS')) $this->pass = DB_PASS;
        if (defined('DB_NAME')) $this->name = DB_NAME;

        $this->connect();
    }

    private function connect(): void {
        try {
            $this->connection = new \mysqli($this->host, $this->user, $this->pass, $this->name);
            if ($this->connection->connect_error) {
                throw new \Exception("Database connection failed: " . $this->connection->connect_error);
            }
            $this->connection->set_charset("utf8mb4");
        } catch (\Exception $e) {
            error_log("[Database Error] " . $e->getMessage());
            die(json_encode([
                "error" => "Critical Database Connection Error. Incident logged.",
                "status" => 500
            ]));
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): \mysqli {
        if ($this->connection === null || !$this->connection->ping()) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Executes a secure prepared statement query
     *
     * @param string $sql Rich SQL query with placeholders (?)
     * @param string $types Type string e.g. "sis"
     * @param array $params Parameter variables to bind
     * @return \mysqli_result|bool
     */
    public function query(string $sql, string $types = "", array $params = []) {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("[SQL Prepare Error] " . $conn->error . " for statement: " . $sql);
            return false;
        }

        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            error_log("[SQL Execute Error] " . $stmt->error);
            return false;
        }

        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    /**
     * Inserts records securely and returns the inserted ID
     */
    public function insert(string $sql, string $types = "", array $params = []): int {
        $conn = $this->getConnection();
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("[SQL Prepare Error] " . $conn->error);
            return 0;
        }

        if (!empty($types) && !empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            error_log("[SQL Insert Error] " . $stmt->error);
            return 0;
        }

        $lastId = $stmt->insert_id;
        $stmt->close();
        return $lastId;
    }

    // Escape strings fallback for legacy procedures
    public function escape(string $value): string {
        return $this->getConnection()->real_escape_string($value);
    }

    // Cloning and wakeup is forbidden for singleton structural integrity
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton class.");
    }
}
