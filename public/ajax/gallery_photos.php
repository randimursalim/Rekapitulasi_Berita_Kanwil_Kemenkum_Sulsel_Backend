<?php
header('Content-Type: application/json');

// Include necessary files
require_once __DIR__ . '/../../app/models/KontenModel.php';
require_once __DIR__ . '/../../config/database.php';

try {
    // Use existing database connection
    if (!isset($conn) || !$conn) {
        throw new Exception('Database connection not available');
    }
    
    $kontenModel = new KontenModel();
    
    // Get photos from database (only those with dokumentasi)
    // Filter sudah dilakukan di getGalleryPhotos() - hanya return file yang benar-benar ada
    $photos = $kontenModel->getGalleryPhotos();
    
    // Log untuk debugging
    error_log("[GALLERY API] Returning " . count($photos) . " photos");
    
    echo json_encode([
        'success' => true,
        'data' => $photos ? $photos : [],
        'count' => count($photos)
    ]);
} catch (Exception $e) {
    error_log("Gallery Photos Error: " . $e->getMessage());
    error_log("Gallery Photos Error Trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ]);
}
?>
