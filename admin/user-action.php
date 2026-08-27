<?php
/* ============================================================
   admin/user-action.php
   Handles: ban, unban, verify (Ghana card), reset_password
   POST: csrf_token, user_id, action
   ============================================================ */
if (basename(__FILE__) === 'user-action.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
if (!adminCheckCSRF() || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=users&msg=Invalid+request&t=error'); exit; }
$uid    = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';
$db     = adminGetDB();
$admin  = currentAdmin();
if (!$uid) { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=users&msg=Invalid+user&t=error'); exit; }
switch ($action) {
    case 'ban':
        $db->prepare("UPDATE users SET is_banned=1 WHERE id=? AND role!='admin'")->execute([$uid]);
        adminWriteLog($admin['id'], 'ban_user', 'user', $uid);
        $msg = 'User banned successfully.'; break;
    case 'unban':
        $db->prepare("UPDATE users SET is_banned=0 WHERE id=?")->execute([$uid]);
        adminWriteLog($admin['id'], 'unban_user', 'user', $uid);
        $msg = 'User unbanned.'; break;
    case 'verify':
        $db->prepare("UPDATE users SET ghana_card_verified=1, email_verified=1 WHERE id=?")->execute([$uid]);
        adminWriteLog($admin['id'], 'verify_user', 'user', $uid);
        $msg = 'User verified successfully.'; break;
    case 'reset_password':
        $token = bin2hex(random_bytes(32));
        $db->prepare("UPDATE users SET password_reset_token=?, password_reset_expires=DATE_ADD(NOW(), INTERVAL 2 HOUR) WHERE id=?")->execute([$token, $uid]);
        adminWriteLog($admin['id'], 'reset_password', 'user', $uid);
        $msg = 'Password reset token generated. (Email logic pending)'; break;
    default:
        $msg = 'Unknown action.'; $t = 'error';
}
header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=users&msg='.urlencode($msg ?? 'Done').'&t='.($t??'success'));
exit;
endif;


/* ============================================================
   admin/job-action.php
   Handles: flag, unflag, delete
   ============================================================ */
if (basename(__FILE__) === 'job-action.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
if (!adminCheckCSRF() || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=jobs&msg=Invalid&t=error'); exit; }
$jid    = (int)($_POST['job_id'] ?? 0);
$action = $_POST['action'] ?? '';
$db     = adminGetDB();
$admin  = currentAdmin();
switch ($action) {
    case 'flag':
        $db->prepare("UPDATE jobs SET is_flagged=1, flag_reason='Admin flagged' WHERE id=?")->execute([$jid]);
        adminWriteLog($admin['id'], 'flag_job', 'job', $jid);
        $msg = 'Job flagged.'; break;
    case 'unflag':
        $db->prepare("UPDATE jobs SET is_flagged=0, flag_reason=NULL WHERE id=?")->execute([$jid]);
        adminWriteLog($admin['id'], 'unflag_job', 'job', $jid);
        $msg = 'Job flag removed.'; break;
    case 'delete':
        $db->prepare("UPDATE jobs SET status='cancelled' WHERE id=?")->execute([$jid]);
        adminWriteLog($admin['id'], 'cancel_job', 'job', $jid);
        $msg = 'Job cancelled.'; break;
}
header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=jobs&msg='.urlencode($msg ?? 'Done').'&t=success');
exit;
endif;


/* ============================================================
   admin/dispute-action.php
   Handles: resolve_client, resolve_provider, reviewing
   ============================================================ */
if (basename(__FILE__) === 'dispute-action.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
if (!adminCheckCSRF() || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=disputes&msg=Invalid&t=error'); exit; }
$did    = (int)($_POST['dispute_id'] ?? 0);
$action = $_POST['action'] ?? '';
$db     = adminGetDB();
$admin  = currentAdmin();
$statusMap = [
    'resolve_client'   => 'resolved_client',
    'resolve_provider' => 'resolved_provider',
    'reviewing'        => 'under_review',
    'close'            => 'closed',
];
if (!isset($statusMap[$action])) { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=disputes&msg=Invalid+action&t=error'); exit; }
$newStatus = $statusMap[$action];
$db->prepare("UPDATE disputes SET status=?, resolved_by=?, resolved_at=NOW() WHERE id=?")->execute([$newStatus, $admin['id'], $did]);
adminWriteLog($admin['id'], 'dispute_'.$action, 'dispute', $did);
$msg = match($action){
    'resolve_client'   => 'Dispute resolved in favour of client.',
    'resolve_provider' => 'Dispute resolved in favour of provider.',
    'reviewing'        => 'Dispute marked as under review.',
    default            => 'Dispute updated.',
};
header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=disputes&msg='.urlencode($msg).'&t=success');
exit;
endif;


/* ============================================================
   admin/verify-action.php
   Handles: approve, reject provider verification
   ============================================================ */
if (basename(__FILE__) === 'verify-action.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
if (!adminCheckCSRF() || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=analytics&msg=Invalid&t=error'); exit; }
$vid    = (int)($_POST['ver_id'] ?? 0);
$action = $_POST['action'] ?? '';
$db     = adminGetDB();
$admin  = currentAdmin();
$newStatus = $action === 'approve' ? 'approved' : 'rejected';
$db->prepare("UPDATE provider_verifications SET status=? WHERE id=?")->execute([$newStatus, $vid]);
if ($action === 'approve') {
    // Also flip ghana_card_verified on user
    $row = $db->prepare("SELECT p.user_id FROM provider_verifications pv JOIN providers p ON p.id=pv.provider_id WHERE pv.id=? LIMIT 1");
    $row->execute([$vid]);
    $r = $row->fetch();
    if ($r) $db->prepare("UPDATE users SET ghana_card_verified=1 WHERE id=?")->execute([$r['user_id']]);
}
adminWriteLog($admin['id'], 'verification_'.$action, 'verification', $vid);
$msg = $action === 'approve' ? 'Verification approved.' : 'Verification rejected.';
header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=analytics&msg='.urlencode($msg).'&t=success');
exit;
endif;


/* ============================================================
   admin/withdrawal-action.php
   Handles: approve, reject withdrawal requests
   ============================================================ */
if (basename(__FILE__) === 'withdrawal-action.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
if (!adminCheckCSRF() || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=transactions&msg=Invalid&t=error'); exit; }
$wid    = (int)($_POST['withdrawal_id'] ?? 0);
$action = $_POST['action'] ?? '';
$db     = adminGetDB();
$admin  = currentAdmin();
if ($action === 'approve') {
    $wd = $db->prepare("SELECT * FROM withdrawals WHERE id=? AND status='pending' LIMIT 1");
    $wd->execute([$wid]);
    $withdrawal = $wd->fetch();
    if ($withdrawal) {
        $db->prepare("UPDATE withdrawals SET status='processing', processed_at=NOW() WHERE id=?")->execute([$wid]);
        // Deduct from wallet
        $db->prepare("UPDATE wallets SET available_balance=available_balance-? WHERE user_id=?")->execute([$withdrawal['amount'], $withdrawal['user_id']]);
        adminWriteLog($admin['id'], 'approve_withdrawal', 'withdrawal', $wid, 'GHS '.$withdrawal['amount']);
        $msg = 'Withdrawal approved and marked as processing.';
    } else { $msg = 'Withdrawal not found or already processed.'; $t = 'error'; }
} else {
    $wd2 = $db->prepare("SELECT user_id, amount FROM withdrawals WHERE id=? LIMIT 1");
    $wd2->execute([$wid]); $wr = $wd2->fetch();
    $db->prepare("UPDATE withdrawals SET status='failed' WHERE id=?")->execute([$wid]);
    // Refund wallet
    if ($wr) $db->prepare("UPDATE wallets SET available_balance=available_balance+? WHERE user_id=?")->execute([$wr['amount'], $wr['user_id']]);
    adminWriteLog($admin['id'], 'reject_withdrawal', 'withdrawal', $wid);
    $msg = 'Withdrawal rejected and funds returned to wallet.';
}
header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=transactions&msg='.urlencode($msg ?? 'Done').'&t='.($t??'success'));
exit;
endif;


/* ============================================================
   admin/save-settings.php
   Handles: pricing, rules platform settings
   ============================================================ */
if (basename(__FILE__) === 'save-settings.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
if (!adminCheckCSRF() || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg=Invalid+request&t=error'); exit; }
$admin   = currentAdmin();
$section = $_POST['section'] ?? '';
switch ($section) {
    case 'pricing':
        $fields = ['commission_rate','verified_price','premium_price','free_proposals_limit'];
        foreach ($fields as $f) {
            $v = trim($_POST[$f] ?? '');
            if ($v !== '') adminSaveSetting($f, $v, $admin['id']);
        }
        adminWriteLog($admin['id'], 'save_settings_pricing', 'settings');
        $msg = 'Pricing settings saved.'; break;
    case 'rules':
        adminSaveSetting('require_ghcard', isset($_POST['require_ghcard']) ? '1' : '0', $admin['id']);
        adminSaveSetting('moderate_jobs',  isset($_POST['moderate_jobs'])  ? '1' : '0', $admin['id']);
        adminSaveSetting('maintenance_mode', isset($_POST['maintenance_mode']) ? '1' : '0', $admin['id']);
        $gw = in_array($_POST['payment_gateway']??'',['paystack','hubtel','manual']) ? $_POST['payment_gateway'] : 'paystack';
        adminSaveSetting('payment_gateway', $gw, $admin['id']);
        adminWriteLog($admin['id'], 'save_settings_rules', 'settings');
        $msg = 'Platform rules saved.'; break;
    default:
        $msg = 'Nothing to save.'; $t = 'warning';
}
header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg='.urlencode($msg ?? 'Done').'&t='.($t??'success'));
exit;
endif;


/* ============================================================
   admin/change-password.php
   Allows admin to change their own password
   ============================================================ */
if (basename(__FILE__) === 'change-password.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
if (!adminCheckCSRF() || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg=Invalid&t=error'); exit; }
$admin  = currentAdmin();
$db     = adminGetDB();
$cur    = $_POST['current_password'] ?? '';
$new    = $_POST['new_password']     ?? '';
$conf   = $_POST['confirm_password'] ?? '';
$stmt = $db->prepare("SELECT password_hash FROM users WHERE id=? AND role='admin' LIMIT 1");
$stmt->execute([$admin['id']]);
$row = $stmt->fetch();
if (!$row || !password_verify($cur, $row['password_hash'])) {
    header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg=Current+password+is+wrong&t=error'); exit;
}
if (strlen($new) < 8) {
    header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg=Password+must+be+at+least+8+characters&t=error'); exit;
}
if ($new !== $conf) {
    header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg=Passwords+do+not+match&t=error'); exit;
}
$hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
$db->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, $admin['id']]);
adminWriteLog($admin['id'], 'change_password', 'admin', $admin['id']);
/* Force re-login for security */
adminLogout(false);
header('Location: ' . ADMIN_BASE . '/admin/login.php?reason=password_changed');
exit;
endif;


/* ============================================================
   admin/dangerous.php
   Dangerous maintenance actions
   ============================================================ */
if (basename(__FILE__) === 'dangerous.php'):
require_once __DIR__ . '/../config/admin_auth.php';
adminOnly();
$csrfGet = $_GET['csrf_token'] ?? $_GET['csrf'] ?? '';
if (!hash_equals($_SESSION['admin_csrf'] ?? '', $csrfGet)) {
    header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg=CSRF+invalid&t=error'); exit;
}
$action = $_GET['action'] ?? '';
$db     = adminGetDB();
$admin  = currentAdmin();
switch ($action) {
    case 'clear_notifs':
        $db->exec("DELETE FROM notifications WHERE is_read=1");
        adminWriteLog($admin['id'], 'clear_read_notifications', 'system');
        $msg = 'Read notifications cleared.'; break;
    case 'clear_attempts':
        $db->exec("DELETE FROM admin_login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
        adminWriteLog($admin['id'], 'clear_login_attempts', 'system');
        $msg = 'Old login attempt logs cleared.'; break;
    default:
        $msg = 'Unknown action.'; $t = 'error';
}
header('Location: ' . ADMIN_BASE . '/admin/dashboard.php?panel=settings&msg='.urlencode($msg ?? 'Done').'&t='.($t??'success'));
exit;
endif;