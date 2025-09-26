<?php
session_start();
require_once __DIR__ . '/../includes/data/db.php';
require_once __DIR__ . '/../includes/functions/simple_auth.php';
require_once __DIR__ . '/../includes/functions/huggingface_ai.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('ETag: "' . md5(time()) . '"');

$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$evaluationId = $_GET['evaluation_id'] ?? 0;
$aiManager = new HuggingFaceAI();
$db = getDB();

try {
    // Get analysis results
    $analysis = $aiManager->getAnalysisResults($evaluationId);
    
    if (!$analysis) {
        echo json_encode(['success' => false, 'message' => 'Analysis not found']);
        exit;
    }
    
    // Get evaluation details
    $stmt = $db->prepare("
        SELECT e.*, 
               emp.first_name as employee_first_name, emp.last_name as employee_last_name,
               eval.first_name as evaluator_first_name, eval.last_name as evaluator_last_name,
               ec.name as cycle_name, cm.name as model_name
        FROM evaluations e
        JOIN users emp ON e.employee_id = emp.id
        JOIN users eval ON e.evaluator_id = eval.id
        JOIN evaluation_cycles ec ON e.cycle_id = ec.id
        JOIN competency_models cm ON e.model_id = cm.id
        WHERE e.id = ?
    ");
    $stmt->execute([$evaluationId]);
    $evaluation = $stmt->fetch();
    
    if (!$evaluation) {
        echo json_encode(['success' => false, 'message' => 'Evaluation not found']);
        exit;
    }
    
    // Get competency scores with comments (avoid duplicates)
    $stmt = $db->prepare("
        SELECT DISTINCT cs.*, c.name as competency_name, c.weight
        FROM competency_scores cs
        JOIN competencies c ON cs.competency_id = c.id
        WHERE cs.evaluation_id = ?
        ORDER BY c.name
    ");
    $stmt->execute([$evaluationId]);
    $competencyScores = $stmt->fetchAll();
    
    // Parse analysis data
    $analysisData = is_string($analysis['analysis_data']) ? json_decode($analysis['analysis_data'], true) : $analysis['analysis_data'];
    $competencyAnalysis = $analysisData['competency_analysis'] ?? [];
    
    // Generate HTML content
    ob_start();
    ?>
    
    <div class="row">
        <div class="col-md-6">
            <h6 class="text-primary mb-3">Evaluation Overview</h6>
            <table class="table table-sm">
                <tr>
                    <td><strong>Employee:</strong></td>
                    <td><?php echo htmlspecialchars($evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Evaluator:</strong></td>
                    <td><?php echo htmlspecialchars($evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Cycle:</strong></td>
                    <td><?php echo htmlspecialchars($evaluation['cycle_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Model:</strong></td>
                    <td><?php echo htmlspecialchars($evaluation['model_name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Overall Score:</strong></td>
                    <td>
                        <span class="badge badge-<?php 
                            echo $evaluation['overall_score'] >= 4 ? 'success' : 
                                ($evaluation['overall_score'] >= 3 ? 'warning' : 'danger'); 
                        ?>">
                            <?php echo number_format($evaluation['overall_score'], 1); ?>/5
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h6 class="text-primary mb-3">AI Analysis Summary</h6>
            <div class="mb-3">
                <strong>Sentiment:</strong>
                <span class="badge badge-<?php 
                    $sentiment = $analysis['sentiment'];
                    echo $sentiment === 'positive' ? 'success' : 
                        ($sentiment === 'negative' ? 'danger' : 'warning'); 
                ?> ml-2">
                    <?php echo ucfirst($analysis['sentiment']); ?>
                </span>
                <small class="text-muted ml-2">
                    (<?php echo round($analysis['sentiment_confidence'] * 100, 1); ?>% confidence)
                </small>
            </div>
            
            <div class="mb-3">
                <strong>Summary:</strong>
                <p class="text-muted mt-1"><?php echo htmlspecialchars($analysis['summary']); ?></p>
            </div>
            
            <div class="mb-3">
                <strong>Compression Ratio:</strong>
                <span class="badge badge-info">
                    <?php echo round($analysis['compression_ratio'] * 100, 1); ?>%
                </span>
                <small class="text-muted ml-2">
                    (<?php echo $analysis['original_length']; ?> → <?php echo $analysis['summary_length']; ?> chars)
                </small>
            </div>
        </div>
    </div>
    
    <hr>
    
    <h6 class="text-primary mb-3">Competency Analysis</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Competency</th>
                    <th>Score</th>
                    <th>Sentiment</th>
                    <th>Confidence</th>
                    <th>Comments</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competencyScores as $score): ?>
                    <?php
                    // Find corresponding AI analysis
                    $aiAnalysis = null;
                    foreach ($competencyAnalysis as $analysis) {
                        if ($analysis['competency_name'] === $score['competency_name']) {
                            $aiAnalysis = $analysis;
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($score['competency_name']); ?></strong>
                            <br><small class="text-muted">Weight: <?php echo $score['weight']; ?></small>
                        </td>
                        <td>
                            <span class="badge badge-<?php 
                                $scoreValue = floatval($score['score']);
                                echo $scoreValue >= 4 ? 'success' : 
                                    ($scoreValue >= 3 ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo $score['score']; ?>/5
                            </span>
                        </td>
                        <td>
                            <?php if ($aiAnalysis): ?>
                                <span class="badge badge-<?php 
                                    $sentiment = $aiAnalysis['sentiment'];
                                    echo $sentiment === 'positive' ? 'success' : 
                                        ($sentiment === 'negative' ? 'danger' : 'warning'); 
                                ?>">
                                    <?php echo ucfirst($aiAnalysis['sentiment']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($aiAnalysis): ?>
                                <div class="progress" style="width: 60px; height: 15px;">
                                    <div class="progress-bar bg-<?php 
                                        $confidence = $aiAnalysis['confidence'] * 100;
                                        echo $confidence >= 90 ? 'success' : 
                                            ($confidence >= 70 ? 'warning' : 'danger'); 
                                    ?>" 
                                         style="width: <?php echo $confidence; ?>%">
                                    </div>
                                </div>
                                <small class="text-muted"><?php echo round($aiAnalysis['confidence'] * 100, 1); ?>%</small>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($score['comments']): ?>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                      title="<?php echo htmlspecialchars($score['comments']); ?>">
                                    <?php echo htmlspecialchars(substr($score['comments'], 0, 100)) . (strlen($score['comments']) > 100 ? '...' : ''); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">No comments</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (!empty($analysisData['insights'])): ?>
    <hr>
    <h6 class="text-primary mb-3">AI Insights</h6>
    <div class="row">
        <?php foreach ($analysisData['insights'] as $insight): ?>
            <div class="col-md-6 mb-3">
                <div class="card border-<?php 
                    echo $insight['type'] === 'strength' ? 'success' : 
                        ($insight['type'] === 'improvement_area' ? 'warning' : 
                        ($insight['type'] === 'risk' ? 'danger' : 'info')); 
                ?>">
                    <div class="card-body">
                        <h6 class="card-title text-<?php 
                            echo $insight['type'] === 'strength' ? 'success' : 
                                ($insight['type'] === 'improvement_area' ? 'warning' : 
                                ($insight['type'] === 'risk' ? 'danger' : 'info')); 
                        ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $insight['type'])); ?>
                        </h6>
                        <p class="card-text"><?php echo htmlspecialchars($insight['text']); ?></p>
                        <small class="text-muted">
                            Confidence: <?php echo round($insight['confidence'] * 100, 1); ?>% | 
                            Priority: <?php echo ucfirst($insight['priority']); ?>
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="mt-3">
        <small class="text-muted">
            Analysis completed on <?php echo isset($analysis['created_at']) ? date('M j, Y H:i', strtotime($analysis['created_at'])) : 'Unknown'; ?>
        </small>
    </div>
    
    <?php
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    error_log("Error getting analysis details: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error loading analysis details: ' . $e->getMessage()
    ]);
}
?>
