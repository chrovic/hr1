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

$action = $_POST['action'] ?? '';

$aiManager = new HuggingFaceAI();
$db = getDB();

try {
    if ($action === 'bulk_analysis') {
        // Get all completed evaluations without AI analysis
        $stmt = $db->prepare("
            SELECT e.id, e.overall_score,
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   eval.first_name as evaluator_first_name, eval.last_name as evaluator_last_name,
                   ec.name as cycle_name, cm.name as model_name,
                   COUNT(cs.id) as comment_count
            FROM evaluations e
            JOIN users emp ON e.employee_id = emp.id
            JOIN users eval ON e.evaluator_id = eval.id
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            JOIN competency_models cm ON e.model_id = cm.id
            LEFT JOIN competency_scores cs ON e.id = cs.evaluation_id AND cs.comments IS NOT NULL AND cs.comments != ''
            LEFT JOIN ai_analysis_results aar ON e.id = aar.evaluation_id AND aar.analysis_type = 'competency_feedback'
            WHERE e.status = 'completed' 
            AND aar.id IS NULL
            GROUP BY e.id
            HAVING comment_count > 0
            ORDER BY e.completed_at DESC
        ");
        $stmt->execute();
        $evaluations = $stmt->fetchAll();
        
        if (empty($evaluations)) {
            echo json_encode([
                'success' => false,
                'message' => 'No evaluations found that need AI analysis'
            ]);
            exit;
        }
        
        $totalEvaluations = count($evaluations);
        $successCount = 0;
        $errorCount = 0;
        $results = [];
        
        // Process each evaluation
        foreach ($evaluations as $evaluation) {
            try {
                $result = $aiManager->analyzeCompetencyFeedback($evaluation['id']);
                
                if ($result['success']) {
                    $successCount++;
                    $results[] = [
                        'evaluation_id' => $evaluation['id'],
                        'employee' => $evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name'],
                        'evaluator' => $evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name'],
                        'sentiment' => $result['overall_sentiment']['sentiment'],
                        'confidence' => $result['overall_sentiment']['confidence'],
                        'status' => 'success'
                    ];
                    
                    // Log the activity
                    $auth->logActivity('bulk_ai_analysis_completed', 'evaluations', $evaluation['id'], null, [
                        'sentiment' => $result['overall_sentiment']['sentiment'],
                        'confidence' => $result['overall_sentiment']['confidence']
                    ]);
                    
                } else {
                    $errorCount++;
                    $results[] = [
                        'evaluation_id' => $evaluation['id'],
                        'employee' => $evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name'],
                        'evaluator' => $evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name'],
                        'error' => $result['message'],
                        'status' => 'error'
                    ];
                }
                
                // Add small delay to prevent API rate limiting
                usleep(500000); // 0.5 seconds
                
            } catch (Exception $e) {
                $errorCount++;
                $results[] = [
                    'evaluation_id' => $evaluation['id'],
                    'employee' => $evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name'],
                    'evaluator' => $evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name'],
                    'error' => $e->getMessage(),
                    'status' => 'error'
                ];
            }
        }
        
        // Final response
        echo json_encode([
            'success' => true,
            'message' => "Bulk analysis completed: {$successCount} successful, {$errorCount} errors",
            'total' => $totalEvaluations,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results,
            'completed' => true
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Bulk AI analysis error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error running bulk analysis: ' . $e->getMessage()
    ]);
}
?>
