-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 07:50 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gigghana`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `profile_picture` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `uuid`, `name`, `email`, `password_hash`, `role`, `profile_picture`, `is_active`, `last_login`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'abd25535-209e-11f1-93eb-f0761c2b872c', 'Super Admin', 'superadmin@gigghana.com', '$2y$12$tempHashWillBeReplacedBySetupWizard000000000000000000000', 'super_admin', NULL, 1, NULL, NULL, '2026-03-15 18:42:08', '2026-03-15 18:42:08');

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_attempts`
--

CREATE TABLE `admin_login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_login_attempts`
--

INSERT INTO `admin_login_attempts` (`id`, `email`, `ip_address`, `attempted_at`) VALUES
(7, 'superadmin@gigghana.com', '127.0.0.1', '2026-03-15 17:57:39'),
(8, 'superadmin@gigghana.com', '127.0.0.1', '2026-03-15 17:57:41'),
(9, 'superadmin@gigghana.com', '127.0.0.1', '2026-03-15 17:57:42'),
(10, 'superadmin@gigghana.com', '127.0.0.1', '2026-03-15 17:57:43'),
(17, 'gigghana123', '::1', '2026-03-15 18:04:43'),
(18, 'admin@gigghana.com', '::1', '2026-03-15 18:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logs`
--

INSERT INTO `admin_logs` (`id`, `admin_id`, `action`, `target_type`, `target_id`, `notes`, `ip_address`, `created_at`) VALUES
(1, 2, 'admin_login', 'auth', NULL, 'Login OK', '::1', '2026-03-15 18:04:57');

-- --------------------------------------------------------

--
-- Table structure for table `admin_sessions`
--

CREATE TABLE `admin_sessions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_sessions`
--

INSERT INTO `admin_sessions` (`id`, `admin_id`, `session_token`, `ip_address`, `user_agent`, `is_active`, `expires_at`, `created_at`) VALUES
(1, 2, 'fb41eac22c9d026c4262f5b2957744fae4581447ab51a415745e5edb671e81c3', '::1', NULL, 1, '2026-03-16 02:04:57', '2026-03-15 18:04:57');

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(10) DEFAULT '?',
  `color` varchar(30) DEFAULT 'var(--cyan)',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `name`, `slug`, `icon`, `color`, `description`, `is_active`, `created_at`) VALUES
(1, 'Top Provider', 'top-provider', '🏆', '#F7B731', 'Awarded to providers with 20+ completed jobs', 1, '2026-03-15 18:42:08'),
(2, 'Verified Expert', 'verified-expert', '✓', '#00D4C8', 'Ghana Card and skills verified', 1, '2026-03-15 18:42:08'),
(3, 'Rising Talent', 'rising-talent', '📈', '#7C6FF7', 'Fast-growing freelancer with great reviews', 1, '2026-03-15 18:42:08'),
(4, 'Trusted Client', 'trusted-client', '🤝', '#1FD9A0', 'Client with 5+ completed hires', 1, '2026-03-15 18:42:08'),
(5, 'Premium Member', 'premium-member', '⭐', '#FF6B4A', 'Active premium subscription holder', 1, '2026-03-15 18:42:08'),
(6, '5-Star Rated', 'five-star', '⭐', '#F7B731', 'Maintained 5-star rating for 3+ months', 1, '2026-03-15 18:42:08');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT 'briefcase',
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `parent_id`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Web Development', 'web-development', 'code', 'Full-stack web apps, websites, and APIs', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(2, 'Mobile Apps', 'mobile-apps', 'smartphone', 'iOS and Android app development', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(3, 'Graphic Design', 'graphic-design', 'pen-tool', 'Logos, branding, UI design, and illustrations', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(4, 'Digital Marketing', 'digital-marketing', 'trending-up', 'SEO, social media, ads, and content marketing', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(5, 'Writing & Translation', 'writing-translation', 'file-text', 'Copywriting, content, and translation services', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(6, 'Video & Animation', 'video-animation', 'film', 'Video editing, motion graphics, and 3D animation', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(7, 'Data Science & AI', 'data-science-ai', 'cpu', 'Machine learning, data analysis, and AI solutions', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(8, 'Accounting & Finance', 'accounting-finance', 'dollar-sign', 'Bookkeeping, financial planning, and tax services', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(9, 'Legal Services', 'legal-services', 'briefcase', 'Contracts, compliance, and legal consultation', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(10, 'Virtual Assistant', 'virtual-assistant', 'headphones', 'Admin support, scheduling, and customer service', NULL, 0, 1, '2026-03-13 14:29:29', '2026-03-13 14:29:29'),
(11, 'Skilled Trades', 'skilled-trades', 'tool', 'Carpenter, Plumber, Electrician, Mechanic, Painter', NULL, 10, 1, '2026-03-14 17:45:21', '2026-03-14 17:45:21'),
(12, 'Health & Wellness', 'health-wellness', 'headphones', 'Nurse, Physiotherapist, Fitness Coach, Nutritionist', NULL, 11, 1, '2026-03-14 17:45:21', '2026-03-14 17:45:21'),
(13, 'Construction', 'construction', 'briefcase', 'Builder, Architect, Quantity Surveyor, Mason', NULL, 12, 1, '2026-03-14 17:45:21', '2026-03-14 17:45:21'),
(14, 'Education & Tutoring', 'education-tutoring', 'file-text', 'Teacher, Tutor, Music Instructor, Art Coach', NULL, 13, 1, '2026-03-14 17:45:21', '2026-03-14 17:45:21'),
(15, 'Hospitality', 'hospitality', 'bar-chart', 'Chef, Event Planner, Driver, Housekeeper', NULL, 14, 1, '2026-03-14 17:45:21', '2026-03-14 17:45:21'),
(16, 'Agriculture', 'agriculture', 'globe', 'Farmer, Agri-tech, Livestock, Crop Advisor', NULL, 15, 1, '2026-03-14 17:45:21', '2026-03-14 17:45:21'),
(17, 'Others', 'others', 'trending-up', 'Delivery, Security Guard, Handyman, Cleaning', NULL, 16, 1, '2026-03-14 17:45:21', '2026-03-14 17:45:21');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `last_message_preview` varchar(255) DEFAULT NULL,
  `unread_count_user1` int(11) DEFAULT 0,
  `unread_count_user2` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `uuid`, `user1_id`, `user2_id`, `job_id`, `last_message_at`, `last_message_preview`, `unread_count_user1`, `unread_count_user2`, `created_at`, `updated_at`) VALUES
(1, '69b214dd-78f1-42e5-ae44-a5d626f8bcd4', 1, 2, NULL, '2026-03-15 00:30:12', NULL, 0, 0, '2026-03-14 20:16:01', '2026-03-15 00:30:12'),
(2, 'd467a6fd-e475-4475-aa6e-b12a2c74f1ab', 1, 3, NULL, '2026-03-14 20:18:21', NULL, 0, 0, '2026-03-14 20:18:17', '2026-03-14 20:18:21'),
(3, '1fe1e536-9734-4832-bb11-c91c5027b0ed', 1, 5, NULL, '2026-03-15 01:08:28', NULL, 0, 0, '2026-03-15 00:55:00', '2026-03-15 01:08:28'),
(4, '2eabf170-9b6d-4dcd-801e-4590f812ca29', 3, 7, NULL, '2026-03-15 16:55:16', NULL, 0, 0, '2026-03-15 16:54:04', '2026-03-15 16:55:16');

-- --------------------------------------------------------

--
-- Table structure for table `conversation_status`
--

CREATE TABLE `conversation_status` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_typing` tinyint(1) DEFAULT 0,
  `last_seen_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversation_status`
--

INSERT INTO `conversation_status` (`id`, `conversation_id`, `user_id`, `is_typing`, `last_seen_at`) VALUES
(1, 1, 1, 0, '2026-03-15 00:54:33'),
(4, 1, 2, 0, '2026-03-15 01:07:51'),
(40, 2, 3, 0, '2026-03-15 04:21:31'),
(45, 2, 1, 0, '2026-03-15 04:41:56'),
(56, 3, 1, 0, '2026-03-15 01:09:23'),
(60, 3, 5, 0, '2026-03-15 01:08:43'),
(88, 4, 7, 0, '2026-03-15 16:55:27');

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `proposal_id` int(11) DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT 0.00,
  `client_confirmed` tinyint(1) DEFAULT 0,
  `provider_confirmed` tinyint(1) DEFAULT 0,
  `status` enum('active','client_done','provider_done','completed','disputed','cancelled') DEFAULT 'active',
  `client_confirmed_at` timestamp NULL DEFAULT NULL,
  `provider_confirmed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

CREATE TABLE `disputes` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `job_id` int(11) NOT NULL,
  `raised_by` int(11) NOT NULL,
  `against_user` int(11) NOT NULL,
  `reason` enum('non_delivery','poor_quality','payment_issue','communication','other') NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','under_review','resolved_client','resolved_provider','closed') DEFAULT 'open',
  `resolution_notes` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `escrow`
--

CREATE TABLE `escrow` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `platform_fee` decimal(10,2) DEFAULT 0.00,
  `provider_amount` decimal(14,2) NOT NULL,
  `status` enum('held','released','refunded','disputed') DEFAULT 'held',
  `locked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `released_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fraud_flags`
--

CREATE TABLE `fraud_flags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `flag_type` enum('multiple_accounts','no_completion','excessive_disputes','suspicious_messages','other') NOT NULL,
  `description` text DEFAULT NULL,
  `flagged_by` int(11) DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `client_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(300) NOT NULL,
  `description` text NOT NULL,
  `requirements` text DEFAULT NULL,
  `budget_type` enum('fixed','hourly') DEFAULT 'fixed',
  `budget_min` decimal(12,2) DEFAULT 0.00,
  `budget_max` decimal(12,2) DEFAULT 0.00,
  `duration` enum('less_1_week','1_2_weeks','1_month','3_months','6_months','ongoing') DEFAULT '1_month',
  `experience_level` enum('entry','intermediate','expert','any') DEFAULT 'any',
  `location_type` enum('remote','onsite','hybrid') DEFAULT 'remote',
  `location` varchar(150) DEFAULT NULL,
  `status` enum('draft','open','in_progress','completed','cancelled','disputed') DEFAULT 'open',
  `hired_provider_id` int(11) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `proposal_count` int(11) DEFAULT 0,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_urgent` tinyint(1) DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_flagged` tinyint(1) DEFAULT 0,
  `flag_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `uuid`, `client_id`, `category_id`, `title`, `slug`, `description`, `requirements`, `budget_type`, `budget_min`, `budget_max`, `duration`, `experience_level`, `location_type`, `location`, `status`, `hired_provider_id`, `views`, `proposal_count`, `is_featured`, `is_urgent`, `deadline`, `created_at`, `updated_at`, `is_flagged`, `flag_reason`) VALUES
(1, '5a12df3c-235f-46f5-b5b0-feaf27440b7d', 1, 5, 'wfdgfbadsfdgfggggggggggggggggggggggggggggggggggggg', 'wfdgfbadsfdgfggggggggggggggggggggggggggggggggggggg-68fa63', 'dfgbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbskldfffffff', '', 'fixed', 221.00, 0.00, '1_month', 'any', 'remote', '', 'cancelled', NULL, 9, 1, 0, 0, NULL, '2026-03-14 01:06:10', '2026-03-14 23:39:01', 0, NULL),
(2, 'cbfc9697-eec8-4d7b-890b-61f13f0deb0a', 1, NULL, 'fggggggggggggggggggggggggggggg', 'fggggggggggggggggggggggggggggg-5de074', 'fgggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg', '', 'hourly', 111.00, 0.00, '1_month', 'any', 'remote', '', 'completed', NULL, 4, 1, 0, 0, NULL, '2026-03-14 15:45:46', '2026-03-14 23:16:42', 0, NULL),
(3, '75693622-9e0f-401d-bb2f-72882c9e1845', 1, 1, 'Full-Stack Developer to build a custom E-commerce Website for a local fashion brand.', 'full-stack-developer-to-build-a-custom-e-commerce-website-for-a-local-fashion-br-ed839b', 'We are looking for a talented developer to build a responsive, user-friendly e-commerce platform. The project involves setting up a product catalog, integrating a secure payment gateway (Hubtel or Paystack), and creating an admin dashboard for inventory management. The final deliverable must be mobile-optimized and SEO-ready. Total expected timeline is 4 weeks.', 'Proficiency in React.js and Node.js.\r\n\r\nExperience with local Ghanaian payment API integrations.', 'fixed', 2500.00, 5000.00, '1_month', 'intermediate', 'remote', '', 'open', NULL, 9, 1, 1, 1, NULL, '2026-03-14 23:53:50', '2026-03-15 16:40:16', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_skills`
--

CREATE TABLE `job_skills` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `job_skills`
--

INSERT INTO `job_skills` (`id`, `job_id`, `skill_id`) VALUES
(1, 2, 16),
(2, 3, 4),
(3, 3, 6),
(4, 3, 23);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `message_type` enum('text','image','file','system') DEFAULT 'text',
  `file_url` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT 0,
  `is_read` tinyint(1) DEFAULT 0,
  `is_delivered` tinyint(1) DEFAULT 0,
  `reply_to_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `sender_id`, `content`, `message_type`, `file_url`, `file_name`, `file_size`, `is_read`, `is_delivered`, `reply_to_id`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'hi vardyy', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:16:09', '2026-03-14 20:27:01'),
(2, 1, 1, 'hi snr', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:16:30', '2026-03-14 20:27:01'),
(3, 2, 1, 'fh', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:18:21', '2026-03-14 23:12:36'),
(4, 1, 2, 'hi snt', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:27:36', '2026-03-14 20:28:14'),
(5, 1, 2, 'how are you doing', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:27:42', '2026-03-14 20:28:14'),
(6, 1, 1, '🎉', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:32:23', '2026-03-14 20:43:47'),
(7, 1, 1, 'ruyio', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:33:19', '2026-03-14 20:43:47'),
(8, 1, 1, 'hi', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:42:08', '2026-03-14 20:43:47'),
(9, 1, 2, 'hi', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-14 20:46:32', '2026-03-14 20:47:06'),
(10, 1, 2, 'hi', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-15 00:30:12', '2026-03-15 00:53:34'),
(11, 3, 1, 'hi', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-15 00:55:05', '2026-03-15 00:58:53'),
(12, 3, 5, 'huhu', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-15 00:59:01', '2026-03-15 01:08:23'),
(13, 3, 1, 'brother', 'text', NULL, NULL, 0, 1, 1, NULL, 0, '2026-03-15 01:08:28', '2026-03-15 01:08:43'),
(14, 4, 7, 'BROTHER', 'text', NULL, NULL, 0, 0, 0, NULL, 0, '2026-03-15 16:55:16', '2026-03-15 16:55:16');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(60) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `is_read`, `created_at`) VALUES
(1, 1, 'new_proposal', 'New Proposal Received', 'You received a new proposal for: wfdgfbadsfdgfggggggggggggggggggggggggggggggggggggg', '{\"job_id\":1,\"provider_id\":1}', 0, '2026-03-14 01:15:17'),
(2, 1, 'new_proposal', 'New Proposal Received', 'You received a new proposal for: fggggggggggggggggggggggggggggg', '{\"job_id\":2,\"provider_id\":1}', 0, '2026-03-14 15:47:07'),
(3, 1, 'job_posted', 'Job Posted Successfully', 'Your job \"Full-Stack Developer to build a custom E-commerce Website for a local fashion brand.\" is now live and accepting proposals.', '{\"job_id\":3}', 0, '2026-03-14 23:53:50'),
(4, 1, 'new_proposal', 'New Proposal Received', 'You received a new proposal for: Full-Stack Developer to build a custom E-commerce Website for a local fashion brand.', '{\"job_id\":3,\"provider_id\":1}', 0, '2026-03-14 23:55:56');

-- --------------------------------------------------------

--
-- Table structure for table `platform_settings`
--

CREATE TABLE `platform_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_val` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_settings`
--

INSERT INTO `platform_settings` (`id`, `setting_key`, `setting_val`, `updated_by`, `updated_at`) VALUES
(1, 'commission_rate', '12', NULL, '2026-03-15 17:20:01'),
(2, 'verified_price', '49', NULL, '2026-03-15 17:20:01'),
(3, 'premium_price', '99', NULL, '2026-03-15 17:20:01'),
(4, 'free_proposals_limit', '3', NULL, '2026-03-15 17:20:01'),
(5, 'free_jobs_limit', '5', NULL, '2026-03-15 17:20:01'),
(6, 'require_ghcard', '1', NULL, '2026-03-15 17:20:01'),
(7, 'moderate_jobs', '0', NULL, '2026-03-15 17:20:01'),
(8, 'maintenance_mode', '0', NULL, '2026-03-15 17:20:01'),
(9, 'payment_gateway', 'paystack', NULL, '2026-03-15 17:20:01'),
(10, 'site_name', 'GigGhana', NULL, '2026-03-15 17:20:01'),
(11, 'support_email', 'support@gigghana.com', NULL, '2026-03-15 17:20:01');

-- --------------------------------------------------------

--
-- Table structure for table `portfolio_items`
--

CREATE TABLE `portfolio_items` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `item_type` enum('image','video') DEFAULT 'image',
  `sort_order` int(11) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `portfolio_items`
--

INSERT INTO `portfolio_items` (`id`, `provider_id`, `title`, `description`, `image_url`, `video_url`, `project_url`, `item_type`, `sort_order`, `category_id`, `created_at`) VALUES
(1, 1, 'jhkl', 'ghcvj', 'http://localhost/gigghana/uploads/portfolio/gg_69b585707b91d3.60561541.png', NULL, '', 'image', 1, NULL, '2026-03-14 15:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `job_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `cover_letter` text NOT NULL,
  `bid_amount` decimal(12,2) NOT NULL,
  `delivery_days` int(11) NOT NULL,
  `status` enum('pending','shortlisted','accepted','rejected','withdrawn') DEFAULT 'pending',
  `portfolio_urls` text DEFAULT NULL,
  `client_viewed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`id`, `uuid`, `job_id`, `provider_id`, `cover_letter`, `bid_amount`, `delivery_days`, `status`, `portfolio_urls`, `client_viewed`, `created_at`, `updated_at`) VALUES
(1, '59cc30a3-964e-4057-97a3-90ea3c5d453c', 1, 1, 'dfffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffffff', 1.00, 12, 'pending', '', 1, '2026-03-14 01:15:17', '2026-03-14 15:24:55'),
(2, 'd327e1fe-7bd7-4341-a391-b965d4962a3a', 2, 1, 'effffffffffffffffffffffffffffffffffffffffffffsdffffffffffffffffffffffffffffsdffffffffffffffffffffffffffffff', 121.00, 1, 'pending', '', 1, '2026-03-14 15:47:07', '2026-03-14 15:47:46'),
(3, 'b4a03fe4-6804-4b3c-a5ac-933ee07133c2', 3, 1, '3\r\nJob Details\r\nLocation, skills, extras.\r\nWork Location\r\n*\r\n🌐\r\nRemote\r\nWork from anywhere\r\n📍\r\nOn-site\r\nPhysically present\r\n🔄\r\nHybrid\r\nMix of both\r\nRequired Skills\r\nSelect all that apply\r\nNo skills selected yet — click tags below to add\r\n🔍  Search skills…\r\nData Science &amp; AI\r\nDigital Marketing\r\nGraphic Design\r\nMobile Apps\r\nVideo &amp; Animation\r\nWeb Development\r\nWriting &amp; Translation\r\nSkilled Trades\r\nHealth &amp; Wellness\r\nConstruction\r\nEducation &amp; Tutoring\r\nHospitality\r\nAgriculture\r\nOthers\r\nOptional Extras\r\n🔥\r\nUrgent Job\r\nHighlighted in search results\r\n🌐\r\nRemote OK\r\nProvider can work remotely\r\n🔄\r\nRecurring Job\r\nThis is a repeated task\r\n3\r\nJob Details\r\nLocation, skills, extras.\r\nWork Location\r\n*\r\n🌐\r\nRemote\r\nWork from anywhere\r\n📍\r\nOn-site\r\nPhysically present\r\n🔄\r\nHybrid\r\nMix of both\r\nRequired Skills\r\nSelect all that apply\r\nNo skills selected yet — click tags below to add\r\n🔍  Search skills…\r\nData Science &amp; AI\r\nDigital Marketing\r\nGraphic Design\r\nMobile Apps\r\nVideo &amp; Animation\r\nWeb Development\r\nWriting &amp; Translation\r\nSkilled Trades\r\nHealth &amp; Wellness\r\nConstruction\r\nEducation &amp; Tutoring\r\nHospitality\r\nAgriculture\r\nOthers\r\nOptional Extras\r\n🔥\r\nUrgent Job\r\nHighlighted in search results\r\n🌐\r\nRemote OK\r\nProvider can work remotely\r\n🔄\r\nRecurring Job\r\nThis is a repeated task\r\n3\r\nJob Details\r\nLocation, skills, extras.\r\nWork Location\r\n*\r\n🌐\r\nRemote\r\nWork from anywhere\r\n📍\r\nOn-site\r\nPhysically present\r\n🔄\r\nHybrid\r\nMix of both\r\nRequired Skills\r\nSelect all that apply\r\nNo skills selected yet — click tags below to add\r\n🔍  Search skills…\r\nData Science &amp; AI\r\nDigital Marketing\r\nGraphic Design\r\nMobile Apps\r\nVideo &amp; Animation\r\nWeb Development\r\nWriting &amp; Translation\r\nSkilled Trades\r\nHealth &amp; Wellness\r\nConstruction\r\nEducation &amp; Tutoring\r\nHospitality\r\nAgriculture\r\nOthers\r\nOptional Extras\r\n🔥\r\nUrgent Job\r\nHighlighted in search results\r\n🌐\r\nRemote OK\r\nProvider can work remotely\r\n🔄\r\nRecurring Job\r\nThis is a repeated task\r\n3\r\nJob Details\r\nLocation, skills, extras.\r\nWork Location\r\n*\r\n🌐\r\nRemote\r\nWork from anywhere\r\n📍\r\nOn-site\r\nPhysically present\r\n🔄\r\nHybrid\r\nMix of both\r\nRequired Skills\r\nSelect all that apply\r\nNo skills selected yet — click tags below to add\r\n🔍  Search skills…\r\nData Science &amp; AI\r\nDigital Marketing\r\nGraphic Design\r\nMobile Apps\r\nVideo &amp; Animation\r\nWeb Development\r\nWriting &amp; Translation\r\nSkilled Trades\r\nHealth &amp; Wellness\r\nConstruction\r\nEducation &amp; Tutoring\r\nHospitality\r\nAgriculture\r\nOthers\r\nOptional Extras\r\n🔥\r\nUrgent Job\r\nHighlighted in search results\r\n🌐\r\nRemote OK\r\nProvider can work remotely\r\n🔄\r\nRecurring Job\r\nThis is a repeated task\r\n3\r\nJob Details\r\nLocation, skills, extras.\r\nWork Location\r\n*\r\n🌐\r\nRemote\r\nWork from anywhere\r\n📍\r\nOn-site\r\nPhysically present\r\n🔄\r\nHybrid\r\nMix of both\r\nRequired Skills\r\nSelect all that apply\r\nNo skills selected yet — click tags below to add\r\n🔍  Search skills…\r\nData Science &amp; AI\r\nDigital Marketing\r\nGraphic Design\r\nMobile Apps\r\nVideo &amp; Animation\r\nWeb Development\r\nWriting &amp; Translation\r\nSkilled Trades\r\nHealth &amp; Wellness\r\nConstruction\r\nEducation &amp; Tutoring\r\nHospitality\r\nAgriculture\r\nOthers\r\nOptional Extras\r\n🔥\r\nUrgent Job\r\nHighlighted in search results\r\n🌐\r\nRemote OK\r\nProvider can work remotely\r\n🔄\r\nRecurring Job\r\nThis is a repeated', 1221.00, 4, 'pending', '', 0, '2026-03-14 23:55:56', '2026-03-14 23:55:56');

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tagline` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT 0.00,
  `availability` enum('full_time','part_time','not_available') DEFAULT 'full_time',
  `experience_level` enum('entry','intermediate','expert') DEFAULT 'intermediate',
  `total_earnings` decimal(14,2) DEFAULT 0.00,
  `completed_jobs` int(11) DEFAULT 0,
  `success_rate` decimal(5,2) DEFAULT 0.00,
  `response_time` varchar(50) DEFAULT '1 hour',
  `languages` varchar(255) DEFAULT 'English',
  `portfolio_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `video_intro_url` varchar(500) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `profile_views` int(11) DEFAULT 0,
  `rating_avg` decimal(3,2) DEFAULT 0.00,
  `rating_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subscription_tier` enum('free','verified','premium') DEFAULT 'free',
  `proposals_used` int(11) DEFAULT 0,
  `subscription_expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `providers`
--

INSERT INTO `providers` (`id`, `user_id`, `tagline`, `bio`, `hourly_rate`, `availability`, `experience_level`, `total_earnings`, `completed_jobs`, `success_rate`, `response_time`, `languages`, `portfolio_url`, `linkedin_url`, `github_url`, `video_intro_url`, `is_featured`, `is_verified`, `profile_views`, `rating_avg`, `rating_count`, `created_at`, `updated_at`, `subscription_tier`, `proposals_used`, `subscription_expires_at`) VALUES
(1, 2, '', NULL, 0.00, 'full_time', 'intermediate', 0.00, 0, 0.00, '1 hour', 'English', NULL, NULL, NULL, '', 0, 0, 26, 0.00, 0, '2026-03-14 00:54:56', '2026-03-15 04:34:18', 'free', 0, NULL),
(2, 3, 'Painter', NULL, 40.00, 'part_time', 'expert', 0.00, 0, 0.00, 'Within 1 hour', 'English', '', '', '', NULL, 0, 0, 13, 0.00, 0, '2026-03-14 16:24:04', '2026-03-15 16:54:01', 'free', 0, NULL),
(3, 5, NULL, NULL, 0.00, 'full_time', 'intermediate', 0.00, 0, 0.00, '1 hour', 'English', NULL, NULL, NULL, NULL, 0, 0, 3, 0.00, 0, '2026-03-15 00:23:15', '2026-03-15 17:50:08', 'free', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `provider_packages`
--

CREATE TABLE `provider_packages` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `tier` enum('basic','standard','premium') NOT NULL DEFAULT 'basic',
  `name` varchar(100) NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `delivery_days` int(11) NOT NULL DEFAULT 7,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_packages`
--

INSERT INTO `provider_packages` (`id`, `provider_id`, `tier`, `name`, `price`, `description`, `delivery_days`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'basic', 'Basic', 100.00, 'Simple project, core deliverables only.', 3, 0, '2026-03-14 17:53:41', '2026-03-14 17:53:41'),
(2, 1, 'standard', 'Standard', 250.00, 'Complete solution with revisions included.', 7, 1, '2026-03-14 17:53:41', '2026-03-14 17:53:41'),
(3, 1, 'premium', 'Premium', 500.00, 'Full premium package with priority support.', 14, 2, '2026-03-14 17:53:41', '2026-03-14 17:53:41');

-- --------------------------------------------------------

--
-- Table structure for table `provider_skills`
--

CREATE TABLE `provider_skills` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `proficiency` enum('beginner','intermediate','expert') DEFAULT 'intermediate'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_skills`
--

INSERT INTO `provider_skills` (`id`, `provider_id`, `skill_id`, `proficiency`) VALUES
(15, 1, 16, 'intermediate'),
(16, 1, 186, 'intermediate'),
(17, 2, 17, 'intermediate'),
(18, 2, 178, 'intermediate'),
(19, 2, 181, 'intermediate');

-- --------------------------------------------------------

--
-- Table structure for table `provider_verifications`
--

CREATE TABLE `provider_verifications` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `type` enum('id_verified','payment_verified','skill_certified','email_verified','phone_verified','background_check') NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `rating_overall` decimal(3,2) NOT NULL,
  `rating_communication` decimal(3,2) DEFAULT 0.00,
  `rating_quality` decimal(3,2) DEFAULT 0.00,
  `rating_professionalism` decimal(3,2) DEFAULT 0.00,
  `rating_timeliness` decimal(3,2) DEFAULT 0.00,
  `comment` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_helpful`
--

CREATE TABLE `review_helpful` (
  `id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_providers`
--

CREATE TABLE `saved_providers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `name`, `slug`, `category_id`, `is_active`, `created_at`) VALUES
(1, 'HTML/CSS', 'html-css', 1, 1, '2026-03-13 14:29:29'),
(2, 'JavaScript', 'javascript', 1, 1, '2026-03-13 14:29:29'),
(3, 'PHP', 'php', 1, 1, '2026-03-13 14:29:29'),
(4, 'React', 'react', 1, 1, '2026-03-13 14:29:29'),
(5, 'Node.js', 'nodejs', 1, 1, '2026-03-13 14:29:29'),
(6, 'Python', 'python', 1, 1, '2026-03-13 14:29:29'),
(7, 'Laravel', 'laravel', 1, 1, '2026-03-13 14:29:29'),
(8, 'WordPress', 'wordpress', 1, 1, '2026-03-13 14:29:29'),
(9, 'React Native', 'react-native', 2, 1, '2026-03-13 14:29:29'),
(10, 'Flutter', 'flutter', 2, 1, '2026-03-13 14:29:29'),
(11, 'Logo Design', 'logo-design', 3, 1, '2026-03-13 14:29:29'),
(12, 'UI/UX Design', 'ui-ux-design', 3, 1, '2026-03-13 14:29:29'),
(13, 'Figma', 'figma', 3, 1, '2026-03-13 14:29:29'),
(14, 'SEO', 'seo', 4, 1, '2026-03-13 14:29:29'),
(15, 'Social Media Marketing', 'social-media', 4, 1, '2026-03-13 14:29:29'),
(16, 'Content Writing', 'content-writing', 5, 1, '2026-03-13 14:29:29'),
(17, 'Copywriting', 'copywriting', 5, 1, '2026-03-13 14:29:29'),
(18, 'Video Editing', 'video-editing', 6, 1, '2026-03-13 14:29:29'),
(19, 'After Effects', 'after-effects', 6, 1, '2026-03-13 14:29:29'),
(20, 'Machine Learning', 'machine-learning', 7, 1, '2026-03-13 14:29:29'),
(21, 'Data Analysis', 'data-analysis', 7, 1, '2026-03-13 14:29:29'),
(22, 'Digital Marketing', 'digital-marketing', 1, 1, '2026-03-14 17:45:21'),
(23, 'UI/UX Design', 'ui-ux-design-web', 1, 1, '2026-03-14 17:45:21'),
(24, 'Network Engineering', 'network-engineering', 1, 1, '2026-03-14 17:45:21'),
(25, 'Database Admin', 'database-admin', 1, 1, '2026-03-14 17:45:21'),
(26, 'Cybersecurity', 'cybersecurity', 1, 1, '2026-03-14 17:45:21'),
(27, 'DevOps', 'devops', 1, 1, '2026-03-14 17:45:21'),
(28, 'Photography', 'photography', 3, 1, '2026-03-14 17:45:21'),
(29, 'Videography', 'videography', 3, 1, '2026-03-14 17:45:21'),
(30, 'Animation', 'animation', 3, 1, '2026-03-14 17:45:21'),
(31, 'Brand Identity', 'brand-identity', 3, 1, '2026-03-14 17:45:21'),
(32, 'Illustration', 'illustration', 3, 1, '2026-03-14 17:45:21'),
(33, 'Blog Writing', 'blog-writing', 5, 1, '2026-03-14 17:45:21'),
(34, 'Technical Writing', 'technical-writing', 5, 1, '2026-03-14 17:45:21'),
(35, 'Translation (Twi)', 'translation-twi', 5, 1, '2026-03-14 17:45:21'),
(36, 'Carpenter', 'carpenter', 11, 1, '2026-03-14 17:45:21'),
(37, 'Plumber', 'plumber', 11, 1, '2026-03-14 17:45:21'),
(38, 'Electrician', 'electrician', 11, 1, '2026-03-14 17:45:21'),
(39, 'Mechanic', 'mechanic', 11, 1, '2026-03-14 17:45:21'),
(40, 'Painter / Decorator', 'painter-decorator', 11, 1, '2026-03-14 17:45:21'),
(41, 'Mason / Bricklayer', 'mason-bricklayer', 11, 1, '2026-03-14 17:45:21'),
(42, 'Welder', 'welder', 11, 1, '2026-03-14 17:45:21'),
(43, 'Tiler', 'tiler', 11, 1, '2026-03-14 17:45:21'),
(44, 'Roofer', 'roofer', 11, 1, '2026-03-14 17:45:21'),
(81, 'Nurse', 'nurse', 12, 1, '2026-03-14 17:49:01'),
(82, 'Home Caregiver', 'home-caregiver', 12, 1, '2026-03-14 17:49:01'),
(83, 'Physiotherapist', 'physiotherapist', 12, 1, '2026-03-14 17:49:01'),
(84, 'Fitness Coach', 'fitness-coach', 12, 1, '2026-03-14 17:49:01'),
(85, 'Nutritionist', 'nutritionist', 12, 1, '2026-03-14 17:49:01'),
(86, 'Pharmacist Assistant', 'pharmacist-assistant', 12, 1, '2026-03-14 17:49:01'),
(87, 'Midwife', 'midwife', 12, 1, '2026-03-14 17:49:01'),
(88, 'Mental Health Counselor', 'mental-health-counselor', 12, 1, '2026-03-14 17:49:01'),
(105, 'Builder / Contractor', 'builder-contractor', 13, 1, '2026-03-14 17:50:23'),
(106, 'Architect', 'architect', 13, 1, '2026-03-14 17:50:23'),
(107, 'Quantity Surveyor', 'quantity-surveyor', 13, 1, '2026-03-14 17:50:23'),
(108, 'Interior Designer', 'interior-designer', 13, 1, '2026-03-14 17:50:23'),
(109, 'Landscaper', 'landscaper', 13, 1, '2026-03-14 17:50:23'),
(110, 'Civil Engineer', 'civil-engineer', 13, 1, '2026-03-14 17:50:23'),
(163, 'Math Tutor', 'math-tutor', 14, 1, '2026-03-14 17:53:11'),
(164, 'English Tutor', 'english-tutor', 14, 1, '2026-03-14 17:53:11'),
(165, 'Science Tutor', 'science-tutor', 14, 1, '2026-03-14 17:53:11'),
(166, 'Music Instructor', 'music-instructor', 14, 1, '2026-03-14 17:53:11'),
(167, 'Art Teacher', 'art-teacher', 14, 1, '2026-03-14 17:53:11'),
(168, 'Primary School Teacher', 'primary-teacher', 14, 1, '2026-03-14 17:53:11'),
(169, 'French Tutor', 'french-tutor', 14, 1, '2026-03-14 17:53:11'),
(170, 'Private Chef', 'private-chef', 15, 1, '2026-03-14 17:53:11'),
(171, 'Event Planner', 'event-planner', 15, 1, '2026-03-14 17:53:11'),
(172, 'Driver', 'driver', 15, 1, '2026-03-14 17:53:11'),
(173, 'Waiter / Waitress', 'waiter-waitress', 15, 1, '2026-03-14 17:53:11'),
(174, 'Housekeeper', 'housekeeper', 15, 1, '2026-03-14 17:53:11'),
(175, 'Security Guard', 'security-guard', 15, 1, '2026-03-14 17:53:11'),
(176, 'Bartender', 'bartender', 15, 1, '2026-03-14 17:53:11'),
(177, 'Farmer', 'farmer', 16, 1, '2026-03-14 17:53:11'),
(178, 'Agri-tech Specialist', 'agri-tech', 16, 1, '2026-03-14 17:53:11'),
(179, 'Livestock Manager', 'livestock-manager', 16, 1, '2026-03-14 17:53:11'),
(180, 'Crop Advisor', 'crop-advisor', 16, 1, '2026-03-14 17:53:11'),
(181, 'Agricultural Engineer', 'agricultural-engineer', 16, 1, '2026-03-14 17:53:11'),
(184, 'Delivery Rider', 'delivery-rider', 17, 1, '2026-03-14 17:53:11'),
(185, 'Handyman', 'handyman', 17, 1, '2026-03-14 17:53:11'),
(186, 'Cleaning Staff', 'cleaning-staff', 17, 1, '2026-03-14 17:53:11'),
(187, 'Laundry Services', 'laundry-services', 17, 1, '2026-03-14 17:53:11'),
(188, 'Pest Control', 'pest-control', 17, 1, '2026-03-14 17:53:11');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `reference` varchar(100) NOT NULL,
  `type` enum('deposit','withdrawal','escrow_lock','escrow_release','platform_fee','refund') NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(14,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'GHS',
  `payment_method` enum('mtn_momo','vodafone_cash','airteltigo','card','bank_transfer') DEFAULT 'card',
  `payment_gateway` enum('paystack','hubtel','manual') DEFAULT 'paystack',
  `status` enum('pending','processing','completed','failed','reversed') DEFAULT 'pending',
  `gateway_reference` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_type` enum('avatar','portfolio_image','portfolio_video','document') DEFAULT 'avatar',
  `file_size` int(11) DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('client','provider','admin') DEFAULT 'client',
  `avatar` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Ghana',
  `ghana_card_number` varchar(50) DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `facebook_id` varchar(100) DEFAULT NULL,
  `oauth_provider` enum('email','google','facebook') DEFAULT 'email',
  `ghana_card_verified` tinyint(1) DEFAULT 0,
  `payment_verified` tinyint(1) DEFAULT 0,
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_banned` tinyint(1) DEFAULT 0,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_seen` timestamp NULL DEFAULT NULL,
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `email_verification_token` varchar(100) DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ban_reason` text DEFAULT NULL,
  `suspension_ends_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `role`, `avatar`, `bio`, `location`, `country`, `ghana_card_number`, `google_id`, `facebook_id`, `oauth_provider`, `ghana_card_verified`, `payment_verified`, `email_verified`, `phone_verified`, `is_active`, `is_banned`, `last_login`, `last_seen`, `otp_code`, `otp_expires_at`, `email_verification_token`, `password_reset_token`, `password_reset_expires`, `created_at`, `updated_at`, `ban_reason`, `suspension_ends_at`, `admin_notes`) VALUES
(1, 'cc186d2b-84a0-4405-a6d4-d65d9bf8877a', 'Joe', 'Vardy', 'joevardy2004@gmail.com', 'admin@gigghana.com', '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS', 'client', NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, '2026-03-15 11:55:33', '2026-03-15 11:55:33', NULL, NULL, 'a3ad347743f5f077e3d54a24b1391529d9623ea47ce18559f77531c562db5338', NULL, NULL, '2026-03-14 00:35:52', '2026-03-15 11:55:33', NULL, NULL, NULL),
(2, '864acfe1-d137-42e1-ba9a-d7cfdf9a0260', 'GigGhana', 'Admin', 'admin@gigghana.com', 'admin@gigghana.com', '$2y$12$DyQoSWWnE0n3H79LFqatc.qeyCExuAkL5i.J8XfyHkxQd7o0yqyfW', 'admin', 'http://localhost/gigghana/uploads/avatars/gg_av_2_1773517983.jpg', 'bvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv', '', 'Ghana', '', NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, '2026-03-15 18:04:57', '2026-03-15 01:07:51', NULL, NULL, 'b6b27239f3af15e8a3514fdbe83650d7539b0620d541f5cb6a56528c5db3cbab', NULL, NULL, '2026-03-14 00:54:56', '2026-03-15 18:04:57', NULL, NULL, NULL),
(3, 'a861f805-d2f5-4dbe-9d79-7a97bb96295c', 'Musah', 'Sadik', 'abubakarsadikmusah2004@gmail.com', '+233256259336', '$2y$12$wWj02FRM8UQJuIqBab0C8.cW.cWe8EAMf2qar2YNRFdF7RTkQqUo6', 'provider', 'http://localhost/gigghana/uploads/avatars/gg_av_3_1773505708.jpg', 'a painter refined and scales painted for 20 years Based in ghana', 'kumasi', 'Ghana', '', NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, '2026-03-15 16:49:02', '2026-03-15 16:49:02', NULL, NULL, 'c90c2662b5d572e67885be185bef7f13ba1983d0d2116f20eb13db4a9fac6dd0', NULL, NULL, '2026-03-14 16:24:04', '2026-03-15 16:49:02', NULL, NULL, NULL),
(4, '9c1b2a7c-4ee2-45b7-8d65-2351711f2eef', 'Musah', 'Sadik', 'a@gmail.com', '+233256259336', '$2y$12$NMlL.odjbvxGyEGrVGUhy.eX6pfFi6DH7e3fz.Q3yT1mqaWxObmF.', 'client', NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, '2026-03-15 00:19:07', NULL, NULL, NULL, 'c09c06bb4af58eea6585ae45e57d27d44352facc5e51a89dc3fde2d184e109a2', NULL, NULL, '2026-03-15 00:19:02', '2026-03-15 00:19:07', NULL, NULL, NULL),
(5, '3ac1e28c-ce63-4e79-b685-a24b6c925ef4', 'Musah', 'Sadik', 'ab@gmail.com', '+233256259336', '$2y$12$JvM1ZYnbZf09nkUbGixTf.08OUS5wAYt8xM6SkvD8zc.E0TqUIDPO', 'provider', NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, '2026-03-15 00:23:20', '2026-03-15 01:08:57', NULL, NULL, '4a87cdcc8afa744c19080d92b041064b7a68bed058b3adb00ccd26739151040e', NULL, NULL, '2026-03-15 00:23:15', '2026-03-15 01:08:57', NULL, NULL, NULL),
(6, 'e7652e22-f53a-4af5-8fb7-9c3ea08cd5d9', 'Musah', 'Sadik', 'abubakarsadikmusah2kj004@gmail.com', 'admin@gnuts.org.gh', '$2y$12$p6h7oRoOFAHPckyqFBabdOyz/e/qXf2qVWwGNhaG2bKkC6S3GVVMa', 'client', NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, NULL, NULL, NULL, NULL, '01e41ce636cfd837832bd4347889aa63e0e91a3703a3276fa19e39719d20eb18', NULL, NULL, '2026-03-15 04:06:26', '2026-03-15 04:08:21', NULL, NULL, NULL),
(7, 'ccf3fa01-e031-4963-bc7e-30e99931c2c7', 'Musah', 'Sadik', 'abubakarsadikmusah2004677@gmail.com', 'admin@gmail.com', '$2y$12$2mKJBYygJMqzFSPkekJzxeXoIPF8uj72YI/C6peOsSClQYoUH8K1u', 'client', NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, '2026-03-15 17:48:58', '2026-03-15 17:48:58', NULL, NULL, 'b656865d9472b4e030a53bcd1433ab6559e69ace00db8034534ffb153f2803d2', NULL, NULL, '2026-03-15 16:53:02', '2026-03-15 17:48:58', NULL, NULL, NULL),
(8, '3353f343-2093-11f1-8b19-f0761c2b872c', 'Super', 'Admin', 'superadmin@gigghana.com', '+233000000000', '$2y$12$gHSLOt3J6BrT9F4aU2PxmeYbWxzFcKQpX5MKoN1pW8DVGmJdO3sMq', 'admin', NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 17:20:01', '2026-03-15 17:20:01', NULL, NULL, NULL),
(9, 'f5be0ad3-f735-4bb7-84d7-6ad4540d7a15', 'Musah Abubakar', 'Sadik', 'abubakarsadikmusah200411@gmail.com', NULL, NULL, 'client', 'https://lh3.googleusercontent.com/a/ACg8ocKOKDMzfk6kq-mdwo1NAG3UBJdfzO0-sJTDtvobiOUYoNSPfDI=s96-c', NULL, NULL, 'Ghana', NULL, '106044548046514144624', NULL, 'google', 0, 0, 1, 0, 1, 0, '2026-03-15 17:33:37', '2026-03-15 17:33:37', NULL, NULL, NULL, NULL, NULL, '2026-03-15 17:32:52', '2026-03-15 17:33:37', NULL, NULL, NULL),
(10, 'f2877f66-5f7f-41ce-97fc-aa58e9137162', 'JOe Vardy', 'Jnr', 'techloomgh@yahoo.com', '', '$2y$12$Rm559eIx9iM90ecoi5WdF.H.focT1nFgyuLpgJNnUKMgIiUOAPbsK', 'client', NULL, NULL, NULL, 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, '2026-03-15 17:49:59', '2026-03-15 17:49:59', NULL, NULL, 'ba9dc0f28312c5c94a75d18e20b8acd49b625372ac7a68318096a8ec6a0bc900', NULL, NULL, '2026-03-15 17:49:51', '2026-03-15 17:49:59', NULL, NULL, NULL),
(11, '16e23691-209b-11f1-9e6e-f0761c2b872c', 'Samuel', 'Dogbatse', 'sam@freelance.com', '0555000001', '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS', 'provider', NULL, NULL, 'Accra', 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 18:16:30', '2026-03-15 18:16:30', NULL, NULL, NULL),
(12, '16e29de1-209b-11f1-9e6e-f0761c2b872c', 'Prince', 'Nyarko', 'prince@freelance.com', '0555000002', '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS', 'provider', NULL, NULL, 'Kumasi', 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 18:16:30', '2026-03-15 18:16:30', NULL, NULL, NULL),
(13, '16e2a0f6-209b-11f1-9e6e-f0761c2b872c', 'Benedicta', 'Gyamfi', 'bene@freelance.com', '0555000003', '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS', 'provider', NULL, NULL, 'Takoradi', 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 18:16:30', '2026-03-15 18:16:30', NULL, NULL, NULL),
(14, '16e2a27b-209b-11f1-9e6e-f0761c2b872c', 'Emanuel', 'Tetteh', 'emma@freelance.com', '0555000004', '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS', 'provider', NULL, NULL, 'Accra', 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 18:16:30', '2026-03-15 18:16:30', NULL, NULL, NULL),
(15, '16e2a384-209b-11f1-9e6e-f0761c2b872c', 'Janet', 'Ofori', 'janet@freelance.com', '0555000005', '$2y$12$RSSTyFyJAy9/jCxJ/h9VQejzet1a4hVMAVSDADw2Lwz//WXsk9IXS', 'provider', NULL, NULL, 'Tema', 'Ghana', NULL, NULL, NULL, 'email', 0, 0, 1, 0, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 18:16:30', '2026-03-15 18:16:30', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `awarded_by` int(11) DEFAULT NULL,
  `awarded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('ghana_card','premium','identity','business') NOT NULL,
  `document_url` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `available_balance` decimal(14,2) DEFAULT 0.00,
  `pending_balance` decimal(14,2) DEFAULT 0.00,
  `total_earned` decimal(14,2) DEFAULT 0.00,
  `total_spent` decimal(14,2) DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'GHS',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `available_balance`, `pending_balance`, `total_earned`, `total_spent`, `currency`, `created_at`, `updated_at`) VALUES
(1, 1, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-14 00:35:52', '2026-03-14 00:35:52'),
(2, 2, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-14 00:54:56', '2026-03-14 00:54:56'),
(3, 3, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-14 16:24:04', '2026-03-14 16:24:04'),
(4, 4, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-15 00:19:02', '2026-03-15 00:19:02'),
(5, 5, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-15 00:23:15', '2026-03-15 00:23:15'),
(6, 6, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-15 04:06:26', '2026-03-15 04:06:26'),
(7, 7, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-15 16:53:02', '2026-03-15 16:53:02'),
(8, 9, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-15 17:32:52', '2026-03-15 17:32:52'),
(9, 10, 0.00, 0.00, 0.00, 0.00, 'GHS', '2026-03-15 17:49:51', '2026-03-15 17:49:51');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(14,2) NOT NULL,
  `method` enum('mtn_momo','vodafone_cash','airteltigo','bank_transfer') NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `status` enum('pending','processing','completed','failed') DEFAULT 'pending',
  `reference` varchar(100) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_login_attempts`
--
ALTER TABLE `admin_login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip` (`ip_address`),
  ADD KEY `idx_email` (`email`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin` (`admin_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_token` (`session_token`);

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_categories_slug` (`slug`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_user1` (`user1_id`),
  ADD KEY `idx_user2` (`user2_id`),
  ADD KEY `conversations_ibfk_3` (`job_id`),
  ADD KEY `idx_users_pair` (`user1_id`,`user2_id`),
  ADD KEY `idx_last_msg` (`last_message_at`);

--
-- Indexes for table `conversation_status`
--
ALTER TABLE `conversation_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_conv_user` (`conversation_id`,`user_id`),
  ADD KEY `idx_conv` (`conversation_id`),
  ADD KEY `cs_ibfk_2` (`user_id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_job` (`job_id`),
  ADD KEY `idx_client` (`client_id`),
  ADD KEY `idx_provider` (`provider_id`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `raised_by` (`raised_by`);

--
-- Indexes for table `escrow`
--
ALTER TABLE `escrow`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `job_id` (`job_id`);

--
-- Indexes for table `fraud_flags`
--
ALTER TABLE `fraud_flags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_client` (`client_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `jobs_hired_provider_fk` (`hired_provider_id`);

--
-- Indexes for table `job_skills`
--
ALTER TABLE `job_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_job_skill` (`job_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `messages_ibfk_3` (`reply_to_id`),
  ADD KEY `idx_conv_created` (`conversation_id`,`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sort` (`provider_id`,`sort_order`),
  ADD KEY `idx_provider_sort` (`provider_id`,`sort_order`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `unique_job_provider` (`job_id`,`provider_id`),
  ADD KEY `idx_job` (`job_id`),
  ADD KEY `idx_provider` (`provider_id`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_rating` (`rating_avg`);

--
-- Indexes for table `provider_packages`
--
ALTER TABLE `provider_packages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_provider_tier` (`provider_id`,`tier`),
  ADD KEY `idx_provider` (`provider_id`);

--
-- Indexes for table `provider_skills`
--
ALTER TABLE `provider_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_provider_skill` (`provider_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `provider_verifications`
--
ALTER TABLE `provider_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_provider_type` (`provider_id`,`type`),
  ADD KEY `idx_provider` (`provider_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_job_review` (`job_id`,`reviewer_id`),
  ADD KEY `idx_reviewee` (`reviewee_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `review_helpful`
--
ALTER TABLE `review_helpful`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_helpful` (`review_id`,`user_id`),
  ADD KEY `idx_review` (`review_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_saved` (`user_id`,`job_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `saved_providers`
--
ALTER TABLE `saved_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_saved_prov` (`user_id`,`provider_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_skills_slug` (`slug`),
  ADD KEY `idx_skills_category` (`category_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_reference` (`reference`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uf_user` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD UNIQUE KEY `facebook_id` (`facebook_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_uuid` (`uuid`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `idx_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_login_attempts`
--
ALTER TABLE `admin_login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_sessions`
--
ALTER TABLE `admin_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `conversation_status`
--
ALTER TABLE `conversation_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `escrow`
--
ALTER TABLE `escrow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fraud_flags`
--
ALTER TABLE `fraud_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `job_skills`
--
ALTER TABLE `job_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `platform_settings`
--
ALTER TABLE `platform_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `provider_packages`
--
ALTER TABLE `provider_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `provider_skills`
--
ALTER TABLE `provider_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `provider_verifications`
--
ALTER TABLE `provider_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_helpful`
--
ALTER TABLE `review_helpful`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_providers`
--
ALTER TABLE `saved_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_3` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `conversation_status`
--
ALTER TABLE `conversation_status`
  ADD CONSTRAINT `cs_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `disputes_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  ADD CONSTRAINT `disputes_ibfk_2` FOREIGN KEY (`raised_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `escrow`
--
ALTER TABLE `escrow`
  ADD CONSTRAINT `escrow_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_hired_provider_fk` FOREIGN KEY (`hired_provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jobs_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_skills`
--
ALTER TABLE `job_skills`
  ADD CONSTRAINT `job_skills_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`reply_to_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `portfolio_items`
--
ALTER TABLE `portfolio_items`
  ADD CONSTRAINT `portfolio_items_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `proposals`
--
ALTER TABLE `proposals`
  ADD CONSTRAINT `proposals_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `proposals_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `providers`
--
ALTER TABLE `providers`
  ADD CONSTRAINT `providers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_packages`
--
ALTER TABLE `provider_packages`
  ADD CONSTRAINT `pp_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_skills`
--
ALTER TABLE `provider_skills`
  ADD CONSTRAINT `provider_skills_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_verifications`
--
ALTER TABLE `provider_verifications`
  ADD CONSTRAINT `pv_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `review_helpful`
--
ALTER TABLE `review_helpful`
  ADD CONSTRAINT `rh_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rh_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD CONSTRAINT `saved_jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_jobs_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_providers`
--
ALTER TABLE `saved_providers`
  ADD CONSTRAINT `saved_providers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_providers_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills`
--
ALTER TABLE `skills`
  ADD CONSTRAINT `skills_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uf_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
