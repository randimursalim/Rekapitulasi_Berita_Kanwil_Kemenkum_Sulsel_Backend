<?php
// app/views/pages/jadwal-kegiatan.php
?>

<div class="overview">
    <div class="title">
        <i class="uil uil-schedule"></i>
        <span class="text">Jadwal Kegiatan</span>
    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-kegiatan'">
            <i class="uil uil-plus"></i> Tambah Kegiatan
        </button>
    </div>

    <!-- Tabel Jadwal Kegiatan -->
    <div class="activity" style="margin-top: 20px;">
        <div class="activity-data">
            <div class="data no">
                <span class="data-title">No</span>
                <span class="data-list">1</span>
                <span class="data-list">2</span>
                <span class="data-list">3</span>
                <span class="data-list">4</span>
            </div>
            <div class="data kegiatan">
                <span class="data-title">Nama Kegiatan</span>
                <span class="data-list">Rapat Koordinasi Bulanan</span>
                <span class="data-list">Sosialisasi Layanan Hukum</span>
                <span class="data-list">Workshop Digitalisasi</span>
                <span class="data-list">Rapat Sosialisasi</span>
            </div>
            <div class="data tanggal">
                <span class="data-title">Tanggal</span>
                <span class="data-list">2025-09-30</span>
                <span class="data-list">2025-10-05</span>
                <span class="data-list">2025-10-10</span>
                <span class="data-list">2025-10-11</span>
            </div>
            <div class="data waktu">
                <span class="data-title">Waktu</span>
                <span class="data-list">09.00-10.00</span>
                <span class="data-list">13.00-14.00</span>
                <span class="data-list">10.00-11.00</span>
                <span class="data-list">18.00-19.00</span>
            </div>
            <div class="data keterangan">
                <span class="data-title">Keterangan</span>
                <span class="data-list">Dihadiri oleh Kakanwil dan Kabag Humas. Membahas rencana kerja bulan depan.</span>
                <span class="data-list">Sosialisasi kepada ASN baru tentang layanan hukum dan inovasi digital.</span>
                <span class="data-list">Pelatihan penggunaan aplikasi digital rekap konten.</span>
                <span class="data-list">Koordinasi kegiatan publikasi internal antar subbagian.</span>
            </div>
            <div class="data status">
                <span class="data-title">Status</span>
                <span class="data-list status-selesai">Selesai</span>
                <span class="data-list status-ditunda">Ditunda</span>
                <span class="data-list status-dibatalkan">Dibatalkan</span>
                <span class="data-list status-belum">Belum Dimulai</span>
            </div>
            <div class="data actions">
                <span class="data-title">Aksi</span>
                <span class="data-list">
                    <button class="btn-action-aksi view"><i class="uil uil-eye"></i></button>
                    <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-kegiatan'"><i class="uil uil-edit"></i></button>
                    <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
                </span>
                <span class="data-list">
                    <button class="btn-action-aksi view"><i class="uil uil-eye"></i></button>
                    <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                    <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
                </span>
                <span class="data-list">
                    <button class="btn-action-aksi view"><i class="uil uil-eye"></i></button>
                    <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                    <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
                </span>
                <span class="data-list">
                    <button class="btn-action-aksi view"><i class="uil uil-eye"></i></button>
                    <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                    <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
                </span>
            </div>
        </div>
    </div>

    <!-- Modal Keterangan -->
    <div id="keteranganModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detail Keterangan Kegiatan</h3>
            <p id="modalText"></p>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <button class="active">1</button>
        <button>2</button>
        <button>3</button>
        <button>Next</button>
    </div>
</div>

<script>
// Daftar keterangan kegiatan
const keteranganList = [
  "Kegiatan ini dihadiri oleh Kakanwil, Kabag Humas, dan seluruh kasubbag. Membahas rencana kerja bulan depan, evaluasi program, serta strategi peningkatan publikasi.",
  "Sosialisasi kepada ASN baru tentang layanan hukum dan inovasi digital.",
  "Pelatihan penggunaan aplikasi digital rekap konten.",
  "Koordinasi kegiatan publikasi internal antar subbagian."
];

// Fungsi tampilkan modal
function showKeterangan(index) {
  document.getElementById("modalText").textContent = keteranganList[index];
  document.getElementById("keteranganModal").style.display = "block";
}

// Fungsi tutup modal
function closeModal() {
  document.getElementById("keteranganModal").style.display = "none";
}

// Pasang event listener ke semua tombol view
document.querySelectorAll(".btn-action-aksi.view").forEach((btn, i) => {
  btn.addEventListener("click", () => showKeterangan(i));
});

// Tutup modal dengan tombol close
document.querySelector("#keteranganModal .close").addEventListener("click", closeModal);

// Tutup modal jika klik di luar konten
window.addEventListener("click", function(event) {
  const modal = document.getElementById("keteranganModal");
  if (event.target === modal) closeModal();
});
</script>
