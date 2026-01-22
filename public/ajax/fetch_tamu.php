<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // ===== PARAMETER =====
    $page   = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $search = trim($_GET['search'] ?? '');
    $tahun  = $_GET['tahun'] ?? '';
    $bulan  = $_GET['bulan'] ?? '';

    $limit  = 10;
    $offset = ($page - 1) * $limit;

    // ===== BASE QUERY =====
    $baseSql = "
        FROM tb_tamu
        WHERE 1=1
    ";

    $params = [];

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

    // ===== FILTER SEARCH =====
    if ($search !== '') {
        $baseSql .= " AND (nama LIKE ? OR tujuan LIKE ?)";
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
        SELECT id, tgl, jam, nama, telp, email, alamat, tujuan, ttd, foto
        $baseSql
        ORDER BY tgl DESC, jam DESC
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
