<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$q        = sanitize($_GET['q']        ?? '');
$catId    = (int)($_GET['category']    ?? 0);
$minRate  = (float)($_GET['min_rate']  ?? 0);
$maxRate  = (float)($_GET['max_rate']  ?? 0);
$avail    = sanitize($_GET['availability'] ?? '');
$expLevel = sanitize($_GET['experience']   ?? '');
$location = sanitize($_GET['location']     ?? '');
$minRating= (float)($_GET['min_rating']   ?? 0);
$badge    = sanitize($_GET['badge']        ?? '');
$sort     = sanitize($_GET['sort']     ?? 'rating');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page-1)*$perPage;

try {
    $db    = getDB();
    $cats  = getCategories();
    $where = ["u.is_active=1", "u.role='provider'", "u.is_banned=0"];
    $params = [];

    if ($q) { $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR p.tagline LIKE ?)"; $params=array_merge($params,["%$q%","%$q%","%$q%"]); }
    if ($avail)    { $where[] = "p.availability=?";       $params[]=$avail; }
    if ($expLevel) { $where[] = "p.experience_level=?";   $params[]=$expLevel; }
    if ($minRate)  { $where[] = "p.hourly_rate >= ?";     $params[]=$minRate; }
    if ($maxRate)  { $where[] = "p.hourly_rate <= ?";     $params[]=$maxRate; }
    if ($minRating){ $where[] = "p.rating_avg >= ?";      $params[]=$minRating; }
    if ($location) { $where[] = "u.location LIKE ?";      $params[]="%$location%"; }
    if ($badge === 'verified') { $where[] = "p.is_verified=1"; }
    if ($badge === 'featured') { $where[] = "p.is_featured=1"; }
    if ($catId) {
        $where[] = "EXISTS (SELECT 1 FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id WHERE ps.provider_id=p.id AND s.category_id=?)";
        $params[] = $catId;
    }

    $w = implode(' AND ', $where);
    $orderBy = match($sort) {
        'rate_low'  => 'p.hourly_rate ASC',
        'rate_high' => 'p.hourly_rate DESC',
        'jobs'      => 'p.completed_jobs DESC',
        'newest'    => 'u.created_at DESC',
        default     => 'p.rating_avg DESC, p.rating_count DESC'
    };

    $total = $db->prepare("SELECT COUNT(*) FROM users u JOIN providers p ON p.user_id=u.id WHERE $w");
    $total->execute($params); $totalProvs=$total->fetchColumn(); $totalPages=ceil($totalProvs/$perPage);

    $stmt = $db->prepare(
        "SELECT u.first_name, u.last_name, u.avatar, u.location, u.id AS user_id,
         p.id AS provider_id, p.tagline, p.hourly_rate, p.availability, p.experience_level,
         p.completed_jobs, p.rating_avg, p.rating_count, p.is_verified, p.is_featured,
         p.response_time, p.success_rate
         FROM users u JOIN providers p ON p.user_id=u.id
         WHERE $w ORDER BY p.is_featured DESC, $orderBy
         LIMIT $perPage OFFSET $offset"
    );
    $stmt->execute($params); $providers=$stmt->fetchAll();

    foreach ($providers as &$pv) {
        $stSk = $db->prepare("SELECT s.name FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id WHERE ps.provider_id=? LIMIT 4");
        $stSk->execute([$pv['provider_id']]);
        $pv['skills'] = $stSk->fetchAll(PDO::FETCH_COLUMN);
    }
    unset($pv);

} catch(Exception $e) { error_log($e->getMessage()); $providers=[]; $totalProvs=0; $totalPages=0; $cats=[]; }

$user = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;

function rankLabel(int $j):array{
    if($j>=50) return['i'=>'🏆','l'=>'Elite Expert','c'=>'rk-gold'];
    if($j>=20) return['i'=>'⭐','l'=>'Top Rated','c'=>'rk-blue'];
    if($j>=5)  return['i'=>'📈','l'=>'Rising Talent','c'=>'rk-teal'];
    return['i'=>'🌱','l'=>'New','c'=>'rk-dim'];
}
function initials(string $f,string $l):string{ return strtoupper(substr($f,0,1).substr($l,0,1)); }

$activeCount = ($catId?1:0)+($avail?1:0)+($expLevel?1:0)+($minRate?1:0)+($maxRate?1:0)+($location?1:0)+($minRating?1:0)+($badge?1:0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="Find top freelancers across Ghana — GigGhana">
<title>Find Freelancers — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════
   DESIGN TOKENS — Volcanic Charcoal × Electric Cyan × Coral
   My palette: deep near-black base, crisp cyan primary,
   coral/amber warmth for CTAs, slate-mist text hierarchy.
══════════════════════════════════════ */
:root{
  /* ── Surfaces ── */
  --bg:#0C0E14;       /* near-black with a warm graphite tint */
  --s1:#13161E;       /* card base */
  --s2:#191D27;       /* elevated card */
  --s3:#1F2433;       /* hover / input bg */
  --glass:rgba(19,22,30,0.82);

  /* ── Primary: Electric Cyan ── */
  --cyan:#00D4C8;     /* bright teal-cyan, the hero accent */
  --cyan-d:#00A89F;   /* darker press state */
  --cyan-l:#4DFFE8;   /* glow / highlight */
  --cyan-dim:rgba(0,212,200,0.12);
  --cyan-border:rgba(0,212,200,0.25);

  /* ── Secondary: Coral / Amber warmth ── */
  --coral:#FF6B4A;    /* warm coral — CTAs, featured items */
  --coral-d:#E04D2E;
  --coral-l:#FF8F70;
  --coral-dim:rgba(255,107,74,0.12);
  --coral-border:rgba(255,107,74,0.28);

  /* ── Tertiary: Soft Violet ── */
  --violet:#7C6FF7;
  --violet-d:#5D52E0;
  --violet-dim:rgba(124,111,247,0.12);
  --violet-border:rgba(124,111,247,0.25);

  /* ── Semantic ── */
  --green:#1FD9A0;    /* success — slightly teal-shifted green */
  --green-d:#13B882;
  --green-dim:rgba(31,217,160,0.1);
  --red:#FF4D6A;
  --amber:#F7B731;    /* rating stars / highlights */

  /* ── Text ── */
  --tx:#F2F4F8;       /* primary — crisp white-grey */
  --tx-2:#9BA8BF;     /* secondary — muted slate */
  --tx-3:#4E5A6E;     /* tertiary — quiet */

  /* ── Borders ── */
  --bd:rgba(255,255,255,0.065);
  --bd2:rgba(255,255,255,0.12);

  /* ── Glows ── */
  --gC:rgba(0,212,200,0.18);
  --gO:rgba(255,107,74,0.15);
  --gV:rgba(124,111,247,0.15);

  /* ── Type ── */
  --fm:'Plus Jakarta Sans',sans-serif;
  --fb:'DM Sans',sans-serif;

  /* ── Shape ── */
  --r:16px; --rs:10px; --e:all 0.26s ease;
}

/* ── LIGHT MODE ── */
.lm{
  --bg:#F3F5FA;
  --s1:#EAEEF7;
  --s2:#E0E6F2;
  --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95;
  --cyan-d:#007870;
  --cyan-l:#00CFC3;
  --cyan-dim:rgba(0,158,149,0.1);
  --cyan-border:rgba(0,158,149,0.22);
  --coral:#E8512B;
  --coral-d:#C43C1C;
  --coral-dim:rgba(232,81,43,0.1);
  --coral-border:rgba(232,81,43,0.22);
  --violet:#5B4FD9;
  --violet-dim:rgba(91,79,217,0.1);
  --violet-border:rgba(91,79,217,0.2);
  --tx:#0D1220;
  --tx-2:#344060;
  --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.1);
  --bd2:rgba(30,40,80,0.18);
  --gC:rgba(0,158,149,0.1);
  --gO:rgba(232,81,43,0.1);
}
.lm .navbar{background:rgba(243,245,250,0.96)!important;border-bottom-color:rgba(30,40,80,0.08);}
.lm .navbar.on{background:rgba(243,245,250,0.99)!important;box-shadow:0 4px 28px rgba(13,18,32,0.08);}
.lm .mobile-nav{background:rgba(243,245,250,0.99);border-bottom-color:rgba(30,40,80,0.08);}
.lm .hero-providers{background:linear-gradient(180deg,var(--s2),var(--bg));}
.lm .search-strip{background:rgba(243,245,250,0.97);}
.lm .search-input-wrap{background:rgba(255,255,255,0.8);border-color:rgba(30,40,80,0.12);}
.lm .search-input-wrap input{color:var(--tx);}
.lm .filter-panel{background:rgba(234,238,247,0.99);}
.lm .fsel,.lm .finp{background:rgba(255,255,255,0.85);border-color:rgba(30,40,80,0.12);color:var(--tx);}
.lm .sort-wrap select{background:rgba(255,255,255,0.8);border-color:rgba(30,40,80,0.12);color:var(--tx);}
.lm .prov-card{background:rgba(255,255,255,0.92);}
.lm .skill-pill{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);}
.lm .btn-ghost{border-color:rgba(30,40,80,0.18);color:var(--tx-2);}
.lm .rk-gold{background:rgba(247,183,49,0.12);border-color:rgba(247,183,49,0.28);color:#B8860B;}
.lm .rk-blue{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan-d);}
.lm .rk-teal{background:rgba(31,217,160,0.1);border-color:rgba(31,217,160,0.2);color:#0F8A62;}
.lm .breadcrumb a,.lm .breadcrumb span{color:var(--tx-3);}
.lm .stat-strip-inner{background:rgba(255,255,255,0.85);}
.lm .view-toggles{background:rgba(255,255,255,0.8);}
.lm .sort-wrap select{background:rgba(255,255,255,0.8);}
.lm .chip{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan-d);}
.lm .empty-state{background:rgba(255,255,255,0.85);}
.lm .pag-btn{background:rgba(255,255,255,0.75);}
.lm .quick-filters{background:rgba(234,238,247,0.9);}
.lm .qf-pill{border-color:rgba(30,40,80,0.12);color:var(--tx-3);}
.lm .qf-pill:hover{border-color:var(--cyan-border);color:var(--cyan);}
.lm .qf-pill.active{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);}
.lm .filter-toggle-btn{background:var(--violet-dim);border-color:var(--violet-border);color:var(--violet);}
.lm .active-filter-count{background:var(--violet);}
.lm .s-badge-sm{background:var(--violet-dim);border-color:var(--violet-border);color:var(--violet);}
.lm .hero-stats-panel{background:rgba(255,255,255,0.85);border-color:rgba(30,40,80,0.1);}
.lm .badge-free{color:var(--tx-3);border-color:var(--bd);}
.lm .badge-verified{background:var(--green-dim);border-color:rgba(31,217,160,0.2);color:#0F8A62;}
.lm .badge-premium{color:var(--coral-d);}
.lm .prov-rate{color:var(--cyan);}
.lm .btn-indigo{background:linear-gradient(135deg,var(--violet),var(--violet-d));}
.lm .sidebar-card{background:rgba(255,255,255,0.88);}
.lm .trending-item:hover{background:rgba(0,158,149,0.05);}

/* ══ RESET ══ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);font-size:15px;line-height:1.65;overflow-x:hidden;transition:background .3s,color .3s;font-weight:400;-webkit-font-smoothing:antialiased;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
h1,h2,h3,h4,.logo-text,.btn{font-family:var(--fm);-webkit-font-smoothing:antialiased;}
.btn{font-weight:600;letter-spacing:0.01em;}

/* ══ NAVBAR ══ */
.navbar{position:fixed;top:0;left:0;right:0;z-index:1000;display:flex;align-items:center;justify-content:space-between;padding:0 5%;height:64px;background:rgba(12,14,20,0.84);backdrop-filter:blur(24px);border-bottom:1px solid var(--bd);transition:var(--e);}
.navbar.on{background:rgba(12,14,20,0.97);box-shadow:0 2px 30px rgba(0,0,0,0.5);}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none;flex-shrink:0;}
.logo-mark{width:36px;height:36px;flex-shrink:0;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px;color:#0C0E14;font-family:var(--fm);}
.logo-text{font-size:20px;font-weight:800;color:var(--tx);letter-spacing:-0.3px;}.logo-text span{color:var(--cyan);}
.nav-links{display:flex;align-items:center;gap:2px;}
.nav-links a{color:var(--tx-2);text-decoration:none;font-size:13.5px;font-weight:500;padding:6px 13px;border-radius:var(--rs);transition:var(--e);white-space:nowrap;}
.nav-links a:hover,.nav-links a.active{color:var(--tx);background:rgba(255,255,255,0.05);}
.nav-links a.active{color:var(--cyan);}
.nav-acts{display:flex;align-items:center;gap:8px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--rs);font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:var(--e);white-space:nowrap;line-height:1.3;}
.btn-lg{padding:13px 28px;font-size:14px;border-radius:14px;}
.btn-ghost{background:transparent;color:var(--tx-2);border:1px solid var(--bd);}
.btn-ghost:hover{background:rgba(255,255,255,0.06);border-color:var(--bd2);color:var(--tx);}
.btn-gold{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:700;box-shadow:0 3px 18px var(--gO);}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 8px 28px var(--gO);}
.btn-blue{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;font-weight:700;box-shadow:0 3px 18px var(--gC);}
.btn-blue:hover{transform:translateY(-2px);box-shadow:0 8px 28px var(--gC);}
.btn-green{background:linear-gradient(135deg,var(--green),var(--green-d));color:#0C0E14;font-weight:700;}
.btn-green:hover{transform:translateY(-2px);}
.btn-indigo{background:linear-gradient(135deg,var(--violet),var(--violet-d));color:#fff;font-weight:700;}
.btn-indigo:hover{transform:translateY(-2px);}
.btn-theme{background:transparent;color:var(--tx-2);border:1px solid var(--bd);border-radius:var(--rs);padding:7px 11px;cursor:pointer;font-size:14px;transition:var(--e);line-height:1;font-family:var(--fb);}
.btn-theme:hover{background:rgba(255,255,255,0.07);}
.ham{display:none;flex-direction:column;gap:4.5px;cursor:pointer;padding:8px;}
.ham span{display:block;width:20px;height:2px;background:var(--tx);border-radius:2px;transition:var(--e);}
.mobile-nav{display:none;position:fixed;top:64px;left:0;right:0;background:rgba(12,14,20,0.98);backdrop-filter:blur(24px);border-bottom:1px solid var(--bd);padding:14px 5%;z-index:999;flex-direction:column;gap:4px;}
.mobile-nav.open{display:flex;}
.mobile-nav a{color:var(--tx-2);text-decoration:none;padding:10px 14px;border-radius:var(--rs);font-size:14px;font-weight:500;transition:var(--e);}
.mobile-nav a:hover{color:var(--tx);background:rgba(255,255,255,0.05);}

/* ══ HERO ══ */
.hero-providers{
  position:relative;overflow:hidden;
  background:linear-gradient(180deg,#0C0E14 0%,#10141E 100%);
  padding:104px 5% 0;
}
.hero-providers::before{content:'';position:absolute;top:-140px;right:-60px;width:680px;height:680px;border-radius:50%;background:radial-gradient(circle,rgba(0,212,200,0.08),transparent 65%);pointer-events:none;}
.hero-providers::after{content:'';position:absolute;bottom:-80px;left:-80px;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(255,107,74,0.06),transparent 65%);pointer-events:none;}
.hero-grid-bg{position:absolute;inset:0;background-image:linear-gradient(rgba(0,212,200,0.03) 1px,transparent 1px),linear-gradient(90deg,rgba(0,212,200,0.03) 1px,transparent 1px);background-size:54px 54px;pointer-events:none;}
.hero-inner-prov{position:relative;z-index:1;max-width:1160px;margin:0 auto;}

/* breadcrumb */
.breadcrumb{display:flex;align-items:center;gap:8px;font-size:12px;margin-bottom:22px;}
.breadcrumb a{color:var(--tx-3);text-decoration:none;transition:var(--e);}
.breadcrumb a:hover{color:var(--cyan);}
.breadcrumb span{color:var(--tx-3);}
.breadcrumb .sep{color:var(--tx-3);opacity:0.4;}

.hero-row{display:flex;align-items:flex-start;justify-content:space-between;gap:32px;flex-wrap:wrap;padding-bottom:40px;}
.hero-text-prov{flex:1;min-width:260px;max-width:560px;}
.s-badge-sm{display:inline-block;background:var(--violet-dim);border:1px solid var(--violet-border);color:var(--violet);padding:4px 13px;border-radius:50px;font-size:10.5px;font-weight:700;font-family:var(--fm);letter-spacing:1.2px;text-transform:uppercase;margin-bottom:14px;}
.hero-text-prov h1{font-family:var(--fm);font-size:clamp(28px,4vw,52px);font-weight:800;line-height:1.1;letter-spacing:-0.4px;margin-bottom:12px;}
.hero-text-prov h1 .gold{color:var(--cyan);}
.hero-text-prov p{color:var(--tx-2);font-size:15px;max-width:460px;line-height:1.75;font-weight:400;}

/* hero stats panel */
.hero-stats-panel{background:rgba(19,22,30,0.85);backdrop-filter:blur(16px);border:1px solid var(--bd2);border-radius:16px;padding:20px 22px;display:grid;grid-template-columns:repeat(2,1fr);gap:18px 28px;flex-shrink:0;align-self:flex-start;}
.hero-stat{text-align:center;}
.hero-stat-n{font-family:var(--fm);font-size:24px;font-weight:800;line-height:1.1;background:linear-gradient(135deg,var(--cyan-l),var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.hero-stat-l{font-size:10px;color:var(--tx-3);margin-top:3px;white-space:nowrap;text-transform:uppercase;letter-spacing:.6px;font-family:var(--fm);}

/* ══ STAT STRIP (below hero) ══ */
.stat-strip{max-width:1160px;margin:0 auto;padding:0 5% 28px;}
.stat-strip-inner{display:grid;grid-template-columns:repeat(5,1fr);gap:1px;background:var(--bd);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;}
.stat-mini{background:var(--glass);backdrop-filter:blur(14px);padding:14px 12px;text-align:center;transition:var(--e);}
.stat-mini:hover{background:rgba(0,212,200,0.04);}
.sm-val{font-family:var(--fm);font-size:19px;font-weight:800;line-height:1.1;margin-bottom:3px;background:linear-gradient(135deg,var(--cyan-l),var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.sm-lbl{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;}

/* ══ STICKY SEARCH STRIP ══ */
.search-strip{background:rgba(12,14,20,0.96);backdrop-filter:blur(20px);border-top:1px solid var(--bd);border-bottom:1px solid var(--bd);padding:14px 5%;position:sticky;top:64px;z-index:90;}
.search-strip-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.search-input-wrap{flex:1;min-width:200px;display:flex;align-items:center;gap:10px;background:var(--s2);border:1px solid var(--bd);border-radius:var(--rs);padding:9px 14px;}
.search-input-wrap:focus-within{border-color:var(--cyan-border);background:rgba(0,212,200,0.04);}
.search-input-wrap input{flex:1;background:transparent;border:none;outline:none;color:var(--tx);font-size:14px;font-family:var(--fb);}
.search-input-wrap input::placeholder{color:var(--tx-3);}
.filter-toggle-btn{display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:var(--rs);background:var(--violet-dim);border:1px solid var(--violet-border);color:var(--violet);font-size:13px;font-weight:600;cursor:pointer;transition:var(--e);font-family:var(--fm);}
.filter-toggle-btn:hover{background:rgba(124,111,247,0.18);}
.filter-toggle-btn.active{background:rgba(124,111,247,0.2);border-color:rgba(124,111,247,0.45);color:#A99EFF;}
.active-filter-count{background:var(--violet);color:#fff;width:18px;height:18px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;}

/* ══ FILTER PANEL ══ */
.filter-panel{background:rgba(13,16,22,0.99);backdrop-filter:blur(20px);border-bottom:1px solid var(--bd);padding:0 5%;max-height:0;overflow:hidden;transition:max-height .4s ease,padding .4s ease;position:sticky;top:114px;z-index:89;}
.filter-panel.open{max-height:500px;padding:22px 5%;}
.filter-panel-inner{max-width:1160px;margin:0 auto;display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap;}
.fg{display:flex;flex-direction:column;gap:5px;}
.flbl{font-size:10px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:1px;font-family:var(--fm);}
.fsel,.finp{background:var(--s2);border:1px solid var(--bd);border-radius:var(--rs);padding:9px 13px;color:var(--tx);font-family:var(--fb);font-size:13px;outline:none;transition:var(--e);}
.fsel:focus,.finp:focus{border-color:var(--cyan-border);background:rgba(0,212,200,0.04);}
.fsel option{background:#191D27;}
.finp{width:110px;}
.filter-actions{display:flex;gap:8px;align-items:flex-end;margin-left:auto;}

/* ══ QUICK FILTER PILLS ══ */
.quick-filters{background:rgba(12,14,20,0.6);border-bottom:1px solid var(--bd);padding:10px 5%;}
.qf-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;gap:8px;overflow-x:auto;padding-bottom:2px;-ms-overflow-style:none;scrollbar-width:none;}
.qf-inner::-webkit-scrollbar{display:none;}
.qf-pill{display:inline-flex;align-items:center;gap:5px;padding:5px 13px;border-radius:50px;font-size:12px;font-weight:600;cursor:pointer;transition:var(--e);border:1px solid var(--bd);color:var(--tx-3);white-space:nowrap;text-decoration:none;font-family:var(--fm);}
.qf-pill:hover{border-color:var(--cyan-border);color:var(--cyan);}
.qf-pill.active{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);}

/* ══ MAIN LAYOUT ══ */
.main-wrap{max-width:1160px;margin:0 auto;padding:28px 5% 80px;}

/* results bar */
.results-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:14px;flex-wrap:wrap;}
.results-info{font-size:13.5px;color:var(--tx-3);font-weight:400;}
.results-info strong{color:var(--tx);font-family:var(--fm);font-weight:700;}
.sort-wrap{display:flex;align-items:center;gap:8px;}
.sort-wrap label{font-size:11px;color:var(--tx-3);font-weight:700;font-family:var(--fm);text-transform:uppercase;letter-spacing:.7px;}
.sort-wrap select{background:rgba(255,255,255,0.04);border:1px solid var(--bd);border-radius:var(--rs);padding:8px 13px;color:var(--tx);font-family:var(--fb);font-size:13px;outline:none;cursor:pointer;transition:var(--e);}
.sort-wrap select:focus{border-color:var(--cyan-border);}
.sort-wrap select option{background:#1E2A3A;}
.view-toggles{display:flex;gap:4px;background:rgba(255,255,255,0.04);border:1px solid var(--bd);border-radius:var(--rs);padding:3px;}
.vt{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--e);color:var(--tx-3);}
.vt.active,.vt:hover{background:var(--cyan-dim);color:var(--cyan);}

/* active chips */
.chips-row{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:20px;}
.chip{display:flex;align-items:center;gap:5px;background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:4px 10px;border-radius:50px;font-size:11.5px;font-weight:600;font-family:var(--fm);}
.chip a{color:inherit;text-decoration:none;opacity:0.7;margin-left:2px;}
.chip a:hover{opacity:1;}

/* ══ PROVIDER GRID ══ */
.prov-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;}
.prov-grid.list-view{grid-template-columns:1fr;}
.prov-grid.list-view .prov-card{flex-direction:row;}
.prov-grid.list-view .prov-img-wrap{width:180px;height:auto;min-height:160px;flex-shrink:0;border-radius:var(--r) 0 0 var(--r);}
.prov-grid.list-view .prov-body{padding:22px;}

/* ══ PROVIDER CARD ══ */
.prov-card{background:var(--s2);border:1px solid var(--bd);border-radius:var(--r);transition:var(--e);position:relative;overflow:hidden;display:flex;flex-direction:column;animation:fadeUp .45s ease both;}
.prov-card:hover{transform:translateY(-6px);border-color:var(--cyan-border);box-shadow:0 20px 52px rgba(0,0,0,0.45),0 0 0 1px var(--cyan-border);}
.prov-card.featured-card{border-color:var(--coral-border);background:linear-gradient(160deg,rgba(31,27,20,1),rgba(25,22,16,1));}
.prov-card.featured-card::after{content:'';position:absolute;top:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--coral),transparent);}

/* avatar */
.prov-img-wrap{width:100%;height:170px;position:relative;overflow:hidden;background:linear-gradient(135deg,#1A1630,#0F2030);}
.prov-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .5s ease;}
.prov-card:hover .prov-img-wrap img{transform:scale(1.06);}
.prov-initials{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:54px;font-weight:900;color:rgba(255,255,255,0.15);}
.prov-verified-badge{position:absolute;top:10px;right:10px;background:rgba(31,217,160,0.85);color:#0C0E14;padding:3px 9px;border-radius:50px;font-size:10px;font-weight:700;backdrop-filter:blur(8px);font-family:var(--fm);}
.featured-ribbon{position:absolute;top:10px;left:10px;background:rgba(255,107,74,0.9);color:#fff;padding:3px 9px;border-radius:50px;font-size:10px;font-weight:700;backdrop-filter:blur(8px);font-family:var(--fm);}
.avail-dot{position:absolute;bottom:10px;left:10px;display:flex;align-items:center;gap:5px;background:rgba(12,14,20,0.82);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.08);padding:3px 9px;border-radius:50px;font-size:10px;font-weight:600;}
.dot-green{width:6px;height:6px;border-radius:50%;background:var(--green);animation:pls 2s ease-in-out infinite;}
.dot-amber{width:6px;height:6px;border-radius:50%;background:var(--amber);}
.dot-red{width:6px;height:6px;border-radius:50%;background:var(--red);}
@keyframes pls{0%,100%{box-shadow:0 0 0 0 rgba(31,217,160,0.5);}50%{box-shadow:0 0 0 5px rgba(31,217,160,0);}}

/* img overlay on hover */
.prov-img-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;gap:8px;opacity:0;transition:opacity .3s;backdrop-filter:blur(3px);}
.prov-card:hover .prov-img-overlay{opacity:1;}
.poi-btn{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;backdrop-filter:blur(10px);font-size:14px;text-decoration:none;border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.1);transition:var(--e);}
.poi-btn:hover{background:var(--cyan);border-color:var(--cyan);color:#0C0E14;}

.prov-body{padding:18px;flex:1;display:flex;flex-direction:column;}
.prov-name{font-size:15px;font-weight:700;margin-bottom:2px;font-family:var(--fm);letter-spacing:-0.1px;}
.prov-tag{color:var(--tx-3);font-size:12.5px;margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:400;}
.prov-loc{display:flex;align-items:center;gap:4px;font-size:11.5px;color:var(--tx-3);margin-bottom:9px;font-weight:400;}

/* badge row */
.badge-row{display:flex;gap:5px;margin-bottom:9px;flex-wrap:wrap;}
.badge-free{background:rgba(78,90,110,0.15);border:1px solid rgba(78,90,110,0.22);color:var(--tx-3);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:600;font-family:var(--fm);}
.badge-verified{background:var(--green-dim);border:1px solid rgba(31,217,160,0.22);color:var(--green);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:600;font-family:var(--fm);}
.badge-premium{background:var(--coral-dim);border:1px solid var(--coral-border);color:var(--coral);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);}

.prov-stars{color:var(--amber);font-size:12px;letter-spacing:1px;margin-bottom:3px;}
.prov-rc{font-size:11px;color:var(--tx-3);margin-bottom:11px;}

/* progress bar for success rate */
.success-bar-wrap{margin-bottom:11px;}
.success-bar-lbl{display:flex;justify-content:space-between;font-size:10.5px;color:var(--tx-3);margin-bottom:4px;}
.success-bar-track{height:4px;background:rgba(255,255,255,0.05);border-radius:3px;overflow:hidden;}
.success-bar-fill{height:100%;background:linear-gradient(90deg,var(--cyan),var(--green));border-radius:3px;transition:width 1s ease;}

.prov-pills{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:14px;flex:1;}
.skill-pill{background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:500;}
.rk-gold{background:rgba(247,183,49,0.12);border:1px solid rgba(247,183,49,0.28);color:var(--amber);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.rk-blue{background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.rk-teal{background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.rk-dim{background:rgba(78,90,110,0.12);border:1px solid rgba(78,90,110,0.18);color:var(--tx-3);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.prov-foot{display:flex;align-items:center;justify-content:space-between;padding-top:13px;border-top:1px solid var(--bd);}
.prov-rate{font-family:var(--fm);font-weight:700;font-size:18px;color:var(--cyan);}
.prov-rate small{font-size:11px;color:var(--tx-3);font-weight:400;}
.prov-actions{display:flex;gap:6px;}

/* response time meta */
.prov-resp{font-size:10.5px;color:var(--tx-3);display:flex;align-items:center;gap:3px;margin-bottom:8px;}

/* ══ TRENDING SIDEBAR ══ */
.page-layout{display:grid;grid-template-columns:1fr 260px;gap:28px;align-items:start;}

.sidebar{}
.sidebar-card{background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-bottom:16px;}
.sc-head{padding:13px 16px;border-bottom:1px solid var(--bd);font-family:var(--fm);font-size:11.5px;font-weight:700;color:var(--tx-2);text-transform:uppercase;letter-spacing:.8px;}
.sc-body{padding:14px;}
.trending-item{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:9px;text-decoration:none;color:var(--tx);transition:var(--e);font-size:13px;}
.trending-item:hover{background:var(--cyan-dim);color:var(--cyan);}
.tr-num{font-family:var(--fm);font-size:10px;font-weight:800;color:#0C0E14;background:var(--cyan);padding:1px 6px;border-radius:4px;flex-shrink:0;}
.tr-icon{font-size:15px;flex-shrink:0;}

/* top-rated mini card */
.mini-prov{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:9px;text-decoration:none;color:var(--tx);transition:var(--e);margin-bottom:7px;}
.mini-prov:last-child{margin-bottom:0;}
.mini-prov:hover{background:rgba(255,255,255,0.04);}
.mp-av{width:38px;height:38px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:13px;font-weight:700;color:#fff;}
.mp-av img{width:100%;height:100%;object-fit:cover;}
.mp-name{font-weight:700;font-size:12.5px;font-family:var(--fm);}
.mp-tag{font-size:11px;color:var(--tx-3);}
.mp-rate{font-size:12px;color:var(--cyan);font-weight:700;font-family:var(--fm);margin-left:auto;white-space:nowrap;}

/* ══ EMPTY STATE ══ */
.empty-state{text-align:center;padding:72px 24px;background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);}
.empty-state .icon{font-size:48px;margin-bottom:16px;}
.empty-state h3{font-family:var(--fm);font-size:22px;font-weight:700;margin-bottom:8px;}
.empty-state p{color:var(--tx-3);font-size:14px;line-height:1.7;max-width:380px;margin:0 auto 22px;}

/* ══ PAGINATION ══ */
.pagination{display:flex;gap:6px;justify-content:center;margin-top:42px;flex-wrap:wrap;}
.pag-btn{padding:9px 16px;border-radius:var(--rs);text-decoration:none;font-size:13.5px;font-weight:600;font-family:var(--fm);color:var(--tx-3);background:var(--s2);border:1px solid var(--bd);transition:var(--e);}
.pag-btn:hover{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}
.pag-btn.active{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}
.pag-btn.disabled{opacity:0.3;pointer-events:none;}

/* ══ FOOTER STRIP ══ */
.footer-strip{border-top:1px solid var(--bd);padding:28px 5%;background:var(--s1);}
.footer-strip-inner{max-width:1160px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;}
.footer-copy{color:var(--tx-3);font-size:12px;}
.footer-nav{display:flex;gap:18px;}
.footer-nav a{color:var(--tx-3);text-decoration:none;font-size:12px;transition:var(--e);}
.footer-nav a:hover{color:var(--cyan);}

/* ══ SKELETON LOADER ══ */
.skeleton{background:linear-gradient(90deg,var(--s2) 25%,var(--s3) 50%,var(--s2) 75%);background-size:400% 100%;animation:shimmer 1.5s ease infinite;border-radius:6px;}
@keyframes shimmer{0%{background-position:100% 0;}100%{background-position:-100% 0;}}

/* ══ TOAST ══ */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);max-width:330px;min-width:240px;box-shadow:0 12px 32px rgba(0,0,0,.5);animation:toastIn .35s ease;backdrop-filter:blur(14px);}
.toast.success{border-left:3px solid var(--green);}
.toast.error{border-left:3px solid var(--red);}
.toast.info{border-left:3px solid var(--cyan);}
.t-ico{font-size:16px;flex-shrink:0;}.t-bod{flex:1;}.t-ttl{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}.t-msg{font-size:11px;color:var(--tx-3);}.t-cls{cursor:pointer;color:var(--tx-3);font-size:17px;}
@keyframes toastIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}

/* ══ ANIMATIONS ══ */
@keyframes fadeUp{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}
@keyframes fadU{from{opacity:0;transform:translateY(-14px);}to{opacity:1;transform:translateY(0);}}

/* ══ BACK TO TOP ══ */
.back-top{position:fixed;bottom:24px;right:22px;z-index:990;width:40px;height:40px;border-radius:11px;background:var(--s2);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;color:var(--tx-2);font-size:16px;cursor:pointer;transition:var(--e);opacity:0;pointer-events:none;}
.back-top.show{opacity:1;pointer-events:auto;}
.back-top:hover{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}

/* ══ RESPONSIVE ══ */
@media(max-width:1100px){.page-layout{grid-template-columns:1fr;}.sidebar{display:none;}}
@media(max-width:1024px){.prov-grid{grid-template-columns:repeat(2,1fr);}.stat-strip-inner{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){
  .nav-links,.nav-acts{display:none;}.ham{display:flex;}
  /* Hero stacks cleanly — text first, stats panel full width below */
  .hero-row{flex-direction:column;align-items:stretch;gap:22px;padding-bottom:28px;}
  .hero-text-prov{max-width:100%;}
  .hero-text-prov h1{font-size:clamp(26px,7vw,38px);}
  .hero-text-prov p{max-width:100%;font-size:14px;}
  .hero-stats-panel{grid-template-columns:repeat(4,1fr);gap:12px 10px;padding:16px 14px;}
  .hero-stat-n{font-size:18px;}
  .hero-stat-l{font-size:9px;}
  /* Search strip stacks */
  .search-strip-inner{flex-direction:column;gap:8px;}
  .search-input-wrap{width:100%;}
  /* Rest */
  .prov-grid{grid-template-columns:1fr;}
  .prov-grid.list-view .prov-card{flex-direction:column;}
  .prov-grid.list-view .prov-img-wrap{width:100%;height:150px;border-radius:var(--r) var(--r) 0 0;}
  .filter-panel-inner{flex-direction:column;}
  .finp{width:100%;}
  .results-bar{flex-direction:column;align-items:flex-start;}
  .hero-providers{padding-top:88px;}
  .stat-strip-inner{grid-template-columns:repeat(3,1fr);}
  .stat-strip-inner .stat-mini:nth-child(4),.stat-strip-inner .stat-mini:nth-child(5){display:none;}
}
@media(max-width:480px){
  .hero-stats-panel{grid-template-columns:repeat(2,1fr);}
  .stat-strip-inner{grid-template-columns:repeat(2,1fr);}
  .stat-strip-inner .stat-mini:nth-child(5){display:none;}
}
</style>
</head>
<body>

<!-- ══════ NAVBAR ══════ -->
<nav class="navbar" id="nav">
  <a href="<?= APP_URL ?>/index.php" class="logo">
    <div class="logo-mark">G</div>
    <span class="logo-text">Gig<span>Ghana</span></span>
  </a>
  <div class="nav-links">
    <a href="<?= APP_URL ?>/search/providers.php" class="active">Find Talent</a>
    <a href="<?= APP_URL ?>/jobs.php">Browse Jobs</a>
    <a href="<?= APP_URL ?>/index.php#how">How It Works</a>
    <a href="<?= APP_URL ?>/index.php#categories">Categories</a>
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
  <?php if (isLoggedIn()): ?>
    <a href="<?= APP_URL ?>/<?= $user['role'] ?>/dashboard.php">Dashboard</a>
    <a href="<?= APP_URL ?>/auth/logout.php">Sign Out</a>
  <?php else: ?>
    <a href="<?= APP_URL ?>/auth/login.php">Sign In</a>
    <a href="<?= APP_URL ?>/auth/register.php">Get Started Free</a>
  <?php endif; ?>
</div>

<!-- ══════ PAGE HERO ══════ -->
<div class="hero-providers">
  <div class="hero-grid-bg"></div>
  <div class="hero-inner-prov">
    <div class="breadcrumb">
      <a href="<?= APP_URL ?>/index.php">Home</a>
      <span class="sep">›</span>
      <span>Find Talent</span>
      <?php if ($q): ?><span class="sep">›</span><span style="color:var(--cyan);"><?= sanitize($q) ?></span><?php endif; ?>
    </div>
    <div class="hero-row">
      <div class="hero-text-prov">
        <div class="s-badge-sm">Freelance Talent</div>
        <h1>Discover <span class="gold">Expert</span> Freelancers<br>Across Ghana</h1>
        <p>Vetted professionals — developers, designers, carpenters, nurses &amp; more — ready to bring your project to life with world-class results.</p>
      </div>
      <div class="hero-stats-panel">
        <div class="hero-stat">
          <div class="hero-stat-n" data-count="<?= $totalProvs ?>" data-animated="false">0</div>
          <div class="hero-stat-l">Results Found</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-n">4.8★</div>
          <div class="hero-stat-l">Avg. Rating</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-n">24hr</div>
          <div class="hero-stat-l">Avg. Response</div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-n">🔒</div>
          <div class="hero-stat-l">Secure Escrow</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ══════ STAT STRIP ══════ -->
<div class="stat-strip">
  <div class="stat-strip-inner">
    <div class="stat-mini"><div class="sm-val" data-count="<?= $totalProvs ?>">0</div><div class="sm-lbl">Freelancers</div></div>
    <div class="stat-mini"><div class="sm-val">4.8★</div><div class="sm-lbl">Avg Rating</div></div>
    <div class="stat-mini"><div class="sm-val">₵49</div><div class="sm-lbl">Avg Hourly</div></div>
    <div class="stat-mini"><div class="sm-val">98%</div><div class="sm-lbl">Success Rate</div></div>
    <div class="stat-mini"><div class="sm-val">&lt;1hr</div><div class="sm-lbl">Response Time</div></div>
  </div>
</div>

<!-- ══════ STICKY SEARCH STRIP ══════ -->
<div class="search-strip" id="searchStrip">
  <form method="GET" id="filterForm">
    <div class="search-strip-inner">
      <div class="search-input-wrap">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--tx-3);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="text" name="q" id="searchInput" placeholder="Search by name, skill, profession…" value="<?= htmlspecialchars($q) ?>" autocomplete="off">
      </div>
      <div class="search-input-wrap" style="flex:0 0 auto;min-width:160px;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:var(--tx-3);flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        <input type="text" name="location" placeholder="Region or City…" value="<?= htmlspecialchars($location) ?>">
      </div>
      <button type="submit" class="btn btn-gold">Search</button>
      <button type="button" class="filter-toggle-btn <?= $activeCount?'active':'' ?>" onclick="toggleFilters()" id="filterToggleBtn">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        Filters
        <?php if($activeCount): ?><span class="active-filter-count"><?= $activeCount ?></span><?php endif; ?>
      </button>
    </div>
  </form>
</div>

<!-- ══════ FILTER PANEL ══════ -->
<div class="filter-panel" id="filterPanel">
  <form method="GET" id="filterFormFull">
    <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
    <input type="hidden" name="location" value="<?= htmlspecialchars($location) ?>">
    <div class="filter-panel-inner">
      <div class="fg">
        <div class="flbl">Category</div>
        <select name="category" class="fsel">
          <option value="">All Categories</option>
          <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>" <?= $catId===$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="fg">
        <div class="flbl">Experience Level</div>
        <select name="experience" class="fsel">
          <option value="">Any Level</option>
          <option value="entry"        <?= $expLevel==='entry'       ?'selected':'' ?>>Entry</option>
          <option value="intermediate" <?= $expLevel==='intermediate'?'selected':'' ?>>Intermediate</option>
          <option value="expert"       <?= $expLevel==='expert'      ?'selected':'' ?>>Expert</option>
        </select>
      </div>
      <div class="fg">
        <div class="flbl">Availability</div>
        <select name="availability" class="fsel">
          <option value="">Any</option>
          <option value="full_time" <?= $avail==='full_time'?'selected':'' ?>>Full-time</option>
          <option value="part_time" <?= $avail==='part_time'?'selected':'' ?>>Part-time</option>
        </select>
      </div>
      <div class="fg">
        <div class="flbl">Badge</div>
        <select name="badge" class="fsel">
          <option value="">Any Badge</option>
          <option value="verified" <?= $badge==='verified'?'selected':'' ?>>✓ Verified</option>
          <option value="featured" <?= $badge==='featured'?'selected':'' ?>>⭐ Featured</option>
        </select>
      </div>
      <div class="fg">
        <div class="flbl">Min Rating</div>
        <select name="min_rating" class="fsel">
          <option value="">Any Rating</option>
          <option value="4" <?= $minRating==4?'selected':'' ?>>4★ & Up</option>
          <option value="4.5" <?= $minRating==4.5?'selected':'' ?>>4.5★ & Up</option>
          <option value="5" <?= $minRating==5?'selected':'' ?>>5★ Only</option>
        </select>
      </div>
      <div class="fg">
        <div class="flbl">Min Rate (₵/hr)</div>
        <input type="number" name="min_rate" class="finp" placeholder="0" value="<?= $minRate ?: '' ?>">
      </div>
      <div class="fg">
        <div class="flbl">Max Rate (₵/hr)</div>
        <input type="number" name="max_rate" class="finp" placeholder="Any" value="<?= $maxRate ?: '' ?>">
      </div>
      <div class="filter-actions">
        <button type="submit" class="btn btn-indigo">Apply Filters</button>
        <?php if ($activeCount): ?>
        <a href="<?= APP_URL ?>/search/providers.php<?= $q?'?q='.urlencode($q):'' ?>" class="btn btn-ghost">Clear All</a>
        <?php endif; ?>
      </div>
    </div>
  </form>
</div>

<!-- ══════ QUICK FILTER PILLS ══════ -->
<div class="quick-filters">
  <div class="qf-inner">
    <span style="font-size:11px;font-weight:700;color:var(--tx-3);font-family:var(--fm);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;flex-shrink:0;">Quick:</span>
    <?php
    $quickFilters = [
      ['💻', 'Developers', ['q'=>'developer']],
      ['🎨', 'Designers', ['q'=>'designer']],
      ['🔧', 'Skilled Trades', ['experience'=>'expert']],
      ['🏥', 'Health & Care', ['q'=>'nurse']],
      ['🍽️', 'Chefs', ['q'=>'chef']],
      ['📷', 'Photographers', ['q'=>'photographer']],
      ['⭐', 'Top Rated', ['min_rating'=>'4.5']],
      ['✓', 'Verified Only', ['badge'=>'verified']],
      ['🆓', 'Full-time', ['availability'=>'full_time']],
    ];
    foreach ($quickFilters as [$icon, $label, $params]):
      $qStr = http_build_query($params);
      $isActive = false;
      foreach ($params as $k=>$v) {
        if (($k==='q'&&$q===$v)||($k==='min_rating'&&$minRating==(float)$v)||($k==='badge'&&$badge===$v)||($k==='availability'&&$avail===$v)) $isActive=true;
      }
    ?>
    <a href="<?= APP_URL ?>/search/providers.php?<?= $qStr ?>" class="qf-pill <?= $isActive?'active':'' ?>"><?= $icon ?> <?= $label ?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- ══════ MAIN CONTENT ══════ -->
<div class="main-wrap">

  <!-- Active filter chips -->
  <?php
  $chips = [];
  if($q)        $chips[] = ['label'=>'Search: '.$q, 'clear'=>'?'.http_build_query(array_merge($_GET,['q'=>'','page'=>1]))];
  if($location) $chips[] = ['label'=>'📍 '.$location, 'clear'=>'?'.http_build_query(array_merge($_GET,['location'=>'','page'=>1]))];
  if($catId)    { $catName = array_column($cats,'name','id')[$catId]??'Category'; $chips[] = ['label'=>$catName, 'clear'=>'?'.http_build_query(array_merge($_GET,['category'=>'','page'=>1]))]; }
  if($expLevel) $chips[] = ['label'=>ucfirst($expLevel), 'clear'=>'?'.http_build_query(array_merge($_GET,['experience'=>'','page'=>1]))];
  if($avail)    $chips[] = ['label'=>str_replace('_',' ',ucfirst($avail)), 'clear'=>'?'.http_build_query(array_merge($_GET,['availability'=>'','page'=>1]))];
  if($badge)    $chips[] = ['label'=>ucfirst($badge).' badge', 'clear'=>'?'.http_build_query(array_merge($_GET,['badge'=>'','page'=>1]))];
  if($minRating)$chips[] = ['label'=>$minRating.'★ min rating', 'clear'=>'?'.http_build_query(array_merge($_GET,['min_rating'=>'','page'=>1]))];
  if($minRate)  $chips[] = ['label'=>'Min ₵'.$minRate.'/hr', 'clear'=>'?'.http_build_query(array_merge($_GET,['min_rate'=>'','page'=>1]))];
  if($maxRate)  $chips[] = ['label'=>'Max ₵'.$maxRate.'/hr', 'clear'=>'?'.http_build_query(array_merge($_GET,['max_rate'=>'','page'=>1]))];
  ?>
  <?php if(!empty($chips)): ?>
  <div class="chips-row">
    <?php foreach($chips as $chip): ?><div class="chip"><?= sanitize($chip['label']) ?><a href="<?= $chip['clear'] ?>">✕</a></div><?php endforeach; ?>
    <a href="<?= APP_URL ?>/search/providers.php" class="chip" style="opacity:.55;">Clear all</a>
  </div>
  <?php endif; ?>

  <div class="page-layout">
    <div><!-- MAIN COLUMN -->

      <!-- Results bar -->
      <div class="results-bar">
        <div class="results-info">
          Showing <strong><?= number_format(min($offset+1,$totalProvs)) ?>–<?= number_format(min($offset+$perPage,$totalProvs)) ?></strong>
          of <strong><?= number_format($totalProvs) ?></strong> freelancer<?= $totalProvs!=1?'s':'' ?>
          <?php if($q): ?> for "<strong><?= sanitize($q) ?></strong>"<?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
          <div class="sort-wrap">
            <label>Sort:</label>
            <select onchange="window.location='?<?= http_build_query(array_merge($_GET,['sort'=>'__S__','page'=>1])) ?>'.replace('__S__',this.value)">
              <option value="rating"    <?= $sort==='rating'   ?'selected':'' ?>>Top Rated</option>
              <option value="jobs"      <?= $sort==='jobs'     ?'selected':'' ?>>Most Jobs</option>
              <option value="newest"    <?= $sort==='newest'   ?'selected':'' ?>>Newest</option>
              <option value="rate_low"  <?= $sort==='rate_low' ?'selected':'' ?>>Rate ↑</option>
              <option value="rate_high" <?= $sort==='rate_high'?'selected':'' ?>>Rate ↓</option>
            </select>
          </div>
          <div class="view-toggles" id="viewToggles">
            <div class="vt active" id="gridBtn" onclick="setView('grid')" title="Grid view">
              <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2.5A1.5 1.5 0 012.5 1h3A1.5 1.5 0 017 2.5v3A1.5 1.5 0 015.5 7h-3A1.5 1.5 0 011 5.5v-3zm8 0A1.5 1.5 0 0110.5 1h3A1.5 1.5 0 0115 2.5v3A1.5 1.5 0 0113.5 7h-3A1.5 1.5 0 019 5.5v-3zm-8 8A1.5 1.5 0 012.5 9h3A1.5 1.5 0 017 10.5v3A1.5 1.5 0 015.5 15h-3A1.5 1.5 0 011 13.5v-3zm8 0A1.5 1.5 0 0110.5 9h3a1.5 1.5 0 011.5 1.5v3a1.5 1.5 0 01-1.5 1.5h-3A1.5 1.5 0 019 13.5v-3z"/></svg>
            </div>
            <div class="vt" id="listBtn" onclick="setView('list')" title="List view">
              <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5z"/></svg>
            </div>
          </div>
        </div>
      </div>

      <?php if (empty($providers)): ?>
      <!-- Empty state -->
      <div class="empty-state">
        <div class="icon">🔍</div>
        <h3>No freelancers found</h3>
        <p>Try adjusting your search terms or filters. Ghana's best talent is waiting — broaden your search!</p>
        <a href="<?= APP_URL ?>/search/providers.php" class="btn btn-gold btn-lg">Clear All Filters</a>
      </div>

      <?php else: ?>
      <!-- Provider grid -->
      <div class="prov-grid" id="provGrid">
        <?php foreach ($providers as $idx => $pv):
          $sk   = is_array($pv['skills']) ? $pv['skills'] : array_filter(explode('|', $pv['skills'] ?? ''));
          $rk   = rankLabel((int)$pv['completed_jobs']);
          $init = initials($pv['first_name'],$pv['last_name']);
          $rv   = (float)$pv['rating_avg'];
          $jobs = (int)$pv['completed_jobs'];
          $bt   = $jobs>=20?'premium':($jobs>=5?'verified':'free');
          $avMap = ['full_time'=>['lbl'=>'Available','dot'=>'dot-green'],'part_time'=>['lbl'=>'Part-time','dot'=>'dot-amber'],'not_available'=>['lbl'=>'Unavailable','dot'=>'dot-red']];
          $avInfo = $avMap[$pv['availability'] ?? 'full_time'] ?? $avMap['full_time'];
          $delay = ($idx % 3) * 80;
          $sr = min(100, (int)($pv['success_rate'] ?? ($jobs>=10 ? 95 : ($jobs>=5 ? 90 : 80))));
        ?>
        <div class="prov-card <?= $pv['is_featured'] ? 'featured-card' : '' ?>" style="animation-delay:<?= $delay ?>ms;">
          <div class="prov-img-wrap">
            <?php if (!empty($pv['avatar'])): ?>
              <img src="<?= sanitize($pv['avatar']) ?>" alt="<?= sanitize($pv['first_name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="prov-initials"><?= $init ?></div>
            <?php endif; ?>
            <?php if ($pv['is_verified']): ?><div class="prov-verified-badge">✓ Verified</div><?php endif; ?>
            <?php if ($pv['is_featured']): ?><div class="featured-ribbon">⭐ Featured</div><?php endif; ?>
            <div class="avail-dot">
              <div class="<?= $avInfo['dot'] ?>"></div>
              <span style="color:var(--tx-2);font-size:10px;"><?= $avInfo['lbl'] ?></span>
            </div>
            <!-- Hover overlay -->
            <div class="prov-img-overlay">
              <a href="<?= APP_URL ?>/profile.php?id=<?= $pv['user_id'] ?>" class="poi-btn" title="View Profile">👁</a>
              <?php if (isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
                <a href="<?= APP_URL ?>/client/messages.php?start=<?= $pv['user_id'] ?>" class="poi-btn" title="Send Message">💬</a>
              <?php endif; ?>
            </div>
          </div>
          <div class="prov-body">
            <div class="prov-name"><?= sanitize($pv['first_name'].' '.$pv['last_name']) ?></div>
            <div class="prov-tag"><?= sanitize($pv['tagline'] ?? ucfirst($pv['experience_level'] ?? '').' Freelancer') ?></div>
            <?php if ($pv['location']): ?>
            <div class="prov-loc">
              <svg width="10" height="10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <?= sanitize($pv['location']) ?>
            </div>
            <?php endif; ?>

            <!-- Badge row -->
            <div class="badge-row">
              <?php if($bt==='premium'): ?><span class="badge-premium">⭐ Premium</span>
              <?php elseif($bt==='verified'): ?><span class="badge-verified">✓ Verified</span>
              <?php else: ?><span class="badge-free">🌱 Beginner</span><?php endif; ?>
              <?php if(($pv['experience_level']??'')==='expert'): ?><span class="badge-verified" style="background:var(--violet-dim);border-color:var(--violet-border);color:var(--violet);">🏆 Expert</span><?php endif; ?>
            </div>

            <div class="prov-stars">
              <?php for($s=1;$s<=5;$s++) echo $rv>=$s?'★':($rv>=$s-.5?'✦':'☆'); ?>
            </div>
            <div class="prov-rc"><?= number_format($rv,1) ?> (<?= (int)$pv['rating_count'] ?> reviews) · <?= $jobs ?> jobs done</div>

            <!-- Success rate bar -->
            <?php if($jobs > 0): ?>
            <div class="success-bar-wrap">
              <div class="success-bar-lbl"><span>Success Rate</span><span><?= $sr ?>%</span></div>
              <div class="success-bar-track"><div class="success-bar-fill" data-w="<?= $sr ?>"></div></div>
            </div>
            <?php endif; ?>

            <?php if(!empty($pv['response_time'])): ?>
            <div class="prov-resp">⏱ <?= sanitize($pv['response_time']) ?></div>
            <?php endif; ?>

            <div class="prov-pills">
              <?php foreach(array_slice($sk,0,2) as $skill): ?>
                <span class="skill-pill"><?= sanitize($skill) ?></span>
              <?php endforeach; ?>
              <span class="<?= $rk['c'] ?>"><?= $rk['i'].' '.$rk['l'] ?></span>
            </div>
            <div class="prov-foot">
              <div>
                <div class="prov-rate">
                  <?= $pv['hourly_rate']>0 ? formatCurrency($pv['hourly_rate']) : 'Negotiable' ?>
                  <?php if($pv['hourly_rate']>0): ?><small>/hr</small><?php endif; ?>
                </div>
              </div>
              <div class="prov-actions">
                <a href="<?= APP_URL ?>/profile.php?id=<?= $pv['user_id'] ?>" class="btn btn-ghost" style="padding:6px 13px;font-size:11.5px;">View</a>
                <?php if (isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'client'): ?>
                  <a href="<?= APP_URL ?>/client/messages.php?start=<?= $pv['user_id'] ?>" class="btn btn-indigo" style="padding:6px 13px;font-size:11.5px;">Hire</a>
                <?php else: ?>
                  <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-indigo" style="padding:6px 13px;font-size:11.5px;">Hire</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="pag-btn">← Prev</a>
        <?php else: ?><span class="pag-btn disabled">← Prev</span><?php endif; ?>
        <?php for ($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="pag-btn">Next →</a>
        <?php else: ?><span class="pag-btn disabled">Next →</span><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>

    </div><!-- /main column -->

    <!-- ══ SIDEBAR ══ -->
    <div class="sidebar">

      <!-- Trending searches -->
      <div class="sidebar-card">
        <div class="sc-head">🔥 Trending Searches</div>
        <div class="sc-body">
          <?php
          $trending = [
            ['💻','Web Developer'],['🎨','Graphic Designer'],['🔧','Plumber'],
            ['🏥','Home Nurse'],['🍽️','Private Chef'],['📷','Photographer'],
            ['🔌','Electrician'],['📱','App Developer'],['🎓','Math Tutor'],['🌿','Landscaper'],
          ];
          foreach ($trending as $i => [$icon, $label]):
          ?>
          <a href="<?= APP_URL ?>/search/providers.php?q=<?= urlencode($label) ?>" class="trending-item">
            <span class="tr-num">#<?= $i+1 ?></span>
            <span class="tr-icon"><?= $icon ?></span>
            <span style="font-size:12.5px;font-weight:500;"><?= $label ?></span>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Quick stats card -->
      <div class="sidebar-card">
        <div class="sc-head">📊 Platform Stats</div>
        <div class="sc-body">
          <?php
          $pStats = [
            ['👷','Freelancers', number_format($totalProvs)],
            ['⭐','Avg Rating', '4.8★'],
            ['✅','Jobs Done', '2.4K+'],
            ['🔒','Escrow Protected', '100%'],
            ['🇬🇭','Ghana Based', 'Yes'],
          ];
          foreach ($pStats as [$icon, $k, $v]):
          ?>
          <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:8px;font-size:12.5px;margin-bottom:5px;background:rgba(0,0,0,0.12);">
            <span style="color:var(--tx-3);"><?= $icon ?> <?= $k ?></span>
            <span style="font-weight:700;font-family:var(--fm);font-size:13px;"><?= $v ?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Post a Job CTA -->
      <div class="sidebar-card" style="background:linear-gradient(135deg,var(--cyan-dim),rgba(124,111,247,0.06));border-color:var(--cyan-border);">
        <div class="sc-body" style="text-align:center;padding:22px 18px;">
          <div style="font-size:28px;margin-bottom:10px;">📋</div>
          <div style="font-family:var(--fm);font-weight:800;font-size:14px;margin-bottom:6px;">Need a specific skill?</div>
          <div style="font-size:12px;color:var(--tx-2);margin-bottom:16px;line-height:1.6;">Post a job and let Ghana's best talent come to you.</div>
          <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-gold" style="width:100%;justify-content:center;font-size:13px;">Post a Job Free</a>
        </div>
      </div>

    </div><!-- /sidebar -->
  </div><!-- /page-layout -->

</div><!-- /main-wrap -->

<!-- ══════ FOOTER STRIP ══════ -->
<div class="footer-strip">
  <div class="footer-strip-inner">
    <a href="<?= APP_URL ?>/index.php" class="logo">
      <div class="logo-mark">G</div>
      <span class="logo-text">Gig<span>Ghana</span></span>
    </a>
    <div class="footer-nav">
      <a href="<?= APP_URL ?>/jobs.php">Browse Jobs</a>
      <a href="<?= APP_URL ?>/auth/register.php">Post a Job</a>
      <a href="#">Help Centre</a>
      <a href="#">Privacy</a>
    </div>
    <span class="footer-copy">© <?= date('Y') ?> GigGhana Ltd. 🇬🇭</span>
  </div>
</div>

<button class="back-top" id="backTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">↑</button>
<div id="toast-c"></div>

<script>
/* ══ THEME ══ */
function toggleTheme(){
  const l=document.body.classList.toggle('lm');
  localStorage.setItem('gg_theme',l?'light':'dark');
  document.getElementById('themeBtn').textContent=l?'☀️':'🌙';
}
(function(){
  if(localStorage.getItem('gg_theme')==='light'){
    document.body.classList.add('lm');
    const b=document.getElementById('themeBtn');if(b)b.textContent='☀️';
  }
})();

/* ══ NAVBAR SCROLL ══ */
window.addEventListener('scroll',()=>{
  document.getElementById('nav').classList.toggle('on',window.scrollY>40);
  const bt=document.getElementById('backTop');if(bt)bt.classList.toggle('show',window.scrollY>400);
});

/* ══ MOBILE MENU ══ */
function toggleMob(){
  const m=document.getElementById('mobNav'),h=document.getElementById('ham');
  m.classList.toggle('open');
  const sp=h.querySelectorAll('span');
  if(m.classList.contains('open')){sp[0].style.transform='rotate(45deg) translate(5px,5px)';sp[1].style.opacity='0';sp[2].style.transform='rotate(-45deg) translate(5px,-5px)';}
  else{sp.forEach(s=>{s.style.transform='';s.style.opacity='';});}
}

/* ══ FILTER PANEL ══ */
let filtersOpen = <?= ($activeCount > 0) ? 'true' : 'false' ?>;
function toggleFilters(){
  filtersOpen=!filtersOpen;
  document.getElementById('filterPanel').classList.toggle('open',filtersOpen);
  document.getElementById('filterToggleBtn').classList.toggle('active',filtersOpen);
}
if(filtersOpen) document.getElementById('filterPanel').classList.add('open');

/* ══ LIVE SEARCH (debounced) ══ */
let st;
document.getElementById('searchInput').addEventListener('input',function(){
  clearTimeout(st);
  st=setTimeout(()=>document.getElementById('filterForm').submit(),650);
});

/* ══ GRID / LIST VIEW ══ */
function setView(v){
  const grid=document.getElementById('provGrid');
  const gb=document.getElementById('gridBtn'),lb=document.getElementById('listBtn');
  if(v==='list'){grid.classList.add('list-view');lb.classList.add('active');gb.classList.remove('active');}
  else{grid.classList.remove('list-view');gb.classList.add('active');lb.classList.remove('active');}
  localStorage.setItem('gg_prov_view',v);
}
(function(){const s=localStorage.getItem('gg_prov_view');if(s==='list')setView('list');})();

/* ══ STAT COUNTER ══ */
const cObs=new IntersectionObserver(es=>{
  es.forEach(e=>{
    if(!e.isIntersecting||e.target.dataset.animated==='true')return;
    e.target.dataset.animated='true';
    const t=parseInt(e.target.dataset.count||'0');
    if(!t)return;
    let c=0;const s=t/(1200/16);
    const id=setInterval(()=>{c=Math.min(c+s,t);e.target.textContent=Math.floor(c).toLocaleString();if(c>=t)clearInterval(id);},16);
    cObs.unobserve(e.target);
  });
},{threshold:0.5});
document.querySelectorAll('[data-count]').forEach(c=>cObs.observe(c));

/* ══ SUCCESS BARS ══ */
let barsAnimated=false;
const bObs=new IntersectionObserver(es=>{
  es.forEach(e=>{if(e.isIntersecting&&!barsAnimated){barsAnimated=true;document.querySelectorAll('.success-bar-fill').forEach(el=>el.style.width=(el.dataset.w||0)+'%');}});
},{threshold:0.15});
const pg=document.getElementById('provGrid');if(pg)bObs.observe(pg);

/* ══ SCROLL REVEAL ══ */
const rvObs=new IntersectionObserver(es=>{
  es.forEach(e=>{if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='translateY(0)';}});
},{threshold:0.05,rootMargin:'0px 0px -28px 0px'});
document.querySelectorAll('.prov-card,.sidebar-card').forEach((el,i)=>{
  el.style.opacity='0';el.style.transform='translateY(18px)';
  el.style.transition=`opacity .4s ease ${(i%4)*55}ms,transform .4s ease ${(i%4)*55}ms`;
  rvObs.observe(el);
});

/* ══ TOAST ══ */
const _TI={success:'✅',error:'❌',info:'ℹ️'};
function showToast(title,msg,type='info',d=4200){
  const c=document.getElementById('toast-c'),t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${_TI[type]||'ℹ️'}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(48px)';setTimeout(()=>t.remove(),350);},d);
}

<?php if(isset($_GET['success'])): ?>showToast('Success','<?= sanitize($_GET['success']) ?>','success');<?php endif; ?>
<?php if(isset($_GET['error'])): ?>showToast('Error','<?= sanitize($_GET['error']) ?>','error');<?php endif; ?>
</script>
</body>
</html>