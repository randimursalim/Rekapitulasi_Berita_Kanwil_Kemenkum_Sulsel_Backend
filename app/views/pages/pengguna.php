<?php
// app/views/pages/pengguna.php
?>
<div class="overview">
    <div class="title">
        <i class="uil uil-users-alt"></i>
        <span class="text">Manajemen Pengguna</span>
    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-pengguna'">
            <i class="uil uil-plus"></i> Tambah Pengguna
        </button>
    </div>

    <!-- Data Pengguna -->
    <div class="activity-wrapper" style="margin-top:20px;">
        <div class="activity">
            <div class="activity-data" id="penggunaResults">
                <!-- Data akan dimuat via AJAX -->
                <div style="text-align: center; padding: 20px;">
                    <p>Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination akan di-generate via JavaScript -->
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Global variables
  let currentPage = 1;
  let totalPages = 1;
  let totalData = 0;
  let itemsPerPage = 10;
  let currentFilters = {
    search: ''
  };

  // DOM elements
  const container = document.getElementById('penggunaResults');
  const paginationContainer = document.getElementById('pagination');

  // Check if required elements exist
  if (!container || !paginationContainer) {
    console.error('Required DOM elements not found');
    return;
  }

  // Initialize the page
  loadPengguna(1);

  // Expose functions globally untuk digunakan oleh header.php
  window.loadPenggunaArsip = loadPengguna;
  window.setCurrentFiltersPengguna = function(filters) {
    currentFilters = { ...currentFilters, ...filters };
    currentPage = 1;
    loadPengguna(currentPage);
  };

  // Load pengguna with pagination
  async function loadPengguna(page = 1) {
    if (!container) return;
    
    try {
      // Show loading
      container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

      // Build query parameters
      const params = new URLSearchParams({
        page: page,
        search: currentFilters.search
      });

      const response = await fetch(`ajax/fetch_pengguna.php?${params}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();

      if (!result.success) {
        container.innerHTML = `<p style="color:red;">Gagal memuat data pengguna: ${result.error || 'Unknown error'}</p>`;
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
      console.error('Error loading pengguna:', error);
      container.innerHTML = '<p style="color:red;">Terjadi kesalahan saat memuat data.</p>';
    }
  }

  // Render data table
  function renderData(data) {
    if (!container) return;
    
    if (data.length === 0) {
      container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="uil uil-users-alt" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada pengguna yang terdaftar</span></div>';
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

      <div class="data name">
        <span class="data-title">Nama</span>
        ${data.map(p => `<span class="data-list">${p.nama}</span>`).join('')}
      </div>

      <div class="data username">
        <span class="data-title">Username</span>
        ${data.map(p => `<span class="data-list">${p.username}</span>`).join('')}
      </div>

      <div class="data role">
        <span class="data-title">Role</span>
        ${data.map(p => `<span class="data-list">${p.role}</span>`).join('')}
      </div>

      <div class="data actions">
        <span class="data-title">Aksi</span>
        ${data.map(p => `
          <span class="data-list">
            <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-pengguna&id=${p.id_pengguna}'">
              <i class="uil uil-edit"></i>
            </button>
            <button class="btn-action-aksi delete" onclick="hapusPengguna(${p.id_pengguna}, '${p.nama.replace(/'/g, "\\'")}')">
              <i class="uil uil-trash-alt"></i>
            </button>
          </span>
        `).join('')}
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
          loadPengguna(currentPage);
        }
      });
    });
  }

  // Fungsi hapus pengguna
  function hapusPengguna(id, nama) {
    Swal.fire({
      title: 'Apakah kamu yakin?',
      text: `Kamu akan menghapus pengguna "${nama}"`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('index.php?page=hapus-pengguna', {
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
              loadPengguna(currentPage);
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

  // Expose functions globally
  window.hapusPengguna = hapusPengguna;

  // Check for success message from URL parameters
  const urlParams = new URLSearchParams(window.location.search);
  const status = urlParams.get('status');
  
  if (status === 'success') {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Data pengguna berhasil diperbarui.',
      showConfirmButton: false,
      timer: 2000
    }).then(() => {
      // Remove status parameter from URL
      const newUrl = window.location.pathname + '?page=pengguna';
      window.history.replaceState({}, document.title, newUrl);
    });
  }
});
</script>
