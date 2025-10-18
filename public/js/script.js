// Ambil elemen utama
const body = document.querySelector("body");
const modeToggle = document.querySelector(".mode-toggle");
const sidebar = document.querySelector("nav");
const sidebarToggle = document.querySelector(".sidebar-toggle");

// === Mode Dark/Light ===
let getMode = localStorage.getItem("mode");
if (getMode === "dark") body.classList.add("dark");

// === Sidebar Open/Close ===
let getStatus = localStorage.getItem("status");
if (getStatus === "close") sidebar.classList.add("close");

// Toggle dark mode
if (modeToggle) {
  modeToggle.addEventListener("click", () => {
    body.classList.toggle("dark");
    localStorage.setItem("mode", body.classList.contains("dark") ? "dark" : "light");
    // Chart theme akan diupdate oleh MutationObserver di rekap.js
  });
}

// Toggle sidebar
if (sidebarToggle) {
  sidebarToggle.addEventListener("click", () => {
    sidebar.classList.toggle("close");
    localStorage.setItem("status", sidebar.classList.contains("close") ? "close" : "open");
  });
}

//=== Dashboard Detail Modal ===
// const detailModal = document.getElementById("detailModal");
// const modalTitle = document.getElementById("modalTitle");
// const modalList = document.getElementById("modalList");

// if (detailModal && modalTitle && modalList) {
//   window.showDetail = function(type, data = []) {
//     modalList.innerHTML = "";
//     if (type === "berita") modalTitle.textContent = "Rincian Total Berita";
//     else if (type === "medsos") modalTitle.textContent = "Rincian Postingan Medsos";

//     data.forEach(item => {
//       const li = document.createElement("li");
//       li.textContent = `${item.name}: ${item.value}`;
//       modalList.appendChild(li);
//     });

//     detailModal.style.display = "block";
//   };

//   window.closeModal = function() {
//     detailModal.style.display = "none";
//   };

//   window.addEventListener("click", function(e) {
//     if (e.target === detailModal) detailModal.style.display = "none";
//   });
// }

// === Modal Image (arsip.php) ===
const imgModal = document.getElementById("imgModal");
const modalImage = document.getElementById("modalImage");

if (imgModal && modalImage) {
  document.addEventListener("click", function(e) {
    if (e.target.tagName === "IMG" && e.target.closest(".data-list")) {
      modalImage.src = e.target.src;
      imgModal.style.display = "flex";
    }
  });

  imgModal.addEventListener("click", function() {
    imgModal.style.display = "none";
  });
}
