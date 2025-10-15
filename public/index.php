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

    case 'store-konten':
        require_once '../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->storeKonten();
        break;

    case 'edit-konten':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->editKonten();
        break;

    case 'update-konten':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->updateKonten();
        break;

    case 'delete-konten':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->deleteKonten();
        break;

    case 'get-rekap-data':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->getRekapData();
        break;

    case 'get-rekap-tabel':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->getRekapTabel();
        break;

    case 'get-available-periods':
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->getAvailablePeriods();
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

    case 'store-kegiatan':
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->storeKegiatan();
        break;

    case 'update-kegiatan':
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->updateKegiatan();
        break;

    case 'hapus-kegiatan':
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->hapusKegiatan();
        break;

    // === PENGGUNA ===
    case 'pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->daftarPengguna();
        break;

    case 'tambah-pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->tambahPengguna();
        break;

    case 'store-pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->storePengguna();
        break;

    case 'edit-pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->editPengguna();
        break;

    case 'update-pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->updatePengguna();
        break;

    case 'hapus-pengguna':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->hapusPengguna();
        break;

    case 'edit-profil':
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->editProfilPengguna();
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
