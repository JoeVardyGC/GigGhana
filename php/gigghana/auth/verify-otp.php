<?php
/**
 * GigGhana — auth/verify-otp.php
 * Design system: Volcanic Charcoal × Electric Cyan × Coral
 * Fonts: Plus Jakarta Sans + DM Sans
 * Theme: synced from index.php via localStorage('gg_theme')
 *
 * Session requirements:
 *   $_SESSION['pending_user_id']  — user ID awaiting verification
 *   $_SESSION['pending_email']    — email to display / send to
 *   $_SESSION['demo_otp']         — (dev only) raw OTP for hint
 *
 * Query params:
 *   ?type=register  — from registration flow
 *   ?type=email     — email not verified at login
 *   ?type=reset     — password reset flow
 *
 * OTP stored in: users.otp_code + users.otp_expires_at
 * On success: email_verified=1, otp cleared, redirect to login
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/* Guard — must have a pending session */
if (empty($_SESSION['pending_user_id'])) {
    redirect(APP_URL . '/auth/login.php');
}

$type   = in_array($_GET['type'] ?? '', ['register','email','reset']) ? $_GET['type'] : 'register';
$userId = (int)$_SESSION['pending_user_id'];
$email  = $_SESSION['pending_email'] ?? '';

if (!$userId || !$email) {
    redirect(APP_URL . '/auth/login.php');
}

$errors  = [];
$success = '';

/* ══════════════════════════════════════════
   POST HANDLER
══════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid. Please refresh and try again.';
    } else {
        $action = $_POST['action'] ?? 'verify';

        /* ── RESEND OTP ── */
        if ($action === 'resend') {
            $resendKey   = 'otp_resend_' . $userId;
            $resendCount = $_SESSION[$resendKey]['count'] ?? 0;
            $resendStart = $_SESSION[$resendKey]['start'] ?? 0;

            /* Reset window after 15 min */
            if (time() - $resendStart > 900) {
                $_SESSION[$resendKey] = ['count' => 0, 'start' => time()];
                $resendCount = 0;
            }

            if ($resendCount >= 3) {
                $wait = ceil((900 - (time() - $resendStart)) / 60);
                $errors[] = 'Too many resend attempts. Please wait ' . $wait . ' minute(s) before trying again.';
            } else {
                try {
                    $db  = getDB();
                    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $exp = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

                    $db->prepare(
                        "UPDATE users SET otp_code=?, otp_expires_at=? WHERE id=?"
                    )->execute([$otp, $exp, $userId]);

                    $_SESSION[$resendKey]['count'] = $resendCount + 1;
                    $_SESSION[$resendKey]['start'] = $_SESSION[$resendKey]['start'] ?: time();

                    /* Production: send via email/SMS here */
                    /* sendOtpEmail($email, $otp); */

                    /* Dev only — store for hint */
                    $_SESSION['demo_otp'] = $otp;

                    $success = 'A new 6-digit code has been sent to ' . $email . '.';

                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Could not send a new code. Please try again.';
                }
            }

        /* ── VERIFY OTP ── */
        } elseif ($action === 'verify') {

            /* Collect 6 individual digit inputs → single string */
            $digits = [];
            for ($i = 1; $i <= 6; $i++) {
                $digits[] = trim($_POST['d' . $i] ?? '');
            }
            $otp = implode('', $digits);

            if (!ctype_digit($otp) || strlen($otp) !== 6) {
                $errors[] = 'Please enter all 6 digits of the verification code.';
            } else {
                try {
                    $db   = getDB();
                    $stmt = $db->prepare(
                        "SELECT otp_code, otp_expires_at, email_verified, role
                         FROM users WHERE id = ? LIMIT 1"
                    );
                    $stmt->execute([$userId]);
                    $row = $stmt->fetch();

                    if (!$row) {
                        $errors[] = 'Account not found. Please register again.';
                        unset($_SESSION['pending_user_id'], $_SESSION['pending_email'], $_SESSION['demo_otp']);

                    } elseif ($row['email_verified'] && $type !== 'reset') {
                        unset($_SESSION['pending_user_id'], $_SESSION['pending_email'], $_SESSION['demo_otp']);
                        redirect(APP_URL . '/auth/login.php?verified=1');

                    } elseif ($row['otp_code'] !== $otp) {
                        $errors[] = 'Incorrect verification code. Please check and try again.';

                    } elseif (strtotime($row['otp_expires_at']) < time()) {
                        $errors[] = 'This code has expired. Click "Resend code" below to get a fresh one.';

                    } else {
                        /* ✅ OTP correct & valid — mark email verified */
                        $db->prepare(
                            "UPDATE users
                             SET email_verified=1, otp_code=NULL, otp_expires_at=NULL
                             WHERE id=?"
                        )->execute([$userId]);

                        /* Cleanup session */
                        unset(
                            $_SESSION['pending_user_id'],
                            $_SESSION['pending_email'],
                            $_SESSION['demo_otp'],
                            $_SESSION['otp_resend_' . $userId]
                        );

                        if ($type === 'reset') {
                            $_SESSION['reset_user_id'] = $userId;
                            redirect(APP_URL . '/auth/reset-password.php');
                        } else {
                            redirect(APP_URL . '/auth/login.php?verified=1');
                        }
                    }

                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Verification failed. Please try again.';
                }
            }
        }
    }
}

/* ── Page meta ── */
$typeLabels = [
    'register' => ['title' => 'Verify Your Email',      'sub' => 'Complete your GigGhana registration'],
    'email'    => ['title' => 'Confirm Your Identity',  'sub' => 'We need to verify your email before signing you in'],
    'reset'    => ['title' => 'Reset Password',         'sub' => 'Verify your identity to create a new password'],
];
$meta = $typeLabels[$type] ?? $typeLabels['register'];

/* Masked email — ko***ah@gmail.com */
function maskEmail(string $email): string {
    [$local, $domain] = explode('@', $email, 2) + ['', ''];
    $len = mb_strlen($local);
    if ($len <= 2) return str_repeat('*', $len) . '@' . $domain;
    return mb_substr($local, 0, 2)
         . str_repeat('*', max(1, $len - 4))
         . (mb_substr($local, -2) ?: '')
         . '@' . $domain;
}

$maskedEmail = maskEmail($email);
$demoOtp     = $_SESSION['demo_otp'] ?? '';
$csrf        = generateCSRF();
$expiryMins  = OTP_EXPIRY_MINUTES;
$resendUsed  = $_SESSION['otp_resend_' . $userId]['count'] ?? 0;
$resendLeft  = max(0, 3 - $resendUsed);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= htmlspecialchars($meta['title']) ?> — GigGhana</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{heading:['"Plus Jakarta Sans"','sans-serif'],body:['"DM Sans"','sans-serif']}}}}</script>

<!-- Flash-free theme sync — same pattern as login.php & register.php -->
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
  --glass:rgba(19,22,30,0.88);
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
  --glass:rgba(234,238,247,0.95);
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
body.lm .page-card   { background:var(--glass);border-color:var(--bd2); }
body.lm .otp-box     { background:rgba(255,255,255,0.7);border-color:var(--bd2);color:var(--tx); }
body.lm .otp-box:focus{ background:rgba(255,255,255,0.95); }
body.lm .email-chip  { background:var(--green-dim);border-color:rgba(13,175,128,0.2); }
body.lm .demo-chip   { background:var(--violet-dim);border-color:var(--violet-border); }
body.lm .security-note{ background:rgba(255,255,255,0.5);border-color:var(--bd2); }
body.lm .btn-theme   { border-color:var(--bd2);color:var(--tx-2); }
body.lm .resend-btn  { background:rgba(255,255,255,0.5);border-color:var(--bd2);color:var(--tx-2); }
body.lm .resend-btn:hover:not(:disabled){ background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan); }
body.lm .grid-tex    { background-image: linear-gradient(rgba(30,40,80,0.025) 1px,transparent 1px), linear-gradient(90deg,rgba(30,40,80,0.025) 1px,transparent 1px); }

/* ════════ RESET ════════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);font-family:var(--fb);
  min-height:100svh;overflow-x:hidden;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:20px 16px 32px;
}
html.lm-pre body,
html.lm-pre body *{ transition:none !important; }
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}

/* ── Animated gradient bar ── */
.grad-bar{
  position:fixed;top:0;left:0;right:0;height:2px;z-index:200;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--green),var(--cyan));
  background-size:300% 100%;
  animation:gradShift 5s linear infinite;
}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:300% 50%}}

/* ── Background ── */
.bg-layer{position:fixed;inset:0;pointer-events:none;z-index:0;}
.grid-tex{
  position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:
    linear-gradient(rgba(255,255,255,0.013) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,0.013) 1px,transparent 1px);
  background-size:52px 52px;
}
.blob{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;}
.blob-1{width:520px;height:520px;background:radial-gradient(circle,rgba(0,212,200,0.07),transparent 70%);top:-140px;left:-80px;}
.blob-2{width:380px;height:380px;background:radial-gradient(circle,rgba(255,107,74,0.06),transparent 70%);bottom:-80px;right:-60px;animation:blobPulse 8s ease-in-out infinite;}
.blob-3{width:200px;height:200px;background:radial-gradient(circle,rgba(124,111,247,0.08),transparent 70%);top:40%;right:10%;animation:blobPulse 6s 1s ease-in-out infinite;}
@keyframes blobPulse{0%,100%{transform:scale(1);}50%{transform:scale(1.1);}}

/* ── Noise overlay ── */
.noise-tex{
  position:fixed;inset:0;pointer-events:none;opacity:.015;z-index:0;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* ── Theme toggle (matches index.php btn-theme) ── */
.btn-theme{
  background:transparent;color:var(--tx-2);
  border:1px solid var(--bd);border-radius:10px;
  padding:7px 11px;cursor:pointer;font-size:14px;
  transition:all .26s;line-height:1;font-family:var(--fb);
}
.btn-theme:hover{background:rgba(255,255,255,0.07);}

/* ── Gradient text ── */
.grad-text{
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan),var(--coral));
  background-size:200% auto;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:gradShift 4s ease infinite;
}

/* ── Main card ── */
.page-card{
  position:relative;z-index:1;
  width:100%;max-width:480px;
  background:var(--glass);
  backdrop-filter:blur(24px);
  border:1px solid var(--bd);
  border-radius:24px;
  padding:36px 32px 28px;
  box-shadow:0 24px 80px rgba(0,0,0,0.45),0 0 0 1px rgba(255,255,255,0.03) inset;
  animation:cardIn .5s ease both;
}
@keyframes cardIn{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}

/* ── Step indicator ── */
.step-dot{
  width:28px;height:28px;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  font-size:11px;font-weight:700;font-family:var(--fm);
  transition:all .3s;flex-shrink:0;
}
.step-done{background:var(--green);color:#0C0E14;box-shadow:0 0 12px rgba(31,217,160,0.4);}
.step-active{background:var(--cyan);color:#0C0E14;box-shadow:0 0 12px var(--gC);}
.step-upcoming{background:rgba(255,255,255,0.06);color:var(--tx-3);border:1px solid var(--bd);}
.step-line{flex:1;max-width:36px;height:2px;border-radius:1px;background:var(--bd);}
.step-line.done{background:var(--green);}

/* ── Email chip ── */
.email-chip{
  display:flex;align-items:center;gap:11px;
  background:rgba(31,217,160,0.07);
  border:1px solid rgba(31,217,160,0.2);
  border-radius:12px;padding:13px 15px;margin-bottom:20px;
}
.ec-ico{
  width:36px;height:36px;border-radius:9px;
  background:rgba(31,217,160,0.15);
  display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;
}
.ec-label{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;font-weight:600;margin-bottom:2px;}
.ec-email{font-family:var(--fm);font-weight:700;font-size:13.5px;color:var(--green);word-break:break-all;}

/* ── Dev OTP hint ── */
.demo-chip{
  background:var(--violet-dim);border:1px solid var(--violet-border);
  border-radius:12px;padding:14px 16px;margin-bottom:20px;text-align:center;
}
.dc-label{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.8px;font-weight:700;margin-bottom:6px;font-family:var(--fm);}
.dc-code{
  font-family:var(--fm);font-size:30px;font-weight:900;letter-spacing:8px;color:var(--violet);
}
.dc-note{font-size:10.5px;color:var(--tx-3);margin-top:5px;}

/* ── Alert banners ── */
.alert{
  display:flex;align-items:flex-start;gap:10px;
  padding:13px 15px;border-radius:11px;margin-bottom:18px;
  font-size:13.5px;line-height:1.55;
}
.alert-err{background:rgba(255,77,106,0.07);border:1px solid rgba(255,77,106,.25);border-left:3px solid var(--red);color:#fca5a5;}
.alert-ok{background:rgba(31,217,160,0.07);border:1px solid rgba(31,217,160,.22);border-left:3px solid var(--green);color:#6EE7B7;}
.alert-ico{font-size:15px;flex-shrink:0;margin-top:1px;}

/* ── OTP INPUTS ── */
.otp-row{display:flex;align-items:center;justify-content:center;gap:9px;margin-bottom:8px;}
.otp-sep{width:14px;height:2px;border-radius:1px;background:var(--bd2);flex-shrink:0;}
.otp-box{
  width:54px;height:64px;
  background:rgba(0,0,0,0.25);
  border:2px solid var(--bd);border-radius:13px;
  color:var(--tx);font-family:var(--fm);font-size:26px;font-weight:900;
  text-align:center;outline:none;
  transition:all .22s cubic-bezier(.4,0,.2,1);
  caret-color:transparent;
  -moz-appearance:textfield;appearance:textfield;
}
.otp-box::-webkit-outer-spin-button,
.otp-box::-webkit-inner-spin-button{-webkit-appearance:none;margin:0;}
.otp-box:focus{
  border-color:var(--cyan);
  background:rgba(0,212,200,0.06);
  box-shadow:0 0 0 3px var(--cyan-dim);
  transform:scale(1.05);
}
.otp-box.filled{border-color:var(--green);background:rgba(31,217,160,0.06);}
.otp-box.filled:focus{border-color:var(--cyan);background:rgba(0,212,200,0.06);box-shadow:0 0 0 3px var(--cyan-dim);}
.otp-box.is-error{border-color:var(--red);background:rgba(255,77,106,0.07);animation:boxShake .35s ease;}
@keyframes boxShake{0%,100%{transform:translateX(0);}20%,60%{transform:translateX(-4px);}40%,80%{transform:translateX(4px);}}

/* Progress dots under OTP */
.otp-dots{display:flex;justify-content:center;gap:6px;margin-bottom:20px;}
.otp-dot{width:6px;height:6px;border-radius:50%;background:var(--bd2);transition:all .25s;}
.otp-dot.is-filled{background:var(--green);}
.otp-dot.is-active{background:var(--cyan);transform:scale(1.35);}

/* ── COUNTDOWN TIMER ── */
.timer-wrap{display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:20px;}
.timer-svg-wrap{position:relative;width:46px;height:46px;flex-shrink:0;}
.timer-svg{width:46px;height:46px;transform:rotate(-90deg);}
.timer-track{fill:none;stroke:rgba(255,255,255,0.06);stroke-width:3;}
.timer-ring{
  fill:none;stroke:var(--cyan);stroke-width:3;stroke-linecap:round;
  stroke-dasharray:113;stroke-dashoffset:0;
  transition:stroke-dashoffset 1s linear,stroke .5s;
}
.timer-inner{
  position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
  font-family:var(--fm);font-size:10px;font-weight:800;color:var(--cyan);
  transition:color .5s;
}
.timer-label{font-size:13.5px;color:var(--tx-2);}
.timer-label strong{color:var(--tx);font-weight:700;}
.timer-label.expired strong{color:var(--red);}
.timer-expired .timer-ring{stroke:var(--red);}
.timer-expired .timer-inner{color:var(--red);}

/* ── VERIFY BUTTON ── */
.verify-btn{
  position:relative;overflow:hidden;
  width:100%;padding:14px 24px;border-radius:12px;border:none;
  background:linear-gradient(135deg,var(--cyan) 0%,var(--cyan-d) 50%,#009490 100%);
  background-size:200% auto;
  color:#0C0E14;font-family:var(--fm);font-size:15.5px;font-weight:800;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;
  transition:all .4s ease;
  box-shadow:0 4px 22px var(--gC),0 1px 4px rgba(0,0,0,.5);
  margin-bottom:12px;
}
.verify-btn:hover:not(:disabled){
  background-position:right center;
  transform:translateY(-2px);
  box-shadow:0 8px 32px var(--gC);
}
.verify-btn:active:not(:disabled){transform:translateY(0);}
.verify-btn:disabled{opacity:.4;cursor:not-allowed;transform:none;box-shadow:none;}
.verify-btn::before{
  content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;
  background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);
  transition:left .6s ease;
}
.verify-btn:hover:not(:disabled)::before{left:150%;}

/* ── RESEND BUTTON ── */
.resend-btn{
  display:inline-flex;align-items:center;gap:7px;
  padding:10px 18px;border-radius:10px;
  background:rgba(255,255,255,0.04);border:1.5px solid var(--bd);
  color:var(--tx-2);font-family:var(--fm);font-size:13px;font-weight:600;
  cursor:pointer;transition:all .24s;
}
.resend-btn:hover:not(:disabled){
  background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);
  transform:translateY(-1px);
}
.resend-btn:disabled{opacity:.35;cursor:not-allowed;}

/* ── Security note ── */
.security-note{
  display:flex;align-items:center;gap:9px;
  background:rgba(255,255,255,0.028);border:1px solid var(--bd);
  border-radius:10px;padding:11px 14px;
  font-size:11.5px;color:var(--tx-3);line-height:1.5;
  margin-top:18px;
}
.sn-ico{font-size:14px;flex-shrink:0;}

/* ── Spinner ── */
.spinner{animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── Staggered slide-up ── */
.su{animation:suAnim .5s ease both;}
.su-1{animation-delay:.04s;}.su-2{animation-delay:.10s;}.su-3{animation-delay:.16s;}
.su-4{animation-delay:.22s;}.su-5{animation-delay:.28s;}.su-6{animation-delay:.34s;}
@keyframes suAnim{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}

/* ── Success redirect overlay ── */
.redirect-overlay{
  position:fixed;inset:0;z-index:1000;background:var(--bg);
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;
  opacity:0;pointer-events:none;transition:opacity .4s ease;
}
.redirect-overlay.show{opacity:1;pointer-events:auto;}

/* ── Responsive ── */
@media(max-width:520px){
  .page-card{padding:24px 16px 22px;}
  .otp-box{width:44px;height:58px;font-size:22px;border-radius:10px;}
  .otp-row{gap:6px;}
  .otp-sep{width:10px;}
  .dc-code{font-size:24px;letter-spacing:5px;}
}
</style>
</head>

<body class="">
<!-- Sync theme before render -->
<script>
if(document.documentElement.classList.contains('lm-pre')){
  document.body.classList.add('lm');
  document.documentElement.classList.remove('lm-pre');
}
</script>

<!-- Animated gradient bar -->
<div class="grad-bar"></div>

<!-- Atmospheric background -->
<div class="grid-tex"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="blob blob-3"></div>
<div class="noise-tex"></div>

<!-- ════════════════════════════════════════
     MAIN CARD
════════════════════════════════════════ -->
<div class="page-card">

  <!-- ── TOP BAR: Logo + Theme toggle ── -->
  <div class="flex items-center justify-between mb-6 su su-1">
    <a href="<?= APP_URL ?>/index.php" class="flex items-center gap-2.5" style="text-decoration:none;">
      <div class="w-9 h-9 rounded-[10px] flex items-center justify-center font-heading font-black text-[15px] flex-shrink-0"
           style="background:linear-gradient(135deg,var(--cyan),var(--cyan-d));color:#0C0E14;box-shadow:0 3px 14px var(--gC);">G</div>
      <span class="font-heading font-extrabold text-[18px]" style="color:var(--tx);">
        Gig<span style="color:var(--cyan);">Ghana</span>
      </span>
    </a>
    <button class="btn-theme" id="themeBtn" onclick="toggleTheme()" title="Toggle theme">🌙</button>
  </div>

  <!-- ── STEP INDICATOR ── -->
  <div class="flex items-center justify-center gap-2 mb-6 su su-1">
    <div class="step-dot step-done" title="Account Created">✓</div>
    <div class="step-line done"></div>
    <div class="step-dot step-active" title="Verify Email">2</div>
    <div class="step-line"></div>
    <div class="step-dot step-upcoming" title="Get Started">3</div>
  </div>

  <!-- ── 1. HEADING ── -->
  <div class="text-center mb-5 su su-2">
    <div class="text-[36px] mb-3" style="animation:iconBounce 2.5s ease-in-out infinite;">
      <?= $type === 'reset' ? '🔐' : '📬' ?>
    </div>
    <h1 class="font-heading font-black leading-tight tracking-[-1px] mb-2"
        style="font-size:clamp(22px,5vw,28px);color:var(--tx);">
      <?= $type === 'reset' ? 'Verify Your <span class="grad-text">Identity</span>' : 'Verify Your <span class="grad-text">Email</span>' ?>
    </h1>
    <p class="text-[13.5px] leading-relaxed" style="color:var(--tx-2);">
      We've sent a <strong style="color:var(--tx);">6-digit verification code</strong> to your email.<br>
      Enter the code below to <?= $type === 'reset' ? 'reset your password.' : 'activate your account.' ?>
    </p>
  </div>

  <!-- ── Email chip ── -->
  <div class="email-chip su su-2">
    <div class="ec-ico">✉️</div>
    <div>
      <div class="ec-label">Code sent to</div>
      <div class="ec-email"><?= htmlspecialchars($maskedEmail) ?></div>
    </div>
  </div>

  <!-- ── 8. Dev OTP hint (remove in production) ── -->
  <?php if ($demoOtp): ?>
  <div class="demo-chip su su-2">
    <div class="dc-label">🛠 Development Mode — Your Code</div>
    <div class="dc-code" id="demoCode"><?= htmlspecialchars($demoOtp) ?></div>
    <div class="dc-note">In production this code is sent via email. Remove this hint before launch.</div>
  </div>
  <?php endif; ?>

  <!-- ── 6. Error / success alerts ── -->
  <?php if (!empty($errors)): ?>
  <div class="alert alert-err su" id="alertBox">
    <span class="alert-ico">⚠️</span>
    <div><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  </div>
  <?php elseif ($success): ?>
  <div class="alert alert-ok su" id="alertBox">
    <span class="alert-ico">✅</span>
    <div><?= htmlspecialchars($success) ?></div>
  </div>
  <?php endif; ?>

  <!-- ── OTP FORM ── -->
  <form method="POST" id="otpForm" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="action"     value="verify">

    <!-- ── 2. OTP INPUT FIELDS ── -->
    <div class="text-center text-[10px] font-extrabold uppercase tracking-widest mb-3 su su-3"
         style="color:var(--tx-3);font-family:var(--fm);">
      Enter 6-digit verification code
    </div>

    <div class="otp-row su su-3" id="otpRow">
      <input type="text" name="d1" id="d1" class="otp-box" maxlength="1" inputmode="numeric"
             pattern="[0-9]" autocomplete="one-time-code" aria-label="Digit 1">
      <input type="text" name="d2" id="d2" class="otp-box" maxlength="1" inputmode="numeric"
             pattern="[0-9]" autocomplete="off" aria-label="Digit 2">
      <input type="text" name="d3" id="d3" class="otp-box" maxlength="1" inputmode="numeric"
             pattern="[0-9]" autocomplete="off" aria-label="Digit 3">
      <div class="otp-sep" aria-hidden="true"></div>
      <input type="text" name="d4" id="d4" class="otp-box" maxlength="1" inputmode="numeric"
             pattern="[0-9]" autocomplete="off" aria-label="Digit 4">
      <input type="text" name="d5" id="d5" class="otp-box" maxlength="1" inputmode="numeric"
             pattern="[0-9]" autocomplete="off" aria-label="Digit 5">
      <input type="text" name="d6" id="d6" class="otp-box" maxlength="1" inputmode="numeric"
             pattern="[0-9]" autocomplete="off" aria-label="Digit 6">
    </div>

    <!-- Progress dots -->
    <div class="otp-dots" id="otpDots" aria-hidden="true">
      <?php for($i=1;$i<=6;$i++): ?>
      <div class="otp-dot <?= $i===1?'is-active':'' ?>" id="dot<?= $i ?>"></div>
      <?php endfor; ?>
    </div>

    <!-- ── 5. TIMER COUNTDOWN ── -->
    <div class="timer-wrap su su-4" id="timerWrap">
      <div class="timer-svg-wrap" id="timerSvgWrap">
        <svg class="timer-svg" viewBox="0 0 46 46" aria-hidden="true">
          <circle class="timer-track" cx="23" cy="23" r="18"/>
          <circle class="timer-ring" cx="23" cy="23" r="18" id="timerRing"/>
        </svg>
        <div class="timer-inner" id="timerInner">--</div>
      </div>
      <div class="timer-label" id="timerLabel">
        Code expires in <strong id="timerCountdown">--:--</strong>
      </div>
    </div>

    <!-- ── 3. VERIFY BUTTON ── -->
    <div class="su su-4">
      <button type="submit" class="verify-btn" id="verifyBtn" disabled>
        <svg id="btnSpinner" class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:none;">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"></circle>
          <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".8"></path>
        </svg>
        <span id="verifyLabel">Verify Code →</span>
      </button>
    </div>

  </form>

  <!-- ── 4. RESEND OTP ── -->
  <div class="flex items-center justify-between gap-3 mt-1 su su-5">
    <form method="POST" id="resendForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action"     value="resend">
      <button type="submit" class="resend-btn" id="resendBtn" disabled>
        <span id="resendIcon">↺</span>
        <span id="resendLabel">Didn't receive the code? Resend</span>
      </button>
    </form>
    <div class="text-[11px] flex-shrink-0" style="color:var(--tx-3);">
      <span id="resendLeftBadge"><?= $resendLeft ?></span>/3 left
    </div>
  </div>

  <!-- Resend cooldown note -->
  <p class="text-center text-[11px] mt-2 su su-5" style="color:var(--tx-3);" id="resendCooldownNote">
    You can request a new code after <strong style="color:var(--tx-2);">30 seconds</strong>.
  </p>

  <!-- Back to login -->
  <div class="text-center mt-4 su su-6">
    <a href="<?= APP_URL ?>/auth/login.php"
       class="inline-flex items-center gap-1.5 text-[13px] hover:underline transition-all"
       style="color:var(--tx-3);text-decoration:none;">
      ← Back to Sign In
    </a>
  </div>

  <!-- Security note -->
  <div class="security-note su su-6">
    <span class="sn-ico">🔒</span>
    <span>
      For your security, this code expires in
      <strong style="color:var(--tx-2);"><?= $expiryMins ?> minutes</strong>
      and can only be used once. Check your spam folder if you don't see it.
    </span>
  </div>

</div><!-- /page-card -->

<!-- Success redirect overlay -->
<div class="redirect-overlay" id="redirectOverlay">
  <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-[30px]"
       style="background:linear-gradient(135deg,var(--cyan-dim),var(--green-dim));border:1px solid var(--cyan-border);">
    ✅
  </div>
  <div class="font-heading font-black text-[22px]" style="color:var(--tx);">Verification successful!</div>
  <div class="text-[14px]" style="color:var(--tx-2);">Redirecting to your dashboard…</div>
  <div class="w-48 h-1 rounded-full overflow-hidden mt-2" style="background:var(--bd);">
    <div id="redirectBar" class="h-full rounded-full"
         style="background:var(--cyan);width:0%;transition:width 2s ease;"></div>
  </div>
</div>

<style>
@keyframes iconBounce{
  0%,100%{transform:scale(1);}
  50%{transform:scale(1.08);filter:drop-shadow(0 0 10px rgba(0,212,200,.35));}
}
</style>

<script>
(function(){
'use strict';

/* ══════════════════════════════════════════════════════
   THEME SYNC — identical to login.php & register.php
══════════════════════════════════════════════════════ */
function applyTheme(isLight){
  document.body.classList.toggle('lm', isLight);
  const btn = document.getElementById('themeBtn');
  if(btn) btn.textContent = isLight ? '☀️' : '🌙';
}
(function initTheme(){
  applyTheme(localStorage.getItem('gg_theme')==='light');
})();
window.toggleTheme = function(){
  const nowLight = !document.body.classList.contains('lm');
  localStorage.setItem('gg_theme', nowLight ? 'light' : 'dark');
  applyTheme(nowLight);
};
window.addEventListener('storage', function(e){
  if(e.key==='gg_theme') applyTheme(e.newValue==='light');
});

/* ══════════════════════════════════════════════════════
   DOM REFS
══════════════════════════════════════════════════════ */
const inputs      = Array.from(document.querySelectorAll('.otp-box'));
const dots        = Array.from({length:6},(_,i)=>document.getElementById('dot'+(i+1)));
const verifyBtn   = document.getElementById('verifyBtn');
const verifyLabel = document.getElementById('verifyLabel');
const btnSpinner  = document.getElementById('btnSpinner');
const resendBtn   = document.getElementById('resendBtn');
const resendLabel = document.getElementById('resendLabel');
const resendIcon  = document.getElementById('resendIcon');
const cooldownNote= document.getElementById('resendCooldownNote');
const timerRing   = document.getElementById('timerRing');
const timerInner  = document.getElementById('timerInner');
const timerCountdown = document.getElementById('timerCountdown');
const timerLabel  = document.getElementById('timerLabel');
const timerSvgWrap= document.getElementById('timerSvgWrap');

const TOTAL_SECS  = <?= $expiryMins * 60 ?>;
const CIRCUMF     = 2 * Math.PI * 18; // r=18
timerRing.style.strokeDasharray = CIRCUMF;

/* ══════════════════════════════════════════════════════
   5. COUNTDOWN TIMER
══════════════════════════════════════════════════════ */
let remaining = TOTAL_SECS;
let timerID   = null;
let resendUnlocked = false;

function fmt(s){
  return Math.floor(s/60)+':'+String(s%60).padStart(2,'0');
}

function tick(){
  remaining = Math.max(0, remaining - 1);
  const pct    = remaining / TOTAL_SECS;
  const offset = CIRCUMF * (1 - pct);

  timerRing.style.strokeDashoffset = offset;
  timerInner.textContent  = fmt(remaining);
  timerCountdown.textContent = fmt(remaining);

  /* Colour shift < 60s */
  if(remaining <= 60 && remaining > 0){
    timerRing.style.stroke = 'var(--amber)';
    timerInner.style.color = 'var(--amber)';
  }

  /* Expired */
  if(remaining === 0){
    clearInterval(timerID);
    timerRing.style.stroke = 'var(--red)';
    timerInner.style.color = 'var(--red)';
    timerSvgWrap.classList.add('timer-expired');
    timerLabel.classList.add('expired');
    timerLabel.innerHTML = '<strong style="color:var(--red);">Code expired — request a new one below</strong>';
    verifyBtn.disabled   = true;
    verifyLabel.textContent = 'Code Expired';
    unlockResend();
    return;
  }

  /* Unlock resend after 30 seconds from page load */
  if(!resendUnlocked && remaining <= TOTAL_SECS - 30){
    resendUnlocked = true;
    unlockResend();
  }
}

function unlockResend(){
  const left = parseInt(document.getElementById('resendLeftBadge').textContent) || 0;
  if(left > 0){
    resendBtn.disabled    = false;
    resendLabel.textContent = "Didn't receive the code? Resend";
    resendIcon.textContent  = '↺';
  } else {
    resendLabel.textContent = 'No resends remaining';
  }
  if(cooldownNote) cooldownNote.style.display = 'none';
}

/* Init timer */
timerInner.textContent    = fmt(remaining);
timerCountdown.textContent = fmt(remaining);
timerID = setInterval(tick, 1000);

/* ══════════════════════════════════════════════════════
   2. OTP INPUT LOGIC
══════════════════════════════════════════════════════ */
function syncDots(){
  const firstEmpty = inputs.findIndex(i => !i.value);
  dots.forEach((d,i) => {
    d.classList.remove('is-active','is-filled');
    if(inputs[i].value) d.classList.add('is-filled');
  });
  if(firstEmpty >= 0 && dots[firstEmpty]) dots[firstEmpty].classList.add('is-active');
}

function allFilled(){ return inputs.every(i => i.value.length===1); }

function checkComplete(){
  verifyBtn.disabled = !allFilled() || remaining===0;
  syncDots();
}

inputs.forEach((inp, idx) => {
  inp.addEventListener('focus', function(){ this.select(); });

  inp.addEventListener('input', function(){
    /* Keep only one digit */
    const v = this.value.replace(/\D/g,'').slice(0,1);
    this.value = v;
    if(v){
      this.classList.add('filled');
      this.classList.remove('is-error');
      if(idx < inputs.length - 1) inputs[idx+1].focus();
    } else {
      this.classList.remove('filled');
    }
    checkComplete();
  });

  inp.addEventListener('keydown', function(e){
    if(e.key==='Backspace'){
      if(!this.value && idx > 0){
        e.preventDefault();
        inputs[idx-1].value='';
        inputs[idx-1].classList.remove('filled');
        inputs[idx-1].focus();
      } else {
        this.value='';
        this.classList.remove('filled');
      }
      checkComplete();
    }
    if(e.key==='ArrowLeft'  && idx > 0)              { e.preventDefault(); inputs[idx-1].focus(); }
    if(e.key==='ArrowRight' && idx < inputs.length-1){ e.preventDefault(); inputs[idx+1].focus(); }
    if(e.key==='Enter' && allFilled() && remaining>0) document.getElementById('otpForm').requestSubmit();
  });

  /* Paste — auto-fill all 6 digits */
  inp.addEventListener('paste', function(e){
    e.preventDefault();
    const text = (e.clipboardData||window.clipboardData)
      .getData('text').replace(/\D/g,'').slice(0,6);
    if(!text) return;
    text.split('').forEach((ch,i) => {
      if(inputs[i]){ inputs[i].value=ch; inputs[i].classList.add('filled'); }
    });
    const nextEmpty = inputs.findIndex(i=>!i.value);
    (nextEmpty>=0 ? inputs[nextEmpty] : inputs[inputs.length-1]).focus();
    checkComplete();
  });
});

/* Auto-fill from dev hint */
const demo = document.getElementById('demoCode');
if(demo){
  const code = demo.textContent.replace(/\D/g,'');
  if(code.length===6){
    setTimeout(()=>{
      code.split('').forEach((ch,i)=>{ if(inputs[i]){ inputs[i].value=ch; inputs[i].classList.add('filled'); } });
      checkComplete();
      inputs[5].focus();
    }, 600);
  }
}

/* Initial sync */
syncDots();

/* ══════════════════════════════════════════════════════
   FORM SUBMIT
══════════════════════════════════════════════════════ */
document.getElementById('otpForm').addEventListener('submit', function(e){
  if(!allFilled() || remaining===0){
    e.preventDefault();
    inputs.forEach(i=>i.classList.add('is-error'));
    setTimeout(()=>inputs.forEach(i=>i.classList.remove('is-error')),600);
    return;
  }
  /* Loading + redirect overlay */
  verifyBtn.disabled       = true;
  btnSpinner.style.display = 'block';
  verifyLabel.textContent  = 'Verifying…';
  clearInterval(timerID);

  const ov = document.getElementById('redirectOverlay');
  ov.classList.add('show');
  requestAnimationFrame(()=>{
    document.getElementById('redirectBar').style.width='100%';
  });
});

/* Resend submit */
document.getElementById('resendForm').addEventListener('submit', function(){
  resendBtn.disabled      = true;
  resendLabel.textContent = 'Sending…';
  resendIcon.textContent  = '⏳';
});

/* ══════════════════════════════════════════════════════
   PHP ERROR → Shake & clear inputs
══════════════════════════════════════════════════════ */
<?php if(!empty($errors)): ?>
setTimeout(()=>{
  inputs.forEach(i=>{ i.classList.add('is-error'); i.value=''; i.classList.remove('filled'); });
  dots.forEach(d=>{ d.classList.remove('is-filled','is-active'); });
  if(dots[0]) dots[0].classList.add('is-active');
  setTimeout(()=>inputs.forEach(i=>i.classList.remove('is-error')),600);
  inputs[0].focus();
  checkComplete();
},100);
<?php endif; ?>

/* Auto-dismiss success alert */
<?php if($success): ?>
setTimeout(()=>{
  const a=document.getElementById('alertBox');
  if(a){ a.style.transition='opacity .5s'; a.style.opacity='0'; setTimeout(()=>a.remove(),500); }
},4000);
<?php endif; ?>

/* Auto-focus first input */
<?php if(empty($errors) && !$demoOtp): ?>
window.addEventListener('load', ()=>inputs[0]?.focus());
<?php endif; ?>

})();
</script>
</body>
</html>