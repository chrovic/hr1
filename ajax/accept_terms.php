<?php
session_start();
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';
require_once '../includes/functions/terms_acceptance.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$current_user = $auth->getCurrentUser();
$conn = getDB();
$termsAcceptance = new TermsAcceptance($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'accept_terms') {
        $userId = $current_user['id'] ?? null;
        
        if (!$userId) {
            echo json_encode([
                'success' => false, 
                'message' => 'User ID not found'
            ]);
            exit;
        }
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        try {
            if ($termsAcceptance->acceptTerms($userId, $ipAddress, $userAgent)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Terms and conditions accepted successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Failed to accept terms and conditions'
                ]);
            }
        } catch (Exception $e) {
            error_log("Terms acceptance error: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Database error occurred'
            ]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
