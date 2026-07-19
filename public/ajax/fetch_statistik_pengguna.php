<?php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';

try {
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;
    $role = isset($_GET['role']) && $_GET['role'] !== 'all' ? $_GET['role'] : null;

    $userWhere = "1=1";
    $userParams = [];
    if ($role) {
        $userWhere .= " AND p.role = :role";
        $userParams[':role'] = $role;
    }

    // Get all users matching role
    $queryUsers = "SELECT id_pengguna, nama, username, role, foto FROM pengguna p WHERE $userWhere ORDER BY p.role ASC, p.nama ASC";
    $stmtUsers = $conn->prepare($queryUsers);
    $stmtUsers->execute($userParams);
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    // Prepare date conditions
    function getDateCond($col, $startDate, $endDate) {
        if ($startDate && $endDate) {
            return " AND DATE($col) BETWEEN :start_date AND :end_date";
        } elseif ($startDate) {
            return " AND DATE($col) >= :start_date";
        } elseif ($endDate) {
            return " AND DATE($col) <= :end_date";
        }
        return "";
    }

    $dateParams = [];
    if ($startDate) $dateParams[':start_date'] = $startDate;
    if ($endDate) $dateParams[':end_date'] = $endDate;

    // Total Konten (Total Arsip)
    $qKontenTotal = "SELECT COUNT(*) FROM konten WHERE 1=1" . getDateCond('tanggal_input', $startDate, $endDate);
    $stmtKonten = $conn->prepare($qKontenTotal);
    $stmtKonten->execute($dateParams);
    $totalSemuaKonten = (int)$stmtKonten->fetchColumn();

    // Total Kegiatan
    $qKegiatanTotal = "SELECT COUNT(*) FROM kegiatan WHERE 1=1" . getDateCond('tanggal', $startDate, $endDate);
    $stmtKegiatan = $conn->prepare($qKegiatanTotal);
    $stmtKegiatan->execute($dateParams);
    $totalSemuaKegiatan = (int)$stmtKegiatan->fetchColumn();

    // Total Peminjaman
    $totalSemuaPeminjaman = 0;
    try {
        $qPeminjamanTotal = "SELECT COUNT(*) FROM jadwal_peminjaman_ruangan WHERE 1=1";
        // if date is requested, maybe use tanggal_kegiatan
        if ($startDate || $endDate) {
            $qPeminjamanTotal .= getDateCond('tanggal_kegiatan', $startDate, $endDate);
        }
        $stmtPeminjaman = $conn->prepare($qPeminjamanTotal);
        $stmtPeminjaman->execute($dateParams);
        $totalSemuaPeminjaman = (int)$stmtPeminjaman->fetchColumn();
    } catch (Exception $e) {}

    // Total Tamu
    $totalSemuaTamu = 0;
    try {
        $qTamuTotal = "SELECT COUNT(*) FROM tb_tamu WHERE 1=1" . getDateCond('tgl', $startDate, $endDate);
        $stmtTamu = $conn->prepare($qTamuTotal);
        $stmtTamu->execute($dateParams);
        $totalSemuaTamu = (int)$stmtTamu->fetchColumn();
    } catch (Exception $e) {}

    // Total Pengaduan (Aduan)
    $totalSemuaPengaduan = 0;
    try {
        // try aduan first, then layanan_pengaduan
        $qPengaduanTotal = "SELECT COUNT(*) FROM layanan_pengaduan WHERE 1=1" . getDateCond('tanggal_pengaduan', $startDate, $endDate);
        $stmtPengaduan = $conn->prepare($qPengaduanTotal);
        $stmtPengaduan->execute($dateParams);
        $totalSemuaPengaduan = (int)$stmtPengaduan->fetchColumn();
    } catch (Exception $e) {}

    // Total Harmonisasi
    $totalSemuaHarmonisasi = 0;
    try {
        $qHarmonisasiTotal = "SELECT COUNT(*) FROM harmonisasi WHERE 1=1" . getDateCond('tanggal_rapat', $startDate, $endDate);
        $stmtHarmonisasi = $conn->prepare($qHarmonisasiTotal);
        $stmtHarmonisasi->execute($dateParams);
        $totalSemuaHarmonisasi = (int)$stmtHarmonisasi->fetchColumn();
    } catch (Exception $e) {}

    // Total Log Aktivitas
    $qLogTotal = "SELECT COUNT(*) FROM log_aktivitas WHERE 1=1" . getDateCond('tanggal', $startDate, $endDate);
    $stmtLog = $conn->prepare($qLogTotal);
    $stmtLog->execute($dateParams);
    $totalSemuaLog = (int)$stmtLog->fetchColumn();

    $topUser = null;
    $maxLog = -1;

    $roleDistribution = [
        'Admin' => 0,
        'Operator' => 0,
        'P3H' => 0,
        'Pegawai' => 0
    ];

    foreach ($users as &$u) {
        $id = $u['id_pengguna'];
        $params = array_merge([':id' => $id], $dateParams);

        // Konten
        $q = "SELECT COUNT(*) FROM konten WHERE id_pengguna = :id" . getDateCond('tanggal_input', $startDate, $endDate);
        $s = $conn->prepare($q); $s->execute($params);
        $u['total_konten'] = (int)$s->fetchColumn();

        // Kegiatan
        $q = "SELECT COUNT(*) FROM kegiatan WHERE id_pengguna = :id" . getDateCond('tanggal', $startDate, $endDate);
        $s = $conn->prepare($q); $s->execute($params);
        $u['total_kegiatan'] = (int)$s->fetchColumn();

        // Ruangan / Peminjaman
        $u['total_ruangan'] = 0;
        try {
            $q = "SELECT COUNT(*) FROM jadwal_peminjaman_ruangan WHERE id_pengguna = :id";
            if ($startDate || $endDate) {
                $q .= getDateCond('tanggal_kegiatan', $startDate, $endDate);
            }
            $s = $conn->prepare($q); $s->execute($params);
            $u['total_ruangan'] = (int)$s->fetchColumn();
        } catch (Exception $e) {}

        // Buku Tamu
        $u['total_tamu'] = 0;
        try {
            $q = "SELECT COUNT(*) FROM tb_tamu WHERE id_pengguna = :id" . getDateCond('tgl', $startDate, $endDate);
            $s = $conn->prepare($q); $s->execute($params);
            $u['total_tamu'] = (int)$s->fetchColumn();
        } catch (Exception $e) {}

        // Pengaduan
        $u['total_pengaduan'] = 0;
        try {
            $q = "SELECT COUNT(*) FROM layanan_pengaduan WHERE id_pengguna = :id" . getDateCond('tanggal_pengaduan', $startDate, $endDate);
            $s = $conn->prepare($q); $s->execute($params);
            $u['total_pengaduan'] = (int)$s->fetchColumn();
        } catch (Exception $e) {}

        // Harmonisasi
        $u['total_harmonisasi'] = 0;
        try {
            $q = "SELECT COUNT(*) FROM harmonisasi WHERE id_pengguna = :id" . getDateCond('tanggal_rapat', $startDate, $endDate);
            $s = $conn->prepare($q); $s->execute($params);
            $u['total_harmonisasi'] = (int)$s->fetchColumn();
        } catch (Exception $e) {}

        // Log Aktivitas (Total Aktivitas)
        $q = "SELECT COUNT(*) FROM log_aktivitas WHERE id_pengguna = :id" . getDateCond('tanggal', $startDate, $endDate);
        $s = $conn->prepare($q); $s->execute($params);
        $u['total_log_aktivitas'] = (int)$s->fetchColumn();

        if ($u['total_log_aktivitas'] > $maxLog) {
            $maxLog = $u['total_log_aktivitas'];
            $topUser = $u;
        }

        $r = strtoupper($u['role']);
        if (isset($roleDistribution['Admin']) && $r == 'ADMIN') $roleDistribution['Admin']++;
        elseif (isset($roleDistribution['Operator']) && $r == 'OPERATOR') $roleDistribution['Operator']++;
        elseif (isset($roleDistribution['P3H']) && $r == 'P3H') $roleDistribution['P3H']++;
        elseif ($r == 'PEGAWAI') $roleDistribution['Pegawai']++;
    }

    // Sort users for top kontributor by log_aktivitas
    usort($users, function($a, $b) {
        return $b['total_log_aktivitas'] <=> $a['total_log_aktivitas'];
    });
    
    // Find highest log for progress bar percentage
    $highestLog = count($users) > 0 ? $users[0]['total_log_aktivitas'] : 1;
    if ($highestLog == 0) $highestLog = 1;
    
    foreach($users as &$u) {
        $u['produktivitas_persen'] = round(($u['total_log_aktivitas'] / $highestLog) * 100);
    }

    $topKontributor = array_slice($users, 0, 5);
    $topUsersBar = array_slice($users, 0, 10);

    // Chart 7 Hari Terakhir
    $chart7Hari = [
        'labels' => [],
        'values' => []
    ];
    
    // Default 7 days from the latest log date in the database if no filter is applied
    if ($endDate) {
        $end = new DateTime($endDate);
    } else {
        $latestDateStr = date('Y-m-d');
        try {
            $stmtLatest = $conn->query("SELECT MAX(tanggal) FROM log_aktivitas");
            $resLatest = $stmtLatest->fetchColumn();
            if ($resLatest) {
                $latestDateStr = $resLatest;
            }
        } catch (Exception $e) {}
        $end = new DateTime($latestDateStr);
    }
    
    $start = $startDate ? new DateTime($startDate) : (clone $end)->modify('-6 days');
    
    // If date range is larger than 30 days, we might group by month. For simplicity, group by day.
    // If it's too large, we just fetch what we can. Let's limit to 30 points.
    $interval = $start->diff($end)->days;
    if ($interval > 31) {
        $start = (clone $end)->modify('-30 days');
    }

    $current = clone $start;
    while ($current <= $end) {
        $dateStr = $current->format('Y-m-d');
        $labelStr = $current->format('d M');
        $chart7Hari['labels'][] = $labelStr;
        
        $q = "SELECT COUNT(*) FROM log_aktivitas WHERE tanggal = :tgl";
        $p = [':tgl' => $dateStr];
        if ($role) {
            $q = "SELECT COUNT(l.id_log) FROM log_aktivitas l JOIN pengguna p ON l.id_pengguna = p.id_pengguna WHERE l.tanggal = :tgl AND p.role = :role";
            $p[':role'] = $role;
        }
        $stmt = $conn->prepare($q);
        $stmt->execute($p);
        $chart7Hari['values'][] = (int)$stmt->fetchColumn();
        
        $current->modify('+1 day');
    }

    echo json_encode([
        'success' => true,
        'summary' => [
            'total_konten' => $totalSemuaKonten,
            'total_aktivitas' => $totalSemuaLog,
            'pengguna_aktif' => count($users),
            'top_user' => $topUser ? $topUser['nama'] : '-',
            'top_user_aktivitas' => $topUser ? $topUser['total_log_aktivitas'] : 0,
            'total_kegiatan' => $totalSemuaKegiatan,
            'total_peminjaman' => $totalSemuaPeminjaman,
            'total_tamu' => $totalSemuaTamu ?? 0,
            'total_pengaduan' => $totalSemuaPengaduan ?? 0,
            'total_harmonisasi' => $totalSemuaHarmonisasi ?? 0
        ],
        'chart7Hari' => $chart7Hari,
        'topKontributor' => $topKontributor,
        'topUsersBar' => $topUsersBar,
        'roleDistribution' => $roleDistribution,
        'tableData' => $users
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
