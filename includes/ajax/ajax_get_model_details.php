<?php
// Dedicated AJAX endpoint for competency model details
// This file handles only AJAX requests and returns clean JSON

// Suppress all error reporting for clean JSON output
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any warnings
ob_start();

try {
    // Include required files
    require_once 'includes/data/db.php';
    require_once 'includes/functions/competency.php';
    
    // Get the model ID from the request
    $modelId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($modelId <= 0) {
        throw new Exception('Invalid model ID');
    }
    
    // Initialize database and competency manager
    $db = getDB();
    $competencyManager = new CompetencyManager();
    
    // Get model details
    $stmt = $db->prepare("
        SELECT cm.*, u.first_name, u.last_name
        FROM competency_models cm
        LEFT JOIN users u ON cm.created_by = u.id
        WHERE cm.id = ?
    ");
    $stmt->execute([$modelId]);
    $model = $stmt->fetch();
    
    if (!$model) {
        throw new Exception('Model not found');
    }
    
    // Process model data
    $model['target_roles'] = json_decode($model['target_roles'], true) ?: [];
    $model['assessment_method_form'] = $competencyManager->mapAssessmentMethodToForm($model['assessment_method']);
    
    // Set default status if not present
    if (!isset($model['status'])) {
        $model['status'] = 'active';
    }
    
    // Get competencies for this model
    $competencies = $competencyManager->getModelCompetencies($modelId);
    
    // Clean output buffer and set headers
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Return success response
    echo json_encode([
        'success' => true,
        'model' => $model,
        'competencies' => $competencies
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Clean output buffer and return error
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit;
?>
