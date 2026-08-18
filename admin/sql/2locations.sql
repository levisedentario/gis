

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
  `weather_data_source` varchar(20) DEFAULT 'realtime',
  `weather_input` text DEFAULT NULL,
  `rain_intensity` decimal(10,2) DEFAULT NULL,
  `rain_intensity_unit` varchar(20) DEFAULT NULL,
  `precipitation_probability` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `drainage_conveyance` varchar(100) DEFAULT NULL,
  `recommendation` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);
 
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;
 