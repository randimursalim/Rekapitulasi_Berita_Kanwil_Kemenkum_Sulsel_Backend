<?php
require_once __DIR__ . '/../../config/database.php';

class StatistikPenggunaModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    public function getStatistikSemuaPengguna() {
        $query = "
            SELECT p.id_pengguna, p.nama, p.username, p.role,
                   (SELECT COUNT(*) FROM konten WHERE id_pengguna = p.id_pengguna) as total_konten,
                   (SELECT COUNT(*) FROM kegiatan WHERE id_pengguna = p.id_pengguna) as total_kegiatan,
                   (SELECT COUNT(*) FROM jadwal_peminjaman_ruangan WHERE id_pengguna = p.id_pengguna) as total_ruangan,
                   (SELECT COUNT(*) FROM aduan WHERE id_pengguna = p.id_pengguna) as total_aduan,
                   (SELECT COUNT(*) FROM layanan_pengaduan WHERE id_pengguna = p.id_pengguna) as total_layanan_pengaduan,
                   (SELECT COUNT(*) FROM harmonisasi WHERE id_pengguna = p.id_pengguna) as total_harmonisasi,
                   (SELECT COUNT(*) FROM tb_tamu WHERE id_pengguna = p.id_pengguna) as total_tamu,
                   (SELECT COUNT(*) FROM log_aktivitas WHERE id_pengguna = p.id_pengguna) as total_log_aktivitas
            FROM pengguna p
            ORDER BY p.role ASC, p.nama ASC
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
