<?php
// Test untuk memverifikasi 15 foto terbaru
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../app/models/KontenModel.php';
    require_once __DIR__ . '/../../config/database.php';
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $kontenModel = new KontenModel();
    $photos = $kontenModel->getGalleryPhotos(15);
    
    echo json_encode([
        'success' => true,
        'data' => $photos,
        'count' => count($photos),
        'message' => 'Gallery photos loaded successfully',
        'debug' => [
            'total_photos' => count($photos),
            'limit_requested' => 15,
            'photos_returned' => count($photos)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
