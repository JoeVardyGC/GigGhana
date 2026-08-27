<?php
/**
 * GigGhana — auth/register.php
 * Theme synced from index.php via localStorage('gg_theme')
 * Removed: testimonials · live counter · country field
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) redirect(APP_URL . '/' . $_SESSION['user_role'] . '/dashboard.php');

$role   = in_array($_GET['role'] ?? '', ['client', 'provider']) ? $_GET['role'] : 'client';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch — please try again.';
    } else {
        $firstName = sanitize(trim($_POST['first_name'] ?? ''));
        $lastName  = sanitize(trim($_POST['last_name']  ?? ''));
        $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $phone     = sanitize(trim($_POST['phone']    ?? ''));
        $password  = $_POST['password']         ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';
        $postRole  = in_array($_POST['role'] ?? '', ['client', 'provider']) ? $_POST['role'] : 'client';
        $terms     = $_POST['terms'] ?? '';

        if (strlen($firstName) < 2)                      $errors[] = 'First name must be at least 2 characters.';
        if (strlen($lastName)  < 2)                      $errors[] = 'Last name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Enter a valid email address.';
        if (strlen($password) < 8)                       $errors[] = 'Password must be at least 8 characters.';
        if (!preg_match('/[A-Z]/', $password))           $errors[] = 'Password must contain an uppercase letter.';
        if (!preg_match('/[0-9]/', $password))           $errors[] = 'Password must contain a number.';
        if ($password !== $confirm)                      $errors[] = 'Passwords do not match.';
        if (!$terms)                                     $errors[] = 'Please accept the Terms of Service to continue.';

        if (empty($errors)) {
            try {
                $db    = getDB();
                $check = $db->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $errors[] = 'An account with this email already exists. Try signing in.';
                } else {
                    $uuid  = generateUUID();
                    $hash  = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                    $otp   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $otpEx = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
                    $token = bin2hex(random_bytes(32));

                    $db->beginTransaction();
                    $db->prepare(
                        "INSERT INTO users
                         (uuid,first_name,last_name,email,phone,password_hash,role,country,
                          otp_code,otp_expires_at,email_verification_token)
                         VALUES (?,?,?,?,?,?,?,?,?,?,?)"
                    )->execute([$uuid,$firstName,$lastName,$email,$phone,$hash,$postRole,'Ghana',$otp,$otpEx,$token]);
                    $userId = $db->lastInsertId();
                    $db->prepare("INSERT INTO wallets (user_id) VALUES (?)")->execute([$userId]);
                    if ($postRole === 'provider') {
                        $db->prepare("INSERT INTO providers (user_id) VALUES (?)")->execute([$userId]);
                    }
                    $db->commit();
                    $_SESSION['pending_user_id'] = $userId;
                    $_SESSION['pending_email']   = $email;
                    $_SESSION['demo_otp']        = $otp;
                    redirect(APP_URL . '/auth/verify-otp.php?type=register');
                }
            } catch (Exception $e) {
                if (isset($db) && $db->inTransaction()) $db->rollBack();
                error_log($e->getMessage());
                $errors[] = 'Registration failed — please try again.';
            }
        }
    }
}

$csrf = generateCSRF();
$old  = [
    'first_name' => $_POST['first_name'] ?? '',
    'last_name'  => $_POST['last_name']  ?? '',
    'email'      => $_POST['email']      ?? '',
    'phone'      => $_POST['phone']      ?? '',
    'role'       => $_POST['role']       ?? $role,
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Join GigGhana — Africa's Freelance Marketplace</title>
<meta name="description" content="Create your free GigGhana account. Hire top Ghanaian talent or earn money with your skills.">
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      fontFamily: {
        heading: ['"Plus Jakarta Sans"', 'sans-serif'],
        body:    ['"DM Sans"', 'sans-serif'],
      },
    }
  }
}
</script>

<!--
  ╔═══════════════════════════════════════════════════╗
  ║  FLASH-FREE THEME SYNC — runs before first paint  ║
  ║  Reads localStorage('gg_theme') set by index.php  ║
  ║  Same key, same class (.lm) — zero flicker        ║
  ╚═══════════════════════════════════════════════════╝
-->
<script>
(function(){
  if(localStorage.getItem('gg_theme')==='light'){
    document.documentElement.classList.add('lm-pre');
  }
})();
</script>

<style>
/* ════════════════════════════════════════════════════════
   DESIGN TOKENS — Dark mode (default)
   Exact token set from index.php
════════════════════════════════════════════════════════ */
:root{
  --bg:#0C0E14; --s1:#13161E; --s2:#191D27; --s3:#1F2433;
  --glass:rgba(19,22,30,0.82);
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
  --gC:rgba(0,212,200,0.18); --gO:rgba(255,107,74,0.14);
  --fm:'Plus Jakarta Sans',sans-serif; --fb:'DM Sans',sans-serif;
}

/* ════════════════════════════════════════════════════════
   LIGHT MODE — exact copy of index.php .lm block
   Applied via body.lm (same class index uses)
════════════════════════════════════════════════════════ */
body.lm{
  --bg:#F3F5FA; --s1:#EAEEF7; --s2:#E0E6F2; --s3:#D4DCEE;
  --glass:rgba(234,238,247,0.92);
  --cyan:#009E95; --cyan-d:#007870; --cyan-l:#00CFC3;
  --cyan-dim:rgba(0,158,149,0.08); --cyan-border:rgba(0,158,149,0.2);
  --coral:#E8512B; --coral-d:#C43C1C;
  --coral-dim:rgba(232,81,43,0.08); --coral-border:rgba(232,81,43,0.2);
  --violet:#5B4FD9; --violet-d:#4540C0;
  --violet-dim:rgba(91,79,217,0.08); --violet-border:rgba(91,79,217,0.18);
  --green:#0DAF80; --green-d:#088C65; --green-dim:rgba(13,175,128,0.08);
  --amber:#D4980A; --red:#D63251;
  --tx:#0D1220; --tx-2:#344060; --tx-3:#6B7A99;
  --bd:rgba(30,40,80,0.09); --bd2:rgba(30,40,80,0.16);
  --gC:rgba(0,158,149,0.14); --gO:rgba(232,81,43,0.12);
}

/* Light mode specific overrides matching index.php */
body.lm .brand-panel-inner  { background:var(--s1); }
body.lm .form-panel-inner   { background:var(--s2); }
body.lm .mob-header-inner   { background:rgba(234,238,247,0.96) !important; border-color:var(--bd); }
body.lm .gg-input           { background:rgba(255,255,255,0.7); }
body.lm .gg-input:focus     { background:rgba(255,255,255,0.9); }
body.lm .role-card          { background:rgba(255,255,255,0.6); }
body.lm .role-card.active   { background:var(--cyan-dim); }
body.lm .social-btn         { background:rgba(255,255,255,0.6); border-color:var(--bd2); }
body.lm .social-btn:hover   { background:rgba(255,255,255,0.9); }
body.lm .str-seg            { background:rgba(30,40,80,0.1); }
body.lm .t-ndot             { background:rgba(30,40,80,0.12); }
body.lm .stat-num           { -webkit-text-fill-color: var(--cyan); }
body.lm .btn-theme          { border-color:var(--bd2); color:var(--tx-2); }
body.lm .s-badge-pill       { background:var(--violet-dim); border-color:var(--violet-border); color:var(--violet); }
body.lm .grid-tex           { background-image: linear-gradient(rgba(30,40,80,0.025) 1px,transparent 1px), linear-gradient(90deg,rgba(30,40,80,0.025) 1px,transparent 1px); }

/* ════════════════════════════════════════════════════════
   BASE RESET + BODY
════════════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);font-family:var(--fb);
  min-height:100svh;overflow-x:hidden;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
/* Apply pre-paint class before body.lm is ready */
html.lm-pre body,
html.lm-pre body *{transition:none !important;}

::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}

/* ── Animated top gradient bar ── */
.grad-bar{
  position:fixed;top:0;left:0;right:0;height:2px;z-index:200;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--green),var(--cyan));
  background-size:300% 100%;
  animation:gradShift 5s linear infinite;
}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:300% 50%}}

/* ── Ambient blobs ── */
.blob{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;}
.blob-1{width:500px;height:500px;background:radial-gradient(circle,rgba(0,212,200,0.08),transparent 70%);top:-140px;right:-100px;animation:blobFloat 10s ease-in-out infinite;}
.blob-2{width:320px;height:320px;background:radial-gradient(circle,rgba(255,107,74,0.07),transparent 70%);bottom:-80px;left:-60px;animation:blobFloat 8s 2s ease-in-out infinite;}
.blob-3{width:200px;height:200px;background:radial-gradient(circle,rgba(124,111,247,0.09),transparent 70%);top:42%;left:18%;animation:blobFloat 6s 1s ease-in-out infinite;}
@keyframes blobFloat{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-18px) scale(1.04);}}

/* Ghana flag conic orb */
.gh-orb{
  position:absolute;width:480px;height:480px;border-radius:50%;
  background:conic-gradient(from 0deg,#006B3F 0% 33.3%,#FCD116 33.3% 66.6%,#CE1126 66.6% 100%);
  opacity:0.025;top:-60px;right:-110px;pointer-events:none;
  animation:slowSpin 60s linear infinite;
}
@keyframes slowSpin{to{transform:rotate(360deg);}}

/* Grid texture */
.grid-tex{
  position:absolute;inset:0;pointer-events:none;
  background-image:
    linear-gradient(rgba(255,255,255,0.013) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,0.013) 1px,transparent 1px);
  background-size:52px 52px;
}

/* Noise overlay */
.noise{
  position:absolute;inset:0;pointer-events:none;opacity:.015;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* Brand panel right edge line */
.bp-edge{
  position:absolute;top:0;right:0;width:1.5px;height:100%;
  background:linear-gradient(to bottom,transparent,var(--cyan) 30%,var(--coral) 70%,transparent);
  opacity:.2;pointer-events:none;
}

/* ── Gradient text ── */
.grad-text{
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan),var(--coral));
  background-size:200% auto;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:gradShift 4s ease infinite;
}

/* ── Pulse dots ── */
.pulse-dot{width:7px;height:7px;border-radius:50%;background:var(--cyan);animation:pulseDot 2.2s ease infinite;flex-shrink:0;}
.pulse-dot-green{width:6px;height:6px;border-radius:50%;background:var(--green);animation:pulseDot 2s ease infinite;flex-shrink:0;}
@keyframes pulseDot{
  0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(0,212,200,.4);}
  50%{opacity:.2;box-shadow:0 0 0 7px rgba(0,212,200,0);}
}

/* ── Stats number gradient ── */
.stat-num{
  font-family:var(--fm);font-size:27px;font-weight:900;line-height:1;margin-bottom:3px;
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

/* ── Theme toggle button — identical to index.php btn-theme ── */
.btn-theme{
  background:transparent;color:var(--tx-2);
  border:1px solid var(--bd);border-radius:10px;
  padding:7px 11px;cursor:pointer;font-size:14px;
  transition:all .26s;line-height:1;font-family:var(--fb);
  flex-shrink:0;
}
.btn-theme:hover{background:rgba(255,255,255,0.07);}

/* ── Section label ── */
.s-badge-pill{
  display:inline-flex;align-items:center;gap:8px;
  background:var(--violet-dim);border:1px solid var(--violet-border);
  color:var(--violet);padding:4px 13px;border-radius:50px;
  font-size:10.5px;font-weight:700;font-family:var(--fm);
  letter-spacing:1.2px;text-transform:uppercase;margin-bottom:12px;
}

/* ── ROLE CARDS ── */
.role-card{
  cursor:pointer;background:rgba(0,0,0,0.22);
  border:1.5px solid var(--bd);border-radius:13px;
  padding:16px 14px;transition:all .22s cubic-bezier(.4,0,.2,1);
  position:relative;overflow:hidden;user-select:none;
}
.role-card::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,var(--cyan-dim),transparent);
  opacity:0;transition:opacity .22s;
}
.role-card.active{
  border-color:var(--cyan-border);background:var(--cyan-dim);
  box-shadow:0 0 0 1px rgba(0,212,200,.12),0 8px 28px rgba(0,212,200,0.07);
}
.role-card.active::before{opacity:1;}
.role-card.active .rc-title{color:var(--cyan);}
.role-card.active .rc-check{opacity:1;background:var(--cyan);border-color:var(--cyan);}
.role-card:hover:not(.active){border-color:var(--bd2);}
.rc-check{
  width:18px;height:18px;border-radius:50%;
  border:1.5px solid var(--bd2);
  display:flex;align-items:center;justify-content:center;
  font-size:9px;color:#0C0E14;opacity:0;
  transition:all .22s;flex-shrink:0;font-family:var(--fm);
}
.rc-emoji{font-size:26px;line-height:1;transition:transform .22s;}
.role-card.active .rc-emoji{transform:scale(1.1) rotate(-5deg);}
.rc-title{font-family:var(--fm);font-weight:700;font-size:13.5px;color:var(--tx-2);transition:color .22s;margin-bottom:2px;}
.rc-sub{font-size:11px;color:var(--tx-3);}

/* ── FORM FIELDS ── */
.field-wrap{position:relative;}
.field-ico{
  position:absolute;left:14px;top:50%;transform:translateY(-50%);
  font-size:15px;opacity:.4;pointer-events:none;transition:opacity .25s;line-height:1;
}
.field-wrap:focus-within .field-ico{opacity:.9;}

.gg-input{
  width:100%;background:rgba(0,0,0,0.25);
  border:1.5px solid var(--bd);border-radius:11px;
  padding:12px 14px 12px 44px;
  color:var(--tx);font-family:var(--fb);font-size:14px;
  outline:none;transition:all .24s;-webkit-appearance:none;
}
.gg-input::placeholder{color:var(--tx-3);opacity:.7;}
.gg-input:hover{border-color:var(--bd2);}
.gg-input:focus{
  border-color:var(--cyan);
  background:rgba(0,212,200,0.04);
  box-shadow:0 0 0 3px var(--cyan-dim);
}
.gg-input.is-valid{border-color:var(--green);background:rgba(31,217,160,0.04);}
.gg-input.is-error{border-color:var(--red);background:rgba(255,77,106,0.04);animation:shake .3s ease;}
@keyframes shake{0%,100%{transform:translateX(0);}25%,75%{transform:translateX(-4px);}50%{transform:translateX(4px);}}

.field-status{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  font-size:13px;pointer-events:none;opacity:0;transition:opacity .24s;line-height:1;
}
.gg-input.is-valid~.field-status,
.gg-input.is-error~.field-status{opacity:1;}

.pwd-eye{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;font-size:15px;
  color:var(--tx-3);z-index:2;line-height:1;padding:0;transition:color .2s;
}
.pwd-eye:hover{color:var(--tx);}

.field-err{font-size:11px;color:var(--red);margin-top:4px;display:none;align-items:center;gap:4px;}
.field-err.show{display:flex;}

/* ── PASSWORD STRENGTH ── */
.str-segs{display:grid;grid-template-columns:repeat(4,1fr);gap:4px;margin-bottom:5px;}
.str-seg{height:3px;border-radius:2px;background:rgba(255,255,255,0.07);transition:background .35s;}
.str-seg.s1{background:var(--red);}
.str-seg.s2{background:var(--coral);}
.str-seg.s3{background:var(--amber);}
.str-seg.s4{background:var(--green);}
.pwd-rules{display:grid;grid-template-columns:1fr 1fr;gap:4px 12px;margin-top:9px;}
.pwd-rule{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--tx-3);transition:color .24s;}
.pwd-rule.met{color:var(--green);}
.rule-c{
  width:14px;height:14px;border-radius:50%;border:1.5px solid currentColor;
  display:flex;align-items:center;justify-content:center;
  font-size:8px;flex-shrink:0;transition:all .24s;
}
.pwd-rule.met .rule-c{background:var(--green);border-color:var(--green);color:#0C0E14;}

/* ── CUSTOM CHECKBOX ── */
.cb-visual{
  width:20px;height:20px;border-radius:6px;
  background:rgba(0,0,0,0.28);border:1.5px solid var(--bd2);
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:900;color:#0C0E14;
  transition:all .22s;flex-shrink:0;cursor:pointer;
}
.cb-visual.checked{
  background:var(--cyan);border-color:var(--cyan);
  box-shadow:0 0 12px rgba(0,212,200,0.3);
}

/* ── SOCIAL BUTTONS ── */
.social-btn{
  position:relative;overflow:hidden;
  display:flex;align-items:center;justify-content:center;gap:10px;
  padding:12px 18px;border-radius:12px;
  background:rgba(255,255,255,0.04);border:1.5px solid var(--bd);
  color:var(--tx);font-family:var(--fm);font-size:13.5px;font-weight:600;
  cursor:pointer;text-decoration:none;
  transition:all .26s cubic-bezier(.4,0,.2,1);
}
.social-btn:hover{
  background:rgba(255,255,255,0.09);border-color:var(--bd2);
  transform:translateY(-2px);box-shadow:0 10px 30px rgba(0,0,0,0.3);
}
.social-btn::after{
  content:'';position:absolute;top:-50%;left:-100%;
  width:50%;height:200%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.07),transparent);
  transform:skewX(-20deg);transition:left .6s ease;
}
.social-btn:hover::after{left:180%;}

/* ── SUBMIT BUTTON ── */
.submit-btn{
  position:relative;overflow:hidden;
  width:100%;padding:15px 24px;border-radius:13px;border:none;
  background:linear-gradient(135deg,var(--cyan) 0%,var(--cyan-d) 50%,#009490 100%);
  background-size:200% auto;
  color:#0C0E14;font-family:var(--fm);font-size:16px;font-weight:800;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;
  transition:all .4s ease;
  box-shadow:0 4px 24px var(--gC),0 1px 4px rgba(0,0,0,.5);
}
.submit-btn:hover:not(:disabled){
  background-position:right center;
  transform:translateY(-2px);
  box-shadow:0 8px 36px var(--gC);
}
.submit-btn:active:not(:disabled){transform:translateY(0);}
.submit-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}
.submit-btn::before{
  content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);
  transition:left .6s ease;
}
.submit-btn:hover:not(:disabled)::before{left:150%;}

/* Spinner */
.spinner{animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── OR DIVIDER ── */
.divider{display:flex;align-items:center;gap:12px;margin:14px 0;}
.div-line{flex:1;height:1px;background:var(--bd);}
.div-txt{font-size:11px;color:var(--tx-3);white-space:nowrap;}

/* ── STAGGERED FADE-UP ── */
.su{animation:suAnim .5s ease both;}
.su-1{animation-delay:.04s;} .su-2{animation-delay:.09s;} .su-3{animation-delay:.14s;}
.su-4{animation-delay:.19s;} .su-5{animation-delay:.24s;} .su-6{animation-delay:.30s;}
.su-7{animation-delay:.36s;} .su-8{animation-delay:.42s;}
@keyframes suAnim{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}

/* ── RESPONSIVE ── */
@media(max-width:479px){.two-col{grid-template-columns:1fr !important;}}
</style>
</head>

<!--
  Apply .lm synchronously before first paint — no flash.
  The pre-paint script set html.lm-pre; now we mirror it to body.lm.
-->
<body class="">
<script>
// Synchronously apply to body before any rendering
if(document.documentElement.classList.contains('lm-pre')){
  document.body.classList.add('lm');
  document.documentElement.classList.remove('lm-pre');
}
</script>

<!-- Animated gradient bar -->
<div class="grad-bar"></div>

<!-- ════════════════════════════════════════
     MOBILE STICKY HEADER
════════════════════════════════════════ -->
<header class="lg:hidden fixed top-2 left-0 right-0 z-50">
  <div class="mob-header-inner mx-4 flex items-center justify-between px-4 py-3 rounded-2xl transition-all" style="background:rgba(19,22,30,0.95);backdrop-filter:blur(20px);border:1px solid var(--bd);">
    <a href="<?= APP_URL ?>/index.php" class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-[9px] flex items-center justify-center font-heading font-black text-sm flex-shrink-0" style="background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;">G</div>
      <span class="font-heading font-extrabold text-[17px]" style="color:var(--tx);">Gig<span style="color:var(--cyan);">Ghana</span></span>
    </a>
    <div class="flex items-center gap-2">
      <button class="btn-theme" id="themeBtn" onclick="toggleTheme()" title="Toggle theme">🌙</button>
      <a href="<?= APP_URL ?>/auth/login.php" class="text-[12px] font-semibold px-3 py-1.5 rounded-lg transition-all" style="color:var(--tx-3);background:rgba(255,255,255,0.04);border:1px solid var(--bd);">Sign in →</a>
    </div>
  </div>
</header>

<!-- ════════════════════════════════════════
     PAGE GRID — 50/50 split
════════════════════════════════════════ -->
<div class="lg:grid min-h-screen" style="grid-template-columns:50% 50%;">

  <!-- ════ LEFT — BRAND PANEL (desktop only) ════ -->
  <aside class="brand-panel-inner hidden lg:flex flex-col relative overflow-hidden min-h-screen px-12 py-11 transition-all" style="background:var(--s1);">
    <!-- Atmospheric layers -->
    <div class="grid-tex"></div>
    <div class="gh-orb"></div>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <div class="bp-edge"></div>

    <div class="relative z-10 flex flex-col h-full">

      <!-- Logo + theme toggle -->
      <div class="flex items-center justify-between mb-auto">
        <a href="<?= APP_URL ?>/index.php" class="flex items-center gap-2.5">
          <div class="w-10 h-10 rounded-[11px] flex items-center justify-center font-heading font-black text-[17px] flex-shrink-0" style="background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;box-shadow:0 4px 18px var(--gC);">G</div>
          <span class="font-heading font-extrabold text-[22px]" style="color:var(--tx);">Gig<span style="color:var(--cyan);">Ghana</span></span>
        </a>
        <!-- Theme toggle — same as index.php -->
        <button class="btn-theme" id="themeBtnDesktop" onclick="toggleTheme()" title="Toggle theme">🌙</button>
      </div>

      <!-- Hero copy -->
      <div class="flex-1 flex flex-col justify-center py-8">

        <!-- Eyebrow badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full mb-5 w-fit font-heading font-extrabold text-[10px] tracking-widest uppercase" style="background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);">
          <span class="pulse-dot"></span>
          🇬🇭 Ghana's #1 Freelance Platform
        </div>

        <!-- Headline -->
        <h1 class="font-heading font-black leading-[1.07] tracking-[-1.8px] mb-4" style="font-size:clamp(34px,3vw,50px);color:var(--tx);">
          Your Skill.<br>
          Your <span class="grad-text">Success.</span><br>
          Your <span style="color:var(--coral);">Ghana.</span>
        </h1>

        <p class="text-[14.5px] leading-[1.75] max-w-[360px] mb-8" style="color:var(--tx-2);">
          Join <strong style="color:var(--tx);">1,200+ verified freelancers</strong> and hundreds of growing businesses — all on one secure, escrow-protected marketplace built for Ghana.
        </p>

        <!-- Stats row -->
        <div class="flex gap-7 mb-8">
          <?php foreach([['1.2K+','Freelancers'],['₵195K','Paid Out'],['4.9★','Avg Rating'],['Free','First 3 Jobs']] as [$n,$l]): ?>
          <div>
            <div class="stat-num"><?= $n ?></div>
            <div class="text-[10px] font-bold uppercase tracking-[.9px]" style="color:var(--tx-3);font-family:var(--fm);"><?= $l ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Payment badges -->
        <div class="mb-8">
          <div class="text-[10px] font-bold uppercase tracking-[1px] mb-2.5" style="color:var(--tx-3);font-family:var(--fm);">Accepted Payments</div>
          <div class="flex flex-wrap gap-2">
            <?php foreach(['🟡 MTN MoMo','🔴 Vodafone Cash','💳 Visa / MC','🔒 Escrow','⚡ Instant Payouts'] as $b): ?>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11.5px] font-medium transition-all" style="background:rgba(255,255,255,0.03);border:1px solid var(--bd);color:var(--tx-2);"><?= $b ?></span>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Trust bullets -->
        <div class="flex flex-col gap-3">
          <?php
          $trusts = [
            ['🔒','Secure Escrow','Funds held until you approve delivery'],
            ['✅','Ghana Card Verified','Freelancers ID-checked for your safety'],
            ['📱','MoMo & Card Payments','MTN, Vodafone Cash, Visa accepted'],
            ['🆓','3 Free Jobs','Start earning with no upfront cost'],
          ];
          foreach($trusts as [$ico,$title,$sub]):
          ?>
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-[10px] flex items-center justify-center text-[15px] flex-shrink-0" style="background:var(--cyan-dim);border:1px solid var(--cyan-border);"><?= $ico ?></div>
            <div>
              <div class="font-heading font-bold text-[13px]" style="color:var(--tx);"><?= $title ?></div>
              <div class="text-[11px]" style="color:var(--tx-3);"><?= $sub ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

      </div><!-- /hero copy -->

      <!-- Bottom brand footer -->
      <div class="pt-6 border-t" style="border-color:var(--bd);">
        <p class="text-[11px] leading-relaxed" style="color:var(--tx-3);">
          © <?= date('Y') ?> GigGhana Ltd. Made with ❤️ in Ghana 🇬🇭<br>
          Secure · Escrow-protected · MoMo enabled
        </p>
      </div>

    </div><!-- /z-10 -->
  </aside>

  <!-- ════ RIGHT — FORM PANEL ════ -->
  <main class="form-panel-inner relative flex flex-col items-center justify-start lg:justify-center min-h-screen px-5 sm:px-8 lg:px-14 pt-24 lg:pt-10 pb-14 transition-all" style="background:var(--s2);">
    <div class="noise"></div>
    <!-- Ambient glow top-right -->
    <div class="absolute top-0 right-0 w-[360px] h-[360px] rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(0,212,200,0.04),transparent 70%);"></div>

    <div class="relative z-10 w-full max-w-[460px]">

      <!-- ══ 1. HERO SECTION ══ -->
      <div class="su su-1 mb-6">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full mb-3 font-heading font-extrabold text-[10px] tracking-widest uppercase" style="background:var(--green-dim,rgba(31,217,160,0.10));border:1px solid rgba(31,217,160,.22);color:var(--green);">
          <span class="pulse-dot-green"></span>
          Free · No Card Needed
        </div>
        <h1 class="font-heading font-black leading-[1.08] tracking-[-1.2px] mb-2" style="font-size:clamp(28px,5vw,36px);color:var(--tx);">
          Join <span class="grad-text">GigGhana</span> Today
        </h1>
        <p class="text-[13.5px] leading-relaxed" style="color:var(--tx-2);">
          Africa's premier freelance marketplace.
          Already have an account? <a href="<?= APP_URL ?>/auth/login.php" class="font-semibold hover:underline" style="color:var(--cyan);">Sign in →</a>
        </p>
      </div>

      <!-- ══ 2. ACCOUNT TYPE SELECTION ══ -->
      <div class="su su-2 mb-5">
        <div class="text-[10px] font-extrabold uppercase tracking-[1px] mb-2.5" style="color:var(--tx-3);font-family:var(--fm);">I want to…</div>
        <div class="grid grid-cols-2 gap-2.5">
          <div class="role-card <?= $old['role']==='client'?'active':'' ?>" id="rc-client" onclick="setRole('client')">
            <div class="flex items-start justify-between mb-2.5">
              <span class="rc-emoji">🏢</span>
              <div class="rc-check">✓</div>
            </div>
            <div class="rc-title">Hire Talent</div>
            <div class="rc-sub">Post jobs &amp; find pros</div>
          </div>
          <div class="role-card <?= $old['role']==='provider'?'active':'' ?>" id="rc-provider" onclick="setRole('provider')">
            <div class="flex items-start justify-between mb-2.5">
              <span class="rc-emoji">💼</span>
              <div class="rc-check">✓</div>
            </div>
            <div class="rc-title">Find Work</div>
            <div class="rc-sub">Earn with your skills</div>
          </div>
        </div>
      </div>

      <!-- ══ 4. SOCIAL SIGNUP ══ -->
      <div class="su su-3">
        <div class="grid grid-cols-2 gap-2.5 mb-1">
          <!-- Google -->
          <a href="<?= APP_URL ?>/auth/oauth.php?provider=google&role=<?= htmlspecialchars($old['role']) ?>" class="social-btn" id="googleBtn">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
              <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
              <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
              <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            Google
          </a>
          <!-- Facebook -->
          <a href="<?= APP_URL ?>/auth/oauth.php?provider=facebook&role=<?= htmlspecialchars($old['role']) ?>" class="social-btn" id="facebookBtn">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M18 9a9 9 0 1 0-10.406 8.892v-6.29H5.31V9h2.284V7.018C7.594 4.76 8.946 3.5 11 3.5c.955 0 1.954.17 1.954.17v2.218h-1.1c-1.085 0-1.423.674-1.423 1.365V9h2.42l-.387 2.602H10.43v6.29A9.002 9.002 0 0 0 18 9z" fill="#1877F2"/>
            </svg>
            Facebook
          </a>
        </div>
      </div>

      <!-- OR divider -->
      <div class="divider su su-3">
        <div class="div-line"></div>
        <span class="div-txt">or register with email</span>
        <div class="div-line"></div>
      </div>

      <!-- ══ ERROR BANNER ══ -->
      <?php if (!empty($errors)): ?>
      <div class="su mb-4 px-4 py-3.5 rounded-xl text-[12.5px]" style="background:rgba(255,77,106,0.07);border:1px solid rgba(255,77,106,.25);border-left:3px solid var(--red);">
        <div class="flex items-center gap-2 font-heading font-bold text-[12px] mb-1.5" style="color:#F87171;">⚠️ Please fix the following:</div>
        <ul class="space-y-0.5">
          <?php foreach($errors as $err): ?>
          <li class="flex items-start gap-1.5 text-[12px]" style="color:#fca5a5;">· <?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <!-- ══ 3. REGISTRATION FORM ══ -->
      <form method="POST" id="regForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($old['role']) ?>">

        <!-- First + Last Name -->
        <div class="grid grid-cols-2 gap-3 mb-3.5 two-col su su-3">
          <div>
            <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5" style="color:var(--tx-3);font-family:var(--fm);" for="firstName">First Name</label>
            <div class="field-wrap">
              <span class="field-ico">👤</span>
              <input type="text" name="first_name" id="firstName" class="gg-input"
                     placeholder="Kofi" maxlength="100" autocomplete="given-name"
                     value="<?= htmlspecialchars($old['first_name']) ?>">
              <div class="field-status" id="fs-first"></div>
            </div>
            <div class="field-err" id="err-first"></div>
          </div>
          <div>
            <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5" style="color:var(--tx-3);font-family:var(--fm);" for="lastName">Last Name</label>
            <div class="field-wrap">
              <span class="field-ico">👤</span>
              <input type="text" name="last_name" id="lastName" class="gg-input"
                     placeholder="Mensah" maxlength="100" autocomplete="family-name"
                     value="<?= htmlspecialchars($old['last_name']) ?>">
              <div class="field-status" id="fs-last"></div>
            </div>
            <div class="field-err" id="err-last"></div>
          </div>
        </div>

        <!-- Email -->
        <div class="mb-3.5 su su-4">
          <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5" style="color:var(--tx-3);font-family:var(--fm);" for="email">Email Address</label>
          <div class="field-wrap">
            <span class="field-ico">✉️</span>
            <input type="email" name="email" id="email" class="gg-input"
                   placeholder="you@example.com" autocomplete="email"
                   value="<?= htmlspecialchars($old['email']) ?>">
            <div class="field-status" id="fs-email"></div>
          </div>
          <div class="field-err" id="err-email"></div>
        </div>

        <!-- Phone -->
        <div class="mb-3.5 su su-4">
          <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5" style="color:var(--tx-3);font-family:var(--fm);" for="phone">
            Phone Number
            <span class="text-[10px] font-normal normal-case tracking-normal ml-1" style="color:var(--tx-3);">(Optional)</span>
          </label>
          <div class="field-wrap">
            <span class="field-ico">📱</span>
            <input type="tel" name="phone" id="phone" class="gg-input"
                   placeholder="+233 24 000 0000" autocomplete="tel"
                   value="<?= htmlspecialchars($old['phone']) ?>">
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3.5 su su-5">
          <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5" style="color:var(--tx-3);font-family:var(--fm);" for="password">Password</label>
          <div class="field-wrap">
            <span class="field-ico">🔑</span>
            <input type="password" name="password" id="password" class="gg-input"
                   placeholder="Create a strong password" autocomplete="new-password"
                   style="padding-right:42px;">
            <button type="button" class="pwd-eye" onclick="togglePwd('password',this)">👁</button>
          </div>
          <!-- Strength indicator -->
          <div id="strengthWrap" style="display:none;margin-top:9px;">
            <div class="str-segs">
              <div class="str-seg" id="seg1"></div>
              <div class="str-seg" id="seg2"></div>
              <div class="str-seg" id="seg3"></div>
              <div class="str-seg" id="seg4"></div>
            </div>
            <div class="flex justify-between text-[11px] mb-1">
              <span class="font-bold" id="strLabel" style="color:var(--tx-3);">Enter password</span>
              <span id="strPct" style="color:var(--tx-3);">0%</span>
            </div>
            <div class="pwd-rules">
              <div class="pwd-rule" id="rule-len"><div class="rule-c"></div>8+ chars</div>
              <div class="pwd-rule" id="rule-upper"><div class="rule-c"></div>Uppercase</div>
              <div class="pwd-rule" id="rule-num"><div class="rule-c"></div>Number (0–9)</div>
              <div class="pwd-rule" id="rule-special"><div class="rule-c"></div>Special char</div>
            </div>
          </div>
          <div class="field-err" id="err-pwd"></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-5 su su-5">
          <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5" style="color:var(--tx-3);font-family:var(--fm);" for="confirmPwd">Confirm Password</label>
          <div class="field-wrap">
            <span class="field-ico">🔒</span>
            <input type="password" name="confirm_password" id="confirmPwd" class="gg-input"
                   placeholder="Repeat your password" autocomplete="new-password"
                   style="padding-right:42px;">
            <button type="button" class="pwd-eye" onclick="togglePwd('confirmPwd',this)">👁</button>
          </div>
          <div class="field-err" id="err-confirm"></div>
        </div>

        <!-- ══ 5. TERMS & PRIVACY ══ -->
        <div class="mb-5 su su-6" id="termsRow">
          <div class="flex items-start gap-3">
            <div class="mt-0.5 flex-shrink-0" onclick="toggleTerms()" style="cursor:pointer;">
              <div class="cb-visual <?= !empty($old['terms']??'') ? 'checked' : '' ?>" id="cbVisual">
                <span id="cbCheck" style="<?= empty($old['terms']??'') ? 'display:none' : '' ?>">✓</span>
              </div>
              <input type="checkbox" name="terms" id="termsInput" value="1"
                     style="position:absolute;opacity:0;width:1px;height:1px;"
                     <?= !empty($old['terms']??'') ? 'checked' : '' ?>>
            </div>
            <label for="termsInput" class="text-[12.5px] leading-relaxed" style="color:var(--tx-2);cursor:pointer;"
                   onclick="event.preventDefault();toggleTerms()">
              I agree to GigGhana's
              <a href="<?= APP_URL ?>/terms.php" target="_blank" onclick="event.stopPropagation()"
                 class="font-semibold hover:underline" style="color:var(--cyan);">Terms of Service</a>
              and
              <a href="<?= APP_URL ?>/privacy.php" target="_blank" onclick="event.stopPropagation()"
                 class="font-semibold hover:underline" style="color:var(--cyan);">Privacy Policy</a>.
              I confirm I am 18+ and a legal resident of Ghana.
            </label>
          </div>
          <div class="field-err" id="err-terms"></div>
        </div>

        <!-- ══ 6. CREATE ACCOUNT BUTTON ══ -->
        <div class="su su-7">
          <button type="submit" class="submit-btn" id="submitBtn">
            <svg id="btnSpinner" class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:none;">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"></circle>
              <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".8"></path>
            </svg>
            <span id="btnLabel">Create My Account →</span>
          </button>
        </div>

        <!-- Trust strip -->
        <div class="flex items-center justify-center gap-4 mt-3.5 su su-7">
          <div class="flex items-center gap-1.5 text-[10.5px]" style="color:var(--tx-3);">🆓 Free</div>
          <div class="w-px h-3" style="background:var(--bd);"></div>
          <div class="flex items-center gap-1.5 text-[10.5px]" style="color:var(--tx-3);">🔒 SSL secured</div>
          <div class="w-px h-3" style="background:var(--bd);"></div>
          <div class="flex items-center gap-1.5 text-[10.5px]" style="color:var(--tx-3);">✅ No card needed</div>
          <div class="w-px h-3" style="background:var(--bd);"></div>
          <div class="flex items-center gap-1.5 text-[10.5px]" style="color:var(--tx-3);">🇬🇭 Ghana only</div>
        </div>
      </form>

      <!-- ══ 7. LINK TO LOGIN PAGE ══ -->
      <div class="text-center text-[13.5px] mt-6 su su-8" style="color:var(--tx-2);">
        Already have an account?
        <a href="<?= APP_URL ?>/auth/login.php" class="font-bold ml-0.5 hover:underline" style="color:var(--cyan);">Sign in to GigGhana →</a>
      </div>

    </div><!-- /form inner -->
  </main>

</div><!-- /page grid -->

<script>
(function(){
'use strict';

/* ══════════════════════════════════════════════════════
   THEME SYNC — mirrors index.php exactly
   Key: 'gg_theme'  |  Dark class: none  |  Light class: body.lm
   Listens to storage events so toggling on index tab
   instantly reflects here with no reload.
══════════════════════════════════════════════════════ */
function applyTheme(isLight) {
  document.body.classList.toggle('lm', isLight);
  // Update both toggle buttons (mobile + desktop)
  document.querySelectorAll('#themeBtn, #themeBtnDesktop').forEach(btn => {
    if (btn) btn.textContent = isLight ? '☀️' : '🌙';
  });
}

// Init from stored preference (already applied pre-paint, just sync the button icon)
(function initTheme() {
  const isLight = localStorage.getItem('gg_theme') === 'light';
  applyTheme(isLight);
})();

// Toggle — identical logic to index.php toggleTheme()
window.toggleTheme = function() {
  const nowLight = !document.body.classList.contains('lm');
  localStorage.setItem('gg_theme', nowLight ? 'light' : 'dark');
  applyTheme(nowLight);
};

// Cross-tab sync — if user toggles on index.php, register updates instantly
window.addEventListener('storage', function(e) {
  if (e.key === 'gg_theme') {
    applyTheme(e.newValue === 'light');
  }
});

/* ══════════════════════════════════════════════════════
   ROLE SELECTION
══════════════════════════════════════════════════════ */
window.setRole = function(role) {
  document.getElementById('roleInput').value = role;
  document.getElementById('rc-client').classList.toggle('active', role === 'client');
  document.getElementById('rc-provider').classList.toggle('active', role === 'provider');
  // Update social auth hrefs
  ['googleBtn','facebookBtn'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.href = el.href.replace(/role=[^&]*/, 'role=' + role);
  });
};

/* ══════════════════════════════════════════════════════
   TERMS CHECKBOX
══════════════════════════════════════════════════════ */
let termsChecked = <?= !empty($old['terms']??'') ? 'true' : 'false' ?>;
window.toggleTerms = function() {
  termsChecked = !termsChecked;
  document.getElementById('cbVisual').classList.toggle('checked', termsChecked);
  document.getElementById('cbCheck').style.display = termsChecked ? '' : 'none';
  document.getElementById('termsInput').checked    = termsChecked;
  if (termsChecked) showErr('err-terms', '');
};

/* ══════════════════════════════════════════════════════
   PASSWORD VISIBILITY
══════════════════════════════════════════════════════ */
window.togglePwd = function(id, btn) {
  const el = document.getElementById(id);
  el.type  = el.type === 'password' ? 'text' : 'password';
  btn.textContent = el.type === 'password' ? '👁' : '🙈';
};

/* ══════════════════════════════════════════════════════
   VALIDATION HELPERS
══════════════════════════════════════════════════════ */
function setValid(el, errId, statusId) {
  el.classList.remove('is-error'); el.classList.add('is-valid');
  showErr(errId, '');
  const s = statusId ? document.getElementById(statusId) : null;
  if (s) { s.textContent = '✓'; s.style.color = 'var(--green)'; s.style.opacity = '1'; }
}
function setInvalid(el, errId, msg, statusId) {
  el.classList.remove('is-valid'); el.classList.add('is-error');
  showErr(errId, '⚠ ' + msg);
  const s = statusId ? document.getElementById(statusId) : null;
  if (s) { s.textContent = '✕'; s.style.color = 'var(--red)'; s.style.opacity = '1'; }
}
function clearState(el, statusId) {
  el.classList.remove('is-valid','is-error');
  const s = statusId ? document.getElementById(statusId) : null;
  if (s) { s.textContent = ''; s.style.opacity = '0'; }
}
function showErr(id, msg) {
  const e = document.getElementById(id);
  if (!e) return;
  if (msg) { e.textContent = msg; e.classList.add('show'); }
  else     { e.textContent = ''; e.classList.remove('show'); }
}

/* ── Name fields ── */
function wireNameField(id, errId, statusId, label) {
  const el = document.getElementById(id);
  el.addEventListener('blur', function() {
    if (!this.value.trim()) { clearState(this, statusId); return; }
    if (this.value.trim().length < 2) setInvalid(this, errId, label + ' must be at least 2 characters.', statusId);
    else setValid(this, errId, statusId);
  });
  el.addEventListener('input', function() {
    if (this.classList.contains('is-error') && this.value.trim().length >= 2)
      setValid(this, errId, statusId);
  });
}
wireNameField('firstName', 'err-first', 'fs-first', 'First name');
wireNameField('lastName',  'err-last',  'fs-last',  'Last name');

/* ── Email ── */
const emailEl = document.getElementById('email');
const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
emailEl.addEventListener('blur', function() {
  if (!this.value.trim()) { clearState(this, 'fs-email'); return; }
  if (!emailRx.test(this.value.trim()))
    setInvalid(this, 'err-email', 'Enter a valid email address.', 'fs-email');
  else setValid(this, 'err-email', 'fs-email');
});
emailEl.addEventListener('input', function() {
  if (this.classList.contains('is-error') && emailRx.test(this.value.trim()))
    setValid(this, 'err-email', 'fs-email');
});

/* ── Password strength ── */
const pwdEl  = document.getElementById('password');
const swrap  = document.getElementById('strengthWrap');
const segs   = [1,2,3,4].map(i => document.getElementById('seg'+i));
const strLbl = document.getElementById('strLabel');
const strPct = document.getElementById('strPct');
const strMeta = [
  {label:'Too weak', cls:'s1', pct:'25%'},
  {label:'Weak',     cls:'s2', pct:'50%'},
  {label:'Good',     cls:'s3', pct:'75%'},
  {label:'Strong ✓', cls:'s4', pct:'100%'},
];
const pwdRules = [
  {id:'rule-len',     test:v => v.length >= 8},
  {id:'rule-upper',   test:v => /[A-Z]/.test(v)},
  {id:'rule-num',     test:v => /[0-9]/.test(v)},
  {id:'rule-special', test:v => /[^A-Za-z0-9]/.test(v)},
];

pwdEl.addEventListener('input', function() {
  const v = this.value;
  if (!v) { swrap.style.display='none'; return; }
  swrap.style.display = 'block';
  let score = 0;
  pwdRules.forEach(r => {
    const met = r.test(v);
    const rEl = document.getElementById(r.id);
    rEl.classList.toggle('met', met);
    const rc = rEl.querySelector('.rule-c');
    if (rc) rc.textContent = met ? '✓' : '';
    if (met) score++;
  });
  segs.forEach((s,i) => {
    s.className = 'str-seg';
    if (i < score) s.classList.add(strMeta[score-1].cls);
  });
  const m = strMeta[Math.max(0, score-1)];
  strLbl.textContent = score === 0 ? 'Enter password' : m.label;
  strPct.textContent = score === 0 ? '0%' : m.pct;
  if (this.classList.contains('is-error') && score >= 3) setValid(this,'err-pwd',null);
});

pwdEl.addEventListener('blur', function() {
  if (!this.value) { clearState(this, null); return; }
  const v = this.value;
  if (v.length < 8)            setInvalid(this,'err-pwd','Must be at least 8 characters.',null);
  else if (!/[A-Z]/.test(v))  setInvalid(this,'err-pwd','Must contain an uppercase letter.',null);
  else if (!/[0-9]/.test(v))  setInvalid(this,'err-pwd','Must contain a number.',null);
  else setValid(this,'err-pwd',null);
});

/* ── Confirm password ── */
const confEl = document.getElementById('confirmPwd');
function checkConf() {
  if (!confEl.value) { clearState(confEl, null); return; }
  if (confEl.value !== pwdEl.value)
    setInvalid(confEl,'err-confirm','Passwords do not match.',null);
  else setValid(confEl,'err-confirm',null);
}
confEl.addEventListener('blur', checkConf);
confEl.addEventListener('input', checkConf);

/* ── Pre-validate on PHP error reload ── */
<?php if (!empty($errors)): ?>
['firstName','lastName','email','password','confirmPwd'].forEach(id => {
  const el = document.getElementById(id);
  if (el && el.value) el.dispatchEvent(new Event('blur'));
});
<?php endif; ?>

/* ── Form submit ── */
document.getElementById('regForm').addEventListener('submit', function(e) {
  let bad = false;
  const fn = document.getElementById('firstName');
  const ln = document.getElementById('lastName');
  const em = document.getElementById('email');
  const pw = document.getElementById('password');
  const cf = document.getElementById('confirmPwd');

  if (fn.value.trim().length < 2) { setInvalid(fn,'err-first','First name is required.','fs-first'); bad=true; }
  if (ln.value.trim().length < 2) { setInvalid(ln,'err-last','Last name is required.','fs-last');   bad=true; }
  if (!emailRx.test(em.value.trim())) { setInvalid(em,'err-email','Enter a valid email.','fs-email'); bad=true; }
  if (pw.value.length<8 || !/[A-Z]/.test(pw.value) || !/[0-9]/.test(pw.value)) {
    setInvalid(pw,'err-pwd','Password does not meet requirements.',null); bad=true;
  }
  if (cf.value !== pw.value) { setInvalid(cf,'err-confirm','Passwords do not match.',null); bad=true; }

  if (!termsChecked) {
    showErr('err-terms','⚠ Please accept the Terms of Service to continue.');
    const tr = document.getElementById('termsRow');
    tr.style.outline     = '1.5px solid var(--red)';
    tr.style.borderRadius = '10px';
    setTimeout(() => { tr.style.outline = ''; }, 2000);
    bad = true;
  } else {
    showErr('err-terms','');
  }

  if (bad) { e.preventDefault(); return; }

  // Loading state
  document.getElementById('submitBtn').disabled = true;
  document.getElementById('btnSpinner').style.display = 'block';
  document.getElementById('btnLabel').textContent = 'Creating account…';
});

})();
</script>
</body>
</html>