<?php
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

$res = $conn->query("SELECT id, id_skp_global, NAMA, NIP, TAHUN, TRIWULAN, JENIS_KINERJA, PERSPEKTIF, STATUS FROM skp_pegawai ORDER BY id_skp_global, id");
if ($res) {
    echo "<table border='1'>";
    echo "<tr><th>id</th><th>id_skp_global</th><th>NAMA</th><th>TRIWULAN</th><th>JENIS_KINERJA</th><th>PERSPEKTIF</th><th>STATUS</th></tr>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['id_skp_global'] . "</td>";
        echo "<td>" . htmlspecialchars($row['NAMA']) . "</td>";
        echo "<td>" . htmlspecialchars($row['TRIWULAN']) . "</td>";
        echo "<td>" . htmlspecialchars($row['JENIS_KINERJA']) . "</td>";
        echo "<td>" . ($row['PERSPEKTIF'] !== null ? htmlspecialchars($row['PERSPEKTIF']) : "NULL") . "</td>";
        echo "<td>" . htmlspecialchars($row['STATUS']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error: " . $conn->error;
}
$conn->close();
?>
