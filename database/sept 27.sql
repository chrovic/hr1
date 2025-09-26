-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 26, 2025 at 06:17 PM
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
(39, 11, 'competency_feedback', 'negative', '0.97', 'Satisfactory performance in Technical. You\'re meeting the basic requirements with room for growth. Utter failure in meet the standard work. This is the worst possible performance and requires urgent intervention.', 557, 212, '0.38', '{\"analysis_timestamp\": \"2025-09-26 18:09:01\", \"competency_analysis\": [{\"score\": \"3.00\", \"weight\": \"1.00\", \"comments\": \"Satisfactory performance in Technical. You\'re meeting the basic requirements with room for growth.\", \"sentiment\": \"positive\", \"confidence\": 0.6036842142045498, \"competency_name\": \"Technical\", \"contextual_comments\": \"Competency: Technical (Score: 3.00/5). Comment: Satisfactory performance in Technical. You\'re meeting the basic requirements with room for growth.\"}, {\"score\": \"1.00\", \"weight\": \"1.00\", \"comments\": \"Utter failure in meet the standard work. This is the worst possible performance and requires urgent intervention.\", \"sentiment\": \"negative\", \"confidence\": 0.7406966973841189, \"competency_name\": \"meet the standard work\", \"contextual_comments\": \"Competency: meet the standard work (Score: 1.00/5). Comment: Utter failure in meet the standard work. This is the worst possible performance and requires urgent intervention.\"}, {\"score\": \"2.00\", \"weight\": \"1.00\", \"comments\": \"Utter failure in Communication. This is the worst possible performance and requires urgent intervention.\", \"sentiment\": \"negative\", \"confidence\": 0.7413067977130412, \"competency_name\": \"Communication\", \"contextual_comments\": \"Competency: Communication (Score: 2.00/5). Comment: Utter failure in Communication. This is the worst possible performance and requires urgent intervention.\"}]}', '2025-09-26 18:09:01', '2025-09-26 18:09:01'),
(40, 10, 'competency_feedback', 'neutral', '0.85', 'You\'re meeting the basic requirements with room for growth. You\'re getting by but should aim to exceed expectations. You\'ve completely failed to meet any standards and this is a crisis.', 773, 185, '0.24', '{\"analysis_timestamp\": \"2025-09-26 18:10:03\", \"competency_analysis\": [{\"score\": \"3.00\", \"weight\": \"0.25\", \"comments\": \"Satisfactory performance in Communication Skills. You\'re meeting the basic requirements with room for growth.\", \"sentiment\": \"positive\", \"confidence\": 0.5954674185812472, \"competency_name\": \"Communication Skills\", \"contextual_comments\": \"Competency: Communication Skills (Score: 3.00/5). Comment: Satisfactory performance in Communication Skills. You\'re meeting the basic requirements with room for growth.\"}, {\"score\": \"2.00\", \"weight\": \"0.20\", \"comments\": \"Utter failure in Product Knowledge. This is the worst possible performance and requires urgent intervention.\", \"sentiment\": \"negative\", \"confidence\": 0.7408262678980827, \"competency_name\": \"Product Knowledge\", \"contextual_comments\": \"Competency: Product Knowledge (Score: 2.00/5). Comment: Utter failure in Product Knowledge. This is the worst possible performance and requires urgent intervention.\"}, {\"score\": \"2.00\", \"weight\": \"0.25\", \"comments\": \"Utter failure in Customer Service. This is the worst possible performance and requires urgent intervention.\", \"sentiment\": \"negative\", \"confidence\": 0.7413219057023525, \"competency_name\": \"Customer Service\", \"contextual_comments\": \"Competency: Customer Service (Score: 2.00/5). Comment: Utter failure in Customer Service. This is the worst possible performance and requires urgent intervention.\"}, {\"score\": \"3.00\", \"weight\": \"0.20\", \"comments\": \"Standard performance in Sales Performance. You\'re getting by but should aim to exceed expectations.\", \"sentiment\": \"positive\", \"confidence\": 0.5085478046536445, \"competency_name\": \"Sales Performance\", \"contextual_comments\": \"Competency: Sales Performance (Score: 3.00/5). Comment: Standard performance in Sales Performance. You\'re getting by but should aim to exceed expectations.\"}, {\"score\": \"2.00\", \"weight\": \"0.10\", \"comments\": \"Total failure in Team Collaboration. You\'ve completely failed to meet any standards and this is a crisis.\", \"sentiment\": \"negative\", \"confidence\": 0.7678968606889247, \"competency_name\": \"Team Collaboration\", \"contextual_comments\": \"Competency: Team Collaboration (Score: 2.00/5). Comment: Total failure in Team Collaboration. You\'ve completely failed to meet any standards and this is a crisis.\"}]}', '2025-09-26 18:10:03', '2025-09-26 18:10:03'),
(41, 12, 'competency_feedback', 'positive', '0.87', 'Very clever hardworker employee have a better communication skill. very clever hardworking employee need to be more open and honest with each other.', 311, 148, '0.48', '{\"analysis_timestamp\": \"2025-09-26 18:13:41\", \"competency_analysis\": [{\"score\": \"4.00\", \"weight\": \"1.00\", \"comments\": \"very clever\", \"sentiment\": \"positive\", \"confidence\": 0.5853242656588554, \"competency_name\": \"Technical\", \"contextual_comments\": \"Competency: Technical (Score: 4.00/5). Comment: very clever\"}, {\"score\": \"4.00\", \"weight\": \"1.00\", \"comments\": \"hardworker employee\", \"sentiment\": \"neutral\", \"confidence\": 0.6338352638483047, \"competency_name\": \"meet the standard work\", \"contextual_comments\": \"Competency: meet the standard work (Score: 4.00/5). Comment: hardworker employee\"}, {\"score\": \"3.00\", \"weight\": \"1.00\", \"comments\": \"have a better communication skill\", \"sentiment\": \"neutral\", \"confidence\": 0.6505364137887953, \"competency_name\": \"Communication\", \"contextual_comments\": \"Competency: Communication (Score: 3.00/5). Comment: have a better communication skill\"}]}', '2025-09-26 18:13:41', '2025-09-26 18:13:41');

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
(94, 21, 'Communication', 'how it interacts', '1.00', 5, '2025-09-26 17:45:00', '2025-09-26 17:45:00');

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
(21, 'Entry Level E-Commerce', 'Basic competencies for new e-commerce employees', 'Communication', '[\"employee\", \" junior_staff\"]', 'manager_review', 'active', 1, '2025-09-26 17:43:01', '2025-09-26 17:43:01');

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
(91, 10, 87, '3.00', '', '2025-09-26 17:41:56'),
(92, 10, 88, '2.00', '', '2025-09-26 17:41:56'),
(93, 10, 89, '2.00', '', '2025-09-26 17:41:56'),
(94, 10, 90, '3.00', '', '2025-09-26 17:41:56'),
(95, 10, 91, '2.00', '', '2025-09-26 17:41:56'),
(96, 11, 92, '3.00', '', '2025-09-26 17:46:43'),
(97, 11, 93, '1.00', '', '2025-09-26 17:46:43'),
(98, 11, 94, '2.00', '', '2025-09-26 17:46:43'),
(99, 12, 92, '4.00', 'very clever', '2025-09-26 18:11:53'),
(100, 12, 93, '4.00', 'hardworker employee', '2025-09-26 18:11:53'),
(101, 12, 94, '3.00', 'have a better communication skill', '2025-09-26 18:11:53');

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
(10, 5, 4, 1, 20, 'completed', '2.45', '2025-09-26 17:09:28', '2025-09-26 17:41:56'),
(11, 5, 4, 1, 21, 'completed', '2.00', '2025-09-26 17:45:17', '2025-09-26 17:46:43'),
(12, 5, 4, 5, 21, 'completed', '3.67', '2025-09-26 18:10:48', '2025-09-26 18:11:53');

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
(5, 'Entry Level E-Commerce', 'annual', '2025-09-26', '2025-09-27', 'draft', 1, '2025-09-26 17:09:12', '2025-09-26 17:09:12');

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
(2, 5, 1, '2025-09-26 15:45:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 15:45:58', '2025-09-26 15:45:58');

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
(1, 2, 4, '2025-09-25', 'enrolled', '2025-09-25', '75.00', 'strongs', '2025-09-25 11:33:26', 'present', 'completed', NULL, '2025-09-25 14:14:27');

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
(13, 'E-Commerce Fundamentals', 'Introduction to online business, digital marketing, and customer experience', 'E-Commerce Core', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(14, 'Digital Marketing & SEO', 'Search engine optimization, social media marketing, and digital advertising', 'Marketing', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '42000.00', NULL, NULL),
(15, 'Customer Service Excellence', 'Online customer support, chat systems, and customer retention strategies', 'Customer Service', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '22400.00', NULL, NULL),
(16, 'E-Commerce Analytics', 'Google Analytics, conversion tracking, and performance metrics', 'Analytics', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(17, 'Shopify Development', 'Building and customizing Shopify stores, themes, and apps', 'Technical', 16, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '67200.00', NULL, NULL),
(18, 'WooCommerce Management', 'WordPress e-commerce setup, plugins, and customization', 'Technical', 14, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '56000.00', NULL, NULL),
(19, 'Payment Gateway Integration', 'Stripe, PayPal, and other payment processing systems', 'Technical', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL),
(20, 'E-Commerce Security', 'SSL certificates, PCI compliance, and data protection', 'Security', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(21, 'Inventory Management', 'Stock control, supply chain, and fulfillment processes', 'Operations', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(22, 'E-Commerce Law & Compliance', 'Consumer protection, data privacy, and e-commerce regulations', 'Legal', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '39200.00', NULL, NULL),
(23, 'Conversion Rate Optimization', 'A/B testing, user experience, and sales funnel optimization', 'Optimization', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '50400.00', NULL, NULL),
(24, 'Mobile Commerce', 'Mobile-first design, app development, and mobile payment systems', 'Mobile', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:52:41', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL),
(25, 'E-Commerce Fundamentals', 'Introduction to online business, digital marketing, and customer experience', 'E-Commerce Core', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(26, 'Digital Marketing & SEO', 'Search engine optimization, social media marketing, and digital advertising', 'Marketing', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '42000.00', NULL, NULL),
(27, 'Customer Service Excellence', 'Online customer support, chat systems, and customer retention strategies', 'Customer Service', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '22400.00', NULL, NULL),
(28, 'E-Commerce Analytics', 'Google Analytics, conversion tracking, and performance metrics', 'Analytics', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(29, 'Shopify Development', 'Building and customizing Shopify stores, themes, and apps', 'Technical', 16, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '67200.00', NULL, NULL),
(30, 'WooCommerce Management', 'WordPress e-commerce setup, plugins, and customization', 'Technical', 14, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '56000.00', NULL, NULL),
(31, 'Payment Gateway Integration', 'Stripe, PayPal, and other payment processing systems', 'Technical', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL),
(32, 'E-Commerce Security', 'SSL certificates, PCI compliance, and data protection', 'Security', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(33, 'Inventory Management', 'Stock control, supply chain, and fulfillment processes', 'Operations', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(34, 'E-Commerce Law & Compliance', 'Consumer protection, data privacy, and e-commerce regulations', 'Legal', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '39200.00', NULL, NULL),
(35, 'Conversion Rate Optimization', 'A/B testing, user experience, and sales funnel optimization', 'Optimization', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '50400.00', NULL, NULL),
(36, 'Mobile Commerce', 'Mobile-first design, app development, and mobile payment systems', 'Mobile', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:53:40', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL),
(37, 'E-Commerce Fundamentals', 'Introduction to online business, digital marketing, and customer experience', 'E-Commerce Core', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(38, 'Digital Marketing & SEO', 'Search engine optimization, social media marketing, and digital advertising', 'Marketing', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '42000.00', NULL, NULL),
(39, 'Customer Service Excellence', 'Online customer support, chat systems, and customer retention strategies', 'Customer Service', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '22400.00', NULL, NULL),
(40, 'E-Commerce Analytics', 'Google Analytics, conversion tracking, and performance metrics', 'Analytics', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(41, 'Shopify Development', 'Building and customizing Shopify stores, themes, and apps', 'Technical', 16, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '67200.00', NULL, NULL),
(42, 'WooCommerce Management', 'WordPress e-commerce setup, plugins, and customization', 'Technical', 14, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '56000.00', NULL, NULL),
(43, 'Payment Gateway Integration', 'Stripe, PayPal, and other payment processing systems', 'Technical', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL),
(44, 'E-Commerce Security', 'SSL certificates, PCI compliance, and data protection', 'Security', 6, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '28000.00', NULL, NULL),
(45, 'Inventory Management', 'Stock control, supply chain, and fulfillment processes', 'Operations', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '33600.00', NULL, NULL),
(46, 'E-Commerce Law & Compliance', 'Consumer protection, data privacy, and e-commerce regulations', 'Legal', 8, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '39200.00', NULL, NULL),
(47, 'Conversion Rate Optimization', 'A/B testing, user experience, and sales funnel optimization', 'Optimization', 12, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '50400.00', NULL, NULL),
(48, 'Mobile Commerce', 'Mobile-first design, app development, and mobile payment systems', 'Mobile', 10, 'beginner', NULL, NULL, 'active', 1, '2025-09-23 06:54:51', '2025-09-23 07:14:22', NULL, NULL, NULL, 'skill_development', 20, '44800.00', NULL, NULL);

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
(1, 4, 41, '2025-09-23', 'approved', 1, '2025-09-25 11:33:26', NULL, '2025-09-23 07:19:56', 'jhnj', 'medium', 0, 1, NULL, 0, '0.00', '');

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
(1, 1, 'E-Commerce Fundamentals - Q1 2024', NULL, 1, '2024-01-15 00:00:00', '2024-01-16 00:00:00', 'Online', 20, 'active', 1, '2025-09-23 06:54:51'),
(2, 1, 'E-Commerce Fundamentals - Q2 2024', NULL, 1, '2024-04-15 00:00:00', '2024-04-16 00:00:00', 'Online', 20, 'active', 1, '2025-09-23 06:54:51'),
(3, 2, 'Digital Marketing & SEO - January 2024', NULL, 1, '2024-01-22 00:00:00', '2024-01-24 00:00:00', 'Online', 15, 'active', 1, '2025-09-23 06:54:51'),
(4, 2, 'Digital Marketing & SEO - March 2024', NULL, 1, '2024-03-18 00:00:00', '2024-03-20 00:00:00', 'Online', 15, 'active', 1, '2025-09-23 06:54:51'),
(5, 5, 'Shopify Development - February 2024', NULL, 1, '2024-02-05 00:00:00', '2024-02-07 00:00:00', 'Online', 12, 'active', 1, '2025-09-23 06:54:51'),
(6, 6, 'WooCommerce Management - February 2024', NULL, 1, '2024-02-12 00:00:00', '2024-02-14 00:00:00', 'Online', 12, 'active', 1, '2025-09-23 06:54:51'),
(7, 3, 'Customer Service Excellence - March 2024', NULL, 1, '2024-03-05 00:00:00', '2024-03-06 00:00:00', 'Online', 25, 'active', 1, '2025-09-23 06:54:51'),
(8, 4, 'E-Commerce Analytics - April 2024', NULL, 1, '2024-04-08 00:00:00', '2024-04-10 00:00:00', 'Online', 18, 'active', 1, '2025-09-23 06:54:51');

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `competency_models`
--
ALTER TABLE `competency_models`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `competency_reports`
--
ALTER TABLE `competency_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `competency_scores`
--
ALTER TABLE `competency_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `evaluation_cycles`
--
ALTER TABLE `evaluation_cycles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `training_catalog`
--
ALTER TABLE `training_catalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `training_enrollments`
--
ALTER TABLE `training_enrollments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `training_modules`
--
ALTER TABLE `training_modules`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `training_requests`
--
ALTER TABLE `training_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `training_waitlist`
--
ALTER TABLE `training_waitlist`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
