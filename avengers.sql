-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 02, 2024 at 08:51 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `avengers`
--

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int NOT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `image` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `release_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='avengers members';

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `title`, `image`, `summary`, `release_at`) VALUES
(78, 'Rudeus Greyrat', 'https://static.wikia.nocookie.net/mushokutensei/images/a/ad/Rudeus_Greyrat_Adult.png', 'Protagonis utama, seorang NEET yang bereinkarnasi ke dunia fantasi dengan bakat sihir luar biasa.', '2024-01-01'),
(79, 'Sylphiette (Sylphie)', 'https://i.pinimg.com/736x/e6/80/80/e6808026ac5503b2e52bebf514024541.jpg', 'Teman masa kecil Rudeus dan cinta pertamanya, memiliki rambut hijau dan kepribadian lembut.', '2024-01-02'),
(80, 'Roxy Migurdia', 'https://image.civitai.com/xG1nkqKTMzGDvpLrqFT7WA/7ce25db3-05a9-4cb0-8b4b-d54cf61d146f/width=450/00002-37ba5.jpeg', 'Guru sihir Rudeus, berasal dari ras Migurd, dengan rambut biru dan telinga seperti elf.', '2024-01-03'),
(81, 'Zenith Greyrat', '', 'Ibu Rudeus yang penuh kasih dan mencintai keluarganya dengan sepenuh hati.', '2024-01-04'),
(82, 'Paul Greyrat', '', 'Ayah Rudeus, seorang petualang ceria dengan sisi gelap.', '2024-01-05'),
(83, 'Norn Greyrat', '', 'Adik perempuan Rudeus yang kuat dan bertekad untuk menjadi lebih baik dari kakaknya.', '2024-01-06'),
(84, 'Eris Greyrat', 'https://static.wikia.nocookie.net/joblessreincarnation/images/6/6c/Eris.Boreas.Greyrat.600.3067829.jpg', 'Adik perempuan Rudeus yang lahir setelahnya, ceria dan penuh semangat.', '2024-01-07'),
(85, 'Lilia', '', 'Pelayan keluarga Greyrat yang sangat peduli pada Rudeus dan keluarganya.', '2024-01-08'),
(86, 'Ghislaine Dedoldia', '', 'Guru bela diri Rudeus, seorang beastwoman yang sangat kuat dan tegas.', '2024-01-09'),
(87, 'Elinalise Dragonroad', '', 'Petualang berpengalaman dengan daya tarik kuat dan masa lalu yang misterius.', '2024-01-10'),
(88, 'Ruijerd Supardia', '', 'Pria dari ras Superd yang memiliki reputasi buruk namun baik hati.', '2024-01-11'),
(89, 'Sion', '', 'Penyihir kuat dengan penampilan mencolok dan sifat misterius.', '2024-01-12'),
(90, 'Orsted', '', 'Karakter misterius dengan kekuatan luar biasa dan tujuan penting dalam cerita.', '2024-01-13'),
(91, 'Miko', '', 'Gadis muda berbakat sihir, teman Rudeus dengan kepribadian ceria.', '2024-01-14'),
(92, 'Khadar', '', 'Petualang berpengalaman dan mentor Rudeus dalam beberapa hal.', '2024-01-15'),
(93, 'Aisha', '', 'Karakter ceria dengan hubungan dekat dengan Rudeus.', '2024-01-16'),
(94, 'Sophie', '', 'Gadis berbakat sihir yang menjadi salah satu teman Rudeus.', '2024-01-17'),
(95, 'Gina', '', 'Karakter dengan latar belakang kompleks, memiliki hubungan kuat dengan Rudeus.', '2024-01-18'),
(96, 'Kraft', '', 'Penyihir dengan pengetahuan mendalam tentang sihir, mentor bagi Rudeus.', '2024-01-19'),
(97, 'Fitz', '', 'Karakter dengan identitas ganda, memiliki hubungan rumit dengan Rudeus.', '2024-01-20'),
(98, 'Frieren', 'https://static.wikia.nocookie.net/frieren/images/3/35/Frieren_anime_profile.png', 'Protagonis utama, seorang penyihir elf yang merupakan anggota tim pahlawan. Dia memiliki pandangan yang mendalam tentang waktu dan kehidupan.', '2024-11-02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
