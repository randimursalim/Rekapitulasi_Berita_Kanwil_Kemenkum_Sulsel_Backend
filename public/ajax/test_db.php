<?php
// Test database connection
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    
    // Test simple query
    $stmt = $conn->query("SELECT 1 as test");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'test' => $result
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
