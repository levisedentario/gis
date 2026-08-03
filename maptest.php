<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sample Full Page Map</title>
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
      max-width: 280px;
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
    <h1>Sample Full-Page Map</h1>
    <p>This map fills the whole page and is centered on Minglanilla, Cebu.</p>
  </div>

  <div id="map"></div>

  <script>
    function initMap() {
      const center = { lat: 10.242412, lng: 123.808390 };

      const map = new google.maps.Map(document.getElementById("map"), {
        center: center,
        zoom: 20,
        mapTypeId: "hybrid",
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true
      });

      new google.maps.Marker({
        position: center,
        map: map,
        title: "Minglanilla, Cebu"
      });



      const pulseLocation = { lat: 10.242412, lng: 123.808390 };

      const pulseMarker = new google.maps.Marker({
        position: pulseLocation,
        map: map,
        title: "Pulse Point",
        icon: {
          path: google.maps.SymbolPath.CIRCLE,
          scale: 10,
          fillColor: "#ff0000",
          fillOpacity: 0.9,
          strokeColor: "#ffffff",
          strokeWeight: 2
        }
      });

      let pulseRadius = 0;
      const pulseCircle = new google.maps.Circle({
        map: map,
        center: pulseLocation,
        radius: 0,
        fillColor: "#ff0000",
        fillOpacity: 0.15,
        strokeColor: "#ff0000",
        strokeOpacity: 0.6,
        strokeWeight: 1
      });

      setInterval(() => {
        pulseRadius += 40;
        if (pulseRadius > 50) pulseRadius = 0;
        pulseCircle.setRadius(pulseRadius);
      }, 700);
    }
  </script>

  <script async defer src="./api.php?callback=initMap"></script>
</body>
</html>
