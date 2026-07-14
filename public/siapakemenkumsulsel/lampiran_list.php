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
    die("Connection failed: " . $e->getMessage());
}

// Get user data for sidebar
$user_data = [
    'nama' => $_SESSION['nama'] ?? 'Nama User',
    'nip' => $_SESSION['nip'] ?? 'NIP User', 
    'jabatan' => $_SESSION['jabatan'] ?? 'Jabatan User'
];

// Check if user is atasan (supervisor) - only show evaluasi menu if ATASAN='YA' (is manager)
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

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $page > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Count total distinct years for this user and their employees (if manager)
$user_nip = $_SESSION['nip'] ?? '';
$manager_name = $_SESSION['nama'] ?? '';

// Both manager and regular employee: count only own lampiran
$count_sql = "SELECT COUNT(DISTINCT tahun) as total FROM skp_lampiran WHERE nip = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param('s', $user_nip);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_lampiran = 0;
if ($count_result && $row = $count_result->fetch_assoc()) {
    $total_lampiran = (int)$row['total'];
}
$count_stmt->close();

// Query lampiran data for table display - distinct by year with status
// Both manager and regular employee: show only own lampiran
$lampiran_sql = "SELECT DISTINCT 
    tahun, 
    nama, 
    nip,
    nama_atasan_langsung,
    MIN(tanggal_input) as tanggal_input,
    COUNT(*) as total_entries,
    status
    FROM skp_lampiran 
    WHERE nip = ? 
    GROUP BY tahun, nama, nip, nama_atasan_langsung, status
    ORDER BY tahun DESC 
    LIMIT ? OFFSET ?";
$lampiran_stmt = $conn->prepare($lampiran_sql);
$lampiran_stmt->bind_param('sii', $user_nip, $per_page, $offset);
$lampiran_stmt->execute();
$lampiran_result = $lampiran_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.67, minimum-scale=0.67, maximum-scale=2.0, user-scalable=yes">
    <title>Daftar Lampiran SKP - SI-APA</title>
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

        .status-draft {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
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

        .add-btn {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 14px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 14px;
            transition: background 0.2s;
        }

        .add-btn:hover {
            background: #218838;
            text-decoration: none;
            color: white;
        }

        .edit-btn {
            background: #fd7e14;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 9px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 0;
        }

        .edit-btn:hover {
            background: #e8590c;
            text-decoration: none;
            color: white;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 9px;
            font-weight: 600;
            cursor: pointer;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .view-details-btn, .submit-evaluasi-btn, .download-pdf-btn, .revisi-lampiran-btn,
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

        .revisi-lampiran-btn {
            background: #dc3545;
            margin-left: 6px;
        }

        .revisi-lampiran-btn:hover {
            background: #c82333;
        }

        .hapus-btn {
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            font-size: 9px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-left: 6px;
            height: 26px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .hapus-btn:hover {
            background: #c82333;
        }

        .pagination-controls {
            margin-top: 24px;
            text-align: center;
            width: 100%;
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
        <div class="page-title">Daftar Lampiran SKP</div>


        <?php if ($lampiran_result && $lampiran_result->num_rows > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Nama Atasan Langsung</th>
                        <th>Tahun</th>
                        <th>Total Entri</th>
                        <th>Status</th>
                        <th>Tanggal Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $counter = 1 + $offset;
                    while ($row = $lampiran_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><strong><?php echo htmlspecialchars($row['nama'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['nama_atasan_langsung'] ?? 'N/A'); ?></td>
                            <td><strong><?php echo htmlspecialchars($row['tahun'] ?? 'N/A'); ?></strong></td>
                            <td>
                                <span class="status-badge status-draft">
                                    <?php echo htmlspecialchars($row['total_entries'] ?? '0'); ?> Entri
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $row['status'] ?? 'draft')); ?>">
                                    <?php echo htmlspecialchars($row['status'] ?? 'Draft'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['tanggal_input'] ?? 'N/A'); ?></td>
                            <td>
                                <div class="aksi-buttons">
                                    <button class="view-details-btn" onclick="viewLampiranDetails('<?php echo htmlspecialchars($row['tahun']); ?>', '<?php echo htmlspecialchars($row['nip']); ?>')">Lihat Detail</button>
                                    <?php if (strtoupper($row['status'] ?? '') === 'DRAFT' || strtoupper($row['status'] ?? '') === 'DRAFT DIKEMBALIKAN'): ?>
                                        <button class="edit-btn view-details-btn" onclick="window.location.href='skp_lampiran.php?tahun=<?php echo htmlspecialchars($row['tahun']); ?>&nip=<?php echo htmlspecialchars($row['nip']); ?>'">UBAH</button>
                                        <button class="submit-evaluasi-btn" onclick="submitLampiran('<?php echo htmlspecialchars($row['tahun']); ?>', '<?php echo htmlspecialchars($row['nip']); ?>')">Submit Lampiran</button>
                                        <button class="hapus-btn" title="Hapus Lampiran" onclick="deleteLampiranByYear(<?php echo htmlspecialchars($row['tahun']); ?>)">Hapus</button>
                                    <?php elseif (strtoupper($row['status'] ?? '') === 'PROSES EVALUASI'): ?>
                                        <?php if ($is_atasan && (strtoupper($row['nama_atasan_langsung'] ?? '') === strtoupper($manager_name) || strtoupper($row['nip_atasan_langsung'] ?? '') === strtoupper($user_nip))): ?>
                                            <button class="revisi-lampiran-btn" onclick="revisiLampiran('<?php echo htmlspecialchars($row['tahun']); ?>', '<?php echo htmlspecialchars($row['nip']); ?>')">REVISI</button>
                                            <button class="submit-evaluasi-btn" onclick="approveLampiran('<?php echo htmlspecialchars($row['tahun']); ?>', '<?php echo htmlspecialchars($row['nip']); ?>')">APPROVE</button>
                                        <?php else: ?>
                                            <button class="submit-evaluasi-btn" disabled>⏳ Menunggu Evaluasi</button>
                                        <?php endif; ?>
                                    <?php elseif (strtoupper($row['status'] ?? '') === 'SELESAI EVALUASI'): ?>
                                        <button class="download-pdf-btn" onclick="downloadLampiranPDF('<?php echo htmlspecialchars($row['tahun']); ?>', '<?php echo htmlspecialchars($row['nip']); ?>')"><img src="images/pdf.png" alt="PDF"> LAMPIRAN SKP</button>
                                        <button class="submit-evaluasi-btn" disabled>✅ Selesai Evaluasi</button>
                                    <?php else: ?>
                                        <button class="submit-evaluasi-btn" disabled>⏳ Menunggu Evaluasi</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                <h3>📭 Belum Ada Lampiran</h3>
                <p>Belum ada lampiran SKP yang dibuat. Silakan tambah lampiran baru.</p>
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
                    <a href="<?php echo $base_url . '?' . http_build_query($query); ?>" style="margin:0 8px;">&laquo; Previous</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php $query['page'] = $i; ?>
                    <a href="<?php echo $base_url . '?' . http_build_query($query); ?>" style="margin:0 4px;<?php echo $i == $page ? 'font-weight:bold;text-decoration:underline;' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <?php if ($page < $total_pages): ?>
                    <?php $query['page'] = $page + 1; ?>
                    <a href="<?php echo $base_url . '?' . http_build_query($query); ?>" style="margin:0 8px;">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function viewLampiranDetails(tahun, nip) {
            // Open lampiran details in a new window/tab (view-only mode)
            window.open('skp_lampiran.php?tahun=' + tahun + '&nip=' + nip + '&view=1', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function submitLampiran(tahun, nip) {
            if (confirm('Apakah Anda yakin ingin submit lampiran SKP untuk tahun ' + tahun + ' ke atasan untuk evaluasi?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
                // Submit lampiran via AJAX
                fetch('submit_lampiran_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'tahun=' + encodeURIComponent(tahun) + '&nip=' + encodeURIComponent(nip)
                })
                .then(response => response.text())
                .then(rawText => {
                    console.log('Server raw response:', rawText);
                    let data;
                    try {
                        data = JSON.parse(rawText);
                    } catch(e) {
                        // Show the actual server output so we can debug
                        alert('❌ Server tidak mengembalikan JSON.\n\nResponse:\n' + rawText.substring(0, 500));
                        button.innerHTML = originalText;
                        button.disabled = false;
                        return;
                    }
                    if (data.success) {
                        alert('✅ Lampiran SKP berhasil disubmit!\nStatus lampiran telah diubah menjadi "PROSES EVALUASI".');
                        location.reload();
                    } else {
                        alert('❌ Gagal submit lampiran: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('❌ Fetch error: ' + error.message);
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
        
        function deleteLampiranByYear(tahun) {
            if (confirm('Apakah Anda yakin ingin menghapus semua lampiran untuk tahun ' + tahun + '? Semua data terkait akan dihapus secara permanen.')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Menghapus...';
                button.disabled = true;
                
                fetch('delete_lampiran.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'tahun=' + encodeURIComponent(tahun) + '&nip=' + encodeURIComponent('<?php echo $user_nip; ?>')
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Semua lampiran untuk tahun ' + tahun + ' berhasil dihapus!');
                        location.reload();
                    } else {
                        alert('❌ Gagal menghapus lampiran: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan saat menghapus lampiran. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function revisiLampiran(tahun, nip) {
            if (confirm('Apakah Anda yakin ingin mengembalikan lampiran SKP untuk tahun ' + tahun + ' ke status DRAFT untuk direvisi oleh pegawai?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
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
                        alert('✅ Lampiran SKP berhasil dikembalikan ke status DRAFT!\nPegawai dapat melakukan revisi pada lampiran ini.');
                        location.reload();
                    } else {
                        alert('❌ Gagal mengembalikan lampiran: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan saat mengembalikan lampiran. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function approveLampiran(tahun, nip) {
            if (confirm('Apakah Anda yakin ingin menyetujui lampiran SKP untuk tahun ' + tahun + '? Status akan berubah menjadi SELESAI EVALUASI.')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
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
                        alert('✅ Lampiran SKP berhasil disetujui!\nStatus lampiran telah diubah menjadi "SELESAI EVALUASI".');
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
    </script>
</body>
</html>

<?php
$conn->close();
?>
