<?php
require_once __DIR__ . '/../../config/database.php';

class KontenModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // === INSERT KONTEN UTAMA ===
    public function insertKonten($jenis, $judul, $divisi, $dokumentasi = null) {
        try {
            $stmt = $this->db->prepare("INSERT INTO konten (jenis, judul, divisi, dokumentasi) VALUES (:jenis, :judul, :divisi, :dokumentasi)");
            $stmt->bindParam(':jenis', $jenis);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':divisi', $divisi);
            $stmt->bindParam(':dokumentasi', $dokumentasi);
            $stmt->execute();
            return $this->db->lastInsertId(); // mengembalikan ID konten yang baru
        } catch (PDOException $e) {
            error_log("Insert Konten Error: " . $e->getMessage());
            return false;
        }
    }

    // === INSERT KONTEN BERITA ===
    public function insertBerita($idKonten, $tanggalBerita = null, $linkBerita = null, $sumberBerita = null, $jenisBerita = null, $ringkasan = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO konten_berita (id_konten, tanggal_berita, link_berita, sumber_berita, jenis_berita, ringkasan)
                VALUES (:id_konten, :tanggal_berita, :link_berita, :sumber_berita, :jenis_berita, :ringkasan)
            ");
            $stmt->bindParam(':id_konten', $idKonten);
            $stmt->bindParam(':tanggal_berita', $tanggalBerita);
            $stmt->bindParam(':link_berita', $linkBerita);
            $stmt->bindParam(':sumber_berita', $sumberBerita);
            $stmt->bindParam(':jenis_berita', $jenisBerita);
            $stmt->bindParam(':ringkasan', $ringkasan);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Insert Berita Error: " . $e->getMessage());
            // jika gagal, hapus konten utama untuk menghindari dangling data
            $this->deleteKonten($idKonten);
            return false;
        }
    }

    // === INSERT KONTEN MEDIA SOSIAL ===
    public function insertMedsos($idKonten, $tanggalPost = null, $linkPost = null, $caption = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO konten_medsos (id_konten, tanggal_post, link_post, caption)
                VALUES (:id_konten, :tanggal_post, :link_post, :caption)
            ");
            $stmt->bindParam(':id_konten', $idKonten);
            $stmt->bindParam(':tanggal_post', $tanggalPost);
            $stmt->bindParam(':link_post', $linkPost);
            $stmt->bindParam(':caption', $caption);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Insert Medsos Error: " . $e->getMessage());
            // hapus konten utama jika gagal
            $this->deleteKonten($idKonten);
            return false;
        }
    }

    // === GET DATA KONTEN UTAMA ===
    public function getAllKonten() {
        $stmt = $this->db->query("SELECT * FROM konten ORDER BY tanggal_input DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === GET DETAIL BERITA ===
    public function getDetailBerita() {
        $stmt = $this->db->query("
            SELECT kb.*, k.judul, k.jenis, k.divisi, k.dokumentasi 
            FROM konten_berita kb
            JOIN konten k ON k.id_konten = kb.id_konten
            ORDER BY kb.id_berita DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === GET DETAIL MEDSOS ===
    public function getDetailMedsos() {
        $stmt = $this->db->query("
            SELECT km.*, k.judul, k.jenis, k.divisi, k.dokumentasi 
            FROM konten_medsos km
            JOIN konten k ON k.id_konten = km.id_konten
            ORDER BY km.id_medsos DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === HAPUS KONTEN UTAMA ===
    public function deleteKonten($idKonten) {
        try {
            $stmt = $this->db->prepare("DELETE FROM konten WHERE id_konten = :id_konten");
            $stmt->bindParam(':id_konten', $idKonten);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Delete Konten Error: " . $e->getMessage());
            return false;
        }
    }

    // === GET KONTEN BY ID (untuk edit) ===
    public function getKontenById($idKonten) {
        $stmt = $this->db->prepare("SELECT * FROM konten WHERE id_konten = :id_konten");
        $stmt->bindParam(':id_konten', $idKonten);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // === GET BERITA BY ID_KONTEN ===
    public function getBeritaById($idKonten) {
        $stmt = $this->db->prepare("SELECT * FROM konten_berita WHERE id_konten = :id_konten");
        $stmt->bindParam(':id_konten', $idKonten);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // === GET MEDSOS BY ID_KONTEN ===
    public function getMedsosById($idKonten) {
        $stmt = $this->db->prepare("SELECT * FROM konten_medsos WHERE id_konten = :id_konten");
        $stmt->bindParam(':id_konten', $idKonten);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
