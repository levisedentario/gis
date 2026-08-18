<?php
require_once "config/auth.php";
require_once "config/db.php";
require_once "includes/api.php";
require_once "includes/mitigation.php";

$flash = $_SESSION['flash'] ?? null;
$status = is_array($flash) ? ($flash['status'] ?? '') : ($_GET['status'] ?? '');
$message = is_array($flash) ? ($flash['message'] ?? '') : ($_GET['message'] ?? '');
$editLocationId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
unset($_SESSION['flash']);

$garbageOptions = [];
$garbageOptionsResult = $conn->query("SELECT id, manhole FROM garbage_accumulation ORDER BY manhole ASC");

if ($garbageOptionsResult) {
    while ($garbageOption = $garbageOptionsResult->fetch_assoc()) {
        $garbageOptions[] = $garbageOption;
    }
 
    $garbageOptionsResult->free();
}

$infiltrationOptions = [];
$infiltrationOptionsResult = $conn->query("SELECT * FROM infiltration_data ORDER BY id ASC");

if ($infiltrationOptionsResult) {
    while ($infiltrationRow = $infiltrationOptionsResult->fetch_assoc()) {
        $labelParts = [];

        foreach ($infiltrationRow as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            $text = trim((string) $value);

            if ($text !== '') {
                $labelParts[] = $text;
            }
        }

        if (!empty($labelParts)) {
            $combinedValue = implode(' / ', $labelParts);
            $infiltrationOptions[] = [
                'value' => $combinedValue,
                'label' => $combinedValue,
            ];
        }
    }

    $infiltrationOptionsResult->free();
}

$imperviousOptions = [];
$imperviousOptionsResult = $conn->query("SELECT * FROM impervious_data ORDER BY id ASC");

if ($imperviousOptionsResult) {
    while ($imperviousRow = $imperviousOptionsResult->fetch_assoc()) {
        $labelParts = [];

        foreach ($imperviousRow as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            $text = trim((string) $value);

            if ($text !== '') {
                $labelParts[] = $text;
            }
        }

        if (!empty($labelParts)) {
            $combinedValue = implode(' / ', $labelParts);
            $imperviousOptions[] = [
                'value' => $combinedValue,
                'label' => $combinedValue,
            ];
        }
    }

    $imperviousOptionsResult->free();
}

$elevationSegments = [];
$elevationStationsResult = $conn->query("SELECT DISTINCT segment FROM elevation_survey_data ORDER BY segment ASC");

if ($elevationStationsResult) {
    while ($segmentRow = $elevationStationsResult->fetch_assoc()) {
        $segment = (int) $segmentRow['segment'];
        $stationsResult = $conn->query("SELECT station FROM elevation_survey_data WHERE segment = $segment ORDER BY station ASC");

        if ($stationsResult) {
            $stations = [];
            while ($stationRow = $stationsResult->fetch_assoc()) {
                $stations[] = $stationRow['station'];
            }
            $stationsResult->free();

            $elevationSegments[$segment] = $stations;
        }
    }
    $elevationStationsResult->free();
}

function getGarbageAccommodationLabel(array $row): string
{
    $manhole = trim((string) ($row['garbage_manhole'] ?? ''));

    if ($manhole !== '') {
        return $manhole;
    }

    return trim((string) ($row['garbage_accommodation'] ?? ''));
}

$result = mysqli_query($conn, "
    SELECT l.*, ga.manhole AS garbage_manhole
    FROM locations AS l
    LEFT JOIN garbage_accumulation AS ga
        ON ga.id = CAST(l.garbage_accommodation AS UNSIGNED)
    ORDER BY l.id DESC
");

$mitigationMeasures = fetchMitigationMeasures($conn);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .built-span-control {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .built-span-control input {
            flex: 1;
        }

        .btn-small {
            padding: 0.7rem 1rem;
            min-width: 110px;
        }

        .modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .modal.show {
            display: flex;
        }

        .modal-dialog {
            width: min(420px, calc(100% - 24px));
            background: #ffffff;
            border-radius: 12px;
            padding: 1.25rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .modal-header h4 {
            margin: 0;
            font-size: 1.2rem;
        }

        .close-modal {
            border: none;
            background: transparent;
            font-size: 1.5rem;
            cursor: pointer;
            color: #475569;
        }

        .modal-body {
            display: grid;
            gap: 0.85rem;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 0.5rem;
        }
    </style>
    <title>GIS Admin Panel</title>
</head>

<body>
    <div class="page">
        <div class="topbar">
            <h1>Location Management Dashboard</h1>
            <div class="user-actions"> <a class="nav-link" href="index.php">Home</a> <a class="nav-link" href="garbage_accumulation.php">Garbage Accumulation</a> <a class="nav-link" href="#">Option 2</a> <a class="nav-link" href="#">Option 3</a> <a class="nav-link" href="#">Option 4</a> <a class="nav-link" href="#">Option 5</a> <a class="nav-link" href="#">Option 6</a> <span class="welcome-text"> Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b> </span> | <a class="logout-link" href="controllers/logout.php">Logout</a> </div>
        </div>

        <div id="toast" class="toast"></div>

        <div class="container">

            <div class="left panel">
                <div class="panel-head">
                    <h3 class="panel-title">Pin A Location On Map</h3>
                </div>

                <div class="panel-body">
                    <div id="map"></div>
                    <p class="hint">Tap anywhere on the map to auto-fill coordinates.</p>

                    <form id="frm" action="controllers/save.php" method="POST">
                        <input type="hidden" name="id" id="id">

                        <div class="field">
                            <label for="name">Location Name</label>
                            <input type="text" name="location_name" id="name" placeholder="Ex: P1" required>
                        </div>

                        <div class="coordinates">
                            <div class="field">
                                <label for="lat">Latitude</label>
                                <input type="text" name="latitude" id="lat" readonly required>
                            </div>
                            <div class="field">
                                <label for="lng">Longitude</label>
                                <input type="text" name="longitude" id="lng" readonly required>
                            </div>
                        </div>

                        <div class="metrics-layout">
                            <div class="static-data">
                                <div class="static-data-label">Static Data</div>
                                <div class="static-data-grid">
                                    <div class="field-group">
                                        <div class="field-group-label">Topographic</div>
                                        <div class="field-group-grid">
                                            <div class="field">
                                                <label for="elevation">Elevation</label>
                                                <div class="built-span-control">
                                                    <input type="number" step="0.01" name="elevation" id="elevation" placeholder="Ex: 15.25" readonly>
                                                    <button type="button" class="btn btn-small" id="elevation_calc_btn">Survey Data</button>
                                                </div>
                                            </div>

                                            <div class="field">
                                                <label for="slope">Slope</label>
                                                <div class="built-span-control">
                                                    <input type="number" step="0.01" name="slope" id="slope" placeholder="Ex: 12.30" readonly>
                                                    <button type="button" class="btn btn-small" id="slope_calc_btn">Survey Data</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <div class="field-group-label">Infrastructure</div>
                                        <div class="field-group-grid">
                                            <div class="field">
                                                <label for="building_density">Building Density</label>
                                                <input type="number" step="0.01" name="building_density" id="building_density" placeholder="Ex: 68.00">
                                            </div>

                                            <div class="field">
                                                <label for="impervious">Impervious</label>
                                                <select name="impervious" id="impervious">
                                                    <option value="">Select impervious data</option>
                                                    <?php foreach ($imperviousOptions as $imperviousOption) { ?>
                                                        <option value="<?= htmlspecialchars($imperviousOption['value'], ENT_QUOTES) ?>"><?= htmlspecialchars($imperviousOption['label']) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <div class="field-group-label">Lithological</div>
                                        <div class="field-group-grid">
                                            <div class="field">
                                                <label for="infiltration_capacity">Infiltration Capacity</label>
                                                <select name="infiltration_capacity" id="infiltration_capacity">
                                                    <option value="">Select infiltration data</option>
                                                    <?php foreach ($infiltrationOptions as $infiltrationOption) { ?>
                                                        <option value="<?= htmlspecialchars($infiltrationOption['value'], ENT_QUOTES) ?>"><?= htmlspecialchars($infiltrationOption['label']) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="field">
                                                <label for="soil_type">Soil Type</label>
                                                <input type="text" name="soil_type" id="soil_type" placeholder="Ex: Clay loam">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <div class="field-group-label">Drainage System</div>
                                        <div class="field-group-grid">
                                            <div class="field">
                                                <label for="structure_type">Structure Type</label>
                                                <input type="text" name="structure_type" id="structure_type" placeholder="Ex: Culvert">
                                            </div>

                                            <div class="field">
                                                <label for="dimensions">Dimensions</label>
                                                <input type="text" name="dimensions" id="dimensions" placeholder="Ex: 0.6m x 0.6m">
                                            </div>

                                            <div class="field">
                                                <label for="shape">Shape</label>
                                                <input type="text" name="shape" id="shape" placeholder="Ex: Rectangular">
                                            </div>

                                            <div class="field">
                                                <label for="built_span">Built Span</label>
                                                <div class="built-span-control">
                                                    <input type="number" step="0.0001" name="built_span" id="built_span" placeholder="Ex: 0.9933" readonly>
                                                    <button type="button" class="btn btn-small" id="built_span_calc_btn">Calculate</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="field-group">
                                        <div class="field-group-label">Drainage Structure</div>
                                        <div class="field-group-grid">
                                            <div class="field">
                                                <label for="manning_n">Manning's n</label>
                                                <input type="number" step="0.0001" name="manning_n" id="manning_n" placeholder="Ex: 0.013">
                                            </div>

                                            <div class="field">
                                                <label for="material">Material</label>
                                                <input type="text" name="material" id="material" placeholder="Ex: Concrete">
                                            </div>
                                        </div>

                                        <div class="field">
                                    <label for="recommendation">Recommendation</label>
                                    <textarea name="recommendation" id="recommendation" rows="3" placeholder="Ex: Clear debris and improve channel maintenance."></textarea>
                                </div>
                                    </div>

                                </div>
                            </div>

                            <div class="non-static-data">
                                <div class="non-static-header">NON STATIC DATA</div>

                                <div class="field">
                                    <label for="garbage_accommodation">Garbage Accumulation</label>
                                    <select name="garbage_accommodation" id="garbage_accommodation">
                                        <option value="">Select manhole</option>
                                        <?php foreach ($garbageOptions as $garbageOption) { ?>
                                            <option value="<?= htmlspecialchars((string) $garbageOption['id']) ?>">
                                                <?= htmlspecialchars((string) $garbageOption['manhole']) ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="field">
                                    <label for="drainage_obstruction">Drainage Obstruction</label>
                                    <input type="number" step="0.01" name="drainage_obstruction" id="drainage_obstruction" placeholder="Ex: 2.00">
                                </div>

                                <div class="field">
                                    <label for="drainage_conveyance">Drainage Conveyance</label>
                                    <input type="text" name="drainage_conveyance" id="drainage_conveyance" placeholder="Ex: Adequate / Moderate / Poor">
                                </div>

                                <div class="field">
                                    <label for="vegetation_cover">Vegetation Cover</label>
                                    <input type="number" step="0.01" name="vegetation_cover" id="vegetation_cover" placeholder="Ex: 45.00">
                                </div>

                                <div class="field">
                                    <label for="structural_condition">Structural Condition</label>
                                    <input type="text" name="structural_condition" id="structural_condition" placeholder="Ex: Good">
                                </div>

                                <div class="field">
                                    <label for="hydraulic_capacity">Hydraulic Capacity</label>
                                    <input type="number" step="0.01" name="hydraulic_capacity" id="hydraulic_capacity" placeholder="Ex: 3.25">
                                </div>

                                 

                                <div class="field-group weather-data-group">
                                    <div class="field-group-label">Weather Data</div>
                                    <div class="field-group-grid">
                                        <div class="field">
                                            <label for="weather_data_source">Weather Data Source</label>
                                            <select name="weather_data_source" id="weather_data_source">
                                                <option value="realtime">Realtime</option>
                                                <option value="manual">Manual Input</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div id="weatherManualFields" hidden>
                                        <div class="field">
                                            <label for="weather_input">Weather Input / Notes</label>
                                            <textarea name="weather_input" id="weather_input" rows="3" placeholder="Enter the manual weather summary or notes."></textarea>
                                        </div>

                                        <div class="field-group-grid">
                                            <div class="field">
                                                <label for="rain_intensity">Rain Intensity</label>
                                                <input type="number" step="0.01" name="rain_intensity" id="rain_intensity" placeholder="Ex: 12.50">
                                            </div>

                                            <div class="field">
                                                <label for="rain_intensity_unit">Rain Intensity Unit</label>
                                                <input type="text" name="rain_intensity_unit" id="rain_intensity_unit" placeholder="Ex: mm">
                                            </div>

                                            <div class="field">
                                                <label for="precipitation_probability">Precipitation Probability</label>
                                                <input type="number" step="0.01" min="0" max="100" name="precipitation_probability" id="precipitation_probability" placeholder="Ex: 65.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button class="btn" id="btn" type="submit">Add Location</button>
                    </form>
                </div>
            </div>

            <div class="right panel">
                <div class="panel-head">
                    <h3 class="panel-title">Saved Locations</h3>

                    <div class="actions-wrap">
                        <form class="alert-form" method="GET" action="../phpmailer.php" onsubmit="return confirm('Send flood alert emails to subscribers in this phase?');">
                            <select name="phase">
                                <option value="phase1">Phase 1</option>
                                <option value="phase2">Phase 2</option>
                                <option value="phase3">Phase 3</option>
                                <option value="phase4">Phase 4</option>
                                <option value="phase5">Phase 5</option>
                            </select>
                            <button class="btn" type="submit">Send Alert</button>
                        </form>

                    </div>
                </div>

                <div class="panel-body">
                    <div class="table-wrap">
                        <table>

                            <thead>
                                <tr>
                                    <th>Location</th>
                                    <th>Latitude</th>
                                    <th>Longitude</th>
                                    <th>Elevation</th>
                                    <th>Built Span</th>
                                    <th>Garbage Accomm.</th>
                                    <th>Building Density</th>
                                    <th>Drainage Obstruction</th>
                                    <th>Drainage Conveyance</th>
                                    <th>Vegetation Cover</th>
                                    <th>Structural Condition</th>
                                    <th>Recommendation</th>
                                    <th>Hydraulic Capacity</th>
                                    <th>Impervious</th>
                                    <th>Infiltration Capacity</th>
                                    <th>Soil Type</th>
                                    <th>Structure Type</th>
                                    <th>Dimensions</th>
                                    <th>Shape</th>
                                    <th>Manning's n</th>
                                    <th>Material</th>
                                    <th>Slope</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                                    <tr>

                                        <td><?= htmlspecialchars($row['location_name']) ?></td>

                                        <td><?= number_format($row['latitude'], 6) ?></td>

                                        <td><?= number_format($row['longitude'], 6) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['elevation'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['built_span'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars(getGarbageAccommodationLabel($row)) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['building_density'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['drainage_obstruction'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['drainage_conveyance'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['vegetation_cover'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['structural_condition'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['recommendation'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['hydraulic_capacity'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['impervious'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['infiltration_capacity'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['soil_type'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['structure_type'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['dimensions'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['shape'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['manning_n'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['material'] ?? '')) ?></td>

                                        <td><?= htmlspecialchars((string) ($row['slope'] ?? '')) ?></td>

                                        <td>
                                            <div class="row-actions">
                                                <button
                                                    class="row-btn edit"
                                                    type="button"
                                                    id="edit-location-<?= (int) $row['id'] ?>"
                                                    data-location='<?= htmlspecialchars(json_encode([
                                                        (string) $row['id'],
                                                        (string) $row['location_name'],
                                                        (string) $row['latitude'],
                                                        (string) $row['longitude'],
                                                        (string) ($row['elevation'] ?? ''),
                                                        (string) ($row['built_span'] ?? ''),
                                                        (string) ($row['garbage_accommodation'] ?? ''),
                                                        (string) ($row['building_density'] ?? ''),
                                                        (string) ($row['drainage_obstruction'] ?? ''),
                                                        (string) ($row['drainage_conveyance'] ?? ''),
                                                        (string) ($row['vegetation_cover'] ?? ''),
                                                        (string) ($row['structural_condition'] ?? ''),
                                                        (string) ($row['recommendation'] ?? ''),
                                                        (string) ($row['hydraulic_capacity'] ?? ''),
                                                        (string) ($row['impervious'] ?? ''),
                                                        (string) ($row['infiltration_capacity'] ?? ''),
                                                        (string) ($row['soil_type'] ?? ''),
                                                        (string) ($row['structure_type'] ?? ''),
                                                        (string) ($row['dimensions'] ?? ''),
                                                        (string) ($row['shape'] ?? ''),
                                                        (string) ($row['manning_n'] ?? ''),
                                                        (string) ($row['material'] ?? ''),
                                                        (string) ($row['slope'] ?? ''),
                                                        (string) ($row['weather_data_source'] ?? ''),
                                                        (string) ($row['weather_input'] ?? ''),
                                                        (string) ($row['rain_intensity'] ?? ''),
                                                        (string) ($row['rain_intensity_unit'] ?? ''),
                                                        (string) ($row['precipitation_probability'] ?? '')
                                                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8') ?>'>
                                                    Edit
                                                </button>

                                                <a class="row-btn delete" href="controllers/delete.php?id=<?= $row['id'] ?>"
                                                    onclick="return confirm('Delete this location?')">
                                                    Delete
                                                </a>
                                            </div>
                                        </td>

                                    </tr>

                                <?php } ?>

                            </tbody>

                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="builtSpanModal" class="modal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="builtSpanModalTitle">
            <div class="modal-header">
                <h4 id="builtSpanModalTitle">Built Span Calculator</h4>
                <button type="button" class="close-modal" aria-label="Close modal" id="closeBuiltSpanModal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="field">
                    <label for="modal_year_built">Year Built</label>
                    <input type="number" id="modal_year_built" min="1900" max="2100" placeholder="Ex: 2010">
                </div>

                <div class="field">
                    <label for="modal_current_year">Current Year</label>
                    <input type="number" id="modal_current_year" min="1900" max="2100" placeholder="Ex: 2026">
                </div>

                <div class="field">
                    <label for="modal_age_score">Age Score Result</label>
                    <input type="number" id="modal_age_score" step="0.0001" readonly placeholder="0.0000">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-small" id="computeBuiltSpanScore">Compute</button>
                    <button type="button" class="btn btn-small" id="applyBuiltSpanScore">Use Result</button>
                </div>
            </div>
        </div>
    </div>

    <div id="elevationSlopeModal" class="modal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="elevationSlopeModalTitle">
            <div class="modal-header">
                <h4 id="elevationSlopeModalTitle">Elevation & Slope Calculator</h4>
                <button type="button" class="close-modal" aria-label="Close modal" id="closeElevationSlopeModal">&times;</button>
            </div>

            <div class="modal-body">
                <div class="field">
                    <label for="modal_segment">Segment</label>
                    <select id="modal_segment">
                        <option value="">Select segment</option>
                        <?php foreach ($elevationSegments as $segment => $stations) { ?>
                            <option value="<?= $segment ?>">Segment <?= $segment ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="field">
                    <label for="modal_station">Station</label>
                    <select id="modal_station">
                        <option value="">Select station</option>
                    </select>
                </div>

                <div class="field">
                    <label for="modal_elevation_type">Elevation Type</label>
                    <select id="modal_elevation_type">
                        <option value="finished_grade">Finished Grade Elevation</option>
                        <option value="drainage_floor">Drainage Floor Line Elevation</option>
                    </select>
                </div>

                <div class="field">
                    <label for="modal_elevation_value">Elevation (m)</label>
                    <input type="number" id="modal_elevation_value" step="0.01" readonly placeholder="0.00">
                </div>

                <div class="field">
                    <label for="modal_slope_value">Slope (°)</label>
                    <input type="number" id="modal_slope_value" step="0.01" readonly placeholder="0.00">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-small" id="applyElevationSlopeData">Use These Values</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.mitigationMeasures = <?= json_encode($mitigationMeasures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>
    <script src="js/dashboard.js"></script>
    <script>
        <?php if ($status === 'success' && $message !== ''): ?>
            showToast('<?= addslashes(htmlspecialchars($message)) ?>', 'success');
        <?php elseif ($status === 'error' && $message !== ''): ?>
            showToast('<?= addslashes(htmlspecialchars($message)) ?>', 'error');
        <?php endif; ?>

        if (window.history && window.history.replaceState) {
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }

        initMap();

        <?php if ($editLocationId !== false && $editLocationId !== null): ?>
            document.getElementById('edit-location-<?= (int) $editLocationId ?>')?.click();
        <?php endif; ?>
    </script>

</body>

</html>