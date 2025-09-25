<?php
require_once '../data/db.php';

// Prevent direct access
if (!isset($_POST['request_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

try {
    $db = getDB();
    $requestId = $_POST['request_id'];
    
    // Get training request details with related information
    $stmt = $db->prepare("
        SELECT tr.*, u.first_name, u.last_name, u.email, u.department,
               tc.title as training_title, tc.description as training_description,
               ts.start_date, ts.end_date, ts.location, ts.session_name,
               approver.first_name as approver_first_name, approver.last_name as approver_last_name
        FROM training_requests tr
        LEFT JOIN users u ON tr.employee_id = u.id
        LEFT JOIN training_catalog tc ON tr.module_id = tc.id
        LEFT JOIN training_sessions ts ON tr.module_id = ts.module_id
        LEFT JOIN users approver ON tr.approved_by = approver.id
        WHERE tr.id = ?
    ");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Training request not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'request' => $request
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in ajax_get_request_details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in ajax_get_request_details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>




