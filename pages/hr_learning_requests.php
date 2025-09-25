<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';
require_once 'includes/functions/redirect_helper.php';

$auth = new SimpleAuth();

// Check if user is logged in and has appropriate role
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();

// Check if user is admin or HR manager
if ($current_user['role'] !== 'admin' && $current_user['role'] !== 'hr_manager') {
    echo '<div class="alert alert-danger">You do not have permission to view this page.</div>';
    exit;
}
$learningManager = new LearningManager();

$message = '';
$error = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['request_id'])) {
        $request_id = intval($_POST['request_id']);
        $action = $_POST['action'];
        $reason = trim($_POST['reason'] ?? '');
        
        try {
            $db = getDB();
            
            if ($action === 'approve') {
                // Update request status to approved
                $stmt = $db->prepare("
                    UPDATE employee_requests 
                    SET status = 'approved', 
                        approved_by = ?, 
                        approved_at = NOW(),
                        rejection_reason = NULL
                    WHERE id = ?
                ");
                $stmt->execute([$current_user['id'], $request_id]);
                
                $message = 'Learning material request approved successfully!';
                
            } elseif ($action === 'reject') {
                // Update request status to rejected
                $stmt = $db->prepare("
                    UPDATE employee_requests 
                    SET status = 'rejected', 
                        approved_by = ?, 
                        approved_at = NOW(),
                        rejection_reason = ?
                    WHERE id = ?
                ");
                $stmt->execute([$current_user['id'], $reason, $request_id]);
                
                $message = 'Learning material request rejected.';
            }
            
            $auth->logActivity('manage_learning_request', 'employee_requests', $request_id, null, ['action' => $action]);
            
        } catch (Exception $e) {
            $error = 'Error processing request: ' . $e->getMessage();
        }
    }
}

// Get all learning material requests
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT er.*, 
               u.first_name, u.last_name, u.email,
               approver.first_name as approver_first_name, 
               approver.last_name as approver_last_name
        FROM employee_requests er
        JOIN users u ON er.employee_id = u.id
        LEFT JOIN users approver ON er.approved_by = approver.id
        WHERE er.request_type = 'other'
        ORDER BY er.request_date DESC
    ");
    $stmt->execute();
    $requests = $stmt->fetchAll();
    
    // Get statistics
    $total_requests = count($requests);
    $pending_requests = count(array_filter($requests, function($r) { return $r['status'] === 'pending'; }));
    $approved_requests = count(array_filter($requests, function($r) { return $r['status'] === 'approved'; }));
    $rejected_requests = count(array_filter($requests, function($r) { return $r['status'] === 'rejected'; }));
    
} catch (Exception $e) {
    $error = 'Error loading requests: ' . $e->getMessage();
    $requests = [];
    $total_requests = $pending_requests = $approved_requests = $rejected_requests = 0;
}
?>

<!-- HR Learning Requests Management Page Content -->
<div class="content">
    <div class="page-header">
        <div class="add-list">
            <h4 class="page-title">Learning Material Requests</h4>
            <p class="text-muted">Review and manage employee learning material requests</p>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fe fe-check-circle mr-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fe fe-alert-triangle mr-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h6 class="text-muted mb-1">Total Requests</h6>
                            <h3 class="mb-0"><?php echo $total_requests; ?></h3>
                        </div>
                        <div class="flex-fill text-right">
                            <i class="fe fe-file-text fe-24 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0 text-warning"><?php echo $pending_requests; ?></h3>
                        </div>
                        <div class="flex-fill text-right">
                            <i class="fe fe-clock fe-24 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h6 class="text-muted mb-1">Approved</h6>
                            <h3 class="mb-0 text-success"><?php echo $approved_requests; ?></h3>
                        </div>
                        <div class="flex-fill text-right">
                            <i class="fe fe-check-circle fe-24 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-fill">
                            <h6 class="text-muted mb-1">Rejected</h6>
                            <h3 class="mb-0 text-danger"><?php echo $rejected_requests; ?></h3>
                        </div>
                        <div class="flex-fill text-right">
                            <i class="fe fe-x-circle fe-24 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Learning Material Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Learning Material Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($requests)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Request Title</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Approved By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($requests as $request): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($request['email']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($request['title']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($request['description'], 0, 100)) . (strlen($request['description']) > 100 ? '...' : ''); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                switch ($request['status']) {
                                                    case 'pending':
                                                        $status_class = 'warning';
                                                        break;
                                                    case 'approved':
                                                        $status_class = 'success';
                                                        break;
                                                    case 'rejected':
                                                        $status_class = 'danger';
                                                        break;
                                                }
                                                ?>
                                                <span class="badge badge-<?php echo $status_class; ?>">
                                                    <?php echo ucfirst($request['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($request['approved_by']): ?>
                                                    <?php echo htmlspecialchars($request['approver_first_name'] . ' ' . $request['approver_last_name']); ?>
                                                    <br><small class="text-muted"><?php echo date('M d, Y', strtotime($request['approved_at'])); ?></small>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($request['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="openApprovalModal(<?php echo $request['id']; ?>, 'approve')">
                                                        <i class="fe fe-check"></i> Approve
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="openApprovalModal(<?php echo $request['id']; ?>, 'reject')">
                                                        <i class="fe fe-x"></i> Reject
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">Processed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fe fe-inbox fe-48 text-muted"></i>
                            <p class="text-muted mt-3">No learning material requests found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval/Rejection Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Process Learning Material Request</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" id="modal_request_id" name="request_id" value="">
                    <input type="hidden" id="modal_action" name="action" value="">
                    
                    <div class="form-group">
                        <label for="modal_reason">Reason (Optional)</label>
                        <textarea class="form-control" id="modal_reason" name="reason" rows="3" placeholder="Enter reason for approval or rejection..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fe fe-info mr-2"></i>
                        <span id="modal_action_text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="modal_submit_btn">
                        <i class="fe fe-check mr-2"></i>Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openApprovalModal(requestId, action) {
    document.getElementById('modal_request_id').value = requestId;
    document.getElementById('modal_action').value = action;
    
    const submitBtn = document.getElementById('modal_submit_btn');
    const actionText = document.getElementById('modal_action_text');
    
    if (action === 'approve') {
        submitBtn.className = 'btn btn-success';
        submitBtn.innerHTML = '<i class="fe fe-check mr-2"></i>Approve Request';
        actionText.textContent = 'This will approve the learning material request and grant the employee access to the requested materials.';
    } else {
        submitBtn.className = 'btn btn-danger';
        submitBtn.innerHTML = '<i class="fe fe-x mr-2"></i>Reject Request';
        actionText.textContent = 'This will reject the learning material request. You can provide a reason for the rejection.';
    }
    
    $('#approvalModal').modal('show');
}
</script>
