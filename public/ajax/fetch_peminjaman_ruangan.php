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
    SELECT id, nama_peminjam, nama_ruangan, kegiatan, tanggal_kegiatan, waktu_kegiatan, created_at
    FROM jadwal_peminjaman_ruangan
    WHERE 1=1
    ";

    // Initialize params array
    $params = [];

    // Filter search - berdasarkan nama peminjam, nama ruangan, dan kegiatan
    if ($search !== '') {
        $search = "%{$search}%";
        $query .= " AND (nama_peminjam LIKE ? OR nama_ruangan LIKE ? OR kegiatan LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }

    // Filter tanggal
    if ($startDate !== '' && $endDate !== '') {
        $query .= " AND DATE(tanggal_kegiatan) BETWEEN ? AND ?";
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
    $query .= " ORDER BY tanggal_kegiatan DESC, waktu_kegiatan ASC LIMIT $limit OFFSET $offset";
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
?>


