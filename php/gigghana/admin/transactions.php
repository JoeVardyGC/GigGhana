<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
requireRole('admin');

$type   = sanitize($_GET['type']   ?? '');
$status = sanitize($_GET['status'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 25; $offset = ($page-1)*$perPage;

try {
    $db = getDB();
    $where = ['1=1']; $params = [];
    if ($type)   { $where[] = "t.type=?";   $params[]=$type; }
    if ($status) { $where[] = "t.status=?"; $params[]=$status; }
    if ($search) { $where[] = "(u.first_name LIKE ? OR u.last_name LIKE ? OR t.reference LIKE ?)"; $params=array_merge($params,["%$search%","%$search%","%$search%"]); }
    $w = implode(' AND ',$where);
    $total = $db->prepare("SELECT COUNT(*) FROM transactions t JOIN users u ON u.id=t.user_id WHERE $w");
    $total->execute($params); $totalTx=$total->fetchColumn(); $totalPages=ceil($totalTx/$perPage);
    $stmt = $db->prepare("SELECT t.*, u.first_name, u.last_name, u.email FROM transactions t JOIN users u ON u.id=t.user_id WHERE $w ORDER BY t.created_at DESC LIMIT $perPage OFFSET $offset");
    $stmt->execute($params); $txs = $stmt->fetchAll();
    $totals = $db->query("SELECT type, SUM(net_amount) AS total FROM transactions WHERE status='completed' GROUP BY type")->fetchAll();
    $totalsMap = [];
    foreach ($totals as $t2) $totalsMap[$t2['type']] = $t2['total'];
} catch(Exception $e) { error_log($e->getMessage()); $txs=[]; $totalTx=0; $totalPages=0; $totalsMap=[]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Transactions — GigGhana Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--obsidian:#0F172A;--slate:#1E293B;--amber:#F59E0B;--mint:#10B981;--indigo:#6366F1;--red:#EF4444;--text:#E2E8F0;--text-dim:#94A3B8;--border:rgba(255,255,255,0.08);--glass:rgba(30,41,59,0.7);--font-head:'Syne',sans-serif;--font-body:'DM Sans',sans-serif;--sidebar:260px;--radius:16px;--radius-sm:10px;--transition:all 0.3s ease;}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--obsidian);color:var(--text);font-family:var(--font-body);min-height:100vh;display:flex;}
.sidebar{width:var(--sidebar);min-height:100vh;background:var(--slate);border-right:1px solid var(--border);position:fixed;top:0;left:0;z-index:200;display:flex;flex-direction:column;}
.sidebar-logo{padding:22px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;text-decoration:none;}
.logo-icon{width:34px;height:34px;background:linear-gradient(135deg,var(--amber),var(--mint));border-radius:9px;display:flex;align-items:center;justify-content:center;font-family:var(--font-head);font-weight:800;font-size:15px;color:#000;}
.logo-text{font-family:var(--font-head);font-size:18px;font-weight:800;color:var(--text);}.logo-text span{color:var(--amber);}
.admin-pill{background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:var(--red);padding:2px 8px;border-radius:5px;font-size:9px;font-weight:800;text-transform:uppercase;margin-left:auto;}
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
.content{padding:28px 32px;}
.totals-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
.total-card{background:var(--glass);backdrop-filter:blur(15px);border:1px solid var(--border);border-radius:var(--radius);padding:20px;}
.tc-label{font-size:11px;color:var(--text-dim);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;}
.tc-val{font-family:var(--font-head);font-size:22px;font-weight:800;color:var(--mint);}
.filters-bar{background:var(--glass);backdrop-filter:blur(15px);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;margin-bottom:20px;display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;}
.filter-group{display:flex;flex-direction:column;gap:5px;}
.filter-label{font-size:11px;font-weight:600;color:var(--text-dim);text-transform:uppercase;letter-spacing:.4px;}
.filter-input,.filter-select{background:rgba(0,0,0,0.25);border:1px solid var(--border);border-radius:9px;padding:8px 12px;color:var(--text);font-family:var(--font-body);font-size:13px;outline:none;transition:var(--transition);}
.filter-input:focus,.filter-select:focus{border-color:var(--red);}
.filter-input::placeholder{color:var(--text-dim);}
.filter-select option{background:var(--slate);}
.table-card{background:var(--glass);backdrop-filter:blur(15px);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
.table-wrap{overflow-x:auto;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:10px 16px;text-align:left;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--text-dim);border-bottom:1px solid var(--border);white-space:nowrap;}
.data-table td{padding:12px 16px;border-bottom:1px solid var(--border);font-size:12px;vertical-align:middle;}
.data-table tr:last-child td{border-bottom:none;}
.data-table tr:hover td{background:rgba(255,255,255,0.015);}
.cell-name{font-weight:600;font-size:13px;}
.cell-sub{font-size:11px;color:var(--text-dim);}
.badge{padding:3px 8px;border-radius:6px;font-size:10px;font-weight:700;white-space:nowrap;}
.b-completed{background:rgba(16,185,129,0.1);color:var(--mint);}
.b-pending{background:rgba(245,158,11,0.1);color:var(--amber);}
.b-failed{background:rgba(239,68,68,0.1);color:var(--red);}
.b-processing{background:rgba(99,102,241,0.1);color:#A5B4FC;}
.tx-credit{color:var(--mint);font-weight:700;font-family:var(--font-head);}
.tx-debit{color:var(--red);font-weight:700;font-family:var(--font-head);}
.ref{font-family:monospace;font-size:11px;color:var(--text-dim);}
.pagination{display:flex;gap:8px;justify-content:center;padding:20px;flex-wrap:wrap;}
.pag-btn{padding:7px 13px;border-radius:9px;text-decoration:none;font-size:13px;font-weight:600;color:var(--text-dim);background:rgba(255,255,255,0.04);border:1px solid var(--border);transition:var(--transition);}
.pag-btn.active,.pag-btn:hover{background:rgba(239,68,68,0.1);color:var(--red);border-color:rgba(239,68,68,0.3);}
@media(max-width:1024px){.totals-row{grid-template-columns:repeat(2,1fr);}}
@media(max-width:768px){.sidebar{display:none;}.main{margin-left:0;}.content{padding:16px;}}
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
    <a href="<?= APP_URL ?>/admin/jobs.php" class="nav-item">📋 Jobs</a>
    <a href="<?= APP_URL ?>/admin/transactions.php" class="nav-item active">💳 Transactions</a>
    <a href="#" class="nav-item">⚖️ Disputes</a>
    <a href="#" class="nav-item">💸 Withdrawals</a>
    <a href="<?= APP_URL ?>/auth/logout.php" class="nav-item" style="color:var(--red);margin-top:20px;">🚪 Sign Out</a>
  </nav>
</aside>

<div class="main">
  <header class="topbar">
    <div>
      <div class="page-title">Transactions</div>
      <div class="page-sub"><?= number_format($totalTx) ?> total transactions</div>
    </div>
    <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn btn-ghost">← Dashboard</a>
  </header>

  <div class="content">
    <!-- TOTALS -->
    <div class="totals-row">
      <div class="total-card"><div class="tc-label">Total Deposits</div><div class="tc-val"><?= formatCurrency($totalsMap['deposit'] ?? 0) ?></div></div>
      <div class="total-card"><div class="tc-label">Escrow Released</div><div class="tc-val"><?= formatCurrency($totalsMap['escrow_release'] ?? 0) ?></div></div>
      <div class="total-card"><div class="tc-label">Withdrawals</div><div class="tc-val" style="color:var(--amber);"><?= formatCurrency($totalsMap['withdrawal'] ?? 0) ?></div></div>
      <div class="total-card"><div class="tc-label">Platform Revenue</div><div class="tc-val" style="color:var(--indigo);"><?= formatCurrency($totalsMap['platform_fee'] ?? 0) ?></div></div>
    </div>

    <!-- FILTERS -->
    <form method="GET">
      <div class="filters-bar">
        <div class="filter-group">
          <div class="filter-label">Search</div>
          <input type="text" name="search" class="filter-input" placeholder="Name or reference…" value="<?= htmlspecialchars($search) ?>" style="min-width:200px;">
        </div>
        <div class="filter-group">
          <div class="filter-label">Type</div>
          <select name="type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="deposit"         <?= $type==='deposit'         ?'selected':'' ?>>Deposit</option>
            <option value="escrow_lock"     <?= $type==='escrow_lock'     ?'selected':'' ?>>Escrow Lock</option>
            <option value="escrow_release"  <?= $type==='escrow_release'  ?'selected':'' ?>>Escrow Release</option>
            <option value="withdrawal"      <?= $type==='withdrawal'      ?'selected':'' ?>>Withdrawal</option>
            <option value="platform_fee"    <?= $type==='platform_fee'    ?'selected':'' ?>>Platform Fee</option>
            <option value="refund"          <?= $type==='refund'          ?'selected':'' ?>>Refund</option>
          </select>
        </div>
        <div class="filter-group">
          <div class="filter-label">Status</div>
          <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="completed"  <?= $status==='completed' ?'selected':'' ?>>Completed</option>
            <option value="pending"    <?= $status==='pending'   ?'selected':'' ?>>Pending</option>
            <option value="failed"     <?= $status==='failed'    ?'selected':'' ?>>Failed</option>
          </select>
        </div>
        <button type="submit" class="btn btn-ghost" style="border-color:rgba(239,68,68,0.3);color:var(--red);">Filter</button>
        <?php if ($search||$type||$status): ?><a href="<?= APP_URL ?>/admin/transactions.php" class="btn btn-ghost">✕ Clear</a><?php endif; ?>
      </div>
    </form>

    <div class="table-card">
      <?php if (empty($txs)): ?>
      <div style="padding:48px;text-align:center;color:var(--text-dim);"><div style="font-size:40px;margin-bottom:12px;">💳</div>No transactions found.</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <thead><tr><th>User</th><th>Reference</th><th>Type</th><th>Amount</th><th>Fee</th><th>Net</th><th>Gateway</th><th>Date</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($txs as $tx): ?>
            <tr>
              <td>
                <div class="cell-name"><?= sanitize($tx['first_name'].' '.$tx['last_name']) ?></div>
                <div class="cell-sub"><?= sanitize(substr($tx['email'],0,26)) ?></div>
              </td>
              <td><span class="ref"><?= sanitize($tx['reference']) ?></span></td>
              <td><div style="font-size:12px;font-weight:600;"><?= ucfirst(str_replace('_',' ',$tx['type'])) ?></div></td>
              <td class="<?= in_array($tx['type'],['deposit','escrow_release'])?'tx-credit':'tx-debit' ?>">
                <?= formatCurrency($tx['amount']) ?>
              </td>
              <td style="color:var(--text-dim);"><?= formatCurrency($tx['fee']) ?></td>
              <td class="tx-credit"><?= formatCurrency($tx['net_amount']) ?></td>
              <td><span class="cell-sub"><?= ucfirst($tx['payment_gateway'] ?? 'paystack') ?></span></td>
              <td style="font-size:11px;color:var(--text-dim);"><?= date('M j, Y<\b\r>g:ia', strtotime($tx['created_at'])) ?></td>
              <td><span class="badge b-<?= $tx['status'] ?>"><?= ucfirst($tx['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <?php if ($page>1): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="pag-btn">← Prev</a><?php endif; ?>
        <?php for($i=max(1,$page-3);$i<=min($totalPages,$page+3);$i++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="pag-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page<$totalPages): ?><a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="pag-btn">Next →</a><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>