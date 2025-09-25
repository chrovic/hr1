<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/employee.php';
require_once 'includes/functions/competency.php';
require_once 'includes/functions/learning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$db = getDB();

$message = '';
$error = '';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $profileData = [
        'first_name' => trim($_POST['first_name']),
        'last_name' => trim($_POST['last_name']),
        'email' => trim($_POST['email']),
        'phone' => trim($_POST['phone']),
        'department' => trim($_POST['department']),
        'position' => trim($_POST['position'])
    ];
    
    $employeeManager = new EmployeeManager();
    if ($employeeManager->updateEmployeeProfile($current_user['id'], $profileData)) {
        $message = 'Profile updated successfully!';
        $auth->logActivity('update_profile', 'users', $current_user['id'], null, $profileData);
        // Refresh user data
        $current_user = $auth->getCurrentUser();
    } else {
        $error = 'Failed to update profile.';
    }
}

// Get employee-specific data
$employeeManager = new EmployeeManager();
$competencyManager = new CompetencyManager();
$learningManager = new LearningManager();

// Get employee statistics
$employeeStats = [
    'total_evaluations' => 0,
    'completed_trainings' => 0,
    'pending_requests' => 0,
    'competency_score' => 0,
    'active_trainings' => 0,
    'certificates_earned' => 0
];

try {
    // Get evaluation count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE employee_id = ?");
    $stmt->execute([$current_user['id']]);
    $employeeStats['total_evaluations'] = $stmt->fetch()['count'] ?? 0;

    // Get completed trainings count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM training_enrollments te JOIN training_sessions ts ON te.session_id = ts.id WHERE te.employee_id = ? AND te.completion_status = 'completed'");
    $stmt->execute([$current_user['id']]);
    $employeeStats['completed_trainings'] = $stmt->fetch()['count'] ?? 0;

    // Get active trainings count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM training_enrollments te JOIN training_sessions ts ON te.session_id = ts.id WHERE te.employee_id = ? AND te.completion_status IN ('enrolled', 'in_progress')");
    $stmt->execute([$current_user['id']]);
    $employeeStats['active_trainings'] = $stmt->fetch()['count'] ?? 0;

    // Get pending requests count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM training_requests WHERE employee_id = ? AND status = 'pending'");
    $stmt->execute([$current_user['id']]);
    $employeeStats['pending_requests'] = $stmt->fetch()['count'] ?? 0;

    // Get latest competency score
    $stmt = $db->prepare("
        SELECT AVG((cs.score / c.max_score) * 100) as avg_score
        FROM competency_scores cs
        JOIN competencies c ON cs.competency_id = c.id
        JOIN evaluations e ON cs.evaluation_id = e.id
        WHERE e.employee_id = ? AND e.status = 'completed'
        ORDER BY e.completed_at DESC
        LIMIT 1
    ");
    $stmt->execute([$current_user['id']]);
    $result = $stmt->fetch();
    $employeeStats['competency_score'] = $result ? round($result['avg_score'], 1) : 0;

} catch (PDOException $e) {
    error_log("Employee stats error: " . $e->getMessage());
}

// Get recent activities
$recentActivities = [];
try {
    $stmt = $db->prepare("
        SELECT action, table_name, created_at 
        FROM activity_logs 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$current_user['id']]);
    $recentActivities = $stmt->fetchAll();
} catch (PDOException $e) {
    // If activity_logs doesn't exist, create sample activities
    $recentActivities = [
        ['action' => 'login', 'table_name' => 'users', 'created_at' => date('Y-m-d H:i:s')],
        ['action' => 'view_profile', 'table_name' => 'users', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))]
    ];
}

// Get upcoming training sessions
$upcomingTrainings = [];
try {
    $stmt = $db->prepare("
        SELECT ts.session_name, ts.start_date, ts.end_date, tm.title as module_title, te.completion_status
        FROM training_enrollments te
        JOIN training_sessions ts ON te.session_id = ts.id
        JOIN training_modules tm ON ts.module_id = tm.id
        WHERE te.employee_id = ? AND ts.start_date > NOW() AND te.completion_status IN ('enrolled', 'in_progress')
        ORDER BY ts.start_date ASC
        LIMIT 3
    ");
    $stmt->execute([$current_user['id']]);
    $upcomingTrainings = $stmt->fetchAll();
} catch (PDOException $e) {
    $upcomingTrainings = [];
}

// Get recent evaluation results
$recentEvaluations = [];
try {
    $stmt = $db->prepare("
        SELECT e.*, cm.name as model_name, 
               AVG((cs.score / c.max_score) * 100) as avg_score
        FROM evaluations e
        JOIN competency_models cm ON e.model_id = cm.id
        LEFT JOIN competency_scores cs ON e.id = cs.evaluation_id
        LEFT JOIN competencies c ON cs.competency_id = c.id
        WHERE e.employee_id = ? AND e.status = 'completed'
        GROUP BY e.id
        ORDER BY e.completed_at DESC
        LIMIT 3
    ");
    $stmt->execute([$current_user['id']]);
    $recentEvaluations = $stmt->fetchAll();
} catch (PDOException $e) {
    $recentEvaluations = [];
}
?>

<!-- Enhanced CSS for Employee Self Service -->
<style>
.ess-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.ess-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    animation: float 20s infinite linear;
}

@keyframes float {
    0% { transform: translateX(-100px) translateY(-100px); }
    100% { transform: translateX(100px) translateY(100px); }
}

.ess-profile-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    padding: 1.5rem;
    position: relative;
    z-index: 2;
}

.ess-avatar {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    color: #333;
    border: 4px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.ess-stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.ess-stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 64px rgba(0, 0, 0, 0.15);
}

.ess-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--accent-color, #667eea);
    border-radius: 15px 15px 0 0;
}

.ess-stat-card.primary::before { background: #667eea; }
.ess-stat-card.success::before { background: #28a745; }
.ess-stat-card.warning::before { background: #ffc107; }
.ess-stat-card.info::before { background: #17a2b8; }
.ess-stat-card.danger::before { background: #dc3545; }

.ess-stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.ess-quick-action {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: inherit;
    display: block;
}

.ess-quick-action:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    text-decoration: none;
    color: inherit;
    border-color: var(--accent-color, #667eea);
}

.ess-section-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.ess-section-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.ess-progress-ring {
    position: relative;
    width: 60px;
    height: 60px;
}

.ess-progress-ring svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.ess-progress-ring circle {
    fill: none;
    stroke-width: 6;
}

.ess-progress-bg {
    stroke: #e9ecef;
}

.ess-progress-fill {
    stroke: #667eea;
    stroke-linecap: round;
    transition: stroke-dashoffset 0.5s ease;
}

.ess-activity-item {
    padding: 1rem;
    border-left: 4px solid #e9ecef;
    margin-bottom: 1rem;
    background: #f8f9fa;
    border-radius: 0 8px 8px 0;
    transition: all 0.3s ease;
}

.ess-activity-item:hover {
    border-left-color: #667eea;
    background: white;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
}

.ess-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 50px;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.ess-badge.success { background: #d4edda; color: #155724; }
.ess-badge.warning { background: #fff3cd; color: #856404; }
.ess-badge.info { background: #d1ecf1; color: #0c5460; }
.ess-badge.primary { background: #cce7ff; color: #0056b3; }

@media (max-width: 768px) {
    .ess-hero {
        padding: 1.5rem;
        text-align: center;
    }
    
    .ess-profile-card {
        text-align: center;
        margin-bottom: 1rem;
    }
    
    .ess-stat-card,
    .ess-quick-action {
        margin-bottom: 1rem;
    }
}
</style>

<!-- Success/Error Messages -->
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

<!-- Hero Section with Profile -->
<div class="ess-hero">
    <div class="row align-items-center">
        <div class="col-md-8">
            <div class="ess-profile-card">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="ess-avatar">
                            <?php echo strtoupper(substr($current_user['first_name'] ?? 'E', 0, 1) . substr($current_user['last_name'] ?? 'M', 0, 1)); ?>
                        </div>
                    </div>
                    <div class="col">
                        <h2 class="mb-1">Welcome back, <?php echo htmlspecialchars($current_user['first_name'] ?? 'Employee'); ?>!</h2>
                        <p class="mb-1 opacity-75">
                            <i class="fe fe-briefcase fe-16 mr-1"></i>
                            <?php echo htmlspecialchars($current_user['position'] ?? 'Employee'); ?>
                        </p>
                        <p class="mb-0 opacity-75">
                            <i class="fe fe-users fe-16 mr-1"></i>
                            <?php echo htmlspecialchars($current_user['department'] ?? 'Department'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <button type="button" class="btn btn-light btn-lg" data-toggle="modal" data-target="#updateProfileModal">
                <i class="fe fe-edit-2 fe-16 mr-2"></i>Update Profile
            </button>
        </div>
    </div>
</div>

<!-- Statistics Dashboard -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="ess-stat-card primary">
            <div class="ess-stat-icon" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                <i class="fe fe-clipboard"></i>
            </div>
            <h3 class="mb-1"><?php echo $employeeStats['total_evaluations']; ?></h3>
            <p class="text-muted mb-0 small">Total Evaluations</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="ess-stat-card success">
            <div class="ess-stat-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                <i class="fe fe-check-circle"></i>
            </div>
            <h3 class="mb-1"><?php echo $employeeStats['completed_trainings']; ?></h3>
            <p class="text-muted mb-0 small">Completed Trainings</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="ess-stat-card info">
            <div class="ess-stat-icon" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                <i class="fe fe-play-circle"></i>
            </div>
            <h3 class="mb-1"><?php echo $employeeStats['active_trainings']; ?></h3>
            <p class="text-muted mb-0 small">Active Trainings</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="ess-stat-card warning">
            <div class="ess-stat-icon" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                <i class="fe fe-clock"></i>
            </div>
            <h3 class="mb-1"><?php echo $employeeStats['pending_requests']; ?></h3>
            <p class="text-muted mb-0 small">Pending Requests</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="ess-stat-card primary">
            <div class="ess-stat-icon" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                <i class="fe fe-target"></i>
            </div>
            <h3 class="mb-1"><?php echo $employeeStats['competency_score']; ?>%</h3>
            <p class="text-muted mb-0 small">Latest Score</p>
        </div>
    </div>
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="ess-stat-card success">
            <div class="ess-stat-icon" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                <i class="fe fe-award"></i>
            </div>
            <h3 class="mb-1"><?php echo $employeeStats['certificates_earned']; ?></h3>
            <p class="text-muted mb-0 small">Certificates</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="ess-section-card">
            <div class="ess-section-header">
                <h5 class="mb-0">
                    <i class="fe fe-zap text-primary mr-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="?page=my_evaluations" class="ess-quick-action">
                            <div class="ess-stat-icon mx-auto" style="background: rgba(102, 126, 234, 0.1); color: #667eea;">
                                <i class="fe fe-clipboard"></i>
                            </div>
                            <h6 class="mb-1">My Evaluations</h6>
                            <p class="text-muted small mb-0">View performance reviews</p>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="?page=employee_training_requests" class="ess-quick-action">
                            <div class="ess-stat-icon mx-auto" style="background: rgba(40, 167, 69, 0.1); color: #28a745;">
                                <i class="fe fe-book-open"></i>
                            </div>
                            <h6 class="mb-1">Training Requests</h6>
                            <p class="text-muted small mb-0">Request new training</p>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="?page=my_trainings" class="ess-quick-action">
                            <div class="ess-stat-icon mx-auto" style="background: rgba(23, 162, 184, 0.1); color: #17a2b8;">
                                <i class="fe fe-play-circle"></i>
                            </div>
                            <h6 class="mb-1">My Trainings</h6>
                            <p class="text-muted small mb-0">View training history</p>
                        </a>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="?page=employee_requests" class="ess-quick-action">
                            <div class="ess-stat-icon mx-auto" style="background: rgba(255, 193, 7, 0.1); color: #ffc107;">
                                <i class="fe fe-file-text"></i>
                            </div>
                            <h6 class="mb-1">My Requests</h6>
                            <p class="text-muted small mb-0">Track request status</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Row -->
<div class="row">
    <!-- Left Column - Recent Activities & Upcoming Trainings -->
    <div class="col-lg-8 mb-4">
        <!-- Recent Activities -->
        <div class="ess-section-card mb-4">
            <div class="ess-section-header">
                <h5 class="mb-0">
                    <i class="fe fe-activity text-primary mr-2"></i>Recent Activities
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentActivities)): ?>
                    <div class="text-center py-5">
                        <i class="fe fe-activity" style="font-size: 3rem; color: #e9ecef;"></i>
                        <h6 class="text-muted mt-3">No Recent Activities</h6>
                        <p class="text-muted small">Your recent activities will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="ess-activity-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo ucfirst(str_replace('_', ' ', $activity['action'])); ?></h6>
                                    <p class="text-muted small mb-0">
                                        <i class="fe fe-database fe-12 mr-1"></i>
                                        <?php echo ucfirst($activity['table_name']); ?>
                                    </p>
                                </div>
                                <small class="text-muted">
                                    <?php echo date('M d, H:i', strtotime($activity['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Trainings -->
        <div class="ess-section-card">
            <div class="ess-section-header">
                <h5 class="mb-0">
                    <i class="fe fe-calendar text-success mr-2"></i>Upcoming Trainings
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingTrainings)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-calendar" style="font-size: 3rem; color: #e9ecef;"></i>
                        <h6 class="text-muted mt-3">No Upcoming Trainings</h6>
                        <p class="text-muted small">Your scheduled trainings will appear here.</p>
                        <a href="?page=employee_training_requests" class="btn btn-primary btn-sm">
                            <i class="fe fe-plus mr-1"></i>Request Training
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingTrainings as $training): ?>
                        <div class="ess-activity-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($training['module_title']); ?></h6>
                                    <p class="text-muted small mb-1">
                                        <i class="fe fe-calendar fe-12 mr-1"></i>
                                        <?php echo date('M d, Y g:i A', strtotime($training['start_date'])); ?>
                                    </p>
                                    <span class="ess-badge info"><?php echo ucfirst($training['completion_status']); ?></span>
                                </div>
                                <div class="text-right">
                                    <small class="text-muted">
                                        <?php 
                                        $days = ceil((strtotime($training['start_date']) - time()) / (60 * 60 * 24));
                                        echo $days > 0 ? "in $days days" : "today";
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column - Performance & Quick Stats -->
    <div class="col-lg-4 mb-4">
        <!-- Performance Overview -->
        <div class="ess-section-card mb-4">
            <div class="ess-section-header">
                <h5 class="mb-0">
                    <i class="fe fe-trending-up text-warning mr-2"></i>Performance Overview
                </h5>
            </div>
            <div class="card-body text-center">
                <div class="ess-progress-ring mx-auto mb-3">
                    <svg>
                        <circle class="ess-progress-bg" cx="30" cy="30" r="25"></circle>
                        <circle class="ess-progress-fill" cx="30" cy="30" r="25" 
                                style="stroke-dasharray: <?php echo 2 * pi() * 25; ?>; 
                                       stroke-dashoffset: <?php echo 2 * pi() * 25 * (1 - $employeeStats['competency_score'] / 100); ?>;"></circle>
                    </svg>
                    <div class="position-absolute" style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <strong><?php echo $employeeStats['competency_score']; ?>%</strong>
                    </div>
                </div>
                <h6 class="mb-1">Overall Competency Score</h6>
                <p class="text-muted small mb-3">Based on latest evaluations</p>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-right">
                            <h5 class="text-success mb-0"><?php echo $employeeStats['completed_trainings']; ?></h5>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h5 class="text-info mb-0"><?php echo $employeeStats['active_trainings']; ?></h5>
                        <small class="text-muted">In Progress</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Evaluations -->
        <div class="ess-section-card">
            <div class="ess-section-header">
                <h5 class="mb-0">
                    <i class="fe fe-star text-info mr-2"></i>Recent Evaluations
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentEvaluations)): ?>
                    <div class="text-center py-4">
                        <i class="fe fe-star" style="font-size: 2rem; color: #e9ecef;"></i>
                        <h6 class="text-muted mt-2">No Evaluations Yet</h6>
                        <p class="text-muted small">Your evaluation results will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentEvaluations as $evaluation): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1"><?php echo htmlspecialchars($evaluation['model_name']); ?></h6>
                                <small class="text-muted">
                                    <?php echo date('M d, Y', strtotime($evaluation['completed_at'])); ?>
                                </small>
                            </div>
                            <div class="text-right">
                                <span class="ess-badge <?php echo $evaluation['avg_score'] >= 80 ? 'success' : ($evaluation['avg_score'] >= 60 ? 'warning' : 'info'); ?>">
                                    <?php echo round($evaluation['avg_score'], 1); ?>%
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Update Profile Modal -->
<div class="modal fade" id="updateProfileModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fe fe-user mr-2"></i>Update Profile
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($current_user['first_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($current_user['last_name'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($current_user['email'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" 
                               value="<?php echo htmlspecialchars($current_user['phone'] ?? ''); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <input type="text" class="form-control" id="department" name="department" 
                                       value="<?php echo htmlspecialchars($current_user['department'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" class="form-control" id="position" name="position" 
                                       value="<?php echo htmlspecialchars($current_user['position'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fe fe-save mr-2"></i>Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Enhanced interactions
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to stat cards
    document.querySelectorAll('.ess-stat-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Animate progress ring
    const progressRing = document.querySelector('.ess-progress-fill');
    if (progressRing) {
        const score = <?php echo $employeeStats['competency_score']; ?>;
        const circumference = 2 * Math.PI * 25;
        const offset = circumference - (score / 100) * circumference;
        
        setTimeout(() => {
            progressRing.style.strokeDashoffset = offset;
        }, 500);
    }

    // Add ripple effect to quick actions
    document.querySelectorAll('.ess-quick-action').forEach(action => {
        action.addEventListener('click', function(e) {
            const ripple = document.createElement('div');
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(102, 126, 234, 0.3);
                pointer-events: none;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                width: 100px;
                height: 100px;
                left: ${e.offsetX - 50}px;
                top: ${e.offsetY - 50}px;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
});

// Add CSS animation for ripple effect
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
</script>