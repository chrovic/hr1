-- ==============================================
-- HR1 to HR2 Data Migration Script
-- ==============================================
-- This script migrates data from HR1 legacy tables to HR2 system
-- Run this after creating the database with HR1_Complete_Schema.sql

USE hr1_system;

-- ==============================================
-- MIGRATION 1: Applicants → Competency System
-- ==============================================

-- Create competency models based on job roles
INSERT INTO competency_models (name, description, category, target_roles, assessment_method, status, created_by)
SELECT
    CONCAT(title, ' Competency Model') as name,
    CONCAT('Competency model for ', title, ' role based on job requirements') as description,
    CASE
        WHEN department = 'Development' THEN 'Technical'
        WHEN department = 'Human Resources' THEN 'Soft Skills'
        WHEN department = 'Design' THEN 'Creative'
        ELSE 'General'
    END as category,
    JSON_ARRAY(title) as target_roles,
    CASE
        WHEN department = 'Development' THEN 'manager_review'
        WHEN department = 'Human Resources' THEN '360_feedback'
        ELSE 'self_assessment'
    END as assessment_method,
    'active' as status,
    1 as created_by
FROM job_roles
WHERE status = 'active';

-- Create competencies based on required skills from job roles
INSERT INTO competencies (model_id, name, description, weight, max_score)
SELECT
    cm.id as model_id,
    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(jr.required_skills, ',', numbers.n), ',', -1)) as name,
    CONCAT('Required skill for ', jr.title, ' role') as description,
    1.00 as weight,
    5 as max_score
FROM job_roles jr
JOIN competency_models cm ON cm.name = CONCAT(jr.title, ' Competency Model')
JOIN (
    SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
) numbers ON CHAR_LENGTH(jr.required_skills) - CHAR_LENGTH(REPLACE(jr.required_skills, ',', '')) >= numbers.n - 1
WHERE jr.status = 'active'
AND TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(jr.required_skills, ',', numbers.n), ',', -1)) != '';

-- ==============================================
-- MIGRATION 2: Interview Results → Competency Evaluations
-- ==============================================

-- Create evaluation cycles for interview periods
INSERT INTO evaluation_cycles (name, type, start_date, end_date, status, created_by)
SELECT
    'Hiring Evaluation Cycle Q1 2024' as name,
    'quarterly' as type,
    '2024-01-01' as start_date,
    '2024-03-31' as end_date,
    'completed' as status,
    1 as created_by
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM evaluation_cycles WHERE name = 'Hiring Evaluation Cycle Q1 2024');

-- Get the evaluation cycle ID
SET @cycle_id = (SELECT id FROM evaluation_cycles WHERE name = 'Hiring Evaluation Cycle Q1 2024' LIMIT 1);

-- Create evaluations for hired applicants
INSERT INTO evaluations (cycle_id, employee_id, evaluator_id, model_id, status, overall_score, created_at, completed_at)
SELECT
    @cycle_id as cycle_id,
    u.id as employee_id,
    2 as evaluator_id, -- HR Manager
    cm.id as model_id,
    'completed' as status,
    ir.rating as overall_score,
    ir.interview_date as created_at,
    ir.interview_date as completed_at
FROM interview_results ir
JOIN applicants a ON ir.applicant_id = a.id
JOIN users u ON u.first_name = SUBSTRING_INDEX(a.name, ' ', 1) AND u.last_name = SUBSTRING_INDEX(a.name, ' ', -1)
JOIN job_roles jr ON ir.job_role_id = jr.id
JOIN competency_models cm ON cm.name = CONCAT(jr.title, ' Competency Model')
WHERE a.status = 'hired';

-- Create competency scores from interview results
INSERT INTO competency_scores (evaluation_id, competency_id, score, comments, created_at)
SELECT
    e.id as evaluation_id,
    c.id as competency_id,
    CASE
        WHEN c.name LIKE '%Technical%' OR c.name LIKE '%Programming%' OR c.name LIKE '%Code%' THEN ir.technical_rating
        WHEN c.name LIKE '%Communication%' THEN ir.communication_rating
        WHEN c.name LIKE '%Leadership%' THEN ir.leadership_rating
        ELSE ir.rating * 0.8 -- Default score based on overall rating
    END as score,
    ir.feedback as comments,
    ir.interview_date as created_at
FROM interview_results ir
JOIN applicants a ON ir.applicant_id = a.id
JOIN users u ON u.first_name = SUBSTRING_INDEX(a.name, ' ', 1) AND u.last_name = SUBSTRING_INDEX(a.name, ' ', -1)
JOIN job_roles jr ON ir.job_role_id = jr.id
JOIN competency_models cm ON cm.name = CONCAT(jr.title, ' Competency Model')
JOIN evaluations e ON e.employee_id = u.id AND e.cycle_id = @cycle_id
JOIN competencies c ON c.model_id = cm.id
WHERE a.status = 'hired';

-- ==============================================
-- MIGRATION 3: Performance Reviews → Competency System
-- ==============================================

-- Create evaluation cycles for performance reviews
INSERT INTO evaluation_cycles (name, type, start_date, end_date, status, created_by)
SELECT
    'Performance Review Q4 2023' as name,
    'quarterly' as type,
    '2023-10-01' as start_date,
    '2023-12-31' as end_date,
    'completed' as status,
    1 as created_by
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM evaluation_cycles WHERE name = 'Performance Review Q4 2023');

SET @perf_cycle_id = (SELECT id FROM evaluation_cycles WHERE name = 'Performance Review Q4 2023' LIMIT 1);

-- Create evaluations from performance reviews
INSERT INTO evaluations (cycle_id, employee_id, evaluator_id, model_id, status, overall_score, created_at, completed_at)
SELECT
    @perf_cycle_id as cycle_id,
    pr.employee_id,
    pr.reviewer_id,
    cm.id as model_id,
    'completed' as status,
    pr.probation_score as overall_score,
    pr.review_date as created_at,
    pr.review_date as completed_at
FROM performance_reviews pr
JOIN users u ON pr.employee_id = u.id
JOIN competency_models cm ON cm.category = CASE
    WHEN u.department = 'Development' THEN 'Technical'
    WHEN u.department = 'Human Resources' THEN 'Soft Skills'
    ELSE 'General'
END
WHERE pr.status = 'completed';

-- Create competency scores from performance review scores
INSERT INTO competency_scores (evaluation_id, competency_id, score, comments, created_at)
SELECT
    e.id as evaluation_id,
    c.id as competency_id,
    CASE
        WHEN c.name LIKE '%Technical%' THEN pr.technical_score
        WHEN c.name LIKE '%Communication%' THEN pr.communication_score
        WHEN c.name LIKE '%Leadership%' THEN pr.leadership_score
        ELSE pr.probation_score * 0.8
    END as score,
    pr.evaluator_feedback as comments,
    pr.review_date as created_at
FROM performance_reviews pr
JOIN evaluations e ON e.employee_id = pr.employee_id AND e.cycle_id = @perf_cycle_id
JOIN competencies c ON c.model_id = e.model_id
WHERE pr.status = 'completed';

-- ==============================================
-- MIGRATION 4: Job Roles → Training Modules
-- ==============================================

-- Create training modules based on job roles
INSERT INTO training_modules (title, description, category, duration_hours, difficulty_level, prerequisites, learning_objectives, status, created_by)
SELECT
    CONCAT('Advanced ', title, ' Training') as title,
    CONCAT('Comprehensive training program for ', title, ' role covering essential skills and best practices') as description,
    CASE
        WHEN department = 'Development' THEN 'Technical'
        WHEN department = 'Human Resources' THEN 'Management'
        WHEN department = 'Design' THEN 'Creative'
        ELSE 'General'
    END as category,
    CASE
        WHEN department = 'Development' THEN 40
        WHEN department = 'Human Resources' THEN 24
        WHEN department = 'Design' THEN 32
        ELSE 16
    END as duration_hours,
    CASE
        WHEN min_experience_years >= 5 THEN 'advanced'
        WHEN min_experience_years >= 3 THEN 'intermediate'
        ELSE 'beginner'
    END as difficulty_level,
    CASE
        WHEN min_experience_years > 0 THEN CONCAT(min_experience_years, ' years of experience recommended')
        ELSE 'No prerequisites required'
    END as prerequisites,
    CONCAT('Master key skills required for ', title, ' role including: ', required_skills) as learning_objectives,
    'active' as status,
    1 as created_by
FROM job_roles
WHERE status = 'active';

-- ==============================================
-- MIGRATION 5: Recognition → Succession Candidates
-- ==============================================

-- Create critical roles based on high-performing positions
INSERT INTO critical_roles (position_title, department, level, description, requirements, risk_level, current_incumbent_id, created_by)
SELECT
    u.position as position_title,
    u.department,
    CASE
        WHEN u.position LIKE '%Manager%' THEN 'manager'
        WHEN u.position LIKE '%Lead%' THEN 'senior'
        WHEN u.position LIKE '%Director%' THEN 'executive'
        ELSE 'individual_contributor'
    END as level,
    CONCAT('Critical role for ', u.position, ' in ', u.department, ' department') as description,
    'High performance, leadership skills, technical expertise' as requirements,
    CASE
        WHEN COUNT(r.id) > 2 THEN 'high'
        WHEN COUNT(r.id) > 0 THEN 'medium'
        ELSE 'low'
    END as risk_level,
    u.id as current_incumbent_id,
    1 as created_by
FROM users u
LEFT JOIN recognition r ON u.id = r.employee_id
WHERE u.role = 'employee' AND u.status = 'active'
GROUP BY u.id, u.position, u.department
HAVING COUNT(r.id) > 0;

-- Create succession candidates based on recognition and performance
INSERT INTO succession_candidates (role_id, employee_id, readiness_level, development_plan, notes, assessment_date)
SELECT
    cr.id as role_id,
    CASE
        WHEN r.recognized_by != r.employee_id THEN r.recognized_by
        ELSE (SELECT id FROM users WHERE role = 'employee' AND id != r.employee_id LIMIT 1)
    END as employee_id,
    CASE
        WHEN pr.probation_score >= 85 THEN 'ready_now'
        WHEN pr.probation_score >= 75 THEN 'ready_soon'
        ELSE 'development_needed'
    END as readiness_level,
    CASE
        WHEN pr.probation_score < 85 THEN 'Complete additional training and gain more experience'
        ELSE 'Ready for advancement with minimal additional development'
    END as development_plan,
    r.reason as notes,
    r.recognition_date as assessment_date
FROM recognition r
JOIN users u ON r.employee_id = u.id
JOIN critical_roles cr ON cr.current_incumbent_id = u.id
LEFT JOIN performance_reviews pr ON pr.employee_id = u.id
WHERE r.status = 'active'
GROUP BY r.id;

-- ==============================================
-- MIGRATION 6: Onboarding → Training Enrollments
-- ==============================================

-- Create training sessions for onboarding tasks
INSERT INTO training_sessions (module_id, session_name, description, trainer_id, start_date, end_date, location, max_participants, status, created_by)
SELECT
    tm.id as module_id,
    o.task as session_name,
    o.description,
    o.assigned_by as trainer_id,
    o.due_date as start_date,
    DATE_ADD(o.due_date, INTERVAL 1 DAY) as end_date,
    'Online' as location,
    1 as max_participants,
    CASE
        WHEN o.status = 'completed' THEN 'completed'
        WHEN o.status = 'in_progress' THEN 'active'
        ELSE 'scheduled'
    END as status,
    o.assigned_by as created_by
FROM onboarding o
JOIN training_modules tm ON tm.title LIKE CONCAT('%', o.task, '%')
WHERE o.status IN ('pending', 'in_progress', 'completed');

-- Create training enrollments from onboarding tasks
INSERT INTO training_enrollments (session_id, employee_id, enrollment_date, status, completion_date)
SELECT
    ts.id as session_id,
    o.employee_id,
    o.created_at as enrollment_date,
    CASE
        WHEN o.status = 'completed' THEN 'completed'
        WHEN o.status = 'in_progress' THEN 'attended'
        ELSE 'enrolled'
    END as status,
    CASE
        WHEN o.status = 'completed' THEN o.completed_date
        ELSE NULL
    END as completion_date
FROM onboarding o
JOIN training_sessions ts ON ts.session_name = o.task AND ts.start_date = o.due_date;

-- ==============================================
-- MIGRATION 7: Create Training Requests from Onboarding
-- ==============================================

INSERT INTO training_requests (employee_id, module_id, request_date, status, approved_by, approved_at)
SELECT
    o.employee_id,
    tm.id as module_id,
    o.created_at as request_date,
    CASE
        WHEN o.status = 'completed' THEN 'approved'
        ELSE 'pending'
    END as status,
    o.assigned_by as approved_by,
    CASE
        WHEN o.status = 'completed' THEN o.created_at
        ELSE NULL
    END as approved_at
FROM onboarding o
JOIN training_modules tm ON tm.title LIKE CONCAT('%', o.task, '%')
WHERE NOT EXISTS (
    SELECT 1 FROM training_requests tr
    WHERE tr.employee_id = o.employee_id AND tr.module_id = tm.id
);

-- ==============================================
-- MIGRATION COMPLETION LOG
-- ==============================================

-- Log the migration completion
INSERT INTO system_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, created_at)
SELECT
    1 as user_id,
    'data_migration' as action,
    'system' as table_name,
    NULL as record_id,
    NULL as old_values,
    '{"migration": "HR1 to HR2", "status": "completed", "tables_migrated": ["applicants", "job_roles", "interview_results", "performance_reviews", "recognition", "onboarding"]}' as new_values,
    '127.0.0.1' as ip_address,
    NOW() as created_at;

-- ==============================================
-- MIGRATION SUMMARY
-- ==============================================

SELECT
    (SELECT COUNT(*) FROM competency_models) as competency_models_created,
    (SELECT COUNT(*) FROM competencies) as competencies_created,
    (SELECT COUNT(*) FROM evaluations) as evaluations_created,
    (SELECT COUNT(*) FROM competency_scores) as competency_scores_created,
    (SELECT COUNT(*) FROM training_modules) as training_modules_created,
    (SELECT COUNT(*) FROM training_sessions) as training_sessions_created,
    (SELECT COUNT(*) FROM training_enrollments) as training_enrollments_created,
    (SELECT COUNT(*) FROM critical_roles) as critical_roles_created,
    (SELECT COUNT(*) FROM succession_candidates) as succession_candidates_created,
    (SELECT COUNT(*) FROM training_requests) as training_requests_created;

SELECT 'HR1 to HR2 data migration completed successfully!' as migration_status;






