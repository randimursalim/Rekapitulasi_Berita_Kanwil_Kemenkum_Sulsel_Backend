<?php
// Simple test untuk API gallery
header('Content-Type: application/json');

try {
    // Include files
    require_once __DIR__ . '/../app/models/KontenModel.php';
    require_once __DIR__ . '/../config/database.php';
    
    // Test koneksi
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Test model
    $kontenModel = new KontenModel();
    
    // Test query langsung
    $stmt = $conn->query("SELECT id_konten, judul, dokumentasi, jenis, created_at FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' LIMIT 5");
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Test method
    $galleryPhotos = $kontenModel->getGalleryPhotos(5);
    
    echo json_encode([
        'success' => true,
        'direct_query' => $photos,
        'model_method' => $galleryPhotos,
        'direct_count' => count($photos),
        'model_count' => count($galleryPhotos)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
