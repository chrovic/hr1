-- Migration script to add missing columns to competency tables
-- Run this script to fix the competency models functionality

USE hr1_system;

-- Add status column to competency_models table if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'competency_models' 
     AND COLUMN_NAME = 'status') = 0,
    'ALTER TABLE competency_models ADD COLUMN status ENUM(''active'', ''draft'', ''archived'') DEFAULT ''active'' AFTER assessment_method',
    'SELECT ''status column already exists'' as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add updated_at column to competency_models table if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'competency_models' 
     AND COLUMN_NAME = 'updated_at') = 0,
    'ALTER TABLE competency_models ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at',
    'SELECT ''updated_at column already exists'' as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add updated_at column to competencies table if it doesn't exist
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'competencies' 
     AND COLUMN_NAME = 'updated_at') = 0,
    'ALTER TABLE competencies ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER max_score',
    'SELECT ''updated_at column already exists'' as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update assessment_method enum values to match the expected values
ALTER TABLE competency_models MODIFY COLUMN assessment_method ENUM('self_assessment', 'manager_review', 'peer_review', '360_feedback') DEFAULT 'self_assessment';

SELECT 'Migration completed successfully!' as message;


