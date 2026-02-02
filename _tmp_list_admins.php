<?php
require_once __DIR__ . '/includes/data/db.php';
$db = getDB();
$stmt = $db->query("SELECT id, username, email, role FROM users WHERE role='admin'");
print_r($stmt->fetchAll());
?>
