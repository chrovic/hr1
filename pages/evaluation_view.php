<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/competency.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$competencyManager = new CompetencyManager();

$evaluation_id = $_GET['id'] ?? 0;
$error = '';

// Get evaluation details
$evaluation = $competencyManager->getEvaluationDetails($evaluation_id);

if (!$evaluation) {
    $error = 'Evaluation not found.';
} else {
    // Check permissions - user must be the evaluator, employee, or have admin/HR permissions
    $canView = false;
    
    if ($evaluation['evaluator_id'] == $current_user['id'] || 
        $evaluation['employee_id'] == $current_user['id'] ||
        $auth->hasPermission('manage_evaluations')) {
        $canView = true;
    }
    
    if (!$canView) {
        $error = 'You are not authorized to view this evaluation.';
    }
}

// Get competency trends for the employee
$trends = [];
if ($evaluation && !$error) {
    $trends = $competencyManager->getEmployeeCompetencyTrends($evaluation['employee_id']);
}
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Evaluation Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="?page=evaluations" class="btn btn-outline-secondary">
            <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Evaluations
        </a>
        <button type="button" class="btn btn-success ml-2" onclick="printEvaluation()">
            <i class="fe fe-printer fe-16 mr-2"></i>Print
        </button>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($evaluation && !$error): ?>
<!-- Evaluation Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Evaluation Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="text-center">
                            <h2 class="text-primary mb-0"><?php echo number_format($evaluation['overall_score'], 1); ?></h2>
                            <small class="text-muted">Overall Score</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-success mb-0"><?php echo count($evaluation['scores']); ?></h4>
                            <small class="text-muted">Competencies</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <h4 class="text-info mb-0"><?php echo ucfirst($evaluation['status']); ?></h4>
                            <small class="text-muted">Status</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <strong>Employee:</strong><br>
                        <?php echo htmlspecialchars($evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Evaluator:</strong><br>
                        <?php echo htmlspecialchars($evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name']); ?>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Evaluation Cycle:</strong><br>
                        <?php echo htmlspecialchars($evaluation['cycle_name']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Competency Model:</strong><br>
                        <?php echo htmlspecialchars($evaluation['model_name']); ?>
                    </div>
                    <div class="col-md-4">
                        <strong>Completed:</strong><br>
                        <?php echo $evaluation['completed_at'] ? date('M d, Y H:i', strtotime($evaluation['completed_at'])) : 'Not completed'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Competency Scores -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Competency Assessment Results</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($evaluation['scores'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Competency</th>
                                    <th>Description</th>
                                    <th>Score</th>
                                    <th>Weight</th>
                                    <th>Weighted Score</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluation['scores'] as $score): ?>
                                    <?php 
                                    $percentage = ($score['score'] / $score['max_score']) * 100;
                                    $scoreClass = '';
                                    if ($percentage >= 80) $scoreClass = 'success';
                                    elseif ($percentage >= 60) $scoreClass = 'warning';
                                    else $scoreClass = 'danger';
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($score['competency_name']); ?></strong>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo htmlspecialchars($score['description']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $scoreClass; ?>">
                                                <?php echo $score['score']; ?>/<?php echo $score['max_score']; ?>
                                            </span>
                                            <small class="text-muted">(<?php echo number_format($percentage, 1); ?>%)</small>
                                        </td>
                                        <td><?php echo $score['weight']; ?>%</td>
                                        <td>
                                            <strong><?php echo number_format(($score['score'] / $score['max_score']) * $score['weight'], 1); ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($score['comments']): ?>
                                                <div class="comments-box">
                                                    <?php echo nl2br(htmlspecialchars($score['comments'])); ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">No comments</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="fe fe-alert-circle fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Scores Available</h4>
                        <p class="text-muted">This evaluation doesn't have any competency scores recorded.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Performance Trends -->
<?php if (!empty($trends)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Performance Trends</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Cycle</th>
                                <th>Model</th>
                                <th>Date</th>
                                <th>Score</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trends as $index => $trend): ?>
                                <?php 
                                $trendIcon = '';
                                $trendClass = '';
                                if ($index > 0) {
                                    $prevScore = $trends[$index - 1]['overall_score'];
                                    if ($trend['overall_score'] > $prevScore) {
                                        $trendIcon = '↗';
                                        $trendClass = 'text-success';
                                    } elseif ($trend['overall_score'] < $prevScore) {
                                        $trendIcon = '↘';
                                        $trendClass = 'text-danger';
                                    } else {
                                        $trendIcon = '→';
                                        $trendClass = 'text-muted';
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($trend['cycle_name']); ?></td>
                                    <td><?php echo htmlspecialchars($trend['model_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($trend['start_date'])); ?></td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo number_format($trend['overall_score'], 1); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($trendIcon): ?>
                                            <span class="<?php echo $trendClass; ?>"><?php echo $trendIcon; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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

<!-- Action Buttons -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <a href="?page=evaluations" class="btn btn-secondary">
                    <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Evaluations
                </a>
                <?php if ($evaluation['evaluator_id'] == $current_user['id'] && $evaluation['status'] !== 'completed'): ?>
                    <a href="?page=evaluation_form&id=<?php echo $evaluation['id']; ?>" class="btn btn-primary ml-2">
                        <i class="fe fe-edit fe-16 mr-2"></i>Continue Evaluation
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-success ml-2" onclick="exportEvaluation()">
                    <i class="fe fe-download fe-16 mr-2"></i>Export PDF
                </button>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<style>
.comments-box {
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    border-left: 3px solid #007bff;
    font-size: 0.9rem;
    max-width: 300px;
}
</style>

<script>
function printEvaluation() {
    window.print();
}

function exportEvaluation() {
    // This would typically generate a PDF
    alert('PDF export functionality would be implemented here.');
}

// Print styles
window.addEventListener('beforeprint', function() {
    document.body.classList.add('printing');
});

window.addEventListener('afterprint', function() {
    document.body.classList.remove('printing');
});
</script>

<style media="print">
@media print {
    .btn-toolbar, .card-header .btn {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        font-size: 12px;
    }
    
    .badge {
        border: 1px solid #000;
    }
}
</style>




