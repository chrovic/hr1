-- ==============================================
-- CLEANUP ALL STATIC/SAMPLE DATA FROM HR1 SYSTEM
-- ==============================================
-- This script removes all sample/static data while preserving table structure
-- Run this script to clean your system for production use

USE hr1_system;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- ==============================================
-- CLEANUP USER DATA (Keep only essential admin users)
-- ==============================================
-- Keep only admin users, remove all sample employees
DELETE FROM users WHERE role IN ('employee', 'hr_manager') AND username NOT IN ('admin', 'admin2', 'hrmanager', 'hrmanager2');

-- ==============================================
-- CLEANUP COMPETENCY DATA
-- ==============================================
-- Remove all competency scores
DELETE FROM competency_scores;

-- Remove all competencies
DELETE FROM competencies;

-- Remove all competency models
DELETE FROM competency_models;

-- ==============================================
-- CLEANUP EVALUATION DATA
-- ==============================================
-- Remove all evaluations
DELETE FROM evaluations;

-- Remove all evaluation cycles
DELETE FROM evaluation_cycles;

-- ==============================================
-- CLEANUP TRAINING DATA
-- ==============================================
-- Remove all training enrollments
DELETE FROM training_enrollments;

-- Remove all training sessions
DELETE FROM training_sessions;

-- Remove all training requests
DELETE FROM training_requests;

-- Remove all training modules
DELETE FROM training_modules;

-- ==============================================
-- CLEANUP SUCCESSION PLANNING DATA
-- ==============================================
-- Remove all succession candidates
DELETE FROM succession_candidates;

-- Remove all succession plans
DELETE FROM succession_plans;

-- ==============================================
-- CLEANUP EMPLOYEE REQUESTS
-- ==============================================
-- Remove all employee requests
DELETE FROM employee_requests;

-- ==============================================
-- CLEANUP ANNOUNCEMENTS
-- ==============================================
-- Remove all announcements
DELETE FROM announcements;

-- ==============================================
-- CLEANUP SYSTEM LOGS
-- ==============================================
-- Remove all system logs (optional - you might want to keep these)
DELETE FROM system_logs;

-- ==============================================
-- RESET AUTO_INCREMENT VALUES
-- ==============================================
-- Reset auto-increment counters for clean IDs
ALTER TABLE competency_models AUTO_INCREMENT = 1;
ALTER TABLE competencies AUTO_INCREMENT = 1;
ALTER TABLE competency_scores AUTO_INCREMENT = 1;
ALTER TABLE evaluation_cycles AUTO_INCREMENT = 1;
ALTER TABLE evaluations AUTO_INCREMENT = 1;
ALTER TABLE training_modules AUTO_INCREMENT = 1;
ALTER TABLE training_sessions AUTO_INCREMENT = 1;
ALTER TABLE training_enrollments AUTO_INCREMENT = 1;
ALTER TABLE training_requests AUTO_INCREMENT = 1;
ALTER TABLE succession_plans AUTO_INCREMENT = 1;
ALTER TABLE succession_candidates AUTO_INCREMENT = 1;
ALTER TABLE employee_requests AUTO_INCREMENT = 1;
ALTER TABLE announcements AUTO_INCREMENT = 1;
ALTER TABLE system_logs AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================
-- VERIFICATION QUERIES
-- ==============================================
-- Show remaining data counts
SELECT 'CLEANUP COMPLETED' as status;
SELECT 'Remaining Users:' as info, COUNT(*) as count FROM users;
SELECT 'Remaining Competency Models:' as info, COUNT(*) as count FROM competency_models;
SELECT 'Remaining Competencies:' as info, COUNT(*) as count FROM competencies;
SELECT 'Remaining Evaluations:' as info, COUNT(*) as count FROM evaluations;
SELECT 'Remaining Training Modules:' as info, COUNT(*) as count FROM training_modules;
SELECT 'Remaining Announcements:' as info, COUNT(*) as count FROM announcements;
SELECT 'Remaining Employee Requests:' as info, COUNT(*) as count FROM employee_requests;

-- Show remaining users
SELECT 'Remaining Users:' as info;
SELECT id, username, email, role, status, department, position FROM users ORDER BY role, username;





