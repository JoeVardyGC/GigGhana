<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('provider');

$userId = $_SESSION['user_id'];
$jobId  = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 0);

if (!$jobId) redirect(APP_URL . '/provider/browse-jobs.php');

$errors = [];

try {
    $db = getDB();

    // Get provider
    $stProv = $db->prepare("SELECT * FROM providers WHERE user_id=? LIMIT 1");
    $stProv->execute([$userId]);
    $provider = $stProv->fetch();
    if (!$provider) redirect(APP_URL . '/provider/dashboard.php');
    $providerId = $provider['id'];

    // Get job
    $stJob = $db->prepare(
        "SELECT j.*, c.name AS cat_name, u.first_name, u.last_name, u.avatar, u.location
         FROM jobs j LEFT JOIN categories c ON c.id=j.category_id JOIN users u ON u.id=j.client_id
         WHERE j.id=? AND j.status='open' LIMIT 1"
    );
    $stJob->execute([$jobId]);
    $job = $stJob->fetch();

    if (!$job) redirect(APP_URL . '/provider/browse-jobs.php?error=Job+not+found+or+closed');

    // Check already applied
    $stCheck = $db->prepare("SELECT id FROM proposals WHERE job_id=? AND provider_id=? LIMIT 1");
    $stCheck->execute([$jobId, $providerId]);
    $alreadyApplied = $stCheck->fetch();

} catch(Exception $e) {
    error_log($e->getMessage());
    redirect(APP_URL . '/provider/browse-jobs.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid token.'; }
    elseif ($alreadyApplied)                     { $errors[] = 'You have already applied for this job.'; }
    else {
        $cover    = sanitize($_POST['cover_letter'] ?? '');
        $bid      = (float)($_POST['bid_amount']    ?? 0);
        $days     = (int)($_POST['delivery_days']   ?? 0);
        $portUrls = sanitize($_POST['portfolio_urls'] ?? '');

        if (strlen($cover) < 100) $errors[] = 'Cover letter must be at least 100 characters.';
        if ($bid <= 0)             $errors[] = 'Please enter a valid bid amount.';
        if ($days <= 0)            $errors[] = 'Please enter delivery days.';

        if (empty($errors)) {
            try {
                $uuid = generateUUID();
                $db->prepare(
                    "INSERT INTO proposals (uuid,job_id,provider_id,cover_letter,bid_amount,delivery_days,portfolio_urls)
                     VALUES (?,?,?,?,?,?,?)"
                )->execute([$uuid, $jobId, $providerId, $cover, $bid, $days, $portUrls]);

                // Update proposal count
                $db->prepare("UPDATE jobs SET proposal_count=proposal_count+1 WHERE id=?")->execute([$jobId]);

                // Notify client
                createNotification($job['client_id'] ?? 0, 'new_proposal',
                    'New Proposal Received',
                    "You received a new proposal for: {$job['title']}",
                    ['job_id' => $jobId, 'provider_id' => $providerId]
                );

                redirect(APP_URL . '/provider/dashboard.php?success=Proposal+submitted+successfully');
            } catch(Exception $e) {
                error_log($e->getMessage());
                $errors[] = 'Failed to submit proposal. Please try again.';
            }
        }
    }
}

$csrf = generateCSRF();
$user = getUserById($userId);
/* Theme from cookie */
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Submit Proposal — GigGhana</title>
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
  --cyan:#00D4C8;   --cyan-d:#00A89F;
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
}
.lm .sidebar{background:var(--s1);border-right-color:var(--bd);}
.lm .topbar{background:rgba(243,245,250,0.96);}
.lm .form-card,.lm .job-summary,.lm .tips-box,.lm .milestone-row,.lm .file-dropzone,.lm .bid-guide{background:rgba(255,255,255,0.9);}
.lm .form-input,.lm .form-textarea,.lm .form-select{background:rgba(0,0,0,0.05);color:var(--tx);}
.lm .sb-item{color:var(--tx-3);}
.lm .sb-item:hover{background:rgba(0,0,0,0.05);color:var(--tx);}
.lm .sb-item.active{background:var(--cyan-dim);color:var(--cyan);}
.lm .btn-ghost{background:rgba(0,0,0,0.05);border-color:var(--bd2);color:var(--tx-2);}

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
h1,h2,h3,.page-title,.form-section-title,.js-title{font-family:var(--fm);-webkit-font-smoothing:antialiased;}

/* ══ SIDEBAR ══ */
.sidebar{
  width:var(--sb);min-height:100vh;background:var(--s1);
  border-right:1px solid var(--bd);position:fixed;top:0;left:0;z-index:200;
  display:flex;flex-direction:column;overflow:hidden;transition:background .3s;
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

/* ══ MAIN ══ */
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-width:0;}

/* ══ TOPBAR ══ */
.topbar{
  display:flex;align-items:center;justify-content:space-between;
  padding:0 32px;height:64px;
  background:rgba(12,14,20,0.92);backdrop-filter:blur(24px);
  border-bottom:1px solid var(--bd);position:sticky;top:0;z-index:100;
  transition:background .3s;
}
.page-title{font-size:20px;font-weight:800;}
.topbar-right{display:flex;align-items:center;gap:8px;}
.theme-btn{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.04);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;transition:var(--e);}
.theme-btn:hover{background:rgba(255,255,255,0.08);}

/* ══ BUTTONS ══ */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:var(--rs);font-family:var(--fb);font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:var(--e);white-space:nowrap;line-height:1.3;}
.btn-ghost{background:rgba(255,255,255,0.04);border:1px solid var(--bd);color:var(--tx-2);}
.btn-ghost:hover{background:rgba(255,255,255,0.08);color:var(--tx);border-color:var(--bd2);}
.btn-cyan{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;font-weight:700;box-shadow:0 3px 14px var(--gC);}
.btn-cyan:hover{transform:translateY(-2px);box-shadow:0 8px 24px var(--gC);}
.btn-coral{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:700;}
.btn-coral:hover{transform:translateY(-2px);}
.btn-violet{background:linear-gradient(135deg,var(--violet),var(--violet-d));color:#fff;font-weight:700;}
.btn-violet:hover{transform:translateY(-2px);}
.btn-sm{padding:5px 12px;font-size:11.5px;border-radius:8px;}
.btn-lg{padding:13px 28px;font-size:15px;border-radius:12px;font-family:var(--fm);font-weight:800;}

/* ══ CONTENT ══ */
.content{padding:28px 32px 80px;max-width:1060px;}

/* ══ LAYOUT ══ */
.proposal-grid{display:grid;grid-template-columns:1fr 360px;gap:26px;align-items:start;}

/* ══ FORM CARD ══ */
.form-card{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:30px;margin-bottom:0;
  transition:background .3s,border-color .3s;
}
.form-section-title{
  font-size:17px;font-weight:700;margin-bottom:22px;
  padding-bottom:14px;border-bottom:1px solid var(--bd);
  display:flex;align-items:center;gap:8px;
}
.form-group{margin-bottom:20px;}
.form-group:last-of-type{margin-bottom:0;}
.form-label{
  display:block;font-size:10.5px;font-weight:800;color:var(--tx-3);
  margin-bottom:6px;text-transform:uppercase;letter-spacing:.6px;
}
.req{color:var(--red);}
.form-input,.form-textarea,.form-select{
  width:100%;background:rgba(0,0,0,0.22);border:1.5px solid var(--bd);
  border-radius:var(--rs);padding:12px 15px;color:var(--tx);
  font-family:var(--fb);font-size:14px;outline:none;
  transition:border-color .3s,box-shadow .3s;
}
.form-input:focus,.form-textarea:focus,.form-select:focus{
  border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-dim);
}
.form-input::placeholder,.form-textarea::placeholder{color:var(--tx-3);}
.form-select option{background:var(--s2);color:var(--tx);}
.form-textarea{resize:vertical;min-height:200px;line-height:1.75;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.form-hint{font-size:11.5px;color:var(--tx-3);margin-top:5px;line-height:1.5;}
.char-count{font-size:11.5px;text-align:right;color:var(--tx-3);margin-top:4px;transition:color .2s;}
.char-count.warn{color:var(--amber);}
.char-count.ok{color:var(--green);}

/* ══ BID GUIDE ══ */
.bid-guide{
  background:var(--violet-dim);border:1px solid var(--violet-border);
  border-radius:var(--rs);padding:13px 15px;margin-top:9px;
  transition:background .3s;
}
.bid-guide-row{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;margin-bottom:5px;}
.bid-guide-row:last-child{margin-bottom:0;padding-top:5px;border-top:1px solid var(--violet-border);}

/* ══ TIPS BOX ══ */
.tips-box{
  background:var(--cyan-dim);border:1px solid var(--cyan-border);
  border-radius:14px;padding:18px 20px;margin-bottom:22px;
  transition:background .3s;
}
.tips-title{font-family:var(--fm);font-weight:700;font-size:14px;color:var(--cyan);margin-bottom:10px;display:flex;align-items:center;gap:7px;}
.tips-list{list-style:none;display:flex;flex-direction:column;gap:7px;}
.tips-list li{font-size:12.5px;color:var(--tx-2);padding-left:18px;position:relative;line-height:1.55;}
.tips-list li::before{content:'→';position:absolute;left:0;color:var(--cyan);font-weight:700;}

/* ══ MILESTONES ══ */
.milestones-wrap{margin-top:6px;}
.milestone-row{
  display:grid;grid-template-columns:1fr 120px 100px 36px;gap:8px;
  align-items:center;margin-bottom:8px;
  background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  border-radius:var(--rs);padding:10px 12px;transition:border-color .3s;
}
.milestone-row:hover{border-color:var(--bd2);}
.milestone-row input{
  background:transparent;border:none;outline:none;color:var(--tx);
  font-family:var(--fb);font-size:13px;width:100%;
}
.milestone-row input::placeholder{color:var(--tx-3);}
.ms-num{
  width:22px;height:22px;border-radius:6px;background:var(--violet-dim);
  border:1px solid var(--violet-border);color:var(--violet);
  font-family:var(--fm);font-weight:800;font-size:11px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.ms-del{
  width:32px;height:32px;border-radius:8px;border:1px solid var(--bd);
  background:rgba(255,77,106,0.06);color:var(--red);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:var(--e);font-size:14px;flex-shrink:0;
}
.ms-del:hover{background:rgba(255,77,106,0.15);}
.add-milestone-btn{
  display:inline-flex;align-items:center;gap:6px;
  margin-top:6px;padding:7px 14px;border-radius:9px;
  background:rgba(0,0,0,0.15);border:1.5px dashed var(--bd2);
  color:var(--tx-3);font-size:12.5px;font-weight:600;
  cursor:pointer;transition:var(--e);
}
.add-milestone-btn:hover{border-color:var(--cyan-border);color:var(--cyan);}
.milestone-total{
  display:flex;align-items:center;justify-content:space-between;
  font-size:12.5px;padding:8px 12px;
  background:rgba(0,0,0,0.12);border-radius:9px;margin-top:4px;
  color:var(--tx-2);
}
.milestone-total span{font-family:var(--fm);font-weight:800;color:var(--cyan);}

/* ══ FILE DROPZONE ══ */
.file-dropzone{
  border:2px dashed var(--bd2);border-radius:var(--rs);
  padding:24px 20px;text-align:center;cursor:pointer;
  transition:var(--e);background:rgba(0,0,0,0.1);
  margin-bottom:10px;
}
.file-dropzone:hover,.file-dropzone.dragover{
  border-color:var(--cyan-border);background:var(--cyan-dim);
}
.dz-icon{font-size:30px;margin-bottom:8px;}
.dz-title{font-family:var(--fm);font-size:14px;font-weight:700;margin-bottom:4px;}
.dz-sub{font-size:12px;color:var(--tx-3);}
.file-list{display:flex;flex-direction:column;gap:6px;margin-top:10px;}
.file-item{
  display:flex;align-items:center;gap:9px;
  padding:9px 12px;border-radius:9px;
  background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  font-size:12.5px;transition:var(--e);
}
.file-item:hover{border-color:var(--bd2);}
.file-icon{font-size:18px;flex-shrink:0;}
.file-name{flex:1;color:var(--tx-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.file-size{font-size:11px;color:var(--tx-3);flex-shrink:0;}
.file-del{cursor:pointer;color:var(--red);font-size:14px;flex-shrink:0;opacity:.7;transition:opacity .2s;}
.file-del:hover{opacity:1;}

/* ══ ALERTS ══ */
.alert{padding:13px 17px;border-radius:var(--rs);margin-bottom:18px;font-size:13.5px;display:flex;align-items:flex-start;gap:9px;}
.alert-error  {background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.22);color:#FF9EB0;}
.alert-warning{background:rgba(247,183,49,0.08);border:1px solid rgba(247,183,49,0.2);color:var(--amber);}
.alert-success{background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);}

/* ══ JOB SUMMARY (right column) ══ */
.job-summary{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:26px;position:sticky;top:80px;
  transition:background .3s,border-color .3s;
}
.job-summary:hover{border-color:var(--bd2);}
.js-badge{
  display:inline-flex;align-items:center;gap:6px;
  background:var(--coral-dim);border:1px solid var(--coral-border);
  color:var(--coral);padding:4px 11px;border-radius:7px;
  font-size:10.5px;font-weight:700;font-family:var(--fm);
  margin-bottom:12px;
}
.js-title{font-size:17px;font-weight:800;margin-bottom:10px;line-height:1.3;color:var(--tx);}
.js-client{display:flex;align-items:center;gap:10px;margin-bottom:16px;}
.js-ava{
  width:38px;height:38px;border-radius:50%;
  background:linear-gradient(135deg,var(--violet),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-weight:800;font-size:14px;color:#fff;
  overflow:hidden;flex-shrink:0;
}
.js-ava img{width:100%;height:100%;object-fit:cover;}
.js-client-name{font-family:var(--fm);font-weight:700;font-size:13.5px;}
.js-client-loc{font-size:11.5px;color:var(--tx-3);margin-top:2px;}
.js-divider{height:1px;background:var(--bd);margin:16px 0;}
.js-meta{display:flex;flex-direction:column;gap:9px;}
.js-meta-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;}
.js-meta-label{color:var(--tx-3);}
.js-meta-val{font-weight:600;font-family:var(--fm);font-size:13px;}
.js-budget{color:var(--cyan);font-family:var(--fm);font-weight:900;font-size:20px;}
.js-desc{
  font-size:12.5px;color:var(--tx-2);line-height:1.7;margin-top:12px;
  display:-webkit-box;-webkit-line-clamp:6;-webkit-box-orient:vertical;overflow:hidden;
}

/* Proposal tips sidebar card */
.tips-sidebar{
  background:var(--glass);backdrop-filter:blur(14px);
  border:1px solid var(--bd);border-radius:var(--r);
  padding:20px;margin-top:16px;
}
.ts-title{font-family:var(--fm);font-weight:700;font-size:14px;margin-bottom:14px;color:var(--tx);}
.ts-item{display:flex;align-items:flex-start;gap:10px;margin-bottom:12px;}
.ts-item:last-child{margin-bottom:0;}
.ts-ico{
  width:30px;height:30px;border-radius:9px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:14px;
}
.ts-text{font-size:12px;color:var(--tx-2);line-height:1.6;}
.ts-bold{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:2px;color:var(--tx);}

/* ══ PREVIEW MODAL ══ */
.modal-overlay{
  display:none;position:fixed;inset:0;z-index:2000;
  background:rgba(0,0,0,0.88);backdrop-filter:blur(18px);
  align-items:center;justify-content:center;padding:20px;
}
.modal-overlay.open{display:flex;}
.modal-box{
  background:var(--s1);border:1px solid var(--bd2);border-radius:20px;
  width:100%;max-width:680px;max-height:90vh;overflow-y:auto;
  animation:mIn .25s ease;
}
@keyframes mIn{from{opacity:0;transform:scale(.94);}to{opacity:1;transform:scale(1);}}
.modal-head{
  display:flex;align-items:center;justify-content:space-between;
  padding:20px 24px;border-bottom:1px solid var(--bd);
  position:sticky;top:0;background:var(--s1);z-index:2;
}
.modal-title{font-family:var(--fm);font-size:17px;font-weight:800;}
.modal-close{
  width:32px;height:32px;border-radius:9px;border:1px solid var(--bd);
  background:rgba(255,255,255,0.04);display:flex;align-items:center;
  justify-content:center;cursor:pointer;font-size:16px;color:var(--tx-3);
  transition:var(--e);
}
.modal-close:hover{background:rgba(255,77,106,0.12);color:var(--red);}
.modal-body{padding:24px;}
.preview-section{margin-bottom:22px;}
.preview-label{
  font-size:10px;font-weight:800;color:var(--tx-3);
  text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;
}
.preview-val{font-size:14px;color:var(--tx);line-height:1.75;}
.preview-budget{font-family:var(--fm);font-size:24px;font-weight:900;color:var(--cyan);}
.preview-cover{
  background:rgba(0,0,0,0.15);border:1px solid var(--bd);
  border-radius:12px;padding:16px;font-size:13.5px;
  color:var(--tx-2);line-height:1.8;white-space:pre-wrap;
}
.preview-ms-row{
  display:flex;align-items:center;gap:10px;padding:9px 12px;
  border-radius:9px;background:rgba(0,0,0,0.12);
  border:1px solid var(--bd);margin-bottom:6px;font-size:13px;
}
.pm-num{
  width:22px;height:22px;border-radius:6px;background:var(--violet-dim);
  border:1px solid var(--violet-border);color:var(--violet);
  font-family:var(--fm);font-weight:800;font-size:11px;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.pm-title{flex:1;font-weight:600;}
.pm-amount{color:var(--cyan);font-weight:700;font-family:var(--fm);}
.pm-days{font-size:11.5px;color:var(--tx-3);}
.modal-footer{padding:16px 24px;border-top:1px solid var(--bd);display:flex;gap:10px;flex-wrap:wrap;}

/* ══ TOAST ══ */
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);max-width:320px;min-width:240px;box-shadow:0 12px 36px rgba(0,0,0,.5);animation:toastIn .35s ease;backdrop-filter:blur(14px);}
.toast.success{border-left:3px solid var(--green);}
.toast.error  {border-left:3px solid var(--red);}
.toast.warning{border-left:3px solid var(--amber);}
.t-ico{font-size:17px;flex-shrink:0;}.t-bod{flex:1;}
.t-ttl{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}
.t-msg{font-size:11.5px;color:var(--tx-3);}
.t-cls{cursor:pointer;color:var(--tx-3);font-size:17px;flex-shrink:0;}
@keyframes toastIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}

/* ══ RESPONSIVE ══ */
@media(max-width:960px){.proposal-grid{grid-template-columns:1fr;}.job-summary{position:static;}}
@media(max-width:768px){
  .sidebar{display:none;}.main{margin-left:0;}
  .content{padding:18px 14px 60px;}
  .topbar{padding:0 16px;}
  .form-row{grid-template-columns:1fr;}
  .milestone-row{grid-template-columns:1fr 100px 36px;}
}
</style>
</head>
<body class="<?= $isLight?'lm':'' ?>" id="appBody">

<!-- ══════ SIDEBAR ══════ -->
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
    <div class="nav-section">Account</div>
    <a href="<?= APP_URL ?>/index.php"                class="sb-item">🏠 Homepage</a>
    <a href="<?= APP_URL ?>/auth/logout.php"          class="sb-item danger">🚪 Sign Out</a>
  </nav>
</aside>

<!-- ══════ MAIN ══════ -->
<div class="main">
  <header class="topbar">
    <div class="page-title">Submit Proposal</div>
    <div class="topbar-right">
      <button class="theme-btn" id="themeBtn" onclick="toggleTheme()"><?= $isLight?'☀️':'🌙' ?></button>
      <a href="<?= APP_URL ?>/job-details.php?id=<?= $jobId ?>" class="btn btn-ghost">← View Full Job</a>
    </div>
  </header>

  <div class="content">

    <?php if($alreadyApplied): ?>
    <div class="alert alert-warning">⚠️ You have already submitted a proposal for this job. You can only submit one proposal per job.</div>
    <?php endif; ?>

    <?php if(!empty($errors)): ?>
    <div class="alert alert-error">❌ <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
    <?php endif; ?>

    <div class="proposal-grid">

      <!-- ══ LEFT: FORM COLUMN ══ -->
      <div>

        <!-- Tips banner -->
        <div class="tips-box">
          <div class="tips-title">💡 Tips for a Winning Proposal</div>
          <ul class="tips-list">
            <li>Address the client's specific needs — show you read the job carefully.</li>
            <li>Highlight relevant past work or similar projects you've completed.</li>
            <li>Be realistic with your bid — quality wins over the lowest price.</li>
            <li>Keep your tone professional but warm and confident.</li>
            <li>Add portfolio links or attachments to stand out from other applicants.</li>
          </ul>
        </div>

        <div class="form-card">
          <div class="form-section-title">📝 Your Proposal</div>

          <?php if(!$alreadyApplied): ?>
          <form method="POST" id="proposalForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="job_id"     value="<?= $jobId ?>">

            <!-- ── COVER LETTER ── -->
            <div class="form-group">
              <label class="form-label">Cover Letter <span class="req">*</span></label>
              <textarea name="cover_letter" class="form-textarea" id="coverInput"
                        rows="10" maxlength="3000"
                        placeholder="Dear <?= sanitize($job['first_name']) ?>,

I'm excited to apply for this project. Here's why I'm the perfect fit...

[Describe your relevant experience]
[Explain your approach to this project]
[Mention 1–2 similar projects you've completed]
[Express your enthusiasm for the work]

I look forward to discussing this opportunity with you.

Best regards,
<?= sanitize($user['first_name']) ?>"><?= htmlspecialchars($_POST['cover_letter'] ?? '') ?></textarea>
              <div class="char-count" id="charCountDisplay"><span id="coverCount">0</span>/3000 · min 100</div>
            </div>

            <!-- ── BID + DELIVERY ── -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Your Bid (GHS) <span class="req">*</span></label>
                <input type="number" name="bid_amount" class="form-input" id="bidInput"
                       min="1" step="10" placeholder="e.g. 1500"
                       value="<?= htmlspecialchars($_POST['bid_amount'] ?? '') ?>">
                <div class="bid-guide">
                  <div class="bid-guide-row">
                    <span style="color:var(--tx-3);">Client's budget</span>
                    <span style="color:var(--cyan);font-weight:700;font-family:var(--fm);"><?= formatCurrency($job['budget_min']) ?><?= $job['budget_max']>$job['budget_min']?' – '.formatCurrency($job['budget_max']):'' ?></span>
                  </div>
                  <div class="bid-guide-row">
                    <span style="color:var(--tx-3);">Platform fee (10%)</span>
                    <span id="feeDisplay" style="color:var(--amber);font-weight:600;">₵0.00</span>
                  </div>
                  <div class="bid-guide-row">
                    <span style="color:var(--tx-3);">You'll receive</span>
                    <span id="netDisplay" style="color:var(--green);font-weight:800;font-family:var(--fm);">₵0.00</span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Delivery Time (days) <span class="req">*</span></label>
                <input type="number" name="delivery_days" class="form-input" id="daysInput"
                       min="1" max="365" placeholder="e.g. 7"
                       value="<?= htmlspecialchars($_POST['delivery_days'] ?? '') ?>">
                <div class="form-hint">Client's expected duration: <strong><?= ucfirst(str_replace('_',' ',$job['duration'])) ?></strong></div>
              </div>
            </div>

            <!-- ── MILESTONES ── -->
            <div class="form-group">
              <label class="form-label">Milestones <span style="font-size:10px;text-transform:none;letter-spacing:0;color:var(--tx-3);">(optional — break project into phases)</span></label>
              <div class="milestones-wrap" id="milestonesWrap">
                <!-- rows injected by JS -->
              </div>
              <button type="button" class="add-milestone-btn" onclick="addMilestone()">＋ Add Milestone</button>
              <div class="milestone-total" id="msTotal" style="display:none;">
                Milestone total: <span id="msTotalVal">₵0.00</span>
              </div>
              <div class="form-hint">Milestones help clients understand your working process and build trust.</div>
            </div>

            <!-- ── PORTFOLIO URLS ── -->
            <div class="form-group">
              <label class="form-label">Portfolio / Sample Links (optional)</label>
              <input type="text" name="portfolio_urls" class="form-input"
                     placeholder="https://behance.net/yourwork, https://github.com/yourproject"
                     value="<?= htmlspecialchars($_POST['portfolio_urls'] ?? '') ?>">
              <div class="form-hint">Add links to relevant past work to boost your chances.</div>
            </div>

            <!-- ── FILE ATTACHMENTS ── -->
            <div class="form-group">
              <label class="form-label">Attachments <span style="font-size:10px;text-transform:none;letter-spacing:0;color:var(--tx-3);">(optional — max 5 files, 5MB each)</span></label>
              <div class="file-dropzone" id="fileDropzone"
                   onclick="document.getElementById('fileInput').click()"
                   ondragover="event.preventDefault();this.classList.add('dragover')"
                   ondragleave="this.classList.remove('dragover')"
                   ondrop="handleFileDrop(event)">
                <div class="dz-icon">📎</div>
                <div class="dz-title">Drop files here or click to upload</div>
                <div class="dz-sub">PDF, DOC, PNG, JPG, ZIP · Max 5MB each</div>
              </div>
              <input type="file" id="fileInput" name="attachments[]" multiple
                     accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.gif,.zip,.rar"
                     style="display:none;" onchange="handleFileSelect(this)">
              <div class="file-list" id="fileList"></div>
            </div>

            <!-- ── ACTIONS ── -->
            <div style="display:flex;gap:10px;margin-top:24px;flex-wrap:wrap;">
              <button type="button" class="btn btn-ghost" style="flex:1;justify-content:center;" onclick="previewProposal()">
                👁 Preview Proposal
              </button>
              <button type="submit" class="btn btn-cyan btn-lg" style="flex:2;justify-content:center;" id="submitBtn">
                🚀 Submit Proposal
              </button>
            </div>
          </form>

          <?php else: ?>
          <!-- Already applied state -->
          <div style="text-align:center;padding:40px 20px;">
            <div style="font-size:52px;margin-bottom:14px;">✅</div>
            <div style="font-family:var(--fm);font-size:20px;font-weight:800;margin-bottom:8px;">Proposal Already Sent</div>
            <p style="color:var(--tx-3);font-size:14px;margin-bottom:22px;line-height:1.7;">You submitted a proposal for this job. The client will review it and get back to you.</p>
            <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="btn btn-cyan btn-lg">Browse More Jobs →</a>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ══ RIGHT: JOB SUMMARY + TIPS SIDEBAR ══ -->
      <div>
        <!-- Job Summary Card -->
        <div class="job-summary">
          <?php if($job['is_urgent']): ?><div class="js-badge">🔥 Urgent</div><?php endif; ?>
          <div class="js-title"><?= sanitize($job['title']) ?></div>

          <div class="js-client">
            <div class="js-ava">
              <?php if(!empty($job['avatar'])): ?><img src="<?= sanitize($job['avatar']) ?>" alt="" loading="lazy"><?php else: echo strtoupper(substr($job['first_name'],0,1)); endif; ?>
            </div>
            <div>
              <div class="js-client-name"><?= sanitize($job['first_name'].' '.$job['last_name']) ?></div>
              <div class="js-client-loc">📍 <?= sanitize($job['location'] ?: 'Ghana') ?></div>
            </div>
          </div>

          <div class="js-divider"></div>

          <div class="js-meta">
            <div class="js-meta-row">
              <span class="js-meta-label">Budget</span>
              <span class="js-budget"><?= formatCurrency($job['budget_min']) ?><?= $job['budget_max']>$job['budget_min']?' – '.formatCurrency($job['budget_max']):'' ?><?= $job['budget_type']==='hourly'?'/hr':'' ?></span>
            </div>
            <div class="js-meta-row">
              <span class="js-meta-label">Duration</span>
              <span class="js-meta-val"><?= ucfirst(str_replace('_',' ',$job['duration'])) ?></span>
            </div>
            <div class="js-meta-row">
              <span class="js-meta-label">Experience</span>
              <span class="js-meta-val"><?= ucfirst($job['experience_level']) ?></span>
            </div>
            <div class="js-meta-row">
              <span class="js-meta-label">Work type</span>
              <span class="js-meta-val"><?= ucfirst(str_replace('_',' ',$job['location_type'])) ?></span>
            </div>
            <div class="js-meta-row">
              <span class="js-meta-label">Proposals</span>
              <span class="js-meta-val"><?= $job['proposal_count'] ?> received</span>
            </div>
            <div class="js-meta-row">
              <span class="js-meta-label">Category</span>
              <span class="js-meta-val"><?= sanitize($job['cat_name'] ?? 'General') ?></span>
            </div>
          </div>

          <div class="js-divider"></div>
          <div class="js-desc"><?= sanitize($job['description']) ?></div>

          <?php if(!empty($job['requirements'])): ?>
          <div class="js-divider"></div>
          <div style="font-size:11px;font-weight:800;color:var(--tx-3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:7px;">Requirements</div>
          <div style="font-size:12.5px;color:var(--tx-2);line-height:1.65;"><?= sanitize($job['requirements']) ?></div>
          <?php endif; ?>

          <div style="margin-top:18px;">
            <a href="<?= APP_URL ?>/job-details.php?id=<?= $jobId ?>" class="btn btn-ghost" style="width:100%;justify-content:center;">↗ View Full Details</a>
          </div>
        </div>

        <!-- Proposal Tips Sidebar -->
        <div class="tips-sidebar">
          <div class="ts-title">✨ Writing a Great Proposal</div>

          <?php $sidebarTips = [
            ['🎯','Be specific','Mention the client by name and reference exact details from the job posting.','var(--coral-dim)'],
            ['💼','Show your work','Add 2–3 relevant examples or links to similar completed projects.','var(--cyan-dim)'],
            ['₵','Price wisely','Bid based on the value you deliver, not just to undercut others.','rgba(247,183,49,0.1)'],
            ['⏱','Be realistic','Give an honest delivery time — missed deadlines hurt your rating.','var(--violet-dim)'],
            ['💬','End with a CTA','Invite the client to chat or ask a clarifying question to spark dialogue.','var(--green-dim)'],
          ]; ?>
          <?php foreach($sidebarTips as [$ico,$bold,$text,$bg]): ?>
          <div class="ts-item">
            <div class="ts-ico" style="background:<?= $bg ?>;"><?= $ico ?></div>
            <div>
              <div class="ts-bold"><?= $bold ?></div>
              <div class="ts-text"><?= $text ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div><!-- /proposal-grid -->
  </div><!-- /content -->
</div><!-- /main -->

<!-- ══ PREVIEW MODAL ══ -->
<div class="modal-overlay" id="previewModal" onclick="if(event.target===this)closePreview()">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title">👁 Proposal Preview</div>
      <button class="modal-close" onclick="closePreview()">✕</button>
    </div>
    <div class="modal-body" id="previewBody">
      <!-- populated by JS -->
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" style="flex:1;justify-content:center;" onclick="closePreview()">← Edit</button>
      <button class="btn btn-cyan btn-lg" style="flex:2;justify-content:center;" onclick="closePreview();document.getElementById('proposalForm').requestSubmit()">🚀 Submit Now</button>
    </div>
  </div>
</div>

<div id="toast-c"></div>

<script>
/* ══ THEME ══ */
function toggleTheme(){
  const l=document.getElementById('appBody').classList.toggle('lm');
  const v=l?'light':'dark';
  localStorage.setItem('gg_theme',v);
  document.cookie=`gg_theme=${v};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent=l?'☀️':'🌙';
}
(function(){
  const s=localStorage.getItem('gg_theme')||'<?= $isLight?"light":"dark" ?>';
  const b=document.getElementById('appBody'),btn=document.getElementById('themeBtn');
  if(s==='light'){b.classList.add('lm');if(btn)btn.textContent='☀️';}
  else{b.classList.remove('lm');if(btn)btn.textContent='🌙';}
})();

/* ══ CHAR COUNTER ══ */
const coverInput = document.getElementById('coverInput');
const coverCount = document.getElementById('coverCount');
const charDisp   = document.getElementById('charCountDisplay');
function updateCount(){
  const len = coverInput.value.length;
  coverCount.textContent = len;
  charDisp.className = 'char-count ' + (len >= 100 ? 'ok' : (len > 80 ? 'warn' : ''));
}
if(coverInput){ coverInput.addEventListener('input', updateCount); updateCount(); }

/* ══ BID CALCULATOR ══ */
const bidInput = document.getElementById('bidInput');
if(bidInput){
  bidInput.addEventListener('input', function(){
    const bid = parseFloat(this.value) || 0;
    const fee = bid * 0.10;
    document.getElementById('feeDisplay').textContent = '₵' + fee.toFixed(2);
    document.getElementById('netDisplay').textContent = '₵' + (bid - fee).toFixed(2);
    updateMilestoneTotal(); // keep in sync if milestones exist
  });
}

/* ══ MILESTONES ══ */
let milestoneCount = 0;
function addMilestone(){
  milestoneCount++;
  const n = milestoneCount;
  const wrap = document.getElementById('milestonesWrap');
  const row = document.createElement('div');
  row.className = 'milestone-row';
  row.id = `ms_row_${n}`;
  row.innerHTML = `
    <div class="ms-num">${n}</div>
    <input type="text"   name="ms_title[]"  placeholder="e.g. Design mockup"     onchange="updateMilestoneTotal()">
    <input type="number" name="ms_amount[]" placeholder="₵ amount" min="0" step="10"
           style="text-align:right;" oninput="updateMilestoneTotal()">
    <input type="number" name="ms_days[]"   placeholder="days"     min="1"
           style="text-align:center;">
    <div class="ms-del" onclick="removeMilestone(${n})">✕</div>
  `;
  wrap.appendChild(row);
  updateMilestoneTotal();
  // show/hide grid label
  if(milestoneCount === 1){
    wrap.insertAdjacentHTML('afterbegin',
      `<div id="ms_header" style="display:grid;grid-template-columns:22px 1fr 120px 100px 36px;gap:8px;padding:0 12px 4px;font-size:10px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:.5px;">
        <span></span><span>Title</span><span>Amount (₵)</span><span>Days</span><span></span>
      </div>`
    );
  }
}
function removeMilestone(n){
  document.getElementById(`ms_row_${n}`)?.remove();
  if(!document.querySelector('.milestone-row')){ document.getElementById('ms_header')?.remove(); }
  updateMilestoneTotal();
}
function updateMilestoneTotal(){
  const amts = [...document.querySelectorAll('[name="ms_amount[]"]')].map(i=>parseFloat(i.value)||0);
  const total = amts.reduce((a,b)=>a+b,0);
  const wrap = document.getElementById('msTotal');
  if(amts.length > 0){ wrap.style.display='flex'; document.getElementById('msTotalVal').textContent='₵'+total.toFixed(2); }
  else{ wrap.style.display='none'; }
}

/* ══ FILE HANDLING ══ */
const uploadedFiles = [];
const ALLOWED_TYPES = ['application/pdf','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document','image/png','image/jpeg','image/gif','application/zip','application/x-rar-compressed'];
const FILE_ICONS = {'image':'🖼','pdf':'📄','doc':'📝','zip':'📦','other':'📎'};

function getFileIcon(type){
  if(type.startsWith('image')) return '🖼';
  if(type.includes('pdf'))     return '📄';
  if(type.includes('word')||type.includes('document')) return '📝';
  if(type.includes('zip')||type.includes('rar'))       return '📦';
  return '📎';
}
function handleFileSelect(input){
  [...input.files].forEach(addFile);
  input.value = '';
}
function handleFileDrop(e){
  e.preventDefault();
  document.getElementById('fileDropzone').classList.remove('dragover');
  [...e.dataTransfer.files].forEach(addFile);
}
function addFile(file){
  if(uploadedFiles.length >= 5){ showToast('Limit reached','Maximum 5 attachments allowed.','warning'); return; }
  if(file.size > 5242880){ showToast('File too large',`${file.name} exceeds 5MB.`,'error'); return; }
  const id = Date.now() + Math.random();
  uploadedFiles.push({id, file});
  renderFileList();
}
function removeFile(id){
  const idx = uploadedFiles.findIndex(f=>f.id===id);
  if(idx > -1) uploadedFiles.splice(idx,1);
  renderFileList();
}
function renderFileList(){
  const list = document.getElementById('fileList');
  list.innerHTML = uploadedFiles.map(({id,file})=>`
    <div class="file-item">
      <span class="file-icon">${getFileIcon(file.type)}</span>
      <span class="file-name">${file.name}</span>
      <span class="file-size">${(file.size/1024).toFixed(0)} KB</span>
      <span class="file-del" onclick="removeFile(${id})">✕</span>
    </div>`).join('');
  // Show count in dropzone label
  const dz = document.getElementById('fileDropzone');
  if(uploadedFiles.length > 0){
    dz.querySelector('.dz-sub').textContent = `${uploadedFiles.length}/5 files attached · Click to add more`;
  } else {
    dz.querySelector('.dz-sub').textContent = 'PDF, DOC, PNG, JPG, ZIP · Max 5MB each';
  }
}

/* ══ PREVIEW PROPOSAL ══ */
function previewProposal(){
  const cover = document.getElementById('coverInput')?.value?.trim();
  if(!cover || cover.length < 10){ showToast('Cover letter empty','Write your cover letter before previewing.','warning'); return; }
  const bid  = parseFloat(document.getElementById('bidInput')?.value)||0;
  const days = parseInt(document.getElementById('daysInput')?.value)||0;
  const fee  = bid*0.10;
  const net  = bid-fee;
  const portUrls = document.querySelector('[name="portfolio_urls"]')?.value || '';

  // Milestones
  const msTitles  = [...document.querySelectorAll('[name="ms_title[]"]')].map(i=>i.value.trim());
  const msAmounts = [...document.querySelectorAll('[name="ms_amount[]"]')].map(i=>parseFloat(i.value)||0);
  const msDays    = [...document.querySelectorAll('[name="ms_days[]"]')].map(i=>parseInt(i.value)||0);

  let msHtml = '';
  if(msTitles.length > 0){
    msHtml = `<div class="preview-section">
      <div class="preview-label">Milestones</div>
      ${msTitles.map((t,i)=>`
        <div class="preview-ms-row">
          <div class="pm-num">${i+1}</div>
          <div class="pm-title">${t||'(untitled)'}</div>
          <div class="pm-amount">₵${msAmounts[i].toFixed(2)}</div>
          <div class="pm-days">⏱ ${msDays[i]||'—'} day${msDays[i]!==1?'s':''}</div>
        </div>`).join('')}
    </div>`;
  }

  const filesHtml = uploadedFiles.length ? `<div class="preview-section">
    <div class="preview-label">Attachments (${uploadedFiles.length})</div>
    ${uploadedFiles.map(({file})=>`<div style="font-size:12.5px;color:var(--tx-2);margin-bottom:4px;">${getFileIcon(file.type)} ${file.name} <span style="color:var(--tx-3);">(${(file.size/1024).toFixed(0)} KB)</span></div>`).join('')}
  </div>` : '';

  const portHtml = portUrls ? `<div class="preview-section">
    <div class="preview-label">Portfolio Links</div>
    <div class="preview-val" style="font-size:12.5px;word-break:break-all;">${portUrls}</div>
  </div>` : '';

  document.getElementById('previewBody').innerHTML = `
    <div style="background:var(--cyan-dim);border:1px solid var(--cyan-border);border-radius:12px;padding:14px 16px;margin-bottom:20px;display:grid;grid-template-columns:repeat(3,1fr);gap:10px;text-align:center;">
      <div><div style="font-family:var(--fm);font-weight:900;font-size:18px;color:var(--cyan);">₵${bid.toFixed(2)}</div><div style="font-size:11px;color:var(--tx-3);">Your Bid</div></div>
      <div><div style="font-family:var(--fm);font-weight:900;font-size:18px;color:var(--amber);">₵${fee.toFixed(2)}</div><div style="font-size:11px;color:var(--tx-3);">Platform Fee</div></div>
      <div><div style="font-family:var(--fm);font-weight:900;font-size:18px;color:var(--green);">₵${net.toFixed(2)}</div><div style="font-size:11px;color:var(--tx-3);">You'll Receive</div></div>
    </div>
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
      <span style="background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:9px;padding:7px 13px;font-size:12.5px;color:var(--tx-2);">⏱ ${days} day${days!==1?'s':''} delivery</span>
      <span style="background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:9px;padding:7px 13px;font-size:12.5px;color:var(--tx-2);">📋 For: <?= sanitize($job['title']) ?></span>
    </div>
    <div class="preview-section">
      <div class="preview-label">Cover Letter</div>
      <div class="preview-cover">${cover.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
    </div>
    ${msHtml}
    ${portHtml}
    ${filesHtml}
  `;
  document.getElementById('previewModal').classList.add('open');
  document.body.style.overflow='hidden';
}
function closePreview(){
  document.getElementById('previewModal').classList.remove('open');
  document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closePreview(); });

/* ══ FORM SUBMIT ══ */
const form = document.getElementById('proposalForm');
if(form){
  form.addEventListener('submit', function(){
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ Submitting…';
  });
}

/* ══ TOAST ══ */
const ICONS={success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
function showToast(title,msg,type='info',d=4000){
  const c=document.getElementById('toast-c');
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${ICONS[type]}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(50px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),330);},d);
}
</script>
</body>
</html>