<?php
// Enhanced session security
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Database connection
require_once 'config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Check if user table exists (try different possible names)
$user_table_name = null;
$possible_tables = ['user', 'users', 'User', 'Users'];

foreach ($possible_tables as $table) {
    $check_table = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check_table && $check_table->num_rows > 0) {
        $user_table_name = $table;
        break;
    }
}

// If no user table exists, create the default one
if (!$user_table_name) {
    $user_table_name = 'users';
    $create_table_sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nama VARCHAR(100) NOT NULL,
        nip VARCHAR(20) NOT NULL,
        jabatan VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_table_sql);

    // Insert default admin user
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = "INSERT INTO users (username, password, nama, nip, jabatan) VALUES ('admin', '$default_password', 'Administrator', '123456789', 'Admin Sistem')";
    $conn->query($insert_admin);
}

$error_message = '';
$success_message = '';

if (isset($_GET['timeout']) && $_GET['timeout'] == '1') {
    $error_message = 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan login kembali.';
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        if ($user_table_name === 'user' || $user_table_name === 'User') {
            $stmt = $conn->prepare("SELECT u.NAMA, u.NIP, u.USERNAME, u.PASSWORD, u.ATASAN, u.ESELON, p.JABATAN 
                                    FROM $user_table_name u 
                                    LEFT JOIN Pegawai p ON u.NIP = p.NIP 
                                    WHERE u.USERNAME = ?");
            if (!$stmt) {
                $error_message = 'Database error: ' . $conn->error;
            } else {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    $password_valid = false;
                    if (password_verify($password, $user['PASSWORD'])) {
                        $password_valid = true;
                    } elseif ($user['PASSWORD'] === $password) {
                        $password_valid = true;
                    }

                    if ($password_valid) {
                        $_SESSION['user_id'] = $user['NIP'];
                        $_SESSION['username'] = $user['USERNAME'];
                        $_SESSION['nama'] = $user['NAMA'];
                        $_SESSION['nip'] = $user['NIP'];
                        $_SESSION['jabatan'] = $user['JABATAN'] ?? 'Pegawai';
                        $_SESSION['atasan'] = $user['ATASAN'];
                        $_SESSION['eselon'] = $user['ESELON'];
                        $_SESSION['logged_in'] = true;
                        session_regenerate_id(true);
                        header('Location: skploginpage.php');
                        exit();
                    } else {
                        $error_message = 'Password salah!';
                    }
                } else {
                    $error_message = 'Username tidak ditemukan!';
                }
                $stmt->close();
            }
        } else {
            $stmt = $conn->prepare("SELECT id, username, password, nama, nip, jabatan, atasan FROM $user_table_name WHERE username = ?");
            if (!$stmt) {
                $error_message = 'Database error: ' . $conn->error;
            } else {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['nama'] = $user['nama'];
                        $_SESSION['nip'] = $user['nip'];
                        $_SESSION['jabatan'] = $user['jabatan'];
                        $_SESSION['atasan'] = $user['atasan'];
                        $_SESSION['logged_in'] = true;
                        session_regenerate_id(true);
                        header('Location: skploginpage.php');
                        exit();
                    } else {
                        $error_message = 'Password salah!';
                    }
                } else {
                    $error_message = 'Username tidak ditemukan!';
                }
                $stmt->close();
            }
        }
    }
}

// Fetch Statistics for Dashboard
$actual_year = date('Y');
$actual_triwulan = ceil(date('n') / 3);
if ($actual_triwulan > 4) $actual_triwulan = 4; // safety

$tahun_sekarang = $actual_year;
$current_triwulan = $actual_triwulan;

if (isset($_GET['filter_periode'])) {
    $parts = explode('-', $_GET['filter_periode']);
    if (count($parts) == 2) {
        $current_triwulan = (int)$parts[0];
        $tahun_sekarang = (int)$parts[1];
    }
}

// Helper to safely get counts
function get_count($conn, $query, ...$params) {
    $stmt = $conn->prepare($query);
    if (!$stmt) return 0;
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $count = 0;
    if ($row = $res->fetch_assoc()) {
        $count = (int)array_values($row)[0];
    }
    $stmt->close();
    return $count;
}

// 1. Total Pegawai
$total_pegawai = get_count($conn, "SELECT COUNT(*) FROM Pegawai WHERE NIP IS NOT NULL AND NIP != ''");

// 2. Divisi
$total_divisi = get_count($conn, "SELECT COUNT(DISTINCT UNIT_KERJA) FROM Pegawai WHERE UNIT_KERJA IS NOT NULL AND UNIT_KERJA != '' AND ATASAN_LANGSUNG IS NOT NULL AND ATASAN_LANGSUNG != '' AND UNIT_KERJA <> 'Kantor Wilayah Sulawesi Selatan'");

// 3. Pegawai per Divisi (For Bar Chart)
$divisi_data = [];
$res_divisi = $conn->query("SELECT UNIT_KERJA, COUNT(*) as c FROM Pegawai WHERE UNIT_KERJA IS NOT NULL AND UNIT_KERJA != '' AND NIP IS NOT NULL AND NIP != '' AND ATASAN_LANGSUNG IS NOT NULL AND ATASAN_LANGSUNG != '' AND UNIT_KERJA <> 'Kantor Wilayah Sulawesi Selatan' GROUP BY UNIT_KERJA ORDER BY c DESC LIMIT 6");
while ($row = $res_divisi->fetch_assoc()) {
    $divisi_data[] = [
        'nama' => $row['UNIT_KERJA'],
        'jumlah' => (int)$row['c']
    ];
}

// 4. SKP Triwulan Current (Made)
$triwulan_current_made = get_count($conn, "SELECT COUNT(DISTINCT NIP) FROM skp_pegawai WHERE TRIWULAN = ? AND TAHUN = ?", $current_triwulan, $tahun_sekarang);

// 5. SKP Tahunan Current (Made)
$tahunan_current_made = get_count($conn, "SELECT COUNT(DISTINCT NIP) FROM skp_akhir_pegawai WHERE TAHUN = ?", $tahun_sekarang);

// 6. Trend Data (T1, T2, T3, T4 made)
$trend_triwulan = [];
for ($i = 1; $i <= 4; $i++) {
    $trend_triwulan[$i] = get_count($conn, "SELECT COUNT(DISTINCT NIP) FROM skp_pegawai WHERE TRIWULAN = ? AND TAHUN = ?", $i, $tahun_sekarang);
}

// 7. Trend Data (T1, T2, T3, T4 evaluated)
$trend_eval_triwulan = [];
for ($i = 1; $i <= 4; $i++) {
    $trend_eval_triwulan[$i] = get_count($conn, "SELECT COUNT(DISTINCT NIP) FROM skp_pegawai WHERE TRIWULAN = ? AND TAHUN = ? AND STATUS = 'SELESAI EVALUASI'", $i, $tahun_sekarang);
}

// 7. Pegawai Belum Membuat SKP Triwulan Current
$limit_belum = 5;
$page_belum = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset_belum = ($page_belum - 1) * $limit_belum;

$total_belum = get_count($conn, "SELECT COUNT(*) FROM Pegawai WHERE (ATASAN_LANGSUNG IS NOT NULL AND ATASAN_LANGSUNG != '' AND NIP != '-') AND JABATAN NOT IN ('Kepala Kantor Wilayah Kementerian Hukum Sulawesi Selatan', 'SEKRETARIS JENDERAL') AND NIP NOT IN (SELECT NIP FROM skp_pegawai WHERE TRIWULAN = ? AND TAHUN = ?)", $current_triwulan, $tahun_sekarang);
$total_pages_belum = ceil($total_belum / $limit_belum);

$belum_triwulan_list = [];
$res_belum = $conn->query("
    SELECT NAMA, JABATAN, UNIT_KERJA 
    FROM Pegawai 
    WHERE (ATASAN_LANGSUNG IS NOT NULL AND ATASAN_LANGSUNG != '' AND NIP != '-') AND JABATAN NOT IN ('Kepala Kantor Wilayah Kementerian Hukum Sulawesi Selatan', 'SEKRETARIS JENDERAL') AND NIP NOT IN (
        SELECT NIP FROM skp_pegawai WHERE TRIWULAN = $current_triwulan AND TAHUN = $tahun_sekarang
    ) 
    LIMIT $limit_belum OFFSET $offset_belum
");
if ($res_belum) {
    while ($row = $res_belum->fetch_assoc()) {
        $belum_triwulan_list[] = $row;
    }
}

// 7.b Pegawai SKP Triwulan Belum Dievaluasi
$limit_belum_eval = 5;
$page_belum_eval = isset($_GET['page_belum_eval']) ? max(1, (int)$_GET['page_belum_eval']) : 1;
$offset_belum_eval = ($page_belum_eval - 1) * $limit_belum_eval;

$total_belum_eval = get_count($conn, "
    SELECT COUNT(DISTINCT p.NIP) 
    FROM Pegawai p
    JOIN skp_pegawai s ON p.NIP = s.NIP
    WHERE s.STATUS = 'PROSES EVALUASI' 
      AND s.TRIWULAN = ? 
      AND s.TAHUN = ? 
      AND p.JABATAN NOT IN ('Kepala Kantor Wilayah Kementerian Hukum Sulawesi Selatan', 'SEKRETARIS JENDERAL') 
      AND p.NIP != '-'
", $current_triwulan, $tahun_sekarang);
$total_pages_belum_eval = ceil($total_belum_eval / $limit_belum_eval);

$belum_eval_list = [];
$res_belum_eval = $conn->query("
    SELECT DISTINCT p.NAMA, p.JABATAN, p.UNIT_KERJA 
    FROM Pegawai p
    JOIN skp_pegawai s ON p.NIP = s.NIP
    WHERE s.STATUS = 'PROSES EVALUASI' 
      AND s.TRIWULAN = $current_triwulan 
      AND s.TAHUN = $tahun_sekarang 
      AND p.JABATAN NOT IN ('Kepala Kantor Wilayah Kementerian Hukum Sulawesi Selatan', 'SEKRETARIS JENDERAL') 
      AND p.NIP != '-'
    LIMIT $limit_belum_eval OFFSET $offset_belum_eval
");
if ($res_belum_eval) {
    while ($row = $res_belum_eval->fetch_assoc()) {
        $belum_eval_list[] = $row;
    }
}
 
// 8. Count SKP by Status for Current period
$status_draft_count = get_count($conn, "SELECT COUNT(DISTINCT NIP) FROM skp_pegawai WHERE TRIWULAN = ? AND TAHUN = ? AND STATUS = 'draft'", $current_triwulan, $tahun_sekarang);
$status_proses_count = get_count($conn, "SELECT COUNT(DISTINCT NIP) FROM skp_pegawai WHERE TRIWULAN = ? AND TAHUN = ? AND STATUS = 'PROSES EVALUASI'", $current_triwulan, $tahun_sekarang);
$status_selesai_count = get_count($conn, "SELECT COUNT(DISTINCT NIP) FROM skp_pegawai WHERE TRIWULAN = ? AND TAHUN = ? AND STATUS = 'SELESAI EVALUASI'", $current_triwulan, $tahun_sekarang);
$status_total_made = $status_draft_count + $status_proses_count + $status_selesai_count;

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI-APA (Sistem Informasi Penilaian ASN)</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            opacity: 1;
        }
        .login-wrap {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }
        .left-panel {
            flex: 0 0 60%;
            background: #0D2052;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 50px;
            position: relative;
        }
        .left-panel .brand-logo {
            width: 600px;
            height: auto;
            flex-shrink: 0;
        }
        .right-panel {
            flex: 0 0 40%;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 50px;
        }
        .right-panel .top-logo {
            width: 80px;
            height: 80px;
            margin-bottom: 28px;
            object-fit: contain;
        }
        .login-form {
            width: 100%;
            max-width: 320px;
        }
        .input-box {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
            font-size: 0.95rem;
            color: #333;
            text-align: center;
            letter-spacing: 0.05em;
        }
        .input-box::placeholder {
            color: #666;
            text-transform: uppercase;
        }
        .login-btn {
            width: 100%;
            background: #0D2052;
            color: #fff;
            border: none;
            border-radius: 4px;
            padding: 14px;
            font-size: 1rem;
            font-weight: bold;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
            margin-bottom: 24px;
        }
        .login-btn:hover {
            background: #16367a;
        }
        .panduan-links {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        .panduan-links a {
            color: #444;
            text-decoration: underline;
            font-size: 0.9rem;
        }
        .panduan-links a:hover {
            color: #0D2052;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            width: 100%;
            max-width: 320px;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            width: 100%;
            max-width: 320px;
        }
        @media (max-width: 900px) {
            .login-wrap { flex-direction: column; }
            .left-panel {
                flex: none;
                min-height: 200px;
                padding: 30px 24px;
                position: relative;
                flex-direction: column;
            }
            .left-panel .brand-logo { 
                width: 450px; 
                margin-bottom: 20px;
            }
            .scroll-btn {
                position: relative;
                bottom: auto;
                right: auto;
                left: auto;
                transform: none;
                margin: 10px auto 0 auto;
            }
            .right-panel {
                flex: none;
                padding: 32px 24px;
            }
        }
        @media (max-width: 480px) {
            .left-panel .brand-logo { width: 350px; }
            .input-box { font-size: 16px; }
            .chart-grid { grid-template-columns: 1fr; }
        }

        /* --- New Dashboard Styles --- */
        .dashboard-container {
            background: #f8f9fc;
            padding: 2rem;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #858796;
            width: 100%;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .dashboard-title h1 {
            margin: 0;
            font-size: 2.2rem;
            color: #5a5c69;
            font-weight: 800;
        }
        .dashboard-title p {
            margin: 0.2rem 0 0;
            font-size: 1.1rem;
            color: #6e707e;
        }
        .filter-dropdown {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d3e2;
            background: #fff;
            color: #5a5c69;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -0.75rem;
            margin-left: -0.75rem;
            margin-bottom: 1.5rem;
        }
        .col {
            padding-right: 0.75rem;
            padding-left: 0.75rem;
            box-sizing: border-box;
            margin-bottom: 1.5rem;
        }
        .col-3 { width: 25%; }
        .col-4 { width: 33.333%; }
        .col-6 { width: 50%; }
        .col-8 { width: 66.666%; }
        .col-12 { width: 100%; }
        .card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.08);
            border: none;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .card-body {
            padding: 1.5rem;
            flex: 1 1 auto;
        }
        .text-xs { font-size: 0.9rem; font-weight: 800; text-transform: uppercase; margin-bottom: 0.25rem; letter-spacing: 0.5px; }
        .text-lg { font-size: 2.2rem; font-weight: 800; color: #5a5c69; margin: 0; line-height: 1.2; }
        .text-sm { font-size: 1rem; margin-top: 0.5rem; font-weight: 600; }
        .mt-2 { margin-top: 0.5rem; }
        
        /* Colors */
        .text-primary { color: #4e73df; }
        .text-success { color: #1cc88a; }
        .text-warning { color: #f6c23e; }
        .text-purple { color: #6f42c1; }
        
        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bg-primary-light { background: #e0e8ff; color: #4e73df; }
        .bg-success-light { background: #d1f5e8; color: #1cc88a; }
        .bg-purple-light { background: #ebdfff; color: #6f42c1; }
        .bg-warning-light { background: #fef0cd; color: #f6c23e; }
        
        .d-flex { display: flex; }
        .justify-between { justify-content: space-between; }
        .align-center { align-items: center; }
        
        .progress {
            height: 0.5rem;
            background-color: #eaecf4;
            border-radius: 0.35rem;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        .progress-bar { height: 100%; }
        .bg-purple { background-color: #6f42c1; }
        .bg-warning { background-color: #f6c23e; }
        
        .card-header {
            padding: 1rem 1.25rem;
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .card-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            color: #5a5c69;
        }
        .chart-area { position: relative; height: 300px; width: 100%; }
        .chart-pie { position: relative; height: 250px; width: 100%; }
        
        .list-group { list-style: none; padding: 0; margin: 0; }
        .list-group-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eaecf4;
        }
        .list-group-item:last-child { border-bottom: none; }
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #eaecf4;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
            color: #858796;
        }
        .emp-info { flex: 1; }
        .emp-name { font-size: 1.1rem; font-weight: 800; color: #5a5c69; margin: 0 0 0.3rem 0; }
        .emp-role { font-size: 0.95rem; margin: 0; color: #858796;}
        .badge-divisi {
            font-size: 0.85rem;
            padding: 0.3rem 0.6rem;
            border-radius: 1rem;
            background: #fdeaea;
            color: #e74a3b;
            font-weight: 700;
            display: inline-block;
            margin-top: 0.3rem;
        }
        
        @media (max-width: 900px) {
            .col-3 { width: 50%; }
            .col-4, .col-6, .col-8 { width: 100%; }
        }
        @media (max-width: 600px) {
            .col-3 { width: 100%; }
        }
        html { scroll-behavior: smooth; }
        .scroll-btn {
            position: absolute;
            bottom: 45px;
            right: 15%;
            display: flex;
            align-items: center;
            gap: 12px;
            background: transparent;
            color: #fff;
            text-decoration: none;
            z-index: 100;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .scroll-btn-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: transparent;
        }
        .scroll-btn-circle svg {
            width: 18px;
            height: 18px;
            stroke: #fff;
            stroke-width: 3;
            fill: none;
            transition: transform 0.3s ease;
        }
        .scroll-btn-text {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .scroll-btn:hover .scroll-btn-circle {
            border-color: #fff;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }
        .scroll-btn:hover .scroll-btn-circle svg {
            transform: translateY(2px);
        }
        .scroll-btn:hover .scroll-btn-text {
            color: #fff;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }
        .scroll-btn-up {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: transparent;
            color: #0D2052;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .scroll-btn-up-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(13, 32, 82, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: transparent;
        }
        .scroll-btn-up-circle svg {
            width: 18px;
            height: 18px;
            stroke: #0D2052;
            stroke-width: 3;
            fill: none;
            transition: transform 0.3s ease;
        }
        .scroll-btn-up-text {
            color: #0D2052;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .scroll-btn-up:hover .scroll-btn-up-circle {
            border-color: #0D2052;
            background: rgba(13, 32, 82, 0.08);
            box-shadow: 0 0 8px rgba(13, 32, 82, 0.2);
        }
        .scroll-btn-up:hover .scroll-btn-up-circle svg {
            transform: translateY(-2px);
        }
        .scroll-btn-up:hover .scroll-btn-up-text {
            color: #1574F3;
        }

        /* --- Tombol Kembali --- */
        .back-btn {
            position: absolute;
            top: 40px;
            left: 40px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: transparent;
            color: #fff;
            text-decoration: none;
            z-index: 100;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .back-btn-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            background: transparent;
        }
        .back-btn-circle svg {
            width: 18px;
            height: 18px;
            stroke: #fff;
            stroke-width: 3;
            fill: none;
            transition: transform 0.3s ease;
        }
        .back-btn-text {
            color: rgba(255, 255, 255, 0.95);
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .back-btn:hover .back-btn-circle {
            border-color: #fff;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }
        .back-btn:hover .back-btn-circle svg {
            transform: translateX(-2px);
        }
        .back-btn:hover .back-btn-text {
            color: #fff;
            text-shadow: 0 0 8px rgba(255, 255, 255, 0.3);
        }

        /* Responsive styling untuk tombol kembali */
        @media (max-width: 900px) {
            .back-btn {
                top: 20px;
                left: 20px;
            }
            .back-btn-circle {
                width: 36px;
                height: 36px;
            }
            .back-btn-circle svg {
                width: 14px;
                height: 14px;
            }
            .back-btn-text {
                font-size: 0.95rem;
            }
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="login-wrap" id="login-section">
        <div class="left-panel">
            <!-- Tombol Kembali ke Landing Page -->
            <a href="../landing.php" class="back-btn">
                <span class="back-btn-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </span>
                <span class="back-btn-text">Kembali</span>
            </a>

            <img src="images/SIAPA.gif" alt="SI-APA" class="brand-logo">
            <!-- Scroll Button to Dashboard centered exactly at bottom of left panel -->
            <a href="#dashboard-section" class="scroll-btn">
                <span class="scroll-btn-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </span>
                <span class="scroll-btn-text">Lihat Dashboard</span>
            </a>
        </div>
        <div class="right-panel">
            <img src="images/logo.jpeg" alt="Logo" class="top-logo">
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            <form class="login-form" method="post" action="">
                <input type="text" class="input-box" name="username" placeholder="USERNAME" autocomplete="off" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <input type="password" class="input-box" name="password" placeholder="PASSWORD" autocomplete="off" required>
                <button type="submit" class="login-btn">LOGIN</button>
            </form>
            <div class="panduan-links">
                <a href="https://www.youtube.com/watch?v=ppItiwqsPVM" target="_blank" rel="noopener noreferrer">VIDEO PANDUAN PENGGUNAAN</a>
                <a href="https://drive.google.com/drive/folders/1dei3064kXfmNX0xkpsxhmNTYmgbTXVyx" target="_blank" rel="noopener noreferrer">DOKUMEN PANDUAN PENGGUNAAN</a>
            </div>
        </div>
    </div>

    <div class="dashboard-container" id="dashboard-section">
        <div class="dashboard-header">
            <div class="dashboard-title">
                <h1>Dashboard</h1>
                <p>Ringkasan data pegawai dan status penyusunan SKP</p>
            </div>
            <div>
                <form method="GET" action="">
                    <select class="filter-dropdown" name="filter_periode" onchange="this.form.submit()">
                        <?php
                        $years_to_show = [$actual_year, $actual_year - 1, $actual_year - 2];
                        foreach($years_to_show as $yr) {
                            for($t = 4; $t >= 1; $t--) {
                                // Skip future triwulans in the current year
                                if($yr == $actual_year && $t > $actual_triwulan) continue;
                                
                                $val = $t . '-' . $yr;
                                $selected = ($yr == $tahun_sekarang && $t == $current_triwulan) ? 'selected' : '';
                                echo "<option value='{$val}' {$selected}>Triwulan {$t} ({$yr})</option>";
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- ROW 1 -->
        <div class="row">
            <!-- Card 1 -->
            <div class="col col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-between align-center">
                            <div>
                                <div class="text-xs text-primary">TOTAL PEGAWAI</div>
                                <div class="text-lg"><?= $total_pegawai ?></div>
                                <div class="text-sm">Orang</div>
                            </div>
                            <div class="icon-box bg-primary-light">
                                <svg style="width:24px;height:24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" fill="currentColor"><path d="M144 160c-44.2 0-80-35.8-80-80S99.8 0 144 0s80 35.8 80 80-35.8 80-80 80zm352 0c-44.2 0-80-35.8-80-80s35.8-80 80-80 80 35.8 80 80-35.8 80-80 80zM320 256c-61.9 0-112-50.1-112-112S258.1 32 320 32s112 50.1 112 112-50.1 112-112 112zm-32 32h64c88.4 0 160 71.6 160 160v32c0 17.7-14.3 32-32 32H160c-17.7 0-32-14.3-32-32v-32c0-88.4 71.6-160 160-160zm-175 0h-9C46.6 288 0 334.6 0 393v23c0 17.7 14.3 32 32 32h84.5c-7-15.7-11-33.1-11-51.4v-42.6c0-42.3 22.8-79.3 56.4-99.3-15.5-1.7-31.5-2.7-48.9-2.7zm431 0c-17.4 0-33.4 1-48.9 2.7 33.6 20 56.4 57 56.4 99.3v42.6c0 18.3-4 35.7-11 51.4H608c17.7 0 32-14.3 32-32v-23c0-58.4-46.6-105-105-105h-9z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 2 -->
            <div class="col col-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-between align-center">
                            <div>
                                <div class="text-xs text-success">TOTAL DIVISI</div>
                                <div class="text-lg"><?= $total_divisi ?></div>
                                <div class="text-sm">Divisi</div>
                            </div>
                            <div class="icon-box bg-success-light">
                                <svg style="width:24px;height:24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="currentColor"><path d="M48 0C21.5 0 0 21.5 0 48V464c0 26.5 21.5 48 48 48h96V432c0-26.5 21.5-48 48-48s48 21.5 48 48v80h96c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H48zM64 240c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16v-32zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-32c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-32zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16h-32c-8.8 0-16-7.2-16-16v-32c0-8.8 7.2-16 16-16z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 3 -->
            <div class="col col-3">
                <div class="card">
                    <div class="card-body">
                        <?php $pct_triwulan = $total_pegawai > 0 ? round(($triwulan_current_made / $total_pegawai) * 100) : 0; ?>
                        <div class="d-flex justify-between align-center">
                            <div style="flex: 1; padding-right: 1rem;">
                                <div class="text-xs text-purple">SKP TRIWULAN</div>
                                <div class="text-lg"><?= $triwulan_current_made ?> / <?= $total_pegawai ?></div>
                                <div class="text-sm"><?= $pct_triwulan ?>% Selesai</div>
                                <div class="progress">
                                    <div class="progress-bar bg-purple" style="width: <?= $pct_triwulan ?>%"></div>
                                </div>
                                <div class="text-sm mt-2 text-purple">Belum: <?= max(0, $total_pegawai - $triwulan_current_made) ?> Pegawai</div>
                            </div>
                            <div class="icon-box bg-purple-light">
                                <svg style="width:24px;height:24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="currentColor"><path d="M192 0c-41.8 0-77.4 26.7-90.5 64H64C28.7 64 0 92.7 0 128V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H282.5C269.4 26.7 233.8 0 192 0zm0 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64zM112 192H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Card 4 -->
            <div class="col col-3">
                <div class="card">
                    <div class="card-body">
                        <?php $pct_tahunan = $total_pegawai > 0 ? round(($tahunan_current_made / $total_pegawai) * 100) : 0; ?>
                        <div class="d-flex justify-between align-center">
                            <div style="flex: 1; padding-right: 1rem;">
                                <div class="text-xs text-warning">SKP TAHUNAN</div>
                                <div class="text-lg"><?= $tahunan_current_made ?> / <?= $total_pegawai ?></div>
                                <div class="text-sm"><?= $pct_tahunan ?>% Selesai</div>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" style="width: <?= $pct_tahunan ?>%"></div>
                                </div>
                                <div class="text-sm mt-2" style="color: #e74a3b;">Belum: <?= max(0, $total_pegawai - $tahunan_current_made) ?> Pegawai</div>
                            </div>
                            <div class="icon-box bg-warning-light">
                                <svg style="width:24px;height:24px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="currentColor"><path d="M192 0c-41.8 0-77.4 26.7-90.5 64H64C28.7 64 0 92.7 0 128V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V128c0-35.3-28.7-64-64-64H282.5C269.4 26.7 233.8 0 192 0zm0 64a32 32 0 1 1 0 64 32 32 0 1 1 0-64zM112 192H272c8.8 0 16 7.2 16 16s-7.2 16-16 16H112c-8.8 0-16-7.2-16-16s7.2-16 16-16z"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2 -->
        <div class="row">
            <div class="col col-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Jumlah Pegawai per Divisi</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="barDivisi"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col col-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Status SKP Triwulan (Periode Ini)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie" style="display: flex; align-items: center; justify-content: space-between; height: 250px; width: 100%;">
                            <div style="width: 50%; height: 100%; position: relative;">
                                <canvas id="doughnutTriwulan"></canvas>
                            </div>
                            <div style="width: 50%; padding-left: 0.5rem; display: flex; flex-direction: column; gap: 12px; line-height: 1.3;">
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 12px; height: 12px; border-radius: 2px; background-color: #1cc88a; margin-top: 4px; flex-shrink: 0;"></div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #5a5c69;">Sudah Membuat SKP</span>
                                        <span style="font-size: 0.9rem; font-weight: 800; color: #2e2f37; margin-top: 2px;">
                                            <?= $triwulan_current_made ?> <span style="font-weight: 600; color: #858796; font-size: 0.85rem;">(<?= $pct_triwulan ?>%)</span>
                                        </span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 12px; height: 12px; border-radius: 2px; background-color: #e74a3b; margin-top: 4px; flex-shrink: 0;"></div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #5a5c69;">Belum Membuat SKP</span>
                                        <span style="font-size: 0.9rem; font-weight: 800; color: #2e2f37; margin-top: 2px;">
                                            <?= max(0, $total_pegawai - $triwulan_current_made) ?> <span style="font-weight: 600; color: #858796; font-size: 0.85rem;">(<?= 100 - $pct_triwulan ?>%)</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="text-align:center; font-size:1rem; margin-top:1rem; padding: 0.5rem; background:#eef2fd; border-radius:0.35rem; color:#4e73df;">
                            ⏱️ Batas akhir pengisian: <strong>Akhir Triwulan <?= $current_triwulan ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3 -->
        <div class="row">
            <div class="col col-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Status SKP Tahunan (Periode Ini)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-pie" style="display: flex; align-items: center; justify-content: space-between; height: 250px; width: 100%;">
                            <div style="width: 50%; height: 100%; position: relative;">
                                <canvas id="doughnutTahunan"></canvas>
                            </div>
                            <div style="width: 50%; padding-left: 0.5rem; display: flex; flex-direction: column; gap: 12px; line-height: 1.3;">
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 12px; height: 12px; border-radius: 2px; background-color: #1cc88a; margin-top: 4px; flex-shrink: 0;"></div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #5a5c69;">Sudah Membuat SKP</span>
                                        <span style="font-size: 0.9rem; font-weight: 800; color: #2e2f37; margin-top: 2px;">
                                            <?= $tahunan_current_made ?> <span style="font-weight: 600; color: #858796; font-size: 0.85rem;">(<?= $pct_tahunan ?>%)</span>
                                        </span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 12px; height: 12px; border-radius: 2px; background-color: #e74a3b; margin-top: 4px; flex-shrink: 0;"></div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #5a5c69;">Belum Membuat SKP</span>
                                        <span style="font-size: 0.9rem; font-weight: 800; color: #2e2f37; margin-top: 2px;">
                                            <?= max(0, $total_pegawai - $tahunan_current_made) ?> <span style="font-weight: 600; color: #858796; font-size: 0.85rem;">(<?= 100 - $pct_tahunan ?>%)</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="text-align:center; font-size:1rem; margin-top:1rem; padding: 0.5rem; background:#fff3cd; color:#856404; border-radius:0.35rem;">
                            ⏱️ Batas akhir pengisian: <strong>31 Desember <?= $tahun_sekarang ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col col-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Perbandingan Penyelesaian SKP</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="lineTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col col-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Status Evaluasi SKP Triwulan</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $pct_selesai = $status_total_made > 0 ? round(($status_selesai_count / $status_total_made) * 100) : 0;
                        $pct_proses = $status_total_made > 0 ? round(($status_proses_count / $status_total_made) * 100) : 0;
                        $pct_draft = $status_total_made > 0 ? round(($status_draft_count / $status_total_made) * 100) : 0;
                        ?>
                        <div class="chart-pie" style="display: flex; align-items: center; justify-content: space-between; height: 250px; width: 100%;">
                            <div style="width: 50%; height: 100%; position: relative;">
                                <canvas id="doughnutStatusEvaluasi"></canvas>
                            </div>
                            <div style="width: 50%; padding-left: 0.5rem; display: flex; flex-direction: column; gap: 8px; line-height: 1.3;">
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 12px; height: 12px; border-radius: 2px; background-color: #1cc88a; margin-top: 4px; flex-shrink: 0;"></div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #5a5c69;">Selesai Evaluasi</span>
                                        <span style="font-size: 0.9rem; font-weight: 800; color: #2e2f37; margin-top: 2px;">
                                            <?= $status_selesai_count ?> <span style="font-weight: 600; color: #858796; font-size: 0.85rem;">(<?= $pct_selesai ?>%)</span>
                                        </span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 12px; height: 12px; border-radius: 2px; background-color: #f6c23e; margin-top: 4px; flex-shrink: 0;"></div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #5a5c69;">Proses Evaluasi</span>
                                        <span style="font-size: 0.9rem; font-weight: 800; color: #2e2f37; margin-top: 2px;">
                                            <?= $status_proses_count ?> <span style="font-weight: 600; color: #858796; font-size: 0.85rem;">(<?= $pct_proses ?>%)</span>
                                        </span>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: flex-start; gap: 8px;">
                                    <div style="width: 12px; height: 12px; border-radius: 2px; background-color: #4e73df; margin-top: 4px; flex-shrink: 0;"></div>
                                    <div style="display: flex; flex-direction: column;">
                                        <span style="font-size: 0.9rem; font-weight: 700; color: #5a5c69;">Draft</span>
                                        <span style="font-size: 0.9rem; font-weight: 800; color: #2e2f37; margin-top: 2px;">
                                            <?= $status_draft_count ?> <span style="font-weight: 600; color: #858796; font-size: 0.85rem;">(<?= $pct_draft ?>%)</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="text-align:center; font-size:1rem; margin-top:1rem; padding: 0.5rem; background:#eef2fd; border-radius:0.35rem; color:#4e73df;">
                            Total SKP Dibuat: <strong><?= $status_total_made ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 4 -->
        <div class="row">
            <div class="col col-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title">Progress SKP Triwulan (Pembuatan vs Evaluasi Atasan)</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="height: 300px;">
                            <canvas id="barProgressTriwulan"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 5 -->
        <div class="row">
            <!-- List 1: Pegawai Belum Membuat SKP -->
            <div class="col col-6">
                <div class="card">
                    <div class="card-header d-flex justify-between align-center">
                        <h6 class="card-title">Pegawai Belum Membuat SKP</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach($belum_triwulan_list as $emp): ?>
                            <li class="list-group-item">
                                <div class="avatar"><?= strtoupper(substr($emp['NAMA'], 0, 1)) ?></div>
                                <div class="emp-info">
                                    <p class="emp-name"><?= htmlspecialchars($emp['NAMA']) ?></p>
                                    <p class="emp-role"><?= htmlspecialchars($emp['JABATAN']) ?></p>
                                </div>
                                <?php if(!empty($emp['UNIT_KERJA'])): ?>
                                <div class="badge-divisi">
                                    <?= htmlspecialchars(substr($emp['UNIT_KERJA'], 0, 15)) ?>...
                                </div>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                            <?php if(empty($belum_triwulan_list)): ?>
                            <li class="list-group-item text-center" style="justify-content:center;">Semua pegawai telah mengisi SKP! 🎉</li>
                            <?php endif; ?>
                        </ul>

                        <?php if (isset($total_pages_belum) && $total_pages_belum > 1): ?>
                        <div class="pagination" style="margin-top: 1.5rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                            <?php 
                            $filter_param = (isset($_GET['filter_periode']) ? '&filter_periode=' . urlencode($_GET['filter_periode']) : '') . (isset($_GET['page_belum_eval']) ? '&page_belum_eval=' . urlencode($_GET['page_belum_eval']) : '');
                            if ($page_belum > 1): ?>
                                <a href="?page=<?= $page_belum - 1 ?><?= $filter_param ?>#dashboard-section" style="padding: 0.3rem 0.7rem; border: 1px solid #d1d3e2; border-radius: 0.3rem; text-decoration: none; color: #4e73df; font-weight: bold; font-size: 0.9rem;">&laquo; Prev</a>
                            <?php endif; ?>
                            
                            <span style="padding: 0.3rem 0.8rem; border: 1px solid #4e73df; background: #4e73df; color: white; border-radius: 0.3rem; font-weight: bold; font-size: 0.9rem;">
                                <?= $page_belum ?> / <?= $total_pages_belum ?>
                            </span>

                            <?php if ($page_belum < $total_pages_belum): ?>
                                <a href="?page=<?= $page_belum + 1 ?><?= $filter_param ?>#dashboard-section" style="padding: 0.3rem 0.7rem; border: 1px solid #d1d3e2; border-radius: 0.3rem; text-decoration: none; color: #4e73df; font-weight: bold; font-size: 0.9rem;">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- List 2: Pegawai SKP Triwulan Belum Dievaluasi -->
            <div class="col col-6">
                <div class="card">
                    <div class="card-header d-flex justify-between align-center">
                        <h6 class="card-title">Pegawai SKP Triwulan Belum Dievaluasi</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach($belum_eval_list as $emp): ?>
                            <li class="list-group-item">
                                <div class="avatar"><?= strtoupper(substr($emp['NAMA'], 0, 1)) ?></div>
                                <div class="emp-info">
                                    <p class="emp-name"><?= htmlspecialchars($emp['NAMA']) ?></p>
                                    <p class="emp-role"><?= htmlspecialchars($emp['JABATAN']) ?></p>
                                </div>
                                <?php if(!empty($emp['UNIT_KERJA'])): ?>
                                <div class="badge-divisi">
                                    <?= htmlspecialchars(substr($emp['UNIT_KERJA'], 0, 15)) ?>...
                                </div>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                            <?php if(empty($belum_eval_list)): ?>
                            <li class="list-group-item text-center" style="justify-content:center;">Semua SKP telah dievaluasi! 🎉</li>
                            <?php endif; ?>
                        </ul>

                        <?php if (isset($total_pages_belum_eval) && $total_pages_belum_eval > 1): ?>
                        <div class="pagination" style="margin-top: 1.5rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                            <?php 
                            $filter_param_eval = (isset($_GET['filter_periode']) ? '&filter_periode=' . urlencode($_GET['filter_periode']) : '') . (isset($_GET['page']) ? '&page=' . urlencode($_GET['page']) : '');
                            if ($page_belum_eval > 1): ?>
                                <a href="?page_belum_eval=<?= $page_belum_eval - 1 ?><?= $filter_param_eval ?>#dashboard-section" style="padding: 0.3rem 0.7rem; border: 1px solid #d1d3e2; border-radius: 0.3rem; text-decoration: none; color: #4e73df; font-weight: bold; font-size: 0.9rem;">&laquo; Prev</a>
                            <?php endif; ?>
                            
                            <span style="padding: 0.3rem 0.8rem; border: 1px solid #4e73df; background: #4e73df; color: white; border-radius: 0.3rem; font-weight: bold; font-size: 0.9rem;">
                                <?= $page_belum_eval ?> / <?= $total_pages_belum_eval ?>
                            </span>

                            <?php if ($page_belum_eval < $total_pages_belum_eval): ?>
                                <a href="?page_belum_eval=<?= $page_belum_eval + 1 ?><?= $filter_param_eval ?>#dashboard-section" style="padding: 0.3rem 0.7rem; border: 1px solid #d1d3e2; border-radius: 0.3rem; text-decoration: none; color: #4e73df; font-weight: bold; font-size: 0.9rem;">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; padding: 3rem 0 2rem 0; clear: both;">
            <a href="#login-section" class="scroll-btn-up">
                <span class="scroll-btn-up-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </span>
                <span class="scroll-btn-up-text">Login</span>
            </a>
        </div>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Global Chart.js Font Size
        Chart.defaults.font.size = 14;
        
        const totalPegawai = <?= $total_pegawai ?>;
        
        // Data from PHP
        const divisiData = <?= json_encode($divisi_data) ?>;
        const triwulanMade = <?= $triwulan_current_made ?>;
        const tahunanMade = <?= $tahunan_current_made ?>;
        const trendTriwulan = <?= json_encode($trend_triwulan) ?>;
        const trendEvalTriwulan = <?= json_encode($trend_eval_triwulan) ?>;
        const statusDraft = <?= $status_draft_count ?>;
        const statusProses = <?= $status_proses_count ?>;
        const statusSelesai = <?= $status_selesai_count ?>;
        const statusTotal = <?= $status_total_made ?>;

        // Custom Plugin to exactly center text in Doughnut charts (respecting legend bounds)
        const centerTextPlugin = {
            id: 'centerText',
            beforeDraw: function(chart) {
                if (chart.config.type !== 'doughnut' || !chart.config.options.elements || !chart.config.options.elements.center) return;
                var ctx = chart.ctx;
                ctx.save();
                
                var width = chart.chartArea.right - chart.chartArea.left;
                var height = chart.chartArea.bottom - chart.chartArea.top;
                
                var centerX = chart.chartArea.left + width / 2;
                var centerY = chart.chartArea.top + height / 2;
                
                var text = chart.config.options.elements.center.text;
                var subText = chart.config.options.elements.center.subText;
                
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                
                ctx.font = "bold 3rem 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif";
                ctx.fillStyle = "#5a5c69";
                ctx.fillText(text, centerX, centerY - 10);
                
                ctx.font = "normal 1.1rem 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif";
                ctx.fillStyle = "#858796";
                ctx.fillText(subText, centerX, centerY + 25);
                
                ctx.restore();
            }
        };

        // Plugin to draw data labels on Bar charts
        const barLabelsPlugin = {
            id: 'barLabels',
            afterDatasetsDraw: function(chart) {
                if (chart.config.type !== 'bar') return;
                var ctx = chart.ctx;
                chart.data.datasets.forEach(function(dataset, i) {
                    var meta = chart.getDatasetMeta(i);
                    if (!meta.hidden) {
                        meta.data.forEach(function(element, index) {
                            var dataString = dataset.data[index].toString();
                            if (dataString === '0') return; // Hide zeros
                            ctx.fillStyle = '#858796';
                            ctx.font = "bold 1rem 'Nunito', sans-serif";
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'bottom';
                            var padding = 2;
                            var position = element.tooltipPosition();
                            ctx.fillText(dataString, position.x, position.y - padding);
                        });
                    }
                });
            }
        };

        Chart.register(centerTextPlugin, barLabelsPlugin);

        // --- 1. Bar Chart: Pegawai per Divisi ---
        const barCtx = document.getElementById('barDivisi');
        if (barCtx) {
            new Chart(barCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: divisiData.map(d => d.nama.length > 15 ? d.nama.substring(0, 15) + '...' : d.nama),
                    datasets: [{
                        label: 'Pegawai',
                        data: divisiData.map(d => d.jumlah),
                        backgroundColor: '#4e73df',
                        hoverBackgroundColor: '#2e59d9',
                        borderColor: '#4e73df',
                        borderRadius: 4,
                        barPercentage: 0.3
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#eaecf4' } }
                    }
                }
            });
        }

        // --- 2. Doughnut Chart: SKP Triwulan ---
        const dTriCtx = document.getElementById('doughnutTriwulan');
        if (dTriCtx) {
            new Chart(dTriCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Sudah Membuat SKP', 'Belum Membuat SKP'],
                    datasets: [{
                        data: [triwulanMade, Math.max(0, totalPegawai - triwulanMade)],
                        backgroundColor: ['#1cc88a', '#e74a3b'],
                        hoverBackgroundColor: ['#17a673', '#e02d1b'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '75%',
                    elements: {
                        center: { text: totalPegawai.toString(), subText: 'Total Pegawai' }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // --- 3. Doughnut Chart: SKP Tahunan ---
        const dTahCtx = document.getElementById('doughnutTahunan');
        if (dTahCtx) {
            new Chart(dTahCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Sudah Membuat SKP', 'Belum Membuat SKP'],
                    datasets: [{
                        data: [tahunanMade, Math.max(0, totalPegawai - tahunanMade)],
                        backgroundColor: ['#1cc88a', '#e74a3b'],
                        hoverBackgroundColor: ['#17a673', '#e02d1b'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '75%',
                    elements: {
                        center: { text: totalPegawai.toString(), subText: 'Total Pegawai' }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // --- 4. Line Chart: Trend Penyelesaian ---
        const lineCtx = document.getElementById('lineTrend');
        if (lineCtx) {
            new Chart(lineCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: ['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'],
                    datasets: [{
                        label: 'SKP Triwulan Selesai',
                        data: [trendTriwulan[1], trendTriwulan[2], trendTriwulan[3], trendTriwulan[4]],
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        pointRadius: 4,
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#fff',
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#eaecf4' } }
                    }
                }
            });
        }

        // --- 5. Grouped Bar Chart: Progress Triwulan ---
        const barProgCtx = document.getElementById('barProgressTriwulan');
        if (barProgCtx) {
            new Chart(barProgCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: ['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'],
                    datasets: [
                        {
                            label: 'Sudah Membuat SKP',
                            data: [trendTriwulan[1], trendTriwulan[2], trendTriwulan[3], trendTriwulan[4]],
                            backgroundColor: '#4e73df',
                            borderRadius: 4,
                            barPercentage: 0.3,
                            categoryPercentage: 0.5
                        },
                        {
                            label: 'Selesai Dievaluasi',
                            data: [trendEvalTriwulan[1], trendEvalTriwulan[2], trendEvalTriwulan[3], trendEvalTriwulan[4]],
                            backgroundColor: '#1cc88a',
                            borderRadius: 4,
                            barPercentage: 0.3,
                            categoryPercentage: 0.5
                        }
                    ]
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { position: 'top', labels: { boxWidth: 12, padding: 20 } } 
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { borderDash: [2, 2], color: '#eaecf4' } }
                    }
                }
            });
        }

        // --- 6. Doughnut Chart: Status Evaluasi SKP ---
        const dEvalCtx = document.getElementById('doughnutStatusEvaluasi');
        if (dEvalCtx) {
            new Chart(dEvalCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Selesai Evaluasi', 'Proses Evaluasi', 'Draft'],
                    datasets: [{
                        data: [statusSelesai, statusProses, statusDraft],
                        backgroundColor: ['#1cc88a', '#f6c23e', '#4e73df'],
                        hoverBackgroundColor: ['#17a673', '#dda20a', '#2e59d9'],
                        hoverBorderColor: "rgba(234, 236, 244, 1)",
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    cutout: '75%',
                    elements: {
                        center: { text: statusTotal.toString(), subText: 'SKP Dibuat' }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
