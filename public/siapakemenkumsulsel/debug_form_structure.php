<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    die('Not logged in');
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

$id_skp_global = 3;
$is_skp_akhir = true;

echo "=== FORM STRUCTURE DEBUG ===\n";
echo "ID_SKP_GLOBAL: $id_skp_global\n";
echo "is_skp_akhir: " . ($is_skp_akhir ? 'true' : 'false') . "\n\n";

// Fetch SKP data
$sql = "SELECT s.*, p.JABATAN, p.UNIT_KERJA, p.PANGKAT_GOL_RUANG 
        FROM skp_akhir_pegawai s 
        LEFT JOIN Pegawai p ON s.NIP = p.NIP 
        WHERE s.ID_SKP_GLOBAL = ? 
        ORDER BY s.ID_SKP";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_skp_global);
$stmt->execute();
$result = $stmt->get_result();

$skp_details = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $skp_details[] = $row;
    }
}

echo "Records found: " . count($skp_details) . "\n";
foreach ($skp_details as $index => $row) {
    echo "Index $index: ID_SKP=" . $row['ID_SKP'] . ", RENCANA_HASIL_KERJA=" . substr($row['RENCANA_HASIL_KERJA'], 0, 30) . "...\n";
}

echo "\nExpected form fields:\n";
foreach ($skp_details as $index => $row) {
    echo "skp_feedback[$index] - for ID_SKP " . $row['ID_SKP'] . "\n";
}

$stmt->close();
$conn->close();
?>
