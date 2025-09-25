<?php
// Admin & System Management
class AdminManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // User Management
    public function createUser($userData) {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, role, first_name, last_name, employee_id, department, position, phone, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
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
            $userData['hire_date'],
            $userData['status'] ?? 'active'
        ]);
    }
    
    public function updateUser($user_id, $userData) {
        $fields = [];
        $values = [];
        
        foreach ($userData as $key => $value) {
            if ($key !== 'id' && $key !== 'password') {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        if (isset($userData['password']) && !empty($userData['password'])) {
            $fields[] = "password_hash = ?";
            $values[] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }
        
        $values[] = $user_id;
        
        $stmt = $this->db->prepare("UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?");
        return $stmt->execute($values);
    }
    
    public function deleteUser($user_id) {
        $stmt = $this->db->prepare("UPDATE users SET status = 'terminated' WHERE id = ?");
        return $stmt->execute([$user_id]);
    }
    
    public function getAllUsers($role = null, $status = 'active') {
        $sql = "SELECT id, username, email, role, first_name, last_name, employee_id, department, position, phone, hire_date, status, last_login, created_at FROM users";
        $params = [];
        $conditions = [];
        
        if ($role) {
            $conditions[] = "role = ?";
            $params[] = $role;
        }
        
        if ($status) {
            $conditions[] = "status = ?";
            $params[] = $status;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY last_name, first_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // System Settings
    public function getSystemSettings() {
        $stmt = $this->db->prepare("SELECT * FROM system_settings ORDER BY setting_key");
        $stmt->execute();
        
        $settings = [];
        $results = $stmt->fetchAll();
        
        foreach ($results as $setting) {
            $settings[$setting['setting_key']] = [
                'value' => $setting['setting_value'],
                'type' => $setting['setting_type'],
                'description' => $setting['description']
            ];
        }
        
        return $settings;
    }
    
    public function updateSystemSetting($key, $value, $updated_by) {
        $stmt = $this->db->prepare("UPDATE system_settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?");
        return $stmt->execute([$value, $updated_by, $key]);
    }
    
    public function createSystemSetting($key, $value, $type, $description, $updated_by) {
        $stmt = $this->db->prepare("INSERT INTO system_settings (setting_key, setting_value, setting_type, description, updated_by) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$key, $value, $type, $description, $updated_by]);
    }
    
    // System Logs
    public function getSystemLogs($limit = 100, $user_id = null, $action = null) {
        $sql = "
            SELECT sl.*, u.username, u.first_name, u.last_name
            FROM system_logs sl
            LEFT JOIN users u ON sl.user_id = u.id
        ";
        
        $params = [];
        $conditions = [];
        
        if ($user_id) {
            $conditions[] = "sl.user_id = ?";
            $params[] = $user_id;
        }
        
        if ($action) {
            $conditions[] = "sl.action = ?";
            $params[] = $action;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY sl.created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $logs = $stmt->fetchAll();
        
        // Decode JSON fields
        foreach ($logs as &$log) {
            $log['old_values'] = $log['old_values'] ? json_decode($log['old_values'], true) : null;
            $log['new_values'] = $log['new_values'] ? json_decode($log['new_values'], true) : null;
        }
        
        return $logs;
    }
    
    // System Statistics
    public function getSystemStats() {
        $stats = [];
        
        // User statistics
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND status = 'active'");
        $stmt->execute();
        $stats['admin_users'] = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'hr_manager' AND status = 'active'");
        $stmt->execute();
        $stats['hr_users'] = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'employee' AND status = 'active'");
        $stmt->execute();
        $stats['employee_users'] = $stmt->fetch()['count'];
        
        // Activity statistics
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM system_logs WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['today_activities'] = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM user_sessions WHERE expires_at > NOW()");
        $stmt->execute();
        $stats['active_sessions'] = $stmt->fetch()['count'];
        
        // Data statistics
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM competency_models");
        $stmt->execute();
        $stats['competency_models'] = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_catalog WHERE status = 'active'");
        $stmt->execute();
        $stats['active_trainings'] = $stmt->fetch()['count'];
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM critical_positions");
        $stmt->execute();
        $stats['critical_positions'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    // Department Management
    public function getDepartments() {
        $stmt = $this->db->prepare("SELECT DISTINCT department, COUNT(*) as employee_count FROM users WHERE department IS NOT NULL AND status = 'active' GROUP BY department ORDER BY department");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Role Management
    public function getRoles() {
        return [
            'admin' => [
                'name' => 'Administrator',
                'description' => 'Full system access and management capabilities',
                'permissions' => ['manage_users', 'manage_system', 'view_all_data', 'manage_evaluations', 'manage_trainings', 'manage_succession']
            ],
            'hr_manager' => [
                'name' => 'HR Manager',
                'description' => 'HR operations and employee management',
                'permissions' => ['manage_evaluations', 'manage_trainings', 'manage_succession', 'view_hr_data']
            ],
            'employee' => [
                'name' => 'Employee',
                'description' => 'Self-service portal access',
                'permissions' => ['view_own_data', 'request_training', 'submit_evaluations']
            ]
        ];
    }
    
    // Security Management
    public function getSecurityLogs($limit = 50) {
        $stmt = $this->db->prepare("
            SELECT sl.*, u.username, u.first_name, u.last_name
            FROM system_logs sl
            LEFT JOIN users u ON sl.user_id = u.id
            WHERE sl.action IN ('login', 'logout', 'password_change', 'role_change', 'user_create', 'user_delete')
            ORDER BY sl.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        
        return $stmt->fetchAll();
    }
    
    public function getFailedLoginAttempts($limit = 50) {
        // This would track failed login attempts
        // For now, return empty array
        return [];
    }
    
    // System Health Check
    public function getSystemHealth() {
        $health = [
            'database' => 'healthy',
            'sessions' => 'healthy',
            'storage' => 'healthy',
            'performance' => 'healthy'
        ];
        
        // Check database connection
        try {
            $stmt = $this->db->prepare("SELECT 1");
            $stmt->execute();
        } catch (Exception $e) {
            $health['database'] = 'error';
        }
        
        // Check session cleanup
        try {
            $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE expires_at < NOW()");
            $stmt->execute();
        } catch (Exception $e) {
            $health['sessions'] = 'warning';
        }
        
        return $health;
    }
    
    // Export Data
    public function exportUserData($format = 'csv') {
        $users = $this->getAllUsers();
        
        if ($format === 'csv') {
            $output = "ID,Username,Email,Role,First Name,Last Name,Employee ID,Department,Position,Phone,Hire Date,Status,Last Login\n";
            
            foreach ($users as $user) {
                $output .= implode(',', [
                    $user['id'],
                    $user['username'],
                    $user['email'],
                    $user['role'],
                    $user['first_name'],
                    $user['last_name'],
                    $user['employee_id'],
                    $user['department'],
                    $user['position'],
                    $user['phone'],
                    $user['hire_date'],
                    $user['status'],
                    $user['last_login']
                ]) . "\n";
            }
            
            return $output;
        }
        
        return $users;
    }
    
    // Backup System Settings
    public function backupSystemSettings() {
        $settings = $this->getSystemSettings();
        return json_encode($settings, JSON_PRETTY_PRINT);
    }
    
    // Restore System Settings
    public function restoreSystemSettings($settings_json, $updated_by) {
        $settings = json_decode($settings_json, true);
        
        if (!$settings) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            foreach ($settings as $key => $setting) {
                $this->updateSystemSetting($key, $setting['value'], $updated_by);
            }
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
?>


