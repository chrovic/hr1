<?php
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/learning.php';
require_once 'includes/functions/smart_recommendations.php';

// Initialize authentication
$auth = new SimpleAuth();

// Check if user is logged in and is Employee
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

if (!$auth->isEmployee()) {
    $error = 'Access denied. This page is only available to employees.';
    // For now, let's allow access to test - remove this in production
    // header('Location: dashboard.php');
    // exit;
}

// Get current user
$current_user = $auth->getCurrentUser();
$smartRecommendations = new SmartRecommendations();

// Check if user data was retrieved successfully
if (!$current_user) {
    $error = 'Unable to retrieve user information. Please log in again.';
    // Redirect to login if user data is not available
    header('Location: auth/login.php');
    exit;
}

// Initialize learning manager
$learningManager = new LearningManager();

$message = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['request_training'])) {
        // Validate required fields
        if (empty($_POST['module_id']) || empty($_POST['request_date']) || empty($_POST['reason'])) {
            $error = 'Please fill in all required fields.';
        } else {
            $requestData = [
                'employee_id' => $current_user['id'],
                'module_id' => intval($_POST['module_id']),
                'request_date' => $_POST['request_date'],
                'reason' => trim($_POST['reason']),
                'priority' => $_POST['priority'],
                'manager_id' => !empty($_POST['manager_id']) ? intval($_POST['manager_id']) : null,
                'estimated_cost' => 0.00,
                'session_preference' => trim($_POST['session_preference'])
            ];
            
            try {
                if ($learningManager->submitTrainingRequest($requestData)) {
                    $message = 'Training request submitted successfully!';
                    $auth->logActivity('request_training', 'training_requests', null, null, $requestData);
                } else {
                    $error = 'Failed to submit training request.';
                }
            } catch (Exception $e) {
                $error = 'Error submitting request: ' . $e->getMessage();
            }
        }
    }
}

// Get employee data
try {
    $employeeTrainings = $learningManager->getEmployeeEnrollments($current_user['id']);
    $availableTrainings = $learningManager->getAllTrainings();
    $trainingRequests = $learningManager->getEnhancedTrainingRequests(null, $current_user['id']);
    $employeeSkills = $learningManager->getEmployeeSkills($current_user['id']);
    $employeeCertifications = $learningManager->getEmployeeCertifications($current_user['id']);
    $employeeLearningPaths = $learningManager->getEmployeeLearningPaths($current_user['id']);
    $learningSummary = $learningManager->getEmployeeLearningSummary($current_user['id']);
    
    // Get smart recommendations
    $trainingRecommendations = $smartRecommendations->getTrainingRecommendations($current_user['id'], 3);
    $nextBestTraining = $smartRecommendations->getNextBestTraining($current_user['id']);
    $skillGapAnalysis = $smartRecommendations->getSkillGapAnalysis($current_user['id']);
} catch (PDOException $e) {
    error_log("Employee training error: " . $e->getMessage());
    $availableTrainings = [];
    $trainingRequests = [];
    $employeeSkills = [];
    $employeeCertifications = [];
    $employeeLearningPaths = [];
    $learningSummary = [];
    $trainingRecommendations = [];
    $nextBestTraining = null;
    $skillGapAnalysis = ['gaps' => [], 'strengths' => [], 'overall_score' => 0];
}
?>

<div class="row">
    <div class="col-12">
        <div class="mb-2">
            <h1 class="h3 mb-1">My Training Requests</h1>
            <p class="text-muted">View your training enrollments and submit new training requests</p>
        </div>
    </div>
</div>

<!-- Smart Recommendations Section -->
<?php if (!empty($trainingRecommendations) || $nextBestTraining): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fe fe-lightbulb mr-2"></i>🤖 AI Recommendations for You
                </h5>
            </div>
            <div class="card-body">
                <?php if ($nextBestTraining): ?>
                <div class="alert alert-info mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="alert-heading mb-1">
                                <i class="fe fe-star mr-1"></i>Recommended Next Training
                            </h6>
                            <strong><?php echo htmlspecialchars($nextBestTraining['training']['title'] ?? 'No training available'); ?></strong>
                            <p class="mb-0 small text-muted"><?php echo htmlspecialchars($nextBestTraining['reason'] ?? ''); ?></p>
                        </div>
                        <div class="col-md-4 text-right">
                            <?php if ($nextBestTraining['training']): ?>
                            <button type="button" class="btn btn-primary btn-sm" onclick="quickRequestTraining(<?php echo $nextBestTraining['training']['id']; ?>, '<?php echo htmlspecialchars($nextBestTraining['training']['title']); ?>')">
                                <i class="fe fe-plus mr-1"></i>Quick Request
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($trainingRecommendations)): ?>
                <h6><i class="fe fe-target mr-1"></i>Based on Your Competency Gaps:</h6>
                <div class="row">
                    <?php foreach ($trainingRecommendations as $recommendation): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 border-left-warning">
                            <div class="card-body p-3">
                                <h6 class="card-title text-warning">
                                    <?php echo htmlspecialchars($recommendation['competency']['competency_name']); ?>
                                </h6>
                                <p class="card-text small text-muted mb-2">
                                    Gap Score: <?php echo round($recommendation['competency']['gap_score'], 1); ?>/5
                                </p>
                                <?php if (!empty($recommendation['trainings'])): ?>
                                <div class="small">
                                    <strong>Recommended:</strong>
                                    <?php foreach (array_slice($recommendation['trainings'], 0, 2) as $training): ?>
                                    <div class="mt-1">
                                        <a href="#" onclick="quickRequestTraining(<?php echo $training['id']; ?>, '<?php echo htmlspecialchars($training['title']); ?>')" class="text-primary">
                                            <?php echo htmlspecialchars($training['title']); ?>
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

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

<!-- Learning Statistics -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-primary"><?php echo $learningSummary['skills_count'] ?? 0; ?></div>
                <div class="text-muted">Skills</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-warning"><?php echo $learningSummary['certifications_count'] ?? 0; ?></div>
                <div class="text-muted">Certifications</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-info"><?php echo $learningSummary['learning_paths_count'] ?? 0; ?></div>
                <div class="text-muted">Learning Paths</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-success"><?php echo $learningSummary['completed_trainings_count'] ?? 0; ?></div>
                <div class="text-muted">Completed</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-secondary"><?php echo $learningSummary['training_requests_count'] ?? 0; ?></div>
                <div class="text-muted">Requests</div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="display-4 font-weight-bold text-danger"><?php echo $learningSummary['completed_paths_count'] ?? 0; ?></div>
                <div class="text-muted">Paths Done</div>
            </div>
        </div>
    </div>
</div>

<!-- Available Training Modules -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Available Training Modules</strong>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#requestTrainingModal">
                    <span class="fe fe-plus fe-16 mr-2"></span>
                    Request Training
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($availableTrainings)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-book-open fe-48 mb-3"></i>
                        <p class="mb-0">No training modules available at this time.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($availableTrainings as $training): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($training['title']); ?></h6>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars(substr($training['description'] ?? '', 0, 100)) . (strlen($training['description'] ?? '') > 100 ? '...' : ''); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                Duration: <?php echo $training['duration'] ?? 'N/A'; ?>
                                            </small>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="requestSpecificTraining(<?php echo $training['id']; ?>)">
                                                Request
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- My Training Requests -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">My Training Requests</strong>
            </div>
            <div class="card-body">
                <?php if (empty($trainingRequests)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-file-text fe-48 mb-3"></i>
                        <p class="mb-0">You haven't submitted any training requests yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Training Module</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trainingRequests as $request): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['training_title'] ?? 'N/A'); ?></strong>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($request['request_date'])); ?></td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            switch ($request['status']) {
                                                case 'approved':
                                                    $status_class = 'success';
                                                    break;
                                                case 'rejected':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'pending':
                                                    $status_class = 'warning';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($request['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewRequest(<?php echo $request['id']; ?>)">
                                                <span class="fe fe-eye fe-12"></span>
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

<!-- My Training Enrollments -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">My Training Enrollments</strong>
            </div>
            <div class="card-body">
                <?php if (empty($employeeTrainings)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-book-open fe-48 mb-3"></i>
                        <p class="mb-0">You are not enrolled in any training sessions.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Training</th>
                                    <th>Session</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employeeTrainings as $enrollment): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($enrollment['training_title'] ?? 'N/A'); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($enrollment['session_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo $enrollment['start_date'] ? date('M d, Y', strtotime($enrollment['start_date'])) : 'N/A'; ?></td>
                                        <td><?php echo $enrollment['end_date'] ? date('M d, Y', strtotime($enrollment['end_date'])) : 'N/A'; ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['location'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            // Use completion_status if available, otherwise fall back to status
                                            $displayStatus = $enrollment['completion_status'] ?? $enrollment['status'];
                                            $status_class = '';
                                            switch ($displayStatus) {
                                                case 'completed':
                                                    $status_class = 'success';
                                                    break;
                                                case 'in_progress':
                                                    $status_class = 'warning';
                                                    break;
                                                case 'enrolled':
                                                case 'pending':
                                                    $status_class = 'info';
                                                    break;
                                                case 'failed':
                                                    $status_class = 'danger';
                                                    break;
                                                case 'not_started':
                                                    $status_class = 'secondary';
                                                    break;
                                                default:
                                                    $status_class = 'secondary';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>">
                                                <?php echo ucfirst($displayStatus); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            // Use score if available, otherwise use progress
                                            $progressValue = $enrollment['score'] ?? $enrollment['progress'] ?? 0;
                                            $progressClass = '';
                                            if ($progressValue >= 80) {
                                                $progressClass = 'bg-success';
                                            } elseif ($progressValue >= 60) {
                                                $progressClass = 'bg-warning';
                                            } else {
                                                $progressClass = 'bg-danger';
                                            }
                                            ?>
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar <?php echo $progressClass; ?>" style="width: <?php echo $progressValue; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo $progressValue; ?>%</small>
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

<!-- Request Training Modal -->
<div class="modal fade" id="requestTrainingModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Training</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="request_training" value="1">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="module_id">Training Module *</label>
                                <select class="form-control" id="module_id" name="module_id" required>
                                    <option value="">Select a training module...</option>
                                    <?php foreach ($availableTrainings as $training): ?>
                                        <option value="<?php echo $training['id']; ?>" 
                                                data-duration="<?php echo $training['duration_hours'] ?? 0; ?>">
                                            <?php echo htmlspecialchars($training['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small id="duration_info" class="form-text text-muted" style="display: none;"></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priority *</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="request_date">Preferred Start Date *</label>
                                <input type="date" class="form-control" id="request_date" name="request_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manager_id">Manager</label>
                                <select class="form-control" id="manager_id" name="manager_id">
                                    <option value="">Select Manager (Optional)</option>
                                    <!-- TODO: Populate with actual managers -->
                                    <option value="1">John Manager</option>
                                    <option value="2">Jane Supervisor</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="reason">Reason for Request *</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" 
                                          placeholder="Please explain why you need this training and how it will benefit your role..." required></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="session_preference">Session Preferences</label>
                                <textarea class="form-control" id="session_preference" name="session_preference" rows="2" 
                                          placeholder="Any specific preferences for timing, location, or format..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="fe fe-send fe-16 mr-2"></span>
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- My Skills -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">My Skills</strong>
            </div>
            <div class="card-body">
                <?php if (empty($employeeSkills)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-zap fe-48 mb-3"></i>
                        <p class="mb-0">You haven't been assigned any skills yet.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($employeeSkills as $skill): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($skill['skill_name']); ?></h6>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars(substr($skill['skill_description'] ?? '', 0, 80)) . (strlen($skill['skill_description'] ?? '') > 80 ? '...' : ''); ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge badge-<?php echo $skill['proficiency_level'] === 'expert' ? 'danger' : ($skill['proficiency_level'] === 'advanced' ? 'warning' : ($skill['proficiency_level'] === 'intermediate' ? 'info' : 'success')); ?>">
                                                <?php echo ucfirst($skill['proficiency_level']); ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php echo $skill['acquired_date'] ? date('M Y', strtotime($skill['acquired_date'])) : 'N/A'; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- My Certifications -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">My Certifications</strong>
            </div>
            <div class="card-body">
                <?php if (empty($employeeCertifications)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-award fe-48 mb-3"></i>
                        <p class="mb-0">You don't have any certifications yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Certification</th>
                                    <th>Issuing Body</th>
                                    <th>Issue Date</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th>Verification</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employeeCertifications as $cert): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cert['certification_name']); ?></strong>
                                            <?php if ($cert['certificate_number']): ?>
                                                <br><small class="text-muted">Cert #: <?php echo htmlspecialchars($cert['certificate_number']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($cert['issuing_body']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($cert['issue_date'])); ?></td>
                                        <td>
                                            <?php if ($cert['expiry_date']): ?>
                                                <?php 
                                                $expiryDate = strtotime($cert['expiry_date']);
                                                $isExpiringSoon = $expiryDate <= strtotime('+3 months');
                                                $isExpired = $expiryDate < time();
                                                ?>
                                                <span class="<?php echo $isExpired ? 'text-danger' : ($isExpiringSoon ? 'text-warning' : 'text-success'); ?>">
                                                    <?php echo date('M d, Y', $expiryDate); ?>
                                                </span>
                                                <?php if ($isExpired): ?>
                                                    <br><small class="text-danger">Expired</small>
                                                <?php elseif ($isExpiringSoon): ?>
                                                    <br><small class="text-warning">Expires Soon</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">No expiry</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $cert['status'] === 'active' ? 'success' : ($cert['status'] === 'expired' ? 'danger' : 'secondary'); ?>">
                                                <?php echo ucfirst($cert['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $cert['verification_status'] === 'verified' ? 'success' : ($cert['verification_status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($cert['verification_status']); ?>
                                            </span>
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

<!-- My Learning Paths -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">My Learning Paths</strong>
            </div>
            <div class="card-body">
                <?php if (empty($employeeLearningPaths)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-map fe-48 mb-3"></i>
                        <p class="mb-0">You haven't been assigned any learning paths yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Learning Path</th>
                                    <th>Target Role</th>
                                    <th>Progress</th>
                                    <th>Status</th>
                                    <th>Assigned Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($employeeLearningPaths as $path): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($path['path_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($path['path_description'] ?? '', 0, 100)) . (strlen($path['path_description'] ?? '') > 100 ? '...' : ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo htmlspecialchars($path['target_role']); ?></span>
                                        </td>
                                        <td>
                                            <div class="progress" style="width: 100px;">
                                                <div class="progress-bar" style="width: <?php echo $path['progress_percentage']; ?>%"></div>
                                            </div>
                                            <small class="text-muted"><?php echo $path['progress_percentage']; ?>%</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $path['status'] === 'completed' ? 'success' : ($path['status'] === 'in_progress' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($path['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $path['assigned_date'] ? date('M d, Y', strtotime($path['assigned_date'])) : 'N/A'; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewLearningPath(<?php echo $path['path_id']; ?>)">
                                                <span class="fe fe-eye fe-12"></span>
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

<script>
function requestSpecificTraining(trainingId) {
    document.getElementById('module_id').value = trainingId;
    $('#requestTrainingModal').modal('show');
}

function viewRequest(requestId) {
    // In a real implementation, this would show request details
    alert('Request details view will be implemented here');
}

function quickRequestTraining(trainingId, trainingTitle) {
    // Pre-fill the training request form
    document.getElementById('module_id').value = trainingId;
    document.getElementById('reason').value = `Recommended training for skill development: ${trainingTitle}`;
    document.getElementById('priority').value = 'medium';
    
    // Show the modal
    $('#requestTrainingModal').modal('show');
    
    // Show a helpful message
    setTimeout(() => {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info alert-dismissible fade show';
        alertDiv.innerHTML = `
            <i class="fe fe-info mr-2"></i><strong>AI Recommendation:</strong> This training was suggested based on your competency analysis.
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        `;
        const modalBody = document.querySelector('#requestTrainingModal .modal-body');
        modalBody.insertBefore(alertDiv, modalBody.firstChild);
    }, 500);
}

// Show duration info when training module is selected
document.getElementById('module_id').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const duration = selectedOption.getAttribute('data-duration');
    
    // Show duration info if available
    if (duration) {
        const durationInfo = document.getElementById('duration_info');
        if (durationInfo) {
            durationInfo.textContent = `Duration: ${duration} hours`;
            durationInfo.style.display = 'block';
        }
    }
});
</script>

