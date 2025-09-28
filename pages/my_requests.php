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

// Get employee's requests
$employeeRequests = $requestManager->getEmployeeRequests($current_user['id']);
$requestTypes = $requestManager->getRequestTypes();

$message = '';
$error = '';

// Handle request creation
if ($_POST && isset($_POST['create_request'])) {
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
        'approvers' => [1] // Default to admin for now
    ];
    
    $requestId = $requestManager->createRequest($requestData);
    
    if ($requestId) {
        $message = 'Request submitted successfully!';
        $auth->logActivity('create_request', 'employee_requests', $requestId, null, $requestData);
        // Refresh requests
        header('Location: ?page=my_requests&success=1');
        exit;
    } else {
        $error = 'Failed to submit request.';
    }
}

// Handle success message
if (isset($_GET['success'])) {
    $message = 'Request submitted successfully!';
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Requests</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRequestModal">
            <i class="fe fe-plus fe-16 mr-2"></i>Submit Request
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

<!-- Request Statistics -->
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

<!-- My Requests -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">My Request History</h5>
            </div>
            <div class="card-body">
                <?php if (empty($employeeRequests)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-file-text fe-48 text-muted mb-3"></i>
                        <h4 class="text-muted">No Requests Found</h4>
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
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Submitted Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employeeRequests as $request): ?>
                                    <tr>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($request['request_type_name'] ?? 'Unknown'); ?></span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($request['title'] ?? 'Untitled'); ?></strong>
                                                <div class="text-muted small"><?php echo htmlspecialchars(substr($request['description'] ?? '', 0, 50)) . (strlen($request['description'] ?? '') > 50 ? '...' : ''); ?></div>
                                            </div>
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
                                        <td><?php echo $request['created_at'] ? date('M d, Y', strtotime($request['created_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                    <i class="fe fe-eye fe-12"></i>
                                                </button>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="cancelRequest(<?php echo $request['id']; ?>)">
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
                                <label for="request_type_id">Request Type</label>
                                <select class="form-control" id="request_type_id" name="request_type_id" required>
                                    <option value="">Select Request Type</option>
                                    <?php foreach ($requestTypes as $type): ?>
                                        <option value="<?php echo $type['id']; ?>"><?php echo htmlspecialchars($type['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="requested_date">Request Date</label>
                                <input type="date" class="form-control" id="requested_date" name="requested_date" required>
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

<script>
function viewRequest(requestId) {
    alert('Request details functionality will be implemented soon. Request ID: ' + requestId);
}

function cancelRequest(requestId) {
    if (confirm('Are you sure you want to cancel this request?')) {
        // Implement cancellation logic
        alert('Request cancellation functionality will be implemented soon. Request ID: ' + requestId);
    }
}

// Set default dates
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    document.getElementById('requested_date').value = today.toISOString().split('T')[0];
});
</script>


