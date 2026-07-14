SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `skp_akhir_perilaku_pegawai` (
  `id` int(10) UNSIGNED NOT NULL,
  `NAMA` varchar(255) NOT NULL,
  `NIP` varchar(32) NOT NULL,
  `NAMA_ATASAN_LANGSUNG` varchar(255) DEFAULT NULL,
  `NIP_ATASAN_LANGSUNG` varchar(32) DEFAULT NULL,
  `PERILAKU_KERJA_BERORIENTASI_PELAYANAN` text DEFAULT NULL,
  `PERILAKU_KERJA_AKUNTABEL` text DEFAULT NULL,
  `PERILAKU_KERJA_KOMPETEN` text DEFAULT NULL,
  `PERILAKU_KERJA_HARMONIS` text DEFAULT NULL,
  `PERILAKU_KERJA_LOYAL` text DEFAULT NULL,
  `PERILAKU_KERJA_ADAPTIF` text DEFAULT NULL,
  `PERILAKU_KERJA_KOLABORATIF` text DEFAULT NULL,
  `EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN` text DEFAULT NULL,
  `EKSPEKTASI_PIMPINAN_AKUNTABEL` text DEFAULT NULL,
  `EKSPEKTASI_PIMPINAN_KOMPETEN` text DEFAULT NULL,
  `EKSPEKTASI_PIMPINAN_HARMONIS` text DEFAULT NULL,
  `EKSPEKTASI_PIMPINAN_LOYAL` text DEFAULT NULL,
  `EKSPEKTASI_PIMPINAN_ADAPTIF` text DEFAULT NULL,
  `EKSPEKTASI_PIMPINAN_KOLABORATIF` text DEFAULT NULL,
  `UMPAN_BALIK_BERORIENTASI_PELAYANAN` text DEFAULT NULL,
  `UMPAN_BALIK_AKUNTABEL` text DEFAULT NULL,
  `UMPAN_BALIK_KOMPETEN` text DEFAULT NULL,
  `UMPAN_BALIK_HARMONIS` text DEFAULT NULL,
  `UMPAN_BALIK_LOYAL` text DEFAULT NULL,
  `UMPAN_BALIK_ADAPTIF` text DEFAULT NULL,
  `UMPAN_BALIK_KOLABORATIF` text DEFAULT NULL,
  `TAHUN` smallint(5) UNSIGNED NOT NULL,
  `STATUS` varchar(50) NOT NULL DEFAULT 'perlu evaluasi',
  `TANGGAL_INPUT_SKP` datetime NOT NULL DEFAULT current_timestamp(),
  `ID_SKP_GLOBAL` int(255) NOT NULL,
  `PANGKAT_GOL_RUANG` varchar(100) DEFAULT NULL,
  `JABATAN` varchar(100) DEFAULT NULL,
  `UNIT_KERJA` varchar(200) DEFAULT NULL,
  `SATUAN_KERJA` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `skp_akhir_perilaku_pegawai`
--

INSERT INTO `skp_akhir_perilaku_pegawai` (`id`, `NAMA`, `NIP`, `NAMA_ATASAN_LANGSUNG`, `NIP_ATASAN_LANGSUNG`, `PERILAKU_KERJA_BERORIENTASI_PELAYANAN`, `PERILAKU_KERJA_AKUNTABEL`, `PERILAKU_KERJA_KOMPETEN`, `PERILAKU_KERJA_HARMONIS`, `PERILAKU_KERJA_LOYAL`, `PERILAKU_KERJA_ADAPTIF`, `PERILAKU_KERJA_KOLABORATIF`, `EKSPEKTASI_PIMPINAN_BERORIENTASI_PELAYANAN`, `EKSPEKTASI_PIMPINAN_AKUNTABEL`, `EKSPEKTASI_PIMPINAN_KOMPETEN`, `EKSPEKTASI_PIMPINAN_HARMONIS`, `EKSPEKTASI_PIMPINAN_LOYAL`, `EKSPEKTASI_PIMPINAN_ADAPTIF`, `EKSPEKTASI_PIMPINAN_KOLABORATIF`, `UMPAN_BALIK_BERORIENTASI_PELAYANAN`, `UMPAN_BALIK_AKUNTABEL`, `UMPAN_BALIK_KOMPETEN`, `UMPAN_BALIK_HARMONIS`, `UMPAN_BALIK_LOYAL`, `UMPAN_BALIK_ADAPTIF`, `UMPAN_BALIK_KOLABORATIF`, `TAHUN`, `STATUS`, `TANGGAL_INPUT_SKP`, `ID_SKP_GLOBAL`, `PANGKAT_GOL_RUANG`, `JABATAN`, `UNIT_KERJA`, `SATUAN_KERJA`) VALUES
(0, 'Andi Wahyu Iskandar Zainal, S.H, M.Kn', '198907232019011001', 'Heny Widyawati, S.H., MH', '197601312001122001', '- Memahami dan memenuhi kebutuhan masyarakat.\n- Ramah, cekatan, solutif, dan dapat diandalkan.\n- Melakukan perbaikan tiada henti.', '- Melaksanakan tugas dengan jujur, bertanggungjawab, cermat, disiplin dan berintegritas tinggi.\n- Menggunakan kekayaan dan barang milik negara secara bertanggungjawab, efektif dan efisien.\n- Tidak menyalahgunakan kewenangan jabatan.', '- Meningkatkan kompetensi diri untuk menjawab tantangan yang selalu berubah.\n- Membantu orang lain belajar.\n- Melaksanakan tugas dengan kualitas terbaik.', '- Menghargai setiap orang apapun latar belakangnya.\n- Suka menolong orang lain.\n- Membangun lingkungan kerja yang kondusif.', '- Memegang teguh ideologi Pancasila, Undang-Undang Dasar Negara Republik Indonesia Tahun 1945, setia pada Negara Kesatuan Republik Indonesia serta pemerintahan yang sah.\n- Menjaga nama baik ASN, Pimpinan, Instansi dan Negara.\n- Menjaga rahasia jabatan dan negara.', '- Cepat menyesuaikan diri menghadapi perubahan\n- Terus berinovasi dan mengembangkan kreativitas\n- Bertindak proaktif', '- Memberi kesempatan kepada berbagai pihak untuk berkontribusi.\n- Terbuka dalam bekerjasama untuk menghasilkan nilai tambah.\n- Menggerakan pemanfaatan berbagai sumber daya untuk tujuan bersama.', 'Ekspektasi Khusus Pimpinan : Memberikan layanan yang cepat dan ramah, Bersedia ditegur dan memperbaiki kesalahan yang dilakukan.', 'Ekspektasi Khusus Pimpinan : Melaksanakan tugas dengan jujur dan bertanggung jawab serta menjunjung tinggi integritas pegawai', 'Ekspektasi Khusus Pimpinan : Menyelesaikan  setiap  pekerjaan  sesuai  dengan  target  dan standar kualitas yang ditetapkan', 'Ekspektasi Khusus Pimpinan : Saling membantu dengan rekan kerja, khususnya pekerjaan yang perlu diselesaikan tepat waktu,  serta jalin komunikasi yang baik dengan Instansi terkait.', 'Ekspektasi Khusus Pimpinan : Utamakan tugas dan fungsi sebagai ASN diatas kepentingan pribadi/golongan', 'Ekspektasi Khusus Pimpinan : Bersedia mengikuti pembinaan dan pengembangan kompetensi', 'Ekspektasi Khusus Pimpinan : bangun kerjasama yang baik dengan Instansi terkait untuk kelancaran pelaksanaan Tugas dan Fungsi', '', '', '', '', '', '', '', 2025, 'PROSES EVALUASI', '2025-11-06 12:21:29', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `skp_kuantitatif_awal_tahun_pegawai`
--


COMMIT;
