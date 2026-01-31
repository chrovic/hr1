<?php
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';
require_once '../includes/functions/succession_planning.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('manage_succession')) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

$tokenTime = $_SESSION['export_ok'] ?? 0;
if (!$tokenTime || (time() - $tokenTime) > 120) {
    http_response_code(403);
    echo 'Password verification required.';
    exit;
}
unset($_SESSION['export_ok']);

$department = $_GET['department'] ?? '';
$riskLevel = $_GET['risk_level'] ?? '';

$filters = [];
if ($department !== '') {
    $filters['department'] = $department;
}
if ($riskLevel !== '') {
    $filters['risk_level'] = $riskLevel;
}

$successionManager = new SuccessionPlanning();
$reportData = $successionManager->generateSuccessionReport($filters);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="succession_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Role', 'Department', 'Risk Level', 'Total Candidates', 'Ready Now', 'Ready Soon', 'Development Needed']);
foreach ($reportData as $row) {
    fputcsv($output, [
        $row['position_title'] ?? '',
        $row['department'] ?? '',
        $row['risk_level'] ?? '',
        $row['total_candidates'] ?? 0,
        $row['ready_now'] ?? 0,
        $row['ready_soon'] ?? 0,
        $row['development_needed'] ?? 0
    ]);
}

fclose($output);
exit;
