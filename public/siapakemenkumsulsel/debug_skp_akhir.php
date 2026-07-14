<?php
// Debug page to show all SKP Akhir records with their ID_SKP_GLOBAL values

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "<h2>🔍 Debug: All SKP Akhir Records</h2>";
echo "<p>This page shows all records in skp_akhir_pegawai with their ID_SKP_GLOBAL values.</p>";

// Get all records
$sql = "SELECT ID_SKP, ID_SKP_GLOBAL, NAMA, NIP, TAHUN, STATUS, TANGGAL_INPUT_SKP FROM skp_akhir_pegawai ORDER BY ID_SKP_GLOBAL, ID_SKP";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID_SKP</th><th>ID_SKP_GLOBAL</th><th>NAMA</th><th>NIP</th><th>TAHUN</th><th>STATUS</th><th>TANGGAL_INPUT</th>";
    echo "</tr>";
    
    while ($row = $result->fetch_assoc()) {
        $status_color = '';
        if ($row['STATUS'] === 'draft') $status_color = 'background-color: #fff3cd;';
        elseif ($row['STATUS'] === 'PROSES EVALUASI') $status_color = 'background-color: #d1ecf1;';
        elseif ($row['STATUS'] === 'SELESAI EVALUASI') $status_color = 'background-color: #d4edda;';
        
        echo "<tr style='$status_color'>";
        echo "<td>" . htmlspecialchars($row['ID_SKP']) . "</td>";
        echo "<td><strong style='color: #007bff; font-size: 16px;'>" . htmlspecialchars($row['ID_SKP_GLOBAL']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['NAMA']) . "</td>";
        echo "<td>" . htmlspecialchars($row['NIP']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['TAHUN']) . "</strong></td>";
        echo "<td><strong>" . htmlspecialchars($row['STATUS']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['TANGGAL_INPUT_SKP']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Show summary
    $summary_sql = "SELECT 
        COUNT(*) as total_records,
        COUNT(DISTINCT ID_SKP_GLOBAL) as unique_global_ids,
        COUNT(DISTINCT NIP) as unique_employees,
        MIN(STATUS) as min_status,
        MAX(STATUS) as max_status
    FROM skp_akhir_pegawai";
    $summary_result = $conn->query($summary_sql);
    $summary = $summary_result->fetch_assoc();
    
    echo "<div style='background-color: #e9ecef; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
    echo "<h3>📊 Summary</h3>";
    echo "<p><strong>Total Records:</strong> " . $summary['total_records'] . "</p>";
    echo "<p><strong>Unique Global IDs:</strong> " . $summary['unique_global_ids'] . "</p>";
    echo "<p><strong>Unique Employees:</strong> " . $summary['unique_employees'] . "</p>";
    echo "<p><strong>Status Range:</strong> " . $summary['min_status'] . " to " . $summary['max_status'] . "</p>";
    echo "</div>";
    
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ No records found in skp_akhir_pegawai</p>";
}

$conn->close();
?>

<style>
table { font-family: Arial, sans-serif; }
th { background-color: #1a4a8a; color: white; padding: 10px; }
td { padding: 8px; border: 1px solid #ddd; }
</style>
