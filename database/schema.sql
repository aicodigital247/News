-- NeuralPress AI News Network DB Schema
-- Production SQL for MySQL 8+ (mysqli compatible)

CREATE DATABASE IF NOT EXISTS neuralpress_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE neuralpress_db;

-- 1. Users & Roles
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'journalist', 'viewer') DEFAULT 'journalist',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_role (role)
) ENGINE=InnoDB;

-- 2. Articles (Posts) table with optimizations and caching indexes
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    summary TEXT,
    content LONGTEXT NOT NULL,
    category ENUM('World', 'Business', 'Technology', 'Sports') NOT NULL DEFAULT 'World',
    language VARCHAR(5) DEFAULT 'en',
    thumbnail_url VARCHAR(512) DEFAULT NULL,
    status ENUM('draft', 'pending_review', 'approved', 'published', 'rejected', 'flagged') DEFAULT 'draft',
    trust_score INT DEFAULT 100,
    risk_level ENUM('low', 'medium', 'high', 'fake_risk') DEFAULT 'low',
    verification_reason TEXT,
    seo_title VARCHAR(255),
    seo_description TEXT,
    seo_keywords VARCHAR(255),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_post_slug (slug),
    INDEX idx_post_status (status),
    INDEX idx_post_category (category),
    INDEX idx_post_trust (trust_score),
    INDEX idx_post_views (views DESC)
) ENGINE=InnoDB;

-- 3. Translaton Cache table to store translated article components and prevent expensive translation API calls
CREATE TABLE IF NOT EXISTS post_translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    language_code VARCHAR(5) NOT NULL, -- fr, es, ar, ha, yo, ig
    translated_title VARCHAR(255) NOT NULL,
    translated_summary TEXT,
    translated_content LONGTEXT NOT NULL,
    translated_seo_title VARCHAR(255),
    translated_seo_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY uq_post_lang (post_id, language_code),
    INDEX idx_translate_lang (language_code)
) ENGINE=InnoDB;

-- 4. Ad monetization and optimization schema
CREATE TABLE IF NOT EXISTS ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code_snippet TEXT NOT NULL,
    slot_position ENUM('header_banner', 'sidebar', 'in_article') NOT NULL,
    status ENUM('active', 'paused', 'ab_test') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ad_slot (slot_position, status)
) ENGINE=InnoDB;

-- Ad performance metrics tables for auditing CTR and RPM
CREATE TABLE IF NOT EXISTS ad_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    impressions INT DEFAULT 0,
    clicks INT DEFAULT 0,
    ctr DECIMAL(5,4) GENERATED ALWAYS AS (CASE WHEN impressions > 0 THEN clicks / impressions ELSE 0 END) STORED,
    rpm DECIMAL(10,2) DEFAULT 0.00,
    log_date DATE NOT NULL,
    FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE,
    UNIQUE KEY uq_ad_date (ad_id, log_date),
    INDEX idx_performance_date (log_date)
) ENGINE=InnoDB;

-- Ad event logs (real-time click/impression records)
CREATE TABLE IF NOT EXISTS ad_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    ad_id INT NOT NULL,
    event_type ENUM('impression', 'click') NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    ref_url VARCHAR(512),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE,
    INDEX idx_event_tracking (ad_id, event_type, created_at)
) ENGINE=InnoDB;

-- 5. AI Queue & Verification logs for background processing
CREATE TABLE IF NOT EXISTS ai_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT DEFAULT NULL,
    action_type ENUM('transcribe', 'verify', 'translate', 'summarize') NOT NULL,
    status ENUM('queued', 'processing', 'completed', 'failed') DEFAULT 'queued',
    prompt_payload TEXT NOT NULL,
    reponse_payload LONGTEXT DEFAULT NULL,
    attempts INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_queue_status (status)
) ENGINE=InnoDB;

-- 6. System audit and activity logs
CREATE TABLE IF NOT EXISTS system_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'info',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sys_severity (severity)
) ENGINE=InnoDB;
