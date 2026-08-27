<?php
/**
 * GigGhana — client/messages.php  (v4 — theme toggle + unread badges + message preview added)
 *
 * ADDITIONS (v4):
 *   A. Dark/Light theme toggle button in top nav (persists via localStorage + cookie)
 *   B. WhatsApp-style unread count badge (green circle) on thread items
 *   C. Bold name + bold preview text when conversation has unread messages
 *
 * Original fixes (v3) — untouched:
 *   1. Online dot = real DB last_seen (≤5 min), not availability field
 *   2. Voice notes = MediaRecorder API → upload → playable audio bubble
 *   3. Call button = fetches real phone from DB → opens tel: link
 *   4. Typing indicator = real AJAX poll on conversation_status table, NOT setTimeout
 *   5. Read ticks = 100% from DB (is_delivered / is_read), no fake simulation
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('client');

$userId       = (int)$_SESSION['user_id'];
$user         = getUserById($userId);
$activeConvId = (int)($_GET['conv'] ?? 0);

/* ADDITION A: Read theme preference from cookie (same as dashboard) */
$isLight = ($_COOKIE['gg_theme'] ?? '') === 'light';

/* ── ?start=providerUserId → find/create conversation & redirect ── */
if (!$activeConvId && isset($_GET['start'])) {
    $otherId = (int)$_GET['start'];
    $jobId   = isset($_GET['job']) ? (int)$_GET['job'] : null;
    if ($otherId && $otherId !== $userId) {
        try {
            $db = getDB();
            $u1 = min($userId, $otherId);
            $u2 = max($userId, $otherId);
            $st = $db->prepare(
                "SELECT id FROM conversations WHERE user1_id=? AND user2_id=?" .
                ($jobId ? " AND job_id=?" : "") . " LIMIT 1"
            );
            $st->execute($jobId ? [$u1,$u2,$jobId] : [$u1,$u2]);
            $cid = $st->fetchColumn();
            if (!$cid) {
                $db->prepare(
                    "INSERT INTO conversations (uuid,user1_id,user2_id,job_id) VALUES (?,?,?,?)"
                )->execute([generateUUID(),$u1,$u2,$jobId]);
                $cid = $db->lastInsertId();
            }
            header("Location: " . APP_URL . "/client/messages.php?conv={$cid}");
            exit;
        } catch (Exception $e) { error_log($e->getMessage()); }
    }
}

try {
    $db = getDB();

    /* ── All conversations ── */
    $stC = $db->prepare("
        SELECT
            c.id, c.job_id, c.last_message_at, c.last_message_preview,
            CASE WHEN c.user1_id=:me  THEN c.unread_count_user1
                                       ELSE c.unread_count_user2 END AS my_unread,
            CASE WHEN c.user1_id=:me2 THEN c.user2_id
                                       ELSE c.user1_id             END AS other_id,
            u.first_name, u.last_name, u.avatar,
            (CASE WHEN u.last_seen IS NOT NULL
                   AND TIMESTAMPDIFF(SECOND, u.last_seen, NOW()) <= 300
                  THEN 1 ELSE 0 END)          AS is_online,
            TIMESTAMPDIFF(SECOND, u.last_seen, NOW()) AS last_seen_secs,
            p.tagline, p.rating_avg, p.is_verified, p.experience_level,
            cat.name  AS profession,
            j.title   AS job_title,
            j.status  AS job_status
        FROM conversations c
        JOIN  users      u   ON u.id  = CASE WHEN c.user1_id=:me3
                                             THEN c.user2_id ELSE c.user1_id END
        LEFT JOIN providers  p   ON p.user_id  = u.id
        LEFT JOIN jobs       j   ON j.id       = c.job_id
        LEFT JOIN categories cat ON cat.id     = j.category_id
        WHERE c.user1_id=:me4 OR c.user2_id=:me5
        ORDER BY COALESCE(c.last_message_at, c.created_at) DESC
    ");
    $stC->execute([':me'=>$userId,':me2'=>$userId,':me3'=>$userId,':me4'=>$userId,':me5'=>$userId]);
    $conversations = $stC->fetchAll();
    $totalUnread   = (int)array_sum(array_column($conversations, 'my_unread'));

    /* ── Active conversation ── */
    $messages = []; $activeConv = null; $otherUser = null; $activeJob = null;

    if ($activeConvId) {
        $stV = $db->prepare(
            "SELECT * FROM conversations WHERE id=? AND (user1_id=? OR user2_id=?) LIMIT 1"
        );
        $stV->execute([$activeConvId,$userId,$userId]);
        $activeConv = $stV->fetch();

        if ($activeConv) {
            $otherId = ((int)$activeConv['user1_id'] === $userId)
                ? (int)$activeConv['user2_id'] : (int)$activeConv['user1_id'];

            $stO = $db->prepare("
                SELECT u.id, u.first_name, u.last_name, u.avatar, u.location, u.phone,
                       u.last_seen,
                       (CASE WHEN u.last_seen IS NOT NULL
                              AND TIMESTAMPDIFF(SECOND, u.last_seen, NOW()) <= 300
                             THEN 1 ELSE 0 END) AS is_online,
                       TIMESTAMPDIFF(SECOND, u.last_seen, NOW()) AS last_seen_secs,
                       p.tagline, p.rating_avg, p.rating_count,
                       p.completed_jobs, p.is_verified, p.experience_level,
                       (SELECT cat.name FROM proposals pr
                        JOIN jobs jj ON jj.id=pr.job_id
                        LEFT JOIN categories cat ON cat.id=jj.category_id
                        JOIN providers pv ON pv.id=pr.provider_id
                        WHERE pv.user_id=u.id AND jj.client_id=:cid
                        ORDER BY pr.created_at DESC LIMIT 1) AS profession
                FROM users u
                LEFT JOIN providers p ON p.user_id=u.id
                WHERE u.id=:oid LIMIT 1
            ");
            $stO->execute([':cid'=>$userId,':oid'=>$otherId]);
            $otherUser = $stO->fetch();

            if ($activeConv['job_id']) {
                $stJ = $db->prepare("
                    SELECT j.id, j.title, j.status, j.budget_min, j.budget_max,
                           j.budget_type, c.name AS cat_name
                    FROM jobs j LEFT JOIN categories c ON c.id=j.category_id
                    WHERE j.id=? LIMIT 1
                ");
                $stJ->execute([$activeConv['job_id']]);
                $activeJob = $stJ->fetch();
            }

            $isU1      = ((int)$activeConv['user1_id'] === $userId);
            $unreadCol = $isU1 ? 'unread_count_user1' : 'unread_count_user2';
            $db->prepare(
                "UPDATE messages SET is_read=1, is_delivered=1
                 WHERE conversation_id=? AND sender_id!=? AND is_read=0"
            )->execute([$activeConvId,$userId]);
            $db->prepare("UPDATE conversations SET {$unreadCol}=0 WHERE id=?")->execute([$activeConvId]);

            $stM = $db->prepare("
                SELECT m.id, m.sender_id, m.content, m.message_type,
                       m.file_url, m.file_name, m.file_size,
                       m.is_read, m.is_delivered, m.reply_to_id, m.created_at,
                       rm.content AS reply_content, ru.first_name AS reply_fname
                FROM messages m
                LEFT JOIN messages rm ON rm.id=m.reply_to_id
                LEFT JOIN users    ru ON ru.id=rm.sender_id
                WHERE m.conversation_id=? AND m.is_deleted=0
                ORDER BY m.created_at ASC
            ");
            $stM->execute([$activeConvId]);
            $messages = $stM->fetchAll();
        }
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    $conversations=[]; $messages=[]; $activeConv=null;
    $otherUser=null; $activeJob=null; $totalUnread=0;
}

$csrf     = generateCSRF();
$myInit   = strtoupper(substr($user['first_name']??'M',0,1).substr($user['last_name']??'',0,1));
$myAvatar = $user['avatar'] ?? '';

function tFmt(string $ts): string {
    $d = time() - strtotime($ts);
    if ($d < 55)    return 'now';
    if ($d < 3600)  return floor($d/60).'m';
    if ($d < 86400) return date('g:i A',strtotime($ts));
    if ($d < 604800)return date('D',strtotime($ts));
    return date('M j',strtotime($ts));
}
function lastSeenLabel(?string $ts, bool $isOnline): string {
    if ($isOnline) return 'Online now';
    if (!$ts)      return 'Offline';
    $d = time() - strtotime($ts);
    if ($d < 60)   return 'Active just now';
    if ($d < 3600) return 'Active '.floor($d/60).'m ago';
    if ($d < 86400)return 'Active '.date('g:i A',strtotime($ts));
    return 'Active '.date('M j',strtotime($ts));
}
function szFmt(int $b): string {
    if ($b<1024)    return $b.' B';
    if ($b<1048576) return round($b/1024,1).' KB';
    return round($b/1048576,1).' MB';
}
function fIcon(string $n): array {
    $e = strtolower(pathinfo($n,PATHINFO_EXTENSION));
    return match($e){
        'pdf'              =>['📄','fi-pdf'],
        'doc','docx'       =>['📝','fi-doc'],
        'xls','xlsx'       =>['📊','fi-xls'],
        'zip','rar'        =>['📦','fi-zip'],
        'jpg','jpeg','png',
        'gif','webp'       =>['🖼','fi-img'],
        'mp4','mov'        =>['🎬','fi-vid'],
        'mp3','wav','webm','ogg'=>['🎵','fi-aud'],
        default            =>['📁','fi-def'],
    };
}
function starsHtml(float $r): string {
    $o='';
    for($i=1;$i<=5;$i++)
        $o.=$r>=$i?'<span class="s1">★</span>':($r>=$i-.5?'<span class="s2">★</span>':'<span class="s0">★</span>');
    return $o;
}
function ini2(string $f,string $l):string{ return strtoupper(substr($f,0,1).substr($l,0,1)); }

$profMap=[
    'Web Development'=>'Web Developer','Mobile Apps'=>'App Developer',
    'Graphic Design'=>'Graphic Designer','Digital Marketing'=>'Marketer',
    'Skilled Trades'=>'Tradesperson','Health & Wellness'=>'Health Worker',
    'Education & Tutoring'=>'Tutor','Hospitality'=>'Hospitality Pro',
    'Construction'=>'Builder','Business Services'=>'Consultant',
    'Agriculture'=>'Agri Specialist','Creative Arts'=>'Creative Pro',
];
function profLabel(string $raw,array $map):string{ return $map[$raw] ?? $raw; }

$oInit       = $otherUser ? ini2($otherUser['first_name'],$otherUser['last_name']) : '?';
$oIsOnline   = (bool)($otherUser['is_online'] ?? false);
$oPhone      = $otherUser['phone'] ?? '';
$oLastSeen   = lastSeenLabel($otherUser['last_seen']??null, $oIsOnline);
$oProfession = $otherUser ? profLabel($otherUser['profession']??$otherUser['tagline']??'Freelancer',$profMap) : '';
$oRating     = (float)($otherUser['rating_avg'] ?? 0);
$lastMsgId   = !empty($messages) ? (int)end($messages)['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,viewport-fit=cover">
<meta name="theme-color" content="#0C0E14">
<title>Messages — GigGhana</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0C0E14;--s1:#111520;--s2:#181C28;--s3:#1F2436;--s4:#252B3E;
  --glass:rgba(24,28,40,.86);
  --cyan:#00D4C8;--cyan-d:#00B8AD;--cyan-glo:rgba(0,212,200,.18);
  --coral:#FF6B4A;--coral-d:#E85A39;--coral-glo:rgba(255,107,74,.18);
  --violet:#7C6FF7;--teal:#1FD9A0;--amber:#F7B731;--red:#FF4D6D;--blue:#4E9EFF;
  --tx:#F2F4F8;--tx-2:#9BA8BF;--tx-3:#5C6A85;--tx-4:#3A4560;
  --bd:rgba(255,255,255,.065);--bd2:rgba(255,255,255,.13);
  --fh:'Plus Jakarta Sans',sans-serif;--fb:'DM Sans',sans-serif;
  --r:16px;--rs:10px;--e:all .24s cubic-bezier(.4,0,.2,1);
  --nav-h:62px;--snav-w:58px;--panel-w:322px;
}

/* ══════════════════════════════════════════════
   ADDITION A — LIGHT MODE TOKENS
   Mirrors dashboard.php .lm so the cookie works
   across both pages seamlessly.
══════════════════════════════════════════════ */
.lm{
  --bg:#F3F5FA;--s1:#EAEEF7;--s2:#E0E6F2;--s3:#D4DCEE;--s4:#C8D2E8;
  --glass:rgba(234,238,247,.92);
  --cyan:#009E95;--cyan-d:#007870;--cyan-glo:rgba(0,158,149,.14);
  --coral:#E8512B;--coral-d:#C43C1C;--coral-glo:rgba(232,81,43,.14);
  --violet:#5B4FD9;--teal:#0DAF80;--amber:#D4980A;--red:#D63050;
  --tx:#0D1220;--tx-2:#344060;--tx-3:#6B7A99;--tx-4:#9BA8BF;
  --bd:rgba(30,40,80,.09);--bd2:rgba(30,40,80,.18);
}
.lm .topnav     { background:rgba(234,238,247,.97); border-bottom-color:var(--bd); }
.lm .snav       { background:var(--s1); border-right-color:var(--bd); }
.lm .thread-panel{ background:var(--s1); border-right-color:var(--bd); }
.lm .chat-panel { background:var(--bg); }
.lm .chat-head  { background:rgba(234,238,247,.95); }
.lm .chat-input { background:rgba(234,238,247,.97); }
.lm .titem:hover{ background:rgba(0,0,0,.04); }
.lm .titem.on   { background:rgba(0,158,149,.07); }
.lm .bub.recv   { background:var(--s3); }
.lm .bub.sent   { background:linear-gradient(135deg,rgba(0,158,149,.18),rgba(13,175,128,.12)); border-color:rgba(0,158,149,.22); }
.lm .filebub    { background:var(--s4); }
.lm .sbox       { background:rgba(0,0,0,.06); }
.lm .msgbox     { background:rgba(0,0,0,.06); }
.lm .icon-btn   { background:rgba(0,0,0,.05); }
.lm .attbtn     { background:rgba(0,0,0,.05); }
.lm .ctxmenu    { background:var(--s2); }
.lm .epick      { background:var(--s2); }
.lm .ftab.on    { background:rgba(0,158,149,.1); border-color:rgba(0,158,149,.28); }
.lm .theme-btn  { background:rgba(0,0,0,.05); }
/* unread badge stays coral/teal in both modes */
.lm .tiunread   { background:var(--teal); color:#000; }
/* ── end light mode ── */

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html,body{height:100%;overflow:hidden;}
body{background:var(--bg);color:var(--tx);font-family:var(--fb);font-size:14.5px;
     line-height:1.65;-webkit-font-smoothing:antialiased;display:flex;flex-direction:column;
     transition:background .3s,color .3s;}
img{display:block;max-width:100%;}a{text-decoration:none;color:inherit;}
button{font-family:var(--fb);cursor:pointer;}
::-webkit-scrollbar{width:3px;height:3px;}::-webkit-scrollbar-track{background:transparent;}
::-webkit-scrollbar-thumb{background:var(--s4);border-radius:2px;}

/* NAV */
.topnav{height:var(--nav-h);padding:0 18px;flex-shrink:0;z-index:300;
  display:flex;align-items:center;justify-content:space-between;
  background:rgba(12,14,20,.92);backdrop-filter:blur(28px);
  border-bottom:1px solid var(--bd);
  transition:background .3s,border-color .3s;}
.nav-logo{display:flex;align-items:center;gap:9px;text-decoration:none;}
.logo-mark{width:34px;height:34px;border-radius:9px;
  background:linear-gradient(135deg,var(--cyan),var(--teal));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-weight:900;font-size:15px;color:#000;}
.logo-txt{font-family:var(--fh);font-size:18px;font-weight:900;}
.logo-txt span{color:var(--cyan);}
.nav-links{display:flex;gap:3px;}
.navlink{padding:7px 13px;border-radius:50px;font-size:12.5px;font-weight:700;
  font-family:var(--fh);color:var(--tx-3);transition:var(--e);}
.navlink:hover{background:rgba(255,255,255,.055);color:var(--tx);}
.navlink.on{background:rgba(0,212,200,.1);color:var(--cyan);}
.nav-r{display:flex;align-items:center;gap:8px;}
.icon-btn{width:36px;height:36px;border-radius:var(--rs);background:rgba(255,255,255,.04);
  border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:var(--e);font-size:15px;color:var(--tx-2);position:relative;}
.icon-btn:hover{background:rgba(255,255,255,.08);color:var(--tx);}
.pip{position:absolute;top:6px;right:6px;width:7px;height:7px;border-radius:50%;
  background:var(--coral);border:2px solid var(--bg);animation:pipA 2s ease-in-out infinite;}
@keyframes pipA{0%,100%{box-shadow:0 0 0 0 rgba(255,107,74,.5);}50%{box-shadow:0 0 0 4px rgba(255,107,74,0);}}
.uchip{display:flex;align-items:center;gap:7px;background:rgba(255,255,255,.04);
  border:1px solid var(--bd);border-radius:50px;padding:3px 12px 3px 4px;
  font-size:12.5px;font-weight:700;font-family:var(--fh);transition:var(--e);}
.uchip:hover{background:rgba(255,255,255,.08);}
.uchip-av{width:26px;height:26px;border-radius:50%;overflow:hidden;
  background:linear-gradient(135deg,var(--violet),var(--cyan));
  display:flex;align-items:center;justify-content:center;
  font-size:10px;font-weight:800;color:#fff;font-family:var(--fh);}
.uchip-av img{width:100%;height:100%;object-fit:cover;}

/* ════════════════════════════════
   ADDITION A — THEME TOGGLE BUTTON
   Sits in .nav-r before the bell icon
════════════════════════════════ */
.theme-btn{
  width:36px;height:36px;border-radius:var(--rs);
  background:rgba(255,255,255,.04);border:1px solid var(--bd);
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;transition:var(--e);font-size:16px;
  color:var(--tx-2);line-height:1;
}
.theme-btn:hover{
  background:rgba(255,255,255,.08);
  color:var(--tx);
  transform:scale(1.08) rotate(12deg);
}

/* APP */
.app{flex:1;display:flex;overflow:hidden;height:calc(100vh - var(--nav-h));}

/* ICON SIDEBAR */
.snav{width:var(--snav-w);border-right:1px solid var(--bd);background:var(--s1);
  display:flex;flex-direction:column;align-items:center;padding:10px 0;gap:3px;flex-shrink:0;
  transition:background .3s,border-color .3s;}
.sni{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;
  justify-content:center;font-size:17px;cursor:pointer;transition:var(--e);
  text-decoration:none;color:var(--tx-3);position:relative;}
.sni:hover{background:rgba(255,255,255,.06);}
.sni.on{background:rgba(0,212,200,.12);color:var(--cyan);}
.sni-dot{position:absolute;top:5px;right:5px;width:7px;height:7px;border-radius:50%;
  background:var(--coral);border:2px solid var(--s1);}
.sn-gap{flex:1;}
.sn-me{width:40px;height:40px;border-radius:50%;overflow:hidden;margin-bottom:4px;
  background:linear-gradient(135deg,var(--violet),var(--cyan));
  border:2px solid rgba(0,212,200,.3);
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-size:12px;font-weight:800;color:#fff;cursor:pointer;}
.sn-me img{width:100%;height:100%;object-fit:cover;}

/* THREAD PANEL */
.thread-panel{width:var(--panel-w);border-right:1px solid var(--bd);
  background:var(--s1);display:flex;flex-direction:column;flex-shrink:0;overflow:hidden;
  transition:background .3s,border-color .3s;}
.tp-head{padding:14px 15px 11px;border-bottom:1px solid var(--bd);flex-shrink:0;}
.tp-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:11px;}
.tp-title{font-family:var(--fh);font-size:18px;font-weight:900;}
.ubadge{background:var(--coral);color:#fff;font-size:10px;font-weight:800;
  padding:2px 8px;border-radius:50px;font-family:var(--fh);}
.sbox{display:flex;align-items:center;gap:8px;background:rgba(0,0,0,.28);
  border:1.5px solid var(--bd);border-radius:var(--rs);padding:8px 12px;
  margin-bottom:10px;transition:var(--e);}
.sbox:focus-within{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-glo);}
.sbox input{flex:1;background:transparent;border:none;outline:none;
  color:var(--tx);font-size:13px;font-family:var(--fb);}
.sbox input::placeholder{color:var(--tx-3);}
.ftabs{display:flex;gap:5px;padding:0 15px 10px;flex-shrink:0;}
.ftab{padding:5px 12px;border-radius:50px;font-size:11.5px;font-weight:700;
  font-family:var(--fh);color:var(--tx-3);background:transparent;
  border:1.5px solid var(--bd);transition:var(--e);}
.ftab:hover{color:var(--tx);background:rgba(255,255,255,.05);}
.ftab.on{background:rgba(0,212,200,.1);border-color:rgba(0,212,200,.3);color:var(--cyan);}
.tlist{flex:1;overflow-y:auto;}
.t-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:48px 20px;text-align:center;color:var(--tx-3);}
.t-empty-ico{font-size:38px;margin-bottom:11px;}
.t-empty p{font-size:13px;line-height:1.65;}
.t-empty a{color:var(--cyan);}

/* ════════════════════════════════════════════
   THREAD ITEMS
   ADDITION B: WhatsApp-style unread treatment
════════════════════════════════════════════ */
.titem{
  display:flex;align-items:center;gap:11px;padding:13px 15px;
  border-bottom:1px solid var(--bd);cursor:pointer;transition:var(--e);
  text-decoration:none;color:var(--tx);position:relative;
}
.titem:hover{background:rgba(255,255,255,.025);}
.titem.on{
  background:rgba(0,212,200,.07);
  border-left:3px solid var(--cyan);
  padding-left:12px;
}
.titem.on .tiname{color:var(--cyan);}

/* Unread: teal left stripe + subtle teal tint (like WhatsApp green) */
.titem.unread:not(.on){
  border-left:3px solid var(--teal);
  padding-left:12px;
  background:rgba(31,217,160,.025);
}
/* Bold name when unread */
.titem.unread .tiname{
  font-weight:900;
}
/* Brighter, bolder preview text when unread */
.titem.unread .tiprev{
  color:var(--tx-2);
  font-weight:700;
}
/* Timestamp turns teal when unread */
.titem.unread:not(.on) .titime{
  color:var(--teal);
  font-weight:700;
}

.tiavwrap{position:relative;flex-shrink:0;}
.tiav{width:44px;height:44px;border-radius:50%;
  background:linear-gradient(135deg,var(--violet),var(--coral));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-size:15px;font-weight:800;color:#fff;overflow:hidden;}
.tiav img{width:100%;height:100%;object-fit:cover;}
.ti-online-dot{position:absolute;bottom:1px;right:1px;width:11px;height:11px;
  border-radius:50%;background:var(--teal);border:2px solid var(--s1);}
.tibody{flex:1;min-width:0;}
.tirow1{display:flex;justify-content:space-between;align-items:baseline;gap:5px;margin-bottom:1px;}
.tiname{font-family:var(--fh);font-size:13px;font-weight:800;transition:color .2s;}
.titime{font-size:10.5px;color:var(--tx-3);white-space:nowrap;flex-shrink:0;transition:color .2s;}
.tiprof{font-size:11px;color:var(--violet);font-weight:700;font-family:var(--fh);margin-bottom:2px;}
/* ADDITION C: last message preview line */
.tiprev{
  font-size:12px;color:var(--tx-3);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  transition:color .2s,font-weight .2s;
}
.tiprev.bold{color:var(--tx-2);font-weight:600;}
.tijob{font-size:10.5px;color:var(--tx-4);margin-top:2px;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}

/* ════════════════════════════════
   ADDITION B — UNREAD COUNT BADGE
   Green circle, WhatsApp style
════════════════════════════════ */
.tiunread{
  background:var(--teal);
  color:#0C0E14;
  font-size:10px;font-weight:900;
  min-width:20px;height:20px;
  border-radius:50px;
  display:flex;align-items:center;justify-content:center;
  padding:0 6px;
  font-family:var(--fh);
  flex-shrink:0;margin-left:2px;
  box-shadow:0 2px 8px rgba(31,217,160,.4);
  animation:unreadPop .3s cubic-bezier(.34,1.56,.64,1);
}
@keyframes unreadPop{from{transform:scale(0);}to{transform:scale(1);}}

/* CHAT PANEL */
.chat-panel{flex:1;display:flex;flex-direction:column;overflow:hidden;
  background:var(--bg);position:relative;transition:background .3s;}
.chat-bg-mesh{position:absolute;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(ellipse 55% 65% at 15% 30%,rgba(0,212,200,.025),transparent 60%),
    radial-gradient(ellipse 40% 50% at 85% 72%,rgba(255,107,74,.02),transparent 55%);}
.chat-bg-grid{position:absolute;inset:0;pointer-events:none;z-index:0;
  background-image:linear-gradient(rgba(0,212,200,.012) 1px,transparent 1px),
    linear-gradient(90deg,rgba(0,212,200,.012) 1px,transparent 1px);
  background-size:38px 38px;}

/* Chat header */
.chat-head{display:flex;align-items:center;gap:12px;padding:12px 20px;flex-shrink:0;
  background:rgba(12,14,20,.9);backdrop-filter:blur(22px);
  border-bottom:1px solid var(--bd);z-index:10;position:relative;
  transition:background .3s,border-color .3s;}
.back-btn{display:none;width:34px;height:34px;border-radius:var(--rs);
  background:rgba(255,255,255,.05);border:1px solid var(--bd);
  align-items:center;justify-content:center;font-size:18px;color:var(--tx-2);transition:var(--e);}
.back-btn:hover{background:rgba(255,255,255,.09);color:var(--tx);}
.chav{width:42px;height:42px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,var(--coral),var(--violet));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-size:14px;font-weight:800;color:#fff;
  overflow:hidden;position:relative;border:2px solid rgba(0,212,200,.3);}
.chav img{width:100%;height:100%;object-fit:cover;}
.ch-online-dot{position:absolute;bottom:1px;right:1px;width:10px;height:10px;
  border-radius:50%;background:var(--tx-3);border:2px solid rgba(12,14,20,.9);
  transition:background .5s;}
.ch-online-dot.live{background:var(--teal);}
.chinfo{flex:1;min-width:0;}
.chname{font-family:var(--fh);font-size:15px;font-weight:900;line-height:1.2;}
.chmeta{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:2px;}
.chprof{font-size:11.5px;color:var(--violet);font-weight:700;font-family:var(--fh);}
.chstatus-lbl{font-size:11.5px;font-weight:600;color:var(--tx-3);transition:color .4s;}
.chstatus-lbl.online{color:var(--teal);}
.chstars{display:flex;align-items:center;gap:2px;font-size:11px;}
.s1{color:var(--amber);}.s2{color:var(--amber);opacity:.5;}.s0{color:var(--tx-4);}
.chrating{font-family:var(--fh);font-size:11px;font-weight:800;color:var(--amber);margin-left:3px;}
.chacts{display:flex;align-items:center;gap:7px;}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--rs);
  font-size:12.5px;font-weight:700;font-family:var(--fh);border:none;cursor:pointer;
  transition:var(--e);white-space:nowrap;text-decoration:none;}
.btn-xs{padding:5px 10px;font-size:11.5px;}
.btn-ghost{background:rgba(255,255,255,.05);border:1px solid var(--bd);color:var(--tx);}
.btn-ghost:hover{background:rgba(255,255,255,.09);border-color:var(--bd2);}
.btn-cyan{background:linear-gradient(135deg,var(--cyan),var(--teal));color:#000;font-weight:800;}
.btn-cyan:hover{transform:translateY(-1px);}
.btn-coral{background:linear-gradient(135deg,var(--coral),#FF9A6C);color:#fff;font-weight:800;}
.btn-coral:hover{transform:translateY(-1px);}
.btn-call{background:rgba(31,217,160,.12);border:1px solid rgba(31,217,160,.28);color:var(--teal);font-weight:700;}
.btn-call:hover{background:rgba(31,217,160,.2);}
.btn-call.no-phone{background:rgba(100,116,139,.1);border-color:var(--bd);color:var(--tx-3);cursor:not-allowed;}

/* Job banner */
.job-banner{display:flex;align-items:center;gap:11px;padding:9px 20px;flex-shrink:0;
  background:rgba(0,212,200,.05);border-bottom:1px solid rgba(0,212,200,.14);z-index:9;position:relative;}
.jbico{width:28px;height:28px;border-radius:8px;flex-shrink:0;
  background:rgba(0,212,200,.1);border:1px solid rgba(0,212,200,.18);
  display:flex;align-items:center;justify-content:center;font-size:13px;}
.jbinfo{flex:1;min-width:0;}
.jbtitle{font-family:var(--fh);font-size:12.5px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.jbmeta{font-size:11px;color:var(--tx-3);}
.jbpill{padding:3px 9px;border-radius:6px;font-size:10.5px;font-weight:800;font-family:var(--fh);white-space:nowrap;}
.jp-open{background:rgba(31,217,160,.1);color:var(--teal);border:1px solid rgba(31,217,160,.2);}
.jp-progress{background:rgba(0,212,200,.1);color:var(--cyan);border:1px solid rgba(0,212,200,.22);}
.jp-done{background:rgba(247,183,49,.1);color:var(--amber);border:1px solid rgba(247,183,49,.2);}
.jp-other{background:rgba(100,116,139,.1);color:var(--tx-3);border:1px solid var(--bd);}

/* Messages body */
.chat-body{flex:1;overflow-y:auto;padding:18px 22px 10px;
  display:flex;flex-direction:column;gap:2px;position:relative;z-index:1;scroll-behavior:smooth;}
.ddiv{display:flex;align-items:center;gap:10px;margin:12px 0 8px;}
.dline{flex:1;height:1px;background:var(--bd);}
.dlbl{font-size:11px;font-weight:700;color:var(--tx-3);font-family:var(--fh);background:var(--bg);padding:0 4px;white-space:nowrap;}

/* Bubble rows */
.brow{display:flex;align-items:flex-end;gap:7px;margin-bottom:2px;animation:bIn .2s ease;}
@keyframes bIn{from{opacity:0;transform:translateY(7px);}to{opacity:1;transform:translateY(0);}}
.brow.mine{flex-direction:row-reverse;}
.brow.hideav .bav{visibility:hidden;}
.bav{width:26px;height:26px;border-radius:50%;flex-shrink:0;
  background:linear-gradient(135deg,var(--coral),var(--violet));
  display:flex;align-items:center;justify-content:center;
  font-family:var(--fh);font-size:9px;font-weight:800;color:#fff;overflow:hidden;}
.bav img{width:100%;height:100%;object-fit:cover;}
.bav.mine-av{background:linear-gradient(135deg,var(--cyan),var(--teal));}
.bwrap{display:flex;flex-direction:column;gap:1px;max-width:min(66%,460px);}
.brow.mine .bwrap{align-items:flex-end;}
.rsnip{background:rgba(0,0,0,.25);border-left:3px solid var(--cyan);
  border-radius:8px 8px 0 0;padding:7px 11px;margin-bottom:2px;font-size:11.5px;color:var(--tx-2);}
.rsnip-name{font-family:var(--fh);font-size:10.5px;font-weight:800;color:var(--cyan);margin-bottom:2px;}
.rsnip-txt{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.bub{padding:10px 14px;border-radius:16px;font-size:14px;line-height:1.6;word-break:break-word;transition:var(--e);}
.bub:hover{transform:scale(1.005);}
.bub.recv{background:var(--s3);color:var(--tx);border-bottom-left-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.25);}
.bub.sent{background:linear-gradient(135deg,rgba(0,212,200,.2),rgba(31,217,160,.13));
  border:1px solid rgba(0,212,200,.23);color:var(--tx);
  border-bottom-right-radius:4px;box-shadow:0 2px 10px rgba(0,212,200,.1);}

/* Image bubbles */
.imgbub{padding:3px;border-radius:14px;overflow:hidden;cursor:zoom-in;transition:var(--e);}
.imgbub:hover{transform:scale(1.01);}
.imgbub img{border-radius:11px;max-width:240px;width:100%;display:block;transition:opacity .25s;}
.imgbub img.lazy{opacity:0;}
.imgbub img.loaded{opacity:1;}
.img-cap{padding:6px 10px 3px;font-size:12px;color:var(--tx-2);}

/* File bubbles */
.filebub{display:flex;align-items:center;gap:11px;padding:11px 14px;border-radius:14px;
  background:var(--s4);border:1px solid var(--bd2);max-width:270px;cursor:pointer;
  transition:var(--e);text-decoration:none;}
.filebub:hover{border-color:var(--cyan);background:rgba(0,212,200,.05);}
.fibico{width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;}
.fi-pdf{background:rgba(255,77,109,.12);border:1px solid rgba(255,77,109,.22);}
.fi-doc{background:rgba(78,158,255,.12);border:1px solid rgba(78,158,255,.22);}
.fi-xls{background:rgba(31,217,160,.1);border:1px solid rgba(31,217,160,.2);}
.fi-zip{background:rgba(124,111,247,.12);border:1px solid rgba(124,111,247,.22);}
.fi-img{background:rgba(0,212,200,.1);border:1px solid rgba(0,212,200,.2);}
.fi-vid{background:rgba(255,107,74,.1);border:1px solid rgba(255,107,74,.2);}
.fi-aud{background:rgba(247,183,49,.1);border:1px solid rgba(247,183,49,.2);}
.fi-def{background:rgba(100,116,139,.1);border:1px solid var(--bd);}
.fibinfo{flex:1;min-width:0;}
.fibname{font-family:var(--fh);font-size:12.5px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.fibsize{font-size:11px;color:var(--tx-3);margin-top:1px;}
.fibdl{color:var(--cyan);font-size:16px;flex-shrink:0;}

/* Voice note bubble */
.voice-bub{display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:16px;min-width:220px;max-width:280px;}
.voice-bub.sent{background:linear-gradient(135deg,rgba(0,212,200,.2),rgba(31,217,160,.13));border:1px solid rgba(0,212,200,.23);border-bottom-right-radius:4px;}
.voice-bub.recv{background:var(--s3);border-bottom-left-radius:4px;}
.vn-play{width:34px;height:34px;border-radius:50%;flex-shrink:0;border:none;display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;transition:var(--e);}
.voice-bub.sent .vn-play{background:rgba(0,212,200,.25);color:var(--cyan);}
.voice-bub.recv .vn-play{background:rgba(255,255,255,.08);color:var(--tx-2);}
.vn-play:hover{transform:scale(1.1);}
.vn-waveform{flex:1;display:flex;align-items:center;gap:2px;height:28px;}
.vn-bar{width:3px;border-radius:2px;background:var(--tx-3);transition:background .3s;}
.vn-bar.active{background:var(--cyan);}
.vn-dur{font-family:var(--fh);font-size:11px;font-weight:700;color:var(--tx-3);white-space:nowrap;}

/* System msg */
.sysmsg{text-align:center;font-size:11.5px;color:var(--tx-3);padding:5px 16px;
  background:rgba(255,255,255,.025);border-radius:50px;margin:7px auto;
  width:fit-content;font-family:var(--fh);border:1px solid var(--bd);}

/* Bubble meta */
.bmeta{display:flex;align-items:center;gap:4px;margin-top:3px;font-size:10.5px;color:var(--tx-3);}
.brow.mine .bmeta{justify-content:flex-end;}
.tick{font-size:11px;letter-spacing:-2.5px;line-height:1;}
.t-s{color:var(--tx-3);}
.t-d{color:var(--tx-2);}
.t-r{color:var(--cyan);}

/* Typing indicator */
.typrow{display:flex;align-items:flex-end;gap:7px;margin-top:4px;animation:bIn .2s ease;}
.typbub{background:var(--s3);border-radius:16px;border-bottom-left-radius:4px;padding:13px 16px;display:flex;gap:4px;align-items:center;}
.tdot{width:6px;height:6px;border-radius:50%;background:var(--tx-3);animation:td 1.2s ease-in-out infinite;}
.tdot:nth-child(2){animation-delay:.2s;}
.tdot:nth-child(3){animation-delay:.4s;}
@keyframes td{0%,60%,100%{transform:translateY(0);}30%{transform:translateY(-5px);background:var(--cyan);}}

/* Empty chat */
.chat-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px 24px;position:relative;z-index:1;}
.ceav{width:70px;height:70px;border-radius:50%;margin:0 auto 16px;background:linear-gradient(135deg,var(--coral),var(--violet));display:flex;align-items:center;justify-content:center;font-family:var(--fh);font-size:24px;font-weight:800;color:#fff;overflow:hidden;border:3px solid rgba(0,212,200,.3);}
.ceav img{width:100%;height:100%;object-fit:cover;}
.cename{font-family:var(--fh);font-size:17px;font-weight:900;margin-bottom:4px;}
.cesub{font-size:13px;color:var(--tx-2);max-width:290px;line-height:1.7;}

/* Input area */
.chat-input{padding:12px 18px;padding-bottom:max(12px,env(safe-area-inset-bottom));
  background:rgba(12,14,20,.94);backdrop-filter:blur(22px);
  border-top:1px solid var(--bd);flex-shrink:0;position:relative;z-index:10;
  transition:background .3s,border-color .3s;}
.reply-bar{display:none;align-items:center;gap:9px;background:rgba(0,212,200,.07);
  border:1px solid rgba(0,212,200,.18);border-radius:var(--rs);padding:8px 12px;margin-bottom:10px;}
.reply-bar.show{display:flex;}
.rb-stripe{width:3px;background:var(--cyan);border-radius:2px;align-self:stretch;flex-shrink:0;}
.rb-txt{flex:1;font-size:12px;color:var(--tx-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rb-close{background:none;border:none;color:var(--tx-3);font-size:17px;padding:0 2px;transition:var(--e);}
.rb-close:hover{color:var(--tx);}
.upstrip{display:none;flex-wrap:wrap;gap:7px;padding-bottom:10px;}
.upstrip.show{display:flex;}
.upitem{position:relative;width:54px;height:54px;border-radius:10px;overflow:hidden;background:var(--s4);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;font-size:22px;}
.upitem img{width:100%;height:100%;object-fit:cover;}
.upitem-rm{position:absolute;top:-4px;right:-4px;width:17px;height:17px;border-radius:50%;background:var(--red);color:#fff;font-size:10px;font-weight:800;display:flex;align-items:center;justify-content:center;border:2px solid var(--bg);cursor:pointer;}
.rec-bar{display:none;align-items:center;gap:12px;background:rgba(255,77,109,.08);border:1px solid rgba(255,77,109,.2);border-radius:var(--rs);padding:10px 14px;margin-bottom:10px;}
.rec-bar.show{display:flex;}
.rec-dot{width:8px;height:8px;border-radius:50%;background:var(--red);flex-shrink:0;animation:recPulse 1s ease-in-out infinite;}
@keyframes recPulse{0%,100%{opacity:1;}50%{opacity:.3;}}
.rec-time{font-family:var(--fh);font-size:13px;font-weight:800;color:var(--red);}
.rec-cancel{background:none;border:none;color:var(--tx-3);font-size:13px;font-weight:600;font-family:var(--fh);margin-left:auto;cursor:pointer;transition:var(--e);}
.rec-cancel:hover{color:var(--tx);}
.input-row{display:flex;align-items:flex-end;gap:8px;}
.attgrp{display:flex;gap:5px;flex-shrink:0;}
.attbtn{width:38px;height:38px;border-radius:var(--rs);background:rgba(255,255,255,.04);border:1.5px solid var(--bd);display:flex;align-items:center;justify-content:center;font-size:16px;color:var(--tx-2);transition:var(--e);position:relative;overflow:hidden;}
.attbtn:hover{background:rgba(0,212,200,.1);border-color:rgba(0,212,200,.3);color:var(--cyan);}
.attbtn input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;font-size:0;}
.attbtn.recording{background:rgba(255,77,109,.15);border-color:rgba(255,77,109,.4);color:var(--red);}
.msgbox{flex:1;display:flex;align-items:flex-end;background:rgba(0,0,0,.3);border:1.5px solid var(--bd);border-radius:13px;padding:0 12px;gap:6px;transition:var(--e);}
.msgbox:focus-within{border-color:var(--cyan);box-shadow:0 0 0 3px var(--cyan-glo);}
.msgta{flex:1;background:transparent;border:none;outline:none;color:var(--tx);font-family:var(--fb);font-size:14px;resize:none;overflow-y:hidden;max-height:120px;padding:10px 0;line-height:1.6;}
.msgta::placeholder{color:var(--tx-3);}
.emojibtn{background:none;border:none;font-size:18px;padding:8px 0;color:var(--tx-3);transition:var(--e);flex-shrink:0;}
.emojibtn:hover{color:var(--amber);transform:scale(1.15);}
.sendbtn{width:42px;height:42px;border-radius:13px;flex-shrink:0;background:linear-gradient(135deg,var(--coral),#FF9A6C);border:none;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px var(--coral-glo);transition:var(--e);}
.sendbtn:hover{transform:scale(1.07);}
.sendbtn:active{transform:scale(.95);}
.sendbtn:disabled{opacity:.45;cursor:not-allowed;transform:none;}
.epick{position:absolute;bottom:calc(100% + 8px);right:18px;background:var(--s2);border:1px solid var(--bd2);border-radius:16px;padding:12px;box-shadow:0 18px 52px rgba(0,0,0,.65);display:none;z-index:50;width:274px;backdrop-filter:blur(22px);}
.epick.open{display:block;animation:epIn .17s ease;}
@keyframes epIn{from{opacity:0;transform:scale(.94) translateY(8px);}to{opacity:1;transform:scale(1) translateY(0);}}
.epgrid{display:grid;grid-template-columns:repeat(8,1fr);gap:3px;}
.epbtn{background:none;border:none;font-size:19px;padding:5px;border-radius:8px;cursor:pointer;transition:var(--e);line-height:1;}
.epbtn:hover{background:rgba(255,255,255,.08);transform:scale(1.22);}

/* No chat */
.no-chat{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:48px 32px;position:relative;z-index:1;}
.nc-orb{width:96px;height:96px;border-radius:50%;margin:0 auto 22px;background:linear-gradient(135deg,rgba(0,212,200,.14),rgba(255,107,74,.08));border:2px solid rgba(0,212,200,.2);display:flex;align-items:center;justify-content:center;font-size:42px;animation:orb 4s ease-in-out infinite;}
@keyframes orb{0%,100%{transform:translateY(0);}50%{transform:translateY(-9px);}}
.nc-title{font-family:var(--fh);font-size:22px;font-weight:900;margin-bottom:8px;}
.nc-sub{font-size:14px;color:var(--tx-2);max-width:320px;line-height:1.75;}
.nc-ctas{display:flex;gap:10px;margin-top:22px;flex-wrap:wrap;justify-content:center;}

/* Lightbox */
.lightbox{display:none;position:fixed;inset:0;z-index:3000;background:rgba(0,0,0,.95);backdrop-filter:blur(22px);align-items:center;justify-content:center;padding:20px;}
.lightbox.open{display:flex;}
.lightbox img{max-width:90vw;max-height:90vh;object-fit:contain;border-radius:12px;}
.lb-close{position:absolute;top:16px;right:18px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:#fff;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:17px;transition:var(--e);}
.lb-close:hover{background:rgba(255,77,109,.3);}

/* Context menu */
.ctxmenu{position:fixed;z-index:2000;background:var(--s2);border:1px solid var(--bd2);border-radius:12px;padding:6px;min-width:158px;box-shadow:0 14px 44px rgba(0,0,0,.65);display:none;backdrop-filter:blur(18px);}
.ctxmenu.open{display:block;animation:ctxIn .15s ease;}
@keyframes ctxIn{from{opacity:0;transform:scale(.94);}to{opacity:1;transform:scale(1);}}
.ctxi{display:flex;align-items:center;gap:9px;padding:9px 13px;border-radius:9px;font-size:13px;font-weight:600;font-family:var(--fh);cursor:pointer;transition:var(--e);color:var(--tx-2);}
.ctxi:hover{background:rgba(255,255,255,.06);color:var(--tx);}
.ctxi.del{color:var(--red);}
.ctxi.del:hover{background:rgba(255,77,109,.08);}

/* Toast */
#toasts{position:fixed;bottom:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:8px;}
.toast{display:flex;align-items:center;gap:11px;background:var(--s2);border:1px solid var(--bd2);padding:11px 15px;border-radius:var(--rs);max-width:320px;box-shadow:0 14px 40px rgba(0,0,0,.6);animation:tIn .3s ease;backdrop-filter:blur(18px);}
.toast.success{border-left:3px solid var(--teal);}
.toast.error{border-left:3px solid var(--red);}
.toast.info{border-left:3px solid var(--cyan);}
.ti{font-size:15px;flex-shrink:0;}.tb{flex:1;}
.ttl{font-family:var(--fh);font-weight:800;font-size:12px;margin-bottom:1px;}
.tmg{font-size:11px;color:var(--tx-3);}
.tx{color:var(--tx-3);font-size:16px;cursor:pointer;flex-shrink:0;}
@keyframes tIn{from{opacity:0;transform:translateX(48px);}to{opacity:1;transform:translateX(0);}}

/* Responsive */
@media(max-width:900px){
  .snav{display:none;}
  .nav-links{display:none;}
  .thread-panel{width:100%;}
  .chat-panel{display:none;position:fixed;inset:0;z-index:600;}
  .chat-panel.mobopen{display:flex;}
  .back-btn{display:flex;}
}
@media(max-width:540px){
  .bwrap{max-width:82%;}
  .chat-body{padding:14px 12px 8px;}
  .chat-input{padding:10px 12px;}
  .chacts .btn-ghost:not(.btn-call){display:none;}
}
</style>
</head>
<!-- ADDITION A: server-side cookie applies .lm immediately — no theme flash -->
<body class="<?= $isLight ? 'lm' : '' ?>" id="appBody">

<!-- TOP NAV -->
<nav class="topnav">
  <a href="<?= APP_URL ?>/index.php" class="nav-logo">
    <div class="logo-mark">G</div>
    <span class="logo-txt">Gig<span>Ghana</span></span>
  </a>
  <div class="nav-links">
    <a href="<?= APP_URL ?>/client/dashboard.php" class="navlink">📊 Dashboard</a>
    <a href="<?= APP_URL ?>/client/my-jobs.php"   class="navlink">📋 Jobs</a>
    <a href="<?= APP_URL ?>/client/messages.php"  class="navlink on">💬 Messages</a>
    <a href="<?= APP_URL ?>/search/providers.php" class="navlink">🔍 Talent</a>
  </div>
  <div class="nav-r">
    <!-- ADDITION A — Dark/Light theme toggle (matches dashboard.php exactly) -->
    <button class="theme-btn" id="themeBtn" onclick="toggleTheme()"
            title="Toggle light / dark mode">
      <?= $isLight ? '☀️' : '🌙' ?>
    </button>

    <div class="icon-btn">🔔<?php if($totalUnread>0):?><div class="pip"></div><?php endif;?></div>
    <div class="uchip">
      <div class="uchip-av">
        <?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?>
      </div>
      <?= sanitize($user['first_name']??'Me') ?>
    </div>
  </div>
</nav>

<div class="app">
  <!-- ICON SIDEBAR -->
  <nav class="snav">
    <a class="sni" href="<?= APP_URL ?>/client/dashboard.php"  title="Dashboard">📊</a>
    <a class="sni" href="<?= APP_URL ?>/client/post-job.php"   title="Post Job">✏️</a>
    <a class="sni" href="<?= APP_URL ?>/client/my-jobs.php"    title="My Jobs">📋</a>
    <a class="sni" href="<?= APP_URL ?>/client/proposals.php"  title="Proposals">📩</a>
    <a class="sni on" href="<?= APP_URL ?>/client/messages.php" title="Messages">
      💬<?php if($totalUnread>0):?><div class="sni-dot"></div><?php endif;?>
    </a>
    <a class="sni" href="<?= APP_URL ?>/search/providers.php"  title="Find Talent">🔍</a>
    <div class="sn-gap"></div>
    <a href="<?= APP_URL ?>/auth/logout.php" class="sni" style="color:var(--red);" title="Sign Out">🚪</a>
    <div class="sn-me">
      <?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?>
    </div>
  </nav>

  <!-- THREAD PANEL -->
  <div class="thread-panel" id="threadPanel">
    <div class="tp-head">
      <div class="tp-top">
        <div class="tp-title">Messages</div>
        <?php if($totalUnread>0):?><span class="ubadge"><?= $totalUnread ?> unread</span><?php endif;?>
      </div>
      <div class="sbox">
        <span style="color:var(--tx-3);font-size:14px;">🔍</span>
        <input type="text" id="tsearch" placeholder="Search conversations…" autocomplete="off">
      </div>
      <div class="ftabs">
        <button class="ftab on" onclick="filterConvs('all',this)">All</button>
        <button class="ftab"    onclick="filterConvs('unread',this)">Unread</button>
        <button class="ftab"    onclick="filterConvs('active',this)">Active Jobs</button>
      </div>
    </div>

    <div class="tlist" id="tlist">
      <?php if(empty($conversations)):?>
      <div class="t-empty">
        <div class="t-empty-ico">💬</div>
        <p>No conversations yet.<br>
          <a href="<?= APP_URL ?>/client/post-job.php">Post a job</a>
          to connect with providers.</p>
      </div>
      <?php else:
        foreach($conversations as $c):
          $cInit   = ini2($c['first_name'],$c['last_name']);
          $isOn    = (int)$c['id'] === $activeConvId;
          $unread  = (int)$c['my_unread'];
          $preview = htmlspecialchars(mb_substr($c['last_message_preview']??'Start a conversation',0,54));
          $cProf   = profLabel($c['profession']??$c['tagline']??'Freelancer',$profMap);
          $cOnline = (bool)$c['is_online'];
      ?>
      <a href="<?= APP_URL ?>/client/messages.php?conv=<?= $c['id'] ?>"
         class="titem <?= $isOn?'on':'' ?> <?= $unread>0?'unread':'' ?>"
         data-unread="<?= $unread>0?1:0 ?>" data-job="<?= htmlspecialchars($c['job_title']??'') ?>"
         onclick="convClick(event,<?= $c['id'] ?>)">
        <div class="tiavwrap">
          <div class="tiav">
            <?php if($c['avatar']):?><img src="<?= sanitize($c['avatar']) ?>" alt="" loading="lazy">
            <?php else: echo $cInit; endif;?>
          </div>
          <?php if($cOnline):?>
          <div class="ti-online-dot"></div>
          <?php endif;?>
        </div>
        <div class="tibody">
          <div class="tirow1">
            <div class="tiname"><?= sanitize($c['first_name'].' '.$c['last_name']) ?></div>
            <div class="titime"><?= $c['last_message_at'] ? tFmt($c['last_message_at']) : '' ?></div>
          </div>
          <div class="tiprof"><?= sanitize($cProf) ?></div>
          <!-- ADDITION C: last message preview, bold when unread -->
          <div class="tiprev <?= $unread>0?'bold':'' ?>"><?= $preview ?></div>
          <?php if(!empty($c['job_title'])):?>
          <div class="tijob">📋 <?= sanitize(mb_substr($c['job_title'],0,40)) ?></div>
          <?php endif;?>
        </div>
        <!-- ADDITION B: WhatsApp-style green unread count -->
        <?php if($unread>0):?><div class="tiunread"><?= min($unread,99) ?></div><?php endif;?>
      </a>
      <?php endforeach; endif;?>
    </div>
  </div>

  <!-- CHAT PANEL -->
  <div class="chat-panel <?= $activeConvId?'mobopen':'' ?>" id="chatPanel">
    <div class="chat-bg-mesh"></div><div class="chat-bg-grid"></div>

    <?php if($activeConv && $otherUser): ?>

    <!-- CHAT HEADER -->
    <div class="chat-head">
      <button class="back-btn" onclick="goBack()">‹</button>
      <div class="chav">
        <?php if($otherUser['avatar']):?><img src="<?= sanitize($otherUser['avatar']) ?>" alt="">
        <?php else: echo $oInit; endif;?>
        <div class="ch-online-dot <?= $oIsOnline?'live':'' ?>" id="onlineDot"></div>
      </div>
      <div class="chinfo">
        <div class="chname"><?= sanitize($otherUser['first_name'].' '.$otherUser['last_name']) ?></div>
        <div class="chmeta">
          <span class="chprof"><?= sanitize($oProfession) ?></span>
          <span class="chstatus-lbl <?= $oIsOnline?'online':'' ?>" id="statusLbl">
            <?= htmlspecialchars($oLastSeen) ?>
          </span>
          <?php if($oRating>0):?>
          <div class="chstars"><?= starsHtml($oRating) ?><span class="chrating"><?= number_format($oRating,1) ?></span></div>
          <?php endif;?>
        </div>
      </div>
      <div class="chacts">
        <a href="<?= APP_URL ?>/profile.php?id=<?= $otherUser['id'] ?>"
           class="btn btn-ghost btn-xs" target="_blank">👤 Profile</a>
        <?php if($activeJob):?>
        <a href="<?= APP_URL ?>/job-details.php?id=<?= $activeJob['id'] ?>"
           class="btn btn-ghost btn-xs" target="_blank">📋 Job</a>
        <?php endif;?>
        <?php if(!empty($oPhone)):?>
        <a href="tel:<?= htmlspecialchars($oPhone) ?>" class="btn btn-call btn-xs">📞 Call</a>
        <?php else:?>
        <button class="btn btn-call btn-xs no-phone"
          onclick="toast('No Number','This provider has not added a phone number.','info')">📞 Call</button>
        <?php endif;?>
        <div class="icon-btn" onclick="openCtx(event,'hdr')" title="More">⋮</div>
      </div>
    </div>

    <!-- JOB BANNER -->
    <?php if($activeJob):
      $jpClass = match($activeJob['status']??'open'){'in_progress'=>'jp-progress','completed'=>'jp-done','open'=>'jp-open',default=>'jp-other'};
      $jpLabel = ucwords(str_replace('_',' ',$activeJob['status']??'open'));
      $jBudg   = formatCurrency($activeJob['budget_min']);
      if($activeJob['budget_max']>$activeJob['budget_min']) $jBudg .= ' – '.formatCurrency($activeJob['budget_max']);
      if($activeJob['budget_type']==='hourly') $jBudg .= '/hr';
    ?>
    <div class="job-banner">
      <div class="jbico">💼</div>
      <div class="jbinfo">
        <div class="jbtitle"><?= sanitize($activeJob['title']) ?></div>
        <div class="jbmeta"><?= sanitize($activeJob['cat_name']??'Job') ?> · <?= $jBudg ?></div>
      </div>
      <span class="jbpill <?= $jpClass ?>"><?= $jpLabel ?></span>
      <a href="<?= APP_URL ?>/job-details.php?id=<?= $activeJob['id'] ?>"
         class="btn btn-ghost btn-xs" target="_blank">View →</a>
    </div>
    <?php endif;?>

    <!-- MESSAGES BODY -->
    <div class="chat-body" id="chatBody">
      <?php if(empty($messages)):?>
      <div class="chat-empty">
        <div class="ceav">
          <?php if($otherUser['avatar']):?><img src="<?= sanitize($otherUser['avatar']) ?>" alt=""><?php else: echo $oInit; endif;?>
        </div>
        <div class="cename"><?= sanitize($otherUser['first_name'].' '.$otherUser['last_name']) ?></div>
        <p class="cesub">No messages yet — say hello to <?= sanitize($otherUser['first_name']) ?>!</p>
      </div>
      <?php else:
        $prevDate = ''; $prevSender = -1;
        foreach($messages as $m):
          $isMine     = (int)$m['sender_id'] === $userId;
          $mDate      = date('Y-m-d',strtotime($m['created_at']));
          $mTime      = date('g:i A',strtotime($m['created_at']));
          $mType      = $m['message_type'] ?? 'text';
          $hideAv     = ($prevSender === (int)$m['sender_id']);
          $prevSender = (int)$m['sender_id'];
          if($mDate !== $prevDate):
            $prevDate = $mDate; $hideAv = false; $prevSender = -1;
            $dLbl = $mDate===date('Y-m-d')?'Today':($mDate===date('Y-m-d',strtotime('-1 day'))?'Yesterday':date('D, M j Y',strtotime($m['created_at'])));
      ?>
      <div class="ddiv"><div class="dline"></div><div class="dlbl"><?= $dLbl ?></div><div class="dline"></div></div>
      <?php endif;?>
      <?php if($mType==='system'):?><div class="sysmsg"><?= htmlspecialchars($m['content']) ?></div><?php continue; endif;?>

      <div class="brow <?= $isMine?'mine':'' ?> <?= $hideAv?'hideav':'' ?>"
           data-mid="<?= $m['id'] ?>"
           oncontextmenu="openCtx(event,'msg',<?= $m['id'] ?>,<?= $isMine?1:0 ?>)">
        <div class="bav <?= $isMine?'mine-av':'' ?>">
          <?php if(!$isMine && $otherUser['avatar']):?><img src="<?= sanitize($otherUser['avatar']) ?>" alt="" loading="lazy">
          <?php elseif(!$isMine): echo $oInit;
          elseif($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt="">
          <?php else: echo $myInit; endif;?>
        </div>
        <div class="bwrap">
          <?php if(!empty($m['reply_to_id']) && !empty($m['reply_content'])):?>
          <div class="rsnip">
            <div class="rsnip-name"><?= sanitize($m['reply_fname']??'') ?></div>
            <div class="rsnip-txt"><?= htmlspecialchars(mb_substr($m['reply_content'],0,65)) ?></div>
          </div>
          <?php endif;?>

          <?php if($mType==='image' && !empty($m['file_url'])):?>
          <div class="imgbub <?= $isMine?'sent':'recv' ?>" onclick="openLb('<?= sanitize($m['file_url']) ?>')">
            <img src="<?= sanitize($m['file_url']) ?>" alt="Image" loading="lazy" class="lazy" onload="this.classList.replace('lazy','loaded')">
            <?php if(!empty($m['content'])):?><div class="img-cap"><?= htmlspecialchars($m['content']) ?></div><?php endif;?>
          </div>

          <?php elseif(($mType==='file'||$mType==='audio') && !empty($m['file_url'])):
            $fname = $m['file_name'] ?: basename($m['file_url']);
            $ext   = strtolower(pathinfo($fname,PATHINFO_EXTENSION));
            $isAudio = in_array($ext,['mp3','wav','webm','ogg','m4a']);
          ?>
          <?php if($isAudio || $mType==='audio'):?>
          <div class="voice-bub <?= $isMine?'sent':'recv' ?>">
            <button class="vn-play" onclick="playVoice(this,'<?= sanitize($m['file_url']) ?>')" title="Play voice note">▶</button>
            <div class="vn-waveform" id="vw<?= $m['id'] ?>">
              <?php for($w=0;$w<18;$w++):
                $h = [6,8,10,14,18,22,16,10,7,12,20,16,11,8,14,10,7,9][$w] ?? 10;
              ?><div class="vn-bar" style="height:<?= $h ?>px;"></div><?php endfor;?>
            </div>
            <span class="vn-dur" id="vd<?= $m['id'] ?>"><?= $m['file_size'] ? szFmt((int)$m['file_size']) : '0:00' ?></span>
          </div>
          <?php else:
            [$fEmoji,$fCls] = fIcon($fname);
            $fSz = $m['file_size'] ? szFmt((int)$m['file_size']) : 'Download';
          ?>
          <a href="<?= sanitize($m['file_url']) ?>" target="_blank" rel="noopener" class="filebub">
            <div class="fibico <?= $fCls ?>"><?= $fEmoji ?></div>
            <div class="fibinfo">
              <div class="fibname"><?= htmlspecialchars($fname) ?></div>
              <div class="fibsize"><?= $fSz ?></div>
            </div>
            <div class="fibdl">↓</div>
          </a>
          <?php endif;?>

          <?php else:?>
          <div class="bub <?= $isMine?'sent':'recv' ?>"><?= nl2br(htmlspecialchars($m['content']??'')) ?></div>
          <?php endif;?>

          <div class="bmeta">
            <span><?= $mTime ?></span>
            <?php if($isMine):
              if($m['is_read'])           echo '<span class="tick t-r" title="Read">✓✓</span>';
              elseif($m['is_delivered'])  echo '<span class="tick t-d" title="Delivered">✓✓</span>';
              else                        echo '<span class="tick t-s" title="Sent">✓</span>';
            endif;?>
          </div>
        </div>
      </div>
      <?php endforeach;?>

      <div class="typrow" id="typingRow" style="display:none;">
        <div class="bav">
          <?php if($otherUser['avatar']):?><img src="<?= sanitize($otherUser['avatar']) ?>" alt=""><?php else: echo $oInit; endif;?>
        </div>
        <div class="typbub"><div class="tdot"></div><div class="tdot"></div><div class="tdot"></div></div>
      </div>
      <?php endif;?>
    </div>

    <!-- INPUT AREA -->
    <div class="chat-input">
      <div class="reply-bar" id="replyBar">
        <div class="rb-stripe"></div>
        <div class="rb-txt" id="replyTxt"></div>
        <button class="rb-close" onclick="cancelReply()">✕</button>
      </div>
      <div class="rec-bar" id="recBar">
        <div class="rec-dot"></div>
        <span class="rec-time" id="recTime">0:00</span>
        <span style="font-size:12px;color:var(--tx-3);">Recording voice note…</span>
        <button class="rec-cancel" onclick="cancelRecording()">Cancel</button>
      </div>
      <div class="upstrip" id="upstrip"></div>
      <div class="input-row">
        <div class="attgrp">
          <div class="attbtn" title="Attach file">
            📎<input type="file" id="fileIn" multiple
               accept=".pdf,.doc,.docx,.xls,.xlsx,.zip,.rar,.mp4"
               onchange="queueF(this,'file')">
          </div>
          <div class="attbtn" title="Send photo">
            📷<input type="file" id="imgIn" multiple accept="image/*" onchange="queueF(this,'image')">
          </div>
          <div class="attbtn" id="voiceBtn" title="Hold to record voice note" onmousedown="startRec()" ontouchstart="startRec()">🎙</div>
        </div>
        <div class="msgbox" id="msgbox">
          <textarea class="msgta" id="msgta" rows="1"
            placeholder="Message <?= sanitize($otherUser['first_name']) ?>…"
            onkeydown="hk(event)" oninput="ag(this);onTyping()"></textarea>
          <button class="emojibtn" onclick="toggleEp(event)">😊</button>
        </div>
        <button class="sendbtn" id="sendbtn" onclick="sendMsg()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
            <path d="M22 2L11 13M22 2L15 22L11 13L2 9L22 2Z" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
      <div class="epick" id="epick"><div class="epgrid" id="epgrid"></div></div>
    </div>

    <?php else:?>
    <div class="no-chat">
      <div class="nc-orb">💬</div>
      <div class="nc-title">Your Messages</div>
      <p class="nc-sub">Select a conversation or post a job to start connecting with Ghana's best freelancers.</p>
      <div class="nc-ctas">
        <a href="<?= APP_URL ?>/client/post-job.php"  class="btn btn-coral">+ Post a Job</a>
        <a href="<?= APP_URL ?>/search/providers.php" class="btn btn-ghost">Browse Talent →</a>
      </div>
    </div>
    <?php endif;?>
  </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lb" onclick="if(event.target===this)closeLb()">
  <button class="lb-close" onclick="closeLb()">✕</button>
  <img id="lbImg" src="" alt="">
</div>

<!-- CONTEXT MENU -->
<div class="ctxmenu" id="ctxmenu">
  <div class="ctxi" onclick="ctxDo('reply')">↩ Reply</div>
  <div class="ctxi" onclick="ctxDo('copy')">📋 Copy text</div>
  <div class="ctxi del" id="ctxDel" style="display:none" onclick="ctxDo('delete')">🗑 Delete</div>
</div>

<div id="toasts"></div>

<script>
const CONV_ID   = <?= (int)$activeConvId ?>;
const USER_ID   = <?= (int)$userId ?>;
const APP_URL   = '<?= APP_URL ?>';
const CSRF      = '<?= $csrf ?>';
const O_PHONE   = '<?= addslashes($oPhone) ?>';
let   LAST_ID   = <?= $lastMsgId ?>;

/* ═══════════════════════════════════════════════
   ADDITION A — THEME TOGGLE
   Identical to dashboard.php so the cookie
   persists the preference across both pages.
═══════════════════════════════════════════════ */
function toggleTheme() {
  const body    = document.getElementById('appBody');
  const isLight = body.classList.toggle('lm');
  const val     = isLight ? 'light' : 'dark';
  localStorage.setItem('gg_theme', val);
  document.cookie = `gg_theme=${val};path=/;max-age=31536000;SameSite=Lax`;
  document.getElementById('themeBtn').textContent = isLight ? '☀️' : '🌙';
  toast('Theme', isLight ? '☀️ Light mode' : '🌙 Dark mode', 'info');
}

/* Sync button to current state on load */
(function(){
  const stored = localStorage.getItem('gg_theme') || '<?= $isLight ? "light" : "dark" ?>';
  const body   = document.getElementById('appBody');
  const btn    = document.getElementById('themeBtn');
  if (stored === 'light') { body.classList.add('lm');    if(btn) btn.textContent = '☀️'; }
  else                    { body.classList.remove('lm'); if(btn) btn.textContent = '🌙'; }
})();

/* ═══════════════════════════════════════════
   FIX 1 — REAL PRESENCE POLLING
═══════════════════════════════════════════ */
let pollTimer = null, heartbeatTimer = null;
let isTypingLocal = false, typingStopTimer = null;

function startPolling() {
  if (!CONV_ID) return;
  sendHeartbeat();
  heartbeatTimer = setInterval(sendHeartbeat, 30000);
  pollTimer      = setInterval(doPoll, 3000);
}

function sendHeartbeat() {
  navigator.sendBeacon
    ? navigator.sendBeacon(APP_URL + '/api/presence.php',
        new URLSearchParams({ action:'heartbeat', conversation_id:CONV_ID, csrf:CSRF }))
    : fetch(APP_URL + '/api/presence.php', {
        method:'POST', keepalive:true,
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body: new URLSearchParams({ action:'heartbeat', conversation_id:CONV_ID, csrf:CSRF })
      }).catch(()=>{});
}

function doPoll() {
  fetch(`${APP_URL}/api/presence.php?action=poll&conversation_id=${CONV_ID}&since_id=${LAST_ID}`)
    .then(r => r.json())
    .then(d => {
      if (!d.success) return;
      const dot = document.getElementById('onlineDot');
      const lbl = document.getElementById('statusLbl');
      if (dot) { d.other_online ? dot.classList.add('live') : dot.classList.remove('live'); }
      if (lbl) {
        lbl.textContent = d.last_seen_str || (d.other_online ? 'Online now' : 'Offline');
        lbl.className   = 'chstatus-lbl' + (d.other_online ? ' online' : '');
      }
      const tr = document.getElementById('typingRow');
      if (tr) { tr.style.display = d.other_typing ? 'flex' : 'none'; if(d.other_typing) scrollBot(); }
      if (d.new_messages && d.new_messages.length > 0) {
        d.new_messages.forEach(m => { if (parseInt(m.id) > LAST_ID) { appendPollMsg(m); LAST_ID = parseInt(m.id); } });
        scrollBot();
      }
    })
    .catch(() => {});
}

function onTyping() {
  if (!CONV_ID) return;
  if (!isTypingLocal) {
    isTypingLocal = true;
    fetch(APP_URL + '/api/presence.php', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ action:'typing', conversation_id:CONV_ID, csrf:CSRF })
    }).catch(()=>{});
  }
  clearTimeout(typingStopTimer);
  typingStopTimer = setTimeout(stopTyping, 3000);
}
function stopTyping() {
  if (!CONV_ID || !isTypingLocal) return;
  isTypingLocal = false;
  fetch(APP_URL + '/api/presence.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ action:'stop_typing', conversation_id:CONV_ID, csrf:CSRF })
  }).catch(()=>{});
}

if (CONV_ID) startPolling();
window.addEventListener('pagehide', () => { stopTyping(); clearInterval(pollTimer); clearInterval(heartbeatTimer); });
window.addEventListener('beforeunload', stopTyping);

function appendPollMsg(m) {
  const body = document.getElementById('chatBody'); if (!body) return;
  const emp  = body.querySelector('.chat-empty');   if (emp) emp.remove();
  const isMine = parseInt(m.sender_id) === USER_ID;
  const mTime  = new Date(m.created_at.replace(' ','T')).toLocaleTimeString([],{hour:'numeric',minute:'2-digit'});
  const row = document.createElement('div');
  row.className = `brow ${isMine ? 'mine' : ''}`;
  row.dataset.mid = m.id;
  const myAvStr = `<?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?>`;
  const oAvStr  = `<?php if(!empty($otherUser['avatar'])):?><img src="<?= sanitize($otherUser['avatar']) ?>" alt="" loading="lazy"><?php else: echo $oInit; endif;?>`;
  let bHTML = '';
  if (m.message_type === 'image' && m.file_url) {
    bHTML = `<div class="imgbub ${isMine?'sent':'recv'}" onclick="openLb('${m.file_url}')"><img src="${m.file_url}" loading="lazy" class="lazy" onload="this.classList.replace('lazy','loaded')"></div>`;
  } else if (m.file_url) {
    const fn = m.file_name || m.file_url.split('/').pop();
    bHTML = `<a href="${m.file_url}" target="_blank" class="filebub"><div class="fibico fi-def">📎</div><div class="fibinfo"><div class="fibname">${esc(fn)}</div></div><div class="fibdl">↓</div></a>`;
  } else {
    bHTML = `<div class="bub ${isMine?'sent':'recv'}">${esc(m.content||'').replace(/\n/g,'<br>')}</div>`;
  }
  let tick = '';
  if (isMine) {
    if (m.is_read)           tick = '<span class="tick t-r" title="Read">✓✓</span>';
    else if (m.is_delivered) tick = '<span class="tick t-d" title="Delivered">✓✓</span>';
    else                     tick = '<span class="tick t-s" title="Sent">✓</span>';
  }
  row.innerHTML = `<div class="bav ${isMine?'mine-av':''}">${isMine?myAvStr:oAvStr}</div><div class="bwrap">${bHTML}<div class="bmeta"><span>${mTime}</span>${tick}</div></div>`;
  const tr = document.getElementById('typingRow');
  tr ? body.insertBefore(row, tr) : body.appendChild(row);
}

/* ═══════════════════════════════════════════
   SEND MESSAGE
═══════════════════════════════════════════ */
let replyId = null, pending = [];

function sendMsg() {
  const ta  = document.getElementById('msgta');
  const btn = document.getElementById('sendbtn');
  if (!CONV_ID) return;
  const text = ta ? ta.value.trim() : '';
  if (!text && pending.length === 0) return;
  btn.disabled = true;
  stopTyping();
  if (pending.length > 0) { uploadSend(text); return; }
  const tmpRow = addOptimisticBubble(text, 'text');
  ta.value = ''; ag(ta); cancelReply(); scrollBot();
  fetch(APP_URL + '/api/messages.php', {
    method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ action:'send', conversation_id:CONV_ID, content:text, message_type:'text', reply_to_id:replyId||'', csrf:CSRF })
  })
  .then(r => r.json())
  .then(d => {
    btn.disabled = false;
    if (d.success) {
      if (d.message_id) { LAST_ID = Math.max(LAST_ID, parseInt(d.message_id)); if(tmpRow) tmpRow.dataset.mid = d.message_id; }
      upgradeTick(tmpRow, 't-d', '✓✓');
    } else { toast('Error', d.message || 'Failed to send', 'error'); }
  })
  .catch(() => { btn.disabled = false; toast('Error','Network issue.','error'); });
}

function uploadSend(caption) {
  const btn = document.getElementById('sendbtn');
  const ta  = document.getElementById('msgta');
  const proms = pending.map(p => {
    const fd = new FormData();
    fd.append('action','send'); fd.append('conversation_id',CONV_ID);
    fd.append('content',caption); fd.append('message_type',p.type);
    fd.append('reply_to_id',replyId||''); fd.append('csrf',CSRF);
    fd.append('file',p.file,p.file.name);
    return fetch(APP_URL+'/api/messages.php',{method:'POST',body:fd}).then(r=>r.json());
  });
  Promise.all(proms).then(results => {
    btn.disabled = false; if(ta){ta.value='';ag(ta);} pending=[]; renderStrip(); cancelReply();
    results.forEach(d => { if(d.success){ addOptimisticBubble(caption,d.message_type,d.file_url,d.file_name,d.file_size); if(d.message_id) LAST_ID=Math.max(LAST_ID,parseInt(d.message_id)); } });
    scrollBot();
  }).catch(() => { btn.disabled=false; toast('Upload Failed','Check connection.','error'); });
}

function addOptimisticBubble(content, type, fileUrl, fileName, fileSize) {
  const body = document.getElementById('chatBody'); if (!body) return null;
  const emp  = body.querySelector('.chat-empty');   if (emp) emp.remove();
  const row  = document.createElement('div');
  row.className = 'brow mine';
  const myAvStr = `<?php if($myAvatar):?><img src="<?= sanitize($myAvatar) ?>" alt=""><?php else: echo $myInit; endif;?>`;
  let bHTML = '';
  if (type === 'image' && fileUrl) {
    bHTML = `<div class="imgbub sent" onclick="openLb('${fileUrl}')"><img src="${fileUrl}" loading="lazy" class="lazy" onload="this.classList.replace('lazy','loaded')"></div>`;
  } else if (type === 'file' && fileUrl) {
    const fn = fileName || fileUrl.split('/').pop();
    bHTML = `<a href="${fileUrl}" target="_blank" class="filebub"><div class="fibico fi-def">📎</div><div class="fibinfo"><div class="fibname">${esc(fn)}</div><div class="fibsize">${fileSize?szFmt(fileSize):'Uploading…'}</div></div><div class="fibdl">↓</div></a>`;
  } else if (type === 'audio' && fileUrl) {
    bHTML = `<div class="voice-bub sent"><button class="vn-play" onclick="playVoice(this,'${fileUrl}')">▶</button><div class="vn-waveform">${'<div class="vn-bar" style="height:10px;"></div>'.repeat(18)}</div><span class="vn-dur">Sent</span></div>`;
  } else {
    bHTML = `<div class="bub sent">${esc(content).replace(/\n/g,'<br>')}</div>`;
  }
  row.innerHTML = `<div class="bav mine-av">${myAvStr}</div><div class="bwrap">${bHTML}<div class="bmeta"><span>now</span><span class="tick t-s" title="Sent">✓</span></div></div>`;
  const tr = document.getElementById('typingRow');
  tr ? body.insertBefore(row, tr) : body.appendChild(row);
  return row;
}

function upgradeTick(row, cls, symbol) {
  if (!row) return;
  const t = row.querySelector('.tick');
  if (t) { t.className = 'tick ' + cls; t.textContent = symbol; }
}

/* ═══════════════════════════════════════════
   FIX 2 — VOICE NOTES (MediaRecorder API)
═══════════════════════════════════════════ */
let mediaRecorder=null, audioChunks=[], recInterval=null, recSeconds=0, recStream=null;

async function startRec() {
  if (mediaRecorder && mediaRecorder.state === 'recording') return;
  try {
    recStream = await navigator.mediaDevices.getUserMedia({ audio: true });
    mediaRecorder = new MediaRecorder(recStream, { mimeType: 'audio/webm' });
    audioChunks = []; recSeconds = 0;
    mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
    mediaRecorder.onstop = () => {
      clearInterval(recInterval);
      document.getElementById('recBar').classList.remove('show');
      document.getElementById('voiceBtn').classList.remove('recording');
      recStream.getTracks().forEach(t => t.stop());
      if (audioChunks.length > 0 && recSeconds >= 1) uploadVoiceNote(new Blob(audioChunks, { type: 'audio/webm' }));
    };
    mediaRecorder.start(200);
    document.getElementById('recBar').classList.add('show');
    document.getElementById('voiceBtn').classList.add('recording');
    recInterval = setInterval(() => {
      recSeconds++;
      const m=Math.floor(recSeconds/60),s=recSeconds%60;
      document.getElementById('recTime').textContent=`${m}:${s.toString().padStart(2,'0')}`;
      if(recSeconds>=120) stopRec();
    }, 1000);
    document.addEventListener('mouseup', stopRec, { once: true });
    document.addEventListener('touchend', stopRec, { once: true });
  } catch(err) { toast('Microphone','Could not access microphone. Please allow permission.','error'); }
}

function stopRec() { if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop(); }

function cancelRecording() {
  audioChunks=[];
  if (mediaRecorder && mediaRecorder.state === 'recording') mediaRecorder.stop();
  clearInterval(recInterval);
  document.getElementById('recBar').classList.remove('show');
  document.getElementById('voiceBtn').classList.remove('recording');
  if (recStream) recStream.getTracks().forEach(t => t.stop());
  recSeconds=0;
}

function uploadVoiceNote(blob) {
  const btn = document.getElementById('sendbtn'); btn.disabled=true;
  const fd  = new FormData();
  fd.append('action','send'); fd.append('conversation_id',CONV_ID);
  fd.append('content',''); fd.append('message_type','audio');
  fd.append('csrf',CSRF); fd.append('file',blob,`voice_${Date.now()}.webm`);
  fetch(APP_URL+'/api/messages.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(d=>{ btn.disabled=false; if(d.success){addOptimisticBubble('','audio',d.file_url);if(d.message_id)LAST_ID=Math.max(LAST_ID,parseInt(d.message_id));scrollBot();}else toast('Error',d.message||'Voice note failed','error'); })
    .catch(()=>{ btn.disabled=false; toast('Error','Upload failed.','error'); });
}

let currentAudio=null;
function playVoice(btn,url){
  if(currentAudio){currentAudio.pause();currentAudio=null;}
  const audio=new Audio(url); currentAudio=audio;
  btn.textContent='⏸';
  audio.play().catch(()=>toast('Error','Cannot play audio.','error'));
  audio.onended=()=>{btn.textContent='▶';currentAudio=null;};
  audio.onpause=()=>{btn.textContent='▶';};
}

/* ═══════════════════════════════════════════
   EMOJI, FILE QUEUE, TEXTAREA, SCROLL
═══════════════════════════════════════════ */
const EM=['😊','😂','🙏','👍','👌','🔥','💪','✅','❤️','😍','🥰','🎉','😅','🤔','😮','😁','💯','🌟','⭐','🎊','🙌','👏','🤝','💼','📋','⚡','🔑','💡','🇬🇭','✨','🚀','🏆','💰','📱','💻','🔧','🎨','📷','🎬','🌍','🍽️','🏗️','🔌','🔨','📚','🎵','💊','🌿'];
(()=>{const g=document.getElementById('epgrid');if(!g)return;EM.forEach(e=>{const b=document.createElement('button');b.className='epbtn';b.textContent=e;b.onclick=()=>insEmoji(e);g.appendChild(b);});})();
function toggleEp(e){e.stopPropagation();document.getElementById('epick').classList.toggle('open');}
function insEmoji(em){const ta=document.getElementById('msgta');if(!ta)return;const s=ta.selectionStart,en=ta.selectionEnd;ta.value=ta.value.slice(0,s)+em+ta.value.slice(en);ta.setSelectionRange(s+em.length,s+em.length);ta.focus();ag(ta);document.getElementById('epick').classList.remove('open');}
document.addEventListener('click',()=>document.getElementById('epick')?.classList.remove('open'));
function ag(el){el.style.height='auto';el.style.height=Math.min(el.scrollHeight,120)+'px';}
function hk(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();sendMsg();}}

function queueF(inp,type){Array.from(inp.files).slice(0,5-pending.length).forEach(f=>pending.push({file:f,type}));renderStrip();inp.value='';}
function renderStrip(){
  const s=document.getElementById('upstrip');s.innerHTML='';
  if(!pending.length){s.classList.remove('show');return;}
  s.classList.add('show');
  pending.forEach((p,i)=>{
    const d=document.createElement('div');d.className='upitem';
    if(p.type==='image'){const img=document.createElement('img');const rd=new FileReader();rd.onload=e=>img.src=e.target.result;rd.readAsDataURL(p.file);d.appendChild(img);}
    else d.textContent='📎';
    const rm=document.createElement('div');rm.className='upitem-rm';rm.textContent='×';rm.onclick=()=>{pending.splice(i,1);renderStrip();};d.appendChild(rm);s.appendChild(d);
  });
}

function setReply(id,txt){replyId=id;document.getElementById('replyTxt').textContent=txt;document.getElementById('replyBar').classList.add('show');document.getElementById('msgta')?.focus();}
function cancelReply(){replyId=null;document.getElementById('replyBar').classList.remove('show');}
function scrollBot(s=true){const b=document.getElementById('chatBody');if(!b)return;b.scrollTo({top:b.scrollHeight,behavior:s?'smooth':'auto'});}
setTimeout(()=>scrollBot(false),80);

document.getElementById('tsearch')?.addEventListener('input',function(){
  const q=this.value.trim().toLowerCase();
  document.querySelectorAll('.titem').forEach(el=>{el.style.display=!q||el.textContent.toLowerCase().includes(q)?'':'none';});
});
function filterConvs(mode,btn){
  document.querySelectorAll('.ftab').forEach(b=>b.classList.remove('on'));btn.classList.add('on');
  document.querySelectorAll('.titem').forEach(el=>{el.style.display=(mode==='all'?true:mode==='unread'?el.dataset.unread==='1':!!el.dataset.job)?'':'none';});
}

function convClick(e,id){if(window.innerWidth<=900){e.preventDefault();window.location.href=APP_URL+'/client/messages.php?conv='+id;}}
function goBack(){document.getElementById('chatPanel').classList.remove('mobopen');history.pushState(null,'',APP_URL+'/client/messages.php');}

function openLb(src){document.getElementById('lbImg').src=src;document.getElementById('lb').classList.add('open');}
function closeLb(){document.getElementById('lb').classList.remove('open');}
document.addEventListener('keydown',e=>{if(e.key==='Escape')closeLb();});

let ctxId=null,ctxMine=false;
function openCtx(e,scope,id,mine){
  e.preventDefault();const m=document.getElementById('ctxmenu');ctxId=id;ctxMine=!!mine;
  document.getElementById('ctxDel').style.display=(scope==='msg'&&mine)?'':'none';
  m.style.left=Math.min(e.clientX,window.innerWidth-170)+'px';
  m.style.top=Math.min(e.clientY,window.innerHeight-120)+'px';
  m.classList.add('open');
}
document.addEventListener('click',()=>document.getElementById('ctxmenu').classList.remove('open'));
function ctxDo(action){
  document.getElementById('ctxmenu').classList.remove('open');if(!ctxId)return;
  if(action==='reply'){const bub=document.querySelector(`[data-mid="${ctxId}"] .bub`);if(bub)setReply(ctxId,bub.textContent.trim().slice(0,80));}
  if(action==='copy'){const bub=document.querySelector(`[data-mid="${ctxId}"] .bub`);if(bub)navigator.clipboard.writeText(bub.textContent.trim()).then(()=>toast('Copied','Message copied.','info'));}
  if(action==='delete'&&ctxMine){
    fetch(APP_URL+'/api/messages.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`action=delete&message_id=${ctxId}&csrf=${CSRF}`})
    .then(r=>r.json()).then(d=>{if(d.success){const el=document.querySelector(`[data-mid="${ctxId}"]`);if(el){el.style.opacity='0';el.style.transition='opacity .3s';setTimeout(()=>el.remove(),310);}toast('Deleted','Message removed.','info');}});
  }
}

function esc(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function szFmt(b){if(b<1024)return b+' B';if(b<1048576)return(b/1024).toFixed(1)+' KB';return(b/1048576).toFixed(1)+' MB';}

const TI={success:'✅',error:'❌',info:'ℹ️',warning:'⚠️'};
function toast(title,msg,type='info',d=4200){
  const c=document.getElementById('toasts');const t=document.createElement('div');t.className=`toast ${type}`;
  t.innerHTML=`<div class="ti">${TI[type]}</div><div class="tb"><div class="ttl">${title}</div><div class="tmg">${msg}</div></div><div class="tx" onclick="this.parentElement.remove()">×</div>`;
  c.appendChild(t);
  setTimeout(()=>{t.style.opacity='0';t.style.transform='translateX(48px)';t.style.transition='all .3s';setTimeout(()=>t.remove(),310);},d);
}
<?php if(isset($_GET['success'])):?>toast('','<?= sanitize($_GET['success']) ?>','success');<?php endif;?>
</script>
</body>
</html>