<?php
require_once __DIR__ . '/admin/config/db.php';

$locationId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($locationId === null || $locationId === false) {
  header('Location: ta.php');
  exit;
}

$stmt = $conn->prepare("
  SELECT
    l.id,
    l.location_name,
    l.latitude,
    l.longitude,
    l.elevation,
    l.slope,
    l.building_density,
    l.drainage_obstruction,
    l.vegetation_cover,
    l.impervious,
    l.infiltration_capacity,
    l.soil_type,
    l.structure_type,
    l.dimensions,
    l.shape,
    l.manning_n,
    l.material,
    l.drainage_conveyance,
    l.recommendation,
    ga.manhole AS garbage_manhole,
    ga.flood_susceptibility AS garbage_flood_susceptibility
  FROM locations AS l
  LEFT JOIN garbage_accumulation AS ga
    ON ga.id = CAST(l.garbage_accommodation AS UNSIGNED)
  WHERE l.id = ?
  LIMIT 1
");
$stmt->bind_param('i', $locationId);
$stmt->execute();
$result = $stmt->get_result();
$location = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$location) {
  header('Location: ta.php');
  exit;
}

// Buckets values into the five-word susceptibility scale used across the report.
function classifyLevel($value, array $thresholds): ?string
{
  if ($value === null || $value === '') {
    return null;
  }

  $labels = ['Very Low', 'Low', 'Moderate', 'High', 'Very High'];
  $numeric = (float) $value;

  foreach ($thresholds as $index => $threshold) {
    if ($numeric <= $threshold) {
      return $labels[$index];
    }
  }

  return $labels[4];
}

// infiltration_capacity/impervious are stored as "descriptor / ... / <Category>" strings.
function extractTrailingCategory(?string $value): ?string
{
  if ($value === null || trim($value) === '') {
    return null;
  }

  $parts = array_values(array_filter(array_map('trim', explode('/', $value)), fn($part) => $part !== ''));

  if (empty($parts)) {
    return null;
  }

  $last = strtolower(end($parts));
  $known = ['very low', 'low', 'moderate', 'high', 'very high'];

  return in_array($last, $known, true) ? ucwords($last) : null;
}

// dimensions is a free-text "1.0m x 1.0m" style string; use the smallest number found (matches mitigation.php's "Small Drainage Dimensions" check).
function extractMinimumDimension(?string $value): ?float
{
  if ($value === null || trim($value) === '' || !preg_match_all('/-?\d+(?:\.\d+)?/', $value, $matches) || empty($matches[0])) {
    return null;
  }

  return min(array_map('floatval', $matches[0]));
}

function badgeClassForLevel(?string $level): string
{
  if ($level === null) {
    return '';
  }

  $map = [
    'very low' => 'status-very-low',
    'low' => 'status-low',
    'moderate' => 'status-moderate',
    'high' => 'status-high',
    'very high' => 'status-very-high',
  ];

  return $map[strtolower($level)] ?? '';
}

function renderLevelRow(string $label, ?string $level): string
{
  if ($level === null) {
    return '<div class="info-row"><span class="info-label">' . htmlspecialchars($label) . '</span><span class="info-value">Not provided</span></div>';
  }

  $badgeClass = badgeClassForLevel($level);
  $valueHtml = $badgeClass !== '' ? '<span class="badge ' . $badgeClass . '">' . htmlspecialchars($level) . '</span>' : htmlspecialchars($level);

  return '<div class="info-row"><span class="info-label">' . htmlspecialchars($label) . '</span><span class="info-value">' . $valueHtml . '</span></div>';
}

function renderTextRow(string $label, $value): string
{
  $text = ($value === null || trim((string) $value) === '') ? 'Not provided' : (string) $value;
  return '<div class="info-row"><span class="info-label">' . htmlspecialchars($label) . '</span><span class="info-value">' . htmlspecialchars($text) . '</span></div>';
}

// Maps a level word to a 1-5 weight so an overall average/major-reason list can be derived.
function levelScore(?string $level): ?int
{
  $scores = [
    'very low' => 1,
    'low' => 2,
    'moderate' => 3,
    'high' => 4,
    'very high' => 5,
  ];

  return $level === null ? null : ($scores[strtolower($level)] ?? null);
}

// recommendation is stored as multiple "Factor: mitigation text" blocks separated by blank lines.
function parseRecommendationItems(?string $value): array
{
  if ($value === null || trim($value) === '') {
    return [];
  }

  $items = preg_split('/\n\s*\n/', trim($value));
  return array_values(array_filter(array_map('trim', $items), fn($item) => $item !== ''));
}

function renderRecommendationItem(string $item): string
{
  $separatorIndex = strpos($item, ':');

  if ($separatorIndex !== false && $separatorIndex < 60) {
    $factor = trim(substr($item, 0, $separatorIndex));
    $detail = trim(substr($item, $separatorIndex + 1));
    return '<li><strong>' . htmlspecialchars($factor) . ':</strong> ' . htmlspecialchars($detail) . '</li>';
  }

  return '<li>' . htmlspecialchars($item) . '</li>';
}

$topographicFactors = [
  ['label' => 'Elevation', 'level' => classifyLevel($location['elevation'], [1.5, 2.5, 5, 10])],
  ['label' => 'Slope', 'level' => classifyLevel($location['slope'] !== null ? abs((float) $location['slope']) : null, [0.2, 1, 3, 6])],
];

$infrastructureFactors = [
  ['label' => 'Building Density', 'level' => classifyLevel($location['building_density'], [12.5, 25, 50, 75])],
];

$lithologicalFactors = [
  ['label' => 'Infiltration Capacity', 'level' => extractTrailingCategory($location['infiltration_capacity'])],
];

$environmentalFactors = [
  ['label' => 'Garbage Accumulation', 'level' => $location['garbage_flood_susceptibility']],
  ['label' => 'Drainage Obstruction', 'level' => classifyLevel($location['drainage_obstruction'], [1, 2, 3, 4])],
  ['label' => 'Vegetation Cover', 'level' => classifyLevel($location['vegetation_cover'], [20, 40, 60, 80])],
];

$drainageFactors = [
  ['label' => 'Hydraulic Capacity', 'level' => classifyLevel($location['manning_n'], [0.015, 0.025, 0.035, 0.05])],
  ['label' => 'Dimensions', 'level' => classifyLevel(extractMinimumDimension($location['dimensions']), [0.3, 0.6, 1.0, 1.5])],
];

$allFactors = array_merge($topographicFactors, $infrastructureFactors, $lithologicalFactors, $environmentalFactors, $drainageFactors);
$scoredFactors = [];

foreach ($allFactors as $factor) {
  $score = levelScore($factor['level']);

  if ($score !== null) {
    $scoredFactors[] = ['label' => $factor['label'], 'level' => $factor['level'], 'score' => $score];
  }
}

$overallLevel = null;
$majorReasons = [];

if (!empty($scoredFactors)) {
  $averageScore = array_sum(array_column($scoredFactors, 'score')) / count($scoredFactors);
  $overallLevel = classifyLevel($averageScore, [1.5, 2.5, 3.5, 4.5]);

  $maxScore = max(array_column($scoredFactors, 'score'));
  $majorReasons = array_values(array_filter($scoredFactors, fn($factor) => $factor['score'] >= 4));

  if (empty($majorReasons)) {
    $majorReasons = array_values(array_filter($scoredFactors, fn($factor) => $factor['score'] === $maxScore));
  }
}

// Presentation-only: turns the overall level into a 0-100 fill percentage for the gauge graphic below.
$levelOrder = ['Very Low', 'Low', 'Moderate', 'High', 'Very High'];
$gaugeIndex = $overallLevel !== null ? array_search($overallLevel, $levelOrder, true) : null;
$gaugePercent = $gaugeIndex !== false && $gaugeIndex !== null ? (($gaugeIndex + 1) / 5) * 100 : 0;

$recommendationItems = parseRecommendationItems($location['recommendation']);

$topographicRows =
  renderLevelRow('Elevation', $topographicFactors[0]['level']) .
  renderLevelRow('Slope', $topographicFactors[1]['level']);

$infrastructureRows =
  renderLevelRow('Building Density', $infrastructureFactors[0]['level']) .
  renderTextRow('Impervious', $location['impervious']);

$lithologicalRows =
  renderLevelRow('Infiltration Capacity', $lithologicalFactors[0]['level']) .
  renderTextRow('Soil Type', $location['soil_type']);

$environmentalRows =
  renderLevelRow('Garbage Accumulation', $environmentalFactors[0]['level']) .
  renderLevelRow('Drainage Obstruction', $environmentalFactors[1]['level']) .
  renderLevelRow('Vegetation Cover', $environmentalFactors[2]['level']);

$drainageRows =
  renderLevelRow('Hydraulic Capacity', $drainageFactors[0]['level']) .
  renderTextRow('Structural Condition', $location['structure_type']) .
  renderLevelRow('Dimensions', $drainageFactors[1]['level']) .
  renderTextRow('Shape', $location['shape']) .
  renderTextRow('Material', $location['material']) .
  renderTextRow('Drainage Conveyance', $location['drainage_conveyance']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Public+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
  <title>Flood Susceptibility Report — <?= htmlspecialchars($location['location_name'] ?? 'Location') ?></title>
  <style>
    :root {
      --ink: #10262A;
      --ink-soft: #4B6A6E;
      --ink-faint: #7E9698;
      --paper: #EEF2ED;
      --paper-raised: #FFFFFF;
      --line: rgba(16, 38, 42, 0.13);
      --water: #1D5C82;
      --water-deep: #0F3A50;
      --water-pale: #DCEAF0;
      --contour: #2F7A66;
      --flood: #C1571B;
      --flood-pale: #F6E2D3;
      --gauge-track: #D8E3DE;
      --radius: 16px;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      min-height: 100%;
      font-family: 'Public Sans', -apple-system, sans-serif;
      background: var(--paper);
      background-image:
        linear-gradient(var(--line) 1px, transparent 1px),
        linear-gradient(90deg, var(--line) 1px, transparent 1px);
      background-size: 42px 42px;
      background-attachment: fixed;
      color: var(--ink);
    }

    a { color: inherit; }

    .icon {
      width: 17px;
      height: 17px;
      flex-shrink: 0;
      stroke: currentColor;
      fill: none;
      stroke-width: 1.7;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* ---------- Header ---------- */

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 16px 28px;
      background: var(--water-deep);
      color: #fff;
      position: sticky;
      top: 0;
      z-index: 20;
      box-shadow: 0 4px 18px rgba(10, 30, 40, 0.18);
    }

    .topbar .eyebrow {
      display: block;
      font-family: 'JetBrains Mono', monospace;
      font-size: 10.5px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.55);
      margin-bottom: 2px;
    }

    .topbar h1 {
      margin: 0;
      font-family: 'Fraunces', serif;
      font-size: 19px;
      font-weight: 600;
    }

    .back-button {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 9px 16px;
      background: transparent;
      border: 1px solid rgba(255, 255, 255, 0.35);
      color: #fff;
      text-decoration: none;
      border-radius: 999px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      transition: background 0.2s ease, border-color 0.2s ease;
      white-space: nowrap;
    }

    .back-button:hover {
      background: rgba(255, 255, 255, 0.12);
      border-color: rgba(255, 255, 255, 0.6);
    }

    /* ---------- Title strip ---------- */

    .title-strip {
      padding: 30px 28px 8px;
      max-width: 1320px;
      margin: 0 auto;
    }

    .title-strip .location-name {
      margin: 0;
      font-family: 'Fraunces', serif;
      font-size: clamp(28px, 4vw, 40px);
      font-weight: 600;
      line-height: 1.05;
    }

    .title-strip .location-coords {
      margin: 8px 0 0;
      font-family: 'JetBrains Mono', monospace;
      font-size: 12.5px;
      color: var(--ink-soft);
      letter-spacing: 0.02em;
    }

    .title-strip .location-coords span {
      color: var(--contour);
      font-weight: 600;
    }

    /* ---------- Layout ---------- */

    .report-layout {
      display: grid;
      grid-template-columns: minmax(320px, 460px) 1fr;
      gap: 22px;
      padding: 20px 28px 44px;
      max-width: 1320px;
      margin: 0 auto;
      align-items: start;
    }

    /* ---------- Gauge signature panel ---------- */

    .gauge-panel {
      background: var(--water-deep);
      background-image: radial-gradient(circle at 85% -10%, rgba(255,255,255,0.10), transparent 55%);
      border-radius: var(--radius);
      color: #fff;
      padding: 22px 22px 20px;
      display: grid;
      grid-template-columns: 56px 1fr;
      gap: 20px;
      box-shadow: 0 16px 36px rgba(10, 30, 40, 0.22);
    }

    .gauge-stem {
      position: relative;
      width: 22px;
      justify-self: center;
      background: var(--gauge-track);
      background: rgba(255, 255, 255, 0.14);
      border-radius: 11px;
      overflow: hidden;
      border: 1px solid rgba(255, 255, 255, 0.28);
    }

    .gauge-fill {
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      height: var(--fill, 0%);
      background: linear-gradient(180deg, var(--flood), #E08A4C);
      transition: height 1s cubic-bezier(.4,0,.2,1);
    }

    .gauge-fill::before {
      content: "";
      position: absolute;
      top: -4px;
      left: 0;
      right: 0;
      height: 8px;
      background: radial-gradient(ellipse at 30% 50%, rgba(255,255,255,0.55), transparent 60%),
                  radial-gradient(ellipse at 70% 50%, rgba(255,255,255,0.4), transparent 60%);
      animation: ripple 2.6s ease-in-out infinite;
    }

    @keyframes ripple {
      0%, 100% { transform: translateY(0); opacity: 0.8; }
      50% { transform: translateY(-1.5px); opacity: 1; }
    }

    .gauge-ticks {
      position: absolute;
      inset: 0;
      display: flex;
      flex-direction: column-reverse;
      justify-content: space-between;
      padding: 3px 0;
    }

    .gauge-ticks span {
      display: block;
      height: 1px;
      background: rgba(255, 255, 255, 0.3);
    }

    .gauge-body .eyebrow {
      font-family: 'JetBrains Mono', monospace;
      font-size: 10.5px;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.6);
      margin: 0 0 8px;
    }

    .gauge-level {
      font-family: 'Fraunces', serif;
      font-size: 27px;
      font-weight: 600;
      margin: 0 0 12px;
      display: flex;
      align-items: baseline;
      gap: 8px;
    }

    .gauge-level .unit {
      font-family: 'JetBrains Mono', monospace;
      font-size: 12px;
      font-weight: 500;
      color: rgba(255, 255, 255, 0.55);
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }

    .reason-label {
      margin: 14px 0 6px;
      font-size: 12px;
      font-family: 'JetBrains Mono', monospace;
      letter-spacing: 0.04em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.55);
    }

    .reason-list {
      margin: 0;
      padding: 0;
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 5px;
      font-size: 13.5px;
      line-height: 1.5;
    }

    .reason-list li {
      display: flex;
      gap: 8px;
    }

    .reason-list li::before {
      content: "";
      width: 5px;
      height: 5px;
      border-radius: 50%;
      background: var(--flood);
      margin-top: 7px;
      flex-shrink: 0;
    }

    /* ---------- Field sheets ---------- */

    .field-sheet {
      background: var(--paper-raised);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      margin-top: 16px;
      overflow: hidden;
    }

    .field-sheet summary {
      list-style: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 15px 18px;
      font-family: 'JetBrains Mono', monospace;
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--water-deep);
    }

    .field-sheet summary::-webkit-details-marker { display: none; }

    .field-sheet summary .icon { color: var(--contour); }

    .field-sheet summary::after {
      content: "";
      width: 8px;
      height: 8px;
      margin-left: auto;
      border-right: 1.6px solid var(--ink-faint);
      border-bottom: 1.6px solid var(--ink-faint);
      transform: rotate(45deg);
      transition: transform 0.2s ease;
    }

    .field-sheet[open] summary::after {
      transform: rotate(-135deg);
    }

    .info-grid {
      border-top: 1px solid var(--line);
    }

    .info-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 12px;
      align-items: center;
      padding: 10px 18px;
      font-size: 13.5px;
    }

    .info-row:nth-child(even) {
      background: #F7FAF8;
    }

    .info-row + .info-row {
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

    .badge {
      display: inline-block;
      padding: 3px 11px;
      border-radius: 999px;
      font-weight: 700;
      font-size: 11.5px;
      letter-spacing: 0.01em;
    }

    .badge.status-low, .badge.status-very-low {
      background: #DCEFE6;
      color: #1F7A5C;
    }

    .badge.status-moderate {
      background: #FBEBD2;
      color: #A9711A;
    }

    .badge.status-high {
      background: #FBDFCB;
      color: var(--flood);
    }

    .badge.status-very-high {
      background: #F6D4CE;
      color: #9B2A1F;
    }

    /* ---------- Recommendation ---------- */

    .recommendation-list {
      margin: 0;
      padding: 4px 18px 16px;
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 10px;
      font-size: 13.5px;
      line-height: 1.55;
      color: var(--ink);
    }

    .recommendation-list li {
      position: relative;
      padding-left: 18px;
    }

    .recommendation-list li::before {
      content: "";
      position: absolute;
      left: 0;
      top: 7px;
      width: 8px;
      height: 8px;
      border: 1.7px solid var(--flood);
      border-radius: 2px;
      transform: rotate(45deg);
    }

    .recommendation-empty {
      margin: 0;
      padding: 4px 18px 16px;
      font-size: 13.5px;
      color: var(--ink-soft);
    }

    /* ---------- Map plate ---------- */

    .map-plate {
      position: sticky;
      top: 90px;
      background: var(--paper-raised);
      border: 1px solid var(--line);
      border-radius: var(--radius);
      padding: 14px;
      box-shadow: 0 12px 30px rgba(14, 36, 48, 0.10);
    }

    .map-plate-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 2px 4px 12px;
    }

    .map-plate-head .eyebrow {
      font-family: 'JetBrains Mono', monospace;
      font-size: 10.5px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      color: var(--ink-faint);
    }

    .map-frame {
      position: relative;
      border-radius: 10px;
      overflow: hidden;
      min-height: 480px;
    }

    .map-frame::before,
    .map-frame::after,
    .map-frame .corner-b,
    .map-frame .corner-c {
      content: "";
      position: absolute;
      width: 18px;
      height: 18px;
      border: 2px solid var(--flood);
      z-index: 10;
      pointer-events: none;
    }

    .map-frame::before { top: 8px; left: 8px; border-right: none; border-bottom: none; }
    .map-frame::after { top: 8px; right: 8px; border-left: none; border-bottom: none; }
    .map-frame .corner-b { bottom: 8px; left: 8px; border-right: none; border-top: none; }
    .map-frame .corner-c { bottom: 8px; right: 8px; border-left: none; border-top: none; }

    #map {
      width: 100%;
      height: 100%;
      min-height: 480px;
    }

    @media (max-width: 900px) {
      .report-layout {
        grid-template-columns: 1fr;
      }

      .map-plate {
        position: static;
      }

      .map-frame, #map {
        min-height: 340px;
      }

      .gauge-panel {
        grid-template-columns: 40px 1fr;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .gauge-fill, .gauge-fill::before {
        transition: none;
        animation: none;
      }
    }
  </style>
</head>

<body>
  <div class="topbar">
    <div>
      <span class="eyebrow">Field Assessment · #<?= htmlspecialchars((string) $location['id']) ?></span>
      <h1>Flood Susceptibility Report</h1>
    </div>
    <a href="ta.php" class="back-button">
      <svg class="icon" viewBox="0 0 24 24"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
      Back to map
    </a>
  </div>

  <div class="title-strip">
    <p class="location-name"><?= htmlspecialchars($location['location_name'] ?? 'Location Details') ?></p>
    <p class="location-coords">LAT <span><?= number_format((float) $location['latitude'], 5) ?></span> &nbsp;·&nbsp; LNG <span><?= number_format((float) $location['longitude'], 5) ?></span></p>
  </div>

  <div class="report-layout">
    <div>

      <!-- Signature element: flood-gauge style overall reading -->
      <section class="gauge-panel">
        <div class="gauge-stem">
          <div class="gauge-ticks"><span></span><span></span><span></span><span></span><span></span></div>
          <div class="gauge-fill" style="--fill: <?= (float) $gaugePercent ?>%;"></div>
        </div>
        <div class="gauge-body">
          <p class="eyebrow">Overall Flood Susceptibility</p>
          <p class="gauge-level">
            <?= $overallLevel !== null ? htmlspecialchars($overallLevel) : 'Not enough data' ?>
            <?php if ($overallLevel !== null): ?><span class="unit"><?= round($gaugePercent) ?>/100</span><?php endif; ?>
          </p>
          <?php if (!empty($majorReasons)): ?>
            <p class="reason-label">Primary drivers</p>
            <ul class="reason-list">
              <?php foreach ($majorReasons as $reason): ?>
                <li><strong><?= htmlspecialchars($reason['label']) ?>:</strong>&nbsp;<?= htmlspecialchars($reason['level']) ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>

      <details class="field-sheet" open>
        <summary>
          <svg class="icon" viewBox="0 0 24 24"><path d="M3 19l6-11 4 7 3-5 5 9H3z"/></svg>
          Topographic
        </summary>
        <div class="info-grid"><?= $topographicRows ?></div>
      </details>

      <details class="field-sheet" open>
        <summary>
          <svg class="icon" viewBox="0 0 24 24"><path d="M4 21V8l6-4v17M4 21h16M14 21V11l6-3v13M9 9h.01M9 13h.01M9 17h.01"/></svg>
          Infrastructure
        </summary>
        <div class="info-grid"><?= $infrastructureRows ?></div>
      </details>

      <details class="field-sheet" open>
        <summary>
          <svg class="icon" viewBox="0 0 24 24"><path d="M3 18h18M4 18v-3.5l4-2 3 1.8 4-2.6 6 2.3V18M4 11l3-2 4 1.7L15 8l5 2"/></svg>
          Lithological
        </summary>
        <div class="info-grid"><?= $lithologicalRows ?></div>
      </details>

      <details class="field-sheet" open>
        <summary>
          <svg class="icon" viewBox="0 0 24 24"><path d="M5 21c8 0 14-6 14-16-8 0-14 6-14 16z"/><path d="M5 21c0-4 2-8 6-10"/></svg>
          Environmental
        </summary>
        <div class="info-grid"><?= $environmentalRows ?></div>
      </details>

      <details class="field-sheet" open>
        <summary>
          <svg class="icon" viewBox="0 0 24 24"><path d="M4 8h9a4 4 0 010 8H9M4 8l3-3M4 8l3 3"/></svg>
          Drainage System
        </summary>
        <div class="info-grid"><?= $drainageRows ?></div>
      </details>

      <details class="field-sheet" open>
        <summary>
          <svg class="icon" viewBox="0 0 24 24"><path d="M9 18h6M10 21h4M12 3a6 6 0 00-3.4 10.9c.6.4.9 1 .9 1.7V16h5v-.4c0-.7.3-1.3.9-1.7A6 6 0 0012 3z"/></svg>
          Recommendation
        </summary>
        <?php if (!empty($recommendationItems)): ?>
          <ul class="recommendation-list"><?php foreach ($recommendationItems as $item) {
            echo renderRecommendationItem($item);
          } ?></ul>
        <?php else: ?>
          <p class="recommendation-empty">Not provided</p>
        <?php endif; ?>
      </details>
    </div>

    <div class="map-plate">
      <div class="map-plate-head">
        <span class="eyebrow">Site Plate</span>
        <span class="eyebrow">Zoom 18</span>
      </div>
      <div class="map-frame">
        <span class="corner-b"></span><span class="corner-c"></span>
        <div id="map"></div>
      </div>
    </div>
  </div>

  <script>
    const lat = <?= json_encode((float) $location['latitude']) ?>;
    const lng = <?= json_encode((float) $location['longitude']) ?>;
    const locationName = <?= json_encode($location['location_name'] ?? 'Location') ?>;

    const map = L.map("map").setView([lat, lng], 18);

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 20,
      attribution: "&copy; OpenStreetMap contributors"
    }).addTo(map);

    L.marker([lat, lng]).addTo(map).bindPopup(locationName).openPopup();

    const pulseCircle = L.circle([lat, lng], {
      radius: 0,
      fillColor: "#C1571B",
      fillOpacity: 0.14,
      color: "#C1571B",
      weight: 1
    }).addTo(map);

    let pulseRadius = 0;
    setInterval(() => {
      pulseRadius += 2;
      if (pulseRadius > 8) pulseRadius = 0;
      pulseCircle.setRadius(pulseRadius);
    }, 700);
  </script>
</body>

</html>