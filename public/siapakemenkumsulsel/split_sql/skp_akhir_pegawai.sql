SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `skp_akhir_pegawai` (
  `ID_SKP` int(11) NOT NULL,
  `NAMA` varchar(250) DEFAULT NULL,
  `NIP` char(18) DEFAULT NULL,
  `NAMA_ATASAN_LANGSUNG` varchar(250) DEFAULT NULL,
  `NIP_ATASAN_LANGSUNG` char(18) DEFAULT NULL,
  `RHK_PIMPINAN_INTERV` varchar(1000) DEFAULT NULL,
  `RENCANA_HASIL_KERJA` varchar(1000) DEFAULT NULL,
  `ASPEK` varchar(250) DEFAULT NULL,
  `INDIKATOR_KINERJA_INDIVIDU` varchar(1000) DEFAULT NULL,
  `TARGET` int(100) DEFAULT NULL,
  `REALISASI_BERDASARKAN_BUKTI_DUKUNG` int(100) DEFAULT NULL,
  `UMPAN_BALIK_DENGAN_BUKTI_DUKUNG` varchar(1000) DEFAULT NULL,
  `TANGGAL_INPUT_SKP` date DEFAULT NULL,
  `TANGGAL_EVALUASI_SKP` date DEFAULT NULL,
  `TAHUN` int(11) DEFAULT NULL,
  `ID_SKP_GLOBAL` int(255) NOT NULL,
  `JENIS_KINERJA` varchar(1000) DEFAULT NULL,
  `RATING_PERILAKU_KERJA` varchar(100) NOT NULL,
  `PREDIKAT_KINERJA_PEGAWAI` varchar(100) NOT NULL,
  `UMPAN_BALIK_STICKER` varchar(1) NOT NULL,
  `RATING_HASIL_KERJA` varchar(100) NOT NULL,
  `SATUAN_ASPEK` varchar(100) NOT NULL,
  `STATUS` varchar(50) NOT NULL,
  `CAPAIAN_KINERJA_ORGANISASI` varchar(100) NOT NULL,
  `SATUAN` varchar(500) NOT NULL,
  `PANGKAT_GOL_RUANG` varchar(100) DEFAULT NULL,
  `JABATAN` varchar(100) DEFAULT NULL,
  `UNIT_KERJA` varchar(200) DEFAULT NULL,
  `SATUAN_KERJA` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `skp_akhir_pegawai`
--

INSERT INTO `skp_akhir_pegawai` (`ID_SKP`, `NAMA`, `NIP`, `NAMA_ATASAN_LANGSUNG`, `NIP_ATASAN_LANGSUNG`, `RHK_PIMPINAN_INTERV`, `RENCANA_HASIL_KERJA`, `ASPEK`, `INDIKATOR_KINERJA_INDIVIDU`, `TARGET`, `REALISASI_BERDASARKAN_BUKTI_DUKUNG`, `UMPAN_BALIK_DENGAN_BUKTI_DUKUNG`, `TANGGAL_INPUT_SKP`, `TANGGAL_EVALUASI_SKP`, `TAHUN`, `ID_SKP_GLOBAL`, `JENIS_KINERJA`, `RATING_PERILAKU_KERJA`, `PREDIKAT_KINERJA_PEGAWAI`, `UMPAN_BALIK_STICKER`, `RATING_HASIL_KERJA`, `SATUAN_ASPEK`, `STATUS`, `CAPAIAN_KINERJA_ORGANISASI`, `SATUAN`, `PANGKAT_GOL_RUANG`, `JABATAN`, `UNIT_KERJA`, `SATUAN_KERJA`) VALUES
(1, 'Andi Wahyu Iskandar Zainal, S.H, M.Kn', '198907232019011001', 'Heny Widyawati, S.H., MH', '197601312001122001', 'Analisis dan\r\nEvaluasi Produk Hukum di\r\nWilayah', 'Tersedianya klasifikasi, identifikasi, dan Inventarisasi bahan dan data terkait peraturan perundang-undangan', 'kuantitas', 'Jumlah Analisis dan Evaluasi Produk Hukun Yang Dilakukan', 2, 2, '', '2025-11-06', '2025-11-06', 2025, 1, 'kinerja utama', '', '', '', '', '', 'PROSES EVALUASI', '', 'Dokumen', NULL, NULL, NULL, NULL),
(2, 'Andi Wahyu Iskandar Zainal, S.H, M.Kn', '198907232019011001', 'Heny Widyawati, S.H., MH', '197601312001122001', 'Analisis dan\r\nEvaluasi Produk Hukum di\r\nWilayah', 'Tersedianya klasifikasi, identifikasi, dan Inventarisasi bahan dan data terkait peraturan perundang-undangan', 'waktu', 'Waktu Pelaksanaan ', 12, 12, '', '2025-11-06', '2025-11-06', 2025, 1, 'kinerja utama', '', '', '', '', '', 'PROSES EVALUASI', '', 'Bulan', NULL, NULL, NULL, NULL),
(3, 'Andi Wahyu Iskandar Zainal, S.H, M.Kn', '198907232019011001', 'Heny Widyawati, S.H., MH', '197601312001122001', 'Analisis dan\r\nEvaluasi Produk Hukum di\r\nWilayah', 'Tersedianya klasifikasi, identifikasi, dan Inventarisasi bahan dan data terkait peraturan perundang-undangan', 'kuantitas', 'Jumlah Fasilitasi Perencanaan Peraturan Daerah', 2, 2, '', '2025-11-06', '2025-11-06', 2025, 1, 'kinerja utama', '', '', '', '', '', 'PROSES EVALUASI', '', 'Dokumen', NULL, NULL, NULL, NULL),
(4, 'Andi Wahyu Iskandar Zainal, S.H, M.Kn', '198907232019011001', 'Heny Widyawati, S.H., MH', '197601312001122001', 'Analisis dan\r\nEvaluasi Produk Hukum di\r\nWilayah', 'Tersedianya klasifikasi, identifikasi, dan Inventarisasi bahan dan data terkait peraturan perundang-undangan', 'waktu', 'Waktu Pelaksanaan ', 12, 12, '', '2025-11-06', '2025-11-06', 2025, 1, 'kinerja utama', '', '', '', '', '', 'PROSES EVALUASI', '', 'Bulan', NULL, NULL, NULL, NULL),
(5, 'Andi Wahyu Iskandar Zainal, S.H, M.Kn', '198907232019011001', 'Heny Widyawati, S.H., MH', '197601312001122001', 'Terlaksananya  fasilitasi perancangan pendampingan/supervisi fasilitasi perancangan peraturan daerah', 'Tersedianya bahan dan data kegiatan fasilitasi perancangan pendampingan/supervisi fasilitasi perancangan peraturan daerah', 'kuantitas', 'Jumlah pelaksanaan fasilitasi perancangan pendampingan/supervisi fasilitasi perancangan peraturan daerah', 10, 10, '', '2025-11-06', '2025-11-06', 2025, 1, 'kinerja utama', '', '', '', '', '', 'PROSES EVALUASI', '', 'Kegiatan', NULL, NULL, NULL, NULL),
(6, 'Andi Wahyu Iskandar Zainal, S.H, M.Kn', '198907232019011001', 'Heny Widyawati, S.H., MH', '197601312001122001', 'Terlaksananya  fasilitasi konsultasi perancangan peraturan daerah', 'Tersedianya bahan dan data kegiatan fasilitasi konsultasi perancangan peraturan daerah', 'kuantitas', ' Jumlah pelaksanaan fasilitasi konsultasi perancangan peraturan daerah', 10, 10, '', '2025-11-06', '2025-11-06', 2025, 1, 'kinerja utama', '', '', '', '', '', 'PROSES EVALUASI', '', 'Kegiatan', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `skp_akhir_perilaku_pegawai`
--


COMMIT;
