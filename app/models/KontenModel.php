<?php
class KontenModel {
    public function getStatistik() {
        return [
            'total_berita' => 80,
            'total_medsos' => 34,
            'total_arsip'  => 114,
        ];
    }

    public function getLogAktivitas() {
        return [
            ['aktivitas'=>'Randi menambahkan berita baru','tanggal'=>'2025-09-08','waktu'=>'08:45','user'=>'Admin','status'=>'Tambah'],
            ['aktivitas'=>'Operator mengedit postingan Instagram','tanggal'=>'2025-09-07','waktu'=>'10:15','user'=>'Operator','status'=>'Edit'],
            ['aktivitas'=>'Humas menghapus draft berita','tanggal'=>'2025-09-06','waktu'=>'11:20','user'=>'Humas','status'=>'Hapus'],
        ];
    }

    public function getDetailBerita() {
        return [
            ['name' => 'Media Online', 'value' => 2],
            ['name' => 'Surat Kabar', 'value' => 45],
            ['name' => 'Website Kanwil', 'value' => 15],
        ];
    }

    public function getDetailMedsos() {
        return [
            ['name' => 'FacebookCihuy', 'value' => 3],
            ['name' => 'Instagram', 'value' => 8],
            ['name' => 'Twitter (X)', 'value' => 6],
            ['name' => 'TikTok', 'value' => 10],
        ];
    }
}

