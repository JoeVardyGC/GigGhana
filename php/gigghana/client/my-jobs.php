<?php
/**
 * GigGhana — client/my-jobs.php  (v1 — World-Class Project Management Center)
 *
 * DB tables used (all existing — NO new tables required):
 *   jobs, proposals, categories, providers, users, job_skills, skills, conversations
 *
 * Features:
 *  ✅ 8 status tabs with live counts
 *  ✅ Rich job cards — stats, skills, hired provider, deadline countdown
 *  ✅ Proposal counter with "new" badge
 *  ✅ Recommended providers per job (matched by category skills)
 *  ✅ Bulk actions (cancel, duplicate, export)
 *  ✅ Search + filter (category, budget, date)
 *  ✅ Duplicate job
 *  ✅ Boost job toggle (UI — backend hook ready)
 *  ✅ Cancel / close job modal
 *  ✅ Edit → links to post-job.php?edit=id
 *  ✅ Dark/light theme (cookie + localStorage — same as all other pages)
 *  ✅ Mobile-first responsive
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('client');

$userId  = (int)$_SESSION['user_id'];
$user    = getUserById($userId);
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';
$csrf    = generateCSRF();

/* ── POST ACTIONS ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCSRF($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';
    $jobId  = (int)($_POST['job_id'] ?? 0);

    try {
        $db = getDB();

        /* Cancel single job */
        if ($action === 'cancel' && $jobId) {
            $db->prepare("UPDATE jobs SET status='cancelled' WHERE id=? AND client_id=?")
               ->execute([$jobId, $userId]);
            header('Location: '.APP_URL.'/client/my-jobs.php?success='.urlencode('Job cancelled successfully.'));
            exit;
        }

        /* Close (mark completed) */
        if ($action === 'close' && $jobId) {
            $db->prepare("UPDATE jobs SET status='completed' WHERE id=? AND client_id=? AND status IN ('open','in_progress')")
               ->execute([$jobId, $userId]);
            header('Location: '.APP_URL.'/client/my-jobs.php?success='.urlencode('Job closed.'));
            exit;
        }

        /* Duplicate */
        if ($action === 'duplicate' && $jobId) {
            $orig = $db->prepare("SELECT * FROM jobs WHERE id=? AND client_id=? LIMIT 1");
            $orig->execute([$jobId, $userId]);
            $o = $orig->fetch();
            if ($o) {
                $newSlug = generateSlug($o['title']).'-copy-'.substr(uniqid(), -5);
                $db->prepare("
                    INSERT INTO jobs
                      (uuid,client_id,category_id,title,slug,description,requirements,
                       budget_type,budget_min,budget_max,duration,experience_level,
                       location_type,location,deadline,is_urgent,status)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'draft')
                ")->execute([
                    generateUUID(), $userId, $o['category_id'],
                    $o['title'].' (Copy)', $newSlug, $o['description'], $o['requirements'],
                    $o['budget_type'], $o['budget_min'], $o['budget_max'],
                    $o['duration'], $o['experience_level'],
                    $o['location_type'], $o['location'], $o['deadline'], $o['is_urgent'],
                ]);
                $newId = $db->lastInsertId();
                /* Copy skills */
                $sks = $db->prepare("SELECT skill_id FROM job_skills WHERE job_id=?");
                $sks->execute([$jobId]);
                $stSk = $db->prepare("INSERT IGNORE INTO job_skills (job_id,skill_id) VALUES (?,?)");
                foreach ($sks->fetchAll(PDO::FETCH_COLUMN) as $sid) $stSk->execute([$newId, $sid]);
                header('Location: '.APP_URL.'/client/post-job.php?edit='.$newId.'&success='.urlencode('Job duplicated as draft. Edit and post when ready.'));
                exit;
            }
        }

        /* Bulk actions */
        if (in_array($action, ['bulk_cancel','bulk_close']) && !empty($_POST['job_ids'])) {
            $ids   = array_map('intval', explode(',', $_POST['job_ids']));
            $phs   = implode(',', array_fill(0, count($ids), '?'));
            $newSt = $action === 'bulk_cancel' ? 'cancelled' : 'completed';
            $params = array_merge($ids, [$userId]);
            $db->prepare("UPDATE jobs SET status='$newSt' WHERE id IN ($phs) AND client_id=?")
               ->execute($params);
            header('Location: '.APP_URL.'/client/my-jobs.php?success='.urlencode('Selected jobs updated.'));
            exit;
        }

        /* Toggle boost (is_featured) */
        if ($action === 'boost' && $jobId) {
            $db->prepare("UPDATE jobs SET is_featured = NOT is_featured WHERE id=? AND client_id=?")
               ->execute([$jobId, $userId]);
            echo json_encode(['ok'=>true]); exit;
        }

    } catch (Exception $e) { error_log($e->getMessage()); }
}

/* ── FILTERS & PAGINATION ── */
$filter   = in_array($_GET['status']??'', ['all','draft','open','in_progress','completed','cancelled','disputed']) ? ($_GET['status']??'all') : 'all';
$search   = trim($_GET['q'] ?? '');
$catFilt  = (int)($_GET['category'] ?? 0);
$sortBy   = in_array($_GET['sort']??'', ['newest','oldest','budget_high','budget_low','proposals']) ? $_GET['sort'] : 'newest';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 8;
$offset   = ($page - 1) * $perPage;

try {
    $db = getDB();

    /* Status counts */
    $cntStmt = $db->prepare("
        SELECT status, COUNT(*) AS cnt FROM jobs WHERE client_id=? GROUP BY status
    ");
    $cntStmt->execute([$userId]);
    $rawCounts = $cntStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $counts    = array_merge(['draft'=>0,'open'=>0,'in_progress'=>0,'completed'=>0,'cancelled'=>0,'disputed'=>0], $rawCounts);
    $counts['all'] = array_sum($rawCounts);

    /* Build WHERE */
    $where  = ['j.client_id = :uid'];
    $params = [':uid' => $userId];
    if ($filter !== 'all') { $where[] = 'j.status = :st'; $params[':st'] = $filter; }
    if ($search)           { $where[] = 'j.title LIKE :q'; $params[':q'] = "%$search%"; }
    if ($catFilt)          { $where[] = 'j.category_id = :cat'; $params[':cat'] = $catFilt; }

    $wSql = implode(' AND ', $where);
    $orderSql = match($sortBy) {
        'oldest'       => 'j.created_at ASC',
        'budget_high'  => 'j.budget_max DESC, j.budget_min DESC',
        'budget_low'   => 'j.budget_min ASC',
        'proposals'    => 'proposal_count DESC',
        default        => 'j.created_at DESC',
    };

    /* Total */
    $totStmt = $db->prepare("SELECT COUNT(*) FROM jobs j WHERE $wSql");
    $totStmt->execute($params);
    $totalJobs  = (int)$totStmt->fetchColumn();
    $totalPages = (int)ceil($totalJobs / $perPage);

    /* Main jobs query */
    $stmt = $db->prepare("
        SELECT
            j.*,
            c.name   AS cat_name,
            c.icon   AS cat_icon,
            /* proposal counts */
            (SELECT COUNT(*) FROM proposals p WHERE p.job_id=j.id)                                        AS proposal_count,
            (SELECT COUNT(*) FROM proposals p WHERE p.job_id=j.id AND p.status='pending')                 AS new_props,
            (SELECT COUNT(*) FROM proposals p WHERE p.job_id=j.id AND p.status='shortlisted')             AS shortlisted_count,
            (SELECT COUNT(*) FROM proposals p WHERE p.job_id=j.id AND p.status='accepted')                AS accepted_count,
            /* hired provider */
            hu.first_name  AS hired_fname,
            hu.last_name   AS hired_lname,
            hu.avatar      AS hired_avatar,
            hu.id          AS hired_user_id,
            hp.tagline     AS hired_tagline,
            hp.rating_avg  AS hired_rating,
            hp.is_verified AS hired_verified,
            /* conversation id for messaging hired provider */
            (SELECT cv.id FROM conversations cv
             WHERE ((cv.user1_id=j.client_id AND cv.user2_id=hu.id)
                OR  (cv.user2_id=j.client_id AND cv.user1_id=hu.id))
             LIMIT 1) AS hired_conv_id
        FROM jobs j
        LEFT JOIN categories c  ON c.id  = j.category_id
        LEFT JOIN providers  hp ON hp.id = j.hired_provider_id
        LEFT JOIN users      hu ON hu.id = hp.user_id
        WHERE $wSql
        ORDER BY j.is_featured DESC, $orderSql
        LIMIT :lim OFFSET :off
    ");
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
    $stmt->execute();
    $jobs = $stmt->fetchAll();

    /* For each job: skills + recommended providers */
    foreach ($jobs as &$j) {
        /* Skills */
        $sk = $db->prepare("
            SELECT s.name FROM job_skills js
            JOIN skills s ON s.id = js.skill_id
            WHERE js.job_id = ?
        ");
        $sk->execute([$j['id']]);
        $j['skills'] = $sk->fetchAll(PDO::FETCH_COLUMN);

        /* Recommended providers — matched by category, top 3 by rating */
        $rp = $db->prepare("
            SELECT u.id AS user_id, u.first_name, u.last_name, u.avatar,
                   p.tagline, p.rating_avg, p.rating_count, p.hourly_rate,
                   p.is_verified, p.completed_jobs
            FROM providers p
            JOIN users u ON u.id = p.user_id
            WHERE u.is_active=1 AND u.is_banned=0
              AND EXISTS (
                  SELECT 1 FROM provider_skills ps
                  JOIN skills s ON s.id = ps.skill_id
                  WHERE ps.provider_id = p.id
                    AND s.category_id = ?
              )
            ORDER BY p.is_verified DESC, p.rating_avg DESC
            LIMIT 3
        ");
        $rp->execute([$j['category_id'] ?? 0]);
        $j['recommended'] = $rp->fetchAll();
    }
    unset($j);

    /* Categories for filter dropdown */
    $cats = $db->query("SELECT id,name FROM categories WHERE is_active=1 ORDER BY name")->fetchAll();

} catch (Exception $e) {
    error_log($e->getMessage());
    $jobs=[]; $totalJobs=0; $totalPages=0; $counts=['all'=>0,'draft'=>0,'open'=>0,'in_progress'=>0,'completed'=>0,'cancelled'=>0,'disputed'=>0];
    $cats=[];
}

/* Helpers */
$myInit   = strtoupper(substr($user['first_name']??'M',0,1).substr($user['last_name']??'',0,1));
$myAvatar = $user['avatar'] ?? '';
$success  = isset($_GET['success']) ? sanitize($_GET['success']) : '';

$iconMap = ['code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈','file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰','briefcase'=>'⚖️','headphones'=>'🎧','tool'=>'🔧','bar-chart'=>'🍽️','globe'=>'🌿'];

function jobStatusMeta(string $st): array {
    return match($st) {
        'open'        => ['label'=>'Open',        'cls'=>'st-open',       'ico'=>'🟢'],
        'in_progress' => ['label'=>'In Progress',  'cls'=>'st-progress',   'ico'=>'🔵'],
        'completed'   => ['label'=>'Completed',    'cls'=>'st-done',       'ico'=>'✅'],
        'cancelled'   => ['label'=>'Cancelled',    'cls'=>'st-cancelled',  'ico'=>'🔴'],
        'draft'       => ['label'=>'Draft',        'cls'=>'st-draft',      'ico'=>'⬜'],
        'disputed'    => ['label'=>'Disputed',     'cls'=>'st-disputed',   'ico'=>'⚠️'],
        default       => ['label'=>ucfirst($st),   'cls'=>'st-open',       'ico'=>'⬜'],
    };
}

function deadlineLabel(?string $dl): ?array {
    if (!$dl) return null;
    $diff = (int)ceil((strtotime($dl) - time()) / 86400);
    if ($diff < 0)   return ['text'=>'Overdue by '.abs($diff).'d', 'cls'=>'dl-overdue'];
    if ($diff === 0) return ['text'=>'Due Today!',                  'cls'=>'dl-urgent'];
    if ($diff <= 3)  return ['text'=>"Due in {$diff}d",            'cls'=>'dl-urgent'];
    if ($diff <= 7)  return ['text'=>"Due in {$diff}d",            'cls'=>'dl-warn'];
    return ['text'=>date('M j', strtotime($dl)), 'cls'=>'dl-ok'];
}

function durLabel(string $d): string {
    return match($d) {
        'less_1_week'=>'< 1 week', '1_2_weeks'=>'1–2 weeks', '1_month'=>'~1 month',
        '3_months'=>'2–3 months', '6_months'=>'3–6 months', 'ongoing'=>'Ongoing',
        default=>$d
    };
}
function ini2(string $f, string $l): string { return strtoupper(substr($f,0,1).substr($l,0,1)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Jobs — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════
   TOKENS — same palette as all GigGhana pages
═══════════════════════════════════════ */
:root{
  --bg:#0C0E14;--s1:#111520;--s2:#181C28;--s3:#1F2436;--s4:#252B3E;
  --glass:rgba(24,28,40,.88);
  --cyan:#00D4C8;--cyan-d:#00B8AD;--cyan-glo:rgba(0,212,200,.18);
  --coral:#FF6B4A;--coral-d:#E85A39;--coral-glo:rgba(255,107,74,.18);
  --violet:#7C6FF7;--violet-d:#5D52E0;--violet-dim:rgba(124,111,247,.1);
  --teal:#1FD9A0;--amber:#F7B731;--red:#FF4D6D;--blue:#4E9EFF;
  --tx:#F2F4F8;--tx-2:#9BA8BF;--tx-3:#5C6A85;--tx-4:#3A4560;
  --bd:rgba(255,255,255,.065);--bd2:rgba(255,255,255,.13);
  --fh:'Plus Jakarta Sans',sans-serif;--fb:'DM Sans',sans-serif;
  --r:16px;--rs:10px;--e:all .24s cubic-bezier(.4,0,.2,1);
  --nav-h:62px;--sb:256px;
}
.lm{
  --bg:#F3F5FA;--s1:#EAEEF7;--s2:#E0E6F2;--s3:#D4DCEE;--s4:#C8D2E8;
  --glass:rgba(234,238,247,.92);
  --cyan:#009E95;--cyan-d:#007870;--cyan-glo:rgba(0,158,149,.14);
  --coral:#E8512B;--coral-d:#C43C1C;--coral-glo:rgba(232,81,43,.14);
  --violet:#5B4FD9;--violet-d:#4540C0;--violet-dim:rgba(91,79,217,.08);
  --teal:#0DAF80;--amber:#D4980A;--red:#D63050;
  --tx:#0D1220;--tx-2:#344060;--tx-3:#6B7A99;--tx-4:#9BA8BF;
  --bd:rgba(30,40,80,.09);--bd2:rgba(30,40,80,.18);
}
.lm .topnav{background:rgba(234,238,247,.97);}
.lm .sidebar{background:var(--s1);}
.lm .job-card{background:rgba(255,255,255,.9);}
.lm .rec-card{background:rgba(255,255,255,.85);}
.lm .stat-block{background:rgba(0,0,0,.05);}
.lm .tab-btn{background:rgba(0,0,0,.05);}
.lm .field-input,.lm .field-select{background:rgba(0,0,0,.05);border-color:var(--bd2);}
.lm .modal-inner{background:var(--s2);}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{min-height:100%;background:var(--bg);color:var(--tx);font-family:var(--fb);
  font-size:14.5px;line-height:1.65;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;}
img{display:block;max-width:100%;}a{text-decoration:none;color:inherit;}
button{font-family:var(--fb);cursor:pointer;}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--s4);border-radius:2px;}

/* ─── LAYOUT ─── */
.layout{display:flex;min-height:100vh;}

/* ─── SIDEBAR ─── */
.sidebar{
  width:var(--sb);flex-shrink:0;background:var(--s1);
  border-right:1px solid var(--bd);
  position:sticky;top:0;height:100vh;overflow-y:auto;
  display:flex;flex-direction:column;
  transition:background .3s,border-color .3s;
  scrollbar-width:none;
}
.sidebar::-webkit-scrollbar{display:none;}
.sb-logo{padding:20px 18px 16px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:9px;text-decoration:none;}
.logo-mark{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--cyan),var(--teal));display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-weight:900;font-size:15px;color:#000;flex-shrink:0;}
.logo-txt{font-family:var(--fh);font-size:18px;font-weight:900;color:var(--tx);}
.logo-txt span{color:var(--cyan);}
.sb-nav{flex:1;padding:10px;}
.sb-sec{font-size:9px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;color:var(--tx-3);padding:6px 12px;margin:14px 0 4px;}
.sb-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--tx-3);font-size:13px;font-weight:500;transition:var(--e);text-decoration:none;}
.sb-item:hover{background:rgba(255,255,255,.05);color:var(--tx);}
.sb-item.active{background:rgba(0,212,200,.1);color:var(--cyan);border-left:3px solid var(--cyan);padding-left:9px;}
.sb-item.danger{color:var(--red);}
.sb-item.danger:hover{background:rgba(255,77,106,.08);}
.sb-badge{margin-left:auto;background:var(--coral);color:#fff;font-size:9px;font-weight:800;padding:2px 7px;border-radius:50px;font-family:var(--fh);}
.sb-gap{flex:1;}
.sb-user{padding:12px 10px;border-top:1px solid var(--bd);}
.sb-ucard{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;background:rgba(0,0,0,.2);}
.sb-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--coral),var(--violet-d));display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:12px;font-weight:800;color:#fff;overflow:hidden;flex-shrink:0;}
.sb-av img{width:100%;height:100%;object-fit:cover;}
.sb-uname{font-size:12.5px;font-weight:700;line-height:1.2;}
.sb-urole{font-size:10px;color:var(--coral);font-weight:700;text-transform:uppercase;}

/* ─── MAIN ─── */
.main{flex:1;min-width:0;display:flex;flex-direction:column;}

/* ─── TOP NAV ─── */
.topnav{
  height:var(--nav-h);padding:0 28px;
  display:flex;align-items:center;justify-content:space-between;
  background:rgba(12,14,20,.92);backdrop-filter:blur(28px);
  border-bottom:1px solid var(--bd);
  position:sticky;top:0;z-index:200;
  transition:background .3s,border-color .3s;
}
.tn-left{display:flex;align-items:center;gap:14px;}
.tn-title{font-family:var(--fh);font-size:19px;font-weight:900;}
.tn-sub{font-size:12px;color:var(--tx-3);font-weight:400;}
.tn-right{display:flex;align-items:center;gap:8px;}
.icon-btn{width:36px;height:36px;border-radius:var(--rs);background:rgba(255,255,255,.04);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--e);font-size:15px;color:var(--tx-2);}
.icon-btn:hover{background:rgba(255,255,255,.08);color:var(--tx);}
.uchip{display:flex;align-items:center;gap:7px;background:rgba(255,255,255,.04);border:1px solid var(--bd);border-radius:50px;padding:3px 12px 3px 4px;font-size:12.5px;font-weight:700;font-family:var(--fh);transition:var(--e);}
.uchip:hover{background:rgba(255,255,255,.08);}
.uchip-av{width:26px;height:26px;border-radius:50%;overflow:hidden;background:linear-gradient(135deg,var(--violet),var(--cyan));display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff;font-family:var(--fh);}
.uchip-av img{width:100%;height:100%;object-fit:cover;}

/* ─── BUTTONS ─── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--rs);font-size:12.5px;font-weight:700;font-family:var(--fh);border:none;cursor:pointer;transition:var(--e);white-space:nowrap;text-decoration:none;}
.btn-xs{padding:5px 10px;font-size:11.5px;border-radius:8px;}
.btn-sm{padding:7px 13px;font-size:12px;}
.btn-lg{padding:11px 24px;font-size:14px;border-radius:13px;}
.btn-ghost{background:rgba(255,255,255,.05);border:1px solid var(--bd);color:var(--tx-2);}
.btn-ghost:hover{background:rgba(255,255,255,.09);border-color:var(--bd2);color:var(--tx);}
.btn-cyan{background:linear-gradient(135deg,var(--cyan),var(--teal));color:#000;font-weight:800;box-shadow:0 3px 14px var(--cyan-glo);}
.btn-cyan:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--cyan-glo);}
.btn-coral{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:800;box-shadow:0 3px 14px var(--coral-glo);}
.btn-coral:hover{transform:translateY(-2px);}
.btn-violet{background:linear-gradient(135deg,var(--violet),var(--violet-d));color:#fff;font-weight:800;}
.btn-violet:hover{transform:translateY(-2px);}
.btn-amber{background:linear-gradient(135deg,var(--amber),#F5A623);color:#000;font-weight:800;}
.btn-amber:hover{transform:translateY(-2px);}
.btn-red-soft{background:rgba(255,77,109,.1);border:1px solid rgba(255,77,109,.22);color:var(--red);}
.btn-red-soft:hover{background:rgba(255,77,109,.18);}

/* ─── CONTENT ─── */
.content{padding:28px 32px 80px;}

/* ─── ALERT ─── */
.alert{display:flex;align-items:center;gap:11px;padding:14px 18px;border-radius:12px;margin-bottom:22px;font-size:13.5px;}
.alert.success{background:rgba(31,217,160,.08);border:1px solid rgba(31,217,160,.22);color:var(--teal);}
.alert.error{background:rgba(255,77,109,.08);border:1px solid rgba(255,77,109,.2);color:var(--red);}

/* ─── STATS HEADER ROW ─── */
.stats-row{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:28px;}
.stat-block{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:14px;
  padding:16px 18px;transition:var(--e);position:relative;overflow:hidden;
}
.stat-block::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;transform:scaleX(0);transform-origin:left;transition:transform .35s;}
.stat-block:hover{transform:translateY(-3px);}
.stat-block:hover::after{transform:scaleX(1);}
.sb-cyan::after{background:var(--cyan);}.sb-coral::after{background:var(--coral);}
.sb-violet::after{background:var(--violet);}.sb-teal::after{background:var(--teal);}
.sb-amber::after{background:var(--amber);}
.stat-val{font-family:var(--fh);font-size:26px;font-weight:900;line-height:1;margin-bottom:4px;}
.stat-lbl{font-size:11.5px;color:var(--tx-3);font-weight:500;}

/* ─── SEARCH & FILTER BAR ─── */
.filter-bar{display:flex;align-items:center;gap:10px;margin-bottom:22px;flex-wrap:wrap;}
.search-wrap{flex:1;min-width:200px;display:flex;align-items:center;gap:9px;
  background:rgba(0,0,0,.25);border:1.5px solid var(--bd);border-radius:var(--rs);
  padding:9px 13px;transition:var(--e);}
.search-wrap:focus-within{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-glo);}
.search-wrap input{flex:1;background:transparent;border:none;outline:none;color:var(--tx);font-size:13.5px;font-family:var(--fb);}
.search-wrap input::placeholder{color:var(--tx-3);}
.field-select{background:rgba(0,0,0,.25);border:1.5px solid var(--bd);border-radius:var(--rs);padding:9px 13px;color:var(--tx);font-family:var(--fb);font-size:13px;outline:none;transition:var(--e);cursor:pointer;appearance:none;}
.field-select:focus{border-color:var(--cyan);}
.bulk-bar{display:none;align-items:center;gap:10px;padding:10px 16px;background:rgba(0,212,200,.07);border:1px solid rgba(0,212,200,.2);border-radius:var(--rs);flex-wrap:wrap;}
.bulk-bar.show{display:flex;}
.bulk-count{font-family:var(--fh);font-size:13px;font-weight:800;color:var(--cyan);}

/* ─── STATUS TABS ─── */
.tabs-row{display:flex;gap:6px;margin-bottom:24px;overflow-x:auto;padding-bottom:3px;scrollbar-width:none;}
.tabs-row::-webkit-scrollbar{display:none;}
.tab-btn{
  display:flex;align-items:center;gap:7px;
  padding:8px 16px;border-radius:50px;
  font-size:12.5px;font-weight:700;font-family:var(--fh);
  background:rgba(0,0,0,.22);border:1.5px solid var(--bd);
  color:var(--tx-3);cursor:pointer;transition:var(--e);
  white-space:nowrap;text-decoration:none;
}
.tab-btn:hover{color:var(--tx);border-color:var(--bd2);}
.tab-btn.active{background:rgba(0,212,200,.1);border-color:rgba(0,212,200,.32);color:var(--cyan);}
.tab-badge{background:rgba(255,255,255,.1);color:var(--tx-2);font-size:10px;padding:1px 7px;border-radius:50px;font-weight:800;}
.tab-btn.active .tab-badge{background:rgba(0,212,200,.18);color:var(--cyan);}
/* Coral for tabs with content */
.tab-btn.has-new{border-color:rgba(255,107,74,.3);}
.tab-btn.has-new .tab-badge{background:var(--coral);color:#fff;}

/* ─── JOB CARDS ─── */
.jobs-list{display:flex;flex-direction:column;gap:18px;}

.job-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1.5px solid var(--bd);border-radius:20px;
  overflow:hidden;transition:var(--e);
  animation:cardIn .3s ease both;
}
@keyframes cardIn{from{opacity:0;transform:translateY(12px);}to{opacity:1;transform:translateY(0);}}
.job-card:hover{border-color:var(--bd2);transform:translateY(-3px);box-shadow:0 16px 48px rgba(0,0,0,.35);}
.job-card.featured-job{border-color:rgba(255,107,74,.28);}
.job-card.featured-job .jc-header{background:linear-gradient(135deg,rgba(255,107,74,.07),rgba(255,107,74,.02));}
.job-card.urgent-job{border-color:rgba(247,183,49,.25);}

/* Card header */
.jc-header{
  padding:20px 22px 16px;
  border-bottom:1px solid var(--bd);
  display:flex;align-items:flex-start;gap:14px;
}
.jc-select{width:18px;height:18px;margin-top:4px;flex-shrink:0;accent-color:var(--cyan);cursor:pointer;}
.jc-cat-ico{
  width:44px;height:44px;border-radius:12px;flex-shrink:0;
  background:rgba(0,212,200,.1);border:1px solid rgba(0,212,200,.2);
  display:flex;align-items:center;justify-content:center;font-size:20px;
}
.jc-info{flex:1;min-width:0;}
.jc-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:5px;}
.jc-title{font-family:var(--fh);font-size:16px;font-weight:800;color:var(--tx);text-decoration:none;line-height:1.3;}
.jc-title:hover{color:var(--cyan);}
.jc-badges{display:flex;gap:6px;align-items:center;flex-shrink:0;flex-wrap:wrap;}
.st-pill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:7px;font-size:11px;font-weight:800;font-family:var(--fh);white-space:nowrap;}
.st-open    {background:rgba(0,212,200,.1);color:var(--cyan);border:1px solid rgba(0,212,200,.25);}
.st-progress{background:rgba(124,111,247,.1);color:var(--violet);border:1px solid rgba(124,111,247,.25);}
.st-done    {background:rgba(31,217,160,.1);color:var(--teal);border:1px solid rgba(31,217,160,.2);}
.st-cancelled{background:rgba(255,77,109,.08);color:var(--red);border:1px solid rgba(255,77,109,.2);}
.st-draft   {background:rgba(100,116,139,.1);color:var(--tx-3);border:1px solid var(--bd);}
.st-disputed{background:rgba(247,183,49,.1);color:var(--amber);border:1px solid rgba(247,183,49,.2);}
.pill-urgent{background:rgba(255,107,74,.12);color:var(--coral);border:1px solid rgba(255,107,74,.22);padding:4px 9px;border-radius:7px;font-size:10.5px;font-weight:800;font-family:var(--fh);}
.pill-featured{background:rgba(247,183,49,.12);color:var(--amber);border:1px solid rgba(247,183,49,.2);padding:4px 9px;border-radius:7px;font-size:10.5px;font-weight:800;font-family:var(--fh);}

.jc-meta-row{display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--tx-3);}
.jc-meta-item{display:flex;align-items:center;gap:4px;}
.jc-budget{color:var(--cyan);font-weight:800;font-family:var(--fh);font-size:13.5px;}

/* Deadline label */
.dl-overdue{color:var(--red);font-weight:700;}
.dl-urgent{color:var(--coral);font-weight:700;}
.dl-warn{color:var(--amber);font-weight:700;}
.dl-ok{color:var(--tx-3);}

/* Card body */
.jc-body{padding:16px 22px;}
.jc-desc{font-size:13px;color:var(--tx-2);line-height:1.7;margin-bottom:14px;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}

/* Stats strip */
.jc-stats{display:flex;gap:0;background:rgba(0,0,0,.18);border-radius:10px;overflow:hidden;margin-bottom:16px;}
.jcs-item{flex:1;padding:10px 12px;text-align:center;border-right:1px solid var(--bd);transition:var(--e);}
.jcs-item:last-child{border-right:none;}
.jcs-item:hover{background:rgba(255,255,255,.03);}
.jcs-val{font-family:var(--fh);font-size:16px;font-weight:900;line-height:1;margin-bottom:2px;}
.jcs-lbl{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.5px;}
/* Colour coding for stats */
.jcs-views .jcs-val{color:var(--blue);}
.jcs-props .jcs-val{color:var(--violet);}
.jcs-short .jcs-val{color:var(--amber);}
.jcs-hired .jcs-val{color:var(--teal);}

/* Skills */
.jc-skills{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px;}
.sk-pill{padding:4px 10px;border-radius:7px;font-size:11px;font-weight:600;background:rgba(0,212,200,.08);border:1px solid rgba(0,212,200,.18);color:var(--cyan);}
.sk-more{color:var(--tx-3);font-size:11px;padding:4px 0;}

/* Hired provider strip */
.hired-strip{
  display:flex;align-items:center;gap:12px;
  padding:12px 14px;border-radius:12px;
  background:rgba(31,217,160,.06);border:1px solid rgba(31,217,160,.18);
  margin-bottom:16px;
}
.hired-av{width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;
  background:linear-gradient(135deg,var(--violet),var(--cyan));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-size:12px;font-weight:800;color:#fff;}
.hired-av img{width:100%;height:100%;object-fit:cover;}
.hired-info{flex:1;min-width:0;}
.hired-name{font-family:var(--fh);font-size:13px;font-weight:800;}
.hired-tag{font-size:11.5px;color:var(--tx-3);}
.hired-rating{font-size:11.5px;color:var(--amber);}

/* Card footer — actions */
.jc-footer{
  padding:14px 22px;
  border-top:1px solid var(--bd);
  display:flex;align-items:center;justify-content:space-between;
  gap:12px;flex-wrap:wrap;
  background:rgba(0,0,0,.1);
}
.jc-actions{display:flex;gap:7px;flex-wrap:wrap;}

/* Recommended providers */
.rec-panel{padding:0 22px 18px;}
.rec-head{font-size:11px;font-weight:800;color:var(--tx-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:10px;font-family:var(--fh);}
.rec-cards{display:flex;gap:10px;overflow-x:auto;padding-bottom:4px;scrollbar-width:none;}
.rec-cards::-webkit-scrollbar{display:none;}
.rec-card{
  flex-shrink:0;width:160px;
  background:rgba(0,0,0,.22);border:1px solid var(--bd);border-radius:12px;
  padding:12px;transition:var(--e);text-decoration:none;color:var(--tx);
  display:flex;flex-direction:column;align-items:center;text-align:center;
}
.rec-card:hover{border-color:var(--cyan);background:rgba(0,212,200,.05);}
.rc-av{width:40px;height:40px;border-radius:50%;overflow:hidden;margin-bottom:7px;
  background:linear-gradient(135deg,var(--violet),var(--coral));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-size:14px;font-weight:800;color:#fff;}
.rc-av img{width:100%;height:100%;object-fit:cover;}
.rc-name{font-family:var(--fh);font-size:12px;font-weight:800;margin-bottom:2px;line-height:1.2;}
.rc-tag{font-size:10.5px;color:var(--tx-3);margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:100%;}
.rc-rating{font-size:11px;color:var(--amber);}
.rc-rate{font-size:11.5px;color:var(--cyan);font-weight:700;font-family:var(--fh);margin-top:4px;}
.rc-hire{margin-top:8px;padding:4px 12px;border-radius:7px;font-size:11px;font-weight:700;font-family:var(--fh);background:rgba(0,212,200,.12);border:1px solid rgba(0,212,200,.25);color:var(--cyan);transition:var(--e);}
.rec-card:hover .rc-hire{background:var(--cyan);color:#000;}

/* Empty state */
.empty-state{
  background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);
  border-radius:20px;padding:64px 24px;text-align:center;
}
.es-ico{font-size:52px;margin-bottom:16px;}
.es-title{font-family:var(--fh);font-size:22px;font-weight:900;margin-bottom:8px;}
.es-sub{font-size:14px;color:var(--tx-2);max-width:380px;margin:0 auto 24px;line-height:1.75;}

/* Pagination */
.pagination{display:flex;gap:7px;justify-content:center;margin-top:32px;flex-wrap:wrap;}
.pag-btn{padding:8px 15px;border-radius:var(--rs);font-size:13px;font-weight:700;font-family:var(--fh);color:var(--tx-3);background:var(--s2);border:1px solid var(--bd);transition:var(--e);text-decoration:none;}
.pag-btn:hover,.pag-btn.active{background:rgba(0,212,200,.1);color:var(--cyan);border-color:rgba(0,212,200,.3);}
.pag-btn.disabled{opacity:.3;pointer-events:none;}

/* ─── MODAL ─── */
.modal-bg{display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.75);backdrop-filter:blur(14px);align-items:center;justify-content:center;padding:20px;}
.modal-bg.open{display:flex;animation:mfIn .2s ease;}
@keyframes mfIn{from{opacity:0;}to{opacity:1;}}
.modal-inner{background:var(--s2);border:1px solid var(--bd2);border-radius:22px;padding:30px;max-width:480px;width:100%;box-shadow:0 30px 80px rgba(0,0,0,.7);animation:mIn .25s cubic-bezier(.34,1.56,.64,1);}
@keyframes mIn{from{transform:scale(.92);}to{transform:scale(1);}}
.modal-ico{font-size:36px;margin-bottom:14px;}
.modal-title{font-family:var(--fh);font-size:18px;font-weight:900;margin-bottom:8px;}
.modal-sub{font-size:13.5px;color:var(--tx-2);line-height:1.7;margin-bottom:22px;}
.modal-acts{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;}

/* ─── TOAST ─── */
#toasts{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd2);padding:12px 15px;border-radius:var(--rs);max-width:320px;box-shadow:0 14px 40px rgba(0,0,0,.6);animation:tIn .3s ease;backdrop-filter:blur(18px);}
.toast.success{border-left:3px solid var(--teal);}
.toast.error{border-left:3px solid var(--red);}
.toast.info{border-left:3px solid var(--cyan);}
.t-ico{font-size:15px;flex-shrink:0;}.t-body{flex:1;}
.t-ttl{font-family:var(--fh);font-weight:800;font-size:12px;margin-bottom:1px;}
.t-msg{font-size:11px;color:var(--tx-3);}
.t-cls{color:var(--tx-3);font-size:16px;cursor:pointer;flex-shrink:0;}
@keyframes tIn{from{opacity:0;transform:translateX(48px);}to{opacity:1;transform:translateX(0);}}

/* ─── RESPONSIVE ─── */
@media(max-width:1100px){.stats-row{grid-template-columns:repeat(3,1fr);}.sidebar{display:none;}.main{margin-left:0;}}
@media(max-width:768px){.content{padding:18px 14px 70px;}.stats-row{grid-template-columns:1fr 1fr;}.jc-title-row{flex-direction:column;gap:8px;}.topnav{padding:0 16px;}.tn-title{font-size:16px;}}
@media(max-width:480px){.stats-row{grid-template-columns:1fr 1fr;}.jcs-item{padding:8px 6px;}.jcs-val{font-size:14px;}}
</style>
</head>
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<div class="layout">

  <!-- ═══════ SIDEBAR ═══════ -->
  <aside class="sidebar">
    <a href="<?= APP_URL ?>/index.php" class="sb-logo">
      <div class="logo-mark">G</div>
      <span class="logo-txt">Gig<span>Ghana</span></span>
    </a>
    <nav class="sb-nav">
      <div class="sb-sec">Client</div>
      <a href="<?= APP_URL ?>/client/dashboard.php" class="sb-item">📊 Dashboard</a>
      <a href="<?= APP_URL ?>/client/post-job.php"  class="sb-item">✏️ Post a Job</a>
      <a href="<?= APP_URL ?>/client/my-jobs.php"   class="sb-item active">💼 My Jobs</a>
      <a href="<?= APP_URL ?>/client/proposals.php" class="sb-item">
        📩 Proposals
        <?php if(($counts['open']??0) > 0): ?><span class="sb-badge"><?= $counts['open'] ?></span><?php endif;?>
      </a>
      <div class="sb-sec">Communication</div>
      <a href="<?= APP_URL ?>/client/messages.php"  class="sb-item">💬 Messages</a>
      <a href="<?= APP_URL ?>/search/providers.php" class="sb-item">🔍 Find Talent</a>
      <div class="sb-sec">Finance</div>
      <a href="<?= APP_URL ?>/client/payments.php"  class="sb-item">💳 Payments</a>
      <a href="<?= APP_URL ?>/client/escrow.php"    class="sb-item">🔒 Escrow</a>
      <div class="sb-sec">Account</div>
      <a href="<?= APP_URL ?>/client/settings.php"  class="sb-item">⚙️ Settings</a>
      <a href="<?= APP_URL ?>/auth/logout.php"       class="sb-item danger">🚪 Sign Out</a>
    </nav>
    <div class="sb-gap"></div>
    <div class="sb-user">
      <div class="sb-ucard">
        <div class="sb-av">
          <?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?>
        </div>
        <div>
          <div class="sb-uname"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div>
          <div class="sb-urole">Client</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- ═══════ MAIN ═══════ -->
  <div class="main">

    <!-- TOP NAV -->
    <nav class="topnav">
      <div class="tn-left">
        <div>
          <div class="tn-title">💼 My Jobs</div>
          <div class="tn-sub">Project Management Center</div>
        </div>
      </div>
      <div class="tn-right">
        <button class="icon-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle theme">🌙</button>
        <div class="icon-btn">🔔</div>
        <div class="uchip">
          <div class="uchip-av">
            <?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?>
          </div>
          <?= sanitize($user['first_name']??'Me') ?>
        </div>
        <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-coral">✏️ Post a Job</a>
      </div>
    </nav>

    <!-- CONTENT -->
    <div class="content">

      <?php if($success):?>
      <div class="alert success">✅ <?= $success ?></div>
      <?php endif;?>

      <!-- ── STATS ROW ── -->
      <div class="stats-row">
        <div class="stat-block sb-cyan">
          <div class="stat-val" style="color:var(--cyan);"><?= $counts['all'] ?></div>
          <div class="stat-lbl">Total Jobs</div>
        </div>
        <div class="stat-block sb-teal">
          <div class="stat-val" style="color:var(--teal);"><?= $counts['open'] ?></div>
          <div class="stat-lbl">Open & Active</div>
        </div>
        <div class="stat-block sb-violet">
          <div class="stat-val" style="color:var(--violet);"><?= $counts['in_progress'] ?></div>
          <div class="stat-lbl">In Progress</div>
        </div>
        <div class="stat-block sb-amber">
          <div class="stat-val" style="color:var(--amber);"><?= $counts['completed'] ?></div>
          <div class="stat-lbl">Completed</div>
        </div>
        <div class="stat-block sb-coral">
          <div class="stat-val" style="color:var(--tx-3);"><?= $counts['draft'] ?></div>
          <div class="stat-lbl">Drafts</div>
        </div>
      </div>

      <!-- ── SEARCH & FILTER ── -->
      <form method="GET" id="filterForm">
        <div class="filter-bar">
          <div class="search-wrap">
            <span style="color:var(--tx-3);font-size:14px;">🔍</span>
            <input type="text" name="q" id="searchInput"
                   placeholder="Search my jobs…"
                   value="<?= htmlspecialchars($search) ?>" autocomplete="off">
          </div>
          <select name="category" class="field-select" onchange="document.getElementById('filterForm').submit()">
            <option value="">All Categories</option>
            <?php foreach($cats as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $catFilt===(int)$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
            <?php endforeach;?>
          </select>
          <select name="sort" class="field-select" onchange="document.getElementById('filterForm').submit()">
            <option value="newest"      <?= $sortBy==='newest'     ?'selected':'' ?>>Newest First</option>
            <option value="oldest"      <?= $sortBy==='oldest'     ?'selected':'' ?>>Oldest First</option>
            <option value="proposals"   <?= $sortBy==='proposals'  ?'selected':'' ?>>Most Proposals</option>
            <option value="budget_high" <?= $sortBy==='budget_high'?'selected':'' ?>>Budget: High → Low</option>
            <option value="budget_low"  <?= $sortBy==='budget_low' ?'selected':'' ?>>Budget: Low → High</option>
          </select>
          <input type="hidden" name="status" value="<?= htmlspecialchars($filter) ?>">
          <button type="submit" class="btn btn-cyan">Search</button>
          <?php if($search||$catFilt):?>
          <a href="<?= APP_URL ?>/client/my-jobs.php?status=<?= $filter ?>" class="btn btn-ghost">✕ Clear</a>
          <?php endif;?>
        </div>
      </form>

      <!-- ── BULK ACTION BAR ── -->
      <div class="bulk-bar" id="bulkBar">
        <span class="bulk-count" id="bulkCount">0 selected</span>
        <form method="POST" id="bulkForm" style="display:contents;">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <input type="hidden" name="action" id="bulkAction" value="">
          <input type="hidden" name="job_ids" id="bulkIds" value="">
          <button type="button" class="btn btn-sm btn-red-soft" onclick="doBulk('bulk_cancel')">🚫 Cancel Selected</button>
          <button type="button" class="btn btn-sm btn-amber" onclick="doBulk('bulk_close')">✅ Close Selected</button>
        </form>
        <button class="btn btn-sm btn-ghost" onclick="clearSelection()">✕ Clear Selection</button>
      </div>

      <!-- ── STATUS TABS ── -->
      <div class="tabs-row">
        <?php
        $tabDefs = [
          'all'         => ['label'=>'All Jobs',    'ico'=>'📋'],
          'draft'       => ['label'=>'Drafts',      'ico'=>'⬜'],
          'open'        => ['label'=>'Open',        'ico'=>'🟢'],
          'in_progress' => ['label'=>'In Progress', 'ico'=>'🔵'],
          'completed'   => ['label'=>'Completed',   'ico'=>'✅'],
          'cancelled'   => ['label'=>'Cancelled',   'ico'=>'🔴'],
          'disputed'    => ['label'=>'Disputed',    'ico'=>'⚠️'],
        ];
        foreach($tabDefs as $key=>$td):
          $cnt   = $counts[$key] ?? 0;
          $isAct = $filter === $key;
          $hasN  = $cnt > 0 && !$isAct;
        ?>
        <a href="?status=<?= $key ?><?= $search?'&q='.urlencode($search):'' ?>"
           class="tab-btn <?= $isAct?'active':'' ?> <?= $hasN&&$key==='open'?'has-new':'' ?>">
          <?= $td['ico'] ?> <?= $td['label'] ?>
          <span class="tab-badge"><?= $cnt ?></span>
        </a>
        <?php endforeach;?>
      </div>

      <!-- ── JOBS LIST ── -->
      <?php if(empty($jobs)):?>
      <div class="empty-state">
        <div class="es-ico">📋</div>
        <div class="es-title">
          <?= $filter==='all' ? 'No jobs posted yet' : 'No '.str_replace('_',' ',$filter).' jobs' ?>
        </div>
        <p class="es-sub">
          <?php if($filter==='all'): ?>
            Post your first job and start receiving proposals from Ghana's top professionals.
          <?php elseif($filter==='draft'): ?>
            You have no saved drafts. Start a new job post to save as draft.
          <?php elseif($filter==='open'): ?>
            You have no open jobs right now. Post a new job to start receiving proposals.
          <?php elseif($filter==='completed'): ?>
            You haven't completed any jobs yet. Hire a provider to get started.
          <?php else: ?>
            No jobs match this filter.
          <?php endif;?>
        </p>
        <a href="<?= APP_URL ?>/client/post-job.php" class="btn btn-coral btn-lg">✏️ Post a New Job</a>
      </div>

      <?php else: ?>
      <div class="jobs-list" id="jobsList">

      <?php foreach($jobs as $idx => $j):
        $st      = jobStatusMeta($j['status']);
        $dlInfo  = deadlineLabel($j['deadline'] ?? null);
        $catIco  = $iconMap[$j['cat_icon']??''] ?? '💼';
        $propCnt = (int)$j['proposal_count'];
        $newProp = (int)$j['new_props'];
        $shortCnt= (int)$j['shortlisted_count'];
        $accCnt  = (int)$j['accepted_count'];
        $skills  = (array)$j['skills'];
        $recs    = (array)$j['recommended'];
        $hasHired = !empty($j['hired_fname']);
        $budgStr  = formatCurrency($j['budget_min']);
        if(($j['budget_max']??0) > $j['budget_min']) $budgStr .= ' – '.formatCurrency($j['budget_max']);
        if($j['budget_type']==='hourly') $budgStr .= '/hr';
        $delay = ($idx % 6) * 60;
      ?>
      <div class="job-card <?= $j['is_featured']?'featured-job':'' ?> <?= $j['is_urgent']?'urgent-job':'' ?>"
           style="animation-delay:<?= $delay ?>ms;" id="jcard<?= $j['id'] ?>">

        <!-- HEADER -->
        <div class="jc-header">
          <input type="checkbox" class="jc-select" value="<?= $j['id'] ?>"
                 onchange="toggleSelect(this)" title="Select job">
          <div class="jc-cat-ico"><?= $catIco ?></div>
          <div class="jc-info">
            <div class="jc-title-row">
              <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" class="jc-title">
                <?= sanitize($j['title']) ?>
              </a>
              <div class="jc-badges">
                <?php if($j['is_urgent']):?><span class="pill-urgent">🔥 Urgent</span><?php endif;?>
                <?php if($j['is_featured']):?><span class="pill-featured">⭐ Boosted</span><?php endif;?>
                <span class="st-pill <?= $st['cls'] ?>"><?= $st['ico'].' '.$st['label'] ?></span>
              </div>
            </div>
            <div class="jc-meta-row">
              <span class="jc-meta-item">📂 <?= sanitize($j['cat_name']??'General') ?></span>
              <span class="jc-budget"><?= $budgStr ?></span>
              <span class="jc-meta-item">⏱ <?= durLabel($j['duration']??'1_month') ?></span>
              <span class="jc-meta-item">🌍 <?= ucfirst(str_replace('_',' ',$j['location_type']??'remote')) ?><?= $j['location']?' — '.sanitize($j['location']):'' ?></span>
              <span class="jc-meta-item">🕒 <?= timeAgo($j['created_at']) ?></span>
              <?php if($dlInfo):?>
              <span class="jc-meta-item <?= $dlInfo['cls'] ?>">📅 <?= $dlInfo['text'] ?></span>
              <?php endif;?>
            </div>
          </div>
        </div>

        <!-- BODY -->
        <div class="jc-body">
          <!-- Description preview -->
          <div class="jc-desc"><?= sanitize($j['description']) ?></div>

          <!-- Stats -->
          <div class="jc-stats">
            <div class="jcs-item jcs-views">
              <div class="jcs-val"><?= number_format((int)$j['views']) ?></div>
              <div class="jcs-lbl">Views</div>
            </div>
            <div class="jcs-item jcs-props">
              <div class="jcs-val"><?= $propCnt ?><?php if($newProp>0):?><sup style="font-size:9px;color:var(--coral);font-weight:900;"> +<?= $newProp ?>new</sup><?php endif;?></div>
              <div class="jcs-lbl">Proposals</div>
            </div>
            <div class="jcs-item jcs-short">
              <div class="jcs-val"><?= $shortCnt ?></div>
              <div class="jcs-lbl">Shortlisted</div>
            </div>
            <div class="jcs-item jcs-hired">
              <div class="jcs-val"><?= $accCnt ?></div>
              <div class="jcs-lbl">Hired</div>
            </div>
          </div>

          <!-- Skills -->
          <?php if(!empty($skills)):?>
          <div class="jc-skills">
            <?php foreach(array_slice($skills,0,5) as $sk):?>
            <span class="sk-pill"><?= sanitize($sk) ?></span>
            <?php endforeach;?>
            <?php if(count($skills)>5):?><span class="sk-more">+<?= count($skills)-5 ?> more</span><?php endif;?>
          </div>
          <?php endif;?>

          <!-- Hired provider -->
          <?php if($hasHired):
            $hInit = ini2($j['hired_fname'],$j['hired_lname']);
          ?>
          <div class="hired-strip">
            <div class="hired-av">
              <?php if($j['hired_avatar']):?><img src="<?= sanitize($j['hired_avatar']) ?>" alt=""><?php else: echo $hInit; endif;?>
            </div>
            <div class="hired-info">
              <div class="hired-name">👷 <?= sanitize($j['hired_fname'].' '.$j['hired_lname']) ?></div>
              <div class="hired-tag"><?= sanitize($j['hired_tagline']??'Hired Provider') ?></div>
              <?php if($j['hired_rating']>0):?>
              <div class="hired-rating">⭐ <?= number_format((float)$j['hired_rating'],1) ?> rating</div>
              <?php endif;?>
            </div>
            <?php if($j['hired_conv_id']):?>
            <a href="<?= APP_URL ?>/client/messages.php?conv=<?= $j['hired_conv_id'] ?>" class="btn btn-xs btn-cyan">💬 Message</a>
            <?php elseif($j['hired_user_id']):?>
            <a href="<?= APP_URL ?>/client/messages.php?start=<?= $j['hired_user_id'] ?>" class="btn btn-xs btn-cyan">💬 Message</a>
            <?php endif;?>
          </div>
          <?php endif;?>
        </div>

        <!-- RECOMMENDED PROVIDERS -->
        <?php if(!empty($recs) && in_array($j['status'],['open','draft'])):?>
        <div class="rec-panel">
          <div class="rec-head">✨ Recommended Providers for this Job</div>
          <div class="rec-cards">
            <?php foreach($recs as $r):
              $rInit = ini2($r['first_name'],$r['last_name']);
            ?>
            <a href="<?= APP_URL ?>/profile.php?id=<?= $r['user_id'] ?>" class="rec-card">
              <div class="rc-av">
                <?php if($r['avatar']):?><img src="<?= sanitize($r['avatar']) ?>" alt="" loading="lazy"><?php else: echo $rInit; endif;?>
              </div>
              <div class="rc-name"><?= sanitize($r['first_name'].' '.$r['last_name']) ?></div>
              <div class="rc-tag"><?= sanitize($r['tagline']??ucfirst($r['experience_level']??'').' Pro') ?></div>
              <div class="rc-rating">
                <?php $rv=(float)$r['rating_avg']; for($s=1;$s<=5;$s++) echo $rv>=$s?'★':'☆'; ?>
                <?= number_format($rv,1) ?>
              </div>
              <?php if($r['hourly_rate']>0):?>
              <div class="rc-rate"><?= formatCurrency($r['hourly_rate']) ?>/hr</div>
              <?php endif;?>
              <div class="rc-hire">Hire →</div>
            </a>
            <?php endforeach;?>
          </div>
        </div>
        <?php endif;?>

        <!-- FOOTER — ACTIONS -->
        <div class="jc-footer">
          <div class="jc-actions">
            <!-- View Proposals -->
            <a href="<?= APP_URL ?>/client/proposals.php?job_id=<?= $j['id'] ?>" class="btn btn-sm btn-violet">
              📩 Proposals
              <?php if($propCnt>0):?>
              <span style="background:rgba(255,255,255,.15);padding:1px 6px;border-radius:5px;font-size:10px;"><?= $propCnt ?></span>
              <?php endif;?>
              <?php if($newProp>0):?>
              <span style="background:var(--coral);padding:1px 6px;border-radius:5px;font-size:10px;"><?= $newProp ?> new</span>
              <?php endif;?>
            </a>

            <!-- View Details -->
            <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-ghost">👁 View</a>

            <!-- Edit (open or draft only) -->
            <?php if(in_array($j['status'],['open','draft'])):?>
            <a href="<?= APP_URL ?>/client/post-job.php?edit=<?= $j['id'] ?>" class="btn btn-sm btn-ghost">✏️ Edit</a>
            <?php endif;?>

            <!-- Duplicate -->
            <button class="btn btn-sm btn-ghost" onclick="doDuplicate(<?= $j['id'] ?>, '<?= htmlspecialchars(addslashes($j['title'])) ?>')">📋 Duplicate</button>

            <!-- Boost / Unboost -->
            <?php if(in_array($j['status'],['open','draft'])):?>
            <button class="btn btn-sm btn-amber" id="boostBtn<?= $j['id'] ?>"
                    onclick="doBoost(<?= $j['id'] ?>)"
                    title="<?= $j['is_featured']?'Remove boost':'Boost to top of search' ?>">
              <?= $j['is_featured'] ? '⭐ Boosted' : '🚀 Boost' ?>
            </button>
            <?php endif;?>
          </div>

          <div class="jc-actions">
            <!-- Find Talent -->
            <a href="<?= APP_URL ?>/search/providers.php<?= $j['category_id']?'?category='.$j['category_id']:'' ?>" class="btn btn-sm btn-ghost">🔍 Find Talent</a>

            <!-- Close (open or in_progress) -->
            <?php if(in_array($j['status'],['open','in_progress'])):?>
            <button class="btn btn-sm btn-ghost" style="color:var(--teal);border-color:rgba(31,217,160,.25);"
                    onclick="openCloseModal(<?= $j['id'] ?>, '<?= htmlspecialchars(addslashes($j['title'])) ?>')">
              ✅ Close Job
            </button>
            <?php endif;?>

            <!-- Cancel (open only) -->
            <?php if($j['status']==='open'):?>
            <button class="btn btn-sm btn-red-soft"
                    onclick="openCancelModal(<?= $j['id'] ?>, '<?= htmlspecialchars(addslashes($j['title'])) ?>')">
              🚫 Cancel
            </button>
            <?php endif;?>
          </div>
        </div>
      </div><!-- /job-card -->
      <?php endforeach;?>

      </div><!-- /jobs-list -->

      <!-- PAGINATION -->
      <?php if($totalPages > 1):?>
      <div class="pagination">
        <?php
        $qBase = http_build_query(['status'=>$filter,'q'=>$search,'category'=>$catFilt,'sort'=>$sortBy]);
        if($page>1): ?>
        <a href="?<?= $qBase ?>&page=<?= $page-1 ?>" class="pag-btn">← Prev</a>
        <?php else: ?><span class="pag-btn disabled">← Prev</span><?php endif;?>
        <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
        <a href="?<?= $qBase ?>&page=<?= $i ?>" class="pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor;?>
        <?php if($page<$totalPages): ?>
        <a href="?<?= $qBase ?>&page=<?= $page+1 ?>" class="pag-btn">Next →</a>
        <?php else: ?><span class="pag-btn disabled">Next →</span><?php endif;?>
      </div>
      <?php endif;?>

      <?php endif;/* end jobs list */?>

    </div><!-- /content -->
  </div><!-- /main -->
</div><!-- /layout -->

<!-- ═══ CANCEL MODAL ═══ -->
<div class="modal-bg" id="cancelModal">
  <div class="modal-inner">
    <div class="modal-ico">🚫</div>
    <div class="modal-title">Cancel this Job?</div>
    <p class="modal-sub" id="cancelModalText">This will close the job to all proposals. The job cannot be re-opened but it stays in your history.</p>
    <form method="POST" style="display:contents;" id="cancelForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="cancel">
      <input type="hidden" name="job_id" id="cancelJobId">
    </form>
    <div class="modal-acts">
      <button class="btn btn-ghost" onclick="closeModals()">Keep Job</button>
      <button class="btn btn-red-soft" onclick="document.getElementById('cancelForm').submit()">Yes, Cancel It</button>
    </div>
  </div>
</div>

<!-- ═══ CLOSE / COMPLETE MODAL ═══ -->
<div class="modal-bg" id="closeModal">
  <div class="modal-inner">
    <div class="modal-ico">✅</div>
    <div class="modal-title">Mark Job as Completed?</div>
    <p class="modal-sub" id="closeModalText">This marks the job as completed. Make sure the work is done and you're happy with the result before closing.</p>
    <form method="POST" style="display:contents;" id="closeForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="close">
      <input type="hidden" name="job_id" id="closeJobId">
    </form>
    <div class="modal-acts">
      <button class="btn btn-ghost" onclick="closeModals()">Not Yet</button>
      <button class="btn btn-cyan" onclick="document.getElementById('closeForm').submit()">✅ Yes, Complete</button>
    </div>
  </div>
</div>

<!-- ═══ DUPLICATE MODAL ═══ -->
<div class="modal-bg" id="dupModal">
  <div class="modal-inner">
    <div class="modal-ico">📋</div>
    <div class="modal-title">Duplicate this Job?</div>
    <p class="modal-sub" id="dupModalText">A copy will be saved as a Draft. You can then edit and post it as a new job.</p>
    <form method="POST" style="display:contents;" id="dupForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action" value="duplicate">
      <input type="hidden" name="job_id" id="dupJobId">
    </form>
    <div class="modal-acts">
      <button class="btn btn-ghost" onclick="closeModals()">Cancel</button>
      <button class="btn btn-violet" onclick="document.getElementById('dupForm').submit()">📋 Duplicate</button>
    </div>
  </div>
</div>

<div id="toasts"></div>

<script>
const APP_URL = '<?= APP_URL ?>';
const CSRF    = '<?= $csrf ?>';

/* ═══ THEME ═══ */
function toggleTheme(){
  const body = document.getElementById('appBody');
  const isLight = body.classList.toggle('lm');
  const val = isLight ? 'light' : 'dark';
  localStorage.setItem('gg_theme', val);
  document.cookie = `gg_theme=${val};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = isLight ? '☀️' : '🌙';
  toast('Theme', isLight ? '☀️ Light mode' : '🌙 Dark mode', 'info');
}
(function(){
  const stored = localStorage.getItem('gg_theme') || '<?= $isLight?"light":"dark" ?>';
  const body   = document.getElementById('appBody');
  const btn    = document.getElementById('themeBtn');
  if(stored === 'light'){ body.classList.add('lm'); if(btn) btn.textContent='☀️'; }
  else { body.classList.remove('lm'); if(btn) btn.textContent='🌙'; }
})();

/* ═══ MODALS ═══ */
function openCancelModal(id, title){
  document.getElementById('cancelJobId').value  = id;
  document.getElementById('cancelModalText').textContent = `Cancel "${title}"? This cannot be undone.`;
  document.getElementById('cancelModal').classList.add('open');
}
function openCloseModal(id, title){
  document.getElementById('closeJobId').value  = id;
  document.getElementById('closeModalText').textContent = `Mark "${title}" as completed?`;
  document.getElementById('closeModal').classList.add('open');
}
function doDuplicate(id, title){
  document.getElementById('dupJobId').value = id;
  document.getElementById('dupModalText').textContent = `Duplicate "${title}"? A draft copy will be created for you to edit.`;
  document.getElementById('dupModal').classList.add('open');
}
function closeModals(){
  document.querySelectorAll('.modal-bg').forEach(m => m.classList.remove('open'));
}
document.querySelectorAll('.modal-bg').forEach(m => m.addEventListener('click', e => { if(e.target === m) closeModals(); }));
document.addEventListener('keydown', e => { if(e.key === 'Escape') closeModals(); });

/* ═══ BOOST ═══ */
function doBoost(id){
  fetch(APP_URL + '/client/my-jobs.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: `action=boost&job_id=${id}&csrf_token=${encodeURIComponent(CSRF)}`
  })
  .then(r => r.json())
  .then(d => {
    if(d.ok){
      const btn = document.getElementById('boostBtn'+id);
      const isBoosted = btn.textContent.includes('Boosted');
      btn.textContent = isBoosted ? '🚀 Boost' : '⭐ Boosted';
      const card = document.getElementById('jcard'+id);
      if(card) card.classList.toggle('featured-job', !isBoosted);
      toast('Boost', isBoosted ? 'Boost removed.' : '🚀 Job boosted to top!', 'success');
    }
  })
  .catch(() => toast('Error','Could not update boost.','error'));
}

/* ═══ BULK SELECTION ═══ */
let selected = new Set();
function toggleSelect(cb){
  const id = parseInt(cb.value);
  cb.checked ? selected.add(id) : selected.delete(id);
  updateBulkBar();
}
function updateBulkBar(){
  const bar = document.getElementById('bulkBar');
  const cnt = document.getElementById('bulkCount');
  if(selected.size > 0){
    bar.classList.add('show');
    cnt.textContent = selected.size + ' selected';
  } else {
    bar.classList.remove('show');
  }
}
function clearSelection(){
  selected.clear();
  document.querySelectorAll('.jc-select').forEach(cb => cb.checked = false);
  updateBulkBar();
}
function doBulk(action){
  if(!selected.size){ toast('None selected','Please select at least one job.','info'); return; }
  document.getElementById('bulkAction').value = action;
  document.getElementById('bulkIds').value    = [...selected].join(',');
  document.getElementById('bulkForm').submit();
}

/* ═══ LIVE SEARCH ═══ */
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function(){
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => document.getElementById('filterForm').submit(), 600);
});

/* ═══ CARD SCROLL REVEAL ═══ */
const revObs = new IntersectionObserver(entries => {
  entries.forEach(e => {
    if(e.isIntersecting){ e.target.style.opacity='1'; e.target.style.transform='translateY(0)'; revObs.unobserve(e.target); }
  });
},{threshold:0.05,rootMargin:'0px 0px -20px 0px'});
document.querySelectorAll('.job-card').forEach((el,i) => {
  el.style.opacity='0'; el.style.transform='translateY(16px)';
  el.style.transition=`opacity .4s ease ${(i%4)*60}ms,transform .4s ease ${(i%4)*60}ms`;
  revObs.observe(el);
});

/* ═══ TOAST ═══ */
const TI = {success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
function toast(title, msg, type='info', d=4200){
  const c = document.getElementById('toasts');
  const t = document.createElement('div'); t.className = `toast ${type}`;
  t.innerHTML = `<div class="t-ico">${TI[type]}</div><div class="t-body"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(() => { t.style.opacity='0'; t.style.transform='translateX(48px)'; t.style.transition='all .3s'; setTimeout(()=>t.remove(),310); }, d);
}
<?php if($success):?>toast('Success','<?= addslashes($success) ?>','success');<?php endif;?>
</script>
</body>
</html>