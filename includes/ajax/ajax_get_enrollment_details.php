<?php
require_once '../../includes/data/db.php';
require_once '../../includes/functions/simple_auth.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_evaluations')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$enrollment_id = $_POST['enrollment_id'] ?? null;

if (!$enrollment_id) {
    echo json_encode(['success' => false, 'message' => 'Enrollment ID is required']);
    exit;
}

try {
    $db = getDB();
    
    $stmt = $db->prepare("
        SELECT te.*, ts.session_name, ts.start_date, ts.end_date, ts.location,
               tm.title as training_title, tm.description as training_description,
               u.first_name as employee_first_name, u.last_name as employee_last_name, 
               u.email as employee_email, u.department as employee_department, u.position as employee_position
        FROM training_enrollments te
        JOIN training_sessions ts ON te.session_id = ts.id
        JOIN training_modules tm ON ts.module_id = tm.id
        JOIN users u ON te.employee_id = u.id
        WHERE te.id = ?
    ");
    
    $stmt->execute([$enrollment_id]);
    $enrollment = $stmt->fetch();
    
    if (!$enrollment) {
        echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'enrollment' => $enrollment
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching enrollment details: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>