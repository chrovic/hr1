-- E-Commerce HR System Setup
-- This script updates the HR system for e-commerce business needs

USE hr1_system;

-- Update existing users with e-commerce roles
UPDATE users SET 
    department = 'E-Commerce Operations',
    position = 'E-Commerce Developer'
WHERE username = 'john.doe';

UPDATE users SET 
    department = 'Human Resources',
    position = 'HR Manager - E-Commerce'
WHERE username = 'jane.smith';

UPDATE users SET 
    department = 'IT & E-Commerce',
    position = 'E-Commerce System Administrator'
WHERE username = 'admin.test';

-- Insert e-commerce specific training modules
INSERT INTO training_modules (title, description, category, duration_hours, cost, status, created_by) VALUES

-- E-Commerce Core Skills
('E-Commerce Fundamentals', 'Introduction to online business, digital marketing, and customer experience', 'E-Commerce Core', 8, 500.00, 'active', 1),
('Digital Marketing & SEO', 'Search engine optimization, social media marketing, and digital advertising', 'Marketing', 12, 750.00, 'active', 1),
('Customer Service Excellence', 'Online customer support, chat systems, and customer retention strategies', 'Customer Service', 6, 400.00, 'active', 1),
('E-Commerce Analytics', 'Google Analytics, conversion tracking, and performance metrics', 'Analytics', 10, 600.00, 'active', 1),

-- Technical E-Commerce Skills
('Shopify Development', 'Building and customizing Shopify stores, themes, and apps', 'Technical', 16, 1200.00, 'active', 1),
('WooCommerce Management', 'WordPress e-commerce setup, plugins, and customization', 'Technical', 14, 1000.00, 'active', 1),
('Payment Gateway Integration', 'Stripe, PayPal, and other payment processing systems', 'Technical', 8, 800.00, 'active', 1),
('E-Commerce Security', 'SSL certificates, PCI compliance, and data protection', 'Security', 6, 500.00, 'active', 1),

-- Business & Operations
('Inventory Management', 'Stock control, supply chain, and fulfillment processes', 'Operations', 10, 600.00, 'active', 1),
('E-Commerce Law & Compliance', 'Consumer protection, data privacy, and e-commerce regulations', 'Legal', 8, 700.00, 'active', 1),
('Conversion Rate Optimization', 'A/B testing, user experience, and sales funnel optimization', 'Optimization', 12, 900.00, 'active', 1),
('Mobile Commerce', 'Mobile-first design, app development, and mobile payment systems', 'Mobile', 10, 800.00, 'active', 1);

-- Insert e-commerce specific skills
INSERT INTO skills_catalog (name, description, category, level, status) VALUES

-- E-Commerce Skills
('E-Commerce Platform Management', 'Managing online store platforms like Shopify, WooCommerce, Magento', 'E-Commerce', 'Intermediate', 'active'),
('Digital Marketing', 'Online advertising, social media marketing, email campaigns', 'Marketing', 'Intermediate', 'active'),
('Customer Experience Design', 'Creating seamless online customer journeys', 'UX/UI', 'Advanced', 'active'),
('E-Commerce Analytics', 'Tracking and analyzing online business performance', 'Analytics', 'Intermediate', 'active'),
('Payment Processing', 'Managing online payment systems and gateways', 'Technical', 'Intermediate', 'active'),
('Inventory Management', 'Stock control and supply chain management', 'Operations', 'Intermediate', 'active'),
('SEO & Content Marketing', 'Search engine optimization and content strategy', 'Marketing', 'Advanced', 'active'),
('Mobile Commerce', 'Mobile-first e-commerce development', 'Technical', 'Advanced', 'active'),
('E-Commerce Security', 'Online security and compliance', 'Security', 'Advanced', 'active'),
('Conversion Optimization', 'Improving website conversion rates', 'Optimization', 'Advanced', 'active');

-- Insert e-commerce specific certifications
INSERT INTO certifications_catalog (name, description, provider, validity_months, cost, status) VALUES

-- E-Commerce Certifications
('Google Analytics Certified', 'Google Analytics certification for e-commerce tracking', 'Google', 12, 0.00, 'active'),
('Shopify Partner Certification', 'Official Shopify development and management certification', 'Shopify', 24, 200.00, 'active'),
('WooCommerce Specialist', 'WordPress e-commerce specialization certification', 'WooCommerce', 18, 150.00, 'active'),
('Digital Marketing Professional', 'Comprehensive digital marketing certification', 'HubSpot', 12, 300.00, 'active'),
('E-Commerce Security Expert', 'E-commerce security and compliance certification', 'PCI Security Standards Council', 24, 500.00, 'active'),
('Conversion Rate Optimization', 'CRO specialist certification', 'CXL Institute', 12, 400.00, 'active'),
('Mobile Commerce Specialist', 'Mobile-first e-commerce development certification', 'Mobile Marketing Association', 18, 350.00, 'active'),
('E-Commerce Law & Compliance', 'Legal compliance for online businesses', 'E-Commerce Law Institute', 24, 600.00, 'active');

-- Insert e-commerce learning paths
INSERT INTO learning_paths (name, description, category, duration_weeks, difficulty_level, status) VALUES

-- E-Commerce Learning Paths
('E-Commerce Fundamentals Path', 'Complete introduction to e-commerce business', 'Foundation', 8, 'Beginner', 'active'),
('Digital Marketing Mastery', 'Comprehensive digital marketing for e-commerce', 'Marketing', 12, 'Intermediate', 'active'),
('E-Commerce Development', 'Technical skills for e-commerce platforms', 'Technical', 16, 'Advanced', 'active'),
('Customer Experience Excellence', 'Creating exceptional online customer experiences', 'Customer Service', 10, 'Intermediate', 'active'),
('E-Commerce Analytics & Optimization', 'Data-driven e-commerce optimization', 'Analytics', 14, 'Advanced', 'active'),
('Mobile Commerce Specialist', 'Mobile-first e-commerce development', 'Mobile', 12, 'Advanced', 'active'),
('E-Commerce Security & Compliance', 'Security and legal compliance for online businesses', 'Security', 8, 'Advanced', 'active'),
('E-Commerce Operations Management', 'End-to-end e-commerce operations', 'Operations', 16, 'Intermediate', 'active');

-- Insert e-commerce specific training sessions
INSERT INTO training_sessions (module_id, session_name, start_date, end_date, location, trainer_id, max_participants, status) VALUES

-- E-Commerce Fundamentals Sessions
(1, 'E-Commerce Fundamentals - Q1 2024', '2024-01-15', '2024-01-16', 'Online', 1, 20, 'active'),
(1, 'E-Commerce Fundamentals - Q2 2024', '2024-04-15', '2024-04-16', 'Online', 1, 20, 'active'),

-- Digital Marketing Sessions
(2, 'Digital Marketing & SEO - January 2024', '2024-01-22', '2024-01-24', 'Online', 1, 15, 'active'),
(2, 'Digital Marketing & SEO - March 2024', '2024-03-18', '2024-03-20', 'Online', 1, 15, 'active'),

-- Technical E-Commerce Sessions
(5, 'Shopify Development - February 2024', '2024-02-05', '2024-02-07', 'Online', 1, 12, 'active'),
(6, 'WooCommerce Management - February 2024', '2024-02-12', '2024-02-14', 'Online', 1, 12, 'active'),

-- Customer Service Sessions
(3, 'Customer Service Excellence - March 2024', '2024-03-05', '2024-03-06', 'Online', 1, 25, 'active'),

-- Analytics Sessions
(4, 'E-Commerce Analytics - April 2024', '2024-04-08', '2024-04-10', 'Online', 1, 18, 'active');

SELECT 'E-Commerce HR system setup completed successfully!' as message;


