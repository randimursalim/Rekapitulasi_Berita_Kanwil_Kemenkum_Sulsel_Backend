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
require_once '../config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if all umpan balik fields are filled for SKP Akhir
    $validation_errors = [];
    
    // Check SKP Akhir umpan balik (Kinerja Utama and Kinerja Tambahan)
    // Either UMPAN_BALIK_DENGAN_BUKTI_DUKUNG has text OR UMPAN_BALIK_STICKER is 'C' (or both)
    // Skip activities that were not performed (TARGET=0 and REALISASI_BERDASARKAN_BUKTI_DUKUNG=0)
    $skp_check_sql = "SELECT ID_SKP, TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG, UMPAN_BALIK_DENGAN_BUKTI_DUKUNG, UMPAN_BALIK_STICKER 
                      FROM skp_akhir_pegawai 
                      WHERE ID_SKP_GLOBAL = ?";
    $stmt_check = $conn->prepare($skp_check_sql);
    $stmt_check->bind_param('i', $id_skp_global);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    $incomplete_skp = [];
    while ($row = $result_check->fetch_assoc()) {
        $target = trim($row['TARGET'] ?? '');
        $realisasi = trim($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '');
        $is_not_performed = ($target === '0' && $realisasi === '0');
        
        // Skip validation for activities not performed
        if ($is_not_performed) {
            continue;
        }
        
        // Check if either text or sticker (or both) is filled
        $has_text = !empty(trim($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?? ''));
        $has_sticker = (!empty($row['UMPAN_BALIK_STICKER']) && $row['UMPAN_BALIK_STICKER'] === 'C');
        
        // If neither text nor sticker is filled, it's incomplete
        if (!$has_text && !$has_sticker) {
            $incomplete_skp[] = $row['ID_SKP'];
        }
    }
    $stmt_check->close();
    
    if (!empty($incomplete_skp)) {
        $validation_errors[] = "Umpan balik untuk Kinerja Utama/Tambahan belum lengkap. Harus mengisi text atau memilih sticker 👍 (atau keduanya)";
    }
    
    // Check Perilaku Kerja umpan balik for SKP Akhir
    $perilaku_fields = [
        'UMPAN_BALIK_BERORIENTASI_PELAYANAN',
        'UMPAN_BALIK_AKUNTABEL', 
        'UMPAN_BALIK_KOMPETEN',
        'UMPAN_BALIK_HARMONIS',
        'UMPAN_BALIK_LOYAL',
        'UMPAN_BALIK_ADAPTIF',
        'UMPAN_BALIK_KOLABORATIF'
    ];
    
    $perilaku_check_sql = "SELECT " . implode(', ', $perilaku_fields) . " FROM skp_akhir_perilaku_pegawai WHERE ID_SKP_GLOBAL = ?";
    $stmt_perilaku_check = $conn->prepare($perilaku_check_sql);
    $stmt_perilaku_check->bind_param('i', $id_skp_global);
    $stmt_perilaku_check->execute();
    $result_perilaku = $stmt_perilaku_check->get_result();
    
    if ($result_perilaku->num_rows > 0) {
        $perilaku_data = $result_perilaku->fetch_assoc();
        $empty_perilaku = [];
        
        foreach ($perilaku_fields as $field) {
            if (empty($perilaku_data[$field])) {
                $empty_perilaku[] = str_replace('UMPAN_BALIK_', '', $field);
            }
        }
        
        if (!empty($empty_perilaku)) {
            $validation_errors[] = "Umpan balik untuk Perilaku Kerja belum lengkap: " . implode(', ', $empty_perilaku);
        }
    }
    $stmt_perilaku_check->close();
    
    // If validation errors exist, return error
    if (!empty($validation_errors)) {
        echo json_encode(['success' => false, 'message' => 'Tidak dapat submit evaluasi. ' . implode('; ', $validation_errors)]);
        exit();
    }
    
    // Update status in skp_akhir_pegawai table to 'SELESAI EVALUASI'
    $stmt1 = $conn->prepare("UPDATE skp_akhir_pegawai SET STATUS = 'SELESAI EVALUASI' WHERE ID_SKP_GLOBAL = ?");
    if (!$stmt1) {
        throw new Exception("Error preparing skp_akhir_pegawai update: " . $conn->error);
    }
    $stmt1->bind_param('i', $id_skp_global);
    if (!$stmt1->execute()) {
        throw new Exception("Error updating skp_akhir_pegawai: " . $stmt1->error);
    }
    $stmt1->close();
    
    // Update status in skp_akhir_perilaku_pegawai table to 'SELESAI EVALUASI'
    $stmt2 = $conn->prepare("UPDATE skp_akhir_perilaku_pegawai SET STATUS = 'SELESAI EVALUASI' WHERE ID_SKP_GLOBAL = ?");
    if (!$stmt2) {
        throw new Exception("Error preparing skp_akhir_perilaku_pegawai update: " . $conn->error);
    }
    $stmt2->bind_param('i', $id_skp_global);
    if (!$stmt2->execute()) {
        throw new Exception("Error updating skp_akhir_perilaku_pegawai: " . $stmt2->error);
    }
    $stmt2->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Evaluasi SKP Akhir berhasil disubmit! Status SKP telah diubah menjadi "SELESAI EVALUASI".']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
