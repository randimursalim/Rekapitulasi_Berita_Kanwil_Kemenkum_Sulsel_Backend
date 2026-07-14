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

// Get user data for sidebar
$user_data = [
    'nama' => $_SESSION['nama'] ?? 'Nama User',
    'nip' => $_SESSION['nip'] ?? 'NIP User', 
    'jabatan' => $_SESSION['jabatan'] ?? 'Jabatan User'
];

// Check if user is manager
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
}

// Get manager name for filtering
$manager_name = '';
if ($is_atasan) {
    $manager_sql = "SELECT NAMA FROM Pegawai WHERE NIP = ? LIMIT 1";
    $manager_stmt = $conn->prepare($manager_sql);
    $manager_stmt->bind_param('s', $user_nip);
    $manager_stmt->execute();
    $manager_result = $manager_stmt->get_result();
    if ($manager_result && $manager_result->num_rows > 0) {
        $manager_row = $manager_result->fetch_assoc();
        $manager_name = $manager_row['NAMA'] ?? '';
    }
    $manager_stmt->close();
}

// Handle generate summary action
$success_message = '';
$error_message = '';

if (isset($_POST['generate_summary'])) {
    $tahun = $_POST['tahun'] ?? date('Y');
    
    try {
        // Start transaction
        $conn->autocommit(false);
        
        // Check if summary already exists for this year and user
        $check_sql = "SELECT COUNT(*) as count FROM skp_akhir_pegawai WHERE TAHUN = ? AND NIP = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('is', $tahun, $user_nip);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $existing_count = $check_result->fetch_assoc()['count'] ?? 0;
        $check_stmt->close();
        
        if ($existing_count > 0) {
            // Data for this year already exists: do nothing, leave data unchanged
            $success_message = "Ringkasan SKP tahunan untuk tahun $tahun sudah tersedia. Data tidak diubah.";
            $conn->commit();
        } else {
        
        {
            // Get all employees with complete 4-triwulan evaluation for the year
            $complete_skp_sql = "
                SELECT 
                    s.NIP,
                    s.NAMA,
                    s.NAMA_ATASAN_LANGSUNG,
                    s.NIP_ATASAN_LANGSUNG,
                    s.TAHUN,
                    s.RHK_PIMPINAN_INTERV,
                    s.RENCANA_HASIL_KERJA,
                    s.ASPEK,
                    s.INDIKATOR_KINERJA_INDIVIDU,
                    s.JENIS_KINERJA,
                    s.SATUAN,
                    s.TANGGAL_INPUT_SKP,
                    s.TANGGAL_EVALUASI_SKP,
                    s.UMPAN_BALIK_STICKER,
                    s.id_skp_global,
                    s.id_skp,
                    s.TRIWULAN,
                    s.TARGET,
                    s.REALISASI_BERDASARKAN_BUKTI_DUKUNG,
                    s.UMPAN_BALIK_DENGAN_BUKTI_DUKUNG
                FROM skp_pegawai s
                INNER JOIN (
                    SELECT NIP, TAHUN, COUNT(DISTINCT TRIWULAN) as triwulan_count
                    FROM skp_pegawai 
                    WHERE STATUS IN ('SELESAI EVALUASI', 'SELESAI') AND TAHUN = ? AND NIP = ?
                    GROUP BY NIP, TAHUN
                    HAVING triwulan_count = 4
                ) complete_skp ON s.NIP = complete_skp.NIP AND s.TAHUN = complete_skp.TAHUN
                WHERE s.TAHUN = ? AND s.STATUS IN ('SELESAI EVALUASI', 'SELESAI') AND s.NIP = ?
                ORDER BY s.NIP, s.TRIWULAN, s.id_skp
            ";
            
            $complete_stmt = $conn->prepare($complete_skp_sql);
            $complete_stmt->bind_param('isis', $tahun, $user_nip, $tahun, $user_nip);
            $complete_stmt->execute();
            $complete_result = $complete_stmt->get_result();
            
            $inserted_count = 0;
            $current_id_skp = 1;
            
            // Get max ID_SKP for auto-increment
            $max_id_sql = "SELECT MAX(ID_SKP) as max_id FROM skp_akhir_pegawai";
            $max_result = $conn->query($max_id_sql);
            $max_row = $max_result->fetch_assoc();
            $current_id_skp = ($max_row['max_id'] ?? 0) + 1;
            
            // Get max ID_SKP_GLOBAL for auto-increment
            $max_global_sql = "SELECT MAX(CAST(ID_SKP_GLOBAL AS UNSIGNED)) as max_global FROM skp_akhir_pegawai";
            $max_global_result = $conn->query($max_global_sql);
            $max_global_row = $max_global_result->fetch_assoc();
            $current_id_skp_global = ($max_global_row['max_global'] ?? 0) + 1;
            
            // Group data by employee and position within triwulan
            $grouped_data = [];
            while ($row = $complete_result->fetch_assoc()) {
                $nip = $row['NIP'];
                $triwulan = $row['TRIWULAN'];
                
                if (!isset($grouped_data[$nip])) {
                    $grouped_data[$nip] = [
                        'employee_info' => $row,
                        'triwulans' => []
                    ];
                }
                
                if (!isset($grouped_data[$nip]['triwulans'][$triwulan])) {
                    $grouped_data[$nip]['triwulans'][$triwulan] = [];
                }
                $grouped_data[$nip]['triwulans'][$triwulan][] = $row;
            }
            
            // Process each employee's grouped data
            foreach ($grouped_data as $nip => $employee_data) {
                $employee_info = $employee_data['employee_info'];
                
                // Fetch Pegawai static data
                $pegawai_sql = "SELECT PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA FROM Pegawai WHERE NIP = ? LIMIT 1";
                $pegawai_stmt = $conn->prepare($pegawai_sql);
                $pegawai_stmt->bind_param('s', $nip);
                $pegawai_stmt->execute();
                $pegawai_res = $pegawai_stmt->get_result();
                $pegawai_data = $pegawai_res->fetch_assoc() ?: [];
                $pangkatPegawai = $pegawai_data['PANGKAT_GOL_RUANG'] ?? '';
                $jabatanPegawai = $pegawai_data['JABATAN'] ?? '';
                $unitKerjaPegawai = $pegawai_data['UNIT_KERJA'] ?? '';
                $satuanKerjaPegawai = $pegawai_data['SATUAN_KERJA'] ?? '';
                $pegawai_stmt->close();
                
                // Assign unique id_skp_global for this employee
                $employee_id_skp_global = $current_id_skp_global;
                $current_id_skp_global++; // Increment for next employee
                
                // Group by position across all triwulans
                $position_groups = [];
                
                // For each triwulan, assign position numbers based on order
                foreach ($employee_data['triwulans'] as $triwulan => $entries) {
                    // Sort entries by id_skp to ensure consistent ordering
                    usort($entries, function($a, $b) {
                        return $a['id_skp'] - $b['id_skp'];
                    });
                    
                    // Assign position numbers (1, 2, 3, etc.) based on order within triwulan
                    foreach ($entries as $index => $entry) {
                        $position_num = $index + 1; // Position 1, 2, 3, etc.
                        
                        if (!isset($position_groups[$position_num])) {
                            $position_groups[$position_num] = [];
                        }
                        $position_groups[$position_num][] = $entry;
                    }
                }
                
                // Create summary rows for each position
                foreach ($position_groups as $position_num => $entries) {
                    // Debug: Log position grouping
                    error_log("Position $position_num has " . count($entries) . " entries");
                    foreach ($entries as $idx => $entry) {
                        error_log("  Entry $idx: id_skp=" . $entry['id_skp'] . ", triwulan=" . $entry['TRIWULAN'] . ", jenis_kinerja=" . $entry['JENIS_KINERJA']);
                    }
                    
                    // Sum TARGET and REALISASI for this position across all triwulans
                    $total_target = 0;
                    $total_realisasi = 0;
                    $combined_umpan_balik = [];
                    
                    foreach ($entries as $entry) {
                        $total_target += $entry['TARGET'];
                        $total_realisasi += $entry['REALISASI_BERDASARKAN_BUKTI_DUKUNG'];
                        if (!empty($entry['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'])) {
                            $combined_umpan_balik[] = $entry['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'];
                        }
                    }
                    
                    $sum_data = [
                        'total_target' => $total_target,
                        'total_realisasi' => $total_realisasi,
                        'combined_umpan_balik' => implode(' | ', $combined_umpan_balik)
                    ];
                    
                    // Use the first entry for other details
                    $row = $entries[0];
                
                // Debug: Check what JENIS_KINERJA we're getting from the first entry
                error_log("Employee JENIS_KINERJA: " . $row['JENIS_KINERJA'] . " (type: " . gettype($row['JENIS_KINERJA']) . ")");
                
                // Insert into skp_akhir_pegawai - Use two-step approach to avoid the issue
                // Step 1: Insert with minimal fields (like the working test)
                $insert_sql = "INSERT INTO skp_akhir_pegawai (ID_SKP, NAMA, NIP, JENIS_KINERJA, TAHUN, STATUS) VALUES (?, ?, ?, ?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                
                // Prepare empty values for rating and feedback columns
                $empty_rating_perilaku = '';
                $empty_predikat = '';
                $empty_rating_hasil = '';
                $empty_umpan_balik_sticker = '';
                $empty_umpan_balik_dengan_bukti = '';
                $status_draft = 'draft';
                
                // Step 1: Insert basic data
                $insert_stmt->bind_param('isssis', $current_id_skp, $row['NAMA'], $row['NIP'], $row['JENIS_KINERJA'], $row['TAHUN'], $status_draft);
                
                if ($insert_stmt->execute()) {
                    error_log("✅ Step 1 INSERT successful for ID_SKP=" . $current_id_skp . " with JENIS_KINERJA=" . $row['JENIS_KINERJA'] . " for employee " . $row['NAMA'] . " (" . $row['NIP'] . ")");
                    $insert_stmt->close();
                    
                    // Step 2: Update with remaining fields
                    $update_sql = "UPDATE skp_akhir_pegawai SET 
                        NAMA_ATASAN_LANGSUNG = ?, NIP_ATASAN_LANGSUNG = ?,
                        RHK_PIMPINAN_INTERV = ?, RENCANA_HASIL_KERJA = ?, ASPEK = ?, INDIKATOR_KINERJA_INDIVIDU = ?,
                        TARGET = ?, REALISASI_BERDASARKAN_BUKTI_DUKUNG = ?, UMPAN_BALIK_DENGAN_BUKTI_DUKUNG = ?,
                        TANGGAL_INPUT_SKP = NOW() + INTERVAL 8 HOUR, TANGGAL_EVALUASI_SKP = NOW() + INTERVAL 8 HOUR, ID_SKP_GLOBAL = ?,
                        RATING_PERILAKU_KERJA = ?, PREDIKAT_KINERJA_PEGAWAI = ?, UMPAN_BALIK_STICKER = ?, RATING_HASIL_KERJA = ?, SATUAN = ?,
                        PANGKAT_GOL_RUANG = ?, JABATAN = ?, UNIT_KERJA = ?, SATUAN_KERJA = ?
                        WHERE ID_SKP = ?";
                    
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param('ssssssiisisssssssssi',
                        $row['NAMA_ATASAN_LANGSUNG'], $row['NIP_ATASAN_LANGSUNG'],
                        $row['RHK_PIMPINAN_INTERV'], $row['RENCANA_HASIL_KERJA'], $row['ASPEK'], $row['INDIKATOR_KINERJA_INDIVIDU'],
                        $sum_data['total_target'], $sum_data['total_realisasi'], $empty_umpan_balik_dengan_bukti,
                        $employee_id_skp_global,
                        $empty_rating_perilaku, $empty_predikat, $empty_umpan_balik_sticker, $empty_rating_hasil, $row['SATUAN'],
                        $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai,
                        $current_id_skp
                    );
                    
                    if ($update_stmt->execute()) {
                        error_log("✅ Step 2 UPDATE successful for ID_SKP=" . $current_id_skp . " with ID_SKP_GLOBAL=" . $employee_id_skp_global);
                        $inserted_count++;
                        $current_id_skp++;
                    } else {
                        error_log("❌ Step 2 UPDATE failed: " . $update_stmt->error);
                    }
                    $update_stmt->close();
                } else {
                    error_log("❌ Step 1 INSERT failed: " . $insert_stmt->error);
                    $insert_stmt->close();
                }
                }
            }
            $complete_stmt->close();
            
            // Insert perilaku data for each employee
            $perilaku_inserted_count = 0;
            foreach ($grouped_data as $nip => $employee_data) {
                $employee_info = $employee_data['employee_info'];
                
                // Fetch Pegawai static data
                $pegawai_sql = "SELECT PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA FROM Pegawai WHERE NIP = ? LIMIT 1";
                $pegawai_stmt = $conn->prepare($pegawai_sql);
                $pegawai_stmt->bind_param('s', $nip);
                $pegawai_stmt->execute();
                $pegawai_res = $pegawai_stmt->get_result();
                $pegawai_data = $pegawai_res->fetch_assoc() ?: [];
                $pangkatPegawai = $pegawai_data['PANGKAT_GOL_RUANG'] ?? '';
                $jabatanPegawai = $pegawai_data['JABATAN'] ?? '';
                $unitKerjaPegawai = $pegawai_data['UNIT_KERJA'] ?? '';
                $satuanKerjaPegawai = $pegawai_data['SATUAN_KERJA'] ?? '';
                $pegawai_stmt->close();
                
                // Get perilaku data from triwulan 1 for this employee (only for logged-in user)
                $perilaku_sql = "SELECT * FROM skp_perilaku_pegawai WHERE NIP = ? AND TAHUN = ? AND TRIWULAN = 1 AND NIP = ? LIMIT 1";
                $perilaku_stmt = $conn->prepare($perilaku_sql);
                $perilaku_stmt->bind_param('sis', $nip, $tahun, $user_nip);
                $perilaku_stmt->execute();
                $perilaku_result = $perilaku_stmt->get_result();
                
                if ($perilaku_result && $perilaku_result->num_rows > 0) {
                    $perilaku_row = $perilaku_result->fetch_assoc();
                    
                    // Find the employee's ID_SKP_GLOBAL from the main processing
                    $employee_id_skp_global = null;
                    foreach ($grouped_data as $emp_nip => $emp_data) {
                        if ($emp_nip === $nip) {
                            // Get the global ID that was assigned to this employee
                            $global_id_sql = "SELECT ID_SKP_GLOBAL FROM skp_akhir_pegawai WHERE NIP = ? AND TAHUN = ? LIMIT 1";
                            $global_stmt = $conn->prepare($global_id_sql);
                            $global_stmt->bind_param('si', $nip, $tahun);
                            $global_stmt->execute();
                            $global_result = $global_stmt->get_result();
                            if ($global_result && $global_result->num_rows > 0) {
                                $global_row = $global_result->fetch_assoc();
                                $employee_id_skp_global = $global_row['ID_SKP_GLOBAL'];
                            }
                            $global_stmt->close();
                            break;
                        }
                    }
                    
                    if ($employee_id_skp_global) {
                        // Check if perilaku record already exists for this employee and year
                        $check_perilaku_sql = "SELECT COUNT(*) as count FROM skp_akhir_perilaku_pegawai WHERE NIP = ? AND TAHUN = ?";
                        $check_perilaku_stmt = $conn->prepare($check_perilaku_sql);
                        $check_perilaku_stmt->bind_param('si', $nip, $tahun);
                        $check_perilaku_stmt->execute();
                        $check_perilaku_result = $check_perilaku_stmt->get_result();
                        $perilaku_exists = $check_perilaku_result->fetch_assoc()['count'] > 0;
                        $check_perilaku_stmt->close();
                        
                        if (!$perilaku_exists) {
                            // Insert into skp_akhir_perilaku_pegawai
                            $insert_perilaku_sql = "INSERT INTO skp_akhir_perilaku_pegawai (
                            NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG,
                            PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA,
                            PERILAKU_KERJA_BERORIENTASI_PELAYANAN, PERILAKU_KERJA_AKUNTABEL, PERILAKU_KERJA_KOMPETEN,
                            PERILAKU_KERJA_HARMONIS, PERILAKU_KERJA_LOYAL, PERILAKU_KERJA_ADAPTIF, PERILAKU_KERJA_KOLABORATIF,
                            EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN, EKSPEKTASI_PIMPINAN_AKUNTABEL, EKSPEKTASI_PIMPINAN_KOMPETEN,
                            EKSPEKTASI_PIMPINAN_HARMONIS, EKSPEKTASI_PIMPINAN_LOYAL, EKSPEKTASI_PIMPINAN_ADAPTIF, EKSPEKTASI_PIMPINAN_KOLABORATIF,
                            UMPAN_BALIK_BERORIENTASI_PELAYANAN, UMPAN_BALIK_AKUNTABEL, UMPAN_BALIK_KOMPETEN,
                            UMPAN_BALIK_HARMONIS, UMPAN_BALIK_LOYAL, UMPAN_BALIK_ADAPTIF, UMPAN_BALIK_KOLABORATIF,
                            TAHUN, STATUS, TANGGAL_INPUT_SKP, ID_SKP_GLOBAL
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 8 HOUR, ?)";
                        
                        // Prepare variables for bind_param (all values must be variables, not literals)
                        $empty_umpan_balik_1 = '';
                        $empty_umpan_balik_2 = '';
                        $empty_umpan_balik_3 = '';
                        $empty_umpan_balik_4 = '';
                        $empty_umpan_balik_5 = '';
                        $empty_umpan_balik_6 = '';
                        $empty_umpan_balik_7 = '';
                        $status_draft = 'draft';
                        
                        $insert_perilaku_stmt = $conn->prepare($insert_perilaku_sql);
                        $insert_perilaku_stmt->bind_param('sssssssssssssssssssssssssssssisi',
                            $perilaku_row['NAMA'], $perilaku_row['NIP'], $perilaku_row['NAMA_ATASAN_LANGSUNG'], $perilaku_row['NIP_ATASAN_LANGSUNG'],
                            $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai,
                            $perilaku_row['PERILAKU_KERJA_BERORIENTASI_PELAYANAN'], $perilaku_row['PERILAKU_KERJA_AKUNTABEL'], $perilaku_row['PERILAKU_KERJA_KOMPETEN'],
                            $perilaku_row['PERILAKU_KERJA_HARMONIS'], $perilaku_row['PERILAKU_KERJA_LOYAL'], $perilaku_row['PERILAKU_KERJA_ADAPTIF'], $perilaku_row['PERILAKU_KERJA_KOLABORATIF'],
                            $perilaku_row['EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN'], $perilaku_row['EKSPEKTASI_PIMPINAN_AKUNTABEL'], $perilaku_row['EKSPEKTASI_PIMPINAN_KOMPETEN'],
                            $perilaku_row['EKSPEKTASI_PIMPINAN_HARMONIS'], $perilaku_row['EKSPEKTASI_PIMPINAN_LOYAL'], $perilaku_row['EKSPEKTASI_PIMPINAN_ADAPTIF'], $perilaku_row['EKSPEKTASI_PIMPINAN_KOLABORATIF'],
                            $empty_umpan_balik_1, $empty_umpan_balik_2, $empty_umpan_balik_3, $empty_umpan_balik_4, $empty_umpan_balik_5, $empty_umpan_balik_6, $empty_umpan_balik_7,
                            $tahun, $status_draft, $employee_id_skp_global
                        );
                        
                        if ($insert_perilaku_stmt->execute()) {
                            error_log("✅ Perilaku data inserted for employee " . $perilaku_row['NAMA'] . " (" . $nip . ") with ID_SKP_GLOBAL=" . $employee_id_skp_global);
                            $perilaku_inserted_count++;
                        } else {
                            error_log("❌ Failed to insert perilaku data for employee " . $nip . ": " . $insert_perilaku_stmt->error);
                        }
                        $insert_perilaku_stmt->close();
                        } else {
                            error_log("⚠️ Perilaku data already exists for employee " . $nip . " for year " . $tahun . ", skipping insertion");
                        }
                    }
                } else {
                    error_log("⚠️ No perilaku data found for employee " . $nip . " in triwulan 1 for year " . $tahun);
                }
                $perilaku_stmt->close();
            }
            
            $conn->commit();
            
            // Debug: Verify what was actually inserted
            $verify_sql = "SELECT ID_SKP, NAMA, JENIS_KINERJA FROM skp_akhir_pegawai WHERE TAHUN = ? ORDER BY ID_SKP DESC LIMIT 5";
            $verify_stmt = $conn->prepare($verify_sql);
            $verify_stmt->bind_param('i', $tahun);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            error_log("🔍 Verification - Last 5 inserted records:");
            while ($verify_row = $verify_result->fetch_assoc()) {
                error_log("  ID_SKP=" . $verify_row['ID_SKP'] . ", NAMA=" . $verify_row['NAMA'] . ", JENIS_KINERJA=" . $verify_row['JENIS_KINERJA']);
            }
            $verify_stmt->close();
            
            // Debug: Check table structure
            $structure_sql = "DESCRIBE skp_akhir_pegawai";
            $structure_result = $conn->query($structure_sql);
            error_log("🔍 Table structure for JENIS_KINERJA:");
            while ($structure_row = $structure_result->fetch_assoc()) {
                if ($structure_row['Field'] === 'JENIS_KINERJA') {
                    error_log("  Field: " . $structure_row['Field'] . ", Type: " . $structure_row['Type'] . ", Null: " . $structure_row['Null'] . ", Key: " . $structure_row['Key'] . ", Default: " . $structure_row['Default'] . ", Extra: " . $structure_row['Extra']);
                }
            }
            
            // Debug: Check for triggers on the table
            $trigger_sql = "SHOW TRIGGERS LIKE 'skp_akhir_pegawai'";
            $trigger_result = $conn->query($trigger_sql);
            error_log("🔍 Database triggers on skp_akhir_pegawai:");
            if ($trigger_result && $trigger_result->num_rows > 0) {
                while ($trigger_row = $trigger_result->fetch_assoc()) {
                    error_log("  Trigger: " . $trigger_row['Trigger'] . " - " . $trigger_row['Event'] . " - " . $trigger_row['Timing']);
                }
            } else {
                error_log("  No triggers found");
            }
            
            // Debug: Test simple INSERT to isolate the issue
            $test_sql = "INSERT INTO skp_akhir_pegawai (ID_SKP, NAMA, NIP, JENIS_KINERJA, TAHUN) VALUES (?, ?, ?, ?, ?)";
            $test_stmt = $conn->prepare($test_sql);
            $test_id = 999999;
            $test_nama = "TEST USER";
            $test_nip = "TEST123456789";
            $test_jenis = "kinerja utama";
            $test_tahun = $tahun;
            $test_stmt->bind_param('isssi', $test_id, $test_nama, $test_nip, $test_jenis, $test_tahun);
            if ($test_stmt->execute()) {
                error_log("🧪 Test INSERT successful with JENIS_KINERJA=kinerja utama");
                // Check what was actually inserted
                $test_check_sql = "SELECT ID_SKP, JENIS_KINERJA FROM skp_akhir_pegawai WHERE ID_SKP = ?";
                $test_check_stmt = $conn->prepare($test_check_sql);
                $test_check_stmt->bind_param('i', $test_id);
                $test_check_stmt->execute();
                $test_check_result = $test_check_stmt->get_result();
                $test_check_row = $test_check_result->fetch_assoc();
                error_log("🧪 Test result: ID_SKP=" . $test_check_row['ID_SKP'] . ", JENIS_KINERJA=" . $test_check_row['JENIS_KINERJA']);
                $test_check_stmt->close();
                
                // Clean up test record
                $delete_test_sql = "DELETE FROM skp_akhir_pegawai WHERE ID_SKP = ?";
                $delete_test_stmt = $conn->prepare($delete_test_sql);
                $delete_test_stmt->bind_param('i', $test_id);
                $delete_test_stmt->execute();
                $delete_test_stmt->close();
            } else {
                error_log("🧪 Test INSERT failed: " . $test_stmt->error);
            }
            $test_stmt->close();
            
            // Debug: Compare simple vs full test to identify the difference
            error_log("🔍 Comparing simple test vs full test:");
            error_log("  Simple test: Only 5 fields (ID_SKP, NAMA, NIP, JENIS_KINERJA, TAHUN)");
            error_log("  Full test: 22 fields including all columns");
            error_log("  Simple test result: JENIS_KINERJA=kinerja utama ✅");
            error_log("  Full test result: JENIS_KINERJA=0 ❌");
            error_log("  Conclusion: Issue is with one of the additional 17 fields in full INSERT");
            
            // Debug: Test with the same structure as main INSERT but with minimal data
            $test_full_sql = "INSERT INTO skp_akhir_pegawai (
                ID_SKP, NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG,
                PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA,
                RHK_PIMPINAN_INTERV, RENCANA_HASIL_KERJA, ASPEK, INDIKATOR_KINERJA_INDIVIDU,
                TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG, UMPAN_BALIK_DENGAN_BUKTI_DUKUNG,
                TANGGAL_INPUT_SKP, TANGGAL_EVALUASI_SKP, TAHUN, ID_SKP_GLOBAL, JENIS_KINERJA,
                RATING_PERILAKU_KERJA, PREDIKAT_KINERJA_PEGAWAI, UMPAN_BALIK_STICKER, RATING_HASIL_KERJA, SATUAN
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $test_full_stmt = $conn->prepare($test_full_sql);
            $test_full_id = 999998;
            // Prepare variables for test FULL INSERT
            $test_full_nama = "TEST FULL";
            $test_full_nip = "TEST123456789";
            $test_full_atasan_nama = "TEST SUPERVISOR";
            $test_full_atasan_nip = "TEST123456789";
            $test_full_rhk = "test";
            $test_full_rencana = "test";
            $test_full_aspek = "test";
            $test_full_indikator = "test";
            $test_full_target = 100;
            $test_full_realisasi = 200;
            $test_full_umpan_balik = "";
            $test_full_tanggal_input = "2025-01-01";
            $test_full_tanggal_evaluasi = "2025-01-01";
            $test_full_jenis_kinerja = "kinerja utama";
            $test_full_rating_perilaku = "";
            $test_full_predikat = "";
            $test_full_umpan_sticker = "";
            $test_full_rating_hasil = "";
            $test_full_satuan = "test";
            
            $test_empty_str = "";
            $test_full_stmt->bind_param('issssssssssssssiissssiisssss',
                $test_full_id, $test_full_nama, $test_full_nip, $test_full_atasan_nama, $test_full_atasan_nip,
                $test_empty_str, $test_empty_str, $test_empty_str, $test_empty_str,
                $test_full_rhk, $test_full_rencana, $test_full_aspek, $test_full_indikator, $test_full_target, $test_full_realisasi, $test_full_umpan_balik,
                $test_full_tanggal_input, $test_full_tanggal_evaluasi, $tahun, $test_full_id, $test_full_jenis_kinerja,
                $test_full_rating_perilaku, $test_full_predikat, $test_full_umpan_sticker, $test_full_rating_hasil, $test_full_satuan
            );
            if ($test_full_stmt->execute()) {
                error_log("🧪 Test FULL INSERT successful with JENIS_KINERJA=kinerja utama");
                // Check what was actually inserted
                $test_full_check_sql = "SELECT ID_SKP, JENIS_KINERJA FROM skp_akhir_pegawai WHERE ID_SKP = ?";
                $test_full_check_stmt = $conn->prepare($test_full_check_sql);
                $test_full_check_stmt->bind_param('i', $test_full_id);
                $test_full_check_stmt->execute();
                $test_full_check_result = $test_full_check_stmt->get_result();
                $test_full_check_row = $test_full_check_result->fetch_assoc();
                error_log("🧪 Test FULL result: ID_SKP=" . $test_full_check_row['ID_SKP'] . ", JENIS_KINERJA=" . $test_full_check_row['JENIS_KINERJA']);
                $test_full_check_stmt->close();
                
                // Debug: Check if there's a constraint or issue with specific fields
                $debug_full_sql = "SELECT * FROM skp_akhir_pegawai WHERE ID_SKP = ?";
                $debug_full_stmt = $conn->prepare($debug_full_sql);
                $debug_full_stmt->bind_param('i', $test_full_id);
                $debug_full_stmt->execute();
                $debug_full_result = $debug_full_stmt->get_result();
                $debug_full_row = $debug_full_result->fetch_assoc();
                error_log("🔍 Full test record details:");
                error_log("  NAMA: " . $debug_full_row['NAMA']);
                error_log("  ASPEK: " . $debug_full_row['ASPEK']);
                error_log("  JENIS_KINERJA: " . $debug_full_row['JENIS_KINERJA']);
                error_log("  TARGET: " . $debug_full_row['TARGET']);
                error_log("  REALISASI: " . $debug_full_row['REALISASI_BERDASARKAN_BUKTI_DUKUNG']);
                $debug_full_stmt->close();
                
                // Clean up test record
                $delete_test_full_sql = "DELETE FROM skp_akhir_pegawai WHERE ID_SKP = ?";
                $delete_test_full_stmt = $conn->prepare($delete_test_full_sql);
                $delete_test_full_stmt->bind_param('i', $test_full_id);
                $delete_test_full_stmt->execute();
                $delete_test_full_stmt->close();
            } else {
                error_log("🧪 Test FULL INSERT failed: " . $test_full_stmt->error);
            }
            $test_full_stmt->close();
            
            // Success message removed
            
        }
        } // end else (existing_count == 0)
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "❌ Error: " . $e->getMessage();
    } finally {
        $conn->autocommit(true);
    }
}

// Pagination setup
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $per_page;

// Get filter parameters
$filter_tahun = $_GET['tahun'] ?? '';

// Build query for skp_akhir_pegawai
$where_conditions = [];
$params = [];
$param_types = '';

// Always show only the logged-in user's own SKP Akhir data
$where_conditions[] = "NIP = ?";
$params[] = $user_nip;
$param_types .= 's';

if (!empty($filter_tahun)) {
    $where_conditions[] = "TAHUN = ?";
    $params[] = $filter_tahun;
    $param_types .= 'i';
}


$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count query - Group by visible columns to count unique employees
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

// Main query - Group by visible columns to show only one row per employee per year
$main_sql = "SELECT 
    MIN(ID_SKP) as ID_SKP,
    MIN(ID_SKP_GLOBAL) as ID_SKP_GLOBAL,
    NAMA,
    NIP,
    TAHUN,
    MIN(STATUS) as STATUS,
    MIN(PREDIKAT_KINERJA_PEGAWAI) as PREDIKAT_KINERJA_PEGAWAI,
    MAX(TANGGAL_EVALUASI_SKP) as TANGGAL_EVALUASI_SKP,
    SUM(TARGET) as TARGET,
    SUM(REALISASI_BERDASARKAN_BUKTI_DUKUNG) as REALISASI_BERDASARKAN_BUKTI_DUKUNG
FROM skp_akhir_pegawai $where_clause 
GROUP BY NAMA, NIP, TAHUN 
ORDER BY TAHUN DESC, NAMA ASC 
LIMIT ? OFFSET ?";

// Add pagination parameters to the existing params array
$params[] = $per_page;
$params[] = $offset;
$param_types .= 'ii';

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

// Update existing records with NULL dates (one-time fix)
$update_null_dates_sql = "UPDATE skp_akhir_pegawai 
                          SET TANGGAL_EVALUASI_SKP = NOW() + INTERVAL 8 HOUR
                          WHERE TANGGAL_EVALUASI_SKP IS NULL OR TANGGAL_EVALUASI_SKP = ''";
$conn->query($update_null_dates_sql);

// Get available years and jenis for filters
$years_sql = "SELECT DISTINCT TAHUN FROM skp_akhir_pegawai ORDER BY TAHUN DESC";
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
    <title>SKP Akhir Pegawai - Sistem Kinerja Pegawai</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <?php include 'includes/sidebar_styles.php'; ?>
    <style>
        
        .main-content {
            background-color: white;
        }
        .page-title {
            font-size: 24px;
            font-weight: bold;
            color: #0D2052;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        
        .generate-section {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #0D2052;
        }
        
        .generate-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            min-width: 150px;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #495057;
        }
        
        .form-group select,
        .form-group input {
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
            box-shadow: none;
        }
        
        .btn-danger:hover {
            background: #c82333;
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
            display: inline-block;
        }

        .view-details-btn {
            background: #007bff;
            box-shadow: none;
        }

        .view-details-btn:hover {
            background: #0056b3;
        }

        .lihat-detail-btn {
            padding: 5px 10px !important;
            font-size: 9px !important;
            height: 26px !important;
            border-radius: 4px !important;
            background: #007bff !important;
            box-shadow: none !important;
        }

        .lihat-detail-btn:hover {
            background: #0056b3 !important;
        }

        .submit-evaluasi-btn {
            background: #28a745;
            box-shadow: none;
            margin-left: 6px;
        }

        .submit-evaluasi-btn:hover {
            background: #218838;
        }

        .download-pdf-btn {
            background: #28a745 !important;
            box-shadow: none !important;
            margin-left: 6px;
            font-size: 9px !important;
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
            box-shadow: none;
            margin-left: 6px;
        }

        .revisi-skp-btn:hover {
            background: #c82333;
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
            padding: 12px 8px;
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

        .data-table th:nth-child(1),
        .data-table td:nth-child(1) { width: 3%; min-width: 36px; }
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) { width: 15%; }
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) { width: 11%; }
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) { width: 5%; min-width: 52px; }
        .data-table th:nth-child(5),
        .data-table td:nth-child(5) { width: 11%; min-width: 100px; }
        .data-table th:nth-child(6),
        .data-table td:nth-child(6) { width: 9%; min-width: 90px; }
        .data-table th:nth-child(7),
        .data-table td:nth-child(7) { width: 46%; min-width: 480px; }

        .data-table td:nth-child(1),
        .data-table td:nth-child(2),
        .data-table td:nth-child(3),
        .data-table td:nth-child(4),
        .data-table td:nth-child(5),
        .data-table td:nth-child(6) {
            padding: 10px 6px;
        }
        .data-table td:nth-child(7) {
            padding: 10px 12px;
        }
        .data-table .aksi-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-start;
            min-height: 48px;
            flex-wrap: wrap;
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
        
        .status-selesai {
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
        
        .filter-section {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #0D2052;
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
        <div class="page-title">SKP Akhir Pegawai (Tahunan)</div>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message">
                <strong>✅ Berhasil:</strong> <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <strong>❌ Error:</strong> <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>
        
        <div class="generate-section">
            <h3>Generate SKP Tahunan</h3>
            <form method="POST" action="">
                <div class="generate-form">
                    <div class="form-group">
                        <label for="tahun"></label>
                        <select name="tahun" id="tahun" required>
                            <option value="">Pilih Tahun</option>
                            <?php for ($year = date('Y') + 1; $year >= 2020; $year--): ?>
                                <option value="<?= $year ?>" <?= (isset($_POST['tahun']) && $_POST['tahun'] == $year) ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-buttons">
                        <button type="submit" name="generate_summary" class="btn btn-success">
                            🚀 Generate Ringkasan
                        </button>
                    </div>
                </div>
            </form>
            <p style="margin-top: 10px; color: #6c757d; font-size: 14px;">
                <strong>ℹ️ Informasi:</strong> Fitur ini akan membuat ringkasan SKP tahunan dengan mengakumulasi data dari semua 4 triwulan yang telah dievaluasi.
            </p>
        </div>
        
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
                        <a href="skp_akhir.php" class="btn btn-secondary">🔄 Reset</a>
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
                        <th>Tanggal Evaluasi</th>
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
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $row['STATUS'] ?? 'draft')) ?>">
                                    <?= htmlspecialchars($row['STATUS'] ?? 'DRAFT') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($row['TANGGAL_EVALUASI_SKP'] ?? 'N/A') ?></td>
                            <td>
                                <div class="aksi-buttons">
                                    <button class="view-details-btn lihat-detail-btn" onclick="viewSKPAkhirDetails(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)">Lihat Detail</button>
                                    <?php if ($row['STATUS'] === 'draft'): ?>
                                        <button class="submit-evaluasi-btn" onclick="submitSKPAkhir(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>, <?= htmlspecialchars($row['TAHUN']) ?>)">Submit SKP Akhir</button>
                                    <?php elseif ($row['STATUS'] === 'SELESAI EVALUASI'): ?>
                                        <button class="download-pdf-btn" onclick="downloadSKPAkhirPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Evaluasi Kuantitatif Akhir</button>
                                        <button class="download-pdf-btn" onclick="downloadEvaluasiKuantitatifPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)" style="background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%); box-shadow: 0 2px 8px rgba(32, 201, 151, 0.3);"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Umpan Balik Kuantitatif Akhir</button>
                                        <button class="download-pdf-btn" onclick="downloadKuantitatifPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)" style="background: linear-gradient(135deg, #fd7e14 0%, #e8590c 100%); box-shadow: 0 2px 8px rgba(253, 126, 20, 0.3);"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Kuantitatif</button>
                                        <button class="download-pdf-btn" onclick="downloadDokumenEvaluasiPDF(<?= htmlspecialchars($row['ID_SKP_GLOBAL']) ?>)" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); box-shadow: 0 2px 8px rgba(111, 66, 193, 0.3);"><img src="images/pdf.png" style="height:16px;vertical-align:middle;margin-right:4px;" alt="PDF"> Dokumen Evaluasi</button>
                                        <button class="submit-evaluasi-btn" disabled style="background: #6c757d; cursor: not-allowed; opacity: 0.6;">🔒 Terkunci</button>
                                    <?php else: ?>
                                        <button class="submit-evaluasi-btn" disabled style="background: #6c757d; cursor: not-allowed; opacity: 0.6;">⏳ Menunggu Evaluasi</button>
                                    <?php endif; ?>
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
                <p>Tidak ada ringkasan SKP yang tersedia. Silakan generate ringkasan SKP tahunan terlebih dahulu.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function viewSKPAkhirDetails(idSkp) {
            // Open SKP Akhir details in a new window/tab using skp_detail.php with skp_akhir parameter
            window.open('skp_detail.php?id_skp_global=' + idSkp + '&skp_akhir=1', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }
        
        function submitSKPAkhir(idSkp, year) {
            // Debug logging
            console.log('🔍 Submit SKP Akhir Debug:');
            console.log('  ID_SKP_GLOBAL (idSkp):', idSkp);
            console.log('  Year:', year);
            console.log('  Type of idSkp:', typeof idSkp);
            console.log('  Type of year:', typeof year);
            
            // Show debug info in alert
            alert('Debug Info:\nID_SKP_GLOBAL: ' + idSkp + '\nYear: ' + year + '\nType: ' + typeof idSkp);
            
            if (confirm('Apakah Anda yakin ingin submit SKP Akhir ini ke atasan untuk evaluasi?')) {
                // Show loading state
                const button = event.target;
                const originalText = button.innerHTML;
                button.innerHTML = '⏳ Processing...';
                button.disabled = true;
                
                // Debug the request body
                const requestBody = 'id_skp=' + encodeURIComponent(idSkp) + '&year=' + encodeURIComponent(year);
                console.log('🔍 Request Body:', requestBody);
                
                // Submit SKP Akhir via AJAX with year parameter
                fetch('skp/submit_skp_akhir.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: requestBody
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ SKP Akhir berhasil disubmit!\nStatus SKP Akhir telah diubah menjadi "PROSES EVALUASI".');
                        location.reload(); // Refresh page to show updated status
                    } else {
                        alert('❌ Gagal submit SKP Akhir: ' + data.message);
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Terjadi kesalahan saat submit SKP Akhir. Silakan coba lagi.');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }
        
        function downloadSKPAkhirPDF(idSkpGlobal) {
            // Show loading state
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '⏳ Generating...';
            button.disabled = true;
            
            // Open PDF generation in new window/tab (correct path to skp subdirectory)
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
            // Open umpan balik PDF generation in new window/tab (annual SKP)
            window.open('generate_umpan_balik_annual_pdf.php?id_skp_global=' + idSkpGlobal, '_blank');
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
            // Open kuantitatif PDF generation in new window/tab (annual SKP)
            window.open('generate_kuantitatif_annual_pdf.php?id_skp_global=' + idSkpGlobal, '_blank');
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
