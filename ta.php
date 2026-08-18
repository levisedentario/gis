<?php
require_once __DIR__ . '/admin/config/db.php';

$stmt = $conn->prepare("
  SELECT
    l.id,
    l.location_name,
    l.latitude,
    l.longitude,
    l.elevation,
    l.built_span,
    l.building_density,
    l.drainage_obstruction,
    l.vegetation_cover,
    l.impervious,
    l.infiltration_capacity,
    l.slope,
    l.soil_type,
    l.structure_type,
    l.dimensions,
    l.shape,
    l.manning_n,
    l.material,
    l.drainage_conveyance,
    l.recommendation,
    l.weather_data_source,
    l.weather_input,
    l.rain_intensity,
    l.rain_intensity_unit,
    l.precipitation_probability,
    ga.manhole AS garbage_manhole,
    ga.flood_susceptibility AS garbage_flood_susceptibility
  FROM locations AS l
  LEFT JOIN garbage_accumulation AS ga
    ON ga.id = CAST(l.garbage_accommodation AS UNSIGNED)
  ORDER BY l.id ASC
");
$stmt->execute();

$result = $stmt->get_result();
$locations = [];

while ($row = $result->fetch_assoc()) {
  $locations[] = [
    'id' => (int) $row['id'],
    'name' => $row['location_name'],
    'lat' => (float) $row['latitude'],
    'lng' => (float) $row['longitude'],
    'elevation' => $row['elevation'] !== null ? (float) $row['elevation'] : null,
    'built_span' => $row['built_span'] !== null ? (float) $row['built_span'] : null,
    'garbage_manhole' => $row['garbage_manhole'],
    'garbage_flood_susceptibility' => $row['garbage_flood_susceptibility'],
    'building_density' => $row['building_density'] !== null ? (float) $row['building_density'] : null,
    'drainage_obstruction' => $row['drainage_obstruction'] !== null ? (float) $row['drainage_obstruction'] : null,
    'vegetation_cover' => $row['vegetation_cover'] !== null ? (float) $row['vegetation_cover'] : null,
    'impervious' => $row['impervious'] !== null ? (string) $row['impervious'] : null,
    'infiltration_capacity' => $row['infiltration_capacity'] !== null ? (string) $row['infiltration_capacity'] : null,
    'slope' => $row['slope'] !== null ? (float) $row['slope'] : null,
    'soil_type' => $row['soil_type'],
    'structure_type' => $row['structure_type'],
    'dimensions' => $row['dimensions'],
    'shape' => $row['shape'],
    'manning_n' => $row['manning_n'] !== null ? (float) $row['manning_n'] : null,
    'material' => $row['material'],
    'drainage_conveyance' => $row['drainage_conveyance'],
    'recommendation' => $row['recommendation'],
    'weather_data_source' => $row['weather_data_source'] !== null ? (string) $row['weather_data_source'] : null,
    'weather_input' => $row['weather_input'] !== null ? (string) $row['weather_input'] : null,
    'rain_intensity' => $row['rain_intensity'] !== null ? (float) $row['rain_intensity'] : null,
    'rain_intensity_unit' => $row['rain_intensity_unit'] !== null ? (string) $row['rain_intensity_unit'] : null,
    'precipitation_probability' => $row['precipitation_probability'] !== null ? (float) $row['precipitation_probability'] : null,
  ];
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Source+Serif+4:ital,wght@0,400;0,600&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <title>Target Area Flood Map</title>
  <style>
    :root {
      --ink: #0E2430;
      --ink-soft: #274A55;
      --paper: #EAF0ED;
      --paper-2: #DDE7E2;
      --water: #2E6F9E;
      --water-deep: #153A52;
      --flood: #D9622B;
      --line: rgba(14, 36, 48, 0.14);
    }

    * {
      box-sizing: border-box;
    }

    html,
    body {
      height: 100%;
      margin: 0;
      overflow: hidden;
      font-family: 'Source Serif 4', Georgia, serif;
      background: var(--paper);
      color: var(--ink);
    }

    #map {
      width: 100%;
      height: 100vh;
    }

    /* ---------- Floating info panel ---------- */
    .info-card {
      position: fixed;
      top: 18px;
      left: 18px;
      z-index: 900;
      width: 300px;
      max-width: calc(100vw - 36px);
      background: rgba(255, 255, 255, 0.97);
      border: 1px solid var(--line);
      border-radius: 14px;
      box-shadow: 0 12px 32px rgba(14, 36, 48, 0.22);
      overflow: hidden;
    }

    .info-card.collapsed .info-card-body {
      display: none;
    }

    .info-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 12px 14px;
      background: var(--water-deep);
      color: #fff;
    }

    .info-card-header h1 {
      margin: 0;
      font-family: 'Fraunces', serif;
      font-size: 15.5px;
      font-weight: 600;
      letter-spacing: -0.01em;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .panel-toggle {
      border: none;
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      width: 26px;
      height: 26px;
      border-radius: 8px;
      font-size: 15px;
      line-height: 1;
      cursor: pointer;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.2s ease;
    }

    .panel-toggle:hover {
      background: rgba(255, 255, 255, 0.28);
    }

    .info-card-body {
      padding: 14px 16px 16px;
    }

    .back-button {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 12px;
      padding: 7px 12px;
      background: var(--flood);
      color: #fff;
      text-decoration: none;
      border-radius: 999px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      transition: background 0.2s ease;
    }

    .back-button:hover {
      background: #b94e1e;
    }

    .info-card-body p {
      margin: 0 0 10px;
      font-size: 13.5px;
      line-height: 1.55;
      color: var(--ink-soft);
    }

    .location-guide {
      margin: 0 0 8px;
      padding: 9px 10px;
      border: 1px solid var(--line);
      border-radius: 10px;
      background: var(--paper);
      color: var(--ink);
      font-size: 12.5px;
      line-height: 1.5;
      display: flex;
      align-items: flex-start;
      gap: 8px;
    }

    .location-guide:last-of-type {
      margin-bottom: 0;
    }

    .location-guide .guide-icon {
      font-size: 14px;
      line-height: 1.3;
    }

    .info-card-footer {
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px dashed var(--line);
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--ink-soft);
    }

    /* ---------- Modal ---------- */
    .modal {
      position: fixed;
      inset: 0;
      z-index: 1000;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      background: rgba(14, 36, 48, 0.55);
      backdrop-filter: blur(2px);
    }

    .modal.show {
      display: flex;
    }

    .modal-content {
      width: min(560px, 100%);
      max-height: 88vh;
      overflow-y: auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 24px 60px rgba(14, 36, 48, 0.35);
      position: relative;
      animation: modalPop 0.18s ease;
    }

    @keyframes modalPop {
      from {
        transform: scale(0.96);
        opacity: 0;
      }

      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    .modal-close {
      position: absolute;
      top: 12px;
      right: 14px;
      border: none;
      background: rgba(255, 255, 255, 0.85);
      width: 30px;
      height: 30px;
      border-radius: 50%;
      font-size: 20px;
      line-height: 1;
      cursor: pointer;
      color: var(--ink);
      z-index: 2;
    }

    .modal-close:hover {
      background: #fff;
    }

    .modal-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 20px 50px 16px 22px;
      background: linear-gradient(135deg, var(--water-deep), var(--water));
      color: #fff;
      border-radius: 16px 16px 0 0;
    }

    .modal-header-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
    }

    .modal-header h2 {
      margin: 0;
      font-family: 'Fraunces', serif;
      font-size: 19px;
      font-weight: 600;
    }

    .modal-subtitle {
      margin: 2px 0 0;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      letter-spacing: 0.03em;
      color: rgba(255, 255, 255, 0.85);
    }

    .modal-body {
      padding: 18px 22px 22px;
    }

    .report-link-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      width: 100%;
      margin-bottom: 16px;
      padding: 11px 14px;
      background: var(--water);
      color: #fff;
      text-decoration: none;
      border-radius: 10px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.04em;
      transition: background 0.2s ease;
    }

    .report-link-btn:hover {
      background: var(--water-deep);
    }

    .info-section {
      margin-bottom: 18px;
    }

    .info-section:last-child {
      margin-bottom: 0;
    }

    .section-title {
      display: flex;
      align-items: center;
      gap: 8px;
      margin: 0 0 10px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--water-deep);
      font-weight: 700;
    }

    .section-icon {
      font-size: 14px;
    }

    .info-grid {
      border: 1px solid var(--line);
      border-radius: 12px;
      overflow: hidden;
    }

    .info-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 12px;
      align-items: center;
      padding: 9px 12px;
      font-size: 13.5px;
      background: #fff;
    }

    .info-row:nth-child(even) {
      background: #f8fafc;
    }

    .info-row+.info-row {
      border-top: 1px solid var(--line);
    }

    .info-label {
      font-weight: 600;
      color: var(--ink-soft);
    }

    .info-value {
      text-align: right;
      color: var(--ink);
      word-break: break-word;
    }

    .weather-section .info-grid {
      border-color: rgba(46, 111, 158, 0.25);
    }

    .weather-section .info-row {
      background: #eef5fb;
    }

    .weather-section .info-row:nth-child(even) {
      background: #e4eef8;
    }

    .weather-section .info-label {
      color: var(--water-deep);
    }

    /* ---------- Recommendation accordion ---------- */
    .accordion-toggle {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      padding: 10px 12px;
      border: 1px solid var(--line);
      border-radius: 12px;
      background: #fff;
      cursor: pointer;
      font: inherit;
      text-align: left;
    }

    .accordion-toggle .section-title {
      margin: 0;
    }

    .accordion-toggle:hover {
      background: #f8fafc;
    }

    .accordion-chevron {
      transition: transform 0.2s ease;
      color: var(--ink-soft);
      font-size: 12px;
      flex-shrink: 0;
    }

    .accordion-toggle[aria-expanded="true"] .accordion-chevron {
      transform: rotate(180deg);
    }

    .accordion-panel {
      display: none;
      margin-top: 8px;
      border: 1px solid var(--line);
      border-radius: 12px;
      padding: 10px 14px;
      background: #f8fafc;
    }

    .accordion-panel.open {
      display: block;
    }

    .recommendation-list {
      margin: 0;
      padding-left: 18px;
      display: flex;
      flex-direction: column;
      gap: 8px;
      font-size: 13.5px;
      line-height: 1.5;
      color: var(--ink);
    }

    .recommendation-list li::marker {
      color: var(--flood);
    }

    .recommendation-empty {
      margin: 0;
      font-size: 13px;
      color: var(--ink-soft);
    }

    /* ---------- Badges ---------- */
    .badge {
      display: inline-block;
      padding: 3px 11px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 12px;
      letter-spacing: 0.01em;
    }

    .badge.risk-low,
    .badge.status-low,
    .badge.status-very-low {
      background: #dcfce7;
      color: #15803d;
    }

    .badge.risk-moderate,
    .badge.status-moderate {
      background: #fef3c7;
      color: #b45309;
    }

    .badge.risk-high,
    .badge.status-high {
      background: #fee2e2;
      color: #b91c1c;
    }

    .badge.status-very-high {
      background: #fecaca;
      color: #7f1d1d;
    }

    .badge.weather-none {
      background: #ccfbf1;
      color: #0f766e;
    }

    .badge.weather-light {
      background: #dbeafe;
      color: #1d4ed8;
    }

    .badge.weather-moderate {
      background: #fef3c7;
      color: #b45309;
    }

    .badge.weather-heavy {
      background: #ffedd5;
      color: #c2410c;
    }

    .badge.weather-very-heavy {
      background: #fee2e2;
      color: #dc2626;
    }

    .badge.weather-extreme {
      background: #fecaca;
      color: #7f1d1d;
    }

    @media (max-width: 640px) {
      .info-card {
        width: calc(100vw - 32px);
      }

      .modal-content {
        max-height: 92vh;
      }

      .modal-header {
        padding: 18px 44px 14px 16px;
      }

      .modal-body {
        padding: 14px 16px 18px;
      }
    }
  </style>
</head>

<body>
  <div id="map"></div>

  <div class="info-card" id="infoCard">
    <div class="info-card-header">
      <h1><span aria-hidden="true">🌊</span> Target Flood Locations</h1>
      <button class="panel-toggle" id="panelToggle" type="button" aria-label="Collapse panel" aria-expanded="true">−</button>
    </div>
    <div class="info-card-body">
      <a href="mapping.php" class="back-button">← Back to Map</a>
      <p>Explore surveyed points across the target area. Each pulsing marker represents a monitored flood-risk location.</p>
      <p class="location-guide"><span class="guide-icon" aria-hidden="true">&#128205;</span> Pulsing markers show monitored locations.</p>
      <p class="location-guide"><span class="guide-icon" aria-hidden="true">&#128072;</span> Click a marker to view full site details.</p>
      <div class="info-card-footer"><?= count($locations) ?> Location<?= count($locations) === 1 ? '' : 's' ?> Loaded</div>
    </div>
  </div>

  <div id="locationModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
      <button class="modal-close" id="closeModal" type="button" aria-label="Close">×</button>
      <div class="modal-header">
        <div class="modal-header-icon" aria-hidden="true">📍</div>
        <div>
          <h2 id="modalTitle">Location Details</h2>
          <p class="modal-subtitle" id="modalSubtitle"></p>
        </div>
      </div>
      <div id="modalBody" class="modal-body"></div>
    </div>
  </div>

  <script>
    function initMap() {
      // ===== CONFIGURATION: Add your weather API endpoints here =====
      // Format: { baseUrl: "https://...", apikey: "your_key_here" }
      const weatherApiEndpoints = [
        {
          baseUrl: "https://my.meteoblue.com/packages/basic-day",
          apikey: "Be8VLcNijdqtDfZxx"
        },
        {
          baseUrl: "https://my.meteoblue.com/packages/basic-day",
          apikey: "10G4JxM5BnSEfHiqq"
        },
        {
          baseUrl: "https://my.meteoblue.com/packages/basic-day",
          apikey: "gymdWYMKhuNIS15pp"
        },
        {
          baseUrl: "https://my.meteoblue.com/packages/basic-day",
          apikey: "f5d3r69bmsNQLMt22"
        },
        {
          baseUrl: "https://my.meteoblue.com/packages/basic-day",
          apikey: "lJeCrtzOubQiTN711"
        }
      ];
      // ============================================================

      const locations = <?= json_encode($locations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
      const fallbackCenter = {
        lat: 10.242412,
        lng: 123.808390
      };
      const center = locations.length ? {
        lat: locations[0].lat,
        lng: locations[0].lng
      } : fallbackCenter;
      const modal = document.getElementById("locationModal");
      const modalTitle = document.getElementById("modalTitle");
      const modalSubtitle = document.getElementById("modalSubtitle");
      const modalBody = document.getElementById("modalBody");
      const closeModalButton = document.getElementById("closeModal");
      const infoCard = document.getElementById("infoCard");
      const panelToggle = document.getElementById("panelToggle");
      const weatherCache = new Map();
      let recommendationPanelOpen = false;

      if (panelToggle && infoCard) {
        panelToggle.addEventListener("click", () => {
          const collapsed = infoCard.classList.toggle("collapsed");
          panelToggle.textContent = collapsed ? "+" : "\u2212";
          panelToggle.setAttribute("aria-expanded", String(!collapsed));
        });
      }

      modalBody.addEventListener("click", (event) => {
        const toggle = event.target.closest(".accordion-toggle");
        if (!toggle) {
          return;
        }

        recommendationPanelOpen = !recommendationPanelOpen;
        const panel = toggle.parentElement.querySelector(".accordion-panel");
        if (panel) {
          panel.classList.toggle("open", recommendationPanelOpen);
        }
        toggle.setAttribute("aria-expanded", String(recommendationPanelOpen));
      });

      function escapeHtml(value) {
        return String(value)
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/\"/g, "&quot;")
          .replace(/'/g, "&#39;");
      }

      function formatValue(value) {
        if (value === null || value === undefined || value === "") {
          return "Not provided";
        }

        if (typeof value === "number") {
          return Number.isInteger(value) ? value.toString() : value.toFixed(2);
        }

        return String(value);
      }

      function parseRecommendationItems(value) {
        if (value === null || value === undefined) {
          return [];
        }

        const text = String(value).trim();
        if (text === "") {
          return [];
        }

        return text.split(/\n\s*\n/).map((item) => item.trim()).filter((item) => item !== "");
      }

      function formatRecommendationItem(item) {
        const separatorIndex = item.indexOf(":");

        if (separatorIndex > -1 && separatorIndex < 60) {
          const factor = item.slice(0, separatorIndex).trim();
          const detail = item.slice(separatorIndex + 1).trim();
          return `<li><strong>${escapeHtml(factor)}:</strong> ${escapeHtml(detail)}</li>`;
        }

        return `<li>${escapeHtml(item)}</li>`;
      }

      function buildRecommendationSection(location) {
        const items = parseRecommendationItems(location.recommendation);
        const isOpen = recommendationPanelOpen && items.length > 0;
        const panelHtml = items.length ?
          `<ul class="recommendation-list">${items.map(formatRecommendationItem).join("")}</ul>` :
          `<p class="recommendation-empty">Not provided</p>`;

        return `<section class="info-section">` +
          `<button type="button" class="accordion-toggle" aria-expanded="${isOpen}">` +
          `<span class="section-title"><span class="section-icon" aria-hidden="true">\uD83D\uDCA1</span> Recommendation${items.length ? ` (${items.length})` : ""}</span>` +
          `<span class="accordion-chevron" aria-hidden="true">\u25BE</span>` +
          `</button>` +
          `<div class="accordion-panel${isOpen ? " open" : ""}">${panelHtml}</div>` +
          `</section>`;
      }

      function formatGarbageInfo(location) {
        const manhole = location.garbage_manhole === null || location.garbage_manhole === undefined ?
          "" :
          String(location.garbage_manhole).trim();
        const flood = location.garbage_flood_susceptibility === null || location.garbage_flood_susceptibility === undefined ?
          "" :
          String(location.garbage_flood_susceptibility).trim();

        if (!manhole && !flood) {
          return "Not provided";
        }

        if (manhole && flood) {
          return `${manhole} (${flood})`;
        }

        return manhole || flood;
      }

      function getFloodRiskClass(location) {
        const flood = location.garbage_flood_susceptibility === null || location.garbage_flood_susceptibility === undefined ?
          "" :
          String(location.garbage_flood_susceptibility).trim().toLowerCase();

        if (flood === "low") {
          return "risk-low";
        }

        if (flood === "moderate") {
          return "risk-moderate";
        }

        if (flood === "high") {
          return "risk-high";
        }

        return "";
      }

      function getStatusClass(value) {
        if (value === null || value === undefined) {
          return "";
        }

        const normalized = String(value).trim().toLowerCase();

        if (normalized === "very low") {
          return "status-very-low";
        }

        if (normalized === "low") {
          return "status-low";
        }

        if (normalized === "moderate") {
          return "status-moderate";
        }

        if (normalized === "high") {
          return "status-high";
        }

        if (normalized === "very high") {
          return "status-very-high";
        }

        return "";
      }

      function formatInfiltrationValue(value) {
        if (value === null || value === undefined || value === "") {
          return "Not provided";
        }

        const text = String(value).trim();
        const parts = text.split("/").map((part) => part.trim()).filter((part) => part !== "");

        if (parts.length <= 1) {
          const className = getStatusClass(text);
          if (!className) {
            return escapeHtml(text);
          }
          return `<span class="badge ${className}">${escapeHtml(text)}</span>`;
        }

        const lastPart = parts[parts.length - 1];
        const formattedPrefix = parts.slice(0, -1).join(" / ");
        const className = getStatusClass(lastPart);

        if (!className) {
          return escapeHtml(text);
        }

        return `${escapeHtml(formattedPrefix)} / <span class="badge ${className}">${escapeHtml(lastPart)}</span>`;
      }

      function getRainIntensity(precipitationAmount) {
        if (precipitationAmount >= 50) {
          return "Extreme";
        }

        if (precipitationAmount >= 20) {
          return "Very Heavy";
        }

        if (precipitationAmount >= 10) {
          return "Heavy";
        }

        if (precipitationAmount >= 2.5) {
          return "Moderate";
        }

        if (precipitationAmount > 0) {
          return "Light";
        }

        return "None";
      }

      function getWeatherIntensityClass(intensityText) {
        const normalized = String(intensityText || "").trim().toLowerCase();

        if (normalized === "none") {
          return "weather-none";
        }

        if (normalized === "light") {
          return "weather-light";
        }

        if (normalized === "moderate") {
          return "weather-moderate";
        }

        if (normalized === "heavy") {
          return "weather-heavy";
        }

        if (normalized === "very heavy") {
          return "weather-very-heavy";
        }

        if (normalized === "extreme") {
          return "weather-extreme";
        }

        return "";
      }

      function getMetricFromCandidates(source, metricCandidates, index) {
        if (!source) {
          return null;
        }

        for (let i = 0; i < metricCandidates.length; i++) {
          const key = metricCandidates[i];
          const value = source[key];

          if (Array.isArray(value)) {
            const item = value[index];
            if (typeof item === "number" && !Number.isNaN(item)) {
              return item;
            }
          } else if (typeof value === "number" && !Number.isNaN(value)) {
            return value;
          }
        }

        return null;
      }

      function formatManualRainIntensity(location) {
        const rainIntensity = location.rain_intensity;
        const rainIntensityUnit = location.rain_intensity_unit === null || location.rain_intensity_unit === undefined ?
          "" :
          String(location.rain_intensity_unit).trim();

        if (rainIntensity === null || rainIntensity === undefined) {
          return "Not provided";
        }

        const intensityText = Number.isFinite(Number(rainIntensity)) ? Number(rainIntensity).toFixed(2) : String(rainIntensity);

        if (rainIntensityUnit !== "") {
          return intensityText + " " + rainIntensityUnit;
        }

        return intensityText;
      }

      function formatManualProbability(location) {
        if (location.precipitation_probability === null || location.precipitation_probability === undefined) {
          return "Not provided";
        }

        return Number(location.precipitation_probability).toFixed(2) + "%";
      }

      function buildWeatherRows(location, weather) {
        const weatherSource = String(location.weather_data_source || "realtime").trim().toLowerCase();

        if (weatherSource === "manual") {
          return [
            {
              label: "Weather Source",
              value: "Manual Input"
            },
            {
              label: "Weather Notes",
              value: location.weather_input
            },
            {
              label: "Rain Intensity",
              value: formatManualRainIntensity(location)
            },
            {
              label: "Precipitation Probability",
              value: formatManualProbability(location)
            }
          ];
        }

        if (!weather) {
          return [
            {
              label: "Weather Source",
              value: "Realtime / Meteoblue"
            },
            {
              label: "Meteoblue Weather",
              value: "Loading weather..."
            }
          ];
        }

        return [
          {
            label: "Weather Source",
            value: "Realtime / Meteoblue"
          },
          {
            label: "Forecast Date",
            value: weather.dateText
          },
          {
            label: "Rain Intensity",
            value: `${weather.intensityText} / ${Number(weather.precipitationAmount ?? 0).toFixed(2)}`,
            valueClass: getWeatherIntensityClass(weather.intensityText)
          },
          {
            label: "Rain Intensity Units",
            value: weather.precipitationText
          },
          {
            label: "Precipitation Probability",
            value: weather.probabilityText
          }
        ];
      }

      function buildInfoRows(rows) {
        return rows.map((row) => {
          let valueHtml;

          if (row.rawHtml) {
            valueHtml = row.value;
          } else {
            const text = escapeHtml(formatValue(row.value));
            valueHtml = row.valueClass ? `<span class="badge ${escapeHtml(row.valueClass)}">${text}</span>` : text;
          }

          return `<div class="info-row"><span class="info-label">${escapeHtml(row.label)}</span><span class="info-value">${valueHtml}</span></div>`;
        }).join("");
      }

      function renderModal(location, weather) {
        modalTitle.textContent = location.name || "Location Details";
        modalSubtitle.textContent = `Lat ${Number(location.lat).toFixed(5)}, Lng ${Number(location.lng).toFixed(5)}`;

        const locationRows = [{
            label: "Location Name",
            value: location.name
          },
          {
            label: "Latitude",
            value: location.lat
          },
          {
            label: "Longitude",
            value: location.lng
          },
          {
            label: "Elevation",
            value: location.elevation
          },
          {
            label: "Built Span",
            value: location.built_span
          },
          {
            label: "Building Density",
            value: location.building_density
          },
          {
            label: "Impervious",
            value: location.impervious
          },
          {
            label: "Infiltration Capacity",
            value: formatInfiltrationValue(location.infiltration_capacity),
            rawHtml: true
          },
          {
            label: "Slope",
            value: location.slope
          },
          {
            label: "Soil Type",
            value: location.soil_type
          },
          {
            label: "Dimensions",
            value: location.dimensions
          },
          {
            label: "Shape",
            value: location.shape
          },
          {
            label: "Material",
            value: location.material
          }
        ];

        const environmentalRows = [{
            label: "Garbage Accumulation",
            value: formatGarbageInfo(location),
            valueClass: getFloodRiskClass(location)
          },
          {
            label: "Drainage Obstruction",
            value: location.drainage_obstruction
          },
          {
            label: "Vegetation Cover",
            value: location.vegetation_cover
          },
          {
            label: "Structural Condition",
            value: location.structure_type
          },
          {
            label: "Hydraulic Capacity",
            value: location.manning_n
          },
          {
            label: "Drainage Conveyance",
            value: location.drainage_conveyance
          }
        ];

        const weatherRows = buildWeatherRows(location, weather);

        const weatherHTML = `<section class="info-section weather-section">` +
          `<h3 class="section-title"><span class="section-icon" aria-hidden="true">\uD83C\uDF26\uFE0F</span> Weather Forecast</h3>` +
          `<div class="info-grid">${buildInfoRows(weatherRows)}</div>` +
          `</section>`;

        const environmentalHTML = `<section class="info-section">` +
          `<h3 class="section-title"><span class="section-icon" aria-hidden="true">\uD83C\uDF31</span> Environmental Factors</h3>` +
          `<div class="info-grid">${buildInfoRows(environmentalRows)}</div>` +
          `</section>`;

        const locationHTML = `<section class="info-section">` +
          `<h3 class="section-title"><span class="section-icon" aria-hidden="true">\uD83D\uDCCA</span> Site Information</h3>` +
          `<div class="info-grid">${buildInfoRows(locationRows)}</div>` +
          `</section>`;

        const recommendationHTML = buildRecommendationSection(location);

        const reportButtonHTML = `<a class="report-link-btn" href="flood_susceptibility.php?id=${encodeURIComponent(location.id)}"><span aria-hidden="true">\uD83D\uDCCA</span> View Flood Susceptibility Report</a>`;

        modalBody.innerHTML = reportButtonHTML + weatherHTML + environmentalHTML + locationHTML + recommendationHTML;

        modal.classList.add("show");
      }

      function fetchMeteoblueWeather(location) {
        const cacheKey = `${location.lat},${location.lng},${location.elevation ?? ""}`;
        if (weatherCache.has(cacheKey)) {
          return Promise.resolve(weatherCache.get(cacheKey));
        }

        // Try each API endpoint in sequence
        const tryNextApi = (apiIndex) => {
          if (apiIndex >= weatherApiEndpoints.length) {
            return Promise.reject(new Error("All weather APIs failed"));
          }

          const api = weatherApiEndpoints[apiIndex];
          const endpoint = api.baseUrl +
            "?apikey=" + encodeURIComponent(api.apikey) +
            "&lon=" + encodeURIComponent(location.lng) +
            "&lat=" + encodeURIComponent(location.lat) +
            "&asl=" + encodeURIComponent(location.elevation ?? 16) +
            "&format=json";

          return fetch(endpoint)
            .then((response) => {
              if (!response.ok) {
                throw new Error("API " + (apiIndex + 1) + " failed with status " + response.status);
              }
              return response.json();
            })
            .then((data) => {
              const day = data && data.data_day ? data.data_day : null;
              if (!day) {
                throw new Error("Invalid API " + (apiIndex + 1) + " payload");
              }

              const index = 0;
              const forecastDate = Array.isArray(day.time) && day.time.length ? day.time[index] : "Not available";
              const precipitationAmount = getMetricFromCandidates(day, [
                "precipitation",
                "precipitation_total",
                "precipitation_amount",
                "rain",
                "rain_sum",
                "totalprecipitation"
              ], index);
              const precipitationProbability = getMetricFromCandidates(day, [
                "precipitation_probability",
                "precipitation_probability_mean",
                "precipitation_probability_max",
                "rain_probability",
                "probability_precipitation"
              ], index);

              const amountValue = precipitationAmount === null ? 0 : precipitationAmount;
              const probabilityValue = precipitationProbability === null ? 0 : precipitationProbability;
              const intensityText = getRainIntensity(amountValue);

              const weather = {
                dateText: forecastDate,
                intensityText,
                precipitationAmount: amountValue,
                precipitationText: amountValue.toFixed(2) + " mm",
                probabilityText: probabilityValue.toFixed(0) + "%"
              };

              weatherCache.set(cacheKey, weather);

              fetch("admin/controllers/resultController.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json"
                },
                body: JSON.stringify({
                  api_name: "meteoblue",
                  latitude: location.lat,
                  longitude: location.lng,
                  elevation: location.elevation ?? 16,
                  response_code: 200,
                  payload: weather
                })
              }).then((response) => response.json())
                .then((result) => {
                  if (!result.success) {
                    console.warn("Weather cache insert failed:", result.message || "Unknown error");
                  }
                })
                .catch((error) => {
                  console.warn("Weather cache insert error:", error);
                });

              return weather;
            })
            .catch((error) => {
              console.warn("API " + (apiIndex + 1) + " error:", error.message);
              // Try next API
              return tryNextApi(apiIndex + 1);
            });
        };

        return tryNextApi(0);
      }

      function showLocationModal(location) {
        recommendationPanelOpen = false;
        const weatherSource = String(location.weather_data_source || "realtime").trim().toLowerCase();

        if (weatherSource === "manual") {
          renderModal(location, null);
          return;
        }

        renderModal(location, null);

        fetchMeteoblueWeather(location)
          .then((weather) => {
            renderModal(location, weather);
          })
          .catch(() => {
            renderModal(location, {
              dateText: "Not available",
              intensityText: "Unavailable",
              precipitationText: "Unavailable",
              probabilityText: "Unavailable"
            });
          });
      }

      function hideLocationModal() {
        modal.classList.remove("show");
      }

      if (closeModalButton) {
        closeModalButton.addEventListener("click", hideLocationModal);
      }

      if (modal) {
        modal.addEventListener("click", (event) => {
          if (event.target === modal) {
            hideLocationModal();
          }
        });
      }

      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
          hideLocationModal();
        }
      });

      const map = L.map("map").setView([center.lat, center.lng], 17);

      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        maxZoom: 18,
        attribution: "&copy; OpenStreetMap contributors"
      }).addTo(map);

      const smallMarkerIcon = L.icon({
        iconUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png",
        iconRetinaUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png",
        shadowUrl: "https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png",
        iconSize: [18, 30],
        iconAnchor: [9, 30],
        popupAnchor: [1, -24],
        shadowSize: [30, 30]
      });

      if (!locations.length) {
        L.marker([center.lat, center.lng])
          .addTo(map)
          .bindPopup("No Phase 2 locations found")
          .openPopup();
        return;
      }

      const bounds = L.latLngBounds([]);
      // Pulse tuning: increase PULSE_MAX_RADIUS (and optionally PULSE_STEP) if you want a larger pulse later.
      const PULSE_STEP = 2;
      const PULSE_MAX_RADIUS = 4;

      locations.forEach((location) => {
        const position = [location.lat, location.lng];

        const marker = L.marker(position, {
          title: location.name,
          icon: smallMarkerIcon
        }).addTo(map);

        marker.on("click", () => {
          showLocationModal(location);
        });

        const pulseCircle = L.circle(position, {
          radius: 0,
          fillColor: "#ff0000",
          fillOpacity: 0.12,
          color: "#ff0000",
          weight: 1
        }).addTo(map);

        let pulseRadius = 0;
        setInterval(() => {
          pulseRadius += PULSE_STEP;
          if (pulseRadius > PULSE_MAX_RADIUS) pulseRadius = 0;
          pulseCircle.setRadius(pulseRadius);
        }, 700);

        bounds.extend(position);
      });

      map.fitBounds(bounds, {
        padding: [40, 40]
      });
    }
  </script>

  <script>
    initMap();
  </script>
</body>

</html>