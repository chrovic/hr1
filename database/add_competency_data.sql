-- Add E-Commerce Competency Data
-- This script adds comprehensive competency models and data for e-commerce businesses

USE hr1_system;

-- Insert E-Commerce Competency Models
INSERT INTO competency_models (name, description, category, target_roles, assessment_method, status, created_by) VALUES

-- E-Commerce Core Competencies
('E-Commerce Fundamentals', 'Core competencies for e-commerce professionals including online business understanding, digital customer service, and basic platform management', 'E-Commerce Core', '["employee", "manager"]', 'self_assessment', 'active', 1),

('Digital Marketing Excellence', 'Advanced digital marketing skills including SEO, social media marketing, email campaigns, and conversion optimization', 'Marketing', '["marketing_specialist", "manager", "employee"]', '360_feedback', 'active', 1),

('E-Commerce Technical Skills', 'Technical competencies for e-commerce platforms including Shopify, WooCommerce, payment gateways, and mobile commerce', 'Technical', '["developer", "technical_specialist", "manager"]', 'manager_review', 'active', 1),

('Customer Experience Management', 'Skills for creating exceptional online customer experiences, support systems, and retention strategies', 'Customer Service', '["customer_service", "manager", "employee"]', 'peer_review', 'active', 1),

('E-Commerce Analytics & Data', 'Competencies for data analysis, performance tracking, and business intelligence in e-commerce', 'Analytics', '["analyst", "manager", "specialist"]', 'manager_review', 'active', 1),

('E-Commerce Operations', 'Operational competencies including inventory management, supply chain, fulfillment, and process optimization', 'Operations', '["operations_manager", "employee", "specialist"]', 'self_assessment', 'active', 1),

('E-Commerce Security & Compliance', 'Security and compliance competencies for online businesses including PCI compliance, data protection, and legal requirements', 'Security', '["security_specialist", "manager", "compliance_officer"]', 'manager_review', 'active', 1),

('Mobile Commerce Expertise', 'Specialized competencies for mobile-first e-commerce including app development, mobile payments, and responsive design', 'Mobile', '["mobile_developer", "technical_specialist", "manager"]', 'peer_review', 'active', 1);

-- Insert Competencies for E-Commerce Fundamentals Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(1, 'Online Business Understanding', 'Understanding of e-commerce business models, online marketplaces, and digital commerce fundamentals', 1.00, 5),
(1, 'Digital Customer Service', 'Ability to provide excellent customer service through digital channels including chat, email, and social media', 1.20, 5),
(1, 'Platform Navigation', 'Proficiency in navigating and using e-commerce platforms and tools', 0.80, 5),
(1, 'Online Communication', 'Effective communication skills for digital environments and remote collaboration', 1.00, 5),
(1, 'Digital Literacy', 'Basic digital skills including file management, online tools, and digital security awareness', 0.90, 5);

-- Insert Competencies for Digital Marketing Excellence Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(2, 'SEO & Content Marketing', 'Search engine optimization and content creation skills for e-commerce', 1.30, 5),
(2, 'Social Media Marketing', 'Social media strategy, content creation, and community management', 1.20, 5),
(2, 'Email Marketing', 'Email campaign design, automation, and performance optimization', 1.10, 5),
(2, 'Conversion Rate Optimization', 'A/B testing, user experience optimization, and conversion improvement', 1.40, 5),
(2, 'Digital Advertising', 'Paid advertising across platforms including Google Ads, Facebook, and other channels', 1.25, 5),
(2, 'Analytics & Reporting', 'Data analysis, performance tracking, and marketing ROI measurement', 1.15, 5);

-- Insert Competencies for E-Commerce Technical Skills Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(3, 'Shopify Development', 'Shopify platform customization, theme development, and app integration', 1.50, 5),
(3, 'WooCommerce Management', 'WordPress e-commerce setup, plugin management, and customization', 1.40, 5),
(3, 'Payment Gateway Integration', 'Payment processing systems, security implementation, and transaction management', 1.30, 5),
(3, 'Mobile Commerce Development', 'Mobile-first design, responsive development, and mobile app integration', 1.35, 5),
(3, 'E-Commerce Security', 'SSL implementation, PCI compliance, and security best practices', 1.25, 5),
(3, 'Performance Optimization', 'Site speed optimization, caching, and technical performance improvement', 1.20, 5);

-- Insert Competencies for Customer Experience Management Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(4, 'Online Customer Support', 'Multi-channel customer support including chat, email, and phone', 1.30, 5),
(4, 'Customer Journey Mapping', 'Understanding and optimizing customer touchpoints and experiences', 1.20, 5),
(4, 'Retention Strategies', 'Customer loyalty programs, retention campaigns, and relationship building', 1.25, 5),
(4, 'Feedback Management', 'Collecting, analyzing, and acting on customer feedback', 1.15, 5),
(4, 'Crisis Communication', 'Handling customer complaints, negative reviews, and crisis situations', 1.10, 5);

-- Insert Competencies for E-Commerce Analytics & Data Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(5, 'Google Analytics', 'Advanced Google Analytics setup, tracking, and analysis', 1.40, 5),
(5, 'E-Commerce Metrics', 'Key performance indicators, conversion tracking, and business metrics', 1.35, 5),
(5, 'Data Visualization', 'Creating dashboards, reports, and visual representations of data', 1.20, 5),
(5, 'Business Intelligence', 'Data-driven decision making and strategic analysis', 1.30, 5),
(5, 'A/B Testing', 'Experimental design, statistical analysis, and optimization testing', 1.25, 5);

-- Insert Competencies for E-Commerce Operations Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(6, 'Inventory Management', 'Stock control, demand forecasting, and inventory optimization', 1.30, 5),
(6, 'Supply Chain Management', 'Vendor relationships, procurement, and supply chain optimization', 1.25, 5),
(6, 'Fulfillment Operations', 'Order processing, shipping, and delivery management', 1.20, 5),
(6, 'Process Optimization', 'Workflow improvement, automation, and operational efficiency', 1.15, 5),
(6, 'Quality Control', 'Product quality assurance and customer satisfaction monitoring', 1.10, 5);

-- Insert Competencies for E-Commerce Security & Compliance Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(7, 'PCI Compliance', 'Payment card industry compliance and security standards', 1.50, 5),
(7, 'Data Protection', 'GDPR, privacy laws, and data security implementation', 1.40, 5),
(7, 'E-Commerce Law', 'Online business regulations, consumer protection, and legal compliance', 1.35, 5),
(7, 'Security Monitoring', 'Threat detection, incident response, and security monitoring', 1.30, 5),
(7, 'Risk Assessment', 'Security risk evaluation and mitigation strategies', 1.25, 5);

-- Insert Competencies for Mobile Commerce Expertise Model
INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES
(8, 'Mobile App Development', 'Native and hybrid mobile app development for e-commerce', 1.50, 5),
(8, 'Responsive Design', 'Mobile-first design principles and responsive web development', 1.40, 5),
(8, 'Mobile Payments', 'Mobile payment systems, digital wallets, and mobile commerce', 1.35, 5),
(8, 'Mobile Analytics', 'Mobile-specific tracking, analytics, and performance measurement', 1.30, 5),
(8, 'Mobile UX/UI', 'Mobile user experience design and interface optimization', 1.25, 5);

-- Insert sample competency scores for existing users
INSERT INTO competency_scores (evaluation_id, competency_id, score, comments) VALUES

-- John Doe (Employee) - E-Commerce Fundamentals
(1, 1, 4.00, 'Good understanding of e-commerce basics, needs improvement in digital customer service'),
(1, 2, 3.50, 'Developing skills in digital customer service, shows potential'),
(1, 3, 4.50, 'Excellent platform navigation skills'),
(1, 4, 4.00, 'Good online communication skills'),
(1, 5, 3.00, 'Basic digital literacy, needs more training'),

-- John Doe - Digital Marketing Excellence
(1, 6, 3.00, 'Basic SEO knowledge, needs advanced training'),
(1, 7, 2.50, 'Limited social media marketing experience'),
(1, 8, 3.50, 'Good email marketing skills'),
(1, 9, 2.00, 'Needs CRO training'),
(1, 10, 3.00, 'Basic digital advertising knowledge'),
(1, 11, 3.50, 'Good analytics understanding'),

-- Jane Smith (HR Manager) - E-Commerce Fundamentals
(2, 1, 4.50, 'Excellent understanding of e-commerce business'),
(2, 2, 4.00, 'Good digital customer service skills'),
(2, 3, 4.00, 'Proficient in platform navigation'),
(2, 4, 4.50, 'Excellent online communication'),
(2, 5, 4.00, 'Good digital literacy'),

-- Admin Test - E-Commerce Technical Skills
(3, 12, 4.50, 'Advanced Shopify development skills'),
(3, 13, 4.00, 'Good WooCommerce management'),
(3, 14, 4.50, 'Expert in payment gateway integration'),
(3, 15, 4.00, 'Good mobile commerce development'),
(3, 16, 4.50, 'Excellent e-commerce security knowledge'),
(3, 17, 4.00, 'Good performance optimization skills');

-- Insert sample competency reports
INSERT INTO competency_reports (title, description, report_type, filters, created_by) VALUES

('E-Commerce Skills Assessment Q4 2024', 'Comprehensive assessment of e-commerce competencies across all departments', 'summary', '{"department": "all", "date_range": "2024-10-01 to 2024-12-31"}', 1),

('Digital Marketing Competency Analysis', 'Detailed analysis of digital marketing skills and gaps', 'detailed', '{"competency_model": "Digital Marketing Excellence", "department": "Marketing"}', 1),

('Technical Skills Trend Report', 'Trend analysis of technical e-commerce skills over time', 'trend', '{"competency_model": "E-Commerce Technical Skills", "period": "6_months"}', 1),

('Department Competency Comparison', 'Comparative analysis of competencies across different departments', 'comparison', '{"departments": ["IT", "Marketing", "Operations"], "competency_models": "all"}', 1);

SELECT 'E-Commerce competency data added successfully!' as message;

