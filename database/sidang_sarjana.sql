/*M!999999\- enable the sandbox mode */ 

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `catatan_pengajuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `catatan_pengajuan` (
  `id_catatan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int(10) unsigned NOT NULL,
  `id_user` int(10) unsigned NOT NULL,
  `isi_catatan` text NOT NULL,
  `terlihat_mahasiswa` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_catatan`),
  KEY `catatan_pengajuan_id_pengajuan_foreign` (`id_pengajuan`),
  KEY `catatan_pengajuan_id_user_foreign` (`id_user`),
  CONSTRAINT `catatan_pengajuan_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE,
  CONSTRAINT `catatan_pengajuan_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `catatan_pengajuan` WRITE;
/*!40000 ALTER TABLE `catatan_pengajuan` DISABLE KEYS */;
/*!40000 ALTER TABLE `catatan_pengajuan` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `dokumen_pengajuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dokumen_pengajuan` (
  `id_dokumen` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_pengajuan` int(10) unsigned NOT NULL,
  `id_jenis` int(10) unsigned NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `ukuran_kb` int(10) unsigned NOT NULL,
  `status_dokumen` enum('menunggu','lengkap','perlu_perbaikan') NOT NULL DEFAULT 'menunggu',
  `catatan_pemeriksaan` text DEFAULT NULL,
  `uploaded_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_dokumen`),
  UNIQUE KEY `dokumen_pengajuan_id_pengajuan_id_jenis_unique` (`id_pengajuan`,`id_jenis`),
  KEY `dokumen_pengajuan_id_jenis_foreign` (`id_jenis`),
  CONSTRAINT `dokumen_pengajuan_id_jenis_foreign` FOREIGN KEY (`id_jenis`) REFERENCES `jenis_dokumen` (`id_jenis`),
  CONSTRAINT `dokumen_pengajuan_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `dokumen_pengajuan` WRITE;
/*!40000 ALTER TABLE `dokumen_pengajuan` DISABLE KEYS */;
/*!40000 ALTER TABLE `dokumen_pengajuan` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `jenis_dokumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jenis_dokumen` (
  `id_jenis` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_dokumen` varchar(100) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `wajib_skripsi` tinyint(1) NOT NULL DEFAULT 1,
  `wajib_kompre` tinyint(1) NOT NULL DEFAULT 1,
  `khusus_transfer` tinyint(1) NOT NULL DEFAULT 0,
  `format_file` varchar(30) NOT NULL DEFAULT 'PDF',
  `ukuran_maks_mb` tinyint(3) unsigned NOT NULL DEFAULT 2,
  `urutan` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_jenis`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `jenis_dokumen` WRITE;
/*!40000 ALTER TABLE `jenis_dokumen` DISABLE KEYS */;
INSERT INTO `jenis_dokumen` VALUES
(1,'KRS','Unggah KRS aktif PTA 2026/2027 semester berjalan.',1,1,0,'PDF',2,1),
(2,'Workshop','Unggah 1 sertifikat workshop.',1,1,0,'PDF',2,2),
(3,'Kursus','Unggah seluruh sertifikat kursus dalam satu PDF.',1,1,0,'PDF',5,3),
(4,'Manual Book','Unggah manual book dalam bentuk PDF.',1,0,0,'PDF',10,4),
(5,'Berkas Tambahan','Khusus mahasiswa transfer. Gabungkan pengesahan penyetaraan PI yang sudah ditandatangani, ijazah, konversi nilai, dan transkrip D3 dalam satu PDF.',1,1,1,'PDF',10,5);
/*!40000 ALTER TABLE `jenis_dokumen` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `kategori_hasil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori_hasil` (
  `id_kategori` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(3) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `teks_notifikasi` text NOT NULL,
  `id_user` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_kategori`),
  UNIQUE KEY `kategori_hasil_kode_unique` (`kode`),
  KEY `kategori_hasil_id_user_foreign` (`id_user`),
  CONSTRAINT `kategori_hasil_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `kategori_hasil` WRITE;
/*!40000 ALTER TABLE `kategori_hasil` DISABLE KEYS */;
INSERT INTO `kategori_hasil` VALUES
(1,'1','Bisa Daftar Sidang','Berkas Anda telah diterima. Silakan melanjutkan pendaftaran sidang sesuai jadwal prodi.',1,'2026-11-24 09:07:10'),
(2,'2a','Kendala Pembayaran','Terdapat kendala pembayaran. Mohon selesaikan pembayaran lalu hubungi bagian keuangan.',1,'2026-11-24 09:07:10'),
(3,'2b','Kendala Berkas','Mohon perbaiki dokumen yang ditandai agar verifikasi dapat dilanjutkan.',1,'2026-11-24 09:07:10'),
(4,'3','Kendala Nilai','Terdapat nilai yang belum lengkap. Mohon lengkapi nilai mata kuliah wajib terlebih dahulu.',1,'2026-11-24 09:07:10');
/*!40000 ALTER TABLE `kategori_hasil` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `konten_landing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `konten_landing` (
  `id_konten` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bagian` enum('alur','faq','informasi') NOT NULL,
  `judul` varchar(150) NOT NULL,
  `isi` text NOT NULL,
  `urutan` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `is_tampil` tinyint(1) NOT NULL DEFAULT 1,
  `id_user` int(10) unsigned DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_konten`),
  KEY `konten_landing_id_user_foreign` (`id_user`),
  CONSTRAINT `konten_landing_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `konten_landing` WRITE;
/*!40000 ALTER TABLE `konten_landing` DISABLE KEYS */;
INSERT INTO `konten_landing` VALUES
(1,'informasi','hero_label','LAYANAN AKADEMIK • SIDANG SARJANA',1,1,1,'2026-11-24 09:07:10'),
(2,'informasi','hero_judul_1','Pendaftaran',2,1,1,'2026-11-24 09:07:10'),
(3,'informasi','hero_judul_2','Sidang Sarjana.',3,1,1,'2026-11-24 09:07:10'),
(4,'informasi','hero_deskripsi','Ajukan berkas sidang sarjana Program Studi Sistem Informasi secara daring, pantau status pemeriksaan, dan perbaiki berkas tanpa mengulang dari awal.',4,1,1,'2026-11-24 09:07:10'),
(5,'informasi','hero_tombol','Daftar Sidang',5,1,1,'2026-11-24 09:07:10'),
(6,'informasi','kontak_alamat','Kampus D: Jl. Margonda Raya No. 100, Pondok Cina, Depok 16424',6,1,1,'2026-11-24 09:07:10'),
(7,'informasi','kontak_email','sidang@gunadarma.ac.id',7,1,1,'2026-11-24 09:07:10'),
(8,'informasi','kontak_telepon','(021) 78881112',8,1,1,'2026-11-24 09:07:10'),
(9,'alur','Daftar dan masuk ke akun','Buat akun dengan email aktif, verifikasi kode OTP, lalu lengkapi profil.',1,1,1,'2026-11-24 09:07:10'),
(10,'alur','Isi formulir & unggah PDF','Isi data mahasiswa dan data sidang, lalu unggah dokumen persyaratan.',2,1,1,'2026-11-24 09:07:10'),
(11,'alur','Petugas memeriksa berkas','Admin prodi memeriksa kelengkapan dan kesesuaian dokumen.',3,1,1,'2026-11-24 09:07:10'),
(12,'alur','Pantau hasil & catatan pemeriksaan','Lihat status, kategori hasil, dan perbaiki dokumen yang ditandai.',4,1,1,'2026-11-24 09:07:10'),
(13,'faq','Apa saja berkas yang perlu diunggah?','Unggah KRS aktif semester berjalan, unggah 1 sertifikat workshop, dan unggah seluruh sertifikat kursus dalam satu PDF.',1,1,1,'2026-11-24 09:07:10'),
(14,'faq','Apa persyaratan mahasiswa transfer?','Khusus mahasiswa transfer mengisi nama dosen pembimbing Penulisan Ilmiah beserta gelar.',2,1,1,'2026-11-24 09:07:10'),
(15,'faq','Apa pilihan paket sidang?','Paket yang tersedia adalah Skripsi dan Komprehensif.\nUntuk Komprehensif, mahasiswa wajib memilih materi sidang yang disediakan operator.',3,1,1,'2026-11-24 09:07:10'),
(16,'faq','Apa arti status pengajuan?','Menunggu: dalam antrean atau terdapat kendala nilai yang belum tersedia.\nDiproses: berkas sedang diperiksa.\nPerbaikan: mahasiswa perlu melengkapi atau memperbaiki berkas.\nSelesai: berkas telah diterima.',4,1,1,'2026-11-24 09:07:10'),
(17,'faq','Bagaimana jika ada berkas yang salah?','Buka Status Pengajuan, pilih Dokumen, lalu periksa catatan petugas dan unggah ulang berkas yang ditandai Perlu Perbaikan.',5,1,1,'2026-11-24 09:07:10'),
(18,'faq','Format dan ukuran berkas apa yang diterima?','Gabungkan sertifikat kursus dan berkas tambahan masing-masing dalam satu PDF. Ukuran maksimal tertera pada formulir.',6,1,1,'2026-11-24 09:07:10');
/*!40000 ALTER TABLE `konten_landing` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `mahasiswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `npm` varchar(10) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `fakultas` varchar(100) DEFAULT NULL,
  `program_studi` varchar(100) DEFAULT NULL,
  `angkatan` smallint(5) unsigned DEFAULT NULL,
  `status_akun` enum('aktif','dilaporkan','arsip') NOT NULL DEFAULT 'aktif',
  `diarsipkan_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_mahasiswa`),
  UNIQUE KEY `mahasiswa_id_user_unique` (`id_user`),
  UNIQUE KEY `mahasiswa_npm_unique` (`npm`),
  CONSTRAINT `mahasiswa_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `mahasiswa` WRITE;
/*!40000 ALTER TABLE `mahasiswa` DISABLE KEYS */;
/*!40000 ALTER TABLE `mahasiswa` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `materi_kompre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `materi_kompre` (
  `id_materi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_paket` varchar(50) NOT NULL,
  `mata_kuliah_1` varchar(150) NOT NULL,
  `mata_kuliah_2` varchar(150) NOT NULL,
  `mata_kuliah_3` varchar(150) NOT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_materi`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `materi_kompre` WRITE;
/*!40000 ALTER TABLE `materi_kompre` DISABLE KEYS */;
INSERT INTO `materi_kompre` VALUES
(1,'Paket 1','Disain dan Manajemen Jaringan Komputer','Sistem Keamanan Teknologi Informasi','Sistem Operasi',NULL,'2026-11-24 09:07:10','2026-11-24 09:07:10'),
(2,'Paket 2','Analisis dan Perancangan Sistem Informasi','Interaksi Manusia dan Komputer','Sistem Basis Data 2',NULL,'2026-11-24 09:07:10','2026-11-24 09:07:10'),
(3,'Paket 3','Pemrograman Berorientasi Objek','Struktur dan Organisasi Data','Testing dan Implementasi Sistem',NULL,'2026-11-24 09:07:10','2026-11-24 09:07:10');
/*!40000 ALTER TABLE `materi_kompre` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'2026_11_01_000001_create_sidang_tables',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `notifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifikasi` (
  `id_notifikasi` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `id_pengajuan` int(10) unsigned DEFAULT NULL,
  `jenis` enum('pengajuan','akun','periode') NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text DEFAULT NULL,
  `dibaca_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_notifikasi`),
  KEY `notifikasi_id_user_foreign` (`id_user`),
  KEY `notifikasi_id_pengajuan_foreign` (`id_pengajuan`),
  CONSTRAINT `notifikasi_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE SET NULL,
  CONSTRAINT `notifikasi_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `notifikasi` WRITE;
/*!40000 ALTER TABLE `notifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifikasi` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `otp_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `otp_codes` (
  `id_otp` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned NOT NULL,
  `tujuan` enum('registrasi','reset_password') NOT NULL,
  `kode_otp` varchar(255) NOT NULL,
  `kedaluwarsa_at` datetime NOT NULL,
  `digunakan_at` datetime DEFAULT NULL,
  `percobaan` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_otp`),
  KEY `otp_codes_id_user_foreign` (`id_user`),
  CONSTRAINT `otp_codes_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `otp_codes` WRITE;
/*!40000 ALTER TABLE `otp_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `otp_codes` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `pengajuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pengajuan` (
  `id_pengajuan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `no_pengajuan` varchar(20) NOT NULL,
  `id_mahasiswa` int(10) unsigned NOT NULL,
  `id_periode` int(10) unsigned NOT NULL,
  `jalur` enum('reguler','transfer') NOT NULL,
  `paket_sidang` enum('skripsi','kompre') NOT NULL,
  `id_materi` int(10) unsigned DEFAULT NULL,
  `dosen_pembimbing_pi` varchar(100) DEFAULT NULL,
  `status_pengajuan` enum('menunggu','diproses','perbaikan','selesai') NOT NULL DEFAULT 'menunggu',
  `status_verifikasi` enum('belum','terverifikasi') NOT NULL DEFAULT 'belum',
  `id_kategori` int(10) unsigned DEFAULT NULL,
  `diajukan_at` datetime DEFAULT NULL,
  `id_admin_terakhir` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_pengajuan`),
  UNIQUE KEY `pengajuan_no_pengajuan_unique` (`no_pengajuan`),
  KEY `pengajuan_id_mahasiswa_foreign` (`id_mahasiswa`),
  KEY `pengajuan_id_periode_foreign` (`id_periode`),
  KEY `pengajuan_id_materi_foreign` (`id_materi`),
  KEY `pengajuan_id_kategori_foreign` (`id_kategori`),
  KEY `pengajuan_id_admin_terakhir_foreign` (`id_admin_terakhir`),
  CONSTRAINT `pengajuan_id_admin_terakhir_foreign` FOREIGN KEY (`id_admin_terakhir`) REFERENCES `users` (`id_user`) ON DELETE SET NULL,
  CONSTRAINT `pengajuan_id_kategori_foreign` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_hasil` (`id_kategori`) ON DELETE SET NULL,
  CONSTRAINT `pengajuan_id_mahasiswa_foreign` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  CONSTRAINT `pengajuan_id_materi_foreign` FOREIGN KEY (`id_materi`) REFERENCES `materi_kompre` (`id_materi`),
  CONSTRAINT `pengajuan_id_periode_foreign` FOREIGN KEY (`id_periode`) REFERENCES `periode_pendaftaran` (`id_periode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `pengajuan` WRITE;
/*!40000 ALTER TABLE `pengajuan` DISABLE KEYS */;
/*!40000 ALTER TABLE `pengajuan` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `periode_pendaftaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `periode_pendaftaran` (
  `id_periode` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama_periode` varchar(100) NOT NULL,
  `tanggal_buka` date NOT NULL,
  `tanggal_tutup` date NOT NULL,
  `is_dibuka` tinyint(1) NOT NULL DEFAULT 0,
  `tautan_google_form` varchar(255) DEFAULT NULL,
  `id_user` int(10) unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_periode`),
  KEY `periode_pendaftaran_id_user_foreign` (`id_user`),
  CONSTRAINT `periode_pendaftaran_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `periode_pendaftaran` WRITE;
/*!40000 ALTER TABLE `periode_pendaftaran` DISABLE KEYS */;
INSERT INTO `periode_pendaftaran` VALUES
(1,'Sidang Sarjana Periode I 2026/2027','2026-10-01','2026-12-31',1,NULL,1,'2026-11-24 09:07:10','2026-11-24 09:07:10');
/*!40000 ALTER TABLE `periode_pendaftaran` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `permintaan_hapus_akun`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `permintaan_hapus_akun` (
  `id_permintaan` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int(10) unsigned NOT NULL,
  `id_pengaju` int(10) unsigned NOT NULL,
  `alasan` text NOT NULL,
  `status_permintaan` enum('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  `id_peninjau` int(10) unsigned DEFAULT NULL,
  `catatan_peninjau` text DEFAULT NULL,
  `diajukan_at` datetime DEFAULT NULL,
  `ditinjau_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_permintaan`),
  KEY `permintaan_hapus_akun_id_mahasiswa_foreign` (`id_mahasiswa`),
  KEY `permintaan_hapus_akun_id_pengaju_foreign` (`id_pengaju`),
  KEY `permintaan_hapus_akun_id_peninjau_foreign` (`id_peninjau`),
  CONSTRAINT `permintaan_hapus_akun_id_mahasiswa_foreign` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  CONSTRAINT `permintaan_hapus_akun_id_pengaju_foreign` FOREIGN KEY (`id_pengaju`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `permintaan_hapus_akun_id_peninjau_foreign` FOREIGN KEY (`id_peninjau`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `permintaan_hapus_akun` WRITE;
/*!40000 ALTER TABLE `permintaan_hapus_akun` DISABLE KEYS */;
/*!40000 ALTER TABLE `permintaan_hapus_akun` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `riwayat_aktivitas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `riwayat_aktivitas` (
  `id_riwayat` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` int(10) unsigned DEFAULT NULL,
  `id_pengajuan` int(10) unsigned DEFAULT NULL,
  `id_mahasiswa` int(10) unsigned DEFAULT NULL,
  `aksi` varchar(100) NOT NULL,
  `status_lama` varchar(20) DEFAULT NULL,
  `status_baru` varchar(20) DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_riwayat`),
  KEY `riwayat_aktivitas_id_user_foreign` (`id_user`),
  KEY `riwayat_aktivitas_id_pengajuan_foreign` (`id_pengajuan`),
  KEY `riwayat_aktivitas_id_mahasiswa_foreign` (`id_mahasiswa`),
  CONSTRAINT `riwayat_aktivitas_id_mahasiswa_foreign` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE SET NULL,
  CONSTRAINT `riwayat_aktivitas_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE SET NULL,
  CONSTRAINT `riwayat_aktivitas_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `riwayat_aktivitas` WRITE;
/*!40000 ALTER TABLE `riwayat_aktivitas` DISABLE KEYS */;
/*!40000 ALTER TABLE `riwayat_aktivitas` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id_user` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_whatsapp` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `google_id` varchar(50) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `role` enum('mahasiswa','admin','super_admin') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_no_whatsapp_unique` (`no_whatsapp`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Bagian Akademik','superadmin@sidangsarjana.id',NULL,'$2y$12$bNxKD0iGhwylVTczLF9vRuJOGdGFZNr5LHCq3xlqKFrhZ6YxTW32y',NULL,NULL,'super_admin',1,'2026-11-24 09:07:10',NULL,NULL,'2026-11-24 09:07:10','2026-11-24 09:07:10'),
(2,'Siti Rahayu','admin@sidangsarjana.id',NULL,'$2y$12$6n5dh0hHJGP8ZxNfF1RE1uEHXFx0HDPu7Aw6QS5gRwjSvOFGQsZje',NULL,NULL,'admin',1,'2026-11-24 09:07:10',NULL,NULL,'2026-11-24 09:07:10','2026-11-24 09:07:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

