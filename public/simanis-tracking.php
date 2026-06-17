<?php
// Auto-detect BASE_URL untuk localhost vs hosting
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$httpHost = $_SERVER['HTTP_HOST'] ?? '';

// Deteksi localhost dengan lebih akurat
$isLocalhost = (
    strpos($serverName, 'localhost') !== false ||
    strpos($serverName, '127.0.0.1') !== false ||
    strpos($httpHost, 'localhost') !== false ||
    strpos($httpHost, '127.0.0.1') !== false ||
    strpos($requestUri, '/rekap-konten/public') !== false ||
    strpos($scriptName, '/rekap-konten/public') !== false ||
    (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && strpos($_SERVER['HTTP_X_FORWARDED_HOST'], 'localhost') !== false)
);

// Set BASE - di hosting biasanya kosong karena file ada di root public
$BASE = $isLocalhost ? '/rekap-konten/public' : '';

// Fallback: jika BASE kosong tapi script ada di subdirectory, deteksi otomatis
if (empty($BASE) && strpos($scriptName, '/public/') !== false) {
    $pathParts = explode('/public/', $scriptName);
    if (count($pathParts) > 1) {
        $BASE = $pathParts[0] . '/public';
    }
}

// Pastikan BASE selalu dimulai dengan / jika tidak kosong
if (!empty($BASE) && $BASE[0] !== '/') {
    $BASE = '/' . $BASE;
}

// Pastikan BASE tidak diakhiri dengan /
$BASE = rtrim($BASE, '/');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Surat - Simanis</title>
    <link rel="icon" type="image/png" href="<?= $BASE ?>/Images/aset_landing.png">
    <!-- Cache busting: update version number when CSS/JS changes -->
    <?php $version = '1.0.1'; // Update this number when you make CSS/JS changes 
    ?>
    <link rel="stylesheet" href="<?= $BASE ?>/css/simanis.css?v=<?= $version ?>">
    <link rel="stylesheet" href="<?= $BASE ?>/vendor/fontawesome/css/all.min.css">

</head>

<body>
    <!-- SIMTAMU-TRACKING -->
    <section id="simanis">
        <!-- HEADER -->
        <div class="simanis-header">

            <img src="<?= $BASE ?>/Images/LOGO KEMENKUM.jpeg" class="simanis-logo">

            <div class="simanis-header-text">

                <div class="line-1">
                    KANTOR WILAYAH KEMENTERIAN HUKUM
                </div>

                <div class="line-2">
                    SULAWESI SELATAN
                </div>

            </div>

        </div>

        <!-- BODY -->
        <div class="section-content">
            <div class="tracking-wrapper">
                <h1 class="tracking-title">
                    TRACKING SURAT ONLINE
                </h1>

                <p class="tracking-desc">
                    Masukkan ID Surat untuk <b>Lacak</b>:
                </p>

                <div class="tracking-search">
                    <i class="fas fa-search"></i>
                    <input type="text" id="trackingId" placeholder="Masukkan ID Pengajuan Anda">

                    <button id="btnCariTracking">
                        Cari
                    </button>
                </div>

                <div id="trackingResult"></div>

                <a href="<?= $BASE ?>/index.php?page=simanis" class="btn-tracking">
                    Ajukan Surat
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer-section">

        <div class="footer-container">

            <div class="footer-logo">
                <p>© 2026 <strong>Simanis</strong><br>
                    Humas Kanwil Kemenkum SulSel</p>
            </div>

            <div class="footer-social">
                <a href="https://www.instagram.com/kemenkumsulsel"><i class="fab fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@kemenkumsulsel"><i class="fab fa-tiktok"></i></a>
                <a href="https://www.facebook.com/kemenkumsulsel"><i class="fab fa-facebook"></i></a>
                <a href="https://www.youtube.com/@kemenkumsulsel"><i class="fab fa-youtube"></i></a>
                <a href="https://x.com/kemenkumsulsel"><i class="fab fa-x-twitter"></i></a>
            </div>

            <div class="footer-contact">
                <p>Layanan Pengaduan:</p>
                <a href="https://wa.me/6282196735747">
                    <i class="fab fa-whatsapp"></i>
                    +62 821-9673-5747
                </a>
            </div>
        </div>
    </footer>

    <script>
        window.APP_BASE = "<?= $BASE ?>";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $BASE ?>/js/simanis-tracking.js?v=<?= $version ?>"></script>

</body>

</html>