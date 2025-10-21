<?php
// Debug gallery query specifically
require_once __DIR__ . '/../config/database.php';

echo "<h1>Gallery Query Debug</h1>";

if (!$conn) {
    echo "<p style='color:red;'>❌ Database connection failed</p>";
    exit;
}

try {
    // Check total records
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total records:</strong> " . $total['total'] . "</p>";
    
    // Check records with dokumentasi
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != ''");
    $withDoc = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Records with dokumentasi:</strong> " . $withDoc['total'] . "</p>";
    
    // Check records with dokumentasi != 'user.jpg'
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' AND dokumentasi != 'user.jpg'");
    $notUser = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Records with dokumentasi != 'user.jpg':</strong> " . $notUser['total'] . "</p>";
    
    // Check records with image extensions
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' AND dokumentasi != 'user.jpg' AND (dokumentasi LIKE '%.jpg' OR dokumentasi LIKE '%.jpeg' OR dokumentasi LIKE '%.png' OR dokumentasi LIKE '%.gif')");
    $withImageExt = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Records with image extensions:</strong> " . $withImageExt['total'] . "</p>";
    
    // Show all dokumentasi values
    echo "<h2>All Dokumentasi Values:</h2>";
    $stmt = $conn->query("SELECT DISTINCT dokumentasi FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' ORDER BY dokumentasi");
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<ul>";
    foreach ($docs as $doc) {
        $isImage = preg_match('/\.(jpg|jpeg|png|gif)$/i', $doc['dokumentasi']);
        $style = $isImage ? "color:green;" : "color:red;";
        echo "<li style='$style'>" . $doc['dokumentasi'] . ($isImage ? " ✅" : " ❌") . "</li>";
    }
    echo "</ul>";
    
    // Test the exact query from getGalleryPhotos
    echo "<h2>Gallery Query Test:</h2>";
    $stmt = $conn->prepare("
        SELECT 
            id_konten,
            judul,
            dokumentasi,
            jenis,
            created_at
        FROM konten 
        WHERE dokumentasi IS NOT NULL 
        AND dokumentasi != '' 
        AND dokumentasi != 'user.jpg'
        ORDER BY created_at DESC
        LIMIT 12
    ");
    $stmt->execute();
    $galleryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Gallery query results:</strong> " . count($galleryData) . " records</p>";
    
    if (count($galleryData) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Judul</th><th>Dokumentasi</th><th>Jenis</th><th>Created At</th></tr>";
        foreach ($galleryData as $item) {
            echo "<tr>";
            echo "<td>" . $item['id_konten'] . "</td>";
            echo "<td>" . $item['judul'] . "</td>";
            echo "<td>" . $item['dokumentasi'] . "</td>";
            echo "<td>" . $item['jenis'] . "</td>";
            echo "<td>" . $item['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test API endpoint
    echo "<h2>API Test:</h2>";
    echo "<p><a href='ajax/gallery_photos.php' target='_blank'>Test Gallery API</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
