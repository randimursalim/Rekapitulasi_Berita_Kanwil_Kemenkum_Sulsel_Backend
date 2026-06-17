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
if (!$file) {
    http_response_code(400);
    exit('File tidak valid');
}

$baseDir = realpath(__DIR__ . '/storage/uploads');
$fullPath = realpath(__DIR__ . '/' . $file);

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