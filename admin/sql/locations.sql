 
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

INSERT INTO `locations` (`id`, `location_name`, `latitude`, `longitude`, `elevation`, `built_span`, `garbage_accommodation`, `building_density`, `drainage_obstruction`, `vegetation_cover`, `impervious`, `infiltration_capacity`, `soil_type`, `structure_type`, `dimensions`, `shape`, `manning_n`, `material`, `structural_condition`, `hydraulic_capacity`, `slope`, `created_at`, `drainage_conveyance`, `recommendation`) VALUES
(15, 'test', 10.24364700, 123.80940500, 3.21, 0.90, 1.00, 1.00, 1.00, 1.00, 'Building Roof Footprints / 136 Townhouse Units / 2651.86 / 28.12 / Roof Runoff / Structural Coverage', 'Heavy Clay / 6.50 / 0.65 / 0.18 / 0.83 / Very High', 'Clay Loam', 'Culvert', '0.6m x 0.6m', 'Rectangular', 1.0000, 'Concrete', 'Good', '2', -0.15, '2026-08-15 06:29:43', NULL, NULL),
(16, 'Line Ditch', 10.24460200, 123.81373500, 3.23, 1.00, 1.00, 28.12, 2.00, 45.00, 'Roadway & Pavement / Asphalt/Concrete Road Corridor / 9429.70 / 100.00 / Direct Surface Runoff', 'Clay Loam / 20.00 / 2.00 / 0.54 / 2.54 / High', 'Clay Loam', 'Line', '0.30 x 0.30', 'Rectangular', 0.0130, 'Concrete', 'Good', '0.17', -0.15, '2026-08-16 02:38:40', 'Good', 'test');
 
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);
 
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;
 