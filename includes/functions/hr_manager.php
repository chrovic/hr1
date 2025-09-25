<?php

class HRManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    // Get HR Manager Dashboard Statistics
    public function getDashboardStats() {
        try {
            $stats = [];
            
            // Total Employees
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'employee' AND status = 'active'");
            $stmt->execute();
            $stats['total_employees'] = $stmt->fetch()['total'];
            
            // Pending Evaluations
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM evaluations WHERE status = 'pending'");
            $stmt->execute();
            $stats['pending_evaluations'] = $stmt->fetch()['total'];
            
            // Active Learning Sessions
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM training_sessions WHERE status = 'active'");
            $stmt->execute();
            $stats['active_learning_sessions'] = $stmt->fetch()['total'];
            
            // Pending Requests
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM employee_requests WHERE status = 'pending'");
            $stmt->execute();
            $stats['pending_requests'] = $stmt->fetch()['total'];
            
            // Succession Plans
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM succession_plans WHERE status = 'active'");
            $stmt->execute();
            $stats['active_succession_plans'] = $stmt->fetch()['total'];
            
            // Competency Models
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM competency_models WHERE status = 'active'");
            $stmt->execute();
            $stats['competency_models'] = $stmt->fetch()['total'];
            
            return $stats;
        } catch (PDOException $e) {
            error_log("HR Manager stats error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get HR Manager Alerts and Notifications
    public function getHRAlerts() {
        try {
            $alerts = [];
            
            // Overdue Evaluations
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count, 'overdue_evaluations' as type
                FROM evaluations e
                JOIN evaluation_cycles ec ON e.cycle_id = ec.id
                WHERE ec.end_date < CURDATE() AND e.status = 'pending'
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Overdue Evaluations',
                    'message' => $result['count'] . ' evaluations are overdue',
                    'action' => 'View Evaluations',
                    'url' => '?page=evaluation_cycles'
                ];
            }
            
            // High Priority Requests
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count, 'urgent_requests' as type
                FROM employee_requests 
                WHERE status = 'pending'
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Urgent Requests',
                    'message' => $result['count'] . ' high priority requests need attention',
                    'action' => 'Review Requests',
                    'url' => '?page=employee_requests'
                ];
            }
            
            // Upcoming Training Sessions
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count, 'upcoming_training' as type
                FROM training_sessions 
                WHERE start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND status = 'scheduled'
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Upcoming Training',
                    'message' => $result['count'] . ' training sessions this week',
                    'action' => 'View Schedule',
                    'url' => '?page=learning_management'
                ];
            }
            
            // Succession Planning Alerts
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count, 'succession_alerts' as type
                FROM critical_roles 
                WHERE status = 'active'
            ");
            $stmt->execute();
            $result = $stmt->fetch();
            if ($result['count'] > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Succession Review Due',
                    'message' => $result['count'] . ' succession plans need review',
                    'action' => 'Review Plans',
                    'url' => '?page=succession_planning'
                ];
            }
            
            return $alerts;
        } catch (PDOException $e) {
            error_log("HR Manager alerts error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get Recent HR Activities
    public function getRecentHRActivities($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    sl.action,
                    sl.table_name,
                    sl.created_at,
                    u.first_name,
                    u.last_name,
                    u.role
                FROM system_logs sl
                LEFT JOIN users u ON sl.user_id = u.id
                WHERE sl.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY sl.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("HR Manager activities error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get Employee Performance Summary
    public function getEmployeePerformanceSummary() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(DISTINCT u.id) as total_employees,
                    AVG(CASE WHEN e.status = 'completed' THEN e.overall_score END) as avg_rating,
                    COUNT(CASE WHEN e.status = 'pending' THEN 1 END) as pending_evaluations,
                    COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_evaluations
                FROM users u
                LEFT JOIN evaluations e ON u.id = e.employee_id AND e.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                WHERE u.role = 'employee' AND u.status = 'active'
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("HR Manager performance summary error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get Learning Progress Summary
    public function getLearningProgressSummary() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_enrollments,
                    COUNT(CASE WHEN te.status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN te.status = 'in_progress' THEN 1 END) as in_progress,
                    COUNT(CASE WHEN te.status = 'pending' THEN 1 END) as pending
                FROM training_enrollments te
                WHERE te.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            ");
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("HR Manager learning summary error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get Department Performance Overview
    public function getDepartmentPerformanceOverview() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.department as department_name,
                    COUNT(DISTINCT u.id) as employee_count,
                    AVG(CASE WHEN e.status = 'completed' THEN e.overall_score END) as avg_rating,
                    COUNT(e.id) as evaluation_count
                FROM users u
                LEFT JOIN evaluations e ON u.id = e.employee_id AND e.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                WHERE u.role = 'employee' AND u.status = 'active' AND u.department IS NOT NULL
                GROUP BY u.department
                ORDER BY avg_rating DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("HR Manager department overview error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get Upcoming HR Events
    public function getUpcomingHREvents($days = 30) {
        try {
            $events = [];
            
            // Upcoming Training Sessions
            $stmt = $this->db->prepare("
                SELECT 
                    'training' as event_type,
                    ts.session_name as event_title,
                    ts.start_date as event_date,
                    ts.location,
                    COUNT(te.id) as attendees
                FROM training_sessions ts
                LEFT JOIN training_enrollments te ON ts.id = te.session_id
                WHERE ts.start_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND ts.status = 'scheduled'
                GROUP BY ts.id
                ORDER BY ts.start_date ASC
            ");
            $stmt->execute([$days]);
            $training_events = $stmt->fetchAll();
            
            foreach ($training_events as $event) {
                $events[] = [
                    'type' => 'training',
                    'title' => $event['event_title'],
                    'date' => $event['event_date'],
                    'location' => $event['location'],
                    'attendees' => $event['attendees'],
                    'icon' => 'fe-book-open'
                ];
            }
            
            // Upcoming Evaluations
            $stmt = $this->db->prepare("
                SELECT 
                    'evaluation' as event_type,
                    CONCAT('Evaluation: ', u.first_name, ' ', u.last_name) as event_title,
                    ec.end_date as event_date,
                    '' as location,
                    1 as attendees
                FROM evaluations e
                JOIN users u ON e.employee_id = u.id
                JOIN evaluation_cycles ec ON e.cycle_id = ec.id
                WHERE ec.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND e.status = 'pending'
                ORDER BY ec.end_date ASC
            ");
            $stmt->execute([$days]);
            $evaluation_events = $stmt->fetchAll();
            
            foreach ($evaluation_events as $event) {
                $events[] = [
                    'type' => 'evaluation',
                    'title' => $event['event_title'],
                    'date' => $event['event_date'],
                    'location' => '',
                    'attendees' => 1,
                    'icon' => 'fe-target'
                ];
            }
            
            // Sort all events by date
            usort($events, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            
            return array_slice($events, 0, 10); // Return next 10 events
        } catch (PDOException $e) {
            error_log("HR Manager events error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get HR Manager Quick Actions Data
    public function getQuickActionsData() {
        return [
            'competency_models' => [
                'title' => 'Competency Models',
                'description' => 'Manage competency frameworks and models',
                'icon' => 'fe-target',
                'url' => '?page=competency_models',
                'color' => 'primary'
            ],
            'learning_management' => [
                'title' => 'Learning Management',
                'description' => 'Oversee training and development programs',
                'icon' => 'fe-book-open',
                'url' => '?page=learning_management',
                'color' => 'success'
            ],
            'succession_planning' => [
                'title' => 'Succession Planning',
                'description' => 'Plan and manage succession strategies',
                'icon' => 'fe-trending-up',
                'url' => '?page=succession_planning',
                'color' => 'info'
            ],
            'employee_requests' => [
                'title' => 'Employee Requests',
                'description' => 'Handle employee requests and approvals',
                'icon' => 'fe-file-text',
                'url' => '?page=employee_requests',
                'color' => 'warning'
            ],
            'evaluation_cycles' => [
                'title' => 'Evaluation Cycles',
                'description' => 'Manage performance evaluation cycles',
                'icon' => 'fe-bar-chart-2',
                'url' => '?page=evaluation_cycles',
                'color' => 'secondary'
            ],
            'hr_reports' => [
                'title' => 'HR Reports',
                'description' => 'Generate HR analytics and reports',
                'icon' => 'fe-pie-chart',
                'url' => '?page=hr_reports',
                'color' => 'dark'
            ]
        ];
    }
}

