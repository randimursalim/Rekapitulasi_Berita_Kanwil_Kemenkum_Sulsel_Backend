<?php
// Test if tables exist
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    
    // Check if tables exist
    $tables = ['konten', 'konten_berita', 'konten_medsos'];
    $existingTables = [];
    
    foreach ($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            $existingTables[] = $table;
        }
    }
    
    echo json_encode([
        'success' => true,
        'existing_tables' => $existingTables,
        'all_tables_exist' => count($existingTables) === count($tables)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
