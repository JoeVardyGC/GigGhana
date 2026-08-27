<?php
define('APP_NAME', 'GigGhana');
$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? 'https://' : 'http://';
$host  = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
define('APP_URL', $proto . $host);
define('APP_VERSION', '1.0.0');
define('PLATFORM_FEE_PERCENT', 10);
define('MIN_WITHDRAWAL', 50);
define('CURRENCY', 'GHS');
define('CURRENCY_SYMBOL', '₵');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024);
define('SESSION_TIMEOUT', 3600 * 8);
define('OTP_EXPIRY_MINUTES', 10);

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
date_default_timezone_set('Africa/Accra');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_TIMEOUT,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verifyCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}
function requireLogin(string $redirect = ''): void {
    if (!isLoggedIn()) {
        $r = $redirect ?: APP_URL . '/auth/login.php';
        header("Location: $r"); exit;
    }
}
function requireRole(string $role): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== $role) {
        header("Location: " . APP_URL . "/index.php"); exit;
    }
}
function redirect(string $url): void { header("Location: $url"); exit; }
function generateUUID(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
}
function slugify(string $text): string {
    $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
    $text = strtolower(trim(preg_replace('/\s+/', '-', $text)));
    return $text . '-' . substr(md5(uniqid()), 0, 6);
}
function formatCurrency(float $amount): string {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}
function timeAgo(string $datetime): string {
    $diff = (new DateTime())->diff(new DateTime($datetime));
    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'just now';
}
function renderStars(float $rating): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($rating >= $i) $out .= '<span class="star filled">★</span>';
        elseif ($rating >= $i - 0.5) $out .= '<span class="star half">★</span>';
        else $out .= '<span class="star">☆</span>';
    }
    return $out;
}
function createNotification(int $userId, string $type, string $title, string $message, array $data = []): void {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id,type,title,message,data) VALUES (?,?,?,?,?)");
        $stmt->execute([$userId, $type, $title, $message, json_encode($data)]);
    } catch(Exception $e) { error_log($e->getMessage()); }
}
function getUserById(int $id): ?array {
    $stmt = getDB()->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
function getCategories(): array {
    $stmt = getDB()->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order,name");
    return $stmt->fetchAll();
}
function getUnreadNotifications(int $userId): array {
    $stmt = getDB()->prepare("SELECT * FROM notifications WHERE user_id=? AND is_read=0 ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}