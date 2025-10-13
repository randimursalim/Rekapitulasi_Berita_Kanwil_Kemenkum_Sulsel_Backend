<?php
header('Content-Type: application/json; charset=utf-8');

// 🔹 Koneksi database
require_once '../../config/database.php'; // sesuaikan path

// 🔹 Ambil query dari AJAX
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

try {
    // 🔹 Prepare statement dengan LIKE untuk judul
    $stmt = $conn->prepare("
        SELECT k.id_konten, k.jenis, k.judul, k.divisi, k.dokumentasi,
               kb.tanggal_berita, kb.link_berita, kb.sumber_berita, kb.jenis_berita, kb.ringkasan,
               km.tanggal_post, km.link_post, km.caption
        FROM konten k
        LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
        LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
        WHERE k.judul LIKE :query
        ORDER BY k.tanggal_input DESC
        LIMIT 50
    ");

    $stmt->execute(['query' => "%$query%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔹 Kirim hasil JSON
    echo json_encode($results);

} catch (PDOException $e) {
    // 🔹 Jika error, kirim JSON kosong atau log error
    echo json_encode([]);
    // error_log($e->getMessage()); // bisa diaktifkan untuk debug
}
