<?php
// Competency Management System
class CompetencyManager {
    private $db;
    private $notificationManager;
    
    public function __construct() {
        $this->db = getDB();
        require_once 'notification_manager.php';
        $this->notificationManager = new NotificationManager();
    }
    
    // Helper method to map database assessment method values to form values
    public function mapAssessmentMethodToForm($dbValue) {
        $formValueMap = [
            'self_assessment' => 'self',
            'manager_review' => 'manager',
            'peer_review' => 'peer',
            '360_feedback' => '360'
        ];
        
        return $formValueMap[$dbValue] ?? 'self';
    }
    
    // Helper method to map form assessment method values to database values
    private function mapAssessmentMethodToDb($formValue) {
        $dbValueMap = [
            'self' => 'self_assessment',
            'manager' => 'manager_review',
            'peer' => 'peer_review',
            '360' => '360_feedback',
            'combined' => 'self_assessment'
        ];
        
        return $dbValueMap[$formValue] ?? 'self_assessment';
    }
    
    // Create competency model
    public function createModel($modelData) {
        $assessmentMethod = $this->mapAssessmentMethodToDb($modelData['assessment_method']);
        
        $stmt = $this->db->prepare("INSERT INTO competency_models (name, description, category, target_roles, assessment_method, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $modelData['name'],
            $modelData['description'],
            $modelData['category'],
            json_encode($modelData['target_roles']),
            $assessmentMethod,
            $modelData['created_by']
        ]);
        
        if ($result) {
            $modelId = $this->db->lastInsertId();
            
            // Get creator name for notification
            $creatorStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $creatorStmt->execute([$modelData['created_by']]);
            $creator = $creatorStmt->fetch();
            $creatorName = $creator ? $creator['first_name'] . ' ' . $creator['last_name'] : 'Unknown';
            
            // Notify HR managers and admins
            $this->notificationManager->notifyHRManagers(
                'model_created',
                [
                    'model_name' => $modelData['name'],
                    'created_by' => $creatorName,
                    'category' => $modelData['category']
                ],
                $modelId,
                'model',
                '?page=competency_models&action=view&id=' . $modelId,
                true
            );
        }
        
        return $result;
    }
    
    // Get all competency models
    public function getAllModels() {
        try {
            // Check if status column exists
            $checkStmt = $this->db->prepare("SHOW COLUMNS FROM competency_models LIKE 'status'");
            $checkStmt->execute();
            $hasStatusColumn = $checkStmt->rowCount() > 0;
            
            $whereClause = $hasStatusColumn ? "WHERE cm.status != 'archived'" : "";
            
            $stmt = $this->db->prepare("
                SELECT cm.*, u.first_name, u.last_name,
                       COUNT(DISTINCT c.id) as competency_count,
                       COUNT(DISTINCT e.id) as evaluation_count
                FROM competency_models cm
                LEFT JOIN users u ON cm.created_by = u.id
                LEFT JOIN competencies c ON cm.id = c.model_id
                LEFT JOIN evaluations e ON cm.id = e.model_id
                $whereClause
                GROUP BY cm.id
                ORDER BY cm.created_at DESC
            ");
            $stmt->execute();
            
            $models = $stmt->fetchAll();
            
            // Decode JSON fields and map assessment method for display
            foreach ($models as &$model) {
                $model['target_roles'] = $model['target_roles'] ? json_decode($model['target_roles'], true) ?: [] : [];
                $model['assessment_method_form'] = $this->mapAssessmentMethodToForm($model['assessment_method']);
                // Set default status if column doesn't exist
                if (!$hasStatusColumn) {
                    $model['status'] = 'active';
                }
            }
            
            return $models;
        } catch (PDOException $e) {
            error_log("Error getting competency models: " . $e->getMessage());
            return [];
        }
    }
    
    // Add competency to model
    public function addCompetency($competencyData) {
        $stmt = $this->db->prepare("INSERT INTO competencies (model_id, name, description, weight, max_score) VALUES (?, ?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $competencyData['model_id'],
            $competencyData['name'],
            $competencyData['description'],
            $competencyData['weight'],
            $competencyData['max_score']
        ]);
        
        if ($result) {
            $competencyId = $this->db->lastInsertId();
            
            // Get model name for notification
            $modelStmt = $this->db->prepare("SELECT name FROM competency_models WHERE id = ?");
            $modelStmt->execute([$competencyData['model_id']]);
            $model = $modelStmt->fetch();
            $modelName = $model ? $model['name'] : 'Unknown Model';
            
            // Notify HR managers and admins
            $this->notificationManager->notifyHRManagers(
                'competency_added',
                [
                    'competency_name' => $competencyData['name'],
                    'model_name' => $modelName
                ],
                $competencyId,
                'competency',
                '?page=competency_models&action=view&id=' . $competencyData['model_id'],
                false
            );
        }
        
        return $result;
    }
    
    // Update competency model
    public function updateModel($modelId, $updateData, $updatedBy = null) {
        try {
            $assessmentMethod = $this->mapAssessmentMethodToDb($updateData['assessment_method']);
            
            // Check if status column exists
            $checkStmt = $this->db->prepare("SHOW COLUMNS FROM competency_models LIKE 'status'");
            $checkStmt->execute();
            $hasStatusColumn = $checkStmt->rowCount() > 0;
            
            // Check if updated_at column exists
            $checkStmt2 = $this->db->prepare("SHOW COLUMNS FROM competency_models LIKE 'updated_at'");
            $checkStmt2->execute();
            $hasUpdatedAtColumn = $checkStmt2->rowCount() > 0;
            
            $updateFields = "name = ?, description = ?, category = ?, target_roles = ?, assessment_method = ?";
            $params = [
                $updateData['name'],
                $updateData['description'],
                $updateData['category'],
                json_encode($updateData['target_roles']),
                $assessmentMethod
            ];
            
            if ($hasStatusColumn) {
                $updateFields .= ", status = ?";
                $params[] = $updateData['status'] ?? 'active';
            }
            
            if ($hasUpdatedAtColumn) {
                $updateFields .= ", updated_at = NOW()";
            }
            
            $params[] = $modelId;
            
            $stmt = $this->db->prepare("UPDATE competency_models SET $updateFields WHERE id = ?");
            
            $result = $stmt->execute($params);
            
            if ($result) {
                // Get model name for notification
                $modelStmt = $this->db->prepare("SELECT name FROM competency_models WHERE id = ?");
                $modelStmt->execute([$modelId]);
                $model = $modelStmt->fetch();
                $modelName = $model ? $model['name'] : 'Unknown Model';
                
                // Use provided user info or get from session
                if (!$updatedBy) {
                    $userStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                    $userStmt->execute([$_SESSION['user_id'] ?? 1]);
                    $user = $userStmt->fetch();
                    $updatedBy = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'System';
                }
                
                // Notify HR managers and admins
                $this->notificationManager->notifyHRManagers(
                    'model_updated',
                    [
                        'model_name' => $modelName,
                        'updated_by' => $updatedBy
                    ],
                    $modelId,
                    'model',
                    '?page=competency_models&action=view&id=' . $modelId,
                    false
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating competency model: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete competency model (soft delete)
    public function deleteModel($modelId) {
        try {
            // Get model name before deletion for notification
            $modelStmt = $this->db->prepare("SELECT name FROM competency_models WHERE id = ?");
            $modelStmt->execute([$modelId]);
            $model = $modelStmt->fetch();
            $modelName = $model ? $model['name'] : 'Unknown Model';
            
            // Check if status column exists
            $checkStmt = $this->db->prepare("SHOW COLUMNS FROM competency_models LIKE 'status'");
            $checkStmt->execute();
            $hasStatusColumn = $checkStmt->rowCount() > 0;
            
            // Check if updated_at column exists
            $checkStmt2 = $this->db->prepare("SHOW COLUMNS FROM competency_models LIKE 'updated_at'");
            $checkStmt2->execute();
            $hasUpdatedAtColumn = $checkStmt2->rowCount() > 0;
            
            if ($hasStatusColumn) {
                $updateFields = "status = 'archived'";
                if ($hasUpdatedAtColumn) {
                    $updateFields .= ", updated_at = NOW()";
                }
                $stmt = $this->db->prepare("UPDATE competency_models SET $updateFields WHERE id = ?");
            } else {
                // If no status column, do a hard delete
                $stmt = $this->db->prepare("DELETE FROM competency_models WHERE id = ?");
            }
            
            $result = $stmt->execute([$modelId]);
            
            if ($result) {
                // Notify HR managers and admins
                $this->notificationManager->notifyHRManagers(
                    $hasStatusColumn ? 'model_archived' : 'model_deleted',
                    [
                        'model_name' => $modelName
                    ],
                    $modelId,
                    'model',
                    '?page=competency_models',
                    true
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting competency model: " . $e->getMessage());
            return false;
        }
    }
    
    // Update competency
    public function updateCompetency($competencyId, $updateData) {
        try {
            // Get competency and model info for notification
            $competencyStmt = $this->db->prepare("
                SELECT c.name as competency_name, cm.name as model_name, c.model_id 
                FROM competencies c 
                JOIN competency_models cm ON c.model_id = cm.id 
                WHERE c.id = ?
            ");
            $competencyStmt->execute([$competencyId]);
            $competency = $competencyStmt->fetch();
            
            // Check if updated_at column exists
            $checkStmt = $this->db->prepare("SHOW COLUMNS FROM competencies LIKE 'updated_at'");
            $checkStmt->execute();
            $hasUpdatedAtColumn = $checkStmt->rowCount() > 0;
            
            $updateFields = "name = ?, description = ?, weight = ?, max_score = ?";
            if ($hasUpdatedAtColumn) {
                $updateFields .= ", updated_at = NOW()";
            }
            
            $stmt = $this->db->prepare("UPDATE competencies SET $updateFields WHERE id = ?");
            
            $result = $stmt->execute([
                $updateData['name'],
                $updateData['description'],
                $updateData['weight'],
                $updateData['max_score'],
                $competencyId
            ]);
            
            if ($result && $competency) {
                // Notify HR managers and admins
                $this->notificationManager->notifyHRManagers(
                    'competency_updated',
                    [
                        'competency_name' => $updateData['name'],
                        'model_name' => $competency['model_name']
                    ],
                    $competencyId,
                    'competency',
                    '?page=competency_models&action=view&id=' . $competency['model_id'],
                    false
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating competency: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete competency
    public function deleteCompetency($competencyId) {
        // Get competency and model info before deletion for notification
        $competencyStmt = $this->db->prepare("
            SELECT c.name as competency_name, cm.name as model_name, c.model_id 
            FROM competencies c 
            JOIN competency_models cm ON c.model_id = cm.id 
            WHERE c.id = ?
        ");
        $competencyStmt->execute([$competencyId]);
        $competency = $competencyStmt->fetch();
        
        $stmt = $this->db->prepare("DELETE FROM competencies WHERE id = ?");
        $result = $stmt->execute([$competencyId]);
        
        if ($result && $competency) {
            // Notify HR managers and admins
            $this->notificationManager->notifyHRManagers(
                'competency_deleted',
                [
                    'competency_name' => $competency['competency_name'],
                    'model_name' => $competency['model_name']
                ],
                $competencyId,
                'competency',
                '?page=competency_models&action=view&id=' . $competency['model_id'],
                true
            );
        }
        
        return $result;
    }
    
    // Get competencies for a model
    public function getModelCompetencies($model_id) {
        $stmt = $this->db->prepare("SELECT * FROM competencies WHERE model_id = ? ORDER BY id");
        $stmt->execute([$model_id]);
        
        return $stmt->fetchAll();
    }
    
    // Create evaluation cycle
    public function createEvaluationCycle($cycleData) {
        try {
            $stmt = $this->db->prepare("INSERT INTO evaluation_cycles (name, type, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?)");
            
            $result = $stmt->execute([
                $cycleData['name'],
                $cycleData['type'],
                $cycleData['start_date'],
                $cycleData['end_date'],
                $cycleData['created_by']
            ]);
            
            if ($result) {
                $cycleId = $this->db->lastInsertId();
                
                // Get creator name for notification
                $creatorStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                $creatorStmt->execute([$cycleData['created_by']]);
                $creator = $creatorStmt->fetch();
                $creatorName = $creator ? $creator['first_name'] . ' ' . $creator['last_name'] : 'Unknown';
                
                // Notify HR managers and admins
                $this->notificationManager->notifyHRManagers(
                    'cycle_created',
                    [
                        'cycle_name' => $cycleData['name'],
                        'created_by' => $creatorName,
                        'type' => $cycleData['type']
                    ],
                    $cycleId,
                    'cycle',
                    '?page=evaluation_cycles&action=view&id=' . $cycleId,
                    true
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Evaluation cycle creation error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all evaluation cycles
    public function getAllCycles() {
        $stmt = $this->db->prepare("
            SELECT ec.*, u.first_name, u.last_name,
                   COUNT(e.id) as evaluation_count,
                   COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count
            FROM evaluation_cycles ec
            LEFT JOIN users u ON ec.created_by = u.id
            LEFT JOIN evaluations e ON ec.id = e.cycle_id
            GROUP BY ec.id
            ORDER BY ec.created_at DESC
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Assign evaluation
    public function assignEvaluation($evaluationData) {
        $stmt = $this->db->prepare("INSERT INTO evaluations (cycle_id, employee_id, evaluator_id, model_id) VALUES (?, ?, ?, ?)");
        
        $result = $stmt->execute([
            $evaluationData['cycle_id'],
            $evaluationData['employee_id'],
            $evaluationData['evaluator_id'],
            $evaluationData['model_id']
        ]);
        
        if ($result) {
            $evaluationId = $this->db->lastInsertId();
            
            // Get employee and cycle names for notification
            $employeeStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $employeeStmt->execute([$evaluationData['employee_id']]);
            $employee = $employeeStmt->fetch();
            $employeeName = $employee ? $employee['first_name'] . ' ' . $employee['last_name'] : 'Unknown Employee';
            
            $cycleStmt = $this->db->prepare("SELECT name FROM evaluation_cycles WHERE id = ?");
            $cycleStmt->execute([$evaluationData['cycle_id']]);
            $cycle = $cycleStmt->fetch();
            $cycleName = $cycle ? $cycle['name'] : 'Unknown Cycle';
            
            // Notify the evaluator
            $this->notificationManager->notifyUser(
                $evaluationData['evaluator_id'],
                'evaluation_assigned',
                [
                    'employee_name' => $employeeName,
                    'cycle_name' => $cycleName
                ],
                $evaluationId,
                'evaluation',
                '?page=evaluation_form&id=' . $evaluationId,
                true
            );
        }
        
        return $result;
    }
    
    // Get evaluations for employee
    public function getEmployeeEvaluations($employee_id) {
        $stmt = $this->db->prepare("
            SELECT e.*, ec.name as cycle_name, ec.type as cycle_type,
                   ev.first_name as evaluator_first_name, ev.last_name as evaluator_last_name,
                   cm.name as model_name
            FROM evaluations e
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            JOIN users ev ON e.evaluator_id = ev.id
            JOIN competency_models cm ON e.model_id = cm.id
            WHERE e.employee_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get evaluations assigned to evaluator
    public function getEvaluatorAssignments($evaluator_id) {
        $stmt = $this->db->prepare("
            SELECT e.*, ec.name as cycle_name, ec.type as cycle_type,
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   cm.name as model_name
            FROM evaluations e
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            JOIN users emp ON e.employee_id = emp.id
            JOIN competency_models cm ON e.model_id = cm.id
            WHERE e.evaluator_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$evaluator_id]);
        
        return $stmt->fetchAll();
    }
    
    // Submit competency scores
    public function submitScores($evaluation_id, $scores) {
        $this->db->beginTransaction();
        
        try {
            // Get evaluation details for notification
            $evaluationStmt = $this->db->prepare("
                SELECT e.employee_id, e.evaluator_id, emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                       ev.first_name as evaluator_first_name, ev.last_name as evaluator_last_name
                FROM evaluations e
                JOIN users emp ON e.employee_id = emp.id
                JOIN users ev ON e.evaluator_id = ev.id
                WHERE e.id = ?
            ");
            $evaluationStmt->execute([$evaluation_id]);
            $evaluation = $evaluationStmt->fetch();
            
            // Delete existing scores
            $stmt = $this->db->prepare("DELETE FROM competency_scores WHERE evaluation_id = ?");
            $stmt->execute([$evaluation_id]);
            
            // Insert new scores
            $stmt = $this->db->prepare("INSERT INTO competency_scores (evaluation_id, competency_id, score, comments) VALUES (?, ?, ?, ?)");
            
            $total_score = 0;
            $total_weight = 0;
            
            foreach ($scores as $score) {
                $stmt->execute([
                    $evaluation_id,
                    $score['competency_id'],
                    $score['score'],
                    $score['comments']
                ]);
                
                // Calculate weighted score
                $competency_stmt = $this->db->prepare("SELECT weight FROM competencies WHERE id = ?");
                $competency_stmt->execute([$score['competency_id']]);
                $competency = $competency_stmt->fetch();
                
                $total_score += $score['score'] * $competency['weight'];
                $total_weight += $competency['weight'];
            }
            
            // Update evaluation with overall score
            $overall_score = $total_weight > 0 ? $total_score / $total_weight : 0;
            
            $stmt = $this->db->prepare("UPDATE evaluations SET overall_score = ?, status = 'completed', completed_at = NOW() WHERE id = ?");
            $stmt->execute([$overall_score, $evaluation_id]);
            
            $this->db->commit();
            
            // Send notifications after successful completion
            if ($evaluation) {
                $employeeName = $evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name'];
                $evaluatorName = $evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name'];
                
                // Notify HR managers about completed evaluation
                $this->notificationManager->notifyHRManagers(
                    'evaluation_completed',
                    [
                        'employee_name' => $employeeName,
                        'evaluator_name' => $evaluatorName
                    ],
                    $evaluation_id,
                    'evaluation',
                    '?page=evaluations&action=view&id=' . $evaluation_id,
                    false
                );
                
                // Notify the employee about completed evaluation
                $this->notificationManager->notifyUser(
                    $evaluation['employee_id'],
                    'score_submitted',
                    [
                        'employee_name' => $employeeName,
                        'evaluator_name' => $evaluatorName
                    ],
                    $evaluation_id,
                    'evaluation',
                    '?page=my_evaluations&action=view&id=' . $evaluation_id,
                    false
                );
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    // Get evaluation details with scores
    public function getEvaluationDetails($evaluation_id) {
        $stmt = $this->db->prepare("
            SELECT e.*, ec.name as cycle_name, ec.type as cycle_type,
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   ev.first_name as evaluator_first_name, ev.last_name as evaluator_last_name,
                   cm.name as model_name
            FROM evaluations e
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            JOIN users emp ON e.employee_id = emp.id
            JOIN users ev ON e.evaluator_id = ev.id
            JOIN competency_models cm ON e.model_id = cm.id
            WHERE e.id = ?
        ");
        $stmt->execute([$evaluation_id]);
        $evaluation = $stmt->fetch();
        
        if (!$evaluation) {
            return null;
        }
        
        // Get competency scores
        $stmt = $this->db->prepare("
            SELECT cs.*, c.name as competency_name, c.description, c.weight, c.max_score
            FROM competency_scores cs
            JOIN competencies c ON cs.competency_id = c.id
            WHERE cs.evaluation_id = ?
            ORDER BY c.id
        ");
        $stmt->execute([$evaluation_id]);
        $evaluation['scores'] = $stmt->fetchAll();
        
        return $evaluation;
    }
    
    // Generate competency reports
    public function generateCompetencyReport($employee_id = null, $department = null, $cycle_id = null) {
        $sql = "
            SELECT e.*, ec.name as cycle_name, ec.type as cycle_type,
                   emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   emp.department, emp.position,
                   ev.first_name as evaluator_first_name, ev.last_name as evaluator_last_name,
                   cm.name as model_name
            FROM evaluations e
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
            JOIN users emp ON e.employee_id = emp.id
            JOIN users ev ON e.evaluator_id = ev.id
            JOIN competency_models cm ON e.model_id = cm.id
            WHERE e.status = 'completed'
        ";
        
        $params = [];
        
        if ($employee_id) {
            $sql .= " AND e.employee_id = ?";
            $params[] = $employee_id;
        }
        
        if ($department) {
            $sql .= " AND emp.department = ?";
            $params[] = $department;
        }
        
        if ($cycle_id) {
            $sql .= " AND e.cycle_id = ?";
            $params[] = $cycle_id;
        }
        
        $sql .= " ORDER BY emp.last_name, emp.first_name, ec.start_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get competency trends for employee
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
    
    
    // Get evaluation cycle details
    public function getEvaluationCycleDetails($cycleId) {
        $stmt = $this->db->prepare("
            SELECT ec.*, u.first_name, u.last_name,
                   COUNT(e.id) as evaluation_count,
                   COUNT(CASE WHEN e.status = 'completed' THEN 1 END) as completed_count,
                   COUNT(CASE WHEN e.status = 'pending' THEN 1 END) as pending_count,
                   COUNT(CASE WHEN e.status = 'in_progress' THEN 1 END) as in_progress_count
            FROM evaluation_cycles ec
            LEFT JOIN users u ON ec.created_by = u.id
            LEFT JOIN evaluations e ON ec.id = e.cycle_id
            WHERE ec.id = ?
            GROUP BY ec.id
        ");
        $stmt->execute([$cycleId]);
        
        return $stmt->fetch();
    }
    
    // Get evaluations for a cycle
    public function getCycleEvaluations($cycleId) {
        $stmt = $this->db->prepare("
            SELECT e.*, emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   emp.department, emp.position,
                   ev.first_name as evaluator_first_name, ev.last_name as evaluator_last_name,
                   cm.name as model_name
            FROM evaluations e
            JOIN users emp ON e.employee_id = emp.id
            JOIN users ev ON e.evaluator_id = ev.id
            JOIN competency_models cm ON e.model_id = cm.id
            WHERE e.cycle_id = ?
            ORDER BY e.created_at DESC
        ");
        $stmt->execute([$cycleId]);
        
        return $stmt->fetchAll();
    }
    
    // Get all evaluations
    public function getAllEvaluations($status = null, $limit = null) {
        $sql = "
            SELECT e.*, emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                   emp.department, emp.position,
                   ev.first_name as evaluator_first_name, ev.last_name as evaluator_last_name,
                   cm.name as model_name, ec.name as cycle_name, ec.type as cycle_type
            FROM evaluations e
            JOIN users emp ON e.employee_id = emp.id
            JOIN users ev ON e.evaluator_id = ev.id
            JOIN competency_models cm ON e.model_id = cm.id
            JOIN evaluation_cycles ec ON e.cycle_id = ec.id
        ";
        
        $params = [];
        
        if ($status) {
            $sql .= " WHERE e.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY e.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Update evaluation cycle
    public function updateEvaluationCycle($cycleId, $updateData, $updatedBy = null) {
        try {
            // Get cycle name before update for notification
            $cycleStmt = $this->db->prepare("SELECT name FROM evaluation_cycles WHERE id = ?");
            $cycleStmt->execute([$cycleId]);
            $cycle = $cycleStmt->fetch();
            $cycleName = $cycle ? $cycle['name'] : 'Unknown Cycle';
            
            $stmt = $this->db->prepare("
                UPDATE evaluation_cycles SET 
                    name = ?, type = ?, start_date = ?, end_date = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $updateData['name'],
                $updateData['type'],
                $updateData['start_date'],
                $updateData['end_date'],
                $cycleId
            ]);
            
            if ($result) {
                // Use provided user info or get from session
                if (!$updatedBy) {
                    $userStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                    $userStmt->execute([$_SESSION['user_id'] ?? 1]);
                    $user = $userStmt->fetch();
                    $updatedBy = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'System';
                }
                
                // Notify HR managers and admins
                $this->notificationManager->notifyHRManagers(
                    'cycle_updated',
                    [
                        'cycle_name' => $cycleName,
                        'new_name' => $updateData['name'],
                        'type' => $updateData['type'],
                        'updated_by' => $updatedBy
                    ],
                    $cycleId,
                    'cycle',
                    '?page=evaluation_cycles&action=view&id=' . $cycleId,
                    true
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error updating evaluation cycle: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete evaluation cycle
    public function deleteEvaluationCycle($cycleId, $deletedBy = null) {
        try {
            // Get cycle name before deletion for notification
            $cycleStmt = $this->db->prepare("SELECT name FROM evaluation_cycles WHERE id = ?");
            $cycleStmt->execute([$cycleId]);
            $cycle = $cycleStmt->fetch();
            $cycleName = $cycle ? $cycle['name'] : 'Unknown Cycle';
            
            // First delete all evaluations in this cycle
            $stmt = $this->db->prepare("DELETE FROM evaluations WHERE cycle_id = ?");
            $stmt->execute([$cycleId]);
            
            // Then delete the cycle
            $stmt = $this->db->prepare("DELETE FROM evaluation_cycles WHERE id = ?");
            $result = $stmt->execute([$cycleId]);
            
            if ($result) {
                // Use provided user info or get from session
                if (!$deletedBy) {
                    $userStmt = $this->db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
                    $userStmt->execute([$_SESSION['user_id'] ?? 1]);
                    $user = $userStmt->fetch();
                    $deletedBy = $user ? $user['first_name'] . ' ' . $user['last_name'] : 'System';
                }
                
                // Notify HR managers and admins
                $this->notificationManager->notifyHRManagers(
                    'cycle_deleted',
                    [
                        'cycle_name' => $cycleName,
                        'deleted_by' => $deletedBy
                    ],
                    $cycleId,
                    'cycle',
                    '?page=evaluation_cycles',
                    true
                );
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error deleting evaluation cycle: " . $e->getMessage());
            return false;
        }
    }
}
?>
