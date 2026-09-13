<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('provider');

$userId  = $_SESSION['user_id'];
$user    = getUserById($userId);
$errors  = [];
$success = '';

try {
    $db = getDB();
    $stProv = $db->prepare("SELECT * FROM providers WHERE user_id = ? LIMIT 1");
    $stProv->execute([$userId]);
    $provider = $stProv->fetch();
    if (!$provider) {
        $db->prepare("INSERT IGNORE INTO providers (user_id) VALUES (?)")->execute([$userId]);
        $stProv->execute([$userId]);
        $provider = $stProv->fetch();
    }
    $providerId = $provider['id'];

    $allSkills = $db->query("SELECT s.*, c.name AS cat_name FROM skills s LEFT JOIN categories c ON c.id = s.category_id WHERE s.is_active = 1 ORDER BY c.name, s.name")->fetchAll();
    $stSel = $db->prepare("SELECT skill_id, proficiency FROM provider_skills WHERE provider_id = ?");
    $stSel->execute([$providerId]);
    $selectedSkills = [];
    foreach ($stSel->fetchAll() as $r) $selectedSkills[$r['skill_id']] = $r['proficiency'];

    $stPort = $db->prepare("SELECT * FROM portfolio_items WHERE provider_id = ? ORDER BY sort_order ASC, created_at DESC");
    $stPort->execute([$providerId]);
    $portfolioItems = $stPort->fetchAll();

    $stRevs = $db->prepare(
        "SELECT r.*, u.first_name, u.last_name, u.avatar,
                u.role AS reviewer_role, j.title AS job_title,
                COALESCE(rh.helpful_count, 0) AS helpful_count
         FROM reviews r
         JOIN  users u ON u.id = r.reviewer_id
         JOIN  jobs  j ON j.id = r.job_id
         LEFT  JOIN (
               SELECT review_id, COUNT(*) AS helpful_count
               FROM review_helpful GROUP BY review_id
         ) rh ON rh.review_id = r.id
         WHERE r.reviewee_id = ? AND r.is_public = 1
         ORDER BY r.created_at DESC LIMIT 10"
    );
    $stRevs->execute([$userId]);
    $reviews = $stRevs->fetchAll();

    $packages = [];
    try {
        $stPkg = $db->prepare("SELECT * FROM provider_packages WHERE provider_id = ? ORDER BY sort_order ASC LIMIT 3");
        $stPkg->execute([$providerId]);
        $packages = $stPkg->fetchAll();
    } catch (Exception $e) { /* table not yet migrated — silent */ }

    $simProviders = $db->query(
        "SELECT u.first_name, u.last_name, u.avatar, u.location,
                p.tagline, p.rating_avg, p.rating_count, p.hourly_rate,
                p.user_id, p.is_verified
         FROM providers p
         JOIN users u ON u.id = p.user_id
         WHERE p.id != $providerId AND u.is_active = 1 AND u.is_banned = 0
         ORDER BY p.rating_avg DESC, p.completed_jobs DESC LIMIT 4"
    )->fetchAll();

} catch (Exception $e) {
    error_log($e->getMessage());
    $provider = []; $allSkills = []; $selectedSkills = []; $portfolioItems = [];
    $reviews = []; $providerId = 0; $packages = []; $simProviders = [];
}

function handleUpload(string $key, string $subdir, array $allowedTypes, int $maxBytes = 5242880): string|false {
    if (empty($_FILES[$key]['tmp_name'])) return false;
    $file = $_FILES[$key];
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $maxBytes) return false;
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('gg_', true) . '.' . strtolower($ext);
    $dir  = __DIR__ . '/../uploads/' . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $dest = $dir . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;
    return APP_URL . '/uploads/' . $subdir . '/' . $name;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid security token.';
    } else {
        $section = $_POST['section'] ?? '';

        if ($section === 'basic') {
            $fn  = sanitize($_POST['first_name'] ?? '');
            $ln  = sanitize($_POST['last_name']  ?? '');
            $bio = sanitize($_POST['bio']         ?? '');
            $loc = sanitize($_POST['location']    ?? '');
            $ph  = sanitize($_POST['phone']       ?? '');
            $tag = sanitize($_POST['tagline']     ?? '');
            $gc  = sanitize($_POST['ghana_card']  ?? '');
            $vin = sanitize($_POST['video_intro'] ?? '');

            if (strlen($fn) < 2) $errors[] = 'First name too short.';
            if (strlen($ln) < 2) $errors[] = 'Last name too short.';
            if (strlen($bio) < 50) $errors[] = 'Bio must be at least 50 characters.';

            $avatarUrl = $user['avatar'] ?? null;
            if (!empty($_POST['avatar_cropped'])) {
                $b64 = $_POST['avatar_cropped'];
                if (preg_match('/^data:image\/(jpeg|png|webp);base64,/', $b64, $m)) {
                    $imgData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $b64));
                    if ($imgData !== false && strlen($imgData) < 3145728) {
                        $ext  = $m[1] === 'jpeg' ? 'jpg' : $m[1];
                        $dir  = __DIR__ . '/../uploads/avatars/';
                        if (!is_dir($dir)) mkdir($dir, 0755, true);
                        $fname = 'gg_av_' . $userId . '_' . time() . '.' . $ext;
                        if (file_put_contents($dir . $fname, $imgData)) {
                            if (!empty($user['avatar']) && str_contains($user['avatar'], '/uploads/avatars/')) {
                                $oldPath = __DIR__ . '/../' . parse_url($user['avatar'], PHP_URL_PATH);
                                if (file_exists($oldPath)) @unlink($oldPath);
                            }
                            $avatarUrl = APP_URL . '/uploads/avatars/' . $fname;
                        }
                    }
                }
            }

            if (empty($errors)) {
                try {
                    $db->prepare("UPDATE users SET first_name=?,last_name=?,bio=?,location=?,phone=?,ghana_card_number=?,avatar=?,updated_at=NOW() WHERE id=?")
                       ->execute([$fn,$ln,$bio,$loc,$ph,$gc,$avatarUrl,$userId]);
                    try {
                        $db->prepare("UPDATE providers SET tagline=?,video_intro_url=?,updated_at=NOW() WHERE user_id=?")
                           ->execute([$tag,$vin,$userId]);
                    } catch(Exception $e2) {
                        $db->prepare("UPDATE providers SET tagline=?,updated_at=NOW() WHERE user_id=?")
                           ->execute([$tag,$userId]);
                    }
                    $_SESSION['user_name'] = $fn.' '.$ln;
                    $success = 'basic';
                    $user    = getUserById($userId);
                    $stProv->execute([$userId]); $provider = $stProv->fetch();
                } catch(Exception $e){ error_log($e->getMessage()); $errors[] = 'Save failed.'; }
            }
        }

        if ($section === 'professional') {
            $rate  = max(0,(float)($_POST['hourly_rate'] ?? 0));
            $avail = sanitize($_POST['availability']     ?? 'full_time');
            $exp   = sanitize($_POST['experience_level'] ?? 'intermediate');
            $lang  = sanitize($_POST['languages']        ?? 'English');
            $resp  = sanitize($_POST['response_time']    ?? 'Within 1 hour');
            $purl  = sanitize($_POST['portfolio_url']    ?? '');
            $lnk   = sanitize($_POST['linkedin_url']     ?? '');
            $gh    = sanitize($_POST['github_url']       ?? '');
            try {
                $db->prepare("UPDATE providers SET hourly_rate=?,availability=?,experience_level=?,languages=?,response_time=?,portfolio_url=?,linkedin_url=?,github_url=?,updated_at=NOW() WHERE user_id=?")
                   ->execute([$rate,$avail,$exp,$lang,$resp,$purl,$lnk,$gh,$userId]);
                $success = 'professional';
                $stProv->execute([$userId]); $provider = $stProv->fetch();
            } catch(Exception $e){ error_log($e->getMessage()); $errors[] = 'Save failed.'; }
        }

        if ($section === 'skills') {
            $sids  = array_map('intval', $_POST['skill_ids']   ?? []);
            $profs = $_POST['proficiencies'] ?? [];
            try {
                $db->prepare("DELETE FROM provider_skills WHERE provider_id=?")->execute([$providerId]);
                if (!empty($sids)) {
                    $ins = $db->prepare("INSERT IGNORE INTO provider_skills (provider_id,skill_id,proficiency) VALUES (?,?,?)");
                    foreach(array_slice($sids,0,30) as $sid) {
                        $p = in_array($profs[$sid]??'',['beginner','intermediate','expert']) ? $profs[$sid] : 'intermediate';
                        $ins->execute([$providerId,$sid,$p]);
                    }
                }
                $stSel->execute([$providerId]); $selectedSkills=[];
                foreach($stSel->fetchAll() as $r) $selectedSkills[$r['skill_id']]=$r['proficiency'];
                $success = 'skills';
            } catch(Exception $e){ error_log($e->getMessage()); $errors[] = 'Skills save failed.'; }
        }

        if ($section === 'portfolio') {
            $pt      = sanitize($_POST['port_title']      ?? '');
            $pd      = sanitize($_POST['port_desc']       ?? '');
            $pu      = sanitize($_POST['port_url']        ?? '');
            $ptype   = sanitize($_POST['port_type']       ?? 'image');
            $videoUrl = sanitize($_POST['port_video_url'] ?? '');

            if (strlen($pt) < 3) $errors[] = 'Title must be at least 3 characters.';
            $cnt = $db->prepare("SELECT COUNT(*) FROM portfolio_items WHERE provider_id=?");
            $cnt->execute([$providerId]);
            if ((int)$cnt->fetchColumn() >= 30) $errors[] = 'Maximum 30 portfolio items allowed.';

            $imgUrl = null;
            if ($ptype === 'image') {
                $imgUrl = handleUpload('port_image', 'portfolio', ['image/jpeg','image/png','image/webp','image/gif'], 8388608);
                if (!$imgUrl && !empty($_POST['port_img_url'])) $imgUrl = sanitize($_POST['port_img_url']);
            }

            if (empty($errors)) {
                try {
                    $sort = (int)$db->query("SELECT COALESCE(MAX(sort_order),0)+1 FROM portfolio_items WHERE provider_id=$providerId")->fetchColumn();
                    $db->prepare("INSERT INTO portfolio_items (provider_id,title,description,project_url,image_url,video_url,item_type,sort_order) VALUES (?,?,?,?,?,?,?,?)")
                       ->execute([$providerId,$pt,$pd,$pu,$imgUrl,$ptype==='video'?$videoUrl:null,$ptype,$sort]);
                    $stPort->execute([$providerId]); $portfolioItems=$stPort->fetchAll();
                    $success = 'portfolio';
                } catch(Exception $e){ error_log($e->getMessage()); $errors[] = 'Add failed.'; }
            }
        }

        if ($section === 'delete_portfolio') {
            $iid = (int)($_POST['item_id'] ?? 0);
            try {
                $row = $db->prepare("SELECT image_url FROM portfolio_items WHERE id=? AND provider_id=?");
                $row->execute([$iid,$providerId]);
                $old = $row->fetchColumn();
                if ($old && str_contains($old,'/uploads/portfolio/')) {
                    $p = __DIR__.'/../'.parse_url($old,PHP_URL_PATH);
                    if(file_exists($p)) @unlink($p);
                }
                $db->prepare("DELETE FROM portfolio_items WHERE id=? AND provider_id=?")->execute([$iid,$providerId]);
                $stPort->execute([$providerId]); $portfolioItems=$stPort->fetchAll();
                $success = 'portfolio_deleted';
            } catch(Exception $e){ error_log($e->getMessage()); }
        }

        if ($section === 'packages') {
            try {
                $db->prepare("DELETE FROM provider_packages WHERE provider_id=?")->execute([$providerId]);
                $tiers = ['basic','standard','premium'];
                foreach($tiers as $tier) {
                    $pname  = sanitize($_POST["pkg_{$tier}_name"]  ?? '');
                    $pprice = max(0,(float)($_POST["pkg_{$tier}_price"] ?? 0));
                    $pdesc  = sanitize($_POST["pkg_{$tier}_desc"]  ?? '');
                    $pdays  = max(1,(int)($_POST["pkg_{$tier}_days"] ?? 7));
                    if (strlen($pname) > 1 && $pprice > 0) {
                        $db->prepare("INSERT INTO provider_packages (provider_id,tier,name,price,description,delivery_days,sort_order) VALUES (?,?,?,?,?,?,?)")
                           ->execute([$providerId,$tier,$pname,$pprice,$pdesc,$pdays,array_search($tier,$tiers)]);
                    }
                }
                $stPkg2 = $db->prepare("SELECT * FROM provider_packages WHERE provider_id = ? ORDER BY sort_order ASC LIMIT 3");
                $stPkg2->execute([$providerId]); $packages = $stPkg2->fetchAll();
                $success = 'packages';
            } catch(Exception $e){ error_log($e->getMessage()); $errors[]='Packages save failed.'; }
        }
    }
}

$checks = [
    'Photo'     => !empty($user['avatar']),
    'Bio'       => strlen($user['bio']??'') >= 50,
    'Location'  => !empty($user['location']),
    'Tagline'   => !empty($provider['tagline']),
    'Rate'      => ($provider['hourly_rate']??0) > 0,
    '3+ Skills' => count($selectedSkills) >= 3,
    'Portfolio' => count($portfolioItems) >= 1,
    'Phone'     => !empty($user['phone']),
];
$completeness = (int)(array_sum(array_map('intval',$checks))/count($checks)*100);
$compColor    = $completeness >= 80 ? '#1FD9A0' : ($completeness >= 50 ? '#F7B731' : '#FF4D6A');
$csrf         = generateCSRF();
$skillsByCat  = [];
foreach($allSkills as $sk) $skillsByCat[$sk['cat_name']?:'General'][] = $sk;

$ghanaSkillFallbacks = ['IT & Tech'=>['Web Developer','App Developer','Digital Marketer','UI/UX Designer','Network Engineer','Data Analyst','Cybersecurity'],'Creative Arts'=>['Graphic Designer','Photographer','Videographer','Content Writer','Animator','Illustrator'],'Skilled Trades'=>['Carpenter','Plumber','Electrician','Mechanic','Painter/Decorator','Mason/Bricklayer','Welder'],'Health & Wellness'=>['Nurse','Physiotherapist','Fitness Coach','Nutritionist','Home Caregiver','Pharmacist Assistant'],'Construction'=>['Builder/Contractor','Architect','Quantity Surveyor','Interior Designer','Landscaper'],'Education'=>['Math Tutor','English Tutor','Music Instructor','Art Teacher','Primary School Teacher'],'Hospitality'=>['Private Chef','Event Planner','Waiter/Waitress','Driver','Security Guard','Housekeeper'],'Business Services'=>['Accountant','Business Consultant','Admin Support','Legal Assistant','HR Consultant'],'Agriculture'=>['Farmer','Agri-tech Specialist','Livestock Manager','Crop Advisor']];
if (empty($allSkills)) {
    foreach($ghanaSkillFallbacks as $cat=>$skills) {
        foreach($skills as $i=>$sname) {
            $skillsByCat[$cat][] = ['id'=>$cat.'_'.$i,'name'=>$sname,'cat_name'=>$cat];
        }
    }
}

$msgs = ['basic'=>'Personal info saved!','professional'=>'Professional details updated!','skills'=>'Skills saved!','portfolio'=>'Portfolio item added!','portfolio_deleted'=>'Item removed.','packages'=>'Packages saved!'];
$jobs_done = (int)($provider['completed_jobs'] ?? 0);

$badgeTier = $jobs_done >= 20 ? 'premium' : ($jobs_done >= 5 ? 'verified' : 'free');
$showSubBanner = isLoggedIn() && $jobs_done >= 3;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Profile — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js" defer></script>
<style>
/* ══════════════════════════════════════
   DESIGN TOKENS — Volcanic Charcoal × Electric Cyan × Coral
   (mirrors index.php palette exactly)
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

  /* Legacy aliases used throughout the profile template */
  --gold:var(--amber);
  --gold-d:#D4980A;
  --blue:var(--cyan);
  --blue-l:var(--cyan-l);
  --indigo:var(--violet);
  --indigo-d:var(--violet-d);

  --gA:rgba(247,183,49,0.18);
  --gG:rgba(31,217,160,0.18);
  --gI:rgba(124,111,247,0.18);

  --fm:'Plus Jakarta Sans',sans-serif;
  --fb:'DM Sans',sans-serif;
  --sb:260px; --r:16px; --rs:10px; --e:all 0.26s ease;
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

  --gold:var(--amber); --gold-d:#B37D08;
  --blue:var(--cyan); --blue-l:var(--cyan-l);
  --indigo:var(--violet); --indigo-d:var(--violet-d);
  --gA:rgba(212,152,10,0.15); --gG:rgba(13,175,128,0.15); --gI:rgba(91,79,217,0.12);
}
.lm .sidebar{background:rgba(234,238,247,0.98);border-right-color:var(--bd);}
.lm .topbar{background:rgba(243,245,250,0.97);border-bottom-color:var(--bd);}
.lm .card{background:rgba(255,255,255,0.9);border-color:var(--bd);}
.lm .fi,.lm .fs,.lm .fta{background:rgba(255,255,255,0.7);border-color:var(--bd2);color:var(--tx);}
.lm .skc{background:rgba(255,255,255,0.7);border-color:var(--bd);}
.lm .port-card{background:rgba(255,255,255,0.8);border-color:var(--bd);}
.lm .review-card{background:rgba(255,255,255,0.8);}
.lm .pkg-card{background:rgba(255,255,255,0.85);}
.lm .sim-card{background:rgba(255,255,255,0.85);}
.lm .sub-banner{background:rgba(243,245,250,0.98);border-top-color:var(--coral-border);}

/* ══ RESET ══ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);min-height:100vh;display:flex;font-size:14px;transition:background .3s,color .3s;-webkit-font-smoothing:antialiased;}
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}
img{display:block;max-width:100%;}
h1,h2,h3,h4,.logo-text,.btn,.card-title,.stat-val,.pkg-name{font-family:var(--fm);-webkit-font-smoothing:antialiased;}

/* ══ SIDEBAR ══ */
.sidebar{
  width:var(--sb);min-height:100vh;
  background:var(--s1);border-right:1px solid var(--bd);
  position:fixed;top:0;left:0;z-index:200;
  display:flex;flex-direction:column;transition:var(--e);
}
.sidebar-logo{padding:20px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:9px;text-decoration:none;}
.logo-mark{width:34px;height:34px;background:linear-gradient(135deg,var(--cyan),var(--cyan-d));border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#0C0E14;flex-shrink:0;font-family:var(--fm);}
.logo-text{font-size:19px;font-weight:800;color:var(--tx);}
.logo-text span{color:var(--cyan);}
.sidebar-nav{flex:1;padding:10px;overflow-y:auto;}
.nav-section{font-size:9.5px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--tx-3);padding:6px 10px;margin:12px 0 3px;}
.nav-item{display:flex;align-items:center;gap:9px;padding:10px 12px;border-radius:10px;text-decoration:none;color:var(--tx-3);font-size:13px;font-weight:500;transition:var(--e);}
.nav-item:hover{background:rgba(255,255,255,0.05);color:var(--tx);}
.nav-item.active{background:var(--cyan-dim);color:var(--cyan);border-left:3px solid var(--cyan);padding-left:9px;}
.nav-item.danger{color:var(--red);}
.nav-item.danger:hover{background:rgba(255,77,106,0.08);}
.sidebar-user{padding:12px 10px;border-top:1px solid var(--bd);}
.suser-card{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;background:rgba(0,0,0,0.15);}
.suser-av{width:34px;height:34px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;overflow:hidden;}
.suser-av img{width:100%;height:100%;object-fit:cover;}
.suser-name{font-size:13px;font-weight:600;}
.suser-role{font-size:10px;color:var(--cyan);font-weight:700;text-transform:uppercase;}

/* ══ MAIN ══ */
.main{margin-left:var(--sb);flex:1;display:flex;flex-direction:column;min-width:0;}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 28px;height:62px;background:rgba(12,14,20,0.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--bd);position:sticky;top:0;z-index:100;}
.topbar-left h1{font-size:20px;font-weight:800;line-height:1.1;}
.topbar-left p{font-size:11.5px;color:var(--tx-3);margin-top:1px;}
.topbar-right{display:flex;align-items:center;gap:8px;}

/* ══ BUTTONS ══ */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--rs);font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:var(--e);white-space:nowrap;line-height:1.3;}
.btn-ghost{background:rgba(255,255,255,0.05);border:1px solid var(--bd);color:var(--tx);}
.btn-ghost:hover{background:rgba(255,255,255,0.09);border-color:var(--bd2);}
.btn-green{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;font-weight:700;box-shadow:0 3px 12px var(--gC);}
.btn-green:hover{transform:translateY(-2px);box-shadow:0 7px 20px var(--gC);}
.btn-gold{background:linear-gradient(135deg,var(--coral),var(--coral-d));color:#fff;font-weight:700;box-shadow:0 3px 12px var(--gO);}
.btn-gold:hover{transform:translateY(-2px);box-shadow:0 7px 20px var(--gO);}
.btn-indigo{background:linear-gradient(135deg,var(--violet),var(--violet-d));color:#fff;font-weight:700;}
.btn-indigo:hover{transform:translateY(-2px);}
.btn-blue{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;font-weight:700;}
.btn-blue:hover{transform:translateY(-2px);}
.btn-red{background:rgba(255,77,106,0.1);border:1px solid rgba(255,77,106,0.25);color:var(--red);}
.btn-red:hover{background:rgba(255,77,106,0.18);}
.btn-sm{padding:6px 12px;font-size:12px;}
.btn-lg{padding:12px 24px;font-size:14px;border-radius:12px;}
.btn-theme{background:transparent;color:var(--tx);border:1px solid var(--bd);border-radius:var(--rs);padding:6px 10px;cursor:pointer;font-size:13px;transition:var(--e);line-height:1;font-family:var(--fb);}

/* ══ CONTENT GRID ══ */
.content{padding:24px 28px 80px;}
.pg{display:grid;grid-template-columns:290px 1fr;gap:22px;align-items:start;}

/* ══ PROFILE SIDEBAR CARD ══ */
.profile-card{background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);border-radius:var(--r);overflow:hidden;position:sticky;top:78px;}
.pc-cover{height:80px;background:linear-gradient(135deg,rgba(124,111,247,0.25),rgba(0,212,200,0.18));position:relative;overflow:hidden;}
.pc-cover-grid{position:absolute;inset:0;background-image:radial-gradient(var(--tx) 0.5px,transparent 0.5px);background-size:14px 14px;opacity:0.03;}
.pc-avatar-wrap{padding:0 20px 16px;text-align:center;}
.pc-avatar{
  width:88px;height:88px;border-radius:50%;
  background:linear-gradient(135deg,var(--violet),var(--cyan-d));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-size:30px;font-weight:800;color:#fff;
  margin:-40px auto 12px;border:4px solid var(--s1);overflow:hidden;
  position:relative;cursor:pointer;transition:var(--e);
}
.pc-avatar:hover{transform:scale(1.04);}
.pc-avatar img{width:100%;height:100%;object-fit:cover;}
.pc-avatar-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .25s;border-radius:50%;font-size:18px;}
.pc-avatar:hover .pc-avatar-overlay{opacity:1;}
.pc-name{font-family:var(--fm);font-size:16px;font-weight:800;margin-bottom:3px;}
.pc-tag{color:var(--tx-3);font-size:12px;margin-bottom:8px;line-height:1.4;}
.pc-location{font-size:11.5px;color:var(--tx-3);margin-bottom:10px;display:flex;align-items:center;justify-content:center;gap:4px;}
.pc-badges{display:flex;flex-wrap:wrap;gap:5px;justify-content:center;margin-bottom:14px;}
.pbadge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:50px;font-size:10.5px;font-weight:700;font-family:var(--fm);}
.pb-v  {background:var(--green-dim);border:1px solid rgba(31,217,160,0.28);color:var(--green);}
.pb-av {background:var(--green-dim);border:1px solid rgba(31,217,160,0.18);color:var(--green);}
.pb-pt {background:rgba(247,183,49,0.08);border:1px solid rgba(247,183,49,0.18);color:var(--amber);}
.pb-prem{background:linear-gradient(135deg,rgba(255,107,74,0.12),rgba(247,183,49,0.1));border:1px solid var(--coral-border);color:var(--coral);}
.pb-tier{background:rgba(78,90,110,0.1);border:1px solid rgba(78,90,110,0.18);color:var(--tx-3);}

/* Quick stats strip */
.pc-quick-stats{
  display:grid;grid-template-columns:repeat(3,1fr);
  border-top:1px solid var(--bd);border-bottom:1px solid var(--bd);
}
.pqs-item{padding:12px 6px;text-align:center;position:relative;}
.pqs-item+.pqs-item::before{content:'';position:absolute;left:0;top:20%;bottom:20%;width:1px;background:var(--bd);}
.pqs-val{font-family:var(--fm);font-size:18px;font-weight:800;line-height:1;}
.pqs-lbl{font-size:9.5px;color:var(--tx-3);margin-top:3px;text-transform:uppercase;letter-spacing:.4px;}

/* Completeness */
.comp-section{padding:14px 18px;}
.comp-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:7px;}
.comp-label{font-size:11px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;}
.comp-pct{font-family:var(--fm);font-size:15px;font-weight:800;}
.comp-track{height:6px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;margin-bottom:10px;}
.comp-fill{height:100%;border-radius:3px;transition:width 1.2s cubic-bezier(.16,1,.3,1);}
.comp-checklist{display:flex;flex-direction:column;gap:5px;}
.comp-item{display:flex;align-items:center;gap:7px;font-size:11.5px;}
.comp-item.done{color:var(--green);}
.comp-item.todo{color:var(--tx-3);}
.comp-dot{width:14px;height:14px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:7px;flex-shrink:0;}
.comp-item.done .comp-dot{background:var(--green-dim);color:var(--green);}
.comp-item.todo .comp-dot{background:rgba(255,255,255,0.05);}

/* Meta rows */
.pc-meta{padding:13px 18px;border-top:1px solid var(--bd);}
.meta-row{display:flex;align-items:center;gap:9px;margin-bottom:7px;}
.meta-row:last-child{margin-bottom:0;}
.meta-icon{width:26px;height:26px;border-radius:7px;background:rgba(255,255,255,0.04);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.meta-lbl{font-size:9.5px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.3px;}
.meta-val{font-weight:600;font-size:12px;}

/* Rating */
.pc-rating{padding:13px 18px;border-top:1px solid var(--bd);text-align:center;}
.rating-big{font-family:var(--fm);font-size:36px;font-weight:800;color:var(--amber);line-height:1;}
.rating-stars{color:var(--amber);font-size:14px;letter-spacing:2px;margin:4px 0;}
.rating-count{font-size:11px;color:var(--tx-3);}
.rating-bars{margin-top:10px;display:flex;flex-direction:column;gap:5px;}
.rb-r{display:flex;align-items:center;gap:7px;font-size:11px;}
.rb-l{width:66px;color:var(--tx-3);text-align:right;font-size:10.5px;}
.rb-t{flex:1;height:4px;background:rgba(255,255,255,0.05);border-radius:2px;overflow:hidden;}
.rb-f{height:100%;background:var(--amber);border-radius:2px;}
.rb-v{width:22px;font-size:10px;color:var(--tx-3);}

/* CTA actions in sidebar */
.pc-cta{padding:14px 16px;border-top:1px solid var(--bd);display:flex;flex-direction:column;gap:8px;}
.pc-view-link{display:flex;align-items:center;justify-content:center;gap:7px;padding:12px 16px;border-top:1px solid var(--bd);font-size:13px;color:var(--cyan);text-decoration:none;font-weight:600;transition:background var(--e);}
.pc-view-link:hover{background:var(--cyan-dim);}

/* Share buttons */
.share-row{display:flex;gap:6px;justify-content:center;padding:12px 16px;border-top:1px solid var(--bd);}
.share-btn{
  width:34px;height:34px;border-radius:9px;border:1px solid var(--bd);
  display:flex;align-items:center;justify-content:center;
  font-size:14px;cursor:pointer;transition:var(--e);text-decoration:none;
  background:rgba(255,255,255,0.03);
}
.share-btn:hover{transform:translateY(-2px);border-color:var(--bd2);}
.share-wa{color:#25D366;}  .share-wa:hover{background:rgba(37,211,102,0.1);}
.share-fb{color:#1877F2;}  .share-fb:hover{background:rgba(24,119,242,0.1);}
.share-tw{color:#1DA1F2;}  .share-tw:hover{background:rgba(29,161,242,0.1);}
.share-cp{color:var(--tx-3);} .share-cp:hover{background:rgba(255,255,255,0.07);}

/* ══ TABS ══ */
.tab-bar{display:flex;gap:3px;background:rgba(0,0,0,0.2);padding:4px;border-radius:13px;margin-bottom:20px;overflow-x:auto;-ms-overflow-style:none;scrollbar-width:none;}
.tab-bar::-webkit-scrollbar{display:none;}
.tab-btn{display:flex;align-items:center;gap:6px;padding:10px 16px;border-radius:10px;font-family:var(--fb);font-size:13px;font-weight:600;color:var(--tx-3);background:transparent;border:none;cursor:pointer;transition:var(--e);white-space:nowrap;}
.tab-btn:hover{color:var(--tx);background:rgba(255,255,255,0.05);}
.tab-btn.active{background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;box-shadow:0 4px 14px var(--gC);}
.tab-count{background:rgba(0,0,0,0.2);padding:1px 6px;border-radius:50px;font-size:10px;}
.tab-pane{display:none;}
.tab-pane.active{display:block;}

/* ══ CARDS + FORMS ══ */
.card{background:var(--glass);backdrop-filter:blur(14px);border:1px solid var(--bd);border-radius:var(--r);padding:22px;margin-bottom:16px;transition:border-color .3s;}
.card:hover{border-color:var(--cyan-border);}
.card-title{font-size:16px;font-weight:700;margin-bottom:3px;}
.card-sub{color:var(--tx-3);font-size:13px;margin-bottom:18px;line-height:1.5;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{margin-bottom:14px;}
.form-group:last-child{margin-bottom:0;}
label.fl{display:block;font-size:10.5px;font-weight:700;color:var(--tx-3);margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px;}
.rq{color:var(--red);margin-left:2px;}
.fi,.fs,.fta{width:100%;background:rgba(0,0,0,0.22);border:1.5px solid var(--bd);border-radius:var(--rs);padding:11px 14px;color:var(--tx);font-family:var(--fb);font-size:14px;outline:none;transition:var(--e);}
.fi:focus,.fs:focus,.fta:focus{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-dim);}
.fi::placeholder,.fta::placeholder{color:var(--tx-3);}
.fs option{background:var(--s2);}
.fta{resize:vertical;min-height:100px;line-height:1.65;}
.fiw{position:relative;}
.fiw .fi{padding-left:38px;}
.fic{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:14px;pointer-events:none;}
.field-hint{font-size:11px;color:var(--tx-3);margin-top:4px;line-height:1.5;}
.char-count{font-size:11px;color:var(--tx-3);text-align:right;margin-top:3px;}
.form-actions{display:flex;align-items:center;gap:12px;margin-top:18px;}

/* ══ VIDEO INTRO ══ */
.video-intro-preview{
  background:rgba(0,0,0,0.25);border:1px solid var(--bd);
  border-radius:var(--rs);padding:14px;text-align:center;
  margin-top:10px;display:none;
}
.video-intro-preview.show{display:block;}
.video-intro-preview iframe,.video-intro-preview video{
  width:100%;border-radius:8px;border:none;
  max-height:200px;
}

/* ══ AVATAR UPLOAD ══ */
.avatar-upload-area{display:flex;align-items:center;gap:20px;padding:18px;background:rgba(0,0,0,0.15);border:2px dashed var(--bd2);border-radius:var(--rs);margin-bottom:18px;cursor:pointer;transition:var(--e);}
.avatar-upload-area:hover{border-color:var(--cyan-border);background:var(--cyan-dim);}
.avatar-upload-area.dragover{border-color:var(--cyan);background:rgba(0,212,200,0.06);}
.aua-preview{width:80px;height:80px;border-radius:50%;flex-shrink:0;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:26px;font-weight:800;color:#fff;overflow:hidden;border:3px solid var(--cyan-border);}
.aua-preview img{width:100%;height:100%;object-fit:cover;}
.aua-text h4{font-size:14px;font-weight:700;font-family:var(--fm);margin-bottom:4px;}
.aua-text p{font-size:12px;color:var(--tx-3);line-height:1.5;}
.aua-btn{margin-top:8px;}

/* ══ CROP MODAL ══ */
.crop-modal{display:none;position:fixed;inset:0;z-index:3000;background:rgba(0,0,0,0.92);backdrop-filter:blur(20px);align-items:center;justify-content:center;padding:20px;}
.crop-modal.open{display:flex;}
.crop-box{background:var(--s2);border:1px solid var(--bd2);border-radius:20px;overflow:hidden;width:100%;max-width:560px;animation:scaleIn .3s ease;}
@keyframes scaleIn{from{opacity:0;transform:scale(.92);}to{opacity:1;transform:scale(1);}}
.crop-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--bd);}
.crop-header h3{font-family:var(--fm);font-size:16px;font-weight:700;}
.crop-close{background:rgba(255,255,255,0.06);border:1px solid var(--bd);border-radius:8px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;color:var(--tx-3);transition:var(--e);}
.crop-close:hover{background:rgba(255,77,106,0.15);color:var(--red);}
.crop-canvas-wrap{padding:18px;background:#000;min-height:300px;display:flex;align-items:center;justify-content:center;}
#cropImage{max-height:340px;display:block;}
.crop-controls{padding:16px 22px;border-top:1px solid var(--bd);display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
.crop-zoom-btns{display:flex;gap:6px;}
.crop-zoom-btn{width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,0.06);border:1px solid var(--bd);color:var(--tx);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--e);}
.crop-zoom-btn:hover{background:rgba(255,255,255,0.1);}
.crop-footer{display:flex;gap:9px;margin-left:auto;}

/* ══ SKILLS ══ */
.skill-search-wrap{position:relative;margin-bottom:16px;}
.skill-search-wrap .fi{padding-left:38px;}
.selected-skills-preview{background:var(--cyan-dim);border:1px solid var(--cyan-border);border-radius:var(--rs);padding:12px 14px;margin-bottom:16px;display:none;}
.selected-skills-preview.has-skills{display:block;}
.ssp-title{font-size:10.5px;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.7px;margin-bottom:9px;}
.ssp-tags{display:flex;flex-wrap:wrap;gap:6px;}
.ssp-tag{display:inline-flex;align-items:center;gap:6px;background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);padding:4px 9px;border-radius:6px;font-size:11.5px;font-weight:600;}
.ssp-tag select{background:transparent;border:none;color:var(--cyan);font-size:10px;outline:none;cursor:pointer;font-family:var(--fb);}
.ssp-tag select option{background:var(--s2);color:var(--tx);}
.ssp-tag .rm{cursor:pointer;opacity:.6;font-size:12px;}
.ssp-tag .rm:hover{opacity:1;}
.skill-cats{display:flex;flex-direction:column;gap:16px;}
.skill-cat-label{font-size:10px;font-weight:700;color:var(--tx-3);text-transform:uppercase;letter-spacing:1px;padding-bottom:6px;border-bottom:1px solid var(--bd);margin-bottom:8px;}
.skill-chips{display:flex;flex-wrap:wrap;gap:6px;}
.skc{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;font-size:12.5px;font-weight:500;cursor:pointer;user-select:none;transition:var(--e);background:rgba(255,255,255,0.04);border:1.5px solid var(--bd);color:var(--tx-3);}
.skc:hover{border-color:var(--cyan-border);color:var(--tx);}
.skc.selected{background:var(--cyan-dim);border-color:var(--cyan);color:var(--cyan);}
.skc.selected::before{content:'✓ ';}

/* Proficiency bars */
.prof-bar-wrap{display:flex;align-items:center;gap:8px;font-size:11px;}
.prof-label{width:80px;color:var(--tx-3);}
.prof-track{flex:1;height:5px;background:rgba(255,255,255,0.06);border-radius:3px;overflow:hidden;}
.prof-fill-b{width:33%;background:var(--tx-3);}
.prof-fill-i{width:66%;background:var(--violet);}
.prof-fill-e{width:100%;background:var(--cyan);}

/* ══ PORTFOLIO ══ */
.portfolio-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(185px,1fr));gap:13px;margin-bottom:20px;}
.port-card{background:rgba(0,0,0,0.22);border:1px solid var(--bd);border-radius:12px;overflow:hidden;transition:var(--e);position:relative;}
.port-card:hover{transform:translateY(-4px);border-color:var(--violet-border);box-shadow:0 14px 36px rgba(0,0,0,.4);}
.port-thumb{height:130px;overflow:hidden;position:relative;background:linear-gradient(135deg,var(--s3),var(--violet-dim));display:flex;align-items:center;justify-content:center;font-size:32px;}
.port-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .4s;}
.port-card:hover .port-thumb img{transform:scale(1.06);}
.port-thumb video{width:100%;height:100%;object-fit:cover;}
.port-type-badge{position:absolute;top:7px;left:7px;background:rgba(0,0,0,0.7);backdrop-filter:blur(8px);color:#fff;padding:2px 7px;border-radius:5px;font-size:10px;font-weight:700;}
.port-body{padding:10px 12px;}
.port-name{font-weight:700;font-size:13px;font-family:var(--fm);margin-bottom:3px;}
.port-desc{font-size:11px;color:var(--tx-3);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:8px;}
.port-actions{display:flex;gap:5px;flex-wrap:wrap;}
.upload-dropzone{border:2px dashed var(--bd2);border-radius:var(--rs);padding:26px 20px;text-align:center;cursor:pointer;transition:var(--e);margin-bottom:14px;background:rgba(0,0,0,0.1);}
.upload-dropzone:hover,.upload-dropzone.dragover{border-color:var(--cyan-border);background:var(--cyan-dim);}
.upload-dropzone .dz-icon{font-size:32px;margin-bottom:8px;}
.upload-dropzone h4{font-family:var(--fm);font-size:14px;font-weight:700;margin-bottom:4px;}
.upload-dropzone p{font-size:12px;color:var(--tx-3);}
.upload-preview{display:none;width:100%;max-height:200px;border-radius:var(--rs);object-fit:cover;margin-bottom:8px;border:1px solid var(--bd);}
.upload-preview.visible{display:block;}
.port-type-toggle{display:flex;gap:6px;margin-bottom:14px;}
.ptt-btn{padding:8px 16px;border-radius:8px;font-size:12.5px;font-weight:600;background:rgba(255,255,255,0.04);border:1.5px solid var(--bd);color:var(--tx-3);cursor:pointer;transition:var(--e);font-family:var(--fb);}
.ptt-btn.active{background:var(--cyan-dim);border-color:var(--cyan);color:var(--cyan);}

/* ══ PRICING PACKAGES ══ */
.pkg-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px;}
.pkg-card{
  background:rgba(0,0,0,0.22);border:1px solid var(--bd);border-radius:var(--r);
  padding:22px;text-align:center;transition:var(--e);position:relative;overflow:hidden;
}
.pkg-card:hover{transform:translateY(-4px);border-color:var(--coral-border);box-shadow:0 16px 40px rgba(0,0,0,.3);}
.pkg-card.standard{border-color:var(--coral-border);background:var(--coral-dim);}
.pkg-card.standard::before{content:'POPULAR';position:absolute;top:12px;right:-22px;background:var(--coral);color:#fff;font-size:9px;font-weight:800;padding:3px 26px;transform:rotate(45deg);font-family:var(--fm);}
.pkg-tier-icon{font-size:28px;margin-bottom:10px;}
.pkg-name{font-size:16px;font-weight:800;margin-bottom:6px;}
.pkg-price{font-family:var(--fm);font-size:28px;font-weight:900;color:var(--cyan);margin-bottom:4px;line-height:1;}
.pkg-price small{font-size:13px;color:var(--tx-3);font-weight:400;}
.pkg-desc{font-size:12.5px;color:var(--tx-2);line-height:1.65;margin-bottom:14px;}
.pkg-delivery{font-size:11.5px;color:var(--green);margin-bottom:16px;}
.pkg-cta{display:flex;flex-direction:column;gap:7px;}

/* Package edit form */
.pkg-edit-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;}
.pkg-edit-col{background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:var(--rs);padding:14px;}
.pkg-edit-tier{font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:1px;color:var(--tx-3);margin-bottom:10px;}

/* ══ REVIEWS ══ */
.review-card{background:rgba(0,0,0,0.15);border:1px solid var(--bd);border-radius:12px;padding:16px;margin-bottom:12px;transition:var(--e);}
.review-card:hover{border-color:var(--cyan-border);}
.rv-header{display:flex;align-items:flex-start;gap:11px;margin-bottom:11px;}
.rv-av{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0;overflow:hidden;color:#fff;}
.rv-av img{width:100%;height:100%;object-fit:cover;}
.rv-name{font-weight:700;font-size:13.5px;margin-bottom:1px;}
.rv-job{font-size:11px;color:var(--tx-3);}
.rv-prof{font-size:10.5px;color:var(--violet);font-weight:600;}
.rv-stars{color:var(--amber);font-size:13px;letter-spacing:1px;}
.rv-date{font-size:10px;color:var(--tx-3);margin-left:auto;white-space:nowrap;}
.rv-text{font-size:13px;color:var(--tx-2);line-height:1.75;font-style:italic;margin-bottom:9px;}
.rv-breakdown{display:flex;gap:10px;flex-wrap:wrap;padding-top:9px;border-top:1px solid var(--bd);}
.rbd{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--tx-3);}
.rbd .stars{color:var(--amber);font-size:10px;}
.rv-verified{display:inline-flex;align-items:center;gap:4px;background:var(--green-dim);border:1px solid rgba(31,217,160,0.18);color:var(--green);padding:2px 8px;border-radius:5px;font-size:10px;font-weight:700;margin-bottom:8px;}
.rv-actions{display:flex;align-items:center;gap:10px;margin-top:9px;padding-top:9px;border-top:1px solid var(--bd);}
.rv-helpful{font-size:11px;color:var(--tx-3);display:flex;align-items:center;gap:5px;cursor:pointer;transition:var(--e);}
.rv-helpful:hover{color:var(--green);}
.rv-report{font-size:11px;color:var(--tx-3);cursor:pointer;margin-left:auto;transition:var(--e);}
.rv-report:hover{color:var(--red);}

/* ══ SIMILAR PROVIDERS ══ */
.sim-section{margin-top:24px;}
.sim-title{font-family:var(--fm);font-size:15px;font-weight:700;margin-bottom:14px;}
.sim-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.sim-card{background:rgba(0,0,0,0.2);border:1px solid var(--bd);border-radius:12px;padding:14px;transition:var(--e);text-decoration:none;color:var(--tx);display:flex;gap:11px;align-items:center;}
.sim-card:hover{border-color:var(--cyan-border);transform:translateY(-2px);}
.sim-av{width:42px;height:42px;border-radius:50%;flex-shrink:0;overflow:hidden;background:linear-gradient(135deg,var(--violet),var(--cyan-d));display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:14px;font-weight:700;color:#fff;}
.sim-av img{width:100%;height:100%;object-fit:cover;}
.sim-name{font-size:13px;font-weight:700;font-family:var(--fm);}
.sim-tag{font-size:11px;color:var(--tx-3);margin-top:2px;line-height:1.3;}
.sim-rate{font-size:12px;font-weight:700;color:var(--cyan);font-family:var(--fm);}

/* ══ SUBSCRIPTION BANNER ══ */
.sub-banner{
  position:fixed;bottom:0;left:var(--sb);right:0;z-index:998;
  background:linear-gradient(135deg,rgba(12,14,20,0.98),rgba(25,29,39,0.98));
  backdrop-filter:blur(20px);border-top:1px solid var(--coral-border);
  padding:13px 28px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;
  transform:translateY(100%);transition:transform .5s ease;
}
.sub-banner.show{transform:translateY(0);}
.sub-banner-text{font-size:13.5px;color:var(--tx-2);}
.sub-banner-text strong{color:var(--coral);font-family:var(--fm);}
.sub-close{cursor:pointer;color:var(--tx-3);font-size:20px;padding:4px;line-height:1;transition:var(--e);}
.sub-close:hover{color:var(--tx);}

/* ══ ALERTS + TOAST ══ */
.alert{padding:12px 16px;border-radius:var(--rs);margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:9px;}
.alert-err{background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.22);color:#FF9EB0;}
.alert-ok {background:var(--green-dim);border:1px solid rgba(31,217,160,0.2);color:var(--green);}
#toast-c{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd);padding:13px 16px;border-radius:var(--rs);max-width:330px;min-width:240px;box-shadow:0 12px 32px rgba(0,0,0,.45);animation:toastIn .35s ease;backdrop-filter:blur(14px);}
.toast.success{border-left:3px solid var(--green);}
.toast.error  {border-left:3px solid var(--red);}
.toast.info   {border-left:3px solid var(--cyan);}
.t-ico{font-size:16px;flex-shrink:0;}
.t-bod{flex:1;}
.t-ttl{font-family:var(--fm);font-weight:700;font-size:12.5px;margin-bottom:1px;}
.t-msg{font-size:11px;color:var(--tx-3);}
.t-cls{cursor:pointer;color:var(--tx-3);font-size:17px;}
@keyframes toastIn{from{opacity:0;transform:translateX(50px);}to{opacity:1;transform:translateX(0);}}

/* ══ RESPONSIVE ══ */
@media(max-width:1100px){.pg{grid-template-columns:1fr;}.profile-card{position:static;}.pkg-grid{grid-template-columns:1fr;}}
@media(max-width:900px){.sim-grid{grid-template-columns:1fr;}.pkg-edit-grid{grid-template-columns:1fr;}}
@media(max-width:768px){
  .sidebar{display:none;}.main{margin-left:0;}
  .content{padding:16px 14px 80px;}
  .form-row{grid-template-columns:1fr;}
  .topbar{padding:0 16px;}
  .portfolio-grid{grid-template-columns:repeat(2,1fr);}
  .sub-banner{left:0;}
}
@media(max-width:480px){.portfolio-grid{grid-template-columns:1fr;}.pkg-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>

<!-- ══════ SIDEBAR ══════ -->
<aside class="sidebar">
  <a href="<?= APP_URL ?>/index.php" class="sidebar-logo">
    <div class="logo-mark">G</div>
    <span class="logo-text">Gig<span>Ghana</span></span>
  </a>
  <nav class="sidebar-nav">
    <div class="nav-section">Provider</div>
    <a href="<?= APP_URL ?>/provider/dashboard.php"   class="nav-item">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/provider/browse-jobs.php" class="nav-item">🔍 Browse Jobs</a>
    <a href="<?= APP_URL ?>/provider/profile.php"     class="nav-item active">👤 My Profile</a>
    <a href="<?= APP_URL ?>/provider/earnings.php"    class="nav-item">💰 Earnings</a>
    <div class="nav-section">Activity</div>
    <a href="<?= APP_URL ?>/client/messages.php"                     class="nav-item">💬 Messages</a>
    <a href="<?= APP_URL ?>/profile.php?id=<?= $userId ?>" target="_blank" class="nav-item">🌐 Public View</a>
    <div class="nav-section">Account</div>
    <a href="<?= APP_URL ?>/auth/logout.php" class="nav-item danger">🚪 Sign Out</a>
  </nav>
  <div class="sidebar-user">
    <div class="suser-card">
      <div class="suser-av">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= sanitize($user['avatar']) ?>" alt="">
        <?php else: ?>
          <?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?>
        <?php endif; ?>
      </div>
      <div>
        <div class="suser-name"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div>
        <div class="suser-role">Freelancer</div>
      </div>
    </div>
  </div>
</aside>

<!-- ══════ MAIN ══════ -->
<div class="main">
  <header class="topbar">
    <div class="topbar-left">
      <h1>My Profile</h1>
      <p>Strength: <?= $completeness ?>% complete</p>
    </div>
    <div class="topbar-right">
      <button onclick="toggleTheme()" class="btn-theme" id="themeBtn">🌙</button>
      <a href="<?= APP_URL ?>/profile.php?id=<?= $userId ?>" target="_blank" class="btn btn-ghost">🌐 Public View</a>
      <a href="<?= APP_URL ?>/provider/dashboard.php" class="btn btn-ghost">← Dashboard</a>
    </div>
  </header>

  <div class="content">
    <?php if (!empty($errors)): ?>
    <div class="alert alert-err">⚠️ <?= implode(' · ', array_map('htmlspecialchars',$errors)) ?></div>
    <?php endif; ?>
    <?php if ($success && isset($msgs[$success])): ?>
    <div class="alert alert-ok" id="sAlert">✓ <?= $msgs[$success] ?></div>
    <?php endif; ?>

    <div class="pg">

      <!-- ════ LEFT PROFILE CARD ════ -->
      <div>
        <div class="profile-card">
          <div class="pc-cover">
            <div class="pc-cover-grid"></div>
          </div>
          <div class="pc-avatar-wrap">
            <div class="pc-avatar" id="pcAvatar" onclick="triggerAvatarUpload()">
              <?php if (!empty($user['avatar'])): ?>
                <img src="<?= sanitize($user['avatar']) ?>" alt="" id="pcAvatarImg">
              <?php else: ?>
                <span id="pcAvatarInit"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></span>
              <?php endif; ?>
              <div class="pc-avatar-overlay">📷</div>
            </div>
            <div class="pc-name" id="pcName"><?= sanitize($user['first_name'].' '.$user['last_name']) ?></div>
            <div class="pc-tag"  id="pcTag"><?= sanitize($provider['tagline'] ?? 'Add your professional tagline') ?></div>
            <?php if (!empty($user['location'])): ?>
            <div class="pc-location">📍 <?= sanitize($user['location']) ?></div>
            <?php endif; ?>
            <div class="pc-badges">
              <?php if ($badgeTier==='premium'): ?><span class="pbadge pb-prem">⭐ Premium</span>
              <?php elseif ($badgeTier==='verified'): ?><span class="pbadge pb-v">✓ Verified</span>
              <?php else: ?><span class="pbadge pb-tier">🌱 Beginner</span><?php endif; ?>
              <?php if ($provider['is_verified'] ?? 0): ?><span class="pbadge pb-v">🪪 ID Verified</span><?php endif; ?>
              <?php
              $avBadge = match($provider['availability'] ?? 'full_time') {
                  'full_time'     => ['pb-av','🟢 Available'],
                  'part_time'     => ['pb-pt','🟡 Part-time'],
                  'not_available' => ['pb-na','🔴 Busy'],
                  default         => ['pb-av','🟢 Available'],
              };
              ?>
              <span class="pbadge <?= $avBadge[0] ?>"><?= $avBadge[1] ?></span>
            </div>
          </div>

          <!-- ── Quick Stats ── -->
          <div class="pc-quick-stats">
            <div class="pqs-item">
              <div class="pqs-val"><?= $jobs_done ?></div>
              <div class="pqs-lbl">Jobs Done</div>
            </div>
            <div class="pqs-item">
              <div class="pqs-val"><?= number_format((float)($provider['rating_avg']??0),1) ?></div>
              <div class="pqs-lbl">Avg Rating</div>
            </div>
            <div class="pqs-item">
              <div class="pqs-val"><?= $provider['success_rate']??'—' ?><?= $provider['success_rate']??false ? '%' : '' ?></div>
              <div class="pqs-lbl">Success</div>
            </div>
          </div>

          <!-- ── Completeness ── -->
          <div class="comp-section">
            <div class="comp-header">
              <span class="comp-label">Profile Strength</span>
              <span class="comp-pct" style="color:<?= $compColor ?>;"><?= $completeness ?>%</span>
            </div>
            <div class="comp-track"><div class="comp-fill" id="compFill" style="width:0%;background:<?= $compColor ?>;"></div></div>
            <div class="comp-checklist">
              <?php foreach($checks as $lbl=>$done): ?>
              <div class="comp-item <?= $done?'done':'todo' ?>">
                <div class="comp-dot"><?= $done?'✓':'○' ?></div><span><?= $lbl ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- ── Meta ── -->
          <div class="pc-meta">
            <?php $metas=[
              ['📍','Location',   $user['location']??'Not set'],
              ['⏱','Response',   $provider['response_time']??'1 hour'],
              ['₵','Rate',       formatCurrency($provider['hourly_rate']??0).'/hr'],
              ['✅','Jobs Done',  $provider['completed_jobs']??0],
              ['🌍','Languages', $provider['languages']??'English'],
            ];
            foreach($metas as [$ic,$lb,$vl]): ?>
            <div class="meta-row">
              <div class="meta-icon"><?= $ic ?></div>
              <div><div class="meta-lbl"><?= $lb ?></div><div class="meta-val"><?= is_string($vl)?sanitize($vl):$vl ?></div></div>
            </div>
            <?php endforeach; ?>
          </div>

          <!-- ── Rating ── -->
          <?php if(($provider['rating_count']??0) > 0):
            $rav=(float)($provider['rating_avg']??0);
            $avgC=$avgQ=$avgP=$avgT=0;
            if(!empty($reviews)){$avgC=array_sum(array_column($reviews,'rating_communication'))/count($reviews);$avgQ=array_sum(array_column($reviews,'rating_quality'))/count($reviews);$avgP=array_sum(array_column($reviews,'rating_professionalism'))/count($reviews);$avgT=array_sum(array_column($reviews,'rating_timeliness'))/count($reviews);}
          ?>
          <div class="pc-rating">
            <div class="rating-big"><?= number_format($rav,1) ?></div>
            <div class="rating-stars"><?php for($i=1;$i<=5;$i++) echo $rav>=$i?'★':($rav>=$i-.5?'✦':'☆'); ?></div>
            <div class="rating-count"><?= $provider['rating_count'] ?> review<?= $provider['rating_count']!=1?'s':'' ?></div>
            <div class="rating-bars">
              <?php foreach(['Comm.'=>$avgC,'Quality'=>$avgQ,'Prof.'=>$avgP,'Time'=>$avgT] as $l=>$v): ?>
              <div class="rb-r"><span class="rb-l"><?= $l ?></span><div class="rb-t"><div class="rb-f" style="width:<?= round($v/5*100) ?>%"></div></div><span class="rb-v"><?= number_format($v,1) ?></span></div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>

          <!-- ── CTA buttons ── -->
          <div class="pc-cta">
            <a href="<?= APP_URL ?>/client/messages.php?provider=<?= $userId ?>" class="btn btn-green" style="justify-content:center;">💬 Contact Me</a>
            <a href="<?= APP_URL ?>/jobs/hire.php?provider=<?= $userId ?>" class="btn btn-gold" style="justify-content:center;">⚡ Hire Me</a>
          </div>

          <!-- ── Share ── -->
          <div class="share-row">
            <?php $shareUrl = APP_URL.'/profile.php?id='.$userId; $shareName = sanitize($user['first_name'].' '.$user['last_name']); ?>
            <a class="share-btn share-wa"  href="https://wa.me/?text=Check+out+<?= urlencode($shareName) ?>+on+GigGhana+<?= urlencode($shareUrl) ?>" target="_blank" title="Share on WhatsApp">💬</a>
            <a class="share-btn share-fb"  href="https://facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank" title="Share on Facebook">f</a>
            <a class="share-btn share-tw"  href="https://twitter.com/intent/tweet?text=Check+out+<?= urlencode($shareName) ?>+on+GigGhana&url=<?= urlencode($shareUrl) ?>" target="_blank" title="Share on Twitter">𝕏</a>
            <div class="share-btn share-cp" onclick="copyProfileLink('<?= $shareUrl ?>')" title="Copy link">🔗</div>
          </div>

          <a href="<?= APP_URL ?>/profile.php?id=<?= $userId ?>" class="pc-view-link" target="_blank">🌐 View Public Profile →</a>
        </div>

        <!-- ── Similar Providers ── -->
        <?php if (!empty($simProviders)): ?>
        <div class="sim-section">
          <div class="sim-title">👥 Similar Freelancers</div>
          <div class="sim-grid">
            <?php foreach($simProviders as $sp):
              $sinit = strtoupper(substr($sp['first_name'],0,1).substr($sp['last_name'],0,1));
            ?>
            <a href="<?= APP_URL ?>/profile.php?id=<?= $sp['user_id'] ?>" class="sim-card">
              <div class="sim-av">
                <?php if (!empty($sp['avatar'])): ?><img src="<?= sanitize($sp['avatar']) ?>" alt="" loading="lazy"><?php else: echo $sinit; endif; ?>
              </div>
              <div>
                <div class="sim-name"><?= sanitize($sp['first_name'].' '.$sp['last_name']) ?></div>
                <div class="sim-tag"><?= sanitize($sp['tagline'] ?? '') ?></div>
                <div class="sim-rate"><?= $sp['hourly_rate']>0 ? formatCurrency($sp['hourly_rate']).'/hr' : 'Negotiable' ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- ════ RIGHT TABS ════ -->
      <div>
        <div class="tab-bar" role="tablist">
          <button class="tab-btn active" data-tab="basic"        role="tab">👤 Basic Info</button>
          <button class="tab-btn"        data-tab="professional" role="tab">💼 Professional</button>
          <button class="tab-btn"        data-tab="skills"       role="tab">🛠 Skills <span class="tab-count"><?= count($selectedSkills) ?></span></button>
          <button class="tab-btn"        data-tab="portfolio"    role="tab">🖼 Portfolio <span class="tab-count"><?= count($portfolioItems) ?></span></button>
          <button class="tab-btn"        data-tab="packages"     role="tab">💰 Packages <span class="tab-count"><?= count($packages) ?></span></button>
          <button class="tab-btn"        data-tab="reviews"      role="tab">⭐ Reviews <span class="tab-count"><?= count($reviews) ?></span></button>
        </div>

        <!-- ── BASIC INFO ── -->
        <div class="tab-pane active" id="tab-basic">
          <form method="POST" id="basicForm" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token"     value="<?= $csrf ?>">
            <input type="hidden" name="section"        value="basic">
            <input type="hidden" name="avatar_cropped" id="avatarCroppedData">

            <div class="card">
              <div class="card-title">📷 Profile Photo</div>
              <div class="card-sub">Upload and crop your photo. A clear, professional headshot builds client trust.</div>
              <div class="avatar-upload-area" id="avatarDropArea" onclick="triggerAvatarUpload()" ondrop="handleAvatarDrop(event)" ondragover="e=>e.preventDefault()" ondragenter="this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')">
                <div class="aua-preview" id="auaPreview">
                  <?php if (!empty($user['avatar'])): ?>
                    <img src="<?= sanitize($user['avatar']) ?>" alt="" id="auaPreviewImg">
                  <?php else: ?>
                    <span><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></span>
                  <?php endif; ?>
                </div>
                <div class="aua-text">
                  <h4>Click to upload or drag & drop</h4>
                  <p>JPG, PNG, or WebP · Max 3MB · Cropped to circle</p>
                  <div class="aua-btn"><span class="btn btn-ghost btn-sm" style="pointer-events:none;">📁 Choose Photo</span></div>
                </div>
              </div>
              <input type="file" id="avatarFileInput" accept="image/*" style="display:none;" onchange="handleAvatarFile(this)">
            </div>

            <div class="card">
              <div class="card-title">👤 Personal Information</div>
              <div class="card-sub">Your public identity on GigGhana — use your real name.</div>
              <div class="form-row">
                <div class="form-group">
                  <label class="fl">First Name <span class="rq">*</span></label>
                  <input type="text" name="first_name" class="fi" id="fnInp" value="<?= htmlspecialchars($user['first_name']??'') ?>" placeholder="Kofi" maxlength="100">
                </div>
                <div class="form-group">
                  <label class="fl">Last Name <span class="rq">*</span></label>
                  <input type="text" name="last_name"  class="fi" id="lnInp" value="<?= htmlspecialchars($user['last_name']??'') ?>"  placeholder="Mensah" maxlength="100">
                </div>
              </div>
              <div class="form-group">
                <label class="fl">Professional Tagline <span class="rq">*</span></label>
                <input type="text" name="tagline" class="fi" id="tagInp" value="<?= htmlspecialchars($provider['tagline']??'') ?>" placeholder="e.g. Expert Carpenter · 10 Years Experience in Accra" maxlength="160">
                <div class="char-count"><span id="tagCnt"><?= strlen($provider['tagline']??'') ?></span>/160</div>
              </div>
              <div class="form-group">
                <label class="fl">About Me / Bio <span class="rq">*</span></label>
                <textarea name="bio" class="fta" id="bioInp" rows="6" maxlength="1000" placeholder="Describe your background, specialties, experience and work style… (min 50 characters)"><?= htmlspecialchars($user['bio']??'') ?></textarea>
                <div class="char-count"><span id="bioCnt"><?= strlen($user['bio']??'') ?></span>/1000 · min 50</div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="fl">Phone</label>
                  <div class="fiw"><span class="fic">📱</span><input type="tel" name="phone" class="fi" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="+233 24 000 0000"></div>
                </div>
                <div class="form-group">
                  <label class="fl">Location</label>
                  <div class="fiw"><span class="fic">📍</span><input type="text" name="location" class="fi" value="<?= htmlspecialchars($user['location']??'') ?>" placeholder="Accra, Greater Accra"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="fl">Ghana Card Number</label>
                <div class="fiw"><span class="fic">🪪</span><input type="text" name="ghana_card" class="fi" value="<?= htmlspecialchars($user['ghana_card_number']??'') ?>" placeholder="GHA-XXXXXXXXX-X"></div>
                <div class="field-hint">Adding your Ghana Card adds a Verified badge that builds client trust.<?php if($user['ghana_card_verified']??0): ?> <span style="color:var(--green);font-weight:700;">✓ Already Verified</span><?php endif; ?></div>
              </div>
            </div>

            <!-- Video Intro -->
            <div class="card">
              <div class="card-title">🎬 Video Introduction</div>
              <div class="card-sub">A short intro video (30–90 seconds) dramatically increases hire rates. Paste a YouTube or Loom URL.</div>
              <div class="form-group">
                <div class="fiw">
                  <span class="fic">🎬</span>
                <input type="url" name="video_intro" id="videoIntroInput" class="fi" value="<?= htmlspecialchars($provider['video_intro_url']??'') ?>" placeholder="https://youtube.com/watch?v=… or https://loom.com/share/…" oninput="previewVideoIntro(this.value)">
                </div>
                <div class="field-hint">YouTube, Loom, or Vimeo links accepted.</div>
              </div>
              <div class="video-intro-preview <?= !empty($provider['video_intro_url']??'')?'show':'' ?>" id="videoIntroPreview">
                <?php $vin_url = $provider['video_intro_url'] ?? ''; if (!empty($vin_url)): ?>
                  <?php $vid = htmlspecialchars($vin_url); ?>
                  <?php if (str_contains($vid,'youtube.com') || str_contains($vid,'youtu.be')): ?>
                    <?php preg_match('/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $vid, $vm); ?>
                    <iframe src="https://www.youtube.com/embed/<?= $vm[1]??'' ?>" allowfullscreen style="width:100%;border-radius:8px;border:none;height:200px;"></iframe>
                  <?php else: ?>
                    <div style="font-size:13px;color:var(--tx-3);padding:14px;">🎬 Video intro saved: <a href="<?= $vid ?>" target="_blank" style="color:var(--cyan);">View</a></div>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn btn-green">💾 Save Basic Info</button>
              </div>
            </div>
          </form>
        </div>

        <!-- ── PROFESSIONAL ── -->
        <div class="tab-pane" id="tab-professional">
          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="section"    value="professional">
            <div class="card">
              <div class="card-title">💼 Professional Details</div>
              <div class="card-sub">Rates, availability, and experience shown to clients.</div>
              <div class="form-row">
                <div class="form-group">
                  <label class="fl">Hourly Rate (GHS)</label>
                  <div class="fiw"><span class="fic">₵</span><input type="number" name="hourly_rate" class="fi" value="<?= htmlspecialchars($provider['hourly_rate']??0) ?>" min="0" step="5" placeholder="80"></div>
                  <div class="field-hint">Set to 0 to display "Negotiable" on your profile.</div>
                </div>
                <div class="form-group">
                  <label class="fl">Availability</label>
                  <select name="availability" class="fs">
                    <option value="full_time"     <?= ($provider['availability']??'')==='full_time'    ?'selected':'' ?>>● Full-time Available</option>
                    <option value="part_time"     <?= ($provider['availability']??'')==='part_time'    ?'selected':'' ?>>◐ Part-time</option>
                    <option value="not_available" <?= ($provider['availability']??'')==='not_available'?'selected':'' ?>>○ Not Available</option>
                  </select>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="fl">Experience Level</label>
                  <select name="experience_level" class="fs">
                    <option value="entry"        <?= ($provider['experience_level']??'')==='entry'       ?'selected':'' ?>>Entry Level (0–2 yrs)</option>
                    <option value="intermediate" <?= ($provider['experience_level']??'')==='intermediate'?'selected':'' ?>>Intermediate (2–5 yrs)</option>
                    <option value="expert"       <?= ($provider['experience_level']??'')==='expert'      ?'selected':'' ?>>Expert (5+ yrs)</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="fl">Typical Response Time</label>
                  <select name="response_time" class="fs">
                    <?php foreach(['Within 1 hour','Within 4 hours','Within 24 hours','Within 2 days'] as $rt): ?>
                    <option <?= ($provider['response_time']??'')===$rt?'selected':'' ?>><?= $rt ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="fl">Languages Spoken</label>
                <input type="text" name="languages" class="fi" value="<?= htmlspecialchars($provider['languages']??'English') ?>" placeholder="English, Twi, French, Hausa">
                <div class="field-hint">Comma-separated working languages.</div>
              </div>
              <div class="form-actions"><button type="submit" class="btn btn-green">💾 Save Professional Details</button></div>
            </div>
            <div class="card">
              <div class="card-title">🔗 Online Presence</div>
              <div class="card-sub">Links build client trust — show off your work.</div>
              <div class="form-group">
                <label class="fl">Portfolio Website</label>
                <div class="fiw"><span class="fic">🌐</span><input type="url" name="portfolio_url" class="fi" value="<?= htmlspecialchars($provider['portfolio_url']??'') ?>" placeholder="https://yoursite.com"></div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="fl">LinkedIn</label>
                  <div class="fiw"><span class="fic">💼</span><input type="url" name="linkedin_url" class="fi" value="<?= htmlspecialchars($provider['linkedin_url']??'') ?>" placeholder="https://linkedin.com/in/you"></div>
                </div>
                <div class="form-group">
                  <label class="fl">GitHub</label>
                  <div class="fiw"><span class="fic">🐙</span><input type="url" name="github_url" class="fi" value="<?= htmlspecialchars($provider['github_url']??'') ?>" placeholder="https://github.com/you"></div>
                </div>
              </div>
              <div class="form-actions"><button type="submit" class="btn btn-green">💾 Save Links</button></div>
            </div>
          </form>
        </div>

        <!-- ── SKILLS ── -->
        <div class="tab-pane" id="tab-skills">
          <form method="POST" id="skillsForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="section"    value="skills">
            <div id="skillHiddenInputs"></div>
            <div class="card">
              <div class="card-title">🛠 Skills & Expertise</div>
              <div class="card-sub">Select up to 30 skills and set proficiency. These help clients find you across all Ghanaian professions.</div>
              <div class="selected-skills-preview" id="sspWrap">
                <div class="ssp-title">✓ Selected (<span id="sspCount">0</span>/30)</div>
                <div class="ssp-tags" id="sspTags"></div>
              </div>
              <div class="skill-search-wrap form-group">
                <span class="fic" style="top:50%;transform:translateY(-50%);">🔍</span>
                <input type="text" id="skillSearch" class="fi" placeholder="Search skills e.g. carpenter, nurse, developer…" autocomplete="off">
              </div>
              <div class="skill-cats">
                <?php foreach($skillsByCat as $cat=>$catSkills): ?>
                <div class="skill-cat-group" data-cat-group>
                  <div class="skill-cat-label"><?= sanitize($cat) ?></div>
                  <div class="skill-chips">
                    <?php foreach($catSkills as $sk): ?>
                    <div class="skc <?= isset($selectedSkills[$sk['id']])?'selected':'' ?>"
                         data-id="<?= $sk['id'] ?>"
                         data-name="<?= htmlspecialchars($sk['name']) ?>"
                         data-prof="<?= htmlspecialchars($selectedSkills[$sk['id']]??'intermediate') ?>"
                         onclick="toggleSkill(this)"><?= sanitize($sk['name']) ?></div>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="form-actions" style="margin-top:20px;">
                <button type="submit" class="btn btn-green">💾 Save Skills</button>
                <span style="font-size:12px;color:var(--tx-3);" id="skillCountDisplay"><?= count($selectedSkills) ?>/30 selected</span>
              </div>
            </div>
          </form>
        </div>

        <!-- ── PORTFOLIO ── -->
        <div class="tab-pane" id="tab-portfolio">
          <?php if (!empty($portfolioItems)): ?>
          <div class="portfolio-grid">
            <?php foreach($portfolioItems as $pi):
              $isVideo = ($pi['item_type']??'image') === 'video';
            ?>
            <div class="port-card">
              <div class="port-thumb">
                <?php if ($isVideo): ?>
                  <?php if (!empty($pi['video_url'])): ?><video src="<?= sanitize($pi['video_url']) ?>" muted loop playsinline preload="metadata"></video><?php else: ?><span>🎬</span><?php endif; ?>
                  <span class="port-type-badge">🎬 Video</span>
                <?php elseif (!empty($pi['image_url'])): ?>
                  <img src="<?= sanitize($pi['image_url']) ?>" alt="<?= sanitize($pi['title']) ?>" loading="lazy">
                <?php else: ?>🖼<?php endif; ?>
              </div>
              <div class="port-body">
                <div class="port-name"><?= sanitize($pi['title']) ?></div>
                <div class="port-desc"><?= sanitize($pi['description']??'') ?></div>
                <div class="port-actions">
                  <?php if (!empty($pi['project_url'])): ?>
                  <a href="<?= sanitize($pi['project_url']) ?>" target="_blank" rel="noopener" class="btn btn-ghost btn-sm">↗ View</a>
                  <?php endif; ?>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="section"    value="delete_portfolio">
                    <input type="hidden" name="item_id"    value="<?= $pi['id'] ?>">
                    <button type="submit" class="btn btn-red btn-sm" onclick="return confirm('Remove this item?')">🗑</button>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div style="text-align:center;padding:36px;border:2px dashed var(--bd2);border-radius:var(--r);color:var(--tx-3);margin-bottom:18px;">
            <div style="font-size:36px;margin-bottom:10px;">🖼</div>
            <div style="font-weight:700;font-family:var(--fm);margin-bottom:4px;">No portfolio items yet</div>
            <div style="font-size:13px;">Add your best work below to impress clients.</div>
          </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data" id="portForm">
            <input type="hidden" name="csrf_token"   value="<?= $csrf ?>">
            <input type="hidden" name="section"      value="portfolio">
            <input type="hidden" name="port_type"    id="portTypeInput" value="image">
            <div class="card">
              <div class="card-title">➕ Add Portfolio Item</div>
              <div class="card-sub">Showcase your best work — images or demo videos. Up to 30 items. Lazy-loaded for performance.</div>
              <div class="port-type-toggle">
                <button type="button" class="ptt-btn active" id="pttImage" onclick="setPortType('image')">🖼 Image</button>
                <button type="button" class="ptt-btn"        id="pttVideo" onclick="setPortType('video')">🎬 Video</button>
              </div>
              <div class="form-group">
                <label class="fl">Project Title <span class="rq">*</span></label>
                <input type="text" name="port_title" class="fi" placeholder="e.g. Kitchen Renovation in East Legon" maxlength="200">
              </div>
              <div class="form-group">
                <label class="fl">Description</label>
                <textarea name="port_desc" class="fta" rows="3" placeholder="Your role, what you did, and the outcome…"></textarea>
              </div>
              <div id="portImageSection">
                <div class="form-group">
                  <label class="fl">Project Screenshot / Image</label>
                  <div class="upload-dropzone" id="portDropzone" onclick="document.getElementById('portImageFile').click()" ondrop="handlePortDrop(event)" ondragover="event.preventDefault()" ondragenter="this.classList.add('dragover')" ondragleave="this.classList.remove('dragover')">
                    <div class="dz-icon">📤</div><h4>Upload Image</h4>
                    <p>JPG, PNG, WebP, GIF · Max 8MB · Click or drag & drop</p>
                  </div>
                  <img id="portImgPreview" class="upload-preview" src="" alt="">
                  <input type="file" id="portImageFile" name="port_image" accept="image/*" style="display:none;" onchange="handlePortImageFile(this)">
                </div>
              </div>
              <div id="portVideoSection" style="display:none;">
                <div class="form-group">
                  <label class="fl">Video URL <span class="rq">*</span></label>
                  <div class="fiw"><span class="fic">🎬</span><input type="url" name="port_video_url" class="fi" placeholder="https://youtube.com/watch?v=…"></div>
                  <div class="field-hint">YouTube, Vimeo, or direct MP4 URL.</div>
                </div>
              </div>
              <div class="form-group">
                <label class="fl">Live / Demo URL</label>
                <div class="fiw"><span class="fic">🔗</span><input type="url" name="port_url" class="fi" placeholder="https://yourproject.com"></div>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn btn-green">➕ Add to Portfolio</button>
                <span style="font-size:12px;color:var(--tx-3);"><?= count($portfolioItems) ?>/30 items used</span>
              </div>
            </div>
          </form>
        </div>

        <!-- ── PRICING PACKAGES ── -->
        <div class="tab-pane" id="tab-packages">
          <?php if (!empty($packages)): ?>
          <div class="pkg-grid">
            <?php
            $pkgIcons=['basic'=>'🌱','standard'=>'⭐','premium'=>'🏆'];
            foreach($packages as $pk):
            ?>
            <div class="pkg-card <?= $pk['tier'] ?>">
              <div class="pkg-tier-icon"><?= $pkgIcons[$pk['tier']]??'📦' ?></div>
              <div class="pkg-name"><?= sanitize($pk['name']) ?></div>
              <div class="pkg-price">₵<?= number_format($pk['price'],0) ?><small> / project</small></div>
              <div class="pkg-delivery">⏱ <?= (int)$pk['delivery_days'] ?> day<?= $pk['delivery_days']!=1?'s':'' ?> delivery</div>
              <div class="pkg-desc"><?= sanitize($pk['description']??'') ?></div>
              <div class="pkg-cta">
                <a href="<?= APP_URL ?>/jobs/hire.php?provider=<?= $userId ?>&pkg=<?= $pk['tier'] ?>" class="btn btn-gold btn-sm" style="justify-content:center;">Hire Me — ₵<?= number_format($pk['price'],0) ?></a>
                <a href="<?= APP_URL ?>/client/messages.php?provider=<?= $userId ?>" class="btn btn-ghost btn-sm" style="justify-content:center;">Request Custom Offer</a>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
          <div style="text-align:center;padding:30px;border:2px dashed var(--bd2);border-radius:var(--r);color:var(--tx-3);margin-bottom:18px;">
            <div style="font-size:32px;margin-bottom:8px;">💰</div>
            <div style="font-weight:700;font-family:var(--fm);margin-bottom:4px;">No pricing packages set</div>
            <div style="font-size:13px;">Set up your Basic, Standard, and Premium packages below to help clients choose the right option.</div>
          </div>
          <?php endif; ?>

          <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="section"    value="packages">
            <div class="card">
              <div class="card-title">💰 Set Your Pricing Packages</div>
              <div class="card-sub">Offer Basic, Standard, and Premium tiers with GHS pricing. Leave name empty to skip a tier.</div>
              <?php
              $pkgDefaults=['basic'=>['name'=>'Basic','price'=>100,'desc'=>'Simple project, core deliverables only.','days'=>3],'standard'=>['name'=>'Standard','price'=>250,'desc'=>'Complete solution with revisions included.','days'=>7],'premium'=>['name'=>'Premium','price'=>500,'desc'=>'Full premium package with priority support.','days'=>14]];
              $pkgMap=[];foreach($packages as $pk) $pkgMap[$pk['tier']]=$pk;
              ?>
              <div class="pkg-edit-grid">
                <?php foreach(['basic','standard','premium'] as $tier):
                  $pd=$pkgMap[$tier]??$pkgDefaults[$tier];
                  $icons=['basic'=>'🌱','standard'=>'⭐','premium'=>'🏆'];
                ?>
                <div class="pkg-edit-col">
                  <div class="pkg-edit-tier"><?= $icons[$tier].' '.ucfirst($tier) ?></div>
                  <div class="form-group">
                    <label class="fl">Package Name</label>
                    <input type="text" name="pkg_<?= $tier ?>_name" class="fi" value="<?= htmlspecialchars($pd['name']??'') ?>" placeholder="e.g. <?= ucfirst($tier) ?>" maxlength="80">
                  </div>
                  <div class="form-group">
                    <label class="fl">Price (GHS)</label>
                    <div class="fiw"><span class="fic">₵</span><input type="number" name="pkg_<?= $tier ?>_price" class="fi" value="<?= htmlspecialchars($pd['price']??0) ?>" min="0" step="10" placeholder="0"></div>
                  </div>
                  <div class="form-group">
                    <label class="fl">Delivery Days</label>
                    <input type="number" name="pkg_<?= $tier ?>_days" class="fi" value="<?= htmlspecialchars($pd['delivery_days']??$pd['days']??7) ?>" min="1" max="365">
                  </div>
                  <div class="form-group">
                    <label class="fl">Description</label>
                    <textarea name="pkg_<?= $tier ?>_desc" class="fta" rows="3" placeholder="What's included…"><?= htmlspecialchars($pd['description']??$pd['desc']??'') ?></textarea>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="form-actions" style="margin-top:18px;">
                <button type="submit" class="btn btn-green">💾 Save Packages</button>
              </div>
            </div>
          </form>
        </div>

        <!-- ── REVIEWS ── -->
        <div class="tab-pane" id="tab-reviews">
          <?php if (empty($reviews)): ?>
          <div style="text-align:center;padding:60px 20px;color:var(--tx-3);">
            <div style="font-size:48px;margin-bottom:14px;">⭐</div>
            <div style="font-family:var(--fm);font-size:20px;font-weight:700;margin-bottom:6px;">No reviews yet</div>
            <p style="font-size:14px;max-width:320px;margin:0 auto;">Complete jobs and deliver great work to earn client reviews.</p>
          </div>
          <?php else: ?>
          <?php foreach($reviews as $rev): $rv=(float)$rev['rating_overall']; ?>
          <div class="review-card">
            <div class="rv-header">
              <div class="rv-av">
                <?php if (!empty($rev['avatar'])): ?><img src="<?= sanitize($rev['avatar']) ?>" alt="" loading="lazy">
                <?php else: echo strtoupper(substr($rev['first_name'],0,1).substr($rev['last_name'],0,1)); endif; ?>
              </div>
              <div style="flex:1;min-width:0;">
                <div class="rv-name"><?= sanitize($rev['first_name'].' '.$rev['last_name']) ?></div>
                <div class="rv-prof"><?= ucfirst($rev['reviewer_role']??'Client') ?></div>
                <div class="rv-job">📋 <?= sanitize($rev['job_title']) ?></div>
                <div class="rv-stars"><?php for($s=1;$s<=5;$s++) echo $rv>=$s?'★':($rv>=$s-.5?'✦':'☆'); ?></div>
              </div>
              <div class="rv-date"><?= timeAgo($rev['created_at']) ?></div>
            </div>
            <div class="rv-verified">✓ Verified Purchase</div>
            <?php if ($rev['comment']): ?>
            <div class="rv-text">"<?= sanitize($rev['comment']) ?>"</div>
            <?php endif; ?>
            <div class="rv-breakdown">
              <?php foreach(['Communication'=>$rev['rating_communication'],'Quality'=>$rev['rating_quality'],'Professionalism'=>$rev['rating_professionalism'],'Timeliness'=>$rev['rating_timeliness']] as $bl=>$bv): if((float)$bv>0): ?>
              <div class="rbd"><?= $bl ?>: <span class="stars"><?php $vv=round($bv);for($s=1;$s<=5;$s++) echo $vv>=$s?'★':'☆'; ?></span><?= number_format($bv,1) ?></div>
              <?php endif; endforeach; ?>
            </div>
            <div class="rv-actions">
              <div class="rv-helpful" onclick="markHelpful(this,<?= $rev['id']??0 ?>)">
                👍 Helpful (<?= (int)($rev['helpful_count']??0) ?>)
              </div>
              <div class="rv-report" onclick="reportReview(<?= $rev['id']??0 ?>)">🚩 Report</div>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>

      </div><!-- /right -->
    </div><!-- /pg -->
  </div><!-- /content -->
</div><!-- /main -->

<!-- ══════ CROP MODAL ══════ -->
<div class="crop-modal" id="cropModal">
  <div class="crop-box">
    <div class="crop-header">
      <h3>✂️ Crop Your Photo</h3>
      <button type="button" class="crop-close" onclick="closeCropModal()">✕</button>
    </div>
    <div class="crop-canvas-wrap"><img id="cropImage" src="" alt="Crop preview"></div>
    <div class="crop-controls">
      <div class="crop-zoom-btns">
        <button type="button" class="crop-zoom-btn" onclick="cropperInst?.zoom(0.1)">＋</button>
        <button type="button" class="crop-zoom-btn" onclick="cropperInst?.zoom(-0.1)">－</button>
        <button type="button" class="crop-zoom-btn" onclick="cropperInst?.rotate(-90)">↺</button>
        <button type="button" class="crop-zoom-btn" onclick="cropperInst?.rotate(90)">↻</button>
      </div>
      <div class="crop-footer">
        <button type="button" class="btn btn-ghost" onclick="closeCropModal()">Cancel</button>
        <button type="button" class="btn btn-green" onclick="applyCrop()">✓ Apply Crop</button>
      </div>
    </div>
  </div>
</div>

<!-- Subscription banner -->
<?php if ($showSubBanner): ?>
<div class="sub-banner" id="subBanner">
  <div class="sub-banner-text">
    <strong>🚀 You've used your 3 free job slots!</strong> Upgrade to <strong>Verified (₵49/mo)</strong> or <strong>Premium (₵99/mo)</strong> to keep applying and get top placement.
  </div>
  <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
    <a href="<?= APP_URL ?>/provider/upgrade.php" class="btn btn-gold" style="padding:8px 18px;font-size:13px;">⭐ Upgrade Now</a>
    <span class="sub-close" id="subClose">×</span>
  </div>
</div>
<?php endif; ?>

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

/* ══ TABS ══ */
document.querySelectorAll('.tab-btn').forEach(btn=>{
  btn.addEventListener('click',function(){
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    this.classList.add('active');
    document.getElementById('tab-'+this.dataset.tab).classList.add('active');
  });
});
<?php if($success): ?>
const jumpTab='<?= match($success){'basic'=>'basic','professional'=>'professional','skills'=>'skills','packages'=>'packages','portfolio','portfolio_deleted'=>'portfolio',default=>'basic'} ?>';
document.querySelector(`[data-tab="${jumpTab}"]`)?.click();
<?php endif; ?>

/* ══ COMPLETENESS BAR ══ */
setTimeout(()=>{ document.getElementById('compFill').style.width='<?= $completeness ?>%'; },300);

/* ══ CHAR COUNTERS ══ */
[['bioInp','bioCnt'],['tagInp','tagCnt']].forEach(([a,b])=>{
  const e=document.getElementById(a),c=document.getElementById(b);
  if(e&&c) e.addEventListener('input',()=>c.textContent=e.value.length);
});

/* ══ LIVE PREVIEW ══ */
document.getElementById('fnInp')?.addEventListener('input',function(){
  const ln=document.getElementById('lnInp')?.value||'';
  document.getElementById('pcName').textContent=(this.value+' '+ln).trim()||'Your Name';
});
document.getElementById('lnInp')?.addEventListener('input',function(){
  const fn=document.getElementById('fnInp')?.value||'';
  document.getElementById('pcName').textContent=(fn+' '+this.value).trim()||'Your Name';
});
document.getElementById('tagInp')?.addEventListener('input',function(){
  document.getElementById('pcTag').textContent=this.value||'Add your professional tagline';
});

/* ══ VIDEO INTRO PREVIEW ══ */
function previewVideoIntro(url){
  const wrap=document.getElementById('videoIntroPreview');
  if(!url){wrap.classList.remove('show');wrap.innerHTML='';return;}
  wrap.classList.add('show');
  const ytMatch=url.match(/(?:v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
  if(ytMatch){
    wrap.innerHTML=`<iframe src="https://www.youtube.com/embed/${ytMatch[1]}" allowfullscreen style="width:100%;border-radius:8px;border:none;height:200px;"></iframe>`;
  } else {
    wrap.innerHTML=`<div style="font-size:13px;color:var(--tx-3);padding:14px;">🎬 Video intro will be saved: <a href="${url}" target="_blank" style="color:var(--cyan);">Preview ↗</a></div>`;
  }
}

/* ══ AVATAR UPLOAD + CROP ══ */
let cropperInst = null;
function triggerAvatarUpload(){ document.getElementById('avatarFileInput').click(); }
function handleAvatarDrop(e){
  e.preventDefault();
  document.getElementById('avatarDropArea').classList.remove('dragover');
  const file=e.dataTransfer?.files?.[0];
  if(file&&file.type.startsWith('image/')) openCropper(file);
}
function handleAvatarFile(input){ if(input.files?.[0]) openCropper(input.files[0]); }
function openCropper(file){
  if(file.size>3145728){ showToast('File too large','Maximum avatar size is 3MB.','error'); return; }
  const reader=new FileReader();
  reader.onload=e=>{
    const img=document.getElementById('cropImage');
    img.src=e.target.result;
    document.getElementById('cropModal').classList.add('open');
    document.body.style.overflow='hidden';
    const init=()=>{
      if(typeof Cropper==='undefined'){setTimeout(init,100);return;}
      if(cropperInst){cropperInst.destroy();cropperInst=null;}
      cropperInst=new Cropper(img,{aspectRatio:1,viewMode:1,dragMode:'move',autoCropArea:0.9,restore:false,guides:true,center:true,highlight:false,cropBoxMovable:true,cropBoxResizable:true,toggleDragModeOnDblclick:false});
    };
    setTimeout(init,50);
  };
  reader.readAsDataURL(file);
}
function applyCrop(){
  if(!cropperInst){closeCropModal();return;}
  const canvas=cropperInst.getCroppedCanvas({width:400,height:400,imageSmoothingEnabled:true,imageSmoothingQuality:'high'});
  const dataURL=canvas.toDataURL('image/jpeg',0.88);
  document.getElementById('avatarCroppedData').value=dataURL;
  ['auaPreviewImg','pcAvatarImg'].forEach(id=>{
    let img=document.getElementById(id);
    if(!img){img=document.createElement('img');img.id=id;img.style.cssText='width:100%;height:100%;object-fit:cover;';const wrap=id==='auaPreviewImg'?document.getElementById('auaPreview'):document.getElementById('pcAvatar');if(wrap){wrap.querySelector('span')?.remove();wrap.insertBefore(img,wrap.querySelector('.pc-avatar-overlay')||wrap.firstChild);}}
    img.src=dataURL;
  });
  closeCropModal();
  showToast('Photo ready','Click Save Basic Info to apply your photo.','info');
}
function closeCropModal(){
  document.getElementById('cropModal').classList.remove('open');
  document.body.style.overflow='';
  if(cropperInst){cropperInst.destroy();cropperInst=null;}
}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeCropModal();});

/* ══ SKILLS SYSTEM ══ */
let selMap={};
document.querySelectorAll('.skc.selected').forEach(c=>{
  selMap[c.dataset.id]={name:c.dataset.name,prof:c.dataset.prof||'intermediate'};
});
syncSkills();

function toggleSkill(chip){
  const id=chip.dataset.id,name=chip.dataset.name,prof=chip.dataset.prof||'intermediate';
  if(selMap[id]){delete selMap[id];chip.classList.remove('selected');}
  else{
    if(Object.keys(selMap).length>=30){showToast('Limit reached','Maximum 30 skills allowed.','error');return;}
    selMap[id]={name,prof};chip.classList.add('selected');
  }
  syncSkills();
}
function syncSkills(){
  const count=Object.keys(selMap).length;
  document.getElementById('sspCount').textContent=count;
  document.getElementById('skillCountDisplay').textContent=count+'/30 selected';
  const wrap=document.getElementById('sspWrap');
  wrap.classList.toggle('has-skills',count>0);
  const tags=document.getElementById('sspTags');tags.innerHTML='';
  Object.entries(selMap).forEach(([id,{name,prof}])=>{
    const d=document.createElement('div');d.className='ssp-tag';
    d.innerHTML=`<span>${name}</span>`+
      `<select data-id="${id}" onchange="updateProf(this)">`+
      ['beginner','intermediate','expert'].map(p=>`<option value="${p}"${p===prof?' selected':''}>${p.charAt(0).toUpperCase()+p.slice(1)}</option>`).join('')+
      `</select><span class="rm" onclick="removeSkill('${id}')">✕</span>`;
    tags.appendChild(d);
  });
  const si=document.getElementById('skillHiddenInputs');si.innerHTML='';
  Object.entries(selMap).forEach(([id,{prof}])=>{
    si.innerHTML+=`<input type="hidden" name="skill_ids[]" value="${id}"><input type="hidden" name="proficiencies[${id}]" value="${prof}">`;
  });
}
function updateProf(sel){
  const id=sel.dataset.id;
  if(selMap[id]){selMap[id].prof=sel.value;const c=document.querySelector(`.skc[data-id="${id}"]`);if(c)c.dataset.prof=sel.value;syncSkills();}
}
function removeSkill(id){
  delete selMap[id];
  const c=document.querySelector(`.skc[data-id="${id}"]`);if(c)c.classList.remove('selected');
  syncSkills();
}
document.getElementById('skillSearch')?.addEventListener('input',function(){
  const q=this.value.toLowerCase().trim();
  document.querySelectorAll('.skc').forEach(c=>c.style.display=(!q||c.dataset.name.toLowerCase().includes(q))?'':'none');
  document.querySelectorAll('[data-cat-group]').forEach(g=>{
    g.style.display=[...g.querySelectorAll('.skc')].some(c=>c.style.display!=='none')?'':'none';
  });
});

/* ══ PORTFOLIO ══ */
function setPortType(type){
  document.getElementById('portTypeInput').value=type;
  document.getElementById('portImageSection').style.display=type==='image'?'':'none';
  document.getElementById('portVideoSection').style.display=type==='video'?'':'none';
  document.getElementById('pttImage').classList.toggle('active',type==='image');
  document.getElementById('pttVideo').classList.toggle('active',type==='video');
}
function handlePortDrop(e){
  e.preventDefault();
  document.getElementById('portDropzone').classList.remove('dragover');
  const file=e.dataTransfer?.files?.[0];
  if(file&&file.type.startsWith('image/')){
    const inp=document.getElementById('portImageFile');
    const dt=new DataTransfer();dt.items.add(file);inp.files=dt.files;
    handlePortImageFile(inp);
  }
}
function handlePortImageFile(input){
  const file=input.files?.[0];if(!file)return;
  if(file.size>8388608){showToast('Too large','Max image size is 8MB.','error');return;}
  const reader=new FileReader();
  reader.onload=e=>{
    const prev=document.getElementById('portImgPreview');
    prev.src=e.target.result;prev.classList.add('visible');
  };
  reader.readAsDataURL(file);
  const dz=document.getElementById('portDropzone');
  dz.innerHTML=`<div class="dz-icon">✅</div><h4>${file.name}</h4><p>${(file.size/1024/1024).toFixed(1)} MB</p>`;
}

/* ══ VIDEO HOVER PLAY ══ */
document.querySelectorAll('.port-thumb video').forEach(v=>{
  v.addEventListener('mouseenter',()=>v.play().catch(()=>{}));
  v.addEventListener('mouseleave',()=>{v.pause();v.currentTime=0;});
});

/* ══ REVIEW ACTIONS ══ */
function markHelpful(el,id){
  el.style.color='var(--green)';
  el.innerHTML='👍 Helpful (Marked)';
  showToast('Thanks!','Review marked as helpful.','success');
}
function reportReview(id){
  if(confirm('Report this review as inappropriate?'))
    showToast('Reported','Our team will review this within 24 hours.','info');
}

/* ══ SHARE PROFILE ══ */
function copyProfileLink(url){
  navigator.clipboard.writeText(url).then(()=>showToast('Copied!','Profile link copied to clipboard.','success'));
}

/* ══ SUBSCRIPTION BANNER ══ */
<?php if ($showSubBanner): ?>
setTimeout(()=>{
  const b=document.getElementById('subBanner');
  if(b&&!sessionStorage.getItem('subDismissed'))b.classList.add('show');
},3500);
document.getElementById('subClose')?.addEventListener('click',()=>{
  document.getElementById('subBanner').classList.remove('show');
  sessionStorage.setItem('subDismissed','1');
});
<?php endif; ?>

/* ══ FORM SUBMIT STATES ══ */
document.querySelectorAll('form').forEach(f=>{
  f.addEventListener('submit',function(){
    const b=this.querySelector('[type="submit"]');
    if(b){b.disabled=true;b.innerHTML='⏳ Saving…';}
  });
});

/* ══ SUCCESS ALERT AUTO-HIDE ══ */
setTimeout(()=>{
  const a=document.getElementById('sAlert');
  if(a){a.style.transition='opacity .5s';a.style.opacity='0';setTimeout(()=>a.remove(),500);}
},4000);

/* ══ TOAST ══ */
const TOAST_ICONS={success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
function showToast(title,msg,type='info',d=4200){
  const c=document.getElementById('toast-c');
  const t=document.createElement('div');
  t.className=`toast ${type}`;
  t.innerHTML=`<div class="t-ico">${TOAST_ICONS[type]||'ℹ️'}</div><div class="t-bod"><div class="t-ttl">${title}</div><div class="t-msg">${msg}</div></div><div class="t-cls" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(48px)';setTimeout(()=>t.remove(),360);},d);
}
<?php if($success&&isset($msgs[$success])): ?>
showToast('Saved!','<?= addslashes($msgs[$success]) ?>','success');
<?php endif; ?>
<?php if(!empty($errors)): ?>
showToast('Error','<?= addslashes(implode('. ',$errors)) ?>','error',7000);
<?php endif; ?>
</script>
</body>
</html>