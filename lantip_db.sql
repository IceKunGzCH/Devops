-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 06:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lantip_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

CREATE TABLE `comment` (
  `comment_id` int(11) NOT NULL,
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment`
--

INSERT INTO `comment` (`comment_id`, `topic_id`, `user_id`, `content`, `created_at`) VALUES
(1, 5, 2, 'ควย', '2025-11-21 14:54:14'),
(2, 5, 3, 'สวัสดี', '2025-11-24 13:58:58'),
(3, 9, 2, 'ดีจริงผมพึ่งไปมา', '2025-12-08 13:22:17'),
(4, 10, 2, 'ผมก็พึ่งไปมา', '2025-12-08 13:27:49'),
(5, 9, 2, 'เพื่อนชื่อจีก็พึ่งไปมา', '2025-12-08 13:29:41');

-- --------------------------------------------------------

--
-- Table structure for table `topic`
--

CREATE TABLE `topic` (
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `tags` varchar(255) DEFAULT '',
  `image_url` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `replies` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `topic`
--

INSERT INTO `topic` (`topic_id`, `user_id`, `title`, `content`, `tags`, `image_url`, `views`, `replies`, `created_at`, `updated_at`) VALUES
(2, 1, 'ควย', 'หีควยแตด', 'เรื่องหีๆควยๆ', NULL, 2, 0, '2025-11-21 07:08:58', '2025-11-24 06:30:04'),
(3, 1, 'ควย', 'หีควยแตด', 'เรื่องหีๆควยๆ', NULL, 4, 0, '2025-11-21 07:10:06', '2025-11-21 07:17:24'),
(4, 1, '12345', '13245', '12345', NULL, 7, 0, '2025-11-21 07:17:37', '2025-11-21 08:09:37'),
(5, 1, '12345679ๅ/-ภถุึคต', '123456789', '12345689', NULL, 30, 0, '2025-11-21 07:19:18', '2025-12-08 06:28:45'),
(7, 2, 'ๅ/-ภถุึคตจ', 'ๅ/-ภถึคต', '12345689', 'img_69311d09c2ea58.88013143.jpg', 7, 0, '2025-12-04 05:32:57', '2025-12-04 05:49:36'),
(8, 2, 'ๅ/-ภถุึค', 'ๅ/-ภถุึ', 'เรื่องหีๆควยๆ', 'img_693120fe332ac0.67058723.png', 10, 0, '2025-12-04 05:49:50', '2025-12-08 06:30:04'),
(9, 2, 'ซายุริ', 'บะหมี่อันดับ 1 ในเชียงใหม่', 'ตีบะหมี่', 'img_69366e8881e059.18471237.jpg', 6, 0, '2025-12-08 06:22:00', '2025-12-08 06:29:41'),
(10, 2, 'erggjreejkljgrn', 'lkmdrlgkrg', 'frfrf', 'img_69366fd1c1a3d9.10607025.jpg', 4, 0, '2025-12-08 06:27:29', '2025-12-08 06:28:37'),
(11, 2, '12345678', '12345678', '1234567', NULL, 1, 0, '2025-12-10 05:44:12', '2025-12-10 05:44:12');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `email`, `date_of_birth`, `password_hash`, `created_at`) VALUES
(1, 'ice1', 'chanachai982548@gmail.com', '2025-11-21', '$2y$10$uEIL7gYiFLPZe9bJcuqjeOUdUx0Miu2FCeptm5hde0.tymCVXcqpW', '2025-11-21 07:02:48'),
(2, 'ice2', 'mricekungthailand@gmail.com', '2025-11-21', '$2y$10$utRq8OYd7RtEuj7zIdSXV..DPYghWnRgaBihZgoQYBOVlqVbQJWAq', '2025-11-21 07:48:15'),
(3, 'ice3', 'icekung3@gmail.com', '2025-11-07', '$2y$10$IyVSq9LZ0I3Nny1AVT0xIOkRFyHwsUN.6XO8vL/TK8wfEr4LfTiN2', '2025-11-24 06:33:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `topic_id` (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `topic`
--
ALTER TABLE `topic`
  ADD PRIMARY KEY (`topic_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `topic`
--
ALTER TABLE `topic`
  MODIFY `topic_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`topic_id`) REFERENCES `topic` (`topic_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `topic`
--
ALTER TABLE `topic`
  ADD CONSTRAINT `topic_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
