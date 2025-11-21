<?php
// Set header untuk JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // Ambil parameter dari AJAX
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $keywords = isset($_GET['keywords']) ? $_GET['keywords'] : ''; // Comma-separated keywords
    $filterJenis = isset($_GET['filterJenis']) ? $_GET['filterJenis'] : '';
    $filterKategori = isset($_GET['filterKategori']) ? $_GET['filterKategori'] : '';
    $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : '';
    $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : '';

    // Pagination setup - support limit parameter untuk download all data
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Base query - include sumber_berita untuk kolom sumber/media
    $query = "
    SELECT k.id_konten, k.jenis, k.judul, k.divisi, k.dokumentasi, k.tanggal_input,
           kb.tanggal_berita, kb.link_berita, kb.sumber_berita, kb.jenis_berita, kb.ringkasan,
           km.tanggal_post, km.link_post, km.caption
    FROM konten k
    LEFT JOIN konten_berita kb ON k.id_konten = kb.id_konten
    LEFT JOIN konten_medsos km ON k.id_konten = km.id_konten
    WHERE 1=1
    ";

    // Initialize params array
    $params = [];

    // Filter keywords - multiple keywords dengan OR logic
    if ($keywords !== '') {
        $keywordArray = array_filter(array_map('trim', explode(',', $keywords)));
        if (!empty($keywordArray)) {
            $keywordConditions = [];
            foreach ($keywordArray as $keyword) {
                $keywordConditions[] = "k.judul LIKE ?";
                $params[] = "%{$keyword}%";
            }
            if (!empty($keywordConditions)) {
                $query .= " AND (" . implode(" OR ", $keywordConditions) . ")";
            }
        }
    }

    // Filter jenis
    if ($filterJenis !== '' && $filterJenis !== 'all') {
        if ($filterJenis === 'medsos') {
            $query .= " AND k.jenis IN ('instagram', 'youtube', 'tiktok', 'twitter', 'facebook')";
        } else {
            $query .= " AND k.jenis = ?";
            $params[] = $filterJenis;
        }
    }

    // Filter kategori/platform
    if ($filterKategori !== '' && $filterKategori !== 'all') {
        if (in_array($filterKategori, ['media_online', 'surat_kabar', 'website_kanwil'])) {
            $query .= " AND kb.jenis_berita = ?";
            $params[] = $filterKategori;
        } else {
            $query .= " AND k.jenis = ?";
            $params[] = $filterKategori;
        }
    }

    // Filter tanggal
    if ($startDate !== '' && $endDate !== '') {
        $query .= " AND (
          (k.jenis = 'berita' AND DATE(kb.tanggal_berita) BETWEEN ? AND ?) OR
          (k.jenis IN ('instagram', 'youtube', 'tiktok', 'twitter', 'facebook') AND DATE(km.tanggal_post) BETWEEN ? AND ?)
        )";
        $params[] = $startDate;
        $params[] = $endDate;
        $params[] = $startDate;
        $params[] = $endDate;
    }

    // Hitung total data
    $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as sub";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->execute($params);
    $totalData = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalData / $limit);

    // Tambahkan limit dan order (jika limit > 1000, berarti download all data, skip pagination)
    if ($limit > 1000) {
        // Untuk download all data, tidak perlu pagination
        $query .= " ORDER BY k.tanggal_input DESC";
    } else {
        $query .= " ORDER BY k.tanggal_input DESC LIMIT $limit OFFSET $offset";
    }
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON
    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalData' => $totalData
        ]
    ]);

} catch (Exception $e) {
    error_log("[ERROR] Fetch Search Konten: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'data' => [],
        'pagination' => [
            'currentPage' => 1,
            'totalPages' => 0,
            'totalData' => 0
        ]
    ]);
}

