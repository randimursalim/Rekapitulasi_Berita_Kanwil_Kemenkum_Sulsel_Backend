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
                    (nama, telp, email, alamat, tujuan, foto, ttd, tgl, jam) 
                    VALUES 
                    (:nama, :telp, :email, :alamat, :tujuan, :foto, :ttd, CURDATE(), CURTIME())";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                ':nama'   => $data['nama'],
                ':telp'   => $data['telp'],
                ':email'  => $data['email'],
                ':alamat' => $data['alamat'],
                ':tujuan' => $data['tujuan'],
                ':foto'   => $data['foto'],
                ':ttd'    => $data['ttd'],
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

    public function getTamuByBulan($tahun, $bulan)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM tb_tamu
        WHERE YEAR(tgl) = ?
        AND MONTH(tgl) = ?
        ORDER BY tgl ASC, jam ASC
    ");
        $stmt->execute([$tahun, $bulan]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTamuByTahun($tahun)
    {
        $stmt = $this->db->prepare("
        SELECT * FROM tb_tamu
        WHERE YEAR(tgl) = ?
        ORDER BY tgl ASC, jam ASC
    ");
        $stmt->execute([$tahun]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
