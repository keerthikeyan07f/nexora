-- =========================================================
-- NEXORA - AI STOCK INTELLIGENCE
-- Database Schema: schema.sql
-- =========================================================

CREATE DATABASE IF NOT EXISTS `nexora_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nexora_db`;

-- ---------------------------------------------------------
-- 1. Table: users
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `profile_pic` VARCHAR(255) DEFAULT 'assets/img/default-avatar.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2. Table: stocks
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stocks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `symbol` VARCHAR(10) NOT NULL UNIQUE,
  `company_name` VARCHAR(150) NOT NULL,
  `exchange` VARCHAR(20) DEFAULT 'NASDAQ',
  `sector` VARCHAR(50) DEFAULT 'Technology',
  `industry` VARCHAR(100) DEFAULT 'Software',
  `currency` VARCHAR(5) DEFAULT 'USD',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_stocks_symbol` (`symbol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 3. Table: stock_history
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stock_history` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `symbol` VARCHAR(10) NOT NULL,
  `trade_date` DATE NOT NULL,
  `open_price` DECIMAL(12,4) NOT NULL,
  `high_price` DECIMAL(12,4) NOT NULL,
  `low_price` DECIMAL(12,4) NOT NULL,
  `close_price` DECIMAL(12,4) NOT NULL,
  `volume` BIGINT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_symbol_date` (`symbol`, `trade_date`),
  INDEX `idx_stock_history_symbol_date` (`symbol`, `trade_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 4. Table: watchlist
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `watchlist` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_symbol` (`user_id`, `symbol`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 5. Table: portfolio
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portfolio` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `quantity` DECIMAL(14,4) NOT NULL DEFAULT 0.0000,
  `avg_buy_price` DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_portfolio_user_symbol` (`user_id`, `symbol`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 6. Table: portfolio_transactions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portfolio_transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `transaction_type` ENUM('BUY', 'SELL') NOT NULL,
  `quantity` DECIMAL(14,4) NOT NULL,
  `price` DECIMAL(12,4) NOT NULL,
  `total_amount` DECIMAL(14,4) NOT NULL,
  `transaction_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_portfolio_tx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 7. Table: predictions
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `predictions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `symbol` VARCHAR(10) NOT NULL,
  `current_price` DECIMAL(12,4) NOT NULL,
  `predicted_price` DECIMAL(12,4) NOT NULL,
  `predicted_change_pct` DECIMAL(6,2) NOT NULL,
  `prediction_horizon_days` INT NOT NULL DEFAULT 7,
  `target_date` DATE NOT NULL,
  `rmse` DECIMAL(10,4) DEFAULT NULL,
  `mae` DECIMAL(10,4) DEFAULT NULL,
  `mape` DECIMAL(6,2) DEFAULT NULL,
  `model_version` VARCHAR(50) DEFAULT 'NEXORA-LSTM-v1.0',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_predictions_symbol` (`symbol`),
  INDEX `idx_predictions_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 8. Table: prediction_history
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `prediction_history` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `prediction_id` INT UNSIGNED NOT NULL,
  `actual_price` DECIMAL(12,4) DEFAULT NULL,
  `evaluation_date` DATE DEFAULT NULL,
  `error_abs` DECIMAL(12,4) DEFAULT NULL,
  `error_pct` DECIMAL(6,2) DEFAULT NULL,
  `is_evaluated` TINYINT(1) DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`prediction_id`) REFERENCES `predictions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 9. Table: user_settings
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL UNIQUE,
  `theme` VARCHAR(10) DEFAULT 'dark',
  `currency` VARCHAR(5) DEFAULT 'USD',
  `email_notifications` TINYINT(1) DEFAULT 1,
  `prediction_alerts` TINYINT(1) DEFAULT 1,
  `dashboard_layout` VARCHAR(50) DEFAULT 'compact',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 10. Table: notifications
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_notifications_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Popular Stock Symbols
INSERT INTO `stocks` (`symbol`, `company_name`, `exchange`, `sector`, `industry`) VALUES
('AAPL', 'Apple Inc.', 'NASDAQ', 'Technology', 'Consumer Electronics'),
('MSFT', 'Microsoft Corporation', 'NASDAQ', 'Technology', 'Software - Infrastructure'),
('GOOGL', 'Alphabet Inc.', 'NASDAQ', 'Communication Services', 'Internet Content & Information'),
('AMZN', 'Amazon.com Inc.', 'NASDAQ', 'Consumer Cyclical', 'Internet Retail'),
('NVDA', 'NVIDIA Corporation', 'NASDAQ', 'Technology', 'Semiconductors'),
('TSLA', 'Tesla Inc.', 'NASDAQ', 'Consumer Cyclical', 'Auto Manufacturers'),
('META', 'Meta Platforms Inc.', 'NASDAQ', 'Communication Services', 'Internet Content'),
('NFLX', 'Netflix Inc.', 'NASDAQ', 'Communication Services', 'Entertainment');

-- Default Demo User (Password: "DemoUser123!")
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`) VALUES
(1, 'demouser', 'demo@nexora.ai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nexora Demo User')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Default User Settings
INSERT INTO `user_settings` (`user_id`, `theme`, `currency`, `email_notifications`, `prediction_alerts`) VALUES
(1, 'dark', 'USD', 1, 1)
ON DUPLICATE KEY UPDATE `user_id`=`user_id`;

-- Sample Initial Watchlist for Demo User
INSERT INTO `watchlist` (`user_id`, `symbol`) VALUES
(1, 'AAPL'),
(1, 'NVDA'),
(1, 'MSFT')
ON DUPLICATE KEY UPDATE `symbol`=`symbol`;

-- Sample Portfolio Holdings for Demo User
INSERT INTO `portfolio` (`user_id`, `symbol`, `quantity`, `avg_buy_price`) VALUES
(1, 'AAPL', 15.0000, 175.5000),
(1, 'NVDA', 8.0000, 420.0000)
ON DUPLICATE KEY UPDATE `symbol`=`symbol`;

-- Sample Transactions
INSERT INTO `portfolio_transactions` (`user_id`, `symbol`, `transaction_type`, `quantity`, `price`, `total_amount`) VALUES
(1, 'AAPL', 'BUY', 15.0000, 175.5000, 2632.5000),
(1, 'NVDA', 'BUY', 8.0000, 420.0000, 3360.0000);

-- Initial Welcome Notification
INSERT INTO `notifications` (`user_id`, `title`, `message`, `type`) VALUES
(1, 'Welcome to NEXORA AI', 'Your NEXORA AI Stock Intelligence account is active. Explore live stock indicators and run LSTM prediction models.', 'success'),
(1, 'Portfolio Initialized', 'Your virtual portfolio has been credited with sample holdings (AAPL, NVDA).', 'info');
