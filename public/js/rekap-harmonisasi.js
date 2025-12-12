// Pastikan elemen canvas ada dulu
const canvas = document.getElementById("rekapChart");

if (canvas) {
  const ctx = canvas.getContext("2d");

  // Data awal kosong
  const chartData = {
    labels: [],
    datasets: [{
      label: "Jumlah Harmonisasi",
      data: [],
      backgroundColor: "rgba(54, 162, 235, 0.7)",
      borderColor: "rgba(54, 162, 235, 1)",
      borderWidth: 1
    }]
  };

  // Inisialisasi chart
  const rekapChart = new Chart(ctx, {
    type: "bar",
    data: chartData,
    options: {
      responsive: true,
      plugins: {
        legend: {
          display: true,
          position: "top",
          align: "center",
          labels: {
            padding: 20,
            color: function(context) {
              const isDark = document.body.classList.contains('dark');
              return isDark ? '#ffffff' : '#333333';
            }
          }
        },
        datalabels: {
          anchor: "end",
          align: "top",
          color: function(context) {
            const isDark = document.body.classList.contains('dark');
            return isDark ? '#ffffff' : '#000000';
          },
          font: { weight: "bold" },
          formatter: (value) => value
        }
      },
      layout: {
        padding: { top: 20 }
      },
      scales: {
        x: { 
          ticks: { 
            color: function(context) {
              const isDark = document.body.classList.contains('dark');
              return isDark ? '#ffffff' : '#333333';
            }
          } 
        },
        y: { 
          beginAtZero: true, 
          ticks: { 
            color: function(context) {
              const isDark = document.body.classList.contains('dark');
              return isDark ? '#ffffff' : '#333333';
            }
          } 
        }
      }
    },
    plugins: [ChartDataLabels]
  });

  // Fungsi untuk fetch data dari backend
  async function fetchRekapData(filter = 'monthly', startDate = null, endDate = null, status = 'all') {
    try {
      const params = new URLSearchParams({
        filter: filter,
        status: status
      });
      
      if (startDate) params.append('startDate', startDate);
      if (endDate) params.append('endDate', endDate);

      const response = await fetch(`index.php?page=get-rekap-data-harmonisasi&${params}`);
      const result = await response.json();

      if (result.success) {
        updateChart(result.data);
      } else {
        showError('Gagal memuat data rekap');
      }
    } catch (error) {
      showError('Terjadi kesalahan saat memuat data');
    }
  }

  // Fungsi untuk update chart dengan data baru
  function updateChart(data) {
    if (!data.labels || data.labels.length === 0) {
      rekapChart.data.labels = ['Belum Ada Data'];
      rekapChart.data.datasets[0].data = [0];
      rekapChart.data.datasets[0].backgroundColor = "rgba(200, 200, 200, 0.7)";
      rekapChart.data.datasets[0].borderColor = "rgba(200, 200, 200, 1)";
    } else {
      rekapChart.data.labels = data.labels;
      rekapChart.data.datasets[0].data = data.data;
      rekapChart.data.datasets[0].backgroundColor = "rgba(54, 162, 235, 0.7)";
      rekapChart.data.datasets[0].borderColor = "rgba(54, 162, 235, 1)";
    }
    
    rekapChart.update();
    updateTotal(data.total || 0);
  }

  // Fungsi update total
  function updateTotal(total = null) {
    const totalEl = document.getElementById("totalHarmonisasi");
    if (totalEl) {
      if (total !== null) {
        totalEl.innerText = "Total Harmonisasi: " + total;
      } else {
        const total = rekapChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
        totalEl.innerText = "Total Harmonisasi: " + total;
      }
    }

    if (rekapChart.data.datasets[0].data.length > 0) {
      const maxVal = Math.max(...rekapChart.data.datasets[0].data);
      const margin = Math.ceil(maxVal * 0.1);
      rekapChart.options.scales.y.suggestedMax = maxVal + margin;
      rekapChart.update();
    }
  }

  // Fungsi untuk menampilkan error
  function showError(message) {
    const totalEl = document.getElementById("totalHarmonisasi");
    if (totalEl) {
      totalEl.innerText = message;
      totalEl.style.color = 'red';
    }
  }

  // Fungsi untuk memuat dropdown periode dinamis
  async function loadAvailablePeriods() {
    try {
      const response = await fetch('index.php?page=get-available-periods-harmonisasi');
      const result = await response.json();

      if (result.success) {
        populateDropdowns(result.data);
      }
    } catch (error) {
      // Error handling tanpa console log
    }
  }

  // Fungsi untuk mengisi dropdown bulan dan tahun
  function populateDropdowns(data) {
    const bulanSelect = document.getElementById('filterBulan');
    const tahunSelect = document.getElementById('filterTahun');

    if (!bulanSelect || !tahunSelect) return;

    bulanSelect.innerHTML = '<option value="">-- Pilih Bulan --</option>';
    tahunSelect.innerHTML = '<option value="">-- Pilih Tahun --</option>';

    const monthNames = {
      1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
      5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
      9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
    };

    const months = [...new Set(data.map(d => d.bulan))].sort((a, b) => a - b);
    const years = [...new Set(data.map(d => d.tahun))].sort((a, b) => b - a);

    months.forEach(month => {
      const option = document.createElement('option');
      option.value = month;
      option.textContent = monthNames[month] || `Bulan ${month}`;
      bulanSelect.appendChild(option);
    });

    years.forEach(year => {
      const option = document.createElement('option');
      option.value = year;
      option.textContent = year;
      tahunSelect.appendChild(option);
    });

    if (data.length > 0) {
      const latest = data[0];
      bulanSelect.value = latest.bulan;
      tahunSelect.value = latest.tahun;
      
      updateTableTitle(latest.bulan, latest.tahun);
      fetchRekapTabel(latest.bulan, latest.tahun);
    }
  }

  // Fungsi untuk update judul tabel
  function updateTableTitle(bulan, tahun) {
    const title = document.getElementById('tableTitle');
    if (title && bulan && tahun) {
      const monthNames = {
        1: 'JANUARI', 2: 'FEBRUARI', 3: 'MARET', 4: 'APRIL',
        5: 'MEI', 6: 'JUNI', 7: 'JULI', 8: 'AGUSTUS',
        9: 'SEPTEMBER', 10: 'OKTOBER', 11: 'NOVEMBER', 12: 'DESEMBER'
      };
      title.textContent = `REKAP HARMONISASI PERUNDANG-UNDANGAN BULAN ${monthNames[bulan] || bulan} TAHUN ${tahun}`;
    }
  }

  function updateBulanTabel(bulan, tahun) {
    const bulanEl = document.getElementById('bulanTabel');
    if (bulanEl && bulan && tahun) {
      const monthNames = {
        1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
        5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
        9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
      };
      bulanEl.textContent = `${monthNames[bulan] || bulan} ${tahun}`;
    }
  }

  // Fungsi untuk update warna chart berdasarkan mode dark/light
  function updateChartColors() {
    const isDark = document.body.classList.contains('dark');
    const textColor = isDark ? '#ffffff' : '#333333';
    
    rekapChart.options.plugins.legend.labels.color = textColor;
    rekapChart.options.plugins.datalabels.color = textColor;
    rekapChart.options.scales.x.ticks.color = textColor;
    rekapChart.options.scales.y.ticks.color = textColor;
    
    rekapChart.update('active');
  }

  // Load data awal
  fetchRekapData('monthly');
  loadAvailablePeriods();
  
  // Listen untuk perubahan mode dark/light
  const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
      if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
        updateChartColors();
      }
    });
  });
  
  observer.observe(document.body, {
    attributes: true,
    attributeFilter: ['class']
  });

  // Filter waktu
  document.querySelectorAll(".filter-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const filter = btn.dataset.filter;
      
      document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
      btn.classList.add("active");
      
      fetchRekapData(filter);
    });
  });

  // Filter range tanggal
  const applyRangeBtn = document.getElementById("apply-range");
  if (applyRangeBtn) {
    applyRangeBtn.addEventListener("click", () => {
      const startDate = document.getElementById("start-date").value;
      const endDate = document.getElementById("end-date").value;
      const status = document.getElementById("filterStatus").value;
      
      if (!startDate || !endDate) {
        alert("Pilih rentang tanggal dulu!");
        return;
      }
      
      document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
      fetchRekapData('range', startDate, endDate, status);
    });
  }

  // Filter status
  const filterStatus = document.getElementById("filterStatus");
  if (filterStatus) {
    filterStatus.addEventListener("change", (e) => {
      const status = e.target.value;
      
      const startDate = document.getElementById("start-date").value;
      const endDate = document.getElementById("end-date").value;
      
      const activeFilterBtn = document.querySelector(".filter-btn.active");
      let currentFilter = 'monthly';
      
      if (activeFilterBtn) {
        currentFilter = activeFilterBtn.dataset.filter;
      }
      
      if (startDate && endDate) {
        fetchRekapData('range', startDate, endDate, status);
      } else {
        fetchRekapData(currentFilter, null, null, status);
      }
    });
  }

  // Reset filter
  const resetFilterBtn = document.getElementById("reset-filter");
  if (resetFilterBtn) {
    resetFilterBtn.addEventListener("click", () => {
      // Reset semua filter ke default
      document.getElementById("start-date").value = "";
      document.getElementById("end-date").value = "";
      document.getElementById("filterStatus").value = "all";
      
      // Reset tombol filter aktif
      document.querySelectorAll(".filter-btn").forEach(btn => {
        btn.classList.remove("active");
      });
      
      // Set tombol "Bulanan" sebagai aktif (default)
      const monthlyBtn = document.querySelector(".filter-btn[data-filter='monthly']");
      if (monthlyBtn) {
        monthlyBtn.classList.add("active");
      }
      
      // Load data default (bulanan)
      fetchRekapData('monthly');
    });
  }

  // Download JPG
  const downloadJPG = document.getElementById("downloadJPG");
  if (downloadJPG) {
    downloadJPG.addEventListener("click", () => {
      const originalLegendColor = rekapChart.options.plugins.legend.labels.color;
      const originalDatalabelsColor = rekapChart.options.plugins.datalabels.color;
      const originalXTicksColor = rekapChart.options.scales.x.ticks.color;
      const originalYTicksColor = rekapChart.options.scales.y.ticks.color;
      
      rekapChart.options.plugins.legend.labels.color = "#333333";
      rekapChart.options.plugins.datalabels.color = "#000000";
      rekapChart.options.scales.x.ticks.color = "#333333";
      rekapChart.options.scales.y.ticks.color = "#333333";
      
      rekapChart.update('none');
      
      setTimeout(() => {
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        
        tempCanvas.width = canvas.width;
        tempCanvas.height = canvas.height;
        
        tempCtx.fillStyle = '#ffffff';
        tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        tempCtx.drawImage(canvas, 0, 0);
        
        const url = tempCanvas.toDataURL("image/jpeg", 0.95);
        const link = document.createElement("a");
        link.href = url;
        link.download = "rekap-harmonisasi-" + new Date().toISOString().split('T')[0] + ".jpg";
        link.click();
        
        rekapChart.options.plugins.legend.labels.color = originalLegendColor;
        rekapChart.options.plugins.datalabels.color = originalDatalabelsColor;
        rekapChart.options.scales.x.ticks.color = originalXTicksColor;
        rekapChart.options.scales.y.ticks.color = originalYTicksColor;
        rekapChart.update('none');
      }, 100);
    });
  }

  // Download PDF
  const downloadPDF = document.getElementById("downloadPDF");
  if (downloadPDF) {
    downloadPDF.addEventListener("click", () => {
      const originalLegendColor = rekapChart.options.plugins.legend.labels.color;
      const originalDatalabelsColor = rekapChart.options.plugins.datalabels.color;
      const originalXTicksColor = rekapChart.options.scales.x.ticks.color;
      const originalYTicksColor = rekapChart.options.scales.y.ticks.color;
      
      rekapChart.options.plugins.legend.labels.color = "#333333";
      rekapChart.options.plugins.datalabels.color = "#000000";
      rekapChart.options.scales.x.ticks.color = "#333333";
      rekapChart.options.scales.y.ticks.color = "#333333";
      
      rekapChart.update('none');
      
      setTimeout(() => {
        try {
          const { jsPDF } = window.jspdf;
          const pdf = new jsPDF("landscape");
          
          const tempCanvas = document.createElement('canvas');
          const tempCtx = tempCanvas.getContext('2d');
          
          tempCanvas.width = canvas.width;
          tempCanvas.height = canvas.height;
          
          tempCtx.fillStyle = '#ffffff';
          tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
          tempCtx.drawImage(canvas, 0, 0);
          
          const imgData = tempCanvas.toDataURL("image/png", 1.0);
          
          pdf.setFontSize(16);
          pdf.setTextColor(0, 0, 0);
          pdf.text("Rekap Harmonisasi - KEMENKUM SULSEL", 15, 20);
          
          pdf.addImage(imgData, "PNG", 15, 30, 260, 120);
          
          const total = rekapChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
          pdf.setFontSize(12);
          pdf.setTextColor(0, 0, 0);
          pdf.text("Total Harmonisasi: " + total, 15, 160);
          
          pdf.save("rekap-harmonisasi-" + new Date().toISOString().split('T')[0] + ".pdf");
          
          rekapChart.options.plugins.legend.labels.color = originalLegendColor;
          rekapChart.options.plugins.datalabels.color = originalDatalabelsColor;
          rekapChart.options.scales.x.ticks.color = originalXTicksColor;
          rekapChart.options.scales.y.ticks.color = originalYTicksColor;
          rekapChart.update('none');
          
        } catch (error) {
          alert('Gagal mengexport PDF. Silakan coba lagi.');
          
          rekapChart.options.plugins.legend.labels.color = originalLegendColor;
          rekapChart.options.plugins.datalabels.color = originalDatalabelsColor;
          rekapChart.options.scales.x.ticks.color = originalXTicksColor;
          rekapChart.options.scales.y.ticks.color = originalYTicksColor;
          rekapChart.update('none');
        }
      }, 100);
    });
  }

  // Fungsi untuk fetch data tabel dari backend
  async function fetchRekapTabel(bulan = null, tahun = null) {
    try {
      const params = new URLSearchParams();
      if (bulan) params.append('bulan', bulan);
      if (tahun) params.append('tahun', tahun);

      const response = await fetch(`index.php?page=get-rekap-tabel-harmonisasi&${params}`);
      const result = await response.json();

      if (result.success) {
        updateTabel(result.data);
      }
    } catch (error) {
      // Error handling tanpa console log
    }
  }

  // Fungsi untuk update tabel dengan data baru
  function updateTabel(data) {
    const diterimaEl = document.getElementById('diterima');
    const dikembalikanEl = document.getElementById('dikembalikan');
    const totalEl = document.getElementById('total');
    const bulanEl = document.getElementById('bulanTabel');

    if (diterimaEl) diterimaEl.textContent = (data.diterima || 0);
    if (dikembalikanEl) dikembalikanEl.textContent = (data.dikembalikan || 0);
    if (totalEl) totalEl.textContent = (data.total || 0);
    if (bulanEl && data.bulan && data.tahun) {
      const monthNames = {
        1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
        5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
        9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
      };
      bulanEl.textContent = `${monthNames[parseInt(data.bulan)] || data.bulan} ${data.tahun}`;
    }
  }

  // Filter tabel
  const applyFilter = document.getElementById("applyFilter");
  if (applyFilter) {
    applyFilter.addEventListener("click", () => {
      const bulan = document.getElementById("filterBulan").value;
      const tahun = document.getElementById("filterTahun").value;
      
      if (!bulan || !tahun) {
        alert("Pilih bulan dan tahun terlebih dahulu!");
        return;
      }
      
      updateTableTitle(parseInt(bulan), parseInt(tahun));
      updateBulanTabel(parseInt(bulan), parseInt(tahun));
      fetchRekapTabel(parseInt(bulan), parseInt(tahun));
    });
  }

  // Download tabel PDF
  const downloadTablePDF = document.getElementById("downloadTablePDF");
  if (downloadTablePDF) {
    downloadTablePDF.addEventListener("click", () => {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF("l", "pt", "a4");
      const title = document.getElementById("tableTitle")?.innerText || "";
      doc.setFontSize(14);
      doc.text(title, doc.internal.pageSize.getWidth() / 2, 40, { align: 'center' });
      doc.autoTable({ 
        html: "#rekapTable", 
        startY: 60, 
        theme: "grid", 
        styles: { 
          fontSize: 8,
          cellPadding: 3
        },
        headStyles: {
          fontSize: 8,
          fontStyle: 'bold'
        }
      });
      doc.save("Rekap_Harmonisasi_" + new Date().toISOString().split('T')[0] + ".pdf");
    });
  }

  // Download tabel Word
  const downloadTableWord = document.getElementById("downloadTableWord");
  if (downloadTableWord) {
    downloadTableWord.addEventListener("click", () => {
      const table = document.getElementById("rekapTable")?.outerHTML || "";
      const title = document.getElementById("tableTitle")?.innerText || "";
      const htmlContent = `
        <html xmlns:o='urn:schemas-microsoft-com:office:office'
              xmlns:w='urn:schemas-microsoft-com:office:word'
              xmlns='http://www.w3.org/TR/REC-html40'>
        <head>
          <meta charset='utf-8'>
          <title>Rekap Harmonisasi</title>
          <!--[if gte mso 9]>
          <xml>
            <w:WordDocument>
              <w:View>Print</w:View>
              <w:Zoom>90</w:Zoom>
              <w:DoNotOptimizeForBrowser/>
            </w:WordDocument>
          </xml>
          <![endif]-->
          <style>
            @page { size: 8.5in 11in; margin: 0.5in; }
            body { font-family: Arial, sans-serif; }
            h3 { text-align: center; }
            table { border-collapse: collapse; width: 100%; }
            th, td { border: 1px solid #000; padding: 8px; text-align: center; }
            th { background-color: #f0f0f0; font-weight: bold; }
          </style>
        </head>
        <body>
          <h3>${title}</h3>
          ${table}
        </body>
        </html>
      `;
      
      const blob = new Blob([htmlContent], { type: 'application/msword' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = "Rekap_Harmonisasi_" + new Date().toISOString().split('T')[0] + ".doc";
      link.click();
      URL.revokeObjectURL(url);
    });
  }

}

// ============================================
// PENCARIAN KATA KUNCI
// ============================================
document.addEventListener('DOMContentLoaded', function() {
  // Global variables untuk pencarian
  let searchKeywords = [];
  let searchCurrentPage = 1;
  let searchTotalPages = 1;
  let searchTotalData = 0;
  let searchItemsPerPage = 10;
  let searchCurrentFilters = {
    status: 'all',
    startDate: '',
    endDate: ''
  };

  // DOM elements
  const keywordInput = document.getElementById('keywordInput');
  const addKeywordBtn = document.getElementById('addKeywordBtn');
  const keywordsList = document.getElementById('keywordsList');
  const searchBtn = document.getElementById('searchBtn');
  const resetSearchBtn = document.getElementById('resetSearchBtn');
  const searchResultsSection = document.getElementById('searchResultsSection');
  const searchResults = document.getElementById('searchResults');
  const searchStartDate = document.getElementById('searchStartDate');
  const searchEndDate = document.getElementById('searchEndDate');
  const searchFilterStatus = document.getElementById('searchFilterStatus');
  const downloadSearchWord = document.getElementById('downloadSearchWord');
  const downloadSearchExcel = document.getElementById('downloadSearchExcel');

  // Check if elements exist
  if (!keywordInput || !addKeywordBtn || !keywordsList || !searchBtn) {
    return; // Exit if search section doesn't exist
  }

  // Helper functions
  function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function formatDate(dateStr) {
    if (!dateStr || dateStr === '-') return '-';
    try {
      const date = new Date(dateStr);
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    } catch (e) {
      return dateStr;
    }
  }

  function formatSearchDate(dateStr) {
    if (!dateStr || dateStr === '-') return '-';
    try {
      const date = new Date(dateStr);
      // Format: DD/MM/YYYY
      const day = String(date.getDate()).padStart(2, '0');
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    } catch (e) {
      return dateStr;
    }
  }

  function escapeXml(text) {
    if (!text) return '';
    return String(text)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&apos;');
  }

  // Tambah keyword
  function addKeyword() {
    const keyword = keywordInput.value.trim();
    if (keyword && !searchKeywords.includes(keyword)) {
      searchKeywords.push(keyword);
      renderKeywords();
      keywordInput.value = '';
      keywordInput.focus();
    }
  }

  // Hapus keyword
  function removeKeyword(keyword) {
    searchKeywords = searchKeywords.filter(k => k !== keyword);
    renderKeywords();
  }

  // Render keywords list
  function renderKeywords() {
    if (!keywordsList) return;
    
    if (searchKeywords.length === 0) {
      keywordsList.innerHTML = '<span style="color: var(--text-color); opacity: 0.5;">Kata kunci (opsional)</span>';
      return;
    }

    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#3085d6';
    
    keywordsList.innerHTML = searchKeywords.map(keyword => `
      <span style="display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; background: ${primaryColor}; color: white; border-radius: 20px; font-size: 14px;">
        ${escapeHtml(keyword)}
        <button onclick="removeSearchKeywordHarmonisasi('${escapeHtml(keyword)}')" style="background: rgba(255,255,255,0.3); border: none; color: white; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;">
          ×
        </button>
      </span>
    `).join('');
  }

  // Expose remove keyword function globally
  window.removeSearchKeywordHarmonisasi = function(keyword) {
    removeKeyword(keyword);
  };

  // Event listeners
  addKeywordBtn.addEventListener('click', addKeyword);
  keywordInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      addKeyword();
    }
  });

  searchBtn.addEventListener('click', function() {
    const hasKeywords = searchKeywords.length > 0;
    const hasFilterStatus = searchFilterStatus.value !== 'all';
    const hasFilterDate = searchStartDate.value && searchEndDate.value;
    const hasAnyFilter = hasFilterStatus || hasFilterDate;
    
    if (!hasKeywords && !hasAnyFilter) {
      Swal.fire({
        icon: 'warning',
        title: 'Filter Kosong',
        text: 'Silakan tambahkan kata kunci atau pilih filter untuk pencarian',
        showConfirmButton: true
      });
      return;
    }
    
    searchCurrentFilters.status = searchFilterStatus.value;
    searchCurrentFilters.startDate = searchStartDate.value;
    searchCurrentFilters.endDate = searchEndDate.value;
    searchCurrentPage = 1;
    loadSearchResults(1);
  });

  resetSearchBtn.addEventListener('click', function() {
    searchKeywords = [];
    searchCurrentFilters = {
      status: 'all',
      startDate: '',
      endDate: ''
    };
    searchFilterStatus.value = 'all';
    searchStartDate.value = '';
    searchEndDate.value = '';
    keywordInput.value = '';
    renderKeywords();
    searchResultsSection.style.display = 'none';
  });

  // Load search results
  async function loadSearchResults(page = 1) {
    if (!searchResults) return;
    
    try {
      const panelColor = getComputedStyle(document.documentElement).getPropertyValue('--panel-color').trim() || '#fff';
      const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim() || '#333';
      const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#3085d6';
      
      searchResults.innerHTML = `<div style="text-align: center; padding: 40px; max-width: 600px; width: 100%; margin: 0 auto; display: block !important; background: ${panelColor}; color: ${textColor};"><i class="fas fa-spinner fa-spin" style="font-size: 32px; color: ${primaryColor};"></i><p style="margin-top: 15px; color: ${textColor};">Memuat data...</p></div>`;
      searchResultsSection.style.display = 'block';

      const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
      const fetchUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/ajax/fetch_search_harmonisasi.php') : 'ajax/fetch_search_harmonisasi.php';

      // Untuk menghitung total data, cukup fetch 1 data saja (tidak perlu semua data)
      const params = new URLSearchParams({
        page: 1,
        keywords: searchKeywords.join(','),
        filterStatus: searchCurrentFilters.status === 'all' ? '' : searchCurrentFilters.status,
        startDate: searchCurrentFilters.startDate,
        endDate: searchCurrentFilters.endDate,
        limit: 1 // Hanya ambil 1 data untuk menghitung total, tidak perlu semua data
      });

      const response = await fetch(`${fetchUrl}?${params}`);
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();

      if (!result.success) {
        searchResults.innerHTML = `<p style="color:red;">Gagal memuat data: ${result.error || 'Unknown error'}</p>`;
        return;
      }

      // Hanya ambil total data dari pagination, tidak perlu simpan data
      searchTotalPages = result.pagination.totalPages;
      searchTotalData = result.pagination.totalData;
      searchCurrentPage = result.pagination.currentPage;

      // Render hanya jumlah data, bukan tabel
      renderSearchResults(searchTotalData);

    } catch (error) {
      searchResults.innerHTML = '<p style="color:red;">Terjadi kesalahan saat memuat data.</p>';
    }
  }

  // Render search results - hanya menampilkan jumlah data
  function renderSearchResults(totalData) {
    if (!searchResults) return;
    
    // Get CSS variables untuk dark mode support
    const panelColor = getComputedStyle(document.documentElement).getPropertyValue('--panel-color').trim() || '#fff';
    const textColor = getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim() || '#333';
    const borderColor = getComputedStyle(document.documentElement).getPropertyValue('--border-color').trim() || '#ddd';
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#3085d6';
    
    if (totalData === 0) {
      searchResults.innerHTML = `
        <div style="text-align: center; padding: 40px; background: ${panelColor}; border-radius: 8px; border: 1px solid ${borderColor}; max-width: 600px; width: 100%; margin: 0 auto; display: block !important; color: ${textColor};">
          <i class="fas fa-search" style="font-size: 48px; color: ${textColor}; opacity: 0.5; margin-bottom: 15px;"></i>
          <p style="color: ${textColor}; font-size: 1.1rem; margin: 0;">Tidak ada data ditemukan</p>
        </div>
      `;
      return;
    }

    // Buat informasi filter yang digunakan
    let filterInfo = [];
    if (searchKeywords.length > 0) {
      filterInfo.push(`Kata kunci: ${searchKeywords.join(', ')}`);
    }
    if (searchCurrentFilters.status !== 'all') {
      filterInfo.push(`Status: ${searchCurrentFilters.status}`);
    }
    if (searchCurrentFilters.startDate && searchCurrentFilters.endDate) {
      filterInfo.push(`Tanggal: ${formatSearchDate(searchCurrentFilters.startDate)} - ${formatSearchDate(searchCurrentFilters.endDate)}`);
    }
    
    const html = `
      <div style="text-align: center; padding: 40px; background: ${panelColor}; border-radius: 8px; border: 1px solid ${borderColor}; max-width: 600px; width: 100%; margin: 0 auto; display: block !important; color: ${textColor};">
        <div style="margin-bottom: 20px;">
          <i class="fas fa-check-circle" style="font-size: 64px; color: ${primaryColor}; margin-bottom: 15px;"></i>
          <h3 style="color: ${textColor}; font-size: 1.5rem; margin: 0 0 10px 0; font-weight: bold;">
            ${totalData} data ditemukan
          </h3>
          ${filterInfo.length > 0 ? `
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid ${borderColor};">
              <p style="color: ${textColor}; opacity: 0.8; font-size: 0.9rem; margin: 5px 0;">
                ${filterInfo.join(' • ')}
              </p>
            </div>
          ` : ''}
        </div>
        <p style="color: ${textColor}; opacity: 0.8; font-size: 0.95rem; margin: 20px 0 0 0;">
          Gunakan tombol <strong style="color: ${textColor};">Download Word</strong> atau <strong style="color: ${textColor};">Download Excel</strong> untuk melihat detail data
        </p>
      </div>
    `;

    searchResults.innerHTML = html;
  }

  // Download Word
  if (downloadSearchWord) {
    downloadSearchWord.addEventListener('click', async function() {
      try {
        const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
        const fetchUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/ajax/fetch_search_harmonisasi.php') : 'ajax/fetch_search_harmonisasi.php';

        const params = new URLSearchParams({
          page: 1,
          keywords: searchKeywords.join(','),
          filterStatus: searchCurrentFilters.status === 'all' ? '' : searchCurrentFilters.status,
          startDate: searchCurrentFilters.startDate,
          endDate: searchCurrentFilters.endDate,
          limit: 10000 // Download all data
        });

        const response = await fetch(`${fetchUrl}?${params}`);
        const result = await response.json();

        if (!result.success || !result.data || result.data.length === 0) {
          Swal.fire({
            icon: 'warning',
            title: 'Tidak Ada Data',
            text: 'Tidak ada data untuk diunduh'
          });
          return;
        }

        // Buat tabel HTML
        let tableHTML = `
          <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
            <thead>
              <tr>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">No</th>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">Judul Rancangan</th>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">Pemrakarsa</th>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">Pemerintah Daerah</th>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">Tanggal Rapat</th>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">Pemegang Draf</th>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">Status</th>
                <th style="border: 1px solid #000; padding: 8px; background: #f0f0f0; text-align: center;">Alasan Pengembalian Draf</th>
              </tr>
            </thead>
            <tbody>
        `;

        result.data.forEach((h, index) => {
          tableHTML += `
            <tr>
              <td style="border: 1px solid #000; padding: 6px; text-align: center;">${index + 1}</td>
              <td style="border: 1px solid #000; padding: 6px;">${escapeHtml(h.judul_rancangan || '-')}</td>
              <td style="border: 1px solid #000; padding: 6px;">${escapeHtml(h.pemrakarsa || '-')}</td>
              <td style="border: 1px solid #000; padding: 6px;">${escapeHtml(h.pemerintah_daerah || '-')}</td>
              <td style="border: 1px solid #000; padding: 6px; text-align: center;">${formatDate(h.tanggal_rapat)}</td>
              <td style="border: 1px solid #000; padding: 6px;">${escapeHtml(h.pemegang_draf || '-')}</td>
              <td style="border: 1px solid #000; padding: 6px; text-align: center;">${escapeHtml(h.status || 'Diterima')}</td>
              <td style="border: 1px solid #000; padding: 6px;">${escapeHtml(h.alasan_pengembalian_draf || '-')}</td>
            </tr>
          `;
        });

        tableHTML += `
            </tbody>
          </table>
        `;

        const htmlContent = `
          <html xmlns:o='urn:schemas-microsoft-com:office:office'
                xmlns:w='urn:schemas-microsoft-com:office:word'
                xmlns='http://www.w3.org/TR/REC-html40'>
          <head>
            <meta charset='utf-8'>
            <title>Hasil Pencarian Harmonisasi</title>
            <!--[if gte mso 9]>
            <xml>
              <w:WordDocument>
                <w:View>Print</w:View>
                <w:Zoom>90</w:Zoom>
                <w:DoNotOptimizeForBrowser/>
              </w:WordDocument>
            </xml>
            <![endif]-->
            <style>
              @page { size: A4 landscape; margin: 1cm; }
              body { font-family: Arial, sans-serif; }
              table { border-collapse: collapse; width: 100%; font-size: 10px; }
              th, td { border: 1px solid #000; padding: 4px 6px; }
              th { background: #f2f2f2; font-weight: bold; text-align: center; }
              td { text-align: left; }
            </style>
          </head>
          <body>
            <h3 style="text-align: center;">Hasil Pencarian Harmonisasi</h3>
            ${tableHTML}
          </body>
          </html>
        `;

        const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = "Hasil_Pencarian_Harmonisasi_" + new Date().toISOString().split('T')[0] + ".doc";
        link.click();
        URL.revokeObjectURL(url);

      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Gagal mengunduh data'
        });
      }
    });
  }

  // Download Excel
  if (downloadSearchExcel) {
    downloadSearchExcel.addEventListener('click', async function() {
      try {
        const baseUrl = (typeof window.BASE_URL !== 'undefined' && window.BASE_URL) ? window.BASE_URL : '';
        const fetchUrl = baseUrl ? (baseUrl.replace(/\/$/, '') + '/ajax/fetch_search_harmonisasi.php') : 'ajax/fetch_search_harmonisasi.php';

        const params = new URLSearchParams({
          page: 1,
          keywords: searchKeywords.join(','),
          filterStatus: searchCurrentFilters.status === 'all' ? '' : searchCurrentFilters.status,
          startDate: searchCurrentFilters.startDate,
          endDate: searchCurrentFilters.endDate,
          limit: 10000 // Download all data
        });

        const response = await fetch(`${fetchUrl}?${params}`);
        const result = await response.json();

        if (!result.success || !result.data || result.data.length === 0) {
          Swal.fire({
            icon: 'warning',
            title: 'Tidak Ada Data',
            text: 'Tidak ada data untuk diunduh'
          });
          return;
        }

        // Gunakan SheetJS untuk membuat file Excel yang benar
        if (typeof XLSX === 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Library Excel tidak tersedia. Silakan refresh halaman.'
          });
          return;
        }

        // Siapkan data untuk Excel
        let excelData = [];
        
        // Header row
        let headers = ['No', 'Judul Rancangan', 'Pemrakarsa', 'Pemerintah Daerah', 'Tanggal Rapat', 'Pemegang Draf', 'Status', 'Alasan Pengembalian Draf'];
        excelData.push(headers);

        // Data rows
        result.data.forEach((h, index) => {
          let row = [
            index + 1,
            h.judul_rancangan || '-',
            h.pemrakarsa || '-',
            h.pemerintah_daerah || '-',
            formatDate(h.tanggal_rapat),
            h.pemegang_draf || '-',
            h.status || 'Diterima',
            h.alasan_pengembalian_draf || '-'
          ];
          excelData.push(row);
        });

        // Buat workbook
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(excelData);
        
        // Set column widths
        ws['!cols'] = [
          { wch: 5 },   // No
          { wch: 50 },  // Judul Rancangan
          { wch: 25 },  // Pemrakarsa
          { wch: 25 },  // Pemerintah Daerah
          { wch: 12 },  // Tanggal Rapat
          { wch: 20 },  // Pemegang Draf
          { wch: 15 },  // Status
          { wch: 40 }   // Alasan Pengembalian Draf
        ];

        // Tambahkan worksheet ke workbook
        XLSX.utils.book_append_sheet(wb, ws, 'Hasil Pencarian');

        // Download file
        XLSX.writeFile(wb, 'Hasil_Pencarian_Harmonisasi_' + new Date().toISOString().split('T')[0] + '.xlsx');

      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Gagal mengunduh data'
        });
      }
    });
  }
});

