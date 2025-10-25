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
            // Validasi parameter
            if (!$idKonten || !is_numeric($idKonten) || $idKonten <= 0) {
                error_log("[ERROR] Delete Konten: ID tidak valid - " . $idKonten);
                return false;
            }

            // Cek apakah konten ada sebelum hapus
            $existingKonten = $this->getKontenById($idKonten);
            if (!$existingKonten) {
                error_log("[ERROR] Delete Konten: Konten tidak ditemukan - ID: " . $idKonten);
                return false;
            }

            // Mulai transaction untuk memastikan konsistensi data
            $this->db->beginTransaction();

            // Hapus dari tabel konten (akan cascade ke tabel detail karena FOREIGN KEY)
            $stmt = $this->db->prepare("DELETE FROM konten WHERE id_konten = :id_konten");
            $stmt->bindParam(':id_konten', $idKonten);
            $result = $stmt->execute();

            if ($result && $stmt->rowCount() > 0) {
                // Commit transaction jika berhasil
                $this->db->commit();
                error_log("[SUCCESS] Delete Konten: Berhasil hapus konten ID " . $idKonten);
                return true;
            } else {
                // Rollback jika tidak ada row yang terhapus
                $this->db->rollBack();
                error_log("[ERROR] Delete Konten: Tidak ada konten yang terhapus - ID: " . $idKonten);
                return false;
            }

        } catch (PDOException $e) {
            // Rollback transaction jika terjadi error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
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
    
    // === UPDATE KONTEN UTAMA ===
    public function updateKonten($idKonten, $jenis, $judul, $divisi, $dokumentasi = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE konten 
                SET jenis = :jenis, judul = :judul, divisi = :divisi, dokumentasi = :dokumentasi
                WHERE id_konten = :id_konten
            ");
            $stmt->bindParam(':id_konten', $idKonten);
            $stmt->bindParam(':jenis', $jenis);
            $stmt->bindParam(':judul', $judul);
            $stmt->bindParam(':divisi', $divisi);
            $stmt->bindParam(':dokumentasi', $dokumentasi);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[ERROR] Update Konten: " . $e->getMessage());
            return false;
        }
    }

    // === UPDATE KONTEN BERITA ===
    public function updateBerita($idKonten, $tanggalBerita = null, $linkBerita = null, $sumberBerita = null, $jenisBerita = null, $ringkasan = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE konten_berita 
                SET tanggal_berita = :tanggal_berita, link_berita = :link_berita, 
                    sumber_berita = :sumber_berita, jenis_berita = :jenis_berita, ringkasan = :ringkasan
                WHERE id_konten = :id_konten
            ");
            $stmt->bindParam(':id_konten', $idKonten);
            $stmt->bindParam(':tanggal_berita', $tanggalBerita);
            $stmt->bindParam(':link_berita', $linkBerita);
            $stmt->bindParam(':sumber_berita', $sumberBerita);
            $stmt->bindParam(':jenis_berita', $jenisBerita);
            $stmt->bindParam(':ringkasan', $ringkasan);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[ERROR] Update Berita: " . $e->getMessage());
            return false;
        }
    }

    // === UPDATE KONTEN MEDIA SOSIAL ===
    public function updateMedsos($idKonten, $tanggalPost = null, $linkPost = null, $caption = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE konten_medsos 
                SET tanggal_post = :tanggal_post, link_post = :link_post, caption = :caption
                WHERE id_konten = :id_konten
            ");
            $stmt->bindParam(':id_konten', $idKonten);
            $stmt->bindParam(':tanggal_post', $tanggalPost);
            $stmt->bindParam(':link_post', $linkPost);
            $stmt->bindParam(':caption', $caption);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[ERROR] Update Medsos: " . $e->getMessage());
            return false;
        }
    }

    // === GET KONTEN LENGKAP BY ID ===
    public function getKontenLengkapById($idKonten) {
        try {
            $stmt = $this->db->prepare("
        SELECT k.*, kb.tanggal_berita, kb.link_berita, kb.sumber_berita, kb.jenis_berita, kb.ringkasan,
               km.tanggal_post, km.link_post, km.caption
        FROM konten k
        LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
        LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
                WHERE k.id_konten = :id_konten
            ");
            $stmt->bindParam(':id_konten', $idKonten);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[ERROR] Get Konten Lengkap: " . $e->getMessage());
            return false;
        }
    }

    // === REKAP DATA UNTUK GRAFIK ===
    public function getRekapData($filter = 'monthly', $startDate = null, $endDate = null, $jenis = 'all') {
        try {
            $data = [];
            
            switch ($filter) {
                case 'daily':
                    $data = $this->getRekapHarian($startDate, $endDate, $jenis);
                    break;
                case 'weekly':
                    $data = $this->getRekapMingguan($startDate, $endDate, $jenis);
                    break;
                case 'monthly':
                    $data = $this->getRekapBulanan($startDate, $endDate, $jenis);
                    break;
                case 'yearly':
                    $data = $this->getRekapTahunan($startDate, $endDate, $jenis);
                    break;
                case 'range':
                    $data = $this->getRekapRange($startDate, $endDate, $jenis);
                    break;
                default:
                    $data = $this->getRekapBulanan($startDate, $endDate, $jenis);
            }
            
            return $data;
        } catch (PDOException $e) {
            error_log("[ERROR] Get Rekap Data: " . $e->getMessage());
            return ['labels' => [], 'data' => [], 'total' => 0];
        }
    }

    // === REKAP RANGE TANGGAL ===
    private function getRekapRange($startDate, $endDate, $jenis) {
        // Filter range tanggal: tampilkan data agregat untuk range yang dipilih
        $whereClause = $this->buildWhereClause($startDate, $endDate, $jenis);
        
        $sql = "
            SELECT 
                COUNT(DISTINCT k.id_konten) as jumlah
            FROM konten k
            LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
            LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
            WHERE COALESCE(kb.tanggal_berita, km.tanggal_post) IS NOT NULL
            $whereClause
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $jenis);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['jumlah'] ?? 0;
        
        // Format untuk chart - tampilkan sebagai single bar dengan label range tanggal
        $startFormatted = date('d/m/Y', strtotime($startDate));
        $endFormatted = date('d/m/Y', strtotime($endDate));
        $label = $startFormatted . ' - ' . $endFormatted;
        
        return [
            'labels' => [$label],
            'data' => [$total],
            'total' => $total
        ];
    }

    // === REKAP HARIAN ===
    private function getRekapHarian($startDate, $endDate, $jenis) {
        // Filter harian: 7 hari terakhir (termasuk hari ini)
        $endDate = date('Y-m-d'); // Hari ini
        $startDate = date('Y-m-d', strtotime('-6 days')); // 7 hari terakhir
        
        $whereClause = $this->buildWhereClause($startDate, $endDate, $jenis);
        
        $sql = "
            SELECT 
                DATE(COALESCE(kb.tanggal_berita, km.tanggal_post)) as tanggal,
                COUNT(DISTINCT k.id_konten) as jumlah
            FROM konten k
            LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
            LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
            WHERE COALESCE(kb.tanggal_berita, km.tanggal_post) IS NOT NULL
            $whereClause
            GROUP BY DATE(COALESCE(kb.tanggal_berita, km.tanggal_post))
            ORDER BY tanggal DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $jenis);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'daily');
    }

    // === REKAP MINGGUAN ===
    private function getRekapMingguan($startDate, $endDate, $jenis) {
        // Filter mingguan: data mingguan bulan sekarang
        $currentMonth = date('Y-m');
        $startDate = $currentMonth . '-01'; // Tanggal 1 bulan ini
        $endDate = date('Y-m-t'); // Tanggal terakhir bulan ini
        
        $whereClause = $this->buildWhereClause($startDate, $endDate, $jenis);
        
        $sql = "
            SELECT 
                COALESCE(kb.tanggal_berita, km.tanggal_post) as tanggal,
                COUNT(DISTINCT k.id_konten) as jumlah
            FROM konten k
            LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
            LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
            WHERE COALESCE(kb.tanggal_berita, km.tanggal_post) IS NOT NULL
            AND COALESCE(kb.tanggal_berita, km.tanggal_post) >= :startDate
            AND COALESCE(kb.tanggal_berita, km.tanggal_post) <= :endDate
            $whereClause
            GROUP BY DATE(COALESCE(kb.tanggal_berita, km.tanggal_post))
            ORDER BY tanggal ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $this->bindWhereParams($stmt, $startDate, $endDate, $jenis);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'weekly');
    }

    // === REKAP BULANAN ===
    private function getRekapBulanan($startDate, $endDate, $jenis) {
        $whereClause = $this->buildWhereClause($startDate, $endDate, $jenis);
        
        $sql = "
            SELECT 
                MONTH(COALESCE(kb.tanggal_berita, km.tanggal_post)) as bulan,
                YEAR(COALESCE(kb.tanggal_berita, km.tanggal_post)) as tahun,
                COUNT(DISTINCT k.id_konten) as jumlah
            FROM konten k
            LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
            LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
            WHERE COALESCE(kb.tanggal_berita, km.tanggal_post) IS NOT NULL
            $whereClause
            GROUP BY YEAR(COALESCE(kb.tanggal_berita, km.tanggal_post)), MONTH(COALESCE(kb.tanggal_berita, km.tanggal_post))
            ORDER BY tahun DESC, bulan DESC
            LIMIT 12
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $jenis);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'monthly');
    }

    // === REKAP TAHUNAN ===
    private function getRekapTahunan($startDate, $endDate, $jenis) {
        $whereClause = $this->buildWhereClause($startDate, $endDate, $jenis);
        
        $sql = "
            SELECT 
                YEAR(COALESCE(kb.tanggal_berita, km.tanggal_post)) as tahun,
                COUNT(DISTINCT k.id_konten) as jumlah
            FROM konten k
            LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
            LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
            WHERE COALESCE(kb.tanggal_berita, km.tanggal_post) IS NOT NULL
            $whereClause
            GROUP BY YEAR(COALESCE(kb.tanggal_berita, km.tanggal_post))
            ORDER BY tahun DESC
            LIMIT 10
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $jenis);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'yearly');
    }

    // === BUILD WHERE CLAUSE ===
    private function buildWhereClause($startDate, $endDate, $jenis) {
        $conditions = [];
        
        if ($startDate && $endDate) {
            $conditions[] = "COALESCE(kb.tanggal_berita, km.tanggal_post) BETWEEN :start_date AND :end_date";
        }
        
        if ($jenis !== 'all') {
            if ($jenis === 'berita') {
                $conditions[] = "k.jenis = 'berita'";
            } elseif ($jenis === 'medsos') {
                $conditions[] = "k.jenis IN ('instagram', 'youtube', 'tiktok', 'twitter', 'facebook')";
            } elseif (in_array($jenis, ['media_online', 'surat_kabar', 'website_kanwil'])) {
                // Filter berdasarkan jenis_berita untuk berita
                $conditions[] = "k.jenis = 'berita' AND kb.jenis_berita = :jenis_berita";
            } else {
                $conditions[] = "k.jenis = :jenis";
            }
        }
        
        return empty($conditions) ? '' : ' AND ' . implode(' AND ', $conditions);
    }

    // === BIND WHERE PARAMS ===
    private function bindWhereParams($stmt, $startDate, $endDate, $jenis) {
        if ($startDate && $endDate) {
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
        }
        
        if ($jenis !== 'all' && $jenis !== 'berita' && $jenis !== 'medsos') {
            if (in_array($jenis, ['media_online', 'surat_kabar', 'website_kanwil'])) {
                $stmt->bindParam(':jenis_berita', $jenis);
            } else {
                $stmt->bindParam(':jenis', $jenis);
            }
        }
    }

    // === FORMAT REKAP DATA ===
    private function formatRekapData($results, $type) {
        $labels = [];
        $data = [];
        $total = 0;
        
        // Jika tidak ada data, return skeleton
        if (empty($results)) {
            return [
                'labels' => [],
                'data' => [],
                'total' => 0
            ];
        }
        
        // Untuk filter harian, buat array 7 hari terakhir dengan nilai 0
        if ($type === 'daily') {
            $dailyData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $dailyData[$date] = 0;
            }
            
            // Isi data yang ada
            foreach ($results as $row) {
                $dailyData[$row['tanggal']] = $row['jumlah'];
                $total += $row['jumlah'];
            }
            
            // Format untuk chart
            foreach ($dailyData as $date => $jumlah) {
                $labels[] = date('d/m', strtotime($date));
                $data[] = $jumlah;
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
                'total' => $total
            ];
        }
        
        // Untuk filter mingguan, kelompokkan data per minggu dalam bulan
        if ($type === 'weekly') {
            $weeklyData = [];
            
            foreach ($results as $row) {
                $tanggal = $row['tanggal'];
                $dayOfMonth = (int)date('j', strtotime($tanggal));
                $weekNumber = ceil($dayOfMonth / 7); // Hitung minggu ke berapa dalam bulan
                
                if (!isset($weeklyData[$weekNumber])) {
                    $weeklyData[$weekNumber] = 0;
                }
                $weeklyData[$weekNumber] += $row['jumlah'];
                $total += $row['jumlah'];
            }
            
            // Format untuk chart - maksimal 5 minggu dalam sebulan
            for ($week = 1; $week <= 5; $week++) {
                $labels[] = "Minggu $week";
                $data[] = $weeklyData[$week] ?? 0;
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
                'total' => $total
            ];
        }
        
        // Untuk filter lainnya
        foreach ($results as $row) {
            $total += $row['jumlah'];
            
            switch ($type) {
                case 'monthly':
                    $monthNames = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                    $labels[] = $monthNames[$row['bulan']];
                    break;
                case 'yearly':
                    $labels[] = $row['tahun'];
                    break;
            }
            
            $data[] = (int)$row['jumlah'];
        }
        
        return [
            'labels' => array_reverse($labels),
            'data' => array_reverse($data),
            'total' => $total
        ];
    }

    // === REKAP DATA UNTUK TABEL ===
    public function getRekapTabel($bulan = null, $tahun = null) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($bulan && $tahun) {
                $whereClause = " AND MONTH(COALESCE(kb.tanggal_berita, km.tanggal_post)) = :bulan AND YEAR(COALESCE(kb.tanggal_berita, km.tanggal_post)) = :tahun";
                $params[':bulan'] = $bulan;
                $params[':tahun'] = $tahun;
            }
            
            $sql = "
                SELECT 
                    k.jenis,
                    kb.jenis_berita,
                    COUNT(DISTINCT k.id_konten) as jumlah
                FROM konten k
                LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
                LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
                WHERE COALESCE(kb.tanggal_berita, km.tanggal_post) IS NOT NULL
                $whereClause
                GROUP BY k.jenis, kb.jenis_berita
                ORDER BY k.jenis, kb.jenis_berita
            ";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
    $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format data untuk tabel
            $tabelData = [
                'media_online' => 0,
                'surat_kabar' => 0,
                'website_kanwil' => 0,
                'instagram' => 0,
                'twitter' => 0,
                'youtube' => 0,
                'facebook' => 0,
                'tiktok' => 0
            ];
            
            foreach ($results as $row) {
                if ($row['jenis'] === 'berita' && $row['jenis_berita']) {
                    // Untuk berita, gunakan jenis_berita
                    $tabelData[$row['jenis_berita']] = (int)$row['jumlah'];
                } else {
                    // Untuk media sosial, gunakan jenis dari tabel konten
                    if (isset($tabelData[$row['jenis']])) {
                        $tabelData[$row['jenis']] = (int)$row['jumlah'];
                    }
                }
            }
            
            return $tabelData;
        } catch (PDOException $e) {
            error_log("[ERROR] Get Rekap Tabel: " . $e->getMessage());
            return [];
        }
    }

    // === GET AVAILABLE MONTHS AND YEARS ===
    public function getAvailablePeriods() {
        try {
            $sql = "
                SELECT DISTINCT 
                    MONTH(COALESCE(kb.tanggal_berita, km.tanggal_post)) as bulan,
                    YEAR(COALESCE(kb.tanggal_berita, km.tanggal_post)) as tahun
                FROM konten k
                LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
                LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
                WHERE COALESCE(kb.tanggal_berita, km.tanggal_post) IS NOT NULL
                ORDER BY tahun DESC, bulan DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $months = [];
            $years = [];
            
            foreach ($results as $row) {
                if (!in_array($row['bulan'], $months)) {
                    $months[] = (int)$row['bulan'];
                }
                if (!in_array($row['tahun'], $years)) {
                    $years[] = (int)$row['tahun'];
                }
            }
            
            // Sort months and years
            sort($months);
            rsort($years);
            
            return [
                'months' => $months,
                'years' => $years,
                'periods' => $results
            ];
        } catch (PDOException $e) {
            error_log("[ERROR] Get Available Periods: " . $e->getMessage());
            return [
                'months' => [],
                'years' => [],
                'periods' => []
            ];
        }
    }

    // === GALLERY PHOTOS ===
    public function getGalleryPhotos($limit = 15) {
        try {
            // First, get all unique photos (no duplicates)
            $stmt = $this->db->prepare("
                SELECT 
                    k.id_konten,
                    k.judul,
                    k.dokumentasi,
                    k.jenis
                FROM konten k
                JOIN konten_berita kb ON k.id_konten = kb.id_konten
                WHERE k.dokumentasi IS NOT NULL 
                AND k.dokumentasi != '' 
                AND k.dokumentasi != 'user.jpg'
                AND k.jenis = 'berita'
                ORDER BY k.id_konten DESC
            ");
            $stmt->execute();
            
            $allPhotos = [];
            $uniqueFiles = []; // Track unique file names
            $photos = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $dokumentasi = $row['dokumentasi'];
                
                // Check if it's an image file
                $isImage = (
                    strpos($dokumentasi, '.jpg') !== false ||
                    strpos($dokumentasi, '.jpeg') !== false ||
                    strpos($dokumentasi, '.png') !== false ||
                    strpos($dokumentasi, '.gif') !== false
                );
                
                if ($isImage) {
                    // Extract filename without path for comparison
                    $filename = basename($dokumentasi);
                    
                    // Check if we already have this filename
                    if (!in_array($filename, $uniqueFiles)) {
                        $uniqueFiles[] = $filename;
                        $photos[] = [
                            'id' => $row['id_konten'],
                            'title' => $row['judul'],
                            'image' => $dokumentasi,
                            'type' => $row['jenis'],
                            'date' => date('Y-m-d'),
                            'category' => ucfirst($row['jenis'])
                        ];
                        
                        // Stop if we have enough unique photos
                        if (count($photos) >= $limit) {
                            break;
                        }
                    }
                }
            }
            
            return $photos;
        } catch (PDOException $e) {
            error_log("[ERROR] Get Gallery Photos: " . $e->getMessage());
            return [];
        }
    }

    // === GET LATEST NEWS FOR PORTAL ===
    public function getLatestNews($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    k.id_konten,
                    k.judul,
                    k.jenis,
                    k.dokumentasi,
                    k.tanggal_input,
                    kb.tanggal_berita,
                    kb.link_berita,
                    kb.sumber_berita,
                    kb.jenis_berita,
                    kb.ringkasan
                FROM konten k
                JOIN konten_berita kb ON k.id_konten = kb.id_konten
                WHERE k.jenis = 'berita' 
                AND kb.jenis_berita = 'website_kanwil'
                ORDER BY k.id_konten DESC
                LIMIT :limit
            ");
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

            $news = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $news[] = [
                    'id' => $row['id_konten'],
                    'judul' => $row['judul'],
                    'isi' => $row['ringkasan'], // Gunakan ringkasan sebagai isi
                    'jenis' => $row['jenis'],
                    'dokumentasi' => $row['dokumentasi'],
                    'tanggal' => $row['tanggal_berita'] ?: $row['tanggal_input'],
                    'link' => $row['link_berita'],
                    'sumber' => $row['sumber_berita'],
                    'ringkasan' => $row['ringkasan'],
                    'jenis_berita' => $row['jenis_berita']
                ];
            }
            
            return $news;
        } catch (PDOException $e) {
            error_log("[ERROR] Get Latest News: " . $e->getMessage());
            return [];
        }
}

}
