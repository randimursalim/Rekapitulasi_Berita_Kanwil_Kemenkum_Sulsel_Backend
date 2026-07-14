<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_skp_global'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}
$id_skp_global = $_POST['id_skp_global'];

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}
$conn->autocommit(false);
try {
    $stmt1 = $conn->prepare('DELETE FROM skp_pegawai WHERE id_skp_global = ?');
    $stmt1->bind_param('i', $id_skp_global);
    $stmt1->execute();
    $stmt2 = $conn->prepare('DELETE FROM skp_perilaku_pegawai WHERE id_skp_global = ?');
    $stmt2->bind_param('i', $id_skp_global);
    $stmt2->execute();
    $conn->commit();
    $stmt1->close();
    $stmt2->close();
    $conn->close();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus SKP.']);
}
