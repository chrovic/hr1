<?php
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';
require_once '../includes/functions/notification_manager.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$current_user = $auth->getCurrentUser();
$notificationManager = new NotificationManager();

// Get unread count
$unreadCount = $notificationManager->getUnreadCount($current_user['id']);

echo json_encode(['success' => true, 'count' => $unreadCount]);
?>


