<?php
// Debug script to check ID_SKP_GLOBAL values in skp_akhir_pegawai

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "<h2>Debug ID_SKP_GLOBAL in skp_akhir_pegawai</h2>";

// Check all records in skp_akhir_pegawai
$sql = "SELECT ID_SKP, ID_SKP_GLOBAL, NAMA, NIP, TAHUN, STATUS FROM skp_akhir_pegawai ORDER BY ID_SKP_GLOBAL, ID_SKP";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID_SKP</th><th>ID_SKP_GLOBAL</th><th>NAMA</th><th>NIP</th><th>TAHUN</th><th>STATUS</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ID_SKP']) . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['ID_SKP_GLOBAL']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['NAMA']) . "</td>";
        echo "<td>" . htmlspecialchars($row['NIP']) . "</td>";
        echo "<td>" . htmlspecialchars($row['TAHUN']) . "</td>";
        echo "<td>" . htmlspecialchars($row['STATUS']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No records found in skp_akhir_pegawai</p>";
}

$conn->close();
?>
