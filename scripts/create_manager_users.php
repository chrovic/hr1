<?php
// Utility to create manager users with password "password"
require_once __DIR__ . '/../includes/data/db.php';

$db = getDB();

$users = [
    [
        'username' => 'competency_manager',
        'email' => 'competency_manager@example.com',
        'role' => 'competency_manager',
        'first_name' => 'Competency',
        'last_name' => 'Manager',
    ],
    [
        'username' => 'learning_training_manager',
        'email' => 'learning_training_manager@example.com',
        'role' => 'learning_training_manager',
        'first_name' => 'Learning',
        'last_name' => 'Manager',
    ],
    [
        'username' => 'succession_manager',
        'email' => 'succession_manager@example.com',
        'role' => 'succession_manager',
        'first_name' => 'Succession',
        'last_name' => 'Manager',
    ],
];

$created = 0;
foreach ($users as $u) {
    $passwordHash = password_hash('password', PASSWORD_DEFAULT);
    // Upsert by username
    $sql = "INSERT INTO users (username, email, password_hash, role, first_name, last_name, status, hire_date, created_at)
            VALUES (:username, :email, :password_hash, :role, :first_name, :last_name, 'active', CURDATE(), NOW())
            ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role), status = 'active'";
    $stmt = $db->prepare($sql);
    if ($stmt->execute([
        ':username' => $u['username'],
        ':email' => $u['email'],
        ':password_hash' => $passwordHash,
        ':role' => $u['role'],
        ':first_name' => $u['first_name'],
        ':last_name' => $u['last_name'],
    ])) {
        $created++;
    }
}

echo json_encode(['success' => true, 'processed' => $created], JSON_UNESCAPED_UNICODE) . "\n";
?>


