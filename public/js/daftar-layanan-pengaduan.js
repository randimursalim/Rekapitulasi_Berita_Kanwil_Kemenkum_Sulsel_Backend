// Daftar Layanan Pengaduan Management
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
  const container = document.getElementById('layananPengaduanResults');
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
  loadLayananPengaduan(1);
  attachEventListeners();

  // Expose functions globally untuk digunakan oleh live-search.js
  window.loadLayananPengaduanArsip = loadLayananPengaduan;
  window.setCurrentFiltersLayananPengaduan = function(filters) {
    currentFilters = { ...currentFilters, ...filters };
    currentPage = 1;
    loadLayananPengaduan(currentPage);
  };

  // Event listeners
  function attachEventListeners() {
    // Filter button
    if (filterBtn && startDate && endDate) {
      filterBtn.addEventListener('click', function() {
        currentFilters.startDate = startDate.value;
        currentFilters.endDate = endDate.value;
        currentPage = 1;
        loadLayananPengaduan(currentPage);
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
        loadLayananPengaduan(currentPage);
      });
    }
  }

  // Load layanan pengaduan with pagination
  async function loadLayananPengaduan(page = 1) {
    if (!container) return;
    
    try {
      // Show loading
      container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

      // Get BASE_URL untuk path dinamis
      const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
      const fetchUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/ajax/fetch_layanan_pengaduan.php') : 'ajax/fetch_layanan_pengaduan.php';

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
        container.innerHTML = `<p style="color:red;">Gagal memuat data layanan pengaduan: ${result.error || 'Unknown error'}</p>`;
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
      container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="fas fa-gavel" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada data layanan pengaduan</span></div>';
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
        ${data.map(lp => `<span class="data-list">${lp.no_register_pengaduan || '-'}</span>`).join('')}
      </div>

      <div class="data nama">
        <span class="data-title">Nama</span>
        ${data.map(lp => {
          const nama = lp.nama || '-';
          const namaDisplay = nama.length > 25 ? nama.substring(0, 25) + '...' : nama;
          return `<span class="data-list" title="${escapeHtml(nama)}"><span class="text-content">${escapeHtml(namaDisplay)}</span></span>`;
        }).join('')}
      </div>

      <div class="data judul">
        <span class="data-title">Judul Laporan</span>
        ${data.map(lp => {
          const judul = lp.judul_laporan || '-';
          const judulDisplay = judul.length > 35 ? judul.substring(0, 35) + '...' : judul;
          return `<span class="data-list" title="${escapeHtml(judul)}"><span class="text-content">${escapeHtml(judulDisplay)}</span></span>`;
        }).join('')}
      </div>

      <div class="data tanggal">
        <span class="data-title">Tanggal Pengaduan</span>
        ${data.map(lp => `<span class="data-list">${formatDate(lp.tanggal_pengaduan)}</span>`).join('')}
      </div>

      <div class="data kategori">
        <span class="data-title">Kategori</span>
        ${data.map(lp => `<span class="data-list">${lp.kategori_laporan || '-'}</span>`).join('')}
      </div>

      <div class="data jenis">
        <span class="data-title">Jenis Aduan</span>
        ${data.map(lp => `<span class="data-list">${lp.jenis_aduan || '-'}</span>`).join('')}
      </div>

      <div class="data tindak-lanjut">
        <span class="data-title">Tindak Lanjut</span>
        ${data.map(lp => {
          const status = lp.tindak_lanjut || 'belum diproses';
          const statusClass = status === 'selesai' ? 'status-selesai' : status === 'proses' ? 'status-proses' : 'status-belum';
          const statusText = status === 'selesai' ? 'Selesai' : status === 'proses' ? 'Proses' : 'Belum Diproses';
          return `<span class="data-list"><span class="status-badge ${statusClass}">${statusText}</span></span>`;
        }).join('')}
      </div>

      <div class="data actions">
        <span class="data-title">Aksi</span>
        ${data.map((lp, index) => `
          <span class="data-list">
            <button class="btn-action-aksi view" data-keterangan="${btoa(unescape(encodeURIComponent(lp.keterangan || '')))}" onclick="showDetailLayananPengaduanFromButton(this, ${lp.id}, '${escapeHtml(lp.no_register_pengaduan || '')}', '${escapeHtml(lp.nama || '')}', '${escapeHtml(lp.alamat || '')}', '${lp.jenis_tanda_pengenal || ''}', '${escapeHtml(lp.jenis_tanda_pengenal_lainnya || '')}', '${escapeHtml(lp.no_tanda_pengenal || '')}', '${escapeHtml(lp.no_telp || '')}', '${escapeHtml(lp.judul_laporan || '')}', '${escapeHtml(lp.isi_laporan || '')}', '${formatDate(lp.tanggal_kejadian)}', '${escapeHtml(lp.lokasi_kejadian || '')}', '${lp.kategori_laporan || ''}', '${lp.jenis_aduan || ''}', '${escapeHtml(lp.jenis_aduan_lainnya || '')}', '${formatDate(lp.tanggal_pengaduan)}', '${lp.tindak_lanjut || 'belum diproses'}')">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-layanan-pengaduan&id=${lp.id}'">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-action-aksi delete" onclick="hapusLayananPengaduan(${lp.id}, '${escapeHtml(lp.no_register_pengaduan || '')}')">
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

  function basename(path) {
    return path.split('/').pop().split('\\').pop();
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
          loadLayananPengaduan(currentPage);
        }
      });
    });
  }

  // Fungsi tampilkan modal detail (dari button dengan data attribute)
  function showDetailLayananPengaduanFromButton(button, id, noRegister, nama, alamat, jenisTandaPengenal, jenisTandaPengenalLainnya, noTandaPengenal, noTelp, judulLaporan, isiLaporan, tanggalKejadian, lokasiKejadian, kategoriLaporan, jenisAduan, jenisAduanLainnya, tanggalPengaduan, tindakLanjut) {
    // Ambil keterangan dari data attribute (base64 encoded)
    const keteranganEncoded = button.getAttribute('data-keterangan') || '';
    let keterangan = '';
    if (keteranganEncoded) {
      try {
        keterangan = decodeURIComponent(escape(atob(keteranganEncoded)));
      } catch (e) {
        // Error decoding keterangan - handled silently
        keterangan = '';
      }
    }
    showDetailLayananPengaduan(id, noRegister, nama, alamat, jenisTandaPengenal, jenisTandaPengenalLainnya, noTandaPengenal, noTelp, judulLaporan, isiLaporan, tanggalKejadian, lokasiKejadian, kategoriLaporan, jenisAduan, jenisAduanLainnya, tanggalPengaduan, tindakLanjut, keterangan);
  }
  
  // Expose fungsi ke global scope untuk digunakan dari onclick
  window.showDetailLayananPengaduanFromButton = showDetailLayananPengaduanFromButton;

  // Fungsi tampilkan modal detail
  function showDetailLayananPengaduan(id, noRegister, nama, alamat, jenisTandaPengenal, jenisTandaPengenalLainnya, noTandaPengenal, noTelp, judulLaporan, isiLaporan, tanggalKejadian, lokasiKejadian, kategoriLaporan, jenisAduan, jenisAduanLainnya, tanggalPengaduan, tindakLanjut, keterangan) {
    const modalContent = document.getElementById('modalContent');
    if (!modalContent) return;

    // Build HTML content untuk support file download
    let htmlContent = '';
    
    htmlContent += `<strong>No. Register Pengaduan:</strong> ${noRegister || '-'}<br>`;
    htmlContent += `<strong>Tanggal Pengaduan:</strong> ${tanggalPengaduan}<br><br>`;
    
    htmlContent += `<strong>DATA PELAPOR:</strong><br>`;
    htmlContent += `Nama: ${nama || '-'}<br>`;
    htmlContent += `Alamat: ${alamat || '-'}<br>`;
    if (jenisTandaPengenal === 'LAINNYA' && jenisTandaPengenalLainnya) {
      htmlContent += `Jenis Tanda Pengenal: ${jenisTandaPengenal} - ${jenisTandaPengenalLainnya}<br>`;
    } else {
      htmlContent += `Jenis Tanda Pengenal: ${jenisTandaPengenal || '-'}<br>`;
    }
    htmlContent += `No. Tanda Pengenal: ${noTandaPengenal || '-'}<br>`;
    htmlContent += `No. Telepon: ${noTelp || '-'}<br><br>`;
    
    htmlContent += `<strong>DATA LAPORAN:</strong><br>`;
    htmlContent += `Judul Laporan: ${judulLaporan || '-'}<br>`;
    htmlContent += `Isi Laporan:<br>${isiLaporan || '-'}<br><br>`;
    htmlContent += `Tanggal Kejadian: ${tanggalKejadian}<br>`;
    htmlContent += `Lokasi Kejadian: ${lokasiKejadian || '-'}<br>`;
    htmlContent += `Kategori Laporan: ${kategoriLaporan || '-'}<br>`;
    if (jenisAduan === 'Lainnya' && jenisAduanLainnya) {
      htmlContent += `Jenis Aduan: ${jenisAduan} - ${jenisAduanLainnya}<br>`;
    } else {
      htmlContent += `Jenis Aduan: ${jenisAduan || '-'}<br>`;
    }
    
    htmlContent += `<br><strong>TINDAK LANJUT:</strong><br>`;
    const statusText = tindakLanjut === 'selesai' ? 'Selesai' : tindakLanjut === 'proses' ? 'Proses' : 'Belum Diproses';
    const statusClass = tindakLanjut === 'selesai' ? 'status-selesai' : tindakLanjut === 'proses' ? 'status-proses' : 'status-belum';
    htmlContent += `Status: <span class="status-badge ${statusClass}">${statusText}</span><br>`;
    
    // Handle keterangan (bisa teks saja, file saja, atau teks + file)
    if (keterangan) {
      htmlContent += `<br><strong>KETERANGAN:</strong><br>`;
      
      // Parse keterangan: format bisa "TEXT\nFILE: path" atau hanya teks atau hanya file
      let keteranganText = '';
      let filePath = '';
      
      // Pastikan keterangan adalah string
      if (typeof keterangan !== 'string') {
        keterangan = String(keterangan || '');
      }
      
      // Decode HTML entities jika ada
      let decodedKeterangan = keterangan;
      if (keterangan.includes('&lt;') || keterangan.includes('&gt;') || keterangan.includes('&amp;') || keterangan.includes('&quot;')) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = keterangan;
        decodedKeterangan = tempDiv.textContent || tempDiv.innerText || keterangan;
      }
      
      // Replace escaped newlines (\n) dengan actual newline
      decodedKeterangan = decodedKeterangan.replace(/\\n/g, '\n');
      
      // Cek apakah ada "FILE:" di keterangan (case insensitive)
      const filePattern = /FILE:\s*/i;
      const fileMatch = decodedKeterangan.match(filePattern);
      
      if (fileMatch) {
        // Format: "TEXT\nFILE: path|original_name" atau "FILE: path|original_name"
        const fileIndex = fileMatch.index;
        keteranganText = decodedKeterangan.substring(0, fileIndex).trim();
        filePath = decodedKeterangan.substring(fileIndex + fileMatch[0].length).trim();
      } else if (decodedKeterangan.includes('storage/uploads/') || decodedKeterangan.match(/\.(pdf|jpg|jpeg|png|doc|docx)$/i)) {
        // Format lama: hanya file (tanpa prefix FILE:)
        filePath = decodedKeterangan.trim();
      } else {
        // Hanya teks
        keteranganText = decodedKeterangan.trim();
      }
      
      // Parse filePath untuk dapatkan path dan original name (format: "path|original_name")
      let filePathOnly = filePath;
      let originalFileName = '';
      if (filePath.includes('|')) {
        const fileParts = filePath.split('|');
        filePathOnly = fileParts[0];
        originalFileName = fileParts[1] || basename(filePathOnly);
      } else {
        originalFileName = basename(filePathOnly);
      }
      
      // Tampilkan teks jika ada
      if (keteranganText) {
        htmlContent += `<div style="margin: 10px 0; padding: 10px; background: #f9f9f9; border-radius: 5px; white-space: pre-wrap;">${escapeHtml(keteranganText).replace(/\n/g, '<br>')}</div>`;
      }
      
      // Tampilkan file jika ada
      if (filePath) {
        // Gunakan originalFileName jika ada, jika tidak gunakan basename dari path
        const displayFileName = originalFileName || filePathOnly.split('/').pop().split('\\').pop();
        const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
        
        // Gunakan endpoint download untuk proper file handling dengan nama file asli
        const downloadUrl = baseUrl ? 
          `${baseUrl}/index.php?page=download-keterangan&file=${encodeURIComponent(filePath)}` : 
          `index.php?page=download-keterangan&file=${encodeURIComponent(filePath)}`;
        
        // Deteksi tipe file untuk icon berdasarkan original name atau extension
        const fileExt = displayFileName.split('.').pop().toLowerCase();
        let fileIcon = 'fa-file';
        if (fileExt === 'pdf') fileIcon = 'fa-file-pdf';
        else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) fileIcon = 'fa-file-image';
        else if (['doc', 'docx'].includes(fileExt)) fileIcon = 'fa-file-word';
        
        htmlContent += `<div style="margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 5px; border-left: 4px solid #0E4BF1;">`;
        htmlContent += `<p style="margin: 0 0 10px 0;"><i class="fas ${fileIcon}" style="color: #0E4BF1; margin-right: 8px;"></i><strong>${escapeHtml(displayFileName)}</strong></p>`;
        htmlContent += `<a href="${downloadUrl}" style="display: inline-block; padding: 10px 20px; background: #0E4BF1; color: white; text-decoration: none; border-radius: 5px; font-weight: 500; transition: all 0.3s ease;">`;
        htmlContent += `<i class="fas fa-download" style="margin-right: 8px;"></i>Download File`;
        htmlContent += `</a>`;
        htmlContent += `</div>`;
      }
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

  // Fungsi hapus layanan pengaduan
  function hapusLayananPengaduan(id, noRegister) {
    Swal.fire({
      title: 'Apakah kamu yakin?',
      text: `Kamu akan menghapus layanan pengaduan dengan No. Register "${noRegister}"`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
        const deleteUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/index.php?page=hapus-layanan-pengaduan') : 'index.php?page=hapus-layanan-pengaduan';
        
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
              loadLayananPengaduan(currentPage);
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

  // Expose functions lainnya ke global scope
  window.showDetailLayananPengaduan = showDetailLayananPengaduan;
  window.hapusLayananPengaduan = hapusLayananPengaduan;
});

