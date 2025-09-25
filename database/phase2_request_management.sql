-- Phase 2: Request Management System Database Schema
-- This file contains the complete database structure for employee request management

USE hr1_system;

-- Request types table
CREATE TABLE request_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    requires_approval BOOLEAN DEFAULT TRUE,
    approval_workflow TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Employee requests table
CREATE TABLE employee_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    request_type_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    requested_date DATE,
    requested_start_date DATE,
    requested_end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id),
    FOREIGN KEY (request_type_id) REFERENCES request_types(id)
);

-- Request approvals table
CREATE TABLE request_approvals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    approver_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    comments TEXT,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES users(id)
);

-- Request attachments table
CREATE TABLE request_attachments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    uploaded_by INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Request comments table
CREATE TABLE request_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Request notifications table
CREATE TABLE request_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    user_id INT NOT NULL,
    notification_type ENUM('created', 'approved', 'rejected', 'cancelled', 'comment') NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default request types
INSERT INTO request_types (name, description, requires_approval, approval_workflow) VALUES
('Training Request', 'Request to attend training or professional development', TRUE, 'Manager approval required'),
('Leave Request', 'Request for time off or vacation', TRUE, 'Manager approval required'),
('Equipment Request', 'Request for equipment or tools', TRUE, 'IT/Admin approval required'),
('Schedule Adjustment', 'Request for schedule changes', TRUE, 'Manager approval required'),
('Professional Development', 'Request for conferences, courses, or certifications', TRUE, 'Manager and HR approval required'),
('Work From Home', 'Request for remote work arrangement', TRUE, 'Manager approval required'),
('Travel Request', 'Request for business travel', TRUE, 'Manager approval required'),
('Expense Reimbursement', 'Request for expense reimbursement', TRUE, 'Manager approval required'),
('General Request', 'General request or inquiry', FALSE, 'No approval required');

-- Create indexes for better performance
CREATE INDEX idx_employee_requests_employee_id ON employee_requests(employee_id);
CREATE INDEX idx_employee_requests_status ON employee_requests(status);
CREATE INDEX idx_employee_requests_created_at ON employee_requests(created_at);
CREATE INDEX idx_request_approvals_request_id ON request_approvals(request_id);
CREATE INDEX idx_request_approvals_approver_id ON request_approvals(approver_id);
CREATE INDEX idx_request_notifications_user_id ON request_notifications(user_id);
CREATE INDEX idx_request_notifications_is_read ON request_notifications(is_read);

SELECT 'Request Management database schema created successfully!' as message;

