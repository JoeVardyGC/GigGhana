<?php
/**
 * GigGhana — auth/forgot-password.php
 * Design system: Volcanic Charcoal × Electric Cyan × Coral
 * Fonts: Plus Jakarta Sans + DM Sans
 * Theme: synced from index.php via localStorage('gg_theme')
 *
 * 3-step flow (single page, session-driven):
 *   Step 1 — Email entry        → generates OTP, stores in users.otp_code
 *   Step 2 — OTP verification   → 6-digit code confirms identity
 *   Step 3 — New password       → bcrypt-hashed, password_reset cleared
 *
 * DB columns used (no new table needed):
 *   users.otp_code              VARCHAR(10)
 *   users.otp_expires_at        TIMESTAMP
 *   users.password_reset_token  VARCHAR(100)  — used as step-gate token
 *   users.password_reset_expires TIMESTAMP
 *   users.password_hash
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/* Already logged in — no need to reset */
if (isLoggedIn()) {
    redirect(APP_URL . '/' . $_SESSION['user_role'] . '/dashboard.php');
}

/* ── Determine current step from session ── */
$step   = (int)($_SESSION['fp_step'] ?? 1);       // 1 | 2 | 3
$userId = (int)($_SESSION['fp_user_id'] ?? 0);
$email  = $_SESSION['fp_email'] ?? '';

/* Safety: if someone lands on step 2/3 without prior steps, reset */
if ($step === 2 && (!$userId || !$email)) { $step = 1; unset($_SESSION['fp_step']); }
if ($step === 3 && (!$userId || empty($_SESSION['fp_verified']))) { $step = 1; unset($_SESSION['fp_step'], $_SESSION['fp_verified']); }

$errors  = [];
$success = '';
$csrf    = generateCSRF();

/* Helpers */
function maskEmail(string $e): string {
    $parts  = explode('@', $e, 2);
    $local  = isset($parts[0]) ? $parts[0] : '';
    $domain = isset($parts[1]) ? $parts[1] : '';
    $len    = mb_strlen($local);
    if ($len <= 2) return str_repeat('*', $len) . '@' . $domain;
    $tail = ($len > 4) ? mb_substr($local, -2) : '';
    return mb_substr($local, 0, 2) . str_repeat('*', max(1, $len - 4)) . $tail . '@' . $domain;
}

/* ══════════════════════════════════════════════════
   POST HANDLERS
══════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch — please refresh and try again.';
    } else {

        $action = $_POST['action'] ?? '';

        /* ─────────────────────────────────────────
           STEP 1: Find account & send OTP
        ───────────────────────────────────────── */
        if ($action === 'send_otp') {
            $emailInput = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);

            if (!filter_var($emailInput, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Please enter a valid email address.';
            } else {
                try {
                    $db   = getDB();
                    $stmt = $db->prepare("SELECT id, first_name, is_active, is_banned FROM users WHERE email=? LIMIT 1");
                    $stmt->execute([$emailInput]);
                    $u = $stmt->fetch();

                    /*
                     * Security: always show success even if email not found
                     * (prevents account enumeration)
                     */
                    if ($u && !(int)$u['is_banned'] && (int)$u['is_active']) {
                        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $exp = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

                        $db->prepare(
                            "UPDATE users SET otp_code=?, otp_expires_at=?, password_reset_token=NULL, password_reset_expires=NULL WHERE id=?"
                        )->execute([$otp, $exp, $u['id']]);

                        /* Production: send $otp to $emailInput via email/SMS */
                        /* sendResetEmail($emailInput, $u['first_name'], $otp); */

                        $_SESSION['fp_step']    = 2;
                        $_SESSION['fp_user_id'] = $u['id'];
                        $_SESSION['fp_email']   = $emailInput;
                        $_SESSION['fp_otp_dev'] = $otp; /* DEV ONLY — remove in production */
                        $_SESSION['fp_resend']  = ['count'=>0,'start'=>time()];

                    } else {
                        /* Still set step so UI shows "email sent" without revealing existence */
                        $_SESSION['fp_step']    = 2;
                        $_SESSION['fp_user_id'] = 0;
                        $_SESSION['fp_email']   = $emailInput;
                        $_SESSION['fp_otp_dev'] = null;
                    }

                    redirect(APP_URL . '/auth/forgot-password.php');

                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Something went wrong. Please try again.';
                }
            }
        }

        /* ─────────────────────────────────────────
           STEP 1: Resend OTP (from step 2 form)
        ───────────────────────────────────────── */
        elseif ($action === 'resend_otp' && $step === 2) {
            $rk    = 'fp_resend';
            $rc    = $_SESSION[$rk]['count'] ?? 0;
            $rs    = $_SESSION[$rk]['start'] ?? 0;

            if (time() - $rs > 900) { $_SESSION[$rk] = ['count'=>0,'start'=>time()]; $rc=0; }

            if ($rc >= 3) {
                $errors[] = 'Too many resend attempts. Please wait ' . ceil((900-(time()-$rs))/60) . ' minute(s).';
            } elseif ($userId) {
                try {
                    $db  = getDB();
                    $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $exp = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));
                    $db->prepare("UPDATE users SET otp_code=?, otp_expires_at=? WHERE id=?")->execute([$otp,$exp,$userId]);
                    $_SESSION[$rk]['count'] = $rc + 1;
                    $_SESSION['fp_otp_dev'] = $otp;
                    $success = 'A new code was sent to ' . maskEmail($email) . '.';
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Could not resend. Please try again.';
                }
            } else {
                $success = 'If that email exists, a new code has been sent.';
            }
        }

        /* ─────────────────────────────────────────
           STEP 2: Verify OTP
        ───────────────────────────────────────── */
        elseif ($action === 'verify_otp' && $step === 2) {
            $digits = [];
            for ($i = 1; $i <= 6; $i++) $digits[] = trim($_POST['d'.$i] ?? '');
            $otp = implode('', $digits);

            if (!ctype_digit($otp) || strlen($otp) !== 6) {
                $errors[] = 'Please enter all 6 digits of the verification code.';
            } elseif (!$userId) {
                /* Non-existent account — fake success to prevent enumeration */
                $_SESSION['fp_step']     = 3;
                $_SESSION['fp_verified'] = true;
                redirect(APP_URL . '/auth/forgot-password.php');
            } else {
                try {
                    $db   = getDB();
                    $stmt = $db->prepare("SELECT otp_code, otp_expires_at FROM users WHERE id=? LIMIT 1");
                    $stmt->execute([$userId]);
                    $row  = $stmt->fetch();

                    if (!$row || $row['otp_code'] !== $otp) {
                        $errors[] = 'Incorrect code. Please check and try again.';
                    } elseif (strtotime($row['otp_expires_at']) < time()) {
                        $errors[] = 'This code has expired. Click "Resend" to get a fresh one.';
                    } else {
                        /* Valid — gate step 3 with a server-side reset token */
                        $token = bin2hex(random_bytes(32));
                        $exp   = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        $db->prepare(
                            "UPDATE users SET otp_code=NULL, otp_expires_at=NULL,
                                             password_reset_token=?, password_reset_expires=?
                             WHERE id=?"
                        )->execute([$token, $exp, $userId]);

                        $_SESSION['fp_step']     = 3;
                        $_SESSION['fp_verified'] = true;
                        $_SESSION['fp_token']    = $token;
                        redirect(APP_URL . '/auth/forgot-password.php');
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Verification failed. Please try again.';
                }
            }
        }

        /* ─────────────────────────────────────────
           STEP 3: Set new password
        ───────────────────────────────────────── */
        elseif ($action === 'set_password' && $step === 3) {
            $pwd1 = $_POST['password']         ?? '';
            $pwd2 = $_POST['confirm_password'] ?? '';

            if (strlen($pwd1) < 8)            $errors[] = 'Password must be at least 8 characters.';
            if (!preg_match('/[A-Z]/',$pwd1)) $errors[] = 'Password must contain an uppercase letter.';
            if (!preg_match('/[0-9]/',$pwd1)) $errors[] = 'Password must contain a number.';
            if ($pwd1 !== $pwd2)              $errors[] = 'Passwords do not match.';

            if (empty($errors) && $userId) {
                try {
                    $db    = getDB();
                    $token = $_SESSION['fp_token'] ?? '';

                    /* Verify the reset token hasn't expired */
                    $stmt = $db->prepare("SELECT password_reset_expires FROM users WHERE id=? AND password_reset_token=? LIMIT 1");
                    $stmt->execute([$userId, $token]);
                    $row  = $stmt->fetch();

                    if (!$row || strtotime($row['password_reset_expires']) < time()) {
                        $errors[] = 'Your reset session expired. Please start again.';
                        unset($_SESSION['fp_step'],$_SESSION['fp_verified'],$_SESSION['fp_token'],
                              $_SESSION['fp_user_id'],$_SESSION['fp_email'],$_SESSION['fp_otp_dev']);
                    } else {
                        $hash = password_hash($pwd1, PASSWORD_BCRYPT, ['cost' => 12]);
                        $db->prepare(
                            "UPDATE users SET password_hash=?, password_reset_token=NULL, password_reset_expires=NULL WHERE id=?"
                        )->execute([$hash, $userId]);

                        /* Clean up all forgot-password session keys */
                        foreach(['fp_step','fp_user_id','fp_email','fp_otp_dev','fp_verified','fp_token','fp_resend'] as $k)
                            unset($_SESSION[$k]);

                        $_SESSION['flash_success'] = '✅ Password updated! Please sign in with your new password.';
                        redirect(APP_URL . '/auth/login.php?reset=1');
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $errors[] = 'Could not update password. Please try again.';
                }
            } elseif (empty($errors)) {
                /* Non-existent account — fake success */
                foreach(['fp_step','fp_user_id','fp_email','fp_otp_dev','fp_verified','fp_token','fp_resend'] as $k)
                    unset($_SESSION[$k]);
                $_SESSION['flash_success'] = '✅ Password updated! Please sign in with your new password.';
                redirect(APP_URL . '/auth/login.php?reset=1');
            }
        }
    }
}

/* Re-read after possible session changes */
$step    = (int)($_SESSION['fp_step']    ?? 1);
$userId  = (int)($_SESSION['fp_user_id'] ?? 0);
$email   = $_SESSION['fp_email']   ?? '';
$demoOtp = $_SESSION['fp_otp_dev'] ?? '';
$resendLeft = max(0, 3 - ($_SESSION['fp_resend']['count'] ?? 0));

$stepTitles = [
    1 => ['icon'=>'🔑', 'title'=>'Forgot Password',     'sub'=>'Enter your email and we\'ll send you a reset code.'],
    2 => ['icon'=>'📬', 'title'=>'Enter Reset Code',     'sub'=>'We sent a 6-digit code to your email address.'],
    3 => ['icon'=>'🔒', 'title'=>'Create New Password',  'sub'=>'Choose a strong password for your account.'],
];
$meta = $stepTitles[$step];
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
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>tailwind.config={theme:{extend:{fontFamily:{heading:['"Plus Jakarta Sans"','sans-serif'],body:['"DM Sans"','sans-serif']}}}}</script>

<!-- Flash-free theme sync — reads localStorage('gg_theme') before first paint -->
<script>
(function(){
  if(localStorage.getItem('gg_theme')==='light')
    document.documentElement.classList.add('lm-pre');
})();
</script>

<style>
/* ════════════════════════════════════════════
   DESIGN TOKENS — Dark (exact match to index.php)
════════════════════════════════════════════ */
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

/* ════════════════════════════════════════════
   LIGHT MODE — exact copy of index.php .lm
════════════════════════════════════════════ */
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
body.lm .page-card     { background:var(--glass); border-color:var(--bd2); }
body.lm .gg-input      { background:rgba(255,255,255,0.7); color:var(--tx); border-color:var(--bd2); }
body.lm .gg-input:focus{ background:rgba(255,255,255,0.95); }
body.lm .gg-input::placeholder{ color:var(--tx-3); }
body.lm .otp-box       { background:rgba(255,255,255,0.65); color:var(--tx); border-color:var(--bd2); }
body.lm .otp-box:focus { background:rgba(255,255,255,0.95); }
body.lm .btn-theme     { border-color:var(--bd2); color:var(--tx-2); }
body.lm .step-pip      { background:var(--bd2); }
body.lm .step-pip.done { background:var(--green); }
body.lm .step-pip.active{ background:var(--cyan); }
body.lm .str-seg       { background:rgba(30,40,80,0.1); }
body.lm .resend-btn    { background:rgba(255,255,255,0.5); border-color:var(--bd2); }
body.lm .grid-tex      {
  background-image: linear-gradient(rgba(30,40,80,0.025) 1px,transparent 1px),
                    linear-gradient(90deg,rgba(30,40,80,0.025) 1px,transparent 1px);
}

/* ════ BASE ════ */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{scroll-behavior:smooth;}
body{
  background:var(--bg);color:var(--tx);font-family:var(--fb);
  min-height:100svh;overflow-x:hidden;-webkit-font-smoothing:antialiased;
  transition:background .3s,color .3s;
  display:flex;align-items:center;justify-content:center;
  padding:20px 16px 40px;
}
html.lm-pre body,html.lm-pre body *{ transition:none !important; }
::-webkit-scrollbar{width:4px;}
::-webkit-scrollbar-track{background:var(--bg);}
::-webkit-scrollbar-thumb{background:var(--s3);border-radius:2px;}

/* ── Gradient bar ── */
.grad-bar{
  position:fixed;top:0;left:0;right:0;height:2px;z-index:200;
  background:linear-gradient(90deg,var(--cyan),var(--coral),var(--violet),var(--green),var(--cyan));
  background-size:300% 100%;animation:gradShift 5s linear infinite;
}
@keyframes gradShift{0%{background-position:0% 50%}100%{background-position:300% 50%}}

/* ── Background layers ── */
.grid-tex{
  position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:linear-gradient(rgba(255,255,255,0.013) 1px,transparent 1px),
                   linear-gradient(90deg,rgba(255,255,255,0.013) 1px,transparent 1px);
  background-size:52px 52px;
}
.blob{position:fixed;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:0;}
.blob-c{width:500px;height:500px;background:radial-gradient(circle,rgba(0,212,200,0.07),transparent 70%);top:-140px;left:-80px;animation:bFloat 10s ease-in-out infinite;}
.blob-r{width:340px;height:340px;background:radial-gradient(circle,rgba(255,107,74,0.06),transparent 70%);bottom:-80px;right:-60px;animation:bFloat 8s 2s ease-in-out infinite;}
.blob-v{width:200px;height:200px;background:radial-gradient(circle,rgba(124,111,247,0.08),transparent 70%);top:40%;right:10%;animation:bFloat 6s 1s ease-in-out infinite;}
@keyframes bFloat{0%,100%{transform:scale(1);}50%{transform:scale(1.08);}}
.noise-tex{
  position:fixed;inset:0;pointer-events:none;opacity:.015;z-index:0;
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
}

/* ── Gradient text ── */
.grad-text{
  background:linear-gradient(135deg,var(--cyan-l),var(--cyan),var(--coral));
  background-size:200% auto;
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  animation:gradShift 4s ease infinite;
}

/* ── Theme toggle ── */
.btn-theme{
  background:transparent;color:var(--tx-2);border:1px solid var(--bd);border-radius:10px;
  padding:7px 11px;cursor:pointer;font-size:14px;transition:all .26s;line-height:1;font-family:var(--fb);
}
.btn-theme:hover{background:rgba(255,255,255,0.07);}

/* ── MAIN CARD ── */
.page-card{
  position:relative;z-index:1;width:100%;max-width:460px;
  background:var(--glass);backdrop-filter:blur(24px);
  border:1px solid var(--bd);border-radius:24px;
  padding:36px 32px 30px;
  box-shadow:0 24px 80px rgba(0,0,0,0.45),0 0 0 1px rgba(255,255,255,0.03) inset;
  animation:cardIn .5s ease both;
}
@keyframes cardIn{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:translateY(0);}}

/* ── Step progress pills ── */
.step-pip{
  height:4px;border-radius:2px;flex:1;background:rgba(255,255,255,0.08);
  transition:background .4s,transform .4s;
}
.step-pip.done  { background:var(--green); }
.step-pip.active{ background:var(--cyan);  }

/* ── Form fields ── */
.field-wrap{position:relative;}
.field-ico{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:15px;opacity:.4;pointer-events:none;transition:opacity .25s;line-height:1;}
.field-wrap:focus-within .field-ico{opacity:.9;}
.gg-input{
  width:100%;background:rgba(0,0,0,0.25);border:1.5px solid var(--bd);border-radius:11px;
  padding:12px 14px 12px 44px;color:var(--tx);font-family:var(--fb);font-size:14px;
  outline:none;transition:all .24s;-webkit-appearance:none;
}
.gg-input::placeholder{color:var(--tx-3);opacity:.7;}
.gg-input:hover{border-color:var(--bd2);}
.gg-input:focus{border-color:var(--cyan);background:rgba(0,212,200,0.04);box-shadow:0 0 0 3px var(--cyan-dim);}
.gg-input.is-valid{border-color:var(--green);background:rgba(31,217,160,0.04);}
.gg-input.is-error{border-color:var(--red);background:rgba(255,77,106,0.04);animation:fShake .3s ease;}
@keyframes fShake{0%,100%{transform:translateX(0);}25%,75%{transform:translateX(-4px);}50%{transform:translateX(4px);}}
.pwd-eye{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:15px;color:var(--tx-3);z-index:2;line-height:1;padding:4px;transition:color .2s;}
.pwd-eye:hover{color:var(--tx);}
.field-err{font-size:11px;color:var(--red);margin-top:4px;display:none;align-items:center;gap:4px;}
.field-err.show{display:flex;}

/* ── Password strength ── */
.str-segs{display:grid;grid-template-columns:repeat(4,1fr);gap:4px;margin-bottom:5px;}
.str-seg{height:3px;border-radius:2px;background:rgba(255,255,255,0.07);transition:background .35s;}
.str-seg.s1{background:var(--red);} .str-seg.s2{background:var(--coral);}
.str-seg.s3{background:var(--amber);} .str-seg.s4{background:var(--green);}
.pwd-rules{display:grid;grid-template-columns:1fr 1fr;gap:4px 12px;margin-top:8px;}
.pwd-rule{display:flex;align-items:center;gap:6px;font-size:11px;color:var(--tx-3);transition:color .24s;}
.pwd-rule.met{color:var(--green);}
.rule-c{width:14px;height:14px;border-radius:50%;border:1.5px solid currentColor;display:flex;align-items:center;justify-content:center;font-size:8px;flex-shrink:0;transition:all .24s;}
.pwd-rule.met .rule-c{background:var(--green);border-color:var(--green);color:#0C0E14;}

/* ── OTP INPUTS ── */
.otp-row{display:flex;align-items:center;justify-content:center;gap:9px;}
.otp-sep{width:14px;height:2px;border-radius:1px;background:var(--bd2);flex-shrink:0;}
.otp-box{
  width:54px;height:64px;background:rgba(0,0,0,0.25);
  border:2px solid var(--bd);border-radius:13px;
  color:var(--tx);font-family:var(--fm);font-size:26px;font-weight:900;
  text-align:center;outline:none;transition:all .22s cubic-bezier(.4,0,.2,1);
  caret-color:transparent;-moz-appearance:textfield;appearance:textfield;
}
.otp-box::-webkit-outer-spin-button,.otp-box::-webkit-inner-spin-button{-webkit-appearance:none;margin:0;}
.otp-box:focus{border-color:var(--cyan);background:rgba(0,212,200,0.06);box-shadow:0 0 0 3px var(--cyan-dim);transform:scale(1.05);}
.otp-box.filled{border-color:var(--green);background:rgba(31,217,160,0.06);}
.otp-box.filled:focus{border-color:var(--cyan);background:rgba(0,212,200,0.06);box-shadow:0 0 0 3px var(--cyan-dim);}
.otp-box.is-error{border-color:var(--red);background:rgba(255,77,106,0.07);animation:otpShake .35s ease;}
@keyframes otpShake{0%,100%{transform:translateX(0);}20%,60%{transform:translateX(-4px);}40%,80%{transform:translateX(4px);}}

/* OTP progress dots */
.otp-dots{display:flex;justify-content:center;gap:6px;margin-top:8px;margin-bottom:18px;}
.otp-dot{width:6px;height:6px;border-radius:50%;background:var(--bd2);transition:all .25s;}
.otp-dot.is-filled{background:var(--green);}
.otp-dot.is-active{background:var(--cyan);transform:scale(1.35);}

/* ── Timer ── */
.timer-row{display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:18px;}
.timer-svg-wrap{position:relative;width:44px;height:44px;flex-shrink:0;}
.timer-svg{width:44px;height:44px;transform:rotate(-90deg);}
.t-track{fill:none;stroke:rgba(255,255,255,0.06);stroke-width:3;}
.t-ring{fill:none;stroke:var(--cyan);stroke-width:3;stroke-linecap:round;stroke-dasharray:107;stroke-dashoffset:0;transition:stroke-dashoffset 1s linear,stroke .5s;}
.t-inner{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:var(--fm);font-size:10px;font-weight:800;color:var(--cyan);transition:color .5s;}
.timer-txt{font-size:13px;color:var(--tx-2);}
.timer-txt strong{color:var(--tx);font-weight:700;}

/* ── Dev OTP chip ── */
.demo-chip{
  background:var(--violet-dim);border:1px solid var(--violet-border);
  border-radius:12px;padding:13px 16px;text-align:center;margin-bottom:18px;
}
.dc-label{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.8px;font-weight:700;margin-bottom:5px;font-family:var(--fm);}
.dc-code{font-family:var(--fm);font-size:28px;font-weight:900;letter-spacing:8px;color:var(--violet);}
.dc-note{font-size:10.5px;color:var(--tx-3);margin-top:4px;}

/* ── Alerts ── */
.alert{display:flex;align-items:flex-start;gap:10px;padding:13px 15px;border-radius:11px;margin-bottom:18px;font-size:13.5px;line-height:1.55;}
.alert-err{background:rgba(255,77,106,0.07);border:1px solid rgba(255,77,106,.25);border-left:3px solid var(--red);color:#fca5a5;}
.alert-ok{background:rgba(31,217,160,0.07);border:1px solid rgba(31,217,160,.22);border-left:3px solid var(--green);color:#6EE7B7;}
.alert-ico{font-size:15px;flex-shrink:0;margin-top:1px;}

/* ── Email chip ── */
.email-chip{display:flex;align-items:center;gap:10px;background:rgba(31,217,160,0.07);border:1px solid rgba(31,217,160,0.2);border-radius:11px;padding:11px 14px;margin-bottom:18px;}
.ec-ico{width:34px;height:34px;border-radius:9px;background:rgba(31,217,160,0.15);display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;}
.ec-lbl{font-size:10px;color:var(--tx-3);text-transform:uppercase;letter-spacing:.6px;font-weight:600;margin-bottom:1px;}
.ec-email{font-family:var(--fm);font-weight:700;font-size:13px;color:var(--green);}

/* ── Submit button ── */
.submit-btn{
  position:relative;overflow:hidden;width:100%;padding:14px 24px;border-radius:12px;border:none;
  background:linear-gradient(135deg,var(--cyan) 0%,var(--cyan-d) 50%,#009490 100%);
  background-size:200% auto;color:#0C0E14;font-family:var(--fm);font-size:15.5px;font-weight:800;
  cursor:pointer;display:flex;align-items:center;justify-content:center;gap:9px;
  transition:all .4s ease;box-shadow:0 4px 22px var(--gC),0 1px 4px rgba(0,0,0,.5);
}
.submit-btn:hover:not(:disabled){background-position:right center;transform:translateY(-2px);box-shadow:0 8px 32px var(--gC);}
.submit-btn:active:not(:disabled){transform:translateY(0);}
.submit-btn:disabled{opacity:.4;cursor:not-allowed;transform:none;box-shadow:none;}
.submit-btn::before{content:'';position:absolute;top:0;left:-100%;width:50%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);transition:left .6s ease;}
.submit-btn:hover:not(:disabled)::before{left:150%;}

/* ── Resend button ── */
.resend-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:10px;background:rgba(255,255,255,0.04);border:1.5px solid var(--bd);color:var(--tx-2);font-family:var(--fm);font-size:12.5px;font-weight:600;cursor:pointer;transition:all .24s;}
.resend-btn:hover:not(:disabled){background:var(--cyan-dim);border-color:var(--cyan-border);color:var(--cyan);}
.resend-btn:disabled{opacity:.35;cursor:not-allowed;}

/* ── Spinner ── */
.spinner{animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

/* ── Slide-up ── */
.su{animation:suA .5s ease both;}
.su-1{animation-delay:.04s;} .su-2{animation-delay:.10s;} .su-3{animation-delay:.16s;}
.su-4{animation-delay:.22s;} .su-5{animation-delay:.28s;} .su-6{animation-delay:.34s;}
@keyframes suA{from{opacity:0;transform:translateY(14px);}to{opacity:1;transform:translateY(0);}}

/* ── Icon bounce ── */
@keyframes bounce{0%,100%{transform:scale(1);}50%{transform:scale(1.1);filter:drop-shadow(0 0 10px rgba(0,212,200,.4));}}

@media(max-width:520px){
  .page-card{padding:24px 16px 22px;}
  .otp-box{width:44px;height:58px;font-size:22px;border-radius:10px;}
  .otp-row{gap:6px;}
  .otp-sep{width:10px;}
}
</style>
</head>

<body class="">
<!-- Apply theme to body before render -->
<script>
if(document.documentElement.classList.contains('lm-pre')){
  document.body.classList.add('lm');
  document.documentElement.classList.remove('lm-pre');
}
</script>

<div class="grad-bar"></div>
<div class="grid-tex"></div>
<div class="blob blob-c"></div>
<div class="blob blob-r"></div>
<div class="blob blob-v"></div>
<div class="noise-tex"></div>

<!-- ════════════════════════════════════════
     MAIN CARD
════════════════════════════════════════ -->
<div class="page-card">

  <!-- ── TOP BAR: Logo + Theme ── -->
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

  <!-- ── STEP PROGRESS PILLS ── -->
  <div class="flex gap-2 mb-6 su su-1" aria-label="Step progress">
    <div class="step-pip <?= $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' ?>"></div>
    <div class="step-pip <?= $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' ?>"></div>
    <div class="step-pip <?= $step >= 3 ? 'active' : '' ?>"></div>
  </div>
  <div class="flex justify-between text-[10px] font-bold uppercase tracking-wider mb-6 su su-1"
       style="color:var(--tx-3);font-family:var(--fm);">
    <span style="<?= $step===1 ? 'color:var(--cyan);' : ($step>1 ? 'color:var(--green);' : '') ?>">① Email</span>
    <span style="<?= $step===2 ? 'color:var(--cyan);' : ($step>2 ? 'color:var(--green);' : '') ?>">② Verify</span>
    <span style="<?= $step===3 ? 'color:var(--cyan);' : '' ?>">③ New Password</span>
  </div>

  <!-- ── ICON + HEADING ── -->
  <div class="text-center mb-5 su su-2">
    <div class="text-[40px] mb-3" style="animation:bounce 2.8s ease-in-out infinite;display:inline-block;">
      <?= $meta['icon'] ?>
    </div>
    <h1 class="font-heading font-black leading-tight tracking-[-1px] mb-2"
        style="font-size:clamp(22px,5vw,28px);color:var(--tx);">
      <?php if($step===1): ?>Forgot <span class="grad-text">Password?</span>
      <?php elseif($step===2): ?>Enter <span class="grad-text">Reset Code</span>
      <?php else: ?>Create <span class="grad-text">New Password</span>
      <?php endif; ?>
    </h1>
    <p class="text-[13.5px] leading-relaxed" style="color:var(--tx-2);">
      <?= htmlspecialchars($meta['sub']) ?>
    </p>
  </div>

  <!-- ── ALERTS ── -->
  <?php if(!empty($errors)): ?>
  <div class="alert alert-err su" id="alertBox">
    <span class="alert-ico">⚠️</span>
    <div><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  </div>
  <?php elseif($success): ?>
  <div class="alert alert-ok su" id="alertBox">
    <span class="alert-ico">✅</span>
    <div><?= htmlspecialchars($success) ?></div>
  </div>
  <?php endif; ?>

  <?php /* ══════════════════════════════════════
           STEP 1 — Email Input
         ══════════════════════════════════════ */ if($step===1): ?>

  <form method="POST" id="step1Form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="action"     value="send_otp">

    <div class="mb-5 su su-3">
      <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5"
             style="color:var(--tx-3);font-family:var(--fm);" for="email">
        Your Account Email
      </label>
      <div class="field-wrap">
        <span class="field-ico">✉️</span>
        <input type="email" name="email" id="email" class="gg-input <?= !empty($errors)?'is-error':'' ?>"
               placeholder="you@example.com" autocomplete="email"
               value="<?= htmlspecialchars($_POST['email']??'') ?>">
      </div>
      <div class="field-err" id="err-email"></div>
      <p class="text-[11.5px] mt-2 leading-relaxed" style="color:var(--tx-3);">
        We'll send a 6-digit reset code to this address. Check your spam folder if you don't see it.
      </p>
    </div>

    <div class="su su-4">
      <button type="submit" class="submit-btn" id="submitBtn">
        <svg id="btnSpinner" class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:none;">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"></circle>
          <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".8"></path>
        </svg>
        <span id="btnLabel">Send Reset Code →</span>
      </button>
    </div>
  </form>

  <?php /* ══════════════════════════════════════
           STEP 2 — OTP Verification
         ══════════════════════════════════════ */ elseif($step===2): ?>

  <!-- Email chip -->
  <div class="email-chip su su-2">
    <div class="ec-ico">✉️</div>
    <div>
      <div class="ec-lbl">Code sent to</div>
      <div class="ec-email"><?= htmlspecialchars(maskEmail($email)) ?></div>
    </div>
  </div>

  <!-- Dev OTP hint -->
  <?php if($demoOtp): ?>
  <div class="demo-chip su su-2">
    <div class="dc-label">🛠 Development Mode — Your Code</div>
    <div class="dc-code" id="demoCode"><?= htmlspecialchars($demoOtp) ?></div>
    <div class="dc-note">In production this is sent via email. Remove before launch.</div>
  </div>
  <?php endif; ?>

  <form method="POST" id="step2Form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="action"     value="verify_otp">

    <div class="text-center text-[10px] font-extrabold uppercase tracking-widest mb-3 su su-3"
         style="color:var(--tx-3);font-family:var(--fm);">
      Enter 6-digit code
    </div>

    <!-- OTP boxes -->
    <div class="otp-row su su-3" id="otpRow">
      <?php for($i=1;$i<=6;$i++): ?>
      <?php if($i===4): ?><div class="otp-sep" aria-hidden="true"></div><?php endif; ?>
      <input type="text" name="d<?= $i ?>" id="d<?= $i ?>" class="otp-box"
             maxlength="1" inputmode="numeric" pattern="[0-9]"
             <?= $i===1?'autocomplete="one-time-code"':'autocomplete="off"' ?>
             aria-label="Digit <?= $i ?>">
      <?php endfor; ?>
    </div>

    <!-- Progress dots -->
    <div class="otp-dots" id="otpDots" aria-hidden="true">
      <?php for($i=1;$i<=6;$i++): ?>
      <div class="otp-dot <?= $i===1?'is-active':'' ?>" id="dot<?= $i ?>"></div>
      <?php endfor; ?>
    </div>

    <!-- Timer -->
    <div class="timer-row su su-4" id="timerWrap">
      <div class="timer-svg-wrap" id="timerSvgWrap">
        <svg class="timer-svg" viewBox="0 0 44 44" aria-hidden="true">
          <circle class="t-track" cx="22" cy="22" r="17"/>
          <circle class="t-ring"  cx="22" cy="22" r="17" id="timerRing"/>
        </svg>
        <div class="t-inner" id="timerInner">--</div>
      </div>
      <div class="timer-txt" id="timerTxt">
        Code expires in <strong id="timerCount">--:--</strong>
      </div>
    </div>

    <div class="su su-5">
      <button type="submit" class="submit-btn" id="verifyBtn" disabled>
        <svg id="verSpinner" class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:none;">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"></circle>
          <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".8"></path>
        </svg>
        <span id="verLabel">Verify Code →</span>
      </button>
    </div>
  </form>

  <!-- Resend -->
  <div class="flex items-center justify-between gap-3 mt-3 su su-5">
    <form method="POST" id="resendForm">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action"     value="resend_otp">
      <button type="submit" class="resend-btn" id="resendBtn" disabled>
        <span id="resendIcon">↺</span>
        <span id="resendLabel">Resend code</span>
      </button>
    </form>
    <span class="text-[11px]" style="color:var(--tx-3);">
      <span id="resendLeft"><?= $resendLeft ?></span>/3 left
    </span>
  </div>
  <p class="text-[11px] text-center mt-2 su su-5" style="color:var(--tx-3);" id="cooldownNote">
    Resend available after <strong style="color:var(--tx-2);">30 seconds</strong>.
  </p>

  <?php /* ══════════════════════════════════════
           STEP 3 — New Password
         ══════════════════════════════════════ */ else: ?>

  <form method="POST" id="step3Form" novalidate>
    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
    <input type="hidden" name="action"     value="set_password">

    <!-- New password -->
    <div class="mb-3.5 su su-3">
      <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5"
             style="color:var(--tx-3);font-family:var(--fm);" for="password">
        New Password
      </label>
      <div class="field-wrap">
        <span class="field-ico">🔑</span>
        <input type="password" name="password" id="password" class="gg-input"
               placeholder="Create a strong password" autocomplete="new-password"
               style="padding-right:42px;">
        <button type="button" class="pwd-eye" onclick="togglePwd('password',this)">👁</button>
      </div>
      <!-- Strength meter -->
      <div id="strengthWrap" style="display:none;margin-top:9px;">
        <div class="str-segs">
          <div class="str-seg" id="seg1"></div><div class="str-seg" id="seg2"></div>
          <div class="str-seg" id="seg3"></div><div class="str-seg" id="seg4"></div>
        </div>
        <div class="flex justify-between text-[11px] mb-1">
          <span class="font-bold" id="strLabel" style="color:var(--tx-3);">Enter password</span>
          <span id="strPct" style="color:var(--tx-3);">0%</span>
        </div>
        <div class="pwd-rules">
          <div class="pwd-rule" id="r-len"><div class="rule-c"></div>8+ chars</div>
          <div class="pwd-rule" id="r-upper"><div class="rule-c"></div>Uppercase</div>
          <div class="pwd-rule" id="r-num"><div class="rule-c"></div>Number (0–9)</div>
          <div class="pwd-rule" id="r-special"><div class="rule-c"></div>Special char</div>
        </div>
      </div>
      <div class="field-err" id="err-pwd"></div>
    </div>

    <!-- Confirm password -->
    <div class="mb-5 su su-4">
      <label class="block text-[10px] font-extrabold uppercase tracking-[.7px] mb-1.5"
             style="color:var(--tx-3);font-family:var(--fm);" for="confirmPwd">
        Confirm New Password
      </label>
      <div class="field-wrap">
        <span class="field-ico">🔒</span>
        <input type="password" name="confirm_password" id="confirmPwd" class="gg-input"
               placeholder="Repeat your password" autocomplete="new-password"
               style="padding-right:42px;">
        <button type="button" class="pwd-eye" onclick="togglePwd('confirmPwd',this)">👁</button>
      </div>
      <div class="field-err" id="err-confirm"></div>
    </div>

    <div class="su su-5">
      <button type="submit" class="submit-btn" id="setPwdBtn">
        <svg id="setPwdSpinner" class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" style="display:none;">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"></circle>
          <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" opacity=".8"></path>
        </svg>
        <span id="setPwdLabel">Update Password →</span>
      </button>
    </div>
  </form>

  <?php endif; ?>

  <!-- ── FOOTER LINKS ── -->
  <div class="flex items-center justify-between mt-5 pt-4 su su-6" style="border-top:1px solid var(--bd);">
    <?php if($step > 1): ?>
    <form method="POST" style="margin:0;">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <input type="hidden" name="action"     value="send_otp">
      <button type="button"
              onclick="if(confirm('Restart the password reset process?')){sessionReset()}"
              class="text-[12px] hover:underline flex items-center gap-1.5"
              style="color:var(--tx-3);background:none;border:none;cursor:pointer;font-family:var(--fb);">
        ← Start over
      </button>
    </form>
    <?php else: ?>
    <span></span>
    <?php endif; ?>
    <a href="<?= APP_URL ?>/auth/login.php"
       class="text-[12.5px] font-semibold hover:underline"
       style="color:var(--cyan);text-decoration:none;">
      Back to Sign In →
    </a>
  </div>

  <!-- Security note -->
  <div class="flex items-start gap-2.5 mt-4 p-3 rounded-xl su su-6"
       style="background:rgba(255,255,255,0.025);border:1px solid var(--bd);">
    <span style="font-size:13px;flex-shrink:0;">🔒</span>
    <p class="text-[11px] leading-relaxed" style="color:var(--tx-3);">
      Reset codes expire in <strong style="color:var(--tx-2);"><?= OTP_EXPIRY_MINUTES ?> minutes</strong>
      and are one-time use only. Never share this code with anyone.
    </p>
  </div>

</div><!-- /page-card -->

<script>
(function(){
'use strict';

/* ══════════════════════════════════════════════
   THEME SYNC — identical pattern to all auth pages
══════════════════════════════════════════════ */
function applyTheme(isLight){
  document.body.classList.toggle('lm', isLight);
  const btn = document.getElementById('themeBtn');
  if(btn) btn.textContent = isLight ? '☀️' : '🌙';
}
applyTheme(localStorage.getItem('gg_theme') === 'light');
window.toggleTheme = function(){
  const nowLight = !document.body.classList.contains('lm');
  localStorage.setItem('gg_theme', nowLight ? 'light' : 'dark');
  applyTheme(nowLight);
};
window.addEventListener('storage', e => {
  if(e.key === 'gg_theme') applyTheme(e.newValue === 'light');
});

/* ── Session reset helper ── */
window.sessionReset = function(){
  fetch('', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=send_otp&csrf_token=<?= urlencode($csrf) ?>&email='
  }).finally(()=>location.href='<?= APP_URL ?>/auth/forgot-password.php?restart=1');
};

/* ══════════════════════════════════════════════
   STEP 1 — Email form
══════════════════════════════════════════════ */
<?php if($step===1): ?>
const emailEl = document.getElementById('email');
const emailRx = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function showErr(id,msg){ const e=document.getElementById(id); if(e){e.textContent='⚠ '+msg;e.classList.add('show');} }
function clearErr(id){ const e=document.getElementById(id); if(e){e.textContent='';e.classList.remove('show');} }

emailEl?.addEventListener('blur', function(){
  if(!this.value.trim()) return;
  if(!emailRx.test(this.value.trim())){
    this.classList.add('is-error'); showErr('err-email','Enter a valid email address.');
  } else { this.classList.remove('is-error'); this.classList.add('is-valid'); clearErr('err-email'); }
});
emailEl?.addEventListener('input', function(){
  if(this.classList.contains('is-error') && emailRx.test(this.value.trim())){
    this.classList.remove('is-error'); this.classList.add('is-valid'); clearErr('err-email');
  }
});

document.getElementById('step1Form')?.addEventListener('submit', function(e){
  if(!emailEl.value.trim() || !emailRx.test(emailEl.value.trim())){
    e.preventDefault();
    emailEl.classList.add('is-error'); showErr('err-email','Enter a valid email address.');
    return;
  }
  document.getElementById('submitBtn').disabled = true;
  document.getElementById('btnSpinner').style.display = 'block';
  document.getElementById('btnLabel').textContent = 'Sending…';
});

setTimeout(()=>emailEl?.focus(), 300);
<?php endif; ?>

/* ══════════════════════════════════════════════
   STEP 2 — OTP inputs + timer
══════════════════════════════════════════════ */
<?php if($step===2): ?>
const inputs      = Array.from(document.querySelectorAll('.otp-box'));
const dots        = Array.from({length:6},(_,i)=>document.getElementById('dot'+(i+1)));
const verifyBtn   = document.getElementById('verifyBtn');
const verLabel    = document.getElementById('verLabel');
const verSpinner  = document.getElementById('verSpinner');
const resendBtn   = document.getElementById('resendBtn');
const resendLabel = document.getElementById('resendLabel');
const resendIcon  = document.getElementById('resendIcon');
const coolNote    = document.getElementById('cooldownNote');
const timerRing   = document.getElementById('timerRing');
const timerInner  = document.getElementById('timerInner');
const timerCount  = document.getElementById('timerCount');
const timerTxt    = document.getElementById('timerTxt');

const TOTAL = <?= OTP_EXPIRY_MINUTES * 60 ?>;
const CIRC  = 2 * Math.PI * 17; // r=17
timerRing.style.strokeDasharray = CIRC;

let remaining = TOTAL, timerID = null, resendUnlocked = false;

function fmt(s){ return Math.floor(s/60)+':'+String(s%60).padStart(2,'0'); }

function tick(){
  remaining = Math.max(0, remaining-1);
  timerRing.style.strokeDashoffset = CIRC * (1 - remaining/TOTAL);
  timerInner.textContent = fmt(remaining);
  timerCount.textContent = fmt(remaining);
  if(remaining <= 60 && remaining > 0){
    timerRing.style.stroke='var(--amber)'; timerInner.style.color='var(--amber)';
  }
  if(remaining === 0){
    clearInterval(timerID);
    timerRing.style.stroke='var(--red)'; timerInner.style.color='var(--red)';
    timerTxt.innerHTML='<strong style="color:var(--red);">Code expired — request a new one</strong>';
    verifyBtn.disabled=true; verLabel.textContent='Code Expired';
    unlockResend();
    return;
  }
  if(!resendUnlocked && remaining <= TOTAL-30){ resendUnlocked=true; unlockResend(); }
}

function unlockResend(){
  const left = parseInt(document.getElementById('resendLeft')?.textContent)||0;
  if(left > 0){ resendBtn.disabled=false; resendLabel.textContent='Resend code'; resendIcon.textContent='↺'; }
  else { resendLabel.textContent='No resends left'; }
  if(coolNote) coolNote.style.display='none';
}

timerInner.textContent = fmt(remaining);
timerCount.textContent = fmt(remaining);
timerID = setInterval(tick,1000);

/* OTP input logic */
function syncDots(){
  const first = inputs.findIndex(i=>!i.value);
  dots.forEach((d,i)=>{ d.classList.remove('is-active','is-filled'); if(inputs[i]?.value) d.classList.add('is-filled'); });
  if(first>=0 && dots[first]) dots[first].classList.add('is-active');
}
function allFilled(){ return inputs.every(i=>i.value.length===1); }
function checkComplete(){ verifyBtn.disabled = !allFilled() || remaining===0; syncDots(); }

inputs.forEach((inp,idx)=>{
  inp.addEventListener('focus',function(){ this.select(); });
  inp.addEventListener('input',function(){
    const v = this.value.replace(/\D/g,'').slice(0,1);
    this.value=v;
    if(v){ this.classList.add('filled'); this.classList.remove('is-error'); if(idx<inputs.length-1) inputs[idx+1].focus(); }
    else  { this.classList.remove('filled'); }
    checkComplete();
  });
  inp.addEventListener('keydown',function(e){
    if(e.key==='Backspace'){
      if(!this.value && idx>0){ e.preventDefault(); inputs[idx-1].value=''; inputs[idx-1].classList.remove('filled'); inputs[idx-1].focus(); }
      else { this.value=''; this.classList.remove('filled'); }
      checkComplete();
    }
    if(e.key==='ArrowLeft'  && idx>0)              { e.preventDefault(); inputs[idx-1].focus(); }
    if(e.key==='ArrowRight' && idx<inputs.length-1){ e.preventDefault(); inputs[idx+1].focus(); }
    if(e.key==='Enter' && allFilled() && remaining>0) document.getElementById('step2Form').requestSubmit();
  });
  inp.addEventListener('paste',function(e){
    e.preventDefault();
    const txt=(e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
    if(!txt) return;
    txt.split('').forEach((ch,i)=>{ if(inputs[i]){ inputs[i].value=ch; inputs[i].classList.add('filled'); } });
    const nxt=inputs.findIndex(i=>!i.value);
    (nxt>=0 ? inputs[nxt] : inputs[inputs.length-1]).focus();
    checkComplete();
  });
});

/* Auto-fill from dev hint */
const demo = document.getElementById('demoCode');
if(demo){
  const code=demo.textContent.replace(/\D/g,'');
  if(code.length===6) setTimeout(()=>{
    code.split('').forEach((ch,i)=>{ if(inputs[i]){ inputs[i].value=ch; inputs[i].classList.add('filled'); } });
    checkComplete(); inputs[5].focus();
  },600);
}

syncDots();

/* Form submit */
document.getElementById('step2Form')?.addEventListener('submit',function(e){
  if(!allFilled()||remaining===0){
    e.preventDefault();
    inputs.forEach(i=>i.classList.add('is-error'));
    setTimeout(()=>inputs.forEach(i=>i.classList.remove('is-error')),600);
    return;
  }
  verifyBtn.disabled=true; verSpinner.style.display='block'; verLabel.textContent='Verifying…';
  clearInterval(timerID);
});

/* Resend submit */
document.getElementById('resendForm')?.addEventListener('submit',function(){
  resendBtn.disabled=true; resendLabel.textContent='Sending…'; resendIcon.textContent='⏳';
});

/* PHP error → shake + clear */
<?php if(!empty($errors)): ?>
setTimeout(()=>{
  inputs.forEach(i=>{ i.classList.add('is-error'); i.value=''; i.classList.remove('filled'); });
  dots.forEach(d=>d.classList.remove('is-filled','is-active'));
  if(dots[0]) dots[0].classList.add('is-active');
  setTimeout(()=>inputs.forEach(i=>i.classList.remove('is-error')),600);
  inputs[0].focus(); checkComplete();
},100);
<?php endif; ?>
<?php endif; ?>

/* ══════════════════════════════════════════════
   STEP 3 — New password + strength meter
══════════════════════════════════════════════ */
<?php if($step===3): ?>
function showErr(id,msg){ const e=document.getElementById(id); if(e){e.textContent='⚠ '+msg;e.classList.add('show');} }
function clearErr(id){ const e=document.getElementById(id); if(e){e.textContent='';e.classList.remove('show');} }

window.togglePwd = function(id,btn){
  const el=document.getElementById(id);
  el.type = el.type==='password' ? 'text' : 'password';
  btn.textContent = el.type==='password' ? '👁' : '🙈';
};

const pwdEl  = document.getElementById('password');
const confEl = document.getElementById('confirmPwd');
const swrap  = document.getElementById('strengthWrap');
const segs   = [1,2,3,4].map(i=>document.getElementById('seg'+i));
const strLbl = document.getElementById('strLabel');
const strPct = document.getElementById('strPct');
const strMeta= [{label:'Too weak',cls:'s1',pct:'25%'},{label:'Weak',cls:'s2',pct:'50%'},{label:'Good',cls:'s3',pct:'75%'},{label:'Strong ✓',cls:'s4',pct:'100%'}];
const rules  = [
  {id:'r-len',    test:v=>v.length>=8},
  {id:'r-upper',  test:v=>/[A-Z]/.test(v)},
  {id:'r-num',    test:v=>/[0-9]/.test(v)},
  {id:'r-special',test:v=>/[^A-Za-z0-9]/.test(v)},
];

pwdEl.addEventListener('input',function(){
  const v=this.value;
  if(!v){ swrap.style.display='none'; return; }
  swrap.style.display='block';
  let score=0;
  rules.forEach(r=>{
    const met=r.test(v);
    const el=document.getElementById(r.id);
    el.classList.toggle('met',met);
    const rc=el.querySelector('.rule-c');
    if(rc) rc.textContent=met?'✓':'';
    if(met) score++;
  });
  segs.forEach((s,i)=>{ s.className='str-seg'; if(i<score) s.classList.add(strMeta[score-1].cls); });
  const m=strMeta[Math.max(0,score-1)];
  strLbl.textContent=score===0?'Enter password':m.label;
  strPct.textContent=score===0?'0%':m.pct;
  if(this.classList.contains('is-error')&&score>=3){ this.classList.remove('is-error'); this.classList.add('is-valid'); clearErr('err-pwd'); }
});

pwdEl.addEventListener('blur',function(){
  if(!this.value) return;
  const v=this.value;
  if(v.length<8)           { this.classList.add('is-error'); showErr('err-pwd','Must be at least 8 characters.'); }
  else if(!/[A-Z]/.test(v)){ this.classList.add('is-error'); showErr('err-pwd','Must contain an uppercase letter.'); }
  else if(!/[0-9]/.test(v)){ this.classList.add('is-error'); showErr('err-pwd','Must contain a number.'); }
  else                      { this.classList.remove('is-error'); this.classList.add('is-valid'); clearErr('err-pwd'); }
});

function checkConf(){
  if(!confEl.value) return;
  if(confEl.value!==pwdEl.value){ confEl.classList.add('is-error'); confEl.classList.remove('is-valid'); showErr('err-confirm','Passwords do not match.'); }
  else { confEl.classList.remove('is-error'); confEl.classList.add('is-valid'); clearErr('err-confirm'); }
}
confEl.addEventListener('blur',checkConf);
confEl.addEventListener('input',checkConf);

document.getElementById('step3Form')?.addEventListener('submit',function(e){
  let bad=false;
  const v=pwdEl.value;
  if(!v||v.length<8||!/[A-Z]/.test(v)||!/[0-9]/.test(v)){
    pwdEl.classList.add('is-error'); showErr('err-pwd','Password does not meet requirements.'); bad=true;
  }
  if(confEl.value!==pwdEl.value){ confEl.classList.add('is-error'); showErr('err-confirm','Passwords do not match.'); bad=true; }
  if(bad){ e.preventDefault(); return; }
  document.getElementById('setPwdBtn').disabled=true;
  document.getElementById('setPwdSpinner').style.display='block';
  document.getElementById('setPwdLabel').textContent='Updating…';
});

setTimeout(()=>pwdEl?.focus(),300);
<?php endif; ?>

/* Auto-dismiss success alert */
<?php if($success): ?>
setTimeout(()=>{
  const a=document.getElementById('alertBox');
  if(a){ a.style.transition='opacity .5s'; a.style.opacity='0'; setTimeout(()=>a.remove(),500); }
},4000);
<?php endif; ?>

})();
</script>
</body>
</html>