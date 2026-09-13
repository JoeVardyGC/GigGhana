<?php
/**
 * GigGhana — client/post-job.php  (v2 — form submission fixed)
 *
 * FIXES:
 *  1. POST handler moved OUTSIDE the data-loading try block — runs independently
 *  2. All DB errors shown as visible alerts, not silently swallowed
 *  3. CSRF verification failure shows error instead of dying silently
 *  4. slug / uuid generation fallbacks added
 *  5. $errors always defined before use
 *  6. submitPost() no longer closes modal before submit (modal close was racing the navigation)
 *  7. form action attribute explicitly set to current page URL
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('client');

$userId  = (int)$_SESSION['user_id'];
$user    = getUserById($userId);
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';
$csrf    = generateCSRF();

$editId       = (int)($_GET['edit'] ?? 0);
$editJob      = null;
$editSkillIds = [];
$errors       = [];   /* always defined */
$success      = isset($_GET['success']) ? sanitize($_GET['success']) : '';

/* ═══════════════════════════════════════════════════════════
   POST HANDLER — completely separate from data loading below
   This runs first, before any DB reads for page display.
═══════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {

    /* CSRF check — show error rather than die silently */
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid. Please reload the page and try again.';
    } else {
        $action = $_POST['action'];

        if ($action === 'post_job' || $action === 'save_draft') {

            $status   = ($action === 'post_job') ? 'open' : 'draft';
            $postEditId = (int)($_POST['edit_id'] ?? 0);

            /* Collect & sanitize inputs */
            $title    = trim($_POST['title']        ?? '');
            $catId    = (int)($_POST['category_id'] ?? 0) ?: null;
            $desc     = trim($_POST['description']  ?? '');
            $reqs     = trim($_POST['requirements'] ?? '');
            $budgType = in_array($_POST['budget_type'] ?? '', ['fixed','hourly'])
                        ? $_POST['budget_type'] : 'fixed';
            $budgMin  = (float)($_POST['budget_min'] ?? 0);
            $budgMax  = (float)($_POST['budget_max'] ?? 0);
            $duration = $_POST['duration'] ?? '1_month';
            $expLvl   = $_POST['experience_level'] ?? 'any';
            $locType  = in_array($_POST['location_type'] ?? '', ['remote','onsite','hybrid'])
                        ? $_POST['location_type'] : 'remote';
            /* Location: use custom text if provided, otherwise dropdown value */
            $location = trim($_POST['location_custom'] ?? '') ?: trim($_POST['location'] ?? '');
            $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
            $isUrgent = isset($_POST['is_urgent']) ? 1 : 0;
            $skillIds = array_filter(array_map('intval', $_POST['skills'] ?? []));

            /* Validate */
            if (strlen($title) < 10 || strlen($title) > 100)
                $errors[] = 'Job title must be 10–100 characters.';
            if (strlen($desc) < 50)
                $errors[] = 'Description must be at least 50 characters.';
            if ($budgMin < 0)
                $errors[] = 'Budget minimum cannot be negative.';
            if ($deadline && strtotime($deadline) < time())
                $errors[] = 'Deadline must be a future date.';
            if (!in_array($duration, ['less_1_week','1_2_weeks','1_month','3_months','6_months','ongoing']))
                $duration = '1_month';

            if (empty($errors)) {
                try {
                    $db = getDB();

                    /* Slug & UUID helpers — inline fallbacks if functions missing */
                    $makeSlug = function(string $s): string {
                        if (function_exists('generateSlug')) return generateSlug($s);
                        $s = strtolower(trim($s));
                        $s = preg_replace('/[^a-z0-9\s-]/', '', $s);
                        $s = preg_replace('/[\s-]+/', '-', $s);
                        return substr(trim($s, '-'), 0, 80);
                    };
                    $makeUuid = function(): string {
                        if (function_exists('generateUUID')) return generateUUID();
                        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                            mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
                            mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,
                            mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
                    };

                    $slug = $makeSlug($title) . '-' . substr(uniqid(), -6);
                    $uuid = $makeUuid();

                    if ($postEditId) {
                        /* UPDATE existing job */
                        $stUp = $db->prepare("
                            UPDATE jobs SET
                              category_id=?, title=?, slug=?, description=?, requirements=?,
                              budget_type=?, budget_min=?, budget_max=?, duration=?,
                              experience_level=?, location_type=?, location=?,
                              deadline=?, is_urgent=?, status=?, updated_at=NOW()
                            WHERE id=? AND client_id=?
                        ");
                        $stUp->execute([
                            $catId, $title, $slug, $desc, $reqs,
                            $budgType, $budgMin, $budgMax, $duration,
                            $expLvl, $locType, $location,
                            $deadline, $isUrgent, $status,
                            $postEditId, $userId
                        ]);
                        $jobId = $postEditId;
                        $db->prepare("DELETE FROM job_skills WHERE job_id=?")->execute([$jobId]);

                    } else {
                        /* INSERT new job */
                        $stIn = $db->prepare("
                            INSERT INTO jobs
                              (uuid, client_id, category_id, title, slug,
                               description, requirements,
                               budget_type, budget_min, budget_max,
                               duration, experience_level,
                               location_type, location, deadline, is_urgent, status)
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                        ");
                        $stIn->execute([
                            $uuid, $userId, $catId, $title, $slug,
                            $desc, $reqs,
                            $budgType, $budgMin, $budgMax,
                            $duration, $expLvl,
                            $locType, $location, $deadline, $isUrgent, $status
                        ]);
                        $jobId = (int)$db->lastInsertId();
                    }

                    /* Insert skills */
                    if ($jobId && !empty($skillIds)) {
                        $stSk = $db->prepare(
                            "INSERT IGNORE INTO job_skills (job_id, skill_id) VALUES (?,?)"
                        );
                        foreach ($skillIds as $sid) {
                            if ($sid > 0) $stSk->execute([$jobId, $sid]);
                        }
                    }

                    /* Notification for live posts */
                    if ($status === 'open' && $jobId) {
                        try {
                            $db->prepare("
                                INSERT INTO notifications (user_id, type, title, message, data)
                                VALUES (?,?,?,?,?)
                            ")->execute([
                                $userId,
                                'job_posted',
                                'Job Posted Successfully',
                                "Your job \"$title\" is now live and accepting proposals.",
                                json_encode(['job_id' => $jobId])
                            ]);
                        } catch (Exception $ne) {
                            error_log('Notification error: '.$ne->getMessage());
                            /* Non-fatal — job was saved, just notification failed */
                        }
                    }

                    /* Redirect on success */
                    if ($status === 'open') {
                        header('Location: '.APP_URL.'/client/my-jobs.php?success='.
                            urlencode('Your job is now live! Providers can submit proposals.'));
                    } else {
                        header('Location: '.APP_URL.'/client/post-job.php?edit='.$jobId.
                            '&success='.urlencode('Draft saved successfully.'));
                    }
                    exit;

                } catch (Exception $e) {
                    error_log('post-job POST error: '.$e->getMessage());
                    $errors[] = 'Database error: '.$e->getMessage().
                                ' — Please try again or contact support.';
                }
            }
        }
    }
}

/* ═══════════════════════════════════════════════════════════
   DATA LOADING — categories, skills, edit pre-fill
   Runs for GET requests and for failed POST (re-display form)
═══════════════════════════════════════════════════════════ */
$cats        = [];
$skillsByCat = [];

try {
    $db = getDB();

    $cats = $db->query(
        "SELECT id, name, icon FROM categories WHERE is_active=1 ORDER BY sort_order, name"
    )->fetchAll();

    $skillsRaw = $db->query(
        "SELECT id, name, slug, category_id FROM skills WHERE is_active=1 ORDER BY name"
    )->fetchAll();
    foreach ($skillsRaw as $sk) {
        $skillsByCat[(int)$sk['category_id']][] = $sk;
    }

    if ($editId) {
        $stE = $db->prepare("SELECT * FROM jobs WHERE id=? AND client_id=? LIMIT 1");
        $stE->execute([$editId, $userId]);
        $editJob = $stE->fetch();
        if ($editJob) {
            $stES = $db->prepare("SELECT skill_id FROM job_skills WHERE job_id=?");
            $stES->execute([$editId]);
            $editSkillIds = array_column($stES->fetchAll(), 'skill_id');
        }
    }
} catch (Exception $e) {
    error_log('post-job data load error: '.$e->getMessage());
    $errors[] = 'Could not load form data: '.$e->getMessage();
}

/* ═══════════════════════════════════════════════════════════
   PRE-FILL VALUES
═══════════════════════════════════════════════════════════ */
/* On failed POST, re-fill from $_POST so user doesn't lose their input */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $f = [
        'title'            => trim($_POST['title']           ?? ''),
        'category_id'      => (int)($_POST['category_id']   ?? 0),
        'description'      => trim($_POST['description']    ?? ''),
        'requirements'     => trim($_POST['requirements']   ?? ''),
        'budget_type'      => $_POST['budget_type']         ?? 'fixed',
        'budget_min'       => $_POST['budget_min']          ?? '',
        'budget_max'       => $_POST['budget_max']          ?? '',
        'duration'         => $_POST['duration']            ?? '1_month',
        'experience_level' => $_POST['experience_level']    ?? 'any',
        'location_type'    => $_POST['location_type']       ?? 'remote',
        'location'         => trim($_POST['location']       ?? ''),
        'deadline'         => $_POST['deadline']            ?? '',
        'is_urgent'        => isset($_POST['is_urgent']) ? 1 : 0,
        'skills'           => array_filter(array_map('intval', $_POST['skills'] ?? [])),
    ];
} elseif ($editJob) {
    $f = [
        'title'            => $editJob['title'],
        'category_id'      => $editJob['category_id'],
        'description'      => $editJob['description'],
        'requirements'     => $editJob['requirements'],
        'budget_type'      => $editJob['budget_type'],
        'budget_min'       => $editJob['budget_min'],
        'budget_max'       => $editJob['budget_max'],
        'duration'         => $editJob['duration'],
        'experience_level' => $editJob['experience_level'],
        'location_type'    => $editJob['location_type'],
        'location'         => $editJob['location'],
        'deadline'         => $editJob['deadline'],
        'is_urgent'        => $editJob['is_urgent'],
        'skills'           => $editSkillIds,
    ];
} else {
    $f = [
        'title'=>'','category_id'=>'','description'=>'','requirements'=>'',
        'budget_type'=>'fixed','budget_min'=>'','budget_max'=>'',
        'duration'=>'1_month','experience_level'=>'any',
        'location_type'=>'remote','location'=>'','deadline'=>'',
        'is_urgent'=>0,'skills'=>[],
    ];
}

$myInit   = strtoupper(substr($user['first_name']??'M',0,1).substr($user['last_name']??'',0,1));
$myAvatar = $user['avatar'] ?? '';

$iconMap = [
    'code'=>'💻','smartphone'=>'📱','pen-tool'=>'🎨','trending-up'=>'📈',
    'file-text'=>'✍️','film'=>'🎬','cpu'=>'🤖','dollar-sign'=>'💰',
    'briefcase'=>'⚖️','headphones'=>'🎧','tool'=>'🔧','bar-chart'=>'🍽️',
    'globe'=>'🌿','music'=>'🎵',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $editId ? 'Edit Job' : 'Post a Job' ?> — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0C0E14;--s1:#111520;--s2:#181C28;--s3:#1F2436;--s4:#252B3E;
  --glass:rgba(24,28,40,.88);
  --cyan:#00D4C8;--cyan-d:#00B8AD;--cyan-glo:rgba(0,212,200,.18);
  --coral:#FF6B4A;--coral-d:#E85A39;--coral-glo:rgba(255,107,74,.18);
  --violet:#7C6FF7;--teal:#1FD9A0;--amber:#F7B731;--red:#FF4D6D;--blue:#4E9EFF;
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
  --violet:#5B4FD9;--teal:#0DAF80;--amber:#D4980A;--red:#D63050;
  --tx:#0D1220;--tx-2:#344060;--tx-3:#6B7A99;--tx-4:#9BA8BF;
  --bd:rgba(30,40,80,.09);--bd2:rgba(30,40,80,.18);
}
.lm .topnav{background:rgba(234,238,247,.97);}
.lm .sidebar{background:var(--s1);}
.lm .step-card{background:rgba(255,255,255,.9);border-color:var(--bd);}
.lm .step-card.active{border-color:var(--cyan);}
.lm .field-input,.lm .field-select,.lm .field-textarea{background:rgba(0,0,0,.05);border-color:var(--bd2);}
.lm .skill-tag{background:rgba(0,0,0,.06);border-color:var(--bd2);}
.lm .skill-tag.sel{background:rgba(0,158,149,.12);border-color:var(--cyan);color:var(--cyan);}
.lm .cat-btn{background:rgba(0,0,0,.05);border-color:var(--bd);}
.lm .cat-btn.sel{background:rgba(0,158,149,.1);border-color:var(--cyan);}
.lm .budget-type-btn{background:rgba(0,0,0,.05);border-color:var(--bd);}
.lm .budget-type-btn.sel{background:rgba(0,158,149,.1);border-color:var(--cyan);color:var(--cyan);}
.lm .loc-btn{background:rgba(0,0,0,.05);}
.lm .loc-btn.sel{background:rgba(0,158,149,.1);border-color:var(--cyan);color:var(--cyan);}
.lm .summary-box{background:rgba(255,255,255,.88);}
.lm .step-nav{background:rgba(234,238,247,.97);}
.lm .modal-box{background:var(--s2);}

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{background:var(--bg);color:var(--tx);font-family:var(--fb);font-size:14.5px;line-height:1.65;-webkit-font-smoothing:antialiased;transition:background .3s,color .3s;}
img{display:block;max-width:100%;}a{text-decoration:none;color:inherit;}
button,input,textarea,select{font-family:var(--fb);}
::-webkit-scrollbar{width:4px;}::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--s4);border-radius:2px;}

/* NAV */
.topnav{height:var(--nav-h);padding:0 18px;position:sticky;top:0;z-index:300;display:flex;align-items:center;justify-content:space-between;background:rgba(12,14,20,.92);backdrop-filter:blur(28px);border-bottom:1px solid var(--bd);transition:background .3s,border-color .3s;}
.nav-logo{display:flex;align-items:center;gap:9px;}
.logo-mark{width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,var(--cyan),var(--teal));display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-weight:900;font-size:15px;color:#000;}
.logo-txt{font-family:var(--fh);font-size:18px;font-weight:900;}
.logo-txt span{color:var(--cyan);}
.nav-links{display:flex;gap:3px;}
.navlink{padding:7px 13px;border-radius:50px;font-size:12.5px;font-weight:700;font-family:var(--fh);color:var(--tx-3);transition:var(--e);}
.navlink:hover{background:rgba(255,255,255,.055);color:var(--tx);}
.navlink.on{background:rgba(0,212,200,.1);color:var(--cyan);}
.nav-r{display:flex;align-items:center;gap:8px;}
.theme-btn,.icon-btn{width:36px;height:36px;border-radius:var(--rs);background:rgba(255,255,255,.04);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;cursor:pointer;transition:var(--e);font-size:15px;color:var(--tx-2);}
.theme-btn:hover,.icon-btn:hover{background:rgba(255,255,255,.08);color:var(--tx);}
.uchip{display:flex;align-items:center;gap:7px;background:rgba(255,255,255,.04);border:1px solid var(--bd);border-radius:50px;padding:3px 12px 3px 4px;font-size:12.5px;font-weight:700;font-family:var(--fh);}
.uchip-av{width:26px;height:26px;border-radius:50%;overflow:hidden;background:linear-gradient(135deg,var(--violet),var(--cyan));display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#fff;}
.uchip-av img{width:100%;height:100%;object-fit:cover;}

/* LAYOUT */
.page{display:flex;min-height:calc(100vh - var(--nav-h));}
.sidebar{width:var(--sb);flex-shrink:0;border-right:1px solid var(--bd);background:var(--s1);display:flex;flex-direction:column;position:sticky;top:var(--nav-h);height:calc(100vh - var(--nav-h));overflow-y:auto;transition:background .3s,border-color .3s;}
.sb-section{font-size:9px;font-weight:800;letter-spacing:1.8px;text-transform:uppercase;color:var(--tx-3);padding:6px 12px;margin:14px 0 4px;}
.sb-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:var(--tx-3);font-size:13px;font-weight:500;transition:var(--e);text-decoration:none;margin:0 8px;}
.sb-item:hover{background:rgba(255,255,255,.05);color:var(--tx);}
.sb-item.active{background:rgba(0,212,200,.1);color:var(--cyan);border-left:3px solid var(--cyan);padding-left:9px;}
.sb-item.danger{color:var(--red);}
.sb-item.danger:hover{background:rgba(255,77,106,.08);}
.sb-gap{flex:1;}
.sb-user{padding:14px 10px;border-top:1px solid var(--bd);}
.sb-ucard{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;background:rgba(0,0,0,.2);}
.sb-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--coral),var(--violet));display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:13px;font-weight:800;color:#fff;overflow:hidden;flex-shrink:0;}
.sb-av img{width:100%;height:100%;object-fit:cover;}
.sb-uname{font-size:13px;font-weight:700;}
.sb-urole{font-size:10px;color:var(--coral);font-weight:700;text-transform:uppercase;margin-top:1px;}

.main-wrap{flex:1;padding:32px 40px 80px;min-width:0;}
.breadcrumb{display:flex;align-items:center;gap:7px;font-size:12px;color:var(--tx-3);margin-bottom:8px;}
.breadcrumb a{color:var(--cyan);}
.pg-head{margin-bottom:28px;}
.pg-title{font-family:var(--fh);font-size:clamp(22px,3vw,30px);font-weight:900;margin-bottom:6px;}
.pg-sub{font-size:13.5px;color:var(--tx-2);}

/* PROGRESS */
.progress-bar{display:flex;align-items:center;margin-bottom:36px;}
.prog-step{display:flex;flex-direction:column;align-items:center;flex:1;position:relative;cursor:pointer;}
.prog-step::before{content:'';position:absolute;top:18px;left:50%;right:-50%;height:2px;background:var(--bd);z-index:0;}
.prog-step:last-child::before{display:none;}
.prog-step.done::before,.prog-step.active::before{background:var(--cyan);}
.ps-circle{width:36px;height:36px;border-radius:50%;border:2.5px solid var(--bd);background:var(--s2);display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:13px;font-weight:800;color:var(--tx-3);position:relative;z-index:1;transition:var(--e);}
.prog-step.done .ps-circle{background:var(--teal);border-color:var(--teal);color:#000;}
.prog-step.active .ps-circle{background:var(--cyan);border-color:var(--cyan);color:#000;box-shadow:0 0 0 5px var(--cyan-glo);}
.ps-label{margin-top:7px;font-size:11px;font-weight:700;font-family:var(--fh);color:var(--tx-3);text-align:center;white-space:nowrap;}
.prog-step.active .ps-label{color:var(--cyan);}
.prog-step.done .ps-label{color:var(--teal);}

/* STEP CARDS */
.step-card{background:var(--glass);backdrop-filter:blur(14px);border:1.5px solid var(--bd);border-radius:20px;padding:32px;margin-bottom:24px;display:none;animation:fadeUp .28s ease;transition:background .3s,border-color .3s;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}
.step-card.active{display:block;border-color:rgba(0,212,200,.18);}
.step-heading{display:flex;align-items:center;gap:13px;margin-bottom:26px;padding-bottom:18px;border-bottom:1px solid var(--bd);}
.step-num{width:40px;height:40px;border-radius:12px;flex-shrink:0;background:linear-gradient(135deg,var(--cyan),var(--teal));display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:16px;font-weight:900;color:#000;}
.step-ttl{font-family:var(--fh);font-size:17px;font-weight:900;}
.step-sub{font-size:12.5px;color:var(--tx-3);margin-top:2px;}

/* FIELDS */
.field-group{margin-bottom:22px;}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;}
.field-label{display:flex;align-items:center;gap:6px;font-family:var(--fh);font-size:12.5px;font-weight:700;margin-bottom:8px;color:var(--tx-2);}
.field-req{color:var(--coral);font-size:11px;margin-left:2px;}
.field-hint{font-size:11px;color:var(--tx-3);font-weight:400;margin-left:auto;}
.field-input,.field-select,.field-textarea{width:100%;background:rgba(0,0,0,.25);border:1.5px solid var(--bd);border-radius:var(--rs);padding:11px 14px;color:var(--tx);font-size:14px;outline:none;transition:var(--e);}
.field-input:focus,.field-select:focus,.field-textarea:focus{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-glo);}
.field-input.err,.field-textarea.err{border-color:var(--red);}
.field-select{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%235C6A85' stroke-width='1.8' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;}
.field-textarea{resize:vertical;min-height:130px;line-height:1.65;}
.char-count{font-size:11px;color:var(--tx-3);text-align:right;margin-top:4px;}
.char-count.warn{color:var(--amber);}.char-count.ok{color:var(--teal);}
.field-err{font-size:11.5px;color:var(--red);margin-top:5px;display:none;}
.field-err.show{display:block;}

/* CATEGORY GRID */
.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:6px;}
.cat-btn{display:flex;flex-direction:column;align-items:center;gap:8px;padding:16px 10px;border-radius:14px;cursor:pointer;background:rgba(0,0,0,.18);border:1.5px solid var(--bd);transition:var(--e);text-align:center;}
.cat-btn:hover{border-color:var(--cyan);background:rgba(0,212,200,.05);}
.cat-btn.sel{background:rgba(0,212,200,.1);border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-glo);}
.cat-ico{font-size:22px;}
.cat-name{font-family:var(--fh);font-size:11.5px;font-weight:700;line-height:1.3;}
.cat-btn input{display:none;}

/* BUDGET */
.budget-type-row{display:flex;gap:12px;margin-bottom:18px;}
.budget-type-btn{flex:1;padding:13px 16px;border-radius:12px;border:1.5px solid var(--bd);background:rgba(0,0,0,.18);cursor:pointer;transition:var(--e);text-align:center;}
.budget-type-btn.sel{background:rgba(0,212,200,.1);border-color:var(--cyan);color:var(--cyan);}
.btt-ico{font-size:20px;margin-bottom:4px;}
.btt-lbl{font-family:var(--fh);font-size:13px;font-weight:800;}
.btt-sub{font-size:11px;color:var(--tx-3);margin-top:2px;}
.budget-type-btn.sel .btt-sub{color:var(--cyan);}
.budget-type-btn input{display:none;}
.budget-preview{background:rgba(0,212,200,.05);border:1px solid rgba(0,212,200,.15);border-radius:12px;padding:14px 18px;margin-top:14px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;}
.bp-label{font-size:12px;color:var(--tx-3);font-weight:600;}
.bp-val{font-family:var(--fh);font-size:18px;font-weight:900;color:var(--cyan);}

/* LOCATION */
.loc-row{display:flex;gap:10px;margin-bottom:14px;}
.loc-btn{flex:1;padding:11px 10px;border-radius:12px;border:1.5px solid var(--bd);background:rgba(0,0,0,.18);cursor:pointer;transition:var(--e);text-align:center;font-family:var(--fh);font-size:12.5px;font-weight:700;}
.loc-btn.sel{background:rgba(0,212,200,.1);border-color:var(--cyan);color:var(--cyan);}
.loc-btn input{display:none;}

/* SKILLS */
.skills-search{margin-bottom:12px;}
.skills-panel{max-height:280px;overflow-y:auto;padding:4px 0;}
.skill-cat-header{font-size:10.5px;font-weight:800;letter-spacing:.8px;text-transform:uppercase;color:var(--tx-3);padding:6px 0 4px;margin-top:8px;font-family:var(--fh);}
.skill-tags-row{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:4px;}
.skill-tag{padding:5px 12px;border-radius:8px;border:1.5px solid var(--bd);background:rgba(0,0,0,.18);cursor:pointer;font-size:12.5px;font-weight:600;transition:var(--e);color:var(--tx-2);user-select:none;}
.skill-tag:hover{border-color:var(--cyan);color:var(--tx);}
.skill-tag.sel{background:rgba(0,212,200,.12);border-color:var(--cyan);color:var(--cyan);font-weight:700;}
.selected-skills{display:flex;flex-wrap:wrap;gap:7px;min-height:36px;padding:10px;background:rgba(0,0,0,.15);border:1.5px dashed var(--bd);border-radius:var(--rs);margin-bottom:10px;}
.sel-tag{display:flex;align-items:center;gap:5px;padding:4px 10px;border-radius:7px;background:rgba(0,212,200,.15);border:1px solid rgba(0,212,200,.3);font-size:12px;font-weight:700;color:var(--cyan);cursor:pointer;transition:var(--e);}
.sel-tag:hover{background:rgba(255,77,109,.15);border-color:rgba(255,77,109,.3);color:var(--red);}
.no-skills-msg{font-size:12.5px;color:var(--tx-3);text-align:center;padding:8px 0;}

/* EXTRAS */
.extras-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}
.extra-card{padding:16px;border-radius:14px;border:1.5px solid var(--bd);background:rgba(0,0,0,.18);cursor:pointer;transition:var(--e);}
.extra-card.on{border-color:var(--cyan);background:rgba(0,212,200,.07);}
.extra-card input{display:none;}
.extra-ico{font-size:22px;margin-bottom:7px;}
.extra-lbl{font-family:var(--fh);font-size:12.5px;font-weight:800;margin-bottom:2px;}
.extra-sub{font-size:11px;color:var(--tx-3);}
.extra-card.on .extra-sub{color:var(--cyan);}
.toggle-dot{width:32px;height:18px;border-radius:9px;background:var(--bd);display:inline-flex;align-items:center;padding:2px;transition:background .25s;margin-top:8px;}
.toggle-dot::after{content:'';width:14px;height:14px;border-radius:50%;background:#fff;transition:transform .25s;}
.extra-card.on .toggle-dot{background:var(--cyan);}
.extra-card.on .toggle-dot::after{transform:translateX(14px);}

/* UPLOAD */
.upload-zone{border:2px dashed var(--bd);border-radius:var(--r);padding:36px 24px;text-align:center;cursor:pointer;transition:var(--e);position:relative;background:rgba(0,0,0,.1);}
.upload-zone:hover,.upload-zone.drag{border-color:var(--cyan);background:rgba(0,212,200,.04);}
.upload-zone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.uz-ico{font-size:36px;margin-bottom:10px;}
.uz-ttl{font-family:var(--fh);font-size:14px;font-weight:800;margin-bottom:4px;}
.uz-sub{font-size:12px;color:var(--tx-3);}
.upload-previews{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px;}
.up-item{width:80px;height:80px;border-radius:11px;overflow:hidden;background:var(--s4);border:1px solid var(--bd);position:relative;display:flex;align-items:center;justify-content:center;font-size:26px;}
.up-item img{width:100%;height:100%;object-fit:cover;}
.up-rm{position:absolute;top:-5px;right:-5px;width:18px;height:18px;border-radius:50%;background:var(--red);color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;cursor:pointer;border:2px solid var(--bg);}

/* SUMMARY */
.vis-panel{background:linear-gradient(135deg,rgba(124,111,247,.07),rgba(0,212,200,.05));border:1px solid rgba(124,111,247,.22);border-radius:14px;padding:18px 20px;margin-bottom:24px;display:flex;gap:14px;align-items:flex-start;}
.vis-ico{font-size:22px;flex-shrink:0;}
.vis-txt{font-size:12.5px;color:var(--tx-2);line-height:1.7;}
.vis-txt strong{color:var(--violet);}
.summary-box{background:var(--glass);backdrop-filter:blur(14px);border:1.5px solid var(--bd);border-radius:20px;overflow:hidden;margin-bottom:20px;}
.sum-head{padding:18px 24px;background:linear-gradient(135deg,rgba(0,212,200,.07),rgba(31,217,160,.04));border-bottom:1px solid var(--bd);}
.sum-title{font-family:var(--fh);font-size:15px;font-weight:800;}
.review-row{display:flex;gap:16px;padding:14px 24px;border-bottom:1px solid var(--bd);}
.review-row:last-child{border-bottom:none;}
.rr-label{min-width:140px;font-size:12px;font-weight:700;font-family:var(--fh);color:var(--tx-3);flex-shrink:0;}
.rr-val{font-size:13px;color:var(--tx);flex:1;}
.rr-pill{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:700;font-family:var(--fh);}
.rr-cyan{background:rgba(0,212,200,.1);color:var(--cyan);border:1px solid rgba(0,212,200,.2);}
.rr-coral{background:rgba(255,107,74,.1);color:var(--coral);border:1px solid rgba(255,107,74,.2);}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:var(--rs);font-size:13px;font-weight:700;font-family:var(--fh);border:none;cursor:pointer;transition:var(--e);white-space:nowrap;text-decoration:none;}
.btn-ghost{background:rgba(255,255,255,.05);border:1px solid var(--bd);color:var(--tx-2);}
.btn-ghost:hover{background:rgba(255,255,255,.09);border-color:var(--bd2);color:var(--tx);}
.btn-cyan{background:linear-gradient(135deg,var(--cyan),var(--teal));color:#000;font-weight:800;box-shadow:0 3px 14px var(--cyan-glo);}
.btn-cyan:hover{transform:translateY(-2px);}
.btn-coral{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:800;box-shadow:0 3px 14px var(--coral-glo);}
.btn-coral:hover{transform:translateY(-2px);}
.btn-lg{padding:13px 28px;font-size:14.5px;border-radius:13px;}
.btn:disabled{opacity:.45;cursor:not-allowed;transform:none!important;}

/* STEP NAV */
.step-nav{display:flex;align-items:center;justify-content:space-between;padding:16px 32px;gap:14px;flex-wrap:wrap;background:rgba(12,14,20,.96);backdrop-filter:blur(22px);border-top:1px solid var(--bd);position:sticky;bottom:0;z-index:100;transition:background .3s;}
.lm .step-nav{background:rgba(234,238,247,.97);}
.sn-left,.sn-right{display:flex;gap:10px;align-items:center;}

/* ALERTS */
.alert{display:flex;align-items:flex-start;gap:11px;padding:14px 16px;border-radius:12px;margin-bottom:20px;}
.alert.error{background:rgba(255,77,109,.08);border:1px solid rgba(255,77,109,.22);}
.alert.success{background:rgba(31,217,160,.07);border:1px solid rgba(31,217,160,.2);}
.al-ico{font-size:16px;flex-shrink:0;margin-top:1px;}
.al-txt{font-size:13px;}
.al-ul{padding-left:16px;margin-top:4px;}
.al-ul li{font-size:12.5px;margin-bottom:2px;}

/* MODAL */
.modal-bg{display:none;position:fixed;inset:0;z-index:2000;background:rgba(0,0,0,.75);backdrop-filter:blur(14px);align-items:center;justify-content:center;padding:20px;}
.modal-bg.open{display:flex;animation:mfIn .22s ease;}
@keyframes mfIn{from{opacity:0;}to{opacity:1;}}
.modal-box{background:var(--s2);border:1px solid var(--bd2);border-radius:22px;padding:32px;max-width:520px;width:100%;box-shadow:0 30px 80px rgba(0,0,0,.7);animation:mIn .25s cubic-bezier(.34,1.56,.64,1);}
@keyframes mIn{from{transform:scale(.92);}to{transform:scale(1);}}
.modal-head{display:flex;align-items:center;gap:13px;margin-bottom:22px;}
.modal-ico{width:46px;height:46px;border-radius:14px;background:linear-gradient(135deg,var(--cyan),var(--teal));display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;}
.modal-ttl{font-family:var(--fh);font-size:18px;font-weight:900;}
.modal-sub{font-size:12.5px;color:var(--tx-3);margin-top:2px;}
.modal-acts{display:flex;gap:10px;margin-top:26px;justify-content:flex-end;}

/* TOAST */
#toasts{position:fixed;bottom:90px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd2);padding:11px 15px;border-radius:var(--rs);max-width:320px;box-shadow:0 14px 40px rgba(0,0,0,.6);animation:tIn .3s ease;backdrop-filter:blur(18px);}
.toast.success{border-left:3px solid var(--teal);}
.toast.error{border-left:3px solid var(--red);}
.toast.info{border-left:3px solid var(--cyan);}
.toast-ico{font-size:15px;flex-shrink:0;}.toast-body{flex:1;}
.toast-ttl{font-family:var(--fh);font-weight:800;font-size:12px;margin-bottom:1px;}
.toast-msg{font-size:11px;color:var(--tx-3);}
.toast-close{color:var(--tx-3);font-size:16px;cursor:pointer;flex-shrink:0;}
@keyframes tIn{from{opacity:0;transform:translateX(48px);}to{opacity:1;transform:translateX(0);}}

@media(max-width:1024px){.sidebar{display:none;}.main-wrap{padding:24px 20px 80px;}}
@media(max-width:700px){.field-row{grid-template-columns:1fr;}.cat-grid{grid-template-columns:repeat(3,1fr);}.extras-grid{grid-template-columns:1fr 1fr;}.ps-label{display:none;}.main-wrap{padding:16px 14px 80px;}}
</style>
</head>
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<nav class="topnav">
  <a href="<?= APP_URL ?>/index.php" class="nav-logo">
    <div class="logo-mark">G</div>
    <span class="logo-txt">Gig<span>Ghana</span></span>
  </a>
  <div class="nav-links">
    <a href="<?= APP_URL ?>/client/dashboard.php" class="navlink">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/client/my-jobs.php"   class="navlink">📋 Jobs</a>
    <a href="<?= APP_URL ?>/client/messages.php"  class="navlink">💬 Messages</a>
    <a href="<?= APP_URL ?>/search/providers.php" class="navlink">🔍 Talent</a>
  </div>
  <div class="nav-r">
    <button class="theme-btn" id="themeBtn" onclick="toggleTheme()" title="Toggle theme"><?= $isLight ? '☀️' : '🌙' ?></button>
    <div class="icon-btn">🔔</div>
    <div class="uchip">
      <div class="uchip-av"><?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?></div>
      <?= sanitize($user['first_name']??'Me') ?>
    </div>
  </div>
</nav>

<div class="page">
  <aside class="sidebar">
    <div class="sb-section">Client</div>
    <a href="<?= APP_URL ?>/client/dashboard.php" class="sb-item">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/client/post-job.php"  class="sb-item active">✏️ Post a Job</a>
    <a href="<?= APP_URL ?>/client/my-jobs.php"   class="sb-item">💼 My Jobs</a>
    <a href="<?= APP_URL ?>/client/proposals.php" class="sb-item">📩 Proposals</a>
    <div class="sb-section">Communication</div>
    <a href="<?= APP_URL ?>/client/messages.php"  class="sb-item">💬 Messages</a>
    <a href="<?= APP_URL ?>/search/providers.php" class="sb-item">🔍 Find Talent</a>
    <div class="sb-section">Finance</div>
    <a href="<?= APP_URL ?>/client/payments.php"  class="sb-item">💳 Payments</a>
    <div class="sb-section">Account</div>
    <a href="<?= APP_URL ?>/client/settings.php"  class="sb-item">⚙️ Settings</a>
    <a href="<?= APP_URL ?>/auth/logout.php"       class="sb-item danger">🚪 Sign Out</a>
    <div class="sb-gap"></div>
    <div class="sb-user">
      <div class="sb-ucard">
        <div class="sb-av"><?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?></div>
        <div><div class="sb-uname"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div><div class="sb-urole">Client</div></div>
      </div>
    </div>
  </aside>

  <div class="main-wrap">
    <div class="breadcrumb">
      <a href="<?= APP_URL ?>/client/dashboard.php">Dashboard</a><span>›</span>
      <a href="<?= APP_URL ?>/client/my-jobs.php">Jobs</a><span>›</span>
      <span style="color:var(--tx-2);"><?= $editId ? 'Edit Job' : 'Post a Job' ?></span>
    </div>

    <div class="pg-head">
      <h1 class="pg-title"><?= $editId ? '✏️ Edit Job' : '✏️ Post a New Job' ?></h1>
      <p class="pg-sub"><?= $editId ? 'Update your job listing. Changes are live immediately.' : "Fill in the details below and get proposals from Ghana's top professionals." ?></p>
    </div>

    <?php if($success):?>
    <div class="alert success"><div class="al-ico">✅</div><div class="al-txt"><?= htmlspecialchars($success) ?></div></div>
    <?php endif;?>

    <?php if(!empty($errors)):?>
    <div class="alert error">
      <div class="al-ico">❌</div>
      <div class="al-txt">
        Please fix the following:
        <ul class="al-ul">
          <?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach;?>
        </ul>
      </div>
    </div>
    <?php endif;?>

    <!-- PROGRESS -->
    <div class="progress-bar" id="progressBar">
      <div class="prog-step active" data-step="1" onclick="goStep(1)"><div class="ps-circle" id="pc1">1</div><div class="ps-label">Basics</div></div>
      <div class="prog-step" data-step="2" onclick="goStep(2)"><div class="ps-circle" id="pc2">2</div><div class="ps-label">Budget</div></div>
      <div class="prog-step" data-step="3" onclick="goStep(3)"><div class="ps-circle" id="pc3">3</div><div class="ps-label">Details</div></div>
      <div class="prog-step" data-step="4" onclick="goStep(4)"><div class="ps-circle" id="pc4">4</div><div class="ps-label">Review</div></div>
    </div>

    <!-- ✅ FIX: form has explicit action, method POST, id=jobForm -->
    <form method="POST" action="<?= APP_URL ?>/client/post-job.php<?= $editId ? '?edit='.$editId : '' ?>"
          enctype="multipart/form-data" id="jobForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action"     id="formAction" value="post_job">
      <?php if($editId):?><input type="hidden" name="edit_id" value="<?= $editId ?>"><?php endif;?>

      <!-- STEP 1 -->
      <div class="step-card active" id="step1">
        <div class="step-heading"><div class="step-num">1</div><div><div class="step-ttl">Job Basics</div><div class="step-sub">What are you looking to get done?</div></div></div>

        <div class="field-group">
          <label class="field-label" for="title">Job Title <span class="field-req">*</span><span class="field-hint">10–100 characters</span></label>
          <input type="text" id="title" name="title" class="field-input"
                 placeholder="e.g. Need a skilled electrician to wire my new office"
                 minlength="10" maxlength="100" value="<?= htmlspecialchars($f['title']) ?>" required>
          <div class="char-count" id="titleCount">0 / 100</div>
          <div class="field-err" id="titleErr">Title must be 10–100 characters.</div>
        </div>

        <div class="field-group">
          <label class="field-label">Category <span class="field-req">*</span></label>
          <div class="cat-grid" id="catGrid">
            <?php foreach($cats as $cat):
              $ico = $iconMap[$cat['icon'] ?? ''] ?? '💼';
              $sel = ((int)$f['category_id'] === (int)$cat['id']) ? 'sel' : '';
            ?>
            <label class="cat-btn <?= $sel ?>" data-cat-id="<?= $cat['id'] ?>">
              <input type="radio" name="category_id" value="<?= $cat['id'] ?>" <?= $sel?'checked':'' ?> onchange="onCatChange(<?= $cat['id'] ?>)">
              <div class="cat-ico"><?= $ico ?></div>
              <div class="cat-name"><?= sanitize($cat['name']) ?></div>
            </label>
            <?php endforeach;?>
          </div>
          <div class="field-err" id="catErr">Please select a category.</div>
        </div>

        <div class="field-group">
          <label class="field-label" for="description">Description <span class="field-req">*</span><span class="field-hint">Min 50 characters</span></label>
          <textarea id="description" name="description" class="field-textarea"
                    placeholder="Describe what you need done. Include scope, deliverables, requirements…"
                    minlength="50" required><?= htmlspecialchars($f['description']) ?></textarea>
          <div class="char-count" id="descCount">0 / 3000</div>
          <div class="field-err" id="descErr">Description must be at least 50 characters.</div>
        </div>

        <div class="field-group">
          <label class="field-label" for="requirements">Additional Requirements <span class="field-hint">Optional</span></label>
          <textarea id="requirements" name="requirements" class="field-textarea" style="min-height:90px;"
                    placeholder="Tools, certifications, experience, or documents the provider must have…"><?= htmlspecialchars($f['requirements']) ?></textarea>
        </div>
      </div>

      <!-- STEP 2 -->
      <div class="step-card" id="step2">
        <div class="step-heading"><div class="step-num">2</div><div><div class="step-ttl">Budget & Timeline</div><div class="step-sub">How much and when?</div></div></div>

        <div class="field-group">
          <label class="field-label">Budget Type <span class="field-req">*</span></label>
          <div class="budget-type-row">
            <label class="budget-type-btn <?= $f['budget_type']==='fixed'?'sel':'' ?>" id="btFixed">
              <input type="radio" name="budget_type" value="fixed" <?= $f['budget_type']==='fixed'?'checked':'' ?>>
              <div class="btt-ico">💰</div><div class="btt-lbl">Fixed Price</div>
              <div class="btt-sub">Pay a flat rate</div>
            </label>
            <label class="budget-type-btn <?= $f['budget_type']==='hourly'?'sel':'' ?>" id="btHourly">
              <input type="radio" name="budget_type" value="hourly" <?= $f['budget_type']==='hourly'?'checked':'' ?>>
              <div class="btt-ico">⏱</div><div class="btt-lbl">Hourly Rate</div>
              <div class="btt-sub">Pay per hour</div>
            </label>
          </div>
        </div>

        <div class="field-row">
          <div class="field-group" style="margin-bottom:0;">
            <label class="field-label" for="budget_min">Min Budget (GHS) <span class="field-req">*</span></label>
            <input type="number" id="budget_min" name="budget_min" class="field-input"
                   placeholder="e.g. 200" min="0" step="10"
                   value="<?= htmlspecialchars($f['budget_min']) ?>" oninput="updateBudgetPreview()">
            <div class="field-err" id="budgMinErr">Enter a valid minimum budget.</div>
          </div>
          <div class="field-group" style="margin-bottom:0;">
            <label class="field-label" for="budget_max">Max Budget (GHS) <span class="field-hint">Optional</span></label>
            <input type="number" id="budget_max" name="budget_max" class="field-input"
                   placeholder="e.g. 500" min="0" step="10"
                   value="<?= htmlspecialchars($f['budget_max']) ?>" oninput="updateBudgetPreview()">
          </div>
        </div>

        <div class="budget-preview" id="budgetPreview">
          <div><div class="bp-label">Your budget range</div><div class="bp-val" id="budgetDisplay">GHS —</div></div>
          <div style="font-size:11px;color:var(--tx-3);max-width:200px;text-align:right;">GigGhana takes a <strong style="color:var(--cyan);">10% fee</strong> on completed jobs, paid by the provider.</div>
        </div>

        <div class="field-row" style="margin-top:22px;">
          <div class="field-group" style="margin-bottom:0;">
            <label class="field-label" for="duration">Project Duration <span class="field-req">*</span></label>
            <select id="duration" name="duration" class="field-select">
              <option value="less_1_week" <?= $f['duration']==='less_1_week'?'selected':''?>>Less than 1 week</option>
              <option value="1_2_weeks"  <?= $f['duration']==='1_2_weeks'  ?'selected':''?>>1–2 weeks</option>
              <option value="1_month"    <?= $f['duration']==='1_month'    ?'selected':''?>>About 1 month</option>
              <option value="3_months"   <?= $f['duration']==='3_months'   ?'selected':''?>>2–3 months</option>
              <option value="6_months"   <?= $f['duration']==='6_months'   ?'selected':''?>>3–6 months</option>
              <option value="ongoing"    <?= $f['duration']==='ongoing'    ?'selected':''?>>Ongoing / Long-term</option>
            </select>
          </div>
          <div class="field-group" style="margin-bottom:0;">
            <label class="field-label" for="deadline">Deadline Date <span class="field-hint">Optional</span></label>
            <input type="date" id="deadline" name="deadline" class="field-input"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                   value="<?= htmlspecialchars($f['deadline'] ?? '') ?>">
            <div class="field-err" id="deadlineErr">Deadline must be a future date.</div>
          </div>
        </div>

        <div class="field-group" style="margin-top:22px;">
          <label class="field-label" for="experience_level">Required Experience Level</label>
          <select id="experience_level" name="experience_level" class="field-select">
            <option value="any"          <?= $f['experience_level']==='any'          ?'selected':''?>>Any level</option>
            <option value="entry"        <?= $f['experience_level']==='entry'        ?'selected':''?>>Entry</option>
            <option value="intermediate" <?= $f['experience_level']==='intermediate' ?'selected':''?>>Intermediate</option>
            <option value="expert"       <?= $f['experience_level']==='expert'       ?'selected':''?>>Expert</option>
          </select>
        </div>
      </div>

      <!-- STEP 3 -->
      <div class="step-card" id="step3">
        <div class="step-heading"><div class="step-num">3</div><div><div class="step-ttl">Job Details</div><div class="step-sub">Location, skills, extras.</div></div></div>

        <div class="field-group">
          <label class="field-label">Work Location <span class="field-req">*</span></label>
          <div class="loc-row">
            <?php foreach(['remote'=>['🌐','Remote','Work from anywhere'],'onsite'=>['📍','On-site','Physically present'],'hybrid'=>['🔄','Hybrid','Mix of both']] as $lv=>[$li,$ll,$ls]):
              $lsel = ($f['location_type']===$lv)?'sel':''; ?>
            <label class="loc-btn <?= $lsel ?>">
              <input type="radio" name="location_type" value="<?= $lv ?>" <?= $lsel?'checked':'' ?> onchange="onLocType('<?= $lv ?>')">
              <div style="font-size:20px;margin-bottom:5px;"><?= $li ?></div>
              <div style="font-family:var(--fh);font-size:12.5px;font-weight:800;"><?= $ll ?></div>
              <div style="font-size:10.5px;color:var(--tx-3);margin-top:2px;"><?= $ls ?></div>
            </label>
            <?php endforeach;?>
          </div>
        </div>

        <div class="field-group" id="locationField" style="<?= $f['location_type']==='remote'?'display:none':'display:block' ?>">
          <label class="field-label" for="location">City / Region</label>
          <select id="location" name="location" class="field-select" onchange="document.getElementById('locationCustom').style.display=this.value==='Custom'?'block':'none'">
            <option value="">— Select region —</option>
            <?php foreach(['Accra','Kumasi','Takoradi','Tamale','Cape Coast','Koforidua','Sunyani','Ho','Bolgatanga','Wa','Tema','Ashaiman','Obuasi','Tarkwa','Techiman','Custom'] as $reg):
              $sel = ($f['location']===$reg)?'selected':''; ?>
            <option value="<?= $reg ?>" <?= $sel ?>><?= $reg ?></option>
            <?php endforeach;?>
          </select>
          <input type="text" id="locationCustom" name="location_custom" class="field-input"
                 placeholder="Enter city or town…" style="margin-top:10px;display:none;"
                 value="<?= (!in_array($f['location'],['Accra','Kumasi','Takoradi','Tamale','Cape Coast','Koforidua','Sunyani','Ho','Bolgatanga','Wa','Tema','Ashaiman','Obuasi','Tarkwa','Techiman','Custom',''])&&$f['location'])?htmlspecialchars($f['location']):'' ?>">
        </div>

        <div class="field-group">
          <label class="field-label">Required Skills <span class="field-hint">Select all that apply</span></label>
          <div class="selected-skills" id="selectedSkills">
            <span class="no-skills-msg">No skills selected yet — click tags below to add</span>
          </div>
          <div class="skills-search">
            <input type="text" class="field-input" id="skillSearch"
                   placeholder="🔍  Search skills…" oninput="filterSkills(this.value)" autocomplete="off">
          </div>
          <div class="skills-panel" id="skillsPanel">
            <?php foreach ($cats as $cat):
              $catSkills = $skillsByCat[(int)$cat['id']] ?? [];
              if (empty($catSkills)) continue;
            ?>
            <div class="skill-cat-section" data-cat="<?= (int)$cat['id'] ?>">
              <div class="skill-cat-header"><?= sanitize($cat['name']) ?></div>
              <div class="skill-tags-row">
                <?php foreach($catSkills as $sk):
                  $isSel = in_array((int)$sk['id'], (array)$f['skills']) ? 'sel' : '';
                ?>
                <div class="skill-tag <?= $isSel ?>" data-id="<?= $sk['id'] ?>"
                     data-name="<?= htmlspecialchars($sk['name']) ?>"
                     onclick="toggleSkill(this)"><?= sanitize($sk['name']) ?></div>
                <?php endforeach;?>
              </div>
            </div>
            <?php endforeach;?>
          </div>
          <div id="skillInputs"></div>
        </div>

        <div class="field-group">
          <label class="field-label">Optional Extras</label>
          <div class="extras-grid">
            <label class="extra-card <?= $f['is_urgent']?'on':'' ?>" onclick="toggleExtra(this)">
              <input type="checkbox" name="is_urgent" id="is_urgent" <?= $f['is_urgent']?'checked':'' ?>>
              <div class="extra-ico">🔥</div><div class="extra-lbl">Urgent Job</div>
              <div class="extra-sub">Highlighted in search results</div>
              <div class="toggle-dot"></div>
            </label>
            <label class="extra-card" onclick="toggleExtra(this)">
              <input type="checkbox" name="is_remote_ok" id="is_remote_ok">
              <div class="extra-ico">🌐</div><div class="extra-lbl">Remote OK</div>
              <div class="extra-sub">Provider can work remotely</div>
              <div class="toggle-dot"></div>
            </label>
            <label class="extra-card" onclick="toggleExtra(this)">
              <input type="checkbox" name="is_recurring" id="is_recurring">
              <div class="extra-ico">🔄</div><div class="extra-lbl">Recurring Job</div>
              <div class="extra-sub">This is a repeated task</div>
              <div class="toggle-dot"></div>
            </label>
          </div>
        </div>
      </div>

      <!-- STEP 4 -->
      <div class="step-card" id="step4">
        <div class="step-heading"><div class="step-num">4</div><div><div class="step-ttl">Attachments & Review</div><div class="step-sub">Review before posting.</div></div></div>

        <div class="field-group">
          <label class="field-label">Attachments <span class="field-hint">Optional · Max 10MB each</span></label>
          <div class="upload-zone" id="uploadZone">
            <input type="file" name="attachments[]" id="attachInput" multiple accept="image/*,.pdf,.doc,.docx" onchange="handleFiles(this.files)">
            <div class="uz-ico">📎</div>
            <div class="uz-ttl">Drag & drop or click to browse</div>
            <div class="uz-sub">JPG, PNG, PDF, DOC · Max 10MB</div>
          </div>
          <div class="upload-previews" id="uploadPreviews"></div>
        </div>

        <div class="vis-panel">
          <div class="vis-ico">⭐</div>
          <div class="vis-txt"><strong>Verified & Premium providers</strong> are prioritised in matching. Urgent jobs get a highlighted badge in search results.</div>
        </div>

        <div class="summary-box">
          <div class="sum-head"><div class="sum-title">📋 Job Summary — Review Before Posting</div></div>
          <div class="review-row"><div class="rr-label">Title</div><div class="rr-val" id="sum-title">—</div></div>
          <div class="review-row"><div class="rr-label">Category</div><div class="rr-val" id="sum-cat">—</div></div>
          <div class="review-row"><div class="rr-label">Budget</div><div class="rr-val" id="sum-budget">—</div></div>
          <div class="review-row"><div class="rr-label">Duration</div><div class="rr-val" id="sum-duration">—</div></div>
          <div class="review-row"><div class="rr-label">Location</div><div class="rr-val" id="sum-location">—</div></div>
          <div class="review-row"><div class="rr-label">Experience</div><div class="rr-val" id="sum-exp">—</div></div>
          <div class="review-row"><div class="rr-label">Skills</div><div class="rr-val" id="sum-skills">—</div></div>
          <div class="review-row"><div class="rr-label">Extras</div><div class="rr-val" id="sum-extras">—</div></div>
          <div class="review-row"><div class="rr-label">Deadline</div><div class="rr-val" id="sum-deadline">Not set</div></div>
        </div>
      </div>

    </form>

    <!-- STICKY NAV -->
    <div class="step-nav">
      <div class="sn-left">
        <button class="btn btn-ghost" id="btnBack" onclick="prevStep()" style="display:none;">← Back</button>
        <button class="btn btn-ghost" id="btnDraft" onclick="saveDraft()">💾 Save Draft</button>
      </div>
      <div style="font-size:12px;color:var(--tx-3);font-family:var(--fh);">Step <span id="stepIndicator">1</span> of 4</div>
      <div class="sn-right">
        <button class="btn btn-cyan btn-lg" id="btnNext" onclick="nextStep()">Continue →</button>
        <button class="btn btn-coral btn-lg" id="btnPost" style="display:none;" onclick="openConfirmModal()">🚀 Post Job Now</button>
      </div>
    </div>
  </div>
</div>

<!-- CONFIRM MODAL -->
<div class="modal-bg" id="confirmModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-ico">🚀</div>
      <div><div class="modal-ttl">Ready to Post?</div><div class="modal-sub">Your job will go live immediately.</div></div>
    </div>
    <div style="background:rgba(0,212,200,.05);border:1px solid rgba(0,212,200,.15);border-radius:12px;padding:16px 18px;margin-bottom:16px;">
      <div style="font-family:var(--fh);font-size:14px;font-weight:800;margin-bottom:4px;" id="modalJobTitle">—</div>
      <div style="font-size:12.5px;color:var(--tx-3);" id="modalJobMeta">—</div>
    </div>
    <div style="font-size:12px;color:var(--tx-3);line-height:1.7;">
      ✅ Visible to all providers immediately.<br>
      ✅ Verified providers with matching skills get notified.<br>
      ✅ Edit or close anytime from My Jobs.
    </div>
    <div class="modal-acts">
      <button type="button" class="btn btn-ghost" onclick="closeConfirmModal()">Cancel</button>
      <!-- ✅ FIX: button directly submits the form — no intermediate JS that can race -->
      <button type="button" class="btn btn-coral btn-lg" id="postSubmitBtn" onclick="submitPost()">🚀 Yes, Post It!</button>
    </div>
  </div>
</div>

<div id="toasts"></div>

<script>
const SKILLS_BY_CAT = <?php
  $out = [];
  foreach ($skillsByCat as $cid => $sks)
      $out[$cid] = array_map(fn($s) => ['id'=>(int)$s['id'],'name'=>$s['name']], $sks);
  echo json_encode($out, JSON_UNESCAPED_UNICODE);
?>;
const CATS = <?php
  $clist = [];
  foreach ($cats as $c) $clist[(int)$c['id']] = $c['name'];
  echo json_encode($clist);
?>;
const APP_URL    = '<?= APP_URL ?>';
const PRE_SKILLS = <?= json_encode(array_map('intval', (array)$f['skills'])) ?>;

let currentStep = 1;
let selectedSkills = {};

document.addEventListener('DOMContentLoaded', () => {
  PRE_SKILLS.forEach(id => {
    const el = document.querySelector(`.skill-tag[data-id="${id}"]`);
    if (el) { selectedSkills[id] = el.dataset.name; el.classList.add('sel'); }
  });
  renderSelectedSkills();
  renderSkillInputs();
  updateBudgetPreview();
  updateTitleCount();
  updateDescCount();
  updateProgress();

  const precat = <?= (int)($f['category_id'] ?? 0) ?>;
  if (precat) highlightCat(precat);

  const uz = document.getElementById('uploadZone');
  uz.addEventListener('dragover', e => { e.preventDefault(); uz.classList.add('drag'); });
  uz.addEventListener('dragleave', () => uz.classList.remove('drag'));
  uz.addEventListener('drop', e => { e.preventDefault(); uz.classList.remove('drag'); handleFiles(e.dataTransfer.files); });

  /* If form came back with errors, go to step 1 so user can see them */
  <?php if(!empty($errors) && $_SERVER['REQUEST_METHOD']==='POST'):?>
  goStep(1);
  <?php endif;?>
});

/* ══ THEME ══ */
function toggleTheme(){
  const body = document.getElementById('appBody');
  const isL  = body.classList.toggle('lm');
  const val  = isL ? 'light' : 'dark';
  localStorage.setItem('gg_theme', val);
  document.cookie = `gg_theme=${val};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = isL ? '☀️' : '🌙';
}
(function(){
  const s = localStorage.getItem('gg_theme') || '<?= $isLight?"light":"dark" ?>';
  const body = document.getElementById('appBody');
  const btn  = document.getElementById('themeBtn');
  if(s==='light'){body.classList.add('lm');if(btn)btn.textContent='☀️';}
  else{body.classList.remove('lm');if(btn)btn.textContent='🌙';}
})();

/* ══ STEPS ══ */
function goStep(n) {
  if (n > currentStep && !validateStep(currentStep)) return;
  currentStep = n;
  updateProgress();
  renderStep();
  window.scrollTo({top:0,behavior:'smooth'});
}
function nextStep() {
  if (!validateStep(currentStep)) return;
  if (currentStep < 4) {
    currentStep++;
    if (currentStep === 4) buildSummary();
    updateProgress();
    renderStep();
    window.scrollTo({top:0,behavior:'smooth'});
  }
}
function prevStep() {
  if (currentStep > 1) { currentStep--; updateProgress(); renderStep(); window.scrollTo({top:0,behavior:'smooth'}); }
}
function renderStep() {
  document.querySelectorAll('.step-card').forEach((c,i) => c.classList.toggle('active', i+1===currentStep));
  document.getElementById('stepIndicator').textContent = currentStep;
  document.getElementById('btnBack').style.display  = currentStep > 1 ? '' : 'none';
  document.getElementById('btnNext').style.display  = currentStep < 4 ? '' : 'none';
  document.getElementById('btnPost').style.display  = currentStep === 4 ? '' : 'none';
}
function updateProgress() {
  document.querySelectorAll('.prog-step').forEach((ps, i) => {
    const n = i+1;
    ps.classList.remove('active','done');
    if (n===currentStep) ps.classList.add('active');
    else if (n<currentStep) ps.classList.add('done');
    ps.querySelector('.ps-circle').textContent = n < currentStep ? '✓' : n;
  });
}

/* ══ VALIDATION ══ */
function validateStep(n) {
  let ok = true;
  if (n===1) {
    const title = document.getElementById('title').value.trim();
    const desc  = document.getElementById('description').value.trim();
    const cat   = document.querySelector('input[name="category_id"]:checked');
    if (title.length<10||title.length>100){showErr('titleErr',true);ok=false;}else showErr('titleErr',false);
    if (!cat){showErr('catErr',true);ok=false;}else showErr('catErr',false);
    if (desc.length<50){showErr('descErr',true);ok=false;}else showErr('descErr',false);
  }
  if (n===2) {
    const dl = document.getElementById('deadline').value;
    if (dl && new Date(dl) < new Date()){showErr('deadlineErr',true);ok=false;}else showErr('deadlineErr',false);
  }
  if (!ok) toast('Oops','Please complete all required fields.','error');
  return ok;
}
function showErr(id, show) { document.getElementById(id)?.classList.toggle('show', show); }

/* ══ CATEGORY ══ */
function onCatChange(catId) { highlightCat(catId); }
function highlightCat(catId) {
  document.querySelectorAll('.cat-btn').forEach(b =>
    b.classList.toggle('sel', parseInt(b.dataset.catId)===parseInt(catId)));
}

/* ══ BUDGET ══ */
function updateBudgetPreview() {
  const min  = parseFloat(document.getElementById('budget_min').value)||0;
  const max  = parseFloat(document.getElementById('budget_max').value)||0;
  const type = document.querySelector('input[name="budget_type"]:checked')?.value||'fixed';
  const sfx  = type==='hourly'?'/hr':'';
  let txt = min>0&&max>min ? `GHS ${fmt(min)} – GHS ${fmt(max)}${sfx}` : min>0 ? `GHS ${fmt(min)}${sfx}` : 'GHS —';
  document.getElementById('budgetDisplay').textContent = txt;
  document.getElementById('btFixed').classList.toggle('sel', type==='fixed');
  document.getElementById('btHourly').classList.toggle('sel', type==='hourly');
}
function fmt(n){ return Number(n).toLocaleString('en-GH',{minimumFractionDigits:0,maximumFractionDigits:2}); }
document.querySelectorAll('input[name="budget_type"]').forEach(r=>r.addEventListener('change',updateBudgetPreview));

/* ══ LOCATION ══ */
function onLocType(val) {
  document.getElementById('locationField').style.display = val==='remote'?'none':'block';
  document.querySelectorAll('.loc-btn').forEach(b =>
    b.classList.toggle('sel', b.querySelector('input')?.value===val));
}

/* ══ SKILLS ══ */
function toggleSkill(el) {
  const id=parseInt(el.dataset.id), name=el.dataset.name;
  if (selectedSkills[id]) { delete selectedSkills[id]; el.classList.remove('sel'); }
  else {
    if (Object.keys(selectedSkills).length>=15){toast('Limit','Max 15 skills.','info');return;}
    selectedSkills[id]=name; el.classList.add('sel');
  }
  renderSelectedSkills(); renderSkillInputs();
}
function renderSelectedSkills() {
  const box=document.getElementById('selectedSkills');
  box.innerHTML='';
  const ids=Object.keys(selectedSkills);
  if(!ids.length){box.innerHTML='<span class="no-skills-msg">No skills selected yet — click tags below to add</span>';return;}
  ids.forEach(id=>{
    const tag=document.createElement('div');tag.className='sel-tag';
    tag.innerHTML=`${esc(selectedSkills[id])} <span onclick="removeSkill(${id})">×</span>`;
    box.appendChild(tag);
  });
}
function removeSkill(id) {
  delete selectedSkills[id];
  document.querySelector(`.skill-tag[data-id="${id}"]`)?.classList.remove('sel');
  renderSelectedSkills(); renderSkillInputs();
}
function renderSkillInputs() {
  const box=document.getElementById('skillInputs'); box.innerHTML='';
  Object.keys(selectedSkills).forEach(id=>{
    const inp=document.createElement('input');
    inp.type='hidden';inp.name='skills[]';inp.value=id;
    box.appendChild(inp);
  });
}
function filterSkills(q) {
  q=q.toLowerCase().trim();
  document.querySelectorAll('.skill-cat-section').forEach(sec=>{
    let any=false;
    sec.querySelectorAll('.skill-tag').forEach(t=>{
      const m=!q||t.dataset.name.toLowerCase().includes(q);
      t.style.display=m?'':'none'; if(m)any=true;
    });
    sec.style.display=any?'':'none';
  });
}

/* ══ EXTRAS ══ */
function toggleExtra(card) {
  const inp=card.querySelector('input[type=checkbox]');
  setTimeout(()=>card.classList.toggle('on',inp.checked),0);
}

/* ══ CHAR COUNTS ══ */
function updateTitleCount() {
  const l=document.getElementById('title').value.length;
  const el=document.getElementById('titleCount');
  el.textContent=`${l} / 100`;
  el.className='char-count'+(l<10?'':l>90?' warn':' ok');
}
function updateDescCount() {
  const l=document.getElementById('description').value.length;
  const el=document.getElementById('descCount');
  el.textContent=`${l} / 3000`;
  el.className='char-count'+(l<50?'':' ok');
}
document.getElementById('title').addEventListener('input',updateTitleCount);
document.getElementById('description').addEventListener('input',updateDescCount);

/* ══ FILE UPLOAD ══ */
let uploadedFiles=[];
function handleFiles(files){
  Array.from(files).forEach(f=>{
    if(f.size>10*1024*1024){toast('Too large',`${f.name} exceeds 10MB.`,'error');return;}
    if(uploadedFiles.length>=5){toast('Limit','Max 5 files.','info');return;}
    uploadedFiles.push(f);
    const box=document.getElementById('uploadPreviews');
    const div=document.createElement('div');div.className='up-item';
    const rm=document.createElement('div');rm.className='up-rm';rm.textContent='×';
    rm.onclick=()=>{uploadedFiles.splice(uploadedFiles.indexOf(f),1);div.remove();};
    if(f.type.startsWith('image/')){
      const img=document.createElement('img');
      const rd=new FileReader();rd.onload=e=>img.src=e.target.result;rd.readAsDataURL(f);
      div.appendChild(img);
    } else { div.textContent=f.name.endsWith('.pdf')?'📄':'📝'; }
    div.appendChild(rm);box.appendChild(div);
  });
}

/* ══ REVIEW SUMMARY ══ */
function buildSummary() {
  const title  = document.getElementById('title').value.trim()||'—';
  const catEl  = document.querySelector('input[name="category_id"]:checked');
  const catName= catEl?CATS[catEl.value]||'—':'—';
  const bmin   = document.getElementById('budget_min').value||'0';
  const bmax   = document.getElementById('budget_max').value||'';
  const btype  = document.querySelector('input[name="budget_type"]:checked')?.value||'fixed';
  const dur    = document.getElementById('duration');
  const durLbl = dur.options[dur.selectedIndex]?.text||'—';
  const locType= document.querySelector('input[name="location_type"]:checked')?.value||'remote';
  const locVal = document.getElementById('location')?.value||'';
  const exp    = document.getElementById('experience_level');
  const expLbl = exp.options[exp.selectedIndex]?.text||'—';
  const dl     = document.getElementById('deadline').value||'';
  const urgent = document.getElementById('is_urgent')?.checked;
  const skills = Object.values(selectedSkills);

  const fmtB=()=>{const s=btype==='hourly'?'/hr':'';return bmax&&parseFloat(bmax)>parseFloat(bmin)?`GHS ${fmt(bmin)} – GHS ${fmt(bmax)}${s}`:`GHS ${fmt(bmin)}${s}`;};
  const locFull=locType==='remote'?'🌐 Remote':`📍 ${locType.charAt(0).toUpperCase()+locType.slice(1)} — ${locVal||'Not specified'}`;

  setText('sum-title',title);setText('sum-cat',catName);setText('sum-budget',fmtB());
  setText('sum-duration',durLbl);setText('sum-location',locFull);setText('sum-exp',expLbl);
  document.getElementById('sum-skills').innerHTML=skills.length
    ?skills.map(s=>`<span class="rr-pill rr-cyan">${esc(s)}</span>`).join(' ')
    :'<span style="color:var(--tx-3)">None specified</span>';
  const extras=[];
  if(urgent)extras.push('🔥 Urgent');
  if(document.getElementById('is_remote_ok')?.checked)extras.push('🌐 Remote OK');
  if(document.getElementById('is_recurring')?.checked)extras.push('🔄 Recurring');
  document.getElementById('sum-extras').innerHTML=extras.length
    ?extras.map(e=>`<span class="rr-pill rr-coral">${e}</span>`).join(' ')
    :'<span style="color:var(--tx-3)">None</span>';
  setText('sum-deadline',dl||'Not set');

  document.getElementById('modalJobTitle').textContent=title;
  document.getElementById('modalJobMeta').textContent=`${catName} · ${fmtB()} · ${durLbl}`;
}
function setText(id,v){const e=document.getElementById(id);if(e)e.textContent=v;}

/* ══ MODAL + SUBMIT — fixed ══
   submitPost() sets the action value then directly submits the form.
   No closeConfirmModal() call before submit (that was causing a race).
   The modal stays visible until the page navigates away.
══ */
function openConfirmModal()  { buildSummary(); document.getElementById('confirmModal').classList.add('open'); }
function closeConfirmModal() { document.getElementById('confirmModal').classList.remove('open'); }

function submitPost() {
  const btn = document.getElementById('postSubmitBtn');
  btn.disabled = true;
  btn.textContent = '⏳ Posting…';

  document.getElementById('formAction').value = 'post_job';
  document.getElementById('jobForm').submit();
  /* modal stays open — page will navigate on success */
}

function saveDraft() {
  document.getElementById('formAction').value = 'save_draft';
  document.getElementById('jobForm').submit();
}

/* ══ HELPERS ══ */
function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
const TI={success:'✅',error:'❌',info:'ℹ️'};
function toast(title,msg,type='info',d=4200){
  const c=document.getElementById('toasts');
  const t=document.createElement('div');t.className=`toast ${type}`;
  t.innerHTML=`<div class="toast-ico">${TI[type]}</div><div class="toast-body"><div class="toast-ttl">${title}</div><div class="toast-msg">${msg}</div></div><div class="toast-close" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(48px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),310);},d);
}
<?php if($success):?>toast('Success','<?= addslashes(htmlspecialchars($success)) ?>','success');<?php endif;?>
<?php if(!empty($errors)):?>toast('Fix errors','Scroll up to see what needs fixing.','error');<?php endif;?>
</script>
</body>
</html>