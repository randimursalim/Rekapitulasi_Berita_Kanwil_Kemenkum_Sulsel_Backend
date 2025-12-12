// Jadwal Peminjaman Ruangan Management
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
  const container = document.getElementById('peminjamanRuanganResults');
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
  loadPeminjamanRuangan(1);
  attachEventListeners();

  // Expose functions globally untuk digunakan oleh header.php
  window.loadPeminjamanRuanganArsip = loadPeminjamanRuangan;
  window.setCurrentFiltersPeminjamanRuangan = function(filters) {
    currentFilters = { ...currentFilters, ...filters };
    currentPage = 1;
    loadPeminjamanRuangan(currentPage);
  };

  // Event listeners
  function attachEventListeners() {
    // Filter button
    if (filterBtn && startDate && endDate) {
      filterBtn.addEventListener('click', function() {
        currentFilters.startDate = startDate.value;
        currentFilters.endDate = endDate.value;
        currentPage = 1;
        loadPeminjamanRuangan(currentPage);
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
        loadPeminjamanRuangan(currentPage);
      });
    }
  }

  // Load peminjaman ruangan with pagination
  async function loadPeminjamanRuangan(page = 1) {
    if (!container) return;
    
    try {
      // Show loading
      container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

      // Build query parameters
      const params = new URLSearchParams({
        page: page,
        search: currentFilters.search,
        startDate: currentFilters.startDate,
        endDate: currentFilters.endDate
      });

      const response = await fetch(`ajax/fetch_peminjaman_ruangan.php?${params}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();

      if (!result.success) {
        container.innerHTML = `<p style="color:red;">Gagal memuat data peminjaman ruangan: ${result.error || 'Unknown error'}</p>`;
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
      container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="fas fa-door-open" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada peminjaman ruangan yang dijadwalkan</span></div>';
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

      <div class="data peminjam">
        <span class="data-title">Nama Peminjam</span>
        ${data.map(p => `<span class="data-list"><span class="text-content">${p.nama_peminjam || '-'}</span></span>`).join('')}
      </div>

      <div class="data ruangan">
        <span class="data-title">Nama Ruangan</span>
        ${data.map(p => `<span class="data-list"><span class="text-content">${p.nama_ruangan || '-'}</span></span>`).join('')}
      </div>

      <div class="data kegiatan">
        <span class="data-title">Kegiatan</span>
        ${data.map(p => `<span class="data-list"><span class="text-content">${p.kegiatan || '-'}</span></span>`).join('')}
      </div>

      <div class="data tanggal">
        <span class="data-title">Tanggal Kegiatan</span>
        ${data.map(p => `<span class="data-list">${formatDate(p.tanggal_kegiatan)}</span>`).join('')}
      </div>

      <div class="data waktu">
        <span class="data-title">Waktu Kegiatan</span>
        ${data.map(p => `<span class="data-list">${formatTime(p.waktu_kegiatan)}</span>`).join('')}
      </div>

      <div class="data actions">
        <span class="data-title">Aksi</span>
        ${data.map((p, index) => `
          <span class="data-list">
            <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-peminjaman-ruangan&id=${p.id}'">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-action-aksi delete" onclick="hapusPeminjamanRuangan(${p.id}, '${(p.nama_peminjam || '').replace(/'/g, "\\'")}')">
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

  function formatTime(timeStr) {
    if (!timeStr) return '-';
    return timeStr.substring(0, 5); // HH:MM
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
          loadPeminjamanRuangan(currentPage);
        }
      });
    });
  }

  // Fungsi hapus peminjaman ruangan
  function hapusPeminjamanRuangan(id, nama) {
    Swal.fire({
      title: 'Apakah kamu yakin?',
      text: `Kamu akan menghapus peminjaman ruangan "${nama}"`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        // Kirim request hapus
        fetch('index.php?page=hapus-peminjaman-ruangan', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `id=${id}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: data.message,
              showConfirmButton: false,
              timer: 2000
            }).then(() => {
              loadPeminjamanRuangan(currentPage);
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal!',
              text: data.message || 'Gagal menghapus data'
            });
          }
        })
        .catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat menghapus data'
          });
        });
      }
    });
  }

  // Expose hapus function globally
  window.hapusPeminjamanRuangan = hapusPeminjamanRuangan;
});


