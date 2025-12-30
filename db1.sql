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

-- Dumping structure for table lpskbckp.anggaran
CREATE TABLE IF NOT EXISTS `anggaran` (
  `kode_anggaran` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_anggaran` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_anggaran` decimal(15,2) NOT NULL,
  `tahun` year NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`kode_anggaran`,`tahun`),
  KEY `tahun` (`tahun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.jenis_perlindungan
CREATE TABLE IF NOT EXISTS `jenis_perlindungan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `sub_pilihan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.layanan
CREATE TABLE IF NOT EXISTS `layanan` (
  `no_kep_smpl` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_reg_medan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_registrasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_spk` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_no_kep_smpl` date NOT NULL,
  `status_spk` enum('Sudah TTD','Belum TTD') COLLATE utf8mb4_unicode_ci DEFAULT 'Belum TTD',
  `nama_terlindung` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_tindak_pidana` enum('KSA','PENYIKSAAN','KORUPSI','TPPO','PHB','TERORISME','KS','PENGANIAYAAN BERAT','NARKOTIKA','TPL','TPPU','PENGANIAYAAN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_disposisi` date DEFAULT NULL,
  `id_pegawai` int NOT NULL,
  `tgl_mulai_layanan` date NOT NULL,
  `masa_layanan` enum('3 BULAN','6 BULAN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tambahan_masa_layanan` enum('3 BULAN','6 BULAN') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_berakhir_layanan` date DEFAULT NULL,
  `jenis_perlindungan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wilayah_hukum` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ta_layanan` enum('AM','AJC','RW','TP','SMW') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('BERJALAN','DIHENTIKAN','PERPANJANGAN') COLLATE utf8mb4_unicode_ci DEFAULT 'BERJALAN',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `atensi` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`no_kep_smpl`),
  KEY `no_reg_medan` (`no_reg_medan`),
  KEY `no_registrasi` (`no_registrasi`),
  KEY `id_pegawai` (`id_pegawai`),
  KEY `idx_layanan_tgl_mulai` (`tgl_mulai_layanan`),
  KEY `idx_layanan_tgl_berakhir` (`tgl_berakhir_layanan`),
  KEY `idx_layanan_status` (`status`),
  CONSTRAINT `layanan_ibfk_1` FOREIGN KEY (`no_reg_medan`) REFERENCES `permohonan` (`no_reg_medan`) ON DELETE CASCADE,
  CONSTRAINT `layanan_ibfk_2` FOREIGN KEY (`no_registrasi`) REFERENCES `penelaahan` (`no_registrasi`) ON DELETE CASCADE,
  CONSTRAINT `layanan_ibfk_3` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.layanan_perlindungan
CREATE TABLE IF NOT EXISTS `layanan_perlindungan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_kep_smpl` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_perlindungan` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `no_kep_smpl` (`no_kep_smpl`),
  KEY `id_perlindungan` (`id_perlindungan`),
  CONSTRAINT `layanan_perlindungan_ibfk_1` FOREIGN KEY (`no_kep_smpl`) REFERENCES `layanan` (`no_kep_smpl`) ON DELETE CASCADE,
  CONSTRAINT `layanan_perlindungan_ibfk_2` FOREIGN KEY (`id_perlindungan`) REFERENCES `jenis_perlindungan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.mak
CREATE TABLE IF NOT EXISTS `mak` (
  `kode_mak` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_mak` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`kode_mak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.pegawai
CREATE TABLE IF NOT EXISTS `pegawai` (
  `id_pegawai` int NOT NULL AUTO_INCREMENT,
  `nama_pegawai` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_kerja` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pegawai`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.penelaahan
CREATE TABLE IF NOT EXISTS `penelaahan` (
  `no_registrasi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_reg_medan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proses_hukum` enum('Penyelidikan','Penyidikan','P-19','P-21','P-22','Penuntutan','Putusan Pengadilan Negeri','Putusan Pengadilan Tinggi','Tidak Ada Proses Hukum','SP3 Lidik','SP3 Sidik','Dakwaan','Pemeriksaan Saksi','Pledoi','Putusan Mahkamah Agung') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_dispo` date NOT NULL,
  `proses_penalaahan` text COLLATE utf8mb4_unicode_ci,
  `id_pegawai` int NOT NULL,
  `tgl_berakhir_penelaahan` date DEFAULT NULL,
  `waktu_tambahan` enum('15 HARI') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ta_penalaahan` enum('YM','MBF','GPJ','IM') COLLATE utf8mb4_unicode_ci NOT NULL,
  `risalah_laporan` enum('BELUM','SUDAH') COLLATE utf8mb4_unicode_ci DEFAULT 'BELUM',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `atensi` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`no_registrasi`),
  KEY `no_reg_medan` (`no_reg_medan`),
  KEY `id_pegawai` (`id_pegawai`),
  KEY `idx_penelaahan_tanggal_dispo` (`tanggal_dispo`),
  KEY `idx_penelaahan_proses_hukum` (`proses_hukum`),
  CONSTRAINT `penelaahan_ibfk_1` FOREIGN KEY (`no_reg_medan`) REFERENCES `permohonan` (`no_reg_medan`) ON DELETE CASCADE,
  CONSTRAINT `penelaahan_ibfk_2` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.pengeluaran
CREATE TABLE IF NOT EXISTS `pengeluaran` (
  `nomor_kuintasi` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `tanggal` date NOT NULL,
  `kode_mak` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `kode_anggaran` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun` year NOT NULL,
  `pembayaran` enum('UP','LS','TUP','KKP') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_pegawai` int DEFAULT NULL,
  PRIMARY KEY (`nomor_kuintasi`),
  KEY `kode_anggaran` (`kode_anggaran`),
  KEY `kode_mak` (`kode_mak`),
  KEY `pengeluaran_fk_anggaran` (`kode_anggaran`,`tahun`),
  KEY `idx_pengeluaran_tanggal` (`tanggal`),
  KEY `idx_pengeluaran_anggaran_mak` (`kode_anggaran`,`kode_mak`),
  KEY `fk_pengeluaran_pegawai` (`id_pegawai`),
  CONSTRAINT `fk_pengeluaran_pegawai` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`) ON DELETE SET NULL,
  CONSTRAINT `pengeluaran_fk_anggaran` FOREIGN KEY (`kode_anggaran`, `tahun`) REFERENCES `anggaran` (`kode_anggaran`, `tahun`),
  CONSTRAINT `pengeluaran_fk_mak` FOREIGN KEY (`kode_mak`) REFERENCES `mak` (`kode_mak`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.permohonan
CREATE TABLE IF NOT EXISTS `permohonan` (
  `no_reg_medan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pemohon` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_hukum` enum('Saksi','Korban','Ahli','Pelapor','Saksi Pelaku','Anak Korban','Terlapor','Tersangka','Terdakwa','Tidak Ada Status Hukum') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_pengajuan` date NOT NULL,
  `pihak_perwakilan` enum('KELUARGA','APH','INSTANSI PEMERINTAH','DIRI SENDIRI','DLL') COLLATE utf8mb4_unicode_ci NOT NULL,
  `tindak_pidana` enum('KSA','PENYIKSAAN','KORUPSI','TPPO','PHB','TERORISME','KS','PENGANIAYAAN BERAT','NARKOTIKA','TPL','TPPU','PENGANIAYAAN') COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_pegawai` int NOT NULL,
  `kelengkapan_berkas` text COLLATE utf8mb4_unicode_ci,
  `media_pengajuan` enum('DATANG LANGSUNG','WA','EMAIL','SURAT','MPP','Pro Aktif') COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_berkas_permohonan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_perlindungan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kab_kot_locus` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kab_kota_pemohon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provinsi_pemohon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_permohonan` enum('MEDAN','JAKARTA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `atensi` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`no_reg_medan`),
  KEY `id_pegawai` (`id_pegawai`),
  KEY `idx_permohonan_tgl_pengajuan` (`tgl_pengajuan`),
  KEY `idx_permohonan_status_hukum` (`status_hukum`),
  KEY `idx_permohonan_tindak_pidana` (`tindak_pidana`),
  CONSTRAINT `permohonan_ibfk_1` FOREIGN KEY (`id_pegawai`) REFERENCES `pegawai` (`id_pegawai`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.permohonan_perlindungan
CREATE TABLE IF NOT EXISTS `permohonan_perlindungan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_reg_medan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_perlindungan` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `no_reg_medan` (`no_reg_medan`),
  KEY `id_perlindungan` (`id_perlindungan`),
  CONSTRAINT `permohonan_perlindungan_ibfk_1` FOREIGN KEY (`no_reg_medan`) REFERENCES `permohonan` (`no_reg_medan`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_perlindungan_ibfk_2` FOREIGN KEY (`id_perlindungan`) REFERENCES `jenis_perlindungan` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1753 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for table lpskbckp.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

-- Dumping structure for trigger lpskbckp.hitung_tgl_berakhir
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `hitung_tgl_berakhir` BEFORE INSERT ON `layanan` FOR EACH ROW BEGIN
    DECLARE lama INT DEFAULT 0;
    DECLARE tambahan INT DEFAULT 0;

    -- Konversi masa_layanan
    IF NEW.masa_layanan = '3 BULAN' THEN
        SET lama = 3;
    ELSEIF NEW.masa_layanan = '6 BULAN' THEN
        SET lama = 6;
    END IF;

    -- Konversi tambahan_masa_layanan
    IF NEW.tambahan_masa_layanan = '3 BULAN' THEN
        SET tambahan = 3;
    ELSEIF NEW.tambahan_masa_layanan = '6 BULAN' THEN
        SET tambahan = 6;
    END IF;

    -- Hitung tanggal berakhir
    SET NEW.tgl_berakhir_layanan = DATE_ADD(NEW.tgl_mulai_layanan, INTERVAL (lama + tambahan) MONTH);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lpskbckp.update_tgl_berakhir
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `update_tgl_berakhir` BEFORE UPDATE ON `layanan` FOR EACH ROW BEGIN
    DECLARE lama INT DEFAULT 0;
    DECLARE tambahan INT DEFAULT 0;

    IF NEW.masa_layanan = '3 BULAN' THEN
        SET lama = 3;
    ELSEIF NEW.masa_layanan = '6 BULAN' THEN
        SET lama = 6;
    END IF;

    IF NEW.tambahan_masa_layanan = '3 BULAN' THEN
        SET tambahan = 3;
    ELSEIF NEW.tambahan_masa_layanan = '6 BULAN' THEN
        SET tambahan = 6;
    END IF;

    SET NEW.tgl_berakhir_layanan = DATE_ADD(NEW.tgl_mulai_layanan, INTERVAL (lama + tambahan) MONTH);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
