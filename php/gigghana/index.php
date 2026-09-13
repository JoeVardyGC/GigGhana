<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$iconMap = [
  'code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
  'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
  'briefcase'=>'⚖️','headphones'=>'🎧','camera'=>'📷','globe'=>'🌐',
  'tool'=>'🔧','bar-chart'=>'📊','music'=>'🎵',
];

try {
    $db = getDB();
    $stats = [
        'providers' => (int)$db->query("SELECT COUNT(*) FROM providers p JOIN users u ON u.id=p.user_id WHERE u.is_active=1 AND u.is_banned=0")->fetchColumn(),
        'jobs'      => (int)$db->query("SELECT COUNT(*) FROM jobs WHERE status='open'")->fetchColumn(),
        'completed' => (int)$db->query("SELECT COUNT(*) FROM jobs WHERE status='completed'")->fetchColumn(),
        'clients'   => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='client' AND is_active=1")->fetchColumn(),
        'earnings'  => (float)$db->query("SELECT COALESCE(SUM(net_amount),0) FROM transactions WHERE type='escrow_release' AND status='completed'")->fetchColumn(),
    ];
    $categories = $db->query("SELECT id, name, slug, icon, description FROM categories WHERE is_active=1 ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $skillSub = "(SELECT GROUP_CONCAT(s.name ORDER BY ps.proficiency DESC SEPARATOR '|') FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id WHERE ps.provider_id=p.id LIMIT 4)";
    $featured = $db->query("SELECT u.first_name,u.last_name,u.avatar,u.location,p.tagline,p.rating_avg,p.rating_count,p.hourly_rate,p.completed_jobs,p.is_verified,p.user_id,p.availability,p.experience_level,$skillSub AS skill_names FROM providers p JOIN users u ON u.id=p.user_id WHERE p.is_featured=1 AND u.is_active=1 AND u.is_banned=0 ORDER BY p.rating_avg DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($featured)) {
        $featured = $db->query("SELECT u.first_name,u.last_name,u.avatar,u.location,p.tagline,p.rating_avg,p.rating_count,p.hourly_rate,p.completed_jobs,p.is_verified,p.user_id,p.availability,p.experience_level,$skillSub AS skill_names FROM providers p JOIN users u ON u.id=p.user_id WHERE u.is_active=1 AND u.is_banned=0 ORDER BY p.rating_avg DESC,p.completed_jobs DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    }
    $matchedProviders = $db->query("SELECT u.first_name,u.last_name,u.avatar,p.tagline,p.rating_avg,p.rating_count,p.hourly_rate,p.completed_jobs,p.is_verified,p.user_id,p.experience_level,(SELECT GROUP_CONCAT(s.name ORDER BY ps.proficiency DESC SEPARATOR '|') FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id WHERE ps.provider_id=p.id LIMIT 3) AS skill_names FROM providers p JOIN users u ON u.id=p.user_id WHERE u.is_active=1 AND u.is_banned=0 ORDER BY p.rating_avg DESC,p.rating_count DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
    $recentJobs = $db->query("SELECT j.id,j.title,j.description,j.budget_min,j.budget_max,j.budget_type,j.is_urgent,j.is_featured,j.proposal_count,j.created_at,u.first_name,u.last_name,u.avatar AS client_avatar,c.name AS cat_name,c.icon AS cat_icon FROM jobs j JOIN users u ON u.id=j.client_id LEFT JOIN categories c ON c.id=j.category_id WHERE j.status='open' ORDER BY j.is_featured DESC,j.is_urgent DESC,j.created_at DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
    $liveJobs = $db->query("SELECT j.title,j.budget_min,j.budget_type,j.created_at,c.name AS cat_name FROM jobs j LEFT JOIN categories c ON c.id=j.category_id WHERE j.status='open' ORDER BY j.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $earningsRaw = $db->query("SELECT MONTH(created_at) AS m, SUM(net_amount) AS total FROM transactions WHERE type='escrow_release' AND status='completed' AND YEAR(created_at)=YEAR(CURDATE()) GROUP BY MONTH(created_at) ORDER BY m ASC")->fetchAll(PDO::FETCH_ASSOC);
    $earningsData = array_fill(1, 12, 0);
    foreach ($earningsRaw as $row) $earningsData[(int)$row['m']] = (float)$row['total'];
    $earningsJson = json_encode(array_values($earningsData));
    $earningsTotal = array_sum($earningsData);
    $reviews = $db->query("SELECT r.comment,r.rating_overall,u.first_name,u.last_name,u.avatar,u.location,u.role FROM reviews r JOIN users u ON u.id=r.reviewer_id WHERE r.is_public=1 AND r.comment IS NOT NULL AND r.comment!='' ORDER BY r.rating_overall DESC,r.created_at DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats=['providers'=>0,'jobs'=>0,'completed'=>0,'clients'=>0,'earnings'=>0];
    $categories=$featured=$matchedProviders=$recentJobs=$liveJobs=$reviews=[];
    $earningsJson='[0,0,0,0,0,0,0,0,0,0,0,0]';
    $earningsTotal=0;
}

$user = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
$csrf = generateCSRF();

$ghanaFallbackCats = [
  ['id'=>'tech',   'icon'=>'code',        'name'=>'IT & Tech',           'description'=>'Web Dev, App Dev, Digital Marketing', 'slug'=>'it-tech'],
  ['id'=>'design', 'icon'=>'pen-tool',    'name'=>'Creative Arts',        'description'=>'Graphics, Photography, Video',         'slug'=>'creative-arts'],
  ['id'=>'trades', 'icon'=>'tool',        'name'=>'Skilled Trades',       'description'=>'Carpenter, Plumber, Electrician',      'slug'=>'skilled-trades'],
  ['id'=>'health', 'icon'=>'headphones',  'name'=>'Health & Wellness',    'description'=>'Nurse, Physio, Fitness Coach',         'slug'=>'health-wellness'],
  ['id'=>'build',  'icon'=>'briefcase',   'name'=>'Construction',         'description'=>'Builder, Architect, Surveyor',         'slug'=>'construction'],
  ['id'=>'edu',    'icon'=>'file-text',   'name'=>'Education & Tutoring', 'description'=>'Teacher, Music, Art Instructor',       'slug'=>'education'],
  ['id'=>'hosp',   'icon'=>'bar-chart',   'name'=>'Hospitality',          'description'=>'Chef, Event Planner, Driver',          'slug'=>'hospitality'],
  ['id'=>'biz',    'icon'=>'dollar-sign', 'name'=>'Business Services',    'description'=>'Accountant, Consultant, Admin',        'slug'=>'business'],
  ['id'=>'farm',   'icon'=>'globe',       'name'=>'Agriculture',          'description'=>'Farmer, Agri-tech, Livestock',         'slug'=>'agriculture'],
  ['id'=>'other',  'icon'=>'trending-up', 'name'=>'Others',               'description'=>'Delivery, Security, Handyman',         'slug'=>'others'],
];
if (empty($categories)) $categories = $ghanaFallbackCats;
$hotSlugs = ['it-tech','skilled-trades','hospitality','tech','trades','hosp'];

$testimonialFallbacks = [
    ['first_name'=>'Kwame','last_name'=>'Asante','comment'=>"I'm a painter and GigGhana changed my life — I now get 5 jobs per week from clients I never could have reached before!",'rating_overall'=>5,'role'=>'provider','location'=>'Accra','avatar'=>''],
    ['first_name'=>'Abena','last_name'=>'Mensah','comment'=>"As a nurse, I now offer home care through GigGhana. The platform is safe and payments always come on time via MoMo.",'rating_overall'=>5,'role'=>'provider','location'=>'Kumasi','avatar'=>''],
    ['first_name'=>'Kofi','last_name'=>'Boateng','comment'=>"Carpenters like me now get long-term contracts. GigGhana verified my skills and clients trust me immediately.",'rating_overall'=>5,'role'=>'provider','location'=>'Takoradi','avatar'=>''],
    ['first_name'=>'Ama','last_name'=>'Owusu','comment'=>"Finding a reliable electrician used to be a nightmare. Now I hire through GigGhana in minutes and the escrow keeps me safe.",'rating_overall'=>5,'role'=>'client','location'=>'Accra','avatar'=>''],
];
if (empty($reviews)) $reviews = $testimonialFallbacks;

function rankLabel(int $j):array{
    if($j>=50) return['i'=>'🏆','l'=>'Elite Expert','c'=>'rk-gold'];
    if($j>=20) return['i'=>'⭐','l'=>'Top Rated','c'=>'rk-blue'];
    if($j>=5)  return['i'=>'📈','l'=>'Rising Talent','c'=>'rk-teal'];
    return['i'=>'🌱','l'=>'New Freelancer','c'=>'rk-dim'];
}
function initials(string $f,string $l):string{ return strtoupper(substr($f,0,1).substr($l,0,1)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="description" content="GigGhana — Africa's premier freelance marketplace. Your Skill. Your Success. Your Ghana.">
<title>GigGhana — Africa's Freelance Marketplace</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ══════════════════════════════════════
   DESIGN TOKENS — Volcanic Charcoal × Electric Cyan × Coral
══════════════════════════════════════ */
:root{
  --bg:#0C0E14;
  --s1:#13161E;
  --s2:#191D27;
  --s3:#1F2433;
  --glass:rgba(19,22,30,0.82);

  --cyan:#00D4C8;
  --cyan-d:#00A89F;
  --cyan-l:#4DFFE8;
  --cyan-dim:rgba(0,212,200,0.10);
  --cyan-border:rgba(0,212,200,0.22);

  --coral:#FF6B4A;
  --coral-d:#E04D2E;
  --coral-l:#FF8F70;
  --coral-dim:rgba(255,107,74,0.10);
  --coral-border:rgba(255,107,74,0.25);

  --violet:#7C6FF7;
  --violet-d:#5D52E0;
  --violet-dim:rgba(124,111,247,0.10);
  --violet-border:rgba(124,111,247,0.22);

  --green:#1FD9A0;
  --green-d:#13B882;
  --green-dim:rgba(31,217,160,0.10);
  --red:#FF4D6A;
  --amber:#F7B731;

  --tx:#F2F4F8;
  --tx-2:#9BA8BF;
  --tx-3:#4E5A6E;

  --bd:rgba(255,255,255,0.065);
  --bd2:rgba(255,255,255,0.12);

  --gC:rgba(0,212,200,0.16);
  --gO:rgba(255,107,74,0.14);
  --gV:rgba(124,111,247,0.14);

  --fm:'Plus Jakarta Sans',sans-serif;
  --fb:'DM Sans',sans-serif;
  --r:16px; --rs:10px; --e:all 0.26s ease;
}

/* ── LIGHT MODE ── */
.lm{
  --bg:#F3F5FA; --s1:#EAEEF7; --s2:#E0E6F2; --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95; --cyan-d:#007870; --cyan-l:#00CFC3;
  --cyan-dim:rgba(0,158,149,0.08); --cyan-border:rgba(0,158,149,0.2);
  --coral:#E8512B; --coral-d:#C43C1C;
  --coral-dim:rgba(232,81,43,0.08); --coral-border:rgba(232,81,43,0.2);
  --violet:#5B4FD9; --violet-d:#4540C0;
  --violet-dim:rgba(91,79,217,0.08); --violet-border:rgba(91,79,217,0.18);
  --green:#0DAF80; --green-d:#088C65;
  --amber:#D4980A;
  --tx:#0D1220; --tx-2:#344060; --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.09); --bd2:rgba(30,40,80,0.16);
  --gC:rgba(0,158,149,0.09); --gO:rgba(232,81,43,0.09);
}
.lm .navbar{background:rgba(243,245,250,0.96)!important;border-bottom-color:var(--bd);}
.lm .navbar.on{background:rgba(243,245,250,0.99)!important;box-shadow:0 4px 28px rgba(13,18,32,0.07);}
.lm .mobile-nav{background:rgba(243,245,250,0.99);}
.lm .stat-number{background:linear-gradient(135deg,var(--cyan),var(--violet));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.lm .s-badge{background:var(--violet-dim);border-color:var(--violet-border);color:var(--violet);}
.lm .cta-wrap{background:linear-gradient(135deg,var(--s2),var(--s1));border-color:var(--cyan-border);}
.lm .footer-wrap{background:var(--s1);}
.lm .skill-pill{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);}
.lm .btn-ghost{border-color:var(--bd2);color:var(--tx-2);}
.lm .search-wrap{background:rgba(255,255,255,0.92);border-color:var(--bd2);}
.lm .search-wrap input{color:var(--tx);}
.lm .search-wrap input::placeholder{color:var(--tx-3);}
.lm .autocomplete-drop{background:rgba(234,238,247,0.99);border-color:var(--bd2);}
.lm .auto-item:hover{background:var(--cyan-dim);}
.lm .sub-banner{background:linear-gradient(135deg,var(--s1),var(--s2));border-top-color:var(--coral-border);}
.lm .pay-logo{border-color:var(--bd2);}
.lm .pay-logo img{filter:none;opacity:0.85;}
.lm .cat-card{background:rgba(255,255,255,0.85);}
.lm .prov-card{background:rgba(255,255,255,0.9);}
.lm .job-card{background:rgba(255,255,255,0.85);}
.lm .feed-item{background:rgba(255,255,255,0.8);}
.lm .prov-rate{color:var(--cyan);}
.lm .job-budget{color:var(--cyan);}
.lm .soc-btn:hover{background:var(--coral-dim);color:var(--coral);border-color:var(--coral-border);}
.lm .footer-links a:hover{color:var(--cyan);}
.lm .back-top:hover{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}
.lm .trend-pill:hover{border-color:var(--cyan-border);color:var(--cyan);}
.lm .trend-num{background:var(--cyan);color:#fff;}
.lm .rv-nav-btn:hover{background:var(--coral-dim);border-color:var(--coral-border);color:var(--coral);}

/* ══ RESET ══ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);font-size:15px;line-height:1.65;overflow-x:hidden;transition:background .3s,color .3s;font-weight:400;-webkit-font-smoothing:antialiased;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
h1,h2,h3,h4,.logo-text,.stat-number,.btn{font-family:var(--fm);-webkit-font-smoothing:antialiased;}

/* ── TYPOGRAPHY ── */
.hero-title{font-family:var(--fm);font-size:clamp(28px,4vw,52px);font-weight:800;line-height:1.1;letter-spacing:-0.4px;}
.hero-title .gold{color:var(--cyan);}
.hero-title .green{color:var(--green);}
.s-title{font-family:var(--fm);font-size:clamp(20px,2.6vw,34px);font-weight:700;line-height:1.2;letter-spacing:-0.3px;margin-bottom:10px;}
.stat-number{font-family:var(--fm);font-size:clamp(24px,3vw,40px);font-weight:800;line-height:1.1;background:linear-gradient(135deg,var(--cyan-l),var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.cta-title{font-family:var(--fm);font-size:clamp(22px,3vw,40px);font-weight:800;line-height:1.15;margin-bottom:14px;}
.s-sub{color:var(--tx-2);font-size:15px;max-width:480px;margin:0 auto;line-height:1.75;font-weight:400;}

/* ══ NAVBAR ══ */
.navbar{position:fixed;top:0;left:0;right:0;z-index:1000;display:flex;align-items:center;justify-content:space-between;padding:0 5%;height:64px;background:rgba(12,14,20,0.84);backdrop-filter:blur(24px);border-bottom:1px solid var(--bd);transition:var(--e);}
.navbar.on{background:rgba(12,14,20,0.97);box-shadow:0 2px 30px rgba(0,0,0,0.5);}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none;flex-shrink:0;}
.logo-mark{width:36px;height:36px;flex-shrink:0;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px;color:#0C0E14;font-family:var(--fm);}
.logo-text{font-size:20px;font-weight:800;color:var(--tx);letter-spacing:-0.3px;}.logo-text span{color:var(--cyan);}
.nav-links{display:flex;align-items:center;gap:2px;}
.nav-links a{color:var(--tx-2);text-decoration:none;font-size:13.5px;font-weight:500;padding:6px 13px;border-radius:var(--rs);transition:var(--e);white-space:nowrap;}
.nav-links a:hover{color:var(--tx);background:rgba(255,255,255,0.05);}
.nav-acts{display:flex;align-items:center;gap:8px;}
.lang-pill{display:flex;align-items:center;gap:5px;background:rgba(255,255,255,0.04);border:1px solid var(--bd);border-radius:50px;padding:4px 4px 4px 9px;cursor:pointer;font-size:12px;font-weight:600;color:var(--tx-3);transition:var(--e);font-family:var(--fm);}
.lang-pill:hover{border-color:var(--cyan-border);color:var(--cyan);}
.lang-inner{background:var(--s2);border-radius:50px;padding:2px 7px;font-size:11px;font-weight:700;color:var(--tx-3);}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--rs);font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:var(--e);white-space:nowrap;line-height:1.3;letter-spacing:0.01em;}
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

/* ══ SUBSCRIPTION BANNER ══ */
.sub-banner{position:fixed;bottom:0;left:0;right:0;z-index:998;background:linear-gradient(135deg,rgba(19,22,30,0.98),rgba(25,29,39,0.98));backdrop-filter:blur(20px);border-top:1px solid var(--coral-border);padding:13px 5%;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;transform:translateY(100%);transition:transform .5s ease;}
.sub-banner.show{transform:translateY(0);}
.sub-banner-text{font-size:13.5px;color:var(--tx-2);}
.sub-banner-text strong{color:var(--coral);font-family:var(--fm);}
.sub-close{cursor:pointer;color:var(--tx-3);font-size:20px;padding:4px;transition:var(--e);line-height:1;}
.sub-close:hover{color:var(--tx);}

/* ══ HERO ══ */
.hero{position:relative;height:100vh;min-height:600px;overflow:hidden;}
.hero-slides{position:absolute;inset:0;}
.hero-slide{position:absolute;inset:0;background-size:cover;background-position:center;opacity:0;transition:opacity 1s ease;}
.hero-slide.active{opacity:1;}
.hero-slide::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(12,14,20,0.85) 0%,rgba(12,14,20,0.55) 60%,rgba(12,14,20,0.32) 100%);}
.hs1{background-image:url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1600&q=80&auto=format');}
.hs2{background-image:url('https://images.unsplash.com/photo-1573164713988-8665fc963095?w=1600&q=80&auto=format');}
.hs3{background-image:url('https://images.unsplash.com/photo-1551434678-e076c223a692?w=1600&q=80&auto=format');}
.hs4{background-image:url('https://images.unsplash.com/photo-1582515073490-39981397c445?w=1600&q=80&auto=format');}
.hs5{background-image:url('https://images.unsplash.com/photo-1542744173-8e7e53415bb0?w=1600&q=80&auto=format');}
.hs6{background-image:url('https://images.unsplash.com/photo-1565514020179-026b92b84bb6?w=1600&q=80&auto=format');}
.hero-content{position:relative;z-index:2;height:100%;display:flex;align-items:center;padding:0 5%;}
.hero-inner{max-width:680px;}

.hero-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(0,212,200,0.1);border:1px solid var(--cyan-border);color:var(--cyan);padding:6px 15px;border-radius:50px;font-size:12px;font-weight:600;font-family:var(--fm);margin-bottom:22px;animation:fadU .7s ease both;overflow:hidden;}
.hero-badge::before{content:'🇬🇭';}
.ticker-wrap{display:inline-block;vertical-align:middle;}
.ticker-text{display:inline-block;transition:opacity .3s ease,transform .3s ease;}
.hero-title{margin-bottom:18px;animation:fadU .7s .1s ease both;text-shadow:0 2px 20px rgba(0,0,0,0.5);}
.hero-sub{font-size:clamp(14px,1.6vw,16.5px);color:rgba(242,244,248,0.8);max-width:520px;margin-bottom:32px;animation:fadU .7s .2s ease both;font-weight:400;line-height:1.75;}

/* SEARCH BAR */
.search-outer{position:relative;max-width:580px;margin-bottom:24px;animation:fadU .7s .25s ease both;}
.search-wrap{display:flex;align-items:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,0.15);border-radius:14px;padding:6px 6px 6px 16px;gap:9px;box-shadow:0 8px 32px rgba(0,0,0,0.35);transition:border-color .3s,box-shadow .3s;}
.search-wrap:focus-within{border-color:var(--cyan-border);box-shadow:0 8px 32px rgba(0,0,0,0.35),0 0 0 3px var(--cyan-dim);}
.search-wrap input{flex:1;background:transparent;border:none;outline:none;color:#fff;font-size:13.5px;font-family:var(--fb);min-width:0;}
.search-wrap input::placeholder{color:rgba(255,255,255,0.45);}
.search-div{width:1px;height:22px;background:rgba(255,255,255,0.15);}
.search-wrap select{background:transparent;border:none;outline:none;color:rgba(255,255,255,0.65);font-size:12.5px;font-family:var(--fb);cursor:pointer;padding:3px;}
.search-wrap select option{background:#191D27;color:var(--tx);}
.autocomplete-drop{position:absolute;top:calc(100% + 7px);left:0;right:0;background:rgba(19,22,30,0.98);backdrop-filter:blur(20px);border:1px solid var(--bd);border-radius:14px;box-shadow:0 18px 50px rgba(0,0,0,0.5);z-index:900;overflow:hidden;display:none;}
.autocomplete-drop.open{display:block;}
.auto-item{display:flex;align-items:center;gap:10px;padding:11px 15px;cursor:pointer;transition:var(--e);}
.auto-item:hover{background:var(--cyan-dim);}
.auto-icon{width:30px;height:30px;border-radius:8px;background:var(--cyan-dim);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.auto-text{font-size:13px;font-weight:500;}
.auto-cat{font-size:11px;color:var(--tx-3);margin-top:1px;}

.hero-acts{display:flex;align-items:center;gap:11px;flex-wrap:wrap;margin-bottom:36px;animation:fadU .7s .3s ease both;}
.hero-trust{display:flex;align-items:center;gap:18px;flex-wrap:wrap;animation:fadU .7s .4s ease both;}
.trust-i{display:flex;align-items:center;gap:7px;font-size:12px;color:rgba(242,244,248,0.65);}
.dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}
.dot-g{background:var(--cyan);}
.dot-b{background:var(--violet);}
.dot-gr{background:var(--green);}
.dot-i{background:var(--coral);}

.hero-dots{position:absolute;bottom:28px;left:5%;z-index:3;display:flex;gap:8px;align-items:center;}
.hero-dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,0.3);cursor:pointer;transition:all .3s;}
.hero-dot.active{width:24px;border-radius:4px;background:var(--cyan);}

.hero-panel{position:absolute;right:5%;top:50%;transform:translateY(-50%);width:340px;z-index:3;background:rgba(12,14,20,0.72);backdrop-filter:blur(24px);border:1px solid var(--bd2);border-radius:var(--r);box-shadow:0 20px 60px rgba(0,0,0,0.45);overflow:hidden;animation:fadU .8s .3s ease both;}
.panel-slide{padding:28px 26px 16px;opacity:0;transform:translateX(24px);transition:opacity .5s,transform .5s;position:absolute;inset:0;pointer-events:none;}
.panel-slide.active{opacity:1;transform:translateX(0);position:relative;pointer-events:auto;}
.p-icon{width:42px;height:42px;border-radius:12px;margin-bottom:14px;display:flex;align-items:center;justify-content:center;font-size:19px;background:var(--cyan-dim);border:1px solid var(--cyan-border);}
.panel-slide h3{font-size:16px;font-weight:700;margin-bottom:9px;color:var(--tx);line-height:1.3;}
.panel-slide p{font-size:12.5px;color:var(--tx-2);line-height:1.65;margin-bottom:14px;}
.panel-dots{display:flex;gap:5px;padding:0 26px 20px;}
.p-dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,0.18);cursor:pointer;transition:all .3s;}
.p-dot.active{width:20px;border-radius:3px;background:var(--cyan);}
.panel-sub-badge{background:var(--cyan-dim);border:1px solid var(--cyan-border);border-radius:10px;padding:10px 13px;margin-top:6px;}
.panel-sub-title{font-family:var(--fm);font-size:11.5px;font-weight:700;color:var(--cyan);margin-bottom:3px;}
.panel-sub-text{font-size:11px;color:var(--tx-3);}

/* ══ STATS ══ */
.stats-bar{padding:52px 5%;background:linear-gradient(180deg,transparent,rgba(19,22,30,0.4),transparent);}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;max-width:1160px;margin:0 auto;}
.stat-card{background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);border-radius:var(--r);padding:28px 22px;text-align:center;transition:var(--e);position:relative;overflow:hidden;}
.stat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--cyan),var(--coral));transform:scaleX(0);transition:var(--e);transform-origin:left;}
.stat-card:hover{transform:translateY(-4px);border-color:var(--cyan-border);}
.stat-card:hover::after{transform:scaleX(1);}
.stat-icon{font-size:22px;margin-bottom:8px;}
.stat-label{color:var(--tx-2);font-size:12.5px;margin-top:5px;font-weight:500;}

/* ══ SECTIONS ══ */
.section{padding:70px 5%;}
.s-head{text-align:center;margin-bottom:48px;}
.s-badge{display:inline-block;background:var(--violet-dim);border:1px solid var(--violet-border);color:var(--violet);padding:4px 13px;border-radius:50px;font-size:10.5px;font-weight:700;font-family:var(--fm);letter-spacing:1.2px;text-transform:uppercase;margin-bottom:12px;}
.empty{text-align:center;padding:44px 20px;color:var(--tx-3);background:var(--glass);border:1px solid var(--bd);border-radius:var(--r);max-width:420px;margin:0 auto;font-size:14px;}
.empty span{display:block;font-size:32px;margin-bottom:10px;}

/* ══ LIVE FEED ══ */
.feed-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 16px;background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:12px;margin-bottom:7px;transition:var(--e);}
.feed-item:hover{border-color:var(--cyan-border);transform:translateX(4px);}
.feed-ttl{font-weight:600;font-size:12.5px;font-family:var(--fm);}
.feed-meta{font-size:11px;color:var(--tx-3);margin-top:2px;}
.live-dot{width:7px;height:7px;border-radius:50%;background:var(--green);flex-shrink:0;animation:pls 2s ease-in-out infinite;}
@keyframes pls{0%,100%{box-shadow:0 0 0 0 rgba(31,217,160,0.5);}50%{box-shadow:0 0 0 7px rgba(31,217,160,0);}}

/* ══ CATEGORIES ══ */
.cat-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;max-width:1160px;margin:0 auto;}
.cat-card{background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);padding:24px 14px;text-align:center;cursor:pointer;transition:var(--e);text-decoration:none;color:var(--tx);position:relative;overflow:hidden;}
.cat-card::before{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,var(--cyan),var(--coral));transform:scaleX(0);transition:var(--e);transform-origin:left;}
.cat-card:hover{transform:translateY(-6px);border-color:var(--cyan-border);box-shadow:0 12px 36px rgba(0,0,0,0.3);}
.cat-card:hover::before{transform:scaleX(1);}
.cat-hot{position:absolute;top:8px;right:8px;background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-size:9px;font-weight:800;font-family:var(--fm);padding:2px 7px;border-radius:50px;letter-spacing:0.4px;}
.cat-icon{width:48px;height:48px;border-radius:14px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:22px;background:linear-gradient(135deg,var(--cyan-dim),var(--violet-dim));border:1px solid var(--cyan-border);transition:transform .3s;}
.cat-card:hover .cat-icon{transform:scale(1.1) rotate(-4deg);}
.cat-name{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:3px;}
.cat-desc{font-size:10.5px;color:var(--tx-3);line-height:1.4;margin-bottom:4px;}
.cat-arrow{font-size:11px;color:var(--tx-3);opacity:0;transition:var(--e);}
.cat-card:hover .cat-arrow{opacity:1;color:var(--cyan);}

/* ══ TRENDING ══ */
.trending-wrap{display:flex;gap:9px;flex-wrap:wrap;justify-content:center;max-width:840px;margin:0 auto;}
.trend-pill{display:flex;align-items:center;gap:7px;background:var(--glass);border:1px solid var(--bd);border-radius:50px;padding:7px 15px;cursor:pointer;transition:var(--e);font-size:13px;font-weight:500;color:var(--tx-2);text-decoration:none;}
.trend-pill:hover{border-color:var(--cyan-border);color:var(--cyan);transform:translateY(-2px);}
.trend-num{font-family:var(--fm);font-size:9.5px;font-weight:800;color:#0C0E14;background:var(--cyan);padding:2px 6px;border-radius:50px;}

/* ══ PROVIDER CARDS ══ */
.prov-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;max-width:1160px;margin:0 auto;}
.prov-card{background:var(--s2);border:1px solid var(--bd);border-radius:var(--r);transition:var(--e);position:relative;overflow:hidden;display:flex;flex-direction:column;}
.prov-card:hover{transform:translateY(-6px);border-color:var(--cyan-border);box-shadow:0 20px 52px rgba(0,0,0,0.4);}
.prov-img-wrap{width:100%;height:160px;position:relative;overflow:hidden;background:linear-gradient(135deg,#1A1630,#0F2030);}
.prov-img-wrap img{width:100%;height:100%;object-fit:cover;transition:transform .5s;}
.prov-card:hover .prov-img-wrap img{transform:scale(1.06);}
.prov-initials{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:52px;font-weight:800;color:rgba(255,255,255,0.12);}
.prov-verified-badge{position:absolute;top:10px;right:10px;background:rgba(31,217,160,0.88);color:#0C0E14;padding:3px 8px;border-radius:50px;font-size:10px;font-weight:700;backdrop-filter:blur(8px);font-family:var(--fm);}
.prov-body{padding:18px;flex:1;display:flex;flex-direction:column;}
.prov-name{font-size:15px;font-weight:700;margin-bottom:3px;font-family:var(--fm);letter-spacing:-0.1px;}
.prov-loc{font-size:11.5px;color:var(--tx-3);margin-bottom:4px;display:flex;align-items:center;gap:3px;font-weight:400;}
.prov-tag{color:var(--tx-3);font-size:12px;margin-bottom:10px;font-weight:400;}
.badge-row{display:flex;gap:5px;margin-bottom:9px;flex-wrap:wrap;}
.badge-free{background:rgba(78,90,110,0.12);border:1px solid rgba(78,90,110,0.2);color:var(--tx-3);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:600;font-family:var(--fm);}
.badge-verified{background:var(--green-dim);border:1px solid rgba(31,217,160,0.22);color:var(--green);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:600;font-family:var(--fm);}
.badge-premium{background:var(--coral-dim);border:1px solid var(--coral-border);color:var(--coral);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);}
.prov-stars{color:var(--amber);font-size:12px;letter-spacing:1px;margin-bottom:4px;}
.prov-rc{font-size:11px;color:var(--tx-3);margin-bottom:12px;}
.prov-pills{display:flex;flex-wrap:wrap;gap:5px;margin-bottom:14px;flex:1;}
.skill-pill{background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:500;}
.rk-gold{background:rgba(247,183,49,0.12);border:1px solid rgba(247,183,49,0.28);color:var(--amber);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.rk-blue{background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.rk-teal{background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.rk-dim{background:rgba(78,90,110,0.1);border:1px solid rgba(78,90,110,0.16);color:var(--tx-3);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:600;}
.prov-foot{display:flex;align-items:center;justify-content:space-between;padding-top:13px;border-top:1px solid var(--bd);}
.prov-rate{font-family:var(--fm);font-weight:700;font-size:17px;color:var(--cyan);}
.prov-rate small{font-size:11px;color:var(--tx-3);font-weight:400;}

/* ══ JOB CARDS ══ */
.jobs-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;max-width:1160px;margin:0 auto;}
.job-card{background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);padding:22px;transition:var(--e);text-decoration:none;color:var(--tx);display:block;}
.job-card:hover{border-color:var(--cyan-border);transform:translateY(-3px);box-shadow:0 14px 36px rgba(0,0,0,0.3);}
.job-top{display:flex;align-items:flex-start;justify-content:space-between;gap:9px;margin-bottom:9px;}
.job-ttl{font-family:var(--fm);font-size:14.5px;font-weight:700;line-height:1.3;}
.jb{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;white-space:nowrap;flex-shrink:0;font-family:var(--fm);}
.jb-open{background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.2);}
.jb-urgent{background:var(--coral-dim);color:var(--coral);border:1px solid var(--coral-border);}
.jb-feat{background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.job-desc{color:var(--tx-2);font-size:12.5px;margin-bottom:13px;line-height:1.6;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.job-meta{display:flex;align-items:center;gap:12px;flex-wrap:wrap;font-size:11.5px;color:var(--tx-3);}
.job-budget{color:var(--cyan);font-weight:700;font-size:14px;font-family:var(--fm);}
.job-poster{display:flex;align-items:center;gap:10px;margin-top:14px;padding-top:14px;border-top:1px solid var(--bd);}
.client-av{width:34px;height:34px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--cyan),var(--violet-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:13px;font-weight:700;color:#0C0E14;border:2px solid var(--cyan-border);}
.client-av img{width:100%;height:100%;object-fit:cover;}
.client-info{flex:1;}
.client-name{font-weight:600;font-size:12.5px;}
.client-lbl{font-size:10.5px;color:var(--tx-3);}

/* ══ HOW IT WORKS ══ */
.how-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;max-width:1160px;margin:0 auto;}
.how-card{text-align:center;padding:36px 22px;background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);transition:var(--e);}
.how-card:hover{transform:translateY(-4px);border-color:var(--cyan-border);}
.how-step{width:52px;height:52px;border-radius:15px;margin:0 auto 18px;display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:22px;font-weight:800;}
.hs1c{background:var(--coral-dim);color:var(--coral);border:1px solid var(--coral-border);}
.hs2c{background:var(--cyan-dim);color:var(--cyan);border:1px solid var(--cyan-border);}
.hs3c{background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.22);}
.how-title{font-size:17px;margin-bottom:9px;font-family:var(--fm);font-weight:700;}
.how-desc{color:var(--tx-2);font-size:13.5px;line-height:1.7;font-weight:400;}

.badge-tiers{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;max-width:1160px;margin:32px auto 0;}
.badge-tier-card{background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);padding:22px;text-align:center;transition:var(--e);}
.badge-tier-card:hover{transform:translateY(-3px);}
.badge-tier-card.featured{border-color:var(--cyan-border);background:var(--cyan-dim);}
.bt-icon{font-size:26px;margin-bottom:10px;}
.bt-name{font-family:var(--fm);font-weight:700;font-size:14px;margin-bottom:5px;}
.bt-price{font-size:12px;color:var(--tx-3);margin-bottom:10px;}
.bt-perks{font-size:11.5px;color:var(--tx-2);line-height:1.6;font-weight:400;}

/* ══ PROPOSAL TEMPLATES ══ */
.prop-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;max-width:1160px;margin:0 auto;}
.prop-card{background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);padding:28px 20px;text-align:center;transition:var(--e);}
.prop-card:hover{transform:translateY(-4px);border-color:var(--cyan-border);}
.prop-ico{width:46px;height:46px;border-radius:13px;margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:20px;}
.pi-a{background:var(--coral-dim);border:1px solid var(--coral-border);}
.pi-b{background:var(--cyan-dim);border:1px solid var(--cyan-border);}
.pi-c{background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);}
.prop-title{font-size:16px;margin-bottom:9px;font-family:var(--fm);font-weight:700;}
.prop-text{font-size:13px;color:var(--tx-2);line-height:1.7;font-weight:400;}

/* ══ REVIEWS CAROUSEL ══ */
.rv-carousel-outer{max-width:1160px;margin:0 auto;overflow:hidden;}
.rv-track{display:flex;gap:18px;transition:transform .55s ease;}
.rv-card{background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);padding:22px;position:relative;overflow:hidden;transition:var(--e);min-width:calc(50% - 9px);flex-shrink:0;}
.rv-card:hover{border-color:var(--cyan-border);}
.rv-card::before{content:'"';position:absolute;top:8px;right:18px;font-size:72px;font-family:Georgia,serif;color:var(--cyan);opacity:0.07;line-height:1;}
.rv-stars{color:var(--amber);font-size:12px;margin-bottom:11px;}
.rv-text{font-size:13.5px;line-height:1.75;color:var(--tx);margin-bottom:15px;font-style:italic;font-weight:400;}
.rv-author{display:flex;align-items:center;gap:9px;}
.rv-av{width:36px;height:36px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:12px;font-weight:700;color:#fff;overflow:hidden;}
.rv-av img{width:100%;height:100%;object-fit:cover;}
.rv-name{font-family:var(--fm);font-weight:700;font-size:13px;}
.rv-role{font-size:11px;color:var(--tx-3);}
.rv-nav{display:flex;gap:8px;justify-content:center;margin-top:20px;}
.rv-nav-btn{width:36px;height:36px;border-radius:50%;background:var(--glass);border:1px solid var(--bd);color:var(--tx);font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--e);}
.rv-nav-btn:hover{background:var(--coral-dim);border-color:var(--coral-border);color:var(--coral);}

/* ══ CTA ══ */
.cta-wrap{margin:0 5% 72px;border-radius:24px;overflow:hidden;position:relative;background:linear-gradient(135deg,var(--s2) 0%,var(--bg) 100%);border:1px solid var(--cyan-border);padding:72px 52px;}
.cta-glo{position:absolute;width:380px;height:380px;border-radius:50%;background:radial-gradient(circle,var(--gC),transparent 70%);top:-100px;right:-80px;pointer-events:none;}
.cta-glo2{position:absolute;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,var(--gO),transparent 70%);bottom:-70px;left:0;pointer-events:none;}
.cta-inner{position:relative;z-index:1;max-width:560px;}
.cta-sub{color:var(--tx-2);font-size:15px;margin-bottom:28px;line-height:1.7;font-weight:400;}
.cta-btns{display:flex;gap:12px;flex-wrap:wrap;}

/* ══ EARNINGS CHART ══ */
.chart-box{background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:var(--r);padding:28px;max-width:860px;margin:0 auto;}
.chart-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;}
.chart-ttl{font-family:var(--fm);font-size:16px;font-weight:700;}
.chart-kpi-val{font-family:var(--fm);font-size:26px;font-weight:800;color:var(--cyan);}
.chart-kpi-lbl{font-size:11px;color:var(--tx-3);}

/* ══ PAYMENT LOGOS ══ */
.pay-section{padding:36px 5%;border-top:1px solid var(--bd);}
.pay-inner{max-width:1160px;margin:0 auto;text-align:center;}
.pay-ttl{color:var(--tx-3);font-size:11px;font-weight:600;font-family:var(--fm);letter-spacing:1.4px;text-transform:uppercase;margin-bottom:22px;}
.pay-logos{display:flex;align-items:center;justify-content:center;gap:16px;flex-wrap:wrap;}
.pay-logo{display:flex;align-items:center;justify-content:center;background:var(--glass);border:1px solid var(--bd);border-radius:12px;padding:10px 18px;height:52px;min-width:90px;transition:var(--e);position:relative;overflow:hidden;}
.pay-logo:hover{border-color:var(--cyan-border);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,0.25);}
.pay-logo img{max-height:28px;max-width:90px;object-fit:contain;filter:brightness(0) invert(1);opacity:0.65;transition:var(--e);}
.pay-logo:hover img{opacity:1;}
.pay-logo .pay-txt{font-family:var(--fm);font-weight:700;font-size:12px;color:var(--tx-2);display:none;}
.pay-logo img.err + .pay-txt{display:block;}

/* ══ FOOTER ══ */
.footer-wrap{background:var(--s1);border-top:1px solid var(--bd);padding:52px 5% 0;}
.footer-top{display:grid;grid-template-columns:1.8fr 1fr 1fr 1fr;gap:36px;max-width:1160px;margin:0 auto;padding-bottom:44px;}
.footer-brand p{color:var(--tx-3);font-size:13px;line-height:1.7;margin-top:13px;max-width:230px;font-weight:400;}
.footer-ttl{font-family:var(--fm);font-weight:700;font-size:13px;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:14px;color:var(--tx-2);}
.footer-links{list-style:none;display:flex;flex-direction:column;gap:9px;}
.footer-links a{color:var(--tx-3);text-decoration:none;font-size:13px;transition:var(--e);font-weight:400;}
.footer-links a:hover{color:var(--cyan);}
.footer-nl{margin-top:18px;}
.nl-form{display:flex;gap:6px;margin-top:9px;}
.nl-input{flex:1;background:var(--s2);border:1px solid var(--bd);border-radius:var(--rs);padding:8px 12px;font-size:13px;font-family:var(--fb);color:var(--tx);outline:none;min-width:0;}
.nl-input:focus{border-color:var(--cyan-border);}
.nl-input::placeholder{color:var(--tx-3);}
.footer-bar{max-width:1160px;margin:0 auto;padding:20px 0;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;border-top:1px solid var(--bd);}
.footer-copy{color:var(--tx-3);font-size:12px;}
.footer-socials{display:flex;gap:8px;}
.soc-btn{width:34px;height:34px;border-radius:9px;background:rgba(255,255,255,0.03);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;color:var(--tx-3);font-size:13.5px;cursor:pointer;transition:var(--e);text-decoration:none;font-weight:700;}
.soc-btn:hover{background:var(--coral-dim);color:var(--coral);border-color:var(--coral-border);}
.footer-badges{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;}
.f-badge{display:flex;align-items:center;gap:5px;background:rgba(255,255,255,0.02);border:1px solid var(--bd);border-radius:8px;padding:5px 10px;font-size:11px;color:var(--tx-3);}

/* ══ BACK TO TOP ══ */
.back-top{position:fixed;bottom:80px;right:20px;z-index:990;width:40px;height:40px;border-radius:11px;background:var(--s2);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;color:var(--tx-2);font-size:16px;cursor:pointer;transition:var(--e);opacity:0;pointer-events:none;box-shadow:0 4px 18px rgba(0,0,0,0.3);}
.back-top.show{opacity:1;pointer-events:auto;}
.back-top:hover{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}

/* ══ TOAST ══ */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);max-width:340px;min-width:250px;box-shadow:0 12px 32px rgba(0,0,0,0.5);animation:sR .4s ease;backdrop-filter:blur(14px);}
.toast.success{border-left:3px solid var(--green);}
.toast.error{border-left:3px solid var(--red);}
.toast.warning{border-left:3px solid var(--coral);}
.toast.info{border-left:3px solid var(--cyan);}
.t-ico{font-size:17px;flex-shrink:0;}.t-bod{flex:1;}
.t-ttl{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}
.t-msg{font-size:11.5px;color:var(--tx-3);}
.t-cls{cursor:pointer;color:var(--tx-3);font-size:17px;flex-shrink:0;}

/* ══ ANIMATIONS ══ */
@keyframes fadU{from{opacity:0;transform:translateY(-14px);}to{opacity:1;transform:translateY(0);}}
@keyframes sR{from{opacity:0;transform:translateX(48px);}to{opacity:1;transform:translateX(0);}}

/* ══ RESPONSIVE ══ */
@media(max-width:1100px){.hero-panel{display:none;}.stats-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:1024px){
  .cat-grid{grid-template-columns:repeat(3,1fr);}
  .prov-grid{grid-template-columns:repeat(2,1fr);}
  .footer-top{grid-template-columns:1fr 1fr;}
  .rv-card{min-width:100%;}
  .prop-grid{grid-template-columns:1fr 1fr;}
  .badge-tiers{grid-template-columns:1fr 1fr 1fr;}
}
@media(max-width:768px){
  .nav-links,.nav-acts{display:none;}.ham{display:flex;}
  .hero{min-height:100svh;}
  .jobs-grid,.how-grid,.prop-grid{grid-template-columns:1fr;}
  .badge-tiers{grid-template-columns:1fr;}
  .cta-wrap{padding:40px 22px;}
  .footer-top{grid-template-columns:1fr;}
  .stats-grid{grid-template-columns:1fr 1fr;}
  .trending-wrap{gap:7px;}
}
@media(max-width:480px){
  .cat-grid{grid-template-columns:repeat(2,1fr);}
  .prov-grid{grid-template-columns:1fr;}
  .stats-grid{grid-template-columns:1fr;}
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
    <a href="<?= APP_URL ?>/search/providers.php">Find Talent</a>
    <a href="<?= APP_URL ?>/jobs.php">Browse Jobs</a>
    <a href="#how">How It Works</a>
    <a href="#categories">Categories</a>
    <a href="#trending">Trending</a>
  </div>
  <div class="nav-acts">
    <div class="lang-pill" onclick="toggleLang()" title="Switch language">🌍 <span id="langLabel">EN</span><div class="lang-inner" id="langAlt">TW</div></div>
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
  <a href="#how">How It Works</a>
  <a href="#categories">Categories</a>
  <a href="#trending">Trending</a>
  <?php if (isLoggedIn()): ?>
    <a href="<?= APP_URL ?>/<?= $user['role'] ?>/dashboard.php">Dashboard</a>
    <a href="<?= APP_URL ?>/auth/logout.php">Sign Out</a>
  <?php else: ?>
    <a href="<?= APP_URL ?>/auth/login.php">Sign In</a>
    <a href="<?= APP_URL ?>/auth/register.php">Get Started Free</a>
  <?php endif; ?>
</div>

<!-- ══════ HERO ══════ -->
<section class="hero">
  <div class="hero-slides">
    <div class="hero-slide hs1 active"></div>
    <div class="hero-slide hs2"></div>
    <div class="hero-slide hs3"></div>
    <div class="hero-slide hs4"></div>
    <div class="hero-slide hs5"></div>
    <div class="hero-slide hs6"></div>
  </div>

  <div class="hero-panel" id="heroPanel">
    <div class="panel-slide active">
      <div class="p-icon">🚀</div>
      <h3>Hire Elite African Talent</h3>
      <p>Vetted developers, designers, carpenters, nurses and more — ready to deliver world-class results.</p>
      <div class="panel-sub-badge">
        <div class="panel-sub-title">🆓 3 Jobs Free for Every Provider</div>
        <div class="panel-sub-text">Upgrade to Verified or Premium to unlock unlimited jobs &amp; top placement.</div>
      </div>
    </div>
    <div class="panel-slide">
      <div class="p-icon">🔒</div>
      <h3>Work &amp; Get Paid Securely</h3>
      <p>Escrow holds funds until you approve. Instant MoMo &amp; bank payouts for freelancers.</p>
    </div>
    <div class="panel-slide">
      <div class="p-icon">🌍</div>
      <h3>Every Ghanaian Skill Counts</h3>
      <p>From software engineers to skilled tradespeople — GigGhana connects every talent to paying opportunities.</p>
    </div>
    <div class="panel-dots" id="pDots">
      <div class="p-dot active" onclick="goPSlide(0)"></div>
      <div class="p-dot" onclick="goPSlide(1)"></div>
      <div class="p-dot" onclick="goPSlide(2)"></div>
    </div>
  </div>

  <div class="hero-content">
    <div class="hero-inner">
      <div class="hero-badge">
        <span>Ghana's #1 Marketplace for </span>
        <span class="ticker-wrap"><span class="ticker-text" id="profTicker">Developers</span></span>
      </div>
      <h1 class="hero-title">Your Skill. Your Success.<br><span class="gold">Your Ghana.</span></h1>
      <p class="hero-sub" id="heroSubText">Connecting every Ghanaian talent to opportunities that pay — across IT, trades, health, education, hospitality &amp; more.</p>

      <div class="search-outer">
        <form class="search-wrap" id="searchBar" action="<?= APP_URL ?>/search/providers.php" method="GET">
          <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:rgba(255,255,255,0.45);flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="text" name="q" id="searchInput" placeholder="e.g. Carpenter, Nurse, React Developer, Chef…" autocomplete="off">
          <div class="search-div"></div>
          <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?><option value="<?= htmlspecialchars($cat['id']) ?>"><?= sanitize($cat['name']) ?></option><?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-gold">Search</button>
        </form>
        <div class="autocomplete-drop" id="autocompleteDrop"></div>
      </div>

      <div class="hero-acts">
        <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-gold btn-lg">🏢 I Need Talent</a>
        <a href="<?= APP_URL ?>/auth/register.php?role=provider" class="btn btn-blue btn-lg">💼 I Have Skills</a>
      </div>

      <div class="hero-trust">
        <div class="trust-i"><div class="dot dot-g"></div>Secure Escrow</div>
        <div class="trust-i"><div class="dot dot-b"></div>Ghana Card Verified</div>
        <div class="trust-i"><div class="dot dot-gr"></div>MoMo &amp; Card</div>
        <div class="trust-i"><div class="dot dot-i"></div>3 Jobs Free</div>
      </div>
    </div>
  </div>

  <div class="hero-dots" id="heroDots">
    <div class="hero-dot active" onclick="goHSlide(0)"></div>
    <div class="hero-dot" onclick="goHSlide(1)"></div>
    <div class="hero-dot" onclick="goHSlide(2)"></div>
    <div class="hero-dot" onclick="goHSlide(3)"></div>
    <div class="hero-dot" onclick="goHSlide(4)"></div>
    <div class="hero-dot" onclick="goHSlide(5)"></div>
  </div>
</section>

<!-- ══════ STATS ══════ -->
<section class="stats-bar">
  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">👷</div><div class="stat-number" data-count="<?= $stats['providers'] ?>">0</div><div class="stat-label">Verified Freelancers</div></div>
    <div class="stat-card"><div class="stat-icon">💼</div><div class="stat-number" data-count="<?= $stats['jobs'] ?>">0</div><div class="stat-label">Open Jobs</div></div>
    <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-number" data-count="<?= $stats['completed'] ?>">0</div><div class="stat-label">Jobs Completed</div></div>
    <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-number" data-count="<?= number_format($stats['earnings']/1000,0) ?>" data-suffix="K+">0</div><div class="stat-label">GHS Paid to Talent</div></div>
  </div>
</section>

<!-- ══════ LIVE FEED ══════ -->
<?php if (!empty($liveJobs)): ?>
<section class="section" style="padding-top:28px;padding-bottom:28px;">
  <div style="max-width:1160px;margin:0 auto;">
    <div style="display:flex;align-items:center;gap:9px;margin-bottom:16px;">
      <div class="live-dot"></div>
      <h3 style="font-size:15px;font-family:var(--fm);font-weight:700;">🔥 Live Job Feed</h3>
      <span style="font-size:11px;color:var(--tx-3);margin-left:auto;">Real-time updates</span>
    </div>
    <?php foreach ($liveJobs as $lj): ?>
    <a href="<?= APP_URL ?>/jobs.php" class="feed-item" style="text-decoration:none;color:var(--tx);">
      <div>
        <div class="feed-ttl"><?= sanitize($lj['title']) ?></div>
        <div class="feed-meta"><?= sanitize($lj['cat_name'] ?? 'General') ?> · Posted <?= timeAgo($lj['created_at']) ?></div>
      </div>
      <div style="font-family:var(--fm);font-weight:700;color:var(--cyan);white-space:nowrap;font-size:13px;"><?= formatCurrency($lj['budget_min']) ?><?= $lj['budget_type']==='hourly' ? '/hr' : '' ?></div>
    </a>
    <?php endforeach; ?>
    <div style="text-align:center;margin-top:14px;"><a href="<?= APP_URL ?>/jobs.php" class="btn btn-ghost">View All Jobs →</a></div>
  </div>
</section>
<?php endif; ?>

<!-- ══════ CATEGORIES ══════ -->
<section class="section" id="categories">
  <div class="s-head">
    <div class="s-badge">Categories</div>
    <h2 class="s-title">Every Skill. Every Profession.</h2>
    <p class="s-sub">From cutting-edge tech to skilled trades — GigGhana covers every Ghanaian profession.</p>
  </div>
  <?php if (!empty($categories)): ?>
  <div class="cat-grid">
    <?php foreach ($categories as $cat): $isHot = in_array($cat['slug'] ?? $cat['id'], $hotSlugs); ?>
    <a href="<?= APP_URL ?>/search/providers.php?category=<?= htmlspecialchars($cat['id']) ?>" class="cat-card">
      <?php if ($isHot): ?><div class="cat-hot">🔥 HOT</div><?php endif; ?>
      <div class="cat-icon"><?= $iconMap[$cat['icon']] ?? '🔧' ?></div>
      <div class="cat-name"><?= sanitize($cat['name']) ?></div>
      <?php if (!empty($cat['description'])): ?><div class="cat-desc"><?= sanitize($cat['description']) ?></div><?php endif; ?>
      <div class="cat-arrow">Explore →</div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?><div class="empty"><span>📂</span>No categories yet.</div><?php endif; ?>
</section>

<!-- ══════ TRENDING ══════ -->
<section class="section" id="trending" style="padding-top:10px;padding-bottom:56px;">
  <div class="s-head">
    <div class="s-badge">Trending Now</div>
    <h2 class="s-title">Most In-Demand Skills</h2>
    <p class="s-sub">What Ghanaian businesses are searching for this week.</p>
  </div>
  <div class="trending-wrap">
    <?php $trends=[['💻','Web Developer','#1'],['🎨','Graphic Designer','#2'],['🔧','Plumber','#3'],['🏥','Home Nurse','#4'],['🍽️','Private Chef','#5'],['📷','Photographer','#6'],['🔌','Electrician','#7'],['📱','App Developer','#8'],['🌿','Landscaper','#9'],['🎓','Math Tutor','#10']];
    foreach ($trends as [$ic,$lb,$nm]): ?>
    <a href="<?= APP_URL ?>/search/providers.php?q=<?= urlencode($lb) ?>" class="trend-pill">
      <span><?= $ic ?></span><span><?= $lb ?></span><span class="trend-num"><?= $nm ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- ══════ AI MATCHED PROVIDERS ══════ -->
<?php if (!empty($matchedProviders)): ?>
<section class="section" style="background:linear-gradient(180deg,transparent,rgba(0,212,200,0.02),transparent);">
  <div class="s-head">
    <div class="s-badge">AI Matching</div>
    <h2 class="s-title">Recommended Freelancers</h2>
    <p class="s-sub">Smart suggestions based on platform ratings and activity.</p>
  </div>
  <div class="prov-grid">
    <?php foreach ($matchedProviders as $p):
      $sk=$p['skill_names']?array_filter(explode('|',$p['skill_names'])):[];
      $rk=rankLabel((int)$p['completed_jobs']); $init=initials($p['first_name'],$p['last_name']);
      $jobs=(int)$p['completed_jobs']; $bt=$jobs>=20?'premium':($jobs>=5?'verified':'free');
    ?>
    <div class="prov-card">
      <div class="prov-img-wrap">
        <?php if(!empty($p['avatar'])): ?><img src="<?= sanitize($p['avatar']) ?>" alt="<?= sanitize($p['first_name']) ?>" loading="lazy"><?php else: ?><div class="prov-initials"><?= $init ?></div><?php endif; ?>
        <?php if($p['is_verified']): ?><div class="prov-verified-badge">✓ Verified</div><?php endif; ?>
      </div>
      <div class="prov-body">
        <div class="prov-name"><?= sanitize($p['first_name'].' '.$p['last_name']) ?></div>
        <div class="prov-tag"><?= sanitize($p['tagline'] ?? ucfirst($p['experience_level'] ?? '').' Freelancer') ?></div>
        <div class="badge-row"><?php if($bt==='premium'): ?><span class="badge-premium">⭐ Premium</span><?php elseif($bt==='verified'): ?><span class="badge-verified">✓ Verified</span><?php else: ?><span class="badge-free">🌱 Beginner</span><?php endif; ?></div>
        <div class="prov-stars"><?php $rv=(float)$p['rating_avg']; for($s=1;$s<=5;$s++) echo $rv>=$s?'★':($rv>=$s-.5?'✦':'☆'); ?></div>
        <div class="prov-rc"><?= number_format($rv,1) ?> · <?= (int)$p['rating_count'] ?> reviews</div>
        <div class="prov-pills">
          <?php foreach(array_slice($sk,0,2) as $skill): ?><span class="skill-pill"><?= sanitize($skill) ?></span><?php endforeach; ?>
          <span class="<?= $rk['c'] ?>"><?= $rk['i'].' '.$rk['l'] ?></span>
        </div>
        <div class="prov-foot">
          <div class="prov-rate"><?= $p['hourly_rate']>0 ? formatCurrency($p['hourly_rate']) : 'Negotiable' ?><?php if($p['hourly_rate']>0): ?><small>/hr</small><?php endif; ?></div>
          <a href="<?= APP_URL ?>/profile.php?id=<?= $p['user_id'] ?>" class="btn btn-indigo" style="padding:6px 14px;font-size:11.5px;">Invite</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ══════ FEATURED PROVIDERS ══════ -->
<section class="section" style="background:linear-gradient(180deg,transparent,rgba(19,22,30,0.3),transparent);">
  <div class="s-head">
    <div class="s-badge">Top Talent</div>
    <h2 class="s-title">Featured Freelancers</h2>
    <p class="s-sub">Handpicked performers ready to bring your vision to life.</p>
  </div>
  <?php $avMap=['full_time'=>'Full Time','part_time'=>'Part Time','not_available'=>'Unavailable'];
  if (!empty($featured)): ?>
  <div class="prov-grid">
    <?php foreach ($featured as $pv):
      $sk=$pv['skill_names']?array_filter(explode('|',$pv['skill_names'])):[];
      $rk=rankLabel((int)$pv['completed_jobs']); $init=initials($pv['first_name'],$pv['last_name']);
      $jobs=(int)$pv['completed_jobs']; $bt=$jobs>=20?'premium':($jobs>=5?'verified':'free');
    ?>
    <div class="prov-card">
      <div class="prov-img-wrap">
        <?php if(!empty($pv['avatar'])): ?><img src="<?= sanitize($pv['avatar']) ?>" alt="<?= sanitize($pv['first_name']) ?>" loading="lazy"><?php else: ?><div class="prov-initials"><?= $init ?></div><?php endif; ?>
        <?php if($pv['is_verified']): ?><div class="prov-verified-badge">✓ Verified</div><?php endif; ?>
      </div>
      <div class="prov-body">
        <div class="prov-name"><?= sanitize($pv['first_name'].' '.$pv['last_name']) ?></div>
        <?php if(!empty($pv['location'])): ?><div class="prov-loc">📍 <?= sanitize($pv['location']) ?></div><?php endif; ?>
        <div class="prov-tag"><?= sanitize($pv['tagline'] ?? ucfirst($pv['experience_level'] ?? '').' Freelancer') ?></div>
        <div class="badge-row"><?php if($bt==='premium'): ?><span class="badge-premium">⭐ Premium</span><?php elseif($bt==='verified'): ?><span class="badge-verified">✓ Verified</span><?php else: ?><span class="badge-free">🌱 Beginner</span><?php endif; ?></div>
        <div class="prov-stars"><?php $rv=(float)$pv['rating_avg']; for($s=1;$s<=5;$s++) echo $rv>=$s?'★':($rv>=$s-.5?'✦':'☆'); ?></div>
        <div class="prov-rc"><?= number_format($rv,1) ?> (<?= (int)$pv['rating_count'] ?> reviews) · <?= $jobs ?> jobs done</div>
        <div class="prov-pills">
          <?php foreach(array_slice($sk,0,2) as $skill): ?><span class="skill-pill"><?= sanitize($skill) ?></span><?php endforeach; ?>
          <?php if(!empty($pv['availability'])): ?><span class="skill-pill"><?= $avMap[$pv['availability']]??'Available' ?></span><?php endif; ?>
          <span class="<?= $rk['c'] ?>"><?= $rk['i'].' '.$rk['l'] ?></span>
        </div>
        <div class="prov-foot">
          <div class="prov-rate"><?= $pv['hourly_rate']>0 ? formatCurrency($pv['hourly_rate']) : 'Negotiable' ?><?php if($pv['hourly_rate']>0): ?><small>/hr</small><?php endif; ?></div>
          <a href="<?= APP_URL ?>/profile.php?id=<?= $pv['user_id'] ?>" class="btn btn-indigo" style="padding:6px 14px;font-size:11.5px;">View Profile</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?><div class="empty"><span>👤</span>No freelancers yet. <a href="<?= APP_URL ?>/auth/register.php?role=provider" style="color:var(--cyan)">Be the first!</a></div><?php endif; ?>
  <div style="text-align:center;margin-top:32px;"><a href="<?= APP_URL ?>/search/providers.php" class="btn btn-ghost btn-lg">View All Freelancers →</a></div>
</section>

<!-- ══════ PROPOSALS ══════ -->
<section class="section" style="padding-top:32px;padding-bottom:56px;">
  <div class="s-head">
    <div class="s-badge">Proposals</div>
    <h2 class="s-title">Smart Proposal Templates</h2>
    <p class="s-sub">Win more jobs faster with professionally crafted starters.</p>
  </div>
  <div class="prop-grid">
    <div class="prop-card"><div class="prop-ico pi-a">⚡</div><h3 class="prop-title">Quick Professional</h3><p class="prop-text">Hello, I have carefully reviewed your project and can deliver high-quality results within 5 days. Fully committed to your satisfaction.</p></div>
    <div class="prop-card"><div class="prop-ico pi-b">🛠</div><h3 class="prop-title">Technical Proposal</h3><p class="prop-text">I will implement a scalable, modern architecture with thorough testing, documentation, and post-delivery support built to last.</p></div>
    <div class="prop-card"><div class="prop-ico pi-c">💡</div><h3 class="prop-title">Budget Friendly</h3><p class="prop-text">I can complete this project efficiently within your budget. Maximum value — quality work, on time, every time. No hidden fees.</p></div>
  </div>
</section>

<!-- ══════ RECENT JOBS ══════ -->
<section class="section">
  <div class="s-head">
    <div class="s-badge">Latest Opportunities</div>
    <h2 class="s-title">Recently Posted Jobs</h2>
    <p class="s-sub">Businesses across Ghana are actively looking for professionals like you.</p>
  </div>
  <?php if (!empty($recentJobs)): ?>
  <div class="jobs-grid">
    <?php foreach ($recentJobs as $job): ?>
    <a href="<?= APP_URL ?>/job-details.php?id=<?= $job['id'] ?>" class="job-card">
      <div class="job-top">
        <div class="job-ttl"><?= sanitize($job['title']) ?></div>
        <span class="jb <?= $job['is_urgent']?'jb-urgent':($job['is_featured']?'jb-feat':'jb-open') ?>"><?= $job['is_urgent']?'🔥 Urgent':($job['is_featured']?'⭐ Featured':'● Open') ?></span>
      </div>
      <div class="job-desc"><?= sanitize(mb_substr($job['description'],0,145)) ?>…</div>
      <div class="job-meta">
        <span><?= $iconMap[$job['cat_icon']??'']??'📂' ?> <?= sanitize($job['cat_name']??'General') ?></span>
        <span>🕒 <?= timeAgo($job['created_at']) ?></span>
        <span>📝 <?= (int)$job['proposal_count'] ?> proposals</span>
        <span class="job-budget"><?= formatCurrency($job['budget_min']) ?><?= $job['budget_max']>$job['budget_min']?' – '.formatCurrency($job['budget_max']):'' ?><?= $job['budget_type']==='hourly'?'/hr':'' ?></span>
      </div>
      <div class="job-poster">
        <div class="client-av"><?php if(!empty($job['client_avatar'])): ?><img src="<?= sanitize($job['client_avatar']) ?>" alt="" loading="lazy"><?php else: echo strtoupper(substr($job['first_name'],0,1)); endif; ?></div>
        <div class="client-info"><div class="client-name"><?= sanitize($job['first_name'].' '.$job['last_name']) ?></div><div class="client-lbl">Verified Client</div></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?><div class="empty"><span>💼</span>No open jobs yet. <a href="<?= APP_URL ?>/auth/register.php?role=client" style="color:var(--cyan)">Post the first job!</a></div><?php endif; ?>
  <div style="text-align:center;margin-top:32px;"><a href="<?= APP_URL ?>/jobs.php" class="btn btn-ghost btn-lg">Browse All Jobs →</a></div>
</section>

<!-- ══════ HOW IT WORKS ══════ -->
<section class="section" id="how" style="background:linear-gradient(180deg,transparent,rgba(19,22,30,0.2),transparent);">
  <div class="s-head">
    <div class="s-badge">Process</div>
    <h2 class="s-title">How GigGhana Works</h2>
    <p class="s-sub">Three simple steps between you and your next successful project.</p>
  </div>
  <div class="how-grid">
    <div class="how-card"><div class="how-step hs1c">1</div><h3 class="how-title">Create Your Profile</h3><p class="how-desc">Sign up free as Beginner, Verified or Premium. Clients post detailed job briefs. Freelancers browse hundreds of opportunities across Ghana &amp; Africa.</p></div>
    <div class="how-card"><div class="how-step hs2c">2</div><h3 class="how-title">Connect &amp; Negotiate</h3><p class="how-desc">Review proposals, compare portfolios, chat in real-time, and hire the perfect match. Define milestones and timelines collaboratively through the platform.</p></div>
    <div class="how-card"><div class="how-step hs3c">3</div><h3 class="how-title">Get Paid Securely</h3><p class="how-desc">Funds held in escrow until you approve. Freelancers get instant payouts via MTN MoMo, Vodafone Cash, AirtelTigo, or bank transfer.</p></div>
  </div>
  <div class="badge-tiers">
    <div class="badge-tier-card"><div class="bt-icon">🌱</div><div class="bt-name">Beginner</div><div class="bt-price">Free · 3 jobs included</div><div class="bt-perks">Get started at no cost. Basic profile listing and access to open jobs.</div></div>
    <div class="badge-tier-card featured"><div class="bt-icon">✓</div><div class="bt-name">Verified</div><div class="bt-price">₵49/mo · Unlimited jobs</div><div class="bt-perks">Verified badge, unlimited applications, higher search ranking &amp; client trust.</div></div>
    <div class="badge-tier-card"><div class="bt-icon">⭐</div><div class="bt-name">Premium</div><div class="bt-price">₵99/mo · Top placement</div><div class="bt-perks">Featured listing, top search placement, priority support &amp; exclusive jobs.</div></div>
  </div>
</section>

<!-- ══════ TESTIMONIALS ══════ -->
<section class="section" style="padding-top:32px;">
  <div class="s-head">
    <div class="s-badge">Reviews</div>
    <h2 class="s-title">Ghanaians Winning on GigGhana</h2>
    <p class="s-sub">Real feedback from painters, nurses, carpenters, chefs and more across Ghana.</p>
  </div>
  <div class="rv-carousel-outer">
    <div class="rv-track" id="rvTrack">
      <?php foreach ($reviews as $rv):
        $init=initials($rv['first_name'],$rv['last_name']); $rs=(float)$rv['rating_overall'];
        $profIcons=['provider'=>'💼','client'=>'🏢'];
      ?>
      <div class="rv-card">
        <div class="rv-stars"><?php for($s=1;$s<=5;$s++) echo $rs>=$s?'★':'☆'; ?></div>
        <div class="rv-text">"<?= sanitize($rv['comment']) ?>"</div>
        <div class="rv-author">
          <div class="rv-av"><?php if(!empty($rv['avatar'])): ?><img src="<?= sanitize($rv['avatar']) ?>" alt="" loading="lazy"><?php else: echo $init; endif; ?></div>
          <div>
            <div class="rv-name"><?= ($profIcons[$rv['role']]??'👤').' '.sanitize($rv['first_name'].' '.$rv['last_name']) ?></div>
            <div class="rv-role"><?= ucfirst($rv['role']) ?><?= $rv['location'] ? ' · '.sanitize($rv['location']) : '' ?></div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="rv-nav">
    <button class="rv-nav-btn" onclick="rvSlide(-1)">←</button>
    <button class="rv-nav-btn" onclick="rvSlide(1)">→</button>
  </div>
</section>

<!-- ══════ CTA ══════ -->
<div class="cta-wrap">
  <div class="cta-glo"></div><div class="cta-glo2"></div>
  <div class="cta-inner">
    <h2 class="cta-title">Join Thousands of Ghanaians Winning Every Day</h2>
    <p class="cta-sub">Join <?= number_format($stats['providers']) ?> verified freelancers and <?= number_format($stats['clients']) ?> businesses already on GigGhana. Africa's talent economy starts here.</p>
    <div class="cta-btns">
      <a href="<?= APP_URL ?>/auth/register.php?role=provider" class="btn btn-gold btn-lg">Sign Up as Provider</a>
      <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-blue btn-lg">Hire Talent</a>
    </div>
  </div>
</div>

<!-- ══════ EARNINGS CHART ══════ -->
<section class="section" style="padding-top:32px;">
  <div class="s-head">
    <div class="s-badge">Analytics</div>
    <h2 class="s-title">Track Your Earnings</h2>
    <p class="s-sub">Real earnings data from the platform — your personal dashboard updates after login.</p>
  </div>
  <div class="chart-box">
    <div class="chart-hd">
      <div>
        <div class="chart-ttl">Monthly Earnings Overview (<?= date('Y') ?>)</div>
        <div style="font-size:11.5px;color:var(--tx-3);margin-top:3px;"><?= $earningsTotal > 0 ? 'Live from escrow release transactions' : 'No completed transactions yet this year' ?></div>
      </div>
      <div>
        <div class="chart-kpi-val">₵<?= number_format($earningsTotal, 2) ?></div>
        <div class="chart-kpi-lbl">Total paid out (<?= date('Y') ?>)</div>
      </div>
    </div>
    <canvas id="earningsChart" height="105"></canvas>
  </div>
</section>

<!-- ══════ PAYMENT PARTNERS ══════ -->
<div class="pay-section">
  <div class="pay-inner">
    <div class="pay-ttl">Trusted Payment &amp; Technology Partners</div>
    <div class="pay-logos">
      <div class="pay-logo" title="MTN MoMo"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/9a/MTN_Logo.svg/512px-MTN_Logo.svg.png" alt="MTN MoMo" onerror="this.classList.add('err')"><span class="pay-txt">MTN MoMo</span></div>
      <div class="pay-logo" title="Vodafone Cash"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a6/Vodafone_icon.svg/512px-Vodafone_icon.svg.png" alt="Vodafone" onerror="this.classList.add('err')"><span class="pay-txt">Vodafone Cash</span></div>
      <div class="pay-logo" title="AirtelTigo" style="background:linear-gradient(135deg,var(--coral-dim),rgba(239,68,68,0.04));"><span style="font-family:var(--fm);font-weight:800;font-size:12px;color:var(--coral);white-space:nowrap;">AirtelTigo</span></div>
      <div class="pay-logo" title="Paystack"><img src="https://website-assets.paystack.com/assets/img/meta/paystack-logo-new.png" alt="Paystack" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/a/a5/Paystack_logo.png';this.onerror=function(){this.classList.add('err');}"><span class="pay-txt">Paystack</span></div>
      <div class="pay-logo" title="Visa"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/800px-Visa_Inc._logo.svg.png" alt="Visa" onerror="this.classList.add('err')"><span class="pay-txt">Visa</span></div>
      <div class="pay-logo" title="Mastercard"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png" alt="Mastercard" style="filter:none!important;opacity:1!important;" onerror="this.classList.add('err')"><span class="pay-txt">Mastercard</span></div>
      <div class="pay-logo" title="Telesoft" style="background:var(--violet-dim);"><span style="font-family:var(--fm);font-weight:800;font-size:12px;color:var(--violet);display:flex;align-items:center;gap:5px;">🧠 Telesoft</span></div>
      <div class="pay-logo" title="Secure Escrow" style="background:var(--green-dim);"><span style="font-family:var(--fm);font-weight:700;font-size:11.5px;color:var(--green);display:flex;align-items:center;gap:5px;">🔒 Secure Escrow</span></div>
    </div>
  </div>
</div>

<!-- ══════ FOOTER ══════ -->
<footer class="footer-wrap">
  <div class="footer-top">
    <div>
      <a href="<?= APP_URL ?>/index.php" class="logo"><div class="logo-mark">G</div><span class="logo-text">Gig<span>Ghana</span></span></a>
      <p class="footer-brand">Africa's premier freelance marketplace connecting every Ghanaian talent — from IT and design to trades, health and education — with forward-thinking businesses.</p>
      <div class="footer-nl">
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
        <li><a href="#">Upgrade Badge</a></li>
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

<?php if(isLoggedIn() && $user && $user['role']==='provider'): ?>
<div class="sub-banner" id="subBanner">
  <div class="sub-banner-text"><strong>🚀 Unlock More Jobs!</strong> You've used your 3 free applications. Upgrade to Verified or Premium to keep applying and stand out to clients.</div>
  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
    <a href="<?= APP_URL ?>/provider/upgrade.php" class="btn btn-gold" style="padding:8px 18px;font-size:13px;">Upgrade Now</a>
    <span class="sub-close" id="subClose">×</span>
  </div>
</div>
<?php endif; ?>

<div id="toast-c"></div>

<script>
/* ══ AUTOCOMPLETE ══ */
const acSuggestions=[
  {icon:'💻',text:'Web Developer',cat:'IT & Tech'},{icon:'📱',text:'App Developer',cat:'IT & Tech'},
  {icon:'📈',text:'Digital Marketer',cat:'IT & Tech'},{icon:'🎨',text:'Graphic Designer',cat:'Creative Arts'},
  {icon:'📷',text:'Photographer',cat:'Creative Arts'},{icon:'🎬',text:'Video Editor',cat:'Creative Arts'},
  {icon:'✍️',text:'Content Writer',cat:'Creative Arts'},{icon:'🔧',text:'Plumber',cat:'Skilled Trades'},
  {icon:'🪚',text:'Carpenter',cat:'Skilled Trades'},{icon:'🔌',text:'Electrician',cat:'Skilled Trades'},
  {icon:'🚗',text:'Mechanic',cat:'Skilled Trades'},{icon:'🏥',text:'Home Nurse',cat:'Health & Wellness'},
  {icon:'💪',text:'Fitness Coach',cat:'Health & Wellness'},{icon:'🏗️',text:'Builder / Contractor',cat:'Construction'},
  {icon:'🍽️',text:'Private Chef',cat:'Hospitality'},{icon:'🎉',text:'Event Planner',cat:'Hospitality'},
  {icon:'🚕',text:'Driver',cat:'Hospitality'},{icon:'📚',text:'Math Tutor',cat:'Education'},
  {icon:'🎵',text:'Music Instructor',cat:'Education'},{icon:'💼',text:'Business Consultant',cat:'Business Services'},
  {icon:'📊',text:'Accountant',cat:'Business Services'},{icon:'🌾',text:'Farmer / Agri-tech',cat:'Agriculture'},
  {icon:'📦',text:'Delivery Rider',cat:'Others'},{icon:'🌿',text:'Landscaper / Gardener',cat:'Others'},
  {icon:'🔐',text:'Security Guard',cat:'Others'},
];
const si=document.getElementById('searchInput');
const drop=document.getElementById('autocompleteDrop');
si.addEventListener('input',function(){
  const q=this.value.trim().toLowerCase();
  drop.innerHTML='';
  if(q.length<1){drop.classList.remove('open');return;}
  const hits=acSuggestions.filter(d=>d.text.toLowerCase().includes(q)||d.cat.toLowerCase().includes(q)).slice(0,6);
  if(!hits.length){drop.classList.remove('open');return;}
  hits.forEach(m=>{
    const el=document.createElement('div');el.className='auto-item';
    el.innerHTML=`<div class="auto-icon">${m.icon}</div><div><div class="auto-text">${m.text}</div><div class="auto-cat">${m.cat}</div></div>`;
    el.onclick=()=>{si.value=m.text;drop.classList.remove('open');document.getElementById('searchBar').submit();};
    drop.appendChild(el);
  });
  drop.classList.add('open');
});
document.addEventListener('click',e=>{if(!e.target.closest('.search-outer'))drop.classList.remove('open');});

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

/* ══ LANGUAGE ══ */
let curLang='en';
const i18n={
  en:{sub:'Connecting every Ghanaian talent to opportunities that pay — across IT, trades, health, education, hospitality & more.',alt:'TW',lbl:'EN'},
  tw:{sub:'GigGhana de Ghanafoɔ nyinaa ho adwuma na wɔtua ka pɛ — IT, adwuma, yadeɛ, adesua, ahosiesie ne ebi.',alt:'EN',lbl:'TW'},
};
function toggleLang(){
  curLang=curLang==='en'?'tw':'en';
  const t=i18n[curLang];
  document.getElementById('heroSubText').textContent=t.sub;
  document.getElementById('langLabel').textContent=t.lbl;
  document.getElementById('langAlt').textContent=t.alt;
  showToast('Language','Switched to '+(curLang==='en'?'English':'Twi'),'info');
}

/* ══ NAVBAR SCROLL ══ */
window.addEventListener('scroll',()=>{
  document.getElementById('nav').classList.toggle('on',window.scrollY>40);
  const bt=document.getElementById('backTop');if(bt)bt.classList.toggle('show',window.scrollY>400);
});

/* ══ STICKY SEARCH ══ */
(function(){
  const sb=document.getElementById('searchBar');if(!sb)return;
  window.addEventListener('scroll',()=>{
    if(window.scrollY>550){sb.style.cssText='position:fixed;top:74px;z-index:995;max-width:540px;left:50%;transform:translateX(-50%);width:88%;box-shadow:0 8px 36px rgba(0,0,0,0.5);border-radius:14px;';}
    else{sb.style.cssText='';}
  });
})();

/* ══ MOBILE MENU ══ */
function toggleMob(){
  const m=document.getElementById('mobNav'),h=document.getElementById('ham');
  m.classList.toggle('open');
  const sp=h.querySelectorAll('span');
  if(m.classList.contains('open')){sp[0].style.transform='rotate(45deg) translate(5px,5px)';sp[1].style.opacity='0';sp[2].style.transform='rotate(-45deg) translate(5px,-5px)';}
  else{sp.forEach(s=>{s.style.transform='';s.style.opacity='';});}
}

/* ══ HERO CAROUSEL ══ */
let hSlide=0;
const hSlides=document.querySelectorAll('.hero-slide');
const hDots=document.querySelectorAll('#heroDots .hero-dot');
function goHSlide(i){hSlides[hSlide].classList.remove('active');hDots[hSlide].classList.remove('active');hSlide=i;hSlides[hSlide].classList.add('active');hDots[hSlide].classList.add('active');}
if(hSlides.length) setInterval(()=>goHSlide((hSlide+1)%hSlides.length),5500);

/* ══ PANEL CAROUSEL ══ */
let pSlide=0;
const pSlides=document.querySelectorAll('#heroPanel .panel-slide');
const pDts=document.querySelectorAll('#pDots .p-dot');
function goPSlide(i){if(!pSlides[pSlide])return;pSlides[pSlide].classList.remove('active');pDts[pSlide].classList.remove('active');pSlide=i;pSlides[pSlide].classList.add('active');pDts[pSlide].classList.add('active');}
if(pSlides.length) setInterval(()=>goPSlide((pSlide+1)%pSlides.length),5200);

/* ══ PROFESSION TICKER ══ */
const profs=['Developers','Carpenters','Nurses','Graphic Designers','Chefs','Electricians','Teachers','Photographers','Mechanics','Accountants','Plumbers','Event Planners'];
let pIdx=0;
setInterval(()=>{
  pIdx=(pIdx+1)%profs.length;
  const el=document.getElementById('profTicker');if(!el)return;
  el.style.opacity='0';el.style.transform='translateY(-8px)';
  setTimeout(()=>{el.textContent=profs[pIdx];el.style.opacity='1';el.style.transform='translateY(0)';},280);
},2600);

/* ══ TESTIMONIAL CAROUSEL ══ */
let rvPos=0;
const rvTrack=document.getElementById('rvTrack');
function rvSlide(dir){
  if(!rvTrack)return;
  const cards=rvTrack.querySelectorAll('.rv-card');
  const visible=window.innerWidth<768?1:2;
  const max=Math.max(0,cards.length-visible);
  rvPos=Math.max(0,Math.min(rvPos+dir,max));
  const w=cards[0]?cards[0].offsetWidth+18:0;
  rvTrack.style.transform=`translateX(-${rvPos*w}px)`;
}
setInterval(()=>{
  if(!rvTrack)return;
  const cards=rvTrack.querySelectorAll('.rv-card');
  const visible=window.innerWidth<768?1:2;
  const max=Math.max(0,cards.length-visible);
  rvPos=rvPos>=max?0:rvPos+1;
  const w=cards[0]?cards[0].offsetWidth+18:0;
  rvTrack.style.transform=`translateX(-${rvPos*w}px)`;
},5800);

/* ══ STATS COUNTER ══ */
const obs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{
    if(!e.isIntersecting)return;
    const el=e.target,tgt=parseFloat(el.dataset.count)||0,sfx=el.dataset.suffix||'';
    let cur=0;const stp=tgt/(1800/16);
    const id=setInterval(()=>{cur=Math.min(cur+stp,tgt);el.textContent=Math.floor(cur).toLocaleString()+sfx;if(cur>=tgt)clearInterval(id);},16);
    obs.unobserve(el);
  });
},{threshold:0.5});
document.querySelectorAll('[data-count]').forEach(c=>obs.observe(c));

/* ══ EARNINGS CHART ══ */
(function(){
  const ctx=document.getElementById('earningsChart');if(!ctx)return;
  const data=<?= $earningsJson ?>;
  const hasData=data.some(v=>v>0);
  new Chart(ctx,{
    type:'line',
    data:{
      labels:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
      datasets:[{
        label:'Earnings Released (₵)',data,
        borderColor:'#00D4C8',backgroundColor:'rgba(0,212,200,0.07)',
        borderWidth:2.5,pointBackgroundColor:'#00D4C8',
        pointRadius:hasData?4:0,pointHoverRadius:6,fill:true,tension:0.42
      }]
    },
    options:{
      responsive:true,
      plugins:{legend:{display:false},tooltip:{backgroundColor:'#13161E',titleColor:'#F2F4F8',bodyColor:'#4E5A6E',borderColor:'rgba(0,212,200,0.15)',borderWidth:1,titleFont:{family:'Plus Jakarta Sans',weight:'700'},callbacks:{label:c=>' ₵'+c.parsed.y.toLocaleString('en-GH',{minimumFractionDigits:2})}}},
      scales:{x:{grid:{color:'rgba(78,90,110,0.08)'},ticks:{color:'#4E5A6E',font:{size:11,family:'DM Sans'}}},y:{grid:{color:'rgba(78,90,110,0.08)'},beginAtZero:true,ticks:{color:'#4E5A6E',font:{size:11,family:'DM Sans'},callback:v=>'₵'+v.toLocaleString()}}}
    }
  });
})();

/* ══ SCROLL REVEAL ══ */
const rvObs=new IntersectionObserver(entries=>{
  entries.forEach(e=>{if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='translateY(0)';}});
},{threshold:0.07,rootMargin:'0px 0px -32px 0px'});
document.querySelectorAll('.cat-card,.prov-card,.job-card,.how-card,.stat-card,.feed-item,.rv-card,.prop-card,.badge-tier-card,.trend-pill').forEach(el=>{
  el.style.opacity='0';el.style.transform='translateY(18px)';el.style.transition='opacity .45s ease,transform .45s ease';
  rvObs.observe(el);
});

/* ══ TOAST ══ */
const _TI={success:'✅',error:'❌',warning:'⚠️',info:'ℹ️'};
function showToast(title,msg,type='info',dur=4200){
  const c=document.getElementById('toast-c'),t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${_TI[type]||'ℹ️'}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.animation='sR .4s ease reverse';setTimeout(()=>t.remove(),400);},dur);
}

/* ══ NEWSLETTER ══ */
function subscribeNL(){
  const em=document.getElementById('nlEmail');
  if(!em||!em.value.includes('@')){showToast('Oops!','Please enter a valid email address.','error');return;}
  showToast('Subscribed! 🇬🇭','Thank you for joining GigGhana updates.','success');
  em.value='';
}

/* ══ SUBSCRIPTION BANNER ══ */
<?php if(isLoggedIn() && $user && $user['role']==='provider'): ?>
setTimeout(()=>{const b=document.getElementById('subBanner');if(b&&!sessionStorage.getItem('subDismissed'))b.classList.add('show');},4000);
document.getElementById('subClose')?.addEventListener('click',()=>{document.getElementById('subBanner').classList.remove('show');sessionStorage.setItem('subDismissed','1');});
<?php endif; ?>

<?php if(isset($_GET['success'])): ?>showToast('Success','<?= sanitize($_GET['success']) ?>','success');<?php endif; ?>
<?php if(isset($_GET['error'])): ?>showToast('Error','<?= sanitize($_GET['error']) ?>','error');<?php endif; ?>
<?php if(!isLoggedIn()): ?>setTimeout(()=>showToast('Welcome to GigGhana! 🇬🇭','Your Skill. Your Success. Your Ghana.','info',5500),1500);<?php endif; ?>
</script>
</body>
</html>