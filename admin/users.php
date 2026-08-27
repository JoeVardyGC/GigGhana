<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $uid    = (int)($_POST['uid'] ?? 0);
    try {
        $db = getDB();
        match($action) {
            'ban'      => $db->prepare("UPDATE users SET is_banned=1,is_active=0 WHERE id=?")->execute([$uid]),
            'unban'    => $db->prepare("UPDATE users SET is_banned=0,is_active=1 WHERE id=?")->execute([$uid]),
            'verify'   => $db->prepare("UPDATE users SET email_verified=1 WHERE id=?")->execute([$uid]),
            'activate' => $db->prepare("UPDATE users SET is_active=1 WHERE id=?")->execute([$uid]),
            default    => null
        };
        redirect(APP_URL . '/admin/users.php?success=' . ucfirst($action) . '+applied');
    } catch(Exception $e) { error_log($e->getMessage()); }
}

$search = sanitize($_GET['search'] ?? '');
$role   = sanitize($_GET['role']   ?? '');
$status = sanitize($_GET['status'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

try {
    $db     = getDB();
    $where  = ['1=1'];
    $params = [];

    if ($search) {
        $where[]  = "(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $params   = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
    }
    if ($role && in_array($role, ['client','provider','admin'])) {
        $where[] = "u.role = ?"; $params[] = $role;
    }
    if ($status === 'banned')   { $where[] = "u.is_banned=1"; }
    elseif ($status === 'inactive') { $where[] = "u.is_active=0 AND u.is_banned=0"; }
    elseif ($status === 'active')   { $where[] = "u.is_active=1 AND u.is_banned=0"; }
    elseif ($status === 'unverified') { $where[] = "u.email_verified=0"; }

    $w = implode(' AND ', $where);
    $total = $db->prepare("SELECT COUNT(*) FROM users u WHERE $w");
    $total->execute($params);
    $totalUsers = $total->fetchColumn();
    $totalPages = ceil($totalUsers / $perPage);

    $stmt = $db->prepare(
        "SELECT u.*, p.rating_avg, p.completed_jobs AS jobs_done, p.is_verified AS prov_verified,
         w.available_balance
         FROM users u
         LEFT JOIN providers p ON p.user_id=u.id
         LEFT JOIN wallets w ON w.user_id=u.id
         WHERE $w ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset"
    );
    $stmt->execute($params);
    $users = $stmt->fetchAll();

} catch(Exception $e) {
    error_log($e->getMessage()); $users=[]; $totalUsers=0; $totalPages=0;
}

$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Users — GigGhana Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--obsidian:#0F172A;--slate:#1E293B;--slate2:#263548;--amber:#F59E0B;--mint:#10B981;--indigo:#6366F1;--red:#EF4444;--text:#E2E8F0;--text-dim:#94A3B8;--border:rgba(255,255,255,0.08);--glass:rgba(30,41,59,0.7);--font-head:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--sidebar:260px;--radius:16px;--radius-sm:10px;--transition:all 0.3s ease;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--obsidian);color:var(--text);font-family:var(--font-body);min-height:100vh;display:flex;}
.sidebar{width:var(--sidebar);min-height:100vh;background:var(--slate);border-right:1px solid var(--border);position:fixed;top:0;left:0;z-index:200;display:flex;flex-direction:column;}
.sidebar-logo{padding:22px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;text-decoration:none;}
.logo-icon{width:34px;height:34px;background:linear-gradient(135deg,var(--amber),var(--mint));border-radius:9px;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:800;font-size:15px;color:#000;}
.logo-text{font-family:var(--font-head);font-size:18px;font-weight:800;color:var(--text);}.logo-text span{color:var(--amber);}
.admin-pill{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:var(--red);padding:2px 8px;border-radius:5px;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1px;margin-left:auto;}
.sidebar-nav{flex:1;padding:12px;overflow-y:auto;}
.nav-section{font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--text-dim);padding:8px 10px;margin:14px 0 4px;}
.nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;text-decoration:none;color:var(--text-dim);font-size:13px;font-weight:500;transition:var(--transition);}
.nav-item:hover{background:rgba(255,255,255,0.05);color:var(--text);}
.nav-item.active{background:rgba(239,68,68,0.08);color:var(--red);border-left:3px solid var(--red);padding-left:9px;}
.main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:0 32px;height:66px;background:rgba(15,23,42,0.92);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;}
.page-title{font-family:var(--font-head);font-size:21px;font-weight:800;}
.page-sub{font-size:12px;color:var(--text-dim);margin-top:2px;}
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:var(--radius-sm);font-family:var(--font-body);font-size:13px;font-weight:600;cursor:pointer;border:none;text-decoration:none;transition:var(--transition);}
.btn-ghost{background:rgba(255,255,255,0.05);border:1px solid var(--border);color:var(--text);}
.btn-ghost:hover{background:rgba(255,255,255,0.1);}
.btn-red{background:linear-gradient(135deg,#EF4444,#DC2626);color:#fff;}
.btn-mint{background:linear-gradient(135deg,var(--mint),#059669);color:#fff;}
.btn-sm{padding:5px 11px;font-size:11px;}
.content{padding:28px 32px;}

/* FILTERS */
.filters-bar{background:var(--glass);backdrop-filter:blur(15px);border:1px solid var(--border);border-radius:var(--radius);padding:18px 22px;margin-bottom:22px;display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap;}
.filter-group{display:flex;flex-direction:column;gap:6px;}
.filter-label{font-size:11px;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;}
.filter-input,.filter-select{background:rgba(0,0,0,0.25);border:1px solid var(--border);border-radius:9px;padding:9px 13px;color:var(--text);font-family:var(--font-body);font-size:13px;outline:none;transition:var(--transition);}
.filter-input{min-width:220px;}
.filter-input:focus,.filter-select:focus{border-color:var(--red);}
.filter-input::placeholder{color:var(--text-dim);}
.filter-select option{background:var(--slate);}

/* TABLE CARD */
.table-card{background:var(--glass);backdrop-filter:blur(15px);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
.table-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);}
.table-title{font-family:var(--font-head);font-weight:700;font-size:15px;}
.table-wrap{overflow-x:auto;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:10px 18px;text-align:left;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);border-bottom:1px solid var(--border);white-space:nowrap;}
.data-table td{padding:13px 18px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle;}
.data-table tr:last-child td{border-bottom:none;}
.data-table tr:hover td{background:rgba(255,255,255,0.015);}
.user-cell{display:flex;align-items:center;gap:12px;}
.mini-ava{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--indigo),var(--mint));display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;color:#fff;overflow:hidden;}
.mini-ava img{width:100%;height:100%;object-fit:cover;}
.cell-name{font-weight:600;font-size:13px;}
.cell-sub{font-size:11px;color:var(--text-dim);}
.badge{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;white-space:nowrap;}
.b-client{background:rgba(99,102,241,0.1);color:#A5B4FC;}
.b-provider{background:rgba(16,185,129,0.1);color:var(--mint);}
.b-admin{background:rgba(239,68,68,0.1);color:var(--red);}
.b-active{background:rgba(16,185,129,0.1);color:var(--mint);}
.b-banned{background:rgba(239,68,68,0.12);color:var(--red);}
.b-inactive{background:rgba(255,255,255,0.06);color:var(--text-dim);}
.b-verified{background:rgba(16,185,129,0.1);color:var(--mint);}
.b-unverified{background:rgba(245,158,11,0.1);color:var(--amber);}
.action-btns{display:flex;gap:5px;flex-wrap:wrap;}

/* PAGINATION */
.pagination{display:flex;gap:8px;justify-content:center;padding:20px;flex-wrap:wrap;}
.pag-btn{padding:8px 14px;border-radius:9px;text-decoration:none;font-size:13px;font-weight:600;color:var(--text-dim);background:rgba(255,255,255,0.04);border:1px solid var(--border);transition:var(--transition);}
.pag-btn.active,.pag-btn:hover{background:rgba(239,68,68,0.1);color:var(--red);border-color:rgba(239,68,68,0.3);}

/* ALERT */
.alert{padding:13px 18px;border-radius:10px;margin-bottom:18px;font-size:14px;}
.alert-success{background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:#6EE7B7;}

/* MODAL */
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:500;backdrop-filter:blur(4px);align-items:center;justify-content:center;}
.modal-backdrop.show{display:flex;}
.modal{background:var(--slate);border:1px solid var(--border);border-radius:20px;padding:30px;max-width:420px;width:90%;}
.modal-title{font-family:var(--font-head);font-size:19px;font-weight:800;margin-bottom:10px;}
.modal-text{color:var(--text-dim);font-size:14px;margin-bottom:24px;line-height:1.6;}
.modal-actions{display:flex;gap:10px;}

@media(max-width:768px){.sidebar{display:none;}.main{margin-left:0;}.content{padding:18px 14px;}.filters-bar{flex-direction:column;}.filter-input{min-width:100%;}}
</style>
</head>
<body>
<aside class="sidebar">
  <a href="<?= APP_URL ?>/index.php" class="sidebar-logo">
    <div class="logo-icon">G</div><span class="logo-text">Gig<span>Ghana</span></span><span class="admin-pill">Admin</span>
  </a>
  <nav class="sidebar-nav">
    <div class="nav-section">Overview</div>
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="nav-item">📊 Dashboard</a>
    <div class="nav-section">Management</div>
    <a href="<?= APP_URL ?>/admin/users.php" class="nav-item active">👥 Users</a>
    <a href="<?= APP_URL ?>/admin/jobs.php" class="nav-item">📋 Jobs</a>
    <a href="<?= APP_URL ?>/admin/transactions.php" class="nav-item">💳 Transactions</a>
    <a href="#" class="nav-item">⚖️ Disputes</a>
    <a href="#" class="nav-item">💸 Withdrawals</a>
    <div class="nav-section">Platform</div>
    <a href="<?= APP_URL ?>/index.php" class="nav-item" target="_blank">🌐 View Site</a>
    <a href="<?= APP_URL ?>/auth/logout.php" class="nav-item" style="color:var(--red);">🚪 Sign Out</a>
  </nav>
</aside>

<div class="main">
  <header class="topbar">
    <div>
      <div class="page-title">Manage Users</div>
      <div class="page-sub"><?= number_format($totalUsers) ?> total users</div>
    </div>
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn btn-ghost">← Dashboard</a>
  </header>

  <div class="content">
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✓ <?= sanitize($_GET['success']) ?></div>
    <?php endif; ?>

    <!-- FILTERS -->
    <form method="GET" id="filterForm">
      <div class="filters-bar">
        <div class="filter-group">
          <div class="filter-label">Search</div>
          <input type="text" name="search" class="filter-input" placeholder="Name or email…" value="<?= htmlspecialchars($search) ?>" id="searchInput">
        </div>
        <div class="filter-group">
          <div class="filter-label">Role</div>
          <select name="role" class="filter-select" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="client"   <?= $role==='client'   ?'selected':'' ?>>Client</option>
            <option value="provider" <?= $role==='provider' ?'selected':'' ?>>Provider</option>
            <option value="admin"    <?= $role==='admin'    ?'selected':'' ?>>Admin</option>
          </select>
        </div>
        <div class="filter-group">
          <div class="filter-label">Status</div>
          <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active"     <?= $status==='active'     ?'selected':'' ?>>Active</option>
            <option value="banned"     <?= $status==='banned'     ?'selected':'' ?>>Banned</option>
            <option value="inactive"   <?= $status==='inactive'   ?'selected':'' ?>>Inactive</option>
            <option value="unverified" <?= $status==='unverified' ?'selected':'' ?>>Unverified</option>
          </select>
        </div>
        <button type="submit" class="btn btn-red">Search</button>
        <?php if ($search || $role || $status): ?>
        <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-ghost">✕ Clear</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- TABLE -->
    <div class="table-card">
      <div class="table-header">
        <div class="table-title">Users <span style="color:var(--text-dim);font-weight:400;font-size:13px;">(<?= number_format($totalUsers) ?>)</span></div>
      </div>
      <?php if (empty($users)): ?>
      <div style="padding:48px;text-align:center;color:var(--text-dim);">
        <div style="font-size:40px;margin-bottom:12px;">🔍</div>
        No users found matching your filters.
      </div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Email</th>
              <th>Wallet</th>
              <th>Joined</th>
              <th>Verified</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
              <td>
                <div class="user-cell">
                  <div class="mini-ava">
                    <?php if ($u['avatar']): ?><img src="<?= sanitize($u['avatar']) ?>" alt="">
                    <?php else: echo strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)); endif; ?>
                  </div>
                  <div>
                    <div class="cell-name"><?= sanitize($u['first_name'].' '.$u['last_name']) ?></div>
                    <div class="cell-sub"><?= sanitize($u['location'] ?: 'Ghana') ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge b-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
              <td>
                <div class="cell-sub" style="font-size:12px;"><?= sanitize($u['email']) ?></div>
                <?php if ($u['phone']): ?><div class="cell-sub"><?= sanitize($u['phone']) ?></div><?php endif; ?>
              </td>
              <td style="color:var(--mint);font-weight:700;font-family:var(--font-head);">
                <?= formatCurrency($u['available_balance'] ?? 0) ?>
              </td>
              <td style="font-size:12px;color:var(--text-dim);"><?= date('M j, Y', strtotime($u['created_at'])) ?><br><?= timeAgo($u['created_at']) ?></td>
              <td>
                <span class="badge <?= $u['email_verified'] ? 'b-verified' : 'b-unverified' ?>">
                  <?= $u['email_verified'] ? '✓ Verified' : '! Unverified' ?>
                </span>
              </td>
              <td>
                <span class="badge <?= $u['is_banned'] ? 'b-banned' : ($u['is_active'] ? 'b-active' : 'b-inactive') ?>">
                  <?= $u['is_banned'] ? 'Banned' : ($u['is_active'] ? 'Active' : 'Inactive') ?>
                </span>
              </td>
              <td>
                <div class="action-btns">
                  <?php if ($u['role'] !== 'admin'): ?>
                    <?php if ($u['is_banned']): ?>
                    <button class="btn btn-mint btn-sm" onclick="quickAction('unban',<?= $u['id'] ?>,'Unban <?= htmlspecialchars(addslashes($u['first_name'])) ?>?')">Unban</button>
                    <?php else: ?>
                    <button class="btn btn-sm" style="background:rgba(239,68,68,0.1);color:var(--red);border:1px solid rgba(239,68,68,0.2);"
                            onclick="quickAction('ban',<?= $u['id'] ?>,'Ban <?= htmlspecialchars(addslashes($u['first_name'])) ?>?')">Ban</button>
                    <?php endif; ?>
                    <?php if (!$u['email_verified']): ?>
                    <button class="btn btn-ghost btn-sm" onclick="quickAction('verify',<?= $u['id'] ?>,'Verify email for <?= htmlspecialchars(addslashes($u['first_name'])) ?>?')">Verify</button>
                    <?php endif; ?>
                  <?php endif; ?>
                  <a href="mailto:<?= sanitize($u['email']) ?>" class="btn btn-ghost btn-sm">Email</a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="pag-btn">← Prev</a>
        <?php endif; ?>
        <?php for ($i = max(1,$page-3); $i <= min($totalPages,$page+3); $i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="pag-btn">Next →</a>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ACTION MODAL -->
<div class="modal-backdrop" id="actionModal">
  <div class="modal">
    <div class="modal-title" id="actionTitle">Confirm Action</div>
    <p class="modal-text" id="actionText">Are you sure?</p>
    <div class="modal-actions">
      <form method="POST" style="display:contents;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" id="actionName">
        <input type="hidden" name="uid" id="actionUid">
        <button type="submit" class="btn btn-red" id="actionConfirmBtn">Confirm</button>
      </form>
      <button class="btn btn-ghost" onclick="document.getElementById('actionModal').classList.remove('show')">Cancel</button>
    </div>
  </div>
</div>

<script>
// Live search
let st;
document.getElementById('searchInput').addEventListener('input', function() {
  clearTimeout(st);
  st = setTimeout(() => document.getElementById('filterForm').submit(), 600);
});

function quickAction(action, uid, msg) {
  document.getElementById('actionName').value = action;
  document.getElementById('actionUid').value  = uid;
  document.getElementById('actionTitle').textContent = msg;
  document.getElementById('actionText').textContent  = 'This action will be applied immediately.';
  const btn = document.getElementById('actionConfirmBtn');
  btn.className = action === 'ban' ? 'btn btn-red' : 'btn btn-mint';
  btn.textContent = action === 'ban' ? '⛔ Ban' : action === 'unban' ? '✓ Unban' : '✓ Apply';
  document.getElementById('actionModal').classList.add('show');
}

document.getElementById('actionModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('show');
});
</script>
</body>
</html>