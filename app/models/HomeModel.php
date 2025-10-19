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
            // Cek apakah tabel log_aktivitas ada
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'log_aktivitas'");
            $stmt->execute();
            $tableExists = $stmt->fetch();
            
            if ($tableExists) {
                // Ambil log aktivitas dari tabel log_aktivitas dengan JOIN ke tabel pengguna
                $stmt = $this->db->prepare("
                    SELECT 
                        la.aktivitas,
                        la.tanggal,
                        la.waktu,
                        COALESCE(p.nama, la.user) as user,
                        la.status
                    FROM log_aktivitas la
                    LEFT JOIN pengguna p ON la.id_pengguna = p.id_pengguna
                    ORDER BY la.tanggal DESC, la.waktu DESC 
                    LIMIT 10
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
            } else {
                // Jika tabel tidak ada, return dummy
                return [
                    ['aktivitas'=>'Tabel log_aktivitas belum dibuat','tanggal'=>date('Y-m-d'),'waktu'=>date('H:i'),'user'=>'System','status'=>'Info'],
                ];
            }
        } catch (Exception $e) {
            // Log error untuk debugging
            error_log("Error in getLogAktivitas (log_aktivitas table): " . $e->getMessage());
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

    public function addLogAktivitas($aktivitas, $id_user = null, $status = 'Berhasil') {
        try {
            // Ambil nama user yang sedang login (yang melakukan aktivitas)
            $currentUser = 'System'; // Default
            if (session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['user']['nama'])) {
                $currentUser = $_SESSION['user']['nama'];
            }
            
            // Jika id_user tidak diberikan, ambil dari session
            if ($id_user === null && session_status() == PHP_SESSION_ACTIVE && isset($_SESSION['user']['id_pengguna'])) {
                $id_user = $_SESSION['user']['id_pengguna'];
            }

            $stmt = $this->db->prepare("
                INSERT INTO log_aktivitas (aktivitas, tanggal, waktu, user, status, id_pengguna) 
                VALUES (?, CURDATE(), CURTIME(), ?, ?, ?)
            ");
            $stmt->execute([$aktivitas, $currentUser, $status, $id_user]);
            return true;
        } catch (Exception $e) {
            // Jika tabel belum ada, tidak perlu error
            return false;
        }
    }

    public function debugLogAktivitas() {
        try {
            // Test koneksi database
            $stmt = $this->db->prepare("SELECT 1");
            $stmt->execute();
            
            // Cek apakah tabel konten ada
            $stmt = $this->db->prepare("SHOW TABLES LIKE 'konten'");
            $stmt->execute();
            $kontenExists = $stmt->fetch();
            
            if (!$kontenExists) {
                return ['error' => 'Tabel konten tidak ditemukan'];
            }
            
            // Cek struktur tabel konten
            $stmt = $this->db->prepare("DESCRIBE konten");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Cek data konten
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM konten");
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'database_connected' => true,
                'konten_table_exists' => true,
                'columns' => $columns,
                'total_konten' => $total['total']
            ];
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}

