<?php
require_once __DIR__ . '/../models/PeminjamanRuanganModel.php';

class PeminjamanRuanganController {
    private $model;

    public function __construct() {
        $this->model = new PeminjamanRuanganModel();
    }

    // Halaman jadwal-peminjaman-ruangan.php
    public function jadwalPeminjamanRuangan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/jadwal-peminjaman-ruangan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-peminjaman-ruangan.php
    public function tambahPeminjamanRuangan() {
        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/tambah-peminjaman-ruangan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Halaman tambah-peminjaman-ruangan-masyarakat.php (untuk masyarakat, tanpa login)
    public function tambahPeminjamanRuanganMasyarakat() {
        include __DIR__ . '/../views/pages/tambah-peminjaman-ruangan-masyarakat.php';
    }

    // Proses tambah peminjaman ruangan
    public function storePeminjamanRuangan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_peminjam' => $_POST['namaPeminjam'] ?? '',
                'nama_ruangan' => $_POST['namaRuangan'] ?? '',
                'kegiatan' => $_POST['kegiatan'] ?? '',
                'tanggal_kegiatan' => $_POST['tanggalKegiatan'] ?? '',
                'waktu_kegiatan' => $_POST['waktuKegiatan'] ?? '',
                'durasi_kegiatan' => !empty($_POST['durasiKegiatan']) ? floatval($_POST['durasiKegiatan']) : 2
            ];

            // Validasi data
            if (empty($data['nama_peminjam']) || empty($data['nama_ruangan']) || 
                empty($data['kegiatan']) || empty($data['tanggal_kegiatan']) || 
                empty($data['waktu_kegiatan'])) {
                header('Location: index.php?page=tambah-peminjaman-ruangan&status=error');
                exit;
            }

            // Validasi durasi (minimal 1 jam, maksimal 8 jam)
            if ($data['durasi_kegiatan'] < 1 || $data['durasi_kegiatan'] > 8) {
                header('Location: index.php?page=tambah-peminjaman-ruangan&status=error&message=' . urlencode('Durasi kegiatan harus antara 1-8 jam.'));
                exit;
            }

            // Cek konflik peminjaman ruangan (dengan durasi)
            $konflik = $this->model->cekKonflikPeminjaman(
                $data['nama_ruangan'], 
                $data['tanggal_kegiatan'], 
                $data['waktu_kegiatan'],
                $data['durasi_kegiatan']
            );

            if ($konflik) {
                // Format tanggal dan waktu untuk pesan error
                $tanggal_formatted = date('d-m-Y', strtotime($konflik['tanggal_kegiatan']));
                // Format waktu: ambil HH:MM dari waktu_kegiatan (format TIME adalah HH:MM:SS)
                $waktu_formatted = substr($konflik['waktu_kegiatan'], 0, 5); // Ambil HH:MM
                
                // Hitung waktu selesai untuk kegiatan yang konflik (perhitungan manual)
                $durasi_konflik = isset($konflik['durasi_kegiatan']) && $konflik['durasi_kegiatan'] > 0 
                                 ? floatval($konflik['durasi_kegiatan']) 
                                 : 2;
                
                // Parse waktu mulai
                $waktu_parts = explode(':', $konflik['waktu_kegiatan']);
                $jam_mulai = intval($waktu_parts[0]);
                $menit_mulai = intval($waktu_parts[1]);
                
                // Hitung waktu selesai (tambahkan durasi dalam jam)
                $total_menit = ($jam_mulai * 60) + $menit_mulai + ($durasi_konflik * 60);
                $jam_selesai = floor($total_menit / 60) % 24; // Modulo 24 untuk handle overflow
                $menit_selesai = $total_menit % 60;
                
                $waktu_selesai_konflik = sprintf('%02d:%02d', $jam_selesai, $menit_selesai);
                
                $pesan_error = "Peminjaman gagal. Ruangan sudah digunakan untuk kegiatan " . 
                              htmlspecialchars($konflik['kegiatan']) . 
                              " oleh " . htmlspecialchars($konflik['nama_peminjam']) . 
                              " pada " . $tanggal_formatted . 
                              " pukul " . $waktu_formatted . " - " . $waktu_selesai_konflik . ".";
                
                header('Location: index.php?page=tambah-peminjaman-ruangan&status=error&message=' . urlencode($pesan_error));
                exit;
            }

            if ($this->model->tambahPeminjamanRuangan($data)) {
                header('Location: index.php?page=tambah-peminjaman-ruangan&status=success');
            } else {
                header('Location: index.php?page=tambah-peminjaman-ruangan&status=error');
            }
            exit;
        }
    }

    // Proses tambah peminjaman ruangan untuk masyarakat (tanpa login)
    public function storePeminjamanRuanganMasyarakat() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_peminjam' => $_POST['namaPeminjam'] ?? '',
                'nama_ruangan' => $_POST['namaRuangan'] ?? '',
                'kegiatan' => $_POST['kegiatan'] ?? '',
                'tanggal_kegiatan' => $_POST['tanggalKegiatan'] ?? '',
                'waktu_kegiatan' => $_POST['waktuKegiatan'] ?? '',
                'durasi_kegiatan' => !empty($_POST['durasiKegiatan']) ? floatval($_POST['durasiKegiatan']) : 2
            ];

            // Validasi data
            if (empty($data['nama_peminjam']) || empty($data['nama_ruangan']) || 
                empty($data['kegiatan']) || empty($data['tanggal_kegiatan']) || 
                empty($data['waktu_kegiatan'])) {
                header('Location: index.php?page=tambah-peminjaman-ruangan-masyarakat&status=error');
                exit;
            }

            // Validasi durasi (minimal 1 jam, maksimal 8 jam)
            if ($data['durasi_kegiatan'] < 1 || $data['durasi_kegiatan'] > 8) {
                header('Location: index.php?page=tambah-peminjaman-ruangan-masyarakat&status=error&message=' . urlencode('Durasi kegiatan harus antara 1-8 jam.'));
                exit;
            }

            // Cek konflik peminjaman ruangan (dengan durasi)
            $konflik = $this->model->cekKonflikPeminjaman(
                $data['nama_ruangan'], 
                $data['tanggal_kegiatan'], 
                $data['waktu_kegiatan'],
                $data['durasi_kegiatan']
            );

            if ($konflik) {
                // Format tanggal dan waktu untuk pesan error
                $tanggal_formatted = date('d-m-Y', strtotime($konflik['tanggal_kegiatan']));
                $waktu_formatted = substr($konflik['waktu_kegiatan'], 0, 5);
                
                // Hitung waktu selesai untuk kegiatan yang konflik
                $durasi_konflik = isset($konflik['durasi_kegiatan']) && $konflik['durasi_kegiatan'] > 0 
                                 ? floatval($konflik['durasi_kegiatan']) 
                                 : 2;
                
                $waktu_parts = explode(':', $konflik['waktu_kegiatan']);
                $jam_mulai = intval($waktu_parts[0]);
                $menit_mulai = intval($waktu_parts[1]);
                
                $total_menit = ($jam_mulai * 60) + $menit_mulai + ($durasi_konflik * 60);
                $jam_selesai = floor($total_menit / 60) % 24;
                $menit_selesai = $total_menit % 60;
                
                $waktu_selesai_konflik = sprintf('%02d:%02d', $jam_selesai, $menit_selesai);
                
                $pesan_error = "Peminjaman gagal. Ruangan sudah digunakan untuk kegiatan " . 
                              htmlspecialchars($konflik['kegiatan']) . 
                              " oleh " . htmlspecialchars($konflik['nama_peminjam']) . 
                              " pada " . $tanggal_formatted . 
                              " pukul " . $waktu_formatted . " - " . $waktu_selesai_konflik . ".";
                
                header('Location: index.php?page=tambah-peminjaman-ruangan-masyarakat&status=error&message=' . urlencode($pesan_error));
                exit;
            }

            if ($this->model->tambahPeminjamanRuangan($data)) {
                header('Location: index.php?page=tambah-peminjaman-ruangan-masyarakat&status=success');
            } else {
                header('Location: index.php?page=tambah-peminjaman-ruangan-masyarakat&status=error');
            }
            exit;
        }
    }

    // Halaman edit-peminjaman-ruangan.php
    public function editPeminjamanRuangan() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: index.php?page=jadwal-peminjaman-ruangan');
            exit;
        }

        $peminjaman = $this->model->getPeminjamanRuanganById($id);
        
        if (!$peminjaman) {
            header('Location: index.php?page=jadwal-peminjaman-ruangan');
            exit;
        }

        include __DIR__ . '/../views/layouts/header.php';
        include __DIR__ . '/../views/pages/edit-peminjaman-ruangan.php';
        include __DIR__ . '/../views/layouts/footer.php';
    }

    // Proses update peminjaman ruangan
    public function updatePeminjamanRuangan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                header('Location: index.php?page=jadwal-peminjaman-ruangan');
                exit;
            }

            $data = [
                'nama_peminjam' => $_POST['namaPeminjam'] ?? '',
                'nama_ruangan' => $_POST['namaRuangan'] ?? '',
                'kegiatan' => $_POST['kegiatan'] ?? '',
                'tanggal_kegiatan' => $_POST['tanggalKegiatan'] ?? '',
                'waktu_kegiatan' => $_POST['waktuKegiatan'] ?? '',
                'durasi_kegiatan' => !empty($_POST['durasiKegiatan']) ? floatval($_POST['durasiKegiatan']) : 2
            ];

            // Validasi data
            if (empty($data['nama_peminjam']) || empty($data['nama_ruangan']) || 
                empty($data['kegiatan']) || empty($data['tanggal_kegiatan']) || 
                empty($data['waktu_kegiatan'])) {
                header('Location: index.php?page=edit-peminjaman-ruangan&id=' . $id . '&status=error');
                exit;
            }

            // Validasi durasi (minimal 1 jam, maksimal 8 jam)
            if ($data['durasi_kegiatan'] < 1 || $data['durasi_kegiatan'] > 8) {
                header('Location: index.php?page=edit-peminjaman-ruangan&id=' . $id . '&status=error&message=' . urlencode('Durasi kegiatan harus antara 1-8 jam.'));
                exit;
            }

            // Cek konflik peminjaman ruangan (exclude ID yang sedang diupdate, dengan durasi)
            $konflik = $this->model->cekKonflikPeminjaman(
                $data['nama_ruangan'], 
                $data['tanggal_kegiatan'], 
                $data['waktu_kegiatan'],
                $data['durasi_kegiatan'],
                $id
            );

            if ($konflik) {
                // Format tanggal dan waktu untuk pesan error
                $tanggal_formatted = date('d-m-Y', strtotime($konflik['tanggal_kegiatan']));
                // Format waktu: ambil HH:MM dari waktu_kegiatan (format TIME adalah HH:MM:SS)
                $waktu_formatted = substr($konflik['waktu_kegiatan'], 0, 5); // Ambil HH:MM
                
                // Hitung waktu selesai untuk kegiatan yang konflik (perhitungan manual)
                $durasi_konflik = isset($konflik['durasi_kegiatan']) && $konflik['durasi_kegiatan'] > 0 
                                 ? floatval($konflik['durasi_kegiatan']) 
                                 : 2;
                
                // Parse waktu mulai
                $waktu_parts = explode(':', $konflik['waktu_kegiatan']);
                $jam_mulai = intval($waktu_parts[0]);
                $menit_mulai = intval($waktu_parts[1]);
                
                // Hitung waktu selesai (tambahkan durasi dalam jam)
                $total_menit = ($jam_mulai * 60) + $menit_mulai + ($durasi_konflik * 60);
                $jam_selesai = floor($total_menit / 60) % 24; // Modulo 24 untuk handle overflow
                $menit_selesai = $total_menit % 60;
                
                $waktu_selesai_konflik = sprintf('%02d:%02d', $jam_selesai, $menit_selesai);
                
                $pesan_error = "Peminjaman gagal. Ruangan sudah digunakan untuk kegiatan " . 
                              htmlspecialchars($konflik['kegiatan']) . 
                              " oleh " . htmlspecialchars($konflik['nama_peminjam']) . 
                              " pada " . $tanggal_formatted . 
                              " pukul " . $waktu_formatted . " - " . $waktu_selesai_konflik . ".";
                
                header('Location: index.php?page=edit-peminjaman-ruangan&id=' . $id . '&status=error&message=' . urlencode($pesan_error));
                exit;
            }

            if ($this->model->updatePeminjamanRuangan($id, $data)) {
                header('Location: index.php?page=edit-peminjaman-ruangan&id=' . $id . '&status=success');
            } else {
                header('Location: index.php?page=edit-peminjaman-ruangan&id=' . $id . '&status=error');
            }
            exit;
        }
    }

    // Proses hapus peminjaman ruangan
    public function hapusPeminjamanRuangan() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
                exit;
            }

            if ($this->model->hapusPeminjamanRuangan($id)) {
                echo json_encode(['success' => true, 'message' => 'Peminjaman ruangan berhasil dihapus']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menghapus peminjaman ruangan']);
            }
            exit;
        }
    }
}
?>

