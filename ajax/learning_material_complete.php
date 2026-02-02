<?php
session_start();
require_once __DIR__ . '/../includes/data/db.php';
require_once __DIR__ . '/../includes/functions/simple_auth.php';
require_once __DIR__ . '/../includes/functions/learning_materials.php';

header('Content-Type: application/json');

$auth = new SimpleAuth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$requestId = (int)($_POST['request_id'] ?? 0);
if ($requestId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user = $auth->getCurrentUser();
$db = getDB();

$stmt = $db->prepare("SELECT * FROM employee_requests WHERE id = ? LIMIT 1");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request || $request['status'] !== 'approved') {
    echo json_encode(['success' => false, 'message' => 'Material not available']);
    exit;
}

if ((int)$request['employee_id'] !== (int)$user['id']) {
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$materials = new LearningMaterials();
if (!$materials->markCompleted($requestId, $user['id'])) {
    echo json_encode(['success' => false, 'message' => 'Unable to update status']);
    exit;
}

// Generate certificate
$cert = $materials->getCertificate($requestId, $user['id']);
if (!$cert) {
    $certDir = __DIR__ . '/../uploads/certificates/learning_materials';
    if (!is_dir($certDir)) {
        mkdir($certDir, 0777, true);
    }

    $title = $request['title'] ?? 'Learning Material';
    $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $issuedDate = date('F d, Y');

    $filename = 'certificate_' . $requestId . '_' . $user['id'] . '.pdf';
    $filePath = $certDir . '/' . $filename;

    // Basic PDF generation
    $lines = [
        'Certificate of Completion',
        '',
        'This certifies that',
        $fullName !== '' ? $fullName : 'Employee',
        'has successfully completed the learning material:',
        $title,
        '',
        'Date: ' . $issuedDate
    ];

    $y = 720;
    $content = "BT\n/F1 18 Tf\n";
    foreach ($lines as $line) {
        $safe = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
        $content .= "72 {$y} Td ({$safe}) Tj\n";
        $y -= 26;
    }
    $content .= "ET\n";
    $contentBytes = $content;

    $objects = [];
    $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
    $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
    $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";
    $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";
    $objects[] = "5 0 obj\n<< /Length " . strlen($contentBytes) . " >>\nstream\n" . $contentBytes . "endstream\nendobj\n";

    $pdf = "%PDF-1.4\n";
    $xref = [];
    foreach ($objects as $obj) {
        $xref[] = strlen($pdf);
        $pdf .= $obj;
    }
    $xrefStart = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach ($xref as $pos) {
        $pdf .= sprintf("%010d 00000 n \n", $pos);
    }
    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefStart}\n%%EOF\n";

    file_put_contents($filePath, $pdf);
    $materials->saveCertificate($requestId, $user['id'], 'uploads/certificates/learning_materials/' . $filename);
    $cert = $materials->getCertificate($requestId, $user['id']);
}

$auth->logActivity('learning_material_completed', 'employee_requests', $requestId, null, null);

echo json_encode([
    'success' => true,
    'certificate' => $cert ? $cert['certificate_path'] : null
]);
?>
