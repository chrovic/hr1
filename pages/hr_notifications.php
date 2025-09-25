<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/hr_manager.php';

// Get database connection
$db = getDB();

// Initialize authentication
$auth = new SimpleAuth();

// Check if user is logged in and is HR Manager or Admin
if (!$auth->isLoggedIn() || (!$auth->isHRManager() && !$auth->isAdmin())) {
    header('Location: auth/login.php');
    exit;
}

// Initialize HR Manager
$hr_manager = new HRManager($db);

// Get HR alerts and notifications
$hr_alerts = $hr_manager->getHRAlerts();
$upcoming_events = $hr_manager->getUpcomingHREvents(30);

// Handle notification actions
if ($_POST['action'] ?? false) {
    $action = $_POST['action'];
    $notification_id = $_POST['notification_id'] ?? null;
    
    switch ($action) {
        case 'mark_read':
            // Mark notification as read
            // Implementation will be added when notification system is created
            break;
        case 'dismiss':
            // Dismiss notification
            // Implementation will be added when notification system is created
            break;
    }
}
?>

<div class="row">
    <div class="col-12">
        <div class="mb-2">
            <h1 class="h3 mb-1">HR Notifications & Alerts</h1>
            <p class="text-muted">Stay updated with important HR activities and alerts</p>
        </div>
    </div>
</div>

<!-- HR Alerts -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">
                    <span class="fe fe-bell fe-16 mr-2"></span>
                    HR Alerts & Notifications
                </strong>
            </div>
            <div class="card-body">
                <?php if (empty($hr_alerts)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-check-circle fe-48 mb-3 text-success"></i>
                        <p class="mb-0">All caught up! No urgent alerts at this time.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($hr_alerts as $alert): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <?php if ($alert['type'] === 'danger'): ?>
                                            <span class="fe fe-alert-triangle fe-16 text-danger"></span>
                                        <?php elseif ($alert['type'] === 'warning'): ?>
                                            <span class="fe fe-alert-circle fe-16 text-warning"></span>
                                        <?php else: ?>
                                            <span class="fe fe-info fe-16 text-info"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex align-items-center">
                                            <strong class="mr-2"><?php echo htmlspecialchars($alert['title']); ?></strong>
                                            <span class="badge badge-<?php echo $alert['type']; ?> badge-sm"><?php echo ucfirst($alert['type']); ?></span>
                                        </div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($alert['message']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <a href="<?php echo htmlspecialchars($alert['url']); ?>" class="btn btn-sm btn-outline-primary">
                                            <?php echo htmlspecialchars($alert['action']); ?>
                                        </a>
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

<!-- Upcoming Events -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">
                    <span class="fe fe-calendar fe-16 mr-2"></span>
                    Upcoming HR Events
                </strong>
            </div>
            <div class="card-body">
                <?php if (empty($upcoming_events)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fe fe-calendar fe-48 mb-3 text-muted"></i>
                        <p class="mb-0">No upcoming events scheduled.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="list-group-item px-0 border-0">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="fe fe-<?php echo $event['icon']; ?> fe-16 text-primary"></span>
                                    </div>
                                    <div class="col">
                                        <div class="d-flex align-items-center">
                                            <strong class="mr-2"><?php echo htmlspecialchars($event['title']); ?></strong>
                                            <span class="badge badge-light badge-sm">
                                                <?php echo date('M j, Y', strtotime($event['date'])); ?>
                                            </span>
                                        </div>
                                        <div class="text-muted small">
                                            <?php if ($event['location']): ?>
                                                <span class="fe fe-map-pin fe-12 mr-1"></span>
                                                <?php echo htmlspecialchars($event['location']); ?>
                                            <?php endif; ?>
                                            <?php if ($event['attendees'] > 0): ?>
                                                <span class="fe fe-users fe-12 mr-1 ml-2"></span>
                                                <?php echo $event['attendees']; ?> attendees
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <small class="text-muted">
                                            <?php echo timeAgo($event['date']); ?>
                                        </small>
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

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Quick Actions</strong>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <a href="?page=evaluation_cycles" class="btn btn-outline-primary btn-block">
                            <span class="fe fe-target fe-16 mr-2"></span>
                            Review Evaluations
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="?page=employee_requests" class="btn btn-outline-warning btn-block">
                            <span class="fe fe-file-text fe-16 mr-2"></span>
                            Handle Requests
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="?page=learning_management" class="btn btn-outline-success btn-block">
                            <span class="fe fe-book-open fe-16 mr-2"></span>
                            Manage Learning
                        </a>
                    </div>
                    <div class="col-6 mb-3">
                        <a href="?page=succession_planning" class="btn btn-outline-info btn-block">
                            <span class="fe fe-trending-up fe-16 mr-2"></span>
                            Succession Plans
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow">
            <div class="card-header">
                <strong class="card-title">Notification Settings</strong>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="email_notifications" checked>
                            <label class="custom-control-label" for="email_notifications">
                                Email Notifications
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="urgent_alerts" checked>
                            <label class="custom-control-label" for="urgent_alerts">
                                Urgent Alerts Only
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="daily_summary">
                            <label class="custom-control-label" for="daily_summary">
                                Daily Summary
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <span class="fe fe-save fe-16 mr-2"></span>
                        Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh notifications every 5 minutes
setInterval(function() {
    // Refresh the page to get latest notifications
    // In a real implementation, this would use AJAX
}, 300000);

// Mark notifications as read when clicked
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('btn-outline-primary')) {
        // Mark notification as read
        console.log('Notification action clicked');
    }
});
</script>

