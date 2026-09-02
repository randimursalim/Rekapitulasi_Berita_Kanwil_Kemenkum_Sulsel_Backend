<?php
// HENTIKAN SEMUA OUTPUT
while (ob_get_level()) {
    ob_end_clean();
}

// HAPUS SEMUA HEADER DEFAULT
header_remove();

// ⛔ PENTING: IZINKAN IFRAME
header('X-Frame-Options: SAMEORIGIN');

$file = $_GET['file'] ?? '';
$id = $_GET['id'] ?? '';

if (!$file && !$id) {
    http_response_code(400);
    exit('File atau ID tidak valid');
}

$filePath = $file;

// Jika parameter ID diberikan, cari path file dari database
if ($id) {
    require_once __DIR__ . '/../config/database.php';
    global $conn;
    if ($conn) {
        $stmt = $conn->prepare("SELECT file, file_balasan FROM tb_izin WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            // Prioritaskan file_balasan jika ada, jika tidak ada gunakan file pengajuan
            $filePath = !empty($data['file_balasan']) ? $data['file_balasan'] : $data['file'];
        }
    }
}

if (!$filePath) {
    http_response_code(404);
    exit('File tidak ditemukan');
}

$baseDir = realpath(__DIR__ . '/storage/uploads');
$fullPath = realpath(__DIR__ . '/' . ltrim($filePath, '/'));

// SECURITY
if (!$fullPath || strpos($fullPath, $baseDir) !== 0) {
    http_response_code(403);
    exit('Akses ditolak');
}

if (!file_exists($fullPath)) {
    http_response_code(404);
    exit('File tidak ditemukan');
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
header('Content-Length: ' . filesize($fullPath));

readfile($fullPath);
exit;