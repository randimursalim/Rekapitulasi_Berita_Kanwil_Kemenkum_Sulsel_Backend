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
               kategori_laporan, jenis_aduan, jenis_aduan_lainnya, tanggal_pengaduan, tindak_lanjut, keterangan
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

    // Generate nomor register acak dengan prefix "P"
    public function generateNoRegister() {
        try {
            $maxAttempts = 10; // Maksimal 10 kali coba untuk menghindari infinite loop
            $attempt = 0;
            
            do {
                // Generate nomor acak 6 digit (P + 6 angka)
                // Format: P123456, P789012, dll
                $randomNumber = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
                $noRegister = 'P' . $randomNumber;
                
                // Cek apakah nomor sudah ada
                $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM layanan_pengaduan WHERE no_register_pengaduan = :no_register");
                $stmt->bindParam(':no_register', $noRegister);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $attempt++;
                
                // Jika nomor belum ada, return
                if ($result['count'] == 0) {
                    return $noRegister;
                }
                
            } while ($attempt < $maxAttempts);
            
            // Jika setelah 10 kali masih duplikat, gunakan timestamp sebagai fallback
            $timestamp = substr(time(), -6); // Ambil 6 digit terakhir dari timestamp
            return 'P' . $timestamp;
            
        } catch (PDOException $e) {
            error_log("[ERROR] Generate No Register: " . $e->getMessage());
            // Fallback: use timestamp-based number
            $timestamp = substr(time(), -6);
            return 'P' . $timestamp;
        }
    }

    // Ambil layanan pengaduan berdasarkan nomor register
    public function getLayananPengaduanByNoRegister($noRegister) {
        $query = "SELECT * FROM layanan_pengaduan WHERE no_register_pengaduan = :no_register";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':no_register', $noRegister);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tambah layanan pengaduan baru
    public function tambahLayananPengaduan($data) {
        try {
            $query = "INSERT INTO layanan_pengaduan (no_register_pengaduan, nama, alamat, jenis_tanda_pengenal, jenis_tanda_pengenal_lainnya, no_tanda_pengenal, no_telp, judul_laporan, isi_laporan, tanggal_kejadian, lokasi_kejadian, kategori_laporan, jenis_aduan, jenis_aduan_lainnya, tindak_lanjut, keterangan) 
                      VALUES (:no_register_pengaduan, :nama, :alamat, :jenis_tanda_pengenal, :jenis_tanda_pengenal_lainnya, :no_tanda_pengenal, :no_telp, :judul_laporan, :isi_laporan, :tanggal_kejadian, :lokasi_kejadian, :kategori_laporan, :jenis_aduan, :jenis_aduan_lainnya, :tindak_lanjut, :keterangan)";
            
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
            
            // Handle tindak_lanjut (default: 'belum diproses')
            $tindakLanjut = $data['tindak_lanjut'] ?? 'belum diproses';
            $stmt->bindParam(':tindak_lanjut', $tindakLanjut);
            
            // Handle keterangan (nullable)
            $keterangan = isset($data['keterangan']) && $data['keterangan'] !== '' ? $data['keterangan'] : null;
            error_log("[LAYANAN PENGADUAN MODEL] Keterangan value: " . ($keterangan ?? 'NULL'));
            error_log("[LAYANAN PENGADUAN MODEL] Keterangan length: " . ($keterangan ? strlen($keterangan) : 0));
            if ($keterangan !== null) {
                $stmt->bindParam(':keterangan', $keterangan);
                error_log("[LAYANAN PENGADUAN MODEL] Binding keterangan as string: " . substr($keterangan, 0, 100) . "...");
            } else {
                $stmt->bindValue(':keterangan', null, PDO::PARAM_NULL);
                error_log("[LAYANAN PENGADUAN MODEL] Binding keterangan as NULL");
            }

            error_log("[LAYANAN PENGADUAN MODEL] Executing INSERT query...");
            $result = $stmt->execute();
            
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("[ERROR] Tambah Layanan Pengaduan Model: " . print_r($errorInfo, true));
            } else {
                error_log("[LAYANAN PENGADUAN MODEL] INSERT successful! Last insert ID: " . $this->db->lastInsertId());
                // Verify data was saved
                $lastId = $this->db->lastInsertId();
                $verifyStmt = $this->db->prepare("SELECT keterangan FROM layanan_pengaduan WHERE id = ?");
                $verifyStmt->execute([$lastId]);
                $verifyData = $verifyStmt->fetch(PDO::FETCH_ASSOC);
                error_log("[LAYANAN PENGADUAN MODEL] Verified saved keterangan: " . ($verifyData['keterangan'] ?? 'NULL'));
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
                      jenis_aduan_lainnya = :jenis_aduan_lainnya,
                      tindak_lanjut = :tindak_lanjut,
                      keterangan = :keterangan
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
            
            // Handle tindak_lanjut
            $tindakLanjut = $data['tindak_lanjut'] ?? 'belum diproses';
            $stmt->bindParam(':tindak_lanjut', $tindakLanjut);
            
            // Handle keterangan (nullable)
            $keterangan = !empty($data['keterangan']) ? $data['keterangan'] : null;
            if ($keterangan !== null) {
                $stmt->bindParam(':keterangan', $keterangan);
            } else {
                $stmt->bindValue(':keterangan', null, PDO::PARAM_NULL);
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

