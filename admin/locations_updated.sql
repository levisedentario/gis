
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";



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
  `impervious` decimal(10,2) DEFAULT NULL,
  `infiltration_capacity` decimal(10,2) DEFAULT NULL,
  `soil_type` varchar(100) DEFAULT NULL,
  `structure_type` varchar(100) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `shape` varchar(50) DEFAULT NULL,
  `manning_n` decimal(6,4) DEFAULT NULL,
  `material` varchar(100) DEFAULT NULL,
  `structural_condition` varchar(100) DEFAULT NULL,
  `hydraulic_capacity` varchar(100) DEFAULT NULL,
  `slope` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `locations` (`id`, `location_name`, `latitude`, `longitude`, `elevation`, `built_span`, `garbage_accommodation`, `building_density`, `drainage_obstruction`, `vegetation_cover`, `impervious`, `infiltration_capacity`, `soil_type`, `structure_type`, `dimensions`, `shape`, `manning_n`, `material`, `structural_condition`, `hydraulic_capacity`, `slope`, `created_at`) VALUES
(10, 'Deca 1', 10.24334500, 123.81051100, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, 1.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1.00, '2026-08-08 06:07:29');

ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
COMMIT;

