<?php
// Employee Self-Service System
class EmployeeSelfService {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Submit employee request
    public function submitRequest($requestData) {
        $stmt = $this->db->prepare("INSERT INTO employee_requests (employee_id, request_type, title, description, request_data) VALUES (?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $requestData['employee_id'],
            $requestData['request_type'],
            $requestData['title'],
            $requestData['description'],
            json_encode($requestData['request_data'])
        ]);
    }
    
    // Get employee requests
    public function getEmployeeRequests($employee_id, $status = null) {
        $sql = "
            SELECT er.*, 
                   reviewer.first_name as reviewer_first_name, reviewer.last_name as reviewer_last_name
            FROM employee_requests er
            LEFT JOIN users reviewer ON er.reviewed_by = reviewer.id
            WHERE er.employee_id = ?
        ";
        
        $params = [$employee_id];
        
        if ($status) {
            $sql .= " AND er.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY er.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $requests = $stmt->fetchAll();
        
        // Decode JSON data
        foreach ($requests as &$request) {
            $request['request_data'] = json_decode($request['request_data'], true);
        }
        
        return $requests;
    }
    
    // Get all requests for HR/Admin review
    public function getAllRequests($status = null, $request_type = null) {
        $sql = "
            SELECT er.*, 
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   emp.department as employee_department,
                   reviewer.first_name as reviewer_first_name, reviewer.last_name as reviewer_last_name
            FROM employee_requests er
            JOIN users emp ON er.employee_id = emp.id
            LEFT JOIN users reviewer ON er.reviewed_by = reviewer.id
        ";
        
        $params = [];
        $conditions = [];
        
        if ($status) {
            $conditions[] = "er.status = ?";
            $params[] = $status;
        }
        
        if ($request_type) {
            $conditions[] = "er.request_type = ?";
            $params[] = $request_type;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY er.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $requests = $stmt->fetchAll();
        
        // Decode JSON data
        foreach ($requests as &$request) {
            $request['request_data'] = json_decode($request['request_data'], true);
        }
        
        return $requests;
    }
    
    // Process request (approve/reject)
    public function processRequest($request_id, $action, $reviewed_by, $comments = null) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        $stmt = $this->db->prepare("UPDATE employee_requests SET status = ?, reviewed_by = ?, reviewed_at = NOW(), review_comments = ? WHERE id = ?");
        
        return $stmt->execute([
            $status,
            $reviewed_by,
            $comments,
            $request_id
        ]);
    }
    
    // Get employee performance history
    public function getEmployeePerformanceHistory($employee_id) {
        $stmt = $this->db->prepare("
            SELECT e.overall_score, e.feedback, e.completed_at,
                   ec.name as cycle_name, ec.type as cycle_type, ec.start_date,
                   cm.name as model_name,
                   evaluator.first_name as evaluator_first_name, evaluator.last_name as evaluator_last_name
            FROM evaluations e
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            JOIN competency_models cm ON e.model_id = cm.id
            JOIN users evaluator ON e.evaluator_id = evaluator.id
            WHERE e.employee_id = ? AND e.status = 'completed'
            ORDER BY ec.start_date DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get employee training history
    public function getEmployeeTrainingHistory($employee_id) {
        $stmt = $this->db->prepare("
            SELECT te.*, ts.session_date, ts.start_time, ts.end_time, ts.location,
                   tc.title as training_title, tc.description as training_description, tc.category,
                   trainer.first_name as trainer_first_name, trainer.last_name as trainer_last_name
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            JOIN training_catalog tc ON ts.training_id = tc.id
            JOIN users trainer ON ts.trainer_id = trainer.id
            WHERE te.employee_id = ?
            ORDER BY ts.session_date DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get employee competency trends
    public function getEmployeeCompetencyTrends($employee_id) {
        $stmt = $this->db->prepare("
            SELECT e.overall_score, ec.name as cycle_name, ec.start_date,
                   cm.name as model_name
            FROM evaluations e
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            JOIN competency_models cm ON e.model_id = cm.id
            WHERE e.employee_id = ? AND e.status = 'completed'
            ORDER BY ec.start_date ASC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get employee dashboard data
    public function getEmployeeDashboardData($employee_id) {
        $data = [];
        
        // Pending requests
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_requests WHERE employee_id = ? AND status = 'pending'");
        $stmt->execute([$employee_id]);
        $data['pending_requests'] = $stmt->fetch()['count'];
        
        // Upcoming trainings
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            WHERE te.employee_id = ? 
            AND ts.session_date >= CURDATE() 
            AND te.completion_status = 'not_started'
        ");
        $stmt->execute([$employee_id]);
        $data['upcoming_trainings'] = $stmt->fetch()['count'];
        
        // Completed trainings this year
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            WHERE te.employee_id = ? 
            AND te.completion_status = 'completed'
            AND YEAR(ts.session_date) = YEAR(CURDATE())
        ");
        $stmt->execute([$employee_id]);
        $data['completed_trainings_year'] = $stmt->fetch()['count'];
        
        // Pending evaluations
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM evaluations WHERE employee_id = ? AND status = 'pending'");
        $stmt->execute([$employee_id]);
        $data['pending_evaluations'] = $stmt->fetch()['count'];
        
        // Succession opportunities
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM succession_candidates WHERE candidate_id = ?");
        $stmt->execute([$employee_id]);
        $data['succession_opportunities'] = $stmt->fetch()['count'];
        
        return $data;
    }
    
    // Get employee leave balance (mock data for now)
    public function getEmployeeLeaveBalance($employee_id) {
        // This would integrate with a leave management system
        // For now, return mock data
        return [
            'annual_leave' => 15,
            'sick_leave' => 5,
            'personal_days' => 3,
            'used_annual' => 10,
            'used_sick' => 2,
            'used_personal' => 1,
            'remaining_annual' => 5,
            'remaining_sick' => 3,
            'remaining_personal' => 2
        ];
    }
    
    // Get employee documents (mock data for now)
    public function getEmployeeDocuments($employee_id) {
        // This would integrate with a document management system
        // For now, return mock data
        return [
            [
                'name' => 'Employment Contract',
                'type' => 'Contract',
                'date' => '2024-01-15',
                'status' => 'active',
                'url' => '#'
            ],
            [
                'name' => 'Performance Review 2024',
                'type' => 'Review',
                'date' => '2024-11-15',
                'status' => 'pending',
                'url' => '#'
            ],
            [
                'name' => 'Training Certificate - Leadership',
                'type' => 'Certificate',
                'date' => '2024-10-20',
                'status' => 'completed',
                'url' => '#'
            ]
        ];
    }
    
    // Get recent activities
    public function getRecentActivities($employee_id) {
        $activities = [];
        
        // Recent evaluations
        $stmt = $this->db->prepare("
            SELECT 'evaluation' as type, e.completed_at as date, 
                   CONCAT('Evaluation completed: ', ec.name) as description,
                   'success' as status
            FROM evaluations e
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            WHERE e.employee_id = ? AND e.status = 'completed'
            ORDER BY e.completed_at DESC
            LIMIT 5
        ");
        $stmt->execute([$employee_id]);
        $evaluations = $stmt->fetchAll();
        
        // Recent training completions
        $stmt = $this->db->prepare("
            SELECT 'training' as type, ts.session_date as date,
                   CONCAT('Training completed: ', tc.title) as description,
                   'success' as status
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            JOIN training_catalog tc ON ts.training_id = tc.id
            WHERE te.employee_id = ? AND te.completion_status = 'completed'
            ORDER BY ts.session_date DESC
            LIMIT 5
        ");
        $stmt->execute([$employee_id]);
        $trainings = $stmt->fetchAll();
        
        // Recent requests
        $stmt = $this->db->prepare("
            SELECT 'request' as type, er.created_at as date,
                   CONCAT('Request submitted: ', er.title) as description,
                   er.status
            FROM employee_requests er
            WHERE er.employee_id = ?
            ORDER BY er.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$employee_id]);
        $requests = $stmt->fetchAll();
        
        // Combine and sort by date
        $activities = array_merge($evaluations, $trainings, $requests);
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return array_slice($activities, 0, 10);
    }
    
    // Get training recommendations
    public function getTrainingRecommendations($employee_id) {
        // This would integrate with competency gap analysis
        // For now, return general recommendations
        $stmt = $this->db->prepare("
            SELECT tc.*, COUNT(tr.id) as request_count
            FROM training_catalog tc
            LEFT JOIN training_requests tr ON tc.id = tr.training_id AND tr.employee_id = ?
            WHERE tc.status = 'active'
            GROUP BY tc.id
            ORDER BY tc.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
}
?>


