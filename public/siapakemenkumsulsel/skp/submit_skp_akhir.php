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

// Get ID_SKP_GLOBAL and year from POST data
$id_skp_global_raw = $_POST['id_skp'] ?? '';
$year_raw = $_POST['year'] ?? '';

// Debug logging
error_log("🔍 Submit SKP Akhir Debug:");
error_log("  ID_SKP_GLOBAL received (raw): " . $id_skp_global_raw);
error_log("  Year received (raw): " . $year_raw);
error_log("  POST data: " . print_r($_POST, true));

// Cast to integers for proper comparison
$id_skp_global = (int)$id_skp_global_raw;
$year = (int)$year_raw;

error_log("  ID_SKP_GLOBAL (cast to int): " . $id_skp_global);
error_log("  Year (cast to int): " . $year);

if (empty($id_skp_global_raw) || $id_skp_global <= 0) {
    error_log("❌ ID_SKP_GLOBAL is empty or invalid");
    echo json_encode(['success' => false, 'message' => 'ID SKP Global tidak valid']);
    exit();
}

if (empty($year_raw) || $year <= 0) {
    error_log("❌ Year is empty or invalid");
    echo json_encode(['success' => false, 'message' => 'Tahun tidak valid']);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Verify that the ID_SKP_GLOBAL exists for the specified year
    $verify_sql = "SELECT COUNT(*) as count FROM skp_akhir_pegawai WHERE CAST(ID_SKP_GLOBAL AS UNSIGNED) = ? AND TAHUN = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param('ii', $id_skp_global, $year);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $count = $verify_result->fetch_assoc()['count'];
    $verify_stmt->close();
    
    if ($count === 0) {
        throw new Exception("SKP Akhir tidak ditemukan.");
    }
    
    // Debug: Check current status before update
    $debug_sql = "SELECT ID_SKP, STATUS FROM skp_akhir_pegawai WHERE CAST(ID_SKP_GLOBAL AS UNSIGNED) = ? AND TAHUN = ?";
    $debug_stmt = $conn->prepare($debug_sql);
    $debug_stmt->bind_param('ii', $id_skp_global, $year);
    $debug_stmt->execute();
    $debug_result = $debug_stmt->get_result();
    error_log("🔍 Debug - Records with ID_SKP_GLOBAL = $id_skp_global:");
    while ($debug_row = $debug_result->fetch_assoc()) {
        error_log("  ID_SKP: " . $debug_row['ID_SKP'] . ", STATUS: '" . $debug_row['STATUS'] . "'");
    }
    $debug_stmt->close();
    
    // Update ALL skp_akhir_pegawai records with the same ID_SKP_GLOBAL and year to PROSES EVALUASI (only for DRAFT status)
    $update_sql = "UPDATE skp_akhir_pegawai SET STATUS = 'PROSES EVALUASI' WHERE CAST(ID_SKP_GLOBAL AS UNSIGNED) = ? AND TAHUN = ? AND UPPER(STATUS) = 'DRAFT'";
    error_log("🔍 Update SQL: " . $update_sql);
    error_log("🔍 Update Parameters: ID_SKP_GLOBAL=" . $id_skp_global . ", TAHUN=" . $year);
    
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ii', $id_skp_global, $year);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Error updating skp_akhir_pegawai: " . $update_stmt->error);
    }
    
    $affected_rows = $update_stmt->affected_rows;
    $update_stmt->close();
    
    error_log("🔍 Update result: $affected_rows rows affected for ID_SKP_GLOBAL = $id_skp_global");
    error_log("🔍 Update result: $affected_rows rows affected for TAHUN = $year");
    
    if ($affected_rows === 0) {
        throw new Exception("SKP Akhir tidak dapat disubmit. Status mungkin sudah berubah atau tidak ditemukan.");
    }
    
    // Update ALL skp_akhir_perilaku_pegawai records with the same ID_SKP_GLOBAL and year to PROSES EVALUASI
    $perilaku_update_sql = "UPDATE skp_akhir_perilaku_pegawai SET STATUS = 'PROSES EVALUASI' WHERE CAST(ID_SKP_GLOBAL AS UNSIGNED) = ? AND TAHUN = ? AND UPPER(STATUS) = 'DRAFT'";
    $perilaku_update_stmt = $conn->prepare($perilaku_update_sql);
    $perilaku_update_stmt->bind_param('ii', $id_skp_global, $year);
    
    if (!$perilaku_update_stmt->execute()) {
        throw new Exception("Error updating skp_akhir_perilaku_pegawai: " . $perilaku_update_stmt->error);
    }
    
    $perilaku_affected_rows = $perilaku_update_stmt->affected_rows;
    $perilaku_update_stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'SKP Akhir berhasil disubmit ke atasan untuk evaluasi. Total ' . $affected_rows . ' SKP records dan ' . $perilaku_affected_rows . ' perilaku records diupdate.']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
