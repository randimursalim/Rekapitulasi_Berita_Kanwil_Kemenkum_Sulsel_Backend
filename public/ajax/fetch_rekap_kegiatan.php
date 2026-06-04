<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

try {
    // Auto update status kegiatan yang belum mulai
    $updateBelumDimulai = "UPDATE kegiatan 
                           SET status = 'Belum Dimulai' 
                           WHERE status IN ('Selesai', 'Sedang Berlangsung') 
                           AND CONCAT(tanggal, ' ', jam_mulai) > NOW()";
    $conn->exec($updateBelumDimulai);

    // Auto update status kegiatan yang sudah lewat menjadi 'Selesai'
    $updateSelesai = "UPDATE kegiatan 
                      SET status = 'Selesai' 
                      WHERE status IN ('Belum Dimulai', 'Sedang Berlangsung') 
                      AND CONCAT(tanggal, ' ', jam_selesai) < NOW()";
    $conn->exec($updateSelesai);
    
    // Auto update status kegiatan yang sedang berjalan
    $updateBerlangsung = "UPDATE kegiatan 
                          SET status = 'Sedang Berlangsung' 
                          WHERE status IN ('Belum Dimulai', 'Selesai') 
                          AND CONCAT(tanggal, ' ', jam_mulai) <= NOW() 
                          AND CONCAT(tanggal, ' ', jam_selesai) >= NOW()";
    $conn->exec($updateBerlangsung);

    $bulan = isset($_GET['bulan']) && $_GET['bulan'] !== 'all' ? (int)$_GET['bulan'] : null;
    $tahun = isset($_GET['tahun']) && $_GET['tahun'] !== 'all' ? (int)$_GET['tahun'] : null;
    $pimti = isset($_GET['pimti']) && $_GET['pimti'] !== 'all' ? $_GET['pimti'] : null;
    $status = isset($_GET['status']) && $_GET['status'] !== 'all' ? $_GET['status'] : null;
    $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    $whereConditions = [];
    $params = [];

    if ($bulan) {
        $whereConditions[] = "MONTH(tanggal) = :bulan";
        $params[':bulan'] = $bulan;
    }

    if ($tahun) {
        $whereConditions[] = "YEAR(tanggal) = :tahun";
        $params[':tahun'] = $tahun;
    }

    if ($pimti) {
        if ($pimti === 'kakanwil') {
            $whereConditions[] = "hadir_kakanwil = 1";
        } elseif ($pimti === 'kadiv_p3h') {
            $whereConditions[] = "hadir_kadiv_p3h = 1";
        } elseif ($pimti === 'kadiv_yankum') {
            $whereConditions[] = "hadir_kadiv_yankum = 1";
        }
    }

    if ($status) {
        $whereConditions[] = "status = :status";
        $params[':status'] = $status;
    }

    if ($keyword !== '') {
        $whereConditions[] = "(nama_kegiatan LIKE :keyword OR keterangan LIKE :keyword)";
        $params[':keyword'] = "%{$keyword}%";
    }

    $whereClause = "";
    if (count($whereConditions) > 0) {
        $whereClause = "WHERE " . implode(" AND ", $whereConditions);
    }

    // Query untuk tabel data
    $queryTabel = "SELECT * FROM kegiatan $whereClause ORDER BY tanggal DESC, jam_mulai ASC";
    $stmtTabel = $conn->prepare($queryTabel);
    foreach ($params as $key => &$val) {
        $stmtTabel->bindParam($key, $val);
    }
    $stmtTabel->execute();
    $dataTabel = $stmtTabel->fetchAll(PDO::FETCH_ASSOC);

    // Query untuk chart (dikelompokkan per bulan jika tahun dipilih, atau per pimti jika bulan&tahun dipilih)
    // Default chart: jumlah kegiatan per bulan dalam tahun yang dipilih (atau semua tahun)
    
    $dataChart = [
        'labels' => [],
        'values' => []
    ];

    if ($tahun && !$bulan) {
        // Tampilkan per bulan
        $queryChart = "SELECT MONTH(tanggal) as label, COUNT(*) as total FROM kegiatan $whereClause GROUP BY MONTH(tanggal) ORDER BY MONTH(tanggal) ASC";
        $stmtChart = $conn->prepare($queryChart);
        foreach ($params as $key => &$val) {
            $stmtChart->bindParam($key, $val);
        }
        $stmtChart->execute();
        $chartResult = $stmtChart->fetchAll(PDO::FETCH_ASSOC);
        
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $chartDataAssoc = [];
        foreach ($chartResult as $row) {
            $chartDataAssoc[$row['label']] = $row['total'];
        }

        for ($i = 1; $i <= 12; $i++) {
            $dataChart['labels'][] = $months[$i-1];
            $dataChart['values'][] = isset($chartDataAssoc[$i]) ? $chartDataAssoc[$i] : 0;
        }

    } elseif ($bulan && $tahun) {
        // Tampilkan per minggu/tanggal
        $queryChart = "SELECT DAY(tanggal) as label, COUNT(*) as total FROM kegiatan $whereClause GROUP BY DAY(tanggal) ORDER BY DAY(tanggal) ASC";
        $stmtChart = $conn->prepare($queryChart);
        foreach ($params as $key => &$val) {
            $stmtChart->bindParam($key, $val);
        }
        $stmtChart->execute();
        $chartResult = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

        foreach ($chartResult as $row) {
            $dataChart['labels'][] = "Tgl " . $row['label'];
            $dataChart['values'][] = $row['total'];
        }
    } else {
        // Tampilkan per tahun
        $queryChart = "SELECT YEAR(tanggal) as label, COUNT(*) as total FROM kegiatan $whereClause GROUP BY YEAR(tanggal) ORDER BY YEAR(tanggal) ASC";
        $stmtChart = $conn->prepare($queryChart);
        foreach ($params as $key => &$val) {
            $stmtChart->bindParam($key, $val);
        }
        $stmtChart->execute();
        $chartResult = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

        foreach ($chartResult as $row) {
            $dataChart['labels'][] = $row['label'];
            $dataChart['values'][] = $row['total'];
        }
    }

    // Ambil tahun unik untuk dropdown filter
    $stmtTahun = $conn->query("SELECT DISTINCT YEAR(tanggal) as tahun FROM kegiatan ORDER BY tahun DESC");
    $availableYears = $stmtTahun->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'data' => $dataTabel,
        'chart' => $dataChart,
        'years' => $availableYears
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
