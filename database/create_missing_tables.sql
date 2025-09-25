-- Create missing tables for HR1 system
USE hr1_system;

-- Create critical_positions table
CREATE TABLE IF NOT EXISTS critical_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_title VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    description TEXT,
    priority_level ENUM('critical', 'high', 'medium', 'low') DEFAULT 'medium',
    succession_timeline ENUM('0-6_months', '6-12_months', '1-2_years', '2-3_years', '3-4_years') DEFAULT '1-2_years',
    risk_level ENUM('high', 'medium', 'low') DEFAULT 'medium',
    current_incumbent_id INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_incumbent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create succession_candidates table
CREATE TABLE IF NOT EXISTS succession_candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_id INT NOT NULL,
    candidate_id INT NOT NULL,
    readiness_level ENUM('ready_now', 'ready_soon', 'development_needed', 'not_ready') DEFAULT 'development_needed',
    readiness_score DECIMAL(3,1) DEFAULT 0.0,
    development_plan TEXT,
    timeline_months INT DEFAULT 12,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES critical_positions(id) ON DELETE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create training_catalog table
CREATE TABLE IF NOT EXISTS training_catalog (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    type ENUM('in_person', 'virtual', 'hybrid', 'self_paced') DEFAULT 'in_person',
    duration_hours INT DEFAULT 1,
    max_participants INT DEFAULT 20,
    prerequisites TEXT,
    learning_objectives TEXT,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create training_sessions table
CREATE TABLE IF NOT EXISTS training_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    training_id INT NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(255),
    max_participants INT DEFAULT 20,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (training_id) REFERENCES training_catalog(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create training_enrollments table
CREATE TABLE IF NOT EXISTS training_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    employee_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    attendance_status ENUM('enrolled', 'attended', 'absent', 'dropped') DEFAULT 'enrolled',
    completion_status ENUM('not_started', 'in_progress', 'completed', 'failed') DEFAULT 'not_started',
    score INT,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES training_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (session_id, employee_id)
);

SELECT 'Missing tables created successfully!' as message;
