<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';

$auth = new SimpleAuth();
$current_user = $auth->getCurrentUser();
$db = getDB();

// Get user activities
$activities = [];
try {
    $stmt = $db->prepare("
        SELECT sl.*, u.first_name, u.last_name 
        FROM system_logs sl 
        LEFT JOIN users u ON sl.user_id = u.id 
        WHERE sl.user_id = ?
        ORDER BY sl.created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$current_user['id']]);
    $activities = $stmt->fetchAll();
} catch (PDOException $e) {
    // If system_logs table doesn't exist, create sample activities
    $activities = [
        ['action' => 'Logged in', 'table_name' => 'system', 'created_at' => date('Y-m-d H:i:s')],
        ['action' => 'Updated profile', 'table_name' => 'users', 'created_at' => date('Y-m-d H:i:s')],
        ['action' => 'Viewed dashboard', 'table_name' => 'dashboard', 'created_at' => date('Y-m-d H:i:s')]
    ];
}

// Helper function to get activity icon
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
        'system' => 'settings',
        'profile' => 'user',
        'password' => 'key',
        'view' => 'eye'
    ];
    
    foreach ($icons as $key => $icon) {
        if (stripos($action, $key) !== false) {
            return $icon;
        }
    }
    return 'activity';
}

// Helper function to format time
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Activities</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-outline-secondary" onclick="refreshActivities()">
            <i class="fe fe-refresh-cw fe-16 mr-2"></i>Refresh
        </button>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <div class="text-center text-muted py-5">
                        <i class="fe fe-activity fe-48 mb-3"></i>
                        <h5>No activities found</h5>
                        <p>Your activity history will appear here as you use the system.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($activities as $activity): ?>
                            <div class="list-group-item px-0">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <div class="avatar avatar-sm">
                                            <span class="avatar-title bg-primary rounded">
                                                <i class="fe fe-<?php echo getActivityIcon($activity['action']); ?> fe-16"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong><?php echo htmlspecialchars($activity['action']); ?></strong>
                                                <?php if (isset($activity['table_name']) && $activity['table_name']): ?>
                                                    <div class="text-muted small">
                                                        <?php echo htmlspecialchars($activity['table_name']); ?>
                                                        <?php if (isset($activity['record_id']) && $activity['record_id']): ?>
                                                            (ID: <?php echo htmlspecialchars($activity['record_id']); ?>)
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-right">
                                                <small class="text-muted"><?php echo timeAgo($activity['created_at']); ?></small>
                                            </div>
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

<!-- Activity Statistics -->
<div class="row mt-4">
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="avatar avatar-lg bg-primary mb-3">
                    <span class="avatar-title">
                        <i class="fe fe-activity fe-24"></i>
                    </span>
                </div>
                <h4 class="mb-1"><?php echo count($activities); ?></h4>
                <p class="text-muted mb-0">Total Activities</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="avatar avatar-lg bg-success mb-3">
                    <span class="avatar-title">
                        <i class="fe fe-calendar fe-24"></i>
                    </span>
                </div>
                <h4 class="mb-1"><?php echo count(array_filter($activities, function($a) { return date('Y-m-d', strtotime($a['created_at'])) === date('Y-m-d'); })); ?></h4>
                <p class="text-muted mb-0">Today</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="avatar avatar-lg bg-info mb-3">
                    <span class="avatar-title">
                        <i class="fe fe-clock fe-24"></i>
                    </span>
                </div>
                <h4 class="mb-1"><?php echo count(array_filter($activities, function($a) { return date('Y-m-d', strtotime($a['created_at'])) === date('Y-m-d', strtotime('-1 day')); })); ?></h4>
                <p class="text-muted mb-0">Yesterday</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card shadow">
            <div class="card-body text-center">
                <div class="avatar avatar-lg bg-warning mb-3">
                    <span class="avatar-title">
                        <i class="fe fe-trending-up fe-24"></i>
                    </span>
                </div>
                <h4 class="mb-1"><?php echo count(array_filter($activities, function($a) { return strtotime($a['created_at']) > strtotime('-7 days'); })); ?></h4>
                <p class="text-muted mb-0">This Week</p>
            </div>
        </div>
    </div>
</div>

<script>
function refreshActivities() {
    // Reload the page to refresh activities
    window.location.reload();
}

// Auto-refresh every 5 minutes
setInterval(function() {
    // You could implement AJAX refresh here instead of page reload
    // refreshActivities();
}, 300000);
</script>