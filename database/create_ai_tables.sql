-- Create AI Integration Tables
-- These tables support the AI integration functionality

USE hr1_system;

-- AI Analysis Log table
CREATE TABLE IF NOT EXISTS ai_analysis_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    analysis_type ENUM('sentiment', 'summarization', 'competency_gap') NOT NULL,
    input_text TEXT,
    result TEXT,
    confidence DECIMAL(3,2),
    analysis_method VARCHAR(50) DEFAULT 'placeholder',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- AI Recommendation Log table
CREATE TABLE IF NOT EXISTS ai_recommendation_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT NOT NULL,
    recommendation_type ENUM('training', 'development', 'career') NOT NULL,
    recommendations JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id)
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_ai_analysis_type ON ai_analysis_log(analysis_type);
CREATE INDEX IF NOT EXISTS idx_ai_analysis_created ON ai_analysis_log(created_at);
CREATE INDEX IF NOT EXISTS idx_ai_recommendation_employee ON ai_recommendation_log(employee_id);
CREATE INDEX IF NOT EXISTS idx_ai_recommendation_type ON ai_recommendation_log(recommendation_type);

-- Insert some sample AI analysis data
INSERT INTO ai_analysis_log (analysis_type, input_text, result, confidence, analysis_method) VALUES
('sentiment', 'Excellent work on the project! Great communication and leadership skills.', 'Positive', 0.85, 'rule_based_placeholder'),
('sentiment', 'The performance needs improvement in several areas.', 'Negative', 0.78, 'rule_based_placeholder'),
('summarization', 'This is a comprehensive evaluation covering technical skills, communication abilities, leadership potential, and overall performance. The employee shows strong technical capabilities but needs development in soft skills.', 'Strong technical skills, needs soft skills development', 0.82, 'extraction_placeholder'),
('competency_gap', 'Leadership, Communication, Technical Skills', 'Leadership: 3/5, Communication: 4/5, Technical: 5/5', 0.75, 'keyword_matching_placeholder');

-- Insert some sample AI recommendation data
INSERT INTO ai_recommendation_log (employee_id, recommendation_type, recommendations) VALUES
(3, 'training', '["Leadership Fundamentals", "Communication Skills Workshop", "Advanced Technical Training"]'),
(4, 'development', '["Mentoring Program", "Cross-functional Projects", "Leadership Development"]'),
(5, 'career', '["Senior Role Preparation", "Management Training", "Strategic Thinking Course"]');

SELECT 'AI Integration tables created successfully!' as message;





