<?php
require_once __DIR__ . '/../data/db.php';

// Prevent direct access
if (!isset($_POST['enrollment_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Enrollment ID is required']);
    exit;
}

try {
    $db = getDB();
    $enrollmentId = $_POST['enrollment_id'];
    
    // Remove the enrollment
    $stmt = $db->prepare("DELETE FROM training_enrollments WHERE id = ?");
    $result = $stmt->execute([$enrollmentId]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Enrollment removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove enrollment']);
    }
    
} catch (PDOException $e) {
    error_log('Database error in ajax_remove_enrollment.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in ajax_remove_enrollment.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>









