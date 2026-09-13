<?php
/**
 * GigGhana — provider/dashboard.php
 *
 * DB ALTERATIONS REQUIRED (run once):
 *   ALTER TABLE providers
 *     ADD COLUMN IF NOT EXISTS subscription_tier ENUM('free','verified','premium') DEFAULT 'free',
 *     ADD COLUMN IF NOT EXISTS proposals_used INT(11) DEFAULT 0,
 *     ADD COLUMN IF NOT EXISTS subscription_expires_at DATETIME DEFAULT NULL;
 *
 *   ALTER TABLE users
 *     ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL DEFAULT NULL;
 *
 * These are additive — no existing data is changed.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('provider');

$userId = (int)$_SESSION['user_id'];
$user   = getUserById($userId);

/* Theme from cookie (no flash) */
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';

/* Time-based greeting */
$hour     = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

try {
    $db = getDB();

    /* ── ENSURE COLUMNS EXIST (idempotent) ─────────────── */
    try {
        $db->exec("ALTER TABLE providers
            ADD COLUMN IF NOT EXISTS subscription_tier ENUM('free','verified','premium') DEFAULT 'free',
            ADD COLUMN IF NOT EXISTS proposals_used INT(11) DEFAULT 0,
            ADD COLUMN IF NOT EXISTS subscription_expires_at DATETIME DEFAULT NULL");
    } catch(Exception $e) { /* columns may already exist */ }
    try {
        $db->exec("ALTER TABLE users
            ADD COLUMN IF NOT EXISTS last_seen TIMESTAMP NULL DEFAULT NULL");
    } catch(Exception $e) { /* may already exist */ }

    /* Touch last_seen */
    $db->prepare("UPDATE users SET last_seen=NOW() WHERE id=?")->execute([$userId]);

    /* ── PROVIDER RECORD ──────────────────────────────── */
    $stP = $db->prepare("SELECT * FROM providers WHERE user_id=? LIMIT 1");
    $stP->execute([$userId]);
    $provider = $stP->fetch();
    if (!$provider) {
        $db->prepare("INSERT IGNORE INTO providers (user_id) VALUES (?)")->execute([$userId]);
        $stP->execute([$userId]);
        $provider = $stP->fetch();
    }
    $providerId = (int)$provider['id'];

    /* Subscription tier (default free) */
    $subTier     = $provider['subscription_tier'] ?? 'free';
    $subLimits   = ['free' => 3, 'verified' => 999, 'premium' => 999];
    $propLimit   = $subLimits[$subTier];
    $propUsed    = (int)($provider['proposals_used'] ?? 0);
    $subExpires  = $provider['subscription_expires_at'] ?? null;
    $subActive   = $subTier !== 'free' && ($subExpires === null || strtotime($subExpires) > time());

    /* ── STATS ────────────────────────────────────────── */
    $st = $db->prepare("SELECT COUNT(*) FROM proposals p
        JOIN jobs j ON j.id=p.job_id
        WHERE p.provider_id=? AND j.status='in_progress'");
    $st->execute([$providerId]); $activeJobs = (int)$st->fetchColumn();

    $st2 = $db->prepare("SELECT COUNT(*) FROM jobs WHERE hired_provider_id=? AND status='completed'");
    $st2->execute([$providerId]); $completedJobs = (int)$st2->fetchColumn();

    $st3 = $db->prepare("SELECT COUNT(*) FROM proposals WHERE provider_id=? AND status='pending'");
    $st3->execute([$providerId]); $pendingProposals = (int)$st3->fetchColumn();

    $st4 = $db->prepare("SELECT COUNT(*) FROM proposals WHERE provider_id=?");
    $st4->execute([$providerId]); $totalProposalsSent = (int)$st4->fetchColumn();

    $profileViews = (int)($provider['profile_views'] ?? 0);

    /* ── WALLET ───────────────────────────────────────── */
    $stW = $db->prepare("SELECT * FROM wallets WHERE user_id=? LIMIT 1");
    $stW->execute([$userId]);
    $wallet = $stW->fetch() ?: ['available_balance'=>0,'pending_balance'=>0,'total_earned'=>0];

    /* ── EARNINGS THIS MONTH ──────────────────────────── */
    $stEM = $db->prepare(
        "SELECT COALESCE(SUM(net_amount),0) FROM transactions
         WHERE user_id=? AND type='escrow_release' AND status='completed'
         AND YEAR(created_at)=YEAR(CURDATE()) AND MONTH(created_at)=MONTH(CURDATE())"
    );
    $stEM->execute([$userId]); $earningsMonth = (float)$stEM->fetchColumn();

    /* ── MONTHLY EARNINGS CHART (12 months) ──────────── */
    $stEC = $db->prepare(
        "SELECT MONTH(created_at) AS m, SUM(net_amount) AS total
         FROM transactions
         WHERE user_id=? AND type='escrow_release' AND status='completed'
         AND YEAR(created_at)=YEAR(CURDATE())
         GROUP BY MONTH(created_at) ORDER BY m ASC"
    );
    $stEC->execute([$userId]);
    $earningsArr = array_fill(1, 12, 0);
    foreach ($stEC->fetchAll() as $r) $earningsArr[(int)$r['m']] = (float)$r['total'];
    $earningsJson = json_encode(array_values($earningsArr));

    /* ── RECENT PROPOSALS ─────────────────────────────── */
    $stProp = $db->prepare(
        "SELECT p.*, j.title AS job_title, j.budget_min, j.budget_max, j.budget_type,
                j.status AS job_status, c.name AS cat_name,
                u.first_name AS client_fname, u.last_name AS client_lname, u.avatar AS client_avatar
         FROM proposals p
         JOIN jobs j     ON j.id  = p.job_id
         LEFT JOIN categories c ON c.id = j.category_id
         JOIN users u    ON u.id  = j.client_id
         WHERE p.provider_id=?
         ORDER BY p.created_at DESC LIMIT 6"
    );
    $stProp->execute([$providerId]); $proposals = $stProp->fetchAll();

    /* ── ACTIVE PROJECTS ─────────────────────────────── */
    $stAct = $db->prepare(
        "SELECT j.*, u.first_name AS client_fname, u.last_name AS client_lname,
                u.avatar AS client_avatar, c.name AS cat_name,
                (SELECT id FROM conversations
                 WHERE (user1_id=? AND user2_id=u.id)
                    OR (user2_id=? AND user1_id=u.id)
                 LIMIT 1) AS conv_id
         FROM jobs j
         JOIN users u ON u.id=j.client_id
         LEFT JOIN categories c ON c.id=j.category_id
         WHERE j.hired_provider_id=? AND j.status='in_progress'
         ORDER BY j.updated_at DESC LIMIT 5"
    );
    $stAct->execute([$userId,$userId,$providerId]); $activeProjects = $stAct->fetchAll();

    /* ── RECOMMENDED JOBS (skill-matched) ────────────── */
    $stRJ = $db->prepare(
        "SELECT j.*, c.name AS cat_name, c.icon AS cat_icon,
                u.first_name, u.last_name,
                (SELECT COUNT(*) FROM proposals WHERE job_id=j.id AND provider_id=?) AS already_applied
         FROM jobs j
         LEFT JOIN categories c ON c.id=j.category_id
         JOIN users u ON u.id=j.client_id
         WHERE j.status='open'
           AND j.id NOT IN (SELECT job_id FROM proposals WHERE provider_id=?)
         ORDER BY j.is_urgent DESC, j.is_featured DESC, j.created_at DESC
         LIMIT 5"
    );
    $stRJ->execute([$providerId,$providerId]); $recommendedJobs = $stRJ->fetchAll();

    /* ── REVIEWS ─────────────────────────────────────── */
    $stRev = $db->prepare(
        "SELECT r.*, u.first_name, u.last_name, u.avatar, j.title AS job_title
         FROM reviews r
         JOIN users u ON u.id=r.reviewer_id
         JOIN jobs j  ON j.id=r.job_id
         WHERE r.reviewee_id=? AND r.is_public=1
         ORDER BY r.created_at DESC LIMIT 4"
    );
    $stRev->execute([$userId]); $reviews = $stRev->fetchAll();

    /* ── VERIFICATIONS ───────────────────────────────── */
    $stVer = $db->prepare(
        "SELECT type, status FROM provider_verifications WHERE provider_id=?"
    );
    $stVer->execute([$providerId]); $verRows = $stVer->fetchAll();
    $verMap = [];
    foreach ($verRows as $v) $verMap[$v['type']] = $v['status'];

    /* ── NOTIFICATIONS ───────────────────────────────── */
    $stNotif = $db->prepare(
        "SELECT * FROM notifications WHERE user_id=? AND is_read=0
         ORDER BY created_at DESC LIMIT 8"
    );
    $stNotif->execute([$userId]); $notifs = $stNotif->fetchAll();
    $unreadNotifs = count($notifs);

    /* ── UNREAD MESSAGES ─────────────────────────────── */
    $stUM = $db->prepare(
        "SELECT COUNT(*) FROM messages m
         JOIN conversations c ON c.id=m.conversation_id
         WHERE (c.user1_id=? OR c.user2_id=?)
           AND m.sender_id!=? AND m.is_read=0 AND m.is_deleted=0"
    );
    $stUM->execute([$userId,$userId,$userId]); $unreadMsgs = (int)$stUM->fetchColumn();

    /* ── PROVIDER SKILLS ─────────────────────────────── */
    $stSkills = $db->prepare(
        "SELECT s.name, ps.proficiency FROM provider_skills ps
         JOIN skills s ON s.id=ps.skill_id
         WHERE ps.provider_id=? ORDER BY ps.proficiency DESC LIMIT 8"
    );
    $stSkills->execute([$providerId]); $mySkills = $stSkills->fetchAll();

    /* ── PERFORMANCE ─────────────────────────────────── */
    $successRate   = (float)($provider['success_rate'] ?? 0);
    $ratingAvg     = (float)($provider['rating_avg'] ?? 0);
    $responseTime  = $provider['response_time'] ?? '1 hour';

} catch(Exception $e) {
    error_log($e->getMessage());
    $provider=[]; $providerId=0;
    $subTier='free'; $propLimit=3; $propUsed=0; $subActive=false; $subExpires=null;
    $activeJobs=$completedJobs=$pendingProposals=$totalProposalsSent=$profileViews=0;
    $earningsMonth=0; $earningsJson='[0,0,0,0,0,0,0,0,0,0,0,0]';
    $wallet=['available_balance'=>0,'pending_balance'=>0,'total_earned'=>0];
    $proposals=$activeProjects=$recommendedJobs=$reviews=$notifs=$mySkills=[];
    $verMap=[]; $unreadNotifs=0; $unreadMsgs=0;
    $successRate=$ratingAvg=0; $responseTime='1 hour';
}

/* ── PROFILE COMPLETENESS ────────────────────────── */
$checks = [
    'Photo'       => !empty($user['avatar']),
    'Bio'         => strlen($user['bio']??'') >= 50,
    'Tagline'     => !empty($provider['tagline']),
    'Location'    => !empty($user['location']),
    'Rate'        => ($provider['hourly_rate']??0) > 0,
    'Skills'      => count($mySkills) >= 3,
    'Portfolio'   => !empty($provider['portfolio_url']),
    'Phone'       => !empty($user['phone']),
];
$completeness  = (int)(array_sum(array_map('intval',$checks)) / count($checks) * 100);
$missing       = array_keys(array_filter($checks, fn($v) => !$v));
$compColor     = $completeness >= 80 ? '#1FD9A0' : ($completeness >= 50 ? '#F7B731' : '#FF4D6A');

$csrf = generateCSRF();

$iconMap = [
    'code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
    'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
    'briefcase'=>'⚖️','headphones'=>'🎧','camera'=>'📷','globe'=>'🌐',
    'tool'=>'🔧','bar-chart'=>'📊','music'=>'🎵',
];

$subLabels = [
    'free'     => ['label'=>'Free Freelancer','icon'=>'🌱','color'=>'var(--tx-3)','bg'=>'rgba(78,90,110,0.1)','border'=>'rgba(78,90,110,0.18)'],
    'verified' => ['label'=>'Verified','icon'=>'✓','color'=>'var(--cyan)','bg'=>'var(--cyan-dim)','border'=>'var(--cyan-border)'],
    'premium'  => ['label'=>'Premium','icon'=>'⭐','color'=>'var(--coral)','bg'=>'var(--coral-dim)','border'=>'var(--coral-border)'],
];
$sub = $subLabels[$subTier] ?? $subLabels['free'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Provider Dashboard — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
/* ══════════════════════════════════════════════════════
   DESIGN TOKENS — Volcanic Charcoal × Electric Cyan × Coral
   (unified with index.php & client/dashboard.php)
══════════════════════════════════════════════════════ */
:root{
  --bg:#0C0E14; --s1:#13161E; --s2:#191D27; --s3:#1F2433;
  --glass:rgba(19,22,30,0.85);
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
  --sb:256px; --r:16px; --rs:10px; --e:all 0.26s cubic-bezier(.4,0,.2,1);
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
  --gC:rgba(0,158,149,0.09); --gO:rgba(232,81,43,0.09); --gV:rgba(91,79,217,0.09);
}
.lm .sidebar{background:var(--s1);border-right-color:var(--bd);}
.lm .topbar{background:rgba(243,245,250,0.96);}
.lm .sb-item{color:var(--tx-3);}
.lm .sb-item:hover{background:rgba(0,0,0,0.05);color:var(--tx);}
.lm .sb-item.active{background:var(--cyan-dim);color:var(--cyan);}
.lm .sb-user-card{background:rgba(0,0,0,0.06);}
.lm .stat-card{background:rgba(255,255,255,0.88);}
.lm .qa-card{background:rgba(255,255,255,0.85);}
.lm .section-card{background:rgba(255,255,255,0.9);}
.lm .sub-box{background:rgba(255,255,255,0.9);}
.lm .verify-card{background:rgba(255,255,255,0.85);}
.lm .perf-card{background:rgba(255,255,255,0.85);}
.lm .earn-box{background:rgba(255,255,255,0.88);}
.lm .job-mini:hover,.lm .prop-row:hover,.lm .proj-row:hover,
.lm .rev-item:hover,.lm .activity-item:hover{background:rgba(0,0,0,0.04);}
.lm .btn-ghost{background:rgba(0,0,0,0.05);border-color:var(--bd2);color:var(--tx-2);}
.lm .btn-ghost:hover{background:rgba(0,0,0,0.1);color:var(--tx);}
.lm .mobile-nav{background:rgba(243,245,250,0.98);border-top-color:var(--bd);}
.lm .toast{background:var(--s2);border-color:var(--bd2);}
.lm .notif-drop{background:var(--s2);}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);min-height:100vh;
  display:flex;font-size:14px;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
a{text-decoration:none;color:inherit;}
h1,h2,h3,h4,.logo-text,.stat-val,.card-ttl,.pkg-name{font-family:var(--fm);-webkit-font-smoothing:antialiased;}

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
.sb-logo{padding:22px 18px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:9px;}
.sb-logo-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));border-radius:9px;display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-weight:800;font-size:15px;color:#0C0E14;flex-shrink:0;}
.sb-logo-text{font-family:var(--fm);font-size:18px;font-weight:800;color:var(--tx);}
.sb-logo-text span{color:var(--cyan);}
.sb-nav{flex:1;padding:10px;overflow-y:auto;scrollbar-width:none;}
.sb-nav::-webkit-scrollbar{display:none;}
.sb-section{font-size:9px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;color:var(--tx-3);padding:6px 12px;margin:14px 0 4px;}
.sb-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--tx-3);font-size:13px;font-weight:500;transition:var(--e);text-decoration:none;}
.sb-item:hover{background:rgba(255,255,255,0.05);color:var(--tx);}
.sb-item.active{background:var(--cyan-dim);color:var(--cyan);border-left:3px solid var(--cyan);padding-left:9px;}
.sb-item.danger{color:var(--red);}
.sb-item.danger:hover{background:rgba(255,77,106,0.08);}
.sb-badge{margin-left:auto;background:var(--coral);color:#fff;font-size:9px;font-weight:800;padding:2px 7px;border-radius:50px;font-family:var(--fm);min-width:18px;text-align:center;}
.sb-badge.cyan{background:var(--cyan);color:#0C0E14;}
.sb-user{padding:14px 10px;border-top:1px solid var(--bd);}
.sb-user-card{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;background:rgba(0,0,0,0.2);transition:background .3s;}
.sb-av{width:36px;height:36px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:13px;font-weight:800;color:#fff;overflow:hidden;}
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
.topbar-title h1{font-size:20px;font-weight:800;line-height:1.1;}
.topbar-title p{font-size:11.5px;color:var(--tx-3);margin-top:1px;}
.topbar-right{display:flex;align-items:center;gap:8px;}
.notif-wrap{position:relative;}
.notif-btn{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.04);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;transition:var(--e);}
.notif-btn:hover{background:rgba(255,255,255,0.08);}
.notif-count{position:absolute;top:-4px;right:-4px;background:var(--coral);color:#fff;font-family:var(--fm);font-size:9px;font-weight:800;padding:2px 5px;border-radius:50px;min-width:17px;text-align:center;border:2px solid var(--bg);animation:pipA 2s ease-in-out infinite;}
@keyframes pipA{0%,100%{box-shadow:0 0 0 0 rgba(255,107,74,.5);}50%{box-shadow:0 0 0 4px rgba(255,107,74,0);}}
.notif-drop{display:none;position:absolute;top:calc(100%+8px);right:0;width:320px;background:var(--s2);border:1px solid var(--bd2);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,0.55);z-index:900;overflow:hidden;}
.notif-drop.open{display:block;animation:dropIn .18s ease;}
@keyframes dropIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
.nd-head{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--bd);font-family:var(--fm);font-size:13px;font-weight:700;}
.nd-mark{font-size:11px;color:var(--cyan);cursor:pointer;font-weight:600;}
.nd-item{display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-bottom:1px solid var(--bd);transition:var(--e);cursor:pointer;}
.nd-item:last-child{border-bottom:none;}
.nd-item:hover{background:rgba(255,255,255,0.03);}
.nd-ico{width:30px;height:30px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:14px;}
.nd-ttl{font-family:var(--fm);font-weight:700;font-size:12px;margin-bottom:2px;}
.nd-msg{font-size:11.5px;color:var(--tx-3);}
.nd-time{font-size:10px;color:var(--tx-3);margin-top:3px;}
.nd-empty{padding:24px;text-align:center;color:var(--tx-3);font-size:13px;}
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
.btn-green{background:linear-gradient(135deg,var(--green),var(--green-d));color:#0C0E14;font-weight:700;}
.btn-green:hover{transform:translateY(-2px);}
.btn-amber{background:linear-gradient(135deg,var(--amber),#E8A520);color:#0C0E14;font-weight:700;}
.btn-amber:hover{transform:translateY(-2px);}
.btn-red-soft{background:rgba(255,77,106,0.1);border:1px solid rgba(255,77,106,0.2);color:var(--red);}
.btn-red-soft:hover{background:rgba(255,77,106,0.18);}
.btn-sm{padding:5px 12px;font-size:11.5px;border-radius:8px;}
.btn-lg{padding:12px 26px;font-size:14px;border-radius:12px;}

/* ══ CONTENT ══ */
.content{padding:26px 28px 100px;}

/* ══ WELCOME BANNER ══ */
.welcome-banner{
  position:relative;overflow:hidden;border-radius:20px;
  background:linear-gradient(135deg,var(--s2) 0%,rgba(0,212,200,0.06) 50%,rgba(124,111,247,0.05) 100%);
  border:1px solid var(--cyan-border);padding:28px 32px;margin-bottom:26px;
  display:flex;align-items:center;justify-content:space-between;gap:16px;
  transition:background .3s;
}
.wb-glow{position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(0,212,200,0.1),transparent 70%);top:-100px;right:-60px;pointer-events:none;}
.wb-glow2{position:absolute;width:180px;height:180px;border-radius:50%;background:radial-gradient(circle,rgba(124,111,247,0.08),transparent 70%);bottom:-50px;left:40px;pointer-events:none;}
.wb-left{position:relative;z-index:1;flex:1;}
.wb-greeting{font-size:12px;font-weight:600;color:var(--cyan);text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;}
.wb-name{font-family:var(--fm);font-size:clamp(18px,2.5vw,26px);font-weight:800;margin-bottom:6px;line-height:1.1;}
.wb-tag{font-size:13px;color:var(--tx-2);margin-bottom:14px;line-height:1.5;}
/* Avatar in banner */
.wb-av{width:64px;height:64px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:22px;font-weight:800;color:#fff;overflow:hidden;border:3px solid var(--cyan-border);position:relative;z-index:1;}
.wb-av img{width:100%;height:100%;object-fit:cover;}
/* Rating stars */
.wb-stars{color:var(--amber);font-size:14px;letter-spacing:1px;margin-bottom:12px;}
/* Progress */
.wb-progress{display:flex;align-items:center;gap:10px;}
.wb-track{width:160px;height:5px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;}
.wb-fill{height:100%;background:linear-gradient(90deg,var(--cyan),var(--violet));border-radius:3px;transition:width 1.2s cubic-bezier(.16,1,.3,1);}
.wb-lbl{font-size:11px;color:var(--tx-3);}
.wb-right{display:flex;flex-direction:column;gap:9px;flex-shrink:0;position:relative;z-index:1;}

/* ══ STATS ══ */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:26px;}
.stat-card{
  background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);
  border-radius:var(--r);padding:22px 20px;transition:var(--e);position:relative;overflow:hidden;
}
.stat-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;transform:scaleX(0);transition:transform .35s;transform-origin:left;}
.stat-card.sc-cyan::after{background:var(--cyan);}
.stat-card.sc-coral::after{background:var(--coral);}
.stat-card.sc-violet::after{background:var(--violet);}
.stat-card.sc-green::after{background:var(--green);}
.stat-card.sc-amber::after{background:var(--amber);}
.stat-card:hover{transform:translateY(-4px);border-color:var(--bd2);}
.stat-card:hover::after{transform:scaleX(1);}
.stat-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;}
.stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.si-cyan  {background:var(--cyan-dim);border:1px solid var(--cyan-border);}
.si-coral {background:var(--coral-dim);border:1px solid var(--coral-border);}
.si-violet{background:var(--violet-dim);border:1px solid var(--violet-border);}
.si-green {background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);}
.si-amber {background:rgba(247,183,49,0.1);border:1px solid rgba(247,183,49,0.2);}
.stat-delta{font-size:10px;padding:2px 7px;border-radius:50px;font-weight:700;font-family:var(--fm);}
.delta-up  {background:var(--green-dim);color:var(--green);}
.delta-info{background:var(--cyan-dim);color:var(--cyan);}
.delta-warn{background:var(--coral-dim);color:var(--coral);}
.stat-val{font-family:var(--fm);font-size:28px;font-weight:800;line-height:1;margin-bottom:4px;}
.stat-lbl{font-size:12px;color:var(--tx-3);font-weight:500;}

/* ══ QUICK ACTIONS ══ */
.qa-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px;}
.qa-card{background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);border-radius:14px;padding:18px 14px;text-align:center;cursor:pointer;transition:var(--e);text-decoration:none;color:var(--tx);display:block;}
.qa-card:hover{transform:translateY(-5px);border-color:var(--cyan-border);box-shadow:0 12px 32px rgba(0,0,0,0.3);}
.qa-icon{width:44px;height:44px;border-radius:13px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center;font-size:19px;transition:transform .3s;}
.qa-card:hover .qa-icon{transform:scale(1.12) rotate(-5deg);}
.qa-label{font-family:var(--fm);font-size:12px;font-weight:700;}
.qa-sub{font-size:10.5px;color:var(--tx-3);margin-top:2px;}

/* ══ DASH GRID ══ */
.dash-grid{display:grid;grid-template-columns:1fr 320px;gap:22px;}

/* ══ SECTION CARDS ══ */
.section-card{background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;margin-bottom:20px;transition:background .3s,border-color .3s;}
.sc-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--bd);}
.card-ttl{font-family:var(--fm);font-size:15px;font-weight:700;}
.card-cnt{font-size:11px;color:var(--tx-3);}
.empty-state{text-align:center;padding:44px 20px;color:var(--tx-3);}
.es-ico{font-size:36px;margin-bottom:10px;}
.es-ttl{font-family:var(--fm);font-size:15px;font-weight:700;margin-bottom:4px;color:var(--tx-2);}
.es-sub{font-size:13px;}

/* ══ SUBSCRIPTION BOX ══ */
.sub-box{
  background:var(--s2);border:1px solid var(--bd);
  border-radius:var(--r);padding:22px;margin-bottom:20px;
  transition:background .3s;
}
.sub-tier-row{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.sub-tier-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:50px;font-family:var(--fm);font-size:12px;font-weight:800;background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);}
.sub-usage-lbl{display:flex;align-items:center;justify-content:space-between;font-size:12px;color:var(--tx-2);margin-bottom:6px;}
.sub-usage-lbl strong{color:var(--tx);}
.sub-bar-track{height:8px;background:rgba(255,255,255,0.06);border-radius:4px;overflow:hidden;margin-bottom:14px;}
.sub-bar-fill{height:100%;border-radius:4px;transition:width 1s ease;}
.sub-bar-fill.safe  {background:linear-gradient(90deg,var(--cyan),var(--green));}
.sub-bar-fill.warn  {background:linear-gradient(90deg,var(--amber),var(--coral));}
.sub-bar-fill.full  {background:linear-gradient(90deg,var(--coral),var(--red));}
.sub-alert{background:var(--coral-dim);border:1px solid var(--coral-border);border-radius:var(--rs);padding:12px 14px;font-size:12.5px;color:var(--coral);margin-bottom:14px;display:flex;align-items:center;gap:8px;}
.sub-plans{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;}
.sub-plan{border:1px solid var(--bd);border-radius:12px;padding:14px 12px;text-align:center;transition:var(--e);cursor:pointer;}
.sub-plan:hover{border-color:var(--cyan-border);transform:translateY(-2px);}
.sub-plan.featured{border-color:var(--coral-border);background:var(--coral-dim);}
.sp-icon{font-size:20px;margin-bottom:6px;}
.sp-name{font-family:var(--fm);font-weight:700;font-size:13px;margin-bottom:2px;}
.sp-price{font-family:var(--fm);font-weight:800;font-size:16px;color:var(--cyan);margin-bottom:4px;}
.sp-price small{font-size:11px;color:var(--tx-3);font-weight:400;}
.sp-perks{font-size:11px;color:var(--tx-3);line-height:1.5;}

/* ══ VERIFICATION ══ */
.verify-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:14px 22px;}
.verify-card{background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:12px;padding:14px;text-align:center;transition:var(--e);}
.verify-card:hover{border-color:var(--bd2);}
.verify-card.done{border-color:rgba(31,217,160,0.2);background:var(--green-dim);}
.verify-card.pending{border-color:rgba(247,183,49,0.2);background:rgba(247,183,49,0.06);}
.vc-icon{font-size:22px;margin-bottom:6px;}
.vc-label{font-family:var(--fm);font-size:11.5px;font-weight:700;margin-bottom:3px;}
.vc-status{font-size:10.5px;font-weight:600;}
.vc-status.done{color:var(--green);}
.vc-status.pending{color:var(--amber);}
.vc-status.missing{color:var(--tx-3);}

/* ══ RECOMMENDED JOBS ══ */
.job-mini{padding:16px 22px;border-bottom:1px solid var(--bd);transition:var(--e);}
.job-mini:last-child{border-bottom:none;}
.job-mini:hover{background:rgba(255,255,255,0.025);}
.jm-top{display:flex;align-items:flex-start;gap:10px;margin-bottom:6px;}
.jm-icon{width:36px;height:36px;border-radius:10px;background:var(--cyan-dim);border:1px solid var(--cyan-border);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;}
.jm-info{flex:1;min-width:0;}
.jm-title{font-family:var(--fm);font-weight:700;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;}
.jm-meta{display:flex;align-items:center;gap:9px;font-size:11.5px;color:var(--tx-3);flex-wrap:wrap;}
.jm-budget{color:var(--cyan);font-weight:700;}
.jm-actions{display:flex;gap:6px;margin-top:8px;}
.sp-open{background:var(--cyan-dim);color:var(--cyan);border:1px solid var(--cyan-border);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);}
.sp-urgent{background:var(--coral-dim);color:var(--coral);border:1px solid var(--coral-border);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);}

/* ══ ACTIVE PROJECTS ══ */
.proj-row{display:flex;align-items:center;gap:14px;padding:16px 22px;border-bottom:1px solid var(--bd);transition:var(--e);}
.proj-row:last-child{border-bottom:none;}
.proj-row:hover{background:rgba(255,255,255,0.025);}
.proj-av{width:40px;height:40px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--coral),var(--violet-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:14px;font-weight:800;color:#fff;}
.proj-av img{width:100%;height:100%;object-fit:cover;}
.proj-info{flex:1;min-width:0;}
.proj-title{font-family:var(--fm);font-weight:700;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;}
.proj-meta{font-size:11.5px;color:var(--tx-3);}
.proj-deadline{font-size:11px;color:var(--amber);font-weight:600;margin-top:2px;}
.proj-status{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}

/* ══ PROPOSALS TABLE ══ */
.prop-row{display:flex;align-items:center;gap:12px;padding:14px 22px;border-bottom:1px solid var(--bd);transition:var(--e);}
.prop-row:last-child{border-bottom:none;}
.prop-row:hover{background:rgba(255,255,255,0.025);}
.prop-job{flex:1;min-width:0;}
.prop-title{font-family:var(--fm);font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:2px;}
.prop-sub{font-size:11.5px;color:var(--tx-3);}
.prop-bid{font-family:var(--fm);font-weight:700;font-size:14px;color:var(--cyan);flex-shrink:0;}
.prop-stat{padding:3px 8px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);flex-shrink:0;white-space:nowrap;}
.ps-pending    {background:rgba(247,183,49,0.1);color:var(--amber);border:1px solid rgba(247,183,49,0.2);}
.ps-shortlisted{background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.ps-accepted   {background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.2);}
.ps-rejected   {background:rgba(255,77,106,0.1);color:var(--red);border:1px solid rgba(255,77,106,0.2);}
.ps-withdrawn  {background:rgba(78,90,110,0.1);color:var(--tx-3);border:1px solid rgba(78,90,110,0.15);}
.viewed-dot{width:6px;height:6px;border-radius:50%;background:var(--cyan);flex-shrink:0;}

/* ══ PERFORMANCE CARDS ══ */
.perf-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;padding:14px 22px;}
.perf-card{background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:12px;padding:16px;text-align:center;transition:var(--e);}
.perf-card:hover{border-color:var(--bd2);}
.perf-val{font-family:var(--fm);font-size:22px;font-weight:800;margin-bottom:3px;}
.perf-lbl{font-size:11px;color:var(--tx-3);}

/* ══ REVIEWS ══ */
.rev-item{padding:14px 22px;border-bottom:1px solid var(--bd);transition:var(--e);}
.rev-item:last-child{border-bottom:none;}
.rev-item:hover{background:rgba(255,255,255,0.02);}
.rev-head{display:flex;align-items:center;gap:9px;margin-bottom:8px;}
.rev-av{width:34px;height:34px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:12px;font-weight:700;color:#fff;}
.rev-av img{width:100%;height:100%;object-fit:cover;}
.rev-name{font-family:var(--fm);font-weight:700;font-size:13px;}
.rev-job{font-size:11px;color:var(--tx-3);}
.rev-stars{color:var(--amber);font-size:12px;margin-left:auto;}
.rev-date{font-size:10.5px;color:var(--tx-3);}
.rev-text{font-size:12.5px;color:var(--tx-2);line-height:1.65;font-style:italic;}

/* ══ RIGHT COLUMN EARN BOX ══ */
.earn-box{
  background:linear-gradient(135deg,rgba(0,212,200,0.08),rgba(124,111,247,0.06));
  border:1px solid var(--cyan-border);border-radius:var(--r);
  padding:22px;margin-bottom:20px;transition:background .3s;
}
.eb-label{font-size:11px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;margin-bottom:5px;}
.eb-amount{font-family:var(--fm);font-size:32px;font-weight:900;color:var(--cyan);margin-bottom:16px;line-height:1;}
.eb-row{display:flex;gap:10px;margin-bottom:14px;}
.eb-mini{flex:1;background:rgba(0,0,0,0.18);border-radius:10px;padding:11px;text-align:center;}
.eb-mini-val{font-family:var(--fm);font-weight:800;font-size:14px;color:var(--amber);}
.eb-mini-lbl{font-size:10.5px;color:var(--tx-3);margin-top:3px;}

/* ══ SKILLS PILLS ══ */
.skill-pills{display:flex;flex-wrap:wrap;gap:6px;padding:14px 22px;}
.skill-pill{display:inline-flex;align-items:center;gap:5px;background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:4px 10px;border-radius:7px;font-size:11.5px;font-weight:600;}
.skill-pill .prof{font-size:9.5px;opacity:.7;}

/* ══ ACTIVITY FEED ══ */
.activity-item{display:flex;align-items:flex-start;gap:10px;padding:11px 20px;border-bottom:1px solid var(--bd);transition:var(--e);}
.activity-item:last-child{border-bottom:none;}
.activity-item:hover{background:rgba(255,255,255,0.02);}
.ac-dot{width:28px;height:28px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:13px;margin-top:1px;}
.ac-text{font-size:12.5px;line-height:1.5;color:var(--tx-2);}
.ac-time{font-size:10px;color:var(--tx-3);margin-top:2px;}

/* ══ CHART ══ */
.chart-wrap{padding:14px 22px 22px;}

/* ══ FAB ══ */
.fab{position:fixed;bottom:28px;right:28px;z-index:990;width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));border:none;cursor:pointer;font-size:20px;color:#0C0E14;box-shadow:0 6px 24px var(--gC);transition:var(--e);display:flex;align-items:center;justify-content:center;}
.fab:hover{transform:scale(1.1);box-shadow:0 10px 34px var(--gC);}
.fab-tip{position:fixed;bottom:34px;right:86px;z-index:990;background:var(--s2);border:1px solid var(--bd);border-radius:10px;padding:7px 12px;font-size:12px;font-weight:600;font-family:var(--fm);color:var(--tx);white-space:nowrap;opacity:0;pointer-events:none;transition:opacity .25s;}
.fab:hover ~ .fab-tip{opacity:1;}

/* ══ TOAST ══ */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);max-width:340px;min-width:250px;box-shadow:0 12px 36px rgba(0,0,0,.5);animation:toastIn .35s ease;backdrop-filter:blur(14px);transition:background .3s;}
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
.mobile-nav{display:none;position:fixed;bottom:0;left:0;right:0;z-index:500;background:rgba(12,14,20,0.97);backdrop-filter:blur(20px);border-top:1px solid var(--bd);padding:8px 0 env(safe-area-inset-bottom);grid-template-columns:repeat(5,1fr);transition:background .3s,border-color .3s;}
.mn-item{display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 4px;cursor:pointer;transition:var(--e);text-decoration:none;color:var(--tx-3);position:relative;}
.mn-item.active{color:var(--cyan);}
.mn-item:hover{color:var(--tx);}
.mn-ico{font-size:20px;}
.mn-lbl{font-size:9px;font-weight:600;font-family:var(--fm);text-transform:uppercase;}
.mn-badge{position:absolute;top:2px;right:14px;background:var(--coral);color:#fff;font-size:8px;font-weight:800;padding:1px 5px;border-radius:50px;font-family:var(--fm);}

/* ══ RESPONSIVE ══ */
@media(max-width:1200px){.stats-grid{grid-template-columns:repeat(2,1fr);}.qa-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:1024px){.dash-grid{grid-template-columns:1fr;}}
@media(max-width:768px){
  .sidebar{display:none;}.main{margin-left:0;}
  .mobile-nav{display:grid;}
  .content{padding:18px 14px 90px;}
  .topbar{padding:0 16px;}
  .welcome-banner{flex-direction:column;padding:22px 18px;}
  .wb-right{flex-direction:row;flex-wrap:wrap;}
  .fab{bottom:80px;}.fab-tip{bottom:86px;}
  #toast-c{bottom:90px;}
}
@media(max-width:480px){
  .stats-grid{grid-template-columns:1fr 1fr;}
  .qa-grid{grid-template-columns:1fr 1fr;}
  .verify-grid{grid-template-columns:1fr 1fr;}
  .perf-grid{grid-template-columns:1fr 1fr;}
}
</style>
</head>
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<!-- ══════════════════ SIDEBAR ══════════════════ -->
<aside class="sidebar">
  <a href="<?= APP_URL ?>/index.php" class="sb-logo">
    <div class="sb-logo-mark">G</div>
    <span class="sb-logo-text">Gig<span>Ghana</span></span>
  </a>
  <nav class="sb-nav">
    <div class="sb-section">Provider</div>
    <a href="<?= APP_URL ?>/provider/dashboard.php"   class="sb-item active">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="sb-item">🔍 Browse Jobs</a>
    <a href="<?= APP_URL ?>/provider/profile.php"     class="sb-item">👤 My Profile</a>
    <a href="<?= APP_URL ?>/provider/earnings.php"    class="sb-item">💰 Earnings</a>
    <a href="<?= APP_URL ?>/provider/upgrade.php"     class="sb-item">⭐ Upgrade Plan</a>
    <div class="sb-section">Activity</div>
    <a href="messages.php"      class="sb-item">
      💬 Messages
      <?php if($unreadMsgs > 0): ?><span class="sb-badge"><?= $unreadMsgs ?></span><?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/provider/proposals.php"   class="sb-item">
      📩 My Proposals
      <?php if($pendingProposals > 0): ?><span class="sb-badge cyan"><?= $pendingProposals ?></span><?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/provider/reviews.php"     class="sb-item">⭐ Reviews</a>
    <div class="sb-section">Account</div>
    <a href="<?= APP_URL ?>/provider/settings.php"    class="sb-item">⚙️ Settings</a>
    <a href="<?= APP_URL ?>/index.php"                class="sb-item">🏠 Homepage</a>
    <a href="<?= APP_URL ?>/auth/logout.php"          class="sb-item danger">🚪 Sign Out</a>
  </nav>
  <div class="sb-user">
    <div class="sb-user-card">
      <div class="sb-av">
        <?php if(!empty($user['avatar'])): ?><img src="<?= sanitize($user['avatar']) ?>" alt=""><?php else: echo strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)); endif; ?>
      </div>
      <div>
        <div class="sb-uname"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div>
        <div class="sb-urole"><?= ucfirst($subTier) ?> Freelancer</div>
      </div>
    </div>
  </div>
</aside>

<!-- ══════════════════ MAIN ══════════════════ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-title">
      <h1>Provider Dashboard</h1>
      <p><?= date('l, F j, Y') ?></p>
    </div>
    <div class="topbar-right">
      <button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle theme"><?= $isLight ? '☀️' : '🌙' ?></button>
      <div class="notif-wrap">
        <div class="notif-btn" id="notifBtn" onclick="toggleNotifs()">
          🔔<?php if($unreadNotifs > 0): ?><span class="notif-count"><?= min($unreadNotifs,99) ?></span><?php endif; ?>
        </div>
        <div class="notif-drop" id="notifDrop">
          <div class="nd-head">
            🔔 Notifications
            <?php if($unreadNotifs > 0): ?><span class="nd-mark" onclick="markAllRead()">Mark all read</span><?php endif; ?>
          </div>
          <?php if(empty($notifs)): ?>
          <div class="nd-empty">All caught up! 🎉</div>
          <?php else: foreach($notifs as $n):
            $nIco = match($n['type']??''){
              'new_message'=>'💬','payment'=>'💰','proposal_update'=>'📩',
              'job_hired'=>'🎉','review'=>'⭐',default=>'🔔'
            };
          ?>
          <div class="nd-item">
            <div class="nd-ico" style="background:var(--cyan-dim);"><?= $nIco ?></div>
            <div>
              <div class="nd-ttl"><?= sanitize($n['title']) ?></div>
              <div class="nd-msg"><?= sanitize(mb_substr($n['message'],0,70)) ?></div>
              <div class="nd-time"><?= timeAgo($n['created_at']) ?></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
      <a href="<?= APP_URL ?>/index.php"            class="btn btn-ghost">🏠 Home</a>
      <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="btn btn-cyan">🔍 Find Jobs</a>
      <a href="<?= APP_URL ?>/auth/logout.php"       class="btn btn-ghost" style="color:var(--red);border-color:rgba(255,77,106,0.2);">🚪</a>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- ══ WELCOME BANNER ══ -->
    <div class="welcome-banner">
      <div class="wb-glow"></div><div class="wb-glow2"></div>
      <div class="wb-av">
        <?php if(!empty($user['avatar'])): ?><img src="<?= sanitize($user['avatar']) ?>" alt=""><?php else: echo strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)); endif; ?>
      </div>
      <div class="wb-left" style="margin-left:16px;">
        <div class="wb-greeting">👋 <?= $greeting ?></div>
        <div class="wb-name"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div>
        <div class="wb-tag"><?= sanitize($provider['tagline'] ?? 'Ready to land your next gig today?') ?></div>
        <?php if($ratingAvg > 0): ?>
        <div class="wb-stars"><?php for($i=1;$i<=5;$i++) echo $ratingAvg>=$i?'★':($ratingAvg>=$i-.5?'✦':'☆'); ?> <span style="font-size:13px;color:var(--tx-3);vertical-align:middle;"><?= number_format($ratingAvg,1) ?> · <?= $provider['rating_count']??0 ?> reviews</span></div>
        <?php endif; ?>
        <div class="wb-progress">
          <div class="wb-track"><div class="wb-fill" id="wbFill" style="width:0%"></div></div>
          <span class="wb-lbl"><?= $completeness ?>% profile strength</span>
          <?php if($completeness < 100): ?><a href="<?= APP_URL ?>/provider/profile.php" style="font-size:11px;color:var(--cyan);margin-left:4px;">Improve →</a><?php endif; ?>
        </div>
        <?php if($completeness < 80 && !empty($missing)): ?>
        <div style="margin-top:8px;font-size:11.5px;color:var(--amber);">💡 Add <?= implode(', ', array_slice($missing,0,3)) ?> to boost visibility</div>
        <?php endif; ?>
      </div>
      <div class="wb-right">
        <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="btn btn-cyan btn-lg">🔍 Browse Jobs</a>
        <a href="<?= APP_URL ?>/provider/profile.php"     class="btn btn-ghost btn-lg">👤 Complete Profile</a>
        <?php if($subTier === 'free'): ?>
        <a href="<?= APP_URL ?>/provider/upgrade.php"     class="btn btn-coral btn-lg">⭐ Upgrade Plan</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- ══ STATS ══ -->
    <div class="stats-grid">
      <div class="stat-card sc-cyan">
        <div class="stat-top">
          <div class="stat-icon si-cyan">₵</div>
          <span class="stat-delta delta-up">Earned</span>
        </div>
        <div class="stat-val"><?= formatCurrency($wallet['total_earned'] ?? 0) ?></div>
        <div class="stat-lbl">Total Earnings</div>
      </div>
      <div class="stat-card sc-violet">
        <div class="stat-top">
          <div class="stat-icon si-violet">⚡</div>
          <span class="stat-delta delta-info">Live</span>
        </div>
        <div class="stat-val" data-count="<?= $activeJobs ?>"><?= $activeJobs ?></div>
        <div class="stat-lbl">Active Jobs</div>
      </div>
      <div class="stat-card sc-coral">
        <div class="stat-top">
          <div class="stat-icon si-coral">📩</div>
          <?php if($pendingProposals > 0): ?><span class="stat-delta delta-warn">Pending</span><?php endif; ?>
        </div>
        <div class="stat-val" data-count="<?= $totalProposalsSent ?>"><?= $totalProposalsSent ?></div>
        <div class="stat-lbl">Proposals Sent</div>
      </div>
      <div class="stat-card sc-green">
        <div class="stat-top">
          <div class="stat-icon si-green">👁</div>
        </div>
        <div class="stat-val" data-count="<?= $profileViews ?>"><?= $profileViews ?></div>
        <div class="stat-lbl">Profile Views</div>
      </div>
    </div>

    <!-- ══ QUICK ACTIONS ══ -->
    <div class="qa-grid">
      <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="qa-card">
        <div class="qa-icon" style="background:var(--cyan-dim);border:1px solid var(--cyan-border);">🔍</div>
        <div class="qa-label">Browse Jobs</div>
        <div class="qa-sub">Find new gigs</div>
      </a>
      <a href="<?= APP_URL ?>/provider/profile.php" class="qa-card">
        <div class="qa-icon" style="background:var(--violet-dim);border:1px solid var(--violet-border);">👤</div>
        <div class="qa-label">Update Profile</div>
        <div class="qa-sub"><?= $completeness ?>% complete</div>
      </a>
      <a href="messages.php" class="qa-card">
        <div class="qa-icon" style="background:var(--coral-dim);border:1px solid var(--coral-border);position:relative;">
          💬<?php if($unreadMsgs > 0): ?><span style="position:absolute;top:-5px;right:-5px;background:var(--coral);color:#fff;font-size:8px;font-weight:800;padding:1px 5px;border-radius:50px;border:2px solid var(--bg);font-family:var(--fm);"><?= $unreadMsgs ?></span><?php endif; ?>
        </div>
        <div class="qa-label">View Messages</div>
        <div class="qa-sub"><?= $unreadMsgs > 0 ? $unreadMsgs.' unread' : 'Inbox' ?></div>
      </a>
      <a href="earnings.php" class="qa-card">
        <div class="qa-icon" style="background:rgba(247,183,49,0.1);border:1px solid rgba(247,183,49,0.2);">💸</div>
        <div class="qa-label">Withdraw Earnings</div>
        <div class="qa-sub"><?= formatCurrency($wallet['available_balance']??0) ?> available</div>
      </a>
    </div>

    <!-- ══ MAIN GRID ══ -->
    <div class="dash-grid">

      <!-- LEFT COLUMN -->
      <div>

        <!-- SUBSCRIPTION STATUS -->
        <div class="sub-box">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div>
              <div style="font-family:var(--fm);font-size:15px;font-weight:700;margin-bottom:4px;">🗂 Your Plan</div>
              <div class="sub-tier-row" style="margin:0;">
                <span class="sub-tier-badge" style="background:<?= $sub['bg'] ?>;border-color:<?= $sub['border'] ?>;color:<?= $sub['color'] ?>;">
                  <?= $sub['icon'] ?> <?= $sub['label'] ?>
                </span>
                <?php if($subTier !== 'free' && $subExpires): ?><span style="font-size:11px;color:var(--tx-3);">Expires <?= date('M j, Y', strtotime($subExpires)) ?></span><?php endif; ?>
              </div>
            </div>
            <?php if($subTier === 'free'): ?>
            <a href="<?= APP_URL ?>/provider/upgrade.php" class="btn btn-coral btn-sm">⭐ Upgrade</a>
            <?php endif; ?>
          </div>

          <?php
          $barPct  = $propLimit > 0 ? min(100, round($propUsed / $propLimit * 100)) : 100;
          $barClass= $barPct >= 100 ? 'full' : ($barPct >= 70 ? 'warn' : 'safe');
          $remaining = max(0, $propLimit - $propUsed);
          ?>
          <div class="sub-usage-lbl">
            <span>Proposals Used</span>
            <strong><?= $propUsed ?> / <?= $propLimit === 999 ? '∞' : $propLimit ?></strong>
          </div>
          <div class="sub-bar-track">
            <div class="sub-bar-fill <?= $barClass ?>" id="subBarFill" style="width:0%"></div>
          </div>

          <?php if($subTier === 'free' && $remaining <= 0): ?>
          <div class="sub-alert">⚠️ You've reached your <strong>3 free proposal limit</strong>. Upgrade to keep applying!</div>
          <?php elseif($subTier === 'free' && $remaining <= 1): ?>
          <div class="sub-alert" style="background:rgba(247,183,49,0.08);border-color:rgba(247,183,49,0.2);color:var(--amber);">⚡ Only <strong><?= $remaining ?> free proposal<?= $remaining!=1?'s':'' ?></strong> remaining. Consider upgrading.</div>
          <?php endif; ?>

          <div class="sub-plans">
            <div class="sub-plan <?= $subTier==='verified'?'featured':'' ?>">
              <div class="sp-icon">✓</div>
              <div class="sp-name">Verified</div>
              <div class="sp-price">₵49<small>/mo</small></div>
              <div class="sp-perks">Unlimited proposals · Verified badge · Priority ranking</div>
              <a href="<?= APP_URL ?>/provider/upgrade.php?plan=verified" class="btn btn-cyan btn-sm" style="margin-top:10px;width:100%;justify-content:center;"><?= $subTier==='verified'?'✓ Current':'Upgrade'?></a>
            </div>
            <div class="sub-plan <?= $subTier==='premium'?'featured':'' ?>">
              <div class="sp-icon">⭐</div>
              <div class="sp-name">Premium</div>
              <div class="sp-price">₵99<small>/mo</small></div>
              <div class="sp-perks">Top placement · Featured badge · Exclusive jobs</div>
              <a href="<?= APP_URL ?>/provider/upgrade.php?plan=premium" class="btn btn-coral btn-sm" style="margin-top:10px;width:100%;justify-content:center;"><?= $subTier==='premium'?'✓ Current':'Go Premium'?></a>
            </div>
          </div>
        </div>

        <!-- RECOMMENDED JOBS -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">💡 Recommended Jobs For You</div>
              <div class="card-cnt"><?= count($recommendedJobs) ?> fresh opportunities</div>
            </div>
            <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="btn btn-ghost btn-sm">Browse All →</a>
          </div>
          <?php if(empty($recommendedJobs)): ?>
          <div class="empty-state">
            <div class="es-ico">💼</div>
            <div class="es-ttl">No matching jobs right now</div>
            <div class="es-sub">Add more skills to your profile to get better recommendations.</div>
            <a href="<?= APP_URL ?>/provider/profile.php" class="btn btn-cyan" style="margin-top:14px;">Add Skills →</a>
          </div>
          <?php else: foreach($recommendedJobs as $j):
            $jIco = $iconMap[$j['cat_icon']??''] ?? '📋';
          ?>
          <div class="job-mini">
            <div class="jm-top">
              <div class="jm-icon"><?= $jIco ?></div>
              <div class="jm-info">
                <div class="jm-title">
                  <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" style="color:var(--tx);"><?= sanitize($j['title']) ?></a>
                </div>
                <div class="jm-meta">
                  <span><?= sanitize($j['cat_name']??'General') ?></span>
                  <span class="jm-budget"><?= formatCurrency($j['budget_min']) ?><?= $j['budget_max']>$j['budget_min']?' – '.formatCurrency($j['budget_max']):'' ?><?= $j['budget_type']==='hourly'?'/hr':'' ?></span>
                  <span>📝 <?= $j['proposal_count'] ?> proposals</span>
                  <span><?= timeAgo($j['created_at']) ?></span>
                </div>
              </div>
              <div style="flex-shrink:0;display:flex;flex-direction:column;gap:4px;align-items:flex-end;">
                <?php if($j['is_urgent']): ?><span class="sp-urgent">🔥 Urgent</span><?php else: ?><span class="sp-open">● Open</span><?php endif; ?>
              </div>
            </div>
            <div class="jm-actions">
              <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" class="btn btn-ghost btn-sm">View Job</a>
              <?php if($subTier==='free' && $remaining <= 0): ?>
              <a href="<?= APP_URL ?>/provider/upgrade.php" class="btn btn-coral btn-sm">🔒 Upgrade to Apply</a>
              <?php else: ?>
              <a href="<?= APP_URL ?>/provider/submit-proposal.php?job_id=<?= $j['id'] ?>" class="btn btn-cyan btn-sm">Apply Now →</a>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- ACTIVE PROJECTS -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">⚡ Active Projects</div>
              <div class="card-cnt"><?= count($activeProjects) ?> in progress</div>
            </div>
          </div>
          <?php if(empty($activeProjects)): ?>
          <div class="empty-state">
            <div class="es-ico">🚀</div>
            <div class="es-ttl">No active projects yet</div>
            <div class="es-sub">Win proposals and start delivering great work!</div>
          </div>
          <?php else: foreach($activeProjects as $ap):
            $cinit = strtoupper(substr($ap['client_fname'],0,1).substr($ap['client_lname'],0,1));
            $daysLeft = $ap['deadline'] ? (int)ceil((strtotime($ap['deadline'])-time())/86400) : null;
          ?>
          <div class="proj-row">
            <div class="proj-av">
              <?php if(!empty($ap['client_avatar'])): ?><img src="<?= sanitize($ap['client_avatar']) ?>" alt="" loading="lazy"><?php else: echo $cinit; endif; ?>
            </div>
            <div class="proj-info">
              <div class="proj-title"><?= sanitize($ap['title']) ?></div>
              <div class="proj-meta">Client: <?= sanitize($ap['client_fname'].' '.$ap['client_lname']) ?> · <?= sanitize($ap['cat_name']??'General') ?></div>
              <?php if($daysLeft !== null): ?>
              <div class="proj-deadline"><?= $daysLeft > 0 ? "⏰ {$daysLeft} day".($daysLeft!=1?'s':'')." remaining" : "⚠️ Deadline passed!" ?></div>
              <?php endif; ?>
            </div>
            <span class="proj-status">🔄 In Progress</span>
            <?php if($ap['conv_id']): ?>
            <a href="<?= APP_URL ?>/client/messages.php?conv=<?= $ap['conv_id'] ?>" class="btn btn-violet btn-sm">💬 Message</a>
            <?php endif; ?>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- RECENT PROPOSALS -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">📩 Recent Proposals</div>
              <div class="card-cnt"><?= $totalProposalsSent ?> total sent</div>
            </div>
            <a href="<?= APP_URL ?>/provider/proposals.php" class="btn btn-ghost btn-sm">View All</a>
          </div>
          <?php if(empty($proposals)): ?>
          <div class="empty-state">
            <div class="es-ico">📩</div>
            <div class="es-ttl">No proposals submitted yet</div>
            <div class="es-sub">Start exploring jobs and grow your freelance career.</div>
            <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="btn btn-cyan" style="margin-top:14px;">Browse Jobs →</a>
          </div>
          <?php else: foreach($proposals as $pp):
            $pstClass = match($pp['status']){'accepted'=>'ps-accepted','rejected'=>'ps-rejected','shortlisted'=>'ps-shortlisted','withdrawn'=>'ps-withdrawn',default=>'ps-pending'};
          ?>
          <div class="prop-row">
            <?php if($pp['client_viewed']): ?><div class="viewed-dot" title="Client viewed your proposal"></div><?php endif; ?>
            <div class="prop-job">
              <div class="prop-title">
                <a href="<?= APP_URL ?>/job-details.php?id=<?= $pp['job_id'] ?>" style="color:var(--tx);"><?= sanitize($pp['job_title']) ?></a>
              </div>
              <div class="prop-sub">
                <?= sanitize($pp['cat_name']??'') ?> · <?= sanitize($pp['client_fname'].' '.$pp['client_lname']) ?> · <?= timeAgo($pp['created_at']) ?>
                <?php if($pp['client_viewed']): ?> · <span style="color:var(--cyan);font-size:10.5px;">👁 Viewed</span><?php endif; ?>
              </div>
            </div>
            <div class="prop-bid"><?= formatCurrency($pp['bid_amount']) ?></div>
            <span class="prop-stat <?= $pstClass ?>"><?= ucfirst($pp['status']) ?></span>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- PERFORMANCE ANALYTICS -->
        <div class="section-card">
          <div class="sc-head">
            <div class="card-ttl">📊 Performance Analytics</div>
          </div>
          <div class="perf-grid">
            <div class="perf-card">
              <div class="perf-val" style="color:var(--green);"><?= $successRate > 0 ? number_format($successRate,1).'%' : '—' ?></div>
              <div class="perf-lbl">Job Success Rate</div>
            </div>
            <div class="perf-card">
              <div class="perf-val" style="color:var(--amber);"><?= $ratingAvg > 0 ? number_format($ratingAvg,1) : '—' ?></div>
              <div class="perf-lbl">Average Rating</div>
            </div>
            <div class="perf-card">
              <div class="perf-val" style="color:var(--cyan);"><?= $responseTime ?></div>
              <div class="perf-lbl">Avg Response Time</div>
            </div>
            <div class="perf-card">
              <div class="perf-val" data-count="<?= $completedJobs ?>" style="color:var(--violet);"><?= $completedJobs ?></div>
              <div class="perf-lbl">Jobs Completed</div>
            </div>
          </div>

          <!-- Earnings chart -->
          <div style="padding:4px 22px 8px;font-family:var(--fm);font-size:13px;font-weight:700;color:var(--tx-2);">📈 Earnings This Year</div>
          <div class="chart-wrap">
            <canvas id="earningsChart" height="110"></canvas>
          </div>
        </div>

        <!-- RECENT REVIEWS -->
        <?php if(!empty($reviews)): ?>
        <div class="section-card">
          <div class="sc-head">
            <div class="card-ttl">⭐ Client Reviews</div>
            <a href="<?= APP_URL ?>/provider/reviews.php" class="btn btn-ghost btn-sm">All Reviews</a>
          </div>
          <?php foreach($reviews as $rv):
            $rinit = strtoupper(substr($rv['first_name'],0,1).substr($rv['last_name'],0,1));
            $rval  = (float)$rv['rating_overall'];
          ?>
          <div class="rev-item">
            <div class="rev-head">
              <div class="rev-av">
                <?php if(!empty($rv['avatar'])): ?><img src="<?= sanitize($rv['avatar']) ?>" alt="" loading="lazy"><?php else: echo $rinit; endif; ?>
              </div>
              <div>
                <div class="rev-name"><?= sanitize($rv['first_name'].' '.$rv['last_name']) ?></div>
                <div class="rev-job">📋 <?= sanitize($rv['job_title']) ?></div>
              </div>
              <div class="rev-stars"><?php for($s=1;$s<=5;$s++) echo $rval>=$s?'★':'☆'; ?></div>
              <div class="rev-date"><?= timeAgo($rv['created_at']) ?></div>
            </div>
            <?php if($rv['comment']): ?>
            <div class="rev-text">"<?= sanitize(mb_substr($rv['comment'],0,180)) ?><?= mb_strlen($rv['comment'])>180?'…':'' ?>"</div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

      </div><!-- /left -->

      <!-- RIGHT COLUMN -->
      <div>

        <!-- EARNINGS SUMMARY -->
        <div class="earn-box">
          <div class="eb-label">Available Balance</div>
          <div class="eb-amount"><?= formatCurrency($wallet['available_balance']??0) ?></div>
          <div class="eb-row">
            <div class="eb-mini">
              <div class="eb-mini-val"><?= formatCurrency($wallet['pending_balance']??0) ?></div>
              <div class="eb-mini-lbl">In Escrow</div>
            </div>
            <div class="eb-mini">
              <div class="eb-mini-val"><?= formatCurrency($earningsMonth) ?></div>
              <div class="eb-mini-lbl">This Month</div>
            </div>
          </div>
          <a href="<?= APP_URL ?>/provider/earnings.php" class="btn btn-cyan" style="width:100%;justify-content:center;">💸 Withdraw Funds</a>
        </div>

        <!-- VERIFICATION STATUS -->
        <div class="section-card" style="margin-bottom:20px;">
          <div class="sc-head">
            <div class="card-ttl">🪪 Verification Status</div>
            <a href="<?= APP_URL ?>/provider/verify.php" class="btn btn-ghost btn-sm">Manage</a>
          </div>
          <?php
          $verChecks = [
            ['email_verified',   '📧', 'Email',       (bool)($user['email_verified']??0)],
            ['phone_verified',   '📱', 'Phone',       (bool)($user['phone_verified']??0)],
            ['id_verified',      '🪪', 'Ghana Card',  (bool)($user['ghana_card_verified']??0) || ($verMap['id_verified']??'')  === 'approved'],
            ['payment_verified', '💳', 'Payment',     (bool)($user['payment_verified']??0)],
          ];
          ?>
          <div class="verify-grid">
            <?php foreach($verChecks as [$type,$ico,$label,$done]):
              $vStatus = $verMap[$type] ?? null;
              $isPending = $vStatus === 'pending';
            ?>
            <div class="verify-card <?= $done?'done':($isPending?'pending':'') ?>">
              <div class="vc-icon"><?= $ico ?></div>
              <div class="vc-label"><?= $label ?></div>
              <div class="vc-status <?= $done?'done':($isPending?'pending':'missing') ?>">
                <?= $done ? '✓ Verified' : ($isPending ? '⏳ Pending' : '○ Not set') ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php if(!($user['ghana_card_verified']??0)): ?>
          <div style="padding:14px 22px;border-top:1px solid var(--bd);">
            <a href="<?= APP_URL ?>/provider/verify.php" class="btn btn-amber btn-sm" style="width:100%;justify-content:center;">🪪 Verify Ghana Card</a>
          </div>
          <?php endif; ?>
        </div>

        <!-- MY SKILLS -->
        <div class="section-card" style="margin-bottom:20px;">
          <div class="sc-head">
            <div class="card-ttl">🛠 My Skills</div>
            <a href="<?= APP_URL ?>/provider/profile.php?tab=skills" class="btn btn-ghost btn-sm">Edit</a>
          </div>
          <?php if(empty($mySkills)): ?>
          <div class="empty-state" style="padding:24px;">
            <div class="es-ico">🛠</div>
            <div class="es-sub">Add skills to get job recommendations.</div>
            <a href="<?= APP_URL ?>/provider/profile.php" class="btn btn-cyan btn-sm" style="margin-top:10px;">Add Skills</a>
          </div>
          <?php else: ?>
          <div class="skill-pills">
            <?php foreach($mySkills as $sk): ?>
            <span class="skill-pill"><?= sanitize($sk['name']) ?> <span class="prof">(<?= ucfirst($sk['proficiency']) ?>)</span></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- ACTIVITY FEED -->
        <div class="section-card" style="margin-bottom:20px;">
          <div class="sc-head"><div class="card-ttl">📡 Activity Feed</div></div>
          <?php
          $activities = [];
          foreach(array_slice($proposals,0,3) as $pp)
            $activities[] = ['ico'=>'📩','bg'=>'var(--violet-dim)',
              'text'=>'Proposal for <strong>'.sanitize($pp['job_title']).'</strong> is <strong>'.ucfirst($pp['status']).'</strong>',
              'time'=>$pp['updated_at']??$pp['created_at']];
          foreach(array_slice($activeProjects,0,2) as $ap)
            $activities[] = ['ico'=>'⚡','bg'=>'var(--cyan-dim)',
              'text'=>'Active project: <strong>'.sanitize($ap['title']).'</strong>',
              'time'=>$ap['updated_at']??$ap['created_at']];
          foreach(array_slice($reviews,0,2) as $rv)
            $activities[] = ['ico'=>'⭐','bg'=>'rgba(247,183,49,0.1)',
              'text'=>sanitize($rv['first_name']).' left you a <strong>'.number_format($rv['rating_overall'],1).'★ review</strong>',
              'time'=>$rv['created_at']];
          usort($activities, fn($a,$b)=>strtotime($b['time'])-strtotime($a['time']));
          ?>
          <?php if(empty($activities)): ?>
          <div class="empty-state" style="padding:24px;"><div class="es-ico">📡</div><div class="es-sub">Activity will appear here as you work.</div></div>
          <?php else: foreach(array_slice($activities,0,6) as $ac): ?>
          <div class="activity-item">
            <div class="ac-dot" style="background:<?= $ac['bg'] ?>;"><?= $ac['ico'] ?></div>
            <div>
              <div class="ac-text"><?= $ac['text'] ?></div>
              <div class="ac-time"><?= timeAgo($ac['time']) ?></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- QUICK ACTIONS PANEL -->
        <div class="section-card">
          <div class="sc-head"><div class="card-ttl">⚡ Quick Actions</div></div>
          <div style="padding:14px;display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <a href="<?= APP_URL ?>/provider/browse-jobs.php"   class="btn btn-cyan"     style="justify-content:center;font-size:12px;">🔍 Browse Jobs</a>
            <a href="<?= APP_URL ?>/provider/profile.php"       class="btn btn-violet"   style="justify-content:center;font-size:12px;">👤 Profile</a>
            <a href="<?= APP_URL ?>/client/messages.php"        class="btn btn-ghost"    style="justify-content:center;font-size:12px;">💬 Messages</a>
            <a href="<?= APP_URL ?>/provider/earnings.php"      class="btn btn-ghost"    style="justify-content:center;font-size:12px;">💰 Earnings</a>
            <a href="<?= APP_URL ?>/provider/upgrade.php"       class="btn btn-coral"    style="justify-content:center;font-size:12px;">⭐ Upgrade</a>
            <a href="<?= APP_URL ?>/provider/proposals.php"     class="btn btn-ghost"    style="justify-content:center;font-size:12px;">📩 Proposals</a>
            <a href="<?= APP_URL ?>/index.php"                  class="btn btn-ghost"    style="justify-content:center;font-size:12px;">🏠 Homepage</a>
            <a href="<?= APP_URL ?>/auth/logout.php"            class="btn btn-red-soft" style="justify-content:center;font-size:12px;">🚪 Logout</a>
          </div>
        </div>

      </div><!-- /right -->
    </div><!-- /dash-grid -->
  </div><!-- /content -->
</div><!-- /main -->

<!-- MOBILE BOTTOM NAV -->
<nav class="mobile-nav">
  <a href="<?= APP_URL ?>/provider/dashboard.php"   class="mn-item active"><div class="mn-ico">📊</div><div class="mn-lbl">Home</div></a>
  <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="mn-item"><div class="mn-ico">🔍</div><div class="mn-lbl">Jobs</div></a>
  <a href="<?= APP_URL ?>/provider/profile.php"     class="mn-item"><div class="mn-ico">👤</div><div class="mn-lbl">Profile</div></a>
  <a href="<?= APP_URL ?>/client/messages.php"      class="mn-item">
    <div class="mn-ico">💬</div><div class="mn-lbl">Chat</div>
    <?php if($unreadMsgs>0): ?><span class="mn-badge"><?= $unreadMsgs ?></span><?php endif; ?>
  </a>
  <a href="<?= APP_URL ?>/provider/earnings.php"    class="mn-item"><div class="mn-ico">💰</div><div class="mn-lbl">Earn</div></a>
</nav>

<!-- FAB -->
<button class="fab" onclick="window.location='<?= APP_URL ?>/provider/browse-jobs.php'" title="Browse Jobs">🔍</button>
<div class="fab-tip">Browse Jobs</div>

<div id="toast-c"></div>

<script>
/* ══ THEME ══ */
function toggleTheme() {
  const isLight = document.getElementById('appBody').classList.toggle('lm');
  const val = isLight ? 'light' : 'dark';
  localStorage.setItem('gg_theme', val);
  document.cookie = `gg_theme=${val};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = isLight ? '☀️' : '🌙';
  showToast('Theme', isLight ? '☀️ Light mode on' : '🌙 Dark mode on', 'info', 2000);
}
(function(){
  const s = localStorage.getItem('gg_theme') || '<?= $isLight ? "light" : "dark" ?>';
  const body = document.getElementById('appBody');
  const btn  = document.getElementById('themeBtn');
  if (s === 'light') { body.classList.add('lm'); if(btn) btn.textContent = '☀️'; }
  else               { body.classList.remove('lm'); if(btn) btn.textContent = '🌙'; }
})();

/* ══ STAT COUNTERS ══ */
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    const el = e.target, tgt = parseInt(el.dataset.count) || 0;
    if (!tgt) return;
    let c = 0; const step = tgt / 55;
    const id = setInterval(() => {
      c = Math.min(c + step, tgt);
      el.textContent = Math.floor(c);
      if (c >= tgt) clearInterval(id);
    }, 16);
    obs.unobserve(el);
  });
}, { threshold: 0.5 });
document.querySelectorAll('[data-count]').forEach(el => obs.observe(el));

/* ══ PROGRESS BARS ══ */
setTimeout(() => {
  const wb = document.getElementById('wbFill'); if(wb) wb.style.width = '<?= $completeness ?>%';
  const sb = document.getElementById('subBarFill');
  if(sb) {
    const pct = <?= $propLimit > 0 && $propLimit < 999 ? min(100, round($propUsed/$propLimit*100)) : ($propUsed>0?50:0) ?>;
    sb.style.width = pct + '%';
  }
}, 400);

/* ══ NOTIFICATIONS ══ */
function toggleNotifs() {
  document.getElementById('notifDrop').classList.toggle('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.notif-wrap')) document.getElementById('notifDrop')?.classList.remove('open');
});
function markAllRead() {
  fetch('<?= APP_URL ?>/api/notifications.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=mark_all_read&csrf=<?= $csrf ?>'
  }).then(() => {
    const cnt = document.querySelector('.notif-count'); if(cnt) cnt.remove();
    showToast('Done','All notifications marked as read.','success');
  });
}

/* ══ EARNINGS CHART ══ */
(function(){
  const ctx = document.getElementById('earningsChart'); if(!ctx) return;
  const data = <?= $earningsJson ?>;
  const labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  new Chart(ctx, {
    type:'bar',
    data:{
      labels,
      datasets:[{
        label:'GHS',data,
        backgroundColor:'rgba(0,212,200,0.18)',
        borderColor:'rgba(0,212,200,0.7)',
        borderWidth:2,
        borderRadius:6,
        hoverBackgroundColor:'rgba(0,212,200,0.35)',
      }]
    },
    options:{
      responsive:true,
      plugins:{
        legend:{display:false},
        tooltip:{
          backgroundColor:'#13161E',titleColor:'#F2F4F8',bodyColor:'#4E5A6E',
          borderColor:'rgba(0,212,200,0.15)',borderWidth:1,
          titleFont:{family:'Plus Jakarta Sans',weight:'700'},
          callbacks:{label:c=>' ₵'+c.parsed.y.toLocaleString('en-GH',{minimumFractionDigits:2})}
        }
      },
      scales:{
        x:{grid:{color:'rgba(78,90,110,0.06)'},ticks:{color:'#4E5A6E',font:{size:10,family:'DM Sans'}}},
        y:{grid:{color:'rgba(78,90,110,0.06)'},beginAtZero:true,ticks:{color:'#4E5A6E',font:{size:10,family:'DM Sans'},callback:v=>'₵'+v.toLocaleString()}}
      }
    }
  });
})();

/* ══ TOAST ══ */
const ICONS = {success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
function showToast(title,msg,type='info',d=4500){
  const c = document.getElementById('toast-c');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<div class="t-ico">${ICONS[type]}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(50px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),360);},d);
}

/* URL param toasts */
<?php if(isset($_GET['success'])): ?>showToast('Success','<?= addslashes(sanitize($_GET['success'])) ?>','success');<?php endif; ?>
<?php if(isset($_GET['error'])  ): ?>showToast('Error',  '<?= addslashes(sanitize($_GET['error']))   ?>','error');<?php endif; ?>
<?php if(isset($_GET['info'])   ): ?>showToast('Info',   '<?= addslashes(sanitize($_GET['info']))    ?>','info');<?php endif; ?>

/* Welcome toast */
setTimeout(() => showToast(
  '<?= $greeting ?>, <?= sanitize($user["first_name"]) ?>! 🇬🇭',
  '<?= $activeJobs ?> active job<?= $activeJobs!=1?"s":"" ?> · <?= $pendingProposals ?> pending proposal<?= $pendingProposals!=1?"s":"" ?> · <?= $remaining > 0 && $subTier==="free" ? $remaining." free proposal".($remaining!=1?"s":"")." left" : ucfirst($subTier)." plan" ?>',
  'info', 5000
), 900);
</script>
</body>
</html>