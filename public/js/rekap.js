// Pastikan elemen canvas ada dulu
const canvas = document.getElementById("rekapChart");

if (canvas) {
  const ctx = canvas.getContext("2d");

  // Data awal kosong
  const chartData = {
    labels: [],
    datasets: [{
      label: "Jumlah Konten",
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
            color: "#333"
          }
        },
        datalabels: {
          anchor: "end",
          align: "top",
          color: "#000",
          font: { weight: "bold" },
          formatter: (value) => value
        }
      },
      layout: {
        padding: { top: 20 }
      },
      scales: {
        x: { ticks: { color: "#333" } },
        y: { beginAtZero: true, ticks: { color: "#333" } }
      }
    },
    plugins: [ChartDataLabels]
  });

  // Fungsi untuk fetch data dari backend
  async function fetchRekapData(filter = 'monthly', startDate = null, endDate = null, jenis = 'all') {
    try {
      const params = new URLSearchParams({
        filter: filter,
        jenis: jenis
      });
      
      if (startDate) params.append('startDate', startDate);
      if (endDate) params.append('endDate', endDate);

      const response = await fetch(`index.php?page=get-rekap-data&${params}`);
      const result = await response.json();

      if (result.success) {
        updateChart(result.data);
      } else {
        console.error('Error fetching rekap data:', result.error);
        showError('Gagal memuat data rekap');
      }
    } catch (error) {
      console.error('Error fetching rekap data:', error);
      showError('Terjadi kesalahan saat memuat data');
    }
  }

  // Fungsi untuk update chart dengan data baru
  function updateChart(data) {
    // Jika tidak ada data, tampilkan skeleton
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
    const totalEl = document.getElementById("totalBerita");
    if (totalEl) {
      if (total !== null) {
        totalEl.innerText = "Total Konten: " + total;
      } else {
        const total = rekapChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
        totalEl.innerText = "Total Konten: " + total;
      }
    }

    // Update scale jika ada data
    if (rekapChart.data.datasets[0].data.length > 0) {
      const maxVal = Math.max(...rekapChart.data.datasets[0].data);
      const margin = Math.ceil(maxVal * 0.1);
      rekapChart.options.scales.y.suggestedMax = maxVal + margin;
      rekapChart.update();
    }
  }

  // Fungsi untuk menampilkan error
  function showError(message) {
    const totalEl = document.getElementById("totalBerita");
    if (totalEl) {
      totalEl.innerText = message;
      totalEl.style.color = 'red';
    }
  }

  // Fungsi untuk memuat dropdown periode dinamis
  async function loadAvailablePeriods() {
    try {
      const response = await fetch('index.php?page=get-available-periods');
      const result = await response.json();

      if (result.success) {
        populateDropdowns(result.data);
      } else {
        console.error('Error loading periods:', result.error);
      }
    } catch (error) {
      console.error('Error loading periods:', error);
    }
  }

  // Fungsi untuk mengisi dropdown bulan dan tahun
  function populateDropdowns(data) {
    const bulanSelect = document.getElementById('filterBulan');
    const tahunSelect = document.getElementById('filterTahun');

    if (!bulanSelect || !tahunSelect) return;

    // Clear existing options
    bulanSelect.innerHTML = '<option value="">-- Pilih Bulan --</option>';
    tahunSelect.innerHTML = '<option value="">-- Pilih Tahun --</option>';

    // Month names mapping
    const monthNames = {
      1: 'Januari', 2: 'Februari', 3: 'Maret', 4: 'April',
      5: 'Mei', 6: 'Juni', 7: 'Juli', 8: 'Agustus',
      9: 'September', 10: 'Oktober', 11: 'November', 12: 'Desember'
    };

    // Populate months
    data.months.forEach(month => {
      const option = document.createElement('option');
      option.value = month;
      option.textContent = monthNames[month] || `Bulan ${month}`;
      bulanSelect.appendChild(option);
    });

    // Populate years
    data.years.forEach(year => {
      const option = document.createElement('option');
      option.value = year;
      option.textContent = year;
      tahunSelect.appendChild(option);
    });

    // Set default to latest period if available
    if (data.periods.length > 0) {
      const latest = data.periods[0];
      bulanSelect.value = latest.bulan;
      tahunSelect.value = latest.tahun;
      
      // Update table title
      updateTableTitle(latest.bulan, latest.tahun);
      
      // Load data for default period
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
      title.textContent = `REKAP PUBLIKASI DAN GLORIFIKASI BULAN ${monthNames[bulan] || bulan} TAHUN ${tahun}`;
    }
  }

  // Load data awal
  fetchRekapData('monthly');
  loadAvailablePeriods();

  // Filter waktu
  document.querySelectorAll(".filter-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const filter = btn.dataset.filter;
      
      // Remove active class from all buttons
      document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
      
      // Add active class to clicked button
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
      const jenis = document.getElementById("filterJenis").value;
      
      if (!startDate || !endDate) {
        alert("Pilih rentang tanggal dulu!");
        return;
      }
      
      // Remove active class from all filter buttons
      document.querySelectorAll(".filter-btn").forEach(b => b.classList.remove("active"));
      
      // Untuk range tanggal, gunakan filter 'range' bukan 'daily'
      fetchRekapData('range', startDate, endDate, jenis);
    });
  }

  // Filter jenis konten
  const filterJenis = document.getElementById("filterJenis");
  if (filterJenis) {
    filterJenis.addEventListener("change", (e) => {
      const jenis = e.target.value;
      
      // Cek apakah ada filter tanggal yang aktif
      const startDate = document.getElementById("start-date").value;
      const endDate = document.getElementById("end-date").value;
      
      // Cek apakah ada tombol filter yang aktif
      const activeFilterBtn = document.querySelector(".filter-btn.active");
      let currentFilter = 'monthly'; // default
      
      if (activeFilterBtn) {
        currentFilter = activeFilterBtn.dataset.filter;
      }
      
      // Jika ada range tanggal, gunakan filter range
      if (startDate && endDate) {
        fetchRekapData('range', startDate, endDate, jenis);
      } else {
        // Gunakan filter yang sedang aktif
        fetchRekapData(currentFilter, null, null, jenis);
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
      document.getElementById("filterJenis").value = "all";
      
      // Reset tombol filter aktif
      document.querySelectorAll(".filter-btn").forEach(btn => {
        btn.classList.remove("active");
      });
      
      // Load data default (bulanan)
      fetchRekapData('monthly');
    });
  }

  // Download JPG
  const downloadJPG = document.getElementById("downloadJPG");
  if (downloadJPG) {
    downloadJPG.addEventListener("click", () => {
      const url = canvas.toDataURL("image/jpeg", 1.0);
      const link = document.createElement("a");
      link.href = url;
      link.download = "rekap-konten.jpg";
      link.click();
    });
  }

  // Download PDF
  const downloadPDF = document.getElementById("downloadPDF");
  if (downloadPDF) {
    downloadPDF.addEventListener("click", () => {
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF("landscape");
      const imgData = canvas.toDataURL("image/png", 1.0);
      pdf.setFontSize(16);
      pdf.text("Rekap Konten - KEMENKUM SULSEL", 15, 20);
      pdf.addImage(imgData, "PNG", 15, 30, 260, 120);
      const total = rekapChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
      pdf.setFontSize(12);
      pdf.text("Total Konten: " + total, 15, 160);
      pdf.save("rekap-konten.pdf");
    });
  }

  // Fungsi untuk fetch data tabel dari backend
  async function fetchRekapTabel(bulan = null, tahun = null) {
    try {
      const params = new URLSearchParams();
      if (bulan) params.append('bulan', bulan);
      if (tahun) params.append('tahun', tahun);

      const response = await fetch(`index.php?page=get-rekap-tabel&${params}`);
      const result = await response.json();

      if (result.success) {
        console.log('Tabel data received:', result.data);
        updateTabel(result.data);
      } else {
        console.error('Error fetching tabel data:', result.error);
      }
    } catch (error) {
      console.error('Error fetching tabel data:', error);
    }
  }

  // Fungsi untuk update tabel dengan data baru
  function updateTabel(data) {
    // Update data di tabel menggunakan ID
    const mediaOnlineEl = document.getElementById('mediaOnline');
    const websiteKanwilEl = document.getElementById('websiteKanwil');
    const instagramEl = document.getElementById('instagram');
    const twitterEl = document.getElementById('twitter');
    const youtubeEl = document.getElementById('youtube');
    const facebookEl = document.getElementById('facebook');

    if (mediaOnlineEl) mediaOnlineEl.textContent = (data.media_online || 0) + ' Rilis Berita';
    if (websiteKanwilEl) websiteKanwilEl.textContent = (data.website_kanwil || 0) + ' Berita';
    if (instagramEl) instagramEl.textContent = (data.instagram || 0) + ' Postingan';
    if (twitterEl) twitterEl.textContent = (data.twitter || 0) + ' Twit';
    if (youtubeEl) youtubeEl.textContent = (data.youtube || 0) + ' Video';
    if (facebookEl) facebookEl.textContent = (data.facebook || 0) + ' Postingan';
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
      
      // Update table title
      updateTableTitle(parseInt(bulan), parseInt(tahun));
      
      // Fetch data tabel
      fetchRekapTabel(parseInt(bulan), parseInt(tahun));
    });
  }

  // Download tabel PDF
  const downloadTablePDF = document.getElementById("downloadTablePDF");
  if (downloadTablePDF) {
    downloadTablePDF.addEventListener("click", () => {
      const { jsPDF } = window.jspdf;
      const doc = new jsPDF("p", "pt", "a4");
      const title = document.getElementById("tableTitle")?.innerText || "";
      doc.setFontSize(14);
      doc.text(title, 40, 40);
      doc.autoTable({ html: "#rekapTable", startY: 60, theme: "grid", styles: { fontSize: 10 } });
      doc.save("rekap_tabel.pdf");
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
        <head><meta charset='utf-8'><title>Rekap</title>
        <style>
          table { border-collapse: collapse; width: 100%; font-family: Arial; }
          table, th, td { border: 1px solid #000; }
          th, td { padding: 8px 12px; text-align: center; }
          th { background: #f2f2f2; } h3 { text-align: center; }
        </style></head>
        <body><h3>${title}</h3>${table}</body></html>`;
      const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = "rekap_tabel.doc";
      link.click();
    });
  }

} // END IF canvas
