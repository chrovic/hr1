<?php
session_start();
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';
require_once '../includes/functions/notification_manager.php';

header('Content-Type: application/json');

try {
    $auth = new SimpleAuth();
    if (!$auth->isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    $current_user = $auth->getCurrentUser();
    $notificationManager = new NotificationManager();
    
    // Get current unread count
    $unreadCount = $notificationManager->getUnreadCount($current_user['id']);
    
    // Get recent notifications (last 10)
    $notifications = $notificationManager->getUserNotifications($current_user['id'], false, 10);
    
    // Get timestamp of last check (from session or default to 1 hour ago)
    $lastCheck = $_SESSION['last_notification_check'] ?? date('Y-m-d H:i:s', strtotime('-1 hour'));
    
    // Check for new notifications since last check
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COUNT(*) as new_count 
        FROM competency_notifications 
        WHERE user_id = ? AND created_at > ?
    ");
    $stmt->execute([$current_user['id'], $lastCheck]);
    $newNotifications = $stmt->fetch()['new_count'];
    
    // Update last check time
    $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');
    
    echo json_encode([
        'success' => true,
        'unreadCount' => $unreadCount,
        'newNotifications' => $newNotifications,
        'notifications' => $notifications,
        'lastCheck' => $lastCheck
    ]);
    
} catch (Exception $e) {
    error_log("Notification check error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error checking notifications']);
}
?>


