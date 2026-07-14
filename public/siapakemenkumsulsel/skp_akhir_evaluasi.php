<?php
session_start();

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

// Get user info
$user_nip = $_SESSION['nip'] ?? '';
$user_name = $_SESSION['nama'] ?? '';
$user_jabatan = '';

// Check if user is manager and get their job title
$is_atasan = false;
if ($user_nip) {
    $user_sql = "SELECT ATASAN FROM user WHERE NIP = ? LIMIT 1";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param('s', $user_nip);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_result && $user_result->num_rows > 0) {
        $user_row = $user_result->fetch_assoc();
        $is_atasan = strtoupper($user_row['ATASAN'] ?? '') === 'YA';
    }
    $user_stmt->close();
    
    // Get user's job title from Pegawai table
    if ($is_atasan) {
        $jabatan_sql = "SELECT JABATAN FROM Pegawai WHERE NIP = ? LIMIT 1";
        $jabatan_stmt = $conn->prepare($jabatan_sql);
        $jabatan_stmt->bind_param('s', $user_nip);
        $jabatan_stmt->execute();
        $jabatan_result = $jabatan_stmt->get_result();
        if ($jabatan_result && $jabatan_result->num_rows > 0) {
            $jabatan_row = $jabatan_result->fetch_assoc();
            $user_jabatan = $jabatan_row['JABATAN'] ?? '';
        }
        $jabatan_stmt->close();
    }
}

// Redirect if not manager
if (!$is_atasan) {
    header("Location: skp_akhir.php");
    exit();
}


// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get filter parameters
$filter_tahun = $_GET['tahun'] ?? '';

// Build query for SKP Akhir that need evaluation
$where_conditions = [];
$params = [];
$param_types = '';

// Only show SKP Akhir where the manager's job title matches the ATASAN_LANGSUNG field
// We need to join with Pegawai table to get the ATASAN_LANGSUNG field
if (!empty($user_jabatan)) {
    $where_conditions[] = "EXISTS (
        SELECT 1 FROM Pegawai p 
        WHERE p.NIP = skp_akhir_pegawai.NIP 
        AND p.ATASAN_LANGSUNG = ?
    )";
    $params[] = $user_jabatan;
    $param_types .= 's';
}

// Only show SKP Akhir that are in PROSES EVALUASI or SELESAI EVALUASI status
$where_conditions[] = "(STATUS = 'PROSES EVALUASI' OR STATUS = 'SELESAI EVALUASI')";

if (!empty($filter_tahun)) {
    $where_conditions[] = "TAHUN = ?";
    $params[] = $filter_tahun;
    $param_types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count query - Group by employee to count unique employees
$count_sql = "SELECT COUNT(*) as total FROM (
    SELECT NAMA, NIP, TAHUN 
    FROM skp_akhir_pegawai $where_clause 
    GROUP BY NAMA, NIP, TAHUN
) as grouped_data";
$count_stmt = $conn->prepare($count_sql);
if ($count_stmt && !empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
if ($count_stmt) {
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'] ?? 0;
    $count_stmt->close();
} else {
    $total_records = 0;
}

$total_pages = ceil($total_records / $per_page);

// Main query - Group by employee to show one row per employee
$main_sql = "SELECT 
    MIN(ID_SKP) as ID_SKP,
    NAMA,
    NIP,
    TAHUN,
    MIN(STATUS) as STATUS,
    MIN(TANGGAL_EVALUASI_SKP) as TANGGAL_EVALUASI_SKP,
    SUM(TARGET) as TARGET,
    SUM(REALISASI_BERDASARKAN_BUKTI_DUKUNG) as REALISASI_BERDASARKAN_BUKTI_DUKUNG,
    MIN(ID_SKP_GLOBAL) as ID_SKP_GLOBAL
FROM skp_akhir_pegawai $where_clause 
GROUP BY NAMA, NIP, TAHUN 
ORDER BY TANGGAL_EVALUASI_SKP ASC, NAMA ASC 
LIMIT $per_page OFFSET $offset";
$main_stmt = $conn->prepare($main_sql);
if ($main_stmt && !empty($params)) {
    $main_stmt->bind_param($param_types, ...$params);
}
if ($main_stmt) {
    $main_stmt->execute();
    $skp_result = $main_stmt->get_result();
    $main_stmt->close();
} else {
    $skp_result = false;
}

// Get available years for filters
$years_sql = "SELECT DISTINCT TAHUN FROM skp_akhir_pegawai WHERE (STATUS = 'PROSES EVALUASI' OR STATUS = 'SELESAI EVALUASI') ORDER BY TAHUN DESC";
$years_result = $conn->query($years_sql);
$available_years = [];
if ($years_result) {
    while ($year_row = $years_result->fetch_assoc()) {
        $available_years[] = $year_row['TAHUN'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.67, minimum-scale=0.67, maximum-scale=2.0, user-scalable=yes">
    <title>Evaluasi SKP Akhir - Sistem Kinerja Pegawai</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <?php include 'includes/sidebar_styles.php'; ?>
    <style>
        
        .page-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        .filter-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 9px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-weight: 600;
            height: 26px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
            box-shadow: none;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
            box-shadow: none;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
            box-shadow: none;
        }
        
        .btn-warning:hover {
            background: #e0a800;
        }
        
        .evaluate-btn, .view-details-btn, .submit-evaluasi-btn, .download-pdf-btn {
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

        .evaluate-btn {
            background: #ffc107;
            color: #212529;
            margin-left: 0;
        }

        .evaluate-btn:hover {
            background: #e0a800;
        }

        .view-details-btn {
            background: #007bff;
            margin-left: 6px;
        }

        .view-details-btn:hover {
            background: #0056b3;
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
        .data-table td:nth-child(1) { width: 3%; min-width: 36px; }
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) { width: 14%; }
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) { width: 11%; }
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) { width: 5%; min-width: 52px; }
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) { width: 12%; min-width: 100px; }
        .data-table th:nth-child(6),
        .data-table td:nth-child(6) { width: 6%; min-width: 56px; }
        .data-table th:nth-child(7),
        .data-table td:nth-child(7) { width: 6%; min-width: 56px; }
        .data-table th:nth-child(8),
        .data-table td:nth-child(8) { width: 43%; min-width: 380px; }
        .data-table td:nth-child(8) {
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

        .main-content {
            background-color: white;
        }
        .page-title {
            color: #0D2052;
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
            padding: 10px 8px;
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
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-proses-evaluasi {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-selesai-evaluasi {
            background: #d4edda;
            color: #155724;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        
        .pagination a {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            color: #007bff;
            text-decoration: none;
            border-radius: 4px;
        }
        
        .pagination a:hover {
            background: #e9ecef;
        }
        
        .pagination .current {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        
        .pagination .disabled {
            color: #6c757d;
            cursor: not-allowed;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }


        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
            .user-profile {
                margin-bottom: 0;
            }
            .main-content {
                border-left: 2px solid #0D2052;
                border-top: none;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="page-title">Evaluasi SKP Akhir Pegawai</div>
        
        <?php if (isset($_GET['success']) && $_GET['success'] === 'evaluasi_berhasil'): ?>
            <div class="success-message">
                <strong>✅ Berhasil:</strong> SKP Akhir berhasil dievaluasi! Status telah diubah menjadi 'SELESAI EVALUASI'.
            </div>
        <?php endif; ?>
        
        <div class="filter-section">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="tahun">Tahun:</label>
                        <select name="tahun" id="tahun">
                            <option value="">Semua Tahun</option>
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?= $year ?>" <?= $filter_tahun == $year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-buttons">
                        <button type="submit" class="btn btn-primary">🔍 Filter</button>
                        <a href="skp_akhir_evaluasi.php" class="btn btn-primary">🔄 Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($skp_result && $skp_result->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pegawai</th>
                        <th>NIP</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th>Target</th>
                        <th>Realisasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1 + $offset;
                    while ($row = $skp_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><strong><?= htmlspecialchars($row['NAMA'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($row['NIP'] ?? 'N/A') ?></td>
                            <td><strong><?= htmlspecialchars($row['TAHUN'] ?? 'N/A') ?></strong></td>
                            <td>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['STATUS'] ?? 'PROSES EVALUASI')) ?>">
                                    <?= htmlspecialchars($row['STATUS'] ?? 'PROSES EVALUASI') ?>
                                </span>
                            </td>
                            <td><?= number_format($row['TARGET'] ?? 0) ?></td>
                            <td><?= number_format($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? 0) ?></td>
                            <td>
                                <div class="aksi-buttons">
                                    <?php if ($row['STATUS'] === 'PROSES EVALUASI'): ?>
                                        <button class="evaluate-btn" onclick="evaluateSKPAkhir(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)">
                                            Evaluasi
                                        </button>
                                    <?php elseif ($row['STATUS'] === 'SELESAI EVALUASI'): ?>
                                        <button class="download-pdf-btn" onclick="downloadSKPAkhirPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Evaluasi Kuantitatif Akhir</button>
                                        <button class="download-pdf-btn" onclick="downloadEvaluasiKuantitatifPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)" style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); box-shadow: 0 2px 8px rgba(32, 201, 151, 0.3);"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Umpan Balik Kuantitatif Akhir</button>
                                        <button class="download-pdf-btn" onclick="downloadKuantitatifPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)" style="background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); box-shadow: 0 2px 8px rgba(253, 126, 20, 0.3);"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Kuantitatif</button>
                                        <button class="download-pdf-btn" onclick="downloadDokumenEvaluasiPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); box-shadow: 0 2px 8px rgba(111, 66, 193, 0.3);"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Dokumen Evaluasi</button>
                                        <button class="submit-evaluasi-btn" disabled style="background: #6c757d; cursor: not-allowed; opacity: 0.6;">🔒 Selesai Evaluasi</button>
                                    <?php endif; ?>
                                    <button class="view-details-btn" onclick="viewSKPAkhirDetails(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)">
                                        Lihat Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">« Sebelumnya</a>
                    <?php else: ?>
                        <span class="disabled">« Sebelumnya</span>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Selanjutnya »</a>
                    <?php else: ?>
                        <span class="disabled">Selanjutnya »</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-data">
                <h3>📭 Tidak Ada Data</h3>
                <p>Tidak ada SKP Akhir yang menunggu evaluasi.</p>
            </div>
        <?php endif; ?>
    </div>
    
    
    <script>
        function evaluateSKPAkhir(idSkpGlobal) {
            // Open SKP Akhir evaluation in a new window/tab using skp_detail.php with edit_feedback parameter
            window.open('skp/skp_detail.php?id_skp_global=' + idSkpGlobal + '&skp_akhir=1&edit_feedback=1', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function viewSKPAkhirDetails(idSkpGlobal) {
            window.open('skp/skp_detail.php?id_skp_global=' + idSkpGlobal + '&skp_akhir=1', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function downloadSKPAkhirPDF(idSkpGlobal) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            
            // Open PDF generation in new window/tab
            window.open('skp/generate_skp_akhir_pdf.php?id_skp_global=' + idSkpGlobal, '_blank');
            
            // Reset button after a short delay
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }
        
        function downloadEvaluasiKuantitatifPDF(idSkpGlobal) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            window.open('generate_umpan_balik_pdf.php?id_skp_global=' + idSkpGlobal, '_blank');
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }

        function downloadKuantitatifPDF(idSkpGlobal) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            window.open('generate_kuantitatif_pdf.php?id_skp_global=' + idSkpGlobal, '_blank');
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }

        function downloadDokumenEvaluasiPDF(idSkpGlobal) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            window.open('generate_dokumen_evaluasi_pdf.php?id_skp_global=' + idSkpGlobal, '_blank');
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 2000);
        }
        
        // Auto-hide messages
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.success-message, .error-message');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s ease';
                    message.style.opacity = '0';
                    setTimeout(() => {
                        message.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
