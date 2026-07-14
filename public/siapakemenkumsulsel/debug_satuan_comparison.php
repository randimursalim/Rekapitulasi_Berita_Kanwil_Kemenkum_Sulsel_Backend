<?php
// Debug script to compare SATUAN data between regular SKP and SKP Akhir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "<h2>SATUAN Column Comparison</h2>";

// Check regular SKP (id_skp_global = 8)
echo "<h3>Regular SKP (id_skp_global = 8)</h3>";
$sql_regular = "SELECT s.*, p.JABATAN, p.UNIT_KERJA, p.PANGKAT_GOL_RUANG 
                FROM skp_pegawai s 
                LEFT JOIN Pegawai p ON s.NIP = p.NIP 
                WHERE s.id_skp_global = 8 
                ORDER BY s.id_skp";
$result_regular = $conn->query($sql_regular);

if ($result_regular && $result_regular->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>JENIS_KINERJA</th><th>TARGET</th><th>REALISASI</th><th>SATUAN</th></tr>";
    while ($row = $result_regular->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['JENIS_KINERJA'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['TARGET'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? 'N/A') . "</td>";
        echo "<td style='background: #ffffcc; font-weight: bold;'>" . htmlspecialchars($row['SATUAN'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found for regular SKP</p>";
}

// Check SKP Akhir (ID_SKP_GLOBAL = 3)
echo "<h3>SKP Akhir (ID_SKP_GLOBAL = 3)</h3>";
$sql_akhir = "SELECT s.*, p.JABATAN, p.UNIT_KERJA, p.PANGKAT_GOL_RUANG 
              FROM skp_akhir_pegawai s 
              LEFT JOIN Pegawai p ON s.NIP = p.NIP 
              WHERE s.ID_SKP_GLOBAL = 3 
              ORDER BY s.ID_SKP";
$result_akhir = $conn->query($sql_akhir);

if ($result_akhir && $result_akhir->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID_SKP</th><th>JENIS_KINERJA</th><th>TARGET</th><th>REALISASI</th><th>SATUAN</th></tr>";
    while ($row = $result_akhir->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ID_SKP'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['JENIS_KINERJA'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['TARGET'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? 'N/A') . "</td>";
        echo "<td style='background: #ffffcc; font-weight: bold;'>" . htmlspecialchars($row['SATUAN'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found for SKP Akhir</p>";
}

$conn->close();
?>
