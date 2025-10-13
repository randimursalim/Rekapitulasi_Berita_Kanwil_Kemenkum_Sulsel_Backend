<?php
// Test the actual query
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';
    
    // Simple query to test
    $query = "
    SELECT k.id_konten, k.jenis, k.judul, k.divisi, k.dokumentasi, k.tanggal_input,
           kb.tanggal_berita, kb.link_berita, kb.sumber_berita, kb.jenis_berita, kb.ringkasan,
           km.tanggal_post, km.link_post, km.caption, km.jenis as medsos_jenis
    FROM konten k
    LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
    LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
    WHERE 1=1
    ORDER BY k.tanggal_input DESC
    LIMIT 5
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'query' => $query ?? 'Query not set'
    ]);
}
?>
