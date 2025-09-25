-- Phase 2: Succession Planning Module Database Schema
-- This file contains the complete database structure for succession planning functionality

USE hr1_system;

-- Critical roles table
CREATE TABLE critical_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    position_title VARCHAR(200) NOT NULL,
    department VARCHAR(100),
    level ENUM('entry', 'mid', 'senior', 'executive') NOT NULL,
    description TEXT,
    requirements TEXT,
    risk_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    current_incumbent_id INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_incumbent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Succession candidates table
CREATE TABLE succession_candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    employee_id INT NOT NULL,
    readiness_level ENUM('ready_now', 'ready_soon', 'development_needed') NOT NULL,
    development_plan TEXT,
    notes TEXT,
    assessment_date DATE,
    next_review_date DATE,
    assigned_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES critical_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY unique_role_employee (role_id, employee_id)
);

-- Succession plans table
CREATE TABLE succession_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    plan_name VARCHAR(200) NOT NULL,
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    start_date DATE,
    end_date DATE,
    objectives TEXT,
    success_metrics TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES critical_roles(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Succession plan candidates table (links plans to candidates)
CREATE TABLE succession_plan_candidates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    candidate_id INT NOT NULL,
    priority_order INT DEFAULT 1,
    target_readiness_date DATE,
    development_focus TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES succession_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES succession_candidates(id) ON DELETE CASCADE
);

-- Succession assessments table (track candidate progress)
CREATE TABLE succession_assessments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    candidate_id INT NOT NULL,
    assessor_id INT NOT NULL,
    assessment_type ENUM('initial', 'progress', 'final') NOT NULL,
    technical_readiness_score INT,
    leadership_readiness_score INT,
    cultural_fit_score INT,
    overall_readiness_score INT,
    strengths TEXT,
    development_areas TEXT,
    recommendations TEXT,
    assessment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (candidate_id) REFERENCES succession_candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (assessor_id) REFERENCES users(id)
);

-- Succession risk analysis table
CREATE TABLE succession_risks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    risk_type ENUM('vacancy', 'knowledge_loss', 'competition', 'market') NOT NULL,
    risk_level ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    risk_description TEXT,
    mitigation_strategy TEXT,
    contingency_plan TEXT,
    identified_by INT NOT NULL,
    identified_date DATE NOT NULL,
    status ENUM('open', 'mitigated', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES critical_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (identified_by) REFERENCES users(id)
);

-- Insert sample data
INSERT INTO critical_roles (position_title, department, level, description, requirements, risk_level, created_by) VALUES
('Chief Executive Officer', 'Executive', 'executive', 'Overall company leadership and strategic direction', 'MBA, 10+ years executive experience, proven leadership track record', 'critical', 1),
('Chief Technology Officer', 'Technology', 'executive', 'Technology strategy and innovation leadership', 'Computer Science degree, 8+ years tech leadership, innovation experience', 'high', 1),
('Head of Human Resources', 'Human Resources', 'senior', 'HR strategy and people operations', 'HR degree, 5+ years HR leadership, strategic thinking', 'medium', 1),
('Senior Software Engineer', 'Technology', 'senior', 'Technical leadership and architecture decisions', 'Computer Science degree, 5+ years development experience, leadership skills', 'medium', 1),
('Sales Manager', 'Sales', 'mid', 'Sales team leadership and revenue growth', 'Business degree, 3+ years sales experience, team management', 'low', 1);

SELECT 'Succession Planning database schema created successfully!' as message;

