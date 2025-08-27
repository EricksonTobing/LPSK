-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_lpsk
CREATE DATABASE IF NOT EXISTS `db_lpsk` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_lpsk`;

-- Dumping structure for table db_lpsk.anggaran
CREATE TABLE IF NOT EXISTS `anggaran` (
  `kode_anggaran` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_anggaran` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_anggaran` decimal(15,2) NOT NULL,
  `tahun` year NOT NULL COMMENT 'Kolom Baru Revisi2',
  PRIMARY KEY (`kode_anggaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.anggaran: ~0 rows (approximately)

-- Dumping structure for table db_lpsk.layanan
CREATE TABLE IF NOT EXISTS `layanan` (
  `no_kep_smpl` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `no_reg_medan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `no_registrasi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `no_spk` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_spk` enum('Sudah TTD','Belum TTD') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Kolom tambahan Revisi',
  `nama_terlindung` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tanggal_disposisi` date DEFAULT NULL,
  `tgl_mulai_layanan` date NOT NULL COMMENT 'Kolom Tambahan Revisi',
  `masa_layanan` enum('3 BULAN','6 BULAN') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Revisi3 Ubah Tipe Data',
  `tambahan_masa_layanan` enum('3 BULAN','6 BULAN') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Revisi3 Ubah Tipe Data',
  `tgl_berakhir_layanan` date NOT NULL COMMENT 'Komlom Tambahan Revisi',
  `jenis_perlindungan` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wilayah_hukum` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_tindak_pidana` enum('KSA','PENYIKSAAN','KORUPSI','TPPO','PHB','TERORISME','KS','PENGANIAYAAN BERAT','NARKOTIKA','TPL') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_ta_layanan` enum('AM','AJC','RW','TP','SMW') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Revisi 2 Ubah Tipe Data\r\n, Perbaikan opsi AJ=AJC Revisi 3 ',
  `status` enum('BERJALAN','DIHENTIKAN','PERPANJANGAN') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tgl_no_kep_smpl` date NOT NULL COMMENT 'Tambah Kolom Revisi2',
  `id_pegawai` int NOT NULL COMMENT 'Kolom Baru Revisi4',
  PRIMARY KEY (`no_kep_smpl`),
  UNIQUE KEY `no_spk` (`no_spk`),
  KEY `no_reg_medan` (`no_reg_medan`),
  KEY `no_registrasi` (`no_registrasi`),
  KEY `fk_layanan_pegawai` (`id_pegawai`),
  CONSTRAINT `fk_layanan_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`),
  CONSTRAINT `layanan_ibfk_1` FOREIGN KEY (`no_reg_medan`) REFERENCES `permohonan` (`no_reg_medan`),
  CONSTRAINT `layanan_ibfk_2` FOREIGN KEY (`no_registrasi`) REFERENCES `penelaahan` (`no_registrasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.layanan: ~0 rows (approximately)
INSERT INTO `layanan` (`no_kep_smpl`, `no_reg_medan`, `no_registrasi`, `no_spk`, `status_spk`, `nama_terlindung`, `tanggal_disposisi`, `tgl_mulai_layanan`, `masa_layanan`, `tambahan_masa_layanan`, `tgl_berakhir_layanan`, `jenis_perlindungan`, `wilayah_hukum`, `jenis_tindak_pidana`, `nama_ta_layanan`, `status`, `tgl_no_kep_smpl`, `id_pegawai`) VALUES
	('KEP-SMPL-2025-01', 'REG-MDN-2025-001', 'REG-PNL-2023-01', 'SPK-2025-020', '', 'OKTOMA', '2025-08-21', '2025-08-20', '', '', '2025-08-28', 'javascript', 'medan', '', 'AM', '', '2025-08-28', 1);

-- Dumping structure for table db_lpsk.mak
CREATE TABLE IF NOT EXISTS `mak` (
  `kode_mak` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_mak` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`kode_mak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.mak: ~14 rows (approximately)
INSERT INTO `mak` (`kode_mak`, `nama_mak`) VALUES
	('521111', 'Belanja Keperluan Perkantoran'),
	('521113', 'Belanja Penambah Daya Tahan Tubuh'),
	('521114', 'Belanja Pengiriman Surat Dinas Pos Surat'),
	('521211', 'Belanja Bahan'),
	('521213', 'Honor Output Kegiatan'),
	('521219', 'Belanja Barang Non Operasional Lainnya'),
	('522112', 'Belanja Langganan Telepon'),
	('522151', 'Belanja Jasa Profesi'),
	('522191', 'Belanja Jasa Lainnya'),
	('523111', 'Belanja Pemeliharaan Gedung dan Bangunan'),
	('523121', 'Belanja Pemeliharaan Peralatan dan Mesin'),
	('524111', 'Belanja Perjalanan Dinas Biasa'),
	('524113', 'Belanja Perjalanan Dinas Dalam Kota'),
	('524114', 'Belanja Perjalanan Dinas Paket Meeting Dalam Kota');

-- Dumping structure for table db_lpsk.pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id_pegawai` int NOT NULL AUTO_INCREMENT,
  `nama_pegawai` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_pegawai`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.pegawai: ~0 rows (approximately)
INSERT INTO `pegawai` (`id_pegawai`, `nama_pegawai`) VALUES
	(1, 'erikson'),
	(2, 'valois');

-- Dumping structure for table db_lpsk.penelaahan
CREATE TABLE IF NOT EXISTS `penelaahan` (
  `no_registrasi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `no_reg_medan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal_dispo` date DEFAULT NULL,
  `proses_penalaahan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Kolom tambahan revisi',
  `id_pegawai` int DEFAULT NULL COMMENT 'Kolom Baru Revisi4',
  `tgl_berakhir_penelaahan` date DEFAULT NULL,
  `waktu_tambahan` date DEFAULT NULL,
  `nama_ta_penalaahan` enum('YM','MBF','GPJ','IM') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Kolom tambahan Revisi',
  `risalah_laporan` enum('BELUM','SUDAH') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `proses_hukum` enum('Penyelidikan','Penyidikan','P-19','P-21','P-22','Penuntutan','Putusan Pengadilan Negeri','Putusan Pengadilan Tinggi') COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Tambah Kolom Revisi 3',
  PRIMARY KEY (`no_registrasi`),
  KEY `no_reg_medan` (`no_reg_medan`),
  KEY `fk_penelaahan_pegawai` (`id_pegawai`),
  CONSTRAINT `fk_penelaahan_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`),
  CONSTRAINT `penelaahan_ibfk_1` FOREIGN KEY (`no_reg_medan`) REFERENCES `permohonan` (`no_reg_medan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.penelaahan: ~0 rows (approximately)
INSERT INTO `penelaahan` (`no_registrasi`, `no_reg_medan`, `tanggal_dispo`, `proses_penalaahan`, `id_pegawai`, `tgl_berakhir_penelaahan`, `waktu_tambahan`, `nama_ta_penalaahan`, `risalah_laporan`, `proses_hukum`) VALUES
	('REG-PNL-2023-01', 'REG-MDN-2025-001', '2025-08-27', 'BERLANGSUNG', 1, '2025-08-21', NULL, 'MBF', 'BELUM', 'Penyelidikan');

-- Dumping structure for table db_lpsk.pengeluaran
CREATE TABLE IF NOT EXISTS `pengeluaran` (
  `nomor_kuintasi` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `kode_anggaran` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `kode_mak` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'FK Tabel MAK',
  `keterangan` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`nomor_kuintasi`),
  KEY `fk_pengeluaran_anggaran` (`kode_anggaran`),
  KEY `kode_mak` (`kode_mak`),
  CONSTRAINT `fk_pengeluaran_anggaran` FOREIGN KEY (`kode_anggaran`) REFERENCES `anggaran` (`kode_anggaran`),
  CONSTRAINT `pengeluaran_ibfk_1` FOREIGN KEY (`kode_mak`) REFERENCES `mak` (`kode_mak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.pengeluaran: ~0 rows (approximately)

-- Dumping structure for table db_lpsk.permohonan
CREATE TABLE IF NOT EXISTS `permohonan` (
  `no_reg_medan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_pemohon` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jenis_kelamin` enum('L','P') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_hukum` enum('Saksi','Korban','Ahli','Pelapor','Saksi Pelaku') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Ubah tipe data',
  `tgl_pengajuan` date DEFAULT NULL,
  `pihak_perwakilan` enum('KELUARGA','APH','INSTASI PEMERINTAH','DIRI SENDIRI','DLL') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tambah Opsi dll, Revisi2',
  `tindak_pidana` enum('KSA','PENYIKSAAN','KORUPSI','TPPO','PHB','TERORISME','KS','PENGANIAYAAN BERAT','NARKOTIKA','TPL','TPPU') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tambah Opsi TPPU, Revisi 3',
  `id_pegawai` int DEFAULT NULL COMMENT 'Kolom Baru Revisi4',
  `kelengkapan_berkas` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `media_pengajuan` enum('DATANG LANGSUNG','WA','EMAIL','SURAT','MPP') COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Tambah Opsi Revisi4',
  `link_berkas_permohonan` text COLLATE utf8mb4_general_ci,
  `jenis_perlindungan` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kab_kot_locus` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `provinsi_pemohon` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Kolom Tambahan Revisi 3',
  `tempat_permohonan` enum('MEDAN','JAKARTA') COLLATE utf8mb4_general_ci NOT NULL,
  `kab_kota_pemohon` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Kolom Tabahanan Revisi 3',
  `provinsi` varchar(50) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Kolom Tambahan Revisi4',
  PRIMARY KEY (`no_reg_medan`),
  KEY `fk_permohonan_pegawai` (`id_pegawai`),
  CONSTRAINT `fk_permohonan_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.permohonan: ~0 rows (approximately)
INSERT INTO `permohonan` (`no_reg_medan`, `nama_pemohon`, `jenis_kelamin`, `status_hukum`, `tgl_pengajuan`, `pihak_perwakilan`, `tindak_pidana`, `id_pegawai`, `kelengkapan_berkas`, `media_pengajuan`, `link_berkas_permohonan`, `jenis_perlindungan`, `kab_kot_locus`, `provinsi_pemohon`, `tempat_permohonan`, `kab_kota_pemohon`, `provinsi`) VALUES
	('REG-MDN-2025-001', 'OKTOMA', '', 'Saksi', '2025-08-26', 'KELUARGA', 'PENGANIAYAAN BERAT', 2, 'LENGKAP', '', 'https://limo.lpsk.go.id/apps/files/files', 'PHP', 'binjai', 'Sumatera utara', 'MEDAN', 'sidikalang', 'SUMATERA UTARA'),
	('REG-MDN-2025-002', 'erikson', '', 'Saksi', '2025-08-26', 'KELUARGA', 'KS', 1, 'LENGKAP', '', 'https://limo.lpsk.go.id/apps/files/files', 'PHP', 'MEDAN', 'Sumatera utara', 'MEDAN', 'sidikalang', 'SUMATERA UTARA');

-- Dumping structure for table db_lpsk.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `sid` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `sess` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `expired` datetime NOT NULL,
  PRIMARY KEY (`sid`),
  KEY `sessions_expired_index` (`expired`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.sessions: ~2 rows (approximately)
INSERT INTO `sessions` (`sid`, `sess`, `expired`) VALUES
	('Ffy9ahVTCq5ygIXQ_Zq-auE__eYVrwCr', '{"cookie":{"originalMaxAge":28800000,"expires":"2025-08-15T11:08:23.832Z","secure":false,"httpOnly":true,"path":"/","sameSite":"lax"},"csrfSecret":"4SDFoSc30GZ2vnH9jKCmwx4F"}', '2025-08-15 16:30:21'),
	('FUuFRzhek5dWRzqtTy2LMOsyr6Q5idVr', '{"cookie":{"originalMaxAge":28800000,"expires":"2025-08-15T01:59:28.379Z","secure":false,"httpOnly":true,"path":"/","sameSite":"lax"},"csrfSecret":"wxl5q6aYRjD_UGI2SKU8L-0c","user":{"id_user":1,"username":"admin","nama_lengkap":"Administrator","role":"admin"}}', '2025-08-15 09:50:55');

-- Dumping structure for table db_lpsk.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table db_lpsk.users: ~2 rows (approximately)
INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `role`) VALUES
	(1, 'admin', '$2b$12$k0.VMroQsaoi2irwiL69ae86IbGN.W0MlF7.ZA/JI2/pfrhkSLgx2', 'Administrator', 'admin@example.com', 'admin'),
	(2, 'erik', '$2y$12$522sTyanYbUg25iLUjDh9O7tAnwrrJI96Dt9im4/b.XSefykWdpNC', 'erikson', 'eriksontobing28@gmail.com', 'user');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
