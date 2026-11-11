-- ============================================================
-- Izin Keluar SMKN 5 Surakarta — schema.sql (MySQL 8)
-- Dibuat dari database/migrations (Laravel 12).
-- Cara pakai (pilih SALAH SATU):
--   A) mysql -u root izin_keluar_smkn5 < schema.sql
--      lalu: php artisan db:seed
--   B) php artisan migrate --seed   (cara utama, tanpa file ini)
-- Jangan jalankan migrate setelah import file ini bila tabel
-- `migrations` sudah terisi dari dump.
-- ============================================================

CREATE DATABASE IF NOT EXISTS `izin_keluar_smkn5`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `izin_keluar_smkn5`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `email_verified_at` TIMESTAMP NULL DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jurusan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(10) NOT NULL,
  `nama` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `jurusan_kode_unique` (`kode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guru` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(255) NOT NULL,
  `nip` VARCHAR(30) NULL DEFAULT NULL,
  `no_hp` VARCHAR(20) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guru_nip_unique` (`nip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `kelas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `jurusan_id` BIGINT UNSIGNED NOT NULL,
  `tingkat` ENUM('X','XI','XII','XIII') NOT NULL,
  `nama` VARCHAR(255) NOT NULL,
  `wali_kelas_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kelas_jurusan_id_tingkat_nama_unique` (`jurusan_id`,`tingkat`,`nama`),
  CONSTRAINT `kelas_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kelas_wali_kelas_id_foreign` FOREIGN KEY (`wali_kelas_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mapel` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(255) NOT NULL,
  `kode` VARCHAR(20) NULL DEFAULT NULL,
  `kelompok` VARCHAR(5) NULL DEFAULT NULL,
  `jurusan_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `mapel_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guru_mapel` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `guru_id` BIGINT UNSIGNED NOT NULL,
  `mapel_id` BIGINT UNSIGNED NOT NULL,
  `kelas_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `jurusan_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guru_mapel_unique` (`guru_id`,`mapel_id`,`kelas_id`),
  CONSTRAINT `guru_mapel_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guru_mapel_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guru_mapel_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `guru_mapel_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `guru_token` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `guru_id` BIGINT UNSIGNED NOT NULL,
  `token` VARCHAR(10) NOT NULL,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `dibuat_oleh` BIGINT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `guru_token_guru_id_token_unique` (`guru_id`,`token`),
  CONSTRAINT `guru_token_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE CASCADE,
  CONSTRAINT `guru_token_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permohonan_izin` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(30) NOT NULL,
  `nama_siswa` VARCHAR(255) NOT NULL,
  `nis` VARCHAR(30) NOT NULL,
  `no_absen` VARCHAR(10) NULL DEFAULT NULL,
  `kelas_id` BIGINT UNSIGNED NOT NULL,
  `jurusan_id` BIGINT UNSIGNED NOT NULL,
  `keperluan` TEXT NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_ke_mulai` TINYINT UNSIGNED NOT NULL,
  `jam_ke_selesai` TINYINT UNSIGNED NOT NULL,
  `kendaraan` ENUM('Sepeda Motor','Mobil','Sepeda','Jalan Kaki','Antar Jemput','Lainnya') NOT NULL,
  `cara_pulang` ENUM('dijemput','pulang_sendiri') NOT NULL,
  `nama_penjemput` VARCHAR(255) NULL DEFAULT NULL,
  `status` ENUM('menunggu','disetujui_sebagian','disetujui_semua') NOT NULL DEFAULT 'menunggu',
  `barcode_kode` VARCHAR(32) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permohonan_izin_kode_unique` (`kode`),
  UNIQUE KEY `permohonan_izin_barcode_kode_unique` (`barcode_kode`),
  INDEX `permohonan_izin_tanggal_index` (`tanggal`),
  INDEX `permohonan_izin_status_index` (`status`),
  CONSTRAINT `permohonan_izin_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`),
  CONSTRAINT `permohonan_izin_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permohonan_item` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_id` BIGINT UNSIGNED NOT NULL,
  `mapel_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `guru_id` BIGINT UNSIGNED NOT NULL,
  `peran` ENUM('guru_mapel','wali_kelas') NOT NULL DEFAULT 'guru_mapel',
  `status` ENUM('menunggu','disetujui') NOT NULL DEFAULT 'menunggu',
  `disetujui_pada` TIMESTAMP NULL DEFAULT NULL,
  `catatan` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `permohonan_item_permohonan_id_foreign` FOREIGN KEY (`permohonan_id`) REFERENCES `permohonan_izin` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permohonan_item_mapel_id_foreign` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permohonan_item_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `persetujuan` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `permohonan_item_id` BIGINT UNSIGNED NOT NULL,
  `guru_id` BIGINT UNSIGNED NOT NULL,
  `token_digunakan` VARCHAR(10) NOT NULL,
  `disetujui_pada` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `persetujuan_permohonan_item_id_foreign` FOREIGN KEY (`permohonan_item_id`) REFERENCES `permohonan_item` (`id`) ON DELETE CASCADE,
  CONSTRAINT `persetujuan_guru_id_foreign` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
