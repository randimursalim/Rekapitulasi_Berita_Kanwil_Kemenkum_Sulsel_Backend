// Tamu Management
document.addEventListener('DOMContentLoaded', function () {
    // Global variables
    let currentPage = 1;
    let totalPages = 1;
    let totalData = 0;
    let itemsPerPage = 10;
    let currentFilters = {
        search: '',
        startDate: '',
        endDate: '',
        layanan: '',
        layanan_item: ''
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
                startDate: currentFilters.startDate,
                endDate: currentFilters.endDate,
                layanan: currentFilters.layanan,
                layanan_item: currentFilters.layanan_item
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

    // Helper function to escape HTML special characters
    function escapeHtml(string) {
        if (!string) return '';
        return String(string)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Render data table
    function renderData(data) {
        if (!container) return;

        if (data.length === 0) {
            container.innerHTML = '<div class="data no-data" style="grid-column: 1 / -1; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;"><i class="fas fa-users" style="font-size: 3rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>Belum ada tamu yang terdaftar</span></div>';
            return;
        }

        const html = `
        <div class="tamu-table-container">
            <table class="tamu-table">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 140px;">Tanggal</th>
                        <th style="width: 160px;">Nama Tamu</th>
                        <th style="width: 150px;">Kontak</th>
                        <th>Alamat</th>
                        <th class="text-center" style="width: 100px;">Layanan</th>
                        <th style="width: 130px;">Item Layanan</th>
                        <th>Maksud/Tujuan</th>
                        <th class="text-center" style="width: 80px;">Antrean</th>
                        <th class="text-center" style="width: 80px;">TTD</th>
                        <th class="text-center" style="width: 80px;">Foto</th>
                        <th class="text-center" style="width: 60px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.map((t, i) => {
                        const startNumber = (currentPage - 1) * itemsPerPage + 1;
                        const escapedNama = escapeHtml(t.nama).replace(/'/g, "\\'");
                        return `
                        <tr>
                            <td class="text-center font-weight-bold">${startNumber + i}</td>
                            <td>
                                <div>${formatTanggal(t.tgl)}</div>
                                <div class="text-muted" style="font-size: 0.85em;">${t.jam ?? '-'}</div>
                            </td>
                            <td><strong>${escapeHtml(t.nama)}</strong></td>
                            <td>
                                <div>${escapeHtml(t.telp)}</div>
                                <div class="text-muted" style="font-size: 0.8em; word-break: break-all;">${escapeHtml(t.email ?? '-')}</div>
                            </td>
                            <td class="text-alamat">${escapeHtml(t.alamat)}</td>
                            <td class="text-center">
                                <span class="badge badge-layanan badge-${t.layanan ?? ''}">
                                    ${escapeHtml((t.layanan ?? '').toUpperCase())}
                                </span>
                            </td>
                            <td>${escapeHtml(t.layanan_item ?? '-')}</td>
                            <td>${escapeHtml(t.tujuan)}</td>
                            <td class="text-center">
                                ${t.entrain === 'yes' 
                                    ? '<span class="text-success font-weight-bold">Ya</span>' 
                                    : '<span class="text-muted">Tidak</span>'
                                }
                            </td>
                            <td class="text-center">
                                ${t.ttd
                                ? `<img 
                                        src="${getImageUrl('storage/uploads/ttd/' + t.ttd)}" 
                                        alt="TTD ${escapeHtml(t.nama)}" 
                                        class="img-ttd img-preview"
                                        data-caption="TTD ${escapeHtml(t.nama)}"
                                    >`
                                : '-'}
                            </td>
                            <td class="text-center">
                                ${t.foto
                                ? `<img 
                                        src="${getImageUrl('storage/uploads/foto/' + t.foto)}"
                                        alt="Foto ${escapeHtml(t.nama)}" 
                                        class="img-foto img-preview"
                                        data-caption="Foto ${escapeHtml(t.nama)}"
                                    >`
                                : '-'}
                            </td>
                            <td class="text-center" style="white-space: nowrap;">
                                <a href="index.php?page=edit-tamu&id=${t.id}" class="btn-edit" title="Edit data tamu" style="margin-right: 5px;">
                                    <i class="fas fa-user-edit"></i>
                                </a>
                                <button class="btn-delete" onclick="hapusTamu(${t.id}, '${escapedNama}')" title="Hapus data tamu">
                                    <i class="fas fa-trash-alt"></i>
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

    // Dynamic item loading for filter
    const filterLayanan = document.getElementById('filterLayanan');
    const filterLayananItem = document.getElementById('filterLayananItem');

    if (filterLayanan && filterLayananItem) {
        filterLayanan.addEventListener('change', async function () {
            const val = this.value;
            filterLayananItem.innerHTML = '<option value="">Semua Item Layanan</option>';
            
            if (!val) {
                filterLayananItem.disabled = true;
                return;
            }
            
            filterLayananItem.innerHTML = '<option value="">Memuat...</option>';
            filterLayananItem.disabled = true;
            
            try {
                const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
                const response = await fetch(`${baseUrl}/api/get-layanan-item.php?layanan=${val}`);
                const result = await response.json();
                
                filterLayananItem.innerHTML = '<option value="">Semua Item Layanan</option>';
                if (result.success && result.items.length > 0) {
                    result.items.forEach(item => {
                        filterLayananItem.innerHTML += `<option value="${item}">${item}</option>`;
                    });
                    filterLayananItem.disabled = false;
                }
            } catch (error) {
                filterLayananItem.innerHTML = '<option value="">Gagal memuat data</option>';
                filterLayananItem.disabled = true;
            }
        });
    }

    // Filter button (bulan dan tahun dan layanan dan item layanan)
    document.getElementById('btnCari').addEventListener('click', () => {
        const startDate = document.getElementById('filterStartDate').value;
        const endDate = document.getElementById('filterEndDate').value;
        const layanan = document.getElementById('filterLayanan').value;
        const layanan_item = document.getElementById('filterLayananItem').value;

        window.setCurrentFiltersTamu({
            startDate: startDate,
            endDate: endDate,
            layanan: layanan,
            layanan_item: layanan_item
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

// export pdf
document.querySelector('.btn-print').addEventListener('click', () => {
    const startDate = document.getElementById('filterStartDate').value;
    const endDate = document.getElementById('filterEndDate').value;
    const layanan = document.getElementById('filterLayanan').value;
    const layanan_item = document.getElementById('filterLayananItem').value;

    window.open(
        `index.php?page=print-tamu&startDate=${startDate}&endDate=${endDate}&layanan=${encodeURIComponent(layanan)}&layanan_item=${encodeURIComponent(layanan_item)}`,
        '_blank'
    );
});

// export excel
document.querySelector('.btn-export-excel').addEventListener('click', () => {
    const startDate = document.getElementById('filterStartDate').value;
    const endDate = document.getElementById('filterEndDate').value;
    const layanan = document.getElementById('filterLayanan').value;
    const layanan_item = document.getElementById('filterLayananItem').value;

    window.open(
        `index.php?page=export-excel&startDate=${startDate}&endDate=${endDate}&layanan=${encodeURIComponent(layanan)}&layanan_item=${encodeURIComponent(layanan_item)}`,
        '_blank'
    );
});

// EXPORT WORD
document.querySelector('.btn-export-word').addEventListener('click', () => {
    const startDate = document.getElementById('filterStartDate').value;
    const endDate = document.getElementById('filterEndDate').value;
    const layanan = document.getElementById('filterLayanan').value;
    const layanan_item = document.getElementById('filterLayananItem').value;

    window.open(
        `index.php?page=export-word&startDate=${startDate}&endDate=${endDate}&layanan=${encodeURIComponent(layanan)}&layanan_item=${encodeURIComponent(layanan_item)}`,
        '_blank'
    );
});