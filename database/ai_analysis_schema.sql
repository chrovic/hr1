-- AI Analysis Database Schema for HR1 System
-- This file contains the database structure for storing AI analysis results

USE hr1_system;

-- AI Analysis Results table
CREATE TABLE IF NOT EXISTS ai_analysis_results (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evaluation_id INT NOT NULL,
    analysis_type ENUM('competency_feedback', 'training_feedback', 'performance_review') NOT NULL,
    sentiment ENUM('positive', 'negative', 'neutral') NOT NULL,
    sentiment_confidence DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    summary TEXT,
    original_length INT DEFAULT 0,
    summary_length INT DEFAULT 0,
    compression_ratio DECIMAL(3,2) DEFAULT 0.00,
    analysis_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    UNIQUE KEY unique_evaluation_analysis (evaluation_id, analysis_type)
);

-- AI Analysis Context table - provides context for AI analysis
CREATE TABLE IF NOT EXISTS ai_analysis_context (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evaluation_id INT NOT NULL,
    employee_profile JSON COMMENT 'Employee background, role, department, experience',
    evaluator_profile JSON COMMENT 'Evaluator background, relationship to employee',
    evaluation_context JSON COMMENT 'Evaluation cycle, model, competencies being evaluated',
    performance_history JSON COMMENT 'Historical performance data, previous evaluations',
    organizational_context JSON COMMENT 'Department goals, company values, strategic objectives',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE
);

-- AI Analysis Insights table - stores derived insights from analysis
CREATE TABLE IF NOT EXISTS ai_analysis_insights (
    id INT PRIMARY KEY AUTO_INCREMENT,
    evaluation_id INT NOT NULL,
    insight_type ENUM('strength', 'improvement_area', 'development_opportunity', 'risk', 'recommendation') NOT NULL,
    insight_text TEXT NOT NULL,
    confidence_score DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    competency_id INT,
    priority_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    actionable BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluation_id) REFERENCES evaluations(id) ON DELETE CASCADE,
    FOREIGN KEY (competency_id) REFERENCES competencies(id) ON DELETE SET NULL
);

-- AI Analysis Patterns table - tracks patterns across evaluations
CREATE TABLE IF NOT EXISTS ai_analysis_patterns (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pattern_type ENUM('sentiment_trend', 'competency_gap', 'evaluator_bias', 'department_pattern') NOT NULL,
    pattern_description TEXT NOT NULL,
    affected_employees JSON,
    affected_departments JSON,
    confidence_score DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    severity_level ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    recommendations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- AI Model Performance tracking
CREATE TABLE IF NOT EXISTS ai_model_performance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    analysis_type VARCHAR(50) NOT NULL,
    accuracy_score DECIMAL(3,2),
    processing_time_ms INT,
    success_rate DECIMAL(3,2),
    error_count INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_model_type (model_name, analysis_type)
);

-- Insert sample context data for existing evaluations
INSERT INTO ai_analysis_context (evaluation_id, employee_profile, evaluator_profile, evaluation_context, performance_history, organizational_context)
SELECT 
    e.id,
    JSON_OBJECT(
        'employee_id', e.employee_id,
        'name', CONCAT(emp.first_name, ' ', emp.last_name),
        'position', emp.position,
        'department', emp.department,
        'hire_date', emp.hire_date,
        'experience_years', TIMESTAMPDIFF(YEAR, emp.hire_date, NOW()),
        'role_level', CASE 
            WHEN emp.position LIKE '%Manager%' THEN 'management'
            WHEN emp.position LIKE '%Senior%' THEN 'senior'
            WHEN emp.position LIKE '%Lead%' THEN 'lead'
            ELSE 'individual_contributor'
        END
    ),
    JSON_OBJECT(
        'evaluator_id', e.evaluator_id,
        'name', CONCAT(eval.first_name, ' ', eval.last_name),
        'position', eval.position,
        'department', eval.department,
        'relationship', CASE 
            WHEN eval.position LIKE '%Manager%' THEN 'direct_manager'
            WHEN eval.position LIKE '%HR%' THEN 'hr_representative'
            ELSE 'peer'
        END
    ),
    JSON_OBJECT(
        'cycle_name', ec.name,
        'cycle_type', ec.type,
        'model_name', cm.name,
        'evaluation_period', CONCAT(ec.start_date, ' to ', ec.end_date),
        'competencies_count', (SELECT COUNT(*) FROM competencies WHERE model_id = e.model_id)
    ),
    JSON_OBJECT(
        'previous_evaluations', (
            SELECT JSON_ARRAYAGG(
                JSON_OBJECT(
                    'evaluation_id', prev_eval.id,
                    'overall_score', prev_eval.overall_score,
                    'completed_at', prev_eval.completed_at,
                    'cycle_name', prev_ec.name
                )
            )
            FROM evaluations prev_eval
            JOIN evaluation_cycles prev_ec ON prev_eval.cycle_id = prev_ec.id
            WHERE prev_eval.employee_id = e.employee_id 
            AND prev_eval.id != e.id
            AND prev_eval.status = 'completed'
            ORDER BY prev_eval.completed_at DESC
            LIMIT 3
        ),
        'average_historical_score', (
            SELECT AVG(prev_eval.overall_score)
            FROM evaluations prev_eval
            WHERE prev_eval.employee_id = e.employee_id 
            AND prev_eval.id != e.id
            AND prev_eval.status = 'completed'
        )
    ),
    JSON_OBJECT(
        'company_values', JSON_ARRAY('Excellence', 'Innovation', 'Collaboration', 'Integrity'),
        'department_goals', JSON_OBJECT(
            'productivity', 'Increase team productivity by 15%',
            'quality', 'Maintain 95% quality standards',
            'innovation', 'Implement 2 new process improvements'
        ),
        'strategic_objectives', JSON_ARRAY(
            'Digital transformation',
            'Customer satisfaction improvement',
            'Operational efficiency'
        )
    )
FROM evaluations e
JOIN users emp ON e.employee_id = emp.id
JOIN users eval ON e.evaluator_id = eval.id
JOIN evaluation_cycles ec ON e.cycle_id = ec.id
JOIN competency_models cm ON e.model_id = cm.id
WHERE e.status = 'completed'
ON DUPLICATE KEY UPDATE
employee_profile = VALUES(employee_profile),
evaluator_profile = VALUES(evaluator_profile),
evaluation_context = VALUES(evaluation_context),
performance_history = VALUES(performance_history),
organizational_context = VALUES(organizational_context),
updated_at = NOW();

-- Create indexes for better performance
CREATE INDEX idx_ai_analysis_evaluation ON ai_analysis_results(evaluation_id);
CREATE INDEX idx_ai_analysis_type ON ai_analysis_results(analysis_type);
CREATE INDEX idx_ai_analysis_sentiment ON ai_analysis_results(sentiment);
CREATE INDEX idx_ai_analysis_created ON ai_analysis_results(created_at);

CREATE INDEX idx_ai_context_evaluation ON ai_analysis_context(evaluation_id);
CREATE INDEX idx_ai_insights_evaluation ON ai_analysis_insights(evaluation_id);
CREATE INDEX idx_ai_insights_type ON ai_analysis_insights(insight_type);
CREATE INDEX idx_ai_patterns_type ON ai_analysis_patterns(pattern_type);

SELECT 'AI Analysis database schema created successfully!' as message;

