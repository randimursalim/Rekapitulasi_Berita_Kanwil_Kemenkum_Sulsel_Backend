<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Input Konten - KEMENKUM SULSEL</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css" />
</head>
<body>
  <!-- Sidebar -->
  <nav>
    <div class="logo-name">
      <div class="logo-image">
        <img src="images/LOGO KEMENKUM.jpeg" alt="Logo Kemenkum" />
      </div>
      <span class="logo_name">KEMENKUM SULSEL</span>
    </div>

    <div class="menu-items">
      <ul class="nav-links">
        <li>
          <a href="index.html">
            <i class="uil uil-estate"></i>
            <span class="link-name">Dashboard</span>
          </a>
        </li>
        <li>
          <a href="input-konten.html" class="active">
            <i class="uil uil-file-plus"></i>
            <span class="link-name">Input Konten</span>
          </a>
        </li>
        <li>
          <a href="rekap-konten.html">
            <i class="uil uil-database"></i>
            <span class="link-name">Rekap Konten</span>
          </a>
        </li>
        <li>
          <a href="arsip.html">
            <i class="uil uil-archive"></i>
            <span class="link-name">Arsip</span>
          </a>
        </li>
        <li>
          <a href="jadwal-kegiatan.html">
            <i class="uil uil-schedule"></i>
            <span class="link-name">Jadwal Kegiatan</span>
          </a>
        </li>
        <li>
          <a href="pengguna.html">
            <i class="uil uil-users-alt"></i>
            <span class="link-name">Pengguna</span>
          </a>
        </li>
      </ul>

      <ul class="logout-mode">
        <li>
          <a href="#">
            <i class="uil uil-signout"></i>
            <span class="link-name">Logout</span>
          </a>
        </li>
        <li class="mode">
          <a href="#">
            <i class="uil uil-moon"></i>
            <span class="link-name">Dark Mode</span>
          </a>
          <div class="mode-toggle">
            <span class="switch"></span>
          </div>
        </li>
      </ul>
    </div>
  </nav>

  <!-- Dashboard Content -->
  <section class="dashboard">
    <div class="top">
      <i class="uil uil-bars sidebar-toggle"></i>
      <a href="edit-profil.html">
        <img src="images/user.jpg" alt="Profile" class="profile-link" />
      </a>
    </div>

    <div class="dash-content">
      <div class="overview">
        <div class="title">
          <i class="uil uil-file-plus"></i>
          <span class="text">Input Konten</span>
        </div>

        <!-- Form Input Konten -->
        <div class="form-container">
          <form action="#" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">

            <!-- Pilih Jenis Konten -->
            <div class="form-group">
              <label for="jenis">Jenis Konten</label>
              <select id="jenis" name="jenis" required>
                <option value="">-- Pilih Jenis --</option>
                <option value="berita">Berita</option>
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
              <input type="text" id="judul" name="judul" placeholder="Masukkan judul konten" required />
            </div>

            <!-- Form khusus Berita -->
            <div id="form-berita" style="display: none;">
              <div class="form-group">
                <label for="tanggalBerita">Tanggal Berita</label>
                <input type="date" id="tanggalBerita" name="tanggalBerita" />
              </div>

              <div class="form-group">
                <label for="linkBerita">Link Berita</label>
                <input type="url" id="linkBerita" name="linkBerita" placeholder="https://contoh.com/berita" />
              </div>

              <div class="form-group">
                <label for="sumberBerita">Sumber / Media Berita</label>
                <input type="text" id="sumberBerita" name="sumberBerita" placeholder="Masukkan nama media/sumber" />
              </div>

              <!-- Tambahan Jenis Berita -->
              <div class="form-group">
                <label for="jenisBerita">Jenis Berita</label>
                <select id="jenisBerita" name="jenisBerita" required>
                  <option value="">-- Pilih Jenis Berita --</option>
                  <option value="media_online">Media Online</option>
                  <option value="surat_kabar">Surat Kabar</option>
                  <option value="website_kanwil">Website Kanwil</option>
                </select>
              </div>

              <div class="form-group">
                <label for="ringkasan">Ringkasan Berita</label>
                <textarea id="ringkasan" name="ringkasan" rows="4" placeholder="Tuliskan ringkasan berita..."></textarea>
              </div>
            </div>

            <!-- Form khusus Media Sosial -->
            <div id="form-medsos" style="display: none;">
              <div class="form-group">
                <label for="tanggalPost">Tanggal Posting</label>
                <input type="date" id="tanggalPost" name="tanggalPost" />
              </div>

              <div class="form-group">
                <label for="linkPost">Link Postingan</label>
                <input type="url" id="linkPost" name="linkPost" placeholder="https://instagram.com/..." />
              </div>

              <div class="form-group">
                <label for="caption">Deskripsi / Caption</label>
                <textarea id="caption" name="caption" rows="4" placeholder="Tuliskan deskripsi atau caption..."></textarea>
              </div>
            </div>

            <!-- Dokumentasi -->
            <div class="form-group">
              <label for="dokumentasi">Dokumentasi (Opsional)</label>
              <input type="file" id="dokumentasi" name="dokumentasi" accept="image/*" />
            </div>

            <!-- Divisi / Bagian -->
            <div class="form-group">
              <label for="divisi">Divisi / Bagian</label>
              <select id="divisi" name="divisi" required>
                <option value="">-- Pilih Divisi --</option>
                <option value="ppu">Peraturan Perundang-undangan dan Pembinaan Hukum</option>
                <option value="pelayanan">Pelayanan Hukum</option>
                <option value="umum">Umum</option>
              </select>
            </div>

            <!-- Tombol -->
            <div class="form-actions" style="text-align: center; margin-top: 20px;">
              <button type="submit" class="btn-simpan">
                <i class="uil uil-save"></i> Simpan
              </button>
              <button type="button" class="btn-batal" onclick="window.location.href='index.html'">
                <i class="uil uil-times"></i> Batal
              </button>
            </div>
          </form>
        </div>
        <!-- End Form -->
      </div>
    </div>
  </section>

  <script src="script.js"></script>
  <script>
    // Toggle form berdasarkan pilihan jenis konten
    const jenisSelect = document.getElementById("jenis");
    const formBerita = document.getElementById("form-berita");
    const formMedsos = document.getElementById("form-medsos");

    jenisSelect.addEventListener("change", function () {
      if (this.value === "berita") {
        formBerita.style.display = "block";
        formMedsos.style.display = "none";
      } else if (this.value !== "") {
        formBerita.style.display = "none";
        formMedsos.style.display = "block";
      } else {
        formBerita.style.display = "none";
        formMedsos.style.display = "none";
      }
    });
  </script>
</body>
</html>
