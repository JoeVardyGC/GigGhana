<?php
/**
 * GigGhana — profile.php (Public Profile)
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$profileUserId = (int)($_GET['id'] ?? 0);
if (!$profileUserId) redirect(APP_URL . '/search/providers.php');

try {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT u.id, u.first_name, u.last_name, u.avatar, u.bio,
                u.location, u.phone, u.ghana_card_verified,
                u.payment_verified, u.created_at AS member_since,
                p.id AS provider_id, p.tagline, p.hourly_rate, p.availability,
                p.experience_level, p.response_time, p.languages, p.portfolio_url,
                p.linkedin_url, p.github_url, p.completed_jobs, p.rating_avg,
                p.rating_count, p.is_verified, p.is_featured, p.profile_views,
                p.total_earnings, p.success_rate
         FROM users u
         JOIN providers p ON p.user_id = u.id
         WHERE u.id = ? AND u.is_active = 1 AND u.is_banned = 0 AND u.role = 'provider'
         LIMIT 1"
    );
    $stmt->execute([$profileUserId]);
    $profile = $stmt->fetch();
    if (!$profile) redirect(APP_URL . '/search/providers.php?error=Provider+not+found');

    if (!isset($_SESSION['viewed_profile_' . $profileUserId])) {
        $db->prepare("UPDATE providers SET profile_views = profile_views + 1 WHERE user_id = ?")->execute([$profileUserId]);
        $_SESSION['viewed_profile_' . $profileUserId] = true;
    }

    $stSkills = $db->prepare("SELECT s.name, ps.proficiency, c.name AS category FROM provider_skills ps JOIN skills s ON s.id = ps.skill_id LEFT JOIN categories c ON c.id = s.category_id WHERE ps.provider_id = ? ORDER BY c.sort_order ASC, ps.proficiency DESC, s.name ASC");
    $stSkills->execute([$profile['provider_id']]);
    $skills = $stSkills->fetchAll();
    $skillsByCategory = [];
    foreach ($skills as $sk) { $skillsByCategory[$sk['category'] ?: 'General'][] = $sk; }

    $stPort = $db->prepare("SELECT * FROM portfolio_items WHERE provider_id = ? ORDER BY sort_order ASC, created_at DESC LIMIT 30");
    $stPort->execute([$profile['provider_id']]);
    $portfolio = $stPort->fetchAll();

    $stRevs = $db->prepare("SELECT r.*, u.first_name, u.last_name, u.avatar, u.role AS reviewer_role, j.title AS job_title FROM reviews r JOIN users u ON u.id = r.reviewer_id JOIN jobs j ON j.id = r.job_id WHERE r.reviewee_id = ? AND r.is_public = 1 ORDER BY r.created_at DESC LIMIT 20");
    $stRevs->execute([$profileUserId]);
    $reviews = $stRevs->fetchAll();

    $rd = ['communication'=>0,'quality'=>0,'professionalism'=>0,'timeliness'=>0];
    $starDist = [5=>0,4=>0,3=>0,2=>0,1=>0];
    if (!empty($reviews)) {
        foreach (array_keys($rd) as $k) {
            $vals = array_filter(array_column($reviews,"rating_$k"),fn($v)=>(float)$v>0);
            $rd[$k] = $vals ? round(array_sum($vals)/count($vals),1) : 0;
        }
        foreach ($reviews as $rv) { $s=(int)round((float)$rv['rating_overall']); if(isset($starDist[$s])) $starDist[$s]++; }
    }

    $stJobs = $db->prepare("SELECT j.id,j.title,j.budget_min,j.budget_max,j.budget_type,j.created_at,c.name AS cat_name, u.first_name AS client_fn, u.last_name AS client_ln FROM jobs j LEFT JOIN categories c ON c.id=j.category_id LEFT JOIN users u ON u.id=j.client_id WHERE j.hired_provider_id=? AND j.status='completed' ORDER BY j.created_at DESC LIMIT 8");
    $stJobs->execute([$profile['provider_id']]);
    $completedJobs = $stJobs->fetchAll();

    $stCerts = $db->prepare("SELECT * FROM provider_verifications WHERE provider_id=? AND status='approved' ORDER BY created_at ASC");
    $stCerts->execute([$profile['provider_id']]);
    $certifications = $stCerts->fetchAll();

    $packages = [];
    try { $stPkg = $db->prepare("SELECT * FROM provider_packages WHERE provider_id = ? ORDER BY sort_order ASC LIMIT 3"); $stPkg->execute([$profile['provider_id']]); $packages = $stPkg->fetchAll(); } catch (Exception $e) {}

    $videoIntro = '';
    try { $vi = $db->prepare("SELECT video_intro_url FROM providers WHERE id=? LIMIT 1"); $vi->execute([$profile['provider_id']]); $videoIntro = $vi->fetchColumn() ?: ''; } catch (Exception $e) {}

    $simProviders = $db->query("SELECT u.first_name,u.last_name,u.avatar,u.location,p.tagline,p.rating_avg,p.rating_count,p.hourly_rate,p.user_id,p.is_verified,p.completed_jobs FROM providers p JOIN users u ON u.id=p.user_id WHERE p.id != {$profile['provider_id']} AND u.is_active=1 AND u.is_banned=0 ORDER BY p.rating_avg DESC, p.completed_jobs DESC LIMIT 4")->fetchAll();

    $isSaved = false;
    if (isLoggedIn() && $_SESSION['user_role'] === 'client') {
        $st = $db->prepare("SELECT 1 FROM saved_providers WHERE user_id=? AND provider_id=?");
        $st->execute([$_SESSION['user_id'], $profile['provider_id']]);
        $isSaved = (bool)$st->fetchColumn();
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    redirect(APP_URL . '/search/providers.php?error=Profile+unavailable');
}

$viewerUser = isLoggedIn() ? getUserById($_SESSION['user_id']) : null;
$isOwner    = isLoggedIn() && (int)$_SESSION['user_id'] === $profileUserId;
$csrf       = generateCSRF();

function profColor(string $p):string{ return match($p){'expert'=>'#1FD9A0','intermediate'=>'#F7B731',default=>'#4E5A6E'}; }
function profLabel(string $p):string{ return match($p){'expert'=>'Expert','intermediate'=>'Mid',default=>'Junior'}; }
function strs(float $r, int $max=5):string{ $o=''; for($i=1;$i<=$max;$i++) $o.=$r>=$i?'★':($r>=$i-.5?'✦':'☆'); return $o; }
function ini(string $f, string $l):string{ return strtoupper(substr($f,0,1).substr($l,0,1)); }

$avBadge = match($profile['availability']??'full_time'){
    'full_time'     => ['pb-av','🟢 Available Now'],
    'part_time'     => ['pb-pt','🟡 Part-time'],
    'not_available' => ['pb-na','🔴 Not Available'],
    default         => ['pb-av','🟢 Available Now']
};
$adDot  = match($profile['availability']??'full_time'){'full_time'=>'ad-green','part_time'=>'ad-amber',default=>'ad-red'};
$adText = match($profile['availability']??'full_time'){'full_time'=>'Available Now','part_time'=>'Part-time',default=>'Not Available'};

$jobs_done = (int)($profile['completed_jobs'] ?? 0);
$badgeTier = $jobs_done >= 20 ? 'premium' : ($jobs_done >= 5 ? 'verified' : 'beginner');
$showSubPrompt = $jobs_done >= 3 && $isOwner;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= sanitize($profile['first_name'].' '.$profile['last_name']) ?> — GigGhana Freelancer</title>
<meta name="description" content="<?= sanitize(substr($profile['tagline']??$profile['bio']??'Freelancer on GigGhana',0,160)) ?>">
<?php if(!empty($profile['avatar'])): ?><meta property="og:image" content="<?= sanitize($profile['avatar']) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════
   DESIGN TOKENS — Volcanic Charcoal × Electric Cyan × Coral
══════════════════════════════════════ */
:root{
  --bg:#0C0E14; --s1:#13161E; --s2:#191D27; --s3:#1F2433;
  --glass:rgba(19,22,30,0.82);

  --cyan:#00D4C8; --cyan-d:#00A89F; --cyan-l:#4DFFE8;
  --cyan-dim:rgba(0,212,200,0.10); --cyan-border:rgba(0,212,200,0.22);

  --coral:#FF6B4A; --coral-d:#E04D2E; --coral-l:#FF8F70;
  --coral-dim:rgba(255,107,74,0.10); --coral-border:rgba(255,107,74,0.25);

  --violet:#7C6FF7; --violet-d:#5D52E0;
  --violet-dim:rgba(124,111,247,0.10); --violet-border:rgba(124,111,247,0.22);

  --green:#1FD9A0; --green-d:#13B882; --green-dim:rgba(31,217,160,0.10);
  --red:#FF4D6A; --amber:#F7B731;

  --tx:#F2F4F8; --tx-2:#9BA8BF; --tx-3:#4E5A6E;
  --bd:rgba(255,255,255,0.065); --bd2:rgba(255,255,255,0.12);
  --gC:rgba(0,212,200,0.16); --gO:rgba(255,107,74,0.14);

  --fm:'Plus Jakarta Sans',sans-serif; --fb:'DM Sans',sans-serif;
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
  --green:#0DAF80; --green-d:#088C65; --green-dim:rgba(13,175,128,0.08);
  --amber:#D4980A; --red:#D93040;
  --tx:#0D1220; --tx-2:#344060; --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.09); --bd2:rgba(30,40,80,0.16);
  --gC:rgba(0,158,149,0.09); --gO:rgba(232,81,43,0.09);
}
.lm .navbar{background:rgba(243,245,250,0.96)!important;border-bottom-color:var(--bd);}
.lm .navbar.on{background:rgba(243,245,250,0.99)!important;}
.lm .mobile-nav{background:rgba(243,245,250,0.99);}
.lm .section-card,.lm .sidebar-card{background:rgba(255,255,255,0.9);border-color:var(--bd2);}
.lm .skill-chip,.lm .port-card,.lm .review-card,.lm .jh-item,.lm .meta-cell{background:rgba(255,255,255,0.75);border-color:var(--bd2);}
.lm .stat-mini{background:rgba(255,255,255,0.9);}
.lm .share-btn{background:rgba(255,255,255,0.6);border-color:var(--bd2);}
.lm .social-link{background:rgba(255,255,255,0.7);}
.lm .btn-ghost{border-color:var(--bd2);color:var(--tx-2);}
.lm .hero-rate-col{background:rgba(255,255,255,0.92);border-color:var(--bd2);}
.lm .pkg-card{background:rgba(255,255,255,0.85);}
.lm .sim-card{background:rgba(255,255,255,0.85);}
.lm .cta-banner{background:linear-gradient(135deg,var(--green-dim),var(--cyan-dim));}
.lm .hero-rate-num{color:var(--cyan);}
.lm .jh-budget{color:var(--cyan);}
.lm .sim-rate{color:var(--cyan);}
.lm .ss-val{color:var(--cyan);}
.lm .bio-toggle{color:var(--cyan);}
.lm .pkg-price{color:var(--coral);}
.lm .pkg-days{color:var(--green);}

/* ══ RESET ══ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);font-size:15px;line-height:1.65;overflow-x:hidden;transition:background .3s,color .3s;font-weight:400;-webkit-font-smoothing:antialiased;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:var(--bg);}::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
h1,h2,h3,.logo-text,.btn,.card-title{font-family:var(--fm);-webkit-font-smoothing:antialiased;}

/* ══ NAVBAR ══ */
.navbar{position:fixed;top:0;left:0;right:0;z-index:1000;display:flex;align-items:center;justify-content:space-between;padding:0 5%;height:64px;background:rgba(12,14,20,0.84);backdrop-filter:blur(24px);border-bottom:1px solid var(--bd);transition:var(--e);}
.navbar.on{background:rgba(12,14,20,0.97);box-shadow:0 2px 30px rgba(0,0,0,0.5);}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none;flex-shrink:0;}
.logo-mark{width:36px;height:36px;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:17px;color:#0C0E14;font-family:var(--fm);}
.logo-text{font-size:20px;font-weight:800;color:var(--tx);letter-spacing:-0.3px;}.logo-text span{color:var(--cyan);}
.nav-links{display:flex;align-items:center;gap:2px;}
.nav-links a{color:var(--tx-2);text-decoration:none;font-size:13.5px;font-weight:500;padding:6px 13px;border-radius:var(--rs);transition:var(--e);}
.nav-links a:hover{color:var(--tx);background:rgba(255,255,255,0.05);}
.nav-acts{display:flex;align-items:center;gap:8px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:var(--rs);font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:var(--e);white-space:nowrap;line-height:1.3;font-family:var(--fm);letter-spacing:0.01em;}
.btn-lg{padding:12px 26px;font-size:14px;border-radius:14px;}
.btn-ghost{background:transparent;color:var(--tx-2);border:1px solid var(--bd);}
.btn-ghost:hover{background:rgba(255,255,255,0.06);border-color:var(--bd2);color:var(--tx);}
.btn-gold{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:700;box-shadow:0 3px 16px var(--gO);}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--gO);}
.btn-green{background:linear-gradient(135deg,var(--green),var(--green-d));color:#0C0E14;font-weight:700;box-shadow:0 3px 12px var(--gC);}
.btn-green:hover{transform:translateY(-2px);}
.btn-indigo{background:linear-gradient(135deg,var(--violet),var(--violet-d));color:#fff;font-weight:700;}
.btn-indigo:hover{transform:translateY(-2px);}
.btn-outline-gold{background:transparent;border:1.5px solid var(--cyan-border);color:var(--cyan);font-weight:600;}
.btn-outline-gold:hover{background:var(--cyan-dim);}
.btn-theme{background:transparent;color:var(--tx-2);border:1px solid var(--bd);border-radius:var(--rs);padding:7px 11px;cursor:pointer;font-size:14px;transition:var(--e);line-height:1;font-family:var(--fb);}
.btn-theme:hover{background:rgba(255,255,255,0.07);}
.ham{display:none;flex-direction:column;gap:4.5px;cursor:pointer;padding:8px;}
.ham span{display:block;width:20px;height:2px;background:var(--tx);border-radius:2px;transition:var(--e);}
.mobile-nav{display:none;position:fixed;top:64px;left:0;right:0;background:rgba(12,14,20,0.98);backdrop-filter:blur(24px);border-bottom:1px solid var(--bd);padding:14px 5%;z-index:999;flex-direction:column;gap:4px;}
.mobile-nav.open{display:flex;}
.mobile-nav a{color:var(--tx-2);text-decoration:none;padding:10px 14px;border-radius:var(--rs);font-size:14px;font-weight:500;transition:var(--e);}
.mobile-nav a:hover{color:var(--tx);background:rgba(255,255,255,0.05);}

/* ══ HERO SECTION ══ */
.hero-section{position:relative;margin-top:64px;background:linear-gradient(135deg,#0C0E14 0%,#10141E 40%,#0E121C 100%);overflow:hidden;padding-bottom:24px;}
.hero-bg-mesh{position:absolute;inset:0;z-index:0;background:radial-gradient(ellipse 70% 80% at 30% 50%,rgba(0,212,200,0.07),transparent 65%),radial-gradient(ellipse 50% 60% at 80% 20%,rgba(255,107,74,0.05),transparent 55%),radial-gradient(ellipse 40% 50% at 60% 90%,rgba(124,111,247,0.06),transparent 55%);}
.hero-bg-grid{position:absolute;inset:0;z-index:0;background-image:linear-gradient(rgba(0,212,200,0.025) 1px,transparent 1px),linear-gradient(90deg,rgba(0,212,200,0.025) 1px,transparent 1px);background-size:52px 52px;}
.hero-section::after{content:'';position:absolute;bottom:0;left:0;right:0;height:80px;background:linear-gradient(transparent,var(--bg));z-index:1;pointer-events:none;}
.hero-inner{position:relative;z-index:2;max-width:1160px;margin:0 auto;padding:52px 28px 28px;display:flex;align-items:flex-end;gap:36px;flex-wrap:wrap;}

/* AVATAR */
.hero-avatar-col{flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:12px;}
.hero-avatar{width:210px;height:210px;border-radius:50%;border:5px solid var(--cyan-border);box-shadow:0 0 0 14px var(--cyan-dim),0 0 60px var(--gC),0 24px 64px rgba(0,0,0,0.65);background:linear-gradient(135deg,var(--violet-d),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:76px;font-weight:900;color:rgba(255,255,255,0.18);overflow:hidden;transition:transform .35s ease;position:relative;}
.hero-avatar:hover{transform:scale(1.03);}
.hero-avatar img{width:100%;height:100%;object-fit:cover;object-position:center top;}
.hero-avatar::before{content:'';position:absolute;inset:-5px;border-radius:50%;background:conic-gradient(var(--cyan),transparent 40%,var(--coral),transparent 80%,var(--cyan));animation:rotateBorder 7s linear infinite;z-index:-1;}
@keyframes rotateBorder{to{transform:rotate(360deg);}}
.hero-avail-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(12,14,20,0.85);backdrop-filter:blur(10px);border:1.5px solid var(--bd2);border-radius:50px;padding:6px 14px;font-size:12px;font-weight:700;font-family:var(--fm);}
.avail-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.ad-green{background:var(--green);box-shadow:0 0 8px rgba(31,217,160,0.7);animation:pulse 2s ease-in-out infinite;}
.ad-amber{background:var(--amber);}
.ad-red{background:var(--red);}
@keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(31,217,160,0.5);}50%{box-shadow:0 0 0 6px rgba(31,217,160,0);}}

/* HERO INFO */
.hero-info{flex:1;min-width:260px;padding-bottom:6px;}
.hero-breadcrumb{display:flex;align-items:center;gap:7px;font-size:12px;color:rgba(242,244,248,0.38);margin-bottom:16px;}
.hero-breadcrumb a{color:rgba(242,244,248,0.38);text-decoration:none;transition:var(--e);}
.hero-breadcrumb a:hover{color:var(--cyan);}
.hero-breadcrumb .sep{opacity:.35;}
.hero-name{font-family:var(--fm);font-size:clamp(28px,4.5vw,52px);font-weight:800;line-height:1.05;margin-bottom:9px;background:linear-gradient(135deg,var(--tx) 55%,var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.hero-tagline{color:var(--tx-2);font-size:16px;margin-bottom:14px;max-width:560px;line-height:1.6;font-weight:400;}
.hero-meta{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:16px;}
.hero-meta-item{display:flex;align-items:center;gap:5px;font-size:13px;color:rgba(242,244,248,0.55);}
.hero-meta-item strong{color:var(--tx);font-weight:600;}
.hero-badges{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:22px;}
.pbadge{display:inline-flex;align-items:center;gap:4px;padding:4px 11px;border-radius:50px;font-size:11px;font-weight:700;font-family:var(--fm);}
.pb-v  {background:var(--green-dim);border:1px solid rgba(31,217,160,0.3);color:var(--green);}
.pb-f  {background:var(--coral-dim);border:1px solid var(--coral-border);color:var(--coral);}
.pb-e  {background:var(--violet-dim);border:1px solid var(--violet-border);color:var(--violet);}
.pb-id {background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);}
.pb-av {background:var(--green-dim);border:1px solid rgba(31,217,160,0.22);color:var(--green);}
.pb-pt {background:rgba(247,183,49,0.1);border:1px solid rgba(247,183,49,0.22);color:var(--amber);}
.pb-na {background:rgba(255,77,106,0.1);border:1px solid rgba(255,77,106,0.22);color:var(--red);}
.pb-prem{background:var(--coral-dim);border:1px solid var(--coral-border);color:var(--coral);}
.pb-beg{background:rgba(78,90,110,0.1);border:1px solid rgba(78,90,110,0.18);color:var(--tx-3);}
.hero-ctas{display:flex;gap:10px;flex-wrap:wrap;}

/* HERO RATE CARD */
.hero-rate-col{flex-shrink:0;background:rgba(12,14,20,0.75);backdrop-filter:blur(20px);border:1px solid var(--cyan-border);border-radius:20px;padding:24px 22px;min-width:230px;text-align:center;align-self:center;}
.hero-rate-num{font-family:var(--fm);font-size:44px;font-weight:800;color:var(--cyan);line-height:1;}
.hero-rate-sub{font-size:13px;color:var(--tx-3);margin-bottom:3px;}
.hero-rate-lbl{font-size:11px;color:var(--tx-3);margin-bottom:18px;}
.hrm-row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;margin-bottom:7px;}
.hrm-k{color:var(--tx-3);}
.hrm-v{font-weight:600;}
.share-row{display:flex;gap:6px;margin-top:6px;}
.share-btn{flex:1;padding:8px 4px;border-radius:8px;font-size:11px;font-weight:600;background:rgba(255,255,255,0.04);border:1px solid var(--bd);color:var(--tx-3);cursor:pointer;text-align:center;transition:var(--e);font-family:var(--fm);}
.share-btn:hover{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}

/* STAT STRIP */
.stat-strip{max-width:1160px;margin:0 auto;padding:0 28px 24px;}
.stat-strip-inner{display:grid;grid-template-columns:repeat(5,1fr);gap:1px;background:var(--bd);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;}
.stat-mini{background:var(--glass);backdrop-filter:blur(14px);padding:16px 12px;text-align:center;transition:var(--e);}
.stat-mini:hover{background:var(--cyan-dim);}
.sm-val{font-family:var(--fm);font-size:22px;font-weight:800;line-height:1.1;margin-bottom:4px;background:linear-gradient(135deg,var(--cyan-l),var(--cyan));-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
.sm-lbl{font-size:10.5px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;}

/* PAGE BODY */
.page-body{max-width:1160px;margin:0 auto;padding:28px 28px 80px;}
.content-grid{display:grid;grid-template-columns:1fr 320px;gap:26px;align-items:start;}

/* SECTION CARDS */
.section-card{background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-bottom:24px;transition:border-color .3s;}
.section-card:hover{border-color:var(--cyan-border);}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:16px 22px;border-bottom:1px solid var(--bd);}
.card-title{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px;}
.card-badge{font-size:11px;color:var(--tx-3);background:rgba(255,255,255,0.04);border:1px solid var(--bd);padding:3px 10px;border-radius:50px;}
.card-body{padding:22px;}

/* CTA BANNER */
.cta-banner{background:linear-gradient(135deg,var(--green-dim),var(--cyan-dim));border:1px solid var(--cyan-border);border-radius:var(--r);padding:22px 26px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px;position:relative;overflow:hidden;}
.cta-banner::before{content:'';position:absolute;right:-40px;top:-40px;width:160px;height:160px;background:radial-gradient(circle,var(--gO),transparent 70%);pointer-events:none;}
.cta-text h3{font-family:var(--fm);font-size:17px;font-weight:800;margin-bottom:3px;}
.cta-text p{color:var(--tx-2);font-size:13px;font-weight:400;}
.cta-actions{display:flex;gap:8px;flex-shrink:0;}

/* SUB PROMPT */
.sub-prompt{background:linear-gradient(135deg,var(--coral-dim),rgba(255,143,112,0.06));border:1px solid var(--coral-border);border-radius:var(--r);padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:24px;flex-wrap:wrap;}
.sub-prompt-text{font-size:13.5px;color:var(--tx-2);font-weight:400;}
.sub-prompt-text strong{color:var(--coral);font-family:var(--fm);}

/* ABOUT */
.about-text{font-size:14.5px;line-height:1.9;color:var(--tx-2);font-weight:400;}
.about-text p{margin-bottom:13px;}
.about-text p:last-child{margin-bottom:0;}
.bio-wrap{position:relative;}
.bio-inner{max-height:170px;overflow:hidden;transition:max-height .4s ease;}
.bio-inner.open{max-height:9999px;}
.bio-fade{position:absolute;bottom:0;left:0;right:0;height:60px;background:linear-gradient(transparent,var(--glass));pointer-events:none;transition:opacity .3s;}
.bio-inner.open~.bio-fade{opacity:0;}
.bio-toggle{display:inline-flex;align-items:center;gap:4px;background:none;border:none;color:var(--cyan);font-size:13px;font-weight:600;cursor:pointer;margin-top:9px;padding:0;font-family:var(--fb);}

/* VIDEO INTRO */
.video-intro-wrap{margin-top:20px;padding-top:20px;border-top:1px solid var(--bd);}
.video-intro-lbl{font-size:11px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px;}
.video-intro-frame{border-radius:12px;overflow:hidden;background:#000;position:relative;padding-bottom:56.25%;height:0;}
.video-intro-frame iframe{position:absolute;inset:0;width:100%;height:100%;border:none;}

/* SKILLS */
.skills-cats{display:flex;flex-direction:column;gap:20px;}
.skill-cat-hd{font-size:10px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:1.2px;margin-bottom:9px;padding-bottom:6px;border-bottom:1px solid var(--bd);}
.skills-wrap{display:flex;flex-wrap:wrap;gap:8px;}
.skill-chip{display:inline-flex;align-items:center;gap:7px;padding:7px 12px;border-radius:9px;background:rgba(255,255,255,0.04);border:1px solid var(--bd);font-size:13px;font-weight:500;transition:var(--e);cursor:pointer;text-decoration:none;color:var(--tx);}
.skill-chip:hover{background:var(--cyan-dim);transform:translateY(-2px);border-color:var(--cyan-border);}
.sk-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0;}
.sk-lbl{font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:4px;}

/* PORTFOLIO */
.port-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(195px,1fr));gap:14px;}
.port-card{background:rgba(0,0,0,0.25);border:1px solid var(--bd);border-radius:12px;overflow:hidden;cursor:pointer;transition:var(--e);position:relative;}
.port-card:hover{transform:translateY(-5px);border-color:var(--cyan-border);box-shadow:0 16px 40px rgba(0,0,0,0.4);}
.port-thumb{height:150px;overflow:hidden;position:relative;background:linear-gradient(135deg,var(--s3),var(--violet-dim));display:flex;align-items:center;justify-content:center;font-size:36px;}
.port-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .5s;}
.port-card:hover .port-thumb img{transform:scale(1.07);}
.port-thumb video{width:100%;height:100%;object-fit:cover;}
.port-type-badge{position:absolute;top:7px;left:7px;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);color:#fff;padding:2px 8px;border-radius:5px;font-size:10px;font-weight:700;}
.port-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.62);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .3s;backdrop-filter:blur(3px);}
.port-card:hover .port-overlay{opacity:1;}
.port-view{background:rgba(255,255,255,0.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.25);color:#fff;padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;font-family:var(--fm);}
.port-body{padding:11px 13px;}
.port-title{font-weight:700;font-size:13px;font-family:var(--fm);margin-bottom:3px;}
.port-desc{font-size:11.5px;color:var(--tx-3);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}

/* PORTFOLIO MODAL */
.port-modal{display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,0.94);backdrop-filter:blur(22px);align-items:center;justify-content:center;padding:20px;}
.port-modal.open{display:flex;}
.port-modal-box{background:var(--s2);border:1px solid var(--bd2);border-radius:20px;max-width:860px;width:100%;overflow:hidden;animation:modalIn .3s ease;position:relative;}
@keyframes modalIn{from{opacity:0;transform:scale(.93);}to{opacity:1;transform:scale(1);}}
.port-modal-media{background:#000;min-height:200px;display:flex;align-items:center;justify-content:center;}
.port-modal-media img{max-height:500px;width:100%;object-fit:contain;}
.port-modal-media video,.port-modal-media iframe{max-height:480px;width:100%;border:none;}
.port-modal-info{padding:20px 24px;}
.port-modal-close{position:absolute;top:14px;right:16px;background:rgba(0,0,0,0.6);border:none;color:var(--tx-2);width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:17px;transition:var(--e);z-index:5;}
.port-modal-close:hover{background:rgba(255,77,106,0.2);color:var(--red);}
.port-modal-nav{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.55);border:1px solid var(--bd2);color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:20px;transition:var(--e);z-index:5;}
.port-modal-nav:hover{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);}
.port-modal-prev{left:12px;}.port-modal-next{right:12px;}

/* PRICING PACKAGES */
.pkg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.pkg-card{background:rgba(0,0,0,0.22);border:1px solid var(--bd);border-radius:var(--r);padding:24px;text-align:center;transition:var(--e);position:relative;overflow:hidden;}
.pkg-card:hover{transform:translateY(-4px);border-color:var(--cyan-border);box-shadow:0 16px 40px rgba(0,0,0,.3);}
.pkg-card.standard{border-color:var(--coral-border);background:var(--coral-dim);}
.pkg-card.standard::before{content:'POPULAR';position:absolute;top:12px;right:-22px;background:var(--coral);color:#fff;font-size:9px;font-weight:800;padding:3px 28px;transform:rotate(45deg);font-family:var(--fm);}
.pkg-icon{font-size:28px;margin-bottom:10px;}
.pkg-name{font-family:var(--fm);font-size:16px;font-weight:800;margin-bottom:8px;}
.pkg-price{font-family:var(--fm);font-size:30px;font-weight:900;color:var(--coral);line-height:1;margin-bottom:4px;}
.pkg-price small{font-size:13px;color:var(--tx-3);font-weight:400;}
.pkg-days{font-size:12px;color:var(--green);margin-bottom:10px;}
.pkg-desc{font-size:12.5px;color:var(--tx-2);line-height:1.7;margin-bottom:18px;font-weight:400;}
.pkg-btns{display:flex;flex-direction:column;gap:7px;}

/* REVIEWS */
.rating-wrap{display:grid;grid-template-columns:155px 1fr;gap:26px;align-items:center;padding:20px;background:var(--cyan-dim);border:1px solid var(--cyan-border);border-radius:var(--rs);margin-bottom:22px;}
.rating-big-n{font-family:var(--fm);font-size:66px;font-weight:900;color:var(--cyan);line-height:1;text-align:center;}
.rating-big-s{color:var(--amber);font-size:18px;letter-spacing:2px;text-align:center;margin:5px 0 4px;}
.rating-big-c{font-size:12px;color:var(--tx-3);text-align:center;}
.star-dist{display:flex;flex-direction:column;gap:6px;margin-bottom:14px;}
.sd-row{display:flex;align-items:center;gap:9px;font-size:12.5px;}
.sd-lbl{width:24px;text-align:right;color:var(--tx-3);}
.sd-track{flex:1;height:7px;background:rgba(255,255,255,0.05);border-radius:4px;overflow:hidden;}
.sd-fill{height:100%;background:linear-gradient(90deg,var(--cyan),var(--coral));border-radius:4px;width:0%;transition:width .9s ease;}
.sd-cnt{width:20px;font-size:11px;color:var(--tx-3);}
.rb-grid{display:grid;grid-template-columns:1fr 1fr;gap:7px;margin-top:12px;padding-top:12px;border-top:1px solid var(--bd);}
.rb-item{background:rgba(0,0,0,0.14);border-radius:8px;padding:9px 12px;}
.rb-item-lbl{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;}
.rb-item-track{height:4px;background:rgba(255,255,255,0.05);border-radius:3px;overflow:hidden;margin-bottom:4px;}
.rb-item-fill{height:100%;background:linear-gradient(90deg,var(--cyan),var(--green));border-radius:3px;width:0%;transition:width 1.1s ease;}
.rb-item-val{font-family:var(--fm);font-size:12.5px;font-weight:700;}
.reviews-list{display:flex;flex-direction:column;gap:13px;}
.review-card{background:rgba(0,0,0,0.17);border:1px solid var(--bd);border-radius:12px;padding:18px;transition:var(--e);position:relative;overflow:hidden;}
.review-card::before{content:'"';position:absolute;top:4px;right:14px;font-size:68px;font-family:Georgia,serif;color:var(--cyan);opacity:0.05;line-height:1;pointer-events:none;}
.review-card:hover{border-color:var(--cyan-border);}
.rv-top{display:flex;align-items:flex-start;gap:12px;margin-bottom:11px;}
.rv-av{width:40px;height:40px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--violet-d),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:14px;font-weight:700;color:#fff;}
.rv-av img{width:100%;height:100%;object-fit:cover;}
.rv-name{font-weight:700;font-size:14px;margin-bottom:1px;}
.rv-role{font-size:11px;color:var(--cyan);font-weight:600;margin-bottom:2px;}
.rv-job{font-size:11.5px;color:var(--tx-3);margin-bottom:4px;}
.rv-stars{color:var(--amber);font-size:13px;letter-spacing:1px;}
.rv-date{font-size:10.5px;color:var(--tx-3);white-space:nowrap;flex-shrink:0;}
.rv-verified{display:inline-flex;align-items:center;gap:4px;background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);padding:2px 8px;border-radius:5px;font-size:10px;font-weight:700;margin-bottom:9px;}
.rv-text{font-size:13.5px;color:var(--tx-2);line-height:1.8;font-style:italic;margin-bottom:10px;font-weight:400;}
.rv-breakdown{display:flex;flex-wrap:wrap;gap:7px;padding-top:10px;border-top:1px solid var(--bd);}
.rbd-pill{display:flex;align-items:center;gap:4px;background:rgba(255,255,255,0.03);border:1px solid var(--bd);border-radius:6px;padding:3px 8px;font-size:10.5px;color:var(--tx-3);}
.rbd-s{color:var(--amber);font-size:10px;}
.rv-actions{display:flex;align-items:center;gap:12px;margin-top:10px;padding-top:10px;border-top:1px solid var(--bd);}
.rv-helpful{font-size:11.5px;color:var(--tx-3);cursor:pointer;display:flex;align-items:center;gap:5px;transition:var(--e);}
.rv-helpful:hover{color:var(--green);}
.rv-report{font-size:11.5px;color:var(--tx-3);cursor:pointer;margin-left:auto;transition:var(--e);}
.rv-report:hover{color:var(--red);}

/* WORK HISTORY */
.jh-list{display:flex;flex-direction:column;gap:10px;}
.jh-item{display:flex;align-items:center;gap:13px;padding:14px 16px;background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:12px;transition:var(--e);}
.jh-item:hover{border-color:var(--green-dim);}
.jh-icon{width:36px;height:36px;border-radius:9px;background:var(--green-dim);border:1px solid rgba(31,217,160,0.18);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.jh-ttl{font-weight:700;font-size:13.5px;font-family:var(--fm);margin-bottom:2px;}
.jh-meta{font-size:11.5px;color:var(--tx-3);font-weight:400;}
.jh-right{margin-left:auto;display:flex;flex-direction:column;align-items:flex-end;gap:4px;}
.jh-badge{background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:700;}
.jh-budget{font-family:var(--fm);font-size:12.5px;font-weight:700;color:var(--cyan);}

/* CERTIFICATIONS */
.cert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px;}
.cert-card{background:var(--green-dim);border:1px solid rgba(31,217,160,0.15);border-radius:10px;padding:14px;text-align:center;transition:var(--e);}
.cert-card:hover{border-color:rgba(31,217,160,0.3);}
.cert-icon{font-size:24px;margin-bottom:7px;}
.cert-name{font-size:12px;font-weight:700;font-family:var(--fm);color:var(--green);margin-bottom:2px;}
.cert-status{font-size:10px;color:var(--tx-3);}

/* SIMILAR PROVIDERS */
.sim-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.sim-card{display:flex;align-items:center;gap:11px;padding:12px 14px;background:rgba(0,0,0,0.18);border:1px solid var(--bd);border-radius:12px;text-decoration:none;color:var(--tx);transition:var(--e);}
.sim-card:hover{border-color:var(--cyan-border);transform:translateY(-2px);}
.sim-av{width:42px;height:42px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--violet-d),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:14px;font-weight:700;color:#fff;}
.sim-av img{width:100%;height:100%;object-fit:cover;}
.sim-name{font-size:13px;font-weight:700;font-family:var(--fm);}
.sim-tag{font-size:11px;color:var(--tx-3);margin-top:1px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;font-weight:400;}
.sim-rate{font-size:12px;font-weight:700;color:var(--cyan);font-family:var(--fm);margin-top:2px;}

/* SIDEBAR */
.sidebar-card{background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-bottom:16px;}
.sc-head{padding:14px 18px;border-bottom:1px solid var(--bd);font-family:var(--fm);font-size:12.5px;font-weight:700;color:var(--tx-2);text-transform:uppercase;letter-spacing:.8px;}
.sc-body{padding:18px;}
.meta-cell{display:flex;justify-content:space-between;align-items:center;padding:9px 11px;background:rgba(0,0,0,0.14);border-radius:8px;font-size:13px;margin-bottom:7px;transition:var(--e);}
.meta-cell:last-child{margin-bottom:0;}
.meta-cell:hover{background:var(--cyan-dim);}
.mc-k{color:var(--tx-3);font-size:12.5px;font-weight:400;}
.mc-v{font-weight:600;}
.social-link{display:flex;align-items:center;gap:10px;padding:10px 12px;background:rgba(0,0,0,0.14);border:1px solid var(--bd);border-radius:9px;text-decoration:none;color:var(--tx);font-size:13px;font-weight:500;transition:var(--e);margin-bottom:7px;}
.social-link:last-child{margin-bottom:0;}
.social-link:hover{border-color:var(--cyan-border);background:var(--cyan-dim);}
.sl-icon{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.sl-web{background:var(--violet-dim);}
.sl-li{background:var(--cyan-dim);}
.sl-gh{background:rgba(255,255,255,0.05);}
.sidebar-stats{display:grid;grid-template-columns:1fr 1fr;gap:9px;}
.ss-cell{background:rgba(0,0,0,0.14);border:1px solid var(--bd);border-radius:10px;padding:12px;text-align:center;}
.ss-icon{font-size:18px;margin-bottom:4px;}
.ss-val{font-family:var(--fm);font-size:17px;font-weight:800;color:var(--cyan);}
.ss-lbl{font-size:10px;color:var(--tx-3);margin-top:1px;}

/* BADGE TOOLTIP */
.badge-tooltip-wrap{position:relative;display:inline-flex;}
.badge-tooltip{position:absolute;bottom:calc(100% + 8px);left:50%;transform:translateX(-50%);background:var(--s2);border:1px solid var(--bd2);border-radius:10px;padding:10px 14px;font-size:12px;color:var(--tx-2);white-space:nowrap;pointer-events:none;opacity:0;transition:opacity .22s;z-index:200;box-shadow:0 8px 24px rgba(0,0,0,0.5);font-weight:400;}
.badge-tooltip::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);border:5px solid transparent;border-top-color:var(--bd2);}
.badge-tooltip-wrap:hover .badge-tooltip{opacity:1;}

/* EMPTY */
.empty-block{text-align:center;padding:40px 20px;color:var(--tx-3);}
.empty-block .ei{font-size:38px;margin-bottom:10px;}

/* TOAST */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);max-width:330px;min-width:240px;box-shadow:0 12px 32px rgba(0,0,0,.5);animation:toastIn .35s ease;backdrop-filter:blur(14px);}
.toast.success{border-left:3px solid var(--green);}
.toast.error  {border-left:3px solid var(--red);}
.toast.info   {border-left:3px solid var(--cyan);}
.t-ico{font-size:16px;flex-shrink:0;}.t-bod{flex:1;}.t-ttl{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}.t-msg{font-size:11px;color:var(--tx-3);}.t-cls{cursor:pointer;color:var(--tx-3);font-size:17px;}
@keyframes toastIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}

/* ANCHORS */
.section-anchor{display:block;margin-top:-80px;padding-top:80px;visibility:hidden;position:absolute;}

/* RESPONSIVE */
@media(max-width:1100px){.content-grid{grid-template-columns:1fr;}}
@media(max-width:900px){.sim-grid{grid-template-columns:1fr;}.pkg-grid{grid-template-columns:1fr;}}
@media(max-width:768px){
  .nav-links,.nav-acts{display:none;}.ham{display:flex;}
  .hero-inner{padding:44px 16px 28px;gap:20px;}
  .hero-avatar{width:150px;height:150px;font-size:54px;}
  .hero-rate-col{display:none;}
  .stat-strip-inner{grid-template-columns:repeat(3,1fr);}
  .stat-strip-inner .stat-mini:nth-child(4),.stat-strip-inner .stat-mini:nth-child(5){display:none;}
  .port-grid{grid-template-columns:repeat(2,1fr);}
  .rating-wrap{grid-template-columns:1fr;}
  .rb-grid{grid-template-columns:1fr;}
  .cta-banner{flex-direction:column;}
  .page-body{padding:22px 16px 60px;}
  .stat-strip{padding:0 16px 20px;}
  .sim-grid{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:480px){.port-grid{grid-template-columns:1fr;}.stat-strip-inner{grid-template-columns:repeat(2,1fr);}.stat-strip-inner .stat-mini:nth-child(5){display:none;}.sim-grid{grid-template-columns:1fr;}}
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
    <a href="<?= APP_URL ?>/index.php#how">How It Works</a>
  </div>
  <div class="nav-acts">
    <button onclick="toggleTheme()" class="btn-theme" id="themeBtn">🌙</button>
    <?php if($isOwner): ?>
      <a href="<?= APP_URL ?>/provider/profile.php" class="btn btn-gold">✏️ Edit Profile</a>
    <?php elseif(isLoggedIn()): ?>
      <a href="<?= APP_URL ?>/<?= $viewerUser['role'] ?>/dashboard.php" class="btn btn-ghost">Dashboard</a>
    <?php else: ?>
      <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-ghost">Sign In</a>
      <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-gold">Post a Job</a>
    <?php endif; ?>
  </div>
  <div class="ham" id="ham" onclick="toggleMob()"><span></span><span></span><span></span></div>
</nav>

<div class="mobile-nav" id="mobNav">
  <a href="<?= APP_URL ?>/search/providers.php">Find Talent</a>
  <a href="<?= APP_URL ?>/jobs.php">Browse Jobs</a>
  <?php if(isLoggedIn()): ?>
    <a href="<?= APP_URL ?>/<?= $viewerUser['role'] ?>/dashboard.php">Dashboard</a>
    <a href="<?= APP_URL ?>/auth/logout.php">Sign Out</a>
  <?php else: ?>
    <a href="<?= APP_URL ?>/auth/login.php">Sign In</a>
    <a href="<?= APP_URL ?>/auth/register.php">Get Started</a>
  <?php endif; ?>
</div>

<!-- ══════ HERO ══════ -->
<div class="hero-section">
  <div class="hero-bg-mesh"></div>
  <div class="hero-bg-grid"></div>
  <div class="hero-inner">

    <!-- AVATAR -->
    <div class="hero-avatar-col">
      <div class="hero-avatar">
        <?php if(!empty($profile['avatar'])): ?>
          <img src="<?= sanitize($profile['avatar']) ?>" alt="<?= sanitize($profile['first_name'].' '.$profile['last_name']) ?>" onerror="this.style.display='none'">
        <?php else: echo ini($profile['first_name'],$profile['last_name']); endif; ?>
      </div>
      <div class="hero-avail-badge">
        <div class="avail-dot <?= $adDot ?>"></div>
        <span style="color:var(--tx-2);"><?= $adText ?></span>
      </div>
    </div>

    <!-- INFO -->
    <div class="hero-info">
      <div class="hero-breadcrumb">
        <a href="<?= APP_URL ?>/index.php">Home</a>
        <span class="sep">›</span>
        <a href="<?= APP_URL ?>/search/providers.php">Freelancers</a>
        <span class="sep">›</span>
        <span style="color:rgba(242,244,248,0.55);"><?= sanitize($profile['first_name'].' '.$profile['last_name']) ?></span>
      </div>
      <div class="hero-name"><?= sanitize($profile['first_name'].' '.$profile['last_name']) ?></div>
      <div class="hero-tagline"><?= sanitize($profile['tagline']??'Freelance Professional on GigGhana') ?></div>

      <div class="hero-meta">
        <?php if(!empty($profile['location'])): ?>
        <div class="hero-meta-item">📍 <strong><?= sanitize($profile['location']) ?></strong></div>
        <?php endif; ?>
        <div class="hero-meta-item">⭐ <strong><?= number_format((float)$profile['rating_avg'],1) ?></strong> (<?= (int)$profile['rating_count'] ?> reviews)</div>
        <div class="hero-meta-item">✅ <strong><?= (int)$profile['completed_jobs'] ?></strong> jobs done</div>
        <?php if(!empty($profile['response_time'])): ?>
        <div class="hero-meta-item">⏱ <strong><?= sanitize($profile['response_time']) ?></strong></div>
        <?php endif; ?>
        <div class="hero-meta-item">🗓 Member since <?= date('M Y',strtotime($profile['member_since'])) ?></div>
      </div>

      <div class="hero-badges">
        <div class="badge-tooltip-wrap">
          <?php if($badgeTier==='premium'): ?>
            <span class="pbadge pb-prem">⭐ Premium</span>
            <div class="badge-tooltip">Top-tier badge · Priority placement · Unlimited jobs</div>
          <?php elseif($badgeTier==='verified'): ?>
            <span class="pbadge pb-v">✓ Verified</span>
            <div class="badge-tooltip">Verified provider · Paid subscription · ID checked</div>
          <?php else: ?>
            <span class="pbadge pb-beg">🌱 Beginner</span>
            <div class="badge-tooltip">Getting started on GigGhana · First 3 jobs free</div>
          <?php endif; ?>
        </div>
        <?php if($profile['is_verified']): ?>
        <div class="badge-tooltip-wrap"><span class="pbadge pb-id">🪪 ID Verified</span><div class="badge-tooltip">Ghana Card identity confirmed</div></div>
        <?php endif; ?>
        <?php if($profile['is_featured']): ?>
        <div class="badge-tooltip-wrap"><span class="pbadge pb-f">⭐ Featured</span><div class="badge-tooltip">Handpicked by GigGhana team</div></div>
        <?php endif; ?>
        <?php if(($profile['experience_level']??'')==='expert'): ?>
        <div class="badge-tooltip-wrap"><span class="pbadge pb-e">🏆 Expert</span><div class="badge-tooltip">5+ years experience</div></div>
        <?php endif; ?>
        <?php if(!empty($profile['payment_verified'])): ?>
        <div class="badge-tooltip-wrap"><span class="pbadge pb-id">💳 Payment Verified</span><div class="badge-tooltip">Payment method confirmed</div></div>
        <?php endif; ?>
        <div class="badge-tooltip-wrap">
          <span class="pbadge <?= $avBadge[0] ?>"><?= $avBadge[1] ?></span>
          <div class="badge-tooltip">Current availability status</div>
        </div>
      </div>

      <div class="hero-ctas">
        <?php if($isOwner): ?>
          <a href="<?= APP_URL ?>/provider/profile.php" class="btn btn-gold btn-lg">✏️ Edit Profile</a>
          <a href="<?= APP_URL ?>/provider/dashboard.php" class="btn btn-ghost btn-lg">📊 Dashboard</a>
        <?php elseif(!isLoggedIn()): ?>
          <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-green btn-lg">💬 Hire <?= sanitize($profile['first_name']) ?></a>
          <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-ghost btn-lg">Sign In</a>
        <?php elseif(($viewerUser['role']??'')==='client'): ?>
          <a href="<?= APP_URL ?>/client/messages.php?start=<?= $profileUserId ?>" class="btn btn-green btn-lg">💬 Send Message</a>
          <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-gold btn-lg">📋 Invite to Job</a>
          <button class="btn btn-outline-gold" id="saveBtn" onclick="toggleSave(<?= $profile['provider_id'] ?>)"><?= $isSaved?'🔖 Saved':'📌 Save' ?></button>
        <?php endif; ?>
      </div>
    </div>

    <!-- RATE CARD (desktop) -->
    <div class="hero-rate-col">
      <?php if(($profile['hourly_rate']??0)>0): ?>
        <div class="hero-rate-num"><?= formatCurrency($profile['hourly_rate']) ?></div>
        <div class="hero-rate-sub">/hr</div>
        <div class="hero-rate-lbl">Hourly Rate · GHS</div>
      <?php else: ?>
        <div class="hero-rate-num" style="font-size:26px;">Negotiable</div>
        <div class="hero-rate-lbl" style="margin-bottom:18px;">Contact for pricing</div>
      <?php endif; ?>
      <div style="margin-bottom:18px;">
        <div class="hrm-row"><span class="hrm-k">📍 Location</span><span class="hrm-v"><?= sanitize($profile['location']??'Ghana') ?></span></div>
        <div class="hrm-row"><span class="hrm-k">🌍 Languages</span><span class="hrm-v"><?= sanitize($profile['languages']??'English') ?></span></div>
        <div class="hrm-row"><span class="hrm-k">🎓 Level</span><span class="hrm-v"><?= ucfirst($profile['experience_level']??'Intermediate') ?></span></div>
        <div class="hrm-row"><span class="hrm-k">⏱ Response</span><span class="hrm-v"><?= sanitize($profile['response_time']??'< 1 hr') ?></span></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <?php if($isOwner): ?>
          <a href="<?= APP_URL ?>/provider/profile.php" class="btn btn-gold" style="justify-content:center;">✏️ Edit Profile</a>
        <?php elseif(!isLoggedIn()): ?>
          <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-green" style="justify-content:center;">💬 Hire Now</a>
        <?php elseif(($viewerUser['role']??'')==='client'): ?>
          <a href="<?= APP_URL ?>/client/messages.php?start=<?= $profileUserId ?>" class="btn btn-green" style="justify-content:center;">💬 Message</a>
          <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-gold" style="justify-content:center;">📋 Invite to Job</a>
          <button class="btn btn-outline-gold" id="saveBtnSide" onclick="toggleSave(<?= $profile['provider_id'] ?>)" style="justify-content:center;"><?= $isSaved?'🔖 Saved':'📌 Save Provider' ?></button>
        <?php endif; ?>
        <div class="share-row">
          <button class="share-btn" onclick="copyLink()">🔗 Link</button>
          <button class="share-btn" onclick="shareOn('twitter')">𝕏</button>
          <button class="share-btn" onclick="shareOn('whatsapp')">💬</button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ══════ STAT STRIP ══════ -->
<div class="stat-strip">
  <div class="stat-strip-inner">
    <div class="stat-mini"><div class="sm-val" data-count="<?= (int)$profile['completed_jobs'] ?>">0</div><div class="sm-lbl">Jobs Done</div></div>
    <div class="stat-mini"><div class="sm-val"><?= number_format((float)$profile['rating_avg'],1) ?>★</div><div class="sm-lbl">Avg Rating</div></div>
    <div class="stat-mini"><div class="sm-val" data-count="<?= (int)$profile['rating_count'] ?>">0</div><div class="sm-lbl">Reviews</div></div>
    <div class="stat-mini"><div class="sm-val"><?= $profile['success_rate']?(int)$profile['success_rate'].'%':'100%' ?></div><div class="sm-lbl">Success Rate</div></div>
    <div class="stat-mini"><div class="sm-val" data-count="<?= (int)$profile['profile_views'] ?>">0</div><div class="sm-lbl">Profile Views</div></div>
  </div>
</div>

<!-- ══════ PAGE BODY ══════ -->
<div class="page-body">

  <?php if($showSubPrompt): ?>
  <div class="sub-prompt">
    <div class="sub-prompt-text"><strong>🚀 You've used your 3 free job slots!</strong> Upgrade to <strong>Verified (₵49/mo)</strong> or <strong>Premium (₵99/mo)</strong> to keep applying and get top placement on GigGhana.</div>
    <div style="display:flex;gap:8px;flex-shrink:0;"><a href="<?= APP_URL ?>/provider/upgrade.php" class="btn btn-gold" style="padding:9px 20px;">⭐ Upgrade Now</a></div>
  </div>
  <?php endif; ?>

  <?php if(!$isOwner && isLoggedIn() && ($viewerUser['role']??'')==='client'): ?>
  <div class="cta-banner">
    <div class="cta-text">
      <h3>Ready to work with <?= sanitize($profile['first_name']) ?>?</h3>
      <p><?= sanitize($profile['first_name']) ?> is <?= strtolower($adText) ?> — responds <?= sanitize($profile['response_time']??'fast') ?>.</p>
    </div>
    <div class="cta-actions">
      <a href="<?= APP_URL ?>/client/messages.php?start=<?= $profileUserId ?>" class="btn btn-green">💬 Message</a>
      <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-gold">📋 Post a Job</a>
    </div>
  </div>
  <?php endif; ?>

  <div class="content-grid">
    <div>

      <!-- ① ABOUT -->
      <span class="section-anchor" id="about"></span>
      <div class="section-card">
        <div class="card-header"><div class="card-title">👤 About <?= sanitize($profile['first_name']) ?></div></div>
        <div class="card-body">
          <?php if(!empty($profile['bio'])): ?>
          <div class="bio-wrap">
            <div class="bio-inner" id="bioInner">
              <div class="about-text">
                <?php foreach(explode("\n", htmlspecialchars($profile['bio'],ENT_QUOTES,'UTF-8')) as $para): if(trim($para)) echo '<p>'.nl2br($para).'</p>'; endforeach; ?>
              </div>
            </div>
            <?php if(strlen($profile['bio'])>450): ?>
            <div class="bio-fade" id="bioFade"></div>
            <button class="bio-toggle" id="bioToggle" onclick="toggleBio()">Show more ▾</button>
            <?php endif; ?>
          </div>
          <?php else: ?><div class="empty-block"><div class="ei">📝</div><p>No bio added yet.</p></div><?php endif; ?>

          <?php if(!empty($videoIntro)): ?>
          <div class="video-intro-wrap">
            <div class="video-intro-lbl">🎬 Video Introduction</div>
            <?php $ytMatch=preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/',$videoIntro,$vm); $viMatch=preg_match('/vimeo\.com\/(\d+)/',$videoIntro,$vvm); ?>
            <div class="video-intro-frame">
              <?php if($ytMatch): ?><iframe src="https://www.youtube.com/embed/<?= $vm[1] ?>" allowfullscreen></iframe>
              <?php elseif($viMatch): ?><iframe src="https://player.vimeo.com/video/<?= $vvm[1] ?>" allowfullscreen></iframe>
              <?php else: ?><video src="<?= sanitize($videoIntro) ?>" controls style="width:100%;max-height:300px;border-radius:12px;"></video><?php endif; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ② SKILLS -->
      <?php if(!empty($skills)): ?>
      <span class="section-anchor" id="skills"></span>
      <div class="section-card">
        <div class="card-header"><div class="card-title">🛠 Skills & Expertise</div><span class="card-badge"><?= count($skills) ?> skills</span></div>
        <div class="card-body">
          <div class="skills-cats">
            <?php foreach($skillsByCategory as $cat=>$catSkills): ?>
            <div>
              <div class="skill-cat-hd"><?= sanitize($cat) ?></div>
              <div class="skills-wrap">
                <?php foreach($catSkills as $sk): $c=profColor($sk['proficiency']); $l=profLabel($sk['proficiency']); ?>
                <a href="<?= APP_URL ?>/search/providers.php?skill=<?= urlencode($sk['name']) ?>" class="skill-chip" title="Find more <?= sanitize($sk['name']) ?> providers">
                  <div class="sk-dot" style="background:<?= $c ?>;"></div>
                  <?= sanitize($sk['name']) ?>
                  <span class="sk-lbl" style="background:<?= $c ?>22;color:<?= $c ?>;"><?= $l ?></span>
                </a>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <div style="display:flex;gap:13px;margin-top:18px;padding-top:14px;border-top:1px solid var(--bd);flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--tx-3);"><div style="width:7px;height:7px;border-radius:50%;background:var(--green);"></div>Expert</div>
            <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--tx-3);"><div style="width:7px;height:7px;border-radius:50%;background:var(--amber);"></div>Intermediate</div>
            <div style="display:flex;align-items:center;gap:5px;font-size:12px;color:var(--tx-3);"><div style="width:7px;height:7px;border-radius:50%;background:var(--tx-3);"></div>Junior</div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ③ PORTFOLIO -->
      <?php if(!empty($portfolio)): ?>
      <span class="section-anchor" id="portfolio"></span>
      <div class="section-card">
        <div class="card-header">
          <div class="card-title">🖼 Portfolio</div>
          <div style="display:flex;align-items:center;gap:9px;">
            <span class="card-badge"><?= count($portfolio) ?> item<?= count($portfolio)!==1?'s':'' ?></span>
            <?php if($isOwner): ?><a href="<?= APP_URL ?>/provider/profile.php#tab-portfolio" class="btn btn-ghost" style="padding:5px 12px;font-size:11.5px;">➕ Add Item</a><?php endif; ?>
          </div>
        </div>
        <div class="card-body">
          <div class="port-grid">
            <?php foreach($portfolio as $idx=>$item):
              $isVid=($item['item_type']??'image')==='video';
              $hasYt=!empty($item['video_url'])&&(str_contains($item['video_url'],'youtube')||str_contains($item['video_url'],'youtu.be'));
              $hasVimeo=!empty($item['video_url'])&&str_contains($item['video_url'],'vimeo');
            ?>
            <div class="port-card" onclick="openModal(<?= $idx ?>)">
              <div class="port-thumb">
                <?php if($isVid): ?>
                  <?php if($hasYt||$hasVimeo): ?><?php if(!empty($item['image_url'])): ?><img src="<?= sanitize($item['image_url']) ?>" alt="" loading="lazy"><?php else: ?><span>🎬</span><?php endif; ?>
                  <?php elseif(!empty($item['video_url'])): ?><video src="<?= sanitize($item['video_url']) ?>" muted loop playsinline preload="metadata"></video>
                  <?php elseif(!empty($item['image_url'])): ?><img src="<?= sanitize($item['image_url']) ?>" alt="" loading="lazy">
                  <?php else: ?><span>🎬</span><?php endif; ?>
                  <span class="port-type-badge">🎬 Video</span>
                <?php elseif(!empty($item['image_url'])): ?><img src="<?= sanitize($item['image_url']) ?>" alt="<?= sanitize($item['title']??'') ?>" loading="lazy">
                <?php else: ?><span>🖼</span><?php endif; ?>
                <div class="port-overlay">
                  <?php if(!empty($item['project_url'])): ?><a href="<?= sanitize($item['project_url']) ?>" class="port-view" target="_blank" rel="noopener" onclick="event.stopPropagation()">↗ View Live</a>
                  <?php else: ?><span class="port-view">🔍 Preview</span><?php endif; ?>
                </div>
              </div>
              <div class="port-body">
                <div class="port-title"><?= sanitize($item['title']??'Untitled') ?></div>
                <?php if(!empty($item['description'])): ?><div class="port-desc"><?= sanitize($item['description']) ?></div><?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ④ PRICING PACKAGES -->
      <?php if(!empty($packages)): ?>
      <span class="section-anchor" id="packages"></span>
      <div class="section-card">
        <div class="card-header"><div class="card-title">💰 Pricing Packages</div><span class="card-badge">GHS · Choose a plan</span></div>
        <div class="card-body">
          <div class="pkg-grid">
            <?php $pkgIcons=['basic'=>'🌱','standard'=>'⭐','premium'=>'🏆'];
            foreach($packages as $pk): ?>
            <div class="pkg-card <?= $pk['tier'] ?>">
              <div class="pkg-icon"><?= $pkgIcons[$pk['tier']]??'📦' ?></div>
              <div class="pkg-name"><?= sanitize($pk['name']) ?></div>
              <div class="pkg-price">₵<?= number_format($pk['price'],0) ?><small> / project</small></div>
              <div class="pkg-days">⏱ <?= (int)$pk['delivery_days'] ?> day<?= $pk['delivery_days']!=1?'s':'' ?> delivery</div>
              <div class="pkg-desc"><?= sanitize($pk['description']??'') ?></div>
              <div class="pkg-btns">
                <?php if(!$isOwner && isLoggedIn() && ($viewerUser['role']??'')==='client'): ?>
                  <a href="<?= APP_URL ?>/client/messages.php?start=<?= $profileUserId ?>&pkg=<?= $pk['tier'] ?>" class="btn btn-gold btn-sm" style="justify-content:center;">Hire Me — ₵<?= number_format($pk['price'],0) ?></a>
                  <a href="<?= APP_URL ?>/client/messages.php?start=<?= $profileUserId ?>&custom=1" class="btn btn-ghost btn-sm" style="justify-content:center;">Request Custom Offer</a>
                <?php elseif(!isLoggedIn()): ?>
                  <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-gold btn-sm" style="justify-content:center;">Hire Now</a>
                <?php else: ?><span class="btn btn-ghost btn-sm" style="justify-content:center;opacity:.5;">Your Package</span><?php endif; ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ⑤ REVIEWS -->
      <span class="section-anchor" id="reviews"></span>
      <div class="section-card">
        <div class="card-header"><div class="card-title">⭐ Client Reviews</div><span class="card-badge"><?= (int)$profile['rating_count'] ?> review<?= $profile['rating_count']!=1?'s':'' ?></span></div>
        <div class="card-body">
          <?php if(empty($reviews)): ?>
          <div class="empty-block"><div class="ei">⭐</div><p>No reviews yet. Be the first to leave one!</p></div>
          <?php else: ?>
          <div class="rating-wrap">
            <div>
              <div class="rating-big-n"><?= number_format((float)$profile['rating_avg'],1) ?></div>
              <div class="rating-big-s"><?= strs((float)$profile['rating_avg']) ?></div>
              <div class="rating-big-c"><?= (int)$profile['rating_count'] ?> reviews</div>
            </div>
            <div>
              <div class="star-dist">
                <?php for($s=5;$s>=1;$s--): $pct=count($reviews)>0?round($starDist[$s]/count($reviews)*100):0; ?>
                <div class="sd-row"><span class="sd-lbl"><?= $s ?>★</span><div class="sd-track"><div class="sd-fill" data-w="<?= $pct ?>"></div></div><span class="sd-cnt"><?= $starDist[$s] ?></span></div>
                <?php endfor; ?>
              </div>
              <div class="rb-grid">
                <?php foreach(['Communication'=>$rd['communication'],'Quality'=>$rd['quality'],'Professionalism'=>$rd['professionalism'],'Timeliness'=>$rd['timeliness']] as $lbl=>$val): ?>
                <div class="rb-item">
                  <div class="rb-item-lbl"><?= $lbl ?></div>
                  <div class="rb-item-track"><div class="rb-item-fill" data-w="<?= $val>0?round($val/5*100):0 ?>"></div></div>
                  <div class="rb-item-val"><?= number_format($val,1) ?></div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="reviews-list">
            <?php foreach($reviews as $rv): $rs=(float)$rv['rating_overall']; $profRole=ucfirst($rv['reviewer_role']??'Client'); ?>
            <div class="review-card">
              <div class="rv-top">
                <div class="rv-av"><?php if(!empty($rv['avatar'])): ?><img src="<?= sanitize($rv['avatar']) ?>" alt="" loading="lazy"><?php else: echo ini($rv['first_name'],$rv['last_name']); endif; ?></div>
                <div style="flex:1;min-width:0;">
                  <div class="rv-name"><?= sanitize($rv['first_name'].' '.$rv['last_name']) ?></div>
                  <div class="rv-role"><?= $profRole ?></div>
                  <div class="rv-job">📋 <?= sanitize($rv['job_title']) ?></div>
                  <div class="rv-stars"><?= strs($rs) ?> <span style="color:var(--tx-3);font-size:11px;margin-left:4px;"><?= number_format($rs,1) ?>/5.0</span></div>
                </div>
                <div class="rv-date"><?= timeAgo($rv['created_at']) ?></div>
              </div>
              <div class="rv-verified">✓ Verified Purchase</div>
              <?php if(!empty($rv['comment'])): ?><div class="rv-text">"<?= sanitize($rv['comment']) ?>"</div><?php endif; ?>
              <div class="rv-breakdown">
                <?php foreach(['Quality'=>$rv['rating_quality'],'Communication'=>$rv['rating_communication'],'Timeliness'=>$rv['rating_timeliness'],'Professional'=>$rv['rating_professionalism']] as $bl=>$bv): if((float)$bv>0): ?>
                <div class="rbd-pill"><span><?= $bl ?>:</span><span class="rbd-s"><?= strs(round($bv)) ?></span><strong style="color:var(--tx);font-size:10.5px;"><?= number_format($bv,1) ?></strong></div>
                <?php endif; endforeach; ?>
              </div>
              <div class="rv-actions">
                <div class="rv-helpful" onclick="markHelpful(this,<?= $rv['id'] ?>)">👍 Helpful</div>
                <div class="rv-report" onclick="reportReview(<?= $rv['id'] ?>)">🚩 Report</div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ⑥ WORK HISTORY -->
      <?php if(!empty($completedJobs)): ?>
      <span class="section-anchor" id="history"></span>
      <div class="section-card">
        <div class="card-header"><div class="card-title">✅ Work History</div><span class="card-badge"><?= (int)$profile['completed_jobs'] ?> total</span></div>
        <div class="card-body">
          <div class="jh-list">
            <?php foreach($completedJobs as $cj): ?>
            <div class="jh-item">
              <div class="jh-icon">✅</div>
              <div>
                <div class="jh-ttl"><?= sanitize($cj['title']) ?></div>
                <div class="jh-meta"><?= sanitize($cj['cat_name']??'General') ?><?= !empty($cj['client_fn'])?' · '.sanitize($cj['client_fn'].' '.$cj['client_ln']):'' ?></div>
              </div>
              <div class="jh-right">
                <span class="jh-badge">Completed</span>
                <?php if($cj['budget_min']>0): ?><span class="jh-budget"><?= formatCurrency($cj['budget_min']) ?><?= $cj['budget_max']>$cj['budget_min']?' – '.formatCurrency($cj['budget_max']):'' ?><?= $cj['budget_type']==='hourly'?'/hr':'' ?></span><?php endif; ?>
                <span style="font-size:10.5px;color:var(--tx-3);"><?= timeAgo($cj['created_at']) ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ⑦ CERTIFICATIONS -->
      <?php if(!empty($certifications)): ?>
      <span class="section-anchor" id="certs"></span>
      <div class="section-card">
        <div class="card-header"><div class="card-title">🏅 Certifications & Verification</div></div>
        <div class="card-body">
          <div class="cert-grid">
            <?php $cIcons=['id_verified'=>'🪪','payment_verified'=>'💳','skill_certified'=>'🎓','email_verified'=>'📧','phone_verified'=>'📱','background_check'=>'🔍'];
            $cLabels=['id_verified'=>'ID Verified','payment_verified'=>'Payment Verified','skill_certified'=>'Skill Certified','email_verified'=>'Email Verified','phone_verified'=>'Phone Verified','background_check'=>'Background Check'];
            foreach($certifications as $cert): ?>
            <div class="cert-card">
              <div class="cert-icon"><?= $cIcons[$cert['type']]??'✅' ?></div>
              <div class="cert-name"><?= $cLabels[$cert['type']]??sanitize($cert['type']) ?></div>
              <div class="cert-status">Approved · <?= date('M Y',strtotime($cert['created_at'])) ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- ⑧ SIMILAR PROVIDERS -->
      <?php if(!empty($simProviders)): ?>
      <div class="section-card">
        <div class="card-header"><div class="card-title">👥 Similar Freelancers</div></div>
        <div class="card-body">
          <div class="sim-grid">
            <?php foreach($simProviders as $sp): $sinit=strtoupper(substr($sp['first_name'],0,1).substr($sp['last_name'],0,1)); ?>
            <a href="<?= APP_URL ?>/profile.php?id=<?= $sp['user_id'] ?>" class="sim-card">
              <div class="sim-av"><?php if(!empty($sp['avatar'])): ?><img src="<?= sanitize($sp['avatar']) ?>" alt="" loading="lazy"><?php else: echo $sinit; endif; ?></div>
              <div style="min-width:0;">
                <div class="sim-name"><?= sanitize($sp['first_name'].' '.$sp['last_name']) ?></div>
                <div class="sim-tag"><?= sanitize($sp['tagline']??'Freelancer') ?></div>
                <div class="sim-rate"><?= ($sp['hourly_rate']??0)>0 ? formatCurrency($sp['hourly_rate']).'/hr' : 'Negotiable' ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div><!-- /main -->

    <!-- ══ SIDEBAR ══ -->
    <div>

      <!-- HIRE CARD -->
      <div class="sidebar-card">
        <div class="sc-head">💼 Hire <?= sanitize($profile['first_name']) ?></div>
        <div class="sc-body">
          <?php if(($profile['hourly_rate']??0)>0): ?>
          <div style="display:flex;align-items:baseline;gap:5px;margin-bottom:3px;">
            <span style="font-family:var(--fm);font-size:36px;font-weight:800;color:var(--cyan);line-height:1;"><?= formatCurrency($profile['hourly_rate']) ?></span>
            <span style="color:var(--tx-3);font-size:14px;">/hr</span>
          </div>
          <div style="font-size:11px;color:var(--tx-3);margin-bottom:16px;">Hourly Rate · GHS</div>
          <?php else: ?>
          <div style="font-family:var(--fm);font-size:22px;font-weight:800;color:var(--cyan);margin-bottom:3px;">Negotiable</div>
          <div style="font-size:11px;color:var(--tx-3);margin-bottom:16px;">Contact for pricing</div>
          <?php endif; ?>
          <div style="display:flex;flex-direction:column;gap:7px;margin-bottom:16px;">
            <div class="meta-cell"><span class="mc-k">⏱ Response</span><span class="mc-v"><?= sanitize($profile['response_time']??'< 1 hour') ?></span></div>
            <div class="meta-cell"><span class="mc-k">📍 Location</span><span class="mc-v"><?= sanitize($profile['location']??'Ghana') ?></span></div>
            <div class="meta-cell"><span class="mc-k">🌍 Languages</span><span class="mc-v"><?= sanitize($profile['languages']??'English') ?></span></div>
            <div class="meta-cell"><span class="mc-k">🎓 Level</span><span class="mc-v"><?= ucfirst($profile['experience_level']??'Intermediate') ?></span></div>
            <div class="meta-cell"><span class="mc-k">📅 Status</span><span class="mc-v"><?= match($profile['availability']??'full_time'){'full_time'=>'🟢 Available','part_time'=>'🟡 Part-time',default=>'🔴 Unavailable'} ?></span></div>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <?php if($isOwner): ?>
              <a href="<?= APP_URL ?>/provider/profile.php" class="btn btn-gold" style="justify-content:center;width:100%;">✏️ Edit My Profile</a>
              <a href="<?= APP_URL ?>/provider/dashboard.php" class="btn btn-ghost" style="justify-content:center;width:100%;">📊 Dashboard</a>
            <?php elseif(!isLoggedIn()): ?>
              <a href="<?= APP_URL ?>/auth/register.php?role=client" class="btn btn-green" style="justify-content:center;width:100%;">💬 Hire Now</a>
              <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-ghost" style="justify-content:center;width:100%;">Sign In</a>
            <?php elseif(($viewerUser['role']??'')==='client'): ?>
              <a href="<?= APP_URL ?>/client/messages.php?start=<?= $profileUserId ?>" class="btn btn-green" style="justify-content:center;width:100%;">💬 Send Message</a>
              <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-gold" style="justify-content:center;width:100%;">📋 Invite to Job</a>
              <button class="btn btn-outline-gold" id="saveBtnSbar" onclick="toggleSave(<?= $profile['provider_id'] ?>)" style="justify-content:center;width:100%;"><?= $isSaved?'🔖 Saved':'📌 Save Provider' ?></button>
            <?php endif; ?>
            <div class="share-row">
              <button class="share-btn" onclick="copyLink()">🔗 Copy</button>
              <button class="share-btn" onclick="shareOn('twitter')">𝕏 Tweet</button>
              <button class="share-btn" onclick="shareOn('whatsapp')">💬 Share</button>
            </div>
          </div>
        </div>
      </div>

      <!-- LINKS -->
      <?php if($profile['portfolio_url'] || $profile['linkedin_url'] || $profile['github_url']): ?>
      <div class="sidebar-card">
        <div class="sc-head">🔗 Links</div>
        <div class="sc-body" style="padding:13px 15px;">
          <?php if($profile['portfolio_url']): ?><a href="<?= sanitize($profile['portfolio_url']) ?>" class="social-link" target="_blank" rel="noopener"><div class="sl-icon sl-web">🌐</div><span>Portfolio Website</span><span style="margin-left:auto;font-size:11px;color:var(--tx-3);">↗</span></a><?php endif; ?>
          <?php if($profile['linkedin_url']): ?><a href="<?= sanitize($profile['linkedin_url']) ?>" class="social-link" target="_blank" rel="noopener"><div class="sl-icon sl-li">💼</div><span>LinkedIn</span><span style="margin-left:auto;font-size:11px;color:var(--tx-3);">↗</span></a><?php endif; ?>
          <?php if($profile['github_url']): ?><a href="<?= sanitize($profile['github_url']) ?>" class="social-link" target="_blank" rel="noopener"><div class="sl-icon sl-gh">🐙</div><span>GitHub</span><span style="margin-left:auto;font-size:11px;color:var(--tx-3);">↗</span></a><?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- QUICK STATS -->
      <div class="sidebar-card">
        <div class="sc-head">📊 Quick Stats</div>
        <div class="sc-body">
          <div class="sidebar-stats">
            <?php foreach([['✅','Jobs',(int)$profile['completed_jobs']],['⭐','Rating',number_format((float)$profile['rating_avg'],1)],['💬','Reviews',(int)$profile['rating_count']],['👁','Views',number_format((int)$profile['profile_views'])]] as [$i,$l,$v]): ?>
            <div class="ss-cell"><div class="ss-icon"><?= $i ?></div><div class="ss-val"><?= $v ?></div><div class="ss-lbl"><?= $l ?></div></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- PAGE NAV -->
      <div class="sidebar-card">
        <div class="sc-head">🗂 On This Page</div>
        <div class="sc-body" style="padding:10px 14px;">
          <?php
          $navItems=[['#about','👤 About Me'],['#skills','🛠 Skills']];
          if(!empty($portfolio)) $navItems[]=['#portfolio','🖼 Portfolio'];
          if(!empty($packages))  $navItems[]=['#packages','💰 Pricing'];
          $navItems[]=['#reviews','⭐ Reviews'];
          if(!empty($completedJobs)) $navItems[]=['#history','✅ Work History'];
          if(!empty($certifications)) $navItems[]=['#certs','🏅 Verifications'];
          foreach($navItems as [$href,$label]):
          ?>
          <a href="<?= $href ?>" style="display:flex;align-items:center;gap:8px;padding:9px 10px;border-radius:8px;text-decoration:none;color:var(--tx-2);font-size:13px;font-weight:500;transition:var(--e);" onmouseover="this.style.background='var(--cyan-dim)';this.style.color='var(--cyan)'" onmouseout="this.style.background='';this.style.color='var(--tx-2)'"><?= $label ?></a>
          <?php endforeach; ?>
        </div>
      </div>

    </div><!-- /sidebar -->
  </div>
</div>

<!-- ══════ PORTFOLIO LIGHTBOX ══════ -->
<div class="port-modal" id="portModal" onclick="if(event.target===this)closeModal()">
  <div class="port-modal-box">
    <button class="port-modal-close" onclick="closeModal()">✕</button>
    <button class="port-modal-nav port-modal-prev" onclick="navModal(-1)" id="modalPrev">‹</button>
    <button class="port-modal-nav port-modal-next" onclick="navModal(1)" id="modalNext">›</button>
    <div class="port-modal-media" id="portModalMedia"></div>
    <div class="port-modal-info">
      <div style="font-family:var(--fm);font-size:17px;font-weight:700;margin-bottom:5px;" id="portModalTitle"></div>
      <div style="font-size:13.5px;color:var(--tx-2);line-height:1.7;margin-bottom:12px;font-weight:400;" id="portModalDesc"></div>
      <div id="portModalLink"></div>
    </div>
  </div>
</div>

<div id="toast-c"></div>

<script>
const portData=<?= json_encode(array_map(fn($p)=>['image_url'=>$p['image_url']??'','video_url'=>$p['video_url']??'','item_type'=>$p['item_type']??'image','title'=>sanitize($p['title']??'Untitled'),'description'=>sanitize($p['description']??''),'project_url'=>$p['project_url']??''],$portfolio),JSON_HEX_TAG) ?>;
let modalIdx=0;

/* ══ THEME ══ */
function toggleTheme(){
  const l=document.body.classList.toggle('lm');
  localStorage.setItem('gg_theme',l?'light':'dark');
  document.getElementById('themeBtn').textContent=l?'☀️':'🌙';
}
(function(){
  if(localStorage.getItem('gg_theme')==='light'){document.body.classList.add('lm');const b=document.getElementById('themeBtn');if(b)b.textContent='☀️';}
})();

/* ══ NAVBAR ══ */
window.addEventListener('scroll',()=>document.getElementById('nav').classList.toggle('on',window.scrollY>40));

/* ══ MOBILE MENU ══ */
function toggleMob(){
  const m=document.getElementById('mobNav'),h=document.getElementById('ham');
  m.classList.toggle('open');
  const sp=h.querySelectorAll('span');
  if(m.classList.contains('open')){sp[0].style.transform='rotate(45deg) translate(5px,5px)';sp[1].style.opacity='0';sp[2].style.transform='rotate(-45deg) translate(5px,-5px)';}
  else{sp.forEach(s=>{s.style.transform='';s.style.opacity='';});}
}

/* ══ BIO TOGGLE ══ */
let bioOpen=false;
function toggleBio(){
  bioOpen=!bioOpen;
  document.getElementById('bioInner').classList.toggle('open',bioOpen);
  document.getElementById('bioToggle').textContent=bioOpen?'Show less ▴':'Show more ▾';
}

/* ══ PORTFOLIO MODAL ══ */
function openModal(idx){modalIdx=idx;renderModal(idx);document.getElementById('portModal').classList.add('open');document.body.style.overflow='hidden';}
function renderModal(idx){
  const item=portData[idx];if(!item)return;
  const m=document.getElementById('portModalMedia');m.innerHTML='';
  if(item.item_type==='video'&&item.video_url){
    if(item.video_url.includes('youtube')||item.video_url.includes('youtu.be')){
      const vid=item.video_url.match(/(?:v=|youtu\.be\/)([^&?/]+)/)?.[1];
      if(vid) m.innerHTML=`<iframe width="100%" height="420" src="https://www.youtube.com/embed/${vid}" frameborder="0" allowfullscreen></iframe>`;
    } else if(item.video_url.includes('vimeo')){
      const vid=item.video_url.match(/vimeo\.com\/(\d+)/)?.[1];
      if(vid) m.innerHTML=`<iframe width="100%" height="420" src="https://player.vimeo.com/video/${vid}" frameborder="0" allowfullscreen></iframe>`;
    } else {m.innerHTML=`<video src="${item.video_url}" controls style="width:100%;max-height:480px;"></video>`;}
  } else if(item.image_url){m.innerHTML=`<img src="${item.image_url}" alt="${item.title}" style="max-height:500px;width:100%;object-fit:contain;">`;}
  else{m.innerHTML=`<div style="height:200px;display:flex;align-items:center;justify-content:center;font-size:48px;">🖼</div>`;}
  document.getElementById('portModalTitle').textContent=item.title;
  document.getElementById('portModalDesc').textContent=item.description;
  document.getElementById('portModalLink').innerHTML=item.project_url?`<a href="${item.project_url}" class="btn btn-gold" target="_blank" rel="noopener">↗ Visit Project</a>`:'';
  document.getElementById('modalPrev').style.display=idx>0?'flex':'none';
  document.getElementById('modalNext').style.display=idx<portData.length-1?'flex':'none';
}
function navModal(d){const n=modalIdx+d;if(n>=0&&n<portData.length){modalIdx=n;renderModal(n);}}
function closeModal(){
  document.getElementById('portModal').classList.remove('open');document.body.style.overflow='';
  document.querySelectorAll('#portModalMedia video,#portModalMedia iframe').forEach(v=>{try{v.src=v.src;}catch(e){}});
}
document.addEventListener('keydown',e=>{
  if(!document.getElementById('portModal').classList.contains('open'))return;
  if(e.key==='Escape')closeModal();
  if(e.key==='ArrowLeft')navModal(-1);
  if(e.key==='ArrowRight')navModal(1);
});

/* ══ VIDEO HOVER ══ */
document.querySelectorAll('.port-thumb video').forEach(v=>{
  v.addEventListener('mouseenter',()=>v.play().catch(()=>{}));
  v.addEventListener('mouseleave',()=>{v.pause();v.currentTime=0;});
});

/* ══ RATING BARS ══ */
let barsAnimated=false;
function triggerBars(){
  if(barsAnimated)return;barsAnimated=true;
  document.querySelectorAll('.sd-fill,.rb-item-fill').forEach(el=>el.style.width=(el.dataset.w||0)+'%');
}
const rvObs=new IntersectionObserver(es=>{es.forEach(e=>{if(e.isIntersecting)triggerBars();});},{threshold:0.15});
document.querySelectorAll('.rating-wrap').forEach(el=>rvObs.observe(el));

/* ══ STAT COUNTERS ══ */
let cntDone=false;
function animateCounters(){
  if(cntDone)return;cntDone=true;
  document.querySelectorAll('[data-count]').forEach(el=>{
    const t=parseInt(el.dataset.count||0);if(!t){el.textContent='0';return;}
    let c=0;const s=t/(1400/16);
    const id=setInterval(()=>{c=Math.min(c+s,t);el.textContent=Math.floor(c).toLocaleString();if(c>=t)clearInterval(id);},16);
  });
}
const cObs=new IntersectionObserver(es=>{es.forEach(e=>{if(e.isIntersecting)animateCounters();});},{threshold:0.4});
document.querySelectorAll('.stat-strip-inner').forEach(el=>cObs.observe(el));

/* ══ SCROLL REVEAL ══ */
const revObs=new IntersectionObserver(es=>{
  es.forEach(e=>{if(e.isIntersecting){e.target.style.opacity='1';e.target.style.transform='translateY(0)';}});
},{threshold:0.04,rootMargin:'0px 0px -18px 0px'});
document.querySelectorAll('.section-card,.sidebar-card,.review-card,.port-card,.jh-item,.cert-card,.sim-card,.pkg-card').forEach((el,i)=>{
  el.style.opacity='0';el.style.transform='translateY(18px)';
  el.style.transition=`opacity .42s ease ${(i%5)*45}ms,transform .42s ease ${(i%5)*45}ms`;
  revObs.observe(el);
});

/* ══ SAVE TOGGLE ══ */
function toggleSave(pid){
  fetch('<?= APP_URL ?>/api/providers.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=toggle_save&provider_id=${pid}&csrf=<?= $csrf ?>`})
  .then(r=>r.json())
  .then(d=>{
    const t=d.saved?'🔖 Saved':'📌 Save';
    ['saveBtn','saveBtnSide','saveBtnSbar'].forEach(id=>{const el=document.getElementById(id);if(el)el.textContent=t+(id==='saveBtnSbar'?' Provider':'');});
    toast(d.saved?'Saved!':'Removed',d.saved?'Added to your saved list.':'Removed from saved.',d.saved?'success':'info');
  })
  .catch(()=>toast('Error','Could not save provider.','error'));
}

/* ══ SHARE ══ */
function copyLink(){navigator.clipboard.writeText(window.location.href).then(()=>toast('Copied!','Profile link copied to clipboard.','success')).catch(()=>toast('Error','Could not copy link.','error'));}
function shareOn(p){
  const url=encodeURIComponent(window.location.href);
  const name='<?= addslashes(sanitize($profile['first_name'].' '.$profile['last_name'])) ?>';
  const text=encodeURIComponent(`Check out ${name} on GigGhana!`);
  if(p==='twitter')window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`,'_blank');
  if(p==='whatsapp')window.open(`https://wa.me/?text=${text}%20${url}`,'_blank');
}

/* ══ REVIEW ACTIONS ══ */
function markHelpful(el,id){el.style.color='var(--green)';el.textContent='👍 Helpful (Marked)';el.style.pointerEvents='none';toast('Thanks!','Marked as helpful.','success');}
function reportReview(id){if(confirm('Report this review as inappropriate?'))toast('Reported','Our team will review this within 24 hours.','info');}

/* ══ TOAST ══ */
const TI={success:'✅',error:'❌',info:'ℹ️'};
function toast(title,msg,type='info',d=4200){
  const c=document.getElementById('toast-c'),t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${TI[type]||'ℹ️'}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(48px)';setTimeout(()=>t.remove(),350);},d);
}

<?php if(isset($_GET['hired'])): ?>toast('Invitation Sent!','<?= sanitize($profile['first_name']) ?> has been notified.','success');<?php endif; ?>
</script>
</body>
</html>