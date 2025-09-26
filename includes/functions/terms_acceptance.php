<?php

class TermsAcceptance {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Check if user has accepted terms and conditions
     */
    public function hasUserAcceptedTerms($userId) {
        try {
            $stmt = $this->db->prepare("SELECT accepted FROM terms_acceptance WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            if ($result) {
                return (bool) $result['accepted'];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Error checking terms acceptance: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Record user acceptance of terms and conditions
     */
    public function acceptTerms($userId, $ipAddress = null, $userAgent = null) {
        try {
            // Get client IP if not provided
            if (!$ipAddress) {
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            }
            
            // Get user agent if not provided
            if (!$userAgent) {
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            }
            
            // Check if record exists
            $stmt = $this->db->prepare("SELECT id FROM terms_acceptance WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            if ($result) {
                // Update existing record
                $stmt = $this->db->prepare("UPDATE terms_acceptance SET accepted = 1, accepted_at = NOW(), ip_address = ?, user_agent = ? WHERE user_id = ?");
                return $stmt->execute([$ipAddress, $userAgent, $userId]);
            } else {
                // Insert new record
                $stmt = $this->db->prepare("INSERT INTO terms_acceptance (user_id, accepted, accepted_at, ip_address, user_agent) VALUES (?, 1, NOW(), ?, ?)");
                return $stmt->execute([$userId, $ipAddress, $userAgent]);
            }
        } catch (Exception $e) {
            error_log("Error accepting terms: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get terms acceptance status for user
     */
    public function getTermsStatus($userId) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM terms_acceptance WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            
            if ($result) {
                return $result;
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error getting terms status: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Check if terms acceptance is required for user
     */
    public function isTermsAcceptanceRequired($userId) {
        return !$this->hasUserAcceptedTerms($userId);
    }
}
