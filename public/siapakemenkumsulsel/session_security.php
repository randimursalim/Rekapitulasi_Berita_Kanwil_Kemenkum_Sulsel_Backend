<?php
/**
 * Session Security Configuration
 * Include this file at the top of all pages that use sessions
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session settings
    ini_set('session.cookie_httponly', 1);           // Prevent XSS
    ini_set('session.use_only_cookies', 1);          // Prevent session fixation
    ini_set('session.cookie_secure', 1);              // HTTPS only (uncomment for production)
    ini_set('session.cookie_samesite', 'Strict');     // CSRF protection
    
    // Session timeout disabled for easier testing
    ini_set('session.gc_maxlifetime', 86400);        // 24 hours
    
    // Start the session
    session_start();
    
    // Regenerate session ID on login for security
    if (!isset($_SESSION['session_regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['session_regenerated'] = true;
    }
    
    // Session timeout check disabled for easier testing
    // Users can stay logged in without automatic logout
    
    // Update last activity time (kept for potential future use)
    $_SESSION['last_activity'] = time();
}
?>
