<?php
// Test if image files exist
require_once __DIR__ . '/../config/database.php';

echo "<h1>Image Files Check</h1>";

if (!$conn) {
    echo "<p style='color:red;'>❌ Database connection failed</p>";
    exit;
}

try {
    // Get all dokumentasi values
    $stmt = $conn->query("SELECT DISTINCT dokumentasi FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' ORDER BY dokumentasi");
    $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Checking Image Files:</h2>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Dokumentasi</th><th>File Exists</th><th>Path</th></tr>";
    
    foreach ($docs as $doc) {
        $filename = $doc['dokumentasi'];
        $imagePath = __DIR__ . '/Images/' . $filename;
        $fileExists = file_exists($imagePath);
        
        $style = $fileExists ? "color:green;" : "color:red;";
        $status = $fileExists ? "✅ EXISTS" : "❌ NOT FOUND";
        
        echo "<tr>";
        echo "<td>" . $filename . "</td>";
        echo "<td style='$style'>" . $status . "</td>";
        echo "<td>" . $imagePath . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Test API with debug
    echo "<h2>API Test with Debug:</h2>";
    echo "<p><a href='ajax/gallery_photos.php' target='_blank'>Test Gallery API</a></p>";
    
    // Show what the API should return
    echo "<h2>Expected API Response:</h2>";
    $stmt = $conn->query("SELECT id_konten, judul, dokumentasi, jenis, created_at FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' AND dokumentasi != 'user.jpg' ORDER BY created_at DESC LIMIT 5");
    $galleryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $expectedPhotos = [];
    foreach ($galleryData as $item) {
        $filename = $item['dokumentasi'];
        $imagePath = __DIR__ . '/Images/' . $filename;
        $fileExists = file_exists($imagePath);
        
        if ($fileExists) {
            $expectedPhotos[] = [
                'id' => $item['id_konten'],
                'title' => $item['judul'],
                'image' => $filename,
                'type' => $item['jenis'],
                'date' => $item['created_at'],
                'category' => ucfirst($item['jenis'])
            ];
        }
    }
    
    echo "<pre>" . json_encode($expectedPhotos, JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
