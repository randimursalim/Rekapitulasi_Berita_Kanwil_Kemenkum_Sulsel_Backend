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
  // Render data table
  function renderData(data) {
    if (!container) return;
    
    if (data.length === 0) {
      container.innerHTML = '<div class="no-data" style="text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="fas fa-balance-scale" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada data harmonisasi</span></div>';
      return;
    }

    const startNumber = (currentPage - 1) * itemsPerPage + 1;

    const html = `
      <div class="harmonisasi-table-container">
        <table class="table-harmonisasi">
          <thead>
            <tr>
              <th class="col-no">No</th>
              <th class="col-judul">Judul Rancangan</th>
              <th class="col-pemrakarsa">Pemrakarsa</th>
              <th class="col-pemda">Pemerintah Daerah</th>
              <th class="col-tanggal-surat">Tanggal Surat Diterima</th>
              <th class="col-tanggal-rapat">Tanggal Rapat</th>
              <th class="col-pemegang">Pemegang Draf</th>
              <th class="col-status">Status</th>
              <th class="col-aksi">Aksi</th>
            </tr>
          </thead>
          <tbody>
            ${data.map((h, i) => {
              const status = h.status || 'Diterima';
              const statusClass = status === 'Diterima' ? 'status-selesai' : 'status-proses';
              const statusText = status === 'Diterima' ? 'Diterima' : 'Dikembalikan';
              return `
                <tr>
                  <td class="col-no">${startNumber + i}</td>
                  <td class="col-judul">${escapeHtmlText(h.judul_rancangan || '-')}</td>
                  <td class="col-pemrakarsa">${escapeHtmlText(h.pemrakarsa || '-')}</td>
                  <td class="col-pemda">${escapeHtmlText(h.pemerintah_daerah || '-')}</td>
                  <td class="col-tanggal-surat">${formatDate(h.tanggal_surat_diterima)}</td>
                  <td class="col-tanggal-rapat">${formatDate(h.tanggal_rapat)}</td>
                  <td class="col-pemegang">${escapeHtmlText(h.pemegang_draf || '-')}</td>
                  <td class="col-status"><span class="status-badge ${statusClass}">${statusText}</span></td>
                  <td class="col-aksi">
                    <div class="action-buttons">
                      <button class="btn-action-aksi view" title="Detail" onclick="showDetailHarmonisasi(${h.id}, '${escapeJsParam(h.judul_rancangan || '')}', '${escapeJsParam(h.pemrakarsa || '')}', '${escapeJsParam(h.pemerintah_daerah || '')}', '${formatDate(h.tanggal_surat_diterima)}', '${formatDate(h.tanggal_rapat)}', '${escapeJsParam(h.pemegang_draf || '')}', '${h.status || 'Diterima'}', '${escapeJsParam(h.alasan_pengembalian_draf || '')}')">
                        <i class="fas fa-eye"></i>
                      </button>
                      <button class="btn-action-aksi edit" title="Edit" onclick="window.location.href='index.php?page=edit-harmonisasi&id=${h.id}'">
                        <i class="fas fa-edit"></i>
                      </button>
                      <button class="btn-action-aksi delete" title="Hapus" onclick="hapusHarmonisasi(${h.id}, '${escapeJsParam(h.judul_rancangan || '')}')">
                        <i class="fas fa-trash-alt"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
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

  function escapeHtmlText(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function escapeJsParam(text) {
    if (!text) return '';
    return text.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/\n/g, ' ').replace(/\r/g, ' ');
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
  function showDetailHarmonisasi(id, judulRancangan, pemrakarsa, pemerintahDaerah, tanggalSuratDiterima, tanggalRapat, pemegangDraf, status, alasanPengembalianDraf) {
    const modalContent = document.getElementById('modalContent');
    if (!modalContent) return;

    let htmlContent = '';
    
    htmlContent += `<strong>Judul Rancangan:</strong><br>`;
    htmlContent += `<div style="margin: 10px 0; padding: 10px; background: var(--panel-color); border: 1px solid var(--border-color); border-radius: 5px; white-space: pre-wrap; color: var(--text-color); word-wrap: break-word; word-break: break-word;">${escapeHtml(judulRancangan || '-')}</div><br>`;
    htmlContent += `<strong>Pemrakarsa:</strong> ${escapeHtml(pemrakarsa || '-')}<br>`;
    htmlContent += `<strong>Pemerintah Daerah:</strong> ${escapeHtml(pemerintahDaerah || '-')}<br>`;
    htmlContent += `<strong>Tanggal Surat Diterima:</strong> ${tanggalSuratDiterima || '-'}<br>`;
    htmlContent += `<strong>Tanggal Rapat:</strong> ${tanggalRapat}<br>`;
    htmlContent += `<strong>Pemegang Draf:</strong> ${escapeHtml(pemegangDraf || '-')}<br><br>`;
    
    htmlContent += `<strong>Status:</strong> `;
    const statusText = status === 'Diterima' ? 'Diterima' : 'Dikembalikan';
    const statusClass = status === 'Diterima' ? 'status-selesai' : 'status-proses';
    htmlContent += `<span class="status-badge ${statusClass}">${statusText}</span><br>`;
    
    if (status === 'Dikembalikan' && alasanPengembalianDraf) {
      htmlContent += `<br><strong>Alasan Pengembalian Draf:</strong><br>`;
      htmlContent += `<div style="margin: 10px 0; padding: 10px; background: var(--panel-color); border: 1px solid var(--border-color); border-radius: 5px; white-space: pre-wrap; color: var(--text-color); word-wrap: break-word; word-break: break-word;">${escapeHtml(alasanPengembalianDraf).replace(/\n/g, '<br>')}</div>`;
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

