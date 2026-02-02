<?php
require_once __DIR__ . '/includes/data/db.php';
require_once __DIR__ . '/includes/functions/otp.php';
$db = getDB();
if (!$db) { echo "DB fail\n"; exit(1);} 
$stmt = $db->query("SELECT id, email FROM users WHERE role='admin' ORDER BY id ASC LIMIT 1");
$user = $stmt->fetch();
if (!$user) { echo "No admin\n"; exit(1);} 
$otp = new OTPManager();
$result = $otp->generateOtp($user['id'], $user['email']);
var_dump($result);
?>
