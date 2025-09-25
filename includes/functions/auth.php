<?php
// Authentication and User Management System
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // User login with role-based authentication
    public function login($username, $password, $remember_me = false) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND status = 'active'");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            
            // Create session
            $session_token = $this->createSession($user['id'], $remember_me);
            
            // Store user data in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['session_token'] = $session_token;
            
            return [
                'success' => true,
                'user' => $user,
                'requires_2fa' => $user['two_factor_enabled']
            ];
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    // Two-factor authentication
    public function verify2FA($user_id, $code) {
        $stmt = $this->db->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user && $this->verifyTOTP($user['two_factor_secret'], $code)) {
            return true;
        }
        
        return false;
    }
    
    // Create user session
    private function createSession($user_id, $remember_me = false) {
        $session_token = bin2hex(random_bytes(32));
        $expires_at = $remember_me ? date('Y-m-d H:i:s', strtotime('+30 days')) : date('Y-m-d H:i:s', strtotime('+8 hours'));
        
        $stmt = $this->db->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $session_token,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $expires_at
        ]);
        
        return $session_token;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_token'])) {
            return false;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM user_sessions WHERE user_id = ? AND session_token = ? AND expires_at > NOW()");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
        
        return $stmt->fetch() !== false;
    }
    
    // Get current user
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        return $stmt->fetch();
    }
    
    // Check user permissions
    public function hasPermission($permission) {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        $permissions = [
            'admin' => ['manage_users', 'manage_system', 'view_all_data', 'manage_evaluations', 'manage_trainings', 'manage_succession'],
            'hr_manager' => ['manage_evaluations', 'manage_trainings', 'manage_succession', 'view_hr_data'],
            'employee' => ['view_own_data', 'request_training', 'submit_evaluations']
        ];
        
        return in_array($permission, $permissions[$user['role']] ?? []);
    }
    
    // Logout user
    public function logout() {
        if (isset($_SESSION['session_token'])) {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
            $stmt->execute([$_SESSION['session_token']]);
        }
        
        session_destroy();
        return true;
    }
    
    // Update last login
    private function updateLastLogin($user_id) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    // Generate TOTP secret for 2FA
    public function generate2FASecret() {
        return base32_encode(random_bytes(20));
    }
    
    // Verify TOTP code
    private function verifyTOTP($secret, $code) {
        // Simple TOTP verification (in production, use a proper library)
        $time = floor(time() / 30);
        $expected_code = $this->generateTOTP($secret, $time);
        
        return hash_equals($expected_code, $code);
    }
    
    // Generate TOTP code
    private function generateTOTP($secret, $time) {
        // Simplified TOTP generation
        $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $time), base32_decode($secret), true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
    
    // Create new user
    public function createUser($userData) {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, role, first_name, last_name, employee_id, department, position, phone, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $userData['username'],
            $userData['email'],
            password_hash($userData['password'], PASSWORD_DEFAULT),
            $userData['role'],
            $userData['first_name'],
            $userData['last_name'],
            $userData['employee_id'],
            $userData['department'],
            $userData['position'],
            $userData['phone'],
            $userData['hire_date']
        ]);
    }
    
    // Update user
    public function updateUser($user_id, $userData) {
        $fields = [];
        $values = [];
        
        foreach ($userData as $key => $value) {
            if ($key !== 'id') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        $values[] = $user_id;
        
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }
    
    // Get all users
    public function getAllUsers($role = null) {
        $sql = "SELECT id, username, email, role, first_name, last_name, employee_id, department, position, status, last_login FROM users";
        $params = [];
        
        if ($role) {
            $sql .= " WHERE role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY last_name, first_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Log system activity
    public function logActivity($action, $table_name = null, $record_id = null, $old_values = null, $new_values = null) {
        $user_id = $_SESSION['user_id'] ?? null;
        
        $stmt = $this->db->prepare("INSERT INTO system_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $user_id,
            $action,
            $table_name,
            $record_id,
            $old_values ? json_encode($old_values) : null,
            $new_values ? json_encode($new_values) : null,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}

// Helper functions for base32 encoding/decoding
function base32_encode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0, $j = strlen($data); $i < $j; $i++) {
        $v <<= 8;
        $v += ord($data[$i]);
        $vbits += 8;
        
        while ($vbits >= 5) {
            $vbits -= 5;
            $output .= $alphabet[$v >> $vbits];
            $v &= ((1 << $vbits) - 1);
        }
    }
    
    if ($vbits > 0) {
        $v <<= (5 - $vbits);
        $output .= $alphabet[$v];
    }
    
    return $output;
}

function base32_decode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0, $j = strlen($data); $i < $j; $i++) {
        $v <<= 5;
        $v += strpos($alphabet, $data[$i]);
        $vbits += 5;
        
        if ($vbits >= 8) {
            $vbits -= 8;
            $output .= chr($v >> $vbits);
            $v &= ((1 << $vbits) - 1);
        }
    }
    
    return $output;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>


