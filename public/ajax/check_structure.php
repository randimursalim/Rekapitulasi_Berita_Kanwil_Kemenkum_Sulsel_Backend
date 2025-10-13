<?php
// Check table structure
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    
    $tables = ['konten', 'konten_berita', 'konten_medsos'];
    $structures = [];
    
    foreach ($tables as $table) {
        $stmt = $conn->query("DESCRIBE $table");
        $structures[$table] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'structures' => $structures
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
