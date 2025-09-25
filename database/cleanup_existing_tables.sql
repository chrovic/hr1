-- Database Cleanup Script for Existing Tables Only
-- This script removes data from tables that actually exist

USE hr1_system;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clean up training-related data
DELETE FROM training_enrollments;
DELETE FROM training_sessions;
DELETE FROM training_requests;
DELETE FROM training_modules;
DELETE FROM training_catalog;

-- Clean up skills and certifications data
DELETE FROM employee_skills;
DELETE FROM employee_certifications;
DELETE FROM employee_learning_paths;
DELETE FROM skills_catalog;
DELETE FROM certifications_catalog;
DELETE FROM learning_paths;
DELETE FROM learning_path_modules;

-- Clean up user-related data (keep admin users)
DELETE FROM users WHERE role != 'admin';

-- Clean up competency and evaluation data
DELETE FROM competency_scores;
DELETE FROM evaluation_scores;
DELETE FROM evaluations;
DELETE FROM competency_models;
DELETE FROM competencies;
DELETE FROM evaluation_cycles;
DELETE FROM competency_reports;

-- Clean up request management data
DELETE FROM employee_requests;

-- Clean up succession planning data
DELETE FROM critical_positions;

-- Clean up system data
DELETE FROM announcements;
DELETE FROM system_logs;
DELETE FROM ai_analysis_log;
DELETE FROM ai_recommendation_log;

-- Reset auto-increment counters
ALTER TABLE training_enrollments AUTO_INCREMENT = 1;
ALTER TABLE training_sessions AUTO_INCREMENT = 1;
ALTER TABLE training_requests AUTO_INCREMENT = 1;
ALTER TABLE training_modules AUTO_INCREMENT = 1;
ALTER TABLE training_catalog AUTO_INCREMENT = 1;
ALTER TABLE employee_skills AUTO_INCREMENT = 1;
ALTER TABLE employee_certifications AUTO_INCREMENT = 1;
ALTER TABLE employee_learning_paths AUTO_INCREMENT = 1;
ALTER TABLE skills_catalog AUTO_INCREMENT = 1;
ALTER TABLE certifications_catalog AUTO_INCREMENT = 1;
ALTER TABLE learning_paths AUTO_INCREMENT = 1;
ALTER TABLE learning_path_modules AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE competency_scores AUTO_INCREMENT = 1;
ALTER TABLE evaluation_scores AUTO_INCREMENT = 1;
ALTER TABLE evaluations AUTO_INCREMENT = 1;
ALTER TABLE competency_models AUTO_INCREMENT = 1;
ALTER TABLE competencies AUTO_INCREMENT = 1;
ALTER TABLE evaluation_cycles AUTO_INCREMENT = 1;
ALTER TABLE competency_reports AUTO_INCREMENT = 1;
ALTER TABLE employee_requests AUTO_INCREMENT = 1;
ALTER TABLE critical_positions AUTO_INCREMENT = 1;
ALTER TABLE announcements AUTO_INCREMENT = 1;
ALTER TABLE system_logs AUTO_INCREMENT = 1;
ALTER TABLE ai_analysis_log AUTO_INCREMENT = 1;
ALTER TABLE ai_recommendation_log AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert basic admin user if not exists
INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role, status, employee_id, department, position, hire_date) VALUES
('admin', 'admin@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', 'ADM001', 'IT', 'System Administrator', '2023-01-01');

SELECT 'Database cleanup completed successfully!' as message;


