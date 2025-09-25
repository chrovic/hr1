<?php
// Smart Recommendations System
class SmartRecommendations {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    // Get training recommendations based on competency gaps
    public function getTrainingRecommendations($employee_id, $limit = 5) {
        try {
            // Get employee's competency scores and identify gaps
            $stmt = $this->db->prepare("
                SELECT c.name as competency_name, c.description, cs.score, c.max_score,
                       cm.category, cm.name as model_name,
                       (c.max_score - cs.score) as gap_score
                FROM competency_scores cs
                JOIN competencies c ON cs.competency_id = c.id
                JOIN evaluations e ON cs.evaluation_id = e.id
                JOIN competency_models cm ON c.model_id = cm.id
                WHERE e.employee_id = ? AND e.status = 'completed'
                AND cs.score < (c.max_score * 0.8)  -- Less than 80% of max score
                ORDER BY gap_score DESC, c.weight DESC
                LIMIT ?
            ");
            $stmt->execute([$employee_id, $limit]);
            $competencyGaps = $stmt->fetchAll();
            
            $recommendations = [];
            
            foreach ($competencyGaps as $gap) {
                // Find relevant training modules
                $trainingStmt = $this->db->prepare("
                    SELECT tm.*, 
                           CASE 
                               WHEN LOWER(tm.title) LIKE LOWER(?) OR LOWER(tm.description) LIKE LOWER(?) THEN 3
                               WHEN LOWER(tm.category) = LOWER(?) THEN 2
                               ELSE 1
                           END as relevance_score
                    FROM training_modules tm
                    WHERE (LOWER(tm.title) LIKE LOWER(?) 
                           OR LOWER(tm.description) LIKE LOWER(?)
                           OR LOWER(tm.category) = LOWER(?))
                    AND tm.status = 'active'
                    ORDER BY relevance_score DESC, tm.created_at DESC
                    LIMIT 3
                ");
                
                $searchTerm = '%' . $gap['competency_name'] . '%';
                $categoryTerm = $gap['category'];
                
                $trainingStmt->execute([
                    $searchTerm, $searchTerm, $categoryTerm,
                    $searchTerm, $searchTerm, $categoryTerm
                ]);
                $trainings = $trainingStmt->fetchAll();
                
                if (!empty($trainings)) {
                    $recommendations[] = [
                        'competency' => $gap,
                        'trainings' => $trainings,
                        'priority' => $this->calculatePriority($gap['gap_score'], $gap['competency_name'])
                    ];
                }
            }
            
            // Sort by priority
            usort($recommendations, function($a, $b) {
                return $b['priority'] - $a['priority'];
            });
            
            return array_slice($recommendations, 0, $limit);
            
        } catch (PDOException $e) {
            error_log("Error getting training recommendations: " . $e->getMessage());
            return [];
        }
    }
    
    // Calculate priority based on gap score and competency importance
    private function calculatePriority($gap_score, $competency_name) {
        $base_priority = $gap_score * 10;
        
        // Boost priority for critical competencies
        $critical_competencies = [
            'leadership', 'management', 'customer service', 'communication',
            'sales', 'technical', 'safety', 'compliance'
        ];
        
        foreach ($critical_competencies as $critical) {
            if (stripos($competency_name, $critical) !== false) {
                $base_priority += 20;
                break;
            }
        }
        
        return $base_priority;
    }
    
    // Get career path suggestions based on current competencies
    public function getCareerPathSuggestions($employee_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT AVG(cs.score) as avg_score, cm.category, COUNT(*) as competency_count
                FROM competency_scores cs
                JOIN competencies c ON cs.competency_id = c.id
                JOIN evaluations e ON cs.evaluation_id = e.id
                JOIN competency_models cm ON c.model_id = cm.id
                WHERE e.employee_id = ? AND e.status = 'completed'
                GROUP BY cm.category
                HAVING avg_score >= 3.5  -- Good performance threshold
                ORDER BY avg_score DESC, competency_count DESC
            ");
            $stmt->execute([$employee_id]);
            $strengths = $stmt->fetchAll();
            
            $career_paths = [];
            
            foreach ($strengths as $strength) {
                $paths = $this->getCareerPathsByCategory($strength['category']);
                foreach ($paths as $path) {
                    $career_paths[] = [
                        'path' => $path,
                        'match_score' => $strength['avg_score'],
                        'category' => $strength['category']
                    ];
                }
            }
            
            return $career_paths;
            
        } catch (PDOException $e) {
            error_log("Error getting career path suggestions: " . $e->getMessage());
            return [];
        }
    }
    
    // Get career paths by competency category
    private function getCareerPathsByCategory($category) {
        $career_mapping = [
            'Leadership' => [
                'Team Lead', 'Department Manager', 'Director', 'VP', 'Executive'
            ],
            'Technical' => [
                'Senior Developer', 'Tech Lead', 'Solutions Architect', 'CTO'
            ],
            'E-Commerce' => [
                'E-Commerce Specialist', 'Digital Marketing Manager', 'E-Commerce Director'
            ],
            'Sales' => [
                'Senior Sales Rep', 'Sales Manager', 'Sales Director', 'VP Sales'
            ],
            'Customer Service' => [
                'Customer Success Manager', 'Support Team Lead', 'Customer Experience Manager'
            ],
            'Marketing' => [
                'Marketing Specialist', 'Marketing Manager', 'Brand Manager', 'CMO'
            ]
        ];
        
        return $career_mapping[$category] ?? ['Specialist', 'Manager', 'Director'];
    }
    
    // Get skill gap analysis for specific role
    public function getSkillGapAnalysis($employee_id, $target_role = null) {
        try {
            // Get employee's current competencies
            $stmt = $this->db->prepare("
                SELECT c.name, cs.score, c.max_score, cm.category
                FROM competency_scores cs
                JOIN competencies c ON cs.competency_id = c.id
                JOIN evaluations e ON cs.evaluation_id = e.id
                JOIN competency_models cm ON c.model_id = cm.id
                WHERE e.employee_id = ? AND e.status = 'completed'
                ORDER BY e.completed_at DESC
            ");
            $stmt->execute([$employee_id]);
            $current_skills = $stmt->fetchAll();
            
            // Calculate skill gaps
            $gaps = [];
            $strengths = [];
            
            foreach ($current_skills as $skill) {
                $percentage = ($skill['score'] / $skill['max_score']) * 100;
                
                if ($percentage < 70) {
                    $gaps[] = [
                        'skill' => $skill['name'],
                        'current_score' => $skill['score'],
                        'max_score' => $skill['max_score'],
                        'gap_percentage' => 100 - $percentage,
                        'category' => $skill['category']
                    ];
                } elseif ($percentage >= 85) {
                    $strengths[] = [
                        'skill' => $skill['name'],
                        'score_percentage' => $percentage,
                        'category' => $skill['category']
                    ];
                }
            }
            
            return [
                'gaps' => $gaps,
                'strengths' => $strengths,
                'overall_score' => $this->calculateOverallScore($current_skills)
            ];
            
        } catch (PDOException $e) {
            error_log("Error getting skill gap analysis: " . $e->getMessage());
            return ['gaps' => [], 'strengths' => [], 'overall_score' => 0];
        }
    }
    
    // Calculate overall competency score
    private function calculateOverallScore($skills) {
        if (empty($skills)) return 0;
        
        $total_weighted_score = 0;
        $total_weight = 0;
        
        foreach ($skills as $skill) {
            $weight = 1; // Default weight, could be enhanced to use actual weights
            $total_weighted_score += ($skill['score'] / $skill['max_score']) * $weight * 100;
            $total_weight += $weight;
        }
        
        return $total_weight > 0 ? round($total_weighted_score / $total_weight, 1) : 0;
    }
    
    // Get next best training based on employee's learning path
    public function getNextBestTraining($employee_id) {
        try {
            // Get completed trainings
            $stmt = $this->db->prepare("
                SELECT tm.category, COUNT(*) as completed_count
                FROM training_enrollments te
                JOIN training_sessions ts ON te.session_id = ts.id
                JOIN training_modules tm ON ts.module_id = tm.id
                WHERE te.employee_id = ? AND te.completion_status = 'completed'
                GROUP BY tm.category
                ORDER BY completed_count DESC
            ");
            $stmt->execute([$employee_id]);
            $completed_categories = $stmt->fetchAll();
            
            // Get recommendations based on competency gaps
            $recommendations = $this->getTrainingRecommendations($employee_id, 1);
            
            if (!empty($recommendations)) {
                return [
                    'training' => $recommendations[0]['trainings'][0] ?? null,
                    'reason' => 'Based on competency gap in: ' . $recommendations[0]['competency']['competency_name'],
                    'priority' => 'high'
                ];
            }
            
            // Fallback: suggest popular training in employee's field
            $stmt = $this->db->prepare("
                SELECT tm.*, COUNT(te.id) as enrollment_count
                FROM training_modules tm
                LEFT JOIN training_sessions ts ON tm.id = ts.module_id
                LEFT JOIN training_enrollments te ON ts.id = te.session_id
                WHERE tm.status = 'active'
                GROUP BY tm.id
                ORDER BY enrollment_count DESC, tm.created_at DESC
                LIMIT 1
            ");
            $stmt->execute();
            $popular_training = $stmt->fetch();
            
            return [
                'training' => $popular_training,
                'reason' => 'Popular training for skill development',
                'priority' => 'medium'
            ];
            
        } catch (PDOException $e) {
            error_log("Error getting next best training: " . $e->getMessage());
            return null;
        }
    }
}
?>
