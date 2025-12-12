<?php
// app/views/pages/tambah-layanan-pengaduan-masyarakat.php
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
    <title>Ajukan Pengaduan - SiCakap</title>
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
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus,
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
        .form-section-title {
            margin: 30px 0 20px 0;
            color: #667eea;
            font-size: 1.3em;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .form-actions {
            text-align: center;
            margin-top: 30px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            font-size: 1.1em;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
        }
        .btn-submit i {
            margin-right: 8px;
        }
        .required {
            color: red;
        }
        
        /* Responsive untuk smartphone */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .form-wrapper-public {
                padding: 20px;
                margin-top: 10px;
                margin-bottom: 20px;
            }
            .form-header h1 {
                font-size: 1.5em;
            }
            .form-header p {
                font-size: 1em;
            }
            .form-section-title {
                font-size: 1.1em;
                margin: 20px 0 15px 0;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group input,
            .form-group textarea,
            .form-group select {
                padding: 10px;
                font-size: 16px; /* Mencegah zoom di iOS */
            }
            .btn-submit {
                padding: 12px 30px;
                font-size: 1em;
                width: 100%;
            }
            .back-to-landing {
                padding: 8px 16px;
                font-size: 0.9em;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            .form-wrapper-public {
                padding: 15px;
                border-radius: 10px;
            }
            .form-header h1 {
                font-size: 1.3em;
            }
            .form-header p {
                font-size: 0.9em;
            }
            .form-section-title {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div style="max-width: 900px; margin: 0 auto;">
        <a href="landing.php#layanan-pengaduan" class="back-to-landing">
            <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
        </a>
        
        <div class="form-wrapper-public">
            <div class="form-header">
                <h1><i class="fas fa-gavel"></i> Form Pengaduan Masyarakat</h1>
                <p>Silakan isi form di bawah ini untuk mengajukan pengaduan Anda</p>
            </div>

            <form id="formLayananPengaduanMasyarakat" action="index.php?page=store-layanan-pengaduan-masyarakat" method="POST" autocomplete="off" enctype="multipart/form-data">
                <h3 class="form-section-title">Data Pelapor</h3>
                
                <div class="form-group">
                    <label for="nama">Nama <span class="required">*</span></label>
                    <input type="text" id="nama" name="nama" placeholder="Masukkan nama pelapor" required>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat <span class="required">*</span></label>
                    <textarea id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat pelapor" required></textarea>
                </div>

                <div class="form-group">
                    <label for="jenisTandaPengenal">Jenis Tanda Pengenal <span class="required">*</span></label>
                    <select id="jenisTandaPengenal" name="jenisTandaPengenal" required>
                        <option value="">-- Pilih Jenis Tanda Pengenal --</option>
                        <option value="KTP">KTP</option>
                        <option value="SIM">SIM</option>
                        <option value="PASPOR">PASPOR</option>
                        <option value="LAINNYA">LAINNYA</option>
                    </select>
                </div>

                <div class="form-group" id="jenisTandaPengenalLainnyaGroup" style="display: none;">
                    <label for="jenisTandaPengenalLainnya">Jenis Tanda Pengenal Lainnya <span class="required">*</span></label>
                    <input type="text" id="jenisTandaPengenalLainnya" name="jenisTandaPengenalLainnya" placeholder="Masukkan jenis tanda pengenal lainnya">
                </div>

                <div class="form-group">
                    <label for="noTandaPengenal">No. Tanda Pengenal <span class="required">*</span></label>
                    <input type="text" id="noTandaPengenal" name="noTandaPengenal" placeholder="Masukkan nomor tanda pengenal" required>
                </div>

                <div class="form-group">
                    <label for="noTelp">No. Telepon</label>
                    <input type="text" id="noTelp" name="noTelp" placeholder="Masukkan nomor telepon (opsional)">
                </div>

                <h3 class="form-section-title">Data Laporan</h3>

                <div class="form-group">
                    <label for="judulLaporan">Judul Laporan <span class="required">*</span></label>
                    <input type="text" id="judulLaporan" name="judulLaporan" placeholder="Masukkan judul laporan" required>
                </div>

                <div class="form-group">
                    <label for="isiLaporan">Isi Laporan <span class="required">*</span></label>
                    <textarea id="isiLaporan" name="isiLaporan" rows="5" placeholder="Masukkan isi laporan secara detail" required></textarea>
                </div>

                <div class="form-group">
                    <label for="tanggalKejadian">Tanggal Kejadian <span class="required">*</span></label>
                    <input type="date" id="tanggalKejadian" name="tanggalKejadian" required>
                </div>

                <div class="form-group">
                    <label for="lokasiKejadian">Lokasi Kejadian <span class="required">*</span></label>
                    <input type="text" id="lokasiKejadian" name="lokasiKejadian" placeholder="Masukkan lokasi kejadian" required>
                </div>

                <div class="form-group">
                    <label for="kategoriLaporan">Kategori Laporan <span class="required">*</span></label>
                    <select id="kategoriLaporan" name="kategoriLaporan" required>
                        <option value="">-- Pilih Kategori Laporan --</option>
                        <option value="AHU">AHU</option>
                        <option value="KI">KI</option>
                        <option value="Umum">Umum</option>
                        <option value="Pelayanan Hukum (Peraturan Perundang-undangan dan Pembinaan Hukum / P3H)">Pelayanan Hukum (Peraturan Perundang-undangan dan Pembinaan Hukum / P3H)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="jenisAduan">Jenis Aduan <span class="required">*</span></label>
                    <select id="jenisAduan" name="jenisAduan" required>
                        <option value="">-- Pilih Jenis Aduan --</option>
                        <option value="Suap">Suap</option>
                        <option value="Gratifikasi">Gratifikasi</option>
                        <option value="Pungli">Pungli</option>
                        <option value="Korupsi">Korupsi</option>
                        <option value="Mal Administrasi">Mal Administrasi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group" id="jenisAduanLainnyaGroup" style="display: none;">
                    <label for="jenisAduanLainnya">Jenis Aduan Lainnya <span class="required">*</span></label>
                    <input type="text" id="jenisAduanLainnya" name="jenisAduanLainnya" placeholder="Masukkan jenis aduan lainnya">
                </div>

                <!-- Tombol Aksi -->
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Kirim Pengaduan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= $BASE ?>/js/tambah-layanan-pengaduan-masyarakat.js"></script>

    <?php if (isset($_GET['status'])): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
      <?php if ($_GET['status'] == 'success'): ?>
        <?php 
        $noRegister = isset($_GET['no_register']) ? htmlspecialchars($_GET['no_register'], ENT_QUOTES, 'UTF-8') : '';
        $htmlContent = '';
        $footerContent = '';
        
        if ($noRegister) {
            $htmlContent = '<strong>Nomor Register Anda:</strong><br><h2 style="color: #667eea; margin: 10px 0;">' . $noRegister . '</h2><p style="margin-top: 15px;">Simpan nomor register ini untuk tracking pengaduan Anda.</p>';
            $footerContent = '<a href="index.php?page=tracking-layanan-pengaduan" style="color: #667eea; text-decoration: underline;">Tracking Pengaduan</a>';
        } else {
            $htmlContent = 'Terima kasih. Pengaduan Anda telah diterima dan akan segera diproses oleh tim kami.';
        }
        ?>
        Swal.fire({
          icon: 'success',
          title: 'Pengaduan Berhasil Dikirim!',
          html: <?= json_encode($htmlContent, JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
          showConfirmButton: true,
          confirmButtonText: 'Kembali ke Halaman Utama',
          footer: <?= json_encode($footerContent, JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        }).then(() => {
          window.location.href = 'landing.php#layanan-pengaduan';
        });
      <?php elseif ($_GET['status'] == 'error'): ?>
        Swal.fire({
          icon: 'error',
          title: 'Gagal Mengirim Pengaduan!',
          text: <?= json_encode(isset($_GET['message']) ? urldecode($_GET['message']) : 'Silakan coba lagi atau periksa data yang diinput.', JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
          showConfirmButton: true
        });
      <?php endif; ?>
    });
    </script>
    <?php endif; ?>
</body>
</html>

