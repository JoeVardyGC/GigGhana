<?php
/**
 * GigGhana — config/admin_auth.php  (v4 — uses dedicated `admins` table)
 *
 * USAGE: require at top of every admin page, then call adminOnly()
 *
 *   require_once __DIR__ . '/../config/admin_auth.php';
 *   adminOnly();
 */

if (session_status() === PHP_SESSION_NONE) {
    session_name('GG_ADMIN');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// ── Auto-detect base URL ──
if (!defined('ADMIN_BASE')) {
    if (defined('APP_URL')) {
        define('ADMIN_BASE', APP_URL);
    } else {
        $proto  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $parts  = explode('/', trim($script, '/'));
        $base   = (count($parts) >= 2 && strtolower($parts[1] ?? '') === 'admin') ? '/' . $parts[0] : (count($parts) >= 1 && strtolower($parts[0]) !== 'admin' ? '/' . $parts[0] : '');
        define('ADMIN_BASE', $proto . '://' . $host . $base);
    }
}

// ── DB connection ──
if (!function_exists('adminGetDB')) {
    function adminGetDB(): PDO {
        static $pdo = null;
        if ($pdo !== null) return $pdo;
        if (function_exists('getDB')) { $pdo = getDB(); return $pdo; }
        $f = __DIR__ . '/database.php';
        if (file_exists($f)) { require_once $f; $pdo = getDB(); return $pdo; }
        // Fallback direct — update DB_PASS if needed
        $pdo = new PDO('mysql:host=localhost;dbname=gigghana;charset=utf8mb4', 'root', '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        return $pdo;
    }
}

// ── GATE ──
function adminOnly(): void {
    $login = ADMIN_BASE . '/admin/login.php';
    if (empty($_SESSION['gg_admin_ok']) || empty($_SESSION['gg_admin_id'])) {
        header('Location: ' . $login . '?reason=auth'); exit;
    }
    if (!empty($_SESSION['gg_admin_time']) && (time() - $_SESSION['gg_admin_time']) > 28800) {
        $_SESSION = []; session_destroy();
        header('Location: ' . $login . '?reason=expired'); exit;
    }
}

function adminCan(string $permission): bool {
    $role = $_SESSION['gg_admin_role'] ?? 'admin';
    if ($role === 'super_admin') return true;
    $perms = [
        'admin'     => ['view_users','ban_users','manage_jobs','manage_deals','view_chats','manage_verifications'],
        'moderator' => ['view_users','manage_jobs','manage_verifications'],
    ];
    return in_array($permission, $perms[$role] ?? []);
}

// ── LOGIN ──
function adminAttemptLogin(string $email, string $password, string $ip): array {
    try { $db = adminGetDB(); } catch (Exception $e) {
        return ['ok' => false, 'msg' => 'Database error: ' . $e->getMessage()];
    }

    // Rate limit
    try {
        $rs = $db->prepare("SELECT COUNT(*) FROM admin_login_attempts WHERE ip_address=? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $rs->execute([$ip]);
        if ((int)$rs->fetchColumn() >= 8) return ['ok' => false, 'msg' => 'Too many attempts. Wait 15 minutes.'];
    } catch (Exception $e) {}

    // Fetch admin from dedicated table
    try {
        $stmt = $db->prepare("SELECT * FROM admins WHERE email=? AND is_active=1 LIMIT 1");
        $stmt->execute([trim($email)]);
        $admin = $stmt->fetch();
    } catch (Exception $e) {
        return ['ok' => false, 'msg' => 'Query failed: ' . $e->getMessage()];
    }

    $fail = function() use ($db, $email, $ip) {
        try { $db->prepare("INSERT INTO admin_login_attempts (email,ip_address) VALUES (?,?)")->execute([$email,$ip]); } catch(Exception $e){}
    };

    if (!$admin) { $fail(); return ['ok' => false, 'msg' => 'No admin account found with that email.']; }
    if (!password_verify($password, $admin['password_hash'])) { $fail(); return ['ok' => false, 'msg' => 'Incorrect password.']; }

    // Create session
    $token = bin2hex(random_bytes(48));
    try {
        $db->prepare("DELETE FROM admin_sessions WHERE admin_id=?")->execute([$admin['id']]);
        $db->prepare("INSERT INTO admin_sessions (admin_id,session_token,ip_address,user_agent,expires_at) VALUES (?,?,?,?,DATE_ADD(NOW(),INTERVAL 8 HOUR))")
           ->execute([$admin['id'], $token, $ip, substr($_SERVER['HTTP_USER_AGENT']??'',0,255)]);
    } catch (Exception $e) { $token = 'fallback-' . bin2hex(random_bytes(16)); }

    try { $db->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")->execute([$admin['id']]); } catch(Exception $e){}

    adminWriteLog($admin['id'], 'admin_login', 'auth', null, 'Login OK', $ip);

    session_regenerate_id(true);
    $_SESSION['gg_admin_ok']     = true;
    $_SESSION['gg_admin_id']     = (int)$admin['id'];
    $_SESSION['gg_admin_name']   = $admin['name'];
    $_SESSION['gg_admin_email']  = $admin['email'];
    $_SESSION['gg_admin_role']   = $admin['role'];
    $_SESSION['gg_admin_avatar'] = $admin['profile_picture'] ?? '';
    $_SESSION['gg_admin_token']  = $token;
    $_SESSION['gg_admin_time']   = time();

    return ['ok' => true, 'admin' => $admin];
}

// ── LOGOUT ──
function adminLogout(bool $log = true): void {
    if ($log && !empty($_SESSION['gg_admin_id'])) {
        try {
            $db = adminGetDB();
            if (!empty($_SESSION['gg_admin_token'])) $db->prepare("UPDATE admin_sessions SET is_active=0 WHERE session_token=?")->execute([$_SESSION['gg_admin_token']]);
            adminWriteLog((int)$_SESSION['gg_admin_id'], 'admin_logout', 'auth');
        } catch(Exception $e) {}
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(),'',time()-42000,'/',$p['domain'],$p['secure'],$p['httponly']); }
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
}

// ── WRITE LOG ──
function adminWriteLog(int $adminId, string $action, string $targetType='', ?int $targetId=null, string $notes='', string $ip=''): void {
    try { adminGetDB()->prepare("INSERT INTO admin_logs (admin_id,action,target_type,target_id,notes,ip_address) VALUES (?,?,?,?,?,?)")->execute([$adminId,$action,$targetType,$targetId,$notes,$ip?:($_SERVER['REMOTE_ADDR']??'')]); } catch(Exception $e){}
}

// ── CURRENT ADMIN ──
function currentAdmin(): array {
    return [
        'id'     => (int)($_SESSION['gg_admin_id']    ?? 0),
        'name'   => $_SESSION['gg_admin_name']         ?? 'Admin',
        'email'  => $_SESSION['gg_admin_email']        ?? '',
        'role'   => $_SESSION['gg_admin_role']         ?? 'admin',
        'avatar' => $_SESSION['gg_admin_avatar']       ?? '',
    ];
}

// ── SETTINGS ──
function adminSetting(string $key, string $default=''): string {
    static $cache=[];
    if (array_key_exists($key,$cache)) return $cache[$key];
    try { $s=adminGetDB()->prepare("SELECT setting_val FROM platform_settings WHERE setting_key=? LIMIT 1"); $s->execute([$key]); $v=$s->fetchColumn(); $cache[$key]=$v!==false?(string)$v:$default; } catch(Exception $e){$cache[$key]=$default;}
    return $cache[$key];
}
function adminSaveSetting(string $key, string $value, int $adminId): void {
    try { adminGetDB()->prepare("INSERT INTO platform_settings (setting_key,setting_val,updated_by) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_val=?,updated_by=?")->execute([$key,$value,$adminId,$value,$adminId]); } catch(Exception $e){}
}

// ── CSRF ──
function adminCSRF(): string {
    if (empty($_SESSION['gg_admin_csrf'])) $_SESSION['gg_admin_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['gg_admin_csrf'];
}
function adminCheckCSRF(): bool {
    $t = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? $_GET['csrf'] ?? '';
    return !empty($t) && hash_equals($_SESSION['gg_admin_csrf']??'', $t);
}

// ── Helpers ──
function aSan(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function aAgo(string $ts): string {
    $d=time()-strtotime($ts);
    if($d<60) return 'just now';
    if($d<3600) return floor($d/60).'m ago';
    if($d<86400) return floor($d/3600).'h ago';
    if($d<604800) return floor($d/86400).'d ago';
    return date('M j, Y',strtotime($ts));
}
function aFmt(float $v): string { return '₵'.number_format($v,2); }