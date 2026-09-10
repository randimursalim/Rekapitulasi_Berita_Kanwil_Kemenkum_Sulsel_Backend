// Ambil elemen utama
const body = document.querySelector("body");
const modeToggle = document.querySelector(".mode-toggle");
const sidebar = document.querySelector("nav");
const sidebarToggle = document.querySelector(".sidebar-toggle");

// === Mode Dark/Light ===
let getMode = localStorage.getItem("mode");
if (getMode === "dark") body.classList.add("dark");

// === Sidebar Open/Close & Mobile Overlay ===
let sidebarOverlay = document.querySelector(".sidebar-overlay");
if (!sidebarOverlay) {
  sidebarOverlay = document.createElement("div");
  sidebarOverlay.className = "sidebar-overlay";
  document.body.appendChild(sidebarOverlay);
}

// Auto-close sidebar on mobile initial load
if (window.innerWidth <= 768) {
  if (sidebar) sidebar.classList.add("close");
} else {
  let getStatus = localStorage.getItem("status");
  if (getStatus === "close" && sidebar) sidebar.classList.add("close");
}

function syncMobileSidebarState() {
  if (!sidebar) return;
  const isMobile = window.innerWidth <= 768;
  const isClosed = sidebar.classList.contains("close");
  if (isMobile && !isClosed) {
    body.classList.add("sidebar-open");
  } else {
    body.classList.remove("sidebar-open");
  }
}

// Toggle dark mode
if (modeToggle) {
  modeToggle.addEventListener("click", () => {
    body.classList.toggle("dark");
    localStorage.setItem("mode", body.classList.contains("dark") ? "dark" : "light");
  });
}

// Toggle sidebar
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener("click", (e) => {
    e.stopPropagation();
    sidebar.classList.toggle("close");
    localStorage.setItem("status", sidebar.classList.contains("close") ? "close" : "open");
    syncMobileSidebarState();
  });
}

// Backdrop click closes sidebar on mobile
sidebarOverlay.addEventListener("click", () => {
  if (sidebar) {
    sidebar.classList.add("close");
    localStorage.setItem("status", "close");
    syncMobileSidebarState();
  }
});

// Close mobile sidebar when clicking any navigation link
document.querySelectorAll("nav .nav-links a").forEach(link => {
  link.addEventListener("click", () => {
    if (window.innerWidth <= 768 && sidebar) {
      sidebar.classList.add("close");
      syncMobileSidebarState();
    }
  });
});

window.addEventListener("resize", syncMobileSidebarState);
document.addEventListener("DOMContentLoaded", syncMobileSidebarState);
syncMobileSidebarState();

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
