SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `Pegawai`
--
ALTER TABLE `Pegawai`
  ADD PRIMARY KEY (`NIP`);

--
-- Indeks untuk tabel `skp_kuantitatif_awal_tahun_pegawai`
--
ALTER TABLE `skp_kuantitatif_awal_tahun_pegawai`
  ADD PRIMARY KEY (`ID_SKP`);

--
-- Indeks untuk tabel `skp_lampiran`
--
ALTER TABLE `skp_lampiran`
  ADD PRIMARY KEY (`id_lampiran`);

--
-- Indeks untuk tabel `skp_pegawai`
--
ALTER TABLE `skp_pegawai`
  ADD PRIMARY KEY (`ID_SKP`);

--
-- Indeks untuk tabel `skp_perilaku_awal_tahun_pegawai`
--
ALTER TABLE `skp_perilaku_awal_tahun_pegawai`
  ADD PRIMARY KEY (`ID_SKP`);

--
-- Indeks untuk tabel `skp_perilaku_pegawai`
--
ALTER TABLE `skp_perilaku_pegawai`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nip` (`NIP`),
  ADD KEY `idx_nip_atasan` (`NIP_ATASAN_LANGSUNG`),
  ADD KEY `idx_periode` (`TAHUN`,`TRIWULAN`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`USERNAME`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`),
  ADD KEY `NIP` (`NIP`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `skp_kuantitatif_awal_tahun_pegawai`
--
ALTER TABLE `skp_kuantitatif_awal_tahun_pegawai`
  MODIFY `ID_SKP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1823;

--
-- AUTO_INCREMENT untuk tabel `skp_lampiran`
--
ALTER TABLE `skp_lampiran`
  MODIFY `id_lampiran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=232;

--
-- AUTO_INCREMENT untuk tabel `skp_pegawai`
--
ALTER TABLE `skp_pegawai`
  MODIFY `ID_SKP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13642;

--
-- AUTO_INCREMENT untuk tabel `skp_perilaku_awal_tahun_pegawai`
--
ALTER TABLE `skp_perilaku_awal_tahun_pegawai`
  MODIFY `ID_SKP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT untuk tabel `skp_perilaku_pegawai`
--
ALTER TABLE `skp_perilaku_pegawai`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1477;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`NIP`) REFERENCES `Pegawai` (`NIP`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

COMMIT;
