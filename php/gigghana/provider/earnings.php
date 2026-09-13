<?php
/**
 * GigGhana — client/proposals.php  (v3 — Dashboard-aligned rebuild)
 *
 * Design: Matches dashboard.php exactly — same tokens, same row/card
 * patterns (prop-row, prop-av, prop-bid, prop-stars, prop-status),
 * same glass cards, same sidebar, same topbar chrome.
 *
 * New features layered on top:
 *  ✅ Job Summary Banner  (budget, status, meta, edit/view buttons)
 *  ✅ Analytics Strip     (total, shortlisted, avg bid, lowest bid)
 *  ✅ Status Tabs         (All / Pending / Shortlisted / Accepted / Declined)
 *  ✅ Sort + Search bar
 *  ✅ Expanded prop-row   (trust badges, skills, cover letter preview)
 *  ✅ Full Detail modal   (cover letter, reviews, portfolio links)
 *  ✅ Comparison Tool     (select up to 3 → side-by-side modal table)
 *  ✅ Shortlist / Accept & Hire / Decline / Unshortlist  → confirm modal
 *  ✅ Recommended Providers (matched by category)
 *  ✅ Dark / light theme   (cookie + localStorage, identical to dashboard)
 *  ✅ Mobile bottom nav    (identical to dashboard)
 *  ✅ Unread messages badge in sidebar (live DB count)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('client');

$userId  = (int)$_SESSION['user_id'];
$user    = getUserById($userId);
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';
$csrf    = generateCSRF();

/* ══ POST HANDLER ══════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF($_POST['csrf_token'] ?? '');
    $action     = $_POST['action']      ?? '';
    $proposalId = (int)($_POST['proposal_id'] ?? 0);

    try {
        $db = getDB();

        $stV = $db->prepare("
            SELECT p.*, j.title AS job_title, j.client_id, j.id AS job_id
            FROM proposals p JOIN jobs j ON j.id=p.job_id
            WHERE p.id=? AND j.client_id=? LIMIT 1
        ");
        $stV->execute([$proposalId, $userId]);
        $prop = $stV->fetch();

        if ($prop) {
            $getProvUser = function() use ($db, $prop) {
                $s = $db->prepare("SELECT user_id FROM providers WHERE id=? LIMIT 1");
                $s->execute([$prop['provider_id']]);
                return $s->fetchColumn();
            };

            match ($action) {
                'shortlist' => (function() use ($db,$proposalId,$prop,$getProvUser) {
                    $db->prepare("UPDATE proposals SET status='shortlisted' WHERE id=?")->execute([$proposalId]);
                    if ($pu = $getProvUser()) createNotification($pu,'shortlisted','📌 Proposal Shortlisted!',
                        "Your proposal for \"{$prop['job_title']}\" has been shortlisted.",['job_id'=>$prop['job_id']]);
                    $_SESSION['flash_ok'] = 'Proposal shortlisted.';
                })(),
                'accept' => (function() use ($db,$proposalId,$prop,$userId,$getProvUser) {
                    $db->beginTransaction();
                    $db->prepare("UPDATE proposals SET status='accepted' WHERE id=?")->execute([$proposalId]);
                    $db->prepare("UPDATE proposals SET status='rejected' WHERE job_id=? AND id!=?")->execute([$prop['job_id'],$proposalId]);
                    $db->prepare("UPDATE jobs SET status='in_progress',hired_provider_id=? WHERE id=?")->execute([$prop['provider_id'],$prop['job_id']]);
                    $db->commit();
                    if ($pu = $getProvUser()) createNotification($pu,'accepted','🎉 You\'re Hired!',
                        "Your proposal for \"{$prop['job_title']}\" was accepted!",['job_id'=>$prop['job_id']]);
                    createNotification($userId,'job_hired','✅ Freelancer Hired',
                        "You hired a freelancer for \"{$prop['job_title']}\".",['job_id'=>$prop['job_id']]);
                    $_SESSION['flash_ok'] = '🎉 Freelancer hired! Job is now in progress.';
                })(),
                'reject' => (function() use ($db,$proposalId,$prop,$getProvUser) {
                    $db->prepare("UPDATE proposals SET status='rejected' WHERE id=?")->execute([$proposalId]);
                    if ($pu = $getProvUser()) createNotification($pu,'rejected','Proposal Update',
                        "Your proposal for \"{$prop['job_title']}\" was not selected.",['job_id'=>$prop['job_id']]);
                    $_SESSION['flash_ok'] = 'Proposal declined.';
                })(),
                'unshortlist' => (function() use ($db,$proposalId) {
                    $db->prepare("UPDATE proposals SET status='pending' WHERE id=?")->execute([$proposalId]);
                    $_SESSION['flash_ok'] = 'Removed from shortlist.';
                })(),
                default => null,
            };
        }
    } catch (Exception $e) {
        if (isset($db) && $db->inTransaction()) $db->rollBack();
        error_log($e->getMessage());
        $_SESSION['flash_err'] = 'Action failed. Please try again.';
    }

    header("Location: ".APP_URL."/client/proposals.php?".http_build_query(array_filter([
        'job_id' => (int)($_POST['job_id'] ?? 0),
        'status' => sanitize($_POST['curr_status'] ?? ''),
        'sort'   => sanitize($_POST['curr_sort'] ?? ''),
        'q'      => sanitize($_POST['curr_q'] ?? ''),
    ]))); exit;
}

/* ══ FLASH ══ */
$flashOk  = $_SESSION['flash_ok']  ?? ''; unset($_SESSION['flash_ok']);
$flashErr = $_SESSION['flash_err'] ?? ''; unset($_SESSION['flash_err']);

/* ══ FILTERS ══ */
$filterJob  = (int)($_GET['job_id'] ?? 0);
$filterStat = in_array($_GET['status']??'',['all','pending','shortlisted','accepted','rejected','withdrawn'])
              ? ($_GET['status'] ?? 'all') : 'all';
$sortBy     = in_array($_GET['sort']??'',['newest','bid_low','bid_high','rating','delivery'])
              ? ($_GET['sort'] ?? 'newest') : 'newest';
$search     = trim($_GET['q'] ?? '');
$page       = max(1,(int)($_GET['page'] ?? 1));
$perPage    = 10;
$offset     = ($page-1)*$perPage;

/* ══ DATA ══ */
try {
    $db = getDB();

    /* Sidebar unread count */
    $stUM = $db->prepare("SELECT COUNT(*) FROM messages m JOIN conversations c ON c.id=m.conversation_id
        WHERE (c.user1_id=? OR c.user2_id=?) AND m.sender_id!=? AND m.is_read=0 AND m.is_deleted=0");
    $stUM->execute([$userId,$userId,$userId]);
    $unreadMsgs = (int)$stUM->fetchColumn();

    /* Sidebar active jobs count */
    $stAJ = $db->prepare("SELECT COUNT(*) FROM jobs WHERE client_id=? AND status IN ('open','in_progress')");
    $stAJ->execute([$userId]); $activeJobs = (int)$stAJ->fetchColumn();

    /* Client jobs dropdown */
    $stJ = $db->prepare("SELECT j.id,j.title,j.status,(SELECT COUNT(*) FROM proposals WHERE job_id=j.id) AS pc
        FROM jobs j WHERE j.client_id=? ORDER BY j.created_at DESC");
    $stJ->execute([$userId]); $clientJobs = $stJ->fetchAll();

    /* Active job meta */
    $activeJob = null;
    if ($filterJob) {
        $stAj = $db->prepare("SELECT j.*,c.name AS cat_name,c.icon AS cat_icon
            FROM jobs j LEFT JOIN categories c ON c.id=j.category_id
            WHERE j.id=? AND j.client_id=? LIMIT 1");
        $stAj->execute([$filterJob,$userId]); $activeJob = $stAj->fetch();
    }

    /* WHERE clause */
    $where  = ['j.client_id=?']; $params = [$userId];
    if ($filterJob)            { $where[] = 'p.job_id=?';     $params[] = $filterJob; }
    if ($filterStat !== 'all') { $where[] = 'p.status=?';     $params[] = $filterStat; }
    if ($search)               { $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR p.cover_letter LIKE ?)";
                                 $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }
    $wSql = implode(' AND ',$where);

    $orderSql = match($sortBy){
        'bid_low'  => 'p.bid_amount ASC',
        'bid_high' => 'p.bid_amount DESC',
        'rating'   => 'pr.rating_avg DESC',
        'delivery' => 'p.delivery_days ASC',
        default    => 'p.created_at DESC',
    };

    /* Total count */
    $stTot = $db->prepare("SELECT COUNT(*) FROM proposals p JOIN jobs j ON j.id=p.job_id WHERE $wSql");
    $stTot->execute($params); $totalProps = (int)$stTot->fetchColumn();
    $totalPages = (int)ceil($totalProps/$perPage);

    /* Status tab counts */
    $stCnt = $db->prepare("SELECT p.status,COUNT(*) AS c FROM proposals p JOIN jobs j ON j.id=p.job_id
        WHERE j.client_id=? ".($filterJob?"AND p.job_id=? ":" ")."GROUP BY p.status");
    $stCnt->execute($filterJob?[$userId,$filterJob]:[$userId]);
    $statusCounts = ['all'=>0];
    foreach($stCnt->fetchAll() as $r){ $statusCounts[$r['status']]=(int)$r['c']; $statusCounts['all']+=(int)$r['c']; }

    /* Analytics */
    $analytics = ['total'=>0,'shortlisted'=>0,'avg_bid'=>0,'min_bid'=>0,'max_bid'=>0];
    if ($filterJob) {
        $stAn = $db->prepare("SELECT COUNT(*) AS total,SUM(status='shortlisted') AS shortlisted,
            AVG(bid_amount) AS avg_bid,MIN(bid_amount) AS min_bid,MAX(bid_amount) AS max_bid
            FROM proposals WHERE job_id=?");
        $stAn->execute([$filterJob]); $analytics = $stAn->fetch() ?: $analytics;
    }

    /* Main proposals */
    $stProps = $db->prepare("
        SELECT p.id,p.uuid,p.job_id,p.provider_id,p.cover_letter,p.bid_amount,
               p.delivery_days,p.status,p.portfolio_urls,p.client_viewed,p.created_at,
               j.title AS job_title,j.status AS job_status,j.budget_min,j.budget_max,j.id AS jid,
               u.first_name,u.last_name,u.avatar,u.location,u.id AS uid,
               u.phone_verified,u.email_verified,u.ghana_card_verified,
               pr.id AS provider_id,pr.tagline,pr.hourly_rate,pr.rating_avg,pr.rating_count,
               pr.completed_jobs,pr.is_verified,pr.is_featured,pr.response_time,
               pr.availability,pr.experience_level,pr.success_rate,pr.languages
        FROM proposals p
        JOIN jobs j       ON j.id  = p.job_id
        JOIN providers pr ON pr.id = p.provider_id
        JOIN users u      ON u.id  = pr.user_id
        WHERE $wSql
        ORDER BY $orderSql
        LIMIT :lim OFFSET :off
    ");
    foreach($params as $i=>$v) $stProps->bindValue($i+1,$v);
    $stProps->bindValue(':lim',$perPage,PDO::PARAM_INT);
    $stProps->bindValue(':off',$offset,PDO::PARAM_INT);
    $stProps->execute(); $proposals = $stProps->fetchAll();

    foreach($proposals as &$p){
        if(!$p['client_viewed']) $db->prepare("UPDATE proposals SET client_viewed=1 WHERE id=?")->execute([$p['id']]);
        $stSk = $db->prepare("SELECT s.name FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id WHERE ps.provider_id=? LIMIT 5");
        $stSk->execute([$p['provider_id']]); $p['skills'] = $stSk->fetchAll(PDO::FETCH_COLUMN);
        $stRv = $db->prepare("SELECT r.rating_overall,r.comment,r.created_at,u2.first_name AS rev_fname,u2.last_name AS rev_lname
            FROM reviews r JOIN users u2 ON u2.id=r.reviewer_id
            WHERE r.reviewee_id=? AND r.is_public=1 ORDER BY r.created_at DESC LIMIT 3");
        $stRv->execute([$p['uid']]); $p['reviews'] = $stRv->fetchAll();
        $stCv = $db->prepare("SELECT id FROM conversations WHERE ((user1_id=? AND user2_id=?) OR (user2_id=? AND user1_id=?)) LIMIT 1");
        $stCv->execute([$userId,$p['uid'],$userId,$p['uid']]); $p['conv_id'] = $stCv->fetchColumn();
    }
    unset($p);

    /* Recommended */
    $recommended = [];
    if($filterJob && $activeJob && ($activeJob['category_id']??0)){
        $excl = array_column($proposals,'uid');
        $exclSql = $excl ? 'AND u.id NOT IN ('.implode(',',array_fill(0,count($excl),'?')).')' : '';
        $stRec = $db->prepare("SELECT u.id AS uid,u.first_name,u.last_name,u.avatar,u.location,
            pr.tagline,pr.hourly_rate,pr.rating_avg,pr.rating_count,pr.completed_jobs,
            pr.is_verified,pr.experience_level
            FROM providers pr JOIN users u ON u.id=pr.user_id
            WHERE u.is_active=1 AND u.is_banned=0
              AND EXISTS(SELECT 1 FROM provider_skills ps JOIN skills s ON s.id=ps.skill_id
                         WHERE ps.provider_id=pr.id AND s.category_id=?) $exclSql
            ORDER BY pr.is_verified DESC,pr.rating_avg DESC LIMIT 4");
        $stRec->execute([$activeJob['category_id'],...$excl]); $recommended = $stRec->fetchAll();
    }

} catch(Exception $e){
    error_log($e->getMessage());
    $proposals=[]; $clientJobs=[]; $totalProps=0; $totalPages=0;
    $statusCounts=['all'=>0]; $activeJob=null;
    $analytics=['total'=>0,'shortlisted'=>0,'avg_bid'=>0,'min_bid'=>0,'max_bid'=>0];
    $recommended=[]; $unreadMsgs=0; $activeJobs=0;
}

/* ══ TEMPLATE HELPERS ══ */
$iconMap = ['code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
            'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
            'briefcase'=>'⚖️','headphones'=>'🎧','tool'=>'🔧','bar-chart'=>'📊',
            'globe'=>'🌐','camera'=>'📷','music'=>'🎵'];

$hour     = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$myInit   = strtoupper(substr($user['first_name']??'M',0,1).substr($user['last_name']??'',0,1));
$myAvatar = $user['avatar'] ?? '';

function ini2(string $f,string $l):string{ return strtoupper(substr($f,0,1).substr($l,0,1)); }
function starsHtml(float $r):string{
    $o=''; for($i=1;$i<=5;$i++) $o.=$r>=$i?'<span class="s-full">★</span>':($r>=$i-.5?'<span class="s-half">★</span>':'<span class="s-empty">★</span>'); return $o;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Proposals<?= $activeJob?' — '.sanitize($activeJob['title']):'' ?> — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════════════════════════
   DESIGN TOKENS — identical to dashboard.php
   Light mode overrides also identical so theme cookie works cross-page
═══════════════════════════════════════════════════════════════════════ */
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
  --fm:'Plus Jakarta Sans',sans-serif; --fb:'DM Sans',sans-serif;
  --sb:256px; --r:16px; --rs:10px; --e:all 0.26s cubic-bezier(.4,0,.2,1);
}
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
.lm .sidebar{background:var(--s1);border-right-color:var(--bd);}
.lm .topbar{background:rgba(243,245,250,0.96);border-bottom-color:var(--bd);}
.lm .sb-item{color:var(--tx-3);}
.lm .sb-item:hover{background:rgba(0,0,0,0.05);color:var(--tx);}
.lm .sb-item.active{background:var(--cyan-dim);color:var(--cyan);}
.lm .sb-user-card{background:rgba(0,0,0,0.06);}
.lm .section-card,.lm .job-banner,.lm .analytics-strip .an-item{background:rgba(255,255,255,0.9);}
.lm .prop-row:hover{background:rgba(0,0,0,0.04);}
.lm .btn-ghost{background:rgba(0,0,0,0.05);border-color:var(--bd2);color:var(--tx-2);}
.lm .btn-ghost:hover{background:rgba(0,0,0,0.1);color:var(--tx);}
.lm .mobile-nav{background:rgba(243,245,250,0.98);border-top-color:var(--bd);}
.lm .modal-box{background:var(--s2);}
.lm .field-select,.lm .search-box{background:rgba(0,0,0,0.05);border-color:var(--bd2);}
.lm .tab-pill{background:rgba(0,0,0,0.05);border-color:var(--bd);}
.lm .cov-box{background:rgba(0,0,0,0.04);}
.lm .toast{background:var(--s2);border-color:var(--bd2);}
.lm .rec-card{background:rgba(0,0,0,0.04);}
.lm .compare-row-item{background:rgba(255,255,255,0.85);}
.lm .compare-bar{background:rgba(91,79,217,0.06);border-color:var(--violet-border);}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);min-height:100vh;display:flex;
  font-size:14px;-webkit-font-smoothing:antialiased;transition:background .3s,color .3s;}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}a{text-decoration:none;color:inherit;}
h1,h2,h3,h4,.logo-text,.card-ttl{font-family:var(--fm);-webkit-font-smoothing:antialiased;}

/* ══ SIDEBAR (copied from dashboard) ══ */
.sidebar{
  width:var(--sb);min-height:100vh;background:var(--s1);
  border-right:1px solid var(--bd);position:fixed;top:0;left:0;z-index:200;
  display:flex;flex-direction:column;overflow:hidden;transition:background .3s,border-color .3s;
}
.sidebar::before{content:'';position:absolute;top:0;left:0;right:0;height:2px;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--cyan));
  background-size:200% 100%;animation:gradShift 4s linear infinite;}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:200% 50%}}
.sb-logo{padding:22px 18px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:9px;text-decoration:none;}
.sb-logo-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));
  border-radius:9px;display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:15px;color:#0C0E14;flex-shrink:0;}
.sb-logo-text{font-family:var(--fm);font-size:18px;font-weight:800;color:var(--tx);}
.sb-logo-text span{color:var(--cyan);}
.sb-nav{flex:1;padding:10px;overflow-y:auto;scrollbar-width:none;}
.sb-nav::-webkit-scrollbar{display:none;}
.sb-section{font-size:9px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;
  color:var(--tx-3);padding:6px 12px;margin:14px 0 4px;}
.sb-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;
  color:var(--tx-3);font-size:13px;font-weight:500;transition:var(--e);
  position:relative;cursor:pointer;text-decoration:none;}
.sb-item:hover{background:rgba(255,255,255,0.05);color:var(--tx);}
.sb-item.active{background:var(--cyan-dim);color:var(--cyan);border-left:3px solid var(--cyan);padding-left:9px;}
.sb-item.danger{color:var(--red);}
.sb-item.danger:hover{background:rgba(255,77,106,0.08);}
.sb-badge{margin-left:auto;background:var(--coral);color:#fff;font-size:9px;font-weight:800;
  padding:2px 7px;border-radius:50px;font-family:var(--fm);min-width:18px;text-align:center;}
.sb-badge.cyan{background:var(--cyan);color:#0C0E14;}
.sb-user{padding:14px 10px;border-top:1px solid var(--bd);}
.sb-user-card{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;
  background:rgba(0,0,0,0.2);transition:background .3s;}
.sb-av{width:36px;height:36px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,var(--coral),var(--violet-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-size:13px;font-weight:800;color:#fff;overflow:hidden;}
.sb-av img{width:100%;height:100%;object-fit:cover;}
.sb-uname{font-size:13px;font-weight:700;}
.sb-urole{font-size:10px;color:var(--coral);font-weight:700;text-transform:uppercase;margin-top:1px;}

/* ══ MAIN ══ */
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-width:0;}

/* ══ TOPBAR (identical to dashboard) ══ */
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:64px;
  background:rgba(12,14,20,0.92);backdrop-filter:blur(24px);border-bottom:1px solid var(--bd);
  position:sticky;top:0;z-index:100;transition:background .3s,border-color .3s;}
.topbar-title h1{font-size:20px;font-weight:800;line-height:1.1;}
.topbar-title p{font-size:11.5px;color:var(--tx-3);margin-top:1px;}
.topbar-right{display:flex;align-items:center;gap:8px;}
.theme-btn{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.04);
  border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;
  font-size:16px;cursor:pointer;transition:var(--e);}
.theme-btn:hover{background:rgba(255,255,255,0.08);}

/* ══ BUTTONS ══ */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--rs);
  font-family:var(--fb);font-size:13px;font-weight:600;cursor:pointer;border:none;
  text-decoration:none;transition:var(--e);white-space:nowrap;line-height:1.3;}
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
.btn-amber{background:linear-gradient(135deg,var(--amber),#E8A220);color:#0C0E14;font-weight:700;}
.btn-amber:hover{transform:translateY(-2px);}
.btn-red-soft{background:rgba(255,77,106,0.1);border:1px solid rgba(255,77,106,0.2);color:var(--red);}
.btn-red-soft:hover{background:rgba(255,77,106,0.18);}
.btn-sm{padding:5px 12px;font-size:11.5px;border-radius:8px;}
.btn-lg{padding:12px 26px;font-size:14px;border-radius:12px;}

/* ══ CONTENT ══ */
.content{padding:26px 28px 100px;}

/* ══ ALERT ══ */
.alert{display:flex;align-items:center;gap:11px;padding:13px 18px;border-radius:12px;margin-bottom:22px;font-size:13.5px;}
.alert.ok{background:rgba(31,217,160,0.07);border:1px solid rgba(31,217,160,0.22);color:var(--green);}
.alert.err{background:rgba(255,77,106,0.07);border:1px solid rgba(255,77,106,0.2);color:var(--red);}

/* ══ JOB SUMMARY BANNER ══ */
.job-banner{
  position:relative;overflow:hidden;border-radius:20px;
  background:linear-gradient(135deg,var(--s2) 0%,rgba(0,212,200,0.07) 60%,rgba(255,107,74,0.05) 100%);
  border:1px solid var(--cyan-border);padding:22px 26px;margin-bottom:22px;
  display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;
  transition:background .3s,border-color .3s;
}
.jb-glow{position:absolute;width:280px;height:280px;border-radius:50%;
  background:radial-gradient(circle,rgba(0,212,200,0.08),transparent 70%);
  top:-100px;right:-80px;pointer-events:none;}
.jb-badge{display:inline-block;background:var(--cyan-dim);border:1px solid var(--cyan-border);
  color:var(--cyan);padding:3px 11px;border-radius:50px;font-size:10.5px;font-weight:800;
  font-family:var(--fm);letter-spacing:.8px;text-transform:uppercase;margin-bottom:7px;}
.jb-title{font-family:var(--fm);font-size:clamp(16px,2.2vw,22px);font-weight:900;
  margin-bottom:8px;line-height:1.2;position:relative;z-index:1;}
.jb-meta{display:flex;gap:14px;flex-wrap:wrap;font-size:12.5px;color:var(--tx-3);}
.jb-budget{color:var(--cyan);font-weight:800;font-family:var(--fm);}
.jb-acts{display:flex;gap:8px;flex-shrink:0;flex-wrap:wrap;position:relative;z-index:1;}
.jst{padding:4px 11px;border-radius:7px;font-size:11px;font-weight:800;font-family:var(--fm);}
.jst-open      {background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.22);}
.jst-progress  {background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.jst-completed {background:rgba(247,183,49,0.1);color:var(--amber);border:1px solid rgba(247,183,49,0.22);}
.jst-other     {background:rgba(78,90,110,0.1);color:var(--tx-3);border:1px solid var(--bd);}

/* ══ ANALYTICS STRIP ══ */
.analytics-strip{
  display:grid;grid-template-columns:repeat(4,1fr);gap:1px;
  background:var(--bd);border:1px solid var(--bd);border-radius:14px;
  overflow:hidden;margin-bottom:22px;
}
.an-item{
  background:var(--glass);backdrop-filter:blur(12px);
  padding:14px 18px;text-align:center;transition:var(--e);
}
.an-item:hover{background:rgba(0,212,200,0.04);}
.an-val{font-family:var(--fm);font-size:22px;font-weight:900;line-height:1;margin-bottom:3px;}
.an-lbl{font-size:10.5px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.5px;}

/* ══ FILTER / SEARCH BAR ══ */
.filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.search-box{flex:1;min-width:180px;display:flex;align-items:center;gap:9px;
  background:rgba(0,0,0,0.25);border:1.5px solid var(--bd);border-radius:var(--rs);
  padding:9px 13px;transition:var(--e);}
.search-box:focus-within{border-color:var(--cyan);box-shadow:0 0 0 3px var(--gC);}
.search-box input{flex:1;background:transparent;border:none;outline:none;color:var(--tx);
  font-size:13.5px;font-family:var(--fb);}
.search-box input::placeholder{color:var(--tx-3);}
.field-select{background:rgba(0,0,0,0.25);border:1.5px solid var(--bd);border-radius:var(--rs);
  padding:9px 13px;color:var(--tx);font-family:var(--fb);font-size:13px;outline:none;
  transition:var(--e);cursor:pointer;appearance:none;}
.field-select:focus{border-color:var(--cyan);}

/* ══ STATUS TABS ══ */
.tabs-row{display:flex;gap:6px;margin-bottom:22px;overflow-x:auto;padding-bottom:4px;scrollbar-width:none;}
.tabs-row::-webkit-scrollbar{display:none;}
.tab-pill{display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:50px;
  font-size:12.5px;font-weight:700;font-family:var(--fm);
  background:rgba(0,0,0,0.22);border:1.5px solid var(--bd);
  color:var(--tx-3);cursor:pointer;transition:var(--e);white-space:nowrap;text-decoration:none;}
.tab-pill:hover{color:var(--tx);border-color:var(--bd2);}
.tab-pill.active{background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);}
.tp-cnt{background:rgba(255,255,255,0.08);color:var(--tx-3);font-size:10px;padding:1px 7px;border-radius:50px;font-weight:800;}
.tab-pill.active .tp-cnt{background:var(--cyan-dim);color:var(--cyan);}
.tab-pill.has-new .tp-cnt{background:var(--coral);color:#fff;}

/* ══ COMPARE BAR ══ */
.compare-bar{
  display:none;align-items:center;gap:10px;padding:10px 16px;
  background:var(--violet-dim);border:1px solid var(--violet-border);
  border-radius:var(--rs);margin-bottom:16px;flex-wrap:wrap;
}
.compare-bar.show{display:flex;}
.compare-count{font-family:var(--fm);font-size:13px;font-weight:800;color:var(--violet);}

/* ══ SECTION CARD ══ */
.section-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  overflow:hidden;margin-bottom:20px;transition:background .3s,border-color .3s;
}
.sc-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--bd);}
.card-ttl{font-family:var(--fm);font-size:15px;font-weight:700;}
.card-cnt{font-size:11px;color:var(--tx-3);}

/* ══ PROPOSAL ROWS (dashboard-style, extended) ══ */
.prop-row{
  border-bottom:1px solid var(--bd);transition:var(--e);
  position:relative;
}
.prop-row:last-child{border-bottom:none;}
.prop-row:hover{background:rgba(255,255,255,0.025);}
.prop-row.pr-accepted{border-left:3px solid var(--green);}
.prop-row.pr-shortlisted{border-left:3px solid var(--violet);}
.prop-row.pr-rejected{opacity:.6;}
.prop-row.pr-selected{background:rgba(124,111,247,0.05);}

/* Main row line — mirrors dashboard prop-row exactly */
.pr-main{display:flex;align-items:center;gap:14px;padding:16px 22px;}

.prop-av{width:46px;height:46px;border-radius:50%;flex-shrink:0;overflow:hidden;
  background:linear-gradient(135deg,var(--violet),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-size:15px;font-weight:800;color:#fff;
  border:2px solid rgba(0,212,200,0.2);position:relative;}
.prop-av img{width:100%;height:100%;object-fit:cover;}
.prop-av-new{position:absolute;top:-4px;right:-4px;background:var(--coral);color:#fff;
  font-size:8px;font-weight:900;padding:2px 5px;border-radius:50px;border:2px solid var(--bg);
  font-family:var(--fm);}

.prop-info{flex:1;min-width:0;}
.prop-name{font-family:var(--fm);font-weight:700;font-size:13.5px;margin-bottom:1px;
  display:flex;align-items:center;gap:7px;flex-wrap:wrap;}
.prop-name a{color:var(--tx);text-decoration:none;}
.prop-name a:hover{color:var(--cyan);}
.verify-badge{display:inline-flex;align-items:center;gap:3px;background:var(--green-dim);
  color:var(--green);padding:2px 7px;border-radius:50px;font-size:10px;font-weight:700;font-family:var(--fm);}
.prop-tag{font-size:11.5px;color:var(--tx-3);margin-bottom:5px;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.prop-job-ctx{font-size:11.5px;color:var(--tx-2);margin-bottom:4px;}
.prop-job-ctx a{color:var(--cyan);font-weight:600;}
.prop-stars{color:var(--amber);font-size:11.5px;letter-spacing:.5px;margin-bottom:4px;}
.s-full{color:var(--amber);}.s-half{color:var(--amber);opacity:.5;}.s-empty{color:var(--tx-3);}
.prop-meta-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap;font-size:11.5px;color:var(--tx-3);}

.prop-right{text-align:right;flex-shrink:0;min-width:120px;}
.prop-bid{font-family:var(--fm);font-weight:900;font-size:20px;color:var(--green);margin-bottom:3px;line-height:1;}
.prop-days{font-size:11.5px;color:var(--tx-3);margin-bottom:5px;}
.prop-status{padding:4px 10px;border-radius:7px;font-size:10.5px;font-weight:700;font-family:var(--fm);display:inline-block;}
.pst-pending    {background:rgba(247,183,49,0.1);color:var(--amber);border:1px solid rgba(247,183,49,0.22);}
.pst-shortlisted{background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.pst-accepted   {background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.22);}
.pst-rejected   {background:rgba(255,77,106,0.08);color:var(--red);border:1px solid rgba(255,77,106,0.18);}
.pst-withdrawn  {background:rgba(78,90,110,0.08);color:var(--tx-3);border:1px solid var(--bd);}

/* Compare checkbox */
.prop-check{position:absolute;top:14px;left:14px;width:16px;height:16px;
  accent-color:var(--violet);cursor:pointer;z-index:5;}

/* Bid bar */
.bid-bar-wrap{padding:0 22px 10px;}
.bid-bar-labels{display:flex;justify-content:space-between;font-size:10.5px;color:var(--tx-3);margin-bottom:4px;}
.bid-track{height:4px;background:rgba(255,255,255,0.06);border-radius:2px;overflow:hidden;}
.bid-fill{height:100%;background:linear-gradient(90deg,var(--green),var(--cyan));border-radius:2px;
  transition:width 1s cubic-bezier(.16,1,.3,1);}

/* Trust badges */
.trust-row{display:flex;gap:6px;flex-wrap:wrap;padding:0 22px 10px;}
.tb{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;
  font-size:10.5px;font-weight:700;font-family:var(--fm);}
.tb-ghana {background:var(--green-dim);color:var(--green);border:1px solid rgba(31,217,160,0.2);}
.tb-phone {background:rgba(78,158,255,0.1);color:#4E9EFF;border:1px solid rgba(78,158,255,0.2);}
.tb-email {background:var(--violet-dim);color:var(--violet);border:1px solid var(--violet-border);}
.tb-pro   {background:var(--coral-dim);color:var(--coral);border:1px solid var(--coral-border);}
.tb-rising{background:rgba(247,183,49,0.1);color:var(--amber);border:1px solid rgba(247,183,49,0.2);}

/* Skills row */
.skills-row{display:flex;gap:6px;flex-wrap:wrap;padding:0 22px 10px;}
.sk-pill{padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;
  background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);}

/* Cover letter */
.cov-section{padding:0 22px 10px;}
.cov-label{font-size:10px;font-weight:800;color:var(--tx-3);text-transform:uppercase;
  letter-spacing:.8px;margin-bottom:6px;font-family:var(--fm);}
.cov-box{background:rgba(0,0,0,0.18);border:1px solid var(--bd);border-radius:10px;
  padding:12px 15px;font-size:13px;color:var(--tx-2);line-height:1.7;}
.cov-preview{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.cov-full{display:none;}
.cov-box.expanded .cov-preview{display:none;}
.cov-box.expanded .cov-full{display:block;}
.read-more{background:none;border:none;color:var(--cyan);font-size:11.5px;font-weight:700;
  cursor:pointer;margin-top:6px;padding:0;transition:var(--e);}
.read-more:hover{color:var(--cyan-d);}

/* Card footer / actions */
.prop-actions{display:flex;align-items:center;justify-content:space-between;
  gap:12px;flex-wrap:wrap;padding:12px 22px;border-top:1px solid var(--bd);
  background:rgba(0,0,0,0.08);}
.pa-left{display:flex;gap:6px;flex-wrap:wrap;}
.pa-right{font-size:11px;color:var(--tx-3);}

/* ══ EMPTY STATE ══ */
.empty-state{text-align:center;padding:56px 20px;color:var(--tx-3);}
.empty-state .es-ico{font-size:44px;margin-bottom:12px;}
.empty-state .es-ttl{font-family:var(--fm);font-size:17px;font-weight:700;margin-bottom:5px;color:var(--tx-2);}
.empty-state .es-sub{font-size:13px;max-width:380px;margin:0 auto 20px;line-height:1.7;}
.share-row{display:flex;align-items:center;gap:8px;max-width:440px;margin:0 auto 16px;}
.share-row input{flex:1;background:rgba(0,0,0,0.25);border:1.5px solid var(--bd);border-radius:var(--rs);
  padding:9px 14px;color:var(--tx-2);font-family:var(--fb);font-size:13px;outline:none;}

/* ══ PAGINATION ══ */
.pagination{display:flex;gap:6px;justify-content:center;margin-top:24px;flex-wrap:wrap;}
.pag-btn{padding:7px 14px;border-radius:var(--rs);font-size:13px;font-weight:700;font-family:var(--fm);
  color:var(--tx-3);background:var(--s2);border:1px solid var(--bd);transition:var(--e);text-decoration:none;}
.pag-btn:hover,.pag-btn.active{background:var(--cyan-dim);color:var(--cyan);border-color:var(--cyan-border);}
.pag-btn.disabled{opacity:.3;pointer-events:none;}

/* ══ RECOMMENDED ══ */
.rec-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:18px 22px;}
.rec-card{background:rgba(0,0,0,0.2);border:1px solid var(--bd);border-radius:12px;
  padding:14px;text-align:center;transition:var(--e);text-decoration:none;color:var(--tx);display:block;}
.rec-card:hover{border-color:var(--cyan-border);background:var(--cyan-dim);}
.rc-av{width:42px;height:42px;border-radius:50%;overflow:hidden;margin:0 auto 9px;
  background:linear-gradient(135deg,var(--violet),var(--coral));
  display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:14px;font-weight:800;color:#fff;}
.rc-av img{width:100%;height:100%;object-fit:cover;}
.rc-name{font-family:var(--fm);font-size:12.5px;font-weight:800;margin-bottom:2px;}
.rc-tag{font-size:11px;color:var(--tx-3);margin-bottom:5px;}
.rc-stars{font-size:11px;margin-bottom:3px;}
.rc-rate{font-size:12px;color:var(--cyan);font-weight:700;}
.rc-cta{display:inline-block;margin-top:9px;padding:5px 13px;border-radius:6px;
  font-size:11px;font-weight:700;font-family:var(--fm);
  background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);}
.rec-card:hover .rc-cta{background:var(--cyan);color:#0C0E14;}

/* ══ MODAL ══ */
.modal-bg{display:none;position:fixed;inset:0;z-index:2000;
  background:rgba(0,0,0,0.78);backdrop-filter:blur(16px);
  align-items:center;justify-content:center;padding:16px;overflow-y:auto;}
.modal-bg.open{display:flex;}
.modal-box{background:var(--s2);border:1px solid var(--bd2);border-radius:20px;padding:26px;
  max-width:560px;width:100%;box-shadow:0 30px 80px rgba(0,0,0,0.7);
  animation:mIn .24s cubic-bezier(.34,1.56,.64,1);margin:auto;}
@keyframes mIn{from{transform:scale(.93);opacity:0;}to{transform:scale(1);opacity:1;}}
.modal-box.wide{max-width:720px;}
.modal-head{display:flex;align-items:center;justify-content:space-between;
  margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--bd);}
.modal-title{font-family:var(--fm);font-size:16px;font-weight:800;}
.modal-close{background:none;border:none;color:var(--tx-3);font-size:18px;cursor:pointer;
  padding:4px 8px;border-radius:6px;transition:var(--e);}
.modal-close:hover{color:var(--tx);background:rgba(255,255,255,0.06);}

/* Confirm detail rows */
.cm-ico{font-size:34px;margin-bottom:11px;}
.cm-ttl{font-family:var(--fm);font-size:17px;font-weight:900;margin-bottom:6px;}
.cm-txt{font-size:13px;color:var(--tx-2);line-height:1.7;margin-bottom:16px;}
.cm-detail{background:rgba(0,0,0,0.2);border-radius:10px;padding:14px 16px;margin-bottom:16px;}
.cm-row{display:flex;justify-content:space-between;padding:5px 0;font-size:13px;border-bottom:1px solid var(--bd);}
.cm-row:last-child{border-bottom:none;}
.cm-k{color:var(--tx-3);}
.cm-v{font-weight:700;}
.cm-acts{display:flex;gap:9px;flex-wrap:wrap;justify-content:flex-end;}

/* Detail modal */
.dm-header{display:flex;align-items:flex-start;gap:14px;margin-bottom:18px;}
.dm-av{width:56px;height:56px;border-radius:50%;overflow:hidden;flex-shrink:0;
  background:linear-gradient(135deg,var(--violet),var(--coral));
  display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:19px;font-weight:800;color:#fff;
  border:2px solid rgba(0,212,200,0.25);}
.dm-av img{width:100%;height:100%;object-fit:cover;}
.dm-name{font-family:var(--fm);font-size:17px;font-weight:900;margin-bottom:3px;}
.dm-tag{font-size:12.5px;color:var(--tx-3);}
.dm-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin:16px 0;}
.dm-stat{background:rgba(0,0,0,0.2);border-radius:9px;padding:11px;text-align:center;}
.dm-sv{font-family:var(--fm);font-size:18px;font-weight:900;margin-bottom:2px;}
.dm-sl{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.4px;}
.dm-sec-ttl{font-size:10px;font-weight:800;color:var(--tx-3);text-transform:uppercase;letter-spacing:.8px;margin:14px 0 8px;font-family:var(--fm);}
.dm-cl{background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:10px;padding:13px 15px;
  font-size:13px;color:var(--tx-2);line-height:1.75;max-height:200px;overflow-y:auto;}
.rv-item{background:rgba(0,0,0,0.14);border:1px solid var(--bd);border-radius:9px;padding:11px 13px;margin-bottom:8px;}
.rv-item:last-child{margin-bottom:0;}
.rv-top{display:flex;justify-content:space-between;margin-bottom:4px;}
.rv-name{font-family:var(--fm);font-size:12px;font-weight:800;}
.rv-stars{font-size:12px;color:var(--amber);}
.rv-comment{font-size:12px;color:var(--tx-3);line-height:1.6;}
.dm-acts{display:flex;gap:8px;flex-wrap:wrap;padding-top:14px;border-top:1px solid var(--bd);margin-top:14px;}

/* Compare table */
.compare-table{width:100%;border-collapse:collapse;font-size:13px;}
.compare-table th,.compare-table td{padding:11px 14px;border:1px solid var(--bd);text-align:center;}
.compare-table th{background:rgba(0,0,0,0.2);font-family:var(--fm);font-size:10.5px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:var(--tx-3);}
.compare-table th:first-child,.compare-table td:first-child{text-align:left;font-weight:700;color:var(--tx-2);}
.ct-best{color:var(--green);font-weight:800;font-family:var(--fm);}
.ct-prov-name{font-family:var(--fm);font-weight:800;font-size:12.5px;}

/* ══ TOAST ══ */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);
  padding:12px 15px;border-radius:var(--rs);max-width:320px;box-shadow:0 14px 40px rgba(0,0,0,.55);
  animation:tIn .3s ease;backdrop-filter:blur(18px);transition:background .3s;}
.toast.ok{border-left:3px solid var(--green);}
.toast.err{border-left:3px solid var(--red);}
.toast.info{border-left:3px solid var(--cyan);}
.t-ico{font-size:15px;flex-shrink:0;}.t-body{flex:1;}
.t-ttl{font-family:var(--fm);font-weight:800;font-size:12px;margin-bottom:1px;}
.t-msg{font-size:11px;color:var(--tx-3);}.t-cls{color:var(--tx-3);font-size:16px;cursor:pointer;}
@keyframes tIn{from{opacity:0;transform:translateX(48px);}to{opacity:1;transform:translateX(0);}}

/* ══ MOBILE BOTTOM NAV (identical to dashboard) ══ */
.mobile-nav{display:none;position:fixed;bottom:0;left:0;right:0;z-index:500;
  background:rgba(12,14,20,0.97);backdrop-filter:blur(20px);
  border-top:1px solid var(--bd);padding:8px 0 env(safe-area-inset-bottom);
  grid-template-columns:repeat(5,1fr);transition:background .3s,border-color .3s;}
.mn-item{display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 4px;
  cursor:pointer;transition:var(--e);text-decoration:none;color:var(--tx-3);position:relative;}
.mn-item.active{color:var(--cyan);}
.mn-item:hover{color:var(--tx);}
.mn-ico{font-size:20px;}.mn-lbl{font-size:9px;font-weight:600;font-family:var(--fm);text-transform:uppercase;letter-spacing:.3px;}
.mn-badge{position:absolute;top:2px;right:14px;background:var(--coral);color:#fff;font-size:8px;font-weight:800;padding:1px 5px;border-radius:50px;font-family:var(--fm);}

/* ══ RESPONSIVE ══ */
@media(max-width:1024px){.analytics-strip{grid-template-columns:repeat(2,1fr);}.rec-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){
  .sidebar{display:none;}.main{margin-left:0;}
  .mobile-nav{display:grid;}
  .content{padding:18px 14px 90px;}
  .topbar{padding:0 16px;}
  .job-banner{flex-direction:column;}
  .jb-acts{width:100%;}
  .pr-main{flex-wrap:wrap;}
  .prop-right{flex-direction:row;align-items:center;gap:14px;width:100%;text-align:left;}
}
@media(max-width:480px){
  .analytics-strip{grid-template-columns:1fr 1fr;}
  .rec-grid{grid-template-columns:1fr 1fr;}
}
</style>
</head>
<!-- Apply .lm server-side so no flash on load -->
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<!-- ══════════ SIDEBAR ══════════ -->
<aside class="sidebar">
  <a href="<?= APP_URL ?>/index.php" class="sb-logo">
    <div class="sb-logo-mark">G</div>
    <span class="sb-logo-text">Gig<span>Ghana</span></span>
  </a>
  <nav class="sb-nav">
    <div class="sb-section">Client</div>
    <a href="<?= APP_URL ?>/client/dashboard.php"  class="sb-item">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/client/post-job.php"   class="sb-item">✏️ Post a Job</a>
    <a href="<?= APP_URL ?>/client/my-jobs.php"    class="sb-item">
      💼 My Jobs
      <?php if($activeJobs > 0): ?><span class="sb-badge cyan"><?= $activeJobs ?></span><?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/client/proposals.php"  class="sb-item active">
      📩 Proposals
      <?php if(($statusCounts['pending']??0) > 0): ?><span class="sb-badge"><?= $statusCounts['pending'] ?></span><?php endif; ?>
    </a>
    <div class="sb-section">Communication</div>
    <a href="<?= APP_URL ?>/client/messages.php"   class="sb-item">
      💬 Messages
      <?php if($unreadMsgs > 0): ?><span class="sb-badge"><?= $unreadMsgs ?></span><?php endif; ?>
    </a>
    <a href="<?= APP_URL ?>/search/providers.php"  class="sb-item">🔍 Find Talent</a>
    <div class="sb-section">Finance</div>
    <a href="<?= APP_URL ?>/client/payments.php"   class="sb-item">💳 Payments</a>
    <a href="<?= APP_URL ?>/client/escrow.php"     class="sb-item">🔒 Escrow</a>
    <div class="sb-section">Account</div>
    <a href="<?= APP_URL ?>/client/settings.php"   class="sb-item">⚙️ Settings</a>
    <a href="<?= APP_URL ?>/index.php"             class="sb-item">🏠 Homepage</a>
    <a href="<?= APP_URL ?>/auth/logout.php"       class="sb-item danger">🚪 Sign Out</a>
  </nav>
  <div class="sb-user">
    <div class="sb-user-card">
      <div class="sb-av">
        <?php if(!empty($myAvatar)): ?><img src="<?= sanitize($myAvatar) ?>" alt="">
        <?php else: echo $myInit; endif; ?>
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
      <h1>📩 Proposals<?= $activeJob ? ' — <span style="color:var(--cyan);font-size:16px;">'.sanitize(mb_substr($activeJob['title'],0,42)).'</span>' : '' ?></h1>
      <p><?= $filterJob ? date('l, F j, Y') : 'All proposals across your jobs' ?></p>
    </div>
    <div class="topbar-right">
      <button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle theme">
        <?= $isLight ? '☀️' : '🌙' ?>
      </button>
      <a href="<?= APP_URL ?>/client/dashboard.php" class="btn btn-ghost">📊 Dashboard</a>
      <a href="<?= APP_URL ?>/client/post-job.php"  class="btn btn-coral">+ Post Job</a>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <?php if($flashOk): ?>
    <div class="alert ok" id="flashAlert">✅ <?= htmlspecialchars($flashOk) ?></div>
    <?php endif; ?>
    <?php if($flashErr): ?>
    <div class="alert err" id="flashAlert">⚠️ <?= htmlspecialchars($flashErr) ?></div>
    <?php endif; ?>

    <!-- ══ JOB SUMMARY BANNER ══ -->
    <?php if($activeJob):
      $jIco    = $iconMap[$activeJob['cat_icon']??''] ?? '💼';
      $jBudg   = formatCurrency($activeJob['budget_min']);
      if(($activeJob['budget_max']??0) > $activeJob['budget_min']) $jBudg .= ' – '.formatCurrency($activeJob['budget_max']);
      if(($activeJob['budget_type']??'')==='hourly') $jBudg .= '/hr';
      $jStClass = match($activeJob['status']??'open'){'open'=>'jst-open','in_progress'=>'jst-progress','completed'=>'jst-completed',default=>'jst-other'};
      $jStLabel = match($activeJob['status']??'open'){'open'=>'● Open','in_progress'=>'🔄 In Progress','completed'=>'✅ Done',default=>ucfirst(str_replace('_',' ',$activeJob['status']))};
    ?>
    <div class="job-banner">
      <div class="jb-glow"></div>
      <div style="position:relative;z-index:1;flex:1;min-width:0;">
        <div class="jb-badge"><?= $jIco ?> <?= sanitize($activeJob['cat_name']??'Job') ?></div>
        <div class="jb-title"><?= sanitize($activeJob['title']) ?></div>
        <div class="jb-meta">
          <span class="jb-budget"><?= $jBudg ?></span>
          <span>📩 <?= $statusCounts['all'] ?? 0 ?> proposals</span>
          <span>🕒 <?= timeAgo($activeJob['created_at']) ?></span>
          <?php if($activeJob['deadline']??null):?>
          <span>📅 Deadline <?= date('M j, Y',strtotime($activeJob['deadline'])) ?></span>
          <?php endif;?>
          <span class="jst <?= $jStClass ?>"><?= $jStLabel ?></span>
        </div>
      </div>
      <div class="jb-acts">
        <a href="<?= APP_URL ?>/job-details.php?id=<?= $activeJob['id'] ?>" class="btn btn-ghost btn-sm" target="_blank">👁 View Job</a>
        <?php if(in_array($activeJob['status']??'open',['open','draft'])):?>
        <a href="<?= APP_URL ?>/client/post-job.php?edit=<?= $activeJob['id'] ?>" class="btn btn-ghost btn-sm">✏️ Edit</a>
        <?php endif;?>
        <a href="<?= APP_URL ?>/client/my-jobs.php" class="btn btn-ghost btn-sm">← All Jobs</a>
      </div>
    </div>

    <!-- ══ ANALYTICS STRIP ══ -->
    <div class="analytics-strip">
      <div class="an-item">
        <div class="an-val" style="color:var(--cyan);"><?= (int)$analytics['total'] ?></div>
        <div class="an-lbl">Total Proposals</div>
      </div>
      <div class="an-item">
        <div class="an-val" style="color:var(--violet);"><?= (int)$analytics['shortlisted'] ?></div>
        <div class="an-lbl">Shortlisted</div>
      </div>
      <div class="an-item">
        <div class="an-val" style="color:var(--green);"><?= $analytics['avg_bid']>0 ? formatCurrency((float)$analytics['avg_bid']) : '—' ?></div>
        <div class="an-lbl">Avg Bid</div>
      </div>
      <div class="an-item">
        <div class="an-val" style="color:var(--amber);"><?= $analytics['min_bid']>0 ? formatCurrency((float)$analytics['min_bid']) : '—' ?></div>
        <div class="an-lbl">Lowest Bid</div>
      </div>
    </div>
    <?php endif; ?>

    <!-- ══ FILTER BAR ══ -->
    <form method="GET" id="filterForm">
      <?php if($filterJob):?><input type="hidden" name="job_id" value="<?= $filterJob ?>"><?php endif;?>
      <div class="filter-bar">
        <?php if(!$filterJob):?>
        <select name="job_id" class="field-select" style="min-width:220px;" onchange="this.form.submit()">
          <option value="">All Jobs</option>
          <?php foreach($clientJobs as $cj): ?>
          <option value="<?= $cj['id'] ?>" <?= $filterJob===(int)$cj['id']?'selected':'' ?>>
            <?= sanitize(mb_substr($cj['title'],0,42)) ?> (<?= $cj['pc'] ?>)
          </option>
          <?php endforeach;?>
        </select>
        <?php endif;?>

        <div class="search-box">
          <span style="color:var(--tx-3);font-size:14px;">🔍</span>
          <input type="text" name="q" placeholder="Search freelancer or cover letter…"
                 value="<?= htmlspecialchars($search) ?>" autocomplete="off" id="searchInput">
        </div>

        <select name="sort" class="field-select" onchange="this.form.submit()">
          <option value="newest"   <?= $sortBy==='newest'  ?'selected':'' ?>>Newest First</option>
          <option value="bid_low"  <?= $sortBy==='bid_low' ?'selected':'' ?>>Bid: Low → High</option>
          <option value="bid_high" <?= $sortBy==='bid_high'?'selected':'' ?>>Bid: High → Low</option>
          <option value="rating"   <?= $sortBy==='rating'  ?'selected':'' ?>>Highest Rated</option>
          <option value="delivery" <?= $sortBy==='delivery'?'selected':'' ?>>Fastest Delivery</option>
        </select>
        <input type="hidden" name="status" value="<?= htmlspecialchars($filterStat) ?>">
        <button type="submit" class="btn btn-cyan btn-sm">Search</button>
        <?php if($search || $filterStat!=='all'):?>
        <a href="<?= APP_URL.'/client/proposals.php'.($filterJob?'?job_id='.$filterJob:'') ?>" class="btn btn-ghost btn-sm">✕ Clear</a>
        <?php endif;?>
      </div>
    </form>

    <!-- ══ STATUS TABS ══ -->
    <div class="tabs-row">
      <?php foreach(['all'=>['All Proposals','📩'],'pending'=>['Pending','⏳'],'shortlisted'=>['Shortlisted','📌'],'accepted'=>['Accepted','✅'],'rejected'=>['Declined','✕']] as $key=>[$lbl,$ico]):
        $cnt  = $statusCounts[$key] ?? 0;
        $isAc = $filterStat === $key;
        $href = APP_URL.'/client/proposals.php?'.http_build_query(array_filter(['job_id'=>$filterJob,'status'=>$key,'sort'=>$sortBy,'q'=>$search]));
      ?>
      <a href="<?= $href ?>" class="tab-pill <?= $isAc?'active':'' ?> <?= (!$isAc&&$cnt>0&&$key==='pending')?'has-new':'' ?>">
        <?= $ico ?> <?= $lbl ?> <span class="tp-cnt"><?= $cnt ?></span>
      </a>
      <?php endforeach;?>
    </div>

    <!-- ══ COMPARE BAR ══ -->
    <div class="compare-bar" id="compareBar">
      <span class="compare-count" id="compareCnt">0 selected</span>
      <button class="btn btn-violet btn-sm" onclick="openCompare()">⚖️ Compare</button>
      <button class="btn btn-ghost btn-sm" onclick="clearCompare()">✕ Clear</button>
    </div>

    <!-- ══ PROPOSALS LIST ══ -->
    <?php if(empty($proposals)): ?>
    <div class="section-card">
      <div class="empty-state">
        <div class="es-ico">📭</div>
        <div class="es-ttl"><?= $filterStat==='all' ? 'No proposals yet' : 'No '.htmlspecialchars($filterStat).' proposals' ?></div>
        <p class="es-sub">
          <?php if($filterStat==='all'): ?>
            Freelancers are reviewing your job. Share the link to attract more talent.
          <?php else: ?>
            No proposals match this filter. <a href="<?= APP_URL.'/client/proposals.php'.($filterJob?'?job_id='.$filterJob:'') ?>" style="color:var(--cyan);">View all →</a>
          <?php endif;?>
        </p>
        <?php if($filterStat==='all' && $filterJob):?>
        <div class="share-row">
          <input type="text" id="shareUrl" value="<?= APP_URL ?>/job-details.php?id=<?= $filterJob ?>" readonly>
          <button class="btn btn-cyan" onclick="copyShare()">📋 Copy Link</button>
        </div>
        <?php endif;?>
        <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-coral btn-lg">✏️ Post a New Job</a>
      </div>
    </div>

    <?php else: ?>

    <div class="section-card">
      <div class="sc-head">
        <div>
          <div class="card-ttl">📩 Proposals Received</div>
          <div class="card-cnt"><?= $totalProps ?> total · page <?= $page ?> of <?= max(1,$totalPages) ?></div>
        </div>
        <?php if(!empty($proposals)):?>
        <div style="font-size:12px;color:var(--tx-3);">
          ☑️ Select up to 3 to compare
        </div>
        <?php endif;?>
      </div>

      <?php foreach($proposals as $idx => $p):
        $sc     = ['pst-pending','pst-shortlisted','pst-accepted','pst-rejected','pst-withdrawn'];
        $stCls  = 'pst-'.str_replace(' ','-',$p['status']??'pending');
        $stLbl  = match($p['status']??'pending'){'pending'=>'⏳ Pending','shortlisted'=>'📌 Shortlisted','accepted'=>'✅ Accepted','rejected'=>'✕ Declined','withdrawn'=>'↩ Withdrawn',default=>'⏳ Pending'};
        $pInit  = ini2($p['first_name'],$p['last_name']);
        $rv     = (float)$p['rating_avg'];
        $prCls  = match($p['status']??'pending'){'accepted'=>'pr-accepted','shortlisted'=>'pr-shortlisted','rejected'=>'pr-rejected',default=>''};
        $pct    = ($activeJob && (float)($activeJob['budget_max']??0) > (float)($activeJob['budget_min']??0))
                  ? min(100,max(0,((float)$p['bid_amount']-(float)$activeJob['budget_min'])/((float)$activeJob['budget_max']-(float)$activeJob['budget_min'])*100))
                  : 50;
      ?>
      <div class="prop-row <?= $prCls ?>" id="prow<?= $p['id'] ?>">

        <!-- Compare checkbox -->
        <input type="checkbox" class="prop-check" value="<?= $p['id'] ?>"
               data-name="<?= htmlspecialchars($p['first_name'].' '.$p['last_name']) ?>"
               data-bid="<?= $p['bid_amount'] ?>"
               data-del="<?= $p['delivery_days'] ?>"
               data-rat="<?= $rv ?>"
               data-jobs="<?= $p['completed_jobs'] ?>"
               data-uid="<?= $p['uid'] ?>"
               data-pid="<?= $p['id'] ?>"
               data-jid="<?= $p['job_id'] ?>"
               onchange="toggleCompare(this)">

        <!-- MAIN ROW — mirrors dashboard prop-row layout -->
        <div class="pr-main" style="padding-left:38px;">
          <!-- Avatar -->
          <div class="prop-av">
            <?php if($p['avatar']):?><img src="<?= sanitize($p['avatar']) ?>" alt="" loading="lazy">
            <?php else: echo $pInit; endif;?>
            <?php if(!$p['client_viewed']):?><div class="prop-av-new">NEW</div><?php endif;?>
          </div>

          <!-- Info -->
          <div class="prop-info">
            <div class="prop-name">
              <a href="<?= APP_URL ?>/profile.php?id=<?= $p['uid'] ?>" target="_blank">
                <?= sanitize($p['first_name'].' '.$p['last_name']) ?>
              </a>
              <?php if($p['is_verified']):?><span class="verify-badge">✓ Verified</span><?php endif;?>
            </div>
            <div class="prop-tag"><?= sanitize($p['tagline']??ucfirst($p['experience_level']??'').' Freelancer') ?></div>
            <?php if(!$filterJob):?>
            <div class="prop-job-ctx">📋 <a href="<?= APP_URL ?>/client/proposals.php?job_id=<?= $p['job_id'] ?>"><?= sanitize(mb_substr($p['job_title'],0,48)) ?><?= mb_strlen($p['job_title'])>48?'…':'' ?></a></div>
            <?php endif;?>
            <div class="prop-stars"><?= starsHtml($rv) ?> <span style="color:var(--tx-3);font-size:11px;"><?= number_format($rv,1) ?> (<?= (int)$p['rating_count'] ?>)</span></div>
            <div class="prop-meta-row">
              <span>✅ <?= (int)$p['completed_jobs'] ?> jobs</span>
              <?php if($p['location']):?><span>📍 <?= sanitize($p['location']) ?></span><?php endif;?>
              <?php if($p['response_time']):?><span>⏱ <?= sanitize($p['response_time']) ?></span><?php endif;?>
              <?php if(($p['experience_level']??'')):?><span>🏆 <?= ucfirst($p['experience_level']) ?></span><?php endif;?>
            </div>
          </div>

          <!-- Bid + Status (right side — mirrors dashboard) -->
          <div class="prop-right">
            <div class="prop-bid"><?= formatCurrency($p['bid_amount']) ?></div>
            <div class="prop-days">⏱ <?= (int)$p['delivery_days'] ?> day<?= $p['delivery_days']!=1?'s':'' ?></div>
            <span class="prop-status <?= $stCls ?>"><?= $stLbl ?></span>
          </div>
        </div>

        <!-- BID VS BUDGET BAR -->
        <?php if($activeJob && (float)($activeJob['budget_max']??0) > 0):?>
        <div class="bid-bar-wrap">
          <div class="bid-bar-labels">
            <span>Min <?= formatCurrency($activeJob['budget_min']) ?></span>
            <span style="color:var(--green);font-weight:700;">Bid: <?= formatCurrency($p['bid_amount']) ?></span>
            <span>Max <?= formatCurrency($activeJob['budget_max']) ?></span>
          </div>
          <div class="bid-track"><div class="bid-fill" style="width:<?= $pct ?>%"></div></div>
        </div>
        <?php endif;?>

        <!-- TRUST BADGES -->
        <div class="trust-row">
          <?php if($p['ghana_card_verified']):?><span class="tb tb-ghana">🇬🇭 Ghana Card ✓</span><?php endif;?>
          <?php if($p['phone_verified']):?><span class="tb tb-phone">📱 Phone ✓</span><?php endif;?>
          <?php if($p['email_verified']):?><span class="tb tb-email">✉️ Email ✓</span><?php endif;?>
          <?php if((int)$p['completed_jobs']>=20):?><span class="tb tb-pro">⭐ Premium Pro</span>
          <?php elseif((int)$p['completed_jobs']>=5):?><span class="tb tb-rising">📈 Rising Talent</span><?php endif;?>
        </div>

        <!-- SKILLS -->
        <?php if(!empty($p['skills'])):?>
        <div class="skills-row">
          <?php foreach(array_slice($p['skills'],0,5) as $sk):?>
          <span class="sk-pill"><?= sanitize($sk) ?></span>
          <?php endforeach;?>
          <?php if(count($p['skills'])>5):?><span style="font-size:11px;color:var(--tx-3);align-self:center;">+<?= count($p['skills'])-5 ?> more</span><?php endif;?>
        </div>
        <?php endif;?>

        <!-- COVER LETTER (preview — same collapsed pattern as dashboard) -->
        <div class="cov-section">
          <div class="cov-label">Cover Letter</div>
          <div class="cov-box" id="cov<?= $p['id'] ?>">
            <div class="cov-preview"><?= nl2br(htmlspecialchars(mb_substr($p['cover_letter'],0,300))) ?></div>
            <?php if(mb_strlen($p['cover_letter']) > 300):?>
            <div class="cov-full"><?= nl2br(htmlspecialchars($p['cover_letter'])) ?></div>
            <button class="read-more" onclick="toggleCov(<?= $p['id'] ?>,this)">Read more →</button>
            <?php endif;?>
          </div>
        </div>

        <!-- ACTION ROW — proposal buttons identical to dashboard -->
        <div class="prop-actions">
          <div class="pa-left">
            <button class="btn btn-ghost btn-sm" onclick="openDetail(<?= $p['id'] ?>)">📄 View Full</button>
            <a href="<?= APP_URL ?>/profile.php?id=<?= $p['uid'] ?>" class="btn btn-ghost btn-sm" target="_blank">👤 Profile</a>
            <?php if($p['conv_id']):?>
            <a href="<?= APP_URL ?>/client/messages.php?conv=<?= $p['conv_id'] ?>" class="btn btn-violet btn-sm">💬 Chat</a>
            <?php else:?>
            <a href="<?= APP_URL ?>/client/messages.php?start=<?= $p['uid'] ?>" class="btn btn-violet btn-sm">💬 Chat</a>
            <?php endif;?>

            <?php if(in_array($p['status'],['pending'])):?>
            <button class="btn btn-amber btn-sm" onclick="openConfirm('shortlist',<?= $p['id'] ?>,<?= $p['job_id'] ?>,'<?= addslashes(sanitize($p['first_name'].' '.$p['last_name'])) ?>','<?= formatCurrency($p['bid_amount']) ?>','<?= (int)$p['delivery_days'] ?>')">
              📌 Shortlist
            </button>
            <button class="btn btn-green btn-sm" onclick="openConfirm('accept',<?= $p['id'] ?>,<?= $p['job_id'] ?>,'<?= addslashes(sanitize($p['first_name'].' '.$p['last_name'])) ?>','<?= formatCurrency($p['bid_amount']) ?>','<?= (int)$p['delivery_days'] ?>')">
              ✅ Hire
            </button>
            <button class="btn btn-red-soft btn-sm" onclick="openConfirm('reject',<?= $p['id'] ?>,<?= $p['job_id'] ?>,'<?= addslashes(sanitize($p['first_name'].' '.$p['last_name'])) ?>','<?= formatCurrency($p['bid_amount']) ?>','<?= (int)$p['delivery_days'] ?>')">
              ✕ Decline
            </button>
            <?php elseif(in_array($p['status'],['shortlisted'])):?>
            <button class="btn btn-green btn-sm" onclick="openConfirm('accept',<?= $p['id'] ?>,<?= $p['job_id'] ?>,'<?= addslashes(sanitize($p['first_name'].' '.$p['last_name'])) ?>','<?= formatCurrency($p['bid_amount']) ?>','<?= (int)$p['delivery_days'] ?>')">
              ✅ Hire
            </button>
            <button class="btn btn-red-soft btn-sm" onclick="openConfirm('reject',<?= $p['id'] ?>,<?= $p['job_id'] ?>,'<?= addslashes(sanitize($p['first_name'].' '.$p['last_name'])) ?>','<?= formatCurrency($p['bid_amount']) ?>','<?= (int)$p['delivery_days'] ?>')">
              ✕ Decline
            </button>
            <button class="btn btn-ghost btn-sm" onclick="openConfirm('unshortlist',<?= $p['id'] ?>,<?= $p['job_id'] ?>,'<?= addslashes(sanitize($p['first_name'].' '.$p['last_name'])) ?>','<?= formatCurrency($p['bid_amount']) ?>','<?= (int)$p['delivery_days'] ?>')">
              ↩ Unshortlist
            </button>
            <?php endif;?>
          </div>
          <div class="pa-right"><?= timeAgo($p['created_at']) ?></div>
        </div>

      </div><!-- /prop-row -->
      <?php endforeach;?>
    </div><!-- /section-card -->

    <!-- PAGINATION -->
    <?php if($totalPages > 1):
      $qBase = http_build_query(array_filter(['job_id'=>$filterJob,'status'=>$filterStat,'sort'=>$sortBy,'q'=>$search]));
    ?>
    <div class="pagination">
      <?php if($page>1):?><a href="?<?=$qBase?>&page=<?=$page-1?>" class="pag-btn">← Prev</a><?php else:?><span class="pag-btn disabled">← Prev</span><?php endif;?>
      <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
      <a href="?<?=$qBase?>&page=<?=$i?>" class="pag-btn <?=$i===$page?'active':''?>"><?=$i?></a>
      <?php endfor;?>
      <?php if($page<$totalPages):?><a href="?<?=$qBase?>&page=<?=$page+1?>" class="pag-btn">Next →</a><?php else:?><span class="pag-btn disabled">Next →</span><?php endif;?>
    </div>
    <?php endif;?>
    <?php endif; /* end proposals list */?>

    <!-- ══ RECOMMENDED PROVIDERS ══ -->
    <?php if(!empty($recommended)):?>
    <div class="section-card">
      <div class="sc-head">
        <div>
          <div class="card-ttl">✨ Recommended Talent</div>
          <div class="card-cnt">Based on this job's category — not yet applied</div>
        </div>
        <a href="<?= APP_URL ?>/search/providers.php<?= $activeJob&&($activeJob['category_id']??0)?'?category='.$activeJob['category_id']:'' ?>" class="btn btn-ghost btn-sm">Browse All →</a>
      </div>
      <div class="rec-grid">
        <?php foreach($recommended as $r):
          $rInit = ini2($r['first_name'],$r['last_name']);
          $rRv   = (float)$r['rating_avg'];
        ?>
        <a href="<?= APP_URL ?>/profile.php?id=<?= $r['uid'] ?>" class="rec-card">
          <div class="rc-av">
            <?php if($r['avatar']):?><img src="<?= sanitize($r['avatar']) ?>" alt="" loading="lazy"><?php else: echo $rInit; endif;?>
          </div>
          <div class="rc-name"><?= sanitize($r['first_name'].' '.$r['last_name']) ?></div>
          <div class="rc-tag"><?= sanitize($r['tagline']??ucfirst($r['experience_level']??'').' Pro') ?></div>
          <div class="rc-stars"><?= starsHtml($rRv) ?> <?= number_format($rRv,1) ?></div>
          <?php if((float)$r['hourly_rate']>0):?><div class="rc-rate"><?= formatCurrency($r['hourly_rate']) ?>/hr</div><?php endif;?>
          <div class="rc-cta">Invite to Bid →</div>
        </a>
        <?php endforeach;?>
      </div>
    </div>
    <?php endif;?>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ══ MOBILE BOTTOM NAV ══ -->
<nav class="mobile-nav">
  <a href="<?= APP_URL ?>/client/dashboard.php"  class="mn-item"><div class="mn-ico">📊</div><div class="mn-lbl">Home</div></a>
  <a href="<?= APP_URL ?>/client/my-jobs.php"    class="mn-item">
    <div class="mn-ico">💼</div><div class="mn-lbl">Jobs</div>
    <?php if($activeJobs>0):?><span class="mn-badge"><?=$activeJobs?></span><?php endif;?>
  </a>
  <a href="<?= APP_URL ?>/client/post-job.php"   class="mn-item">
    <div class="mn-ico" style="background:var(--coral);border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:-6px;font-size:22px;">+</div>
    <div class="mn-lbl">Post</div>
  </a>
  <a href="<?= APP_URL ?>/client/messages.php"   class="mn-item">
    <div class="mn-ico">💬</div><div class="mn-lbl">Messages</div>
    <?php if($unreadMsgs>0):?><span class="mn-badge"><?=$unreadMsgs?></span><?php endif;?>
  </a>
  <a href="<?= APP_URL ?>/client/proposals.php"  class="mn-item active">
    <div class="mn-ico">📩</div><div class="mn-lbl">Bids</div>
    <?php if(($statusCounts['pending']??0)>0):?><span class="mn-badge"><?=$statusCounts['pending']?></span><?php endif;?>
  </a>
</nav>

<!-- ══ CONFIRM MODAL ══ -->
<div class="modal-bg" id="confirmModal">
  <div class="modal-box">
    <div class="cm-ico" id="cmIco">⏳</div>
    <div class="cm-ttl" id="cmTtl">Confirm Action</div>
    <p class="cm-txt" id="cmTxt">Are you sure?</p>
    <div class="cm-detail">
      <div class="cm-row"><span class="cm-k">Freelancer</span><span class="cm-v" id="cmProv">—</span></div>
      <div class="cm-row"><span class="cm-k">Bid Amount</span><span class="cm-v" id="cmBid">—</span></div>
      <div class="cm-row"><span class="cm-k">Delivery</span><span class="cm-v" id="cmDel">—</span></div>
    </div>
    <form method="POST" action="<?= APP_URL ?>/client/proposals.php" id="confirmForm">
      <input type="hidden" name="csrf_token"  value="<?= $csrf ?>">
      <input type="hidden" name="action"      id="cfAction">
      <input type="hidden" name="proposal_id" id="cfPropId">
      <input type="hidden" name="job_id"      id="cfJobId">
      <input type="hidden" name="curr_status" value="<?= htmlspecialchars($filterStat) ?>">
      <input type="hidden" name="curr_sort"   value="<?= htmlspecialchars($sortBy) ?>">
      <input type="hidden" name="curr_q"      value="<?= htmlspecialchars($search) ?>">
      <div class="cm-acts">
        <button type="button" class="btn btn-ghost" onclick="closeModal('confirmModal')">Cancel</button>
        <button type="submit" class="btn" id="cmConfirmBtn">Confirm</button>
      </div>
    </form>
  </div>
</div>

<!-- ══ DETAIL MODAL ══ -->
<div class="modal-bg" id="detailModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="detailTitle">Proposal Details</div>
      <button class="modal-close" onclick="closeModal('detailModal')">×</button>
    </div>
    <div id="detailBody" style="color:var(--tx-2);">Loading…</div>
    <div class="dm-acts" id="detailActs"></div>
  </div>
</div>

<!-- ══ COMPARE MODAL ══ -->
<div class="modal-bg" id="compareModal">
  <div class="modal-box wide">
    <div class="modal-head">
      <div class="modal-title">⚖️ Freelancer Comparison</div>
      <button class="modal-close" onclick="closeModal('compareModal')">×</button>
    </div>
    <div style="overflow-x:auto;">
      <table class="compare-table" id="compareTable">
        <thead><tr id="compareThead"></tr></thead>
        <tbody id="compareTbody"></tbody>
      </table>
    </div>
    <div style="display:flex;gap:9px;margin-top:14px;flex-wrap:wrap;" id="compareHire"></div>
  </div>
</div>

<div id="toast-c"></div>

<!-- ══ PROPOSAL DATA FOR JS ══ -->
<script>
const PROPOSALS_DATA = <?php
  $jsD = [];
  foreach($proposals as $p) $jsD[] = [
    'id'=>$p['id'],'uid'=>$p['uid'],'job_id'=>$p['job_id'],
    'fname'=>$p['first_name'],'lname'=>$p['last_name'],
    'avatar'=>$p['avatar']??'','tagline'=>$p['tagline']??'',
    'rating_avg'=>(float)$p['rating_avg'],'rating_count'=>(int)$p['rating_count'],
    'completed_jobs'=>(int)$p['completed_jobs'],'bid_amount'=>(float)$p['bid_amount'],
    'delivery_days'=>(int)$p['delivery_days'],'cover_letter'=>$p['cover_letter'],
    'status'=>$p['status'],'is_verified'=>(bool)$p['is_verified'],
    'ghana_card_verified'=>(bool)$p['ghana_card_verified'],
    'phone_verified'=>(bool)$p['phone_verified'],'email_verified'=>(bool)$p['email_verified'],
    'experience_level'=>$p['experience_level']??'','languages'=>$p['languages']??'English',
    'response_time'=>$p['response_time']??'','skills'=>$p['skills'],
    'portfolio_urls'=>$p['portfolio_urls']??'','conv_id'=>$p['conv_id']??null,
    'reviews'=>array_map(fn($r)=>['rating'=>(float)$r['rating_overall'],
        'comment'=>$r['comment']??'','fname'=>$r['rev_fname'],'lname'=>$r['rev_lname']],
      $p['reviews']),
  ];
  echo json_encode($jsD,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG);
?>;
const APP_URL  = '<?= APP_URL ?>';
const CSRF     = '<?= $csrf ?>';
const CURR_STATUS = '<?= htmlspecialchars($filterStat) ?>';
const CURR_SORT   = '<?= htmlspecialchars($sortBy) ?>';
const CURR_Q      = '<?= htmlspecialchars($search) ?>';
const FILTER_JOB  = <?= $filterJob ?>;

/* ── Theme toggle (identical to dashboard) ── */
function toggleTheme(){
  const isLight = document.getElementById('appBody').classList.toggle('lm');
  const val = isLight ? 'light' : 'dark';
  localStorage.setItem('gg_theme',val);
  document.cookie = `gg_theme=${val};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = isLight ? '☀️' : '🌙';
  showToast('Theme',isLight?'☀️ Light mode on':'🌙 Dark mode on','info',2000);
}
(function(){
  const s = localStorage.getItem('gg_theme') || '<?= $isLight?"light":"dark" ?>';
  const b = document.getElementById('appBody');
  const btn = document.getElementById('themeBtn');
  if(s==='light'){ b.classList.add('lm'); if(btn) btn.textContent='☀️'; }
  else { b.classList.remove('lm'); if(btn) btn.textContent='🌙'; }
})();

/* ── Live search ── */
let st;
document.getElementById('searchInput')?.addEventListener('input',function(){
  clearTimeout(st); st = setTimeout(()=>document.getElementById('filterForm').submit(),700);
});

/* ── Cover letter expand ── */
function toggleCov(id, btn){
  const el = document.getElementById('cov'+id);
  el.classList.toggle('expanded');
  btn.textContent = el.classList.contains('expanded') ? '← Show less' : 'Read more →';
}

/* ── Modal helpers ── */
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-bg').forEach(m =>
  m.addEventListener('click', e => { if(e.target===m) m.classList.remove('open'); }));
document.addEventListener('keydown', e => {
  if(e.key==='Escape') document.querySelectorAll('.modal-bg.open').forEach(m=>m.classList.remove('open'));
});

/* ── Confirm action modal ── */
const CFG = {
  shortlist:   {ico:'📌',ttl:'Shortlist Proposal',txt:'Move to your shortlist for further review.',btn:'📌 Shortlist',cls:'btn-amber'},
  accept:      {ico:'✅',ttl:'Accept & Hire Freelancer',txt:'This hires the freelancer, sets the job to In Progress, and notifies all other applicants. This cannot be undone.',btn:'✅ Accept & Hire',cls:'btn-green'},
  reject:      {ico:'✕', ttl:'Decline Proposal',txt:'The freelancer will be notified that their proposal was not selected.',btn:'✕ Decline',cls:'btn-red-soft'},
  unshortlist: {ico:'↩', ttl:'Remove from Shortlist',txt:'Moves the proposal back to pending.',btn:'↩ Remove',cls:'btn-ghost'},
};
function openConfirm(action,pid,jid,prov,bid,days){
  const c = CFG[action];
  document.getElementById('cmIco').textContent = c.ico;
  document.getElementById('cmTtl').textContent = c.ttl;
  document.getElementById('cmTxt').textContent = c.txt;
  document.getElementById('cfAction').value    = action;
  document.getElementById('cfPropId').value    = pid;
  document.getElementById('cfJobId').value     = jid;
  document.getElementById('cmProv').textContent = prov;
  document.getElementById('cmBid').textContent  = bid;
  document.getElementById('cmDel').textContent  = days+' day'+(days!=1?'s':'');
  const btn = document.getElementById('cmConfirmBtn');
  btn.textContent = c.btn; btn.className = 'btn '+c.cls;
  openModal('confirmModal');
}
document.getElementById('confirmForm')?.addEventListener('submit',function(){
  const btn = document.getElementById('cmConfirmBtn');
  btn.disabled=true;
  btn.innerHTML='<span style="display:inline-block;width:12px;height:12px;border:2px solid rgba(0,0,0,.2);border-top-color:currentColor;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle;margin-right:5px;"></span>Processing…';
});

/* ── Full detail modal ── */
function openDetail(pid){
  const p = PROPOSALS_DATA.find(x=>x.id===pid);
  if(!p){ showToast('Error','Data not found.','err'); return; }
  const init = (p.fname[0]||'?').toUpperCase()+(p.lname[0]||'').toUpperCase();
  const stars = s => {let o='';for(let i=1;i<=5;i++) o+=s>=i?'<span style="color:var(--amber)">★</span>':'<span style="color:var(--tx-3)">★</span>';return o;}
  let revHtml = '';
  if(p.reviews.length){
    revHtml = `<div class="dm-sec-ttl">Reviews (${p.reviews.length})</div>`;
    p.reviews.forEach(r=>{
      revHtml+=`<div class="rv-item"><div class="rv-top"><div class="rv-name">${esc(r.fname+' '+r.lname)}</div><div class="rv-stars">${stars(r.rating)} ${r.rating.toFixed(1)}</div></div><div class="rv-comment">${esc(r.comment||'No comment.')}</div></div>`;
    });
  }
  let portHtml = '';
  if(p.portfolio_urls){
    const urls = p.portfolio_urls.split(',').map(s=>s.trim()).filter(Boolean);
    if(urls.length) portHtml = `<div class="dm-sec-ttl">Portfolio Links</div><div style="display:flex;gap:7px;flex-wrap:wrap;">${urls.map(u=>`<a href="${esc(u)}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">↗ View Work</a>`).join('')}</div>`;
  }
  let trust = '';
  if(p.ghana_card_verified) trust+='<span class="tb tb-ghana">🇬🇭 Ghana Card ✓</span>';
  if(p.phone_verified) trust+='<span class="tb tb-phone">📱 Phone ✓</span>';
  if(p.email_verified) trust+='<span class="tb tb-email">✉️ Email ✓</span>';
  let skillsHtml = p.skills.map(s=>`<span class="sk-pill">${esc(s)}</span>`).join('');

  document.getElementById('detailTitle').textContent = p.fname+' '+p.lname+' — Proposal';
  document.getElementById('detailBody').innerHTML = `
    <div class="dm-header">
      <div class="dm-av">${p.avatar?`<img src="${esc(p.avatar)}" alt="" loading="lazy">`:`<span>${init}</span>`}</div>
      <div>
        <div class="dm-name">${esc(p.fname+' '+p.lname)}</div>
        <div class="dm-tag">${esc(p.tagline||ucFirst(p.experience_level)+' Freelancer')}</div>
        <div style="display:flex;gap:7px;align-items:center;margin-top:5px;flex-wrap:wrap;">
          ${stars(p.rating_avg)} <span style="font-size:12px;color:var(--tx-3);">${p.rating_avg.toFixed(1)} (${p.rating_count} reviews)</span>
          ${p.is_verified?'<span class="verify-badge">✓ Verified</span>':''}
        </div>
        ${trust?`<div class="trust-row" style="margin-top:6px;">${trust}</div>`:''}
      </div>
    </div>
    <div class="dm-grid">
      <div class="dm-stat"><div class="dm-sv" style="color:var(--green);">₵${p.bid_amount.toLocaleString()}</div><div class="dm-sl">Bid Amount</div></div>
      <div class="dm-stat"><div class="dm-sv" style="color:var(--amber);">${p.delivery_days}d</div><div class="dm-sl">Delivery</div></div>
      <div class="dm-stat"><div class="dm-sv" style="color:var(--violet);">${p.completed_jobs}</div><div class="dm-sl">Jobs Done</div></div>
    </div>
    ${skillsHtml?`<div class="dm-sec-ttl">Skills</div><div class="skills-row">${skillsHtml}</div>`:''}
    <div class="dm-sec-ttl">Cover Letter</div>
    <div class="dm-cl">${nl2br(esc(p.cover_letter))}</div>
    ${revHtml}
    ${portHtml}
    ${p.languages?`<p style="font-size:11.5px;color:var(--tx-3);margin-top:10px;">🌍 Languages: ${esc(p.languages)}</p>`:''}
    ${p.response_time?`<p style="font-size:11.5px;color:var(--tx-3);margin-top:3px;">⏱ Response time: ${esc(p.response_time)}</p>`:''}
  `;
  const cv = p.conv_id;
  document.getElementById('detailActs').innerHTML = `
    <a href="${APP_URL}/profile.php?id=${p.uid}" class="btn btn-ghost btn-sm" target="_blank">👤 Full Profile</a>
    ${cv?`<a href="${APP_URL}/client/messages.php?conv=${cv}" class="btn btn-violet btn-sm">💬 Chat</a>`
       :`<a href="${APP_URL}/client/messages.php?start=${p.uid}" class="btn btn-violet btn-sm">💬 Chat</a>`}
    ${p.status==='pending'||p.status==='shortlisted'
      ?`<button class="btn btn-amber btn-sm" onclick="closeModal('detailModal');openConfirm('shortlist',${p.id},${p.job_id},'${esc(p.fname+' '+p.lname)}','₵${p.bid_amount}','${p.delivery_days}')">📌 Shortlist</button>
        <button class="btn btn-green btn-sm" onclick="closeModal('detailModal');openConfirm('accept',${p.id},${p.job_id},'${esc(p.fname+' '+p.lname)}','₵${p.bid_amount}','${p.delivery_days}')">✅ Hire</button>
        <button class="btn btn-red-soft btn-sm" onclick="closeModal('detailModal');openConfirm('reject',${p.id},${p.job_id},'${esc(p.fname+' '+p.lname)}','₵${p.bid_amount}','${p.delivery_days}')">✕ Decline</button>`
      :''}
    <button class="btn btn-ghost btn-sm" onclick="closeModal('detailModal')">Close</button>
  `;
  openModal('detailModal');
}

/* ── Comparison tool ── */
let compareMap = {};
function toggleCompare(cb){
  const id = parseInt(cb.value);
  if(cb.checked){
    if(Object.keys(compareMap).length>=3){ cb.checked=false; showToast('Limit','Max 3 for comparison.','info'); return; }
    compareMap[id]=cb;
    document.getElementById('prow'+id)?.classList.add('pr-selected');
  } else {
    delete compareMap[id];
    document.getElementById('prow'+id)?.classList.remove('pr-selected');
  }
  updateCompareBar();
}
function updateCompareBar(){
  const cnt = Object.keys(compareMap).length;
  const bar = document.getElementById('compareBar');
  document.getElementById('compareCnt').textContent = cnt+' selected';
  cnt>0 ? bar.classList.add('show') : bar.classList.remove('show');
}
function clearCompare(){
  Object.keys(compareMap).forEach(id=>{
    compareMap[id].checked=false;
    document.getElementById('prow'+id)?.classList.remove('pr-selected');
  });
  compareMap={};
  updateCompareBar();
}
function openCompare(){
  const ids = Object.keys(compareMap).map(Number);
  if(ids.length<2){ showToast('Select 2+','Please select at least 2 freelancers.','info'); return; }
  const data = ids.map(id=>{
    const cb = compareMap[id];
    return { id:parseInt(cb.dataset.pid), jid:parseInt(cb.dataset.jid),
      name:cb.dataset.name, bid:parseFloat(cb.dataset.bid),
      del:parseInt(cb.dataset.del), rat:parseFloat(cb.dataset.rat), jobs:parseInt(cb.dataset.jobs) };
  });
  const bestBid=Math.min(...data.map(d=>d.bid));
  const bestDel=Math.min(...data.map(d=>d.del));
  const bestRat=Math.max(...data.map(d=>d.rat));
  const bestJob=Math.max(...data.map(d=>d.jobs));
  document.getElementById('compareThead').innerHTML =
    '<th>Metric</th>'+data.map(d=>`<th><div class="ct-prov-name">${esc(d.name.split(' ')[0])}</div></th>`).join('');
  const rows = [
    {k:'Bid Amount',  vs:data.map(d=>`<span class="${d.bid===bestBid?'ct-best':''}">₵${d.bid.toLocaleString()}</span>`)},
    {k:'Delivery',    vs:data.map(d=>`<span class="${d.del===bestDel?'ct-best':''}">${d.del}d</span>`)},
    {k:'Rating',      vs:data.map(d=>`<span class="${d.rat===bestRat?'ct-best':''}">${d.rat.toFixed(1)} ★</span>`)},
    {k:'Jobs Done',   vs:data.map(d=>`<span class="${d.jobs===bestJob?'ct-best':''}">${d.jobs}</span>`)},
  ];
  document.getElementById('compareTbody').innerHTML =
    rows.map(r=>`<tr><td>${r.k}</td>${r.vs.map(v=>`<td>${v}</td>`).join('')}</tr>`).join('');
  document.getElementById('compareHire').innerHTML =
    data.map(d=>`<button class="btn btn-green btn-sm" onclick="closeModal('compareModal');openConfirm('accept',${d.id},${d.jid},'${esc(d.name)}','₵${d.bid}','${d.del}')">✅ Hire ${esc(d.name.split(' ')[0])}</button>`).join('');
  openModal('compareModal');
}

/* ── Share link ── */
function copyShare(){
  const inp = document.getElementById('shareUrl');
  if(inp) navigator.clipboard.writeText(inp.value).then(()=>showToast('Copied!','Job link copied.','info'));
}

/* ── Helpers ── */
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function nl2br(s){ return s.replace(/\n/g,'<br>'); }
function ucFirst(s){ return s?s[0].toUpperCase()+s.slice(1):''; }

/* ── Toast (identical to dashboard) ── */
const TI = {ok:'✅',err:'❌',info:'ℹ️',warning:'⚠️'};
function showToast(title,msg,type='info',d=4500){
  const c = document.getElementById('toast-c');
  const t = document.createElement('div'); t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${TI[type]||'ℹ️'}</div><div class="t-body"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(50px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),340);},d);
}

/* URL param toasts */
<?php if(isset($_GET['success'])): ?>showToast('Success','<?= addslashes(sanitize($_GET['success'])) ?>','ok');<?php endif; ?>
<?php if(isset($_GET['error'])):   ?>showToast('Error',  '<?= addslashes(sanitize($_GET['error'])) ?>','err');<?php endif; ?>

/* Flash auto-hide */
setTimeout(()=>{const a=document.getElementById('flashAlert');if(a){a.style.opacity='0';a.style.transition='opacity .5s';setTimeout(()=>a.remove(),500);}},4500);
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
</body>
</html>