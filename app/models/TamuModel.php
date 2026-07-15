<?php
// app/models/TamuModel.php
require_once __DIR__ . '/../../config/database.php';

class TamuModel
{
    private $db;

    public function __construct()
    {
        global $conn;
        $this->db = $conn;
    }

    // Ambil satu data tamu
    public function getTamuById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM tb_tamu WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Simpan data tamu baru
    public function tambahTamu(array $data)
    {
        try {
            $sql = "INSERT INTO tb_tamu 
                    (nama, telp, email, alamat, tujuan, layanan, layanan_item, entrain, foto, ttd, tgl, jam) 
                    VALUES 
                    (:nama, :telp, :email, :alamat, :tujuan, :layanan, :layanan_item, :entrain, :foto, :ttd, CURDATE(), CURTIME())";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':nama' => $data['nama'],
                ':telp' => $data['telp'],
                ':email' => $data['email'],
                ':alamat' => $data['alamat'],
                ':tujuan' => $data['tujuan'],
                ':layanan' => $data['layanan'],
                ':layanan_item' => $data['layanan_item'],
                ':entrain' => $data['entrain'],
                ':foto' => $data['foto'],
                ':ttd' => $data['ttd'],
            ]);
        } catch (PDOException $e) {
            // Untuk debugging (hapus di production)
            // error_log($e->getMessage());
            return false;
        }
    }

    // Hapus tamu
    public function hapusTamu($id)
    {
        $stmt = $this->db->prepare("DELETE FROM tb_tamu WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTamuByBulan($tahun, $bulan, $layanan = '', $layanan_item = '')
    {
        return $this->getTamuFiltered($tahun, $bulan, $layanan, $layanan_item);
    }

    public function getTamuByTahun($tahun, $layanan = '', $layanan_item = '')
    {
        return $this->getTamuFiltered($tahun, 'all', $layanan, $layanan_item);
    }

    public function getTamuFiltered($startDate = '', $endDate = '', $layanan = '', $layanan_item = '')
    {
        $sql = "SELECT * FROM tb_tamu WHERE 1=1";
        $params = [];
        
        if (!empty($startDate) && is_numeric($startDate) && strlen($startDate) === 4) {
            $sql .= " AND YEAR(tgl) = :tahun";
            $params[':tahun'] = $startDate;
            
            if (!empty($endDate) && $endDate !== 'all') {
                $sql .= " AND MONTH(tgl) = :bulan";
                $params[':bulan'] = $endDate;
            }
        } else {
            if (!empty($startDate)) {
                $sql .= " AND tgl >= :startDate";
                $params[':startDate'] = $startDate;
            }
            
            if (!empty($endDate) && $endDate !== 'all') {
                $sql .= " AND tgl <= :endDate";
                $params[':endDate'] = $endDate;
            }
        }
        
        if (!empty($layanan)) {
            $sql .= " AND layanan = :layanan";
            $params[':layanan'] = $layanan;
        }
        
        if (!empty($layanan_item)) {
            $sql .= " AND layanan_item = :layanan_item";
            $params[':layanan_item'] = $layanan_item;
        }
        
        $sql .= " ORDER BY tgl ASC, jam ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // fungsi export excel
    public function getTamuExportExcel($startDate = '', $endDate = '', $layanan = '', $layanan_item = '')
    {
        $sql = "SELECT * FROM tb_tamu WHERE 1=1";
        $params = [];
        
        if (!empty($startDate) && is_numeric($startDate) && strlen($startDate) === 4) {
            $sql .= " AND YEAR(tgl) = :tahun";
            $params[':tahun'] = $startDate;
            
            if (!empty($endDate) && $endDate !== 'all') {
                $sql .= " AND MONTH(tgl) = :bulan";
                $params[':bulan'] = $endDate;
            }
        } else {
            if (!empty($startDate)) {
                $sql .= " AND tgl >= :startDate";
                $params[':startDate'] = $startDate;
            }
            
            if (!empty($endDate) && $endDate !== 'all') {
                $sql .= " AND tgl <= :endDate";
                $params[':endDate'] = $endDate;
            }
        }
        
        if (!empty($layanan)) {
            $sql .= " AND layanan = :layanan";
            $params[':layanan'] = $layanan;
        }
        
        if (!empty($layanan_item)) {
            $sql .= " AND layanan_item = :layanan_item";
            $params[':layanan_item'] = $layanan_item;
        }
        
        $sql .= " ORDER BY
            MONTH(tgl) ASC,
            FIELD(
                layanan,
                'priority',
                'adm',
                'ahu',
                'ki',
                'p3h'
            ) ASC,
            layanan_item ASC,
            tgl ASC,
            jam ASC";
            
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update data tamu
    public function updateTamu(array $data)
    {
        try {
            $sql = "UPDATE tb_tamu SET 
                    nama = :nama, 
                    telp = :telp, 
                    email = :email, 
                    alamat = :alamat, 
                    tujuan = :tujuan, 
                    layanan = :layanan, 
                    layanan_item = :layanan_item, 
                    entrain = :entrain, 
                    foto = :foto, 
                    ttd = :ttd 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':id' => $data['id'],
                ':nama' => $data['nama'],
                ':telp' => $data['telp'],
                ':email' => $data['email'],
                ':alamat' => $data['alamat'],
                ':tujuan' => $data['tujuan'],
                ':layanan' => $data['layanan'],
                ':layanan_item' => $data['layanan_item'],
                ':entrain' => $data['entrain'],
                ':foto' => $data['foto'],
                ':ttd' => $data['ttd']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
