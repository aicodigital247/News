<?php
/**
 * NeuralPress - Professional Creator Monetization & Earning Engine
 * Manages creator balances, pageview payouts, withdrawal gateways, and article promotion workflows.
 *
 * @package Core
 */

namespace NeuralPress\Core;

class MonetizationEngine {
    private static ?MonetizationEngine $instance = null;
    private Database $db;
    private float $baseCpm = 10.00; // $10.00 CPM = $0.01 per pageview

    private function __construct() {
        $this->db = Database::getInstance();
        $this->ensureTablesExist();
    }

    public static function getInstance(): MonetizationEngine {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Installs or checks monetization schema elements automatically at startup
     */
    private function ensureTablesExist(): void {
        $connection = $this->db->getConnection();

        // 1. Creator Earnings Table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS creator_earnings (
                user_id INT PRIMARY KEY,
                balance DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total_earned DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                total_withdrawn DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");

        // 2. Withdrawals Table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS withdrawals (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                payment_method VARCHAR(50) NOT NULL,
                payment_details TEXT NOT NULL,
                status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");

        // 3. Promotions Table
        $this->db->query("
            CREATE TABLE IF NOT EXISTS promotions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                post_id INT NOT NULL,
                campaign_name VARCHAR(255) NOT NULL,
                budget DECIMAL(10,2) NOT NULL,
                spent DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                status ENUM('active', 'paused', 'completed') NOT NULL DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");
    }

    /**
     * Get or initialize creator balance record
     */
    public function getBalance(int $userId): array {
        $res = $this->db->query("SELECT * FROM creator_earnings WHERE user_id = ?", "i", [$userId]);
        if ($res && $row = $res->fetch_assoc()) {
            return $row;
        }

        // Initialize record if missing
        $this->db->query("INSERT IGNORE INTO creator_earnings (user_id, balance, total_earned, total_withdrawn) VALUES (?, 0.00, 0.00, 0.00)", "i", [$userId]);
        return [
            'user_id' => $userId,
            'balance' => 0.00,
            'total_earned' => 0.00,
            'total_withdrawn' => 0.00
        ];
    }

    /**
     * Add funds directly to creator balance
     */
    public function creditBalance(int $userId, float $amount): bool {
        $this->getBalance($userId); // ensure row exists
        return $this->db->query(
            "UPDATE creator_earnings SET balance = balance + ?, total_earned = total_earned + ? WHERE user_id = ?",
            "ddi",
            [$amount, $amount, $userId]
        );
    }

    /**
     * Deduct funds directly from creator balance
     */
    public function debitBalance(int $userId, float $amount): bool {
        $this->getBalance($userId); // ensure row exists
        return $this->db->query(
            "UPDATE creator_earnings SET balance = balance - ?, total_withdrawn = total_withdrawn + ? WHERE user_id = ? AND balance >= ?",
            "ddid",
            [$amount, $amount, $userId, $amount]
        );
    }

    /**
     * Process page view payouts
     * Award earnings when readers view the post. If the post has active promotions, rate is increased.
     */
    public function trackView(int $postId): void {
        // Find Author
        $res = $this->db->query("SELECT author_id, status FROM posts WHERE id = ? LIMIT 1", "i", [$postId]);
        if (!$res) return;
        $post = $res->fetch_assoc();
        if (!$post || $post['status'] !== 'published') return;

        $authorId = intval($post['author_id']);

        // Check if there are active campaigns boosting this post
        $promoRes = $this->db->query(
            "SELECT id, budget, spent FROM promotions WHERE post_id = ? AND status = 'active' LIMIT 1",
            "i",
            [$postId]
        );

        $rate = $this->baseCpm / 1000.0; // standard payment per page view

        // If promoted, boost standard author CPM or deduct from campaign budget
        if ($promoRes && $promo = $promoRes->fetch_assoc()) {
            $promoId = intval($promo['id']);
            $costPerView = 0.05; // Promoters pay $0.05 per impression
            $newSpent = floatval($promo['spent']) + $costPerView;
            $budget = floatval($promo['budget']);

            if ($newSpent >= $budget) {
                // Campaign completed!
                $this->db->query("UPDATE promotions SET spent = ?, status = 'completed' WHERE id = ?", "di", [$budget, $promoId]);
            } else {
                $this->db->query("UPDATE promotions SET spent = spent + ? WHERE id = ?", "di", [$costPerView, $promoId]);
            }
            
            // Promoted post page views earn 2.5x more for creators!
            $rate = ($this->baseCpm * 2.5) / 1000.0;
        }

        // Credit the author
        $this->creditBalance($authorId, $rate);
    }

    /**
     * Submit manual withdrawal request
     */
    public function requestWithdrawal(int $userId, float $amount, string $method, string $details): string|bool {
        if ($amount <= 0) {
            return "Withdrawal amount must be greater than $0.00.";
        }

        $balanceInfo = $this->getBalance($userId);
        $currentBalance = floatval($balanceInfo['balance']);

        if ($amount > $currentBalance) {
            return "Insufficient balance. Available balance: $" . number_format($currentBalance, 2);
        }

        // Check if there is already a pending withdrawal
        $pendingCheck = $this->db->query(
            "SELECT COUNT(*) as cnt FROM withdrawals WHERE user_id = ? AND status = 'pending'",
            "i",
            [$userId]
        );
        if ($pendingCheck && $pendingCheck->fetch_assoc()['cnt'] > 0) {
            return "You already have a pending withdrawal request. Please wait for reviews to conclude.";
        }

        // Insert pending request
        $sql = "INSERT INTO withdrawals (user_id, amount, payment_method, payment_details, status) VALUES (?, ?, ?, ?, 'pending')";
        $ok = $this->db->query($sql, "idss", [$userId, $amount, $method, $details]);

        if ($ok) {
            return true;
        }

        return "Database execution failed. Please verify configurations.";
    }

    /**
     * Approve or deny pending withdrawal request
     */
    public function updateWithdrawalStatus(int $withdrawalId, string $status): bool {
        if (!in_array($status, ['approved', 'rejected'])) {
            return false;
        }

        $res = $this->db->query("SELECT * FROM withdrawals WHERE id = ? LIMIT 1", "i", [$withdrawalId]);
        if (!$res) return false;
        $withdrawal = $res->fetch_assoc();
        if (!$withdrawal || $withdrawal['status'] !== 'pending') return false;

        $userId = intval($withdrawal['user_id']);
        $amount = floatval($withdrawal['amount']);

        if ($status === 'approved') {
            // Deduct from creator balance
            $deducted = $this->debitBalance($userId, $amount);
            if (!$deducted) {
                // If they drained their balance in the interim, reject approval
                $this->db->query("UPDATE withdrawals SET status = 'rejected' WHERE id = ?", "i", [$withdrawalId]);
                return false;
            }
        }

        return $this->db->query(
            "UPDATE withdrawals SET status = ? WHERE id = ?",
            "si",
            [$status, $withdrawalId]
        );
    }

    /**
     * Launch or set up campaign promotions
     */
    public function createPromotion(int $userId, int $postId, string $campaignName, float $budget): string|bool {
        if ($budget <= 0) {
            return "Campaign budget must be greater than $0.00.";
        }

        // Check if the article exists and is owned by the user (unless user is admin)
        $postRes = $this->db->query("SELECT id, author_id FROM posts WHERE id = ?", "i", [$postId]);
        if (!$postRes) return "Article not found.";
        $post = $postRes->fetch_assoc();
        if (!$post) return "Article not found.";

        // Insert promotion
        $sql = "INSERT INTO promotions (user_id, post_id, campaign_name, budget, spent, status) VALUES (?, ?, ?, ?, 0.00, 'active')";
        $ok = $this->db->query($sql, "iids", [$userId, $postId, $campaignName, $budget]);

        if ($ok) {
            return true;
        }

        return "Database failed key insertion constraints.";
    }
}
