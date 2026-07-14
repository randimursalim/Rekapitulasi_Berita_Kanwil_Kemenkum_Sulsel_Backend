<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

header('Content-Type: application/json');

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Use session NIP directly
$nip   = trim($_SESSION['nip'] ?? '');
$tahun = trim($_POST['tahun'] ?? '');

if (empty($nip) || empty($tahun)) {
    echo json_encode(['success' => false, 'message' => 'NIP sesi atau Tahun tidak valid']);
    exit();
}

try {
    $conn->autocommit(false);

    // Check current status
    $check_sql = "SELECT status FROM skp_lampiran WHERE nip = ? AND tahun = ? LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        throw new Exception('Gagal menyiapkan query: ' . $conn->error);
    }
    $check_stmt->bind_param('ss', $nip, $tahun);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_stmt->close();

    if (!$check_result || $check_result->num_rows === 0) {
        throw new Exception('Lampiran tidak ditemukan untuk tahun ' . $tahun);
    }

    $check_row      = $check_result->fetch_assoc();
    $current_status = strtoupper(trim($check_row['status'] ?? ''));

    if ($current_status !== 'DRAFT' && $current_status !== 'DRAFT DIKEMBALIKAN') {
        throw new Exception('Status saat ini "' . $check_row['status'] . '" tidak dapat disubmit. Hanya DRAFT yang bisa disubmit.');
    }

    // Update status to PROSES EVALUASI
    $update_sql = "UPDATE skp_lampiran SET status = 'PROSES EVALUASI' WHERE nip = ? AND tahun = ?";
    $stmt = $conn->prepare($update_sql);
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan update: ' . $conn->error);
    }
    $stmt->bind_param('ss', $nip, $tahun);

    if (!$stmt->execute()) {
        throw new Exception('Gagal eksekusi update: ' . $stmt->error);
    }

    $affected_rows = $stmt->affected_rows;
    $stmt->close();

    if ($affected_rows === 0) {
        throw new Exception('Tidak ada baris yang terupdate.');
    }

    $conn->commit();
    $conn->autocommit(true);

    echo json_encode([
        'success'       => true,
        'message'       => 'Lampiran SKP berhasil disubmit ke atasan untuk evaluasi',
        'affected_rows' => $affected_rows
    ]);

} catch (Exception $e) {
    $conn->rollback();
    $conn->autocommit(true);
    error_log("Submit lampiran status error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>
