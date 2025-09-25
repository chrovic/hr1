<?php
require_once '../data/db.php';
require_once '../functions/simple_auth.php';

// Prevent direct access
if (!isset($_POST['request_id']) || !isset($_POST['reason'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Request ID and reason are required']);
    exit;
}

try {
    $auth = new SimpleAuth();
    $current_user = $auth->getCurrentUser();
    
    // Check if user has permission to reject requests
    if ($current_user['role'] !== 'admin' && $current_user['role'] !== 'hr_manager') {
        echo json_encode(['success' => false, 'message' => 'Insufficient permissions']);
        exit;
    }
    
    $db = getDB();
    $requestId = $_POST['request_id'];
    $reason = $_POST['reason'];
    
    // Update request status to rejected
    $stmt = $db->prepare("UPDATE employee_requests SET status = 'rejected', rejection_reason = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
    $result = $stmt->execute([$reason, $current_user['id'], $requestId]);
    
    if ($result) {
        // Log the activity
        $auth->logActivity('reject_request', 'employee_requests', $requestId, null, ['rejected_by' => $current_user['id'], 'reason' => $reason]);
        
        echo json_encode(['success' => true, 'message' => 'Request rejected successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to reject request']);
    }
    
} catch (PDOException $e) {
    error_log('Database error in ajax_reject_request.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in ajax_reject_request.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>




