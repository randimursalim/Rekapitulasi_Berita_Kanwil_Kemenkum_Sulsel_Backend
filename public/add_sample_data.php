<?php
// Add sample data to database
require_once __DIR__ . '/../config/database.php';

echo "<h1>Add Sample Data</h1>";

if (!$conn) {
    echo "<p style='color:red;'>❌ Database connection failed</p>";
    exit;
}

try {
    // Check if konten table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'konten'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color:red;'>❌ Table 'konten' does not exist. Please create it first.</p>";
        exit;
    }
    
    // Check current data
    $stmt = $conn->query("SELECT COUNT(*) as total FROM konten");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Current records: " . $count['total'] . "</p>";
    
    if ($count['total'] == 0) {
        echo "<p>Adding sample data...</p>";
        
        // Add sample data
        $sampleData = [
            ['berita', 'Kegiatan Humas Kanwil Sulsel', 'Dirjen AHU Pastikan Dukungan Layanan Publik di Sulsel Makin Optimal.jpeg'],
            ['berita', 'Harmonisasi Ramperda', 'Proses Pengajuan Permohonan Harmonisasi Ramperda dan Ramperkada 1.jpeg'],
            ['medsos', 'Posting Instagram', 'user_1_1760510656.jpg'],
            ['medsos', 'Posting Facebook', 'user_2_1760510647.jpg'],
            ['berita', 'Kegiatan Rutin', 'user_7_1760510611.jpg'],
            ['medsos', 'Posting Twitter', 'user_11_1760594532.jpg']
        ];
        
        $stmt = $conn->prepare("INSERT INTO konten (jenis, judul, divisi, dokumentasi) VALUES (?, ?, ?, ?)");
        
        foreach ($sampleData as $data) {
            $stmt->execute([$data[0], $data[1], 'Humas', $data[2]]);
        }
        
        echo "<p style='color:green;'>✅ Sample data added successfully!</p>";
        
        // Show new count
        $stmt = $conn->query("SELECT COUNT(*) as total FROM konten");
        $newCount = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>New total records: " . $newCount['total'] . "</p>";
        
    } else {
        echo "<p style='color:orange;'>⚠️ Database already has data. No sample data added.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
