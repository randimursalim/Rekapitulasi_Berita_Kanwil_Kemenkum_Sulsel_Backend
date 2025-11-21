<?php
require_once __DIR__ . '/../../config/database.php';

class LayananPengaduanModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // Ambil semua layanan pengaduan dengan pagination dan filter
    public function getAllLayananPengaduan($page = 1, $limit = 10, $search = '', $startDate = '', $endDate = '') {
        $offset = ($page - 1) * $limit;
        
        // Base query
        $query = "
        SELECT id, no_register_pengaduan, nama, alamat, jenis_tanda_pengenal, jenis_tanda_pengenal_lainnya, no_tanda_pengenal, 
               no_telp, judul_laporan, isi_laporan, tanggal_kejadian, lokasi_kejadian, 
               kategori_laporan, jenis_aduan, jenis_aduan_lainnya, tanggal_pengaduan
        FROM layanan_pengaduan
        WHERE 1=1
        ";
        
        $params = [];
        
        // Filter search
        if ($search !== '') {
            $searchParam = "%{$search}%";
            $query .= " AND (no_register_pengaduan LIKE ? OR nama LIKE ? OR judul_laporan LIKE ? OR isi_laporan LIKE ?)";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Filter tanggal (berdasarkan tanggal_pengaduan)
        if ($startDate !== '' && $endDate !== '') {
            $query .= " AND DATE(tanggal_pengaduan) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        // Hitung total data
        $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as sub";
        $stmtCount = $this->db->prepare($countQuery);
        $stmtCount->execute($params);
        $totalData = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Tambahkan limit dan order
        $query .= " ORDER BY tanggal_pengaduan DESC LIMIT $limit OFFSET $offset";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $data,
            'total' => $totalData,
            'totalPages' => ceil($totalData / $limit),
            'currentPage' => $page
        ];
    }

    // Ambil layanan pengaduan berdasarkan ID
    public function getLayananPengaduanById($id) {
        $query = "SELECT * FROM layanan_pengaduan WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tambah layanan pengaduan baru
    public function tambahLayananPengaduan($data) {
        try {
            $query = "INSERT INTO layanan_pengaduan (no_register_pengaduan, nama, alamat, jenis_tanda_pengenal, jenis_tanda_pengenal_lainnya, no_tanda_pengenal, no_telp, judul_laporan, isi_laporan, tanggal_kejadian, lokasi_kejadian, kategori_laporan, jenis_aduan, jenis_aduan_lainnya) 
                      VALUES (:no_register_pengaduan, :nama, :alamat, :jenis_tanda_pengenal, :jenis_tanda_pengenal_lainnya, :no_tanda_pengenal, :no_telp, :judul_laporan, :isi_laporan, :tanggal_kejadian, :lokasi_kejadian, :kategori_laporan, :jenis_aduan, :jenis_aduan_lainnya)";
            
            $stmt = $this->db->prepare($query);
            $jenisTandaPengenalLainnya = !empty($data['jenis_tanda_pengenal_lainnya']) ? $data['jenis_tanda_pengenal_lainnya'] : null;
            $jenisAduanLainnya = !empty($data['jenis_aduan_lainnya']) ? $data['jenis_aduan_lainnya'] : null;
            
            $stmt->bindParam(':no_register_pengaduan', $data['no_register_pengaduan']);
            $stmt->bindParam(':nama', $data['nama']);
            $stmt->bindParam(':alamat', $data['alamat']);
            $stmt->bindParam(':jenis_tanda_pengenal', $data['jenis_tanda_pengenal']);
            if ($jenisTandaPengenalLainnya !== null) {
                $stmt->bindParam(':jenis_tanda_pengenal_lainnya', $jenisTandaPengenalLainnya);
            } else {
                $stmt->bindValue(':jenis_tanda_pengenal_lainnya', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':no_tanda_pengenal', $data['no_tanda_pengenal']);
            $noTelp = !empty($data['no_telp']) ? $data['no_telp'] : null;
            if ($noTelp !== null) {
                $stmt->bindParam(':no_telp', $noTelp);
            } else {
                $stmt->bindValue(':no_telp', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':judul_laporan', $data['judul_laporan']);
            $stmt->bindParam(':isi_laporan', $data['isi_laporan']);
            $stmt->bindParam(':tanggal_kejadian', $data['tanggal_kejadian']);
            $stmt->bindParam(':lokasi_kejadian', $data['lokasi_kejadian']);
            $stmt->bindParam(':kategori_laporan', $data['kategori_laporan']);
            $stmt->bindParam(':jenis_aduan', $data['jenis_aduan']);
            if ($jenisAduanLainnya !== null) {
                $stmt->bindParam(':jenis_aduan_lainnya', $jenisAduanLainnya);
            } else {
                $stmt->bindValue(':jenis_aduan_lainnya', null, PDO::PARAM_NULL);
            }

            $result = $stmt->execute();
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("[ERROR] Tambah Layanan Pengaduan Model: " . print_r($errorInfo, true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("[ERROR] Tambah Layanan Pengaduan Model PDO: " . $e->getMessage());
            return false;
        }
    }

    // Update layanan pengaduan
    public function updateLayananPengaduan($id, $data) {
        try {
            $query = "UPDATE layanan_pengaduan SET 
                      no_register_pengaduan = :no_register_pengaduan,
                      nama = :nama,
                      alamat = :alamat,
                      jenis_tanda_pengenal = :jenis_tanda_pengenal,
                      jenis_tanda_pengenal_lainnya = :jenis_tanda_pengenal_lainnya,
                      no_tanda_pengenal = :no_tanda_pengenal,
                      no_telp = :no_telp,
                      judul_laporan = :judul_laporan,
                      isi_laporan = :isi_laporan,
                      tanggal_kejadian = :tanggal_kejadian,
                      lokasi_kejadian = :lokasi_kejadian,
                      kategori_laporan = :kategori_laporan,
                      jenis_aduan = :jenis_aduan,
                      jenis_aduan_lainnya = :jenis_aduan_lainnya
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $jenisTandaPengenalLainnya = !empty($data['jenis_tanda_pengenal_lainnya']) ? $data['jenis_tanda_pengenal_lainnya'] : null;
            $jenisAduanLainnya = !empty($data['jenis_aduan_lainnya']) ? $data['jenis_aduan_lainnya'] : null;
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':no_register_pengaduan', $data['no_register_pengaduan']);
            $stmt->bindParam(':nama', $data['nama']);
            $stmt->bindParam(':alamat', $data['alamat']);
            $stmt->bindParam(':jenis_tanda_pengenal', $data['jenis_tanda_pengenal']);
            if ($jenisTandaPengenalLainnya !== null) {
                $stmt->bindParam(':jenis_tanda_pengenal_lainnya', $jenisTandaPengenalLainnya);
            } else {
                $stmt->bindValue(':jenis_tanda_pengenal_lainnya', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':no_tanda_pengenal', $data['no_tanda_pengenal']);
            $noTelp = !empty($data['no_telp']) ? $data['no_telp'] : null;
            if ($noTelp !== null) {
                $stmt->bindParam(':no_telp', $noTelp);
            } else {
                $stmt->bindValue(':no_telp', null, PDO::PARAM_NULL);
            }
            $stmt->bindParam(':judul_laporan', $data['judul_laporan']);
            $stmt->bindParam(':isi_laporan', $data['isi_laporan']);
            $stmt->bindParam(':tanggal_kejadian', $data['tanggal_kejadian']);
            $stmt->bindParam(':lokasi_kejadian', $data['lokasi_kejadian']);
            $stmt->bindParam(':kategori_laporan', $data['kategori_laporan']);
            $stmt->bindParam(':jenis_aduan', $data['jenis_aduan']);
            if ($jenisAduanLainnya !== null) {
                $stmt->bindParam(':jenis_aduan_lainnya', $jenisAduanLainnya);
            } else {
                $stmt->bindValue(':jenis_aduan_lainnya', null, PDO::PARAM_NULL);
            }

            $result = $stmt->execute();
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("[ERROR] Update Layanan Pengaduan Model: " . print_r($errorInfo, true));
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("[ERROR] Update Layanan Pengaduan Model PDO: " . $e->getMessage());
            return false;
        }
    }

    // Hapus layanan pengaduan
    public function hapusLayananPengaduan($id) {
        $query = "DELETE FROM layanan_pengaduan WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

