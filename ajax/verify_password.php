<?php
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$password = $_POST['password'] ?? '';
if ($password === '') {
    echo json_encode(['success' => false, 'message' => 'Password is required']);
    exit;
}

$currentUser = $auth->getCurrentUser();
$userId = $currentUser['id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare('SELECT password_hash FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    $hash = $stmt->fetchColumn();

    if ($hash && password_verify($password, $hash)) {
        $_SESSION['export_ok'] = time();
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Incorrect password']);
    exit;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Verification failed']);
    exit;
}
