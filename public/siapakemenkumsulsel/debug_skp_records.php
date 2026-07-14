<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'skp';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$id_skp_global = 3;
echo "All SKP Akhir records for ID_SKP_GLOBAL = " . $id_skp_global . "\n";

$sql = "SELECT ID_SKP, RENCANA_HASIL_KERJA, UMPAN_BALIK_DENGAN_BUKTI_DUKUNG FROM skp_akhir_pegawai WHERE ID_SKP_GLOBAL = ? ORDER BY ID_SKP";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_skp_global);
$stmt->execute();
$result = $stmt->get_result();

$index = 0;
while ($row = $result->fetch_assoc()) {
    echo "Index " . $index . ": ID_SKP=" . $row['ID_SKP'] . ", RENCANA_HASIL_KERJA=" . substr($row['RENCANA_HASIL_KERJA'], 0, 50) . "..., UMPAN_BALIK=" . ($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?: 'NULL') . "\n";
    $index++;
}
$stmt->close();
$conn->close();
?>
