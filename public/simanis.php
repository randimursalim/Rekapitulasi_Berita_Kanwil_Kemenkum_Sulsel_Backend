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
    <title>Simanis</title>
    <link rel="icon" type="image/png" href="<?= $BASE ?>/Images/aset_landing.png">
    <!-- Cache busting: update version number when CSS/JS changes -->
    <?php $version = '1.0.2'; // Update this number when you make CSS/JS changes 
    ?>
    <link rel="stylesheet" href="<?= $BASE ?>/css/simanis.css?v=<?= $version ?>">
    <link rel="stylesheet" href="<?= $BASE ?>/vendor/fontawesome/css/all.min.css">

</head>

<body>
    <!-- SIMTAMU -->
    <section id="simanis" class="section white-background">
        <!-- HEADER KEMENKUM -->
        <div class="simanis-header">
            <img src="<?= $BASE ?>/Images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" class="simanis-logo">

            <div class="simanis-header-text">
                <div class="line-1">KANTOR WILAYAH KEMENTERIAN HUKUM</div>
                <div class="line-2">SULAWESI SELATAN</div>
            </div>
        </div>

        <!-- BODY -->
        <div class="section-content">

            <div class="simanis-container">

                <!-- FORM -->
                <div class="simanis-form">
                    <h2 class="simanis-title">🗂️Pengajuan Perizinan</h2>
                    <p class="simanis-desc">Surat Izin Penelitian/Magang/External lainnya</p>

                    <form method="POST" id="FormTambah" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>NIK</label>
                            <input type="text" name="nik" placeholder="7301000000000001" required>
                        </div>

                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="nama" placeholder="Nama Lengkap" required>
                        </div>

                        <div class="form-group">
                            <label>Telepon/WA</label>
                            <input type="number" name="tlp" placeholder="628123456789" required>
                        </div>

                        <div class="form-group">
                            <label for="jenis_surat">Pilih Jenis Surat <span>*</span></label>
                            <select name="jenis_surat" id="jenis_surat" required>
                                <option value="">Pilih</option>
                                <optgroup label="Surat Pengantar">
                                    <option value="magang">Magang</option>
                                    <option value="penelitian">Penelitian</option>
                                </optgroup>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>
                                File Berkas/Lampiran
                                <span style="color:red;">*Only PDF | Max 2MB</span>
                            </label>
                            <input type="file" name="lampiran" accept="application/pdf" required>
                        </div>

                        <button type="button" id="BSimpan" class="btn-submit">
                            Simpan Data
                        </button>

                        <a href="<?= $BASE ?>/landing.php" class="simanis-back">
                            Kembali
                        </a>

                    </form>

                </div>

                <!-- LEFT IMAGE -->
                <div class="simanis-image">
                    <!-- SIMANIS -->
                    <div class="sicakap-header-center">
                        <div class="sicakap-title">SIMANIS</div>
                        <div class="sicakap-subtitle">
                            Sistem Informasi Manajemen Administrasi dan Izin Studi.
                        </div>
                        <a href="<?= $BASE ?>/index.php?page=simanis-tracking" class="btn-tracking">
                            🔎 Tracking Surat
                        </a>
                    </div>

                    <!-- GAMBAR -->
                    <img src="<?= $BASE ?>/Images/simanis-image.svg" alt="Ilustrasi Simanis"
                        class="simanis-illustration">
                </div>
            </div>

        </div>
    </section>

    <!-- <p class="created-by"><a href="#">© 2025 SiCakap - Humas Kanwil Kemenkum SulSel</a></p> -->

    <footer class="footer-section">
        <div class="footer-container">
            <div class="footer-logo">
                <p>© 2026 <strong>Simanis</strong><br>Humas Kanwil Kemenkum SulSel</p>
            </div>

            <div class="footer-social">
                <a href="https://www.instagram.com/kemenkumsulsel" target="_blank" aria-label="Instagram"><i
                        class="fab fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@kemenkumsulsel" target="_blank" aria-label="TikTok"><i
                        class="fab fa-tiktok"></i></a>
                <a href="https://www.facebook.com/kemenkumsulsel" target="_blank" aria-label="Facebook"><i
                        class="fab fa-facebook"></i></a>
                <a href="https://www.youtube.com/@kemenkumsulsel" target="_blank" aria-label="YouTube"><i
                        class="fab fa-youtube"></i></a>
                <a href="https://x.com/kemenkumsulsel" target="_blank" aria-label="X"><i
                        class="fab fa-x-twitter"></i></a>
            </div>

            <div class="footer-contact">
                <p>Layanan Pengaduan:</p>
                <a href="https://wa.me/6282196735747" target="_blank"><i class="fab fa-whatsapp"></i> +62
                    821-9673-5747</a>
            </div>

        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $BASE ?>/js/simanis.js?v=<?= $version ?>"></script>
</body>

</html>