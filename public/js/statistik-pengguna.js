document.addEventListener('DOMContentLoaded', function () {
    let dtTable = null;
    let userChart = null;
    let recentChart = null;
    let roleChart = null;

    // Global data untuk export
    window.statistikData = [];

    // Inisialisasi Flatpickr
    const fp = flatpickr("#dateRangeFilter", {
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d M Y",
        locale: "id",
        onChange: function (selectedDates, dateStr, instance) {
            // Fetch if range is complete or cleared
            if (selectedDates.length === 2 || selectedDates.length === 0) {
                fetchStatistik();
            }
        }
    });

    const roleFilter = document.getElementById('roleFilter');
    roleFilter.addEventListener('change', fetchStatistik);

    function initDataTables() {
        if ($.fn.DataTable.isDataTable('#statistikTable')) {
            $('#statistikTable').DataTable().destroy();
        }

        dtTable = $('#statistikTable').DataTable({
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                "paginate": {
                    "previous": "<i class='fas fa-chevron-left' style='font-size: 12px;'></i>",
                    "next": "<i class='fas fa-chevron-right' style='font-size: 12px;'></i>"
                }
            },
            "pageLength": 10,
            "ordering": true,
            "info": true,
            "searching": true,
            "responsive": true,
            "lengthChange": true,
            "autoWidth": false,
            "dom": '<"dt-top-wrapper"lf>rt<"bottom"ip><"clear">',
            "columnDefs": [
                { "orderable": false, "targets": 0 }
            ]
        });
    }

    // Initialize empty charts
    function initCharts() {
        const ctxUser = document.getElementById('userActivityChart');
        if (ctxUser) {
            userChart = new Chart(ctxUser.getContext('2d'), {
                type: 'bar',
                data: { labels: [], datasets: [] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 8 } } }
                }
            });
        }

        const ctxRecent = document.getElementById('recentActivityChart');
        if (ctxRecent) {
            recentChart = new Chart(ctxRecent.getContext('2d'), {
                type: 'line',
                data: { labels: [], datasets: [] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    tension: 0.4,
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                        x: { grid: { display: false } }
                    },
                    plugins: { legend: { display: false } }
                }
            });
        }

        const ctxRole = document.getElementById('roleDistributionChart');
        if (ctxRole) {
            roleChart = new Chart(ctxRole.getContext('2d'), {
                type: 'doughnut',
                data: { labels: [], datasets: [] },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } }
                    }
                }
            });
        }
    }

    function isDarkMode() {
        return document.body.classList.contains('dark');
    }

    function updateChartColors() {
        const textColor = isDarkMode() ? '#CCC' : '#666';
        const gridColor = isDarkMode() ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';

        [userChart, recentChart].forEach(chart => {
            if (!chart) return;
            if (chart.options.scales.x) {
                chart.options.scales.x.ticks.color = textColor;
                chart.options.scales.x.grid.color = gridColor;
            }
            if (chart.options.scales.y) {
                chart.options.scales.y.ticks.color = textColor;
                chart.options.scales.y.grid.color = gridColor;
            }
            if (chart.options.plugins.legend) chart.options.plugins.legend.labels.color = textColor;
            chart.update();
        });

        if (roleChart) {
            roleChart.options.plugins.legend.labels.color = textColor;
            roleChart.update();
        }
    }

    // Observer untuk mendeteksi perubahan dark mode (jika aplikasi menggunakan class 'dark' di body)
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (mutation.attributeName === "class") {
                updateChartColors();
            }
        });
    });
    observer.observe(document.body, { attributes: true });


    function fetchStatistik() {
        const dates = fp.selectedDates;
        let start = '', end = '';
        if (dates.length === 2) {
            start = flatpickr.formatDate(dates[0], "Y-m-d");
            end = flatpickr.formatDate(dates[1], "Y-m-d");
        }

        const role = roleFilter.value;
        const params = new URLSearchParams();
        if (start) params.append('start_date', start);
        if (end) params.append('end_date', end);
        if (role && role !== 'all') params.append('role', role);

        fetch(`${window.BASE_URL}/ajax/fetch_statistik_pengguna.php?${params}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.statistikData = data.tableData;
                    window.statistikSummary = data.summary;
                    renderData(data);
                } else {
                    console.error("Error:", data.message);
                }
            })
            .catch(err => console.error("Fetch error:", err));
    }

    function renderData(data) {
        // 1. Update Top Cards
        document.getElementById('valTotalKonten').textContent = data.summary.total_konten.toLocaleString('id-ID');
        document.getElementById('valTotalAktivitas').textContent = data.summary.total_aktivitas.toLocaleString('id-ID');
        document.getElementById('valPenggunaAktif').textContent = data.summary.pengguna_aktif.toLocaleString('id-ID');
        document.getElementById('valTopUser').textContent = data.summary.top_user;
        document.getElementById('valTopUserAktivitas').textContent = data.summary.top_user_aktivitas.toLocaleString('id-ID');

        // Update Mini Cards
        document.getElementById('valJenisKegiatan').textContent = data.summary.total_kegiatan.toLocaleString('id-ID');
        document.getElementById('valJenisPeminjaman').textContent = data.summary.total_peminjaman.toLocaleString('id-ID');
        document.getElementById('valJenisKonten').textContent = data.summary.total_konten.toLocaleString('id-ID');
        if (document.getElementById('valJenisTamu')) document.getElementById('valJenisTamu').textContent = data.summary.total_tamu.toLocaleString('id-ID');
        if (document.getElementById('valJenisPengaduan')) document.getElementById('valJenisPengaduan').textContent = data.summary.total_pengaduan.toLocaleString('id-ID');
        if (document.getElementById('valJenisHarmonisasi')) document.getElementById('valJenisHarmonisasi').textContent = data.summary.total_harmonisasi.toLocaleString('id-ID');

        // 2. Update DataTables
        if ($.fn.DataTable.isDataTable('#statistikTable')) {
            $('#statistikTable').DataTable().destroy();
        }
        const tbody = document.getElementById('statistikTableBody');
        tbody.innerHTML = '';

        data.tableData.forEach((u, i) => {
            const tr = document.createElement('tr');

            let roleColor = '#0dcaf0'; // info
            if (u.role === 'Admin') roleColor = '#dc3545'; // danger
            else if (u.role === 'Operator') roleColor = '#0d6efd'; // primary
            else if (u.role === 'p3h' || u.role === 'P3H') roleColor = '#ffc107'; // warning

            const initials = u.nama.charAt(0).toUpperCase();

            tr.innerHTML = `
                <td style="text-align: center;">${i + 1}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 35px; height: 35px; background: rgba(13, 110, 253, 0.15); color: #0d6efd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                            ${initials}
                        </div>
                        <div>
                            <div style="font-weight: bold; color: var(--text-color);">${u.nama}</div>
                            <small style="color: #888;">@${u.username}</small>
                        </div>
                    </div>
                </td>
                <td><span style="font-size: 12px; padding: 5px 10px; border-radius: 20px; font-weight: bold; background: ${roleColor}20; color: ${roleColor};">${u.role}</span></td>
                <td style="text-align: center;">${u.total_log_aktivitas}</td>
                <td style="text-align: center;">${u.total_konten}</td>
                <td style="text-align: center;">${u.total_kegiatan}</td>
                <td style="text-align: center;">${u.total_ruangan}</td>
                <td style="text-align: center;">${u.total_tamu || 0}</td>
                <td style="text-align: center;">${u.total_pengaduan || 0}</td>
                <td style="text-align: center;">${u.total_harmonisasi || 0}</td>
                <td style="text-align: center; font-weight: bold;">${u.total_log_aktivitas}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="custom-progress">
                            <div class="custom-progress-bar" style="background: #198754; width: ${u.produktivitas_persen}%"></div>
                        </div>
                        <span style="font-size: 12px; color: #888; min-width: 35px; text-align: right;">${u.produktivitas_persen}%</span>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
        initDataTables();

        // 3. Update Top Kontributor List
        const topList = document.getElementById('topContributorsList');
        topList.innerHTML = '';
        data.topKontributor.forEach((u, i) => {
            let medal = '';
            if (i === 0) medal = '<i class="fas fa-medal text-warning" style="font-size: 18px;"></i>';
            else if (i === 1) medal = '<i class="fas fa-medal" style="color: #9e9e9e; font-size: 18px;"></i>';
            else if (i === 2) medal = '<i class="fas fa-medal" style="color: #cd7f32; font-size: 18px;"></i>';
            else medal = `<span style="color: #888; width: 18px; display:inline-block; text-align:center; font-weight: bold;">${i + 1}</span>`;

            topList.innerHTML += `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                        ${medal}
                        <span style="font-weight: bold; font-size: 14px; color: var(--text-color);">${u.nama}</span>
                    </div>
                    <div class="custom-progress" style="margin: 0 15px;">
                        <div class="custom-progress-bar" style="background: #0d6efd; width: ${u.produktivitas_persen}%"></div>
                    </div>
                    <div style="font-weight: bold; min-width: 30px; text-align: right; color: var(--text-color);">${u.total_log_aktivitas}</div>
                </div>
            `;
        });

        // 4. Update Charts

        // Bar Chart (Top Users)
        userChart.data.labels = data.topUsersBar.map(u => {
            const parts = u.nama.split(' ');
            let name = parts[0];
            // Jika nama pertama terlalu pendek (misal A.) atau merupakan gelar/nama depan umum, ambil juga kata kedua
            if (parts.length > 1 && (name.length <= 3 || ['andi', 'muh', 'muhammad', 'ahmad', 'abdul', 'sri', 'nur'].includes(name.toLowerCase()))) {
                name += ' ' + parts[1];
            }
            // Batasi panjang agar tidak menutupi chart
            return name.length > 15 ? name.substring(0, 15) + '...' : name;
        });

        userChart.data.datasets = [
            { label: 'Log', data: data.topUsersBar.map(u => u.total_log_aktivitas), backgroundColor: '#0d6efd', barPercentage: 0.5 },
            { label: 'Konten', data: data.topUsersBar.map(u => u.total_konten), backgroundColor: '#198754', barPercentage: 0.5 },
            { label: 'Kegiatan', data: data.topUsersBar.map(u => u.total_kegiatan), backgroundColor: '#6f42c1', barPercentage: 0.5 },
            { label: 'Peminjaman', data: data.topUsersBar.map(u => u.total_ruangan), backgroundColor: '#fd7e14', barPercentage: 0.5 },
            { label: 'Tamu', data: data.topUsersBar.map(u => u.total_tamu || 0), backgroundColor: '#0dcaf0', barPercentage: 0.5 },
            { label: 'Aduan', data: data.topUsersBar.map(u => u.total_pengaduan || 0), backgroundColor: '#dc3545', barPercentage: 0.5 },
            { label: 'Harmonisasi', data: data.topUsersBar.map(u => u.total_harmonisasi || 0), backgroundColor: '#d63384', barPercentage: 0.5 }
        ];
        userChart.update();

        // Line Chart (7 Hari)
        recentChart.data.labels = data.chart7Hari.labels;
        recentChart.data.datasets = [{
            label: 'Aktivitas',
            data: data.chart7Hari.values,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            borderWidth: 2,
            fill: true,
            pointBackgroundColor: '#fff',
            pointBorderColor: '#0d6efd',
            pointRadius: 4
        }];
        recentChart.update();

        // Doughnut Chart (Role)
        roleChart.data.labels = ['Admin', 'Operator', 'P3H'];
        roleChart.data.datasets = [{
            data: [data.roleDistribution.Admin, data.roleDistribution.Operator, data.roleDistribution.P3H],
            backgroundColor: ['#0d6efd', '#198754', '#ffc107'],
            borderWidth: 0,
            hoverOffset: 4
        }];
        roleChart.update();

        document.getElementById('totalUsersDoughnut').textContent = data.summary.pengguna_aktif;

        updateChartColors();
    }

    // Export Functions
    document.getElementById('btnExportExcel').addEventListener('click', function () {
        if (window.statistikData.length === 0) return alert('Tidak ada data');

        const summary = window.statistikSummary || {};
        const wb = XLSX.utils.book_new();

        // Buat struktur tabel gabungan: Bagian 1 (Ringkasan), Bagian 2 (Rincian)
        let exportData = [
            ['RINGKASAN TOTAL AKTIVITAS PER JENIS', ''],
            ['Jenis Aktivitas', 'Total Data'],
            ['Jadwal Kegiatan', summary.total_kegiatan || 0],
            ['Peminjaman Ruangan', summary.total_peminjaman || 0],
            ['Konten', summary.total_konten || 0],
            ['Tamu', summary.total_tamu || 0],
            ['Aduan', summary.total_pengaduan || 0],
            ['Harmonisasi', summary.total_harmonisasi || 0],
            ['', ''],
            ['RINCIAN STATISTIK PER PENGGUNA', '', '', '', '', '', '', '', '', '', ''],
            ['No', 'Nama Pengguna', 'Username', 'Role', 'Log Aktivitas', 'Konten', 'Kegiatan', 'Peminjaman', 'Tamu', 'Aduan', 'Harmonisasi']
        ];

        window.statistikData.forEach((u, i) => {
            exportData.push([
                i + 1, u.nama, u.username, u.role, u.total_log_aktivitas, u.total_konten, u.total_kegiatan, u.total_ruangan, u.total_tamu, u.total_pengaduan, u.total_harmonisasi
            ]);
        });

        const ws = XLSX.utils.aoa_to_sheet(exportData);
        XLSX.utils.book_append_sheet(wb, ws, "Statistik Pengguna");
        XLSX.writeFile(wb, `Statistik_Pengguna_${new Date().getTime()}.xlsx`);
    });

    document.getElementById('btnExportPDF').addEventListener('click', function () {
        if (window.statistikData.length === 0) return alert('Tidak ada data');
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt'); // changed to landscape for more columns

        doc.setFontSize(16);
        doc.setFont("helvetica", "bold");
        doc.text("Laporan Statistik Pengguna", 40, 40);

        const summary = window.statistikSummary || {};
        const summaryData = [
            ['Jadwal Kegiatan', summary.total_kegiatan || 0],
            ['Peminjaman Ruangan', summary.total_peminjaman || 0],
            ['Konten', summary.total_konten || 0],
            ['Tamu', summary.total_tamu || 0],
            ['Aduan', summary.total_pengaduan || 0],
            ['Harmonisasi', summary.total_harmonisasi || 0]
        ];

        doc.setFontSize(12);
        doc.text("Ringkasan Total Aktivitas per Jenis", 40, 70);

        doc.autoTable({
            head: [['Jenis Aktivitas', 'Total Data']],
            body: summaryData,
            startY: 80,
            theme: 'grid',
            styles: { fontSize: 9 },
            headStyles: { fillColor: [13, 110, 253] },
            margin: { left: 40 },
            tableWidth: 300
        });

        let finalY = doc.lastAutoTable.finalY || 200;

        doc.setFontSize(12);
        doc.setFont("helvetica", "bold");
        doc.text("Rincian Statistik per Pengguna", 40, finalY + 30);

        let bodyData = [];
        window.statistikData.forEach((u, i) => {
            bodyData.push([
                i + 1, u.nama, u.role, u.total_log_aktivitas, u.total_konten, u.total_kegiatan, u.total_ruangan, u.total_tamu, u.total_pengaduan, u.total_harmonisasi
            ]);
        });

        doc.autoTable({
            head: [['No', 'Nama Pengguna', 'Role', 'Total Log', 'Konten', 'Kegiatan', 'Ruangan', 'Tamu', 'Aduan', 'Harmonisasi']],
            body: bodyData,
            startY: finalY + 40,
            theme: 'grid',
            styles: { fontSize: 8 },
            headStyles: { fillColor: [25, 135, 84] },
            margin: { left: 40 }
        });

        doc.save(`Statistik_Pengguna_${new Date().getTime()}.pdf`);
    });

    document.getElementById('btnExportWord').addEventListener('click', function () {
        if (window.statistikData.length === 0) return alert('Tidak ada data');

        const summary = window.statistikSummary || {};
        
        let userRows = '';
        window.statistikData.forEach((u, i) => {
            let roleBg = '#0dcaf020';
            let roleColor = '#0dcaf0';
            if (u.role === 'Admin') {
                roleBg = '#dc354520';
                roleColor = '#dc3545';
            } else if (u.role === 'Operator') {
                roleBg = '#0d6efd20';
                roleColor = '#0d6efd';
            } else if (u.role === 'p3h' || u.role === 'P3H') {
                roleBg = '#ffc10720';
                roleColor = '#ffc107';
            }

            userRows += `
                <tr>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">${i + 1}</td>
                    <td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">
                        <strong>${u.nama}</strong><br/>
                        <span style="font-size: 8pt; color: #888888;">@${u.username}</span>
                    </td>
                    <td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">
                        <span style="font-size: 8.5pt; padding: 2px 6px; border-radius: 10px; font-weight: bold; background-color: ${roleBg}; color: ${roleColor}; display: inline-block;">
                            ${u.role}
                        </span>
                    </td>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; font-weight: bold;">${u.total_log_aktivitas}</td>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">${u.total_konten}</td>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">${u.total_kegiatan}</td>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">${u.total_ruangan}</td>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">${u.total_tamu || 0}</td>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">${u.total_pengaduan || 0}</td>
                    <td style="text-align: center; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">${u.total_harmonisasi || 0}</td>
                </tr>
            `;
        });

        const htmlContent = `
        <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
            <meta charset='utf-8'>
            <title>Laporan Statistik Pengguna</title>
            <!--[if gte mso 9]>
            <xml>
                <w:WordDocument>
                    <w:View>Print</w:View>
                    <w:Zoom>100</w:Zoom>
                    <w:DoNotOptimizeForBrowser/>
                </w:WordDocument>
            </xml>
            <![endif]-->
            <style>
                @page Section1 {
                    size: 841.9pt 595.3pt; /* A4 Landscape in points */
                    margin: 1.0in 1.0in 1.0in 1.0in;
                    mso-header-margin: .5in;
                    mso-footer-margin: .5in;
                    mso-paper-source: 0;
                }
                div.Section1 {
                    page: Section1;
                }
                body { font-family: 'Arial', sans-serif; line-height: 1.5; color: #333333; }
                h1 { font-size: 18pt; color: #0d6efd; margin-top: 0; margin-bottom: 5px; }
                h2 { font-size: 12pt; color: #333333; margin-top: 25px; margin-bottom: 10px; border-bottom: 2px solid #0d6efd; padding-bottom: 3px; }
                p { font-size: 10pt; color: #666666; margin-top: 0; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
                th { background-color: #0d6efd; color: #ffffff; font-weight: bold; font-size: 10pt; border: 1px solid #dddddd; padding: 8px; text-align: left; }
                td { border: 1px solid #dddddd; padding: 8px; font-size: 10pt; }
                .text-center { text-align: center; }
            </style>
        </head>
        <body>
            <div class="Section1">
                <h1>Laporan Statistik Pengguna</h1>
                <p>Dicetak pada: ${new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</p>
                
                <h2>Ringkasan Total Aktivitas per Jenis</h2>
                <table style="width: 50%; max-width: 400px;">
                    <thead>
                        <tr>
                            <th style="background-color: #0d6efd; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Jenis Aktivitas</th>
                            <th style="background-color: #0d6efd; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 100px;">Total Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Jadwal Kegiatan</td><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center;">${summary.total_kegiatan || 0}</td></tr>
                        <tr><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Peminjaman Ruangan</td><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center;">${summary.total_peminjaman || 0}</td></tr>
                        <tr><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Konten</td><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center;">${summary.total_konten || 0}</td></tr>
                        <tr><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Tamu</td><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center;">${summary.total_tamu || 0}</td></tr>
                        <tr><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Aduan</td><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center;">${summary.total_pengaduan || 0}</td></tr>
                        <tr><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Harmonisasi</td><td style="border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center;">${summary.total_harmonisasi || 0}</td></tr>
                    </tbody>
                </table>

                <h2>Rincian Statistik per Pengguna</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 40px;">No</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt;">Nama Pengguna</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; width: 80px;">Role</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 70px;">Total Log</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 60px;">Konten</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 60px;">Kegiatan</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 65px;">Ruangan</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 50px;">Tamu</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 50px;">Aduan</th>
                            <th style="background-color: #198754; color: white; border: 1px solid #dddddd; padding: 8px; font-size: 10pt; text-align: center; width: 75px;">Harmonisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${userRows}
                    </tbody>
                </table>
            </div>
        </body>
        </html>
        `;

        const blob = new Blob(['\ufeff' + htmlContent], {
            type: 'application/msword'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Statistik_Pengguna_${new Date().getTime()}.doc`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    // Run First Init
    initCharts();
    fetchStatistik();
});
