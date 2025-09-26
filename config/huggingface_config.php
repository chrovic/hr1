<?php
// Hugging Face AI Configuration
// This file contains configuration settings for Hugging Face AI integration

// Hugging Face API Configuration
define('HUGGINGFACE_API_KEY', getenv('HUGGINGFACE_API_KEY') ?: 'your_huggingface_api_key_here');
define('HUGGINGFACE_BASE_URL', 'https://api-inference.huggingface.co/models/');

// Model Configuration - Updated to more accurate models
define('SENTIMENT_MODEL', 'cardiffnlp/twitter-roberta-base-sentiment-latest');
define('SUMMARIZATION_MODEL', 'facebook/bart-large-cnn');
define('EMOTION_MODEL', 'j-hartmann/emotion-english-distilroberta-base');
define('CLASSIFICATION_MODEL', 'microsoft/DialoGPT-medium');

// Analysis Settings
define('AI_ANALYSIS_ENABLED', true);
define('AI_ANALYSIS_TIMEOUT', 30); // seconds
define('AI_ANALYSIS_MAX_RETRIES', 3);
define('AI_ANALYSIS_MIN_TEXT_LENGTH', 10);
define('AI_ANALYSIS_MAX_TEXT_LENGTH', 5000);

// Sentiment Analysis Settings
define('SENTIMENT_CONFIDENCE_THRESHOLD', 0.6);
define('SENTIMENT_POSITIVE_THRESHOLD', 0.7);
define('SENTIMENT_NEGATIVE_THRESHOLD', 0.7);

// Summarization Settings
define('SUMMARY_MAX_LENGTH', 150);
define('SUMMARY_MIN_LENGTH', 30);
define('SUMMARY_COMPRESSION_TARGET', 0.3); // 30% of original length

// Context Settings
define('INCLUDE_EMPLOYEE_CONTEXT', true);
define('INCLUDE_EVALUATOR_CONTEXT', true);
define('INCLUDE_PERFORMANCE_HISTORY', true);
define('INCLUDE_ORGANIZATIONAL_CONTEXT', true);

// Insight Generation Settings
define('GENERATE_INSIGHTS', true);
define('INSIGHT_CONFIDENCE_THRESHOLD', 0.7);
define('DETECT_BIAS_PATTERNS', true);
define('DETECT_SCORE_SENTIMENT_MISMATCH', true);

// Performance Settings
define('CACHE_ANALYSIS_RESULTS', true);
define('CACHE_DURATION', 86400); // 24 hours in seconds
define('BATCH_ANALYSIS_LIMIT', 10); // Max evaluations per batch

// Error Handling
define('FALLBACK_ON_API_ERROR', true);
define('LOG_AI_ERRORS', true);
define('NOTIFY_ON_AI_ERRORS', false);

// Development Settings
define('AI_DEBUG_MODE', false);
define('AI_DEBUG_LOG_FILE', 'logs/ai_debug.log');

// Feature Flags
define('ENABLE_SENTIMENT_ANALYSIS', true);
define('ENABLE_TEXT_SUMMARIZATION', true);
define('ENABLE_INSIGHT_GENERATION', true);
define('ENABLE_PATTERN_DETECTION', true);
define('ENABLE_BIAS_DETECTION', true);

// API Rate Limiting
define('API_RATE_LIMIT_PER_MINUTE', 60);
define('API_RATE_LIMIT_PER_HOUR', 1000);

// Quality Assurance
define('MIN_ANALYSIS_CONFIDENCE', 0.5);
define('REQUIRE_HUMAN_REVIEW_LOW_CONFIDENCE', true);
define('FLAG_INCONSISTENT_RESULTS', true);

// Notification Settings
define('NOTIFY_ON_ANALYSIS_COMPLETE', false);
define('NOTIFY_ON_ANALYSIS_ERROR', true);
define('NOTIFY_ON_BIAS_DETECTED', true);

// Export Settings
define('EXPORT_ANALYSIS_RESULTS', true);
define('EXPORT_FORMATS', ['json', 'csv', 'pdf']);
define('EXPORT_INCLUDE_RAW_DATA', false);

// Privacy Settings
define('ANONYMIZE_PERSONAL_DATA', false);
define('RETAIN_ANALYSIS_HISTORY', true);
define('ANALYSIS_RETENTION_DAYS', 365);

// Integration Settings
define('INTEGRATE_WITH_COMPETENCY_SYSTEM', true);
define('INTEGRATE_WITH_LEARNING_SYSTEM', true);
define('INTEGRATE_WITH_PERFORMANCE_SYSTEM', true);

// Model Performance Tracking
define('TRACK_MODEL_PERFORMANCE', true);
define('MODEL_PERFORMANCE_THRESHOLD', 0.8);
define('AUTO_SWITCH_MODELS', false);

// Cost Management
define('TRACK_API_COSTS', true);
define('DAILY_COST_LIMIT', 100.00); // USD
define('MONTHLY_COST_LIMIT', 1000.00); // USD

// Security Settings
define('ENCRYPT_ANALYSIS_DATA', true);
define('SECURE_API_COMMUNICATION', true);
define('AUDIT_AI_ACCESS', true);

// Backup Settings
define('BACKUP_ANALYSIS_DATA', true);
define('BACKUP_FREQUENCY', 'daily');
define('BACKUP_RETENTION_DAYS', 30);

// Monitoring Settings
define('MONITOR_API_HEALTH', true);
define('ALERT_ON_API_DOWNTIME', true);
define('HEALTH_CHECK_INTERVAL', 300); // 5 minutes

// Custom Models (if using custom trained models)
define('CUSTOM_SENTIMENT_MODEL', '');
define('CUSTOM_SUMMARIZATION_MODEL', '');
define('USE_CUSTOM_MODELS', false);

// Language Settings
define('DEFAULT_LANGUAGE', 'en');
define('SUPPORTED_LANGUAGES', ['en', 'es', 'fr', 'de']);
define('AUTO_DETECT_LANGUAGE', true);

// Advanced Features
define('ENABLE_MULTI_LANGUAGE_ANALYSIS', false);
define('ENABLE_EMOTION_ANALYSIS', false);
define('ENABLE_TOPIC_MODELING', false);
define('ENABLE_NAMED_ENTITY_RECOGNITION', false);

// Compliance Settings
define('GDPR_COMPLIANT', true);
define('DATA_RETENTION_POLICY', 'standard');
define('RIGHT_TO_BE_FORGOTTEN', true);

// Reporting Settings
define('GENERATE_AI_REPORTS', true);
define('REPORT_FREQUENCY', 'monthly');
define('INCLUDE_ACCURACY_METRICS', true);
define('INCLUDE_BIAS_ANALYSIS', true);

// Training Data Settings
define('COLLECT_TRAINING_DATA', false);
define('ANONYMIZE_TRAINING_DATA', true);
define('TRAINING_DATA_RETENTION', 90); // days

// Model Updates
define('AUTO_UPDATE_MODELS', false);
define('MODEL_UPDATE_FREQUENCY', 'monthly');
define('TEST_NEW_MODELS', true);

// Quality Metrics
define('TRACK_ACCURACY', true);
define('TRACK_PRECISION', true);
define('TRACK_RECALL', true);
define('TRACK_F1_SCORE', true);

// User Feedback
define('COLLECT_USER_FEEDBACK', true);
define('FEEDBACK_WEIGHT', 0.3);
define('USE_FEEDBACK_FOR_IMPROVEMENT', true);

// System Integration
define('INTEGRATE_WITH_HR_SYSTEM', true);
define('INTEGRATE_WITH_ANALYTICS', true);
define('INTEGRATE_WITH_REPORTING', true);

// Performance Optimization
define('USE_CACHING', true);
define('CACHE_WARMING', true);
define('ASYNC_PROCESSING', true);
define('BATCH_PROCESSING', true);

// Error Recovery
define('AUTO_RETRY_FAILED_ANALYSES', true);
define('MAX_RETRY_ATTEMPTS', 3);
define('RETRY_DELAY_SECONDS', 60);

// Data Validation
define('VALIDATE_INPUT_DATA', true);
define('SANITIZE_INPUT_DATA', true);
define('VALIDATE_OUTPUT_DATA', true);

// Logging Configuration
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_TO_FILE', true);
define('LOG_TO_DATABASE', true);
define('LOG_TO_CONSOLE', false);

// API Versioning
define('API_VERSION', '1.0');
define('SUPPORT_LEGACY_API', true);
define('API_DEPRECATION_NOTICE', false);

// Feature Toggles
$feature_toggles = [
    'sentiment_analysis' => true,
    'text_summarization' => true,
    'insight_generation' => true,
    'bias_detection' => true,
    'pattern_analysis' => true,
    'trend_analysis' => true,
    'predictive_analytics' => false,
    'real_time_analysis' => false,
    'batch_analysis' => true,
    'custom_models' => false
];

// Environment-specific settings
if (defined('ENVIRONMENT')) {
    switch (ENVIRONMENT) {
        case 'development':
            define('AI_DEBUG_MODE', true);
            define('LOG_LEVEL', 'DEBUG');
            define('CACHE_ANALYSIS_RESULTS', false);
            break;
        case 'testing':
            define('AI_DEBUG_MODE', true);
            define('LOG_LEVEL', 'DEBUG');
            define('USE_MOCK_API', true);
            break;
        case 'production':
            define('AI_DEBUG_MODE', false);
            define('LOG_LEVEL', 'WARNING');
            define('CACHE_ANALYSIS_RESULTS', true);
            break;
    }
}

// Validate configuration
function validateHuggingFaceConfig() {
    $errors = [];
    
    if (!defined('HUGGINGFACE_API_KEY') || HUGGINGFACE_API_KEY === 'your_api_key_here') {
        $errors[] = 'Hugging Face API key not configured';
    }
    
    if (AI_ANALYSIS_TIMEOUT < 10 || AI_ANALYSIS_TIMEOUT > 300) {
        $errors[] = 'AI analysis timeout must be between 10 and 300 seconds';
    }
    
    if (SENTIMENT_CONFIDENCE_THRESHOLD < 0.1 || SENTIMENT_CONFIDENCE_THRESHOLD > 1.0) {
        $errors[] = 'Sentiment confidence threshold must be between 0.1 and 1.0';
    }
    
    if (SUMMARY_MAX_LENGTH < SUMMARY_MIN_LENGTH) {
        $errors[] = 'Summary max length must be greater than min length';
    }
    
    return $errors;
}

// Get configuration summary
function getHuggingFaceConfigSummary() {
    return [
        'api_configured' => defined('HUGGINGFACE_API_KEY') && HUGGINGFACE_API_KEY !== 'your_api_key_here',
        'features_enabled' => [
            'sentiment_analysis' => ENABLE_SENTIMENT_ANALYSIS,
            'text_summarization' => ENABLE_TEXT_SUMMARIZATION,
            'insight_generation' => ENABLE_INSIGHT_GENERATION,
            'pattern_detection' => ENABLE_PATTERN_DETECTION,
            'bias_detection' => ENABLE_BIAS_DETECTION
        ],
        'models' => [
            'sentiment' => SENTIMENT_MODEL,
            'summarization' => SUMMARIZATION_MODEL
        ],
        'limits' => [
            'timeout' => AI_ANALYSIS_TIMEOUT,
            'max_text_length' => AI_ANALYSIS_MAX_TEXT_LENGTH,
            'min_text_length' => AI_ANALYSIS_MIN_TEXT_LENGTH
        ],
        'quality' => [
            'min_confidence' => MIN_ANALYSIS_CONFIDENCE,
            'sentiment_threshold' => SENTIMENT_CONFIDENCE_THRESHOLD,
            'summary_compression' => SUMMARY_COMPRESSION_TARGET
        ]
    ];
}
?>
