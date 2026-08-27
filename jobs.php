<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

/* ── Filters ─────────────────────────────────────────────── */
$q        = sanitize($_GET['q']             ?? '');
$catId    = (int)($_GET['category']         ?? 0);
$budMin   = (float)($_GET['budget_min']     ?? 0);
$budMax   = (float)($_GET['budget_max']     ?? 0);
$expLevel = sanitize($_GET['experience']    ?? '');
$locType  = sanitize($_GET['location_type'] ?? '');
$jobType  = sanitize($_GET['budget_type']   ?? '');
$posted   = sanitize($_GET['posted']        ?? '');
$sort     = sanitize($_GET['sort']          ?? 'newest');
$page     = max(1, (int)($_GET['page']      ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';
$user    = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
$csrf    = generateCSRF();

/* ── Provider context (only matters if logged in as provider) ── */
$providerId = 0;
$subTier    = 'free';
$remaining  = 3;

/* ── Data loading ─────────────────────────────────────────── */
try {
    $db         = getDB();
    $categories = getCategories();

    /* Provider record for apply-button logic */
    if (isLoggedIn() && $user && $user['role'] === 'provider') {
        $stPv = $db->prepare("SELECT * FROM providers WHERE user_id=? LIMIT 1");
        $stPv->execute([$_SESSION['user_id']]);
        $prov = $stPv->fetch();
        if ($prov) {
            $providerId = (int)$prov['id'];
            $subTier    = $prov['subscription_tier'] ?? 'free';
            $propUsed   = (int)($prov['proposals_used'] ?? 0);
            $propLimit  = ['free'=>3,'verified'=>999,'premium'=>999][$subTier] ?? 3;
            $remaining  = max(0, $propLimit - $propUsed);
        }
    }

    /* Client trust sub-queries */
    $trustSub = "(SELECT COUNT(*) FROM jobs jx WHERE jx.client_id=u.id AND jx.status='completed') AS client_jobs_done,
                 (SELECT COUNT(*) FROM jobs jy WHERE jy.client_id=u.id AND jy.hired_provider_id IS NOT NULL) AS client_hired,
                 (SELECT COUNT(*) FROM jobs jz WHERE jz.client_id=u.id) AS client_total_jobs";

    /* WHERE builder */
    $where  = ["j.status = 'open'"];
    $params = [];

    if ($q) {
        $where[]  = "(j.title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ?)";
        $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
    }
    if ($catId)    { $where[] = "j.category_id = ?";  $params[] = $catId; }
    if ($budMin)   { $where[] = "j.budget_max >= ?";   $params[] = $budMin; }
    if ($budMax)   { $where[] = "j.budget_min <= ?";   $params[] = $budMax; }
    if ($expLevel) { $where[] = "(j.experience_level = ? OR j.experience_level = 'any')"; $params[] = $expLevel; }
    if ($locType)  { $where[] = "j.location_type = ?"; $params[] = $locType; }
    if ($jobType)  { $where[] = "j.budget_type = ?";   $params[] = $jobType; }
    if ($posted === '24h')  { $where[] = "j.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)"; }
    if ($posted === '3d')   { $where[] = "j.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)"; }
    if ($posted === 'week') { $where[] = "j.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; }

    $whereStr = implode(' AND ', $where);
    $orderBy  = match($sort) {
        'budget_high' => 'j.budget_max DESC',
        'budget_low'  => 'j.budget_min ASC',
        'proposals'   => 'j.proposal_count ASC',
        default       => 'j.is_urgent DESC, j.is_featured DESC, j.created_at DESC',
    };

    /* Total */
    $stT = $db->prepare("SELECT COUNT(*) FROM jobs j WHERE $whereStr");
    $stT->execute($params);
    $totalJobs  = (int)$stT->fetchColumn();
    $totalPages = (int)ceil($totalJobs / $perPage);

    /* Main query */
    $alreadyApplied = $providerId
        ? "(SELECT 1 FROM proposals WHERE job_id=j.id AND provider_id=$providerId LIMIT 1)"
        : "NULL";
    $isSaved = isLoggedIn()
        ? "(SELECT 1 FROM saved_jobs WHERE job_id=j.id AND user_id={$_SESSION['user_id']} LIMIT 1)"
        : "NULL";

    $stmt = $db->prepare(
        "SELECT j.*,
                c.name AS cat_name, c.icon AS cat_icon,
                u.first_name, u.last_name, u.avatar AS client_avatar,
                u.phone_verified, u.payment_verified,
                $trustSub,
                $alreadyApplied AS already_applied,
                $isSaved AS is_saved
         FROM jobs j
         LEFT JOIN categories c ON c.id = j.category_id
         JOIN  users u           ON u.id = j.client_id
         WHERE $whereStr
         ORDER BY $orderBy
         LIMIT $perPage OFFSET $offset"
    );
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();

    /* Skills for visible jobs */
    $jobSkills = [];
    if ($jobs) {
        $ids = implode(',', array_map(fn($j) => (int)$j['id'], $jobs));
        $stSk = $db->query(
            "SELECT js.job_id, s.name FROM job_skills js
             JOIN skills s ON s.id=js.skill_id WHERE js.job_id IN ($ids)"
        );
        foreach ($stSk->fetchAll() as $sk) $jobSkills[$sk['job_id']][] = $sk['name'];
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $jobs = []; $totalJobs = 0; $totalPages = 0;
    $categories = []; $jobSkills = [];
}

/* ── Helpers ─────────────────────────────────────────────── */
$iconMap = [
    'code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
    'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
    'briefcase'=>'⚖️','headphones'=>'🎧','camera'=>'📷','globe'=>'🌐',
    'tool'=>'🔧','bar-chart'=>'📊','music'=>'🎵',
];
function propColor(int $n): string {
    if ($n <= 5)  return '#1FD9A0';
    if ($n <= 15) return '#F7B731';
    return '#FF4D6A';
}
function propLabel(int $n): string {
    if ($n <= 5)  return 'Low competition';
    if ($n <= 15) return 'Medium competition';
    return 'High competition';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="Browse open freelance jobs across Ghana — IT, trades, health, education and more on GigGhana.">
<title>Browse Jobs — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════════════════
   TOKENS — exact match to index.php
══════════════════════════════════════════════════════ */
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
  --gC:rgba(0,212,200,0.16); --gO:rgba(255,107,74,0.14); --gV:rgba(124,111,247,0.14);
  --fm:'Plus Jakarta Sans',sans-serif; --fb:'DM Sans',sans-serif;
  --r:16px; --rs:10px; --e:all 0.26s ease;
}
.lm{
  --bg:#F3F5FA; --s1:#EAEEF7; --s2:#E0E6F2; --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95; --cyan-d:#007870; --cyan-l:#00CFC3;
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

/* ── Light-mode specific overrides ── */
.lm .navbar { background:rgba(243,245,250,0.96)!important; border-bottom-color:var(--bd); }
.lm .navbar.on { background:rgba(243,245,250,0.99)!important; box-shadow:0 4px 28px rgba(13,18,32,0.07); }
.lm .mobile-nav { background:rgba(243,245,250,0.99); }
.lm .filter-panel, .lm .job-card, .lm .section-card, .lm .cat-pill, .lm .rec-card { background:rgba(255,255,255,0.9); }
.lm .sb-input, .lm .sb-select, .lm .sort-select { background:rgba(0,0,0,0.05); color:var(--tx); }
.lm .btn-ghost { border-color:var(--bd2); color:var(--tx-2); }
.lm .pag-btn { background:rgba(0,0,0,0.05); }
.lm .fp-option:hover { background:rgba(0,0,0,0.04); }
.lm .fp-budget-inputs input { background:rgba(0,0,0,0.05); }
.lm .footer-wrap { background:var(--s1); }
.lm .soc-btn:hover { background:var(--coral-dim); color:var(--coral); border-color:var(--coral-border); }
.lm .footer-links a:hover { color:var(--cyan); }
.lm .page-hero { background:linear-gradient(135deg,var(--s1),var(--s2)); }
.lm .search-card { background:rgba(255,255,255,0.9); }
.lm .job-card::before { background:radial-gradient(circle,rgba(0,158,149,0.06),transparent 70%); }

/* ══ RESET ══ */
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body { background:var(--bg); color:var(--tx); font-family:var(--fb); font-size:15px; line-height:1.65; overflow-x:hidden; -webkit-font-smoothing:antialiased; transition:background .3s,color .3s; }
::-webkit-scrollbar { width:4px; } ::-webkit-scrollbar-track { background:var(--bg); } ::-webkit-scrollbar-thumb { background:var(--s3); border-radius:2px; }
img { display:block; max-width:100%; }
a { text-decoration:none; color:inherit; }
h1,h2,h3,h4,.logo-text { font-family:var(--fm); -webkit-font-smoothing:antialiased; }

/* ══ NAVBAR (index.php exact) ══ */
.navbar { position:fixed; top:0; left:0; right:0; z-index:1000; display:flex; align-items:center; justify-content:space-between; padding:0 5%; height:64px; background:rgba(12,14,20,0.84); backdrop-filter:blur(24px); border-bottom:1px solid var(--bd); transition:var(--e); }
.navbar.on { background:rgba(12,14,20,0.97); box-shadow:0 2px 30px rgba(0,0,0,0.5); }
.logo { display:flex; align-items:center; gap:9px; flex-shrink:0; }
.logo-mark { width:36px; height:36px; background:linear-gradient(135deg,var(--cyan),var(--cyan-d)); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:17px; color:#0C0E14; font-family:var(--fm); }
.logo-text { font-size:20px; font-weight:800; letter-spacing:-0.3px; } .logo-text span { color:var(--cyan); }
.nav-links { display:flex; align-items:center; gap:2px; }
.nav-links a { color:var(--tx-2); font-size:13.5px; font-weight:500; padding:6px 13px; border-radius:var(--rs); transition:var(--e); white-space:nowrap; }
.nav-links a:hover { color:var(--tx); background:rgba(255,255,255,0.05); }
.nav-links a.active { color:var(--cyan); background:var(--cyan-dim); }
.nav-acts { display:flex; align-items:center; gap:8px; }
.btn { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:var(--rs); font-size:13px; font-weight:600; cursor:pointer; border:none; transition:var(--e); white-space:nowrap; line-height:1.3; font-family:var(--fm); }
.btn-lg { padding:12px 26px; font-size:14px; border-radius:13px; }
.btn-sm { padding:6px 13px; font-size:12px; border-radius:9px; }
.btn-ghost { background:transparent; color:var(--tx-2); border:1px solid var(--bd); }
.btn-ghost:hover { background:rgba(255,255,255,0.06); border-color:var(--bd2); color:var(--tx); }
.btn-gold { background:linear-gradient(135deg,var(--coral),var(--coral-d)); color:#fff; font-weight:700; box-shadow:0 3px 18px var(--gO); }
.btn-gold:hover { transform:translateY(-2px); box-shadow:0 8px 28px var(--gO); }
.btn-blue { background:linear-gradient(135deg,var(--cyan),var(--cyan-d)); color:#0C0E14; font-weight:700; box-shadow:0 3px 18px var(--gC); }
.btn-blue:hover { transform:translateY(-2px); box-shadow:0 8px 28px var(--gC); }
.btn-theme { background:transparent; color:var(--tx-2); border:1px solid var(--bd); border-radius:var(--rs); padding:7px 11px; cursor:pointer; font-size:14px; transition:var(--e); line-height:1; }
.btn-theme:hover { background:rgba(255,255,255,0.07); }
.ham { display:none; flex-direction:column; gap:4.5px; cursor:pointer; padding:8px; }
.ham span { display:block; width:20px; height:2px; background:var(--tx); border-radius:2px; transition:var(--e); }
.mobile-nav { display:none; position:fixed; top:64px; left:0; right:0; background:rgba(12,14,20,0.98); backdrop-filter:blur(24px); border-bottom:1px solid var(--bd); padding:14px 5%; z-index:999; flex-direction:column; gap:4px; }
.mobile-nav.open { display:flex; }
.mobile-nav a { color:var(--tx-2); padding:10px 14px; border-radius:var(--rs); font-size:14px; font-weight:500; transition:var(--e); }
.mobile-nav a:hover { color:var(--tx); background:rgba(255,255,255,0.05); }

/* ══ PAGE HERO ══ */
.page-hero {
  margin-top:64px; padding:52px 5% 44px;
  background:linear-gradient(135deg,var(--s1) 0%,rgba(0,212,200,0.04) 60%,rgba(124,111,247,0.03) 100%);
  border-bottom:1px solid var(--bd); position:relative; overflow:hidden;
}
.page-hero::before {
  content:''; position:absolute; top:-80px; right:-80px;
  width:320px; height:320px; border-radius:50%;
  background:radial-gradient(circle,rgba(0,212,200,0.09),transparent 70%);
  pointer-events:none;
}
.page-hero::after {
  content:''; position:absolute; bottom:-60px; left:200px;
  width:220px; height:220px; border-radius:50%;
  background:radial-gradient(circle,rgba(124,111,247,0.07),transparent 70%);
  pointer-events:none;
}
.hero-inner { max-width:1160px; margin:0 auto; position:relative; z-index:1; }
.hero-breadcrumb { display:flex; align-items:center; gap:7px; font-size:12.5px; color:var(--tx-3); margin-bottom:16px; }
.hero-breadcrumb a { color:var(--cyan); transition:color .2s; } .hero-breadcrumb a:hover { color:var(--cyan-l); }
.hero-breadcrumb span { color:var(--tx-3); }
.hero-h1 { font-family:var(--fm); font-size:clamp(24px,3vw,38px); font-weight:800; margin-bottom:8px; letter-spacing:-0.3px; }
.hero-sub { font-size:15px; color:var(--tx-2); max-width:520px; line-height:1.7; }
.hero-stats { display:flex; gap:22px; margin-top:22px; flex-wrap:wrap; }
.hstat { display:flex; align-items:center; gap:7px; font-size:13px; color:var(--tx-3); }
.hstat strong { font-family:var(--fm); font-weight:800; color:var(--cyan); }

/* ══ SEARCH CARD ══ */
.search-card {
  background:var(--glass); backdrop-filter:blur(14px);
  border:1px solid var(--bd); border-radius:var(--r);
  padding:22px 24px; margin-bottom:22px; transition:border-color .3s;
}
.search-card:focus-within { border-color:var(--cyan-border); }
.sb-row { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; }
.sb-main { flex:1; min-width:200px; }
.sb-label { font-size:10.5px; font-weight:700; color:var(--tx-3); text-transform:uppercase; letter-spacing:.6px; margin-bottom:5px; }
.sb-input, .sb-select {
  width:100%; background:rgba(0,0,0,0.22); border:1.5px solid var(--bd);
  border-radius:var(--rs); padding:10px 14px; color:var(--tx);
  font-family:var(--fb); font-size:13.5px; outline:none; transition:border-color .3s;
}
.sb-input:focus, .sb-select:focus { border-color:var(--cyan); box-shadow:0 0 0 3px var(--cyan-dim); }
.sb-input::placeholder { color:var(--tx-3); }
.sb-select option { background:var(--s2); color:var(--tx); }
.sb-filters { display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end; margin-top:14px; }
.sb-filter-item { min-width:130px; }

/* ══ CATEGORY PILLS ══ */
.cat-scroll-wrap {
  display:flex; gap:8px; overflow-x:auto; padding:0 2px 10px;
  margin-bottom:22px; scrollbar-width:none;
}
.cat-scroll-wrap::-webkit-scrollbar { display:none; }
.cat-pill {
  display:inline-flex; align-items:center; gap:7px;
  padding:8px 16px; border-radius:50px;
  background:var(--glass); border:1.5px solid var(--bd);
  color:var(--tx-2); font-size:12.5px; font-weight:600;
  cursor:pointer; transition:var(--e); white-space:nowrap;
  text-decoration:none; font-family:var(--fb); flex-shrink:0;
}
.cat-pill:hover { border-color:var(--cyan-border); color:var(--cyan); }
.cat-pill.active { background:var(--cyan-dim); border-color:var(--cyan); color:var(--cyan); }

/* ══ PAGE BODY ══ */
.page-wrap { max-width:1160px; margin:0 auto; padding:28px 5% 80px; }
.page-body { display:grid; grid-template-columns:248px 1fr; gap:22px; align-items:start; }

/* ══ FILTER PANEL ══ */
.filter-panel {
  background:var(--glass); backdrop-filter:blur(14px);
  border:1px solid var(--bd); border-radius:var(--r);
  padding:18px 16px; position:sticky; top:82px;
  transition:background .3s, border-color .3s;
}
.fp-title { font-family:var(--fm); font-size:14px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; }
.fp-section { margin-bottom:18px; }
.fp-section:last-child { margin-bottom:0; }
.fp-label { font-size:10px; font-weight:800; color:var(--tx-3); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
.fp-option {
  display:flex; align-items:center; gap:9px; padding:8px 10px;
  border-radius:9px; cursor:pointer; transition:var(--e);
  font-size:13px; color:var(--tx-2); text-decoration:none;
}
.fp-option:hover { background:rgba(255,255,255,0.04); color:var(--tx); }
.fp-option.active { background:var(--cyan-dim); color:var(--cyan); }
.fp-check {
  width:16px; height:16px; border-radius:50%; border:2px solid var(--bd2);
  flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:var(--e);
}
.fp-option.active .fp-check { border-color:var(--cyan); background:var(--cyan); }
.fp-option.active .fp-check::after { content:''; width:6px; height:6px; border-radius:50%; background:#0C0E14; }
.fp-budget-inputs { display:flex; gap:6px; }
.fp-budget-inputs input {
  width:100%; background:rgba(0,0,0,0.22); border:1.5px solid var(--bd);
  border-radius:9px; padding:8px 10px; color:var(--tx);
  font-family:var(--fb); font-size:12.5px; outline:none; transition:border-color .3s;
}
.fp-budget-inputs input:focus { border-color:var(--cyan); }
.fp-budget-inputs input::placeholder { color:var(--tx-3); }

/* ══ RESULTS HEADER ══ */
.results-header {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:18px; gap:12px; flex-wrap:wrap;
}
.results-count { font-size:14px; color:var(--tx-3); }
.results-count strong { color:var(--tx); font-family:var(--fm); }
.sort-select {
  background:rgba(0,0,0,0.2); border:1.5px solid var(--bd); border-radius:10px;
  padding:8px 14px; color:var(--tx); font-family:var(--fb); font-size:12.5px;
  outline:none; cursor:pointer; transition:border-color .3s;
}
.sort-select:focus { border-color:var(--cyan); }
.sort-select option { background:var(--s2); }

/* ══ JOB GRID ══ */
.jobs-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(320px,1fr)); gap:18px; }

/* ══ JOB CARD (browse-jobs.php style) ══ */
.job-card {
  background:var(--glass); backdrop-filter:blur(14px);
  border:1px solid var(--bd); border-radius:var(--r);
  padding:20px; transition:var(--e);
  position:relative; overflow:hidden; display:flex; flex-direction:column;
  animation:cardIn .3s ease both;
}
@keyframes cardIn { from{opacity:0;transform:translateY(10px);} to{opacity:1;transform:translateY(0);} }
.job-card::before {
  content:''; position:absolute; top:-28px; right:-28px;
  width:100px; height:100px; border-radius:50%;
  background:radial-gradient(circle,rgba(0,212,200,0.06),transparent 70%);
  pointer-events:none;
}
.job-card:hover { transform:translateY(-4px); border-color:var(--cyan-border); box-shadow:0 18px 52px rgba(0,0,0,0.38); }
.job-card.applied-card { border-color:rgba(247,183,49,0.2); }
.job-card.featured-card { border-color:var(--violet-border); }

/* Card top */
.jc-top { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:12px; }
.jc-client { display:flex; align-items:center; gap:9px; }
.client-ava {
  width:38px; height:38px; border-radius:50%; flex-shrink:0; overflow:hidden;
  background:linear-gradient(135deg,var(--violet),var(--cyan-d));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-weight:800; font-size:13px; color:#fff;
  border:2px solid var(--cyan-border);
}
.client-ava img { width:100%; height:100%; object-fit:cover; }
.client-name { font-family:var(--fm); font-weight:700; font-size:12.5px; margin-bottom:1px; }
.client-time { font-size:10.5px; color:var(--tx-3); }
.trust-row { display:flex; gap:4px; margin-top:3px; }
.t-badge { font-size:9.5px; font-weight:700; padding:1px 6px; border-radius:4px; font-family:var(--fm); }
.tb-phone   { background:var(--green-dim); color:var(--green); }
.tb-payment { background:rgba(247,183,49,0.1); color:var(--amber); }

.jc-badges { display:flex; gap:5px; align-items:flex-start; flex-shrink:0; flex-wrap:wrap; }
.jbadge { padding:3px 9px; border-radius:6px; font-size:10px; font-weight:700; font-family:var(--fm); white-space:nowrap; }
.jb-urgent  { background:var(--coral-dim);  color:var(--coral);  border:1px solid var(--coral-border); }
.jb-feat    { background:var(--violet-dim); color:var(--violet); border:1px solid var(--violet-border); }
.jb-open    { background:var(--green-dim);  color:var(--green);  border:1px solid rgba(31,217,160,0.2); }
.jb-applied { background:rgba(247,183,49,0.1); color:var(--amber); border:1px solid rgba(247,183,49,0.22); }
.jb-cat     { background:var(--cyan-dim);   color:var(--cyan);   border:1px solid var(--cyan-border); }

/* Job title */
.job-title {
  font-family:var(--fm); font-weight:800; font-size:15.5px; line-height:1.3;
  margin-bottom:9px; color:var(--tx); display:block; transition:color .2s;
}
.job-title:hover { color:var(--cyan); }

/* Description */
.jc-desc {
  color:var(--tx-2); font-size:12.5px; line-height:1.65;
  margin-bottom:12px; flex:1;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}

/* Meta */
.jc-meta { display:flex; align-items:center; gap:9px; flex-wrap:wrap; font-size:11.5px; color:var(--tx-3); margin-bottom:12px; }

/* Skills */
.jc-skills { display:flex; flex-wrap:wrap; gap:5px; margin-bottom:12px; }
.skill-tag { background:var(--cyan-dim); border:1px solid var(--cyan-border); color:var(--cyan); padding:3px 9px; border-radius:6px; font-size:10.5px; font-weight:600; }
.skill-more { background:rgba(255,255,255,0.04); border:1px solid var(--bd); color:var(--tx-3); padding:3px 9px; border-radius:6px; font-size:10.5px; }

/* Client trust strip */
.client-trust {
  background:rgba(0,0,0,0.15); border:1px solid var(--bd);
  border-radius:10px; padding:9px 12px; margin-bottom:12px;
  display:flex; gap:14px; flex-wrap:wrap;
}
.ct-item { text-align:center; }
.ct-val { font-family:var(--fm); font-weight:800; font-size:13px; color:var(--tx); }
.ct-lbl { font-size:9.5px; color:var(--tx-3); margin-top:1px; }

/* Proposal competition */
.prop-competition {
  display:flex; align-items:center; gap:8px;
  padding:8px 12px; border-radius:9px;
  background:rgba(0,0,0,0.15); border:1px solid var(--bd);
  margin-bottom:12px;
}
.pc-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
.pc-count { font-family:var(--fm); font-weight:800; font-size:14px; }
.pc-label { font-size:11px; color:var(--tx-3); }

/* Card footer */
.jc-footer {
  display:flex; align-items:center; justify-content:space-between;
  padding-top:12px; border-top:1px solid var(--bd); gap:9px; margin-top:auto;
}
.jc-budget { font-family:var(--fm); font-weight:800; font-size:17px; color:var(--cyan); }
.jc-exp { font-size:10.5px; color:var(--tx-3); margin-top:2px; }
.jc-actions { display:flex; align-items:center; gap:6px; flex-shrink:0; }
.details-btn {
  display:inline-flex; align-items:center; gap:5px;
  padding:8px 13px; background:rgba(255,255,255,0.04);
  border:1px solid var(--bd); border-radius:10px;
  color:var(--tx-2); font-size:12px; font-weight:600;
  cursor:pointer; transition:var(--e); text-decoration:none; font-family:var(--fb);
}
.details-btn:hover { background:rgba(255,255,255,0.08); color:var(--tx); border-color:var(--bd2); }
.apply-btn {
  display:inline-flex; align-items:center; gap:5px;
  padding:8px 16px;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  color:#0C0E14; border:none; border-radius:10px;
  font-family:var(--fm); font-weight:700; font-size:12.5px;
  cursor:pointer; transition:var(--e); text-decoration:none;
  box-shadow:0 3px 12px var(--gC); white-space:nowrap;
}
.apply-btn:hover { transform:translateY(-2px); box-shadow:0 7px 22px var(--gC); }
.apply-btn.sent {
  background:rgba(247,183,49,0.1); color:var(--amber);
  border:1px solid rgba(247,183,49,0.22); box-shadow:none;
}
.apply-btn.sent:hover { transform:none; }
.save-btn {
  width:34px; height:34px; border-radius:9px;
  background:rgba(255,255,255,0.04); border:1px solid var(--bd);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; transition:var(--e); flex-shrink:0; font-size:15px;
}
.save-btn:hover, .save-btn.saved { background:rgba(247,183,49,0.1); border-color:rgba(247,183,49,0.3); }

/* ══ EMPTY STATE ══ */
.empty-state { text-align:center; padding:64px 24px; }
.es-icon { font-size:52px; margin-bottom:16px; }
.es-title { font-family:var(--fm); font-size:20px; font-weight:800; margin-bottom:8px; }
.es-text { color:var(--tx-3); font-size:14px; max-width:340px; margin:0 auto 22px; }

/* ══ PAGINATION ══ */
.pagination { display:flex; gap:7px; justify-content:center; margin-top:32px; flex-wrap:wrap; }
.pag-btn {
  padding:9px 16px; border-radius:10px; text-decoration:none;
  font-size:13.5px; font-weight:600; font-family:var(--fm);
  color:var(--tx-3); background:rgba(255,255,255,0.04);
  border:1px solid var(--bd); transition:var(--e);
}
.pag-btn.active { background:var(--cyan-dim); color:var(--cyan); border-color:var(--cyan-border); }
.pag-btn:hover:not(.active) { background:rgba(255,255,255,0.08); color:var(--tx); border-color:var(--bd2); }

/* ══ JOB DETAIL MODAL ══ */
.modal-overlay {
  display:none; position:fixed; inset:0; z-index:2000;
  background:rgba(0,0,0,0.85); backdrop-filter:blur(16px);
  align-items:center; justify-content:center; padding:20px;
}
.modal-overlay.open { display:flex; animation:mfIn .2s ease; }
@keyframes mfIn { from{opacity:0;} to{opacity:1;} }
.modal-box {
  background:var(--s1); border:1px solid var(--bd2); border-radius:20px;
  width:100%; max-width:640px; max-height:88vh; overflow-y:auto;
  animation:mIn .25s ease;
}
@keyframes mIn { from{opacity:0;transform:scale(.94);} to{opacity:1;transform:scale(1);} }
.modal-head {
  display:flex; align-items:flex-start; justify-content:space-between;
  padding:22px 24px; border-bottom:1px solid var(--bd); gap:12px;
  position:sticky; top:0; background:var(--s1); z-index:2;
}
.modal-title { font-family:var(--fm); font-size:18px; font-weight:800; line-height:1.3; }
.modal-close {
  width:32px; height:32px; border-radius:9px; border:1px solid var(--bd);
  background:rgba(255,255,255,0.04); display:flex; align-items:center;
  justify-content:center; cursor:pointer; font-size:16px; color:var(--tx-3); transition:var(--e);
}
.modal-close:hover { background:rgba(255,77,106,0.12); color:var(--red); }
.modal-body { padding:22px 24px; }
.mb-section { margin-bottom:20px; }
.mb-label { font-size:10.5px; font-weight:800; color:var(--tx-3); text-transform:uppercase; letter-spacing:.8px; margin-bottom:8px; }
.mb-text { font-size:13.5px; color:var(--tx-2); line-height:1.75; }
.modal-footer { padding:16px 24px; border-top:1px solid var(--bd); display:flex; gap:10px; flex-wrap:wrap; }

/* ══ TOAST ══ */
#toast-c { position:fixed; bottom:22px; right:22px; z-index:9999; display:flex; flex-direction:column; gap:9px; }
.toast { display:flex; align-items:center; gap:11px; background:var(--s2); border:1px solid var(--bd); padding:13px 16px; border-radius:var(--rs); max-width:330px; min-width:240px; box-shadow:0 12px 36px rgba(0,0,0,.5); animation:tIn .35s ease; backdrop-filter:blur(14px); }
.toast.success { border-left:3px solid var(--green); }
.toast.error   { border-left:3px solid var(--red); }
.toast.info    { border-left:3px solid var(--cyan); }
.toast.warning { border-left:3px solid var(--amber); }
.t-ico { font-size:17px; flex-shrink:0; } .t-bod { flex:1; }
.t-ttl { font-family:var(--fm); font-weight:700; font-size:12.5px; margin-bottom:1px; }
.t-msg { font-size:11.5px; color:var(--tx-3); }
.t-cls { cursor:pointer; color:var(--tx-3); font-size:17px; flex-shrink:0; }
@keyframes tIn { from{opacity:0;transform:translateX(50px);} to{opacity:1;transform:translateX(0);} }

/* ══ FOOTER (index.php exact) ══ */
.footer-wrap { background:var(--s1); border-top:1px solid var(--bd); padding:52px 5% 0; }
.footer-top { display:grid; grid-template-columns:1.8fr 1fr 1fr 1fr; gap:36px; max-width:1160px; margin:0 auto; padding-bottom:44px; }
.footer-brand p { color:var(--tx-3); font-size:13px; line-height:1.7; margin-top:13px; max-width:230px; }
.footer-ttl { font-family:var(--fm); font-weight:700; font-size:13px; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:14px; color:var(--tx-2); }
.footer-links { list-style:none; display:flex; flex-direction:column; gap:9px; }
.footer-links a { color:var(--tx-3); font-size:13px; transition:var(--e); }
.footer-links a:hover { color:var(--cyan); }
.nl-form { display:flex; gap:6px; margin-top:9px; }
.nl-input { flex:1; background:var(--s2); border:1px solid var(--bd); border-radius:var(--rs); padding:8px 12px; font-size:13px; font-family:var(--fb); color:var(--tx); outline:none; min-width:0; }
.nl-input:focus { border-color:var(--cyan-border); }
.nl-input::placeholder { color:var(--tx-3); }
.footer-bar { max-width:1160px; margin:0 auto; padding:20px 0; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; border-top:1px solid var(--bd); }
.footer-copy { color:var(--tx-3); font-size:12px; }
.footer-socials { display:flex; gap:8px; }
.soc-btn { width:34px; height:34px; border-radius:9px; background:rgba(255,255,255,0.03); border:1px solid var(--bd); display:flex; align-items:center; justify-content:center; color:var(--tx-3); font-size:13.5px; cursor:pointer; transition:var(--e); font-weight:700; }
.soc-btn:hover { background:var(--coral-dim); color:var(--coral); border-color:var(--coral-border); }
.footer-badges { display:flex; gap:8px; flex-wrap:wrap; margin-top:14px; }
.f-badge { display:flex; align-items:center; gap:5px; background:rgba(255,255,255,0.02); border:1px solid var(--bd); border-radius:8px; padding:5px 10px; font-size:11px; color:var(--tx-3); }

/* ══ BACK TO TOP ══ */
.back-top { position:fixed; bottom:22px; left:22px; z-index:990; width:38px; height:38px; border-radius:10px; background:var(--s2); border:1px solid var(--bd); display:flex; align-items:center; justify-content:center; color:var(--tx-2); font-size:16px; cursor:pointer; transition:var(--e); opacity:0; pointer-events:none; }
.back-top.show { opacity:1; pointer-events:auto; }
.back-top:hover { background:var(--cyan-dim); color:var(--cyan); border-color:var(--cyan-border); }

/* ══ RESPONSIVE ══ */
@media(max-width:1100px) { .page-body { grid-template-columns:210px 1fr; } }
@media(max-width:900px) {
  .page-body { grid-template-columns:1fr; }
  .filter-panel { position:static; display:none; }
  .filter-panel.mobile-open { display:block; }
}
@media(max-width:768px) {
  .nav-links, .nav-acts { display:none; } .ham { display:flex; }
  .page-wrap { padding:20px 4% 60px; }
  .jobs-grid { grid-template-columns:1fr; }
  .footer-top { grid-template-columns:1fr; }
  #toast-c { bottom:16px; right:16px; }
}
@media(max-width:480px) { .sb-row { flex-direction:column; } .sb-filters { flex-direction:column; } }
</style>
</head>
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<!-- ════════════════════════════════════════
     NAVBAR — index.php exact
════════════════════════════════════════ -->
<nav class="navbar" id="nav">
  <a href="<?= APP_URL ?>/index.php" class="logo">
    <div class="logo-mark">G</div>
    <span class="logo-text">Gig<span>Ghana</span></span>
  </a>
  <div class="nav-links">
    <a href="<?= APP_URL ?>/search/providers.php">Find Talent</a>
    <a href="<?= APP_URL ?>/jobs.php" class="active">Browse Jobs</a>
    <a href="<?= APP_URL ?>/index.php#how">How It Works</a>
    <a href="<?= APP_URL ?>/index.php#categories">Categories</a>
    <a href="<?= APP_URL ?>/index.php#trending">Trending</a>
  </div>
  <div class="nav-acts">
    <button onclick="toggleTheme()" class="btn-theme" id="themeBtn" title="Toggle theme">🌙</button>
    <?php if (isLoggedIn() && $user): ?>
      <a href="<?= APP_URL ?>/<?= $user['role'] ?>/dashboard.php" class="btn btn-ghost">Dashboard</a>
      <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-ghost">Sign Out</a>
    <?php else: ?>
      <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-ghost">Sign In</a>
      <a href="<?= APP_URL ?>/auth/register.php" class="btn btn-gold">Get Started Free</a>
    <?php endif; ?>
  </div>
  <div class="ham" id="ham" onclick="toggleMob()"><span></span><span></span><span></span></div>
</nav>

<div class="mobile-nav" id="mobNav">
  <a href="<?= APP_URL ?>/search/providers.php">Find Talent</a>
  <a href="<?= APP_URL ?>/jobs.php">Browse Jobs</a>
  <a href="<?= APP_URL ?>/index.php#how">How It Works</a>
  <a href="<?= APP_URL ?>/index.php#categories">Categories</a>
  <?php if (isLoggedIn() && $user): ?>
    <a href="<?= APP_URL ?>/<?= $user['role'] ?>/dashboard.php">Dashboard</a>
    <a href="<?= APP_URL ?>/auth/logout.php">Sign Out</a>
  <?php else: ?>
    <a href="<?= APP_URL ?>/auth/login.php">Sign In</a>
    <a href="<?= APP_URL ?>/auth/register.php">Get Started Free</a>
  <?php endif; ?>
</div>

<!-- ════════════════════════════════════════
     PAGE HERO
════════════════════════════════════════ -->
<div class="page-hero">
  <div class="hero-inner">
    <div class="hero-breadcrumb">
      <a href="<?= APP_URL ?>/index.php">Home</a>
      <span>›</span>
      <span style="color:var(--tx-2);">Browse Jobs</span>
    </div>
    <h1 class="hero-h1">💼 Browse Freelance Jobs</h1>
    <p class="hero-sub">Find work that matches your skills — from IT and design to trades, health, education and beyond.</p>
    <div class="hero-stats">
      <div class="hstat"><strong><?= number_format($totalJobs) ?></strong> open jobs</div>
      <div class="hstat" style="color:var(--tx-3);">·</div>
      <div class="hstat">🇬🇭 Posted across Ghana</div>
      <div class="hstat" style="color:var(--tx-3);">·</div>
      <div class="hstat">Updated in real time</div>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════
     SEARCH + FILTERS + RESULTS
════════════════════════════════════════ -->
<div class="page-wrap">

  <!-- Search card -->
  <form method="GET" id="filterForm">
    <div class="search-card">
      <div class="sb-row">
        <div class="sb-main">
          <div class="sb-label">Search Jobs</div>
          <input type="text" name="q" id="searchInput" class="sb-input"
                 placeholder="Search by title, skill or keyword…"
                 value="<?= htmlspecialchars($q) ?>">
        </div>
        <div style="min-width:180px;">
          <div class="sb-label">Category</div>
          <select name="category" class="sb-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-blue">🔍 Search</button>
      </div>
      <div class="sb-filters">
        <div class="sb-filter-item">
          <div class="sb-label">Experience</div>
          <select name="experience" class="sb-select" onchange="this.form.submit()">
            <option value="">Any Level</option>
            <option value="entry"        <?= $expLevel==='entry'        ?'selected':'' ?>>Entry</option>
            <option value="intermediate" <?= $expLevel==='intermediate' ?'selected':'' ?>>Intermediate</option>
            <option value="expert"       <?= $expLevel==='expert'       ?'selected':'' ?>>Expert</option>
          </select>
        </div>
        <div class="sb-filter-item">
          <div class="sb-label">Work Type</div>
          <select name="location_type" class="sb-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="remote" <?= $locType==='remote' ?'selected':'' ?>>Remote</option>
            <option value="onsite" <?= $locType==='onsite' ?'selected':'' ?>>On-site</option>
            <option value="hybrid" <?= $locType==='hybrid' ?'selected':'' ?>>Hybrid</option>
          </select>
        </div>
        <div class="sb-filter-item">
          <div class="sb-label">Job Type</div>
          <select name="budget_type" class="sb-select" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="fixed"  <?= $jobType==='fixed'  ?'selected':'' ?>>Fixed Price</option>
            <option value="hourly" <?= $jobType==='hourly' ?'selected':'' ?>>Hourly</option>
          </select>
        </div>
        <div class="sb-filter-item">
          <div class="sb-label">Posted</div>
          <select name="posted" class="sb-select" onchange="this.form.submit()">
            <option value="">Any Time</option>
            <option value="24h"  <?= $posted==='24h'  ?'selected':'' ?>>Last 24 Hours</option>
            <option value="3d"   <?= $posted==='3d'   ?'selected':'' ?>>Last 3 Days</option>
            <option value="week" <?= $posted==='week' ?'selected':'' ?>>Last Week</option>
          </select>
        </div>
        <div>
          <div class="sb-label">Min ₵</div>
          <input type="number" name="budget_min" class="sb-input" style="width:96px;" placeholder="0" value="<?= $budMin ?: '' ?>">
        </div>
        <div>
          <div class="sb-label">Max ₵</div>
          <input type="number" name="budget_max" class="sb-input" style="width:96px;" placeholder="Any" value="<?= $budMax ?: '' ?>">
        </div>
        <?php if ($q||$catId||$expLevel||$locType||$jobType||$posted||$budMin||$budMax): ?>
        <a href="<?= APP_URL ?>/jobs.php" class="btn btn-ghost btn-sm" style="align-self:flex-end;">✕ Clear</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Category pills -->
    <div class="cat-scroll-wrap">
      <?php
      $catIconMap = ['code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈','file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰','briefcase'=>'⚖️','headphones'=>'🎧','camera'=>'📷','globe'=>'🌐','tool'=>'🔧','bar-chart'=>'📊','music'=>'🎵'];
      ?>
      <a href="?<?= http_build_query(array_merge($_GET,['category'=>'','page'=>1])) ?>" class="cat-pill <?= !$catId?'active':'' ?>">🌍 All</a>
      <?php foreach ($categories as $c): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['category'=>$c['id'],'page'=>1])) ?>"
         class="cat-pill <?= $catId===$c['id']?'active':'' ?>">
        <?= $catIconMap[$c['icon']??'briefcase'] ?? '📂' ?> <?= sanitize($c['name']) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </form>

  <!-- Body: filter panel + results -->
  <div class="page-body">

    <!-- LEFT: Filter sidebar -->
    <div>
      <div class="filter-panel" id="filterPanel">
        <div class="fp-title">
          ⚙️ Filters
          <?php if ($q||$catId||$expLevel||$locType||$jobType||$posted||$budMin||$budMax): ?>
          <a href="<?= APP_URL ?>/jobs.php" style="font-size:11px;color:var(--cyan);font-weight:600;">Clear all</a>
          <?php endif; ?>
        </div>

        <div class="fp-section">
          <div class="fp-label">Budget Range</div>
          <?php
          $budPresets = [
              ['Under ₵200', 0, 200], ['₵200 – ₵500', 200, 500],
              ['₵500 – ₵1,000', 500, 1000], ['₵1,000+', 1000, 0],
          ];
          foreach ($budPresets as [$lbl, $mn, $mx]):
              $isAct = ($budMin == $mn && $budMax == $mx);
          ?>
          <a href="?<?= http_build_query(array_merge($_GET,['budget_min'=>$mn,'budget_max'=>$mx,'page'=>1])) ?>"
             class="fp-option <?= $isAct?'active':'' ?>">
            <div class="fp-check"></div><?= $lbl ?>
          </a>
          <?php endforeach; ?>
          <div style="margin-top:10px;">
            <div class="fp-label" style="margin-bottom:6px;">Custom (₵)</div>
            <form method="GET">
              <?php foreach ($_GET as $k=>$v): if (!in_array($k,['budget_min','budget_max'])): ?><input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>"><?php endif; endforeach; ?>
              <div class="fp-budget-inputs">
                <input type="number" name="budget_min" placeholder="Min" value="<?= $budMin ?: '' ?>">
                <input type="number" name="budget_max" placeholder="Max" value="<?= $budMax ?: '' ?>">
              </div>
              <button type="submit" class="btn btn-blue btn-sm" style="width:100%;justify-content:center;margin-top:8px;">Apply</button>
            </form>
          </div>
        </div>

        <div class="fp-section">
          <div class="fp-label">Work Location</div>
          <?php foreach ([''=>'All Locations','remote'=>'🌐 Remote','onsite'=>'🏢 On-site','hybrid'=>'🔀 Hybrid'] as $v=>$l): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['location_type'=>$v,'page'=>1])) ?>"
             class="fp-option <?= $locType===$v?'active':'' ?>">
            <div class="fp-check"></div><?= $l ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="fp-section">
          <div class="fp-label">Experience Level</div>
          <?php foreach ([''=>'Any Level','entry'=>'🌱 Beginner','intermediate'=>'📈 Intermediate','expert'=>'🏆 Expert'] as $v=>$l): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['experience'=>$v,'page'=>1])) ?>"
             class="fp-option <?= $expLevel===$v?'active':'' ?>">
            <div class="fp-check"></div><?= $l ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="fp-section">
          <div class="fp-label">Job Type</div>
          <?php foreach ([''=>'All','fixed'=>'📌 Fixed Price','hourly'=>'⏱ Hourly Rate'] as $v=>$l): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['budget_type'=>$v,'page'=>1])) ?>"
             class="fp-option <?= $jobType===$v?'active':'' ?>">
            <div class="fp-check"></div><?= $l ?>
          </a>
          <?php endforeach; ?>
        </div>

        <div class="fp-section">
          <div class="fp-label">Date Posted</div>
          <?php foreach ([''=>'Any Time','24h'=>'⚡ Last 24 Hours','3d'=>'📅 Last 3 Days','week'=>'🗓 Last Week'] as $v=>$l): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['posted'=>$v,'page'=>1])) ?>"
             class="fp-option <?= $posted===$v?'active':'' ?>">
            <div class="fp-check"></div><?= $l ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT: Results -->
    <div>
      <!-- Results header -->
      <div class="results-header">
        <div class="results-count">
          Showing <strong><?= number_format($totalJobs) ?></strong> open job<?= $totalJobs!=1?'s':'' ?>
          <?php if ($q): ?> for "<strong><?= htmlspecialchars($q) ?></strong>"<?php endif; ?>
          <?php if ($catId && $categories): foreach ($categories as $c) { if ((int)$c['id']===$catId) echo ' in <strong>'.sanitize($c['name']).'</strong>'; } endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          <button class="btn btn-ghost btn-sm" id="filterToggleBtn" onclick="toggleFilter()" style="display:none;">⚙️ Filters</button>
          <select class="sort-select" onchange="window.location='?<?= http_build_query(array_merge($_GET,['sort'=>'__S__'])) ?>'.replace('__S__',this.value)">
            <option value="newest"      <?= $sort==='newest'     ?'selected':'' ?>>🕒 Newest First</option>
            <option value="budget_high" <?= $sort==='budget_high'?'selected':'' ?>>₵ Budget: High → Low</option>
            <option value="budget_low"  <?= $sort==='budget_low' ?'selected':'' ?>>₵ Budget: Low → High</option>
            <option value="proposals"   <?= $sort==='proposals'  ?'selected':'' ?>>📩 Fewest Proposals</option>
          </select>
        </div>
      </div>

      <!-- Cards -->
      <?php if (empty($jobs)): ?>
      <div class="empty-state">
        <div class="es-icon">🔍</div>
        <div class="es-title">No jobs found</div>
        <p class="es-text">Try adjusting your filters or search terms to find more opportunities.</p>
        <a href="<?= APP_URL ?>/jobs.php" class="btn btn-blue">Clear Filters</a>
      </div>

      <?php else: ?>
      <div class="jobs-grid">
        <?php foreach ($jobs as $idx => $j):
          $propCnt  = (int)$j['proposal_count'];
          $pColor   = propColor($propCnt);
          $pLabel   = propLabel($propCnt);
          $hireRate = (int)($j['client_total_jobs']??0) > 0
            ? round($j['client_hired'] / $j['client_total_jobs'] * 100) : 0;
          $catIco   = $iconMap[$j['cat_icon']??'briefcase'] ?? '📋';
          $skills   = $jobSkills[$j['id']] ?? [];
          $delay    = ($idx % 6) * 40;
        ?>
        <div class="job-card <?= $j['already_applied']?'applied-card':'' ?> <?= $j['is_featured']?'featured-card':'' ?>"
             style="animation-delay:<?= $delay ?>ms"
             data-job-id="<?= $j['id'] ?>"
             data-title="<?= htmlspecialchars($j['title']) ?>"
             data-desc="<?= htmlspecialchars($j['description']) ?>"
             data-req="<?= htmlspecialchars($j['requirements']??'') ?>"
             data-budget-min="<?= $j['budget_min'] ?>"
             data-budget-max="<?= $j['budget_max'] ?>"
             data-budget-type="<?= $j['budget_type'] ?>"
             data-duration="<?= $j['duration'] ?>"
             data-exp="<?= $j['experience_level'] ?>"
             data-loc="<?= $j['location_type'] ?>"
             data-location="<?= htmlspecialchars($j['location']??'') ?>">

          <!-- Top row -->
          <div class="jc-top">
            <div class="jc-client">
              <div class="client-ava">
                <?php if (!empty($j['client_avatar'])): ?><img src="<?= sanitize($j['client_avatar']) ?>" alt="" loading="lazy"><?php else: echo strtoupper(substr($j['first_name'],0,1)); endif; ?>
              </div>
              <div>
                <div class="client-name"><?= sanitize($j['first_name'].' '.$j['last_name']) ?></div>
                <div class="client-time"><?= timeAgo($j['created_at']) ?></div>
                <div class="trust-row">
                  <?php if ($j['phone_verified']):   ?><span class="t-badge tb-phone">📱 Phone</span><?php endif; ?>
                  <?php if ($j['payment_verified']): ?><span class="t-badge tb-payment">💳 Payment</span><?php endif; ?>
                </div>
              </div>
            </div>
            <div class="jc-badges">
              <?php if ($j['is_urgent']):       ?><span class="jbadge jb-urgent">🔥 Urgent</span><?php endif; ?>
              <?php if ($j['is_featured']):     ?><span class="jbadge jb-feat">⭐ Featured</span><?php endif; ?>
              <?php if ($j['already_applied']): ?><span class="jbadge jb-applied">✓ Applied</span><?php endif; ?>
              <?php if ($j['cat_name'] && !$j['is_urgent'] && !$j['is_featured']): ?><span class="jbadge jb-open">● Open</span><?php endif; ?>
              <button class="save-btn <?= $j['is_saved']?'saved':'' ?>"
                      onclick="toggleSave(this,<?= $j['id'] ?>)"
                      title="<?= $j['is_saved']?'Unsave':'Save' ?>">
                <?= $j['is_saved'] ? '🔖' : '📌' ?>
              </button>
            </div>
          </div>

          <!-- Title -->
          <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" class="job-title">
            <?= sanitize($j['title']) ?>
          </a>

          <!-- Description -->
          <p class="jc-desc"><?= sanitize($j['description']) ?></p>

          <!-- Meta -->
          <div class="jc-meta">
            <?php if ($j['cat_name']): ?><span><?= $catIco ?> <?= sanitize($j['cat_name']) ?></span><?php endif; ?>
            <span>🌍 <?= ucfirst(str_replace('_',' ',$j['location_type'])) ?></span>
            <?php if ($j['location'] && $j['location_type']!=='remote'): ?><span>📍 <?= sanitize($j['location']) ?></span><?php endif; ?>
            <span>🎯 <?= ucfirst($j['experience_level']) ?></span>
            <span>📋 <?= ucfirst(str_replace('_',' ',$j['budget_type'])) ?></span>
          </div>

          <!-- Skills -->
          <?php if ($skills): ?>
          <div class="jc-skills">
            <?php foreach (array_slice($skills,0,3) as $sk): ?><span class="skill-tag"><?= sanitize($sk) ?></span><?php endforeach; ?>
            <?php if (count($skills)>3): ?><span class="skill-more">+<?= count($skills)-3 ?> more</span><?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Client trust -->
          <div class="client-trust">
            <div class="ct-item">
              <div class="ct-val" style="color:var(--amber);"><?= number_format((float)($j['rating_avg']??0),1) ?></div>
              <div class="ct-lbl">⭐ Rating</div>
            </div>
            <div class="ct-item">
              <div class="ct-val"><?= (int)($j['client_total_jobs']??0) ?></div>
              <div class="ct-lbl">Jobs Posted</div>
            </div>
            <div class="ct-item">
              <div class="ct-val" style="color:var(--green);"><?= $hireRate ?>%</div>
              <div class="ct-lbl">Hire Rate</div>
            </div>
            <div class="ct-item">
              <div class="ct-val" style="color:var(--cyan);"><?= (int)($j['client_jobs_done']??0) ?></div>
              <div class="ct-lbl">Completed</div>
            </div>
          </div>

          <!-- Proposal competition -->
          <div class="prop-competition">
            <div class="pc-dot" style="background:<?= $pColor ?>;box-shadow:0 0 6px <?= $pColor ?>44;"></div>
            <div>
              <div class="pc-count" style="color:<?= $pColor ?>;"><?= $propCnt ?> proposal<?= $propCnt!=1?'s':'' ?></div>
              <div class="pc-label"><?= $pLabel ?></div>
            </div>
          </div>

          <!-- Footer -->
          <div class="jc-footer">
            <div>
              <div class="jc-budget">
                <?= formatCurrency($j['budget_min']) ?>
                <?= $j['budget_max'] > $j['budget_min'] ? ' – '.formatCurrency($j['budget_max']) : '' ?>
                <?= $j['budget_type']==='hourly' ? '<small style="font-size:11px;font-weight:400;color:var(--tx-3);">/hr</small>' : '' ?>
              </div>
              <div class="jc-exp"><?= ucfirst($j['experience_level']) ?> level</div>
            </div>
            <div class="jc-actions">
              <button class="details-btn" onclick="openModal(this.closest('.job-card'))">👁 Details</button>
              <?php if ($j['already_applied']): ?>
              <span class="apply-btn sent">✓ Sent</span>
              <?php elseif (!isLoggedIn()): ?>
              <a href="<?= APP_URL ?>/auth/login.php" class="apply-btn">Apply →</a>
              <?php elseif ($user && $user['role']==='client'): ?>
              <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" class="apply-btn">View →</a>
              <?php elseif ($subTier==='free' && $remaining <= 0): ?>
              <a href="<?= APP_URL ?>/provider/upgrade.php" class="apply-btn" style="background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;">🔒 Upgrade</a>
              <?php else: ?>
              <a href="<?= APP_URL ?>/provider/submit-proposal.php?job_id=<?= $j['id'] ?>" class="apply-btn">Apply →</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1):
        $qBase = http_build_query(array_filter(array_merge($_GET, ['page'=>''])));
      ?>
      <div class="pagination">
        <?php if ($page > 1): ?><a href="?<?= $qBase ?>&page=<?= $page-1 ?>" class="pag-btn">← Prev</a><?php endif; ?>
        <?php for ($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
        <a href="?<?= $qBase ?>&page=<?= $i ?>" class="pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="?<?= $qBase ?>&page=<?= $page+1 ?>" class="pag-btn">Next →</a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════
     FOOTER — index.php exact
════════════════════════════════════════ -->
<footer class="footer-wrap">
  <div class="footer-top">
    <div>
      <a href="<?= APP_URL ?>/index.php" class="logo">
        <div class="logo-mark">G</div>
        <span class="logo-text">Gig<span>Ghana</span></span>
      </a>
      <p class="footer-brand">Africa's premier freelance marketplace connecting every Ghanaian talent — from IT and design to trades, health and education — with forward-thinking businesses.</p>
      <div style="margin-top:18px;">
        <div style="font-size:12.5px;color:var(--tx-2);font-weight:600;font-family:var(--fm);">Stay in the Loop</div>
        <div class="nl-form">
          <input class="nl-input" type="email" placeholder="your@email.com" id="nlEmail">
          <button class="btn btn-gold" style="padding:8px 16px;font-size:12px;" onclick="subscribeNL()">Subscribe</button>
        </div>
      </div>
      <div class="footer-badges">
        <div class="f-badge">🔒 SSL Secured</div>
        <div class="f-badge">🇬🇭 Ghana Registered</div>
        <div class="f-badge">✓ Escrow Protected</div>
        <div class="f-badge">🌍 Africa-wide</div>
      </div>
    </div>
    <div>
      <div class="footer-ttl">Platform</div>
      <ul class="footer-links">
        <li><a href="<?= APP_URL ?>/search/providers.php">Find Talent</a></li>
        <li><a href="<?= APP_URL ?>/jobs.php">Browse Jobs</a></li>
        <li><a href="<?= APP_URL ?>/auth/register.php">Post a Job</a></li>
        <li><a href="#">Enterprise</a></li>
        <li><a href="#">Pricing</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-ttl">Company</div>
      <ul class="footer-links">
        <li><a href="#">About Us</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Careers</a></li>
        <li><a href="#">Press</a></li>
        <li><a href="#">Partners</a></li>
      </ul>
    </div>
    <div>
      <div class="footer-ttl">Support</div>
      <ul class="footer-links">
        <li><a href="#">Help Centre</a></li>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Service</a></li>
        <li><a href="#">Contact Us</a></li>
        <li><a href="#">Dispute Resolution</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bar">
    <span class="footer-copy">© <?= date('Y') ?> GigGhana Ltd. Made with ❤️ in Ghana 🇬🇭</span>
    <div class="footer-socials">
      <a class="soc-btn" href="#" title="Twitter / X">𝕏</a>
      <a class="soc-btn" href="#" title="LinkedIn">in</a>
      <a class="soc-btn" href="#" title="Instagram">ig</a>
      <a class="soc-btn" href="#" title="Facebook">fb</a>
      <a class="soc-btn" href="#" title="TikTok" style="font-size:11px;">TT</a>
    </div>
  </div>
</footer>

<button class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>

<!-- JOB DETAIL MODAL -->
<div class="modal-overlay" id="jobModal" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="modalTitle">Job Details</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
    <div class="modal-footer" id="modalFooter"></div>
  </div>
</div>

<div id="toast-c"></div>

<script>
/* ── THEME ── */
function toggleTheme(){
  const l = document.body.classList.toggle('lm');
  const v = l ? 'light' : 'dark';
  localStorage.setItem('gg_theme', v);
  document.cookie = `gg_theme=${v};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = l ? '☀️' : '🌙';
}
(function(){
  const s = localStorage.getItem('gg_theme') || '<?= $isLight?"light":"dark" ?>';
  const b = document.body, btn = document.getElementById('themeBtn');
  if(s==='light'){b.classList.add('lm');if(btn)btn.textContent='☀️';}
  else{b.classList.remove('lm');if(btn)btn.textContent='🌙';}
})();

/* ── NAVBAR SCROLL ── */
window.addEventListener('scroll',()=>{
  document.getElementById('nav').classList.toggle('on',window.scrollY>40);
  const bt=document.getElementById('backTop');if(bt)bt.classList.toggle('show',window.scrollY>400);
});

/* ── MOBILE NAV ── */
function toggleMob(){
  const m=document.getElementById('mobNav'),h=document.getElementById('ham');
  m.classList.toggle('open');
  const sp=h.querySelectorAll('span');
  if(m.classList.contains('open')){sp[0].style.transform='rotate(45deg) translate(5px,5px)';sp[1].style.opacity='0';sp[2].style.transform='rotate(-45deg) translate(5px,-5px)';}
  else{sp.forEach(s=>{s.style.transform='';s.style.opacity='';});}
}

/* ── FILTER TOGGLE (mobile) ── */
function toggleFilter(){
  document.getElementById('filterPanel').classList.toggle('mobile-open');
}
(function(){
  const btn=document.getElementById('filterToggleBtn');
  if(window.innerWidth<=900)btn.style.display='flex';
  window.addEventListener('resize',()=>{btn.style.display=window.innerWidth<=900?'flex':'none';});
})();

/* ── LIVE SEARCH ── */
let st;
document.getElementById('searchInput')?.addEventListener('input',function(){
  clearTimeout(st);
  st=setTimeout(()=>document.getElementById('filterForm').submit(),600);
});

/* ── SAVE / UNSAVE ── */
function toggleSave(btn,jobId){
  if(!<?= isLoggedIn()?'true':'false' ?>){showToast('Sign in','Please sign in to save jobs.','info');return;}
  fetch('<?= APP_URL ?>/api/jobs.php',{
    method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=toggle_save&job_id=${jobId}&csrf=<?= $csrf ?>`
  }).then(r=>r.json()).then(d=>{
    if(d.saved){btn.classList.add('saved');btn.textContent='🔖';showToast('Saved!','Job added to saved list.','success');}
    else{btn.classList.remove('saved');btn.textContent='📌';showToast('Removed','Job removed from saved.','info');}
  }).catch(()=>showToast('Error','Could not save job.','error'));
}

/* ── JOB MODAL ── */
function openModal(card){
  const jId  = card.dataset.jobId;
  const budMin = parseFloat(card.dataset.budgetMin)||0;
  const budMax = parseFloat(card.dataset.budgetMax)||0;
  const budType= card.dataset.budgetType;
  const fmt  = n=>'₵'+parseFloat(n).toLocaleString('en-GH',{minimumFractionDigits:2});
  const budget = budMax>budMin?`${fmt(budMin)} – ${fmt(budMax)}`:fmt(budMin);
  const applied = card.classList.contains('applied-card');

  document.getElementById('modalTitle').textContent = card.dataset.title;
  document.getElementById('modalBody').innerHTML = `
    <div class="mb-section">
      <div class="mb-label">Budget</div>
      <div style="font-family:var(--fm);font-size:22px;font-weight:800;color:var(--cyan);">${budget}${budType==='hourly'?'/hr':''}</div>
    </div>
    <div class="mb-section" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
      <div style="background:rgba(0,0,0,.15);border:1px solid var(--bd);border-radius:10px;padding:12px;text-align:center;">
        <div style="font-family:var(--fm);font-weight:800;">${budType==='hourly'?'Hourly':'Fixed'}</div>
        <div style="font-size:11px;color:var(--tx-3);">Type</div>
      </div>
      <div style="background:rgba(0,0,0,.15);border:1px solid var(--bd);border-radius:10px;padding:12px;text-align:center;">
        <div style="font-family:var(--fm);font-weight:800;">${card.dataset.exp}</div>
        <div style="font-size:11px;color:var(--tx-3);">Level</div>
      </div>
      <div style="background:rgba(0,0,0,.15);border:1px solid var(--bd);border-radius:10px;padding:12px;text-align:center;">
        <div style="font-family:var(--fm);font-weight:800;">${card.dataset.loc}${card.dataset.location?' · '+card.dataset.location:''}</div>
        <div style="font-size:11px;color:var(--tx-3);">Location</div>
      </div>
    </div>
    <div class="mb-section" style="margin-top:16px;">
      <div class="mb-label">Description</div>
      <div class="mb-text">${card.dataset.desc.replace(/\n/g,'<br>')}</div>
    </div>
    ${card.dataset.req?`<div class="mb-section"><div class="mb-label">Requirements</div><div class="mb-text">${card.dataset.req.replace(/\n/g,'<br>')}</div></div>`:''}
    ${card.dataset.duration?`<div class="mb-section"><div class="mb-label">Duration</div><div class="mb-text">${card.dataset.duration.replace(/_/g,' ')}</div></div>`:''}
  `;
  document.getElementById('modalFooter').innerHTML = `
    <a href="<?= APP_URL ?>/job-details.php?id=${jId}" class="btn btn-ghost" style="flex:1;justify-content:center;">Full Details ↗</a>
    ${applied
      ?'<span class="btn" style="flex:1;justify-content:center;background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,.2);">✓ Proposal Sent</span>'
      :`<a href="<?= APP_URL ?>/provider/submit-proposal.php?job_id=${jId}" class="btn btn-blue" style="flex:1;justify-content:center;">Apply Now →</a>`
    }
  `;
  document.getElementById('jobModal').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeModal(){
  document.getElementById('jobModal').classList.remove('open');
  document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeModal();});

/* ── TOAST ── */
const TI={success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
function showToast(title,msg,type='info',d=3800){
  const c=document.getElementById('toast-c');
  const t=document.createElement('div');t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${TI[type]}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(50px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),330);},d);
}

/* ── NEWSLETTER ── */
function subscribeNL(){
  const em=document.getElementById('nlEmail');
  if(!em||!em.value.includes('@')){showToast('Oops!','Please enter a valid email address.','error');return;}
  showToast('Subscribed! 🇬🇭','Thank you for joining GigGhana updates.','success');
  em.value='';
}

/* URL param toasts */
<?php if (isset($_GET['success'])): ?>showToast('Success','<?= addslashes(sanitize($_GET['success'])) ?>','success');<?php endif; ?>
<?php if (isset($_GET['error'])):   ?>showToast('Error',  '<?= addslashes(sanitize($_GET['error']))   ?>','error');<?php endif; ?>
</script>
</body>
</html>