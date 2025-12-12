<?php
// app/views/pages/tambah-peminjaman-ruangan-masyarakat.php
// Form untuk masyarakat (tanpa login)
// Auto-detect BASE_URL jika belum tersedia
if (!isset($BASE)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    
    $isLocalhost = (
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($httpHost, 'localhost') !== false ||
        strpos($requestUri, '/rekap-konten/public') !== false ||
        strpos($scriptName, '/rekap-konten/public') !== false
    );
    
    $BASE = $isLocalhost ? 
        (defined('BASE_URL') ? BASE_URL : '/rekap-konten/public') : 
        '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Ruangan - SiCakap</title>
    <link rel="icon" type="image/png" href="<?= $BASE ?>/Images/aset_landing.png">
    <link rel="stylesheet" href="<?= $BASE ?>/css/style.css">
    <link rel="stylesheet" href="<?= $BASE ?>/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Poppins', sans-serif;
        }
        .form-wrapper-public {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: 20px;
            margin-bottom: 40px;
        }
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-header h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .form-header p {
            color: #666;
            font-size: 1.1em;
        }
        .back-to-landing {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: all 0.3s;
        }
        .back-to-landing:hover {
            background: rgba(255,255,255,0.3);
        }
        .back-to-landing i {
            margin-right: 8px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 0.9em;
        }
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 40px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-submit i {
            margin-right: 8px;
        }
        @media (max-width: 768px) {
            .form-wrapper-public {
                padding: 20px;
            }
            .form-header h1 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div style="text-align: center; padding: 20px 0;">
        <a href="landing.php" class="back-to-landing">
            <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
        </a>
    </div>
    
    <div class="form-wrapper-public">
        <div class="form-header">
            <h1><i class="fas fa-door-open"></i> Pinjam Ruangan</h1>
            <p>Formulir peminjaman ruangan untuk kegiatan di Kantor Wilayah Kemenkum Sulsel</p>
        </div>

        <form id="formPeminjamanRuangan" action="index.php?page=store-peminjaman-ruangan-masyarakat" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="namaPeminjam">Nama Peminjam <span style="color: red;">*</span></label>
                <input type="text" id="namaPeminjam" name="namaPeminjam" placeholder="Masukkan nama peminjam" required>
            </div>

            <div class="form-group">
                <label for="namaRuangan">Nama Ruangan <span style="color: red;">*</span></label>
                <select id="namaRuangan" name="namaRuangan" required>
                    <option value="">-- Pilih Ruangan --</option>
                    <option value="Ruang Rapat Baharuddin Lopa (Kakanwil)">Ruang Rapat Baharuddin Lopa (Kakanwil)</option>
                    <option value="Ruang Rapat Andi Mattalatta (Lantai 1)">Ruang Rapat Andi Mattalatta (Lantai 1)</option>
                    <option value="Ruang Rapat Hamid Awaluddin (Lantai 2)">Ruang Rapat Hamid Awaluddin (Lantai 2)</option>
                    <option value="Ruang Rapat Bhinneka Tunggal Ika (Lantai 3)">Ruang Rapat Bhinneka Tunggal Ika (Lantai 3)</option>
                    <option value="Aula Pancasila (Lantai 3)">Aula Pancasila (Lantai 3)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="kegiatan">Kegiatan <span style="color: red;">*</span></label>
                <input type="text" id="kegiatan" name="kegiatan" placeholder="Masukkan nama kegiatan" required>
            </div>

            <div class="form-group">
                <label for="tanggalKegiatan">Tanggal Kegiatan <span style="color: red;">*</span></label>
                <input type="date" id="tanggalKegiatan" name="tanggalKegiatan" required>
            </div>

            <div class="form-group">
                <label for="waktuKegiatan">Waktu Kegiatan <span style="color: red;">*</span></label>
                <input type="time" id="waktuKegiatan" name="waktuKegiatan" required>
            </div>

            <div class="form-group">
                <label for="durasiKegiatan">Durasi Kegiatan (jam) <span style="color: red;">*</span></label>
                <input type="number" id="durasiKegiatan" name="durasiKegiatan" 
                       min="1" max="8" value="2" step="0.5" 
                       placeholder="Durasi dalam jam (default: 2 jam)" required>
                <small>Durasi minimal 1 jam, maksimal 8 jam</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Kirim Permohonan
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $BASE ?>/js/tambah-peminjaman-ruangan-masyarakat.js"></script>

    <?php if (isset($_GET['status'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if ($_GET['status'] == 'success'): ?>
        Swal.fire({
          icon: 'success',
          title: 'Peminjaman Berhasil!',
          text: 'peminjaman ruangan Anda telah berhasil dikirim.',
          showConfirmButton: true,
          confirmButtonText: 'Kembali ke Halaman Utama'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = 'landing.php';
          }
        });
      <?php elseif ($_GET['status'] == 'error'): ?>
        Swal.fire({
          icon: 'error',
          title: 'Gagal Mengirim Permohonan!',
          text: '<?= isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Silakan coba lagi atau periksa data yang diinput.' ?>',
          showConfirmButton: true
        }).then(() => {
          window.history.replaceState({}, document.title, window.location.pathname + '?page=tambah-peminjaman-ruangan-masyarakat');
        });
      <?php endif; ?>
    });
    </script>
    <?php endif; ?>
</body>
</html>

