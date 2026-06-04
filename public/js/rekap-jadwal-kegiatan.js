document.addEventListener('DOMContentLoaded', function() {
    let rekapChart = null;
    let availableYearsLoaded = false;
  
    const filterBulan = document.getElementById('filterBulan');
    const filterTahun = document.getElementById('filterTahun');
    const filterPimti = document.getElementById('filterPimti');
    const filterStatus = document.getElementById('filterStatus');
    const keywordInput = document.getElementById('keywordInput');
    const applyFilterBtn = document.getElementById('applyFilter');
    const resetFilterBtn = document.getElementById('resetFilter');
  
    // Populate bulan
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    months.forEach((m, i) => {
        const option = document.createElement('option');
        option.value = i + 1;
        option.textContent = m;
        filterBulan.appendChild(option);
    });
  
    // Set default filter to current month and year
    const now = new Date();
    filterBulan.value = now.getMonth() + 1;
  
    // Function to load data
    function loadRekapData() {
        const searchResults = document.getElementById('searchResults');
        if (searchResults) {
            searchResults.innerHTML = `
            <div style="text-align: center; padding: 40px; background: var(--panel-color); border: 1px solid var(--border-color); border-radius: 8px; width: 100%; color: var(--text-color);">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px; color: var(--primary-color);"></i>
                <p style="margin-top: 15px;">Memuat data...</p>
            </div>`;
        }
  
        const params = new URLSearchParams({
            bulan: filterBulan.value,
            tahun: filterTahun.value,
            pimti: filterPimti.value,
            status: filterStatus.value,
            keyword: keywordInput.value
        });
  
        fetch(`ajax/fetch_rekap_kegiatan.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateYearOptions(data.years);
                    window.allRekapData = data.data; // Simpan ke global
                    renderSummary(data.data.length);
                    renderChart(data.chart);
                    document.getElementById('totalKegiatan').textContent = `Total Kegiatan: ${data.data.length}`;
                } else {
                    if(searchResults) searchResults.innerHTML = `<div style="text-align: center; padding: 40px; color: red;">Error: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error("Error fetching data:", error);
                if(searchResults) searchResults.innerHTML = '<div style="text-align: center; padding: 40px; color: red;">Gagal memuat data.</div>';
            });
    }
  
    function populateYearOptions(years) {
        if (availableYearsLoaded || !years) return;
        
        filterTahun.innerHTML = '<option value="all">Semua Tahun</option>';
        years.forEach(y => {
            if (y) {
                const option = document.createElement('option');
                option.value = y;
                option.textContent = y;
                if (y == now.getFullYear()) {
                    option.selected = true;
                }
                filterTahun.appendChild(option);
            }
        });
        
        if (!filterTahun.value || filterTahun.value === "all") {
             if (years.includes(now.getFullYear().toString()) || years.includes(now.getFullYear())) {
                  filterTahun.value = now.getFullYear();
             } else if (years.length > 0) {
                  filterTahun.value = years[0];
             }
        }
        
        availableYearsLoaded = true;
    }
  
    function renderSummary(totalData) {
        const searchResults = document.getElementById('searchResults');
        if (!searchResults) return;

        if (totalData === 0) {
            searchResults.innerHTML = `
            <div style="text-align: center; padding: 40px; background: var(--panel-color); border-radius: 8px; border: 1px solid var(--border-color); width: 100%; margin: 0 auto; display: block !important; color: var(--text-color);">
                <i class="fas fa-search" style="font-size: 48px; opacity: 0.5; margin-bottom: 15px;"></i>
                <p style="font-size: 1.1rem; margin: 0;">Tidak ada data ditemukan</p>
            </div>`;
            return;
        }

        const html = `
        <div style="text-align: center; padding: 40px; background: var(--panel-color); border-radius: 8px; border: 1px solid var(--border-color); width: 100%; margin: 0 auto; display: block !important; color: var(--text-color);">
            <div style="margin-bottom: 20px;">
                <i class="fas fa-check-circle" style="font-size: 64px; color: var(--primary-color); margin-bottom: 15px;"></i>
                <h3 style="font-size: 1.5rem; margin: 0 0 10px 0; font-weight: bold;">
                    ${totalData} data kegiatan ditemukan
                </h3>
            </div>
            <p style="opacity: 0.8; font-size: 0.95rem; margin: 20px 0 0 0;">
                Gunakan tombol <strong>Download Word</strong>, <strong>Excel</strong>, atau <strong>PDF</strong> untuk melihat detail data
            </p>
        </div>`;
        searchResults.innerHTML = html;
    }
  
    function renderChart(chartData) {
        const ctx = document.getElementById('rekapChart').getContext('2d');
        
        if (rekapChart) {
            rekapChart.destroy();
        }
  
        let chartLabel = "Jumlah Kegiatan";
        if (filterTahun.value !== 'all' && filterBulan.value === 'all') {
            chartLabel = "Jumlah Kegiatan per Bulan Tahun " + filterTahun.value;
        } else if (filterBulan.value !== 'all' && filterTahun.value !== 'all') {
            chartLabel = "Jumlah Kegiatan per Tanggal";
        }
  
        rekapChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: chartLabel,
                    data: chartData.values,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
  
    // Event Listeners
    applyFilterBtn.addEventListener('click', loadRekapData);
    
    resetFilterBtn.addEventListener('click', () => {
        filterBulan.value = now.getMonth() + 1;
        if(document.querySelector(`#filterTahun option[value="${now.getFullYear()}"]`)) {
             filterTahun.value = now.getFullYear();
        } else {
             filterTahun.value = "all";
        }
        filterPimti.value = 'all';
        filterStatus.value = 'all';
        keywordInput.value = '';
        loadRekapData();
    });
    
    keywordInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            loadRekapData();
        }
    });
  
    // Initial Load - load twice to ensure years are populated, then fetch with correct year
    // Wait for the DOM and then execute
    setTimeout(() => {
         fetch(`ajax/fetch_rekap_kegiatan.php`)
         .then(res => res.json())
         .then(data => {
              if (data.success && data.years) {
                   populateYearOptions(data.years);
              }
              loadRekapData();
         })
         .catch(err => {
              console.error(err);
              loadRekapData();
         });
    }, 100);
  
    // ==========================================
    // EXPORT FUNCTIONS
    // ==========================================
  
    document.getElementById('downloadTableExcel').addEventListener('click', function() {
        if (!window.allRekapData || window.allRekapData.length === 0) {
            alert('Tidak ada data untuk di-download');
            return;
        }

        let excelData = [];
        let headers = ['No', 'Tanggal & Waktu', 'Nama Kegiatan', 'Pimti Hadir', 'Keterangan', 'Status'];
        excelData.push(headers);

        window.allRekapData.forEach((item, index) => {
            const dateObj = new Date(item.tanggal);
            const dateStr = dateObj.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            const timeStr = `${item.jam_mulai.substring(0,5)} - ${item.jam_selesai.substring(0,5)}`;
            
            let pimti = [];
            if (item.hadir_kakanwil == 1) pimti.push('Kakanwil');
            if (item.hadir_kadiv_p3h == 1) pimti.push('Kadiv P3H');
            if (item.hadir_kadiv_yankum == 1) pimti.push('Kadiv Yankum');
            let pimtiStr = pimti.length > 0 ? pimti.map((p, i) => `${i + 1}. ${p}`).join('\n') : '-';

            excelData.push([
                index + 1,
                `${dateStr}\n${timeStr}`,
                item.nama_kegiatan || '-',
                pimtiStr,
                item.keterangan || '-',
                item.status || '-'
            ]);
        });

        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(excelData);
        ws['!cols'] = [{ wch: 5 }, { wch: 20 }, { wch: 35 }, { wch: 20 }, { wch: 40 }, { wch: 15 }];
        XLSX.utils.book_append_sheet(wb, ws, 'Rekap Jadwal');
        XLSX.writeFile(wb, `Rekap_Jadwal_Kegiatan_${new Date().getTime()}.xlsx`);
    });
  
    document.getElementById('downloadTableWord').addEventListener('click', function() {
        if (!window.allRekapData || window.allRekapData.length === 0) {
            alert('Tidak ada data untuk di-download');
            return;
        }

        let tableRows = '';
        window.allRekapData.forEach((item, index) => {
            const dateObj = new Date(item.tanggal);
            const dateStr = dateObj.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            const timeStr = `${item.jam_mulai.substring(0,5)} - ${item.jam_selesai.substring(0,5)}`;
            
            let pimti = [];
            if (item.hadir_kakanwil == 1) pimti.push('Kakanwil');
            if (item.hadir_kadiv_p3h == 1) pimti.push('Kadiv P3H');
            if (item.hadir_kadiv_yankum == 1) pimti.push('Kadiv Yankum');
            let pimtiStr = pimti.length > 0 ? pimti.map((p, i) => `${i + 1}. ${p}`).join('<br>') : '-';
            
            const ket = item.keterangan ? item.keterangan.replace(/\\n/g, '<br>') : '-';

            tableRows += `
                <tr>
                    <td style="text-align: center;">${index + 1}</td>
                    <td>${dateStr}<br>${timeStr}</td>
                    <td>${item.nama_kegiatan || '-'}</td>
                    <td>${pimtiStr}</td>
                    <td>${ket}</td>
                    <td style="text-align: center;">${item.status || '-'}</td>
                </tr>
            `;
        });

        const html = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset="utf-8">
                <title>Rekap Jadwal Kegiatan</title>
                <style>
                    table { border-collapse: collapse; width: 100%; border: 1px solid black; }
                    th, td { border: 1px solid black; padding: 8px; text-align: left; vertical-align: top; }
                    th { text-align: center; font-weight: bold; background-color: #f2f2f2; }
                </style>
            </head>
            <body>
                <h2 style="text-align: center;">Rekapitulasi Jadwal Kegiatan</h2>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">Tanggal & Waktu</th>
                            <th style="width: 25%;">Nama Kegiatan</th>
                            <th style="width: 15%;">Pimti Hadir</th>
                            <th style="width: 25%;">Keterangan</th>
                            <th style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tableRows}
                    </tbody>
                </table>
            </body>
            </html>
        `;
        const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `Rekap_Jadwal_Kegiatan_${new Date().getTime()}.doc`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
    
    document.getElementById('downloadTablePDF').addEventListener('click', function() {
        if (!window.allRekapData || window.allRekapData.length === 0) {
            alert('Tidak ada data untuk di-download');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('landscape');
        
        const pageWidth = doc.internal.pageSize.getWidth();
        doc.text("Rekapitulasi Jadwal Kegiatan", pageWidth / 2, 15, { align: 'center' });
        
        let bodyData = [];
        window.allRekapData.forEach((item, index) => {
            const dateObj = new Date(item.tanggal);
            const dateStr = dateObj.toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' });
            const timeStr = `${item.jam_mulai.substring(0,5)} - ${item.jam_selesai.substring(0,5)}`;
            
            let pimti = [];
            if (item.hadir_kakanwil == 1) pimti.push('Kakanwil');
            if (item.hadir_kadiv_p3h == 1) pimti.push('Kadiv P3H');
            if (item.hadir_kadiv_yankum == 1) pimti.push('Kadiv Yankum');
            let pimtiStr = pimti.length > 0 ? pimti.map((p, i) => `${i + 1}. ${p}`).join('\n') : '-';

            bodyData.push([
                index + 1,
                `${dateStr}\n${timeStr}`,
                item.nama_kegiatan || '-',
                pimtiStr,
                item.keterangan || '-',
                item.status || '-'
            ]);
        });

        doc.autoTable({
            head: [['No', 'Tanggal & Waktu', 'Nama Kegiatan', 'Pimti Hadir', 'Keterangan', 'Status']],
            body: bodyData,
            startY: 25,
            theme: 'grid',
            styles: { fontSize: 8, cellPadding: 3 },
            headStyles: { fillColor: [41, 128, 185], textColor: 255, halign: 'center' },
            columnStyles: {
                0: { halign: 'center', cellWidth: 15 },
                1: { cellWidth: 40 },
                2: { cellWidth: 60 },
                3: { cellWidth: 40 },
                4: { cellWidth: 80 },
                5: { halign: 'center', cellWidth: 35 }
            }
        });
        
        doc.save(`Rekap_Jadwal_Kegiatan_${new Date().getTime()}.pdf`);
    });
});
