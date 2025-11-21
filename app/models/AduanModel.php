<?php
require_once __DIR__ . '/../../config/database.php';

class AduanModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // Ambil semua aduan dengan pagination dan filter
    public function getAllAduan($page = 1, $limit = 10, $search = '', $startDate = '', $endDate = '') {
        $offset = ($page - 1) * $limit;
        
        // Base query
        $query = "
        SELECT id_aduan, no_register, tanggal, aduan, jenis_aduan, media_digunakan, tindak_lanjut, keterangan, created_at, updated_at
        FROM aduan
        WHERE 1=1
        ";
        
        $params = [];
        
        // Filter search
        if ($search !== '') {
            $searchParam = "%{$search}%";
            $query .= " AND (no_register LIKE ? OR aduan LIKE ? OR jenis_aduan LIKE ? OR media_digunakan LIKE ?)";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Filter tanggal
        if ($startDate !== '' && $endDate !== '') {
            $query .= " AND DATE(tanggal) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        // Hitung total data
        $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as sub";
        $stmtCount = $this->db->prepare($countQuery);
        $stmtCount->execute($params);
        $totalData = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Tambahkan limit dan order
        $query .= " ORDER BY tanggal DESC, created_at DESC LIMIT $limit OFFSET $offset";
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

    // Ambil aduan berdasarkan ID
    public function getAduanById($id) {
        $query = "SELECT * FROM aduan WHERE id_aduan = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tambah aduan baru
    public function tambahAduan($data) {
        $query = "INSERT INTO aduan (no_register, tanggal, aduan, jenis_aduan, media_digunakan, tindak_lanjut, keterangan) 
                  VALUES (:no_register, :tanggal, :aduan, :jenis_aduan, :media_digunakan, :tindak_lanjut, :keterangan)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':no_register', $data['no_register']);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':aduan', $data['aduan']);
        $stmt->bindParam(':jenis_aduan', $data['jenis_aduan']);
        $stmt->bindParam(':media_digunakan', $data['media_digunakan']);
        $stmt->bindParam(':tindak_lanjut', $data['tindak_lanjut']);
        $stmt->bindParam(':keterangan', $data['keterangan']);

        return $stmt->execute();
    }

    // Update aduan
    public function updateAduan($id, $data) {
        $query = "UPDATE aduan SET 
                  no_register = :no_register,
                  tanggal = :tanggal,
                  aduan = :aduan,
                  jenis_aduan = :jenis_aduan,
                  media_digunakan = :media_digunakan,
                  tindak_lanjut = :tindak_lanjut,
                  keterangan = :keterangan
                  WHERE id_aduan = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':no_register', $data['no_register']);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':aduan', $data['aduan']);
        $stmt->bindParam(':jenis_aduan', $data['jenis_aduan']);
        $stmt->bindParam(':media_digunakan', $data['media_digunakan']);
        $stmt->bindParam(':tindak_lanjut', $data['tindak_lanjut']);
        $stmt->bindParam(':keterangan', $data['keterangan']);

        return $stmt->execute();
    }

    // Hapus aduan
    public function hapusAduan($id) {
        $query = "DELETE FROM aduan WHERE id_aduan = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

