<?php
// Set header untuk JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/database.php';

    // Ambil parameter dari AJAX
    $page        = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $search      = isset($_GET['search']) ? trim($_GET['search']) : '';
    $filterJenis = isset($_GET['filterJenis']) ? $_GET['filterJenis'] : '';
    $filterDivisi = isset($_GET['filterDivisi']) ? $_GET['filterDivisi'] : '';
    $startDate   = isset($_GET['startDate']) ? $_GET['startDate'] : '';
    $endDate     = isset($_GET['endDate']) ? $_GET['endDate'] : '';

    // Pagination setup
    $limit  = 10;
    $offset = ($page - 1) * $limit;

    // Base query
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

    // Filter search
    if ($search !== '') {
      $search = "%{$search}%";
      $query .= " AND (k.judul LIKE ? OR kb.ringkasan LIKE ? OR km.caption LIKE ?)";
      $params[] = $search;
      $params[] = $search;
      $params[] = $search;
    }

    // Filter jenis
    if ($filterJenis !== '') {
      if ($filterJenis === 'medsos') {
        // Filter untuk semua platform media sosial (bukan berita)
        $query .= " AND k.jenis IN ('instagram', 'youtube', 'tiktok', 'twitter', 'facebook')";
      } else {
        $query .= " AND k.jenis = ?";
        $params[] = $filterJenis;
      }
    }

    // Filter divisi (untuk kategori/platform)
    if ($filterDivisi !== '') {
      // Untuk berita, filter berdasarkan jenis_berita
      // Untuk medsos, filter berdasarkan jenis (platform) dari tabel konten
      if (in_array($filterDivisi, ['media_online', 'surat_kabar', 'website_kanwil'])) {
        // Filter untuk kategori berita
        $query .= " AND kb.jenis_berita = ?";
        $params[] = $filterDivisi;
      } else {
        // Filter untuk platform media sosial
        $query .= " AND k.jenis = ?";
        $params[] = $filterDivisi;
      }
    }

    // Filter tanggal
    if ($startDate !== '' && $endDate !== '') {
      $query .= " AND DATE(k.tanggal_input) BETWEEN ? AND ?";
      $params[] = $startDate;
      $params[] = $endDate;
    }

    // Hitung total data
    $countQuery = "SELECT COUNT(*) as total FROM ({$query}) as sub";
    $stmtCount = $conn->prepare($countQuery);
    $stmtCount->execute($params);
    $totalData = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalData / $limit);

    // Tambahkan limit
    $query .= " ORDER BY k.tanggal_input DESC LIMIT $limit OFFSET $offset";
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
    // Return error JSON
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
