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
      <a onclick="getElementById('close-menu').checked = false;" href="#home">SiCakap</a>
    </h1>
    <nav>
      <ul onclick="getElementById('close-menu').checked = false;">
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
      <p>Sistem Cerdas Arsip dan Rekapitulasi Konten Publikasi. Platform terintegrasi untuk mengelola dan merekap konten digital, termasuk berita, media sosial, dan dokumentasi kegiatan. 
        Dapatkan insight mendalam tentang aktivitas konten Anda dengan dashboard yang informatif dan mudah digunakan.</p>
    </div>

    <div class="main-header-logo">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 600">
        <!-- Professional gradients -->
        <defs>
          <linearGradient id="newsGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#4CAF50;stop-opacity:0.95" />
            <stop offset="100%" style="stop-color:#2E7D32;stop-opacity:0.95" />
          </linearGradient>
          <linearGradient id="socialGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#2196F3;stop-opacity:0.95" />
            <stop offset="100%" style="stop-color:#1565C0;stop-opacity:0.95" />
          </linearGradient>
          <linearGradient id="docGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#FF9800;stop-opacity:0.95" />
            <stop offset="100%" style="stop-color:#E65100;stop-opacity:0.95" />
          </linearGradient>
          <linearGradient id="archiveGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#9C27B0;stop-opacity:0.95" />
            <stop offset="100%" style="stop-color:#6A1B9A;stop-opacity:0.95" />
          </linearGradient>
          <linearGradient id="reportGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#FF5722;stop-opacity:0.95" />
            <stop offset="100%" style="stop-color:#D84315;stop-opacity:0.95" />
          </linearGradient>
          <linearGradient id="dashboardGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#607D8B;stop-opacity:0.9" />
            <stop offset="100%" style="stop-color:#37474F;stop-opacity:0.9" />
          </linearGradient>
          <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
            <feDropShadow dx="2" dy="4" stdDeviation="3" flood-color="#000000" flood-opacity="0.3"/>
          </filter>
        </defs>
        
        <!-- Subtle background elements -->
        <circle cx="150" cy="100" r="3" fill="#6c63ff" opacity="0.15"/>
        <circle cx="700" cy="150" r="4" fill="#6c63ff" opacity="0.12"/>
        <circle cx="650" cy="500" r="2" fill="#6c63ff" opacity="0.18"/>
        <circle cx="100" cy="450" r="3" fill="#6c63ff" opacity="0.1"/>
        <circle cx="300" cy="80" r="2" fill="#6c63ff" opacity="0.08"/>
        <circle cx="500" cy="120" r="2" fill="#6c63ff" opacity="0.1"/>
        
        <!-- BERITA Section - Professional Design -->
        <g transform="translate(200, 200)">
          <!-- Main news block -->
          <rect x="-60" y="-40" width="120" height="80" rx="12" fill="url(#newsGradient)" stroke="#2E7D32" stroke-width="2" filter="url(#shadow)"/>
          
          <!-- Professional Newspaper -->
          <g transform="translate(-40, -20)">
            <!-- Newspaper shadow -->
            <rect x="-13" y="-7" width="26" height="18" rx="2" fill="#000" opacity="0.2"/>
            <!-- Newspaper base -->
            <rect x="-15" y="-10" width="30" height="20" rx="2" fill="#FFFFFF"/>
            <!-- Newspaper header -->
            <rect x="-12" y="-8" width="24" height="4" rx="1" fill="#2E7D32"/>
            <text x="0" y="-5" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="5" font-weight="bold">HUMAS NEWS</text>
            <!-- Newspaper content -->
            <rect x="-12" y="-3" width="6" height="1" fill="#424242" opacity="0.8"/>
            <rect x="-4" y="-3" width="8" height="1" fill="#424242" opacity="0.8"/>
            <rect x="6" y="-3" width="6" height="1" fill="#424242" opacity="0.8"/>
            <rect x="-12" y="-1" width="10" height="1" fill="#424242" opacity="0.6"/>
            <rect x="-1" y="-1" width="8" height="1" fill="#424242" opacity="0.6"/>
            <rect x="-12" y="1" width="7" height="1" fill="#424242" opacity="0.6"/>
            <rect x="-3" y="1" width="9" height="1" fill="#424242" opacity="0.6"/>
            <rect x="6" y="1" width="6" height="1" fill="#424242" opacity="0.6"/>
            <!-- Newspaper fold -->
            <path d="M -15 -10 L -15 10 M 15 -10 L 15 10" stroke="#E0E0E0" stroke-width="0.5"/>
          </g>
          
          <!-- Professional News Portal -->
          <g transform="translate(20, -20)">
            <!-- Monitor shadow -->
            <rect x="-10" y="-6" width="20" height="14" rx="2" fill="#000" opacity="0.2"/>
            <!-- Monitor frame -->
            <rect x="-12" y="-8" width="24" height="16" rx="3" fill="#212121"/>
            <!-- Screen -->
            <rect x="-10" y="-6" width="20" height="12" rx="2" fill="#1976D2"/>
            <!-- Screen content -->
            <rect x="-9" y="-5" width="18" height="2" fill="#FFFFFF" opacity="0.9"/>
            <text x="0" y="-3" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="4" font-weight="bold">BREAKING NEWS</text>
            <rect x="-9" y="-2" width="16" height="1" fill="white" opacity="0.7"/>
            <rect x="-9" y="0" width="14" height="1" fill="white" opacity="0.7"/>
            <rect x="-9" y="2" width="12" height="1" fill="white" opacity="0.7"/>
            <!-- Monitor base -->
            <rect x="-6" y="6" width="12" height="2" rx="1" fill="#424242"/>
            <rect x="-4" y="8" width="8" height="1" fill="#616161"/>
          </g>
          
          <text x="0" y="55" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">BERITA</text>
        </g>
        
        <!-- MEDIA SOSIAL Section - Professional Design -->
        <g transform="translate(400, 180)">
          <!-- Main social media block -->
          <rect x="-60" y="-40" width="120" height="80" rx="12" fill="url(#socialGradient)" stroke="#1565C0" stroke-width="2" filter="url(#shadow)"/>
          
          <!-- Professional Social Media Icons -->
          <!-- TikTok -->
          <g transform="translate(-35, -25)">
            <rect x="-6" y="-6" width="12" height="12" rx="2" fill="#000000"/>
            <rect x="-5" y="-5" width="10" height="10" rx="1" fill="#FF0050"/>
            <text x="0" y="1" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="6" font-weight="bold">TT</text>
          </g>
          
          <!-- YouTube -->
          <g transform="translate(-15, -25)">
            <rect x="-6" y="-6" width="12" height="12" rx="2" fill="#FF0000"/>
            <polygon points="-2,-3 2,0 -2,3" fill="white"/>
            <rect x="-1" y="-2" width="2" height="4" fill="#FF0000"/>
          </g>
          
          <!-- Facebook -->
          <g transform="translate(5, -25)">
            <rect x="-6" y="-6" width="12" height="12" rx="2" fill="#1877F2"/>
            <text x="0" y="1" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="8" font-weight="bold">f</text>
            <rect x="-1" y="-3" width="2" height="8" fill="white"/>
            <rect x="-3" y="-1" width="4" height="2" fill="white"/>
          </g>
          
          <!-- Twitter/X -->
          <g transform="translate(25, -25)">
            <rect x="-6" y="-6" width="12" height="12" rx="2" fill="#000000"/>
            <path d="M -3 -3 L 3 3 M 3 -3 L -3 3" stroke="white" stroke-width="1.5"/>
          </g>
          
          <!-- Instagram -->
          <g transform="translate(35, -25)">
            <rect x="-6" y="-6" width="12" height="12" rx="2" fill="#E4405F"/>
            <circle cx="0" cy="0" r="3" fill="none" stroke="white" stroke-width="1"/>
            <circle cx="2" cy="-2" r="1" fill="white"/>
            <rect x="-1" y="-1" width="2" height="1" fill="white"/>
          </g>
          
          <text x="0" y="55" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">MEDIA SOSIAL</text>
        </g>
        
        <!-- DOKUMENTASI Section - Professional Design -->
        <g transform="translate(300, 350)">
          <!-- Main documentation block -->
          <rect x="-60" y="-40" width="120" height="80" rx="12" fill="url(#docGradient)" stroke="#E65100" stroke-width="2" filter="url(#shadow)"/>
          
          <!-- Professional Camera -->
          <g transform="translate(-25, -15)">
            <!-- Camera shadow -->
            <rect x="-8" y="-6" width="16" height="8" rx="2" fill="#000" opacity="0.2"/>
            <!-- Camera body -->
            <rect x="-10" y="-8" width="20" height="10" rx="3" fill="#212121"/>
            <rect x="-9" y="-7" width="18" height="8" rx="2" fill="#424242"/>
            <!-- Camera lens -->
            <circle cx="0" cy="-2" r="4" fill="#000000"/>
            <circle cx="0" cy="-2" r="3" fill="#333333"/>
            <circle cx="0" cy="-2" r="2" fill="#555555"/>
            <!-- Camera flash -->
            <rect x="6" y="-6" width="3" height="2" rx="1" fill="#FFD700"/>
            <!-- Camera viewfinder -->
            <rect x="-6" y="-6" width="4" height="3" rx="1" fill="#000000"/>
            <!-- Camera strap -->
            <path d="M -10 -8 Q -14 -12 -10 -16 Q -6 -12 -10 -8" stroke="#8B4513" stroke-width="2" fill="none"/>
            <path d="M 10 -8 Q 14 -12 10 -16 Q 6 -12 10 -8" stroke="#8B4513" stroke-width="2" fill="none"/>
            <!-- Camera grip -->
            <rect x="-12" y="-2" width="3" height="6" rx="1" fill="#333333"/>
          </g>
          
          <!-- Professional Photo Frames -->
          <g transform="translate(15, -20)">
            <!-- Photo 1 -->
            <rect x="-6" y="-4" width="12" height="8" rx="1" fill="#BDBDBD" opacity="0.3"/>
            <rect x="-7" y="-5" width="10" height="6" rx="1" fill="#FFFFFF"/>
            <rect x="-5" y="-3" width="6" height="2" fill="#4CAF50" opacity="0.8"/>
            <rect x="-3" y="-1" width="2" height="1" fill="#2196F3" opacity="0.8"/>
            <path d="M -7 -5 L -5 -5 L -5 -3 L -7 -3 Z" fill="#E0E0E0"/>
          </g>
          
          <g transform="translate(30, -15)">
            <!-- Photo 2 -->
            <rect x="-5" y="-3" width="10" height="7" rx="1" fill="#BDBDBD" opacity="0.3"/>
            <rect x="-6" y="-4" width="8" height="5" rx="1" fill="#FFFFFF"/>
            <rect x="-4" y="-2" width="4" height="1" fill="#FF9800" opacity="0.8"/>
            <rect x="-2" y="0" width="2" height="1" fill="#9C27B0" opacity="0.8"/>
            <path d="M -6 -4 L -4 -4 L -4 -2 L -6 -2 Z" fill="#E0E0E0"/>
          </g>
          
          <!-- Video Camera -->
          <g transform="translate(40, -10)">
            <rect x="-5" y="-3" width="10" height="6" rx="1" fill="#333333"/>
            <rect x="-4" y="-2" width="8" height="4" rx="1" fill="#1976D2"/>
            <circle cx="0" cy="0" r="1.5" fill="white"/>
            <circle cx="0" cy="0" r="1" fill="#1976D2"/>
            <circle cx="2" cy="-1" r="0.5" fill="#F44336"/>
          </g>
          
          <text x="0" y="55" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">DOKUMENTASI</text>
        </g>
        
        <!-- Dashboard Section - Professional Design -->
        <rect x="500" y="300" width="200" height="150" rx="12" fill="url(#dashboardGradient)" stroke="#37474F" stroke-width="2" filter="url(#shadow)"/>
        <text x="600" y="330" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">DASHBOARD ANALISIS</text>
        
        <!-- Professional Chart -->
        <rect x="520" y="360" width="20" height="60" fill="#4CAF50" rx="2"/>
        <rect x="550" y="380" width="20" height="40" fill="#2196F3" rx="2"/>
        <rect x="580" y="350" width="20" height="70" fill="#FF9800" rx="2"/>
        <rect x="610" y="370" width="20" height="50" fill="#9C27B0" rx="2"/>
        
        <!-- Professional Humas Team -->
        <g transform="translate(100, 300)">
          <!-- Photographer -->
          <circle cx="0" cy="-20" r="16" fill="#FFB6C1"/>
          <!-- Hair -->
          <path d="M -12 -35 Q -8 -40 -4 -35 Q 0 -40 4 -35 Q 8 -40 12 -35 Q 8 -30 4 -35 Q 0 -30 -4 -35 Q -8 -30 -12 -35" fill="#8B4513"/>
          <!-- Body -->
          <rect x="-9" y="-5" width="18" height="28" rx="4" fill="#4169E1"/>
          <rect x="-7" y="-3" width="14" height="8" rx="2" fill="#1E90FF"/>
          <rect x="-7" y="23" width="14" height="22" rx="2" fill="#2E8B57"/>
          
          <!-- Professional camera -->
          <g transform="translate(10, 8)">
            <rect x="-5" y="-4" width="10" height="8" rx="2" fill="#212121"/>
            <rect x="-4" y="-3" width="8" height="6" rx="1" fill="#424242"/>
            <circle cx="0" cy="0" r="2" fill="#000000"/>
            <circle cx="0" cy="0" r="1" fill="#333333"/>
            <rect x="3" y="-2" width="2" height="1" rx="0.5" fill="#FFD700"/>
            <path d="M -5 -4 Q -8 -7 -5 -10 Q -2 -7 -5 -4" stroke="#8B4513" stroke-width="1" fill="none"/>
          </g>
          
          <!-- Press badge -->
          <rect x="-3" y="-15" width="6" height="4" rx="1" fill="#FFD700"/>
          <text x="0" y="-12" text-anchor="middle" fill="#000" font-family="Arial, sans-serif" font-size="3" font-weight="bold">PRESS</text>
        </g>
        
        <g transform="translate(200, 320)">
          <!-- Government Official -->
          <circle cx="0" cy="-20" r="16" fill="#FFE4B5"/>
          <!-- Hair -->
          <path d="M -12 -35 Q -8 -40 -4 -35 Q 0 -40 4 -35 Q 8 -40 12 -35 Q 8 -30 4 -35 Q 0 -30 -4 -35 Q -8 -30 -12 -35" fill="#654321"/>
          <!-- Body -->
          <rect x="-9" y="-5" width="18" height="28" rx="4" fill="#DC143C"/>
          <rect x="-7" y="-3" width="14" height="8" rx="2" fill="#B22222"/>
          <rect x="-7" y="23" width="14" height="22" rx="2" fill="#2E8B57"/>
          
          <!-- Official hat -->
          <rect x="-7" y="-37" width="14" height="10" rx="3" fill="#000000"/>
          <rect x="-5" y="-27" width="10" height="3" fill="#FFD700"/>
          <circle cx="0" cy="-25" r="2" fill="#FFD700"/>
          <text x="0" y="-24" text-anchor="middle" fill="#000" font-family="Arial, sans-serif" font-size="2" font-weight="bold">★</text>
          
          <!-- Official badge -->
          <rect x="-2" y="-12" width="4" height="3" rx="1" fill="#FFD700"/>
          <text x="0" y="-10" text-anchor="middle" fill="#000" font-family="Arial, sans-serif" font-size="2" font-weight="bold">ID</text>
        </g>
        
        <!-- Professional Connection Lines -->
        <path d="M 200 240 Q 300 200 400 220" stroke="#6c63ff" stroke-width="3" fill="none" opacity="0.6" stroke-dasharray="5,5"/>
        <path d="M 400 260 Q 350 300 300 350" stroke="#6c63ff" stroke-width="3" fill="none" opacity="0.6" stroke-dasharray="5,5"/>
        <path d="M 300 390 Q 400 350 500 375" stroke="#6c63ff" stroke-width="3" fill="none" opacity="0.6" stroke-dasharray="5,5"/>
        
        <!-- ARSIP DIGITAL - Professional Design -->
        <g transform="translate(600, 100)">
          <rect x="-25" y="-20" width="50" height="35" rx="5" fill="url(#archiveGradient)" stroke="#6A1B9A" stroke-width="2" filter="url(#shadow)"/>
          <rect x="-20" y="-15" width="40" height="25" rx="3" fill="#FFFFFF"/>
          
          <!-- Archive shelves -->
          <rect x="-18" y="-12" width="36" height="3" fill="#9C27B0" opacity="0.7"/>
          <rect x="-18" y="-6" width="36" height="3" fill="#9C27B0" opacity="0.7"/>
          <rect x="-18" y="0" width="36" height="3" fill="#9C27B0" opacity="0.7"/>
          
          <!-- Professional documents -->
          <rect x="-15" y="-10" width="8" height="2" fill="#4CAF50" opacity="0.8"/>
          <rect x="-5" y="-10" width="8" height="2" fill="#2196F3" opacity="0.8"/>
          <rect x="5" y="-10" width="8" height="2" fill="#FF9800" opacity="0.8"/>
          
          <rect x="-15" y="-4" width="8" height="2" fill="#9C27B0" opacity="0.8"/>
          <rect x="-5" y="-4" width="8" height="2" fill="#E91E63" opacity="0.8"/>
          <rect x="5" y="-4" width="8" height="2" fill="#00BCD4" opacity="0.8"/>
          
          <rect x="-15" y="2" width="8" height="2" fill="#795548" opacity="0.8"/>
          <rect x="-5" y="2" width="8" height="2" fill="#607D8B" opacity="0.8"/>
          <rect x="5" y="2" width="8" height="2" fill="#FF5722" opacity="0.8"/>
          
          <text x="0" y="25" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="14" font-weight="bold">ARSIP DIGITAL</text>
          
          <!-- Connection lines -->
          <path d="M 575 90 Q 550 50 400 220" stroke="#9C27B0" stroke-width="2" fill="none" opacity="0.4" stroke-dasharray="3,3"/>
          <path d="M 575 90 Q 500 60 300 350" stroke="#9C27B0" stroke-width="2" fill="none" opacity="0.4" stroke-dasharray="3,3"/>
        </g>
        
        <!-- LAPORAN ANALISIS - Professional Design -->
        <g transform="translate(650, 200)">
          <rect x="-20" y="-25" width="40" height="30" rx="4" fill="url(#reportGradient)" stroke="#D84315" stroke-width="2" filter="url(#shadow)"/>
          <rect x="-15" y="-20" width="30" height="20" rx="2" fill="#FFFFFF"/>
          
          <!-- Report header -->
          <rect x="-12" y="-18" width="24" height="3" fill="#FF5722" opacity="0.8"/>
          
          <!-- Report content -->
          <rect x="-12" y="-13" width="20" height="1" fill="#666" opacity="0.6"/>
          <rect x="-12" y="-11" width="18" height="1" fill="#666" opacity="0.6"/>
          <rect x="-12" y="-9" width="22" height="1" fill="#666" opacity="0.6"/>
          <rect x="-12" y="-7" width="16" height="1" fill="#666" opacity="0.6"/>
          
          <!-- Professional chart -->
          <rect x="-10" y="-4" width="3" height="8" fill="#4CAF50" opacity="0.8"/>
          <rect x="-6" y="-2" width="3" height="6" fill="#2196F3" opacity="0.8"/>
          <rect x="-2" y="-5" width="3" height="9" fill="#FF9800" opacity="0.8"/>
          <rect x="2" y="-3" width="3" height="7" fill="#9C27B0" opacity="0.8"/>
          
          <text x="0" y="15" text-anchor="middle" fill="white" font-family="Arial, sans-serif" font-size="12" font-weight="bold">LAPORAN ANALISIS</text>
          
          <!-- Connection line -->
          <path d="M 630 175 Q 640 150 500 375" stroke="#FF5722" stroke-width="2" fill="none" opacity="0.4" stroke-dasharray="3,3"/>
        </g>
      </svg>
    </div>
  </header>
</section>

<div id="intro" class="section white-background">
  <div class="section-content">
    <div class="text-container">
      <article>
        <h2>Tentang SiCakap</h2>
        <p>SiCakap (Sistem Cerdas Arsip dan Rekapitulasi Konten Publikasi) merupakan sebuah platform digital yang 
          dirancang untuk membantu pengelolaan dan rekapitulasi konten publikasi pada lingkungan kerja Humas Kantor Wilayah Kementerian Hukum Sulawesi Selatan. 
          Sistem ini hadir sebagai solusi untuk mengarsipkan berbagai jenis konten seperti berita, unggahan media sosial, serta dokumentasi kegiatan secara sistematis dan efisien</p>
        <p>Melalui SiCakap, seluruh proses mulai dari input data konten, pengelolaan arsip, 
          hingga pembuatan laporan rekap dapat dilakukan secara terpusat dalam satu sistem. 
          Fitur-fitur utama seperti dashboard informasi, form input konten, rekap konten otomatis, 
          serta jadwal kegiatan dirancang agar pengguna dapat dengan mudah memantau, mengatur, 
          dan mengevaluasi aktivitas publikasi yang telah dilakukan oleh tim Humas. 
          Fitur jadwal kegiatan memungkinkan pegawai untuk melihat dan mengelola agenda Humas secara terstruktur, 
          lengkap dengan tanggal dan keterangan kegiatan, sehingga koordinasi dan dokumentasi kegiatan dapat
           berjalan lebih optimal</p>
        <p>Selain memudahkan proses dokumentasi dan pelaporan, 
          SiCakap juga berfungsi sebagai upaya digitalisasi arsip publikasi agar data lebih aman, mudah diakses, 
          dan terorganisir dengan baik. Dengan antarmuka yang sederhana namun informatif, 
          sistem ini mendukung peningkatan kinerja Humas dalam menjaga konsistensi publikasi serta 
          transparansi informasi kepada pimpinan dan masyarakat.</p>
      </article>
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
        <div class="dashboard-stats">
          <div class="stat-box">
            <h3>Total Berita</h3>
            <span class="stat-number">150+</span>
          </div>
          <div class="stat-box">
            <h3>Postingan Medsos</h3>
            <span class="stat-number">300+</span>
          </div>
          <div class="stat-box">
            <h3>Total Arsip</h3>
            <span class="stat-number">450+</span>
          </div>
        </div>
        
        <div class="dashboard-features">
          <div class="feature-item">
            <h4>📊 Statistik Real-time</h4>
            <p>Pantau perkembangan konten dengan statistik yang selalu update</p>
          </div>
          <div class="feature-item">
            <h4>📝 Log Aktivitas</h4>
            <p>Lacak semua aktivitas pengguna dengan sistem log yang komprehensif</p>
          </div>
          <div class="feature-item">
            <h4>🔍 Analisis Mendalam</h4>
            <p>Dapatkan insight mendalam tentang tren dan pola konten</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="gallery-foto" class="section white-background">
  <div class="section-content portfolio">
    <div class="full-height ">
      <header class="section-header">
        <h2>Gallery Foto</h2>
        <p>Koleksi foto dokumentasi kegiatan dan konten visual.</p>
      </header>

      <div class="grid-section">
        <div class="gallery-images">
          <img src="https://source.unsplash.com/random/800x800?nature&sig=1" alt="Dokumentasi Kegiatan" />
        </div>
        <div class="gallery-images">
          <img src="https://source.unsplash.com/random/900x900?business&sig=2" alt="Presentasi" />
        </div>
        <div class="gallery-images">
          <img src="https://source.unsplash.com/random/500x500?meeting&sig=3" alt="Rapat" />
        </div>
        <div class="gallery-images">
          <img src="https://source.unsplash.com/random/801x801?office&sig=4" alt="Kantor" />
        </div>
        <div class="gallery-images">
          <img src="https://source.unsplash.com/random/904x904?team&sig=5" alt="Tim" />
        </div>
        <div class="gallery-images">
          <img src="https://source.unsplash.com/random/505x505?event&sig=6" alt="Event" />
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

</body>
</html>