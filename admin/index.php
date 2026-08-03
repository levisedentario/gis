<?php
require_once "config/auth.php";
require_once "config/db.php";

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
?>

<!DOCTYPE html>
<html>

<head>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        body {
            background: #f4f6f9;
            padding: 20px;
        }

        .container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .left {
            width: 60%;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        }

        .right {
            width: 40%;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
            max-height: 720px;
            overflow-y: auto;
        }

        h2 {
            margin-bottom: 15px;
            color: #333;
        }

        #map {
            height: 400px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            outline: none;
        }

        input:focus {
            border-color: #0d6efd;
        }

        button {
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: #0d6efd;
            color: #fff;
            font-weight: bold;
        }

        button:hover {
            background: #0b5ed7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table thead {
            background: #0d6efd;
            color: #fff;
        }

        table th,
        table td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        table tbody tr:hover {
            background: #f8f9fa;
        }

        .edit-btn {
            background: #ffc107;
            color: #000;
            padding: 6px 10px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .delete-btn {
            background: #dc3545;
            color: #fff;
            padding: 6px 10px;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 5px;
        }

        .edit-btn:hover {
            background: #e0a800;
        }

        .delete-btn:hover {
            background: #bb2d3b;
        }

        .form-title {
            margin-bottom: 15px;
            color: #0d6efd;
        }

        .table-title {
            margin-bottom: 15px;
            color: #0d6efd;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background: #fff;
            font-size: 14px;
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
    </style>

</head>

<body>

    <div style="text-align:right;margin-bottom:15px;">

        Welcome,
        <b><?= htmlspecialchars($_SESSION['username']) ?></b>

        |

        <a href="controllers/logout.php">
            Logout
        </a>

    </div>

    <div id="toast" class="toast"></div>

    <div class="container">

        <div class="left">

            <div id="map"></div>

            <form id="frm" action="controllers/save.php" method="POST">

                <input type="hidden" name="id" id="id">

                <input type="text" name="location_name" id="name" placeholder="Location Name">

                <input type="text" name="latitude" id="lat" readonly>

                <input type="text" name="longitude" id="lng" readonly>

                <button id="btn">Add Location</button>

            </form>

        </div>

        <div class="right">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                <h3>Saved Locations</h3>

                <div style="display:flex;gap:10px;align-items:center;">
                    <form method="GET" action="../phpmailer.php" onsubmit="return confirm('Send flood alert emails to subscribers in this phase?');">
                        <select name="phase">
                            <option value="phase1">Phase 1</option>
                            <option value="phase2">Phase 2</option>
                            <option value="phase3">Phase 3</option>
                            <option value="phase4">Phase 4</option>
                            <option value="phase5">Phase 5</option>
                        </select>
                        <button type="submit">Send Alert</button>
                    </form>

                    <form method="GET">
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
            <table>

                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                        <tr>

                            <td><?= htmlspecialchars($row['location_name']) ?></td>

                            <td><?= number_format($row['latitude'], 6) ?></td>

                            <td><?= number_format($row['longitude'], 6) ?></td>

                            <td>

                                <button
                                    onclick="editLocation(
                '<?= $row['id'] ?>',
                '<?= htmlspecialchars($row['location_name'], ENT_QUOTES) ?>',
                '<?= $row['latitude'] ?>',
                '<?= $row['longitude'] ?>'
                )">
                                    Edit
                                </button>

                                <a href="controllers/delete.php?id=<?= $row['id'] ?>"
                                    onclick="return confirm('Delete this location?')">
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        var map = L.map('map').setView([10.243013056575343, 123.81043633362316], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        var marker;

        map.on('click', function(e) {

            document.getElementById("lat").value = e.latlng.lat;
            document.getElementById("lng").value = e.latlng.lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }

        });

        function editLocation(id, name, lat, lng) {

            document.getElementById("id").value = id;
            document.getElementById("name").value = name;
            document.getElementById("lat").value = lat;
            document.getElementById("lng").value = lng;

            document.getElementById("frm").action = "controllers/update.php";
            document.getElementById("btn").innerHTML = "Update";

            map.setView([lat, lng], 17);

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
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
    </script>

</body>

</html>