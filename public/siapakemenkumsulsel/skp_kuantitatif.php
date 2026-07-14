<?php
// Global Error & Exception Logger
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $msg = "[" . date('Y-m-d H:i:s') . "] Error ($errno): $errstr in $errfile on line $errline\n";
    file_put_contents('debug_errors.txt', $msg, FILE_APPEND);
});
set_exception_handler(function($exception) {
    $msg = "[" . date('Y-m-d H:i:s') . "] Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine() . "\n" . $exception->getTraceAsString() . "\n";
    file_put_contents('debug_errors.txt', $msg, FILE_APPEND);
});

session_start();
date_default_timezone_set('Asia/Makassar');
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get success message if any
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] === 'evaluasi_berhasil') {
    $success_message = 'Evaluasi berhasil disimpan!';
} elseif (isset($_GET['success']) && $_GET['success'] === 'save_berhasil') {
    $success_message = 'Data SKP berhasil disimpan!';
} elseif (isset($_GET['success']) && $_GET['success'] === 'lampiran_saved') {
    $success_message = 'Lampiran SKP berhasil disimpan!';
} elseif (isset($_GET['success']) && $_GET['success'] === 'lampiran_updated') {
    $success_message = 'Lampiran SKP berhasil diperbarui!';
}

// Query 1: Fetch user data for sidebar
$user_nip = $_SESSION['nip'] ?? '';

// Fetch user's unit_kerja for manager view
$user_unit_kerja = '';
$unit_stmt = $conn->prepare("SELECT unit_kerja FROM Pegawai WHERE nip = ?");
$unit_stmt->bind_param('s', $user_nip);
$unit_stmt->execute();
$unit_result = $unit_stmt->get_result();
if ($unit_row = $unit_result->fetch_assoc()) {
    $user_unit_kerja = $unit_row['unit_kerja'];
}
$unit_stmt->close();

$user_data = [
    'nama' => $_SESSION['nama'] ?? 'Nama User',
    'nip' => $_SESSION['nip'] ?? 'NIP User', 
    'jabatan' => $_SESSION['jabatan'] ?? 'Jabatan User',
    'unit_kerja' => $user_unit_kerja
];

// Determine if user is atasan
$is_atasan = (isset($_SESSION['atasan']) && $_SESSION['atasan'] === 'YA');

// Check if manager wants to view employee SKP for evaluation (evaluation mode)
$view_employee_skp = isset($_GET['manager']) && $_GET['manager'] == '1';
$is_eselon = (isset($_SESSION['eselon']) && $_SESSION['eselon'] === 'YA');

// Filter parameters for managers viewing employee SKP
$filter_nama = $_GET['filter_nama'] ?? '';
$filter_tahun = $_GET['filter_tahun'] ?? '';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total SKP for this user or manager (for pagination)
$total_skp = 0;
if (!$is_atasan) {
    // Regular employee - count their own SKP
    $user_nip = $_SESSION['nip'] ?? '';
    $count_sql = "SELECT COUNT(DISTINCT id_skp_global) as total FROM skp_kuantitatif_awal_tahun_pegawai WHERE NIP = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param('s', $user_nip);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    if ($count_result && $row = $count_result->fetch_assoc()) {
        $total_skp = (int)$row['total'];
    }
    $count_stmt->close();
} else {
    // Manager - check if viewing personal SKP or employee SKP
    if ($view_employee_skp) {
        // Manager viewing employee SKP for evaluation (All employees in their unit_kerja or direct subordinates for eselon)
        $manager_name = $_SESSION['nama'] ?? '';
        
        // Build count query with filters - filter by NIP_ATASAN_LANGSUNG for eselon or UNIT_KERJA for non-eselon
        if ($is_eselon) {
            $count_where_conditions = ["s.NIP_ATASAN_LANGSUNG = ?", "s.STATUS IN ('SUBMITTED', 'PROSES EVALUASI', 'SELESAI EVALUASI', 'DISETUJUI')"];
            $count_params = [$user_nip];
        } else {
            $count_where_conditions = ["p.unit_kerja = ?", "s.STATUS IN ('SUBMITTED', 'PROSES EVALUASI', 'SELESAI EVALUASI', 'DISETUJUI')"];
            $count_params = [$user_unit_kerja];
        }
        $count_param_types = 's';
        
        if (!empty($filter_nama)) {
            $count_where_conditions[] = "s.NAMA LIKE ?";
            $count_params[] = '%' . $filter_nama . '%';
            $count_param_types .= 's';
        }
        
        if (!empty($filter_tahun)) {
            $count_where_conditions[] = "s.TAHUN = ?";
            $count_params[] = $filter_tahun;
            $count_param_types .= 'i';
        }
        
        $count_sql = "SELECT COUNT(DISTINCT s.id_skp_global) as total FROM skp_kuantitatif_awal_tahun_pegawai s LEFT JOIN Pegawai p ON s.NIP = p.nip WHERE " . implode(' AND ', $count_where_conditions);
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param($count_param_types, ...$count_params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        if ($count_result && $row = $count_result->fetch_assoc()) {
            $total_skp = (int)$row['total'];
        }
        $count_stmt->close();
    } else {
        // Manager viewing their own personal SKP
        $user_nip = $_SESSION['nip'] ?? '';
        $count_sql = "SELECT COUNT(DISTINCT id_skp_global) as total FROM skp_kuantitatif_awal_tahun_pegawai WHERE NIP = ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param('s', $user_nip);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        if ($count_result && $row = $count_result->fetch_assoc()) {
            $total_skp = (int)$row['total'];
        }
        $count_stmt->close();
    }
}

// Query SKP data for table display (grouped by name and year)
if (!$is_atasan) {
    // Regular employee - show their own SKP
    $user_nip = $_SESSION['nip'] ?? '';
    $skp_sql = "SELECT DISTINCT
        s.ID_SKP_GLOBAL AS id_skp_global,
        s.NAMA,
        s.NIP,
        s.NAMA_ATASAN_LANGSUNG,
        s.NIP_ATASAN_LANGSUNG,
        s.TRIWULAN,
        s.TAHUN,
        s.STATUS,
        s.TANGGAL_INPUT_SKP,
        p.JABATAN AS jabatan,
        p.UNIT_KERJA AS unit_kerja,
        u.ESELON AS ESELON_PEGAWAI
    FROM skp_kuantitatif_awal_tahun_pegawai s
    LEFT JOIN Pegawai p ON s.NIP = p.NIP
    LEFT JOIN user u ON s.NIP = u.NIP
    WHERE s.NIP = ?
    ORDER BY s.NAMA, s.TAHUN DESC, s.TRIWULAN ASC, s.TANGGAL_INPUT_SKP DESC
    LIMIT ? OFFSET ?";
    $skp_stmt = $conn->prepare($skp_sql);
    if (!$skp_stmt) {
        die("Fatal Database Error: Failed to prepare query. " . $conn->error . " | Query: " . $skp_sql);
    }
    $skp_stmt->bind_param('sii', $user_nip, $per_page, $offset);
    $skp_stmt->execute();
    $skp_result = $skp_stmt->get_result();
} else {
    // Manager - check if viewing personal SKP or employee SKP
    if ($view_employee_skp) {
        // Manager viewing employee SKP for evaluation
        $manager_name = $_SESSION['nama'] ?? '';
        
        // Build main query with filters - filter by NIP_ATASAN_LANGSUNG for eselon or UNIT_KERJA for non-eselon
        if ($is_eselon) {
            $main_where_conditions = ["s.NIP_ATASAN_LANGSUNG = ?", "s.STATUS IN ('SUBMITTED', 'PROSES EVALUASI', 'SELESAI EVALUASI', 'DISETUJUI')"];
            $main_params = [$user_nip];
        } else {
            $main_where_conditions = ["p.unit_kerja = ?", "s.STATUS IN ('SUBMITTED', 'PROSES EVALUASI', 'SELESAI EVALUASI', 'DISETUJUI')"];
            $main_params = [$user_unit_kerja];
        }
        $main_param_types = 's';
        
        if (!empty($filter_nama)) {
            $main_where_conditions[] = "s.NAMA LIKE ?";
            $main_params[] = '%' . $filter_nama . '%';
            $main_param_types .= 's';
        }
        
        if (!empty($filter_tahun)) {
            $main_where_conditions[] = "s.TAHUN = ?";
            $main_params[] = $filter_tahun;
            $main_param_types .= 'i';
        }
        
        $skp_sql = "SELECT DISTINCT
            s.ID_SKP_GLOBAL AS id_skp_global,
            s.NAMA,
            s.NIP,
            s.NAMA_ATASAN_LANGSUNG,
            s.NIP_ATASAN_LANGSUNG,
            s.TRIWULAN,
            s.TAHUN,
            s.STATUS,
            s.TANGGAL_INPUT_SKP,
            p.JABATAN AS jabatan,
            p.UNIT_KERJA AS unit_kerja,
            u.ESELON AS ESELON_PEGAWAI
        FROM skp_kuantitatif_awal_tahun_pegawai s
        LEFT JOIN Pegawai p ON s.NIP = p.NIP
        LEFT JOIN user u ON s.NIP = u.NIP
        WHERE " . implode(' AND ', $main_where_conditions) . "
        ORDER BY s.NAMA, s.TAHUN DESC, s.TRIWULAN ASC, s.TANGGAL_INPUT_SKP DESC
        LIMIT ? OFFSET ?";
        
        // Add pagination parameters
        $main_params[] = $per_page;
        $main_params[] = $offset;
        $main_param_types .= 'ii';
        
        $skp_stmt = $conn->prepare($skp_sql);
        if (!$skp_stmt) {
            die("Fatal Database Error: Failed to prepare manager query. " . $conn->error . " | Query: " . $skp_sql);
        }
        $skp_stmt->bind_param($main_param_types, ...$main_params);
        $skp_stmt->execute();
        $skp_result = $skp_stmt->get_result();
    } else {
        // Manager viewing their own personal SKP
        $user_nip = $_SESSION['nip'] ?? '';
        $skp_sql = "SELECT DISTINCT
            s.ID_SKP_GLOBAL AS id_skp_global,
            s.NAMA,
            s.NIP,
            s.NAMA_ATASAN_LANGSUNG,
            s.NIP_ATASAN_LANGSUNG,
            s.TRIWULAN,
            s.TAHUN,
            s.STATUS,
            s.TANGGAL_INPUT_SKP,
            p.JABATAN AS jabatan,
            p.UNIT_KERJA AS unit_kerja,
            u.ESELON AS ESELON_PEGAWAI
        FROM skp_kuantitatif_awal_tahun_pegawai s
        LEFT JOIN Pegawai p ON s.NIP = p.NIP
        LEFT JOIN user u ON s.NIP = u.NIP
        WHERE s.NIP = ?
        ORDER BY s.NAMA, s.TAHUN DESC, s.TRIWULAN ASC, s.TANGGAL_INPUT_SKP DESC
        LIMIT ? OFFSET ?";
        $skp_stmt = $conn->prepare($skp_sql);
        if (!$skp_stmt) {
            die("Fatal Database Error: Failed to prepare personal manager query. " . $conn->error . " | Query: " . $skp_sql);
        }
        $skp_stmt->bind_param('sii', $user_nip, $per_page, $offset);
        $skp_stmt->execute();
        $skp_result = $skp_stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.67, minimum-scale=0.67, maximum-scale=2.0, user-scalable=yes">
    <title>Pengajuan Evaluasi SKP Pegawai</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <?php include 'includes/sidebar_styles.php'; ?>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: #0D2052;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            min-height: 100vh;
            display: flex;
        }

        /* Keep sidebar active state consistent with skpbaru.php */
        .sidebar .nav-item.active {
            background: rgba(255, 255, 255, 0.2) !important;
            color: #fff !important;
            font-weight: 600;
        }

        .main-content {
            background-color: white;
            flex: 1;
            width: 100%;
            min-width: 0;
        }
        .page-title {
            font-size: 24px;
            font-weight: bold;
            color: #0D2052;
            margin-bottom: 30px;
            text-transform: uppercase;
        }

        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
        }

        .data-table th:nth-child(1),
        .data-table td:nth-child(1) { width: 6%; min-width: 44px; }
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) { width: 12%; min-width: 80px; }
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) { width: 18%; min-width: 110px; }
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) { width: 16%; min-width: 120px; }
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) { width: 48%; min-width: 280px; }
        .data-table td:nth-child(5) {
            overflow: hidden;
        }
        .data-table .aksi-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: flex-start;
            min-height: 40px;
        }

        .data-table th {
            background: #0D2052;
            color: white;
            padding: 18px 16px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.8px;
            border: none;
            position: relative;
        }

        .data-table th:first-child {
            border-top-left-radius: 12px;
        }

        .data-table th:last-child {
            border-top-right-radius: 12px;
        }

        .data-table td {
            padding: 16px;
            border: none;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background-color: #f0f0f0;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }

        .data-table tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-perlu-evaluasi {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-telah-dievaluasi {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-selesai-evaluasi {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .view-details-btn, .submit-evaluasi-btn, .download-pdf-btn, .revisi-skp-btn,
        .edit-btn.view-details-btn {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            border: none;
            color: white;
            min-width: 0;
            height: 26px;
            display: inline-block;
            box-shadow: none;
        }

        .view-details-btn {
            background: #007bff;
            margin-left: 0;
        }

        .view-details-btn:hover {
            background: #0056b3;
        }

        .lihat-detail-btn {
            background: #007bff !important;
        }

        .lihat-detail-btn:hover {
            background: #0056b3 !important;
        }

        /* EVALUASI / UBAH (orange) */
        .edit-btn.view-details-btn {
            background: #fd7e14 !important;
            margin-left: 6px;
        }
        .edit-btn.view-details-btn:hover {
            background: #e8590c !important;
        }

        /* UBAH REALISASI / EVALUASI (teal) */
        .edit-btn.view-details-btn[onclick*="editRealisasi"],
        .edit-btn.view-details-btn[onclick*="edit_feedback=1"] {
            background: #17a2b8 !important;
        }
        .edit-btn.view-details-btn[onclick*="editRealisasi"]:hover,
        .edit-btn.view-details-btn[onclick*="edit_feedback=1"]:hover {
            background: #138496 !important;
        }

        /* Hapus (red) */
        .view-details-btn[onclick*="hapusSKP"] {
            background: #dc3545 !important;
            margin-left: 6px;
        }
        .view-details-btn[onclick*="hapusSKP"]:hover {
            background: #c82333 !important;
        }

        .submit-evaluasi-btn {
            background: #28a745;
            margin-left: 6px;
        }

        .submit-evaluasi-btn:hover {
            background: #218838;
        }

        .submit-evaluasi-btn:disabled {
            background: #6c757d !important;
        }

        .download-pdf-btn {
            background: #28a745 !important;
            margin-left: 6px;
        }

        .download-pdf-btn img {
            height: 10px !important;
            vertical-align: middle;
            margin-right: 3px;
        }

        .download-pdf-btn:hover {
            background: #218838 !important;
        }

        .revisi-skp-btn {
            background: #dc3545;
            margin-left: 6px;
        }

        .revisi-skp-btn:hover {
            background: #c82333;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        .hapus-btn {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(220,53,69,0.12);
            transition: background 0.2s;
            margin-left: 6px;
            padding: 0;
        }
        .hapus-btn:hover {
            background: #b52a37;
        }

        .pagination-controls {
            margin-top: 24px;
            text-align: center;
            width: 100%;
        }
        
        /* Grouped data styles */
        .group-header {
            background: #0D2052;
            color: white;
            padding: 15px 20px;
            margin: 20px 0 10px 0;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 2px 8px rgba(13, 32, 82, 0.3);
            transition: all 0.3s ease;
        }
        
        .group-header:hover {
            background: #0D2052;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(13, 32, 82, 0.4);
        }
        
        .group-header .group-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .group-header .group-info {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .group-header .toggle-icon {
            font-size: 18px;
            transition: transform 0.3s ease;
        }
        
        .group-header.collapsed .toggle-icon {
            transform: rotate(-90deg);
        }
        
        .group-content {
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease;
            max-height: 2000px;
            opacity: 1;
        }
        
        .group-content.collapsed {
            max-height: 0;
            opacity: 0;
        }
        
        .grouped-table {
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .grouped-table .data-table {
            margin: 0;
            border-radius: 0;
            box-shadow: none;
        }
        
        .grouped-table .data-table th {
            background: #f0f0f0;
            color: #0D2052;
            border-bottom: 2px solid #0D2052;
        }
        
        .year-badge {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .name-badge {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Two-level grouping styles */
        .name-group {
            margin-bottom: 25px;
        }
        
        .name-header {
            background: #0D2052;
            color: white;
            font-size: 18px;
            padding: 20px 25px;
            margin-bottom: 15px;
        }
        
        .name-header:hover {
            background: #0D2052;
        }
        
        .year-group {
            margin-left: 20px;
            margin-bottom: 15px;
            border-left: 3px solid #0D2052;
            padding-left: 15px;
        }
        
        .year-header {
            background: #0D2052;
            color: white;
            font-size: 16px;
            padding: 15px 20px;
            margin-bottom: 10px;
        }
        
        .year-header:hover {
            background: #0D2052;
        }
        
        .year-group .grouped-table {
            margin: 0;
            box-shadow: none;
        }
        
        .year-group .data-table {
            margin: 0;
            border-radius: 0;
            box-shadow: none;
        }
        
        .year-group .data-table th {
            background: #f0f0f0;
            color: #0D2052;
            border-bottom: 2px solid #0D2052;
            font-size: 13px;
            padding: 12px 16px;
        }
        
        .year-group .data-table td {
            padding: 12px 16px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-title">
            <?php if ($is_atasan && !$view_employee_skp): ?>
                SKP Saya
            <?php elseif ($is_atasan && $view_employee_skp): ?>
                Evaluasi SKP Pegawai
            <?php else: ?>
                Daftar SKP Saya
            <?php endif; ?>
        </div>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <strong>Berhasil:</strong> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($view_employee_skp && $is_atasan): ?>
            <!-- Filter form for managers -->
            <div style="background: #f0f0f0; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #0D2052;">
                <form method="GET" action="" style="display: flex; gap: 15px; align-items: end; flex-wrap: wrap;">
                    <input type="hidden" name="manager" value="1">
                    
                    <div style="display: flex; flex-direction: column; min-width: 200px;">
                        <label for="filter_nama" style="font-weight: 600; margin-bottom: 5px; color: #495057;">Nama Pegawai:</label>
                        <input type="text" name="filter_nama" id="filter_nama" value="<?= htmlspecialchars($filter_nama) ?>" placeholder="Cari nama..." style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                    </div>
                    
                    <div style="display: flex; flex-direction: column; min-width: 150px;">
                        <label for="filter_tahun" style="font-weight: 600; margin-bottom: 5px; color: #495057;">Tahun:</label>
                        <select name="filter_tahun" id="filter_tahun" style="padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px; font-size: 14px;">
                            <option value="">Semua Tahun</option>
                            <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                                <option value="<?= $year ?>" <?= $filter_tahun == $year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 10px; align-items: end;">
                        <button type="submit" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 2px 4px rgba(0,123,255,0.3);">
                            🔍 Filter
                        </button>
                        <a href="?manager=1" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; border: none; border-radius: 4px; padding: 8px 16px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-block; box-shadow: 0 2px 4px rgba(108,117,125,0.3);">
                            🔄 Reset
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($skp_result && $skp_result->num_rows > 0): ?>
            <?php
            // Group data by name first, then by year within each name
                $grouped_data = [];
                while ($row = $skp_result->fetch_assoc()) {
                    $name = $row['NAMA'] ?? 'N/A';
                    $year = $row['TAHUN'] ?? 'N/A';
                    
                    // First level: by name
                    if (!isset($grouped_data[$name])) {
                        $grouped_data[$name] = [
                            'name' => $name,
                            'nip' => $row['NIP'] ?? 'N/A',
                            'jabatan' => $row['jabatan'] ?? 'N/A',
                            'unit_kerja' => $row['unit_kerja'] ?? 'N/A',
                            'years' => []
                        ];
                    }
                    
                    // Second level: by year within name
                    if (!isset($grouped_data[$name]['years'][$year])) {
                        $grouped_data[$name]['years'][$year] = [
                            'year' => $year,
                            'records' => []
                        ];
                    }
                    
                    $grouped_data[$name]['years'][$year]['records'][] = $row;
                }
                ?>
                
                <?php foreach ($grouped_data as $name => $name_group): ?>
                    <!-- Name Level Group -->
                    <div class="grouped-table name-group">
                        <div class="group-header name-header" onclick="toggleGroup('name-<?= htmlspecialchars($name) ?>')">
                            <div class="group-title">
                                <span class="name-badge"><?= htmlspecialchars($name_group['name']) ?></span>
                                <span class="group-info">
                                    <?= htmlspecialchars($name_group['jabatan']) ?> - <?= htmlspecialchars($name_group['unit_kerja']) ?>
                                </span>
                            </div>
                            <span class="toggle-icon">▼</span>
                        </div>
                        
                        <div class="group-content" id="group-name-<?= htmlspecialchars($name) ?>">
                            <?php foreach ($name_group['years'] as $year => $year_group): ?>
                                <!-- Year Level Group -->
                                <div class="grouped-table year-group">
                                    <div class="group-header year-header" onclick="toggleGroup('year-<?= htmlspecialchars($name) ?>-<?= htmlspecialchars($year) ?>')">
                                        <div class="group-title">
                                            <span class="year-badge"><?= htmlspecialchars($year) ?></span>
                                            <span class="group-info">
                                                (<?= count($year_group['records']) ?> SKP)
                                            </span>
                                        </div>
                                        <span class="toggle-icon">▼</span>
                                    </div>
                                    
                                    <div class="group-content" id="group-year-<?= htmlspecialchars($name) ?>-<?= htmlspecialchars($year) ?>">
                                        <table class="data-table">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Periode</th>
                                                    <th>Tanggal Pembuatan</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $counter = 1;
                                                foreach ($year_group['records'] as $row): ?>
                                                    <tr>
                                                        <td><?= $counter++ ?></td>
                                                        <td><strong>Tahunan <?= htmlspecialchars($row['TAHUN'] ?? 'N/A') ?></strong></td>
                                                        <td><?= htmlspecialchars($row['TANGGAL_INPUT_SKP'] ?? 'N/A') ?></td>
                                                        <td>
                                                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['STATUS'] ?? 'perlu evaluasi')) ?>">
                                                                <?= htmlspecialchars($row['STATUS'] ?? 'Perlu Evaluasi') ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="aksi-buttons">
                                                                <button class="view-details-btn lihat-detail-btn" onclick="viewSKPDetails(<?= htmlspecialchars($row['id_skp_global']) ?>, '<?= htmlspecialchars($row['ESELON_PEGAWAI'] ?? '') ?>')">Lihat Detail</button>
                                                                
                                                                <?php if ($view_employee_skp && $is_atasan): ?>
                                                                    <!-- Manager View: Can approve/reject AND manage drafts -->
                                                                    
                                                                    <!-- Approval Actions -->
                                                                    <?php if (($row['STATUS'] ?? '') === 'DISETUJUI' || ($row['STATUS'] ?? '') === 'SELESAI EVALUASI'): ?>
                                                                        <button class="download-pdf-btn" onclick="downloadKuantitatifAwalTahunPDF(<?= htmlspecialchars($row['id_skp_global']) ?>, '<?= htmlspecialchars($row['ESELON_PEGAWAI'] ?? '') ?>')" style="background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); box-shadow: 0 2px 8px rgba(253, 126, 20, 0.3);"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Dokumen Kuantitatif</button>
                                                                        <button class="submit-evaluasi-btn" disabled style="background: #28a745; cursor: not-allowed; opacity: 0.8;">✓ Disetujui</button>
                                                                    <?php elseif (($row['STATUS'] ?? '') === 'SUBMITTED' || ($row['STATUS'] ?? '') === 'PROSES EVALUASI'): ?>
                                                                        <button class="submit-evaluasi-btn" onclick="approveKuantitatif(<?= htmlspecialchars($row['id_skp_global']) ?>)" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); box-shadow: 0 2px 8px rgba(40,167,69,0.3);">✓ Setujui</button>
                                                                        <button class="revisi-skp-btn" onclick="tolakKuantitatif(<?= htmlspecialchars($row['id_skp_global']) ?>)">✗ Tolak</button>
                                                                    <?php endif; ?>

                                                                    <!-- Employee Management Actions (If Manager wants to manage) -->
                                                                    <?php if (strtoupper($row['STATUS'] ?? '') === 'DRAFT' || strtoupper($row['STATUS'] ?? '') === 'DITOLAK'): ?>
                                                                        <button class="edit-btn view-details-btn" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3); color: white;" onclick="window.location.href='<?= (isset($row['ESELON_PEGAWAI']) && $row['ESELON_PEGAWAI'] === 'YA') ? 'edit_skp_kuantitatif_eselon.php' : 'edit_skp_kuantitatif.php' ?>?id_skp_global=<?= htmlspecialchars($row['id_skp_global']) ?>'">UBAH</button>
                                                                        <button class="submit-evaluasi-btn" onclick="submitSKP(<?= htmlspecialchars($row['id_skp_global']) ?>)">Submit SKP</button>
                                                                        <button class="view-details-btn" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3); color: white;" onclick="hapusSKP(<?= htmlspecialchars($row['id_skp_global']) ?>)">Hapus</button>
                                                                    <?php endif; ?>

                                                                    <?php if (!in_array(strtoupper($row['STATUS'] ?? ''), ['DISETUJUI', 'SELESAI EVALUASI', 'SUBMITTED', 'PROSES EVALUASI', 'DRAFT', 'DITOLAK'])): ?>
                                                                        <button class="submit-evaluasi-btn" disabled style="background: #6c757d; cursor: not-allowed; opacity: 0.6;">⏳ Menunggu</button>
                                                                    <?php endif; ?>

                                                                <?php else: ?>
                                                                    <!-- Employee personal view -->
                                                                    <?php if (strtoupper($row['STATUS'] ?? '') === 'DRAFT' || strtoupper($row['STATUS'] ?? '') === 'DITOLAK'): ?>
                                                                        <button class="edit-btn view-details-btn" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3); color: white;" onclick="window.location.href='<?= $is_eselon ? 'edit_skp_kuantitatif_eselon.php' : 'edit_skp_kuantitatif.php' ?>?id_skp_global=<?= htmlspecialchars($row['id_skp_global']) ?>'">UBAH</button>
                                                                        <button class="submit-evaluasi-btn" onclick="submitSKP(<?= htmlspecialchars($row['id_skp_global']) ?>)">Submit SKP</button>
                                                                        <button class="view-details-btn" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3); color: white;" onclick="hapusSKP(<?= htmlspecialchars($row['id_skp_global']) ?>)">Hapus</button>
                                                                    <?php elseif (strtoupper($row['STATUS'] ?? '') === 'SUBMITTED'): ?>
                                                                        <button class="submit-evaluasi-btn" disabled style="background: #17a2b8; cursor: not-allowed; opacity: 0.8;">⏳ Menunggu Persetujuan</button>
                                                                    <?php elseif (strtoupper($row['STATUS'] ?? '') === 'DISETUJUI'): ?>
                                                                        <button class="download-pdf-btn" onclick="downloadKuantitatifAwalTahunPDF(<?= htmlspecialchars($row['id_skp_global']) ?>, '<?= htmlspecialchars($row['ESELON_PEGAWAI'] ?? '') ?>')"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Dokumen Kuantitatif</button>
                                                                        <button class="submit-evaluasi-btn" disabled style="background: #28a745; cursor: not-allowed; opacity: 0.8;">✓ Disetujui</button>
                                                                    <?php else: ?>
                                                                        <button class="submit-evaluasi-btn" disabled style="background: #6c757d; cursor: not-allowed; opacity: 0.6;">Menunggu</button>
                                                                    <?php endif; ?>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">
                    <h3>📭 Belum Ada Data SKP</h3>
                    <p>Belum ada data SKP yang dikumpulkan. Silakan buat SKP baru terlebih dahulu.</p>
                </div>
            <?php endif; ?>

        <?php if ($total_skp > $per_page): ?>
            <?php
            $total_pages = ceil($total_skp / $per_page);
            $base_url = strtok($_SERVER['REQUEST_URI'], '?');
            $query = $_GET;
            ?>
            <div class="pagination-controls">
                <?php if ($page > 1): ?>
                    <?php $query['page'] = $page - 1; ?>
                    <a href="<?= $base_url . '?' . http_build_query($query) ?>" style="margin:0 8px;">&laquo; Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php $query['page'] = $i; ?>
                    <a href="<?= $base_url . '?' . http_build_query($query) ?>" style="margin:0 4px;<?= $i == $page ? 'font-weight:bold;text-decoration:underline;' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <?php $query['page'] = $page + 1; ?>
                    <a href="<?= $base_url . '?' . http_build_query($query) ?>" style="margin:0 8px;">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function viewSKPDetails(idSkpGlobal, eselon) {
            // Open SKP details in a new window/tab
            var detailPage = (eselon === 'YA') ? "skp_detail_kuantitatif_eselon.php" : "skp_detail_kuantitatif.php";
            window.open(detailPage + '?id_skp_global=' + idSkpGlobal, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function editRealisasi(idSkpGlobal) {
            // Open edit realisasi page for SUBMITTED status
            window.open('edit_realisasi.php?id_skp_global=' + idSkpGlobal, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function downloadKuantitatifAwalTahunPDF(idSkpGlobal, eselon) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            const path = (eselon === 'YA') ? 'skp_eselon/generate_kuantitatif_awal_tahun_pdf.php' : 'generate_kuantitatif_awal_tahun_pdf.php';
            window.open(path + '?id_skp_global=' + idSkpGlobal, '_blank');
            setTimeout(() => { button.innerHTML = originalText; button.disabled = false; }, 2000);
        }

        function approveKuantitatif(idSkpGlobal) {
            if (!confirm('Apakah Anda yakin ingin menyetujui SKP Kuantitatif ini?')) return;
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Menyetujui...'; button.disabled = true;
            fetch('approve_skp_kuantitatif.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) { alert('SKP Kuantitatif berhasil disetujui!'); location.reload(); }
                else { alert('Gagal: ' + data.message); button.innerHTML = originalText; button.disabled = false; }
            })
            .catch(() => { alert('Terjadi kesalahan.'); button.innerHTML = originalText; button.disabled = false; });
        }

        function tolakKuantitatif(idSkpGlobal) {
            if (!confirm('Apakah Anda yakin ingin menolak SKP Kuantitatif ini? Pegawai dapat mengajukan ulang.')) return;
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Menolak...'; button.disabled = true;
            fetch('tolak_skp_kuantitatif.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) { alert('SKP Kuantitatif telah ditolak.'); location.reload(); }
                else { alert('Gagal: ' + data.message); button.innerHTML = originalText; button.disabled = false; }
            })
            .catch(() => { alert('Terjadi kesalahan.'); button.innerHTML = originalText; button.disabled = false; });
        }
        
        function submitSKPFinal(idSkpGlobal) {
            if (confirm('Apakah Anda yakin ingin mengirim SKP ini ke atasan untuk evaluasi?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
                // Submit SKP via AJAX
                fetch('submit_skp_final_kuantitatif.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('SKP berhasil dikirim ke atasan!\nStatus SKP telah diubah menjadi "PROSES EVALUASI".');
                        location.reload(); // Refresh page to show updated status
                    } else {
                        alert('Gagal mengirim SKP: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengirim SKP. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function submitSKP(idSkpGlobal) {
            if (confirm('Apakah Anda yakin ingin submit SKP ini ke atasan untuk evaluasi?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
                // Submit SKP via AJAX
                fetch('submit_skp_kuantitatif.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('SKP berhasil disubmit!\nStatus SKP telah diubah menjadi "PROSES EVALUASI".');
                        location.reload(); // Refresh page to show updated status
                    } else {
                        alert('Gagal submit SKP: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat submit SKP. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function submitEvaluasi(idSkpGlobal) {
            if (confirm('Apakah Anda yakin ingin submit evaluasi untuk SKP Global ID: ' + idSkpGlobal + '?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
                // Submit evaluation via AJAX
                fetch('submit_evaluasi_kuantitatif.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Evaluasi berhasil disubmit!\nStatus SKP telah diubah menjadi "SELESAI EVALUASI".');
                        location.reload(); // Refresh page to show updated status
                    } else {
                        alert('Gagal submit evaluasi: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat submit evaluasi. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function downloadPDF(idSkpGlobal, eselon) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            
            // Open PDF generation in new window/tab
            const path = (eselon === 'YA') ? 'skp_eselon/generate_pdf.php' : 'generate_pdf_kuantitatif.php';
            window.open(path + '?id_skp_global=' + idSkpGlobal, '_blank');
            
            // Reset button after a short delay
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }
        
        function downloadKuantitatifPDF(idSkpGlobal, eselon) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            
            // Open kuantitatif PDF generation in new window/tab (quarterly SKP)
            const path = (eselon === 'YA') ? 'skp_eselon/generate_kuantitatif_quarterly_pdf.php' : 'generate_kuantitatif_quarterly_pdf.php';
            window.open(path + '?id_skp_global=' + idSkpGlobal, '_blank');
            
            // Reset button after a short delay
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }
        
        function downloadUmpanBalikPDF(idSkpGlobal, eselon) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            
            // Open umpan balik PDF generation in new window/tab (quarterly SKP)
            const path = (eselon === 'YA') ? 'skp_eselon/generate_umpan_balik_quarterly_pdf_kuantitatif.php' : 'generate_umpan_balik_quarterly_pdf_kuantitatif.php';
            window.open(path + '?id_skp_global=' + idSkpGlobal, '_blank');
            
            // Reset button after a short delay
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }

        function downloadDokumenEvaluasiTriwulanPDF(idSkpGlobal, eselon) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            
            // Open dokumen evaluasi triwulan PDF generation in new window/tab
            const path = (eselon === 'YA') ? 'skp_eselon/generate_dokumen_evaluasi_triwulan_pdf.php' : 'generate_dokumen_evaluasi_triwulan_pdf_kuantitatif.php';
            window.open(path + '?id_skp_global=' + idSkpGlobal, '_blank');
            
            // Reset button after a short delay
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }
        
        function revisiSKP(idSkpGlobal) {
            if (confirm('Apakah Anda yakin ingin mengembalikan SKP ini ke status DRAFT untuk direvisi oleh pegawai?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
                // Submit revisi via AJAX
                fetch('revisi_skp_kuantitatif.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('SKP berhasil dikembalikan ke status DRAFT!\nPegawai dapat melakukan revisi pada SKP ini.');
                        location.reload(); // Refresh page to show updated status
                    } else {
                        alert('Gagal mengembalikan SKP: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengembalikan SKP. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function hapusSKP(idSkpGlobal) {
            if (confirm('Apakah Anda yakin ingin menghapus SKP ini? Semua data terkait akan dihapus secara permanen.')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Menghapus...';
                button.disabled = true;
                fetch('hapus_skp_kuantitatif.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('SKP berhasil dihapus!');
                        location.reload();
                    } else {
                        alert('Gagal menghapus SKP: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus SKP. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function toggleGroup(groupKey) {
            const groupContent = document.getElementById('group-' + groupKey);
            const groupHeader = groupContent.previousElementSibling;
            const toggleIcon = groupHeader.querySelector('.toggle-icon');
            
            if (groupContent.classList.contains('collapsed')) {
                groupContent.classList.remove('collapsed');
                groupHeader.classList.remove('collapsed');
                toggleIcon.textContent = '▼';
            } else {
                groupContent.classList.add('collapsed');
                groupHeader.classList.add('collapsed');
                toggleIcon.textContent = '▶';
            }
        }
        
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success message after 5 seconds
            const successMessage = document.querySelector('[style*="background:#d4edda"]');
            if (successMessage) {
                setTimeout(() => {
                    successMessage.style.transition = 'opacity 0.5s ease';
                    successMessage.style.opacity = '0';
                    setTimeout(() => {
                        successMessage.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
