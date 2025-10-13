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
            $stmt = $this->db->prepare("
                INSERT INTO konten (jenis, judul, divisi, dokumentasi)
                VALUES (:jenis, :judul, :divisi, :dokumentasi)
            ");
            $stmt->bindParam(':jenis', $jenis);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':divisi', $divisi);
            $stmt->bindParam(':dokumentasi', $dokumentasi);
            $stmt->execute();

            return $this->db->lastInsertId(); // return id konten baru
        } catch (PDOException $e) {
            error_log("[ERROR] Insert Konten: " . $e->getMessage());
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

            if (!$stmt->execute()) {
                throw new PDOException("Gagal menyimpan data berita");
            }
            return true;
        } catch (PDOException $e) {
            error_log("[ERROR] Insert Berita: " . $e->getMessage());
            $this->deleteKonten($idKonten);
            return false;
        }
    }

    // === INSERT KONTEN MEDIA SOSIAL ===
    public function insertMedsos($idKonten, $tanggalPost = null, $linkPost = null, $caption = null) {
        try {
            // pastikan minimal ada salah satu data
            if (empty($tanggalPost) && empty($linkPost) && empty($caption)) {
                throw new PDOException("Semua data medsos kosong, tidak bisa disimpan.");
            }

            $stmt = $this->db->prepare("
                INSERT INTO konten_medsos (id_konten, tanggal_post, link_post, caption)
                VALUES (:id_konten, :tanggal_post, :link_post, :caption)
            ");
            $stmt->bindParam(':id_konten', $idKonten);
            $stmt->bindParam(':tanggal_post', $tanggalPost);
            $stmt->bindParam(':link_post', $linkPost);
            $stmt->bindParam(':caption', $caption);

            if (!$stmt->execute()) {
                throw new PDOException("Gagal menyimpan data medsos");
            }
            return true;
        } catch (PDOException $e) {
            error_log("[ERROR] Insert Medsos: " . $e->getMessage());
            $this->deleteKonten($idKonten);
            return false;
        }
    }

    // === GET SEMUA KONTEN ===
    public function getAllKonten() {
        $stmt = $this->db->query("SELECT * FROM konten ORDER BY tanggal_input DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === DETAIL BERITA ===
    public function getDetailBerita() {
        $stmt = $this->db->query("
            SELECT kb.*, k.judul, k.jenis, k.divisi, k.dokumentasi
            FROM konten_berita kb
            JOIN konten k ON k.id_konten = kb.id_konten
            ORDER BY kb.id_berita DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === DETAIL MEDSOS ===
    public function getDetailMedsos() {
        $stmt = $this->db->query("
            SELECT km.*, k.judul, k.jenis, k.divisi, k.dokumentasi
            FROM konten_medsos km
            JOIN konten k ON k.id_konten = km.id_konten
            ORDER BY km.id_medsos DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // === HAPUS KONTEN ===
    public function deleteKonten($idKonten) {
        try {
            $stmt = $this->db->prepare("DELETE FROM konten WHERE id_konten = :id_konten");
            $stmt->bindParam(':id_konten', $idKonten);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[ERROR] Delete Konten: " . $e->getMessage());
            return false;
        }
    }

    // === GET KONTEN BY ID ===
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
    
    // Search konten
    public function searchKonten($keyword) {
    $db = $this->db; // misal $this->db adalah PDO
    $keyword = "%$keyword%";

    $sql = "
        SELECT k.*, kb.tanggal_berita, kb.link_berita, kb.sumber_berita, kb.jenis_berita, kb.ringkasan,
               km.tanggal_post, km.link_post, km.caption
        FROM konten k
        LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
        LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
        WHERE k.judul LIKE :keyword
           OR kb.link_berita LIKE :keyword
           OR kb.sumber_berita LIKE :keyword
           OR kb.ringkasan LIKE :keyword
           OR km.link_post LIKE :keyword
           OR km.caption LIKE :keyword
        ORDER BY k.tanggal_input DESC
    ";

    $stmt = $db->prepare($sql);
    $stmt->bindParam(':keyword', $keyword);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
