<?php
// Learning Materials helper functions

class LearningMaterials {
    private $db;

    public function __construct() {
        $this->db = getDB();
        $this->ensureTables();
    }

    private function ensureTables() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS learning_materials (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    request_id INT NOT NULL,
                    material_type ENUM('file','link','text') NOT NULL DEFAULT 'file',
                    file_path VARCHAR(255) DEFAULT NULL,
                    link_url VARCHAR(500) DEFAULT NULL,
                    notes TEXT DEFAULT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE
                )
            ");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_learning_materials_request ON learning_materials(request_id)");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS learning_material_progress (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    request_id INT NOT NULL,
                    employee_id INT NOT NULL,
                    status ENUM('not_started','completed') DEFAULT 'not_started',
                    completed_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
                    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_learning_material_progress_request ON learning_material_progress(request_id)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_learning_material_progress_employee ON learning_material_progress(employee_id)");
            $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_learning_material_progress_unique ON learning_material_progress(request_id, employee_id)");

            $this->db->exec("
                CREATE TABLE IF NOT EXISTS learning_material_certificates (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    request_id INT NOT NULL,
                    employee_id INT NOT NULL,
                    certificate_path VARCHAR(255) NOT NULL,
                    issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (request_id) REFERENCES employee_requests(id) ON DELETE CASCADE,
                    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_learning_material_cert_unique ON learning_material_certificates(request_id, employee_id)");
        } catch (PDOException $e) {
            error_log("LearningMaterials table error: " . $e->getMessage());
        }
    }

    public function upsertMaterial($requestId, $data) {
        try {
            $stmt = $this->db->prepare("SELECT id FROM learning_materials WHERE request_id = ? LIMIT 1");
            $stmt->execute([$requestId]);
            $existing = $stmt->fetchColumn();

            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE learning_materials
                    SET material_type = ?, file_path = ?, link_url = ?, notes = ?, updated_at = NOW()
                    WHERE request_id = ?
                ");
                return $stmt->execute([
                    $data['material_type'],
                    $data['file_path'],
                    $data['link_url'],
                    $data['notes'],
                    $requestId
                ]);
            }

            $stmt = $this->db->prepare("
                INSERT INTO learning_materials (request_id, material_type, file_path, link_url, notes)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $requestId,
                $data['material_type'],
                $data['file_path'],
                $data['link_url'],
                $data['notes']
            ]);
        } catch (PDOException $e) {
            error_log("LearningMaterials upsert error: " . $e->getMessage());
            return false;
        }
    }

    public function getMaterial($requestId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM learning_materials WHERE request_id = ? LIMIT 1");
            $stmt->execute([$requestId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("LearningMaterials fetch error: " . $e->getMessage());
            return null;
        }
    }

    public function markCompleted($requestId, $employeeId) {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("
                DELETE FROM learning_material_progress
                WHERE request_id = ? AND employee_id = ?
            ");
            $stmt->execute([$requestId, $employeeId]);

            $stmt = $this->db->prepare("
                INSERT INTO learning_material_progress (request_id, employee_id, status, completed_at)
                VALUES (?, ?, 'completed', NOW())
            ");
            $ok = $stmt->execute([$requestId, $employeeId]);
            $this->db->commit();
            return $ok;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("LearningMaterials progress error: " . $e->getMessage());
            return false;
        }
    }

    public function getCertificate($requestId, $employeeId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM learning_material_certificates
                WHERE request_id = ? AND employee_id = ?
                LIMIT 1
            ");
            $stmt->execute([$requestId, $employeeId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function saveCertificate($requestId, $employeeId, $path) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO learning_material_certificates (request_id, employee_id, certificate_path)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE certificate_path = VALUES(certificate_path), issued_at = NOW()
            ");
            return $stmt->execute([$requestId, $employeeId, $path]);
        } catch (PDOException $e) {
            error_log("LearningMaterials certificate error: " . $e->getMessage());
            return false;
        }
    }

    public function getProgress($requestId, $employeeId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM learning_material_progress
                WHERE request_id = ? AND employee_id = ?
                LIMIT 1
            ");
            $stmt->execute([$requestId, $employeeId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>
