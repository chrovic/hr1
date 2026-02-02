<?php
require_once __DIR__ . '/includes/data/db.php';
$db = getDB();
$stmt = $db->prepare("UPDATE users SET email = ? WHERE username = ? AND role='admin'");
$stmt->execute(['jerosaculingan@gmail.com','admin']);
$cnt = $stmt->rowCount();
echo "Updated {$cnt} admin user(s).\n";
?>
