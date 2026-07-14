<?php
// Debug script to check SATUAN column in SKP Akhir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

$id_skp_global = $_GET['id_skp_global'] ?? '3';

echo "<h2>Debug SKP Akhir SATUAN Column</h2>";
echo "<p>ID SKP Global: $id_skp_global</p>";

// Check table structure
echo "<h3>Table Structure - skp_akhir_pegawai</h3>";
$structure_sql = "DESCRIBE skp_akhir_pegawai";
$structure_result = $conn->query($structure_sql);
if ($structure_result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $structure_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check actual data
echo "<h3>Actual Data from skp_akhir_pegawai</h3>";
$data_sql = "SELECT * FROM skp_akhir_pegawai WHERE ID_SKP_GLOBAL = ?";
$stmt = $conn->prepare($data_sql);
$stmt->bind_param('i', $id_skp_global);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    $first_row = true;
    while ($row = $result->fetch_assoc()) {
        if ($first_row) {
            echo "<tr>";
            foreach (array_keys($row) as $key) {
                echo "<th>" . htmlspecialchars($key) . "</th>";
            }
            echo "</tr>";
            $first_row = false;
        }
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found for ID_SKP_GLOBAL = $id_skp_global</p>";
}

$conn->close();
?>
