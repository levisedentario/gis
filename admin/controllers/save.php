<?php
require_once "../config/auth.php";
require_once "../config/db.php";
require_once "../includes/mitigation.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php?status=error&message=Invalid+request+method");
    exit;
}

$name = $_POST['location_name'];
$lat  = $_POST['latitude'];
$lng  = $_POST['longitude'];

$elevation = trim($_POST['elevation'] ?? '');
$builtSpan = trim($_POST['built_span'] ?? '');
$weatherDataSource = strtolower(trim($_POST['weather_data_source'] ?? 'realtime'));
$weatherInput = trim($_POST['weather_input'] ?? '');
$rainIntensity = trim($_POST['rain_intensity'] ?? '');
$rainIntensityUnit = trim($_POST['rain_intensity_unit'] ?? '');
$precipitationProbability = trim($_POST['precipitation_probability'] ?? '');
$garbageAccommodationInput = trim($_POST['garbage_accommodation'] ?? '');
$buildingDensity = trim($_POST['building_density'] ?? '');
$drainageObstruction = trim($_POST['drainage_obstruction'] ?? '');
$drainageConveyance = trim($_POST['drainage_conveyance'] ?? '');
$recommendation = trim($_POST['recommendation'] ?? '');
$vegetationCover = trim($_POST['vegetation_cover'] ?? '');
$structuralCondition = trim($_POST['structural_condition'] ?? '');
$hydraulicCapacity = trim($_POST['hydraulic_capacity'] ?? '');
$impervious = trim($_POST['impervious'] ?? '');
$infiltrationCapacity = trim($_POST['infiltration_capacity'] ?? '');
$soilType = trim($_POST['soil_type'] ?? '');
$structureType = trim($_POST['structure_type'] ?? '');
$dimensions = trim($_POST['dimensions'] ?? '');
$shape = trim($_POST['shape'] ?? '');
$manningN = trim($_POST['manning_n'] ?? '');
$material = trim($_POST['material'] ?? '');
$slope = trim($_POST['slope'] ?? '');

if (!in_array($weatherDataSource, ['manual', 'realtime'], true)) {
    $weatherDataSource = 'realtime';
}

if ($weatherDataSource !== 'manual') {
    $weatherInput = '';
    $rainIntensity = '';
    $rainIntensityUnit = '';
    $precipitationProbability = '';
}

$garbageAccommodation = '';

if ($garbageAccommodationInput !== '') {
    $garbageAccommodationId = filter_var($garbageAccommodationInput, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($garbageAccommodationId === false) {
        $_SESSION['flash'] = [
            'status' => 'error',
            'message' => 'Please select a valid manhole for garbage accumulation',
        ];
        header("Location: ../index.php");
        exit;
    }

    $manholeCheckStmt = $conn->prepare("SELECT id FROM garbage_accumulation WHERE id = ? LIMIT 1");
    $manholeCheckStmt->bind_param("i", $garbageAccommodationId);
    $manholeCheckStmt->execute();
    $manholeCheckResult = $manholeCheckStmt->get_result();

    if (!$manholeCheckResult || $manholeCheckResult->num_rows !== 1) {
        $manholeCheckStmt->close();
        $_SESSION['flash'] = [
            'status' => 'error',
            'message' => 'Selected manhole was not found',
        ];
        header("Location: ../index.php");
        exit;
    }

    $manholeCheckStmt->close();
    $garbageAccommodation = (string) $garbageAccommodationId;
}

$mitigationMeasures = fetchMitigationMeasures($conn);

if ($recommendation === '') {
    $recommendation = buildMitigationRecommendationText([
        'elevation' => $elevation,
        'built_span' => $builtSpan,
        'garbage_accommodation' => $garbageAccommodation,
        'building_density' => $buildingDensity,
        'drainage_obstruction' => $drainageObstruction,
        'vegetation_cover' => $vegetationCover,
        'hydraulic_capacity' => $hydraulicCapacity,
        'impervious' => $impervious,
        'infiltration_capacity' => $infiltrationCapacity,
        'soil_type' => $soilType,
        'structure_type' => $structureType,
        'dimensions' => $dimensions,
        'shape' => $shape,
        'manning_n' => $manningN,
        'material' => $material,
        'slope' => $slope,
        'weather_data_source' => $weatherDataSource,
        'weather_input' => $weatherInput,
        'rain_intensity' => $rainIntensity,
        'rain_intensity_unit' => $rainIntensityUnit,
        'precipitation_probability' => $precipitationProbability,
    ], $mitigationMeasures);
}

$stmt = $conn->prepare("\n    INSERT INTO locations\n    (\n        location_name, latitude, longitude, elevation, built_span, garbage_accommodation,\n        building_density, drainage_obstruction, drainage_conveyance, vegetation_cover,\n        structural_condition, recommendation, hydraulic_capacity, impervious,\n        infiltration_capacity, soil_type, structure_type, dimensions, shape, manning_n,\n        material, slope, weather_data_source, weather_input, rain_intensity, rain_intensity_unit, precipitation_probability\n    )\n    VALUES\n    (\n        ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, '')\n    )\n");

$params = [
    $name,
    $lat,
    $lng,
    $elevation,
    $builtSpan,
    $garbageAccommodation,
    $buildingDensity,
    $drainageObstruction,
    $drainageConveyance,
    $vegetationCover,
    $structuralCondition,
    $recommendation,
    $hydraulicCapacity,
    $impervious,
    $infiltrationCapacity,
    $soilType,
    $structureType,
    $dimensions,
    $shape,
    $manningN,
    $material,
    $slope,
    $weatherDataSource,
    $weatherInput,
    $rainIntensity,
    $rainIntensityUnit,
    $precipitationProbability,
];

$stmt->bind_param(str_repeat('s', count($params)), ...$params);

if ($stmt->execute()) {

    $_SESSION['flash'] = [
        'status' => 'success',
        'message' => 'Location saved successfully'
    ];
    header("Location: ../index.php");
    exit;

} else {

    $_SESSION['flash'] = [
        'status' => 'error',
        'message' => 'Failed to save location'
    ];
    header("Location: ../index.php");
    exit;

}

$stmt->close();
$conn->close();

?>

$mitigationMeasures = fetchMitigationMeasures($conn);

if ($recommendation === '') {
    $recommendation = buildMitigationRecommendationText([
        'elevation' => $elevation,
        'built_span' => $builtSpan,
        'garbage_accommodation' => $garbageAccommodation,
        'building_density' => $buildingDensity,
        'drainage_obstruction' => $drainageObstruction,
        'vegetation_cover' => $vegetationCover,
        'hydraulic_capacity' => $hydraulicCapacity,
        'impervious' => $impervious,
        'infiltration_capacity' => $infiltrationCapacity,
        'soil_type' => $soilType,
        'structure_type' => $structureType,
        'dimensions' => $dimensions,
        'shape' => $shape,
        'manning_n' => $manningN,
        'material' => $material,
        'slope' => $slope,
        'weather_data_source' => $weatherDataSource,
        'weather_input' => $weatherInput,
        'rain_intensity' => $rainIntensity,
        'rain_intensity_unit' => $rainIntensityUnit,
        'precipitation_probability' => $precipitationProbability,
    ], $mitigationMeasures);
}

$stmt = $conn->prepare("\n    INSERT INTO locations\n    (\n        location_name, latitude, longitude, elevation, built_span, garbage_accommodation,\n        building_density, drainage_obstruction, drainage_conveyance, vegetation_cover,\n*** End Patch
    exit;
}

$name = $_POST['location_name'];
$lat  = $_POST['latitude'];
$lng  = $_POST['longitude'];

$elevation = trim($_POST['elevation'] ?? '');
$builtSpan = trim($_POST['built_span'] ?? '');
$weatherDataSource = strtolower(trim($_POST['weather_data_source'] ?? 'realtime'));
$weatherInput = trim($_POST['weather_input'] ?? '');
$rainIntensity = trim($_POST['rain_intensity'] ?? '');
$rainIntensityUnit = trim($_POST['rain_intensity_unit'] ?? '');
$precipitationProbability = trim($_POST['precipitation_probability'] ?? '');
$garbageAccommodationInput = trim($_POST['garbage_accommodation'] ?? '');
$buildingDensity = trim($_POST['building_density'] ?? '');
$drainageObstruction = trim($_POST['drainage_obstruction'] ?? '');
$drainageConveyance = trim($_POST['drainage_conveyance'] ?? '');
$recommendation = trim($_POST['recommendation'] ?? '');
$vegetationCover = trim($_POST['vegetation_cover'] ?? '');
$structuralCondition = trim($_POST['structural_condition'] ?? '');
$hydraulicCapacity = trim($_POST['hydraulic_capacity'] ?? '');
$impervious = trim($_POST['impervious'] ?? '');
$infiltrationCapacity = trim($_POST['infiltration_capacity'] ?? '');
$soilType = trim($_POST['soil_type'] ?? '');
$structureType = trim($_POST['structure_type'] ?? '');
$dimensions = trim($_POST['dimensions'] ?? '');
$shape = trim($_POST['shape'] ?? '');
$manningN = trim($_POST['manning_n'] ?? '');
$material = trim($_POST['material'] ?? '');
$slope = trim($_POST['slope'] ?? '');

if (!in_array($weatherDataSource, ['manual', 'realtime'], true)) {
    $weatherDataSource = 'realtime';
}

if ($weatherDataSource !== 'manual') {
    $weatherInput = '';
    $rainIntensity = '';
    $rainIntensityUnit = '';
    $precipitationProbability = '';
}

$garbageAccommodation = '';

if ($garbageAccommodationInput !== '') {
    $garbageAccommodationId = filter_var($garbageAccommodationInput, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($garbageAccommodationId === false) {
        $_SESSION['flash'] = [
            'status' => 'error',
            'message' => 'Please select a valid manhole for garbage accumulation',
        ];
        header("Location: ../index.php");
        exit;
    }

    $manholeCheckStmt = $conn->prepare("SELECT id FROM garbage_accumulation WHERE id = ? LIMIT 1");
    $manholeCheckStmt->bind_param("i", $garbageAccommodationId);
    $manholeCheckStmt->execute();
    $manholeCheckResult = $manholeCheckStmt->get_result();

    if (!$manholeCheckResult || $manholeCheckResult->num_rows !== 1) {
        $manholeCheckStmt->close();
        $_SESSION['flash'] = [
            'status' => 'error',
            'message' => 'Selected manhole was not found',
        ];
        header("Location: ../index.php");
        exit;
    }

    $manholeCheckStmt->close();
    $garbageAccommodation = (string) $garbageAccommodationId;
}

$stmt = $conn->prepare("\n    INSERT INTO locations\n    (\n        location_name, latitude, longitude, elevation, built_span, garbage_accommodation,\n        building_density, drainage_obstruction, drainage_conveyance, vegetation_cover,\n        structural_condition, recommendation, hydraulic_capacity, impervious,\n        infiltration_capacity, soil_type, structure_type, dimensions, shape, manning_n,\n        material, slope, weather_data_source, weather_input, rain_intensity, rain_intensity_unit, precipitation_probability\n    )\n    VALUES\n    (\n        ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, ''),\n        NULLIF(?, ''), NULLIF(?, ''), NULLIF(?, '')\n    )\n");

$params = [
    $name,
    $lat,
    $lng,
    $elevation,
    $builtSpan,
    $garbageAccommodation,
    $buildingDensity,
    $drainageObstruction,
    $drainageConveyance,
    $vegetationCover,
    $structuralCondition,
    $recommendation,
    $hydraulicCapacity,
    $impervious,
    $infiltrationCapacity,
    $soilType,
    $structureType,
    $dimensions,
    $shape,
    $manningN,
    $material,
    $slope,
    $weatherDataSource,
    $weatherInput,
    $rainIntensity,
    $rainIntensityUnit,
    $precipitationProbability,
];

$stmt->bind_param(str_repeat('s', count($params)), ...$params);

if ($stmt->execute()) {

    $_SESSION['flash'] = [
        'status' => 'success',
        'message' => 'Location saved successfully'
    ];
    header("Location: ../index.php");
    exit;

} else {

    $_SESSION['flash'] = [
        'status' => 'error',
        'message' => 'Failed to save location'
    ];
    header("Location: ../index.php");
    exit;

}

$stmt->close();
$conn->close();

?>