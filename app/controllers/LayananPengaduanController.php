<?php
require_once __DIR__ . '/../models/LayananPengaduanModel.php';

class LayananPengaduanController {
    private $model;

    public function __construct() {
        $this->model = new LayananPengaduanModel();
    }

    // Halaman daftar-layanan-pengaduan.php
    public function daftarLayananPengaduan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/daftar-layanan-pengaduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-layanan-pengaduan.php
    public function tambahLayananPengaduan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-layanan-pengaduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah layanan pengaduan
    public function storeLayananPengaduan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'no_register_pengaduan' => $_POST['noRegisterPengaduan'] ?? '',
                'nama' => $_POST['nama'] ?? '',
                'alamat' => $_POST['alamat'] ?? '',
                'jenis_tanda_pengenal' => $_POST['jenisTandaPengenal'] ?? '',
                'jenis_tanda_pengenal_lainnya' => ($_POST['jenisTandaPengenal'] ?? '') === 'LAINNYA' ? ($_POST['jenisTandaPengenalLainnya'] ?? '') : null,
                'no_tanda_pengenal' => $_POST['noTandaPengenal'] ?? '',
                'no_telp' => $_POST['noTelp'] ?? '',
                'judul_laporan' => $_POST['judulLaporan'] ?? '',
                'isi_laporan' => $_POST['isiLaporan'] ?? '',
                'tanggal_kejadian' => $_POST['tanggalKejadian'] ?? '',
                'lokasi_kejadian' => $_POST['lokasiKejadian'] ?? '',
                'kategori_laporan' => $_POST['kategoriLaporan'] ?? '',
                'jenis_aduan' => $_POST['jenisAduan'] ?? '',
                'jenis_aduan_lainnya' => ($_POST['jenisAduan'] ?? '') === 'Lainnya' ? ($_POST['jenisAduanLainnya'] ?? '') : null
            ];

            // Validasi data wajib
            if (empty($data['no_register_pengaduan']) || empty($data['nama']) || 
                empty($data['alamat']) || empty($data['jenis_tanda_pengenal']) || 
                empty($data['no_tanda_pengenal']) || empty($data['judul_laporan']) || 
                empty($data['isi_laporan']) || empty($data['tanggal_kejadian']) || 
                empty($data['lokasi_kejadian']) || empty($data['kategori_laporan']) || 
                empty($data['jenis_aduan'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                exit;
            }

            // Validasi jika pilihan "LAINNYA" atau "Lainnya" harus diisi field lainnya
            if ($data['jenis_tanda_pengenal'] === 'LAINNYA' && empty($data['jenis_tanda_pengenal_lainnya'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                exit;
            }
            if ($data['jenis_aduan'] === 'Lainnya' && empty($data['jenis_aduan_lainnya'])) {
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                exit;
            }

            try {
                if ($this->model->tambahLayananPengaduan($data)) {
                    // Tambahkan log aktivitas
                    require_once __DIR__ . '/../models/HomeModel.php';
                    $homeModel = new HomeModel();
                    $homeModel->addLogAktivitas("Menambahkan layanan pengaduan: " . $data['no_register_pengaduan']);
                    
                    header('Location: index.php?page=tambah-layanan-pengaduan&status=success');
                } else {
                    error_log("[ERROR] Store Layanan Pengaduan: Failed to insert data");
                    header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
                }
            } catch (Exception $e) {
                error_log("[ERROR] Store Layanan Pengaduan: " . $e->getMessage());
                header('Location: index.php?page=tambah-layanan-pengaduan&status=error');
            }
            exit;
        }
    }

    // Halaman edit-layanan-pengaduan.php
    public function editLayananPengaduan() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: index.php?page=layanan-pengaduan');
            exit;
        }

        $layananPengaduan = $this->model->getLayananPengaduanById($id);
        
        if (!$layananPengaduan) {
            header('Location: index.php?page=layanan-pengaduan');
            exit;
        }

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-layanan-pengaduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update layanan pengaduan
    public function updateLayananPengaduan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                header('Location: index.php?page=layanan-pengaduan');
                exit;
            }

            $data = [
                'no_register_pengaduan' => $_POST['noRegisterPengaduan'] ?? '',
                'nama' => $_POST['nama'] ?? '',
                'alamat' => $_POST['alamat'] ?? '',
                'jenis_tanda_pengenal' => $_POST['jenisTandaPengenal'] ?? '',
                'jenis_tanda_pengenal_lainnya' => ($_POST['jenisTandaPengenal'] ?? '') === 'LAINNYA' ? ($_POST['jenisTandaPengenalLainnya'] ?? '') : null,
                'no_tanda_pengenal' => $_POST['noTandaPengenal'] ?? '',
                'no_telp' => $_POST['noTelp'] ?? '',
                'judul_laporan' => $_POST['judulLaporan'] ?? '',
                'isi_laporan' => $_POST['isiLaporan'] ?? '',
                'tanggal_kejadian' => $_POST['tanggalKejadian'] ?? '',
                'lokasi_kejadian' => $_POST['lokasiKejadian'] ?? '',
                'kategori_laporan' => $_POST['kategoriLaporan'] ?? '',
                'jenis_aduan' => $_POST['jenisAduan'] ?? '',
                'jenis_aduan_lainnya' => ($_POST['jenisAduan'] ?? '') === 'Lainnya' ? ($_POST['jenisAduanLainnya'] ?? '') : null
            ];

            // Validasi data wajib
            if (empty($data['no_register_pengaduan']) || empty($data['nama']) || 
                empty($data['alamat']) || empty($data['jenis_tanda_pengenal']) || 
                empty($data['no_tanda_pengenal']) || empty($data['judul_laporan']) || 
                empty($data['isi_laporan']) || empty($data['tanggal_kejadian']) || 
                empty($data['lokasi_kejadian']) || empty($data['kategori_laporan']) || 
                empty($data['jenis_aduan'])) {
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                exit;
            }

            // Validasi jika pilihan "LAINNYA" atau "Lainnya" harus diisi field lainnya
            if ($data['jenis_tanda_pengenal'] === 'LAINNYA' && empty($data['jenis_tanda_pengenal_lainnya'])) {
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                exit;
            }
            if ($data['jenis_aduan'] === 'Lainnya' && empty($data['jenis_aduan_lainnya'])) {
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                exit;
            }

            try {
                if ($this->model->updateLayananPengaduan($id, $data)) {
                    // Tambahkan log aktivitas
                    require_once __DIR__ . '/../models/HomeModel.php';
                    $homeModel = new HomeModel();
                    $homeModel->addLogAktivitas("Mengedit layanan pengaduan: " . $data['no_register_pengaduan']);
                    
                    header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=success');
                } else {
                    error_log("[ERROR] Update Layanan Pengaduan: Failed to update data");
                    header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
                }
            } catch (Exception $e) {
                error_log("[ERROR] Update Layanan Pengaduan: " . $e->getMessage());
                header('Location: index.php?page=edit-layanan-pengaduan&id=' . $id . '&status=error');
            }
            exit;
        }
    }

    // Hapus layanan pengaduan (AJAX)
    public function hapusLayananPengaduan() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
                exit;
            }

            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID layanan pengaduan tidak valid']);
                exit;
            }

            // Validasi ID
            $id = (int) $id;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID layanan pengaduan tidak valid']);
                exit;
            }

            // Cek apakah layanan pengaduan ada
            $layananPengaduan = $this->model->getLayananPengaduanById($id);
            if (!$layananPengaduan) {
                echo json_encode(['success' => false, 'message' => 'Layanan pengaduan tidak ditemukan']);
                exit;
            }

            // Hapus layanan pengaduan
            $result = $this->model->hapusLayananPengaduan($id);
            
            if ($result) {
                // Tambahkan log aktivitas
                require_once __DIR__ . '/../models/HomeModel.php';
                $homeModel = new HomeModel();
                $homeModel->addLogAktivitas("Menghapus layanan pengaduan: " . $layananPengaduan['no_register_pengaduan']);
                
                echo json_encode(['success' => true, 'message' => 'Layanan pengaduan berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus layanan pengaduan dari database']);
            }
            
        } catch (Exception $e) {
            error_log("[ERROR] Delete Layanan Pengaduan Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server']);
        }
    }
}

