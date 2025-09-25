<?php
// Simplified Authentication and User Management System
class SimpleAuth {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // User login with role-based authentication
    public function login($username, $password, $remember_me = false) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                // Handle remember me functionality
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    try {
                        $stmt = $this->db->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                        $stmt->execute([$user['id'], $token, $expires]);
                        
                        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                    } catch (PDOException $e) {
                        // If remember_tokens table doesn't exist, just continue without remember me
                        error_log("Remember me error: " . $e->getMessage());
                    }
                }
                
                $this->logActivity('login', 'users', $user['id'], null, null);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    // Get current user data
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    // Check user permissions
    public function hasPermission($permission) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['role'];
        
        // Define role-based permissions
        $permissions = [
            'admin' => [
                'manage_users', 'manage_evaluations', 'manage_competencies', 'manage_learning', 
                'manage_succession', 'manage_training', 'manage_requests', 'view_reports',
                'manage_system', 'manage_announcements', 'view_all_data', 'manage_departments'
            ],
            'hr_manager' => [
                'manage_evaluations', 'manage_competencies', 'manage_learning', 'manage_succession',
                'manage_training', 'manage_requests', 'view_reports', 'manage_announcements',
                'view_employee_data', 'manage_competency_models', 'manage_evaluation_cycles',
                'approve_requests', 'view_learning_progress', 'manage_succession_plans',
                'view_performance_data', 'manage_employee_development'
            ],
            'employee' => [
                'view_own_data', 'submit_requests', 'view_own_evaluations', 'view_own_trainings',
                'update_profile', 'view_own_competencies', 'request_training', 'view_announcements'
            ]
        ];
        
        return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
    }
    
    // Check if user is HR Manager
    public function isHRManager() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'hr_manager';
    }
    
    // Check if user is Admin
    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'admin';
    }
    
    // Check if user is Employee
    public function isEmployee() {
        return $this->isLoggedIn() && $_SESSION['role'] === 'employee';
    }
    
    // Get user role
    public function getUserRole() {
        return $this->isLoggedIn() ? $_SESSION['role'] : null;
    }
    
    // Check if user can manage specific employee data
    public function canManageEmployee($employee_id) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['role'];
        
        // Admin can manage all employees
        if ($role === 'admin') {
            return true;
        }
        
        // HR Manager can manage all employees
        if ($role === 'hr_manager') {
            return true;
        }
        
        // Employee can only manage their own data
        if ($role === 'employee') {
            return $_SESSION['user_id'] == $employee_id;
        }
        
        return false;
    }
    
    // Check if user can view specific employee data
    public function canViewEmployee($employee_id) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $role = $_SESSION['role'];
        
        // Admin and HR Manager can view all employees
        if (in_array($role, ['admin', 'hr_manager'])) {
            return true;
        }
        
        // Employee can only view their own data
        if ($role === 'employee') {
            return $_SESSION['user_id'] == $employee_id;
        }
        
        return false;
    }
    
    // Get role-based dashboard data
    public function getRoleBasedDashboardData() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $role = $_SESSION['role'];
        $data = ['role' => $role];
        
        switch ($role) {
            case 'admin':
                $data['can_manage_users'] = true;
                $data['can_manage_system'] = true;
                $data['can_view_all_reports'] = true;
                break;
                
            case 'hr_manager':
                $data['can_manage_evaluations'] = true;
                $data['can_manage_competencies'] = true;
                $data['can_manage_learning'] = true;
                $data['can_manage_succession'] = true;
                $data['can_view_hr_reports'] = true;
                break;
                
            case 'employee':
                $data['can_view_own_data'] = true;
                $data['can_submit_requests'] = true;
                $data['can_view_own_evaluations'] = true;
                break;
        }
        
        return $data;
    }
    
    // Logout user
    public function logout() {
        if ($this->isLoggedIn()) {
            $this->logActivity('logout', 'users', $_SESSION['user_id'], null, null);
        }
        
        session_destroy();
        session_start();
    }
    
    // Create new user
    public function createUser($userData) {
        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, role, first_name, last_name, employee_id, department, position, phone, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            return $stmt->execute([
                $userData['username'],
                $userData['email'],
                password_hash($userData['password'], PASSWORD_DEFAULT),
                $userData['role'],
                $userData['first_name'],
                $userData['last_name'],
                $userData['employee_id'] ?? null,
                $userData['department'] ?? null,
                $userData['position'] ?? null,
                $userData['phone'] ?? null,
                $userData['hire_date'] ?? null,
                'active'
            ]);
        } catch (PDOException $e) {
            error_log("Create user error: " . $e->getMessage());
            return false;
        }
    }
    
    // Update user
    public function updateUser($user_id, $userData) {
        try {
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
        } catch (PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete user (soft delete)
    public function deleteUser($user_id) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET status = 'terminated' WHERE id = ?");
            return $stmt->execute([$user_id]);
        } catch (PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all users
    public function getAllUsers($role = null, $status = 'active') {
        try {
            $sql = "SELECT * FROM users";
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
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }

    // Log user activities
    public function logActivity($action, $table, $record_id = null, $old_values = null, $new_values = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $action,
                $table,
                $record_id,
                $old_values ? json_encode($old_values) : null,
                $new_values ? json_encode($new_values) : null,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (PDOException $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
}

// Start session if not already started and headers not sent
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
?>