<?php
/**
 * GigGhana — admin/auth.php
 * Processes the admin login form POST.
 *
 * WHAT IT DOES:
 *  1. Validates CSRF token
 *  2. Looks up user by email in the `users` table
 *  3. Verifies role = 'admin'  ← this is why regular users can't get in
 *  4. Verifies password with password_verify() against bcrypt hash
 *  5. Checks is_banned = 0
 *  6. Sets session variables that admin/dashboard.php expects:
 *       $_SESSION['admin_logged_in'] = true
 *       $_SESSION['user_id']         = users.id
 *       $_SESSION['user_role']       = 'admin'
 *       $_SESSION['admin_id']        = users.id
 *       $_SESSION['admin_name']      = full name
 *       $_SESSION['admin_email']     = email
 *  7. Handles "remember me" via a 30-day cookie
 *  8. Regenerates session ID to prevent fixation attacks
 *
 * ONLY ACCEPTS POST. Any GET → redirect to login.
 */

session_start();

/* Only handle POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

/* ── Load config ─────────────────────────────────────── */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

/* ── Rate limiting (simple session-based) ─────────────
   Max 5 failed attempts per 15 minutes per session.      */
$now     = time();
$window  = 15 * 60; // 15 minutes
$maxTries = 5;

if (!isset($_SESSION['admin_login_attempts'])) {
    $_SESSION['admin_login_attempts']  = 0;
    $_SESSION['admin_login_window_start'] = $now;
}

/* Reset window if expired */
if (($now - $_SESSION['admin_login_window_start']) > $window) {
    $_SESSION['admin_login_attempts']     = 0;
    $_SESSION['admin_login_window_start'] = $now;
}

if ($_SESSION['admin_login_attempts'] >= $maxTries) {
    $wait = ceil(($window - ($now - $_SESSION['admin_login_window_start'])) / 60);
    redirect('login.php?error=ratelimit&wait=' . $wait);
}

/* ── Helper ──────────────────────────────────────────── */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/* ── CSRF check ──────────────────────────────────────── */
$submittedCsrf = trim($_POST['csrf_token'] ?? '');
$sessionCsrf   = $_SESSION['admin_csrf']   ?? '';

if (
    empty($submittedCsrf) ||
    empty($sessionCsrf)   ||
    !hash_equals($sessionCsrf, $submittedCsrf)
) {
    redirect('login.php?error=csrf');
}

/* Rotate CSRF token after use */
$_SESSION['admin_csrf'] = bin2hex(random_bytes(32));

/* ── Input ───────────────────────────────────────────── */
$email    = trim(strtolower($_POST['email']    ?? ''));
$password = trim($_POST['password'] ?? '');
$remember = isset($_POST['remember']);

if (empty($email) || empty($password)) {
    redirect('login.php?error=empty');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect('login.php?error=invalid');
}

/* ── Database lookup ─────────────────────────────────── */
try {
    $db = getDB();

    $stmt = $db->prepare("
        SELECT id, first_name, last_name, email, password,
               role, is_banned, avatar
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    error_log('[GigGhana Admin Login] DB error: ' . $e->getMessage());
    redirect('login.php?error=invalid');
}

/* ── Validation sequence ─────────────────────────────── */

/* 1. User must exist */
if (!$user) {
    $_SESSION['admin_login_attempts']++;
    /* Artificial delay to slow brute force */
    usleep(random_int(200000, 500000));
    redirect('login.php?error=invalid');
}

/* 2. Must be role = admin */
if ($user['role'] !== 'admin') {
    $_SESSION['admin_login_attempts']++;
    usleep(random_int(200000, 500000));
    redirect('login.php?error=notadmin');
}

/* 3. Must not be banned */
if ((int)$user['is_banned'] === 1) {
    redirect('login.php?error=banned');
}

/* 4. Password must match */
if (!password_verify($password, $user['password'])) {
    $_SESSION['admin_login_attempts']++;
    usleep(random_int(200000, 500000));
    redirect('login.php?error=invalid');
}

/* ── SUCCESS ─────────────────────────────────────────── */

/* Prevent session fixation */
session_regenerate_id(true);

/* Reset rate limit counter */
$_SESSION['admin_login_attempts']     = 0;
$_SESSION['admin_login_window_start'] = $now;

/* Set all session keys the dashboard expects */
$_SESSION['admin_logged_in'] = true;
$_SESSION['user_id']         = (int)$user['id'];   /* ← dashboard reads this */
$_SESSION['user_role']       = 'admin';              /* ← requireRole() checks this */
$_SESSION['admin_id']        = (int)$user['id'];
$_SESSION['admin_name']      = trim($user['first_name'] . ' ' . $user['last_name']);
$_SESSION['admin_email']     = $user['email'];
$_SESSION['admin_avatar']    = $user['avatar'] ?? '';
$_SESSION['admin_login_at']  = date('Y-m-d H:i:s');

/* Update last_seen timestamp */
try {
    $db->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?")
       ->execute([$user['id']]);
} catch (Exception $e) {
    /* Non-fatal — continue */
}

/* ── Remember Me cookie (30 days) ────────────────────── */
if ($remember) {
    $token  = bin2hex(random_bytes(32));
    $expiry = time() + (30 * 24 * 60 * 60);

    /* Store hashed token in DB for validation on next visit */
    try {
        $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?")
           ->execute([hash('sha256', $token), $user['id']]);
    } catch (Exception $e) {
        /* Non-fatal */
    }

    /* Set the cookie */
    setcookie(
        'gg_admin_remember',
        $user['id'] . ':' . $token,
        [
            'expires'  => $expiry,
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/* ── Redirect to dashboard ───────────────────────────── */
$redirect = $_SESSION['admin_intended_url'] ?? 'dashboard.php';
unset($_SESSION['admin_intended_url']);
redirect($redirect);