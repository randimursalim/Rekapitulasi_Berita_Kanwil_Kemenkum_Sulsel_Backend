<?php
require_once __DIR__ . '/../models/HarmonisasiModel.php';

class HarmonisasiController {
    private $model;

    public function __construct() {
        $this->model = new HarmonisasiModel();
    }

    // Halaman daftar-harmonisasi.php
    public function daftarHarmonisasi() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/daftar-harmonisasi.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman rekap-harmonisasi.php
    public function rekapHarmonisasi() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/rekap-harmonisasi.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // === GET REKAP DATA (AJAX) ===
    public function getRekapData() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
                exit;
            }

            $filter = $_GET['filter'] ?? 'monthly';
            $startDate = $_GET['startDate'] ?? null;
            $endDate = $_GET['endDate'] ?? null;
            $status = $_GET['status'] ?? 'all';

            $allowedFilters = ['daily', 'weekly', 'monthly', 'yearly', 'range'];
            if (!in_array($filter, $allowedFilters)) {
                $filter = 'monthly';
            }

            $allowedStatus = ['all', 'Diterima', 'Dikembalikan'];
            if (!in_array($status, $allowedStatus)) {
                $status = 'all';
            }

            $rekapData = $this->model->getRekapData($filter, $startDate, $endDate, $status);

            echo json_encode([
                'success' => true,
                'data' => $rekapData,
                'filter' => $filter,
                'status' => $status
            ]);

        } catch (Exception $e) {
            error_log("[ERROR] Get Rekap Data Harmonisasi Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
        }
    }

    // === GET REKAP TABEL (AJAX) ===
    public function getRekapTabel() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
                exit;
            }

            $bulan = $_GET['bulan'] ?? null;
            $tahun = $_GET['tahun'] ?? null;

            if ($bulan && $tahun) {
                $bulan = (int)$bulan;
                $tahun = (int)$tahun;
                
                if ($bulan < 1 || $bulan > 12) {
                    $bulan = null;
                }
                if ($tahun < 2020 || $tahun > 2030) {
                    $tahun = null;
                }
            }

            $tabelData = $this->model->getRekapTabel($bulan, $tahun);

            echo json_encode([
                'success' => true,
                'data' => $tabelData,
                'bulan' => $bulan,
                'tahun' => $tahun
            ]);

        } catch (Exception $e) {
            error_log("[ERROR] Get Rekap Tabel Harmonisasi Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
        }
    }

    // === GET AVAILABLE PERIODS (AJAX) ===
    public function getAvailablePeriods() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
                exit;
            }

            $periods = $this->model->getAvailablePeriods();

            echo json_encode([
                'success' => true,
                'data' => $periods
            ]);

        } catch (Exception $e) {
            error_log("[ERROR] Get Available Periods Harmonisasi Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
        }
    }

    // Halaman tambah-harmonisasi.php
    public function tambahHarmonisasi() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-harmonisasi.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman edit-harmonisasi.php
    public function editHarmonisasi() {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        
        if ($id <= 0) {
            header('Location: index.php?page=harmonisasi&status=error&message=' . urlencode('ID tidak valid.'));
            exit;
        }

        $harmonisasi = $this->model->getHarmonisasiById($id);
        
        if (!$harmonisasi) {
            header('Location: index.php?page=harmonisasi&status=error&message=' . urlencode('Data tidak ditemukan.'));
            exit;
        }

        // Pass $harmonisasi to view using extract
        extract(['harmonisasi' => $harmonisasi]);
        
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-harmonisasi.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah data harmonisasi
    public function storeHarmonisasi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=harmonisasi&status=error&message=' . urlencode('Method tidak diizinkan.'));
            exit;
        }

        $data = [
            'judul_rancangan' => trim($_POST['judul_rancangan'] ?? ''),
            'pemrakarsa' => trim($_POST['pemrakarsa'] ?? ''),
            'pemerintah_daerah' => trim($_POST['pemerintah_daerah'] ?? ''),
            'tanggal_rapat' => $_POST['tanggal_rapat'] ?? '',
            'pemegang_draf' => trim($_POST['pemegang_draf'] ?? ''),
            'status' => $_POST['status'] ?? 'Diterima',
            'alasan_pengembalian_draf' => trim($_POST['alasan_pengembalian_draf'] ?? '')
        ];

        // Validasi
        if (empty($data['judul_rancangan']) || empty($data['pemrakarsa']) || empty($data['pemerintah_daerah']) || 
            empty($data['tanggal_rapat']) || empty($data['pemegang_draf'])) {
            header('Location: index.php?page=tambah-harmonisasi&status=error&message=' . urlencode('Semua field wajib diisi kecuali Alasan Pengembalian Draf.'));
            exit;
        }

        if ($this->model->tambahHarmonisasi($data)) {
            header('Location: index.php?page=harmonisasi&status=success&message=' . urlencode('Data harmonisasi berhasil ditambahkan.'));
        } else {
            header('Location: index.php?page=tambah-harmonisasi&status=error&message=' . urlencode('Gagal menambahkan data harmonisasi.'));
        }
        exit;
    }

    // Proses update data harmonisasi
    public function updateHarmonisasi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=harmonisasi&status=error&message=' . urlencode('Method tidak diizinkan.'));
            exit;
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        
        if ($id <= 0) {
            header('Location: index.php?page=harmonisasi&status=error&message=' . urlencode('ID tidak valid.'));
            exit;
        }

        $data = [
            'judul_rancangan' => trim($_POST['judul_rancangan'] ?? ''),
            'pemrakarsa' => trim($_POST['pemrakarsa'] ?? ''),
            'pemerintah_daerah' => trim($_POST['pemerintah_daerah'] ?? ''),
            'tanggal_rapat' => $_POST['tanggal_rapat'] ?? '',
            'pemegang_draf' => trim($_POST['pemegang_draf'] ?? ''),
            'status' => $_POST['status'] ?? 'Diterima',
            'alasan_pengembalian_draf' => trim($_POST['alasan_pengembalian_draf'] ?? '')
        ];

        // Validasi
        if (empty($data['judul_rancangan']) || empty($data['pemrakarsa']) || empty($data['pemerintah_daerah']) || 
            empty($data['tanggal_rapat']) || empty($data['pemegang_draf'])) {
            header('Location: index.php?page=edit-harmonisasi&id=' . $id . '&status=error&message=' . urlencode('Semua field wajib diisi kecuali Alasan Pengembalian Draf.'));
            exit;
        }

        if ($this->model->updateHarmonisasi($id, $data)) {
            header('Location: index.php?page=harmonisasi&status=success&message=' . urlencode('Data harmonisasi berhasil diupdate.'));
        } else {
            header('Location: index.php?page=edit-harmonisasi&id=' . $id . '&status=error&message=' . urlencode('Gagal mengupdate data harmonisasi.'));
        }
        exit;
    }

    // Proses hapus data harmonisasi
    public function hapusHarmonisasi() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan.']);
            exit;
        }

        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        
        if ($id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID tidak valid.']);
            exit;
        }

        if ($this->model->hapusHarmonisasi($id)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Data harmonisasi berhasil dihapus.']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus data harmonisasi.']);
        }
        exit;
    }
}

