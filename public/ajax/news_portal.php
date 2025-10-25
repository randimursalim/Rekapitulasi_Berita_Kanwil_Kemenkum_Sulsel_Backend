<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Include database connection
    require_once '../../config/database.php';
    
    // Include KontenModel
    require_once '../../app/models/KontenModel.php';
    
    // Create KontenModel instance
    $kontenModel = new KontenModel($conn);
    
    // Get latest news (limit to 10 for portal display)
    $news = $kontenModel->getLatestNews(10);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $news,
        'count' => count($news),
        'message' => 'News loaded successfully'
    ]);
    
} catch (Exception $e) {
    error_log("[ERROR] News Portal API: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'data' => [],
        'count' => 0,
        'message' => 'Failed to load news: ' . $e->getMessage()
    ]);
}
?>
