<?php
error_reporting(0);
ini_set('display_errors', 0);
session_start();

// SKP Detail page loaded

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get id_skp_global from URL
$id_skp_global = $_GET['id_skp_global'] ?? '';

if (empty($id_skp_global)) {
    die('ID SKP Global tidak ditemukan');
}

// Check if this is SKP Akhir data (from skp_akhir.php)
$is_skp_akhir = isset($_GET['skp_akhir']) && $_GET['skp_akhir'] == '1';

// Debug logging
error_log("🔍 SKP Detail Debug:");
error_log("  is_skp_akhir: " . ($is_skp_akhir ? 'true' : 'false'));
error_log("  GET skp_akhir: " . ($_GET['skp_akhir'] ?? 'not set'));
error_log("  REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);

// Database connection
require_once '../config/database.php';
try {
    $conn = getDatabaseConnection();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission for evaluation
$evaluation_success = '';
$evaluation_error = '';

// Debug form submission
error_log("🔍 Form Submission Debug:");
error_log("  REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("  POST data keys: " . implode(', ', array_keys($_POST)));
error_log("  evaluate_skp isset: " . (isset($_POST['evaluate_skp']) ? 'true' : 'false'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evaluate_skp'])) {
    $update_success = true;
    $evaluation_error = '';
    
    // Debug: Log received data
    error_log("Form submitted - SKP Feedback: " . print_r($_POST['skp_feedback'] ?? 'none', true));
    error_log("Form submitted - Perilaku Feedback: " . print_r($_POST['perilaku_feedback'] ?? 'none', true));
    
    // Process SKP evaluation
    if (isset($_POST['skp_feedback']) && is_array($_POST['skp_feedback'])) {
        $skp_feedback = $_POST['skp_feedback'];
        $skp_stickers = $_POST['skp_stickers'] ?? [];
        $skp_ids = $_POST['skp_ids'] ?? [];
        
        error_log("🔍 Total SKP feedback records to process: " . count($skp_feedback));
        $processed_count = 0;
        
        foreach ($skp_feedback as $index => $feedback) {
            error_log("🔍 Starting processing for index $index");
            
            // First, check if this activity was not performed (TARGET=0 and REALISASI=0)
            // We need to fetch the TARGET and REALISASI values for this specific SKP
            $should_skip_activity = false;
            if ($is_skp_akhir) {
                $stmt_check_activity = $conn->prepare("SELECT TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG FROM skp_akhir_pegawai WHERE ID_SKP_GLOBAL = ? ORDER BY ID_SKP LIMIT 1 OFFSET ?");
            } else {
                $stmt_check_activity = $conn->prepare("SELECT TARGET, REALISASI_BERDASARKAN_BUKTI_DUKUNG FROM skp_pegawai WHERE id_skp_global = ? ORDER BY id_skp LIMIT 1 OFFSET ?");
            }
            
            if ($stmt_check_activity) {
                $stmt_check_activity->bind_param('ii', $id_skp_global, $index);
                $stmt_check_activity->execute();
                $result_check_activity = $stmt_check_activity->get_result();
                if ($row_check_activity = $result_check_activity->fetch_assoc()) {
                    $target_val = trim($row_check_activity['TARGET']);
                    $realisasi_val = trim($row_check_activity['REALISASI_BERDASARKAN_BUKTI_DUKUNG']);
                    
                    // Skip if activity not performed (TARGET=0 and REALISASI=0)
                    if ($target_val === '0' && $realisasi_val === '0') {
                        $should_skip_activity = true;
                        error_log("🔍 Skipping index $index - activity not performed (TARGET=0, REALISASI=0)");
                    }
                }
                $stmt_check_activity->close();
            }
            
            // If activity was not performed, skip processing
            if ($should_skip_activity) {
                continue;
            }
            
            // Handle sticker (thumbs up) - store 'C' if checked, NULL if not
            $sticker_value = (isset($skp_stickers[$index]) && $skp_stickers[$index] == '1') ? 'C' : null;
            
            error_log("🔍 Processing SKP feedback for index $index, is_skp_akhir: " . ($is_skp_akhir ? 'true' : 'false'));
            error_log("🔍 Feedback value: " . $feedback);
            error_log("🔍 Sticker value: " . ($sticker_value ? 'C' : 'NULL'));
            
            // Skip if feedback is empty and no sticker
            if (empty($feedback) && empty($sticker_value)) {
                error_log("🔍 Skipping index $index - no feedback and no sticker");
                continue;
            } else {
                try {
                
                $processed_count++;
                error_log("🔍 Incremented processed_count to: $processed_count");
                
                if ($is_skp_akhir) {
                    // For SKP Akhir, we need to get the ID_SKP for this specific index
                    $stmt_check = $conn->prepare("SELECT ID_SKP FROM skp_akhir_pegawai WHERE ID_SKP_GLOBAL = ? ORDER BY ID_SKP LIMIT 1 OFFSET ?");
                    if ($stmt_check) {
                        $stmt_check->bind_param('ii', $id_skp_global, $index);
                        $stmt_check->execute();
                        $result_check = $stmt_check->get_result();
                        error_log("🔍 Query executed for index $index, found " . $result_check->num_rows . " rows");
                        if ($row_check = $result_check->fetch_assoc()) {
                            $skp_id = $row_check['ID_SKP'];
                            
                            // Update using ID_SKP
                            $stmt = $conn->prepare("UPDATE skp_akhir_pegawai SET UMPAN_BALIK_DENGAN_BUKTI_DUKUNG = ?, UMPAN_BALIK_STICKER = ? WHERE ID_SKP = ?");
                            if ($stmt) {
                                $stmt->bind_param('ssi', $feedback, $sticker_value, $skp_id);
                                if (!$stmt->execute()) {
                                    $update_success = false;
                                    $evaluation_error = 'Error updating SKP Akhir: ' . $conn->error;
                                    error_log("SKP Akhir Update Error: " . $conn->error);
                                } else {
                                    error_log("SKP Akhir Updated successfully for ID_SKP: " . $skp_id . " with sticker: " . ($sticker_value ? 'C' : 'NULL'));
                                }
                                $stmt->close();
                            } else {
                                $update_success = false;
                                $evaluation_error = 'Error preparing SKP Akhir statement: ' . $conn->error;
                                error_log("SKP Akhir Prepare Error: " . $conn->error);
                            }
                        } else {
                            $update_success = false;
                            $evaluation_error = 'Error: Could not find ID_SKP for index ' . $index;
                            error_log("SKP Akhir ID Error: Could not find ID_SKP for index " . $index);
                        }
                        $stmt_check->close();
                    } else {
                        $update_success = false;
                        $evaluation_error = 'Error preparing SKP Akhir check statement: ' . $conn->error;
                        error_log("SKP Akhir Check Prepare Error: " . $conn->error);
                    }
                } else {
                    // For regular SKP, use the original logic
                    $nip = $_POST['skp_nips'][$index] ?? null;
                    if (!$nip) {
                        // Fetch NIP from database using the skp_id
                        $stmt_check = $conn->prepare("SELECT NIP FROM skp_pegawai WHERE id_skp_global = ? LIMIT 1 OFFSET ?");
                        if ($stmt_check) {
                            $stmt_check->bind_param('ii', $id_skp_global, $index);
                            $stmt_check->execute();
                            $result_check = $stmt_check->get_result();
                            if ($row_check = $result_check->fetch_assoc()) {
                                $nip = $row_check['NIP'];
                            }
                            $stmt_check->close();
                        }
                    }
                    
                    if ($nip) {
                        $stmt = $conn->prepare("UPDATE skp_pegawai SET UMPAN_BALIK_DENGAN_BUKTI_DUKUNG = ?, UMPAN_BALIK_STICKER = ? WHERE id_skp_global = ? AND NIP = ?");
                        if ($stmt) {
                            $stmt->bind_param('ssis', $feedback, $sticker_value, $id_skp_global, $nip);
                            if (!$stmt->execute()) {
                                $update_success = false;
                                $evaluation_error = 'Error updating SKP: ' . $conn->error;
                                error_log("SKP Update Error: " . $conn->error);
                            } else {
                                error_log("SKP Updated successfully for NIP: " . $nip . " with sticker: " . ($sticker_value ? 'C' : 'NULL'));
                            }
                            $stmt->close();
                        } else {
                            $update_success = false;
                            $evaluation_error = 'Error preparing SKP statement: ' . $conn->error;
                            error_log("SKP Prepare Error: " . $conn->error);
                        }
                    } else {
                        $update_success = false;
                        $evaluation_error = 'Error: Could not find NIP for index ' . $index;
                        error_log("SKP NIP Error: Could not find NIP for index " . $index);
                    }
                }
                } catch (Exception $e) {
                    error_log("🔍 Exception processing index $index: " . $e->getMessage());
                    $update_success = false;
                    $evaluation_error = 'Error processing feedback for index ' . $index . ': ' . $e->getMessage();
                }
            }
            error_log("🔍 Finished processing index $index");
        }
        error_log("🔍 Total records processed: $processed_count");
    }
    
    // Process Perilaku Kerja evaluation
    if (isset($_POST['perilaku_feedback']) && is_array($_POST['perilaku_feedback'])) {
        $perilaku_feedback = $_POST['perilaku_feedback'];
        
        $perilaku_fields = [
            'UMPAN_BALIK_BERORIENTASI_PELAYANAN',
            'UMPAN_BALIK_AKUNTABEL', 
            'UMPAN_BALIK_KOMPETEN',
            'UMPAN_BALIK_HARMONIS',
            'UMPAN_BALIK_LOYAL',
            'UMPAN_BALIK_ADAPTIF',
            'UMPAN_BALIK_KOLABORATIF'
        ];
        
        $update_fields = [];
        $update_values = [];
        $types = '';
        
        foreach ($perilaku_fields as $field) {
            $key = strtolower(str_replace('UMPAN_BALIK_', '', $field));
            if (isset($perilaku_feedback[$key]) && !empty($perilaku_feedback[$key])) {
                $update_fields[] = $field . ' = ?';
                $update_values[] = $perilaku_feedback[$key];
                $types .= 's';
            }
        }
        
        if (!empty($update_fields)) {
            $update_values[] = $id_skp_global;
            $types .= 'i';
            
            if ($is_skp_akhir) {
                // For SKP Akhir, use skp_akhir_perilaku_pegawai table
                $sql = "UPDATE skp_akhir_perilaku_pegawai SET " . implode(', ', $update_fields) . " WHERE ID_SKP_GLOBAL = ?";
            } else {
                // For regular SKP, use skp_perilaku_pegawai table
                $sql = "UPDATE skp_perilaku_pegawai SET " . implode(', ', $update_fields) . " WHERE id_skp_global = ?";
            }
            
            error_log("Perilaku SQL: " . $sql);
            error_log("Perilaku Values: " . print_r($update_values, true));
            error_log("Perilaku Types: " . $types);
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$update_values);
                if (!$stmt->execute()) {
                    $update_success = false;
                    $evaluation_error = 'Error updating Perilaku: ' . $conn->error;
                    error_log("Perilaku Update Error: " . $conn->error);
                } else {
                    error_log("Perilaku Updated successfully for ID: " . $id_skp_global);
                }
                $stmt->close();
            } else {
                $update_success = false;
                $evaluation_error = 'Error preparing Perilaku statement: ' . $conn->error;
                error_log("Perilaku Prepare Error: " . $conn->error);
            }
        }
    }
    
    // Process Rating and Predikat fields
    if (isset($_POST['rating_perilaku_kerja']) || isset($_POST['predikat_kinerja_pegawai']) || isset($_POST['rating_hasil_kerja']) || isset($_POST['capaian_kinerja_organisasi']) || isset($_POST['umpan_balik_dengan_bukti_dukung']) || isset($_POST['umpan_balik_sticker'])) {
        $rating_fields = [];
        $rating_values = [];
        $rating_types = '';
        
        if (isset($_POST['rating_perilaku_kerja']) && !empty($_POST['rating_perilaku_kerja'])) {
            $rating_fields[] = 'RATING_PERILAKU_KERJA = ?';
            $rating_values[] = $_POST['rating_perilaku_kerja'];
            $rating_types .= 's';
        }
        
        if (isset($_POST['predikat_kinerja_pegawai']) && !empty($_POST['predikat_kinerja_pegawai'])) {
            $rating_fields[] = 'PREDIKAT_KINERJA_PEGAWAI = ?';
            $rating_values[] = $_POST['predikat_kinerja_pegawai'];
            $rating_types .= 's';
        }
        
        if (isset($_POST['rating_hasil_kerja']) && !empty($_POST['rating_hasil_kerja'])) {
            $rating_fields[] = 'RATING_HASIL_KERJA = ?';
            $rating_values[] = $_POST['rating_hasil_kerja'];
            $rating_types .= 's';
        }
        
        if (isset($_POST['capaian_kinerja_organisasi']) && !empty($_POST['capaian_kinerja_organisasi'])) {
            $rating_fields[] = 'CAPAIAN_KINERJA_ORGANISASI = ?';
            $rating_values[] = $_POST['capaian_kinerja_organisasi'];
            $rating_types .= 's';
        }
        
        if (isset($_POST['umpan_balik_dengan_bukti_dukung']) && !empty($_POST['umpan_balik_dengan_bukti_dukung'])) {
            $rating_fields[] = 'UMPAN_BALIK_DENGAN_BUKTI_DUKUNG = ?';
            $rating_values[] = $_POST['umpan_balik_dengan_bukti_dukung'];
            $rating_types .= 's';
        }
        
        if (isset($_POST['umpan_balik_sticker']) && !empty($_POST['umpan_balik_sticker'])) {
            $rating_fields[] = 'UMPAN_BALIK_STICKER = ?';
            $rating_values[] = $_POST['umpan_balik_sticker'];
            $rating_types .= 's';
        }
        
        if (!empty($rating_fields)) {
            $rating_values[] = $id_skp_global;
            $rating_types .= 'i';
            
            if ($is_skp_akhir) {
                // For SKP Akhir, use skp_akhir_pegawai table
                $sql = "UPDATE skp_akhir_pegawai SET " . implode(', ', $rating_fields) . " WHERE ID_SKP_GLOBAL = ?";
            } else {
                // For regular SKP, use skp_pegawai table
                $sql = "UPDATE skp_pegawai SET " . implode(', ', $rating_fields) . " WHERE id_skp_global = ?";
            }
            
            error_log("Rating SQL: " . $sql);
            error_log("Rating Values: " . print_r($rating_values, true));
            error_log("Rating Types: " . $rating_types);
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param($rating_types, ...$rating_values);
                if (!$stmt->execute()) {
                    $update_success = false;
                    $evaluation_error = 'Error updating Rating/Predikat: ' . $conn->error;
                    error_log("Rating Update Error: " . $conn->error);
                } else {
                    error_log("Rating/Predikat Updated successfully for ID: " . $id_skp_global);
                }
                $stmt->close();
            } else {
                $update_success = false;
                $evaluation_error = 'Error preparing Rating/Predikat statement: ' . $conn->error;
                error_log("Rating Prepare Error: " . $conn->error);
            }
        }
    }
    
    if ($update_success) {
        // For SKP Akhir, close the window instead of redirecting
        if ($is_skp_akhir) {
            echo "<!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Evaluasi Berhasil</title>
            </head>
            <body>
                <script>
                    alert('Evaluasi berhasil disimpan!');
                    if (window.opener) {
                        window.opener.location.reload();
                    }
                    window.close();
                </script>
            </body>
            </html>";
            exit();
        } else {
            // For regular SKP, redirect back to evaluation page with success message
            header("Location: skploginpage.php?success=evaluasi_berhasil");
            exit();
        }
    } else {
        if (empty($evaluation_error)) {
            $evaluation_error = 'Gagal menyimpan evaluasi. Silakan coba lagi.';
        }
        error_log("Final Error: " . $evaluation_error);
    }
}

// In the PHP section at the top, handle the POST for 'edit_skp' to update the SKP values in the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_skp'])) {
    // Update Kinerja Utama
    if (isset($_POST['utama_id'])) {
        foreach ($_POST['utama_id'] as $i => $id) {
            if ($is_skp_akhir) {
                // For SKP Akhir, use skp_akhir_pegawai table
                $stmt = $conn->prepare("UPDATE skp_akhir_pegawai SET RHK_PIMPINAN_INTERV=?, RENCANA_HASIL_KERJA=?, ASPEK=?, INDIKATOR_KINERJA_INDIVIDU=?, TARGET=?, REALISASI_BERDASARKAN_BUKTI_DUKUNG=? WHERE ID_SKP=? AND NIP=?");
            } else {
                // For regular SKP, use skp_pegawai table
                $stmt = $conn->prepare("UPDATE skp_pegawai SET RHK_PIMPINAN_INTERV=?, RENCANA_HASIL_KERJA=?, ASPEK=?, INDIKATOR_KINERJA_INDIVIDU=?, TARGET=?, REALISASI_BERDASARKAN_BUKTI_DUKUNG=? WHERE id=? AND NIP=?");
            }
            $stmt->bind_param('ssssssis',
                $_POST['utama_pimpinan'][$i],
                $_POST['utama_kerja'][$i],
                $_POST['utama_aspek'][$i],
                $_POST['utama_indikator'][$i],
                $_POST['utama_target'][$i],
                $_POST['utama_realisasi'][$i],
                $id,
                $_SESSION['nip']
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    // Update Kinerja Tambahan
    if (isset($_POST['tambahan_id'])) {
        foreach ($_POST['tambahan_id'] as $i => $id) {
            if ($is_skp_akhir) {
                // For SKP Akhir, use skp_akhir_pegawai table
                $stmt = $conn->prepare("UPDATE skp_akhir_pegawai SET RHK_PIMPINAN_INTERV=?, RENCANA_HASIL_KERJA=?, ASPEK=?, INDIKATOR_KINERJA_INDIVIDU=?, TARGET=?, REALISASI_BERDASARKAN_BUKTI_DUKUNG=? WHERE ID_SKP=? AND NIP=?");
            } else {
                // For regular SKP, use skp_pegawai table
                $stmt = $conn->prepare("UPDATE skp_pegawai SET RHK_PIMPINAN_INTERV=?, RENCANA_HASIL_KERJA=?, ASPEK=?, INDIKATOR_KINERJA_INDIVIDU=?, TARGET=?, REALISASI_BERDASARKAN_BUKTI_DUKUNG=? WHERE id=? AND NIP=?");
            }
            $stmt->bind_param('ssssssis',
                $_POST['tambahan_pimpinan'][$i],
                $_POST['tambahan_kerja'][$i],
                $_POST['tambahan_aspek'][$i],
                $_POST['tambahan_indikator'][$i],
                $_POST['tambahan_target'][$i],
                $_POST['tambahan_realisasi'][$i],
                $id,
                $_SESSION['nip']
            );
            $stmt->execute();
            $stmt->close();
        }
    }
    // Redirect to avoid resubmission
    header('Location: skp_detail.php?id_skp_global=' . $id_skp_global);
    exit();
}

// Fetch detailed SKP data
$skp_details = [];
if ($is_skp_akhir) {
    // For SKP Akhir, use skp_akhir_pegawai table
    $sql = "SELECT s.*, p.JABATAN, p.UNIT_KERJA, p.PANGKAT_GOL_RUANG 
            FROM skp_akhir_pegawai s 
            LEFT JOIN Pegawai p ON s.NIP = p.NIP 
            WHERE s.ID_SKP_GLOBAL = ? 
            ORDER BY s.ID_SKP";
} else {
    // For regular SKP, use skp_pegawai table
    $sql = "SELECT s.*, p.JABATAN, p.UNIT_KERJA, p.PANGKAT_GOL_RUANG 
            FROM skp_pegawai s 
            LEFT JOIN Pegawai p ON s.NIP = p.NIP 
            WHERE s.id_skp_global = ? 
            ORDER BY s.id_skp";
}
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_skp_global);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $skp_details[] = $row;
    }
}

// Fetch Perilaku Kerja data
$perilaku_data = [];
if ($is_skp_akhir) {
    // For SKP Akhir, use skp_akhir_perilaku_pegawai table
    $perilaku_sql = "SELECT * FROM skp_akhir_perilaku_pegawai WHERE ID_SKP_GLOBAL = ?";
} else {
    // For regular SKP, use skp_perilaku_pegawai table
    $perilaku_sql = "SELECT * FROM skp_perilaku_pegawai WHERE id_skp_global = ?";
}
$perilaku_stmt = $conn->prepare($perilaku_sql);
$perilaku_stmt->bind_param('i', $id_skp_global);
$perilaku_stmt->execute();
$perilaku_result = $perilaku_stmt->get_result();

if ($perilaku_result && $perilaku_result->num_rows > 0) {
    $perilaku_data = $perilaku_result->fetch_assoc();
}

$stmt->close();
$perilaku_stmt->close();
$conn->close();

if (empty($skp_details)) {
    die('Data SKP tidak ditemukan');
}

$first_row = $skp_details[0];

// Determine status
$status = strtoupper(trim($first_row['STATUS'] ?? ''));
$is_draft = ($status === 'DRAFT' || $status === 'DRAFT DIKEMBALIKAN');
$is_proses = ($status === 'PROSES EVALUASI');
$is_selesai = ($status === 'SELESAI EVALUASI');

// Determine if user is manager/atasan
$is_atasan = (isset($_SESSION['atasan']) && $_SESSION['atasan'] === 'YA');
$is_owner = (isset($_SESSION['nip']) && isset($first_row['NIP']) && $_SESSION['nip'] === $first_row['NIP']);

// Check if manager is in edit feedback mode
$is_edit_feedback = (isset($_GET['edit_feedback']) && $_GET['edit_feedback'] == '1' && $is_atasan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail SKP - SI-APA</title>
    <link rel="icon" type="image/png" href="../images/SIAPA.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0D2052;
            padding: 20px;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: #0D2052;
            color: white;
            padding: 20px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .info-section {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #212529;
            font-size: 14px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #0D2052;
            margin: 30px 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #0D2052;
        }
        
        .skp-table {
            width: 100%;
            min-width: 1200px;
            border-collapse: collapse;
            margin-bottom: 30px;
            border: 1px solid #dee2e6;
        }
        
        .skp-table-container {
            overflow-x: auto;
        }
        
        .skp-table th:last-child,
        .skp-table td:last-child {
            min-width: 100px;
            white-space: nowrap;
        }
        
        .skp-table th {
            background: #0D2052;
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        
        .skp-table td {
            border: 1px solid #dee2e6;
            padding: 10px 8px;
            font-size: 12px;
            vertical-align: top;
        }
        
        .skp-table tr:nth-child(even) {
            background: #f0f0f0;
        }
        
        .perilaku-section {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .perilaku-item {
            display: grid;
            grid-template-columns: 40px 1fr 1fr;
            gap: 15px;
            margin-bottom: 0;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #0D2052;
            min-height: 120px;
        }
        
        .perilaku-number {
            background: #0D2052;
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .perilaku-title {
            font-weight: bold;
            color: #0D2052;
            font-size: 14px;
        }
        
        .perilaku-desc {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            font-size: 12px;
            white-space: pre-line;
        }
        
        .ekspektasi {
            background: #fff3cd;
            padding: 10px;
            border-radius: 4px;
            border-left: 4px solid #ffc107;
            font-size: 12px;
            white-space: pre-line;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-perlu-evaluasi {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-sudah-dievaluasi {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-disetujui {
            background: #d4edda;
            color: #155724;
        }
        
        
        .evaluation-section {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .evaluation-item {
            background: white;
            border: 1px solid #0D2052;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .evaluation-header {
            font-weight: bold;
            color: #0D2052;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .evaluation-textarea {
            width: 100%;
            min-height: 80px;
            border: 1px solid #0D2052;
            border-radius: 6px;
            padding: 10px;
            font-family: 'Bookman Old Style', serif;
            font-size: 14px;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .evaluation-textarea:focus {
            outline: none;
            border-color: #0D2052;
            box-shadow: 0 0 0 2px rgba(0,82,155,0.1);
        }
        
        .evaluation-textarea select {
            width: 100%;
            height: 50px;
            border: 1px solid #0D2052;
            border-radius: 6px;
            padding: 10px;
            font-family: 'Bookman Old Style', serif;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        
        .evaluation-textarea select:focus {
            outline: none;
            border-color: #0D2052;
            box-shadow: 0 0 0 2px rgba(0,82,155,0.1);
        }
        
        .rating-predikat-section {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .rating-predikat-section select {
            font-family: 'Bookman Old Style', serif !important;
            font-size: 14px !important;
        }
        
        .rating-predikat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 768px) {
            .rating-predikat-grid {
                grid-template-columns: 1fr;
            }
        }
        
        
        .skp-item-with-feedback {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            align-items: flex-start;
            padding-left: 60px;
        }
        
        .skp-item-with-feedback .perilaku-item {
            margin-bottom: 0;
        }
        
        .skp-table-container {
            flex: 1;
            min-width: 0;
        }
        
        .feedback-form-sidebar {
            width: 350px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            position: sticky;
            top: 20px;
            margin-left: 20px;
        }
        
        .feedback-form-sidebar h5 {
            color: #0D2052;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .feedback-form-sidebar .evaluation-textarea {
            width: 100%;
            min-height: 100px;
            border: 1px solid #0D2052;
            border-radius: 6px;
            padding: 10px;
            font-family: 'Bookman Old Style', serif;
            font-size: 14px;
            resize: vertical;
            box-sizing: border-box;
        }
        
        .feedback-form-sidebar .evaluation-textarea:focus {
            outline: none;
            border-color: #0D2052;
            box-shadow: 0 0 0 2px rgba(0,82,155,0.1);
        }
        
        
        @media (max-width: 1200px) {
            .skp-item-with-feedback {
                flex-direction: column;
            }
            
            .feedback-form-sidebar {
                width: 100%;
                position: static;
            }
        }
        
        .skp-item-container {
            background: white;
            border: 1px solid #0D2052;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .skp-item-header {
            background: #0D2052;
            color: white;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .skp-item-content {
            padding: 20px;
        }
        
        .skp-field {
            margin-bottom: 15px;
        }
        
        .skp-field label {
            display: block;
            font-weight: bold;
            color: #0D2052;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .skp-field-content {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 6px;
            padding: 12px;
            font-size: 14px;
            line-height: 1.5;
            min-height: 40px;
        }
        
        .evaluation-form-inline {
            background: #fff3cd;
            border-top: 2px solid #ffc107;
            padding: 20px;
        }
        
        .evaluation-form-inline h5 {
            color: #0D2052;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
        }
        
        .perilaku-item-container {
            background: white;
            border: 1px solid #0D2052;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .perilaku-item-header {
            background: #0D2052;
            color: white;
            padding: 15px 20px;
            font-size: 16px;
            font-weight: bold;
        }
        
        .perilaku-item-content {
            padding: 20px;
        }
        
        .perilaku-field {
            margin-bottom: 15px;
        }
        
        .perilaku-field label {
            display: block;
            font-weight: bold;
            color: #0D2052;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .perilaku-field-content {
            background: #f0f0f0;
            border: 1px solid #0D2052;
            border-radius: 6px;
            padding: 12px;
            font-size: 14px;
            line-height: 1.5;
            min-height: 40px;
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .perilaku-item {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .skp-table {
                font-size: 10px;
            }
            
            .skp-table th,
            .skp-table td {
                padding: 6px 4px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $is_skp_akhir ? 'DETAIL SKP AKHIR PEGAWAI' : 'DETAIL SKP PEGAWAI' ?></h1>
            <p>ID SKP Global: <?= htmlspecialchars($id_skp_global) ?> | Tanggal: <?= date('d/m/Y H:i', strtotime($first_row['TANGGAL_INPUT_SKP'])) ?></p>
        </div>
        
        <div class="content">
            <!-- Pegawai Information -->
            <div class="info-section">
                <h3 style="color: #0D2052; margin-bottom: 15px;">INFORMASI PEGAWAI</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Nama Pegawai</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NAMA']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIP</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NIP']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Jabatan</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['JABATAN'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Unit Kerja</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['UNIT_KERJA'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Pangkat/Gol Ruang</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['PANGKAT_GOL_RUANG'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Atasan Langsung</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NAMA_ATASAN_LANGSUNG'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">NIP Atasan</div>
                        <div class="info-value"><?= htmlspecialchars($first_row['NIP_ATASAN_LANGSUNG'] ?? 'N/A') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Periode</div>
                        <div class="info-value">
                            <?php if ($is_skp_akhir): ?>
                                Ringkasan Tahunan <?= htmlspecialchars($first_row['TAHUN']) ?>
                            <?php else: ?>
                                Triwulan <?= htmlspecialchars($first_row['TRIWULAN']) ?> Tahun <?= htmlspecialchars($first_row['TAHUN']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $first_row['STATUS'])) ?>">
                                <?= htmlspecialchars($first_row['STATUS']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SKP Data Display -->
            <?php 
            // Define variables for SKP data display
            $kinerja_utama = array_filter($skp_details, function($row) {
                return isset($row['JENIS_KINERJA']) && $row['JENIS_KINERJA'] === 'kinerja utama';
            });
            $kinerja_tambahan = array_filter($skp_details, function($row) {
                return isset($row['JENIS_KINERJA']) && $row['JENIS_KINERJA'] === 'kinerja tambahan';
            });
            ?>
            
            <?php if ($is_draft): ?>
                <form method="post" id="edit-skp-form">
                    <input type="hidden" name="edit_skp" value="1">
            <?php elseif ($is_edit_feedback): ?>
                <form method="post" id="edit-feedback-form">
                    <input type="hidden" name="evaluate_skp" value="1">
            <?php endif; ?>
                    
                    <div class="section-title">A. KINERJA UTAMA</div>
                    <?php foreach ($kinerja_utama as $index => $row): ?>
                    <div class="skp-item-with-feedback" style="position: relative;">
                        <div class="skp-table-container">
                            <table class="skp-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">NO</th>
                                        <th>RENCANA HASIL KERJA PIMPINAN YANG DIINTERVENSI</th>
                                        <th>RENCANA HASIL KERJA</th>
                                        <th>ASPEK</th>
                                        <th>INDIKATOR KINERJA INDIVIDU</th>
                                        <th>TARGET</th>
                                        <th>REALISASI BERDASARKAN BUKTI DUKUNG</th>
                                        <th>SATUAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align: center; font-weight: bold;"><?= $index + 1 ?></td>
                                        <?php if ($is_draft): ?>
                                            <td><textarea name="utama_pimpinan[<?= $index ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['RHK_PIMPINAN_INTERV']) ?></textarea></td>
                                            <td><textarea name="utama_kerja[<?= $index ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['RENCANA_HASIL_KERJA']) ?></textarea></td>
                                            <td><textarea name="utama_aspek[<?= $index ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['ASPEK']) ?></textarea></td>
                                            <td><textarea name="utama_indikator[<?= $index ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU']) ?></textarea></td>
                                            <td><textarea name="utama_target[<?= $index ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['TARGET']) ?></textarea></td>
                                            <td><textarea name="utama_realisasi[<?= $index ?>]" class="evaluation-textarea" required><?= htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG']) ?></textarea></td>
                                            <td><input type="text" name="utama_satuan[<?= $index ?>]" class="evaluation-textarea" value="<?= htmlspecialchars($row['SATUAN'] ?? '') ?>" required></td>
                                            <input type="hidden" name="utama_id[<?= $index ?>]" value="<?= htmlspecialchars($is_skp_akhir ? $row['ID_SKP'] : $row['id']) ?>">
                                        <?php else: ?>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RHK_PIMPINAN_INTERV'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RENCANA_HASIL_KERJA'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['ASPEK'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['TARGET'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= htmlspecialchars($row['SATUAN'] ?? 'N/A') ?></div></td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Feedback Form Sidebar for Kinerja Utama -->
                        <div class="feedback-form-sidebar">
                            <h5>UMPAN BALIK KINERJA UTAMA</h5>
                            <?php 
                            // Check if activity was not performed (TARGET = 0 and REALISASI = 0)
                            $target_value = trim($row['TARGET']);
                            $realisasi_value = trim($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG']);
                            $is_not_performed = ($target_value === '0' && $realisasi_value === '0');
                            ?>
                            
                            <?php if ($is_not_performed): ?>
                                <!-- Activity not performed - no feedback allowed -->
                                <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 6px; padding: 15px; text-align: center;">
                                    <div style="font-size: 24px; margin-bottom: 10px;">🚫</div>
                                    <div style="font-weight: bold; color: #856404; font-size: 14px; margin-bottom: 5px;">KEGIATAN TIDAK DILAKUKAN</div>
                                    <div style="color: #856404; font-size: 12px;">Umpan balik tidak diperlukan untuk kegiatan yang tidak dilakukan</div>
                                </div>
                            <?php elseif ($is_edit_feedback): ?>
                                <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; min-width: 60px;">
                                        <label style="display: flex; align-items: center; cursor: pointer; font-size: 30px; color: #0D2052;">
                                            <input type="checkbox" name="skp_stickers[<?= $index ?>]" value="1" <?= (!empty($row['UMPAN_BALIK_STICKER']) && $row['UMPAN_BALIK_STICKER'] == 'C') ? 'checked' : '' ?> style="margin-right: 5px; transform: scale(1.1);">
                                            👍 
                                        </label>
                                    </div>
                                    <div style="flex: 1;">
                                        <textarea name="skp_feedback[<?= $index ?>]" class="evaluation-textarea" placeholder="Masukkan umpan balik untuk kinerja utama..." id="skp_feedback_<?= $index ?>" style="width: 100%;"><?= htmlspecialchars($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; min-width: 60px;">
                                        <?php if (!empty($row['UMPAN_BALIK_STICKER']) && $row['UMPAN_BALIK_STICKER'] == 'C'): ?>
                                            <span style="font-family: 'Wingdings', 'Wingdings 2', 'Wingdings 3', 'Arial', sans-serif; font-size: 60px; color: #28a745;">C</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="evaluation-textarea" style="background: #f0f0f0; border: 1px solid #0D2052; border-radius: 6px; padding: 10px; font-size: 14px; min-height: 100px; white-space: pre-line;" readonly>
                                            <?= htmlspecialchars($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?? 'Belum ada umpan balik') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                    <?php endforeach; ?>

                    <div class="section-title">B. KINERJA TAMBAHAN</div>
                    <?php foreach ($kinerja_tambahan as $index => $row): ?>
                    <div class="skp-item-with-feedback" style="position: relative;">
                        <div class="skp-table-container">
                            <table class="skp-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">NO</th>
                                        <th>RENCANA HASIL KERJA PIMPINAN YANG DIINTERVENSI</th>
                                        <th>RENCANA HASIL KERJA</th>
                                        <th>ASPEK</th>
                                        <th>INDIKATOR KINERJA INDIVIDU</th>
                                        <th>TARGET</th>
                                        <th>REALISASI BERDASARKAN BUKTI DUKUNG</th>
                                        <th>SATUAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align: center; font-weight: bold;"><?= $index + 1 ?></td>
                                        <?php if ($is_draft): ?>
                                            <td><textarea name="tambahan_pimpinan[<?= $index ?>]" class="evaluation-textarea"><?= htmlspecialchars($row['RHK_PIMPINAN_INTERV']) ?></textarea></td>
                                            <td><textarea name="tambahan_kerja[<?= $index ?>]" class="evaluation-textarea"><?= htmlspecialchars($row['RENCANA_HASIL_KERJA']) ?></textarea></td>
                                            <td><textarea name="tambahan_aspek[<?= $index ?>]" class="evaluation-textarea"><?= htmlspecialchars($row['ASPEK']) ?></textarea></td>
                                            <td><textarea name="tambahan_indikator[<?= $index ?>]" class="evaluation-textarea"><?= htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU']) ?></textarea></td>
                                            <td><textarea name="tambahan_target[<?= $index ?>]" class="evaluation-textarea"><?= htmlspecialchars($row['TARGET']) ?></textarea></td>
                                            <td><textarea name="tambahan_realisasi[<?= $index ?>]" class="evaluation-textarea"><?= htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG']) ?></textarea></td>
                                            <td><input type="text" name="tambahan_satuan[<?= $index ?>]" class="evaluation-textarea" value="<?= htmlspecialchars($row['SATUAN'] ?? '') ?>"></td>
                                            <input type="hidden" name="tambahan_id[<?= $index ?>]" value="<?= htmlspecialchars($is_skp_akhir ? $row['ID_SKP'] : $row['id']) ?>">
                                        <?php else: ?>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RHK_PIMPINAN_INTERV'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['RENCANA_HASIL_KERJA'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['ASPEK'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['INDIKATOR_KINERJA_INDIVIDU'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['TARGET'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= nl2br(htmlspecialchars($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG'])) ?></div></td>
                                            <td><div class="skp-field-content"><?= htmlspecialchars($row['SATUAN'] ?? 'N/A') ?></div></td>
                                        <?php endif; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Feedback Form Sidebar for Kinerja Tambahan -->
                        <div class="feedback-form-sidebar">
                            <h5>UMPAN BALIK KINERJA TAMBAHAN</h5>
                            <?php 
                            // Check if activity was not performed (TARGET = 0 and REALISASI = 0)
                            $target_value = trim($row['TARGET']);
                            $realisasi_value = trim($row['REALISASI_BERDASARKAN_BUKTI_DUKUNG']);
                            $is_not_performed = ($target_value === '0' && $realisasi_value === '0');
                            ?>
                            
                            <?php if ($is_not_performed): ?>
                                <!-- Activity not performed - no feedback allowed -->
                                <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 6px; padding: 15px; text-align: center;">
                                    <div style="font-size: 24px; margin-bottom: 10px;">🚫</div>
                                    <div style="font-weight: bold; color: #856404; font-size: 14px; margin-bottom: 5px;">KEGIATAN TIDAK DILAKUKAN</div>
                                    <div style="color: #856404; font-size: 12px;">Umpan balik tidak diperlukan untuk kegiatan yang tidak dilakukan</div>
                                </div>
                            <?php elseif ($is_edit_feedback): ?>
                                <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; min-width: 60px;">
                                        <label style="display: flex; align-items: center; cursor: pointer; font-size: 60px; color: #0D2052;">
                                            <input type="checkbox" name="skp_stickers[<?= $index ?>]" value="1" <?= (!empty($row['UMPAN_BALIK_STICKER']) && $row['UMPAN_BALIK_STICKER'] == 'C') ? 'checked' : '' ?> style="margin-right: 5px; transform: scale(1.1);">
                                            👍 
                                        </label>
                                    </div>
                                    <div style="flex: 1;">
                                        <textarea name="skp_feedback[<?= $index ?>]" class="evaluation-textarea" placeholder="Masukkan umpan balik untuk kinerja tambahan..." id="skp_feedback_tambahan_<?= $index ?>" style="width: 100%;"><?= htmlspecialchars($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; min-width: 60px;">
                                        <?php if (!empty($row['UMPAN_BALIK_STICKER']) && $row['UMPAN_BALIK_STICKER'] == 'C'): ?>
                                            <span style="font-family: 'Wingdings', 'Wingdings 2', 'Wingdings 3', 'Arial', sans-serif; font-size: 60px; color: #28a745;">C</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="evaluation-textarea" style="background: #f0f0f0; border: 1px solid #0D2052; border-radius: 6px; padding: 10px; font-size: 14px; min-height: 100px; white-space: pre-line;" readonly>
                                            <?= htmlspecialchars($row['UMPAN_BALIK_DENGAN_BUKTI_DUKUNG'] ?? 'Belum ada umpan balik') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                    </div>
                    <?php endforeach; ?>
            
            <!-- Capaian Kinerja Organisasi Section for Managers -->
            <?php if ($is_edit_feedback): ?>
            <div class="section-title">CAPAIAN KINERJA ORGANISASI</div>
            <div class="rating-predikat-section">
                <div class="skp-item-header" style="margin-bottom: 20px;">
                    EVALUASI CAPAIAN KINERJA ORGANISASI
                </div>
                <div class="rating-predikat-grid" style="grid-template-columns: 1fr; max-width: 400px;">
                    <div class="skp-field">
                        <label for="capaian_kinerja_organisasi">CAPAIAN KINERJA ORGANISASI</label>
                        <select name="capaian_kinerja_organisasi" id="capaian_kinerja_organisasi" class="evaluation-textarea" required>
                            <option value="">-- Pilih Capaian --</option>
                            <option value="ISTIMEWA" <?= (isset($first_row['CAPAIAN_KINERJA_ORGANISASI']) && $first_row['CAPAIAN_KINERJA_ORGANISASI'] == 'ISTIMEWA') ? 'selected' : '' ?>>ISTIMEWA</option>
                            <option value="BAIK" <?= (isset($first_row['CAPAIAN_KINERJA_ORGANISASI']) && $first_row['CAPAIAN_KINERJA_ORGANISASI'] == 'BAIK') ? 'selected' : '' ?>>BAIK</option>
                            <option value="BUTUH PERBAIKAN" <?= (isset($first_row['CAPAIAN_KINERJA_ORGANISASI']) && $first_row['CAPAIAN_KINERJA_ORGANISASI'] == 'BUTUH PERBAIKAN') ? 'selected' : '' ?>>BUTUH PERBAIKAN</option>
                            <option value="KURANG" <?= (isset($first_row['CAPAIAN_KINERJA_ORGANISASI']) && $first_row['CAPAIAN_KINERJA_ORGANISASI'] == 'KURANG') ? 'selected' : '' ?>>KURANG</option>
                            <option value="SANGAT KURANG" <?= (isset($first_row['CAPAIAN_KINERJA_ORGANISASI']) && $first_row['CAPAIAN_KINERJA_ORGANISASI'] == 'SANGAT KURANG') ? 'selected' : '' ?>>SANGAT KURANG</option>
                        </select>
                    </div>
                </div>
                <div class="skp-field" style="margin-top: 20px;">
                    <label>Keterangan:</label>
                    <div class="skp-field-content" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; font-size: 13px; line-height: 1.6;">
                        <strong>Capaian Kinerja Organisasi:</strong> Evaluasi terhadap pencapaian kinerja organisasi berdasarkan kontribusi pegawai terhadap pencapaian tujuan organisasi.
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Capaian Kinerja Organisasi Display Section for All Users -->
            <?php if (!empty($first_row['CAPAIAN_KINERJA_ORGANISASI']) && !$is_edit_feedback): ?>
            <div class="section-title">CAPAIAN KINERJA ORGANISASI</div>
            <div class="rating-predikat-section">
                <div class="skp-item-header" style="margin-bottom: 20px;">
                    EVALUASI CAPAIAN KINERJA ORGANISASI
                </div>
                <div class="rating-predikat-grid" style="grid-template-columns: 1fr; max-width: 400px;">
                    <div class="skp-field">
                        <label>CAPAIAN KINERJA ORGANISASI</label>
                        <div class="skp-field-content" style="background: #e8f5e8; border-left: 4px solid #28a745; font-weight: bold; color: #155724;">
                            <?= htmlspecialchars($first_row['CAPAIAN_KINERJA_ORGANISASI'] ?? 'Belum dievaluasi') ?>
                        </div>
                    </div>
                </div>
                <div class="skp-field" style="margin-top: 20px;">
                    <label>Keterangan:</label>
                    <div class="skp-field-content" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; font-size: 13px; line-height: 1.6;">
                        <strong>Capaian Kinerja Organisasi:</strong> Evaluasi terhadap pencapaian kinerja organisasi berdasarkan kontribusi pegawai terhadap pencapaian tujuan organisasi.
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Rating Hasil Kerja Section for Managers -->
            <?php if ($is_edit_feedback): ?>
            <div class="section-title">RATING HASIL KERJA</div>
            <div class="rating-predikat-section">
                <div class="skp-item-header" style="margin-bottom: 20px;">
                    EVALUASI HASIL KERJA PEGAWAI
                </div>
                <div class="rating-predikat-grid" style="grid-template-columns: 1fr; max-width: 400px;">
                    <div class="skp-field">
                        <label for="rating_hasil_kerja">RATING HASIL KERJA</label>
                        <select name="rating_hasil_kerja" id="rating_hasil_kerja" class="evaluation-textarea" required>
                            <option value="">-- Pilih Rating --</option>
                            <option value="DIBAWAH EKSPEKTASI" <?= (isset($first_row['RATING_HASIL_KERJA']) && $first_row['RATING_HASIL_KERJA'] == 'DIBAWAH EKSPEKTASI') ? 'selected' : '' ?>>DIBAWAH EKSPEKTASI</option>
                            <option value="SESUAI EKSPEKTASI" <?= (isset($first_row['RATING_HASIL_KERJA']) && $first_row['RATING_HASIL_KERJA'] == 'SESUAI EKSPEKTASI') ? 'selected' : '' ?>>SESUAI EKSPEKTASI</option>
                            <option value="DIATAS EKSPEKTASI" <?= (isset($first_row['RATING_HASIL_KERJA']) && $first_row['RATING_HASIL_KERJA'] == 'DIATAS EKSPEKTASI') ? 'selected' : '' ?>>DIATAS EKSPEKTASI</option>
                        </select>
                    </div>
                </div>
                <div class="skp-field" style="margin-top: 20px;">
                    <label>Keterangan:</label>
                    <div class="skp-field-content" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; font-size: 13px; line-height: 1.6;">
                        <strong>Rating Hasil Kerja:</strong> Evaluasi terhadap pencapaian hasil kerja pegawai berdasarkan kinerja utama dan kinerja tambahan.
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Rating Hasil Kerja Display Section for All Users -->
            <?php if (!empty($first_row['RATING_HASIL_KERJA']) && !$is_edit_feedback): ?>
            <div class="section-title">RATING HASIL KERJA</div>
            <div class="rating-predikat-section">
                <div class="skp-item-header" style="margin-bottom: 20px;">
                    EVALUASI HASIL KERJA PEGAWAI
                </div>
                <div class="rating-predikat-grid" style="grid-template-columns: 1fr; max-width: 400px;">
                    <div class="skp-field">
                        <label>RATING HASIL KERJA</label>
                        <div class="skp-field-content" style="background: #e8f5e8; border-left: 4px solid #28a745; font-weight: bold; color: #155724;">
                            <?= htmlspecialchars($first_row['RATING_HASIL_KERJA'] ?? 'Belum dievaluasi') ?>
                        </div>
                    </div>
                </div>
                <div class="skp-field" style="margin-top: 20px;">
                    <label>Keterangan:</label>
                    <div class="skp-field-content" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; font-size: 13px; line-height: 1.6;">
                        <strong>Rating Hasil Kerja:</strong> Evaluasi terhadap pencapaian hasil kerja pegawai berdasarkan kinerja utama dan kinerja tambahan.
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Perilaku Kerja -->
            <?php if (!empty($perilaku_data)): ?>
            <div class="section-title">C. PERILAKU KERJA</div>
            <?php
            $perilaku_items = [
                'berorientasi_pelayanan' => 'BERORIENTASI PELAYANAN',
                'akuntabel' => 'AKUNTABEL', 
                'kompeten' => 'KOMPETEN',
                'harmonis' => 'HARMONIS',
                'loyal' => 'LOYAL',
                'adaptif' => 'ADAPTIF',
                'kolaboratif' => 'KOLABORATIF'
            ];
            
            $descriptions = [
                'berorientasi_pelayanan' => "- Memahami dan memenuhi kebutuhan masyarakat.\n- Ramah, cekatan, solutif, dan dapat diandalkan.\n- Melakukan perbaikan tiada henti.",
                'akuntabel' => "- Melaksanakan tugas dengan jujur, bertanggungjawab, cermat, disiplin dan berintegritas tinggi.\n- Menggunakan kekayaan dan barang milik negara secara bertanggungjawab, efektif dan efisien.\n- Tidak menyalahgunakan kewenangan jabatan.",
                'kompeten' => "- Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah.\n- Membantu orang lain belajar.\n- Melaksanakan tugas dengan kualitas terbaik.",
                'harmonis' => "- Menghargai setiap orang apapun latar belakangnya.\n- Suka menolong orang lain.\n- Membangun lingkungan kerja yang kondusif.",
                'loyal' => "- Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, setia pada Negara Kesatuan Republik Indonesia serta pemerintahan yang sah.\n- Menjaga nama baik ASN, Pimpinan, Instansi dan Negara.\n- Menjaga rahasia jabatan dan negara.",
                'adaptif' => "- Cepat menyesuaikan diri menghadapi perubahan\n- Terus berinovasi dan mengembangkan kreativitas\n- Bertindak proaktif",
                'kolaboratif' => "- Memberi kesempatan kepada berbagai pihak untuk berkontribusi.\n- Terbuka dalam bekerjasama untuk menghasilkan nilai tambah.\n- Menggerakan pemanfaatan berbagai sumber daya untuk tujuan bersama."
            ];
            
            $index = 1;
            foreach ($perilaku_items as $key => $title):
                $ekspektasi_key = 'EKSPEKTASI_PIMPINAN_' . strtoupper($key);
                $ekspektasi = $perilaku_data[$ekspektasi_key] ?? '';
                $feedback_key = 'UMPAN_BALIK_' . strtoupper($key);
                $current_feedback = $perilaku_data[$feedback_key] ?? '';
            ?>
                <div class="skp-item-with-feedback">
                    <div class="skp-table-container">
                        <div class="perilaku-item">
                            <div class="perilaku-number"><?= $index ?></div>
                            <div>
                                <div class="perilaku-title"><?= $title ?></div>
                                <div class="perilaku-desc"><?= nl2br(htmlspecialchars($descriptions[$key])) ?></div>
                            </div>
                            <div>
                                <strong>Ekspektasi Khusus Pimpinan:</strong>
                                <div class="ekspektasi"><?= nl2br(htmlspecialchars($ekspektasi)) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Feedback Form Sidebar -->
                    <div class="feedback-form-sidebar">
                        <h5>UMPAN BALIK PERILAKU KERJA</h5>
                        <?php if ($is_edit_feedback): ?>
                            <textarea name="perilaku_feedback[<?php echo $key; ?>]" class="evaluation-textarea" placeholder="Masukkan umpan balik untuk perilaku kerja <?php echo $title; ?>..." id="perilaku_feedback_<?php echo $key; ?>"><?php echo htmlspecialchars($current_feedback); ?></textarea>
                        <?php else: ?>
                            <div class="evaluation-textarea" style="background: #f0f0f0; border: 1px solid #0D2052; border-radius: 6px; padding: 10px; font-size: 14px; min-height: 100px; white-space: pre-line;" readonly><?php echo htmlspecialchars($current_feedback ?: 'Belum ada umpan balik'); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php 
            $index++;
            endforeach; 
            ?>
            <?php endif; ?>
            
            <!-- Rating and Predikat Display Section for All Users (not in edit mode) -->
            <?php if ((!empty($first_row['RATING_PERILAKU_KERJA']) || !empty($first_row['PREDIKAT_KINERJA_PEGAWAI'])) && !$is_edit_feedback): ?>
            <div class="section-title">RATING DAN PREDIKAT KINERJA</div>
            <div class="rating-predikat-section">
                <div class="skp-item-header" style="margin-bottom: 20px;">
                    EVALUASI AKHIR KINERJA PEGAWAI
                </div>
                <div class="rating-predikat-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="skp-field">
                        <label>RATING PERILAKU KERJA</label>
                        <div class="skp-field-content" style="background: #e8f5e8; border-left: 4px solid #28a745; font-weight: bold; color: #155724;">
                            <?= htmlspecialchars($first_row['RATING_PERILAKU_KERJA'] ?? 'Belum dievaluasi') ?>
                        </div>
                    </div>
                    <div class="skp-field">
                        <label>PREDIKAT KINERJA PEGAWAI</label>
                        <div class="skp-field-content" style="background: #e8f5e8; border-left: 4px solid #28a745; font-weight: bold; color: #155724;">
                            <?= htmlspecialchars($first_row['PREDIKAT_KINERJA_PEGAWAI'] ?? 'Belum dievaluasi') ?>
                        </div>
                    </div>
                </div>
                <div class="skp-field" style="margin-top: 20px;">
                    <label>Keterangan:</label>
                    <div class="skp-field-content" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; font-size: 13px; line-height: 1.6;">
                        <strong>Rating Perilaku Kerja:</strong> Evaluasi terhadap perilaku kerja pegawai berdasarkan 7 aspek perilaku kerja yang telah dinilai.<br>
                        <strong>Predikat Kinerja Pegawai:</strong> Evaluasi keseluruhan kinerja pegawai yang mencakup kinerja utama, kinerja tambahan, dan perilaku kerja.
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Rating and Predikat Section for Managers -->
            <?php if ($is_edit_feedback): ?>
            <div class="section-title">RATING DAN PREDIKAT KINERJA</div>
            <div class="rating-predikat-section">
                <div class="skp-item-header" style="margin-bottom: 20px;">
                    EVALUASI AKHIR KINERJA PEGAWAI
                </div>
                <div class="rating-predikat-grid" style="grid-template-columns: 1fr 1fr;">
                    <div class="skp-field">
                        <label for="rating_perilaku_kerja">RATING PERILAKU KERJA</label>
                        <select name="rating_perilaku_kerja" id="rating_perilaku_kerja" class="evaluation-textarea" required>
                            <option value="">-- Pilih Rating --</option>
                            <option value="DIBAWAH EKSPEKTASI" <?= (isset($first_row['RATING_PERILAKU_KERJA']) && $first_row['RATING_PERILAKU_KERJA'] == 'DIBAWAH EKSPEKTASI') ? 'selected' : '' ?>>DIBAWAH EKSPEKTASI</option>
                            <option value="SESUAI EKSPEKTASI" <?= (isset($first_row['RATING_PERILAKU_KERJA']) && $first_row['RATING_PERILAKU_KERJA'] == 'SESUAI EKSPEKTASI') ? 'selected' : '' ?>>SESUAI EKSPEKTASI</option>
                            <option value="DIATAS EKSPEKTASI" <?= (isset($first_row['RATING_PERILAKU_KERJA']) && $first_row['RATING_PERILAKU_KERJA'] == 'DIATAS EKSPEKTASI') ? 'selected' : '' ?>>DIATAS EKSPEKTASI</option>
                        </select>
                    </div>
                    <div class="skp-field">
                        <label for="predikat_kinerja_pegawai">PREDIKAT KINERJA PEGAWAI</label>
                        <select name="predikat_kinerja_pegawai" id="predikat_kinerja_pegawai" class="evaluation-textarea" required>
                            <option value="">-- Pilih Predikat --</option>
                            <option value="SANGAT BAIK" <?= (isset($first_row['PREDIKAT_KINERJA_PEGAWAI']) && $first_row['PREDIKAT_KINERJA_PEGAWAI'] == 'SANGAT BAIK') ? 'selected' : '' ?>>SANGAT BAIK</option>
                            <option value="BAIK" <?= (isset($first_row['PREDIKAT_KINERJA_PEGAWAI']) && $first_row['PREDIKAT_KINERJA_PEGAWAI'] == 'BAIK') ? 'selected' : '' ?>>BAIK</option>
                            <option value="BUTUH PERBAIKAN" <?= (isset($first_row['PREDIKAT_KINERJA_PEGAWAI']) && $first_row['PREDIKAT_KINERJA_PEGAWAI'] == 'BUTUH PERBAIKAN') ? 'selected' : '' ?>>BUTUH PERBAIKAN</option>
                            <option value="KURANG" <?= (isset($first_row['PREDIKAT_KINERJA_PEGAWAI']) && $first_row['PREDIKAT_KINERJA_PEGAWAI'] == 'KURANG') ? 'selected' : '' ?>>KURANG</option>
                            <option value="SANGAT KURANG" <?= (isset($first_row['PREDIKAT_KINERJA_PEGAWAI']) && $first_row['PREDIKAT_KINERJA_PEGAWAI'] == 'SANGAT KURANG') ? 'selected' : '' ?>>SANGAT KURANG</option>
                        </select>
                    </div>
                </div>
                <div class="skp-field" style="margin-top: 20px;">
                    <label>Keterangan:</label>
                    <div class="skp-field-content" style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; font-size: 13px; line-height: 1.6;">
                        <strong>Rating Perilaku Kerja:</strong> Evaluasi terhadap perilaku kerja pegawai berdasarkan 7 aspek perilaku kerja yang telah dinilai.<br>
                        <strong>Predikat Kinerja Pegawai:</strong> Evaluasi keseluruhan kinerja pegawai yang mencakup kinerja utama, kinerja tambahan, dan perilaku kerja.
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Save Button for Edit Feedback Mode -->
            <?php if ($is_edit_feedback): ?>
                <div style="text-align: center; margin: 30px 0; padding: 20px; background: #f0f0f0; border-radius: 8px; border: 1px solid #0D2052;">
                    <?php if ($is_skp_akhir): ?>
                        <button type="submit" style="background: #00529B; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; margin-right: 10px;">💾 SIMPAN UMPAN BALIK</button>
                        <button type="button" onclick="submitSKPAkhirEvaluasi(<?= htmlspecialchars($id_skp_global) ?>)" style="background: #28a745; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; margin-right: 10px;">✅ SUBMIT EVALUASI</button>
                        <button type="button" onclick="window.close()" style="background: #6c757d; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer;">❌ BATAL</button>
                    <?php else: ?>
                        <button type="submit" style="background: #00529B; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s;">💾 SIMPAN UMPAN BALIK</button>
                        <button type="button" onclick="window.location.href='skploginpage.php'" style="background: #6c757d; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-left: 10px;">❌ BATAL</button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($is_draft || $is_edit_feedback): ?>
                </form>
            <?php endif; ?>
            
            <!-- Success/Error Messages -->
            <?php if (!empty($evaluation_success)): ?>
                <div style="background:#d4edda;color:#155724;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #28a745;">
                    <strong>✅ Berhasil:</strong> <?= htmlspecialchars($evaluation_success) ?>
                </div>
            <?php elseif (!empty($evaluation_error)): ?>
                <div style="background:#fdecea;color:#b00020;padding:12px 20px;border-radius:8px;margin-bottom:20px;border-left:4px solid #dc3545;">
                    <strong>❌ Error:</strong> <?= htmlspecialchars($evaluation_error) ?>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</body>
<script>
// Simple feedback form handling - no symbol mode functionality

function submitSKPAkhirEvaluasi(idSkpGlobal) {
    // First validate all required fields before showing confirmation
    const form = document.getElementById('edit-feedback-form');
    if (!form) {
        alert('❌ Form tidak ditemukan!');
        return;
    }
    
    // Check if all required feedback fields are filled
    const requiredTextareas = form.querySelectorAll('textarea[required]');
    const requiredSelects = form.querySelectorAll('select[required]');
    let allFilled = true;
    let emptyFields = [];
    let emptyFieldDetails = [];
    
    // Validate SKP feedback fields (either checkbox OR textarea OR both required)
    const skpFeedbackTextareas = form.querySelectorAll('textarea[name^="skp_feedback"]');
    skpFeedbackTextareas.forEach(function(textarea) {
        const fieldName = textarea.getAttribute('name');
        const indexMatch = fieldName.match(/\[(\d+)\]/);
        
        if (indexMatch) {
            const index = indexMatch[1];
            const checkbox = form.querySelector('input[name="skp_stickers[' + index + ']"]');
            const hasText = textarea.value.trim() !== '';
            const hasSticker = checkbox && checkbox.checked;
            
            // Check if this activity was not performed (should be skipped)
            // We check if the parent container has a "not performed" message
            const parentContainer = textarea.closest('.feedback-form-sidebar');
            const notPerformedDiv = parentContainer ? parentContainer.querySelector('div[style*="KEGIATAN TIDAK DILAKUKAN"]') : null;
            
            // If activity was performed, require either sticker OR text
            if (!notPerformedDiv && !hasText && !hasSticker) {
                allFilled = false;
                const placeholder = textarea.getAttribute('placeholder') || '';
                emptyFields.push(`Umpan Balik Kinerja ${parseInt(index) + 1}`);
                emptyFieldDetails.push(`• Kinerja ${parseInt(index) + 1}: Harus mengisi text atau memilih sticker 👍 (atau keduanya)`);
            }
        }
    });
    
    // Validate all required textareas (for perilaku feedback)
    requiredTextareas.forEach(function(textarea, index) {
        if (!textarea.value.trim()) {
            allFilled = false;
            const fieldName = textarea.getAttribute('name');
            const placeholder = textarea.getAttribute('placeholder') || '';
            
            if (fieldName.includes('perilaku_feedback')) {
                const key = fieldName.match(/\[([^\]]+)\]/);
                if (key) {
                    const perilakuNames = {
                        'berorientasi_pelayanan': 'Berorientasi Pelayanan',
                        'akuntabel': 'Akuntabel',
                        'kompeten': 'Kompeten',
                        'harmonis': 'Harmonis',
                        'loyal': 'Loyal',
                        'adaptif': 'Adaptif',
                        'kolaboratif': 'Kolaboratif'
                    };
                    const displayName = perilakuNames[key[1]] || key[1];
                    emptyFields.push(`Umpan Balik Perilaku Kerja - ${displayName}`);
                    emptyFieldDetails.push(`• ${displayName}: ${placeholder}`);
                }
            }
        }
    });
    
    // Validate all required selects
    requiredSelects.forEach(function(select) {
        if (!select.value.trim()) {
            allFilled = false;
            const fieldName = select.getAttribute('name');
            const label = select.previousElementSibling ? select.previousElementSibling.textContent : fieldName;
            emptyFields.push(label);
            emptyFieldDetails.push(`• ${label}: Harus dipilih`);
        }
    });
    
    if (!allFilled) {
        const errorMessage = `⚠️ SEBELUM MENYELESAIKAN EVALUASI SKP AKHIR, MOHON LENGKAPI SEMUA FIELD YANG WAJIB DIISI:\n\n` + 
                           emptyFieldDetails.join('\n') + 
                           '\n\n💡 Catatan: Untuk umpan balik kinerja, Anda harus mengisi text ATAU memilih sticker 👍 (atau keduanya). Untuk perilaku kerja, text wajib diisi. Dropdown wajib diisi.';
        alert(errorMessage);
        return;
    }
    
    if (confirm('Apakah Anda yakin ingin submit evaluasi SKP Akhir untuk ID: ' + idSkpGlobal + '?')) {
        // Show loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '⏳ Processing...';
        button.disabled = true;
        
        // Submit evaluation via AJAX
        fetch('submit_skp_akhir_evaluasi.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id_skp_global=' + encodeURIComponent(idSkpGlobal)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Evaluasi SKP Akhir berhasil disubmit!\nStatus SKP telah diubah menjadi "SELESAI EVALUASI".');
                // Refresh the parent page and close the window
                if (window.opener) {
                    window.opener.location.reload();
                }
                window.close();
            } else {
                alert('❌ Gagal submit evaluasi: ' + data.message);
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Terjadi kesalahan saat submit evaluasi. Silakan coba lagi.');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
}

// Enhanced feedback form validation for both SKP Akhir and regular SKP
document.addEventListener('DOMContentLoaded', function() {
    // Check for both forms
    const editFeedbackForm = document.getElementById('edit-feedback-form');
    const editSkpForm = document.getElementById('edit-skp-form');
    
    // Apply validation to edit-feedback-form (for both SKP Akhir and regular SKP feedback)
    if (editFeedbackForm) {
        applyValidation(editFeedbackForm);
    }
    
    // Apply validation to edit-skp-form (for draft editing)
    if (editSkpForm) {
        applyValidation(editSkpForm);
    }
    
    function applyValidation(form) {
        form.addEventListener('submit', function(e) {
            // Check if all required feedback fields are filled
            const requiredTextareas = form.querySelectorAll('textarea[required]');
            let allFilled = true;
            let emptyFields = [];
            let emptyFieldDetails = [];
            
            // Validate SKP feedback fields (either checkbox OR textarea OR both required)
            const skpFeedbackTextareas = form.querySelectorAll('textarea[name^="skp_feedback"]');
            skpFeedbackTextareas.forEach(function(textarea) {
                const fieldName = textarea.getAttribute('name');
                const indexMatch = fieldName.match(/\[(\d+)\]/);
                
                if (indexMatch) {
                    const index = indexMatch[1];
                    const checkbox = form.querySelector('input[name="skp_stickers[' + index + ']"]');
                    const hasText = textarea.value.trim() !== '';
                    const hasSticker = checkbox && checkbox.checked;
                    
                    // Check if this activity was not performed (should be skipped)
                    // We check if the parent container has a "not performed" message
                    const parentContainer = textarea.closest('.feedback-form-sidebar');
                    const notPerformedDiv = parentContainer ? parentContainer.querySelector('div[style*="KEGIATAN TIDAK DILAKUKAN"]') : null;
                    
                    // If activity was performed, require either sticker OR text
                    if (!notPerformedDiv && !hasText && !hasSticker) {
                        allFilled = false;
                        const placeholder = textarea.getAttribute('placeholder') || '';
                        emptyFields.push(`Umpan Balik Kinerja ${parseInt(index) + 1}`);
                        emptyFieldDetails.push(`• Kinerja ${parseInt(index) + 1}: Harus mengisi text atau memilih sticker 👍 (atau keduanya)`);
                    }
                }
            });
            
            // Validate all required textareas (for perilaku feedback)
            requiredTextareas.forEach(function(textarea) {
                if (!textarea.value.trim()) {
                    allFilled = false;
                    const fieldName = textarea.getAttribute('name');
                    const placeholder = textarea.getAttribute('placeholder') || '';
                    
                    if (fieldName.includes('perilaku_feedback')) {
                        const key = fieldName.match(/\[([^\]]+)\]/);
                        if (key) {
                            const perilakuNames = {
                                'berorientasi_pelayanan': 'Berorientasi Pelayanan',
                                'akuntabel': 'Akuntabel',
                                'kompeten': 'Kompeten',
                                'harmonis': 'Harmonis',
                                'loyal': 'Loyal',
                                'adaptif': 'Adaptif',
                                'kolaboratif': 'Kolaboratif'
                            };
                            const displayName = perilakuNames[key[1]] || key[1];
                            emptyFields.push(`Umpan Balik Perilaku Kerja - ${displayName}`);
                            emptyFieldDetails.push(`• ${displayName}: ${placeholder}`);
                        }
                    }
                }
            });
            
            if (!allFilled) {
                e.preventDefault();
                
                // Get the submit button to determine the context
                const submitBtn = form.querySelector('button[type="submit"]');
                const isSkpAkhir = submitBtn && submitBtn.innerHTML.includes('SUBMIT EVALUASI SKP AKHIR');
                const isSimpanUmpanBalik = submitBtn && submitBtn.innerHTML.includes('SIMPAN UMPAN BALIK');
                
                let contextMessage = '';
                if (isSkpAkhir) {
                    contextMessage = 'Sebelum menyelesaikan evaluasi SKP Akhir,';
                } else if (isSimpanUmpanBalik) {
                    contextMessage = 'Sebelum menyimpan umpan balik,';
                } else {
                    contextMessage = 'Sebelum menyimpan,';
                }
                
                const errorMessage = `⚠️ ${contextMessage.toUpperCase()} MOHON LENGKAPI SEMUA UMPAN BALIK YANG WAJIB DIISI:\n\n` + 
                                   emptyFieldDetails.join('\n') + 
                                   '\n\n💡 Catatan: Untuk umpan balik kinerja, Anda harus mengisi text ATAU memilih sticker 👍 (atau keduanya). Untuk perilaku kerja, text wajib diisi.';
                alert(errorMessage);
                return false;
            }
            
            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                if (submitBtn.innerHTML.includes('SUBMIT EVALUASI SKP AKHIR')) {
                    submitBtn.innerHTML = '⏳ Menyimpan Evaluasi...';
                } else if (submitBtn.innerHTML.includes('SIMPAN UMPAN BALIK')) {
                    submitBtn.innerHTML = '⏳ Menyimpan Umpan Balik...';
                } else {
                    submitBtn.innerHTML = '⏳ Menyimpan...';
                }
                submitBtn.disabled = true;
            }
        });
        
        // Add visual feedback for SKP feedback fields (checkbox OR textarea OR both)
        const skpFeedbackTextareas = form.querySelectorAll('textarea[name^="skp_feedback"]');
        skpFeedbackTextareas.forEach(function(textarea) {
            const fieldName = textarea.getAttribute('name');
            const indexMatch = fieldName.match(/\[(\d+)\]/);
            
            if (indexMatch) {
                const index = indexMatch[1];
                const checkbox = form.querySelector('input[name="skp_stickers[' + index + ']"]');
                
                function updateBorder() {
                    const hasText = textarea.value.trim() !== '';
                    const hasSticker = checkbox && checkbox.checked;
                    
                    // Check if activity was not performed (should be skipped)
                    const parentContainer = textarea.closest('.feedback-form-sidebar');
                    const notPerformedDiv = parentContainer ? parentContainer.querySelector('div[style*="KEGIATAN TIDAK DILAKUKAN"]') : null;
                    
                    // If activity was performed, require either sticker OR text
                    if (notPerformedDiv) {
                        // Activity not performed, no validation needed
                        textarea.style.borderColor = '#0D2052';
                        textarea.style.borderWidth = '1px';
                    } else if (hasText || hasSticker) {
                        // Either text or sticker (or both) is filled
                        textarea.style.borderColor = '#28a745';
                        textarea.style.borderWidth = '2px';
                    } else {
                        // Neither text nor sticker is filled
                        textarea.style.borderColor = '#dc3545';
                        textarea.style.borderWidth = '2px';
                    }
                }
                
                // Update border when textarea changes
                textarea.addEventListener('input', updateBorder);
                
                // Update border when checkbox changes
                if (checkbox) {
                    checkbox.addEventListener('change', updateBorder);
                }
                
                // Initial state
                updateBorder();
            }
        });
        
        // Add visual feedback for required fields (perilaku feedback)
        const requiredTextareas = form.querySelectorAll('textarea[required]');
        requiredTextareas.forEach(function(textarea) {
            textarea.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#28a745';
                    this.style.borderWidth = '2px';
                } else {
                    this.style.borderColor = '#dc3545';
                    this.style.borderWidth = '2px';
                }
            });
            
            // Initial state
            if (textarea.value.trim()) {
                textarea.style.borderColor = '#28a745';
                textarea.style.borderWidth = '2px';
            } else {
                textarea.style.borderColor = '#dc3545';
                textarea.style.borderWidth = '2px';
            }
        });
    }
});
</script>
</html>
