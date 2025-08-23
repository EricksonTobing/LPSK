-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Agu 2025 pada 09.45
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_lpsk2 (4)`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggaran`
--

CREATE TABLE `anggaran` (
  `kode_anggaran` varchar(20) NOT NULL,
  `nama_anggaran` varchar(100) DEFAULT NULL,
  `total_anggaran` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `layanan`
--

CREATE TABLE `layanan` (
  `no_kep_smpl` varchar(50) NOT NULL,
  `no_reg_medan` varchar(50) NOT NULL,
  `no_registrasi` varchar(50) NOT NULL,
  `no_spk` varchar(50) DEFAULT NULL,
  `status_spk` enum('Sudah TTD','Belum TTD') NOT NULL COMMENT 'Kolom tambahan Revisi',
  `nama_terlindung` varchar(100) DEFAULT NULL,
  `tanggal_disposisi` date DEFAULT NULL,
  `tgl_mulai_layanan` date NOT NULL COMMENT 'Kolom Tambahan Revisi',
  `masa_layanan` varchar(50) DEFAULT NULL COMMENT 'Revisi Ubah Tipe Data',
  `tambahan_masa_layanan` varchar(50) DEFAULT NULL COMMENT 'Revisi Ubah Tipe Data',
  `tgl_berakhir_layanan` date NOT NULL COMMENT 'Komlom Tambahan Revisi',
  `jenis_perlindungan` varchar(200) DEFAULT NULL,
  `wilayah_hukum` varchar(100) DEFAULT NULL,
  `jenis_tindak_pidana` enum('KSA','PENYIKSAAN','KORUPSI','TPPO','PHB','TERORISME','KS','PENGANIAYAAN BERAT','NARKOTIKA','TPL') DEFAULT NULL,
  `nama_ta_layanan` enum('AM','AJ','RW','TP','SMW') DEFAULT NULL COMMENT 'Revisi Ubah Tipe Data ',
  `status` enum('BERJALAN','DIHENTIKAN','PERPANJANGAN') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penelaahan`
--

CREATE TABLE `penelaahan` (
  `no_registrasi` varchar(50) NOT NULL,
  `no_reg_medan` varchar(50) NOT NULL,
  `tanggal_dispo` date DEFAULT NULL,
  `proses_penalaahan` varchar(50) NOT NULL COMMENT 'Kolom tambahan revisi',
  `wilayah_perkara` varchar(100) DEFAULT NULL,
  `layanan_dimohonkan` varchar(200) DEFAULT NULL,
  `case_manager` varchar(100) DEFAULT NULL,
  `tgl_berakhir_penelaahan` date DEFAULT NULL,
  `waktu_tambahan` date DEFAULT NULL,
  `nama_ta_penalaahan` enum('YM','MBF','GPJ','IM') NOT NULL COMMENT 'Kolom tambahan Revisi',
  `risalah_laporan` enum('BELUM','SUDAH') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `nomor_kuintasi` varchar(20) NOT NULL,
  `kode_anggaran` varchar(20) NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `kode_mak` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `permohonan`
--

CREATE TABLE `permohonan` (
  `no_reg_medan` varchar(50) NOT NULL,
  `nama_pemohon` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `status_hukum` enum('Saksi','Korban','Ahli','Pelapor','Saksi Pelaku') DEFAULT NULL COMMENT 'Ubah tipe data',
  `tgl_pengajuan` date DEFAULT NULL,
  `pihak_perwakilan` enum('KELUARGA','APH','INSTASI PEMERINTAH','DIRI SENDIRI') DEFAULT NULL,
  `tindak_pidana` enum('KSA','PENYIKSAAN','KORUPSI','TPPO','PHB','TERORISME','KS','PENGANIAYAAN BERAT','NARKOTIKA','TPL') DEFAULT NULL,
  `petugas_penerima` varchar(100) DEFAULT NULL,
  `kelengkapan_berkas` varchar(100) DEFAULT NULL,
  `media_pengajuan` enum('DATANG LANGSUNG','WA','EMAIL','SURAT') DEFAULT NULL,
  `link_berkas_permohonan` text DEFAULT NULL,
  `jenis_perlindungan` varchar(150) DEFAULT NULL,
  `kab_kot_locus` varchar(100) DEFAULT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `tempat_permohonan` enum('MEDAN','JAKARTA') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
--

CREATE TABLE `sessions` (
  `sid` varchar(255) NOT NULL,
  `sess` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `expired` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`sid`, `sess`, `expired`) VALUES
('Ffy9ahVTCq5ygIXQ_Zq-auE__eYVrwCr', '{\"cookie\":{\"originalMaxAge\":28800000,\"expires\":\"2025-08-15T11:08:23.832Z\",\"secure\":false,\"httpOnly\":true,\"path\":\"/\",\"sameSite\":\"lax\"},\"csrfSecret\":\"4SDFoSc30GZ2vnH9jKCmwx4F\"}', '2025-08-15 16:30:21'),
('FUuFRzhek5dWRzqtTy2LMOsyr6Q5idVr', '{\"cookie\":{\"originalMaxAge\":28800000,\"expires\":\"2025-08-15T01:59:28.379Z\",\"secure\":false,\"httpOnly\":true,\"path\":\"/\",\"sameSite\":\"lax\"},\"csrfSecret\":\"wxl5q6aYRjD_UGI2SKU8L-0c\",\"user\":{\"id_user\":1,\"username\":\"admin\",\"nama_lengkap\":\"Administrator\",\"role\":\"admin\"}}', '2025-08-15 09:50:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
(1, 'admin', '$2b$12$k0.VMroQsaoi2irwiL69ae86IbGN.W0MlF7.ZA/JI2/pfrhkSLgx2', 'Administrator', 'admin@example.com', 'admin'),
(2, 'erik', '$2y$12$522sTyanYbUg25iLUjDh9O7tAnwrrJI96Dt9im4/b.XSefykWdpNC', 'erikson', 'eriksontobing28@gmail.com', 'user');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `anggaran`
--
ALTER TABLE `anggaran`
  ADD PRIMARY KEY (`kode_anggaran`);

--
-- Indeks untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD PRIMARY KEY (`no_kep_smpl`),
  ADD UNIQUE KEY `no_spk` (`no_spk`),
  ADD KEY `no_reg_medan` (`no_reg_medan`),
  ADD KEY `no_registrasi` (`no_registrasi`);

--
-- Indeks untuk tabel `penelaahan`
--
ALTER TABLE `penelaahan`
  ADD PRIMARY KEY (`no_registrasi`),
  ADD KEY `no_reg_medan` (`no_reg_medan`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`nomor_kuintasi`),
  ADD KEY `fk_pengeluaran_anggaran` (`kode_anggaran`);

--
-- Indeks untuk tabel `permohonan`
--
ALTER TABLE `permohonan`
  ADD PRIMARY KEY (`no_reg_medan`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`sid`),
  ADD KEY `sessions_expired_index` (`expired`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `layanan`
--
ALTER TABLE `layanan`
  ADD CONSTRAINT `layanan_ibfk_1` FOREIGN KEY (`no_reg_medan`) REFERENCES `permohonan` (`no_reg_medan`),
  ADD CONSTRAINT `layanan_ibfk_2` FOREIGN KEY (`no_registrasi`) REFERENCES `penelaahan` (`no_registrasi`);

--
-- Ketidakleluasaan untuk tabel `penelaahan`
--
ALTER TABLE `penelaahan`
  ADD CONSTRAINT `penelaahan_ibfk_1` FOREIGN KEY (`no_reg_medan`) REFERENCES `permohonan` (`no_reg_medan`);

--
-- Ketidakleluasaan untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD CONSTRAINT `fk_pengeluaran_anggaran` FOREIGN KEY (`kode_anggaran`) REFERENCES `anggaran` (`kode_anggaran`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
