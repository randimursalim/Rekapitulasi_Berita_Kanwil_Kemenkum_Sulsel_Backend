<?php
// app/views/pages/edit-kegiatan.php
?>

<div class="overview">
    <div class="title">
        <i class="uil uil-edit"></i>
        <span class="text">Edit Kegiatan</span>
    </div>

    <!-- Form Edit Kegiatan -->
    <form action="index.php?page=update-kegiatan" method="post" class="input-berita-form" autocomplete="off">
        <!-- Hidden ID -->
        <input type="hidden" id="idKegiatan" name="idKegiatan" value="1">

        <div class="form-group">
            <label for="namaKegiatan">Nama Kegiatan</label>
            <input type="text" id="namaKegiatan" name="namaKegiatan" value="Rapat Koordinasi" required>
        </div>

        <div class="form-group">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="2025-09-25" required>
        </div>

        <div class="form-group">
            <label for="jamMulai">Jam Mulai</label>
            <input type="time" id="jamMulai" name="jamMulai" value="09:00" required>
        </div>

        <div class="form-group">
            <label for="jamSelesai">Jam Selesai</label>
            <input type="time" id="jamSelesai" name="jamSelesai" value="11:00" required>
        </div>

        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="Belum Dimulai" selected>Belum Dimulai</option>
                <option value="Selesai">Selesai</option>
                <option value="Ditunda">Ditunda</option>
                <option value="Dibatalkan">Dibatalkan</option>
            </select>
        </div>

        <div class="form-group">
            <label for="keterangan">Keterangan</label>
            <textarea id="keterangan" name="keterangan" rows="5" placeholder="Masukkan detail kegiatan (misal: kegiatan dihadiri oleh..., membahas tentang...)" required>Rapat ini membahas koordinasi pelaksanaan kegiatan bulanan, dihadiri oleh seluruh staf humas.</textarea>
        </div>

        <!-- Tombol Aksi -->
        <div style="text-align:center; margin-top:20px;" class="form-actions">
            <button type="submit" class="btn-simpan">
                <i class="uil uil-save"></i> Update
            </button>
            <button type="button" class="btn-batal" onclick="window.location.href='index.php?page=jadwal-kegiatan'">
                <i class="uil uil-times"></i> Batal
            </button>
        </div>
    </form>
</div>
