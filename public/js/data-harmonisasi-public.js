// Data Harmonisasi Public - untuk halaman publik tanpa login
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
  const searchInput = document.getElementById('searchInput');

  if (!container || !paginationContainer) {
    return;
  }

  // Initialize the page
  loadHarmonisasi(1);
  attachEventListeners();

  // Event listeners
  function attachEventListeners() {
    // Search input - live search dengan debounce
    if (searchInput) {
      let searchTimeout;
      searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          currentFilters.search = this.value.trim();
          currentPage = 1;
          loadHarmonisasi(currentPage);
        }, 500); // Debounce 500ms
      });
    }

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

      // Update pagination info
      if (result.pagination) {
        totalPages = result.pagination.totalPages;
        totalData = result.pagination.totalData;
        currentPage = result.pagination.currentPage;
      }

      const data = result.data || [];

      if (data.length === 0) {
        container.innerHTML = '<div style="text-align: center; padding: 40px; grid-column: 1 / -1;"><span style="color: #333; font-size: 1.1rem;"><i class="fas fa-balance-scale" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada data harmonisasi</span></div>';
        paginationContainer.innerHTML = '';
        return;
      }

      // Render data
      renderData(data);

      // Render pagination
      renderPagination();

    } catch (error) {
      console.error('Error loading harmonisasi:', error);
      container.innerHTML = '<div style="color:red; text-align: center; padding: 20px;">Terjadi kesalahan saat memuat data.</div>';
    }
  }

  // Render data table - menggunakan struktur tabel HTML seperti di landing.php
  function renderData(data) {
    if (!container) return;

    function formatDate(dateStr) {
      if (!dateStr) return '-';
      const date = new Date(dateStr);
      return date.toLocaleDateString('id-ID');
    }

    function escapeHtml(text) {
      if (!text) return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    const startIndex = (currentPage - 1) * itemsPerPage;

    const html = `
      <div class="harmonisasi-preview-table">
        <table class="harmonisasi-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Judul Rancangan</th>
              <th>Pemrakarsa</th>
              <th>Pemerintah Daerah</th>
              <th>Tanggal Surat Diterima</th>
              <th>Tanggal Rapat</th>
              <th>Pemegang Draf</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            ${data.map((h, index) => {
              const judul = h.judul_rancangan || '-';
              const pemrakarsa = h.pemrakarsa || '-';
              const status = h.status || 'Diterima';
              const statusClass = status === 'Diterima' ? 'status-selesai' : 'status-proses';
              const statusText = status === 'Diterima' ? 'Diterima' : 'Dikembalikan';
              
              return `
                <tr>
                  <td data-label="">${startIndex + index + 1}</td>
                  <td class="text-full" data-label="Judul Rancangan" title="${escapeHtml(judul)}">
                    <div class="cell-content">${escapeHtml(judul)}</div>
                  </td>
                  <td class="text-full" data-label="Pemrakarsa" title="${escapeHtml(pemrakarsa)}">
                    <div class="cell-content">${escapeHtml(pemrakarsa)}</div>
                  </td>
                  <td data-label="Pemerintah Daerah">${escapeHtml(h.pemerintah_daerah || '-')}</td>
                  <td data-label="Tanggal Surat Diterima">${formatDate(h.tanggal_surat_diterima)}</td>
                  <td data-label="Tanggal Rapat">${formatDate(h.tanggal_rapat)}</td>
                  <td data-label="Pemegang Draf">${escapeHtml(h.pemegang_draf || '-')}</td>
                  <td data-label="Status"><span class="status-badge ${statusClass}">${statusText}</span></td>
                  <td data-label="Aksi">
                    <button class="btn-view-detail" onclick="showDetailHarmonisasi(${h.id}, '${escapeHtml(judul).replace(/'/g, "\\'")}', '${escapeHtml(pemrakarsa).replace(/'/g, "\\'")}', '${escapeHtml(h.pemerintah_daerah || '').replace(/'/g, "\\'")}', '${formatDate(h.tanggal_surat_diterima)}', '${formatDate(h.tanggal_rapat)}', '${escapeHtml(h.pemegang_draf || '').replace(/'/g, "\\'")}', '${h.status || 'Diterima'}', '${escapeHtml(h.alasan_pengembalian_draf || '').replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r')}')">
                      <i class="fas fa-eye"></i> Detail
                    </button>
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
  window.showDetailHarmonisasi = function(id, judulRancangan, pemrakarsa, pemerintahDaerah, tanggalSuratDiterima, tanggalRapat, pemegangDraf, status, alasanPengembalianDraf) {
    const modalContent = document.getElementById('modalContent');
    const modal = document.getElementById('detailModal');
    if (!modalContent || !modal) return;

    function escapeHtml(text) {
      if (!text) return '';
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    let htmlContent = '';
    
    htmlContent += `<strong>Judul Rancangan:</strong><br>`;
    htmlContent += `<div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; white-space: pre-wrap; color: #333; word-wrap: break-word; word-break: break-word;">${escapeHtml(judulRancangan || '-')}</div><br>`;
    htmlContent += `<strong>Pemrakarsa:</strong> ${escapeHtml(pemrakarsa) || '-'}<br>`;
    htmlContent += `<strong>Pemerintah Daerah:</strong> ${escapeHtml(pemerintahDaerah) || '-'}<br>`;
    htmlContent += `<strong>Tanggal Surat Diterima:</strong> ${tanggalSuratDiterima || '-'}<br>`;
    htmlContent += `<strong>Tanggal Rapat:</strong> ${tanggalRapat}<br>`;
    htmlContent += `<strong>Pemegang Draf:</strong> ${escapeHtml(pemegangDraf) || '-'}<br><br>`;
    
    htmlContent += `<strong>Status:</strong> `;
    const statusText = status === 'Diterima' ? 'Diterima' : 'Dikembalikan';
    const statusClass = status === 'Diterima' ? 'status-selesai' : 'status-proses';
    htmlContent += `<span class="status-badge ${statusClass}">${statusText}</span><br>`;
    
    if (status === 'Dikembalikan' && alasanPengembalianDraf) {
      htmlContent += `<br><strong>Alasan Pengembalian Draf:</strong><br>`;
      htmlContent += `<div style="margin: 10px 0; padding: 10px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; white-space: pre-wrap; color: #333; word-wrap: break-word; word-break: break-word;">${escapeHtml(alasanPengembalianDraf).replace(/\n/g, '<br>')}</div>`;
    }
    
    modalContent.innerHTML = htmlContent;
    modal.style.display = 'block';
  };

  // Fungsi tutup modal
  function closeModal() {
    const modal = document.getElementById('detailModal');
    if (modal) {
      modal.style.display = 'none';
    }
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
});
