<?php
require_once __DIR__ . '/../models/KontenModel.php';

class KontenController {
    private $model;

    public function __construct() {
        $this->model = new KontenModel();
    }

    // === FORM INPUT ===
    public function inputKonten() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/input-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // === SIMPAN DATA KONTEN ===
    public function storeKonten() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: index.php?page=input-konten&status=invalid');
        exit;
    }

    // Ambil data utama
    $jenis = $_POST['jenis'] ?? '';
    $judul = $_POST['judul'] ?? '';
    $divisi = $_POST['divisi'] ?? '';

    // Handle upload dokumentasi (opsional)
    $dokumentasi = null;
    if (!empty($_FILES['dokumentasi']['name'])) {
        // Path folder uploads di public supaya bisa diakses browser
        $targetDir = __DIR__ . '/../../public/storage/uploads/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Nama file unik
        $fileName = time() . '_' . basename($_FILES['dokumentasi']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $targetFile)) {
            // Path relatif untuk akses via browser
            $dokumentasi = 'storage/uploads/' . $fileName;
        }
    }

    // Simpan ke tabel konten
    $idKonten = $this->model->insertKonten($jenis, $judul, $divisi, $dokumentasi);

    if (!$idKonten) {
        header('Location: index.php?page=input-konten&status=error_main');
        exit;
    }

    // Simpan ke tabel detail
    $detailSaved = false;
    if ($jenis === 'berita') {
        $detailSaved = $this->model->insertBerita(
            $idKonten,
            $_POST['tanggalBerita'] ?? null,
            $_POST['linkBerita'] ?? null,
            $_POST['sumberBerita'] ?? null,
            $_POST['jenisBerita'] ?? null,
            $_POST['ringkasan'] ?? null
        );
    } else {
        $detailSaved = $this->model->insertMedsos(
            $idKonten,
            $_POST['tanggalPost'] ?? null,
            $_POST['linkPost'] ?? null,
            $_POST['caption'] ?? null
        );
    }

    // === CEK HASIL ===
    if ($detailSaved) {
        // ✅ jika sukses simpan semua
        header('Location: index.php?page=input-konten&status=success');
        exit;
    } else {
        // ❌ jika gagal, hapus data utama agar tidak nyangkut di DB
        $this->model->deleteKonten($idKonten);
        header('Location: index.php?page=input-konten&status=error_detail');
        exit;
    }
}


    // === REKAP ===
    public function rekapKonten() {
        $detailBerita = $this->model->getDetailBerita();
        $detailMedsos = $this->model->getDetailMedsos();

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/rekap-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // === GET REKAP DATA (AJAX) ===
    public function getRekapData() {
        // Set header untuk JSON response
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
                exit;
            }

            // Ambil parameter dari request
            $filter = $_GET['filter'] ?? 'monthly';
            $startDate = $_GET['startDate'] ?? null;
            $endDate = $_GET['endDate'] ?? null;
            $jenis = $_GET['jenis'] ?? 'all';

            // Validasi filter
            $allowedFilters = ['daily', 'weekly', 'monthly', 'yearly', 'range'];
            if (!in_array($filter, $allowedFilters)) {
                $filter = 'monthly';
            }

            // Validasi jenis
            $allowedJenis = ['all', 'berita', 'medsos', 'instagram', 'youtube', 'tiktok', 'twitter', 'facebook', 'media_online', 'surat_kabar', 'website_kanwil'];
            if (!in_array($jenis, $allowedJenis)) {
                $jenis = 'all';
            }

            // Ambil data dari model
            $rekapData = $this->model->getRekapData($filter, $startDate, $endDate, $jenis);

            echo json_encode([
                'success' => true,
                'data' => $rekapData,
                'filter' => $filter,
                'jenis' => $jenis
            ]);

        } catch (Exception $e) {
            error_log("[ERROR] Get Rekap Data Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
        }
    }

    // === GET REKAP TABEL (AJAX) ===
    public function getRekapTabel() {
        // Set header untuk JSON response
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
                exit;
            }

            // Ambil parameter dari request
            $bulan = $_GET['bulan'] ?? null;
            $tahun = $_GET['tahun'] ?? null;

            // Validasi bulan dan tahun
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

            // Ambil data dari model
            $tabelData = $this->model->getRekapTabel($bulan, $tahun);

            echo json_encode([
                'success' => true,
                'data' => $tabelData,
                'bulan' => $bulan,
                'tahun' => $tahun
            ]);

        } catch (Exception $e) {
            error_log("[ERROR] Get Rekap Tabel Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
        }
    }

    // === GET AVAILABLE PERIODS (AJAX) ===
    public function getAvailablePeriods() {
        // Set header untuk JSON response
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
                exit;
            }

            // Ambil data periode dari model
            $periods = $this->model->getAvailablePeriods();

            echo json_encode([
                'success' => true,
                'data' => $periods
            ]);

        } catch (Exception $e) {
            error_log("[ERROR] Get Available Periods Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
        }
    }

    // === EDIT ===
    public function editKonten() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=arsip&status=invalid_id');
            exit;
        }
        
        $konten = $this->model->getKontenLengkapById($id);
        if (!$konten) {
            header('Location: index.php?page=arsip&status=not_found');
            exit;
        }

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-konten.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // === UPDATE KONTEN ===
    public function updateKonten() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=arsip&status=invalid_method');
            exit;
        }

        $idKonten = $_POST['id_konten'] ?? null;
        if (!$idKonten) {
            header('Location: index.php?page=arsip&status=invalid_id');
            exit;
        }

        // Ambil data utama
        $jenis = $_POST['jenis'] ?? '';
        $judul = $_POST['judul'] ?? '';
        $divisi = $_POST['divisi'] ?? '';

        // Handle upload dokumentasi (opsional)
        $dokumentasi = null;
        if (!empty($_FILES['dokumentasi']['name'])) {
            // Path folder uploads di public supaya bisa diakses browser
            $targetDir = __DIR__ . '/../../public/storage/uploads/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // Nama file unik
            $fileName = time() . '_' . basename($_FILES['dokumentasi']['name']);
            $targetFile = $targetDir . $fileName;

            if (move_uploaded_file($_FILES['dokumentasi']['tmp_name'], $targetFile)) {
                // Path relatif untuk akses via browser
                $dokumentasi = 'storage/uploads/' . $fileName;
            }
        } else {
            // Jika tidak ada file baru, ambil dokumentasi lama
            $kontenLama = $this->model->getKontenById($idKonten);
            $dokumentasi = $kontenLama['dokumentasi'] ?? null;
        }

        // Update tabel konten
        $kontenUpdated = $this->model->updateKonten($idKonten, $jenis, $judul, $divisi, $dokumentasi);

        if (!$kontenUpdated) {
            header('Location: index.php?page=edit-konten&id=' . $idKonten . '&status=error_main');
            exit;
        }

        // Update tabel detail
        $detailUpdated = false;
        if ($jenis === 'berita') {
            $detailUpdated = $this->model->updateBerita(
                $idKonten,
                $_POST['tanggalBerita'] ?? null,
                $_POST['linkBerita'] ?? null,
                $_POST['sumberBerita'] ?? null,
                $_POST['jenisBerita'] ?? null,
                $_POST['ringkasan'] ?? null
            );
        } else {
            $detailUpdated = $this->model->updateMedsos(
                $idKonten,
                $_POST['tanggalPost'] ?? null,
                $_POST['linkPost'] ?? null,
                $_POST['caption'] ?? null
            );
        }

        // === CEK HASIL ===
        if ($detailUpdated) {
            // ✅ jika sukses update semua
            header('Location: index.php?page=arsip&status=update_success');
            exit;
        } else {
            // ❌ jika gagal update detail
            header('Location: index.php?page=edit-konten&id=' . $idKonten . '&status=error_detail');
            exit;
        }
    }

    // === DELETE KONTEN ===
    public function deleteKonten() {
        // Set header untuk JSON response
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'error' => 'Method tidak diizinkan']);
                exit;
            }

            $idKonten = $_POST['id_konten'] ?? null;
            
            if (!$idKonten) {
                echo json_encode(['success' => false, 'error' => 'ID konten tidak valid']);
                exit;
            }

            // Validasi ID konten
            $idKonten = (int) $idKonten;
            if ($idKonten <= 0) {
                echo json_encode(['success' => false, 'error' => 'ID konten tidak valid']);
                exit;
            }

            // Cek apakah konten ada
            $konten = $this->model->getKontenById($idKonten);
            if (!$konten) {
                echo json_encode(['success' => false, 'error' => 'Konten tidak ditemukan']);
                exit;
            }

            // Hapus file dokumentasi DULU (sebelum hapus dari database)
            $fileDeleted = true;
            if (!empty($konten['dokumentasi'])) {
                $filePath = __DIR__ . '/../../public/' . $konten['dokumentasi'];
                if (file_exists($filePath)) {
                    $fileDeleted = unlink($filePath);
                    if (!$fileDeleted) {
                        error_log("[WARNING] Gagal hapus file dokumentasi: " . $filePath);
                        // Tidak exit, tetap lanjut hapus dari database
                    }
                }
            }

            // Hapus konten menggunakan model
            $result = $this->model->deleteKonten($idKonten);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Konten berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Gagal menghapus konten dari database']);
            }
            
        } catch (Exception $e) {
            error_log("[ERROR] Delete Konten Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Terjadi kesalahan server']);
        }
    }

    // === ARSIP ===
public function arsip() {
    // Ambil data dari model
    $detailBerita = $this->model->getDetailBerita();
    $detailMedsos  = $this->model->getDetailMedsos();

    // Pastikan variabel berupa array (menghindari null)
    if (!is_array($detailBerita)) $detailBerita = [];
    if (!is_array($detailMedsos)) $detailMedsos = [];

    include __DIR__ . '/../views/layouts/header.php';
    include __DIR__ . '/../views/pages/arsip.php';
    include __DIR__ . '/../views/layouts/footer.php';
}

}
