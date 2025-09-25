-- Update Training Costs to Philippine Peso
-- This script converts all training costs from USD to PHP (Philippine Peso)

USE hr1_system;

-- Update training modules costs to Philippine Peso
-- Using approximate exchange rate: 1 USD = 56 PHP

UPDATE training_modules SET cost = 67200.00 WHERE title = 'Shopify Development'; -- $1,200 = ₱67,200
UPDATE training_modules SET cost = 56000.00 WHERE title = 'WooCommerce Management'; -- $1,000 = ₱56,000
UPDATE training_modules SET cost = 50400.00 WHERE title = 'Conversion Rate Optimization'; -- $900 = ₱50,400
UPDATE training_modules SET cost = 44800.00 WHERE title = 'Payment Gateway Integration'; -- $800 = ₱44,800
UPDATE training_modules SET cost = 44800.00 WHERE title = 'Mobile Commerce'; -- $800 = ₱44,800
UPDATE training_modules SET cost = 42000.00 WHERE title = 'Digital Marketing & SEO'; -- $750 = ₱42,000
UPDATE training_modules SET cost = 39200.00 WHERE title = 'E-Commerce Law & Compliance'; -- $700 = ₱39,200
UPDATE training_modules SET cost = 33600.00 WHERE title = 'E-Commerce Analytics'; -- $600 = ₱33,600
UPDATE training_modules SET cost = 33600.00 WHERE title = 'Inventory Management'; -- $600 = ₱33,600
UPDATE training_modules SET cost = 28000.00 WHERE title = 'E-Commerce Fundamentals'; -- $500 = ₱28,000
UPDATE training_modules SET cost = 28000.00 WHERE title = 'E-Commerce Security'; -- $500 = ₱28,000
UPDATE training_modules SET cost = 22400.00 WHERE title = 'Customer Service Excellence'; -- $400 = ₱22,400

-- Update certifications costs to Philippine Peso
UPDATE certifications_catalog SET cost = 33600.00 WHERE name = 'E-Commerce Law & Compliance'; -- $600 = ₱33,600
UPDATE certifications_catalog SET cost = 28000.00 WHERE name = 'E-Commerce Security Expert'; -- $500 = ₱28,000
UPDATE certifications_catalog SET cost = 22400.00 WHERE name = 'Conversion Rate Optimization'; -- $400 = ₱22,400
UPDATE certifications_catalog SET cost = 19600.00 WHERE name = 'Mobile Commerce Specialist'; -- $350 = ₱19,600
UPDATE certifications_catalog SET cost = 16800.00 WHERE name = 'Digital Marketing Professional'; -- $300 = ₱16,800
UPDATE certifications_catalog SET cost = 11200.00 WHERE name = 'Shopify Partner Certification'; -- $200 = ₱11,200
UPDATE certifications_catalog SET cost = 8400.00 WHERE name = 'WooCommerce Specialist'; -- $150 = ₱8,400
-- Google Analytics Certified remains free (₱0.00)

SELECT 'Training costs updated to Philippine Peso successfully!' as message;

