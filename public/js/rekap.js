// Pastikan elemen canvas ada dulu
const canvas = document.getElementById("rekapChart");

if (canvas) {
  const ctx = canvas.getContext("2d");

  // Dummy data awal
  const chartData = {
    labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
    datasets: [{
      label: "Jumlah Konten",
      data: [23, 8, 3, 12, 7, 9, 12, 7, 14, 11, 23, 15],
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

  // Fungsi update total
  function updateTotal() {
    const total = rekapChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
    const totalEl = document.getElementById("totalBerita");
    if (totalEl) totalEl.innerText = "Total Konten: " + total;

    const maxVal = Math.max(...rekapChart.data.datasets[0].data);
    const margin = Math.ceil(maxVal * 0.1);
    rekapChart.options.scales.y.suggestedMax = maxVal + margin;
    rekapChart.update();
  }
  updateTotal();

  // Filter waktu
  document.querySelectorAll(".filter-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const filter = btn.dataset.filter;
      if (filter === "daily") {
        rekapChart.data.labels = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
        rekapChart.data.datasets[0].data = [2, 3, 1, 5, 4, 0, 6];
      } else if (filter === "weekly") {
        rekapChart.data.labels = ["Minggu 1", "Minggu 2", "Minggu 3", "Minggu 4"];
        rekapChart.data.datasets[0].data = [10, 14, 7, 9];
      } else if (filter === "monthly") {
        rekapChart.data.labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun"];
        rekapChart.data.datasets[0].data = [5, 8, 3, 12, 7, 9];
      } else if (filter === "yearly") {
        rekapChart.data.labels = ["2021", "2022", "2023", "2024", "2025"];
        rekapChart.data.datasets[0].data = [45, 67, 52, 80, 34];
      }
      updateTotal();
    });
  });

  // Filter range tanggal
  const applyRangeBtn = document.getElementById("apply-range");
  if (applyRangeBtn) {
    applyRangeBtn.addEventListener("click", () => {
      const startDate = document.getElementById("start-date").value;
      const endDate = document.getElementById("end-date").value;
      if (!startDate || !endDate) return alert("Pilih rentang tanggal dulu!");
      rekapChart.data.labels = ["Rentang Terpilih"];
      rekapChart.data.datasets[0].data = [Math.floor(Math.random() * 20)];
      updateTotal();
    });
  }

  // Filter jenis konten
  const filterJenis = document.getElementById("filterJenis");
  if (filterJenis) {
    filterJenis.addEventListener("change", (e) => {
      const jenis = e.target.value;
      const d = rekapChart.data.datasets[0];
      if (jenis === "all") {
        d.data = [23, 8, 3, 12, 7, 9, 12, 7, 14, 11, 23, 15];
        d.label = "Semua Konten";
      } else if (jenis === "berita") {
        d.data = [5, 2, 1, 3, 4, 2, 1, 3, 5, 4, 6, 7];
        d.label = "Berita (Total)";
      }
      rekapChart.update();
      updateTotal();
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

  // Filter tabel
  const applyFilter = document.getElementById("applyFilter");
  if (applyFilter) {
    applyFilter.addEventListener("click", () => {
      const bulan = document.getElementById("filterBulan").value;
      const tahun = document.getElementById("filterTahun").value;
      const title = document.getElementById("tableTitle");
      if (title) title.textContent = `REKAP PUBLIKASI DAN GLORIFIKASI BULAN ${bulan.toUpperCase()} TAHUN ${tahun}`;
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
