<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/competency.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$competencyManager = new CompetencyManager();

$evaluation_id = $_GET['id'] ?? 0;
$message = '';
$error = '';

// Get evaluation details
$evaluation = $competencyManager->getEvaluationDetails($evaluation_id);

if (!$evaluation) {
    $error = 'Evaluation not found.';
} else {
    // Check if current user is the evaluator
    if ($evaluation['evaluator_id'] != $current_user['id']) {
        $error = 'You are not authorized to conduct this evaluation.';
    }
    
    // Check if evaluation is already completed
    if ($evaluation['status'] === 'completed') {
        $error = 'This evaluation has already been completed.';
    }
}

// Form processing is now handled in index.php before any output

// Handle success messages from redirects
if (isset($_GET['success']) && $_GET['success'] === 'scores_submitted') {
    $message = 'Evaluation completed successfully!';
    // Refresh evaluation data
    $evaluation = $competencyManager->getEvaluationDetails($evaluation_id);
}

// Get competencies for the model
$competencies = [];
if ($evaluation && !$error) {
    $competencies = $competencyManager->getModelCompetencies($evaluation['model_id']);
}
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Conduct Evaluation</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="?page=evaluations" class="btn btn-outline-secondary">
            <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Evaluations
        </a>
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

<?php if ($evaluation && !$error): ?>
<!-- Evaluation Information -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Evaluation Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Employee:</strong><br>
                        <?php echo htmlspecialchars($evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Cycle:</strong><br>
                        <?php echo htmlspecialchars($evaluation['cycle_name']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Model:</strong><br>
                        <?php echo htmlspecialchars($evaluation['model_name']); ?>
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge badge-warning"><?php echo ucfirst($evaluation['status']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($evaluation['status'] !== 'completed'): ?>
<!-- Evaluation Form -->
<form method="POST">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Competency Assessment</h5>
                    <small class="text-muted">Rate each competency on a scale of 1-5 and provide comments</small>
                </div>
                <div class="card-body">
                    <?php foreach ($competencies as $competency): ?>
                        <div class="competency-item mb-4 p-3 border rounded">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="mb-2"><?php echo htmlspecialchars($competency['name']); ?></h6>
                                    <p class="text-muted mb-2"><?php echo htmlspecialchars($competency['description']); ?></p>
                                    <small class="text-info">
                                        <strong>Weight:</strong> <?php echo $competency['weight']; ?>% | 
                                        <strong>Max Score:</strong> <?php echo $competency['max_score']; ?>
                                    </small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="score_<?php echo $competency['id']; ?>">Score (1-<?php echo $competency['max_score']; ?>)</label>
                                        <select class="form-control" id="score_<?php echo $competency['id']; ?>" name="scores[<?php echo $competency['id']; ?>][score]" required>
                                            <option value="">Select Score</option>
                                            <?php for ($i = 1; $i <= $competency['max_score']; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="comments_<?php echo $competency['id']; ?>">Comments</label>
                                        <textarea class="form-control" id="comments_<?php echo $competency['id']; ?>" name="scores[<?php echo $competency['id']; ?>][comments]" rows="2" placeholder="Add your comments..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($competencies)): ?>
                        <div class="text-center py-4">
                            <i class="fe fe-alert-circle fe-48 text-muted"></i>
                            <h4 class="text-muted mt-3">No Competencies Found</h4>
                            <p class="text-muted">This competency model doesn't have any competencies defined.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Instructions:</strong> Rate each competency based on the employee's performance. 
                                Use the comments section to provide specific feedback and examples.
                            </small>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" name="submit_scores" class="btn btn-success btn-lg" <?php echo empty($competencies) ? 'disabled' : ''; ?>>
                                <i class="fe fe-check fe-16 mr-2"></i>Complete Evaluation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?php else: ?>
<!-- Completed Evaluation View -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Evaluation Results</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="text-center">
                            <h3 class="text-primary"><?php echo number_format($evaluation['overall_score'], 1); ?></h3>
                            <p class="text-muted">Overall Score</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center">
                            <h3 class="text-success"><?php echo count($evaluation['scores']); ?></h3>
                            <p class="text-muted">Competencies Evaluated</p>
                        </div>
                    </div>
                </div>
                
                <h6>Competency Scores:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Competency</th>
                                <th>Score</th>
                                <th>Weight</th>
                                <th>Weighted Score</th>
                                <th>Comments</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($evaluation['scores'] as $score): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($score['competency_name']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($score['description']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary"><?php echo $score['score']; ?>/<?php echo $score['max_score']; ?></span>
                                    </td>
                                    <td><?php echo $score['weight']; ?>%</td>
                                    <td><?php echo number_format(($score['score'] / $score['max_score']) * $score['weight'], 1); ?></td>
                                    <td><?php echo htmlspecialchars($score['comments']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="text-center mt-4">
                    <a href="?page=evaluations" class="btn btn-primary">
                        <i class="fe fe-arrow-left fe-16 mr-2"></i>Back to Evaluations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<script>
// Auto-save functionality
let autoSaveTimeout;
const form = document.querySelector('form');

if (form) {
    form.addEventListener('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            // Auto-save logic could be implemented here
            console.log('Auto-saving evaluation...');
        }, 2000);
    });
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.querySelector('button[name="submit_scores"]');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            const requiredFields = document.querySelectorAll('select[name*="[score]"]');
            let allFilled = true;
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    allFilled = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!allFilled) {
                e.preventDefault();
                alert('Please fill in all competency scores before submitting.');
            }
        });
    }
});
</script>