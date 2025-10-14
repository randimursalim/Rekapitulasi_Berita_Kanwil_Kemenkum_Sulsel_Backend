<?php
// app/views/pages/arsip.php
?>

<div class="overview arsip-page">
  <div class="title">
    <i class="uil uil-archive"></i>
    <span class="text">Arsip Konten</span>
  </div>

  <!-- 🔹 Filter -->
  <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
    <label for="startDate">Tanggal:</label>
    <input type="date" id="startDate">
    <span>-</span>
    <input type="date" id="endDate">
    <button id="filterBtn">Terapkan</button>
    <button id="resetBtn">Reset</button>

    <label for="filterJenis">Jenis Konten:</label>
    <select id="filterJenis" class="filter-select">
      <option value="all">Semua</option>
      <option value="berita">Berita</option>
      <option value="medsos">Media Sosial</option>
    </select>

    <label for="filterKategori">Kategori/Platform:</label>
    <select id="filterKategori" class="filter-select">
      <option value="all">Semua</option>
      <option value="media_online">Media Online</option>
      <option value="surat_kabar">Surat Kabar</option>
      <option value="website_kanwil">Website Kanwil</option>
      <option value="facebook">Facebook</option>
      <option value="instagram">Instagram</option>
      <option value="tiktok">TikTok</option>
      <option value="twitter">Twitter</option>
      <option value="youtube">YouTube</option>
    </select>
  </div>

  <!-- 🔹 Arsip -->
  <div class="activity-wrapper" style="margin-top:20px;">
    <div class="activity">
      <div class="activity-data" id="searchResults">
        <!-- Data akan dimuat via AJAX -->
        <div style="text-align: center; padding: 20px;">
          <p>Memuat data...</p>
        </div>
      </div>
    </div>
  </div>

  <!-- 🔹 Pagination -->
  <div class="pagination" id="pagination">
    <!-- Pagination akan di-generate via JavaScript -->
  </div>
</div>

<!-- 🔹 Modal Preview -->
<div class="modal-img" id="imgModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); text-align:center; padding-top:5%;">
  <img id="modalImage" src="" alt="Preview" style="max-width:90%; max-height:80%;">
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Global variables
  let currentPage = 1;
  let totalPages = 1;
  let totalData = 0;
  let itemsPerPage = 10; // Default items per page
  let currentFilters = {
    search: '',
    jenis: 'all',
    kategori: 'all',
    startDate: '',
    endDate: ''
  };

  // DOM elements
  const container = document.getElementById('searchResults');
  const paginationContainer = document.getElementById('pagination');
  const filterBtn = document.getElementById('filterBtn');
  const resetBtn = document.getElementById('resetBtn');
  const filterJenis = document.getElementById('filterJenis');
  const filterKategori = document.getElementById('filterKategori');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');

  // Check if required elements exist
  if (!container || !paginationContainer) {
    console.error('Required DOM elements not found');
    return;
  }

  // Initialize the page
  loadKonten(1);
  attachEventListeners();

  // Expose loadKonten function globally untuk digunakan oleh header.php
  window.loadKontenArsip = loadKonten;
  window.setCurrentFilters = function(filters) {
    currentFilters = { ...currentFilters, ...filters };
    currentPage = 1;
    loadKonten(currentPage);
  };

  // Event listeners
  function attachEventListeners() {
    // Filter button
    if (filterBtn && filterJenis && filterKategori && startDate && endDate) {
      filterBtn.addEventListener('click', function() {
        currentFilters.jenis = filterJenis.value;
        currentFilters.kategori = filterKategori.value;
        currentFilters.startDate = startDate.value;
        currentFilters.endDate = endDate.value;
        currentPage = 1;
        loadKonten(currentPage);
      });
    }

    // Reset button
    if (resetBtn && filterJenis && filterKategori && startDate && endDate) {
      resetBtn.addEventListener('click', function() {
        filterJenis.value = 'all';
        filterKategori.value = 'all';
        startDate.value = '';
        endDate.value = '';
        currentFilters = {
          search: '',
          jenis: 'all',
          kategori: 'all',
          startDate: '',
          endDate: ''
        };
        currentPage = 1;
        loadKonten(currentPage);
      });
    }

    // Live search akan dihandle oleh header.php
  }

  // Load konten with pagination
  async function loadKonten(page = 1) {
    if (!container) return;
    
    try {
      // Show loading
      container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

      // Build query parameters
      const params = new URLSearchParams({
        page: page,
        search: currentFilters.search,
        filterJenis: currentFilters.jenis === 'all' ? '' : currentFilters.jenis,
        filterDivisi: currentFilters.kategori === 'all' ? '' : currentFilters.kategori,
        startDate: currentFilters.startDate,
        endDate: currentFilters.endDate
      });

      const response = await fetch(`ajax/fetch_konten.php?${params}`);
      
      // Check if response is ok
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();

      if (!result.success) {
        container.innerHTML = `<p style="color:red;">Gagal memuat data konten: ${result.error || 'Unknown error'}</p>`;
        return;
      }

      const data = result.data;
      totalPages = result.pagination.totalPages;
      totalData = result.pagination.totalData;
      currentPage = result.pagination.currentPage;
      
      // Items per page is fixed at 10 (as defined in fetch_konten.php)
      itemsPerPage = 10;

      // Render data
      renderData(data);
      
      // Render pagination
      renderPagination();

    } catch (error) {
      console.error('Error loading konten:', error);
      container.innerHTML = '<p style="color:red;">Terjadi kesalahan saat memuat data.</p>';
    }
  }

  // Render data table
  function renderData(data) {
    if (!container) return;
    
    if (data.length === 0) {
      container.innerHTML = '<p style="text-align: center; padding: 20px;">Tidak ada data konten ditemukan.</p>';
      return;
    }

    const html = `
      <div class="data no">
        <span class="data-title">No</span>
        ${data.map((_, i) => {
          const startNumber = (currentPage - 1) * itemsPerPage + 1;
          return `<span class="data-list">${startNumber + i}</span>`;
        }).join('')}
      </div>

      <div class="data title-news">
        <span class="data-title">Judul</span>
        ${data.map(k => `<span class="data-list">${k.judul}</span>`).join('')}
      </div>

      <div class="data jenis">
        <span class="data-title">Jenis</span>
        ${data.map(k => `<span class="data-list">${k.jenis === 'berita' ? 'Berita' : 'Media Sosial'}</span>`).join('')}
      </div>

      <div class="data kategori">
        <span class="data-title">Kategori/Platform</span>
        ${data.map(k => {
          if (k.jenis === 'berita') {
            // Ubah underscore menjadi spasi dan kapitalkan setiap kata
            const kategoriBerita = k.jenis_berita ? 
              k.jenis_berita.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : '-';
            return `<span class="data-list">${kategoriBerita}</span>`;
          } else {
            // Untuk media sosial, tampilkan platform spesifik
            const platformNames = {
              'instagram': 'Instagram',
              'youtube': 'YouTube', 
              'tiktok': 'TikTok',
              'twitter': 'Twitter',
              'facebook': 'Facebook'
            };
            return `<span class="data-list">${platformNames[k.jenis] || k.jenis || '-'}</span>`;
          }
        }).join('')}
      </div>

      <div class="data date">
        <span class="data-title">Tanggal</span>
        ${data.map(k => `<span class="data-list">${k.tanggal_berita || k.tanggal_post || '-'}</span>`).join('')}
      </div>

      <div class="data dokumentasi">
        <span class="data-title">Dokumentasi</span>
        ${data.map(k => `
          <span class="data-list">
            ${k.dokumentasi ? `<img src="${k.dokumentasi}" alt="Foto" class="preview-img" style="width:60px;cursor:pointer;">` : '-'}
          </span>
        `).join('')}
      </div>

      <div class="data actions">
        <span class="data-title">Aksi</span>
        ${data.map(k => `
          <span class="data-list">
            <button class="btn-action-aksi view" onclick="window.open('${k.jenis === 'berita' ? k.link_berita : k.link_post}','_blank')"><i class="uil uil-eye"></i></button>
            <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-konten&id=${k.id_konten}'"><i class="uil uil-edit"></i></button>
            <button class="btn-action-aksi delete" data-id="${k.id_konten}"><i class="uil uil-trash-alt"></i></button>
          </span>
        `).join('')}
      </div>
    `;

    container.innerHTML = html;
    attachActionEvents();
  }

  // Render pagination
  function renderPagination() {
    if (!paginationContainer) return;
    
    if (totalPages <= 1) {
      paginationContainer.innerHTML = '';
      return;
    }

    let paginationHTML = '';
    
    // Previous button
    if (currentPage > 1) {
      paginationHTML += `<button class="pagination-btn" data-page="${currentPage - 1}">Previous</button>`;
    }

    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
      paginationHTML += `<button class="pagination-btn" data-page="1">1</button>`;
      if (startPage > 2) {
        paginationHTML += `<span class="pagination-dots">...</span>`;
      }
    }

    for (let i = startPage; i <= endPage; i++) {
      const activeClass = i === currentPage ? 'active' : '';
      paginationHTML += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
    }

    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        paginationHTML += `<span class="pagination-dots">...</span>`;
      }
      paginationHTML += `<button class="pagination-btn" data-page="${totalPages}">${totalPages}</button>`;
    }

    // Next button
    if (currentPage < totalPages) {
      paginationHTML += `<button class="pagination-btn" data-page="${currentPage + 1}">Next</button>`;
    }

    paginationContainer.innerHTML = paginationHTML;

    // Attach pagination event listeners
    const paginationBtns = paginationContainer.querySelectorAll('.pagination-btn');
    paginationBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const page = parseInt(this.dataset.page);
        if (page && page !== currentPage) {
          currentPage = page;
          loadKonten(currentPage);
        }
      });
    });
  }

  // Attach action events (delete, image preview)
  function attachActionEvents() {
    if (!container) return;
    
    // Delete buttons
    const deleteBtns = container.querySelectorAll('.btn-action-aksi.delete');
    deleteBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const id = this.dataset.id;
        
        Swal.fire({
          title: 'Hapus Konten?',
          text: "Data yang dihapus tidak bisa dikembalikan!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Ya, hapus!',
          cancelButtonText: 'Batal'
        }).then(result => {
          if(result.isConfirmed){
            // AJAX call untuk hapus konten
            deleteKonten(id);
          }
        });
      });
    });

    // Image preview
    const previewImgs = container.querySelectorAll('.preview-img');
    previewImgs.forEach(img => {
      img.addEventListener('click', function() {
        const modal = document.getElementById('imgModal');
        const modalImg = document.getElementById('modalImage');
        if (modal && modalImg) {
          modalImg.src = this.src;
          modal.style.display = 'block';
        }
      });
    });
  }

  // Close modal when clicking outside
  const modal = document.getElementById('imgModal');
  if (modal) {
    modal.addEventListener('click', function() {
      this.style.display = 'none';
    });
  }

  // Function untuk hapus konten via AJAX
  async function deleteKonten(idKonten) {
    try {
      // Show loading
      Swal.fire({
        title: 'Menghapus...',
        text: 'Sedang menghapus konten',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
          Swal.showLoading();
        }
      });

      // AJAX request untuk hapus
      const formData = new FormData();
      formData.append('id_konten', idKonten);

      const response = await fetch('index.php?page=delete-konten', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success) {
        // Success message
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: 'Konten berhasil dihapus',
          showConfirmButton: false,
          timer: 1500
        }).then(() => {
          // Reload data setelah hapus
          loadKonten(currentPage);
        });
      } else {
        // Error message
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: result.error || 'Terjadi kesalahan saat menghapus konten',
          confirmButtonText: 'OK'
        });
      }
    } catch (error) {
      console.error('Error deleting konten:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Terjadi kesalahan saat menghapus konten',
        confirmButtonText: 'OK'
      });
    }
  }

  // Check for success message from URL parameters
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get('status');
  
  if (status === 'update_success') {
    Swal.fire({
      icon: 'success',
      title: 'Ubah Konten Sukses!',
      text: 'Data konten berhasil diperbarui.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      // Remove status parameter from URL
      const newUrl = window.location.pathname + '?page=arsip';
      window.history.replaceState({}, document.title, newUrl);
    });
  } else if (status === 'error_main' || status === 'error_detail') {
    Swal.fire({
      icon: 'error',
      title: 'Gagal Mengubah Data!',
      text: 'Terjadi kesalahan saat memperbarui konten. Silakan coba lagi.',
      showConfirmButton: true
    }).then(() => {
      // Remove status parameter from URL
      const newUrl = window.location.pathname + '?page=arsip';
      window.history.replaceState({}, document.title, newUrl);
    });
  }
});
</script>
