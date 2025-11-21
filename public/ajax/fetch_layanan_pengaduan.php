<?php
// Set header untuk JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // Ambil parameter dari AJAX
    $page        = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $search      = isset($_GET['search']) ? trim($_GET['search']) : '';
    $startDate   = isset($_GET['startDate']) ? $_GET['startDate'] : '';
    $endDate     = isset($_GET['endDate']) ? $_GET['endDate'] : '';

    // Pagination setup
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    // Base query
    $query = "
    SELECT id, no_register_pengaduan, nama, alamat, jenis_tanda_pengenal, jenis_tanda_pengenal_lainnya, no_tanda_pengenal, 
           no_telp, judul_laporan, isi_laporan, tanggal_kejadian, lokasi_kejadian, 
           kategori_laporan, jenis_aduan, jenis_aduan_lainnya, tanggal_pengaduan
    FROM layanan_pengaduan
    WHERE 1=1
    ";

    // Initialize params array
    $params = [];

    // Filter search - berdasarkan no_register_pengaduan, nama, judul_laporan, isi_laporan
    if ($search !== '') {
        $search = "%{$search}%";
        $query .= " AND (no_register_pengaduan LIKE ? OR nama LIKE ? OR judul_laporan LIKE ? OR isi_laporan LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    // Filter tanggal (berdasarkan tanggal_pengaduan)
    if ($startDate !== '' && $endDate !== '') {
        $query .= " AND DATE(tanggal_pengaduan) BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
    }

    // Hitung total data
    $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as sub";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->execute($params);
    $totalData = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalData / $limit);

    // Tambahkan limit dan order
    $query .= " ORDER BY tanggal_pengaduan DESC LIMIT $limit OFFSET $offset";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON
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
    // Return error JSON
    error_log("[ERROR] Fetch Layanan Pengaduan: " . $e->getMessage());
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

