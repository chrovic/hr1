<?php
// Notification Dropdown Widget
try {
    require_once 'includes/functions/notification_manager.php';
    $notificationManager = new NotificationManager();
    $notifications = $notificationManager->getUserNotifications($current_user['id'], false, 10);
    $unreadCount = $notificationManager->getUnreadCount($current_user['id']);
    ?>
    <!-- Notification Dropdown -->
    <div class="dropdown">
        <button class="btn btn-link text-decoration-none" type="button" id="notificationDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="position: relative;">
            <i class="fe fe-bell fe-20"></i>
            <?php if ($unreadCount > 0): ?>
                <span class="badge badge-danger badge-pill position-absolute" style="top: -5px; right: -5px; font-size: 10px;">
                    <?php echo $unreadCount; ?>
                </span>
            <?php endif; ?>
        </button>
        
        <div class="dropdown-menu dropdown-menu-right notification-dropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
            <div class="dropdown-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Notifications</h6>
                <?php if ($unreadCount > 0): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="markAllAsRead()">
                        Mark all read
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="dropdown-divider"></div>
            
            <?php if (empty($notifications)): ?>
                <div class="text-center py-3 text-muted">
                    <i class="fe fe-bell-off fe-24 mb-2"></i>
                    <p class="mb-0">No notifications</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item <?php echo $notification['is_read'] ? '' : 'unread'; ?>" 
                         data-notification-id="<?php echo $notification['id']; ?>"
                         onclick="handleNotificationClick(<?php echo $notification['id']; ?>, '<?php echo htmlspecialchars($notification['action_url'] ?? '', ENT_QUOTES); ?>')">
                        <div class="d-flex">
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
                                <i class="fe <?php echo $iconClass; ?> fe-16 <?php echo $iconColor; ?>"></i>
                            </div>
                            <div class="flex-grow-1 ml-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h6 class="mb-1 <?php echo $notification['is_read'] ? '' : 'font-weight-bold'; ?>">
                                        <?php echo htmlspecialchars($notification['title']); ?>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo date('M j, g:i A', strtotime($notification['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-1 text-muted small">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </p>
                                <?php if ($notification['is_important']): ?>
                                    <span class="badge badge-warning badge-sm">Important</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!$notification['is_read']): ?>
                                <div class="flex-shrink-0">
                                    <div class="unread-indicator"></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <div class="dropdown-footer text-center">
                <a href="?page=notifications" class="btn btn-sm btn-outline-primary">
                    View all notifications
                </a>
            </div>
        </div>
    </div>
    
    <style>
    .notification-dropdown {
        border: 1px solid #dee2e6;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .notification-item {
        padding: 12px 16px;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .notification-item:hover {
        background-color: #f8f9fa;
    }
    
    .notification-item.unread {
        background-color: #e3f2fd;
        border-left: 3px solid #2196f3;
    }
    
    .unread-indicator {
        width: 8px;
        height: 8px;
        background-color: #2196f3;
        border-radius: 50%;
        margin-top: 8px;
    }
    
    .notification-item h6 {
        font-size: 14px;
        line-height: 1.3;
    }
    
    .notification-item p {
        font-size: 12px;
        line-height: 1.4;
    }

    body.dark .notification-dropdown,
    .dark .notification-dropdown {
        background: #1e2428;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.45);
    }

    body.dark .notification-dropdown .dropdown-header,
    .dark .notification-dropdown .dropdown-header {
        color: #e9ecef;
    }

    body.dark .notification-dropdown .dropdown-divider,
    .dark .notification-dropdown .dropdown-divider {
        border-top-color: rgba(255, 255, 255, 0.08);
    }

    body.dark .notification-item,
    .dark .notification-item {
        color: #e9ecef;
    }

    body.dark .notification-item:hover,
    .dark .notification-item:hover {
        background-color: rgba(255, 255, 255, 0.06);
    }

    body.dark .notification-item.unread,
    .dark .notification-item.unread {
        background-color: rgba(33, 150, 243, 0.18);
        border-left-color: #2196f3;
    }

    body.dark .notification-item h6,
    .dark .notification-item h6 {
        color: #f1f3f5;
    }

    body.dark .notification-item .text-muted,
    .dark .notification-item .text-muted {
        color: rgba(255, 255, 255, 0.65) !important;
    }
    </style>
    
    <script>
    function handleNotificationClick(notificationId, actionUrl) {
        // Mark as read
        markAsRead(notificationId);
        
        // Navigate to action URL if provided
        if (actionUrl) {
            window.location.href = actionUrl;
        }
    }
    
    function markAsRead(notificationId) {
        fetch('ajax/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    const unreadIndicator = notificationItem.querySelector('.unread-indicator');
                    if (unreadIndicator) {
                        unreadIndicator.remove();
                    }
                }
                
                // Update unread count
                updateUnreadCount();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }
    
    function markAllAsRead() {
        fetch('ajax/mark_all_notifications_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show updated notifications
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
    }
    
    function updateUnreadCount() {
        fetch('ajax/get_unread_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.badge-danger');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    // Create badge if it doesn't exist
                    const button = document.querySelector('#notificationDropdown');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge badge-danger badge-pill position-absolute';
                    newBadge.style.cssText = 'top: -5px; right: -5px; font-size: 10px;';
                    newBadge.textContent = data.count;
                    button.appendChild(newBadge);
                }
            } else {
                if (badge) {
                    badge.remove();
                }
            }
        })
        .catch(error => {
            console.error('Error updating unread count:', error);
        });
    }
    
    // Auto-refresh notifications every 30 seconds
    setInterval(function() {
        // Only refresh if dropdown is not open
        const dropdown = document.querySelector('.notification-dropdown');
        if (!dropdown.classList.contains('show')) {
            updateUnreadCount();
        }
    }, 30000);
    </script>
    <?php
} catch (Exception $e) {
    // Fallback notification bell if widget fails
    echo '<a class="nav-link text-muted my-2" href="?page=notifications">
            <span class="fe fe-bell fe-16"></span>
          </a>';
    error_log("Notification widget error: " . $e->getMessage());
}
?>
