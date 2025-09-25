-- Update Competency Models for E-Commerce Company
-- This script updates existing competency models to be more relevant for e-commerce operations

USE hr1_system;

-- Update existing competency models with e-commerce focused names and descriptions
UPDATE competency_models SET 
    name = 'E-Commerce Operations Model',
    description = 'Core competencies for e-commerce operations including order processing, inventory management, and customer service excellence.',
    target_roles = '["warehouse_manager", "order_processor", "inventory_specialist", "customer_service_rep"]'
WHERE id = 1;

UPDATE competency_models SET 
    name = 'Digital Marketing & Sales Model',
    description = 'Essential skills for digital marketing, online sales, social media management, and customer acquisition.',
    target_roles = '["digital_marketer", "sales_rep", "social_media_manager", "seo_specialist"]'
WHERE id = 2;

UPDATE competency_models SET 
    name = 'Customer Experience Excellence Model',
    description = 'Skills focused on delivering exceptional customer experiences, handling inquiries, and building customer loyalty.',
    target_roles = '["customer_service_rep", "customer_success_manager", "support_specialist"]'
WHERE id = 3;

UPDATE competency_models SET 
    name = 'E-Commerce Technology Model',
    description = 'Technical competencies for managing e-commerce platforms, payment systems, and digital infrastructure.',
    target_roles = '["ecommerce_developer", "payment_specialist", "platform_manager", "tech_support"]'
WHERE id = 4;

UPDATE competency_models SET 
    name = 'Supply Chain & Logistics Model',
    description = 'Expertise in supply chain management, logistics coordination, vendor relationships, and fulfillment operations.',
    target_roles = '["supply_chain_manager", "logistics_coordinator", "vendor_manager", "fulfillment_specialist"]'
WHERE id = 5;

-- Add new e-commerce specific competency models
INSERT INTO competency_models (name, description, status, target_roles, created_at) VALUES
('E-Commerce Leadership Model', 'Leadership skills for managing e-commerce teams, driving growth, and strategic decision-making.', 'active', '["team_lead", "department_manager", "operations_manager", "ceo"]', NOW()),

('Data Analytics & Insights Model', 'Skills for analyzing e-commerce data, generating insights, and making data-driven decisions.', 'active', '["data_analyst", "business_intelligence", "reporting_specialist", "insights_manager"]', NOW()),

('Product Management Model', 'Competencies for product development, catalog management, and product lifecycle in e-commerce.', 'active', '["product_manager", "catalog_manager", "merchandiser", "product_specialist"]', NOW());

-- Update competencies to be e-commerce focused
UPDATE competencies SET 
    name = 'Order Processing Efficiency',
    description = 'Ability to process customer orders accurately and efficiently',
    assessment_method = 'performance_review'
WHERE id = 1;

UPDATE competencies SET 
    name = 'Customer Service Excellence',
    description = 'Skills in providing outstanding customer service and resolving issues',
    assessment_method = 'customer_feedback'
WHERE id = 2;

UPDATE competencies SET 
    name = 'Digital Marketing Proficiency',
    description = 'Competency in digital marketing strategies and execution',
    assessment_method = 'project_assessment'
WHERE id = 3;

UPDATE competencies SET 
    name = 'E-Commerce Platform Management',
    description = 'Technical skills for managing e-commerce platforms and systems',
    assessment_method = 'technical_assessment'
WHERE id = 4;

UPDATE competencies SET 
    name = 'Inventory Management',
    description = 'Ability to effectively manage inventory levels and stock rotation',
    assessment_method = 'performance_review'
WHERE id = 5;

-- Add new e-commerce specific competencies
INSERT INTO competencies (model_id, name, description, weight, assessment_method, created_at) VALUES
(1, 'Order Fulfillment Speed', 'Ability to fulfill orders quickly and accurately', 25, 'performance_review', NOW()),
(1, 'Quality Control', 'Skills in maintaining product quality standards', 20, 'quality_audit', NOW()),
(1, 'Process Optimization', 'Ability to identify and implement process improvements', 15, 'project_assessment', NOW()),

(2, 'Social Media Marketing', 'Competency in social media strategy and execution', 30, 'campaign_review', NOW()),
(2, 'SEO & SEM', 'Skills in search engine optimization and marketing', 25, 'performance_review', NOW()),
(2, 'Conversion Rate Optimization', 'Ability to improve website conversion rates', 20, 'analytics_review', NOW()),

(3, 'Customer Retention', 'Skills in building customer loyalty and retention', 35, 'customer_metrics', NOW()),
(3, 'Issue Resolution', 'Ability to quickly and effectively resolve customer issues', 30, 'customer_feedback', NOW()),
(3, 'Communication Skills', 'Excellent verbal and written communication abilities', 25, 'peer_review', NOW()),

(4, 'Payment Gateway Management', 'Technical skills for managing payment processing systems', 30, 'technical_assessment', NOW()),
(4, 'Platform Integration', 'Ability to integrate various e-commerce tools and platforms', 25, 'project_assessment', NOW()),
(4, 'Security & Compliance', 'Knowledge of e-commerce security and compliance requirements', 20, 'certification', NOW()),

(5, 'Vendor Management', 'Skills in managing supplier relationships and negotiations', 30, 'performance_review', NOW()),
(5, 'Logistics Coordination', 'Ability to coordinate shipping and delivery operations', 25, 'operational_review', NOW()),
(5, 'Cost Optimization', 'Skills in optimizing supply chain costs', 20, 'financial_review', NOW());

SELECT 'E-Commerce competency models updated successfully!' as message;





