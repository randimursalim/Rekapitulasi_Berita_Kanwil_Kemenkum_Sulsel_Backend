<?php
require_once __DIR__ . '/../../config/database.php';

class PeminjamanRuanganModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // Ambil semua peminjaman ruangan
    public function getAllPeminjamanRuangan() {
        $query = "SELECT * FROM jadwal_peminjaman_ruangan ORDER BY tanggal_kegiatan DESC, waktu_kegiatan ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil peminjaman ruangan berdasarkan ID
    public function getPeminjamanRuanganById($id) {
        $query = "SELECT * FROM jadwal_peminjaman_ruangan WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tambah peminjaman ruangan baru
    public function tambahPeminjamanRuangan($data) {
        // Gunakan durasi dari data yang dikirim (sudah divalidasi di controller)
        $durasi = isset($data['durasi_kegiatan']) && $data['durasi_kegiatan'] > 0 
                  ? floatval($data['durasi_kegiatan']) 
                  : 2; // Fallback hanya jika benar-benar tidak ada
        
        // Cek apakah kolom durasi_kegiatan ada di tabel
        $checkColumn = $this->db->query("SHOW COLUMNS FROM jadwal_peminjaman_ruangan LIKE 'durasi_kegiatan'");
        $columnExists = $checkColumn->rowCount() > 0;
        
        if ($columnExists) {
            $query = "INSERT INTO jadwal_peminjaman_ruangan (nama_peminjam, nama_ruangan, kegiatan, tanggal_kegiatan, waktu_kegiatan, durasi_kegiatan) 
                      VALUES (:nama_peminjam, :nama_ruangan, :kegiatan, :tanggal_kegiatan, :waktu_kegiatan, :durasi_kegiatan)";
        } else {
            $query = "INSERT INTO jadwal_peminjaman_ruangan (nama_peminjam, nama_ruangan, kegiatan, tanggal_kegiatan, waktu_kegiatan) 
                      VALUES (:nama_peminjam, :nama_ruangan, :kegiatan, :tanggal_kegiatan, :waktu_kegiatan)";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nama_peminjam', $data['nama_peminjam']);
        $stmt->bindParam(':nama_ruangan', $data['nama_ruangan']);
        $stmt->bindParam(':kegiatan', $data['kegiatan']);
        $stmt->bindParam(':tanggal_kegiatan', $data['tanggal_kegiatan']);
        $stmt->bindParam(':waktu_kegiatan', $data['waktu_kegiatan']);
        
        if ($columnExists) {
            $stmt->bindValue(':durasi_kegiatan', $durasi, PDO::PARAM_STR); // Gunakan STR untuk DECIMAL
        }

        return $stmt->execute();
    }

    // Update peminjaman ruangan
    public function updatePeminjamanRuangan($id, $data) {
        // Cek apakah kolom durasi_kegiatan ada
        $checkColumn = $this->db->query("SHOW COLUMNS FROM jadwal_peminjaman_ruangan LIKE 'durasi_kegiatan'");
        $columnExists = $checkColumn->rowCount() > 0;
        
        // Gunakan durasi dari data yang dikirim (sudah divalidasi di controller)
        $durasi = isset($data['durasi_kegiatan']) && $data['durasi_kegiatan'] > 0 
                  ? floatval($data['durasi_kegiatan']) 
                  : 2; // Fallback hanya jika benar-benar tidak ada
        
        if ($columnExists) {
            $query = "UPDATE jadwal_peminjaman_ruangan SET 
                      nama_peminjam = :nama_peminjam,
                      nama_ruangan = :nama_ruangan,
                      kegiatan = :kegiatan,
                      tanggal_kegiatan = :tanggal_kegiatan,
                      waktu_kegiatan = :waktu_kegiatan,
                      durasi_kegiatan = :durasi_kegiatan
                      WHERE id = :id";
        } else {
            $query = "UPDATE jadwal_peminjaman_ruangan SET 
                      nama_peminjam = :nama_peminjam,
                      nama_ruangan = :nama_ruangan,
                      kegiatan = :kegiatan,
                      tanggal_kegiatan = :tanggal_kegiatan,
                      waktu_kegiatan = :waktu_kegiatan
                      WHERE id = :id";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nama_peminjam', $data['nama_peminjam']);
        $stmt->bindParam(':nama_ruangan', $data['nama_ruangan']);
        $stmt->bindParam(':kegiatan', $data['kegiatan']);
        $stmt->bindParam(':tanggal_kegiatan', $data['tanggal_kegiatan']);
        $stmt->bindParam(':waktu_kegiatan', $data['waktu_kegiatan']);
        
        if ($columnExists) {
            $stmt->bindValue(':durasi_kegiatan', $durasi, PDO::PARAM_STR); // Gunakan STR untuk DECIMAL
        }

        return $stmt->execute();
    }

    // Hapus peminjaman ruangan
    public function hapusPeminjamanRuangan($id) {
        $query = "DELETE FROM jadwal_peminjaman_ruangan WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Ambil peminjaman ruangan berdasarkan tanggal
    public function getPeminjamanRuanganByTanggal($tanggal) {
        $query = "SELECT * FROM jadwal_peminjaman_ruangan WHERE tanggal_kegiatan = :tanggal ORDER BY waktu_kegiatan ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hitung total peminjaman ruangan
    public function getTotalPeminjamanRuangan() {
        $query = "SELECT COUNT(*) as total FROM jadwal_peminjaman_ruangan";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Cek apakah ruangan sudah dipinjam pada tanggal dan waktu yang overlap
    // Menggunakan durasi untuk mengecek overlap waktu
    public function cekKonflikPeminjaman($nama_ruangan, $tanggal_kegiatan, $waktu_kegiatan, $durasi_jam, $exclude_id = null) {
        // Hitung waktu mulai dan selesai untuk kegiatan baru (dalam detik)
        $waktu_mulai_baru = strtotime($tanggal_kegiatan . ' ' . $waktu_kegiatan);
        $waktu_selesai_baru = $waktu_mulai_baru + ($durasi_jam * 3600); // Durasi dalam detik
        
        // Query untuk mengambil semua kegiatan di ruangan dan tanggal yang sama
        $query = "SELECT * FROM jadwal_peminjaman_ruangan 
                  WHERE nama_ruangan = :nama_ruangan 
                  AND tanggal_kegiatan = :tanggal_kegiatan";
        
        if ($exclude_id !== null) {
            $query .= " AND id != :exclude_id";
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nama_ruangan', $nama_ruangan);
        $stmt->bindParam(':tanggal_kegiatan', $tanggal_kegiatan);
        
        if ($exclude_id !== null) {
            $stmt->bindParam(':exclude_id', $exclude_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $existing = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cek overlap dengan setiap kegiatan yang ada
        foreach ($existing as $item) {
            // Hitung waktu mulai dan selesai untuk kegiatan yang sudah ada
            $waktu_mulai_lama = strtotime($item['tanggal_kegiatan'] . ' ' . $item['waktu_kegiatan']);
            // Ambil durasi dari database, pastikan menggunakan floatval untuk handle DECIMAL
            $durasi_lama_jam = isset($item['durasi_kegiatan']) && $item['durasi_kegiatan'] > 0 
                              ? floatval($item['durasi_kegiatan']) 
                              : 2.0; // Default 2 jam jika tidak ada atau 0
            $durasi_lama = $durasi_lama_jam * 3600; // Konversi ke detik
            $waktu_selesai_lama = $waktu_mulai_lama + $durasi_lama;
            
            // Cek overlap: waktu_mulai_baru < waktu_selesai_lama AND waktu_selesai_baru > waktu_mulai_lama
            // Artinya: kegiatan baru mulai sebelum kegiatan lama selesai DAN kegiatan baru selesai setelah kegiatan lama mulai
            if ($waktu_mulai_baru < $waktu_selesai_lama && $waktu_selesai_baru > $waktu_mulai_lama) {
                return $item; // Ada overlap
            }
        }
        
        return false; // Tidak ada overlap
    }
}
?>


