<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$learningManager = new LearningManager();

// Check if user is properly retrieved
if (!$current_user) {
    $error = 'Unable to retrieve user information. Please log in again.';
}

// Get employee's training enrollments
$enrollments = [];
$availableTrainings = [];

try {
    $db = getDB();
    
    // Get employee's training enrollments
    $stmt = $db->prepare("
        SELECT te.*, ts.session_name, ts.start_date, ts.end_date, ts.location, ts.status as session_status,
               tm.title as training_title, tm.description as training_description, tm.category, tm.type,
               u.first_name as trainer_first_name, u.last_name as trainer_last_name
        FROM training_enrollments te
        JOIN training_sessions ts ON te.session_id = ts.id
        JOIN training_modules tm ON ts.module_id = tm.id
        LEFT JOIN users u ON ts.trainer_id = u.id
        WHERE te.employee_id = ?
        ORDER BY te.enrollment_date DESC
    ");
    $stmt->execute([$current_user['id']]);
    $enrollments = $stmt->fetchAll();
    
    // Get available trainings for enrollment
    $stmt = $db->prepare("
        SELECT ts.*, tm.title as training_title, tm.description, tm.category, tm.type,
               u.first_name as trainer_first_name, u.last_name as trainer_last_name,
               COUNT(te.id) as current_enrollments
        FROM training_sessions ts
        JOIN training_modules tm ON ts.module_id = tm.id
        LEFT JOIN users u ON ts.trainer_id = u.id
        LEFT JOIN training_enrollments te ON ts.id = te.session_id
        WHERE ts.status = 'scheduled' AND ts.start_date > NOW()
        GROUP BY ts.id
        HAVING current_enrollments < ts.max_participants OR ts.max_participants IS NULL
        ORDER BY ts.start_date ASC
    ");
    $stmt->execute();
    $availableTrainings = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Error fetching training data: " . $e->getMessage());
}

$message = '';
$error = '';

// Handle training enrollment
if ($_POST && isset($_POST['enroll_training'])) {
    $sessionId = $_POST['session_id'];
    
    try {
        $stmt = $db->prepare("
            INSERT INTO training_enrollments (employee_id, session_id, enrollment_date, status) 
            VALUES (?, ?, NOW(), 'enrolled')
        ");
        
        if ($stmt->execute([$current_user['id'], $sessionId])) {
            $message = 'Successfully enrolled in training!';
            $auth->logActivity('enroll_training', 'training_enrollments', $db->lastInsertId(), null, ['session_id' => $sessionId]);
            // Refresh enrollments
            header('Location: ?page=my_trainings&success=1');
            exit;
        } else {
            $error = 'Failed to enroll in training.';
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage();
    }
}

// Handle success message
if (isset($_GET['success'])) {
    $message = 'Successfully enrolled in training!';
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Trainings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enrollModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Enroll in Training
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


<!-- Training Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary"><?php echo count($enrollments); ?></h5>
                <p class="card-text">Total Enrollments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success"><?php echo count(array_filter($enrollments, function($e) { return ($e['completion_status'] ?? $e['status']) === 'completed'; })); ?></h5>
                <p class="card-text">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning"><?php echo count(array_filter($enrollments, function($e) { 
                    $status = $e['completion_status'] ?? $e['status'];
                    return $status === 'enrolled' || $status === 'in_progress' || $status === 'not_started';
                })); ?></h5>
                <p class="card-text">In Progress</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info"><?php echo count($availableTrainings); ?></h5>
                <p class="card-text">Available</p>
            </div>
        </div>
    </div>
</div>

<!-- My Training Enrollments -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">My Training History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($enrollments)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-book-open fe-48 text-muted mb-3"></i>
                        <h4 class="text-muted">No Training Records</h4>
                        <p class="text-muted">You haven't enrolled in any training sessions yet.</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#enrollModal">
                            Enroll in Your First Training
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Training</th>
                                    <th>Session</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $enrollment): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($enrollment['training_title'] ?? 'Untitled Training'); ?></strong>
                                                <div class="text-muted small"><?php echo htmlspecialchars($enrollment['category'] ?? 'N/A'); ?></div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($enrollment['session_name'] ?? 'Training Session'); ?></td>
                                        <td>
                                            <?php if ($enrollment['start_date']): ?>
                                                <strong><?php echo date('M d, Y', strtotime($enrollment['start_date'])); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <?php echo date('g:i A', strtotime($enrollment['start_date'])); ?> - 
                                                    <?php echo date('g:i A', strtotime($enrollment['end_date'] ?? $enrollment['start_date'])); ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">TBD</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($enrollment['location'] ?? 'TBD'); ?></td>
                                        <td>
                                            <?php
                                            // Use completion_status if available, otherwise fall back to status
                                            $displayStatus = $enrollment['completion_status'] ?? $enrollment['status'];
                                            $statusClass = [
                                                'enrolled' => 'badge-info',
                                                'completed' => 'badge-success',
                                                'cancelled' => 'badge-danger',
                                                'failed' => 'badge-danger',
                                                'in_progress' => 'badge-warning',
                                                'not_started' => 'badge-secondary'
                                            ][$displayStatus] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($displayStatus ?? 'Unknown'); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($enrollment['score']): ?>
                                                <span class="badge badge-primary"><?php echo $enrollment['score']; ?>%</span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewTrainingDetails(<?php echo $enrollment['id']; ?>)">
                                                    <i class="fe fe-eye fe-12"></i>
                                                </button>
                                                <?php if ($enrollment['status'] === 'enrolled'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelEnrollment(<?php echo $enrollment['id']; ?>)">
                                                        <i class="fe fe-x fe-12"></i>
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

<!-- Enroll in Training Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enroll in Training</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <?php if (empty($availableTrainings)): ?>
                        <div class="text-center py-4">
                            <i class="fe fe-calendar fe-48 text-muted mb-3"></i>
                            <h4 class="text-muted">No Available Trainings</h4>
                            <p class="text-muted">There are currently no training sessions available for enrollment.</p>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="session_id">Select Training Session</label>
                            <select class="form-control" id="session_id" name="session_id" required>
                                <option value="">Choose a training session...</option>
                                <?php foreach ($availableTrainings as $training): ?>
                                    <option value="<?php echo $training['id']; ?>">
                                        <?php echo htmlspecialchars($training['training_title']); ?> - 
                                        <?php echo date('M d, Y', strtotime($training['start_date'])); ?> 
                                        (<?php echo date('g:i A', strtotime($training['start_date'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div id="trainingDetails" class="mt-3" style="display: none;">
                            <!-- Training details will be populated here -->
                        </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <?php if (!empty($availableTrainings)): ?>
                        <button type="submit" name="enroll_training" class="btn btn-primary">Enroll in Training</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewTrainingDetails(enrollmentId) {
    alert('Training details functionality will be implemented soon. Enrollment ID: ' + enrollmentId);
}

function cancelEnrollment(enrollmentId) {
    if (confirm('Are you sure you want to cancel this enrollment?')) {
        // Implement cancellation logic
        alert('Enrollment cancellation functionality will be implemented soon. Enrollment ID: ' + enrollmentId);
    }
}

// Show training details when session is selected
document.getElementById('session_id').addEventListener('change', function() {
    const sessionId = this.value;
    const detailsDiv = document.getElementById('trainingDetails');
    
    if (sessionId) {
        // You can implement AJAX call here to fetch training details
        detailsDiv.style.display = 'block';
        detailsDiv.innerHTML = '<div class="alert alert-info">Training details will be loaded here.</div>';
    } else {
        detailsDiv.style.display = 'none';
    }
});
</script>


