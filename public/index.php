<?php
// Redirect ke landing page jika tidak ada parameter page
if (!isset($_GET['page'])) {
    header('Location: landing.php');
    exit();
}

// Set custom session path dengan error handling
$sessionPath = __DIR__ . '/../storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}

// Cek apakah folder writable
if (is_writable($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
} else {
    // Fallback: gunakan folder temp default tapi suppress error
    error_reporting(E_ALL & ~E_WARNING);
}

// Start session dengan error suppression
@session_start();
$page = $_GET['page'] ?? 'dashboard'; // Default halaman dashboard

switch ($page) {
    // === AUTHENTICATION ===
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

    case 'update-activity':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        // Clean output buffer to ensure pure JSON response
        if (ob_get_length()) {
            ob_clean();
        }
        // Set JSON header
        header('Content-Type: application/json');
        // Update activity
        AuthController::updateActivity();
        // Return JSON response
        echo json_encode(['success' => true]);
        exit;
        break;

        // === DASHBOARD ===
    case 'dashboard':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin(); // Cek login dulu
        
        require_once __DIR__ . '/../app/controllers/HomeController.php';
        $controller = new HomeController();
        $controller->index();
        break;

    // === KONTEN ===
    case 'rekap-konten':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->rekapKonten();
        break;

    case 'input-konten':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->inputKonten();
        break;

    case 'store-konten':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once '../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->storeKonten();
        break;

    case 'edit-konten':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->editKonten();
        break;

    case 'update-konten':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->updateKonten();
        break;

    case 'delete-konten':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->deleteKonten();
        break;

    case 'get-rekap-data':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->getRekapData();
        break;

    case 'get-rekap-tabel':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->getRekapTabel();
        break;

    case 'get-available-periods':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->getAvailablePeriods();
        break;

    case 'arsip':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KontenController.php';
        $controller = new KontenController();
        $controller->arsip();
        break;

    // === PENGGUNA (Admin Only) ===
    case 'pengguna':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireAdmin(); // Hanya admin yang bisa akses
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->daftarPengguna();
        break;

    case 'tambah-pengguna':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireAdmin(); // Hanya admin yang bisa akses
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->tambahPengguna();
        break;

    case 'edit-pengguna':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireAdmin(); // Hanya admin yang bisa akses
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->editPengguna();
        break;

    case 'edit-profil':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin(); // Semua user bisa edit profil sendiri
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->editProfilPengguna();
        break;

    case 'update-profil':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin(); // Semua user bisa update profil sendiri
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->updateProfilPengguna();
        break;

    // === KEGIATAN ===
    case 'jadwal-kegiatan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->jadwalKegiatan();
        break;

    case 'tambah-kegiatan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->tambahKegiatan();
        break;

    case 'edit-kegiatan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->editKegiatan();
        break;

    case 'store-kegiatan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->storeKegiatan();
        break;

    case 'update-kegiatan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->updateKegiatan();
        break;

    case 'hapus-kegiatan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/KegiatanController.php';
        $controller = new KegiatanController();
        $controller->hapusKegiatan();
        break;


    case 'store-pengguna':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireAdmin(); // Hanya admin yang bisa akses
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->storePengguna();
        break;


    case 'update-pengguna':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireAdmin(); // Hanya admin yang bisa akses
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->updatePengguna();
        break;

    case 'hapus-pengguna':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireAdmin(); // Hanya admin yang bisa akses
        
        require_once __DIR__ . '/../app/controllers/PenggunaController.php';
        $controller = new PenggunaController();
        $controller->hapusPengguna();
        break;


    // === DEFAULT ===
    default:
        echo "404 - Halaman tidak ditemukan";
        break;
}
