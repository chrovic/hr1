<?php
require_once '../data/db.php';
require_once '../functions/simple_auth.php';

// Prevent direct access
if (!isset($_POST['request_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Request ID is required']);
    exit;
}

try {
    $auth = new SimpleAuth();
    $current_user = $auth->getCurrentUser();
    
    // Check if user has permission to approve requests
    if ($current_user['role'] !== 'admin' && $current_user['role'] !== 'hr_manager') {
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }
    
    $db = getDB();
    $requestId = $_POST['request_id'];
    
    // Update request status to approved
    $stmt = $db->prepare("UPDATE employee_requests SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$current_user['id'], $requestId]);
    
    if ($result) {
        // Log the activity
        $auth->logActivity('approve_request', 'employee_requests', $requestId, null, ['approved_by' => $current_user['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Request approved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to approve request']);
    }
    
} catch (PDOException $e) {
    error_log('Database error in ajax_approve_request.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in ajax_approve_request.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>









