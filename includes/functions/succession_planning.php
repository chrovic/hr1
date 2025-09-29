<?php
// Succession Planning Management System
class SuccessionPlanning {
    private $db;
    private $notificationManager;
    
    public function __construct() {
        $this->db = getDB();
        require_once 'notification_manager.php';
        $this->notificationManager = new NotificationManager();
    }
    
    // Create critical role
    public function createCriticalRole($roleData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO critical_positions (position_title, department, description, priority_level, risk_level, current_incumbent_id, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $roleData['position_title'],
                $roleData['department'],
                $roleData['description'],
                $roleData['priority_level'] ?? 'medium',
                $roleData['risk_level'],
                $roleData['current_incumbent_id'] ?? null,
                $roleData['created_by']
            ]);
            
            if ($result) {
                $roleId = $this->db->lastInsertId();
                
                // Get creator name for notification
                $creatorStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $creatorStmt->execute([$roleData['created_by']]);
                $creator = $creatorStmt->fetch();
                $creatorName = $creator ? $creator['first_name'] . ' ' . $creator['last_name'] : 'System';
                
                // Notify succession managers
                $this->notificationManager->notifySuccessionManagers(
                    'role_created',
                    [
                        'role_title' => $roleData['position_title'],
                        'department' => $roleData['department'],
                        'risk_level' => $roleData['risk_level'],
                        'created_by' => $creatorName
                    ],
                    $roleId,
                    'critical_role',
                    '?page=succession_planning&action=view_role&id=' . $roleId,
                    true
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error creating critical role: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all critical roles
    public function getAllCriticalRoles() {
        try {
            $stmt = $this->db->prepare("
                SELECT cr.*, u.first_name, u.last_name, u.position as incumbent_position,
                       COUNT(sc.id) as candidate_count,
                       COUNT(CASE WHEN sc.readiness_level = 'ready_now' THEN 1 END) as ready_now_count
                FROM critical_positions cr
                LEFT JOIN users u ON cr.current_incumbent_id = u.id
                LEFT JOIN succession_candidates sc ON cr.id = sc.role_id
                GROUP BY cr.id
                ORDER BY cr.risk_level DESC, cr.position_title ASC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting critical roles: " . $e->getMessage());
            return [];
        }
    }
    
    // Get critical role by ID
    public function getCriticalRole($roleId) {
        try {
            $stmt = $this->db->prepare("
                SELECT cr.*, u.first_name, u.last_name, u.position as incumbent_position,
                       creator.first_name as created_by_first_name, creator.last_name as created_by_last_name
                FROM critical_positions cr
                LEFT JOIN users u ON cr.current_incumbent_id = u.id
                LEFT JOIN users creator ON cr.created_by = creator.id
                WHERE cr.id = ?
            ");
            $stmt->execute([$roleId]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting critical role: " . $e->getMessage());
            return null;
        }
    }
    
    // Update critical role
    public function updateCriticalRole($roleId, $updateData) {
        try {
            $stmt = $this->db->prepare("
                UPDATE critical_positions SET 
                    position_title = ?, department = ?, description = ?, 
                    priority_level = ?, risk_level = ?, current_incumbent_id = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $updateData['position_title'],
                $updateData['department'],
                $updateData['description'],
                $updateData['priority_level'] ?? 'medium',
                $updateData['risk_level'],
                $updateData['current_incumbent_id'] ?? null,
                $roleId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating critical role: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete critical role
    public function deleteCriticalRole($roleId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM critical_positions WHERE id = ?");
            return $stmt->execute([$roleId]);
        } catch (PDOException $e) {
            error_log("Error deleting critical role: " . $e->getMessage());
            return false;
        }
    }
    
    // Assign succession candidate
    public function assignCandidate($candidateData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO succession_candidates (role_id, employee_id, readiness_level, development_plan, notes, assessment_date, next_review_date, assigned_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $candidateData['role_id'],
                $candidateData['employee_id'],
                $candidateData['readiness_level'],
                $candidateData['development_plan'],
                $candidateData['notes'],
                $candidateData['assessment_date'],
                $candidateData['next_review_date'],
                $candidateData['assigned_by']
            ]);
            
            if ($result) {
                $candidateId = $this->db->lastInsertId();
                
                // Get employee, role, and assigner names for notification
                $employeeStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $employeeStmt->execute([$candidateData['employee_id']]);
                $employee = $employeeStmt->fetch();
                $employeeName = $employee ? $employee['first_name'] . ' ' . $employee['last_name'] : 'Unknown Employee';
                
                $roleStmt = $this->db->prepare("SELECT position_title FROM critical_positions WHERE id = ?");
                $roleStmt->execute([$candidateData['role_id']]);
                $role = $roleStmt->fetch();
                $roleTitle = $role ? $role['position_title'] : 'Unknown Role';
                
                $assignerStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $assignerStmt->execute([$candidateData['assigned_by']]);
                $assigner = $assignerStmt->fetch();
                $assignerName = $assigner ? $assigner['first_name'] . ' ' . $assigner['last_name'] : 'System';
                
                // Notify succession managers
                $this->notificationManager->notifySuccessionManagers(
                    'candidate_assigned',
                    [
                        'employee_name' => $employeeName,
                        'role_title' => $roleTitle,
                        'readiness_level' => $candidateData['readiness_level'],
                        'assigned_by' => $assignerName
                    ],
                    $candidateId,
                    'succession_candidate',
                    '?page=succession_planning&action=view_candidates&role_id=' . $candidateData['role_id'],
                    true
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error assigning candidate: " . $e->getMessage());
            return false;
        }
    }
    
    // Get role candidates
    public function getRoleCandidates($roleId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                       assigner.first_name as assigned_by_first_name, assigner.last_name as assigned_by_last_name
                FROM succession_candidates sc
                JOIN users u ON sc.employee_id = u.id
                JOIN users assigner ON sc.assigned_by = assigner.id
                WHERE sc.role_id = ?
                ORDER BY sc.readiness_level ASC, u.last_name ASC
            ");
            $stmt->execute([$roleId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting role candidates: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all succession candidates
    public function getAllCandidates() {
        try {
            $stmt = $this->db->prepare("
                SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                       cr.position_title, cr.department as role_department,
                       assigner.first_name as assigned_by_first_name, assigner.last_name as assigned_by_last_name
                FROM succession_candidates sc
                JOIN users u ON sc.employee_id = u.id
                JOIN critical_positions cr ON sc.role_id = cr.id
                JOIN users assigner ON sc.assigned_by = assigner.id
                ORDER BY cr.position_title ASC, sc.readiness_level ASC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting all candidates: " . $e->getMessage());
            return [];
        }
    }
    
    // Update candidate readiness
    public function updateCandidateReadiness($candidateId, $updateData) {
        try {
            $stmt = $this->db->prepare("
                UPDATE succession_candidates SET 
                    readiness_level = ?, development_plan = ?, notes = ?, 
                    assessment_date = ?, next_review_date = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            return $stmt->execute([
                $updateData['readiness_level'],
                $updateData['development_plan'],
                $updateData['notes'],
                $updateData['assessment_date'],
                $updateData['next_review_date'],
                $candidateId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating candidate readiness: " . $e->getMessage());
            return false;
        }
    }
    
    // Remove candidate from succession
    public function removeCandidate($candidateId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM succession_candidates WHERE id = ?");
            return $stmt->execute([$candidateId]);
        } catch (PDOException $e) {
            error_log("Error removing candidate: " . $e->getMessage());
            return false;
        }
    }
    
    // Create succession plan
    public function createSuccessionPlan($planData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO succession_plans (role_id, plan_name, status, start_date, end_date, objectives, success_metrics, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $planData['role_id'],
                $planData['plan_name'],
                $planData['status'],
                $planData['start_date'],
                $planData['end_date'],
                $planData['objectives'],
                $planData['success_metrics'],
                $planData['created_by']
            ]);
            
            if ($result) {
                $planId = $this->db->lastInsertId();
                
                // Get creator and role names for notification
                $creatorStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $creatorStmt->execute([$planData['created_by']]);
                $creator = $creatorStmt->fetch();
                $creatorName = $creator ? $creator['first_name'] . ' ' . $creator['last_name'] : 'System';
                
                $roleStmt = $this->db->prepare("SELECT position_title FROM critical_positions WHERE id = ?");
                $roleStmt->execute([$planData['role_id']]);
                $role = $roleStmt->fetch();
                $roleTitle = $role ? $role['position_title'] : 'Unknown Role';
                
                // Notify succession managers
                $this->notificationManager->notifySuccessionManagers(
                    'plan_created',
                    [
                        'plan_name' => $planData['plan_name'],
                        'role_title' => $roleTitle,
                        'status' => $planData['status'],
                        'created_by' => $creatorName
                    ],
                    $planId,
                    'succession_plan',
                    '?page=succession_planning&action=view_plan&id=' . $planId,
                    true
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error creating succession plan: " . $e->getMessage());
            return false;
        }
    }
    
    // Get succession plans
    public function getSuccessionPlans() {
        try {
            $stmt = $this->db->prepare("
                SELECT sp.*, cr.position_title, cr.department,
                       creator.first_name as created_by_first_name, creator.last_name as created_by_last_name,
                       COUNT(spc.id) as candidate_count
                FROM succession_plans sp
                JOIN critical_roles cr ON sp.role_id = cr.id
                JOIN users creator ON sp.created_by = creator.id
                LEFT JOIN succession_plan_candidates spc ON sp.id = spc.plan_id
                GROUP BY sp.id
                ORDER BY sp.created_at DESC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting succession plans: " . $e->getMessage());
            return [];
        }
    }
    
    // Get succession pipeline for a role
    public function getSuccessionPipeline($roleId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                       sa.overall_readiness_score, sa.assessment_date as last_assessment
                FROM succession_candidates sc
                JOIN users u ON sc.employee_id = u.id
                LEFT JOIN succession_assessments sa ON sc.id = sa.candidate_id 
                    AND sa.assessment_date = (
                        SELECT MAX(sa2.assessment_date) 
                        FROM succession_assessments sa2 
                        WHERE sa2.candidate_id = sc.id
                    )
                WHERE sc.role_id = ?
                ORDER BY sc.readiness_level ASC, sa.overall_readiness_score DESC
            ");
            $stmt->execute([$roleId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting succession pipeline: " . $e->getMessage());
            return [];
        }
    }
    
    // Generate succession report
    public function generateSuccessionReport($filters = []) {
        try {
            $sql = "
                SELECT cr.*, 
                       COUNT(sc.id) as total_candidates,
                       COUNT(CASE WHEN sc.readiness_level = 'ready_now' THEN 1 END) as ready_now,
                       COUNT(CASE WHEN sc.readiness_level = 'ready_soon' THEN 1 END) as ready_soon,
                       COUNT(CASE WHEN sc.readiness_level = 'development_needed' THEN 1 END) as development_needed,
                       u.first_name as incumbent_first_name, u.last_name as incumbent_last_name
                FROM critical_positions cr
                LEFT JOIN succession_candidates sc ON cr.id = sc.role_id
                LEFT JOIN users u ON cr.current_incumbent_id = u.id
            ";
            
            $params = [];
            $whereClauses = [];
            
            if (!empty($filters['department'])) {
                $whereClauses[] = "cr.department = ?";
                $params[] = $filters['department'];
            }
            
            if (!empty($filters['risk_level'])) {
                $whereClauses[] = "cr.risk_level = ?";
                $params[] = $filters['risk_level'];
            }
            
            if (!empty($whereClauses)) {
                $sql .= " WHERE " . implode(" AND ", $whereClauses);
            }
            
            $sql .= " GROUP BY cr.id ORDER BY cr.risk_level DESC, cr.position_title ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error generating succession report: " . $e->getMessage());
            return [];
        }
    }
    
    // Get employees available for succession
    public function getAvailableEmployees($roleId = null) {
        try {
            $sql = "
                SELECT u.id, u.first_name, u.last_name, u.position, u.department, u.hire_date
                FROM users u
                WHERE u.status = 'active' AND u.role IN ('employee', 'hr_manager')
            ";
            
            $params = [];
            
            if ($roleId) {
                $sql .= " AND u.id NOT IN (
                    SELECT employee_id FROM succession_candidates WHERE role_id = ?
                )";
                $params[] = $roleId;
            }
            
            $sql .= " ORDER BY u.last_name, u.first_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting available employees: " . $e->getMessage());
            return [];
        }
    }
    
    // Add succession assessment
    public function addAssessment($assessmentData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO succession_assessments (candidate_id, assessor_id, assessment_type, technical_readiness_score, leadership_readiness_score, cultural_fit_score, overall_readiness_score, strengths, development_areas, recommendations, assessment_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            return $stmt->execute([
                $assessmentData['candidate_id'],
                $assessmentData['assessor_id'],
                $assessmentData['assessment_type'],
                $assessmentData['technical_readiness_score'],
                $assessmentData['leadership_readiness_score'],
                $assessmentData['cultural_fit_score'],
                $assessmentData['overall_readiness_score'],
                $assessmentData['strengths'],
                $assessmentData['development_areas'],
                $assessmentData['recommendations'],
                $assessmentData['assessment_date']
            ]);
        } catch (PDOException $e) {
            error_log("Error adding assessment: " . $e->getMessage());
            return false;
        }
    }
    
    // Get candidate assessments
    public function getCandidateAssessments($candidateId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sa.*, u.first_name as assessor_first_name, u.last_name as assessor_last_name
                FROM succession_assessments sa
                JOIN users u ON sa.assessor_id = u.id
                WHERE sa.candidate_id = ?
                ORDER BY sa.assessment_date DESC
            ");
            $stmt->execute([$candidateId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting candidate assessments: " . $e->getMessage());
            return [];
        }
    }
}
?>

