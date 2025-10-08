<?php
// app/views/pages/pengguna.php
?>
<div class="overview">
    <div class="title">
        <i class="uil uil-users-alt"></i>
        <span class="text">Manajemen Pengguna</span>
    </div>

    <!-- Data Pengguna -->
    <div class="activity" style="margin-top:20px;">
        <div class="activity-data">
            <div class="data no">
                <span class="data-title">No</span>
                <span class="data-list">1</span>
                <span class="data-list">2</span>
                <span class="data-list">3</span>
            </div>
            <div class="data name">
                <span class="data-title">Nama</span>
                <span class="data-list">Admin</span>
                <span class="data-list">User Operator</span>
                <span class="data-list">Staff Humas</span>
            </div>
            <div class="data username">
                <span class="data-title">Username</span>
                <span class="data-list">admin01</span>
                <span class="data-list">operator02</span>
                <span class="data-list">humas03</span>
            </div>
            <div class="data role">
                <span class="data-title">Role</span>
                <span class="data-list">Admin</span>
                <span class="data-list">Operator</span>
                <span class="data-list">Staff</span>
            </div>
            <div class="data actions">
                <span class="data-title">Aksi</span>
                <span class="data-list">
                    <button class="btn-action-aksi edit" onclick="window.location.href='index.php?page=edit-pengguna'"><i class="uil uil-edit"></i></button>
                    <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
                </span>
                <span class="data-list">
                    <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                    <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
                </span>
                <span class="data-list">
                    <button class="btn-action-aksi edit"><i class="uil uil-edit"></i></button>
                    <button class="btn-action-aksi delete"><i class="uil uil-trash-alt"></i></button>
                </span>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <button class="active">1</button>
        <button>2</button>
        <button>3</button>
        <button>Next</button>
    </div>

    <!-- Tombol tambah pengguna -->
    <div style="margin-top:20px; text-align:right;">
        <button class="btn-tambah" onclick="window.location.href='index.php?page=tambah-pengguna'">
            <i class="uil uil-plus"></i> Tambah Pengguna
        </button>
    </div>
</div>
