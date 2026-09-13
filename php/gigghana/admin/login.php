<?php
/**
 * GigGhana — admin/login.php
 * Standalone admin-only login page.
 * Completely separate from the main user auth system.
 *
 * FLOW:
 *   1. If admin session already active → redirect to dashboard
 *   2. POST → admin/auth.php processes credentials
 *   3. On success → $_SESSION['admin_id'], ['admin_role'] set → dashboard
 *   4. On fail    → back here with ?error=1
 *
 * SESSION KEYS SET ON SUCCESS (in auth.php):
 *   $_SESSION['admin_logged_in'] = true
 *   $_SESSION['admin_id']        = (int) users.id
 *   $_SESSION['user_id']         = (int) users.id   ← what dashboard reads
 *   $_SESSION['user_role']       = 'admin'
 *   $_SESSION['admin_name']      = first + last name
 *   $_SESSION['admin_email']     = email
 */

session_start();

/* Already logged in as admin? Go straight to dashboard */
if (
    isset($_SESSION['admin_logged_in']) &&
    $_SESSION['admin_logged_in'] === true &&
    ($_SESSION['user_role'] ?? '') === 'admin'
) {
    header('Location: dashboard.php');
    exit;
}

$error   = $_GET['error']   ?? '';
$expired = $_GET['expired'] ?? '';
$added   = $_GET['added']   ?? '';

/* Map error codes to human messages */
$errorMsg = match($error) {
    'invalid'  => 'Wrong email or password. Try again.',
    'notadmin' => 'This account does not have admin access.',
    'banned'   => 'This admin account has been suspended.',
    'csrf'     => 'Security token mismatch. Please try again.',
    'empty'    => 'Please enter your email and password.',
    default    => ''
};

/* Generate a CSRF token for the form */
if (empty($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['admin_csrf'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>GigGhana — Admin Access</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Space+Mono:wght@400;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ── Design: same "Mission Control" language as the dashboard ── */
:root {
  --bg:    #080B12;
  --s1:    #0D1120;
  --s2:    #111827;
  --s3:    #1A2236;
  --lime:  #AAFF00;
  --lime-d:#88CC00;
  --lime-dim: rgba(170,255,0,0.07);
  --lime-border: rgba(170,255,0,0.22);
  --coral: #FF4757;
  --coral-dim: rgba(255,71,87,0.08);
  --coral-border: rgba(255,71,87,0.25);
  --sky:   #00C8FF;
  --sky-dim: rgba(0,200,255,0.07);
  --tx:    #E8EDF8;
  --tx-2:  #8896B0;
  --tx-3:  #3D4A61;
  --bd:    rgba(255,255,255,0.06);
  --bd2:   rgba(255,255,255,0.12);
  --fm: 'Syne', sans-serif;
  --fc: 'Space Mono', monospace;
  --fb: 'Inter', sans-serif;
  --e: all 0.24s cubic-bezier(.4,0,.2,1);
}

*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
html, body {
  height:100%; background:var(--bg); color:var(--tx);
  font-family:var(--fb); -webkit-font-smoothing:antialiased;
}

/* Scanline overlay */
body::before {
  content:''; position:fixed; inset:0; pointer-events:none; z-index:1;
  background:repeating-linear-gradient(
    0deg, transparent, transparent 2px,
    rgba(0,0,0,0.018) 2px, rgba(0,0,0,0.018) 4px
  );
}

/* Animated grid background */
.bg-grid {
  position:fixed; inset:0; z-index:0;
  background-image:
    linear-gradient(rgba(170,255,0,0.028) 1px, transparent 1px),
    linear-gradient(90deg, rgba(170,255,0,0.028) 1px, transparent 1px);
  background-size:48px 48px;
  animation:gridDrift 20s linear infinite;
}
@keyframes gridDrift {
  0%   { background-position:0 0; }
  100% { background-position:48px 48px; }
}

/* Glow blobs */
.blob {
  position:fixed; border-radius:50%; filter:blur(90px); pointer-events:none; z-index:0;
}
.blob-1 {
  width:500px; height:500px; top:-180px; right:-150px;
  background:radial-gradient(circle, rgba(170,255,0,0.07), transparent 70%);
  animation:blobFloat1 12s ease-in-out infinite;
}
.blob-2 {
  width:400px; height:400px; bottom:-120px; left:-100px;
  background:radial-gradient(circle, rgba(0,200,255,0.06), transparent 70%);
  animation:blobFloat2 15s ease-in-out infinite;
}
@keyframes blobFloat1 { 0%,100%{transform:translate(0,0);}50%{transform:translate(-30px,40px);} }
@keyframes blobFloat2 { 0%,100%{transform:translate(0,0);}50%{transform:translate(40px,-30px);} }

/* ── LAYOUT ── */
.page {
  position:relative; z-index:2;
  min-height:100vh; display:flex;
  align-items:center; justify-content:center;
  padding:24px 16px;
}

/* ── CARD ── */
.card {
  width:100%; max-width:420px;
  background:var(--s1);
  border:1px solid var(--bd2);
  border-radius:20px;
  box-shadow:0 32px 80px rgba(0,0,0,0.55),
             0 0 0 1px rgba(170,255,0,0.04) inset;
  overflow:hidden;
  animation:cardIn .5s cubic-bezier(.16,1,.3,1);
}
@keyframes cardIn {
  from { opacity:0; transform:translateY(24px) scale(0.97); }
  to   { opacity:1; transform:translateY(0) scale(1); }
}

/* Top accent bar */
.card::before {
  content:''; display:block; height:2px;
  background:linear-gradient(90deg,
    transparent 0%, var(--lime) 30%, var(--sky) 70%, transparent 100%
  );
}

/* ── HEADER ── */
.card-header {
  padding:32px 36px 24px;
  text-align:center;
  border-bottom:1px solid var(--bd);
}
.logo-wrap {
  display:inline-flex; align-items:center; gap:10px;
  margin-bottom:24px;
}
.logo-mark {
  width:42px; height:42px; border-radius:11px;
  background:var(--lime);
  display:flex; align-items:center; justify-content:center;
  font-family:var(--fm); font-weight:800; font-size:20px; color:#080B12;
}
.logo-text {
  font-family:var(--fm); font-size:22px; font-weight:800; color:var(--tx);
}
.logo-text span { color:var(--lime); }

.card-title {
  font-family:var(--fm); font-size:20px; font-weight:800;
  margin-bottom:6px; color:var(--tx);
}
.card-sub {
  font-family:var(--fc); font-size:11px; color:var(--tx-3);
  letter-spacing:0.5px;
}

/* Access badge */
.access-badge {
  display:inline-flex; align-items:center; gap:6px;
  margin-top:14px; padding:5px 14px; border-radius:6px;
  background:var(--lime-dim); border:1px solid var(--lime-border);
  font-family:var(--fc); font-size:10px; font-weight:700; color:var(--lime);
  letter-spacing:1px;
}
.access-dot {
  width:6px; height:6px; border-radius:50%; background:var(--lime);
  animation:accessPulse 2s infinite;
}
@keyframes accessPulse {
  0%,100% { box-shadow:0 0 0 0 rgba(170,255,0,.6); }
  50%     { box-shadow:0 0 0 5px rgba(170,255,0,0); }
}

/* ── BODY ── */
.card-body { padding:28px 36px 32px; }

/* Error banner */
.error-banner {
  display:flex; align-items:center; gap:10px;
  background:var(--coral-dim); border:1px solid var(--coral-border);
  border-radius:10px; padding:12px 16px; margin-bottom:20px;
  font-size:13px; color:var(--coral);
  animation:shakeIn .35s cubic-bezier(.36,.07,.19,.97);
}
@keyframes shakeIn {
  0%,100%{transform:translateX(0);}
  20%    {transform:translateX(-6px);}
  40%    {transform:translateX(6px);}
  60%    {transform:translateX(-4px);}
  80%    {transform:translateX(4px);}
}
.error-icon { font-size:16px; flex-shrink:0; }

/* Expired / success banner */
.info-banner {
  display:flex; align-items:center; gap:10px;
  background:var(--sky-dim); border:1px solid rgba(0,200,255,0.2);
  border-radius:10px; padding:12px 16px; margin-bottom:20px;
  font-size:13px; color:var(--sky);
}
.success-banner {
  display:flex; align-items:center; gap:10px;
  background:rgba(0,230,118,0.07); border:1px solid rgba(0,230,118,0.2);
  border-radius:10px; padding:12px 16px; margin-bottom:20px;
  font-size:13px; color:#00E676;
}

/* ── FORM ── */
.form-group { margin-bottom:18px; }
.form-label {
  display:block; font-family:var(--fc); font-size:9.5px; font-weight:700;
  letter-spacing:1.4px; text-transform:uppercase; color:var(--tx-3);
  margin-bottom:8px;
}

/* Input wrapper — icon + field */
.input-wrap {
  position:relative; display:flex; align-items:center;
}
.input-icon {
  position:absolute; left:13px; font-size:15px;
  color:var(--tx-3); pointer-events:none; transition:color .25s;
  z-index:1;
}
.form-input {
  width:100%; background:var(--s3);
  border:1px solid var(--bd); border-radius:11px;
  padding:12px 42px 12px 40px;
  color:var(--tx); font-family:var(--fb); font-size:14px;
  outline:none; transition:var(--e);
  -webkit-appearance:none;
}
.form-input:focus {
  border-color:var(--lime);
  box-shadow:0 0 0 3px rgba(170,255,0,0.09);
}
.form-input:focus + .input-icon,
.input-wrap:focus-within .input-icon {
  color:var(--lime);
}
/* Password toggle eye */
.pw-toggle {
  position:absolute; right:13px; font-size:16px; cursor:pointer;
  color:var(--tx-3); transition:color .2s; background:none; border:none;
  padding:0; line-height:1;
}
.pw-toggle:hover { color:var(--lime); }

/* Remember me row */
.remember-row {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:22px; font-size:12.5px;
}
.remember-label {
  display:flex; align-items:center; gap:8px; cursor:pointer;
  color:var(--tx-2);
}
.remember-check {
  width:16px; height:16px; border-radius:4px;
  border:1px solid var(--bd2); background:var(--s3);
  appearance:none; cursor:pointer; transition:var(--e); flex-shrink:0;
}
.remember-check:checked {
  background:var(--lime); border-color:var(--lime);
  background-image:url("data:image/svg+xml,%3Csvg width='10' height='8' viewBox='0 0 10 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4L3.5 6.5L9 1' stroke='%23080B12' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:center;
}
.forgot-link {
  color:var(--tx-3); font-size:12px; font-family:var(--fc);
  text-decoration:none; transition:color .2s;
}
.forgot-link:hover { color:var(--lime); }

/* Submit button */
.btn-submit {
  width:100%; padding:14px;
  background:var(--lime); color:#080B12;
  font-family:var(--fm); font-size:15px; font-weight:800;
  border:none; border-radius:11px; cursor:pointer;
  transition:var(--e); position:relative; overflow:hidden;
  letter-spacing:0.3px;
}
.btn-submit:hover {
  background:var(--lime-d);
  transform:translateY(-2px);
  box-shadow:0 10px 30px rgba(170,255,0,0.22);
}
.btn-submit:active { transform:translateY(0); }
.btn-submit.loading { opacity:.7; pointer-events:none; }
.btn-submit .btn-text { transition:opacity .2s; }
.btn-submit .btn-spinner {
  display:none; position:absolute; inset:0;
  align-items:center; justify-content:center;
}
.btn-submit.loading .btn-text { opacity:0; }
.btn-submit.loading .btn-spinner { display:flex; }

/* Spinner */
.spinner {
  width:20px; height:20px; border:2px solid rgba(8,11,18,0.3);
  border-top-color:#080B12; border-radius:50%;
  animation:spin .7s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }

/* ── FOOTER ── */
.card-footer {
  padding:16px 36px 24px;
  text-align:center; border-top:1px solid var(--bd);
}
.footer-note {
  font-family:var(--fc); font-size:10px; color:var(--tx-3);
  line-height:1.7;
}
.footer-note a {
  color:var(--tx-3); text-decoration:none; transition:color .2s;
}
.footer-note a:hover { color:var(--lime); }
.footer-divider {
  display:inline-block; margin:0 8px; opacity:0.3;
}

/* ── BOTTOM LINK ── */
.back-link {
  text-align:center; margin-top:22px;
  font-size:12px; color:var(--tx-3);
}
.back-link a {
  color:var(--lime); text-decoration:none; font-family:var(--fc);
  font-weight:700; font-size:11px;
}
.back-link a:hover { text-decoration:underline; }

/* ── SECURITY NOTE ── */
.security-note {
  display:flex; align-items:center; gap:8px;
  margin-top:16px; padding:10px 14px;
  background:rgba(0,0,0,0.2); border-radius:9px;
  font-family:var(--fc); font-size:10px; color:var(--tx-3);
  border:1px solid var(--bd);
}
</style>
</head>
<body>

<div class="bg-grid"></div>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="page">
  <div class="card">

    <!-- HEADER -->
    <div class="card-header">
      <div class="logo-wrap">
        <div class="logo-mark">G</div>
        <span class="logo-text">Gig<span>Ghana</span></span>
      </div>
      <div class="card-title">Admin Access</div>
      <div class="card-sub">RESTRICTED · AUTHORISED PERSONNEL ONLY</div>
      <div class="access-badge">
        <div class="access-dot"></div>
        SECURE LOGIN PORTAL
      </div>
    </div>

    <!-- BODY -->
    <div class="card-body">

      <?php if($expired): ?>
      <div class="info-banner">
        <span>🔒</span>
        Your session expired. Please sign in again.
      </div>
      <?php endif; ?>

      <?php if($added): ?>
      <div class="success-banner">
        <span>✅</span>
        New admin account created. Sign in below.
      </div>
      <?php endif; ?>

      <?php if($errorMsg): ?>
      <div class="error-banner">
        <span class="error-icon">⚠️</span>
        <?= htmlspecialchars($errorMsg) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="auth.php" id="loginForm" autocomplete="off" novalidate>
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

        <!-- Email -->
        <div class="form-group">
          <label class="form-label" for="email">Email Address</label>
          <div class="input-wrap">
            <span class="input-icon">✉️</span>
            <input
              type="email"
              id="email"
              name="email"
              class="form-input"
              placeholder="admin@gigghana.com"
              value="<?= htmlspecialchars($_GET['email'] ?? '') ?>"
              autocomplete="username"
              required
            >
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔑</span>
            <input
              type="password"
              id="password"
              name="password"
              class="form-input"
              placeholder="Enter your password"
              autocomplete="current-password"
              required
            >
            <button type="button" class="pw-toggle" onclick="togglePw()" id="pwToggle" title="Show/hide password">
              👁
            </button>
          </div>
        </div>

        <!-- Remember + Forgot -->
        <div class="remember-row">
          <label class="remember-label">
            <input type="checkbox" name="remember" class="remember-check" value="1">
            Keep me signed in
          </label>
          <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-submit" id="submitBtn">
          <span class="btn-text">🔓 &nbsp;Sign In to Admin Panel</span>
          <span class="btn-spinner"><div class="spinner"></div></span>
        </button>

        <div class="security-note">
          🔒 All admin sessions are encrypted and logged for security auditing.
        </div>
      </form>
    </div>

    <!-- FOOTER -->
    <div class="card-footer">
      <div class="footer-note">
        <a href="../index.php">← Back to GigGhana</a>
        <span class="footer-divider">|</span>
        <a href="../auth/login.php">User Login</a>
        <span class="footer-divider">|</span>
        <a href="mailto:support@gigghana.com">Support</a>
      </div>
    </div>

  </div><!-- /card -->
</div><!-- /page -->

<script>
/* Password show/hide */
function togglePw() {
  const input = document.getElementById('password');
  const btn   = document.getElementById('pwToggle');
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}

/* Form submit — show spinner, validate */
document.getElementById('loginForm').addEventListener('submit', function(e) {
  const email = document.getElementById('email').value.trim();
  const pw    = document.getElementById('password').value;

  if (!email || !pw) {
    e.preventDefault();
    /* Add shake if not already showing error */
    if (!document.querySelector('.error-banner')) {
      const div = document.createElement('div');
      div.className = 'error-banner';
      div.innerHTML = '<span class="error-icon">⚠️</span> Please fill in both fields.';
      document.getElementById('loginForm').prepend(div);
    }
    return;
  }

  /* Show loading state */
  document.getElementById('submitBtn').classList.add('loading');
});

/* Auto-focus email field */
window.addEventListener('load', () => {
  const emailField = document.getElementById('email');
  if (emailField && !emailField.value) emailField.focus();
});
</script>
</body>
</html>