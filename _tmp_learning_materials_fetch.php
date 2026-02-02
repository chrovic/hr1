<?php
require_once __DIR__ . '/includes/data/db.php';
$db = getDB();
if (!$db) {
    fwrite(STDERR, "DB connection failed\n");
    exit(1);
}
$db->exec("CREATE TABLE IF NOT EXISTS learning_materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    material_type ENUM('file','link','text') NOT NULL DEFAULT 'file',
    file_path VARCHAR(255) DEFAULT NULL,
    link_url VARCHAR(500) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE
)");

$stmt = $db->prepare("SELECT er.id, er.title, er.description
    FROM employee_requests er
    LEFT JOIN learning_materials lm ON er.id = lm.request_id
    WHERE er.request_type = 'other' AND er.status = 'approved' AND lm.id IS NULL
    ORDER BY er.approved_at DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
file_put_contents(__DIR__ . '/_learning_materials_missing.json', json_encode($rows, JSON_PRETTY_PRINT));
?>
