<?php
// app/controllers/AuthController.php

class AuthController
{
    public function __construct()
    {
        // Bisa digunakan nanti untuk inisialisasi model User jika perlu
        // require_once __DIR__ . '/../models/UserModel.php';
        // $this->model = new UserModel();
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

            // Cek kredensial (sementara hardcode, nanti bisa pakai model)
            if ($username === 'admin' && $password === '123456') {
                $_SESSION['user'] = [
                    'username' => $username,
                    'role' => 'admin'
                ];
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
}
