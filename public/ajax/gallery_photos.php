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
    $photos = $kontenModel->getGalleryPhotos();
    
    echo json_encode([
        'success' => true,
        'data' => $photos,
        'count' => count($photos)
    ]);
} catch (Exception $e) {
    error_log("Gallery Photos Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => []
    ]);
}
?>
