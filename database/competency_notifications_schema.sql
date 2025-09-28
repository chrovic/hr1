-- Competency Notifications System Database Schema
-- This file contains the database structure for competency action notifications

USE hr1_system;

-- Competency notifications table
CREATE TABLE IF NOT EXISTS competency_notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    notification_type ENUM(
        'model_created', 'model_updated', 'model_deleted', 'model_archived',
        'competency_added', 'competency_updated', 'competency_deleted',
        'cycle_created', 'cycle_updated', 'cycle_deleted',
        'evaluation_assigned', 'evaluation_completed', 'evaluation_overdue',
        'score_submitted', 'report_generated'
    ) NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    related_id INT NULL, -- ID of related record (model_id, competency_id, evaluation_id, etc.)
    related_type ENUM('model', 'competency', 'cycle', 'evaluation', 'report') NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_important BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(500) NULL, -- URL to navigate to when notification is clicked
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notification preferences table
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    in_app_notifications BOOLEAN DEFAULT TRUE,
    sms_notifications BOOLEAN DEFAULT FALSE,
    competency_model_created BOOLEAN DEFAULT TRUE,
    competency_model_updated BOOLEAN DEFAULT TRUE,
    competency_model_deleted BOOLEAN DEFAULT TRUE,
    competency_added BOOLEAN DEFAULT TRUE,
    competency_updated BOOLEAN DEFAULT TRUE,
    competency_deleted BOOLEAN DEFAULT TRUE,
    cycle_created BOOLEAN DEFAULT TRUE,
    cycle_updated BOOLEAN DEFAULT TRUE,
    cycle_deleted BOOLEAN DEFAULT TRUE,
    evaluation_assigned BOOLEAN DEFAULT TRUE,
    evaluation_completed BOOLEAN DEFAULT TRUE,
    evaluation_overdue BOOLEAN DEFAULT TRUE,
    score_submitted BOOLEAN DEFAULT TRUE,
    report_generated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);

-- Notification templates table
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    notification_type ENUM(
        'model_created', 'model_updated', 'model_deleted', 'model_archived',
        'competency_added', 'competency_updated', 'competency_deleted',
        'cycle_created', 'cycle_updated', 'cycle_deleted',
        'evaluation_assigned', 'evaluation_completed', 'evaluation_overdue',
        'score_submitted', 'report_generated'
    ) NOT NULL,
    title_template VARCHAR(200) NOT NULL,
    message_template TEXT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_notification_type (notification_type)
);

-- Insert default notification templates
INSERT INTO notification_templates (notification_type, title_template, message_template) VALUES
('model_created', 'New Competency Model Created', 'A new competency model "{model_name}" has been created by {created_by}.'),
('model_updated', 'Competency Model Updated', 'The competency model "{model_name}" has been updated by {updated_by}.'),
('model_deleted', 'Competency Model Deleted', 'The competency model "{model_name}" has been deleted by {deleted_by}.'),
('model_archived', 'Competency Model Archived', 'The competency model "{model_name}" has been archived by {archived_by}.'),
('competency_added', 'New Competency Added', 'A new competency "{competency_name}" has been added to model "{model_name}".'),
('competency_updated', 'Competency Updated', 'The competency "{competency_name}" in model "{model_name}" has been updated.'),
('competency_deleted', 'Competency Deleted', 'The competency "{competency_name}" has been removed from model "{model_name}".'),
('cycle_created', 'New Evaluation Cycle Created', 'A new evaluation cycle "{cycle_name}" has been created by {created_by}.'),
('cycle_updated', 'Evaluation Cycle Updated', 'The evaluation cycle "{cycle_name}" has been updated by {updated_by}.'),
('cycle_deleted', 'Evaluation Cycle Deleted', 'The evaluation cycle "{cycle_name}" has been deleted by {deleted_by}.'),
('evaluation_assigned', 'New Evaluation Assigned', 'You have been assigned to evaluate {employee_name} for the {cycle_name} cycle.'),
('evaluation_completed', 'Evaluation Completed', 'The evaluation for {employee_name} has been completed by {evaluator_name}.'),
('evaluation_overdue', 'Evaluation Overdue', 'The evaluation for {employee_name} is overdue. Please complete it as soon as possible.'),
('score_submitted', 'Scores Submitted', 'Competency scores have been submitted for {employee_name} by {evaluator_name}.'),
('report_generated', 'Report Generated', 'A competency report has been generated for {report_scope}.');

-- Create indexes for better performance
CREATE INDEX idx_competency_notifications_user_id ON competency_notifications(user_id);
CREATE INDEX idx_competency_notifications_type ON competency_notifications(notification_type);
CREATE INDEX idx_competency_notifications_is_read ON competency_notifications(is_read);
CREATE INDEX idx_competency_notifications_created_at ON competency_notifications(created_at);
CREATE INDEX idx_competency_notifications_related ON competency_notifications(related_type, related_id);
CREATE INDEX idx_notification_preferences_user_id ON notification_preferences(user_id);

-- Create default notification preferences for existing users
INSERT INTO notification_preferences (user_id, email_notifications, in_app_notifications)
SELECT id, TRUE, TRUE FROM users WHERE id NOT IN (SELECT user_id FROM notification_preferences);

SELECT 'Competency notifications database schema created successfully!' as message;


