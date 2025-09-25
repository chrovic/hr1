<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';

$auth = new SimpleAuth();

// Check permissions (no redirect needed, handled in index.php)
if (!$auth->hasPermission('manage_training')) {
    $error = 'You do not have permission to access this page.';
    // Don't redirect, just show error
}

$current_user = $auth->getCurrentUser();
$learningManager = new LearningManager();
$db = getDB();

$message = '';
$error = '';

// Handle feedback submission
if ($_POST && isset($_POST['submit_feedback'])) {
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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_session'])) {
        $sessionData = [
            'training_id' => $_POST['training_id'],
            'session_name' => $_POST['session_name'],
            'session_date' => $_POST['session_date'],
            'start_time' => $_POST['start_time'],
            'end_time' => $_POST['end_time'],
            'location' => $_POST['location'],
            'max_participants' => $_POST['max_participants'],
            'status' => $_POST['status'],
            'created_by' => $current_user['id']
        ];
        
        if ($learningManager->scheduleSession($sessionData)) {
            $message = 'Training session created successfully!';
            $auth->logActivity('create_session', 'training_sessions', null, null, $sessionData);
        } else {
            $error = 'Failed to create training session.';
        }
    }
    
    if (isset($_POST['enroll_employee'])) {
        $enrollmentData = [
            'session_id' => $_POST['session_id'],
            'employee_id' => $_POST['employee_id'],
            'enrollment_date' => date('Y-m-d'),
            'attendance_status' => 'enrolled',
            'completion_status' => 'not_started'
        ];
        
        if ($learningManager->enrollEmployee($enrollmentData)) {
            $message = 'Employee enrolled successfully!';
            $auth->logActivity('enroll_employee', 'training_enrollments', null, null, $enrollmentData);
        } else {
            $error = 'Failed to enroll employee.';
        }
    }
    
    if (isset($_POST['update_session'])) {
        $sessionId = $_POST['edit_session_id'];
        $updateData = [
            'module_id' => $_POST['edit_training_id'],
            'session_name' => $_POST['edit_session_name'],
            'description' => $_POST['edit_description'],
            'start_date' => $_POST['edit_start_date'],
            'end_date' => $_POST['edit_end_date'],
            'location' => $_POST['edit_location'],
            'max_participants' => $_POST['edit_max_participants'],
            'status' => $_POST['edit_status']
        ];
        
        try {
            $stmt = $db->prepare("
                UPDATE training_sessions 
                SET module_id = ?, session_name = ?, description = ?, 
                    start_date = ?, end_date = ?, location = ?, 
                    max_participants = ?, status = ?
                WHERE id = ?
            ");
            
            if ($stmt->execute([
                $updateData['module_id'],
                $updateData['session_name'],
                $updateData['description'],
                $updateData['start_date'],
                $updateData['end_date'],
                $updateData['location'],
                $updateData['max_participants'],
                $updateData['status'],
                $sessionId
            ])) {
                $message = 'Training session updated successfully!';
                $auth->logActivity('update_session', 'training_sessions', $sessionId, null, $updateData);
            } else {
                $error = 'Failed to update training session.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get data for display
try {
    $sessions = $learningManager->getAllSessions();
    $enrollments = $learningManager->getAllEnrollments();
    $trainings = $learningManager->getAllTrainings();
    
    // Calculate statistics
    $total_sessions = count($sessions);
    $total_enrollments = count($enrollments);
    $completed_sessions = count(array_filter($sessions, function($s) { return $s['status'] === 'completed'; }));
    $active_enrollments = count(array_filter($enrollments, function($e) { return ($e['status'] ?? '') === 'enrolled'; }));
    
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $sessions = [];
    $enrollments = [];
    $trainings = [];
    $total_sessions = 0;
    $total_enrollments = 0;
    $completed_sessions = 0;
    $active_enrollments = 0;
}
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Training Management</h4>
                <div class="page-title-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#scheduleTrainingModal">
                        <span class="fe fe-plus fe-16 mr-2"></span>Schedule Training
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fe fe-check-circle fe-16 mr-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fe fe-alert-circle fe-16 mr-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Total Sessions</h6>
                            <span class="h2 mb-0"><?php echo $total_sessions; ?></span>
                        </div>
                        <div class="col-auto">
                            <span class="fe fe-calendar fe-24 text-primary"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Completed Sessions</h6>
                            <span class="h2 mb-0"><?php echo $completed_sessions; ?></span>
                        </div>
                        <div class="col-auto">
                            <span class="fe fe-check-circle fe-24 text-success"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Total Enrollments</h6>
                            <span class="h2 mb-0"><?php echo $total_enrollments; ?></span>
                        </div>
                        <div class="col-auto">
                            <span class="fe fe-users fe-24 text-info"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="text-uppercase text-muted mb-2">Active Enrollments</h6>
                            <span class="h2 mb-0"><?php echo $active_enrollments; ?></span>
                        </div>
                        <div class="col-auto">
                            <span class="fe fe-trending-up fe-24 text-warning"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Training Sessions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <strong class="card-title">Training Sessions</strong>
                    <button class="btn btn-primary float-right" data-toggle="modal" data-target="#scheduleTrainingModal">
                        <span class="fe fe-plus fe-16 mr-2"></span>Schedule Training
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Training</th>
                                    <th>Trainer</th>
                                    <th>Date & Time</th>
                                    <th>Location</th>
                                    <th>Participants</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sessions)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fe fe-calendar fe-48 mb-3"></i>
                                            <h4 class="text-muted">No Training Sessions</h4>
                                            <p class="text-muted">No training sessions have been scheduled yet.</p>
                                            <button class="btn btn-primary" data-toggle="modal" data-target="#scheduleTrainingModal">
                                                <i class="fe fe-plus fe-16 mr-2"></i>Schedule Your First Session
                                            </button>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($sessions as $session): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($session['training_title'] ?? 'Untitled Training'); ?></strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($session['session_name'] ?? 'Training Session'); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm mr-2">
                                                        <span class="avatar-title bg-primary rounded-circle">
                                                            <?php echo strtoupper(substr($session['trainer_first_name'] ?? 'T', 0, 1) . substr($session['trainer_last_name'] ?? 'R', 0, 1)); ?>
                                                        </span>
                                                    </div>
                                                    <span><?php echo htmlspecialchars(($session['trainer_first_name'] ?? 'Unknown') . ' ' . ($session['trainer_last_name'] ?? 'Trainer')); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div><?php echo date('M d, Y', strtotime($session['start_date'])); ?></div>
                                                <div class="text-muted small">
                                                    <?php echo date('g:i A', strtotime($session['start_date'])); ?> - 
                                                    <?php echo date('g:i A', strtotime($session['end_date'])); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($session['location'] ?? 'TBD'); ?></td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo $session['enrollment_count'] ?? '0'; ?>/<?php echo $session['max_participants'] ?? 'N/A'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = [
                                                    'scheduled' => 'badge-warning',
                                                    'active' => 'badge-success',
                                                    'completed' => 'badge-primary',
                                                    'cancelled' => 'badge-danger'
                                                ][$session['status']] ?? 'badge-secondary';
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo ucfirst($session['status'] ?? 'Unknown'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editSession(<?php echo $session['id']; ?>)">
                                                        <i class="fe fe-edit fe-14"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewSession(<?php echo $session['id']; ?>)">
                                                        <i class="fe fe-eye fe-14"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-success" onclick="manageEnrollments(<?php echo $session['id']; ?>)">
                                                        <i class="fe fe-users fe-14"></i>
                                                    </button>
                                                    <a href="?page=training_feedback_management" class="btn btn-sm btn-outline-warning" title="Give Feedback">
                                                        <i class="fe fe-star fe-14"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Training Programs and Calendar -->
    <div class="row">
        <div class="col-md-8 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <strong class="card-title">Training Calendar</strong>
                </div>
                <div class="card-body">
                    <div id="trainingCalendar"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card shadow">
                <div class="card-header">
                    <strong class="card-title">Training Evaluations</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Training</th>
                                    <th>Rating</th>
                                    <th>Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($enrollments)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            <i class="fe fe-star fe-24 mb-2"></i>
                                            <div>No training evaluations yet</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    // Show only completed enrollments with feedback
                                    $completedEnrollments = array_filter($enrollments, function($e) { 
                                        return ($e['status'] ?? '') === 'completed'; 
                                    });
                                    $completedEnrollments = array_slice($completedEnrollments, 0, 5); // Show only first 5
                                    ?>
                                    <?php if (empty($completedEnrollments)): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-3">
                                                <i class="fe fe-check-circle fe-24 mb-2"></i>
                                                <div>No completed training sessions yet</div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($completedEnrollments as $enrollment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($enrollment['training_title'] ?? 'Training Session'); ?></td>
                                                <td>
                                                    <div class="d-flex">
                                                        <?php 
                                                        $rating = rand(3, 5); // Random rating for demo
                                                        for ($i = 1; $i <= 5; $i++): 
                                                            $class = $i <= $rating ? 'text-warning' : 'text-muted';
                                                        ?>
                                                            <span class="fe fe-star fe-12 <?php echo $class; ?>"></span>
                                                        <?php endfor; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewFeedback(<?php echo $enrollment['id']; ?>)">
                                                        <i class="fe fe-eye fe-14"></i> View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Training Modal -->
<div class="modal fade" id="scheduleTrainingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Schedule Training Session</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="training_id">Training Program</label>
                                <select class="form-control" name="training_id" id="training_id" required>
                                    <option value="">Select Training Program</option>
                                    <?php foreach ($trainings as $training): ?>
                                        <option value="<?php echo $training['id']; ?>">
                                            <?php echo htmlspecialchars($training['title'] ?? 'Untitled'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="session_name">Session Name</label>
                                <input type="text" class="form-control" name="session_name" id="session_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="session_date">Session Date</label>
                                <input type="date" class="form-control" name="session_date" id="session_date" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="time" class="form-control" name="start_time" id="start_time" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="time" class="form-control" name="end_time" id="end_time" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" class="form-control" name="location" id="location" placeholder="Conference Room A">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="max_participants">Max Participants</label>
                                <input type="number" class="form-control" name="max_participants" id="max_participants" value="20" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_session" class="btn btn-primary">Schedule Training</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enroll Employee Modal -->
<div class="modal fade" id="enrollEmployeeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enroll Employee</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="session_id">Training Session</label>
                        <select class="form-control" name="session_id" id="session_id" required>
                            <option value="">Select Session</option>
                            <?php foreach ($sessions as $session): ?>
                                <option value="<?php echo $session['id']; ?>">
                                    <?php echo htmlspecialchars($session['training_title'] ?? 'Untitled'); ?> - 
                                    <?php echo date('M d, Y', strtotime($session['start_date'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="employee_id">Employee</label>
                        <select class="form-control" name="employee_id" id="employee_id" required>
                            <option value="">Select Employee</option>
                            <?php
                            // Get all employees
                            try {
                                $stmt = $db->prepare("SELECT id, first_name, last_name, email FROM users WHERE role = 'employee' ORDER BY first_name, last_name");
                                $stmt->execute();
                                $employees = $stmt->fetchAll();
                                foreach ($employees as $employee):
                            ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                                    (<?php echo htmlspecialchars($employee['email']); ?>)
                                </option>
                            <?php 
                                endforeach;
                            } catch (PDOException $e) {
                                // Handle error silently
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="enroll_employee" class="btn btn-primary">Enroll Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Training Session Modal -->
<div class="modal fade" id="viewSessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Training Session Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 id="view_session_title" class="mb-3"></h4>
                        <div class="mb-3">
                            <h6 class="text-muted">Session Name</h6>
                            <p id="view_session_name" class="mb-2"></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="text-muted">Description</h6>
                            <p id="view_session_description" class="text-justify"></p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Date & Time</h6>
                                <div id="view_session_datetime"></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Location</h6>
                                <div id="view_session_location"></div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Participants</h6>
                                <span id="view_session_participants" class="badge badge-info"></span>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Status</h6>
                                <span id="view_session_status" class="badge"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Session Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted">Trainer</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm mr-2">
                                            <span class="avatar-title bg-primary rounded-circle" id="view_trainer_initials"></span>
                                        </div>
                                        <span id="view_trainer_name"></span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted">Created By</h6>
                                    <span id="view_creator_name"></span>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted">Created Date</h6>
                                    <span id="view_created_date"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="editSessionFromView()">
                    <i class="fe fe-edit fe-14 mr-1"></i>Edit Session
                </button>
                <button type="button" class="btn btn-success" onclick="manageEnrollmentsFromView()">
                    <i class="fe fe-users fe-14 mr-1"></i>Manage Enrollments
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Training Feedback</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Training</h6>
                        <h5 id="view_feedback_training"></h5>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Employee</h6>
                        <h5 id="view_feedback_employee"></h5>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6 class="text-muted">Rating</h6>
                        <div id="view_feedback_rating"></div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Completion Date</h6>
                        <span id="view_feedback_completion_date"></span>
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
                <div class="mb-3">
                    <h6 class="text-muted">Score</h6>
                    <div class="progress">
                        <div class="progress-bar" id="view_feedback_score_bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted" id="view_feedback_score_text"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Training Session Modal -->
<div class="modal fade" id="editSessionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Training Session</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_session_id" id="edit_session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_training_id">Training Program</label>
                                <select class="form-control" name="edit_training_id" id="edit_training_id" required>
                                    <option value="">Select Training Program</option>
                                    <?php foreach ($trainings as $training): ?>
                                        <option value="<?php echo $training['id']; ?>">
                                            <?php echo htmlspecialchars($training['title'] ?? 'Untitled'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_session_name">Session Name</label>
                                <input type="text" class="form-control" name="edit_session_name" id="edit_session_name" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description">Description</label>
                        <textarea class="form-control" name="edit_description" id="edit_description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_start_date">Start Date & Time</label>
                                <input type="datetime-local" class="form-control" name="edit_start_date" id="edit_start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_end_date">End Date & Time</label>
                                <input type="datetime-local" class="form-control" name="edit_end_date" id="edit_end_date" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_location">Location</label>
                                <input type="text" class="form-control" name="edit_location" id="edit_location" placeholder="Conference Room A">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_max_participants">Max Participants</label>
                                <input type="number" class="form-control" name="edit_max_participants" id="edit_max_participants" min="1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="edit_status">Status</label>
                                <select class="form-control" name="edit_status" id="edit_status">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_session" class="btn btn-primary">Update Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Enrollments Modal -->
<div class="modal fade" id="manageEnrollmentsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Session Enrollments</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <h6 class="text-muted">Session: <span id="enrollment_session_title"></span></h6>
                        <small class="text-muted">Date: <span id="enrollment_session_date"></span></small>
                    </div>
                    <div class="col-md-4 text-right">
                        <button class="btn btn-primary" onclick="openEnrollEmployeeModal()">
                            <i class="fe fe-plus fe-14 mr-1"></i>Enroll Employee
                        </button>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Enrollment Date</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="enrollments_table_body">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fe fe-users fe-48 mb-3"></i>
                                    <h4 class="text-muted">Loading enrollments...</h4>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                        <label for="feedback_score">Score (0-100)</label>
                        <input type="number" class="form-control" id="feedback_score" name="score" min="0" max="100" required>
                        <small class="form-text text-muted">Enter a score between 0 and 100</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback_completion_status">Completion Status</label>
                        <select class="form-control" id="feedback_completion_status" name="completion_status" required>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="in_progress">In Progress</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="feedback_text">Feedback</label>
                        <textarea class="form-control" id="feedback_text" name="feedback" rows="5" placeholder="Provide detailed feedback about the employee's performance, areas of strength, and areas for improvement..." required></textarea>
                    </div>
                    
                    <input type="hidden" name="enrollment_id" id="feedback_enrollment_id">
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

<script>
// JavaScript functions for interactive features
function editSession(sessionId) {
    // Fetch session details and populate edit form
    fetch('includes/ajax/ajax_get_session_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'session_id=' + sessionId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const session = data.session;
            
            // Populate edit form with session data
            document.getElementById('edit_session_id').value = session.id;
            document.getElementById('edit_training_id').value = session.module_id || '';
            document.getElementById('edit_session_name').value = session.session_name || '';
            document.getElementById('edit_description').value = session.description || '';
            
            // Format datetime for input fields
            const startDate = new Date(session.start_date);
            const endDate = new Date(session.end_date);
            const startDateTime = startDate.toISOString().slice(0, 16);
            const endDateTime = endDate.toISOString().slice(0, 16);
            
            document.getElementById('edit_start_date').value = startDateTime;
            document.getElementById('edit_end_date').value = endDateTime;
            document.getElementById('edit_location').value = session.location || '';
            document.getElementById('edit_max_participants').value = session.max_participants || '';
            document.getElementById('edit_status').value = session.status || 'scheduled';
            
            // Show edit modal
            $('#editSessionModal').modal('show');
        } else {
            alert('Failed to load session details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load session details. Please try again.');
    });
}

function viewSession(sessionId) {
    // Fetch session details via AJAX
    fetch('includes/ajax/ajax_get_session_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'session_id=' + sessionId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const session = data.session;
            
            // Populate view modal with session data
            document.getElementById('view_session_title').textContent = session.training_title || 'Untitled Training';
            document.getElementById('view_session_name').textContent = session.session_name || 'Training Session';
            document.getElementById('view_session_description').textContent = session.description || 'No description available';
            
            // Format date and time
            const startDate = new Date(session.start_date);
            const endDate = new Date(session.end_date);
            const dateTimeHtml = `
                <strong>${startDate.toLocaleDateString()}</strong><br>
                <small class="text-muted">
                    ${startDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - 
                    ${endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </small>
            `;
            document.getElementById('view_session_datetime').innerHTML = dateTimeHtml;
            
            document.getElementById('view_session_location').textContent = session.location || 'TBD';
            document.getElementById('view_session_participants').textContent = 
                `${session.enrollment_count || '0'}/${session.max_participants || 'N/A'}`;
            
            // Set status badge
            const statusClass = {
                'scheduled': 'badge-warning',
                'active': 'badge-success',
                'completed': 'badge-primary',
                'cancelled': 'badge-danger'
            }[session.status] || 'badge-secondary';
            document.getElementById('view_session_status').textContent = session.status || 'Unknown';
            document.getElementById('view_session_status').className = 'badge ' + statusClass;
            
            // Trainer information
            const trainerName = `${session.trainer_first_name || 'Unknown'} ${session.trainer_last_name || 'Trainer'}`;
            document.getElementById('view_trainer_name').textContent = trainerName;
            document.getElementById('view_trainer_initials').textContent = 
                (session.trainer_first_name || 'T').charAt(0).toUpperCase() + 
                (session.trainer_last_name || 'R').charAt(0).toUpperCase();
            
            // Creator information
            const creatorName = `${session.creator_first_name || 'Unknown'} ${session.creator_last_name || 'User'}`;
            document.getElementById('view_creator_name').textContent = creatorName;
            
            // Created date
            const createdDate = new Date(session.created_at);
            document.getElementById('view_created_date').textContent = createdDate.toLocaleDateString();
            
            // Store session data for other functions
            window.currentSession = session;
            
            // Show view modal
            $('#viewSessionModal').modal('show');
        } else {
            alert('Failed to load session details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load session details. Please try again.');
    });
}

function editSessionFromView() {
    // Close view modal and open edit modal with current session data
    $('#viewSessionModal').modal('hide');
    if (window.currentSession) {
        editSession(window.currentSession.id);
    }
}

function manageEnrollmentsFromView() {
    // Close view modal and manage enrollments
    $('#viewSessionModal').modal('hide');
    if (window.currentSession) {
        manageEnrollments(window.currentSession.id);
    }
}

function manageEnrollments(sessionId) {
    // Fetch session details and enrollments
    Promise.all([
        fetch('includes/ajax/ajax_get_session_details.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'session_id=' + sessionId
        }).then(response => response.json()),
        fetch('includes/ajax/ajax_get_session_enrollments.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'session_id=' + sessionId
        }).then(response => response.json())
    ])
    .then(([sessionData, enrollmentsData]) => {
        if (sessionData.success && enrollmentsData.success) {
            const session = sessionData.session;
            const enrollments = enrollmentsData.enrollments;
            
            // Populate session info
            document.getElementById('enrollment_session_title').textContent = session.training_title || 'Training Session';
            const sessionDate = new Date(session.start_date);
            document.getElementById('enrollment_session_date').textContent = sessionDate.toLocaleDateString();
            
            // Populate enrollments table
            const tbody = document.getElementById('enrollments_table_body');
            if (enrollments.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fe fe-users fe-48 mb-3"></i>
                            <h4 class="text-muted">No Enrollments</h4>
                            <p class="text-muted">No employees are enrolled in this session yet.</p>
                        </td>
                    </tr>
                `;
            } else {
                tbody.innerHTML = enrollments.map(enrollment => `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm mr-2">
                                    <span class="avatar-title bg-primary rounded-circle">
                                        ${(enrollment.employee_first_name || 'E').charAt(0).toUpperCase()}${(enrollment.employee_last_name || 'M').charAt(0).toUpperCase()}
                                    </span>
                                </div>
                                <span>${enrollment.employee_first_name || 'Unknown'} ${enrollment.employee_last_name || 'Employee'}</span>
                            </div>
                        </td>
                        <td>${enrollment.employee_email || 'N/A'}</td>
                        <td>${enrollment.employee_department || 'N/A'}</td>
                        <td>${new Date(enrollment.enrollment_date).toLocaleDateString()}</td>
                        <td>
                            <span class="badge badge-${enrollment.status === 'completed' ? 'success' : enrollment.status === 'enrolled' ? 'info' : 'warning'}">
                                ${enrollment.status || 'Unknown'}
                            </span>
                        </td>
                        <td>${enrollment.score ? enrollment.score + '%' : 'N/A'}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-info" onclick="viewEnrollmentDetails(${enrollment.id})" title="View Details">
                                    <i class="fe fe-eye fe-14"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning" onclick="openFeedbackModal(${enrollment.id})" title="Give Feedback">
                                    <i class="fe fe-star fe-14"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="removeEnrollment(${enrollment.id})" title="Remove Enrollment">
                                    <i class="fe fe-trash fe-14"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
            
            // Store current session ID for enrollment actions
            window.currentSessionId = sessionId;
            
            // Show manage enrollments modal
            $('#manageEnrollmentsModal').modal('show');
        } else {
            alert('Failed to load session or enrollment data: ' + (sessionData.message || enrollmentsData.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load enrollment data. Please try again.');
    });
}

function openEnrollEmployeeModal() {
    // Close manage enrollments modal and open enroll employee modal
    $('#manageEnrollmentsModal').modal('hide');
    setTimeout(() => {
        $('#enrollEmployeeModal').modal('show');
    }, 300);
}

function viewEnrollmentDetails(enrollmentId) {
    // Use the existing viewFeedback function
    viewFeedback(enrollmentId);
}

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
            document.getElementById('feedback_enrollment_id').value = enrollmentId;
            document.getElementById('feedback_employee_name').textContent = 
                `${enrollment.employee_first_name || 'Unknown'} ${enrollment.employee_last_name || 'Employee'}`;
            document.getElementById('feedback_training_title').textContent = enrollment.training_title || 'Training Session';
            document.getElementById('feedback_score').value = enrollment.score || '';
            document.getElementById('feedback_completion_status').value = enrollment.completion_status || 'completed';
            document.getElementById('feedback_text').value = enrollment.feedback || '';
            
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

function removeEnrollment(enrollmentId) {
    if (confirm('Are you sure you want to remove this enrollment?')) {
        fetch('includes/ajax/ajax_remove_enrollment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'enrollment_id=' + enrollmentId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Enrollment removed successfully!');
                // Refresh the enrollments table
                if (window.currentSessionId) {
                    manageEnrollments(window.currentSessionId);
                }
            } else {
                alert('Failed to remove enrollment: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to remove enrollment. Please try again.');
        });
    }
}

function viewFeedback(enrollmentId) {
    // Fetch enrollment/feedback details via AJAX
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
            
            // Populate view modal with enrollment data
            document.getElementById('view_feedback_training').textContent = enrollment.training_title || 'Training Session';
            document.getElementById('view_feedback_employee').textContent = 
                `${enrollment.employee_first_name || 'Unknown'} ${enrollment.employee_last_name || 'Employee'}`;
            
            // Generate random rating for demo (since we don't have actual ratings)
            const rating = Math.floor(Math.random() * 3) + 3; // 3-5 stars
            let ratingHtml = '<div class="d-flex">';
            for (let i = 1; i <= 5; i++) {
                const starClass = i <= rating ? 'text-warning' : 'text-muted';
                ratingHtml += `<span class="fe fe-star fe-12 ${starClass}"></span>`;
            }
            ratingHtml += '</div>';
            document.getElementById('view_feedback_rating').innerHTML = ratingHtml;
            
            // Completion date
            if (enrollment.completion_date) {
                const completionDate = new Date(enrollment.completion_date);
                document.getElementById('view_feedback_completion_date').textContent = completionDate.toLocaleDateString();
            } else {
                document.getElementById('view_feedback_completion_date').textContent = 'Not completed';
            }
            
            // Feedback text
            const feedbackText = enrollment.feedback || 'No feedback provided yet.';
            document.getElementById('view_feedback_text').textContent = feedbackText;
            
            // Score visualization
            const score = enrollment.score || Math.floor(Math.random() * 40) + 60; // Random score 60-100
            document.getElementById('view_feedback_score_bar').style.width = score + '%';
            document.getElementById('view_feedback_score_text').textContent = `${score}%`;
            
            // Set progress bar color based on score
            const scoreBar = document.getElementById('view_feedback_score_bar');
            if (score >= 90) {
                scoreBar.className = 'progress-bar bg-success';
            } else if (score >= 70) {
                scoreBar.className = 'progress-bar bg-warning';
            } else {
                scoreBar.className = 'progress-bar bg-danger';
            }
            
            // Close manage enrollments modal if it's open, then show feedback modal
            $('#manageEnrollmentsModal').modal('hide');
            setTimeout(() => {
                $('#viewFeedbackModal').modal('show');
            }, 300);
        } else {
            alert('Failed to load feedback details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load feedback details. Please try again.');
    });
}

// Initialize calendar (placeholder)
document.addEventListener('DOMContentLoaded', function() {
    // TODO: Initialize training calendar
    console.log('Training calendar will be initialized here');
});
</script>