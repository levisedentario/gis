-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2026 at 01:44 PM
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
-- Database: `gis_mingsci`
--

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `location_name` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `location_name`, `latitude`, `longitude`, `created_at`) VALUES
(4, 'P2', 10.24157226, 123.80927224, '2026-08-02 05:43:46'),
(5, 'P3', 10.24076001, 123.80984750, '2026-08-02 13:22:12'),
(6, 'P1', 10.24220644, 123.81010437, '2026-08-02 13:22:36'),
(7, 'P1', 10.24331501, 123.81029746, '2026-08-02 13:22:48'),
(8, 'P5', 10.24369509, 123.81115623, '2026-08-02 13:23:14'),
(9, 'FP1', 10.24461292, 123.81367120, '2026-08-02 13:24:17'),
(10, 'FP2', 10.24433314, 123.81210493, '2026-08-02 13:24:43'),
(11, 'FP3', 10.24424867, 123.81200301, '2026-08-02 13:25:08'),
(12, 'FP4', 10.24410086, 123.81128918, '2026-08-02 13:25:23'),
(13, 'FP5', 10.24388971, 123.81037751, '2026-08-02 13:25:35'),
(14, 'FP6', 10.24373662, 123.80956756, '2026-08-02 13:25:47'),
(15, 'FP7', 10.24334598, 123.80945475, '2026-08-02 13:27:28'),
(16, 'FP8', 10.24237466, 123.80960463, '2026-08-02 13:27:43'),
(17, 'FP9', 10.24160393, 123.80974992, '2026-08-02 13:28:02'),
(18, 'P2', 10.24266042, 123.80938559, '2026-08-02 13:45:22'),
(19, 'P2', 10.24262277, 123.80908465, '2026-08-02 13:47:38'),
(20, 'P2', 10.24254886, 123.80878920, '2026-08-02 13:48:45'),
(21, 'P3', 10.24126080, 123.80898776, '2026-08-02 14:14:50'),
(22, 'P3', 10.24116578, 123.80867129, '2026-08-02 14:14:57');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `phase` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `phase`, `email`, `created_at`) VALUES
(1, 'Phase 1', 'jayrseedentario@gmail.com', '2026-08-02 14:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2a$12$YRqeK2vdJ7gwPQNkrB.3Wer.tVsVOqhWLuvov9AO7CNv6iyjB1OaO');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
