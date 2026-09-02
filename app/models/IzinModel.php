<?php
// app/models/IzinModel.php
require_once __DIR__ . '/../../config/database.php';

class IzinModel
{
    private $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    // Ambil satu data perizinan
    public function getIzinById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tb_izin WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Simpan data perizinan baru
    public function tambahIzin(array $data)
    {
        try {
            // 1. Cek apakah ada file_balasan
            $fileBalasan = $data['file_balasan'] ?? null;

            $tglBalasan = $fileBalasan ? date('Y-m-d') : null;

            $sql = "INSERT INTO tb_izin 
                    (id, nik, nama, tlp, jenis_surat, tgl, file, keterangan, file_balasan, tgl_balasan, status) 
                    VALUES 
                    (:id, :nik, :nama, :tlp, :jenis_surat, CURDATE(), :file, :keterangan, :file_balasan, :tgl_balasan, :status )";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':id' => $data['id'],
                ':nik' => $data['nik'],
                ':nama' => $data['nama'],
                ':tlp' => $data['tlp'],
                ':jenis_surat' => $data['jenis_surat'],
                ':file' => $data['file'],
                ':keterangan' => $data['keterangan'] ?? null,
                ':file_balasan' => $fileBalasan,
                ':tgl_balasan' => $tglBalasan,
                ':status' => $data['status'],
            ]);
        } catch (PDOException $e) {
            // Untuk debugging (hapus di production)
            // error_log($e->getMessage());
            return false;
        }
    }

    // Update Status Saja
    public function updateStatusIzin($id, $status, $keterangan = null)
    {
        try {
            $sql = "UPDATE tb_izin SET status = :status, keterangan = :keterangan WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':keterangan' => $keterangan,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // public function updateWaTerkirim($id)
    // {
    //     try {
    //         $sql = "UPDATE tb_izin 
    //                 SET wa_terkirim = 1
    //                 WHERE id = :id";

    //         $stmt = $this->db->prepare($sql);

    //         return $stmt->execute([
    //             ':id' => $id
    //         ]);

    //     } catch (PDOException $e) {
    //         return false;
    //     }
    // }

    // update wa success
    public function updateWaSuccess($id, $response = null)
    {
        try {
            $sql = "UPDATE tb_izin 
                    SET wa_status = 'sent',
                        wa_response = :response,
                        wa_sent_at = NOW(),
                        wa_terkirim = 1
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':id' => $id,
                ':response' => $response
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // pending kirim balasan    
    public function setWaPending($id)
    {
        try {
            $sql = "UPDATE tb_izin 
                    SET wa_status = 'pending'
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // update wa failed
    public function updateWaFailed($id, $response = null)
    {
        try {
            $sql = "UPDATE tb_izin 
                    SET wa_status = 'failed',
                        wa_response = :response
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':id' => $id,
                ':response' => $response
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Tambahkan method ini di class IzinModel
    public function updateFileBalasan($id, $filePath)
    {
        try {
            $sql = "UPDATE tb_izin 
                    SET file_balasan = :file, 
                        tgl_balasan = CURDATE(), 
                        status =CASE
                                    WHEN status IN (2,4,6) THEN status
                                    ELSE 6
                                END
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':file' => $filePath,
                ':id' => $id
            ]);
        } catch (PDOException $e) {
            // Untuk debugging (hapus di production)
            // error_log($e->getMessage());
            return false;
        }
    }

    // Hapus Perizinan
    public function hapusIzin($id)
    {
        $stmt = $this->db->prepare("DELETE FROM tb_izin WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getIzinByBulan($tahun, $bulan)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM tb_izin
        WHERE YEAR(tgl) = ?
        AND MONTH(tgl) = ?
        ORDER BY tgl ASC
    ");
        $stmt->execute([$tahun, $bulan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIzinByTahun($tahun)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM tb_izin
        WHERE YEAR(tgl) = ?
        ORDER BY tgl ASC
    ");
        $stmt->execute([$tahun]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ambil status pendaftaran SIMANIS (1 = Buka, 0 = Tutup/Kuota Full)
    public function getStatusSimanis()
    {
        try {
            // Auto create table jika belum ada di DB
            $this->db->exec("CREATE TABLE IF NOT EXISTS tb_setting (
                setting_key VARCHAR(50) NOT NULL PRIMARY KEY,
                setting_value TEXT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $stmt = $this->db->prepare("SELECT setting_value FROM tb_setting WHERE setting_key = 'status_izin_simanis'");
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && isset($row['setting_value'])) {
                return (string) $row['setting_value'];
            }

            // Jika belum ada record, insert default '1' (Buka)
            $stmtInsert = $this->db->prepare("INSERT INTO tb_setting (setting_key, setting_value) VALUES ('status_izin_simanis', '1') ON DUPLICATE KEY UPDATE setting_key=setting_key");
            $stmtInsert->execute();
            return '1';
        } catch (PDOException $e) {
            return '1'; // Fallback default Buka
        }
    }

    // Update status pendaftaran SIMANIS ('1' / '0')
    public function setStatusSimanis($status)
    {
        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS tb_setting (
                setting_key VARCHAR(50) NOT NULL PRIMARY KEY,
                setting_value TEXT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $val = ($status == '1' || $status === 1 || $status === 'open') ? '1' : '0';
            $stmt = $this->db->prepare("INSERT INTO tb_setting (setting_key, setting_value) VALUES ('status_izin_simanis', :val) ON DUPLICATE KEY UPDATE setting_value = :val2");
            return $stmt->execute([':val' => $val, ':val2' => $val]);
        } catch (PDOException $e) {
            return false;
        }
    }
}

