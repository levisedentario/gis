<?php
require_once "config/auth.php";
require_once "config/db.php";
require_once "includes/api.php";

$filter = $_GET['filter'] ?? 'All';
$status = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';

if ($filter == "All") {
    $result = mysqli_query($conn, "SELECT * FROM locations ORDER BY id DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM locations WHERE location_name = ? ORDER BY id DESC");
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $result = $stmt->get_result();
}

$metricLabels = [
    '1' => 'Very Low',
    '2' => 'Low',
    '3' => 'High',
    '4' => 'Very High',
];

function renderMetricLabel($value, $metricLabels)
{
    $trimmedValue = trim((string) $value);

    if ($trimmedValue === '') {
        return '';
    }

    if (is_numeric($trimmedValue)) {
        $numericValue = (float) $trimmedValue;

        if (abs($numericValue - round($numericValue)) < 0.00001) {
            $integerValue = (string) (int) round($numericValue);

            if (isset($metricLabels[$integerValue])) {
                return $metricLabels[$integerValue];
            }
        }
    }

    return $trimmedValue;
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>GIS Admin Panel</title>

    <style>
        :root {
            --bg: #f1f6f3;
            --bg-accent: #dbece2;
            --card: #ffffff;
            --text: #1a2b24;
            --muted: #5e7068;
            --line: #d5e2db;
            --primary: #1e7f5e;
            --primary-strong: #16684c;
            --danger: #d64545;
            --warning: #f0a31f;
            --shadow: 0 20px 45px rgba(20, 58, 44, 0.12);
            --radius-lg: 18px;
            --radius-md: 12px;
            --radius-sm: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Manrope", sans-serif;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(circle at 8% 10%, rgba(30, 127, 94, 0.2), transparent 35%),
                radial-gradient(circle at 92% 0%, rgba(59, 148, 120, 0.2), transparent 42%),
                linear-gradient(135deg, var(--bg) 0%, #f9fcfa 55%, var(--bg-accent) 100%);
            padding: 22px;
        }

        .page {
            width: 100%;
            max-width: none;
            margin: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            box-shadow: 0 10px 25px rgba(18, 55, 40, 0.08);
        }

        .topbar h1 {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: 0.02em;
        }

        .user-actions {
            font-size: 0.95rem;
            color: var(--muted);
        }

        .user-actions b {
            color: var(--text);
            font-weight: 700;
        }

        .logout-link {
            margin-left: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        .container {
            display: flex;
            flex-direction: column;
            gap: 22px;
            align-items: stretch;
        }

        .panel {
            width: 100%;
            background: var(--card);
            border: 1px solid #e7efea;
          
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .panel-head {
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .panel-title {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text);
        }

        .left .panel-body,
        .right .panel-body {
            padding: 18px 20px 20px;
        }

        #map {
            height: 430px;
            border-radius: var(--radius-md);
            border: 1px solid #d8e4dd;
            margin-bottom: 16px;
        }

        .hint {
            font-size: 0.85rem;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .field {
            margin-bottom: 12px;
        }

        .field label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--muted);
        }

        input,
        select,
        button {
            font-family: inherit;
        }

        input,
        select {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cadbd2;
            border-radius: var(--radius-sm);
            font-size: 0.94rem;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: 0.2s border-color ease, 0.2s box-shadow ease;
        }

        input:focus,
        select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(30, 127, 94, 0.16);
        }

        .coordinates {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .btn {
            padding: 11px 16px;
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 700;
            font-size: 0.92rem;
            color: #fff;
            transition: 0.2s transform ease, 0.2s box-shadow ease, 0.2s background ease;
            background: linear-gradient(135deg, var(--primary) 0%, #2e9a75 100%);
            box-shadow: 0 8px 18px rgba(30, 127, 94, 0.25);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 20px rgba(30, 127, 94, 0.3);
            background: linear-gradient(135deg, var(--primary-strong) 0%, #258a67 100%);
        }

        .actions-wrap {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .alert-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .alert-form select,
        .filter-form select {
            min-width: 120px;
            margin: 0;
        }

        .alert-form .btn {
            padding: 10px 14px;
        }

        .right {
            max-height: none;
            overflow: visible;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1500px;
            font-size: 0.8rem;
        }

        thead th {
            background: #edf4ef;
            color: #2a4b3d;
            font-size: 0.68rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            padding: 10px 8px;
            border-bottom: 1px solid #d2e0d8;
        }

        tbody td {
            font-size: 0.77rem;
            padding: 9px 8px;
            border-bottom: 1px solid #ebf1ed;
            text-align: center;
        }

        tbody tr:hover {
            background: #f8fbf9;
        }

        .row-actions {
            display: flex;
            justify-content: center;
            gap: 7px;
        }

        .row-btn {
            border: none;
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s filter ease;
        }

        .row-btn:hover {
            filter: brightness(0.94);
        }

        .row-btn.edit {
            background: var(--warning);
            color: #342000;
        }

        .row-btn.delete {
            background: var(--danger);
            color: #fff;
        }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 12px 16px;
            border-radius: 8px;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            pointer-events: none;
            max-width: 320px;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        .toast.success {
            background: #198754;
        }

        .toast.error {
            background: #dc3545;
        }

        @media (max-width: 1100px) {
            .container {
                display: flex;
            }

            .right {
                max-height: none;
            }
        }

        @media (max-width: 680px) {
            body {
                padding: 14px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .actions-wrap,
            .alert-form {
                width: 100%;
            }

            .alert-form {
                display: grid;
                grid-template-columns: 1fr;
            }

            .coordinates {
                grid-template-columns: 1fr;
            }

            .metrics-grid {
                grid-template-columns: 1fr;
            }

            .panel-head {
                align-items: flex-start;
            }

            #map {
                height: 340px;
            }
        }
    </style>

</head>

<body>
    <div class="page">
        <div class="topbar">
            <h1>Location Management Dashboard</h1>
            <div class="user-actions">
                Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b>
                |
                <a class="logout-link" href="controllers/logout.php">Logout</a>
            </div>
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

                        <div class="metrics-grid">
                            <div class="field">
                                <label for="elevation">Elevation</label>
                                <input type="number" step="0.01" name="elevation" id="elevation" placeholder="Ex: 15.25">
                            </div>

                            <div class="field">
                                <label for="built_span">Built Span</label>
                                <input type="number" step="0.01" name="built_span" id="built_span" placeholder="Ex: 32.00">
                            </div>

                            <div class="field">
                                <label for="garbage_accommodation">Garbage Accomm.</label>
                                <select name="garbage_accommodation" id="garbage_accommodation">
                                    <option value="">Select a rating</option>
                                    <?php foreach ($metricLabels as $value => $label) { ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="building_density">Building Density</label>
                                <select name="building_density" id="building_density">
                                    <option value="">Select a rating</option>
                                    <?php foreach ($metricLabels as $value => $label) { ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="drainage_obstruction">Drainage Obstruction</label>
                                <select name="drainage_obstruction" id="drainage_obstruction">
                                    <option value="">Select a rating</option>
                                    <?php foreach ($metricLabels as $value => $label) { ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="vegetation_cover">Vegetation Cover</label>
                                <select name="vegetation_cover" id="vegetation_cover">
                                    <option value="">Select a rating</option>
                                    <?php foreach ($metricLabels as $value => $label) { ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="impervious">Impervious</label>
                                <select name="impervious" id="impervious">
                                    <option value="">Select a rating</option>
                                    <?php foreach ($metricLabels as $value => $label) { ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="infiltration_capacity">Infiltration Capacity</label>
                                <select name="infiltration_capacity" id="infiltration_capacity">
                                    <option value="">Select a rating</option>
                                    <?php foreach ($metricLabels as $value => $label) { ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="slope">Slope</label>
                                <select name="slope" id="slope">
                                    <option value="">Select a rating</option>
                                    <?php foreach ($metricLabels as $value => $label) { ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php } ?>
                                </select>
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

                        <form class="filter-form" method="GET">
                            <select name="filter" onchange="this.form.submit()">
                                <option value="All" <?= $filter == "All" ? "selected" : "" ?>>All</option>
                                <option value="P1" <?= $filter == "P1" ? "selected" : "" ?>>P1</option>
                                <option value="P2" <?= $filter == "P2" ? "selected" : "" ?>>P2</option>
                                <option value="P3" <?= $filter == "P3" ? "selected" : "" ?>>P3</option>
                                <option value="P4" <?= $filter == "P4" ? "selected" : "" ?>>P4</option>
                                <option value="P5" <?= $filter == "P5" ? "selected" : "" ?>>P5</option>
                                <option value="P6" <?= $filter == "P6" ? "selected" : "" ?>>P6</option>
                                <option value="FP" <?= $filter == "FP" ? "selected" : "" ?>>FP</option>
                            </select>
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
                                    <th>Vegetation Cover</th>
                                    <th>Impervious</th>
                                    <th>Infiltration Capacity</th>
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

                                        <td><?= htmlspecialchars(renderMetricLabel($row['garbage_accommodation'] ?? '', $metricLabels)) ?></td>

                                        <td><?= htmlspecialchars(renderMetricLabel($row['building_density'] ?? '', $metricLabels)) ?></td>

                                        <td><?= htmlspecialchars(renderMetricLabel($row['drainage_obstruction'] ?? '', $metricLabels)) ?></td>

                                        <td><?= htmlspecialchars(renderMetricLabel($row['vegetation_cover'] ?? '', $metricLabels)) ?></td>

                                        <td><?= htmlspecialchars(renderMetricLabel($row['impervious'] ?? '', $metricLabels)) ?></td>

                                        <td><?= htmlspecialchars(renderMetricLabel($row['infiltration_capacity'] ?? '', $metricLabels)) ?></td>

                                        <td><?= htmlspecialchars(renderMetricLabel($row['slope'] ?? '', $metricLabels)) ?></td>

                                        <td>
                                            <div class="row-actions">
                                                <button
                                                    class="row-btn edit"
                                                    type="button"
                                                    onclick="editLocation(
                                            '<?= $row['id'] ?>',
                                            '<?= htmlspecialchars($row['location_name'], ENT_QUOTES) ?>',
                                            '<?= $row['latitude'] ?>',
                                            '<?= $row['longitude'] ?>',
                                            '<?= htmlspecialchars((string) ($row['elevation'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['built_span'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['garbage_accommodation'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['building_density'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['drainage_obstruction'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['vegetation_cover'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['impervious'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['infiltration_capacity'] ?? ''), ENT_QUOTES) ?>',
                                            '<?= htmlspecialchars((string) ($row['slope'] ?? ''), ENT_QUOTES) ?>'
                                            )">
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
    <script>
        var map;
        var marker;

        function initMap() {
            var center = {
                lat: 10.243013056575343,
                lng: 123.81043633362316
            };

            map = new google.maps.Map(document.getElementById("map"), {
                center: center,
                zoom: 16,
                mapTypeControl: true,
                streetViewControl: false,
                fullscreenControl: true
            });

            map.addListener("click", function(e) {
                var clickedLat = e.latLng.lat();
                var clickedLng = e.latLng.lng();

                document.getElementById("lat").value = clickedLat.toFixed(6);
                document.getElementById("lng").value = clickedLng.toFixed(6);

                if (marker) {
                    marker.setPosition(e.latLng);
                } else {
                    marker = new google.maps.Marker({
                        position: e.latLng,
                        map: map
                    });
                }
            });
        }

        function editLocation(id, name, lat, lng, elevation, builtSpan, garbageAccommodation, buildingDensity, drainageObstruction, vegetationCover, impervious, infiltrationCapacity, slope) {
            var latitude = parseFloat(lat);
            var longitude = parseFloat(lng);
            var selectedPoint = {
                lat: latitude,
                lng: longitude
            };

            document.getElementById("id").value = id;
            document.getElementById("name").value = name;
            document.getElementById("lat").value = latitude.toFixed(6);
            document.getElementById("lng").value = longitude.toFixed(6);
            document.getElementById("elevation").value = elevation;
            document.getElementById("built_span").value = builtSpan;
            setMetricField("garbage_accommodation", garbageAccommodation);
            setMetricField("building_density", buildingDensity);
            setMetricField("drainage_obstruction", drainageObstruction);
            setMetricField("vegetation_cover", vegetationCover);
            setMetricField("impervious", impervious);
            setMetricField("infiltration_capacity", infiltrationCapacity);
            setMetricField("slope", slope);

            document.getElementById("frm").action = "controllers/update.php";
            document.getElementById("btn").innerHTML = "Update";

            if (map) {
                map.setCenter(selectedPoint);
                map.setZoom(17);
            }

            if (marker) {
                marker.setPosition(selectedPoint);
            } else {
                marker = new google.maps.Marker({
                    position: selectedPoint,
                    map: map
                });
            }
        }

        window.initMap = initMap;
        window.editLocation = editLocation;

        function setMetricField(fieldId, fieldValue) {
            var select = document.getElementById(fieldId);
            var value = fieldValue === null || fieldValue === undefined ? '' : String(fieldValue).trim();

            if (value === 'Very Low') {
                value = '1';
            } else if (value === 'Low') {
                value = '2';
            } else if (value === 'High') {
                value = '3';
            } else if (value === 'Very High') {
                value = '4';
            }

            if (select.querySelector('option[value="' + value.replace(/"/g, '\\"') + '"]')) {
                select.value = value;
            } else {
                select.value = '';
            }
        }

        function showToast(message, type = 'success') {
            var toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast show ' + type;
            clearTimeout(window.toastTimer);
            window.toastTimer = setTimeout(function() {
                toast.className = 'toast';
            }, 3000);
        }

        <?php if ($status === 'success' && $message !== ''): ?>
            showToast('<?= addslashes(htmlspecialchars($message)) ?>', 'success');
        <?php elseif ($status === 'error' && $message !== ''): ?>
            showToast('<?= addslashes(htmlspecialchars($message)) ?>', 'error');
        <?php endif; ?>

        if (window.history && window.history.replaceState) {
            const cleanUrl = window.location.origin + window.location.pathname;
            window.history.replaceState({}, document.title, cleanUrl);
        }
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode(GOOGLE_MAPS_API_KEY) ?>&callback=initMap"></script>

</body>

</html>