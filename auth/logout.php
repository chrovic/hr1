<?php
session_start();
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';

$auth = new SimpleAuth();
$auth->logout();

header('Location: ../auth/login.php?message=logged_out');
exit;
?>
