<?php
// Simple database check
require_once __DIR__ . '/../config/database.php';

echo "<h1>Database Check</h1>";

if (!$conn) {
    echo "<p style='color:red;'>❌ Database connection failed</p>";
    exit;
}

echo "<p style='color:green;'>✅ Database connected</p>";

// Check if konten table exists
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'konten'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:green;'>✅ Table 'konten' exists</p>";
        
        // Count total records
        $stmt = $conn->query("SELECT COUNT(*) as total FROM konten");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Total records:</strong> " . $count['total'] . "</p>";
        
        // Count records with dokumentasi
        $stmt = $conn->query("SELECT COUNT(*) as total FROM konten WHERE dokumentasi IS NOT NULL AND dokumentasi != ''");
        $countWithDoc = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p><strong>Records with dokumentasi:</strong> " . $countWithDoc['total'] . "</p>";
        
        // Show sample data
        $stmt = $conn->query("SELECT id_konten, judul, dokumentasi, jenis FROM konten LIMIT 5");
        $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($samples) > 0) {
            echo "<h2>Sample Data:</h2>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Judul</th><th>Dokumentasi</th><th>Jenis</th></tr>";
            foreach ($samples as $sample) {
                echo "<tr>";
                echo "<td>" . $sample['id_konten'] . "</td>";
                echo "<td>" . $sample['judul'] . "</td>";
                echo "<td>" . $sample['dokumentasi'] . "</td>";
                echo "<td>" . $sample['jenis'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p style='color:red;'>❌ Table 'konten' does not exist</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
