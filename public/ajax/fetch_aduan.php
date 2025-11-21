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
    SELECT id_aduan, no_register, tanggal, aduan, jenis_aduan, media_digunakan, tindak_lanjut, keterangan, created_at, updated_at
    FROM aduan
    WHERE 1=1
    ";

    // Initialize params array
    $params = [];

    // Filter search - berdasarkan no_register, aduan, jenis_aduan, media_digunakan
    if ($search !== '') {
        $search = "%{$search}%";
        $query .= " AND (no_register LIKE ? OR aduan LIKE ? OR jenis_aduan LIKE ? OR media_digunakan LIKE ?)";
        $params[] = $search;
        $params[] = $search;
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
    $query .= " ORDER BY tanggal DESC, created_at DESC LIMIT $limit OFFSET $offset";
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
    error_log("[ERROR] Fetch Aduan: " . $e->getMessage());
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

