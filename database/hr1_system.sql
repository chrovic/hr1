-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 25, 2025 at 02:30 PM
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
(1, 1, 'Online Business Understanding', 'Understanding of e-commerce business models, online marketplaces, and digital commerce fundamentals', '1.00', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(2, 1, 'Digital Customer Service', 'Ability to provide excellent customer service through digital channels including chat, email, and social media', '1.20', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(3, 1, 'Platform Navigation', 'Proficiency in navigating and using e-commerce platforms and tools', '0.80', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(4, 1, 'Online Communication', 'Effective communication skills for digital environments and remote collaboration', '1.00', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(5, 1, 'Digital Literacy', 'Basic digital skills including file management, online tools, and digital security awareness', '0.90', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(6, 2, 'SEO & Content Marketing', 'Search engine optimization and content creation skills for e-commerce', '1.30', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(7, 2, 'Social Media Marketing', 'Social media strategy, content creation, and community management', '1.20', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(8, 2, 'Email Marketing', 'Email campaign design, automation, and performance optimization', '1.10', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(9, 2, 'Conversion Rate Optimization', 'A/B testing, user experience optimization, and conversion improvement', '1.40', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(10, 2, 'Digital Advertising', 'Paid advertising across platforms including Google Ads, Facebook, and other channels', '1.25', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(11, 2, 'Analytics & Reporting', 'Data analysis, performance tracking, and marketing ROI measurement', '1.15', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(12, 3, 'Shopify Development', 'Shopify platform customization, theme development, and app integration', '1.50', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(13, 3, 'WooCommerce Management', 'WordPress e-commerce setup, plugin management, and customization', '1.40', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(14, 3, 'Payment Gateway Integration', 'Payment processing systems, security implementation, and transaction management', '1.30', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(15, 3, 'Mobile Commerce Development', 'Mobile-first design, responsive development, and mobile app integration', '1.35', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(16, 3, 'E-Commerce Security', 'SSL implementation, PCI compliance, and security best practices', '1.25', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(17, 3, 'Performance Optimization', 'Site speed optimization, caching, and technical performance improvement', '1.20', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(18, 4, 'Online Customer Support', 'Multi-channel customer support including chat, email, and phone', '1.30', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(19, 4, 'Customer Journey Mapping', 'Understanding and optimizing customer touchpoints and experiences', '1.20', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(20, 4, 'Retention Strategies', 'Customer loyalty programs, retention campaigns, and relationship building', '1.25', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(21, 4, 'Feedback Management', 'Collecting, analyzing, and acting on customer feedback', '1.15', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(22, 4, 'Crisis Communication', 'Handling customer complaints, negative reviews, and crisis situations', '1.10', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(23, 5, 'Google Analytics', 'Advanced Google Analytics setup, tracking, and analysis', '1.40', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(24, 5, 'E-Commerce Metrics', 'Key performance indicators, conversion tracking, and business metrics', '1.35', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(25, 5, 'Data Visualization', 'Creating dashboards, reports, and visual representations of data', '1.20', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(26, 5, 'Business Intelligence', 'Data-driven decision making and strategic analysis', '1.30', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(27, 5, 'A/B Testing', 'Experimental design, statistical analysis, and optimization testing', '1.25', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(28, 6, 'Inventory Management', 'Stock control, demand forecasting, and inventory optimization', '1.30', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(29, 6, 'Supply Chain Management', 'Vendor relationships, procurement, and supply chain optimization', '1.25', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(30, 6, 'Fulfillment Operations', 'Order processing, shipping, and delivery management', '1.20', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(31, 6, 'Process Optimization', 'Workflow improvement, automation, and operational efficiency', '1.15', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(32, 6, 'Quality Control', 'Product quality assurance and customer satisfaction monitoring', '1.10', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(33, 7, 'PCI Compliance', 'Payment card industry compliance and security standards', '1.50', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(34, 7, 'Data Protection', 'GDPR, privacy laws, and data security implementation', '1.40', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(35, 7, 'E-Commerce Law', 'Online business regulations, consumer protection, and legal compliance', '1.35', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(36, 7, 'Security Monitoring', 'Threat detection, incident response, and security monitoring', '1.30', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(37, 7, 'Risk Assessment', 'Security risk evaluation and mitigation strategies', '1.25', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(38, 8, 'Mobile App Development', 'Native and hybrid mobile app development for e-commerce', '1.50', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(39, 8, 'Responsive Design', 'Mobile-first design principles and responsive web development', '1.40', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(40, 8, 'Mobile Payments', 'Mobile payment systems, digital wallets, and mobile commerce', '1.35', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(41, 8, 'Mobile Analytics', 'Mobile-specific tracking, analytics, and performance measurement', '1.30', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(42, 8, 'Mobile UX/UI', 'Mobile user experience design and interface optimization', '1.25', 5, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(43, 1, 'Online Business Understanding', 'Understanding of e-commerce business models, online marketplaces, and digital commerce fundamentals', '1.00', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(44, 1, 'Digital Customer Service', 'Ability to provide excellent customer service through digital channels including chat, email, and social media', '1.20', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(45, 1, 'Platform Navigation', 'Proficiency in navigating and using e-commerce platforms and tools', '0.80', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(46, 1, 'Online Communication', 'Effective communication skills for digital environments and remote collaboration', '1.00', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(47, 1, 'Digital Literacy', 'Basic digital skills including file management, online tools, and digital security awareness', '0.90', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(48, 2, 'SEO & Content Marketing', 'Search engine optimization and content creation skills for e-commerce', '1.30', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(49, 2, 'Social Media Marketing', 'Social media strategy, content creation, and community management', '1.20', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(50, 2, 'Email Marketing', 'Email campaign design, automation, and performance optimization', '1.10', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(51, 2, 'Conversion Rate Optimization', 'A/B testing, user experience optimization, and conversion improvement', '1.40', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(52, 2, 'Digital Advertising', 'Paid advertising across platforms including Google Ads, Facebook, and other channels', '1.25', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(53, 2, 'Analytics & Reporting', 'Data analysis, performance tracking, and marketing ROI measurement', '1.15', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(54, 3, 'Shopify Development', 'Shopify platform customization, theme development, and app integration', '1.50', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(55, 3, 'WooCommerce Management', 'WordPress e-commerce setup, plugin management, and customization', '1.40', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(56, 3, 'Payment Gateway Integration', 'Payment processing systems, security implementation, and transaction management', '1.30', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(57, 3, 'Mobile Commerce Development', 'Mobile-first design, responsive development, and mobile app integration', '1.35', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(58, 3, 'E-Commerce Security', 'SSL implementation, PCI compliance, and security best practices', '1.25', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(59, 3, 'Performance Optimization', 'Site speed optimization, caching, and technical performance improvement', '1.20', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(60, 4, 'Online Customer Support', 'Multi-channel customer support including chat, email, and phone', '1.30', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(61, 4, 'Customer Journey Mapping', 'Understanding and optimizing customer touchpoints and experiences', '1.20', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(62, 4, 'Retention Strategies', 'Customer loyalty programs, retention campaigns, and relationship building', '1.25', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(63, 4, 'Feedback Management', 'Collecting, analyzing, and acting on customer feedback', '1.15', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(64, 4, 'Crisis Communication', 'Handling customer complaints, negative reviews, and crisis situations', '1.10', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(65, 5, 'Google Analytics', 'Advanced Google Analytics setup, tracking, and analysis', '1.40', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(66, 5, 'E-Commerce Metrics', 'Key performance indicators, conversion tracking, and business metrics', '1.35', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(67, 5, 'Data Visualization', 'Creating dashboards, reports, and visual representations of data', '1.20', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(68, 5, 'Business Intelligence', 'Data-driven decision making and strategic analysis', '1.30', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(69, 5, 'A/B Testing', 'Experimental design, statistical analysis, and optimization testing', '1.25', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(70, 6, 'Inventory Management', 'Stock control, demand forecasting, and inventory optimization', '1.30', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(71, 6, 'Supply Chain Management', 'Vendor relationships, procurement, and supply chain optimization', '1.25', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(72, 6, 'Fulfillment Operations', 'Order processing, shipping, and delivery management', '1.20', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(73, 6, 'Process Optimization', 'Workflow improvement, automation, and operational efficiency', '1.15', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(74, 6, 'Quality Control', 'Product quality assurance and customer satisfaction monitoring', '1.10', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(75, 7, 'PCI Compliance', 'Payment card industry compliance and security standards', '1.50', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(76, 7, 'Data Protection', 'GDPR, privacy laws, and data security implementation', '1.40', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(77, 7, 'E-Commerce Law', 'Online business regulations, consumer protection, and legal compliance', '1.35', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(78, 7, 'Security Monitoring', 'Threat detection, incident response, and security monitoring', '1.30', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(79, 7, 'Risk Assessment', 'Security risk evaluation and mitigation strategies', '1.25', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(80, 8, 'Mobile App Development', 'Native and hybrid mobile app development for e-commerce', '1.50', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(81, 8, 'Responsive Design', 'Mobile-first design principles and responsive web development', '1.40', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(82, 8, 'Mobile Payments', 'Mobile payment systems, digital wallets, and mobile commerce', '1.35', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(83, 8, 'Mobile Analytics', 'Mobile-specific tracking, analytics, and performance measurement', '1.30', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(84, 8, 'Mobile UX/UI', 'Mobile user experience design and interface optimization', '1.25', 5, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(85, 9, 'Digital Customer Service', 'try try testing', '1.00', 5, '2025-09-25 11:28:38', '2025-09-25 11:28:38');

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
(1, 'E-Commerce Fundamentals', 'Core competencies for e-commerce professionals including online business understanding, digital customer service, and basic platform management', 'E-Commerce Core', '[\"employee\", \"manager\"]', 'self_assessment', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(2, 'Digital Marketing Excellence', 'Advanced digital marketing skills including SEO, social media marketing, email campaigns, and conversion optimization', 'Marketing', '[\"marketing_specialist\", \"manager\", \"employee\"]', '360_feedback', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(3, 'E-Commerce Technical Skills', 'Technical competencies for e-commerce platforms including Shopify, WooCommerce, payment gateways, and mobile commerce', 'Technical', '[\"developer\", \"technical_specialist\", \"manager\"]', 'manager_review', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(4, 'Customer Experience Management', 'Skills for creating exceptional online customer experiences, support systems, and retention strategies', 'Customer Service', '[\"customer_service\", \"manager\", \"employee\"]', 'peer_review', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(5, 'E-Commerce Analytics & Data', 'Competencies for data analysis, performance tracking, and business intelligence in e-commerce', 'Analytics', '[\"analyst\", \"manager\", \"specialist\"]', 'manager_review', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(6, 'E-Commerce Operations', 'Operational competencies including inventory management, supply chain, fulfillment, and process optimization', 'Operations', '[\"operations_manager\", \"employee\", \"specialist\"]', 'self_assessment', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(7, 'E-Commerce Security & Compliance', 'Security and compliance competencies for online businesses including PCI compliance, data protection, and legal requirements', 'Security', '[\"security_specialist\", \"manager\", \"compliance_officer\"]', 'manager_review', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(8, 'Mobile Commerce Expertise', 'Specialized competencies for mobile-first e-commerce including app development, mobile payments, and responsive design', 'Mobile', '[\"mobile_developer\", \"technical_specialist\", \"manager\"]', 'peer_review', 'active', 1, '2025-09-23 07:25:09', '2025-09-23 07:25:09'),
(9, 'E-Commerce Fundamentals', 'Core competencies for e-commerce professionals including online business understanding, digital customer service, and basic platform management', 'E-Commerce Core', '[\"employee\", \"manager\"]', 'self_assessment', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(10, 'Digital Marketing Excellence', 'Advanced digital marketing skills including SEO, social media marketing, email campaigns, and conversion optimization', 'Marketing', '[\"marketing_specialist\", \"manager\", \"employee\"]', '360_feedback', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(11, 'E-Commerce Technical Skills', 'Technical competencies for e-commerce platforms including Shopify, WooCommerce, payment gateways, and mobile commerce', 'Technical', '[\"developer\", \"technical_specialist\", \"manager\"]', 'manager_review', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(12, 'Customer Experience Management', 'Skills for creating exceptional online customer experiences, support systems, and retention strategies', 'Customer Service', '[\"customer_service\", \"manager\", \"employee\"]', 'peer_review', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(13, 'E-Commerce Analytics & Data', 'Competencies for data analysis, performance tracking, and business intelligence in e-commerce', 'Analytics', '[\"analyst\", \"manager\", \"specialist\"]', 'manager_review', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(14, 'E-Commerce Operations', 'Operational competencies including inventory management, supply chain, fulfillment, and process optimization', 'Operations', '[\"operations_manager\", \"employee\", \"specialist\"]', 'self_assessment', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(15, 'E-Commerce Security & Compliance', 'Security and compliance competencies for online businesses including PCI compliance, data protection, and legal requirements', 'Security', '[\"security_specialist\", \"manager\", \"compliance_officer\"]', 'manager_review', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(16, 'Mobile Commerce Expertise', 'Specialized competencies for mobile-first e-commerce including app development, mobile payments, and responsive design', 'Mobile', '[\"mobile_developer\", \"technical_specialist\", \"manager\"]', 'peer_review', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04');

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
(23, 1, 1, '4.00', 'Good understanding of e-commerce basics, needs improvement in digital customer service', '2025-09-23 07:27:04'),
(24, 1, 2, '3.50', 'Developing skills in digital customer service, shows potential', '2025-09-23 07:27:04'),
(25, 1, 3, '4.50', 'Excellent platform navigation skills', '2025-09-23 07:27:04'),
(26, 1, 4, '4.00', 'Good online communication skills', '2025-09-23 07:27:04'),
(27, 1, 5, '3.00', 'Basic digital literacy, needs more training', '2025-09-23 07:27:04'),
(28, 2, 6, '3.00', 'Basic SEO knowledge, needs advanced training', '2025-09-23 07:27:04'),
(29, 2, 7, '2.50', 'Limited social media marketing experience', '2025-09-23 07:27:04'),
(30, 2, 8, '3.50', 'Good email marketing skills', '2025-09-23 07:27:04'),
(31, 2, 9, '2.00', 'Needs CRO training', '2025-09-23 07:27:04'),
(32, 2, 10, '3.00', 'Basic digital advertising knowledge', '2025-09-23 07:27:04'),
(33, 2, 11, '3.50', 'Good analytics understanding', '2025-09-23 07:27:04'),
(34, 3, 1, '4.50', 'Excellent understanding of e-commerce business', '2025-09-23 07:27:04'),
(35, 3, 2, '4.00', 'Good digital customer service skills', '2025-09-23 07:27:04'),
(36, 3, 3, '4.00', 'Proficient in platform navigation', '2025-09-23 07:27:04'),
(37, 3, 4, '4.50', 'Excellent online communication', '2025-09-23 07:27:04'),
(38, 3, 5, '4.00', 'Good digital literacy', '2025-09-23 07:27:04'),
(39, 4, 12, '4.50', 'Advanced Shopify development skills', '2025-09-23 07:27:04'),
(40, 4, 13, '4.00', 'Good WooCommerce management', '2025-09-23 07:27:04'),
(41, 4, 14, '4.50', 'Expert in payment gateway integration', '2025-09-23 07:27:04'),
(42, 4, 15, '4.00', 'Good mobile commerce development', '2025-09-23 07:27:04'),
(43, 4, 16, '4.50', 'Excellent e-commerce security knowledge', '2025-09-23 07:27:04'),
(44, 4, 17, '4.00', 'Good performance optimization skills', '2025-09-23 07:27:04');

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
(1, 1, 4, 5, 1, 'completed', '3.80', '2025-09-23 07:27:04', '2024-11-15 02:30:00'),
(2, 1, 4, 5, 2, 'completed', '3.00', '2025-09-23 07:27:04', '2024-11-15 03:00:00'),
(3, 1, 5, 1, 1, 'completed', '4.20', '2025-09-23 07:27:04', '2024-11-16 01:00:00'),
(4, 1, 6, 1, 3, 'completed', '4.25', '2025-09-23 07:27:04', '2024-11-16 06:00:00'),
(5, 2, 4, 5, 4, 'in_progress', NULL, '2025-09-23 07:27:04', NULL),
(6, 2, 5, 1, 2, 'pending', NULL, '2025-09-23 07:27:04', NULL);

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
(1, 'Q4 2024 E-Commerce Assessment', 'quarterly', '2024-10-01', '2024-12-31', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(2, 'Annual 2024 Competency Review', 'annual', '2024-01-01', '2024-12-31', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(3, 'E-Commerce Skills Evaluation', 'project_based', '2024-11-01', '2024-11-30', 'active', 1, '2025-09-23 07:27:04', '2025-09-23 07:27:04'),
(4, 'eccomm', 'annual', '2025-09-25', '2025-10-30', 'draft', 1, '2025-09-25 11:29:23', '2025-09-25 11:29:23');

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
-- Indexes for table `ai_analysis_log`
--
ALTER TABLE `ai_analysis_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_analysis_type` (`analysis_type`),
  ADD KEY `idx_ai_analysis_created` (`created_at`);

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
-- AUTO_INCREMENT for table `ai_analysis_log`
--
ALTER TABLE `ai_analysis_log`
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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `competency_models`
--
ALTER TABLE `competency_models`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `competency_reports`
--
ALTER TABLE `competency_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `competency_scores`
--
ALTER TABLE `competency_scores`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_skills`
--
ALTER TABLE `employee_skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `evaluations`
--
ALTER TABLE `evaluations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `evaluation_cycles`
--
ALTER TABLE `evaluation_cycles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- AUTO_INCREMENT for table `training_catalog`
--
ALTER TABLE `training_catalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

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
