<?php
// app/views/pages/jadwal-kegiatan.php
?>

<div class="overview">
    <div class="title">
        <i class="uil uil-schedule"></i>
        <span class="text">Jadwal Kegiatan</span>
    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-kegiatan'">
            <i class="uil uil-plus"></i> Tambah Kegiatan
        </button>
    </div>

    <!-- Filter -->
    <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom: 20px;">
        <label for="startDate">Tanggal:</label>
        <input type="date" id="startDate">
        <span>-</span>
        <input type="date" id="endDate">
        <button id="filterBtn">Terapkan</button>
        <button id="resetBtn">Reset</button>
    </div>

    <!-- Tabel Jadwal Kegiatan -->
    <div class="activity-wrapper" style="margin-top: 20px;">
        <div class="activity">
            <div class="activity-data" id="kegiatanResults">
                <!-- Data akan dimuat via AJAX -->
                <div style="text-align: center; padding: 20px;">
                    <p>Memuat data...</p>
            </div>
            </div>
        </div>
    </div>

    <!-- Modal Keterangan -->
    <div id="keteranganModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detail Keterangan Kegiatan</h3>
            <div id="modalText" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto; padding: 15px; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: 8px; margin: 15px 0; color: var(--text-color); line-height: 1.6; font-size: 14px;"></div>
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
    search: '',
    startDate: '',
    endDate: ''
  };

  // DOM elements
  const container = document.getElementById('kegiatanResults');
  const paginationContainer = document.getElementById('pagination');
  const filterBtn = document.getElementById('filterBtn');
  const resetBtn = document.getElementById('resetBtn');
  const startDate = document.getElementById('startDate');
  const endDate = document.getElementById('endDate');

  // Check if required elements exist
  if (!container || !paginationContainer) {
    console.error('Required DOM elements not found');
    return;
  }

  // Initialize the page
  loadKegiatan(1);
  attachEventListeners();

  // Expose functions globally untuk digunakan oleh header.php
  window.loadKegiatanArsip = loadKegiatan;
  window.setCurrentFiltersKegiatan = function(filters) {
    currentFilters = { ...currentFilters, ...filters };
    currentPage = 1;
    loadKegiatan(currentPage);
  };

  // Event listeners
  function attachEventListeners() {
    // Filter button
    if (filterBtn && startDate && endDate) {
      filterBtn.addEventListener('click', function() {
        currentFilters.startDate = startDate.value;
        currentFilters.endDate = endDate.value;
        currentPage = 1;
        loadKegiatan(currentPage);
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
        loadKegiatan(currentPage);
      });
    }
  }

  // Load kegiatan with pagination
  async function loadKegiatan(page = 1) {
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

      const response = await fetch(`ajax/fetch_kegiatan.php?${params}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();

      if (!result.success) {
        container.innerHTML = `<p style="color:red;">Gagal memuat data kegiatan: ${result.error || 'Unknown error'}</p>`;
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
      console.error('Error loading kegiatan:', error);
      container.innerHTML = '<p style="color:red;">Terjadi kesalahan saat memuat data.</p>';
    }
  }

  // Render data table
  function renderData(data) {
    if (!container) return;
    
    if (data.length === 0) {
      container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="uil uil-calendar-alt" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada kegiatan yang dijadwalkan</span></div>';
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

      <div class="data kegiatan">
        <span class="data-title">Nama Kegiatan</span>
        ${data.map(k => `<span class="data-list">${k.nama_kegiatan}</span>`).join('')}
      </div>

      <div class="data tanggal">
        <span class="data-title">Tanggal</span>
        ${data.map(k => `<span class="data-list">${formatDate(k.tanggal)}</span>`).join('')}
      </div>

      <div class="data waktu">
        <span class="data-title">Waktu</span>
        ${data.map(k => `<span class="data-list">${formatTime(k.jam_mulai)}-${formatTime(k.jam_selesai)}</span>`).join('')}
      </div>

      <div class="data keterangan">
        <span class="data-title">Keterangan</span>
        ${data.map(k => `<span class="data-list">${k.keterangan ? (k.keterangan.length > 50 ? k.keterangan.substring(0, 50) + '...' : k.keterangan) : '-'}</span>`).join('')}
      </div>

      <div class="data status">
        <span class="data-title">Status</span>
        ${data.map(k => {
          const statusInfo = getDynamicStatus(k);
          return `<span class="data-list ${statusInfo.class}" data-status="${k.status}" data-tanggal="${k.tanggal}" data-jam-mulai="${k.jam_mulai}" data-jam-selesai="${k.jam_selesai}">${statusInfo.text}</span>`;
        }).join('')}
      </div>

      <div class="data actions">
        <span class="data-title">Aksi</span>
        ${data.map((k, index) => `
          <span class="data-list">
            <button class="btn-action-aksi view" onclick="showKeterangan('${k.keterangan ? k.keterangan.replace(/'/g, "\\'").replace(/"/g, '\\"').replace(/\n/g, '\\n').replace(/\r/g, '\\r') : ''}')">
              <i class="uil uil-eye"></i>
            </button>
            <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-kegiatan&id=${k.id_kegiatan}'">
              <i class="uil uil-edit"></i>
            </button>
            <button class="btn-action-aksi delete" onclick="hapusKegiatan(${k.id_kegiatan}, '${k.nama_kegiatan.replace(/'/g, "\\'")}')">
              <i class="uil uil-trash-alt"></i>
            </button>
          </span>
        `).join('')}
      </div>
    `;

    container.innerHTML = html;
    updateStatusRealTime();
  }

  // Helper functions
  function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID');
  }

  function formatTime(timeStr) {
    return timeStr.substring(0, 5); // HH:MM
  }

  function getDynamicStatus(kegiatan) {
    const now = new Date();
    const tanggalKegiatan = new Date(kegiatan.tanggal);
    const jamMulai = new Date(kegiatan.tanggal + ' ' + kegiatan.jam_mulai);
    const jamSelesai = new Date(kegiatan.tanggal + ' ' + kegiatan.jam_selesai);
    
    // Jika status di database bukan "Selesai", "Ditunda", atau "Dibatalkan"
    if (!['Selesai', 'Ditunda', 'Dibatalkan'].includes(kegiatan.status)) {
      if (now > jamSelesai) {
        return { text: 'Selesai', class: 'status-selesai' };
      } else if (now >= jamMulai && now <= jamSelesai) {
        return { text: 'Sedang Berlangsung', class: 'status-berlangsung' };
      } else {
        return { text: 'Belum Dimulai', class: 'status-belum' };
      }
    } else {
      // Status dari database
      switch(kegiatan.status) {
        case 'Selesai': return { text: 'Selesai', class: 'status-selesai' };
        case 'Ditunda': return { text: 'Ditunda', class: 'status-ditunda' };
        case 'Dibatalkan': return { text: 'Dibatalkan', class: 'status-dibatalkan' };
        default: return { text: kegiatan.status, class: 'status-belum' };
      }
    }
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
          loadKegiatan(currentPage);
        }
      });
    });
  }

// Fungsi tampilkan modal
  function showKeterangan(keterangan) {
    // Decode escape characters untuk display
    const decodedKeterangan = keterangan
      .replace(/\\n/g, '\n')
      .replace(/\\r/g, '\r')
      .replace(/\\'/g, "'")
      .replace(/\\"/g, '"');
    
    document.getElementById("modalText").textContent = decodedKeterangan || 'Tidak ada keterangan';
    document.getElementById("keteranganModal").style.display = "block";
}

// Fungsi tutup modal
function closeModal() {
  document.getElementById("keteranganModal").style.display = "none";
}

  // Fungsi hapus kegiatan
  function hapusKegiatan(id, nama) {
    Swal.fire({
      title: 'Apakah kamu yakin?',
      text: `Kamu akan menghapus kegiatan "${nama}"`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        fetch('index.php?page=hapus-kegiatan', {
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
              loadKegiatan(currentPage);
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

  // Fungsi untuk update status secara real-time
  function updateStatusRealTime() {
    const statusElements = document.querySelectorAll('.data-list[data-status]');
    const now = new Date();
    
    statusElements.forEach(element => {
      const dbStatus = element.getAttribute('data-status');
      const tanggal = element.getAttribute('data-tanggal');
      const jamMulai = element.getAttribute('data-jam-mulai');
      const jamSelesai = element.getAttribute('data-jam-selesai');
      
      if (!['Selesai', 'Ditunda', 'Dibatalkan'].includes(dbStatus)) {
        const jamMulaiDateTime = new Date(tanggal + ' ' + jamMulai);
        const jamSelesaiDateTime = new Date(tanggal + ' ' + jamSelesai);
        
        let newStatus = dbStatus;
        let newClass = 'status-belum';
        
        if (now > jamSelesaiDateTime) {
          newStatus = 'Selesai';
          newClass = 'status-selesai';
        } else if (now >= jamMulaiDateTime && now <= jamSelesaiDateTime) {
          newStatus = 'Sedang Berlangsung';
          newClass = 'status-berlangsung';
        } else {
          newStatus = 'Belum Dimulai';
          newClass = 'status-belum';
        }
        
        if (element.textContent !== newStatus) {
          element.textContent = newStatus;
          element.className = 'data-list ' + newClass;
        }
      }
    });
  }

  // Update status setiap menit
  setInterval(updateStatusRealTime, 60000);

// Tutup modal dengan tombol close
document.querySelector("#keteranganModal .close").addEventListener("click", closeModal);

// Tutup modal jika klik di luar konten
window.addEventListener("click", function(event) {
  const modal = document.getElementById("keteranganModal");
  if (event.target === modal) closeModal();
  });

  // Expose functions globally
  window.showKeterangan = showKeterangan;
  window.hapusKegiatan = hapusKegiatan;
});
</script>
