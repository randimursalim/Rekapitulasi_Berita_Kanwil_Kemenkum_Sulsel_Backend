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

    // Debug: Log parameters
    error_log("Kegiatan Filter Parameters: " . json_encode([
        'page' => $page,
        'search' => $search,
        'startDate' => $startDate,
        'endDate' => $endDate
    ]));

    // Pagination setup
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    // Base query
    $query = "
    SELECT id_kegiatan, nama_kegiatan, tanggal, jam_mulai, jam_selesai, keterangan, status, created_at
    FROM kegiatan
    WHERE 1=1
    ";

    // Initialize params array
    $params = [];

    // Filter search - berdasarkan nama kegiatan dan keterangan
    if ($search !== '') {
        $search = "%{$search}%";
        $query .= " AND (nama_kegiatan LIKE ? OR keterangan LIKE ?)";
        $params[] = $search;
        $params[] = $search;
    }

    // Filter tanggal
    if ($startDate !== '' && $endDate !== '') {
        $query .= " AND DATE(tanggal) BETWEEN ? AND ?";
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
    $query .= " ORDER BY tanggal DESC, jam_mulai ASC LIMIT $limit OFFSET $offset";
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
