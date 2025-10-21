<?php
// Debug file untuk memeriksa database konten
require_once __DIR__ . '/../config/database.php';

echo "<h1>Database Konten Debug</h1>";

try {
    // Test koneksi database
    if (!$conn) {
        echo "<p style='color:red;'>❌ Database connection failed</p>";
        exit;
    }
    echo "<p style='color:green;'>✅ Database connection successful</p>";
    
    // Test query sederhana
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total records in konten table:</strong> " . $count['total'] . "</p>";
    
    // Test query dengan dokumentasi
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != ''");
    $countWithDoc = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Records with dokumentasi:</strong> " . $countWithDoc['total'] . "</p>";
    
    // Test query dengan filter gambar
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' AND dokumentasi != 'user.jpg'");
    $countWithImage = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Records with image files:</strong> " . $countWithImage['total'] . "</p>";
    
    // Tampilkan beberapa contoh data
    echo "<h2>Sample Data:</h2>";
    $stmt = $conn->query("SELECT id_konten, judul, dokumentasi, jenis, created_at FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != '' LIMIT 10");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($samples) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Judul</th><th>Dokumentasi</th><th>Jenis</th><th>Created At</th></tr>";
        foreach ($samples as $sample) {
            echo "<tr>";
            echo "<td>" . $sample['id_konten'] . "</td>";
            echo "<td>" . $sample['judul'] . "</td>";
            echo "<td>" . $sample['dokumentasi'] . "</td>";
            echo "<td>" . $sample['jenis'] . "</td>";
            echo "<td>" . $sample['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>❌ No data found with dokumentasi</p>";
    }
    
    // Test API endpoint
    echo "<h2>API Test:</h2>";
    echo "<p><a href='ajax/gallery_photos.php' target='_blank'>Test Gallery API</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
