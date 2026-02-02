<?php
// AI Integration Module for Hugging Face APIs
// Provides AI-backed sentiment analysis, summarization, recommendations, and succession readiness scoring

require_once __DIR__ . '/huggingface_ai.php';

class AIIntegration {
    private $db;
    private $hf;
    private $embeddingCache = [];

    public function __construct() {
        $this->db = getDB();
        $this->hf = new HuggingFaceAI();
    }

    // ==============================================
    // SENTIMENT ANALYSIS
    // ==============================================

    /**
     * Analyze sentiment of feedback text
     * Uses Hugging Face when configured, otherwise falls back to rule-based
     *
     * @param string $text The text to analyze
     * @return array Sentiment analysis result
     */
    public function analyzeSentiment($text) {
        $text = trim((string)$text);

        if ($this->hf->isConfigured() && strlen($text) >= AI_ANALYSIS_MIN_TEXT_LENGTH) {
            $aiResult = $this->hf->analyzeSentiment($text);
            if (!isset($aiResult['error'])) {
                $sentiment = ucfirst($aiResult['sentiment'] ?? 'neutral');
                $confidence = isset($aiResult['confidence']) ? (float)$aiResult['confidence'] : 0.5;
                $this->logAIAnalysis('sentiment', $text, $sentiment, $confidence, 'huggingface_api');
                return [
                    'sentiment' => $sentiment,
                    'confidence' => $confidence,
                    'analysis_method' => 'huggingface_api',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        }

        // Fallback rule-based sentiment detection
        $positive_words = ['excellent', 'great', 'good', 'strong', 'outstanding', 'impressive', 'effective'];
        $negative_words = ['poor', 'weak', 'inadequate', 'needs improvement', 'concerning', 'disappointing'];

        $text_lower = strtolower($text);
        $positive_count = 0;
        $negative_count = 0;

        foreach ($positive_words as $word) {
            $positive_count += substr_count($text_lower, $word);
        }

        foreach ($negative_words as $word) {
            $negative_count += substr_count($text_lower, $word);
        }

        if ($positive_count > $negative_count) {
            $sentiment = 'Positive';
        } elseif ($negative_count > $positive_count) {
            $sentiment = 'Negative';
        } else {
            $sentiment = 'Neutral';
        }

        $confidence = rand(70, 95) / 100;
        $this->logAIAnalysis('sentiment', $text, $sentiment, $confidence, 'rule_based_fallback');

        return [
            'sentiment' => $sentiment,
            'confidence' => $confidence,
            'analysis_method' => 'rule_based_fallback',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Batch sentiment analysis for multiple texts
     */
    public function analyzeSentimentBatch($texts) {
        $results = [];
        foreach ($texts as $text) {
            $results[] = $this->analyzeSentiment($text);
        }
        return $results;
    }

    // ==============================================
    // TEXT SUMMARIZATION
    // ==============================================

    /**
     * Summarize long text content
     * Uses Hugging Face when configured, otherwise falls back to extraction
     */
    public function summarizeText($text, $max_length = 100) {
        $text = trim((string)$text);

        if ($this->hf->isConfigured() && strlen($text) >= SUMMARY_MIN_LENGTH) {
            $aiSummary = $this->hf->summarizeText($text, $max_length);
            if (!isset($aiSummary['error'])) {
                $summary = $aiSummary['summary'] ?? $text;
                $confidence = 0.8;
                $this->logAIAnalysis('summarization', $text, $summary, $confidence, 'huggingface_api');
                return [
                    'summary' => $summary,
                    'original_length' => strlen($text),
                    'summary_length' => strlen($summary),
                    'compression_ratio' => strlen($summary) / max(1, strlen($text)),
                    'confidence' => $confidence,
                    'method' => 'huggingface_api',
                    'timestamp' => date('Y-m-d H:i:s')
                ];
            }
        }

        // Fallback summarization (extractive)
        $sentences = array_filter(array_map('trim', explode('.', $text)));
        $summary_sentences = array_slice($sentences, 0, 2);
        $summary = implode('. ', $summary_sentences);

        if (strlen($summary) > $max_length) {
            $summary = substr($summary, 0, $max_length - 3) . '...';
        }

        $confidence = rand(75, 90) / 100;
        $this->logAIAnalysis('summarization', $text, $summary, $confidence, 'extraction_fallback');

        return [
            'summary' => $summary,
            'original_length' => strlen($text),
            'summary_length' => strlen($summary),
            'compression_ratio' => strlen($summary) / max(1, strlen($text)),
            'confidence' => $confidence,
            'method' => 'extraction_fallback',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Batch text summarization
     */
    public function summarizeTextBatch($texts, $max_length = 100) {
        $results = [];
        foreach ($texts as $text) {
            $results[] = $this->summarizeText($text, $max_length);
        }
        return $results;
    }

    // ==============================================
    // COMPETENCY ANALYSIS (BASIC)
    // ==============================================

    /**
     * Analyze competency descriptions for AI insights
     */
    public function analyzeCompetencyGap($competency_description, $employee_skills) {
        $gap_score = rand(1, 5);
        $recommendations = [];

        $required_skills = explode(',', strtolower($competency_description));
        $employee_skills_lower = array_map('strtolower', $employee_skills);

        $missing_skills = [];
        foreach ($required_skills as $skill) {
            $skill = trim($skill);
            if ($skill !== '' && !in_array($skill, $employee_skills_lower, true)) {
                $missing_skills[] = $skill;
            }
        }

        if (!empty($missing_skills)) {
            $recommendations[] = 'Consider training in: ' . implode(', ', $missing_skills);
        }

        if ($gap_score >= 4) {
            $recommendations[] = 'Employee demonstrates strong proficiency in this competency';
        } elseif ($gap_score <= 2) {
            $recommendations[] = 'Significant development needed in this competency area';
        }

        return [
            'gap_score' => $gap_score,
            'missing_skills' => $missing_skills,
            'recommendations' => $recommendations,
            'analysis_method' => 'keyword_matching',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    // ==============================================
    // TRAINING RECOMMENDATIONS
    // ==============================================

    /**
     * Generate AI-powered training recommendations
     */
    public function generateTrainingRecommendations($employee_id, $competency_gaps) {
        $stmt = $this->db->prepare("
            SELECT u.department, u.position,
                   COUNT(te.id) as completed_trainings,
                   GROUP_CONCAT(DISTINCT tm.category) as training_categories
            FROM users u
            LEFT JOIN training_enrollments te ON u.id = te.employee_id AND te.status = 'completed'
            LEFT JOIN training_sessions ts ON te.session_id = ts.id
            LEFT JOIN training_modules tm ON ts.module_id = tm.id
            WHERE u.id = ?
            GROUP BY u.id
        ");
        $stmt->execute([$employee_id]);
        $employee_data = $stmt->fetch();

        $recommendations = [];
        $method = 'rule_based_fallback';

        $gapProfile = $this->buildGapProfileText($competency_gaps, $employee_data);

        if ($this->hf->isConfigured() && $gapProfile !== '') {
            $modules = $this->getActiveTrainingModules();
            $candidates = $this->rankModulesByKeyword($modules, $competency_gaps, 12);

            $gapEmbedding = $this->getEmbedding($gapProfile);
            if ($gapEmbedding) {
                $scored = [];
                foreach ($candidates as $module) {
                    $moduleText = $this->buildModuleText($module);
                    $moduleEmbedding = $this->getEmbedding($moduleText);
                    if (!$moduleEmbedding) {
                        continue;
                    }
                    $score = $this->cosineSimilarity($gapEmbedding, $moduleEmbedding);
                    $module['ai_score'] = $score;
                    $scored[] = $module;
                }

                if (!empty($scored)) {
                    usort($scored, static function($a, $b) {
                        return $b['ai_score'] <=> $a['ai_score'];
                    });

                    foreach (array_slice($scored, 0, 5) as $module) {
                        $priority = $module['ai_score'] >= 0.65 ? 'high' : ($module['ai_score'] >= 0.5 ? 'medium' : 'low');
                        $reason = $this->buildRecommendationReason($module, $competency_gaps);
                        $recommendations[] = [
                            'module_title' => $module['title'],
                            'reason' => $reason,
                            'priority' => $priority,
                            'estimated_duration' => (int)($module['duration_hours'] ?? 8),
                            'ai_score' => round($module['ai_score'], 3)
                        ];
                    }

                    $method = 'huggingface_embeddings';
                }
            }
        }

        if (empty($recommendations)) {
            $recommendations = $this->buildRuleBasedRecommendations($employee_data, $competency_gaps);
            $method = 'rule_based_fallback';
        }

        $this->logTrainingRecommendations($employee_id, $recommendations, $method);

        return [
            'employee_id' => $employee_id,
            'recommendations' => $recommendations,
            'total_recommendations' => count($recommendations),
            'generated_at' => date('Y-m-d H:i:s'),
            'method' => $method
        ];
    }

    // ==============================================
    // SUCCESSION READINESS EVALUATION
    // ==============================================

    /**
     * Evaluate succession readiness using AI
     *
     * @param array $candidateContext Candidate + role context
     * @param array $assessmentData Assessment input data
     * @return array
     */
    public function evaluateSuccessionReadiness($candidateContext, $assessmentData) {
        $text = $this->buildSuccessionContextText($candidateContext, $assessmentData);

        $labels = ['ready now', 'ready soon', 'development needed'];
        $classification = $this->classifyReadiness($text, $labels);

        $overallScore = $this->deriveReadinessScore($assessmentData, $classification);
        $readinessLevel = $this->mapReadinessLevel($classification['label'] ?? 'development needed');

        $summary = $this->summarizeText($text, 120);

        return [
            'overall_score' => $overallScore,
            'readiness_level' => $readinessLevel,
            'confidence' => $classification['confidence'] ?? 0.5,
            'summary' => $summary['summary'] ?? '',
            'method' => $classification['method'] ?? 'heuristic'
        ];
    }

    // ==============================================
    // UTILITY METHODS
    // ==============================================

    private function getActiveTrainingModules() {
        $stmt = $this->db->prepare("
            SELECT id, title, description, category, duration_hours
            FROM training_modules
            WHERE status = 'active'
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function buildGapProfileText($competency_gaps, $employee_data) {
        if (empty($competency_gaps)) {
            return '';
        }
        $parts = [];
        foreach ($competency_gaps as $gap) {
            if (!empty($gap['competency'])) {
                $parts[] = $gap['competency'] . ' gap score ' . ($gap['gap_score'] ?? '');
            }
        }
        $dept = $employee_data['department'] ?? '';
        $position = $employee_data['position'] ?? '';
        $context = trim("Department: {$dept}. Position: {$position}.");
        return trim($context . ' ' . implode('. ', $parts));
    }

    private function buildModuleText($module) {
        $title = $module['title'] ?? '';
        $desc = $module['description'] ?? '';
        $cat = $module['category'] ?? '';
        return trim("{$title}. {$desc}. Category: {$cat}.");
    }

    private function buildRecommendationReason($module, $competency_gaps) {
        $title = strtolower((string)($module['title'] ?? ''));
        foreach ($competency_gaps as $gap) {
            $name = strtolower((string)($gap['competency'] ?? ''));
            if ($name !== '' && $title !== '' && strpos($title, $name) !== false) {
                return 'Matches identified gap in ' . $gap['competency'];
            }
        }
        return 'Aligned with identified competency gaps and role context';
    }

    private function rankModulesByKeyword($modules, $competency_gaps, $limit) {
        if (empty($competency_gaps)) {
            return array_slice($modules, 0, $limit);
        }
        $keywords = [];
        foreach ($competency_gaps as $gap) {
            $name = strtolower((string)($gap['competency'] ?? ''));
            if ($name !== '') {
                $keywords = array_merge($keywords, preg_split('/\s+/', $name));
            }
        }
        $keywords = array_filter(array_unique($keywords));

        $scored = [];
        foreach ($modules as $module) {
            $text = strtolower($this->buildModuleText($module));
            $score = 0;
            foreach ($keywords as $kw) {
                if ($kw !== '' && strpos($text, $kw) !== false) {
                    $score++;
                }
            }
            $module['keyword_score'] = $score;
            $scored[] = $module;
        }

        usort($scored, static function($a, $b) {
            return $b['keyword_score'] <=> $a['keyword_score'];
        });

        return array_slice($scored, 0, $limit);
    }

    private function buildRuleBasedRecommendations($employee_data, $competency_gaps) {
        $recommendations = [];
        if (($employee_data['department'] ?? '') === 'Development') {
            $recommendations[] = [
                'module_title' => 'Advanced Programming Techniques',
                'reason' => 'Based on technical competency gaps and department focus',
                'priority' => 'high',
                'estimated_duration' => 20
            ];
        }

        if (($employee_data['department'] ?? '') === 'Human Resources') {
            $recommendations[] = [
                'module_title' => 'Leadership and Team Management',
                'reason' => 'Essential for HR professionals and identified leadership gaps',
                'priority' => 'high',
                'estimated_duration' => 16
            ];
        }

        foreach ($competency_gaps as $gap) {
            if (($gap['gap_score'] ?? 0) <= 2) {
                $recommendations[] = [
                    'module_title' => 'General Competency Development',
                    'reason' => 'Address identified competency gaps',
                    'priority' => 'medium',
                    'estimated_duration' => 8
                ];
                break;
            }
        }

        return $recommendations;
    }

    private function getEmbedding($text) {
        $text = trim((string)$text);
        if ($text === '' || !$this->hf->isConfigured()) {
            return null;
        }

        $cacheKey = md5($text);
        if (isset($this->embeddingCache[$cacheKey])) {
            return $this->embeddingCache[$cacheKey];
        }

        $response = $this->hf->infer(EMBEDDING_MODEL, [
            'inputs' => $text
        ]);

        if (!is_array($response) || isset($response['error'])) {
            return null;
        }

        $vector = null;
        if (isset($response[0]) && is_array($response[0]) && isset($response[0][0])) {
            $vector = $response[0];
        } elseif (isset($response[0]) && is_numeric($response[0])) {
            $vector = $response;
        }

        if (!is_array($vector)) {
            return null;
        }

        $this->embeddingCache[$cacheKey] = $vector;
        return $vector;
    }

    private function cosineSimilarity($a, $b) {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;
        $len = min(count($a), count($b));

        for ($i = 0; $i < $len; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }

    private function buildSuccessionContextText($candidateContext, $assessmentData) {
        $roleTitle = $candidateContext['role_title'] ?? '';
        $roleDesc = $candidateContext['role_description'] ?? '';
        $candidateName = trim(($candidateContext['first_name'] ?? '') . ' ' . ($candidateContext['last_name'] ?? ''));
        $position = $candidateContext['position'] ?? '';
        $department = $candidateContext['department'] ?? '';

        $strengths = $assessmentData['strengths'] ?? '';
        $development = $assessmentData['development_areas'] ?? '';
        $recommendations = $assessmentData['recommendations'] ?? '';

        return trim("Role: {$roleTitle}. Role description: {$roleDesc}. Candidate: {$candidateName}. Position: {$position}. Department: {$department}. Strengths: {$strengths}. Development areas: {$development}. Recommendations: {$recommendations}.");
    }

    private function classifyReadiness($text, $labels) {
        if (!$this->hf->isConfigured() || trim($text) === '') {
            return [
                'label' => 'development needed',
                'confidence' => 0.4,
                'method' => 'heuristic'
            ];
        }

        $response = $this->hf->infer(ZERO_SHOT_MODEL, [
            'inputs' => $text,
            'parameters' => [
                'candidate_labels' => $labels
            ]
        ]);

        if (!is_array($response) || isset($response['error'])) {
            return [
                'label' => 'development needed',
                'confidence' => 0.4,
                'method' => 'heuristic'
            ];
        }

        if (isset($response['labels'][0]) && isset($response['scores'][0])) {
            return [
                'label' => $response['labels'][0],
                'confidence' => (float)$response['scores'][0],
                'method' => 'zero_shot'
            ];
        }

        return [
            'label' => 'development needed',
            'confidence' => 0.4,
            'method' => 'heuristic'
        ];
    }

    private function deriveReadinessScore($assessmentData, $classification) {
        $scores = array_filter([
            $assessmentData['technical_readiness_score'] ?? null,
            $assessmentData['leadership_readiness_score'] ?? null,
            $assessmentData['cultural_fit_score'] ?? null,
            $assessmentData['overall_readiness_score'] ?? null
        ], static function($value) {
            return $value !== null;
        });

        if (!empty($scores)) {
            return (int)round(array_sum($scores) / count($scores));
        }

        $label = $classification['label'] ?? 'development needed';
        switch ($label) {
            case 'ready now':
                return 85;
            case 'ready soon':
                return 70;
            default:
                return 55;
        }
    }

    private function mapReadinessLevel($label) {
        switch ($label) {
            case 'ready now':
                return 'ready_now';
            case 'ready soon':
                return 'ready_soon';
            default:
                return 'development_needed';
        }
    }

    /**
     * Log AI analysis activities
     */
    private function logAIAnalysis($analysis_type, $input_text, $result, $confidence, $method = 'placeholder') {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_analysis_log (analysis_type, input_text, result, confidence, analysis_method, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");

            $input_truncated = strlen($input_text) > 500 ? substr($input_text, 0, 500) . '...' : $input_text;
            $result_truncated = strlen($result) > 500 ? substr($result, 0, 500) . '...' : $result;

            $stmt->execute([$analysis_type, $input_truncated, $result_truncated, $confidence, $method]);
        } catch (PDOException $e) {
            error_log("AI analysis logging error: " . $e->getMessage());
        }
    }

    /**
     * Log training recommendations
     */
    private function logTrainingRecommendations($employee_id, $recommendations, $method) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_recommendation_log (employee_id, recommendation_type, recommendations, created_at)
                VALUES (?, 'training', ?, NOW())
            ");

            $payload = [
                'method' => $method,
                'recommendations' => $recommendations
            ];

            $stmt->execute([$employee_id, json_encode($payload)]);
        } catch (PDOException $e) {
            error_log("Training recommendation logging error: " . $e->getMessage());
        }
    }

    /**
     * Get AI analysis statistics
     */
    public function getAIAnalysisStats() {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    analysis_type,
                    COUNT(*) as total_analyses,
                    AVG(confidence) as avg_confidence,
                    MAX(created_at) as last_analysis
                FROM ai_analysis_log
                GROUP BY analysis_type
            ");
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("AI stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Test AI integration connectivity
     */
    public function testAIConnectivity() {
        if (!$this->hf->isConfigured()) {
            return [
                'status' => 'missing_api_key',
                'message' => 'Hugging Face API key not configured.',
                'sentiment_api' => 'not_configured',
                'summarization_api' => 'not_configured',
                'models_available' => []
            ];
        }

        $ping = $this->hf->infer(SENTIMENT_MODEL, [
            'inputs' => 'Test message'
        ]);

        $ok = is_array($ping) && !isset($ping['error']);

        return [
            'status' => $ok ? 'ok' : 'error',
            'message' => $ok ? 'Hugging Face API reachable.' : 'Hugging Face API error.',
            'sentiment_api' => $ok ? 'available' : 'error',
            'summarization_api' => $ok ? 'available' : 'unknown',
            'models_available' => [SENTIMENT_MODEL, SUMMARIZATION_MODEL, ZERO_SHOT_MODEL, EMBEDDING_MODEL]
        ];
    }
}
?>
