<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRF($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $jobId  = (int)($_POST['job_id'] ?? 0);
    try {
        $db = getDB();
        if ($action === 'feature')   $db->prepare("UPDATE jobs SET is_featured=1 WHERE id=?")->execute([$jobId]);
        if ($action === 'unfeature') $db->prepare("UPDATE jobs SET is_featured=0 WHERE id=?")->execute([$jobId]);
        if ($action === 'cancel')    $db->prepare("UPDATE jobs SET status='cancelled' WHERE id=?")->execute([$jobId]);
        redirect(APP_URL . '/admin/jobs.php?success=Action+applied');
    } catch(Exception $e) { error_log($e->getMessage()); }
}

$search   = sanitize($_GET['search']   ?? '');
$status   = sanitize($_GET['status']   ?? '');
$category = (int)($_GET['category']    ?? 0);
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 20;
$offset   = ($page - 1) * $perPage;

try {
    $db    = getDB();
    $cats  = getCategories();
    $where = ['1=1']; $params = [];
    if ($search)   { $where[] = "(j.title LIKE ? OR j.description LIKE ?)"; $params = array_merge($params,["%$search%","%$search%"]); }
    if ($status)   { $where[] = "j.status=?"; $params[] = $status; }
    if ($category) { $where[] = "j.category_id=?"; $params[] = $category; }
    $w = implode(' AND ', $where);

    $total = $db->prepare("SELECT COUNT(*) FROM jobs j WHERE $w");
    $total->execute($params); $totalJobs=$total->fetchColumn(); $totalPages=ceil($totalJobs/$perPage);

    $stmt = $db->prepare(
        "SELECT j.*, c.name AS cat_name, u.first_name, u.last_name, u.email,
         (SELECT COUNT(*) FROM proposals WHERE job_id=j.id) AS prop_cnt
         FROM jobs j LEFT JOIN categories c ON c.id=j.category_id
         JOIN users u ON u.id=j.client_id WHERE $w
         ORDER BY j.created_at DESC LIMIT $perPage OFFSET $offset"
    );
    $stmt->execute($params); $jobs = $stmt->fetchAll();
} catch(Exception $e) { error_log($e->getMessage()); $jobs=[]; $totalJobs=0; $totalPages=0; $cats=[]; }

$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Manage Jobs — GigGhana Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--obsidian:#0F172A;--slate:#1E293B;--amber:#F59E0B;--mint:#10B981;--indigo:#6366F1;--red:#EF4444;--text:#E2E8F0;--text-dim:#94A3B8;--border:rgba(255,255,255,0.08);--glass:rgba(30,41,59,0.7);--font-head:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--sidebar:260px;--radius:16px;--radius-sm:10px;--transition:all 0.3s ease;}
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
.btn-sm{padding:5px 11px;font-size:11px;}
.content{padding:28px 32px;}
.filters-bar{background:var(--glass);backdrop-filter:blur(15px);border:1px solid var(--border);border-radius:var(--radius);padding:18px 22px;margin-bottom:22px;display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap;}
.filter-group{display:flex;flex-direction:column;gap:6px;}
.filter-label{font-size:11px;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.5px;}
.filter-input,.filter-select{background:rgba(0,0,0,0.25);border:1px solid var(--border);border-radius:9px;padding:9px 13px;color:var(--text);font-family:var(--font-body);font-size:13px;outline:none;transition:var(--transition);}
.filter-input:focus,.filter-select:focus{border-color:var(--red);}
.filter-input::placeholder{color:var(--text-dim);}
.filter-select option{background:var(--slate);}
.table-card{background:var(--glass);backdrop-filter:blur(15px);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
.table-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);}
.table-title{font-family:var(--font-head);font-weight:700;font-size:15px;}
.table-wrap{overflow-x:auto;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:10px 18px;text-align:left;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);border-bottom:1px solid var(--border);white-space:nowrap;}
.data-table td{padding:12px 18px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle;}
.data-table tr:last-child td{border-bottom:none;}
.data-table tr:hover td{background:rgba(255,255,255,0.015);}
.cell-name{font-weight:600;font-size:13px;}
.cell-sub{font-size:11px;color:var(--text-dim);}
.badge{padding:3px 9px;border-radius:6px;font-size:10px;font-weight:700;white-space:nowrap;}
.b-open{background:rgba(16,185,129,0.1);color:var(--mint);}
.b-progress{background:rgba(99,102,241,0.1);color:#A5B4FC;}
.b-completed{background:rgba(245,158,11,0.1);color:var(--amber);}
.b-cancelled{background:rgba(239,68,68,0.1);color:var(--red);}
.b-featured{background:rgba(245,158,11,0.15);color:var(--amber);}
.action-btns{display:flex;gap:5px;flex-wrap:wrap;}
.pagination{display:flex;gap:8px;justify-content:center;padding:20px;flex-wrap:wrap;}
.pag-btn{padding:8px 14px;border-radius:9px;text-decoration:none;font-size:13px;font-weight:600;color:var(--text-dim);background:rgba(255,255,255,0.04);border:1px solid var(--border);transition:var(--transition);}
.pag-btn.active,.pag-btn:hover{background:rgba(239,68,68,0.1);color:var(--red);border-color:rgba(239,68,68,0.3);}
.alert{padding:13px 18px;border-radius:10px;margin-bottom:18px;font-size:14px;}
.alert-success{background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);color:#6EE7B7;}
.modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:500;backdrop-filter:blur(4px);align-items:center;justify-content:center;}
.modal-backdrop.show{display:flex;}
.modal{background:var(--slate);border:1px solid var(--border);border-radius:20px;padding:30px;max-width:420px;width:90%;}
.modal-title{font-family:var(--font-head);font-size:19px;font-weight:800;margin-bottom:10px;}
.modal-text{color:var(--text-dim);font-size:14px;margin-bottom:24px;line-height:1.6;}
.modal-actions{display:flex;gap:10px;}
@media(max-width:768px){.sidebar{display:none;}.main{margin-left:0;}.content{padding:18px 14px;}}
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
    <a href="<?= APP_URL ?>/admin/users.php" class="nav-item">👥 Users</a>
    <a href="<?= APP_URL ?>/admin/jobs.php" class="nav-item active">📋 Jobs</a>
    <a href="<?= APP_URL ?>/admin/transactions.php" class="nav-item">💳 Transactions</a>
    <a href="#" class="nav-item">⚖️ Disputes</a>
    <a href="#" class="nav-item">💸 Withdrawals</a>
    <a href="<?= APP_URL ?>/auth/logout.php" class="nav-item" style="color:var(--red);margin-top:20px;">🚪 Sign Out</a>
  </nav>
</aside>

<div class="main">
  <header class="topbar">
    <div>
      <div class="page-title">Manage Jobs</div>
      <div class="page-sub"><?= number_format($totalJobs) ?> total jobs</div>
    </div>
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn btn-ghost">← Dashboard</a>
  </header>

  <div class="content">
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✓ <?= sanitize($_GET['success']) ?></div>
    <?php endif; ?>

    <form method="GET" id="filterForm">
      <div class="filters-bar">
        <div class="filter-group">
          <div class="filter-label">Search</div>
          <input type="text" name="search" class="filter-input" style="min-width:240px;" placeholder="Job title…" value="<?= htmlspecialchars($search) ?>" id="searchInput">
        </div>
        <div class="filter-group">
          <div class="filter-label">Status</div>
          <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="open"        <?= $status==='open'       ?'selected':'' ?>>Open</option>
            <option value="in_progress" <?= $status==='in_progress'?'selected':'' ?>>In Progress</option>
            <option value="completed"   <?= $status==='completed'  ?'selected':'' ?>>Completed</option>
            <option value="cancelled"   <?= $status==='cancelled'  ?'selected':'' ?>>Cancelled</option>
          </select>
        </div>
        <div class="filter-group">
          <div class="filter-label">Category</div>
          <select name="category" class="filter-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($cats as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $category===$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-ghost" style="border-color:rgba(239,68,68,0.3);color:var(--red);">Search</button>
        <?php if ($search || $status || $category): ?>
        <a href="<?= APP_URL ?>/admin/jobs.php" class="btn btn-ghost">✕ Clear</a>
        <?php endif; ?>
      </div>
    </form>

    <div class="table-card">
      <div class="table-header">
        <div class="table-title">All Jobs <span style="color:var(--text-dim);font-weight:400;font-size:13px;">(<?= number_format($totalJobs) ?>)</span></div>
      </div>
      <?php if (empty($jobs)): ?>
      <div style="padding:48px;text-align:center;color:var(--text-dim);"><div style="font-size:40px;margin-bottom:12px;">📋</div>No jobs found.</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr><th>Job</th><th>Client</th><th>Category</th><th>Budget</th><th>Proposals</th><th>Posted</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($jobs as $j): ?>
            <tr>
              <td style="max-width:220px;">
                <div class="cell-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= sanitize($j['title']) ?></div>
                <?php if ($j['is_featured']): ?><span class="badge b-featured" style="margin-top:3px;display:inline-block;">⭐ Featured</span><?php endif; ?>
                <?php if ($j['is_urgent']): ?><span class="badge" style="background:rgba(245,158,11,0.1);color:var(--amber);margin-top:3px;display:inline-block;">🔥 Urgent</span><?php endif; ?>
              </td>
              <td>
                <div class="cell-name"><?= sanitize($j['first_name'].' '.$j['last_name']) ?></div>
                <div class="cell-sub"><?= sanitize(substr($j['email'],0,28)) ?></div>
              </td>
              <td><div class="cell-sub"><?= sanitize($j['cat_name'] ?? 'General') ?></div></td>
              <td style="color:var(--mint);font-weight:700;font-family:var(--font-head);"><?= formatCurrency($j['budget_min']) ?></td>
              <td style="font-weight:600;"><?= $j['prop_cnt'] ?></td>
              <td style="font-size:12px;color:var(--text-dim);"><?= date('M j, Y', strtotime($j['created_at'])) ?></td>
              <td>
                <?php $bc = match($j['status']){'open'=>'b-open','in_progress'=>'b-progress','completed'=>'b-completed',default=>'b-cancelled'}; ?>
                <span class="badge <?= $bc ?>"><?= ucfirst(str_replace('_',' ',$j['status'])) ?></span>
              </td>
              <td>
                <div class="action-btns">
                  <a href="<?= APP_URL ?>/job-details.php?id=<?= $j['id'] ?>" class="btn btn-ghost btn-sm" target="_blank">View</a>
                  <?php if (!$j['is_featured']): ?>
                  <button class="btn btn-sm" style="background:rgba(245,158,11,0.1);color:var(--amber);border:1px solid rgba(245,158,11,0.2);"
                          onclick="jobAction('feature',<?= $j['id'] ?>,'Feature this job?')">⭐ Feature</button>
                  <?php else: ?>
                  <button class="btn btn-ghost btn-sm"
                          onclick="jobAction('unfeature',<?= $j['id'] ?>,'Remove feature from this job?')">Unfeature</button>
                  <?php endif; ?>
                  <?php if ($j['status'] === 'open'): ?>
                  <button class="btn btn-sm" style="background:rgba(239,68,68,0.1);color:var(--red);border:1px solid rgba(239,68,68,0.2);"
                          onclick="jobAction('cancel',<?= $j['id'] ?>,'Cancel this job?')">Cancel</button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page > 1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="pag-btn">← Prev</a><?php endif; ?>
        <?php for ($i=max(1,$page-3);$i<=min($totalPages,$page+3);$i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="pag-btn">Next →</a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="modal-backdrop" id="jobModal">
  <div class="modal">
    <div class="modal-title" id="jModalTitle">Confirm</div>
    <p class="modal-text">This action will be applied immediately.</p>
    <div class="modal-actions">
      <form method="POST" style="display:contents;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="action" id="jAction">
        <input type="hidden" name="job_id" id="jId">
        <button type="submit" class="btn btn-red" id="jConfirmBtn">Confirm</button>
      </form>
      <button class="btn btn-ghost" onclick="document.getElementById('jobModal').classList.remove('show')">Cancel</button>
    </div>
  </div>
</div>

<script>
let st2;
document.getElementById('searchInput').addEventListener('input',function(){clearTimeout(st2);st2=setTimeout(()=>document.getElementById('filterForm').submit(),600);});
function jobAction(action,id,msg){
  document.getElementById('jAction').value=action;document.getElementById('jId').value=id;
  document.getElementById('jModalTitle').textContent=msg;
  document.getElementById('jobModal').classList.add('show');
}
document.getElementById('jobModal').addEventListener('click',function(e){if(e.target===this)this.classList.remove('show');});
</script>
</body>
</html>