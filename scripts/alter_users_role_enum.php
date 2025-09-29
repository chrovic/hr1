<?php
require_once __DIR__ . '/../includes/data/db.php';

$db = getDB();

$enumValues = [
    'admin',
    'hr_manager',
    'employee',
    'competency_manager',
    'learning_training_manager',
    'succession_manager'
];

$enumList = "'" . implode("','", $enumValues) . "'";

$sql = "ALTER TABLE users MODIFY role ENUM($enumList) NOT NULL";

try {
    $db->exec($sql);
    echo json_encode(['success' => true, 'message' => 'users.role enum updated'], JSON_UNESCAPED_UNICODE) . "\n";
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE) . "\n";
    exit(1);
}
?>


