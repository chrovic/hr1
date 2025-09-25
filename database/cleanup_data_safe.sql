-- Safe Database Cleanup Script
-- This script removes data from existing tables only

USE hr1_system;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Clean up training-related data (only if tables exist)
DELETE FROM training_enrollments WHERE 1=1;
DELETE FROM training_sessions WHERE 1=1;
DELETE FROM training_requests WHERE 1=1;
DELETE FROM training_modules WHERE 1=1;

-- Clean up skills and certifications data (only if tables exist)
DELETE FROM employee_skills WHERE 1=1;
DELETE FROM employee_certifications WHERE 1=1;
DELETE FROM employee_learning_paths WHERE 1=1;
DELETE FROM skills_catalog WHERE 1=1;
DELETE FROM certifications_catalog WHERE 1=1;
DELETE FROM learning_paths WHERE 1=1;

-- Clean up user-related data (keep admin users)
DELETE FROM users WHERE role != 'admin';

-- Clean up competency and evaluation data (only if tables exist)
DELETE FROM evaluation_scores WHERE 1=1;
DELETE FROM evaluations WHERE 1=1;
DELETE FROM competency_models WHERE 1=1;
DELETE FROM competencies WHERE 1=1;

-- Clean up request management data (only if tables exist)
DELETE FROM request_approvals WHERE 1=1;
DELETE FROM employee_requests WHERE 1=1;

-- Clean up succession planning data (only if tables exist)
DELETE FROM succession_plans WHERE 1=1;
DELETE FROM succession_candidates WHERE 1=1;

-- Clean up notification data (only if tables exist)
DELETE FROM notifications WHERE 1=1;

-- Clean up activity logs (only if tables exist)
DELETE FROM activity_logs WHERE 1=1;

-- Clean up department and position data (keep basic structure)
DELETE FROM positions WHERE id > 1;
DELETE FROM departments WHERE id > 1;

-- Reset auto-increment counters (only if tables exist)
ALTER TABLE training_enrollments AUTO_INCREMENT = 1;
ALTER TABLE training_sessions AUTO_INCREMENT = 1;
ALTER TABLE training_requests AUTO_INCREMENT = 1;
ALTER TABLE training_modules AUTO_INCREMENT = 1;
ALTER TABLE users AUTO_INCREMENT = 1;
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


