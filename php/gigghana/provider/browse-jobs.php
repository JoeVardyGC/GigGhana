<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('provider');

$userId = $_SESSION['user_id'];

// Filters
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

/* Theme from cookie */
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';

try {
    $db = getDB();
    $categories = getCategories();

    /* ── Provider record ── */
    $stProv = $db->prepare("SELECT * FROM providers WHERE user_id=? LIMIT 1");
    $stProv->execute([$userId]);
    $prov = $stProv->fetch();
    if (!$prov) {
        $db->prepare("INSERT IGNORE INTO providers (user_id) VALUES (?)")->execute([$userId]);
        $stProv->execute([$userId]);
        $prov = $stProv->fetch();
    }
    $providerId = (int)($prov['id'] ?? 0);

    /* Subscription info */
    $subTier   = $prov['subscription_tier'] ?? 'free';
    $propUsed  = (int)($prov['proposals_used'] ?? 0);
    $propLimit = ['free'=>3,'verified'=>999,'premium'=>999][$subTier] ?? 3;
    $remaining = max(0, $propLimit - $propUsed);

    /* ── Client trust stats sub-query ── */
    $trustSub = "(SELECT COUNT(*) FROM jobs jx WHERE jx.client_id=u.id AND jx.status='completed') AS client_jobs_done,
                 (SELECT COUNT(*) FROM jobs jy WHERE jy.client_id=u.id AND jy.hired_provider_id IS NOT NULL) AS client_hired,
                 (SELECT COUNT(*) FROM jobs jz WHERE jz.client_id=u.id) AS client_total_jobs";

    /* ── Build WHERE ── */
    $where  = ["j.status = 'open'"];
    $params = [];

    if ($q) {
        $where[]  = "(j.title LIKE ? OR j.description LIKE ? OR j.requirements LIKE ?)";
        $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
    }
    if ($catId)    { $where[] = "j.category_id = ?";   $params[] = $catId; }
    if ($budMin)   { $where[] = "j.budget_max >= ?";    $params[] = $budMin; }
    if ($budMax)   { $where[] = "j.budget_min <= ?";    $params[] = $budMax; }
    if ($expLevel) { $where[] = "(j.experience_level = ? OR j.experience_level = 'any')"; $params[] = $expLevel; }
    if ($locType)  { $where[] = "j.location_type = ?";  $params[] = $locType; }
    if ($jobType)  { $where[] = "j.budget_type = ?";    $params[] = $jobType; }
    if ($posted === '24h')   { $where[] = "j.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)"; }
    if ($posted === '3d')    { $where[] = "j.created_at >= DATE_SUB(NOW(), INTERVAL 3 DAY)"; }
    if ($posted === 'week')  { $where[] = "j.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; }

    $whereStr = implode(' AND ', $where);
    $orderBy  = match($sort) {
        'budget_high' => 'j.budget_max DESC',
        'budget_low'  => 'j.budget_min ASC',
        'proposals'   => 'j.proposal_count ASC',
        default       => 'j.is_urgent DESC, j.is_featured DESC, j.created_at DESC'
    };

    /* Total count */
    $totalStmt = $db->prepare("SELECT COUNT(*) FROM jobs j WHERE $whereStr");
    $totalStmt->execute($params);
    $totalJobs  = (int)$totalStmt->fetchColumn();
    $totalPages = (int)ceil($totalJobs / $perPage);

    /* Main job query */
    $stmt = $db->prepare(
        "SELECT j.*,
                c.name AS cat_name, c.icon AS cat_icon,
                u.first_name, u.last_name, u.avatar AS client_avatar,
                u.phone_verified, u.payment_verified,
                $trustSub,
                (SELECT 1 FROM proposals  WHERE job_id=j.id AND provider_id=?  LIMIT 1) AS already_applied,
                (SELECT 1 FROM saved_jobs WHERE job_id=j.id AND user_id=?       LIMIT 1) AS is_saved
         FROM jobs j
         LEFT JOIN categories c ON c.id = j.category_id
         JOIN  users u           ON u.id = j.client_id
         WHERE $whereStr
         ORDER BY $orderBy
         LIMIT $perPage OFFSET $offset"
    );
    $stmt->execute(array_merge([$providerId, $userId], $params));
    $jobs = $stmt->fetchAll();

    /* Recommended jobs (skill-matched, not already applied) */
    $stRec = $db->prepare(
        "SELECT j.id, j.title, j.budget_min, j.budget_max, j.budget_type,
                j.proposal_count, j.created_at, c.name AS cat_name
         FROM jobs j
         LEFT JOIN categories c ON c.id=j.category_id
         WHERE j.status='open'
           AND j.id NOT IN (SELECT job_id FROM proposals WHERE provider_id=?)
         ORDER BY j.created_at DESC LIMIT 4"
    );
    $stRec->execute([$providerId]);
    $recommended = $stRec->fetchAll();

} catch(Exception $e) {
    error_log($e->getMessage());
    $jobs=[]; $totalJobs=0; $totalPages=0; $categories=[];
    $recommended=[]; $subTier='free'; $propUsed=0; $propLimit=3; $remaining=3;
}

$iconMap = [
    'code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
    'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
    'briefcase'=>'⚖️','headphones'=>'🎧','camera'=>'📷','globe'=>'🌐',
    'tool'=>'🔧','bar-chart'=>'📊','music'=>'🎵',
];
$csrf = generateCSRF();

/* Proposal competition colour */
function propColor(int $n): string {
    if ($n <= 5)  return '#1FD9A0'; // green — low
    if ($n <= 15) return '#F7B731'; // amber — medium
    return '#FF4D6A';               // red — high
}
function propLabel(int $n): string {
    if ($n <= 5)  return 'Low competition';
    if ($n <= 15) return 'Medium';
    return 'High competition';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Browse Jobs — GigGhana</title>
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
  --cyan:#00D4C8;   --cyan-d:#00A89F;  --cyan-l:#4DFFE8;
  --cyan-dim:rgba(0,212,200,0.10);     --cyan-border:rgba(0,212,200,0.22);
  --coral:#FF6B4A;  --coral-d:#E04D2E;
  --coral-dim:rgba(255,107,74,0.10);   --coral-border:rgba(255,107,74,0.25);
  --violet:#7C6FF7; --violet-d:#5D52E0;
  --violet-dim:rgba(124,111,247,0.10); --violet-border:rgba(124,111,247,0.22);
  --green:#1FD9A0;  --green-d:#13B882; --green-dim:rgba(31,217,160,0.10);
  --amber:#F7B731;  --red:#FF4D6A;
  --tx:#F2F4F8; --tx-2:#9BA8BF; --tx-3:#4E5A6E;
  --bd:rgba(255,255,255,0.065); --bd2:rgba(255,255,255,0.12);
  --gC:rgba(0,212,200,0.16); --gO:rgba(255,107,74,0.14);
  --fm:'Plus Jakarta Sans',sans-serif;
  --fb:'DM Sans',sans-serif;
  --sb:256px; --r:16px; --rs:10px;
  --e:all 0.26s cubic-bezier(.4,0,.2,1);
}
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
.lm .sidebar{background:var(--s1);border-right-color:var(--bd);}
.lm .topbar{background:rgba(243,245,250,0.96);}
.lm .filter-panel,.lm .search-bar,.lm .job-card,.lm .section-card,.lm .cat-pill,.lm .rec-card{background:rgba(255,255,255,0.9);}
.lm .sb-input,.lm .sb-select,.lm .sort-select{background:rgba(0,0,0,0.05);color:var(--tx);}
.lm .btn-ghost{background:rgba(0,0,0,0.05);border-color:var(--bd2);color:var(--tx-2);}
.lm .pag-btn{background:rgba(0,0,0,0.05);}
.lm .mobile-nav{background:rgba(243,245,250,0.98);}
.lm .sb-item{color:var(--tx-3);}
.lm .sb-item:hover{background:rgba(0,0,0,0.05);color:var(--tx);}
.lm .sb-item.active{background:var(--cyan-dim);color:var(--cyan);}
.lm .sub-warn{background:rgba(255,255,255,0.9);}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);font-family:var(--fb);
  min-height:100vh;display:flex;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
a{text-decoration:none;color:inherit;}
h1,h2,h3,h4,.page-title,.card-title,.job-title{font-family:var(--fm);-webkit-font-smoothing:antialiased;}

/* ══ SIDEBAR ══ */
.sidebar{
  width:var(--sb);min-height:100vh;background:var(--s1);
  border-right:1px solid var(--bd);position:fixed;top:0;left:0;z-index:200;
  display:flex;flex-direction:column;overflow:hidden;transition:background .3s,border-color .3s;
}
.sidebar::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--cyan));
  background-size:200% 100%;animation:gradShift 4s linear infinite;
}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:200% 50%}}
.sb-logo{padding:22px 18px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:9px;text-decoration:none;}
.sb-logo-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));border-radius:9px;display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-weight:800;font-size:15px;color:#0C0E14;flex-shrink:0;}
.sb-logo-text{font-family:var(--fm);font-size:18px;font-weight:800;color:var(--tx);}
.sb-logo-text span{color:var(--cyan);}
.sb-nav{flex:1;padding:10px;overflow-y:auto;scrollbar-width:none;}
.sb-nav::-webkit-scrollbar{display:none;}
.nav-section{font-size:9px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;color:var(--tx-3);padding:6px 12px;margin:14px 0 4px;}
.sb-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;color:var(--tx-3);font-size:13px;font-weight:500;transition:var(--e);}
.sb-item:hover{background:rgba(255,255,255,0.05);color:var(--tx);}
.sb-item.active{background:var(--cyan-dim);color:var(--cyan);border-left:3px solid var(--cyan);padding-left:9px;}
.sb-item.danger{color:var(--red);}
.sb-item.danger:hover{background:rgba(255,77,106,0.08);}
.sb-user{padding:14px 10px;border-top:1px solid var(--bd);}
.sb-user-card{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;background:rgba(0,0,0,0.2);}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:13px;font-weight:800;color:#fff;overflow:hidden;}
.sb-av img{width:100%;height:100%;object-fit:cover;}
.sb-uname{font-size:13px;font-weight:700;}
.sb-urole{font-size:10px;color:var(--cyan);font-weight:700;text-transform:uppercase;margin-top:1px;}

/* ══ MAIN ══ */
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-width:0;}

/* ══ TOPBAR ══ */
.topbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:0 28px;height:64px;
  background:rgba(12,14,20,0.92);backdrop-filter:blur(24px);
  border-bottom:1px solid var(--bd);position:sticky;top:0;z-index:100;
  transition:background .3s,border-color .3s;
}
.page-title{font-size:20px;font-weight:800;display:flex;align-items:center;gap:9px;}
.page-title span{font-size:13px;font-weight:500;color:var(--tx-3);}
.topbar-right{display:flex;align-items:center;gap:8px;}
.theme-btn{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.04);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;transition:var(--e);}
.theme-btn:hover{background:rgba(255,255,255,0.08);}

/* ══ BUTTONS ══ */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--rs);font-family:var(--fb);font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:var(--e);white-space:nowrap;line-height:1.3;}
.btn-ghost{background:rgba(255,255,255,0.04);border:1px solid var(--bd);color:var(--tx-2);}
.btn-ghost:hover{background:rgba(255,255,255,0.08);color:var(--tx);border-color:var(--bd2);}
.btn-cyan{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;font-weight:700;box-shadow:0 3px 14px var(--gC);}
.btn-cyan:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--gC);}
.btn-coral{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:700;box-shadow:0 3px 14px var(--gO);}
.btn-coral:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--gO);}
.btn-violet{background:linear-gradient(135deg,var(--violet),var(--violet-d));color:#fff;font-weight:700;}
.btn-violet:hover{transform:translateY(-2px);}
.btn-sm{padding:5px 12px;font-size:11.5px;border-radius:8px;}
.btn-lg{padding:11px 24px;font-size:14px;border-radius:12px;}

/* ══ CONTENT ══ */
.content{padding:24px 28px 100px;}

/* ══ SUBSCRIPTION WARNING BANNER ══ */
.sub-warn{
  display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;
  background:linear-gradient(135deg,rgba(255,107,74,0.08),rgba(247,183,49,0.06));
  border:1px solid var(--coral-border);border-radius:14px;
  padding:14px 20px;margin-bottom:22px;
}
.sub-warn.yellow{
  background:linear-gradient(135deg,rgba(247,183,49,0.08),rgba(247,183,49,0.04));
  border-color:rgba(247,183,49,0.25);
}
.sw-text{font-size:13.5px;color:var(--tx-2);}
.sw-text strong{color:var(--coral);}
.sub-warn.yellow .sw-text strong{color:var(--amber);}
.sw-bar-wrap{display:flex;align-items:center;gap:9px;flex-shrink:0;}
.sw-bar-track{width:100px;height:6px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;}
.sw-bar-fill{height:100%;border-radius:3px;}

/* ══ TOP SEARCH BAR ══ */
.search-bar{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:20px 22px;margin-bottom:18px;
  transition:border-color .3s;
}
.search-bar:focus-within{border-color:var(--cyan-border);}
.sb-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;}
.sb-main{flex:1;min-width:200px;}
.sb-label{font-size:10.5px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px;}
.sb-input,.sb-select{
  width:100%;background:rgba(0,0,0,0.22);border:1.5px solid var(--bd);
  border-radius:var(--rs);padding:10px 14px;color:var(--tx);
  font-family:var(--fb);font-size:13.5px;outline:none;transition:border-color .3s;
}
.sb-input:focus,.sb-select:focus{border-color:var(--cyan);}
.sb-input::placeholder{color:var(--tx-3);}
.sb-select option{background:var(--s2);color:var(--tx);}
.sb-filters{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-top:14px;}
.sb-filter-item{min-width:130px;}

/* ══ CATEGORY HORIZONTAL SCROLL ══ */
.cat-scroll-wrap{
  display:flex;gap:8px;overflow-x:auto;padding:0 2px 10px;
  margin-bottom:18px;scrollbar-width:none;-ms-overflow-style:none;
}
.cat-scroll-wrap::-webkit-scrollbar{display:none;}
.cat-pill{
  display:inline-flex;align-items:center;gap:7px;
  padding:8px 16px;border-radius:50px;
  background:var(--glass);border:1.5px solid var(--bd);
  color:var(--tx-2);font-size:12.5px;font-weight:600;
  cursor:pointer;transition:var(--e);white-space:nowrap;
  text-decoration:none;font-family:var(--fb);
  flex-shrink:0;
}
.cat-pill:hover{border-color:var(--cyan-border);color:var(--cyan);}
.cat-pill.active{
  background:var(--cyan-dim);border-color:var(--cyan);color:var(--cyan);
}
.cat-pill .cp-icon{font-size:14px;}

/* ══ PAGE BODY LAYOUT ══ */
.page-body{display:grid;grid-template-columns:260px 1fr;gap:22px;align-items:start;}

/* ══ FILTER SIDEBAR ══ */
.filter-panel{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:20px;position:sticky;top:80px;
  transition:background .3s,border-color .3s;
}
.fp-title{font-family:var(--fm);font-size:14px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;}
.fp-section{margin-bottom:18px;}
.fp-section:last-child{margin-bottom:0;}
.fp-label{font-size:10px;font-weight:800;color:var(--tx-3);text-transform:uppercase;letter-spacing:1px;margin-bottom:9px;}
.fp-option{
  display:flex;align-items:center;gap:9px;padding:8px 10px;
  border-radius:9px;cursor:pointer;transition:var(--e);font-size:13px;
  color:var(--tx-2);
}
.fp-option:hover{background:rgba(255,255,255,0.04);color:var(--tx);}
.fp-option.active{background:var(--cyan-dim);color:var(--cyan);}
.fp-option input[type=radio]{display:none;}
.fp-check{
  width:16px;height:16px;border-radius:50%;border:2px solid var(--bd2);
  flex-shrink:0;display:flex;align-items:center;justify-content:center;
  transition:var(--e);
}
.fp-option.active .fp-check{border-color:var(--cyan);background:var(--cyan);}
.fp-option.active .fp-check::after{content:'';width:6px;height:6px;border-radius:50%;background:#0C0E14;}
.fp-budget-inputs{display:flex;gap:6px;}
.fp-budget-inputs input{
  width:100%;background:rgba(0,0,0,0.22);border:1.5px solid var(--bd);
  border-radius:9px;padding:8px 10px;color:var(--tx);font-family:var(--fb);
  font-size:12.5px;outline:none;transition:border-color .3s;
}
.fp-budget-inputs input:focus{border-color:var(--cyan);}
.fp-budget-inputs input::placeholder{color:var(--tx-3);}

/* ══ RIGHT: RESULTS AREA ══ */
.results-area{}

/* Recommended strip */
.section-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  overflow:hidden;margin-bottom:18px;transition:background .3s;
}
.sc-head{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--bd);}
.card-title{font-family:var(--fm);font-size:14px;font-weight:700;}
.rec-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;padding:14px 18px;}
.rec-card{
  background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:12px;
  padding:13px;transition:var(--e);cursor:pointer;text-decoration:none;color:var(--tx);
}
.rec-card:hover{border-color:var(--cyan-border);transform:translateY(-2px);}
.rc-title{font-family:var(--fm);font-weight:700;font-size:13px;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rc-budget{color:var(--cyan);font-weight:700;font-size:13px;margin-bottom:3px;font-family:var(--fm);}
.rc-meta{font-size:11px;color:var(--tx-3);}

/* Results header */
.results-header{
  display:flex;align-items:center;justify-content:space-between;
  margin-bottom:18px;gap:12px;flex-wrap:wrap;
}
.results-count{font-size:14px;color:var(--tx-3);}
.results-count strong{color:var(--tx);font-family:var(--fm);}
.sort-select{
  background:rgba(0,0,0,0.2);border:1.5px solid var(--bd);border-radius:10px;
  padding:8px 14px;color:var(--tx);font-family:var(--fb);font-size:12.5px;
  outline:none;cursor:pointer;transition:border-color .3s;
}
.sort-select:focus{border-color:var(--cyan);}
.sort-select option{background:var(--s2);}

/* ══ JOB CARDS ══ */
.jobs-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:18px;}
.job-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:22px;transition:var(--e);position:relative;overflow:hidden;
  display:flex;flex-direction:column;
}
/* Ambient glow */
.job-card::before{
  content:'';position:absolute;top:-30px;right:-30px;
  width:110px;height:110px;
  background:radial-gradient(circle,rgba(0,212,200,0.06),transparent 70%);
  pointer-events:none;
}
.job-card:hover{transform:translateY(-4px);border-color:var(--cyan-border);box-shadow:0 16px 50px rgba(0,0,0,0.4);}
.job-card.applied{border-color:rgba(247,183,49,0.2);background:rgba(247,183,49,0.03);}
.job-card.featured-job{border-color:var(--violet-border);}

/* Card top row */
.jc-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;}
.jc-client{display:flex;align-items:center;gap:9px;}
.client-ava{
  width:38px;height:38px;border-radius:50%;flex-shrink:0;overflow:hidden;
  background:linear-gradient(135deg,var(--violet),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:13px;color:#fff;
}
.client-ava img{width:100%;height:100%;object-fit:cover;}
.client-name{font-family:var(--fm);font-weight:700;font-size:13px;margin-bottom:1px;}
.client-time{font-size:10.5px;color:var(--tx-3);}
/* Trust badges row */
.trust-row{display:flex;gap:4px;margin-top:3px;}
.t-badge{font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:4px;font-family:var(--fm);}
.tb-phone  {background:var(--green-dim);color:var(--green);}
.tb-payment{background:rgba(247,183,49,0.1);color:var(--amber);}

.jc-badges{display:flex;gap:5px;align-items:center;flex-shrink:0;flex-wrap:wrap;}
.badge{padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:700;font-family:var(--fm);}
.badge-urgent {background:var(--coral-dim);color:var(--coral);border:1px solid var(--coral-border);}
.badge-applied{background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.2);}
.badge-feat   {background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.badge-cat    {background:var(--cyan-dim);color:var(--cyan);border:1px solid var(--cyan-border);}

/* Title */
.job-title{
  font-family:var(--fm);font-weight:800;font-size:16px;line-height:1.3;
  margin-bottom:9px;color:var(--tx);display:block;
  transition:color .2s;
}
.job-title:hover{color:var(--cyan);}

/* Description */
.jc-desc{
  color:var(--tx-2);font-size:12.5px;line-height:1.65;
  margin-bottom:13px;
  display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
  flex:1;
}

/* Meta row */
.jc-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:11.5px;color:var(--tx-3);margin-bottom:13px;}

/* Client trust stats */
.client-trust{
  background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  border-radius:10px;padding:10px 12px;margin-bottom:13px;
  display:flex;gap:14px;flex-wrap:wrap;
}
.ct-item{text-align:center;}
.ct-val{font-family:var(--fm);font-weight:800;font-size:14px;color:var(--tx);}
.ct-lbl{font-size:10px;color:var(--tx-3);margin-top:1px;}

/* Proposal competition */
.prop-competition{
  display:flex;align-items:center;gap:8px;
  padding:8px 12px;border-radius:9px;
  background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  margin-bottom:13px;
}
.pc-count{font-family:var(--fm);font-weight:800;font-size:16px;}
.pc-label{font-size:11px;color:var(--tx-3);}
.pc-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}

/* Footer */
.jc-footer{
  display:flex;align-items:center;justify-content:space-between;
  padding-top:13px;border-top:1px solid var(--bd);gap:10px;
  margin-top:auto;
}
.jc-budget-wrap{}
.jc-budget{font-family:var(--fm);font-weight:800;font-size:18px;color:var(--cyan);}
.jc-exp{font-size:10.5px;color:var(--tx-3);margin-top:2px;}
.jc-actions{display:flex;align-items:center;gap:6px;flex-shrink:0;}
.apply-btn{
  display:inline-flex;align-items:center;gap:5px;
  padding:9px 18px;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  color:#0C0E14;border:none;border-radius:10px;
  font-family:var(--fm);font-weight:700;font-size:12.5px;
  cursor:pointer;transition:var(--e);white-space:nowrap;text-decoration:none;
  box-shadow:0 3px 12px var(--gC);
}
.apply-btn:hover{transform:translateY(-2px);box-shadow:0 7px 20px var(--gC);}
.apply-btn.applied{
  background:rgba(247,183,49,0.1);color:var(--amber);
  border:1px solid rgba(247,183,49,0.2);
  box-shadow:none;cursor:default;
}
.apply-btn.applied:hover{transform:none;}
.details-btn{
  display:inline-flex;align-items:center;gap:5px;
  padding:9px 14px;background:rgba(255,255,255,0.04);
  border:1px solid var(--bd);border-radius:10px;
  color:var(--tx-2);font-size:12.5px;font-weight:600;
  cursor:pointer;transition:var(--e);text-decoration:none;font-family:var(--fb);
}
.details-btn:hover{background:rgba(255,255,255,0.08);color:var(--tx);border-color:var(--bd2);}
.save-btn{
  width:36px;height:36px;border-radius:9px;
  background:rgba(255,255,255,0.04);border:1px solid var(--bd);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:var(--e);flex-shrink:0;font-size:15px;
}
.save-btn:hover,.save-btn.saved{
  background:rgba(247,183,49,0.1);border-color:rgba(247,183,49,0.3);
}

/* ══ PAGINATION ══ */
.pagination{display:flex;gap:7px;justify-content:center;margin-top:32px;flex-wrap:wrap;}
.pag-btn{
  padding:9px 16px;border-radius:10px;text-decoration:none;
  font-size:13.5px;font-weight:600;font-family:var(--fm);
  color:var(--tx-3);background:rgba(255,255,255,0.04);
  border:1px solid var(--bd);transition:var(--e);
}
.pag-btn.active{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}
.pag-btn:hover:not(.active){background:rgba(255,255,255,0.08);color:var(--tx);border-color:var(--bd2);}

/* ══ EMPTY STATE ══ */
.empty{text-align:center;padding:64px 20px;}
.empty-icon{font-size:52px;margin-bottom:16px;}
.empty-title{font-family:var(--fm);font-size:20px;font-weight:800;margin-bottom:8px;}
.empty-text{color:var(--tx-3);font-size:14px;max-width:340px;margin:0 auto;}

/* ══ JOB DETAIL MODAL ══ */
.modal-overlay{
  display:none;position:fixed;inset:0;z-index:2000;
  background:rgba(0,0,0,0.85);backdrop-filter:blur(16px);
  align-items:center;justify-content:center;padding:20px;
}
.modal-overlay.open{display:flex;}
.modal-box{
  background:var(--s1);border:1px solid var(--bd2);border-radius:20px;
  width:100%;max-width:640px;max-height:88vh;overflow-y:auto;
  animation:modalIn .25s ease;
}
@keyframes modalIn{from{opacity:0;transform:scale(.94);}to{opacity:1;transform:scale(1);}}
.modal-head{
  display:flex;align-items:flex-start;justify-content:space-between;
  padding:22px 24px;border-bottom:1px solid var(--bd);gap:12px;
  position:sticky;top:0;background:var(--s1);z-index:2;
}
.modal-title{font-family:var(--fm);font-size:18px;font-weight:800;line-height:1.3;}
.modal-close{
  width:32px;height:32px;border-radius:9px;border:1px solid var(--bd);
  background:rgba(255,255,255,0.04);display:flex;align-items:center;
  justify-content:center;cursor:pointer;font-size:16px;color:var(--tx-3);
  transition:var(--e);flex-shrink:0;
}
.modal-close:hover{background:rgba(255,77,106,0.12);color:var(--red);}
.modal-body{padding:22px 24px;}
.mb-section{margin-bottom:20px;}
.mb-label{font-size:10.5px;font-weight:800;color:var(--tx-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px;}
.mb-text{font-size:13.5px;color:var(--tx-2);line-height:1.75;}
.mb-skills{display:flex;flex-wrap:wrap;gap:6px;}
.skill-tag{background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:4px 10px;border-radius:7px;font-size:11.5px;font-weight:600;}
.modal-footer{padding:16px 24px;border-top:1px solid var(--bd);display:flex;gap:10px;flex-wrap:wrap;}

/* ══ TOAST ══ */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);max-width:330px;min-width:240px;box-shadow:0 12px 36px rgba(0,0,0,.5);animation:toastIn .35s ease;backdrop-filter:blur(14px);}
.toast.success{border-left:3px solid var(--green);}
.toast.error  {border-left:3px solid var(--red);}
.toast.info   {border-left:3px solid var(--cyan);}
.toast.warning{border-left:3px solid var(--amber);}
.t-ico{font-size:17px;flex-shrink:0;}.t-bod{flex:1;}
.t-ttl{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}
.t-msg{font-size:11.5px;color:var(--tx-3);}
.t-cls{cursor:pointer;color:var(--tx-3);font-size:17px;flex-shrink:0;}
@keyframes toastIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}

/* ══ MOBILE BOTTOM NAV ══ */
.mobile-nav{display:none;position:fixed;bottom:0;left:0;right:0;z-index:500;background:rgba(12,14,20,0.97);backdrop-filter:blur(20px);border-top:1px solid var(--bd);padding:8px 0;grid-template-columns:repeat(4,1fr);}
.mn-item{display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 4px;cursor:pointer;transition:var(--e);text-decoration:none;color:var(--tx-3);}
.mn-item.active{color:var(--cyan);}
.mn-ico{font-size:20px;}
.mn-lbl{font-size:9px;font-weight:600;font-family:var(--fm);text-transform:uppercase;}

/* ══ RESPONSIVE ══ */
@media(max-width:1100px){.page-body{grid-template-columns:220px 1fr;}}
@media(max-width:900px){.page-body{grid-template-columns:1fr;}.filter-panel{position:static;display:none;}.filter-panel.mobile-open{display:block;}}
@media(max-width:768px){
  .sidebar{display:none;}.main{margin-left:0;}
  .mobile-nav{display:grid;}
  .content{padding:16px 14px 90px;}
  .topbar{padding:0 16px;}
  .jobs-grid{grid-template-columns:1fr;}
  .rec-grid{grid-template-columns:1fr;}
  #toast-c{bottom:80px;}
}
@media(max-width:480px){.sb-row{flex-direction:column;}.sb-filters{flex-direction:column;}}
</style>
</head>
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar">
  <a href="<?= APP_URL ?>/index.php" class="sb-logo">
    <div class="sb-logo-mark">G</div>
    <span class="sb-logo-text">Gig<span>Ghana</span></span>
  </a>
  <nav class="sb-nav">
    <div class="nav-section">Provider</div>
    <a href="<?= APP_URL ?>/provider/dashboard.php"   class="sb-item">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="sb-item active">🔍 Browse Jobs</a>
    <a href="<?= APP_URL ?>/provider/profile.php"     class="sb-item">👤 My Profile</a>
    <a href="<?= APP_URL ?>/provider/earnings.php"    class="sb-item">💰 Earnings</a>
    <div class="nav-section">Activity</div>
    <a href="<?= APP_URL ?>/client/messages.php"      class="sb-item">💬 Messages</a>
    <a href="<?= APP_URL ?>/provider/proposals.php"   class="sb-item">📩 My Proposals</a>
    <div class="nav-section">Account</div>
    <a href="<?= APP_URL ?>/index.php"                class="sb-item">🏠 Homepage</a>
    <a href="<?= APP_URL ?>/auth/logout.php"          class="sb-item danger">🚪 Sign Out</a>
  </nav>
  <div class="sb-user">
    <div class="sb-user-card">
      <div class="sb-av">
        <?php
        $u = getUserById($userId);
        if(!empty($u['avatar'])): ?><img src="<?= sanitize($u['avatar']) ?>" alt=""><?php
        else: echo strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)); endif; ?>
      </div>
      <div>
        <div class="sb-uname"><?= sanitize($u['first_name'].' '.$u['last_name']) ?></div>
        <div class="sb-urole">Freelancer</div>
      </div>
    </div>
  </div>
</aside>

<!-- ══════════════ MAIN ══════════════ -->
<div class="main">
  <header class="topbar">
    <div class="page-title">
      Browse Jobs
      <span>(<?= number_format($totalJobs) ?> open)</span>
    </div>
    <div class="topbar-right">
      <button class="theme-btn" id="themeBtn" onclick="toggleTheme()"><?= $isLight?'☀️':'🌙' ?></button>
      <!-- Mobile filter toggle -->
      <button class="btn btn-ghost btn-sm" id="filterToggleBtn" onclick="toggleMobileFilter()" style="display:none;">⚙️ Filters</button>
      <a href="<?= APP_URL ?>/provider/dashboard.php" class="btn btn-ghost">← Dashboard</a>
    </div>
  </header>

  <div class="content">

    <!-- ══ SUBSCRIPTION WARNING ══ -->
    <?php if($subTier === 'free'): ?>
      <?php $barPct = min(100, round($propUsed / $propLimit * 100)); ?>
      <?php if($remaining <= 0): ?>
      <div class="sub-warn">
        <div class="sw-text">⚠️ <strong>Proposal limit reached.</strong> You've used all <?= $propLimit ?> free proposals. Upgrade to keep applying!</div>
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
          <a href="<?= APP_URL ?>/provider/upgrade.php" class="btn btn-coral btn-sm">⭐ Upgrade Now</a>
        </div>
      </div>
      <?php elseif($remaining <= 1): ?>
      <div class="sub-warn yellow">
        <div>
          <div class="sw-text">⚡ Only <strong><?= $remaining ?> free proposal<?= $remaining!=1?'s':'' ?></strong> remaining — consider upgrading before you run out.</div>
        </div>
        <div class="sw-bar-wrap">
          <div class="sw-bar-track"><div class="sw-bar-fill" style="width:<?= $barPct ?>%;background:var(--amber);border-radius:3px;"></div></div>
          <span style="font-size:11px;color:var(--tx-3);"><?= $propUsed ?>/<?= $propLimit ?></span>
          <a href="<?= APP_URL ?>/provider/upgrade.php" class="btn btn-sm" style="background:var(--amber);color:#0C0E14;font-weight:700;">Upgrade</a>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>

    <!-- ══ SEARCH BAR ══ -->
    <form method="GET" id="filterForm">
      <div class="search-bar">
        <div class="sb-row">
          <div class="sb-main">
            <div class="sb-label">Search Jobs</div>
            <input type="text" name="q" class="sb-input" id="searchInput"
                   placeholder="Search by title, skill or keyword…"
                   value="<?= htmlspecialchars($q) ?>">
          </div>
          <div style="min-width:180px;">
            <div class="sb-label">Category</div>
            <select name="category" class="sb-select" onchange="this.form.submit()">
              <option value="">All Categories</option>
              <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= $catId===$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-cyan">🔍 Search</button>
        </div>
        <!-- Advanced filters row -->
        <div class="sb-filters">
          <div class="sb-filter-item">
            <div class="sb-label">Experience</div>
            <select name="experience" class="sb-select" onchange="this.form.submit()">
              <option value="">Any Level</option>
              <option value="entry"        <?= $expLevel==='entry'       ?'selected':'' ?>>Entry</option>
              <option value="intermediate" <?= $expLevel==='intermediate'?'selected':'' ?>>Intermediate</option>
              <option value="expert"       <?= $expLevel==='expert'      ?'selected':'' ?>>Expert</option>
            </select>
          </div>
          <div class="sb-filter-item">
            <div class="sb-label">Work Type</div>
            <select name="location_type" class="sb-select" onchange="this.form.submit()">
              <option value="">All Types</option>
              <option value="remote"  <?= $locType==='remote' ?'selected':'' ?>>Remote</option>
              <option value="onsite"  <?= $locType==='onsite' ?'selected':'' ?>>On-site</option>
              <option value="hybrid"  <?= $locType==='hybrid' ?'selected':'' ?>>Hybrid</option>
            </select>
          </div>
          <div class="sb-filter-item">
            <div class="sb-label">Job Type</div>
            <select name="budget_type" class="sb-select" onchange="this.form.submit()">
              <option value="">All</option>
              <option value="fixed"  <?= $jobType==='fixed' ?'selected':'' ?>>Fixed Price</option>
              <option value="hourly" <?= $jobType==='hourly'?'selected':'' ?>>Hourly</option>
            </select>
          </div>
          <div class="sb-filter-item">
            <div class="sb-label">Posted</div>
            <select name="posted" class="sb-select" onchange="this.form.submit()">
              <option value="">Any time</option>
              <option value="24h"  <?= $posted==='24h' ?'selected':'' ?>>Last 24 hours</option>
              <option value="3d"   <?= $posted==='3d'  ?'selected':'' ?>>Last 3 days</option>
              <option value="week" <?= $posted==='week'?'selected':'' ?>>Last week</option>
            </select>
          </div>
          <div>
            <div class="sb-label">Min ₵</div>
            <input type="number" name="budget_min" class="sb-input" style="width:100px;" placeholder="0" value="<?= $budMin ?: '' ?>">
          </div>
          <div>
            <div class="sb-label">Max ₵</div>
            <input type="number" name="budget_max" class="sb-input" style="width:100px;" placeholder="Any" value="<?= $budMax ?: '' ?>">
          </div>
          <?php if($q || $catId || $expLevel || $locType || $jobType || $posted || $budMin || $budMax): ?>
          <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="btn btn-ghost btn-sm" style="align-self:flex-end;">✕ Clear All</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- ══ CATEGORY HORIZONTAL SCROLL ══ -->
      <div class="cat-scroll-wrap">
        <a href="?<?= http_build_query(array_merge($_GET,['category'=>'','page'=>1])) ?>"
           class="cat-pill <?= !$catId?'active':'' ?>">
          <span class="cp-icon">🌍</span> All
        </a>
        <?php
        $catIconMap = [
            'code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
            'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
            'briefcase'=>'⚖️','headphones'=>'🎧','camera'=>'📷','globe'=>'🌐',
            'tool'=>'🔧','bar-chart'=>'📊','music'=>'🎵',
        ];
        foreach($categories as $c):
        ?>
        <a href="?<?= http_build_query(array_merge($_GET,['category'=>$c['id'],'page'=>1])) ?>"
           class="cat-pill <?= $catId===$c['id']?'active':'' ?>">
          <span class="cp-icon"><?= $catIconMap[$c['icon']??'briefcase']??'📂' ?></span>
          <?= sanitize($c['name']) ?>
        </a>
        <?php endforeach; ?>
      </div>
    </form>

    <!-- ══ PAGE BODY: FILTER SIDEBAR + RESULTS ══ -->
    <div class="page-body">

      <!-- LEFT: ADVANCED FILTER SIDEBAR -->
      <div>
        <div class="filter-panel" id="filterPanel">
          <div class="fp-title">
            ⚙️ Filters
            <?php if($q||$catId||$expLevel||$locType||$jobType||$posted||$budMin||$budMax): ?>
            <a href="<?= APP_URL ?>/provider/browse-jobs.php" style="font-size:11px;color:var(--cyan);font-weight:600;">Clear all</a>
            <?php endif; ?>
          </div>

          <!-- Budget presets -->
          <div class="fp-section">
            <div class="fp-label">Budget Range</div>
            <?php
            $budPresets = [
                ['label'=>'Under ₵200',        'min'=>0,    'max'=>200],
                ['label'=>'₵200 – ₵500',       'min'=>200,  'max'=>500],
                ['label'=>'₵500 – ₵1,000',     'min'=>500,  'max'=>1000],
                ['label'=>'₵1,000+',            'min'=>1000, 'max'=>0],
            ];
            foreach($budPresets as $bp):
                $isActive = ($budMin == $bp['min'] && $budMax == $bp['max']);
                $href = http_build_query(array_merge($_GET, ['budget_min'=>$bp['min'], 'budget_max'=>$bp['max'], 'page'=>1]));
            ?>
            <a href="?<?= $href ?>" class="fp-option <?= $isActive?'active':'' ?>">
              <div class="fp-check"></div><?= $bp['label'] ?>
            </a>
            <?php endforeach; ?>
            <div style="margin-top:10px;">
              <div class="fp-label" style="margin-bottom:6px;">Custom Range (₵)</div>
              <form method="GET">
                <?php foreach($_GET as $k=>$v): if(!in_array($k,['budget_min','budget_max'])): ?><input type="hidden" name="<?= htmlspecialchars($k) ?>" value="<?= htmlspecialchars($v) ?>"><?php endif; endforeach; ?>
                <div class="fp-budget-inputs">
                  <input type="number" name="budget_min" placeholder="Min" value="<?= $budMin ?: '' ?>">
                  <input type="number" name="budget_max" placeholder="Max" value="<?= $budMax ?: '' ?>">
                </div>
                <button type="submit" class="btn btn-cyan btn-sm" style="width:100%;justify-content:center;margin-top:8px;">Apply</button>
              </form>
            </div>
          </div>

          <!-- Location type -->
          <div class="fp-section">
            <div class="fp-label">Work Location</div>
            <?php foreach([''=>'All Locations','remote'=>'🌐 Remote','onsite'=>'🏢 On-site','hybrid'=>'🔀 Hybrid'] as $v=>$l): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['location_type'=>$v,'page'=>1])) ?>"
               class="fp-option <?= $locType===$v?'active':'' ?>">
              <div class="fp-check"></div><?= $l ?>
            </a>
            <?php endforeach; ?>
          </div>

          <!-- Experience -->
          <div class="fp-section">
            <div class="fp-label">Experience Level</div>
            <?php foreach([''=>'Any Level','entry'=>'🌱 Beginner','intermediate'=>'📈 Intermediate','expert'=>'🏆 Expert'] as $v=>$l): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['experience'=>$v,'page'=>1])) ?>"
               class="fp-option <?= $expLevel===$v?'active':'' ?>">
              <div class="fp-check"></div><?= $l ?>
            </a>
            <?php endforeach; ?>
          </div>

          <!-- Job type -->
          <div class="fp-section">
            <div class="fp-label">Job Type</div>
            <?php foreach([''=>'All','fixed'=>'📌 Fixed Price','hourly'=>'⏱ Hourly Rate'] as $v=>$l): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['budget_type'=>$v,'page'=>1])) ?>"
               class="fp-option <?= $jobType===$v?'active':'' ?>">
              <div class="fp-check"></div><?= $l ?>
            </a>
            <?php endforeach; ?>
          </div>

          <!-- Posted date -->
          <div class="fp-section">
            <div class="fp-label">Date Posted</div>
            <?php foreach([''=>'Any Time','24h'=>'⚡ Last 24 Hours','3d'=>'📅 Last 3 Days','week'=>'🗓 Last Week'] as $v=>$l): ?>
            <a href="?<?= http_build_query(array_merge($_GET,['posted'=>$v,'page'=>1])) ?>"
               class="fp-option <?= $posted===$v?'active':'' ?>">
              <div class="fp-check"></div><?= $l ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT: RESULTS -->
      <div class="results-area">

        <!-- Recommended jobs strip -->
        <?php if(!empty($recommended) && !$q && !$catId): ?>
        <div class="section-card">
          <div class="sc-head">
            <div class="card-title">💡 Recommended For You</div>
            <span style="font-size:11.5px;color:var(--tx-3);"><?= count($recommended) ?> jobs match your profile</span>
          </div>
          <div class="rec-grid">
            <?php foreach($recommended as $r): ?>
            <a href="<?= APP_URL ?>/job-details.php?id=<?= $r['id'] ?>" class="rec-card">
              <div class="rc-title"><?= sanitize($r['title']) ?></div>
              <div class="rc-budget"><?= formatCurrency($r['budget_min']) ?><?= $r['budget_max']>$r['budget_min']?' – '.formatCurrency($r['budget_max']):'' ?><?= $r['budget_type']==='hourly'?'/hr':'' ?></div>
              <div class="rc-meta"><?= sanitize($r['cat_name']??'General') ?> · 📩 <?= $r['proposal_count'] ?> proposals · <?= timeAgo($r['created_at']) ?></div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Results header -->
        <div class="results-header">
          <div class="results-count">
            Showing <strong><?= number_format($totalJobs) ?></strong> open job<?= $totalJobs!=1?'s':'' ?>
            <?php if($q): ?> for "<strong><?= htmlspecialchars($q) ?></strong>"<?php endif; ?>
            <?php if($catId && !empty($categories)): foreach($categories as $c) if($c['id']===$catId) echo ' in <strong>'.sanitize($c['name']).'</strong>'; endif; ?>
          </div>
          <select class="sort-select" onchange="window.location='?<?= http_build_query(array_merge($_GET,['sort'=>'__S__'])) ?>'.replace('__S__',this.value)">
            <option value="newest"      <?= $sort==='newest'     ?'selected':'' ?>>🕒 Newest First</option>
            <option value="budget_high" <?= $sort==='budget_high'?'selected':'' ?>>₵ Budget: High → Low</option>
            <option value="budget_low"  <?= $sort==='budget_low' ?'selected':'' ?>>₵ Budget: Low → High</option>
            <option value="proposals"   <?= $sort==='proposals'  ?'selected':'' ?>>📩 Fewest Proposals</option>
          </select>
        </div>

        <!-- JOB CARDS -->
        <?php if(empty($jobs)): ?>
        <div class="empty">
          <div class="empty-icon">🔍</div>
          <div class="empty-title">No jobs found</div>
          <p class="empty-text">Try adjusting your search terms or filters to discover more opportunities.</p>
          <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="btn btn-cyan" style="margin-top:18px;">Clear Filters</a>
        </div>

        <?php else: ?>
        <div class="jobs-grid">
          <?php foreach($jobs as $j):
            $propCnt  = (int)$j['proposal_count'];
            $pColor   = propColor($propCnt);
            $pLabel   = propLabel($propCnt);
            $hireRate = $j['client_total_jobs'] > 0
              ? round($j['client_hired'] / $j['client_total_jobs'] * 100)
              : 0;
            $catIco   = $iconMap[$j['cat_icon']??'briefcase'] ?? '📋';
          ?>
          <div class="job-card <?= $j['already_applied']?'applied':'' ?> <?= $j['is_featured']?'featured-job':'' ?>"
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

            <!-- Top row: client + badges -->
            <div class="jc-top">
              <div class="jc-client">
                <div class="client-ava">
                  <?php if(!empty($j['client_avatar'])): ?><img src="<?= sanitize($j['client_avatar']) ?>" alt="" loading="lazy"><?php else: echo strtoupper(substr($j['first_name'],0,1)); endif; ?>
                </div>
                <div>
                  <div class="client-name"><?= sanitize($j['first_name'].' '.$j['last_name']) ?></div>
                  <div class="client-time"><?= timeAgo($j['created_at']) ?></div>
                  <div class="trust-row">
                    <?php if($j['phone_verified']): ?><span class="t-badge tb-phone">📱 Phone</span><?php endif; ?>
                    <?php if($j['payment_verified']): ?><span class="t-badge tb-payment">💳 Payment</span><?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="jc-badges">
                <?php if($j['is_urgent']): ?><span class="badge badge-urgent">🔥 Urgent</span><?php endif; ?>
                <?php if($j['is_featured']): ?><span class="badge badge-feat">⭐ Featured</span><?php endif; ?>
                <?php if($j['already_applied']): ?><span class="badge badge-applied">✓ Applied</span><?php endif; ?>
                <?php if($j['cat_name']): ?><span class="badge badge-cat"><?= $catIco ?> <?= sanitize($j['cat_name']) ?></span><?php endif; ?>
                <button class="save-btn <?= $j['is_saved']?'saved':'' ?>"
                        onclick="toggleSave(this,<?= $j['id'] ?>)"
                        title="<?= $j['is_saved']?'Remove from saved':'Save job' ?>">
                  <?= $j['is_saved']?'🔖':'📌' ?>
                </button>
              </div>
            </div>

            <!-- Title -->
            <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" class="job-title">
              <?= sanitize($j['title']) ?>
            </a>

            <!-- Description preview -->
            <p class="jc-desc"><?= sanitize($j['description']) ?></p>

            <!-- Meta row -->
            <div class="jc-meta">
              <span>🌍 <?= ucfirst(str_replace('_',' ',$j['location_type'])) ?></span>
              <?php if($j['location']): ?><span>📍 <?= sanitize($j['location']) ?></span><?php endif; ?>
              <span>⏱ <?= ucfirst(str_replace(['_',' '],['- ',''],($j['duration']??''))) ?></span>
              <span>🎯 <?= ucfirst($j['experience_level']) ?></span>
              <span>📋 <?= ucfirst(str_replace('_',' ',$j['budget_type'])) ?></span>
            </div>

            <!-- Client trust stats -->
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
              <div class="pc-dot" style="background:<?= $pColor ?>; box-shadow:0 0 6px <?= $pColor ?>40;"></div>
              <div>
                <div class="pc-count" style="color:<?= $pColor ?>;"><?= $propCnt ?> proposal<?= $propCnt!=1?'s':'' ?></div>
                <div class="pc-label"><?= $pLabel ?></div>
              </div>
            </div>

            <!-- Footer: budget + actions -->
            <div class="jc-footer">
              <div class="jc-budget-wrap">
                <div class="jc-budget">
                  <?= formatCurrency($j['budget_min']) ?>
                  <?= $j['budget_max'] > $j['budget_min'] ? ' – '.formatCurrency($j['budget_max']) : '' ?>
                  <?= $j['budget_type']==='hourly' ? '/hr' : '' ?>
                </div>
                <div class="jc-exp"><?= ucfirst($j['experience_level']) ?> level</div>
              </div>
              <div class="jc-actions">
                <button class="details-btn" onclick="openModal(this.closest('.job-card'))">👁 Details</button>
                <?php if($j['already_applied']): ?>
                <span class="apply-btn applied">✓ Sent</span>
                <?php elseif($subTier==='free' && $remaining <= 0): ?>
                <a href="<?= APP_URL ?>/provider/upgrade.php" class="apply-btn" style="background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;">🔒 Upgrade</a>
                <?php else: ?>
                <a href="<?= APP_URL ?>/provider/submit-proposal.php?job_id=<?= $j['id'] ?>" class="apply-btn">Apply →</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- PAGINATION -->
        <?php if($totalPages > 1): ?>
        <div class="pagination">
          <?php if($page > 1): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="pag-btn">← Prev</a>
          <?php endif; ?>
          <?php for($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <?php if($page < $totalPages): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="pag-btn">Next →</a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

      </div><!-- /results-area -->
    </div><!-- /page-body -->
  </div><!-- /content -->
</div><!-- /main -->

<!-- MOBILE BOTTOM NAV -->
<nav class="mobile-nav">
  <a href="<?= APP_URL ?>/provider/dashboard.php"   class="mn-item"><div class="mn-ico">📊</div><div class="mn-lbl">Home</div></a>
  <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="mn-item active"><div class="mn-ico">🔍</div><div class="mn-lbl">Jobs</div></a>
  <a href="<?= APP_URL ?>/client/messages.php"      class="mn-item"><div class="mn-ico">💬</div><div class="mn-lbl">Chat</div></a>
  <a href="<?= APP_URL ?>/provider/profile.php"     class="mn-item"><div class="mn-ico">👤</div><div class="mn-lbl">Profile</div></a>
</nav>

<!-- ══ JOB DETAIL MODAL ══ -->
<div class="modal-overlay" id="jobModal" onclick="if(event.target===this)closeModal()">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="modalTitle">Job Details</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" id="modalBody">
      <!-- Populated by JS -->
    </div>
    <div class="modal-footer" id="modalFooter">
      <!-- Populated by JS -->
    </div>
  </div>
</div>

<div id="toast-c"></div>

<script>
/* ══ THEME ══ */
function toggleTheme(){
  const l = document.getElementById('appBody').classList.toggle('lm');
  const v = l ? 'light' : 'dark';
  localStorage.setItem('gg_theme',v);
  document.cookie = `gg_theme=${v};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = l ? '☀️' : '🌙';
}
(function(){
  const s = localStorage.getItem('gg_theme') || '<?= $isLight?"light":"dark" ?>';
  const b = document.getElementById('appBody'), btn = document.getElementById('themeBtn');
  if(s==='light'){b.classList.add('lm');if(btn)btn.textContent='☀️';}
  else{b.classList.remove('lm');if(btn)btn.textContent='🌙';}
})();

/* ══ MOBILE FILTER TOGGLE ══ */
function toggleMobileFilter(){
  document.getElementById('filterPanel').classList.toggle('mobile-open');
}
/* Show toggle btn on small screens */
if(window.innerWidth <= 900) document.getElementById('filterToggleBtn').style.display='flex';
window.addEventListener('resize',()=>{
  document.getElementById('filterToggleBtn').style.display = window.innerWidth<=900?'flex':'none';
});

/* ══ LIVE SEARCH DEBOUNCE ══ */
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function(){
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 600);
});

/* ══ SAVE / UNSAVE ══ */
function toggleSave(btn, jobId){
  fetch('<?= APP_URL ?>/api/jobs.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:`action=toggle_save&job_id=${jobId}&csrf=<?= $csrf ?>`
  })
  .then(r=>r.json())
  .then(d=>{
    if(d.saved){
      btn.classList.add('saved'); btn.textContent='🔖';
      showToast('Saved!','Job added to your saved list.','success');
    } else {
      btn.classList.remove('saved'); btn.textContent='📌';
      showToast('Removed','Job removed from saved.','info');
    }
  })
  .catch(()=>showToast('Error','Could not save job.','error'));
}

/* ══ JOB DETAIL MODAL ══ */
function openModal(card){
  const title    = card.dataset.title;
  const desc     = card.dataset.desc;
  const req      = card.dataset.req;
  const budMin   = parseFloat(card.dataset.budgetMin)||0;
  const budMax   = parseFloat(card.dataset.budgetMax)||0;
  const budType  = card.dataset.budgetType;
  const duration = (card.dataset.duration||'').replace(/_/g,' ');
  const exp      = card.dataset.exp;
  const loc      = card.dataset.loc;
  const location = card.dataset.location;
  const jobId    = card.dataset.jobId;
  const applied  = card.classList.contains('applied');

  const fmt = n => '₵' + parseFloat(n).toLocaleString('en-GH',{minimumFractionDigits:2});
  const budget = budMax > budMin ? `${fmt(budMin)} – ${fmt(budMax)}` : fmt(budMin);

  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalBody').innerHTML = `
    <div class="mb-section">
      <div class="mb-label">Budget</div>
      <div style="font-family:var(--fm);font-size:22px;font-weight:800;color:var(--cyan);">${budget}${budType==='hourly'?'/hr':''}</div>
    </div>
    <div class="mb-section" style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
      <div style="background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:10px;padding:12px;text-align:center;">
        <div style="font-family:var(--fm);font-weight:800;">${budType==='hourly'?'Hourly':'Fixed'}</div>
        <div style="font-size:11px;color:var(--tx-3);">Type</div>
      </div>
      <div style="background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:10px;padding:12px;text-align:center;">
        <div style="font-family:var(--fm);font-weight:800;">${exp}</div>
        <div style="font-size:11px;color:var(--tx-3);">Level</div>
      </div>
      <div style="background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:10px;padding:12px;text-align:center;">
        <div style="font-family:var(--fm);font-weight:800;">${loc}${location?' · '+location:''}</div>
        <div style="font-size:11px;color:var(--tx-3);">Location</div>
      </div>
    </div>
    <div class="mb-section" style="margin-top:16px;">
      <div class="mb-label">Description</div>
      <div class="mb-text">${desc.replace(/\n/g,'<br>')}</div>
    </div>
    ${req ? `<div class="mb-section"><div class="mb-label">Requirements</div><div class="mb-text">${req.replace(/\n/g,'<br>')}</div></div>` : ''}
    ${duration ? `<div class="mb-section"><div class="mb-label">Duration</div><div class="mb-text">${duration}</div></div>` : ''}
  `;
  document.getElementById('modalFooter').innerHTML = `
    <a href="<?= APP_URL ?>/job-details.php?id=${jobId}" class="btn btn-ghost" style="flex:1;justify-content:center;">Full Details ↗</a>
    ${applied
      ? '<span class="btn" style="flex:1;justify-content:center;background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.2);">✓ Proposal Sent</span>'
      : `<a href="<?= APP_URL ?>/provider/submit-proposal.php?job_id=${jobId}" class="btn btn-cyan" style="flex:1;justify-content:center;">Apply Now →</a>`
    }
  `;
  document.getElementById('jobModal').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeModal(){
  document.getElementById('jobModal').classList.remove('open');
  document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeModal(); });

/* ══ TOAST ══ */
const ICONS={success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
function showToast(title,msg,type='info',d=3800){
  const c=document.getElementById('toast-c');
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${ICONS[type]||'ℹ️'}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(50px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),330);},d);
}

/* URL param toasts */
<?php if(isset($_GET['success'])): ?>showToast('Success','<?= addslashes(sanitize($_GET['success'])) ?>','success');<?php endif; ?>
<?php if(isset($_GET['error'])  ): ?>showToast('Error',  '<?= addslashes(sanitize($_GET['error']))   ?>','error');<?php endif; ?>
</script>
</body>
</html>