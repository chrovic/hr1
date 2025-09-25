-- Fix training_requests table structure
USE hr1_system;

-- Add missing columns to training_requests table
ALTER TABLE training_requests 
ADD COLUMN training_title VARCHAR(200) AFTER employee_id,
ADD COLUMN training_description TEXT AFTER training_title,
ADD COLUMN requested_date DATE AFTER training_description,
ADD COLUMN estimated_cost DECIMAL(10,2) AFTER requested_date,
ADD COLUMN justification TEXT AFTER estimated_cost;

-- Make module_id nullable since we're adding direct training requests
ALTER TABLE training_requests MODIFY COLUMN module_id INT NULL;

SELECT 'Training requests table updated successfully!' as message;
