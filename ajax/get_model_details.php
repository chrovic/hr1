<?php
// Standalone AJAX endpoint for getting model details
// Start session first
session_start();

// Clean all output buffers first
while (ob_get_level()) {
    ob_end_clean();
}

// Prevent any HTML output
ob_start();

// Set JSON headers immediately
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

try {
    // Include required files
    require_once '../includes/data/db.php';
    require_once '../includes/functions/simple_auth.php';
    require_once '../includes/functions/competency.php';
    
    // Get model ID
    $modelId = intval($_GET['id'] ?? 0);
    
    if ($modelId <= 0) {
        throw new Exception('Invalid model ID');
    }
    
    // Initialize components
    $auth = new SimpleAuth();
    $competencyManager = new CompetencyManager();
    $db = getDB();
    
    // Check authentication
    if (!$auth->isLoggedIn()) {
        throw new Exception('Not authenticated');
    }
    
    if (!$auth->hasPermission('manage_evaluations')) {
        throw new Exception('Access denied');
    }
    
    // Get model details
    $stmt = $db->prepare("
        SELECT cm.*, u.first_name, u.last_name
        FROM competency_models cm
        LEFT JOIN users u ON cm.created_by = u.id
        WHERE cm.id = ?
    ");
    $stmt->execute([$modelId]);
    $model = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$model) {
        throw new Exception('Model not found');
    }
    
    // Process model data
    if (!empty($model['target_roles'])) {
        $decoded_roles = json_decode($model['target_roles'], true);
        $model['target_roles'] = is_array($decoded_roles) ? $decoded_roles : [];
    } else {
        $model['target_roles'] = [];
    }
    
    // Map assessment method
    $model['assessment_method_form'] = $competencyManager->mapAssessmentMethodToForm($model['assessment_method']);
    
    // Set default status
    if (!isset($model['status'])) {
        $model['status'] = 'active';
    }
    
    // Get competencies for this model
    $competencies = $competencyManager->getModelCompetencies($modelId);
    
    // Prepare response
    $response = [
        'success' => true,
        'model' => $model,
        'competencies' => $competencies
    ];
    
    // Clear any output and send response
    ob_clean();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // Clear any output and send error response
    ob_clean();
    
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

exit;
?>
