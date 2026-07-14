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
    die('Connection failed: ' . $e->getMessage());
}

$pegawai = [];
$pegawai_by_nip = [];
$pegawai_error = '';

// Check if database connection is successful
if (!$conn) {
    $pegawai_error = 'Koneksi database gagal. Silakan hubungi administrator.';
} else {
    $sql = "SELECT * FROM Pegawai";
    $result = $conn->query($sql);
    
    if (!$result) {
        $pegawai_error = 'Gagal mengambil data pegawai dari database. Error: ' . $conn->error;
    } elseif ($result->num_rows === 0) {
        $pegawai_error = 'Tidak ada data pegawai ditemukan dalam database.';
    } else {
        while ($row = $result->fetch_assoc()) {
            $pegawai[] = $row;
            $pegawai_by_nip[$row['NIP']] = $row;
        }
    }
}

$successMsg = '';
$errorMsg = '';

// Check for edit mode
$edit_mode = false;
$edit_id_skp_global = $_GET['id_skp_global'] ?? '';
$edit_skp_data = [];

if ($edit_id_skp_global) {
    $edit_mode = true;
    
    // Fetch existing Kinerja Utama data
    $sql_utama = "SELECT * FROM skp_pegawai WHERE id_skp_global = ? AND jenis_kinerja = 'kinerja utama' ORDER BY id_skp_global";
    $stmt_utama = $conn->prepare($sql_utama);
    $stmt_utama->bind_param('i', $edit_id_skp_global);
    $stmt_utama->execute();
    $result_utama = $stmt_utama->get_result();
    $edit_utama_data = [];
    if ($result_utama && $result_utama->num_rows > 0) {
        while ($row = $result_utama->fetch_assoc()) {
            $edit_utama_data[] = $row;
        }
    }
    $stmt_utama->close();
    
    // Fetch existing Kinerja Tambahan data
    $sql_tambahan = "SELECT * FROM skp_pegawai WHERE id_skp_global = ? AND jenis_kinerja = 'kinerja tambahan' ORDER BY id_skp_global";
    $stmt_tambahan = $conn->prepare($sql_tambahan);
    $stmt_tambahan->bind_param('i', $edit_id_skp_global);
    $stmt_tambahan->execute();
    $result_tambahan = $stmt_tambahan->get_result();
    $edit_tambahan_data = [];
    if ($result_tambahan && $result_tambahan->num_rows > 0) {
        while ($row = $result_tambahan->fetch_assoc()) {
            $edit_tambahan_data[] = $row;
        }
    }
    $stmt_tambahan->close();
    
    // Fetch existing Perilaku Kerja data
    $sql_perilaku = "SELECT * FROM skp_perilaku_pegawai WHERE id_skp_global = ? LIMIT 1";
    $stmt_perilaku = $conn->prepare($sql_perilaku);
    $stmt_perilaku->bind_param('i', $edit_id_skp_global);
    $stmt_perilaku->execute();
    $result_perilaku = $stmt_perilaku->get_result();
    $edit_perilaku_data = [];
    if ($result_perilaku && $result_perilaku->num_rows > 0) {
        $edit_perilaku_data = $result_perilaku->fetch_assoc();
    }
    $stmt_perilaku->close();
    
    // Get basic info from first record
    if (!empty($edit_utama_data)) {
        $first_record = $edit_utama_data[0];
        $edit_skp_data = [
            'NAMA' => $first_record['NAMA'],
            'NIP' => $first_record['NIP'],
            'NAMA_ATASAN_LANGSUNG' => $first_record['NAMA_ATASAN_LANGSUNG'],
            'NIP_ATASAN_LANGSUNG' => $first_record['NIP_ATASAN_LANGSUNG'],
            'TRIWULAN' => $first_record['TRIWULAN'],
            'TAHUN' => $first_record['TAHUN'],
            'STATUS' => $first_record['STATUS']
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for database errors first
    if (!empty($pegawai_error)) {
        $errorMsg = 'Tidak dapat memproses form karena: ' . $pegawai_error;
    }
    // Check for duplicate submission
    elseif (isset($_SESSION['last_submit_time']) && (time() - $_SESSION['last_submit_time']) < 5) {
        $errorMsg = 'Terlalu cepat! Silakan tunggu sebentar sebelum mengirim ulang.';
    } else {
        $_SESSION['last_submit_time'] = time();
        
        $nip = $_POST['pegawai_nip'] ?? '';
        $pegawaiData = $pegawai_by_nip[$nip] ?? null;
        $atasan = null;
        
        // Get atasan langsung
        if ($pegawaiData && !empty($pegawaiData['ATASAN_LANGSUNG'])) {
            $atasan_sql = "SELECT * FROM Pegawai WHERE JABATAN = ? LIMIT 1";
            $stmt_atasan = $conn->prepare($atasan_sql);
            if ($stmt_atasan) {
                $stmt_atasan->bind_param('s', $pegawaiData['ATASAN_LANGSUNG']);
                $stmt_atasan->execute();
                $result_atasan = $stmt_atasan->get_result();
                if ($result_atasan && $result_atasan->num_rows > 0) {
                    $atasan = $result_atasan->fetch_assoc();
                }
                $stmt_atasan->close();
            }
        }
        
        // Prepare variables
        $namaPegawai = $pegawaiData['NAMA'] ?? '';
        $nipPegawai = $pegawaiData['NIP'] ?? '';
        $namaAtasan = $atasan['NAMA'] ?? '';
        $nipAtasan = $atasan['NIP'] ?? '';
        $tahun = $_POST['tahun'] ?? '';
        $status = 'draft';
        $pangkatPegawai = $pegawaiData['PANGKAT_GOL_RUANG'] ?? '';
        $jabatanPegawai = $pegawaiData['JABATAN'] ?? '';
        $unitKerjaPegawai = $pegawaiData['UNIT_KERJA'] ?? '';
        $satuanKerjaPegawai = $pegawaiData['SATUAN_KERJA'] ?? '';
        
        // Start transaction
        $conn->autocommit(false);
        
        // Generate base id_skp_global (for skp_pegawai / triwulan tables)
        $base_id_skp_global = 1;
        if ($edit_mode && isset($_POST['save_edit'])) {
            $base_id_skp_global = $edit_id_skp_global;
        } else {
            $max_global_sql = "SELECT MAX(CAST(id_skp_global AS UNSIGNED)) as max_global FROM skp_pegawai FOR UPDATE";
            $max_result = $conn->query($max_global_sql);
            if ($max_result && $max_result->num_rows > 0) {
                $max_row = $max_result->fetch_assoc();
                $base_id_skp_global = ($max_row['max_global'] ?? 0) + 1;
            }
        }
        
        // Generate INDEPENDENT id for kuantitatif awal tahun tables (own ID sequence)
        $id_kuantitatif_global = 1;
        if ($edit_mode && isset($_POST['save_edit'])) {
            // In edit mode: find the existing kuantitatif record for this NIP+TAHUN
            $kq = $conn->prepare("SELECT DISTINCT id_skp_global FROM skp_kuantitatif_awal_tahun_pegawai WHERE NIP = ? AND TAHUN = ? LIMIT 1");
            $kq->bind_param('ss', $nipPegawai, $tahun);
            $kq->execute();
            $kr = $kq->get_result();
            if ($kr && $kr->num_rows > 0) {
                $id_kuantitatif_global = (int)$kr->fetch_assoc()['id_skp_global'];
            }
            $kq->close();
        } else {
            $max_k_sql = "SELECT MAX(CAST(id_skp_global AS UNSIGNED)) as max_k FROM skp_kuantitatif_awal_tahun_pegawai FOR UPDATE";
            $max_k_result = $conn->query($max_k_sql);
            if ($max_k_result && $max_k_result->num_rows > 0) {
                $max_k_row = $max_k_result->fetch_assoc();
                $id_kuantitatif_global = ($max_k_row['max_k'] ?? 0) + 1;
            }
        }
        
        // Validation: required sections
        if (empty($nipPegawai)) {
            $errorMsg = 'Silakan pilih Pegawai yang dinilai.';
        } elseif (empty($tahun)) {
            $errorMsg = 'Silakan lengkapi periode penilaian (Tahun).';
        } elseif (!isset($pegawai_by_nip[$nipPegawai])) {
            $errorMsg = 'Data pegawai yang dipilih tidak valid atau tidak ditemukan.';
        }
        
        // Validation: Check if SKP already exists for this user, triwulan, and year
        if (empty($errorMsg)) {
            if ($edit_mode) {
                // In edit mode, get the actual triwulan from existing data
                $get_triwulan_sql = "SELECT DISTINCT TRIWULAN FROM skp_pegawai WHERE id_skp_global = ? LIMIT 1";
                $get_triwulan_stmt = $conn->prepare($get_triwulan_sql);
                $get_triwulan_stmt->bind_param('i', $edit_id_skp_global);
                $get_triwulan_stmt->execute();
                $get_triwulan_result = $get_triwulan_stmt->get_result();
                
                if ($get_triwulan_result && $get_triwulan_result->num_rows > 0) {
                    $triwulan_row = $get_triwulan_result->fetch_assoc();
                    $current_triwulan = $triwulan_row['TRIWULAN'];
                    $triwulan_list = [$current_triwulan];
                } else {
                    $triwulan_list = [1]; // fallback
                }
                $get_triwulan_stmt->close();
            } else {
                $triwulan_list = [1, 2, 3, 4];
            }
            
            $existing_triwulan = [];
            
            foreach ($triwulan_list as $triwulan) {
                $check_sql = "SELECT COUNT(*) as count FROM skp_pegawai WHERE NIP = ? AND TRIWULAN = ? AND TAHUN = ?";
                if ($edit_mode) {
                    $check_sql .= " AND id_skp_global != ?";
                }
                $check_stmt = $conn->prepare($check_sql);
                
                if ($check_stmt) {
                    if ($edit_mode) {
                        $check_stmt->bind_param('siis', $nipPegawai, $triwulan, $tahun, $edit_id_skp_global);
                    } else {
                        $check_stmt->bind_param('sis', $nipPegawai, $triwulan, $tahun);
                    }
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result && $check_result->num_rows > 0) {
                        $count_row = $check_result->fetch_assoc();
                        if ($count_row['count'] > 0) {
                            $existing_triwulan[] = $triwulan;
                        }
                    }
                    $check_stmt->close();
                }
            }
            
            if (!empty($existing_triwulan)) {
                $triwulan_text = implode(', ', $existing_triwulan);
                $errorMsg = "SKP untuk Triwulan {$triwulan_text} Tahun {$tahun} sudah ada untuk pegawai ini. Silakan pilih triwulan atau tahun yang berbeda.";
            }
        }

        // Validate Perilaku Kerja: all 7 must be filled
        if (empty($errorMsg)) {
            $eksArr = $_POST['perilaku_ekspektasi'] ?? [];
            for ($i = 0; $i < 7; $i++) {
                $val = trim($eksArr[$i] ?? '');
                if ($val === '') {
                    $errorMsg = 'Silakan isi seluruh Ekspektasi Khusus Pimpinan pada bagian Perilaku Kerja.';
                    break;
                }
            }
        }

        // Validate Kinerja Utama: must have at least one row, and if a row is started, all fields required
        if (empty($errorMsg)) {
            $utama_perspektif = $_POST['utama_perspektif'] ?? [];
            $utama_kerja = $_POST['utama_kerja'] ?? [];
            $utama_indikator = $_POST['utama_indikator'] ?? [];
            $utama_target = $_POST['utama_target'] ?? [];
            $utama_satuan = $_POST['utama_satuan'] ?? [];
            $count = max(count($utama_perspektif), count($utama_kerja), count($utama_indikator), count($utama_target), count($utama_satuan));
            $foundFilledRow = false;
            for ($i = 0; $i < $count; $i++) {
                $r1 = trim($utama_perspektif[$i] ?? '');
                $r2 = trim($utama_kerja[$i] ?? '');
                $r4 = trim($utama_indikator[$i] ?? '');
                $r5 = trim($utama_target[$i] ?? '');
                $r6 = trim($utama_satuan[$i] ?? '');
                $any = ($r1 !== '' || $r2 !== '' || $r4 !== '' || $r5 !== '' || $r6 !== '');
                $all = ($r1 !== '' && $r2 !== '' && $r4 !== '' && $r5 !== '' && $r6 !== '');
                if ($any && !$all) {
                    $errorMsg = 'Mohon lengkapi seluruh kolom pada baris Kinerja Utama yang diisi.';
                    break;
                }
                if ($all) { $foundFilledRow = true; }
            }
            if (empty($errorMsg) && !$foundFilledRow) {
                $errorMsg = 'Minimal satu baris Kinerja Utama harus diisi lengkap.';
            }
        }
        
        // Process data for each triwulan (1-4) or single triwulan in edit mode
        if ($edit_mode) {
            // In edit mode, get the actual triwulan from existing data
            $get_triwulan_sql = "SELECT DISTINCT TRIWULAN FROM skp_pegawai WHERE id_skp_global = ? LIMIT 1";
            $get_triwulan_stmt = $conn->prepare($get_triwulan_sql);
            $get_triwulan_stmt->bind_param('i', $edit_id_skp_global);
            $get_triwulan_stmt->execute();
            $get_triwulan_result = $get_triwulan_stmt->get_result();
            
            if ($get_triwulan_result && $get_triwulan_result->num_rows > 0) {
                $triwulan_row = $get_triwulan_result->fetch_assoc();
                $current_triwulan = $triwulan_row['TRIWULAN'];
                $triwulan_list = [$current_triwulan];
            } else {
                $triwulan_list = [1]; // fallback
            }
            $get_triwulan_stmt->close();
        } else {
            $triwulan_list = [1, 2, 3, 4];
        }
        
        $all_triwulan_saved = false;
        
        foreach ($triwulan_list as $triwulan) {
            error_log("Processing triwulan: $triwulan");
            
            // Calculate unique id_skp_global for this triwulan
            $id_skp_global = $edit_mode ? $base_id_skp_global : $base_id_skp_global + ($triwulan - 1);
            
            $savedUtama = false;
            $savedTambahan = false;
            $savedPerilaku = false;
            
            // Process Kinerja Utama
            if (empty($errorMsg) && isset($_POST['utama_perspektif']) && !empty(array_filter($_POST['utama_perspektif']))) {
                // Delete all existing Kinerja Utama for this SKP
                $delete_utama = $conn->prepare("DELETE FROM skp_pegawai WHERE id_skp_global = ? AND jenis_kinerja = 'kinerja utama'");
                $delete_utama->bind_param('i', $id_skp_global);
                $delete_utama->execute();
                $delete_utama->close();
                
                if ($triwulan == 1 && !$edit_mode) {
                    $delete_utama_k = $conn->prepare("DELETE FROM skp_kuantitatif_awal_tahun_pegawai WHERE id_skp_global = ? AND jenis_kinerja = 'kinerja utama'");
                    $delete_utama_k->bind_param('i', $id_kuantitatif_global);
                    $delete_utama_k->execute();
                    $delete_utama_k->close();
                }

                $count = count($_POST['utama_perspektif'] ?? []);
                for ($i = 0; $i < $count; $i++) {
                    $rhk = '';
                    $perspektif = $_POST['utama_perspektif'][$i] ?? '';
                    $rencana = $_POST['utama_kerja'][$i] ?? '';
                    $aspek = '';
                    $indikator = $_POST['utama_indikator'][$i] ?? '';
                    $target = $_POST['utama_target'][$i] ?? '';
                    $realisasi = $_POST['utama_realisasi'][$i] ?? '';
                    $satuan = $_POST['utama_satuan'][$i] ?? '';
                    
                    // Insert only complete rows
                    if ($perspektif !== '' && $rencana !== '' && $indikator !== '' && $target !== '' && $satuan !== '') {
                        $stmt = $conn->prepare("INSERT INTO skp_pegawai (NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG, PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA, RHK_PIMPINAN_INTERV, RENCANA_HASIL_KERJA, ASPEK, INDIKATOR_KINERJA_INDIVIDU, TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG, SATUAN, TRIWULAN, TAHUN, STATUS, TANGGAL_INPUT_SKP, ID_SKP_GLOBAL, JENIS_KINERJA, PERSPEKTIF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 8 HOUR, ?, ?, ?)");
                        if ($stmt) {
                            $jenisKinerjaUtama = 'kinerja utama';
                            $stmt->bind_param(
                                'ssssssssssssssssssiss',
                                $namaPegawai,
                                $nipPegawai,
                                $namaAtasan,
                                $nipAtasan,
                                $pangkatPegawai,
                                $jabatanPegawai,
                                $unitKerjaPegawai,
                                $satuanKerjaPegawai,
                                $rhk,
                                $rencana,
                                $aspek,
                                $indikator,
                                $target,
                                $realisasi,
                                $satuan,
                                $triwulan,
                                $tahun,
                                $status,
                                $id_skp_global,
                                $jenisKinerjaUtama,
                                $perspektif
                            );
                            $stmt->execute();
                            $stmt->close();
                            $savedUtama = true;
                            
                            if ($triwulan == 1 && !$edit_mode) {
                                $stmt_k = $conn->prepare("INSERT INTO skp_kuantitatif_awal_tahun_pegawai (NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG, PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA, RHK_PIMPINAN_INTERV, RENCANA_HASIL_KERJA, ASPEK, INDIKATOR_KINERJA_INDIVIDU, TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG, SATUAN, TRIWULAN, TAHUN, STATUS, TANGGAL_INPUT_SKP, ID_SKP_GLOBAL, JENIS_KINERJA, PERSPEKTIF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 8 HOUR, ?, ?, ?)");
                                if ($stmt_k) {
                                    $stmt_k->bind_param('ssssssssssssssssssiss', $namaPegawai, $nipPegawai, $namaAtasan, $nipAtasan, $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai, $rhk, $rencana, $aspek, $indikator, $target, $realisasi, $satuan, $triwulan, $tahun, $status, $id_kuantitatif_global, $jenisKinerjaUtama, $perspektif);
                                    if (!$stmt_k->execute()) {
                                        error_log("Error inserting into skp_kuantitatif_awal_tahun_pegawai (Utama): " . $stmt_k->error);
                                    }
                                    $stmt_k->close();
                                } else {
                                    error_log("Failed to prepare stmt_k (Utama): " . $conn->error);
                                }
                            }
                        }
                    }
                }
            }
            
            // Process Kinerja Tambahan
            if (isset($_POST['tambahan_perspektif']) && !empty(array_filter($_POST['tambahan_perspektif']))) {
                // Delete all existing Kinerja Tambahan for this SKP
                $delete_tambahan = $conn->prepare("DELETE FROM skp_pegawai WHERE id_skp_global = ? AND jenis_kinerja = 'kinerja tambahan'");
                $delete_tambahan->bind_param('i', $id_skp_global);
                $delete_tambahan->execute();
                $delete_tambahan->close();

                if ($triwulan == 1 && !$edit_mode) {
                    $delete_tambahan_k = $conn->prepare("DELETE FROM skp_kuantitatif_awal_tahun_pegawai WHERE id_skp_global = ? AND jenis_kinerja = 'kinerja tambahan'");
                    $delete_tambahan_k->bind_param('i', $id_kuantitatif_global);
                    $delete_tambahan_k->execute();
                    $delete_tambahan_k->close();
                }

                $count = count($_POST['tambahan_perspektif'] ?? []);
                for ($i = 0; $i < $count; $i++) {
                    $rhk = '';
                    $perspektif = $_POST['tambahan_perspektif'][$i] ?? '';
                    $rencana = $_POST['tambahan_kerja'][$i] ?? '';
                    $aspek = '';
                    $indikator = $_POST['tambahan_indikator'][$i] ?? '';
                    $target = $_POST['tambahan_target'][$i] ?? '';
                    $realisasi = $_POST['tambahan_realisasi'][$i] ?? '';
                    $satuan = $_POST['tambahan_satuan'][$i] ?? '';
                    
                    // Only insert if there's actual content
                    if (trim($perspektif) !== '' || trim($rencana) !== '' || trim($indikator) !== '' || trim($target) !== '' || trim($satuan) !== '') {
                        $stmt = $conn->prepare("INSERT INTO skp_pegawai (NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG, PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA, RHK_PIMPINAN_INTERV, RENCANA_HASIL_KERJA, ASPEK, INDIKATOR_KINERJA_INDIVIDU, TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG, SATUAN, TRIWULAN, TAHUN, STATUS, TANGGAL_INPUT_SKP, ID_SKP_GLOBAL, JENIS_KINERJA, PERSPEKTIF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 8 HOUR, ?, ?, ?)");
                        if ($stmt) {
                            $jenisKinerjaTambahan = 'kinerja tambahan';
                            $stmt->bind_param(
                                'ssssssssssssssssssiss',
                                $namaPegawai,
                                $nipPegawai,
                                $namaAtasan,
                                $nipAtasan,
                                $pangkatPegawai,
                                $jabatanPegawai,
                                $unitKerjaPegawai,
                                $satuanKerjaPegawai,
                                $rhk,
                                $rencana,
                                $aspek,
                                $indikator,
                                $target,
                                $realisasi,
                                $satuan,
                                $triwulan,
                                $tahun,
                                $status,
                                $id_skp_global,
                                $jenisKinerjaTambahan,
                                $perspektif
                            );
                            $stmt->execute();
                            $stmt->close();
                            $savedTambahan = true;
                            
                            if ($triwulan == 1 && !$edit_mode) {
                                $stmt_k = $conn->prepare("INSERT INTO skp_kuantitatif_awal_tahun_pegawai (NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG, PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA, RHK_PIMPINAN_INTERV, RENCANA_HASIL_KERJA, ASPEK, INDIKATOR_KINERJA_INDIVIDU, TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG, SATUAN, TRIWULAN, TAHUN, STATUS, TANGGAL_INPUT_SKP, ID_SKP_GLOBAL, JENIS_KINERJA, PERSPEKTIF) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 8 HOUR, ?, ?, ?)");
                                if ($stmt_k) {
                                    $stmt_k->bind_param('ssssssssssssssssssiss', $namaPegawai, $nipPegawai, $namaAtasan, $nipAtasan, $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai, $rhk, $rencana, $aspek, $indikator, $target, $realisasi, $satuan, $triwulan, $tahun, $status, $id_kuantitatif_global, $jenisKinerjaTambahan, $perspektif);
                                    if (!$stmt_k->execute()) {
                                        error_log("Error inserting into skp_kuantitatif_awal_tahun_pegawai (Tambahan): " . $stmt_k->error);
                                    }
                                    $stmt_k->close();
                                } else {
                                    error_log("Failed to prepare stmt_k (Tambahan): " . $conn->error);
                                }
                            }
                        }
                    }
                }
            }
            
            // Process Perilaku Kerja
            if (empty($errorMsg) && isset($_POST['perilaku_ekspektasi'])) {
                // Delete all existing Perilaku Kerja for this SKP
                $delete_perilaku = $conn->prepare("DELETE FROM skp_perilaku_pegawai WHERE id_skp_global = ?");
                $delete_perilaku->bind_param('i', $id_skp_global);
                $delete_perilaku->execute();
                $delete_perilaku->close();

                if ($triwulan == 1 && !$edit_mode) {
                    $delete_perilaku_k = $conn->prepare("DELETE FROM skp_perilaku_awal_tahun_pegawai WHERE id_skp_global = ?");
                    $delete_perilaku_k->bind_param('i', $id_kuantitatif_global);
                    $delete_perilaku_k->execute();
                    $delete_perilaku_k->close();
                }

                $ekspektasiArr = $_POST['perilaku_ekspektasi'];
                // Static mapping for 7 perilaku items
                $perilakuDesc = [
                    "- Memahami dan memenuhi kebutuhan masyarakat.\n- Ramah, cekatan, solutif, dan dapat diandalkan.\n- Melakukan perbaikan tiada henti.",
                    "- Melaksanakan tugas dengan jujur, bertanggungjawab, cermat, disiplin dan berintegritas tinggi.\n- Menggunakan kekayaan dan barang milik negara secara bertanggungjawab, efektif dan efisien.\n- Tidak menyalahgunakan kewenangan jabatan.",
                    "- Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah.\n- Membantu orang lain belajar.\n- Melaksanakan tugas dengan kualitas terbaik.",
                    "- Menghargai setiap orang apapun latar belakangnya.\n- Suka menolong orang lain.\n- Membangun lingkungan kerja yang kondusif.",
                    "- Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, setia pada Negara Kesatuan Republik Indonesia serta pemerintahan yang sah.\n- Menjaga nama baik ASN, Pimpinan, Instansi dan Negara.\n- Menjaga rahasia jabatan dan negara.",
                    "- Cepat menyesuaikan diri menghadapi perubahan\n- Terus berinovasi dan mengembangkan kreativitas\n- Bertindak proaktif",
                    "- Memberi kesempatan kepada berbagai pihak untuk berkontribusi.\n- Terbuka dalam bekerjasama untuk menghasilkan nilai tambah.\n- Menggerakan pemanfaatan berbagai sumber daya untuk tujuan bersama."
                ];

                // Insert into skp_perilaku_pegawai with separate columns for each perilaku type
                $stmt_perilaku = $conn->prepare("INSERT INTO skp_perilaku_pegawai (NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG, PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA, PERILAKU_KERJA_BERORIENTASI_PELAYANAN, PERILAKU_KERJA_AKUNTABEL, PERILAKU_KERJA_KOMPETEN, PERILAKU_KERJA_HARMONIS, PERILAKU_KERJA_LOYAL, PERILAKU_KERJA_ADAPTIF, PERILAKU_KERJA_KOLABORATIF, EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN, EKSPEKTASI_PIMPINAN_AKUNTABEL, EKSPEKTASI_PIMPINAN_KOMPETEN, EKSPEKTASI_PIMPINAN_HARMONIS, EKSPEKTASI_PIMPINAN_LOYAL, EKSPEKTASI_PIMPINAN_ADAPTIF, EKSPEKTASI_PIMPINAN_KOLABORATIF, TRIWULAN, TAHUN, STATUS, TANGGAL_INPUT_SKP, id_skp_global) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 8 HOUR, ?)");
                if ($stmt_perilaku) {
                    // Prepare all 7 perilaku descriptions and ekspektasi values
                    $perilakuValues = [];
                    $ekspektasiValues = [];
                    
                    for ($i = 0; $i < 7; $i++) {
                        $perilakuValues[] = $perilakuDesc[$i] ?? '';
                        $ekspektasiValues[] = trim($ekspektasiArr[$i] ?? '');
                    }
                    
                    $stmt_perilaku->bind_param(
                        'ssssssssssssssssssssssiisi',
                        $namaPegawai,
                        $nipPegawai,
                        $namaAtasan,
                        $nipAtasan,
                        $pangkatPegawai,
                        $jabatanPegawai,
                        $unitKerjaPegawai,
                        $satuanKerjaPegawai,
                        $perilakuValues[0], // BERORIENTASI_PELAYANAN
                        $perilakuValues[1], // AKUNTABEL
                        $perilakuValues[2], // KOMPETEN
                        $perilakuValues[3], // HARMONIS
                        $perilakuValues[4], // LOYAL
                        $perilakuValues[5], // ADAPTIF
                        $perilakuValues[6], // KOLABORATIF
                        $ekspektasiValues[0], // EKSPEKTASI BERORIENTASI_PELAYANAN
                        $ekspektasiValues[1], // EKSPEKTASI AKUNTABEL
                        $ekspektasiValues[2], // EKSPEKTASI KOMPETEN
                        $ekspektasiValues[3], // EKSPEKTASI HARMONIS
                        $ekspektasiValues[4], // EKSPEKTASI LOYAL
                        $ekspektasiValues[5], // EKSPEKTASI ADAPTIF
                        $ekspektasiValues[6], // EKSPEKTASI KOLABORATIF
                        $triwulan,
                        $tahun,
                        $status,
                        $id_skp_global
                    );
                    $stmt_perilaku->execute();
                    $savedPerilaku = true;
                    $stmt_perilaku->close();
                    
                    if ($triwulan == 1 && !$edit_mode) {
                        $stmt_perilaku_k = $conn->prepare("INSERT INTO skp_perilaku_awal_tahun_pegawai (NAMA, NIP, NAMA_ATASAN_LANGSUNG, NIP_ATASAN_LANGSUNG, PANGKAT_GOL_RUANG, JABATAN, UNIT_KERJA, SATUAN_KERJA, PERILAKU_KERJA_BERORIENTASI_PELAYANAN, PERILAKU_KERJA_AKUNTABEL, PERILAKU_KERJA_KOMPETEN, PERILAKU_KERJA_HARMONIS, PERILAKU_KERJA_LOYAL, PERILAKU_KERJA_ADAPTIF, PERILAKU_KERJA_KOLABORATIF, EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN, EKSPEKTASI_PIMPINAN_AKUNTABEL, EKSPEKTASI_PIMPINAN_KOMPETEN, EKSPEKTASI_PIMPINAN_HARMONIS, EKSPEKTASI_PIMPINAN_LOYAL, EKSPEKTASI_PIMPINAN_ADAPTIF, EKSPEKTASI_PIMPINAN_KOLABORATIF, TRIWULAN, TAHUN, STATUS, TANGGAL_INPUT_SKP, id_skp_global) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW() + INTERVAL 8 HOUR, ?)");
                        if ($stmt_perilaku_k) {
                            $stmt_perilaku_k->bind_param('ssssssssssssssssssssssiisi', $namaPegawai, $nipPegawai, $namaAtasan, $nipAtasan, $pangkatPegawai, $jabatanPegawai, $unitKerjaPegawai, $satuanKerjaPegawai, $perilakuValues[0], $perilakuValues[1], $perilakuValues[2], $perilakuValues[3], $perilakuValues[4], $perilakuValues[5], $perilakuValues[6], $ekspektasiValues[0], $ekspektasiValues[1], $ekspektasiValues[2], $ekspektasiValues[3], $ekspektasiValues[4], $ekspektasiValues[5], $ekspektasiValues[6], $triwulan, $tahun, $status, $id_kuantitatif_global);
                            $stmt_perilaku_k->execute();
                            $stmt_perilaku_k->close();
                        }
                    }
                }
            }
            
            // Mark that at least one triwulan was processed
            if ($savedUtama || $savedTambahan || $savedPerilaku) {
                $all_triwulan_saved = true;
            }
        }
        
        // Commit transaction
        if (empty($errorMsg) && $all_triwulan_saved) {
            $conn->commit();
            $successMsg = $edit_mode ? 
                'Data berhasil disimpan!' :
                'Data berhasil disimpan untuk 4 Triwulan!';
            
            // Redirect to prevent double submission
            header('Location: skploginpage.php?success=save_berhasil');
            exit();
        } else {
            $conn->rollback();
            if (empty($errorMsg)) {
                $errorMsg = 'Gagal menyimpan data. Silakan coba lagi.';
            }
        }
        
        // Re-enable autocommit
        $conn->autocommit(true);
        
        // Clear POST data to reset form
        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.67, minimum-scale=0.67, maximum-scale=2.0, user-scalable=yes">
    <title><?= $edit_mode ? 'Edit SKP Eselon' : 'SKP Baru Eselon' ?> - SI-APA</title>
    <link rel="icon" type="image/png" href="images/SIAPA.png">
    <?php include 'includes/sidebar_styles.php'; ?>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            background: #0D2052;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 0;
            min-height: 100vh;
            display: flex;
        }
        .main-content {
            background-color: white;
            flex: 1;
            width: 100%;
            min-width: 0;
        }
        .back-link {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 14px;
        }
        .back-link:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            text-decoration: none;
            color: white;
        }
        .row {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
            flex-wrap: wrap;
        }
        .col {
            flex: 1 1 260px;
            min-width: 240px;
        }
        @media (min-width: 1200px) {
            .col {
                flex: 1 1 320px;
                min-width: 280px;
            }
        }
        @media (min-width: 1600px) {
            .col {
                flex: 1 1 380px;
                min-width: 320px;
            }
        }
        .section-title {
            font-weight: bold;
            margin: 18px 0 8px 0;
            font-size: 0.95rem;
            color: #0D2052;
        }
        .card {
            background: #f0f0f0;
            border: 1px solid rgba(13, 32, 82, 0.2);
            border-radius: 8px;
            padding: 10px 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        }
        .card h4 {
            margin: 0 0 8px 0;
            font-size: 0.9rem;
            color: #0D2052;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 6px 10px;
            align-items: center;
        }
        .info-label {
            color: #4b5b6a;
            font-weight: 600;
            font-size: 0.8rem;
        }
        .info-value {
            background: #ffffff;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 6px 8px;
            min-height: 30px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
        }
        .dropdown {
            width: 100%;
            padding: 6px 8px;
            margin-bottom: 8px;
            border-radius: 3px;
            border: 1px solid #ccc;
            background: #f2f2f2;
            font-size: 0.9rem;
        }
        .dropdown:invalid {
            border-color: #dc3545;
            background-color: #f8d7da;
        }
        .dropdown option[disabled] {
            color: #dc3545;
            font-style: italic;
        }
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
            border-left: 4px solid #dc3545;
            font-size: 0.9rem;
        }
        .loading-message {
            background: #d1ecf1;
            color: #0c5460;
            padding: 10px 15px;
            border-radius: 5px;
            margin-top: 10px;
            border-left: 4px solid #17a2b8;
            font-size: 0.9rem;
        }
        .dropdown input[type="number"] {
            width: 100%;
            padding: 8px 10px;
            margin-bottom: 10px;
            border-radius: 3px;
            border: 1px solid #ccc;
            background: #f2f2f2;
            font-size: 1rem;
            box-sizing: border-box;
        }
        label {
            font-weight: bold;
            font-size: 0.85rem;
            margin-bottom: 4px;
            display: block;
        }
        .submit-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 24px;
            font-size: 0.9rem;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 18px;
            margin-top: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }
        .submit-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }
        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
            padding-top: 6px;
            clear: both;
        }
        /* Table-like grid for SKP items - symmetric columns */
        .skp-grid-header {
            background: #0D2052;
            color: #fff;
            padding: 8px 10px;
            border-radius: 6px;
            display: grid;
            grid-template-columns: 40px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 56px;
            gap: 8px;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 0.75rem;
        }
        .skp-grid {
            display: grid;
            grid-template-columns: 40px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 56px;
            gap: 8px;
            align-items: start;
        }
        @media (min-width: 1200px) {
            .skp-grid-header {
                grid-template-columns: 44px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 60px;
                gap: 10px;
                padding: 10px 12px;
                font-size: 0.8rem;
            }
            .skp-grid {
                grid-template-columns: 44px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 60px;
                gap: 10px;
            }
        }
        @media (min-width: 1600px) {
            .skp-grid-header {
                grid-template-columns: 48px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 60px;
                gap: 10px;
                padding: 10px 12px;
            }
            .skp-grid {
                grid-template-columns: 48px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 60px;
                gap: 10px;
            }
        }
        .skp-cell-aksi { min-width: 0; }
        .btn-hapus-row {
            padding: 4px 8px;
            font-size: 0.7rem;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-hapus-row:hover { background: #c82333; }
        .btn-hapus-row:disabled { background: #ccc; cursor: not-allowed; }
        .skp-item { display: contents; }
        .skp-cell {
            background: #ffffff;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 6px 8px;
            min-height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .skp-cell:focus-within {
            border-color: rgba(13, 32, 82, 0.5);
            box-shadow: 0 0 0 2px rgba(13,32,82,0.1);
            background: #f8f9fa;
        }
        
        .skp-textarea {
            width: 100%;
            min-height: 80px;
            border: none;
            outline: none;
            background: transparent;
            font-size: 0.85rem;
            resize: vertical;
            box-sizing: border-box;
            font-family: inherit;
            line-height: 1.35;
        }
        @media (min-width: 1200px) {
            .skp-textarea {
                min-height: 90px;
                font-size: 0.88rem;
            }
        }
        @media (min-width: 1600px) {
            .skp-textarea {
                min-height: 100px;
                font-size: 0.9rem;
            }
        }
        /* Perilaku Kerja cards - table-like symmetric layout */
        .perilaku-list { 
            display: grid; 
            gap: 10px; 
        }
        .perilaku-card {
            background: #ffffff;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 10px 12px;
            display: grid;
            grid-template-columns: 40px 140px 1fr 1.2fr;
            gap: 10px;
            align-items: start;
        }
        @media (min-width: 1200px) {
            .perilaku-list {
                gap: 12px;
            }
            .perilaku-card {
                grid-template-columns: 44px 160px 1fr 1.2fr;
                gap: 12px;
                padding: 12px 14px;
            }
        }
        @media (min-width: 1600px) {
            .perilaku-list {
                gap: 12px;
            }
            .perilaku-card {
                grid-template-columns: 48px 180px 1fr 1.2fr;
                gap: 12px;
                padding: 12px 14px;
            }
        }
        .perilaku-badge {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            background: #0D2052;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        @media (min-width: 1200px) {
            .perilaku-badge {
                width: 44px;
                height: 44px;
                font-size: 0.95rem;
            }
        }
        @media (min-width: 1600px) {
            .perilaku-badge {
                width: 48px;
                height: 48px;
                font-size: 1rem;
            }
        }
        .perilaku-title { font-weight: 700; color: #0D2052; font-size: 0.8rem; }
        .perilaku-desc {
            background: #e6e6e6;
            border-radius: 6px;
            padding: 6px 8px;
            white-space: pre-line;
            font-size: 0.78rem;
        }
        .perilaku-card textarea {
            width: 100%;
            min-height: 60px;
            border: 1px solid rgba(13, 32, 82, 0.18);
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 0.85rem;
            font-family: inherit;
            box-sizing: border-box;
        }
        .add-btn-wrap {
            margin-top: 14px;
            text-align: center;
        }
        .add-btn {
            background: #b3e0ff;
            color: #00529B;
            border: none;
            border-radius: 3px;
            padding: 5px 12px;
            width: auto;
            margin: 0 0 6px 0;
            display: inline-block;
            font-weight: bold;
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .add-btn:hover {
            background: #7fd0ff;
        }
        /* Enhanced Mobile responsiveness */
        @media (max-width: 768px) {
            /* Hide complex grid on mobile, use stacked layout */
            .skp-grid-header {
                display: none;
            }
            
            .skp-grid {
                display: block;
                gap: 0;
            }
            
            .skp-item {
                display: block;
                margin-bottom: 20px;
                background: #f0f0f0;
                border: 1px solid rgba(13, 32, 82, 0.2);
                border-radius: 8px;
                padding: 15px;
            }
            
            .skp-cell {
                display: block;
                margin-bottom: 15px;
                border: none;
                background: transparent;
                padding: 0;
                min-height: auto;
            }
            
            .skp-cell:before {
                content: attr(data-label);
                display: block;
                font-weight: bold;
                color: #495057;
                margin-bottom: 5px;
                font-size: 0.9rem;
            }
            
            .skp-textarea {
                min-height: 60px;
                font-size: 0.9rem;
                width: 100%;
                border: 1px solid #ced4da;
                border-radius: 4px;
                padding: 8px;
            }
            
            .dropdown {
                width: 100%;
                font-size: 0.9rem;
                padding: 8px;
            }
            
            .perilaku-card {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 10px;
                margin-bottom: 10px;
            }
            
            .perilaku-badge {
                width: 32px;
                height: 32px;
                font-size: 0.85rem;
                margin: 0 auto 6px auto;
            }
            
            .perilaku-title {
                text-align: center;
                margin-bottom: 6px;
                font-size: 0.8rem;
            }
            
            .perilaku-desc {
                font-size: 0.78rem;
                margin-bottom: 6px;
            }
            
            .perilaku-card textarea {
                min-height: 50px;
                font-size: 0.85rem;
            }
            
            .section-title {
                font-size: 0.95rem;
                margin: 14px 0 8px 0;
            }
            
            .card {
                padding: 10px;
                margin-bottom: 10px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
                gap: 6px;
            }
            
            .info-label {
                font-weight: bold;
                margin-bottom: 2px;
            }
            
            .add-btn {
                width: auto;
                padding: 6px 12px;
                font-size: 0.8rem;
                margin-bottom: 8px;
            }
            
            .submit-btn {
                width: 100%;
                padding: 10px;
                font-size: 0.9rem;
                margin-top: 14px;
            }
        }
        
        /* Tablet responsiveness */
        @media (max-width: 1024px) and (min-width: 769px) {
            .skp-grid-header {
                grid-template-columns: 44px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 60px;
                gap: 8px;
                padding: 10px 12px;
                font-size: 0.8rem;
            }
            
            .skp-grid {
                grid-template-columns: 44px 1.8fr 1.5fr 0.8fr 1.2fr 0.8fr 1.1fr 60px;
                gap: 8px;
            }
            
            .skp-textarea {
                min-height: 90px;
                font-size: 0.88rem;
            }
            
            .perilaku-card {
                grid-template-columns: 44px 140px 1fr 1fr;
                gap: 10px;
                padding: 10px 12px;
            }
        }
        @media (max-width: 900px) {
            .row {
                flex-direction: column;
                gap: 10px;
            }
            .col {
                flex: 1 1 100%;
                min-width: 100%;
            }
        }
        
        /* Extra small devices (phones, 480px and down) */
        @media (max-width: 480px) {
            .main-content {
                padding: 60px 10px 15px;
            }
            
            .section-title {
                font-size: 1rem;
                margin: 15px 0 10px 0;
            }
            
            .card {
                padding: 8px;
            }
            
            .skp-item {
                padding: 10px;
                margin-bottom: 15px;
            }
            
            .skp-cell {
                margin-bottom: 10px;
            }
            
            .skp-textarea {
                min-height: 50px;
                font-size: 0.85rem;
            }
            
            .perilaku-card {
                padding: 8px 10px;
            }
            
            .perilaku-badge {
                width: 28px;
                height: 28px;
                font-size: 0.78rem;
            }
            
            .perilaku-title {
                font-size: 0.78rem;
            }
            
            .perilaku-desc {
                font-size: 0.75rem;
            }
            
            .add-btn {
                padding: 5px;
                font-size: 0.78rem;
            }
            
            .submit-btn {
                padding: 12px;
                font-size: 0.9rem;
            }
        }
        
        /* Landscape orientation for mobile */
        @media (max-width: 768px) and (orientation: landscape) {
            .main-content {
                padding: 60px 15px 15px;
            }
            
            .skp-item {
                padding: 12px;
            }
            
            .skp-textarea {
                min-height: 50px;
            }
        }
        @media (max-width: 1200px) {
            .skp-grid-header {
                font-size: 0.8rem;
            }
            .perilaku-card {
                grid-template-columns: 40px 140px 1fr 1fr;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
        
        <?php if ($edit_mode): ?>
            <div style="background: #fff3cd; color: #856404; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                <strong>Mode Edit:</strong> Anda sedang mengedit SKP Triwulan <?= htmlspecialchars($edit_skp_data['TRIWULAN'] ?? '') ?> Tahun <?= htmlspecialchars($edit_skp_data['TAHUN'] ?? '') ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col card">
                <h4>PEGAWAI YANG DINILAI</h4>
                <select id="pegawai" class="dropdown" <?= !empty($pegawai_error) ? 'disabled' : '' ?>>
                    <option value=""><?= empty($pegawai) ? 'Tidak ada data pegawai tersedia' : 'Pilih Pegawai' ?></option>
                    <?php
                    $nip_logged_in = $_SESSION['nip'] ?? '';
                    $pegawai_logged_in = null;
                    $pegawai_logged_in_error = '';
                    
                    if ($nip_logged_in) {
                        if (!$conn) {
                            $pegawai_logged_in_error = 'Koneksi database gagal.';
                        } else {
                            $sql = "SELECT * FROM Pegawai WHERE NIP = ? LIMIT 1";
                            $stmt = $conn->prepare($sql);
                            
                            if (!$stmt) {
                                $pegawai_logged_in_error = 'Gagal menyiapkan query database.';
                            } else {
                                $stmt->bind_param('s', $nip_logged_in);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if (!$result) {
                                    $pegawai_logged_in_error = 'Gagal mengeksekusi query database.';
                                } elseif ($result->num_rows === 0) {
                                    $pegawai_logged_in_error = 'Data pegawai dengan NIP ' . htmlspecialchars($nip_logged_in) . ' tidak ditemukan.';
                                } else {
                                    $pegawai_logged_in = $result->fetch_assoc();
                                }
                                $stmt->close();
                            }
                        }
                    } else {
                        $pegawai_logged_in_error = 'Session NIP tidak ditemukan. Silakan login ulang.';
                    }
                    ?>
                    <?php if ($pegawai_logged_in): ?>
                        <option value="<?= htmlspecialchars($pegawai_logged_in['NIP']) ?>"
                            data-nama="<?= htmlspecialchars($pegawai_logged_in['NAMA']) ?>"
                            data-nip="<?= htmlspecialchars($pegawai_logged_in['NIP']) ?>"
                            data-pangkat="<?= htmlspecialchars($pegawai_logged_in['PANGKAT_GOL_RUANG']) ?>"
                            data-jabatan="<?= htmlspecialchars($pegawai_logged_in['JABATAN']) ?>"
                            data-unit="<?= htmlspecialchars($pegawai_logged_in['UNIT_KERJA']) ?>"
                            data-atasan="<?= isset($pegawai_logged_in['ATASAN_LANGSUNG']) ? htmlspecialchars($pegawai_logged_in['ATASAN_LANGSUNG']) : '' ?>"
                            selected>
                            <?= htmlspecialchars($pegawai_logged_in['NAMA']) ?> (<?= htmlspecialchars($pegawai_logged_in['NIP']) ?>)
                        </option>
                    <?php elseif (!empty($pegawai_logged_in_error)): ?>
                        <option value="" disabled style="color: #dc3545; font-style: italic;">
                            ⚠️ <?= htmlspecialchars($pegawai_logged_in_error) ?>
                        </option>
                    <?php endif; ?>
                </select>
                <?php if (!empty($pegawai_error)): ?>
                    <div class="error-message">
                        <strong>⚠️ Error:</strong> <?= htmlspecialchars($pegawai_error) ?>
                    </div>
                <?php endif; ?>
                <div class="info-grid" id="pegawai-info">
                    <div class="info-label">Nama</div><div class="info-value" id="info-nama"></div>
                    <div class="info-label">NIP</div><div class="info-value" id="info-nip"></div>
                    <div class="info-label">Pangkat/Gol Ruang</div><div class="info-value" id="info-pangkat"></div>
                    <div class="info-label">Jabatan</div><div class="info-value" id="info-jabatan"></div>
                    <div class="info-label">Unit Kerja</div><div class="info-value" id="info-unit"></div>
                </div>
            </div>
            <div class="col card">
                <h4>PEJABAT PENILAI KERJA</h4>
                <div class="info-grid" id="penilai-info">
                    <div class="info-label">Nama</div><div class="info-value" id="penilai-nama"></div>
                    <div class="info-label">NIP</div><div class="info-value" id="penilai-nip"></div>
                    <div class="info-label">Pangkat/Gol Ruang</div><div class="info-value" id="penilai-pangkat"></div>
                    <div class="info-label">Jabatan</div><div class="info-value" id="penilai-jabatan"></div>
                    <div class="info-label">Unit Kerja</div><div class="info-value" id="penilai-unit"></div>
                </div>
            </div>
        </div>
        
        <div class="section-title">PERIODE PENILAIAN</div>
        <div class="row" style="margin-bottom: 30px;">
            <div class="col">
                <label for="tahun">TAHUN:</label><br>
                <input type="number" id="tahun" name="tahun" class="dropdown" min="2020" max="2030" value="<?= $edit_mode && !empty($edit_skp_data['TAHUN']) ? htmlspecialchars($edit_skp_data['TAHUN']) : date('Y') ?>" required form="skp-form">
            </div>
        </div>
        
        <form id="skp-form" method="post" <?= !empty($pegawai_error) ? 'style="opacity: 0.6; pointer-events: none;"' : '' ?>>
            <input type="hidden" name="pegawai_nip" id="pegawai_nip_input" value="">
            
            <?php if (!empty($errorMsg)): ?>
                <div id="alert-error" style="background:#fdecea;color:#b00020;padding:12px 20px;border-radius:8px;margin-bottom:18px;border-left:4px solid #dc3545;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <strong>Error:</strong> <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php elseif (!empty($successMsg)): ?>
                <div id="alert-success" style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:18px;border-left:4px solid #28a745;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
                    <strong>Berhasil:</strong> <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>
            
            <div class="section-title">A. KINERJA UTAMA</div>
            <div class="skp-grid-header">
                <div>NO</div>
                <div>RENCANA HASIL KINERJA</div>
                <div>INDIKATOR KINERJA INDIVIDU</div>
                <div>TARGET</div>
                <div>REALISASI BERDASARKAN BUKTI DUKUNG</div>
                <div>SATUAN</div>
                <div>PERSPEKTIF</div>
                <div>AKSI</div>
            </div>
            <div class="skp-grid" id="utama-table">
                <?php if ($edit_mode && !empty($edit_utama_data)): ?>
                    <?php foreach ($edit_utama_data as $index => $item): ?>
                        <div class="skp-item">
                            <div class="skp-cell" data-label="No"><?= $index + 1 ?></div>
                            <div class="skp-cell" data-label="Rencana Hasil Kerja"><textarea class="skp-textarea" name="utama_kerja[]" required><?= htmlspecialchars($item['RENCANA_HASIL_KERJA'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Indikator Kinerja"><textarea class="skp-textarea" name="utama_indikator[]" required><?= htmlspecialchars($item['INDIKATOR_KINERJA_INDIVIDU'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Target"><textarea class="skp-textarea" name="utama_target[]" required><?= htmlspecialchars($item['TARGET'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Realisasi"><textarea class="skp-textarea" name="utama_realisasi[]"><?= htmlspecialchars($item['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Satuan"><input type="text" name="utama_satuan[]" class="skp-textarea" value="<?= htmlspecialchars($item['SATUAN'] ?? '') ?>" required></div>
                            <div class="skp-cell" data-label="Perspektif"><textarea class="skp-textarea" name="utama_perspektif[]" required><?= htmlspecialchars($item['PERSPEKTIF'] ?? '') ?></textarea></div>
                            <div class="skp-cell skp-cell-aksi" data-label="Aksi"><button type="button" class="btn-hapus-row" onclick="removeRow('utama-table', this)" title="Hapus baris">Hapus</button></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="skp-item">
                        <div class="skp-cell" data-label="No">1</div>
                        <div class="skp-cell" data-label="Rencana Hasil Kerja"><textarea class="skp-textarea" name="utama_kerja[]" required></textarea></div>
                        <div class="skp-cell" data-label="Indikator Kinerja"><textarea class="skp-textarea" name="utama_indikator[]" required></textarea></div>
                        <div class="skp-cell" data-label="Target"><textarea class="skp-textarea" name="utama_target[]" required></textarea></div>
                        <div class="skp-cell" data-label="Realisasi"><textarea class="skp-textarea" name="utama_realisasi[]"></textarea></div>
                        <div class="skp-cell" data-label="Satuan"><input type="text" name="utama_satuan[]" class="skp-textarea" required></div>
                        <div class="skp-cell" data-label="Perspektif"><textarea class="skp-textarea" name="utama_perspektif[]" required></textarea></div>
                        <div class="skp-cell skp-cell-aksi" data-label="Aksi"><button type="button" class="btn-hapus-row" onclick="removeRow('utama-table', this)" title="Hapus baris">Hapus</button></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="add-btn-wrap"><button type="button" class="add-btn" onclick="addRow('utama-table')">+ TAMBAH HASIL KERJA</button></div>
            
            <div class="section-title">B. KINERJA TAMBAHAN</div>
            <div class="skp-grid-header">
                <div>NO</div>
                <div>RENCANA HASIL KERJA</div>
                <div>INDIKATOR KINERJA INDIVIDU</div>
                <div>TARGET</div>
                <div>REALISASI BERDASARKAN BUKTI DUKUNG</div>
                <div>SATUAN</div>
                <div>PERSPEKTIF</div>
                <div>AKSI</div>
            </div>
            <div class="skp-grid" id="tambahan-table">
                <?php if ($edit_mode && !empty($edit_tambahan_data)): ?>
                    <?php foreach ($edit_tambahan_data as $index => $item): ?>
                        <div class="skp-item">
                            <div class="skp-cell" data-label="No"><?= $index + 1 ?></div>
                            <div class="skp-cell" data-label="Rencana Hasil Kerja"><textarea class="skp-textarea" name="tambahan_kerja[]"><?= htmlspecialchars($item['RENCANA_HASIL_KERJA'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Indikator Kinerja"><textarea class="skp-textarea" name="tambahan_indikator[]"><?= htmlspecialchars($item['INDIKATOR_KINERJA_INDIVIDU'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Target"><textarea class="skp-textarea" name="tambahan_target[]"><?= htmlspecialchars($item['TARGET'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Realisasi"><textarea class="skp-textarea" name="tambahan_realisasi[]"><?= htmlspecialchars($item['REALISASI_BERDASARKAN_BUKTI_DUKUNG'] ?? '') ?></textarea></div>
                            <div class="skp-cell" data-label="Satuan"><input type="text" name="tambahan_satuan[]" class="skp-textarea" value="<?= htmlspecialchars($item['SATUAN'] ?? '') ?>"></div>
                            <div class="skp-cell" data-label="Perspektif"><textarea class="skp-textarea" name="tambahan_perspektif[]"><?= htmlspecialchars($item['PERSPEKTIF'] ?? '') ?></textarea></div>
                            <div class="skp-cell skp-cell-aksi" data-label="Aksi"><button type="button" class="btn-hapus-row" onclick="removeRow('tambahan-table', this)" title="Hapus baris">Hapus</button></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="skp-item">
                        <div class="skp-cell" data-label="No">1</div>
                        <div class="skp-cell" data-label="Rencana Hasil Kerja"><textarea class="skp-textarea" name="tambahan_kerja[]"></textarea></div>
                        <div class="skp-cell" data-label="Indikator Kinerja"><textarea class="skp-textarea" name="tambahan_indikator[]"></textarea></div>
                        <div class="skp-cell" data-label="Target"><textarea class="skp-textarea" name="tambahan_target[]"></textarea></div>
                        <div class="skp-cell" data-label="Realisasi"><textarea class="skp-textarea" name="tambahan_realisasi[]"></textarea></div>
                        <div class="skp-cell" data-label="Satuan"><input type="text" name="tambahan_satuan[]" class="skp-textarea"></div>
                        <div class="skp-cell" data-label="Perspektif"><textarea class="skp-textarea" name="tambahan_perspektif[]"></textarea></div>
                        <div class="skp-cell skp-cell-aksi" data-label="Aksi"><button type="button" class="btn-hapus-row" onclick="removeRow('tambahan-table', this)" title="Hapus baris">Hapus</button></div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="add-btn-wrap"><button type="button" class="add-btn" onclick="addRow('tambahan-table')">+ TAMBAH HASIL KERJA</button></div>
            
            <div class="section-title">PERILAKU KERJA</div>
            <div class="perilaku-list">
                <div class="perilaku-card">
                    <div class="perilaku-badge">1</div>
                    <div class="perilaku-title">BERORIENTASI PELAYANAN</div>
                    <div class="perilaku-desc">- Memahami dan memenuhi kebutuhan masyarakat.
- Ramah, cekatan, solutif, dan dapat diandalkan.
- Melakukan perbaikan tiada henti.</div>
                    <div><textarea name="perilaku_ekspektasi[]" placeholder="Ekspektasi Khusus Pimpinan..." required><?= htmlspecialchars($edit_perilaku_data['EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN'] ?? '') ?></textarea></div>
                </div>
                <div class="perilaku-card">
                    <div class="perilaku-badge">2</div>
                    <div class="perilaku-title">AKUNTABEL</div>
                    <div class="perilaku-desc">- Melaksanakan tugas dengan jujur, bertanggungjawab, cermat, disiplin dan berintegritas tinggi.
- Menggunakan kekayaan dan barang milik negara secara bertanggungjawab, efektif dan efisien.
- Tidak menyalahgunakan kewenangan jabatan.</div>
                    <div><textarea name="perilaku_ekspektasi[]" placeholder="Ekspektasi Khusus Pimpinan..." required><?= htmlspecialchars($edit_perilaku_data['EKSPEKTASI_PIMPINAN_AKUNTABEL'] ?? '') ?></textarea></div>
                </div>
                <div class="perilaku-card">
                    <div class="perilaku-badge">3</div>
                    <div class="perilaku-title">KOMPETEN</div>
                    <div class="perilaku-desc">- Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah.
- Membantu orang lain belajar.
- Melaksanakan tugas dengan kualitas terbaik.</div>
                    <div><textarea name="perilaku_ekspektasi[]" placeholder="Ekspektasi Khusus Pimpinan..." required><?= htmlspecialchars($edit_perilaku_data['EKSPEKTASI_PIMPINAN_KOMPETEN'] ?? '') ?></textarea></div>
                </div>
                <div class="perilaku-card">
                    <div class="perilaku-badge">4</div>
                    <div class="perilaku-title">HARMONIS</div>
                    <div class="perilaku-desc">- Menghargai setiap orang apapun latar belakangnya.
- Suka menolong orang lain.
- Membangun lingkungan kerja yang kondusif.</div>
                    <div><textarea name="perilaku_ekspektasi[]" placeholder="Ekspektasi Khusus Pimpinan..." required><?= htmlspecialchars($edit_perilaku_data['EKSPEKTASI_PIMPINAN_HARMONIS'] ?? '') ?></textarea></div>
                </div>
                <div class="perilaku-card">
                    <div class="perilaku-badge">5</div>
                    <div class="perilaku-title">LOYAL</div>
                    <div class="perilaku-desc">- Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, setia pada Negara Kesatuan Republik Indonesia serta pemerintahan yang sah.
- Menjaga nama baik ASN, Pimpinan, Instansi dan Negara.
- Menjaga rahasia jabatan dan negara.</div>
                    <div><textarea name="perilaku_ekspektasi[]" placeholder="Ekspektasi Khusus Pimpinan..." required><?= htmlspecialchars($edit_perilaku_data['EKSPEKTASI_PIMPINAN_LOYAL'] ?? '') ?></textarea></div>
                </div>
                <div class="perilaku-card">
                    <div class="perilaku-badge">6</div>
                    <div class="perilaku-title">ADAPTIF</div>
                    <div class="perilaku-desc">- Cepat menyesuaikan diri menghadapi perubahan
- Terus berinovasi dan mengembangkan kreativitas
- Bertindak proaktif</div>
                    <div><textarea name="perilaku_ekspektasi[]" placeholder="Ekspektasi Khusus Pimpinan..." required><?= htmlspecialchars($edit_perilaku_data['EKSPEKTASI_PIMPINAN_ADAPTIF'] ?? '') ?></textarea></div>
                </div>
                <div class="perilaku-card">
                    <div class="perilaku-badge">7</div>
                    <div class="perilaku-title">KOLABORATIF</div>
                    <div class="perilaku-desc">- Memberi kesempatan kepada berbagai pihak untuk berkontribusi.
- Terbuka dalam bekerjasama untuk menghasilkan nilai tambah.
- Menggerakan pemanfaatan berbagai sumber daya untuk tujuan bersama.</div>
                    <div><textarea name="perilaku_ekspektasi[]" placeholder="Ekspektasi Khusus Pimpinan..." required><?= htmlspecialchars($edit_perilaku_data['EKSPEKTASI_PIMPINAN_KOLABORATIF'] ?? '') ?></textarea></div>
                </div>
            </div>
            
            <div class="form-actions">
                <?php if ($edit_mode): ?>
                    <button type="submit" class="submit-btn" name="save_edit" value="1">SAVE</button>
                <?php else: ?>
                    <button type="submit" class="submit-btn">SUBMIT</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var pegawaiDropdown = document.getElementById('pegawai');
        <?php if ($edit_mode && !empty($edit_skp_data['NIP'])): ?>
            // In edit mode, select the employee from the existing data
            var editNip = '<?= htmlspecialchars($edit_skp_data['NIP']) ?>';
            for (var i = 0; i < pegawaiDropdown.options.length; i++) {
                if (pegawaiDropdown.options[i].value === editNip) {
                    pegawaiDropdown.selectedIndex = i;
                    var event = new Event('change');
                    pegawaiDropdown.dispatchEvent(event);
                    break;
                }
            }
        <?php else: ?>
            // In new mode, auto-select the first employee if available
            if (pegawaiDropdown && pegawaiDropdown.options.length === 2) {
                pegawaiDropdown.selectedIndex = 1;
                var event = new Event('change');
                pegawaiDropdown.dispatchEvent(event);
            }
        <?php endif; ?>
    });
    
    document.getElementById('pegawai').addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        var nip = selected.getAttribute('data-nip') || '';
        
        // Check if the selected option is an error message
        if (selected.disabled && selected.style.color === 'rgb(220, 53, 69)') {
            // This is an error option, don't process further
            return;
        }
        
        // Update pegawai info
        document.getElementById('info-nama').textContent = selected.getAttribute('data-nama') || '';
        document.getElementById('info-nip').textContent = selected.getAttribute('data-nip') || '';
        document.getElementById('info-pangkat').textContent = selected.getAttribute('data-pangkat') || '';
        document.getElementById('info-jabatan').textContent = selected.getAttribute('data-jabatan') || '';
        document.getElementById('info-unit').textContent = selected.getAttribute('data-unit') || '';
        
        // Set hidden input for pegawai nip
        document.getElementById('pegawai_nip_input').value = nip;
        
        // Clear penilai info first
        document.getElementById('penilai-nama').textContent = '';
        document.getElementById('penilai-nip').textContent = '';
        document.getElementById('penilai-pangkat').textContent = '';
        document.getElementById('penilai-jabatan').textContent = '';
        document.getElementById('penilai-unit').textContent = '';
        
        // Get atasan langsung using AJAX
        if (nip) {
            // Show loading indicator
            document.getElementById('penilai-nama').textContent = 'Memuat data atasan...';
            
            fetch('get_atasan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'nip=' + encodeURIComponent(nip)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.atasan) {
                    document.getElementById('penilai-nama').textContent = data.atasan.NAMA || '';
                    document.getElementById('penilai-nip').textContent = data.atasan.NIP || '';
                    document.getElementById('penilai-pangkat').textContent = data.atasan.PANGKAT_GOL_RUANG || '';
                    document.getElementById('penilai-jabatan').textContent = data.atasan.JABATAN || '';
                    document.getElementById('penilai-unit').textContent = data.atasan.UNIT_KERJA || '';
                } else {
                    // Show error message
                    var errorMsg = data.error || 'Gagal mengambil data atasan';
                    document.getElementById('penilai-nama').textContent = '⚠️ ' + errorMsg;
                    document.getElementById('penilai-nip').textContent = '';
                    document.getElementById('penilai-pangkat').textContent = '';
                    document.getElementById('penilai-jabatan').textContent = '';
                    document.getElementById('penilai-unit').textContent = '';
                }
            })
            .catch(error => {
                console.error('Error fetching atasan data:', error);
                // Show error message
                document.getElementById('penilai-nama').textContent = '⚠️ Gagal mengambil data atasan';
                document.getElementById('penilai-nip').textContent = '';
                document.getElementById('penilai-pangkat').textContent = '';
                document.getElementById('penilai-jabatan').textContent = '';
                document.getElementById('penilai-unit').textContent = '';
            });
        } else {
            // No NIP selected, clear all fields
            document.getElementById('penilai-nama').textContent = '';
            document.getElementById('penilai-nip').textContent = '';
            document.getElementById('penilai-pangkat').textContent = '';
            document.getElementById('penilai-jabatan').textContent = '';
            document.getElementById('penilai-unit').textContent = '';
        }
    });
    
    function removeRow(tableId, btn) {
        var grid = document.getElementById(tableId);
        var items = grid.querySelectorAll('.skp-item');
        if (items.length <= 1) {
            alert('Minimal satu baris harus diisi.');
            return;
        }
        var row = btn.closest('.skp-item');
        if (row) row.remove();
        items = grid.querySelectorAll('.skp-item');
        for (var i = 0; i < items.length; i++) {
            var noCell = items[i].querySelector('.skp-cell[data-label="No"]');
            if (noCell) noCell.textContent = i + 1;
        }
    }
    function addRow(tableId) {
        var grid = document.getElementById(tableId);
        var existingItems = grid.querySelectorAll('.skp-item');
        var no = existingItems.length + 1;
        var isUtama = tableId === 'utama-table';
        var wrapper = document.createElement('div');
        wrapper.className = 'skp-item';
        wrapper.innerHTML =
            '<div class="skp-cell" data-label="No">' + no + '</div>' +
            '<div class="skp-cell" data-label="Rencana Hasil Kerja"><textarea class="skp-textarea" ' + (isUtama ? 'required ' : '') + 'name="' + (isUtama ? 'utama_kerja[]' : 'tambahan_kerja[]') + '"></textarea></div>' +
            '<div class="skp-cell" data-label="Indikator Kinerja"><textarea class="skp-textarea" ' + (isUtama ? 'required ' : '') + 'name="' + (isUtama ? 'utama_indikator[]' : 'tambahan_indikator[]') + '"></textarea></div>' +
            '<div class="skp-cell" data-label="Target"><textarea class="skp-textarea" ' + (isUtama ? 'required ' : '') + 'name="' + (isUtama ? 'utama_target[]' : 'tambahan_target[]') + '"></textarea></div>' +
            '<div class="skp-cell" data-label="Realisasi"><textarea class="skp-textarea" name="' + (isUtama ? 'utama_realisasi[]' : 'tambahan_realisasi[]') + '"></textarea></div>' +
            '<div class="skp-cell" data-label="Satuan"><input type="text" class="skp-textarea" name="' + (isUtama ? 'utama_satuan[]' : 'tambahan_satuan[]') + '" ' + (isUtama ? 'required' : '') + '></div>' +
            '<div class="skp-cell" data-label="Perspektif"><textarea class="skp-textarea" ' + (isUtama ? 'required ' : '') + 'name="' + (isUtama ? 'utama_perspektif[]' : 'tambahan_perspektif[]') + '"></textarea></div>' +
            '<div class="skp-cell skp-cell-aksi" data-label="Aksi"><button type="button" class="btn-hapus-row" onclick="removeRow(\'' + tableId + '\', this)" title="Hapus baris">Hapus</button></div>';
        grid.appendChild(wrapper);
    }
    </script>
</body>
</html>
