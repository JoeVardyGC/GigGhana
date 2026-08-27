<?php
/**
 * GigGhana — terms.php
 * Terms of Service
 * Design system: Volcanic Charcoal × Electric Cyan × Coral
 * Fonts: Plus Jakarta Sans + DM Sans
 * Theme: synced from index.php via localStorage('gg_theme')
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$user        = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
$lastUpdated = 'March 15, 2026';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Terms of Service — GigGhana</title>
<meta name="description" content="GigGhana Terms of Service — the rules, rights, and responsibilities that govern use of Africa's premier freelance marketplace.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
        body:    ['"DM Sans"', 'sans-serif'],
      }
    }
  }
}
</script>

<!-- Flash-free theme sync — reads localStorage('gg_theme') before first paint -->
<script>
(function(){
  if(localStorage.getItem('gg_theme')==='light')
    document.documentElement.classList.add('lm-pre');
})();
</script>

<style>
/* ════════════════════════════════════════════
   DESIGN TOKENS — Dark (exact match to index.php)
════════════════════════════════════════════ */
:root{
  --bg:#0C0E14; --s1:#13161E; --s2:#191D27; --s3:#1F2433;
  --glass:rgba(19,22,30,0.82);
  --cyan:#00D4C8; --cyan-d:#00A89F; --cyan-l:#4DFFE8;
  --cyan-dim:rgba(0,212,200,0.10); --cyan-border:rgba(0,212,200,0.22);
  --coral:#FF6B4A; --coral-d:#E04D2E;
  --coral-dim:rgba(255,107,74,0.10); --coral-border:rgba(255,107,74,0.25);
  --violet:#7C6FF7; --violet-d:#5D52E0;
  --violet-dim:rgba(124,111,247,0.10); --violet-border:rgba(124,111,247,0.22);
  --green:#1FD9A0; --green-d:#13B882; --green-dim:rgba(31,217,160,0.10);
  --amber:#F7B731; --red:#FF4D6A;
  --tx:#F2F4F8; --tx-2:#9BA8BF; --tx-3:#4E5A6E;
  --bd:rgba(255,255,255,0.065); --bd2:rgba(255,255,255,0.12);
  --gC:rgba(0,212,200,0.18); --gO:rgba(255,107,74,0.14);
  --fm:'Plus Jakarta Sans',sans-serif; --fb:'DM Sans',sans-serif;
}

/* ════════════════════════════════════════════
   LIGHT MODE — exact copy of index.php .lm
════════════════════════════════════════════ */
body.lm{
  --bg:#F3F5FA; --s1:#EAEEF7; --s2:#E0E6F2; --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95; --cyan-d:#007870; --cyan-l:#00CFC3;
  --cyan-dim:rgba(0,158,149,0.08); --cyan-border:rgba(0,158,149,0.2);
  --coral:#E8512B; --coral-d:#C43C1C;
  --coral-dim:rgba(232,81,43,0.08); --coral-border:rgba(232,81,43,0.2);
  --violet:#5B4FD9; --violet-d:#4540C0;
  --violet-dim:rgba(91,79,217,0.08); --violet-border:rgba(91,79,217,0.18);
  --green:#0DAF80; --green-d:#088C65; --green-dim:rgba(13,175,128,0.08);
  --amber:#D4980A; --red:#D63251;
  --tx:#0D1220; --tx-2:#344060; --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.09); --bd2:rgba(30,40,80,0.16);
  --gC:rgba(0,158,149,0.14); --gO:rgba(232,81,43,0.12);
}

/* Light mode overrides */
body.lm .navbar       { background:rgba(234,238,247,0.97) !important; border-color:var(--bd); }
body.lm .navbar.on    { box-shadow:0 4px 28px rgba(13,18,32,0.07); }
body.lm .toc-card     { background:rgba(255,255,255,0.85); border-color:var(--bd2); }
body.lm .section-card { background:rgba(255,255,255,0.8);  border-color:var(--bd2); }
body.lm .right-rail   { background:rgba(255,255,255,0.8);  border-color:var(--bd2); }
body.lm .toc-link     { color:var(--tx-2); }
body.lm .toc-link:hover  { color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan-border); }
body.lm .toc-link.active { color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan); }
body.lm .toc-link.active .toc-num { background:var(--cyan); color:#0C0E14; }
body.lm .btn-theme    { border-color:var(--bd2); color:var(--tx-2); }
body.lm .data-row     { border-color:var(--bd2); }
body.lm .data-row:hover { background:var(--cyan-dim); }
body.lm .grid-tex     {
  background-image: linear-gradient(rgba(30,40,80,0.02) 1px,transparent 1px),
                    linear-gradient(90deg,rgba(30,40,80,0.02) 1px,transparent 1px);
}

/* ════ BASE RESET ════ */
*,*::before,*::after{ box-sizing:border-box; margin:0; padding:0; }
html{ scroll-behavior:smooth; }
body{
  background:var(--bg); color:var(--tx); font-family:var(--fb);
  min-height:100svh; overflow-x:hidden; -webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
html.lm-pre body, html.lm-pre body *{ transition:none !important; }
::-webkit-scrollbar{ width:5px; }
::-webkit-scrollbar-track{ background:var(--bg); }
::-webkit-scrollbar-thumb{ background:var(--s3); border-radius:3px; }
::-webkit-scrollbar-thumb:hover{ background:var(--cyan-d); }

/* ── Gradient bar ── */
.grad-bar{
  position:fixed; top:0; left:0; right:0; height:2px; z-index:300;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--green),var(--cyan));
  background-size:300% 100%; animation:gradShift 5s linear infinite;
}
@keyframes gradShift{ 0%{background-position:0% 50%} 100%{background-position:300% 50%} }

/* ── Background ── */
.grid-tex{
  position:fixed; inset:0; pointer-events:none; z-index:0;
  background-image:
    linear-gradient(rgba(255,255,255,0.012) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,0.012) 1px,transparent 1px);
  background-size:52px 52px;
}
.blob{ position:fixed; border-radius:50%; filter:blur(100px); pointer-events:none; z-index:0; }
.blob-1{ width:600px; height:600px; background:radial-gradient(circle,rgba(0,212,200,0.07),transparent 70%); top:-200px; right:-100px; }
.blob-2{ width:400px; height:400px; background:radial-gradient(circle,rgba(0,212,200,0.04),transparent 70%); bottom:-100px; left:-80px; }
.blob-3{ width:250px; height:250px; background:radial-gradient(circle,rgba(124,111,247,0.06),transparent 70%); top:40%; left:5%; }

/* ── Navbar ── */
.navbar{
  position:fixed; top:0; left:0; right:0; z-index:200;
  display:flex; align-items:center; justify-content:space-between;
  padding:0 5%; height:64px;
  background:rgba(12,14,20,0.88); backdrop-filter:blur(24px);
  border-bottom:1px solid var(--bd); transition:all .26s;
}
.navbar.on{ background:rgba(12,14,20,0.97); box-shadow:0 2px 30px rgba(0,0,0,0.5); }
.logo{ display:flex; align-items:center; gap:9px; text-decoration:none; }
.logo-mark{
  width:34px; height:34px; border-radius:9px; flex-shrink:0;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-weight:800; font-size:15px; color:#0C0E14;
  box-shadow:0 3px 12px var(--gC);
}
.logo-text{ font-family:var(--fm); font-size:19px; font-weight:800; color:var(--tx); }
.logo-text span{ color:var(--cyan); }
.nav-right{ display:flex; align-items:center; gap:10px; }
.btn-theme{
  background:transparent; color:var(--tx-2); border:1px solid var(--bd);
  border-radius:10px; padding:7px 11px; cursor:pointer; font-size:14px;
  transition:all .26s; line-height:1; font-family:var(--fb);
}
.btn-theme:hover{ background:rgba(255,255,255,0.07); }
.nav-btn{
  display:inline-flex; align-items:center; gap:6px;
  padding:7px 16px; border-radius:9px; font-size:13px; font-weight:600;
  text-decoration:none; transition:all .22s; font-family:var(--fm);
}
.nav-btn-ghost{ color:var(--tx-2); border:1px solid var(--bd); }
.nav-btn-ghost:hover{ color:var(--tx); border-color:var(--bd2); background:rgba(255,255,255,0.05); }
.nav-btn-cyan{
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  color:#0C0E14; font-weight:700; box-shadow:0 3px 14px var(--gC);
}
.nav-btn-cyan:hover{ transform:translateY(-1px); box-shadow:0 6px 20px var(--gC); }

/* ── Gradient text ── */
.grad-text{
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan),var(--coral));
  background-size:200% auto;
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
  animation:gradShift 4s ease infinite;
}

/* ── Hero badge ── */
.hero-badge{
  display:inline-flex; align-items:center; gap:8px;
  padding:5px 14px; border-radius:50px; margin-bottom:16px;
  font-family:var(--fm); font-weight:800; font-size:10px;
  letter-spacing:1.4px; text-transform:uppercase;
  background:var(--violet-dim); border:1px solid var(--violet-border); color:var(--violet);
}
.pulse-dot{
  width:6px; height:6px; border-radius:50%; background:var(--violet);
  animation:pDot 2s ease infinite; flex-shrink:0;
}
@keyframes pDot{ 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(124,111,247,.4);} 50%{opacity:.2;box-shadow:0 0 0 6px rgba(124,111,247,0);} }

/* ── TOC sidebar ── */
.toc-card{
  position:sticky; top:84px;
  background:var(--glass); backdrop-filter:blur(14px);
  border:1px solid var(--bd); border-radius:16px;
  padding:20px; overflow-y:auto; max-height:calc(100vh - 110px);
}
.toc-title{
  font-family:var(--fm); font-weight:800; font-size:11px;
  text-transform:uppercase; letter-spacing:1.2px;
  color:var(--tx-3); margin-bottom:14px;
}
.toc-link{
  display:flex; align-items:center; gap:9px;
  padding:7px 10px; border-radius:8px; border-left:2px solid transparent;
  color:var(--tx-3); font-size:13px; font-weight:500;
  text-decoration:none; transition:all .2s; cursor:pointer; line-height:1.3;
}
.toc-link:hover{ color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan-border); }
.toc-link.active{ color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan); }
.toc-num{
  width:20px; height:20px; border-radius:6px; flex-shrink:0;
  background:var(--s3); display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-weight:800; font-size:9px; color:var(--tx-3);
}
.toc-link.active .toc-num{ background:var(--cyan); color:#0C0E14; }

/* ── SECTION CARDS ── */
.section-card{
  background:var(--glass); backdrop-filter:blur(12px);
  border:1px solid var(--bd); border-radius:18px;
  padding:32px 36px; margin-bottom:20px;
  scroll-margin-top:84px; transition:border-color .25s;
}
.section-card:hover{ border-color:rgba(0,212,200,0.14); }
.section-num{
  display:inline-flex; align-items:center; justify-content:center;
  width:28px; height:28px; border-radius:8px; flex-shrink:0;
  font-family:var(--fm); font-weight:800; font-size:11px; color:#0C0E14;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  box-shadow:0 3px 10px var(--gC); margin-right:10px;
}
.section-title{
  font-family:var(--fm); font-weight:800;
  font-size:clamp(17px,2vw,20px); color:var(--tx);
  letter-spacing:-.3px; margin-bottom:16px;
  display:flex; align-items:center;
}
.section-title .s-icon{ font-size:20px; margin-right:10px; }
.prose{ color:var(--tx-2); font-size:14.5px; line-height:1.85; font-weight:400; }
.prose p{ margin-bottom:13px; }
.prose p:last-child{ margin-bottom:0; }
.prose strong{ color:var(--tx); font-weight:600; }
.prose a{ color:var(--cyan); text-decoration:none; font-weight:500; }
.prose a:hover{ text-decoration:underline; }
.prose ul, .prose ol{ padding-left:18px; margin:10px 0; display:flex; flex-direction:column; gap:7px; }
.prose li{ color:var(--tx-2); font-size:14px; line-height:1.65; }

/* ── Highlight boxes ── */
.highlight-box{
  border-radius:12px; padding:16px 18px; margin:16px 0;
  border-left:3px solid;
}
.highlight-cyan  { background:var(--cyan-dim);  border-color:var(--cyan); }
.highlight-coral { background:var(--coral-dim); border-color:var(--coral); }
.highlight-green { background:var(--green-dim); border-color:var(--green); }
.highlight-violet{ background:var(--violet-dim);border-color:var(--violet); }
.highlight-amber { background:rgba(247,183,49,0.08); border-color:var(--amber); }
.h-label{
  font-family:var(--fm); font-weight:700; font-size:11px;
  text-transform:uppercase; letter-spacing:.8px; margin-bottom:7px;
}
.h-label-cyan  { color:var(--cyan); }
.h-label-coral { color:var(--coral); }
.h-label-green { color:var(--green); }
.h-label-violet{ color:var(--violet); }
.h-label-amber { color:var(--amber); }

/* ── Fees table ── */
.data-row{
  display:grid; gap:12px; padding:12px 14px;
  border-bottom:1px solid var(--bd);
  font-size:13px; transition:background .2s;
}
.data-row:hover{ background:rgba(0,212,200,0.03); }
.data-row.header{
  font-family:var(--fm); font-weight:700; font-size:10.5px;
  text-transform:uppercase; letter-spacing:.8px; color:var(--tx-3);
  border-bottom:2px solid var(--bd2);
  background:rgba(255,255,255,0.02); border-radius:8px 8px 0 0;
}
.data-val{ color:var(--tx-2); }
.data-bold{ color:var(--tx); font-weight:600; }
.data-badge{
  display:inline-flex; align-items:center; gap:5px;
  padding:2px 9px; border-radius:50px; font-size:10px; font-weight:700;
  font-family:var(--fm);
}
.badge-free   { background:var(--green-dim); color:var(--green); border:1px solid rgba(31,217,160,.22); }
.badge-paid   { background:var(--violet-dim); color:var(--violet); border:1px solid var(--violet-border); }
.badge-escrow { background:var(--cyan-dim);  color:var(--cyan);  border:1px solid var(--cyan-border); }

/* ── Tier cards ── */
.tier-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin:16px 0; }
.tier-card{
  background:rgba(255,255,255,0.025); border:1px solid var(--bd);
  border-radius:14px; padding:20px; text-align:center; transition:all .25s;
}
.tier-card:hover{ transform:translateY(-3px); }
.tier-card.featured{ border-color:var(--cyan-border); background:var(--cyan-dim); }
.tier-icon{ font-size:28px; margin-bottom:10px; }
.tier-name{ font-family:var(--fm); font-weight:800; font-size:15px; color:var(--tx); margin-bottom:4px; }
.tier-price{ font-size:12px; color:var(--tx-3); margin-bottom:10px; }
.tier-perks{ font-size:12px; color:var(--tx-2); line-height:1.6; }

/* ── Do / Don't grid ── */
.do-dont{ display:grid; grid-template-columns:1fr 1fr; gap:14px; margin:14px 0; }
.do-card{
  background:var(--green-dim); border:1px solid rgba(31,217,160,.2);
  border-radius:12px; padding:16px;
}
.dont-card{
  background:var(--violet-dim); border:1px solid var(--violet-border);
  border-radius:12px; padding:16px;
}
.dd-title{
  font-family:var(--fm); font-weight:800; font-size:12px;
  text-transform:uppercase; letter-spacing:.8px; margin-bottom:10px;
}
.do-card .dd-title{ color:var(--green); }
.dont-card .dd-title{ color:var(--violet); }
.dd-list{ list-style:none; display:flex; flex-direction:column; gap:7px; }
.dd-list li{ display:flex; gap:8px; font-size:12.5px; color:var(--tx-2); line-height:1.5; }
.dd-ico{ flex-shrink:0; }

/* ── Contact card ── */
.contact-card{
  background:linear-gradient(135deg,var(--cyan-dim),var(--violet-dim));
  border:1px solid var(--cyan-border); border-radius:16px;
  padding:24px 28px; text-align:center;
}
.contact-card h3{ font-family:var(--fm); font-weight:800; font-size:18px; margin-bottom:8px; color:var(--tx); }
.contact-card p{ color:var(--tx-2); font-size:14px; margin-bottom:16px; line-height:1.6; }
.contact-btn{
  display:inline-flex; align-items:center; gap:8px;
  padding:11px 22px; border-radius:11px; text-decoration:none;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  color:#0C0E14; font-family:var(--fm); font-weight:700; font-size:13.5px;
  transition:all .26s; box-shadow:0 4px 16px var(--gC);
}
.contact-btn:hover{ transform:translateY(-2px); box-shadow:0 8px 24px var(--gC); }

/* ── Right rail ── */
.right-rail{
  position:sticky; top:84px;
  background:var(--glass); backdrop-filter:blur(14px);
  border:1px solid var(--bd); border-radius:16px; padding:20px;
}
.rail-title{ font-family:var(--fm); font-weight:800; font-size:11px; text-transform:uppercase; letter-spacing:1.2px; color:var(--tx-3); margin-bottom:14px; }
.rail-item{ display:flex; align-items:flex-start; gap:10px; margin-bottom:12px; }
.rail-ico{ font-size:16px; flex-shrink:0; margin-top:1px; }
.rail-text{ font-size:12.5px; color:var(--tx-2); line-height:1.5; }
.rail-text strong{ color:var(--tx); font-weight:600; }

/* ── Read progress + back to top ── */
.read-bar{
  position:fixed; top:2px; left:0; height:2px; z-index:301;
  background:var(--cyan); width:0%; transition:width .1s linear;
}
.back-top{
  position:fixed; bottom:24px; right:24px; z-index:100;
  width:42px; height:42px; border-radius:12px;
  background:var(--s2); border:1px solid var(--bd);
  display:flex; align-items:center; justify-content:center;
  color:var(--tx-2); font-size:16px; cursor:pointer;
  transition:all .26s; opacity:0; pointer-events:none;
  box-shadow:0 4px 18px rgba(0,0,0,0.3);
}
.back-top.show{ opacity:1; pointer-events:auto; }
.back-top:hover{ background:var(--cyan-dim); color:var(--cyan); border-color:var(--cyan-border); }

/* ── Footer ── */
.footer{ background:var(--s1); border-top:1px solid var(--bd); padding:40px 5%; margin-top:60px; }
.footer-inner{ max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; }
.footer-copy{ font-size:13px; color:var(--tx-3); }
.footer-links{ display:flex; gap:20px; flex-wrap:wrap; }
.footer-links a{ font-size:13px; color:var(--tx-3); text-decoration:none; transition:color .2s; }
.footer-links a:hover{ color:var(--cyan); }
.footer-links a.active-page{ color:var(--cyan); font-weight:600; }

/* ── Slide-up animation ── */
.su{ animation:suA .5s ease both; }
.su-1{ animation-delay:.05s; } .su-2{ animation-delay:.12s; } .su-3{ animation-delay:.19s; }
@keyframes suA{ from{opacity:0;transform:translateY(14px);} to{opacity:1;transform:translateY(0);} }

/* ── Responsive ── */
@media(max-width:1024px){
  .layout-grid{ grid-template-columns:1fr !important; }
  .toc-card, .right-rail{ display:none; }
}
@media(max-width:768px){
  .section-card{ padding:22px 18px; }
  .tier-grid{ grid-template-columns:1fr; }
  .do-dont{ grid-template-columns:1fr; }
  .data-row{ font-size:12px; }
  .navbar{ padding:0 4%; }
}
</style>
</head>

<body class="">
<script>
if(document.documentElement.classList.contains('lm-pre')){
  document.body.classList.add('lm');
  document.documentElement.classList.remove('lm-pre');
}
</script>

<!-- Reading progress bar -->
<div class="read-bar" id="readBar"></div>

<!-- Gradient bar -->
<div class="grad-bar"></div>

<!-- Background layers -->
<div class="grid-tex"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<!-- ════════════════════════════════════════
     NAVBAR
════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
  <a href="<?= APP_URL ?>/index.php" class="logo">
    <div class="logo-mark">G</div>
    <span class="logo-text">Gig<span>Ghana</span></span>
  </a>
  <div class="nav-right">
    <button class="btn-theme" id="themeBtn" onclick="toggleTheme()" title="Toggle theme">🌙</button>
    <?php if($user): ?>
      <a href="<?= APP_URL ?>/<?= $user['role'] ?>/dashboard.php" class="nav-btn nav-btn-ghost">Dashboard</a>
    <?php else: ?>
      <a href="<?= APP_URL ?>/auth/login.php" class="nav-btn nav-btn-ghost">Sign In</a>
      <a href="<?= APP_URL ?>/auth/register.php" class="nav-btn nav-btn-cyan">Get Started</a>
    <?php endif; ?>
  </div>
</nav>

<!-- ════════════════════════════════════════
     HERO
════════════════════════════════════════ -->
<section class="relative z-10 pt-24 pb-12 px-5" style="max-width:1200px;margin:0 auto;">
  <div class="su su-1">
    <div class="hero-badge">
      <span class="pulse-dot"></span>
      Legal Agreement
    </div>
    <h1 class="font-heading font-black leading-tight tracking-[-1.5px] mb-4"
        style="font-size:clamp(32px,4vw,52px);color:var(--tx);">
      Terms of <span class="grad-text">Service</span>
    </h1>
    <div class="flex flex-wrap items-center gap-4 text-[13.5px]" style="color:var(--tx-3);">
      <span class="flex items-center gap-1.5">
        <span style="color:var(--coral);">📅</span>
        Last updated: <strong style="color:var(--tx-2);"><?= $lastUpdated ?></strong>
      </span>
      <span class="w-px h-4" style="background:var(--bd);"></span>
      <span class="flex items-center gap-1.5">
        <span style="color:var(--green);">🇬🇭</span>
        Governed by Ghanaian law
      </span>
      <span class="w-px h-4" style="background:var(--bd);"></span>
      <span class="flex items-center gap-1.5">
        <span style="color:var(--amber);">⏱</span>
        ~10 min read
      </span>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════
     MAIN LAYOUT — 3-column
════════════════════════════════════════ -->
<div class="layout-grid relative z-10 px-5 pb-20"
     style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:220px 1fr 220px;gap:28px;align-items:start;">

  <!-- ════ LEFT — Table of Contents ════ -->
  <aside>
    <div class="toc-card su su-2">
      <div class="toc-title">On This Page</div>
      <?php
      $toc = [
        ['1',  'Agreement Overview',    '📋', 'overview'],
        ['2',  'Eligibility',           '🪪', 'eligibility'],
        ['3',  'Account Rules',         '👤', 'accounts'],
        ['4',  'Client Terms',          '🏢', 'clients'],
        ['5',  'Provider Terms',        '💼', 'providers'],
        ['6',  'Subscription Tiers',    '⭐', 'tiers'],
        ['7',  'Escrow & Payments',     '💰', 'payments'],
        ['8',  'Fees & Charges',        '📊', 'fees'],
        ['9',  'Prohibited Conduct',    '🚫', 'prohibited'],
        ['10', 'Intellectual Property', '©',  'ip'],
        ['11', 'Disputes',             '⚖️', 'disputes'],
        ['12', 'Limitation of Liability','🛡','liability'],
        ['13', 'Termination',           '🚪', 'termination'],
        ['14', 'Governing Law',         '🏛', 'law'],
        ['15', 'Changes to Terms',      '📝', 'changes'],
        ['16', 'Contact Us',            '📧', 'contact'],
      ];
      foreach($toc as [$num,$title,$ico,$id]):
      ?>
      <a class="toc-link" href="#<?= $id ?>" onclick="setActive(this)">
        <div class="toc-num"><?= $num ?></div>
        <?= $ico ?> <?= $title ?>
      </a>
      <?php endforeach; ?>
    </div>
  </aside>

  <!-- ════ CENTRE — Content ════ -->
  <main>

    <!-- ── INTRO CARD ── -->
    <div class="section-card su su-2" style="border-color:var(--cyan-border);background:linear-gradient(135deg,rgba(0,212,200,0.06),rgba(124,111,247,0.04));">
      <p class="prose" style="font-size:15px;">
        These Terms of Service ("<strong>Terms</strong>") form a legally binding agreement between
        <strong>GigGhana Ltd</strong> ("<strong>GigGhana</strong>", "<strong>we</strong>", "<strong>us</strong>") and
        <strong>you</strong>, any person or entity accessing or using the GigGhana platform at
        <a href="<?= APP_URL ?>"><?= APP_URL ?></a>. By creating an account or using any GigGhana service you
        confirm you have read, understood, and agreed to these Terms in full.
      </p>
      <div class="highlight-box highlight-coral" style="margin-top:16px;margin-bottom:0;">
        <div class="h-label h-label-coral">⚠️ Important — Please Read Carefully</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          These Terms include provisions limiting our liability and requiring individual arbitration of disputes.
          If you do not agree to these Terms, you must not use the GigGhana platform.
        </p>
      </div>
    </div>

    <!-- ── 1. OVERVIEW ── -->
    <div class="section-card su su-3" id="overview">
      <div class="section-title">
        <span class="section-num">1</span>
        <span class="s-icon">📋</span>
        Agreement Overview
      </div>
      <div class="prose">
        <p>GigGhana operates a two-sided online marketplace that enables <strong>Clients</strong> to post jobs and hire skilled individuals, and <strong>Providers</strong> (freelancers) to offer and deliver services in exchange for payment. GigGhana acts as an intermediary platform and is not a party to any contract formed between Clients and Providers.</p>
        <p>These Terms govern your use of the Platform, including registration, posting jobs or services, making or receiving payments, using the escrow system, and all communications on the Platform.</p>
        <p>By using GigGhana you also agree to our <a href="<?= APP_URL ?>/privacy.php">Privacy Policy</a>, which is incorporated into these Terms by reference.</p>
      </div>
      <div class="highlight-box highlight-amber" style="margin-bottom:0;">
        <div class="h-label h-label-amber">📌 Definitions</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
          <?php
          $defs = [
            ['"Client"','A user who posts jobs and hires Providers.'],
            ['"Provider"','A freelancer or skilled tradesperson who offers services.'],
            ['"Job"','A task or project posted by a Client on the Platform.'],
            ['"Proposal"','A bid submitted by a Provider for a posted Job.'],
            ['"Escrow"','Funds held in trust by GigGhana until job completion.'],
            ['"Platform"','The GigGhana website, app, and all associated services.'],
          ];
          foreach($defs as [$term,$def]):
          ?>
          <div style="background:rgba(255,255,255,0.025);border-radius:9px;padding:10px 12px;">
            <div style="font-family:var(--fm);font-weight:700;font-size:11.5px;color:var(--amber);margin-bottom:3px;"><?= $term ?></div>
            <div style="font-size:12px;color:var(--tx-3);line-height:1.5;"><?= $def ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── 2. ELIGIBILITY ── -->
    <div class="section-card" id="eligibility">
      <div class="section-title">
        <span class="section-num">2</span>
        <span class="s-icon">🪪</span>
        Eligibility
      </div>
      <div class="prose">
        <p>To use GigGhana you must meet all of the following requirements at the time of registration and throughout your use of the Platform:</p>
      </div>
      <div class="do-dont" style="margin-top:14px;">
        <div class="do-card">
          <div class="dd-title">✅ You Must Be</div>
          <ul class="dd-list">
            <?php
            $must = [
              'At least <strong>18 years of age</strong>',
              'A <strong>legal resident</strong> of Ghana or an African nation',
              'Capable of forming a binding legal contract',
              'Registered with a <strong>valid email address</strong>',
              'Using the Platform for <strong>lawful purposes only</strong>',
              'An individual acting on your own behalf, or authorised to act on behalf of a company',
            ];
            foreach($must as $m):
            ?><li><span class="dd-ico">✓</span><span><?= $m ?></span></li><?php endforeach; ?>
          </ul>
        </div>
        <div class="dont-card">
          <div class="dd-title">🚫 You Must Not Be</div>
          <ul class="dd-list">
            <?php
            $mustNot = [
              'Under 18 years of age',
              'Previously <strong>banned or suspended</strong> from GigGhana',
              'Acting under a false identity or impersonating another person',
              'Located in a jurisdiction where use is <strong>legally prohibited</strong>',
              'A competitor attempting to access proprietary information',
              'An automated bot or non-human agent (without written consent)',
            ];
            foreach($mustNot as $m):
            ?><li><span class="dd-ico">✕</span><span><?= $m ?></span></li><?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- ── 3. ACCOUNT RULES ── -->
    <div class="section-card" id="accounts">
      <div class="section-title">
        <span class="section-num">3</span>
        <span class="s-icon">👤</span>
        Account Rules &amp; Responsibilities
      </div>
      <div class="prose">
        <p>When you create a GigGhana account, you are responsible for maintaining its security and for all activity that occurs under it.</p>
      </div>
      <?php
      $accountRules = [
        ['coral','🔑 Account Security','You must use a strong password (8+ characters, uppercase, number) and must not share your login credentials with any third party. You are liable for any unauthorised use of your account. Notify us immediately at <a href="mailto:support@gigghana.com">support@gigghana.com</a> if you suspect a breach.'],
        ['cyan','✅ Accurate Information','All information you provide — name, skills, rates, location, Ghana Card number — must be truthful, accurate, and kept up to date. Providing false information may result in immediate account termination.'],
        ['violet','🔗 One Account Per Person','Each person may maintain only one active account per role. Operating multiple accounts to circumvent suspension, manipulate reviews, or gain unfair advantage is strictly prohibited.'],
        ['green','📧 Email Verification','You must verify your email address via the 6-digit OTP sent during registration. Unverified accounts have limited access to Platform features.'],
        ['amber','🪪 Identity Verification','GigGhana may require Ghana Card verification for Providers seeking Verified or Premium status. Providing fraudulent identity documents will result in permanent account termination and may be reported to law enforcement.'],
      ];
      foreach($accountRules as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── 4. CLIENT TERMS ── -->
    <div class="section-card" id="clients">
      <div class="section-title">
        <span class="section-num">4</span>
        <span class="s-icon">🏢</span>
        Client Terms
      </div>
      <div class="prose">
        <p>As a Client on GigGhana, you agree to the following when posting jobs and hiring Providers:</p>
        <ul>
          <li><strong>Accurate Job Descriptions:</strong> Job posts must accurately describe the scope, deliverables, timeline, and budget. Misleading posts will be removed.</li>
          <li><strong>Timely Escrow Funding:</strong> Before a Provider begins work, you must fund the escrow with the agreed amount. Work may not begin until escrow is confirmed.</li>
          <li><strong>Fair Review:</strong> Once a job is completed, you agree to review the work honestly and release escrow funds within <strong>7 days</strong> of delivery, or raise a dispute through the Platform.</li>
          <li><strong>No Off-Platform Payments:</strong> You must not attempt to make payments to Providers outside the Platform to circumvent fees. Doing so violates these Terms and voids escrow protection.</li>
          <li><strong>Respectful Communication:</strong> You must communicate with Providers professionally and in good faith. Harassment, threats, or abusive behaviour will result in account suspension.</li>
          <li><strong>Job Cancellation:</strong> If you cancel a funded job before work begins, escrow funds will be refunded minus any processing fees. Cancellations after work has begun require dispute resolution.</li>
        </ul>
      </div>
    </div>

    <!-- ── 5. PROVIDER TERMS ── -->
    <div class="section-card" id="providers">
      <div class="section-title">
        <span class="section-num">5</span>
        <span class="s-icon">💼</span>
        Provider Terms
      </div>
      <div class="prose">
        <p>As a Provider on GigGhana, you agree to the following when offering and delivering services:</p>
        <ul>
          <li><strong>Accurate Profiles:</strong> Your skills, experience, rates, and portfolio must be genuine. Falsely representing qualifications you do not have is grounds for termination.</li>
          <li><strong>Delivery Commitment:</strong> Once you accept a job and escrow is funded, you are contractually obligated to deliver the agreed work by the agreed deadline, or communicate delays promptly.</li>
          <li><strong>Quality Standards:</strong> Work must meet the standards agreed with the Client. Repeatedly poor-quality submissions may result in account suspension.</li>
          <li><strong>Free Tier Limits:</strong> Beginner accounts include <strong>3 free job applications</strong>. Additional applications require a Verified (₵49/mo) or Premium (₵99/mo) subscription.</li>
          <li><strong>No Soliciting Off-Platform:</strong> You must not request or accept payments outside GigGhana during or after the job. Violations will result in permanent bans.</li>
          <li><strong>Tax Obligations:</strong> As an independent contractor, you are solely responsible for declaring and paying income tax, VAT, or any other taxes arising from your earnings. GigGhana does not withhold taxes.</li>
          <li><strong>Independent Contractor:</strong> You are an independent contractor, not an employee, agent, or partner of GigGhana. You have no authority to bind GigGhana to any agreement.</li>
        </ul>
      </div>
      <div class="highlight-box highlight-cyan" style="margin-bottom:0;">
        <div class="h-label h-label-cyan">💡 Provider Tip</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          Your rating, completion rate, and response time directly affect your search ranking. Maintaining a rating above 4.5★ and responding to messages within 24 hours significantly increases your visibility to Clients.
        </p>
      </div>
    </div>

    <!-- ── 6. SUBSCRIPTION TIERS ── -->
    <div class="section-card" id="tiers">
      <div class="section-title">
        <span class="section-num">6</span>
        <span class="s-icon">⭐</span>
        Provider Subscription Tiers
      </div>
      <div class="prose">
        <p>GigGhana offers three subscription tiers for Providers. Fees are charged monthly and are non-refundable except as required by Ghanaian consumer law.</p>
      </div>
      <div class="tier-grid">
        <div class="tier-card">
          <div class="tier-icon">🌱</div>
          <div class="tier-name">Beginner</div>
          <div class="tier-price">Free · 3 jobs included</div>
          <div class="tier-perks">Basic profile listing, access to open jobs, standard search placement, community support.</div>
        </div>
        <div class="tier-card featured">
          <div class="tier-icon">✅</div>
          <div class="tier-name">Verified</div>
          <div class="tier-price">₵49/month · Unlimited</div>
          <div class="tier-perks">Verified badge, unlimited job applications, higher search ranking, Ghana Card verification, priority in search results.</div>
        </div>
        <div class="tier-card">
          <div class="tier-icon">⭐</div>
          <div class="tier-name">Premium</div>
          <div class="tier-price">₵99/month · Top placement</div>
          <div class="tier-perks">Featured listing, top search placement, exclusive Premium jobs, dedicated account support, analytics dashboard.</div>
        </div>
      </div>
      <div class="prose" style="margin-top:14px;">
        <p>Subscriptions auto-renew monthly unless cancelled at least <strong>24 hours before</strong> the renewal date. Downgrades take effect at the end of the current billing period. GigGhana reserves the right to adjust pricing with 30 days' notice.</p>
      </div>
    </div>

    <!-- ── 7. ESCROW & PAYMENTS ── -->
    <div class="section-card" id="payments">
      <div class="section-title">
        <span class="section-num">7</span>
        <span class="s-icon">💰</span>
        Escrow System &amp; Payment Processing
      </div>
      <div class="prose">
        <p>GigGhana's escrow system protects both Clients and Providers by holding funds securely until the agreed work is completed and approved.</p>
      </div>
      <?php
      $escrowSteps = [
        ['🏦','1. Client Funds Escrow','Before work begins, the Client deposits the agreed amount into GigGhana escrow via Paystack (card, MTN MoMo, Vodafone Cash, or AirtelTigo). The Provider receives a notification that escrow is funded and may begin work.'],
        ['⚙️','2. Provider Delivers Work','The Provider completes and submits the agreed deliverables through the Platform. The Client receives a notification to review the work.'],
        ['✅','3. Client Approves','The Client has <strong>7 days</strong> to approve the work and release escrow, or raise a dispute. If no action is taken within 7 days, escrow is automatically released to the Provider.'],
        ['💸','4. Provider Receives Payment','Upon release, the net amount (after GigGhana platform fee) is transferred to the Provider linked MoMo number or bank account within <strong>1-3 business days</strong>.'],
      ];
      foreach($escrowSteps as [$ico,$title,$desc]):
      ?>
      <div class="highlight-box highlight-cyan" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-cyan"><?= $ico ?> <?= $title ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
      <div class="highlight-box highlight-coral" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-coral">⚠️ Refund Policy</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          Escrow funds are released to Providers upon Client approval. Refunds to Clients are only issued: (a) before any work has begun, (b) where GigGhana's dispute resolution team rules in favour of the Client, or (c) as required by Ghanaian consumer protection law. Payment processing fees charged by Paystack are non-refundable in all cases.
        </p>
      </div>
    </div>

    <!-- ── 8. FEES ── -->
    <div class="section-card" id="fees">
      <div class="section-title">
        <span class="section-num">8</span>
        <span class="s-icon">📊</span>
        Fees &amp; Charges
      </div>
      <div class="prose">
        <p>GigGhana charges the following fees. All amounts are in <strong>Ghana Cedis (GHS)</strong> and are inclusive of any applicable taxes.</p>
      </div>
      <div style="margin-top:16px;border-radius:12px;overflow:hidden;border:1px solid var(--bd);">
        <div class="data-row header" style="grid-template-columns:1fr 1fr 1.2fr;">
          <span>Fee Type</span>
          <span>Amount</span>
          <span>Who Pays</span>
        </div>
        <?php
        $fees = [
          ['Platform Commission','10% of job value','Deducted from Provider payout','badge-paid'],
          ['Client Job Posting','Free','Client — no charge to post','badge-free'],
          ['Beginner Provider','Free (3 jobs)','Provider — free tier','badge-free'],
          ['Verified Subscription','₵49/month','Provider (monthly)','badge-paid'],
          ['Premium Subscription','₵99/month','Provider (monthly)','badge-paid'],
          ['Escrow Hold','0% — no hold fee','Neither party','badge-free'],
          ['Paystack Processing','~1.5% + ₵1','Absorbed by GigGhana (card)','badge-escrow'],
          ['MoMo Transfer Fee','₵0.50–₵2 per transfer','Provider (on payout)','badge-paid'],
          ['Dispute Filing','Free','Either party','badge-free'],
          ['Account Reinstatement','₵50 (if suspended)','Provider','badge-paid'],
        ];
        foreach($fees as [$type,$amount,$who,$badge]):
        ?>
        <div class="data-row" style="grid-template-columns:1fr 1fr 1.2fr;">
          <span class="data-bold"><?= $type ?></span>
          <span class="data-val"><?= $amount ?></span>
          <span>
            <span class="data-badge <?= $badge ?>"><?= $who ?></span>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="highlight-box highlight-amber" style="margin-top:14px;margin-bottom:0;">
        <div class="h-label h-label-amber">💡 Fee Changes</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          GigGhana reserves the right to revise fee structures at any time, with at least <strong>30 days' prior notice</strong> to affected users via email and Platform notification. Continued use after the effective date constitutes acceptance of the new fees.
        </p>
      </div>
    </div>

    <!-- ── 9. PROHIBITED CONDUCT ── -->
    <div class="section-card" id="prohibited">
      <div class="section-title">
        <span class="section-num">9</span>
        <span class="s-icon">🚫</span>
        Prohibited Conduct
      </div>
      <div class="prose">
        <p>The following activities are strictly prohibited on GigGhana and may result in immediate account suspension, permanent ban, and/or legal action:</p>
      </div>
      <div class="do-dont" style="margin-top:14px;">
        <div class="dont-card" style="grid-column:1/-1;">
          <div class="dd-title">🚫 Strictly Prohibited</div>
          <ul class="dd-list" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <?php
            $prohibited = [
              'Creating fake accounts or impersonating others',
              'Posting fraudulent jobs or fake Proposals',
              'Soliciting or accepting payments outside the Platform',
              'Manipulating reviews, ratings, or feedback',
              'Spamming, phishing, or sending unsolicited messages',
              'Uploading malware, viruses, or malicious code',
              'Violating any Ghanaian or international law',
              'Posting illegal content, hate speech, or adult material',
              'Attempting to hack, scrape, or reverse-engineer the Platform',
              'Money laundering or using the Platform for financial crime',
              'Harassing, threatening, or abusing other users',
              'Using GigGhana to facilitate human trafficking or exploitation',
              'Circumventing bans through new accounts',
              'Sharing another user\'s private information without consent',
            ];
            foreach($prohibited as $p):
            ?><li><span class="dd-ico">✕</span><span><?= $p ?></span></li><?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- ── 10. INTELLECTUAL PROPERTY ── -->
    <div class="section-card" id="ip">
      <div class="section-title">
        <span class="section-num">10</span>
        <span class="s-icon">©</span>
        Intellectual Property
      </div>
      <?php
      $ipItems = [
        ['violet','🏢 GigGhana IP','All Platform content — including the GigGhana name, logo, design, software, algorithms, and text — is owned by GigGhana Ltd and protected by Ghanaian and international intellectual property law. You may not copy, reproduce, distribute, or create derivative works without our written consent.'],
        ['cyan','✍️ Your Content','Content you create and post on the Platform (portfolio items, job descriptions, messages, reviews) remains yours. By posting it, you grant GigGhana a non-exclusive, royalty-free, worldwide licence to display, store, and transmit it solely for the purpose of operating the Platform.'],
        ['green','📦 Deliverables Ownership','Unless otherwise agreed in writing between the Client and Provider, ownership of work deliverables transfers to the Client upon full and final payment of escrow. Providers retain ownership until full payment is received.'],
        ['amber','⚠️ Third-Party IP','You must not post, upload, or deliver content that infringes any third party\'s copyright, trademark, patent, or trade secret. You are solely liable for any IP infringement claims arising from your content.'],
      ];
      foreach($ipItems as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── 11. DISPUTES ── -->
    <div class="section-card" id="disputes">
      <div class="section-title">
        <span class="section-num">11</span>
        <span class="s-icon">⚖️</span>
        Disputes Between Users
      </div>
      <div class="prose">
        <p>GigGhana provides a dispute resolution process to help resolve disagreements between Clients and Providers fairly and promptly.</p>
      </div>
      <?php
      $disputeSteps = [
        ['cyan','Step 1 — Direct Resolution (Days 1–3)','When a dispute arises, both parties are first encouraged to resolve it directly through the Platform\'s messaging system. Most issues are resolved at this stage.'],
        ['violet','Step 2 — GigGhana Mediation (Days 4–10)','If direct resolution fails, either party may open a formal dispute through the Platform. GigGhana\'s dispute team will review evidence submitted by both parties and issue a binding decision.'],
        ['amber','Step 3 — Legal Escalation','If either party disagrees with GigGhana\'s mediation decision, the matter may be escalated to arbitration or the courts of Ghana. GigGhana\'s decision is not a waiver of either party\'s legal rights.'],
      ];
      foreach($disputeSteps as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
      <div class="highlight-box highlight-coral" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-coral">📌 Dispute Rules</div>
        <div class="prose" style="font-size:13.5px;margin:0;">
          <ul style="padding-left:16px;margin:4px 0;">
            <li>Disputes must be filed within <strong>30 days</strong> of the disputed event</li>
            <li>Both parties must submit evidence within <strong>5 business days</strong> of a dispute being opened</li>
            <li>GigGhana's mediation decisions are final within the Platform and binding on escrow release</li>
            <li>Abuse of the dispute system (frivolous or bad-faith disputes) may result in account suspension</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- ── 12. LIABILITY ── -->
    <div class="section-card" id="liability">
      <div class="section-title">
        <span class="section-num">12</span>
        <span class="s-icon">🛡</span>
        Limitation of Liability
      </div>
      <div class="prose">
        <p>To the fullest extent permitted by Ghanaian law, GigGhana's liability to you for any claim arising from these Terms or use of the Platform is limited as follows:</p>
      </div>
      <div class="highlight-box highlight-coral">
        <div class="h-label h-label-coral">⚠️ Maximum Liability Cap</div>
        <p class="prose" style="font-size:14px;margin:0;">
          GigGhana's total aggregate liability to you for any claim shall not exceed the <strong>total fees paid by you to GigGhana in the 3 months preceding the claim</strong>, or <strong>₵500</strong>, whichever is greater.
        </p>
      </div>
      <div class="prose" style="margin-top:14px;">
        <p><strong>GigGhana is not liable for:</strong></p>
        <ul>
          <li>Indirect, consequential, incidental, or punitive damages</li>
          <li>Loss of profits, revenue, data, or business opportunities</li>
          <li>Actions or omissions of Clients or Providers (GigGhana is a marketplace, not a party to your contract)</li>
          <li>Platform downtime, technical errors, or data loss beyond our reasonable control</li>
          <li>Fraud committed by third parties that we could not reasonably prevent</li>
          <li>Force majeure events including natural disasters, government actions, or internet failures</li>
        </ul>
        <p style="margin-top:12px;"><strong>Indemnification:</strong> You agree to indemnify and hold harmless GigGhana, its officers, directors, employees, and agents from any claims, damages, losses, or legal fees arising from your breach of these Terms, your content, or your use of the Platform.</p>
      </div>
    </div>

    <!-- ── 13. TERMINATION ── -->
    <div class="section-card" id="termination">
      <div class="section-title">
        <span class="section-num">13</span>
        <span class="s-icon">🚪</span>
        Account Termination
      </div>
      <?php
      $termItems = [
        ['green','👤 Termination by You','You may close your account at any time through your account settings or by contacting support@gigghana.com. Outstanding escrow funds must be settled before deletion. Subscription fees for the current period are non-refundable.'],
        ['coral','🏢 Termination by GigGhana','We may suspend or permanently ban your account, with or without notice, if you violate these Terms, engage in fraudulent activity, or if we determine your use poses a risk to other users or the Platform. Serious violations may be reported to Ghanaian law enforcement.'],
        ['amber','📋 Effect of Termination','On termination: your access to the Platform ceases immediately; any pending escrow funds will be resolved per our standard dispute process; your public profile data will be removed within 30 days; data retained for legal compliance periods per our Privacy Policy.'],
        ['violet','↩️ Appeals','If you believe your account was terminated in error, you may appeal within <strong>14 days</strong> by emailing appeals@gigghana.com with full details. GigGhana\'s decision on appeals is final.'],
      ];
      foreach($termItems as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── 14. GOVERNING LAW ── -->
    <div class="section-card" id="law">
      <div class="section-title">
        <span class="section-num">14</span>
        <span class="s-icon">🏛</span>
        Governing Law &amp; Jurisdiction
      </div>
      <div class="prose">
        <p>These Terms are governed by and construed in accordance with the laws of the <strong>Republic of Ghana</strong>, without regard to conflicts of law principles.</p>
        <p>Any legal dispute that cannot be resolved through GigGhana's dispute resolution process shall be submitted to the <strong>courts of Ghana</strong>. You agree to submit to the personal jurisdiction of the Ghanaian courts for this purpose.</p>
        <p>These Terms are written in English. If translated, the English version shall prevail in the event of any conflict.</p>
      </div>
      <div class="highlight-box highlight-green" style="margin-bottom:0;">
        <div class="h-label h-label-green">🇬🇭 Applicable Legislation</div>
        <div style="display:flex;flex-direction:column;gap:6px;margin-top:4px;">
          <?php
          $laws = [
            'Electronic Transactions Act, 2008 (Act 772)',
            'Data Protection Act, 2012 (Act 843)',
            'Payment Systems and Services Act, 2019 (Act 987)',
            'Consumer Protection Act, 2020 (Act 1052)',
            'Contract Act, 1960 (Act 25)',
          ];
          foreach($laws as $law):
          ?>
          <div style="display:flex;gap:8px;font-size:13px;color:var(--tx-2);">
            <span style="color:var(--green);flex-shrink:0;">📜</span>
            <span><?= $law ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── 15. CHANGES ── -->
    <div class="section-card" id="changes">
      <div class="section-title">
        <span class="section-num">15</span>
        <span class="s-icon">📝</span>
        Changes to These Terms
      </div>
      <div class="prose">
        <p>GigGhana reserves the right to modify these Terms at any time. When we make material changes we will:</p>
        <ul style="list-style:none;padding:0;margin:12px 0;display:flex;flex-direction:column;gap:8px;">
          <li style="display:flex;gap:9px;"><span>📧</span><span>Send an email to your registered address at least <strong>30 days before</strong> the changes take effect</span></li>
          <li style="display:flex;gap:9px;"><span>🔔</span><span>Display a prominent banner notification on the Platform</span></li>
          <li style="display:flex;gap:9px;"><span>📅</span><span>Update the "Last Updated" date at the top of this page</span></li>
        </ul>
        <p>Your continued use of the Platform after the effective date constitutes your acceptance of the revised Terms. If you do not agree, you must stop using the Platform and may close your account before the effective date.</p>
      </div>
      <div class="highlight-box highlight-amber" style="margin-bottom:0;">
        <div class="h-label h-label-amber">📜 Version History</div>
        <p class="prose" style="font-size:13px;margin:0;">
          <strong>Version 1.0</strong> — <?= $lastUpdated ?> — Initial Terms published.<br>
          Previous versions available on request: <a href="mailto:legal@gigghana.com">legal@gigghana.com</a>
        </p>
      </div>
    </div>

    <!-- ── 16. CONTACT ── -->
    <div class="section-card" id="contact" style="border-color:var(--cyan-border);">
      <div class="section-title">
        <span class="section-num">16</span>
        <span class="s-icon">📧</span>
        Contact Us
      </div>
      <div class="prose" style="margin-bottom:20px;">
        <p>If you have questions about these Terms, wish to report a violation, or need legal correspondence delivered to GigGhana, please use the contact details below.</p>
        <p style="margin-top:10px;">
          <strong>GigGhana Ltd</strong><br>
          Registered in Ghana 🇬🇭<br>
          Governed by the laws of the Republic of Ghana
        </p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
        <?php
        $contacts = [
          ['📧','General Support','support@gigghana.com','mailto:support@gigghana.com'],
          ['⚖️','Legal & Terms','legal@gigghana.com','mailto:legal@gigghana.com'],
          ['🔒','Privacy Matters','privacy@gigghana.com','mailto:privacy@gigghana.com'],
          ['🔔','Account Appeals','appeals@gigghana.com','mailto:appeals@gigghana.com'],
        ];
        foreach($contacts as [$ico,$label,$val,$href]):
        ?>
        <a href="<?= $href ?>"
           style="display:flex;align-items:center;gap:11px;padding:13px 15px;border-radius:12px;text-decoration:none;background:var(--cyan-dim);border:1px solid var(--cyan-border);transition:all .22s;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.background='var(--cyan-dim)';this.style.transform='translateY(0)'">
          <span style="font-size:20px;"><?= $ico ?></span>
          <div>
            <div style="font-family:var(--fm);font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--tx-3);margin-bottom:2px;"><?= $label ?></div>
            <div style="font-size:13px;color:var(--cyan);font-weight:600;"><?= $val ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="contact-card">
        <h3>Need Legal Assistance?</h3>
        <p>Our legal team responds to all formal correspondence within <strong>5 business days</strong>. For urgent matters related to account security or fraud, contact our 24/7 support team.</p>
        <a href="mailto:legal@gigghana.com" class="contact-btn">✉️ Contact Legal Team</a>
      </div>
    </div>

  </main><!-- /centre -->

  <!-- ════ RIGHT — Summary Rail ════ -->
  <aside>
    <div class="right-rail su su-2">
      <div class="rail-title">Key Points</div>
      <?php
      $rail = [
        ['🤝','GigGhana is a <strong>marketplace</strong>, not a party to Client–Provider contracts.'],
        ['🔒','<strong>Escrow protects both parties</strong> — funds held until job completion.'],
        ['💰','Platform fee is <strong>10% of job value</strong>, deducted from Provider payout.'],
        ['🌱','Providers get <strong>3 free job applications</strong> on the Beginner tier.'],
        ['⏱','Clients have <strong>7 days</strong> to approve work before auto-release.'],
        ['🚫','<strong>No off-platform payments</strong> — voids escrow protection.'],
        ['⚖️','Disputes handled free by GigGhana within <strong>10 business days</strong>.'],
        ['🇬🇭','Governed by the <strong>laws of Ghana</strong>.'],
        ['🔞','Platform is for <strong>users 18+</strong> only.'],
        ['📧','30 days notice before <strong>any fee or Terms changes</strong>.'],
      ];
      foreach($rail as [$ico,$text]):
      ?>
      <div class="rail-item">
        <div class="rail-ico"><?= $ico ?></div>
        <div class="rail-text"><?= $text ?></div>
      </div>
      <?php endforeach; ?>

      <div style="border-top:1px solid var(--bd);padding-top:16px;margin-top:4px;">
        <div class="rail-title">Related Pages</div>
        <a href="<?= APP_URL ?>/privacy.php"
           style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;margin-bottom:5px;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          🔒 Privacy Policy →
        </a>
        <a href="<?= APP_URL ?>/auth/register.php"
           style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;margin-bottom:5px;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          👤 Create Account →
        </a>
        <a href="<?= APP_URL ?>/jobs.php"
           style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          💼 Browse Jobs →
        </a>
      </div>
    </div>
  </aside>

</div><!-- /layout-grid -->

<!-- ════════════════════════════════════════
     FOOTER
════════════════════════════════════════ -->
<footer class="footer relative z-10">
  <div class="footer-inner">
    <div class="flex items-center gap-3">
      <a href="<?= APP_URL ?>/index.php" class="logo" style="gap:8px;">
        <div class="logo-mark" style="width:28px;height:28px;font-size:12px;border-radius:7px;">G</div>
        <span class="logo-text" style="font-size:16px;">Gig<span>Ghana</span></span>
      </a>
      <span class="footer-copy">— Made with ❤️ in Ghana 🇬🇭</span>
    </div>
    <nav class="footer-links">
      <a href="<?= APP_URL ?>/index.php">Home</a>
      <a href="<?= APP_URL ?>/privacy.php">Privacy Policy</a>
      <a href="<?= APP_URL ?>/terms.php" class="active-page">Terms of Service</a>
      <a href="mailto:support@gigghana.com">Support</a>
      <a href="mailto:legal@gigghana.com">Legal</a>
    </nav>
    <span class="footer-copy">© <?= date('Y') ?> GigGhana Ltd. All rights reserved.</span>
  </div>
</footer>

<!-- Back to top -->
<button class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to top">↑</button>

<script>
(function(){
'use strict';

/* ══ THEME SYNC — identical to all GigGhana pages ══ */
function applyTheme(isLight){
  document.body.classList.toggle('lm', isLight);
  const btn = document.getElementById('themeBtn');
  if(btn) btn.textContent = isLight ? '☀️' : '🌙';
}
applyTheme(localStorage.getItem('gg_theme') === 'light');
window.toggleTheme = function(){
  const nowLight = !document.body.classList.contains('lm');
  localStorage.setItem('gg_theme', nowLight ? 'light' : 'dark');
  applyTheme(nowLight);
};
window.addEventListener('storage', function(e){
  if(e.key === 'gg_theme') applyTheme(e.newValue === 'light');
});

/* ══ NAVBAR scroll ══ */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', function(){
  navbar.classList.toggle('on', window.scrollY > 40);
  document.getElementById('backTop').classList.toggle('show', window.scrollY > 500);
  /* Reading progress */
  const doc   = document.documentElement;
  const total = doc.scrollHeight - doc.clientHeight;
  document.getElementById('readBar').style.width = (total > 0 ? (window.scrollY / total) * 100 : 0) + '%';
  highlightTOC();
});

/* ══ TOC active link ══ */
const sections = document.querySelectorAll('.section-card[id]');
const tocLinks = document.querySelectorAll('.toc-link');

function highlightTOC(){
  let current = '';
  sections.forEach(function(s){
    if(window.scrollY + 100 >= s.offsetTop) current = s.id;
  });
  tocLinks.forEach(function(a){
    a.classList.toggle('active', a.getAttribute('href') === '#' + current);
  });
}

window.setActive = function(el){
  tocLinks.forEach(function(a){ a.classList.remove('active'); });
  el.classList.add('active');
};

/* ══ Scroll-reveal for section cards ══ */
var revealObs = new IntersectionObserver(function(entries){
  entries.forEach(function(e){
    if(e.isIntersecting){
      e.target.style.opacity   = '1';
      e.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.06, rootMargin: '0px 0px -30px 0px' });

document.querySelectorAll('.section-card').forEach(function(el){
  el.style.opacity   = '0';
  el.style.transform = 'translateY(18px)';
  el.style.transition = 'opacity .5s ease, transform .5s ease';
  revealObs.observe(el);
});

})();
</script>
</body>
</html><?php
/**
 * GigGhana — terms.php
 * Terms of Service
 * Design system: Volcanic Charcoal × Electric Cyan × Coral
 * Fonts: Plus Jakarta Sans + DM Sans
 * Theme: synced from index.php via localStorage('gg_theme')
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$user        = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
$lastUpdated = 'March 15, 2026';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Terms of Service — GigGhana</title>
<meta name="description" content="GigGhana Terms of Service — the rules, rights, and responsibilities that govern use of Africa's premier freelance marketplace.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
        body:    ['"DM Sans"', 'sans-serif'],
      }
    }
  }
}
</script>

<!-- Flash-free theme sync — reads localStorage('gg_theme') before first paint -->
<script>
(function(){
  if(localStorage.getItem('gg_theme')==='light')
    document.documentElement.classList.add('lm-pre');
})();
</script>

<style>
/* ════════════════════════════════════════════
   DESIGN TOKENS — Dark (exact match to index.php)
════════════════════════════════════════════ */
:root{
  --bg:#0C0E14; --s1:#13161E; --s2:#191D27; --s3:#1F2433;
  --glass:rgba(19,22,30,0.82);
  --cyan:#00D4C8; --cyan-d:#00A89F; --cyan-l:#4DFFE8;
  --cyan-dim:rgba(0,212,200,0.10); --cyan-border:rgba(0,212,200,0.22);
  --coral:#FF6B4A; --coral-d:#E04D2E;
  --coral-dim:rgba(255,107,74,0.10); --coral-border:rgba(255,107,74,0.25);
  --violet:#7C6FF7; --violet-d:#5D52E0;
  --violet-dim:rgba(124,111,247,0.10); --violet-border:rgba(124,111,247,0.22);
  --green:#1FD9A0; --green-d:#13B882; --green-dim:rgba(31,217,160,0.10);
  --amber:#F7B731; --red:#FF4D6A;
  --tx:#F2F4F8; --tx-2:#9BA8BF; --tx-3:#4E5A6E;
  --bd:rgba(255,255,255,0.065); --bd2:rgba(255,255,255,0.12);
  --gC:rgba(0,212,200,0.18); --gO:rgba(255,107,74,0.14);
  --fm:'Plus Jakarta Sans',sans-serif; --fb:'DM Sans',sans-serif;
}

/* ════════════════════════════════════════════
   LIGHT MODE — exact copy of index.php .lm
════════════════════════════════════════════ */
body.lm{
  --bg:#F3F5FA; --s1:#EAEEF7; --s2:#E0E6F2; --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95; --cyan-d:#007870; --cyan-l:#00CFC3;
  --cyan-dim:rgba(0,158,149,0.08); --cyan-border:rgba(0,158,149,0.2);
  --coral:#E8512B; --coral-d:#C43C1C;
  --coral-dim:rgba(232,81,43,0.08); --coral-border:rgba(232,81,43,0.2);
  --violet:#5B4FD9; --violet-d:#4540C0;
  --violet-dim:rgba(91,79,217,0.08); --violet-border:rgba(91,79,217,0.18);
  --green:#0DAF80; --green-d:#088C65; --green-dim:rgba(13,175,128,0.08);
  --amber:#D4980A; --red:#D63251;
  --tx:#0D1220; --tx-2:#344060; --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.09); --bd2:rgba(30,40,80,0.16);
  --gC:rgba(0,158,149,0.14); --gO:rgba(232,81,43,0.12);
}

/* Light mode overrides */
body.lm .navbar       { background:rgba(234,238,247,0.97) !important; border-color:var(--bd); }
body.lm .navbar.on    { box-shadow:0 4px 28px rgba(13,18,32,0.07); }
body.lm .toc-card     { background:rgba(255,255,255,0.85); border-color:var(--bd2); }
body.lm .section-card { background:rgba(255,255,255,0.8);  border-color:var(--bd2); }
body.lm .right-rail   { background:rgba(255,255,255,0.8);  border-color:var(--bd2); }
body.lm .toc-link     { color:var(--tx-2); }
body.lm .toc-link:hover  { color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan-border); }
body.lm .toc-link.active { color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan); }
body.lm .toc-link.active .toc-num { background:var(--cyan); color:#0C0E14; }
body.lm .btn-theme    { border-color:var(--bd2); color:var(--tx-2); }
body.lm .data-row     { border-color:var(--bd2); }
body.lm .data-row:hover { background:var(--cyan-dim); }
body.lm .grid-tex     {
  background-image: linear-gradient(rgba(30,40,80,0.02) 1px,transparent 1px),
                    linear-gradient(90deg,rgba(30,40,80,0.02) 1px,transparent 1px);
}

/* ════ BASE RESET ════ */
*,*::before,*::after{ box-sizing:border-box; margin:0; padding:0; }
html{ scroll-behavior:smooth; }
body{
  background:var(--bg); color:var(--tx); font-family:var(--fb);
  min-height:100svh; overflow-x:hidden; -webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
html.lm-pre body, html.lm-pre body *{ transition:none !important; }
::-webkit-scrollbar{ width:5px; }
::-webkit-scrollbar-track{ background:var(--bg); }
::-webkit-scrollbar-thumb{ background:var(--s3); border-radius:3px; }
::-webkit-scrollbar-thumb:hover{ background:var(--cyan-d); }

/* ── Gradient bar ── */
.grad-bar{
  position:fixed; top:0; left:0; right:0; height:2px; z-index:300;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--green),var(--cyan));
  background-size:300% 100%; animation:gradShift 5s linear infinite;
}
@keyframes gradShift{ 0%{background-position:0% 50%} 100%{background-position:300% 50%} }

/* ── Background ── */
.grid-tex{
  position:fixed; inset:0; pointer-events:none; z-index:0;
  background-image:
    linear-gradient(rgba(255,255,255,0.012) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,0.012) 1px,transparent 1px);
  background-size:52px 52px;
}
.blob{ position:fixed; border-radius:50%; filter:blur(100px); pointer-events:none; z-index:0; }
.blob-1{ width:600px; height:600px; background:radial-gradient(circle,rgba(0,212,200,0.07),transparent 70%); top:-200px; right:-100px; }
.blob-2{ width:400px; height:400px; background:radial-gradient(circle,rgba(0,212,200,0.04),transparent 70%); bottom:-100px; left:-80px; }
.blob-3{ width:250px; height:250px; background:radial-gradient(circle,rgba(124,111,247,0.06),transparent 70%); top:40%; left:5%; }

/* ── Navbar ── */
.navbar{
  position:fixed; top:0; left:0; right:0; z-index:200;
  display:flex; align-items:center; justify-content:space-between;
  padding:0 5%; height:64px;
  background:rgba(12,14,20,0.88); backdrop-filter:blur(24px);
  border-bottom:1px solid var(--bd); transition:all .26s;
}
.navbar.on{ background:rgba(12,14,20,0.97); box-shadow:0 2px 30px rgba(0,0,0,0.5); }
.logo{ display:flex; align-items:center; gap:9px; text-decoration:none; }
.logo-mark{
  width:34px; height:34px; border-radius:9px; flex-shrink:0;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-weight:800; font-size:15px; color:#0C0E14;
  box-shadow:0 3px 12px var(--gC);
}
.logo-text{ font-family:var(--fm); font-size:19px; font-weight:800; color:var(--tx); }
.logo-text span{ color:var(--cyan); }
.nav-right{ display:flex; align-items:center; gap:10px; }
.btn-theme{
  background:transparent; color:var(--tx-2); border:1px solid var(--bd);
  border-radius:10px; padding:7px 11px; cursor:pointer; font-size:14px;
  transition:all .26s; line-height:1; font-family:var(--fb);
}
.btn-theme:hover{ background:rgba(255,255,255,0.07); }
.nav-btn{
  display:inline-flex; align-items:center; gap:6px;
  padding:7px 16px; border-radius:9px; font-size:13px; font-weight:600;
  text-decoration:none; transition:all .22s; font-family:var(--fm);
}
.nav-btn-ghost{ color:var(--tx-2); border:1px solid var(--bd); }
.nav-btn-ghost:hover{ color:var(--tx); border-color:var(--bd2); background:rgba(255,255,255,0.05); }
.nav-btn-cyan{
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  color:#0C0E14; font-weight:700; box-shadow:0 3px 14px var(--gC);
}
.nav-btn-cyan:hover{ transform:translateY(-1px); box-shadow:0 6px 20px var(--gC); }

/* ── Gradient text ── */
.grad-text{
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan),var(--coral));
  background-size:200% auto;
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
  animation:gradShift 4s ease infinite;
}

/* ── Hero badge ── */
.hero-badge{
  display:inline-flex; align-items:center; gap:8px;
  padding:5px 14px; border-radius:50px; margin-bottom:16px;
  font-family:var(--fm); font-weight:800; font-size:10px;
  letter-spacing:1.4px; text-transform:uppercase;
  background:var(--violet-dim); border:1px solid var(--violet-border); color:var(--violet);
}
.pulse-dot{
  width:6px; height:6px; border-radius:50%; background:var(--violet);
  animation:pDot 2s ease infinite; flex-shrink:0;
}
@keyframes pDot{ 0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(124,111,247,.4);} 50%{opacity:.2;box-shadow:0 0 0 6px rgba(124,111,247,0);} }

/* ── TOC sidebar ── */
.toc-card{
  position:sticky; top:84px;
  background:var(--glass); backdrop-filter:blur(14px);
  border:1px solid var(--bd); border-radius:16px;
  padding:20px; overflow-y:auto; max-height:calc(100vh - 110px);
}
.toc-title{
  font-family:var(--fm); font-weight:800; font-size:11px;
  text-transform:uppercase; letter-spacing:1.2px;
  color:var(--tx-3); margin-bottom:14px;
}
.toc-link{
  display:flex; align-items:center; gap:9px;
  padding:7px 10px; border-radius:8px; border-left:2px solid transparent;
  color:var(--tx-3); font-size:13px; font-weight:500;
  text-decoration:none; transition:all .2s; cursor:pointer; line-height:1.3;
}
.toc-link:hover{ color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan-border); }
.toc-link.active{ color:var(--cyan); background:var(--cyan-dim); border-color:var(--cyan); }
.toc-num{
  width:20px; height:20px; border-radius:6px; flex-shrink:0;
  background:var(--s3); display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-weight:800; font-size:9px; color:var(--tx-3);
}
.toc-link.active .toc-num{ background:var(--cyan); color:#0C0E14; }

/* ── SECTION CARDS ── */
.section-card{
  background:var(--glass); backdrop-filter:blur(12px);
  border:1px solid var(--bd); border-radius:18px;
  padding:32px 36px; margin-bottom:20px;
  scroll-margin-top:84px; transition:border-color .25s;
}
.section-card:hover{ border-color:rgba(0,212,200,0.14); }
.section-num{
  display:inline-flex; align-items:center; justify-content:center;
  width:28px; height:28px; border-radius:8px; flex-shrink:0;
  font-family:var(--fm); font-weight:800; font-size:11px; color:#0C0E14;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  box-shadow:0 3px 10px var(--gC); margin-right:10px;
}
.section-title{
  font-family:var(--fm); font-weight:800;
  font-size:clamp(17px,2vw,20px); color:var(--tx);
  letter-spacing:-.3px; margin-bottom:16px;
  display:flex; align-items:center;
}
.section-title .s-icon{ font-size:20px; margin-right:10px; }
.prose{ color:var(--tx-2); font-size:14.5px; line-height:1.85; font-weight:400; }
.prose p{ margin-bottom:13px; }
.prose p:last-child{ margin-bottom:0; }
.prose strong{ color:var(--tx); font-weight:600; }
.prose a{ color:var(--cyan); text-decoration:none; font-weight:500; }
.prose a:hover{ text-decoration:underline; }
.prose ul, .prose ol{ padding-left:18px; margin:10px 0; display:flex; flex-direction:column; gap:7px; }
.prose li{ color:var(--tx-2); font-size:14px; line-height:1.65; }

/* ── Highlight boxes ── */
.highlight-box{
  border-radius:12px; padding:16px 18px; margin:16px 0;
  border-left:3px solid;
}
.highlight-cyan  { background:var(--cyan-dim);  border-color:var(--cyan); }
.highlight-coral { background:var(--coral-dim); border-color:var(--coral); }
.highlight-green { background:var(--green-dim); border-color:var(--green); }
.highlight-violet{ background:var(--violet-dim);border-color:var(--violet); }
.highlight-amber { background:rgba(247,183,49,0.08); border-color:var(--amber); }
.h-label{
  font-family:var(--fm); font-weight:700; font-size:11px;
  text-transform:uppercase; letter-spacing:.8px; margin-bottom:7px;
}
.h-label-cyan  { color:var(--cyan); }
.h-label-coral { color:var(--coral); }
.h-label-green { color:var(--green); }
.h-label-violet{ color:var(--violet); }
.h-label-amber { color:var(--amber); }

/* ── Fees table ── */
.data-row{
  display:grid; gap:12px; padding:12px 14px;
  border-bottom:1px solid var(--bd);
  font-size:13px; transition:background .2s;
}
.data-row:hover{ background:rgba(0,212,200,0.03); }
.data-row.header{
  font-family:var(--fm); font-weight:700; font-size:10.5px;
  text-transform:uppercase; letter-spacing:.8px; color:var(--tx-3);
  border-bottom:2px solid var(--bd2);
  background:rgba(255,255,255,0.02); border-radius:8px 8px 0 0;
}
.data-val{ color:var(--tx-2); }
.data-bold{ color:var(--tx); font-weight:600; }
.data-badge{
  display:inline-flex; align-items:center; gap:5px;
  padding:2px 9px; border-radius:50px; font-size:10px; font-weight:700;
  font-family:var(--fm);
}
.badge-free   { background:var(--green-dim); color:var(--green); border:1px solid rgba(31,217,160,.22); }
.badge-paid   { background:var(--violet-dim); color:var(--violet); border:1px solid var(--violet-border); }
.badge-escrow { background:var(--cyan-dim);  color:var(--cyan);  border:1px solid var(--cyan-border); }

/* ── Tier cards ── */
.tier-grid{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin:16px 0; }
.tier-card{
  background:rgba(255,255,255,0.025); border:1px solid var(--bd);
  border-radius:14px; padding:20px; text-align:center; transition:all .25s;
}
.tier-card:hover{ transform:translateY(-3px); }
.tier-card.featured{ border-color:var(--cyan-border); background:var(--cyan-dim); }
.tier-icon{ font-size:28px; margin-bottom:10px; }
.tier-name{ font-family:var(--fm); font-weight:800; font-size:15px; color:var(--tx); margin-bottom:4px; }
.tier-price{ font-size:12px; color:var(--tx-3); margin-bottom:10px; }
.tier-perks{ font-size:12px; color:var(--tx-2); line-height:1.6; }

/* ── Do / Don't grid ── */
.do-dont{ display:grid; grid-template-columns:1fr 1fr; gap:14px; margin:14px 0; }
.do-card{
  background:var(--green-dim); border:1px solid rgba(31,217,160,.2);
  border-radius:12px; padding:16px;
}
.dont-card{
  background:var(--violet-dim); border:1px solid var(--violet-border);
  border-radius:12px; padding:16px;
}
.dd-title{
  font-family:var(--fm); font-weight:800; font-size:12px;
  text-transform:uppercase; letter-spacing:.8px; margin-bottom:10px;
}
.do-card .dd-title{ color:var(--green); }
.dont-card .dd-title{ color:var(--violet); }
.dd-list{ list-style:none; display:flex; flex-direction:column; gap:7px; }
.dd-list li{ display:flex; gap:8px; font-size:12.5px; color:var(--tx-2); line-height:1.5; }
.dd-ico{ flex-shrink:0; }

/* ── Contact card ── */
.contact-card{
  background:linear-gradient(135deg,var(--cyan-dim),var(--violet-dim));
  border:1px solid var(--cyan-border); border-radius:16px;
  padding:24px 28px; text-align:center;
}
.contact-card h3{ font-family:var(--fm); font-weight:800; font-size:18px; margin-bottom:8px; color:var(--tx); }
.contact-card p{ color:var(--tx-2); font-size:14px; margin-bottom:16px; line-height:1.6; }
.contact-btn{
  display:inline-flex; align-items:center; gap:8px;
  padding:11px 22px; border-radius:11px; text-decoration:none;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  color:#0C0E14; font-family:var(--fm); font-weight:700; font-size:13.5px;
  transition:all .26s; box-shadow:0 4px 16px var(--gC);
}
.contact-btn:hover{ transform:translateY(-2px); box-shadow:0 8px 24px var(--gC); }

/* ── Right rail ── */
.right-rail{
  position:sticky; top:84px;
  background:var(--glass); backdrop-filter:blur(14px);
  border:1px solid var(--bd); border-radius:16px; padding:20px;
}
.rail-title{ font-family:var(--fm); font-weight:800; font-size:11px; text-transform:uppercase; letter-spacing:1.2px; color:var(--tx-3); margin-bottom:14px; }
.rail-item{ display:flex; align-items:flex-start; gap:10px; margin-bottom:12px; }
.rail-ico{ font-size:16px; flex-shrink:0; margin-top:1px; }
.rail-text{ font-size:12.5px; color:var(--tx-2); line-height:1.5; }
.rail-text strong{ color:var(--tx); font-weight:600; }

/* ── Read progress + back to top ── */
.read-bar{
  position:fixed; top:2px; left:0; height:2px; z-index:301;
  background:var(--cyan); width:0%; transition:width .1s linear;
}
.back-top{
  position:fixed; bottom:24px; right:24px; z-index:100;
  width:42px; height:42px; border-radius:12px;
  background:var(--s2); border:1px solid var(--bd);
  display:flex; align-items:center; justify-content:center;
  color:var(--tx-2); font-size:16px; cursor:pointer;
  transition:all .26s; opacity:0; pointer-events:none;
  box-shadow:0 4px 18px rgba(0,0,0,0.3);
}
.back-top.show{ opacity:1; pointer-events:auto; }
.back-top:hover{ background:var(--cyan-dim); color:var(--cyan); border-color:var(--cyan-border); }

/* ── Footer ── */
.footer{ background:var(--s1); border-top:1px solid var(--bd); padding:40px 5%; margin-top:60px; }
.footer-inner{ max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; }
.footer-copy{ font-size:13px; color:var(--tx-3); }
.footer-links{ display:flex; gap:20px; flex-wrap:wrap; }
.footer-links a{ font-size:13px; color:var(--tx-3); text-decoration:none; transition:color .2s; }
.footer-links a:hover{ color:var(--cyan); }
.footer-links a.active-page{ color:var(--cyan); font-weight:600; }

/* ── Slide-up animation ── */
.su{ animation:suA .5s ease both; }
.su-1{ animation-delay:.05s; } .su-2{ animation-delay:.12s; } .su-3{ animation-delay:.19s; }
@keyframes suA{ from{opacity:0;transform:translateY(14px);} to{opacity:1;transform:translateY(0);} }

/* ── Responsive ── */
@media(max-width:1024px){
  .layout-grid{ grid-template-columns:1fr !important; }
  .toc-card, .right-rail{ display:none; }
}
@media(max-width:768px){
  .section-card{ padding:22px 18px; }
  .tier-grid{ grid-template-columns:1fr; }
  .do-dont{ grid-template-columns:1fr; }
  .data-row{ font-size:12px; }
  .navbar{ padding:0 4%; }
}
</style>
</head>

<body class="">
<script>
if(document.documentElement.classList.contains('lm-pre')){
  document.body.classList.add('lm');
  document.documentElement.classList.remove('lm-pre');
}
</script>

<!-- Reading progress bar -->
<div class="read-bar" id="readBar"></div>

<!-- Gradient bar -->
<div class="grad-bar"></div>

<!-- Background layers -->
<div class="grid-tex"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>

<!-- ════════════════════════════════════════
     NAVBAR
════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
  <a href="<?= APP_URL ?>/index.php" class="logo">
    <div class="logo-mark">G</div>
    <span class="logo-text">Gig<span>Ghana</span></span>
  </a>
  <div class="nav-right">
    <button class="btn-theme" id="themeBtn" onclick="toggleTheme()" title="Toggle theme">🌙</button>
    <?php if($user): ?>
      <a href="<?= APP_URL ?>/<?= $user['role'] ?>/dashboard.php" class="nav-btn nav-btn-ghost">Dashboard</a>
    <?php else: ?>
      <a href="<?= APP_URL ?>/auth/login.php" class="nav-btn nav-btn-ghost">Sign In</a>
      <a href="<?= APP_URL ?>/auth/register.php" class="nav-btn nav-btn-cyan">Get Started</a>
    <?php endif; ?>
  </div>
</nav>

<!-- ════════════════════════════════════════
     HERO
════════════════════════════════════════ -->
<section class="relative z-10 pt-24 pb-12 px-5" style="max-width:1200px;margin:0 auto;">
  <div class="su su-1">
    <div class="hero-badge">
      <span class="pulse-dot"></span>
      Legal Agreement
    </div>
    <h1 class="font-heading font-black leading-tight tracking-[-1.5px] mb-4"
        style="font-size:clamp(32px,4vw,52px);color:var(--tx);">
      Terms of <span class="grad-text">Service</span>
    </h1>
    <div class="flex flex-wrap items-center gap-4 text-[13.5px]" style="color:var(--tx-3);">
      <span class="flex items-center gap-1.5">
        <span style="color:var(--coral);">📅</span>
        Last updated: <strong style="color:var(--tx-2);"><?= $lastUpdated ?></strong>
      </span>
      <span class="w-px h-4" style="background:var(--bd);"></span>
      <span class="flex items-center gap-1.5">
        <span style="color:var(--green);">🇬🇭</span>
        Governed by Ghanaian law
      </span>
      <span class="w-px h-4" style="background:var(--bd);"></span>
      <span class="flex items-center gap-1.5">
        <span style="color:var(--amber);">⏱</span>
        ~10 min read
      </span>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════
     MAIN LAYOUT — 3-column
════════════════════════════════════════ -->
<div class="layout-grid relative z-10 px-5 pb-20"
     style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:220px 1fr 220px;gap:28px;align-items:start;">

  <!-- ════ LEFT — Table of Contents ════ -->
  <aside>
    <div class="toc-card su su-2">
      <div class="toc-title">On This Page</div>
      <?php
      $toc = [
        ['1',  'Agreement Overview',    '📋', 'overview'],
        ['2',  'Eligibility',           '🪪', 'eligibility'],
        ['3',  'Account Rules',         '👤', 'accounts'],
        ['4',  'Client Terms',          '🏢', 'clients'],
        ['5',  'Provider Terms',        '💼', 'providers'],
        ['6',  'Subscription Tiers',    '⭐', 'tiers'],
        ['7',  'Escrow & Payments',     '💰', 'payments'],
        ['8',  'Fees & Charges',        '📊', 'fees'],
        ['9',  'Prohibited Conduct',    '🚫', 'prohibited'],
        ['10', 'Intellectual Property', '©',  'ip'],
        ['11', 'Disputes',             '⚖️', 'disputes'],
        ['12', 'Limitation of Liability','🛡','liability'],
        ['13', 'Termination',           '🚪', 'termination'],
        ['14', 'Governing Law',         '🏛', 'law'],
        ['15', 'Changes to Terms',      '📝', 'changes'],
        ['16', 'Contact Us',            '📧', 'contact'],
      ];
      foreach($toc as [$num,$title,$ico,$id]):
      ?>
      <a class="toc-link" href="#<?= $id ?>" onclick="setActive(this)">
        <div class="toc-num"><?= $num ?></div>
        <?= $ico ?> <?= $title ?>
      </a>
      <?php endforeach; ?>
    </div>
  </aside>

  <!-- ════ CENTRE — Content ════ -->
  <main>

    <!-- ── INTRO CARD ── -->
    <div class="section-card su su-2" style="border-color:var(--cyan-border);background:linear-gradient(135deg,rgba(0,212,200,0.06),rgba(124,111,247,0.04));">
      <p class="prose" style="font-size:15px;">
        These Terms of Service ("<strong>Terms</strong>") form a legally binding agreement between
        <strong>GigGhana Ltd</strong> ("<strong>GigGhana</strong>", "<strong>we</strong>", "<strong>us</strong>") and
        <strong>you</strong>, any person or entity accessing or using the GigGhana platform at
        <a href="<?= APP_URL ?>"><?= APP_URL ?></a>. By creating an account or using any GigGhana service you
        confirm you have read, understood, and agreed to these Terms in full.
      </p>
      <div class="highlight-box highlight-coral" style="margin-top:16px;margin-bottom:0;">
        <div class="h-label h-label-coral">⚠️ Important — Please Read Carefully</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          These Terms include provisions limiting our liability and requiring individual arbitration of disputes.
          If you do not agree to these Terms, you must not use the GigGhana platform.
        </p>
      </div>
    </div>

    <!-- ── 1. OVERVIEW ── -->
    <div class="section-card su su-3" id="overview">
      <div class="section-title">
        <span class="section-num">1</span>
        <span class="s-icon">📋</span>
        Agreement Overview
      </div>
      <div class="prose">
        <p>GigGhana operates a two-sided online marketplace that enables <strong>Clients</strong> to post jobs and hire skilled individuals, and <strong>Providers</strong> (freelancers) to offer and deliver services in exchange for payment. GigGhana acts as an intermediary platform and is not a party to any contract formed between Clients and Providers.</p>
        <p>These Terms govern your use of the Platform, including registration, posting jobs or services, making or receiving payments, using the escrow system, and all communications on the Platform.</p>
        <p>By using GigGhana you also agree to our <a href="<?= APP_URL ?>/privacy.php">Privacy Policy</a>, which is incorporated into these Terms by reference.</p>
      </div>
      <div class="highlight-box highlight-amber" style="margin-bottom:0;">
        <div class="h-label h-label-amber">📌 Definitions</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:4px;">
          <?php
          $defs = [
            ['"Client"','A user who posts jobs and hires Providers.'],
            ['"Provider"','A freelancer or skilled tradesperson who offers services.'],
            ['"Job"','A task or project posted by a Client on the Platform.'],
            ['"Proposal"','A bid submitted by a Provider for a posted Job.'],
            ['"Escrow"','Funds held in trust by GigGhana until job completion.'],
            ['"Platform"','The GigGhana website, app, and all associated services.'],
          ];
          foreach($defs as [$term,$def]):
          ?>
          <div style="background:rgba(255,255,255,0.025);border-radius:9px;padding:10px 12px;">
            <div style="font-family:var(--fm);font-weight:700;font-size:11.5px;color:var(--amber);margin-bottom:3px;"><?= $term ?></div>
            <div style="font-size:12px;color:var(--tx-3);line-height:1.5;"><?= $def ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── 2. ELIGIBILITY ── -->
    <div class="section-card" id="eligibility">
      <div class="section-title">
        <span class="section-num">2</span>
        <span class="s-icon">🪪</span>
        Eligibility
      </div>
      <div class="prose">
        <p>To use GigGhana you must meet all of the following requirements at the time of registration and throughout your use of the Platform:</p>
      </div>
      <div class="do-dont" style="margin-top:14px;">
        <div class="do-card">
          <div class="dd-title">✅ You Must Be</div>
          <ul class="dd-list">
            <?php
            $must = [
              'At least <strong>18 years of age</strong>',
              'A <strong>legal resident</strong> of Ghana or an African nation',
              'Capable of forming a binding legal contract',
              'Registered with a <strong>valid email address</strong>',
              'Using the Platform for <strong>lawful purposes only</strong>',
              'An individual acting on your own behalf, or authorised to act on behalf of a company',
            ];
            foreach($must as $m):
            ?><li><span class="dd-ico">✓</span><span><?= $m ?></span></li><?php endforeach; ?>
          </ul>
        </div>
        <div class="dont-card">
          <div class="dd-title">🚫 You Must Not Be</div>
          <ul class="dd-list">
            <?php
            $mustNot = [
              'Under 18 years of age',
              'Previously <strong>banned or suspended</strong> from GigGhana',
              'Acting under a false identity or impersonating another person',
              'Located in a jurisdiction where use is <strong>legally prohibited</strong>',
              'A competitor attempting to access proprietary information',
              'An automated bot or non-human agent (without written consent)',
            ];
            foreach($mustNot as $m):
            ?><li><span class="dd-ico">✕</span><span><?= $m ?></span></li><?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- ── 3. ACCOUNT RULES ── -->
    <div class="section-card" id="accounts">
      <div class="section-title">
        <span class="section-num">3</span>
        <span class="s-icon">👤</span>
        Account Rules &amp; Responsibilities
      </div>
      <div class="prose">
        <p>When you create a GigGhana account, you are responsible for maintaining its security and for all activity that occurs under it.</p>
      </div>
      <?php
      $accountRules = [
        ['coral','🔑 Account Security','You must use a strong password (8+ characters, uppercase, number) and must not share your login credentials with any third party. You are liable for any unauthorised use of your account. Notify us immediately at <a href="mailto:support@gigghana.com">support@gigghana.com</a> if you suspect a breach.'],
        ['cyan','✅ Accurate Information','All information you provide — name, skills, rates, location, Ghana Card number — must be truthful, accurate, and kept up to date. Providing false information may result in immediate account termination.'],
        ['violet','🔗 One Account Per Person','Each person may maintain only one active account per role. Operating multiple accounts to circumvent suspension, manipulate reviews, or gain unfair advantage is strictly prohibited.'],
        ['green','📧 Email Verification','You must verify your email address via the 6-digit OTP sent during registration. Unverified accounts have limited access to Platform features.'],
        ['amber','🪪 Identity Verification','GigGhana may require Ghana Card verification for Providers seeking Verified or Premium status. Providing fraudulent identity documents will result in permanent account termination and may be reported to law enforcement.'],
      ];
      foreach($accountRules as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── 4. CLIENT TERMS ── -->
    <div class="section-card" id="clients">
      <div class="section-title">
        <span class="section-num">4</span>
        <span class="s-icon">🏢</span>
        Client Terms
      </div>
      <div class="prose">
        <p>As a Client on GigGhana, you agree to the following when posting jobs and hiring Providers:</p>
        <ul>
          <li><strong>Accurate Job Descriptions:</strong> Job posts must accurately describe the scope, deliverables, timeline, and budget. Misleading posts will be removed.</li>
          <li><strong>Timely Escrow Funding:</strong> Before a Provider begins work, you must fund the escrow with the agreed amount. Work may not begin until escrow is confirmed.</li>
          <li><strong>Fair Review:</strong> Once a job is completed, you agree to review the work honestly and release escrow funds within <strong>7 days</strong> of delivery, or raise a dispute through the Platform.</li>
          <li><strong>No Off-Platform Payments:</strong> You must not attempt to make payments to Providers outside the Platform to circumvent fees. Doing so violates these Terms and voids escrow protection.</li>
          <li><strong>Respectful Communication:</strong> You must communicate with Providers professionally and in good faith. Harassment, threats, or abusive behaviour will result in account suspension.</li>
          <li><strong>Job Cancellation:</strong> If you cancel a funded job before work begins, escrow funds will be refunded minus any processing fees. Cancellations after work has begun require dispute resolution.</li>
        </ul>
      </div>
    </div>

    <!-- ── 5. PROVIDER TERMS ── -->
    <div class="section-card" id="providers">
      <div class="section-title">
        <span class="section-num">5</span>
        <span class="s-icon">💼</span>
        Provider Terms
      </div>
      <div class="prose">
        <p>As a Provider on GigGhana, you agree to the following when offering and delivering services:</p>
        <ul>
          <li><strong>Accurate Profiles:</strong> Your skills, experience, rates, and portfolio must be genuine. Falsely representing qualifications you do not have is grounds for termination.</li>
          <li><strong>Delivery Commitment:</strong> Once you accept a job and escrow is funded, you are contractually obligated to deliver the agreed work by the agreed deadline, or communicate delays promptly.</li>
          <li><strong>Quality Standards:</strong> Work must meet the standards agreed with the Client. Repeatedly poor-quality submissions may result in account suspension.</li>
          <li><strong>Free Tier Limits:</strong> Beginner accounts include <strong>3 free job applications</strong>. Additional applications require a Verified (₵49/mo) or Premium (₵99/mo) subscription.</li>
          <li><strong>No Soliciting Off-Platform:</strong> You must not request or accept payments outside GigGhana during or after the job. Violations will result in permanent bans.</li>
          <li><strong>Tax Obligations:</strong> As an independent contractor, you are solely responsible for declaring and paying income tax, VAT, or any other taxes arising from your earnings. GigGhana does not withhold taxes.</li>
          <li><strong>Independent Contractor:</strong> You are an independent contractor, not an employee, agent, or partner of GigGhana. You have no authority to bind GigGhana to any agreement.</li>
        </ul>
      </div>
      <div class="highlight-box highlight-cyan" style="margin-bottom:0;">
        <div class="h-label h-label-cyan">💡 Provider Tip</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          Your rating, completion rate, and response time directly affect your search ranking. Maintaining a rating above 4.5★ and responding to messages within 24 hours significantly increases your visibility to Clients.
        </p>
      </div>
    </div>

    <!-- ── 6. SUBSCRIPTION TIERS ── -->
    <div class="section-card" id="tiers">
      <div class="section-title">
        <span class="section-num">6</span>
        <span class="s-icon">⭐</span>
        Provider Subscription Tiers
      </div>
      <div class="prose">
        <p>GigGhana offers three subscription tiers for Providers. Fees are charged monthly and are non-refundable except as required by Ghanaian consumer law.</p>
      </div>
      <div class="tier-grid">
        <div class="tier-card">
          <div class="tier-icon">🌱</div>
          <div class="tier-name">Beginner</div>
          <div class="tier-price">Free · 3 jobs included</div>
          <div class="tier-perks">Basic profile listing, access to open jobs, standard search placement, community support.</div>
        </div>
        <div class="tier-card featured">
          <div class="tier-icon">✅</div>
          <div class="tier-name">Verified</div>
          <div class="tier-price">₵49/month · Unlimited</div>
          <div class="tier-perks">Verified badge, unlimited job applications, higher search ranking, Ghana Card verification, priority in search results.</div>
        </div>
        <div class="tier-card">
          <div class="tier-icon">⭐</div>
          <div class="tier-name">Premium</div>
          <div class="tier-price">₵99/month · Top placement</div>
          <div class="tier-perks">Featured listing, top search placement, exclusive Premium jobs, dedicated account support, analytics dashboard.</div>
        </div>
      </div>
      <div class="prose" style="margin-top:14px;">
        <p>Subscriptions auto-renew monthly unless cancelled at least <strong>24 hours before</strong> the renewal date. Downgrades take effect at the end of the current billing period. GigGhana reserves the right to adjust pricing with 30 days' notice.</p>
      </div>
    </div>

    <!-- ── 7. ESCROW & PAYMENTS ── -->
    <div class="section-card" id="payments">
      <div class="section-title">
        <span class="section-num">7</span>
        <span class="s-icon">💰</span>
        Escrow System &amp; Payment Processing
      </div>
      <div class="prose">
        <p>GigGhana's escrow system protects both Clients and Providers by holding funds securely until the agreed work is completed and approved.</p>
      </div>
      <?php
      $escrowSteps = [
        ['🏦','1. Client Funds Escrow','Before work begins, the Client deposits the agreed amount into GigGhana escrow via Paystack (card, MTN MoMo, Vodafone Cash, or AirtelTigo). The Provider receives a notification that escrow is funded and may begin work.'],
        ['⚙️','2. Provider Delivers Work','The Provider completes and submits the agreed deliverables through the Platform. The Client receives a notification to review the work.'],
        ['✅','3. Client Approves','The Client has <strong>7 days</strong> to approve the work and release escrow, or raise a dispute. If no action is taken within 7 days, escrow is automatically released to the Provider.'],
        ['💸','4. Provider Receives Payment','Upon release, the net amount (after GigGhana platform fee) is transferred to the Provider linked MoMo number or bank account within <strong>1-3 business days</strong>.'],
      ];
      foreach($escrowSteps as [$ico,$title,$desc]):
      ?>
      <div class="highlight-box highlight-cyan" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-cyan"><?= $ico ?> <?= $title ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
      <div class="highlight-box highlight-coral" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-coral">⚠️ Refund Policy</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          Escrow funds are released to Providers upon Client approval. Refunds to Clients are only issued: (a) before any work has begun, (b) where GigGhana's dispute resolution team rules in favour of the Client, or (c) as required by Ghanaian consumer protection law. Payment processing fees charged by Paystack are non-refundable in all cases.
        </p>
      </div>
    </div>

    <!-- ── 8. FEES ── -->
    <div class="section-card" id="fees">
      <div class="section-title">
        <span class="section-num">8</span>
        <span class="s-icon">📊</span>
        Fees &amp; Charges
      </div>
      <div class="prose">
        <p>GigGhana charges the following fees. All amounts are in <strong>Ghana Cedis (GHS)</strong> and are inclusive of any applicable taxes.</p>
      </div>
      <div style="margin-top:16px;border-radius:12px;overflow:hidden;border:1px solid var(--bd);">
        <div class="data-row header" style="grid-template-columns:1fr 1fr 1.2fr;">
          <span>Fee Type</span>
          <span>Amount</span>
          <span>Who Pays</span>
        </div>
        <?php
        $fees = [
          ['Platform Commission','10% of job value','Deducted from Provider payout','badge-paid'],
          ['Client Job Posting','Free','Client — no charge to post','badge-free'],
          ['Beginner Provider','Free (3 jobs)','Provider — free tier','badge-free'],
          ['Verified Subscription','₵49/month','Provider (monthly)','badge-paid'],
          ['Premium Subscription','₵99/month','Provider (monthly)','badge-paid'],
          ['Escrow Hold','0% — no hold fee','Neither party','badge-free'],
          ['Paystack Processing','~1.5% + ₵1','Absorbed by GigGhana (card)','badge-escrow'],
          ['MoMo Transfer Fee','₵0.50–₵2 per transfer','Provider (on payout)','badge-paid'],
          ['Dispute Filing','Free','Either party','badge-free'],
          ['Account Reinstatement','₵50 (if suspended)','Provider','badge-paid'],
        ];
        foreach($fees as [$type,$amount,$who,$badge]):
        ?>
        <div class="data-row" style="grid-template-columns:1fr 1fr 1.2fr;">
          <span class="data-bold"><?= $type ?></span>
          <span class="data-val"><?= $amount ?></span>
          <span>
            <span class="data-badge <?= $badge ?>"><?= $who ?></span>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="highlight-box highlight-amber" style="margin-top:14px;margin-bottom:0;">
        <div class="h-label h-label-amber">💡 Fee Changes</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          GigGhana reserves the right to revise fee structures at any time, with at least <strong>30 days' prior notice</strong> to affected users via email and Platform notification. Continued use after the effective date constitutes acceptance of the new fees.
        </p>
      </div>
    </div>

    <!-- ── 9. PROHIBITED CONDUCT ── -->
    <div class="section-card" id="prohibited">
      <div class="section-title">
        <span class="section-num">9</span>
        <span class="s-icon">🚫</span>
        Prohibited Conduct
      </div>
      <div class="prose">
        <p>The following activities are strictly prohibited on GigGhana and may result in immediate account suspension, permanent ban, and/or legal action:</p>
      </div>
      <div class="do-dont" style="margin-top:14px;">
        <div class="dont-card" style="grid-column:1/-1;">
          <div class="dd-title">🚫 Strictly Prohibited</div>
          <ul class="dd-list" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <?php
            $prohibited = [
              'Creating fake accounts or impersonating others',
              'Posting fraudulent jobs or fake Proposals',
              'Soliciting or accepting payments outside the Platform',
              'Manipulating reviews, ratings, or feedback',
              'Spamming, phishing, or sending unsolicited messages',
              'Uploading malware, viruses, or malicious code',
              'Violating any Ghanaian or international law',
              'Posting illegal content, hate speech, or adult material',
              'Attempting to hack, scrape, or reverse-engineer the Platform',
              'Money laundering or using the Platform for financial crime',
              'Harassing, threatening, or abusing other users',
              'Using GigGhana to facilitate human trafficking or exploitation',
              'Circumventing bans through new accounts',
              'Sharing another user\'s private information without consent',
            ];
            foreach($prohibited as $p):
            ?><li><span class="dd-ico">✕</span><span><?= $p ?></span></li><?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>

    <!-- ── 10. INTELLECTUAL PROPERTY ── -->
    <div class="section-card" id="ip">
      <div class="section-title">
        <span class="section-num">10</span>
        <span class="s-icon">©</span>
        Intellectual Property
      </div>
      <?php
      $ipItems = [
        ['violet','🏢 GigGhana IP','All Platform content — including the GigGhana name, logo, design, software, algorithms, and text — is owned by GigGhana Ltd and protected by Ghanaian and international intellectual property law. You may not copy, reproduce, distribute, or create derivative works without our written consent.'],
        ['cyan','✍️ Your Content','Content you create and post on the Platform (portfolio items, job descriptions, messages, reviews) remains yours. By posting it, you grant GigGhana a non-exclusive, royalty-free, worldwide licence to display, store, and transmit it solely for the purpose of operating the Platform.'],
        ['green','📦 Deliverables Ownership','Unless otherwise agreed in writing between the Client and Provider, ownership of work deliverables transfers to the Client upon full and final payment of escrow. Providers retain ownership until full payment is received.'],
        ['amber','⚠️ Third-Party IP','You must not post, upload, or deliver content that infringes any third party\'s copyright, trademark, patent, or trade secret. You are solely liable for any IP infringement claims arising from your content.'],
      ];
      foreach($ipItems as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── 11. DISPUTES ── -->
    <div class="section-card" id="disputes">
      <div class="section-title">
        <span class="section-num">11</span>
        <span class="s-icon">⚖️</span>
        Disputes Between Users
      </div>
      <div class="prose">
        <p>GigGhana provides a dispute resolution process to help resolve disagreements between Clients and Providers fairly and promptly.</p>
      </div>
      <?php
      $disputeSteps = [
        ['cyan','Step 1 — Direct Resolution (Days 1–3)','When a dispute arises, both parties are first encouraged to resolve it directly through the Platform\'s messaging system. Most issues are resolved at this stage.'],
        ['violet','Step 2 — GigGhana Mediation (Days 4–10)','If direct resolution fails, either party may open a formal dispute through the Platform. GigGhana\'s dispute team will review evidence submitted by both parties and issue a binding decision.'],
        ['amber','Step 3 — Legal Escalation','If either party disagrees with GigGhana\'s mediation decision, the matter may be escalated to arbitration or the courts of Ghana. GigGhana\'s decision is not a waiver of either party\'s legal rights.'],
      ];
      foreach($disputeSteps as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
      <div class="highlight-box highlight-coral" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-coral">📌 Dispute Rules</div>
        <div class="prose" style="font-size:13.5px;margin:0;">
          <ul style="padding-left:16px;margin:4px 0;">
            <li>Disputes must be filed within <strong>30 days</strong> of the disputed event</li>
            <li>Both parties must submit evidence within <strong>5 business days</strong> of a dispute being opened</li>
            <li>GigGhana's mediation decisions are final within the Platform and binding on escrow release</li>
            <li>Abuse of the dispute system (frivolous or bad-faith disputes) may result in account suspension</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- ── 12. LIABILITY ── -->
    <div class="section-card" id="liability">
      <div class="section-title">
        <span class="section-num">12</span>
        <span class="s-icon">🛡</span>
        Limitation of Liability
      </div>
      <div class="prose">
        <p>To the fullest extent permitted by Ghanaian law, GigGhana's liability to you for any claim arising from these Terms or use of the Platform is limited as follows:</p>
      </div>
      <div class="highlight-box highlight-coral">
        <div class="h-label h-label-coral">⚠️ Maximum Liability Cap</div>
        <p class="prose" style="font-size:14px;margin:0;">
          GigGhana's total aggregate liability to you for any claim shall not exceed the <strong>total fees paid by you to GigGhana in the 3 months preceding the claim</strong>, or <strong>₵500</strong>, whichever is greater.
        </p>
      </div>
      <div class="prose" style="margin-top:14px;">
        <p><strong>GigGhana is not liable for:</strong></p>
        <ul>
          <li>Indirect, consequential, incidental, or punitive damages</li>
          <li>Loss of profits, revenue, data, or business opportunities</li>
          <li>Actions or omissions of Clients or Providers (GigGhana is a marketplace, not a party to your contract)</li>
          <li>Platform downtime, technical errors, or data loss beyond our reasonable control</li>
          <li>Fraud committed by third parties that we could not reasonably prevent</li>
          <li>Force majeure events including natural disasters, government actions, or internet failures</li>
        </ul>
        <p style="margin-top:12px;"><strong>Indemnification:</strong> You agree to indemnify and hold harmless GigGhana, its officers, directors, employees, and agents from any claims, damages, losses, or legal fees arising from your breach of these Terms, your content, or your use of the Platform.</p>
      </div>
    </div>

    <!-- ── 13. TERMINATION ── -->
    <div class="section-card" id="termination">
      <div class="section-title">
        <span class="section-num">13</span>
        <span class="s-icon">🚪</span>
        Account Termination
      </div>
      <?php
      $termItems = [
        ['green','👤 Termination by You','You may close your account at any time through your account settings or by contacting support@gigghana.com. Outstanding escrow funds must be settled before deletion. Subscription fees for the current period are non-refundable.'],
        ['coral','🏢 Termination by GigGhana','We may suspend or permanently ban your account, with or without notice, if you violate these Terms, engage in fraudulent activity, or if we determine your use poses a risk to other users or the Platform. Serious violations may be reported to Ghanaian law enforcement.'],
        ['amber','📋 Effect of Termination','On termination: your access to the Platform ceases immediately; any pending escrow funds will be resolved per our standard dispute process; your public profile data will be removed within 30 days; data retained for legal compliance periods per our Privacy Policy.'],
        ['violet','↩️ Appeals','If you believe your account was terminated in error, you may appeal within <strong>14 days</strong> by emailing appeals@gigghana.com with full details. GigGhana\'s decision on appeals is final.'],
      ];
      foreach($termItems as [$c,$t,$d]):
      ?>
      <div class="highlight-box highlight-<?= $c ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $c ?>"><?= $t ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $d ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── 14. GOVERNING LAW ── -->
    <div class="section-card" id="law">
      <div class="section-title">
        <span class="section-num">14</span>
        <span class="s-icon">🏛</span>
        Governing Law &amp; Jurisdiction
      </div>
      <div class="prose">
        <p>These Terms are governed by and construed in accordance with the laws of the <strong>Republic of Ghana</strong>, without regard to conflicts of law principles.</p>
        <p>Any legal dispute that cannot be resolved through GigGhana's dispute resolution process shall be submitted to the <strong>courts of Ghana</strong>. You agree to submit to the personal jurisdiction of the Ghanaian courts for this purpose.</p>
        <p>These Terms are written in English. If translated, the English version shall prevail in the event of any conflict.</p>
      </div>
      <div class="highlight-box highlight-green" style="margin-bottom:0;">
        <div class="h-label h-label-green">🇬🇭 Applicable Legislation</div>
        <div style="display:flex;flex-direction:column;gap:6px;margin-top:4px;">
          <?php
          $laws = [
            'Electronic Transactions Act, 2008 (Act 772)',
            'Data Protection Act, 2012 (Act 843)',
            'Payment Systems and Services Act, 2019 (Act 987)',
            'Consumer Protection Act, 2020 (Act 1052)',
            'Contract Act, 1960 (Act 25)',
          ];
          foreach($laws as $law):
          ?>
          <div style="display:flex;gap:8px;font-size:13px;color:var(--tx-2);">
            <span style="color:var(--green);flex-shrink:0;">📜</span>
            <span><?= $law ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── 15. CHANGES ── -->
    <div class="section-card" id="changes">
      <div class="section-title">
        <span class="section-num">15</span>
        <span class="s-icon">📝</span>
        Changes to These Terms
      </div>
      <div class="prose">
        <p>GigGhana reserves the right to modify these Terms at any time. When we make material changes we will:</p>
        <ul style="list-style:none;padding:0;margin:12px 0;display:flex;flex-direction:column;gap:8px;">
          <li style="display:flex;gap:9px;"><span>📧</span><span>Send an email to your registered address at least <strong>30 days before</strong> the changes take effect</span></li>
          <li style="display:flex;gap:9px;"><span>🔔</span><span>Display a prominent banner notification on the Platform</span></li>
          <li style="display:flex;gap:9px;"><span>📅</span><span>Update the "Last Updated" date at the top of this page</span></li>
        </ul>
        <p>Your continued use of the Platform after the effective date constitutes your acceptance of the revised Terms. If you do not agree, you must stop using the Platform and may close your account before the effective date.</p>
      </div>
      <div class="highlight-box highlight-amber" style="margin-bottom:0;">
        <div class="h-label h-label-amber">📜 Version History</div>
        <p class="prose" style="font-size:13px;margin:0;">
          <strong>Version 1.0</strong> — <?= $lastUpdated ?> — Initial Terms published.<br>
          Previous versions available on request: <a href="mailto:legal@gigghana.com">legal@gigghana.com</a>
        </p>
      </div>
    </div>

    <!-- ── 16. CONTACT ── -->
    <div class="section-card" id="contact" style="border-color:var(--cyan-border);">
      <div class="section-title">
        <span class="section-num">16</span>
        <span class="s-icon">📧</span>
        Contact Us
      </div>
      <div class="prose" style="margin-bottom:20px;">
        <p>If you have questions about these Terms, wish to report a violation, or need legal correspondence delivered to GigGhana, please use the contact details below.</p>
        <p style="margin-top:10px;">
          <strong>GigGhana Ltd</strong><br>
          Registered in Ghana 🇬🇭<br>
          Governed by the laws of the Republic of Ghana
        </p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
        <?php
        $contacts = [
          ['📧','General Support','support@gigghana.com','mailto:support@gigghana.com'],
          ['⚖️','Legal & Terms','legal@gigghana.com','mailto:legal@gigghana.com'],
          ['🔒','Privacy Matters','privacy@gigghana.com','mailto:privacy@gigghana.com'],
          ['🔔','Account Appeals','appeals@gigghana.com','mailto:appeals@gigghana.com'],
        ];
        foreach($contacts as [$ico,$label,$val,$href]):
        ?>
        <a href="<?= $href ?>"
           style="display:flex;align-items:center;gap:11px;padding:13px 15px;border-radius:12px;text-decoration:none;background:var(--cyan-dim);border:1px solid var(--cyan-border);transition:all .22s;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.background='var(--cyan-dim)';this.style.transform='translateY(0)'">
          <span style="font-size:20px;"><?= $ico ?></span>
          <div>
            <div style="font-family:var(--fm);font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--tx-3);margin-bottom:2px;"><?= $label ?></div>
            <div style="font-size:13px;color:var(--cyan);font-weight:600;"><?= $val ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="contact-card">
        <h3>Need Legal Assistance?</h3>
        <p>Our legal team responds to all formal correspondence within <strong>5 business days</strong>. For urgent matters related to account security or fraud, contact our 24/7 support team.</p>
        <a href="mailto:legal@gigghana.com" class="contact-btn">✉️ Contact Legal Team</a>
      </div>
    </div>

  </main><!-- /centre -->

  <!-- ════ RIGHT — Summary Rail ════ -->
  <aside>
    <div class="right-rail su su-2">
      <div class="rail-title">Key Points</div>
      <?php
      $rail = [
        ['🤝','GigGhana is a <strong>marketplace</strong>, not a party to Client–Provider contracts.'],
        ['🔒','<strong>Escrow protects both parties</strong> — funds held until job completion.'],
        ['💰','Platform fee is <strong>10% of job value</strong>, deducted from Provider payout.'],
        ['🌱','Providers get <strong>3 free job applications</strong> on the Beginner tier.'],
        ['⏱','Clients have <strong>7 days</strong> to approve work before auto-release.'],
        ['🚫','<strong>No off-platform payments</strong> — voids escrow protection.'],
        ['⚖️','Disputes handled free by GigGhana within <strong>10 business days</strong>.'],
        ['🇬🇭','Governed by the <strong>laws of Ghana</strong>.'],
        ['🔞','Platform is for <strong>users 18+</strong> only.'],
        ['📧','30 days notice before <strong>any fee or Terms changes</strong>.'],
      ];
      foreach($rail as [$ico,$text]):
      ?>
      <div class="rail-item">
        <div class="rail-ico"><?= $ico ?></div>
        <div class="rail-text"><?= $text ?></div>
      </div>
      <?php endforeach; ?>

      <div style="border-top:1px solid var(--bd);padding-top:16px;margin-top:4px;">
        <div class="rail-title">Related Pages</div>
        <a href="<?= APP_URL ?>/privacy.php"
           style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;margin-bottom:5px;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          🔒 Privacy Policy →
        </a>
        <a href="<?= APP_URL ?>/auth/register.php"
           style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;margin-bottom:5px;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          👤 Create Account →
        </a>
        <a href="<?= APP_URL ?>/jobs.php"
           style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          💼 Browse Jobs →
        </a>
      </div>
    </div>
  </aside>

</div><!-- /layout-grid -->

<!-- ════════════════════════════════════════
     FOOTER
════════════════════════════════════════ -->
<footer class="footer relative z-10">
  <div class="footer-inner">
    <div class="flex items-center gap-3">
      <a href="<?= APP_URL ?>/index.php" class="logo" style="gap:8px;">
        <div class="logo-mark" style="width:28px;height:28px;font-size:12px;border-radius:7px;">G</div>
        <span class="logo-text" style="font-size:16px;">Gig<span>Ghana</span></span>
      </a>
      <span class="footer-copy">— Made with ❤️ in Ghana 🇬🇭</span>
    </div>
    <nav class="footer-links">
      <a href="<?= APP_URL ?>/index.php">Home</a>
      <a href="<?= APP_URL ?>/privacy.php">Privacy Policy</a>
      <a href="<?= APP_URL ?>/terms.php" class="active-page">Terms of Service</a>
      <a href="mailto:support@gigghana.com">Support</a>
      <a href="mailto:legal@gigghana.com">Legal</a>
    </nav>
    <span class="footer-copy">© <?= date('Y') ?> GigGhana Ltd. All rights reserved.</span>
  </div>
</footer>

<!-- Back to top -->
<button class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to top">↑</button>

<script>
(function(){
'use strict';

/* ══ THEME SYNC — identical to all GigGhana pages ══ */
function applyTheme(isLight){
  document.body.classList.toggle('lm', isLight);
  const btn = document.getElementById('themeBtn');
  if(btn) btn.textContent = isLight ? '☀️' : '🌙';
}
applyTheme(localStorage.getItem('gg_theme') === 'light');
window.toggleTheme = function(){
  const nowLight = !document.body.classList.contains('lm');
  localStorage.setItem('gg_theme', nowLight ? 'light' : 'dark');
  applyTheme(nowLight);
};
window.addEventListener('storage', function(e){
  if(e.key === 'gg_theme') applyTheme(e.newValue === 'light');
});

/* ══ NAVBAR scroll ══ */
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', function(){
  navbar.classList.toggle('on', window.scrollY > 40);
  document.getElementById('backTop').classList.toggle('show', window.scrollY > 500);
  /* Reading progress */
  const doc   = document.documentElement;
  const total = doc.scrollHeight - doc.clientHeight;
  document.getElementById('readBar').style.width = (total > 0 ? (window.scrollY / total) * 100 : 0) + '%';
  highlightTOC();
});

/* ══ TOC active link ══ */
const sections = document.querySelectorAll('.section-card[id]');
const tocLinks = document.querySelectorAll('.toc-link');

function highlightTOC(){
  let current = '';
  sections.forEach(function(s){
    if(window.scrollY + 100 >= s.offsetTop) current = s.id;
  });
  tocLinks.forEach(function(a){
    a.classList.toggle('active', a.getAttribute('href') === '#' + current);
  });
}

window.setActive = function(el){
  tocLinks.forEach(function(a){ a.classList.remove('active'); });
  el.classList.add('active');
};

/* ══ Scroll-reveal for section cards ══ */
var revealObs = new IntersectionObserver(function(entries){
  entries.forEach(function(e){
    if(e.isIntersecting){
      e.target.style.opacity   = '1';
      e.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.06, rootMargin: '0px 0px -30px 0px' });

document.querySelectorAll('.section-card').forEach(function(el){
  el.style.opacity   = '0';
  el.style.transform = 'translateY(18px)';
  el.style.transition = 'opacity .5s ease, transform .5s ease';
  revealObs.observe(el);
});

})();
</script>
</body>
</html>