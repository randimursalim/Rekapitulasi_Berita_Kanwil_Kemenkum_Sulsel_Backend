<?php
$page = $_GET['page'] ?? 'dashboard'; // Default halaman dashboard

switch ($page) {
    // === DASHBOARD ===
    case 'dashboard':
        require_once __DIR__ . '/../app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    // === KONTEN ===
    case 'rekap-konten':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->rekapKonten();
        break;

    case 'input-konten':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->inputKonten();
        break;

    case 'edit-konten':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->editKonten();
        break;

    case 'arsip':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->arsip();
        break;

    // === PENGGUNA ===
    case 'pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->daftarPengguna(); // ✅ sudah diganti
        break;

    case 'tambah-pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->tambahPengguna(); // ✅ sudah diganti
        break;

    case 'edit-pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->editPengguna(); // ✅ sudah diganti
        break;

    case 'edit-profil':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->editProfilPengguna(); // ✅ sudah diganti
        break;

    // === KEGIATAN ===
    case 'jadwal-kegiatan':
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->jadwalKegiatan();
        break;

    case 'tambah-kegiatan':
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->tambahKegiatan();
        break;

    case 'edit-kegiatan':
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->editKegiatan();
        break;
    
    // === AUTH ===
    case 'login':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'proses-login':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->prosesLogin();
        break;

    case 'logout':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    // === DEFAULT ===
    default:
        echo "404 - Halaman tidak ditemukan";
        break;
}
