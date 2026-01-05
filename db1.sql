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
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `proses_hukum` enum('Penyelidikan','Penyidikan','P-19','P-21','P-22','Penuntutan','Putusan Pengadilan Negeri','Putusan Pengadilan Tinggi','Tidak Ada Proses Hukum','SP3 Lidik','SP3 Sidik','Dakwaan','Pemeriksaan Saksi','Pledoi','Putusan Mahkamah Agung','Inkracht') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=1757 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

-- --------------------------------------------------------
-- Audit Trail Implementation
-- --------------------------------------------------------

-- Sructure for table audit_logs
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `record_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Taken from @audit_user_id session variable',
  `user_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Taken from @audit_user_name',
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `changed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_table` (`table_name`),
  KEY `idx_audit_action` (`action`),
  KEY `idx_audit_record` (`record_id`),
  KEY `idx_audit_date` (`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELIMITER //

-- 1. Triggers for USERS
CREATE TRIGGER `audit_users_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, new_values)
    VALUES ('users', 'INSERT', NEW.id_user, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('id_user', NEW.id_user, 'username', NEW.username, 'nama_lengkap', NEW.nama_lengkap, 'email', NEW.email, 'role', NEW.role));
END//

CREATE TRIGGER `audit_users_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values, new_values)
    VALUES ('users', 'UPDATE', NEW.id_user, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('id_user', OLD.id_user, 'username', OLD.username, 'nama_lengkap', OLD.nama_lengkap, 'email', OLD.email, 'role', OLD.role),
    JSON_OBJECT('id_user', NEW.id_user, 'username', NEW.username, 'nama_lengkap', NEW.nama_lengkap, 'email', NEW.email, 'role', NEW.role));
END//

CREATE TRIGGER `audit_users_delete` BEFORE DELETE ON `users` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values)
    VALUES ('users', 'DELETE', OLD.id_user, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('id_user', OLD.id_user, 'username', OLD.username, 'nama_lengkap', OLD.nama_lengkap, 'email', OLD.email, 'role', OLD.role));
END//

-- 2. Triggers for PEGAWAI
CREATE TRIGGER `audit_pegawai_insert` AFTER INSERT ON `pegawai` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, new_values)
    VALUES ('pegawai', 'INSERT', NEW.id_pegawai, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('id_pegawai', NEW.id_pegawai, 'nama_pegawai', NEW.nama_pegawai, 'jabatan', NEW.jabatan, 'unit_kerja', NEW.unit_kerja, 'email', NEW.email, 'no_telp', NEW.no_telp, 'aktif', NEW.aktif));
END//

CREATE TRIGGER `audit_pegawai_update` AFTER UPDATE ON `pegawai` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values, new_values)
    VALUES ('pegawai', 'UPDATE', NEW.id_pegawai, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('id_pegawai', OLD.id_pegawai, 'nama_pegawai', OLD.nama_pegawai, 'jabatan', OLD.jabatan, 'unit_kerja', OLD.unit_kerja, 'email', OLD.email, 'no_telp', OLD.no_telp, 'aktif', OLD.aktif),
    JSON_OBJECT('id_pegawai', NEW.id_pegawai, 'nama_pegawai', NEW.nama_pegawai, 'jabatan', NEW.jabatan, 'unit_kerja', NEW.unit_kerja, 'email', NEW.email, 'no_telp', NEW.no_telp, 'aktif', NEW.aktif));
END//

CREATE TRIGGER `audit_pegawai_delete` BEFORE DELETE ON `pegawai` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values)
    VALUES ('pegawai', 'DELETE', OLD.id_pegawai, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('id_pegawai', OLD.id_pegawai, 'nama_pegawai', OLD.nama_pegawai, 'jabatan', OLD.jabatan, 'unit_kerja', OLD.unit_kerja, 'email', OLD.email, 'no_telp', OLD.no_telp, 'aktif', OLD.aktif));
END//

-- 3. Triggers for PERMOHONAN
CREATE TRIGGER `audit_permohonan_insert` AFTER INSERT ON `permohonan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, new_values)
    VALUES ('permohonan', 'INSERT', NEW.no_reg_medan, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_reg_medan', NEW.no_reg_medan, 'nama_pemohon', NEW.nama_pemohon, 'status_hukum', NEW.status_hukum, 'tgl_pengajuan', NEW.tgl_pengajuan, 'tindak_pidana', NEW.tindak_pidana, 'id_pegawai', NEW.id_pegawai, 'media_pengajuan', NEW.media_pengajuan, 'atensi', NEW.atensi));
END//

CREATE TRIGGER `audit_permohonan_update` AFTER UPDATE ON `permohonan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values, new_values)
    VALUES ('permohonan', 'UPDATE', NEW.no_reg_medan, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_reg_medan', OLD.no_reg_medan, 'nama_pemohon', OLD.nama_pemohon, 'status_hukum', OLD.status_hukum, 'tgl_pengajuan', OLD.tgl_pengajuan, 'tindak_pidana', OLD.tindak_pidana, 'id_pegawai', OLD.id_pegawai, 'media_pengajuan', OLD.media_pengajuan, 'atensi', OLD.atensi),
    JSON_OBJECT('no_reg_medan', NEW.no_reg_medan, 'nama_pemohon', NEW.nama_pemohon, 'status_hukum', NEW.status_hukum, 'tgl_pengajuan', NEW.tgl_pengajuan, 'tindak_pidana', NEW.tindak_pidana, 'id_pegawai', NEW.id_pegawai, 'media_pengajuan', NEW.media_pengajuan, 'atensi', NEW.atensi));
END//

CREATE TRIGGER `audit_permohonan_delete` BEFORE DELETE ON `permohonan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values)
    VALUES ('permohonan', 'DELETE', OLD.no_reg_medan, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_reg_medan', OLD.no_reg_medan, 'nama_pemohon', OLD.nama_pemohon, 'status_hukum', OLD.status_hukum, 'tgl_pengajuan', OLD.tgl_pengajuan, 'tindak_pidana', OLD.tindak_pidana, 'id_pegawai', OLD.id_pegawai, 'media_pengajuan', OLD.media_pengajuan, 'atensi', OLD.atensi));
END//

-- 4. Triggers for PENELAAHAN
CREATE TRIGGER `audit_penelaahan_insert` AFTER INSERT ON `penelaahan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, new_values)
    VALUES ('penelaahan', 'INSERT', NEW.no_registrasi, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_registrasi', NEW.no_registrasi, 'no_reg_medan', NEW.no_reg_medan, 'proses_hukum', NEW.proses_hukum, 'tanggal_dispo', NEW.tanggal_dispo, 'id_pegawai', NEW.id_pegawai, 'nama_ta_penalaahan', NEW.nama_ta_penalaahan, 'risalah_laporan', NEW.risalah_laporan));
END//

CREATE TRIGGER `audit_penelaahan_update` AFTER UPDATE ON `penelaahan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values, new_values)
    VALUES ('penelaahan', 'UPDATE', NEW.no_registrasi, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_registrasi', OLD.no_registrasi, 'no_reg_medan', OLD.no_reg_medan, 'proses_hukum', OLD.proses_hukum, 'tanggal_dispo', OLD.tanggal_dispo, 'id_pegawai', OLD.id_pegawai, 'nama_ta_penalaahan', OLD.nama_ta_penalaahan, 'risalah_laporan', OLD.risalah_laporan),
    JSON_OBJECT('no_registrasi', NEW.no_registrasi, 'no_reg_medan', NEW.no_reg_medan, 'proses_hukum', NEW.proses_hukum, 'tanggal_dispo', NEW.tanggal_dispo, 'id_pegawai', NEW.id_pegawai, 'nama_ta_penalaahan', NEW.nama_ta_penalaahan, 'risalah_laporan', NEW.risalah_laporan));
END//

CREATE TRIGGER `audit_penelaahan_delete` BEFORE DELETE ON `penelaahan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values)
    VALUES ('penelaahan', 'DELETE', OLD.no_registrasi, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_registrasi', OLD.no_registrasi, 'no_reg_medan', OLD.no_reg_medan, 'proses_hukum', OLD.proses_hukum, 'tanggal_dispo', OLD.tanggal_dispo, 'id_pegawai', OLD.id_pegawai, 'nama_ta_penalaahan', OLD.nama_ta_penalaahan, 'risalah_laporan', OLD.risalah_laporan));
END//

-- 5. Triggers for LAYANAN
CREATE TRIGGER `audit_layanan_insert` AFTER INSERT ON `layanan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, new_values)
    VALUES ('layanan', 'INSERT', NEW.no_kep_smpl, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_kep_smpl', NEW.no_kep_smpl, 'no_reg_medan', NEW.no_reg_medan, 'no_registrasi', NEW.no_registrasi, 'nama_terlindung', NEW.nama_terlindung, 'jenis_tindak_pidana', NEW.jenis_tindak_pidana, 'id_pegawai', NEW.id_pegawai, 'tgl_mulai_layanan', NEW.tgl_mulai_layanan, 'masa_layanan', NEW.masa_layanan, 'status', NEW.status));
END//

CREATE TRIGGER `audit_layanan_update` AFTER UPDATE ON `layanan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values, new_values)
    VALUES ('layanan', 'UPDATE', NEW.no_kep_smpl, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_kep_smpl', OLD.no_kep_smpl, 'no_reg_medan', OLD.no_reg_medan, 'no_registrasi', OLD.no_registrasi, 'nama_terlindung', OLD.nama_terlindung, 'jenis_tindak_pidana', OLD.jenis_tindak_pidana, 'id_pegawai', OLD.id_pegawai, 'tgl_mulai_layanan', OLD.tgl_mulai_layanan, 'masa_layanan', OLD.masa_layanan, 'status', OLD.status),
    JSON_OBJECT('no_kep_smpl', NEW.no_kep_smpl, 'no_reg_medan', NEW.no_reg_medan, 'no_registrasi', NEW.no_registrasi, 'nama_terlindung', NEW.nama_terlindung, 'jenis_tindak_pidana', NEW.jenis_tindak_pidana, 'id_pegawai', NEW.id_pegawai, 'tgl_mulai_layanan', NEW.tgl_mulai_layanan, 'masa_layanan', NEW.masa_layanan, 'status', NEW.status));
END//

CREATE TRIGGER `audit_layanan_delete` BEFORE DELETE ON `layanan` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values)
    VALUES ('layanan', 'DELETE', OLD.no_kep_smpl, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('no_kep_smpl', OLD.no_kep_smpl, 'no_reg_medan', OLD.no_reg_medan, 'no_registrasi', OLD.no_registrasi, 'nama_terlindung', OLD.nama_terlindung, 'jenis_tindak_pidana', OLD.jenis_tindak_pidana, 'id_pegawai', OLD.id_pegawai, 'tgl_mulai_layanan', OLD.tgl_mulai_layanan, 'masa_layanan', OLD.masa_layanan, 'status', OLD.status));
END//

-- 6. Triggers for PENGELUARAN
CREATE TRIGGER `audit_pengeluaran_insert` AFTER INSERT ON `pengeluaran` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, new_values)
    VALUES ('pengeluaran', 'INSERT', NEW.nomor_kuintasi, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('nomor_kuintasi', NEW.nomor_kuintasi, 'jumlah', NEW.jumlah, 'tanggal', NEW.tanggal, 'kode_mak', NEW.kode_mak, 'kode_anggaran', NEW.kode_anggaran, 'id_pegawai', NEW.id_pegawai));
END//

CREATE TRIGGER `audit_pengeluaran_update` AFTER UPDATE ON `pengeluaran` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values, new_values)
    VALUES ('pengeluaran', 'UPDATE', NEW.nomor_kuintasi, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('nomor_kuintasi', OLD.nomor_kuintasi, 'jumlah', OLD.jumlah, 'tanggal', OLD.tanggal, 'kode_mak', OLD.kode_mak, 'kode_anggaran', OLD.kode_anggaran, 'id_pegawai', OLD.id_pegawai),
    JSON_OBJECT('nomor_kuintasi', NEW.nomor_kuintasi, 'jumlah', NEW.jumlah, 'tanggal', NEW.tanggal, 'kode_mak', NEW.kode_mak, 'kode_anggaran', NEW.kode_anggaran, 'id_pegawai', NEW.id_pegawai));
END//

CREATE TRIGGER `audit_pengeluaran_delete` BEFORE DELETE ON `pengeluaran` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values)
    VALUES ('pengeluaran', 'DELETE', OLD.nomor_kuintasi, @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('nomor_kuintasi', OLD.nomor_kuintasi, 'jumlah', OLD.jumlah, 'tanggal', OLD.tanggal, 'kode_mak', OLD.kode_mak, 'kode_anggaran', OLD.kode_anggaran, 'id_pegawai', OLD.id_pegawai));
END//

-- 7. Triggers for ANGGARAN
CREATE TRIGGER `audit_anggaran_insert` AFTER INSERT ON `anggaran` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, new_values)
    VALUES ('anggaran', 'INSERT', CONCAT(NEW.kode_anggaran, '-', NEW.tahun), @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('kode_anggaran', NEW.kode_anggaran, 'nama_anggaran', NEW.nama_anggaran, 'total_anggaran', NEW.total_anggaran, 'tahun', NEW.tahun));
END//

CREATE TRIGGER `audit_anggaran_update` AFTER UPDATE ON `anggaran` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values, new_values)
    VALUES ('anggaran', 'UPDATE', CONCAT(NEW.kode_anggaran, '-', NEW.tahun), @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('kode_anggaran', OLD.kode_anggaran, 'nama_anggaran', OLD.nama_anggaran, 'total_anggaran', OLD.total_anggaran, 'tahun', OLD.tahun),
    JSON_OBJECT('kode_anggaran', NEW.kode_anggaran, 'nama_anggaran', NEW.nama_anggaran, 'total_anggaran', NEW.total_anggaran, 'tahun', NEW.tahun));
END//

CREATE TRIGGER `audit_anggaran_delete` BEFORE DELETE ON `anggaran` FOR EACH ROW BEGIN
    INSERT INTO audit_logs (table_name, action, record_id, user_id, user_name, old_values)
    VALUES ('anggaran', 'DELETE', CONCAT(OLD.kode_anggaran, '-', OLD.tahun), @audit_user_id, IFNULL(@audit_user_name, CURRENT_USER()), 
    JSON_OBJECT('kode_anggaran', OLD.kode_anggaran, 'nama_anggaran', OLD.nama_anggaran, 'total_anggaran', OLD.total_anggaran, 'tahun', OLD.tahun));
END//

DELIMITER ;

/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
