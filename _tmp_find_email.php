<?php
require_once __DIR__ . '/includes/data/db.php';
$db = getDB();
$stmt = $db->query("SELECT id, username, role, email FROM users WHERE email LIKE '%jerosaculingan%'" );
print_r($stmt->fetchAll());
?>
