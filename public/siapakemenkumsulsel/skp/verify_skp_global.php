<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database connection
require_once '../config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get ID_SKP_GLOBAL from POST data
$id_skp_global_raw = $_POST['id_skp_global'] ?? '';

// Cast to integer for proper comparison
$id_skp_global = (int)$id_skp_global_raw;

if (empty($id_skp_global_raw) || $id_skp_global <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID SKP Global tidak valid']);
    exit();
}

try {
    // Search for ID_SKP_GLOBAL in skp_akhir_pegawai table
    $search_sql = "SELECT COUNT(*) as count, MIN(STATUS) as status FROM skp_akhir_pegawai WHERE CAST(ID_SKP_GLOBAL AS UNSIGNED) = ?";
    $search_stmt = $conn->prepare($search_sql);
    $search_stmt->bind_param('i', $id_skp_global);
    $search_stmt->execute();
    $search_result = $search_stmt->get_result();
    $search_row = $search_result->fetch_assoc();
    $search_stmt->close();
    
    if ($search_row['count'] === 0) {
        echo json_encode(['success' => false, 'message' => 'SKP Akhir tidak ditemukan dalam database']);
    } else {
        $status = $search_row['status'];
        if (strtoupper($status) === 'DRAFT') {
            echo json_encode(['success' => true, 'message' => 'SKP Akhir ditemukan dan siap untuk disubmit', 'status' => $status]);
        } else {
            echo json_encode(['success' => false, 'message' => 'SKP Akhir sudah dalam status: ' . $status]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
