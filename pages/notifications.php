<?php
require_once 'includes/data/db.php';
require_once 'includes/functions/simple_auth.php';
require_once 'includes/functions/notification_manager.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    header('Location: auth/login.php');
    exit;
}

$current_user = $auth->getCurrentUser();
$notificationManager = new NotificationManager();

$message = '';
$error = '';

// Handle notification actions
if ($_POST) {
    if (isset($_POST['mark_read'])) {
        $notificationId = $_POST['notification_id'];
        if ($notificationManager->markAsRead($notificationId, $current_user['id'])) {
            $message = 'Notification marked as read.';
        } else {
            $error = 'Failed to mark notification as read.';
        }
    }
    
    if (isset($_POST['mark_all_read'])) {
        if ($notificationManager->markAllAsRead($current_user['id'])) {
            $message = 'All notifications marked as read.';
        } else {
            $error = 'Failed to mark all notifications as read.';
        }
    }
    
    if (isset($_POST['delete_notification'])) {
        $notificationId = $_POST['notification_id'];
        if ($notificationManager->deleteNotification($notificationId, $current_user['id'])) {
            $message = 'Notification deleted.';
        } else {
            $error = 'Failed to delete notification.';
        }
    }
    
    if (isset($_POST['update_preferences'])) {
        $preferences = [
            'email_notifications' => isset($_POST['email_notifications']),
            'in_app_notifications' => isset($_POST['in_app_notifications']),
            'sms_notifications' => isset($_POST['sms_notifications']),
            'competency_model_created' => isset($_POST['competency_model_created']),
            'competency_model_updated' => isset($_POST['competency_model_updated']),
            'competency_model_deleted' => isset($_POST['competency_model_deleted']),
            'competency_added' => isset($_POST['competency_added']),
            'competency_updated' => isset($_POST['competency_updated']),
            'competency_deleted' => isset($_POST['competency_deleted']),
            'cycle_created' => isset($_POST['cycle_created']),
            'cycle_updated' => isset($_POST['cycle_updated']),
            'cycle_deleted' => isset($_POST['cycle_deleted']),
            'evaluation_assigned' => isset($_POST['evaluation_assigned']),
            'evaluation_completed' => isset($_POST['evaluation_completed']),
            'evaluation_overdue' => isset($_POST['evaluation_overdue']),
            'score_submitted' => isset($_POST['score_submitted']),
            'report_generated' => isset($_POST['report_generated'])
        ];
        
        if ($notificationManager->updateUserPreferences($current_user['id'], $preferences)) {
            $message = 'Notification preferences updated successfully!';
        } else {
            $error = 'Failed to update notification preferences.';
        }
    }
}

// Get notifications
$notifications = $notificationManager->getUserNotifications($current_user['id'], false, 50);
$unreadCount = $notificationManager->getUnreadCount($current_user['id']);
$preferences = $notificationManager->getUserPreferences($current_user['id']);
?>

<link rel="stylesheet" href="assets/css/competency.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Notifications</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-outline-primary mr-2" data-toggle="modal" data-target="#preferencesModal">
            <i class="fe fe-settings fe-16 mr-2"></i>Preferences
        </button>
        <?php if ($unreadCount > 0): ?>
            <form method="POST" class="d-inline">
                <button type="submit" name="mark_all_read" class="btn btn-primary" onclick="return confirm('Mark all notifications as read?')">
                    <i class="fe fe-check fe-16 mr-2"></i>Mark All Read
                </button>
            </form>
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

<!-- Notifications List -->
<div class="row">
    <div class="col-12">
        <?php if (empty($notifications)): ?>
            <div class="text-center py-5">
                <i class="fe fe-bell-off fe-48 text-muted mb-3"></i>
                <h4 class="text-muted">No notifications</h4>
                <p class="text-muted">You don't have any notifications yet.</p>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body p-0">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                             data-notification-id="<?php echo $notification['id']; ?>">
                            <div class="d-flex p-3">
                                <div class="flex-shrink-0">
                                    <?php
                                    $iconClass = 'fe-bell';
                                    $iconColor = 'text-primary';
                                    
                                    switch ($notification['notification_type']) {
                                        case 'model_created':
                                        case 'model_updated':
                                        case 'model_deleted':
                                        case 'model_archived':
                                            $iconClass = 'fe-layers';
                                            $iconColor = 'text-info';
                                            break;
                                        case 'competency_added':
                                        case 'competency_updated':
                                        case 'competency_deleted':
                                            $iconClass = 'fe-check-square';
                                            $iconColor = 'text-success';
                                            break;
                                        case 'cycle_created':
                                        case 'cycle_updated':
                                        case 'cycle_deleted':
                                            $iconClass = 'fe-calendar';
                                            $iconColor = 'text-warning';
                                            break;
                                        case 'evaluation_assigned':
                                        case 'evaluation_completed':
                                        case 'evaluation_overdue':
                                            $iconClass = 'fe-users';
                                            $iconColor = 'text-primary';
                                            break;
                                        case 'score_submitted':
                                            $iconClass = 'fe-award';
                                            $iconColor = 'text-success';
                                            break;
                                        case 'report_generated':
                                            $iconClass = 'fe-bar-chart';
                                            $iconColor = 'text-info';
                                            break;
                                    }
                                    ?>
                                    <i class="fe <?php echo $iconClass; ?> fe-20 <?php echo $iconColor; ?>"></i>
                                </div>
                                <div class="flex-grow-1 ml-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1 <?php echo $notification['is_read'] ? '' : 'font-weight-bold'; ?>">
                                            <?php echo htmlspecialchars($notification['title']); ?>
                                        </h6>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" type="button" data-toggle="dropdown">
                                                <i class="fe fe-more-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <?php if (!$notification['is_read']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                        <button type="submit" name="mark_read" class="dropdown-item">
                                                            <i class="fe fe-check fe-16 mr-2"></i>Mark as read
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this notification?')">
                                                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                                    <button type="submit" name="delete_notification" class="dropdown-item text-danger">
                                                        <i class="fe fe-trash-2 fe-16 mr-2"></i>Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mb-2 text-muted">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <?php echo date('M j, Y g:i A', strtotime($notification['created_at'])); ?>
                                        </small>
                                        <div>
                                            <?php if ($notification['is_important']): ?>
                                                <span class="badge badge-warning badge-sm mr-1">Important</span>
                                            <?php endif; ?>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge badge-primary badge-sm">New</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if ($notification['action_url']): ?>
                                        <div class="mt-2">
                                            <a href="<?php echo htmlspecialchars($notification['action_url']); ?>" class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($notification !== end($notifications)): ?>
                                <hr class="my-0">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Notification Preferences Modal -->
<div class="modal fade" id="preferencesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Notification Preferences</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">General Settings</h6>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="email_notifications" name="email_notifications" 
                                           <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="email_notifications">Email Notifications</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="in_app_notifications" name="in_app_notifications" 
                                           <?php echo $preferences['in_app_notifications'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="in_app_notifications">In-App Notifications</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="sms_notifications" name="sms_notifications" 
                                           <?php echo $preferences['sms_notifications'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="sms_notifications">SMS Notifications</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Competency Notifications</h6>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="competency_model_created" name="competency_model_created" 
                                           <?php echo $preferences['competency_model_created'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="competency_model_created">Model Created</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="competency_model_updated" name="competency_model_updated" 
                                           <?php echo $preferences['competency_model_updated'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="competency_model_updated">Model Updated</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="competency_model_deleted" name="competency_model_deleted" 
                                           <?php echo $preferences['competency_model_deleted'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="competency_model_deleted">Model Deleted</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="competency_added" name="competency_added" 
                                           <?php echo $preferences['competency_added'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="competency_added">Competency Added</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="competency_updated" name="competency_updated" 
                                           <?php echo $preferences['competency_updated'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="competency_updated">Competency Updated</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="competency_deleted" name="competency_deleted" 
                                           <?php echo $preferences['competency_deleted'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="competency_deleted">Competency Deleted</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">Evaluation Notifications</h6>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="cycle_created" name="cycle_created" 
                                           <?php echo $preferences['cycle_created'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="cycle_created">Cycle Created</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="cycle_updated" name="cycle_updated" 
                                           <?php echo $preferences['cycle_updated'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="cycle_updated">Cycle Updated</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="cycle_deleted" name="cycle_deleted" 
                                           <?php echo $preferences['cycle_deleted'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="cycle_deleted">Cycle Deleted</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Assignment Notifications</h6>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="evaluation_assigned" name="evaluation_assigned" 
                                           <?php echo $preferences['evaluation_assigned'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="evaluation_assigned">Evaluation Assigned</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="evaluation_completed" name="evaluation_completed" 
                                           <?php echo $preferences['evaluation_completed'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="evaluation_completed">Evaluation Completed</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="evaluation_overdue" name="evaluation_overdue" 
                                           <?php echo $preferences['evaluation_overdue'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="evaluation_overdue">Evaluation Overdue</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="score_submitted" name="score_submitted" 
                                           <?php echo $preferences['score_submitted'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="score_submitted">Score Submitted</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="report_generated" name="report_generated" 
                                           <?php echo $preferences['report_generated'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="report_generated">Report Generated</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_preferences" class="btn btn-primary">Save Preferences</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 3px solid #2196f3;
}

.notification-item {
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}
</style>


