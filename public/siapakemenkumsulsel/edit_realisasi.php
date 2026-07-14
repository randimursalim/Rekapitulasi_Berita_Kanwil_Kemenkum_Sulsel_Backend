<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Get id_skp_global from URL
$id_skp_global = $_GET['id_skp_global'] ?? '';

if (empty($id_skp_global)) {
    die('ID SKP Global tidak ditemukan');
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch SKP data
$skp_sql = "SELECT * FROM skp_pegawai WHERE id_skp_global = ? AND NIP = ? AND STATUS = 'SUBMITTED' ORDER BY TANGGAL_INPUT_SKP ASC";
$stmt = $conn->prepare($skp_sql);
$stmt->bind_param('is', $id_skp_global, $_SESSION['nip']);
$stmt->execute();
$result = $stmt->get_result();

$skp_data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $skp_data[] = $row;
    }
}

$stmt->close();

if (empty($skp_data)) {
    die('Data SKP tidak ditemukan atau status bukan SUBMITTED');
}

$first_row = $skp_data[0];

// Fetch Perilaku Kerja data
$perilaku_sql = "SELECT * FROM skp_perilaku_pegawai WHERE id_skp_global = ?";
$perilaku_stmt = $conn->prepare($perilaku_sql);
if ($perilaku_stmt) {
    $perilaku_stmt->bind_param('i', $id_skp_global);
    $perilaku_stmt->execute();
    $perilaku_result = $perilaku_stmt->get_result();

    $perilaku_data = [];
    if ($perilaku_result && $perilaku_result->num_rows > 0) {
        $perilaku_data = $perilaku_result->fetch_assoc();
    }

    $perilaku_stmt->close();
} else {
    $perilaku_data = [];
}

// Separate Kinerja Utama and Kinerja Tambahan using JENIS_KINERJA field
$kinerja_utama = [];
$kinerja_tambahan = [];

foreach ($skp_data as $row) {
    if (isset($row['JENIS_KINERJA'])) {
        if (strtolower(trim($row['JENIS_KINERJA'])) === 'kinerja utama') {
            $kinerja_utama[] = $row;
        } elseif (strtolower(trim($row['JENIS_KINERJA'])) === 'kinerja tambahan') {
            $kinerja_tambahan[] = $row;
        }
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        
        // Update realisasi for kinerja utama (SATUAN is read-only, only update REALISASI)
        if (!empty($_POST['realisasi_utama'])) {
            foreach ($_POST['realisasi_utama'] as $id_skp => $realisasi) {
                $update_sql = "UPDATE skp_pegawai SET REALISASI_BERDASARKAN_BUKTI_DUKUNG = ? WHERE ID_SKP = ? AND id_skp_global = ? AND NIP = ? AND STATUS = 'SUBMITTED'";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('siis', $realisasi, $id_skp, $id_skp_global, $_SESSION['nip']);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
        
        // Update realisasi for kinerja tambahan (SATUAN is read-only, only update REALISASI)
        if (!empty($_POST['realisasi_tambahan'])) {
            foreach ($_POST['realisasi_tambahan'] as $id_skp => $realisasi) {
                $update_sql = "UPDATE skp_pegawai SET REALISASI_BERDASARKAN_BUKTI_DUKUNG = ? WHERE ID_SKP = ? AND id_skp_global = ? AND NIP = ? AND STATUS = 'SUBMITTED'";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('siis', $realisasi, $id_skp, $id_skp_global, $_SESSION['nip']);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
        
        $conn->commit();
        $success_message = "Realisasi berhasil diperbarui!";
        
        // Refresh data
        $skp_sql = "SELECT * FROM skp_pegawai WHERE id_skp_global = ? AND NIP = ? AND STATUS = 'SUBMITTED' ORDER BY TANGGAL_INPUT_SKP ASC";
        $stmt = $conn->prepare($skp_sql);
        $stmt->bind_param('is', $id_skp_global, $_SESSION['nip']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $skp_data = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $skp_data[] = $row;
            }
        }
        
        // Re-separate data
        $kinerja_utama = [];
        $kinerja_tambahan = [];
        foreach ($skp_data as $row) {
            if (isset($row['JENIS_KINERJA'])) {
                if ($row['JENIS_KINERJA'] === 'kinerja utama') {
                    $kinerja_utama[] = $row;
                } elseif ($row['JENIS_KINERJA'] === 'kinerja tambahan') {
                    $kinerja_tambahan[] = $row;
                }
            }
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error: " . $e->getMessage();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Realisasi SKP</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: #0D2052;
            color: white;
            padding: 20px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .info-section {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #212529;
            font-size: 14px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #0D2052;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #0D2052;
        }
        
        .skp-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }
        
        .skp-table th {
            background: #0D2052;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        .skp-table td {
            border: 1px solid #dee2e6;
            padding: 10px 8px;
            font-size: 12px;
            vertical-align: top;
        }
        
        .skp-table tr:nth-child(even) {
            background: #f0f0f0;
        }
        
        .skp-field-content {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 6px;
            padding: 12px;
            font-size: 14px;
            line-height: 1.5;
            min-height: 40px;
        }
        
        .evaluation-textarea {
            width: 100%;
            min-height: 80px;
            border: 1px solid #0D2052;
            border-radius: 6px;
            padding: 10px;
            font-family: 'Bookman Old Style', serif;
            font-size: 14px;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .evaluation-textarea:focus {
            outline: none;
            border-color: #0D2052;
            box-shadow: 0 0 0 2px rgba(0,82,155,0.1);
        }
        
        .readonly-field {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }
        
        .buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: #0D2052;
            color: white;
        }
        
        .btn-primary:hover {
            background: #003d73;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .skp-item-container {
            background: white;
            border: 1px solid #0D2052;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .skp-item-header {
            background: #0D2052;
            color: white;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .skp-item-content {
            padding: 20px;
        }
        
        .skp-field {
            margin-bottom: 15px;
        }
        
        .skp-field label {
            display: block;
            font-weight: bold;
            color: #0D2052;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .editable-field {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 6px;
            padding: 12px;
            font-size: 14px;
            line-height: 1.5;
            min-height: 40px;
        }
        
        .editable-field:focus {
            outline: none;
            border-color: #0D2052;
            box-shadow: 0 0 0 2px rgba(0,82,155,0.1);
        }
        
        .skp-item-with-feedback {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            align-items: flex-start;
            padding-left: 60px;
        }
        
        .skp-table-container {
            flex: 1;
            min-width: 0;
        }
        
        .feedback-form-sidebar {
            width: 350px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            position: sticky;
            top: 20px;
            margin-left: 20px;
        }
        
        .feedback-form-sidebar h5 {
            color: #0D2052;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .perilaku-item {
            display: grid;
            grid-template-columns: 40px 1fr 1fr;
            gap: 15px;
            margin-bottom: 0;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #0D2052;
            min-height: 120px;
        }
        
        .perilaku-number {
            background: #0D2052;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .perilaku-title {
            font-weight: bold;
            color: #0D2052;
            font-size: 14px;
        }
        
        .perilaku-desc {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: pre-line;
        }
        
        .ekspektasi {
            background: #fff3cd;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
            font-size: 12px;
            white-space: pre-line;
        }
        
        @media (max-width: 1200px) {
            .skp-item-with-feedback {
                flex-direction: column;
            }
            
            .feedback-form-sidebar {
                width: 100%;
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .skp-table {
                font-size: 10px;
            }
            
            .skp-table th,
            .skp-table td {
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📝 Edit Realisasi SKP</h1>
            <p>ID SKP Global: <?= htmlspecialchars($id_skp_global) ?> | Status: SUBMITTED - Hanya kolom Realisasi yang dapat diedit (SATUAN read-only)</p>
        </div>
        
        <div class="content">
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <strong>✅ Berhasil:</strong> <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <strong>❌ Error:</strong> <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            
            <!-- Pegawai Information -->
            <div class="info-section">
                <h3 style="color: #0D2052; margin-bottom: 15px;">INFORMASI PEGAWAI</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama Pegawai</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NAMA']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIP</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NIP']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jabatan</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['JABATAN'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Unit Kerja</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['UNIT_KERJA'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Pangkat/Gol Ruang</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['PANGKAT_GOL_RUANG'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Atasan Langsung</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NAMA_ATASAN_LANGSUNG'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIP Atasan</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NIP_ATASAN_LANGSUNG'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Periode</div>
                        <div class="info-value">Triwulan <?= htmlspecialchars($first_row['TRIWULAN']) ?> Tahun <?= htmlspecialchars($first_row['TAHUN']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span style="color: #17a2b8; font-weight: bold;">SUBMITTED</span>
                        </div>
                    </div>
                </div>
            </div>



            <form method="POST">
                <!-- A. KINERJA UTAMA -->
                <div class="section-title">A. KINERJA UTAMA</div>
                <?php if (!empty($kinerja_utama)): ?>
                    <?php foreach ($kinerja_utama as $index => $row): ?>
                    <div style="margin-bottom: 20px;">
                        <table class="skp-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">NO</th>
                                        <th>RENCANA HASIL KERJA PIMPINAN YANG DIINTERVENSI</th>
                                        <th>RENCANA HASIL KERJA</th>
                                        <th>ASPEK</th>
                                        <th>INDIKATOR KINERJA INDIVIDU</th>
                                        <th>TARGET</th>
                                        <th>REALISASI BERDASARKAN BUKTI DUKUNG</th>
                                        <th>SATUAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Check if activity was not performed
                                    $target_value = trim($row['TARGET'] ?? '');
                                    $realisasi_value = trim($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '');
                                    $is_not_performed = ($target_value === '0' && $realisasi_value === '0');
                                    ?>
                                    <tr>
                                        <td style="text-align: center; font-weight: bold;"><?= $index + 1 ?></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RHK_PIMPINAN_INTERV'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RENCANA_HASIL_KERJA'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['ASPEK'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['TARGET'] ?? '')) ?></div></td>
                                        <td>
                                            <?php if ($is_not_performed): ?>
                                                <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 6px; padding: 15px; text-align: center;">
                                                    <div style="font-size: 24px; margin-bottom: 10px;">🚫</div>
                                                    <div style="font-weight: bold; color: #856404; font-size: 14px; margin-bottom: 5px;">KEGIATAN TIDAK DILAKUKAN</div>
                                                    <div style="color: #856404; font-size: 12px;">Realisasi tidak dapat diubah untuk kegiatan yang tidak dilakukan</div>
                                                </div>
                                                <input type="hidden" name="realisasi_utama[<?= htmlspecialchars($row['ID_SKP']) ?>]" value="0">
                                            <?php else: ?>
                                                <textarea name="realisasi_utama[<?= htmlspecialchars($row['ID_SKP']) ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '') ?></textarea>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($is_not_performed): ?>
                                                <div style="background: #f0f0f0; border: 1px solid #0D2052; border-radius: 6px; padding: 12px; text-align: center; color: #6c757d;">
                                                    <em>N/A</em>
                                                </div>
                                                <input type="hidden" name="satuan_utama[<?= htmlspecialchars($row['ID_SKP']) ?>]" value="">
                                            <?php else: ?>
                                                <div class="skp-field-content" style="background: #f5f5f5; color: #666; cursor: not-allowed;"><?= htmlspecialchars($row['SATUAN'] ?? 'N/A') ?></div>
                                                <input type="hidden" name="satuan_utama[<?= htmlspecialchars($row['ID_SKP']) ?>]" value="<?= htmlspecialchars($row['SATUAN'] ?? '') ?>">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: #666; padding: 20px; background: #f0f0f0; border-radius: 8px; border: 1px solid #0D2052;">
                        <p>Tidak ada data kinerja utama.</p>
                    </div>
                <?php endif; ?>

                <!-- B. KINERJA TAMBAHAN -->
                <div class="section-title">B. KINERJA TAMBAHAN</div>
                <?php if (!empty($kinerja_tambahan)): ?>
                    <?php foreach ($kinerja_tambahan as $index => $row): ?>
                    <div style="margin-bottom: 20px;">
                        <table class="skp-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">NO</th>
                                        <th>RENCANA HASIL KERJA PIMPINAN YANG DIINTERVENSI</th>
                                        <th>RENCANA HASIL KERJA</th>
                                        <th>ASPEK</th>
                                        <th>INDIKATOR KINERJA INDIVIDU</th>
                                        <th>TARGET</th>
                                        <th>REALISASI BERDASARKAN BUKTI DUKUNG</th>
                                        <th>SATUAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Check if activity was not performed
                                    $target_value = trim($row['TARGET'] ?? '');
                                    $realisasi_value = trim($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '');
                                    $is_not_performed = ($target_value === '0' && $realisasi_value === '0');
                                    ?>
                                    <tr>
                                        <td style="text-align: center; font-weight: bold;"><?= $index + 1 ?></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RHK_PIMPINAN_INTERV'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RENCANA_HASIL_KERJA'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['ASPEK'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU'] ?? '')) ?></div></td>
                                        <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['TARGET'] ?? '')) ?></div></td>
                                        <td>
                                            <?php if ($is_not_performed): ?>
                                                <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 6px; padding: 15px; text-align: center;">
                                                    <div style="font-size: 24px; margin-bottom: 10px;">🚫</div>
                                                    <div style="font-weight: bold; color: #856404; font-size: 14px; margin-bottom: 5px;">KEGIATAN TIDAK DILAKUKAN</div>
                                                    <div style="color: #856404; font-size: 12px;">Realisasi tidak dapat diubah untuk kegiatan yang tidak dilakukan</div>
                                                </div>
                                                <input type="hidden" name="realisasi_tambahan[<?= htmlspecialchars($row['ID_SKP']) ?>]" value="0">
                                            <?php else: ?>
                                                <textarea name="realisasi_tambahan[<?= htmlspecialchars($row['ID_SKP']) ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '') ?></textarea>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($is_not_performed): ?>
                                                <div style="background: #f0f0f0; border: 1px solid #0D2052; border-radius: 6px; padding: 12px; text-align: center; color: #6c757d;">
                                                    <em>N/A</em>
                                                </div>
                                                <input type="hidden" name="satuan_tambahan[<?= htmlspecialchars($row['ID_SKP']) ?>]" value="">
                                            <?php else: ?>
                                                <div class="skp-field-content" style="background: #f5f5f5; color: #666; cursor: not-allowed;"><?= htmlspecialchars($row['SATUAN'] ?? 'N/A') ?></div>
                                                <input type="hidden" name="satuan_tambahan[<?= htmlspecialchars($row['ID_SKP']) ?>]" value="<?= htmlspecialchars($row['SATUAN'] ?? '') ?>">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; color: #666; padding: 20px; background: #f0f0f0; border-radius: 8px; border: 1px solid #0D2052;">
                        <p>Tidak ada data kinerja tambahan.</p>
                    </div>
                <?php endif; ?>
                
                <!-- Perilaku Kerja -->
                <?php if (!empty($perilaku_data) && is_array($perilaku_data)): ?>
                <div class="section-title">C. PERILAKU KERJA</div>
                <?php
                $perilaku_items = [
                    'berorientasi_pelayanan' => 'BERORIENTASI PELAYANAN',
                    'akuntabel' => 'AKUNTABEL', 
                    'kompeten' => 'KOMPETEN',
                    'harmonis' => 'HARMONIS',
                    'loyal' => 'LOYAL',
                    'adaptif' => 'ADAPTIF',
                    'kolaboratif' => 'KOLABORATIF'
                ];
                
                $descriptions = [
                    'berorientasi_pelayanan' => "- Memahami dan memenuhi kebutuhan masyarakat.\n- Ramah, cekatan, solutif, dan dapat diandalkan.\n- Melakukan perbaikan tiada henti.",
                    'akuntabel' => "- Melaksanakan tugas dengan jujur, bertanggungjawab, cermat, disiplin dan berintegritas tinggi.\n- Menggunakan kekayaan dan barang milik negara secara bertanggungjawab, efektif dan efisien.\n- Tidak menyalahgunakan kewenangan jabatan.",
                    'kompeten' => "- Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah.\n- Membantu orang lain belajar.\n- Melaksanakan tugas dengan kualitas terbaik.",
                    'harmonis' => "- Menghargai setiap orang apapun latar belakangnya.\n- Suka menolong orang lain.\n- Membangun lingkungan kerja yang kondusif.",
                    'loyal' => "- Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, setia pada Negara Kesatuan Republik Indonesia serta pemerintahan yang sah.\n- Menjaga nama baik ASN, Pimpinan, Instansi dan Negara.\n- Menjaga rahasia jabatan dan negara.",
                    'adaptif' => "- Cepat menyesuaikan diri menghadapi perubahan\n- Terus berinovasi dan mengembangkan kreativitas\n- Bertindak proaktif",
                    'kolaboratif' => "- Memberi kesempatan kepada berbagai pihak untuk berkontribusi.\n- Terbuka dalam bekerjasama untuk menghasilkan nilai tambah.\n- Menggerakan pemanfaatan berbagai sumber daya untuk tujuan bersama."
                ];
                
                $index = 1;
                foreach ($perilaku_items as $key => $title):
                    $ekspektasi_key = 'EKSPEKTASI_PIMPINAN_' . strtoupper($key);
                    $ekspektasi = isset($perilaku_data[$ekspektasi_key]) ? $perilaku_data[$ekspektasi_key] : '';
                    $feedback_key = 'UMPAN_BALIK_' . strtoupper($key);
                    $current_feedback = isset($perilaku_data[$feedback_key]) ? $perilaku_data[$feedback_key] : '';
                ?>
                    <div class="skp-item-with-feedback">
                        <div class="skp-table-container">
                            <div class="perilaku-item">
                                <div class="perilaku-number"><?= $index ?></div>
                                <div>
                                    <div class="perilaku-title"><?= $title ?></div>
                                    <div class="perilaku-desc"><?= nl2br(htmlspecialchars($descriptions[$key])) ?></div>
                                </div>
                                <div>
                                    <strong>Ekspektasi Khusus Pimpinan:</strong>
                                    <div class="ekspektasi"><?= nl2br(htmlspecialchars($ekspektasi)) ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Feedback Form Sidebar -->
                        <div class="feedback-form-sidebar">
                            <h5>UMPAN BALIK PERILAKU KERJA</h5>
                            <div class="evaluation-textarea" style="background: #f0f0f0; border: 1px solid #0D2052; border-radius: 6px; padding: 10px; font-size: 14px; min-height: 100px; white-space: pre-line;" readonly>
                                <?= htmlspecialchars($current_feedback ?: 'Belum ada umpan balik') ?>
                            </div>
                        </div>
                    </div>
                <?php 
                $index++;
                endforeach; 
                ?>
                <?php endif; ?>
                
                <div class="buttons">
                    <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
