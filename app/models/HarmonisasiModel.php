<?php
require_once __DIR__ . '/../../config/database.php';

class HarmonisasiModel {
    private $db;

    public function __construct() {
        global $conn;
        $this->db = $conn;
    }

    // Ambil semua data harmonisasi dengan pagination dan filter
    public function getAllHarmonisasi($page = 1, $limit = 10, $search = '', $startDate = '', $endDate = '', $status = '') {
        $offset = ($page - 1) * $limit;
        
        // Base query
        $query = "
        SELECT id, judul_rancangan, pemrakarsa, pemerintah_daerah, tanggal_rapat, 
               pemegang_draf, status, alasan_pengembalian_draf
        FROM harmonisasi
        WHERE 1=1
        ";
        
        $params = [];
        
        // Filter search
        if ($search !== '') {
            $searchParam = "%{$search}%";
            $query .= " AND (judul_rancangan LIKE ? OR pemrakarsa LIKE ? OR pemerintah_daerah LIKE ? OR pemegang_draf LIKE ?)";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        // Filter tanggal (berdasarkan tanggal_rapat)
        if ($startDate !== '' && $endDate !== '') {
            $query .= " AND DATE(tanggal_rapat) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
        }
        
        // Filter status
        if ($status !== '') {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        
        // Hitung total data
        $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as sub";
        $stmtCount = $this->db->prepare($countQuery);
        $stmtCount->execute($params);
        $totalData = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Tambahkan limit dan order
        $query .= " ORDER BY tanggal_rapat DESC LIMIT $limit OFFSET $offset";
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

    // Ambil data harmonisasi berdasarkan ID
    public function getHarmonisasiById($id) {
        $query = "SELECT * FROM harmonisasi WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Tambah data harmonisasi baru
    public function tambahHarmonisasi($data) {
        try {
            $query = "INSERT INTO harmonisasi (judul_rancangan, pemrakarsa, pemerintah_daerah, tanggal_rapat, pemegang_draf, status, alasan_pengembalian_draf) 
                      VALUES (:judul_rancangan, :pemrakarsa, :pemerintah_daerah, :tanggal_rapat, :pemegang_draf, :status, :alasan_pengembalian_draf)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':judul_rancangan', $data['judul_rancangan']);
            $stmt->bindParam(':pemrakarsa', $data['pemrakarsa']);
            $stmt->bindParam(':pemerintah_daerah', $data['pemerintah_daerah']);
            $stmt->bindParam(':tanggal_rapat', $data['tanggal_rapat']);
            $stmt->bindParam(':pemegang_draf', $data['pemegang_draf']);
            $stmt->bindParam(':status', $data['status']);
            
            // Handle alasan_pengembalian_draf (nullable)
            $alasan = !empty($data['alasan_pengembalian_draf']) ? $data['alasan_pengembalian_draf'] : null;
            if ($alasan !== null) {
                $stmt->bindParam(':alasan_pengembalian_draf', $alasan);
            } else {
                $stmt->bindValue(':alasan_pengembalian_draf', null, PDO::PARAM_NULL);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[ERROR] Tambah Harmonisasi Model PDO: " . $e->getMessage());
            return false;
        }
    }

    // Update data harmonisasi
    public function updateHarmonisasi($id, $data) {
        try {
            $query = "UPDATE harmonisasi SET 
                      judul_rancangan = :judul_rancangan,
                      pemrakarsa = :pemrakarsa,
                      pemerintah_daerah = :pemerintah_daerah,
                      tanggal_rapat = :tanggal_rapat,
                      pemegang_draf = :pemegang_draf,
                      status = :status,
                      alasan_pengembalian_draf = :alasan_pengembalian_draf
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':judul_rancangan', $data['judul_rancangan']);
            $stmt->bindParam(':pemrakarsa', $data['pemrakarsa']);
            $stmt->bindParam(':pemerintah_daerah', $data['pemerintah_daerah']);
            $stmt->bindParam(':tanggal_rapat', $data['tanggal_rapat']);
            $stmt->bindParam(':pemegang_draf', $data['pemegang_draf']);
            $stmt->bindParam(':status', $data['status']);
            
            // Handle alasan_pengembalian_draf (nullable)
            $alasan = !empty($data['alasan_pengembalian_draf']) ? $data['alasan_pengembalian_draf'] : null;
            if ($alasan !== null) {
                $stmt->bindParam(':alasan_pengembalian_draf', $alasan);
            } else {
                $stmt->bindValue(':alasan_pengembalian_draf', null, PDO::PARAM_NULL);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("[ERROR] Update Harmonisasi Model PDO: " . $e->getMessage());
            return false;
        }
    }

    // Hapus data harmonisasi
    public function hapusHarmonisasi($id) {
        $query = "DELETE FROM harmonisasi WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // === REKAP DATA UNTUK GRAFIK ===
    public function getRekapData($filter = 'monthly', $startDate = null, $endDate = null, $status = 'all') {
        try {
            $data = [];
            
            switch ($filter) {
                case 'daily':
                    $data = $this->getRekapHarian($startDate, $endDate, $status);
                    break;
                case 'weekly':
                    $data = $this->getRekapMingguan($startDate, $endDate, $status);
                    break;
                case 'monthly':
                    $data = $this->getRekapBulanan($startDate, $endDate, $status);
                    break;
                case 'yearly':
                    $data = $this->getRekapTahunan($startDate, $endDate, $status);
                    break;
                case 'range':
                    $data = $this->getRekapRange($startDate, $endDate, $status);
                    break;
                default:
                    $data = $this->getRekapBulanan($startDate, $endDate, $status);
            }
            
            return $data;
        } catch (PDOException $e) {
            error_log("[ERROR] Get Rekap Data Harmonisasi: " . $e->getMessage());
            return ['labels' => [], 'data' => [], 'total' => 0];
        }
    }

    // === REKAP RANGE TANGGAL ===
    private function getRekapRange($startDate, $endDate, $status) {
        $whereClause = $this->buildWhereClause($startDate, $endDate, $status);
        
        $sql = "
            SELECT 
                COUNT(*) as jumlah
            FROM harmonisasi
            WHERE tanggal_rapat IS NOT NULL
            $whereClause
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $status);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['jumlah'] ?? 0;
        
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
    private function getRekapHarian($startDate, $endDate, $status) {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-6 days'));
        
        $whereClause = $this->buildWhereClause($startDate, $endDate, $status);
        
        $sql = "
            SELECT 
                DATE(tanggal_rapat) as tanggal,
                COUNT(*) as jumlah
            FROM harmonisasi
            WHERE tanggal_rapat IS NOT NULL
            $whereClause
            GROUP BY DATE(tanggal_rapat)
            ORDER BY tanggal DESC
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $status);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'daily');
    }

    // === REKAP MINGGUAN ===
    private function getRekapMingguan($startDate, $endDate, $status) {
        // Filter mingguan: data mingguan bulan sekarang
        $currentMonth = date('Y-m');
        $startDate = $currentMonth . '-01'; // Tanggal 1 bulan ini
        $endDate = date('Y-m-t'); // Tanggal terakhir bulan ini
        
        $whereClause = $this->buildWhereClause($startDate, $endDate, $status);
        
        $sql = "
            SELECT 
                tanggal_rapat as tanggal,
                COUNT(*) as jumlah
            FROM harmonisasi
            WHERE tanggal_rapat IS NOT NULL
            AND tanggal_rapat >= :startDate
            AND tanggal_rapat <= :endDate
            $whereClause
            GROUP BY DATE(tanggal_rapat)
            ORDER BY tanggal ASC
        ";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':startDate', $startDate);
        $stmt->bindValue(':endDate', $endDate);
        $this->bindWhereParams($stmt, $startDate, $endDate, $status);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'weekly');
    }

    // === REKAP BULANAN ===
    private function getRekapBulanan($startDate, $endDate, $status) {
        $whereClause = $this->buildWhereClause($startDate, $endDate, $status);
        
        $sql = "
            SELECT 
                MONTH(tanggal_rapat) as bulan,
                YEAR(tanggal_rapat) as tahun,
                COUNT(*) as jumlah
            FROM harmonisasi
            WHERE tanggal_rapat IS NOT NULL
            $whereClause
            GROUP BY YEAR(tanggal_rapat), MONTH(tanggal_rapat)
            ORDER BY tahun DESC, bulan DESC
            LIMIT 12
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $status);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'monthly');
    }

    // === REKAP TAHUNAN ===
    private function getRekapTahunan($startDate, $endDate, $status) {
        $whereClause = $this->buildWhereClause($startDate, $endDate, $status);
        
        $sql = "
            SELECT 
                YEAR(tanggal_rapat) as tahun,
                COUNT(*) as jumlah
            FROM harmonisasi
            WHERE tanggal_rapat IS NOT NULL
            $whereClause
            GROUP BY YEAR(tanggal_rapat)
            ORDER BY tahun DESC
            LIMIT 10
        ";
        
        $stmt = $this->db->prepare($sql);
        $this->bindWhereParams($stmt, $startDate, $endDate, $status);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->formatRekapData($results, 'yearly');
    }

    // === BUILD WHERE CLAUSE ===
    private function buildWhereClause($startDate, $endDate, $status) {
        $conditions = [];
        
        if ($startDate && $endDate) {
            $conditions[] = "DATE(tanggal_rapat) BETWEEN :start_date AND :end_date";
        }
        
        if ($status !== 'all') {
            $conditions[] = "status = :status";
        }
        
        return empty($conditions) ? '' : ' AND ' . implode(' AND ', $conditions);
    }

    // === BIND WHERE PARAMS ===
    private function bindWhereParams($stmt, $startDate, $endDate, $status) {
        if ($startDate && $endDate) {
            $stmt->bindParam(':start_date', $startDate);
            $stmt->bindParam(':end_date', $endDate);
        }
        
        if ($status !== 'all') {
            $stmt->bindParam(':status', $status);
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

    // === GET MONTH NAME ===
    private function getMonthName($month) {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $months[(int)$month] ?? '';
    }

    // === REKAP TABEL ===
    public function getRekapTabel($bulan = null, $tahun = null) {
        try {
            $whereClause = '';
            $params = [];
            
            if ($bulan && $tahun) {
                $whereClause = " AND MONTH(tanggal_rapat) = :bulan AND YEAR(tanggal_rapat) = :tahun";
                $params[':bulan'] = $bulan;
                $params[':tahun'] = $tahun;
            }
            
            $sql = "
                SELECT 
                    MONTH(tanggal_rapat) as bulan,
                    YEAR(tanggal_rapat) as tahun,
                    SUM(CASE WHEN status = 'Diterima' THEN 1 ELSE 0 END) as diterima,
                    SUM(CASE WHEN status = 'Dikembalikan' THEN 1 ELSE 0 END) as dikembalikan,
                    COUNT(*) as total
                FROM harmonisasi
                WHERE tanggal_rapat IS NOT NULL
                $whereClause
                GROUP BY YEAR(tanggal_rapat), MONTH(tanggal_rapat)
                ORDER BY tahun DESC, bulan DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($results)) {
                return [
                    'bulan' => $bulan ? $this->getMonthName($bulan) : '-',
                    'tahun' => $tahun ?? '-',
                    'diterima' => 0,
                    'dikembalikan' => 0,
                    'total' => 0
                ];
            }
            
            $result = $results[0];
            return [
                'bulan' => $this->getMonthName($result['bulan']),
                'tahun' => $result['tahun'],
                'diterima' => (int)$result['diterima'],
                'dikembalikan' => (int)$result['dikembalikan'],
                'total' => (int)$result['total']
            ];
        } catch (PDOException $e) {
            error_log("[ERROR] Get Rekap Tabel Harmonisasi: " . $e->getMessage());
            return [
                'bulan' => '-',
                'tahun' => '-',
                'diterima' => 0,
                'dikembalikan' => 0,
                'total' => 0
            ];
        }
    }

    // === GET AVAILABLE PERIODS ===
    public function getAvailablePeriods() {
        try {
            $sql = "
                SELECT DISTINCT
                    YEAR(tanggal_rapat) as tahun,
                    MONTH(tanggal_rapat) as bulan
                FROM harmonisasi
                WHERE tanggal_rapat IS NOT NULL
                ORDER BY tahun DESC, bulan DESC
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("[ERROR] Get Available Periods Harmonisasi: " . $e->getMessage());
            return [];
        }
    }
}

