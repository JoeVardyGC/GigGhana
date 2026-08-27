<?php
/**
 * GigGhana — admin/debug_login.php
 * TEMPORARY DEBUG FILE — delete after fixing!
 * Visit: http://localhost/gigghana/admin/debug_login.php
 */

// Start session manually
session_name('GG_ADMIN');
session_start();

$info = [];
$errors = [];

// ── 1. Try DB connection ──
try {
    // Try loading the app's own database config
    $dbFile = __DIR__ . '/../config/database.php';
    if (file_exists($dbFile)) {
        require_once $dbFile;
        $info[] = ['✅', 'config/database.php loaded'];
        $db = getDB();
        $info[] = ['✅', 'getDB() worked — connected to database'];
    } else {
        $errors[] = 'config/database.php NOT FOUND at: ' . $dbFile;
    }
} catch (Exception $e) {
    $errors[] = 'DB Error: ' . $e->getMessage();
}

// ── 2. Check users table and admin accounts ──
if (isset($db)) {
    try {
        $all = $db->query("SELECT id, first_name, last_name, email, role, is_active, is_banned, LEFT(password_hash,20) as hash_preview FROM users ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
        $info[] = ['📋', 'All users in database:'];
        foreach ($all as $u) {
            $info[] = ['  →', "ID:{$u['id']} | {$u['email']} | role:{$u['role']} | active:{$u['is_active']} | banned:{$u['is_banned']} | hash:{$u['hash_preview']}..."];
        }
        
        // Check specifically for admin role
        $admins = array_filter($all, fn($u) => $u['role'] === 'admin');
        if (empty($admins)) {
            $errors[] = 'NO ADMIN USERS FOUND — no user has role="admin" in the database!';
        } else {
            $info[] = ['✅', count($admins) . ' admin account(s) found'];
        }
    } catch (Exception $e) {
        $errors[] = 'Cannot query users: ' . $e->getMessage();
    }

    // ── 3. Check required tables ──
    $tables = ['admin_sessions', 'admin_login_attempts', 'admin_logs', 'platform_settings'];
    foreach ($tables as $tbl) {
        try {
            $db->query("SELECT 1 FROM `$tbl` LIMIT 1");
            $info[] = ['✅', "Table $tbl exists"];
        } catch (Exception $e) {
            $errors[] = "Table $tbl MISSING — run admin/setup.php first!";
        }
    }
}

// ── 4. Check rate limiting (might be locked out) ──
if (isset($db)) {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare("SELECT COUNT(*) FROM admin_login_attempts WHERE ip_address=? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$ip]);
        $count = (int)$stmt->fetchColumn();
        if ($count >= 5) {
            $errors[] = "RATE LIMITED! Your IP ($ip) has $count failed attempts in last 15 mins. Clear the table or wait.";
        } else {
            $info[] = ['✅', "Rate limit OK: $count/5 attempts from your IP ($ip)"];
        }
    } catch (Exception $e) {
        $info[] = ['⚠️', 'Could not check rate limit: ' . $e->getMessage()];
    }
}

// ── 5. Test password verify manually ──
if (isset($db)) {
    try {
        $testEmail = 'superadmin@gigghana.com';
        $testPass  = 'Admin@GigGhana2026';
        $stmt = $db->prepare("SELECT id, password_hash, role, is_active FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$testEmail]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $verified = password_verify($testPass, $row['password_hash']);
            $info[] = [$verified ? '✅' : '❌', "password_verify('$testPass', hash) = " . ($verified ? 'TRUE ✅' : 'FALSE ❌')];
            $info[] = ['  →', "role={$row['role']} | is_active={$row['is_active']}"];
            if (!$verified) {
                $errors[] = "Password hash mismatch! The stored hash does not match '$testPass'. Run setup.php again to reset it.";
            }
        } else {
            $info[] = ['⚠️', "No user found with email: $testEmail"];
        }
    } catch (Exception $e) {
        $errors[] = 'Password test error: ' . $e->getMessage();
    }
}

// ── 6. Session info ──
$info[] = ['🔐', 'Current session data: ' . json_encode($_SESSION)];
$info[] = ['🌐', 'Session name: ' . session_name()];
$info[] = ['🌐', 'PHP session.save_path: ' . (ini_get('session.save_path') ?: '(default)') ];
$info[] = ['🌐', 'PHP version: ' . PHP_VERSION];

// ── 7. Test the actual login function ──
$loginTestResult = null;
if (isset($db) && isset($_POST['test_login'])) {
    $testEmail2 = $_POST['email'] ?? '';
    $testPass2  = $_POST['password'] ?? '';
    $ip2        = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    
    require_once __DIR__ . '/../config/admin_auth.php';
    $result = adminAttemptLogin($testEmail2, $testPass2, $ip2);
    $loginTestResult = $result;
    
    if ($result['ok']) {
        // Redirect to dashboard
        header('Location: /admin/dashboard.php?login=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Debug Login — GigGhana</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#0C0E14;color:#F2F4F8;font-family:'Courier New',monospace;padding:20px;font-size:13px;}
h1{font-size:18px;font-weight:700;margin-bottom:4px;color:#FF4D6A;}
.sub{color:#9BA8BF;margin-bottom:20px;font-family:sans-serif;}
.section{background:#13161E;border:1px solid rgba(255,255,255,0.07);border-radius:10px;padding:16px;margin-bottom:16px;}
.section h2{font-size:13px;font-weight:700;margin-bottom:12px;color:#00D4C8;font-family:sans-serif;}
.line{padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.04);display:flex;gap:10px;align-items:flex-start;}
.line:last-child{border-bottom:none;}
.ico{flex-shrink:0;width:20px;}
.txt{flex:1;word-break:break-all;}
.error-box{background:rgba(255,77,106,0.1);border:1px solid rgba(255,77,106,0.3);border-radius:8px;padding:14px;margin-bottom:16px;}
.error-box h2{color:#FF4D6A;font-size:13px;font-weight:700;margin-bottom:10px;font-family:sans-serif;}
.error-line{color:#FF8A9A;padding:4px 0;border-bottom:1px solid rgba(255,77,106,0.1);}
.error-line:last-child{border-bottom:none;}
.form-box{background:#13161E;border:1px solid rgba(0,212,200,0.2);border-radius:10px;padding:20px;margin-bottom:16px;}
.form-box h2{color:#00D4C8;font-size:14px;font-weight:700;margin-bottom:14px;font-family:sans-serif;}
.field{margin-bottom:12px;}
.field label{display:block;font-size:11px;color:#9BA8BF;margin-bottom:4px;font-family:sans-serif;}
.field input{width:100%;background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.1);border-radius:7px;padding:9px 12px;color:#F2F4F8;font-family:monospace;font-size:13px;outline:none;}
.field input:focus{border-color:#00D4C8;}
.btn{background:linear-gradient(135deg,#00D4C8,#00A89F);color:#0C0E14;border:none;border-radius:8px;padding:10px 20px;font-weight:700;font-size:13px;cursor:pointer;font-family:sans-serif;}
.result-ok{background:rgba(31,217,160,0.1);border:1px solid rgba(31,217,160,0.3);border-radius:8px;padding:12px;color:#1FD9A0;margin-top:12px;}
.result-fail{background:rgba(255,77,106,0.1);border:1px solid rgba(255,77,106,0.3);border-radius:8px;padding:12px;color:#FF8A9A;margin-top:12px;}
.fix-btn{display:inline-block;background:rgba(247,183,49,0.15);border:1px solid rgba(247,183,49,0.3);color:#F7B731;border-radius:7px;padding:8px 16px;text-decoration:none;font-family:sans-serif;font-size:12px;font-weight:600;margin-right:8px;margin-top:8px;}
.fix-btn:hover{background:rgba(247,183,49,0.25);}
</style>
</head>
<body>
<h1>🔍 GigGhana Admin Debug</h1>
<p class="sub">This page diagnoses why admin login is failing. Delete it after you're done.</p>

<?php if (!empty($errors)): ?>
<div class="error-box">
  <h2>❌ Problems Found (<?= count($errors) ?>)</h2>
  <?php foreach ($errors as $e): ?>
  <div class="error-line">⚠️ <?= htmlspecialchars($e) ?></div>
  <?php endforeach; ?>
  <div style="margin-top:12px;">
    <a class="fix-btn" href="/admin/setup.php">🛠 Run Setup Wizard</a>
    <a class="fix-btn" href="?clear_attempts=1">🧹 Clear Rate Limit</a>
  </div>
</div>
<?php endif; ?>

<div class="section">
  <h2>📋 System Info</h2>
  <?php foreach ($info as $item): ?>
  <div class="line">
    <span class="ico"><?= $item[0] ?></span>
    <span class="txt"><?= htmlspecialchars($item[1]) ?></span>
  </div>
  <?php endforeach; ?>
</div>

<div class="form-box">
  <h2>🔑 Test Login Directly</h2>
  <p style="font-size:12px;color:#9BA8BF;margin-bottom:14px;font-family:sans-serif;">Enter your admin credentials below. If this works, it'll redirect you to the dashboard.</p>
  <form method="POST">
    <div class="field">
      <label>Email</label>
      <input type="email" name="email" value="superadmin@gigghana.com" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="Admin@GigGhana2026" required>
    </div>
    <button type="submit" name="test_login" class="btn">🚀 Test Login & Redirect</button>
  </form>
  <?php if ($loginTestResult !== null && !$loginTestResult['ok']): ?>
  <div class="result-fail">❌ Login failed: <?= htmlspecialchars($loginTestResult['msg']) ?></div>
  <?php endif; ?>
</div>

<?php
// Handle clear attempts
if (isset($_GET['clear_attempts']) && isset($db)) {
    try {
        $ip3 = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $db->prepare("DELETE FROM admin_login_attempts WHERE ip_address=?")->execute([$ip3]);
        echo '<div class="result-ok">✅ Rate limit cleared for your IP. Try logging in again.</div>';
    } catch (Exception $e) {
        echo '<div class="result-fail">Could not clear: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<div style="margin-top:20px;padding:14px;background:rgba(255,77,106,0.06);border:1px solid rgba(255,77,106,0.15);border-radius:8px;font-family:sans-serif;font-size:12px;color:#9BA8BF;">
  ⚠️ <strong style="color:#FF4D6A;">Security Notice:</strong> Delete this file after debugging! It exposes database info.
</div>
</body>
</html>