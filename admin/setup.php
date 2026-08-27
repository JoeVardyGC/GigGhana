<?php
/**
 * GigGhana — admin/setup.php
 * Run ONCE: http://localhost/gigghana/admin/setup.php
 * Sets up all tables + creates your super admin account.
 * DELETE THIS FILE after first successful login.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'gigghana';
$DB_USER = 'root';
$DB_PASS = '';          // ← your MySQL password
$DB_PORT = 3306;

// ╔═══════════════════════════════╗
// ║  SET YOUR ADMIN CREDENTIALS  ║
// ╚═══════════════════════════════╝
$ADMIN_NAME     = 'Super Admin';
$ADMIN_EMAIL    = 'superadmin@gigghana.com';
$ADMIN_PASSWORD = 'GigAdmin2026!';   // ← CHANGE THIS

$steps = []; $errors = []; $allGood = false;

try {
    $pdo = new PDO("mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $steps[] = ['✅','Connected to database: '.$DB_NAME];

    // Create all tables
    $tables = [
        "CREATE TABLE IF NOT EXISTS `admins` (`id` int NOT NULL AUTO_INCREMENT, `uuid` varchar(36) NOT NULL DEFAULT '', `name` varchar(150) NOT NULL, `email` varchar(191) NOT NULL, `password_hash` varchar(255) NOT NULL, `role` enum('super_admin','admin','moderator') DEFAULT 'admin', `profile_picture` varchar(255) DEFAULT NULL, `is_active` tinyint(1) DEFAULT 1, `last_login` timestamp NULL DEFAULT NULL, `created_by` int DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY (`id`), UNIQUE KEY `email` (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `admin_sessions` (`id` int NOT NULL AUTO_INCREMENT, `admin_id` int NOT NULL, `session_token` varchar(200) NOT NULL, `ip_address` varchar(45) DEFAULT NULL, `user_agent` varchar(255) DEFAULT NULL, `is_active` tinyint(1) DEFAULT 1, `expires_at` timestamp NOT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`), UNIQUE KEY `session_token` (`session_token`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `admin_login_attempts` (`id` int NOT NULL AUTO_INCREMENT, `email` varchar(191) NOT NULL, `ip_address` varchar(45) NOT NULL, `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `admin_logs` (`id` int NOT NULL AUTO_INCREMENT, `admin_id` int NOT NULL, `action` varchar(100) NOT NULL, `target_type` varchar(60) DEFAULT NULL, `target_id` int DEFAULT NULL, `notes` text DEFAULT NULL, `ip_address` varchar(45) DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `platform_settings` (`id` int NOT NULL AUTO_INCREMENT, `setting_key` varchar(100) NOT NULL, `setting_val` text DEFAULT NULL, `updated_by` int DEFAULT NULL, `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY (`id`), UNIQUE KEY `setting_key` (`setting_key`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `badges` (`id` int NOT NULL AUTO_INCREMENT, `name` varchar(100) NOT NULL, `slug` varchar(100) NOT NULL, `icon` varchar(10) DEFAULT '🏅', `color` varchar(30) DEFAULT '#00D4C8', `description` text DEFAULT NULL, `is_active` tinyint(1) DEFAULT 1, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`), UNIQUE KEY `slug` (`slug`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `user_badges` (`id` int NOT NULL AUTO_INCREMENT, `user_id` int NOT NULL, `badge_id` int NOT NULL, `awarded_by` int DEFAULT NULL, `awarded_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`), UNIQUE KEY `unique_user_badge` (`user_id`,`badge_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `deals` (`id` int NOT NULL AUTO_INCREMENT, `job_id` int NOT NULL, `client_id` int NOT NULL, `provider_id` int NOT NULL, `proposal_id` int DEFAULT NULL, `amount` decimal(14,2) DEFAULT 0.00, `client_confirmed` tinyint(1) DEFAULT 0, `provider_confirmed` tinyint(1) DEFAULT 0, `status` enum('active','client_done','provider_done','completed','disputed','cancelled') DEFAULT 'active', `client_confirmed_at` timestamp NULL DEFAULT NULL, `provider_confirmed_at` timestamp NULL DEFAULT NULL, `completed_at` timestamp NULL DEFAULT NULL, `notes` text DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `fraud_flags` (`id` int NOT NULL AUTO_INCREMENT, `user_id` int NOT NULL, `flag_type` enum('multiple_accounts','no_completion','excessive_disputes','suspicious_messages','other') NOT NULL, `description` text DEFAULT NULL, `flagged_by` int DEFAULT NULL, `is_resolved` tinyint(1) DEFAULT 0, `resolved_by` int DEFAULT NULL, `resolved_at` timestamp NULL DEFAULT NULL, `created_at` timestamp NOT NULL DEFAULT current_timestamp(), PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS `verifications` (`id` int NOT NULL AUTO_INCREMENT, `user_id` int NOT NULL, `type` enum('ghana_card','premium','identity','business') NOT NULL, `document_url` varchar(255) DEFAULT NULL, `status` enum('pending','approved','rejected') DEFAULT 'pending', `reviewed_by` int DEFAULT NULL, `notes` text DEFAULT NULL, `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(), `reviewed_at` timestamp NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];
    foreach ($tables as $sql) { $pdo->exec($sql); }
    $steps[] = ['✅', 'All 10 admin tables created/verified'];

    // Add columns to existing tables
    $alters = [
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS ban_reason text DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS suspension_ends_at timestamp NULL DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS admin_notes text DEFAULT NULL",
        "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_seen timestamp NULL DEFAULT NULL",
        "ALTER TABLE jobs ADD COLUMN IF NOT EXISTS is_flagged tinyint(1) DEFAULT 0",
        "ALTER TABLE jobs ADD COLUMN IF NOT EXISTS flag_reason varchar(255) DEFAULT NULL",
    ];
    foreach ($alters as $sql) { try { $pdo->exec($sql); } catch(Exception $e){} }
    $steps[] = ['✅', 'Extended users/jobs tables'];

    // Seed settings
    $settings = ['commission_rate'=>'12','verified_price'=>'49','premium_price'=>'99','free_proposals_limit'=>'3','payment_gateway'=>'paystack','maintenance_mode'=>'0','moderate_jobs'=>'0','require_ghcard'=>'1'];
    foreach ($settings as $k=>$v) { try { $pdo->prepare("INSERT IGNORE INTO platform_settings (setting_key,setting_val) VALUES (?,?)")->execute([$k,$v]); } catch(Exception $e){} }
    $steps[] = ['✅', 'Platform settings seeded'];

    // Seed badges
    $badges = [
        ['Top Provider','top-provider','🏆','#F7B731','Awarded to providers with 20+ completed jobs'],
        ['Verified Expert','verified-expert','✓','#00D4C8','Ghana Card and skills verified'],
        ['Rising Talent','rising-talent','📈','#7C6FF7','Fast-growing freelancer'],
        ['Trusted Client','trusted-client','🤝','#1FD9A0','Client with 5+ completed hires'],
        ['Premium Member','premium-member','⭐','#FF6B4A','Active premium subscription'],
        ['5-Star Rated','five-star','⭐','#F7B731','Maintained 5-star rating'],
    ];
    foreach ($badges as [$n,$s,$i,$c,$d]) { try { $pdo->prepare("INSERT IGNORE INTO badges (name,slug,icon,color,description) VALUES (?,?,?,?,?)")->execute([$n,$s,$i,$c,$d]); } catch(Exception $e){} }
    $steps[] = ['✅', 'Default badges seeded'];

    // Create/update admin account
    $hash = password_hash($ADMIN_PASSWORD, PASSWORD_BCRYPT, ['cost'=>12]);
    $existing = $pdo->prepare("SELECT id FROM admins WHERE email=? LIMIT 1");
    $existing->execute([$ADMIN_EMAIL]);
    $row = $existing->fetch();

    if ($row) {
        $pdo->prepare("UPDATE admins SET name=?,password_hash=?,role='super_admin',is_active=1 WHERE email=?")->execute([$ADMIN_NAME,$hash,$ADMIN_EMAIL]);
        $adminId = $row['id'];
        $steps[] = ['✅', 'Admin account updated (ID: '.$adminId.')'];
    } else {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
        $pdo->prepare("INSERT INTO admins (uuid,name,email,password_hash,role,is_active) VALUES (?,?,?,?,'super_admin',1)")->execute([$uuid,$ADMIN_NAME,$ADMIN_EMAIL,$hash]);
        $adminId = (int)$pdo->lastInsertId();
        $steps[] = ['✅', 'Admin account created (ID: '.$adminId.')'];
    }

    // Verify
    $v = $pdo->prepare("SELECT password_hash, role, is_active FROM admins WHERE email=? LIMIT 1");
    $v->execute([$ADMIN_EMAIL]); $vRow = $v->fetch();
    if ($vRow && password_verify($ADMIN_PASSWORD, $vRow['password_hash']) && $vRow['role']==='super_admin') {
        $steps[] = ['✅✅', 'LOGIN TEST PASSED — credentials verified!'];
        $allGood = true;
    } else {
        $errors[] = 'Login test FAILED — hash or role mismatch!';
    }

    // Clear rate limit
    try { $pdo->prepare("DELETE FROM admin_login_attempts WHERE email=?")->execute([$ADMIN_EMAIL]); } catch(Exception $e){}
    try { $pdo->prepare("DELETE FROM admin_sessions WHERE admin_id=?")->execute([$adminId]); } catch(Exception $e){}
    $steps[] = ['✅', 'Cleared old sessions and rate limits'];

} catch (PDOException $e) {
    $errors[] = 'DB Error: '.$e->getMessage();
    $errors[] = 'Check DB_HOST, DB_NAME, DB_USER, DB_PASS at top of this file.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>GigGhana Admin Setup</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{background:#0C0E14;color:#F2F4F8;font-family:'Segoe UI',system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;background-image:radial-gradient(ellipse 60% 50% at 15% 0%,rgba(255,77,106,0.1) 0%,transparent 55%);}
.card{width:100%;max-width:560px;background:#13161E;border:1px solid rgba(255,255,255,0.08);border-radius:18px;overflow:hidden;box-shadow:0 24px 64px rgba(0,0,0,0.5);}
.card-top{padding:26px 28px 20px;border-bottom:1px solid rgba(255,255,255,0.07);}
.logo{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.mark{width:38px;height:38px;background:linear-gradient(135deg,#FF4D6A,#C0384F);border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;color:#fff;}
.lt{font-size:19px;font-weight:800;} .lt em{color:#FF4D6A;font-style:normal;}
h1{font-size:16px;font-weight:700;margin-bottom:3px;color:#F2F4F8;}
.sub{font-size:12px;color:#4E5A6E;}
.steps{padding:18px 28px;display:flex;flex-direction:column;gap:5px;max-height:320px;overflow-y:auto;}
.step{font-size:12px;padding:7px 12px;border-radius:7px;font-family:'Courier New',monospace;}
.step.ok{background:rgba(31,217,160,0.06);color:#a8f0d8;}
.step.fail{background:rgba(255,77,106,0.08);color:#FF8A9A;}
.creds{margin:4px 28px 20px;background:rgba(0,212,200,0.07);border:1px solid rgba(0,212,200,0.2);border-radius:12px;padding:18px;}
.creds h3{font-size:12px;font-weight:700;color:#00D4C8;margin-bottom:12px;}
.cr{display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:12.5px;}
.cr:last-child{border-bottom:none;}
.cl{color:#9BA8BF;font-size:11.5px;}
.cv{font-family:'Courier New',monospace;background:rgba(0,0,0,0.3);padding:3px 8px;border-radius:4px;font-size:12px;}
.cv.pw{color:#F7B731;}
.errs{margin:0 28px 16px;background:rgba(255,77,106,0.08);border:1px solid rgba(255,77,106,0.22);border-radius:10px;padding:14px;}
.errs h3{color:#FF4D6A;font-size:12px;font-weight:700;margin-bottom:8px;}
.err{font-size:11.5px;color:#FF8A9A;padding:3px 0;font-family:'Courier New',monospace;}
.btns{padding:0 28px 26px;display:flex;flex-direction:column;gap:9px;}
.btn{display:block;width:100%;padding:12px;border-radius:10px;border:none;cursor:pointer;font-size:13.5px;font-weight:700;text-align:center;text-decoration:none;transition:all .2s;}
.btn-go{background:linear-gradient(135deg,#FF4D6A,#C0384F);color:#fff;box-shadow:0 4px 18px rgba(255,77,106,0.3);}
.btn-go:hover{transform:translateY(-2px);}
.btn-del{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#9BA8BF;}
.warn{padding:10px 28px 20px;font-size:11px;color:#F7B731;text-align:center;}
</style>
</head>
<body>
<div class="card">
  <div class="card-top">
    <div class="logo"><div class="mark">G</div><span class="lt">Gig<em>Ghana</em> Setup v2</span></div>
    <h1>🛠 Admin System Setup</h1>
    <p class="sub">Creating tables, seeding data, and setting up your super admin account…</p>
  </div>
  <div class="steps">
    <?php foreach($steps as [$ico,$msg]): ?>
    <div class="step <?= str_starts_with($ico,'✅')?'ok':'fail' ?>"><?= $ico ?> <?= htmlspecialchars($msg) ?></div>
    <?php endforeach; ?>
  </div>
  <?php if(!empty($errors)): ?>
  <div class="errs"><h3>❌ Errors</h3><?php foreach($errors as $e): ?><div class="err"><?= htmlspecialchars($e) ?></div><?php endforeach; ?></div>
  <?php endif; ?>
  <?php if($allGood): ?>
  <div class="creds">
    <h3>🔑 Admin Login Credentials</h3>
    <div class="cr"><span class="cl">URL</span><span class="cv">/admin/login.php</span></div>
    <div class="cr"><span class="cl">Email</span><span class="cv"><?= $ADMIN_EMAIL ?></span></div>
    <div class="cr"><span class="cl">Password</span><span class="cv pw"><?= $ADMIN_PASSWORD ?></span></div>
    <div class="cr"><span class="cl">Role</span><span class="cv" style="color:#1FD9A0;">super_admin ✓</span></div>
  </div>
  <div class="btns">
    <a href="/admin/login.php" class="btn btn-go">🛡 Go to Admin Login →</a>
    <a href="?delete=1" class="btn btn-del" onclick="return confirm('Delete this file now?')">🗑 Delete This Setup File</a>
  </div>
  <div class="warn">⚠️ DELETE this file after logging in. It shows your password!</div>
  <?php else: ?>
  <div class="btns"><a href="?" class="btn btn-del">🔄 Try Again</a></div>
  <?php endif; ?>
</div>
<?php
if (isset($_GET['delete']) && $allGood) {
    if (@unlink(__FILE__)) echo '<script>alert("Deleted! Redirecting..."); window.location="/admin/login.php";</script>';
    else echo '<script>alert("Could not auto-delete. Please delete admin/setup.php manually!");</script>';
}
?>
</body>
</html>