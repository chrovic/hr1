-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 28, 2025 at 05:53 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hr1_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_analysis_context`
--

CREATE TABLE `ai_analysis_context` (
  `id` int NOT NULL,
  `evaluation_id` int NOT NULL,
  `employee_profile` json DEFAULT NULL COMMENT 'Employee background, role, department, experience',
  `evaluator_profile` json DEFAULT NULL COMMENT 'Evaluator background, relationship to employee',
  `evaluation_context` json DEFAULT NULL COMMENT 'Evaluation cycle, model, competencies being evaluated',
  `performance_history` json DEFAULT NULL COMMENT 'Historical performance data, previous evaluations',
  `organizational_context` json DEFAULT NULL COMMENT 'Department goals, company values, strategic objectives',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `ai_analysis_insights`
--

CREATE TABLE `ai_analysis_insights` (
  `id` int NOT NULL,
  `evaluation_id` int NOT NULL,
  `insight_type` enum('strength','improvement_area','development_opportunity','risk','recommendation') NOT NULL,
  `insight_text` text NOT NULL,
  `confidence_score` decimal(3,2) NOT NULL DEFAULT '0.00',
  `competency_id` int DEFAULT NULL,
  `priority_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `actionable` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `ai_analysis_log`
--

CREATE TABLE `ai_analysis_log` (
  `id` int NOT NULL,
  `analysis_type` enum('sentiment','summarization','competency_gap') NOT NULL,
  `input_text` text,
  `result` text,
  `confidence` decimal(3,2) DEFAULT NULL,
  `analysis_method` varchar(50) DEFAULT 'placeholder',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `ai_analysis_patterns`
--

CREATE TABLE `ai_analysis_patterns` (
  `id` int NOT NULL,
  `pattern_type` enum('sentiment_trend','competency_gap','evaluator_bias','department_pattern') NOT NULL,
  `pattern_description` text NOT NULL,
  `affected_employees` json DEFAULT NULL,
  `affected_departments` json DEFAULT NULL,
  `confidence_score` decimal(3,2) NOT NULL DEFAULT '0.00',
  `severity_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `recommendations` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `ai_analysis_results`
--

CREATE TABLE `ai_analysis_results` (
  `id` int NOT NULL,
  `evaluation_id` int NOT NULL,
  `analysis_type` enum('competency_feedback','training_feedback','performance_review') NOT NULL,
  `sentiment` enum('positive','negative','neutral') NOT NULL,
  `sentiment_confidence` decimal(3,2) NOT NULL DEFAULT '0.00',
  `summary` text,
  `original_length` int DEFAULT '0',
  `summary_length` int DEFAULT '0',
  `compression_ratio` decimal(3,2) DEFAULT '0.00',
  `analysis_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `ai_analysis_results`
--

INSERT INTO `ai_analysis_results` (`id`, `evaluation_id`, `analysis_type`, `sentiment`, `sentiment_confidence`, `summary`, `original_length`, `summary_length`, `compression_ratio`, `analysis_data`, `created_at`, `updated_at`) VALUES
(42, 14, 'competency_feedback', 'neutral', '0.98', 'Evaluation Summary: Meets performance standards Overall assessment indicates satisfactory performance.', 248, 102, '0.41', '{\"analysis_timestamp\": \"2025-09-28 08:08:18\", \"competency_analysis\": [{\"score\": \"5.00\", \"weight\": \"1.00\", \"comments\": \"always observance\", \"sentiment\": \"neutral\", \"confidence\": 0.63, \"competency_name\": \"sales\", \"contextual_comments\": \"Competency: sales (Score: 5.00/5). Comment: always observance\"}, {\"score\": \"5.00\", \"weight\": \"1.00\", \"comments\": \"good\", \"sentiment\": \"neutral\", \"confidence\": 0.5599999999999999, \"competency_name\": \"fdsfsdf\", \"contextual_comments\": \"Competency: fdsfsdf (Score: 5.00/5). Comment: good\"}]}', '2025-09-28 08:08:18', '2025-09-28 08:08:18');

-- --------------------------------------------------------

--
-- Table structure for table `ai_model_performance`
--

CREATE TABLE `ai_model_performance` (
  `id` int NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `analysis_type` varchar(50) NOT NULL,
  `accuracy_score` decimal(3,2) DEFAULT NULL,
  `processing_time_ms` int DEFAULT NULL,
  `success_rate` decimal(3,2) DEFAULT NULL,
  `error_count` int DEFAULT '0',
  `last_updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `ai_recommendation_log`
--

CREATE TABLE `ai_recommendation_log` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `recommendation_type` enum('training','development','career') NOT NULL,
  `recommendations` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `target_audience` enum('all','managers','hr','specific') DEFAULT 'all',
  `status` enum('draft','active','inactive','archived') DEFAULT 'draft',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `certifications_catalog`
--

CREATE TABLE `certifications_catalog` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `issuing_body` varchar(200) DEFAULT NULL,
  `validity_period_months` int DEFAULT '24',
  `renewal_required` tinyint(1) DEFAULT '1',
  `cost` decimal(10,2) DEFAULT '0.00',
  `prerequisites` text,
  `exam_required` tinyint(1) DEFAULT '0',
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `certifications_catalog`
--

INSERT INTO `certifications_catalog` (`id`, `name`, `description`, `issuing_body`, `validity_period_months`, `renewal_required`, `cost`, `prerequisites`, `exam_required`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Google Analytics Certified', 'Google Analytics certification for e-commerce tracking', 'Google', 12, 1, '0.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(2, 'Shopify Partner Certification', 'Official Shopify development and management certification', 'Shopify', 24, 1, '11200.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22'),
(3, 'WooCommerce Specialist', 'WordPress e-commerce specialization certification', 'WooCommerce', 18, 1, '8400.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22'),
(4, 'Digital Marketing Professional', 'Comprehensive digital marketing certification', 'HubSpot', 12, 1, '16800.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22'),
(5, 'E-Commerce Security Expert', 'E-commerce security and compliance certification', 'PCI Security Standards Council', 24, 1, '28000.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22'),
(6, 'Conversion Rate Optimization', 'CRO specialist certification', 'CXL Institute', 12, 1, '22400.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22'),
(7, 'Mobile Commerce Specialist', 'Mobile-first e-commerce development certification', 'Mobile Marketing Association', 18, 1, '19600.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22'),
(8, 'E-Commerce Law & Compliance', 'Legal compliance for online businesses', 'E-Commerce Law Institute', 24, 1, '33600.00', NULL, 0, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22'),
(9, 'Google Analytics Certified', 'Google Analytics certification for e-commerce tracking', 'Google', 12, 1, '0.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(10, 'Shopify Partner Certification', 'Official Shopify development and management certification', 'Shopify', 24, 1, '11200.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22'),
(11, 'WooCommerce Specialist', 'WordPress e-commerce specialization certification', 'WooCommerce', 18, 1, '8400.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22'),
(12, 'Digital Marketing Professional', 'Comprehensive digital marketing certification', 'HubSpot', 12, 1, '16800.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22'),
(13, 'E-Commerce Security Expert', 'E-commerce security and compliance certification', 'PCI Security Standards Council', 24, 1, '28000.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22'),
(14, 'Conversion Rate Optimization', 'CRO specialist certification', 'CXL Institute', 12, 1, '22400.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22'),
(15, 'Mobile Commerce Specialist', 'Mobile-first e-commerce development certification', 'Mobile Marketing Association', 18, 1, '19600.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22'),
(16, 'E-Commerce Law & Compliance', 'Legal compliance for online businesses', 'E-Commerce Law Institute', 24, 1, '33600.00', NULL, 0, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22'),
(17, 'Google Analytics Certified', 'Google Analytics certification for e-commerce tracking', 'Google', 12, 1, '0.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(18, 'Shopify Partner Certification', 'Official Shopify development and management certification', 'Shopify', 24, 1, '11200.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22'),
(19, 'WooCommerce Specialist', 'WordPress e-commerce specialization certification', 'WooCommerce', 18, 1, '8400.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22'),
(20, 'Digital Marketing Professional', 'Comprehensive digital marketing certification', 'HubSpot', 12, 1, '16800.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22'),
(21, 'E-Commerce Security Expert', 'E-commerce security and compliance certification', 'PCI Security Standards Council', 24, 1, '28000.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22'),
(22, 'Conversion Rate Optimization', 'CRO specialist certification', 'CXL Institute', 12, 1, '22400.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22'),
(23, 'Mobile Commerce Specialist', 'Mobile-first e-commerce development certification', 'Mobile Marketing Association', 18, 1, '19600.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22'),
(24, 'E-Commerce Law & Compliance', 'Legal compliance for online businesses', 'E-Commerce Law Institute', 24, 1, '33600.00', NULL, 0, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22');

-- --------------------------------------------------------

--
-- Table structure for table `competencies`
--

CREATE TABLE `competencies` (
  `id` int NOT NULL,
  `model_id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `weight` decimal(3,2) DEFAULT '1.00',
  `max_score` int DEFAULT '5',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `competencies`
--

INSERT INTO `competencies` (`id`, `model_id`, `name`, `description`, `weight`, `max_score`, `created_at`, `updated_at`) VALUES
(87, 20, 'Communication Skills', 'Ability to effectively communicate with clients and team members', '0.25', 5, '2025-09-26 17:01:58', '2025-09-26 17:01:58'),
(88, 20, 'Product Knowledge', 'Understanding of products and services offered', '0.20', 5, '2025-09-26 17:01:58', '2025-09-26 17:01:58'),
(89, 20, 'Customer Service', 'Quality of customer interactions and support', '0.25', 5, '2025-09-26 17:01:58', '2025-09-26 17:01:58'),
(90, 20, 'Sales Performance', 'Achievement of sales targets and goals', '0.20', 5, '2025-09-26 17:01:58', '2025-09-26 17:01:58'),
(91, 20, 'Team Collaboration', 'Working effectively with team members', '0.10', 5, '2025-09-26 17:01:58', '2025-09-26 17:01:58'),
(92, 21, 'Technical', 'new ', '1.00', 5, '2025-09-26 17:43:24', '2025-09-26 17:43:24'),
(93, 21, 'meet the standard work', 'asd', '1.00', 5, '2025-09-26 17:43:55', '2025-09-26 17:43:55'),
(94, 21, 'Communication', 'how it interacts', '1.00', 5, '2025-09-26 17:45:00', '2025-09-26 17:45:00'),
(95, 34, 'sales', 'dfsfs', '1.00', 5, '2025-09-28 08:00:58', '2025-09-28 08:00:58'),
(96, 34, 'fdsfsdf', 'dsfsdfds', '1.00', 5, '2025-09-28 08:03:43', '2025-09-28 08:03:43');

-- --------------------------------------------------------

--
-- Table structure for table `competency_models`
--

CREATE TABLE `competency_models` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `target_roles` json DEFAULT NULL,
  `assessment_method` enum('self_assessment','manager_review','peer_review','360_feedback') DEFAULT 'self_assessment',
  `status` enum('active','draft','archived') DEFAULT 'active',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `competency_models`
--

INSERT INTO `competency_models` (`id`, `name`, `description`, `category`, `target_roles`, `assessment_method`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(17, 'Entry Level E-Commerce', 'Basic competencies for new e-commerce employees', 'Management', '[\"employee\", \" junior_staff\"]', 'self_assessment', 'archived', 1, '2025-09-26 16:57:16', '2025-09-26 16:57:35'),
(18, 'Sales Performance Model', 'Comprehensive evaluation framework for sales team members', NULL, NULL, 'self_assessment', 'active', 1, '2025-09-26 17:01:16', '2025-09-26 17:01:16'),
(19, 'Sales Performance Model', 'Comprehensive evaluation framework for sales team members', NULL, NULL, 'self_assessment', 'active', 1, '2025-09-26 17:01:42', '2025-09-26 17:01:42'),
(20, 'Sales Performance Model', 'Comprehensive evaluation framework for sales team members', NULL, NULL, 'self_assessment', 'active', 1, '2025-09-26 17:01:58', '2025-09-26 17:01:58'),
(21, 'Entry Level E-Commerce', 'Basic competencies for new e-commerce employees', 'Communication', '[\"employee\", \" junior_staff\"]', 'manager_review', 'active', 1, '2025-09-26 17:43:01', '2025-09-26 17:43:01'),
(22, 'asdasd', 'hjgkhjkhj', 'Technical', '[\"employee\"]', '360_feedback', 'archived', 1, '2025-09-27 04:02:36', '2025-09-27 04:12:12'),
(23, 'Chrovic1212', 'xdfgxg', 'Communication', '[\"employee\"]', 'manager_review', 'archived', 1, '2025-09-27 04:05:24', '2025-09-27 04:12:02'),
(24, 'Christian Rovic Ocop Castrodes', 'jkljkjjjj', 'Technical', '[\"jkljklj\"]', 'self_assessment', 'archived', 1, '2025-09-27 04:21:05', '2025-09-27 04:29:38'),
(25, 'Christian Rovic Ocop Castrodes', 'asdasd', 'Technical', '[\"employee\"]', 'peer_review', 'archived', 1, '2025-09-27 04:35:18', '2025-09-27 05:29:58'),
(26, 'logistic', 'gfdf', 'Sales', '[\"gfdgdfg\"]', 'manager_review', 'archived', 1, '2025-09-28 07:29:26', '2025-09-28 07:29:51'),
(27, 'dasd', 'sdad', 'Leadership', '[\"asdasd\"]', 'manager_review', 'archived', 1, '2025-09-28 07:32:01', '2025-09-28 07:32:21'),
(28, 'fsdf', 'asdasd', 'Leadership', '[\"dasda\"]', 'manager_review', 'archived', 1, '2025-09-28 07:33:45', '2025-09-28 07:36:48'),
(29, 'fsdf', 'dfgdf', 'Sales', '[\"jklj\"]', 'peer_review', 'archived', 1, '2025-09-28 07:38:05', '2025-09-28 07:40:33'),
(30, 'asdsada', 'asd', 'Sales', '[\"asd\"]', 'manager_review', 'archived', 1, '2025-09-28 07:42:11', '2025-09-28 07:42:22'),
(31, 'logistic', 'feg', 'Leadership', '[\"fgdg\"]', 'manager_review', 'archived', 1, '2025-09-28 07:47:29', '2025-09-28 07:49:03'),
(32, 'logistic', 'asd', 'Sales', '[\"asd\"]', 'self_assessment', 'archived', 1, '2025-09-28 07:50:32', '2025-09-28 07:52:37'),
(33, 'logistic', 'fdsf', 'Technical', '[\"jklj\"]', 'manager_review', 'archived', 1, '2025-09-28 07:53:03', '2025-09-28 07:53:08'),
(34, 'logistic', 'dsfsdfds', 'Technical', '[\"dfssdf\"]', 'self_assessment', 'archived', 1, '2025-09-28 07:55:42', '2025-09-28 17:15:49'),
(35, 'logistic', 'qweqw', 'Leadership', '[\"qweqw\"]', 'peer_review', 'active', 1, '2025-09-28 17:35:35', '2025-09-28 17:35:35');

-- --------------------------------------------------------

--
-- Table structure for table `competency_notifications`
--

CREATE TABLE `competency_notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `notification_type` enum('model_created','model_updated','model_deleted','model_archived','competency_added','competency_updated','competency_deleted','cycle_created','cycle_updated','cycle_deleted','evaluation_assigned','evaluation_completed','evaluation_overdue','score_submitted','report_generated','course_created','course_updated','course_deleted','enrollment_created','enrollment_updated','enrollment_cancelled','employee_enrolled','hr_enrollment_created','training_completed','training_failed','training_overdue','session_created','session_updated','session_cancelled','feedback_submitted','training_score_updated') NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `related_id` int DEFAULT NULL,
  `related_type` enum('model','competency','cycle','evaluation','report','course','enrollment','session') DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `is_important` tinyint(1) DEFAULT '0',
  `action_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `competency_notifications`
--

INSERT INTO `competency_notifications` (`id`, `user_id`, `notification_type`, `title`, `message`, `related_id`, `related_type`, `is_read`, `is_important`, `action_url`, `created_at`, `read_at`) VALUES
(1, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"Chrovic1212\" has been created by System Administrator.', 23, 'model', 1, 1, '?page=competency_models&action=view&id=23', '2025-09-27 04:05:24', '2025-09-27 04:20:53'),
(2, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"Chrovic1212\" has been created by System Administrator.', 23, 'model', 1, 1, '?page=competency_models&action=view&id=23', '2025-09-27 04:05:24', '2025-09-27 05:37:44'),
(3, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"Chrovic1212\" has been archived by {archived_by}.', 23, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:12:02', '2025-09-27 04:20:53'),
(4, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"Chrovic1212\" has been archived by {archived_by}.', 23, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:12:02', '2025-09-27 05:37:44'),
(5, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"asdasd\" has been archived by {archived_by}.', 22, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:12:07', '2025-09-27 04:20:53'),
(6, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"asdasd\" has been archived by {archived_by}.', 22, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:12:07', '2025-09-27 05:37:44'),
(7, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"asdasd\" has been archived by {archived_by}.', 22, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:12:12', '2025-09-27 04:20:53'),
(8, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"asdasd\" has been archived by {archived_by}.', 22, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:12:12', '2025-09-27 05:37:44'),
(9, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"Christian Rovic Ocop Castrodes\" has been created by System Administrator.', 24, 'model', 1, 1, '?page=competency_models&action=view&id=24', '2025-09-27 04:21:05', '2025-09-27 04:23:59'),
(10, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"Christian Rovic Ocop Castrodes\" has been created by System Administrator.', 24, 'model', 1, 1, '?page=competency_models&action=view&id=24', '2025-09-27 04:21:05', '2025-09-27 05:37:44'),
(11, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"Christian Rovic Ocop Castrodes\" has been archived by {archived_by}.', 24, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:29:38', '2025-09-27 04:32:31'),
(12, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"Christian Rovic Ocop Castrodes\" has been archived by {archived_by}.', 24, 'model', 1, 1, '?page=competency_models', '2025-09-27 04:29:38', '2025-09-27 05:37:44'),
(13, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"Christian Rovic Ocop Castrodes\" has been created by System Administrator.', 25, 'model', 1, 1, '?page=competency_models&action=view&id=25', '2025-09-27 04:35:18', '2025-09-27 04:37:16'),
(14, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"Christian Rovic Ocop Castrodes\" has been created by System Administrator.', 25, 'model', 1, 1, '?page=competency_models&action=view&id=25', '2025-09-27 04:35:18', '2025-09-27 05:37:44'),
(15, 1, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"asdasd\" has been created by System Administrator.', 6, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=6', '2025-09-27 05:24:15', '2025-09-27 05:24:17'),
(16, 5, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"asdasd\" has been created by System Administrator.', 6, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=6', '2025-09-27 05:24:15', '2025-09-27 05:37:44'),
(17, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"Christian Rovic Ocop Castrodes\" has been archived by {archived_by}.', 25, 'model', 1, 1, '?page=competency_models', '2025-09-27 05:29:58', '2025-09-27 05:30:01'),
(18, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"Christian Rovic Ocop Castrodes\" has been archived by {archived_by}.', 25, 'model', 1, 1, '?page=competency_models', '2025-09-27 05:29:58', '2025-09-27 05:37:44'),
(19, 1, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"Chrovic\" has been created by System Administrator.', 7, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=7', '2025-09-27 05:33:19', '2025-09-27 05:33:23'),
(20, 5, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"Chrovic\" has been created by System Administrator.', 7, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=7', '2025-09-27 05:33:19', '2025-09-27 05:37:44'),
(21, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 26, 'model', 1, 1, '?page=competency_models&action=view&id=26', '2025-09-28 07:29:26', '2025-09-28 07:29:29'),
(22, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 26, 'model', 1, 1, '?page=competency_models&action=view&id=26', '2025-09-28 07:29:26', '2025-09-28 15:08:16'),
(23, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 26, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:29:51', '2025-09-28 07:29:59'),
(24, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 26, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:29:51', '2025-09-28 15:08:16'),
(25, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"dasd\" has been created by System Administrator.', 27, 'model', 1, 1, '?page=competency_models&action=view&id=27', '2025-09-28 07:32:01', '2025-09-28 07:32:08'),
(26, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"dasd\" has been created by System Administrator.', 27, 'model', 1, 1, '?page=competency_models&action=view&id=27', '2025-09-28 07:32:01', '2025-09-28 15:08:16'),
(27, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"dasd\" has been archived by {archived_by}.', 27, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:32:21', '2025-09-28 07:33:37'),
(28, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"dasd\" has been archived by {archived_by}.', 27, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:32:21', '2025-09-28 15:08:16'),
(29, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"fsdf\" has been created by System Administrator.', 28, 'model', 1, 1, '?page=competency_models&action=view&id=28', '2025-09-28 07:33:45', '2025-09-28 07:36:56'),
(30, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"fsdf\" has been created by System Administrator.', 28, 'model', 1, 1, '?page=competency_models&action=view&id=28', '2025-09-28 07:33:45', '2025-09-28 15:08:16'),
(31, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 28, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:36:48', '2025-09-28 07:36:56'),
(32, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 28, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:36:48', '2025-09-28 15:08:16'),
(33, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"fsdf\" has been created by System Administrator.', 29, 'model', 1, 1, '?page=competency_models&action=view&id=29', '2025-09-28 07:38:05', '2025-09-28 07:38:55'),
(34, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"fsdf\" has been created by System Administrator.', 29, 'model', 1, 1, '?page=competency_models&action=view&id=29', '2025-09-28 07:38:05', '2025-09-28 15:08:16'),
(35, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:05', '2025-09-28 07:40:10'),
(36, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:05', '2025-09-28 15:08:16'),
(37, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:07', '2025-09-28 07:40:10'),
(38, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:07', '2025-09-28 15:08:16'),
(39, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:09', '2025-09-28 07:40:10'),
(40, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:09', '2025-09-28 15:08:16'),
(41, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:11', '2025-09-28 07:40:12'),
(42, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:11', '2025-09-28 15:08:16'),
(43, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:14', '2025-09-28 07:40:36'),
(44, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:14', '2025-09-28 15:08:16'),
(45, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:16', '2025-09-28 07:40:36'),
(46, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:16', '2025-09-28 15:08:16'),
(47, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:18', '2025-09-28 07:40:36'),
(48, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:18', '2025-09-28 15:08:16'),
(49, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:20', '2025-09-28 07:40:36'),
(50, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:20', '2025-09-28 15:08:16'),
(51, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:23', '2025-09-28 07:40:36'),
(52, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:23', '2025-09-28 15:08:16'),
(53, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:26', '2025-09-28 07:40:36'),
(54, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:26', '2025-09-28 15:08:16'),
(55, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:28', '2025-09-28 07:40:36'),
(56, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:28', '2025-09-28 15:08:16'),
(57, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:30', '2025-09-28 07:40:36'),
(58, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:30', '2025-09-28 15:08:16'),
(59, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:33', '2025-09-28 07:40:36'),
(60, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"fsdf\" has been archived by {archived_by}.', 29, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:40:33', '2025-09-28 15:08:16'),
(61, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"asdsada\" has been created by System Administrator.', 30, 'model', 1, 1, '?page=competency_models&action=view&id=30', '2025-09-28 07:42:11', '2025-09-28 07:42:44'),
(62, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"asdsada\" has been created by System Administrator.', 30, 'model', 1, 1, '?page=competency_models&action=view&id=30', '2025-09-28 07:42:11', '2025-09-28 15:08:16'),
(63, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"asdsada\" has been archived by {archived_by}.', 30, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:42:22', '2025-09-28 07:42:44'),
(64, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"asdsada\" has been archived by {archived_by}.', 30, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:42:22', '2025-09-28 15:08:16'),
(65, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 31, 'model', 1, 1, '?page=competency_models&action=view&id=31', '2025-09-28 07:47:29', '2025-09-28 07:49:00'),
(66, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 31, 'model', 1, 1, '?page=competency_models&action=view&id=31', '2025-09-28 07:47:29', '2025-09-28 15:08:16'),
(67, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 31, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:49:03', '2025-09-28 07:52:32'),
(68, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 31, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:49:03', '2025-09-28 15:08:16'),
(69, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 32, 'model', 1, 1, '?page=competency_models&action=view&id=32', '2025-09-28 07:50:32', '2025-09-28 07:52:32'),
(70, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 32, 'model', 1, 1, '?page=competency_models&action=view&id=32', '2025-09-28 07:50:32', '2025-09-28 15:08:16'),
(71, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 32, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:52:37', '2025-09-28 07:52:39'),
(72, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 32, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:52:37', '2025-09-28 15:08:16'),
(73, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 33, 'model', 1, 1, '?page=competency_models&action=view&id=33', '2025-09-28 07:53:03', '2025-09-28 07:53:05'),
(74, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 33, 'model', 1, 1, '?page=competency_models&action=view&id=33', '2025-09-28 07:53:03', '2025-09-28 15:08:16'),
(75, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 33, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:53:08', '2025-09-28 07:53:17'),
(76, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 33, 'model', 1, 1, '?page=competency_models', '2025-09-28 07:53:08', '2025-09-28 15:08:16'),
(77, 1, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 8, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=8', '2025-09-28 07:55:17', '2025-09-28 07:55:30'),
(78, 5, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 8, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=8', '2025-09-28 07:55:17', '2025-09-28 15:08:16'),
(79, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 34, 'model', 1, 1, '?page=competency_models&action=view&id=34', '2025-09-28 07:55:42', '2025-09-28 07:55:50'),
(80, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 34, 'model', 1, 1, '?page=competency_models&action=view&id=34', '2025-09-28 07:55:42', '2025-09-28 15:08:16'),
(81, 1, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 9, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=9', '2025-09-28 07:57:33', '2025-09-28 07:57:43'),
(82, 5, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 9, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=9', '2025-09-28 07:57:33', '2025-09-28 15:08:16'),
(83, 1, 'cycle_updated', 'Evaluation Cycle Updated', 'The evaluation cycle \"logistic\" has been updated by {updated_by}.', 9, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=9', '2025-09-28 07:57:41', '2025-09-28 07:57:43'),
(84, 5, 'cycle_updated', 'Evaluation Cycle Updated', 'The evaluation cycle \"logistic\" has been updated by {updated_by}.', 9, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=9', '2025-09-28 07:57:41', '2025-09-28 15:08:16'),
(85, 1, 'cycle_deleted', 'Evaluation Cycle Deleted', 'The evaluation cycle \"logistic\" has been deleted by {deleted_by}.', 9, 'cycle', 1, 1, '?page=evaluation_cycles', '2025-09-28 07:57:52', '2025-09-28 07:57:53'),
(86, 5, 'cycle_deleted', 'Evaluation Cycle Deleted', 'The evaluation cycle \"logistic\" has been deleted by {deleted_by}.', 9, 'cycle', 1, 1, '?page=evaluation_cycles', '2025-09-28 07:57:52', '2025-09-28 15:08:16'),
(87, 1, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 10, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=10', '2025-09-28 07:59:54', '2025-09-28 07:59:57'),
(88, 5, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 10, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=10', '2025-09-28 07:59:54', '2025-09-28 15:08:16'),
(89, 1, 'cycle_updated', 'Evaluation Cycle Updated', 'The evaluation cycle \"logistic\" has been updated by System Administrator.', 10, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=10', '2025-09-28 08:00:03', '2025-09-28 08:00:04'),
(90, 5, 'cycle_updated', 'Evaluation Cycle Updated', 'The evaluation cycle \"logistic\" has been updated by System Administrator.', 10, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=10', '2025-09-28 08:00:03', '2025-09-28 15:08:16'),
(91, 1, 'cycle_deleted', 'Evaluation Cycle Deleted', 'The evaluation cycle \"logistic\" has been deleted by System Administrator.', 10, 'cycle', 1, 1, '?page=evaluation_cycles', '2025-09-28 08:00:08', '2025-09-28 08:00:09'),
(92, 5, 'cycle_deleted', 'Evaluation Cycle Deleted', 'The evaluation cycle \"logistic\" has been deleted by System Administrator.', 10, 'cycle', 1, 1, '?page=evaluation_cycles', '2025-09-28 08:00:08', '2025-09-28 15:08:16'),
(93, 1, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 11, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=11', '2025-09-28 08:00:23', '2025-09-28 08:00:32'),
(94, 5, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"logistic\" has been created by System Administrator.', 11, 'cycle', 1, 1, '?page=evaluation_cycles&action=view&id=11', '2025-09-28 08:00:23', '2025-09-28 15:08:16'),
(95, 1, 'evaluation_assigned', 'New Evaluation Assigned', 'You have been assigned to evaluate John Doe for the logistic cycle.', 14, 'evaluation', 1, 1, '?page=evaluation_form&id=14', '2025-09-28 08:00:40', '2025-09-28 08:00:41'),
(96, 1, 'competency_added', 'New Competency Added', 'A new competency \"Test Competency\" has been added to model \"Test Model\".', 999, 'competency', 1, 0, '?page=competency_models', '2025-09-28 08:02:45', '2025-09-28 08:03:14'),
(97, 5, 'competency_added', 'New Competency Added', 'A new competency \"Test Competency\" has been added to model \"Test Model\".', 999, 'competency', 1, 0, '?page=competency_models', '2025-09-28 08:02:45', '2025-09-28 15:08:16'),
(98, 1, 'model_updated', 'Competency Model Updated', 'The competency model \"logistic\" has been updated by {updated_by}.', 34, 'model', 1, 0, '?page=competency_models&action=view&id=34', '2025-09-28 08:03:27', '2025-09-28 08:03:29'),
(99, 5, 'model_updated', 'Competency Model Updated', 'The competency model \"logistic\" has been updated by {updated_by}.', 34, 'model', 1, 0, '?page=competency_models&action=view&id=34', '2025-09-28 08:03:27', '2025-09-28 15:08:16'),
(100, 1, 'competency_added', 'New Competency Added', 'A new competency \"fdsfsdf\" has been added to model \"logistic\".', 96, 'competency', 1, 0, '?page=competency_models&action=view&id=34', '2025-09-28 08:03:43', '2025-09-28 08:03:44'),
(101, 5, 'competency_added', 'New Competency Added', 'A new competency \"fdsfsdf\" has been added to model \"logistic\".', 96, 'competency', 1, 0, '?page=competency_models&action=view&id=34', '2025-09-28 08:03:43', '2025-09-28 15:08:16'),
(102, 1, 'model_updated', 'Competency Model Updated', 'The competency model \"Unknown Model\" has been updated by John Doe.', 1, 'model', 1, 0, '?page=competency_models&action=view&id=1', '2025-09-28 08:05:10', '2025-09-28 08:05:39'),
(103, 5, 'model_updated', 'Competency Model Updated', 'The competency model \"Unknown Model\" has been updated by John Doe.', 1, 'model', 1, 0, '?page=competency_models&action=view&id=1', '2025-09-28 08:05:10', '2025-09-28 15:08:16'),
(104, 1, 'model_updated', 'Competency Model Updated', 'The competency model \"logistic\" has been updated by System Administrator.', 34, 'model', 1, 0, '?page=competency_models&action=view&id=34', '2025-09-28 08:05:45', '2025-09-28 08:05:46'),
(105, 5, 'model_updated', 'Competency Model Updated', 'The competency model \"logistic\" has been updated by System Administrator.', 34, 'model', 1, 0, '?page=competency_models&action=view&id=34', '2025-09-28 08:05:45', '2025-09-28 15:08:16'),
(106, 1, 'evaluation_completed', 'Evaluation Completed', 'The evaluation for John Doe has been completed by System Administrator.', 14, 'evaluation', 1, 0, '?page=evaluations&action=view&id=14', '2025-09-28 08:07:44', '2025-09-28 08:07:48'),
(107, 5, 'evaluation_completed', 'Evaluation Completed', 'The evaluation for John Doe has been completed by System Administrator.', 14, 'evaluation', 1, 0, '?page=evaluations&action=view&id=14', '2025-09-28 08:07:44', '2025-09-28 15:08:16'),
(108, 4, 'score_submitted', 'Scores Submitted', 'Competency scores have been submitted for John Doe by System Administrator.', 14, 'evaluation', 1, 0, '?page=my_evaluations&action=view&id=14', '2025-09-28 08:07:44', '2025-09-28 17:41:19'),
(111, 1, 'course_created', 'New Training Course Created', 'A new training course \"Test Course for Notifications\" has been created by System Administrator.', 49, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:21:02', '2025-09-28 08:21:18'),
(112, 5, 'course_created', 'New Training Course Created', 'A new training course \"Test Course for Notifications\" has been created by System Administrator.', 49, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:21:02', '2025-09-28 15:08:16'),
(113, 1, 'course_created', 'New Training Course Created', 'A new training course \"kl\" has been created by System Administrator.', 50, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:42:25', '2025-09-28 08:42:27'),
(114, 5, 'course_created', 'New Training Course Created', 'A new training course \"kl\" has been created by System Administrator.', 50, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:42:25', '2025-09-28 15:08:16'),
(115, 1, 'course_created', 'New Training Course Created', 'A new training course \"Debug Test Course 2025-09-28 10:43:40\" has been created by System Administrator.', 51, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:43:40', '2025-09-28 15:07:57'),
(116, 5, 'course_created', 'New Training Course Created', 'A new training course \"Debug Test Course 2025-09-28 10:43:40\" has been created by System Administrator.', 51, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:43:40', '2025-09-28 15:08:16'),
(117, 1, 'course_created', 'New Training Course Created', 'A new training course \"Debug Test Course 2025-09-28 10:44:03\" has been created by System Administrator.', 52, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:44:03', '2025-09-28 15:07:57'),
(118, 5, 'course_created', 'New Training Course Created', 'A new training course \"Debug Test Course 2025-09-28 10:44:03\" has been created by System Administrator.', 52, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:44:03', '2025-09-28 15:08:16'),
(119, 1, 'course_created', 'New Training Course Created', 'A new training course \"Test Course - 2025-09-28 10:44:32\" has been created by System Administrator.', 53, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:44:32', '2025-09-28 15:07:57'),
(120, 5, 'course_created', 'New Training Course Created', 'A new training course \"Test Course - 2025-09-28 10:44:32\" has been created by System Administrator.', 53, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:44:32', '2025-09-28 15:08:16'),
(121, 1, 'course_created', 'New Training Course Created', 'A new training course \"LearningManager Test - 2025-09-28 10:44:57\" has been created by System Administrator.', 55, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:44:57', '2025-09-28 15:07:57'),
(122, 5, 'course_created', 'New Training Course Created', 'A new training course \"LearningManager Test - 2025-09-28 10:44:57\" has been created by System Administrator.', 55, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:44:57', '2025-09-28 15:08:16'),
(123, 1, 'course_created', 'New Training Course Created', 'A new training course \"Test Course - 2025-09-28 10:45:14\" has been created by System Administrator.', 56, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:45:14', '2025-09-28 15:07:57'),
(124, 5, 'course_created', 'New Training Course Created', 'A new training course \"Test Course - 2025-09-28 10:45:14\" has been created by System Administrator.', 56, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:45:14', '2025-09-28 15:08:16'),
(125, 1, 'course_created', 'New Training Course Created', 'A new training course \"Lifecycle Test - 2025-09-28 10:45:38\" has been created by System Administrator.', 57, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:45:38', '2025-09-28 15:07:57'),
(126, 5, 'course_created', 'New Training Course Created', 'A new training course \"Lifecycle Test - 2025-09-28 10:45:38\" has been created by System Administrator.', 57, 'course', 1, 1, '?page=learning_management', '2025-09-28 08:45:38', '2025-09-28 15:08:16'),
(127, 1, 'course_created', 'New Training Course Created', 'A new training course \"LearningManager Test - 2025-09-28 18:47:06\" has been created by System Administrator.', 60, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:47:06', '2025-09-28 16:47:34'),
(128, 5, 'course_created', 'New Training Course Created', 'A new training course \"LearningManager Test - 2025-09-28 18:47:06\" has been created by System Administrator.', 60, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:47:06', NULL),
(129, 1, 'course_deleted', 'Training Course Deleted', 'The training course \"Lifecycle Test - 2025-09-28 10:45:38\" has been deleted by System Administrator.', 57, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:47:41', '2025-09-28 16:47:42'),
(130, 5, 'course_deleted', 'Training Course Deleted', 'The training course \"Lifecycle Test - 2025-09-28 10:45:38\" has been deleted by System Administrator.', 57, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:47:41', NULL),
(131, 1, 'course_deleted', 'Training Course Deleted', 'The training course \"Test Course - 2025-09-28 10:45:14\" has been deleted by System Administrator.', 56, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:47:45', '2025-09-28 16:48:01'),
(132, 5, 'course_deleted', 'Training Course Deleted', 'The training course \"Test Course - 2025-09-28 10:45:14\" has been deleted by System Administrator.', 56, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:47:45', NULL),
(133, 1, 'course_updated', 'Training Course Updated', 'The training course \"LearningManager Test - 2025-09-28 10:44:57\" has been updated by System Administrator.', 55, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:47:59', '2025-09-28 16:48:01'),
(134, 5, 'course_updated', 'Training Course Updated', 'The training course \"LearningManager Test - 2025-09-28 10:44:57\" has been updated by System Administrator.', 55, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:47:59', NULL),
(135, 1, 'course_deleted', 'Training Course Deleted', 'The training course \"LearningManager Test - 2025-09-28 10:44:57\" has been deleted by System Administrator.', 55, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:48:09', '2025-09-28 16:49:31'),
(136, 5, 'course_deleted', 'Training Course Deleted', 'The training course \"LearningManager Test - 2025-09-28 10:44:57\" has been deleted by System Administrator.', 55, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:48:09', NULL),
(137, 1, 'course_deleted', 'Training Course Deleted', 'The training course \"Test Course - 2025-09-28 10:44:32\" has been deleted by System Administrator.', 53, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:48:12', '2025-09-28 16:49:31'),
(138, 5, 'course_deleted', 'Training Course Deleted', 'The training course \"Test Course - 2025-09-28 10:44:32\" has been deleted by System Administrator.', 53, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:48:12', NULL),
(139, 1, 'course_deleted', 'Training Course Deleted', 'The training course \"Debug Test Course 2025-09-28 10:44:03\" has been deleted by System Administrator.', 52, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:48:15', '2025-09-28 16:49:31'),
(140, 5, 'course_deleted', 'Training Course Deleted', 'The training course \"Debug Test Course 2025-09-28 10:44:03\" has been deleted by System Administrator.', 52, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:48:15', NULL),
(141, 1, 'course_created', 'New Training Course Created', 'A new training course \"dsad\" has been created by System Administrator.', 61, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:48:28', '2025-09-28 16:49:31'),
(142, 5, 'course_created', 'New Training Course Created', 'A new training course \"dsad\" has been created by System Administrator.', 61, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:48:28', NULL),
(143, 1, 'course_deleted', 'Training Course Deleted', 'The training course \"dsad\" has been deleted by System Administrator.', 61, 'course', 1, 1, '?page=learning_management', '2025-09-28 16:48:32', '2025-09-28 16:49:31'),
(144, 5, 'course_deleted', 'Training Course Deleted', 'The training course \"dsad\" has been deleted by System Administrator.', 61, 'course', 0, 1, '?page=learning_management', '2025-09-28 16:48:32', NULL),
(145, 1, 'session_created', 'New Training Session Scheduled', 'A new training session \"tahdsjabsd\" has been scheduled by System Administrator.', 9, 'session', 1, 1, '?page=training_management', '2025-09-28 16:49:24', '2025-09-28 16:49:31'),
(146, 5, 'session_created', 'New Training Session Scheduled', 'A new training session \"tahdsjabsd\" has been scheduled by System Administrator.', 9, 'session', 0, 1, '?page=training_management', '2025-09-28 16:49:24', NULL),
(150, 1, 'session_updated', 'Training Session Updated', 'The training session \"Test Session Update\" has been updated by System Administrator.', 1, 'session', 1, 1, '?page=training_management', '2025-09-28 17:06:46', '2025-09-28 17:07:06'),
(151, 5, 'session_updated', 'Training Session Updated', 'The training session \"Test Session Update\" has been updated by System Administrator.', 1, 'session', 0, 1, '?page=training_management', '2025-09-28 17:06:46', NULL),
(152, 1, 'session_updated', 'Training Session Updated', 'The training session \"tahdsjabsd\" has been updated by System Administrator.', 9, 'session', 1, 1, '?page=training_management', '2025-09-28 17:07:04', '2025-09-28 17:07:06'),
(153, 5, 'session_updated', 'Training Session Updated', 'The training session \"tahdsjabsd\" has been updated by System Administrator.', 9, 'session', 0, 1, '?page=training_management', '2025-09-28 17:07:04', NULL),
(169, 1, 'enrollment_created', 'Test Notification', 'This is a test notification to verify the system is working.', NULL, 'enrollment', 1, 1, '?page=test', '2025-09-28 17:14:57', '2025-09-28 17:15:45'),
(170, 1, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 34, 'model', 1, 1, '?page=competency_models', '2025-09-28 17:15:49', '2025-09-28 17:15:50'),
(171, 5, 'model_archived', 'Competency Model Archived', 'The competency model \"logistic\" has been archived by {archived_by}.', 34, 'model', 0, 1, '?page=competency_models', '2025-09-28 17:15:49', NULL),
(172, 5, 'enrollment_created', 'Test Notification for Widget', 'This is a test notification to verify the widget works.', NULL, 'enrollment', 0, 1, '?page=test', '2025-09-28 17:16:18', NULL),
(189, 4, 'enrollment_created', 'Training Enrollment Created', 'You have been enrolled in training session \"tahdsjabsd\" by System Administrator.', 14, 'enrollment', 1, 1, '?page=my_trainings', '2025-09-28 17:35:12', '2025-09-28 17:41:19'),
(190, 5, 'enrollment_created', 'Employee Enrolled in Training', 'Employee John Doe has been enrolled in training session \"tahdsjabsd\" by System Administrator.', 14, 'enrollment', 0, 1, '?page=training_management', '2025-09-28 17:35:12', NULL),
(191, 1, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 35, 'model', 1, 1, '?page=competency_models&action=view&id=35', '2025-09-28 17:35:35', '2025-09-28 17:35:38'),
(192, 5, 'model_created', 'New Competency Model Created', 'A new competency model \"logistic\" has been created by System Administrator.', 35, 'model', 0, 1, '?page=competency_models&action=view&id=35', '2025-09-28 17:35:35', NULL),
(193, 1, 'course_deleted', 'Training Course Deleted', 'The training course \"Shopify Development\" has been deleted by System Administrator.', 41, 'course', 1, 1, '?page=learning_management', '2025-09-28 17:35:47', '2025-09-28 17:35:48'),
(194, 5, 'course_deleted', 'Training Course Deleted', 'The training course \"Shopify Development\" has been deleted by System Administrator.', 41, 'course', 0, 1, '?page=learning_management', '2025-09-28 17:35:47', NULL),
(195, 4, 'enrollment_created', 'Training Enrollment Created', 'You have been enrolled in training session \"tahdsjabsd\" by System Administrator.', 15, 'enrollment', 1, 1, '?page=my_trainings', '2025-09-28 17:36:03', '2025-09-28 17:41:19'),
(196, 5, 'enrollment_created', 'Employee Enrolled in Training', 'Employee John Doe has been enrolled in training session \"tahdsjabsd\" by System Administrator.', 15, 'enrollment', 0, 1, '?page=training_management', '2025-09-28 17:36:03', NULL),
(216, 4, 'enrollment_created', 'Training Enrollment Created', 'You have been enrolled in training session \"tahdsjabsd\" by System Administrator.', 21, 'enrollment', 1, 1, '?page=my_trainings', '2025-09-28 17:41:00', '2025-09-28 17:41:19'),
(217, 1, 'enrollment_created', 'Employee Enrolled in Training', 'Employee John Doe has been enrolled in training session \"tahdsjabsd\" by System Administrator.', 21, 'enrollment', 1, 1, '?page=training_management', '2025-09-28 17:41:00', '2025-09-28 17:41:02'),
(218, 5, 'enrollment_created', 'Employee Enrolled in Training', 'Employee John Doe has been enrolled in training session \"tahdsjabsd\" by System Administrator.', 21, 'enrollment', 0, 1, '?page=training_management', '2025-09-28 17:41:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `competency_reports`
--

CREATE TABLE `competency_reports` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `report_type` enum('summary','detailed','trend','comparison') DEFAULT 'summary',
  `filters` json DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `competency_reports`
--

INSERT INTO `competency_reports` (`id`, `title`, `description`, `report_type`, `filters`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'E-Commerce Skills Assessment Q4 2024', 'Comprehensive assessment of e-commerce competencies across all departments', 'summary', '{\"date_range\": \"2024-10-01 to 2024-12-31\", \"department\": \"all\"}', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(2, 'Digital Marketing Competency Analysis', 'Detailed analysis of digital marketing skills and gaps', 'detailed', '{\"department\": \"Marketing\", \"competency_model\": \"Digital Marketing Excellence\"}', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(3, 'Technical Skills Trend Report', 'Trend analysis of technical e-commerce skills over time', 'trend', '{\"period\": \"6_months\", \"competency_model\": \"E-Commerce Technical Skills\"}', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(4, 'Department Competency Comparison', 'Comparative analysis of competencies across different departments', 'comparison', '{\"departments\": [\"IT\", \"Marketing\", \"Operations\"], \"competency_models\": \"all\"}', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `competency_scores`
--

CREATE TABLE `competency_scores` (
  `id` int NOT NULL,
  `evaluation_id` int NOT NULL,
  `competency_id` int NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `comments` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `competency_scores`
--

INSERT INTO `competency_scores` (`id`, `evaluation_id`, `competency_id`, `score`, `comments`, `created_at`) VALUES
(102, 14, 95, '5.00', 'always observance', '2025-09-28 08:07:44'),
(103, 14, 96, '5.00', 'good', '2025-09-28 08:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `critical_positions`
--

CREATE TABLE `critical_positions` (
  `id` int NOT NULL,
  `position_title` varchar(255) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `description` text,
  `priority_level` enum('critical','high','medium','low') DEFAULT 'medium',
  `succession_timeline` enum('0-6_months','6-12_months','1-2_years','2-3_years','3-4_years') DEFAULT '1-2_years',
  `risk_level` enum('high','medium','low') DEFAULT 'medium',
  `current_incumbent_id` int DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `employee_certifications`
--

CREATE TABLE `employee_certifications` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `certification_id` int NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `issuing_body` varchar(200) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` int DEFAULT NULL,
  `verification_date` date DEFAULT NULL,
  `status` enum('active','expired','suspended','revoked') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `employee_learning_paths`
--

CREATE TABLE `employee_learning_paths` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `path_id` int NOT NULL,
  `started_date` date DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `progress_percentage` decimal(5,2) DEFAULT '0.00',
  `status` enum('not_started','in_progress','completed','paused','cancelled') DEFAULT 'not_started',
  `assigned_by` int DEFAULT NULL,
  `assigned_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `employee_requests`
--

CREATE TABLE `employee_requests` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `request_type` enum('leave','training','equipment','schedule_change','other') NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `request_date` date NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `employee_requests`
--

INSERT INTO `employee_requests` (`id`, `employee_id`, `request_type`, `title`, `description`, `request_date`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`) VALUES
(1, 4, 'other', 'Customer Experience Excellence', 'I would like to request materials for the learning path: Customer Experience Excellence', '2025-09-25', 'approved', 1, '2025-09-25 15:27:06', NULL, '2025-09-25 15:18:26');

-- --------------------------------------------------------

--
-- Table structure for table `employee_skills`
--

CREATE TABLE `employee_skills` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `skill_id` int NOT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `acquired_date` date DEFAULT NULL,
  `verified_by` int DEFAULT NULL,
  `verification_date` date DEFAULT NULL,
  `status` enum('active','expired','suspended') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int NOT NULL,
  `cycle_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `evaluator_id` int NOT NULL,
  `model_id` int NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `overall_score` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `cycle_id`, `employee_id`, `evaluator_id`, `model_id`, `status`, `overall_score`, `created_at`, `completed_at`) VALUES
(14, 11, 4, 1, 34, 'completed', '5.00', '2025-09-28 08:00:40', '2025-09-28 08:07:44');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_cycles`
--

CREATE TABLE `evaluation_cycles` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `type` enum('quarterly','annual','project_based') DEFAULT 'quarterly',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','active','completed','cancelled') DEFAULT 'draft',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `evaluation_cycles`
--

INSERT INTO `evaluation_cycles` (`id`, `name`, `type`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(11, 'logistic', 'annual', '2025-09-28', '2025-10-30', 'draft', 1, '2025-09-28 08:00:23', '2025-09-28 08:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `evaluation_scores`
--

CREATE TABLE `evaluation_scores` (
  `id` int NOT NULL,
  `evaluation_id` int NOT NULL,
  `competency_id` int NOT NULL,
  `score` decimal(3,1) NOT NULL,
  `comments` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `learning_paths`
--

CREATE TABLE `learning_paths` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `target_role` varchar(100) DEFAULT NULL,
  `estimated_duration_days` int DEFAULT '30',
  `prerequisites` text,
  `learning_objectives` text,
  `status` enum('draft','active','inactive','archived') DEFAULT 'draft',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `learning_paths`
--

INSERT INTO `learning_paths` (`id`, `name`, `description`, `target_role`, `estimated_duration_days`, `prerequisites`, `learning_objectives`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'E-Commerce Fundamentals Path', 'Complete introduction to e-commerce business', 'E-Commerce Specialist', 56, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(2, 'Digital Marketing Mastery', 'Comprehensive digital marketing for e-commerce', 'Digital Marketing Manager', 84, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(3, 'E-Commerce Development', 'Technical skills for e-commerce platforms', 'E-Commerce Developer', 112, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(4, 'Customer Experience Excellence', 'Creating exceptional online customer experiences', 'Customer Experience Manager', 70, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(5, 'E-Commerce Analytics & Optimization', 'Data-driven e-commerce optimization', 'E-Commerce Analyst', 98, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(6, 'Mobile Commerce Specialist', 'Mobile-first e-commerce development', 'Mobile Commerce Developer', 84, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(7, 'E-Commerce Security & Compliance', 'Security and legal compliance for online businesses', 'E-Commerce Security Specialist', 56, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(8, 'E-Commerce Operations Management', 'End-to-end e-commerce operations', 'E-Commerce Operations Manager', 112, NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(9, 'E-Commerce Fundamentals Path', 'Complete introduction to e-commerce business', 'E-Commerce Specialist', 56, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(10, 'Digital Marketing Mastery', 'Comprehensive digital marketing for e-commerce', 'Digital Marketing Manager', 84, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(11, 'E-Commerce Development', 'Technical skills for e-commerce platforms', 'E-Commerce Developer', 112, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(12, 'Customer Experience Excellence', 'Creating exceptional online customer experiences', 'Customer Experience Manager', 70, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(13, 'E-Commerce Analytics & Optimization', 'Data-driven e-commerce optimization', 'E-Commerce Analyst', 98, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(14, 'Mobile Commerce Specialist', 'Mobile-first e-commerce development', 'Mobile Commerce Developer', 84, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(15, 'E-Commerce Security & Compliance', 'Security and legal compliance for online businesses', 'E-Commerce Security Specialist', 56, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(16, 'E-Commerce Operations Management', 'End-to-end e-commerce operations', 'E-Commerce Operations Manager', 112, NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51');

-- --------------------------------------------------------

--
-- Table structure for table `learning_path_modules`
--

CREATE TABLE `learning_path_modules` (
  `id` int NOT NULL,
  `path_id` int NOT NULL,
  `module_id` int NOT NULL,
  `sequence_order` int NOT NULL,
  `is_required` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `notification_preferences`
--

CREATE TABLE `notification_preferences` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `email_notifications` tinyint(1) DEFAULT '1',
  `in_app_notifications` tinyint(1) DEFAULT '1',
  `sms_notifications` tinyint(1) DEFAULT '0',
  `competency_model_created` tinyint(1) DEFAULT '1',
  `competency_model_updated` tinyint(1) DEFAULT '1',
  `competency_model_deleted` tinyint(1) DEFAULT '1',
  `competency_added` tinyint(1) DEFAULT '1',
  `competency_updated` tinyint(1) DEFAULT '1',
  `competency_deleted` tinyint(1) DEFAULT '1',
  `cycle_created` tinyint(1) DEFAULT '1',
  `cycle_updated` tinyint(1) DEFAULT '1',
  `cycle_deleted` tinyint(1) DEFAULT '1',
  `evaluation_assigned` tinyint(1) DEFAULT '1',
  `evaluation_completed` tinyint(1) DEFAULT '1',
  `evaluation_overdue` tinyint(1) DEFAULT '1',
  `score_submitted` tinyint(1) DEFAULT '1',
  `report_generated` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `course_created` tinyint(1) DEFAULT '1',
  `course_updated` tinyint(1) DEFAULT '1',
  `course_deleted` tinyint(1) DEFAULT '1',
  `session_created` tinyint(1) DEFAULT '1',
  `session_updated` tinyint(1) DEFAULT '1',
  `session_deleted` tinyint(1) DEFAULT '1',
  `enrollment_created` tinyint(1) DEFAULT '1',
  `enrollment_completed` tinyint(1) DEFAULT '1',
  `enrollment_cancelled` tinyint(1) DEFAULT '1',
  `training_requested` tinyint(1) DEFAULT '1',
  `training_approved` tinyint(1) DEFAULT '1',
  `training_rejected` tinyint(1) DEFAULT '1',
  `feedback_submitted` tinyint(1) DEFAULT '1',
  `training_completed` tinyint(1) DEFAULT '1',
  `training_failed` tinyint(1) DEFAULT '1',
  `enrollment_updated` tinyint(1) DEFAULT '1',
  `employee_enrolled` tinyint(1) DEFAULT '1',
  `hr_enrollment_created` tinyint(1) DEFAULT '1',
  `training_overdue` tinyint(1) DEFAULT '1',
  `session_cancelled` tinyint(1) DEFAULT '1',
  `training_score_updated` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `notification_preferences`
--

INSERT INTO `notification_preferences` (`id`, `user_id`, `email_notifications`, `in_app_notifications`, `sms_notifications`, `competency_model_created`, `competency_model_updated`, `competency_model_deleted`, `competency_added`, `competency_updated`, `competency_deleted`, `cycle_created`, `cycle_updated`, `cycle_deleted`, `evaluation_assigned`, `evaluation_completed`, `evaluation_overdue`, `score_submitted`, `report_generated`, `created_at`, `updated_at`, `course_created`, `course_updated`, `course_deleted`, `session_created`, `session_updated`, `session_deleted`, `enrollment_created`, `enrollment_completed`, `enrollment_cancelled`, `training_requested`, `training_approved`, `training_rejected`, `feedback_submitted`, `training_completed`, `training_failed`, `enrollment_updated`, `employee_enrolled`, `hr_enrollment_created`, `training_overdue`, `session_cancelled`, `training_score_updated`) VALUES
(1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, '2025-09-27 04:03:39', '2025-09-27 04:03:39', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(2, 2, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, '2025-09-27 04:03:39', '2025-09-27 04:03:39', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(3, 6, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, '2025-09-27 04:03:39', '2025-09-27 04:03:39', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(4, 5, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, '2025-09-27 04:03:39', '2025-09-27 04:03:39', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1),
(5, 4, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, '2025-09-27 04:03:39', '2025-09-27 04:03:39', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notification_templates`
--

CREATE TABLE `notification_templates` (
  `id` int NOT NULL,
  `notification_type` enum('model_created','model_updated','model_deleted','model_archived','competency_added','competency_updated','competency_deleted','cycle_created','cycle_updated','cycle_deleted','evaluation_assigned','evaluation_completed','evaluation_overdue','score_submitted','report_generated','course_created','course_updated','course_deleted','session_created','session_updated','session_deleted','enrollment_created','enrollment_completed','enrollment_cancelled','training_requested','training_approved','training_rejected','feedback_submitted','training_completed','training_failed') NOT NULL,
  `title_template` varchar(200) NOT NULL,
  `message_template` text NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `notification_templates`
--

INSERT INTO `notification_templates` (`id`, `notification_type`, `title_template`, `message_template`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'model_created', 'New Competency Model Created', 'A new competency model \"{model_name}\" has been created by {created_by}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(2, 'model_updated', 'Competency Model Updated', 'The competency model \"{model_name}\" has been updated by {updated_by}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(3, 'model_deleted', 'Competency Model Deleted', 'The competency model \"{model_name}\" has been deleted by {deleted_by}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(4, 'model_archived', 'Competency Model Archived', 'The competency model \"{model_name}\" has been archived by {archived_by}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(5, 'competency_added', 'New Competency Added', 'A new competency \"{competency_name}\" has been added to model \"{model_name}\".', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(6, 'competency_updated', 'Competency Updated', 'The competency \"{competency_name}\" in model \"{model_name}\" has been updated.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(7, 'competency_deleted', 'Competency Deleted', 'The competency \"{competency_name}\" has been removed from model \"{model_name}\".', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(8, 'cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle \"{cycle_name}\" has been created by {created_by}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(9, 'cycle_updated', 'Evaluation Cycle Updated', 'The evaluation cycle \"{cycle_name}\" has been updated by {updated_by}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(10, 'cycle_deleted', 'Evaluation Cycle Deleted', 'The evaluation cycle \"{cycle_name}\" has been deleted by {deleted_by}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(11, 'evaluation_assigned', 'New Evaluation Assigned', 'You have been assigned to evaluate {employee_name} for the {cycle_name} cycle.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(12, 'evaluation_completed', 'Evaluation Completed', 'The evaluation for {employee_name} has been completed by {evaluator_name}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(13, 'evaluation_overdue', 'Evaluation Overdue', 'The evaluation for {employee_name} is overdue. Please complete it as soon as possible.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(14, 'score_submitted', 'Scores Submitted', 'Competency scores have been submitted for {employee_name} by {evaluator_name}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(15, 'report_generated', 'Report Generated', 'A competency report has been generated for {report_scope}.', 1, '2025-09-27 04:03:39', '2025-09-27 04:03:39'),
(16, 'course_created', 'New Training Course Created', 'A new training course \"{course_title}\" has been created by {created_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(17, 'course_updated', 'Training Course Updated', 'The training course \"{course_title}\" has been updated by {updated_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(18, 'course_deleted', 'Training Course Deleted', 'The training course \"{course_title}\" has been deleted by {deleted_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(19, 'session_created', 'New Training Session Scheduled', 'A new training session \"{session_name}\" has been scheduled by {created_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(20, 'session_updated', 'Training Session Updated', 'The training session \"{session_name}\" has been updated by {updated_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(21, 'session_deleted', 'Training Session Deleted', 'The training session \"{session_name}\" has been deleted by {deleted_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(22, 'enrollment_created', 'Training Enrollment Created', 'You have been enrolled in training session \"{session_name}\" by {enrolled_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 17:12:13'),
(23, 'enrollment_completed', 'Training Completed', 'You have successfully completed the training \"{course_title}\".', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(24, 'enrollment_cancelled', 'Training Enrollment Cancelled', 'Your enrollment in \"{session_name}\" has been cancelled by {cancelled_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(25, 'training_requested', 'Training Request Submitted', 'A training request for \"{course_title}\" has been submitted by {employee_name}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(26, 'training_approved', 'Training Request Approved', 'Your training request for \"{course_title}\" has been approved by {approved_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(27, 'training_rejected', 'Training Request Rejected', 'Your training request for \"{course_title}\" has been rejected by {rejected_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(28, 'feedback_submitted', 'Training Feedback Submitted', 'Feedback has been submitted for training session \"{session_name}\" by {submitted_by}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(29, 'training_completed', 'Training Completed Successfully', 'The training \"{course_title}\" has been completed by {employee_name} with a score of {score}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49'),
(30, 'training_failed', 'Training Failed', 'The training \"{course_title}\" has been marked as failed for {employee_name}.', 1, '2025-09-28 08:15:49', '2025-09-28 08:15:49');

-- --------------------------------------------------------

--
-- Table structure for table `skills_catalog`
--

CREATE TABLE `skills_catalog` (
  `id` int NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `skill_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `competency_model_id` int DEFAULT NULL,
  `status` enum('active','inactive','archived') DEFAULT 'active',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `skills_catalog`
--

INSERT INTO `skills_catalog` (`id`, `name`, `description`, `category`, `skill_level`, `competency_model_id`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'E-Commerce Platform Management', 'Managing online store platforms like Shopify, WooCommerce, Magento', 'E-Commerce', 'intermediate', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(2, 'Digital Marketing', 'Online advertising, social media marketing, email campaigns', 'Marketing', 'intermediate', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(3, 'Customer Experience Design', 'Creating seamless online customer journeys', 'UX/UI', 'advanced', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(4, 'E-Commerce Analytics', 'Tracking and analyzing online business performance', 'Analytics', 'intermediate', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(5, 'Payment Processing', 'Managing online payment systems and gateways', 'Technical', 'intermediate', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(6, 'Inventory Management', 'Stock control and supply chain management', 'Operations', 'intermediate', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(7, 'SEO & Content Marketing', 'Search engine optimization and content strategy', 'Marketing', 'advanced', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(8, 'Mobile Commerce', 'Mobile-first e-commerce development', 'Technical', 'advanced', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(9, 'E-Commerce Security', 'Online security and compliance', 'Security', 'advanced', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(10, 'Conversion Optimization', 'Improving website conversion rates', 'Optimization', 'advanced', NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 06:52:41'),
(11, 'E-Commerce Platform Management', 'Managing online store platforms like Shopify, WooCommerce, Magento', 'E-Commerce', 'intermediate', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(12, 'Digital Marketing', 'Online advertising, social media marketing, email campaigns', 'Marketing', 'intermediate', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(13, 'Customer Experience Design', 'Creating seamless online customer journeys', 'UX/UI', 'advanced', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(14, 'E-Commerce Analytics', 'Tracking and analyzing online business performance', 'Analytics', 'intermediate', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(15, 'Payment Processing', 'Managing online payment systems and gateways', 'Technical', 'intermediate', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(16, 'Inventory Management', 'Stock control and supply chain management', 'Operations', 'intermediate', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(17, 'SEO & Content Marketing', 'Search engine optimization and content strategy', 'Marketing', 'advanced', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(18, 'Mobile Commerce', 'Mobile-first e-commerce development', 'Technical', 'advanced', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(19, 'E-Commerce Security', 'Online security and compliance', 'Security', 'advanced', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(20, 'Conversion Optimization', 'Improving website conversion rates', 'Optimization', 'advanced', NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 06:53:40'),
(21, 'E-Commerce Platform Management', 'Managing online store platforms like Shopify, WooCommerce, Magento', 'E-Commerce', 'intermediate', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(22, 'Digital Marketing', 'Online advertising, social media marketing, email campaigns', 'Marketing', 'intermediate', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(23, 'Customer Experience Design', 'Creating seamless online customer journeys', 'UX/UI', 'advanced', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(24, 'E-Commerce Analytics', 'Tracking and analyzing online business performance', 'Analytics', 'intermediate', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(25, 'Payment Processing', 'Managing online payment systems and gateways', 'Technical', 'intermediate', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(26, 'Inventory Management', 'Stock control and supply chain management', 'Operations', 'intermediate', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(27, 'SEO & Content Marketing', 'Search engine optimization and content strategy', 'Marketing', 'advanced', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(28, 'Mobile Commerce', 'Mobile-first e-commerce development', 'Technical', 'advanced', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(29, 'E-Commerce Security', 'Online security and compliance', 'Security', 'advanced', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51'),
(30, 'Conversion Optimization', 'Improving website conversion rates', 'Optimization', 'advanced', NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 06:54:51');

-- --------------------------------------------------------

--
-- Table structure for table `succession_candidates`
--

CREATE TABLE `succession_candidates` (
  `id` int NOT NULL,
  `role_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `readiness_level` enum('ready_now','ready_soon','development_needed') DEFAULT 'development_needed',
  `development_plan` text,
  `notes` text,
  `assessment_date` date DEFAULT NULL,
  `next_review_date` date DEFAULT NULL,
  `assigned_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `description` text,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'company_name', 'HR1 Company', 'Company name', NULL, '2025-09-12 04:14:24'),
(2, 'company_email', 'hr@company.com', 'Company email address', NULL, '2025-09-12 04:14:24'),
(3, 'timezone', 'UTC', 'System timezone', NULL, '2025-09-12 04:14:24'),
(4, 'evaluation_cycle_days', '90', 'Default evaluation cycle duration in days', NULL, '2025-09-12 04:14:24'),
(5, 'training_request_approval_required', 'true', 'Whether training requests require approval', NULL, '2025-09-12 04:14:24'),
(6, 'max_evaluation_cycles', '4', 'Maximum number of active evaluation cycles', NULL, '2025-09-12 04:14:24');

-- --------------------------------------------------------

--
-- Table structure for table `terms_acceptance`
--

CREATE TABLE `terms_acceptance` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `accepted` tinyint(1) NOT NULL DEFAULT '0',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `terms_acceptance`
--

INSERT INTO `terms_acceptance` (`id`, `user_id`, `accepted`, `accepted_at`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-09-26 14:57:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 14:57:30', '2025-09-26 14:57:30'),
(2, 5, 1, '2025-09-26 15:45:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 15:45:58', '2025-09-26 15:45:58'),
(3, 4, 1, '2025-09-28 17:41:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 17:41:16', '2025-09-28 17:41:16');

-- --------------------------------------------------------

--
-- Table structure for table `training_catalog`
--

CREATE TABLE `training_catalog` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `type` enum('in_person','virtual','hybrid','self_paced') DEFAULT 'in_person',
  `duration_hours` int DEFAULT '1',
  `max_participants` int DEFAULT '20',
  `prerequisites` text,
  `learning_objectives` text,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `training_catalog`
--

INSERT INTO `training_catalog` (`id`, `title`, `description`, `category`, `type`, `duration_hours`, `max_participants`, `prerequisites`, `learning_objectives`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Digital Marketing Fundamentals', 'Learn the basics of digital marketing including SEO, social media, and content marketing.', 'Marketing', 'virtual', 8, 25, 'None', 'Understand digital marketing concepts, create social media strategies, implement SEO best practices', 'active', 1, '2025-09-26 17:40:23', '2025-09-26 17:40:23'),
(2, 'Project Management Essentials', 'Master project management methodologies and tools for successful project delivery.', 'Management', 'in_person', 16, 15, 'Basic management experience', 'Learn PM methodologies, use project management tools, manage project timelines and budgets', 'active', 1, '2025-09-26 17:40:23', '2025-09-26 17:40:23'),
(3, 'Data Analysis with Excel', 'Advanced Excel techniques for data analysis, pivot tables, and reporting.', 'Technical', 'virtual', 12, 20, 'Basic Excel knowledge', 'Master advanced Excel functions, create pivot tables, build data visualizations', 'active', 1, '2025-09-26 17:40:23', '2025-09-26 17:40:23'),
(4, 'Leadership Development', 'Develop leadership skills, team management, and communication strategies.', 'Leadership', 'hybrid', 20, 12, 'Management role or 2+ years experience', 'Develop leadership skills, improve team management, enhance communication strategies', 'active', 1, '2025-09-26 17:40:23', '2025-09-26 17:40:23'),
(5, 'Cybersecurity Awareness', 'Learn about cybersecurity threats, best practices, and data protection.', 'Security', 'self_paced', 4, 50, 'None', 'Understand cybersecurity threats, implement security best practices, protect sensitive data', 'active', 1, '2025-09-26 17:40:23', '2025-09-26 17:40:23');

-- --------------------------------------------------------

--
-- Table structure for table `training_enrollments`
--

CREATE TABLE `training_enrollments` (
  `id` int NOT NULL,
  `session_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('enrolled','attended','completed','dropped') DEFAULT 'enrolled',
  `completion_date` date DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `feedback` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `attendance_status` enum('present','absent','late','excused') DEFAULT 'present',
  `completion_status` enum('not_started','in_progress','completed','failed','dropped') DEFAULT 'not_started',
  `completion_score` decimal(5,2) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `training_enrollments`
--

INSERT INTO `training_enrollments` (`id`, `session_id`, `employee_id`, `enrollment_date`, `status`, `completion_date`, `score`, `feedback`, `created_at`, `attendance_status`, `completion_status`, `completion_score`, `updated_at`) VALUES
(1, 2, 4, '2025-09-25', 'enrolled', '2025-09-29', '100.00', 'strongs', '2025-09-25 11:33:26', 'present', 'completed', NULL, '2025-09-28 17:45:54'),
(21, 9, 4, '2025-09-28', 'enrolled', '2025-09-29', '80.00', 'hjgjghjghj', '2025-09-28 17:41:00', 'present', 'in_progress', NULL, '2025-09-28 17:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `training_modules`
--

CREATE TABLE `training_modules` (
  `id` int NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text,
  `category` varchar(100) DEFAULT NULL,
  `duration_hours` int DEFAULT '0',
  `difficulty_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `prerequisites` text,
  `learning_objectives` text,
  `status` enum('draft','active','inactive','archived') DEFAULT 'draft',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `skills_developed` json DEFAULT NULL COMMENT 'Skills that will be developed through this training',
  `certifications_awarded` json DEFAULT NULL COMMENT 'Certifications that can be earned',
  `competency_mapping` json DEFAULT NULL COMMENT 'Mapping to competency models',
  `type` enum('skill_development','certification','compliance','leadership','technical') DEFAULT 'skill_development',
  `max_participants` int DEFAULT '20',
  `cost` decimal(10,2) DEFAULT '0.00',
  `external_provider` varchar(200) DEFAULT NULL,
  `external_link` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `training_modules`
--

INSERT INTO `training_modules` (`id`, `title`, `description`, `category`, `duration_hours`, `difficulty_level`, `prerequisites`, `learning_objectives`, `status`, `created_by`, `created_at`, `updated_at`, `skills_developed`, `certifications_awarded`, `competency_mapping`, `type`, `max_participants`, `cost`, `external_provider`, `external_link`) VALUES
(1, 'E-Commerce Fundamentals', 'Introduction to online business, digital marketing, and customer experience', 'E-Commerce Core', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(2, 'Digital Marketing & SEO', 'Search engine optimization, social media marketing, and digital advertising', 'Marketing', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '42000.00', NULL, NULL),
(3, 'Customer Service Excellence', 'Online customer support, chat systems, and customer retention strategies', 'Customer Service', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '22400.00', NULL, NULL),
(4, 'E-Commerce Analytics', 'Google Analytics, conversion tracking, and performance metrics', 'Analytics', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(5, 'Shopify Development', 'Building and customizing Shopify stores, themes, and apps', 'Technical', 16, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '67200.00', NULL, NULL),
(6, 'WooCommerce Management', 'WordPress e-commerce setup, plugins, and customization', 'Technical', 14, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '56000.00', NULL, NULL),
(7, 'Payment Gateway Integration', 'Stripe, PayPal, and other payment processing systems', 'Technical', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL),
(8, 'E-Commerce Security', 'SSL certificates, PCI compliance, and data protection', 'Security', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(9, 'Inventory Management', 'Stock control, supply chain, and fulfillment processes', 'Operations', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(10, 'E-Commerce Law & Compliance', 'Consumer protection, data privacy, and e-commerce regulations', 'Legal', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '39200.00', NULL, NULL),
(11, 'Conversion Rate Optimization', 'A/B testing, user experience, and sales funnel optimization', 'Optimization', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '50400.00', NULL, NULL),
(12, 'Mobile Commerce', 'Mobile-first design, app development, and mobile payment systems', 'Mobile', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:51:48', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL),
(41, 'Shopify Development', 'Building and customizing Shopify stores, themes, and apps', 'Technical', 16, 'beginner', NULL, NULL, 'inactive', 1, '2025-09-23 06:54:51', '2025-09-28 17:35:47', NULL, NULL, NULL, 'skill_development', 20, '67200.00', NULL, NULL),
(49, 'Test Course for Notifications', 'This is a test course to verify notifications work', 'Technical', 8, 'beginner', 'Basic knowledge', 'Learn test concepts', 'draft', 1, '2025-09-28 08:21:02', '2025-09-28 08:21:02', NULL, NULL, NULL, 'skill_development', 20, '0.00', NULL, NULL),
(50, 'kl', 'kl', 'Leadership', 8, 'beginner', 'k', 'kl', 'draft', 1, '2025-09-28 08:42:25', '2025-09-28 08:42:25', NULL, NULL, NULL, 'certification', 7, '0.00', NULL, NULL),
(51, 'Debug Test Course 2025-09-28 10:43:40', 'This is a test course for debugging', 'Technical', 4, 'beginner', 'Basic knowledge', 'Learn debugging', 'draft', 1, '2025-09-28 08:43:40', '2025-09-28 08:43:40', NULL, NULL, NULL, 'skill_development', 10, '0.00', NULL, NULL),
(52, 'Debug Test Course 2025-09-28 10:44:03', 'This is a test course for debugging', 'Technical', 4, 'beginner', 'Basic knowledge', 'Learn debugging', 'inactive', 1, '2025-09-28 08:44:03', '2025-09-28 16:48:15', NULL, NULL, NULL, 'skill_development', 10, '0.00', NULL, NULL),
(53, 'Test Course - 2025-09-28 10:44:32', 'This is a test course to verify visibility', 'Technical', 2, 'beginner', 'Basic knowledge', 'Learn testing concepts', 'inactive', 1, '2025-09-28 08:44:32', '2025-09-28 16:48:12', NULL, NULL, NULL, 'skill_development', 15, '0.00', NULL, NULL),
(55, 'LearningManager Test - 2025-09-28 10:44:57', 'LearningManager test course', 'Leadership', 2, 'beginner', 'Basic knowledge', 'Learn testing', 'inactive', 1, '2025-09-28 08:44:57', '2025-09-28 16:48:09', NULL, NULL, NULL, 'skill_development', 10, '0.00', NULL, NULL),
(56, 'Test Course - 2025-09-28 10:45:14', 'This is a test course to verify visibility', 'Technical', 2, 'beginner', 'Basic knowledge', 'Learn testing concepts', 'inactive', 1, '2025-09-28 08:45:14', '2025-09-28 16:47:45', NULL, NULL, NULL, 'skill_development', 15, '0.00', NULL, NULL),
(57, 'Lifecycle Test - 2025-09-28 10:45:38', 'Lifecycle test course', 'Technical', 2, 'beginner', 'Basic knowledge', 'Learn testing', 'inactive', 1, '2025-09-28 08:45:38', '2025-09-28 16:47:41', NULL, NULL, NULL, 'skill_development', 10, '0.00', NULL, NULL),
(61, 'dsad', 'gdfg', 'Soft Skills', 5, 'beginner', 'dfg', 'dfg', 'inactive', 1, '2025-09-28 16:48:28', '2025-09-28 16:48:32', NULL, NULL, NULL, 'leadership', 56, '0.00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `training_requests`
--

CREATE TABLE `training_requests` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `module_id` int DEFAULT NULL,
  `request_date` date NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reason` text COMMENT 'Employee reason for requesting training',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `manager_approval` tinyint(1) DEFAULT '0',
  `manager_id` int DEFAULT NULL,
  `manager_comments` text,
  `budget_approved` tinyint(1) DEFAULT '0',
  `estimated_cost` decimal(10,2) DEFAULT '0.00',
  `session_preference` text COMMENT 'Preferred session details'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `training_requests`
--

INSERT INTO `training_requests` (`id`, `employee_id`, `module_id`, `request_date`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `created_at`, `reason`, `priority`, `manager_approval`, `manager_id`, `manager_comments`, `budget_approved`, `estimated_cost`, `session_preference`) VALUES
(1, 4, 41, '2025-09-23', 'approved', 1, '2025-09-25 11:33:26', NULL, '2025-09-23 07:19:56', 'jhnj', 'medium', 0, 1, NULL, 0, '0.00', ''),
(2, 4, 1, '2025-09-28', 'approved', 1, '2025-09-28 17:42:22', NULL, '2025-09-28 17:41:49', 'Recommended training for skill development: E-Commerce Fundamentals', 'medium', 0, NULL, NULL, 0, '0.00', '');

-- --------------------------------------------------------

--
-- Table structure for table `training_sessions`
--

CREATE TABLE `training_sessions` (
  `id` int NOT NULL,
  `module_id` int NOT NULL,
  `session_name` varchar(200) NOT NULL,
  `description` text,
  `trainer_id` int DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `max_participants` int DEFAULT '20',
  `status` enum('scheduled','active','completed','cancelled') DEFAULT 'scheduled',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `training_sessions`
--

INSERT INTO `training_sessions` (`id`, `module_id`, `session_name`, `description`, `trainer_id`, `start_date`, `end_date`, `location`, `max_participants`, `status`, `created_by`, `created_at`) VALUES
(1, 1, 'Test Session Update', NULL, 1, '2025-01-01 09:00:00', '2025-01-01 17:00:00', 'Test Location', 20, 'scheduled', 1, '2025-09-23 06:54:51'),
(2, 1, 'E-Commerce Fundamentals - Q2 2024', NULL, 1, '2024-04-15 00:00:00', '2024-04-16 00:00:00', 'Online', 20, 'active', 1, '2025-09-23 06:54:51'),
(3, 2, 'Digital Marketing & SEO - January 2024', NULL, 1, '2024-01-22 00:00:00', '2024-01-24 00:00:00', 'Online', 15, 'active', 1, '2025-09-23 06:54:51'),
(4, 2, 'Digital Marketing & SEO - March 2024', NULL, 1, '2024-03-18 00:00:00', '2024-03-20 00:00:00', 'Online', 15, 'active', 1, '2025-09-23 06:54:51'),
(5, 5, 'Shopify Development - February 2024', NULL, 1, '2024-02-05 00:00:00', '2024-02-07 00:00:00', 'Online', 12, 'active', 1, '2025-09-23 06:54:51'),
(6, 6, 'WooCommerce Management - February 2024', NULL, 1, '2024-02-12 00:00:00', '2024-02-14 00:00:00', 'Online', 12, 'active', 1, '2025-09-23 06:54:51'),
(7, 3, 'Customer Service Excellence - March 2024', NULL, 1, '2024-03-05 00:00:00', '2024-03-06 00:00:00', 'Online', 25, 'active', 1, '2025-09-23 06:54:51'),
(8, 4, 'E-Commerce Analytics - April 2024', NULL, 1, '2024-04-08 00:00:00', '2024-04-10 00:00:00', 'Online', 18, 'active', 1, '2025-09-23 06:54:51'),
(9, 8, 'tahdsjabsd', NULL, 5, '2025-09-29 16:48:00', '2025-09-29 22:48:00', 'conference room', 20, 'scheduled', 1, '2025-09-28 16:49:24');

-- --------------------------------------------------------

--
-- Table structure for table `training_waitlist`
--

CREATE TABLE `training_waitlist` (
  `id` int NOT NULL,
  `module_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `request_id` int DEFAULT NULL,
  `added_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','enrolled','cancelled') DEFAULT 'active',
  `priority_score` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `training_waitlist`
--

INSERT INTO `training_waitlist` (`id`, `module_id`, `employee_id`, `request_id`, `added_date`, `status`, `priority_score`) VALUES
(1, 1, 4, 2, '2025-09-28 17:42:22', 'active', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','hr_manager','employee') DEFAULT 'employee',
  `status` enum('active','inactive','terminated') DEFAULT 'active',
  `employee_id` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int DEFAULT '0',
  `locked_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `status`, `employee_id`, `department`, `position`, `phone`, `hire_date`, `created_at`, `updated_at`, `last_login`, `login_attempts`, `locked_until`) VALUES
(1, 'admin', 'system.admin@company.com', '$2y$10$Sm5g6goyhIbSP1JcWvdN7.UQMHslYgFSEouldZtCoY6NJfOua.azK', 'System', 'Administrator', 'admin', 'active', 'ADM001', 'IT', 'System Administrator', '555-0000', '2022-01-01', '2025-09-12 04:29:44', '2025-09-23 06:46:41', '2025-09-12 08:58:46', 0, NULL),
(2, 'admin2', 'admin2@hr1.com', '$2y$10$Sm5g6goyhIbSP1JcWvdN7.UQMHslYgFSEouldZtCoY6NJfOua.azK', 'Sarah', 'Johnson', 'admin', 'terminated', 'ADM002', 'Human Resources', 'HR Director', '555-0002', '2019-03-10', '2025-09-12 04:29:44', '2025-09-25 12:17:58', NULL, 0, NULL),
(4, 'john.doe', 'john.doe@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'employee', 'active', 'EMP001', 'E-Commerce Operations', 'E-Commerce Developer', '555-0101', '2023-01-15', '2025-09-23 06:46:41', '2025-09-23 06:51:48', NULL, 0, NULL),
(5, 'jane.smith', 'jane.smith@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'hr_manager', 'active', 'HRM001', 'Human Resources', 'HR Manager - E-Commerce', '555-0102', '2022-06-01', '2025-09-23 06:46:41', '2025-09-23 06:51:48', NULL, 0, NULL),
(6, 'admin.test', 'admin.test@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Test', 'admin', 'terminated', 'ADM002', 'IT & E-Commerce', 'E-Commerce System Administrator', '555-0103', '2022-01-01', '2025-09-23 06:46:41', '2025-09-25 12:18:19', NULL, 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_analysis_context`
--
ALTER TABLE `ai_analysis_context`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_context_evaluation` (`evaluation_id`);

--
-- Indexes for table `ai_analysis_insights`
--
ALTER TABLE `ai_analysis_insights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competency_id` (`competency_id`),
  ADD KEY `idx_ai_insights_evaluation` (`evaluation_id`),
  ADD KEY `idx_ai_insights_type` (`insight_type`);

--
-- Indexes for table `ai_analysis_log`
--
ALTER TABLE `ai_analysis_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_analysis_type` (`analysis_type`),
  ADD KEY `idx_ai_analysis_created` (`created_at`);

--
-- Indexes for table `ai_analysis_patterns`
--
ALTER TABLE `ai_analysis_patterns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_patterns_type` (`pattern_type`);

--
-- Indexes for table `ai_analysis_results`
--
ALTER TABLE `ai_analysis_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evaluation_analysis` (`evaluation_id`,`analysis_type`),
  ADD KEY `idx_ai_analysis_evaluation` (`evaluation_id`),
  ADD KEY `idx_ai_analysis_type` (`analysis_type`),
  ADD KEY `idx_ai_analysis_sentiment` (`sentiment`),
  ADD KEY `idx_ai_analysis_created` (`created_at`);

--
-- Indexes for table `ai_model_performance`
--
ALTER TABLE `ai_model_performance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_model_type` (`model_name`,`analysis_type`);

--
-- Indexes for table `ai_recommendation_log`
--
ALTER TABLE `ai_recommendation_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_recommendation_employee` (`employee_id`),
  ADD KEY `idx_ai_recommendation_type` (`recommendation_type`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_announcements_status` (`status`),
  ADD KEY `idx_announcements_priority` (`priority`);

--
-- Indexes for table `certifications_catalog`
--
ALTER TABLE `certifications_catalog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `competencies`
--
ALTER TABLE `competencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `model_id` (`model_id`);

--
-- Indexes for table `competency_models`
--
ALTER TABLE `competency_models`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_competency_models_status` (`status`),
  ADD KEY `idx_competency_models_category` (`category`);

--
-- Indexes for table `competency_notifications`
--
ALTER TABLE `competency_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `competency_reports`
--
ALTER TABLE `competency_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `competency_scores`
--
ALTER TABLE `competency_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evaluation_id` (`evaluation_id`),
  ADD KEY `competency_id` (`competency_id`);

--
-- Indexes for table `critical_positions`
--
ALTER TABLE `critical_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `current_incumbent_id` (`current_incumbent_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_critical_positions_department` (`department`),
  ADD KEY `idx_critical_positions_priority` (`priority_level`);

--
-- Indexes for table `employee_certifications`
--
ALTER TABLE `employee_certifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_certification` (`employee_id`,`certification_id`),
  ADD KEY `certification_id` (`certification_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `employee_learning_paths`
--
ALTER TABLE `employee_learning_paths`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_path` (`employee_id`,`path_id`),
  ADD KEY `path_id` (`path_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `employee_requests`
--
ALTER TABLE `employee_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_skill` (`employee_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cycle_id` (`cycle_id`),
  ADD KEY `model_id` (`model_id`),
  ADD KEY `idx_evaluations_status` (`status`),
  ADD KEY `idx_evaluations_employee` (`employee_id`),
  ADD KEY `idx_evaluations_evaluator` (`evaluator_id`);

--
-- Indexes for table `evaluation_cycles`
--
ALTER TABLE `evaluation_cycles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_evaluation_competency` (`evaluation_id`,`competency_id`),
  ADD KEY `competency_id` (`competency_id`);

--
-- Indexes for table `learning_paths`
--
ALTER TABLE `learning_paths`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `learning_path_modules`
--
ALTER TABLE `learning_path_modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_path_module` (`path_id`,`module_id`),
  ADD KEY `module_id` (`module_id`);

--
-- Indexes for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_preferences` (`user_id`);

--
-- Indexes for table `notification_templates`
--
ALTER TABLE `notification_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_notification_type` (`notification_type`);

--
-- Indexes for table `skills_catalog`
--
ALTER TABLE `skills_catalog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competency_model_id` (`competency_model_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `succession_candidates`
--
ALTER TABLE `succession_candidates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_employee` (`role_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_system_logs_action` (`action`),
  ADD KEY `idx_system_logs_created_at` (`created_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `terms_acceptance`
--
ALTER TABLE `terms_acceptance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_accepted` (`accepted`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `training_catalog`
--
ALTER TABLE `training_catalog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `training_enrollments`
--
ALTER TABLE `training_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`session_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `training_modules`
--
ALTER TABLE `training_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_training_modules_status` (`status`),
  ADD KEY `idx_training_modules_category` (`category`);

--
-- Indexes for table `training_requests`
--
ALTER TABLE `training_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `trainer_id` (`trainer_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `training_waitlist`
--
ALTER TABLE `training_waitlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_module_employee` (`module_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_status` (`status`),
  ADD KEY `idx_users_department` (`department`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_analysis_context`
--
ALTER TABLE `ai_analysis_context`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ai_analysis_insights`
--
ALTER TABLE `ai_analysis_insights`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_analysis_log`
--
ALTER TABLE `ai_analysis_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_analysis_patterns`
--
ALTER TABLE `ai_analysis_patterns`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_analysis_results`
--
ALTER TABLE `ai_analysis_results`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `ai_model_performance`
--
ALTER TABLE `ai_model_performance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_recommendation_log`
--
ALTER TABLE `ai_recommendation_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `certifications_catalog`
--
ALTER TABLE `certifications_catalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `competencies`
--
ALTER TABLE `competencies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `competency_models`
--
ALTER TABLE `competency_models`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `competency_notifications`
--
ALTER TABLE `competency_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `competency_reports`
--
ALTER TABLE `competency_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `competency_scores`
--
ALTER TABLE `competency_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `critical_positions`
--
ALTER TABLE `critical_positions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_certifications`
--
ALTER TABLE `employee_certifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_learning_paths`
--
ALTER TABLE `employee_learning_paths`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_requests`
--
ALTER TABLE `employee_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_skills`
--
ALTER TABLE `employee_skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `evaluation_cycles`
--
ALTER TABLE `evaluation_cycles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `learning_paths`
--
ALTER TABLE `learning_paths`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `learning_path_modules`
--
ALTER TABLE `learning_path_modules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notification_templates`
--
ALTER TABLE `notification_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `skills_catalog`
--
ALTER TABLE `skills_catalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `succession_candidates`
--
ALTER TABLE `succession_candidates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `terms_acceptance`
--
ALTER TABLE `terms_acceptance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `training_catalog`
--
ALTER TABLE `training_catalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `training_enrollments`
--
ALTER TABLE `training_enrollments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `training_modules`
--
ALTER TABLE `training_modules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `training_requests`
--
ALTER TABLE `training_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `training_waitlist`
--
ALTER TABLE `training_waitlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_analysis_context`
--
ALTER TABLE `ai_analysis_context`
  ADD CONSTRAINT `ai_analysis_context_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_analysis_insights`
--
ALTER TABLE `ai_analysis_insights`
  ADD CONSTRAINT `ai_analysis_insights_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_analysis_insights_ibfk_2` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_analysis_results`
--
ALTER TABLE `ai_analysis_results`
  ADD CONSTRAINT `ai_analysis_results_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ai_recommendation_log`
--
ALTER TABLE `ai_recommendation_log`
  ADD CONSTRAINT `ai_recommendation_log_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `certifications_catalog`
--
ALTER TABLE `certifications_catalog`
  ADD CONSTRAINT `certifications_catalog_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `competencies`
--
ALTER TABLE `competencies`
  ADD CONSTRAINT `competencies_ibfk_1` FOREIGN KEY (`model_id`) REFERENCES `competency_models` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competency_models`
--
ALTER TABLE `competency_models`
  ADD CONSTRAINT `competency_models_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `competency_notifications`
--
ALTER TABLE `competency_notifications`
  ADD CONSTRAINT `competency_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competency_reports`
--
ALTER TABLE `competency_reports`
  ADD CONSTRAINT `competency_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `competency_scores`
--
ALTER TABLE `competency_scores`
  ADD CONSTRAINT `competency_scores_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `competency_scores_ibfk_2` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`);

--
-- Constraints for table `critical_positions`
--
ALTER TABLE `critical_positions`
  ADD CONSTRAINT `critical_positions_ibfk_1` FOREIGN KEY (`current_incumbent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `critical_positions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_certifications`
--
ALTER TABLE `employee_certifications`
  ADD CONSTRAINT `employee_certifications_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employee_certifications_ibfk_2` FOREIGN KEY (`certification_id`) REFERENCES `certifications_catalog` (`id`),
  ADD CONSTRAINT `employee_certifications_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `employee_learning_paths`
--
ALTER TABLE `employee_learning_paths`
  ADD CONSTRAINT `employee_learning_paths_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employee_learning_paths_ibfk_2` FOREIGN KEY (`path_id`) REFERENCES `learning_paths` (`id`),
  ADD CONSTRAINT `employee_learning_paths_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `employee_requests`
--
ALTER TABLE `employee_requests`
  ADD CONSTRAINT `employee_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employee_requests_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `employee_skills`
--
ALTER TABLE `employee_skills`
  ADD CONSTRAINT `employee_skills_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `employee_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills_catalog` (`id`),
  ADD CONSTRAINT `employee_skills_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD CONSTRAINT `evaluations_ibfk_1` FOREIGN KEY (`cycle_id`) REFERENCES `evaluation_cycles` (`id`),
  ADD CONSTRAINT `evaluations_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `evaluations_ibfk_3` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `evaluations_ibfk_4` FOREIGN KEY (`model_id`) REFERENCES `competency_models` (`id`);

--
-- Constraints for table `evaluation_cycles`
--
ALTER TABLE `evaluation_cycles`
  ADD CONSTRAINT `evaluation_cycles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `evaluation_scores`
--
ALTER TABLE `evaluation_scores`
  ADD CONSTRAINT `evaluation_scores_ibfk_1` FOREIGN KEY (`evaluation_id`) REFERENCES `evaluations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evaluation_scores_ibfk_2` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `learning_paths`
--
ALTER TABLE `learning_paths`
  ADD CONSTRAINT `learning_paths_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `learning_path_modules`
--
ALTER TABLE `learning_path_modules`
  ADD CONSTRAINT `learning_path_modules_ibfk_1` FOREIGN KEY (`path_id`) REFERENCES `learning_paths` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `learning_path_modules_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `training_modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notification_preferences`
--
ALTER TABLE `notification_preferences`
  ADD CONSTRAINT `notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `skills_catalog`
--
ALTER TABLE `skills_catalog`
  ADD CONSTRAINT `skills_catalog_ibfk_1` FOREIGN KEY (`competency_model_id`) REFERENCES `competency_models` (`id`),
  ADD CONSTRAINT `skills_catalog_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `succession_candidates`
--
ALTER TABLE `succession_candidates`
  ADD CONSTRAINT `succession_candidates_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `critical_positions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `succession_candidates_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `succession_candidates_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_catalog`
--
ALTER TABLE `training_catalog`
  ADD CONSTRAINT `training_catalog_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `training_enrollments`
--
ALTER TABLE `training_enrollments`
  ADD CONSTRAINT `training_enrollments_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`id`),
  ADD CONSTRAINT `training_enrollments_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_modules`
--
ALTER TABLE `training_modules`
  ADD CONSTRAINT `training_modules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_requests`
--
ALTER TABLE `training_requests`
  ADD CONSTRAINT `training_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `training_requests_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `training_modules` (`id`),
  ADD CONSTRAINT `training_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `training_requests_ibfk_4` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `training_modules` (`id`),
  ADD CONSTRAINT `training_sessions_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `training_sessions_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `training_waitlist`
--
ALTER TABLE `training_waitlist`
  ADD CONSTRAINT `training_waitlist_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `training_modules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_waitlist_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_waitlist_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `training_requests` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
