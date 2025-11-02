<?php
// Secure Session Configuration
require_once __DIR__ . '/database_secure.php';

class SecureSessionHandler {
    private $sessionPath;
    private $isSecure;
    private $sessionLifetime;
    
    public function __construct() {
        $this->sessionPath = __DIR__ . '/../storage/sessions';
        $this->isSecure = $this->isHttps();
        $this->sessionLifetime = $this->getEnv('SESSION_LIFETIME', 3600); // 1 hour default
        $this->ensureSessionDirectory();
        $this->configureSessionSecurity();
    }
    
    private function isHttps() {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
               isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ||
               isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443;
    }
    
    private function getEnv($key, $default = null) {
        // Cek environment variable
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        // Cek dari $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        // Cek dari file .env
        $envFile = __DIR__ . '/env.example';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($envKey, $envValue) = explode('=', $line, 2);
                    if (trim($envKey) === $key) {
                        return trim($envValue);
                    }
                }
            }
        }
        
        return $default;
    }
    
    private function ensureSessionDirectory() {
        if (!is_dir($this->sessionPath)) {
            mkdir($this->sessionPath, 0700, true); // More restrictive permission
        }
        
        // Set permission jika bisa
        if (is_writable($this->sessionPath)) {
            ini_set('session.save_path', $this->sessionPath);
        } else {
            // Fallback ke memory jika folder tidak writable
            ini_set('session.save_handler', 'memcached');
        }
    }
    
    private function configureSessionSecurity() {
        // Session cookie security
        ini_set('session.cookie_lifetime', $this->sessionLifetime);
        ini_set('session.cookie_secure', $this->isSecure ? 1 : 0);
        ini_set('session.cookie_httponly', 1); // Prevent XSS
        ini_set('session.cookie_samesite', 'Lax'); // CSRF protection - Lax lebih fleksibel untuk navigasi normal
        
        // Session security
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        
        // Session regeneration
        ini_set('session.gc_maxlifetime', $this->sessionLifetime);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 100);
        
        // Session name security
        ini_set('session.name', 'REKAP_SESSION');
        
        // Additional security
        ini_set('session.entropy_length', 32);
        ini_set('session.hash_function', 'sha256');
        ini_set('session.hash_bits_per_character', 5);
    }
    
    public function startSession() {
        // Regenerate session ID untuk security
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            
            // Regenerate session ID setiap 5 menit
            if (!isset($_SESSION['last_regeneration'])) {
                $_SESSION['last_regeneration'] = time();
            } elseif (time() - $_SESSION['last_regeneration'] > 300) {
                session_regenerate_id(true);
                $_SESSION['last_regeneration'] = time();
            }
        }
        
        return true;
    }
    
    public function destroySession() {
        // Clear all session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
}

// Gunakan secure session handler
$sessionHandler = new SecureSessionHandler();
$sessionHandler->startSession();
?>
