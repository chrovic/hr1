<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/employee.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$employeeManager = new EmployeeManager();

$message = '';
$error = '';

// Handle employee self-service operations
if ($_POST) {
    if (isset($_POST['update_profile'])) {
        $profileData = [
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'email' => $_POST['email'],
            'phone' => $_POST['phone'],
            'address' => $_POST['address']
        ];
        
        if ($employeeManager->updateEmployeeProfile($current_user['id'], $profileData)) {
            $message = 'Profile updated successfully!';
            $auth->logActivity('update_profile', 'users', $current_user['id'], null, $profileData);
        } else {
            $error = 'Failed to update profile.';
        }
    }
    
    if (isset($_POST['create_request'])) {
        $requestData = [
            'employee_id' => $current_user['id'],
            'request_type' => $_POST['request_type'],
            'title' => $_POST['title'],
            'description' => $_POST['description'],
            'request_date' => $_POST['request_date']
        ];
        
        if ($employeeManager->createEmployeeRequest($requestData)) {
            $message = 'Request submitted successfully!';
            $auth->logActivity('create_request', 'employee_requests', null, null, $requestData);
        } else {
            $error = 'Failed to submit request.';
        }
    }
    
    if (isset($_POST['request_training'])) {
        $requestData = [
            'employee_id' => $current_user['id'],
            'module_id' => $_POST['module_id'],
            'request_date' => $_POST['request_date']
        ];
        
        if ($employeeManager->requestTraining($requestData)) {
            $message = 'Training request submitted successfully!';
            $auth->logActivity('request_training', 'training_requests', null, null, $requestData);
        } else {
            $error = 'Failed to submit training request.';
        }
    }
    
    if (isset($_POST['change_password'])) {
        if ($_POST['new_password'] === $_POST['confirm_password']) {
            if ($employeeManager->updateEmployeePassword($current_user['id'], $_POST['new_password'])) {
                $message = 'Password changed successfully!';
                $auth->logActivity('change_password', 'users', $current_user['id'], null, null);
            } else {
                $error = 'Failed to change password.';
            }
        } else {
            $error = 'Passwords do not match.';
        }
    }
}

// Get employee data
try {
    $employeeProfile = $employeeManager->getEmployeeProfile($current_user['id']);
    $employeeRequests = $employeeManager->getEmployeeRequests($current_user['id']);
    $employeeEvaluations = $employeeManager->getEmployeeEvaluations($current_user['id']);
    $employeeTrainings = $employeeManager->getEmployeeTrainingEnrollments($current_user['id']);
    $dashboardStats = $employeeManager->getEmployeeDashboardStats($current_user['id']);
    $recentActivities = $employeeManager->getEmployeeRecentActivities($current_user['id']);
    $performanceSummary = $employeeManager->getEmployeePerformanceSummary($current_user['id']);
    $announcements = $employeeManager->getEmployeeAnnouncements($current_user['id']);
    $availableTrainings = $employeeManager->getAvailableTrainingModules();
} catch (PDOException $e) {
    $error = 'Database error: ' . $e->getMessage();
    $employeeProfile = $current_user;
    $employeeRequests = [];
    $employeeEvaluations = [];
    $employeeTrainings = [];
    $dashboardStats = ['total_evaluations' => 0, 'completed_evaluations' => 0, 'total_trainings' => 0, 'completed_trainings' => 0, 'pending_requests' => 0, 'approved_requests' => 0];
    $recentActivities = [];
    $performanceSummary = ['avg_score' => 0, 'total_evaluations' => 0, 'last_evaluation_date' => null];
    $announcements = [];
    $availableTrainings = [];
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Employee Self-Service Portal</h1>
</div>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Employee Dashboard Stats -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">My Evaluations</h6>
                        <span class="h2 mb-0"><?php echo $dashboardStats['total_evaluations']; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-clipboard fe-24 text-primary"></span>
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
                        <h6 class="text-uppercase text-muted mb-2">My Trainings</h6>
                        <span class="h2 mb-0"><?php echo $dashboardStats['total_trainings']; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-book fe-24 text-success"></span>
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
                        <h6 class="text-uppercase text-muted mb-2">My Requests</h6>
                        <span class="h2 mb-0"><?php echo $dashboardStats['pending_requests']; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-send fe-24 text-warning"></span>
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
                        <h6 class="text-uppercase text-muted mb-2">Performance Score</h6>
                        <span class="h2 mb-0"><?php echo $performanceSummary['avg_score'] ? round($performanceSummary['avg_score'], 1) : 'N/A'; ?></span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-trending-up fe-24 text-info"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Tabs -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="employeeTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab">
                            <i class="fe fe-user fe-16 mr-2"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="requests-tab" data-toggle="tab" href="#requests" role="tab">
                            <i class="fe fe-send fe-16 mr-2"></i>My Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="evaluations-tab" data-toggle="tab" href="#evaluations" role="tab">
                            <i class="fe fe-clipboard fe-16 mr-2"></i>Evaluations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="trainings-tab" data-toggle="tab" href="#trainings" role="tab">
                            <i class="fe fe-book fe-16 mr-2"></i>Trainings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="announcements-tab" data-toggle="tab" href="#announcements" role="tab">
                            <i class="fe fe-bell fe-16 mr-2"></i>Announcements
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="employeeTabsContent">
                    
                    <!-- Profile Tab -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Personal Information</h5>
                                <form method="POST">
                                    <input type="hidden" name="update_profile" value="1">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($employeeProfile['first_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($employeeProfile['last_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($employeeProfile['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($employeeProfile['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($employeeProfile['address'] ?? ''); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <h5>Change Password</h5>
                                <form method="POST">
                                    <input type="hidden" name="change_password" value="1">
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <button type="submit" class="btn btn-warning">Change Password</button>
                                </form>
                                
                                <hr>
                                
                                <h5>Employee Information</h5>
                                <table class="table table-sm">
                                    <tr><td><strong>Employee ID:</strong></td><td><?php echo $employeeProfile['id'] ?? 'N/A'; ?></td></tr>
                                    <tr><td><strong>Department:</strong></td><td><?php echo htmlspecialchars($employeeProfile['department'] ?? 'N/A'); ?></td></tr>
                                    <tr><td><strong>Position:</strong></td><td><?php echo htmlspecialchars($employeeProfile['position'] ?? 'N/A'); ?></td></tr>
                                    <tr><td><strong>Hire Date:</strong></td><td><?php echo $employeeProfile['hire_date'] ?? 'N/A'; ?></td></tr>
                                    <tr><td><strong>Status:</strong></td><td><span class="badge badge-success"><?php echo ucfirst($employeeProfile['status'] ?? 'Active'); ?></span></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Requests Tab -->
                    <div class="tab-pane fade" id="requests" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>My Requests</h5>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#createRequestModal">
                                    <i class="fe fe-plus fe-16 mr-2"></i>Submit Request
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($employeeRequests)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No requests found. <a href="#" data-toggle="modal" data-target="#createRequestModal">Submit your first request</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($employeeRequests as $request): ?>
                                            <tr>
                                                <td><?php echo ucfirst($request['request_type']); ?></td>
                                                <td><?php echo htmlspecialchars($request['title']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $request['status'] === 'approved' ? 'success' : ($request['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($request['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Evaluations Tab -->
                    <div class="tab-pane fade" id="evaluations" role="tabpanel">
                        <h5>My Evaluations</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Model</th>
                                        <th>Evaluator</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($employeeEvaluations)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No evaluations found.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($employeeEvaluations as $evaluation): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($evaluation['model_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $evaluation['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                        <?php echo ucfirst($evaluation['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $evaluation['overall_score'] ? round($evaluation['overall_score'], 1) : 'N/A'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($evaluation['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewEvaluation(<?php echo $evaluation['id']; ?>)">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Trainings Tab -->
                    <div class="tab-pane fade" id="trainings" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h5>My Training Enrollments</h5>
                            </div>
                            <div class="col-md-6 text-right">
                                <button class="btn btn-primary" data-toggle="modal" data-target="#requestTrainingModal">
                                    <i class="fe fe-plus fe-16 mr-2"></i>Request Training
                                </button>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Training</th>
                                        <th>Session</th>
                                        <th>Trainer</th>
                                        <th>Status</th>
                                        <th>Enrollment Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($employeeTrainings)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No training enrollments found. <a href="#" data-toggle="modal" data-target="#requestTrainingModal">Request your first training</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($employeeTrainings as $training): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($training['training_title'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($training['session_name'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($training['trainer_first_name'] . ' ' . $training['trainer_last_name']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo $training['status'] === 'completed' ? 'success' : ($training['status'] === 'enrolled' ? 'primary' : 'secondary'); ?>">
                                                        <?php echo ucfirst($training['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($training['enrollment_date'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewTraining(<?php echo $training['id']; ?>)">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Announcements Tab -->
                    <div class="tab-pane fade" id="announcements" role="tabpanel">
                        <h5>Company Announcements</h5>
                        <div class="row">
                            <?php if (empty($announcements)): ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        No announcements available.
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($announcements as $announcement): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-header">
                                                <h6 class="card-title mb-0"><?php echo htmlspecialchars($announcement['title']); ?></h6>
                                                <small class="text-muted">
                                                    By <?php echo htmlspecialchars($announcement['creator_first_name'] . ' ' . $announcement['creator_last_name']); ?> 
                                                    on <?php echo date('M d, Y', strtotime($announcement['created_at'])); ?>
                                                </small>
                                            </div>
                                            <div class="card-body">
                                                <p class="card-text"><?php echo htmlspecialchars(substr($announcement['content'], 0, 200)) . (strlen($announcement['content']) > 200 ? '...' : ''); ?></p>
                                                <span class="badge badge-<?php echo $announcement['priority'] === 'high' ? 'danger' : ($announcement['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                                    <?php echo ucfirst($announcement['priority']); ?> Priority
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Request Modal -->
<div class="modal fade" id="createRequestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Request</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="create_request" value="1">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Request Type</label>
                        <select class="form-control" name="request_type" required>
                            <option value="leave">Leave Request</option>
                            <option value="training">Training Request</option>
                            <option value="equipment">Equipment Request</option>
                            <option value="schedule_change">Schedule Change</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Request Date</label>
                        <input type="date" class="form-control" name="request_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Training Modal -->
<div class="modal fade" id="requestTrainingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Training</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="request_training" value="1">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Training Module</label>
                        <select class="form-control" name="module_id" required>
                            <?php foreach ($availableTrainings as $training): ?>
                                <option value="<?php echo $training['id']; ?>"><?php echo htmlspecialchars($training['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Requested Date</label>
                        <input type="date" class="form-control" name="request_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Request Training</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewRequest(requestId) {
    // Redirect to request details page
    window.location.href = '?page=request_details&id=' + requestId;
}

function viewEvaluation(evaluationId) {
    // Redirect to evaluation details page
    window.location.href = '?page=evaluation_details&id=' + evaluationId;
}

function viewTraining(trainingId) {
    // Redirect to training details page
    window.location.href = '?page=training_details&id=' + trainingId;
}
</script>
