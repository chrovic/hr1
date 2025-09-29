<?php
require_once __DIR__ . '/../includes/data/db.php';
require_once __DIR__ . '/../includes/functions/simple_auth.php';

// Ensure session for SimpleAuth usage
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$auth = new SimpleAuth();
$users = [
    'competency_manager',
    'learning_training_manager',
    'succession_manager',
];

$results = [];
foreach ($users as $u) {
    // Logout any existing session between attempts
    if ($auth->isLoggedIn()) {
        $auth->logout();
    }
    $ok = $auth->login($u, 'password');
    $results[$u] = $ok === true ? 'OK' : 'FAIL';
}

echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_UNICODE) . "\n";
?>


