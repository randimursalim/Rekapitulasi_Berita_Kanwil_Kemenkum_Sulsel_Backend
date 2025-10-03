<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Pengguna - KEMENKUM SULSEL</title>
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
        <li><a href="pengguna.html" class="active"><i class="uil uil-users-alt"></i><span class="link-name">Pengguna</span></a></li>
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

  <!-- Dashboard -->
  <section class="dashboard">
    <div class="top">
      <i class="uil uil-bars sidebar-toggle"></i>
      <a href="edit-profil.html">
        <img src="images/user.jpg" alt="Profile" class="profile-link">
      </a>
    </div>

    <div class="dash-content">
      <div class="overview">
        <div class="title">
          <i class="uil uil-edit"></i>
          <span class="text">Edit Pengguna</span>
        </div>

        <!-- Form Edit Pengguna -->
        <div class="form-container">
          <form action="#" method="POST" class="input-berita-form" autocomplete="off" enctype="multipart/form-data">
            
            <!-- Upload Foto -->
            <div class="upload-container">
              <img id="previewImage" src="images/user.jpg" alt="Preview Foto">
              <br>
              <label for="foto"><i class="uil uil-image-plus"></i> Ubah Foto</label>
              <input type="file" id="foto" name="foto" accept="image/*">
            </div>

            <div class="form-group">
              <label for="nama">Nama Lengkap</label>
              <input type="text" id="nama" name="nama" value="Admin" required>
            </div>

            <div class="form-group">
              <label for="username">Username</label>
              <input type="text" id="username" name="username" value="admin01" required>
            </div>

            <div class="form-group">
              <label for="password">Password Baru</label>
              <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak diganti">
            </div>

            <div class="form-group">
              <label for="confirm-password">Konfirmasi Password</label>
              <input type="password" id="confirm-password" name="confirm-password" placeholder="Ulangi password baru">
            </div>

            <div class="form-group">
              <label for="role">Role</label>
              <select id="role" name="role" required>
                <option value="Admin" selected>Admin</option>
                <option value="Operator">User</option>
              </select>
            </div>

            <div style="text-align:center; margin-top:20px;">
              <button type="submit" class="btn-simpan">
                <i class="uil uil-save"></i> Update
              </button>
              <button type="button" class="btn-batal" onclick="window.location.href='pengguna.html'">
                <i class="uil uil-times"></i> Batal
              </button>
            </div>
          </form>
        </div>
        <!-- End Form -->
      </div>
    </div>
  </section>

  <script>
    // Preview gambar sebelum upload
    const fotoInput = document.getElementById('foto');
    const preview = document.getElementById('previewImage');

    fotoInput.addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.setAttribute('src', e.target.result);
        }
        reader.readAsDataURL(file);
      }
    });
  </script>
  <script src="script.js"></script>
</body>
</html>
