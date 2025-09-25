<?php
/**
 * Helper function to handle redirects when headers may have already been sent
 */
function safeRedirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit;
    } else {
        echo '<script>window.location.href = "' . htmlspecialchars($url) . '";</script>';
        exit;
    }
}

/**
 * Helper function to check authentication and redirect if needed
 */
function checkAuth($auth, $requiredPermission = null) {
    if (!$auth->isLoggedIn()) {
        safeRedirect('auth/login.php');
    }
    
    if ($requiredPermission && !$auth->hasPermission($requiredPermission)) {
        echo '<div class="alert alert-danger">You do not have permission to view this page.</div>';
        exit;
    }
}
?>
