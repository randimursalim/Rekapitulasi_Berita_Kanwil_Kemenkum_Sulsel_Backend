<?php
// config/database_secure.php
// Database configuration dengan environment variables

class DatabaseConfig {
    private static $config = null;
    
    public static function getConfig() {
        if (self::$config === null) {
            self::$config = [
                'host' => self::getEnv('DB_HOST', 'localhost'),
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
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $conn = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            return $conn;
        } catch (PDOException $e) {
            // Log error tanpa expose credentials
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check configuration.");
        }
    }
}
