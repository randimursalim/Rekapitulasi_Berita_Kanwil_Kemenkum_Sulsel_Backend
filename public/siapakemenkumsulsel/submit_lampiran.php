<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die('Connection failed: ' . $e->getMessage());
}

$pegawai = [];
$sql = "SELECT * FROM Pegawai";
$result = $conn->query($sql);
$pegawai_by_nip = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pegawai[] = $row;
        $pegawai_by_nip[$row['NIP']] = $row;
    }
}

$successMsg = '';
$errorMsg = '';

// Check for edit mode
$edit_mode = false;
$view_only = false;
$edit_id_lampiran = $_GET['id_lampiran'] ?? '';
$edit_tahun = $_GET['tahun'] ?? '';
$edit_nip = $_GET['nip'] ?? '';
$edit_lampiran_data = [];

// Check if this is view-only mode (no edit parameter)
if (isset($_GET['view']) && $_GET['view'] === '1') {
    $view_only = true;
    // Fetch data for view mode using tahun and nip parameters
    $view_tahun = $_GET['tahun'] ?? '';
    $view_nip = $_GET['nip'] ?? '';
    
    if ($view_tahun && $view_nip) {
        // Get basic info from first record for view mode
        $sql_lampiran = "SELECT * FROM skp_lampiran WHERE nip = ? AND tahun = ? LIMIT 1";
        $stmt_lampiran = $conn->prepare($sql_lampiran);
        $stmt_lampiran->bind_param('si', $view_nip, $view_tahun);
        $stmt_lampiran->execute();
        $result_lampiran = $stmt_lampiran->get_result();
        if ($result_lampiran && $result_lampiran->num_rows > 0) {
            $first_record = $result_lampiran->fetch_assoc();
            // Get complete employee data from Pegawai table
            $sql_pegawai = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
            $stmt_pegawai = $conn->prepare($sql_pegawai);
            $stmt_pegawai->bind_param('s', $view_nip);
            $stmt_pegawai->execute();
            $result_pegawai = $stmt_pegawai->get_result();
            if ($result_pegawai && $result_pegawai->num_rows > 0) {
                $pegawai_data = $result_pegawai->fetch_assoc();
                // Get supervisor data if available
                $atasan_data = null;
                if (!empty($first_record['nip_atasan_langsung'])) {
                    $sql_atasan = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
                    $stmt_atasan = $conn->prepare($sql_atasan);
                    $stmt_atasan->bind_param('s', $first_record['nip_atasan_langsung']);
                    $stmt_atasan->execute();
                    $result_atasan = $stmt_atasan->get_result();
                    if ($result_atasan && $result_atasan->num_rows > 0) {
                        $atasan_data = $result_atasan->fetch_assoc();
                    }
                    $stmt_atasan->close();
                } else {
                    // If no supervisor NIP in lampiran, try to get supervisor from Pegawai table
                    $sql_atasan_from_pegawai = "SELECT ATASAN_LANGSUNG FROM Pegawai WHERE NIP = ? LIMIT 1";
                    $stmt_atasan_from_pegawai = $conn->prepare($sql_atasan_from_pegawai);
                    $stmt_atasan_from_pegawai->bind_param('s', $view_nip);
                    $stmt_atasan_from_pegawai->execute();
                    $result_atasan_from_pegawai = $stmt_atasan_from_pegawai->get_result();
                    if ($result_atasan_from_pegawai && $result_atasan_from_pegawai->num_rows > 0) {
                        $atasan_jabatan = $result_atasan_from_pegawai->fetch_assoc()['ATASAN_LANGSUNG'];
                        if (!empty($atasan_jabatan)) {
                            // Get supervisor data by job title
                            $sql_atasan_by_jabatan = "SELECT * FROM Pegawai WHERE JABATAN = ? LIMIT 1";
                            $stmt_atasan_by_jabatan = $conn->prepare($sql_atasan_by_jabatan);
                            $stmt_atasan_by_jabatan->bind_param('s', $atasan_jabatan);
                            $stmt_atasan_by_jabatan->execute();
                            $result_atasan_by_jabatan = $stmt_atasan_by_jabatan->get_result();
                            if ($result_atasan_by_jabatan && $result_atasan_by_jabatan->num_rows > 0) {
                                $atasan_data = $result_atasan_by_jabatan->fetch_assoc();
                            }
                            $stmt_atasan_by_jabatan->close();
                        }
                    }
                    $stmt_atasan_from_pegawai->close();
                }
                
                // Merge lampiran data with complete pegawai data
                $edit_lampiran_data = array_merge([
                    'nama' => $pegawai_data['NAMA'] ?? $first_record['nama'],
                    'nip' => $pegawai_data['NIP'] ?? $first_record['nip'],
                    'jabatan' => $pegawai_data['JABATAN'] ?? 'N/A',
                    'nama_atasan_langsung' => $atasan_data['NAMA'] ?? $first_record['nama_atasan_langsung'] ?? 'Tidak Ada Atasan',
                    'nip_atasan_langsung' => $atasan_data['NIP'] ?? $first_record['nip_atasan_langsung'] ?? 'N/A',
                    'jabatan_atasan_langsung' => $atasan_data['JABATAN'] ?? 'N/A'
                ], $first_record);
            } else {
                $edit_lampiran_data = $first_record;
            }
            $stmt_pegawai->close();
            
            $user_nip = $view_nip;
            $tahun = $view_tahun;
        }
        $stmt_lampiran->close();
        
        // Get all entries for this user and year for view mode
        if ($user_nip && $tahun) {
            $sql_all = "SELECT * FROM skp_lampiran WHERE nip = ? AND tahun = ? ORDER BY kategori_lampiran, id_lampiran";
            $stmt_all = $conn->prepare($sql_all);
            $stmt_all->bind_param('si', $user_nip, $tahun);
            $stmt_all->execute();
            $result_all = $stmt_all->get_result();
            
            $edit_dukungan_data = [];
            $edit_skema_data = [];
            $edit_konsekuensi_data = [];
            
            while ($row = $result_all->fetch_assoc()) {
                switch ($row['kategori_lampiran']) {
                    case 'DUKUNGAN SUMBER DAYA':
                        $edit_dukungan_data[] = $row;
                        break;
                    case 'SKEMA PERTANGGUNGJAWABAN':
                        $edit_skema_data[] = $row;
                        break;
                    case 'KONSEKUENSI':
                        $edit_konsekuensi_data[] = $row;
                        break;
                }
            }
            $stmt_all->close();
        }
    }
}

if ($edit_id_lampiran) {
    $edit_mode = true;
    // Fetch existing data for edit - get all entries for this user and year
    $sql_lampiran = "SELECT * FROM skp_lampiran WHERE id_lampiran = ? LIMIT 1";
    $stmt_lampiran = $conn->prepare($sql_lampiran);
    $stmt_lampiran->bind_param('i', $edit_id_lampiran);
    $stmt_lampiran->execute();
    $result_lampiran = $stmt_lampiran->get_result();
    if ($result_lampiran && $result_lampiran->num_rows > 0) {
        $first_record = $result_lampiran->fetch_assoc();
        
        // Get complete employee data from Pegawai table
        $sql_pegawai = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
        $stmt_pegawai = $conn->prepare($sql_pegawai);
        $stmt_pegawai->bind_param('s', $first_record['nip']);
        $stmt_pegawai->execute();
        $result_pegawai = $stmt_pegawai->get_result();
        if ($result_pegawai && $result_pegawai->num_rows > 0) {
            $pegawai_data = $result_pegawai->fetch_assoc();
            
            // Get supervisor data if available
            $atasan_data = null;
            if (!empty($first_record['nip_atasan_langsung'])) {
                $sql_atasan = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
                $stmt_atasan = $conn->prepare($sql_atasan);
                $stmt_atasan->bind_param('s', $first_record['nip_atasan_langsung']);
                $stmt_atasan->execute();
                $result_atasan = $stmt_atasan->get_result();
                if ($result_atasan && $result_atasan->num_rows > 0) {
                    $atasan_data = $result_atasan->fetch_assoc();
                }
                $stmt_atasan->close();
            }
            
            // Merge lampiran data with complete pegawai data
            $edit_lampiran_data = array_merge([
                'nama' => $pegawai_data['NAMA'] ?? $first_record['nama'],
                'nip' => $pegawai_data['NIP'] ?? $first_record['nip'],
                'jabatan' => $pegawai_data['JABATAN'] ?? 'N/A',
                'nama_atasan_langsung' => $atasan_data['NAMA'] ?? $first_record['nama_atasan_langsung'] ?? 'Tidak Ada Atasan',
                'nip_atasan_langsung' => $atasan_data['NIP'] ?? $first_record['nip_atasan_langsung'] ?? 'N/A',
                'jabatan_atasan_langsung' => $atasan_data['JABATAN'] ?? 'N/A'
            ], $first_record);
        } else {
            $edit_lampiran_data = $first_record;
        }
        $stmt_pegawai->close();
        
        // Get all entries for this user and year
        $user_nip = $first_record['nip'];
        $tahun = $first_record['tahun'];
    }
    $stmt_lampiran->close();
} elseif ($edit_tahun && $edit_nip) {
    $edit_mode = true;
    // Fetch existing data for edit using tahun and nip parameters
    $user_nip = $edit_nip;
    $tahun = $edit_tahun;
    
    // Get basic info from first record
    $sql_lampiran = "SELECT * FROM skp_lampiran WHERE nip = ? AND tahun = ? LIMIT 1";
    $stmt_lampiran = $conn->prepare($sql_lampiran);
    $stmt_lampiran->bind_param('si', $user_nip, $tahun);
    $stmt_lampiran->execute();
    $result_lampiran = $stmt_lampiran->get_result();
    if ($result_lampiran && $result_lampiran->num_rows > 0) {
        $first_record = $result_lampiran->fetch_assoc();
        
        // Get complete employee data from Pegawai table
        $sql_pegawai = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
        $stmt_pegawai = $conn->prepare($sql_pegawai);
        $stmt_pegawai->bind_param('s', $first_record['nip']);
        $stmt_pegawai->execute();
        $result_pegawai = $stmt_pegawai->get_result();
        if ($result_pegawai && $result_pegawai->num_rows > 0) {
            $pegawai_data = $result_pegawai->fetch_assoc();
            
            // Get supervisor data if available
            $atasan_data = null;
            if (!empty($first_record['nip_atasan_langsung'])) {
                $sql_atasan = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
                $stmt_atasan = $conn->prepare($sql_atasan);
                $stmt_atasan->bind_param('s', $first_record['nip_atasan_langsung']);
                $stmt_atasan->execute();
                $result_atasan = $stmt_atasan->get_result();
                if ($result_atasan && $result_atasan->num_rows > 0) {
                    $atasan_data = $result_atasan->fetch_assoc();
                }
                $stmt_atasan->close();
            }
            
            // Merge lampiran data with complete pegawai data
            $edit_lampiran_data = array_merge([
                'nama' => $pegawai_data['NAMA'] ?? $first_record['nama'],
                'nip' => $pegawai_data['NIP'] ?? $first_record['nip'],
                'jabatan' => $pegawai_data['JABATAN'] ?? 'N/A',
                'nama_atasan_langsung' => $atasan_data['NAMA'] ?? $first_record['nama_atasan_langsung'] ?? 'Tidak Ada Atasan',
                'nip_atasan_langsung' => $atasan_data['NIP'] ?? $first_record['nip_atasan_langsung'] ?? 'N/A',
                'jabatan_atasan_langsung' => $atasan_data['JABATAN'] ?? 'N/A'
            ], $first_record);
        } else {
            $edit_lampiran_data = $first_record;
        }
        $stmt_pegawai->close();
    }
    $stmt_lampiran->close();
}

if ($edit_mode && $user_nip && $tahun) {
    // Get all entries for this user and year
    $sql_all = "SELECT * FROM skp_lampiran WHERE nip = ? AND tahun = ? ORDER BY kategori_lampiran, id_lampiran";
    $stmt_all = $conn->prepare($sql_all);
    $stmt_all->bind_param('si', $user_nip, $tahun);
    $stmt_all->execute();
    $result_all = $stmt_all->get_result();
    
    $edit_dukungan_data = [];
    $edit_skema_data = [];
    $edit_konsekuensi_data = [];
    
    while ($row = $result_all->fetch_assoc()) {
        switch ($row['kategori_lampiran']) {
            case 'DUKUNGAN SUMBER DAYA':
                $edit_dukungan_data[] = $row;
                break;
            case 'SKEMA PERTANGGUNGJAWABAN':
                $edit_skema_data[] = $row;
                break;
            case 'KONSEKUENSI':
                $edit_konsekuensi_data[] = $row;
                break;
        }
    }
    $stmt_all->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for duplicate submission
    if (isset($_SESSION['last_submit_time']) && (time() - $_SESSION['last_submit_time']) < 5) {
        $errorMsg = '❌ Terlalu cepat! Silakan tunggu sebentar sebelum mengirim ulang.';
    } else {
        $_SESSION['last_submit_time'] = time();
        
        $nip = $_POST['pegawai_nip'] ?? '';
        $pegawaiData = $pegawai_by_nip[$nip] ?? null;
        $atasan = null;
        
        // Get atasan langsung
        if ($pegawaiData && !empty($pegawaiData['ATASAN_LANGSUNG'])) {
            $atasan_sql = "SELECT * FROM Pegawai WHERE JABATAN = ? LIMIT 1";
            $stmt_atasan = $conn->prepare($atasan_sql);
            if ($stmt_atasan) {
                $stmt_atasan->bind_param('s', $pegawaiData['ATASAN_LANGSUNG']);
                $stmt_atasan->execute();
                $result_atasan = $stmt_atasan->get_result();
                if ($result_atasan && $result_atasan->num_rows > 0) {
                    $atasan = $result_atasan->fetch_assoc();
                }
                $stmt_atasan->close();
            }
        }
        
        // Prepare variables
        $namaPegawai = $pegawaiData['NAMA'] ?? '';
        $nipPegawai = $pegawaiData['NIP'] ?? '';
        $namaAtasan = $atasan['NAMA'] ?? '';
        $nipAtasan = $atasan['NIP'] ?? '';
        $tahun = $_POST['tahun'] ?? '';
        $kategoriLampiran = $_POST['kategori_lampiran'] ?? '';
        $isiLampiran = $_POST['isi_lampiran'] ?? '';
        $status = 'draft';
        $pangkatPegawai = $pegawaiData['PANGKAT_GOL_RUANG'] ?? '';
        $jabatanPegawai = $pegawaiData['JABATAN'] ?? '';
        $unitKerjaPegawai = $pegawaiData['UNIT_KERJA'] ?? '';
        $satuanKerjaPegawai = $pegawaiData['SATUAN_KERJA'] ?? '';
        
        // Validation
        if (empty($nipPegawai)) {
            $errorMsg = 'Silakan pilih Pegawai.';
        } elseif (empty($tahun)) {
            $errorMsg = 'Silakan lengkapi Tahun.';
        } else {
            // Check if lampiran already exists for this employee and year (only for new entries, not edit mode)
            if (!$edit_mode) {
                $check_existing_sql = "SELECT COUNT(*) as count FROM skp_lampiran WHERE nip = ? AND tahun = ?";
                $check_stmt = $conn->prepare($check_existing_sql);
                $check_stmt->bind_param('si', $nipPegawai, $tahun);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $existing_count = 0;
                if ($check_result && $row = $check_result->fetch_assoc()) {
                    $existing_count = (int)$row['count'];
                }
                $check_stmt->close();
                
                if ($existing_count > 0) {
                    $errorMsg = "❌ Lampiran SKP untuk tahun $tahun sudah ada untuk pegawai ini. Silakan gunakan fitur 'UBAH' untuk mengedit lampiran yang sudah ada.";
                }
            }
            
            // Validate that at least one entry exists for each category (only if no year conflict)
            if (empty($errorMsg)) {
                $dukungan_isi = $_POST['dukungan_isi'] ?? [];
                $skema_isi = $_POST['skema_isi'] ?? [];
                $konsekuensi_isi = $_POST['konsekuensi_isi'] ?? [];
                
                if (empty(array_filter($dukungan_isi))) {
                    $errorMsg = 'Minimal satu entri DUKUNGAN SUMBER DAYA harus diisi.';
                } elseif (empty(array_filter($skema_isi))) {
                    $errorMsg = 'Minimal satu entri SKEMA PERTANGGUNGJAWABAN harus diisi.';
                } elseif (empty(array_filter($konsekuensi_isi))) {
                    $errorMsg = 'Minimal satu entri KONSEKUENSI harus diisi.';
                }
            }
        }
        
        // Process data
        if (empty($errorMsg)) {
            if ($edit_mode && isset($_POST['save_edit'])) {
                // For edit mode, we'll delete existing entries and insert new ones
                // Delete existing entries for this user and year
                $delete_sql = "DELETE FROM skp_lampiran WHERE nip = ? AND tahun = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param('si', $nipPegawai, $tahun);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
            
            // Insert all entries for each category
            $insert_sql = "INSERT INTO skp_lampiran (nama, nip, nama_atasan_langsung, nip_atasan_langsung, PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA, tahun, kategori_lampiran, isi_lampiran, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $inserted_count = 0;
            
            if ($stmt) {
                // Insert DUKUNGAN SUMBER DAYA entries
                $kategori_dukungan = 'DUKUNGAN SUMBER DAYA';
                foreach ($dukungan_isi as $isi) {
                    if (!empty(trim($isi))) {
                        $isi_trimmed = trim($isi);
                        $stmt->bind_param('ssssssssssss', $namaPegawai, $nipPegawai, $namaAtasan, $nipAtasan, $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai, $tahun, $kategori_dukungan, $isi_trimmed, $status);
                        if ($stmt->execute()) {
                            $inserted_count++;
                        }
                    }
                }
                
                // Insert SKEMA PERTANGGUNGJAWABAN entries
                $kategori_skema = 'SKEMA PERTANGGUNGJAWABAN';
                foreach ($skema_isi as $isi) {
                    if (!empty(trim($isi))) {
                        $isi_trimmed = trim($isi);
                        $stmt->bind_param('ssssssssssss', $namaPegawai, $nipPegawai, $namaAtasan, $nipAtasan, $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai, $tahun, $kategori_skema, $isi_trimmed, $status);
                        if ($stmt->execute()) {
                            $inserted_count++;
                        }
                    }
                }
                
                // Insert KONSEKUENSI entries
                $kategori_konsekuensi = 'KONSEKUENSI';
                foreach ($konsekuensi_isi as $isi) {
                    if (!empty(trim($isi))) {
                        $isi_trimmed = trim($isi);
                        $stmt->bind_param('ssssssssssss', $namaPegawai, $nipPegawai, $namaAtasan, $nipAtasan, $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai, $tahun, $kategori_konsekuensi, $isi_trimmed, $status);
                        if ($stmt->execute()) {
                            $inserted_count++;
                        }
                    }
                }
                
                $stmt->close();
                
                if ($inserted_count > 0) {
                    $successMsg = $edit_mode ? 
                        '✅ Data lampiran berhasil diperbarui! (' . $inserted_count . ' entri)' :
                        '✅ Data lampiran berhasil disimpan! (' . $inserted_count . ' entri)';
                    // Redirect to prevent double submission
                    header('Location: skploginpage.php?success=' . ($edit_mode ? 'lampiran_updated' : 'lampiran_saved'));
                    exit();
                } else {
                    $errorMsg = '❌ Tidak ada data yang valid untuk disimpan.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.67, minimum-scale=0.67, maximum-scale=2.0, user-scalable=yes">
    <title><?= $view_only ? 'Lihat Detail Lampiran SKP' : ($edit_mode ? 'Edit Lampiran SKP' : 'Tambah Lampiran SKP') ?> - SI-APA</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <?php include 'includes/sidebar_styles.php'; ?>
    <style>
        * { box-sizing: border-box; }
        body {
            background: #0D2052;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            background: white;
            flex: 1;
            width: 100%;
            min-width: 0;
            margin: 0 0 0 260px;
            border-radius: 0;
            box-shadow: none;
            padding: 20px 24px;
            border: 2px solid #0D2052;
            border-left: none;
            min-height: 100vh;
        }
        @media (min-width: 1200px) {
            .container { padding: 24px 28px; }
        }
        @media (min-width: 1600px) {
            .container { padding: 28px 32px; }
        }
        .back-link {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 14px;
        }
        .back-link:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            text-decoration: none;
            color: white;
        }
        .row {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .col {
            flex: 1 1 260px;
            min-width: 240px;
        }
        @media (min-width: 1200px) {
            .col { flex: 1 1 320px; min-width: 280px; }
        }
        @media (min-width: 1600px) {
            .col { flex: 1 1 380px; min-width: 320px; }
        }
        .section-title {
            font-weight: bold;
            margin: 18px 0 8px 0;
            color: #0D2052;
            font-size: 0.95rem;
        }
        @media (min-width: 1200px) {
            .section-title { font-size: 1rem; margin: 20px 0 10px 0; }
        }
        @media (min-width: 1600px) {
            .section-title { font-size: 1.05rem; margin: 22px 0 10px 0; }
        }
        .card {
            background: #f0f0f0;
            border: 1px solid rgba(13, 32, 82, 0.2);
            border-radius: 8px;
            padding: 10px 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        @media (min-width: 1200px) {
            .card { padding: 12px 14px; }
        }
        @media (min-width: 1600px) {
            .card { padding: 12px 14px; }
        }
        .card h4 {
            margin: 0 0 8px 0;
            font-size: 0.9rem;
            color: #0D2052;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 6px 10px;
            align-items: center;
        }
        @media (min-width: 1200px) {
            .info-grid { grid-template-columns: 140px 1fr; gap: 8px 12px; }
        }
        .info-label {
            color: #4b5b6a;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .info-value {
            background: #ffffff;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 6px 8px;
            min-height: 30px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        .dropdown {
            width: 100%;
            padding: 6px 8px;
            margin-bottom: 8px;
            border-radius: 3px;
            border: 1px solid #ccc;
            background: #f2f2f2;
            font-size: 0.9rem;
        }
        .dropdown input[type="number"], .dropdown input[type="text"], .dropdown textarea, .dropdown select {
            width: 100%;
            padding: 6px 8px;
            margin-bottom: 8px;
            border-radius: 3px;
            border: 1px solid #ccc;
            background: #f2f2f2;
            font-size: 0.9rem;
            box-sizing: border-box;
        }
        .dropdown textarea {
            min-height: 80px;
            resize: vertical;
        }
        label {
            font-weight: bold;
            font-size: 0.85rem;
            margin-bottom: 4px;
            display: block;
        }
        .submit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 24px;
            font-size: 0.9rem;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 18px;
            margin-top: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        .submit-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
            padding-top: 6px;
            clear: both;
        }
        .lampiran-section {
            margin-bottom: 14px;
        }
        .lampiran-item {
            background: #ffffff;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }
        .lampiran-item:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .lampiran-item {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .lampiran-item .lampiran-simple {
            flex: 1;
            min-width: 0;
        }
        .lampiran-item-aksi {
            flex-shrink: 0;
            padding-top: 4px;
        }
        .btn-hapus-row {
            padding: 4px 8px;
            font-size: 0.7rem;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-hapus-row:hover {
            background: #c82333;
        }
        
        @media (max-width: 900px) {
            .row { flex-direction: column; gap: 10px; }
            .container { padding: 16px 14px; }
        }
        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                border-left: 2px solid #0D2052;
                padding: 14px 10px;
            }
            .info-grid { grid-template-columns: 1fr; gap: 4px; }
            .info-label { font-weight: bold; margin-bottom: 2px; }
            .lampiran-textarea, .lampiran-textarea-simple {
                min-height: 70px;
                font-size: 0.85rem;
                padding: 8px;
            }
        }
        .lampiran-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 2px solid #f0f0f0;
        }
        .lampiran-number {
            background: #0D2052;
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 8px;
            font-size: 12px;
        }
        .lampiran-category {
            font-weight: bold;
            color: #0D2052;
            font-size: 0.9rem;
        }
        .lampiran-textarea {
            width: 100%;
            min-height: 80px;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 8px 10px;
            font-family: inherit;
            font-size: 0.85rem;
            resize: vertical;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        @media (min-width: 1200px) {
            .lampiran-textarea {
                min-height: 90px;
                font-size: 0.88rem;
                padding: 8px 10px;
            }
        }
        .lampiran-textarea:focus {
            border-color: rgba(13, 32, 82, 0.5);
            box-shadow: 0 0 0 2px rgba(13, 32, 82, 0.1);
            outline: none;
        }
        .add-lampiran-btn {
            background: #b3e0ff;
            color: #00529B;
            border: none;
            border-radius: 4px;
            padding: 6px 14px;
            font-weight: bold;
            font-size: 11px;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 12px;
        }
        .add-lampiran-btn:hover {
            background: #7fd0ff;
        }
        .lampiran-simple {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 8px;
        }
        .lampiran-number-simple {
            background: #0D2052;
            color: white;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            flex-shrink: 0;
            margin-top: 6px;
        }
        .lampiran-textarea-simple {
            flex: 1;
            min-height: 80px;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 8px 10px;
            font-family: inherit;
            font-size: 0.85rem;
            resize: vertical;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        @media (min-width: 1200px) {
            .lampiran-textarea-simple {
                min-height: 90px;
                font-size: 0.88rem;
            }
        }
        .lampiran-textarea-simple:focus {
            border-color: rgba(13, 32, 82, 0.5);
            box-shadow: 0 0 0 2px rgba(13, 32, 82, 0.1);
            outline: none;
        }
    </style>
</head>
<body>
    <?php if (!$view_only): ?>
        <?php include 'includes/sidebar.php'; ?>
    <?php endif; ?>
    <div class="container" style="<?= $view_only ? 'margin-left: 0; border-left: 2px solid #0D2052;' : '' ?>">
        <?php if ($view_only): ?>
            <div style="margin-bottom: 14px;">
                <button type="button" onclick="window.close()" class="back-link">← Tutup</button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col card">
                <h4>PEGAWAI</h4>
                <?php if (($edit_mode || $view_only) && !empty($edit_lampiran_data)): ?>
                    <!-- Display employee info from lampiran data -->
                    <div class="info-grid" id="pegawai-info">
                        <div class="info-label">Nama</div><div class="info-value"><?php echo htmlspecialchars($edit_lampiran_data['nama'] ?? 'N/A'); ?></div>
                        <div class="info-label">NIP</div><div class="info-value"><?php echo htmlspecialchars($edit_lampiran_data['nip'] ?? 'N/A'); ?></div>
                        <div class="info-label">Jabatan</div><div class="info-value"><?php echo htmlspecialchars($edit_lampiran_data['jabatan'] ?? 'N/A'); ?></div>
                    </div>
                <?php else: ?>
                    <!-- Regular employee selection for new entries -->
                    <select id="pegawai" class="dropdown">
                        <option value=""> </option>
                        <?php
                        $nip_logged_in = $_SESSION['nip'] ?? '';
                        $pegawai_logged_in = null;
                        if ($nip_logged_in) {
                            $sql = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param('s', $nip_logged_in);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            if ($result && $result->num_rows > 0) {
                                $pegawai_logged_in = $result->fetch_assoc();
                            }
                            $stmt->close();
                        }
                        ?>
                        <?php if ($pegawai_logged_in): ?>
                            <option value="<?= htmlspecialchars($pegawai_logged_in['NIP']) ?>"
                                data-nama="<?= htmlspecialchars($pegawai_logged_in['NAMA']) ?>"
                                data-nip="<?= htmlspecialchars($pegawai_logged_in['NIP']) ?>"
                                data-pangkat="<?= htmlspecialchars($pegawai_logged_in['PANGKAT_GOL_RUANG']) ?>"
                                data-jabatan="<?= htmlspecialchars($pegawai_logged_in['JABATAN']) ?>"
                                data-unit="<?= htmlspecialchars($pegawai_logged_in['UNIT_KERJA']) ?>"
                                data-atasan="<?= isset($pegawai_logged_in['ATASAN_LANGSUNG']) ? htmlspecialchars($pegawai_logged_in['ATASAN_LANGSUNG']) : '' ?>"
                                <?= $edit_mode && !empty($edit_lampiran_data['nip']) && $edit_lampiran_data['nip'] === $pegawai_logged_in['NIP'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pegawai_logged_in['NAMA']) ?> (<?= htmlspecialchars($pegawai_logged_in['NIP']) ?>)
                            </option>
                        <?php endif; ?>
                    </select>
                    <div class="info-grid" id="pegawai-info">
                        <div class="info-label">Nama</div><div class="info-value" id="info-nama"></div>
                        <div class="info-label">NIP</div><div class="info-value" id="info-nip"></div>
                        <div class="info-label">Pangkat/Gol Ruang</div><div class="info-value" id="info-pangkat"></div>
                        <div class="info-label">Jabatan</div><div class="info-value" id="info-jabatan"></div>
                        <div class="info-label">Unit Kerja</div><div class="info-value" id="info-unit"></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col card">
                <h4>PEJABAT PENILAI KERJA</h4>
                <?php if (($edit_mode || $view_only) && !empty($edit_lampiran_data)): ?>
                    <!-- Display supervisor info from lampiran data -->
                    <div class="info-grid" id="penilai-info">
                        <div class="info-label">Nama</div><div class="info-value"><?php echo htmlspecialchars($edit_lampiran_data['nama_atasan_langsung'] ?? 'N/A'); ?></div>
                        <div class="info-label">NIP</div><div class="info-value"><?php echo htmlspecialchars($edit_lampiran_data['nip_atasan_langsung'] ?? 'N/A'); ?></div>
                        <div class="info-label">Jabatan</div><div class="info-value"><?php echo htmlspecialchars($edit_lampiran_data['jabatan_atasan_langsung'] ?? 'N/A'); ?></div>
                    </div>
                <?php else: ?>
                    <!-- Regular supervisor info for new entries -->
                    <div class="info-grid" id="penilai-info">
                        <div class="info-label">Nama</div><div class="info-value" id="penilai-nama"></div>
                        <div class="info-label">NIP</div><div class="info-value" id="penilai-nip"></div>
                        <div class="info-label">Pangkat/Gol Ruang</div><div class="info-value" id="penilai-pangkat"></div>
                        <div class="info-label">Jabatan</div><div class="info-value" id="penilai-jabatan"></div>
                        <div class="info-label">Unit Kerja</div><div class="info-value" id="penilai-unit"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($view_only): ?>
            <div style="background: #d1ecf1; color: #0c5460; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #17a2b8;">
                <strong>Mode Lihat Detail:</strong> Anda sedang melihat detail lampiran SKP untuk tahun <?php echo htmlspecialchars($tahun); ?>
            </div>
        <?php elseif ($edit_mode && !empty($edit_lampiran_data['status']) && strtoupper($edit_lampiran_data['status']) === 'SELESAI EVALUASI'): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
                <strong>Status: SELESAI EVALUASI</strong> - Lampiran SKP telah disetujui dan dapat didownload sebagai PDF.
                <div style="margin-top: 10px;">
                    <button onclick="downloadLampiranPDF('<?= htmlspecialchars($tahun) ?>', '<?= htmlspecialchars($edit_lampiran_data['nip']) ?>')" style="background: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        <img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> PDF
                    </button>
                </div>
            </div>
        <?php endif; ?>
        
        <form id="lampiran-form" method="post">
            <input type="hidden" name="pegawai_nip" id="pegawai_nip_input" value="<?= ($edit_mode || $view_only) && !empty($edit_lampiran_data['nip']) ? htmlspecialchars($edit_lampiran_data['nip']) : '' ?>">
            
            <?php if (!empty($errorMsg)): ?>
                <div id="alert-error" style="background:#fdecea;color:#b00020;padding:12px 20px;border-radius:8px;margin-bottom:18px;border-left:4px solid #dc3545;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php elseif (!empty($successMsg)): ?>
                <div id="alert-success" style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:18px;border-left:4px solid #28a745;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <strong>Berhasil:</strong> <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>
            
            <div class="section-title">INFORMASI LAMPIRAN</div>
            <div class="row" style="margin-bottom: 30px;">
                <div class="col">
                    <label for="tahun">TAHUN:</label><br>
                    <input type="number" id="tahun" name="tahun" class="dropdown" min="2020" max="2030" value="<?= $edit_mode && !empty($edit_lampiran_data['tahun']) ? htmlspecialchars($edit_lampiran_data['tahun']) : date('Y') ?>" required>
                </div>
            </div>
            
            <div class="section-title">DUKUNGAN SUMBER DAYA</div>
            <div class="lampiran-section" id="dukungan-section">
                <?php if ($edit_mode && !empty($edit_dukungan_data)): ?>
                    <?php foreach ($edit_dukungan_data as $index => $item): ?>
                        <div class="lampiran-item">
                            <div class="lampiran-simple">
                                <span class="lampiran-number-simple"><?= $index + 1 ?></span>
                                <textarea name="dukungan_isi[]" class="lampiran-textarea-simple" placeholder="Masukkan deskripsi dukungan sumber daya..." <?= $view_only ? 'readonly' : 'required' ?>><?= htmlspecialchars($item['isi_lampiran'] ?? '') ?></textarea>
                            </div>
                            <?php if (!$view_only): ?><div class="lampiran-item-aksi"><button type="button" class="btn-hapus-row" onclick="removeLampiranItem('dukungan-section', this)" title="Hapus baris">Hapus</button></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="lampiran-item">
                        <div class="lampiran-simple">
                            <span class="lampiran-number-simple">1</span>
                            <textarea name="dukungan_isi[]" class="lampiran-textarea-simple" placeholder="Masukkan deskripsi dukungan sumber daya..." <?= $view_only ? 'readonly' : 'required' ?>></textarea>
                        </div>
                        <?php if (!$view_only): ?><div class="lampiran-item-aksi"><button type="button" class="btn-hapus-row" onclick="removeLampiranItem('dukungan-section', this)" title="Hapus baris">Hapus</button></div><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!$view_only): ?>
                <button type="button" class="add-lampiran-btn" onclick="addLampiranItem('dukungan-section', 'dukungan_isi[]')">+ TAMBAH DUKUNGAN SUMBER DAYA</button>
            <?php endif; ?>
            
            <div class="section-title">SKEMA PERTANGGUNGJAWABAN</div>
            <div class="lampiran-section" id="skema-section">
                <?php if ($edit_mode && !empty($edit_skema_data)): ?>
                    <?php foreach ($edit_skema_data as $index => $item): ?>
                        <div class="lampiran-item">
                            <div class="lampiran-simple">
                                <span class="lampiran-number-simple"><?= $index + 1 ?></span>
                                <textarea name="skema_isi[]" class="lampiran-textarea-simple" placeholder="Masukkan deskripsi skema pertanggungjawaban..." <?= $view_only ? 'readonly' : 'required' ?>><?= htmlspecialchars($item['isi_lampiran'] ?? '') ?></textarea>
                            </div>
                            <?php if (!$view_only): ?><div class="lampiran-item-aksi"><button type="button" class="btn-hapus-row" onclick="removeLampiranItem('skema-section', this)" title="Hapus baris">Hapus</button></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="lampiran-item">
                        <div class="lampiran-simple">
                            <span class="lampiran-number-simple">1</span>
                            <textarea name="skema_isi[]" class="lampiran-textarea-simple" placeholder="Masukkan deskripsi skema pertanggungjawaban..." <?= $view_only ? 'readonly' : 'required' ?>></textarea>
                        </div>
                        <?php if (!$view_only): ?><div class="lampiran-item-aksi"><button type="button" class="btn-hapus-row" onclick="removeLampiranItem('skema-section', this)" title="Hapus baris">Hapus</button></div><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!$view_only): ?>
                <button type="button" class="add-lampiran-btn" onclick="addLampiranItem('skema-section', 'skema_isi[]')">+ TAMBAH SKEMA PERTANGGUNGJAWABAN</button>
            <?php endif; ?>
            
            <div class="section-title">KONSEKUENSI</div>
            <div class="lampiran-section" id="konsekuensi-section">
                <?php if ($edit_mode && !empty($edit_konsekuensi_data)): ?>
                    <?php foreach ($edit_konsekuensi_data as $index => $item): ?>
                        <div class="lampiran-item">
                            <div class="lampiran-simple">
                                <span class="lampiran-number-simple"><?= $index + 1 ?></span>
                                <textarea name="konsekuensi_isi[]" class="lampiran-textarea-simple" placeholder="Masukkan deskripsi konsekuensi..." <?= $view_only ? 'readonly' : 'required' ?>><?= htmlspecialchars($item['isi_lampiran'] ?? '') ?></textarea>
                            </div>
                            <?php if (!$view_only): ?><div class="lampiran-item-aksi"><button type="button" class="btn-hapus-row" onclick="removeLampiranItem('konsekuensi-section', this)" title="Hapus baris">Hapus</button></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="lampiran-item">
                        <div class="lampiran-simple">
                            <span class="lampiran-number-simple">1</span>
                            <textarea name="konsekuensi_isi[]" class="lampiran-textarea-simple" placeholder="Masukkan deskripsi konsekuensi..." <?= $view_only ? 'readonly' : 'required' ?>></textarea>
                        </div>
                        <?php if (!$view_only): ?><div class="lampiran-item-aksi"><button type="button" class="btn-hapus-row" onclick="removeLampiranItem('konsekuensi-section', this)" title="Hapus baris">Hapus</button></div><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!$view_only): ?>
                <button type="button" class="add-lampiran-btn" onclick="addLampiranItem('konsekuensi-section', 'konsekuensi_isi[]')">+ TAMBAH KONSEKUENSI</button>
            <?php endif; ?>
            
            <?php if (!$view_only): ?>
                <div class="form-actions">
                    <?php if ($edit_mode): ?>
                        <button type="submit" class="submit-btn" name="save_edit" value="1">UPDATE LAMPIRAN</button>
                    <?php else: ?>
                        <button type="submit" class="submit-btn">SIMPAN LAMPIRAN</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var pegawaiDropdown = document.getElementById('pegawai');
        <?php if ($edit_mode && !empty($edit_lampiran_data['nip'])): ?>
            // In edit mode, select the employee from the existing data
            var editNip = '<?= htmlspecialchars($edit_lampiran_data['nip']) ?>';
            for (var i = 0; i < pegawaiDropdown.options.length; i++) {
                if (pegawaiDropdown.options[i].value === editNip) {
                    pegawaiDropdown.selectedIndex = i;
                    var event = new Event('change');
                    pegawaiDropdown.dispatchEvent(event);
                    break;
                }
            }
        <?php else: ?>
            // In new mode, auto-select the first employee if available
            if (pegawaiDropdown && pegawaiDropdown.options.length === 2) {
                pegawaiDropdown.selectedIndex = 1;
                var event = new Event('change');
                pegawaiDropdown.dispatchEvent(event);
            }
        <?php endif; ?>
    });
    
    document.getElementById('pegawai').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        var nip = selected.getAttribute('data-nip') || '';
        
        // Update pegawai info
        document.getElementById('info-nama').textContent = selected.getAttribute('data-nama') || '';
        document.getElementById('info-nip').textContent = selected.getAttribute('data-nip') || '';
        document.getElementById('info-pangkat').textContent = selected.getAttribute('data-pangkat') || '';
        document.getElementById('info-jabatan').textContent = selected.getAttribute('data-jabatan') || '';
        document.getElementById('info-unit').textContent = selected.getAttribute('data-unit') || '';
        
        // Set hidden input for pegawai nip
        document.getElementById('pegawai_nip_input').value = nip;
        
        // Clear penilai info first
        document.getElementById('penilai-nama').textContent = '';
        document.getElementById('penilai-nip').textContent = '';
        document.getElementById('penilai-pangkat').textContent = '';
        document.getElementById('penilai-jabatan').textContent = '';
        document.getElementById('penilai-unit').textContent = '';
        
        // Get atasan langsung using AJAX
        if (nip) {
            fetch('get_atasan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'nip=' + encodeURIComponent(nip)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.atasan) {
                    document.getElementById('penilai-nama').textContent = data.atasan.NAMA || '';
                    document.getElementById('penilai-nip').textContent = data.atasan.NIP || '';
                    document.getElementById('penilai-pangkat').textContent = data.atasan.PANGKAT_GOL_RUANG || '';
                    document.getElementById('penilai-jabatan').textContent = data.atasan.JABATAN || '';
                    document.getElementById('penilai-unit').textContent = data.atasan.UNIT_KERJA || '';
                }
            })
            .catch(error => {
                console.error('Error fetching atasan data:', error);
            });
        }
    });
    
    function removeLampiranItem(sectionId, btn) {
        var section = document.getElementById(sectionId);
        var items = section.querySelectorAll('.lampiran-item');
        if (items.length <= 1) {
            alert('Minimal satu baris harus diisi.');
            return;
        }
        var row = btn.closest('.lampiran-item');
        if (row) row.remove();
        updateItemNumbers(sectionId);
    }
    
    function addLampiranItem(sectionId, fieldName) {
        var section = document.getElementById(sectionId);
        var existingItems = section.querySelectorAll('.lampiran-item');
        var itemNumber = existingItems.length + 1;
        
        var newItem = document.createElement('div');
        newItem.className = 'lampiran-item';
        newItem.innerHTML = 
            '<div class="lampiran-simple">' +
                '<span class="lampiran-number-simple">' + itemNumber + '</span>' +
                '<textarea name="' + fieldName + '" class="lampiran-textarea-simple" placeholder="Masukkan deskripsi ' + getCategoryName(sectionId).toLowerCase() + '..." required></textarea>' +
            '</div>' +
            '<div class="lampiran-item-aksi"><button type="button" class="btn-hapus-row" onclick="removeLampiranItem(\'' + sectionId + '\', this)" title="Hapus baris">Hapus</button></div>';
        
        section.appendChild(newItem);
        
        updateItemNumbers(sectionId);
    }
    
    function getCategoryName(sectionId) {
        switch(sectionId) {
            case 'dukungan-section': return 'DUKUNGAN SUMBER DAYA';
            case 'skema-section': return 'SKEMA PERTANGGUNGJAWABAN';
            case 'konsekuensi-section': return 'KONSEKUENSI';
            default: return 'LAMPIRAN';
        }
    }
    
    function updateItemNumbers(sectionId) {
        var section = document.getElementById(sectionId);
        var items = section.querySelectorAll('.lampiran-item');
        items.forEach(function(item, index) {
            // Update simple items (all items now use simple layout)
            var simpleNumberSpan = item.querySelector('.lampiran-number-simple');
            if (simpleNumberSpan) {
                simpleNumberSpan.textContent = index + 1;
            }
        });
    }
    
    function downloadLampiranPDF(tahun, nip) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Generating...';
        button.disabled = true;
        
        // Open PDF generation in new window/tab
        window.open('generate_lampiran_pdf.php?tahun=' + tahun + '&nip=' + nip, '_blank');
        
        // Reset button after a short delay
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }
    </script>
</body>
</html>
