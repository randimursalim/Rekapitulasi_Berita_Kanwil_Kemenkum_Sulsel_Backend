// Tamu Management
document.addEventListener('DOMContentLoaded', function () {
    // Global variables
    let currentPage = 1;
    let totalPages = 1;
    let totalData = 0;
    let itemsPerPage = 10;
    let currentFilters = {
        search: '',
        tahun: '',
        bulan: ''
    };

    // DOM elements
    const container = document.getElementById('tamuResults');
    const paginationContainer = document.getElementById('pagination');

    // Check if required elements exist
    if (!container || !paginationContainer) {
        return;
    }

    // Initialize the page
    loadTamu(1);

    // Expose functions globally untuk digunakan oleh header.php
    window.loadTamuArsip = loadTamu;
    window.setCurrentFiltersTamu = function (filters) {
        currentFilters = { ...currentFilters, ...filters };
        currentPage = 1;
        loadTamu(currentPage);
    };

    // Load Tamu with pagination
    async function loadTamu(page = 1) {
        if (!container) return;

        try {
            // Show loading
            container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

            // Build query parameters 
            const params = new URLSearchParams({
                page: page,
                search: currentFilters.search,
                tahun: currentFilters.tahun,
                bulan: currentFilters.bulan
            });

            const response = await fetch(`ajax/fetch_tamu.php?${params}`);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (!result.success) {
                container.innerHTML = `<p style="color:red;">Gagal memuat data tamu: ${result.error || 'Unknown error'}</p>`;
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
            container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="fas fa-users" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada tamu yang terdaftar</span></div>';
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

        <div class="data tanggal">
            <span class="data-title">Tanggal</span>
            ${data.map(t => `
                <span class="data-list">
                ${formatTanggal(t.tgl)}<br>
                ${t.jam ?? '-'}
                </span>
            `).join('')}
        </div>

        <div class="data nama">
          <span class="data-title">Nama Tamu</span>
          ${data.map(t => `<span class="data-list"><span class="text-content" title="${t.nama}">${t.nama}</span></span>`).join('')}
        </div>
  
        <div class="data kontak">
          <span class="data-title">Kontak</span>
          ${data.map(t => `
            <span class="data-list">
              <span class="text-content" title="${t.telp} | ${t.email ?? '-'}">
                ${t.telp}<br>
                <small>${t.email ?? '-'}</small>
              </span>
            </span>
          `).join('')}          
        </div>
  
        <div class="data alamat">
          <span class="data-title">Alamat</span>
          ${data.map(t => `<span class="data-list"><span class="text-content" title="${t.alamat}">${t.alamat}</span></span>`).join('')}
        </div>

        <div class="data tujuan">
          <span class="data-title">Maksud/Tujuan</span>
          ${data.map(t => `<span class="data-list"><span class="text-content" title="${t.tujuan}">${t.tujuan}</span></span>`).join('')}
        </div>
        
        <div class="data ttd">
            <span class="data-title">TTD</span>
            ${data.map(t => `
                <span class="data-list ttd">
                ${t.ttd
                    ? `<img 
                        src="${getImageUrl('storage/uploads/ttd/' + t.ttd)}" 
                        alt="TTD ${t.nama}" 
                        class="img-ttd img-preview"
                        data-caption="TTD ${t.nama}"
                    >`
                    : '-'}
                </span>
            `).join('')}
        </div>

        <div class="data foto">
            <span class="data-title">Foto</span>
            ${data.map(t => `
                <span class="data-list foto">
                ${t.foto
                    ? `<img 
                        src="${getImageUrl('storage/uploads/foto/' + t.foto)}"
                        alt="Foto ${t.nama}" 
                        class="img-foto img-preview"
                        data-caption="Foto ${t.nama}"
                    >`
                    : '-'}
                </span>
            `).join('')}
        </div>
  
        <div class="data actions">
          <span class="data-title">Aksi</span>
          ${data.map(t => `
            <span class="data-list">
              <button class="btn-action-aksi delete" onclick="hapusTamu(${t.id}, '${t.nama.replace(/'/g, "\\'")}')">
                <i class="fas fa-trash-alt"></i>
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
            btn.addEventListener('click', function () {
                const page = parseInt(this.dataset.page);
                if (page && page !== currentPage) {
                    currentPage = page;
                    loadTamu(currentPage);
                }
            });
        });
    }

    // fungsi tampilan tanggal
    function formatTanggal(tgl) {
        if (!tgl) return '-';
        const d = new Date(tgl);
        return d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });
    }

    // Fungsi hapus Tamu
    function hapusTamu(id, nama) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: `Kamu akan menghapus tamu "${nama}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('index.php?page=hapus-tamu', {
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
                                loadTamu(currentPage);
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
    window.hapusTamu = hapusTamu;

    // Check for success message from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data tamu berhasil diperbarui.',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            // Remove status parameter from URL
            const newUrl = window.location.pathname + '?page=tamu';
            window.history.replaceState({}, document.title, newUrl);
        });
    }

    // Filter button (bulan dan tahun)
    document.getElementById('btnCari').addEventListener('click', () => {
        const tahun = document.getElementById('filterTahun').value;
        const bulan = document.getElementById('filterBulan').value;

        window.setCurrentFiltersTamu({
            tahun: tahun,
            bulan: bulan
        });
    });

});

// Modal preview gambar
document.addEventListener('click', function (e) {
    const target = e.target;

    if (target.classList.contains('img-preview')) {
        const modal = document.getElementById('imagePreviewModal');
        const modalImg = document.getElementById('imgPreview');
        const caption = document.getElementById('imgCaption');

        modal.style.display = 'flex';
        modalImg.src = target.src;
        caption.textContent = target.dataset.caption || '';
    }

    if (
        target.classList.contains('img-modal') ||
        target.classList.contains('img-modal-close')
    ) {
        document.getElementById('imagePreviewModal').style.display = 'none';
    }
});

// Print daftar Tamu
document.querySelector('.btn-print').addEventListener('click', () => {
    const tahun = document.getElementById('filterTahun').value;
    const bulan = document.getElementById('filterBulan').value;

    window.open(
        `index.php?page=print-tamu&tahun=${tahun}&bulan=${bulan}`,
        '_blank'
    );
});