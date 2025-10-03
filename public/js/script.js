// Ambil elemen utama
const body = document.querySelector("body");
const modeToggle = document.querySelector(".mode-toggle");
const sidebar = document.querySelector("nav");
const sidebarToggle = document.querySelector(".sidebar-toggle");

// Cek mode tersimpan di localStorage
let getMode = localStorage.getItem("mode");
if (getMode && getMode === "dark") {
    body.classList.add("dark");
}

// Cek status sidebar tersimpan
let getStatus = localStorage.getItem("status");
if (getStatus && getStatus === "close") {
    sidebar.classList.add("close");
}

// Fungsi untuk update tema chart saat dark/light mode
function updateChartTheme() {
    if (typeof rekapChart !== "undefined") {
        const isDark = body.classList.contains("dark");
        rekapChart.options.plugins.legend.labels.color = isDark ? "#fff" : "#333";
        rekapChart.options.plugins.datalabels.color = isDark ? "#fff" : "#000";
        rekapChart.options.scales.x.ticks.color = isDark ? "#fff" : "#333";
        rekapChart.options.scales.y.ticks.color = isDark ? "#fff" : "#333";
        rekapChart.update();
    }
}

// Event toggle dark mode
modeToggle.addEventListener("click", () => {
    body.classList.toggle("dark");

    if (body.classList.contains("dark")) {
        localStorage.setItem("mode", "dark");
    } else {
        localStorage.setItem("mode", "light");
    }

    updateChartTheme(); // update grafik biar ikutan
});

// Event toggle sidebar
sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");

    if (sidebar.classList.contains("close")) {
        localStorage.setItem("status", "close");
    } else {
        localStorage.setItem("status", "open");
    }
});

//detail dashboard//
// function showDetail(type) {
//   const modal = document.getElementById("detailModal");
//   const title = document.getElementById("modalTitle");
//   const list = document.getElementById("modalList");
  
//   list.innerHTML = ''; // reset isi

//   if (type === 'berita') {
//     title.textContent = "Rincian Total Berita";
//     const data = [
//       { name: "Media Online", value: 20 },
//       { name: "Surat Kabar", value: 45 },
//       { name: "Website Kanwil", value: 15 }
//     ];
//     data.forEach(item => {
//       const li = document.createElement("li");
//       li.textContent = `${item.name}: ${item.value}`;
//       list.appendChild(li);
//     });
//   }

//   if (type === 'medsos') {
//     title.textContent = "Rincian Postingan Medsos";
//     const data = [
//       { name: "Facebook", value: 10 },
//       { name: "Instagram", value: 8 },
//       { name: "Twitter (X)", value: 6 },
//       { name: "TikTok", value: 10 }
//     ];
//     data.forEach(item => {
//       const li = document.createElement("li");
//       li.textContent = `${item.name}: ${item.value}`;
//       list.appendChild(li);
//     });
//   }

//   modal.style.display = "block";
// }

// function closeModal() {
//   document.getElementById("detailModal").style.display = "none";
// }

// Tutup modal saat klik luar
// window.onclick = function(e) {
//   const modal = document.getElementById("detailModal");
//   if (e.target === modal) modal.style.display = "none";
// };


// Modal Img //
const imgModal = document.getElementById("imgModal");
  const modalImage = document.getElementById("modalImage");

  // Event delegation agar bisa klik semua gambar
  document.addEventListener("click", function(e) {
    if (e.target.tagName === "IMG" && e.target.closest(".data-list")) {
      modalImage.src = e.target.src;
      imgModal.style.display = "flex";
    }
  });

  imgModal.addEventListener("click", function() {
    imgModal.style.display = "none";
  });