<?php
require_once 'includes/data/db.php';

require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/request_manager.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$requestManager = new RequestManager();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['create_request'])) {
        $requestData = [
            'employee_id' => $current_user['id'],
            'request_type_id' => $_POST['request_type_id'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'priority' => $_POST['priority'],
            'requested_date' => $_POST['requested_date'],
            'requested_start_date' => $_POST['requested_start_date'] ?: null,
            'requested_end_date' => $_POST['requested_end_date'] ?: null,
            'requires_approval' => true,
            'approvers' => [1] // Default to admin for now - can be enhanced later
        ];
        
        $requestId = $requestManager->createRequest($requestData);
        
        if ($requestId) {
            $message = 'Request submitted successfully!';
            $auth->logActivity('create_request', 'employee_requests', $requestId, null, $requestData);
        } else {
            $error = 'Failed to submit request.';
        }
    }
    
    if (isset($_POST['add_comment'])) {
        $requestId = $_POST['request_id'];
        $comment = $_POST['comment'];
        $isInternal = isset($_POST['is_internal']) ? true : false;
        
        if ($requestManager->addComment($requestId, $current_user['id'], $comment, $isInternal)) {
            $message = 'Comment added successfully!';
        } else {
            $error = 'Failed to add comment.';
        }
    }
    
    if (isset($_POST['cancel_request'])) {
        $requestId = $_POST['request_id'];
        
        if ($requestManager->cancelRequest($requestId, $current_user['id'])) {
            $message = 'Request cancelled successfully!';
        } else {
            $error = 'Failed to cancel request.';
        }
    }
}

$requestTypes = $requestManager->getRequestTypes();

// Get requests based on user role
if ($current_user['role'] === 'admin' || $current_user['role'] === 'hr_manager') {
    // Admin and HR Manager see all requests
    $allRequests = $requestManager->getAllRequests();
    $employeeRequests = $requestManager->getEmployeeRequests($current_user['id']); // For personal requests
    $isAdminView = true;
} else {
    // Regular employees see only their own requests
    $employeeRequests = $requestManager->getEmployeeRequests($current_user['id']);
    $allRequests = [];
    $isAdminView = false;
}

$notifications = $requestManager->getUserNotifications($current_user['id'], true);
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><?php echo $isAdminView ? 'Employee Requests Management' : 'My Requests'; ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRequestModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Submit Request
        </button>
        <?php if (count($notifications) > 0): ?>
            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#notificationsModal">
                <i class="fe fe-bell fe-16 mr-2"></i>Notifications (<?php echo count($notifications); ?>)
            </button>
        <?php endif; ?>
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

<?php if ($isAdminView): ?>
<!-- Admin/HR Manager Tabs -->
<ul class="nav nav-tabs mb-4" id="requestTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="all-requests-tab" data-toggle="tab" href="#all-requests" role="tab" aria-controls="all-requests" aria-selected="true">
            <i class="fe fe-users fe-16 mr-1"></i>All Employee Requests
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="my-requests-tab" data-toggle="tab" href="#my-requests" role="tab" aria-controls="my-requests" aria-selected="false">
            <i class="fe fe-user fe-16 mr-1"></i>My Requests
        </a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="requestTabsContent">
    <div class="tab-pane fade show active" id="all-requests" role="tabpanel" aria-labelledby="all-requests-tab">
        <!-- All Requests Content -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><?php echo count($allRequests); ?></h5>
                        <p class="card-text">Total Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><?php echo count(array_filter($allRequests, function($r) { return $r['status'] == 'pending'; })); ?></h5>
                        <p class="card-text">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-success"><?php echo count(array_filter($allRequests, function($r) { return $r['status'] == 'approved'; })); ?></h5>
                        <p class="card-text">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><?php echo count(array_filter($allRequests, function($r) { return $r['status'] == 'rejected'; })); ?></h5>
                        <p class="card-text">Rejected</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Requests Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">All Employee Requests</h5>
            </div>
            <div class="card-body">
                <?php if (empty($allRequests)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-inbox fe-48 text-muted mb-3"></i>
                        <h4 class="text-muted">No Requests Found</h4>
                        <p class="text-muted">No employee requests have been submitted yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Request Type</th>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Department</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allRequests as $request): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-2">
                                                    <span class="avatar-title bg-primary rounded-circle">
                                                        <?php echo strtoupper(substr($request['first_name'] ?? 'U', 0, 1) . substr($request['last_name'] ?? 'U', 0, 1)); ?>
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="font-weight-bold"><?php echo htmlspecialchars(($request['first_name'] ?? 'Unknown') . ' ' . ($request['last_name'] ?? 'User')); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($request['position'] ?? 'N/A'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($request['request_type_name'] ?? 'Unknown'); ?></span>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold"><?php echo htmlspecialchars($request['title'] ?? 'Untitled'); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($request['description'] ?? '', 0, 50)) . (strlen($request['description'] ?? '') > 50 ? '...' : ''); ?></small>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClass = [
                                                'low' => 'badge-secondary',
                                                'medium' => 'badge-warning',
                                                'high' => 'badge-danger'
                                            ][$request['priority']] ?? 'badge-secondary';
                                            ?>
                                            <span class="badge <?php echo $priorityClass; ?>"><?php echo ucfirst($request['priority'] ?? 'Medium'); ?></span>
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
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($request['status'] ?? 'Unknown'); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['department'] ?? 'N/A'); ?></td>
                                        <td><?php echo $request['created_at'] ? date('M d, Y', strtotime($request['created_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                    <i class="fe fe-eye fe-12"></i>
                                                </button>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-success" onclick="approveRequest(<?php echo $request['id']; ?>)">
                                                        <i class="fe fe-check fe-12"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="rejectRequest(<?php echo $request['id']; ?>)">
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

    <div class="tab-pane fade" id="my-requests" role="tabpanel" aria-labelledby="my-requests-tab">
        <!-- My Requests Content (same as original) -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><?php echo count($employeeRequests); ?></h5>
                        <p class="card-text">Total Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-warning"><?php echo count(array_filter($employeeRequests, function($r) { return $r['status'] == 'pending'; })); ?></h5>
                        <p class="card-text">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-success"><?php echo count(array_filter($employeeRequests, function($r) { return $r['status'] == 'approved'; })); ?></h5>
                        <p class="card-text">Approved</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-danger"><?php echo count(array_filter($employeeRequests, function($r) { return $r['status'] == 'rejected'; })); ?></h5>
                        <p class="card-text">Rejected</p>
                    </div>
                </div>
            </div>
        </div>
<?php else: ?>
<!-- Regular Employee View -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary"><?php echo count($employeeRequests); ?></h5>
                <p class="card-text">Total Requests</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning"><?php echo count(array_filter($employeeRequests, function($r) { return $r['status'] == 'pending'; })); ?></h5>
                <p class="card-text">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success"><?php echo count(array_filter($employeeRequests, function($r) { return $r['status'] == 'approved'; })); ?></h5>
                <p class="card-text">Approved</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-danger"><?php echo count(array_filter($employeeRequests, function($r) { return $r['status'] == 'rejected'; })); ?></h5>
                <p class="card-text">Rejected</p>
            </div>
        </div>
    </div>
</div>

<!-- Employee Requests -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">My Requests</h5>
            </div>
            <div class="card-body">
                <?php if (empty($employeeRequests)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-file-text fe-48 text-muted"></i>
                        <h4 class="text-muted mt-3">No Requests</h4>
                        <p class="text-muted">You haven't submitted any requests yet.</p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRequestModal">
                            Submit Your First Request
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Request Type</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Requested Date</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employeeRequests as $request): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($request['request_type_name']); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['title']); ?></strong>
                                            <?php if ($request['description']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($request['description'], 0, 100)) . (strlen($request['description']) > 100 ? '...' : ''); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($request['status']) {
                                                case 'pending': $statusClass = 'badge-warning'; break;
                                                case 'approved': $statusClass = 'badge-success'; break;
                                                case 'rejected': $statusClass = 'badge-danger'; break;
                                                case 'cancelled': $statusClass = 'badge-secondary'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($request['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $priorityClass = '';
                                            switch($request['priority']) {
                                                case 'low': $priorityClass = 'badge-secondary'; break;
                                                case 'medium': $priorityClass = 'badge-info'; break;
                                                case 'high': $priorityClass = 'badge-warning'; break;
                                                case 'urgent': $priorityClass = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $priorityClass; ?>"><?php echo ucfirst($request['priority']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo $request['requested_date'] ? date('M d, Y', strtotime($request['requested_date'])) : 'N/A'; ?>
                                        </td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($request['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                    <i class="fe fe-eye fe-14"></i>
                                                </button>
                                                <?php if ($request['status'] == 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelRequest(<?php echo $request['id']; ?>)">
                                                        <i class="fe fe-x fe-14"></i>
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
    </div>
</div>
<?php endif; ?>

<!-- Create Request Modal -->
<div class="modal fade" id="createRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit New Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="request_type_id">Request Type *</label>
                                <select class="form-control" id="request_type_id" name="request_type_id" required>
                                    <option value="">Select Request Type</option>
                                    <?php foreach ($requestTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>">
                                            <?php echo htmlspecialchars($type['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priority *</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="medium">Medium</option>
                                    <option value="low">Low</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Request Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required placeholder="Brief title for your request">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required placeholder="Provide detailed information about your request..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="requested_date">Requested Date</label>
                                <input type="date" class="form-control" id="requested_date" name="requested_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="requested_start_date">Start Date (if applicable)</label>
                                <input type="date" class="form-control" id="requested_start_date" name="requested_start_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="requested_end_date">End Date (if applicable)</label>
                                <input type="date" class="form-control" id="requested_end_date" name="requested_end_date">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_request" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="requestDetails">
                <!-- Request details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Cancel Request Modal -->
<div class="modal fade" id="cancelRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Cancellation</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="cancel_request_id" name="request_id">
                    <p>Are you sure you want to cancel this request?</p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Keep Request</button>
                    <button type="submit" name="cancel_request" class="btn btn-danger">Yes, Cancel Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notifications Modal -->
<div class="modal fade" id="notificationsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Notifications</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (empty($notifications)): ?>
                    <p class="text-muted">No new notifications.</p>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="alert alert-info">
                            <strong><?php echo ucfirst($notification['notification_type']); ?>:</strong>
                            <?php echo htmlspecialchars($notification['message']); ?>
                            <br><small class="text-muted"><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function viewRequest(requestId) {
    // Load request details via AJAX
    fetch('?page=request_details&id=' + requestId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('requestDetails').innerHTML = html;
            $('#viewRequestModal').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load request details.');
        });
}

function cancelRequest(requestId) {
    document.getElementById('cancel_request_id').value = requestId;
    $('#cancelRequestModal').modal('show');
}

// Admin/HR Manager functions
function approveRequest(requestId) {
    if (confirm('Are you sure you want to approve this request?')) {
        fetch('includes/ajax/ajax_approve_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'request_id=' + requestId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Request approved successfully!');
                location.reload();
            } else {
                alert('Failed to approve request: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to approve request. Please try again.');
        });
    }
}

function rejectRequest(requestId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (reason !== null && reason.trim() !== '') {
        fetch('includes/ajax/ajax_reject_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'request_id=' + requestId + '&reason=' + encodeURIComponent(reason)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Request rejected successfully!');
                location.reload();
            } else {
                alert('Failed to reject request: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reject request. Please try again.');
        });
    }
}

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    document.getElementById('requested_date').value = today.toISOString().split('T')[0];
});
</script>