<?php
require_once __DIR__ . '/../models/KegiatanModel.php';

class KegiatanController {
    private $model;

    public function __construct() {
        $this->model = new KegiatanModel();
    }

    // Halaman jadwal-kegiatan.php
    public function jadwalKegiatan() {
        $kegiatan = $this->model->getAllKegiatan();
        
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/jadwal-kegiatan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-kegiatan.php
    public function tambahKegiatan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-kegiatan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses tambah kegiatan
    public function storeKegiatan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_kegiatan' => $_POST['namaKegiatan'] ?? '',
                'tanggal' => $_POST['tanggal'] ?? '',
                'jam_mulai' => $_POST['jamMulai'] ?? '',
                'jam_selesai' => $_POST['jamSelesai'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? '',
                'status' => $_POST['status'] ?? 'Belum Dimulai'
            ];

            // Validasi data
            if (empty($data['nama_kegiatan']) || empty($data['tanggal']) || 
                empty($data['jam_mulai']) || empty($data['jam_selesai']) || 
                empty($data['status'])) {
                header('Location: index.php?page=tambah-kegiatan&status=error');
                exit;
            }

            // Validasi jam
            if ($data['jam_mulai'] >= $data['jam_selesai']) {
                header('Location: index.php?page=tambah-kegiatan&status=error&message=jam');
                exit;
            }

            if ($this->model->tambahKegiatan($data)) {
                header('Location: index.php?page=tambah-kegiatan&status=success');
            } else {
                header('Location: index.php?page=tambah-kegiatan&status=error');
            }
            exit;
        }
    }

    // Halaman edit-kegiatan.php
    public function editKegiatan() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: index.php?page=jadwal-kegiatan');
            exit;
        }

        $kegiatan = $this->model->getKegiatanById($id);
        
        if (!$kegiatan) {
            header('Location: index.php?page=jadwal-kegiatan');
            exit;
        }

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-kegiatan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update kegiatan
    public function updateKegiatan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                header('Location: index.php?page=jadwal-kegiatan');
                exit;
            }

            $data = [
                'nama_kegiatan' => $_POST['namaKegiatan'] ?? '',
                'tanggal' => $_POST['tanggal'] ?? '',
                'jam_mulai' => $_POST['jamMulai'] ?? '',
                'jam_selesai' => $_POST['jamSelesai'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? '',
                'status' => $_POST['status'] ?? 'Belum Dimulai'
            ];

            // Validasi data
            if (empty($data['nama_kegiatan']) || empty($data['tanggal']) || 
                empty($data['jam_mulai']) || empty($data['jam_selesai']) || 
                empty($data['status'])) {
                header('Location: index.php?page=edit-kegiatan&id=' . $id . '&status=error');
                exit;
            }

            // Validasi jam
            if ($data['jam_mulai'] >= $data['jam_selesai']) {
                header('Location: index.php?page=edit-kegiatan&id=' . $id . '&status=error&message=jam');
                exit;
            }

            if ($this->model->updateKegiatan($id, $data)) {
                header('Location: index.php?page=edit-kegiatan&id=' . $id . '&status=success');
            } else {
                header('Location: index.php?page=edit-kegiatan&id=' . $id . '&status=error');
            }
            exit;
        }
    }

    // Proses hapus kegiatan
    public function hapusKegiatan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
                exit;
            }

            if ($this->model->hapusKegiatan($id)) {
                echo json_encode(['success' => true, 'message' => 'Kegiatan berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus kegiatan']);
            }
            exit;
        }
    }
}
