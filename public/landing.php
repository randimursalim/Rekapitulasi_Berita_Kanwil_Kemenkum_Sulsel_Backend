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
          <a href="#gallery-video">gallery video</a>
        </li>
        <li>
          <a href="#chatbot">chatbot</a>
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
        <p>Koleksi foto dokumentasi kegiatan oleh humas kanwil sulsel</p>
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

<section id="latest-work" class="section primary-background bg-svg-1">
  <div class="section-content portfolio">
    <div class="full-height ">
      <h2>Recent jobs</h2>

      <div class="grid-section">

        <article>
          <h3>A good one</h3>
          <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quibusdam perferendis nisi omnis culpa eius
            optio
            eos inventore rerum? Harum velit consectetur tempora et numquam distinctio vero exercitationem
            mollitia
            laborum inventore?</p>
        </article>
        <article>
          <h3>Great job</h3>
          <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quibusdam perferendis nisi omnis culpa eius
            optio
            eos inventore rerum? Harum velit consectetur tempora et numquam distinctio vero exercitationem
            mollitia
            laborum inventore?</p>
        </article>
        <article>
          <h3>The best</h3>
          <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Quibusdam perferendis nisi omnis culpa eius
            optio
            eos inventore rerum? Harum velit consectetur tempora et numquam distinctio vero exercitationem
            mollitia
            laborum inventore?</p>
        </article>
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

      <div class="grid-section">
        <div class="video-container">
          <div class="video-placeholder">
            <div class="play-button">▶</div>
            <h4>Dokumentasi Kegiatan</h4>
            <p>Video dokumentasi kegiatan organisasi</p>
          </div>
        </div>
        <div class="video-container">
          <div class="video-placeholder">
            <div class="play-button">▶</div>
            <h4>Presentasi</h4>
            <p>Video presentasi dan seminar</p>
          </div>
        </div>
        <div class="video-container">
          <div class="video-placeholder">
            <div class="play-button">▶</div>
            <h4>Training</h4>
            <p>Video pelatihan dan workshop</p>
          </div>
        </div>
        <div class="video-container">
          <div class="video-placeholder">
            <div class="play-button">▶</div>
            <h4>Event</h4>
            <p>Video dokumentasi event dan acara</p>
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
        <p>Layanan chatbot untuk pengaduan dan bantuan pengguna.</p>
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
            <h3>Pengaduan Online</h3>
            <p>Sistem pengaduan online yang mudah dan cepat</p>
          </div>
          <div class="feature-card">
            <div class="feature-icon">💬</div>
            <h3>Bantuan Langsung</h3>
            <p>Dapatkan bantuan langsung untuk masalah teknis</p>
          </div>
        </div>
        
        <div class="chatbot-demo">
          <div class="chat-window">
            <div class="chat-header">
              <h4>Chatbot Pengaduan</h4>
              <span class="status-indicator">Online</span>
            </div>
            <div class="chat-messages">
              <div class="message bot">
                <p>Halo! Saya siap membantu Anda. Ada yang bisa saya bantu?</p>
              </div>
              <div class="message user">
                <p>Saya mengalami masalah dengan login</p>
              </div>
              <div class="message bot">
                <p>Baik, saya akan membantu Anda. Bisa ceritakan lebih detail masalahnya?</p>
              </div>
            </div>
            <div class="chat-input">
              <input type="text" placeholder="Ketik pesan Anda..." disabled>
              <button disabled>Kirim</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<footer id="footer" class="footer bg-svg-1">
  <div class="main-header section-content">
    <section class="main-header-content">
      <h2>SiCakap</h2>
      <p>Sistem Cerdas Arsip dan Rekapitulasi Konten Publikasi untuk Humas Kantor Wilayah Kementerian Hukum Sulawesi Selatan.</p>
    </section>
  </div>
</footer>

<p class="created-by"><a href="#">© 2025 SiCakap - Kanwil Kemenkum SulSel</a></p>

<a class="back-to-top" role="button" aria-label="Back to top" title="Back to top" href="#home"></a>

<script>
  // Smooth scrolling for navigation links
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      
      // Close mobile menu if open
      const closeMenuCheckbox = document.getElementById('close-menu');
      if (closeMenuCheckbox) {
        closeMenuCheckbox.checked = false;
      }
      
      const target = document.querySelector(this.getAttribute('href'));
      if (target) {
        // Add offset for fixed header
        const headerHeight = document.querySelector('.top-menu-space')?.offsetHeight || 0;
        const targetPosition = target.offsetTop - headerHeight;
        
        window.scrollTo({
          top: targetPosition,
          behavior: 'smooth'
        });
      }
    });
  });

  // Scroll to top functionality
  const scrollToTopBtn = document.querySelector('.back-to-top');
  window.addEventListener('scroll', () => {
    if (window.pageYOffset > 300) {
      scrollToTopBtn.style.display = 'block';
    } else {
      scrollToTopBtn.style.display = 'none';
    }
  });

  scrollToTopBtn.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  // Load dashboard statistics
  async function loadDashboardStats() {
    // Add loading state
    document.getElementById('total-berita').classList.add('loading');
    document.getElementById('total-medsos').classList.add('loading');
    document.getElementById('total-arsip').classList.add('loading');
    
    try {
      const response = await fetch('ajax/dashboard_stats.php');
      const data = await response.json();
      
      if (data.success) {
        // Update stat numbers with animation
        updateStatNumber('total-berita', data.data.total_berita);
        updateStatNumber('total-medsos', data.data.total_medsos);
        updateStatNumber('total-arsip', data.data.total_arsip);
      } else {
        console.error('Error loading dashboard stats:', data.error);
        // Fallback to default values
        updateStatNumber('total-berita', 0);
        updateStatNumber('total-medsos', 0);
        updateStatNumber('total-arsip', 0);
      }
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
      // Fallback to default values
      updateStatNumber('total-berita', 0);
      updateStatNumber('total-medsos', 0);
      updateStatNumber('total-arsip', 0);
    }
  }

  // Animate number counting
  function updateStatNumber(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    // Remove loading state
    element.classList.remove('loading');
    
    const startValue = 0;
    const duration = 2000; // 2 seconds
    const increment = targetValue / (duration / 16); // 60fps
    let currentValue = startValue;
    
    const timer = setInterval(() => {
      currentValue += increment;
      if (currentValue >= targetValue) {
        currentValue = targetValue;
        clearInterval(timer);
      }
      element.textContent = Math.floor(currentValue) + '+';
    }, 16);
  }

  // Load dashboard stats when page loads
  document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    
    // Ensure smooth scrolling works after page load
    setTimeout(() => {
      // Re-initialize smooth scrolling for any dynamically added elements
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        if (!anchor.hasAttribute('data-smooth-scroll')) {
          anchor.setAttribute('data-smooth-scroll', 'true');
        }
      });
    }, 100);
    
    // Load gallery photos
    loadGalleryPhotos();
  });

  // Load gallery photos from database
  async function loadGalleryPhotos() {
    const galleryGrid = document.getElementById('galleryGrid');
    if (!galleryGrid) return;
    
    try {
      console.log('Loading gallery photos...');
      const response = await fetch('ajax/gallery_photos.php');
      const result = await response.json();
      
      console.log('Gallery API Response:', result);
      
      if (result.success && result.data.length > 0) {
        console.log('Rendering database photos:', result.data.length);
        console.log('Photos data:', result.data);
        renderGalleryPhotos(result.data);
      } else {
        console.log('No database photos found, using placeholders');
        console.log('API result:', result);
        renderPlaceholderPhotos();
      }
    } catch (error) {
      console.error('Error loading gallery photos:', error);
      renderPlaceholderPhotos();
    }
  }

  // Render photos from database
  function renderGalleryPhotos(photos) {
    const galleryGrid = document.getElementById('galleryGrid');
    
    console.log('Rendering photos:', photos);
    
    const photosHTML = photos.map((photo, index) => {
      // Handle image path - fix path for storage/uploads
      let imageSrc = photo.image;
      if (!imageSrc.startsWith('http') && !imageSrc.startsWith('/')) {
        // If path starts with storage/uploads, use it directly
        if (imageSrc.startsWith('storage/uploads/')) {
          imageSrc = imageSrc; // Use as is
        } else {
          imageSrc = 'Images/' + imageSrc;
        }
      }
      
      console.log(`Photo ${index}:`, {
        original: photo.image,
        processed: imageSrc,
        title: photo.title
      });
      
      return `
        <div class="gallery-item" data-index="${index}">
          <div class="gallery-image">
            <img src="${imageSrc}" alt="${photo.title}" loading="lazy" onerror="console.error('Image failed to load:', '${imageSrc}'); this.src='https://via.placeholder.com/300x200?text=Image+Not+Found'">
            <div class="gallery-overlay">
              <div class="gallery-info">
                <h3>${photo.title}</h3>
                <p>${photo.type === 'berita' ? 'Berita' : 'Media Sosial'}</p>
                <span class="gallery-date">${formatDate(photo.date)}</span>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');
    
    galleryGrid.innerHTML = photosHTML;
    initializeGalleryInteractions();
  }

  // Render placeholder photos if no database photos
  function renderPlaceholderPhotos() {
    const galleryGrid = document.getElementById('galleryGrid');
    
    const placeholderPhotos = [
      { src: 'https://source.unsplash.com/random/800x800?nature&sig=1', title: 'Dokumentasi Kegiatan' },
      { src: 'https://source.unsplash.com/random/900x900?business&sig=2', title: 'Presentasi' },
      { src: 'https://source.unsplash.com/random/500x500?meeting&sig=3', title: 'Rapat' },
      { src: 'https://source.unsplash.com/random/801x801?office&sig=4', title: 'Kantor' },
      { src: 'https://source.unsplash.com/random/904x904?team&sig=5', title: 'Tim' },
      { src: 'https://source.unsplash.com/random/505x505?event&sig=6', title: 'Event' }
    ];
    
    const photosHTML = placeholderPhotos.map((photo, index) => `
      <div class="gallery-item" data-index="${index}">
        <div class="gallery-image">
          <img src="${photo.src}" alt="${photo.title}" loading="lazy">
          <div class="gallery-overlay">
            <div class="gallery-info">
              <h3>${photo.title}</h3>
              <p>Dokumentasi</p>
            </div>
          </div>
        </div>
      </div>
    `).join('');
    
    galleryGrid.innerHTML = photosHTML;
    initializeGalleryInteractions();
  }

  // Initialize gallery interactions
  function initializeGalleryInteractions() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach((item, index) => {
      // Add staggered animation delay
      item.style.animationDelay = `${index * 0.1}s`;
      
      // Add click event for modal
      item.addEventListener('click', function() {
        const img = this.querySelector('img');
        const title = this.querySelector('h3').textContent;
        showImageModal(img.src, title);
      });
      
      // Add hover effects
      item.addEventListener('mouseenter', function() {
        this.classList.add('hovered');
      });
      
      item.addEventListener('mouseleave', function() {
        this.classList.remove('hovered');
      });
    });
  }

  // Show image modal
  function showImageModal(src, title) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('imageModal');
    if (!modal) {
      modal = document.createElement('div');
      modal.id = 'imageModal';
      modal.className = 'image-modal';
      modal.innerHTML = `
        <div class="modal-content">
          <span class="modal-close">&times;</span>
          <img class="modal-image" src="" alt="">
          <div class="modal-info">
            <h3 class="modal-title"></h3>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
      
      // Add close functionality
      modal.querySelector('.modal-close').addEventListener('click', () => {
        modal.style.display = 'none';
      });
      
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          modal.style.display = 'none';
        }
      });
    }
    
    // Set content and show modal
    modal.querySelector('.modal-image').src = src;
    modal.querySelector('.modal-title').textContent = title;
    modal.style.display = 'flex';
  }

  // Format date helper
  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }
</script>

</body>
</html>