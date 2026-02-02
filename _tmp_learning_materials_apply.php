<?php
require_once __DIR__ . '/includes/data/db.php';
$db = getDB();
if (!$db) {
    fwrite(STDERR, "DB connection failed\n");
    exit(1);
}
$mapFile = __DIR__ . '/_learning_materials_mapping.json';
$rows = json_decode(file_get_contents($mapFile), true);
if (!$rows) {
    fwrite(STDERR, "No mapping data\n");
    exit(1);
}

foreach ($rows as $row) {
    $requestId = (int)$row['request_id'];
    $filePath = $row['file_path'];

    $stmt = $db->prepare("SELECT id FROM learning_materials WHERE request_id = ? LIMIT 1");
    $stmt->execute([$requestId]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        $stmt = $db->prepare("UPDATE learning_materials SET material_type = 'file', file_path = ?, link_url = NULL, notes = NULL, updated_at = NOW() WHERE request_id = ?");
        $stmt->execute([$filePath, $requestId]);
    } else {
        $stmt = $db->prepare("INSERT INTO learning_materials (request_id, material_type, file_path) VALUES (?, 'file', ?)");
        $stmt->execute([$requestId, $filePath]);
    }
}
?>
