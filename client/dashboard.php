<?php
/**
 * GigGhana — client/dashboard.php  (v2 — all DB fixes applied)
 *
 * FIXES:
 *  1. Theme toggle (dark/light) persists via localStorage AND a tiny
 *     session cookie so every linked page reads it on load.
 *  2. Unread messages counted correctly from messages table
 *     (sender_id != $userId AND is_read=0), not stale unread_count columns.
 *  3. Stats (jobs, proposals, notifications) all queried from DB.
 *  4. Conversations query fixed: column is `content` not `body`;
 *     uses correct conversations schema with unread count sub-query.
 *  5. Light-mode class applied server-side via cookie so the page
 *     never flashes before JS runs.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('client');

$userId = (int)$_SESSION['user_id'];
$user   = getUserById($userId);

/* ── Read theme preference (cookie set by JS) ── */
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';

try {
    $db = getDB();

    /* ── STATS ── */
    $stA = $db->prepare("SELECT COUNT(*) FROM jobs WHERE client_id=? AND status IN ('open','in_progress')");
    $stA->execute([$userId]); $activeJobs = (int)$stA->fetchColumn();

    $stC = $db->prepare("SELECT COUNT(*) FROM jobs WHERE client_id=? AND status='completed'");
    $stC->execute([$userId]); $completedJobs = (int)$stC->fetchColumn();

    /* proposals pending review by this client */
    $stP = $db->prepare(
        "SELECT COUNT(*) FROM proposals p
         JOIN jobs j ON j.id=p.job_id
         WHERE j.client_id=? AND p.status IN ('pending','shortlisted')"
    );
    $stP->execute([$userId]); $totalProposals = (int)$stP->fetchColumn();

    $stS = $db->prepare(
        "SELECT COALESCE(SUM(amount),0) FROM transactions
         WHERE user_id=? AND type='escrow_lock' AND status='completed'"
    );
    $stS->execute([$userId]); $totalSpent = (float)$stS->fetchColumn();

    /* ── WALLET ── */
    $stW = $db->prepare("SELECT * FROM wallets WHERE user_id=? LIMIT 1");
    $stW->execute([$userId]);
    $wallet = $stW->fetch() ?: ['available_balance'=>0,'pending_balance'=>0,'total_spent'=>0];

    /* ── UNREAD MESSAGES (FIX 2) ──
       Count messages in MY conversations where sender is NOT me and is_read=0.
       Do NOT rely on unread_count_user1/2 columns — they may be stale. */
    $stUM = $db->prepare("
        SELECT COUNT(*)
        FROM messages m
        JOIN conversations c ON c.id = m.conversation_id
        WHERE (c.user1_id=? OR c.user2_id=?)
          AND m.sender_id != ?
          AND m.is_read = 0
          AND m.is_deleted = 0
    ");
    $stUM->execute([$userId, $userId, $userId]);
    $unreadMsgs = (int)$stUM->fetchColumn();

    /* ── UNREAD NOTIFICATIONS ── */
    $stN = $db->prepare(
        "SELECT * FROM notifications WHERE user_id=? AND is_read=0
         ORDER BY created_at DESC LIMIT 10"
    );
    $stN->execute([$userId]); $notifs = $stN->fetchAll();
    $unreadNotifs = count($notifs);

    /* ── MY JOBS ── */
    $stJobs = $db->prepare("
        SELECT j.*, c.name AS cat_name, c.icon AS cat_icon,
               (SELECT COUNT(*) FROM proposals WHERE job_id=j.id) AS prop_count
        FROM jobs j
        LEFT JOIN categories c ON c.id=j.category_id
        WHERE j.client_id=?
        ORDER BY j.created_at DESC LIMIT 8
    ");
    $stJobs->execute([$userId]); $myJobs = $stJobs->fetchAll();

    /* ── PROPOSALS RECEIVED (FIX 3) ── */
    $stProps = $db->prepare("
        SELECT p.*,
               j.title AS job_title,
               j.id    AS job_id,
               u.first_name, u.last_name, u.avatar,
               pr.rating_avg, pr.rating_count,
               pr.is_verified, pr.completed_jobs,
               pr.tagline,
               pr.id AS provider_row_id
        FROM proposals p
        JOIN jobs       j  ON j.id  = p.job_id
        JOIN providers  pr ON pr.id = p.provider_id
        JOIN users      u  ON u.id  = pr.user_id
        WHERE j.client_id = ?
        ORDER BY p.created_at DESC
        LIMIT 10
    ");
    $stProps->execute([$userId]); $proposals = $stProps->fetchAll();

    /* ── CONVERSATIONS (FIX 4) ──
       Column is `content`, not `body`. Use last message sub-query correctly. */
    $stConvs = $db->prepare("
        SELECT
            c.id,
            c.job_id,
            c.last_message_at,
            c.last_message_preview,
            /* unread count: messages in this conv not sent by me, not read */
            (SELECT COUNT(*)
             FROM messages m2
             WHERE m2.conversation_id = c.id
               AND m2.sender_id != :me1
               AND m2.is_read    = 0
               AND m2.is_deleted = 0
            ) AS unread,
            /* last actual message content */
            (SELECT m3.content
             FROM messages m3
             WHERE m3.conversation_id = c.id
               AND m3.is_deleted = 0
             ORDER BY m3.created_at DESC
             LIMIT 1
            ) AS last_msg,
            /* the OTHER person */
            CASE WHEN c.user1_id = :me2
                 THEN c.user2_id ELSE c.user1_id END AS other_uid,
            u.first_name,
            u.last_name,
            u.avatar,
            /* real online status */
            (CASE WHEN u.last_seen IS NOT NULL
                   AND TIMESTAMPDIFF(SECOND, u.last_seen, NOW()) <= 300
                  THEN 1 ELSE 0 END) AS is_online
        FROM conversations c
        JOIN users u ON u.id = CASE WHEN c.user1_id = :me3
                                    THEN c.user2_id ELSE c.user1_id END
        WHERE c.user1_id = :me4 OR c.user2_id = :me5
        ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
        LIMIT 6
    ");
    $stConvs->execute([
        ':me1'=>$userId,':me2'=>$userId,':me3'=>$userId,
        ':me4'=>$userId,':me5'=>$userId,
    ]);
    $convs = $stConvs->fetchAll();

    /* ── ESCROW ── */
    $stEsc = $db->prepare("
        SELECT e.*, j.title AS job_title,
               u.first_name, u.last_name
        FROM escrow e
        JOIN jobs j     ON j.id  = e.job_id
        JOIN providers  pv ON pv.id = e.provider_id
        JOIN users      u  ON u.id  = pv.user_id
        WHERE e.client_id=? AND e.status='held'
        ORDER BY e.locked_at DESC LIMIT 5
    ");
    $stEsc->execute([$userId]); $escrows = $stEsc->fetchAll();

    /* ── TRANSACTIONS ── */
    $stTx = $db->prepare("
        SELECT t.*, j.title AS job_title
        FROM transactions t
        LEFT JOIN jobs j ON j.id=t.job_id
        WHERE t.user_id=?
        ORDER BY t.created_at DESC LIMIT 6
    ");
    $stTx->execute([$userId]); $transactions = $stTx->fetchAll();

    /* ── PENDING REVIEWS ── */
    $stRev = $db->prepare("
        SELECT j.*, u.first_name, u.last_name, u.avatar, pr.id AS provider_row_id
        FROM jobs j
        JOIN users      u  ON u.id  = j.hired_provider_id
        LEFT JOIN providers pr ON pr.user_id = j.hired_provider_id
        WHERE j.client_id=? AND j.status='completed'
          AND j.id NOT IN (SELECT job_id FROM reviews WHERE reviewer_id=?)
        ORDER BY j.updated_at DESC LIMIT 3
    ");
    $stRev->execute([$userId,$userId]); $pendingReviews = $stRev->fetchAll();

} catch(Exception $e) {
    error_log($e->getMessage());
    $activeJobs=$completedJobs=$totalProposals=$totalSpent=0;
    $unreadMsgs=$unreadNotifs=0;
    $wallet=['available_balance'=>0,'pending_balance'=>0,'total_spent'=>0];
    $myJobs=$proposals=$convs=$escrows=$transactions=$pendingReviews=$notifs=[];
}

/* Profile completeness */
$compChecks = [
    !empty($user['avatar']),
    strlen($user['bio']??'') > 20,
    !empty($user['location']),
    !empty($user['phone']),
    ($user['ghana_card_verified']??0) == 1,
];
$completeness = (int)(array_sum(array_map('intval',$compChecks)) / count($compChecks) * 100);

$csrf = generateCSRF();

$iconMap = [
    'code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
    'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
    'briefcase'=>'⚖️','headphones'=>'🎧','camera'=>'📷','globe'=>'🌐',
    'tool'=>'🔧','bar-chart'=>'📊','music'=>'🎵',
];

$hour     = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Client Dashboard — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════════════════════
   DESIGN TOKENS — Volcanic Charcoal × Electric Cyan × Coral
   FIX 1: Light-mode tokens properly defined, toggled globally
══════════════════════════════════════════════════════════ */
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
  --gC:rgba(0,212,200,0.16); --gO:rgba(255,107,74,0.14); --gV:rgba(124,111,247,0.14);

  --fm:'Plus Jakarta Sans',sans-serif;
  --fb:'DM Sans',sans-serif;
  --sb:256px; --r:16px; --rs:10px;
  --e:all 0.26s cubic-bezier(.4,0,.2,1);
}

/* FIX 1 — LIGHT MODE: same tokens as homepage .lm, so all pages match */
.lm{
  --bg:#F3F5FA; --s1:#EAEEF7; --s2:#E0E6F2; --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95;   --cyan-d:#007870;  --cyan-l:#00CFC3;
  --cyan-dim:rgba(0,158,149,0.08);     --cyan-border:rgba(0,158,149,0.2);
  --coral:#E8512B;  --coral-d:#C43C1C;
  --coral-dim:rgba(232,81,43,0.08);    --coral-border:rgba(232,81,43,0.2);
  --violet:#5B4FD9; --violet-d:#4540C0;
  --violet-dim:rgba(91,79,217,0.08);   --violet-border:rgba(91,79,217,0.18);
  --green:#0DAF80;  --green-d:#088C65; --green-dim:rgba(13,175,128,0.08);
  --amber:#D4980A;  --red:#D63050;
  --tx:#0D1220; --tx-2:#344060; --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.09); --bd2:rgba(30,40,80,0.16);
  --gC:rgba(0,158,149,0.09); --gO:rgba(232,81,43,0.09); --gV:rgba(91,79,217,0.09);
}

/* Apply to every child so all elements in ALL pages switch correctly */
.lm,
.lm .sidebar,
.lm .topbar,
.lm .main,
.lm .section-card,
.lm .spend-summary,
.lm .stat-card,
.lm .qa-card,
.lm .welcome-banner,
.lm .review-prompt,
.lm .notif-drop,
.lm .mobile-nav{
  transition:background .3s,border-color .3s,color .3s;
}
.lm .sidebar{background:var(--s1);border-right-color:var(--bd);}
.lm .topbar{background:rgba(243,245,250,0.96);border-bottom-color:var(--bd);}
.lm .sb-item{color:var(--tx-3);}
.lm .sb-item:hover{background:rgba(0,0,0,0.05);color:var(--tx);}
.lm .sb-item.active{background:var(--cyan-dim);color:var(--cyan);}
.lm .sb-user-card{background:rgba(0,0,0,0.06);}
.lm .stat-card{background:rgba(255,255,255,0.88);}
.lm .qa-card{background:rgba(255,255,255,0.85);}
.lm .section-card{background:rgba(255,255,255,0.9);}
.lm .spend-summary{background:linear-gradient(135deg,var(--cyan-dim),var(--violet-dim));}
.lm .review-prompt{background:rgba(255,255,255,0.88);}
.lm .welcome-banner{background:linear-gradient(135deg,var(--s2),var(--cyan-dim));}
.lm .ss-mini{background:rgba(0,0,0,0.06);}
.lm .notif-drop{background:var(--s2);border-color:var(--bd2);}
.lm .job-row:hover,.lm .prop-row:hover,.lm .msg-row:hover,
.lm .escrow-row:hover,.lm .tx-row:hover,.lm .activity-item:hover{background:rgba(0,0,0,0.04);}
.lm .btn-ghost{background:rgba(0,0,0,0.05);border-color:var(--bd2);color:var(--tx-2);}
.lm .btn-ghost:hover{background:rgba(0,0,0,0.1);color:var(--tx);}
.lm .mobile-nav{background:rgba(243,245,250,0.98);border-top-color:var(--bd);}
.lm .fab-tooltip{background:var(--s2);border-color:var(--bd2);}
.lm .toast{background:var(--s2);border-color:var(--bd2);}
.lm .review-textarea{background:rgba(0,0,0,0.05);border-color:var(--bd2);}
.lm .nd-item:hover{background:rgba(0,0,0,0.04);}
.lm .divider{background:var(--bd);}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);
  font-family:var(--fb);min-height:100vh;display:flex;
  font-size:14px;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
a{text-decoration:none;color:inherit;}
h1,h2,h3,h4,.logo-text,.stat-val,.card-ttl{
  font-family:var(--fm);-webkit-font-smoothing:antialiased;
}

/* ══ SIDEBAR ══ */
.sidebar{
  width:var(--sb);min-height:100vh;background:var(--s1);
  border-right:1px solid var(--bd);position:fixed;top:0;left:0;z-index:200;
  display:flex;flex-direction:column;overflow:hidden;
  transition:background .3s,border-color .3s;
}
.sidebar::before{
  content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--cyan));
  background-size:200% 100%;animation:gradShift 4s linear infinite;
}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:200% 50%}}
.sb-logo{
  padding:22px 18px 18px;border-bottom:1px solid var(--bd);
  display:flex;align-items:center;gap:9px;
  text-decoration:none;
}
.sb-logo-mark{
  width:34px;height:34px;
  background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  border-radius:9px;display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:15px;color:#0C0E14;flex-shrink:0;
}
.sb-logo-text{font-family:var(--fm);font-size:18px;font-weight:800;color:var(--tx);}
.sb-logo-text span{color:var(--cyan);}
.sb-nav{flex:1;padding:10px;overflow-y:auto;scrollbar-width:none;}
.sb-nav::-webkit-scrollbar{display:none;}
.sb-section{
  font-size:9px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;
  color:var(--tx-3);padding:6px 12px;margin:14px 0 4px;
}
.sb-item{
  display:flex;align-items:center;gap:10px;padding:10px 12px;
  border-radius:10px;color:var(--tx-3);font-size:13px;font-weight:500;
  transition:var(--e);position:relative;cursor:pointer;
  text-decoration:none;
}
.sb-item:hover{background:rgba(255,255,255,0.05);color:var(--tx);}
.sb-item.active{
  background:var(--cyan-dim);color:var(--cyan);
  border-left:3px solid var(--cyan);padding-left:9px;
}
.sb-item.danger{color:var(--red);}
.sb-item.danger:hover{background:rgba(255,77,106,0.08);}
/* FIX 2 — badge on sidebar items */
.sb-badge{
  margin-left:auto;background:var(--coral);color:#fff;
  font-size:9px;font-weight:800;padding:2px 7px;border-radius:50px;
  font-family:var(--fm);min-width:18px;text-align:center;
}
.sb-badge.cyan{background:var(--cyan);color:#0C0E14;}
.sb-user{padding:14px 10px;border-top:1px solid var(--bd);}
.sb-user-card{
  display:flex;align-items:center;gap:10px;padding:10px 12px;
  border-radius:10px;background:rgba(0,0,0,0.2);
  transition:background .3s;
}
.sb-av{
  width:36px;height:36px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,var(--coral),var(--violet-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-size:13px;font-weight:800;color:#fff;overflow:hidden;
}
.sb-av img{width:100%;height:100%;object-fit:cover;}
.sb-uname{font-size:13px;font-weight:700;}
.sb-urole{font-size:10px;color:var(--coral);font-weight:700;text-transform:uppercase;margin-top:1px;}

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

/* Notification bell with live badge */
.notif-wrap{position:relative;}
.notif-btn{
  width:38px;height:38px;border-radius:10px;
  background:rgba(255,255,255,0.04);border:1px solid var(--bd);
  display:flex;align-items:center;justify-content:center;
  font-size:16px;cursor:pointer;transition:var(--e);
}
.notif-btn:hover{background:rgba(255,255,255,0.08);}
/* FIX 2 — badge count on bell */
.notif-count{
  position:absolute;top:-4px;right:-4px;
  background:var(--coral);color:#fff;
  font-family:var(--fm);font-size:9px;font-weight:800;
  padding:2px 5px;border-radius:50px;min-width:17px;text-align:center;
  border:2px solid var(--bg);
  animation:pipA 2s ease-in-out infinite;
}
@keyframes pipA{0%,100%{box-shadow:0 0 0 0 rgba(255,107,74,.5);}50%{box-shadow:0 0 0 4px rgba(255,107,74,0);}}
.notif-drop{
  display:none;position:absolute;top:calc(100%+8px);right:0;width:320px;
  background:var(--s2);border:1px solid var(--bd2);border-radius:14px;
  box-shadow:0 20px 60px rgba(0,0,0,0.55);z-index:900;overflow:hidden;
  transition:background .3s;
}
.notif-drop.open{display:block;animation:dropIn .18s ease;}
@keyframes dropIn{from{opacity:0;transform:translateY(-8px);}to{opacity:1;transform:translateY(0);}}
.nd-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 16px;border-bottom:1px solid var(--bd);
  font-family:var(--fm);font-size:13px;font-weight:700;
}
.nd-mark-all{font-size:11px;color:var(--cyan);cursor:pointer;font-weight:600;}
.nd-mark-all:hover{text-decoration:underline;}
.nd-item{
  display:flex;align-items:flex-start;gap:10px;padding:12px 16px;
  border-bottom:1px solid var(--bd);transition:var(--e);cursor:pointer;
}
.nd-item:last-child{border-bottom:none;}
.nd-item:hover{background:rgba(255,255,255,0.03);}
.nd-item.unread{background:rgba(0,212,200,0.03);}
.nd-ico{
  width:30px;height:30px;border-radius:9px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:14px;
}
.nd-text{font-size:12px;color:var(--tx-2);line-height:1.5;}
.nd-title{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:2px;}
.nd-time{font-size:10px;color:var(--tx-3);margin-top:3px;}
.nd-empty{padding:24px;text-align:center;color:var(--tx-3);font-size:13px;}

/* FIX 1 — Theme toggle button */
.theme-btn{
  width:38px;height:38px;border-radius:10px;
  background:rgba(255,255,255,0.04);border:1px solid var(--bd);
  display:flex;align-items:center;justify-content:center;
  font-size:16px;cursor:pointer;transition:var(--e);
}
.theme-btn:hover{background:rgba(255,255,255,0.08);}

/* ══ BUTTONS ══ */
.btn{
  display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
  border-radius:var(--rs);font-family:var(--fb);font-size:13px;font-weight:600;
  cursor:pointer;border:none;text-decoration:none;
  transition:var(--e);white-space:nowrap;line-height:1.3;
}
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
.btn-red-soft{background:rgba(255,77,106,0.1);border:1px solid rgba(255,77,106,0.2);color:var(--red);}
.btn-red-soft:hover{background:rgba(255,77,106,0.18);}
.btn-sm{padding:5px 12px;font-size:11.5px;border-radius:8px;}
.btn-lg{padding:12px 26px;font-size:14px;border-radius:12px;}

/* ══ CONTENT ══ */
.content{padding:26px 28px 100px;}

/* ══ WELCOME BANNER ══ */
.welcome-banner{
  position:relative;overflow:hidden;border-radius:20px;
  background:linear-gradient(135deg,var(--s2) 0%,rgba(0,212,200,0.07) 60%,rgba(255,107,74,0.05) 100%);
  border:1px solid var(--cyan-border);padding:28px 32px;margin-bottom:26px;
  display:flex;align-items:center;justify-content:space-between;gap:16px;
  transition:background .3s,border-color .3s;
}
.wb-glow{position:absolute;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(0,212,200,0.1),transparent 70%);top:-120px;right:-80px;pointer-events:none;}
.wb-glow2{position:absolute;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(255,107,74,0.08),transparent 70%);bottom:-60px;left:30px;pointer-events:none;}
.wb-left{position:relative;z-index:1;}
.wb-greeting{font-size:12px;font-weight:600;color:var(--cyan);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;}
.wb-name{font-family:var(--fm);font-size:clamp(20px,2.5vw,28px);font-weight:800;line-height:1.1;margin-bottom:8px;}
.wb-sub{font-size:13px;color:var(--tx-2);line-height:1.65;max-width:420px;}
.wb-right{display:flex;gap:10px;flex-shrink:0;position:relative;z-index:1;flex-wrap:wrap;}
.wb-progress{display:flex;align-items:center;gap:10px;margin-top:14px;}
.wb-prog-track{width:160px;height:5px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;}
.wb-prog-fill{height:100%;background:linear-gradient(90deg,var(--cyan),var(--violet));border-radius:3px;transition:width 1.2s cubic-bezier(.16,1,.3,1);}
.wb-prog-lbl{font-size:11px;color:var(--tx-3);}

/* ══ STATS ══ */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:26px;}
.stat-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:22px 20px;transition:var(--e);position:relative;overflow:hidden;
}
.stat-card::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:2px;
  transform:scaleX(0);transition:transform .35s ease;transform-origin:left;
}
.stat-card.sc-cyan::after  {background:var(--cyan);}
.stat-card.sc-coral::after {background:var(--coral);}
.stat-card.sc-violet::after{background:var(--violet);}
.stat-card.sc-green::after {background:var(--green);}
.stat-card:hover{transform:translateY(-4px);border-color:var(--bd2);}
.stat-card:hover::after{transform:scaleX(1);}
.stat-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;}
.stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.si-cyan  {background:var(--cyan-dim);border:1px solid var(--cyan-border);}
.si-coral {background:var(--coral-dim);border:1px solid var(--coral-border);}
.si-violet{background:var(--violet-dim);border:1px solid var(--violet-border);}
.si-green {background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);}
.stat-delta{font-size:10px;padding:2px 7px;border-radius:50px;font-weight:700;font-family:var(--fm);}
.delta-up  {background:var(--green-dim);color:var(--green);}
.delta-info{background:var(--cyan-dim);color:var(--cyan);}
.delta-warn{background:var(--coral-dim);color:var(--coral);}
.stat-val{font-family:var(--fm);font-size:30px;font-weight:800;line-height:1;margin-bottom:4px;}
.stat-lbl{font-size:12px;color:var(--tx-3);font-weight:500;}

/* ══ QUICK ACTIONS ══ */
.qa-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px;}
.qa-card{
  background:var(--glass);backdrop-filter:blur(12px);border:1px solid var(--bd);
  border-radius:14px;padding:20px 14px;text-align:center;cursor:pointer;
  transition:var(--e);text-decoration:none;color:var(--tx);display:block;
}
.qa-card:hover{transform:translateY(-5px);border-color:var(--cyan-border);box-shadow:0 12px 32px rgba(0,0,0,0.3);}
.qa-icon{width:46px;height:46px;border-radius:13px;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;font-size:20px;transition:transform .3s;position:relative;}
.qa-card:hover .qa-icon{transform:scale(1.12) rotate(-5deg);}
.qa-label{font-family:var(--fm);font-size:12.5px;font-weight:700;}
.qa-sub{font-size:11px;color:var(--tx-3);margin-top:3px;}
/* FIX 2 — badge on quick action icon */
.qa-badge{
  position:absolute;top:-6px;right:-6px;
  background:var(--coral);color:#fff;font-family:var(--fm);
  font-size:9px;font-weight:800;padding:2px 5px;
  border-radius:50px;border:2px solid var(--bg);
}

/* ══ DASH GRID ══ */
.dash-grid{display:grid;grid-template-columns:1fr 330px;gap:22px;}

/* ══ SECTION CARDS ══ */
.section-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  overflow:hidden;margin-bottom:20px;
  transition:background .3s,border-color .3s;
}
.sc-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:18px 22px;border-bottom:1px solid var(--bd);
}
.card-ttl{font-family:var(--fm);font-size:15px;font-weight:700;}
.card-cnt{font-size:11px;color:var(--tx-3);font-weight:500;}
.empty-state{text-align:center;padding:44px 20px;color:var(--tx-3);}
.empty-state .es-ico{font-size:36px;margin-bottom:10px;}
.empty-state .es-ttl{font-family:var(--fm);font-size:15px;font-weight:700;margin-bottom:4px;color:var(--tx-2);}
.empty-state .es-sub{font-size:13px;}

/* ══ JOB ROWS ══ */
.job-row{
  display:flex;align-items:center;gap:14px;padding:16px 22px;
  border-bottom:1px solid var(--bd);transition:var(--e);
}
.job-row:last-child{border-bottom:none;}
.job-row:hover{background:rgba(255,255,255,0.025);}
.job-cat-icon{
  width:40px;height:40px;border-radius:11px;
  background:var(--cyan-dim);border:1px solid var(--cyan-border);
  display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;
}
.job-info{flex:1;min-width:0;}
.job-ttl{font-family:var(--fm);font-weight:700;font-size:13.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px;}
.job-meta{display:flex;align-items:center;gap:10px;font-size:11.5px;color:var(--tx-3);flex-wrap:wrap;}
.job-budget{color:var(--cyan);font-weight:700;}
.job-prop-count{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--violet);font-weight:600;}
.status-pill{padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:700;font-family:var(--fm);white-space:nowrap;}
.sp-open      {background:var(--cyan-dim);color:var(--cyan);border:1px solid var(--cyan-border);}
.sp-in-progress{background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.sp-completed {background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.2);}
.sp-cancelled {background:rgba(78,90,110,0.1);color:var(--tx-3);border:1px solid rgba(78,90,110,0.15);}
.sp-disputed  {background:rgba(247,183,49,0.1);color:var(--amber);border:1px solid rgba(247,183,49,0.2);}
.sp-urgent    {background:var(--coral-dim);color:var(--coral);border:1px solid var(--coral-border);}
.job-actions{display:flex;gap:5px;flex-shrink:0;flex-wrap:wrap;}

/* ══ PROPOSAL ROWS ══ */
.prop-row{
  display:flex;align-items:center;gap:14px;padding:16px 22px;
  border-bottom:1px solid var(--bd);transition:var(--e);
}
.prop-row:last-child{border-bottom:none;}
.prop-row:hover{background:rgba(255,255,255,0.025);}
.prop-av{
  width:40px;height:40px;border-radius:50%;flex-shrink:0;overflow:hidden;
  background:linear-gradient(135deg,var(--violet),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-size:14px;font-weight:800;color:#fff;
}
.prop-av img{width:100%;height:100%;object-fit:cover;}
.prop-info{flex:1;min-width:0;}
.prop-name{font-family:var(--fm);font-weight:700;font-size:13.5px;margin-bottom:2px;}
.prop-tag{font-size:11px;color:var(--tx-3);margin-bottom:4px;}
.prop-job{font-size:11.5px;color:var(--tx-2);}
.prop-right{text-align:right;flex-shrink:0;}
.prop-bid{font-family:var(--fm);font-weight:800;font-size:16px;color:var(--cyan);margin-bottom:3px;}
.prop-days{font-size:11px;color:var(--tx-3);}
.prop-stars{color:var(--amber);font-size:11px;margin-bottom:3px;}
.prop-status{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);}
.pst-pending    {background:rgba(247,183,49,0.1);color:var(--amber);}
.pst-shortlisted{background:var(--violet-dim);color:var(--violet);}
.pst-accepted   {background:var(--green-dim);color:var(--green);}
.pst-rejected   {background:rgba(255,77,106,0.1);color:var(--red);}
.prop-badges{display:flex;gap:4px;flex-wrap:wrap;margin-top:4px;}
.pb-v   {background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);padding:1px 6px;border-radius:4px;font-size:9.5px;font-weight:700;}
.pb-jobs{background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:1px 6px;border-radius:4px;font-size:9.5px;font-weight:600;}
.prop-actions{display:flex;gap:5px;flex-direction:column;}

/* ══ MESSAGES ══ */
.msg-row{
  display:flex;align-items:center;gap:12px;padding:14px 22px;
  border-bottom:1px solid var(--bd);transition:var(--e);cursor:pointer;
  text-decoration:none;color:var(--tx);
}
.msg-row:last-child{border-bottom:none;}
.msg-row:hover{background:rgba(255,255,255,0.025);}
.msg-av{
  width:42px;height:42px;border-radius:50%;flex-shrink:0;overflow:hidden;
  background:linear-gradient(135deg,var(--coral),var(--violet-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-size:14px;font-weight:800;color:#fff;
  position:relative;
}
.msg-av img{width:100%;height:100%;object-fit:cover;}
/* FIX 2+4 — online dot is only green if is_online=1 from DB */
.msg-online-dot{
  position:absolute;bottom:1px;right:1px;
  width:10px;height:10px;border-radius:50%;
  border:2px solid var(--s2);
  background:var(--tx-3); /* offline by default */
  transition:background .5s;
}
.msg-online-dot.live{background:var(--green);}
.msg-info{flex:1;min-width:0;}
.msg-name{font-family:var(--fm);font-weight:700;font-size:13px;margin-bottom:3px;}
.msg-preview{font-size:12px;color:var(--tx-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.msg-right{text-align:right;flex-shrink:0;}
.msg-time{font-size:10.5px;color:var(--tx-3);margin-bottom:5px;}
.msg-unread-badge{
  background:var(--coral);color:#fff;
  font-size:9.5px;font-weight:800;padding:2px 6px;
  border-radius:50px;font-family:var(--fm);display:inline-block;
}

/* ══ ESCROW ══ */
.escrow-row{
  display:flex;align-items:center;gap:14px;padding:14px 22px;
  border-bottom:1px solid var(--bd);transition:var(--e);
}
.escrow-row:last-child{border-bottom:none;}
.escrow-row:hover{background:rgba(255,255,255,0.025);}
.escrow-icon{width:38px;height:38px;border-radius:11px;background:rgba(247,183,49,0.1);border:1px solid rgba(247,183,49,0.2);display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;}
.escrow-info{flex:1;min-width:0;}
.escrow-job{font-family:var(--fm);font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:3px;}
.escrow-provider{font-size:11.5px;color:var(--tx-3);}
.escrow-right{text-align:right;flex-shrink:0;}
.escrow-amount{font-family:var(--fm);font-weight:800;font-size:16px;color:var(--amber);margin-bottom:4px;}
.escrow-badge{background:rgba(247,183,49,0.1);border:1px solid rgba(247,183,49,0.2);color:var(--amber);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;font-family:var(--fm);}

/* ══ TRANSACTIONS ══ */
.tx-row{
  display:flex;align-items:center;gap:12px;padding:12px 22px;
  border-bottom:1px solid var(--bd);transition:var(--e);
}
.tx-row:last-child{border-bottom:none;}
.tx-row:hover{background:rgba(255,255,255,0.025);}
.tx-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.tx-info{flex:1;min-width:0;}
.tx-desc{font-size:12.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.tx-date{font-size:11px;color:var(--tx-3);margin-top:1px;}
.tx-amt{font-family:var(--fm);font-weight:800;font-size:14px;text-align:right;flex-shrink:0;}
.tx-status{font-size:10px;color:var(--tx-3);text-align:right;margin-top:2px;}

/* ══ REVIEW PROMPTS ══ */
.review-prompt{
  background:linear-gradient(135deg,rgba(247,183,49,0.06),rgba(255,107,74,0.04));
  border:1px solid rgba(247,183,49,0.18);border-radius:var(--r);
  padding:18px 20px;margin-bottom:10px;display:flex;align-items:center;gap:14px;
  transition:var(--e);
}
.review-prompt:hover{border-color:rgba(247,183,49,0.3);}
.rp-av{width:44px;height:44px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--coral),var(--violet-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:15px;font-weight:800;color:#fff;}
.rp-av img{width:100%;height:100%;object-fit:cover;}
.rp-info{flex:1;}
.rp-title{font-family:var(--fm);font-weight:700;font-size:13px;margin-bottom:3px;}
.rp-sub{font-size:12px;color:var(--tx-3);}
.star-rating{display:flex;gap:3px;margin-top:8px;}
.star-btn{font-size:20px;cursor:pointer;color:rgba(247,183,49,0.25);transition:color .15s;background:none;border:none;padding:0;line-height:1;}
.star-btn:hover,.star-btn.active{color:var(--amber);}
.review-textarea{width:100%;background:rgba(0,0,0,0.2);border:1px solid var(--bd);border-radius:var(--rs);padding:10px 12px;color:var(--tx);font-family:var(--fb);font-size:13px;outline:none;resize:vertical;min-height:70px;margin-top:8px;transition:border-color .3s;}
.review-textarea:focus{border-color:var(--cyan);}

/* ══ SIDEBAR RIGHT ══ */
.spend-summary{
  background:linear-gradient(135deg,rgba(0,212,200,0.08),rgba(124,111,247,0.06));
  border:1px solid var(--cyan-border);border-radius:var(--r);
  padding:22px;margin-bottom:20px;
  transition:background .3s,border-color .3s;
}
.ss-label{font-size:11px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px;}
.ss-amount{font-family:var(--fm);font-size:34px;font-weight:900;color:var(--cyan);margin-bottom:16px;line-height:1;}
.ss-row{display:flex;gap:10px;}
.ss-mini{flex:1;background:rgba(0,0,0,0.18);border-radius:10px;padding:12px;text-align:center;transition:background .3s;}
.ss-mini-val{font-family:var(--fm);font-weight:800;font-size:15px;color:var(--amber);}
.ss-mini-lbl{font-size:10.5px;color:var(--tx-3);margin-top:3px;}

/* ══ ACTIVITY ══ */
.activity-item{
  display:flex;align-items:flex-start;gap:11px;padding:12px 20px;
  border-bottom:1px solid var(--bd);transition:var(--e);
}
.activity-item:last-child{border-bottom:none;}
.activity-item:hover{background:rgba(255,255,255,0.02);}
.ac-dot{width:28px;height:28px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:13px;margin-top:2px;}
.ac-text{font-size:12.5px;line-height:1.55;color:var(--tx-2);}
.ac-time{font-size:10.5px;color:var(--tx-3);margin-top:3px;}

/* ══ FAB ══ */
.fab{
  position:fixed;bottom:28px;right:28px;z-index:990;
  width:54px;height:54px;border-radius:50%;
  background:linear-gradient(135deg,var(--coral),var(--coral-d));
  border:none;cursor:pointer;font-size:22px;color:#fff;
  box-shadow:0 6px 24px var(--gO);transition:var(--e);
  display:flex;align-items:center;justify-content:center;
}
.fab:hover{transform:scale(1.1) rotate(8deg);box-shadow:0 10px 34px var(--gO);}
.fab-tooltip{
  position:fixed;bottom:34px;right:88px;z-index:990;
  background:var(--s2);border:1px solid var(--bd);border-radius:10px;
  padding:7px 13px;font-size:12px;font-weight:600;font-family:var(--fm);
  color:var(--tx);white-space:nowrap;box-shadow:0 8px 24px rgba(0,0,0,.3);
  opacity:0;pointer-events:none;transition:opacity .25s;
  transition:background .3s,color .3s,border-color .3s,opacity .25s;
}
.fab:hover ~ .fab-tooltip{opacity:1;}

/* ══ TOAST ══ */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{
  display:flex;align-items:center;gap:11px;background:var(--s2);
  border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);
  max-width:340px;min-width:250px;
  box-shadow:0 12px 36px rgba(0,0,0,.5);animation:toastIn .35s ease;
  backdrop-filter:blur(14px);transition:background .3s;
}
.toast.success{border-left:3px solid var(--green);}
.toast.error  {border-left:3px solid var(--red);}
.toast.info   {border-left:3px solid var(--cyan);}
.toast.warning{border-left:3px solid var(--amber);}
.t-ico{font-size:17px;flex-shrink:0;}
.t-bod{flex:1;}
.t-ttl{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}
.t-msg{font-size:11.5px;color:var(--tx-3);}
.t-cls{cursor:pointer;color:var(--tx-3);font-size:17px;flex-shrink:0;}
@keyframes toastIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}

/* ══ MOBILE BOTTOM NAV ══ */
.mobile-nav{
  display:none;position:fixed;bottom:0;left:0;right:0;z-index:500;
  background:rgba(12,14,20,0.97);backdrop-filter:blur(20px);
  border-top:1px solid var(--bd);
  padding:8px 0 env(safe-area-inset-bottom);
  grid-template-columns:repeat(5,1fr);
  transition:background .3s,border-color .3s;
}
.mn-item{
  display:flex;flex-direction:column;align-items:center;gap:3px;
  padding:6px 4px;cursor:pointer;transition:var(--e);
  text-decoration:none;color:var(--tx-3);position:relative;
}
.mn-item.active{color:var(--cyan);}
.mn-item:hover{color:var(--tx);}
.mn-ico{font-size:20px;}
.mn-lbl{font-size:9px;font-weight:600;font-family:var(--fm);text-transform:uppercase;letter-spacing:.3px;}
.mn-badge{
  position:absolute;top:2px;right:14px;
  background:var(--coral);color:#fff;font-size:8px;font-weight:800;
  padding:1px 5px;border-radius:50px;font-family:var(--fm);
}

/* ══ RESPONSIVE ══ */
@media(max-width:1200px){.stats-grid{grid-template-columns:repeat(2,1fr);}.qa-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:1024px){.dash-grid{grid-template-columns:1fr;}}
@media(max-width:768px){
  .sidebar{display:none;}.main{margin-left:0;}
  .mobile-nav{display:grid;}
  .content{padding:18px 14px 90px;}
  .topbar{padding:0 16px;}
  .welcome-banner{flex-direction:column;gap:16px;padding:22px 18px;}
  .wb-right{width:100%;}
  .fab{bottom:80px;}
  .fab-tooltip{bottom:86px;}
  #toast-c{bottom:90px;}
}
@media(max-width:480px){
  .stats-grid{grid-template-columns:1fr 1fr;}
  .qa-grid{grid-template-columns:1fr 1fr;}
}
.divider{height:1px;background:var(--bd);margin:0 22px;}
</style>
</head>
<!-- FIX 1: Apply .lm server-side via cookie — no flash on load -->
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar">
  <a href="<?= APP_URL ?>/index.php" class="sb-logo">
    <div class="sb-logo-mark">G</div>
    <span class="sb-logo-text">Gig<span>Ghana</span></span>
  </a>
  <nav class="sb-nav">
    <div class="sb-section">Client</div>
    <a href="<?= APP_URL ?>/client/dashboard.php" class="sb-item active">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/client/post-job.php"  class="sb-item">✏️ Post a Job</a>
    <a href="<?= APP_URL ?>/client/my-jobs.php"   class="sb-item">
      💼 My Jobs
      <?php if($activeJobs > 0): ?><span class="sb-badge cyan"><?= $activeJobs ?></span><?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/client/proposals.php" class="sb-item">
      📩 Proposals
      <?php if($totalProposals > 0): ?><span class="sb-badge"><?= $totalProposals ?></span><?php endif; ?>
    </a>
    <div class="sb-section">Communication</div>
    <a href="<?= APP_URL ?>/client/messages.php"  class="sb-item">
      💬 Messages
      <?php if($unreadMsgs > 0): ?><span class="sb-badge"><?= $unreadMsgs ?></span><?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/search/providers.php" class="sb-item">🔍 Find Talent</a>
    <div class="sb-section">Finance</div>
    <a href="<?= APP_URL ?>/client/payments.php"  class="sb-item">💳 Payments</a>
    <a href="<?= APP_URL ?>/client/escrow.php"    class="sb-item">
      🔒 Escrow
      <?php if(!empty($escrows)): ?><span class="sb-badge"><?= count($escrows) ?></span><?php endif; ?>
    </a>
    <div class="sb-section">Account</div>
    <a href="<?= APP_URL ?>/client/settings.php"  class="sb-item">⚙️ Settings</a>
    <a href="<?= APP_URL ?>/index.php"             class="sb-item">🏠 Homepage</a>
    <a href="<?= APP_URL ?>/auth/logout.php"       class="sb-item danger">🚪 Sign Out</a>
  </nav>
  <div class="sb-user">
    <div class="sb-user-card">
      <div class="sb-av">
        <?php if(!empty($user['avatar'])): ?>
          <img src="<?= sanitize($user['avatar']) ?>" alt="">
        <?php else: echo strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)); endif; ?>
      </div>
      <div>
        <div class="sb-uname"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div>
        <div class="sb-urole">Client</div>
      </div>
    </div>
  </div>
</aside>

<!-- ══════════ MAIN ══════════ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-title">
      <h1>Client Dashboard</h1>
      <p><?= date('l, F j, Y') ?></p>
    </div>
    <div class="topbar-right">

      <!-- FIX 1 — Theme toggle button (same as homepage) -->
      <button class="theme-btn" id="themeBtn" onclick="toggleTheme()"
              title="Toggle light / dark mode">
        <?= $isLight ? '☀️' : '🌙' ?>
      </button>

      <!-- FIX 2 — Notification bell with live DB count -->
      <div class="notif-wrap">
        <div class="notif-btn" id="notifBtn" onclick="toggleNotifs()" title="Notifications">
          🔔
          <?php if($unreadNotifs > 0): ?>
          <span class="notif-count"><?= min($unreadNotifs,99) ?></span>
          <?php endif; ?>
        </div>
        <div class="notif-drop" id="notifDrop">
          <div class="nd-head">
            🔔 Notifications
            <?php if($unreadNotifs > 0): ?>
            <span class="nd-mark-all" onclick="markAllRead()">Mark all read</span>
            <?php endif; ?>
          </div>
          <?php if(empty($notifs)): ?>
          <div class="nd-empty">You're all caught up! 🎉</div>
          <?php else: foreach($notifs as $n): ?>
          <div class="nd-item unread">
            <div class="nd-ico" style="background:var(--cyan-dim);">
              <?php $nIcon = match($n['type']??''){
                'new_proposal'=>'📩','message'=>'💬','payment'=>'💰',
                'job_completed'=>'✅','job_hired'=>'🎉',default=>'🔔'
              }; echo $nIcon; ?>
            </div>
            <div>
              <div class="nd-title"><?= sanitize($n['title']) ?></div>
              <div class="nd-text"><?= sanitize(mb_substr($n['message'],0,80)) ?></div>
              <div class="nd-time"><?= timeAgo($n['created_at']) ?></div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <a href="<?= APP_URL ?>/index.php"           class="btn btn-ghost">🏠 Home</a>
      <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-cyan">+ Post Job</a>
      <a href="<?= APP_URL ?>/auth/logout.php"     class="btn btn-ghost" style="color:var(--red);border-color:rgba(255,77,106,0.2);">🚪</a>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- WELCOME BANNER -->
    <div class="welcome-banner">
      <div class="wb-glow"></div><div class="wb-glow2"></div>
      <div class="wb-left">
        <div class="wb-greeting">👋 <?= $greeting ?></div>
        <div class="wb-name"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div>
        <div class="wb-sub">
          You have
          <strong style="color:var(--cyan);"><?= $activeJobs ?> active job<?= $activeJobs!=1?'s':'' ?></strong>
          <?php if($totalProposals > 0): ?>
          and <strong style="color:var(--coral);"><?= $totalProposals ?> new proposal<?= $totalProposals!=1?'s':'' ?></strong> awaiting review
          <?php endif; ?>.
          <?php if($unreadMsgs > 0): ?>
          You also have <strong style="color:var(--violet);"><?= $unreadMsgs ?> unread message<?= $unreadMsgs!=1?'s':'' ?></strong>.
          <?php endif; ?>
        </div>
        <div class="wb-progress">
          <div class="wb-prog-track"><div class="wb-prog-fill" id="wbProgFill" style="width:0%"></div></div>
          <span class="wb-prog-lbl"><?= $completeness ?>% profile complete</span>
          <?php if($completeness < 100): ?>
          <a href="<?= APP_URL ?>/client/settings.php" style="font-size:11px;color:var(--cyan);margin-left:4px;">Improve →</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="wb-right">
        <a href="<?= APP_URL ?>/client/post-job.php"  class="btn btn-coral btn-lg">✏️ Post a New Job</a>
        <a href="<?= APP_URL ?>/search/providers.php" class="btn btn-ghost btn-lg">🔍 Browse Talent</a>
      </div>
    </div>

    <!-- STATS — FIX 3: all from DB -->
    <div class="stats-grid">
      <div class="stat-card sc-cyan">
        <div class="stat-top">
          <div class="stat-icon si-cyan">⚡</div>
          <span class="stat-delta delta-info">Live</span>
        </div>
        <div class="stat-val" data-count="<?= $activeJobs ?>"><?= $activeJobs ?></div>
        <div class="stat-lbl">Active Jobs</div>
      </div>
      <div class="stat-card sc-violet">
        <div class="stat-top">
          <div class="stat-icon si-violet">📩</div>
          <?php if($totalProposals > 0): ?><span class="stat-delta delta-warn">New</span><?php endif; ?>
        </div>
        <div class="stat-val" data-count="<?= $totalProposals ?>"><?= $totalProposals ?></div>
        <div class="stat-lbl">Pending Proposals</div>
      </div>
      <div class="stat-card sc-green">
        <div class="stat-top">
          <div class="stat-icon si-green">✅</div>
          <span class="stat-delta delta-up">Done</span>
        </div>
        <div class="stat-val" data-count="<?= $completedJobs ?>"><?= $completedJobs ?></div>
        <div class="stat-lbl">Jobs Completed</div>
      </div>
      <div class="stat-card sc-coral">
        <div class="stat-top">
          <div class="stat-icon si-coral">₵</div>
        </div>
        <div class="stat-val"><?= formatCurrency($totalSpent > 0 ? $totalSpent : ($wallet['total_spent']??0)) ?></div>
        <div class="stat-lbl">Total Spent (GHS)</div>
      </div>
    </div>

    <!-- QUICK ACTIONS — FIX 2: badges from DB -->
    <div class="qa-grid">
      <a href="<?= APP_URL ?>/client/post-job.php" class="qa-card">
        <div class="qa-icon" style="background:var(--coral-dim);border:1px solid var(--coral-border);">✏️</div>
        <div class="qa-label">Post New Job</div>
        <div class="qa-sub">Find the right talent</div>
      </a>
      <a href="<?= APP_URL ?>/search/providers.php" class="qa-card">
        <div class="qa-icon" style="background:var(--cyan-dim);border:1px solid var(--cyan-border);">🔍</div>
        <div class="qa-label">Browse Providers</div>
        <div class="qa-sub">Explore all talent</div>
      </a>
      <a href="<?= APP_URL ?>/client/messages.php" class="qa-card">
        <div class="qa-icon" style="background:var(--violet-dim);border:1px solid var(--violet-border);">
          💬
          <?php if($unreadMsgs > 0): ?><span class="qa-badge"><?= $unreadMsgs ?></span><?php endif; ?>
        </div>
        <div class="qa-label">Messages</div>
        <div class="qa-sub"><?= $unreadMsgs > 0 ? $unreadMsgs.' unread' : 'All caught up' ?></div>
      </a>
      <a href="<?= APP_URL ?>/client/payments.php" class="qa-card">
        <div class="qa-icon" style="background:rgba(247,183,49,0.1);border:1px solid rgba(247,183,49,0.2);">💳</div>
        <div class="qa-label">Payments</div>
        <div class="qa-sub">Escrow &amp; history</div>
      </a>
    </div>

    <!-- MAIN GRID -->
    <div class="dash-grid">

      <!-- LEFT COLUMN -->
      <div>

        <!-- PENDING REVIEWS -->
        <?php if(!empty($pendingReviews)): ?>
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">⭐ Rate Your Experience</div>
              <div class="card-cnt"><?= count($pendingReviews) ?> job<?= count($pendingReviews)!=1?'s':'' ?> awaiting review</div>
            </div>
          </div>
          <div style="padding:16px 22px;display:flex;flex-direction:column;gap:12px;">
            <?php foreach($pendingReviews as $pr):
              $pinit = strtoupper(substr($pr['first_name'],0,1).substr($pr['last_name'],0,1));
            ?>
            <div class="review-prompt" id="rp_<?= $pr['id'] ?>">
              <div class="rp-av">
                <?php if(!empty($pr['avatar'])): ?><img src="<?= sanitize($pr['avatar']) ?>" alt="" loading="lazy"><?php else: echo $pinit; endif; ?>
              </div>
              <div class="rp-info">
                <div class="rp-title"><?= sanitize($pr['title']) ?></div>
                <div class="rp-sub">Provider: <?= sanitize($pr['first_name'].' '.$pr['last_name']) ?></div>
                <div class="star-rating" data-job="<?= $pr['id'] ?>" data-provider="<?= $pr['provider_row_id'] ?>">
                  <?php for($s=1;$s<=5;$s++): ?><button class="star-btn" data-val="<?= $s ?>" onclick="setStar(this,<?= $s ?>)">★</button><?php endfor; ?>
                </div>
                <textarea class="review-textarea" id="rv_comment_<?= $pr['id'] ?>" placeholder="Share your experience…"></textarea>
              </div>
              <button class="btn btn-cyan btn-sm" onclick="submitReview(<?= $pr['id'] ?>,<?= $pr['provider_row_id'] ?>,'<?= $csrf ?>')">Submit</button>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- MY JOBS — FIX 3 -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">💼 My Jobs</div>
              <div class="card-cnt"><?= count($myJobs) ?> jobs</div>
            </div>
            <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-coral btn-sm">+ Post New</a>
          </div>
          <?php if(empty($myJobs)): ?>
          <div class="empty-state">
            <div class="es-ico">💼</div>
            <div class="es-ttl">No jobs posted yet</div>
            <div class="es-sub">Post your first job and start receiving proposals from skilled Ghanaians.</div>
            <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-coral" style="margin-top:16px;justify-content:center;">Post a Job →</a>
          </div>
          <?php else: foreach($myJobs as $j):
            $catIco    = $iconMap[$j['cat_icon']??''] ?? '📋';
            $propCount = (int)$j['prop_count'];
            $st        = $j['status'];
            $stClass   = match($st){'open'=>'sp-open','in_progress'=>'sp-in-progress','completed'=>'sp-completed','cancelled'=>'sp-cancelled','disputed'=>'sp-disputed',default=>'sp-open'};
            $stLabel   = match($st){'open'=>'● Open','in_progress'=>'🔄 In Progress','completed'=>'✅ Done','cancelled'=>'✕ Cancelled','disputed'=>'⚠️ Disputed',default=>ucfirst($st)};
          ?>
          <div class="job-row">
            <div class="job-cat-icon"><?= $catIco ?></div>
            <div class="job-info">
              <div class="job-ttl"><?= sanitize($j['title']) ?></div>
              <div class="job-meta">
                <span><?= sanitize($j['cat_name']??'General') ?></span>
                <span class="job-budget"><?= formatCurrency($j['budget_min']) ?><?= $j['budget_max']>$j['budget_min']?' – '.formatCurrency($j['budget_max']):'' ?><?= $j['budget_type']==='hourly'?'/hr':'' ?></span>
                <span class="job-prop-count">📩 <?= $propCount ?></span>
                <span><?= timeAgo($j['created_at']) ?></span>
              </div>
            </div>
            <div class="job-actions">
              <?php if($j['is_urgent']): ?><span class="status-pill sp-urgent">🔥 Urgent</span><?php endif; ?>
              <span class="status-pill <?= $stClass ?>"><?= $stLabel ?></span>
              <a href="<?= APP_URL ?>/client/view-proposals.php?job_id=<?= $j['id'] ?>" class="btn btn-ghost btn-sm">View <?= $propCount ?></a>
              <?php if($st==='open'): ?>
              <a href="<?= APP_URL ?>/client/edit-job.php?id=<?= $j['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- PROPOSALS — FIX 3 -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">📩 Proposals Received</div>
              <div class="card-cnt"><?= count($proposals) ?> proposal<?= count($proposals)!=1?'s':'' ?></div>
            </div>
            <a href="<?= APP_URL ?>/client/proposals.php" class="btn btn-ghost btn-sm">View All</a>
          </div>
          <?php if(empty($proposals)): ?>
          <div class="empty-state">
            <div class="es-ico">📩</div>
            <div class="es-ttl">No proposals yet</div>
            <div class="es-sub">Post a job to start receiving proposals from verified Ghanaian freelancers.</div>
          </div>
          <?php else: foreach($proposals as $pp):
            $pinit    = strtoupper(substr($pp['first_name'],0,1).substr($pp['last_name'],0,1));
            $rv       = (float)($pp['rating_avg']??0);
            $pstClass = match($pp['status']){'accepted'=>'pst-accepted','rejected'=>'pst-rejected','shortlisted'=>'pst-shortlisted',default=>'pst-pending'};
          ?>
          <div class="prop-row">
            <div class="prop-av">
              <?php if(!empty($pp['avatar'])): ?><img src="<?= sanitize($pp['avatar']) ?>" alt="" loading="lazy"><?php else: echo $pinit; endif; ?>
            </div>
            <div class="prop-info">
              <div class="prop-name"><?= sanitize($pp['first_name'].' '.$pp['last_name']) ?></div>
              <div class="prop-tag"><?= sanitize($pp['tagline']??'Freelancer') ?></div>
              <div class="prop-job">📋 <?= sanitize($pp['job_title']) ?></div>
              <div class="prop-badges">
                <?php if($pp['is_verified']??0): ?><span class="pb-v">✓ Verified</span><?php endif; ?>
                <?php if(($pp['completed_jobs']??0)>0): ?><span class="pb-jobs">✅ <?= $pp['completed_jobs'] ?> jobs</span><?php endif; ?>
              </div>
            </div>
            <div class="prop-right">
              <div class="prop-bid"><?= formatCurrency($pp['bid_amount']) ?></div>
              <div class="prop-days">⏱ <?= $pp['delivery_days'] ?> day<?= $pp['delivery_days']!=1?'s':'' ?></div>
              <div class="prop-stars"><?php for($s=1;$s<=5;$s++) echo $rv>=$s?'★':'☆'; ?> <?= number_format($rv,1) ?></div>
              <span class="prop-status <?= $pstClass ?>"><?= ucfirst($pp['status']) ?></span>
            </div>
            <div class="prop-actions">
              <a href="<?= APP_URL ?>/client/view-proposal.php?id=<?= $pp['id'] ?>"              class="btn btn-ghost btn-sm">View</a>
              <a href="<?= APP_URL ?>/client/messages.php?start=<?= $pp['provider_row_id'] ?>"   class="btn btn-violet btn-sm">Chat</a>
              <?php if(in_array($pp['status'],['pending','shortlisted'])): ?>
              <form method="POST" action="<?= APP_URL ?>/client/accept-proposal.php" style="display:inline;">
                <input type="hidden" name="csrf_token"  value="<?= $csrf ?>">
                <input type="hidden" name="proposal_id" value="<?= $pp['id'] ?>">
                <button type="submit" class="btn btn-cyan btn-sm"
                  onclick="return confirm('Hire <?= sanitize($pp['first_name']) ?> for this job?')">Hire</button>
              </form>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- ESCROW -->
        <?php if(!empty($escrows)): ?>
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">🔒 Funds in Escrow</div>
              <div class="card-cnt"><?= count($escrows) ?> active</div>
            </div>
            <a href="<?= APP_URL ?>/client/payments.php" class="btn btn-ghost btn-sm">All Payments</a>
          </div>
          <?php foreach($escrows as $esc): ?>
          <div class="escrow-row">
            <div class="escrow-icon">🔒</div>
            <div class="escrow-info">
              <div class="escrow-job"><?= sanitize($esc['job_title']) ?></div>
              <div class="escrow-provider">Provider: <?= sanitize($esc['first_name'].' '.$esc['last_name']) ?></div>
            </div>
            <div class="escrow-right">
              <div class="escrow-amount"><?= formatCurrency($esc['amount']) ?></div>
              <span class="escrow-badge">🔒 Held</span>
            </div>
            <form method="POST" action="<?= APP_URL ?>/client/release-payment.php" style="flex-shrink:0;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="escrow_id"  value="<?= $esc['id'] ?>">
              <button type="submit" class="btn btn-green btn-sm"
                onclick="return confirm('Release <?= formatCurrency($esc['amount']) ?> to the provider?')">Release</button>
            </form>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- MESSAGES — FIX 4: correct query, content column, real online -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">💬 Messages</div>
              <?php if($unreadMsgs > 0): ?>
              <div class="card-cnt" style="color:var(--coral);"><?= $unreadMsgs ?> unread</div>
              <?php endif; ?>
            </div>
            <a href="<?= APP_URL ?>/client/messages.php" class="btn btn-ghost btn-sm">Open Inbox</a>
          </div>
          <?php if(empty($convs)): ?>
          <div class="empty-state">
            <div class="es-ico">💬</div>
            <div class="es-ttl">No conversations yet</div>
            <div class="es-sub">Start chatting with providers after receiving proposals.</div>
          </div>
          <?php else: foreach($convs as $cv):
            $cinit    = strtoupper(substr($cv['first_name'],0,1).substr($cv['last_name'],0,1));
            $convOnline = (bool)($cv['is_online'] ?? false);
            $preview  = $cv['last_msg'] ?? $cv['last_message_preview'] ?? 'Start a conversation…';
          ?>
          <a href="<?= APP_URL ?>/client/messages.php?conv=<?= $cv['id'] ?>" class="msg-row">
            <div class="msg-av">
              <?php if(!empty($cv['avatar'])): ?><img src="<?= sanitize($cv['avatar']) ?>" alt="" loading="lazy"><?php else: echo $cinit; endif; ?>
              <!-- FIX 4: online dot only lit when DB confirms online -->
              <div class="msg-online-dot <?= $convOnline ? 'live' : '' ?>"></div>
            </div>
            <div class="msg-info">
              <div class="msg-name"><?= sanitize($cv['first_name'].' '.$cv['last_name']) ?></div>
              <div class="msg-preview"><?= htmlspecialchars(mb_substr($preview,0,55)) ?></div>
            </div>
            <div class="msg-right">
              <div class="msg-time"><?= $cv['last_message_at'] ? timeAgo($cv['last_message_at']) : '' ?></div>
              <?php if((int)$cv['unread'] > 0): ?>
              <span class="msg-unread-badge"><?= min((int)$cv['unread'],99) ?></span>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; endif; ?>
        </div>

        <!-- TRANSACTIONS -->
        <?php if(!empty($transactions)): ?>
        <div class="section-card">
          <div class="sc-head">
            <div class="card-ttl">💳 Recent Transactions</div>
            <a href="<?= APP_URL ?>/client/payments.php" class="btn btn-ghost btn-sm">Full History</a>
          </div>
          <?php foreach($transactions as $tx):
            $isDebit  = in_array($tx['type'],['escrow_lock','platform_fee']);
            $isCredit = in_array($tx['type'],['refund','deposit']);
            $txColor  = $tx['status']==='completed' ? ($isCredit ? 'var(--green)' : ($isDebit ? 'var(--coral)' : 'var(--tx)')) : 'var(--tx-3)';
            $txIcon   = match($tx['type']){'deposit'=>'💰','withdrawal'=>'💸','escrow_lock'=>'🔒','escrow_release'=>'🔓','platform_fee'=>'⚡','refund'=>'↩️',default=>'💳'};
            $txIcoBg  = match($tx['type']){'escrow_lock'=>'rgba(247,183,49,0.1)','escrow_release'=>'var(--green-dim)','refund'=>'var(--cyan-dim)',default=>'rgba(255,255,255,0.04)'};
          ?>
          <div class="tx-row">
            <div class="tx-icon" style="background:<?= $txIcoBg ?>;"><?= $txIcon ?></div>
            <div class="tx-info">
              <div class="tx-desc"><?= sanitize($tx['description'] ?? ucfirst(str_replace('_',' ',$tx['type']))) ?><?= $tx['job_title'] ? ' — '.sanitize($tx['job_title']) : '' ?></div>
              <div class="tx-date"><?= timeAgo($tx['created_at']) ?> · <?= ucfirst($tx['payment_method']??'') ?></div>
            </div>
            <div>
              <div class="tx-amt" style="color:<?= $txColor ?>;"><?= $isDebit?'-':($isCredit?'+':'') ?><?= formatCurrency($tx['amount']) ?></div>
              <div class="tx-status"><?= ucfirst($tx['status']) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

      </div><!-- /left -->

      <!-- RIGHT COLUMN -->
      <div>

        <!-- SPEND SUMMARY -->
        <div class="spend-summary">
          <div class="ss-label">Total Spent (GHS)</div>
          <div class="ss-amount"><?= formatCurrency($totalSpent > 0 ? $totalSpent : ($wallet['total_spent']??0)) ?></div>
          <div class="ss-row">
            <div class="ss-mini">
              <div class="ss-mini-val"><?= formatCurrency($wallet['pending_balance']??0) ?></div>
              <div class="ss-mini-lbl">In Escrow</div>
            </div>
            <div class="ss-mini">
              <div class="ss-mini-val"><?= formatCurrency($wallet['available_balance']??0) ?></div>
              <div class="ss-mini-lbl">Wallet</div>
            </div>
          </div>
          <a href="<?= APP_URL ?>/client/payments.php"
             class="btn btn-cyan" style="width:100%;justify-content:center;margin-top:16px;">
            💳 Fund Wallet
          </a>
        </div>

        <!-- ACTIVITY FEED -->
        <div class="section-card" style="margin-bottom:20px;">
          <div class="sc-head">
            <div class="card-ttl">📡 Activity Feed</div>
          </div>
          <?php
          $activities = [];
          foreach(array_slice($proposals,0,3) as $pp)
            $activities[] = ['ico'=>'📩','bg'=>'var(--violet-dim)',
              'text'=>sanitize($pp['first_name'].' '.$pp['last_name']).' submitted a proposal for <strong>'.sanitize($pp['job_title']).'</strong>',
              'time'=>$pp['created_at']];
          foreach(array_slice($myJobs,0,3) as $j)
            $activities[] = ['ico'=>'💼','bg'=>'var(--cyan-dim)',
              'text'=>'You posted <strong>'.sanitize($j['title']).'</strong>',
              'time'=>$j['created_at']];
          foreach(array_slice($convs,0,2) as $cv)
            $activities[] = ['ico'=>'💬','bg'=>'var(--coral-dim)',
              'text'=>'Conversation with <strong>'.sanitize($cv['first_name'].' '.$cv['last_name']).'</strong>',
              'time'=>$cv['last_message_at']??$cv['created_at']??date('Y-m-d H:i:s')];
          usort($activities, fn($a,$b)=>strtotime($b['time'])-strtotime($a['time']));
          ?>
          <?php if(empty($activities)): ?>
          <div class="empty-state" style="padding:24px;">
            <div class="es-ico">📡</div>
            <div class="es-sub">Activity will appear as you use GigGhana.</div>
          </div>
          <?php else: foreach(array_slice($activities,0,7) as $ac): ?>
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
        <div class="section-card" style="margin-bottom:20px;">
          <div class="sc-head"><div class="card-ttl">⚡ Quick Actions</div></div>
          <div style="padding:14px;display:grid;grid-template-columns:1fr 1fr;gap:9px;">
            <a href="<?= APP_URL ?>/client/post-job.php"   class="btn btn-coral"    style="justify-content:center;font-size:12px;">✏️ Post Job</a>
            <a href="<?= APP_URL ?>/search/providers.php"  class="btn btn-cyan"     style="justify-content:center;font-size:12px;">🔍 Find Talent</a>
            <a href="<?= APP_URL ?>/client/my-jobs.php"    class="btn btn-ghost"    style="justify-content:center;font-size:12px;">💼 My Jobs</a>
            <a href="<?= APP_URL ?>/client/proposals.php"  class="btn btn-ghost"    style="justify-content:center;font-size:12px;">📩 Proposals</a>
            <a href="<?= APP_URL ?>/client/messages.php"   class="btn btn-ghost"    style="justify-content:center;font-size:12px;">💬 Messages</a>
            <a href="<?= APP_URL ?>/client/payments.php"   class="btn btn-ghost"    style="justify-content:center;font-size:12px;">💳 Payments</a>
            <a href="<?= APP_URL ?>/client/settings.php"   class="btn btn-ghost"    style="justify-content:center;font-size:12px;grid-column:1;">⚙️ Settings</a>
            <a href="<?= APP_URL ?>/auth/logout.php"       class="btn btn-red-soft" style="justify-content:center;font-size:12px;">🚪 Logout</a>
          </div>
        </div>

        <!-- PLATFORM TIPS -->
        <div class="section-card">
          <div class="sc-head"><div class="card-ttl">💡 Tips for Clients</div></div>
          <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">
            <?php foreach([
              ['🎯','Write detailed job descriptions to attract better proposals.'],
              ['💬','Message providers quickly — top talent gets hired fast!'],
              ['🔒','Always use Escrow to protect your payments.'],
              ['⭐','Leaving reviews helps the GigGhana community grow.'],
              ['🇬🇭','Verified providers carry our Ghana Card badge for extra trust.'],
            ] as [$ic,$tp]): ?>
            <div style="display:flex;align-items:flex-start;gap:10px;font-size:12.5px;color:var(--tx-2);">
              <span style="font-size:15px;flex-shrink:0;"><?= $ic ?></span>
              <span><?= $tp ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div><!-- /right -->
    </div><!-- /dash-grid -->
  </div><!-- /content -->
</div><!-- /main -->

<!-- MOBILE BOTTOM NAV -->
<nav class="mobile-nav">
  <a href="<?= APP_URL ?>/client/dashboard.php"  class="mn-item active"><div class="mn-ico">📊</div><div class="mn-lbl">Home</div></a>
  <a href="<?= APP_URL ?>/client/my-jobs.php"    class="mn-item">
    <div class="mn-ico">💼</div><div class="mn-lbl">Jobs</div>
    <?php if($activeJobs>0): ?><span class="mn-badge"><?= $activeJobs ?></span><?php endif; ?>
  </a>
  <a href="<?= APP_URL ?>/client/post-job.php"   class="mn-item">
    <div class="mn-ico" style="background:var(--coral);border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:-6px;font-size:22px;">+</div>
    <div class="mn-lbl">Post</div>
  </a>
  <a href="<?= APP_URL ?>/client/messages.php"   class="mn-item">
    <div class="mn-ico">💬</div><div class="mn-lbl">Messages</div>
    <?php if($unreadMsgs>0): ?><span class="mn-badge"><?= $unreadMsgs ?></span><?php endif; ?>
  </a>
  <a href="<?= APP_URL ?>/client/proposals.php"  class="mn-item">
    <div class="mn-ico">📩</div><div class="mn-lbl">Bids</div>
    <?php if($totalProposals>0): ?><span class="mn-badge"><?= $totalProposals ?></span><?php endif; ?>
  </a>
</nav>

<!-- FAB -->
<button class="fab" onclick="window.location='<?= APP_URL ?>/client/post-job.php'" title="Post a new job">+</button>
<div class="fab-tooltip">Post a New Job</div>

<div id="toast-c"></div>

<script>
/* ═══════════════════════════════════════════════════
   FIX 1 — THEME TOGGLE
   Mirrors the homepage toggleTheme() exactly.
   Sets localStorage + a cookie so PHP can read it
   server-side on the next load (no flash).
   All pages that share the .lm CSS class will
   pick up the cookie and render correctly.
═══════════════════════════════════════════════════ */
function toggleTheme() {
  const isLight = document.getElementById('appBody').classList.toggle('lm');
  const val = isLight ? 'light' : 'dark';

  /* localStorage for same-session JS reads */
  localStorage.setItem('gg_theme', val);

  /* Cookie for server-side PHP cookie check (1 year) */
  document.cookie = `gg_theme=${val};path=/;max-age=31536000;SameSite=Lax`;

  /* Update button emoji */
  document.getElementById('themeBtn').textContent = isLight ? '☀️' : '🌙';

  showToast('Theme', isLight ? '☀️ Light mode on' : '🌙 Dark mode on', 'info', 2000);
}

/* On load: sync button to current state (in case server set it) */
(function(){
  const stored = localStorage.getItem('gg_theme') || '<?= $isLight ? "light" : "dark" ?>';
  const body   = document.getElementById('appBody');
  const btn    = document.getElementById('themeBtn');
  if (stored === 'light') { body.classList.add('lm'); if(btn) btn.textContent = '☀️'; }
  else                    { body.classList.remove('lm'); if(btn) btn.textContent = '🌙'; }
})();

/* ═══════════════════════════════════════════════════
   STAT COUNTER ANIMATION
═══════════════════════════════════════════════════ */
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

/* ═══════════════════════════════════════════════════
   PROFILE PROGRESS BAR
═══════════════════════════════════════════════════ */
setTimeout(() => {
  const f = document.getElementById('wbProgFill');
  if (f) f.style.width = '<?= $completeness ?>%';
}, 400);

/* ═══════════════════════════════════════════════════
   NOTIFICATION DROPDOWN
═══════════════════════════════════════════════════ */
function toggleNotifs() {
  document.getElementById('notifDrop').classList.toggle('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.notif-wrap'))
    document.getElementById('notifDrop')?.classList.remove('open');
});

function markAllRead() {
  fetch('<?= APP_URL ?>/api/notifications.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=mark_all_read&csrf=<?= $csrf ?>'
  }).then(() => {
    document.querySelectorAll('.nd-item.unread').forEach(el => el.classList.remove('unread'));
    const cnt = document.querySelector('.notif-count');
    if (cnt) cnt.remove();
    showToast('Done', 'All notifications marked as read.', 'success');
  });
}

/* ═══════════════════════════════════════════════════
   STAR RATING
═══════════════════════════════════════════════════ */
const starRatings = {};
function setStar(btn, val) {
  const group = btn.closest('.star-rating');
  const jobId = group.dataset.job;
  starRatings[jobId] = val;
  group.querySelectorAll('.star-btn').forEach((b, i) => b.classList.toggle('active', i < val));
}

function submitReview(jobId, providerId, csrf) {
  const rating  = starRatings[jobId] || 0;
  const comment = document.getElementById('rv_comment_' + jobId)?.value?.trim() || '';
  if (!rating) { showToast('Rating required', 'Please select a star rating first.', 'warning'); return; }
  const btn = event.target; btn.disabled = true; btn.textContent = '⏳';
  fetch('<?= APP_URL ?>/client/submit-review.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `job_id=${jobId}&provider_id=${providerId}&rating=${rating}&comment=${encodeURIComponent(comment)}&csrf_token=${csrf}`
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) {
      document.getElementById('rp_' + jobId)?.remove();
      showToast('Review submitted!', 'Thank you for rating your experience.', 'success');
    } else {
      showToast('Error', d.message || 'Could not submit.', 'error');
      btn.disabled = false; btn.textContent = 'Submit';
    }
  })
  .catch(() => { showToast('Error','Network error.','error'); btn.disabled=false; btn.textContent='Submit'; });
}

/* ═══════════════════════════════════════════════════
   TOAST
═══════════════════════════════════════════════════ */
const ICONS = { success:'✅', error:'❌', info:'ℹ️', warning:'⚠️' };
function showToast(title, msg, type = 'info', d = 4500) {
  const c = document.getElementById('toast-c');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<div class="t-ico">${ICONS[type]}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(() => {
    t.style.opacity = '0'; t.style.transform = 'translateX(50px)';
    t.style.transition = 'all .3s'; setTimeout(() => t.remove(), 360);
  }, d);
}

/* URL param toasts */
<?php if(isset($_GET['success'])): ?>showToast('Success','<?= addslashes(sanitize($_GET['success'])) ?>','success');<?php endif; ?>
<?php if(isset($_GET['error'])  ): ?>showToast('Error',  '<?= addslashes(sanitize($_GET['error']))   ?>','error');<?php endif; ?>
<?php if(isset($_GET['info'])   ): ?>showToast('Info',   '<?= addslashes(sanitize($_GET['info']))    ?>','info');<?php endif; ?>

/* Welcome toast */
<?php if(!isset($_GET['no_welcome'])): ?>
setTimeout(() => showToast(
  '<?= $greeting ?>, <?= sanitize($user["first_name"]) ?>! 🇬🇭',
  '<?= $activeJobs ?> active job<?= $activeJobs!=1?"s":"" ?> · <?= $totalProposals ?> proposal<?= $totalProposals!=1?"s":"" ?> · <?= $unreadMsgs ?> unread message<?= $unreadMsgs!=1?"s":"" ?>',
  'info', 5000
), 900);
<?php endif; ?>
</script>
</body>
</html>