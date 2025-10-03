<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Edit Konten - KEMENKUM SULSEL</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
</head>
<body>
  <!-- Sidebar -->
  <nav>
    <div class="logo-name">
      <div class="logo-image">
        <img src="images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum">
      </div>
      <span class="logo_name">KEMENKUM SULSEL</span>
    </div>

    <div class="menu-items">
      <ul class="nav-links">
        <li><a href="index.html"><i class="uil uil-estate"></i><span class="link-name">Dashboard</span></a></li>
        <li><a href="input-konten.html"><i class="uil uil-file-plus"></i><span class="link-name">Input Konten</span></a></li>
        <li><a href="rekap-konten.html"><i class="uil uil-database"></i><span class="link-name">Rekap Konten</span></a></li>
        <li><a href="arsip.html"><i class="uil uil-archive"></i><span class="link-name">Arsip</span></a></li>
        <li><a href="jadwal-kegiatan.html"><i class="uil uil-schedule"></i><span class="link-name">Jadwal Kegiatan</span></a></li>
        <li><a href="pengguna.html"><i class="uil uil-users-alt"></i><span class="link-name">Pengguna</span></a></li>
      </ul>

      <ul class="logout-mode">
        <li><a href="#"><i class="uil uil-signout"></i><span class="link-name">Logout</span></a></li>
        <li class="mode">
          <a href="#"><i class="uil uil-moon"></i><span class="link-name">Dark Mode</span></a>
          <div class="mode-toggle"><span class="switch"></span></div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Main -->
  <section class="dashboard">
    <div class="top">
      <i class="uil uil-bars sidebar-toggle"></i>
      <a href="edit-profil.html"><img src="images/user.jpg" alt="Profile" class="profile-link"></a>
    </div>

    <div class="dash-content">
      <div class="overview">
        <div class="title">
          <i class="uil uil-edit"></i>
          <span class="text">Edit Konten</span>
        </div>

        <div class="form-container">
          <!-- NOTE: enctype kept only if file upload is allowed by edit flow -->
          <form id="editKontenForm" action="#" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">

            <!-- Jenis Konten -->
            <div class="form-group">
              <label for="jenis">Jenis Konten</label>
              <select id="jenis" name="jenis" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="berita" selected>Berita</option>
                <option value="instagram">Instagram</option>
                <option value="youtube">YouTube</option>
                <option value="tiktok">TikTok</option>
                <option value="twitter">Twitter (X)</option>
                <option value="facebook">Facebook</option>
              </select>
            </div>

            <!-- Judul -->
            <div class="form-group">
              <label for="judul">Judul Konten</label>
              <input type="text" id="judul" name="judul" value="Judul Lama" required>
            </div>

            <!-- Form khusus Berita (sama struktur dgn input-konten) -->
            <div id="form-berita" style="display:none;">
              <div class="form-group">
                <label for="tanggalBerita">Tanggal Berita</label>
                <input type="date" id="tanggalBerita" name="tanggalBerita" value="2025-09-01">
              </div>

              <div class="form-group">
                <label for="linkBerita">Link Berita</label>
                <input type="url" id="linkBerita" name="linkBerita" value="https://contoh.com/berita">
              </div>

              <div class="form-group">
                <label for="sumberBerita">Sumber / Media Berita</label>
                <input type="text" id="sumberBerita" name="sumberBerita" value="Media Lama">
              </div>

              <div class="form-group">
                <label for="jenisBerita">Jenis Berita</label>
                <select id="jenisBerita" name="jenisBerita" >
                  <option value="">-- Pilih Jenis Berita --</option>
                  <option value="media_online" selected>Media Online</option>
                  <option value="surat_kabar">Surat Kabar</option>
                  <option value="website_kanwil">Website Kanwil</option>
                </select>
              </div>

              <div class="form-group">
                <label for="ringkasan">Ringkasan Berita</label>
                <textarea id="ringkasan" name="ringkasan" rows="4">Ringkasan lama berita...</textarea>
              </div>
            </div>

            <!-- Form Media Sosial -->
            <div id="form-medsos" style="display:none;">
              <div class="form-group">
                <label for="tanggalPost">Tanggal Posting</label>
                <input type="date" id="tanggalPost" name="tanggalPost">
              </div>
              <div class="form-group">
                <label for="linkPost">Link Postingan</label>
                <input type="url" id="linkPost" name="linkPost">
              </div>
              <div class="form-group">
                <label for="caption">Deskripsi / Caption</label>
                <textarea id="caption" name="caption" rows="4"></textarea>
              </div>
            </div>

            <!-- Dokumentasi (optional) -->
            <div class="form-group">
              <label for="dokumentasi">Dokumentasi (Opsional)</label>
              <input type="file" id="dokumentasi" name="dokumentasi" accept="image/*">
            </div>

            <!-- Divisi -->
            <div class="form-group">
              <label for="divisi">Divisi / Bagian</label>
              <select id="divisi" name="divisi">
                <option value="">-- Pilih Divisi --</option>
                <option value="ppu">Peraturan Perundang-undangan dan Pembinaan Hukum</option>
                <option value="pelayanan">Pelayanan Hukum</option>
                <option value="umum">Umum</option>
              </select>
            </div>

            <!-- Buttons -->
            <div style="text-align:center; margin-top:20px;" class="form-buttons">
              <button type="submit" class="btn-simpan">
                <i class="uil uil-save"></i> Update
              </button>
              <button type="button" class="btn-batal" onclick="window.location.href='rekap-konten.html'">
                <i class="uil uil-times"></i> Batal
              </button>
            </div>

          </form>
        </div>
      </div>
    </div>
  </section>

  <script>
    // Toggle form sesuai pilihan (konsisten dengan input-konten.html)
    document.addEventListener('DOMContentLoaded', function () {
      const jenisSelect = document.getElementById('jenis');
      const formBerita = document.getElementById('form-berita');
      const formMedsos = document.getElementById('form-medsos');

      function toggleForm() {
        if (!jenisSelect) return;
        if (jenisSelect.value === 'berita') {
          formBerita.style.display = 'block';
          formMedsos.style.display = 'none';
        } else if (jenisSelect.value !== '') {
          formBerita.style.display = 'none';
          formMedsos.style.display = 'block';
        } else {
          formBerita.style.display = 'none';
          formMedsos.style.display = 'none';
        }
      }

      // jalankan saat load & saat berubah
      toggleForm();
      jenisSelect.addEventListener('change', toggleForm);
    });
  </script>
</body>
</html>
