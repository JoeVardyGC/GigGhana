<?php
/**
 * GigGhana — privacy.php
 * Design system: Volcanic Charcoal × Electric Cyan × Coral
 * Fonts: Plus Jakarta Sans + DM Sans
 * Theme: synced from index.php via localStorage('gg_theme')
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$user = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
$lastUpdated = 'March 15, 2026';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Privacy Policy — GigGhana</title>
<meta name="description" content="GigGhana's Privacy Policy — how we collect, use, and protect your personal information on Africa's premier freelance marketplace.">
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

<!-- Flash-free theme sync -->
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

/* Light mode component overrides */
body.lm .navbar        { background:rgba(234,238,247,0.97) !important; border-color:var(--bd); }
body.lm .navbar.on     { box-shadow:0 4px 28px rgba(13,18,32,0.07); }
body.lm .toc-card      { background:rgba(255,255,255,0.85); border-color:var(--bd2); }
body.lm .section-card  { background:rgba(255,255,255,0.8); border-color:var(--bd2); }
body.lm .highlight-box { background:rgba(255,255,255,0.7); }
body.lm .right-rail    { background:rgba(255,255,255,0.8); border-color:var(--bd2); }
body.lm .toc-link      { color:var(--tx-2); }
body.lm .toc-link:hover{ color:var(--cyan); }
body.lm .toc-link.active{ color:var(--cyan); border-color:var(--cyan); }
body.lm .grid-tex      {
  background-image: linear-gradient(rgba(30,40,80,0.02) 1px,transparent 1px),
                    linear-gradient(90deg,rgba(30,40,80,0.02) 1px,transparent 1px);
}
body.lm .btn-theme     { border-color:var(--bd2); color:var(--tx-2); }
body.lm .data-row      { border-color:var(--bd2); }
body.lm .data-row:hover{ background:var(--cyan-dim); }

/* ════ BASE RESET ════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);font-family:var(--fb);
  min-height:100svh;overflow-x:hidden;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
html.lm-pre body,html.lm-pre body *{transition:none !important;}
::-webkit-scrollbar{width:5px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:3px;}
::-webkit-scrollbar-thumb:hover{background:var(--cyan-d);}

/* ── Gradient bar ── */
.grad-bar{
  position:fixed;top:0;left:0;right:0;height:2px;z-index:300;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--green),var(--cyan));
  background-size:300% 100%;animation:gradShift 5s linear infinite;
}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:300% 50%}}

/* ── Background ── */
.grid-tex{
  position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:
    linear-gradient(rgba(255,255,255,0.012) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,0.012) 1px,transparent 1px);
  background-size:52px 52px;
}
.blob{position:fixed;border-radius:50%;filter:blur(100px);pointer-events:none;z-index:0;}
.blob-1{width:600px;height:600px;background:radial-gradient(circle,rgba(0,212,200,0.05),transparent 70%);top:-200px;right:-100px;}
.blob-2{width:400px;height:400px;background:radial-gradient(circle,rgba(255,107,74,0.04),transparent 70%);bottom:-100px;left:-80px;}

/* ── Navbar ── */
.navbar{
  position:fixed;top:0;left:0;right:0;z-index:200;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 5%;height:64px;
  background:rgba(12,14,20,0.88);backdrop-filter:blur(24px);
  border-bottom:1px solid var(--bd);transition:all .26s;
}
.navbar.on{background:rgba(12,14,20,0.97);box-shadow:0 2px 30px rgba(0,0,0,0.5);}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none;}
.logo-mark{
  width:34px;height:34px;border-radius:9px;flex-shrink:0;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:15px;color:#0C0E14;
  box-shadow:0 3px 12px var(--gC);
}
.logo-text{font-family:var(--fm);font-size:19px;font-weight:800;color:var(--tx);}
.logo-text span{color:var(--cyan);}
.nav-right{display:flex;align-items:center;gap:10px;}
.btn-theme{
  background:transparent;color:var(--tx-2);border:1px solid var(--bd);
  border-radius:10px;padding:7px 11px;cursor:pointer;font-size:14px;
  transition:all .26s;line-height:1;font-family:var(--fb);
}
.btn-theme:hover{background:rgba(255,255,255,0.07);}
.nav-btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:7px 16px;border-radius:9px;font-size:13px;font-weight:600;
  text-decoration:none;transition:all .22s;font-family:var(--fm);
}
.nav-btn-ghost{color:var(--tx-2);border:1px solid var(--bd);}
.nav-btn-ghost:hover{color:var(--tx);border-color:var(--bd2);background:rgba(255,255,255,0.05);}
.nav-btn-cyan{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;font-weight:700;box-shadow:0 3px 14px var(--gC);}
.nav-btn-cyan:hover{transform:translateY(-1px);box-shadow:0 6px 20px var(--gC);}

/* ── Gradient text ── */
.grad-text{
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan),var(--coral));
  background-size:200% auto;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:gradShift 4s ease infinite;
}

/* ── HERO ── */
.hero-badge{
  display:inline-flex;align-items:center;gap:8px;
  padding:5px 14px;border-radius:50px;margin-bottom:16px;
  font-family:var(--fm);font-weight:800;font-size:10px;
  letter-spacing:1.4px;text-transform:uppercase;
  background:var(--violet-dim);border:1px solid var(--violet-border);color:var(--violet);
}
.pulse-dot{
  width:6px;height:6px;border-radius:50%;background:var(--violet);
  animation:pDot 2s ease infinite;flex-shrink:0;
}
@keyframes pDot{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(124,111,247,.4);}50%{opacity:.2;box-shadow:0 0 0 6px rgba(124,111,247,0);}}

/* ── TOC sidebar ── */
.toc-card{
  position:sticky;top:84px;
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:16px;
  padding:20px;overflow-y:auto;max-height:calc(100vh - 110px);
}
.toc-title{
  font-family:var(--fm);font-weight:800;font-size:11px;
  text-transform:uppercase;letter-spacing:1.2px;
  color:var(--tx-3);margin-bottom:14px;
}
.toc-link{
  display:flex;align-items:center;gap:9px;
  padding:7px 10px;border-radius:8px;border-left:2px solid transparent;
  color:var(--tx-3);font-size:13px;font-weight:500;
  text-decoration:none;transition:all .2s;cursor:pointer;
  line-height:1.3;
}
.toc-link:hover{color:var(--cyan);background:var(--cyan-dim);border-color:var(--cyan-border);}
.toc-link.active{color:var(--cyan);background:var(--cyan-dim);border-color:var(--cyan);}
.toc-num{
  width:20px;height:20px;border-radius:6px;flex-shrink:0;
  background:var(--s3);display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:9px;color:var(--tx-3);
}
.toc-link.active .toc-num{background:var(--cyan);color:#0C0E14;}

/* ── SECTION CARDS ── */
.section-card{
  background:var(--glass);backdrop-filter:blur(12px);
  border:1px solid var(--bd);border-radius:18px;
  padding:32px 36px;margin-bottom:20px;
  scroll-margin-top:84px;transition:border-color .25s;
}
.section-card:hover{border-color:rgba(0,212,200,0.12);}
.section-num{
  display:inline-flex;align-items:center;justify-content:center;
  width:28px;height:28px;border-radius:8px;flex-shrink:0;
  font-family:var(--fm);font-weight:800;font-size:11px;color:#0C0E14;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  box-shadow:0 3px 10px var(--gC);margin-right:10px;
}
.section-title{
  font-family:var(--fm);font-weight:800;
  font-size:clamp(17px,2vw,20px);color:var(--tx);
  letter-spacing:-.3px;margin-bottom:16px;
  display:flex;align-items:center;
}
.section-title .s-icon{font-size:20px;margin-right:10px;}
.prose{color:var(--tx-2);font-size:14.5px;line-height:1.85;font-weight:400;}
.prose p{margin-bottom:13px;}
.prose p:last-child{margin-bottom:0;}
.prose strong{color:var(--tx);font-weight:600;}
.prose a{color:var(--cyan);text-decoration:none;font-weight:500;}
.prose a:hover{text-decoration:underline;}

/* ── Highlight box ── */
.highlight-box{
  border-radius:12px;padding:16px 18px;margin:16px 0;
  border-left:3px solid;
}
.highlight-cyan{background:var(--cyan-dim);border-color:var(--cyan);}
.highlight-coral{background:var(--coral-dim);border-color:var(--coral);}
.highlight-green{background:var(--green-dim);border-color:var(--green);}
.highlight-violet{background:var(--violet-dim);border-color:var(--violet);}
.highlight-amber{background:rgba(247,183,49,0.08);border-color:var(--amber);}
.h-label{
  font-family:var(--fm);font-weight:700;font-size:11px;
  text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;
}
.h-label-cyan{color:var(--cyan);}
.h-label-coral{color:var(--coral);}
.h-label-green{color:var(--green);}
.h-label-violet{color:var(--violet);}
.h-label-amber{color:var(--amber);}

/* ── Data table ── */
.data-table{width:100%;border-collapse:collapse;margin:14px 0;}
.data-row{
  display:grid;grid-template-columns:1fr 1.4fr 1fr;
  gap:12px;padding:12px 14px;
  border-bottom:1px solid var(--bd);
  font-size:13px;transition:background .2s;
}
.data-row:hover{background:rgba(0,212,200,0.04);}
.data-row.header{
  font-family:var(--fm);font-weight:700;font-size:10.5px;
  text-transform:uppercase;letter-spacing:.8px;color:var(--tx-3);
  border-bottom:2px solid var(--bd2);
  background:rgba(255,255,255,0.02);border-radius:8px 8px 0 0;
}
.data-val{color:var(--tx-2);}
.data-bold{color:var(--tx);font-weight:600;}
.data-badge{
  display:inline-flex;align-items:center;gap:5px;
  padding:2px 9px;border-radius:50px;font-size:10px;font-weight:700;
  font-family:var(--fm);
}
.badge-yes{background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,.22);}
.badge-no{background:rgba(255,77,106,0.08);color:var(--red);border:1px solid rgba(255,77,106,.2);}
.badge-cond{background:var(--amber-dim,rgba(247,183,49,.1));color:var(--amber);border:1px solid rgba(247,183,49,.25);}

/* ── Rights grid ── */
.rights-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin:14px 0;}
.right-card{
  background:rgba(255,255,255,0.028);border:1px solid var(--bd);
  border-radius:12px;padding:14px;transition:all .2s;
}
.right-card:hover{border-color:var(--cyan-border);transform:translateY(-2px);}
.right-ico{font-size:20px;margin-bottom:8px;}
.right-title{font-family:var(--fm);font-weight:700;font-size:13px;color:var(--tx);margin-bottom:4px;}
.right-desc{font-size:12px;color:var(--tx-3);line-height:1.5;}

/* ── Contact card ── */
.contact-card{
  background:linear-gradient(135deg,var(--cyan-dim),var(--violet-dim));
  border:1px solid var(--cyan-border);border-radius:16px;
  padding:24px 28px;text-align:center;
}
.contact-card h3{font-family:var(--fm);font-weight:800;font-size:18px;margin-bottom:8px;color:var(--tx);}
.contact-card p{color:var(--tx-2);font-size:14px;margin-bottom:16px;line-height:1.6;}
.contact-btn{
  display:inline-flex;align-items:center;gap:8px;
  padding:11px 22px;border-radius:11px;text-decoration:none;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  color:#0C0E14;font-family:var(--fm);font-weight:700;font-size:13.5px;
  transition:all .26s;box-shadow:0 4px 16px var(--gC);
}
.contact-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--gC);}

/* ── Right rail summary ── */
.right-rail{
  position:sticky;top:84px;
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:16px;padding:20px;
}
.rail-title{font-family:var(--fm);font-weight:800;font-size:11px;text-transform:uppercase;letter-spacing:1.2px;color:var(--tx-3);margin-bottom:14px;}
.rail-item{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;}
.rail-ico{font-size:16px;flex-shrink:0;margin-top:1px;}
.rail-text{font-size:12.5px;color:var(--tx-2);line-height:1.5;}
.rail-text strong{color:var(--tx);font-weight:600;}

/* ── Progress bar (reading progress) ── */
.read-bar{
  position:fixed;top:2px;left:0;height:2px;z-index:301;
  background:var(--coral);width:0%;transition:width .1s linear;
}

/* ── Back to top ── */
.back-top{
  position:fixed;bottom:24px;right:24px;z-index:100;
  width:42px;height:42px;border-radius:12px;
  background:var(--s2);border:1px solid var(--bd);
  display:flex;align-items:center;justify-content:center;
  color:var(--tx-2);font-size:16px;cursor:pointer;
  transition:all .26s;opacity:0;pointer-events:none;
  box-shadow:0 4px 18px rgba(0,0,0,0.3);
}
.back-top.show{opacity:1;pointer-events:auto;}
.back-top:hover{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}

/* ── Footer ── */
.footer{background:var(--s1);border-top:1px solid var(--bd);padding:40px 5%;margin-top:60px;}
.footer-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
.footer-copy{font-size:13px;color:var(--tx-3);}
.footer-links{display:flex;gap:20px;flex-wrap:wrap;}
.footer-links a{font-size:13px;color:var(--tx-3);text-decoration:none;transition:color .2s;}
.footer-links a:hover{color:var(--cyan);}
.footer-links a.active-page{color:var(--cyan);font-weight:600;}

/* ── Slide-up animations ── */
.su{animation:suA .5s ease both;}
.su-1{animation-delay:.05s;}.su-2{animation-delay:.12s;}.su-3{animation-delay:.19s;}
@keyframes suA{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}

@media(max-width:1024px){
  .layout-grid{grid-template-columns:1fr !important;}
  .toc-card,.right-rail{display:none;}
}
@media(max-width:768px){
  .section-card{padding:22px 18px;}
  .data-row{grid-template-columns:1fr 1fr;font-size:12px;}
  .data-row .data-val:last-child{display:none;}
  .rights-grid{grid-template-columns:1fr;}
  .navbar{padding:0 4%;}
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

<!-- Animated gradient bar -->
<div class="grad-bar"></div>

<!-- Background -->
<div class="grid-tex"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

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
      Legal Document
    </div>
    <h1 class="font-heading font-black leading-tight tracking-[-1.5px] mb-4" style="font-size:clamp(32px,4vw,52px);color:var(--tx);">
      Privacy <span class="grad-text">Policy</span>
    </h1>
    <div class="flex flex-wrap items-center gap-4 text-[13.5px]" style="color:var(--tx-3);">
      <span class="flex items-center gap-1.5">
        <span style="color:var(--cyan);">📅</span>
        Last updated: <strong style="color:var(--tx-2);"><?= $lastUpdated ?></strong>
      </span>
      <span class="w-px h-4" style="background:var(--bd);"></span>
      <span class="flex items-center gap-1.5">
        <span style="color:var(--green);">🇬🇭</span>
        Governed by Ghanaian law
      </span>
      <span class="w-px h-4" style="background:var(--bd);"></span>
      <span class="flex items-center gap-1.5">
        <span style="color:var(--violet);">⏱</span>
        ~8 min read
      </span>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════
     MAIN LAYOUT — 3 column: TOC | Content | Rail
════════════════════════════════════════ -->
<div class="layout-grid relative z-10 px-5 pb-20"
     style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:220px 1fr 220px;gap:28px;align-items:start;">

  <!-- ════ LEFT — Table of Contents ════ -->
  <aside>
    <div class="toc-card su su-2">
      <div class="toc-title">On This Page</div>
      <?php
      $sections = [
        ['1','Overview',          '📋','overview'],
        ['2','Information We Collect', '📦','collect'],
        ['3','How We Use Data',   '⚙️','use'],
        ['4','Data Sharing',      '🤝','sharing'],
        ['5','Cookies & Tracking','🍪','cookies'],
        ['6','Data Security',     '🔒','security'],
        ['7','Your Rights',       '⚖️','rights'],
        ['8','Data Retention',    '🗂','retention'],
        ['9','Children',          '👶','children'],
        ['10','Third Parties',    '🔗','third-parties'],
        ['11','Changes',          '📝','changes'],
        ['12','Contact Us',       '📧','contact'],
      ];
      foreach($sections as [$num,$title,$ico,$id]):
      ?>
      <a class="toc-link" href="#<?= $id ?>" onclick="setActive(this)">
        <div class="toc-num"><?= $num ?></div>
        <?= $ico ?> <?= $title ?>
      </a>
      <?php endforeach; ?>
    </div>
  </aside>

  <!-- ════ CENTRE — Policy Content ════ -->
  <main>

    <!-- ── INTRO CARD ── -->
    <div class="section-card su su-2" style="border-color:var(--cyan-border);background:linear-gradient(135deg,rgba(0,212,200,0.06),rgba(124,111,247,0.04));">
      <p class="prose" style="font-size:15px;">
        GigGhana Ltd ("<strong>GigGhana</strong>", "<strong>we</strong>", "<strong>us</strong>") operates the platform at
        <a href="<?= APP_URL ?>"><?= APP_URL ?></a> (the "<strong>Platform</strong>").
        This Privacy Policy explains what personal data we collect, why we collect it, how we use it,
        and the rights you have over it. By using our Platform you agree to the practices described here.
      </p>
      <div class="highlight-box highlight-cyan" style="margin-top:16px;margin-bottom:0;">
        <div class="h-label h-label-cyan">🔑 Key Principle</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          We collect only what is necessary to connect Ghanaian talent with opportunities, process payments securely, and keep our community safe. We never sell your personal data to third parties for advertising.
        </p>
      </div>
    </div>

    <!-- ── 1. OVERVIEW ── -->
    <div class="section-card su su-3" id="overview">
      <div class="section-title">
        <span class="section-num">1</span>
        <span class="s-icon">📋</span>
        Overview
      </div>
      <div class="prose">
        <p>GigGhana is a freelance marketplace incorporated in Ghana. We connect <strong>clients</strong> (individuals and businesses seeking services) with <strong>providers</strong> (freelancers and skilled tradespeople). This policy covers all data processed through the Platform, our mobile applications, and associated APIs.</p>
        <p>This policy is written in plain language and complies with Ghana's <strong>Data Protection Act, 2012 (Act 843)</strong> and, where applicable, international standards including GDPR principles.</p>
      </div>
      <div class="highlight-box highlight-green" style="margin-bottom:0;">
        <div class="h-label h-label-green">✅ Who This Applies To</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          This policy applies to all visitors, registered clients, registered providers, and anyone who interacts with GigGhana's services, including through our mobile app or third-party integrations.
        </p>
      </div>
    </div>

    <!-- ── 2. INFORMATION WE COLLECT ── -->
    <div class="section-card" id="collect">
      <div class="section-title">
        <span class="section-num">2</span>
        <span class="s-icon">📦</span>
        Information We Collect
      </div>
      <div class="prose">
        <p>We collect information in three ways: <strong>directly from you</strong>, <strong>automatically</strong> when you use the Platform, and <strong>from third parties</strong>.</p>
      </div>

      <div class="highlight-box highlight-cyan" style="margin:16px 0 12px;">
        <div class="h-label h-label-cyan">📝 Information You Provide Directly</div>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:7px;margin-top:4px;">
          <?php
          $direct = [
            ['🪪','Identity Data','Full name, profile photo, Ghana Card number (for verification)'],
            ['✉️','Contact Data','Email address, phone number, physical location (city/region)'],
            ['💼','Professional Data','Skills, work history, portfolio items, hourly rate, availability'],
            ['💳','Payment Data','Mobile Money numbers (MTN, Vodafone, AirtelTigo), bank account details — processed via Paystack, never stored raw on our servers'],
            ['💬','Communications','Messages, proposals, reviews, dispute details, and any content you post on the Platform'],
            ['🔑','Account Security','Password hash (bcrypt), OTP verification codes (temporary, auto-deleted after use)'],
          ];
          foreach($direct as [$ico,$title,$desc]):
          ?>
          <li style="display:flex;gap:10px;font-size:13px;color:var(--tx-2);line-height:1.5;">
            <span style="flex-shrink:0;font-size:16px;"><?= $ico ?></span>
            <span><strong style="color:var(--tx);"><?= $title ?>:</strong> <?= $desc ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="highlight-box highlight-violet" style="margin-bottom:0;">
        <div class="h-label h-label-violet">🤖 Information Collected Automatically</div>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:7px;margin-top:4px;">
          <?php
          $auto = [
            ['🌐','Technical Data','IP address, browser type/version, operating system, device identifiers'],
            ['📊','Usage Data','Pages visited, features used, search queries, click patterns, session duration'],
            ['🍪','Cookie Data','Session cookies, preference cookies, analytics identifiers (see Section 5)'],
            ['📍','Location Data','Approximate location derived from IP; precise location only if you grant permission'],
          ];
          foreach($auto as [$ico,$title,$desc]):
          ?>
          <li style="display:flex;gap:10px;font-size:13px;color:var(--tx-2);line-height:1.5;">
            <span style="flex-shrink:0;font-size:16px;"><?= $ico ?></span>
            <span><strong style="color:var(--tx);"><?= $title ?>:</strong> <?= $desc ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <!-- ── 3. HOW WE USE DATA ── -->
    <div class="section-card" id="use">
      <div class="section-title">
        <span class="section-num">3</span>
        <span class="s-icon">⚙️</span>
        How We Use Your Data
      </div>
      <div class="prose">
        <p>We only process your data when we have a valid legal basis — your <strong>consent</strong>, <strong>contract performance</strong>, a <strong>legal obligation</strong>, or our <strong>legitimate interests</strong> (balanced against your rights).</p>
      </div>

      <!-- Data usage table -->
      <div style="margin-top:16px;border-radius:12px;overflow:hidden;border:1px solid var(--bd);">
        <div class="data-row header">
          <span>Purpose</span>
          <span>Data Used</span>
          <span>Legal Basis</span>
        </div>
        <?php
        $uses = [
          ['Create &amp; manage your account','Name, email, password','Contract'],
          ['Verify your identity (Ghana Card)','ID number, name, photo','Consent + Legal obligation'],
          ['Match clients with providers','Skills, location, ratings','Contract + Legitimate interest'],
          ['Process payments &amp; escrow','Payment details, transaction history','Contract'],
          ['Send OTP verification codes','Phone/email, OTP token','Contract + Security'],
          ['Notify you of activity','Email, phone, preferences','Contract'],
          ['Resolve disputes','Messages, evidence, job data','Legitimate interest'],
          ['Prevent fraud &amp; abuse','IP, device, usage patterns','Legal obligation + Legitimate interest'],
          ['Improve the Platform','Anonymised usage data','Legitimate interest'],
          ['Comply with Ghanaian law','Any required data','Legal obligation'],
        ];
        foreach($uses as [$purpose,$data,$basis]):
        ?>
        <div class="data-row">
          <span class="data-bold"><?= $purpose ?></span>
          <span class="data-val"><?= $data ?></span>
          <span>
            <span class="data-badge <?= str_contains($basis,'Contract') ? 'badge-yes' : (str_contains($basis,'Consent') ? 'badge-cond' : 'badge-no') ?>">
              <?= $basis ?>
            </span>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── 4. DATA SHARING ── -->
    <div class="section-card" id="sharing">
      <div class="section-title">
        <span class="section-num">4</span>
        <span class="s-icon">🤝</span>
        Data Sharing &amp; Disclosure
      </div>
      <div class="prose">
        <p><strong>We do not sell your personal data.</strong> We share your information only in these limited circumstances:</p>
      </div>
      <?php
      $sharing = [
        ['cyan','Other Platform Users','Your public profile (name, skills, rating, portfolio) is visible to other registered users. Private messages are only visible to the conversation participants.'],
        ['violet','Payment Processors','We use <strong>Paystack</strong> to process card and MoMo payments. Your payment details are transmitted directly to Paystack under their PCI-DSS compliant systems. We store only transaction references and status.'],
        ['green','Verification Partners','Ghana Card verification is processed via authorised government API partners. We transmit only the minimum data required for identity confirmation.'],
        ['amber','Service Providers','Trusted sub-processors (email delivery, cloud hosting, analytics) who are contractually bound to protect your data and may only use it to provide services to us.'],
        ['coral','Legal &amp; Regulatory','We may disclose data to Ghanaian courts, law enforcement, or regulatory bodies when legally required, or to protect the rights, safety, or property of GigGhana, our users, or the public.'],
        ['violet','Business Transfer','If GigGhana is acquired or merges with another entity, your data may be transferred as part of that transaction. You will be notified beforehand.'],
      ];
      foreach($sharing as [$color,$title,$desc]):
      ?>
      <div class="highlight-box highlight-<?= $color ?>" style="margin-top:12px;margin-bottom:0;">
        <div class="h-label h-label-<?= $color ?>"><?= $title ?></div>
        <p class="prose" style="font-size:13.5px;margin:0;"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- ── 5. COOKIES ── -->
    <div class="section-card" id="cookies">
      <div class="section-title">
        <span class="section-num">5</span>
        <span class="s-icon">🍪</span>
        Cookies &amp; Tracking Technologies
      </div>
      <div class="prose">
        <p>We use cookies and similar technologies to keep you signed in, remember your preferences, and understand how the Platform is used.</p>
      </div>
      <div style="margin-top:16px;border-radius:12px;overflow:hidden;border:1px solid var(--bd);">
        <div class="data-row header">
          <span>Cookie Type</span>
          <span>Purpose</span>
          <span>Can Opt Out?</span>
        </div>
        <?php
        $cookies = [
          ['Essential / Session','Log-in state, CSRF security tokens, OTP sessions','No — required for Platform to function'],
          ['Preference','Dark/light theme (gg_theme), language, display settings','Yes — clear browser cookies'],
          ['Analytics','Page views, feature usage (anonymised — no ad tracking)','Yes — contact us'],
          ['Remember Me','Secure 30-day login cookie (gg_remember)','Yes — uncheck \'Remember Me\' at login'],
        ];
        foreach($cookies as [$type,$purpose,$opt]):
        ?>
        <div class="data-row">
          <span class="data-bold"><?= $type ?></span>
          <span class="data-val"><?= $purpose ?></span>
          <span>
            <span class="data-badge <?= str_starts_with($opt,'Yes') ? 'badge-yes' : 'badge-no' ?>">
              <?= $opt ?>
            </span>
          </span>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="prose" style="margin-top:12px;font-size:13px;">
        You can control cookies through your browser settings. Note that disabling essential cookies will prevent you from signing in. We do <strong>not</strong> use third-party advertising cookies or sell behavioural data to advertisers.
      </p>
    </div>

    <!-- ── 6. SECURITY ── -->
    <div class="section-card" id="security">
      <div class="section-title">
        <span class="section-num">6</span>
        <span class="s-icon">🔒</span>
        Data Security
      </div>
      <div class="prose">
        <p>Protecting your data is fundamental to GigGhana. We implement industry-standard technical and organisational measures:</p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:14px;">
        <?php
        $measures = [
          ['🔐','Passwords','Bcrypt hashed (cost 12) — never stored in plaintext'],
          ['🔒','Data in Transit','TLS 1.2+ encryption for all data transfers'],
          ['🛡','CSRF Protection','Per-session tokens on all state-changing requests'],
          ['📱','Two-Factor (OTP)','6-digit OTP required for email verification and password reset'],
          ['💳','Payment Security','Paystack PCI-DSS Level 1 — card data never touches our servers'],
          ['🔑','Escrow System','Funds held in trust until job completion confirmed by client'],
          ['🚫','Rate Limiting','Brute-force protection on login, OTP, and resend actions'],
          ['👤','Access Control','Role-based permissions (client, provider, admin) with session management'],
        ];
        foreach($measures as [$ico,$title,$desc]):
        ?>
        <div style="background:rgba(255,255,255,0.025);border:1px solid var(--bd);border-radius:11px;padding:13px;">
          <div style="font-size:18px;margin-bottom:7px;"><?= $ico ?></div>
          <div style="font-family:var(--fm);font-weight:700;font-size:12.5px;color:var(--tx);margin-bottom:3px;"><?= $title ?></div>
          <div style="font-size:11.5px;color:var(--tx-3);line-height:1.5;"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="highlight-box highlight-coral" style="margin-top:16px;margin-bottom:0;">
        <div class="h-label h-label-coral">⚠️ Breach Notification</div>
        <p class="prose" style="font-size:13.5px;margin:0;">
          In the event of a data breach that affects your personal data, we will notify you and the relevant Ghanaian Data Protection Commission within 72 hours of becoming aware, where feasible and legally required.
        </p>
      </div>
    </div>

    <!-- ── 7. YOUR RIGHTS ── -->
    <div class="section-card" id="rights">
      <div class="section-title">
        <span class="section-num">7</span>
        <span class="s-icon">⚖️</span>
        Your Rights
      </div>
      <div class="prose">
        <p>Under Ghana's Data Protection Act 2012 and general privacy principles, you have the following rights. To exercise any of them, contact us at <a href="mailto:privacy@gigghana.com">privacy@gigghana.com</a>.</p>
      </div>
      <div class="rights-grid">
        <?php
        $rights = [
          ['👁','Right to Access','Request a copy of all personal data we hold about you. We will respond within 30 days.'],
          ['✏️','Right to Rectification','Correct inaccurate or incomplete personal data. Most data can be updated directly in your account settings.'],
          ['🗑','Right to Erasure','Request deletion of your account and associated data, subject to legal retention obligations (e.g. transaction records).'],
          ['⛔','Right to Object','Object to processing based on legitimate interests, including profiling for matching purposes.'],
          ['📦','Right to Portability','Receive your data in a structured, machine-readable format (JSON/CSV) to transfer to another service.'],
          ['⏸','Right to Restriction','Ask us to pause processing your data while a dispute or objection is under review.'],
          ['↩️','Right to Withdraw Consent','Where processing is consent-based, you may withdraw at any time without affecting past processing.'],
          ['📣','Right to Complain','Lodge a complaint with the <strong>Data Protection Commission of Ghana</strong> if you believe your rights have been violated.'],
        ];
        foreach($rights as [$ico,$title,$desc]):
        ?>
        <div class="right-card">
          <div class="right-ico"><?= $ico ?></div>
          <div class="right-title"><?= $title ?></div>
          <div class="right-desc"><?= $desc ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── 8. DATA RETENTION ── -->
    <div class="section-card" id="retention">
      <div class="section-title">
        <span class="section-num">8</span>
        <span class="s-icon">🗂</span>
        Data Retention
      </div>
      <div class="prose">
        <p>We retain your data only for as long as necessary for the purposes described in this policy or as required by Ghanaian law.</p>
      </div>
      <div style="margin-top:14px;border-radius:12px;overflow:hidden;border:1px solid var(--bd);">
        <div class="data-row header" style="grid-template-columns:1fr 1fr;">
          <span>Data Type</span>
          <span>Retention Period</span>
        </div>
        <?php
        $retention = [
          ['Active account data','For the lifetime of your account'],
          ['OTP verification codes','Deleted immediately after use or on expiry (<?= OTP_EXPIRY_MINUTES ?> minutes)'],
          ['Password reset tokens','Deleted immediately after use or after 15 minutes'],
          ['Transaction &amp; escrow records','7 years (Ghanaian financial regulations)'],
          ['Messages between users','3 years after the related job is closed'],
          ['Dispute records','5 years from resolution'],
          ['Deleted account data','Anonymised within 30 days of deletion request'],
          ['Server logs &amp; access logs','90 days, then auto-deleted'],
          ['Analytics data (anonymised)','Up to 2 years'],
        ];
        foreach($retention as [$type,$period]):
        ?>
        <div class="data-row" style="grid-template-columns:1fr 1fr;">
          <span class="data-bold"><?= $type ?></span>
          <span class="data-val"><?= $period ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ── 9. CHILDREN ── -->
    <div class="section-card" id="children">
      <div class="section-title">
        <span class="section-num">9</span>
        <span class="s-icon">👶</span>
        Children's Privacy
      </div>
      <div class="highlight-box highlight-coral" style="margin-bottom:0;">
        <div class="h-label h-label-coral">🔞 Age Restriction</div>
        <p class="prose" style="font-size:14px;margin:0;">
          GigGhana is intended for users who are <strong>18 years of age or older</strong>. We do not knowingly collect personal data from children under 18. If you believe a minor has created an account, please contact us immediately at <a href="mailto:support@gigghana.com">support@gigghana.com</a> and we will delete the account promptly. Users must confirm they are 18+ during registration.
        </p>
      </div>
    </div>

    <!-- ── 10. THIRD PARTIES ── -->
    <div class="section-card" id="third-parties">
      <div class="section-title">
        <span class="section-num">10</span>
        <span class="s-icon">🔗</span>
        Third-Party Services &amp; Links
      </div>
      <div class="prose">
        <p>Our Platform integrates with and may link to third-party services. This policy does not cover their practices.</p>
      </div>
      <?php
      $thirdParties = [
        ['Paystack','Payment processing','payments.paystack.com — see Paystack Privacy Policy','green'],
        ['Google OAuth','Optional social login','accounts.google.com — see Google Privacy Policy','coral'],
        ['Facebook OAuth','Optional social login','facebook.com — see Meta Privacy Policy','violet'],
        ['Google Fonts','Typography (Plus Jakarta Sans, DM Sans)','fonts.googleapis.com — fonts loaded on page request','amber'],
      ];
      foreach($thirdParties as [$name,$purpose,$domain,$color]):
      ?>
      <div class="highlight-box highlight-<?= $color ?>" style="margin-top:12px;margin-bottom:0;display:flex;align-items:center;gap:12px;">
        <div style="flex:1;">
          <div class="h-label h-label-<?= $color ?>"><?= $name ?> — <?= $purpose ?></div>
          <p class="prose" style="font-size:12.5px;margin:0;color:var(--tx-3);"><?= $domain ?></p>
        </div>
      </div>
      <?php endforeach; ?>
      <p class="prose" style="margin-top:14px;">
        Links on our Platform to external websites are provided for convenience. GigGhana is not responsible for the privacy practices of those sites. We encourage you to review their privacy policies before providing personal information.
      </p>
    </div>

    <!-- ── 11. CHANGES ── -->
    <div class="section-card" id="changes">
      <div class="section-title">
        <span class="section-num">11</span>
        <span class="s-icon">📝</span>
        Changes to This Policy
      </div>
      <div class="prose">
        <p>We may update this Privacy Policy periodically to reflect changes in our practices, legal requirements, or Platform features. When we make material changes, we will:</p>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;margin:12px 0;">
          <?php
          $changeNotices = [
            '📧 Send an email notification to your registered email address',
            '🔔 Display a prominent notice on the Platform for at least 30 days',
            '📅 Update the "Last Updated" date at the top of this page',
          ];
          foreach($changeNotices as $n):
          ?>
          <li style="display:flex;align-items:flex-start;gap:9px;font-size:14px;color:var(--tx-2);">
            <span><?= $n ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
        <p>Your continued use of the Platform after changes become effective constitutes your acceptance of the revised policy. If you do not agree, you may close your account at any time.</p>
      </div>
      <div class="highlight-box highlight-amber" style="margin-bottom:0;">
        <div class="h-label h-label-amber">📜 Policy History</div>
        <p class="prose" style="font-size:13px;margin:0;">
          <strong>Version 1.0</strong> — <?= $lastUpdated ?> — Initial policy published.<br>
          Previous versions available on request: <a href="mailto:privacy@gigghana.com">privacy@gigghana.com</a>
        </p>
      </div>
    </div>

    <!-- ── 12. CONTACT ── -->
    <div class="section-card" id="contact" style="border-color:var(--cyan-border);">
      <div class="section-title">
        <span class="section-num">12</span>
        <span class="s-icon">📧</span>
        Contact Us &amp; Data Controller
      </div>
      <div class="prose" style="margin-bottom:20px;">
        <p>
          <strong>Data Controller:</strong> GigGhana Ltd<br>
          <strong>Registered in:</strong> Ghana 🇬🇭<br>
          <strong>Jurisdiction:</strong> Governed by the Data Protection Act, 2012 (Act 843) of Ghana
        </p>
        <p style="margin-top:12px;">For all privacy-related requests, questions, or complaints, please contact our Data Protection Officer:</p>
      </div>
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:20px;">
        <?php
        $contacts = [
          ['📧','Privacy Email','privacy@gigghana.com','mailto:privacy@gigghana.com'],
          ['🛟','Support Email','support@gigghana.com','mailto:support@gigghana.com'],
          ['🌐','Website','gigghana.com', APP_URL],
          ['🏛','Data Protection Commission','nca.org.gh','https://nca.org.gh'],
        ];
        foreach($contacts as [$ico,$label,$val,$href]):
        ?>
        <a href="<?= $href ?>" target="<?= str_starts_with($href,'http') ? '_blank' : '_self' ?>"
           style="display:flex;align-items:center;gap:11px;padding:13px 15px;border-radius:12px;text-decoration:none;background:rgba(0,212,200,0.05);border:1px solid var(--cyan-border);transition:all .22s;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.background='rgba(0,212,200,0.05)';this.style.transform='translateY(0)'">
          <span style="font-size:20px;"><?= $ico ?></span>
          <div>
            <div style="font-family:var(--fm);font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.7px;color:var(--tx-3);margin-bottom:2px;"><?= $label ?></div>
            <div style="font-size:13px;color:var(--cyan);font-weight:600;"><?= $val ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
      <div class="contact-card">
        <h3>Have a Privacy Concern?</h3>
        <p>Our Data Protection Officer is committed to resolving your concerns promptly. Most requests are handled within <strong>14 business days</strong>.</p>
        <a href="mailto:privacy@gigghana.com" class="contact-btn">
          ✉️ Email Our Privacy Team
        </a>
      </div>
    </div>

  </main><!-- /centre -->

  <!-- ════ RIGHT — Summary Rail ════ -->
  <aside>
    <div class="right-rail su su-2">
      <div class="rail-title">Quick Summary</div>
      <?php
      $rail = [
        ['✅','We <strong>never sell</strong> your personal data to advertisers or third parties.'],
        ['🔒','Passwords are <strong>bcrypt hashed</strong> — never stored in plaintext.'],
        ['💳','Payment details go directly to <strong>Paystack</strong> — not our servers.'],
        ['🔑','OTP codes expire in <strong><?= OTP_EXPIRY_MINUTES ?> minutes</strong> and are deleted after use.'],
        ['🇬🇭','Governed by <strong>Ghana\'s Data Protection Act 2012</strong>.'],
        ['👤','You can <strong>delete your account</strong> and data at any time.'],
        ['📧','Privacy requests answered within <strong>14 business days</strong>.'],
        ['🔞','Platform is for <strong>users 18+</strong> only.'],
      ];
      foreach($rail as [$ico,$text]):
      ?>
      <div class="rail-item">
        <div class="rail-ico"><?= $ico ?></div>
        <div class="rail-text"><?= $text ?></div>
      </div>
      <?php endforeach; ?>

      <div style="border-top:1px solid var(--bd);padding-top:16px;margin-top:4px;">
        <div class="rail-title">Related Policies</div>
        <a href="<?= APP_URL ?>/terms.php" style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;margin-bottom:5px;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          📜 Terms of Service →
        </a>
        <a href="<?= APP_URL ?>/auth/register.php" style="display:flex;align-items:center;gap:8px;padding:9px 11px;border-radius:9px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:all .2s;"
           onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'"
           onmouseout="this.style.background='transparent';this.style.color='var(--tx-2)'">
          👤 Create Account →
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
      <a href="<?= APP_URL ?>/privacy.php" class="active-page">Privacy Policy</a>
      <a href="<?= APP_URL ?>/terms.php">Terms of Service</a>
      <a href="mailto:support@gigghana.com">Support</a>
      <a href="mailto:privacy@gigghana.com">Privacy Team</a>
    </nav>
    <span class="footer-copy">© <?= date('Y') ?> GigGhana Ltd. All rights reserved.</span>
  </div>
</footer>

<!-- Back to top -->
<button class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to top">↑</button>

<script>
(function(){
'use strict';

/* ══ THEME SYNC — identical to all auth pages ══ */
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
  /* Back to top */
  document.getElementById('backTop').classList.toggle('show', window.scrollY > 500);
  /* Reading progress bar */
  const doc   = document.documentElement;
  const total = doc.scrollHeight - doc.clientHeight;
  const pct   = total > 0 ? (window.scrollY / total) * 100 : 0;
  document.getElementById('readBar').style.width = pct + '%';
  /* Highlight active TOC item */
  highlightTOC();
});

/* ══ TOC active link ══ */
const sections   = document.querySelectorAll('.section-card[id]');
const tocLinks   = document.querySelectorAll('.toc-link');

function highlightTOC(){
  let current = '';
  sections.forEach(s => {
    if(window.scrollY + 100 >= s.offsetTop) current = s.id;
  });
  tocLinks.forEach(a => {
    const href = a.getAttribute('href');
    a.classList.toggle('active', href === '#' + current);
  });
}

window.setActive = function(el){
  tocLinks.forEach(a => a.classList.remove('active'));
  el.classList.add('active');
};

/* ══ Smooth section reveal on scroll ══ */
const revealObs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if(e.isIntersecting){
      e.target.style.opacity   = '1';
      e.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.06, rootMargin: '0px 0px -30px 0px' });

document.querySelectorAll('.section-card').forEach(el => {
  el.style.opacity   = '0';
  el.style.transform = 'translateY(18px)';
  el.style.transition = 'opacity .5s ease, transform .5s ease';
  revealObs.observe(el);
});

})();
</script>
</body>
</html>