<?php
// Succession Planning System
class SuccessionManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Create critical position
    public function createCriticalPosition($positionData) {
        $stmt = $this->db->prepare("INSERT INTO critical_positions (position_title, department, description, priority_level, succession_timeline, risk_level, current_incumbent_id, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $positionData['position_title'],
            $positionData['department'],
            $positionData['description'],
            $positionData['priority_level'],
            $positionData['succession_timeline'],
            $positionData['risk_level'],
            $positionData['current_incumbent_id'],
            $positionData['created_by']
        ]);
    }
    
    // Get all critical positions
    public function getAllCriticalPositions() {
        $stmt = $this->db->prepare("
            SELECT cp.*, 
                   incumbent.first_name as incumbent_first_name, incumbent.last_name as incumbent_last_name,
                   creator.first_name as creator_first_name, creator.last_name as creator_last_name,
                   COUNT(sc.id) as candidate_count,
                   COUNT(CASE WHEN sc.readiness_level = 'ready_now' THEN 1 END) as ready_now_count,
                   COUNT(CASE WHEN sc.readiness_level = 'ready_soon' THEN 1 END) as ready_soon_count
            FROM critical_positions cp
            LEFT JOIN users incumbent ON cp.current_incumbent_id = incumbent.id
            LEFT JOIN users creator ON cp.created_by = creator.id
            LEFT JOIN succession_candidates sc ON cp.id = sc.plan_id
            GROUP BY cp.id
            ORDER BY cp.priority_level DESC, cp.created_at DESC
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Add succession candidate
    public function addSuccessionCandidate($candidateData) {
        $stmt = $this->db->prepare("INSERT INTO succession_candidates (plan_id, candidate_id, readiness_level, development_plan, assessment_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
        
        return $stmt->execute([
            $candidateData['position_id'],
            $candidateData['candidate_id'],
            $candidateData['readiness_level'],
            $candidateData['development_plan'],
            $candidateData['assessment_date'] ?? date('Y-m-d'),
            $candidateData['notes'] ?? ''
        ]);
    }
    
    // Get succession candidates for position
    public function getPositionCandidates($position_id) {
        $stmt = $this->db->prepare("
            SELECT sc.*, 
                   u.first_name, u.last_name, u.department, u.position, u.hire_date,
                   creator.first_name as creator_first_name, creator.last_name as creator_last_name
            FROM succession_candidates sc
            JOIN users u ON sc.candidate_id = u.id
            LEFT JOIN users creator ON sc.created_by = creator.id
            WHERE sc.position_id = ?
            ORDER BY sc.readiness_score DESC, u.last_name, u.first_name
        ");
        $stmt->execute([$position_id]);
        
        return $stmt->fetchAll();
    }
    
    // Update candidate readiness
    public function updateCandidateReadiness($candidate_id, $readiness_data) {
        $stmt = $this->db->prepare("UPDATE succession_candidates SET readiness_level = ?, readiness_score = ?, development_plan = ?, timeline_months = ? WHERE id = ?");
        
        return $stmt->execute([
            $readiness_data['readiness_level'],
            $readiness_data['readiness_score'],
            $readiness_data['development_plan'],
            $readiness_data['timeline_months'],
            $candidate_id
        ]);
    }
    
    // Generate succession slate
    public function generateSuccessionSlate($position_id) {
        $stmt = $this->db->prepare("
            SELECT sc.*, 
                   u.first_name, u.last_name, u.department, u.position, u.hire_date,
                   cp.position_title, cp.department as position_department,
                   cp.priority_level, cp.succession_timeline, cp.risk_level
            FROM succession_candidates sc
            JOIN users u ON sc.candidate_id = u.id
            JOIN critical_positions cp ON sc.position_id = cp.id
            WHERE sc.position_id = ?
            ORDER BY sc.readiness_score DESC, sc.readiness_level ASC
        ");
        $stmt->execute([$position_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get succession risk assessment
    public function getSuccessionRiskAssessment() {
        $stmt = $this->db->prepare("
            SELECT cp.*, 
                   incumbent.first_name as incumbent_first_name, incumbent.last_name as incumbent_last_name,
                   COUNT(sc.id) as candidate_count,
                   AVG(sc.readiness_score) as avg_readiness_score,
                   COUNT(CASE WHEN sc.readiness_level = 'ready_now' THEN 1 END) as ready_now_count,
                   COUNT(CASE WHEN sc.readiness_level = 'ready_soon' THEN 1 END) as ready_soon_count
            FROM critical_positions cp
            LEFT JOIN users incumbent ON cp.current_incumbent_id = incumbent.id
            LEFT JOIN succession_candidates sc ON cp.id = sc.plan_id
            GROUP BY cp.id
            ORDER BY cp.priority_level DESC, cp.risk_level DESC
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    // Get employee succession opportunities
    public function getEmployeeSuccessionOpportunities($employee_id) {
        $stmt = $this->db->prepare("
            SELECT sc.*, 
                   cp.position_title, cp.department as position_department,
                   cp.priority_level, cp.succession_timeline, cp.risk_level,
                   incumbent.first_name as incumbent_first_name, incumbent.last_name as incumbent_last_name
            FROM succession_candidates sc
            JOIN critical_positions cp ON sc.position_id = cp.id
            LEFT JOIN users incumbent ON cp.current_incumbent_id = incumbent.id
            WHERE sc.candidate_id = ?
            ORDER BY sc.readiness_score DESC, cp.priority_level DESC
        ");
        $stmt->execute([$employee_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get succession statistics
    public function getSuccessionStats() {
        $stats = [];
        
        // Total critical positions
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM critical_positions");
        $stmt->execute();
        $stats['total_positions'] = $stmt->fetch()['count'];
        
        // High priority positions
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM critical_positions WHERE priority_level IN ('critical', 'high')");
        $stmt->execute();
        $stats['high_priority_positions'] = $stmt->fetch()['count'];
        
        // Positions with candidates
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT position_id) as count 
            FROM succession_candidates
        ");
        $stmt->execute();
        $stats['positions_with_candidates'] = $stmt->fetch()['count'];
        
        // Ready now candidates
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM succession_candidates WHERE readiness_level = 'ready_now'");
        $stmt->execute();
        $stats['ready_now_candidates'] = $stmt->fetch()['count'];
        
        // Ready soon candidates
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM succession_candidates WHERE readiness_level = 'ready_soon'");
        $stmt->execute();
        $stats['ready_soon_candidates'] = $stmt->fetch()['count'];
        
        // High risk positions
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM critical_positions WHERE risk_level = 'high'");
        $stmt->execute();
        $stats['high_risk_positions'] = $stmt->fetch()['count'];
        
        return $stats;
    }
    
    // Suggest succession candidates based on performance and training
    public function suggestSuccessionCandidates($position_id) {
        // This would integrate with performance and training data
        // For now, return employees from the same department
        
        $stmt = $this->db->prepare("
            SELECT DISTINCT u.id, u.first_name, u.last_name, u.department, u.position, u.hire_date
            FROM users u
            JOIN critical_positions cp ON u.department = cp.department
            LEFT JOIN succession_candidates sc ON u.id = sc.candidate_id AND sc.position_id = ?
            WHERE u.role = 'employee' 
            AND u.status = 'active'
            AND sc.id IS NULL
            AND cp.id = ?
            ORDER BY u.hire_date ASC
            LIMIT 10
        ");
        $stmt->execute([$position_id, $position_id]);
        
        return $stmt->fetchAll();
    }
    
    // Get succession development plans
    public function getDevelopmentPlans($employee_id = null) {
        $sql = "
            SELECT sc.*, 
                   u.first_name, u.last_name, u.department, u.position,
                   cp.position_title as target_position, cp.department as target_department,
                   cp.priority_level, cp.succession_timeline
            FROM succession_candidates sc
            JOIN users u ON sc.candidate_id = u.id
            JOIN critical_positions cp ON sc.position_id = cp.id
            WHERE sc.development_plan IS NOT NULL AND sc.development_plan != ''
        ";
        
        $params = [];
        if ($employee_id) {
            $sql .= " AND sc.candidate_id = ?";
            $params[] = $employee_id;
        }
        
        $sql .= " ORDER BY cp.priority_level DESC, sc.readiness_score DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    // Get all succession candidates
    public function getAllSuccessionCandidates() {
        $stmt = $this->db->prepare("
            SELECT sc.*, 
                   candidate.first_name as candidate_first_name, candidate.last_name as candidate_last_name,
                   cp.position_title, cp.department
            FROM succession_candidates sc
            LEFT JOIN users candidate ON sc.candidate_id = candidate.id
            LEFT JOIN critical_positions cp ON sc.plan_id = cp.id
            ORDER BY sc.assessment_date DESC
        ");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}

