<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/huggingface_ai.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$aiManager = new HuggingFaceAI();
$db = getDB();

$message = '';
$error = '';

// Handle AI analysis requests
if ($_POST) {
    if (isset($_POST['analyze_evaluation'])) {
        $evaluationId = $_POST['evaluation_id'];
        
        try {
            $result = $aiManager->analyzeCompetencyFeedback($evaluationId);
            
            if ($result['success']) {
                $message = 'AI analysis completed successfully!';
                $auth->logActivity('ai_analysis_completed', 'evaluations', $evaluationId, null, [
                    'sentiment' => $result['overall_sentiment']['sentiment'],
                    'confidence' => $result['overall_sentiment']['confidence']
                ]);
            } else {
                $error = $result['message'];
            }
        } catch (Exception $e) {
            $error = 'Error running AI analysis: ' . $e->getMessage();
        }
    }
}

// Get evaluations with AI analysis
$evaluationsWithAnalysis = [];
$evaluationsWithoutAnalysis = [];

try {
    // Get evaluations with AI analysis
    $stmt = $db->prepare("
        SELECT e.*, 
               emp.first_name as employee_first_name, emp.last_name as employee_last_name,
               eval.first_name as evaluator_first_name, eval.last_name as evaluator_last_name,
               ec.name as cycle_name, cm.name as model_name,
               aar.sentiment, aar.sentiment_confidence, aar.summary, aar.created_at as analysis_date
        FROM evaluations e
        JOIN users emp ON e.employee_id = emp.id
        JOIN users eval ON e.evaluator_id = eval.id
        JOIN evaluation_cycles ec ON e.cycle_id = ec.id
        JOIN competency_models cm ON e.model_id = cm.id
        LEFT JOIN ai_analysis_results aar ON e.id = aar.evaluation_id AND aar.analysis_type = 'competency_feedback'
        WHERE e.status = 'completed'
        ORDER BY e.completed_at DESC
    ");
    $stmt->execute();
    $allEvaluations = $stmt->fetchAll();
    
    foreach ($allEvaluations as $evaluation) {
        if ($evaluation['sentiment']) {
            $evaluationsWithAnalysis[] = $evaluation;
        } else {
            $evaluationsWithoutAnalysis[] = $evaluation;
        }
    }
    
    // Get AI analysis statistics
    $stats = $aiManager->getSentimentStatistics();
    
} catch (Exception $e) {
    $error = 'Error loading evaluation data: ' . $e->getMessage();
}
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fe fe-cpu mr-2"></i>AI Analysis Dashboard
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bulkAnalysisModal">
            <i class="fe fe-zap mr-2"></i>Bulk Analysis
        </button>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- AI Analysis Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo $stats['total_evaluations'] ?? 0; ?></h4>
                        <p class="mb-0">Total Analyzed</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fe fe-bar-chart-2 fe-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo $stats['positive_count'] ?? 0; ?></h4>
                        <p class="mb-0">Positive</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fe fe-thumbs-up fe-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo $stats['neutral_count'] ?? 0; ?></h4>
                        <p class="mb-0">Neutral</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fe fe-minus fe-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo $stats['negative_count'] ?? 0; ?></h4>
                        <p class="mb-0">Negative</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fe fe-thumbs-down fe-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Evaluations with AI Analysis -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fe fe-cpu mr-2"></i>AI Analysis Results
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($evaluationsWithAnalysis)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-cpu fe-48 text-muted mb-3"></i>
                        <h5 class="text-muted">No AI Analysis Results Yet</h5>
                        <p class="text-muted">Run AI analysis on completed evaluations to see results here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Evaluator</th>
                                    <th>Cycle</th>
                                    <th>Sentiment</th>
                                    <th>Confidence</th>
                                    <th>Summary</th>
                                    <th>Analysis Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluationsWithAnalysis as $evaluation): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($evaluation['cycle_name']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo $evaluation['sentiment'] === 'positive' ? 'success' : 
                                                    ($evaluation['sentiment'] === 'negative' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($evaluation['sentiment']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="width: 60px; height: 20px;">
                                                <div class="progress-bar bg-<?php 
                                                    echo $evaluation['sentiment'] === 'positive' ? 'success' : 
                                                        ($evaluation['sentiment'] === 'negative' ? 'danger' : 'warning'); 
                                                ?>" 
                                                     style="width: <?php echo ($evaluation['sentiment_confidence'] * 100); ?>%">
                                                </div>
                                            </div>
                                            <small class="text-muted"><?php echo round($evaluation['sentiment_confidence'] * 100, 1); ?>%</small>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                  title="<?php echo htmlspecialchars($evaluation['summary']); ?>">
                                                <?php echo htmlspecialchars(substr($evaluation['summary'], 0, 100)) . (strlen($evaluation['summary']) > 100 ? '...' : ''); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($evaluation['analysis_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewAnalysisDetails(<?php echo $evaluation['id']; ?>)">
                                                <i class="fe fe-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Evaluations without AI Analysis -->
<?php if (!empty($evaluationsWithoutAnalysis)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fe fe-clock mr-2"></i>Pending AI Analysis
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Evaluator</th>
                                <th>Cycle</th>
                                <th>Model</th>
                                <th>Overall Score</th>
                                <th>Completed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evaluationsWithoutAnalysis as $evaluation): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($evaluation['cycle_name']); ?></td>
                                    <td><?php echo htmlspecialchars($evaluation['model_name']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php 
                                            echo $evaluation['overall_score'] >= 4 ? 'success' : 
                                                ($evaluation['overall_score'] >= 3 ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo number_format($evaluation['overall_score'], 1); ?>/5
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($evaluation['completed_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="evaluation_id" value="<?php echo $evaluation['id']; ?>">
                                            <button type="submit" name="analyze_evaluation" class="btn btn-sm btn-primary">
                                                <i class="fe fe-cpu mr-1"></i>Analyze
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Analysis Details Modal -->
<div class="modal fade" id="analysisDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">AI Analysis Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="analysisDetailsContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Bulk Analysis Modal -->
<div class="modal fade" id="bulkAnalysisModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fe fe-zap mr-2"></i>Bulk AI Analysis
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">What this does:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fe fe-check text-success mr-2"></i>Analyzes sentiment of all feedback comments</li>
                            <li><i class="fe fe-check text-success mr-2"></i>Generates summaries of evaluation feedback</li>
                            <li><i class="fe fe-check text-success mr-2"></i>Detects patterns and inconsistencies</li>
                            <li><i class="fe fe-check text-success mr-2"></i>Creates actionable insights</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Requirements:</h6>
                        <ul class="list-unstyled">
                            <li><i class="fe fe-info text-info mr-2"></i>Completed evaluations only</li>
                            <li><i class="fe fe-info text-info mr-2"></i>Must have feedback comments</li>
                            <li><i class="fe fe-info text-info mr-2"></i>Not already analyzed</li>
                            <li><i class="fe fe-info text-info mr-2"></i>Internet connection required</li>
                        </ul>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fe fe-info mr-2"></i>
                    <strong>Note:</strong> This process may take several minutes depending on the number of evaluations. 
                    The system will process each evaluation individually to ensure quality results.
                </div>
                
                <div id="bulkAnalysisProgress" style="display: none;">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%"></div>
                    </div>
                    <p class="text-center mb-0" id="progressText">Starting analysis...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="startBulkAnalysisBtn" onclick="runBulkAnalysis()">
                    <i class="fe fe-zap mr-1"></i>Start Bulk Analysis
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewAnalysisDetails(evaluationId) {
    // Load analysis details via AJAX with cache-busting
    fetch(`ajax/get_analysis_details.php?evaluation_id=${evaluationId}&t=${Date.now()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('analysisDetailsContent').innerHTML = data.html;
                $('#analysisDetailsModal').modal('show');
            } else {
                showNotification('Error loading analysis details: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading analysis details', 'error');
        });
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 
                      type === 'info' ? 'alert-info' : 'alert-warning';
    const icon = type === 'success' ? 'fe-check-circle' : 
                 type === 'error' ? 'fe-alert-circle' : 
                 type === 'info' ? 'fe-info' : 'fe-alert-triangle';

    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed"
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fe ${icon} mr-2"></i>${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `);

    $('body').append(notification);

    // Auto remove after 5 seconds
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}

function runBulkAnalysis() {
    if (confirm('This will run AI analysis on all completed evaluations that haven\'t been analyzed yet. This may take several minutes. Continue?')) {
        // Show progress section
        document.getElementById('bulkAnalysisProgress').style.display = 'block';
        
        // Show loading state
        const button = document.getElementById('startBulkAnalysisBtn');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fe fe-loader fe-spin mr-1"></i>Starting...';
        button.disabled = true;
        
        // Start bulk analysis
        fetch('ajax/bulk_ai_analysis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=bulk_analysis'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Analysis completed
                showNotification(`Bulk analysis completed: ${data.success_count} successful, ${data.error_count} errors`, 'success');
                $('#bulkAnalysisModal').modal('hide');
                // Reload page to show new results
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showNotification('Error: ' + data.message, 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error running bulk analysis', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

</script>
