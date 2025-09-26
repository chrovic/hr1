<?php
// Hugging Face AI Integration for HR1 System
require_once __DIR__ . '/../../config/huggingface_config.php';

class HuggingFaceAI {
    private $apiKey;
    private $baseUrl;
    private $db;
    
    public function __construct($apiKey = null) {
        $this->apiKey = $apiKey ?: HUGGINGFACE_API_KEY;
        $this->baseUrl = HUGGINGFACE_BASE_URL;
        $this->db = getDB();
    }
    
    /**
     * Analyze sentiment of evaluator feedback
     */
    public function analyzeSentiment($text) {
        if (empty($text) || strlen($text) < 10) {
            return [
                'sentiment' => 'neutral',
                'confidence' => 0.5,
                'error' => 'Text too short for analysis'
            ];
        }
        
        try {
            $response = $this->makeRequest(SENTIMENT_MODEL, [
                'inputs' => $text
            ]);
            
            if (isset($response['error'])) {
                return [
                    'sentiment' => 'neutral',
                    'confidence' => 0.5,
                    'error' => $response['error']
                ];
            }
            
            // Process sentiment results
            $sentiment = 'neutral';
            $confidence = 0.5;
            
            if (is_array($response) && count($response) > 0) {
                $result = $response[0];
                $maxScore = 0;
                
                foreach ($result as $item) {
                    if ($item['score'] > $maxScore) {
                        $maxScore = $item['score'];
                        $label = $item['label'];
                        
                        // Map Hugging Face labels to our sentiment
                        switch ($label) {
                            case 'LABEL_0':
                                $sentiment = 'negative';
                                break;
                            case 'LABEL_1':
                                $sentiment = 'neutral';
                                break;
                            case 'LABEL_2':
                                $sentiment = 'positive';
                                break;
                        }
                        $confidence = $item['score'];
                    }
                }
            }
            
            // Always try score-based sentiment first for better accuracy
            $scoreBasedSentiment = $this->getScoreBasedSentiment($text);
            if ($scoreBasedSentiment['confidence'] >= 0.7) {
                return $scoreBasedSentiment;
            }
            
            // Special case: if text contains high scores (4.0+), force positive sentiment
            if (preg_match('/([4-5]\.?\d*)\/5/', $text, $matches)) {
                $score = floatval($matches[1]);
                if ($score >= 4.0) {
                    return ['sentiment' => 'positive', 'confidence' => 0.9];
                }
            }
            
            // If score-based sentiment is not confident, use AI result
            if ($confidence < 0.7 || $sentiment === 'neutral') {
                return $scoreBasedSentiment;
            }
            
            return [
                'sentiment' => $sentiment,
                'confidence' => $confidence,
                'raw_response' => $response
            ];
            
        } catch (Exception $e) {
            error_log("Hugging Face sentiment analysis error: " . $e->getMessage());
            return [
                'sentiment' => 'neutral',
                'confidence' => 0.5,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate summary of evaluation feedback
     */
    public function summarizeText($text, $maxLength = 150) {
        if (empty($text) || strlen($text) < 50) {
            return [
                'summary' => $text,
                'original_length' => strlen($text),
                'summary_length' => strlen($text),
                'compression_ratio' => 1.0
            ];
        }
        
        try {
            // Clean the text before sending to AI
            $cleanedText = $this->cleanTextForSummary($text);
            
            $response = $this->makeRequest(SUMMARIZATION_MODEL, [
                'inputs' => $cleanedText,
                'parameters' => [
                    'max_length' => $maxLength,
                    'min_length' => SUMMARY_MIN_LENGTH,
                    'do_sample' => false,
                    'temperature' => 0.3, // Lower temperature for more focused summaries
                    'repetition_penalty' => 1.2 // Reduce repetition
                ]
            ]);
            
            if (isset($response['error'])) {
                return [
                    'summary' => $this->fallbackSummary($text, $maxLength),
                    'original_length' => strlen($text),
                    'summary_length' => strlen($this->fallbackSummary($text, $maxLength)),
                    'compression_ratio' => strlen($this->fallbackSummary($text, $maxLength)) / strlen($text),
                    'error' => $response['error']
                ];
            }
            
            $summary = is_array($response) && isset($response[0]['summary_text']) 
                ? $this->postProcessSummary($response[0]['summary_text']) 
                : $this->fallbackSummary($text, $maxLength);
            
            return [
                'summary' => $summary,
                'original_length' => strlen($text),
                'summary_length' => strlen($summary),
                'compression_ratio' => strlen($summary) / strlen($text),
                'raw_response' => $response
            ];
            
        } catch (Exception $e) {
            error_log("Hugging Face summarization error: " . $e->getMessage());
            return [
                'summary' => $this->fallbackSummary($text, $maxLength),
                'original_length' => strlen($text),
                'summary_length' => strlen($this->fallbackSummary($text, $maxLength)),
                'compression_ratio' => strlen($this->fallbackSummary($text, $maxLength)) / strlen($text),
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Analyze competency evaluation feedback with full context
     */
    public function analyzeCompetencyFeedback($evaluationId) {
        try {
            // Get evaluation context first
            $context = $this->getEvaluationContext($evaluationId);
            if (!$context) {
                return [
                    'success' => false,
                    'message' => 'Evaluation context not found'
                ];
            }

            // Get all competency scores for this evaluation (with or without comments)
            $stmt = $this->db->prepare("
                SELECT cs.comments, cs.score, c.name as competency_name, c.weight,
                       e.employee_id, e.evaluator_id, e.overall_score,
                       emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                       eval.first_name as evaluator_first_name, eval.last_name as evaluator_last_name
                FROM competency_scores cs
                JOIN competencies c ON cs.competency_id = c.id
                JOIN evaluations e ON cs.evaluation_id = e.id
                JOIN users emp ON e.employee_id = emp.id
                JOIN users eval ON e.evaluator_id = eval.id
                WHERE cs.evaluation_id = ?
            ");
            $stmt->execute([$evaluationId]);
            $feedbackData = $stmt->fetchAll();
            
            if (empty($feedbackData)) {
                return [
                    'success' => false,
                    'message' => 'No competency scores found for this evaluation'
                ];
            }
            
            // Combine all feedback into one text with context
            $allFeedback = '';
            $competencyAnalysis = [];
            
            // Add context to the feedback for better AI analysis
            $contextualFeedback = $this->buildContextualFeedback($context, $feedbackData);
            
            foreach ($feedbackData as $feedback) {
                // Use comments if available, otherwise create score-based feedback
                $commentText = !empty($feedback['comments']) ? $feedback['comments'] : 
                    $this->generateScoreBasedFeedback($feedback['score'], $feedback['competency_name']);
                
                $allFeedback .= $commentText . ' ';
                
                // Analyze individual competency feedback with context
                $contextualComment = $this->addContextToComment($commentText, $context, $feedback);
                
                // REAL AI ANALYSIS - Use actual AI models to analyze text content
                $score = floatval($feedback['score']);
                
                // Use AI to analyze the actual text content, not just scores
                $aiSentiment = $this->analyzeSentimentWithAI($commentText);
                
                // Combine AI analysis with score context for better accuracy
                $sentiment = $this->combineAISentimentWithScore($aiSentiment, $score, $commentText);
                
                // Log AI analysis for debugging
                error_log("AI ANALYSIS: Text='{$commentText}' → AI Sentiment: {$sentiment['sentiment']} (Confidence: {$sentiment['confidence']}) Method: {$sentiment['method']}");
                
                // Debug log to verify the logic is working
                error_log("FIXED: Score {$score} → Sentiment: {$sentiment['sentiment']} (Confidence: {$sentiment['confidence']})");
                $competencyAnalysis[] = [
                    'competency_name' => $feedback['competency_name'],
                    'score' => $feedback['score'],
                    'weight' => $feedback['weight'],
                    'comments' => $commentText,
                    'contextual_comments' => $contextualComment,
                    'sentiment' => $sentiment['sentiment'],
                    'confidence' => $sentiment['confidence']
                ];
            }
            
            // Calculate weighted overall sentiment based on individual competencies
            $overallSentiment = $this->calculateWeightedOverallSentiment($competencyAnalysis, $context);
            
            // Generate summary with context
            $contextualOverallFeedback = $contextualFeedback . ' ' . $allFeedback;
            $summary = $this->summarizeText($contextualOverallFeedback);
            
            // Generate insights based on analysis
            $insights = $this->generateInsights($context, $competencyAnalysis, $overallSentiment);
            
            // Store analysis results with context
            $this->storeAnalysisResults($evaluationId, $overallSentiment, $summary, $competencyAnalysis, $context, $insights);
            
            return [
                'success' => true,
                'overall_sentiment' => $overallSentiment,
                'summary' => $summary,
                'competency_analysis' => $competencyAnalysis,
                'total_feedback_length' => strlen($allFeedback),
                'competencies_analyzed' => count($competencyAnalysis)
            ];
            
        } catch (Exception $e) {
            error_log("Competency feedback analysis error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error analyzing feedback: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get evaluation context for AI analysis
     */
    private function getEvaluationContext($evaluationId) {
        try {
            // First try to get existing context
            $stmt = $this->db->prepare("
                SELECT ac.*, e.overall_score, e.completed_at,
                       emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                       emp.position as employee_position, emp.department as employee_department,
                       eval.first_name as evaluator_first_name, eval.last_name as evaluator_last_name,
                       eval.position as evaluator_position, eval.department as evaluator_department
                FROM ai_analysis_context ac
                JOIN evaluations e ON ac.evaluation_id = e.id
                JOIN users emp ON e.employee_id = emp.id
                JOIN users eval ON e.evaluator_id = eval.id
                WHERE ac.evaluation_id = ?
            ");
            $stmt->execute([$evaluationId]);
            $result = $stmt->fetch();
            
            if ($result) {
                $result['employee_profile'] = json_decode($result['employee_profile'], true);
                $result['evaluator_profile'] = json_decode($result['evaluator_profile'], true);
                $result['evaluation_context'] = json_decode($result['evaluation_context'], true);
                $result['performance_history'] = json_decode($result['performance_history'], true);
                $result['organizational_context'] = json_decode($result['organizational_context'], true);
                return $result;
            }
            
            // If no context exists, create it on the fly
            return $this->createEvaluationContext($evaluationId);
            
        } catch (Exception $e) {
            error_log("Error getting evaluation context: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Build contextual feedback for AI analysis
     */
    private function buildContextualFeedback($context, $feedbackData) {
        if (!$context) return '';
        
        $contextualText = "Employee: {$context['employee_first_name']} {$context['employee_last_name']} ";
        $contextualText .= "({$context['employee_position']} in {$context['employee_department']}). ";
        $contextualText .= "Evaluator: {$context['evaluator_first_name']} {$context['evaluator_last_name']} ";
        $contextualText .= "({$context['evaluator_position']} in {$context['evaluator_department']}). ";
        
        if (isset($context['evaluation_context']['model_name'])) {
            $contextualText .= "Evaluation Model: {$context['evaluation_context']['model_name']}. ";
        }
        
        if (isset($context['performance_history']['average_historical_score'])) {
            $avgScore = $context['performance_history']['average_historical_score'];
            $contextualText .= "Historical average score: " . round($avgScore, 2) . ". ";
        }
        
        if (isset($context['organizational_context']['company_values'])) {
            $values = implode(', ', $context['organizational_context']['company_values']);
            $contextualText .= "Company values: {$values}. ";
        }
        
        return $contextualText;
    }
    
    /**
     * Add context to individual comments
     */
    private function addContextToComment($comment, $context, $feedback) {
        $contextualComment = "Competency: {$feedback['competency_name']} (Score: {$feedback['score']}/5). ";
        $contextualComment .= "Comment: {$comment}";
        
        return $contextualComment;
    }
    
    /**
     * Generate insights from analysis
     */
    private function generateInsights($context, $competencyAnalysis, $overallSentiment) {
        $insights = [];
        
        // Analyze sentiment patterns
        $positiveCount = 0;
        $negativeCount = 0;
        $neutralCount = 0;
        
        foreach ($competencyAnalysis as $analysis) {
            switch ($analysis['sentiment']) {
                case 'positive':
                    $positiveCount++;
                    break;
                case 'negative':
                    $negativeCount++;
                    break;
                case 'neutral':
                    $neutralCount++;
                    break;
            }
        }
        
        // Generate insights based on patterns
        if ($positiveCount > $negativeCount + $neutralCount) {
            $insights[] = [
                'type' => 'strength',
                'text' => 'Overall positive feedback indicates strong performance across competencies',
                'confidence' => 0.8,
                'priority' => 'medium'
            ];
        } elseif ($negativeCount > $positiveCount + $neutralCount) {
            $insights[] = [
                'type' => 'improvement_area',
                'text' => 'Multiple areas need improvement based on negative feedback patterns',
                'confidence' => 0.8,
                'priority' => 'high'
            ];
        }
        
        // Analyze score vs sentiment correlation
        $lowScoreHighSentiment = 0;
        $highScoreLowSentiment = 0;
        
        foreach ($competencyAnalysis as $analysis) {
            if ($analysis['score'] <= 2 && $analysis['sentiment'] === 'positive') {
                $lowScoreHighSentiment++;
            } elseif ($analysis['score'] >= 4 && $analysis['sentiment'] === 'negative') {
                $highScoreLowSentiment++;
            }
        }
        
        if ($lowScoreHighSentiment > 0) {
            $insights[] = [
                'type' => 'risk',
                'text' => 'Inconsistency detected: Low scores with positive sentiment may indicate evaluation bias',
                'confidence' => 0.7,
                'priority' => 'high'
            ];
        }
        
        if ($highScoreLowSentiment > 0) {
            $insights[] = [
                'type' => 'recommendation',
                'text' => 'High scores with negative sentiment suggest need for clearer evaluation criteria',
                'confidence' => 0.7,
                'priority' => 'medium'
            ];
        }
        
        return $insights;
    }
    
    /**
     * Store AI analysis results in database
     */
    private function storeAnalysisResults($evaluationId, $sentiment, $summary, $competencyAnalysis, $context = null, $insights = []) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_analysis_results (
                    evaluation_id, analysis_type, sentiment, sentiment_confidence, 
                    summary, original_length, summary_length, compression_ratio,
                    analysis_data, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                sentiment = VALUES(sentiment),
                sentiment_confidence = VALUES(sentiment_confidence),
                summary = VALUES(summary),
                original_length = VALUES(original_length),
                summary_length = VALUES(summary_length),
                compression_ratio = VALUES(compression_ratio),
                analysis_data = VALUES(analysis_data),
                updated_at = NOW()
            ");
            
            $analysisData = json_encode([
                'competency_analysis' => $competencyAnalysis,
                'analysis_timestamp' => date('Y-m-d H:i:s')
            ]);
            
            $stmt->execute([
                $evaluationId,
                'competency_feedback',
                $sentiment['sentiment'],
                $sentiment['confidence'],
                $summary['summary'],
                $summary['original_length'],
                $summary['summary_length'],
                $summary['compression_ratio'],
                $analysisData
            ]);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Error storing AI analysis results: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get AI analysis results for an evaluation
     */
    public function getAnalysisResults($evaluationId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM ai_analysis_results 
                WHERE evaluation_id = ? AND analysis_type = 'competency_feedback'
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$evaluationId]);
            $result = $stmt->fetch();
            
            if ($result) {
                $result['analysis_data'] = json_decode($result['analysis_data'], true);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error getting AI analysis results: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Make API request to Hugging Face
     */
    private function makeRequest($model, $data) {
        if (!$this->apiKey) {
            throw new Exception('Hugging Face API key not configured');
        }
        
        $url = $this->baseUrl . $model;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('CURL Error: ' . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception('HTTP Error: ' . $httpCode . ' - ' . $response);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . $response);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Generate readable and understandable summary
     */
    private function fallbackSummary($text, $maxLength) {
        // Clean up the text first
        $text = $this->cleanTextForSummary($text);
        
        // Extract key information for better summary
        $keyPoints = $this->extractKeyPoints($text);
        
        if (empty($keyPoints)) {
            return $this->createSimpleSummary($text, $maxLength);
        }
        
        // Create structured summary
        $summary = $this->createStructuredSummary($keyPoints, $maxLength);
        
        return $summary;
    }
    
    /**
     * Clean text for better summary generation
     */
    private function cleanTextForSummary($text) {
        // Remove redundant information
        $text = preg_replace('/Employee:.*?\./', '', $text);
        $text = preg_replace('/Evaluator:.*?\./', '', $text);
        $text = preg_replace('/Evaluation Model:.*?\./', '', $text);
        $text = preg_replace('/Company values:.*?\./', '', $text);
        $text = preg_replace('/Historical average score:.*?\./', '', $text);
        
        // Clean up multiple spaces and newlines
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * Extract key points from evaluation text
     */
    private function extractKeyPoints($text) {
        $keyPoints = [];
        
        // Look for performance indicators
        if (preg_match('/(excellent|outstanding|exceptional|superior)/i', $text)) {
            $keyPoints[] = 'Strong performance indicators found';
        }
        
        if (preg_match('/(good|satisfactory|meets expectations)/i', $text)) {
            $keyPoints[] = 'Meets performance standards';
        }
        
        if (preg_match('/(needs improvement|below standard|poor|inadequate)/i', $text)) {
            $keyPoints[] = 'Performance improvement needed';
        }
        
        if (preg_match('/(excellent|good|satisfactory).*?(communication|teamwork|leadership|technical)/i', $text)) {
            $keyPoints[] = 'Specific competency strengths identified';
        }
        
        if (preg_match('/(needs improvement|below standard).*?(communication|teamwork|leadership|technical)/i', $text)) {
            $keyPoints[] = 'Specific competency areas need development';
        }
        
        return $keyPoints;
    }
    
    /**
     * Create simple summary when no key points found
     */
    private function createSimpleSummary($text, $maxLength) {
        $sentences = preg_split('/[.!?]+/', $text);
        $summary = '';
        
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if (strlen($summary . $sentence) <= $maxLength && !empty($sentence)) {
                $summary .= $sentence . '. ';
            } else {
                break;
            }
        }
        
        return trim($summary) ?: substr($text, 0, $maxLength) . '...';
    }
    
    /**
     * Create structured, readable summary
     */
    private function createStructuredSummary($keyPoints, $maxLength) {
        $summary = '';
        
        // Start with overall assessment
        if (count($keyPoints) > 0) {
            $summary .= "Evaluation Summary: ";
        }
        
        // Add key points in readable format
        foreach ($keyPoints as $i => $point) {
            if (strlen($summary . $point) <= $maxLength - 10) {
                if ($i > 0) $summary .= " ";
                $summary .= $point;
                if ($i < count($keyPoints) - 1) $summary .= ".";
            } else {
                break;
            }
        }
        
        // Add conclusion if space allows
        if (strlen($summary) < $maxLength - 20) {
            if (strpos($summary, 'Strong performance') !== false) {
                $summary .= " Overall assessment indicates positive performance.";
            } elseif (strpos($summary, 'improvement needed') !== false) {
                $summary .= " Overall assessment indicates areas for development.";
            } else {
                $summary .= " Overall assessment indicates satisfactory performance.";
            }
        }
        
        return trim($summary);
    }
    
    /**
     * Post-process AI summary to make it more readable
     */
    private function postProcessSummary($summary) {
        // Clean up the summary
        $summary = trim($summary);
        
        // Remove redundant phrases
        $summary = preg_replace('/\b(Employee|Evaluator|Evaluation Model|Company values|Historical average score):[^.]*\./i', '', $summary);
        
        // Fix common AI summary issues
        $summary = preg_replace('/\b(The employee|The evaluator|The evaluation)\b/i', 'The evaluation', $summary);
        
        // Ensure proper sentence structure
        if (!preg_match('/[.!?]$/', $summary)) {
            $summary .= '.';
        }
        
        // Remove excessive repetition
        $summary = preg_replace('/(\b\w+\b)(\s+\1)+/i', '$1', $summary);
        
        // Capitalize first letter
        $summary = ucfirst($summary);
        
        return $summary;
    }
    
    /**
     * Get sentiment statistics for multiple evaluations
     */
    public function getSentimentStatistics($filters = []) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) as total_evaluations,
                    AVG(sentiment_confidence) as avg_confidence,
                    COUNT(CASE WHEN sentiment = 'positive' THEN 1 END) as positive_count,
                    COUNT(CASE WHEN sentiment = 'negative' THEN 1 END) as negative_count,
                    COUNT(CASE WHEN sentiment = 'neutral' THEN 1 END) as neutral_count
                FROM ai_analysis_results 
                WHERE analysis_type = 'competency_feedback'
            ";
            
            $params = [];
            $conditions = [];
            
            if (!empty($filters['date_from'])) {
                $conditions[] = "created_at >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $conditions[] = "created_at <= ?";
                $params[] = $filters['date_to'];
            }
            
            if (!empty($conditions)) {
                $sql .= " AND " . implode(" AND ", $conditions);
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetch();
            
        } catch (Exception $e) {
            error_log("Error getting sentiment statistics: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create evaluation context on the fly
     */
    private function createEvaluationContext($evaluationId) {
        try {
            // Get evaluation details
            $stmt = $this->db->prepare("
                SELECT e.*, ec.name as cycle_name, cm.name as model_name,
                       emp.first_name as employee_first_name, emp.last_name as employee_last_name,
                       emp.position as employee_position, emp.department as employee_department,
                       eval.first_name as evaluator_first_name, eval.last_name as evaluator_last_name,
                       eval.position as evaluator_position, eval.department as evaluator_department
                FROM evaluations e
                JOIN evaluation_cycles ec ON e.cycle_id = ec.id
                JOIN competency_models cm ON e.model_id = cm.id
                JOIN users emp ON e.employee_id = emp.id
                JOIN users eval ON e.evaluator_id = eval.id
                WHERE e.id = ?
            ");
            $stmt->execute([$evaluationId]);
            $evaluation = $stmt->fetch();
            
            if (!$evaluation) {
                return null;
            }
            
            // Create context object
            $context = [
                'evaluation_id' => $evaluationId,
                'overall_score' => $evaluation['overall_score'],
                'completed_at' => $evaluation['completed_at'],
                'employee_first_name' => $evaluation['employee_first_name'],
                'employee_last_name' => $evaluation['employee_last_name'],
                'employee_position' => $evaluation['employee_position'],
                'employee_department' => $evaluation['employee_department'],
                'evaluator_first_name' => $evaluation['evaluator_first_name'],
                'evaluator_last_name' => $evaluation['evaluator_last_name'],
                'evaluator_position' => $evaluation['evaluator_position'],
                'evaluator_department' => $evaluation['evaluator_department'],
                'employee_profile' => [
                    'name' => $evaluation['employee_first_name'] . ' ' . $evaluation['employee_last_name'],
                    'position' => $evaluation['employee_position'],
                    'department' => $evaluation['employee_department']
                ],
                'evaluator_profile' => [
                    'name' => $evaluation['evaluator_first_name'] . ' ' . $evaluation['evaluator_last_name'],
                    'position' => $evaluation['evaluator_position'],
                    'department' => $evaluation['evaluator_department']
                ],
                'evaluation_context' => [
                    'cycle_name' => $evaluation['cycle_name'],
                    'model_name' => $evaluation['model_name'],
                    'overall_score' => $evaluation['overall_score']
                ],
                'performance_history' => [
                    'average_score' => $evaluation['overall_score'],
                    'evaluation_count' => 1
                ],
                'organizational_context' => [
                    'company_values' => ['Excellence', 'Innovation', 'Collaboration', 'Integrity'],
                    'focus_areas' => ['Performance', 'Development', 'Growth']
                ]
            ];
            
            return $context;
            
        } catch (Exception $e) {
            error_log("Error creating evaluation context: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Calculate weighted overall sentiment based on individual competencies
     */
    private function calculateWeightedOverallSentiment($competencyAnalysis, $context) {
        if (empty($competencyAnalysis)) {
            return ['sentiment' => 'neutral', 'confidence' => 0.5];
        }
        
        $totalWeight = 0;
        $weightedSentiment = 0;
        $sentimentCounts = ['positive' => 0, 'negative' => 0, 'neutral' => 0];
        
        foreach ($competencyAnalysis as $competency) {
            $weight = floatval($competency['weight']);
            $totalWeight += $weight;
            
            // Convert sentiment to numeric value
            $sentimentValue = 0;
            switch ($competency['sentiment']) {
                case 'positive':
                    $sentimentValue = 1;
                    $sentimentCounts['positive']++;
                    break;
                case 'negative':
                    $sentimentValue = -1;
                    $sentimentCounts['negative']++;
                    break;
                case 'neutral':
                default:
                    $sentimentValue = 0;
                    $sentimentCounts['neutral']++;
                    break;
            }
            
            $weightedSentiment += $sentimentValue * $weight;
        }
        
        if ($totalWeight == 0) {
            return ['sentiment' => 'neutral', 'confidence' => 0.5];
        }
        
        $averageWeightedSentiment = $weightedSentiment / $totalWeight;
        
        // Determine overall sentiment with more accurate thresholds
        if ($averageWeightedSentiment > 0.3) {
            $sentiment = 'positive';
        } elseif ($averageWeightedSentiment < -0.2) {
            $sentiment = 'negative';
        } else {
            $sentiment = 'neutral';
        }
        
        // Calculate confidence based on consistency and score distribution
        $totalCompetencies = count($competencyAnalysis);
        $dominantSentiment = array_keys($sentimentCounts, max($sentimentCounts))[0];
        $consistency = $sentimentCounts[$dominantSentiment] / $totalCompetencies;
        
        // Base confidence on consistency and overall score
        $baseConfidence = 0.7 + ($consistency * 0.25);
        
        // Adjust confidence based on overall score
        $overallScore = $context['overall_score'] ?? 3.0;
        if ($overallScore >= 4.0) {
            $scoreAdjustment = 0.1; // Higher confidence for high scores
        } elseif ($overallScore <= 2.0) {
            $scoreAdjustment = 0.1; // Higher confidence for very low scores
        } else {
            $scoreAdjustment = 0.0; // Standard confidence for mid-range scores
        }
        
        $confidence = min(0.98, $baseConfidence + $scoreAdjustment);
        
        return [
            'sentiment' => $sentiment,
            'confidence' => $confidence,
            'weighted_score' => $averageWeightedSentiment,
            'sentiment_breakdown' => $sentimentCounts
        ];
    }
    
    /**
     * Get sentiment based on score patterns in the text
     */
    private function getScoreBasedSentiment($text) {
        $text = strtolower($text);
        
        // Positive indicators
        $positiveWords = ['excellent', 'outstanding', 'brilliant', 'fantastic', 'remarkable', 'exceptional', 'great', 'good', 'solid', 'well done', 'proud', 'achievement', 'exceeds', 'impressive', 'commendable'];
        $positiveCount = 0;
        foreach ($positiveWords as $word) {
            $positiveCount += substr_count($text, $word);
        }
        
        // Negative indicators  
        $negativeWords = ['terrible', 'awful', 'dismal', 'unacceptable', 'catastrophic', 'failure', 'disaster', 'crisis', 'concerning', 'disappointing', 'poor', 'unsatisfactory', 'below', 'falling short', 'needs improvement'];
        $negativeCount = 0;
        foreach ($negativeWords as $word) {
            $negativeCount += substr_count($text, $word);
        }
        
        // Score-based sentiment (look for score patterns) - More accurate thresholds
        if (preg_match('/(\d+\.?\d*)\/5/', $text, $matches)) {
            $score = floatval($matches[1]);
            if ($score >= 4.5) {
                return ['sentiment' => 'positive', 'confidence' => 0.85];
            } elseif ($score >= 4.0) {
                return ['sentiment' => 'positive', 'confidence' => 0.80];
            } elseif ($score >= 3.5) {
                return ['sentiment' => 'neutral', 'confidence' => 0.75];
            } elseif ($score >= 3.0) {
                return ['sentiment' => 'neutral', 'confidence' => 0.70];
            } elseif ($score >= 2.5) {
                return ['sentiment' => 'negative', 'confidence' => 0.75];
            } elseif ($score >= 2.0) {
                return ['sentiment' => 'negative', 'confidence' => 0.80];
            } else {
                return ['sentiment' => 'negative', 'confidence' => 0.85];
            }
        }
        
        // Word-based sentiment
        if ($positiveCount > $negativeCount) {
            return ['sentiment' => 'positive', 'confidence' => min(0.9, 0.6 + ($positiveCount * 0.1))];
        } elseif ($negativeCount > $positiveCount) {
            return ['sentiment' => 'negative', 'confidence' => min(0.9, 0.6 + ($negativeCount * 0.1))];
        }
        
        return ['sentiment' => 'neutral', 'confidence' => 0.6];
    }
    
    /**
     * Analyze sentiment using real AI models
     */
    private function analyzeSentimentWithAI($text) {
        if (empty($text) || strlen($text) < 10) {
            return ['sentiment' => 'neutral', 'confidence' => 0.5, 'method' => 'fallback'];
        }
        
        try {
            // Use multiple AI models for better accuracy
            $sentimentResults = [];
            
            // Primary sentiment analysis
            $primaryResult = $this->callHuggingFaceAPI(SENTIMENT_MODEL, $text);
            if ($primaryResult && isset($primaryResult[0]) && is_array($primaryResult[0])) {
                // Find the highest scoring sentiment
                $bestResult = null;
                $highestScore = 0;
                foreach ($primaryResult[0] as $result) {
                    if ($result['score'] > $highestScore) {
                        $highestScore = $result['score'];
                        $bestResult = $result;
                    }
                }
                if ($bestResult) {
                    $sentimentResults[] = $this->processSentimentResult($bestResult);
                }
            }
            
            // Secondary emotion analysis for context
            $emotionResult = $this->callHuggingFaceAPI(EMOTION_MODEL, $text);
            if ($emotionResult && isset($emotionResult[0])) {
                $sentimentResults[] = $this->processEmotionResult($emotionResult[0]);
            }
            
            // Combine results for final sentiment
            return $this->combineSentimentResults($sentimentResults);
            
        } catch (Exception $e) {
            error_log("AI Sentiment Analysis Error: " . $e->getMessage());
            return ['sentiment' => 'neutral', 'confidence' => 0.5, 'method' => 'error_fallback'];
        }
    }
    
    /**
     * Combine AI sentiment with score context
     */
    private function combineAISentimentWithScore($aiSentiment, $score, $text) {
        // If AI is very confident, trust it more
        if ($aiSentiment['confidence'] >= 0.8) {
            return $aiSentiment;
        }
        
        // If AI is uncertain, use score as additional context
        $scoreWeight = 0.3; // 30% weight to score
        $aiWeight = 0.7;    // 70% weight to AI analysis
        
        // Score-based sentiment for context
        $scoreSentiment = $this->getScoreBasedSentiment($text);
        
        // Combine AI and score-based sentiment
        $combinedConfidence = ($aiSentiment['confidence'] * $aiWeight) + ($scoreSentiment['confidence'] * $scoreWeight);
        
        // If AI and score agree, increase confidence
        if ($aiSentiment['sentiment'] === $scoreSentiment['sentiment']) {
            $combinedConfidence = min(0.95, $combinedConfidence + 0.1);
        }
        
        return [
            'sentiment' => $aiSentiment['sentiment'],
            'confidence' => $combinedConfidence,
            'method' => 'ai_score_combined'
        ];
    }
    
    /**
     * Process sentiment result from Hugging Face API
     */
    private function processSentimentResult($result) {
        if (!isset($result['label']) || !isset($result['score'])) {
            return ['sentiment' => 'neutral', 'confidence' => 0.5];
        }
        
        $label = strtolower($result['label']);
        $score = floatval($result['score']);
        
        // Map Hugging Face labels to our sentiment
        $sentimentMap = [
            'positive' => 'positive',
            'negative' => 'negative',
            'neutral' => 'neutral',
            'label_0' => 'negative',
            'label_1' => 'neutral', 
            'label_2' => 'positive'
        ];
        
        $sentiment = $sentimentMap[$label] ?? 'neutral';
        
        return [
            'sentiment' => $sentiment,
            'confidence' => $score,
            'raw_label' => $label
        ];
    }
    
    /**
     * Process emotion result for additional context
     */
    private function processEmotionResult($result) {
        if (!isset($result['label']) || !isset($result['score'])) {
            return ['sentiment' => 'neutral', 'confidence' => 0.5];
        }
        
        $emotion = strtolower($result['label']);
        $score = floatval($result['score']);
        
        // Map emotions to sentiment
        $emotionToSentiment = [
            'joy' => 'positive',
            'love' => 'positive',
            'optimism' => 'positive',
            'anger' => 'negative',
            'sadness' => 'negative',
            'fear' => 'negative',
            'disgust' => 'negative',
            'surprise' => 'neutral'
        ];
        
        $sentiment = $emotionToSentiment[$emotion] ?? 'neutral';
        
        return [
            'sentiment' => $sentiment,
            'confidence' => $score * 0.8, // Reduce confidence for emotion-based sentiment
            'emotion' => $emotion
        ];
    }
    
    /**
     * Combine multiple sentiment results
     */
    private function combineSentimentResults($results) {
        if (empty($results)) {
            return ['sentiment' => 'neutral', 'confidence' => 0.5];
        }
        
        $sentimentCounts = ['positive' => 0, 'negative' => 0, 'neutral' => 0];
        $totalConfidence = 0;
        
        foreach ($results as $result) {
            $sentimentCounts[$result['sentiment']]++;
            $totalConfidence += $result['confidence'];
        }
        
        // Get dominant sentiment
        $dominantSentiment = array_keys($sentimentCounts, max($sentimentCounts))[0];
        $averageConfidence = $totalConfidence / count($results);
        
        // If results are consistent, increase confidence
        $consistency = max($sentimentCounts) / count($results);
        $finalConfidence = $averageConfidence * (0.7 + 0.3 * $consistency);
        
        return [
            'sentiment' => $dominantSentiment,
            'confidence' => min(0.95, $finalConfidence),
            'method' => 'multi_model_combined'
        ];
    }
    
    /**
     * Call Hugging Face API for AI analysis
     */
    private function callHuggingFaceAPI($model, $text) {
        $url = HUGGINGFACE_BASE_URL . $model;
        
        $data = [
            'inputs' => $text,
            'options' => [
                'wait_for_model' => true,
                'use_cache' => false
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . HUGGINGFACE_API_KEY,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, AI_ANALYSIS_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("API Error: HTTP $httpCode - $response");
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON Decode Error: " . json_last_error_msg());
        }
        
        return $result;
    }
    
    /**
     * Generate score-based feedback when no comments are available
     */
    private function generateScoreBasedFeedback($score, $competencyName) {
        $score = floatval($score);
        
        if ($score >= 4.5) {
            $positive_feedback = [
                "Outstanding work in {$competencyName}! Truly exceptional performance that sets a great example.",
                "Absolutely brilliant in {$competencyName}. This level of excellence is truly impressive and inspiring.",
                "Fantastic results in {$competencyName}. You've exceeded all expectations and delivered outstanding quality.",
                "Remarkable performance in {$competencyName}. Your dedication and skill are truly commendable."
            ];
            return $positive_feedback[array_rand($positive_feedback)];
        } elseif ($score >= 4.0) {
            $good_feedback = [
                "Great work in {$competencyName}. You've shown strong competency and should be proud of this achievement.",
                "Solid performance in {$competencyName}. You're doing well and have a good foundation to build upon.",
                "Good job in {$competencyName}. You're meeting expectations and showing positive progress.",
                "Well done in {$competencyName}. You're on the right track and showing good potential."
            ];
            return $good_feedback[array_rand($good_feedback)];
        } elseif ($score >= 3.5) {
            $neutral_feedback = [
                "Adequate performance in {$competencyName}. You're meeting the basic requirements but there's room for improvement.",
                "Satisfactory work in {$competencyName}. You're doing okay but could definitely do better with more effort.",
                "Average performance in {$competencyName}. You're getting by but need to step up your game.",
                "Acceptable results in {$competencyName}. You're meeting minimum standards but should aim higher."
            ];
            return $neutral_feedback[array_rand($neutral_feedback)];
        } elseif ($score >= 3.0) {
            $neutral_feedback = [
                "Satisfactory performance in {$competencyName}. You're meeting the basic requirements with room for growth.",
                "Adequate work in {$competencyName}. You're doing okay but could improve with more focus and effort.",
                "Acceptable results in {$competencyName}. You're meeting minimum standards and have potential for development.",
                "Standard performance in {$competencyName}. You're getting by but should aim to exceed expectations."
            ];
            return $neutral_feedback[array_rand($neutral_feedback)];
        } elseif ($score >= 2.5) {
            $below_feedback = [
                "Below expectations in {$competencyName}. You need to improve significantly to meet the required standards.",
                "Unsatisfactory performance in {$competencyName}. This level of work requires immediate attention and development.",
                "Poor results in {$competencyName}. You're not meeting the basic requirements and need serious improvement.",
                "Concerning performance in {$competencyName}. This is below acceptable standards and needs urgent attention."
            ];
            return $below_feedback[array_rand($below_feedback)];
        } else {
            $critical_feedback = [
                "Catastrophic failure in {$competencyName}. This is a complete disaster and requires emergency action.",
                "Total failure in {$competencyName}. You've completely failed to meet any standards and this is a crisis.",
                "Complete breakdown in {$competencyName}. This is an absolute failure and needs immediate drastic measures.",
                "Utter failure in {$competencyName}. This is the worst possible performance and requires urgent intervention."
            ];
            return $critical_feedback[array_rand($critical_feedback)];
        }
    }
}
?>
