</div> <!-- end dash-content -->
</section> <!-- end dashboard -->

<script src="/rekap-konten/public/js/script.js"></script>
<script src="/rekap-konten/public/js/rekap.js"></script>

<script>

// Sweet alert

// Data dari PHP
const detailBerita = <?= json_encode($detailBerita ?? []) ?>;
const detailMedsos = <?= json_encode($detailMedsos ?? []) ?>;

// === Modal Dashboard ===
const modalDashboard = document.getElementById("detailModal");
const modalTitle = document.getElementById("modalTitle");
const modalList = document.getElementById("modalList");

// Cek apakah modal elements ada sebelum mengaksesnya
if (modalDashboard && modalTitle && modalList) {
    const closeBtnDashboard = modalDashboard.querySelector(".close");

    function showDetail(type) {
        modalList.innerHTML = '';
        let data = [];

        if (type === 'berita') {
            modalTitle.textContent = "Rincian Total Berita";
            data = detailBerita;
        } else if (type === 'medsos') {
            modalTitle.textContent = "Rincian Postingan Medsos";
            data = detailMedsos;
        }

        data.forEach(item => {
            const li = document.createElement("li");
            li.textContent = `${item.name}: ${item.value}`;
            modalList.appendChild(li);
        });

        modalDashboard.style.display = "block";
    }

    function closeModalDashboard() {
        modalDashboard.style.display = "none";
    }

    // Event listeners hanya dipasang jika closeBtnDashboard ada
    if (closeBtnDashboard) {
        closeBtnDashboard.addEventListener("click", closeModalDashboard);
    }
    
    window.addEventListener("click", function(e) {
        if (e.target === modalDashboard) closeModalDashboard();
    });

    // Expose function globally untuk digunakan di halaman lain
    window.showDetail = showDetail;
    window.closeModalDashboard = closeModalDashboard;
}

// Event listener box dashboard (hanya jika modal ada)
if (modalDashboard && modalTitle && modalList) {
    document.querySelectorAll(".boxes .box[data-type]").forEach(box => {
        box.addEventListener("click", () => {
            const type = box.getAttribute("data-type");
            if (window.showDetail) {
                window.showDetail(type);
            }
        });
    });
}

// === Toggle form berdasarkan jenis konten ===
// const jenisSelect = document.getElementById("jenis");
// const formBerita = document.getElementById("form-berita");
// const formMedsos = document.getElementById("form-medsos");

// if (jenisSelect) {
//     jenisSelect.addEventListener("change", function() {
//         if (this.value === "berita") {
//             formBerita.style.display = "block";
//             formMedsos.style.display = "none";
//         } else if (this.value !== "") {
//             formBerita.style.display = "none";
//             formMedsos.style.display = "block";
//         } else {
//             formBerita.style.display = "none";
//             formMedsos.style.display = "none";
//         }
//     });
// }

// === Filter log aktivitas ===
const filterBtn = document.getElementById("filterBtn");
const resetBtn = document.getElementById("resetBtn");
const startDateInput = document.getElementById("startDate");
const endDateInput = document.getElementById("endDate");
const filterJenis = document.getElementById("filterJenis");
const filterKategori = document.getElementById("filterKategori");

function applyFilters() {
    const startDate = startDateInput.value ? new Date(startDateInput.value) : null;
    const endDate = endDateInput.value ? new Date(endDateInput.value) : null;
    const jenis = filterJenis.value.toLowerCase();
    const kategori = filterKategori.value.toLowerCase();

    const totalRows = document.querySelectorAll(".activity-data .data.no .data-list").length;

    for (let i = 0; i < totalRows; i++) {
        const dateText = document.querySelectorAll(".activity-data .data.date .data-list")[i]?.innerText || '';
        const jenisText = document.querySelectorAll(".activity-data .data.jenis .data-list")[i]?.innerText.toLowerCase() || '';
        const kategoriText = document.querySelectorAll(".activity-data .data.kategori .data-list")[i]?.innerText.toLowerCase() || '';
        const dateVal = new Date(dateText);

        const matchDate = (!startDate || !endDate) || (dateVal >= startDate && dateVal <= endDate);
        const matchJenis = (jenis === "all" || jenisText.includes(jenis));
        const matchKategori = (kategori === "all" || kategoriText.includes(kategori));

        const visible = matchDate && matchJenis && matchKategori;

        document.querySelectorAll(".activity-data .data").forEach(col => {
            if (col.children[i + 1]) col.children[i + 1].style.display = visible ? "" : "none";
        });
    }
}

if (filterBtn) filterBtn.addEventListener("click", applyFilters);
if (filterJenis) filterJenis.addEventListener("change", applyFilters);
if (filterKategori) filterKategori.addEventListener("change", applyFilters);

if (resetBtn) {
    resetBtn.addEventListener("click", () => {
        startDateInput.value = "";
        endDateInput.value = "";
        filterJenis.value = "all";
        filterKategori.value = "all";
        document.querySelectorAll(".activity-data .data .data-list").forEach(el => el.style.display = "");
    });
}

// === Modal Jadwal Kegiatan ===
// const keteranganList = [
//   "Kegiatan ini dihadiri oleh Kakanwil, Kabag Humas, dan seluruh kasubbag. Membahas rencana kerja bulan depan, evaluasi program, serta strategi peningkatan publikasi.",
//   "Sosialisasi kepada ASN baru tentang layanan hukum dan inovasi digital.",
//   "Pelatihan penggunaan aplikasi digital rekap konten.",
//   "Koordinasi kegiatan publikasi internal antar subbagian."
// ];

// const modalKegiatan = document.getElementById("keteranganModal");
// const closeBtnKegiatan = modalKegiatan.querySelector(".close");

// function showKeterangan(index) {
//     document.getElementById("modalText").textContent = keteranganList[index];
//     modalKegiatan.style.display = "block";
// }

// function closeModalKegiatan() {
//     modalKegiatan.style.display = "none";
// }

// // Pasang event listener ke tombol view
// document.querySelectorAll(".btn-action-aksi.view").forEach((btn, i) => {
//     btn.addEventListener("click", () => showKeterangan(i));
// });

// closeBtnKegiatan.addEventListener("click", closeModalKegiatan);
// window.addEventListener("click", function(event) {
//     if (event.target === modalKegiatan) closeModalKegiatan();
// });

// === Form Tambah Kegiatan ===
function to24HourFormat(timeStr) {
    const [hour, minute] = timeStr.split(":");
    return hour.padStart(2, "0") + "." + minute.padStart(2, "0");
}

// JavaScript untuk form kegiatan sudah dipindah ke tambah-kegiatan.php
</script>
