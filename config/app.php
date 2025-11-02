// config/app.php
<?php
// Auto-detect BASE_URL untuk localhost vs hosting
// Di localhost: /rekap-konten/public
// Di hosting: '' (kosong, handled by .htaccess)

// Deteksi environment
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$httpHost = $_SERVER['HTTP_HOST'] ?? '';

// Cek apakah localhost (berdasarkan beberapa indikator)
$isLocalhost = (
    strpos($serverName, 'localhost') !== false ||
    strpos($serverName, '127.0.0.1') !== false ||
    strpos($httpHost, 'localhost') !== false ||
    strpos($requestUri, '/rekap-konten/public') !== false ||
    strpos($scriptName, '/rekap-konten/public') !== false
);

// Set BASE_URL berdasarkan environment
if (!defined('BASE_URL')) {
    if ($isLocalhost) {
        define('BASE_URL', '/rekap-konten/public');
    } else {
        // Production hosting: kosong (handled by .htaccess)
        define('BASE_URL', '');
    }
}
