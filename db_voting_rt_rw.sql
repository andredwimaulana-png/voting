-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2026 at 07:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_voting_rt_rw`
--

-- --------------------------------------------------------

--
-- Table structure for table `calon`
--

CREATE TABLE `calon` (
  `id` int(11) NOT NULL,
  `nomor_urut` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jabatan` enum('Ketua RT','Ketua RW') NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `alamat` varchar(150) DEFAULT NULL,
  `visi` text DEFAULT NULL,
  `misi` text DEFAULT NULL,
  `program_kerja` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `calon`
--

INSERT INTO `calon` (`id`, `nomor_urut`, `nama`, `jabatan`, `foto`, `alamat`, `visi`, `misi`, `program_kerja`, `deskripsi`, `created_at`) VALUES
(1, '01', 'H. Suryanto, S.Pd.', 'Ketua RT', 'calon_rt_01.jpg', 'RT 05 No. 07', 'Mewujudkan RT 05 yang aman, bersih, dan guyub rukun.', '1. Menghidupkan kembali kegiatan ronda malam.\n2. Mengadakan kerja bakti rutin tiap bulan.\n3. Membuka posko pengaduan warga.', 'Ronda malam, kerja bakti bulanan, posko kesehatan lansia.', 'Tokoh masyarakat RT 05, mantan ketua karang taruna, aktif di kegiatan sosial.', '2026-11-12 11:18:07'),
(2, '02', 'Drs. Bambang W.', 'Ketua RT', 'calon_rt_02.jpg', 'RT 05 No. 15', 'RT 05 yang modern, tertib administrasi, dan peduli lingkungan.', '1. Digitalisasi administrasi kependudukan.\n2. Program bank sampah.\n3. Beasiswa anak berprestasi.', 'Bank sampah, digitalisasi surat, beasiswa warga.', 'Pensiunan ASN, pengalaman 20 tahun di bidang administrasi.', '2026-11-12 11:18:07'),
(3, '03', 'Ibu Sri Wahyuni', 'Ketua RT', 'calon_rt_03.jpg', 'RT 05 No. 22', 'Membangun RT 05 yang ramah keluarga dan peduli anak.', '1. Kegiatan posyandu rutin.\n2. Taman baca anak.\n3. Pemberdayaan ibu-ibu PKK.', 'Posyandu, taman baca, pelatihan UMKM ibu-ibu.', 'Aktif di PKK kecamatan, penggerak posyandu RT 05.', '2026-11-12 11:18:07'),
(4, '01', 'H. Mohammad Ridwan', 'Ketua RW', 'calon_rw_01.jpg', 'RW 03 Blok A', 'RW 03 yang maju, mandiri, dan berdaya saing.', '1. Perbaikan infrastruktur jalan.\n2. Program UMKM warga.\n3. Poskamling terpadu.', 'Perbaikan jalan, pelatihan UMKM, poskamling terpadu.', 'Pengusaha lokal, aktif di kegiatan RW sejak 2010.', '2026-11-12 11:18:07'),
(5, '02', 'Ir. Hendra Kusuma', 'Ketua RW', 'calon_rw_02.jpg', 'RW 03 Blok B', 'RW 03 yang hijau, aman, dan transparan.', '1. Program penghijauan.\n2. Transparansi keuangan RW.\n3. Sistem keamanan berbasis CCTV.', 'Penghijauan, laporan keuangan terbuka, CCTV lingkungan.', 'Insinyur sipil, pengalaman di proyek infrastruktur kota.', '2026-11-12 11:18:07'),
(6, '03', 'Ibu Endang S.', 'Ketua RW', 'calon_rw_03.jpg', 'RW 03 Blok C', 'RW 03 yang inklusif dan peduli sesama.', '1. Layanan kesehatan gratis.\n2. Pemberdayaan difabel.\n3. Kegiatan keagamaan rutin.', 'Posyandu lansia, pelatihan difabel, pengajian rutin.', 'Aktif di organisasi sosial keagamaan, penggerak kegiatan RW.', '2026-11-12 11:18:07'),
(7, '04', 'Neo fidza', 'Ketua RT', 'calon_1794464747_6af55bebdea30.jpg', 'RT 01 No. 5', 'cari istri baru', 'beli mobil 3 m menjaga warmah kalimantan', 'jalan kalimantan lurus', 'karhutla', '2026-11-12 13:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `periode_voting`
--

CREATE TABLE `periode_voting` (
  `id` int(11) NOT NULL,
  `nama_periode` varchar(100) NOT NULL,
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_selesai` datetime NOT NULL,
  `status` enum('Aktif','Nonaktif') NOT NULL DEFAULT 'Nonaktif',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `periode_voting`
--

INSERT INTO `periode_voting` (`id`, `nama_periode`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`) VALUES
(1, 'Pemilihan Ketua RT 05 & RW 03 Periode 2026-2031', '2026-11-01 08:00:00', '2026-11-30 20:00:00', 'Aktif', '2026-11-12 11:18:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat` varchar(150) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pemilih') NOT NULL DEFAULT 'pemilih',
  `status_verifikasi` enum('pending','terverifikasi') NOT NULL DEFAULT 'terverifikasi',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `no_hp`, `alamat`, `password`, `role`, `status_verifikasi`, `created_at`) VALUES
(1, 'Administrator', 'admin@rt-rw.test', '081200000001', 'Kantor RW', '$2y$10$PwjD/PQXv1JJ1U9XI1r8LO16devd3ujQt39Zl4dzfXEXNKRdHmhTS', 'admin', 'terverifikasi', '2026-11-12 11:18:07'),
(2, 'Budi Santoso', 'budi@warga.test', '081200000002', 'RT 05 No. 12', '$2y$10$HEX3.gcWQfqKTzkI92/Wte/NbCk.ico8I4me6EZJKgn9YbUDH0VIS', 'pemilih', 'terverifikasi', '2026-11-12 11:18:07'),
(3, 'Siti Aminah', 'siti@warga.test', '081200000003', 'RT 05 No. 20', '$2y$10$HEX3.gcWQfqKTzkI92/Wte/NbCk.ico8I4me6EZJKgn9YbUDH0VIS', 'pemilih', 'terverifikasi', '2026-11-12 11:18:07'),
(4, 'Ahmad Fauzi', 'ahmad@warga.test', '081200000004', 'RT 05 No. 33', '$2y$10$HEX3.gcWQfqKTzkI92/Wte/NbCk.ico8I4me6EZJKgn9YbUDH0VIS', 'pemilih', 'terverifikasi', '2026-11-12 11:18:07'),
(5, 'Dewi Lestari', 'dewi@warga.test', '081200000005', 'RT 05 No. 41', '$2y$10$HEX3.gcWQfqKTzkI92/Wte/NbCk.ico8I4me6EZJKgn9YbUDH0VIS', 'pemilih', 'terverifikasi', '2026-11-12 11:18:07'),
(6, 'Rudi Hartono', 'rudi@warga.test', '081200000006', 'RT 05 No. 52', '$2y$10$HEX3.gcWQfqKTzkI92/Wte/NbCk.ico8I4me6EZJKgn9YbUDH0VIS', 'pemilih', 'terverifikasi', '2026-11-12 11:18:07'),
(7, 'Warga Baru', 'wargabaru@gmail.com', '081234567890', 'RT 01 No. 5', '$2y$10$peek8yh5LiNpVhCyzHBKtOwpRfcxtewjvqqYfS5Bf6JF0YU0EB17i', 'pemilih', 'terverifikasi', '2026-11-12 11:52:00');

-- --------------------------------------------------------

--
-- Table structure for table `voting`
--

CREATE TABLE `voting` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `calon_id` int(11) NOT NULL,
  `jabatan` enum('Ketua RT','Ketua RW') NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voting`
--

INSERT INTO `voting` (`id`, `user_id`, `calon_id`, `jabatan`, `created_at`) VALUES
(1, 2, 1, 'Ketua RT', '2026-11-12 11:18:07'),
(2, 2, 4, 'Ketua RW', '2026-11-12 11:18:07'),
(3, 3, 1, 'Ketua RT', '2026-11-12 11:18:07'),
(4, 3, 5, 'Ketua RW', '2026-11-12 11:18:07'),
(5, 4, 2, 'Ketua RT', '2026-11-12 11:18:07'),
(6, 4, 4, 'Ketua RW', '2026-11-12 11:18:07'),
(7, 5, 3, 'Ketua RT', '2026-11-12 11:18:07'),
(8, 5, 6, 'Ketua RW', '2026-11-12 11:18:07'),
(9, 7, 1, 'Ketua RT', '2026-11-12 12:45:30'),
(10, 7, 5, 'Ketua RW', '2026-11-12 12:45:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calon`
--
ALTER TABLE `calon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_nomor_jabatan` (`nomor_urut`,`jabatan`);

--
-- Indexes for table `periode_voting`
--
ALTER TABLE `periode_voting`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `voting`
--
ALTER TABLE `voting`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_jabatan` (`user_id`,`jabatan`),
  ADD KEY `fk_voting_calon` (`calon_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calon`
--
ALTER TABLE `calon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `periode_voting`
--
ALTER TABLE `periode_voting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `voting`
--
ALTER TABLE `voting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `voting`
--
ALTER TABLE `voting`
  ADD CONSTRAINT `fk_voting_calon` FOREIGN KEY (`calon_id`) REFERENCES `calon` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_voting_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
