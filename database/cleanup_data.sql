-- Database Cleanup Script
-- This script removes all data from tables while preserving table structures
-- Use with caution - this will delete ALL data!

USE hr1_system;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clean up training-related data
DELETE FROM training_enrollments;
DELETE FROM training_sessions;
DELETE FROM training_requests;
DELETE FROM training_modules;

-- Clean up skills and certifications data
DELETE FROM employee_skills;
DELETE FROM employee_certifications;
DELETE FROM employee_learning_paths;
DELETE FROM skills_catalog;
DELETE FROM certifications_catalog;
DELETE FROM learning_paths;

-- Clean up user-related data (keep admin users)
DELETE FROM user_sessions;
DELETE FROM remember_tokens;
DELETE FROM users WHERE role != 'admin';

-- Clean up competency and evaluation data
DELETE FROM evaluation_scores;
DELETE FROM evaluations;
DELETE FROM competency_models;
DELETE FROM competencies;

-- Clean up request management data
DELETE FROM request_approvals;
DELETE FROM employee_requests;

-- Clean up succession planning data
DELETE FROM succession_plans;
DELETE FROM succession_candidates;

-- Clean up notification data
DELETE FROM notifications;

-- Clean up activity logs
DELETE FROM activity_logs;

-- Clean up department and position data (keep basic structure)
DELETE FROM positions WHERE id > 1;
DELETE FROM departments WHERE id > 1;

-- Reset auto-increment counters
ALTER TABLE training_enrollments AUTO_INCREMENT = 1;
ALTER TABLE training_sessions AUTO_INCREMENT = 1;
ALTER TABLE training_requests AUTO_INCREMENT = 1;
ALTER TABLE training_modules AUTO_INCREMENT = 1;
ALTER TABLE employee_skills AUTO_INCREMENT = 1;
ALTER TABLE employee_certifications AUTO_INCREMENT = 1;
ALTER TABLE employee_learning_paths AUTO_INCREMENT = 1;
ALTER TABLE skills_catalog AUTO_INCREMENT = 1;
ALTER TABLE certifications_catalog AUTO_INCREMENT = 1;
ALTER TABLE learning_paths AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE user_sessions AUTO_INCREMENT = 1;
ALTER TABLE remember_tokens AUTO_INCREMENT = 1;
ALTER TABLE evaluation_scores AUTO_INCREMENT = 1;
ALTER TABLE evaluations AUTO_INCREMENT = 1;
ALTER TABLE competency_models AUTO_INCREMENT = 1;
ALTER TABLE competencies AUTO_INCREMENT = 1;
ALTER TABLE request_approvals AUTO_INCREMENT = 1;
ALTER TABLE employee_requests AUTO_INCREMENT = 1;
ALTER TABLE succession_plans AUTO_INCREMENT = 1;
ALTER TABLE succession_candidates AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;
ALTER TABLE activity_logs AUTO_INCREMENT = 1;
ALTER TABLE positions AUTO_INCREMENT = 1;
ALTER TABLE departments AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Insert basic admin user if not exists
INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role, status, employee_id, department, position, hire_date) VALUES
('admin', 'admin@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', 'ADM001', 'IT', 'System Administrator', '2023-01-01');

-- Insert basic departments
INSERT IGNORE INTO departments (name, description, manager_id, budget, location) VALUES
('IT', 'Information Technology', NULL, 500000.00, 'Main Office'),
('Human Resources', 'Human Resources Management', NULL, 300000.00, 'Main Office'),
('Finance', 'Financial Management', NULL, 400000.00, 'Main Office'),
('Marketing', 'Marketing and Sales', NULL, 350000.00, 'Main Office'),
('Operations', 'Business Operations', NULL, 600000.00, 'Main Office');

-- Insert basic positions
INSERT IGNORE INTO positions (title, department_id, description, requirements, salary_min, salary_max, status) VALUES
('System Administrator', 1, 'IT System Administration', 'Bachelor degree in IT', 60000, 80000, 'active'),
('HR Manager', 2, 'Human Resources Management', 'Bachelor degree in HR', 55000, 75000, 'active'),
('Financial Analyst', 3, 'Financial Analysis', 'Bachelor degree in Finance', 50000, 70000, 'active'),
('Marketing Specialist', 4, 'Marketing and Sales', 'Bachelor degree in Marketing', 45000, 65000, 'active'),
('Operations Manager', 5, 'Business Operations', 'Bachelor degree in Business', 60000, 85000, 'active');

SELECT 'Database cleanup completed successfully!' as message;


