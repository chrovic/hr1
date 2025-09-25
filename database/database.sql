-- HR1 Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS hr1_system;
USE hr1_system;

-- Users table with roles and authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr_manager', 'employee') NOT NULL DEFAULT 'employee',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    employee_id VARCHAR(20) UNIQUE,
    department VARCHAR(50),
    position VARCHAR(100),
    phone VARCHAR(20),
    hire_date DATE,
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(32),
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User sessions for tracking
CREATE TABLE user_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Competency models
CREATE TABLE competency_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    target_roles JSON,
    assessment_method ENUM('self', 'manager', 'peer', '360', 'combined') DEFAULT 'combined',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Competencies within models
CREATE TABLE competencies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    weight DECIMAL(3,2) DEFAULT 1.00,
    max_score INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES competency_models(id) ON DELETE CASCADE
);

-- Evaluation cycles
CREATE TABLE evaluation_cycles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    type ENUM('probation', 'quarterly', 'yearly', 'custom') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Evaluation assignments
CREATE TABLE evaluations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cycle_id INT NOT NULL,
    employee_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    model_id INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    overall_score DECIMAL(3,2),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (cycle_id) REFERENCES evaluation_cycles(id),
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (evaluator_id) REFERENCES users(id),
    FOREIGN KEY (model_id) REFERENCES competency_models(id)
);

-- Individual competency scores
CREATE TABLE competency_scores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evaluation_id INT NOT NULL,
    competency_id INT NOT NULL,
    score INT NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (competency_id) REFERENCES competencies(id)
);

-- Training catalog
CREATE TABLE training_catalog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    type ENUM('in_person', 'virtual', 'hybrid', 'self_paced') DEFAULT 'virtual',
    duration_hours INT,
    max_participants INT,
    prerequisites TEXT,
    learning_objectives TEXT,
    status ENUM('draft', 'active', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Training requests
CREATE TABLE training_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    training_id INT NOT NULL,
    request_type ENUM('self_request', 'manager_recommended', 'hr_assigned') DEFAULT 'self_request',
    justification TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (training_id) REFERENCES training_catalog(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- Training sessions
CREATE TABLE training_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    training_id INT NOT NULL,
    trainer_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(200),
    max_participants INT,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (training_id) REFERENCES training_catalog(id),
    FOREIGN KEY (trainer_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Training enrollments
CREATE TABLE training_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    employee_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM('enrolled', 'present', 'absent', 'late') DEFAULT 'enrolled',
    completion_status ENUM('not_started', 'in_progress', 'completed', 'failed') DEFAULT 'not_started',
    completion_score DECIMAL(5,2),
    feedback TEXT,
    FOREIGN KEY (session_id) REFERENCES training_sessions(id),
    FOREIGN KEY (employee_id) REFERENCES users(id),
    UNIQUE KEY unique_enrollment (session_id, employee_id)
);

-- Succession planning
CREATE TABLE critical_positions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_title VARCHAR(100) NOT NULL,
    department VARCHAR(50),
    description TEXT,
    priority_level ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    succession_timeline ENUM('1-2_years', '2-3_years', '3-4_years', '4-5_years', '5+_years') DEFAULT '2-3_years',
    risk_level ENUM('high', 'medium', 'low') DEFAULT 'medium',
    current_incumbent_id INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_incumbent_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Succession candidates
CREATE TABLE succession_candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_id INT NOT NULL,
    candidate_id INT NOT NULL,
    readiness_level ENUM('ready_now', 'ready_soon', 'development_needed', 'not_ready') DEFAULT 'development_needed',
    readiness_score DECIMAL(3,2),
    development_plan TEXT,
    timeline_months INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES critical_positions(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY unique_candidate (position_id, candidate_id)
);

-- Employee requests (self-service)
CREATE TABLE employee_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    request_type ENUM('leave', 'expense', 'training', 'schedule_adjustment', 'other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    request_data JSON,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    review_comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);

-- System logs
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- System settings
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Insert default admin user
INSERT INTO users (username, email, password_hash, role, first_name, last_name, employee_id, department, position, hire_date) 
VALUES ('admin', 'admin@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System', 'Administrator', 'ADMIN001', 'IT', 'System Administrator', CURDATE());

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('company_name', 'HR1 Company', 'string', 'Company name'),
('company_logo', '', 'string', 'Company logo URL'),
('two_factor_required', 'false', 'boolean', 'Require 2FA for all users'),
('session_timeout', '3600', 'number', 'Session timeout in seconds'),
('email_notifications', 'true', 'boolean', 'Enable email notifications'),
('huggingface_api_key', '', 'string', 'Hugging Face API key for AI features');
