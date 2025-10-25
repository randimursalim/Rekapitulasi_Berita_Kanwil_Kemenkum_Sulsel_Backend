<?php
// app/controllers/AuthController.php
require_once __DIR__ . '/../models/UserModel.php';

class AuthController
{
    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    // === Halaman login ===
    public function login()
    {
        // Jika user sudah login, redirect ke dashboard
        if (isset($_SESSION['user'])) {
            header('Location: index.php?page=dashboard');
            exit;
        }

        // Tampilkan form login
        include __DIR__ . '/../views/pages/login.php';
    }

    // === Proses login ===
    public function prosesLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validasi input
            if (empty($username) || empty($password)) {
                $error = "Username dan password harus diisi!";
                include __DIR__ . '/../views/pages/login.php';
                return;
            }

            // Ambil user dari database
            $user = $this->model->getUserByUsername($username);

                if ($user && $this->model->verifyPassword($password, $user['password'])) {
                    // Regenerate session ID untuk security
                    session_regenerate_id(true);
                    
                    // Login berhasil
                    $_SESSION['user'] = [
                        'id' => $user['id_pengguna'],
                        'username' => $user['username'],
                        'nama' => $user['nama'],
                        'role' => $user['role'],
                        'foto' => $user['foto']
                    ];
                    
                    // Set waktu aktivitas awal
                    $_SESSION['last_activity'] = time();
                    $_SESSION['login_time'] = time();
                    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

                    // Update last login
                    $this->model->updateLastLogin($user['id_pengguna']);

                    // Redirect ke dashboard
                    header('Location: index.php?page=dashboard');
                    exit;
            } else {
                $error = "Username atau password salah!";
                include __DIR__ . '/../views/pages/login.php';
            }
        } else {
            header('Location: index.php?page=login');
            exit;
        }
    }

    // === Logout ===
    public function logout()
    {
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
        header('Location: index.php?page=login');
        exit;
    }

    // === Middleware untuk cek login ===
    public static function requireLogin()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?page=login');
            exit;
        }
        
        // Cek session security
        self::validateSessionSecurity();
        
        // Cek session timeout (15 menit)
        self::checkSessionTimeout();
    }
    
    // === Validasi session security ===
    public static function validateSessionSecurity()
    {
        // Cek IP address consistency
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
            self::destroySessionAndRedirect('IP address changed');
            return;
        }
        
        // Cek User Agent consistency
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown')) {
            self::destroySessionAndRedirect('User agent changed');
            return;
        }
        
        // Cek session hijacking (login time terlalu lama)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 86400) { // 24 jam
            self::destroySessionAndRedirect('Session expired');
            return;
        }
    }
    
    // === Destroy session dan redirect ===
    private static function destroySessionAndRedirect($reason = 'Security violation')
    {
        // Log security violation
        error_log("Session security violation: " . $reason . " - User: " . ($_SESSION['user']['username'] ?? 'unknown'));
        
        // Clear session
        $_SESSION = array();
        session_destroy();
        
        // Redirect ke login
        header('Location: index.php?page=login&error=security');
        exit;
    }
    
    // === Cek session timeout ===
    public static function checkSessionTimeout()
    {
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return;
        }
        
        $timeout = 15 * 60; // 15 menit dalam detik
        $current_time = time();
        $last_activity = $_SESSION['last_activity'];
        
        // Jika lebih dari 15 menit, logout
        if (($current_time - $last_activity) > $timeout) {
            session_destroy();
            header('Location: index.php?page=login&timeout=1');
            exit;
        }
        
        // Update waktu aktivitas terakhir
        $_SESSION['last_activity'] = $current_time;
    }
    
    // === Update aktivitas user ===
    public static function updateActivity()
    {
        if (isset($_SESSION['user'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    // === Middleware untuk cek role admin ===
    public static function requireAdmin()
    {
        self::requireLogin();
        if ($_SESSION['user']['role'] !== 'Admin') {
            header('Location: index.php?page=dashboard');
            exit;
        }
    }
}
