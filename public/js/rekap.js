// Ambil canvas chart
let ctx = document.getElementById("rekapChart").getContext("2d");

// Dummy data awal
let chartData = {
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
let rekapChart = new Chart(ctx, {
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
            x: {
                ticks: { color: "#333" }
            },
            y: { 
                beginAtZero: true,
                ticks: { color: "#333" }
            }
        }
    },
    plugins: [ChartDataLabels]
});

// Fungsi update total konten + skala Y
function updateTotal() {
    let total = rekapChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
    document.getElementById("totalBerita").innerText = "Total Konten: " + total;

    let maxVal = Math.max(...rekapChart.data.datasets[0].data);
    let margin = Math.ceil(maxVal * 0.1);
    rekapChart.options.scales.y.suggestedMax = maxVal + margin;

    rekapChart.update();
}
updateTotal();

// Filter waktu (daily, weekly, monthly, yearly)
document.querySelectorAll(".filter-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        const filter = btn.dataset.filter;

        if (filter === "daily") {
            rekapChart.data.labels = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];
            rekapChart.data.datasets[0].data = [2, 3, 1, 5, 4, 0, 6];
        }
        else if (filter === "weekly") {
            rekapChart.data.labels = ["Minggu 1", "Minggu 2", "Minggu 3", "Minggu 4"];
            rekapChart.data.datasets[0].data = [10, 14, 7, 9];
        }
        else if (filter === "monthly") {
            rekapChart.data.labels = ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun"];
            rekapChart.data.datasets[0].data = [5, 8, 3, 12, 7, 9];
        }
        else if (filter === "yearly") {
            rekapChart.data.labels = ["2021", "2022", "2023", "2024", "2025"];
            rekapChart.data.datasets[0].data = [45, 67, 52, 80, 34];
        }

        updateTotal();
    });
});

// Filter custom range
document.getElementById("apply-range").addEventListener("click", () => {
    let startDate = document.getElementById("start-date").value;
    let endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
        alert("Pilih rentang tanggal dulu!");
        return;
    }

    rekapChart.data.labels = ["Rentang Terpilih"];
    rekapChart.data.datasets[0].data = [Math.floor(Math.random() * 20)]; 
    updateTotal();
});

// 🔹 Filter jenis konten
document.getElementById("filterJenis").addEventListener("change", (e) => {
    let jenis = e.target.value;

    if (jenis === "all") {
        rekapChart.data.datasets[0].data = [23, 8, 3, 12, 7, 9, 12, 7, 14, 11, 23, 15];
        rekapChart.data.datasets[0].label = "Semua Konten";
    } 
    else if (jenis === "berita") {
        rekapChart.data.datasets[0].data = [5, 2, 1, 3, 4, 2, 1, 3, 5, 4, 6, 7];
        rekapChart.data.datasets[0].label = "Berita (Total)";
    }
    else if (jenis === "media_online") {
        rekapChart.data.datasets[0].data = [2, 1, 0, 1, 2, 1, 0, 1, 2, 1, 3, 2];
        rekapChart.data.datasets[0].label = "Berita - Media Online";
    }
    else if (jenis === "surat_kabar") {
        rekapChart.data.datasets[0].data = [1, 0, 0, 1, 1, 0, 1, 2, 1, 1, 2, 1];
        rekapChart.data.datasets[0].label = "Berita - Surat Kabar";
    }
    else if (jenis === "website_kanwil") {
        rekapChart.data.datasets[0].data = [2, 1, 1, 1, 1, 1, 0, 1, 2, 2, 1, 1];
        rekapChart.data.datasets[0].label = "Berita - Website Kanwil";
    }
    else if (jenis === "facebook") {
        rekapChart.data.datasets[0].data = [2, 1, 0, 2, 3, 1, 2, 4, 2, 3, 2, 1];
        rekapChart.data.datasets[0].label = "Facebook";
    } 
    else if (jenis === "tiktok") {
        rekapChart.data.datasets[0].data = [1, 0, 1, 2, 1, 1, 2, 3, 1, 2, 0, 1];
        rekapChart.data.datasets[0].label = "Tiktok";
    } 
    else if (jenis === "twitter") {
        rekapChart.data.datasets[0].data = [3, 2, 1, 4, 3, 2, 1, 2, 4, 5, 3, 2];
        rekapChart.data.datasets[0].label = "Twitter (X)";
    } 
    else if (jenis === "youtube") {
        rekapChart.data.datasets[0].data = [4, 3, 2, 3, 5, 4, 6, 5, 3, 4, 6, 5];
        rekapChart.data.datasets[0].label = "Youtube";
    }

    rekapChart.update();
    updateTotal();
});


// Download JPG
document.getElementById("downloadJPG").addEventListener("click", () => {
    const canvas = document.getElementById("rekapChart");
    const url = canvas.toDataURL("image/jpeg", 1.0);

    const link = document.createElement("a");
    link.href = url;
    link.download = "rekap-konten.jpg";
    link.click();
});

// Download PDF
document.getElementById("downloadPDF").addEventListener("click", () => {
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF("landscape");

    const canvas = document.getElementById("rekapChart");
    const imgData = canvas.toDataURL("image/png", 1.0);

    pdf.setFontSize(16);
    pdf.text("Rekap Konten - KEMENKUM SULSEL", 15, 20);
    pdf.addImage(imgData, "PNG", 15, 30, 260, 120);

    let total = rekapChart.data.datasets[0].data.reduce((a, b) => a + b, 0);
    pdf.setFontSize(12);
    pdf.text("Total Konten: " + total, 15, 160);

    pdf.save("rekap-konten.pdf");
});


// UNTUK TABEL //
document.getElementById("applyFilter").addEventListener("click", function() {
  const bulan = document.getElementById("filterBulan").value;
  const tahun = document.getElementById("filterTahun").value;

  // Ubah judul tabel sesuai pilihan
  document.getElementById("tableTitle").textContent =
    `REKAP PUBLIKASI DAN GLORIFIKASI BULAN ${bulan.toUpperCase()} TAHUN ${tahun}`;
});

// === Download Tabel ke PDF ===
  document.getElementById("downloadTablePDF").addEventListener("click", () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF("p", "pt", "a4");

    // Judul tabel
    const title = document.getElementById("tableTitle").innerText;
    doc.setFontSize(14);
    doc.text(title, 40, 40);

    // Ambil tabel & convert
    doc.autoTable({
      html: "#rekapTable",
      startY: 60,
      theme: "grid",
      styles: { fontSize: 10 },
    });

    doc.save("rekap_tabel.pdf");
  });

  // === Download Tabel ke Word ===
document.getElementById("downloadTableWord").addEventListener("click", () => {
  const table = document.getElementById("rekapTable").outerHTML;
  const title = document.getElementById("tableTitle").innerText;

  const htmlContent = `
    <html xmlns:o='urn:schemas-microsoft-com:office:office'
          xmlns:w='urn:schemas-microsoft-com:office:word'
          xmlns='http://www.w3.org/TR/REC-html40'>
    <head>
      <meta charset='utf-8'>
      <title>Rekap</title>
      <style>
        table { 
          border-collapse: collapse; 
          width: 100%; 
          font-family: Arial, sans-serif; 
        }
        table, th, td {
          border: 1px solid #000; 
        }
        th, td {
          padding: 8px 12px; 
          text-align: center;
        }
        th {
          background: #f2f2f2;
        }
        h3 {
          text-align: center;
        }
      </style>
    </head>
    <body>
      <h3>${title}</h3>
      ${table}
    </body>
    </html>`;

  const blob = new Blob(['\ufeff', htmlContent], {
    type: 'application/msword'
  });

  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "rekap_tabel.doc";
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
});
