<?php
// Test if there's data in tables
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    
    $results = [];
    
    // Check konten table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM konten");
    $results['konten'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Check konten_berita table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM konten_berita");
    $results['konten_berita'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Check konten_medsos table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM konten_medsos");
    $results['konten_medsos'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'success' => true,
        'data_counts' => $results
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>