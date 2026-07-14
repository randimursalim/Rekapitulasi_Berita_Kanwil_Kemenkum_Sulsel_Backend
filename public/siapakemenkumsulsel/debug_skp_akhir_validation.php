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
echo "Checking SKP Akhir data for ID_SKP_GLOBAL = " . $id_skp_global . "\n";

// Check SKP Akhir umpan balik
$skp_check_sql = "SELECT ID_SKP, UMPAN_BALIK_DENGAN_BUKTI_DUKUNG FROM skp_akhir_pegawai WHERE ID_SKP_GLOBAL = ?";
$stmt_check = $conn->prepare($skp_check_sql);
$stmt_check->bind_param('i', $id_skp_global);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

echo "SKP Akhir records found: " . $result_check->num_rows . "\n";
while ($row = $result_check->fetch_assoc()) {
    echo "ID_SKP: " . $row['ID_SKP'] . ", UMPAN_BALIK: " . ($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?: 'NULL/EMPTY') . "\n";
}
$stmt_check->close();

// Check Perilaku Kerja umpan balik
$perilaku_fields = [
    'UMPAN_BALIK_BERORIENTASI_PELAYANAN',
    'UMPAN_BALIK_AKUNTABEL', 
    'UMPAN_BALIK_KOMPETEN',
    'UMPAN_BALIK_HARMONIS',
    'UMPAN_BALIK_LOYAL',
    'UMPAN_BALIK_ADAPTIF',
    'UMPAN_BALIK_KOLABORATIF'
];

$perilaku_check_sql = "SELECT " . implode(', ', $perilaku_fields) . " FROM skp_akhir_perilaku_pegawai WHERE ID_SKP_GLOBAL = ?";
$stmt_perilaku_check = $conn->prepare($perilaku_check_sql);
$stmt_perilaku_check->bind_param('i', $id_skp_global);
$stmt_perilaku_check->execute();
$result_perilaku = $stmt_perilaku_check->get_result();

echo "Perilaku records found: " . $result_perilaku->num_rows . "\n";
if ($result_perilaku->num_rows > 0) {
    $perilaku_data = $result_perilaku->fetch_assoc();
    foreach ($perilaku_fields as $field) {
        echo $field . ": " . ($perilaku_data[$field] ?: 'NULL/EMPTY') . "\n";
    }
}
$stmt_perilaku_check->close();

$conn->close();
?>
