<?php
// app/views/pages/tracking-layanan-pengaduan.php
// Halaman tracking pengaduan untuk masyarakat (tanpa login)
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

// Fungsi untuk menyensor data sensitif
function maskSensitiveData($data, $showFirst = 3, $showLast = 3) {
    if (empty($data)) {
        return '';
    }
    
    $data = trim($data);
    $length = strlen($data);
    
    // Jika data terlalu pendek, sembunyikan semua kecuali 2 karakter pertama
    if ($length <= ($showFirst + $showLast)) {
        $showFirst = min(2, $length);
        $showLast = 0;
    }
    
    $first = substr($data, 0, $showFirst);
    $last = $showLast > 0 ? substr($data, -$showLast) : '';
    $masked = str_repeat('*', max(4, $length - $showFirst - $showLast));
    
    return $first . $masked . $last;
}

// Ambil data pengaduan jika nomor register ada di URL
$pengaduan = null;
if (isset($_GET['no_register']) && isset($_GET['found']) && $_GET['found'] == '1') {
    require_once __DIR__ . '/../../models/LayananPengaduanModel.php';
    $model = new LayananPengaduanModel();
    $pengaduan = $model->getLayananPengaduanByNoRegister($_GET['no_register']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Pengaduan - SiCakap</title>
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
        .tracking-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            margin-top: 20px;
            margin-bottom: 40px;
        }
        .tracking-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .tracking-header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .tracking-header p {
            color: #666;
        }
        .tracking-form {
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn-track {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn-track:hover {
            transform: translateY(-2px);
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .btn-back:hover {
            background: #e0e0e0;
        }
        .result-container {
            margin-top: 30px;
            padding: 25px;
            background: #f9f9f9;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .result-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        .result-header h2 {
            color: #333;
            margin: 0;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        .status-selesai {
            background: #4caf50;
            color: white;
        }
        .status-proses {
            background: #ff9800;
            color: white;
        }
        .status-belum {
            background: #9e9e9e;
            color: white;
        }
        .detail-section {
            margin-bottom: 20px;
        }
        .detail-section h3 {
            color: #667eea;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        .detail-item {
            margin-bottom: 12px;
            line-height: 1.6;
        }
        .detail-item strong {
            color: #333;
            display: inline-block;
            min-width: 180px;
        }
        .detail-item span {
            color: #666;
        }
        .file-download {
            margin-top: 15px;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .file-download a {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .file-download a:hover {
            background: #5568d3;
        }
        
        /* Responsive untuk smartphone */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .tracking-wrapper {
                padding: 20px;
                margin-top: 10px;
                margin-bottom: 20px;
            }
            .tracking-header h1 {
                font-size: 1.5em;
            }
            .tracking-header p {
                font-size: 0.9em;
            }
            .form-group input {
                padding: 10px;
                font-size: 16px; /* Mencegah zoom di iOS */
            }
            .btn-track {
                padding: 12px;
                font-size: 15px;
            }
            .result-container {
                padding: 15px;
            }
            .result-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .result-header h2 {
                font-size: 1.2em;
            }
            .detail-item strong {
                min-width: 140px;
                display: block;
                margin-bottom: 5px;
            }
            .detail-item span {
                display: block;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 5px;
            }
            .tracking-wrapper {
                padding: 15px;
                border-radius: 10px;
            }
            .tracking-header h1 {
                font-size: 1.3em;
            }
            .tracking-header p {
                font-size: 0.85em;
            }
            .detail-section h3 {
                font-size: 1em;
            }
            .detail-item strong {
                min-width: 100%;
                font-size: 0.9em;
            }
            .status-badge {
                padding: 6px 12px;
                font-size: 12px;
            }
            .file-download {
                padding: 10px;
            }
            .file-download a {
                padding: 8px 16px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="tracking-wrapper">
        <div class="tracking-header">
            <h1><i class="fas fa-search"></i> Tracking Penyelesaian Pengaduan</h1>
            <p>Masukkan nomor register pengaduan Anda untuk melihat status dan detail pengaduan</p>
        </div>

        <form class="tracking-form" action="index.php?page=tracking-result" method="POST">
            <div class="form-group">
                <label for="no_register">Nomor Register Pengaduan <span style="color: red;">*</span></label>
                <input type="text" id="no_register" name="no_register" 
                       placeholder="Masukkan nomor register (contoh: P123456)" 
                       value="<?= isset($_GET['no_register']) ? htmlspecialchars($_GET['no_register']) : '' ?>"
                       required>
            </div>
            <button type="submit" class="btn-track">
                <i class="fas fa-search"></i> Cari Pengaduan
            </button>
        </form>

        <a href="landing.php#layanan-pengaduan" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama
        </a>

        <?php if ($pengaduan): ?>
        <div class="result-container">
            <div class="result-header">
                <h2>Detail Pengaduan</h2>
                <?php
                $statusText = $pengaduan['tindak_lanjut'] === 'selesai' ? 'Selesai' : 
                             ($pengaduan['tindak_lanjut'] === 'proses' ? 'Proses' : 'Belum Diproses');
                $statusClass = $pengaduan['tindak_lanjut'] === 'selesai' ? 'status-selesai' : 
                              ($pengaduan['tindak_lanjut'] === 'proses' ? 'status-proses' : 'status-belum');
                ?>
                <span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span>
            </div>

            <div class="detail-section">
                <h3>Informasi Pengaduan</h3>
                <div class="detail-item">
                    <strong>No. Register:</strong>
                    <span><?= htmlspecialchars($pengaduan['no_register_pengaduan']) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Tanggal Pengaduan:</strong>
                    <span><?= date('d/m/Y', strtotime($pengaduan['tanggal_pengaduan'])) ?></span>
                </div>
            </div>

            <div class="detail-section">
                <h3>Data Pelapor</h3>
                <div class="detail-item">
                    <strong>Nama:</strong>
                    <span><?= htmlspecialchars($pengaduan['nama']) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Alamat:</strong>
                    <span><?= htmlspecialchars($pengaduan['alamat']) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Jenis Tanda Pengenal:</strong>
                    <span>
                        <?= htmlspecialchars($pengaduan['jenis_tanda_pengenal']) ?>
                        <?php if ($pengaduan['jenis_tanda_pengenal'] === 'LAINNYA' && !empty($pengaduan['jenis_tanda_pengenal_lainnya'])): ?>
                            - <?= htmlspecialchars($pengaduan['jenis_tanda_pengenal_lainnya']) ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-item">
                    <strong>No. Tanda Pengenal:</strong>
                    <span><?= maskSensitiveData($pengaduan['no_tanda_pengenal'], 3, 3) ?></span>
                </div>
                <?php if (!empty($pengaduan['no_telp'])): ?>
                <div class="detail-item">
                    <strong>No. Telepon:</strong>
                    <span><?= maskSensitiveData($pengaduan['no_telp'], 3, 3) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="detail-section">
                <h3>Data Laporan</h3>
                <div class="detail-item">
                    <strong>Judul Laporan:</strong>
                    <span><?= htmlspecialchars($pengaduan['judul_laporan']) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Isi Laporan:</strong>
                    <span style="white-space: pre-wrap;"><?= htmlspecialchars($pengaduan['isi_laporan']) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Tanggal Kejadian:</strong>
                    <span><?= date('d/m/Y', strtotime($pengaduan['tanggal_kejadian'])) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Lokasi Kejadian:</strong>
                    <span><?= htmlspecialchars($pengaduan['lokasi_kejadian']) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Kategori Laporan:</strong>
                    <span><?= htmlspecialchars($pengaduan['kategori_laporan']) ?></span>
                </div>
                <div class="detail-item">
                    <strong>Jenis Aduan:</strong>
                    <span>
                        <?= htmlspecialchars($pengaduan['jenis_aduan']) ?>
                        <?php if ($pengaduan['jenis_aduan'] === 'Lainnya' && !empty($pengaduan['jenis_aduan_lainnya'])): ?>
                            - <?= htmlspecialchars($pengaduan['jenis_aduan_lainnya']) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($pengaduan['keterangan'])): ?>
            <div class="detail-section">
                <h3>Keterangan / Tindak Lanjut</h3>
                <?php
                $keterangan = $pengaduan['keterangan'];
                $keteranganText = '';
                $filePath = '';
                
                // Parse keterangan: format bisa "TEXT\nFILE: path" atau hanya teks atau hanya file
                if (preg_match('/FILE:\s*/i', $keterangan, $matches, PREG_OFFSET_CAPTURE)) {
                    $fileIndex = $matches[0][1];
                    $keteranganText = trim(substr($keterangan, 0, $fileIndex));
                    $filePath = trim(substr($keterangan, $fileIndex + strlen($matches[0][0])));
                } else if (strpos($keterangan, 'storage/uploads/') !== false || preg_match('/\.(pdf|jpg|jpeg|png|doc|docx)$/i', $keterangan)) {
                    $filePath = trim($keterangan);
                } else {
                    $keteranganText = trim($keterangan);
                }
                
                // Parse filePath untuk dapatkan path dan original name
                $filePathOnly = $filePath;
                $originalFileName = '';
                if ($filePath && strpos($filePath, '|') !== false) {
                    $fileParts = explode('|', $filePath, 2);
                    $filePathOnly = $fileParts[0];
                    $originalFileName = $fileParts[1] ?? basename($filePathOnly);
                } else if ($filePath) {
                    $originalFileName = basename($filePathOnly);
                }
                
                // Tampilkan teks jika ada
                if ($keteranganText): ?>
                    <div style="margin: 10px 0; padding: 15px; background: #f0f0f0; border-radius: 5px; white-space: pre-wrap;">
                        <?= nl2br(htmlspecialchars($keteranganText)) ?>
                    </div>
                <?php endif;
                
                // Tampilkan file jika ada
                if ($filePath): ?>
                    <div class="file-download">
                        <p style="margin: 0 0 10px 0;">
                            <i class="fas fa-file" style="color: #667eea; margin-right: 8px;"></i>
                            <strong><?= htmlspecialchars($originalFileName ?: basename($filePathOnly)) ?></strong>
                        </p>
                        <a href="index.php?page=download-keterangan&file=<?= urlencode($filePath) ?>" target="_blank">
                            <i class="fas fa-download"></i> Download File
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($_GET['status']) && $_GET['status'] == 'error'): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: 'error',
            title: 'Pengaduan Tidak Ditemukan',
            text: '<?= isset($_GET['message']) ? htmlspecialchars(urldecode($_GET['message'])) : 'Nomor register tidak ditemukan. Pastikan nomor register yang Anda masukkan benar.' ?>',
            showConfirmButton: true
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>

