<?php
require_once __DIR__ . '/../../config/database.php';

class HomeModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    public function getStatistik() {
        try {
            // Total berita
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM konten WHERE jenis = 'berita'");
            $stmt->execute();
            $totalBerita = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total medsos
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM konten WHERE jenis IN ('instagram', 'youtube', 'tiktok', 'twitter', 'facebook')");
            $stmt->execute();
            $totalMedsos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total arsip (semua konten)
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM konten");
            $stmt->execute();
            $totalArsip = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                'total_berita' => $totalBerita,
                'total_medsos' => $totalMedsos,
                'total_arsip'  => $totalArsip,
            ];
        } catch (Exception $e) {
            // Fallback jika error
            return [
                'total_berita' => 0,
                'total_medsos' => 0,
                'total_arsip'  => 0,
            ];
        }
    }

    public function getLogAktivitas() {
        try {
            // Ambil log aktivitas dari database (jika ada tabel log)
            // Untuk sementara, kita buat dummy data berdasarkan data konten terbaru
            $stmt = $this->db->prepare("
                SELECT 
                    CONCAT('Menambahkan konten: ', judul) as aktivitas,
                    DATE(tanggal_input) as tanggal,
                    TIME(tanggal_input) as waktu,
                    'Admin' as user,
                    'Tambah' as status
                FROM konten 
                ORDER BY tanggal_input DESC 
                LIMIT 5
            ");
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Jika tidak ada data, return dummy
            if (empty($logs)) {
                return [
                    ['aktivitas'=>'Belum ada aktivitas','tanggal'=>date('Y-m-d'),'waktu'=>date('H:i'),'user'=>'System','status'=>'Info'],
                ];
            }

            return $logs;
        } catch (Exception $e) {
            return [
                ['aktivitas'=>'Error loading logs','tanggal'=>date('Y-m-d'),'waktu'=>date('H:i'),'user'=>'System','status'=>'Error'],
            ];
        }
    }

    public function getDetailBerita() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN jenis_berita = 'media_online' THEN 'Media Online'
                        WHEN jenis_berita = 'surat_kabar' THEN 'Surat Kabar'
                        WHEN jenis_berita = 'website_kanwil' THEN 'Website Kanwil'
                        ELSE 'Lainnya'
                    END as name,
                    COUNT(*) as value
                FROM konten k
                INNER JOIN konten_berita kb ON k.id_konten = kb.id_konten
                WHERE k.jenis = 'berita'
                GROUP BY jenis_berita
            ");
            $stmt->execute();
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Jika tidak ada data, return empty
            if (empty($details)) {
                return [
                    ['name' => 'Media Online', 'value' => 0],
                    ['name' => 'Surat Kabar', 'value' => 0],
                    ['name' => 'Website Kanwil', 'value' => 0],
                ];
            }

            return $details;
        } catch (Exception $e) {
            return [
                ['name' => 'Media Online', 'value' => 0],
                ['name' => 'Surat Kabar', 'value' => 0],
                ['name' => 'Website Kanwil', 'value' => 0],
            ];
        }
    }

    public function getDetailMedsos() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    CASE 
                        WHEN jenis = 'facebook' THEN 'Facebook'
                        WHEN jenis = 'instagram' THEN 'Instagram'
                        WHEN jenis = 'twitter' THEN 'Twitter (X)'
                        WHEN jenis = 'tiktok' THEN 'TikTok'
                        WHEN jenis = 'youtube' THEN 'Youtube'
                        ELSE jenis
                    END as name,
                    COUNT(*) as value
                FROM konten 
                WHERE jenis IN ('instagram', 'youtube', 'tiktok', 'twitter', 'facebook')
                GROUP BY jenis
                ORDER BY value DESC
            ");
            $stmt->execute();
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Jika tidak ada data, return empty
            if (empty($details)) {
                return [
                    ['name' => 'Facebook', 'value' => 0],
                    ['name' => 'Instagram', 'value' => 0],
                    ['name' => 'Twitter (X)', 'value' => 0],
                    ['name' => 'TikTok', 'value' => 0],
                    ['name' => 'Youtube', 'value' => 0],
                ];
            }

            return $details;
        } catch (Exception $e) {
            return [
                ['name' => 'Facebook', 'value' => 0],
                ['name' => 'Instagram', 'value' => 0],
                ['name' => 'Twitter (X)', 'value' => 0],
                ['name' => 'TikTok', 'value' => 0],
                ['name' => 'Youtube', 'value' => 0],
            ];
        }
    }
}

