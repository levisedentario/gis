<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mapping Gallery — Flood Susceptibility Framework</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600;9..144,700&family=Source+Serif+4:ital,wght@0,400;0,500;0,600;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root{
      --ink:#0E2430;
      --ink-soft:#274A55;
      --paper:#EAF0ED;
      --paper-2:#DDE7E2;
      --water:#2E6F9E;
      --water-deep:#153A52;
      --flood:#D9622B;
      --line:rgba(14,36,48,0.14);
      --max:1120px;
    }
    *{box-sizing:border-box;}
    body{
      margin:0;
      background:var(--paper);
      color:var(--ink);
      font-family:'Source Serif 4', serif;
      font-size:17px;
      line-height:1.65;
    }
    h1,h2,h3{
      font-family:'Fraunces', serif;
      font-weight:600;
      letter-spacing:-0.01em;
      margin:0;
    }
    a{color:inherit; text-decoration:none;}
    .wrap{max-width:var(--max); margin:0 auto; padding:0 32px;}
    nav{
      position:sticky; top:0; z-index:20;
      display:flex; align-items:center; justify-content:space-between;
      padding:18px 32px;
      background:rgba(234,240,237,0.9);
      backdrop-filter:blur(10px);
      border-bottom:1px solid var(--line);
    }
    .brand{display:flex; align-items:center; gap:10px; font-family:'JetBrains Mono', monospace; font-size:13px; text-transform:uppercase; letter-spacing:0.09em; font-weight:600;}
    .brand svg{width:22px; height:22px;}
    .nav-links{display:flex; gap:22px; font-family:'JetBrains Mono', monospace; font-size:12.5px; text-transform:uppercase; letter-spacing:0.07em;}
    .nav-links a{color:var(--ink-soft);} 
    .nav-links a:hover{color:var(--flood);} 
    .hero{padding:80px 0 40px;}
    .hero h1{font-size:clamp(2.1rem, 4vw, 3rem); max-width:12ch;}
    .hero p{max-width:700px; margin-top:18px; color:var(--ink-soft); font-size:1.05rem;}
    .grid{display:grid; grid-template-columns:repeat(3, 1fr); gap:24px; margin-top:38px;}
    @media (max-width:900px){.grid{grid-template-columns:1fr 1fr;}}
    @media (max-width:620px){.grid{grid-template-columns:1fr;}}
    .card{
      background:var(--paper-2);
      border:1px solid var(--line);
      border-radius:8px;
      padding:24px;
      display:flex; flex-direction:column; gap:12px;
    }
    .card .tag{font-family:'JetBrains Mono', monospace; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--flood);} 
    .card h3{font-size:1.24rem;}
    .card p{margin:0; color:var(--ink-soft); font-size:0.98rem;}
    .card a{margin-top:auto; display:inline-block; padding:10px 14px; background:var(--water-deep); color:white; border-radius:999px; width:fit-content; font-family:'JetBrains Mono', monospace; font-size:11.5px; text-transform:uppercase; letter-spacing:0.06em;}
    .card a:hover{background:var(--flood);} 
    footer{padding:50px 0 70px; color:var(--ink-soft); font-family:'JetBrains Mono', monospace; font-size:11.5px; text-transform:uppercase; letter-spacing:0.05em;}
  </style>
</head>
<body>
  <nav>
    <a href="index.php" class="brand">
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 2C12 2 5 10.5 5 15a7 7 0 0014 0c0-4.5-7-13-7-13z" stroke="#0E2430" stroke-width="1.4"/>
        <path d="M4 15h16" stroke="#D9622B" stroke-width="1.4"/>
      </svg>
      Tungkil GIS
    </a>
    <div class="nav-links">
      <a href="index.php#rationale">Rationale</a>
      <a href="index.php#factors">Factors</a>
      <a href="index.php#site">Site</a>
      <a href="index.php#impact">Impact</a>
      <a href="mapping.php">Mapping</a>
    </div>
  </nav>

  <main class="wrap">
    <section class="hero">
      <span class="tag" style="font-family:'JetBrains Mono', monospace; font-size:12px; text-transform:uppercase; letter-spacing:0.1em; color:var(--flood);">03 — Mapping Gallery</span>
      <h1>Mapping Gallery</h1>
      <p>Choose a map style to explore the study area, compare basemap layouts, and inspect the GIS outputs for the flood susceptibility framework.</p>

      <?php
      $maps = [
        [
          'title' => 'Border Map',
          'description' => 'Simple reference view with a clean border layout and the study area context.',
          'link' => 'qgis/border/index.html',
          'button' => 'Open Border Map'
        ],
        [
          'title' => 'Esri White',
          'description' => 'Light basemap styling for clearer overlay comparison and presentation.',
          'link' => 'qgis/esri_white/index.html',
          'button' => 'Open Esri White'
        ],
        [
          'title' => 'Hybrid',
          'description' => 'Mixed visual style that combines road, terrain, and satellite context.',
          'link' => 'qgis/hybrid/index.html',
          'button' => 'Open Hybrid Map'
        ],
        [
          'title' => 'Map Option 04',
          'description' => 'Replace this title, description, and link when you add a new mapping view.',
          'link' => '#',
          'button' => 'Open Map Option 04'
        ],
        [
          'title' => 'Map Option 05',
          'description' => 'Replace this title, description, and link when you add a new mapping view.',
          'link' => '#',
          'button' => 'Open Map Option 05'
        ],
        [
          'title' => 'Map Option 06',
          'description' => 'Replace this title, description, and link when you add a new mapping view.',
          'link' => '#',
          'button' => 'Open Map Option 06'
        ]
      ];
      ?>

      <div class="grid">
        <?php foreach ($maps as $index => $map): ?>
          <div class="card">
            <div class="tag">View <?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></div>
            <h3><?= htmlspecialchars($map['title'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars($map['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <a href="<?= htmlspecialchars($map['link'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($map['button'], ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

  <footer class="wrap">
    <p>Back to the <a href="index.php" style="color:var(--water-deep);">main research page</a>.</p>
  </footer>
</body>
</html>
