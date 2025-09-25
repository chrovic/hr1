<?php
// Learning Management System
class LearningManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Create training in catalog
    public function createTraining($trainingData) {
        $stmt = $this->db->prepare("INSERT INTO training_modules (title, description, category, type, duration_hours, max_participants, prerequisites, learning_objectives, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $trainingData['title'],
            $trainingData['description'],
            $trainingData['category'],
            $trainingData['type'],
            $trainingData['duration_hours'],
            $trainingData['max_participants'],
            $trainingData['prerequisites'],
            $trainingData['learning_objectives'],
            $trainingData['created_by']
        ]);
    }
    
    // Get all training catalog
    public function getAllTrainings($status = 'active') {
        $sql = "
            SELECT tc.*, u.first_name, u.last_name,
                   COUNT(tr.id) as request_count,
                   COUNT(ts.id) as session_count
            FROM training_modules tc
            LEFT JOIN users u ON tc.created_by = u.id
            LEFT JOIN training_requests tr ON tc.id = tr.module_id
            LEFT JOIN training_sessions ts ON tc.id = ts.module_id
        ";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE tc.status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY tc.id ORDER BY tc.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Request training
    public function requestTraining($requestData) {
        $stmt = $this->db->prepare("INSERT INTO training_requests (employee_id, module_id, request_date, status) VALUES (?, ?, ?, ?)");
        
        return $stmt->execute([
            $requestData['employee_id'],
            $requestData['training_id'],
            $requestData['request_date'] ?? date('Y-m-d'),
            $requestData['status'] ?? 'pending'
        ]);
    }
    
    // Get training requests
    public function getTrainingRequests($status = null, $employee_id = null) {
        $sql = "
            SELECT tr.*, tc.title as training_title, tc.description as training_description,
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   emp.department as employee_department,
                   approver.first_name as approver_first_name, approver.last_name as approver_last_name
            FROM training_requests tr
            JOIN training_modules tc ON tr.module_id = tc.id
            JOIN users emp ON tr.employee_id = emp.id
            LEFT JOIN users approver ON tr.approved_by = approver.id
        ";
        
        $params = [];
        $conditions = [];
        
        if ($status) {
            $conditions[] = "tr.status = ?";
            $params[] = $status;
        }
        
        if ($employee_id) {
            $conditions[] = "tr.employee_id = ?";
            $params[] = $employee_id;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY tr.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Approve/Reject training request
    public function processTrainingRequest($request_id, $action, $reviewed_by, $comments = null) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        $stmt = $this->db->prepare("UPDATE training_requests SET status = ?, approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?");
        
        return $stmt->execute([
            $status,
            $reviewed_by,
            $action === 'reject' ? $comments : null,
            $request_id
        ]);
    }
    
    // Schedule training session
    public function scheduleSession($sessionData) {
        $stmt = $this->db->prepare("INSERT INTO training_sessions (module_id, session_name, trainer_id, start_date, end_date, location, max_participants, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $sessionData['training_id'],
            $sessionData['session_name'] ?? 'Training Session',
            $sessionData['trainer_id'] ?? $sessionData['created_by'],
            $sessionData['session_date'] . ' ' . ($sessionData['start_time'] ?? '09:00:00'),
            $sessionData['session_date'] . ' ' . ($sessionData['end_time'] ?? '17:00:00'),
            $sessionData['location'],
            $sessionData['max_participants'],
            $sessionData['status'] ?? 'scheduled',
            $sessionData['created_by']
        ]);
    }
    
    // Get training sessions
    public function getTrainingSessions($status = null, $trainer_id = null) {
        $sql = "
            SELECT ts.*, tc.title as training_title, tc.description as training_description,
                   trainer.first_name as trainer_first_name, trainer.last_name as trainer_last_name,
                   creator.first_name as creator_first_name, creator.last_name as creator_last_name,
                   COUNT(te.id) as enrollment_count
            FROM training_sessions ts
            JOIN training_modules tc ON ts.module_id = tc.id
            JOIN users trainer ON ts.trainer_id = trainer.id
            JOIN users creator ON ts.created_by = creator.id
            LEFT JOIN training_enrollments te ON ts.id = te.session_id
        ";
        
        $params = [];
        $conditions = [];
        
        if ($status) {
            $conditions[] = "ts.status = ?";
            $params[] = $status;
        }
        
        if ($trainer_id) {
            $conditions[] = "ts.trainer_id = ?";
            $params[] = $trainer_id;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " GROUP BY ts.id ORDER BY ts.session_date, ts.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Enroll in training session
    public function enrollInSession($session_id, $employee_id) {
        // Check if already enrolled
        $stmt = $this->db->prepare("SELECT id FROM training_enrollments WHERE session_id = ? AND employee_id = ?");
        $stmt->execute([$session_id, $employee_id]);
        
        if ($stmt->fetch()) {
            return false; // Already enrolled
        }
        
        // Check if session has capacity
        $stmt = $this->db->prepare("
            SELECT ts.max_participants, COUNT(te.id) as current_enrollments
            FROM training_sessions ts
            LEFT JOIN training_enrollments te ON ts.id = te.session_id
            WHERE ts.id = ?
            GROUP BY ts.id
        ");
        $stmt->execute([$session_id]);
        $session = $stmt->fetch();
        
        if ($session && $session['current_enrollments'] >= $session['max_participants']) {
            return false; // Session full
        }
        
        // Enroll
        $stmt = $this->db->prepare("INSERT INTO training_enrollments (session_id, employee_id) VALUES (?, ?)");
        
        return $stmt->execute([$session_id, $employee_id]);
    }
    
    // Get employee enrollments
    public function getEmployeeEnrollments($employee_id) {
        $stmt = $this->db->prepare("
            SELECT te.*, ts.session_name, ts.start_date, ts.end_date, ts.location,
                   tm.title as training_title, tm.description as training_description,
                   trainer.first_name as trainer_first_name, trainer.last_name as trainer_last_name
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            JOIN training_modules tm ON ts.module_id = tm.id
            LEFT JOIN users trainer ON ts.trainer_id = trainer.id
            WHERE te.employee_id = ?
            ORDER BY ts.start_date DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // Mark attendance
    public function markAttendance($enrollment_id, $attendance_status) {
        $stmt = $this->db->prepare("UPDATE training_enrollments SET attendance_status = ? WHERE id = ?");
        
        return $stmt->execute([$attendance_status, $enrollment_id]);
    }
    
    // Mark completion
    public function markCompletion($enrollment_id, $completion_status, $score = null, $feedback = null) {
        $stmt = $this->db->prepare("UPDATE training_enrollments SET completion_status = ?, completion_score = ?, feedback = ? WHERE id = ?");
        
        return $stmt->execute([$completion_status, $score, $feedback, $enrollment_id]);
    }
    
    // Get training completion reports
    public function getCompletionReports($training_id = null, $department = null, $date_from = null, $date_to = null) {
        $sql = "
            SELECT te.*, ts.session_date, ts.start_time, ts.end_time,
                   tc.title as training_title, tc.category as training_category,
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   emp.department as employee_department, emp.position as employee_position,
                   trainer.first_name as trainer_first_name, trainer.last_name as trainer_last_name
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            JOIN training_modules tc ON ts.module_id = tc.id
            JOIN users emp ON te.employee_id = emp.id
            JOIN users trainer ON ts.trainer_id = trainer.id
            WHERE te.completion_status IN ('completed', 'failed')
        ";
        
        $params = [];
        $conditions = [];
        
        if ($training_id) {
            $conditions[] = "tc.id = ?";
            $params[] = $training_id;
        }
        
        if ($department) {
            $conditions[] = "emp.department = ?";
            $params[] = $department;
        }
        
        if ($date_from) {
            $conditions[] = "ts.session_date >= ?";
            $params[] = $date_from;
        }
        
        if ($date_to) {
            $conditions[] = "ts.session_date <= ?";
            $params[] = $date_to;
        }
        
        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY ts.session_date DESC, emp.last_name, emp.first_name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get AI-powered training recommendations based on competency gaps
    public function getTrainingRecommendations($employee_id) {
        // Get employee's competency gaps and performance data
        $competency_gaps = $this->getEmployeeCompetencyGaps($employee_id);
        $employee_profile = $this->getEmployeeProfile($employee_id);

        // Initialize AI integration
        require_once 'ai_integration.php';
        $ai = new AIIntegration();

        // Get AI-powered recommendations
        $ai_recommendations = $ai->generateTrainingRecommendations($employee_id, $competency_gaps);

        // Get existing training history to avoid duplicates
        $stmt = $this->db->prepare("
            SELECT tm.id, tm.title, tm.category
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            JOIN training_modules tm ON ts.module_id = tm.id
            WHERE te.employee_id = ? AND te.status = 'completed'
        ");
        $stmt->execute([$employee_id]);
        $completed_trainings = $stmt->fetchAll();

        $completed_training_ids = array_column($completed_trainings, 'id');

        // Get recommended training modules based on gaps and AI analysis
        $recommendations = [];

        // Add AI-recommended trainings
        foreach ($ai_recommendations['recommendations'] as $rec) {
            $stmt = $this->db->prepare("
                SELECT tm.*, COUNT(tr.id) as request_count
                FROM training_modules tm
                LEFT JOIN training_requests tr ON tm.id = tr.module_id AND tr.employee_id = ?
                WHERE tm.title LIKE ? AND tm.status = 'active'
                AND tm.id NOT IN (" . (empty($completed_training_ids) ? '0' : implode(',', $completed_training_ids)) . ")
                GROUP BY tm.id
                LIMIT 1
            ");
            $stmt->execute([$employee_id, '%' . $rec['module_title'] . '%']);
            $module = $stmt->fetch();

            if ($module) {
                $module['ai_reason'] = $rec['reason'];
                $module['ai_priority'] = $rec['priority'];
                $module['ai_estimated_duration'] = $rec['estimated_duration'];
                $recommendations[] = $module;
            }
        }

        // Add department-specific recommendations
        $stmt = $this->db->prepare("
            SELECT tm.*, COUNT(tr.id) as request_count
            FROM training_modules tm
            LEFT JOIN training_requests tr ON tm.id = tr.module_id AND tr.employee_id = ?
            WHERE tm.category = ? AND tm.status = 'active'
            AND tm.id NOT IN (" . (empty($completed_training_ids) ? '0' : implode(',', $completed_training_ids)) . ")
            GROUP BY tm.id
            ORDER BY tm.created_at DESC
            LIMIT 5
        ");
        $stmt->execute([$employee_id, $employee_profile['department_category']]);
        $dept_recommendations = $stmt->fetchAll();

        foreach ($dept_recommendations as $rec) {
            $rec['ai_reason'] = 'Department-specific skill development';
            $rec['ai_priority'] = 'medium';
            $rec['ai_estimated_duration'] = $rec['duration_hours'];
            $recommendations[] = $rec;
        }

        // Remove duplicates and limit to top 10
        $unique_recommendations = [];
        $seen_ids = [];

        foreach ($recommendations as $rec) {
            if (!in_array($rec['id'], $seen_ids)) {
                $unique_recommendations[] = $rec;
                $seen_ids[] = $rec['id'];
            }
        }

        return array_slice($unique_recommendations, 0, 10);
    }

    // Get employee competency gaps (helper method)
    private function getEmployeeCompetencyGaps($employee_id) {
        // Get employee's latest evaluation scores
        $stmt = $this->db->prepare("
            SELECT c.name as competency_name, cs.score, c.max_score
            FROM competency_scores cs
            JOIN competencies c ON cs.competency_id = c.id
            JOIN evaluations e ON cs.evaluation_id = e.id
            WHERE e.employee_id = ? AND e.status = 'completed'
            ORDER BY e.completed_at DESC
            LIMIT 10
        ");
        $stmt->execute([$employee_id]);
        $scores = $stmt->fetchAll();

        $gaps = [];
        foreach ($scores as $score) {
            $gap_score = $score['max_score'] - $score['score'];
            if ($gap_score >= 2) { // Significant gap
                $gaps[] = [
                    'competency' => $score['competency_name'],
                    'current_score' => $score['score'],
                    'max_score' => $score['max_score'],
                    'gap_score' => $gap_score
                ];
            }
        }

        return $gaps;
    }

    // Get employee profile for recommendations (helper method)
    private function getEmployeeProfile($employee_id) {
        $stmt = $this->db->prepare("
            SELECT u.department, u.position,
                   CASE
                       WHEN u.department = 'Development' THEN 'Technical'
                       WHEN u.department = 'Human Resources' THEN 'Management'
                       WHEN u.department = 'Design' THEN 'Creative'
                       ELSE 'General'
                   END as department_category
            FROM users u
            WHERE u.id = ?
        ");
        $stmt->execute([$employee_id]);

        return $stmt->fetch();
    }
    
    // Get training statistics
    public function getTrainingStats() {
        $stats = [];
        
        // Total trainings
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_modules WHERE status = 'active'");
        $stmt->execute();
        $stats['total_trainings'] = $stmt->fetch()['count'];
        
        // Total requests
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_requests");
        $stmt->execute();
        $stats['total_requests'] = $stmt->fetch()['count'];
        
        // Pending requests
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_requests WHERE status = 'pending'");
        $stmt->execute();
        $stats['pending_requests'] = $stmt->fetch()['count'];
        
        // Completed trainings
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_enrollments WHERE completion_status = 'completed'");
        $stmt->execute();
        $stats['completed_trainings'] = $stmt->fetch()['count'];
        
        // Upcoming sessions
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_sessions WHERE session_date >= CURDATE() AND status = 'scheduled'");
        $stmt->execute();
        $stats['upcoming_sessions'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    // Get all sessions
    public function getAllSessions() {
        $stmt = $this->db->prepare("
            SELECT ts.*, tc.title as training_title,
                   trainer.first_name as trainer_first_name, trainer.last_name as trainer_last_name,
                   COUNT(te.id) as enrollment_count
            FROM training_sessions ts
            LEFT JOIN training_modules tc ON ts.module_id = tc.id
            LEFT JOIN users trainer ON ts.trainer_id = trainer.id
            LEFT JOIN training_enrollments te ON ts.id = te.session_id
            GROUP BY ts.id
            ORDER BY ts.start_date DESC
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Get all enrollments
    public function getAllEnrollments() {
        $stmt = $this->db->prepare("
            SELECT te.*, ts.session_name, tc.title as training_title,
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name
            FROM training_enrollments te
            LEFT JOIN training_sessions ts ON te.session_id = ts.id
            LEFT JOIN training_modules tc ON ts.module_id = tc.id
            LEFT JOIN users emp ON te.employee_id = emp.id
            ORDER BY te.enrollment_date DESC
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Create training session (alias for scheduleSession)
    public function createTrainingSession($sessionData) {
        return $this->scheduleSession($sessionData);
    }
    
    // Enroll employee in session
    public function enrollEmployee($enrollmentData) {
        $stmt = $this->db->prepare("INSERT INTO training_enrollments (session_id, employee_id, enrollment_date, status) VALUES (?, ?, ?, ?)");
        
        return $stmt->execute([
            $enrollmentData['session_id'],
            $enrollmentData['employee_id'],
            $enrollmentData['enrollment_date'],
            $enrollmentData['status'] ?? 'enrolled'
        ]);
    }
    
    // Update training
    public function updateTraining($trainingId, $updateData) {
        $stmt = $this->db->prepare("UPDATE training_modules SET title = ?, description = ?, category = ?, type = ?, duration_hours = ?, max_participants = ?, prerequisites = ?, learning_objectives = ?, updated_at = NOW() WHERE id = ?");
        
        return $stmt->execute([
            $updateData['title'],
            $updateData['description'],
            $updateData['category'],
            $updateData['type'],
            $updateData['duration_hours'],
            $updateData['max_participants'],
            $updateData['prerequisites'],
            $updateData['learning_objectives'],
            $trainingId
        ]);
    }
    
    // Delete training
    public function deleteTraining($trainingId) {
        $stmt = $this->db->prepare("UPDATE training_modules SET status = 'inactive' WHERE id = ?");
        return $stmt->execute([$trainingId]);
    }
    
    // ==============================================
    // SKILLS MANAGEMENT
    // ==============================================
    
    // Get all skills
    public function getAllSkills($status = 'active') {
        $sql = "SELECT * FROM skills_catalog";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get employee skills
    public function getEmployeeSkills($employee_id) {
        $stmt = $this->db->prepare("
            SELECT es.*, sc.name as skill_name, sc.description as skill_description, 
                   sc.category as skill_category, sc.skill_level as required_level,
                   verifier.first_name as verifier_first_name, verifier.last_name as verifier_last_name
            FROM employee_skills es
            JOIN skills_catalog sc ON es.skill_id = sc.id
            LEFT JOIN users verifier ON es.verified_by = verifier.id
            WHERE es.employee_id = ? AND es.status = 'active'
            ORDER BY es.acquired_date DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // Add employee skill
    public function addEmployeeSkill($skillData) {
        $stmt = $this->db->prepare("
            INSERT INTO employee_skills (employee_id, skill_id, proficiency_level, acquired_date, verified_by, verification_date) 
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            proficiency_level = VALUES(proficiency_level),
            acquired_date = VALUES(acquired_date),
            verified_by = VALUES(verified_by),
            verification_date = VALUES(verification_date),
            updated_at = NOW()
        ");
        
        return $stmt->execute([
            $skillData['employee_id'],
            $skillData['skill_id'],
            $skillData['proficiency_level'],
            $skillData['acquired_date'],
            $skillData['verified_by'],
            $skillData['verification_date']
        ]);
    }
    
    // ==============================================
    // ENHANCED TRAINING REQUEST MANAGEMENT
    // ==============================================
    
    // Submit enhanced training request
    public function submitTrainingRequest($requestData) {
        $stmt = $this->db->prepare("
            INSERT INTO training_requests 
            (employee_id, module_id, request_date, reason, priority, manager_id, 
             estimated_cost, session_preference, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Ensure proper data types
        $estimated_cost = isset($requestData['estimated_cost']) ? floatval($requestData['estimated_cost']) : 0.00;
        $manager_id = !empty($requestData['manager_id']) ? $requestData['manager_id'] : null;
        
        return $stmt->execute([
            $requestData['employee_id'],
            $requestData['module_id'],
            $requestData['request_date'],
            $requestData['reason'],
            $requestData['priority'],
            $manager_id,
            $estimated_cost,
            $requestData['session_preference'],
            $requestData['status'] ?? 'pending'
        ]);
    }
    
    // Get enhanced training requests with more details
    public function getEnhancedTrainingRequests($status = null, $employee_id = null, $manager_id = null) {
        $sql = "
            SELECT tr.*, tm.title as training_title, tm.description as training_description,
                   tm.type as training_type, tm.cost as training_cost, tm.duration_hours,
                   emp.first_name, emp.last_name, emp.email, emp.department, emp.position,
                   manager.first_name as manager_first_name, manager.last_name as manager_last_name,
                   approver.first_name as approver_first_name, approver.last_name as approver_last_name
            FROM training_requests tr
            LEFT JOIN training_modules tm ON tr.module_id = tm.id
            LEFT JOIN users emp ON tr.employee_id = emp.id
            LEFT JOIN users manager ON tr.manager_id = manager.id
            LEFT JOIN users approver ON tr.approved_by = approver.id
        ";
        
        $params = [];
        $conditions = [];
        
        if ($status) {
            $conditions[] = "tr.status = ?";
            $params[] = $status;
        }
        
        if ($employee_id) {
            $conditions[] = "tr.employee_id = ?";
            $params[] = $employee_id;
        }
        
        if ($manager_id) {
            $conditions[] = "tr.manager_id = ?";
            $params[] = $manager_id;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY tr.request_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get learning analytics
    public function getLearningAnalytics($date_from = null, $date_to = null) {
        $analytics = [];
        
        // Total skills in catalog
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM skills_catalog WHERE status = 'active'");
        $stmt->execute();
        $analytics['total_skills'] = $stmt->fetch()['count'];
        
        // Total certifications in catalog
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM certifications_catalog WHERE status = 'active'");
        $stmt->execute();
        $analytics['total_certifications'] = $stmt->fetch()['count'];
        
        // Total learning paths
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM learning_paths WHERE status = 'active'");
        $stmt->execute();
        $analytics['total_learning_paths'] = $stmt->fetch()['count'];
        
        // Active learning paths (employees enrolled)
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_learning_paths WHERE status = 'in_progress'");
        $stmt->execute();
        $analytics['active_learning_paths'] = $stmt->fetch()['count'];
        
        // Completed learning paths
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_learning_paths WHERE status = 'completed'");
        $stmt->execute();
        $analytics['completed_learning_paths'] = $stmt->fetch()['count'];
        
        return $analytics;
    }
    
    // Get employee learning summary
    public function getEmployeeLearningSummary($employee_id) {
        $summary = [];
        
        // Skills count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_skills WHERE employee_id = ? AND status = 'active'");
        $stmt->execute([$employee_id]);
        $summary['skills_count'] = $stmt->fetch()['count'];
        
        // Certifications count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_certifications WHERE employee_id = ? AND status = 'active'");
        $stmt->execute([$employee_id]);
        $summary['certifications_count'] = $stmt->fetch()['count'];
        
        // Learning paths count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_learning_paths WHERE employee_id = ?");
        $stmt->execute([$employee_id]);
        $summary['learning_paths_count'] = $stmt->fetch()['count'];
        
        // Completed learning paths
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM employee_learning_paths WHERE employee_id = ? AND status = 'completed'");
        $stmt->execute([$employee_id]);
        $summary['completed_paths_count'] = $stmt->fetch()['count'];
        
        // Training requests count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM training_requests WHERE employee_id = ?");
        $stmt->execute([$employee_id]);
        $summary['training_requests_count'] = $stmt->fetch()['count'];
        
        // Completed trainings
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM training_enrollments te
            JOIN training_sessions ts ON te.session_id = ts.id
            WHERE te.employee_id = ? AND te.completion_status = 'completed'
        ");
        $stmt->execute([$employee_id]);
        $summary['completed_trainings_count'] = $stmt->fetch()['count'];
        
        return $summary;
    }
    
    // ==============================================
    // CERTIFICATIONS MANAGEMENT
    // ==============================================
    
    // Get all certifications
    public function getAllCertifications($status = 'active') {
        $sql = "SELECT * FROM certifications_catalog";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get employee certifications
    public function getEmployeeCertifications($employee_id) {
        $stmt = $this->db->prepare("
            SELECT ec.*, cc.name as certification_name, cc.description as certification_description,
                   cc.issuing_body, cc.validity_period_months, cc.renewal_required,
                   verifier.first_name as verifier_first_name, verifier.last_name as verifier_last_name
            FROM employee_certifications ec
            JOIN certifications_catalog cc ON ec.certification_id = cc.id
            LEFT JOIN users verifier ON ec.verified_by = verifier.id
            WHERE ec.employee_id = ? AND ec.status = 'active'
            ORDER BY ec.issue_date DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // ==============================================
    // LEARNING PATHS MANAGEMENT
    // ==============================================
    
    // Get all learning paths
    public function getAllLearningPaths($status = 'active') {
        $sql = "
            SELECT lp.*, creator.first_name as creator_first_name, creator.last_name as creator_last_name,
                   COUNT(lpm.module_id) as module_count
            FROM learning_paths lp
            JOIN users creator ON lp.created_by = creator.id
            LEFT JOIN learning_path_modules lpm ON lp.id = lpm.path_id
        ";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE lp.status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY lp.id ORDER BY lp.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get employee learning paths
    public function getEmployeeLearningPaths($employee_id) {
        $stmt = $this->db->prepare("
            SELECT elp.*, lp.name as path_name, lp.description as path_description,
                   lp.estimated_duration_days, lp.target_role,
                   assigner.first_name as assigner_first_name, assigner.last_name as assigner_last_name
            FROM employee_learning_paths elp
            JOIN learning_paths lp ON elp.path_id = lp.id
            LEFT JOIN users assigner ON elp.assigned_by = assigner.id
            WHERE elp.employee_id = ?
            ORDER BY elp.assigned_date DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
}

