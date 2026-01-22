<?php
// Set header untuk JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // Ambil parameter limit (default 10 untuk preview di landing)
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;

    // Base query - hanya ambil 10 data terbaru
    $query = "
    SELECT id, judul_rancangan, pemrakarsa, pemerintah_daerah, tanggal_surat_diterima, 
           tanggal_rapat, pemegang_draf, status, alasan_pengembalian_draf
    FROM harmonisasi
    ORDER BY tanggal_rapat DESC, id DESC
    LIMIT :limit
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON
    echo json_encode([
        'success' => true,
        'data' => $data,
        'total' => count($data)
    ]);

} catch (Exception $e) {
    // Return error JSON
    error_log("[ERROR] Fetch Harmonisasi Public: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => [],
        'total' => 0
    ]);
}

