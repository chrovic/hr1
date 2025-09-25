-- Create Test Accounts for HR1 System
-- This script creates 3 test accounts: Employee, Admin, and HR Manager

USE hr1_system;

-- Insert test accounts
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, employee_id, department, position, hire_date, phone) VALUES

-- Employee Account
('john.doe', 'john.doe@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'employee', 'active', 'EMP001', 'IT', 'Software Developer', '2023-01-15', '555-0101'),

-- HR Manager Account
('jane.smith', 'jane.smith@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Smith', 'hr_manager', 'active', 'HRM001', 'Human Resources', 'HR Manager', '2022-06-01', '555-0102'),

-- Admin Account
('admin.test', 'admin.test@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'Test', 'admin', 'active', 'ADM002', 'IT', 'System Administrator', '2022-01-01', '555-0103');

-- Update existing admin account details
UPDATE users SET 
    first_name = 'System',
    last_name = 'Administrator',
    email = 'system.admin@company.com',
    employee_id = 'ADM001',
    department = 'IT',
    position = 'System Administrator',
    hire_date = '2022-01-01',
    phone = '555-0000'
WHERE username = 'admin';

-- Show created accounts
SELECT 
    id,
    username,
    email,
    first_name,
    last_name,
    role,
    status,
    employee_id,
    department,
    position,
    hire_date,
    phone
FROM users 
ORDER BY role, username;

SELECT 'Test accounts created successfully!' as message;


