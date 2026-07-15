<?php
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // ===== PARAMETER =====
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $search = trim($_GET['search'] ?? '');
    $startDate = $_GET['startDate'] ?? '';
    $endDate = $_GET['endDate'] ?? '';
    $layanan = $_GET['layanan'] ?? '';
    $layanan_item = $_GET['layanan_item'] ?? '';

    // Fallback untuk parameter tahun & bulan lama
    if (empty($startDate) && !empty($_GET['tahun'])) {
        $tahun = $_GET['tahun'];
        $bulan = $_GET['bulan'] ?? 'all';
        if ($bulan === 'all' || $bulan === '') {
            $startDate = "$tahun-01-01";
            $endDate = "$tahun-12-31";
        } else {
            $startDate = "$tahun-" . str_pad($bulan, 2, '0', STR_PAD_LEFT) . "-01";
            $endDate = date('Y-m-t', strtotime($startDate));
        }
    }

    $limit = 10;
    $offset = ($page - 1) * $limit;

    // ===== BASE QUERY =====
    $baseSql = "
        FROM tb_tamu
        WHERE 1=1
    ";

    $params = [];

    // ===== FILTER TANGGAL (RANGE) =====
    if ($startDate !== '') {
        $baseSql .= " AND tgl >= ?";
        $params[] = $startDate;
    }

    if ($endDate !== '' && $endDate !== 'all') {
        $baseSql .= " AND tgl <= ?";
        $params[] = $endDate;
    }

    // ===== FILTER LAYANAN =====
    if ($layanan !== '') {
        $baseSql .= " AND layanan = ?";
        $params[] = $layanan;
    }

    // ===== FILTER LAYANAN ITEM =====
    if ($layanan_item !== '') {
        $baseSql .= " AND layanan_item = ?";
        $params[] = $layanan_item;
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
        SELECT id, tgl, jam, nama, telp, email, alamat, tujuan, layanan, layanan_item, entrain, ttd, foto
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
