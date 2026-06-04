-- NeuralPress - Database Migrations Log
-- Tracks schema progression increments over audit lifecycles

USE neuralpress_db;

CREATE TABLE IF NOT EXISTS schema_versions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL UNIQUE,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Migration v1.0.0 (Base initialization)
INSERT IGNORE INTO schema_versions (version) VALUES ('1.0.0');

-- Migration v1.1.0: Add language_code index on translations
ALTER TABLE post_translations ADD INDEX IF NOT EXISTS idx_trans_lang_composite (post_id, language_code);
INSERT IGNORE INTO schema_versions (version) VALUES ('1.1.0');
