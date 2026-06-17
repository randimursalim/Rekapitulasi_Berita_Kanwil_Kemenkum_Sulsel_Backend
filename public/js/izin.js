// Izin Management
document.addEventListener('DOMContentLoaded', function () {
    // Global variables
    let currentPage = 1;
    let totalPages = 1;
    let totalData = 0;
    let itemsPerPage = 10;

    // Default tab
    let currentTab = 'masuk';

    let currentFilters = {
        search: '',
        tahun: '',
        bulan: '',
        status: ''
    };

    // State untuk upload balasan
    let isUploading = false;

    let chartMasuk = null;
    let chartKeluar = null;

    // Controller untuk upload balasan (untuk membatalkan jika user ganti tab saat upload)
    let uploadController = null;

    // DOM elements
    const container = document.getElementById('izinResults');
    const paginationContainer = document.getElementById('pagination');

    if (!container || !paginationContainer) return;

    // Initialize
    loadIzin(1);

    // Expose functions globally
    window.loadIzinArsip = loadIzin;

    // FUNGSI SWITCH TAB (Dipanggil dari tombol di izin.php)
    window.switchTab = function (tabName) {
        if (isUploading) {
            Swal.fire('Tunggu', 'Upload sedang berlangsung...', 'info');
            return;
        }
        currentTab = tabName;
        currentPage = 1; // Reset ke halaman 1

        // Update UI Tombol (Active State)
        document.querySelectorAll('.btn-container .btn-tambah').forEach(btn => btn.classList.remove('active'));
        if (tabName === 'masuk') {
            document.getElementById('btnTabMasuk').classList.add('active');
        } else if (tabName === 'balasan') {
            document.getElementById('btnTabBalasan').classList.add('active');
        } else if (tabName === 'dashboard') {
            document.getElementById('btnTabDashboard').classList.add('active');
        }

        // Reload data atau dashboard sesuai tab
        if (tabName === 'dashboard') {
            loadDashboard();
        } else {
            loadIzin(1);
        }
    };

    window.setCurrentFiltersIzin = function (filters) {
        currentFilters = { ...currentFilters, ...filters };
        currentPage = 1;
        if (currentTab === 'dashboard') {
            loadDashboard();
        } else {
            loadIzin(currentPage);
        }
    };

    // Load Izin with pagination
    async function loadIzin(page = 1) {
        if (!container) return;

        try {
            container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat data...</p></div>';

            const params = new URLSearchParams({
                page: page,
                search: currentFilters.search,
                tahun: currentFilters.tahun,
                bulan: currentFilters.bulan,
                status: currentFilters.status,
                tab: currentTab // Kirim parameter tab ke backend
            });

            const response = await fetch(`ajax/fetch_izin.php?${params}`);

            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            if (!result.success) {
                container.innerHTML = `<p style="color:red;">Gagal memuat data: ${result.error}</p>`;
                return;
            }

            const data = result.data;
            totalPages = result.pagination.totalPages;
            totalData = result.pagination.totalData;
            currentPage = result.pagination.currentPage;

            // Render data sesuai TAB yang aktif
            renderData(data);
            renderPagination();

        } catch (error) {
            console.error(error);
            container.innerHTML = '<p style="color:red;">Terjadi kesalahan saat memuat data.</p>';
        }
    }

    async function loadDashboard() {
        if (!container) return;

        container.innerHTML = '<div style="text-align: center; padding: 20px;"><p>Memuat dashboard...</p></div>';
        if (paginationContainer) paginationContainer.innerHTML = '';

        const tahun = currentFilters.tahun || document.getElementById('filterTahun')?.value || '';
        const bulan = currentFilters.bulan || document.getElementById('filterBulan')?.value || 'all';

        try {
            const params = new URLSearchParams({
                tab: 'dashboard',
                tahun: tahun,
                bulan: bulan
            });

            const response = await fetch(`ajax/fetch_izin.php?${params}`);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            const result = await response.json();

            if (!result.success) {
                container.innerHTML = `<p style="color:red;">Gagal memuat dashboard: ${result.error || 'Data tidak tersedia'}</p>`;
                return;
            }

            try {
                renderDashboard(result.data);
            } catch (renderError) {
                console.error('Dashboard render error:', renderError);
                container.innerHTML = `<p style="color:red;">Terjadi kesalahan saat menampilkan grafik: ${renderError.message}</p>`;
            }
        } catch (error) {
            console.error(error);
            container.innerHTML = `<p style="color:red;">Terjadi kesalahan saat memuat dashboard: ${error.message}</p>`;
        }
    }

    function renderDashboard(data) {
        const labels = data.labels || [];
        const masukData = data.masuk || [];
        const keluarData = data.keluar || [];

        const summary = data.summary || {};
        const wa = data.wa || {};
        const status = data.status || [];

        container.innerHTML = `
            <div style="display:flex; flex-direction:column; gap:24px; width:100%;">

                <!-- BARIS 1 -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
                    <div class="chart-card" style="background:#fff; border-radius:12px; padding:20px;">
                        <h3>Surat Masuk (Status 1,3,5)</h3>
                        <div style="height:300px;">
                            <canvas id="chartMasuk"></canvas>
                        </div>
                    </div>

                    <div class="chart-card" style="background:#fff; border-radius:12px; padding:20px;">
                        <h3>Surat Balasan (Status 2,4,6)</h3>
                        <div style="height:300px;">
                            <canvas id="chartKeluar"></canvas>
                        </div>
                    </div>
                </div>

                <!-- BARIS 2 -->
                <div>
                    <h4>Ringkasan Surat</h4>
                    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px; max-width:600px; margin:auto;">
                        ${createCard('📥 Surat Masuk', summary.masuk, '#4facfe')}
                        ${createCard('📤 Surat Balasan', summary.keluar, '#ff6a6a')}
                    </div>
                </div>

                <!-- BARIS 3 -->
                <div>
                    <h4>Status WhatsApp</h4>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
                        ${createCard('✅ WA Terkirim', wa.sent, '#00c853')}
                        ${createCard('⏳ Belum Kirim', wa.pending, '#ffb300')}
                        ${createCard('❌ Gagal Kirim', wa.failed, '#d50000')}
                    </div>
                </div>

                <!-- BARIS 4 -->
                <div>
                    <h4>Distribusi Status Surat</h4>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px,1fr)); gap:16px;">
                        ${[1, 2, 3, 4, 5, 6].map(i =>
            createCard(`Status ${i}`, status[i] || 0, '#6c63ff')
        ).join('')}
                    </div>
                </div>

            </div>
        `;

        if (chartMasuk) chartMasuk.destroy();
        if (chartKeluar) chartKeluar.destroy();

        chartMasuk = createDashboardChart(
            'chartMasuk',
            'Surat Masuk',
            labels,
            masukData,
            'rgba(54, 162, 235, 0.7)'
        );

        chartKeluar = createDashboardChart(
            'chartKeluar',
            'Surat Balasan',
            labels,
            keluarData,
            'rgba(255, 99, 132, 0.7)'
        );
    }

    function createCombinedChart(canvasId, labels, masukData, keluarData) {
        if (typeof Chart === 'undefined') {
            throw new Error('Chart.js tidak ditemukan');
        }

        const canvas = document.getElementById(canvasId);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Belum Ada Data'],
                datasets: [
                    {
                        label: 'Surat Masuk',
                        data: labels.length ? masukData : [0],
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    },
                    {
                        label: 'Surat Keluar',
                        data: labels.length ? keluarData : [0],
                        backgroundColor: 'rgba(255, 159, 64, 0.7)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return `${ctx.dataset.label}: ${ctx.raw} surat`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        ticks: {
                            color: '#444'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#444'
                        }
                    }
                }
            }
        });
    }

    function createDashboardChart(canvasId, label, labels, data, color) {
        if (typeof Chart === 'undefined') {
            throw new Error('Chart.js tidak ditemukan');
        }

        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const ctx = canvas.getContext('2d');
        const borderColor = color.replace(/0\.7\)$/, '1)') || color;
        const chartData = {
            labels: labels.length ? labels : ['Belum Ada Data'],
            datasets: [{
                label: label,
                data: labels.length ? data : [0],
                backgroundColor: labels.length ? color : 'rgba(200, 200, 200, 0.7)',
                borderColor: labels.length ? borderColor : 'rgba(160, 160, 160, 1)',
                borderWidth: 1
            }]
        };

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'center',
                    labels: {
                        color: '#333'
                    }
                }
            },
            layout: {
                padding: { top: 10 }
            },
            scales: {
                x: {
                    ticks: { color: '#333' }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: '#333' }
                }
            }
        };

        if (typeof ChartDataLabels !== 'undefined') {
            chartOptions.plugins.datalabels = {
                anchor: 'end',
                align: 'top',
                color: '#000',
                font: { weight: 'bold' },
                formatter: value => value
            };
        }

        const chartConfig = {
            type: 'bar',
            data: chartData,
            options: chartOptions
        };

        if (typeof ChartDataLabels !== 'undefined') {
            chartConfig.plugins = [ChartDataLabels];
        }

        return new Chart(ctx, chartConfig);
    }

    function createCard(title, value, color) {
        return `
            <div style="
                background: linear-gradient(135deg, ${color}, #ffffff);
                border-radius:16px;
                padding:20px;
                box-shadow:0 10px 25px rgba(0,0,0,0.08);
                transition:0.3s;
            "
            onmouseover="this.style.transform='translateY(-5px)'"
            onmouseout="this.style.transform='none'">

                <div style="font-size:0.9rem; color:#555;">${title}</div>
                <div style="font-size:1.8rem; font-weight:bold; margin-top:8px;">
                    ${value ?? 0}
                </div>
            </div>
        `;
    }

    // STATUS MAPPING
    const STATUS_MAP = {
        1: { text: 'Diterima oleh Pengelola Surat Masuk', class: 'badge-info' },
        2: { text: 'Ditolak karena tidak memenuhi persyaratan', class: 'badge-danger' },
        3: { text: 'Diterima oleh Kakanwil', class: 'badge-primary' },
        4: { text: 'Ditolak oleh Pimpinan', class: 'badge-danger' },
        5: { text: 'Diterima Kabag TU & Umum', class: 'badge-success' },
        6: { text: 'Surat balasan akan dikirim melalui WhatsApp yang terdaftar', class: 'badge-success' }
    };

    function renderStatusBadge(status) {
        const s = STATUS_MAP[status];
        if (!s) return `<span class="badge badge-secondary">Status tidak diketahui</span>`;
        return `<span class="badge ${s.class}">${s.text}</span>`;
    }

    // fungsi render file balasan
    function renderFileBalasan(t) {
        const isPenolakan = t.keterangan && t.keterangan.trim() !== '';

        // kondisi: penolakan + sudah kirim WA
        if (isPenolakan && t.wa_terkirim == 1) {
            return `
                <div style="display:flex; flex-direction:column; gap:6px;">
                    
                    <button type="button" 
                        class="btn-preview"
                        onclick="previewPdf('${t.file_balasan}')">
                        <i class="fas fa-eye"></i> Lihat Surat Balasan
                    </button>
    
                    <label for="file-${t.id}" 
                        class="btn-action-aksi"
                        style="background:#ffc107; color:#000; font-size:11px; cursor:pointer;">
                        <i class="fas fa-edit"></i> Perbarui Balasan
                    </label>
    
                    <input type="file" 
                        id="file-${t.id}" 
                        class="input-file-balasan"
                        accept="application/pdf"
                        style="display:none;"
                        onchange="uploadBalasan(this, '${t.id}')">
                </div>
            `;
        }

        // default
        return `
            <button type="button" 
                class="btn-preview"
                onclick="previewPdf('${t.file_balasan}')">
                <i class="fas fa-eye"></i> Surat Balasan
            </button>
        `;
    }

    // Render Data Table
    function renderData(data) {
        if (!container) return;

        // 1. Cek Data Kosong
        if (data.length === 0) {
            container.innerHTML = '<div class="data no-data" style="width: 100%; text-align: center; padding: 40px;"><span style="color: var(--text-color); font-size: 1.1rem;">Belum ada data pada kategori ini</span></div>';
            return;
        }

        let html = '<div class="new-table-container">';
        const isMasuk = currentTab === 'masuk';
        const gridClass = isMasuk ? 'grid-masuk' : 'grid-balasan';

        // ================= HEADER =================
        html += `<div class="new-table-header ${gridClass}">`;
        html += `<div class="new-cell"><span class="data-title">No</span></div>`;
        html += `<div class="new-cell"><span class="data-title">ID Pengajuan</span></div>`;
        html += `<div class="new-cell"><span class="data-title">Nama Pengaju (NIK)</span></div>`;
        html += `
            <div class="new-cell">
                <span class="data-title status-filter">
                    Status <i class="fas fa-caret-down"></i>
                    <div class="status-dropdown">
                        <div data-status="">Semua Status</div>
                        ${Object.entries(STATUS_MAP).filter(([k]) => k != 6).map(([key, val]) => `
                            <div data-status="${key}">${val.text}</div>
                        `).join('')}
                    </div>
                </span>
            </div>`;

        if (isMasuk) {
            html += `<div class="new-cell"><span class="data-title">File Masuk</span></div>`;
            html += `<div class="new-cell"><span class="data-title">Aksi</span></div>`;
        } else {
            html += `<div class="new-cell"><span class="data-title">Jenis</span></div>`;
            html += `<div class="new-cell"><span class="data-title">File Balasan</span></div>`;
            html += `<div class="new-cell"><span class="data-title">Status</span></div>`;
        }
        html += `</div>`; // Tutup Header

        // ================= BODY =================
        data.forEach((t, i) => {
            const startNumber = (currentPage - 1) * itemsPerPage + 1;

            // --- BARIS UTAMA ---
            html += `<div class="new-table-row ${gridClass}">`;

            // 1. Kolom No (DENGAN TOMBOL EXPAND)
            html += `
            <div class="new-cell" style="display:flex; align-items:center; gap:8px;">
                 <button class="btn-toggle-detail" onclick="toggleDetail('detail-${t.id}', this)">
                    <i class="fas fa-plus-circle"></i>
                 </button>
                 <span class="data-list">${startNumber + i}</span>
            </div>`;

            // 2. Kolom ID
            html += `<div class="new-cell"><span class="data-list">${t.id}</span></div>`;

            // 3. Kolom Nama
            html += `<div class="new-cell"><span class="data-list">${t.nama} (${t.nik ?? '-'})</span></div>`;

            // 4. Kolom Status
            html += `<div class="new-cell"><span class="data-list">
                    <div onclick="openStatusModal('${t.id}', '${t.nama.replace(/'/g, "\\'")}', '${t.nik ?? '-'}', '${t.jenis_surat.replace(/'/g, "\\'")}', '${t.status}')" 
                         style="cursor: pointer; transition: transform 0.2s; display:inline-block;" 
                         onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        ${renderStatusBadge(t.status)} <i class="fas fa-pen" style="font-size:10px; margin-left:5px; opacity:0.6;"></i>
                    </div>
                </span></div>`;

            // Kolom Kondisional (Sama seperti sebelumnya)
            if (isMasuk) {
                html += `<div class="new-cell"><span class="data-list">
                    ${t.file ? `<button type="button" class="btn-preview" onclick="previewPdf('${t.file}')"><i class="fas fa-eye"></i></button>` : '-'}
                </span></div>`;

                html += `
                    <div class="new-cell">
                    <span class="data-list">
                        <button class="btn-action-aksi delete"
                            onclick="hapusIzin('${t.id}', '${t.nama.replace(/'/g, "\\'")}')">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </span>
                </div>`;

            } else {
                html += `<div class="new-cell"><span class="data-list" style="text-transform:capitalize;">${t.jenis_surat}</span></div>`;

                html += `<div class="new-cell"><span class="data-list">
                   ${t.file_balasan
                        ? renderFileBalasan(t)
                        : `<div class="upload-wrapper">
                            <input type="file" id="file-${t.id}" 
                                class="input-file-balasan"
                                accept="application/pdf"
                                style="display:none;"
                                onchange="uploadBalasan(this, '${t.id}')">
                            <label for="file-${t.id}" 
                                class="btn-upload-action"
                                style="cursor:pointer; color:blue; font-size:12px;">
                                <i class="fas fa-upload"></i> Upload Balasan
                            </label>
                            <span id="loading-${t.id}" 
                                style="display:none; font-size:11px; color:grey;">
                                Mengupload...
                            </span>
                        </div>`
                    }
                </span></div>`;

                // Logika Status Balasan
                let statusHtml = '';
                if (!t.file_balasan) {
                    statusHtml = `
                        <span class="badge badge-warning">
                            <i class="fas fa-clock"></i> Surat Balasan akan dikirim melalui WhatsApp
                        </span>
                    `;
                } else if (t.file_balasan && t.wa_status === 'pending') {
                    // const phone = t.tlp ? t.tlp : '';
                    statusHtml = `
                        <button 
                            class="btn-tambah" style="background:#28a745; color:white; font-size:12px; padding:6px 12px;" 
                            onclick="kirimBalasan('${t.id}')">
                                <i class="fab fa-whatsapp"></i> Kirim Surat Balasan
                        </button>
                    `;
                } else if (t.wa_status === 'sent') {
                    // const phone = t.tlp ? t.tlp : '';
                    statusHtml = `
                        <div style="display:flex; flex-direction:column; gap:5px; align-items:flex-start;">
                            <span class="badge badge-success"><i class="fas fa-check-double"></i> Terkirim</span>
                            <button 
                                type="button" 
                                class="btn-action-aksi" style="background:#ffc107; color:#000; font-size:11px;" 
                                onclick="kirimBalasan('${t.id}')"><i class="fas fa-redo"></i> Kirim Ulang
                            </button>
                        </div>
                    `;
                } else if (t.wa_status === 'failed') {
                    // const phone = t.tlp ? t.tlp : '';
                    statusHtml = `
                        <div style="display:flex;flex-direction:column;gap:5px;">
                            <span class="badge badge-danger">Gagal</span>
                            <button class="btn-action-aksi" onclick="kirimBalasan('${t.id}')">
                                Coba Lagi
                            </button>
                        </div>
                    `;
                }
                html += `<div class="new-cell"><span class="data-list">${statusHtml}</span></div>`;
            }
            html += `</div>`; // Tutup Baris Utama

            // --- BARIS DETAIL (HIDDEN BY DEFAULT) ---
            // Kita buat row baru yang tersembunyi
            html += `
            <div id="detail-${t.id}" class="detail-row" style="display: none;">
                <div class="detail-container">
                   ${isMasuk ?
                    // Layout Detail Surat Masuk
                    `
                       <div class="detail-item"><strong>No Hp:</strong> ${t.tlp ?? '-'}</div>
                       <div class="detail-item"><strong>Tanggal:</strong> ${t.tgl ?? '-'}</div>
                       <div class="detail-item"><strong>Jenis Surat:</strong> ${t.jenis_surat ?? '-'}</div>
                       <div class="detail-item full-width"><strong>Keterangan:</strong> ${t.keterangan ?? '-'}</div>
                       `
                    :
                    // Layout Detail Surat Balasan
                    `
                       <div class="detail-item"><strong>No Hp:</strong> ${t.tlp ?? '-'}</div>
                       <div class="detail-item"><strong>Tanggal Pengajuan:</strong> ${t.tgl ?? '-'}</div>
                       <div class="detail-item"><strong>Tanggal Balasan:</strong> ${t.tgl_balasan ?? '-'}</div>
                       <div class="detail-item">
                            <strong>File Pengajuan Awal:</strong> 
                            ${t.file ? `<a href="#" onclick="previewPdf('${t.file}')" style="color:blue;"><i class="fas fa-eye"></i> Lihat File</a>` : '-'}
                       </div>
                       <div class="detail-item full-width"><strong>Keterangan:</strong> ${t.keterangan ?? '-'}</div>
                       `
                }
                </div>
            </div>`;
        });

        html += `</div>`; // Tutup Container Utama
        container.innerHTML = html;
        attachDropdownListeners();
    }

    window.toggleDetail = function (rowId, btn) {
        const detailRow = document.getElementById(rowId);
        if (!detailRow) return;

        const icon = btn.querySelector('i');

        if (detailRow.style.display === 'none') {
            detailRow.style.display = 'block';
            icon.classList.remove('fa-plus-circle');
            icon.classList.add('fa-minus-circle');
            btn.classList.add('active');
        } else {
            detailRow.style.display = 'none';
            icon.classList.remove('fa-minus-circle');
            icon.classList.add('fa-plus-circle');
            btn.classList.remove('active');
        }
    };

    // Helper untuk Dropdown Filter (Dipisah biar rapi)
    function attachDropdownListeners() {
        const statusFilter = container.querySelector('.status-filter');
        const dropdown = statusFilter?.querySelector('.status-dropdown');

        if (statusFilter && dropdown) {
            statusFilter.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('show');
            });

            dropdown.querySelectorAll('div').forEach(item => {
                item.addEventListener('click', () => {
                    currentFilters.status = item.dataset.status;
                    currentPage = 1;
                    loadIzin(1);
                });
            });

            document.addEventListener('click', () => {
                dropdown.classList.remove('show');
            });
        }
    }

    window.previewPdf = function (path) {
        const modal = document.getElementById('pdfModal');
        const iframe = document.getElementById('pdfViewer');
        iframe.src = `${window.APP_BASE}/pdf-viewer.php?file=${encodeURIComponent(path)}`;
        modal.style.display = 'flex';
    };

    window.closePdfModal = function () {
        document.getElementById('pdfViewer').src = '';
        document.getElementById('pdfModal').style.display = 'none';
    };

    // FUNGSI BARU: Toggle Keterangan berdasarkan pilihan status
    window.toggleKeterangan = function () {
        const status = document.getElementById('statusSelect').value;
        const ketGroup = document.getElementById('keteranganGroup');
        const ketInput = document.getElementById('statusKeterangan');

        // Jika status 2 atau 4, tampilkan input keterangan
        if (status == '2' || status == '4') {
            ketGroup.style.display = 'block';
            ketInput.required = true; // Wajib diisi jika ditolak
        } else {
            ketGroup.style.display = 'none';
            ketInput.required = false;
            ketInput.value = ''; // Reset nilai jika status berubah bukan tolak
        }
    };

    // Fungsi update status
    window.openStatusModal = function (id, nama, nik, jenis_surat, currentStatus) {
        document.getElementById('statusId').value = id;
        document.getElementById('statusNama').value = `${nama} - ${nik} - ${jenis_surat}`;

        const select = document.getElementById('statusSelect');
        select.value = currentStatus;

        // Panggil fungsi toggle untuk set tampilan awal sesuai status saat ini
        toggleKeterangan();

        document.getElementById('statusModal').style.display = 'flex';
    };

    window.closeStatusModal = function () {
        document.getElementById('statusModal').style.display = 'none';
    };

    window.submitUpdateStatus = function (e) {
        e.preventDefault();

        const id = document.getElementById('statusId').value;
        const status = document.getElementById('statusSelect').value;
        const keterangan = document.getElementById('statusKeterangan').value; // Ambil nilai keterangan

        // UI Loading
        const btnSubmit = e.target.querySelector('button[type="submit"]');
        const oriText = btnSubmit.innerText;
        btnSubmit.innerText = 'Menyimpan...';
        btnSubmit.disabled = true;

        // Gunakan URLSearchParams untuk kirim data form
        const params = new URLSearchParams();
        params.append('id', id);
        params.append('status', status);

        // Kirim keterangan jika status tolak
        if (status == '2' || status == '4') {
            params.append('keterangan', keterangan);
        }

        fetch('index.php?page=update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: params.toString()
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status berhasil diperbarui',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    closeStatusModal();
                    loadIzin(currentPage);
                } else {
                    Swal.fire('Gagal', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            })
            .finally(() => {
                btnSubmit.innerText = oriText;
                btnSubmit.disabled = false;
            });
    };

    // Fungsi upload surat balasan
    window.uploadBalasan = function (input, id) {
        if (input.disabled) return;
        input.disabled = true;
        isUploading = true;

        const file = input.files[0];
        if (!file) {
            input.disabled = false;
            isUploading = false;
            return;
        }

        // Validasi tipe file client-side
        if (file.type !== 'application/pdf') {
            Swal.fire('Error', 'File harus berformat PDF', 'error');
            input.value = '';
            input.disabled = false;
            isUploading = false;
            return;
        }

        // Validasi ukuran (2MB)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire('Error', 'Ukuran file maksimal 2MB', 'error');
            input.value = '';
            input.disabled = false;
            isUploading = false;
            return;
        }

        // UI Loading
        const label = document.querySelector(`label[for="file-${id}"]`);
        const loading = document.getElementById(`loading-${id}`);
        if (label) label.style.display = 'none';
        if (loading) loading.style.display = 'inline';

        const formData = new FormData();
        formData.append('id', id);
        formData.append('file_balasan', file);

        // Kirim ke Backend
        uploadController = new AbortController();

        // lihat id untuk debug
        // console.log('UPLOAD ID:', id);

        fetch('index.php?page=upload-balasan', { // Pastikan routing ini dibuat
            method: 'POST',
            body: formData,
            signal: uploadController.signal
        })
            // .then(response => response.json())
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                // lihat data untuk debug
                // console.log('DEBUG UPLOAD:', data);
                if (data.success) {
                    input.disabled = false;
                    isUploading = false;

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Surat balasan berhasil diupload!',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // reset UI langsung
                    if (label) label.style.display = 'inline';
                    if (loading) loading.style.display = 'none';

                    // ⬇️ delay kecil sebelum re-render
                    setTimeout(() => {
                        if (currentTab === 'balasan') {
                            loadIzin(currentPage);
                        } else {
                            switchTab('balasan');
                        }
                    }, 800);
                } else {
                    input.disabled = false;
                    isUploading = false;

                    Swal.fire('Gagal', data.message, 'error');
                    // Reset UI jika gagal
                    if (label) label.style.display = 'inline';
                    if (loading) loading.style.display = 'none';
                    input.value = '';
                }
            })
            .catch(error => {
                input.disabled = false;
                isUploading = false;

                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                if (label) label.style.display = 'inline';
                if (loading) loading.style.display = 'none';
            });
    };

    // Fungsi kirim WA via backend (Fonnte-ready)
    window.kirimBalasan = function (id) {
        Swal.fire({
            title: 'Kirim WhatsApp?',
            text: 'Pesan akan dikirim otomatis ke nomor pengaju',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Kirim',
            confirmButtonColor: '#28a745',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch('index.php?page=kirim-wa', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.message || 'Gagal mengirim WhatsApp');
                        }
                        return data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                    });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Pesan WhatsApp berhasil dikirim',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    loadIzin(currentPage);
                });
            }
        });
    };

    function renderPagination() {
        // ... kode pagination lama Anda ...
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
            if (startPage > 2) paginationHTML += `<span class="pagination-dots">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? 'active' : '';
            paginationHTML += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) paginationHTML += `<span class="pagination-dots">...</span>`;
            paginationHTML += `<button class="pagination-btn" data-page="${totalPages}">${totalPages}</button>`;
        }

        // Next button
        if (currentPage < totalPages) {
            paginationHTML += `<button class="pagination-btn" data-page="${currentPage + 1}">Next</button>`;
        }

        paginationContainer.innerHTML = paginationHTML;

        // Attach pagination event listeners
        paginationContainer.querySelectorAll('.pagination-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const page = parseInt(this.dataset.page);
                if (page && page !== currentPage) {
                    currentPage = page;
                    loadIzin(currentPage);
                }
            });
        });
    }

    // Fungsi hapus Izin
    function hapusIzin(id, nama) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: `Kamu akan menghapus perizinan "${nama}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('index.php?page=hapus-izin', {
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
                                loadIzin(currentPage);
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
    window.hapusIzin = hapusIzin;

    // Check for success message from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Data Perizinan berhasil diperbarui.',
            showConfirmButton: false,
            timer: 2000
        }).then(() => {
            // Remove status parameter from URL
            const newUrl = window.location.pathname + '?page=izin';
            window.history.replaceState({}, document.title, newUrl);
        });
    }

    const btnCari = document.getElementById('btnCari');

    btnCari.addEventListener('click', handleFilter);

    function handleFilter() {
        const tahun = document.getElementById('filterTahun').value;
        const bulan = document.getElementById('filterBulan').value;

        window.setCurrentFiltersIzin({ tahun, bulan });
    }
});