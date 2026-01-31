<?php
require_once 'includes/data/db.php';

require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/request_manager.php';

// Get database connection
$db = getDB();

// Initialize authentication
$auth = new SimpleAuth();

// Check if user is logged in and is HR Manager or Admin
if (!$auth->isLoggedIn() || (!$auth->isHRManager() && !$auth->isAdmin())) {
    header('Location: auth/login.php');
    exit;
}

// Initialize request manager
$requestManager = new RequestManager();

$message = '';
$error = '';

// Get current user
$current_user = $auth->getCurrentUser();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['approve_request'])) {
        $requestId = $_POST['request_id'];
        $comments = $_POST['comments'] ?? '';
        
        if ($requestManager->approveRequest($requestId, $current_user['id'], $comments)) {
            $message = 'Request approved successfully!';
            $auth->logActivity('approve_request', 'employee_requests', $requestId, null, ['comments' => $comments]);
        } else {
            $error = 'Failed to approve request.';
        }
    }
    
    if (isset($_POST['reject_request'])) {
        $requestId = $_POST['request_id'];
        $comments = $_POST['comments'] ?? '';
        
        if ($requestManager->rejectRequest($requestId, $current_user['id'], $comments)) {
            $message = 'Request rejected successfully!';
            $auth->logActivity('reject_request', 'employee_requests', $requestId, null, ['comments' => $comments]);
        } else {
            $error = 'Failed to reject request.';
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
}

// Get request data
$filter_status = $_GET['status'] ?? 'pending';
$filter_type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

$filters = [
    'status' => $filter_status ?: null,
    'request_type_id' => $filter_type ?: null,
    'search' => $search ?: null
];
$allRequests = $requestManager->getAllRequests($filters);
$requestTypes = $requestManager->getRequestTypes();
$requestStats = $requestManager->getRequestStatistics();

// Use filtered stats for the cards when filters are applied
$filteredStats = [
    'total' => 0,
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];
foreach ($allRequests as $req) {
    $filteredStats['total']++;
    $statusValue = $req['status'] ?? 'pending';
    if (isset($filteredStats[$statusValue])) {
        $filteredStats[$statusValue]++;
    }
}
$cardStats = ($filter_status || $filter_type || $search) ? $filteredStats : ($requestStats ?: $filteredStats);
?>

<div class="row">
    <div class="col-12">
        <div class="mb-2">
            <h1 class="h3 mb-1">Employee Request Management</h1>
            <p class="text-muted">Review and manage employee requests</p>
        </div>
    </div>
</div>

<!-- Request Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-warning"><?php echo $cardStats['pending'] ?? 0; ?></div>
                <div class="text-muted">Pending Requests</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-success"><?php echo $cardStats['approved'] ?? 0; ?></div>
                <div class="text-muted">Approved</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-danger"><?php echo $cardStats['rejected'] ?? 0; ?></div>
                <div class="text-muted">Rejected</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-info"><?php echo $cardStats['total'] ?? 0; ?></div>
                <div class="text-muted">Total Requests</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Filter Requests</strong>
            </div>
            <div class="card-body">
                <form method="GET" class="row">
                    <input type="hidden" name="page" value="hr_request_management">
                    <div class="col-md-3 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $filter_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $filter_status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $filter_status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="cancelled" <?php echo $filter_status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="type" class="form-label">Request Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            <?php foreach ($requestTypes as $type): ?>
                                <option value="<?php echo htmlspecialchars($type['id']); ?>" <?php echo $filter_type == $type['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Employee name or request title...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <span class="fe fe-search fe-16 mr-2"></span>
                                Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <span class="fe fe-check-circle fe-16 mr-2"></span>
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <span class="fe fe-alert-circle fe-16 mr-2"></span>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Requests List -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">
                    Employee Requests (<?php echo count($allRequests); ?>)
                </strong>
            </div>
            <div class="card-body">
                <?php if (empty($allRequests)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-file-text fe-48 mb-3"></i>
                        <p class="mb-0">No requests found matching your criteria.</p>
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
                                    <th>Requested Date</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allRequests as $request): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm mr-3">
                                                    <img src="assets/images/avatars/face-1.jpg" alt="Avatar" class="avatar-img rounded-circle">
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['employee_first_name'] . ' ' . $request['employee_last_name']); ?></strong>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($request['employee_department'] ?? 'N/A'); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-light">
                                                <?php echo htmlspecialchars($request['request_type_name'] ?? 'N/A'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['title']); ?></strong>
                                            <?php if ($request['description']): ?>
                                                <div class="text-muted small"><?php echo htmlspecialchars(substr($request['description'], 0, 100)) . (strlen($request['description']) > 100 ? '...' : ''); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $priority_class = '';
                                            $priorityValue = $request['priority'] ?? 'medium';
                                            switch ($priorityValue) {
                                                case 'urgent':
                                                    $priority_class = 'danger';
                                                    break;
                                                case 'high':
                                                    $priority_class = 'warning';
                                                    break;
                                                case 'medium':
                                                    $priority_class = 'info';
                                                    break;
                                                case 'low':
                                                    $priority_class = 'secondary';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $priority_class; ?>">
                                                <?php echo ucfirst((string)$priorityValue); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            $statusValue = $request['status'] ?? 'pending';
                                            switch ($statusValue) {
                                                case 'approved':
                                                    $status_class = 'success';
                                                    break;
                                                case 'rejected':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'pending':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'cancelled':
                                                    $status_class = 'secondary';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo ucfirst((string)$statusValue); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $requestedDate = $request['requested_date'] ?? $request['request_date'] ?? null;
                                            echo $requestedDate ? date('M d, Y', strtotime($requestedDate)) : 'N/A';
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary" 
                                                        onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                    <span class="fe fe-eye fe-12"></span>
                                                </button>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-outline-success" 
                                                            onclick="approveRequest(<?php echo $request['id']; ?>)">
                                                        <span class="fe fe-check fe-12"></span>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger" 
                                                            onclick="rejectRequest(<?php echo $request['id']; ?>)">
                                                        <span class="fe fe-x fe-12"></span>
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

<!-- Request Details Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="requestModalBody">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Approve Request Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="approve_request" value="1">
                <input type="hidden" name="request_id" id="approve_request_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="approve_comments">Comments (Optional)</label>
                        <textarea class="form-control" id="approve_comments" name="comments" rows="3" 
                                  placeholder="Add any comments about this approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <span class="fe fe-check fe-16 mr-2"></span>
                        Approve Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Request Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="reject_request" value="1">
                <input type="hidden" name="request_id" id="reject_request_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="reject_comments">Reason for Rejection *</label>
                        <textarea class="form-control" id="reject_comments" name="comments" rows="3" 
                                  placeholder="Please provide a reason for rejecting this request..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <span class="fe fe-x fe-16 mr-2"></span>
                        Reject Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewRequest(requestId) {
    // Load request details
    document.getElementById('requestModalBody').innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    `;
    $('#requestModal').modal('show');

    fetch('ajax/request_details.php?id=' + encodeURIComponent(requestId), { credentials: 'same-origin' })
        .then(function(response) { return response.text(); })
        .then(function(html) {
            document.getElementById('requestModalBody').innerHTML = html;
        })
        .catch(function() {
            document.getElementById('requestModalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fe fe-alert-circle fe-16 mr-2"></i>
                    Failed to load request details.
                </div>
            `;
        });
}

function approveRequest(requestId) {
    document.getElementById('approve_request_id').value = requestId;
    $('#approveModal').modal('show');
}

function rejectRequest(requestId) {
    document.getElementById('reject_request_id').value = requestId;
    $('#rejectModal').modal('show');
}
</script>

