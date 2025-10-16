<?php
require_once __DIR__ . '/../includes/data/db.php';
require_once __DIR__ . '/../includes/functions/simple_auth.php';
require_once __DIR__ . '/../includes/functions/recommendations.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : ($_SESSION['user_id'] ?? 0);
if ($employeeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid employee id']);
    exit;
}

try {
    $service = new RecommendationService();
    $recs = $service->recommendForEmployee($employeeId, 10);
    echo json_encode(['success' => true, 'recommendations' => $recs]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error generating recommendations']);
}
?>


