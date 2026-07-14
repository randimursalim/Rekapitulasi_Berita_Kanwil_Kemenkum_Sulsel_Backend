<?php
/**
 * Centralized Database Configuration
 * 
 * This file contains all database connection settings.
 * To change database credentials, modify only the values below.
 */

// Load konfigurasi secure database dari aplikasi utama
require_once __DIR__ . '/../../../config/database_secure.php';
$secure_config = DatabaseConfig::getConfig();

// Database Configuration (Dinamis dari .env)
$db_config = [
    'host' => $secure_config['host'],
    'username' => $secure_config['username'],
    'password' => $secure_config['password'],
    'database' => $secure_config['dbname'], // Menunjuk ke 1 database yang sama
    'port' => $secure_config['port'],
    'charset' => 'utf8'
];

/**
 * Create and return a database connection
 * 
 * @return mysqli Database connection object
 * @throws Exception If connection fails
 */
function getDatabaseConnection()
{
    global $db_config;

    $conn = new mysqli(
        $db_config['host'],
        $db_config['username'],
        $db_config['password'],
        $db_config['database'],
        $db_config['port'] ?? 3306
    );

    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset($db_config['charset']);

    return $conn;
}

/**
 * Get database configuration array
 * 
 * @return array Database configuration
 */
function getDatabaseConfig()
{
    global $db_config;
    return $db_config;
}

// 20-Minute Session Timeout Mechanism
if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $timeout_duration = 5400; // 20 minutes in seconds
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > $timeout_duration) {
            session_unset();
            session_destroy();

            // Calculate web path to the project root dynamically
            $project_root_dir = str_replace('\\', '/', dirname(__DIR__));
            $doc_root = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
            $web_path = str_replace($doc_root, '', $project_root_dir);
            $login_url = $web_path . '/login.php?timeout=1';

            // Redirect to login page
            if (!headers_sent()) {
                header("Location: " . $login_url);
                exit();
            } else {
                echo '<script>window.location.href="' . $login_url . '";</script>';
                exit();
            }
        }
    }
    $_SESSION['last_activity'] = time();
}
?>