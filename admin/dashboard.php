<?php
/**
 * GigGhana — admin/dashboard.php
 * World-class admin control centre
 *
 * FEATURES:
 *  1. Add Admins (name + email + password → stored in DB)
 *  2. User management: view, ban, delete, verify, award badges, premium verification
 *  3. Job management: view, delete, flag
 *  4. Deal/Job sealing tracker (chats that turned into accepted deals)
 *  5. Chat monitoring (admin can read conversations per deal)
 *  6. Job completion approvals (when provider marks done + client confirms)
 *  7. Admin profile: name, email, password, avatar
 *  8. Subscription/package badge verification (after free 3 jobs)
 *  9. No transaction management (removed as requested)
 * 10. Dark/light theme toggle persisted via cookie
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

$adminId   = (int)$_SESSION['user_id'];
$isLight   = ($_COOKIE['gg_theme'] ?? '') === 'light';

try {
    $db = getDB();

    /* ── PLATFORM STATS ── */
    $stats = [];
    $stats['total_users']     = (int)$db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
    $stats['total_clients']   = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn();
    $stats['total_providers'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='provider'")->fetchColumn();
    $stats['total_admins']    = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
    $stats['active_jobs']     = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE status IN ('open','in_progress')")->fetchColumn();
    $stats['completed_jobs']  = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE status='completed'")->fetchColumn();
    $stats['total_jobs']      = (int)$db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $stats['sealed_deals']    = (int)$db->query("SELECT COUNT(*) FROM proposals WHERE status='accepted'")->fetchColumn();
    $stats['pending_verif']   = (int)$db->query("SELECT COUNT(*) FROM users WHERE ghana_card_verified=0 AND ghana_card_number IS NOT NULL")->fetchColumn();
    $stats['pending_complete']= (int)$db->query("SELECT COUNT(*) FROM jobs WHERE status='pending_approval'")->fetchColumn();
    $stats['banned_users']    = (int)$db->query("SELECT COUNT(*) FROM users WHERE is_banned=1")->fetchColumn();
    $stats['free_job_alerts'] = (int)$db->query("SELECT COUNT(*) FROM users WHERE role='provider' AND free_jobs_used>=3 AND subscription_status='free'")->fetchColumn();

    /* ── ALL USERS ── */
    $stUsers = $db->prepare("
        SELECT u.*,
               (SELECT COUNT(*) FROM jobs WHERE client_id=u.id) AS posted_jobs,
               (SELECT COUNT(*) FROM proposals p JOIN providers pr ON pr.id=p.provider_id WHERE pr.user_id=u.id AND p.status='accepted') AS won_jobs,
               p.completed_jobs, p.rating_avg, p.is_verified AS prov_verified,
               p.subscription_status, p.free_jobs_used
        FROM users u
        LEFT JOIN providers p ON p.user_id = u.id
        WHERE u.role != 'admin'
        ORDER BY u.created_at DESC
        LIMIT 100
    ");
    $stUsers->execute(); $allUsers = $stUsers->fetchAll();

    /* ── ALL JOBS ── */
    $stJobs = $db->prepare("
        SELECT j.*,
               c.name AS cat_name,
               u.first_name AS client_fn, u.last_name AS client_ln,
               (SELECT COUNT(*) FROM proposals WHERE job_id=j.id) AS prop_count
        FROM jobs j
        LEFT JOIN categories c ON c.id = j.category_id
        LEFT JOIN users u ON u.id = j.client_id
        ORDER BY j.created_at DESC
        LIMIT 100
    ");
    $stJobs->execute(); $allJobs = $stJobs->fetchAll();

    /* ── SEALED DEALS (accepted proposals) ── */
    $stDeals = $db->prepare("
        SELECT p.*,
               j.title AS job_title, j.status AS job_status,
               uc.first_name AS client_fn, uc.last_name AS client_ln,
               up.first_name AS prov_fn,   up.last_name AS prov_ln,
               pr.id AS provider_row_id,
               c.id AS conv_id
        FROM proposals p
        JOIN jobs j         ON j.id   = p.job_id
        JOIN users uc       ON uc.id  = j.client_id
        JOIN providers pr   ON pr.id  = p.provider_id
        JOIN users up       ON up.id  = pr.user_id
        LEFT JOIN conversations c ON (
            (c.user1_id = uc.id AND c.user2_id = up.id) OR
            (c.user1_id = up.id AND c.user2_id = uc.id)
        )
        WHERE p.status = 'accepted'
        ORDER BY p.updated_at DESC
        LIMIT 80
    ");
    $stDeals->execute(); $sealedDeals = $stDeals->fetchAll();

    /* ── JOBS PENDING COMPLETION APPROVAL ── */
    $stPending = $db->prepare("
        SELECT j.*,
               uc.first_name AS client_fn, uc.last_name AS client_ln,
               up.first_name AS prov_fn,   up.last_name AS prov_ln
        FROM jobs j
        JOIN users uc ON uc.id = j.client_id
        JOIN users up ON up.id = j.hired_provider_id
        WHERE j.status = 'pending_approval'
        ORDER BY j.updated_at DESC
    ");
    $stPending->execute(); $pendingApprovals = $stPending->fetchAll();

    /* ── ADMINS LIST ── */
    $stAdmins = $db->prepare("SELECT id, first_name, last_name, email, avatar, created_at FROM users WHERE role='admin' ORDER BY created_at ASC");
    $stAdmins->execute(); $adminsList = $stAdmins->fetchAll();

    /* ── CURRENT ADMIN PROFILE ── */
    $adminUser = getUserById($adminId);

    /* ── PROVIDERS NEEDING SUBSCRIPTION PROMPT (used 3 free jobs) ── */
    $stFreeAlert = $db->prepare("
        SELECT u.*, p.free_jobs_used, p.subscription_status, p.completed_jobs
        FROM users u
        JOIN providers p ON p.user_id = u.id
        WHERE p.free_jobs_used >= 3 AND p.subscription_status = 'free'
        ORDER BY p.free_jobs_used DESC
        LIMIT 30
    ");
    $stFreeAlert->execute(); $freeJobAlerts = $stFreeAlert->fetchAll();

    /* ── RECENT CHAT MESSAGES (for a specific conv — loaded via AJAX) ── */

} catch(Exception $e) {
    error_log($e->getMessage());
    $stats = array_fill_keys(['total_users','total_clients','total_providers','total_admins','active_jobs','completed_jobs','total_jobs','sealed_deals','pending_verif','pending_complete','banned_users','free_job_alerts'], 0);
    $allUsers = $allJobs = $sealedDeals = $pendingApprovals = $adminsList = $freeJobAlerts = [];
    $adminUser = [];
}

$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>GigGhana — Admin Control Centre</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Syne:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ══════════════════════════════════════════════════════
   DESIGN DIRECTION: "Mission Control" — dark terminal
   aesthetic with electric accents. Feels like a
   satellite command room, not a SaaS dashboard.
   Font: Syne (headings) + Space Mono (data) + Inter (body)
   Palette: Deep space navy + Electric lime + Neon coral
══════════════════════════════════════════════════════ */

:root {
  --bg:   #080B12;
  --s1:   #0D1120;
  --s2:   #111827;
  --s3:   #1A2236;
  --s4:   #1F2A40;
  --glass: rgba(13,17,32,0.9);

  /* Accents */
  --lime:   #AAFF00;
  --lime-d: #88CC00;
  --lime-dim: rgba(170,255,0,0.08);
  --lime-border: rgba(170,255,0,0.2);

  --coral:   #FF4757;
  --coral-d: #CC2F3E;
  --coral-dim: rgba(255,71,87,0.08);
  --coral-border: rgba(255,71,87,0.2);

  --sky:   #00C8FF;
  --sky-d: #009FCC;
  --sky-dim: rgba(0,200,255,0.08);
  --sky-border: rgba(0,200,255,0.2);

  --amber: #FFB100;
  --amber-dim: rgba(255,177,0,0.08);

  --violet: #9B59F5;
  --violet-dim: rgba(155,89,245,0.08);

  --green:  #00E676;
  --green-dim: rgba(0,230,118,0.08);

  --tx:   #E8EDF8;
  --tx-2: #8896B0;
  --tx-3: #3D4A61;
  --bd:   rgba(255,255,255,0.055);
  --bd2:  rgba(255,255,255,0.11);

  --fm: 'Syne', sans-serif;
  --fc: 'Space Mono', monospace;
  --fb: 'Inter', sans-serif;

  --sb: 260px;
  --r: 14px;
  --rs: 10px;
  --e: all 0.24s cubic-bezier(.4,0,.2,1);
}

/* Light mode */
.lm {
  --bg: #F0F2F8; --s1: #E4E8F4; --s2: #DAE0F0;
  --s3: #CDD6EC; --s4: #C0CCEA;
  --glass: rgba(228,232,244,0.95);
  --lime: #5C8A00; --lime-d: #426500;
  --lime-dim: rgba(92,138,0,0.07); --lime-border: rgba(92,138,0,0.18);
  --coral: #D42035; --coral-d: #A81828;
  --coral-dim: rgba(212,32,53,0.07); --coral-border: rgba(212,32,53,0.16);
  --sky: #006FAA; --sky-d: #004D7A;
  --sky-dim: rgba(0,111,170,0.07); --sky-border: rgba(0,111,170,0.16);
  --amber: #B07600; --amber-dim: rgba(176,118,0,0.07);
  --violet: #6B3DD4; --violet-dim: rgba(107,61,212,0.07);
  --green: #007A3D; --green-dim: rgba(0,122,61,0.07);
  --tx: #0A0F1E; --tx-2: #3A4560; --tx-3: #7A88AA;
  --bd: rgba(0,0,0,0.07); --bd2: rgba(0,0,0,0.13);
}

*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
html { scroll-behavior:smooth; }
body {
  background:var(--bg); color:var(--tx);
  font-family:var(--fb); min-height:100vh;
  display:flex; font-size:13px;
  -webkit-font-smoothing:antialiased;
  transition:background .3s, color .3s;
}

/* Scanline texture overlay (subtle) */
body::before {
  content:'';
  position:fixed; inset:0; pointer-events:none; z-index:9999;
  background:repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.015) 2px, rgba(0,0,0,0.015) 4px);
}
.lm body::before { display:none; }

::-webkit-scrollbar { width:4px; height:4px; }
::-webkit-scrollbar-track { background:var(--bg); }
::-webkit-scrollbar-thumb { background:var(--s4); border-radius:2px; }
img { display:block; max-width:100%; }
a { text-decoration:none; color:inherit; }

h1,h2,h3,h4,.logo-text,.stat-val,.card-ttl,.section-title {
  font-family:var(--fm);
}
code, .mono { font-family:var(--fc); }

/* ── SIDEBAR ── */
.sidebar {
  width:var(--sb); min-height:100vh;
  background:var(--s1);
  border-right:1px solid var(--bd);
  position:fixed; top:0; left:0; z-index:200;
  display:flex; flex-direction:column;
  transition:background .3s, border-color .3s;
}
.sidebar::after {
  content:'';
  position:absolute; top:0; left:0; right:0; height:1px;
  background:linear-gradient(90deg,transparent,var(--lime),transparent);
}

.sb-logo {
  padding:20px 18px 16px;
  border-bottom:1px solid var(--bd);
  display:flex; align-items:center; gap:11px;
}
.sb-logo-mark {
  width:36px; height:36px; border-radius:9px;
  background:var(--lime); display:flex; align-items:center;
  justify-content:center; font-family:var(--fm); font-weight:800;
  font-size:17px; color:#080B12; flex-shrink:0;
}
.sb-logo-text { font-family:var(--fm); font-size:17px; font-weight:800; color:var(--tx); }
.sb-logo-text span { color:var(--lime); }
.sb-logo-badge {
  margin-left:auto; background:var(--coral-dim);
  border:1px solid var(--coral-border); color:var(--coral);
  font-family:var(--fc); font-size:9px; font-weight:700;
  padding:2px 7px; border-radius:4px; white-space:nowrap;
}

.sb-nav { flex:1; padding:10px; overflow-y:auto; scrollbar-width:none; }
.sb-nav::-webkit-scrollbar { display:none; }

.sb-section {
  font-family:var(--fc); font-size:8px; font-weight:700;
  letter-spacing:2px; text-transform:uppercase;
  color:var(--tx-3); padding:5px 11px; margin:14px 0 3px;
}
.sb-item {
  display:flex; align-items:center; gap:10px;
  padding:9px 11px; border-radius:8px;
  color:var(--tx-3); font-size:12.5px; font-weight:500;
  transition:var(--e); cursor:pointer; text-decoration:none;
  position:relative;
}
.sb-item:hover { background:rgba(255,255,255,0.04); color:var(--tx); }
.sb-item.active {
  background:var(--lime-dim); color:var(--lime);
  border-left:2px solid var(--lime); padding-left:9px;
}
.sb-item.danger { color:var(--coral); }
.sb-item.danger:hover { background:var(--coral-dim); }
.sb-badge {
  margin-left:auto; background:var(--coral);
  color:#fff; font-family:var(--fc);
  font-size:9px; font-weight:700;
  padding:2px 7px; border-radius:4px;
  min-width:20px; text-align:center;
}
.sb-badge.lime { background:var(--lime); color:#080B12; }
.sb-badge.amber { background:var(--amber); color:#080B12; }

.sb-user {
  padding:12px 10px;
  border-top:1px solid var(--bd);
}
.sb-user-card {
  display:flex; align-items:center; gap:10px;
  padding:9px 11px; border-radius:10px;
  background:rgba(0,0,0,0.25);
  cursor:pointer; transition:var(--e);
}
.sb-user-card:hover { background:rgba(0,0,0,0.35); }
.lm .sb-user-card { background:rgba(0,0,0,0.06); }
.lm .sb-user-card:hover { background:rgba(0,0,0,0.1); }
.sb-av {
  width:34px; height:34px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg,var(--lime),var(--sky));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-size:13px; font-weight:800;
  color:#080B12; overflow:hidden;
}
.sb-av img { width:100%; height:100%; object-fit:cover; }
.sb-uname { font-size:12.5px; font-weight:700; font-family:var(--fm); }
.sb-urole {
  font-family:var(--fc); font-size:9px; color:var(--lime);
  font-weight:700; text-transform:uppercase; margin-top:1px;
}

/* ── MAIN ── */
.main { margin-left:var(--sb); flex:1; display:flex; flex-direction:column; min-width:0; }

/* ── TOPBAR ── */
.topbar {
  display:flex; align-items:center; justify-content:space-between;
  padding:0 28px; height:60px;
  background:rgba(8,11,18,0.95); backdrop-filter:blur(20px);
  border-bottom:1px solid var(--bd);
  position:sticky; top:0; z-index:100;
  transition:background .3s, border-color .3s;
}
.lm .topbar { background:rgba(240,242,248,0.97); }
.topbar-left h1 { font-size:18px; font-weight:800; line-height:1.1; }
.topbar-left p { font-size:10.5px; color:var(--tx-3); font-family:var(--fc); margin-top:2px; }
.topbar-right { display:flex; align-items:center; gap:8px; }

/* Status dot */
.sys-status {
  display:flex; align-items:center; gap:6px;
  padding:5px 12px; border-radius:6px;
  background:var(--green-dim); border:1px solid rgba(0,230,118,0.2);
  font-family:var(--fc); font-size:10px; color:var(--green);
}
.sys-dot {
  width:7px; height:7px; border-radius:50%; background:var(--green);
  animation:pulse 2s infinite;
}
@keyframes pulse {
  0%,100% { box-shadow:0 0 0 0 rgba(0,230,118,.5); }
  50%      { box-shadow:0 0 0 4px rgba(0,230,118,0); }
}

/* ── BUTTONS ── */
.btn {
  display:inline-flex; align-items:center; gap:6px;
  padding:7px 14px; border-radius:var(--rs);
  font-family:var(--fb); font-size:12px; font-weight:600;
  cursor:pointer; border:none; transition:var(--e);
  white-space:nowrap; line-height:1.3; text-decoration:none;
}
.btn-lime   { background:var(--lime); color:#080B12; font-weight:700; }
.btn-lime:hover { background:var(--lime-d); transform:translateY(-1px); }
.btn-coral  { background:var(--coral); color:#fff; font-weight:700; }
.btn-coral:hover { background:var(--coral-d); transform:translateY(-1px); }
.btn-sky    { background:var(--sky); color:#080B12; font-weight:700; }
.btn-sky:hover { background:var(--sky-d); transform:translateY(-1px); }
.btn-ghost  { background:rgba(255,255,255,0.04); border:1px solid var(--bd); color:var(--tx-2); }
.btn-ghost:hover { background:rgba(255,255,255,0.08); color:var(--tx); }
.lm .btn-ghost { background:rgba(0,0,0,0.04); border-color:var(--bd2); }
.lm .btn-ghost:hover { background:rgba(0,0,0,0.08); }
.btn-red-soft { background:var(--coral-dim); border:1px solid var(--coral-border); color:var(--coral); }
.btn-red-soft:hover { background:rgba(255,71,87,0.15); }
.btn-amber-soft { background:var(--amber-dim); border:1px solid rgba(255,177,0,0.2); color:var(--amber); }
.btn-amber-soft:hover { background:rgba(255,177,0,0.14); }
.btn-green-soft { background:var(--green-dim); border:1px solid rgba(0,230,118,0.2); color:var(--green); }
.btn-green-soft:hover { background:rgba(0,230,118,0.14); }
.btn-sm { padding:4px 10px; font-size:11px; border-radius:7px; }
.btn-xs { padding:3px 8px; font-size:10px; border-radius:6px; }

/* ── THEME BTN ── */
.theme-btn {
  width:36px; height:36px; border-radius:8px;
  background:rgba(255,255,255,0.04); border:1px solid var(--bd);
  display:flex; align-items:center; justify-content:center;
  font-size:15px; cursor:pointer; transition:var(--e);
}
.theme-btn:hover { background:rgba(255,255,255,0.09); }
.lm .theme-btn { background:rgba(0,0,0,0.04); }
.lm .theme-btn:hover { background:rgba(0,0,0,0.09); }

/* ── CONTENT ── */
.content { padding:24px 28px 80px; }

/* ── TABS ── */
.tab-bar {
  display:flex; gap:2px; background:var(--s2);
  border:1px solid var(--bd); border-radius:12px;
  padding:4px; margin-bottom:26px; flex-wrap:wrap;
  transition:background .3s, border-color .3s;
}
.tab-btn {
  padding:8px 16px; border-radius:9px;
  font-family:var(--fm); font-size:12.5px; font-weight:700;
  cursor:pointer; border:none; background:transparent;
  color:var(--tx-3); transition:var(--e);
  display:flex; align-items:center; gap:7px; white-space:nowrap;
}
.tab-btn:hover { color:var(--tx); background:rgba(255,255,255,0.04); }
.tab-btn.active { background:var(--s4); color:var(--lime); }
.lm .tab-btn.active { background:var(--s3); }
.tab-count {
  background:var(--coral-dim); border:1px solid var(--coral-border);
  color:var(--coral); font-family:var(--fc); font-size:9px; font-weight:700;
  padding:1px 6px; border-radius:4px;
}
.tab-count.lime { background:var(--lime-dim); border-color:var(--lime-border); color:var(--lime); }

.tab-panel { display:none; }
.tab-panel.active { display:block; }

/* ── STATS GRID ── */
.stats-grid {
  display:grid; grid-template-columns:repeat(6,1fr);
  gap:14px; margin-bottom:24px;
}
.stat-card {
  background:var(--s2); border:1px solid var(--bd);
  border-radius:var(--r); padding:18px 16px;
  transition:var(--e); position:relative; overflow:hidden;
}
.stat-card::before {
  content:''; position:absolute; top:0; left:0; right:0; height:2px;
  opacity:0; transition:opacity .3s;
}
.stat-card:hover { transform:translateY(-3px); border-color:var(--bd2); }
.stat-card:hover::before { opacity:1; }
.stat-card.sc-lime::before { background:var(--lime); }
.stat-card.sc-coral::before { background:var(--coral); }
.stat-card.sc-sky::before { background:var(--sky); }
.stat-card.sc-amber::before { background:var(--amber); }
.stat-card.sc-violet::before { background:var(--violet); }
.stat-card.sc-green::before { background:var(--green); }
.stat-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
.stat-icon {
  width:36px; height:36px; border-radius:9px;
  display:flex; align-items:center; justify-content:center; font-size:16px;
}
.si-lime   { background:var(--lime-dim);   border:1px solid var(--lime-border); }
.si-coral  { background:var(--coral-dim);  border:1px solid var(--coral-border); }
.si-sky    { background:var(--sky-dim);    border:1px solid var(--sky-border); }
.si-amber  { background:var(--amber-dim);  border:1px solid rgba(255,177,0,0.2); }
.si-violet { background:var(--violet-dim); border:1px solid rgba(155,89,245,0.2); }
.si-green  { background:var(--green-dim);  border:1px solid rgba(0,230,118,0.2); }
.stat-val {
  font-family:var(--fc); font-size:26px; font-weight:700;
  line-height:1; margin-bottom:4px;
}
.stat-lbl { font-size:11px; color:var(--tx-3); }
.stat-delta {
  font-family:var(--fc); font-size:9px; font-weight:700;
  padding:2px 6px; border-radius:4px;
}
.sd-warn { background:var(--coral-dim); color:var(--coral); }
.sd-ok   { background:var(--green-dim); color:var(--green); }
.sd-info { background:var(--sky-dim);   color:var(--sky); }

/* ── SECTION CARD ── */
.section-card {
  background:var(--s2); border:1px solid var(--bd);
  border-radius:var(--r); overflow:hidden; margin-bottom:20px;
  transition:background .3s, border-color .3s;
}
.sc-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:16px 20px; border-bottom:1px solid var(--bd);
  gap:10px; flex-wrap:wrap;
}
.card-ttl { font-family:var(--fm); font-size:14px; font-weight:700; }
.card-cnt { font-size:10.5px; color:var(--tx-3); font-family:var(--fc); }
.sc-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }

/* ── SEARCH BAR ── */
.search-bar {
  display:flex; align-items:center; gap:0;
  background:var(--s3); border:1px solid var(--bd);
  border-radius:9px; overflow:hidden;
  transition:border-color .3s;
}
.search-bar:focus-within { border-color:var(--lime); }
.search-bar input {
  flex:1; background:transparent; border:none; outline:none;
  padding:7px 13px; font-family:var(--fb); font-size:12px;
  color:var(--tx);
}
.search-bar input::placeholder { color:var(--tx-3); }
.search-bar button {
  background:var(--lime-dim); border:none; padding:7px 12px;
  cursor:pointer; font-size:13px; color:var(--lime);
  transition:var(--e);
}
.search-bar button:hover { background:var(--lime-border); }

/* ── TABLE ── */
.data-table { width:100%; border-collapse:collapse; }
.data-table th {
  font-family:var(--fc); font-size:9px; font-weight:700;
  letter-spacing:1.5px; text-transform:uppercase; color:var(--tx-3);
  padding:10px 16px; text-align:left; border-bottom:1px solid var(--bd);
  background:var(--s3); transition:background .3s;
}
.data-table td {
  padding:12px 16px; border-bottom:1px solid var(--bd);
  font-size:12px; vertical-align:middle;
  transition:background .3s;
}
.data-table tr:last-child td { border-bottom:none; }
.data-table tr:hover td { background:rgba(255,255,255,0.02); }
.lm .data-table tr:hover td { background:rgba(0,0,0,0.03); }
.lm .data-table th { background:var(--s3); }

/* User avatar cell */
.u-cell { display:flex; align-items:center; gap:9px; }
.u-av {
  width:32px; height:32px; border-radius:50%;
  background:linear-gradient(135deg,var(--sky),var(--violet));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-size:11px; font-weight:800; color:#fff;
  flex-shrink:0; overflow:hidden;
}
.u-av img { width:100%; height:100%; object-fit:cover; }
.u-name { font-weight:600; font-size:12.5px; }
.u-email { font-size:10.5px; color:var(--tx-3); font-family:var(--fc); margin-top:1px; }

/* Pills */
.pill {
  display:inline-block; padding:3px 9px; border-radius:5px;
  font-family:var(--fc); font-size:10px; font-weight:700;
  white-space:nowrap;
}
.pill-client   { background:var(--sky-dim);    border:1px solid var(--sky-border);    color:var(--sky); }
.pill-provider { background:var(--violet-dim); border:1px solid rgba(155,89,245,0.2); color:var(--violet); }
.pill-admin    { background:var(--lime-dim);   border:1px solid var(--lime-border);   color:var(--lime); }
.pill-active   { background:var(--green-dim);  border:1px solid rgba(0,230,118,0.2);  color:var(--green); }
.pill-banned   { background:var(--coral-dim);  border:1px solid var(--coral-border);  color:var(--coral); }
.pill-open     { background:var(--sky-dim);    border:1px solid var(--sky-border);    color:var(--sky); }
.pill-completed{ background:var(--green-dim);  border:1px solid rgba(0,230,118,0.2);  color:var(--green); }
.pill-pending  { background:var(--amber-dim);  border:1px solid rgba(255,177,0,0.2);  color:var(--amber); }
.pill-verified { background:var(--green-dim);  border:1px solid rgba(0,230,118,0.2);  color:var(--green); }
.pill-unverif  { background:var(--coral-dim);  border:1px solid var(--coral-border);  color:var(--coral); }
.pill-free     { background:var(--amber-dim);  border:1px solid rgba(255,177,0,0.2);  color:var(--amber); }
.pill-premium  { background:var(--violet-dim); border:1px solid rgba(155,89,245,0.2); color:var(--violet); }

/* Badge icon */
.badge-ico { font-size:14px; }

/* Actions cell */
.actions-cell { display:flex; gap:5px; flex-wrap:wrap; }

/* ── DEAL ROWS ── */
.deal-row {
  display:flex; align-items:center; gap:14px;
  padding:14px 20px; border-bottom:1px solid var(--bd);
  transition:var(--e);
}
.deal-row:last-child { border-bottom:none; }
.deal-row:hover { background:rgba(255,255,255,0.02); }
.deal-icon {
  width:38px; height:38px; border-radius:10px; flex-shrink:0;
  background:var(--lime-dim); border:1px solid var(--lime-border);
  display:flex; align-items:center; justify-content:center; font-size:17px;
}
.deal-info { flex:1; min-width:0; }
.deal-title { font-family:var(--fm); font-weight:700; font-size:13px; margin-bottom:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.deal-meta { font-size:11px; color:var(--tx-3); display:flex; gap:10px; flex-wrap:wrap; }
.deal-status { flex-shrink:0; text-align:right; }
.deal-actions { display:flex; gap:5px; flex-shrink:0; flex-wrap:wrap; }

/* ── CHAT VIEWER ── */
.chat-viewer {
  display:none; position:fixed; inset:0; z-index:800;
  background:rgba(0,0,0,0.75); backdrop-filter:blur(8px);
  align-items:center; justify-content:center; padding:20px;
}
.chat-viewer.open { display:flex; }
.chat-modal {
  background:var(--s2); border:1px solid var(--bd2);
  border-radius:20px; width:100%; max-width:640px; max-height:80vh;
  display:flex; flex-direction:column; overflow:hidden;
  box-shadow:0 30px 80px rgba(0,0,0,0.6);
}
.chat-modal-head {
  padding:16px 20px; border-bottom:1px solid var(--bd);
  display:flex; align-items:center; justify-content:space-between;
  flex-shrink:0;
}
.chat-modal-title { font-family:var(--fm); font-weight:700; font-size:14px; }
.chat-modal-sub { font-family:var(--fc); font-size:10px; color:var(--tx-3); margin-top:2px; }
.chat-close-btn {
  width:32px; height:32px; border-radius:8px; border:1px solid var(--bd);
  background:rgba(255,255,255,0.04); cursor:pointer; font-size:16px;
  color:var(--tx-3); display:flex; align-items:center; justify-content:center;
  transition:var(--e);
}
.chat-close-btn:hover { color:var(--coral); border-color:var(--coral-border); background:var(--coral-dim); }
.chat-messages {
  flex:1; overflow-y:auto; padding:16px 20px;
  display:flex; flex-direction:column; gap:10px;
}
.chat-msg {
  display:flex; gap:9px; align-items:flex-start; max-width:85%;
}
.chat-msg.outgoing { flex-direction:row-reverse; align-self:flex-end; }
.chat-msg-av {
  width:30px; height:30px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg,var(--sky),var(--violet));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-size:10px; font-weight:800; color:#fff;
}
.chat-bubble {
  background:var(--s3); border:1px solid var(--bd); border-radius:12px;
  padding:9px 13px; font-size:12px; line-height:1.55;
  transition:background .3s;
}
.chat-msg.outgoing .chat-bubble { background:var(--lime-dim); border-color:var(--lime-border); color:var(--tx); }
.chat-msg-time { font-family:var(--fc); font-size:9px; color:var(--tx-3); margin-top:4px; text-align:right; }
.chat-admin-banner {
  background:var(--coral-dim); border:1px solid var(--coral-border);
  border-radius:8px; padding:8px 14px; margin:0 20px 12px;
  font-family:var(--fc); font-size:10px; color:var(--coral); text-align:center;
}

/* ── MODAL (generic) ── */
.modal-overlay {
  display:none; position:fixed; inset:0; z-index:700;
  background:rgba(0,0,0,0.65); backdrop-filter:blur(6px);
  align-items:center; justify-content:center; padding:20px;
}
.modal-overlay.open { display:flex; }
.modal-box {
  background:var(--s2); border:1px solid var(--bd2);
  border-radius:18px; width:100%; max-width:460px;
  box-shadow:0 24px 70px rgba(0,0,0,0.5);
  overflow:hidden; transition:background .3s;
}
.modal-head {
  padding:18px 22px; border-bottom:1px solid var(--bd);
  display:flex; justify-content:space-between; align-items:center;
}
.modal-title { font-family:var(--fm); font-size:15px; font-weight:800; }
.modal-close {
  width:30px; height:30px; border-radius:8px; border:1px solid var(--bd);
  background:rgba(255,255,255,0.04); cursor:pointer; font-size:15px;
  color:var(--tx-3); display:flex; align-items:center; justify-content:center;
  transition:var(--e);
}
.modal-close:hover { color:var(--coral); background:var(--coral-dim); }
.modal-body { padding:22px; }
.form-group { margin-bottom:16px; }
.form-label {
  display:block; font-family:var(--fc); font-size:10px; font-weight:700;
  letter-spacing:1px; text-transform:uppercase; color:var(--tx-3); margin-bottom:7px;
}
.form-input {
  width:100%; background:var(--s3); border:1px solid var(--bd);
  border-radius:9px; padding:10px 13px; color:var(--tx);
  font-family:var(--fb); font-size:13px; outline:none;
  transition:border-color .3s;
}
.form-input:focus { border-color:var(--lime); }
.form-input::placeholder { color:var(--tx-3); }
.lm .form-input { background:rgba(0,0,0,0.05); }
.form-select {
  width:100%; background:var(--s3); border:1px solid var(--bd);
  border-radius:9px; padding:10px 13px; color:var(--tx);
  font-family:var(--fb); font-size:13px; outline:none; cursor:pointer;
  transition:border-color .3s;
}
.form-select:focus { border-color:var(--lime); }
.lm .form-select { background:rgba(0,0,0,0.05); }
.modal-footer {
  padding:16px 22px; border-top:1px solid var(--bd);
  display:flex; gap:10px; justify-content:flex-end;
}

/* ── ADMIN PROFILE TAB ── */
.profile-grid {
  display:grid; grid-template-columns:280px 1fr;
  gap:20px;
}
.profile-card {
  background:var(--s2); border:1px solid var(--bd);
  border-radius:var(--r); padding:28px 22px; text-align:center;
}
.profile-av-wrap {
  position:relative; width:90px; height:90px;
  margin:0 auto 14px;
}
.profile-av {
  width:90px; height:90px; border-radius:50%;
  background:linear-gradient(135deg,var(--lime),var(--sky));
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-size:28px; font-weight:800;
  color:#080B12; overflow:hidden; border:3px solid var(--lime);
}
.profile-av img { width:100%; height:100%; object-fit:cover; }
.profile-av-edit {
  position:absolute; bottom:0; right:0;
  width:28px; height:28px; border-radius:50%;
  background:var(--lime); border:2px solid var(--s2);
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; font-size:12px; transition:var(--e);
}
.profile-av-edit:hover { transform:scale(1.15); }
.profile-name { font-family:var(--fm); font-size:18px; font-weight:800; margin-bottom:4px; }
.profile-email { font-family:var(--fc); font-size:11px; color:var(--tx-3); margin-bottom:16px; }
.profile-badge {
  background:var(--lime-dim); border:1px solid var(--lime-border);
  color:var(--lime); font-family:var(--fc); font-size:10px; font-weight:700;
  padding:4px 14px; border-radius:6px; display:inline-block; margin-bottom:16px;
}
.profile-stats {
  display:grid; grid-template-columns:1fr 1fr; gap:10px; text-align:center;
}
.ps-item {
  background:var(--s3); border-radius:9px; padding:12px 8px;
}
.ps-val { font-family:var(--fc); font-size:18px; font-weight:700; color:var(--lime); }
.ps-lbl { font-size:10px; color:var(--tx-3); margin-top:2px; }

/* ── FREE JOBS ALERT ── */
.free-alert-row {
  display:flex; align-items:center; gap:12px;
  padding:12px 20px; border-bottom:1px solid var(--bd);
  transition:var(--e);
}
.free-alert-row:last-child { border-bottom:none; }
.free-alert-row:hover { background:rgba(255,177,0,0.03); }
.free-progress-track {
  width:80px; height:6px; background:var(--s4);
  border-radius:3px; overflow:hidden; flex-shrink:0;
}
.free-progress-fill {
  height:100%; border-radius:3px;
  background:linear-gradient(90deg,var(--amber),var(--coral));
}

/* ── EMPTY STATE ── */
.empty-state {
  text-align:center; padding:48px 20px; color:var(--tx-3);
}
.empty-state .es-ico { font-size:36px; margin-bottom:10px; }
.empty-state .es-ttl { font-family:var(--fm); font-size:15px; font-weight:700; margin-bottom:4px; color:var(--tx-2); }
.empty-state .es-sub { font-size:12px; }

/* ── DIVIDER ── */
.divider { height:1px; background:var(--bd); }

/* ── TOAST ── */
#toast-c {
  position:fixed; bottom:20px; right:20px; z-index:9999;
  display:flex; flex-direction:column; gap:8px;
}
.toast {
  display:flex; align-items:center; gap:10px;
  background:var(--s2); border:1px solid var(--bd);
  padding:12px 15px; border-radius:var(--rs);
  max-width:320px; min-width:230px;
  box-shadow:0 12px 36px rgba(0,0,0,.5);
  animation:toastIn .3s ease; backdrop-filter:blur(14px);
  transition:background .3s;
}
.toast.success { border-left:3px solid var(--green); }
.toast.error   { border-left:3px solid var(--coral); }
.toast.info    { border-left:3px solid var(--sky); }
.toast.warning { border-left:3px solid var(--amber); }
.t-ico { font-size:16px; flex-shrink:0; }
.t-bod { flex:1; }
.t-ttl { font-family:var(--fm); font-weight:700; font-size:12px; margin-bottom:1px; }
.t-msg { font-size:11px; color:var(--tx-3); }
.t-cls { cursor:pointer; color:var(--tx-3); font-size:16px; flex-shrink:0; }
@keyframes toastIn { from{opacity:0;transform:translateX(40px);}to{opacity:1;transform:translateX(0);} }

/* ── COMPLETION APPROVAL ROWS ── */
.approval-row {
  display:flex; align-items:center; gap:14px;
  padding:14px 20px; border-bottom:1px solid var(--bd);
  transition:var(--e);
}
.approval-row:last-child { border-bottom:none; }
.approval-row:hover { background:rgba(255,255,255,0.02); }
.approval-icon {
  width:40px; height:40px; border-radius:10px; flex-shrink:0;
  background:var(--amber-dim); border:1px solid rgba(255,177,0,0.2);
  display:flex; align-items:center; justify-content:center; font-size:18px;
}

/* ── RESPONSIVE ── */
@media(max-width:1400px) { .stats-grid { grid-template-columns:repeat(3,1fr); } }
@media(max-width:1100px) { .stats-grid { grid-template-columns:repeat(2,1fr); } }
@media(max-width:1024px) { .profile-grid { grid-template-columns:1fr; } }
@media(max-width:768px)  {
  .sidebar { display:none; }
  .main { margin-left:0; }
  .content { padding:16px 14px 60px; }
  .topbar { padding:0 16px; }
  .tab-bar { overflow-x:auto; flex-wrap:nowrap; }
  .stats-grid { grid-template-columns:1fr 1fr; }
}
@media(max-width:480px) { .stats-grid { grid-template-columns:1fr; } }
</style>
</head>

<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar">
  <a href="<?= APP_URL ?>/admin/dashboard.php" class="sb-logo">
    <div class="sb-logo-mark">G</div>
    <span class="sb-logo-text">Gig<span>Ghana</span></span>
    <span class="sb-logo-badge">ADMIN</span>
  </a>

  <nav class="sb-nav">
    <div class="sb-section">Overview</div>
    <a href="#" class="sb-item active" onclick="switchTab('overview',this)">🛰 Mission Control</a>

    <div class="sb-section">People</div>
    <a href="#" class="sb-item" onclick="switchTab('users',this)">
      👥 Manage Users
      <?php if($stats['pending_verif'] > 0): ?><span class="sb-badge amber"><?= $stats['pending_verif'] ?></span><?php endif; ?>
    </a>
    <a href="#" class="sb-item" onclick="switchTab('admins',this)">
      🔑 Manage Admins
      <span class="sb-badge lime"><?= $stats['total_admins'] ?></span>
    </a>

    <div class="sb-section">Work</div>
    <a href="#" class="sb-item" onclick="switchTab('jobs',this)">
      💼 Jobs
      <span class="sb-badge lime"><?= $stats['total_jobs'] ?></span>
    </a>
    <a href="#" class="sb-item" onclick="switchTab('deals',this)">
      🤝 Sealed Deals
      <span class="sb-badge lime"><?= $stats['sealed_deals'] ?></span>
    </a>
    <a href="#" class="sb-item" onclick="switchTab('approvals',this)">
      ✅ Completions
      <?php if($stats['pending_complete'] > 0): ?><span class="sb-badge"><?= $stats['pending_complete'] ?></span><?php endif; ?>
    </a>

    <div class="sb-section">Subscriptions</div>
    <a href="#" class="sb-item" onclick="switchTab('subscriptions',this)">
      💳 Free Limits
      <?php if($stats['free_job_alerts'] > 0): ?><span class="sb-badge amber"><?= $stats['free_job_alerts'] ?></span><?php endif; ?>
    </a>

    <div class="sb-section">Account</div>
    <a href="#" class="sb-item" onclick="switchTab('profile',this)">🧑‍💼 My Profile</a>
    <a href="<?= APP_URL ?>/index.php" class="sb-item">🏠 Homepage</a>
    <a href="<?= APP_URL ?>/auth/logout.php" class="sb-item danger">🚪 Sign Out</a>
  </nav>

  <div class="sb-user" onclick="switchTab('profile',null)">
    <div class="sb-user-card">
      <div class="sb-av">
        <?php if(!empty($adminUser['avatar'])): ?>
          <img src="<?= sanitize($adminUser['avatar']) ?>" alt="">
        <?php else:
          echo strtoupper(substr($adminUser['first_name']??'A',0,1).substr($adminUser['last_name']??'D',0,1));
        endif; ?>
      </div>
      <div>
        <div class="sb-uname"><?= sanitize(($adminUser['first_name']??'Admin').' '.($adminUser['last_name']??'')) ?></div>
        <div class="sb-urole">Administrator</div>
      </div>
    </div>
  </div>
</aside>

<!-- ══════════ MAIN ══════════ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-left">
      <h1>GigGhana Admin</h1>
      <p><?= date('D, M j Y · H:i') ?> WAT</p>
    </div>
    <div class="topbar-right">
      <div class="sys-status">
        <div class="sys-dot"></div>
        System Online
      </div>
      <button class="theme-btn" id="themeBtn" onclick="toggleTheme()">
        <?= $isLight ? '☀️' : '🌙' ?>
      </button>
      <a href="<?= APP_URL ?>/auth/logout.php" class="btn btn-red-soft btn-sm">🚪 Logout</a>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- TAB BAR -->
    <div class="tab-bar">
      <button class="tab-btn active" id="tabbtn-overview"    onclick="switchTab('overview',this)">🛰 Overview</button>
      <button class="tab-btn" id="tabbtn-users"         onclick="switchTab('users',this)">
        👥 Users
        <?php if($stats['pending_verif']>0): ?><span class="tab-count"><?= $stats['pending_verif'] ?></span><?php endif; ?>
      </button>
      <button class="tab-btn" id="tabbtn-admins"        onclick="switchTab('admins',this)">🔑 Admins</button>
      <button class="tab-btn" id="tabbtn-jobs"          onclick="switchTab('jobs',this)">💼 Jobs</button>
      <button class="tab-btn" id="tabbtn-deals"         onclick="switchTab('deals',this)">🤝 Deals</button>
      <button class="tab-btn" id="tabbtn-approvals"     onclick="switchTab('approvals',this)">
        ✅ Completions
        <?php if($stats['pending_complete']>0): ?><span class="tab-count coral"><?= $stats['pending_complete'] ?></span><?php endif; ?>
      </button>
      <button class="tab-btn" id="tabbtn-subscriptions" onclick="switchTab('subscriptions',this)">
        💳 Free Limits
        <?php if($stats['free_job_alerts']>0): ?><span class="tab-count"><?= $stats['free_job_alerts'] ?></span><?php endif; ?>
      </button>
      <button class="tab-btn" id="tabbtn-profile"       onclick="switchTab('profile',this)">🧑‍💼 My Profile</button>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 1: OVERVIEW / MISSION CONTROL
    ════════════════════════════════════════════════ -->
    <div class="tab-panel active" id="tab-overview">

      <!-- STATS -->
      <div class="stats-grid">
        <div class="stat-card sc-lime">
          <div class="stat-top">
            <div class="stat-icon si-lime">👥</div>
            <span class="stat-delta sd-info">All</span>
          </div>
          <div class="stat-val mono" data-count="<?= $stats['total_users'] ?>"><?= $stats['total_users'] ?></div>
          <div class="stat-lbl">Total Users</div>
        </div>
        <div class="stat-card sc-sky">
          <div class="stat-top">
            <div class="stat-icon si-sky">🏢</div>
          </div>
          <div class="stat-val mono" data-count="<?= $stats['total_clients'] ?>"><?= $stats['total_clients'] ?></div>
          <div class="stat-lbl">Clients</div>
        </div>
        <div class="stat-card sc-violet">
          <div class="stat-top">
            <div class="stat-icon si-violet">🎯</div>
          </div>
          <div class="stat-val mono" data-count="<?= $stats['total_providers'] ?>"><?= $stats['total_providers'] ?></div>
          <div class="stat-lbl">Providers</div>
        </div>
        <div class="stat-card sc-amber">
          <div class="stat-top">
            <div class="stat-icon si-amber">💼</div>
            <span class="stat-delta sd-ok">Live</span>
          </div>
          <div class="stat-val mono" data-count="<?= $stats['active_jobs'] ?>"><?= $stats['active_jobs'] ?></div>
          <div class="stat-lbl">Active Jobs</div>
        </div>
        <div class="stat-card sc-green">
          <div class="stat-top">
            <div class="stat-icon si-green">🤝</div>
          </div>
          <div class="stat-val mono" data-count="<?= $stats['sealed_deals'] ?>"><?= $stats['sealed_deals'] ?></div>
          <div class="stat-lbl">Sealed Deals</div>
        </div>
        <div class="stat-card sc-coral">
          <div class="stat-top">
            <div class="stat-icon si-coral">⚠️</div>
            <?php if($stats['banned_users']>0): ?><span class="stat-delta sd-warn"><?= $stats['banned_users'] ?> banned</span><?php endif; ?>
          </div>
          <div class="stat-val mono" data-count="<?= $stats['pending_verif'] ?>"><?= $stats['pending_verif'] ?></div>
          <div class="stat-lbl">Pending Verifications</div>
        </div>
      </div>

      <!-- QUICK OVERVIEW CARDS -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;">

        <!-- Recent Users -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">🆕 Recent Sign-ups</div>
              <div class="card-cnt">Latest <?= min(count($allUsers),5) ?> users</div>
            </div>
            <button class="btn btn-ghost btn-sm" onclick="switchTab('users',null)">View All</button>
          </div>
          <?php if(empty($allUsers)): ?>
          <div class="empty-state"><div class="es-ico">👤</div><div class="es-sub">No users yet.</div></div>
          <?php else: foreach(array_slice($allUsers,0,5) as $u):
            $init = strtoupper(substr($u['first_name']??'',0,1).substr($u['last_name']??'',0,1));
          ?>
          <div style="display:flex;align-items:center;gap:11px;padding:11px 20px;border-bottom:1px solid var(--bd);transition:var(--e);" class="hover-row">
            <div class="u-av">
              <?php if(!empty($u['avatar'])): ?><img src="<?= sanitize($u['avatar']) ?>" alt=""><?php else: echo $init; endif; ?>
            </div>
            <div style="flex:1;min-width:0;">
              <div class="u-name"><?= sanitize($u['first_name'].' '.$u['last_name']) ?></div>
              <div class="u-email"><?= sanitize($u['email']) ?></div>
            </div>
            <span class="pill pill-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span>
            <?php if($u['is_banned']??0): ?><span class="pill pill-banned">Banned</span><?php endif; ?>
          </div>
          <?php endforeach; endif; ?>
        </div>

        <!-- Pending Completions Snapshot -->
        <div class="section-card">
          <div class="sc-head">
            <div>
              <div class="card-ttl">⏳ Awaiting Completion Approval</div>
              <div class="card-cnt"><?= count($pendingApprovals) ?> job<?= count($pendingApprovals)!=1?'s':'' ?></div>
            </div>
            <button class="btn btn-amber-soft btn-sm" onclick="switchTab('approvals',null)">Review</button>
          </div>
          <?php if(empty($pendingApprovals)): ?>
          <div class="empty-state"><div class="es-ico">✅</div><div class="es-ttl">All clear!</div><div class="es-sub">No jobs pending your approval.</div></div>
          <?php else: foreach(array_slice($pendingApprovals,0,4) as $j): ?>
          <div class="deal-row">
            <div class="approval-icon">⏳</div>
            <div class="deal-info">
              <div class="deal-title"><?= sanitize($j['title']) ?></div>
              <div class="deal-meta">
                <span>Client: <?= sanitize($j['client_fn'].' '.$j['client_ln']) ?></span>
                <span>Provider: <?= sanitize($j['prov_fn'].' '.$j['prov_ln']) ?></span>
              </div>
            </div>
            <form method="POST" action="<?= APP_URL ?>/admin/approve-job.php">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="job_id"    value="<?= $j['id'] ?>">
              <button type="submit" class="btn btn-green-soft btn-sm">Approve</button>
            </form>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 2: USERS
    ════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-users">
      <div class="section-card">
        <div class="sc-head">
          <div>
            <div class="card-ttl">👥 All Users</div>
            <div class="card-cnt"><?= count($allUsers) ?> users loaded</div>
          </div>
          <div class="sc-actions">
            <div class="search-bar">
              <input type="text" id="userSearch" placeholder="Search name, email…" oninput="filterTable('userTable',this.value)">
              <button>🔍</button>
            </div>
            <select class="form-select" style="width:130px;padding:7px 11px;" onchange="filterByRole(this.value)">
              <option value="">All Roles</option>
              <option value="client">Clients</option>
              <option value="provider">Providers</option>
            </select>
          </div>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table" id="userTable">
            <thead>
              <tr>
                <th>User</th>
                <th>Role</th>
                <th>Status</th>
                <th>Ghana Card</th>
                <th>Jobs</th>
                <th>Rating</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($allUsers)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--tx-3);">No users found.</td></tr>
              <?php else: foreach($allUsers as $u):
                $init = strtoupper(substr($u['first_name']??'',0,1).substr($u['last_name']??'',0,1));
                $isBanned = (bool)($u['is_banned']??0);
                $gcVerified = (bool)($u['ghana_card_verified']??0);
              ?>
              <tr data-role="<?= $u['role'] ?>">
                <td>
                  <div class="u-cell">
                    <div class="u-av">
                      <?php if(!empty($u['avatar'])): ?><img src="<?= sanitize($u['avatar']) ?>" alt=""><?php else: echo $init; endif; ?>
                    </div>
                    <div>
                      <div class="u-name"><?= sanitize($u['first_name'].' '.$u['last_name']) ?></div>
                      <div class="u-email"><?= sanitize($u['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td><span class="pill pill-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                <td>
                  <?php if($isBanned): ?>
                    <span class="pill pill-banned">Banned</span>
                  <?php else: ?>
                    <span class="pill pill-active">Active</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($gcVerified): ?>
                    <span class="pill pill-verified">✓ Verified</span>
                  <?php elseif(!empty($u['ghana_card_number'])): ?>
                    <span class="pill pill-pending">Pending</span>
                  <?php else: ?>
                    <span style="color:var(--tx-3);font-size:11px;">—</span>
                  <?php endif; ?>
                </td>
                <td class="mono" style="color:var(--lime);">
                  <?= $u['role']==='client' ? ($u['posted_jobs']??0).' posted' : ($u['completed_jobs']??0).' done' ?>
                </td>
                <td>
                  <?php if($u['role']==='provider' && !empty($u['rating_avg'])): ?>
                    <span style="color:var(--amber);">★</span> <?= number_format((float)$u['rating_avg'],1) ?>
                  <?php else: ?>
                    <span style="color:var(--tx-3);">—</span>
                  <?php endif; ?>
                </td>
                <td style="color:var(--tx-3);font-family:var(--fc);font-size:10.5px;"><?= date('M j, Y',strtotime($u['created_at'])) ?></td>
                <td>
                  <div class="actions-cell">
                    <!-- Verify Ghana Card -->
                    <?php if(!$gcVerified && !empty($u['ghana_card_number'])): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/verify-user.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="user_id"    value="<?= $u['id'] ?>">
                      <input type="hidden" name="action"     value="verify_ghana_card">
                      <button type="submit" class="btn btn-green-soft btn-xs">✓ Verify</button>
                    </form>
                    <?php endif; ?>
                    <!-- Badge -->
                    <button class="btn btn-amber-soft btn-xs" onclick="openBadgeModal(<?= $u['id'] ?>,'<?= sanitize($u['first_name'].' '.$u['last_name']) ?>')">🏅 Badge</button>
                    <!-- Premium verify -->
                    <form method="POST" action="<?= APP_URL ?>/admin/verify-user.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="user_id"    value="<?= $u['id'] ?>">
                      <input type="hidden" name="action"     value="grant_premium">
                      <button type="submit" class="btn btn-ghost btn-xs" title="Grant premium badge after payment confirmation">⭐ Premium</button>
                    </form>
                    <!-- Ban / Unban -->
                    <form method="POST" action="<?= APP_URL ?>/admin/manage-user.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="user_id"    value="<?= $u['id'] ?>">
                      <input type="hidden" name="action"     value="<?= $isBanned ? 'unban' : 'ban' ?>">
                      <button type="submit" class="btn <?= $isBanned ? 'btn-green-soft' : 'btn-amber-soft' ?> btn-xs"
                        onclick="return confirm('<?= $isBanned ? 'Unban' : 'Ban' ?> this user?')">
                        <?= $isBanned ? '✓ Unban' : '🚫 Ban' ?>
                      </button>
                    </form>
                    <!-- Delete -->
                    <form method="POST" action="<?= APP_URL ?>/admin/manage-user.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="user_id"    value="<?= $u['id'] ?>">
                      <input type="hidden" name="action"     value="delete">
                      <button type="submit" class="btn btn-red-soft btn-xs"
                        onclick="return confirm('PERMANENTLY delete this user? This cannot be undone.')">🗑 Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 3: ADMINS
    ════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-admins">
      <div class="section-card">
        <div class="sc-head">
          <div>
            <div class="card-ttl">🔑 Admin Accounts</div>
            <div class="card-cnt"><?= count($adminsList) ?> admin<?= count($adminsList)!=1?'s':'' ?></div>
          </div>
          <button class="btn btn-lime" onclick="openModal('addAdminModal')">+ Add Admin</button>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>Admin</th>
                <th>Email</th>
                <th>Added</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($adminsList)): ?>
              <tr><td colspan="4" style="text-align:center;padding:30px;color:var(--tx-3);">No admins found.</td></tr>
              <?php else: foreach($adminsList as $adm):
                $ainit = strtoupper(substr($adm['first_name']??'',0,1).substr($adm['last_name']??'',0,1));
              ?>
              <tr>
                <td>
                  <div class="u-cell">
                    <div class="u-av" style="background:linear-gradient(135deg,var(--lime),var(--sky));">
                      <?php if(!empty($adm['avatar'])): ?><img src="<?= sanitize($adm['avatar']) ?>" alt=""><?php else: echo $ainit; endif; ?>
                    </div>
                    <div>
                      <div class="u-name"><?= sanitize($adm['first_name'].' '.$adm['last_name']) ?></div>
                      <?php if($adm['id'] == $adminId): ?>
                        <span style="font-family:var(--fc);font-size:9px;color:var(--lime);">YOU</span>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td style="font-family:var(--fc);font-size:11px;color:var(--tx-2);"><?= sanitize($adm['email']) ?></td>
                <td style="font-family:var(--fc);font-size:11px;color:var(--tx-3);"><?= date('M j, Y',strtotime($adm['created_at'])) ?></td>
                <td>
                  <div class="actions-cell">
                    <?php if($adm['id'] != $adminId): ?>
                    <form method="POST" action="<?= APP_URL ?>/admin/manage-admin.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="admin_id"   value="<?= $adm['id'] ?>">
                      <input type="hidden" name="action"     value="delete">
                      <button type="submit" class="btn btn-red-soft btn-xs"
                        onclick="return confirm('Remove this admin account? They will lose all access.')">🗑 Remove</button>
                    </form>
                    <?php else: ?>
                    <button class="btn btn-ghost btn-xs" onclick="switchTab('profile',null)">Edit Profile →</button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 4: JOBS
    ════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-jobs">
      <div class="section-card">
        <div class="sc-head">
          <div>
            <div class="card-ttl">💼 All Jobs</div>
            <div class="card-cnt"><?= count($allJobs) ?> jobs</div>
          </div>
          <div class="sc-actions">
            <div class="search-bar">
              <input type="text" placeholder="Search jobs…" oninput="filterTable('jobTable',this.value)">
              <button>🔍</button>
            </div>
          </div>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table" id="jobTable">
            <thead>
              <tr>
                <th>Job Title</th>
                <th>Client</th>
                <th>Category</th>
                <th>Budget</th>
                <th>Proposals</th>
                <th>Status</th>
                <th>Posted</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($allJobs)): ?>
              <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--tx-3);">No jobs yet.</td></tr>
              <?php else: foreach($allJobs as $j): ?>
              <tr>
                <td style="max-width:220px;">
                  <div class="u-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize($j['title']) ?></div>
                  <?php if($j['is_urgent']??0): ?><span class="pill" style="background:var(--coral-dim);color:var(--coral);border-color:var(--coral-border);">🔥 Urgent</span><?php endif; ?>
                </td>
                <td style="font-size:12px;"><?= sanitize($j['client_fn'].' '.$j['client_ln']) ?></td>
                <td style="color:var(--tx-3);font-size:11.5px;"><?= sanitize($j['cat_name']??'—') ?></td>
                <td style="font-family:var(--fc);color:var(--lime);font-size:12px;">
                  <?= formatCurrency($j['budget_min']) ?><?= ($j['budget_max']??0)>($j['budget_min']??0)?' – '.formatCurrency($j['budget_max']):'' ?>
                </td>
                <td style="font-family:var(--fc);text-align:center;"><?= $j['prop_count']??0 ?></td>
                <td>
                  <?php
                  $st = $j['status']??'open';
                  $stCls = match($st) {
                    'open'=>'pill-open','in_progress'=>'pill-pending',
                    'completed'=>'pill-completed','cancelled'=>'pill-banned',
                    default=>'pill-pending'
                  };
                  ?>
                  <span class="pill <?= $stCls ?>"><?= ucfirst(str_replace('_',' ',$st)) ?></span>
                </td>
                <td style="font-family:var(--fc);font-size:10.5px;color:var(--tx-3);"><?= timeAgo($j['created_at']) ?></td>
                <td>
                  <div class="actions-cell">
                    <form method="POST" action="<?= APP_URL ?>/admin/manage-job.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="job_id"     value="<?= $j['id'] ?>">
                      <input type="hidden" name="action"     value="delete">
                      <button type="submit" class="btn btn-red-soft btn-xs"
                        onclick="return confirm('Delete this job permanently?')">🗑 Delete</button>
                    </form>
                    <form method="POST" action="<?= APP_URL ?>/admin/manage-job.php" style="display:inline;">
                      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                      <input type="hidden" name="job_id"     value="<?= $j['id'] ?>">
                      <input type="hidden" name="action"     value="flag">
                      <button type="submit" class="btn btn-amber-soft btn-xs">🚩 Flag</button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 5: SEALED DEALS (accepted proposals)
    ════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-deals">
      <div class="section-card">
        <div class="sc-head">
          <div>
            <div class="card-ttl">🤝 Sealed Deals</div>
            <div class="card-cnt"><?= count($sealedDeals) ?> accepted proposals — admin can monitor chats</div>
          </div>
        </div>

        <?php if(empty($sealedDeals)): ?>
        <div class="empty-state">
          <div class="es-ico">🤝</div>
          <div class="es-ttl">No sealed deals yet</div>
          <div class="es-sub">When a client accepts a proposal, it will appear here for monitoring.</div>
        </div>
        <?php else: foreach($sealedDeals as $deal):
          $jobStatus = $deal['job_status']??'in_progress';
          $stCls = match($jobStatus) {
            'completed'=>'pill-completed','in_progress'=>'pill-pending',
            'pending_approval'=>'pill-pending',default=>'pill-open'
          };
        ?>
        <div class="deal-row">
          <div class="deal-icon">🤝</div>
          <div class="deal-info">
            <div class="deal-title"><?= sanitize($deal['job_title']) ?></div>
            <div class="deal-meta">
              <span>👤 Client: <?= sanitize($deal['client_fn'].' '.$deal['client_ln']) ?></span>
              <span>🎯 Provider: <?= sanitize($deal['prov_fn'].' '.$deal['prov_ln']) ?></span>
              <span>Bid: <?= formatCurrency($deal['bid_amount']) ?></span>
              <span style="font-family:var(--fc);font-size:10px;color:var(--tx-3);">Sealed <?= timeAgo($deal['updated_at']) ?></span>
            </div>
          </div>
          <div class="deal-status">
            <span class="pill <?= $stCls ?>"><?= ucfirst(str_replace('_',' ',$jobStatus)) ?></span>
          </div>
          <div class="deal-actions">
            <?php if(!empty($deal['conv_id'])): ?>
            <button class="btn btn-sky btn-sm"
              onclick="openChatViewer(<?= $deal['conv_id'] ?>,'<?= sanitize($deal['job_title']) ?>','<?= sanitize($deal['client_fn'].' '.$deal['client_ln']) ?>','<?= sanitize($deal['prov_fn'].' '.$deal['prov_ln']) ?>')">
              💬 Read Chat
            </button>
            <?php else: ?>
            <span style="font-size:11px;color:var(--tx-3);font-style:italic;">No chat yet</span>
            <?php endif; ?>
            <?php if($jobStatus === 'in_progress' || $jobStatus === 'pending_approval'): ?>
            <form method="POST" action="<?= APP_URL ?>/admin/approve-job.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="job_id"     value="<?= $deal['job_id'] ?>">
              <button type="submit" class="btn btn-green-soft btn-sm">✅ Mark Complete</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 6: COMPLETION APPROVALS
    ════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-approvals">
      <div class="section-card">
        <div class="sc-head">
          <div>
            <div class="card-ttl">✅ Jobs Pending Completion Approval</div>
            <div class="card-cnt">Provider has marked done · client may have confirmed · awaiting admin sign-off</div>
          </div>
        </div>
        <?php if(empty($pendingApprovals)): ?>
        <div class="empty-state">
          <div class="es-ico">🎉</div>
          <div class="es-ttl">All clear!</div>
          <div class="es-sub">No jobs are waiting for your approval right now.</div>
        </div>
        <?php else: foreach($pendingApprovals as $j): ?>
        <div class="approval-row">
          <div class="approval-icon">⏳</div>
          <div class="deal-info" style="flex:1;min-width:0;">
            <div class="deal-title"><?= sanitize($j['title']) ?></div>
            <div class="deal-meta">
              <span>👤 Client: <strong><?= sanitize($j['client_fn'].' '.$j['client_ln']) ?></strong></span>
              <span>🎯 Provider: <strong><?= sanitize($j['prov_fn'].' '.$j['prov_ln']) ?></strong></span>
              <span style="font-family:var(--fc);font-size:10px;">Updated <?= timeAgo($j['updated_at']) ?></span>
            </div>
          </div>
          <div class="deal-actions">
            <form method="POST" action="<?= APP_URL ?>/admin/approve-job.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="job_id"     value="<?= $j['id'] ?>">
              <input type="hidden" name="action"     value="approve">
              <button type="submit" class="btn btn-lime btn-sm">✅ Approve Completion</button>
            </form>
            <form method="POST" action="<?= APP_URL ?>/admin/approve-job.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="job_id"     value="<?= $j['id'] ?>">
              <input type="hidden" name="action"     value="dispute">
              <button type="submit" class="btn btn-red-soft btn-sm"
                onclick="return confirm('Flag this job as disputed?')">⚠️ Dispute</button>
            </form>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 7: SUBSCRIPTIONS / FREE LIMITS
    ════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-subscriptions">
      <div class="section-card">
        <div class="sc-head">
          <div>
            <div class="card-ttl">💳 Providers at Free Job Limit</div>
            <div class="card-cnt"><?= count($freeJobAlerts) ?> providers have used all 3 free jobs — need subscription</div>
          </div>
        </div>
        <?php if(empty($freeJobAlerts)): ?>
        <div class="empty-state">
          <div class="es-ico">🎉</div>
          <div class="es-ttl">No alerts</div>
          <div class="es-sub">All active providers are within their free tier or subscribed.</div>
        </div>
        <?php else: foreach($freeJobAlerts as $u):
          $init = strtoupper(substr($u['first_name']??'',0,1).substr($u['last_name']??'',0,1));
          $freeUsed = min((int)($u['free_jobs_used']??0), 3);
        ?>
        <div class="free-alert-row">
          <div class="u-av" style="background:linear-gradient(135deg,var(--amber),var(--coral));">
            <?php if(!empty($u['avatar'])): ?><img src="<?= sanitize($u['avatar']) ?>" alt=""><?php else: echo $init; endif; ?>
          </div>
          <div style="flex:1;min-width:0;">
            <div class="u-name"><?= sanitize($u['first_name'].' '.$u['last_name']) ?></div>
            <div class="u-email"><?= sanitize($u['email']) ?></div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;">
            <span class="pill pill-free">Free: <?= $freeUsed ?>/3 used</span>
            <div class="free-progress-track" style="width:90px;">
              <div class="free-progress-fill" style="width:<?= ($freeUsed/3)*100 ?>%;"></div>
            </div>
          </div>
          <div class="deal-actions">
            <!-- Grant premium manually after off-platform payment confirmation -->
            <form method="POST" action="<?= APP_URL ?>/admin/verify-user.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="user_id"    value="<?= $u['id'] ?>">
              <input type="hidden" name="action"     value="grant_premium">
              <button type="submit" class="btn btn-lime btn-sm" title="Grant premium after confirming payment">⭐ Verify Payment & Grant</button>
            </form>
            <form method="POST" action="<?= APP_URL ?>/admin/verify-user.php" style="display:inline;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="user_id"    value="<?= $u['id'] ?>">
              <input type="hidden" name="action"     value="reset_free_jobs">
              <button type="submit" class="btn btn-ghost btn-sm"
                onclick="return confirm('Reset free job count for this provider?')">↺ Reset Free Count</button>
            </form>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════
         TAB 8: MY PROFILE
    ════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-profile">
      <div class="profile-grid">

        <!-- Profile card -->
        <div class="profile-card">
          <div class="profile-av-wrap">
            <div class="profile-av" id="profileAvDisplay">
              <?php if(!empty($adminUser['avatar'])): ?>
                <img src="<?= sanitize($adminUser['avatar']) ?>" alt="" id="profileAvImg">
              <?php else: ?>
                <span id="profileAvInitials"><?= strtoupper(substr($adminUser['first_name']??'A',0,1).substr($adminUser['last_name']??'D',0,1)) ?></span>
              <?php endif; ?>
            </div>
            <label for="avatarInput" class="profile-av-edit" title="Change photo">📷</label>
            <input type="file" id="avatarInput" accept="image/*" style="display:none;" onchange="uploadAvatar(this)">
          </div>
          <div class="profile-name" id="profileDisplayName"><?= sanitize(($adminUser['first_name']??'Admin').' '.($adminUser['last_name']??'')) ?></div>
          <div class="profile-email"><?= sanitize($adminUser['email']??'') ?></div>
          <div class="profile-badge">🔑 ADMINISTRATOR</div>
          <div class="profile-stats">
            <div class="ps-item">
              <div class="ps-val"><?= $stats['total_users'] ?></div>
              <div class="ps-lbl">Total Users</div>
            </div>
            <div class="ps-item">
              <div class="ps-val"><?= $stats['sealed_deals'] ?></div>
              <div class="ps-lbl">Sealed Deals</div>
            </div>
            <div class="ps-item">
              <div class="ps-val"><?= $stats['completed_jobs'] ?></div>
              <div class="ps-lbl">Jobs Done</div>
            </div>
            <div class="ps-item">
              <div class="ps-val"><?= $stats['total_admins'] ?></div>
              <div class="ps-lbl">Admins</div>
            </div>
          </div>
        </div>

        <!-- Edit forms -->
        <div>
          <!-- Update name / email -->
          <div class="section-card" style="margin-bottom:18px;">
            <div class="sc-head"><div class="card-ttl">✏️ Update Profile Info</div></div>
            <form method="POST" action="<?= APP_URL ?>/admin/update-profile.php" style="padding:22px;display:flex;flex-direction:column;gap:16px;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="action"     value="update_info">
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="form-group" style="margin:0;">
                  <label class="form-label">First Name</label>
                  <input type="text" name="first_name" class="form-input"
                         value="<?= sanitize($adminUser['first_name']??'') ?>" required>
                </div>
                <div class="form-group" style="margin:0;">
                  <label class="form-label">Last Name</label>
                  <input type="text" name="last_name"  class="form-input"
                         value="<?= sanitize($adminUser['last_name']??'') ?>" required>
                </div>
              </div>
              <div class="form-group" style="margin:0;">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input"
                       value="<?= sanitize($adminUser['email']??'') ?>" required>
              </div>
              <div>
                <button type="submit" class="btn btn-lime">💾 Save Changes</button>
              </div>
            </form>
          </div>

          <!-- Change password -->
          <div class="section-card">
            <div class="sc-head"><div class="card-ttl">🔒 Change Password</div></div>
            <form method="POST" action="<?= APP_URL ?>/admin/update-profile.php" style="padding:22px;display:flex;flex-direction:column;gap:14px;">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="action"     value="change_password">
              <div class="form-group" style="margin:0;">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-input" placeholder="Enter current password" required>
              </div>
              <div class="form-group" style="margin:0;">
                <label class="form-label">New Password</label>
                <input type="password" name="new_password" id="newPass" class="form-input" placeholder="Min 8 characters" required>
              </div>
              <div class="form-group" style="margin:0;">
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-input" placeholder="Repeat new password" required>
              </div>
              <div>
                <button type="submit" class="btn btn-coral">🔒 Update Password</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ════════════════════════════════════════════════
     MODAL: Add Admin
════════════════════════════════════════════════ -->
<div class="modal-overlay" id="addAdminModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title">🔑 Add New Admin</div>
      <button class="modal-close" onclick="closeModal('addAdminModal')">×</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/add-admin.php">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <div class="form-group">
          <label class="form-label">First Name</label>
          <input type="text" name="first_name" class="form-input" placeholder="e.g. Kwame" required>
        </div>
        <div class="form-group">
          <label class="form-label">Last Name</label>
          <input type="text" name="last_name"  class="form-input" placeholder="e.g. Mensah" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" placeholder="admin@gigghana.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-input" placeholder="Strong password" required minlength="8">
        </div>
        <div style="padding:10px 14px;background:var(--lime-dim);border:1px solid var(--lime-border);border-radius:9px;font-size:12px;color:var(--lime);">
          ⚠️ This admin will have full access to GigGhana's control panel. Share credentials securely.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('addAdminModal')">Cancel</button>
        <button type="submit" class="btn btn-lime">✓ Create Admin Account</button>
      </div>
    </form>
  </div>
</div>

<!-- ════════════════════════════════════════════════
     MODAL: Assign Badge
════════════════════════════════════════════════ -->
<div class="modal-overlay" id="badgeModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title">🏅 Assign Badge</div>
      <button class="modal-close" onclick="closeModal('badgeModal')">×</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/admin/verify-user.php">
      <div class="modal-body">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" value="grant_badge">
        <input type="hidden" name="user_id" id="badgeUserId">
        <div style="margin-bottom:14px;font-size:13px;color:var(--tx-2);">
          Assigning badge to: <strong id="badgeUserName"></strong>
        </div>
        <div class="form-group">
          <label class="form-label">Select Badge</label>
          <select name="badge_type" class="form-select" required>
            <option value="">— Choose badge —</option>
            <option value="verified">✓ Verified</option>
            <option value="top_rated">⭐ Top Rated</option>
            <option value="premium">💎 Premium</option>
            <option value="rising_star">🌟 Rising Star</option>
            <option value="pro">🏆 Pro</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('badgeModal')">Cancel</button>
        <button type="submit" class="btn btn-amber-soft">🏅 Assign Badge</button>
      </div>
    </form>
  </div>
</div>

<!-- ════════════════════════════════════════════════
     CHAT VIEWER
════════════════════════════════════════════════ -->
<div class="chat-viewer" id="chatViewer" onclick="closeChatViewerOnBg(event)">
  <div class="chat-modal">
    <div class="chat-modal-head">
      <div>
        <div class="chat-modal-title" id="chatModalTitle">Conversation</div>
        <div class="chat-modal-sub" id="chatModalSub">Admin read-only view</div>
      </div>
      <button class="chat-close-btn" onclick="closeChatViewer()">×</button>
    </div>
    <div class="chat-admin-banner">
      🔒 Admin Read-Only Mode — This conversation is being monitored for platform safety.
    </div>
    <div class="chat-messages" id="chatMessages">
      <div style="text-align:center;color:var(--tx-3);padding:20px;font-size:12px;">Loading messages…</div>
    </div>
  </div>
</div>

<div id="toast-c"></div>

<script>
/* ══ THEME ══ */
function toggleTheme() {
  const isLight = document.getElementById('appBody').classList.toggle('lm');
  const val = isLight ? 'light' : 'dark';
  localStorage.setItem('gg_theme', val);
  document.cookie = `gg_theme=${val};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = isLight ? '☀️' : '🌙';
  showToast('Theme', isLight ? '☀️ Light mode' : '🌙 Dark mode', 'info', 1800);
}
(function(){
  const stored = localStorage.getItem('gg_theme') || '<?= $isLight ? "light" : "dark" ?>';
  const body = document.getElementById('appBody');
  const btn  = document.getElementById('themeBtn');
  if (stored === 'light') { body.classList.add('lm'); if(btn) btn.textContent = '☀️'; }
  else { body.classList.remove('lm'); if(btn) btn.textContent = '🌙'; }
})();

/* ══ TABS ══ */
function switchTab(name, btnEl) {
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(b  => b.classList.remove('active'));
  document.querySelectorAll('.sb-item').forEach(b  => b.classList.remove('active'));
  const panel = document.getElementById('tab-' + name);
  if (panel) panel.classList.add('active');
  const topBtn = document.getElementById('tabbtn-' + name);
  if (topBtn) topBtn.classList.add('active');
  if (btnEl) btnEl.classList.add('active');
}

/* ══ MODAL ══ */
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if(e.target === el) el.classList.remove('open'); });
});

/* ══ BADGE MODAL ══ */
function openBadgeModal(userId, userName) {
  document.getElementById('badgeUserId').value = userId;
  document.getElementById('badgeUserName').textContent = userName;
  openModal('badgeModal');
}

/* ══ CHAT VIEWER ══ */
function openChatViewer(convId, jobTitle, clientName, provName) {
  document.getElementById('chatModalTitle').textContent = '💬 ' + jobTitle;
  document.getElementById('chatModalSub').textContent   = clientName + ' ↔ ' + provName;
  document.getElementById('chatViewer').classList.add('open');
  document.getElementById('chatMessages').innerHTML = '<div style="text-align:center;color:var(--tx-3);padding:20px;font-size:12px;">Loading messages…</div>';

  fetch('<?= APP_URL ?>/api/admin-chat.php?conv_id=' + convId + '&csrf=<?= $csrf ?>')
    .then(r => r.json())
    .then(data => renderChatMessages(data.messages || [], clientName, provName))
    .catch(() => {
      document.getElementById('chatMessages').innerHTML =
        '<div style="text-align:center;color:var(--coral);padding:20px;font-size:12px;">⚠️ Could not load messages. Check API endpoint.</div>';
    });
}

function renderChatMessages(messages, clientName, provName) {
  const box = document.getElementById('chatMessages');
  if (!messages.length) {
    box.innerHTML = '<div style="text-align:center;color:var(--tx-3);padding:20px;font-size:12px;">No messages in this conversation yet.</div>';
    return;
  }
  const firstSenderId = messages[0].sender_id;
  box.innerHTML = messages.map(m => {
    const isFirst = m.sender_id === firstSenderId;
    const name    = isFirst ? clientName : provName;
    const initials = name.split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2);
    return `<div class="chat-msg ${isFirst ? '' : 'outgoing'}">
      <div class="chat-msg-av" title="${name}">${initials}</div>
      <div>
        <div style="font-family:var(--fc);font-size:9px;color:var(--tx-3);margin-bottom:4px;">${name}</div>
        <div class="chat-bubble">${escHtml(m.content || m.message || '')}</div>
        <div class="chat-msg-time">${m.created_at || ''}</div>
      </div>
    </div>`;
  }).join('');
  box.scrollTop = box.scrollHeight;
}

function closeChatViewer() { document.getElementById('chatViewer').classList.remove('open'); }
function closeChatViewerOnBg(e) { if(e.target === document.getElementById('chatViewer')) closeChatViewer(); }

function escHtml(s) {
  return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ══ TABLE SEARCH ══ */
function filterTable(tableId, query) {
  const q = query.toLowerCase().trim();
  document.querySelectorAll('#'+tableId+' tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

function filterByRole(role) {
  document.querySelectorAll('#userTable tbody tr').forEach(row => {
    row.style.display = (!role || row.dataset.role === role) ? '' : 'none';
  });
}

/* ══ STAT COUNTER ══ */
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if (!e.isIntersecting) return;
    const el = e.target, tgt = parseInt(el.dataset.count) || 0;
    if (!tgt) return;
    let c = 0; const step = tgt / 50;
    const id = setInterval(() => {
      c = Math.min(c + step, tgt);
      el.textContent = Math.floor(c);
      if (c >= tgt) clearInterval(id);
    }, 18);
    obs.unobserve(el);
  });
}, { threshold: 0.4 });
document.querySelectorAll('[data-count]').forEach(el => obs.observe(el));

/* ══ AVATAR UPLOAD ══ */
function uploadAvatar(input) {
  const file = input.files[0];
  if (!file) return;
  if (file.size > 3 * 1024 * 1024) {
    showToast('Too large', 'Image must be under 3MB.', 'error'); return;
  }
  const reader = new FileReader();
  reader.onload = e => {
    const src = e.target.result;
    let img = document.getElementById('profileAvImg');
    if (!img) {
      document.getElementById('profileAvDisplay').innerHTML = `<img id="profileAvImg" src="${src}" alt="" style="width:100%;height:100%;object-fit:cover;">`;
    } else img.src = src;
  };
  reader.readAsDataURL(file);

  const fd = new FormData();
  fd.append('avatar', file);
  fd.append('csrf_token', '<?= $csrf ?>');
  fd.append('action', 'upload_avatar');

  fetch('<?= APP_URL ?>/admin/update-profile.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(d => {
      if (d.success) showToast('Avatar updated', 'Your profile picture has been saved.', 'success');
      else showToast('Error', d.message || 'Upload failed.', 'error');
    })
    .catch(() => showToast('Network error', 'Could not upload photo.', 'error'));
}

/* ══ TOAST ══ */
const ICONS = { success:'✅', error:'❌', info:'ℹ️', warning:'⚠️' };
function showToast(title, msg, type = 'info', d = 4000) {
  const c = document.getElementById('toast-c');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<div class="t-ico">${ICONS[type]||'ℹ️'}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(() => {
    t.style.transition='all .3s'; t.style.opacity='0'; t.style.transform='translateX(40px)';
    setTimeout(()=>t.remove(),360);
  }, d);
}

/* Welcome */
setTimeout(() => showToast(
  'Admin Centre',
  '<?= sanitize($adminUser["first_name"]??'Admin') ?>, you have <?= $stats["pending_verif"] ?> pending verification<?= $stats["pending_verif"]!=1?"s":"" ?> and <?= $stats["pending_complete"] ?> completion approval<?= $stats["pending_complete"]!=1?"s":"" ?>.',
  'info', 6000
), 800);

<?php if(isset($_GET['success'])): ?>showToast('Success','<?= addslashes(sanitize($_GET['success'])) ?>','success');<?php endif; ?>
<?php if(isset($_GET['error'])  ): ?>showToast('Error','<?= addslashes(sanitize($_GET['error'])) ?>','error');<?php endif; ?>
</script>
</body>
</html>