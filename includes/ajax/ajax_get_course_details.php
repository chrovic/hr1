<?php
require_once '../data/db.php';

// Prevent direct access
if (!isset($_POST['course_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Course ID is required']);
    exit;
}

try {
    $db = getDB();
    $courseId = $_POST['course_id'];
    
    // Get course details
    $stmt = $db->prepare("
        SELECT tc.*, u.first_name, u.last_name
        FROM training_catalog tc
        LEFT JOIN users u ON tc.created_by = u.id
        WHERE tc.id = ?
    ");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$course) {
        echo json_encode(['success' => false, 'message' => 'Course not found']);
        exit;
    }
    
    // Add creator name
    $course['created_by_name'] = ($course['first_name'] ?? 'Unknown') . ' ' . ($course['last_name'] ?? 'User');
    
    echo json_encode([
        'success' => true,
        'course' => $course
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in ajax_get_course_details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in ajax_get_course_details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>









