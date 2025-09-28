<?php
require_once __DIR__ . '/../data/db.php';

// Prevent direct access
if (!isset($_POST['session_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Session ID is required']);
    exit;
}

try {
    $db = getDB();
    $sessionId = $_POST['session_id'];
    
    // Get session details with related information
    $stmt = $db->prepare("
        SELECT ts.*, tm.title as training_title, tm.description as training_description,
               trainer.first_name as trainer_first_name, trainer.last_name as trainer_last_name,
               creator.first_name as creator_first_name, creator.last_name as creator_last_name,
               COUNT(te.id) as enrollment_count
        FROM training_sessions ts
        LEFT JOIN training_modules tm ON ts.module_id = tm.id
        LEFT JOIN users trainer ON ts.trainer_id = trainer.id
        LEFT JOIN users creator ON ts.created_by = creator.id
        LEFT JOIN training_enrollments te ON ts.id = te.session_id
        WHERE ts.id = ?
        GROUP BY ts.id
    ");
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$session) {
        echo json_encode(['success' => false, 'message' => 'Session not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'session' => $session
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in ajax_get_session_details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log('Error in ajax_get_session_details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>









