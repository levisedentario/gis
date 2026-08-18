
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


CREATE TABLE `mitigation_measures` (
  `id` int(11) NOT NULL,
  `dominant_factor` varchar(150) NOT NULL,
  `recommended_mitigation` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `mitigation_measures` (`id`, `dominant_factor`, `recommended_mitigation`) VALUES
(1, 'Low Hydraulic Capacity', 'Increase the hydraulic capacity of the drainage system by enlarging or upgrading drainage structures.'),
(2, 'Poor Structural Condition', 'Repair, rehabilitate, or replace damaged drainage structures.'),
(3, 'Small Drainage Dimensions', 'Upgrade undersized drainage structures based on hydraulic design standards.'),
(4, 'Inappropriate Drainage Structure Type', 'Replace the existing drainage structure with a more suitable drainage type.'),
(5, 'Inefficient Drainage Shape', 'Modify the drainage cross-section to improve flow efficiency.'),
(6, 'Old Built Span (age)', 'Prioritize rehabilitation or replacement of aging drainage infrastructure.'),
(7, 'Very Low Elevation', 'Improve drainage conveyance and provide additional drainage outlets where feasible.'),
(8, 'Low Slope', 'Improve drainage gradients or install supplementary drainage facilities to enhance runoff conveyance.'),
(9, 'High Rainfall Amount', 'Increase stormwater storage capacity and strengthen preventive drainage maintenance.'),
(10, 'High Rainfall Intensity', 'Upgrade drainage systems to accommodate high-intensity rainfall events and improve stormwater management.'),
(11, 'Low Infiltration Capacity', 'Construct infiltration trenches, infiltration wells, bioswales, or rain gardens to increase infiltration.'),
(12, 'High Building Density', 'Improve drainage planning and regulate future developments to minimize runoff generation.'),
(13, 'High Impervious Surface Percentage', 'Install permeable pavements and incorporate green infrastructure to reduce surface runoff.'),
(14, 'High Garbage Accumulation', 'Conduct regular waste collection and routine drainage cleaning to prevent blockage.'),
(15, 'High Drainage Obstruction', 'Remove accumulated debris and sediments through periodic inspection, declogging, and desilting.'),
(16, 'Low Vegetation Cover', 'Increase vegetation cover through tree planting, vegetated swales, and other green infrastructure practices.');

ALTER TABLE `mitigation_measures`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mitigation_measures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

