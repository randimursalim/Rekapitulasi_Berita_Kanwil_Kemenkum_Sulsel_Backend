<?php
// app/views/layouts/header.php

$BASE = defined('BASE_URL') ? BASE_URL : '/rekap-konten/public';
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard'; // Halaman default

function is_active($pageName) {
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

  <!-- Stylesheets -->
  <link rel="stylesheet" href="<?= $BASE ?>/css/style.css" />
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />

  <!-- Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <nav>
    <div class="logo-name">
      <div class="logo-image">
        <img src="<?= $BASE ?>/images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" />
      </div>
      <span class="logo_name">KEMENKUM SULSEL</span>
    </div>

    <div class="menu-items">
      <ul class="nav-links">
        <li><a href="<?= $BASE ?>/index.php?page=dashboard" class="<?= is_active('dashboard') ?>"><i class="uil uil-estate"></i><span class="link-name">Dashboard</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=input-konten" class="<?= is_active('input-konten') ?>"><i class="uil uil-file-plus"></i><span class="link-name">Input Konten</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=rekap-konten" class="<?= is_active('rekap-konten') ?>"><i class="uil uil-database"></i><span class="link-name">Rekap Konten</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=arsip" class="<?= is_active('arsip') ?>"><i class="uil uil-archive"></i><span class="link-name">Arsip</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=jadwal-kegiatan" class="<?= is_active('jadwal-kegiatan') ?>"><i class="uil uil-schedule"></i><span class="link-name">Jadwal Kegiatan</span></a></li>
        <li><a href="<?= $BASE ?>/index.php?page=pengguna" class="<?= is_active('pengguna') ?>"><i class="uil uil-users-alt"></i><span class="link-name">Pengguna</span></a></li>
      </ul>

      <ul class="logout-mode">
        <li><a href="#"><i class="uil uil-signout"></i><span class="link-name">Logout</span></a></li>
        <li class="mode">
          <a href="#"><i class="uil uil-moon"></i><span class="link-name">Dark Mode</span></a>
          <div class="mode-toggle"><span class="switch"></span></div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Konten utama -->
  <section class="dashboard">
    <div class="top">
      <i class="uil uil-bars sidebar-toggle"></i>

      <?php
      // Tampilkan search box di halaman tertentu
      $showSearch = in_array($currentPage, ['arsip', 'jadwal-kegiatan', 'pengguna']);
      ?>

      <?php if ($showSearch): ?>
        <div class="search-box">
          <i class="uil uil-search"></i>
          <input type="text"
                 class="live-search"
                 data-page="<?= $currentPage ?>"
                 placeholder="Cari..." />
        </div>
      <?php endif; ?>

      <a href="<?= $BASE ?>/index.php?page=edit-profil">
        <img src="<?= $BASE ?>/images/user.jpg" alt="Profile" class="profile-link" />
      </a>
    </div>

    <div class="dash-content">

<!-- 🔹 SCRIPT LIVE SEARCH AJAX (Reusable) -->
<script>
document.addEventListener('DOMContentLoaded', function () {

  // Format kategori berita
  function formatKategori(str) {
    if (!str) return '-';
    return str.replace(/_/g, ' ')
              .split(' ')
              .map(word => word.charAt(0).toUpperCase() + word.slice(1))
              .join(' ');
  }

  // Render hasil search ke container (arsip.php)
  function renderArsip(data) {
    const container = document.getElementById('searchResults');
    if (!container) return;

    // Jika kosong, tampilkan tabel asli
    if (!data || data.length === 0) {
      container.innerHTML = document.getElementById('originalTable')?.innerHTML || '';
      if (typeof attachEvents === 'function') {
        attachEvents();
      }
      return;
    }

    let no = 1;
    let html = '';
    const columns = ['no','title-news','jenis','kategori','date','dokumentasi','actions'];

    columns.forEach(col => {
      html += `<div class="data ${col}">`;

      // Header kolom
      if(col==='no') html += '<span class="data-title">No</span>';
      else if(col==='title-news') html += '<span class="data-title">Judul</span>';
      else if(col==='jenis') html += '<span class="data-title">Jenis</span>';
      else if(col==='kategori') html += '<span class="data-title">Kategori/Platform</span>';
      else if(col==='date') html += '<span class="data-title">Tanggal</span>';
      else if(col==='dokumentasi') html += '<span class="data-title">Dokumentasi</span>';
      else if(col==='actions') html += '<span class="data-title">Aksi</span>';

      // Isi data
      data.forEach(konten => {
        if(col==='no') html += `<span class="data-list">${no++}</span>`;
        else if(col==='title-news') html += `<span class="data-list">${konten.judul}</span>`;
        else if(col==='jenis') html += `<span class="data-list">${konten.jenis==='berita'?'Berita':'Sosial Media'}</span>`;
        else if(col==='kategori') html += `<span class="data-list">${konten.jenis==='berita'?formatKategori(konten.jenis_berita):(konten.jenis||'-')}</span>`;
        else if(col==='date') html += `<span class="data-list">${konten.jenis==='berita'?konten.tanggal_berita:konten.tanggal_post}</span>`;
        else if(col==='dokumentasi') html += `<span class="data-list">${konten.dokumentasi?`<img src="${konten.dokumentasi}" style="width:60px;cursor:pointer;">`:'-'}</span>`;
        else if(col==='actions') html += `<span class="data-list">
          <button class="btn-action-aksi view" onclick="window.open('${konten.jenis==='berita'?konten.link_berita:konten.link_post}','_blank')"><i class="uil uil-eye"></i></button>
          <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-konten&id=${konten.id_konten}'"><i class="uil uil-edit"></i></button>
          <button class="btn-action-aksi delete" data-id="${konten.id_konten}"><i class="uil uil-trash-alt"></i></button>
        </span>`;
      });

      html += '</div>';
    });

    container.innerHTML = html;
    if (typeof attachEvents === 'function') {
      attachEvents();
    }
  }

  // Render hasil search ke container (jadwal-kegiatan.php)
  function renderKegiatan(data) {
    const container = document.getElementById('kegiatanResults');
    if (!container) return;

    // Jika kosong, tampilkan pesan kosong
    if (!data || data.length === 0) {
      container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="uil uil-calendar-alt" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada kegiatan yang dijadwalkan</span></div>';
      return;
    }

    let no = 1;
    let html = '';
    const columns = ['no','kegiatan','tanggal','waktu','keterangan','status','actions'];

    columns.forEach(col => {
      html += `<div class="data ${col}">`;

      // Header kolom
      if(col==='no') html += '<span class="data-title">No</span>';
      else if(col==='kegiatan') html += '<span class="data-title">Nama Kegiatan</span>';
      else if(col==='tanggal') html += '<span class="data-title">Tanggal</span>';
      else if(col==='waktu') html += '<span class="data-title">Waktu</span>';
      else if(col==='keterangan') html += '<span class="data-title">Keterangan</span>';
      else if(col==='status') html += '<span class="data-title">Status</span>';
      else if(col==='actions') html += '<span class="data-title">Aksi</span>';

      // Isi data
      data.forEach(kegiatan => {
        if(col==='no') html += `<span class="data-list">${no++}</span>`;
        else if(col==='kegiatan') html += `<span class="data-list">${kegiatan.nama_kegiatan}</span>`;
        else if(col==='tanggal') html += `<span class="data-list">${formatDateKegiatan(kegiatan.tanggal)}</span>`;
        else if(col==='waktu') html += `<span class="data-list">${formatTimeKegiatan(kegiatan.jam_mulai)}-${formatTimeKegiatan(kegiatan.jam_selesai)}</span>`;
        else if(col==='keterangan') html += `<span class="data-list">${kegiatan.keterangan ? (kegiatan.keterangan.length > 50 ? kegiatan.keterangan.substring(0, 50) + '...' : kegiatan.keterangan) : '-'}</span>`;
        else if(col==='status') {
          const statusInfo = getDynamicStatusKegiatan(kegiatan);
          html += `<span class="data-list ${statusInfo.class}" data-status="${kegiatan.status}" data-tanggal="${kegiatan.tanggal}" data-jam-mulai="${kegiatan.jam_mulai}" data-jam-selesai="${kegiatan.jam_selesai}">${statusInfo.text}</span>`;
        }
        else if(col==='actions') html += `<span class="data-list">
          <button class="btn-action-aksi view" onclick="showKeterangan('${kegiatan.keterangan || ''}')"><i class="uil uil-eye"></i></button>
          <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-kegiatan&id=${kegiatan.id_kegiatan}'"><i class="uil uil-edit"></i></button>
          <button class="btn-action-aksi delete" onclick="hapusKegiatan(${kegiatan.id_kegiatan}, '${kegiatan.nama_kegiatan.replace(/'/g, "\\'")}')"><i class="uil uil-trash-alt"></i></button>
        </span>`;
      });

      html += '</div>';
    });

    container.innerHTML = html;
  }

  // Helper functions untuk kegiatan
  function formatDateKegiatan(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID');
  }

  function formatTimeKegiatan(timeStr) {
    return timeStr.substring(0, 5); // HH:MM
  }

  function getDynamicStatusKegiatan(kegiatan) {
    const now = new Date();
    const jamMulai = new Date(kegiatan.tanggal + ' ' + kegiatan.jam_mulai);
    const jamSelesai = new Date(kegiatan.tanggal + ' ' + kegiatan.jam_selesai);
    
    if (!['Selesai', 'Ditunda', 'Dibatalkan'].includes(kegiatan.status)) {
      if (now > jamSelesai) {
        return { text: 'Selesai', class: 'status-selesai' };
      } else if (now >= jamMulai && now <= jamSelesai) {
        return { text: 'Sedang Berlangsung', class: 'status-berlangsung' };
      } else {
        return { text: 'Belum Dimulai', class: 'status-belum' };
      }
    } else {
      switch(kegiatan.status) {
        case 'Selesai': return { text: 'Selesai', class: 'status-selesai' };
        case 'Ditunda': return { text: 'Ditunda', class: 'status-ditunda' };
        case 'Dibatalkan': return { text: 'Dibatalkan', class: 'status-dibatalkan' };
        default: return { text: kegiatan.status, class: 'status-belum' };
      }
    }
  }

  // Event listener live search
  document.querySelectorAll('.live-search').forEach(input=>{
    input.addEventListener('input', function(){
      const page = input.dataset.page;
      const query = input.value.trim();
      
      if(page==='arsip') {
        // Gunakan pagination system untuk arsip
        console.log('Live search for arsip:', query);
        if(typeof window.setCurrentFilters === 'function') {
          console.log('setCurrentFilters function available');
          // Langsung kirim query tanpa validasi panjang
          console.log('Calling setCurrentFilters with:', query);
          window.setCurrentFilters({ search: query });
        } else {
          console.log('setCurrentFilters function not available');
        }
        return;
      }
      
      if(page==='jadwal-kegiatan') {
        // Gunakan pagination system untuk jadwal kegiatan
        console.log('Live search for jadwal-kegiatan:', query);
        if(typeof window.setCurrentFiltersKegiatan === 'function') {
          console.log('setCurrentFiltersKegiatan function available');
          console.log('Calling setCurrentFiltersKegiatan with:', query);
          window.setCurrentFiltersKegiatan({ search: query });
        } else {
          console.log('setCurrentFiltersKegiatan function not available');
        }
        return;
      }
      
      if(page==='pengguna') {
        // Gunakan pagination system untuk pengguna
        console.log('Live search for pengguna:', query);
        if(typeof window.setCurrentFiltersPengguna === 'function') {
          console.log('setCurrentFiltersPengguna function available');
          console.log('Calling setCurrentFiltersPengguna with:', query);
          window.setCurrentFiltersPengguna({ search: query });
        } else {
          console.log('setCurrentFiltersPengguna function not available');
        }
        return;
      }
      
      // Untuk halaman lain, gunakan sistem lama
      if(query==='') return renderArsip(null);

      let url = '';
      // nanti tambah else if untuk pengguna

      if(url==='') return;
      fetch(url)
        .then(res=>res.json())
        .then(data=>renderArsip(data))
        .catch(err=>console.error(err));
    });
  });

});
</script>
