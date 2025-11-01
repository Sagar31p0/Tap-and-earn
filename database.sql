-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 01, 2025 at 08:36 AM
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
-- Database: `u988479389_tape`
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

--
-- Dumping data for table `ad_logs`
--

INSERT INTO `ad_logs` (`id`, `user_id`, `placement`, `ad_unit_id`, `event`, `created_at`) VALUES
(1, 3, 'spin', NULL, 'impression', '2025-10-28 15:38:35'),
(2, 3, 'spin', NULL, 'impression', '2025-10-28 15:38:36'),
(3, 3, 'spin', NULL, 'complete', '2025-10-28 15:38:37'),
(4, 3, 'spin', NULL, 'impression', '2025-10-28 16:52:54'),
(5, 3, 'spin', NULL, 'impression', '2025-10-28 16:52:54'),
(6, 3, 'spin', NULL, 'complete', '2025-10-28 16:52:54'),
(7, 1, 'spin', NULL, 'impression', '2025-10-28 16:54:42'),
(8, 1, 'spin', NULL, 'impression', '2025-10-28 16:54:42'),
(9, 1, 'spin', NULL, 'complete', '2025-10-28 16:54:42'),
(10, 4, 'spin', NULL, 'impression', '2025-10-28 17:09:09'),
(11, 4, 'spin', NULL, 'impression', '2025-10-28 17:09:09'),
(12, 4, 'spin', NULL, 'complete', '2025-10-28 17:09:09'),
(13, 5, 'spin', NULL, 'impression', '2025-10-28 17:09:51'),
(14, 5, 'spin', NULL, 'impression', '2025-10-28 17:09:51'),
(15, 5, 'spin', NULL, 'complete', '2025-10-28 17:09:51'),
(16, 5, 'tap', NULL, 'impression', '2025-10-28 17:09:58'),
(17, 5, 'tap', NULL, 'impression', '2025-10-28 17:09:58'),
(18, 5, 'tap', NULL, 'complete', '2025-10-28 17:09:58'),
(19, 3, 'tap', NULL, 'impression', '2025-10-28 17:21:12'),
(20, 3, 'tap', NULL, 'impression', '2025-10-28 17:21:12'),
(21, 3, 'tap', NULL, 'impression', '2025-10-29 05:31:10'),
(22, 3, 'tap', NULL, 'impression', '2025-10-29 05:31:10'),
(23, 3, 'spin', NULL, 'impression', '2025-10-29 05:32:01'),
(24, 3, 'spin', NULL, 'impression', '2025-10-29 05:32:01'),
(25, 3, 'spin', NULL, 'impression', '2025-10-29 05:33:06'),
(26, 3, 'spin', NULL, 'impression', '2025-10-29 05:33:06'),
(27, 3, 'tap', NULL, 'impression', '2025-10-29 07:36:49'),
(28, 3, 'tap', NULL, 'impression', '2025-10-29 07:36:49'),
(29, 3, 'tap', NULL, 'impression', '2025-10-29 07:37:15'),
(30, 3, 'tap', NULL, 'impression', '2025-10-29 07:37:15'),
(31, 3, 'tap', NULL, 'impression', '2025-10-29 07:37:30'),
(32, 3, 'tap', NULL, 'impression', '2025-10-29 07:37:30'),
(33, 6, 'spin', NULL, 'impression', '2025-10-29 07:37:49'),
(34, 6, 'spin', NULL, 'impression', '2025-10-29 07:37:49'),
(35, 6, 'tap', NULL, 'impression', '2025-10-29 07:40:33'),
(36, 6, 'tap', NULL, 'impression', '2025-10-29 07:40:33'),
(37, 6, 'spin', NULL, 'impression', '2025-10-29 07:40:45'),
(38, 6, 'spin', NULL, 'impression', '2025-10-29 07:40:46'),
(39, 6, 'tap', NULL, 'impression', '2025-10-29 07:41:51'),
(40, 6, 'tap', NULL, 'impression', '2025-10-29 07:41:51'),
(41, 6, 'tap', NULL, 'impression', '2025-10-29 07:44:52'),
(42, 6, 'tap', NULL, 'impression', '2025-10-29 07:44:52'),
(43, 6, 'tap', NULL, 'impression', '2025-10-29 07:44:53'),
(44, 6, 'tap', NULL, 'impression', '2025-10-29 07:44:53'),
(45, 6, 'spin', NULL, 'impression', '2025-10-29 07:45:33'),
(46, 6, 'spin', NULL, 'impression', '2025-10-29 07:45:33'),
(47, 2, 'spin', NULL, 'impression', '2025-10-29 07:47:22'),
(48, 2, 'spin', NULL, 'impression', '2025-10-29 07:47:22'),
(49, 2, 'tap', NULL, 'impression', '2025-10-29 07:47:48'),
(50, 2, 'tap', NULL, 'impression', '2025-10-29 07:47:49'),
(51, 7, 'tap', NULL, 'impression', '2025-10-29 07:49:48'),
(52, 7, 'tap', NULL, 'impression', '2025-10-29 07:49:48'),
(53, 6, 'tap', NULL, 'impression', '2025-10-29 08:20:26'),
(54, 6, 'tap', NULL, 'impression', '2025-10-29 08:20:26'),
(55, 8, 'spin', NULL, 'impression', '2025-10-29 08:21:48'),
(56, 8, 'spin', NULL, 'impression', '2025-10-29 08:21:48'),
(57, 8, 'tap', NULL, 'impression', '2025-10-29 08:25:54'),
(58, 8, 'tap', NULL, 'impression', '2025-10-29 08:25:54'),
(59, 1, 'spin', NULL, 'impression', '2025-10-29 08:26:32'),
(60, 1, 'spin', NULL, 'impression', '2025-10-29 08:26:32'),
(61, 6, 'spin', NULL, 'impression', '2025-10-29 08:47:41'),
(62, 6, 'spin', NULL, 'impression', '2025-10-29 08:47:41'),
(63, 3, 'spin', NULL, 'impression', '2025-10-29 12:55:01'),
(64, 3, 'spin', NULL, 'impression', '2025-10-29 12:55:01'),
(65, 6, 'tap', NULL, 'impression', '2025-10-29 17:18:24'),
(66, 6, 'tap', NULL, 'impression', '2025-10-29 17:18:25'),
(67, 6, 'spin', NULL, 'impression', '2025-10-29 17:19:28'),
(68, 6, 'spin', NULL, 'impression', '2025-10-29 17:19:28'),
(69, 2, 'spin', NULL, 'impression', '2025-10-29 17:19:45'),
(70, 2, 'spin', NULL, 'impression', '2025-10-29 17:19:45'),
(71, 2, 'task_ad', NULL, 'impression', '2025-10-29 17:20:40'),
(72, 2, 'task_ad', NULL, 'impression', '2025-10-29 17:20:40'),
(73, 9, 'tap', 8, 'impression', '2025-10-31 13:28:23'),
(74, 9, 'tap', 8, 'impression', '2025-10-31 13:28:23'),
(75, 9, 'tap', 8, 'impression', '2025-10-31 13:28:27'),
(76, 9, 'tap', 8, 'impression', '2025-10-31 13:28:28'),
(77, 9, 'task', 10, 'impression', '2025-10-31 13:29:46'),
(78, 9, 'task', 10, 'impression', '2025-10-31 13:29:46'),
(79, 9, 'task', 10, 'impression', '2025-10-31 13:29:52'),
(80, 9, 'task', 10, 'impression', '2025-10-31 13:29:52'),
(81, 9, 'task', 10, 'impression', '2025-10-31 13:29:54'),
(82, 9, 'task', 10, 'impression', '2025-10-31 13:29:54'),
(83, 9, 'task', 10, 'impression', '2025-10-31 13:29:58'),
(84, 9, 'task', 10, 'impression', '2025-10-31 13:29:58'),
(85, 9, 'task', 10, 'impression', '2025-10-31 13:30:00'),
(86, 9, 'task', 10, 'impression', '2025-10-31 13:30:00'),
(87, 9, 'task', 10, 'impression', '2025-10-31 13:30:01'),
(88, 9, 'task', 10, 'impression', '2025-10-31 13:30:01'),
(89, 9, 'task', 10, 'impression', '2025-10-31 13:30:02'),
(90, 9, 'task', 10, 'impression', '2025-10-31 13:30:02');

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
(1, 'tap', 'Tap & Earn Ads', 8, 3, 1, 3),
(2, 'spin', 'Spin Wheel Ads', 2, 12, 8, 1),
(3, 'game_preroll', 'Game Pre-roll Ads', 7, 3, 1, 1),
(4, 'task', 'Task Completion Ads', 10, 9, NULL, 1),
(5, 'shortlink', 'Short Link Ads', 3, 9, NULL, 1),
(6, 'wallet', 'Wallet Watch Ads', 4, 12, NULL, 1),
(7, 'task_ad', 'Task Watch Ad Placement', 10, 10, NULL, 1);

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
(1, 1, 'Adexium Interstitial', '8391da33-7acd-47a9-8d83-f7b4bf4956b1', 'interstitial', 'tap', 1, '2025-10-31 10:38:31'),
(2, 2, 'Monetag Rewarded Interstitial', '10113890', 'rewarded', 'spin', 1, '2025-10-31 10:38:31'),
(3, 2, 'Monetag In-App Interstitial', '10113890', 'interstitial', 'game_preroll', 1, '2025-10-31 10:38:31'),
(4, 2, 'Monetag Rewarded Popup', '10113890', 'rewarded', 'wallet', 1, '2025-10-31 10:38:31'),
(5, 4, 'Richads Push-style', '375934', 'native', 'tap', 1, '2025-10-31 10:38:31'),
(6, 4, 'Richads Embedded Banner', '375935', 'banner', 'shortlink', 1, '2025-10-31 10:38:31'),
(7, 4, 'Richads Interstitial Banner', '375936', 'interstitial', 'game_preroll', 1, '2025-10-31 10:38:31'),
(8, 4, 'Richads Interstitial Video', '375937', 'interstitial', 'spin', 1, '2025-10-31 10:38:31'),
(9, 4, 'Richads Playable Ads', '375938', 'native', 'task', 1, '2025-10-31 10:38:31'),
(10, 3, 'Adsgram Task', 'task-16619', 'native', 'task', 1, '2025-10-31 10:38:31'),
(11, 3, 'Adsgram Interstitial', 'int-16618', 'interstitial', 'tap', 1, '2025-10-31 10:38:31'),
(12, 3, 'Adsgram Reward', '16617', 'rewarded', 'wallet', 1, '2025-10-31 10:38:31');

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
(1, 'bot_name', 'CoinTap Pro🎁', '2025-10-31 11:13:44'),
(2, 'bot_username', '@CoinTapProBot', '2025-10-31 11:13:31'),
(3, 'welcome_message', 'Welcome to Earn Bot! Start tapping to earn coins!', '2025-10-26 18:45:35'),
(4, 'support_contact', '@CoinTapProSupport', '2025-10-31 11:13:31'),
(5, 'tap_reward', '1', '2025-10-31 11:12:29'),
(6, 'energy_per_tap', '3', '2025-10-31 11:12:29'),
(7, 'energy_recharge_rate', '5', '2025-10-26 18:45:35'),
(8, 'energy_recharge_interval', '30000', '2025-10-31 11:12:29'),
(9, 'watch_ad_energy', '2', '2025-10-31 11:12:29'),
(10, 'tap_ad_frequency', '3', '2025-10-31 11:12:29'),
(11, 'spin_interval_minutes', '5', '2025-10-31 11:09:02'),
(12, 'spin_daily_limit', '50', '2025-10-31 11:09:02'),
(13, 'referral_reward', '50', '2025-10-31 11:12:29'),
(14, 'referral_unlock_tasks', '1', '2025-10-26 18:45:35'),
(15, 'min_withdrawal', '1000', '2025-10-31 11:09:45'),
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
(1, 'xvKkAk', 'https://youtu.be/ayK3YZVGpPY?si=QSva--Vv8i20IUnT', 'direct_ad', NULL, NULL, 5, 0, '2025-10-27 10:21:11'),
(2, 'MN1xnP', 'https://youtu.be/ayK3YZVGpPY?si=QSva--Vv8i20UnT', 'direct_ad', NULL, 12, 0, 0, '2025-10-31 11:50:28'),
(3, 'yS20Vy', 'https://youtu.be/ay3YZVGpPY?si=QSva--Vv8i20IUnT', 'direct_ad', NULL, 12, 0, 0, '2025-10-31 12:16:16'),
(4, 'VXN2jE', 'https://youtu.be/T7G3Nx4zXVo?si=2dGIFv_50-GWp2Ty', 'direct_ad', NULL, 12, 1, 0, '2025-10-31 13:53:36'),
(5, 'hThAKF', 'https://youtu.be/ayK3YZVGpPY?si=QSva--Vv8i20IUnT', 'direct_ad', NULL, 12, 3, 0, '2025-10-31 14:00:41');

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
(1, 'Task 1', '', 'https://youtu.be/xZUliXTObP4?si=zx-_s3pD-8SLpotC', 20.00, 'fas fa-tasks', 'one_time', 1, 0, 'monetag', '2025-10-27 10:12:29'),
(2, 'Watch Ad & Earn 50 Coins', 'Watch ad daily for 50 coins', '#watch-ad', 50.00, 'fas fa-video', 'daily', 1, 1, 'adsgram', '2025-10-29 08:24:13'),
(3, 'Yyy', 'Uuu', 'https://youtu.be/xZUliXTObP4?si=zx-_s3pD-8SLpotC', 50.00, 'fas fa-tasks', 'daily', 1, 0, 'adsgram', '2025-10-29 08:47:12');

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
(33, 2, 'tap', 185.00, 'Earned from 37 taps', '2025-10-28 07:43:19'),
(34, 2, 'tap', 395.00, 'Earned from 79 taps', '2025-10-28 09:00:46'),
(35, 2, 'tap', 5.00, 'Earned from 1 taps', '2025-10-28 09:00:48'),
(36, 2, 'tap', 15.00, 'Earned from 3 taps', '2025-10-28 09:00:50'),
(37, 3, 'tap', 295.00, 'Earned from 59 taps', '2025-10-28 09:30:20'),
(38, 3, 'task', 20.00, 'Task completed: Task 1', '2025-10-28 09:30:28'),
(39, 3, 'tap', 60.00, 'Earned from 12 taps', '2025-10-28 14:58:41'),
(40, 3, 'tap', 160.00, 'Earned from 32 taps', '2025-10-28 15:04:10'),
(41, 3, 'spin', 50.00, 'Spin wheel: 50', '2025-10-28 15:38:37'),
(42, 3, 'tap', 115.00, 'Earned from 23 taps', '2025-10-28 15:40:19'),
(43, 3, 'tap', 100.00, 'Earned from 20 taps', '2025-10-28 15:52:41'),
(44, 3, 'withdrawal', -10.00, 'Withdrawal request #1', '2025-10-28 15:56:25'),
(45, 3, 'withdrawal', -100.00, 'Withdrawal request #2', '2025-10-28 15:56:45'),
(46, 3, 'withdrawal', -100.00, 'Withdrawal request #3', '2025-10-28 15:57:05'),
(47, 3, 'tap', 110.00, 'Earned from 22 taps', '2025-10-28 16:04:37'),
(48, 3, 'spin', 10.00, 'Spin wheel: 10', '2025-10-28 16:52:54'),
(49, 3, 'tap', 100.00, 'Earned from 20 taps', '2025-10-28 16:53:05'),
(50, 1, 'spin', 20.00, 'Spin wheel: 20', '2025-10-28 16:54:42'),
(51, 1, 'withdrawal', -50.00, 'Withdrawal request #4', '2025-10-28 16:55:02'),
(52, 1, 'tap', 90.00, 'Earned from 18 taps', '2025-10-28 16:55:16'),
(53, 4, 'spin', 10.00, 'Spin wheel: 10', '2025-10-28 17:09:09'),
(54, 5, 'spin', 20.00, 'Spin wheel: 20', '2025-10-28 17:09:51'),
(55, 5, 'tap', 125.00, 'Earned from 25 taps', '2025-10-28 17:09:58'),
(56, 5, 'task', 20.00, 'Task completed: Task 1', '2025-10-28 17:10:12'),
(57, 5, 'tap', 95.00, 'Earned from 19 taps', '2025-10-28 17:10:26'),
(58, 5, 'tap', 20.00, 'Earned from 4 taps', '2025-10-28 17:10:32'),
(59, 1, 'tap', 115.00, 'Earned from 23 taps', '2025-10-28 17:19:52'),
(60, 3, 'tap', 60.00, 'Earned from 12 taps', '2025-10-28 17:21:12'),
(61, 3, 'tap', 65.00, 'Earned from 13 taps', '2025-10-28 17:21:12'),
(62, 3, 'tap', 35.00, 'Earned from 7 taps', '2025-10-29 05:31:10'),
(63, 3, 'tap', 80.00, 'Earned from 16 taps', '2025-10-29 05:33:25'),
(64, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 05:33:26'),
(65, 3, 'tap', 85.00, 'Earned from 17 taps', '2025-10-29 05:33:30'),
(66, 3, 'tap', 135.00, 'Earned from 27 taps', '2025-10-29 05:33:36'),
(67, 3, 'tap', 50.00, 'Earned from 10 taps', '2025-10-29 05:33:39'),
(68, 3, 'tap', 15.00, 'Earned from 3 taps', '2025-10-29 05:33:40'),
(69, 3, 'tap', 35.00, 'Earned from 7 taps', '2025-10-29 07:36:40'),
(70, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:36:41'),
(71, 3, 'tap', 50.00, 'Earned from 10 taps', '2025-10-29 07:36:44'),
(72, 3, 'tap', 10.00, 'Earned from 2 taps', '2025-10-29 07:36:48'),
(73, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:36:49'),
(74, 3, 'tap', 10.00, 'Earned from 2 taps', '2025-10-29 07:36:49'),
(75, 3, 'tap', 130.00, 'Earned from 26 taps', '2025-10-29 07:37:11'),
(76, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:37:13'),
(77, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:37:15'),
(78, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:37:25'),
(79, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:37:27'),
(80, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:37:28'),
(81, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:37:29'),
(82, 3, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:37:30'),
(83, 6, 'tap', 70.00, 'Earned from 14 taps', '2025-10-29 07:40:20'),
(84, 6, 'tap', 65.00, 'Earned from 13 taps', '2025-10-29 07:40:27'),
(85, 6, 'tap', 35.00, 'Earned from 7 taps', '2025-10-29 07:40:30'),
(86, 6, 'tap', 30.00, 'Earned from 6 taps', '2025-10-29 07:40:33'),
(87, 6, 'withdrawal', -20.00, 'Withdrawal request #5', '2025-10-29 07:40:54'),
(88, 6, 'tap', 40.00, 'Earned from 8 taps', '2025-10-29 07:41:42'),
(89, 6, 'tap', 30.00, 'Earned from 6 taps', '2025-10-29 07:41:45'),
(90, 6, 'tap', 25.00, 'Earned from 5 taps', '2025-10-29 07:41:47'),
(91, 6, 'tap', 30.00, 'Earned from 6 taps', '2025-10-29 07:41:50'),
(92, 6, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:44:32'),
(93, 6, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:44:33'),
(94, 6, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:44:34'),
(95, 6, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:44:35'),
(96, 6, 'tap', 25.00, 'Earned from 5 taps', '2025-10-29 07:44:42'),
(97, 6, 'tap', 35.00, 'Earned from 7 taps', '2025-10-29 07:44:48'),
(98, 6, 'tap', 20.00, 'Earned from 4 taps', '2025-10-29 07:44:52'),
(99, 6, 'tap', 25.00, 'Earned from 5 taps', '2025-10-29 07:44:53'),
(100, 6, 'spin', 20.00, 'Spin wheel: 20', '2025-10-29 07:45:40'),
(101, 6, 'task', 20.00, 'Task completed: Task 1', '2025-10-29 07:45:50'),
(102, 2, 'spin', 50.00, 'Spin wheel: 50', '2025-10-29 07:47:29'),
(103, 2, 'tap', 50.00, 'Earned from 10 taps', '2025-10-29 07:47:48'),
(104, 7, 'tap', 35.00, 'Earned from 7 taps', '2025-10-29 07:49:01'),
(105, 7, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:49:02'),
(106, 7, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:49:05'),
(107, 7, 'tap', 15.00, 'Earned from 3 taps', '2025-10-29 07:49:06'),
(108, 7, 'tap', 25.00, 'Earned from 5 taps', '2025-10-29 07:49:09'),
(109, 7, 'tap', 20.00, 'Earned from 4 taps', '2025-10-29 07:49:15'),
(110, 7, 'tap', 30.00, 'Earned from 6 taps', '2025-10-29 07:49:21'),
(111, 7, 'tap', 35.00, 'Earned from 7 taps', '2025-10-29 07:49:27'),
(112, 7, 'tap', 10.00, 'Earned from 2 taps', '2025-10-29 07:49:42'),
(113, 7, 'tap', 15.00, 'Earned from 3 taps', '2025-10-29 07:49:43'),
(114, 7, 'tap', 10.00, 'Earned from 2 taps', '2025-10-29 07:49:44'),
(115, 7, 'tap', 5.00, 'Earned from 1 taps', '2025-10-29 07:49:45'),
(116, 7, 'tap', 65.00, 'Earned from 13 taps', '2025-10-29 07:49:48'),
(117, 6, 'tap', 65.00, 'Earned from 13 taps', '2025-10-29 08:20:13'),
(118, 6, 'tap', 20.00, 'Earned from 4 taps', '2025-10-29 08:20:15'),
(119, 6, 'tap', 25.00, 'Earned from 5 taps', '2025-10-29 08:20:19'),
(120, 6, 'tap', 30.00, 'Earned from 6 taps', '2025-10-29 08:20:21'),
(121, 6, 'tap', 25.00, 'Earned from 5 taps', '2025-10-29 08:20:23'),
(122, 6, 'tap', 20.00, 'Earned from 4 taps', '2025-10-29 08:20:24'),
(123, 6, 'tap', 15.00, 'Earned from 3 taps', '2025-10-29 08:20:26'),
(124, 8, 'spin', 50.00, 'Spin wheel: 50', '2025-10-29 08:21:56'),
(125, 8, 'task', 20.00, 'Task completed: Task 1', '2025-10-29 08:22:20'),
(126, 8, 'tap', 15.00, 'Earned from 3 taps', '2025-10-29 08:22:23'),
(127, 8, 'tap', 40.00, 'Earned from 8 taps', '2025-10-29 08:25:52'),
(128, 8, 'tap', 20.00, 'Earned from 4 taps', '2025-10-29 08:25:54'),
(129, 3, 'tap', 190.00, 'Earned from 38 taps', '2025-10-29 12:55:00'),
(130, 6, 'tap', 10.00, 'Earned from 2 taps', '2025-10-29 17:18:24'),
(131, 9, 'tap', 1.00, 'Earned from 1 taps', '2025-10-31 13:28:01'),
(132, 9, 'tap', 1.00, 'Earned from 1 taps', '2025-10-31 13:28:02'),
(133, 9, 'tap', 6.00, 'Earned from 6 taps', '2025-10-31 13:28:20'),
(134, 9, 'tap', 13.00, 'Earned from 13 taps', '2025-10-31 13:28:23'),
(135, 9, 'tap', 1.00, 'Earned from 1 taps', '2025-10-31 13:28:24'),
(136, 9, 'tap', 1.00, 'Earned from 1 taps', '2025-10-31 13:28:27'),
(137, 9, 'tap', 1.00, 'Earned from 1 taps', '2025-10-31 13:28:27'),
(138, 9, 'tap', 1.00, 'Earned from 1 taps', '2025-10-31 13:28:28'),
(139, 9, 'tap', 1.00, 'Earned from 1 taps', '2025-10-31 13:28:30'),
(140, 6, 'task', 50.00, 'Task completed: Watch Ad & Earn 50 Coins', '2025-10-31 14:11:34');

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
(1, 489077790, 'Sagar_systum', 'Channel Seller', '', 1550.00, 100, '2025-10-29 08:26:29', 'C942232A', NULL, 0, '2025-10-26 18:56:47', '2025-10-29 08:26:29'),
(2, 6901424701, 'Trusted_seller_req', 'Seller', '', 1095.00, 100, '2025-10-29 17:19:42', 'A27A2727', NULL, 0, '2025-10-27 16:40:41', '2025-10-29 17:20:34'),
(3, 6164194424, 'KIRA_JOD_MAXX', '𝙆𝙄𝙍𝘼 𝙅𝙊𝘿 ™🇮🇳', '', 1810.00, 62, '2025-10-29 12:55:00', '2BAACD7E', NULL, 0, '2025-10-28 09:30:08', '2025-10-29 12:54:52'),
(4, 7542336054, '', 'Aryan', '', 10.00, 100, '2025-10-28 17:09:04', '2819F752', NULL, 0, '2025-10-28 17:09:04', '2025-10-28 17:09:04'),
(5, 7582563062, 'Op_bp67', '𝑂𝑃', '', 280.00, 52, '2025-10-28 17:10:32', 'ECA37383', NULL, 0, '2025-10-28 17:09:48', '2025-10-28 17:09:48'),
(6, 7256084472, 'Satkin_79', 'Satkin', '', 730.00, 100, '2025-10-31 13:54:58', '2821AA8C', NULL, 0, '2025-10-29 07:37:46', '2025-10-31 14:11:25'),
(7, 7116449809, '', 'Aman', 'Op', 275.00, 45, '2025-10-29 07:49:48', '1B0B29BB', NULL, 0, '2025-10-29 07:48:56', '2025-10-29 07:49:56'),
(8, 5905451203, '', 'Pradeep', '', 145.00, 85, '2025-10-29 08:25:54', '019A6594', NULL, 0, '2025-10-29 08:21:44', '2025-10-29 08:25:45'),
(9, 921180043, 'yourdadishere', 'Rao', '', 26.00, 22, '2025-10-31 13:28:30', 'DE4AC562', NULL, 0, '2025-10-31 13:27:59', '2025-10-31 13:29:29'),
(10, 7956174350, 'WinWheelPlus_Support', 'WinWheel', 'Plus', 0.00, 100, '2025-10-31 13:54:00', 'B3A29712', NULL, 0, '2025-10-31 13:54:00', '2025-10-31 13:54:00');

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
(1, 1, 0, '2025-10-28 16:54:42', '2025-10-29'),
(2, 2, 1, '2025-10-29 07:47:29', '2025-10-29'),
(3, 3, 0, '2025-10-28 16:52:54', '2025-10-29'),
(4, 4, 1, '2025-10-28 17:09:09', '2025-10-28'),
(5, 5, 1, '2025-10-28 17:09:51', '2025-10-28'),
(6, 6, 0, '2025-10-29 07:45:40', '2025-10-31'),
(7, 7, 0, NULL, '2025-10-29'),
(8, 8, 1, '2025-10-29 08:21:56', '2025-10-29'),
(9, 9, 0, NULL, '2025-10-31'),
(10, 10, 0, NULL, '2025-10-31');

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
(1, 1, 312, 1, 1, 0, 1, 0, '2025-10-28 17:19:52'),
(2, 2, 205, 1, 1, 0, 0, 0, '2025-10-29 07:47:48'),
(3, 3, 388, 2, 1, 0, 2, 0, '2025-10-29 12:55:00'),
(4, 4, 0, 1, 0, 0, 1, 0, '2025-10-28 17:09:09'),
(5, 5, 48, 1, 1, 0, 2, 0, '2025-10-28 17:10:32'),
(6, 6, 132, 1, 2, 0, 0, 0, '2025-10-31 14:11:34'),
(7, 7, 55, 0, 0, 0, 0, 0, '2025-10-29 07:49:48'),
(8, 8, 15, 1, 1, 0, 0, 0, '2025-10-29 08:25:54'),
(9, 9, 26, 0, 0, 0, 0, 0, '2025-10-31 13:28:30'),
(10, 10, 0, 0, 0, 0, 0, 0, '2025-10-31 13:54:00');

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
(2, 2, 1, 'completed', '2025-10-27 17:22:38', '2025-10-27'),
(3, 3, 1, 'completed', '2025-10-28 09:30:28', '2025-10-28'),
(4, 5, 1, 'completed', '2025-10-28 17:10:12', '2025-10-28'),
(5, 6, 1, 'completed', '2025-10-29 07:45:50', '2025-10-29'),
(8, 7, 1, 'pending', NULL, '2025-10-29'),
(10, 8, 1, 'completed', '2025-10-29 08:22:20', '2025-10-29'),
(14, 6, 2, 'completed', '2025-10-31 14:11:34', '2025-10-31'),
(16, 6, 3, 'pending', NULL, '2025-10-31'),
(18, 2, 2, 'pending', NULL, '2025-10-29'),
(19, 9, 1, 'pending', NULL, '2025-10-31');

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
-- Dumping data for table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `user_id`, `amount`, `payment_method`, `payment_details`, `status`, `admin_note`, `payment_proof`, `transaction_id`, `created_at`, `processed_at`) VALUES
(1, 3, 10.00, 'Crypto', '{\"wallet_address\":\"Hsy\"}', 'approved', NULL, 'Hehe', '882', '2025-10-28 15:56:25', '2025-10-28 15:58:04'),
(2, 3, 100.00, 'PayPal', '{\"email\":\"Hshs@gmail.com\"}', 'approved', NULL, 'Hhsjs', '6372', '2025-10-28 15:56:45', '2025-10-28 15:58:11'),
(3, 3, 100.00, 'Bank Transfer', '{\"account_number\":\"7822\",\"ifsc_code\":\"Hshjs\",\"account_name\":\"Huew\"}', 'approved', NULL, 'Hhss', '6272', '2025-10-28 15:57:05', '2025-10-28 15:58:19'),
(4, 1, 50.00, 'Crypto', '{\"wallet_address\":\"Huu\"}', 'pending', NULL, NULL, NULL, '2025-10-28 16:55:02', NULL),
(5, 6, 20.00, 'UPI', '{\"upi_id\":\"Hjii\"}', 'pending', NULL, NULL, NULL, '2025-10-29 07:40:54', NULL);

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
-- AUTO_INCREMENT for table `ad_logs`
--
ALTER TABLE `ad_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `ad_networks`
--
ALTER TABLE `ad_networks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ad_placements`
--
ALTER TABLE `ad_placements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ad_units`
--
ALTER TABLE `ad_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT for table `short_links`
--
ALTER TABLE `short_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `spin_config`
--
ALTER TABLE `spin_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=141;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_games`
--
ALTER TABLE `user_games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_spins`
--
ALTER TABLE `user_spins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_stats`
--
ALTER TABLE `user_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_tasks`
--
ALTER TABLE `user_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

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
