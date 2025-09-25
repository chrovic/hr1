-- HR1 System - Complete Database Schema
-- This file contains the complete database structure for the HR1 HR Management System

-- Create database
CREATE DATABASE IF NOT EXISTS hr1_system;
USE hr1_system;

-- ==============================================
-- CORE TABLES
-- ==============================================

-- Users table (central entity)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    role ENUM('admin', 'hr_manager', 'employee') DEFAULT 'employee',
    status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
    employee_id VARCHAR(20),
    department VARCHAR(100),
    position VARCHAR(100),
    phone VARCHAR(20),
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    login_attempts INT DEFAULT 0,
    locked_until TIMESTAMP NULL
);

-- Competency Models table
CREATE TABLE competency_models (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    target_roles JSON,
    assessment_method ENUM('self_assessment', 'manager_review', 'peer_review', '360_feedback') DEFAULT 'self_assessment',
    status ENUM('active', 'draft', 'archived') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Competencies table
CREATE TABLE competencies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    weight DECIMAL(3,2) DEFAULT 1.00,
    max_score INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (model_id) REFERENCES competency_models(id) ON DELETE CASCADE
);

-- Evaluation Cycles table
CREATE TABLE evaluation_cycles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    type ENUM('quarterly', 'annual', 'project_based') DEFAULT 'quarterly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Evaluations table
CREATE TABLE evaluations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cycle_id INT NOT NULL,
    employee_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    model_id INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    overall_score DECIMAL(5,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (cycle_id) REFERENCES evaluation_cycles(id),
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (evaluator_id) REFERENCES users(id),
    FOREIGN KEY (model_id) REFERENCES competency_models(id)
);

-- Competency Scores table
CREATE TABLE competency_scores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evaluation_id INT NOT NULL,
    competency_id INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (competency_id) REFERENCES competencies(id)
);

-- ==============================================
-- TRAINING MANAGEMENT TABLES
-- ==============================================

-- Training Modules table
CREATE TABLE training_modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    duration_hours INT DEFAULT 0,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'beginner',
    prerequisites TEXT,
    learning_objectives TEXT,
    status ENUM('draft', 'active', 'inactive', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Training Sessions table
CREATE TABLE training_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    module_id INT NOT NULL,
    session_name VARCHAR(200) NOT NULL,
    description TEXT,
    trainer_id INT,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    location VARCHAR(200),
    max_participants INT DEFAULT 20,
    status ENUM('scheduled', 'active', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES training_modules(id),
    FOREIGN KEY (trainer_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Training Enrollments table
CREATE TABLE training_enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    session_id INT NOT NULL,
    employee_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('enrolled', 'attended', 'completed', 'dropped') DEFAULT 'enrolled',
    completion_date DATE NULL,
    score DECIMAL(5,2),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES training_sessions(id),
    FOREIGN KEY (employee_id) REFERENCES users(id),
    UNIQUE KEY unique_enrollment (session_id, employee_id)
);

-- Training Requests table
CREATE TABLE training_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    module_id INT,
    request_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (module_id) REFERENCES training_modules(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- ==============================================
-- SUCCESSION PLANNING TABLES
-- ==============================================

-- Succession Plans table
CREATE TABLE succession_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_title VARCHAR(200) NOT NULL,
    department VARCHAR(100),
    current_incumbent_id INT,
    criticality_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    succession_timeline VARCHAR(100),
    status ENUM('active', 'inactive', 'completed') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (current_incumbent_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Succession Candidates table
CREATE TABLE succession_candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    candidate_id INT NOT NULL,
    readiness_level ENUM('ready_now', 'ready_soon', 'development_needed') DEFAULT 'development_needed',
    development_plan TEXT,
    assessment_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES succession_plans(id),
    FOREIGN KEY (candidate_id) REFERENCES users(id)
);

-- ==============================================
-- SYSTEM MANAGEMENT TABLES
-- ==============================================

-- Announcements table
CREATE TABLE announcements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    target_audience ENUM('all', 'managers', 'hr', 'specific') DEFAULT 'all',
    status ENUM('draft', 'active', 'inactive', 'archived') DEFAULT 'draft',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- System Settings table
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

-- Employee Requests table
CREATE TABLE employee_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    request_type ENUM('leave', 'training', 'equipment', 'schedule_change', 'other') NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    request_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (approved_by) REFERENCES users(id)
);

-- System Logs table
CREATE TABLE system_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100),
    record_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- ==============================================
-- INDEXES
-- ==============================================

-- Additional indexes for performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_users_department ON users(department);
CREATE INDEX idx_competency_models_status ON competency_models(status);
CREATE INDEX idx_competency_models_category ON competency_models(category);
CREATE INDEX idx_evaluations_status ON evaluations(status);
CREATE INDEX idx_evaluations_employee ON evaluations(employee_id);
CREATE INDEX idx_evaluations_evaluator ON evaluations(evaluator_id);
CREATE INDEX idx_training_modules_status ON training_modules(status);
CREATE INDEX idx_training_modules_category ON training_modules(category);
CREATE INDEX idx_announcements_status ON announcements(status);
CREATE INDEX idx_announcements_priority ON announcements(priority);
CREATE INDEX idx_system_logs_action ON system_logs(action);
CREATE INDEX idx_system_logs_created_at ON system_logs(created_at);

-- ==============================================
-- SAMPLE DATA
-- ==============================================

-- Insert default users
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, employee_id, department, position, hire_date) VALUES
('admin', 'admin@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', 'ADM001', 'IT', 'System Administrator', '2023-01-01'),
('hrmanager', 'hr@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'HR', 'Manager', 'hr_manager', 'active', 'HRM001', 'Human Resources', 'HR Manager', '2023-01-15'),
('employee1', 'emp1@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Smith', 'employee', 'active', 'EMP001', 'Marketing', 'Marketing Manager', '2023-02-01'),
('employee2', 'emp2@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Doe', 'employee', 'active', 'EMP002', 'Development', 'Software Developer', '2023-02-15'),
('employee3', 'emp3@hr1.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob', 'Johnson', 'employee', 'active', 'EMP003', 'Sales', 'Sales Representative', '2023-03-01');

-- Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('company_name', 'HR1 Company', 'Company name'),
('company_email', 'hr@company.com', 'Company email address'),
('timezone', 'UTC', 'System timezone'),
('evaluation_cycle_days', '90', 'Default evaluation cycle duration in days'),
('training_request_approval_required', 'true', 'Whether training requests require approval'),
('max_evaluation_cycles', '4', 'Maximum number of active evaluation cycles');

-- Insert sample competency models
INSERT INTO competency_models (name, description, category, target_roles, assessment_method, status, created_by) VALUES
('Leadership Competency Model', 'Comprehensive leadership skills assessment for management roles', 'Leadership', '["Manager", "Director", "VP"]', '360_feedback', 'active', 1),
('Technical Skills Model', 'Technical competency assessment for development and IT roles', 'Technical', '["Developer", "Engineer", "Analyst"]', 'manager_review', 'active', 1),
('Communication Skills Model', 'Communication and interpersonal skills assessment', 'Soft Skills', '["All"]', 'peer_review', 'active', 1),
('Project Management Model', 'Project management competencies and skills', 'Management', '["Project Manager", "Team Lead"]', 'self_assessment', 'active', 1);

-- Insert sample competencies
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(1, 'Strategic Thinking', 'Ability to think strategically and plan for the future', 0.25, 5),
(1, 'Team Leadership', 'Skills in leading and motivating teams', 0.30, 5),
(1, 'Decision Making', 'Quality and speed of decision making', 0.20, 5),
(1, 'Communication', 'Effective communication with stakeholders', 0.25, 5),
(2, 'Programming Skills', 'Proficiency in relevant programming languages', 0.40, 5),
(2, 'Problem Solving', 'Ability to analyze and solve technical problems', 0.30, 5),
(2, 'System Design', 'Skills in designing scalable systems', 0.30, 5),
(3, 'Written Communication', 'Clear and effective written communication', 0.50, 5),
(3, 'Verbal Communication', 'Clear and effective verbal communication', 0.50, 5),
(4, 'Project Planning', 'Ability to plan and organize projects', 0.30, 5),
(4, 'Risk Management', 'Identifying and managing project risks', 0.25, 5),
(4, 'Team Coordination', 'Coordinating team activities and resources', 0.45, 5);

-- Insert sample training modules
INSERT INTO training_modules (title, description, category, duration_hours, difficulty_level, status, created_by) VALUES
('Leadership Fundamentals', 'Basic leadership skills and team management', 'Leadership', 8, 'beginner', 'active', 1),
('Project Management', 'Project planning, execution, and monitoring', 'Management', 16, 'intermediate', 'active', 1),
('Communication Skills', 'Effective communication in the workplace', 'Soft Skills', 4, 'beginner', 'active', 1),
('Technical Writing', 'Professional writing and documentation', 'Communication', 6, 'intermediate', 'active', 1),
('Data Analysis', 'Basic data analysis and reporting', 'Technical', 12, 'intermediate', 'active', 1);

-- Insert sample announcements
INSERT INTO announcements (title, content, priority, target_audience, status, created_by) VALUES
('Welcome to HR1 System', 'Welcome to our new HR management system. Please explore the features and let us know if you have any questions.', 'medium', 'all', 'active', 1),
('Quarterly Review Process', 'The quarterly review process will begin next week. Please ensure all evaluations are completed by the deadline.', 'high', 'managers', 'active', 1),
('Training Opportunities', 'New training modules have been added to the system. Check out the latest courses available for professional development.', 'low', 'all', 'active', 1),
('System Maintenance', 'Scheduled maintenance will occur this weekend. The system will be unavailable from 2 AM to 6 AM on Sunday.', 'medium', 'all', 'draft', 1);

-- Insert sample succession plans
INSERT INTO succession_plans (position_title, department, criticality_level, succession_timeline, created_by) VALUES
('Senior Manager', 'Marketing', 'high', '6-12 months', 1),
('Team Lead', 'Development', 'medium', '3-6 months', 1),
('Director', 'Operations', 'critical', '12-18 months', 1);

-- Insert sample employee requests
INSERT INTO employee_requests (employee_id, request_type, title, description, request_date, status) VALUES
(3, 'training', 'Leadership Training Request', 'Request to attend leadership fundamentals course', CURDATE(), 'pending'),
(4, 'leave', 'Vacation Request', 'Request for 1 week vacation', CURDATE(), 'pending'),
(5, 'equipment', 'Laptop Upgrade', 'Request for new laptop for development work', CURDATE(), 'pending');

-- ==============================================
-- HR1 LEGACY TABLES (Data Source for HR2)
-- ==============================================

-- Applicants table (source for competency management)
CREATE TABLE applicants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    skills TEXT,
    certifications TEXT,
    experience_years INT DEFAULT 0,
    status ENUM('applied', 'interviewed', 'hired', 'rejected') DEFAULT 'applied',
    applied_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Job Roles table (source for competency and learning)
CREATE TABLE job_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    department VARCHAR(100),
    required_skills TEXT,
    kpis TEXT,
    min_experience_years INT DEFAULT 0,
    salary_range VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Interview Results table (source for competency and learning)
CREATE TABLE interview_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    applicant_id INT NOT NULL,
    job_role_id INT NOT NULL,
    interviewer_id INT,
    rating DECIMAL(3,1) DEFAULT 0,
    technical_rating DECIMAL(3,1) DEFAULT 0,
    communication_rating DECIMAL(3,1) DEFAULT 0,
    leadership_rating DECIMAL(3,1) DEFAULT 0,
    feedback TEXT,
    recommendation ENUM('hire', 'reject', 'consider', 'pending') DEFAULT 'pending',
    interview_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (applicant_id) REFERENCES applicants(id),
    FOREIGN KEY (job_role_id) REFERENCES job_roles(id),
    FOREIGN KEY (interviewer_id) REFERENCES users(id)
);

-- Performance Reviews table (source for competency and succession)
CREATE TABLE performance_reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    reviewer_id INT NOT NULL,
    probation_score DECIMAL(5,2),
    technical_score DECIMAL(5,2),
    communication_score DECIMAL(5,2),
    leadership_score DECIMAL(5,2),
    evaluator_feedback TEXT,
    employee_feedback TEXT,
    goals TEXT,
    review_period VARCHAR(50),
    review_date DATE NOT NULL,
    status ENUM('draft', 'completed', 'approved') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (reviewer_id) REFERENCES users(id)
);

-- Recognition table (source for succession and ESS)
CREATE TABLE recognition (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    recognized_by INT NOT NULL,
    recognition_type ENUM('peer', 'manager', 'team', 'company') DEFAULT 'peer',
    reason TEXT NOT NULL,
    impact TEXT,
    points INT DEFAULT 10,
    status ENUM('active', 'inactive') DEFAULT 'active',
    recognition_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (recognized_by) REFERENCES users(id)
);

-- Onboarding table (source for learning and training)
CREATE TABLE onboarding (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    task VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'overdue') DEFAULT 'pending',
    due_date DATE,
    completed_date DATE,
    assigned_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id)
);

-- ==============================================
-- HR1 SAMPLE DUMMY DATA
-- ==============================================

-- Insert sample applicants
INSERT INTO applicants (name, email, phone, skills, certifications, experience_years, status, applied_date) VALUES
('John Doe', 'john.doe@email.com', '+1234567890', 'PHP, SQL, JavaScript, HTML, CSS', 'AWS Certified Developer, PMP', 5, 'hired', '2024-01-15'),
('Jane Smith', 'jane.smith@email.com', '+1234567891', 'Communication, HRM, Project Management, Leadership', 'PMP, CSM', 8, 'hired', '2024-01-20'),
('Mike Johnson', 'mike.johnson@email.com', '+1234567892', 'Python, Django, React, Node.js', 'AWS Solutions Architect', 4, 'interviewed', '2024-02-01'),
('Sarah Wilson', 'sarah.wilson@email.com', '+1234567893', 'UI/UX Design, Figma, Adobe Creative Suite', 'Google UX Design Certificate', 3, 'applied', '2024-02-10'),
('David Brown', 'david.brown@email.com', '+1234567894', 'Data Analysis, Python, SQL, Tableau', 'Google Data Analytics', 6, 'hired', '2024-02-15');

-- Insert sample job roles
INSERT INTO job_roles (title, department, required_skills, kpis, min_experience_years, salary_range, status) VALUES
('Software Engineer', 'Development', 'PHP, SQL, JavaScript, Git, Agile', 'Code quality, Delivery time, Bug rate', 3, '$60,000 - $90,000', 'active'),
('HR Manager', 'Human Resources', 'Communication, Leadership, HRM, Conflict Resolution', 'Employee satisfaction, Retention rate, Training completion', 5, '$70,000 - $100,000', 'active'),
('Project Manager', 'Operations', 'Project Management, Agile, Risk Management, Communication', 'Project delivery, Budget adherence, Stakeholder satisfaction', 4, '$75,000 - $110,000', 'active'),
('Data Analyst', 'Analytics', 'SQL, Python, Excel, Data Visualization', 'Report accuracy, Insights quality, Response time', 2, '$55,000 - $80,000', 'active'),
('UI/UX Designer', 'Design', 'UI/UX Design, Figma, Prototyping, User Research', 'User satisfaction, Design quality, Project delivery', 3, '$65,000 - $95,000', 'active');

-- Insert sample interview results
INSERT INTO interview_results (applicant_id, job_role_id, interviewer_id, rating, technical_rating, communication_rating, leadership_rating, feedback, recommendation, interview_date) VALUES
(1, 1, 2, 8.5, 8.0, 9.0, 8.5, 'Strong technical skills, good communication, shows potential for growth', 'hire', '2024-01-20'),
(2, 2, 2, 9.0, 7.5, 9.5, 9.5, 'Excellent communication and leadership skills, perfect fit for HR role', 'hire', '2024-01-25'),
(3, 1, 2, 7.8, 8.5, 7.0, 8.0, 'Very strong technical background, could improve communication skills', 'consider', '2024-02-05'),
(4, 5, 2, 8.2, 8.0, 8.5, 8.0, 'Creative and talented designer, good portfolio and presentation skills', 'hire', '2024-02-12'),
(5, 4, 2, 8.8, 9.0, 8.5, 8.5, 'Excellent analytical skills and experience, strong technical background', 'hire', '2024-02-18');

-- Insert sample performance reviews
INSERT INTO performance_reviews (employee_id, reviewer_id, probation_score, technical_score, communication_score, leadership_score, evaluator_feedback, goals, review_period, review_date, status) VALUES
(3, 2, 85.0, 88.0, 82.0, 85.0, 'John shows strong technical skills and dedication. Needs to work on communication with non-technical stakeholders.', 'Improve cross-team communication, Lead one small project', 'Q4 2023', '2024-01-15', 'completed'),
(4, 2, 78.0, 75.0, 85.0, 80.0, 'Jane demonstrates excellent interpersonal skills and leadership potential. Could benefit from additional technical training.', 'Complete leadership training, Mentor junior staff', 'Q4 2023', '2024-01-20', 'completed'),
(5, 2, 92.0, 95.0, 88.0, 90.0, 'Outstanding technical performance and problem-solving skills. Great team player.', 'Take on more complex projects, Consider team lead role', 'Q4 2023', '2024-02-01', 'completed');

-- Insert sample recognition
INSERT INTO recognition (employee_id, recognized_by, recognition_type, reason, impact, points, recognition_date) VALUES
(3, 4, 'peer', 'Helped debug critical production issue during off-hours', 'Prevented potential system downtime and saved company resources', 25, '2024-01-10'),
(4, 2, 'manager', 'Successfully led team through challenging project with tight deadlines', 'Project delivered on time with high quality, improved team morale', 30, '2024-01-18'),
(5, 3, 'team', 'Mentored junior developer and helped them achieve certification', 'Improved team capabilities and knowledge sharing', 20, '2024-02-05'),
(3, 4, 'peer', 'Excellent teamwork during quarterly planning session', 'Contributed valuable insights that improved project planning', 15, '2024-02-12'),
(4, 2, 'manager', 'Outstanding communication during client presentations', 'Enhanced client relationships and satisfaction', 25, '2024-02-20');

-- Insert sample onboarding tasks
INSERT INTO onboarding (employee_id, task, description, status, due_date, assigned_by) VALUES
(3, 'Complete IT Setup', 'Set up workstation, email, and access to required systems', 'completed', '2024-01-20', 2),
(3, 'HR Orientation', 'Attend HR orientation session and review company policies', 'completed', '2024-01-22', 2),
(3, 'Technical Training', 'Complete basic technical training modules', 'in_progress', '2024-02-15', 2),
(4, 'Complete IT Setup', 'Set up workstation, email, and access to required systems', 'completed', '2024-01-25', 2),
(4, 'HR Orientation', 'Attend HR orientation session and review company policies', 'completed', '2024-01-27', 2),
(4, 'Leadership Training', 'Complete leadership fundamentals training', 'pending', '2024-03-01', 2),
(5, 'Complete IT Setup', 'Set up workstation, email, and access to required systems', 'completed', '2024-02-20', 2),
(5, 'HR Orientation', 'Attend HR orientation session and review company policies', 'in_progress', '2024-02-25', 2);

-- ==============================================
-- COMPLETION MESSAGE
-- ==============================================

SELECT 'HR1 System database schema created successfully!' as message;
SELECT 'Total tables created: 22 (including HR1 legacy tables)' as table_count;
SELECT 'Sample data inserted successfully!' as data_status;
