-- Learning Management System Enhancements - Safe Version
-- This file contains additional tables and modifications for enhanced learning management

USE hr1_system;

-- Add skills tracking to training_modules table (only if columns don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_modules' 
     AND COLUMN_NAME = 'skills_developed') = 0,
    'ALTER TABLE training_modules ADD COLUMN skills_developed JSON COMMENT "Skills that will be developed through this training"',
    'SELECT "Column skills_developed already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_modules' 
     AND COLUMN_NAME = 'certifications_awarded') = 0,
    'ALTER TABLE training_modules ADD COLUMN certifications_awarded JSON COMMENT "Certifications that can be earned"',
    'SELECT "Column certifications_awarded already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_modules' 
     AND COLUMN_NAME = 'type') = 0,
    'ALTER TABLE training_modules ADD COLUMN type ENUM("skill_development", "certification", "compliance", "leadership", "technical") DEFAULT "skill_development"',
    'SELECT "Column type already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_modules' 
     AND COLUMN_NAME = 'max_participants') = 0,
    'ALTER TABLE training_modules ADD COLUMN max_participants INT DEFAULT 20',
    'SELECT "Column max_participants already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_modules' 
     AND COLUMN_NAME = 'cost') = 0,
    'ALTER TABLE training_modules ADD COLUMN cost DECIMAL(10,2) DEFAULT 0.00',
    'SELECT "Column cost already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create skills catalog table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS skills_catalog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
    competency_model_id INT,
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (competency_model_id) REFERENCES competency_models(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create certifications catalog table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS certifications_catalog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    issuing_body VARCHAR(200),
    validity_period_months INT DEFAULT 24,
    renewal_required BOOLEAN DEFAULT TRUE,
    cost DECIMAL(10,2) DEFAULT 0.00,
    prerequisites TEXT,
    exam_required BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'archived') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create employee skills table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS employee_skills (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
    acquired_date DATE,
    verified_by INT,
    verification_date DATE,
    status ENUM('active', 'expired', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (skill_id) REFERENCES skills_catalog(id),
    FOREIGN KEY (verified_by) REFERENCES users(id),
    UNIQUE KEY unique_employee_skill (employee_id, skill_id)
);

-- Create employee certifications table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS employee_certifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    certification_id INT NOT NULL,
    issue_date DATE NOT NULL,
    expiry_date DATE,
    certificate_number VARCHAR(100),
    issuing_body VARCHAR(200),
    verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    verified_by INT,
    verification_date DATE,
    status ENUM('active', 'expired', 'suspended', 'revoked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (certification_id) REFERENCES certifications_catalog(id),
    FOREIGN KEY (verified_by) REFERENCES users(id),
    UNIQUE KEY unique_employee_certification (employee_id, certification_id)
);

-- Enhance training_requests table (only if columns don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_requests' 
     AND COLUMN_NAME = 'reason') = 0,
    'ALTER TABLE training_requests ADD COLUMN reason TEXT COMMENT "Employee reason for requesting training"',
    'SELECT "Column reason already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_requests' 
     AND COLUMN_NAME = 'priority') = 0,
    'ALTER TABLE training_requests ADD COLUMN priority ENUM("low", "medium", "high", "urgent") DEFAULT "medium"',
    'SELECT "Column priority already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_requests' 
     AND COLUMN_NAME = 'manager_id') = 0,
    'ALTER TABLE training_requests ADD COLUMN manager_id INT, ADD FOREIGN KEY (manager_id) REFERENCES users(id)',
    'SELECT "Column manager_id already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_requests' 
     AND COLUMN_NAME = 'estimated_cost') = 0,
    'ALTER TABLE training_requests ADD COLUMN estimated_cost DECIMAL(10,2) DEFAULT 0.00',
    'SELECT "Column estimated_cost already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Enhance training_enrollments table (only if columns don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_enrollments' 
     AND COLUMN_NAME = 'attendance_status') = 0,
    'ALTER TABLE training_enrollments ADD COLUMN attendance_status ENUM("present", "absent", "late", "excused") DEFAULT "present"',
    'SELECT "Column attendance_status already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_enrollments' 
     AND COLUMN_NAME = 'completion_status') = 0,
    'ALTER TABLE training_enrollments ADD COLUMN completion_status ENUM("not_started", "in_progress", "completed", "failed", "dropped") DEFAULT "not_started"',
    'SELECT "Column completion_status already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'hr1_system' 
     AND TABLE_NAME = 'training_enrollments' 
     AND COLUMN_NAME = 'completion_score') = 0,
    'ALTER TABLE training_enrollments ADD COLUMN completion_score DECIMAL(5,2)',
    'SELECT "Column completion_score already exists"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Create learning paths table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS learning_paths (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    target_role VARCHAR(100),
    estimated_duration_days INT DEFAULT 30,
    prerequisites TEXT,
    learning_objectives TEXT,
    status ENUM('draft', 'active', 'inactive', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Create learning path modules table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS learning_path_modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    path_id INT NOT NULL,
    module_id INT NOT NULL,
    sequence_order INT NOT NULL,
    is_required BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (path_id) REFERENCES learning_paths(id) ON DELETE CASCADE,
    FOREIGN KEY (module_id) REFERENCES training_modules(id) ON DELETE CASCADE,
    UNIQUE KEY unique_path_module (path_id, module_id)
);

-- Create employee learning paths table (only if it doesn't exist)
CREATE TABLE IF NOT EXISTS employee_learning_paths (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    path_id INT NOT NULL,
    started_date DATE,
    completed_date DATE,
    progress_percentage DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('not_started', 'in_progress', 'completed', 'paused', 'cancelled') DEFAULT 'not_started',
    assigned_by INT,
    assigned_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (path_id) REFERENCES learning_paths(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY unique_employee_path (employee_id, path_id)
);

-- Insert sample skills (only if table is empty)
INSERT IGNORE INTO skills_catalog (name, description, category, skill_level, created_by) VALUES
('Project Management', 'Ability to plan, execute, and monitor projects effectively', 'Management', 'intermediate', 1),
('Leadership', 'Skills to lead and motivate teams', 'Management', 'advanced', 1),
('Communication', 'Effective verbal and written communication skills', 'Soft Skills', 'intermediate', 1),
('Problem Solving', 'Analytical thinking and problem-solving abilities', 'Soft Skills', 'intermediate', 1),
('JavaScript Programming', 'Frontend and backend JavaScript development', 'Technical', 'intermediate', 1),
('Database Design', 'Designing and managing database systems', 'Technical', 'advanced', 1),
('Agile Methodology', 'Understanding and applying agile development practices', 'Technical', 'intermediate', 1),
('Customer Service', 'Providing excellent customer support and service', 'Soft Skills', 'beginner', 1),
('Financial Analysis', 'Analyzing financial data and making informed decisions', 'Business', 'advanced', 1),
('Data Analysis', 'Analyzing data to extract meaningful insights', 'Technical', 'intermediate', 1);

-- Insert sample certifications (only if table is empty)
INSERT IGNORE INTO certifications_catalog (name, description, issuing_body, validity_period_months, renewal_required, cost, exam_required, created_by) VALUES
('PMP Certification', 'Project Management Professional certification', 'PMI', 36, TRUE, 555.00, TRUE, 1),
('AWS Certified Solutions Architect', 'Amazon Web Services cloud architecture certification', 'Amazon', 36, TRUE, 150.00, TRUE, 1),
('Google Analytics Certified', 'Google Analytics certification for digital marketing', 'Google', 12, TRUE, 0.00, TRUE, 1),
('Scrum Master Certification', 'Certified Scrum Master for agile project management', 'Scrum Alliance', 24, TRUE, 1295.00, TRUE, 1),
('ITIL Foundation', 'IT Service Management Foundation certification', 'AXELOS', 36, FALSE, 250.00, TRUE, 1),
('Salesforce Administrator', 'Salesforce platform administration certification', 'Salesforce', 12, TRUE, 200.00, TRUE, 1),
('Microsoft Azure Fundamentals', 'Azure cloud platform fundamentals certification', 'Microsoft', 12, TRUE, 99.00, TRUE, 1),
('Six Sigma Green Belt', 'Process improvement and quality management certification', 'ASQ', 36, FALSE, 400.00, TRUE, 1);

-- Insert sample learning paths (only if table is empty)
INSERT IGNORE INTO learning_paths (name, description, target_role, estimated_duration_days, learning_objectives, created_by) VALUES
('Project Management Fundamentals', 'Complete project management training path for new managers', 'Manager', 60, 'Learn project planning, execution, monitoring, and team leadership', 1),
('Technical Leadership', 'Advanced technical skills for senior developers', 'Senior Developer', 90, 'Master advanced programming, architecture, and team leadership', 1),
('Customer Success Specialist', 'Training path for customer-facing roles', 'Customer Success', 45, 'Develop communication, problem-solving, and customer service skills', 1),
('Data Analytics Professional', 'Comprehensive data analysis and visualization training', 'Data Analyst', 75, 'Learn data collection, analysis, visualization, and reporting', 1);

-- Add indexes for better performance (only if they don't exist)
CREATE INDEX IF NOT EXISTS idx_employee_skills_employee ON employee_skills(employee_id);
CREATE INDEX IF NOT EXISTS idx_employee_certifications_employee ON employee_certifications(employee_id);
CREATE INDEX IF NOT EXISTS idx_employee_certifications_expiry ON employee_certifications(expiry_date);
CREATE INDEX IF NOT EXISTS idx_training_requests_status ON training_requests(status);
CREATE INDEX IF NOT EXISTS idx_training_requests_employee ON training_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_learning_paths_status ON learning_paths(status);
CREATE INDEX IF NOT EXISTS idx_employee_learning_paths_employee ON employee_learning_paths(employee_id);


