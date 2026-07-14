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

// Get user data for sidebar
$user_data = [
    'nama' => $_SESSION['nama'] ?? 'Nama User',
    'nip' => $_SESSION['nip'] ?? 'NIP User', 
    'jabatan' => $_SESSION['jabatan'] ?? 'Jabatan User'
];

// Check if user is manager/atasan - check database for ATASAN='YA'
$user_nip = $_SESSION['nip'] ?? '';
$is_atasan = false;
if ($user_nip) {
    $atasan_sql = "SELECT ATASAN FROM user WHERE nip = ?";
    $atasan_stmt = $conn->prepare($atasan_sql);
    $atasan_stmt->bind_param('s', $user_nip);
    $atasan_stmt->execute();
    $atasan_result = $atasan_stmt->get_result();
    if ($atasan_result && $row = $atasan_result->fetch_assoc()) {
        $is_atasan = strtoupper($row['ATASAN'] ?? '') === 'YA';
    }
    $atasan_stmt->close();
}
$manager_name = $_SESSION['nama'] ?? '';

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total lampiran for evaluation (all statuses)
$count_sql = "SELECT COUNT(DISTINCT CONCAT(nip, '-', tahun)) as total 
              FROM skp_lampiran 
              WHERE nama_atasan_langsung = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param('s', $manager_name);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_lampiran = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_lampiran = (int)$row['total'];
}
$count_stmt->close();

// Query lampiran data for evaluation (all statuses)
$lampiran_sql = "SELECT DISTINCT 
    nip,
    nama,
    tahun,
    MIN(tanggal_input) as tanggal_input,
    COUNT(*) as total_entries,
    status
    FROM skp_lampiran 
    WHERE nama_atasan_langsung = ?
    GROUP BY nip, nama, tahun, status
    ORDER BY tahun DESC, nama ASC
    LIMIT ? OFFSET ?";
$lampiran_stmt = $conn->prepare($lampiran_sql);
$lampiran_stmt->bind_param('sii', $manager_name, $per_page, $offset);
$lampiran_stmt->execute();
$lampiran_result = $lampiran_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.67, minimum-scale=0.67, maximum-scale=2.0, user-scalable=yes">
    <title>Evaluasi Lampiran SKP - SI-APA</title>
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

        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
        }

        .data-table .aksi-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            justify-content: flex-start;
            min-height: 40px;
        }

        .view-details-btn, .submit-evaluasi-btn, .download-pdf-btn, .revisi-skp-btn {
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
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none;
        }

        .view-details-btn {
            background: #007bff;
            margin-left: 0;
        }

        .view-details-btn:hover {
            background: #0056b3;
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
            cursor: not-allowed;
            opacity: 0.6;
        }

        .download-pdf-btn {
            background: #28a745;
            margin-left: 6px;
        }

        .download-pdf-btn img {
            height: 10px !important;
            vertical-align: middle;
            margin-right: 3px;
        }

        .download-pdf-btn:hover {
            background: #218838;
        }

        .revisi-skp-btn {
            background: #dc3545;
            margin-left: 6px;
        }

        .revisi-skp-btn:hover {
            background: #c82333;
        }

        .pagination-controls {
            margin-top: 24px;
            text-align: center;
            width: 100%;
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
        <div class="page-title">Evaluasi Lampiran SKP</div>

        <?php if (!$is_atasan): ?>
            <div class="no-data">
                <h3>🚫 Akses Ditolak</h3>
                <p>Halaman ini hanya dapat diakses oleh manager/atasan.</p>
            </div>
        <?php elseif ($lampiran_result && $lampiran_result->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Pegawai</th>
                        <th>Tahun</th>
                        <th>Total Entri</th>
                        <th>Status</th>
                        <th>Tanggal Submit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1 + $offset;
                    while ($row = $lampiran_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><strong><?= htmlspecialchars($row['nama'] ?? 'N/A') ?></strong></td>
                            <td><strong><?= htmlspecialchars($row['tahun'] ?? 'N/A') ?></strong></td>
                            <td>
                                <span class="status-badge status-perlu-evaluasi">
                                    <?= htmlspecialchars($row['total_entries'] ?? '0') ?> Entri
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['status'] ?? 'perlu evaluasi')) ?>">
                                    <?= htmlspecialchars($row['status'] ?? 'Perlu Evaluasi') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['tanggal_input'] ?? 'N/A') ?></td>
                            <td>
                                <div class="aksi-buttons">
                                    <button class="view-details-btn" onclick="viewLampiranDetails('<?= htmlspecialchars($row['tahun']) ?>', '<?= htmlspecialchars($row['nip']) ?>')">Lihat Detail</button>
                                    <?php if (strtoupper($row['status'] ?? '') === 'PROSES EVALUASI'): ?>
                                        <button class="submit-evaluasi-btn" onclick="approveLampiran('<?= htmlspecialchars($row['tahun']) ?>', '<?= htmlspecialchars($row['nip']) ?>')">Setujui</button>
                                        <button class="revisi-skp-btn" onclick="rejectLampiran('<?= htmlspecialchars($row['tahun']) ?>', '<?= htmlspecialchars($row['nip']) ?>')">Revisi</button>
                                    <?php elseif (strtoupper($row['status'] ?? '') === 'SELESAI EVALUASI'): ?>
                                        <button class="download-pdf-btn" onclick="downloadLampiranPDF('<?= htmlspecialchars($row['tahun']) ?>', '<?= htmlspecialchars($row['nip']) ?>')"><img src="images/pdf.png" alt="PDF"> LAMPIRAN SKP</button>
                                        <button class="submit-evaluasi-btn" disabled>✅ Selesai</button>
                                    <?php elseif (strtoupper($row['status'] ?? '') === 'DRAFT DIKEMBALIKAN'): ?>
                                        <button class="submit-evaluasi-btn" disabled>⏳ Dikembalikan</button>
                                    <?php else: ?>
                                        <button class="submit-evaluasi-btn" disabled>⏳ Menunggu</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>📭 Belum Ada Lampiran SKP</h3>
                <p>Belum ada lampiran SKP yang terkait dengan Anda.</p>
            </div>
        <?php endif; ?>

        <?php if ($total_lampiran > $per_page): ?>
            <?php
            $total_pages = ceil($total_lampiran / $per_page);
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
        function viewLampiranDetails(tahun, nip) {
            // Open lampiran details in a new window/tab (view-only mode)
            window.open('skp_lampiran.php?tahun=' + tahun + '&nip=' + nip + '&view=1', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function approveLampiran(tahun, nip) {
            if (confirm('Apakah Anda yakin ingin menyetujui lampiran SKP untuk tahun ' + tahun + '?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Memproses...';
                button.disabled = true;
                
                // Submit approval via AJAX
                fetch('approve_lampiran.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'tahun=' + encodeURIComponent(tahun) + '&nip=' + encodeURIComponent(nip)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Lampiran SKP berhasil disetujui!');
                        location.reload();
                    } else {
                        alert('❌ Gagal menyetujui lampiran: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan saat menyetujui lampiran. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function rejectLampiran(tahun, nip) {
            if (confirm('Apakah Anda yakin ingin merevisi lampiran SKP untuk tahun ' + tahun + '? Lampiran akan dikembalikan ke status DRAFT untuk diperbaiki.')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Memproses...';
                button.disabled = true;
                
                // Submit rejection via AJAX
                fetch('reject_lampiran.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'tahun=' + encodeURIComponent(tahun) + '&nip=' + encodeURIComponent(nip)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Lampiran SKP berhasil dikembalikan untuk revisi dan dikembalikan ke status DRAFT!');
                        location.reload();
                    } else {
                        alert('❌ Gagal mengembalikan lampiran untuk revisi: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan saat mengembalikan lampiran untuk revisi. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
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

<?php
$conn->close();
?>
