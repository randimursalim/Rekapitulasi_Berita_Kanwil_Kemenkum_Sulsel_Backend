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
    <title>Landing Page - SiCakap</title>
    <link rel="icon" type="image/png" href="<?= $BASE ?>/Images/aset_landing.webp">
    <!-- Cache busting: update version number when CSS/JS changes -->
    <?php $version = '1.0.5'; // Update this number when you make CSS/JS changes ?>
    <link rel="stylesheet" href="<?= $BASE ?>/css/landing.css?v=<?= $version ?>">
    <link rel="stylesheet" href="<?= $BASE ?>/vendor/fontawesome/css/all.min.css">


</head>
<body>
<div class="top-menu-space"></div>

<label class="not-visible" for="close-menu">Close Menu</label>
<input class="close-menu" type="checkbox" id="close-menu" role="button" aria-label="Close Menu">

<aside class="menu">
  <div>
    <h1 class="logo">
      <a href="#home">SiCakap</a>
    </h1>
    <nav>
      <ul>
        <li>
          <a href="#intro">Intro</a>
        </li>
        <li>
          <a href="#dashboard">Dashboard</a>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle">Gallery <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown-menu">
            <li><a href="#gallery-foto">Gallery Foto</a></li>
            <li><a href="#gallery-video">Gallery Video</a></li>
          </ul>
        </li>
        <li>
          <a href="#portal-berita">Portal Berita</a>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle">Layanan Kanwil Sulsel<i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown-menu">
            <li><a href="#chatbot">Chatbot</a></li>
            <li><a href="#layanan-pengaduan">Layanan Pengaduan</a></li>
            <li><a href="#data-harmonisasi">Data Harmonisasi</a></li>
            <li><a href="https://simanis.kemenkumsulsel.id/" target="_blank">Simanis</a></li>
            <li><a href="<?= $BASE ?>/index.php?page=simtamu">Simtamu</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle">Jadwal <i class="fas fa-chevron-down"></i></a>
          <ul class="dropdown-menu">
            <li><a href="#jadwal-kegiatan">Jadwal Kegiatan</a></li>
            <li><a href="#jadwal-peminjaman-ruangan">Jadwal Peminjaman Ruangan</a></li>
          </ul>
        </li>
        <li>
          <a href="index.php?page=login" class="nav-login">Login</a>
        </li>
      </ul>
    </nav>
  </div>
</aside>

<section class="section primary-background bg-svg-1">
  <div id="home"></div>
  <header id="main-header" class="main-header section-content">
    <div class="main-header-content">
      <h2>SiCakap
      </h2>
      <p>Sistem Cerdas Arsip dan Pelayanan Publik.</p>
    </div>

    <div class="main-header-logo">
      <img src="<?= $BASE ?>/Images/aset_landing.png" alt="Ilustrasi SiCakap - Rekapitulasi Konten Humas" class="hero-illustration" />
    </div>
  </header>
</section>

<div id="intro" class="section white-background">
  <div class="section-content">
    <div class="intro-container">
      <div class="intro-text">
      <article>
          <h2>Tentang SiCakap</h2>
          <p>SiCakap merupakan platform digital yang dikembangkan untuk mendukung pengelolaan arsip 
            dan pelayanan publik di lingkungan Kantor Wilayah Kementerian Hukum Sulawesi Selatan. 
            Sistem ini mengintegrasikan berbagai kebutuhan kerja seperti pengarsipan konten publikasi, 
            layanan pengaduan, data harmonisasi, serta peminjaman ruangan dalam satu aplikasi terpusat.</p>
          <p>Melalui SiCakap, proses input data, pengelolaan arsip, pemantauan layanan, 
            hingga rekap laporan dapat dilakukan secara lebih cepat dan efisien. 
            Fitur inti seperti dashboard informasi, form input konten, daftar aduan, 
            rekap harmonisasi, dan jadwal kegiatan membantu pegawai mengatur aktivitas kerja 
            secara terstruktur dan terdokumentasi.</p>
          <p>Dengan antarmuka sederhana dan akses yang terpusat, 
            SiCakap hadir sebagai langkah digitalisasi untuk menghadirkan arsip yang aman, 
            pelayanan publik yang lebih responsif, 
            serta transparansi informasi bagi pimpinan maupun masyarakat.</p>
      </article>
      </div>
      <div class="intro-image">
        <img src="<?= $BASE ?>/Images/mockup_sicakap2nobg.png" alt="Mockup SiCakap Dashboard" class="mockup-image">
      </div>
    </div>
  </div>
</div>

<section id="dashboard" class="section primary-background bg-svg-1">

  <div class="section-content portfolio">
    <div class="full-height ">
      <header class="section-header">
        <h2>Dashboard Interaktif</h2>
        <p>Pantau aktivitas konten dengan dashboard yang informatif dan real-time.</p>
      </header>

      <div class="dashboard-preview">
        <!-- Decorative Elements -->
        <div class="dashboard-decoration">
          <div class="floating-element element-1"></div>
          <div class="floating-element element-2"></div>
          <div class="floating-element element-3"></div>
        </div>
        
        <div class="dashboard-stats">
          <div class="stat-box">
            <div class="stat-icon">
              <img src="<?= $BASE ?>/Images/newspaper_bg.gif" alt="Newspaper Icon" class="icon-gif">
            </div>
            <div class="stat-content">
              <span class="stat-number" id="total-berita">-</span>
              <span class="stat-label">Total Berita</span>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon">
              <img src="<?= $BASE ?>/Images/post_bg.gif" alt="Social Media Post Icon" class="icon-gif">
            </div>
            <div class="stat-content">
              <span class="stat-number" id="total-medsos">-</span>
              <span class="stat-label">Postingan Medsos</span>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon">
              <img src="<?= $BASE ?>/Images/arsip_nobg.gif" alt="Archive Icon" class="icon-gif">
            </div>
            <div class="stat-content">
              <span class="stat-number" id="total-arsip">-</span>
              <span class="stat-label">Total Arsip</span>
            </div>
          </div>
        </div>
        
        <div class="dashboard-features">
          <div class="feature-item">
            <div class="feature-icon">
              <i class="uil uil-chart-bar"></i>
            </div>
            <div class="feature-content">
              <h3>Statistik Real-time</h3>
              <p>Pantau perkembangan konten dengan statistik yang selalu update</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon">
              <i class="uil uil-history"></i>
            </div>
            <div class="feature-content">
              <h3>Log Aktivitas</h3>
              <p>Lacak semua aktivitas pengguna dengan sistem log yang komprehensif</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Gallery Foto Section -->
<section id="gallery-foto" class="section white-background">
  <div class="section-content portfolio">
    <div class="full-height">
      <header class="section-header">
        <h2>Gallery Foto</h2>
        <p>Koleksi foto dokumentasi kegiatan dari tim humas kanwil kemenkum sulsel</p>
      </header>

      <div class="gallery-container">
        <div class="gallery-grid" id="galleryGrid">
          <!-- Photos will be loaded dynamically -->
          <div class="gallery-loading">
            <p>Memuat foto...</p>
          </div>
        </div>
        </div>
        </div>
        </div>
</section>

<!-- Portal Berita Section -->
<section id="portal-berita" class="section primary-background bg-svg-1">
  <div class="section-content portfolio">
    <div class="full-height">
      <header class="section-header">
        <h2>Portal Berita</h2>
        <p>Berita terkini dan informasi terbaru dari Humas Kanwil Kemenkum Sulsel</p>
      </header>

      <div class="news-portal-container">
        <div class="news-navigation">
          <button class="news-nav-btn" id="prevBtn" onclick="scrollNews('left')">
            ‹
          </button>
          <button class="news-nav-btn" id="nextBtn" onclick="scrollNews('right')">
            ›
          </button>
        </div>
        
        <div class="news-grid" id="newsGrid">
          <div class="news-loading">
            <div class="loading-spinner"></div>
            <p>Memuat berita...</p>
        </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Gallery Video Section -->
<section id="gallery-video" class="section white-background">
  <div class="section-content portfolio">
    <div class="full-height ">
      <header class="section-header">
        <h2>Gallery Video</h2>
        <p>Koleksi video dokumentasi kegiatan dan konten multimedia.</p>
      </header>

      <div class="video-gallery-container">
        <div class="video-gallery-grid" id="videoGalleryGrid">
          <!-- Videos will be loaded dynamically -->
          <div class="video-gallery-loading">
            <p>Memuat video...</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Chatbot Section -->
<section id="chatbot" class="section primary-background bg-svg-1">
  <div class="section-content portfolio">
    <div class="full-height ">
      <header class="section-header">
        <h2>Chatbot Layanan Informasi</h2>
        <p>Layanan chatbot untuk informasi mengenai layanan di kantor wilayah kemenkum sulsel.</p>
      </header>

      <div class="chatbot-preview">
        <div class="chatbot-features">
          <div class="feature-card">
            <div class="feature-icon">🤖</div>
            <h3>Chatbot 24/7</h3>
            <p>Layanan chatbot yang tersedia 24 jam untuk membantu pengguna</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">📝</div>
            <h3>Informasi Online</h3>
            <p>Sistem informasi online yang mudah dan cepat</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">💬</div>
            <h3>Bantuan Langsung</h3>
            <p>Dapatkan bantuan langsung untuk informasi yang dibutuhkan</p>
          </div>
        </div>
        
        <div class="chatbot-demo">
          <div class="chat-window">
            <div class="chat-header">
              <h4>Chatbot Layanan Informasi</h4>
              <span class="status-indicator">Online</span>
            </div>
            <div class="chat-messages">
              <div class="message bot">
                <p>Halo! Saya siap membantu Anda. Ada yang bisa saya bantu?</p>
              </div>
              <div class="message user">
                <p>Saya ingin mengetahui informasi terbaru tentang layanan di kantor wilayah kemenkum sulsel.</p>
        </div>
              <div class="message bot">
                <p>Baik, saya akan membantu Anda. Bisa ceritakan lebih detail informasi yang ingin Anda tanyakan?</p>
        </div>
        </div>
            <div class="chat-input">
              <input type="text" placeholder="Ketik pesan Anda..." >
              <a href="https://wa.me/6285787028737" target="_blank" rel="noopener noreferrer" class="chat-button-kirim">Kirim</a>
        </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Jadwal Kegiatan Section -->
<section id="jadwal-kegiatan" class="section white-background">
  <div class="section-content portfolio">
    <div class="full-height">
      <header class="section-header">
        <h2>Jadwal Kegiatan</h2>
        <p>Jadwal kegiatan dan agenda terbaru dari Kemenkum Sulsel</p>
      </header>

      <div class="schedule-container">
        <div class="schedule-timeline" id="scheduleTimeline">
          <!-- Schedule items will be loaded dynamically -->
          <div class="schedule-loading">
            <p>Memuat jadwal kegiatan...</p>
          </div>
    </div>

        <div class="schedule-actions">
          <a href="index.php?page=jadwal-kegiatan" class="btn-view-all">
            Lihat Semua Jadwal
          </a>
            </div>
            </div>
      
      <!-- Schedule Detail Modal -->
      <div id="scheduleModal" class="schedule-modal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">Detail Jadwal</h3>
            <button class="modal-close" id="modalClose">&times;</button>
            </div>
          <div class="modal-body" id="modalBody">
            <!-- Content will be populated dynamically -->
            </div>
            </div>
      </div>
    </div>
  </div>
</section>

<!-- Layanan Pengaduan Section -->
<section id="layanan-pengaduan" class="section primary-background bg-svg-1">
  <div class="section-content portfolio">
    <div class="full-height">
      <header class="section-header">
        <h2>Layanan Pengaduan</h2>
        <p>Layanan pengaduan untuk masyarakat mengenai layanan di kantor wilayah kemenkum sulsel</p>
      </header>

      <div class="layanan-pengaduan-container">
        <div class="layanan-pengaduan-features">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-gavel"></i>
            </div>
            <h3>Layanan Pengaduan Online</h3>
            <p>Sampaikan pengaduan Anda secara online dengan mudah dan cepat</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h3>Terjamin Keamanannya</h3>
            <p>Data pengaduan Anda terjaga kerahasiaannya dan diproses dengan profesional</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-clock"></i>
            </div>
            <h3>Respon Cepat</h3>
            <p>Tim kami akan merespon pengaduan Anda dengan segera</p>
          </div>
        </div>
        
        <div class="layanan-pengaduan-actions">
          <a href="index.php?page=tambah-layanan-pengaduan-masyarakat" class="btn-view-all" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-bullhorn"></i> Ajukan Pengaduan
          </a>
          <a href="index.php?page=tracking-layanan-pengaduan" class="btn-view-all" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-search"></i> Tracking Penyelesaian Pengaduan
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Data Harmonisasi Section -->
<section id="data-harmonisasi" class="section white-background">
  <div class="section-content portfolio">
    <div class="full-height">
      <header class="section-header">
        <h2>Data Harmonisasi</h2>
        <p>Informasi data harmonisasi rancangan peraturan terbaru</p>
      </header>

      <div class="harmonisasi-preview-container">
        <div class="harmonisasi-table-wrapper" id="harmonisasiPreview">
          <div class="harmonisasi-loading">
            <p>Memuat data harmonisasi...</p>
          </div>
        </div>

        <div class="harmonisasi-actions">
          <a href="index.php?page=data-harmonisasi-public" class="btn-view-all">
            <i class="fas fa-list"></i> Lihat Semua Data Harmonisasi
          </a>
        </div>
    </div>
  </div>
</div>
    </section>

<!-- Jadwal Peminjaman Ruangan Section -->
<section id="jadwal-peminjaman-ruangan" class="section primary-background bg-svg-1">
  <div class="section-content portfolio">
    <div class="full-height">
      <header class="section-header">
        <h2>Jadwal Peminjaman Ruangan</h2>
        <p>Jadwal peminjaman ruangan dan agenda terbaru dari Kemenkum Sulsel</p>
      </header>

      <div class="schedule-container">
        <div class="schedule-timeline" id="schedulePeminjamanTimeline">
          <!-- Schedule items will be loaded dynamically -->
          <div class="schedule-loading">
            <p>Memuat jadwal peminjaman ruangan...</p>
          </div>
    </div>

        <div class="schedule-actions">
          <a href="index.php?page=tambah-peminjaman-ruangan-masyarakat" class="btn-view-all" target="_blank" rel="noopener noreferrer">
            <i class="fas fa-door-open"></i> Pinjam Ruangan
          </a>
            </div>
            </div>
      
      <!-- Schedule Detail Modal -->
      <div id="schedulePeminjamanModal" class="schedule-modal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
          <div class="modal-header">
            <h3 class="modal-title" id="modalPeminjamanTitle">Detail Jadwal</h3>
            <button class="modal-close" id="modalPeminjamanClose">&times;</button>
            </div>
          <div class="modal-body" id="modalPeminjamanBody">
            <!-- Content will be populated dynamically -->
            </div>
            </div>
            </div>
            </div>
          </div>
</section>

<!-- <p class="created-by"><a href="#">© 2025 SiCakap - Humas Kanwil Kemenkum SulSel</a></p> -->

<footer class="footer-section">
  <div class="footer-container">
    <div class="footer-logo">
      <p>© 2025 <strong>SiCakap</strong><br>Humas Kanwil Kemenkum SulSel</p>
    </div>

    <div class="footer-social">
      <a href="https://www.instagram.com/kemenkumsulsel" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      <a href="https://www.tiktok.com/@kemenkumsulsel" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
      <a href="https://www.facebook.com/kemenkumsulsel" target="_blank" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
      <a href="https://www.youtube.com/@kemenkumsulsel" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
      <a href="https://x.com/kemenkumsulsel" target="_blank" aria-label="X"><i class="fab fa-x-twitter"></i></a>
    </div>

    <div class="footer-contact">
      <p>Layanan Pengaduan:</p>
      <a href="https://wa.me/6282196735747" target="_blank"><i class="fab fa-whatsapp"></i> +62 821-9673-5747</a>
    </div>

  </div>
</footer>



<a class="back-to-top" role="button" aria-label="Back to top" title="Back to top" href="#home"></a>

<!-- JavaScript files -->
<!-- Cache busting: update version number when CSS/JS changes -->
<?php if (!isset($version)) $version = '1.0.5'; // Update this number when you make CSS/JS changes ?>
<script>
  // Set BASE_URL untuk JavaScript
  window.BASE_URL = '<?= $BASE ?>';
</script>
<script src="<?= $BASE ?>/js/common.js?v=<?= $version ?>"></script>
<script src="<?= $BASE ?>/js/landing.js?v=<?= $version ?>"></script>

</body>
</html>