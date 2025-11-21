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
        
        // Redirect ke login, dengan parameter timeout jika ada
        $timeout = isset($_GET['timeout']) ? '&timeout=1' : '';
        header('Location: index.php?page=login' . $timeout);
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
    // NOTE: Validasi ini dibuat lebih permissive untuk menghindari false positive
    // terutama untuk mobile/proxy yang sering berubah IP atau User Agent
    public static function validateSessionSecurity()
    {
        // NONAKTIFKAN sementara validasi IP dan User Agent karena terlalu banyak false positive
        // Untuk production dengan mobile/proxy, validasi ini sering menyebabkan masalah
        // Hanya tetap aktif validasi session hijacking (24 jam max)
        
        // Cek session hijacking (login time terlalu lama) - tetap aktif
        if (isset($_SESSION['login_time'])) {
            $maxLoginTime = 24 * 60 * 60; // 24 jam dalam detik (86400 detik)
            $current_time = time();
            $login_time = $_SESSION['login_time'];
            $timeSinceLogin = $current_time - $login_time;
            
            // Debug logging
            error_log("Session hijacking check - User: " . ($_SESSION['user']['username'] ?? 'unknown') . 
                      ", Login time: $login_time, Current: $current_time, Since login: $timeSinceLogin seconds, Max: $maxLoginTime seconds");
            
            if ($timeSinceLogin > $maxLoginTime) {
                error_log("Session hijacking detected - Logging out user: " . ($_SESSION['user']['username'] ?? 'unknown') . 
                          " - Session active for $timeSinceLogin seconds (max: $maxLoginTime seconds)");
                self::destroySessionAndRedirect('Session expired (24 hours)');
                return;
            }
        } else {
            // Jika login_time tidak ada, set sekarang (untuk session yang sudah ada)
            $_SESSION['login_time'] = time();
        }
        
        // DISABLED: Validasi IP - terlalu banyak false positive untuk mobile/proxy
        // IP address validation disabled untuk production
        /*
        if (isset($_SESSION['ip_address'])) {
            $currentIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $sessionIp = $_SESSION['ip_address'];
            if ($sessionIp !== 'unknown' && $currentIp !== 'unknown' && $sessionIp !== $currentIp) {
                error_log("IP address changed: $sessionIp -> $currentIp (logged but not blocked)");
            }
        }
        */
        
        // DISABLED: Validasi User Agent - terlalu banyak false positive untuk mobile/desktop toggle
        // User Agent validation disabled untuk production
        /*
        if (isset($_SESSION['user_agent'])) {
            $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $sessionUserAgent = $_SESSION['user_agent'];
            if ($sessionUserAgent !== $currentUserAgent) {
                error_log("User Agent changed: (logged but not blocked)");
            }
        }
        */
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
        // Pastikan session sudah started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Initialize last_activity jika belum ada
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
            return;
        }
        
        // Gunakan timeout yang sama dengan client-side (15 menit)
        $timeout = 15 * 60; // 15 menit dalam detik (900 detik)
        $current_time = time();
        $last_activity = $_SESSION['last_activity'] ?? time();
        $timeDifference = $current_time - $last_activity;
        
        // Debug logging (aktifkan untuk testing, bisa di-comment di production)
        error_log("Session timeout check - User: " . ($_SESSION['user']['username'] ?? 'unknown') . 
                  ", Current: $current_time, Last: $last_activity, Diff: $timeDifference seconds, Timeout: $timeout seconds");
        
        // Jika lebih dari timeout yang ditentukan, logout
        if ($timeDifference > $timeout) {
            error_log("Session timeout - Logging out user: " . ($_SESSION['user']['username'] ?? 'unknown') . 
                      " - Last activity: $timeDifference seconds ago (timeout: $timeout seconds)");
            session_destroy();
            header('Location: index.php?page=login&timeout=1');
            exit;
        }
        
        // Update last_activity saat page request (selain dari AJAX)
        // Update setiap kali ada request untuk memastikan aktivitas terhitung
        $_SESSION['last_activity'] = $current_time;
    }
    
    // === Update aktivitas user ===
    public static function updateActivity()
    {
        // Pastikan session sudah started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user'])) {
            $current_time = time();
            $old_activity = $_SESSION['last_activity'] ?? 0;
            
            // Update last_activity dengan waktu sekarang
            $_SESSION['last_activity'] = $current_time;
            
            // Debug logging (aktifkan untuk testing)
            error_log("Activity updated - User: " . ($_SESSION['user']['username'] ?? 'unknown') . 
                      ", Old: $old_activity, New: $current_time");
            
            // PHP akan otomatis save session di akhir request
            // Tidak perlu session_write_close() karena bisa menyebabkan masalah
        }
    }
    
    // === Helper: Extract core User Agent components ===
    private static function getCoreUserAgent($ua) {
        if ($ua === 'unknown') return 'unknown';
        
        // Ambil browser name dan OS utama saja
        $browser = '';
        $os = '';
        
        // Detect browser
        if (stripos($ua, 'Chrome') !== false) $browser = 'Chrome';
        elseif (stripos($ua, 'Firefox') !== false) $browser = 'Firefox';
        elseif (stripos($ua, 'Safari') !== false) $browser = 'Safari';
        elseif (stripos($ua, 'Edge') !== false) $browser = 'Edge';
        elseif (stripos($ua, 'Opera') !== false) $browser = 'Opera';
        
        // Detect OS
        if (stripos($ua, 'Windows') !== false) $os = 'Windows';
        elseif (stripos($ua, 'Android') !== false) $os = 'Android';
        elseif (stripos($ua, 'iOS') !== false || stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $os = 'iOS';
        elseif (stripos($ua, 'Linux') !== false) $os = 'Linux';
        elseif (stripos($ua, 'Mac') !== false) $os = 'Mac';
        
        return $browser . '|' . $os;
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
