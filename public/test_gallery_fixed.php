<?php
// Simple test untuk memverifikasi API gallery
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../app/models/KontenModel.php';
    require_once __DIR__ . '/../../config/database.php';
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $kontenModel = new KontenModel();
    $photos = $kontenModel->getGalleryPhotos(5);
    
    echo json_encode([
        'success' => true,
        'data' => $photos,
        'count' => count($photos),
        'debug' => [
            'total_photos' => count($photos),
            'sample_photo' => count($photos) > 0 ? $photos[0] : null
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
