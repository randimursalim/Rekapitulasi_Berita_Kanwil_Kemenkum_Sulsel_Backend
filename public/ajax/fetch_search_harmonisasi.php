<?php
// Set header untuk JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // Ambil parameter dari AJAX
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : ''; // Comma-separated keywords
    $filterStatus = isset($_GET['filterStatus']) ? $_GET['filterStatus'] : '';
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

    // Pagination setup - support limit parameter untuk download all data
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Base query
    $query = "
    SELECT id, judul_rancangan, pemrakarsa, pemerintah_daerah, tanggal_rapat, 
           pemegang_draf, status, alasan_pengembalian_draf
    FROM harmonisasi
    WHERE 1=1
    ";

    // Initialize params array
    $params = [];

    // Filter keywords - multiple keywords dengan OR logic, hanya di judul_rancangan
    if ($keywords !== '') {
        $keywordArray = array_filter(array_map('trim', explode(',', $keywords)));
        if (!empty($keywordArray)) {
            $keywordConditions = [];
            foreach ($keywordArray as $keyword) {
                $keywordConditions[] = "judul_rancangan LIKE ?";
                $params[] = "%{$keyword}%";
            }
            if (!empty($keywordConditions)) {
                $query .= " AND (" . implode(" OR ", $keywordConditions) . ")";
            }
        }
    }

    // Filter status
    if ($filterStatus !== '' && $filterStatus !== 'all') {
        $query .= " AND status = ?";
        $params[] = $filterStatus;
    }

    // Filter tanggal
    if ($startDate !== '' && $endDate !== '') {
        $query .= " AND DATE(tanggal_rapat) BETWEEN ? AND ?";
        $params[] = $startDate;
        $params[] = $endDate;
    }

    // Hitung total data
    $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as sub";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->execute($params);
    $totalData = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalData / $limit);

    // Tambahkan limit dan order (jika limit > 1000, berarti download all data, skip pagination)
    if ($limit > 1000) {
        // Untuk download all data, tidak perlu pagination
        $query .= " ORDER BY tanggal_rapat DESC";
    } else {
        $query .= " ORDER BY tanggal_rapat DESC LIMIT $limit OFFSET $offset";
    }
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
    error_log("[ERROR] Fetch Search Harmonisasi: " . $e->getMessage());
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

