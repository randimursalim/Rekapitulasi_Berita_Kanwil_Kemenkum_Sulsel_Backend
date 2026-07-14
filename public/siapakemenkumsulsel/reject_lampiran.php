<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get POST data
$tahun = $_POST['tahun'] ?? '';
$nip = $_POST['nip'] ?? '';

// Validate input
if (empty($tahun) || empty($nip)) {
    echo json_encode(['success' => false, 'message' => 'Tahun dan NIP harus diisi']);
    exit();
}

try {
    // Start transaction
    $conn->autocommit(false);
    
    // Update status to DRAFT DIKEMBALIKAN for all lampiran entries for this user and year
    $update_sql = "UPDATE skp_lampiran SET status = 'DRAFT DIKEMBALIKAN' WHERE nip = ? AND tahun = ?";
    $stmt = $conn->prepare($update_sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param('si', $nip, $tahun);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute update: ' . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows === 0) {
        throw new Exception('Tidak ada lampiran yang ditemukan untuk tahun ' . $tahun);
    }
    
    // Commit transaction
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Lampiran SKP berhasil ditolak dan dikembalikan ke status DRAFT',
        'affected_rows' => $affected_rows
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    $conn->autocommit(true);
    
    error_log("Reject lampiran error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Gagal menolak lampiran: ' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
