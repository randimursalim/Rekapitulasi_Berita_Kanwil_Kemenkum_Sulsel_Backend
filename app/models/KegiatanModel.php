<?php
require_once __DIR__ . '/../../config/database.php';

class KegiatanModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // Ambil semua kegiatan
    public function getAllKegiatan() {
        $query = "SELECT * FROM kegiatan ORDER BY tanggal DESC, jam_mulai ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil kegiatan berdasarkan ID
    public function getKegiatanById($id) {
        $query = "SELECT * FROM kegiatan WHERE id_kegiatan = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tambah kegiatan baru
    public function tambahKegiatan($data) {
        $query = "INSERT INTO kegiatan (nama_kegiatan, tanggal, jam_mulai, jam_selesai, keterangan, status) 
                  VALUES (:nama_kegiatan, :tanggal, :jam_mulai, :jam_selesai, :keterangan, :status)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nama_kegiatan', $data['nama_kegiatan']);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':jam_mulai', $data['jam_mulai']);
        $stmt->bindParam(':jam_selesai', $data['jam_selesai']);
        $stmt->bindParam(':keterangan', $data['keterangan']);
        $stmt->bindParam(':status', $data['status']);

        return $stmt->execute();
    }

    // Update kegiatan
    public function updateKegiatan($id, $data) {
        $query = "UPDATE kegiatan SET 
                  nama_kegiatan = :nama_kegiatan,
                  tanggal = :tanggal,
                  jam_mulai = :jam_mulai,
                  jam_selesai = :jam_selesai,
                  keterangan = :keterangan,
                  status = :status
                  WHERE id_kegiatan = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nama_kegiatan', $data['nama_kegiatan']);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':jam_mulai', $data['jam_mulai']);
        $stmt->bindParam(':jam_selesai', $data['jam_selesai']);
        $stmt->bindParam(':keterangan', $data['keterangan']);
        $stmt->bindParam(':status', $data['status']);

        return $stmt->execute();
    }

    // Hapus kegiatan
    public function hapusKegiatan($id) {
        $query = "DELETE FROM kegiatan WHERE id_kegiatan = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Ambil kegiatan berdasarkan status
    public function getKegiatanByStatus($status) {
        $query = "SELECT * FROM kegiatan WHERE status = :status ORDER BY tanggal DESC, jam_mulai ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil kegiatan berdasarkan tanggal
    public function getKegiatanByTanggal($tanggal) {
        $query = "SELECT * FROM kegiatan WHERE tanggal = :tanggal ORDER BY jam_mulai ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tanggal', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hitung total kegiatan
    public function getTotalKegiatan() {
        $query = "SELECT COUNT(*) as total FROM kegiatan";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Hitung kegiatan berdasarkan status
    public function getKegiatanCountByStatus() {
        $query = "SELECT status, COUNT(*) as jumlah FROM kegiatan GROUP BY status";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
