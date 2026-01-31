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

require_once 'includes/functions/competency.php';
require_once 'includes/functions/hr_manager.php';

$db = getDB();

// Initialize HR Manager if user is HR Manager
$hr_manager = null;
if ($auth->isHRManager()) {
    $hr_manager = new HRManager($db);
}

$message = '';
$error = '';

// Admin CRUD operations - Only for admins and HR managers
if ($auth->hasPermission('manage_system') || $current_user['role'] === 'hr_manager') {
    if ($_POST) {
        if (isset($_POST['create_announcement'])) {
            $announcementData = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'priority' => $_POST['priority'],
                'target_audience' => $_POST['target_audience'],
                'created_by' => $current_user['id'],
                'status' => 'active'
            ];
            
            try {
                $stmt = $db->prepare("
                    INSERT INTO announcements (title, content, priority, target_audience, status, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $result = $stmt->execute([
                    $announcementData['title'],
                    $announcementData['content'],
                    $announcementData['priority'],
                    $announcementData['target_audience'],
                    $announcementData['status'],
                    $announcementData['created_by']
                ]);
                
                if ($result) {
                    $message = 'Announcement created successfully!';
                    $auth->logActivity('create_announcement', 'announcements', null, null, $announcementData);
                } else {
                    $error = 'Failed to create announcement.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
        
        if (isset($_POST['update_announcement'])) {
            $announcementId = $_POST['announcement_id'];
            $updateData = [
                'title' => $_POST['title'],
                'content' => $_POST['content'],
                'priority' => $_POST['priority'],
                'target_audience' => $_POST['target_audience'],
                'status' => $_POST['status']
            ];
            
            try {
                $stmt = $db->prepare("
                    UPDATE announcements SET 
                        title = ?, content = ?, priority = ?, target_audience = ?, 
                        status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $result = $stmt->execute([
                    $updateData['title'],
                    $updateData['content'],
                    $updateData['priority'],
                    $updateData['target_audience'],
                    $updateData['status'],
                    $announcementId
                ]);
                
                if ($result) {
                    $message = 'Announcement updated successfully!';
                    $auth->logActivity('update_announcement', 'announcements', $announcementId, null, $updateData);
                } else {
                    $error = 'Failed to update announcement.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
        
        if (isset($_POST['delete_announcement'])) {
            $announcementId = $_POST['announcement_id'];
            
            try {
                $stmt = $db->prepare("UPDATE announcements SET status = 'archived' WHERE id = ?");
                $result = $stmt->execute([$announcementId]);
                
                if ($result) {
                    $message = 'Announcement archived successfully!';
                    $auth->logActivity('delete_announcement', 'announcements', $announcementId, null, null);
                } else {
                    $error = 'Failed to archive announcement.';
                }
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get dashboard statistics with error handling
$stats = [];

try {
    // Total employees
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'employee' AND status = 'active'");
    $stmt->execute();
    $stats['total_employees'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['total_employees'] = 0;
}

try {
    // Active evaluations
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE status IN ('pending', 'in_progress')");
    $stmt->execute();
    $stats['active_evaluations'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['active_evaluations'] = 0;
}

try {
    // Completed evaluations this month
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stmt->execute();
    $stats['completed_evaluations'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['completed_evaluations'] = 0;
}

try {
    // Active training enrollments
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM training_enrollments WHERE status IN ('enrolled', 'attended')");
    $stmt->execute();
    $stats['active_trainings'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['active_trainings'] = 0;
}

try {
    // Pending employee requests
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM employee_requests WHERE status = 'pending'");
    $stmt->execute();
    $stats['pending_requests'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['pending_requests'] = 0;
}

try {
    // Pending training requests
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM training_requests WHERE status = 'pending'");
    $stmt->execute();
    $stats['pending_training_requests'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['pending_training_requests'] = 0;
}

try {
    // Active succession plans
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM succession_plans WHERE status = 'active'");
    $stmt->execute();
    $stats['succession_plans'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['succession_plans'] = 0;
}

// Additional accurate statistics
try {
    // Total training modules
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM training_modules WHERE status = 'active'");
    $stmt->execute();
    $stats['total_training_modules'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['total_training_modules'] = 0;
}

try {
    // Completed trainings this month
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM training_enrollments WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stmt->execute();
    $stats['completed_trainings_month'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['completed_trainings_month'] = 0;
}

try {
    // HR managers count
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'hr_manager' AND status = 'active'");
    $stmt->execute();
    $stats['hr_managers'] = $stmt->fetch()['count'] ?? 0;
} catch (PDOException $e) {
    $stats['hr_managers'] = 0;
}

// Recent activities with error handling
$recent_activities = [];
try {
    $stmt = $db->prepare("
        SELECT sl.*, u.first_name, u.last_name 
        FROM system_logs sl 
        LEFT JOIN users u ON sl.user_id = u.id 
        ORDER BY sl.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activities = $stmt->fetchAll();
} catch (PDOException $e) {
    // If system_logs table doesn't exist, create sample activities
    $recent_activities = [
        ['action' => 'System initialized', 'first_name' => 'System', 'last_name' => 'Admin', 'created_at' => date('Y-m-d H:i:s')],
        ['action' => 'Database setup completed', 'first_name' => 'System', 'last_name' => 'Admin', 'created_at' => date('Y-m-d H:i:s')]
    ];
}

// Recent training requests with error handling
$recent_training_requests = [];
try {
    $stmt = $db->prepare("
        SELECT tr.*, u.first_name, u.last_name, tm.title as training_title
        FROM training_requests tr 
        LEFT JOIN users u ON tr.employee_id = u.id 
        LEFT JOIN training_modules tm ON tr.module_id = tm.id
        ORDER BY tr.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_training_requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $recent_training_requests = [];
}

// Employee performance trends (last 6 months) with error handling
$performance_trends = [];
try {
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(e.completed_at, '%Y-%m') as month,
            AVG(es.score) as avg_score,
            COUNT(*) as evaluation_count
        FROM evaluations e
        JOIN evaluation_scores es ON e.id = es.evaluation_id
        WHERE e.status = 'completed' 
        AND e.completed_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(e.completed_at, '%Y-%m')
        ORDER BY month
    ");
    $stmt->execute();
    $performance_trends = $stmt->fetchAll();
} catch (PDOException $e) {
    // If no data, create sample trends
    $performance_trends = [];
}

// Department distribution
$department_stats = [];
try {
    $stmt = $db->prepare("
        SELECT department, COUNT(*) as count 
        FROM users 
        WHERE role = 'employee' AND status = 'active' AND department IS NOT NULL
        GROUP BY department 
        ORDER BY count DESC
    ");
    $stmt->execute();
    $department_stats = $stmt->fetchAll();
} catch (PDOException $e) {
    $department_stats = [];
}

// Get employee-specific data using EmployeeManager
$employeeStats = [
    'my_evaluations' => 0,
    'my_trainings' => 0,
    'my_requests' => 0,
    'profile_complete' => 100
];

if ($current_user['role'] === 'employee') {
    try {
        $dashboardStats = $employeeManager->getEmployeeDashboardStats($current_user['id']);
        $employeeStats['my_evaluations'] = $dashboardStats['total_evaluations'];
        $employeeStats['my_trainings'] = $dashboardStats['total_trainings'];
        $employeeStats['my_requests'] = $dashboardStats['pending_requests'];
        
        // Calculate profile completeness
        $profileFields = ['first_name', 'last_name', 'email', 'phone', 'department', 'position'];
        $completedFields = 0;
        foreach ($profileFields as $field) {
            if (!empty($current_user[$field])) {
                $completedFields++;
            }
        }
        $employeeStats['profile_complete'] = round(($completedFields / count($profileFields)) * 100);
        
    } catch (Exception $e) {
        $employeeStats = [
            'my_evaluations' => 0,
            'my_trainings' => 0,
            'my_requests' => 0,
            'profile_complete' => 100
        ];
    }
}

// Competency Manager specific stats and notifications
$cmStats = [
	'total_models' => 0,
	'active_cycles' => 0,
	'pending_evaluations' => 0
];
$cmNotifications = [];

if ($auth->isCompetencyManager()) {
	try {
		$stmt = $db->prepare("SELECT COUNT(*) as count FROM competency_models");
		$stmt->execute();
		$cmStats['total_models'] = $stmt->fetch()['count'] ?? 0;
	} catch (PDOException $e) { $cmStats['total_models'] = 0; }

	try {
		$stmt = $db->prepare("SELECT COUNT(*) as count FROM evaluation_cycles");
		$stmt->execute();
		$cmStats['active_cycles'] = $stmt->fetch()['count'] ?? 0;
	} catch (PDOException $e) { $cmStats['active_cycles'] = 0; }

	try {
		$stmt = $db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE status IN ('pending','in_progress')");
		$stmt->execute();
		$cmStats['pending_evaluations'] = $stmt->fetch()['count'] ?? 0;
	} catch (PDOException $e) { $cmStats['pending_evaluations'] = 0; }

	try {
		$stmt = $db->prepare("SELECT id, title, message, created_at, action_url FROM competency_notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
		$stmt->execute([$current_user['id']]);
		$cmNotifications = $stmt->fetchAll();
	} catch (PDOException $e) { $cmNotifications = []; }
}

// Get announcements based on role/target audience
$announcementsForRole = [];
try {
    $audiences = ['all', $current_user['role']];
    $managerRoles = ['admin', 'hr_manager', 'competency_manager', 'learning_training_manager', 'succession_manager'];
    if (in_array($current_user['role'], $managerRoles, true)) {
        $audiences[] = 'managers';
    }
    if (in_array($current_user['role'], ['admin', 'hr_manager'], true)) {
        $audiences[] = 'hr';
    }
    if ($current_user['role'] === 'employee') {
        $audiences[] = 'employees';
    }
    $audiences = array_values(array_unique($audiences));
    $placeholders = implode(',', array_fill(0, count($audiences), '?'));
    
    $stmt = $db->prepare("
        SELECT a.*, u.first_name, u.last_name
        FROM announcements a
        LEFT JOIN users u ON a.created_by = u.id
        WHERE a.status = 'active'
        AND a.target_audience IN ($placeholders)
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $stmt->execute($audiences);
    $announcementsForRole = $stmt->fetchAll();
} catch (PDOException $e) {
    $announcementsForRole = [];
}

// Get announcements for admin management
$announcements = [];
if ($auth->hasPermission('manage_system')) {
    try {
        $stmt = $db->prepare("
            SELECT a.*, u.first_name, u.last_name
            FROM announcements a
            LEFT JOIN users u ON a.created_by = u.id
            WHERE a.status != 'archived'
            ORDER BY a.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        $announcements = $stmt->fetchAll();
    } catch (PDOException $e) {
        $announcements = [];
    }
}

// Helper functions for dashboard
function getActivityIcon($action) {
    $icons = [
        'login' => 'log-in',
        'logout' => 'log-out',
        'create' => 'plus',
        'update' => 'edit',
        'delete' => 'trash',
        'evaluation' => 'clipboard',
        'training' => 'book-open',
        'user' => 'user',
        'system' => 'settings'
    ];
    
    foreach ($icons as $key => $icon) {
        if (stripos($action, $key) !== false) {
            return $icon;
        }
    }
    return 'activity';
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>

<!-- Dashboard Specific CSS -->
<link rel="stylesheet" href="assets/css/dashboard.css">

<?php if (!empty($announcementsForRole)): ?>
<div class="row dashboard-section">
    <div class="col-12">
        <div class="card shadow" id="announcementsCard">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Announcements</strong>
                <?php if (count($announcementsForRole) > 2): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#announcementsModal">
                        View all
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($announcementsForRole, 0, 2) as $announcement): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?php echo htmlspecialchars($announcement['title']); ?></strong>
                                    <div class="text-muted small">
                                        <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?>
                                        &middot; <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?>
                                    </div>
                                </div>
                                <span class="badge badge-<?php echo $announcement['priority'] === 'high' ? 'danger' : ($announcement['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                    <?php echo ucfirst($announcement['priority']); ?>
                                </span>
                            </div>
                            <div class="text-muted small mt-2">
                                <?php echo htmlspecialchars($announcement['content']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($announcementsForRole) && count($announcementsForRole) > 2): ?>
<div class="modal fade" id="announcementsModal" tabindex="-1" role="dialog" aria-labelledby="announcementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="announcementsModalLabel">All Announcements</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($announcementsForRole as $announcement): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong><?php echo htmlspecialchars($announcement['title']); ?></strong>
                                    <div class="text-muted small">
                                        <?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?>
                                        &middot; <?php echo date('M j, Y', strtotime($announcement['created_at'])); ?>
                                    </div>
                                </div>
                                <span class="badge badge-<?php echo $announcement['priority'] === 'high' ? 'danger' : ($announcement['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                    <?php echo ucfirst($announcement['priority']); ?>
                                </span>
                            </div>
                            <div class="text-muted small mt-2">
                                <?php echo htmlspecialchars($announcement['content']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($current_user['role'] === 'employee'): ?>
<!-- Enhanced CSS for Employee Dashboard -->
<style>
.emp-dashboard-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 20px;
    padding: 2.5rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.emp-dashboard-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="1.5" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
    animation: float 25s infinite linear;
}

@keyframes float {
    0% { transform: translateX(-100px) translateY(-100px) rotate(0deg); }
    100% { transform: translateX(100px) translateY(100px) rotate(360deg); }
}

.emp-profile-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(15px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    z-index: 2;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

body.dark .emp-profile-card,
.dark .emp-profile-card {
    background: rgba(15, 20, 24, 0.6);
    border-color: rgba(255, 255, 255, 0.08);
}

.emp-avatar {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: bold;
    color: #333;
    border: 5px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    margin-bottom: 1rem;
}

.emp-stat-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    height: 100%;
}

body.dark .emp-stat-card,
.dark .emp-stat-card {
    background: #1e2428;
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}

.emp-stat-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.emp-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: var(--accent-color, #667eea);
    border-radius: 20px 20px 0 0;
}

.emp-stat-card.primary::before { background: linear-gradient(90deg, #667eea, #764ba2); }
.emp-stat-card.success::before { background: linear-gradient(90deg, #56ab2f, #a8e6cf); }
.emp-stat-card.info::before { background: linear-gradient(90deg, #00c6ff, #0072ff); }
.emp-stat-card.warning::before { background: linear-gradient(90deg, #f7971e, #ffd200); }
.emp-stat-card.danger::before { background: linear-gradient(90deg, #ff416c, #ff4b2b); }

.emp-stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.emp-stat-icon::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 20px;
    background: linear-gradient(45deg, rgba(255,255,255,0.3), transparent);
    pointer-events: none;
}

.emp-quick-action {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 3px solid transparent;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    color: inherit;
    display: block;
    position: relative;
    overflow: hidden;
}

body.dark .emp-quick-action,
.dark .emp-quick-action {
    background: #1e2428;
    border-color: rgba(255, 255, 255, 0.08);
    color: #e9ecef;
}

.emp-quick-action::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.6s;
}

.emp-quick-action:hover::before {
    left: 100%;
}

.emp-quick-action:hover {
    transform: translateY(-8px) scale(1.05);
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
    text-decoration: none;
    color: inherit;
    border-color: var(--accent-color, #667eea);
}

.emp-section-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

body.dark .emp-section-card,
.dark .emp-section-card {
    background: #1c2226;
    border-color: rgba(255, 255, 255, 0.08);
}

.emp-section-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

body.dark .emp-section-header,
.dark .emp-section-header {
    background: linear-gradient(135deg, #232b30 0%, #1d2428 100%);
    border-bottom-color: rgba(255, 255, 255, 0.08);
}

body.light .emp-profile-card,
.light .emp-profile-card {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.2);
}

body.light .emp-stat-card,
.light .emp-stat-card {
    background: #ffffff;
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

body.light .emp-quick-action,
.light .emp-quick-action {
    background: #ffffff;
    border-color: transparent;
    color: inherit;
}

body.light .emp-section-card,
.light .emp-section-card {
    background: #ffffff;
    border-color: rgba(0, 0, 0, 0.05);
}

body.light .emp-section-header,
.light .emp-section-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom-color: rgba(0, 0, 0, 0.1);
}

.emp-welcome-text {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.emp-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    font-weight: 300;
}

.emp-cta-button {
    background: rgba(255, 255, 255, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.3);
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.emp-cta-button:hover {
    background: rgba(255, 255, 255, 0.3);
    border-color: rgba(255, 255, 255, 0.5);
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
    .emp-dashboard-hero {
        padding: 2rem;
        text-align: center;
    }
    
    .emp-profile-card {
        text-align: center;
        padding: 1.5rem;
    }
    
    .emp-welcome-text {
        font-size: 2rem;
    }
    
    .emp-stat-card,
    .emp-quick-action {
        margin-bottom: 1.5rem;
    }
}
</style>

<!-- Employee Dashboard Content -->
<!-- Hero Section with Profile -->
<div class="emp-dashboard-hero">
                <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="emp-profile-card">
                <div class="row align-items-center">
                    <div class="col-md-auto text-center text-md-left">
                        <div class="emp-avatar mx-auto mx-md-0">
                            <?php echo strtoupper(substr($current_user['first_name'] ?? 'E', 0, 1) . substr($current_user['last_name'] ?? 'M', 0, 1)); ?>
                    </div>
                    </div>
                    <div class="col-md text-center text-md-left">
                        <h1 class="emp-welcome-text mb-2">Welcome back, <?php echo htmlspecialchars($current_user['first_name'] ?? 'Employee'); ?>!</h1>
                        <p class="emp-subtitle mb-2">
                            <i class="fe fe-briefcase fe-16 mr-2"></i>
                            <?php echo htmlspecialchars($current_user['position'] ?? 'Employee'); ?>
                        </p>
                        <p class="emp-subtitle mb-0">
                            <i class="fe fe-users fe-16 mr-2"></i>
                            <?php echo htmlspecialchars($current_user['department'] ?? 'Department'); ?>
                        </p>
                </div>
            </div>
        </div>
    </div>
        <div class="col-lg-4 text-center text-lg-right mt-4 mt-lg-0">
            <a href="?page=employee_profile" class="emp-cta-button">
                <i class="fe fe-user mr-2"></i>Edit Profile
            </a>
                    </div>
                    </div>
                </div>

<!-- Enhanced Statistics Dashboard -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="emp-stat-card primary">
            <div class="emp-stat-icon mx-auto" style="background: rgba(102, 126, 234, 0.15); color: #667eea;">
                <i class="fe fe-clipboard"></i>
            </div>
            <div class="text-center">
                <h2 class="mb-2" style="font-size: 3rem; font-weight: 700; color: #667eea;"><?php echo $employeeStats['my_evaluations']; ?></h2>
                <h6 class="text-muted mb-1">My Evaluations</h6>
                <p class="small text-muted mb-0">Performance reviews completed</p>
        </div>
    </div>
                    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="emp-stat-card success">
            <div class="emp-stat-icon mx-auto" style="background: rgba(86, 171, 47, 0.15); color: #56ab2f;">
                <i class="fe fe-book-open"></i>
                    </div>
            <div class="text-center">
                <h2 class="mb-2" style="font-size: 3rem; font-weight: 700; color: #56ab2f;"><?php echo $employeeStats['my_trainings']; ?></h2>
                <h6 class="text-muted mb-1">My Trainings</h6>
                <p class="small text-muted mb-0">Training courses completed</p>
                </div>
            </div>
        </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="emp-stat-card warning">
            <div class="emp-stat-icon mx-auto" style="background: rgba(247, 151, 30, 0.15); color: #f7971e;">
                <i class="fe fe-clock"></i>
    </div>
            <div class="text-center">
                <h2 class="mb-2" style="font-size: 3rem; font-weight: 700; color: #f7971e;"><?php echo $employeeStats['my_requests']; ?></h2>
                <h6 class="text-muted mb-1">My Requests</h6>
                <p class="small text-muted mb-0">Pending requests</p>
                    </div>
                    </div>
                </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="emp-stat-card <?php echo $employeeStats['profile_complete'] >= 80 ? 'success' : ($employeeStats['profile_complete'] >= 60 ? 'warning' : 'danger'); ?>">
            <div class="emp-stat-icon mx-auto" style="background: rgba(<?php echo $employeeStats['profile_complete'] >= 80 ? '86, 171, 47' : ($employeeStats['profile_complete'] >= 60 ? '247, 151, 30' : '255, 65, 108'); ?>, 0.15); color: <?php echo $employeeStats['profile_complete'] >= 80 ? '#56ab2f' : ($employeeStats['profile_complete'] >= 60 ? '#f7971e' : '#ff416c'); ?>;">
                <i class="fe fe-user"></i>
            </div>
            <div class="text-center">
                <h2 class="mb-2" style="font-size: 3rem; font-weight: 700; color: <?php echo $employeeStats['profile_complete'] >= 80 ? '#56ab2f' : ($employeeStats['profile_complete'] >= 60 ? '#f7971e' : '#ff416c'); ?>;"><?php echo $employeeStats['profile_complete']; ?>%</h2>
                <h6 class="text-muted mb-1">Profile Complete</h6>
                <p class="small text-muted mb-0">Profile completeness</p>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Quick Actions -->
<div class="emp-section-card mb-4">
    <div class="emp-section-header">
        <h4 class="mb-0">
            <i class="fe fe-zap text-primary mr-3"></i>Quick Actions
        </h4>
        <p class="text-muted mb-0 mt-2">Access your most used features</p>
            </div>
    <div class="card-body p-4">
                <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="?page=employee_profile" class="emp-quick-action">
                    <div class="emp-stat-icon mx-auto mb-3" style="background: rgba(102, 126, 234, 0.15); color: #667eea;">
                        <i class="fe fe-user"></i>
                    </div>
                    <h5 class="mb-2">Edit Profile</h5>
                    <p class="text-muted small mb-0">Update personal information</p>
                        </a>
                    </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="?page=my_evaluations" class="emp-quick-action">
                    <div class="emp-stat-icon mx-auto mb-3" style="background: rgba(0, 198, 255, 0.15); color: #00c6ff;">
                        <i class="fe fe-clipboard"></i>
                    </div>
                    <h5 class="mb-2">My Evaluations</h5>
                    <p class="text-muted small mb-0">View performance reviews</p>
                        </a>
                    </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="?page=employee_training_requests" class="emp-quick-action">
                    <div class="emp-stat-icon mx-auto mb-3" style="background: rgba(86, 171, 47, 0.15); color: #56ab2f;">
                        <i class="fe fe-book-open"></i>
                    </div>
                    <h5 class="mb-2">Training Requests</h5>
                    <p class="text-muted small mb-0">Request new training courses</p>
                        </a>
                    </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <a href="?page=my_requests" class="emp-quick-action">
                    <div class="emp-stat-icon mx-auto mb-3" style="background: rgba(247, 151, 30, 0.15); color: #f7971e;">
                        <i class="fe fe-file-text"></i>
                </div>
                    <h5 class="mb-2">My Requests</h5>
                    <p class="text-muted small mb-0">Track request status</p>
                </a>
            </div>
        </div>
    </div>
</div>


<?php elseif ($auth->isCompetencyManager()): ?>
<!-- Competency Manager Dashboard Content -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-primary"><span class="fe fe-target fe-32"></span></div>
                <div>
                    <div class="text-muted small">Competency Models</div>
                    <div class="h3 mb-0"><?php echo (int)$cmStats['total_models']; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-info"><span class="fe fe-bar-chart-2 fe-32"></span></div>
                <div>
                    <div class="text-muted small">Evaluation Cycles</div>
                    <div class="h3 mb-0"><?php echo (int)$cmStats['active_cycles']; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-warning"><span class="fe fe-clipboard fe-32"></span></div>
                <div>
                    <div class="text-muted small">Pending Evaluations</div>
                    <div class="h3 mb-0"><?php echo (int)$cmStats['pending_evaluations']; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title">Recent Competency Notifications</strong>
                <a href="?page=competency_models" class="btn btn-sm btn-primary">Go to Models</a>
            </div>
            <div class="card-body">
                <?php if (empty($cmNotifications)): ?>
                    <div class="text-center text-muted py-4 dashboard-empty">
                        <i class="fe fe-bell fe-48 mb-2"></i>
                        <div>No recent notifications.</div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($cmNotifications as $n): ?>
                            <a href="<?php echo htmlspecialchars($n['action_url'] ?? '#'); ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($n['title']); ?></h6>
                                    <small class="text-muted"><?php echo timeAgo($n['created_at']); ?></small>
                                </div>
                                <p class="mb-1 text-muted small"><?php echo htmlspecialchars($n['message']); ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Quick Actions</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3"><a href="?page=competency_models" class="btn btn-outline-primary btn-block"><span class="fe fe-target mr-2"></span>Models</a></div>
                    <div class="col-6 mb-3"><a href="?page=evaluation_cycles" class="btn btn-outline-secondary btn-block"><span class="fe fe-bar-chart-2 mr-2"></span>Cycles</a></div>
                    <div class="col-6 mb-3"><a href="?page=evaluations" class="btn btn-outline-info btn-block"><span class="fe fe-clipboard mr-2"></span>Evaluations</a></div>
                    <div class="col-6 mb-3"><a href="?page=competency_reports" class="btn btn-outline-success btn-block"><span class="fe fe-pie-chart mr-2"></span>Reports</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php elseif ($auth->isLearningTrainingManager()): ?>
<!-- Learning & Training Manager Dashboard Content -->
<?php
// Get learning and training statistics
require_once 'includes/functions/learning.php';
$learningManager = new LearningManager();

$allTrainings = $learningManager->getAllTrainings();
$activeTrainings = array_filter($allTrainings, function($training) {
    return $training['status'] === 'active';
});

$totalCourses = count($activeTrainings);
$totalSessions = 0;
$totalEnrollments = 0;

// Get session and enrollment counts
$stmt = $db->prepare("SELECT COUNT(*) as session_count FROM training_sessions WHERE status = 'active'");
$stmt->execute();
$sessionResult = $stmt->fetch();
$totalSessions = $sessionResult['session_count'];

$stmt = $db->prepare("SELECT COUNT(*) as enrollment_count FROM training_enrollments WHERE status IN ('enrolled', 'completed')");
$stmt->execute();
$enrollmentResult = $stmt->fetch();
$totalEnrollments = $enrollmentResult['enrollment_count'];
?>
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-primary"><span class="fe fe-book fe-32"></span></div>
                <div>
                    <div class="text-muted small">Training Courses</div>
                    <div class="h3 mb-0"><?php echo $totalCourses; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-success"><span class="fe fe-calendar fe-32"></span></div>
                <div>
                    <div class="text-muted small">Training Sessions</div>
                    <div class="h3 mb-0"><?php echo $totalSessions; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-info"><span class="fe fe-users fe-32"></span></div>
                <div>
                    <div class="text-muted small">Total Enrollments</div>
                    <div class="h3 mb-0"><?php echo $totalEnrollments; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-warning"><span class="fe fe-trending-up fe-32"></span></div>
                <div>
                    <div class="text-muted small">Active Programs</div>
                    <div class="h3 mb-0"><?php echo $totalCourses; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Learning Management Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fe fe-bar-chart-2 mr-2"></i>Training Program Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="text-primary">
                            <span class="fe fe-book fe-48"></span>
                            <h4 class="mt-2"><?php echo $totalCourses; ?></h4>
                            <p class="text-muted">Active Courses</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="text-success">
                            <span class="fe fe-calendar fe-48"></span>
                            <h4 class="mt-2"><?php echo $totalSessions; ?></h4>
                            <p class="text-muted">Scheduled Sessions</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="text-info">
                            <span class="fe fe-users fe-48"></span>
                            <h4 class="mt-2"><?php echo $totalEnrollments; ?></h4>
                            <p class="text-muted">Total Enrollments</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fe fe-activity mr-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="?page=learning_management" class="btn btn-primary">
                        <i class="fe fe-plus mr-2"></i>Create Course
                    </a>
                    <a href="?page=training_management" class="btn btn-outline-primary">
                        <i class="fe fe-calendar mr-2"></i>Schedule Session
                    </a>
                    <a href="?page=learning_management" class="btn btn-outline-secondary">
                        <i class="fe fe-bar-chart-2 mr-2"></i>View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Learning Activities -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fe fe-bell mr-2"></i>Recent Learning Notifications</h5>
            </div>
            <div class="card-body">
                <?php
                // Get recent learning notifications
                $stmt = $db->prepare("
                    SELECT * FROM competency_notifications 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $stmt->execute([$current_user['id']]);
                $notifications = $stmt->fetchAll();
                
                if (empty($notifications)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fe fe-bell-off fe-48 mb-3"></i>
                        <p>No recent notifications</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($notification['message']); ?></div>
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?></small>
                                </div>
                                <?php if (!$notification['is_read']): ?>
                                    <span class="badge bg-primary rounded-pill">New</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php elseif ($auth->isSuccessionManager()): ?>
<!-- Succession Manager Dashboard Content -->
<?php
// Get succession planning statistics
require_once 'includes/functions/succession_planning.php';
$successionManager = new SuccessionPlanning();

$criticalRoles = $successionManager->getAllCriticalRoles();
$successionCandidates = $successionManager->getAllCandidates();

$totalRoles = count($criticalRoles);
$totalCandidates = count($successionCandidates);
$readyNowCandidates = 0;
$readySoonCandidates = 0;
$developmentNeededCandidates = 0;

foreach ($successionCandidates as $candidate) {
    switch ($candidate['readiness_level']) {
        case 'ready_now':
            $readyNowCandidates++;
            break;
        case 'ready_soon':
            $readySoonCandidates++;
            break;
        case 'development_needed':
            $developmentNeededCandidates++;
            break;
    }
}

// Get high-risk roles
$highRiskRoles = 0;
foreach ($criticalRoles as $role) {
    if ($role['risk_level'] === 'high') {
        $highRiskRoles++;
    }
}
?>
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-primary"><span class="fe fe-users fe-32"></span></div>
                <div>
                    <div class="text-muted small">Critical Roles</div>
                    <div class="h3 mb-0"><?php echo $totalRoles; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-success"><span class="fe fe-user-check fe-32"></span></div>
                <div>
                    <div class="text-muted small">Succession Candidates</div>
                    <div class="h3 mb-0"><?php echo $totalCandidates; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-warning"><span class="fe fe-alert-triangle fe-32"></span></div>
                <div>
                    <div class="text-muted small">High Risk Roles</div>
                    <div class="h3 mb-0"><?php echo $highRiskRoles; ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow">
            <div class="card-body d-flex align-items-center">
                <div class="mr-3 text-info"><span class="fe fe-target fe-32"></span></div>
                <div>
                    <div class="text-muted small">Ready Now</div>
                    <div class="h3 mb-0"><?php echo $readyNowCandidates; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Succession Planning Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fe fe-bar-chart-2 mr-2"></i>Succession Pipeline Overview</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="text-success">
                            <span class="fe fe-check-circle fe-48"></span>
                            <h4 class="mt-2"><?php echo $readyNowCandidates; ?></h4>
                            <p class="text-muted">Ready Now</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="text-warning">
                            <span class="fe fe-clock fe-48"></span>
                            <h4 class="mt-2"><?php echo $readySoonCandidates; ?></h4>
                            <p class="text-muted">Ready Soon</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="text-info">
                            <span class="fe fe-trending-up fe-48"></span>
                            <h4 class="mt-2"><?php echo $developmentNeededCandidates; ?></h4>
                            <p class="text-muted">Development Needed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fe fe-activity mr-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="?page=succession_planning" class="btn btn-primary">
                        <i class="fe fe-plus mr-2"></i>Create Critical Role
                    </a>
                    <a href="?page=succession_planning" class="btn btn-outline-primary">
                        <i class="fe fe-user-plus mr-2"></i>Assign Candidate
                    </a>
                    <a href="?page=succession_planning" class="btn btn-outline-secondary">
                        <i class="fe fe-bar-chart-2 mr-2"></i>View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Succession Activities -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0"><i class="fe fe-bell mr-2"></i>Recent Succession Notifications</h5>
            </div>
            <div class="card-body">
                <?php
                // Get recent succession notifications
                $stmt = $db->prepare("
                    SELECT * FROM competency_notifications 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 5
                ");
                $stmt->execute([$current_user['id']]);
                $notifications = $stmt->fetchAll();
                
                if (empty($notifications)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fe fe-bell-off fe-48 mb-3"></i>
                        <p>No recent notifications</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold"><?php echo htmlspecialchars($notification['title']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($notification['message']); ?></div>
                                    <small class="text-muted"><?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?></small>
                                </div>
                                <?php if (!$notification['is_read']): ?>
                                    <span class="badge bg-primary rounded-pill">New</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Admin/HR Manager Dashboard Content -->
<div class="row dashboard-header">
    <div class="col-lg-8">
        <h1 class="h3 mb-1 title">
            <?php if ($auth->isHRManager()): ?>
                HR Manager Dashboard
            <?php elseif ($auth->isAdmin()): ?>
                Admin Dashboard
            <?php else: ?>
                HR Dashboard
            <?php endif; ?>
        </h1>
        <p class="text-muted subtitle">
            <?php if ($auth->isHRManager()): ?>
                Manage competencies, evaluations, training programs, and employee development initiatives.
            <?php elseif ($auth->isAdmin()): ?>
                Monitor system performance, manage users, and oversee HR operations.
            <?php else: ?>
                Welcome to your Human Resources Management System.
            <?php endif; ?>
        </p>
    </div>
    <div class="col-lg-4 text-lg-right text-muted small"></div>
</div>

<!-- Stats Cards -->
<div class="row dashboard-metrics">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Total Employees</h6>
                        <span class="h2 mb-0"><?php echo $stats['total_employees']; ?></span>
                        <span class="badge badge-success ml-2">Active</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-users fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Active Evaluations</h6>
                        <span class="h2 mb-0"><?php echo $stats['active_evaluations']; ?></span>
                        <span class="badge badge-warning ml-2">Pending</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-clipboard fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Active Trainings</h6>
                        <span class="h2 mb-0"><?php echo $stats['active_trainings']; ?></span>
                        <span class="badge badge-info ml-2">Enrolled</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-book-open fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Completed Evaluations</h6>
                        <span class="h2 mb-0"><?php echo $stats['completed_evaluations']; ?></span>
                        <span class="badge badge-success ml-2">This Month</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-check-circle fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Stats Row -->
<div class="row dashboard-section">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Pending Requests</h6>
                        <span class="h2 mb-0"><?php echo $stats['pending_requests']; ?></span>
                        <span class="badge badge-warning ml-2">Awaiting</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-file-text fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Training Requests</h6>
                        <span class="h2 mb-0"><?php echo $stats['pending_training_requests']; ?></span>
                        <span class="badge badge-info ml-2">Pending</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-book-open fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Succession Plans</h6>
                        <span class="h2 mb-0"><?php echo $stats['succession_plans']; ?></span>
                        <span class="badge badge-info ml-2">Active</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-trending-up fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Statistics Row -->
<div class="row dashboard-section">
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Training Modules</h6>
                        <span class="h2 mb-0"><?php echo $stats['total_training_modules']; ?></span>
                        <span class="badge badge-success ml-2">Available</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-book fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">Completed This Month</h6>
                        <span class="h2 mb-0"><?php echo $stats['completed_trainings_month']; ?></span>
                        <span class="badge badge-success ml-2">Trainings</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-check-circle fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card shadow dashboard-metric-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="text-uppercase text-muted mb-2">HR Managers</h6>
                        <span class="h2 mb-0"><?php echo $stats['hr_managers']; ?></span>
                        <span class="badge badge-primary ml-2">Active</span>
                    </div>
                    <div class="col-auto">
                        <span class="fe fe-user-check fe-24 text-muted"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Recent Activity -->
<div class="row dashboard-section">
    <div class="col-md-8 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Employee Growth Trend</strong>
            </div>
            <div class="card-body">
                <canvas id="employeeChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Department Distribution</strong>
            </div>
            <div class="card-body">
                <?php if (empty($department_stats)): ?>
                    <div class="text-center text-muted py-4 dashboard-empty">
                        <i class="fe fe-building fe-48 mb-3"></i>
                        <p>No department data available.</p>
                    </div>
                <?php else: ?>
                    <canvas id="departmentChart" width="400" height="200"></canvas>
                    <script>
                    // Department Chart Data
                    const departmentData = <?php echo json_encode($department_stats); ?>;
                    </script>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($auth->hasPermission('manage_system')): ?>
<!-- Admin Announcements Management -->
<div class="row mb-4 dashboard-section">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Announcements Management</h5>
                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#createAnnouncementModal">
                    <i class="fe fe-plus fe-16 mr-2"></i>Create Announcement
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <div class="text-center text-muted py-4 dashboard-empty">
                        <i class="fe fe-megaphone fe-48 mb-3"></i>
                        <p>No announcements found.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Target Audience</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($announcements as $announcement): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($announcement['title']); ?></strong>
                                            <div class="text-muted small"><?php echo htmlspecialchars(substr($announcement['content'], 0, 50)) . '...'; ?></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $announcement['priority'] === 'high' ? 'danger' : ($announcement['priority'] === 'medium' ? 'warning' : 'info'); ?>">
                                                <?php echo ucfirst($announcement['priority']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($announcement['target_audience']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $announcement['status'] === 'active' ? 'success' : ($announcement['status'] === 'draft' ? 'warning' : 'secondary'); ?>">
                                                <?php echo ucfirst($announcement['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($announcement['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="editAnnouncement(<?php echo htmlspecialchars(json_encode($announcement)); ?>)">
                                                    <i class="fe fe-edit fe-14"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAnnouncement(<?php echo $announcement['id']; ?>)">
                                                    <i class="fe fe-trash fe-14"></i>
                                                </button>
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
<?php endif; ?>

<?php if ($auth->isHRManager() && $hr_manager): ?>
<!-- HR Manager Alerts -->
<div class="row mb-4 dashboard-section">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">
                    <span class="fe fe-bell fe-16 mr-2"></span>
                    HR Manager Alerts
                </strong>
            </div>
            <div class="card-body">
                <?php 
                $hr_alerts = $hr_manager->getHRAlerts();
                if (empty($hr_alerts)): ?>
                    <div class="text-center text-muted py-3 dashboard-empty">
                        <i class="fe fe-check-circle fe-24 mb-2 text-success"></i>
                        <p class="mb-0">All caught up! No urgent alerts at this time.</p>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($hr_alerts as $alert): ?>
                            <div class="col-md-6 mb-3">
                                <div class="alert alert-<?php echo $alert['type']; ?> alert-dismissible fade show">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <strong><?php echo htmlspecialchars($alert['title']); ?></strong>
                                            <div class="small"><?php echo htmlspecialchars($alert['message']); ?></div>
                                        </div>
                                        <div class="ml-3">
                                            <a href="<?php echo htmlspecialchars($alert['url']); ?>" class="btn btn-sm btn-outline-<?php echo $alert['type']; ?>">
                                                <?php echo htmlspecialchars($alert['action']); ?>
                                            </a>
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
<?php endif; ?>

<!-- Quick Actions -->
<div class="row dashboard-section">
    <div class="col-12 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">
                    <?php if ($auth->isHRManager()): ?>
                        HR Manager Quick Actions
                    <?php elseif ($auth->isAdmin()): ?>
                        Admin Quick Actions
                    <?php else: ?>
                        Quick Actions
                    <?php endif; ?>
                </strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($auth->isHRManager()): ?>
                        <!-- HR Manager Actions -->
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=competency_models" class="btn btn-outline-primary btn-block">
                                <span class="fe fe-target fe-16 mr-2"></span>
                                Competency Models
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=evaluation_cycles" class="btn btn-outline-secondary btn-block">
                                <span class="fe fe-bar-chart-2 fe-16 mr-2"></span>
                                Evaluation Cycles
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=learning_management_enhanced" class="btn btn-outline-success btn-block">
                                <span class="fe fe-book-open fe-16 mr-2"></span>
                                Learning Management
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=training_requests" class="btn btn-outline-warning btn-block">
                                <span class="fe fe-file-text fe-16 mr-2"></span>
                                Training Requests
                                <?php if ($stats['pending_training_requests'] > 0): ?>
                                <span class="badge badge-warning ml-1"><?php echo $stats['pending_training_requests']; ?></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=succession_planning" class="btn btn-outline-info btn-block">
                                <span class="fe fe-trending-up fe-16 mr-2"></span>
                                Succession Planning
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=hr_employee_management" class="btn btn-outline-dark btn-block">
                                <span class="fe fe-users fe-16 mr-2"></span>
                                Employee Management
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=hr_reports" class="btn btn-outline-purple btn-block">
                                <span class="fe fe-pie-chart fe-16 mr-2"></span>
                                HR Reports
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=evaluations" class="btn btn-outline-teal btn-block">
                                <span class="fe fe-clipboard fe-16 mr-2"></span>
                                Manage Evaluations
                            </a>
                        </div>
                    <?php elseif ($auth->isAdmin()): ?>
                        <!-- Admin Actions -->
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=user_management" class="btn btn-outline-primary btn-block">
                                <span class="fe fe-user-plus fe-16 mr-2"></span>
                                Manage Users
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=system_settings" class="btn btn-outline-success btn-block">
                                <span class="fe fe-settings fe-16 mr-2"></span>
                                System Settings
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=system_logs" class="btn btn-outline-info btn-block">
                                <span class="fe fe-file-text fe-16 mr-2"></span>
                                System Logs
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=reports" class="btn btn-outline-warning btn-block">
                                <span class="fe fe-bar-chart-2 fe-16 mr-2"></span>
                                Generate Reports
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Default Actions -->
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=employee_self_service" class="btn btn-outline-primary btn-block">
                                <span class="fe fe-user-plus fe-16 mr-2"></span>
                                Add Employee
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=training_management" class="btn btn-outline-success btn-block">
                                <span class="fe fe-book-open fe-16 mr-2"></span>
                                Schedule Training
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=recruitment" class="btn btn-outline-info btn-block">
                                <span class="fe fe-briefcase fe-16 mr-2"></span>
                                Post Job
                            </a>
                        </div>
                        <div class="col-6 col-lg-3 mb-3">
                            <a href="?page=hr_reports" class="btn btn-outline-warning btn-block">
                                <span class="fe fe-bar-chart-2 fe-16 mr-2"></span>
                                Generate Report
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Recent Training Requests -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Recent Training Requests</strong>
                <div class="card-actions">
                    <a href="?page=training_requests" class="btn btn-sm btn-primary">
                        <span class="fe fe-eye fe-16 mr-1"></span>View All
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($recent_training_requests)): ?>
                    <div class="text-center text-muted py-4 dashboard-empty">
                        <i class="fe fe-book-open fe-48 mb-3"></i>
                        <p class="mb-0">No training requests found.</p>
                        <small>Training requests will appear here once employees submit them.</small>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Training</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_training_requests as $request): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['training_title'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $request['priority'] === 'urgent' ? 'danger' : ($request['priority'] === 'high' ? 'warning' : ($request['priority'] === 'medium' ? 'info' : 'secondary')); ?>">
                                                <?php echo ucfirst($request['priority'] ?? 'medium'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $request['status'] === 'approved' ? 'success' : ($request['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                                <?php echo ucfirst($request['status'] ?? 'pending'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $request['created_at'] ? date('M d, Y', strtotime($request['created_at'])) : 'N/A'; ?></td>
                                        <td>
                                            <a href="?page=training_requests" class="btn btn-sm btn-outline-primary">
                                                <span class="fe fe-eye fe-16 mr-1"></span>Review
                                            </a>
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

<script src="assets/vendor/js/Chart.min.js"></script>
<script>
// Employee Growth Chart
const ctx1 = document.getElementById('employeeChart').getContext('2d');
const employeeChart = new Chart(ctx1, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Employee Count',
            data: [<?php echo $stats['total_employees']; ?>, <?php echo $stats['total_employees'] + 2; ?>, <?php echo $stats['total_employees'] + 1; ?>, <?php echo $stats['total_employees'] + 3; ?>, <?php echo $stats['total_employees'] + 2; ?>, <?php echo $stats['total_employees']; ?>],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Department Distribution Chart
<?php if (!empty($department_stats)): ?>
const ctx2 = document.getElementById('departmentChart').getContext('2d');
const departmentChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($department_stats, 'department')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($department_stats, 'count')); ?>,
            backgroundColor: [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0',
                '#9966FF',
                '#FF9F40'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
<?php endif; ?>
</script>

<?php if ($auth->hasPermission('manage_system')): ?>
<!-- Create Announcement Modal -->
<div class="modal fade" id="createAnnouncementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Announcement</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority">Priority *</label>
                                <select class="form-control" id="priority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="target_audience">Target Audience *</label>
                                <select class="form-control" id="target_audience" name="target_audience" required>
                                    <option value="all">All Employees</option>
                                    <option value="managers">Managers Only</option>
                                    <option value="hr">HR Team</option>
                                    <option value="specific">Specific Department</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_announcement" class="btn btn-primary">Create Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div class="modal fade" id="editAnnouncementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Announcement</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="edit_announcement_id" name="announcement_id">
                    <div class="form-group">
                        <label for="edit_title">Title *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_content">Content *</label>
                        <textarea class="form-control" id="edit_content" name="content" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_priority">Priority *</label>
                                <select class="form-control" id="edit_priority" name="priority" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_target_audience">Target Audience *</label>
                                <select class="form-control" id="edit_target_audience" name="target_audience" required>
                                    <option value="all">All Employees</option>
                                    <option value="managers">Managers Only</option>
                                    <option value="hr">HR Team</option>
                                    <option value="specific">Specific Department</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="edit_status">Status *</label>
                                <select class="form-control" id="edit_status" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_announcement" class="btn btn-primary">Update Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Announcement Modal -->
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Archive</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="delete_announcement_id" name="announcement_id">
                    <p>Are you sure you want to archive this announcement? This action can be reversed later.</p>
                    <div class="alert alert-warning">
                        <strong>Note:</strong> The announcement will be marked as archived and will not be visible to employees.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_announcement" class="btn btn-danger">Archive Announcement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editAnnouncement(announcement) {
    document.getElementById('edit_announcement_id').value = announcement.id;
    document.getElementById('edit_title').value = announcement.title;
    document.getElementById('edit_content').value = announcement.content;
    document.getElementById('edit_priority').value = announcement.priority;
    document.getElementById('edit_target_audience').value = announcement.target_audience;
    document.getElementById('edit_status').value = announcement.status || 'active';
    
    $('#editAnnouncementModal').modal('show');
}

function deleteAnnouncement(announcementId) {
    document.getElementById('delete_announcement_id').value = announcementId;
    $('#deleteAnnouncementModal').modal('show');
}
</script>
<?php endif; ?>
<?php endif; ?>






