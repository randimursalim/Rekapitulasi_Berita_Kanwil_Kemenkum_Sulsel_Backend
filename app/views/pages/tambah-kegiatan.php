<?php
// app/views/pages/tambah-kegiatan.php
?>

<div class="overview">
    <div class="title">
        <i class="uil uil-plus"></i>
        <span class="text">Tambah Kegiatan</span>
    </div>

    <!-- Form Tambah Kegiatan -->
    <form id="formKegiatan" class="input-berita-form" autocomplete="off">
        <div class="form-group">
            <label for="namaKegiatan">Nama Kegiatan</label>
            <input type="text" id="namaKegiatan" name="namaKegiatan" placeholder="Masukkan nama kegiatan" required>
        </div>

        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" required>
        </div>

        <div class="form-group">
            <label for="jamMulai">Jam Mulai</label>
            <input type="time" id="jamMulai" name="jamMulai" required>
        </div>

        <div class="form-group">
            <label for="jamSelesai">Jam Selesai</label>
            <input type="time" id="jamSelesai" name="jamSelesai" required>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan kegiatan, misal peserta, tujuan, hasil rapat" required></textarea>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="">-- Pilih Status --</option>
                <option value="Selesai">Selesai</option>
                <option value="Ditunda">Ditunda</option>
                <option value="Dibatalkan">Dibatalkan</option>
                <option value="Belum Dimulai">Belum Dimulai</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="form-actions" style="text-align:center; margin-top:20px;">
            <button type="submit" class="btn-simpan">
                <i class="uil uil-save"></i> Simpan
            </button>
            <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=jadwal-kegiatan'">
                <i class="uil uil-times"></i> Batal
            </button>
        </div>
    </form>
</div>
