-- Fix competency_models table schema
USE hr1_system;

-- Update assessment_method column to allow longer values
ALTER TABLE competency_models 
MODIFY COLUMN assessment_method ENUM('self_assessment', 'manager_review', 'peer_review', '360_feedback') DEFAULT 'self_assessment';

-- Add status column if it doesn't exist
ALTER TABLE competency_models 
ADD COLUMN IF NOT EXISTS status ENUM('active', 'draft', 'archived') DEFAULT 'active' AFTER assessment_method;

-- Add updated_at column if it doesn't exist
ALTER TABLE competency_models 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

SELECT 'Competency models table updated successfully!' as message;





