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

// Handle form submissions
if ($_POST) {
    if (isset($_POST['approve_request'])) {
        $requestId = $_POST['request_id'];
        $sessionId = $_POST['session_id'] ?? null;
        $employeeId = $_POST['employee_id'];
        $autoEnroll = isset($_POST['auto_enroll']) ? $_POST['auto_enroll'] : 'manual';
        $moduleId = $_POST['module_id'] ?? null;
        
        try {
            // Update request status
            $stmt = $db->prepare("UPDATE training_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->execute([$current_user['id'], $requestId]);
            
            $enrollmentMessage = '';
            
            // Handle different enrollment options
            if ($autoEnroll === 'immediate' && $sessionId) {
                // Immediate enrollment in specific session
                $stmt = $db->prepare("INSERT INTO training_enrollments (session_id, employee_id, enrollment_date, attendance_status, completion_status) VALUES (?, ?, NOW(), 'enrolled', 'not_started')");
                $stmt->execute([$sessionId, $employeeId]);
                $enrollmentMessage = ' Employee has been automatically enrolled in the selected training session.';
                
            } elseif ($autoEnroll === 'next_available' && $moduleId) {
                // Auto-enroll in next available session for this module
                $stmt = $db->prepare("
                    SELECT id FROM training_sessions 
                    WHERE module_id = ? AND start_date > NOW() AND status = 'scheduled'
                    AND (max_participants IS NULL OR 
                         (SELECT COUNT(*) FROM training_enrollments WHERE session_id = training_sessions.id) < max_participants)
                    ORDER BY start_date ASC 
                    LIMIT 1
                ");
                $stmt->execute([$moduleId]);
                $nextSession = $stmt->fetch();
                
                if ($nextSession) {
                    $stmt = $db->prepare("INSERT INTO training_enrollments (session_id, employee_id, enrollment_date, attendance_status, completion_status) VALUES (?, ?, NOW(), 'enrolled', 'not_started')");
                    $stmt->execute([$nextSession['id'], $employeeId]);
                    $enrollmentMessage = ' Employee has been automatically enrolled in the next available training session.';
                } else {
                    $enrollmentMessage = ' No available sessions found for auto-enrollment. Employee will be notified when sessions become available.';
                }
                
            } elseif ($autoEnroll === 'waitlist' && $moduleId) {
                // Add to waitlist for future sessions
                $stmt = $db->prepare("
                    INSERT INTO training_waitlist (module_id, employee_id, request_id, added_date, status) 
                    VALUES (?, ?, ?, NOW(), 'active')
                    ON DUPLICATE KEY UPDATE status = 'active', added_date = NOW()
                ");
                $stmt->execute([$moduleId, $employeeId, $requestId]);
                $enrollmentMessage = ' Employee has been added to the waitlist and will be automatically enrolled when sessions become available.';
            }
            
            $message = 'Training request approved successfully!' . $enrollmentMessage;
            $auth->logActivity('approve_training_request', 'training_requests', $requestId, null, [
                'session_id' => $sessionId, 
                'auto_enroll' => $autoEnroll,
                'module_id' => $moduleId
            ]);
        } catch (PDOException $e) {
            $error = 'Failed to approve training request: ' . $e->getMessage();
        }
    }
    
    if (isset($_POST['reject_request'])) {
        $requestId = $_POST['request_id'];
        $reason = $_POST['rejection_reason'];
        
        try {
            $stmt = $db->prepare("UPDATE training_requests SET status = 'rejected', rejection_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->execute([$reason, $current_user['id'], $requestId]);
            
            $message = 'Training request rejected successfully!';
            $auth->logActivity('reject_training_request', 'training_requests', $requestId, null, ['reason' => $reason]);
        } catch (PDOException $e) {
            $error = 'Failed to reject training request: ' . $e->getMessage();
        }
    }
}

// Get enhanced training requests with more details
try {
    $requests = $learningManager->getEnhancedTrainingRequests();
    $availableSessions = $learningManager->getAllSessions();
    $analytics = $learningManager->getLearningAnalytics();
} catch (PDOException $e) {
    $error = 'Failed to load training requests: ' . $e->getMessage();
    $requests = [];
    $availableSessions = [];
    $analytics = [];
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Training Requests</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshRequests()">
                <i class="fe fe-refresh-cw fe-16 mr-1"></i>Refresh
            </button>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fe fe-check-circle fe-16 mr-2"></i><?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fe fe-alert-circle fe-16 mr-2"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Training Request Analytics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Total Requests</h6>
                        <span class="h2 mb-0"><?php echo count($requests); ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-file-text fe-24 text-primary"></span>
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
                        <h6 class="text-uppercase text-muted mb-2">Pending</h6>
                        <span class="h2 mb-0"><?php echo count(array_filter($requests, function($r) { return $r['status'] === 'pending'; })); ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-clock fe-24 text-warning"></span>
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
                        <h6 class="text-uppercase text-muted mb-2">Approved</h6>
                        <span class="h2 mb-0"><?php echo count(array_filter($requests, function($r) { return $r['status'] === 'approved'; })); ?></span>
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
                        <h6 class="text-uppercase text-muted mb-2">Rejected</h6>
                        <span class="h2 mb-0"><?php echo count(array_filter($requests, function($r) { return $r['status'] === 'rejected'; })); ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-x-circle fe-24 text-danger"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Training Requests Management</h5>
            </div>
            <div class="card-body">
                <?php if (empty($requests)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-book-open fe-48 text-muted mb-3"></i>
                        <h4 class="text-muted">No Training Requests</h4>
                        <p class="text-muted">No training requests have been submitted yet.</p>
                    </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Training</th>
                                <th>Priority</th>
                                <th>Cost</th>
                                <th>Manager</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-3">
                                                    <span class="avatar-title bg-primary rounded-circle">
                                                        <?php echo strtoupper(substr($request['first_name'] ?? 'U', 0, 1) . substr($request['last_name'] ?? 'U', 0, 1)); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars(($request['first_name'] ?? 'Unknown') . ' ' . ($request['last_name'] ?? 'User')); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['email'] ?? 'N/A'); ?></small>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['department'] ?? 'N/A'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['training_title'] ?? 'N/A'); ?></strong>
                                            <?php if ($request['training_description']): ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars(substr($request['training_description'], 0, 80)) . (strlen($request['training_description']) > 80 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                            <?php if ($request['training_type']): ?>
                                                <br>
                                                <span class="badge badge-info"><?php echo htmlspecialchars($request['training_type']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : ($request['priority'] === 'medium' ? 'info' : 'secondary')); ?>">
                                                <?php echo ucfirst($request['priority'] ?? 'medium'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($request['estimated_cost'] > 0): ?>
                                                ₱<?php echo number_format($request['estimated_cost'], 2); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Free</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($request['manager_first_name']): ?>
                                                <?php echo htmlspecialchars($request['manager_first_name'] . ' ' . $request['manager_last_name']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'badge-warning',
                                                'approved' => 'badge-success',
                                                'rejected' => 'badge-danger',
                                                'cancelled' => 'badge-secondary'
                                            ][$request['status']] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo ucfirst($request['status'] ?? 'Unknown'); ?>
                                            </span>
                                            <?php if ($request['status'] === 'approved' && $request['approver_first_name']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    Approved by <?php echo htmlspecialchars($request['approver_first_name'] . ' ' . $request['approver_last_name']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo $request['request_date'] ? date('M d, Y', strtotime($request['request_date'])) : 'N/A'; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                    <i class="fe fe-eye fe-12"></i> View
                                                </button>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#approveModal<?php echo $request['id']; ?>">
                                                        <i class="fe fe-check fe-12"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal<?php echo $request['id']; ?>">
                                                        <i class="fe fe-x fe-12"></i> Reject
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

<!-- Approve Modal -->
<?php foreach ($requests as $request): ?>
    <?php if ($request['status'] === 'pending'): ?>
        <div class="modal fade" id="approveModal<?php echo $request['id']; ?>" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Training Request</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <p>Are you sure you want to approve this training request?</p>
                            <div class="alert alert-info">
                                <strong>Employee:</strong> <?php echo htmlspecialchars(($request['first_name'] ?? 'Unknown') . ' ' . ($request['last_name'] ?? 'User')); ?><br>
                                <strong>Training:</strong> <?php echo htmlspecialchars($request['training_title'] ?? 'N/A'); ?><br>
                                <strong>Request Date:</strong> <?php echo $request['request_date'] ? date('M d, Y', strtotime($request['request_date'])) : 'N/A'; ?>
                            </div>
                            
                            <!-- Auto-Enrollment Options -->
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">🤖 Smart Enrollment Options</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Enrollment Method:</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="auto_enroll" id="manual_<?php echo $request['id']; ?>" value="manual" checked>
                                            <label class="form-check-label" for="manual_<?php echo $request['id']; ?>">
                                                <strong>Manual Enrollment</strong> - Approve without automatic enrollment
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="auto_enroll" id="immediate_<?php echo $request['id']; ?>" value="immediate">
                                            <label class="form-check-label" for="immediate_<?php echo $request['id']; ?>">
                                                <strong>Immediate Enrollment</strong> - Enroll in selected session right away
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="auto_enroll" id="next_available_<?php echo $request['id']; ?>" value="next_available">
                                            <label class="form-check-label" for="next_available_<?php echo $request['id']; ?>">
                                                <strong>Next Available Session</strong> - Auto-enroll in next available session
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="auto_enroll" id="waitlist_<?php echo $request['id']; ?>" value="waitlist">
                                            <label class="form-check-label" for="waitlist_<?php echo $request['id']; ?>">
                                                <strong>Add to Waitlist</strong> - Auto-enroll when sessions become available
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group" id="session_selection_<?php echo $request['id']; ?>" style="display: none;">
                                <label for="session_id_<?php echo $request['id']; ?>">Select Training Session:</label>
                                <select class="form-control" name="session_id" id="session_id_<?php echo $request['id']; ?>">
                                    <option value="">Choose a session...</option>
                                    <?php foreach ($availableSessions as $session): ?>
                                        <?php if ($session['module_id'] == $request['module_id']): ?>
                                        <option value="<?php echo $session['id']; ?>" data-capacity="<?php echo $session['max_participants']; ?>" data-enrolled="<?php echo $session['enrolled_count'] ?? 0; ?>">
                                            <?php echo htmlspecialchars($session['session_name'] ?? $session['training_title']); ?> - 
                                            <?php echo date('M d, Y', strtotime($session['start_date'])); ?> 
                                            (<?php echo date('g:i A', strtotime($session['start_date'])); ?>)
                                            <?php if ($session['max_participants']): ?>
                                                - <?php echo ($session['enrolled_count'] ?? 0); ?>/<?php echo $session['max_participants']; ?> enrolled
                                            <?php endif; ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Sessions are filtered for the requested training module.</small>
                            </div>
                            
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <input type="hidden" name="employee_id" value="<?php echo $request['employee_id']; ?>">
                            <input type="hidden" name="module_id" value="<?php echo $request['module_id']; ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" name="approve_request" class="btn btn-success">
                                <i class="fe fe-check fe-16 mr-1"></i>Approve Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Reject Modal -->
<?php foreach ($requests as $request): ?>
    <?php if ($request['status'] === 'pending'): ?>
        <div class="modal fade" id="rejectModal<?php echo $request['id']; ?>" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Training Request</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <p>Are you sure you want to reject this training request?</p>
                            <div class="alert alert-warning">
                                <strong>Employee:</strong> <?php echo htmlspecialchars(($request['first_name'] ?? 'Unknown') . ' ' . ($request['last_name'] ?? 'User')); ?><br>
                                <strong>Training:</strong> <?php echo htmlspecialchars($request['training_title'] ?? 'N/A'); ?><br>
                                <strong>Request Date:</strong> <?php echo $request['request_date'] ? date('M d, Y', strtotime($request['request_date'])) : 'N/A'; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="rejection_reason_<?php echo $request['id']; ?>">Reason for Rejection:</label>
                                <textarea class="form-control" name="rejection_reason" id="rejection_reason_<?php echo $request['id']; ?>" rows="3" placeholder="Please provide a reason for rejecting this request..." required></textarea>
                            </div>
                            
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" name="reject_request" class="btn btn-danger">
                                <i class="fe fe-x fe-16 mr-1"></i>Reject Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<!-- View Training Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Training Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 id="view_request_title" class="mb-3"></h4>
                        <div class="mb-3">
                            <h6 class="text-muted">Request Description</h6>
                            <p id="view_request_description" class="text-justify"></p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Training Program</h6>
                                <span id="view_request_training" class="badge badge-primary"></span>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Request Status</h6>
                                <span id="view_request_status" class="badge"></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-muted">Session Information</h6>
                                <div id="view_request_session"></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Request Date</h6>
                                <span id="view_request_date"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Employee Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted">Employee</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm mr-2">
                                            <span class="avatar-title bg-primary rounded-circle" id="view_employee_initials"></span>
                                        </div>
                                        <span id="view_employee_name"></span>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted">Email</h6>
                                    <span id="view_employee_email"></span>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted">Department</h6>
                                    <span id="view_employee_department"></span>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-3" id="approval_info_card" style="display: none;">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Approval Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <h6 class="text-muted">Approved By</h6>
                                    <span id="view_approver_name"></span>
                                </div>
                                <div class="mb-3">
                                    <h6 class="text-muted">Approval Date</h6>
                                    <span id="view_approval_date"></span>
                                </div>
                                <div class="mb-3" id="rejection_reason_div" style="display: none;">
                                    <h6 class="text-muted">Rejection Reason</h6>
                                    <p id="view_rejection_reason" class="small text-muted"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="view_approve_btn" onclick="approveFromView()" style="display: none;">
                    <i class="fe fe-check fe-14 mr-1"></i>Approve Request
                </button>
                <button type="button" class="btn btn-danger" id="view_reject_btn" onclick="rejectFromView()" style="display: none;">
                    <i class="fe fe-x fe-14 mr-1"></i>Reject Request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewRequest(requestId) {
    // Fetch request details via AJAX
    fetch('includes/ajax/ajax_get_request_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'request_id=' + requestId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const request = data.request;
            
            // Populate view modal with request data
            document.getElementById('view_request_title').textContent = request.training_title || 'Training Request';
            document.getElementById('view_request_description').textContent = request.training_description || 'No description available';
            document.getElementById('view_request_training').textContent = request.training_title || 'Untitled Training';
            document.getElementById('view_request_date').textContent = request.request_date ? new Date(request.request_date).toLocaleDateString() : 'N/A';
            
            // Set status badge
            const statusClass = {
                'pending': 'badge-warning',
                'approved': 'badge-success',
                'rejected': 'badge-danger',
                'cancelled': 'badge-secondary'
            }[request.status] || 'badge-secondary';
            document.getElementById('view_request_status').textContent = request.status || 'Unknown';
            document.getElementById('view_request_status').className = 'badge ' + statusClass;
            
            // Session information
            if (request.start_date) {
                const startDate = new Date(request.start_date);
                const endDate = new Date(request.end_date);
                const sessionHtml = `
                    <strong>${request.session_name || 'Training Session'}</strong><br>
                    <small class="text-muted">
                        ${startDate.toLocaleDateString()} ${startDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - 
                        ${endDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                    </small>
                    ${request.location ? `<br><small class="text-muted"><i class="fe fe-map-pin fe-12 mr-1"></i>${request.location}</small>` : ''}
                `;
                document.getElementById('view_request_session').innerHTML = sessionHtml;
            } else {
                document.getElementById('view_request_session').innerHTML = '<span class="text-muted">No session assigned</span>';
            }
            
            // Employee information
            const employeeName = `${request.first_name || 'Unknown'} ${request.last_name || 'Employee'}`;
            document.getElementById('view_employee_name').textContent = employeeName;
            document.getElementById('view_employee_initials').textContent = 
                (request.first_name || 'U').charAt(0).toUpperCase() + (request.last_name || 'E').charAt(0).toUpperCase();
            document.getElementById('view_employee_email').textContent = request.email || 'N/A';
            document.getElementById('view_employee_department').textContent = request.department || 'N/A';
            
            // Approval information
            if (request.status === 'approved' || request.status === 'rejected') {
                document.getElementById('approval_info_card').style.display = 'block';
                const approverName = `${request.approver_first_name || 'Unknown'} ${request.approver_last_name || 'User'}`;
                document.getElementById('view_approver_name').textContent = approverName;
                document.getElementById('view_approval_date').textContent = request.approved_at ? new Date(request.approved_at).toLocaleDateString() : 'N/A';
                
                if (request.status === 'rejected' && request.rejection_reason) {
                    document.getElementById('rejection_reason_div').style.display = 'block';
                    document.getElementById('view_rejection_reason').textContent = request.rejection_reason;
                } else {
                    document.getElementById('rejection_reason_div').style.display = 'none';
                }
            } else {
                document.getElementById('approval_info_card').style.display = 'none';
            }
            
            // Show action buttons based on status
            if (request.status === 'pending') {
                document.getElementById('view_approve_btn').style.display = 'inline-block';
                document.getElementById('view_reject_btn').style.display = 'inline-block';
                window.currentRequestId = requestId;
            } else {
                document.getElementById('view_approve_btn').style.display = 'none';
                document.getElementById('view_reject_btn').style.display = 'none';
            }
            
            // Show view modal
            $('#viewRequestModal').modal('show');
        } else {
            alert('Failed to load request details: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load request details. Please try again.');
    });
}

function approveFromView() {
    // Close view modal and open approve modal
    $('#viewRequestModal').modal('hide');
    setTimeout(() => {
        $('#approveModal' + window.currentRequestId).modal('show');
    }, 300);
}

function rejectFromView() {
    // Close view modal and open reject modal
    $('#viewRequestModal').modal('hide');
    setTimeout(() => {
        $('#rejectModal' + window.currentRequestId).modal('show');
    }, 300);
}

function refreshRequests() {
    location.reload();
}

// Handle enrollment method changes
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for all enrollment radio buttons
    document.querySelectorAll('input[name="auto_enroll"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const requestId = this.id.split('_').pop();
            const sessionSelection = document.getElementById(`session_selection_${requestId}`);
            const sessionSelect = document.getElementById(`session_id_${requestId}`);
            
            if (this.value === 'immediate') {
                sessionSelection.style.display = 'block';
                sessionSelect.required = true;
                showEnrollmentHelp(requestId, 'Select a specific session for immediate enrollment.');
            } else {
                sessionSelection.style.display = 'none';
                sessionSelect.required = false;
                
                if (this.value === 'next_available') {
                    showEnrollmentHelp(requestId, 'Employee will be automatically enrolled in the next available session.');
                } else if (this.value === 'waitlist') {
                    showEnrollmentHelp(requestId, 'Employee will be added to waitlist and enrolled when sessions become available.');
                } else {
                    showEnrollmentHelp(requestId, 'Request will be approved without automatic enrollment.');
                }
            }
        });
    });
});

function showEnrollmentHelp(requestId, message) {
    // Remove existing help text
    const existingHelp = document.querySelector(`#approveModal${requestId} .enrollment-help`);
    if (existingHelp) {
        existingHelp.remove();
    }
    
    // Add new help text
    const helpDiv = document.createElement('div');
    helpDiv.className = 'alert alert-info enrollment-help mt-2';
    helpDiv.innerHTML = `<i class="fe fe-info-circle mr-2"></i>${message}`;
    
    const cardBody = document.querySelector(`#approveModal${requestId} .card-body`);
    cardBody.appendChild(helpDiv);
}

// Auto-refresh every 30 seconds
setInterval(function() {
    // Only refresh if no modals are open
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 30000);
</script>


