<?php
// Dedicated AJAX endpoint for evaluation cycle details
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
    
    // Get the cycle ID from the request
    $cycleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($cycleId <= 0) {
        throw new Exception('Invalid cycle ID');
    }
    
    // Initialize database and competency manager
    $db = getDB();
    $competencyManager = new CompetencyManager();
    
    // Get cycle details
    $stmt = $db->prepare("
        SELECT ec.*, u.first_name, u.last_name
        FROM evaluation_cycles ec
        LEFT JOIN users u ON ec.created_by = u.id
        WHERE ec.id = ?
    ");
    $stmt->execute([$cycleId]);
    $cycle = $stmt->fetch();
    
    if (!$cycle) {
        throw new Exception('Cycle not found');
    }
    
    // Get evaluations for this cycle
    $stmt = $db->prepare("
        SELECT e.*, u1.first_name as employee_first_name, u1.last_name as employee_last_name,
               u2.first_name as evaluator_first_name, u2.last_name as evaluator_last_name,
               cm.name as model_name
        FROM evaluations e
        LEFT JOIN users u1 ON e.employee_id = u1.id
        LEFT JOIN users u2 ON e.evaluator_id = u2.id
        LEFT JOIN competency_models cm ON e.model_id = cm.id
        WHERE e.cycle_id = ?
        ORDER BY e.created_at DESC
    ");
    $stmt->execute([$cycleId]);
    $evaluations = $stmt->fetchAll();
    
    // Clean output buffer and set headers
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Return success response
    echo json_encode([
        'success' => true,
        'cycle' => $cycle,
        'evaluations' => $evaluations
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
