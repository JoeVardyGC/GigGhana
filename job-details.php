<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$jobId = (int)($_GET['id'] ?? 0);
if (!$jobId) redirect(APP_URL . '/search/jobs.php');

try {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT j.*, c.name AS cat_name,
         u.first_name, u.last_name, u.avatar, u.location AS client_location,
         u.created_at AS client_since
         FROM jobs j LEFT JOIN categories c ON c.id=j.category_id
         JOIN users u ON u.id=j.client_id WHERE j.id=? LIMIT 1"
    );
    $stmt->execute([$jobId]);
    $job = $stmt->fetch();
    if (!$job) redirect(APP_URL . '/search/jobs.php');

    // Increment views (once per session)
    if (!isset($_SESSION['viewed_job_'.$jobId])) {
        $db->prepare("UPDATE jobs SET views=views+1 WHERE id=?")->execute([$jobId]);
        $_SESSION['viewed_job_'.$jobId] = true;
    }

    // Skills
    $stSkills = $db->prepare(
        "SELECT s.name FROM job_skills js JOIN skills s ON s.id=js.skill_id WHERE js.job_id=?"
    );
    $stSkills->execute([$jobId]);
    $skills = $stSkills->fetchAll(PDO::FETCH_COLUMN);

    // Check if logged-in provider already applied
    $alreadyApplied = false;
    $isSaved = false;
    if (isLoggedIn()) {
        if ($_SESSION['user_role'] === 'provider') {
            $stProv = $db->prepare("SELECT id FROM providers WHERE user_id=?");
            $stProv->execute([$_SESSION['user_id']]);
            $prov = $stProv->fetch();
            if ($prov) {
                $stApply = $db->prepare("SELECT id FROM proposals WHERE job_id=? AND provider_id=?");
                $stApply->execute([$jobId, $prov['id']]);
                $alreadyApplied = (bool)$stApply->fetch();
            }
        }
        $stSave = $db->prepare("SELECT id FROM saved_jobs WHERE job_id=? AND user_id=?");
        $stSave->execute([$jobId, $_SESSION['user_id']]);
        $isSaved = (bool)$stSave->fetch();
    }

    // Related jobs
    $stRel = $db->prepare(
        "SELECT j.id, j.title, j.budget_min, j.budget_max, j.created_at
         FROM jobs j WHERE j.category_id=? AND j.id!=? AND j.status='open'
         ORDER BY j.created_at DESC LIMIT 4"
    );
    $stRel->execute([$job['category_id'], $jobId]);
    $relatedJobs = $stRel->fetchAll();

} catch(Exception $e) {
    error_log($e->getMessage());
    redirect(APP_URL . '/index.php');
}

$csrf = generateCSRF();
$user = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
/* Theme from cookie */
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= sanitize($job['title']) ?> — GigGhana</title>
<meta name="description" content="<?= sanitize(substr($job['description'],0,160)) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════════════════
   DESIGN TOKENS — Volcanic Charcoal × Electric Cyan × Coral
══════════════════════════════════════════════════════ */
:root{
  --bg:#0C0E14; --s1:#13161E; --s2:#191D27; --s3:#1F2433;
  --glass:rgba(19,22,30,0.85);
  --cyan:#00D4C8;   --cyan-d:#00A89F;
  --cyan-dim:rgba(0,212,200,0.10);     --cyan-border:rgba(0,212,200,0.22);
  --coral:#FF6B4A;  --coral-d:#E04D2E;
  --coral-dim:rgba(255,107,74,0.10);   --coral-border:rgba(255,107,74,0.25);
  --violet:#7C6FF7; --violet-d:#5D52E0;
  --violet-dim:rgba(124,111,247,0.10); --violet-border:rgba(124,111,247,0.22);
  --green:#1FD9A0;  --green-d:#13B882; --green-dim:rgba(31,217,160,0.10);
  --amber:#F7B731;  --red:#FF4D6A;
  --tx:#F2F4F8; --tx-2:#9BA8BF; --tx-3:#4E5A6E;
  --bd:rgba(255,255,255,0.065); --bd2:rgba(255,255,255,0.12);
  --gC:rgba(0,212,200,0.16); --gO:rgba(255,107,74,0.14); --gV:rgba(124,111,247,0.14);
  --fm:'Plus Jakarta Sans',sans-serif;
  --fb:'DM Sans',sans-serif;
  --r:16px; --rs:10px;
  --e:all 0.26s cubic-bezier(.4,0,.2,1);
}

/* ── LIGHT MODE ── */
.lm{
  --bg:#F3F5FA; --s1:#EAEEF7; --s2:#E0E6F2; --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95; --cyan-d:#007870;
  --cyan-dim:rgba(0,158,149,0.08); --cyan-border:rgba(0,158,149,0.2);
  --coral:#E8512B; --coral-d:#C43C1C;
  --coral-dim:rgba(232,81,43,0.08); --coral-border:rgba(232,81,43,0.2);
  --violet:#5B4FD9; --violet-d:#4540C0;
  --violet-dim:rgba(91,79,217,0.08); --violet-border:rgba(91,79,217,0.18);
  --green:#0DAF80; --green-d:#088C65; --green-dim:rgba(13,175,128,0.08);
  --amber:#D4980A; --red:#D63050;
  --tx:#0D1220; --tx-2:#344060; --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.09); --bd2:rgba(30,40,80,0.16);
  --gC:rgba(0,158,149,0.09); --gO:rgba(232,81,43,0.09);
}
.lm .navbar{background:rgba(243,245,250,0.96)!important;border-bottom-color:var(--bd);}
.lm .job-main,.lm .sidebar-card,.lm .related-card{background:rgba(255,255,255,0.9);}
.lm .job-poster,.lm .meta-item,.lm .sidebar-meta,.lm .poster-ava{background:rgba(0,0,0,0.05);}
.lm .btn-ghost{background:rgba(0,0,0,0.05);border-color:var(--bd2);color:var(--tx-2);}
.lm .share-btn{background:rgba(0,0,0,0.05);border-color:var(--bd2);}
.lm .share-btn:hover{background:rgba(0,0,0,0.1);color:var(--tx);}
.lm .skill-tag{background:var(--violet-dim);border-color:var(--violet-border);color:var(--violet);}
.lm .related-item{border-bottom-color:var(--bd);}

/* ══ RESET ══ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);
  font-family:var(--fb);min-height:100vh;
  -webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
a{text-decoration:none;color:inherit;}
h1,h2,h3,.job-title,.budget-main,.section-label,.related-title{
  font-family:var(--fm);-webkit-font-smoothing:antialiased;
}

/* ══ NAVBAR ══ */
.navbar{
  position:fixed;top:0;left:0;right:0;z-index:100;
  display:flex;align-items:center;justify-content:space-between;
  padding:0 5%;height:68px;
  background:rgba(12,14,20,0.88);backdrop-filter:blur(24px);
  border-bottom:1px solid var(--bd);transition:var(--e);
}
.navbar.scrolled{background:rgba(12,14,20,0.97);box-shadow:0 2px 30px rgba(0,0,0,0.5);}
.logo{display:flex;align-items:center;gap:9px;}
.logo-mark{
  width:34px;height:34px;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  border-radius:9px;display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:15px;color:#0C0E14;flex-shrink:0;
}
.logo-text{font-family:var(--fm);font-size:19px;font-weight:800;color:var(--tx);}
.logo-text span{color:var(--cyan);}
.nav-actions{display:flex;align-items:center;gap:8px;}
.theme-btn{
  width:36px;height:36px;border-radius:9px;
  background:rgba(255,255,255,0.04);border:1px solid var(--bd);
  display:flex;align-items:center;justify-content:center;
  font-size:15px;cursor:pointer;transition:var(--e);
}
.theme-btn:hover{background:rgba(255,255,255,0.08);}

/* ══ BUTTONS ══ */
.btn{
  display:inline-flex;align-items:center;gap:6px;padding:9px 18px;
  border-radius:var(--rs);font-family:var(--fb);font-size:13px;font-weight:600;
  cursor:pointer;border:none;text-decoration:none;transition:var(--e);
  white-space:nowrap;line-height:1.3;
}
.btn-ghost{background:rgba(255,255,255,0.04);border:1px solid var(--bd);color:var(--tx-2);}
.btn-ghost:hover{background:rgba(255,255,255,0.08);color:var(--tx);border-color:var(--bd2);}
.btn-cyan{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;font-weight:700;box-shadow:0 3px 14px var(--gC);}
.btn-cyan:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--gC);}
.btn-coral{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:700;box-shadow:0 3px 14px var(--gO);}
.btn-coral:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--gO);}
.btn-violet{background:linear-gradient(135deg,var(--violet),var(--violet-d));color:#fff;font-weight:700;}
.btn-violet:hover{transform:translateY(-2px);}
.btn-amber{background:linear-gradient(135deg,var(--amber),#E8A520);color:#0C0E14;font-weight:700;box-shadow:0 3px 14px rgba(247,183,49,0.3);}
.btn-amber:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(247,183,49,0.4);}
.btn-lg{padding:13px 26px;font-size:14px;border-radius:12px;font-family:var(--fm);font-weight:800;}

/* ══ CONTAINER ══ */
.container{max-width:1160px;margin:0 auto;padding:100px 24px 80px;}

/* ══ BREADCRUMB ══ */
.breadcrumb{
  display:flex;align-items:center;gap:8px;
  font-size:13px;color:var(--tx-3);margin-bottom:24px;flex-wrap:wrap;
}
.breadcrumb a{color:var(--tx-3);transition:color .2s;}
.breadcrumb a:hover{color:var(--cyan);}
.breadcrumb-sep{color:var(--bd2);}
.breadcrumb-current{color:var(--tx-2);}

/* ══ LAYOUT ══ */
.job-grid{display:grid;grid-template-columns:1fr 360px;gap:26px;align-items:start;}

/* ══ MAIN JOB CARD ══ */
.job-main{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:34px;transition:background .3s,border-color .3s;
}

/* Badges */
.job-badges{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:16px;}
.badge{padding:4px 11px;border-radius:7px;font-size:11px;font-weight:700;font-family:var(--fm);}
.b-open    {background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.2);}
.b-urgent  {background:var(--coral-dim);color:var(--coral);border:1px solid var(--coral-border);}
.b-cat     {background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.b-remote  {background:var(--cyan-dim);color:var(--cyan);border:1px solid var(--cyan-border);}
.b-featured{background:rgba(247,183,49,0.1);color:var(--amber);border:1px solid rgba(247,183,49,0.2);}

/* Title */
.job-title{
  font-size:clamp(22px,3.5vw,32px);font-weight:800;line-height:1.2;
  margin-bottom:20px;color:var(--tx);
}

/* Client info row */
.job-poster{
  display:flex;align-items:center;gap:14px;
  padding:16px 20px;
  background:rgba(0,0,0,0.2);border:1px solid var(--bd);
  border-radius:14px;margin-bottom:28px;
  transition:background .3s;
}
.poster-ava{
  width:50px;height:50px;border-radius:50%;flex-shrink:0;overflow:hidden;
  background:linear-gradient(135deg,var(--violet),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:19px;color:#fff;
  border:2px solid var(--cyan-border);
}
.poster-ava img{width:100%;height:100%;object-fit:cover;}
.poster-name{font-family:var(--fm);font-weight:700;font-size:15px;margin-bottom:3px;}
.poster-meta{font-size:12px;color:var(--tx-3);line-height:1.5;}

/* Section */
.section-divider{height:1px;background:var(--bd);margin:26px 0;}
.section-label{
  font-family:var(--fm);font-size:15px;font-weight:700;
  margin-bottom:13px;display:flex;align-items:center;gap:7px;
}

/* Description */
.job-description{font-size:14.5px;line-height:1.85;color:var(--tx-2);}
.job-description p{margin-bottom:12px;}
.job-description p:last-child{margin-bottom:0;}

/* Skills */
.skills-list{display:flex;flex-wrap:wrap;gap:7px;margin-top:8px;}
.skill-tag{
  background:var(--violet-dim);border:1px solid var(--violet-border);
  color:var(--violet);padding:5px 13px;border-radius:7px;
  font-size:12.5px;font-weight:600;font-family:var(--fb);
}

/* Meta grid */
.job-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.meta-item{
  background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  border-radius:11px;padding:14px 16px;transition:border-color .25s;
}
.meta-item:hover{border-color:var(--bd2);}
.meta-item-label{
  font-size:10px;color:var(--tx-3);font-weight:800;
  text-transform:uppercase;letter-spacing:.7px;margin-bottom:6px;
}
.meta-item-val{font-family:var(--fm);font-weight:700;font-size:14px;color:var(--tx);}

/* ══ RELATED JOBS ══ */
.related-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:22px;margin-top:22px;
  transition:background .3s;
}
.related-title{font-size:14px;font-weight:700;margin-bottom:14px;color:var(--tx);}
.related-item{padding:12px 0;border-bottom:1px solid var(--bd);transition:var(--e);}
.related-item:last-child{border-bottom:none;padding-bottom:0;}
.related-name{
  font-family:var(--fm);font-weight:700;font-size:13px;color:var(--tx);
  display:block;margin-bottom:4px;transition:color .2s;
}
.related-name:hover{color:var(--cyan);}
.related-budget{font-family:var(--fm);font-size:12px;color:var(--cyan);font-weight:700;}

/* ══ SIDEBAR ══ */
.job-sidebar{display:flex;flex-direction:column;gap:18px;position:sticky;top:88px;}
.sidebar-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:24px;transition:background .3s,border-color .3s;
}

/* Budget display */
.budget-main{
  font-family:var(--fm);font-size:34px;font-weight:900;
  color:var(--cyan);margin-bottom:3px;line-height:1;
}
.budget-label{font-size:12.5px;color:var(--tx-3);margin-bottom:20px;}

/* Meta rows */
.sidebar-meta{
  display:flex;flex-direction:column;gap:8px;
  padding:14px;background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  border-radius:11px;margin-bottom:18px;
}
.sm-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;}
.sm-key{color:var(--tx-3);}
.sm-val{font-family:var(--fm);font-weight:700;font-size:13px;color:var(--tx);}

/* Action buttons */
.action-stack{display:flex;flex-direction:column;gap:9px;}

/* Share row */
.share-row{display:flex;gap:7px;margin-top:4px;}
.share-btn{
  flex:1;padding:9px;
  background:rgba(255,255,255,0.04);border:1px solid var(--bd);
  border-radius:9px;color:var(--tx-3);font-size:12.5px;
  cursor:pointer;text-align:center;transition:var(--e);
  font-family:var(--fb);font-weight:500;
}
.share-btn:hover{background:rgba(255,255,255,0.08);color:var(--tx);border-color:var(--bd2);}

/* Sidebar stats mini strip */
.sidebar-stats{
  display:flex;gap:10px;margin-bottom:18px;
}
.ss-item{
  flex:1;background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  border-radius:10px;padding:11px 8px;text-align:center;
}
.ss-val{font-family:var(--fm);font-weight:800;font-size:16px;line-height:1;}
.ss-lbl{font-size:10px;color:var(--tx-3);margin-top:3px;}

/* ══ ALERTS ══ */
.alert{padding:12px 16px;border-radius:var(--rs);margin-bottom:18px;font-size:13.5px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);}
.alert-warning{background:rgba(247,183,49,0.08);border:1px solid rgba(247,183,49,0.2);color:var(--amber);}

/* ══ TOAST ══ */
#toast-container{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{
  display:flex;align-items:center;gap:11px;background:var(--s2);
  border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);
  max-width:330px;min-width:240px;
  box-shadow:0 12px 36px rgba(0,0,0,.5);
  animation:toastIn .35s ease;backdrop-filter:blur(14px);
}
.toast.success{border-left:3px solid var(--green);}
.toast.info   {border-left:3px solid var(--cyan);}
.toast.error  {border-left:3px solid var(--red);}
.toast-body{flex:1;}
.toast-title{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}
.toast-msg{font-size:11.5px;color:var(--tx-3);}
.toast-x{cursor:pointer;color:var(--tx-3);font-size:17px;flex-shrink:0;}
@keyframes toastIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}
@keyframes slideIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}

/* ══ RESPONSIVE ══ */
@media(max-width:960px){
  .job-grid{grid-template-columns:1fr;}
  .job-sidebar{position:static;}
  .job-meta-grid{grid-template-columns:1fr 1fr;}
}
@media(max-width:768px){
  .nav-actions .btn:first-child{display:none;}
  .job-main{padding:22px 16px;}
  .container{padding:86px 14px 60px;}
  .job-meta-grid{grid-template-columns:1fr;}
}
</style>
</head>
<body class="<?= $isLight?'lm':'' ?>" id="appBody">

<!-- ══ NAVBAR ══ -->
<nav class="navbar" id="nav">
  <a href="<?= APP_URL ?>/index.php" class="logo">
    <div class="logo-mark">G</div>
    <span class="logo-text">Gig<span>Ghana</span></span>
  </a>
  <div class="nav-actions">
    <button class="theme-btn" id="themeBtn" onclick="toggleTheme()"><?= $isLight?'☀️':'🌙' ?></button>
    <?php if(isLoggedIn()): ?>
      <a href="<?= APP_URL ?>/<?= $user['role'] ?>/dashboard.php" class="btn btn-ghost">Dashboard</a>
      <a href="<?= APP_URL ?>/auth/logout.php"                    class="btn btn-ghost">Sign Out</a>
    <?php else: ?>
      <a href="<?= APP_URL ?>/auth/login.php"                     class="btn btn-ghost">Sign In</a>
      <a href="<?= APP_URL ?>/auth/register.php"                  class="btn btn-coral">Get Started</a>
    <?php endif; ?>
  </div>
</nav>

<!-- ══ CONTAINER ══ -->
<div class="container">

  <?php if(isset($_GET['success'])): ?>
  <div class="alert alert-success">✓ <?= sanitize($_GET['success']) ?></div>
  <?php endif; ?>

  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="<?= APP_URL ?>/index.php">🏠 Home</a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= APP_URL ?>/search/jobs.php">Browse Jobs</a>
    <span class="breadcrumb-sep">/</span>
    <?php if($job['cat_name']): ?>
    <a href="<?= APP_URL ?>/search/jobs.php?category=<?= $job['category_id'] ?>"><?= sanitize($job['cat_name']) ?></a>
    <span class="breadcrumb-sep">/</span>
    <?php endif; ?>
    <span class="breadcrumb-current"><?= sanitize(mb_substr($job['title'],0,40)).(mb_strlen($job['title'])>40?'…':'') ?></span>
  </div>

  <div class="job-grid">

    <!-- ══ MAIN CONTENT ══ -->
    <div>
      <div class="job-main">

        <!-- Badges -->
        <div class="job-badges">
          <span class="badge b-open">● <?= ucfirst(str_replace('_',' ',$job['status'])) ?></span>
          <?php if($job['is_urgent'])  : ?><span class="badge b-urgent">🔥 Urgent</span><?php endif; ?>
          <?php if($job['is_featured']): ?><span class="badge b-featured">⭐ Featured</span><?php endif; ?>
          <?php if($job['cat_name'])   : ?><span class="badge b-cat"><?= sanitize($job['cat_name']) ?></span><?php endif; ?>
          <span class="badge b-remote"><?= ucfirst(str_replace('_',' ',$job['location_type'])) ?></span>
        </div>

        <!-- Title -->
        <h1 class="job-title"><?= sanitize($job['title']) ?></h1>

        <!-- Client row -->
        <div class="job-poster">
          <div class="poster-ava">
            <?php if(!empty($job['avatar'])): ?><img src="<?= sanitize($job['avatar']) ?>" alt="" loading="lazy"><?php else: echo strtoupper(substr($job['first_name'],0,1)); endif; ?>
          </div>
          <div>
            <div class="poster-name"><?= sanitize($job['first_name'].' '.$job['last_name']) ?></div>
            <div class="poster-meta">
              📍 <?= sanitize($job['client_location'] ?: 'Ghana') ?> &nbsp;·&nbsp;
              👤 Member since <?= date('M Y', strtotime($job['client_since'])) ?> &nbsp;·&nbsp;
              👁 <?= number_format($job['views']) ?> views
            </div>
          </div>
        </div>

        <!-- Description -->
        <div class="section-label">📋 Job Description</div>
        <div class="job-description">
          <?= nl2br(htmlspecialchars($job['description'])) ?>
        </div>

        <!-- Requirements -->
        <?php if($job['requirements']): ?>
        <div class="section-divider"></div>
        <div class="section-label">✅ Requirements & Deliverables</div>
        <div class="job-description"><?= nl2br(htmlspecialchars($job['requirements'])) ?></div>
        <?php endif; ?>

        <!-- Skills -->
        <?php if(!empty($skills)): ?>
        <div class="section-divider"></div>
        <div class="section-label">🛠 Required Skills</div>
        <div class="skills-list">
          <?php foreach($skills as $sk): ?>
          <span class="skill-tag"><?= sanitize($sk) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Job details grid -->
        <div class="section-divider"></div>
        <div class="section-label">📊 Job Details</div>
        <div class="job-meta-grid">
          <div class="meta-item">
            <div class="meta-item-label">Budget</div>
            <div class="meta-item-val" style="color:var(--cyan);"><?= formatCurrency($job['budget_min']) ?><?= $job['budget_max']>$job['budget_min']?' – '.formatCurrency($job['budget_max']):'' ?></div>
          </div>
          <div class="meta-item">
            <div class="meta-item-label">Duration</div>
            <div class="meta-item-val"><?= ucfirst(str_replace('_',' ',$job['duration'])) ?></div>
          </div>
          <div class="meta-item">
            <div class="meta-item-label">Experience</div>
            <div class="meta-item-val"><?= ucfirst($job['experience_level']) ?> level</div>
          </div>
          <div class="meta-item">
            <div class="meta-item-label">Work Type</div>
            <div class="meta-item-val"><?= ucfirst(str_replace('_',' ',$job['location_type'])) ?></div>
          </div>
          <div class="meta-item">
            <div class="meta-item-label">Proposals</div>
            <div class="meta-item-val" style="color:var(--violet);"><?= $job['proposal_count'] ?> received</div>
          </div>
          <div class="meta-item">
            <div class="meta-item-label">Posted</div>
            <div class="meta-item-val"><?= timeAgo($job['created_at']) ?></div>
          </div>
          <?php if($job['deadline']): ?>
          <div class="meta-item">
            <div class="meta-item-label">Deadline</div>
            <div class="meta-item-val" style="color:var(--amber);">📅 <?= date('M j, Y', strtotime($job['deadline'])) ?></div>
          </div>
          <?php endif; ?>
          <?php if($job['location']): ?>
          <div class="meta-item">
            <div class="meta-item-label">Location</div>
            <div class="meta-item-val">📍 <?= sanitize($job['location']) ?></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Related Jobs -->
      <?php if(!empty($relatedJobs)): ?>
      <div class="related-card">
        <div class="related-title">💼 Similar Jobs in <?= sanitize($job['cat_name']??'this category') ?></div>
        <?php foreach($relatedJobs as $rj): ?>
        <div class="related-item">
          <a href="<?= APP_URL ?>/job-details.php?id=<?= $rj['id'] ?>" class="related-name"><?= sanitize($rj['title']) ?></a>
          <div style="display:flex;justify-content:space-between;font-size:12px;">
            <span class="related-budget"><?= formatCurrency($rj['budget_min']) ?></span>
            <span style="color:var(--tx-3);"><?= timeAgo($rj['created_at']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- ══ SIDEBAR ══ -->
    <div class="job-sidebar">
      <div class="sidebar-card">

        <!-- Budget hero -->
        <div class="budget-main">
          <?= formatCurrency($job['budget_min']) ?>
          <?= $job['budget_max']>$job['budget_min']?' – '.formatCurrency($job['budget_max']):'' ?>
        </div>
        <div class="budget-label"><?= ucfirst($job['budget_type']) ?> price · GHS</div>

        <!-- Quick stats strip -->
        <div class="sidebar-stats">
          <div class="ss-item">
            <div class="ss-val" style="color:var(--violet);"><?= $job['proposal_count'] ?></div>
            <div class="ss-lbl">Proposals</div>
          </div>
          <div class="ss-item">
            <div class="ss-val" style="color:var(--amber);"><?= number_format($job['views']) ?></div>
            <div class="ss-lbl">Views</div>
          </div>
          <div class="ss-item">
            <div class="ss-val" style="color:var(--cyan);"><?= ucfirst($job['experience_level']) ?></div>
            <div class="ss-lbl">Level</div>
          </div>
        </div>

        <!-- Meta -->
        <div class="sidebar-meta">
          <div class="sm-row">
            <span class="sm-key">Proposals</span>
            <span class="sm-val"><?= $job['proposal_count'] ?></span>
          </div>
          <div class="sm-row">
            <span class="sm-key">Duration</span>
            <span class="sm-val"><?= ucfirst(str_replace('_',' ',$job['duration'])) ?></span>
          </div>
          <div class="sm-row">
            <span class="sm-key">Experience</span>
            <span class="sm-val"><?= ucfirst($job['experience_level']) ?></span>
          </div>
          <div class="sm-row">
            <span class="sm-key">Location</span>
            <span class="sm-val"><?= ucfirst(str_replace('_',' ',$job['location_type'])) ?></span>
          </div>
          <?php if($job['deadline']): ?>
          <div class="sm-row">
            <span class="sm-key">Deadline</span>
            <span class="sm-val" style="color:var(--amber);">📅 <?= date('M j, Y', strtotime($job['deadline'])) ?></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- CTA Actions -->
        <div class="action-stack">
          <?php if(!isLoggedIn()): ?>
            <a href="<?= APP_URL ?>/auth/register.php?role=provider" class="btn btn-cyan btn-lg" style="justify-content:center;">🚀 Apply for this Job</a>
            <a href="<?= APP_URL ?>/auth/login.php"                  class="btn btn-ghost"       style="justify-content:center;">Sign In to Apply</a>

          <?php elseif($_SESSION['user_role'] === 'provider'): ?>
            <?php if($alreadyApplied): ?>
              <div class="alert alert-success" style="margin:0;text-align:center;justify-content:center;">✓ You've already applied!</div>
            <?php elseif($job['status'] === 'open'): ?>
              <a href="<?= APP_URL ?>/provider/submit-proposal.php?job_id=<?= $jobId ?>" class="btn btn-cyan btn-lg" style="justify-content:center;">🚀 Submit Proposal</a>
            <?php endif; ?>
            <button class="btn btn-ghost" style="justify-content:center;" onclick="toggleSaveJob(<?= $jobId ?>)" id="saveBtn">
              <?= $isSaved ? '🔖 Saved' : '📌 Save Job' ?>
            </button>

          <?php elseif($_SESSION['user_role'] === 'client'): ?>
            <a href="<?= APP_URL ?>/client/proposals.php?job_id=<?= $jobId ?>" class="btn btn-coral btn-lg" style="justify-content:center;">📩 View Proposals (<?= $job['proposal_count'] ?>)</a>
            <a href="<?= APP_URL ?>/client/edit-job.php?id=<?= $jobId ?>"      class="btn btn-ghost"       style="justify-content:center;">✏️ Edit Job</a>
          <?php endif; ?>

          <!-- Share row -->
          <div class="share-row">
            <button class="share-btn" onclick="copyLink()">🔗 Copy</button>
            <button class="share-btn" onclick="shareJob('twitter')">𝕏 Tweet</button>
            <button class="share-btn" onclick="shareJob('whatsapp')">💬 Share</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /sidebar -->

  </div><!-- /job-grid -->
</div><!-- /container -->

<div id="toast-container"></div>

<script>
/* ══ THEME ══ */
function toggleTheme(){
  const l=document.getElementById('appBody').classList.toggle('lm');
  const v=l?'light':'dark';
  localStorage.setItem('gg_theme',v);
  document.cookie=`gg_theme=${v};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent=l?'☀️':'🌙';
}
(function(){
  const s=localStorage.getItem('gg_theme')||'<?= $isLight?"light":"dark" ?>';
  const b=document.getElementById('appBody'),btn=document.getElementById('themeBtn');
  if(s==='light'){b.classList.add('lm');if(btn)btn.textContent='☀️';}
  else{b.classList.remove('lm');if(btn)btn.textContent='🌙';}
})();

/* ══ NAVBAR SCROLL ══ */
window.addEventListener('scroll',()=>{
  document.getElementById('nav').classList.toggle('scrolled', window.scrollY > 40);
});

/* ══ TOAST ══ */
function showToast(title, msg, type='info', d=4000){
  const icons={success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
  const c=document.getElementById('toast-container');
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<span>${icons[type]||'ℹ️'}</span><div class="toast-body"><div class="toast-title">${title}</div><div class="toast-msg">${msg}</div></div><span class="toast-x" onclick="this.parentElement.remove()">×</span>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(50px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),330);},d);
}

<?php if(isset($_GET['success'])): ?>showToast('Success','<?= addslashes(sanitize($_GET['success'])) ?>','success');<?php endif; ?>

/* ══ SAVE JOB ══ */
function toggleSaveJob(id){
  fetch('<?= APP_URL ?>/api/jobs.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=toggle_save&job_id=${id}&csrf=<?= $csrf ?>`
  }).then(r=>r.json()).then(d=>{
    const btn=document.getElementById('saveBtn');
    if(d.saved){btn.textContent='🔖 Saved';showToast('Saved','Job saved to your list.','success');}
    else{btn.textContent='📌 Save Job';showToast('Removed','Job removed from saved.','info');}
  }).catch(()=>showToast('Error','Could not save job.','error'));
}

/* ══ SHARE ══ */
function copyLink(){
  navigator.clipboard.writeText(window.location.href)
    .then(()=>showToast('Copied!','Link copied to clipboard.','success'))
    .catch(()=>showToast('Error','Could not copy link.','error'));
}
function shareJob(platform){
  const url   = encodeURIComponent(window.location.href);
  const title = encodeURIComponent('<?= addslashes(sanitize($job['title'])) ?> — GigGhana');
  if(platform==='twitter')  window.open(`https://twitter.com/intent/tweet?text=${title}&url=${url}`,'_blank');
  if(platform==='whatsapp') window.open(`https://wa.me/?text=${title}%20${url}`,'_blank');
}
</script>
</body>
</html>