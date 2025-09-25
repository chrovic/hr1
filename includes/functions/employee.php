<?php
// Employee Portal Management System
class EmployeeManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Get employee profile
    public function getEmployeeProfile($employeeId) {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   COUNT(DISTINCT e.id) as total_evaluations,
                   COUNT(DISTINCT te.id) as total_trainings,
                   COUNT(DISTINCT er.id) as total_requests
            FROM users u
            LEFT JOIN evaluations e ON u.id = e.employee_id
            LEFT JOIN training_enrollments te ON u.id = te.employee_id
            LEFT JOIN employee_requests er ON u.id = er.employee_id
            WHERE u.id = ? AND u.role = 'employee'
        ");
        $stmt->execute([$employeeId]);
        
        return $stmt->fetch();
    }
    
    // Update employee profile
    public function updateEmployeeProfile($employeeId, $profileData) {
        $allowedFields = ['first_name', 'last_name', 'email', 'department', 'position', 'phone', 'address'];
        $updateFields = [];
        $values = [];
        
        foreach ($profileData as $field => $value) {
            if (in_array($field, $allowedFields) && !empty($value)) {
                $updateFields[] = "$field = ?";
                $values[] = $value;
            }
        }
        
        if (empty($updateFields)) {
            return false;
        }
        
        $values[] = $employeeId;
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    // Get employee requests
    public function getEmployeeRequests($employeeId, $status = null) {
        $sql = "
            SELECT er.*, 
                   approver.first_name as approver_first_name, 
                   approver.last_name as approver_last_name
            FROM employee_requests er
            LEFT JOIN users approver ON er.approved_by = approver.id
            WHERE er.employee_id = ?
        ";
        
        $params = [$employeeId];
        
        if ($status) {
            $sql .= " AND er.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY er.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Create employee request
    public function createEmployeeRequest($requestData) {
        $stmt = $this->db->prepare("
            INSERT INTO employee_requests (employee_id, request_type, title, description, request_date, status, created_at) 
            VALUES (?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        return $stmt->execute([
            $requestData['employee_id'],
            $requestData['request_type'],
            $requestData['title'],
            $requestData['description'],
            $requestData['request_date'] ?? date('Y-m-d')
        ]);
    }
    
    // Get employee evaluations
    public function getEmployeeEvaluations($employeeId, $status = null) {
        $sql = "
            SELECT e.*, 
                   cm.name as model_name,
                   evaluator.first_name as evaluator_first_name,
                   evaluator.last_name as evaluator_last_name,
                   ec.name as cycle_name
            FROM evaluations e
            LEFT JOIN competency_models cm ON e.model_id = cm.id
            LEFT JOIN users evaluator ON e.evaluator_id = evaluator.id
            LEFT JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            WHERE e.employee_id = ?
        ";
        
        $params = [$employeeId];
        
        if ($status) {
            $sql .= " AND e.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get employee training enrollments
    public function getEmployeeTrainingEnrollments($employeeId, $status = null) {
        $sql = "
            SELECT te.*, 
                   ts.session_name, ts.start_date, ts.end_date, ts.location,
                   tc.title as training_title, tc.description as training_description,
                   trainer.first_name as trainer_first_name, trainer.last_name as trainer_last_name
            FROM training_enrollments te
            LEFT JOIN training_sessions ts ON te.session_id = ts.id
            LEFT JOIN training_catalog tc ON ts.module_id = tc.id
            LEFT JOIN users trainer ON ts.trainer_id = trainer.id
            WHERE te.employee_id = ?
        ";
        
        $params = [$employeeId];
        
        if ($status) {
            $sql .= " AND te.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY te.enrollment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Request training
    public function requestTraining($requestData) {
        $stmt = $this->db->prepare("
            INSERT INTO training_requests (employee_id, module_id, request_date, status, created_at) 
            VALUES (?, ?, ?, 'pending', NOW())
        ");
        
        return $stmt->execute([
            $requestData['employee_id'],
            $requestData['module_id'],
            $requestData['request_date'] ?? date('Y-m-d')
        ]);
    }
    
    // Get available training modules
    public function getAvailableTrainingModules() {
        $stmt = $this->db->prepare("
            SELECT tc.*, 
                   COUNT(ts.id) as session_count,
                   COUNT(te.id) as enrollment_count
            FROM training_catalog tc
            LEFT JOIN training_sessions ts ON tc.id = ts.module_id
            LEFT JOIN training_enrollments te ON ts.id = te.session_id
            WHERE tc.status = 'active'
            GROUP BY tc.id
            ORDER BY tc.title
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Get employee dashboard statistics
    public function getEmployeeDashboardStats($employeeId) {
        $stats = [];
        
        // Total evaluations
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE employee_id = ?");
        $stmt->execute([$employeeId]);
        $stats['total_evaluations'] = $stmt->fetch()['count'];
        
        // Completed evaluations
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE employee_id = ? AND status = 'completed'");
        $stmt->execute([$employeeId]);
        $stats['completed_evaluations'] = $stmt->fetch()['count'];
        
        // Total training enrollments
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_enrollments WHERE employee_id = ?");
        $stmt->execute([$employeeId]);
        $stats['total_trainings'] = $stmt->fetch()['count'];
        
        // Completed trainings
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_enrollments WHERE employee_id = ? AND status = 'completed'");
        $stmt->execute([$employeeId]);
        $stats['completed_trainings'] = $stmt->fetch()['count'];
        
        // Pending requests
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_requests WHERE employee_id = ? AND status = 'pending'");
        $stmt->execute([$employeeId]);
        $stats['pending_requests'] = $stmt->fetch()['count'];
        
        // Approved requests
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_requests WHERE employee_id = ? AND status = 'approved'");
        $stmt->execute([$employeeId]);
        $stats['approved_requests'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    // Get recent activities for employee
    public function getEmployeeRecentActivities($employeeId, $limit = 10) {
        $activities = [];
        
        // Recent evaluations
        $stmt = $this->db->prepare("
            SELECT 'evaluation' as type, e.created_at, e.status, cm.name as title
            FROM evaluations e
            LEFT JOIN competency_models cm ON e.model_id = cm.id
            WHERE e.employee_id = ?
            ORDER BY e.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$employeeId]);
        $evaluations = $stmt->fetchAll();
        
        // Recent training enrollments
        $stmt = $this->db->prepare("
            SELECT 'training' as type, te.enrollment_date as created_at, te.status, tc.title
            FROM training_enrollments te
            LEFT JOIN training_sessions ts ON te.session_id = ts.id
            LEFT JOIN training_catalog tc ON ts.module_id = tc.id
            WHERE te.employee_id = ?
            ORDER BY te.enrollment_date DESC
            LIMIT 5
        ");
        $stmt->execute([$employeeId]);
        $trainings = $stmt->fetchAll();
        
        // Recent requests
        $stmt = $this->db->prepare("
            SELECT 'request' as type, er.created_at, er.status, er.title
            FROM employee_requests er
            WHERE er.employee_id = ?
            ORDER BY er.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$employeeId]);
        $requests = $stmt->fetchAll();
        
        // Combine and sort by date
        $allActivities = array_merge($evaluations, $trainings, $requests);
        usort($allActivities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($allActivities, 0, $limit);
    }
    
    // Get employee performance summary
    public function getEmployeePerformanceSummary($employeeId) {
        $stmt = $this->db->prepare("
            SELECT 
                AVG(e.overall_score) as avg_score,
                COUNT(e.id) as total_evaluations,
                MAX(e.completed_at) as last_evaluation_date
            FROM evaluations e
            WHERE e.employee_id = ? AND e.status = 'completed'
        ");
        $stmt->execute([$employeeId]);
        
        return $stmt->fetch();
    }
    
    // Update employee password
    public function updateEmployeePassword($employeeId, $newPassword) {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ? AND role = 'employee'");
        return $stmt->execute([$passwordHash, $employeeId]);
    }
    
    // Get employee notifications/announcements
    public function getEmployeeAnnouncements($employeeId) {
        $stmt = $this->db->prepare("
            SELECT a.*, 
                   creator.first_name as creator_first_name,
                   creator.last_name as creator_last_name
            FROM announcements a
            LEFT JOIN users creator ON a.created_by = creator.id
            WHERE a.status = 'active' 
            AND (a.target_audience = 'all' OR a.target_audience = 'employees')
            ORDER BY a.created_at DESC
            LIMIT 10
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
?>




