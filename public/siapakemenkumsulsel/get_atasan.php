<?php
header('Content-Type: application/json');

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get NIP from POST data
$nip = $_POST['nip'] ?? '';

if (empty($nip)) {
    echo json_encode(['success' => false, 'error' => 'NIP is required']);
    exit();
}

try {
    // First, get the selected employee's ATASAN_LANGSUNG
    $sql_pegawai = "SELECT ATASAN_LANGSUNG FROM Pegawai WHERE NIP = ?";
    $stmt_pegawai = $conn->prepare($sql_pegawai);
    
    if (!$stmt_pegawai) {
        throw new Exception('Failed to prepare statement');
    }
    
    $stmt_pegawai->bind_param('s', $nip);
    $stmt_pegawai->execute();
    $result_pegawai = $stmt_pegawai->get_result();
    
    if ($result_pegawai->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Employee not found']);
        exit();
    }
    
    $pegawai_data = $result_pegawai->fetch_assoc();
    $atasan_jabatan = $pegawai_data['ATASAN_LANGSUNG'];
    
    $stmt_pegawai->close();
    
    if (empty($atasan_jabatan)) {
        echo json_encode(['success' => false, 'error' => 'No direct supervisor assigned']);
        exit();
    }
    
    // Now get the atasan langsung employee data using the job title
    $sql_atasan = "SELECT * FROM Pegawai WHERE JABATAN = ? LIMIT 1";
    $stmt_atasan = $conn->prepare($sql_atasan);
    
    if (!$stmt_atasan) {
        throw new Exception('Failed to prepare atasan statement');
    }
    
    $stmt_atasan->bind_param('s', $atasan_jabatan);
    $stmt_atasan->execute();
    $result_atasan = $stmt_atasan->get_result();
    
    if ($result_atasan->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Supervisor not found']);
        exit();
    }
    
    $atasan_data = $result_atasan->fetch_assoc();
    $stmt_atasan->close();
    
    echo json_encode([
        'success' => true,
        'atasan' => $atasan_data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
