<?php
// app/views/pages/data-harmonisasi-public.php
// Halaman publik untuk melihat data harmonisasi (tanpa login)
// $BASE sudah didefinisikan di header.php, tidak perlu didefinisikan lagi
// Jika header.php belum di-include, gunakan fallback
if (!isset($BASE)) {
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    
    $isLocalhost = (
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($httpHost, 'localhost') !== false ||
        strpos($httpHost, '127.0.0.1') !== false ||
        strpos($requestUri, '/rekap-konten/public') !== false ||
        strpos($scriptName, '/rekap-konten/public') !== false ||
        (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && strpos($_SERVER['HTTP_X_FORWARDED_HOST'], 'localhost') !== false)
    );
    
    $BASE = $isLocalhost ? '/rekap-konten/public' : '';
    
    // Fallback: jika BASE kosong tapi script ada di subdirectory, deteksi otomatis
    if (empty($BASE) && strpos($scriptName, '/public/') !== false) {
        $pathParts = explode('/public/', $scriptName);
        if (count($pathParts) > 1) {
            $BASE = $pathParts[0] . '/public';
        }
    }
    
    // Pastikan BASE selalu dimulai dengan / jika tidak kosong
    if (!empty($BASE) && $BASE[0] !== '/') {
        $BASE = '/' . $BASE;
    }
    
    // Pastikan BASE tidak diakhiri dengan /
    $BASE = rtrim($BASE, '/');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Harmonisasi - SiCakap</title>
    <link rel="icon" type="image/png" href="<?= $BASE ?>/Images/aset_landing.png">
    <link rel="stylesheet" href="<?= $BASE ?>/css/style.css">
    <link rel="stylesheet" href="<?= $BASE ?>/vendor/fontawesome/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
        }
        .public-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .public-header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .public-header h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .public-header a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
            transition: background 0.3s;
        }
        .public-header a:hover {
            background: rgba(255,255,255,0.3);
        }
        .public-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .public-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .public-title h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .public-title p {
            color: #666;
            font-size: 1rem;
        }
        .harmonisasi-table-wrapper {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }
        .harmonisasi-preview-table {
            width: 100%;
            overflow-x: auto;
        }
        .harmonisasi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            min-width: 1200px;
        }
        .harmonisasi-table thead {
            background: #0a1128;
            color: #ffffff;
        }
        .harmonisasi-table thead th {
            background: #0a1128 !important;
            color: #ffffff !important;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            border: none;
            letter-spacing: 0.3px;
        }
        .harmonisasi-table thead tr th {
            color: #ffffff !important;
            background-color: #0a1128 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }
        .harmonisasi-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
            font-size: 0.9rem;
            color: #ffffff !important;
            background: #0a1128 !important;
        }
        .harmonisasi-table th:first-child,
        .harmonisasi-table td:first-child {
            text-align: center;
            width: 50px;
        }
        .harmonisasi-table th:nth-child(2) {
            min-width: 300px;
        }
        .harmonisasi-table th:nth-child(3) {
            min-width: 200px;
        }
        .harmonisasi-table th:nth-child(4) {
            min-width: 200px;
        }
        .harmonisasi-table tbody tr {
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s;
        }
        .harmonisasi-table tbody tr:hover {
            background: #f8f9fa;
        }
        .harmonisasi-table td {
            padding: 0.875rem 1rem;
            color: #334155;
            vertical-align: middle;
        }
        .harmonisasi-table td.text-full {
            max-width: 300px;
            min-width: 200px;
        }
        .harmonisasi-table td.text-full .cell-content {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }
        .harmonisasi-table .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
        }
        .harmonisasi-table .status-selesai {
            background: #d4edda;
            color: #155724;
        }
        .harmonisasi-table .status-proses {
            background: #fff3cd;
            color: #856404;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
            margin-top: 20px;
        }
        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: white;
            color: #333;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .pagination-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .pagination-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
            font-weight: bold;
        }
        .pagination-dots {
            padding: 8px 4px;
            color: #666;
        }
        /* Responsive: Card layout untuk mobile */
        @media (max-width: 768px) {
            .harmonisasi-table-wrapper {
                padding: 1rem;
            }
            .harmonisasi-preview-table {
                display: block;
            }
            .harmonisasi-table {
                display: block;
                min-width: 100%;
            }
            .harmonisasi-table thead {
                display: none;
            }
            .harmonisasi-table tbody {
                display: block;
            }
            .harmonisasi-table tbody tr {
                display: block;
                margin-bottom: 1rem;
                background: white;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 1rem;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .harmonisasi-table tbody td {
                display: block;
                padding: 0.5rem 0;
                text-align: left !important;
                border: none;
                border-bottom: 1px solid #f1f5f9;
            }
            .harmonisasi-table tbody td:last-child {
                border-bottom: none;
            }
            .harmonisasi-table tbody td:before {
                content: attr(data-label);
                font-weight: 600;
                color: #0a1128;
                display: block;
                margin-bottom: 0.25rem;
                font-size: 0.85rem;
            }
            .harmonisasi-table tbody td.text-full .cell-content {
                -webkit-line-clamp: 5;
                line-clamp: 5;
                display: block;
            }
            .filters {
                flex-direction: column;
                align-items: stretch !important;
            }
            .filters label {
                margin-bottom: 5px;
            }
            .search-container input {
                font-size: 0.9rem;
            }
        }
        @media (min-width: 769px) and (max-width: 1024px) {
            .harmonisasi-table {
                font-size: 0.85rem;
                min-width: 1000px;
            }
            .harmonisasi-table th,
            .harmonisasi-table td {
                padding: 0.75rem 0.75rem;
            }
            .harmonisasi-table td.text-full .cell-content {
                -webkit-line-clamp: 2;
                line-clamp: 2;
            }
        }
        .btn-view-detail {
            background: #667eea;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: background 0.3s;
        }
        .btn-view-detail:hover {
            background: #5568d3;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-selesai {
            background: #d4edda;
            color: #155724;
        }
        .status-proses {
            background: #fff3cd;
            color: #856404;
        }
        .back-to-landing {
            text-align: center;
            margin-top: 30px;
        }
        .back-to-landing a {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .back-to-landing a:hover {
            background: #5568d3;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="public-header">
        <div class="public-header-content">
            <h1><i class="fas fa-balance-scale"></i> Data Harmonisasi</h1>
            <a href="landing.php"><i class="fas fa-arrow-left"></i> Kembali ke Landing Page</a>
        </div>
    </div>

    <div class="public-container">
        <div class="public-title">
            <h2>Data Harmonisasi Rancangan Peraturan</h2>
            <p>Informasi lengkap mengenai data harmonisasi rancangan peraturan</p>
        </div>

        <div class="harmonisasi-page-public">
            <!-- Pencarian -->
            <div class="search-container" style="margin-bottom: 20px;">
                <input type="text" id="searchInput" placeholder="Cari berdasarkan judul, pemrakarsa, pemerintah daerah, atau pemegang draf..." 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem;">
            </div>

            <!-- Filter -->
            <div class="filters" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom: 20px;">
                <label for="startDate">Tanggal Rapat:</label>
                <input type="date" id="startDate" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <span>-</span>
                <input type="date" id="endDate" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                <label for="statusFilter">Status:</label>
                <select id="statusFilter" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">Semua Status</option>
                    <option value="Diterima">Diterima</option>
                    <option value="Dikembalikan">Dikembalikan</option>
                </select>
                <button id="filterBtn" style="padding: 8px 16px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">Terapkan</button>
                <button id="resetBtn" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer;">Reset</button>
            </div>

            <!-- Tabel Harmonisasi -->
            <div class="harmonisasi-table-wrapper">
                <div id="harmonisasiResults">
                    <div style="text-align: center; padding: 20px;">
                        <p>Memuat data...</p>
                    </div>
                </div>
            </div>

            <!-- Modal Detail -->
            <div id="detailModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h3>Detail Data Harmonisasi</h3>
                    <div id="modalContent" style="max-height: 500px; overflow-y: auto; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; margin: 15px 0; color: #333; line-height: 1.6; font-size: 14px; white-space: pre-wrap;"></div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="pagination" style="margin-top: 20px; text-align: center;">
                <!-- Pagination akan di-generate via JavaScript -->
            </div>
        </div>

        <div class="back-to-landing">
            <a href="landing.php"><i class="fas fa-arrow-left"></i> Kembali ke Landing Page</a>
        </div>
    </div>

    <script>
        // Set BASE_URL untuk JavaScript
        window.BASE_URL = '<?= $BASE ?>';
    </script>
    <script src="<?= $BASE ?>/js/data-harmonisasi-public.js"></script>
</body>
</html>

