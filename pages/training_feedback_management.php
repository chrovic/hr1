<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$learningManager = new LearningManager();
$db = getDB();

$message = '';
$error = '';

// Handle feedback submission
if ($_POST) {
    if (isset($_POST['submit_feedback'])) {
        $enrollmentId = $_POST['enrollment_id'];
        $score = $_POST['score'];
        $feedback = $_POST['feedback'];
        $completionStatus = $_POST['completion_status'];
        
        try {
            $stmt = $db->prepare("
                UPDATE training_enrollments 
                SET score = ?, feedback = ?, completion_status = ?, completion_date = NOW(), updated_at = NOW()
                WHERE id = ?
            ");
            
            if ($stmt->execute([$score, $feedback, $completionStatus, $enrollmentId])) {
                $message = 'Feedback and score submitted successfully!';
                $auth->logActivity('submit_training_feedback', 'training_enrollments', $enrollmentId, null, [
                    'score' => $score,
                    'completion_status' => $completionStatus
                ]);
            } else {
                $error = 'Failed to submit feedback.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get enrollments that need feedback
$enrollmentsNeedingFeedback = [];
$completedEnrollments = [];

try {
    // Get enrollments that are completed but don't have scores/feedback
    $stmt = $db->prepare("
        SELECT te.*, ts.session_name, ts.start_date, ts.end_date, ts.location,
               tm.title as training_title, tm.description as training_description,
               u.first_name, u.last_name, u.email, u.department, u.position
        FROM training_enrollments te
        JOIN training_sessions ts ON te.session_id = ts.id
        JOIN training_modules tm ON ts.module_id = tm.id
        JOIN users u ON te.employee_id = u.id
        WHERE te.completion_status = 'completed' AND (te.score IS NULL OR te.feedback IS NULL)
        ORDER BY te.enrollment_date DESC
    ");
    $stmt->execute();
    $enrollmentsNeedingFeedback = $stmt->fetchAll();
    
    // Get all completed enrollments with feedback
    $stmt = $db->prepare("
        SELECT te.*, ts.session_name, ts.start_date, ts.end_date, ts.location,
               tm.title as training_title, tm.description as training_description,
               u.first_name, u.last_name, u.email, u.department, u.position
        FROM training_enrollments te
        JOIN training_sessions ts ON te.session_id = ts.id
        JOIN training_modules tm ON ts.module_id = tm.id
        JOIN users u ON te.employee_id = u.id
        WHERE te.completion_status = 'completed' AND te.score IS NOT NULL AND te.feedback IS NOT NULL
        ORDER BY te.completion_date DESC
    ");
    $stmt->execute();
    $completedEnrollments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Error fetching enrollment data: ' . $e->getMessage();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Training Feedback Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fe fe-refresh-cw fe-16 mr-2"></i>Refresh
            </button>
        </div>
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

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning"><?php echo count($enrollmentsNeedingFeedback); ?></h5>
                <p class="card-text">Pending Feedback</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success"><?php echo count($completedEnrollments); ?></h5>
                <p class="card-text">Completed with Feedback</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info"><?php echo count($enrollmentsNeedingFeedback) + count($completedEnrollments); ?></h5>
                <p class="card-text">Total Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary">
                    <?php 
                    $totalScore = 0;
                    $scoreCount = 0;
                    foreach ($completedEnrollments as $enrollment) {
                        if ($enrollment['score']) {
                            $totalScore += $enrollment['score'];
                            $scoreCount++;
                        }
                    }
                    echo $scoreCount > 0 ? round($totalScore / $scoreCount, 1) : '0';
                    ?>
                </h5>
                <p class="card-text">Average Score</p>
            </div>
        </div>
    </div>
</div>

<!-- Pending Feedback Section -->
<?php if (!empty($enrollmentsNeedingFeedback)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Enrollments Pending Feedback</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Training</th>
                                <th>Session</th>
                                <th>Completion Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollmentsNeedingFeedback as $enrollment): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm mr-3">
                                                <span class="avatar-title bg-primary rounded-circle">
                                                    <?php echo strtoupper(substr($enrollment['first_name'], 0, 1) . substr($enrollment['last_name'], 0, 1)); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($enrollment['email']); ?></small>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($enrollment['department']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($enrollment['training_title']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars(substr($enrollment['training_description'], 0, 50)) . '...'; ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($enrollment['session_name']); ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($enrollment['start_date'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo $enrollment['completion_date'] ? date('M d, Y', strtotime($enrollment['completion_date'])) : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="openFeedbackModal(<?php echo $enrollment['id']; ?>)">
                                            <i class="fe fe-edit-2 fe-14"></i> Add Feedback
                                        </button>
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

<!-- Completed with Feedback Section -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Completed Training with Feedback</h5>
            </div>
            <div class="card-body">
                <?php if (empty($completedEnrollments)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-check-circle fe-48 text-muted mb-3"></i>
                        <h4 class="text-muted">No Completed Training with Feedback</h4>
                        <p class="text-muted">Completed training sessions with feedback will appear here.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Training</th>
                                    <th>Score</th>
                                    <th>Feedback</th>
                                    <th>Completion Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($completedEnrollments as $enrollment): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-3">
                                                    <span class="avatar-title bg-success rounded-circle">
                                                        <?php echo strtoupper(substr($enrollment['first_name'], 0, 1) . substr($enrollment['last_name'], 0, 1)); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($enrollment['department']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($enrollment['training_title']); ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge badge-<?php echo $enrollment['score'] >= 80 ? 'success' : ($enrollment['score'] >= 60 ? 'warning' : 'danger'); ?>">
                                                    <?php echo $enrollment['score']; ?>%
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($enrollment['feedback']); ?>">
                                                <?php echo htmlspecialchars(substr($enrollment['feedback'], 0, 50)) . (strlen($enrollment['feedback']) > 50 ? '...' : ''); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($enrollment['completion_date'])); ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" onclick="viewFeedback(<?php echo $enrollment['id']; ?>)">
                                                <i class="fe fe-eye fe-14"></i> View
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openFeedbackModal(<?php echo $enrollment['id']; ?>)">
                                                <i class="fe fe-edit-2 fe-14"></i> Edit
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

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Training Feedback & Scoring</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST" id="feedbackForm">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6 class="text-muted">Employee</h6>
                            <h5 id="feedback_employee_name"></h5>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Training</h6>
                            <h5 id="feedback_training_title"></h5>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="score">Score (0-100)</label>
                        <input type="number" class="form-control" id="score" name="score" min="0" max="100" required>
                        <small class="form-text text-muted">Enter a score between 0 and 100</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="completion_status">Completion Status</label>
                        <select class="form-control" id="completion_status" name="completion_status" required>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="in_progress">In Progress</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="5" placeholder="Provide detailed feedback about the employee's performance, areas of strength, and areas for improvement..." required></textarea>
                    </div>
                    
                    <input type="hidden" name="enrollment_id" id="enrollment_id">
                    <input type="hidden" name="submit_feedback" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Training Feedback Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Employee</h6>
                        <h5 id="view_employee_name"></h5>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Training</h6>
                        <h5 id="view_training_title"></h5>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Score</h6>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-lg" id="view_score_badge"></span>
                            <div class="progress ml-3" style="width: 100px; height: 8px;">
                                <div class="progress-bar" id="view_score_progress" role="progressbar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Completion Date</h6>
                        <span id="view_completion_date"></span>
                    </div>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-muted">Feedback</h6>
                    <div class="card">
                        <div class="card-body">
                            <p id="view_feedback_text" class="mb-0"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openFeedbackModal(enrollmentId) {
    // Fetch enrollment details
    fetch('includes/ajax/ajax_get_enrollment_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'enrollment_id=' + enrollmentId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const enrollment = data.enrollment;
            
            // Populate form fields
            document.getElementById('enrollment_id').value = enrollmentId;
            document.getElementById('feedback_employee_name').textContent = 
                `${enrollment.employee_first_name || 'Unknown'} ${enrollment.employee_last_name || 'Employee'}`;
            document.getElementById('feedback_training_title').textContent = enrollment.training_title || 'Training Session';
            document.getElementById('score').value = enrollment.score || '';
            document.getElementById('completion_status').value = enrollment.completion_status || 'completed';
            document.getElementById('feedback').value = enrollment.feedback || '';
            
            // Show modal
            $('#feedbackModal').modal('show');
        } else {
            alert('Failed to load enrollment details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load enrollment details. Please try again.');
    });
}

function viewFeedback(enrollmentId) {
    // Fetch enrollment details
    fetch('includes/ajax/ajax_get_enrollment_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'enrollment_id=' + enrollmentId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const enrollment = data.enrollment;
            
            // Populate view fields
            document.getElementById('view_employee_name').textContent = 
                `${enrollment.employee_first_name || 'Unknown'} ${enrollment.employee_last_name || 'Employee'}`;
            document.getElementById('view_training_title').textContent = enrollment.training_title || 'Training Session';
            
            const score = enrollment.score || 0;
            const scoreBadge = document.getElementById('view_score_badge');
            const scoreProgress = document.getElementById('view_score_progress');
            
            scoreBadge.textContent = score + '%';
            scoreBadge.className = 'badge badge-lg ' + (score >= 80 ? 'badge-success' : (score >= 60 ? 'badge-warning' : 'badge-danger'));
            
            scoreProgress.style.width = score + '%';
            scoreProgress.className = 'progress-bar ' + (score >= 80 ? 'bg-success' : (score >= 60 ? 'bg-warning' : 'bg-danger'));
            
            document.getElementById('view_completion_date').textContent = 
                enrollment.completion_date ? new Date(enrollment.completion_date).toLocaleDateString() : 'N/A';
            document.getElementById('view_feedback_text').textContent = enrollment.feedback || 'No feedback provided.';
            
            // Show modal
            $('#viewFeedbackModal').modal('show');
        } else {
            alert('Failed to load feedback details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load feedback details. Please try again.');
    });
}
</script>


