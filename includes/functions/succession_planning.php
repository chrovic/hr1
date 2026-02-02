<?php
// Succession Planning Management System
class SuccessionPlanning {
    private $db;
    private $notificationManager;
    private $successionCandidateSchema;
    
    public function __construct() {
        $this->db = getDB();
        require_once 'notification_manager.php';
        $this->notificationManager = new NotificationManager();
    }

    private function getTableColumns($tableName) {
        try {
            $stmt = $this->db->prepare("
                SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
            ");
            $stmt->execute([$tableName]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }

    private function tableExists($tableName) {
        try {
            $stmt = $this->db->prepare("
                SELECT 1
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = ?
                LIMIT 1
            ");
            $stmt->execute([$tableName]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function ensureSuccessionAssessmentsTable() {
        if ($this->tableExists('succession_assessments')) {
            return true;
        }
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS succession_assessments (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    candidate_id INT NOT NULL,
                    assessor_id INT NOT NULL,
                    assessment_type ENUM('initial', 'progress', 'final') NOT NULL,
                    technical_readiness_score INT,
                    leadership_readiness_score INT,
                    cultural_fit_score INT,
                    overall_readiness_score INT,
                    strengths TEXT,
                    development_areas TEXT,
                    recommendations TEXT,
                    assessment_date DATE NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (candidate_id) REFERENCES succession_candidates(id) ON DELETE CASCADE,
                    FOREIGN KEY (assessor_id) REFERENCES users(id)
                )
            ");
            return true;
        } catch (PDOException $e) {
            error_log("Error creating succession_assessments table: " . $e->getMessage());
            return false;
        }
    }

    private function getSuccessionCandidateSchema() {
        if ($this->successionCandidateSchema !== null) {
            return $this->successionCandidateSchema;
        }

        $columns = $this->getTableColumns('succession_candidates');
        $this->successionCandidateSchema = [
            'role' => in_array('role_id', $columns, true) ? 'role_id' : 'position_id',
            'employee' => in_array('employee_id', $columns, true) ? 'employee_id' : 'candidate_id',
            'assigner' => in_array('assigned_by', $columns, true) ? 'assigned_by' : 'created_by',
            'assessment_date' => in_array('assessment_date', $columns, true),
            'next_review_date' => in_array('next_review_date', $columns, true),
            'development_plan' => in_array('development_plan', $columns, true),
            'notes' => in_array('notes', $columns, true)
        ];

        return $this->successionCandidateSchema;
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
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $stmt = $this->db->prepare("
                SELECT cr.*, u.first_name, u.last_name, u.position as incumbent_position,
                       COUNT(sc.id) as candidate_count,
                       COUNT(CASE WHEN sc.readiness_level = 'ready_now' THEN 1 END) as ready_now_count
                FROM critical_positions cr
                LEFT JOIN users u ON cr.current_incumbent_id = u.id
                LEFT JOIN succession_candidates sc ON cr.id = sc.{$roleCol}
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
                // Auto-assign readiness level via AI right after candidate is created
                $autoReadiness = $this->autoAssignReadinessLevel($candidateId, $candidateData);
                if ($autoReadiness) {
                    $this->updateCandidateReadinessLevel($candidateId, $autoReadiness);
                }
                // Auto-create an initial AI-assisted assessment record
                $this->createInitialAssessment($candidateId, $candidateData);
                
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

    private function createInitialAssessment($candidateId, $candidateData) {
        $assessmentData = [
            'candidate_id' => $candidateId,
            'assessor_id' => $candidateData['assigned_by'],
            'assessment_type' => 'initial',
            'technical_readiness_score' => null,
            'leadership_readiness_score' => null,
            'cultural_fit_score' => null,
            'overall_readiness_score' => null,
            'strengths' => $candidateData['notes'] ?? null,
            'development_areas' => $candidateData['development_plan'] ?? null,
            'recommendations' => null,
            'assessment_date' => date('Y-m-d')
        ];

        try {
            $this->addAssessment($assessmentData);
        } catch (Exception $e) {
            error_log("Error creating initial assessment: " . $e->getMessage());
        }
    }

    private function autoAssignReadinessLevel($candidateId, $candidateData) {
        require_once 'ai_integration.php';
        $ai = new AIIntegration();
        $context = $this->getCandidateAIContext($candidateId);
        $assessmentData = [
            'strengths' => $candidateData['notes'] ?? '',
            'development_areas' => $candidateData['development_plan'] ?? '',
            'recommendations' => ''
        ];
        $aiResult = $ai->evaluateSuccessionReadiness($context, $assessmentData);
        return $aiResult['readiness_level'] ?? null;
    }

    private function updateCandidateReadinessLevel($candidateId, $readinessLevel) {
        try {
            $stmt = $this->db->prepare("
                UPDATE succession_candidates
                SET readiness_level = ?
                WHERE id = ?
            ");
            $stmt->execute([$readinessLevel, $candidateId]);
        } catch (PDOException $e) {
            error_log("Error updating candidate readiness: " . $e->getMessage());
        }
    }
    
    // Get role candidates
    public function getRoleCandidates($roleId) {
        try {
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $employeeCol = $schema['employee'];
            $assignerCol = $schema['assigner'];
            $assessmentDateSelect = $schema['assessment_date'] ? 'sc.assessment_date' : 'NULL as assessment_date';
            $nextReviewSelect = $schema['next_review_date'] ? 'sc.next_review_date' : 'NULL as next_review_date';
            $developmentPlanSelect = $schema['development_plan'] ? 'sc.development_plan' : 'NULL as development_plan';
            $notesSelect = $schema['notes'] ? 'sc.notes' : 'NULL as notes';

            $stmt = $this->db->prepare("
                SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                       {$assessmentDateSelect}, {$nextReviewSelect}, {$developmentPlanSelect}, {$notesSelect},
                       assigner.first_name as assigned_by_first_name, assigner.last_name as assigned_by_last_name
                FROM succession_candidates sc
                JOIN users u ON sc.{$employeeCol} = u.id
                JOIN users assigner ON sc.{$assignerCol} = assigner.id
                WHERE sc.{$roleCol} = ?
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
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $employeeCol = $schema['employee'];
            $assignerCol = $schema['assigner'];
            $assessmentDateSelect = $schema['assessment_date'] ? 'sc.assessment_date' : 'NULL as assessment_date';
            $nextReviewSelect = $schema['next_review_date'] ? 'sc.next_review_date' : 'NULL as next_review_date';
            $developmentPlanSelect = $schema['development_plan'] ? 'sc.development_plan' : 'NULL as development_plan';
            $notesSelect = $schema['notes'] ? 'sc.notes' : 'NULL as notes';

            $stmt = $this->db->prepare("
                SELECT sc.*, sc.{$roleCol} as role_id, sc.{$employeeCol} as employee_id, sc.{$assignerCol} as assigned_by,
                       u.first_name, u.last_name, u.position, u.department,
                       {$assessmentDateSelect}, {$nextReviewSelect}, {$developmentPlanSelect}, {$notesSelect},
                       cr.position_title, cr.department as role_department,
                       assigner.first_name as assigned_by_first_name, assigner.last_name as assigned_by_last_name
                FROM succession_candidates sc
                JOIN users u ON sc.{$employeeCol} = u.id
                JOIN critical_positions cr ON sc.{$roleCol} = cr.id
                JOIN users assigner ON sc.{$assignerCol} = assigner.id
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

    public function getPlanRoleId($planId) {
        try {
            $stmt = $this->db->prepare("SELECT role_id FROM succession_plans WHERE id = ?");
            $stmt->execute([$planId]);
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            error_log("Error getting plan role: " . $e->getMessage());
            return null;
        }
    }

    public function getCandidateRoleId($candidateId) {
        try {
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $stmt = $this->db->prepare("SELECT {$roleCol} FROM succession_candidates WHERE id = ?");
            $stmt->execute([$candidateId]);
            return $stmt->fetchColumn() ?: null;
        } catch (PDOException $e) {
            error_log("Error getting candidate role: " . $e->getMessage());
            return null;
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

    // Update succession plan
    public function updateSuccessionPlan($planId, $updateData) {
        try {
            $stmt = $this->db->prepare("
                UPDATE succession_plans SET
                    plan_name = ?, role_id = ?, status = ?, start_date = ?, end_date = ?,
                    objectives = ?, success_metrics = ?, updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([
                $updateData['plan_name'],
                $updateData['role_id'],
                $updateData['status'],
                $updateData['start_date'],
                $updateData['end_date'],
                $updateData['objectives'],
                $updateData['success_metrics'],
                $planId
            ]);
        } catch (PDOException $e) {
            error_log("Error updating succession plan: " . $e->getMessage());
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
                JOIN critical_positions cr ON sp.role_id = cr.id
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

    // Get succession plan by ID
    public function getSuccessionPlan($planId) {
        try {
            $stmt = $this->db->prepare("
                SELECT sp.*, cr.position_title, cr.department,
                       creator.first_name as created_by_first_name, creator.last_name as created_by_last_name
                FROM succession_plans sp
                JOIN critical_positions cr ON sp.role_id = cr.id
                JOIN users creator ON sp.created_by = creator.id
                WHERE sp.id = ?
            ");
            $stmt->execute([$planId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting succession plan: " . $e->getMessage());
            return null;
        }
    }

    // Add candidate to succession plan
    public function addPlanCandidate($planId, $candidateId, $priorityOrder = 1, $targetReadinessDate = null, $developmentFocus = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO succession_plan_candidates (plan_id, candidate_id, priority_order, target_readiness_date, development_focus)
                VALUES (?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $planId,
                $candidateId,
                $priorityOrder,
                $targetReadinessDate,
                $developmentFocus
            ]);
        } catch (PDOException $e) {
            error_log("Error adding plan candidate: " . $e->getMessage());
            return false;
        }
    }

    // Remove candidate from succession plan
    public function removePlanCandidate($planCandidateId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM succession_plan_candidates WHERE id = ?");
            return $stmt->execute([$planCandidateId]);
        } catch (PDOException $e) {
            error_log("Error removing plan candidate: " . $e->getMessage());
            return false;
        }
    }

    // Get candidates assigned to a succession plan
    public function getPlanCandidates($planId) {
        try {
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $employeeCol = $schema['employee'];
            $assessmentDateSelect = $schema['assessment_date'] ? 'sc.assessment_date' : 'NULL as assessment_date';
            $nextReviewSelect = $schema['next_review_date'] ? 'sc.next_review_date' : 'NULL as next_review_date';

            $stmt = $this->db->prepare("
                SELECT spc.*, sc.readiness_level, {$assessmentDateSelect}, {$nextReviewSelect},
                       u.first_name, u.last_name, u.position, u.department,
                       cr.position_title as role_title
                FROM succession_plan_candidates spc
                JOIN succession_candidates sc ON spc.candidate_id = sc.id
                JOIN users u ON sc.{$employeeCol} = u.id
                JOIN critical_positions cr ON sc.{$roleCol} = cr.id
                WHERE spc.plan_id = ?
                ORDER BY spc.priority_order ASC, u.last_name ASC
            ");
            $stmt->execute([$planId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error getting plan candidates: " . $e->getMessage());
            return [];
        }
    }

    // Get candidate details with role and assigner
    public function getCandidateDetails($candidateId) {
        try {
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $employeeCol = $schema['employee'];
            $assignerCol = $schema['assigner'];
            $assessmentDateSelect = $schema['assessment_date'] ? 'sc.assessment_date' : 'NULL as assessment_date';
            $nextReviewSelect = $schema['next_review_date'] ? 'sc.next_review_date' : 'NULL as next_review_date';
            $developmentPlanSelect = $schema['development_plan'] ? 'sc.development_plan' : 'NULL as development_plan';
            $notesSelect = $schema['notes'] ? 'sc.notes' : 'NULL as notes';

            $stmt = $this->db->prepare("
                SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                       {$assessmentDateSelect}, {$nextReviewSelect}, {$developmentPlanSelect}, {$notesSelect},
                       cr.position_title, cr.department as role_department, cr.risk_level,
                       assigner.first_name as assigned_by_first_name, assigner.last_name as assigned_by_last_name
                FROM succession_candidates sc
                JOIN users u ON sc.{$employeeCol} = u.id
                JOIN critical_positions cr ON sc.{$roleCol} = cr.id
                JOIN users assigner ON sc.{$assignerCol} = assigner.id
                WHERE sc.id = ?
            ");
            $stmt->execute([$candidateId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error getting candidate details: " . $e->getMessage());
            return null;
        }
    }
    
    // Get succession pipeline for a role
    public function getSuccessionPipeline($roleId) {
        try {
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $employeeCol = $schema['employee'];
            $assessmentDateSelect = $schema['assessment_date'] ? 'sc.assessment_date' : 'NULL as assessment_date';
            $nextReviewSelect = $schema['next_review_date'] ? 'sc.next_review_date' : 'NULL as next_review_date';

            $assessmentsAvailable = $this->tableExists('succession_assessments');
            if ($assessmentsAvailable) {
                $stmt = $this->db->prepare("
                    SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                           {$assessmentDateSelect}, {$nextReviewSelect},
                           sa.overall_readiness_score, sa.assessment_date as last_assessment
                    FROM succession_candidates sc
                    JOIN users u ON sc.{$employeeCol} = u.id
                    LEFT JOIN succession_assessments sa ON sc.id = sa.candidate_id 
                        AND sa.assessment_date = (
                            SELECT MAX(sa2.assessment_date) 
                            FROM succession_assessments sa2 
                            WHERE sa2.candidate_id = sc.id
                        )
                    WHERE sc.{$roleCol} = ?
                    ORDER BY sc.readiness_level ASC, sa.overall_readiness_score DESC
                ");
            } else {
                $stmt = $this->db->prepare("
                    SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                           {$assessmentDateSelect}, {$nextReviewSelect},
                           NULL as overall_readiness_score, NULL as last_assessment
                    FROM succession_candidates sc
                    JOIN users u ON sc.{$employeeCol} = u.id
                    WHERE sc.{$roleCol} = ?
                    ORDER BY sc.readiness_level ASC
                ");
            }
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
            $schema = $this->getSuccessionCandidateSchema();
            $roleCol = $schema['role'];
            $employeeCol = $schema['employee'];
            $sql = "
                SELECT u.id, u.first_name, u.last_name, u.position, u.department, u.hire_date
                FROM users u
                WHERE u.status = 'active' AND u.role IN ('employee', 'hr_manager')
            ";
            
            $params = [];
            
            if ($roleId) {
                $sql .= " AND u.id NOT IN (
                    SELECT {$employeeCol} FROM succession_candidates WHERE {$roleCol} = ?
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
            if (!$this->ensureSuccessionAssessmentsTable()) {
                return false;
            }
            if (($assessmentData['overall_readiness_score'] ?? null) === null && $this->hasAssessmentText($assessmentData)) {
                require_once 'ai_integration.php';
                $ai = new AIIntegration();
                $context = $this->getCandidateAIContext($assessmentData['candidate_id']);
                $aiResult = $ai->evaluateSuccessionReadiness($context, $assessmentData);
                if (!empty($aiResult['overall_score'])) {
                    $assessmentData['overall_readiness_score'] = (int)$aiResult['overall_score'];
                }
                if (empty($assessmentData['recommendations']) && !empty($aiResult['summary'])) {
                    $assessmentData['recommendations'] = 'AI summary: ' . $aiResult['summary'];
                }
            }
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

    private function hasAssessmentText($assessmentData) {
        return !empty($assessmentData['strengths']) ||
            !empty($assessmentData['development_areas']) ||
            !empty($assessmentData['recommendations']);
    }

    private function getCandidateAIContext($candidateId) {
        $schema = $this->getSuccessionCandidateSchema();
        $roleCol = $schema['role'];
        $employeeCol = $schema['employee'];

        try {
            $stmt = $this->db->prepare("
                SELECT sc.*, u.first_name, u.last_name, u.position, u.department,
                       cr.position_title as role_title, cr.description as role_description
                FROM succession_candidates sc
                LEFT JOIN users u ON sc.{$employeeCol} = u.id
                LEFT JOIN critical_positions cr ON sc.{$roleCol} = cr.id
                WHERE sc.id = ?
                LIMIT 1
            ");
            $stmt->execute([$candidateId]);
            return $stmt->fetch() ?: [];
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Get candidate assessments
    public function getCandidateAssessments($candidateId) {
        try {
            if (!$this->tableExists('succession_assessments')) {
                return [];
            }
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
