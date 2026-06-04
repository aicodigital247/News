-- NeuralPress - Database Seeder
-- Initial system states, admin accounts, baseline articles, and default ad spaces

USE neuralpress_db;

-- Insert roles (Passwords are hashed as 'password123' using bcrypt)
INSERT INTO users (username, email, password_hash, role) VALUES
('chief_editor', 'editor@neuralpress.ai', '$2y$10$wKTLgD25uCIs9rI6oVnGAet8M66I3M3rF0hZc1bH/I8V06h0k.pme', 'admin'),
('tech_reporter', 'tech@neuralpress.ai', '$2y$10$wKTLgD25uCIs9rI6oVnGAet8M66I3M3rF0hZc1bH/I8V06h0k.pme', 'journalist'),
('sports_reporter', 'sports@neuralpress.ai', '$2y$10$wKTLgD25uCIs9rI6oVnGAet8M66I3M3rF0hZc1bH/I8V06h0k.pme', 'journalist')
ON DUPLICATE KEY UPDATE username=username;

-- Insert default ad monetizations
INSERT INTO ads (id, name, code_snippet, slot_position, status) VALUES
(101, 'Premium Enterprise Cloud Server Hosting', '<div class="ad-slot banner">Cloud Host Banner</div>', 'header_banner', 'active'),
(102, 'Global Financial Markets Daily Newsletter', '<div class="ad-slot sidebar">Financial Markets Newsletter</div>', 'sidebar', 'active'),
(103, 'Premium Journalistic Subscription Bundle', '<div class="ad-slot in-article">Premium Subscription Upgrade</div>', 'in_article', 'active')
ON DUPLICATE KEY UPDATE name=name;

-- Insert ad baseline performance
INSERT INTO ad_performance (ad_id, impressions, clicks, rpm, log_date) VALUES
(101, 1000, 12, 12.50, '2026-06-04'),
(102, 1000, 23, 18.20, '2026-06-04'),
(103, 1000, 24, 21.00, '2026-06-04')
ON DUPLICATE KEY UPDATE impressions=impressions;
