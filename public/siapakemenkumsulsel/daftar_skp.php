<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
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
}

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total SKP (for pagination)
$total_skp = 0;
$count_sql = "SELECT COUNT(DISTINCT id_skp_global) as total FROM skp_pegawai";
$count_result = $conn->query($count_sql);
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_skp = (int)$row['total'];
}

// Fetch SKP data for table display
$skp_sql = "SELECT 
    s.id_skp_global,
    COUNT(*) as total_rows,
    s.NAMA,
    s.NIP,
    s.NAMA_ATASAN_LANGSUNG,
    s.NIP_ATASAN_LANGSUNG,
    s.TRIWULAN,
    s.TAHUN,
    s.STATUS,
    s.TANGGAL_INPUT_SKP,
    p.jabatan,
    p.unit_kerja
FROM skp_pegawai s
LEFT JOIN pegawai p ON s.NIP = p.nip
GROUP BY s.id_skp_global, s.NAMA, s.NIP, s.NAMA_ATASAN_LANGSUNG, s.NIP_ATASAN_LANGSUNG, s.TRIWULAN, s.TAHUN, s.STATUS, s.TANGGAL_INPUT_SKP, p.jabatan, p.unit_kerja
ORDER BY s.TANGGAL_INPUT_SKP DESC
LIMIT $per_page OFFSET $offset";

$skp_result = $conn->query($skp_sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar SKP Pegawai</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #0D2052;
            margin: 0;
            padding: 20px;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
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
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: none;
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

        .data-table tr {
            transition: all 0.3s ease;
        }

        .data-table tr:nth-child(even) {
            background-color: #f0f0f0;
        }

        .data-table tr:hover {
            background: #f0f0f0;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(13, 32, 82, 0.1);
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
            transition: all 0.3s ease;
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

        .view-details-btn {
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            color: white;
            min-width: 140px;
            height: 40px;
            display: inline-block;
        }

        .view-details-btn {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .view-details-btn:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
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

        .back-btn {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
            text-decoration: none;
            color: white;
        }

        @media (max-width: 768px) {
            body {
                padding: 5px;
            }
            
            .main-content {
                padding: 15px;
                margin: 5px;
            }
            
            .page-title {
                font-size: 22px;
            }
            
            .data-table {
                font-size: 12px;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .data-table th,
            .data-table td {
                padding: 10px 8px;
                font-size: 12px;
            }
            
            .view-details-btn {
                padding: 8px 12px;
                font-size: 11px;
                min-width: 100px;
                height: auto;
            }
            
            .back-btn {
                font-size: 12px;
                padding: 8px 16px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 10px;
                margin: 2px;
            }
            
            .page-title {
                font-size: 18px;
            }
            
            .data-table th,
            .data-table td {
                padding: 8px 6px;
                font-size: 11px;
            }
            
            .view-details-btn {
                padding: 6px 10px;
                font-size: 10px;
                min-width: 80px;
            }
            
            .back-btn {
                font-size: 11px;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <a href="index.php" class="back-btn">← Kembali ke Beranda</a>
        <div class="page-title">Daftar SKP Pegawai</div>

        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <strong>✅ Berhasil:</strong> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($skp_result && $skp_result->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Unit Kerja</th>
                        <th>Tanggal Pembuatan SKP</th>
                        <th>Triwulan</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1;
                    while ($row = $skp_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><strong><?= htmlspecialchars($row['NAMA'] ?? 'N/A') ?></strong></td>
                            <td><?= htmlspecialchars($row['NIP'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['jabatan'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['unit_kerja'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['TANGGAL_INPUT_SKP'] ?? 'N/A') ?></td>
                            <td><strong><?= htmlspecialchars($row['TRIWULAN'] ?? 'N/A') ?></strong></td>
                            <td><strong><?= htmlspecialchars($row['TAHUN'] ?? 'N/A') ?></strong></td>
                            <td>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['STATUS'] ?? 'perlu evaluasi')) ?>">
                                    <?= htmlspecialchars($row['STATUS'] ?? 'Perlu Evaluasi') ?>
                                </span>
                            </td>
                            <td>
                                <button class="view-details-btn" onclick="viewSKPDetails(<?= htmlspecialchars($row['id_skp_global']) ?>)">
                                    Lihat Detail
                                </button>
                                <?php if (($row['STATUS'] ?? '') === 'DRAFT' || ($row['STATUS'] ?? '') === 'DRAFT DIKEMBALIKAN'): ?>
                                    <button class="edit-btn" onclick="window.location.href='skpbaru.php?id_skp_global=<?= htmlspecialchars($row['id_skp_global']) ?>'">Edit</button>
                                    <button class="submit-btn" onclick="submitSKP(<?= htmlspecialchars($row['id_skp_global']) ?>)">Submit SKP</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
            <div style="margin-top:24px; text-align:center;">
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
        function viewSKPDetails(idSkpGlobal) {
            // Open SKP details in a new window/tab
            window.open('skp_detail.php?id_skp_global=' + idSkpGlobal, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function submitSKP(idSkpGlobal) {
            if (confirm('Apakah Anda yakin ingin submit SKP ini ke atasan?')) {
                // Submit SKP via AJAX or redirect to a submit handler
                window.location.href = 'submit_skp.php?id_skp_global=' + idSkpGlobal;
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
