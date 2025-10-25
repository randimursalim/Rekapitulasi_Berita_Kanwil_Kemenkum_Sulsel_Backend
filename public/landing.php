<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <link rel="stylesheet" href="css/landing.css">
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
          <a href="#intro">intro</a>
        </li>
        <li>
          <a href="#dashboard">dashboard</a>
        </li>
        <li>
          <a href="#gallery-foto">gallery foto</a>
        </li>
        <li>
          <a href="#portal-berita">portal berita</a>
        </li>
        <li>
          <a href="#gallery-video">gallery video</a>
        </li>
        <li>
          <a href="#chatbot">chatbot</a>
        </li>
        <li>
          <a href="#jadwal-kegiatan">jadwal kegiatan</a>
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
      <p>Sistem Cerdas Arsip dan Rekapitulasi Konten Publikasi.</p>
    </div>

    <div class="main-header-logo">
      <img src="Images/aset_landing.png" alt="Ilustrasi SiCakap - Rekapitulasi Konten Humas" class="hero-illustration" />
    </div>
  </header>
</section>

<div id="intro" class="section white-background">
  <div class="section-content">
    <div class="intro-container">
      <div class="intro-text">
      <article>
          <h2>Tentang SiCakap</h2>
          <p>SiCakap merupakan platform digital yang dikembangkan untuk mendukung pengelolaan dan rekapitulasi konten publikasi di lingkungan kerja Humas Kantor Wilayah Kementerian Hukum Sulawesi Selatan. 
            Sistem ini berfungsi untuk mengarsipkan berbagai jenis konten seperti berita, unggahan media sosial, 
            dan dokumentasi kegiatan secara sistematis dan efisien.</p>
          <p>Melalui SiCakap, proses input data, pengelolaan arsip, hingga pembuatan laporan rekap dapat dilakukan secara terpusat 
            dalam satu sistem. Fitur utama seperti dashboard informasi, form input konten, rekap konten otomatis, 
            dan jadwal kegiatan membantu pengguna memantau serta mengatur aktivitas publikasi Humas. 
            Fitur jadwal kegiatan juga memudahkan pegawai melihat dan mengelola agenda kerja secara terstruktur 
            lengkap dengan tanggal dan keterangan kegiatan.</p>
          <p>Selain mempermudah dokumentasi dan pelaporan, SiCakap menjadi langkah digitalisasi arsip publikasi agar data lebih aman, 
            mudah diakses, dan terorganisir. Dengan antarmuka yang sederhana namun informatif, 
            sistem ini mendukung peningkatan kinerja Humas dalam menjaga konsistensi publikasi dan transparansi informasi 
            kepada pimpinan serta masyarakat.</p>
      </article>
      </div>
      <div class="intro-image">
        <img src="Images/mockup_sicakap2nobg.png" alt="Mockup SiCakap Dashboard" class="mockup-image">
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
              <img src="Images/newspaper_bg.gif" alt="Newspaper Icon" class="icon-gif">
            </div>
            <div class="stat-content">
              <span class="stat-number" id="total-berita">-</span>
              <span class="stat-label">Total Berita</span>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon">
              <img src="Images/post_bg.gif" alt="Social Media Post Icon" class="icon-gif">
            </div>
            <div class="stat-content">
              <span class="stat-number" id="total-medsos">-</span>
              <span class="stat-label">Postingan Medsos</span>
            </div>
          </div>
          <div class="stat-box">
            <div class="stat-icon">
              <img src="Images/arsip_nobg.gif" alt="Archive Icon" class="icon-gif">
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
              <button>Kirim</button>
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

<p class="created-by"><a href="#">© 2025 SiCakap - Humas Kanwil Kemenkum SulSel</a></p>

<a class="back-to-top" role="button" aria-label="Back to top" title="Back to top" href="#home"></a>

<!-- JavaScript files -->
<script src="js/common.js"></script>
<script src="js/landing.js"></script>

</body>
</html>