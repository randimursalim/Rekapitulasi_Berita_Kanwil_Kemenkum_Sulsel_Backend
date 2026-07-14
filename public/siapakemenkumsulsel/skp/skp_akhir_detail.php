<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Database connection
require_once '../config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get ID_SKP from URL
$id_skp = $_GET['id_skp'] ?? '';

if (empty($id_skp)) {
    die("ID SKP tidak valid");
}

// Fetch SKP Akhir data
$sql = "SELECT * FROM skp_akhir_pegawai WHERE ID_SKP = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_skp);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Data SKP Akhir tidak ditemukan");
}

$skp_data = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail SKP Akhir - <?= htmlspecialchars($skp_data['NAMA']) ?></title>
    <link rel="icon" type="image/png" href="../images/SIAPA.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .header {
            background: #0D2052;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .info-card {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #0D2052;
        }

        .info-card h3 {
            color: #0D2052;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #212529;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-draft {
            background: #fff3cd;
            color: #856404;
        }

        .status-proses-evaluasi {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-selesai-evaluasi {
            background: #d4edda;
            color: #155724;
        }

        .back-btn {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Detail SKP Akhir</h1>
            <p>Tahun <?= htmlspecialchars($skp_data['TAHUN']) ?> - <?= htmlspecialchars($skp_data['NAMA']) ?></p>
        </div>

        <div class="content">
            <div class="info-grid">
                <div class="info-card">
                    <h3>👤 Informasi Pegawai</h3>
                    <div class="info-row">
                        <span class="info-label">Nama Pegawai:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['NAMA']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">NIP:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['NIP']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Atasan Langsung:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['NAMA_ATASAN_LANGSUNG']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">NIP Atasan:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['NIP_ATASAN_LANGSUNG']) ?></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>📈 Data Kinerja</h3>
                    <div class="info-row">
                        <span class="info-label">Jenis Kinerja:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['JENIS_KINERJA']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Target (Akumulasi):</span>
                        <span class="info-value"><strong><?= number_format($skp_data['TARGET']) ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Realisasi (Akumulasi):</span>
                        <span class="info-value"><strong><?= number_format($skp_data['REALISASI_BERDASARKAN_BUKTI_DUKUNG']) ?></strong></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $skp_data['PREDIKAT_KINERJA_PEGAWAI'] ?? 'draft')) ?>">
                                <?= htmlspecialchars($skp_data['PREDIKAT_KINERJA_PEGAWAI'] ?? 'DRAFT') ?>
                            </span>
                        </span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>📋 Detail SKP</h3>
                    <div class="info-row">
                        <span class="info-label">Aspek:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['ASPEK']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Indikator Kinerja:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['INDIKATOR_KINERJA_INDIVIDU']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Satuan Aspek:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['SATUAN']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tahun:</span>
                        <span class="info-value"><strong><?= htmlspecialchars($skp_data['TAHUN']) ?></strong></span>
                    </div>
                </div>

                <div class="info-card">
                    <h3>📅 Tanggal & Evaluasi</h3>
                    <div class="info-row">
                        <span class="info-label">Tanggal Input:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['TANGGAL_INPUT_SKP']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Evaluasi:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['TANGGAL_EVALUASI_SKP'] ?? 'Belum dievaluasi') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Rating Perilaku:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['RATING_PERILAKU_KERJA'] ?? 'Belum dievaluasi') ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Rating Hasil:</span>
                        <span class="info-value"><?= htmlspecialchars($skp_data['RATING_HASIL_KERJA'] ?? 'Belum dievaluasi') ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($skp_data['RHK_PIMPINAN_INTERV'])): ?>
            <div class="info-card" style="margin-top: 20px;">
                <h3>💬 RHK Pimpinan Intervensi</h3>
                <p style="margin-top: 10px; line-height: 1.6;"><?= htmlspecialchars($skp_data['RHK_PIMPINAN_INTERV']) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($skp_data['RENCANA_HASIL_KERJA'])): ?>
            <div class="info-card" style="margin-top: 20px;">
                <h3>🎯 Rencana Hasil Kerja</h3>
                <p style="margin-top: 10px; line-height: 1.6;"><?= htmlspecialchars($skp_data['RENCANA_HASIL_KERJA']) ?></p>
            </div>
            <?php endif; ?>

            <a href="javascript:history.back()" class="back-btn">← Kembali</a>
        </div>
    </div>
</body>
</html>
