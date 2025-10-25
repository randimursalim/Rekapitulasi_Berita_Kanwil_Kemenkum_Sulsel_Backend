<?php
// app/views/pages/pengguna.php
?>
<div class="overview">
    <div class="title">
        <i class="fas fa-users"></i>
        <span class="text">Manajemen Pengguna</span>
    </div>

    <!-- Tombol Tambah -->
    <div class="btn-container" style="margin: 15px 0;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-pengguna'">
            <i class="fas fa-plus"></i> Tambah Pengguna
        </button>
    </div>

    <!-- Data Pengguna -->
    <div class="activity-wrapper" style="margin-top:20px;">
        <div class="activity">
            <div class="activity-data" id="penggunaResults">
                <!-- Data akan dimuat via AJAX -->
                <div style="text-align: center; padding: 20px;">
                    <p>Memuat data...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" id="pagination">
        <!-- Pagination akan di-generate via JavaScript -->
    </div>
</div>

<script src="/rekap-konten/public/js/pengguna.js"></script>
