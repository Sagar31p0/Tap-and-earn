-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 28, 2025 at 07:49 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u988479389_tery`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('admin','moderator') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2a$12$aaj2tmbCv9Wtp2kLg9OBp.092xwqqkQTgfJd/Yk4CXGPtLIp1sBcO', 'admin@example.com', 'admin', 1, '2025-10-26 18:45:35');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ad_logs`
--

CREATE TABLE `ad_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `placement` varchar(100) NOT NULL,
  `ad_unit_id` int(11) DEFAULT NULL,
  `event` enum('impression','click','complete','reward') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ad_networks`
--

CREATE TABLE `ad_networks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `default_settings` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ad_networks`
--

INSERT INTO `ad_networks` (`id`, `name`, `is_enabled`, `default_settings`, `created_at`) VALUES
(1, 'adexium', 1, NULL, '2025-10-26 18:45:35'),
(2, 'monetag', 1, NULL, '2025-10-26 18:45:35'),
(3, 'adsgram', 1, NULL, '2025-10-26 18:45:35'),
(4, 'richads', 1, NULL, '2025-10-26 18:45:35');

-- --------------------------------------------------------

--
-- Table structure for table `ad_placements`
--

CREATE TABLE `ad_placements` (
  `id` int(11) NOT NULL,
  `placement_key` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `primary_ad_unit_id` int(11) DEFAULT NULL,
  `secondary_ad_unit_id` int(11) DEFAULT NULL,
  `tertiary_ad_unit_id` int(11) DEFAULT NULL,
  `frequency` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ad_placements`
--

INSERT INTO `ad_placements` (`id`, `placement_key`, `description`, `primary_ad_unit_id`, `secondary_ad_unit_id`, `tertiary_ad_unit_id`, `frequency`) VALUES
(1, 'tap', 'Tap & Earn Ads', 6, 4, 2, 5),
(2, 'spin', 'Spin Wheel Ads', 2, 1, NULL, 1),
(3, 'game_preroll', 'Game Pre-roll Ads', NULL, NULL, NULL, 1),
(4, 'task', 'Task Completion Ads', NULL, NULL, NULL, 1),
(5, 'shortlink', 'Short Link Ads', NULL, NULL, NULL, 1),
(6, 'wallet', 'Wallet Watch Ads', 3, 4, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ad_units`
--

CREATE TABLE `ad_units` (
  `id` int(11) NOT NULL,
  `network_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit_code` varchar(500) NOT NULL,
  `unit_type` varchar(100) NOT NULL,
  `placement_key` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ad_units`
--

INSERT INTO `ad_units` (`id`, `network_id`, `name`, `unit_code`, `unit_type`, `placement_key`, `is_active`, `created_at`) VALUES
(1, 1, 'Interstitial adexium', '\r\ndocument.addEventListener(&#039;DOMContentLoaded&#039;, () =&gt; {\r\n  // Example: Interstitial\r\n  const adexiumWidget = new AdexiumWidget({\r\n    wid: &#039;ef364bbc-e2b8-434c-8b52-c735de561dc7&#039;, // admin-provided widget id\r\n    adFormat: &#039;interstitial&#039;\r\n  });\r\n  // Auto mode will decide when to show as per SDK\r\n  adexiumWidget.autoMode();\r\n  \r\n  // Example: Show manually\r\n  // adexiumWidget.show();\r\n});\r\n', 'interstitial', 'tap', 1, '2025-10-27 10:48:05'),
(2, 2, 'Interstitial monetag', '// In-App Interstitial with advanced settings:\r\nshow_10055887({\r\n  type: &#039;inApp&#039;,\r\n  inAppSettings: {\r\n    frequency: 2,       // show automatically 2 ads within time window\r\n    capping: 0.1,       // 0.1 hours = 6 minutes\r\n    interval: 30,       // 30 sec between ads\r\n    timeout: 5,         // delay 5 sec before first show\r\n    everyPage: false    // session preserved between pages\r\n  }\r\n});', 'interstitial', 'spin', 1, '2025-10-27 10:50:02'),
(3, 3, 'Reward Adsgram', '16414', 'rewarded', 'shortlink', 1, '2025-10-27 10:50:49'),
(4, 3, 'Interstitial Adsgram', 'int-16415', 'interstitial', 'spin', 1, '2025-10-27 10:51:31'),
(5, 3, 'Adsgram Task ad', 'task-16416', 'native', 'task', 1, '2025-10-27 10:52:10'),
(6, 4, 'Reward richads', '#375144', 'rewarded', 'spin', 1, '2025-10-27 10:53:26'),
(7, 4, 'Interstitial richads', '#375143', 'interstitial', 'tap', 1, '2025-10-27 10:53:58'),
(8, 4, 'Push Adsgram', '#375141', 'native', 'tap', 1, '2025-10-27 10:54:36'),
(9, 4, 'banner richads', '#375142', 'banner', 'shortlink', 1, '2025-10-27 10:55:17');

-- --------------------------------------------------------

--
-- Table structure for table `broadcasts`
--

CREATE TABLE `broadcasts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `cta_text` varchar(100) DEFAULT NULL,
  `cta_url` varchar(500) DEFAULT NULL,
  `segment` enum('all','active','inactive','specific') DEFAULT 'all',
  `target_users` text DEFAULT NULL,
  `sent_count` int(11) DEFAULT 0,
  `status` enum('draft','sent','scheduled') DEFAULT 'draft',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `game_url` varchar(500) NOT NULL,
  `reward` decimal(20,2) NOT NULL,
  `play_limit_type` enum('daily','weekly','unlimited') DEFAULT 'unlimited',
  `play_limit` int(11) DEFAULT 0,
  `ad_network` varchar(50) DEFAULT NULL,
  `ad_unit_id` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `fields_required` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `is_active`, `fields_required`, `created_at`) VALUES
(1, 'PayPal', 1, 'email', '2025-10-26 18:45:35'),
(2, 'Bank Transfer', 1, 'account_number,ifsc_code,account_name', '2025-10-26 18:45:35'),
(3, 'UPI', 1, 'upi_id', '2025-10-26 18:45:35'),
(4, 'Crypto', 1, 'wallet_address,network', '2025-10-26 18:45:35');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `tasks_completed` int(11) DEFAULT 0,
  `reward_given` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'bot_name', 'Earn Bot', '2025-10-26 18:45:35'),
(2, 'bot_username', '@kuchpvildybot', '2025-10-26 18:45:35'),
(3, 'welcome_message', 'Welcome to Earn Bot! Start tapping to earn coins!', '2025-10-26 18:45:35'),
(4, 'support_contact', '@support', '2025-10-26 18:45:35'),
(5, 'tap_reward', '5', '2025-10-26 18:45:35'),
(6, 'energy_per_tap', '1', '2025-10-26 18:45:35'),
(7, 'energy_recharge_rate', '5', '2025-10-26 18:45:35'),
(8, 'energy_recharge_interval', '300', '2025-10-26 18:45:35'),
(9, 'watch_ad_energy', '5', '2025-10-26 18:45:35'),
(10, 'tap_ad_frequency', '5', '2025-10-27 10:38:43'),
(11, 'spin_interval_minutes', '60', '2025-10-26 18:45:35'),
(12, 'spin_daily_limit', '10', '2025-10-26 18:45:35'),
(13, 'referral_reward', '100', '2025-10-26 18:45:35'),
(14, 'referral_unlock_tasks', '1', '2025-10-26 18:45:35'),
(15, 'min_withdrawal', '10', '2025-10-26 18:45:35'),
(16, 'coin_to_usd_rate', '0.001', '2025-10-26 18:45:35'),
(17, 'leaderboard_type', 'referrals', '2025-10-26 19:16:30'),
(18, 'leaderboard_reset', 'weekly', '2025-10-26 19:16:30'),
(19, 'theme_mode', 'auto', '2025-10-26 18:45:35');

-- --------------------------------------------------------

--
-- Table structure for table `short_links`
--

CREATE TABLE `short_links` (
  `id` int(11) NOT NULL,
  `short_code` varchar(50) NOT NULL,
  `original_url` text NOT NULL,
  `mode` enum('task_video','direct_ad') DEFAULT 'direct_ad',
  `task_id` int(11) DEFAULT NULL,
  `ad_unit_id` int(11) DEFAULT NULL,
  `clicks` int(11) DEFAULT 0,
  `conversions` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `short_links`
--

INSERT INTO `short_links` (`id`, `short_code`, `original_url`, `mode`, `task_id`, `ad_unit_id`, `clicks`, `conversions`, `created_at`) VALUES
(1, 'xvKkAk', 'https://youtu.be/ayK3YZVGpPY?si=QSva--Vv8i20IUnT', 'direct_ad', NULL, NULL, 1, 0, '2025-10-27 10:21:11');

-- --------------------------------------------------------

--
-- Table structure for table `spin_config`
--

CREATE TABLE `spin_config` (
  `id` int(11) NOT NULL,
  `block_label` varchar(50) NOT NULL,
  `reward_value` decimal(20,2) NOT NULL,
  `probability` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `allow_double` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `spin_config`
--

INSERT INTO `spin_config` (`id`, `block_label`, `reward_value`, `probability`, `is_active`, `allow_double`, `sort_order`) VALUES
(1, '10', 10.00, 30.00, 1, 1, 1),
(2, '20', 20.00, 25.00, 1, 1, 2),
(3, '50', 50.00, 20.00, 1, 1, 3),
(4, '100', 100.00, 12.00, 1, 1, 4),
(5, '200', 200.00, 7.00, 1, 1, 5),
(6, '500', 500.00, 4.00, 1, 1, 6),
(7, '1000', 1000.00, 1.50, 1, 1, 7),
(8, 'JACKPOT', 5000.00, 0.50, 1, 1, 8);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(500) DEFAULT NULL,
  `reward` decimal(20,2) NOT NULL,
  `icon` varchar(255) DEFAULT 'fa-tasks',
  `type` enum('one_time','daily') DEFAULT 'one_time',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `ad_network` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `url`, `reward`, `icon`, `type`, `is_active`, `sort_order`, `ad_network`, `created_at`) VALUES
(1, 'Task 1', '', 'https://youtu.be/xZUliXTObP4?si=zx-_s3pD-8SLpotC', 20.00, 'fas fa-tasks', 'one_time', 1, 0, 'monetag', '2025-10-27 10:12:29');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('tap','spin','game','task','referral','withdrawal','admin_credit','admin_debit') NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `description`, `created_at`) VALUES
(1, 1, 'tap', 50.00, 'Earned from 10 taps', '2025-10-26 18:56:54'),
(2, 1, 'tap', 125.00, 'Earned from 25 taps', '2025-10-26 18:57:05'),
(3, 1, 'tap', 85.00, 'Earned from 17 taps', '2025-10-26 18:57:08'),
(4, 1, 'tap', 240.00, 'Earned from 48 taps', '2025-10-26 18:57:56'),
(5, 1, 'tap', 175.00, 'Earned from 35 taps', '2025-10-26 19:35:29'),
(6, 1, 'tap', 35.00, 'Earned from 7 taps', '2025-10-27 10:09:34'),
(7, 1, 'task', 20.00, 'Task completed: Task 1', '2025-10-27 10:12:46'),
(8, 1, 'tap', 235.00, 'Earned from 47 taps', '2025-10-27 10:39:46'),
(9, 1, 'tap', 35.00, 'Earned from 7 taps', '2025-10-27 10:56:44'),
(10, 1, 'tap', 85.00, 'Earned from 17 taps', '2025-10-27 10:56:48'),
(11, 1, 'tap', 70.00, 'Earned from 14 taps', '2025-10-27 10:56:52'),
(12, 1, 'tap', 60.00, 'Earned from 12 taps', '2025-10-27 10:57:10'),
(13, 1, 'tap', 90.00, 'Earned from 18 taps', '2025-10-27 10:57:14'),
(14, 1, 'tap', 5.00, 'Earned from 1 taps', '2025-10-27 11:50:28'),
(15, 1, 'tap', 5.00, 'Earned from 1 taps', '2025-10-27 11:50:29'),
(16, 1, 'tap', 20.00, 'Earned from 4 taps', '2025-10-27 11:50:32'),
(17, 1, 'tap', 40.00, 'Earned from 8 taps', '2025-10-27 11:50:36'),
(18, 2, 'tap', 30.00, 'Earned from 6 taps', '2025-10-27 16:41:08'),
(19, 2, 'tap', 95.00, 'Earned from 19 taps', '2025-10-27 16:47:57'),
(20, 2, 'tap', 90.00, 'Earned from 18 taps', '2025-10-27 16:48:02'),
(21, 2, 'tap', 5.00, 'Earned from 1 taps', '2025-10-27 16:48:04'),
(22, 2, 'tap', 5.00, 'Earned from 1 taps', '2025-10-27 16:48:05'),
(23, 2, 'tap', 30.00, 'Earned from 6 taps', '2025-10-27 16:48:07'),
(24, 2, 'tap', 5.00, 'Earned from 1 taps', '2025-10-27 16:48:08'),
(25, 2, 'tap', 30.00, 'Earned from 6 taps', '2025-10-27 17:20:38'),
(26, 2, 'tap', 15.00, 'Earned from 3 taps', '2025-10-27 17:20:39'),
(27, 2, 'tap', 20.00, 'Earned from 4 taps', '2025-10-27 17:20:40'),
(28, 2, 'tap', 10.00, 'Earned from 2 taps', '2025-10-27 17:20:41'),
(29, 2, 'tap', 15.00, 'Earned from 3 taps', '2025-10-27 17:20:42'),
(30, 2, 'tap', 10.00, 'Earned from 2 taps', '2025-10-27 17:20:43'),
(31, 2, 'tap', 15.00, 'Earned from 3 taps', '2025-10-27 17:20:44'),
(32, 2, 'task', 20.00, 'Task completed: Task 1', '2025-10-27 17:22:38'),
(33, 2, 'tap', 185.00, 'Earned from 37 taps', '2025-10-28 07:43:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `coins` decimal(20,2) DEFAULT 0.00,
  `energy` int(11) DEFAULT 100,
  `last_energy_update` timestamp NULL DEFAULT current_timestamp(),
  `referral_code` varchar(50) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL,
  `is_banned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_active` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `telegram_id`, `username`, `first_name`, `last_name`, `coins`, `energy`, `last_energy_update`, `referral_code`, `referred_by`, `is_banned`, `created_at`, `last_active`) VALUES
(1, 489077790, 'Sagar_systum', 'Channel Seller', '', 1375.00, 100, '2025-10-27 16:39:21', 'C942232A', NULL, 0, '2025-10-26 18:56:47', '2025-10-27 16:39:21'),
(2, 6901424701, 'Trusted_seller_req', 'Seller', '', 580.00, 63, '2025-10-28 07:43:19', 'A27A2727', NULL, 0, '2025-10-27 16:40:41', '2025-10-28 07:44:15');

-- --------------------------------------------------------

--
-- Table structure for table `user_games`
--

CREATE TABLE `user_games` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `plays_today` int(11) DEFAULT 0,
  `plays_this_week` int(11) DEFAULT 0,
  `last_played` timestamp NULL DEFAULT current_timestamp(),
  `last_reset_daily` date DEFAULT NULL,
  `last_reset_weekly` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_spins`
--

CREATE TABLE `user_spins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `spins_today` int(11) DEFAULT 0,
  `last_spin` timestamp NULL DEFAULT NULL,
  `last_reset` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_spins`
--

INSERT INTO `user_spins` (`id`, `user_id`, `spins_today`, `last_spin`, `last_reset`) VALUES
(1, 1, 0, NULL, '2025-10-27'),
(2, 2, 0, NULL, '2025-10-28');

-- --------------------------------------------------------

--
-- Table structure for table `user_stats`
--

CREATE TABLE `user_stats` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_taps` int(11) DEFAULT 0,
  `total_spins` int(11) DEFAULT 0,
  `tasks_completed` int(11) DEFAULT 0,
  `games_played` int(11) DEFAULT 0,
  `ads_watched` int(11) DEFAULT 0,
  `referrals_count` int(11) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_stats`
--

INSERT INTO `user_stats` (`id`, `user_id`, `total_taps`, `total_spins`, `tasks_completed`, `games_played`, `ads_watched`, `referrals_count`, `updated_at`) VALUES
(1, 1, 271, 0, 1, 0, 0, 0, '2025-10-27 11:50:36'),
(2, 2, 112, 0, 1, 0, 0, 0, '2025-10-28 07:43:19');

-- --------------------------------------------------------

--
-- Table structure for table `user_tasks`
--

CREATE TABLE `user_tasks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `status` enum('pending','completed','verified') DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `last_reset` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_tasks`
--

INSERT INTO `user_tasks` (`id`, `user_id`, `task_id`, `status`, `completed_at`, `last_reset`) VALUES
(1, 1, 1, 'completed', '2025-10-27 10:12:46', '2025-10-27'),
(2, 2, 1, 'completed', '2025-10-27 17:22:38', '2025-10-27');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(20,2) NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `payment_details` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_note` text DEFAULT NULL,
  `payment_proof` varchar(500) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `ad_logs`
--
ALTER TABLE `ad_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ad_unit_id` (`ad_unit_id`),
  ADD KEY `idx_event` (`event`),
  ADD KEY `idx_placement` (`placement`);

--
-- Indexes for table `ad_networks`
--
ALTER TABLE `ad_networks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ad_placements`
--
ALTER TABLE `ad_placements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `placement_key` (`placement_key`),
  ADD KEY `primary_ad_unit_id` (`primary_ad_unit_id`),
  ADD KEY `secondary_ad_unit_id` (`secondary_ad_unit_id`),
  ADD KEY `tertiary_ad_unit_id` (`tertiary_ad_unit_id`);

--
-- Indexes for table `ad_units`
--
ALTER TABLE `ad_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `network_id` (`network_id`),
  ADD KEY `idx_placement` (`placement_key`);

--
-- Indexes for table `broadcasts`
--
ALTER TABLE `broadcasts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referred_id` (`referred_id`),
  ADD KEY `idx_referrer` (`referrer_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `short_links`
--
ALTER TABLE `short_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `short_code` (`short_code`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `ad_unit_id` (`ad_unit_id`),
  ADD KEY `idx_short_code` (`short_code`);

--
-- Indexes for table `spin_config`
--
ALTER TABLE `spin_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type` (`user_id`,`type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD KEY `idx_telegram_id` (`telegram_id`),
  ADD KEY `idx_referral_code` (`referral_code`);

--
-- Indexes for table `user_games`
--
ALTER TABLE `user_games`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_game` (`user_id`,`game_id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `user_spins`
--
ALTER TABLE `user_spins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_spin` (`user_id`);

--
-- Indexes for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_stats` (`user_id`);

--
-- Indexes for table `user_tasks`
--
ALTER TABLE `user_tasks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_task` (`user_id`,`task_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad_logs`
--
ALTER TABLE `ad_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ad_networks`
--
ALTER TABLE `ad_networks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ad_placements`
--
ALTER TABLE `ad_placements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ad_units`
--
ALTER TABLE `ad_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `broadcasts`
--
ALTER TABLE `broadcasts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `short_links`
--
ALTER TABLE `short_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `spin_config`
--
ALTER TABLE `spin_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_games`
--
ALTER TABLE `user_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_spins`
--
ALTER TABLE `user_spins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_stats`
--
ALTER TABLE `user_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_tasks`
--
ALTER TABLE `user_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ad_logs`
--
ALTER TABLE `ad_logs`
  ADD CONSTRAINT `ad_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ad_logs_ibfk_2` FOREIGN KEY (`ad_unit_id`) REFERENCES `ad_units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ad_placements`
--
ALTER TABLE `ad_placements`
  ADD CONSTRAINT `ad_placements_ibfk_1` FOREIGN KEY (`primary_ad_unit_id`) REFERENCES `ad_units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ad_placements_ibfk_2` FOREIGN KEY (`secondary_ad_unit_id`) REFERENCES `ad_units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ad_placements_ibfk_3` FOREIGN KEY (`tertiary_ad_unit_id`) REFERENCES `ad_units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ad_units`
--
ALTER TABLE `ad_units`
  ADD CONSTRAINT `ad_units_ibfk_1` FOREIGN KEY (`network_id`) REFERENCES `ad_networks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `short_links`
--
ALTER TABLE `short_links`
  ADD CONSTRAINT `short_links_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `short_links_ibfk_2` FOREIGN KEY (`ad_unit_id`) REFERENCES `ad_units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_games`
--
ALTER TABLE `user_games`
  ADD CONSTRAINT `user_games_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_games_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_spins`
--
ALTER TABLE `user_spins`
  ADD CONSTRAINT `user_spins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_stats`
--
ALTER TABLE `user_stats`
  ADD CONSTRAINT `user_stats_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_tasks`
--
ALTER TABLE `user_tasks`
  ADD CONSTRAINT `user_tasks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_tasks_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
