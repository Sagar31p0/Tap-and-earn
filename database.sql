-- Telegram Mini Bot Database Schema
-- Database: u988479389_tery

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `telegram_id` BIGINT UNIQUE NOT NULL,
  `username` VARCHAR(255),
  `first_name` VARCHAR(255),
  `last_name` VARCHAR(255),
  `coins` DECIMAL(20,2) DEFAULT 0,
  `energy` INT DEFAULT 100,
  `last_energy_update` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `referral_code` VARCHAR(50) UNIQUE,
  `referred_by` INT NULL,
  `is_banned` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_active` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_telegram_id` (`telegram_id`),
  INDEX `idx_referral_code` (`referral_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Stats Table
CREATE TABLE IF NOT EXISTS `user_stats` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `total_taps` INT DEFAULT 0,
  `total_spins` INT DEFAULT 0,
  `tasks_completed` INT DEFAULT 0,
  `games_played` INT DEFAULT 0,
  `ads_watched` INT DEFAULT 0,
  `referrals_count` INT DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_stats` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referrals Table
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `referrer_id` INT NOT NULL,
  `referred_id` INT NOT NULL,
  `status` ENUM('pending', 'approved') DEFAULT 'pending',
  `tasks_completed` INT DEFAULT 0,
  `reward_given` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`referrer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referred_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_referrer` (`referrer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tasks Table
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `url` VARCHAR(500),
  `reward` DECIMAL(20,2) NOT NULL,
  `icon` VARCHAR(255) DEFAULT 'fa-tasks',
  `type` ENUM('one_time', 'daily') DEFAULT 'one_time',
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `ad_network` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Tasks Table
CREATE TABLE IF NOT EXISTS `user_tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `task_id` INT NOT NULL,
  `status` ENUM('pending', 'completed', 'verified') DEFAULT 'pending',
  `completed_at` TIMESTAMP NULL,
  `last_reset` DATE NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_task` (`user_id`, `task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Games Table
CREATE TABLE IF NOT EXISTS `games` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `icon` VARCHAR(255),
  `game_url` VARCHAR(500) NOT NULL,
  `reward` DECIMAL(20,2) NOT NULL,
  `play_limit_type` ENUM('daily', 'weekly', 'unlimited') DEFAULT 'unlimited',
  `play_limit` INT DEFAULT 0,
  `ad_network` VARCHAR(50),
  `ad_unit_id` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Games Table
CREATE TABLE IF NOT EXISTS `user_games` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `game_id` INT NOT NULL,
  `plays_today` INT DEFAULT 0,
  `plays_this_week` INT DEFAULT 0,
  `last_played` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_reset_daily` DATE NULL,
  `last_reset_weekly` DATE NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_game` (`user_id`, `game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Spin Wheel Configuration
CREATE TABLE IF NOT EXISTS `spin_config` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `block_label` VARCHAR(50) NOT NULL,
  `reward_value` DECIMAL(20,2) NOT NULL,
  `probability` DECIMAL(5,2) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `allow_double` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default spin wheel blocks
INSERT INTO `spin_config` (`block_label`, `reward_value`, `probability`, `is_active`, `allow_double`, `sort_order`) VALUES
('10', 10, 30, 1, 1, 1),
('20', 20, 25, 1, 1, 2),
('50', 50, 20, 1, 1, 3),
('100', 100, 12, 1, 1, 4),
('200', 200, 7, 1, 1, 5),
('500', 500, 4, 1, 1, 6),
('1000', 1000, 1.5, 1, 1, 7),
('JACKPOT', 5000, 0.5, 1, 1, 8);

-- User Spins Table
CREATE TABLE IF NOT EXISTS `user_spins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `spins_today` INT DEFAULT 0,
  `last_spin` TIMESTAMP NULL,
  `last_reset` DATE NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_user_spin` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Withdrawals Table
CREATE TABLE IF NOT EXISTS `withdrawals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `amount` DECIMAL(20,2) NOT NULL,
  `payment_method` VARCHAR(100) NOT NULL,
  `payment_details` TEXT NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `admin_note` TEXT,
  `payment_proof` VARCHAR(500),
  `transaction_id` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `processed_at` TIMESTAMP NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ad Networks Table
CREATE TABLE IF NOT EXISTS `ad_networks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `is_enabled` TINYINT(1) DEFAULT 1,
  `default_settings` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default ad networks
INSERT INTO `ad_networks` (`name`, `is_enabled`) VALUES
('adexium', 1),
('monetag', 1),
('adsgram', 1),
('richads', 1);

-- Ad Units Table
CREATE TABLE IF NOT EXISTS `ad_units` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `network_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `unit_code` VARCHAR(500) NOT NULL,
  `unit_type` VARCHAR(100) NOT NULL,
  `placement_key` VARCHAR(100),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`network_id`) REFERENCES `ad_networks`(`id`) ON DELETE CASCADE,
  INDEX `idx_placement` (`placement_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ad Placements Table
CREATE TABLE IF NOT EXISTS `ad_placements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `placement_key` VARCHAR(100) UNIQUE NOT NULL,
  `description` VARCHAR(255),
  `primary_ad_unit_id` INT,
  `secondary_ad_unit_id` INT,
  `tertiary_ad_unit_id` INT,
  `frequency` INT DEFAULT 1,
  FOREIGN KEY (`primary_ad_unit_id`) REFERENCES `ad_units`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`secondary_ad_unit_id`) REFERENCES `ad_units`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`tertiary_ad_unit_id`) REFERENCES `ad_units`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default placements
INSERT INTO `ad_placements` (`placement_key`, `description`, `frequency`) VALUES
('tap', 'Tap & Earn Ads', 7),
('spin', 'Spin Wheel Ads', 1),
('game_preroll', 'Game Pre-roll Ads', 1),
('task', 'Task Completion Ads', 1),
('shortlink', 'Short Link Ads', 1),
('wallet', 'Wallet Watch Ads', 1);

-- Ad Logs Table
CREATE TABLE IF NOT EXISTS `ad_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `placement` VARCHAR(100) NOT NULL,
  `ad_unit_id` INT,
  `event` ENUM('impression', 'click', 'complete', 'reward') NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`ad_unit_id`) REFERENCES `ad_units`(`id`) ON DELETE SET NULL,
  INDEX `idx_event` (`event`),
  INDEX `idx_placement` (`placement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Short Links Table
CREATE TABLE IF NOT EXISTS `short_links` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `short_code` VARCHAR(50) UNIQUE NOT NULL,
  `original_url` TEXT NOT NULL,
  `mode` ENUM('task_video', 'direct_ad') DEFAULT 'direct_ad',
  `task_id` INT,
  `ad_unit_id` INT,
  `clicks` INT DEFAULT 0,
  `conversions` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`ad_unit_id`) REFERENCES `ad_units`(`id`) ON DELETE SET NULL,
  INDEX `idx_short_code` (`short_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Users Table
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255),
  `role` ENUM('admin', 'moderator') DEFAULT 'admin',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin (password: admin123)
INSERT INTO `admin_users` (`username`, `password`, `email`, `role`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@example.com', 'admin');

-- Global Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('bot_name', 'Earn Bot'),
('bot_username', '@kuchpvildybot'),
('welcome_message', 'Welcome to Earn Bot! Start tapping to earn coins!'),
('support_contact', '@support'),
('tap_reward', '5'),
('energy_per_tap', '1'),
('energy_recharge_rate', '5'),
('energy_recharge_interval', '300'),
('watch_ad_energy', '5'),
('tap_ad_frequency', '7'),
('spin_interval_minutes', '60'),
('spin_daily_limit', '10'),
('referral_reward', '100'),
('referral_unlock_tasks', '1'),
('min_withdrawal', '10'),
('coin_to_usd_rate', '0.001'),
('leaderboard_type', 'coins'),
('leaderboard_reset', 'monthly'),
('theme_mode', 'auto');

-- Broadcasts Table
CREATE TABLE IF NOT EXISTS `broadcasts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `image_url` VARCHAR(500),
  `video_url` VARCHAR(500),
  `cta_text` VARCHAR(100),
  `cta_url` VARCHAR(500),
  `segment` ENUM('all', 'active', 'inactive', 'specific') DEFAULT 'all',
  `target_users` TEXT,
  `sent_count` INT DEFAULT 0,
  `status` ENUM('draft', 'sent', 'scheduled') DEFAULT 'draft',
  `scheduled_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transaction History Table
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('tap', 'spin', 'game', 'task', 'referral', 'withdrawal', 'admin_credit', 'admin_debit') NOT NULL,
  `amount` DECIMAL(20,2) NOT NULL,
  `description` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_type` (`user_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Methods Table
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `fields_required` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default payment methods
INSERT INTO `payment_methods` (`name`, `is_active`, `fields_required`) VALUES
('PayPal', 1, 'email'),
('Bank Transfer', 1, 'account_number,ifsc_code,account_name'),
('UPI', 1, 'upi_id'),
('Crypto', 1, 'wallet_address,network');
