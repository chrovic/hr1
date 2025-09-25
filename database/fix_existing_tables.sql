-- Add missing columns to existing tables
USE hr1_system;

-- Add status column to competency_models table
ALTER TABLE competency_models ADD COLUMN status ENUM('active', 'draft', 'archived') DEFAULT 'active' AFTER assessment_method;

-- Add updated_at column to competency_models table
ALTER TABLE competency_models ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Add updated_at column to competencies table
ALTER TABLE competencies ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER max_score;

SELECT 'Tables updated successfully!' as message;
