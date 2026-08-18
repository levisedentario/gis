-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 16, 2026 at 04:15 PM
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
-- Table structure for table `api_result`
--

CREATE TABLE `api_result` (
  `id` int(10) UNSIGNED NOT NULL,
  `api_name` varchar(80) NOT NULL,
  `request_key` varchar(255) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `response_code` smallint(5) UNSIGNED DEFAULT NULL,
  `response_payload` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 5 minute)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_result`
--

INSERT INTO `api_result` (`id`, `api_name`, `request_key`, `latitude`, `longitude`, `response_code`, `response_payload`, `created_at`, `updated_at`, `expires_at`) VALUES
(1, 'meteoblue', 'meteoblue|10.24364700|123.80940500|1.00', 10.24364700, 123.80940500, 200, '{\"dateText\":\"2026-08-16\",\"intensityText\":\"Light\",\"precipitationText\":\"1.30 mm\",\"probabilityText\":\"39%\"}', '2026-08-15 11:43:09', '2026-08-16 01:18:11', '2026-08-16 01:23:11'),
(6, 'meteoblue', 'meteoblue|10.24460200|123.81373500|3.23', 10.24460200, 123.81373500, 200, '{\"dateText\":\"2026-08-16\",\"intensityText\":\"Light\",\"precipitationText\":\"0.10 mm\",\"probabilityText\":\"20%\"}', '2026-08-16 13:24:16', '2026-08-16 13:24:16', '2026-08-16 13:29:16');

-- --------------------------------------------------------

--
-- Table structure for table `elevation_survey_data`
--

CREATE TABLE `elevation_survey_data` (
  `id` int(11) NOT NULL,
  `segment` int(11) NOT NULL,
  `station` varchar(20) NOT NULL,
  `finished_grade_elevation` decimal(10,3) DEFAULT NULL,
  `drainage_floor_line_elevation` decimal(10,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elevation_survey_data`
--

INSERT INTO `elevation_survey_data` (`id`, `segment`, `station`, `finished_grade_elevation`, `drainage_floor_line_elevation`) VALUES
(1, 1, '00+000', 3.836, 3.230),
(2, 1, '00+020', 3.840, 3.220),
(3, 1, '00+040', 3.805, 3.205),
(4, 1, '00+060', 3.895, 3.175),
(5, 1, '00+080', 4.072, 3.080),
(6, 1, '00+100', 4.490, 2.600),
(7, 1, '00+120', 4.490, 2.590),
(8, 1, '00+140', 4.490, 2.590),
(9, 1, '00+160', 4.490, 2.590),
(10, 1, '00+180', 4.490, 2.590),
(11, 1, '00+200', 4.490, 2.600),
(12, 1, '00+220', 4.490, 2.600),
(13, 1, '00+240', 4.490, 2.970),
(14, 2, '00+240', 4.490, 2.970),
(15, 2, '00+260', 4.450, 2.950),
(16, 2, '00+280', 4.450, 2.950),
(17, 2, '00+300', 4.450, 2.940),
(18, 2, '00+320', 4.420, 2.910),
(19, 2, '00+340', 4.450, 2.890),
(20, 2, '00+360', 4.400, 2.850),
(21, 2, '00+380', 4.400, 2.800),
(22, 2, '00+400', 4.400, 2.790),
(23, 2, '00+420', 4.400, 2.780),
(24, 2, '00+440', 4.400, 2.750),
(25, 2, '00+460', 4.400, 2.700),
(26, 2, '00+480', 4.400, 2.680),
(27, 2, '00+500', 4.400, 2.650),
(28, 2, '00+520', 4.400, 2.610),
(29, 3, '00+520', 4.400, 2.610),
(30, 3, '00+540', 4.350, 2.590),
(31, 3, '00+560', 4.350, 2.550),
(32, 3, '00+580', 4.300, 2.520),
(33, 3, '00+600', 4.250, 2.490),
(34, 3, '00+620', 4.200, 2.450),
(35, 3, '00+640', 4.150, 2.400),
(36, 3, '00+660', 4.100, 2.350),
(37, 3, '00+680', 4.050, 2.300),
(38, 3, '00+700', 4.000, 2.280),
(39, 3, '00+720', 3.950, 2.250),
(40, 3, '00+740', 3.950, 2.230),
(41, 3, '00+760', 3.950, 2.220),
(42, 3, '00+780', 3.930, 2.210),
(43, 3, '00+800', 3.915, 2.210),
(44, 4, '00+800', 3.915, 2.210),
(45, 4, '00+820', 3.700, 2.180),
(46, 4, '00+840', 3.500, 2.160),
(47, 4, '00+860', 3.200, 2.130),
(48, 4, '00+880', 2.900, 2.110),
(49, 4, '00+900', 2.600, 2.090),
(50, 4, '00+920', 2.300, 2.070),
(51, 4, '00+940', 2.100, 2.060),
(52, 4, '00+960', 1.800, 2.050),
(53, 4, '00+980', 1.600, 2.050),
(54, 4, '00+985', 1.540, 2.050);

-- --------------------------------------------------------

--
-- Table structure for table `garbage_accumulation`
--

CREATE TABLE `garbage_accumulation` (
  `id` int(11) NOT NULL,
  `manhole` varchar(10) NOT NULL,
  `nbase` decimal(5,3) NOT NULL,
  `nmodified` decimal(5,3) NOT NULL,
  `ngarbage` decimal(5,3) NOT NULL,
  `flood_susceptibility` enum('Low','Moderate','High') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `garbage_accumulation`
--

INSERT INTO `garbage_accumulation` (`id`, `manhole`, `nbase`, `nmodified`, `ngarbage`, `flood_susceptibility`) VALUES
(1, 'MH1', 0.013, 0.020, 0.007, 'Low'),
(2, 'MH2', 0.013, 0.030, 0.017, 'Low'),
(3, 'MH3', 0.013, 0.080, 0.067, 'High'),
(4, 'MH4', 0.013, 0.020, 0.007, 'Low'),
(5, 'MH5', 0.013, 0.030, 0.017, 'Low'),
(6, 'MH6', 0.013, 0.020, 0.007, 'Low'),
(7, 'MH7', 0.013, 0.020, 0.007, 'Low'),
(8, 'MH8', 0.013, 0.020, 0.007, 'Low'),
(9, 'MH9', 0.013, 0.050, 0.037, 'Moderate'),
(10, 'MH10', 0.013, 0.050, 0.037, 'Moderate'),
(11, 'MH11', 0.013, 0.030, 0.017, 'Low'),
(12, 'MH12', 0.013, 0.045, 0.032, 'Moderate'),
(13, 'MH13', 0.013, 0.030, 0.017, 'Low'),
(14, 'MH14', 0.013, 0.030, 0.017, 'Low'),
(15, 'MH15', 0.013, 0.080, 0.067, 'High'),
(16, 'MH16', 0.013, 0.045, 0.032, 'Moderate'),
(17, 'MH17', 0.013, 0.030, 0.017, 'Low'),
(18, 'MH18', 0.013, 0.080, 0.067, 'High'),
(19, 'MH19', 0.013, 0.030, 0.017, 'Low'),
(20, 'MH20', 0.013, 0.030, 0.017, 'Low'),
(21, 'MH21', 0.013, 0.045, 0.032, 'Moderate'),
(22, 'MH22', 0.013, 0.050, 0.037, 'Moderate'),
(23, 'MH23', 0.013, 0.080, 0.067, 'High'),
(24, 'MH24', 0.013, 0.045, 0.032, 'Moderate'),
(25, 'MH25', 0.013, 0.080, 0.067, 'High'),
(26, 'MH26', 0.013, 0.045, 0.032, 'Moderate'),
(27, 'MH27', 0.013, 0.045, 0.032, 'Moderate'),
(28, 'MH28', 0.013, 0.045, 0.032, 'Moderate'),
(29, 'MH29', 0.013, 0.030, 0.017, 'Low'),
(30, 'MH30', 0.013, 0.030, 0.017, 'Low'),
(31, 'MH31', 0.013, 0.045, 0.032, 'Moderate'),
(32, 'MH32', 0.013, 0.030, 0.017, 'Low'),
(33, 'MH33', 0.013, 0.045, 0.032, 'Moderate'),
(34, 'MH34', 0.013, 0.045, 0.032, 'Moderate'),
(35, 'MH35', 0.013, 0.050, 0.037, 'Moderate'),
(36, 'MH36', 0.013, 0.030, 0.017, 'Low'),
(37, 'MH37', 0.013, 0.045, 0.032, 'Moderate'),
(38, 'MH38', 0.013, 0.050, 0.037, 'Moderate'),
(39, 'MH39', 0.013, 0.030, 0.017, 'Low'),
(40, 'MH40', 0.013, 0.050, 0.037, 'Moderate'),
(41, 'MH41', 0.013, 0.030, 0.017, 'Low'),
(42, 'MH42', 0.013, 0.045, 0.032, 'Moderate'),
(43, 'MH43', 0.013, 0.030, 0.017, 'Low'),
(44, 'MH44', 0.013, 0.045, 0.032, 'Moderate'),
(45, 'MH45', 0.013, 0.030, 0.017, 'Low'),
(46, 'MH46', 0.013, 0.030, 0.017, 'Low'),
(47, 'MH47', 0.013, 0.045, 0.032, 'Moderate'),
(48, 'MH48', 0.013, 0.050, 0.037, 'Moderate'),
(49, 'MH49', 0.013, 0.045, 0.032, 'Moderate'),
(50, 'MH50', 0.013, 0.045, 0.032, 'Moderate'),
(51, 'MH51', 0.013, 0.050, 0.037, 'Moderate'),
(52, 'MH52', 0.013, 0.030, 0.017, 'Low'),
(53, 'MH53', 0.013, 0.030, 0.017, 'Low'),
(54, 'MH54', 0.013, 0.030, 0.017, 'Low'),
(55, 'MH55', 0.013, 0.045, 0.032, 'Moderate'),
(56, 'MH56', 0.013, 0.045, 0.032, 'Moderate'),
(57, 'MH57', 0.013, 0.045, 0.032, 'Moderate'),
(58, 'MH58', 0.013, 0.045, 0.032, 'Moderate'),
(59, 'MH59', 0.013, 0.030, 0.017, 'Low'),
(60, 'MH60', 0.013, 0.030, 0.017, 'Low'),
(61, 'MH61', 0.013, 0.030, 0.017, 'Low'),
(62, 'MH62', 0.013, 0.030, 0.017, 'Low'),
(63, 'MH63', 0.013, 0.030, 0.017, 'Low'),
(64, 'MH64', 0.013, 0.045, 0.032, 'Moderate'),
(65, 'MH65', 0.013, 0.045, 0.032, 'Moderate'),
(66, 'MH66', 0.013, 0.030, 0.017, 'Low'),
(67, 'MH67', 0.013, 0.030, 0.017, 'Low'),
(68, 'MH68', 0.013, 0.030, 0.017, 'Low'),
(69, 'MH69', 0.013, 0.045, 0.032, 'Moderate'),
(70, 'MH70', 0.013, 0.030, 0.017, 'Low'),
(71, 'MH71', 0.013, 0.030, 0.017, 'Low'),
(72, 'MH72', 0.013, 0.045, 0.032, 'Moderate');

-- --------------------------------------------------------

--
-- Table structure for table `impervious_data`
--

CREATE TABLE `impervious_data` (
  `id` int(11) NOT NULL,
  `surface_category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `area_m2` decimal(12,2) NOT NULL,
  `percentage_total_area` decimal(10,2) DEFAULT NULL,
  `hydrological_function` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `impervious_data`
--

INSERT INTO `impervious_data` (`id`, `surface_category`, `description`, `area_m2`, `percentage_total_area`, `hydrological_function`) VALUES
(1, 'Building Roof Footprints', '136 Townhouse Units', 2651.86, 28.12, 'Roof Runoff / Structural Coverage'),
(2, 'Roadway & Pavement', 'Asphalt/Concrete Road Corridor', 9429.70, 100.00, 'Direct Surface Runoff'),
(3, 'Combined Impervious Area', 'Roofs + Pavement', 12081.56, NULL, 'Composite Runoff Input (C)');

-- --------------------------------------------------------

--
-- Table structure for table `infiltration_data`
--

CREATE TABLE `infiltration_data` (
  `id` int(11) NOT NULL,
  `soil_class` varchar(50) NOT NULL,
  `f0_mm_h` decimal(10,2) NOT NULL,
  `fc_mm_h` decimal(10,2) NOT NULL,
  `infiltration_difference` decimal(10,2) NOT NULL,
  `final_ft_1h_mm_h` decimal(10,2) NOT NULL,
  `runoff_potential` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `infiltration_data`
--

INSERT INTO `infiltration_data` (`id`, `soil_class`, `f0_mm_h`, `fc_mm_h`, `infiltration_difference`, `final_ft_1h_mm_h`, `runoff_potential`) VALUES
(1, 'Sand', 185.00, 40.00, 4.38, 44.38, 'Very Low'),
(2, 'Loam', 75.00, 7.50, 2.04, 9.54, 'Moderate'),
(3, 'Clay Loam', 20.00, 2.00, 0.54, 2.54, 'High'),
(4, 'Heavy Clay', 6.50, 0.65, 0.18, 0.83, 'Very High');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `location_name` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `elevation` decimal(10,2) DEFAULT NULL,
  `built_span` decimal(10,2) DEFAULT NULL,
  `garbage_accommodation` decimal(10,2) DEFAULT NULL,
  `building_density` decimal(10,2) DEFAULT NULL,
  `drainage_obstruction` decimal(10,2) DEFAULT NULL,
  `vegetation_cover` decimal(10,2) DEFAULT NULL,
  `impervious` varchar(100) DEFAULT NULL,
  `infiltration_capacity` varchar(100) DEFAULT NULL,
  `soil_type` varchar(100) DEFAULT NULL,
  `structure_type` varchar(100) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `shape` varchar(50) DEFAULT NULL,
  `manning_n` decimal(6,4) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `structural_condition` varchar(100) DEFAULT NULL,
  `hydraulic_capacity` varchar(100) DEFAULT NULL,
  `slope` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `drainage_conveyance` varchar(100) DEFAULT NULL,
  `recommendation` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `location_name`, `latitude`, `longitude`, `elevation`, `built_span`, `garbage_accommodation`, `building_density`, `drainage_obstruction`, `vegetation_cover`, `impervious`, `infiltration_capacity`, `soil_type`, `structure_type`, `dimensions`, `shape`, `manning_n`, `material`, `structural_condition`, `hydraulic_capacity`, `slope`, `created_at`, `drainage_conveyance`, `recommendation`) VALUES
(15, 'test', 10.24364700, 123.80940500, 3.21, 0.90, 1.00, 1.00, 1.00, 1.00, 'Building Roof Footprints / 136 Townhouse Units / 2651.86 / 28.12 / Roof Runoff / Structural Coverage', 'Heavy Clay / 6.50 / 0.65 / 0.18 / 0.83 / Very High', 'Clay Loam', 'Culvert', '0.6m x 0.6m', 'Rectangular', 1.0000, 'Concrete', 'Good', '2', -0.15, '2026-08-15 06:29:43', NULL, NULL),
(16, 'Line Ditch', 10.24460200, 123.81373500, 3.23, 1.00, 1.00, 28.12, 2.00, 45.00, 'Roadway & Pavement / Asphalt/Concrete Road Corridor / 9429.70 / 100.00 / Direct Surface Runoff', 'Clay Loam / 20.00 / 2.00 / 0.54 / 2.54 / High', 'Clay Loam', 'Line', '0.30 x 0.30', 'Rectangular', 0.0130, 'Concrete', 'Good', '0.17', -0.15, '2026-08-16 02:38:40', 'Good', 'test');

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
(1, 'Phase 1', 'reaeedentario@gmail.com', '2026-08-02 14:23:51');

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
-- Indexes for table `api_result`
--
ALTER TABLE `api_result`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_api_result_request` (`api_name`,`request_key`,`latitude`,`longitude`),
  ADD KEY `idx_api_result_expires` (`expires_at`),
  ADD KEY `idx_api_result_api_name` (`api_name`);

--
-- Indexes for table `elevation_survey_data`
--
ALTER TABLE `elevation_survey_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `segment_station` (`segment`,`station`);

--
-- Indexes for table `garbage_accumulation`
--
ALTER TABLE `garbage_accumulation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `manhole` (`manhole`);

--
-- Indexes for table `impervious_data`
--
ALTER TABLE `impervious_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `infiltration_data`
--
ALTER TABLE `infiltration_data`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `api_result`
--
ALTER TABLE `api_result`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `elevation_survey_data`
--
ALTER TABLE `elevation_survey_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `garbage_accumulation`
--
ALTER TABLE `garbage_accumulation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `impervious_data`
--
ALTER TABLE `impervious_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `infiltration_data`
--
ALTER TABLE `infiltration_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
