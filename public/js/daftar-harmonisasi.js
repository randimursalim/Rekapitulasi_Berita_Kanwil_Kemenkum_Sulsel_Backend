// Daftar Harmonisasi Management
document.addEventListener('DOMContentLoaded', function() {
  // Global variables
  let currentPage = 1;
  let totalPages = 1;
  let totalData = 0;
  let itemsPerPage = 10;
  let currentFilters = {
    search: '',
    startDate: '',
    endDate: '',
    status: ''
  };

  // DOM elements
  const container = document.getElementById('harmonisasiResults');
  const paginationContainer = document.getElementById('pagination');
  const filterBtn = document.getElementById('filterBtn');
  const resetBtn = document.getElementById('resetBtn');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');
  const statusFilter = document.getElementById('statusFilter');

  // Check if required elements exist
  if (!container || !paginationContainer) {
    return;
  }

  // Initialize the page
  loadHarmonisasi(1);
  attachEventListeners();

  // Expose functions globally untuk digunakan oleh live-search.js
  window.loadHarmonisasiArsip = loadHarmonisasi;
  window.setCurrentFiltersHarmonisasi = function(filters) {
    currentFilters = { ...currentFilters, ...filters };
    currentPage = 1;
    loadHarmonisasi(currentPage);
  };

  // Event listeners
  function attachEventListeners() {
    // Filter button
    if (filterBtn && startDate && endDate && statusFilter) {
      filterBtn.addEventListener('click', function() {
        currentFilters.startDate = startDate.value;
        currentFilters.endDate = endDate.value;
        currentFilters.status = statusFilter.value;
        currentPage = 1;
        loadHarmonisasi(currentPage);
      });
    }

    // Reset button
    if (resetBtn && startDate && endDate && statusFilter) {
      resetBtn.addEventListener('click', function() {
        startDate.value = '';
        endDate.value = '';
        statusFilter.value = '';
        // Reset search input di header juga
        const searchInput = document.querySelector('.live-search[data-page="harmonisasi"]');
        if (searchInput) {
          searchInput.value = '';
        }
        currentFilters = {
          search: '',
          startDate: '',
          endDate: '',
          status: ''
        };
        currentPage = 1;
        loadHarmonisasi(currentPage);
      });
    }
  }

  // Load harmonisasi with pagination
  async function loadHarmonisasi(page = 1) {
    if (!container) return;
    
    try {
      // Show loading
      container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

      // Get BASE_URL untuk path dinamis
      const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
      const fetchUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/ajax/fetch_harmonisasi.php') : 'ajax/fetch_harmonisasi.php';

      // Build query parameters
      const params = new URLSearchParams({
        page: page,
        search: currentFilters.search,
        startDate: currentFilters.startDate,
        endDate: currentFilters.endDate,
        status: currentFilters.status
      });

      const response = await fetch(`${fetchUrl}?${params}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();

      if (!result.success) {
        container.innerHTML = `<div style="color:red; text-align: center; padding: 20px;">Gagal memuat data harmonisasi: ${result.error || 'Unknown error'}</div>`;
        return;
      }

      const data = result.data;
      totalPages = result.pagination.totalPages;
      totalData = result.pagination.totalData;
      currentPage = result.pagination.currentPage;
      
      itemsPerPage = 10;

      // Render data
      renderData(data);
      
      // Render pagination
      renderPagination();

    } catch (error) {
      container.innerHTML = '<div style="color:red; text-align: center; padding: 20px;">Terjadi kesalahan saat memuat data.</div>';
    }
  }

  // Render data table
  function renderData(data) {
    if (!container) return;
    
    if (data.length === 0) {
      container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="fas fa-balance-scale" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada data harmonisasi</span></div>';
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

      <div class="data judul">
        <span class="data-title">Judul Rancangan</span>
        ${data.map(h => {
          const judul = h.judul_rancangan || '-';
          const judulDisplay = judul.length > 35 ? judul.substring(0, 35) + '...' : judul;
          return `<span class="data-list" title="${escapeHtml(judul)}"><span class="text-content">${escapeHtml(judulDisplay)}</span></span>`;
        }).join('')}
      </div>

      <div class="data pemrakarsa">
        <span class="data-title">Pemrakarsa</span>
        ${data.map(h => {
          const pemrakarsa = h.pemrakarsa || '-';
          const pemrakarsaDisplay = pemrakarsa.length > 20 ? pemrakarsa.substring(0, 20) + '...' : pemrakarsa;
          return `<span class="data-list" title="${escapeHtml(pemrakarsa)}"><span class="text-content">${escapeHtml(pemrakarsaDisplay)}</span></span>`;
        }).join('')}
      </div>

      <div class="data pemerintah">
        <span class="data-title">Pemerintah Daerah</span>
        ${data.map(h => `<span class="data-list">${escapeHtml(h.pemerintah_daerah || '-')}</span>`).join('')}
      </div>

      <div class="data tanggal">
        <span class="data-title">Tanggal Rapat</span>
        ${data.map(h => `<span class="data-list">${formatDate(h.tanggal_rapat)}</span>`).join('')}
      </div>

      <div class="data pemegang">
        <span class="data-title">Pemegang Draf</span>
        ${data.map(h => `<span class="data-list">${escapeHtml(h.pemegang_draf || '-')}</span>`).join('')}
      </div>

      <div class="data status">
        <span class="data-title">Status</span>
        ${data.map(h => {
          const status = h.status || 'Diterima';
          const statusClass = status === 'Diterima' ? 'status-selesai' : 'status-proses';
          const statusText = status === 'Diterima' ? 'Diterima' : 'Dikembalikan';
          return `<span class="data-list"><span class="status-badge ${statusClass}">${statusText}</span></span>`;
        }).join('')}
      </div>

      <div class="data actions">
        <span class="data-title">Aksi</span>
        ${data.map((h, index) => `
          <span class="data-list">
            <button class="btn-action-aksi view" onclick="showDetailHarmonisasi(${h.id}, '${escapeHtml(h.judul_rancangan || '')}', '${escapeHtml(h.pemrakarsa || '')}', '${escapeHtml(h.pemerintah_daerah || '')}', '${formatDate(h.tanggal_rapat)}', '${escapeHtml(h.pemegang_draf || '')}', '${h.status || 'Diterima'}', '${escapeHtml(h.alasan_pengembalian_draf || '')}')">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-harmonisasi&id=${h.id}'">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-action-aksi delete" onclick="hapusHarmonisasi(${h.id}, '${escapeHtml(h.judul_rancangan || '')}')">
              <i class="fas fa-trash-alt"></i>
            </button>
          </span>
        `).join('')}
      </div>
    `;

    container.innerHTML = html;
  }

  // Helper functions
  function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID');
  }

  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r');
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
          loadHarmonisasi(currentPage);
        }
      });
    });
  }

  // Fungsi tampilkan modal detail
  function showDetailHarmonisasi(id, judulRancangan, pemrakarsa, pemerintahDaerah, tanggalRapat, pemegangDraf, status, alasanPengembalianDraf) {
    const modalContent = document.getElementById('modalContent');
    if (!modalContent) return;

    let htmlContent = '';
    
    htmlContent += `<strong>Judul Rancangan:</strong> ${judulRancangan || '-'}<br><br>`;
    htmlContent += `<strong>Pemrakarsa:</strong> ${pemrakarsa || '-'}<br>`;
    htmlContent += `<strong>Pemerintah Daerah:</strong> ${pemerintahDaerah || '-'}<br>`;
    htmlContent += `<strong>Tanggal Rapat:</strong> ${tanggalRapat}<br>`;
    htmlContent += `<strong>Pemegang Draf:</strong> ${pemegangDraf || '-'}<br><br>`;
    
    htmlContent += `<strong>Status:</strong> `;
    const statusText = status === 'Diterima' ? 'Diterima' : 'Dikembalikan';
    const statusClass = status === 'Diterima' ? 'status-selesai' : 'status-proses';
    htmlContent += `<span class="status-badge ${statusClass}">${statusText}</span><br>`;
    
    if (status === 'Dikembalikan' && alasanPengembalianDraf) {
      htmlContent += `<br><strong>Alasan Pengembalian Draf:</strong><br>`;
      htmlContent += `<div style="margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 5px; white-space: pre-wrap;">${escapeHtml(alasanPengembalianDraf).replace(/\n/g, '<br>')}</div>`;
    }
    
    modalContent.innerHTML = htmlContent;
    document.getElementById('detailModal').style.display = 'block';
  }

  // Fungsi tutup modal
  function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
  }

  // Tutup modal dengan tombol close
  const closeBtn = document.querySelector('#detailModal .close');
  if (closeBtn) {
    closeBtn.addEventListener('click', closeModal);
  }

  // Tutup modal jika klik di luar konten
  window.addEventListener('click', function(event) {
    const modal = document.getElementById('detailModal');
    if (event.target === modal) closeModal();
  });

  // Fungsi hapus harmonisasi
  function hapusHarmonisasi(id, judulRancangan) {
    Swal.fire({
      title: 'Apakah kamu yakin?',
      text: `Kamu akan menghapus data harmonisasi "${judulRancangan}"`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
        const deleteUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/index.php?page=hapus-harmonisasi') : 'index.php?page=hapus-harmonisasi';
        
        fetch(deleteUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: data.message,
              showConfirmButton: false,
              timer: 1500
            }).then(() => {
              loadHarmonisasi(currentPage);
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: data.message,
              showConfirmButton: true
            });
          }
        })
        .catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat menghapus data',
            showConfirmButton: true
          });
        });
      }
    });
  }

  // Expose functions ke global scope
  window.showDetailHarmonisasi = showDetailHarmonisasi;
  window.hapusHarmonisasi = hapusHarmonisasi;
});

