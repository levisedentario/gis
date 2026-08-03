<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Phase 2 Flood Map</title>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      overflow: hidden;
      font-family: Arial, Helvetica, sans-serif;
      background: #f2f2f2;
    }

    #map {
      width: 100%;
      height: 100vh;
    }

    .info-card {
      position: fixed;
      top: 20px;
      left: 20px;
      z-index: 10;
      background: rgba(255, 255, 255, 0.95);
      padding: 16px 18px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
      max-width: 320px;
    }

    .info-card h1 {
      margin: 0 0 8px;
      font-size: 18px;
      color: #1f2937;
    }

    .info-card p {
      margin: 0;
      font-size: 14px;
      color: #4b5563;
      line-height: 1.4;
    }
  </style>
</head>
<body>
  <div class="info-card">
    <h1>Phase 2 Flood Locations</h1>
    <p>Showing the database points for Phase 2 as smaller pulsing markers.</p>
  </div>

  <div id="map"></div>

  <script>
    <?php
    require_once __DIR__ . '/admin/config/db.php';

    $phaseFilter = isset($_GET['phase']) ? strtoupper(trim($_GET['phase'])) : 'FP';
    $phaseFilter = preg_replace('/[^A-Za-z0-9]/', '', $phaseFilter);
    $phasePattern = $phaseFilter . '%';

    $stmt = $conn->prepare("SELECT location_name, latitude, longitude FROM locations WHERE location_name LIKE ? ORDER BY id ASC");
    $stmt->bind_param('s', $phasePattern);
    $stmt->execute();

    $result = $stmt->get_result();
    $locations = [];

    while ($row = $result->fetch_assoc()) {
      $locations[] = [
        'name' => $row['location_name'],
        'lat' => (float) $row['latitude'],
        'lng' => (float) $row['longitude'],
      ];
    }

    $stmt->close();
    $conn->close();
    ?>

    function initMap() {
      const locations = <?= json_encode($locations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
      const fallbackCenter = { lat: 10.242412, lng: 123.808390 };
      const center = locations.length ? { lat: locations[0].lat, lng: locations[0].lng } : fallbackCenter;

      const map = new google.maps.Map(document.getElementById("map"), {
        center: center,
        zoom: 17,
        mapTypeId: "hybrid",
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true
      });

      if (!locations.length) {
        new google.maps.Marker({
          position: center,
          map: map,
          title: "No Phase 2 locations found"
        });
        return;
      }

      const bounds = new google.maps.LatLngBounds();

      locations.forEach((location) => {
        const position = { lat: location.lat, lng: location.lng };

        const marker = new google.maps.Marker({
          position,
          map,
          title: location.name,
          label: {
            text: location.name,
            color: "#111827",
            fontSize: "12px",
            fontWeight: "600"
          },
          icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 4.2,
            fillColor: "#ff0000",
            fillOpacity: 0.95,
            strokeColor: "#ffffff",
            strokeWeight: 1.4
          }
        });

        const infoWindow = new google.maps.InfoWindow({
          content: `<div style="font-size:13px; font-weight:600;">${location.name}</div>`
        });

        marker.addListener("click", () => {
          infoWindow.open({
            anchor: marker,
            map
          });
        });

        const pulseCircle = new google.maps.Circle({
          map,
          center: position,
          radius: 0,
          fillColor: "#ff0000",
          fillOpacity: 0.12,
          strokeColor: "#ff0000",
          strokeOpacity: 0.75,
          strokeWeight: 1
        });

        let pulseRadius = 0;
        setInterval(() => {
          pulseRadius += 4;
          if (pulseRadius > 18) pulseRadius = 0;
          pulseCircle.setRadius(pulseRadius);
        }, 700);

        bounds.extend(position);
      });

      map.fitBounds(bounds, 40);
    }
  </script>

  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD0l9eHd6W2hsZpv4ddvLfaSS55JLt7c7M&callback=initMap"></script>
</body>
</html>
