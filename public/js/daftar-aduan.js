// Daftar Aduan Management
document.addEventListener('DOMContentLoaded', function() {
  // Global variables
  let currentPage = 1;
  let totalPages = 1;
  let totalData = 0;
  let itemsPerPage = 10;
  let currentFilters = {
    search: '',
    startDate: '',
    endDate: ''
  };

  // DOM elements
  const container = document.getElementById('aduanResults');
  const paginationContainer = document.getElementById('pagination');
  const filterBtn = document.getElementById('filterBtn');
  const resetBtn = document.getElementById('resetBtn');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');

  // Check if required elements exist
  if (!container || !paginationContainer) {
    return;
  }

  // Initialize the page
  loadAduan(1);
  attachEventListeners();

  // Expose functions globally untuk digunakan oleh live-search.js
  window.loadAduanArsip = loadAduan;
  window.setCurrentFiltersAduan = function(filters) {
    currentFilters = { ...currentFilters, ...filters };
    currentPage = 1;
    loadAduan(currentPage);
  };

  // Event listeners
  function attachEventListeners() {
    // Filter button
    if (filterBtn && startDate && endDate) {
      filterBtn.addEventListener('click', function() {
        currentFilters.startDate = startDate.value;
        currentFilters.endDate = endDate.value;
        currentPage = 1;
        loadAduan(currentPage);
      });
    }

    // Reset button
    if (resetBtn && startDate && endDate) {
      resetBtn.addEventListener('click', function() {
        startDate.value = '';
        endDate.value = '';
        currentFilters = {
          search: '',
          startDate: '',
          endDate: ''
        };
        currentPage = 1;
        loadAduan(currentPage);
      });
    }
  }

  // Load aduan with pagination
  async function loadAduan(page = 1) {
    if (!container) return;
    
    try {
      // Show loading
      container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

      // Get BASE_URL untuk path dinamis
      const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
      const fetchUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/ajax/fetch_aduan.php') : 'ajax/fetch_aduan.php';

      // Build query parameters
      const params = new URLSearchParams({
        page: page,
        search: currentFilters.search,
        startDate: currentFilters.startDate,
        endDate: currentFilters.endDate
      });

      const response = await fetch(`${fetchUrl}?${params}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();

      if (!result.success) {
        container.innerHTML = `<p style="color:red;">Gagal memuat data aduan: ${result.error || 'Unknown error'}</p>`;
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
      container.innerHTML = '<p style="color:red;">Terjadi kesalahan saat memuat data.</p>';
    }
  }

  // Render data table
  function renderData(data) {
    if (!container) return;
    
    if (data.length === 0) {
      container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada data aduan</span></div>';
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

      <div class="data no-register">
        <span class="data-title">No. Register</span>
        ${data.map(a => `<span class="data-list">${a.no_register || '-'}</span>`).join('')}
      </div>

      <div class="data tanggal">
        <span class="data-title">Tanggal</span>
        ${data.map(a => `<span class="data-list">${formatDate(a.tanggal)}</span>`).join('')}
      </div>

      <div class="data aduan">
        <span class="data-title">Aduan</span>
        ${data.map(a => `<span class="data-list">${a.aduan ? (a.aduan.length > 50 ? a.aduan.substring(0, 50) + '...' : a.aduan) : '-'}</span>`).join('')}
      </div>

      <div class="data jenis">
        <span class="data-title">Jenis Aduan</span>
        ${data.map(a => `<span class="data-list">${a.jenis_aduan || '-'}</span>`).join('')}
      </div>

      <div class="data media">
        <span class="data-title">Media</span>
        ${data.map(a => `<span class="data-list">${a.media_digunakan || '-'}</span>`).join('')}
      </div>

      <div class="data tindak-lanjut">
        <span class="data-title">Tindak Lanjut</span>
        ${data.map(a => `<span class="data-list">${a.tindak_lanjut ? (a.tindak_lanjut.length > 50 ? a.tindak_lanjut.substring(0, 50) + '...' : a.tindak_lanjut) : '-'}</span>`).join('')}
      </div>

      <div class="data actions">
        <span class="data-title">Aksi</span>
        ${data.map((a, index) => `
          <span class="data-list">
            <button class="btn-action-aksi view" onclick="showDetailAduan(${a.id_aduan}, '${escapeHtml(a.no_register || '')}', '${formatDate(a.tanggal)}', '${escapeHtml(a.aduan || '')}', '${escapeHtml(a.jenis_aduan || '')}', '${escapeHtml(a.media_digunakan || '')}', '${escapeHtml(a.tindak_lanjut || '')}', '${escapeHtml(a.keterangan || '')}')">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-aduan&id=${a.id_aduan}'">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-action-aksi delete" onclick="hapusAduan(${a.id_aduan}, '${escapeHtml(a.no_register || '')}')">
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
          loadAduan(currentPage);
        }
      });
    });
  }

  // Fungsi tampilkan modal detail
  function showDetailAduan(id, noRegister, tanggal, aduan, jenisAduan, media, tindakLanjut, keterangan) {
    const modalContent = document.getElementById('modalContent');
    if (!modalContent) return;

    let content = `No. Register: ${noRegister || '-'}\n`;
    content += `Tanggal: ${tanggal}\n`;
    content += `Jenis Aduan: ${jenisAduan || '-'}\n`;
    content += `Media Digunakan: ${media || '-'}\n\n`;
    content += `Aduan:\n${aduan || '-'}\n\n`;
    if (tindakLanjut) {
      content += `Tindak Lanjut:\n${tindakLanjut}\n\n`;
    }
    if (keterangan) {
      content += `Keterangan:\n${keterangan}`;
    }

    modalContent.textContent = content;
    document.getElementById('detailModal').style.display = 'block';
  }

  // Fungsi tutup modal
  function closeModal() {
    document.getElementById('detailModal').style.display = 'none';
  }

  // Fungsi hapus aduan
  function hapusAduan(id, noRegister) {
    Swal.fire({
      title: 'Apakah kamu yakin?',
      text: `Kamu akan menghapus aduan dengan No. Register "${noRegister}"`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
        const deleteUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/index.php?page=hapus-aduan') : 'index.php?page=hapus-aduan';
        
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
              loadAduan(currentPage);
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

  // Expose functions globally
  window.showDetailAduan = showDetailAduan;
  window.hapusAduan = hapusAduan;
});

