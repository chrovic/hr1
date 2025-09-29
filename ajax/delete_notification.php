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
$db = getDB();

$input = json_decode(file_get_contents('php://input'), true);
$notificationId = (int)($input['notification_id'] ?? 0);

if ($notificationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit;
}

try {
    // Ensure the notification belongs to the current user
    $stmt = $db->prepare("DELETE FROM competency_notifications WHERE id = ? AND user_id = ?");
    $ok = $stmt->execute([$notificationId, $current_user['id']]);
    
    if ($ok && $stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Not found or not allowed']);
    }
} catch (PDOException $e) {
    error_log('delete_notification error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>


