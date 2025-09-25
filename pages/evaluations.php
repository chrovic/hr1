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

$message = '';
$error = '';

// Handle success message from redirect
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $message = 'Evaluation assigned successfully!';
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['assign_evaluation'])) {
        $evaluationData = [
            'cycle_id' => $_POST['cycle_id'],
            'employee_id' => $_POST['employee_id'],
            'evaluator_id' => $_POST['evaluator_id'],
            'model_id' => $_POST['model_id']
        ];
        
        // Check if evaluation already exists
        $stmt = $db->prepare("SELECT id FROM evaluations WHERE cycle_id = ? AND employee_id = ? AND evaluator_id = ? AND model_id = ?");
        $stmt->execute([$evaluationData['cycle_id'], $evaluationData['employee_id'], $evaluationData['evaluator_id'], $evaluationData['model_id']]);
        
        if ($stmt->fetch()) {
            $error = 'This evaluation already exists for the selected employee, cycle, and model.';
        } else {
            if ($competencyManager->assignEvaluation($evaluationData)) {
                $message = 'Evaluation assigned successfully!';
                $auth->logActivity('assign_evaluation', 'evaluations', null, null, $evaluationData);
                
                // Redirect to prevent form resubmission
                header('Location: ?page=evaluations&success=1');
                exit;
            } else {
                $error = 'Failed to assign evaluation.';
            }
        }
    }
}

// Get data for dropdowns
$cycles = $competencyManager->getAllCycles();
$models = $competencyManager->getAllModels();

// Get users for dropdowns
$db = getDB();
$stmt = $db->prepare("SELECT id, first_name, last_name, role FROM users WHERE status = 'active' ORDER BY last_name, first_name");
$stmt->execute();
$users = $stmt->fetchAll();

// Get evaluations - show all for admins, only assigned for others
if ($current_user['role'] === 'admin' || $current_user['role'] === 'hr_manager') {
    // Get all evaluations for admins and HR managers
    $stmt = $db->prepare("
        SELECT e.*, ec.name as cycle_name, ec.type as cycle_type,
               emp.first_name as employee_first_name, emp.last_name as employee_last_name,
               ev.first_name as evaluator_first_name, ev.last_name as evaluator_last_name,
               cm.name as model_name
        FROM evaluations e
        JOIN evaluation_cycles ec ON e.cycle_id = ec.id
        JOIN users emp ON e.employee_id = emp.id
        JOIN users ev ON e.evaluator_id = ev.id
        JOIN competency_models cm ON e.model_id = cm.id
        ORDER BY e.created_at DESC
    ");
    $stmt->execute();
    $evaluations = $stmt->fetchAll();
} else {
    // Get evaluations assigned to current user
    $evaluations = $competencyManager->getEvaluatorAssignments($current_user['id']);
}

// Show helpful message if no evaluations found
if (empty($evaluations) && !isset($_GET['success'])) {
    $message = 'No evaluations found. Use the "Assign Evaluation" button to create new evaluations.';
}
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Evaluations</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assignEvaluationModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Assign Evaluation
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

<!-- My Evaluations -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">My Evaluations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($evaluations)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-clipboard fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Evaluations Assigned</h4>
                        <p class="text-muted">You don't have any evaluations assigned to you yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <?php if ($current_user['role'] === 'admin' || $current_user['role'] === 'hr_manager'): ?>
                                        <th>Evaluator</th>
                                    <?php endif; ?>
                                    <th>Cycle</th>
                                    <th>Model</th>
                                    <th>Status</th>
                                    <th>Overall Score</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($evaluations as $evaluation): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars(($evaluation['employee_first_name'] ?? 'Unknown') . ' ' . ($evaluation['employee_last_name'] ?? 'User')); ?></strong>
                                        </td>
                                        <?php if ($current_user['role'] === 'admin' || $current_user['role'] === 'hr_manager'): ?>
                                            <td>
                                                <span class="text-muted"><?php echo htmlspecialchars(($evaluation['evaluator_first_name'] ?? 'Unknown') . ' ' . ($evaluation['evaluator_last_name'] ?? 'User')); ?></span>
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($evaluation['cycle_name'] ?? 'Unknown Cycle'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($evaluation['model_name'] ?? 'Unknown Model'); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($evaluation['status']) {
                                                case 'pending': $statusClass = 'badge-warning'; break;
                                                case 'in_progress': $statusClass = 'badge-info'; break;
                                                case 'completed': $statusClass = 'badge-success'; break;
                                                case 'cancelled': $statusClass = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($evaluation['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($evaluation['overall_score']): ?>
                                                <span class="badge badge-primary"><?php echo number_format($evaluation['overall_score'], 1); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($evaluation['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <?php if ($evaluation['status'] !== 'completed'): ?>
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="conductEvaluation(<?php echo $evaluation['id']; ?>)">
                                                        <i class="fe fe-edit fe-14"></i> Conduct
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewEvaluation(<?php echo $evaluation['id']; ?>)">
                                                        <i class="fe fe-eye fe-14"></i> View
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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

<!-- Assign Evaluation Modal -->
<div class="modal fade" id="assignEvaluationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Evaluation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cycle_id">Evaluation Cycle *</label>
                                <select class="form-control" id="cycle_id" name="cycle_id" required>
                                    <option value="">Select Cycle</option>
                                    <?php foreach ($cycles as $cycle): ?>
                                        <option value="<?php echo $cycle['id']; ?>"><?php echo htmlspecialchars($cycle['name'] ?? 'Unnamed Cycle'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="model_id">Competency Model *</label>
                                <select class="form-control" id="model_id" name="model_id" required>
                                    <option value="">Select Model</option>
                                    <?php foreach ($models as $model): ?>
                                        <option value="<?php echo $model['id']; ?>"><?php echo htmlspecialchars($model['name'] ?? 'Unnamed Model'); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee *</label>
                                <select class="form-control" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($users as $user): ?>
                                        <?php if ($user['role'] === 'employee'): ?>
                                            <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars(($user['first_name'] ?? 'Unknown') . ' ' . ($user['last_name'] ?? 'User')); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="evaluator_id">Evaluator *</label>
                                <select class="form-control" id="evaluator_id" name="evaluator_id" required>
                                    <option value="">Select Evaluator</option>
                                    <?php foreach ($users as $user): ?>
                                        <?php if (in_array($user['role'], ['admin', 'hr_manager'])): ?>
                                            <option value="<?php echo $user['id']; ?>" <?php echo ($user['id'] == $current_user['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars(($user['first_name'] ?? 'Unknown') . ' ' . ($user['last_name'] ?? 'User')); ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_evaluation" class="btn btn-primary">Assign Evaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function conductEvaluation(evaluationId) {
    // Redirect to evaluation form
    window.location.href = '?page=evaluation_form&id=' + evaluationId;
}

function viewEvaluation(evaluationId) {
    // Redirect to evaluation view
    window.location.href = '?page=evaluation_view&id=' + evaluationId;
}

// Prevent double-clicking on assign evaluation button
document.addEventListener('DOMContentLoaded', function() {
    const assignBtn = document.querySelector('button[name="assign_evaluation"]');
    if (assignBtn) {
        assignBtn.addEventListener('click', function(e) {
            // Disable button to prevent double submission
            this.disabled = true;
            this.innerHTML = '<i class="fe fe-loader fe-16 mr-2"></i>Assigning...';
            
            // Re-enable after 3 seconds in case of error
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fe fe-plus fe-16 mr-2"></i>Assign Evaluation';
            }, 3000);
        });
    }
    
    // Auto-refresh evaluations list every 30 seconds
    setTimeout(function() {
        if (window.location.search.indexOf('success=1') === -1) {
            window.location.reload();
        }
    }, 30000);
});
</script>
