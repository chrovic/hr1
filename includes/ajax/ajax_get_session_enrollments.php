<?php
require_once '../data/db.php';

// Prevent direct access
if (!isset($_POST['session_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit;
}

try {
    $db = getDB();
    $sessionId = $_POST['session_id'];
    
    // Get enrollments for the session
    $stmt = $db->prepare("
        SELECT te.*, u.first_name as employee_first_name, u.last_name as employee_last_name, 
               u.email as employee_email, u.department as employee_department
        FROM training_enrollments te
        LEFT JOIN users u ON te.employee_id = u.id
        WHERE te.session_id = ?
        ORDER BY te.enrollment_date DESC
    ");
    $stmt->execute([$sessionId]);
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'enrollments' => $enrollments
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in ajax_get_session_enrollments.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in ajax_get_session_enrollments.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>




