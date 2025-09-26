<?php
session_start();
require_once __DIR__ . '/../includes/data/db.php';
require_once __DIR__ . '/../includes/functions/simple_auth.php';
require_once __DIR__ . '/../includes/functions/huggingface_ai.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$evaluationId = $_POST['evaluation_id'] ?? 0;
$aiManager = new HuggingFaceAI();
$db = getDB();

try {
    // Check if evaluation exists and is completed
    $stmt = $db->prepare("
        SELECT e.*, 
               emp.first_name as employee_first_name, emp.last_name as employee_last_name,
               eval.first_name as evaluator_first_name, eval.last_name as evaluator_last_name
        FROM evaluations e
        JOIN users emp ON e.employee_id = emp.id
        JOIN users eval ON e.evaluator_id = eval.id
        WHERE e.id = ? AND e.status = 'completed'
    ");
    $stmt->execute([$evaluationId]);
    $evaluation = $stmt->fetch();
    
    if (!$evaluation) {
        echo json_encode(['success' => false, 'message' => 'Evaluation not found or not completed']);
        exit;
    }
    
    // Check if analysis already exists
    $stmt = $db->prepare("
        SELECT id FROM ai_analysis_results 
        WHERE evaluation_id = ? AND analysis_type = 'competency_feedback'
    ");
    $stmt->execute([$evaluationId]);
    $existingAnalysis = $stmt->fetch();
    
    if ($existingAnalysis) {
        echo json_encode(['success' => false, 'message' => 'Analysis already exists for this evaluation']);
        exit;
    }
    
    // Check if evaluation has feedback comments
    $stmt = $db->prepare("
        SELECT COUNT(*) as comment_count
        FROM competency_scores 
        WHERE evaluation_id = ? AND comments IS NOT NULL AND comments != ''
    ");
    $stmt->execute([$evaluationId]);
    $commentCount = $stmt->fetch()['comment_count'];
    
    if ($commentCount == 0) {
        echo json_encode(['success' => false, 'message' => 'No feedback comments found for analysis']);
        exit;
    }
    
    // Run AI analysis
    $result = $aiManager->analyzeCompetencyFeedback($evaluationId);
    
    if ($result['success']) {
        // Log the activity
        $auth->logActivity('ai_analysis_completed', 'evaluations', $evaluationId, null, [
            'sentiment' => $result['overall_sentiment']['sentiment'],
            'confidence' => $result['overall_sentiment']['confidence'],
            'competencies_analyzed' => $result['competencies_analyzed']
        ]);
        
        echo json_encode([
            'success' => true,
            'message' => 'AI analysis completed successfully',
            'data' => [
                'sentiment' => $result['overall_sentiment']['sentiment'],
                'confidence' => $result['overall_sentiment']['confidence'],
                'summary' => $result['summary']['summary'],
                'competencies_analyzed' => $result['competencies_analyzed'],
                'total_feedback_length' => $result['total_feedback_length']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error running AI analysis: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error running AI analysis: ' . $e->getMessage()
    ]);
}
?>
