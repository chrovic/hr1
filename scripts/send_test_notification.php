<?php
require_once __DIR__ . '/../includes/data/db.php';
require_once __DIR__ . '/../includes/functions/simple_auth.php';
require_once __DIR__ . '/../includes/functions/notification_manager.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$current_user = $auth->getCurrentUser();
$manager = new NotificationManager();

$ok = $manager->createNotification(
    $current_user['id'],
    'test_notification',
    'Test Notification',
    'This is a test notification for user #' . $current_user['id'],
    null,
    null,
    '?page=learning_management',
    false
);

echo json_encode(['success' => (bool)$ok], JSON_UNESCAPED_UNICODE) . "\n";
?>


