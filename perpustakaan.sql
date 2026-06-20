-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for perpustakaan_db
CREATE DATABASE IF NOT EXISTS `perpustakaan_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `perpustakaan_db`;

-- Dumping structure for table perpustakaan_db.anggota
CREATE TABLE IF NOT EXISTS `anggota` (
  `id_anggota` int NOT NULL AUTO_INCREMENT,
  `nama_anggota` varchar(100) NOT NULL,
  `no_identitas` varchar(30) DEFAULT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_anggota`),
  UNIQUE KEY `no_identitas` (`no_identitas`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.anggota: ~5 rows (approximately)
INSERT INTO `anggota` (`id_anggota`, `nama_anggota`, `no_identitas`, `no_telepon`, `alamat`, `created_at`) VALUES
	(7, 'Rusdi', '251235009', '08571234567', 'Jl. Street no.67', '2026-06-17 07:51:46'),
	(8, 'Fuad Baswedan', '2567126769', '08571234569', 'Jl. st Street no.54', '2026-06-17 07:52:30');

-- Dumping structure for table perpustakaan_db.buku
CREATE TABLE IF NOT EXISTS `buku` (
  `id_buku` int NOT NULL AUTO_INCREMENT,
  `judul_buku` varchar(200) NOT NULL,
  `tahun_terbit` year DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `stok_total` int DEFAULT '0',
  `stok_tersedia` int DEFAULT '0',
  `deskripsi` text,
  `gambar_sampul` varchar(255) DEFAULT NULL,
  `id_kategori` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_buku`),
  KEY `fk_buku_kategori` (`id_kategori`),
  CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.buku: ~2 rows (approximately)
INSERT INTO `buku` (`id_buku`, `judul_buku`, `tahun_terbit`, `isbn`, `stok_total`, `stok_tersedia`, `deskripsi`, `gambar_sampul`, `id_kategori`, `created_at`) VALUES
	(4, 'The Lord of the Rings: The Fellowship of the Ring', '1954', '', 5, 5, 'Perjalanan epik sebuah kelompok pahlawan melintasi dunia fantasi abad pertengahan yang penuh bahaya untuk menghancurkan sebuah cincin kekuasaan kuno.', 'cover_20260617073744_c6044f59.jpg', 6, '2026-06-17 07:37:44'),
	(5, 'Steve Jobs', '2011', '', 2, 2, 'Biografi eksklusif dan mendalam tentang kehidupan, visi, serta inovasi tokoh jenius di balik revolusi komputer personal dan perangkat mobile.', 'cover_20260617073920_863ff423.jpg', 7, '2026-06-17 07:39:20'),
	(6, 'Atomic Habits', '2018', '', 10, 10, 'Panduan komprehensif dan praktis tentang bagaimana menghentikan kebiasaan buruk dan membangun kebiasaan-kebiasaan kecil yang memberikan hasil luar biasa secara kumulatif.', 'cover_20260617074029_f2a41298.jpg', 8, '2026-06-17 07:40:29'),
	(7, 'Clean Code: A Handbook of Agile Software Craftsmanship', '2008', '', 3, 2, 'Panduan wajib bagi pengembang perangkat lunak untuk menulis kode yang bersih, terstruktur, dan menerapkan pilar-pilar Object-Oriented Programming dengan baik.', 'cover_20260617074525_a3abbb63.jpg', 10, '2026-06-17 07:45:25'),
	(8, 'Sapiens: Riwayat Singkat Umat Manusia', '2011', '', 6, 6, 'Eksplorasi sains dan sejarah mengenai bagaimana spesies Homo sapiens berhasil mendominasi bumi, menyatukan masyarakat, dan menciptakan peradaban.', 'cover_20260617075022_500ae199.jpg', 9, '2026-06-17 07:50:22');

-- Dumping structure for table perpustakaan_db.buku_pengarang
CREATE TABLE IF NOT EXISTS `buku_pengarang` (
  `id_buku` int NOT NULL,
  `id_pengarang` int NOT NULL,
  PRIMARY KEY (`id_buku`,`id_pengarang`),
  KEY `id_pengarang` (`id_pengarang`),
  CONSTRAINT `buku_pengarang_ibfk_1` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE,
  CONSTRAINT `buku_pengarang_ibfk_2` FOREIGN KEY (`id_pengarang`) REFERENCES `pengarang` (`id_pengarang`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.buku_pengarang: ~2 rows (approximately)
INSERT INTO `buku_pengarang` (`id_buku`, `id_pengarang`) VALUES
	(6, 6),
	(7, 8),
	(8, 9),
	(5, 10),
	(4, 11);

-- Dumping structure for table perpustakaan_db.detail_peminjaman
CREATE TABLE IF NOT EXISTS `detail_peminjaman` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_peminjaman` int NOT NULL,
  `id_buku` int NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `denda` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id_detail`),
  KEY `id_peminjaman` (`id_peminjaman`),
  KEY `id_buku` (`id_buku`),
  CONSTRAINT `detail_peminjaman_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`),
  CONSTRAINT `detail_peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.detail_peminjaman: ~4 rows (approximately)
INSERT INTO `detail_peminjaman` (`id_detail`, `id_peminjaman`, `id_buku`, `tanggal_kembali`, `denda`) VALUES
	(1, 1, 7, '2026-06-17', 0.00),
	(2, 2, 7, NULL, 0.00);

-- Dumping structure for function perpustakaan_db.fn_hitung_denda
DELIMITER //
CREATE FUNCTION `fn_hitung_denda`(
    jatuh_tempo DATE,
    kembali DATE
) RETURNS decimal(10,2)
    DETERMINISTIC
BEGIN

    DECLARE total_denda DECIMAL(10,2);

    IF kembali <= jatuh_tempo THEN
        SET total_denda = 0;
    ELSE
        SET total_denda =
        DATEDIFF(kembali,jatuh_tempo)*1000;
    END IF;

    RETURN total_denda;

END//
DELIMITER ;

-- Dumping structure for function perpustakaan_db.fn_jumlah_buku_kategori
DELIMITER //
CREATE FUNCTION `fn_jumlah_buku_kategori`(
    kategori_id INT
) RETURNS int
    DETERMINISTIC
BEGIN

    DECLARE total INT;

    SELECT COUNT(*)
    INTO total
    FROM buku
    WHERE id_kategori = kategori_id;

    RETURN total;

END//
DELIMITER ;

-- Dumping structure for table perpustakaan_db.kategori
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.kategori: ~5 rows (approximately)
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
	(6, 'Fiksi', 'Buku fiksi adalah karya yang dibuat berdasarkan imajinasi penulis untuk tujuan hiburan.'),
	(7, 'Biografi', 'Kisah nyata mengenai perjalanan hidup seseorang.'),
	(8, 'Pengembangan Diri', 'Buku panduan untuk memperbaiki kualitas hidup, produktivitas, atau kesehatan mental'),
	(9, 'Sains', 'Pembahasan mengenai fakta-fakta ilmiah dan alam semesta'),
	(10, 'Teknologi', 'Dirancang untuk menjelaskan cara kerja, pengembangan, dan penggunaan berbagai alat yang mempermudah adaptasi manusia dengan lingkungan alam atau digital.');

-- Dumping structure for table perpustakaan_db.peminjaman
CREATE TABLE IF NOT EXISTS `peminjaman` (
  `id_peminjaman` int NOT NULL AUTO_INCREMENT,
  `id_anggota` int NOT NULL,
  `id_user` int NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `status` enum('dipinjam','selesai') DEFAULT 'dipinjam',
  PRIMARY KEY (`id_peminjaman`),
  KEY `id_user` (`id_user`),
  KEY `fk_peminjaman_anggota` (`id_anggota`),
  CONSTRAINT `fk_peminjaman_anggota` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id_anggota`),
  CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.peminjaman: ~4 rows (approximately)
INSERT INTO `peminjaman` (`id_peminjaman`, `id_anggota`, `id_user`, `tanggal_pinjam`, `tanggal_jatuh_tempo`, `status`) VALUES
	(1, 8, 6, '2026-06-17', '2026-06-24', 'selesai'),
	(2, 7, 6, '2026-06-17', '2026-06-30', 'dipinjam');

-- Dumping structure for table perpustakaan_db.pengarang
CREATE TABLE IF NOT EXISTS `pengarang` (
  `id_pengarang` int NOT NULL AUTO_INCREMENT,
  `nama_pengarang` varchar(100) NOT NULL,
  `biografi` text,
  PRIMARY KEY (`id_pengarang`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.pengarang: ~5 rows (approximately)
INSERT INTO `pengarang` (`id_pengarang`, `nama_pengarang`, `biografi`) VALUES
	(6, 'James Clear', 'Penulis dan pembicara yang berfokus pada pengembangan kebiasaan, pengambilan keputusan, dan perbaikan berkelanjutan. Metode pendekatannya banyak didasarkan pada biologi, psikologi, dan ilmu saraf.'),
	(7, 'Morgan Housel', 'Pakar keuangan perilaku dan mantan kolumnis ekonomi. Fokus utamanya adalah membedah sisi psikologis dan emosional manusia dalam berinvestasi serta mengelola kekayaan jangka panjang.'),
	(8, 'Robert C. Martin', 'Dikenal juga sebagai "Uncle Bob", ia adalah insinyur perangkat lunak veteran, salah satu inisiator Agile Manifesto, dan penulis buku-buku pilar yang menjadi panduan utama dalam rekayasa perangkat lunak modern.'),
	(9, 'Yuval Noah Harari', 'Sejarawan, filsuf, dan profesor di Universitas Ibrani Yerusalem. Karyanya banyak membahas sejarah makro umat manusia, evolusi biologi, dan proyeksi masa depan teknologi.'),
	(10, 'Walter Isaacson', 'Penulis biografi ternama, jurnalis, dan mantan pemimpin redaksi majalah Time. Ia merupakan spesialis yang merangkum kehidupan tokoh-tokoh revolusioner yang mengubah dunia melalui inovasi.'),
	(11, 'J.R.R. Tolkien', 'Penulis fantasi legendaris asal Inggris, ahli bahasa, dan profesor di Universitas Oxford. Karyanya yang paling ikonik telah membentuk standar modern untuk genre fantasi tingkat tinggi (high fantasy).');

-- Dumping structure for table perpustakaan_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','petugas') DEFAULT 'petugas',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table perpustakaan_db.users: ~2 rows (approximately)
INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `email`, `role`, `created_at`) VALUES
	(5, 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator Utama', 'admin1@booksphere.com', 'admin', '2026-06-16 10:14:01'),
	(6, 'admin', '$2y$10$wHq2pQjZVDaHE0Lbm5ISm.1QDKxnRx2sMWWemkWZPvbwCIBgyly86', 'Admin BookSphere', 'admin@booksphere.test', 'admin', '2026-06-16 16:07:21');

-- Dumping structure for view perpustakaan_db.vw_buku_dipinjam
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_buku_dipinjam` (
	`id_peminjaman` INT NOT NULL,
	`nama_anggota` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`judul_buku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`tanggal_pinjam` DATE NOT NULL,
	`tanggal_jatuh_tempo` DATE NOT NULL,
	`status_tampil` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci'
) ENGINE=MyISAM;

-- Dumping structure for view perpustakaan_db.vw_buku_lengkap
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `vw_buku_lengkap` (
	`id_buku` INT NOT NULL,
	`judul_buku` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`tahun_terbit` YEAR NULL,
	`isbn` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`gambar_sampul` VARCHAR(1) NULL COLLATE 'utf8mb4_0900_ai_ci',
	`nama_kategori` VARCHAR(1) NOT NULL COLLATE 'utf8mb4_0900_ai_ci',
	`stok_total` INT NULL,
	`stok_tersedia` INT NULL
) ENGINE=MyISAM;

-- Dumping structure for trigger perpustakaan_db.trg_kembali_buku
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_kembali_buku` AFTER UPDATE ON `detail_peminjaman` FOR EACH ROW BEGIN

    IF OLD.tanggal_kembali IS NULL
    AND NEW.tanggal_kembali IS NOT NULL THEN

        UPDATE buku
        SET stok_tersedia = stok_tersedia + 1
        WHERE id_buku = NEW.id_buku;

    END IF;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger perpustakaan_db.trg_pinjam_buku
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_pinjam_buku` AFTER INSERT ON `detail_peminjaman` FOR EACH ROW BEGIN

    UPDATE buku
    SET stok_tersedia = stok_tersedia - 1
    WHERE id_buku = NEW.id_buku;

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vw_buku_dipinjam`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_buku_dipinjam` AS select `p`.`id_peminjaman` AS `id_peminjaman`,`a`.`nama_anggota` AS `nama_anggota`,`b`.`judul_buku` AS `judul_buku`,`p`.`tanggal_pinjam` AS `tanggal_pinjam`,`p`.`tanggal_jatuh_tempo` AS `tanggal_jatuh_tempo`,(case when ((`p`.`status` = 'dipinjam') and (`p`.`tanggal_jatuh_tempo` < curdate())) then 'terlambat' else `p`.`status` end) AS `status_tampil` from (((`peminjaman` `p` join `anggota` `a` on((`p`.`id_anggota` = `a`.`id_anggota`))) join `detail_peminjaman` `dp` on((`p`.`id_peminjaman` = `dp`.`id_peminjaman`))) join `buku` `b` on((`dp`.`id_buku` = `b`.`id_buku`))) where (`dp`.`tanggal_kembali` is null);

-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `vw_buku_lengkap`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `vw_buku_lengkap` AS select `b`.`id_buku` AS `id_buku`,`b`.`judul_buku` AS `judul_buku`,`b`.`tahun_terbit` AS `tahun_terbit`,`b`.`isbn` AS `isbn`,`b`.`gambar_sampul` AS `gambar_sampul`,`k`.`nama_kategori` AS `nama_kategori`,`b`.`stok_total` AS `stok_total`,`b`.`stok_tersedia` AS `stok_tersedia` from (`buku` `b` join `kategori` `k` on((`b`.`id_kategori` = `k`.`id_kategori`)));

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
