<?php
// Generate nomor register otomatis
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    
    // Ambil nomor register terakhir
    $stmt = $conn->prepare("SELECT no_register_pengaduan FROM layanan_pengaduan WHERE no_register_pengaduan LIKE 'P%' ORDER BY id DESC LIMIT 1");
    $stmt->execute();
    $lastRecord = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lastRecord) {
        // Extract number from last register (e.g., "P01" -> 1)
        $lastNumber = (int) substr($lastRecord['no_register_pengaduan'], 1);
        $newNumber = $lastNumber + 1;
    } else {
        // If no record exists, start from 1
        $newNumber = 1;
    }
    
    // Format: P01, P02, P03, etc.
    $noRegister = 'P' . str_pad($newNumber, 2, '0', STR_PAD_LEFT);
    
    echo json_encode([
        'success' => true,
        'no_register' => $noRegister
    ]);
    
} catch (Exception $e) {
    error_log("[ERROR] Generate No Register: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

