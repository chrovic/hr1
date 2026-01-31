<?php
require_once '../includes/data/db.php';
require_once '../includes/functions/simple_auth.php';

$auth = new SimpleAuth();
if (!$auth->isLoggedIn() || !$auth->hasPermission('view_reports')) {
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

$db = getDB();

$reportType = $_GET['report'] ?? 'overview';
$dateRange = (int)($_GET['date_range'] ?? 30);
if ($dateRange <= 0) {
    $dateRange = 30;
}
$since = date('Y-m-d H:i:s', time() - ($dateRange * 86400));

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="hr_report_' . $reportType . '.csv"');

$output = fopen('php://output', 'w');

if ($reportType === 'overview') {
    fputcsv($output, ['Metric', 'Value']);
    $totalEmployees = $db->query("SELECT COUNT(*) FROM users WHERE role = 'employee' AND status = 'active'")->fetchColumn();
    $pendingEvaluations = $db->prepare("SELECT COUNT(*) FROM evaluations WHERE status = 'pending' AND created_at >= ?");
    $pendingEvaluations->execute([$since]);
    $pendingEvaluations = $pendingEvaluations->fetchColumn();
    $activeLearning = $db->prepare("SELECT COUNT(*) FROM training_sessions WHERE status IN ('active','scheduled') AND created_at >= ?");
    $activeLearning->execute([$since]);
    $activeLearning = $activeLearning->fetchColumn();
    $pendingRequests = $db->prepare("SELECT COUNT(*) FROM employee_requests WHERE status = 'pending' AND created_at >= ?");
    $pendingRequests->execute([$since]);
    $pendingRequests = $pendingRequests->fetchColumn();

    fputcsv($output, ['Total Employees', $totalEmployees]);
    fputcsv($output, ['Pending Evaluations (last ' . $dateRange . ' days)', $pendingEvaluations]);
    fputcsv($output, ['Active Learning Sessions (last ' . $dateRange . ' days)', $activeLearning]);
    fputcsv($output, ['Pending Requests (last ' . $dateRange . ' days)', $pendingRequests]);
} elseif ($reportType === 'performance') {
    fputcsv($output, ['Metric', 'Value']);
    $stmt = $db->prepare("SELECT AVG(overall_score) FROM evaluations WHERE status = 'completed' AND created_at >= ?");
    $stmt->execute([$since]);
    $avgScore = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COUNT(*) FROM evaluations WHERE status = 'completed' AND created_at >= ?");
    $stmt->execute([$since]);
    $completed = $stmt->fetchColumn();
    $stmt = $db->prepare("SELECT COUNT(*) FROM evaluations WHERE status = 'pending' AND created_at >= ?");
    $stmt->execute([$since]);
    $pending = $stmt->fetchColumn();

    fputcsv($output, ['Average Rating (last ' . $dateRange . ' days)', $avgScore !== null ? number_format((float)$avgScore, 2) : '0']);
    fputcsv($output, ['Completed Evaluations (last ' . $dateRange . ' days)', $completed]);
    fputcsv($output, ['Pending Evaluations (last ' . $dateRange . ' days)', $pending]);
} elseif ($reportType === 'learning') {
    fputcsv($output, ['Status', 'Count']);
    $stmt = $db->prepare("SELECT status, COUNT(*) as total FROM training_enrollments WHERE created_at >= ? GROUP BY status");
    $stmt->execute([$since]);
    $rows = $stmt->fetchAll();
    if (!$rows) {
        fputcsv($output, ['No data', 0]);
    } else {
        foreach ($rows as $row) {
            fputcsv($output, [ucfirst(str_replace('_', ' ', $row['status'])), $row['total']]);
        }
    }
} elseif ($reportType === 'succession') {
    fputcsv($output, ['Role', 'Department', 'Risk Level', 'Total Candidates', 'Ready Now', 'Ready Soon', 'Development Needed']);
    $stmt = $db->prepare("SELECT cr.position_title, cr.department, cr.risk_level,
               COUNT(sc.id) as total_candidates,
               COUNT(CASE WHEN sc.readiness_level = 'ready_now' THEN 1 END) as ready_now,
               COUNT(CASE WHEN sc.readiness_level = 'ready_soon' THEN 1 END) as ready_soon,
               COUNT(CASE WHEN sc.readiness_level = 'development_needed' THEN 1 END) as development_needed
        FROM critical_positions cr
        LEFT JOIN succession_candidates sc ON cr.id = sc.role_id
        GROUP BY cr.id
        ORDER BY cr.risk_level DESC, cr.position_title ASC");
    $stmt->execute();
    foreach ($stmt->fetchAll() as $row) {
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
} elseif ($reportType === 'requests') {
    fputcsv($output, ['Status', 'Count']);
    $stmt = $db->prepare("SELECT status, COUNT(*) as total FROM employee_requests WHERE created_at >= ? GROUP BY status");
    $stmt->execute([$since]);
    $rows = $stmt->fetchAll();
    if (!$rows) {
        fputcsv($output, ['No data', 0]);
    } else {
        foreach ($rows as $row) {
            fputcsv($output, [ucfirst($row['status']), $row['total']]);
        }
    }
} else {
    fputcsv($output, ['Report type not supported']);
}

fclose($output);
exit;
