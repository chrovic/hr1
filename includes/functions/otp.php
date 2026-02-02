<?php
require_once __DIR__ . '/../data/db.php';
require_once __DIR__ . '/../../config/mailer.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class OTPManager {
    private $db;
    private $lastError = '';

    public function __construct() {
        $this->db = getDB();
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS login_otp (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    otp_hash VARCHAR(255) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    attempts INT DEFAULT 0,
                    resend_count INT DEFAULT 0,
                    last_sent_at DATETIME NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                )
            ");
            // Create index if not present (MySQL < 8 doesn't support IF NOT EXISTS for indexes)
            $this->safeCreateIndex('idx_login_otp_user', 'login_otp', 'user_id');
            // Best-effort column additions for existing tables
            $this->safeAlter("ALTER TABLE login_otp ADD COLUMN resend_count INT DEFAULT 0");
            $this->safeAlter("ALTER TABLE login_otp ADD COLUMN last_sent_at DATETIME NULL");
        } catch (PDOException $e) {
            error_log("OTP table error: " . $e->getMessage());
        }
    }

    private function safeAlter($sql) {
        try {
            $this->db->exec($sql);
        } catch (PDOException $e) {
            // Ignore if column already exists
        }
    }

    private function safeCreateIndex($indexName, $tableName, $columns) {
        try {
            $stmt = $this->db->prepare("SHOW INDEX FROM {$tableName} WHERE Key_name = ?");
            $stmt->execute([$indexName]);
            $exists = $stmt->fetch();
            if (!$exists) {
                $this->db->exec("CREATE INDEX {$indexName} ON {$tableName}({$columns})");
            }
        } catch (PDOException $e) {
            // Ignore if index already exists or cannot be created
        }
    }

    public function generateOtp($userId, $email) {
        $this->lastError = '';
        $existing = $this->getOtpRow($userId);
        if ($existing) {
            $cooldownSeconds = 0;
            $windowSeconds = 600;
            $now = time();
            $lastSent = $existing['last_sent_at'] ? strtotime($existing['last_sent_at']) : 0;
            $resendCount = (int)($existing['resend_count'] ?? 0);

            if ($lastSent && ($now - $lastSent) < $cooldownSeconds) {
                return ['success' => false, 'message' => 'Please wait before requesting another OTP.'];
            }
            if ($lastSent && ($now - $lastSent) > $windowSeconds) {
                $resendCount = 0;
            }
            if ($resendCount >= 3) {
                return ['success' => false, 'message' => 'Resend limit reached. Please try again later.'];
            }
        }

        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hash = password_hash($code, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', time() + 300); // 5 minutes

        try {
            if ($existing) {
                $stmt = $this->db->prepare("
                    UPDATE login_otp
                    SET otp_hash = ?, expires_at = ?, attempts = 0,
                        resend_count = resend_count + 1, last_sent_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt->execute([$hash, $expiresAt, $userId]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO login_otp (user_id, otp_hash, expires_at, attempts, resend_count, last_sent_at)
                    VALUES (?, ?, ?, 0, 1, NOW())
                ");
                $stmt->execute([$userId, $hash, $expiresAt]);
            }
        } catch (PDOException $e) {
            error_log("OTP insert error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Unable to generate OTP. Please try again.'];
        }

        $sent = $this->sendOtpEmail($email, $code);
        return $sent ? ['success' => true] : ['success' => false, 'message' => $this->lastError ?: 'Unable to send OTP email.'];
    }

    public function verifyOtp($userId, $code) {
        try {
            $row = $this->getOtpRow($userId);

            if (!$row) {
                return ['success' => false, 'message' => 'OTP not found. Please request a new code.'];
            }

            if ((int)$row['attempts'] >= 5) {
                return ['success' => false, 'message' => 'OTP attempts exceeded. Please request a new code.'];
            }

            if (strtotime($row['expires_at']) < time()) {
                return ['success' => false, 'message' => 'OTP expired. Please request a new code.'];
            }

            $ok = password_verify($code, $row['otp_hash']);
            if (!$ok) {
                $stmt = $this->db->prepare("UPDATE login_otp SET attempts = attempts + 1 WHERE id = ?");
                $stmt->execute([$row['id']]);
                return ['success' => false, 'message' => 'Invalid OTP. Please try again.'];
            }

            $stmt = $this->db->prepare("DELETE FROM login_otp WHERE id = ?");
            $stmt->execute([$row['id']]);

            return ['success' => true];
        } catch (PDOException $e) {
            error_log("OTP verify error: " . $e->getMessage());
            return ['success' => false, 'message' => 'OTP verification error.'];
        }
    }

    private function getOtpRow($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM login_otp WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    private function sendOtpEmail($toEmail, $code) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION;
            $mail->Port = MAIL_PORT;

            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Your HR2 Login OTP Code';
            $mail->Body = "<p>Your OTP code is:</p><h2>{$code}</h2><p>This code will expire in 5 minutes.</p>";
            $mail->AltBody = "Your OTP code is: {$code}. It expires in 5 minutes.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            $this->lastError = 'Email error: ' . ($mail->ErrorInfo ?: $e->getMessage());
            error_log("OTP email error: " . $this->lastError);
            return false;
        }
    }
}
?>
