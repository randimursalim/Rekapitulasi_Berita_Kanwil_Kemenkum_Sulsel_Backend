<?php
// app/views/pages/statistik-pengguna.php
?>

<link rel="stylesheet" href="<?= $BASE ?>/css/statistik-pengguna.css?v=<?= time() ?>">

<div class="main-content statistik-wrapper">
    <!-- Header Area -->
    <div class="stat-header">
        <div class="title-container">
            <div class="title" style="display: flex; align-items: center; font-size: 1.5rem; font-weight: bold; color: var(--text-color); margin-bottom: 5px;">
                <i class="fas fa-users-cog" style="margin-right: 10px;"></i>
                <span class="text">Statistik Produktivitas Pengguna</span>
            </div>
            <div class="text-muted" style="font-size: 14px;">Pantau aktivitas, kontribusi, dan performa seluruh pengguna secara realtime.</div>
        </div>
        
        <div class="stat-actions">
            <!-- Date Filter -->
            <div class="filter-group" style="width: 250px;">
                <div class="filter-icon"><i class="fas fa-calendar-alt"></i></div>
                <input type="text" id="dateRangeFilter" class="custom-input" style="width: 100%;" placeholder="Pilih Rentang Tanggal">
            </div>
            
            <!-- Role Filter -->
            <select id="roleFilter" class="custom-input" style="width: 150px;">
                <option value="all">Semua Role</option>
                <option value="Admin">Admin</option>
                <option value="Operator">Operator</option>
                <option value="p3h">P3H</option>
            </select>
            
            <!-- Export Buttons -->
            <button id="btnExportExcel" class="custom-btn custom-btn-excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </button>
            <button id="btnExportPDF" class="custom-btn custom-btn-pdf">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button id="btnExportWord" class="custom-btn custom-btn-word">
                <i class="fas fa-file-word"></i> Export Word
            </button>
        </div>
    </div>

    <!-- 4 Top Cards -->
    <div class="stat-grid-4">
        <div class="modern-card">
            <div class="stat-card">
                <div class="stat-icon bg-soft-primary text-primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Konten</div>
                    <div class="stat-value" id="valTotalKonten">0</div>
                </div>
            </div>
        </div>
        <div class="modern-card">
            <div class="stat-card">
                <div class="stat-icon bg-soft-success text-success">
                    <i class="fas fa-history"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Aktivitas</div>
                    <div class="stat-value" id="valTotalAktivitas">0</div>
                </div>
            </div>
        </div>
        <div class="modern-card">
            <div class="stat-card">
                <div class="stat-icon bg-soft-purple text-purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Pengguna Aktif</div>
                    <div class="stat-value" id="valPenggunaAktif">0</div>
                </div>
            </div>
        </div>
        <div class="modern-card">
            <div class="stat-card">
                <div class="stat-icon bg-soft-warning text-warning">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">User Terproduktif</div>
                    <div class="stat-value font-bold" id="valTopUser" style="font-size: 18px; margin-bottom: 2px;">-</div>
                    <div class="stat-label"><span id="valTopUserAktivitas">0</span> aktivitas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="stat-grid-2-asym">
        <!-- Aktivitas Pengguna (Bar Chart) -->
        <div class="modern-card">
            <div class="stat-card-title">
                Aktivitas Pengguna <i class="fas fa-info-circle text-muted" style="font-size: 14px;"></i>
            </div>
            <div class="stat-card-body">
                <div style="height: 300px; width: 100%;">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Aktivitas 7 Hari Terakhir (Line Chart) -->
        <div class="modern-card">
            <div class="stat-card-title">Aktivitas Periode Berjalan</div>
            <div class="stat-card-body">
                <div style="height: 300px; width: 100%;">
                    <canvas id="recentActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Row 2 -->
    <div class="stat-grid-3">
        <!-- Top Kontributor -->
        <div class="modern-card">
            <div class="stat-card-title">Top Kontributor</div>
            <div class="stat-card-body">
                <div id="topContributorsList">
                    <!-- Dimuat via AJAX -->
                    <div style="text-align: center; padding: 20px; color: #888;">Memuat...</div>
                </div>
            </div>
        </div>
        
        <!-- Distribusi Role -->
        <div class="modern-card">
            <div class="stat-card-title">Distribusi Role</div>
            <div class="stat-card-body" style="display: flex; justify-content: center; position: relative;">
                <div style="height: 220px; width: 100%; max-width: 250px;">
                    <canvas id="roleDistributionChart"></canvas>
                </div>
                <div class="chart-center-text">
                    <h4 id="totalUsersDoughnut" style="margin: 0; font-size: 24px;">0</h4>
                    <span class="text-muted" style="font-size: 12px;">Total</span>
                </div>
            </div>
        </div>
        
        <!-- Aktivitas per Jenis -->
        <div class="modern-card" style="grid-column: span 1;">
            <div class="stat-card-title">Aktivitas per Jenis</div>
            <div class="stat-card-body" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                <!-- Kegiatan -->
                <div style="text-align: center; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <i class="fas fa-calendar-check text-info" style="font-size: 20px; margin-bottom: 8px;"></i>
                    <div class="font-bold text-info" id="valJenisKegiatan" style="font-size: 18px;">0</div>
                    <div class="text-muted" style="font-size: 11px;">Kegiatan</div>
                </div>
                <!-- Peminjaman -->
                <div style="text-align: center; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <i class="fas fa-door-open text-warning" style="font-size: 20px; margin-bottom: 8px;"></i>
                    <div class="font-bold text-warning" id="valJenisPeminjaman" style="font-size: 18px;">0</div>
                    <div class="text-muted" style="font-size: 11px;">Peminjaman</div>
                </div>
                <!-- Konten -->
                <div style="text-align: center; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <i class="fas fa-file-alt text-success" style="font-size: 20px; margin-bottom: 8px;"></i>
                    <div class="font-bold text-success" id="valJenisKonten" style="font-size: 18px;">0</div>
                    <div class="text-muted" style="font-size: 11px;">Konten</div>
                </div>
                <!-- Tamu -->
                <div style="text-align: center; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <i class="fas fa-address-book text-primary" style="font-size: 20px; margin-bottom: 8px;"></i>
                    <div class="font-bold text-primary" id="valJenisTamu" style="font-size: 18px;">0</div>
                    <div class="text-muted" style="font-size: 11px;">Tamu</div>
                </div>
                <!-- Pengaduan -->
                <div style="text-align: center; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <i class="fas fa-headset text-danger" style="font-size: 20px; margin-bottom: 8px;"></i>
                    <div class="font-bold text-danger" id="valJenisPengaduan" style="font-size: 18px;">0</div>
                    <div class="text-muted" style="font-size: 11px;">Aduan</div>
                </div>
                <!-- Harmonisasi -->
                <div style="text-align: center; padding: 15px; border: 1px solid var(--border-color); border-radius: 8px;">
                    <i class="fas fa-balance-scale text-purple" style="font-size: 20px; margin-bottom: 8px;"></i>
                    <div class="font-bold text-purple" id="valJenisHarmonisasi" style="font-size: 18px;">0</div>
                    <div class="text-muted" style="font-size: 11px;">Harmonisasi</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Kontribusi -->
    <div class="modern-card mb-5" style="margin-bottom: 50px;">
        <div class="stat-card-title" style="padding: 20px;">Detail Kontribusi Pengguna</div>
        <div class="stat-card-body" style="padding: 0 20px 20px 20px;">
            <div class="table-responsive">
                <table id="statistikTable" class="data-table-custom" style="white-space: nowrap;">
                    <thead>
                        <tr>
                            <th style="min-width: 50px;">No</th>
                            <th style="min-width: 200px;">Pengguna</th>
                            <th style="min-width: 100px;">Role</th>
                            <th style="text-align: center; min-width: 120px;">Log Aktivitas</th>
                            <th style="text-align: center; min-width: 90px;">Konten</th>
                            <th style="text-align: center; min-width: 90px;">Kegiatan</th>
                            <th style="text-align: center; min-width: 110px;">Peminjaman</th>
                            <th style="text-align: center; min-width: 90px;">Tamu</th>
                            <th style="text-align: center; min-width: 90px;">Aduan</th>
                            <th style="text-align: center; min-width: 110px;">Harmonisasi</th>
                            <th style="text-align: center; min-width: 130px;">Total Aktivitas</th>
                            <th style="min-width: 130px;">Produktivitas</th>
                        </tr>
                    </thead>
                    <tbody id="statistikTableBody">
                        <!-- Data dimuat via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Flatpickr CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<!-- Library untuk Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>

<!-- Main Logic -->
<script src="<?= $BASE ?>/js/statistik-pengguna.js?v=<?= time() ?>"></script>
