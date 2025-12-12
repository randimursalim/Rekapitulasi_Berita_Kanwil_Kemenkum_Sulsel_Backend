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
// Default halaman berdasarkan role
$defaultPage = 'dashboard';
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'p3h') {
    $defaultPage = 'harmonisasi';
}
$page = $_GET['page'] ?? $defaultPage;

// Cek akses untuk role p3h (hanya bisa akses harmonisasi dan rekap-harmonisasi)
if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'p3h') {
    $allowedPages = ['harmonisasi', 'rekap-harmonisasi', 'tambah-harmonisasi', 'edit-harmonisasi', 
                     'store-harmonisasi', 'update-harmonisasi', 'hapus-harmonisasi',
                     'get-rekap-data-harmonisasi', 'get-rekap-tabel-harmonisasi', 
                     'get-available-periods-harmonisasi', 'edit-profil', 'update-profil', 
                     'logout', 'update-activity'];
    
    if (!in_array($page, $allowedPages)) {
        header('Location: index.php?page=harmonisasi');
        exit;
    }
}

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
        
        // Block access untuk role p3h
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'p3h') {
            header('Location: index.php?page=harmonisasi');
            exit;
        }
        
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

    // === PEMINJAMAN RUANGAN ===
    case 'jadwal-peminjaman-ruangan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->jadwalPeminjamanRuangan();
        break;

    case 'tambah-peminjaman-ruangan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->tambahPeminjamanRuangan();
        break;

    case 'edit-peminjaman-ruangan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->editPeminjamanRuangan();
        break;

    case 'store-peminjaman-ruangan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->storePeminjamanRuangan();
        break;

    case 'update-peminjaman-ruangan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->updatePeminjamanRuangan();
        break;

    case 'hapus-peminjaman-ruangan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->hapusPeminjamanRuangan();
        break;

    case 'tambah-peminjaman-ruangan-masyarakat':
        // Form untuk masyarakat (tanpa login)
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->tambahPeminjamanRuanganMasyarakat();
        break;

    case 'store-peminjaman-ruangan-masyarakat':
        // Proses untuk masyarakat (tanpa login)
        require_once __DIR__ . '/../app/controllers/PeminjamanRuanganController.php';
        $controller = new PeminjamanRuanganController();
        $controller->storePeminjamanRuanganMasyarakat();
        break;

    // === ADUAN ===
    case 'daftar-aduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/AduanController.php';
        $controller = new AduanController();
        $controller->daftarAduan();
        break;

    case 'tambah-aduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/AduanController.php';
        $controller = new AduanController();
        $controller->tambahAduan();
        break;

    case 'edit-aduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/AduanController.php';
        $controller = new AduanController();
        $controller->editAduan();
        break;

    case 'store-aduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/AduanController.php';
        $controller = new AduanController();
        $controller->storeAduan();
        break;

    case 'update-aduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/AduanController.php';
        $controller = new AduanController();
        $controller->updateAduan();
        break;

    case 'hapus-aduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/AduanController.php';
        $controller = new AduanController();
        $controller->hapusAduan();
        break;

    // === LAYANAN PENGADUAN ===
    case 'layanan-pengaduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->daftarLayananPengaduan();
        break;

    case 'tambah-layanan-pengaduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->tambahLayananPengaduan();
        break;

    case 'tambah-layanan-pengaduan-masyarakat':
        // Form untuk masyarakat (tanpa login)
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->tambahLayananPengaduanMasyarakat();
        break;

    case 'edit-layanan-pengaduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->editLayananPengaduan();
        break;

    case 'store-layanan-pengaduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->storeLayananPengaduan();
        break;

    case 'store-layanan-pengaduan-masyarakat':
        // Store untuk masyarakat (tanpa login)
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->storeLayananPengaduanMasyarakat();
        break;

    case 'tracking-layanan-pengaduan':
        // Tracking pengaduan untuk masyarakat (tanpa login)
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->trackingLayananPengaduan();
        break;

    case 'tracking-result':
        // Hasil tracking pengaduan untuk masyarakat (tanpa login)
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->trackingResult();
        break;

    case 'update-layanan-pengaduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->updateLayananPengaduan();
        break;

    case 'hapus-layanan-pengaduan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->hapusLayananPengaduan();
        break;

    case 'download-keterangan':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/LayananPengaduanController.php';
        $controller = new LayananPengaduanController();
        $controller->downloadKeterangan();
        break;

    // === HARMONISASI ===
    case 'harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->daftarHarmonisasi();
        break;

    case 'tambah-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->tambahHarmonisasi();
        break;

    case 'edit-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->editHarmonisasi();
        break;

    case 'store-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->storeHarmonisasi();
        break;

    case 'update-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->updateHarmonisasi();
        break;

    case 'hapus-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->hapusHarmonisasi();
        break;

    case 'rekap-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->rekapHarmonisasi();
        break;

    case 'get-rekap-data-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->getRekapData();
        break;

    case 'get-rekap-tabel-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->getRekapTabel();
        break;

    case 'get-available-periods-harmonisasi':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        AuthController::requireLogin();
        
        require_once __DIR__ . '/../app/controllers/HarmonisasiController.php';
        $controller = new HarmonisasiController();
        $controller->getAvailablePeriods();
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
