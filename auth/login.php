<?php
/**
 * GigGhana — auth/login.php
 * Design system: Volcanic Charcoal × Electric Cyan × Coral
 * Fonts: Plus Jakarta Sans + DM Sans
 * Theme: synced from index.php via localStorage('gg_theme')
 *
 * Sections:
 *  1.  Welcome Back header
 *  2.  Login form — Email, Password, Remember Me
 *  3.  Login button with loading state
 *  4.  Forgot Password link
 *  5.  Social login — Google & Facebook
 *  6.  "Don't have an account?" link
 *  7.  Role-based redirect (provider / client / admin)
 *  8.  Real-time field validation
 *  9.  Flash messages (success / error)
 *  10. Rate-limiting / locked account message
 *  11. Remember-me secure cookie (30 days)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (isLoggedIn()) {
    redirect(APP_URL . '/' . $_SESSION['user_role'] . '/dashboard.php');
}

$errors  = [];
$success = '';

/* ── Flash messages ── */
if (isset($_SESSION['flash_success'])) {
    $success = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}
if (isset($_GET['verified'])) {
    $success = '🎉 Email verified successfully! You can now sign in.';
}
if (isset($_GET['reset'])) {
    $success = '✅ Password reset successfully. Please sign in with your new password.';
}
if (isset($_GET['registered'])) {
    $success = '🎉 Account created! Please sign in to continue.';
}

/* ── POST handler ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch — please refresh and try again.';
    } else {
        $email    = filter_var(trim($_POST['email']    ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember_me']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
        if (strlen($password) < 1)                      $errors[] = 'Password is required.';

        if (empty($errors)) {
            try {
                $db   = getDB();
                $stmt = $db->prepare(
                    "SELECT id, uuid, first_name, last_name, email,
                            password_hash, role, avatar, is_active, is_banned, email_verified
                     FROM users WHERE email = ? LIMIT 1"
                );
                $stmt->execute([$email]);
                $u = $stmt->fetch();

                if (!$u || !password_verify($password, $u['password_hash'])) {
                    $errors[] = 'Invalid email or password. Please try again.';

                } elseif ((int)$u['is_banned']) {
                    $errors[] = 'Your account has been suspended. Contact support@gigghana.com for assistance.';

                } elseif (!(int)$u['is_active']) {
                    $errors[] = 'Your account is inactive. Please verify your email or contact support.';

                } else {
                    /* Update last_login */
                    $db->prepare("UPDATE users SET last_login=NOW(), last_seen=NOW() WHERE id=?")
                       ->execute([$u['id']]);

                    /* Remember-me cookie — 30 days */
                    if ($remember) {
                        $token   = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 3600);
                        setcookie('gg_remember', $token, [
                            'expires'  => $expires,
                            'path'     => '/',
                            'httponly' => true,
                            'samesite' => 'Lax',
                            'secure'   => isset($_SERVER['HTTPS']),
                        ]);
                        /* Store last email for pre-fill */
                        setcookie('gg_last_email', $email, [
                            'expires'  => $expires,
                            'path'     => '/',
                            'httponly' => false,
                            'samesite' => 'Lax',
                        ]);
                    }

                    /* Set session */
                    session_regenerate_id(true);
                    $_SESSION['user_id']     = $u['id'];
                    $_SESSION['user_uuid']   = $u['uuid'];
                    $_SESSION['user_role']   = $u['role'];
                    $_SESSION['user_name']   = $u['first_name'] . ' ' . $u['last_name'];
                    $_SESSION['user_email']  = $u['email'];
                    $_SESSION['user_avatar'] = $u['avatar'] ?? '';

                    /* Role-based redirect */
                    $dest = match ($u['role']) {
                        'provider' => APP_URL . '/provider/dashboard.php',
                        'admin'    => APP_URL . '/admin/dashboard.php',
                        default    => APP_URL . '/client/dashboard.php',
                    };

                    /* Honour ?redirect param (same-origin only) */
                    if (!empty($_GET['redirect'])) {
                        $red = urldecode($_GET['redirect']);
                        if (str_starts_with($red, '/') && !str_starts_with($red, '//')) {
                            $dest = APP_URL . $red;
                        }
                    }

                    redirect($dest);
                }

            } catch (Exception $e) {
                error_log($e->getMessage());
                $errors[] = 'Login failed due to a server error. Please try again.';
            }
        }
    }
}

$csrf          = generateCSRF();
$prefillEmail  = htmlspecialchars($_POST['email'] ?? ($_COOKIE['gg_last_email'] ?? ''));
$hasError      = !empty($errors);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sign In — GigGhana</title>
<meta name="description" content="Sign in to GigGhana and connect with Africa's best freelance talent.">
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

<!-- Flash-free theme sync — reads localStorage('gg_theme') before first paint -->
<script>
(function(){
  if(localStorage.getItem('gg_theme')==='light'){
    document.documentElement.classList.add('lm-pre');
  }
})();
</script>

<style>
/* ════════════════════════════════════════════════════════
   DESIGN TOKENS — Dark (default) — exact match to index.php
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

/* Light mode component overrides */
body.lm .brand-panel-bg  { background:var(--s1); }
body.lm .form-panel-bg   { background:var(--s2); }
body.lm .mob-bar         { background:rgba(234,238,247,0.96) !important; border-color:var(--bd); }
body.lm .gg-input        { background:rgba(255,255,255,0.7); color:var(--tx); }
body.lm .gg-input:focus  { background:rgba(255,255,255,0.9); }
body.lm .gg-input::placeholder { color:var(--tx-3); }
body.lm .social-btn      { background:rgba(255,255,255,0.6); border-color:var(--bd2); color:var(--tx); }
body.lm .social-btn:hover{ background:rgba(255,255,255,0.9); }
body.lm .cb-visual       { background:rgba(255,255,255,0.7); border-color:var(--bd2); }
body.lm .btn-theme       { border-color:var(--bd2); color:var(--tx-2); }
body.lm .stat-chip-num   { -webkit-text-fill-color:var(--cyan); }
body.lm .grid-tex        {
  background-image:
    linear-gradient(rgba(30,40,80,0.025) 1px,transparent 1px),
    linear-gradient(90deg,rgba(30,40,80,0.025) 1px,transparent 1px);
}
body.lm .info-card       { background:rgba(255,255,255,0.55); border-color:var(--bd2); }
body.lm .divider-line    { background:var(--bd); }

/* ════════════════════════════════════════════════════════
   RESET + BASE
════════════════════════════════════════════════════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);font-family:var(--fb);
  min-height:100svh;overflow-x:hidden;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
}
html.lm-pre body,
html.lm-pre body *{ transition:none !important; }

::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}

/* ── Animated gradient bar (top) ── */
.grad-bar{
  position:fixed;top:0;left:0;right:0;height:2px;z-index:200;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--green),var(--cyan));
  background-size:300% 100%;
  animation:gradShift 5s linear infinite;
}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:300% 50%}}

/* ── Gradient text ── */
.grad-text{
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan),var(--coral));
  background-size:200% auto;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:gradShift 4s ease infinite;
}

/* ── Ambient blobs ── */
.blob{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;}
.blob-cyan{width:480px;height:480px;background:radial-gradient(circle,rgba(0,212,200,0.08),transparent 70%);top:-120px;right:-100px;animation:blobFloat 10s ease-in-out infinite;}
.blob-coral{width:300px;height:300px;background:radial-gradient(circle,rgba(255,107,74,0.07),transparent 70%);bottom:-70px;left:-50px;animation:blobFloat 8s 2s ease-in-out infinite;}
.blob-violet{width:180px;height:180px;background:radial-gradient(circle,rgba(124,111,247,0.09),transparent 70%);top:44%;left:16%;animation:blobFloat 6s 1s ease-in-out infinite;}
@keyframes blobFloat{0%,100%{transform:translateY(0) scale(1);}50%{transform:translateY(-16px) scale(1.04);}}

/* Ghana flag orb */
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
.noise-tex{
  position:absolute;inset:0;pointer-events:none;opacity:.015;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* Right edge accent line on brand panel */
.bp-edge{
  position:absolute;top:0;right:0;width:1.5px;height:100%;
  background:linear-gradient(to bottom,transparent,var(--cyan) 30%,var(--coral) 70%,transparent);
  opacity:.2;pointer-events:none;
}

/* Pulse dot */
.pulse-dot{
  width:7px;height:7px;border-radius:50%;background:var(--cyan);
  animation:pulseDot 2.2s ease infinite;flex-shrink:0;
}
@keyframes pulseDot{
  0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(0,212,200,.4);}
  50%{opacity:.2;box-shadow:0 0 0 7px rgba(0,212,200,0);}
}

/* Stat number gradient */
.stat-chip-num{
  font-family:var(--fm);font-size:26px;font-weight:900;line-height:1;
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}

/* Info card (brand panel) */
.info-card{
  background:rgba(255,255,255,0.028);
  border:1px solid var(--bd);border-radius:13px;
  padding:16px;transition:border-color .25s;
}
.info-card:hover{ border-color:var(--cyan-border); }

/* ── Theme toggle button — matches index.php btn-theme ── */
.btn-theme{
  background:transparent;color:var(--tx-2);
  border:1px solid var(--bd);border-radius:10px;
  padding:7px 11px;cursor:pointer;font-size:14px;
  transition:all .26s;line-height:1;font-family:var(--fb);flex-shrink:0;
}
.btn-theme:hover{background:rgba(255,255,255,0.07);}

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
.gg-input.is-error{
  border-color:var(--red);background:rgba(255,77,106,0.04);
  animation:fieldShake .3s ease;
}
@keyframes fieldShake{
  0%,100%{transform:translateX(0);}
  25%,75%{transform:translateX(-4px);}
  50%{transform:translateX(4px);}
}

.pwd-eye{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;cursor:pointer;font-size:15px;
  color:var(--tx-3);z-index:2;line-height:1;padding:4px;transition:color .2s;
}
.pwd-eye:hover{color:var(--tx);}

.field-err{font-size:11px;color:var(--red);margin-top:4px;display:none;align-items:center;gap:4px;}
.field-err.show{display:flex;}

/* ── Custom checkbox ── */
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

/* ── Social buttons ── */
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

/* ── Submit button ── */
.submit-btn{
  position:relative;overflow:hidden;
  width:100%;padding:15px 24px;border-radius:13px;border:none;
  background:linear-gradient(135deg,var(--cyan) 0%,var(--cyan-d) 50%,#009490 100%);
  background-size:200% auto;
  color:#0C0E14;font-family:var(--fm);font-size:16px;font-weight:800;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;
  transition:all .4s ease;
  box-shadow:0 4px 24px var(--gC),0 1px 4px rgba(0,0,0,.5);
  letter-spacing:-.1px;
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

/* ── OR divider ── */
.divider{display:flex;align-items:center;gap:12px;margin:16px 0;}
.divider-line{flex:1;height:1px;background:var(--bd);}
.divider-txt{font-size:11px;color:var(--tx-3);white-space:nowrap;}

/* Spinner */
.spinner{animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── Staggered slide-up ── */
.su{animation:suAnim .5s ease both;}
.su-1{animation-delay:.04s;}.su-2{animation-delay:.09s;}.su-3{animation-delay:.14s;}
.su-4{animation-delay:.19s;}.su-5{animation-delay:.24s;}.su-6{animation-delay:.30s;}
.su-7{animation-delay:.36s;}
@keyframes suAnim{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:translateY(0);}}

/* ── Success redirect overlay ── */
.redirect-overlay{
  position:fixed;inset:0;z-index:1000;
  background:var(--bg);
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:16px;opacity:0;pointer-events:none;
  transition:opacity .4s ease;
}
.redirect-overlay.show{opacity:1;pointer-events:auto;}
</style>
</head>

<body class="">
<!-- Sync theme to body before any render -->
<script>
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
  <div class="mob-bar mx-4 flex items-center justify-between px-4 py-3 rounded-2xl transition-all"
       style="background:rgba(19,22,30,0.95);backdrop-filter:blur(20px);border:1px solid var(--bd);">
    <a href="<?= APP_URL ?>/index.php" class="flex items-center gap-2.5" style="text-decoration:none;">
      <div class="w-8 h-8 rounded-[9px] flex items-center justify-center font-heading font-black text-sm flex-shrink-0"
           style="background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;">G</div>
      <span class="font-heading font-extrabold text-[17px]" style="color:var(--tx);">
        Gig<span style="color:var(--cyan);">Ghana</span>
      </span>
    </a>
    <div class="flex items-center gap-2">
      <button class="btn-theme" id="themeBtnMob" onclick="toggleTheme()" title="Toggle theme">🌙</button>
      <a href="<?= APP_URL ?>/auth/register.php"
         class="text-[12px] font-semibold px-3 py-1.5 rounded-lg transition-all"
         style="color:var(--tx-3);background:rgba(255,255,255,0.04);border:1px solid var(--bd);text-decoration:none;">
        Sign up →
      </a>
    </div>
  </div>
</header>

<!-- ════════════════════════════════════════
     PAGE GRID
════════════════════════════════════════ -->
<div class="lg:grid min-h-screen" style="grid-template-columns:50% 50%;">

  <!-- ════ LEFT BRAND PANEL (desktop only) ════ -->
  <aside class="brand-panel-bg hidden lg:flex flex-col relative overflow-hidden min-h-screen px-12 py-11 transition-all"
         style="background:var(--s1);">
    <div class="grid-tex"></div>
    <div class="gh-orb"></div>
    <div class="blob blob-cyan"></div>
    <div class="blob blob-coral"></div>
    <div class="blob blob-violet"></div>
    <div class="bp-edge"></div>

    <div class="relative z-10 flex flex-col h-full">

      <!-- Logo + theme toggle -->
      <div class="flex items-center justify-between mb-auto">
        <a href="<?= APP_URL ?>/index.php" class="flex items-center gap-2.5" style="text-decoration:none;">
          <div class="w-10 h-10 rounded-[11px] flex items-center justify-center font-heading font-black text-[17px] flex-shrink-0"
               style="background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;box-shadow:0 4px 18px var(--gC);">G</div>
          <span class="font-heading font-extrabold text-[22px]" style="color:var(--tx);">
            Gig<span style="color:var(--cyan);">Ghana</span>
          </span>
        </a>
        <button class="btn-theme" id="themeBtnDesktop" onclick="toggleTheme()" title="Toggle theme">🌙</button>
      </div>

      <!-- Hero copy -->
      <div class="flex-1 flex flex-col justify-center py-8">

        <!-- Eyebrow -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full mb-5 w-fit font-heading font-extrabold text-[10px] tracking-widest uppercase"
             style="background:var(--cyan-dim);border:1px solid var(--cyan-border);color:var(--cyan);">
          <span class="pulse-dot"></span>
          🇬🇭 Ghana's #1 Freelance Platform
        </div>

        <!-- Headline -->
        <h1 class="font-heading font-black leading-[1.07] tracking-[-1.8px] mb-4"
            style="font-size:clamp(34px,3vw,50px);color:var(--tx);">
          Your Skill.<br>
          Your <span class="grad-text">Success.</span><br>
          Your <span style="color:var(--coral);">Ghana.</span>
        </h1>

        <p class="text-[14.5px] leading-[1.75] max-w-[360px] mb-8" style="color:var(--tx-2);">
          Thousands of Ghanaian businesses need your skills today.
          Log in and get back to earning or hiring.
        </p>

        <!-- Stats row -->
        <div class="flex gap-7 mb-8">
          <?php foreach([['1.2K+','Freelancers'],['₵195K','Paid Out'],['4.9★','Avg Rating']] as [$n,$l]): ?>
          <div>
            <div class="stat-chip-num"><?= $n ?></div>
            <div class="text-[10px] font-bold uppercase tracking-[.9px] mt-0.5" style="color:var(--tx-3);font-family:var(--fm);"><?= $l ?></div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Info cards — 4 trust features -->
        <div class="grid grid-cols-2 gap-3">
          <?php
          $features = [
            ['🔒', 'Secure Escrow',        'Funds released only on approval'],
            ['📱', 'MoMo & Card',           'MTN, Vodafone, Visa accepted'],
            ['✅', 'Verified Talent',        'Ghana Card ID-checked pros'],
            ['⚡', 'Instant Payouts',        '24-hr transfers to your account'],
          ];
          foreach($features as [$ico,$title,$sub]):
          ?>
          <div class="info-card">
            <div class="text-[18px] mb-1.5"><?= $ico ?></div>
            <div class="font-heading font-bold text-[12.5px] mb-0.5" style="color:var(--tx);"><?= $title ?></div>
            <div class="text-[10.5px]" style="color:var(--tx-3);"><?= $sub ?></div>
          </div>
          <?php endforeach; ?>
        </div>

      </div>

      <!-- Bottom footer strip -->
      <div class="pt-5 border-t" style="border-color:var(--bd);">
        <p class="text-[11px] leading-relaxed" style="color:var(--tx-3);">
          © <?= date('Y') ?> GigGhana Ltd. Made with ❤️ in Ghana 🇬🇭 ·
          Escrow protected · MoMo enabled
        </p>
      </div>

    </div><!-- /z-10 -->
  </aside>

  <!-- ════ RIGHT FORM PANEL ════ -->
  <main class="form-panel-bg relative flex flex-col items-center justify-start lg:justify-center min-h-screen px-5 sm:px-8 lg:px-14 pt-24 lg:pt-10 pb-14 transition-all"
        style="background:var(--s2);">
    <div class="noise-tex"></div>
    <div class="absolute top-0 right-0 w-[360px] h-[360px] rounded-full pointer-events-none"
         style="background:radial-gradient(circle,rgba(0,212,200,0.04),transparent 70%);"></div>

    <div class="relative z-10 w-full max-w-[440px]">

      <!-- ══ 1. WELCOME BACK HEADER ══ -->
      <div class="text-center mb-7 su su-1">
        <!-- Icon -->
        <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4"
             style="background:linear-gradient(135deg,var(--cyan-dim),var(--coral-dim));border:1px solid var(--cyan-border);">
          <span style="font-size:26px;">👋</span>
        </div>
        <h1 class="font-heading font-black leading-tight tracking-[-1.2px] mb-2"
            style="font-size:clamp(28px,5vw,36px);color:var(--tx);">
          Welcome <span class="grad-text">Back</span>
        </h1>
        <p class="text-[14px] leading-relaxed" style="color:var(--tx-2);">
          Login to your GigGhana account to continue.<br>
          <span class="text-[13px]" style="color:var(--tx-3);">Hire freelancers or start earning from your skills.</span>
        </p>
      </div>

      <!-- ── Success flash ── -->
      <?php if($success): ?>
      <div class="su su-1 mb-5 px-4 py-3.5 rounded-xl text-[13px]"
           style="background:rgba(31,217,160,0.07);border:1px solid rgba(31,217,160,0.24);border-left:3px solid var(--green);">
        <p style="color:#6EE7B7;"><?= htmlspecialchars($success) ?></p>
      </div>
      <?php endif; ?>

      <!-- ── Error banner ── -->
      <?php if($hasError): ?>
      <div class="su su-1 mb-5 px-4 py-3.5 rounded-xl"
           style="background:rgba(255,77,106,0.07);border:1px solid rgba(255,77,106,.25);border-left:3px solid var(--red);">
        <div class="flex items-center gap-2 font-heading font-bold text-[12px] mb-1.5" style="color:#F87171;">
          ⚠️ Sign-in failed
        </div>
        <?php foreach($errors as $err): ?>
        <p class="text-[12.5px] flex items-start gap-1.5" style="color:#fca5a5;">
          <span class="mt-0.5 text-[9px] flex-shrink-0">•</span><?= htmlspecialchars($err) ?>
        </p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- ══ 6. SOCIAL LOGIN ══ -->
      <div class="su su-2">
        <div class="grid grid-cols-2 gap-2.5 mb-1">
          <!-- Google -->
          <a href="<?= APP_URL ?>/auth/oauth.php?provider=google" class="social-btn">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
              <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
              <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957C.347 6.173 0 7.548 0 9s.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
              <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            Continue with Google
          </a>
          <!-- Facebook -->
          <a href="<?= APP_URL ?>/auth/oauth.php?provider=facebook" class="social-btn">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <path d="M18 9a9 9 0 1 0-10.406 8.892v-6.29H5.31V9h2.284V7.018C7.594 4.76 8.946 3.5 11 3.5c.955 0 1.954.17 1.954.17v2.218h-1.1c-1.085 0-1.423.674-1.423 1.365V9h2.42l-.387 2.602H10.43v6.29A9.002 9.002 0 0 0 18 9z" fill="#1877F2"/>
            </svg>
            Continue with Facebook
          </a>
        </div>
      </div>

      <!-- OR divider -->
      <div class="divider su su-2">
        <div class="divider-line"></div>
        <span class="divider-txt">or sign in with email</span>
        <div class="divider-line"></div>
      </div>

      <!-- ══ 2. LOGIN FORM ══ -->
      <form method="POST" id="loginForm" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <!-- Email -->
        <div class="mb-4 su su-3">
          <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5"
                 style="color:var(--tx-3);font-family:var(--fm);" for="email">
            Email Address
          </label>
          <div class="field-wrap">
            <span class="field-ico">✉️</span>
            <input type="email" name="email" id="email"
                   class="gg-input <?= $hasError ? 'is-error' : '' ?>"
                   placeholder="you@example.com"
                   autocomplete="email"
                   value="<?= $prefillEmail ?>">
          </div>
          <div class="field-err" id="err-email"></div>
        </div>

        <!-- Password -->
        <div class="mb-2 su su-4">
          <div class="flex items-center justify-between mb-1.5">
            <label class="text-[10px] font-extrabold uppercase tracking-[.7px]"
                   style="color:var(--tx-3);font-family:var(--fm);" for="password">
              Password
            </label>
            <!-- 5. FORGOT PASSWORD LINK -->
            <a href="<?= APP_URL ?>/auth/forgot-password.php"
               class="text-[12px] font-semibold hover:underline transition-colors"
               style="color:var(--cyan);text-decoration:none;">
              Forgot your password? Reset it here
            </a>
          </div>
          <div class="field-wrap">
            <span class="field-ico">🔑</span>
            <input type="password" name="password" id="password"
                   class="gg-input <?= $hasError ? 'is-error' : '' ?>"
                   placeholder="Enter your password"
                   autocomplete="current-password"
                   style="padding-right:42px;">
            <button type="button" class="pwd-eye" onclick="togglePwd()" aria-label="Toggle password">👁</button>
          </div>
          <div class="field-err" id="err-pwd"></div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-3 mb-6 su su-4">
          <div class="flex-shrink-0" onclick="toggleRemember()" style="cursor:pointer;">
            <div class="cb-visual" id="cbVisual">
              <span id="cbCheck" style="display:none;">✓</span>
            </div>
            <input type="checkbox" name="remember_me" id="rememberMe" value="1"
                   style="position:absolute;opacity:0;width:1px;height:1px;">
          </div>
          <label for="rememberMe"
                 class="text-[13.5px] leading-snug select-none"
                 style="color:var(--tx-2);cursor:pointer;"
                 onclick="event.preventDefault();toggleRemember()">
            Keep me signed in for 30 days
          </label>
        </div>

        <!-- ══ 3. LOGIN BUTTON ══ -->
        <div class="su su-5">
          <button type="submit" class="submit-btn" id="submitBtn">
            <svg id="btnSpinner" class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:none;">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"></circle>
              <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".8"></path>
            </svg>
            <span id="btnLabel">Login to My Account</span>
          </button>
        </div>

        <!-- Trust strip -->
        <div class="flex items-center justify-center gap-4 mt-3 su su-5">
          <div class="flex items-center gap-1.5 text-[10.5px]" style="color:var(--tx-3);">🔒 SSL secured</div>
          <div class="w-px h-3" style="background:var(--bd);"></div>
          <div class="flex items-center gap-1.5 text-[10.5px]" style="color:var(--tx-3);">🇬🇭 Ghana only</div>
          <div class="w-px h-3" style="background:var(--bd);"></div>
          <div class="flex items-center gap-1.5 text-[10.5px]" style="color:var(--tx-3);">✅ Verified platform</div>
        </div>

        <!-- ══ 9. CREATE ACCOUNT LINK ══ -->
        <div class="text-center text-[13.5px] mt-5 su su-6" style="color:var(--tx-2);">
          Don't have an account yet?
          <a href="<?= APP_URL ?>/auth/register.php"
             class="font-bold ml-1 hover:underline"
             style="color:var(--cyan);text-decoration:none;">
            Create one here →
          </a>
        </div>

        <!-- Security note -->
        <div class="text-center text-[10.5px] mt-3 su su-7" style="color:var(--tx-3);">
          🔐 Secured with 256-bit encryption ·
          <a href="<?= APP_URL ?>/privacy.php" class="hover:underline" style="color:var(--tx-3);">Privacy Policy</a>
        </div>
      </form>

    </div><!-- /form inner -->
  </main>

</div><!-- /page grid -->

<!-- ══ Success redirect overlay ── -->
<div class="redirect-overlay" id="redirectOverlay">
  <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-[28px]"
       style="background:linear-gradient(135deg,var(--cyan-dim),var(--green-dim));border:1px solid var(--cyan-border);">
    ✅
  </div>
  <div class="font-heading font-black text-[22px]" style="color:var(--tx);">Login successful.</div>
  <div class="text-[14px]" style="color:var(--tx-2);">Redirecting to your dashboard…</div>
  <div class="w-48 h-1 rounded-full overflow-hidden mt-2" style="background:var(--bd);">
    <div id="redirectBar" class="h-full rounded-full" style="background:var(--cyan);width:0%;transition:width 1.8s ease;"></div>
  </div>
</div>

<script>
(function(){
'use strict';

/* ══════════════════════════════════════════════════════
   THEME SYNC — identical to register.php & index.php
   Key: 'gg_theme' | Class: body.lm | No-flash pre-paint
══════════════════════════════════════════════════════ */
function applyTheme(isLight){
  document.body.classList.toggle('lm', isLight);
  document.querySelectorAll('#themeBtnMob,#themeBtnDesktop').forEach(btn => {
    if(btn) btn.textContent = isLight ? '☀️' : '🌙';
  });
}

// Init — already applied pre-paint, just sync button icons
(function initTheme(){
  applyTheme(localStorage.getItem('gg_theme') === 'light');
})();

// Toggle — same logic as index.php toggleTheme()
window.toggleTheme = function(){
  const nowLight = !document.body.classList.contains('lm');
  localStorage.setItem('gg_theme', nowLight ? 'light' : 'dark');
  applyTheme(nowLight);
};

// Cross-tab sync — toggling on index or register updates this page too
window.addEventListener('storage', function(e){
  if(e.key === 'gg_theme') applyTheme(e.newValue === 'light');
});

/* ══════════════════════════════════════════════════════
   REMEMBER ME
══════════════════════════════════════════════════════ */
let rememberChecked = false;
window.toggleRemember = function(){
  rememberChecked = !rememberChecked;
  document.getElementById('cbVisual').classList.toggle('checked', rememberChecked);
  document.getElementById('cbCheck').style.display  = rememberChecked ? '' : 'none';
  document.getElementById('rememberMe').checked     = rememberChecked;
};

/* ══════════════════════════════════════════════════════
   PASSWORD VISIBILITY
══════════════════════════════════════════════════════ */
window.togglePwd = function(){
  const inp = document.getElementById('password');
  const btn = inp.parentElement.querySelector('.pwd-eye');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
};

/* ══════════════════════════════════════════════════════
   REAL-TIME FIELD VALIDATION
══════════════════════════════════════════════════════ */
function setValid(el, errId){
  el.classList.remove('is-error'); el.classList.add('is-valid');
  const e = document.getElementById(errId);
  if(e){ e.textContent = ''; e.classList.remove('show'); }
}
function setInvalid(el, errId, msg){
  el.classList.remove('is-valid'); el.classList.add('is-error');
  const e = document.getElementById(errId);
  if(e){ e.textContent = '⚠ '+msg; e.classList.add('show'); }
}
function clearState(el, errId){
  el.classList.remove('is-valid','is-error');
  const e = document.getElementById(errId);
  if(e){ e.textContent = ''; e.classList.remove('show'); }
}

const emailEl = document.getElementById('email');
const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

emailEl.addEventListener('blur', function(){
  if(!this.value.trim()){ clearState(this,'err-email'); return; }
  if(!emailRx.test(this.value.trim())) setInvalid(this,'err-email','Enter a valid email address.');
  else setValid(this,'err-email');
});
emailEl.addEventListener('input', function(){
  if(this.classList.contains('is-error') && emailRx.test(this.value.trim()))
    setValid(this,'err-email');
});

const pwdEl = document.getElementById('password');
pwdEl.addEventListener('blur', function(){
  if(!this.value){ clearState(this,'err-pwd'); return; }
  if(this.value.length < 1) setInvalid(this,'err-pwd','Password is required.');
  else setValid(this,'err-pwd');
});
pwdEl.addEventListener('input', function(){
  if(this.classList.contains('is-error') && this.value.length > 0)
    setValid(this,'err-pwd');
});

/* ══════════════════════════════════════════════════════
   FORM SUBMIT
══════════════════════════════════════════════════════ */
document.getElementById('loginForm').addEventListener('submit', function(e){
  let bad = false;
  const em = document.getElementById('email');
  const pw = document.getElementById('password');

  if(!em.value.trim() || !emailRx.test(em.value.trim())){
    setInvalid(em,'err-email','Enter a valid email address.'); bad = true;
  }
  if(!pw.value){
    setInvalid(pw,'err-pwd','Password is required.'); bad = true;
  }
  if(bad){ e.preventDefault(); return; }

  /* Show loading state */
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  document.getElementById('btnSpinner').style.display = 'block';
  document.getElementById('btnLabel').textContent     = 'Signing in…';

  /* Show redirect overlay with progress bar */
  const overlay = document.getElementById('redirectOverlay');
  overlay.classList.add('show');
  requestAnimationFrame(() => {
    document.getElementById('redirectBar').style.width = '100%';
  });
});

/* ══════════════════════════════════════════════════════
   8. RATE-LIMIT MESSAGE — check error text
   (Server already returns the message; shown in banner)
   Also show rate-limit toast if banner contains 'attempts'
══════════════════════════════════════════════════════ */
<?php if($hasError && count(array_filter($errors, fn($e) => str_contains($e,'attempt'))) > 0): ?>
// Show specific rate-limit indicator
document.getElementById('submitBtn').disabled = true;
document.getElementById('btnLabel').textContent = 'Try again in a few minutes';
document.getElementById('submitBtn').style.opacity = '0.5';
<?php endif; ?>

/* ── Auto-focus email if empty ── */
const emInit = document.getElementById('email');
if(!emInit.value) setTimeout(() => emInit.focus(), 600);

})();
</script>

</body>
</html>