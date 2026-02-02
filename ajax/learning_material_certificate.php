<?php
session_start();
require_once __DIR__ . '/../includes/data/db.php';
require_once __DIR__ . '/../includes/functions/simple_auth.php';
require_once __DIR__ . '/../includes/functions/learning_materials.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo 'Unauthorized';
    exit;
}

$requestId = (int)($_GET['request_id'] ?? 0);
if ($requestId <= 0) {
    http_response_code(400);
    echo 'Invalid request.';
    exit;
}

$user = $auth->getCurrentUser();
$db = getDB();

$stmt = $db->prepare("SELECT * FROM employee_requests WHERE id = ? LIMIT 1");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request || $request['status'] !== 'approved') {
    http_response_code(404);
    echo 'Certificate not available.';
    exit;
}

$isOwner = (int)$request['employee_id'] === (int)$user['id'];
$isManager = in_array($user['role'], ['admin', 'hr_manager'], true);

if (!$isOwner && !$isManager) {
    http_response_code(403);
    echo 'Forbidden.';
    exit;
}

$materials = new LearningMaterials();
$cert = $materials->getCertificate($requestId, $user['id']);

if (!$cert || empty($cert['certificate_path'])) {
    http_response_code(404);
    echo 'Certificate not found.';
    exit;
}

$baseDir = realpath(__DIR__ . '/../');
$filePath = realpath(__DIR__ . '/../' . $cert['certificate_path']);

if (!$filePath || strpos($filePath, $baseDir) !== 0 || !file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

$filename = basename($filePath);
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;
?>
