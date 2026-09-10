<?php
// app/views/layouts/header.php

// Set basic security headers (safe approach)
if (!headers_sent()) {
  // Basic security headers that are safe
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('X-XSS-Protection: 1; mode=block');
  header('Referrer-Policy: strict-origin-when-cross-origin');

  // HTTPS Security Headers (only if HTTPS)
  if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
  }

  // Remove server signature
  if (function_exists('header_remove')) {
    header_remove('Server');
    header_remove('X-Powered-By');
  }
}

// Auto-detect BASE_URL untuk localhost vs hosting
// Deteksi environment (sederhana, tanpa require config)
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$serverName = $_SERVER['SERVER_NAME'] ?? '';
$httpHost = $_SERVER['HTTP_HOST'] ?? '';

// Cek apakah localhost
$isLocalhost = (
  strpos($serverName, 'localhost') !== false ||
  strpos($serverName, '127.0.0.1') !== false ||
  strpos($httpHost, 'localhost') !== false ||
  strpos($requestUri, '/rekap-konten/public') !== false ||
  strpos($scriptName, '/rekap-konten/public') !== false
);

// Set BASE_URL berdasarkan environment
if ($isLocalhost) {
  // Localhost: gunakan BASE_URL dari config jika sudah defined, atau default
  $BASE = defined('BASE_URL') ? BASE_URL : '/rekap-konten/public';
} else {
  // Production hosting: kosong (handled by .htaccess)
  $BASE = '';
}
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // Halaman default

function is_active($pageName)
{
  global $currentPage;
  return $currentPage === $pageName ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= ucfirst($currentPage) ?> - KEMENKUM SULSEL</title>

  <!-- Favicon -->
  <link rel="icon" type="image/jpeg" href="<?= $BASE ?>/Images/lamaccalogonobg.png">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= $BASE ?>/css/style.css?v=1.0.7" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

  <!-- Set BASE_URL untuk JavaScript -->
  <script>
    window.BASE_URL = '<?= $BASE ?>';

    // Helper function untuk generate image URL (global)
    if (typeof getImageUrl === 'undefined') {
      window.getImageUrl = function (path) {
        if (!path) return null;
        // Jika sudah full URL atau absolute path, return as is
        if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) {
          return path;
        }
        // Jika path relatif (storage/uploads/...), tambahkan BASE URL
        const base = window.BASE_URL || '';
        return base + '/' + path.replace(/^\//, '');
      };
    }
  </script>

  <!-- Fallback CSS untuk scrollbar menu (memastikan scrollbar selalu muncul) -->
  <style>
    /* Fallback untuk scrollbar nav-links - memastikan scrollbar selalu muncul */
    nav .menu-items .nav-links {
      overflow-y: auto !important;
      overflow-x: hidden !important;
      scrollbar-width: thin !important;
      scrollbar-color: #0E4BF1 #f5f5f5 !important;
    }

    nav .menu-items .nav-links::-webkit-scrollbar {
      width: 8px !important;
      display: block !important;
    }

    nav .menu-items .nav-links::-webkit-scrollbar-track {
      background: #f5f5f5 !important;
      border-radius: 4px !important;
    }

    nav .menu-items .nav-links::-webkit-scrollbar-thumb {
      background: #0E4BF1 !important;
      border-radius: 4px !important;
      min-height: 30px !important;
    }

    nav .menu-items .nav-links::-webkit-scrollbar-thumb:hover {
      background: #0A3BC7 !important;
    }

    body.dark nav .menu-items .nav-links {
      scrollbar-color: #666 #3A3B3C !important;
    }

    body.dark nav .menu-items .nav-links::-webkit-scrollbar-track {
      background: #3A3B3C !important;
    }

    body.dark nav .menu-items .nav-links::-webkit-scrollbar-thumb {
      background: #666 !important;
    }

    body.dark nav .menu-items .nav-links::-webkit-scrollbar-thumb:hover {
      background: #777 !important;
    }

    /* Fallback untuk dark mode toggle saat sidebar ditutup */
    nav.close .menu-items .logout-mode li.mode a .link-name,
    nav.close .menu-items .logout-mode li.mode a i,
    nav.close .logout-mode li.mode a .link-name,
    nav.close .logout-mode li.mode a i,
    nav.close .logout-mode li.mode a {
      opacity: 0 !important;
      pointer-events: none !important;
      display: none !important;
      visibility: hidden !important;
    }

    nav.close .menu-items .logout-mode li.mode .mode-toggle,
    nav.close .logout-mode li.mode .mode-toggle,
    nav.close .mode-toggle {
      display: flex !important;
      visibility: visible !important;
      opacity: 1 !important;
      position: static !important;
      right: auto !important;
      left: auto !important;
      margin: 0 auto !important;
    }

    nav.close .menu-items .logout-mode li.mode,
    nav.close .logout-mode li.mode {
      display: flex !important;
      visibility: visible !important;
      opacity: 1 !important;
      justify-content: center !important;
      align-items: center !important;
      height: 50px !important;
      margin-top: 10px !important;
    }
  </style>

  <!-- Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Base URL for JavaScript -->
  <script>
    // Set BASE_URL untuk JavaScript (untuk path dinamis)
    window.BASE_URL = '<?= $BASE ?>';

    // Helper function untuk generate image URL (global)
    if (typeof getImageUrl === 'undefined') {
      window.getImageUrl = function (path) {
        if (!path) return null;
        // Jika sudah full URL atau absolute path, return as is
        if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('/')) {
          return path;
        }
        // Jika path relatif (storage/uploads/...), tambahkan BASE URL
        const base = window.BASE_URL || '';
        return base + '/' + path.replace(/^\//, '');
      };
    }
  </script>

  <!-- Custom Scripts -->
  <script src="<?= $BASE ?>/js/console-suppress.js"></script>
  <script src="<?= $BASE ?>/js/live-search.js"></script>
  <script src="<?= $BASE ?>/js/session-timeout.js"></script>
</head>

<body>
  <nav>
    <div class="logo-name">
      <div class="logo-image">
        <img src="<?= $BASE ?>/Images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" />
      </div>
      <span class="logo_name">KEMENKUM SULSEL</span>
    </div>

    <div class="menu-items">
      <ul class="nav-links">
        <?php
        require_once __DIR__ . '/../../controllers/AuthController.php';
        if (!function_exists('check_menu_permission')) {
            function check_menu_permission($menuKey) {
                return AuthController::hasPermission($menuKey);
            }
        }
        ?>

        <?php if (check_menu_permission('dashboard')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=dashboard" class="<?= is_active('dashboard') ?>"><i
                class="fas fa-home"></i><span class="link-name">Dashboard</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('input-konten')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=input-konten" class="<?= is_active('input-konten') ?>"><i
                class="fas fa-plus-circle"></i><span class="link-name">Input Konten</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('rekap-konten')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=rekap-konten" class="<?= is_active('rekap-konten') ?>"><i
                class="fas fa-database"></i><span class="link-name">Rekap Konten</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('tamu')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=tamu" class="<?= is_active('tamu') ?>"><i class="fas fa-book"></i><span
                class="link-name">Buku Tamu</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('izin')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=izin" class="<?= is_active('izin') ?>"><i class="fas fa-file"></i><span
                class="link-name">Perizinan</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('arsip')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=arsip" class="<?= is_active('arsip') ?>"><i
                class="fas fa-archive"></i><span class="link-name">Arsip</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('layanan-pengaduan')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=layanan-pengaduan" class="<?= is_active('layanan-pengaduan') ?>"><i
                class="fas fa-gavel"></i><span class="link-name">Layanan Pengaduan</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('jadwal-kegiatan')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=jadwal-kegiatan" class="<?= is_active('jadwal-kegiatan') ?>"><i
                class="fas fa-calendar-alt"></i><span class="link-name">Jadwal Kegiatan</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('rekap-jadwal-kegiatan')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=rekap-jadwal-kegiatan"
              class="<?= is_active('rekap-jadwal-kegiatan') ?>"><i class="fas fa-chart-pie"></i><span
                class="link-name">Rekap Jadwal</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('jadwal-peminjaman-ruangan')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=jadwal-peminjaman-ruangan"
              class="<?= is_active('jadwal-peminjaman-ruangan') ?>"><i class="fas fa-door-open"></i><span
                class="link-name">Peminjaman Ruangan</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('harmonisasi')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=harmonisasi" class="<?= is_active('harmonisasi') ?>"><i
                class="fas fa-balance-scale"></i><span class="link-name">Data Harmonisasi</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('rekap-harmonisasi')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=rekap-harmonisasi" class="<?= is_active('rekap-harmonisasi') ?>"><i
                class="fas fa-chart-line"></i><span class="link-name">Rekap Harmonisasi</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('pengguna')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=pengguna" class="<?= is_active('pengguna') ?>"><i
                class="fas fa-users"></i><span class="link-name">Pengguna</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('statistik-pengguna')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=statistik-pengguna" class="<?= is_active('statistik-pengguna') ?>"><i
                class="fas fa-users-cog"></i><span class="link-name">Statistik Pengguna</span></a></li>
        <?php endif; ?>

        <?php if (check_menu_permission('role')): ?>
          <li><a href="<?= $BASE ?>/index.php?page=role" class="<?= is_active('role') ?>"><i
                class="fas fa-user-shield"></i><span class="link-name">Manajemen Role</span></a></li>
        <?php endif; ?>
      </ul>

      <ul class="logout-mode">
        <li><a href="<?= $BASE ?>/index.php?page=logout"><i class="fas fa-sign-out-alt"></i><span
              class="link-name">Logout</span></a></li>
        <li class="mode">
          <a href="#"><i class="fas fa-moon"></i><span class="link-name">Dark Mode</span></a>
          <div class="mode-toggle"><span class="switch"></span></div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Konten utama -->
  <section class="dashboard">
    <?php
    require_once __DIR__ . '/../../helpers/maintenance_helper.php';
    $isMaintenanceActive = MaintenanceHelper::isMaintenanceMode();
    $isAdminUser = MaintenanceHelper::isAdmin();
    ?>

    <div class="top">
      <i class="fas fa-bars sidebar-toggle"></i>

      <?php
      // Tampilkan search box di halaman tertentu
      $showSearch = in_array($currentPage, ['arsip', 'jadwal-kegiatan', 'jadwal-peminjaman-ruangan', 'pengguna', 'daftar-aduan', 'layanan-pengaduan', 'harmonisasi', 'izin', 'tamu']);
      ?>

      <?php if ($showSearch): ?>
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" class="live-search" data-page="<?= $currentPage ?>" placeholder="Cari..." />
        </div>
      <?php endif; ?>

      <div style="display: flex; align-items: center; gap: 12px; margin-left: auto;">
        <?php if ($isAdminUser): ?>
          <button type="button" onclick="toggleMaintenanceMode()" 
                  class="btn-maint-topbar <?= $isMaintenanceActive ? 'active' : '' ?>" 
                  title="<?= $isMaintenanceActive ? 'Mode Maintenance AKTIF (Klik untuk Matikan)' : 'Aktifkan Mode Maintenance' ?>"
                  style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; cursor: pointer; border: 1px solid <?= $isMaintenanceActive ? '#f59e0b' : 'var(--border-color)' ?>; background: <?= $isMaintenanceActive ? 'rgba(245, 158, 11, 0.15)' : 'var(--panel-color)' ?>; color: <?= $isMaintenanceActive ? '#d97706' : 'var(--text-color)' ?>; transition: all 0.2s;">
            <i class="fas fa-tools" style="<?= $isMaintenanceActive ? 'color: #d97706;' : '' ?>"></i>
            <span><?= $isMaintenanceActive ? 'Maintenance ON' : 'Maintenance' ?></span>
          </button>
        <?php endif; ?>

        <?php
        $userFoto = $_SESSION['user']['foto'] ?? 'user.jpg';
        $avatarSrc = $BASE . '/Images/user.jpg';
        if (!empty($userFoto) && $userFoto !== 'user.jpg') {
            $publicDir = dirname(dirname(dirname(__DIR__))) . '/public';
            $storagePath = $publicDir . '/storage/uploads/users/' . $userFoto;
            $imagesPath  = $publicDir . '/Images/users/' . $userFoto;
            if (file_exists($storagePath)) {
                $avatarSrc = $BASE . '/storage/uploads/users/' . $userFoto;
            } elseif (file_exists($imagesPath)) {
                $avatarSrc = $BASE . '/Images/users/' . $userFoto;
            }
        }
        ?>
        <div class="profile-info">
          <span class="user-info">
            <?= isset($_SESSION['user']) ? htmlspecialchars($_SESSION['user']['nama']) : 'User' ?>
            <small>(<?= isset($_SESSION['user']) ? $_SESSION['user']['role'] : 'Guest' ?>)</small>
          </span>
          <a href="<?= $BASE ?>/index.php?page=edit-profil">
            <img
              src="<?= $avatarSrc ?>"
              alt="Profile" class="profile-link" />
          </a>
        </div>
      </div>
    </div>

    <?php if ($isMaintenanceActive && $isAdminUser): ?>
      <div style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; text-align: center; padding: 10px 15px; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.15); margin-top: 60px;">
          <i class="fas fa-exclamation-triangle"></i>
          <span>MODE MAINTENANCE AKTIF &mdash; Akses publik/non-admin ditutup. Hanya Admin yang dapat mengakses sistem.</span>
          <button onclick="toggleMaintenanceMode()" style="background: #fff; color: #d97706; border: none; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: 12px; cursor: pointer; margin-left: 10px;">
              Matikan Mode Maintenance
          </button>
      </div>
    <?php endif; ?>

    <script>
      function toggleMaintenanceMode() {
        const isCurrentlyActive = <?= $isMaintenanceActive ? 'true' : 'false' ?>;
        const actionTitle = isCurrentlyActive ? 'Matikan Mode Maintenance?' : 'Aktifkan Mode Maintenance?';
        const actionText = isCurrentlyActive 
          ? 'Akses sistem akan dibuka kembali untuk seluruh pengguna.' 
          : 'Akses sistem akan ditutup untuk pengguna umum dengan pesan: "Website ini sedang maintenance, mohon maaf atas ketidaknyamanan anda, tetap pantau situs kami secara berkala". Admin tetap dapat mengakses sistem.';
        const confirmBtnText = isCurrentlyActive ? 'Ya, Matikan' : 'Ya, Aktifkan Mode Maintenance';
        const confirmBtnColor = isCurrentlyActive ? '#10b981' : '#f59e0b';

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            title: actionTitle,
            text: actionText,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmBtnColor,
            cancelButtonColor: '#6b7280',
            confirmButtonText: confirmBtnText,
            cancelButtonText: 'Batal'
          }).then((result) => {
            if (result.isConfirmed) {
              executeMaintenanceToggle();
            }
          });
        } else {
          if (confirm(actionTitle + '\n' + actionText)) {
            executeMaintenanceToggle();
          }
        }
      }

      function executeMaintenanceToggle() {
        fetch('<?= $BASE ?>/ajax/toggle_maintenance.php?action=toggle', { method: 'POST' })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              if (typeof Swal !== 'undefined') {
                Swal.fire({
                  title: 'Berhasil!',
                  text: data.message,
                  icon: 'success',
                  timer: 1500,
                  showConfirmButton: false
                }).then(() => window.location.reload());
              } else {
                alert(data.message);
                window.location.reload();
              }
            } else {
              alert(data.message || 'Gagal mengubah mode maintenance');
            }
          })
          .catch(err => {
            console.error('Error toggling maintenance:', err);
            alert('Terjadi kesalahan jaringan.');
          });
      }
    </script>

    <div class="dash-content">