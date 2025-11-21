<?php
require_once __DIR__ . '/../models/AduanModel.php';

class AduanController {
    private $model;

    public function __construct() {
        $this->model = new AduanModel();
    }

    // Halaman daftar-aduan.php
    public function daftarAduan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/daftar-aduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-aduan.php
    public function tambahAduan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-aduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah aduan
    public function storeAduan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'no_register' => $_POST['noRegister'] ?? '',
                'tanggal' => $_POST['tanggal'] ?? '',
                'aduan' => $_POST['aduan'] ?? '',
                'jenis_aduan' => $_POST['jenisAduan'] ?? '',
                'media_digunakan' => $_POST['mediaDigunakan'] ?? '',
                'tindak_lanjut' => $_POST['tindakLanjut'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? ''
            ];

            // Validasi data
            if (empty($data['no_register']) || empty($data['tanggal']) || 
                empty($data['aduan']) || empty($data['jenis_aduan']) || 
                empty($data['media_digunakan'])) {
                header('Location: index.php?page=tambah-aduan&status=error');
                exit;
            }

            if ($this->model->tambahAduan($data)) {
                // Tambahkan log aktivitas
                require_once __DIR__ . '/../models/HomeModel.php';
                $homeModel = new HomeModel();
                $homeModel->addLogAktivitas("Menambahkan aduan: " . $data['no_register']);
                
                header('Location: index.php?page=tambah-aduan&status=success');
            } else {
                header('Location: index.php?page=tambah-aduan&status=error');
            }
            exit;
        }
    }

    // Halaman edit-aduan.php
    public function editAduan() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: index.php?page=daftar-aduan');
            exit;
        }

        $aduan = $this->model->getAduanById($id);
        
        if (!$aduan) {
            header('Location: index.php?page=daftar-aduan');
            exit;
        }

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-aduan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update aduan
    public function updateAduan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                header('Location: index.php?page=daftar-aduan');
                exit;
            }

            $data = [
                'no_register' => $_POST['noRegister'] ?? '',
                'tanggal' => $_POST['tanggal'] ?? '',
                'aduan' => $_POST['aduan'] ?? '',
                'jenis_aduan' => $_POST['jenisAduan'] ?? '',
                'media_digunakan' => $_POST['mediaDigunakan'] ?? '',
                'tindak_lanjut' => $_POST['tindakLanjut'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? ''
            ];

            // Validasi data
            if (empty($data['no_register']) || empty($data['tanggal']) || 
                empty($data['aduan']) || empty($data['jenis_aduan']) || 
                empty($data['media_digunakan'])) {
                header('Location: index.php?page=edit-aduan&id=' . $id . '&status=error');
                exit;
            }

            if ($this->model->updateAduan($id, $data)) {
                // Tambahkan log aktivitas
                require_once __DIR__ . '/../models/HomeModel.php';
                $homeModel = new HomeModel();
                $homeModel->addLogAktivitas("Mengedit aduan: " . $data['no_register']);
                
                header('Location: index.php?page=edit-aduan&id=' . $id . '&status=success');
            } else {
                header('Location: index.php?page=edit-aduan&id=' . $id . '&status=error');
            }
            exit;
        }
    }

    // Hapus aduan (AJAX)
    public function hapusAduan() {
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method tidak diizinkan']);
                exit;
            }

            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID aduan tidak valid']);
                exit;
            }

            // Validasi ID aduan
            $id = (int) $id;
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID aduan tidak valid']);
                exit;
            }

            // Cek apakah aduan ada
            $aduan = $this->model->getAduanById($id);
            if (!$aduan) {
                echo json_encode(['success' => false, 'message' => 'Aduan tidak ditemukan']);
                exit;
            }

            // Hapus aduan
            $result = $this->model->hapusAduan($id);
            
            if ($result) {
                // Tambahkan log aktivitas
                require_once __DIR__ . '/../models/HomeModel.php';
                $homeModel = new HomeModel();
                $homeModel->addLogAktivitas("Menghapus aduan: " . $aduan['no_register']);
                
                echo json_encode(['success' => true, 'message' => 'Aduan berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus aduan dari database']);
            }
            
        } catch (Exception $e) {
            error_log("[ERROR] Delete Aduan Controller: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server']);
        }
    }
}

