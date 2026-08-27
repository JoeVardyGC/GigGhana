<?php
/**
 * GigGhana — admin/create_admin.php
 * 
 * STEP 1: Open this file and set YOUR password below
 * STEP 2: Visit http://localhost/gigghana/admin/create_admin.php
 * STEP 3: Delete this file immediately after!
 */

// ╔══════════════════════════════════════════╗
// ║  SET YOUR OWN EMAIL & PASSWORD HERE      ║
// ╚══════════════════════════════════════════╝
$MY_EMAIL    = 'admin@gigghana.com';   // change to your email
$MY_PASSWORD = 'gigghana123';          // change to your password
$MY_FNAME    = 'GigGhana';
$MY_LNAME    = 'Admin';

// ╔══════════════════════════════════════════╗
// ║  DATABASE CONFIG                         ║
// ╚══════════════════════════════════════════╝
$DB_HOST = 'localhost';
$DB_NAME = 'gigghana';
$DB_USER = 'root';
$DB_PASS = '';   // ← your MySQL password (blank if none)

// ═══════════════════════════════════════════
//  DO NOT EDIT BELOW THIS LINE
// ═══════════════════════════════════════════

$done   = [];
$errors = [];

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER, $DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $done[] = '✅ Connected to database: ' . $DB_NAME;

    // Generate hash
    $hash = password_hash($MY_PASSWORD, PASSWORD_BCRYPT, ['cost' => 12]);
    $done[] = '✅ Password hash generated';

    // Does user with this email already exist?
    $check = $pdo->prepare("SELECT id, role FROM users WHERE email = ? LIMIT 1");
    $check->execute([$MY_EMAIL]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        // Update existing user → make them admin
        $pdo->prepare("
            UPDATE users SET
                role          = 'admin',
                password_hash = ?,
                first_name    = ?,
                last_name     = ?,
                is_active     = 1,
                is_banned     = 0,
                email_verified= 1,
                updated_at    = NOW()
            WHERE email = ?
        ")->execute([$hash, $MY_FNAME, $MY_LNAME, $MY_EMAIL]);
        $done[] = '✅ Existing user (ID: ' . $existing['id'] . ') updated → role set to ADMIN';
        $adminId = $existing['id'];
    } else {
        // Create brand new admin user
        $uuid = bin2hex(random_bytes(16));
        $uuid = substr($uuid,0,8).'-'.substr($uuid,8,4).'-'.substr($uuid,12,4).'-'.substr($uuid,16,4).'-'.substr($uuid,20,12);
        $pdo->prepare("
            INSERT INTO users
                (uuid, first_name, last_name, email, phone, password_hash,
                 role, email_verified, is_active, is_banned, country, created_at, updated_at)
            VALUES
                (?, ?, ?, ?, '', ?,
                 'admin', 1, 1, 0, 'Ghana', NOW(), NOW())
        ")->execute([$uuid, $MY_FNAME, $MY_LNAME, $MY_EMAIL, $hash]);
        $adminId = (int)$pdo->lastInsertId();
        $done[] = '✅ New admin user created (ID: ' . $adminId . ')';

        // Create wallet
        try {
            $pdo->prepare("INSERT IGNORE INTO wallets (user_id, currency) VALUES (?, 'GHS')")->execute([$adminId]);
        } catch (Exception $e) {}
    }

    // Create required tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_sessions (
        id int AUTO_INCREMENT PRIMARY KEY,
        admin_id int NOT NULL,
        session_token varchar(200) NOT NULL UNIQUE,
        ip_address varchar(45),
        created_at timestamp DEFAULT current_timestamp(),
        expires_at timestamp NOT NULL,
        is_active tinyint(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done[] = '✅ Table admin_sessions OK';

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_logs (
        id int AUTO_INCREMENT PRIMARY KEY,
        admin_id int NOT NULL,
        action varchar(100) NOT NULL,
        target_type varchar(50) DEFAULT NULL,
        target_id int DEFAULT NULL,
        notes text DEFAULT NULL,
        ip_address varchar(45) DEFAULT NULL,
        created_at timestamp DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done[] = '✅ Table admin_logs OK';

    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_login_attempts (
        id int AUTO_INCREMENT PRIMARY KEY,
        email varchar(191) NOT NULL,
        ip_address varchar(45) NOT NULL,
        attempted_at timestamp DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done[] = '✅ Table admin_login_attempts OK';

    $pdo->exec("CREATE TABLE IF NOT EXISTS platform_settings (
        id int AUTO_INCREMENT PRIMARY KEY,
        setting_key varchar(100) NOT NULL UNIQUE,
        setting_val text DEFAULT NULL,
        updated_by int DEFAULT NULL,
        updated_at timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $done[] = '✅ Table platform_settings OK';

    // Add columns to users if missing
    try { $pdo->exec("ALTER TABLE users ADD COLUMN ban_reason text DEFAULT NULL"); $done[] = '✅ Added users.ban_reason'; } catch(Exception $e) { $done[] = '  ↳ users.ban_reason already exists'; }
    try { $pdo->exec("ALTER TABLE users ADD COLUMN admin_notes text DEFAULT NULL"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE jobs ADD COLUMN is_flagged tinyint(1) DEFAULT 0"); $done[] = '✅ Added jobs.is_flagged'; } catch(Exception $e) { $done[] = '  ↳ jobs.is_flagged already exists'; }
    try { $pdo->exec("ALTER TABLE jobs ADD COLUMN flag_reason varchar(255) DEFAULT NULL"); } catch(Exception $e) {}

    // Seed settings
    $settings = [
        'commission_rate'=>'12','verified_price'=>'49','premium_price'=>'99',
        'free_proposals_limit'=>'3','payment_gateway'=>'paystack',
        'maintenance_mode'=>'0','moderate_jobs'=>'0','require_ghcard'=>'1'
    ];
    foreach ($settings as $k => $v) {
        try { $pdo->prepare("INSERT IGNORE INTO platform_settings (setting_key,setting_val) VALUES (?,?)")->execute([$k,$v]); } catch(Exception $e){}
    }
    $done[] = '✅ Platform settings seeded';

    // Clear any rate-limit blocks
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    try { $pdo->prepare("DELETE FROM admin_login_attempts WHERE email=? OR ip_address=?")->execute([$MY_EMAIL, $ip]); } catch(Exception $e){}
    $done[] = '✅ Rate limit cleared for your email and IP';

    // Clear old sessions
    try { $pdo->prepare("UPDATE admin_sessions SET is_active=0 WHERE admin_id=?")->execute([$adminId]); } catch(Exception $e){}
    $done[] = '✅ Old sessions cleared';

    // ── VERIFY: test that password_verify works right now ──
    $verify = $pdo->prepare("SELECT password_hash, role, is_active, is_banned FROM users WHERE email=? LIMIT 1");
    $verify->execute([$MY_EMAIL]);
    $vRow = $verify->fetch(PDO::FETCH_ASSOC);

    if (!$vRow) {
        $errors[] = '❌ FATAL: User was not saved to database!';
    } elseif ($vRow['role'] !== 'admin') {
        $errors[] = '❌ FATAL: Role is "' . $vRow['role'] . '" not "admin"!';
    } elseif (!password_verify($MY_PASSWORD, $vRow['password_hash'])) {
        $errors[] = '❌ FATAL: password_verify FAILED — hash mismatch!';
    } elseif (!$vRow['is_active']) {
        $errors[] = '❌ FATAL: User is_active = 0!';
    } else {
        $done[] = '✅✅✅ LOGIN TEST PASSED — email, password, role all verified!';
        $allGood = true;
    }

} catch (PDOException $e) {
    $errors[] = '❌ DATABASE ERROR: ' . $e->getMessage();
    $errors[] = 'Check that DB_HOST, DB_NAME, DB_USER, DB_PASS are correct at the top of this file.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Account Creator — GigGhana</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#0C0E14;color:#F2F4F8;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;}
.card{width:100%;max-width:580px;background:#13161E;border:1px solid rgba(255,255,255,0.08);border-radius:18px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,0.5);}
.card-top{padding:26px 28px 20px;background:linear-gradient(135deg,rgba(255,77,106,0.08),transparent);}
.logo{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.mark{width:38px;height:38px;background:linear-gradient(135deg,#FF4D6A,#C0384F);border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;}
.logo span{font-size:20px;font-weight:800;font-family:'Plus Jakarta Sans',sans-serif;}.logo em{color:#FF4D6A;font-style:normal;}
h1{font-size:17px;font-weight:700;margin-bottom:3px;}
.sub{font-size:12px;color:#9BA8BF;}
.steps{padding:18px 28px;display:flex;flex-direction:column;gap:6px;}
.step{display:flex;align-items:flex-start;gap:10px;font-size:12.5px;padding:9px 13px;border-radius:8px;font-family:'Courier New',monospace;}
.step.ok  {background:rgba(31,217,160,0.06);color:#a8f0d8;}
.step.fail{background:rgba(255,77,106,0.08);color:#FF8A9A;}
.step.info{background:rgba(255,255,255,0.03);color:#9BA8BF;}
.step-ico{flex-shrink:0;}
.creds{margin:4px 28px 20px;background:rgba(0,212,200,0.07);border:1px solid rgba(0,212,200,0.22);border-radius:12px;padding:18px 20px;}
.creds h3{font-size:12.5px;font-weight:700;color:#00D4C8;margin-bottom:14px;display:flex;align-items:center;gap:7px;}
.cred-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.06);font-size:13px;}
.cred-row:last-child{border-bottom:none;}
.cred-lbl{color:#9BA8BF;font-size:11.5px;}
.cred-val{font-family:'Courier New',monospace;background:rgba(0,0,0,0.3);padding:3px 9px;border-radius:5px;font-size:12px;color:#F2F4F8;}
.cred-val.pass{color:#F7B731;}
.errors{margin:0 28px 20px;background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.25);border-radius:10px;padding:14px 16px;}
.errors h3{color:#FF4D6A;font-size:12.5px;font-weight:700;margin-bottom:10px;}
.err-line{font-size:12px;color:#FF8A9A;padding:4px 0;font-family:'Courier New',monospace;}
.btns{padding:0 28px 28px;display:flex;flex-direction:column;gap:10px;}
.btn{display:block;width:100%;padding:13px;border-radius:10px;border:none;cursor:pointer;font-size:14px;font-weight:700;text-align:center;text-decoration:none;transition:all .2s;}
.btn-go{background:linear-gradient(135deg,#FF4D6A,#C0384F);color:#fff;box-shadow:0 4px 18px rgba(255,77,106,0.3);}
.btn-go:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(255,77,106,0.4);}
.btn-del{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#9BA8BF;}
.btn-del:hover{background:rgba(255,255,255,0.07);color:#F2F4F8;}
.warn{padding:12px 28px 20px;font-size:11.5px;color:#F7B731;text-align:center;}
</style>
</head>
<body>
<div class="card">
  <div class="card-top">
    <div class="logo">
      <div class="mark">G</div>
      <span>Gig<em>Ghana</em> Admin Setup</span>
    </div>
    <h1>🛠 Admin Account Creator</h1>
    <p class="sub">Creates or resets your admin account in the database</p>
  </div>

  <div class="steps">
    <?php foreach ($done as $d): ?>
    <div class="step <?= str_starts_with($d,'✅') ? 'ok' : 'info' ?>">
      <span><?= htmlspecialchars($d) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (!empty($errors)): ?>
  <div class="errors">
    <h3>❌ Errors</h3>
    <?php foreach ($errors as $e): ?>
    <div class="err-line"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <?php if (!empty($allGood)): ?>
  <div class="creds">
    <h3>🔑 Your Admin Login Credentials</h3>
    <div class="cred-row">
      <span class="cred-lbl">Login URL</span>
      <span class="cred-val">/admin/login.php</span>
    </div>
    <div class="cred-row">
      <span class="cred-lbl">Email</span>
      <span class="cred-val"><?= htmlspecialchars($MY_EMAIL) ?></span>
    </div>
    <div class="cred-row">
      <span class="cred-lbl">Password</span>
      <span class="cred-val pass"><?= htmlspecialchars($MY_PASSWORD) ?></span>
    </div>
    <div class="cred-row">
      <span class="cred-lbl">Role</span>
      <span class="cred-val" style="color:#1FD9A0;">admin ✓</span>
    </div>
  </div>
  <div class="btns">
    <a href="/admin/login.php" class="btn btn-go">🛡 Go to Admin Login →</a>
    <a href="?delete=1" class="btn btn-del" onclick="return confirm('Delete this file now? Make sure you saved your password!')">🗑 Delete This File</a>
  </div>
  <div class="warn">⚠️ DELETE this file after logging in. It shows your password in plain text!</div>
  <?php else: ?>
  <div class="btns">
    <a href="?" class="btn btn-del">🔄 Try Again</a>
  </div>
  <?php endif; ?>
</div>

<?php
if (isset($_GET['delete']) && !empty($allGood)) {
    if (@unlink(__FILE__)) {
        echo '<script>alert("File deleted! Redirecting to login..."); window.location="/admin/login.php";</script>';
    } else {
        echo '<script>alert("Could not auto-delete. Please manually delete admin/create_admin.php from your server!");</script>';
    }
}
?>
</body>
</html>