<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Flood Susceptibility Framework — Minglanilla, Cebu</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,300;9..144,500;9..144,600;9..144,700&family=Source+Serif+4:ital,wght@0,400;0,500;0,600;1,400&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>

  :root{
    --ink:#0E2430;
    --ink-soft:#274A55;
    --paper:#EAF0ED;
    --paper-2:#DDE7E2;
    --contour:#2F6B5E;
    --water:#2E6F9E;
    --water-deep:#153A52;
    --flood:#D9622B;
    --gold:#B8912A;
    --line:rgba(14,36,48,0.14);
    --max:1180px;
  }

  *{box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{
    margin:0;
    background:var(--paper);
    color:var(--ink);
    font-family:'Source Serif 4', serif;
    font-size:17px;
    line-height:1.65;
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,.display{
    font-family:'Fraunces', serif;
    font-weight:600;
    letter-spacing:-0.01em;
    color:var(--ink);
    margin:0;
  }
  .mono{
    font-family:'JetBrains Mono', monospace;
    letter-spacing:0.02em;
  }
  a{color:inherit;}
  img{max-width:100%;display:block;}
  .wrap{max-width:var(--max); margin:0 auto; padding:0 32px;}

  @media (prefers-reduced-motion: reduce){
    *{animation-duration:0.001ms !important; animation-iteration-count:1 !important; transition-duration:0.001ms !important; scroll-behavior:auto !important;}
  }

  /* ---------- NAV ---------- */
  nav{
    position:fixed; top:0; left:0; right:0; z-index:50;
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 32px;
    background:rgba(234,240,237,0.86);
    backdrop-filter:blur(10px);
    border-bottom:1px solid var(--line);
  }
  .brand{
    display:flex; align-items:center; gap:10px;
    font-family:'JetBrains Mono', monospace;
    font-size:13px; font-weight:600;
    text-transform:uppercase; letter-spacing:0.09em;
    color:var(--ink);
    text-decoration:none;
  }
  .brand svg{width:22px; height:22px;}
  .navlinks{display:flex; gap:28px; font-family:'JetBrains Mono', monospace; font-size:12.5px; text-transform:uppercase; letter-spacing:0.07em;}
  .navlinks a{text-decoration:none; color:var(--ink-soft); position:relative; padding-bottom:3px;}
  .navlinks a:hover{color:var(--flood);}
  .navlinks a:focus-visible, a:focus-visible, button:focus-visible{outline:2px solid var(--flood); outline-offset:3px;}
  .navlinks .has-dropdown{position:relative;}
  .navlinks .dropdown{
    position:absolute; top:calc(100% + 10px); left:50%; transform:translateX(-50%) translateY(6px);
    min-width:180px; padding:10px 0; border:1px solid var(--line);
    background:rgba(234,240,237,0.98); box-shadow:0 14px 30px rgba(14,36,48,0.12);
    opacity:0; visibility:hidden; pointer-events:none; transition:all 0.2s ease;
  }
  .navlinks .has-dropdown:hover .dropdown, .navlinks .has-dropdown:focus-within .dropdown{
    opacity:1; visibility:visible; pointer-events:auto; transform:translateX(-50%) translateY(0);
  }
  .navlinks .dropdown a{display:block; padding:10px 16px; white-space:nowrap;}
  .navlinks .dropdown a:hover{background:rgba(217,98,43,0.08); color:var(--flood);}
  @media (max-width:820px){ .navlinks{display:none;} }

  /* ---------- HERO ---------- */
  .hero{
    position:relative;
    min-height:100vh;
    display:flex; flex-direction:column; justify-content:center;
    overflow:hidden;
    background:
      linear-gradient(120deg, rgba(255,255,255,0.82) 0%, rgba(255,255,255,0.55) 45%, rgba(255,255,255,0.72) 100%),
      url('admin/assets/tungkil_2.png') center/cover no-repeat;
    padding-top:90px;
  }
  .contour-field{
    position:absolute; inset:0; z-index:0;
    opacity:0.9;
  }
  .contour-field svg{width:100%; height:100%;}
  .basin{
    fill:var(--water);
    opacity:0.55;
    animation:rise 7s ease-in-out infinite;
  }
  @keyframes rise{
    0%,100%{ transform:translateY(0px); opacity:0.5;}
    50%{ transform:translateY(-3px); opacity:0.68;}
  }

  .hero-inner{
    position:relative; z-index:2;
    max-width:var(--max); margin:0 auto; width:100%;
    padding:40px 32px 80px;
  }
  .eyebrow{
    font-family:'JetBrains Mono', monospace;
    font-size:12.5px; letter-spacing:0.12em; text-transform:uppercase;
    color:var(--water-deep);
    display:flex; align-items:center; gap:10px;
    margin-bottom:26px;
  }
  .eyebrow .dot{width:7px; height:7px; border-radius:50%; background:var(--flood); display:inline-block; animation:pulse 2.4s ease-in-out infinite;}
  @keyframes pulse{0%,100%{opacity:1;} 50%{opacity:0.25;}}

  .hero h1{
    font-size:clamp(2.4rem, 5.4vw, 4.55rem);
    line-height:1.02;
    max-width:15ch;
  }
  .hero h1 em{
    font-style:italic; color:var(--water-deep);
  }
  .hero-sub{
    max-width:640px;
    margin-top:26px;
    font-size:1.15rem;
    color:var(--ink-soft);
  }
  .hero-meta{
    display:flex; flex-wrap:wrap; gap:36px;
    margin-top:52px;
    padding-top:28px;
    border-top:1px solid var(--line);
  }
  .meta-item .k{
    font-family:'JetBrains Mono', monospace; font-size:11.5px; text-transform:uppercase; letter-spacing:0.08em; color:var(--water-deep); opacity:0.75;
  }
  .meta-item .v{
    font-family:'Fraunces', serif; font-weight:600; font-size:1.3rem; margin-top:4px;
  }
  .scroll-cue{
    position:absolute; bottom:28px; left:50%; transform:translateX(-50%);
    z-index:2;
    font-family:'JetBrains Mono', monospace; font-size:11px; letter-spacing:0.1em; text-transform:uppercase;
    color:var(--water-deep); opacity:0.6;
    display:flex; flex-direction:column; align-items:center; gap:6px;
  }
  .scroll-cue .line{width:1px; height:26px; background:var(--water-deep); animation:drip 1.8s ease-in-out infinite;}
  @keyframes drip{0%{transform:scaleY(0); transform-origin:top;} 50%{transform:scaleY(1); transform-origin:top;} 51%{transform-origin:bottom;} 100%{transform:scaleY(0); transform-origin:bottom;}}

  /* ---------- SECTION GENERIC ---------- */
  section{padding:120px 0; position:relative;}
  .section-head{max-width:680px; margin-bottom:64px;}
  .tag{
    font-family:'JetBrains Mono', monospace; font-size:12px; text-transform:uppercase; letter-spacing:0.1em;
    color:var(--flood); margin-bottom:16px; display:block;
  }
  .section-head h2{font-size:clamp(1.9rem, 3.4vw, 2.6rem); line-height:1.12;}
  .section-head p{margin-top:18px; color:var(--ink-soft); font-size:1.08rem; max-width:60ch;}

  .reveal{opacity:0; transform:translateY(22px); transition:opacity 0.7s ease, transform 0.7s ease;}
  .reveal.in{opacity:1; transform:translateY(0);}

  .weather-shell{display:flex; justify-content:center;}
  .weather-card{width:100%; background:linear-gradient(135deg, rgba(255,255,255,0.94), rgba(234,240,237,0.96)); border:1px solid var(--line); border-radius:24px; padding:28px; box-shadow:0 24px 60px rgba(14,36,48,0.08);}
  .weather-header{display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap; margin-bottom:18px;}
  .weather-badge{display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:999px; background:rgba(46,111,158,0.12); color:var(--water-deep); font-family:'JetBrains Mono', monospace; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:10px;}
  .weather-header h3{font-size:1.45rem; margin-bottom:6px;}
  .weather-header p{margin:0; color:var(--ink-soft);}
  .weather-controls{display:flex; flex-wrap:wrap; gap:10px;}
  .weather-input{border:1px solid var(--line); border-radius:12px; padding:10px 12px; min-width:130px; font-family:'Source Serif 4', serif; color:var(--ink); background:rgba(255,255,255,0.8);}
  .weather-button{padding:10px 16px; border:none; border-radius:12px; background:linear-gradient(135deg, var(--water), var(--flood)); color:white; font-family:'JetBrains Mono', monospace; font-size:12px; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer;}
  .weather-button:hover{filter:brightness(1.04);}
  .weather-status{padding:12px 14px; border-radius:12px; background:rgba(14,36,48,0.05); color:var(--ink-soft); margin-bottom:16px; font-size:0.96rem;}
  .weather-current{display:grid; grid-template-columns:1.1fr 0.9fr; gap:16px; margin-bottom:16px;}
  .weather-panel{background:rgba(255,255,255,0.76); border:1px solid var(--line); border-radius:18px; padding:16px;}
  .weather-label{font-family:'JetBrains Mono', monospace; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--water-deep); margin-bottom:8px;}
  .weather-temp{font-family:'Fraunces', serif; font-size:2.2rem; font-weight:700; color:var(--ink); margin:10px 0 8px;}
  .weather-meta{display:flex; flex-wrap:wrap; gap:8px; margin-top:10px;}
  .weather-pill{font-size:0.86rem; color:var(--ink-soft); background:rgba(14,36,48,0.05); padding:7px 10px; border-radius:999px;}
  .weather-grid{display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px;}
  .weather-item{background:rgba(14,36,48,0.04); border:1px solid var(--line); border-radius:12px; padding:10px; min-height:130px;}
  .weather-item strong{display:block; margin-bottom:6px; color:var(--ink); font-family:'JetBrains Mono', monospace; font-size:12px;}
  .weather-item .muted{color:var(--ink-soft); font-size:0.9rem; line-height:1.5;}
  .weather-analysis{display:grid; gap:10px;}
  .weather-summary{color:var(--ink-soft); font-size:0.95rem;}
  .weather-bars{display:grid; grid-template-columns:repeat(6,1fr); gap:8px; align-items:end; min-height:110px; margin-top:8px;}
  .weather-bar-wrap{display:flex; flex-direction:column; align-items:center; gap:6px; font-size:0.78rem; color:var(--ink-soft);}
  .weather-bar{width:100%; min-height:6px; border-radius:8px 8px 4px 4px; background:linear-gradient(180deg, var(--water), var(--flood));}
  @media (max-width:900px){.weather-current{grid-template-columns:1fr;}}
  @media (max-width:680px){.weather-card{padding:20px;} .weather-controls{width:100%;} .weather-input{flex:1 1 140px;} .weather-grid{grid-template-columns:1fr;}}

  /* ---------- FACTORS ---------- */
  .factors-band{background:var(--ink); color:var(--paper);}
  .factors-band .section-head h2{color:var(--paper);}
  .factors-band .section-head p{color:#B9CBCB;}
  .factors-band .tag{color:#7FB8A6;}
  .factor-grid{
    display:grid; grid-template-columns:repeat(3, 1fr); gap:1px;
    background:rgba(255,255,255,0.12);
    border:1px solid rgba(255,255,255,0.12);
  }
  @media (max-width:900px){.factor-grid{grid-template-columns:repeat(2,1fr);}}
  @media (max-width:600px){.factor-grid{grid-template-columns:1fr;}}
  .factor{
    background:var(--ink);
    padding:34px 28px;
    transition:background 0.35s ease;
  }
  .factor:hover{background:#122E39;}
  .factor .fno{font-family:'JetBrains Mono', monospace; font-size:12px; color:var(--flood); letter-spacing:0.06em;}
  .factor h3{font-family:'Fraunces', serif; font-size:1.28rem; font-weight:600; color:var(--paper); margin:14px 0 12px;}
  .factor ul{margin:0; padding:0; list-style:none; font-family:'JetBrains Mono', monospace; font-size:12.5px; color:#9FC2C2; line-height:2;}
  .factor ul li::before{content:"– "; color:var(--flood);}

  /* ---------- FRAMEWORK / EQUATIONS ---------- */
  .framework-grid{display:grid; grid-template-columns:1fr 1fr; gap:28px;}
  @media (max-width:900px){.framework-grid{grid-template-columns:1fr;}}
  .eq-card{
    background:var(--paper-2);
    border:1px solid var(--line);
    border-radius:2px;
    padding:36px;
  }
  .eq-card .eq-label{font-family:'JetBrains Mono', monospace; font-size:11.5px; text-transform:uppercase; letter-spacing:0.08em; color:var(--water-deep);}
  .eq-card .eq-name{font-family:'Fraunces', serif; font-size:1.4rem; font-weight:600; margin:10px 0 20px;}
  .eq-display{
    font-family:'JetBrains Mono', monospace;
    font-size:1.5rem;
    color:var(--water-deep);
    background:var(--paper);
    border:1px solid var(--line);
    padding:20px 24px;
    text-align:center;
    margin-bottom:20px;
  }
  .eq-card p{color:var(--ink-soft); font-size:0.98rem; margin:0;}
  .eq-vars{margin-top:16px; display:flex; flex-wrap:wrap; gap:10px 22px; font-family:'JetBrains Mono', monospace; font-size:12px; color:var(--ink-soft);}
  .eq-vars b{color:var(--ink); font-style:normal;}

  .flow{
    margin-top:56px;
    display:flex; align-items:stretch; gap:0; flex-wrap:wrap;
    border-top:1px solid var(--line); border-bottom:1px solid var(--line);
  }
  .flow-step{
    flex:1; min-width:200px;
    padding:26px 24px;
    border-right:1px solid var(--line);
  }
  .flow-step:last-child{border-right:none;}
  .flow-step .fs-k{font-family:'JetBrains Mono', monospace; font-size:11px; text-transform:uppercase; color:var(--flood); letter-spacing:0.07em;}
  .flow-step h4{font-family:'Fraunces',serif; font-size:1.08rem; font-weight:600; margin:8px 0 8px;}
  .flow-step p{font-size:0.92rem; color:var(--ink-soft); margin:0;}

  /* ---------- SITE ---------- */
  .site-band{background:var(--water-deep); color:var(--paper);}
  .site-band .tag{color:#8FCBEA;}
  .site-band .section-head h2{color:var(--paper);}
  .site-band .section-head p{color:#BFD9E6;}
  .site-grid{display:grid; grid-template-columns:1fr 1fr; gap:56px; align-items:center;}
  @media (max-width:900px){.site-grid{grid-template-columns:1fr;}}
  .map-card{
    border:1px solid rgba(255,255,255,0.18);
    padding:24px;
    background:rgba(255,255,255,0.04);
  }
  .map-card svg{width:100%; height:auto;}
  .site-list{list-style:none; margin:0; padding:0;}
  .site-list li{
    display:flex; gap:18px; padding:18px 0; border-top:1px solid rgba(255,255,255,0.14);
  }
  .site-list li:last-child{border-bottom:1px solid rgba(255,255,255,0.14);}
  .site-list .sk{font-family:'JetBrains Mono', monospace; color:#8FCBEA; font-size:12px; min-width:90px; padding-top:2px;}
  .site-list .sv{color:#DCEAF1; font-size:0.98rem;}

  /* ---------- QUESTIONS ---------- */
  .q-list{counter-reset:q;}
  .q-item{
    display:grid; grid-template-columns:64px 1fr; gap:22px;
    padding:28px 0; border-bottom:1px solid var(--line);
  }
  .q-item:first-child{border-top:1px solid var(--line);}
  .q-num{font-family:'Fraunces', serif; font-weight:700; font-size:1.8rem; color:var(--water); opacity:0.5;}
  .q-item p{margin:0; color:var(--ink-soft);}
  .q-item strong{color:var(--ink); font-weight:600;}

  /* ---------- IMPACT ---------- */
  .impact-grid{display:grid; grid-template-columns:repeat(4,1fr); gap:24px;}
  @media (max-width:900px){.impact-grid{grid-template-columns:repeat(2,1fr);}}
  @media (max-width:560px){.impact-grid{grid-template-columns:1fr;}}
  .impact-card{
    border:1px solid var(--line);
    padding:26px 22px;
    background:var(--paper-2);
  }
  .impact-card .ic-mono{font-family:'JetBrains Mono', monospace; font-size:11px; text-transform:uppercase; letter-spacing:0.07em; color:var(--flood);}
  .impact-card h4{font-family:'Fraunces', serif; font-weight:600; font-size:1.12rem; margin:12px 0 10px;}
  .impact-card p{margin:0; font-size:0.92rem; color:var(--ink-soft);}

  /* ---------- TEAM / FOOTER ---------- */
  footer{background:var(--ink); color:var(--paper); padding:90px 0 40px;}
  .foot-grid{display:grid; grid-template-columns:1.2fr 1fr; gap:60px;}
  @media (max-width:820px){.foot-grid{grid-template-columns:1fr; gap:40px;}}
  footer h2{color:var(--paper); font-size:1.9rem; max-width:16ch;}
  footer .tag{color:#7FB8A6;}
  .team-list{list-style:none; margin:24px 0 0; padding:0; columns:2; gap:20px;}
  @media (max-width:500px){.team-list{columns:1;}}
  .team-list li{font-size:0.95rem; padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.1); break-inside:avoid;}
  .adviser{margin-top:26px; font-family:'JetBrains Mono', monospace; font-size:12px; color:#9FC2C2; letter-spacing:0.04em;}
  .foot-bottom{
    margin-top:70px; padding-top:26px; border-top:1px solid rgba(255,255,255,0.14);
    display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px;
    font-family:'JetBrains Mono', monospace; font-size:11.5px; color:#7C9694; letter-spacing:0.04em;
  }
</style>
</head>
<body>

<nav>
  <a href="#top" class="brand">
    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 2C12 2 5 10.5 5 15a7 7 0 0014 0c0-4.5-7-13-7-13z" stroke="#0E2430" stroke-width="1.4"/>
      <path d="M4 15h16" stroke="#D9622B" stroke-width="1.4"/>
    </svg>
    Tungkil GIS
  </a>
  <div class="navlinks">
    
    <a href="#factors">Factors</a>
    <a href="#site">Site</a>
    <a href="mapping.php">Mapping</a>
    
  </div>
</nav>

<header class="hero" id="top">
  <div class="contour-field" aria-hidden="true">
    <svg viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice">
      <path d="M-50,650 C150,600 300,680 500,640 C700,600 850,660 1250,600 L1250,850 L-50,850 Z" fill="none" stroke="var(--contour)" stroke-width="1" opacity="0.35"/>
      <path d="M-50,600 C180,560 320,630 520,590 C720,555 860,610 1250,555 L1250,850 L-50,850 Z" fill="none" stroke="var(--contour)" stroke-width="1" opacity="0.3"/>
      <path d="M-50,545 C200,510 340,570 540,535 C740,500 870,555 1250,505 L1250,850 L-50,850 Z" fill="none" stroke="var(--contour)" stroke-width="1" opacity="0.28"/>
      <path d="M-50,490 C220,460 360,510 560,480 C760,450 880,500 1250,455 L1250,850 L-50,850 Z" fill="none" stroke="var(--gold)" stroke-width="1" opacity="0.22"/>
      <path class="basin" d="M-50,700 C160,660 310,720 500,690 C690,660 840,710 1250,660 L1250,850 L-50,850 Z"/>
    </svg>
  </div>

  <div class="hero-inner">
    <div class="eyebrow"><span class="dot"></span> Mathematics &amp; Computational Science · Research Study</div>
    <h1>Predicting where <em>Minglanilla's streets flood</em> before the rain does.</h1>
    <p class="hero-sub">A GIS-based computational framework that scores every road segment in Subdivision Phase 3, Barangay Tungkil, against six causative factor groups — turning drainage inspections into a repeatable, mappable index.</p>

    <div class="hero-meta">
      <div class="meta-item"><div class="k">Study Site</div><div class="v">Brgy. Tungkil, Minglanilla</div></div>
      <div class="meta-item"><div class="k">Causative Factors</div><div class="v">6 groups · 15 variables</div></div>
      <div class="meta-item"><div class="k">Core Methods</div><div class="v">Rational Method + Manning's Eq.</div></div>
      <div class="meta-item"><div class="k">Output</div><div class="v">GIS Susceptibility Maps</div></div>
    </div>
  </div>

  <div class="scroll-cue"><span>scroll</span><span class="line"></span></div>
</header>

<section class="factors-band" id="factors">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">02 — Multi-Factor Model</span>
      <h2>Six factor groups feed one susceptibility index.</h2>
      <p>Each road segment is scored on physical, hydrological, and human-driven conditions, then combined into a single classification: low, moderate, or high susceptibility.</p>
    </div>
    <div class="factor-grid reveal">
      <div class="factor">
        <div class="fno">01</div>
        <h3>Drainage System</h3>
        <ul><li>Structure type</li><li>Dimensions</li><li>Shape</li><li>Built span (age)</li><li>Structural condition</li><li>Hydraulic capacity</li></ul>
      </div>
      <div class="factor">
        <div class="fno">02</div>
        <h3>Topographic</h3>
        <ul><li>Elevation</li><li>Slope</li></ul>
      </div>
      <div class="factor">
        <div class="fno">03</div>
        <h3>Rainfall</h3>
        <ul><li>Rainfall amount</li><li>Rainfall intensity</li></ul>
      </div>
      <div class="factor">
        <div class="fno">04</div>
        <h3>Lithological</h3>
        <ul><li>Soil type</li><li>Infiltration capacity</li></ul>
      </div>
      <div class="factor">
        <div class="fno">05</div>
        <h3>Infrastructure Development</h3>
        <ul><li>Building density</li><li>Impervious surface %</li></ul>
      </div>
      <div class="factor">
        <div class="fno">06</div>
        <h3>Environmental</h3>
        <ul><li>Garbage accumulation</li><li>Drainage obstruction</li><li>Vegetation cover</li></ul>
      </div>
    </div>
  </div>
</section>

<section class="site-band" id="site">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">04 — Study Site</span>
      <h2>Deca Homes, Barangay Tungkil</h2>
      <p>A residential subdivision within Minglanilla, Cebu, selected for its recurring street-flooding reports and mixed drainage infrastructure.</p>
    </div>
    <div class="site-grid">
      <div class="map-card reveal">
        <svg viewBox="0 0 420 320" xmlns="http://www.w3.org/2000/svg">
          <rect width="420" height="320" fill="none"/>
          <g stroke="#8FCBEA" stroke-width="1" opacity="0.5" fill="none">
            <path d="M10,260 C90,230 150,270 220,240 C290,210 340,250 410,220"/>
            <path d="M10,225 C100,200 160,235 230,205 C300,178 350,212 410,185"/>
            <path d="M10,190 C110,168 170,200 240,172 C310,148 355,178 410,152"/>
          </g>
          <g stroke="#DCEAF1" stroke-width="2" opacity="0.85" fill="none">
            <path d="M30,60 L30,280"/>
            <path d="M30,120 L390,120"/>
            <path d="M30,190 L390,190"/>
            <path d="M150,60 L150,280"/>
            <path d="M260,60 L260,280"/>
          </g>
          <circle cx="150" cy="190" r="7" fill="#D9622B"/>
          <circle cx="260" cy="120" r="5" fill="#D9622B" opacity="0.7"/>
          <circle cx="30" cy="190" r="5" fill="#D9622B" opacity="0.7"/>
          <text x="160" y="185" fill="#F2E7DA" font-family="JetBrains Mono, monospace" font-size="10">high-susceptibility node</text>
        </svg>
      </div>
      <ul class="site-list reveal">
        <li><span class="sk">Province</span><span class="sv">Cebu, Central Visayas, Philippines</span></li>
        <li><span class="sk">Municipality</span><span class="sv">Minglanilla</span></li>
        <li><span class="sk">Barangay</span><span class="sv">Tungkil</span></li>
        <li><span class="sk">Site</span><span class="sv">Subdivision Phase 3 — residential road network with box culverts, pipe culverts, and open canals</span></li>
        <li><span class="sk">Stakeholders</span><span class="sv">Municipal Engineering Office, Municipal DRRM Office, Barangay Tungkil</span></li>
      </ul>
    </div>
  </div>
</section>

<section id="weather">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="tag">05 — Weather Monitoring</span>
      <h2>Live weather conditions for the Tungkil study site</h2>
      <p>Check current conditions, short-term rainfall outlook, and the next few hours of forecast data around Subdivision Phase 3.</p>
    </div>
    <div class="weather-shell reveal">
      <div class="weather-card">
        <div class="weather-header">
          <div>
            <div class="weather-badge">📍 Tungkil, Minglanilla, Cebu</div>
            <h3>Weather Forecast</h3>
            <p>Realtime conditions for your selected location.</p>
          </div>
          <div class="weather-controls">
            <input id="lat" class="weather-input" type="number" step="0.0001" value="10.244704" placeholder="Latitude">
            <input id="lon" class="weather-input" type="number" step="0.0001" value="123.813120" placeholder="Longitude">
            <button id="loadBtn" class="weather-button">Get Weather</button>
          </div>
        </div>

        <div id="status" class="weather-status">Loading forecast…</div>

        <div class="weather-current">
          <div class="weather-panel">
            <div class="weather-label">Current conditions</div>
            <div class="weather-temp" id="currentTemp">--°C</div>
            <div id="condition">Fetching data...</div>
            <div class="weather-meta">
              <span class="weather-pill" id="currentWeather">Weather: --</span>
              <span class="weather-pill" id="currentRain">Rain: --</span>
              <span class="weather-pill" id="currentCloud">Cloud cover: --</span>
              <span class="weather-pill" id="currentTime">Time: --</span>
            </div>
          </div>
          <div class="weather-panel">
            <div class="weather-label">Next hours</div>
            <div class="weather-grid" id="forecastList"></div>
          </div>
        </div>

        <div class="weather-panel weather-analysis">
          <div class="weather-label">Rain analysis</div>
          <div id="rainSummary" class="weather-summary">Preparing rain trend...</div>
          <div class="weather-bars" id="rainChart"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  const weatherCodeMap = {
    0: { label: 'Clear sky', icon: '☀️' },
    1: { label: 'Mainly clear', icon: '🌤️' },
    2: { label: 'Partly cloudy', icon: '⛅' },
    3: { label: 'Overcast', icon: '☁️' },
    45: { label: 'Fog', icon: '🌫️' },
    48: { label: 'Rime fog', icon: '🌫️' },
    51: { label: 'Light drizzle', icon: '🌦️' },
    53: { label: 'Moderate drizzle', icon: '🌦️' },
    55: { label: 'Dense drizzle', icon: '🌧️' },
    61: { label: 'Slight rain', icon: '🌧️' },
    63: { label: 'Moderate rain', icon: '🌧️' },
    65: { label: 'Heavy rain', icon: '⛈️' },
    71: { label: 'Slight snow', icon: '🌨️' },
    73: { label: 'Moderate snow', icon: '❄️' },
    75: { label: 'Heavy snow', icon: '❄️' },
    95: { label: 'Thunderstorm', icon: '⛈️' },
    96: { label: 'Thunderstorm with hail', icon: '⛈️' },
    99: { label: 'Severe hail', icon: '⛈️' }
  };

  function formatTime(value) {
    const date = new Date(value);
    return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
  }

  function getWeatherLabel(code) {
    return weatherCodeMap[code] || { label: 'Unknown', icon: '🌈' };
  }

  async function loadWeather() {
    const lat = document.getElementById('lat').value;
    const lon = document.getElementById('lon').value;
    const statusEl = document.getElementById('status');
    const locationLabelEl = document.getElementById('currentTemp');
    const currentTempEl = document.getElementById('currentTemp');
    const conditionEl = document.getElementById('condition');
    const currentWeatherEl = document.getElementById('currentWeather');
    const currentRainEl = document.getElementById('currentRain');
    const currentCloudEl = document.getElementById('currentCloud');
    const currentTimeEl = document.getElementById('currentTime');
    const forecastListEl = document.getElementById('forecastList');
    const rainSummaryEl = document.getElementById('rainSummary');
    const rainChartEl = document.getElementById('rainChart');

    if (!lat || !lon) {
      statusEl.textContent = 'Please enter both latitude and longitude.';
      return;
    }

    statusEl.textContent = 'Fetching weather data...';

    try {
      // const url = `https://api.open-meteo.com/v1/forecast?latitude=${encodeURIComponent(lat)}&longitude=${encodeURIComponent(lon)}&current=temperature_2m,rain,weather_code,cloud_cover&hourly=temperature_2m,precipitation,weather_code,cloud_cover&timezone=auto`;
      const response = await fetch(url);

      if (!response.ok) {
        throw new Error('Unable to fetch weather data.');
      }

      const data = await response.json();
      const current = data.current;
      const hourly = data.hourly || {};
      const weatherInfo = getWeatherLabel(current.weather_code);
      const cloudCover = Math.round(current.cloud_cover ?? 0);
      const temperature = Math.round(current.temperature_2m ?? 0);
      const rainAmount = Math.round((current.rain ?? 0) * 10) / 10;

      currentTempEl.textContent = `${temperature}°C`;
      conditionEl.innerHTML = `${weatherInfo.icon} ${weatherInfo.label}`;
      currentWeatherEl.textContent = `Weather: ${weatherInfo.icon} ${weatherInfo.label}`;
      currentRainEl.textContent = `Rain: ${rainAmount} mm`;
      currentCloudEl.textContent = `Cloud cover: ${cloudCover}%`;
      currentTimeEl.textContent = `Time: ${formatTime(current.time)}`;

      const forecastCards = (hourly.time || []).slice(0, 6).map((time, index) => {
        const hourWeatherCode = hourly.weather_code?.[index];
        const hourWeather = getWeatherLabel(hourWeatherCode);
        const hourRain = hourly.precipitation?.[index] ?? 0;
        const hourCloudCover = Math.round(hourly.cloud_cover?.[index] ?? 0);
        const hourTemp = Math.round(hourly.temperature_2m?.[index] ?? 0);

        return `
          <div class="weather-item">
            <strong>${formatTime(time)}</strong>
            <div class="muted">${hourWeather.icon} ${hourWeather.label}</div>
            <div class="muted">${hourTemp}°C</div>
            <div class="muted">Rain: ${Math.round(hourRain * 10) / 10} mm</div>
            <div class="muted">Cloud: ${hourCloudCover}%</div>
          </div>
        `;
      }).join('');

      forecastListEl.innerHTML = forecastCards;

      const rainValues = (hourly.precipitation || []).slice(0, 6);
      const maxRain = Math.max(...rainValues, 0);
      rainChartEl.innerHTML = rainValues.map((value, index) => {
        const height = maxRain > 0 ? Math.max(12, Math.round((value / maxRain) * 100)) : 12;
        return `
          <div class="weather-bar-wrap">
            <div class="weather-bar" style="height:${height}%"></div>
            <span>${index === 0 ? 'Now' : `+${index}`}</span>
          </div>
        `;
      }).join('');

      rainSummaryEl.innerHTML = `Weather: ${weatherInfo.icon} ${weatherInfo.label} • Rain: ${rainAmount} mm • Cloud cover: ${cloudCover}%`;
      statusEl.textContent = 'Weather data loaded successfully.';
    } catch (error) {
      statusEl.textContent = error.message || 'Something went wrong.';
      forecastListEl.innerHTML = '';
      rainSummaryEl.textContent = 'Rain analysis unavailable.';
      rainChartEl.innerHTML = '';
    }
  }

  document.getElementById('loadBtn').addEventListener('click', loadWeather);
  window.addEventListener('DOMContentLoaded', loadWeather);
</script>

<footer id="team">
  <div class="wrap">
    <div class="foot-grid">
      <div>
        <span class="tag">Research Team</span>
        <h2>Geographic Information System-Based Computational Framework for Multi-Factor Flood Susceptibility Assessment in Minglanilla, Cebu</h2>
        <div class="adviser">Research Adviser — Lyle S. Tolico</div>
      </div>
      <div>
        <span class="tag">Researchers</span>
        <ul class="team-list">
          <li>Kristine Shanaya A. Alguno</li>
          <li>Mary Kacey R. Balorio</li>
          <li>Mary Josephine M. Burgos</li>
          <li>Franze Dianne S. Cañada</li>
          <li>Gabriela Grace C. Carin</li>
          <li>Amara Leigh L. Paraiso</li>
        </ul>
      </div>
    </div>
    <div class="foot-bottom">
      <span>Mathematics &amp; Computational Science · Research Study</span>
      <span>Minglanilla, Cebu, Philippines · June 2026</span>
    </div>
  </div>
</footer>

<script>
  const revealEls = document.querySelectorAll('.reveal');
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{
      if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
    });
  }, { threshold: 0.12 });
  revealEls.forEach(el=>io.observe(el));
</script>

</body>
</html>