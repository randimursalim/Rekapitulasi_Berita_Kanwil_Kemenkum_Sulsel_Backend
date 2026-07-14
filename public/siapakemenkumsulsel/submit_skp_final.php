<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get id_skp_global from POST data
$id_skp_global = $_POST['id_skp_global'] ?? '';

if (empty($id_skp_global)) {
    echo json_encode(['success' => false, 'message' => 'ID SKP Global tidak ditemukan']);
    exit();
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Update status in skp_pegawai table to 'PROSES EVALUASI' (only for SUBMITTED)
    $stmt1 = $conn->prepare("UPDATE skp_pegawai SET STATUS = 'PROSES EVALUASI' WHERE id_skp_global = ? AND NIP = ? AND STATUS = 'SUBMITTED'");
    if (!$stmt1) {
        throw new Exception("Error preparing skp_pegawai update: " . $conn->error);
    }
    $stmt1->bind_param('is', $id_skp_global, $_SESSION['nip']);
    if (!$stmt1->execute()) {
        throw new Exception("Error updating skp_pegawai: " . $stmt1->error);
    }
    $affected_rows = $stmt1->affected_rows;
    $stmt1->close();
    
    if ($affected_rows === 0) {
        throw new Exception("SKP tidak dapat disubmit ke atasan. Status SKP mungkin sudah berubah atau tidak ditemukan.");
    }
    
    // Update status in skp_perilaku_pegawai table to 'PROSES EVALUASI' (only for SUBMITTED)
    $stmt2 = $conn->prepare("UPDATE skp_perilaku_pegawai SET STATUS = 'PROSES EVALUASI' WHERE id_skp_global = ? AND NIP = ? AND STATUS = 'SUBMITTED'");
    if (!$stmt2) {
        throw new Exception("Error preparing skp_perilaku_pegawai update: " . $conn->error);
    }
    $stmt2->bind_param('is', $id_skp_global, $_SESSION['nip']);
    if (!$stmt2->execute()) {
        throw new Exception("Error updating skp_perilaku_pegawai: " . $stmt2->error);
    }
    $stmt2->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'SKP berhasil dikirim ke atasan untuk evaluasi!']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
