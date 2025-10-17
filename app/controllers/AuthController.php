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
        
        // Cek session timeout (15 menit)
        self::checkSessionTimeout();
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
