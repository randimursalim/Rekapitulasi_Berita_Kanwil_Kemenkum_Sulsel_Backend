<?php
// config/database_secure.php
// Database configuration dengan environment variables

class DatabaseConfig {
    private static $config = null;
    
    public static function getConfig() {
        if (self::$config === null) {
            $host = self::getEnv('DB_HOST', 'localhost');
            
            // Auto-detect port berdasarkan environment
            // Di hosting biasanya port 3306 (default MySQL)
            // Di localhost bisa berbeda (misal 3307 jika ada konflik)
            $defaultPort = self::isLocalhost() ? '3307' : '3306';
            $port = self::getEnv('DB_PORT', $defaultPort);
            
            self::$config = [
                'host' => $host,
                'port' => $port,
                'dbname' => self::getEnv('DB_NAME', 'rekap_konten'),
                'username' => self::getEnv('DB_USER', 'root'),
                'password' => self::getEnv('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT => false
                ]
            ];
        }
        return self::$config;
    }
    
    private static function isLocalhost() {
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        
        return (
            strpos($serverName, 'localhost') !== false ||
            strpos($serverName, '127.0.0.1') !== false ||
            strpos($httpHost, 'localhost') !== false ||
            strpos($httpHost, '127.0.0.1') !== false ||
            strpos($requestUri, '/rekap-konten/public') !== false ||
            strpos($scriptName, '/rekap-konten/public') !== false
        );
    }
    
    private static function getEnv($key, $default = null) {
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
        $envFile = __DIR__ . '/.env';
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
    
    public static function createConnection() {
        $config = self::getConfig();
        
        try {
            // Gunakan port jika bukan default atau jika host mengandung port
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
            $conn = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            return $conn;
        } catch (PDOException $e) {
            // Log error tanpa expose credentials
            error_log("Database connection failed: " . $e->getMessage());
            
            // Untuk development, tampilkan error detail jika APP_DEBUG aktif
            $debug = self::getEnv('APP_DEBUG', false);
            if ($debug === 'true' || $debug === true) {
                $errorMsg = "Database connection failed.\n\n";
                $errorMsg .= "Error: " . $e->getMessage() . "\n\n";
                $errorMsg .= "Configuration:\n";
                $errorMsg .= "- Host: " . $config['host'] . "\n";
                $errorMsg .= "- Port: " . $config['port'] . "\n";
                $errorMsg .= "- Database: " . $config['dbname'] . "\n";
                $errorMsg .= "- User: " . $config['username'] . "\n";
                $errorMsg .= "\nTroubleshooting Steps:\n";
                $errorMsg .= "1. Open XAMPP Control Panel\n";
                $errorMsg .= "2. Make sure MySQL service is STARTED (green status)\n";
                $errorMsg .= "3. If MySQL won't start, check the error log in XAMPP\n";
                $errorMsg .= "4. Verify database '" . $config['dbname'] . "' exists in phpMyAdmin\n";
                $errorMsg .= "5. Check if MySQL port " . $config['port'] . " is available\n";
                $errorMsg .= "6. Try changing DB_HOST to '127.0.0.1' in config/.env\n";
                $errorMsg .= "7. Verify file config/.env exists and is configured correctly";
                die("<pre>" . htmlspecialchars($errorMsg) . "</pre>");
            }
            
            die("Database connection failed. Please check configuration.");
        }
    }
}
