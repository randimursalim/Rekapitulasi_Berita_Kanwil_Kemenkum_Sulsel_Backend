<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401); echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit();
}

$id_skp_global = intval($_POST['id_skp_global'] ?? 0);
if (!$id_skp_global) {
    echo json_encode(['success' => false, 'message' => 'ID SKP tidak ditemukan']); exit();
}

require_once 'config/database.php';
try { $conn = getDatabaseConnection(); }
catch (Exception $e) { echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]); exit(); }

try {
    $conn->begin_transaction();

    $manager_name = $_SESSION['nama'] ?? '';
    $s1 = $conn->prepare("UPDATE skp_kuantitatif_awal_tahun_pegawai SET STATUS = 'DITOLAK' WHERE id_skp_global = ? AND NAMA_ATASAN_LANGSUNG = ? AND STATUS IN ('SUBMITTED', 'PROSES EVALUASI')");
    $s1->bind_param('is', $id_skp_global, $manager_name);
    $s1->execute();
    $affected = $s1->affected_rows;
    $s1->close();

    if ($affected === 0) throw new Exception('Gagal menolak. Status sudah berubah atau Anda bukan atasan langsung.');

    $s2 = $conn->prepare("UPDATE skp_perilaku_awal_tahun_pegawai SET STATUS = 'DITOLAK' WHERE id_skp_global = ?");
    $s2->bind_param('i', $id_skp_global); $s2->execute(); $s2->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'SKP Kuantitatif telah ditolak. Pegawai dapat mengajukan ulang.']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
$conn->close();
?>
