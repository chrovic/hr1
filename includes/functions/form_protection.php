<?php
/**
 * Form Protection Utilities
 * Prevents duplicate form submissions on page refresh
 */

class FormProtection {
    
    /**
     * Generate a unique token for form protection
     */
    public static function generateToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Store token in session
     */
    public static function storeToken($formName, $token) {
        if (!isset($_SESSION['form_tokens'])) {
            $_SESSION['form_tokens'] = [];
        }
        $_SESSION['form_tokens'][$formName] = $token;
    }
    
    /**
     * Get stored token
     */
    public static function getToken($formName) {
        return $_SESSION['form_tokens'][$formName] ?? null;
    }
    
    /**
     * Validate and consume token (prevents reuse)
     */
    public static function validateAndConsumeToken($formName, $submittedToken) {
        $storedToken = self::getToken($formName);
        
        if (!$storedToken || !$submittedToken) {
            return false;
        }
        
        if (!hash_equals($storedToken, $submittedToken)) {
            return false;
        }
        
        // Consume the token (remove it)
        unset($_SESSION['form_tokens'][$formName]);
        return true;
    }
    
    /**
     * Method 1: POST-Redirect-GET (PRG) Pattern
     * Redirect after successful POST to prevent resubmission
     */
    public static function redirectAfterPost($redirectUrl, $message = '', $messageType = 'success') {
        if ($message) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $messageType;
        }
        
        header("Location: $redirectUrl");
        exit;
    }
    
    /**
     * Get and clear flash messages
     */
    public static function getFlashMessage() {
        $message = $_SESSION['flash_message'] ?? '';
        $type = $_SESSION['flash_type'] ?? 'success';
        
        if ($message) {
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
        
        return ['message' => $message, 'type' => $type];
    }
    
    /**
     * Method 2: Check for duplicate submissions using database
     */
    public static function checkDuplicateSubmission($action, $data, $userId = null) {
        $db = getDB();
        
        // Create a hash of the submission data
        $dataHash = hash('sha256', serialize($data));
        
        // Check if this exact submission was made recently (within last 5 minutes)
        $stmt = $db->prepare("
            SELECT id FROM form_submissions 
            WHERE action = ? AND data_hash = ? AND user_id = ? 
            AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        
        $stmt->execute([$action, $dataHash, $userId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            return true; // Duplicate found
        }
        
        // Record this submission
        $stmt = $db->prepare("
            INSERT INTO form_submissions (action, data_hash, user_id, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$action, $dataHash, $userId]);
        
        return false; // Not a duplicate
    }
    
    /**
     * Method 3: JavaScript prevention
     */
    public static function getJavaScriptProtection() {
        return "
        <script>
        // Prevent double form submission
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[method=\"post\"]');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type=\"submit\"], input[type=\"submit\"]');
                    
                    if (submitBtn && submitBtn.disabled) {
                        e.preventDefault();
                        return false;
                    }
                    
                    // Disable submit button
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.value = 'Processing...';
                        
                        // Re-enable after 3 seconds as fallback
                        setTimeout(() => {
                            submitBtn.disabled = false;
                            submitBtn.value = submitBtn.getAttribute('data-original-value') || 'Submit';
                        }, 3000);
                    }
                });
            });
        });
        </script>";
    }
    
    /**
     * Method 4: Session-based duplicate prevention
     */
    public static function preventDuplicateSession($action, $data) {
        $sessionKey = 'last_submission_' . $action;
        $dataHash = hash('sha256', serialize($data));
        
        // Check if this is the same submission as last time
        if (isset($_SESSION[$sessionKey])) {
            if ($_SESSION[$sessionKey] === $dataHash) {
                return true; // Duplicate
            }
        }
        
        // Store this submission
        $_SESSION[$sessionKey] = $dataHash;
        return false; // Not a duplicate
    }
}

/**
 * Create form_submissions table for duplicate checking
 */
function createFormSubmissionsTable() {
    $db = getDB();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS form_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        action VARCHAR(100) NOT NULL,
        data_hash VARCHAR(64) NOT NULL,
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_action_hash (action, data_hash),
        INDEX idx_user_time (user_id, created_at)
    )";
    
    $db->exec($sql);
}
?>


