<?php
// Session configuration untuk mengatasi permission error
class CustomSessionHandler {
    private $sessionPath;
    
    public function __construct() {
        $this->sessionPath = __DIR__ . '/../storage/sessions';
        $this->ensureSessionDirectory();
    }
    
    private function ensureSessionDirectory() {
        if (!is_dir($this->sessionPath)) {
            mkdir($this->sessionPath, 0755, true);
        }
        
        // Set permission jika bisa
        if (is_writable($this->sessionPath)) {
            ini_set('session.save_path', $this->sessionPath);
        } else {
            // Fallback ke memory jika folder tidak writable
            ini_set('session.save_handler', 'memcached');
        }
    }
    
    public function startSession() {
        // Set session configuration
        ini_set('session.cookie_lifetime', 0);
        ini_set('session.cookie_secure', 0);
        ini_set('session.use_strict_mode', 1);
        
        return session_start();
    }
}

// Gunakan custom session handler
$sessionHandler = new CustomSessionHandler();
$sessionHandler->startSession();
?>
