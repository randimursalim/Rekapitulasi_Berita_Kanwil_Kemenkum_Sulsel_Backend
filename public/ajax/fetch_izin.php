<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // ===== PARAMETER =====
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $search = trim($_GET['search'] ?? '');
    $tahun = $_GET['tahun'] ?? '';
    $bulan = $_GET['bulan'] ?? '';
    $status = $_GET['status'] ?? '';
    $tab = $_GET['tab'] ?? 'masuk';

    $limit = 10;
    $offset = ($page - 1) * $limit;

    // ===== BASE QUERY =====
    $baseSql = "
        FROM tb_izin
        WHERE 1=1
    ";

    $params = [];

    if ($tab === 'dashboard') {
        $filterMasuk = '';
        $filterKeluar = '';
        $paramsMasuk = [];
        $paramsKeluar = [];

        if ($tahun !== '') {
            $filterMasuk .= " AND YEAR(tgl) = ?";
            $paramsMasuk[] = $tahun;
            $filterKeluar .= " AND YEAR(COALESCE(tgl_balasan, tgl)) = ?";
            $paramsKeluar[] = $tahun;
        }

        if ($bulan !== '' && $bulan !== 'all') {
            $filterMasuk .= " AND MONTH(tgl) = ?";
            $paramsMasuk[] = $bulan;
            $filterKeluar .= " AND MONTH(COALESCE(tgl_balasan, tgl)) = ?";
            $paramsKeluar[] = $bulan;
        }

        $masukCounts = array_fill(1, 12, 0);
        $keluarCounts = array_fill(1, 12, 0);

        $stmtMasuk = $conn->prepare("SELECT MONTH(tgl) AS month, COUNT(*) AS total FROM tb_izin WHERE status IN (1,3,5) $filterMasuk GROUP BY MONTH(tgl)");
        $stmtMasuk->execute($paramsMasuk);
        while ($row = $stmtMasuk->fetch(PDO::FETCH_ASSOC)) {
            $month = (int) $row['month'];
            if ($month >= 1 && $month <= 12) {
                $masukCounts[$month] = (int) $row['total'];
            }
        }

        $stmtKeluar = $conn->prepare("SELECT MONTH(COALESCE(tgl_balasan, tgl)) AS month, COUNT(*) AS total FROM tb_izin WHERE status IN (2,4,6) $filterKeluar GROUP BY MONTH(COALESCE(tgl_balasan, tgl))");
        $stmtKeluar->execute($paramsKeluar);
        while ($row = $stmtKeluar->fetch(PDO::FETCH_ASSOC)) {
            $month = (int) $row['month'];
            if ($month >= 1 && $month <= 12) {
                $keluarCounts[$month] = (int) $row['total'];
            }
        }

        $labels = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Agu',
            'Sep',
            'Okt',
            'Nov',
            'Des'
        ];

        // ===== SUMMARY COUNTS =====

        // Surat masuk (1,3,5)
        $stmtTotalMasuk = $conn->query("SELECT COUNT(*) FROM tb_izin WHERE status IN (1,3,5)");
        $totalMasuk = (int) $stmtTotalMasuk->fetchColumn();

        // Surat balasan (2,4,6)
        $stmtTotalKeluar = $conn->query("SELECT COUNT(*) FROM tb_izin WHERE status IN (2,4,6)");
        $totalKeluar = (int) $stmtTotalKeluar->fetchColumn();

        // Total user unik (berdasarkan NIK)
        $stmtUser = $conn->query("SELECT COUNT(DISTINCT nik) FROM tb_izin WHERE nik IS NOT NULL AND nik != ''");
        $totalUser = (int) $stmtUser->fetchColumn();


        // ===== WA STATUS =====
        $stmtWa = $conn->query("
            SELECT 
                SUM(CASE 
                        WHEN (wa_status = 'sent')
                        AND status IN (2,4,6) 
                        THEN 1 
                        ELSE 0 
                    END
                ) as sent,
                SUM(CASE 
                        WHEN (wa_status = 'pending' OR wa_status IS NULL) 
                        AND status IN (2,4,6)
                        THEN 1 
                        ELSE 0 
                    END
                ) as pending,
                SUM(CASE 
                        WHEN (wa_status = 'failed')
                        AND status IN (2,4,6) 
                        THEN 1 
                        ELSE 0 
                    END
                ) as failed
            FROM tb_izin
        ");
        $wa = $stmtWa->fetch(PDO::FETCH_ASSOC);


        // ===== STATUS 1-6 =====
        $stmtStatus = $conn->query("
            SELECT status, COUNT(*) as total 
            FROM tb_izin 
            GROUP BY status
        ");

        $statusCounts = array_fill(1, 6, 0);
        while ($row = $stmtStatus->fetch(PDO::FETCH_ASSOC)) {
            $statusCounts[(int) $row['status']] = (int) $row['total'];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'labels' => $labels,
                'masuk' => array_values($masukCounts),
                'keluar' => array_values($keluarCounts),

                // tambahan
                'summary' => [
                    'masuk' => $totalMasuk,
                    'keluar' => $totalKeluar,
                    'user' => $totalUser
                ],
                'wa' => [
                    'sent' => (int) $wa['sent'],
                    'pending' => (int) $wa['pending'],
                    'failed' => (int) $wa['failed']
                ],
                'status' => $statusCounts
            ]
        ]);

        // echo json_encode([
        //     'success' => true,
        //     'data' => [
        //         'labels' => $labels,
        //         'masuk' => array_values($masukCounts),
        //         'keluar' => array_values($keluarCounts)
        //     ]
        // ]);
        return;
    }

    // ===== LOGIKA TAB (PEMBAGIAN TABEL) =====
    if ($tab === 'balasan') {
        $baseSql .= " AND status IN (2,4,6)";
    } else {
        $baseSql .= " AND status NOT IN (2,4,6)";
    }

    // ===== FILTER TAHUN =====
    if ($tahun !== '') {
        $baseSql .= " AND YEAR(tgl) = ?";
        $params[] = $tahun;
    }

    // ===== FILTER BULAN =====
    if ($bulan !== '' && $bulan !== 'all') {
        $baseSql .= " AND MONTH(tgl) = ?";
        $params[] = $bulan;
    }

    // ===== FILTER STATUS =====
    if ($status !== '') {
        $baseSql .= " AND status = ?";
        $params[] = $status;
    }

    // ===== FILTER SEARCH =====
    if ($search !== '') {
        $baseSql .= " AND (id LIKE ? OR nama LIKE ? OR nik LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    // ===== HITUNG TOTAL DATA =====
    $stmtCount = $conn->prepare("SELECT COUNT(*) $baseSql");
    $stmtCount->execute($params);
    $totalData = (int) $stmtCount->fetchColumn();
    $totalPages = ceil($totalData / $limit);

    // ===== AMBIL DATA =====
    $stmt = $conn->prepare("
        SELECT id, nik, nama, tlp, jenis_surat, tgl, file, keterangan, status, file_balasan, tgl_balasan, wa_terkirim, wa_status, wa_response, wa_sent_at
        $baseSql
        ORDER BY tgl DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ===== RESPONSE =====
    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalData' => $totalData
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => [],
        'pagination' => [
            'currentPage' => 1,
            'totalPages' => 0,
            'totalData' => 0
        ]
    ]);
}