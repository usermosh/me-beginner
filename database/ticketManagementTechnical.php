<?php
require_once "sessionChecker.php";
require_once "config.php";

if ($_SESSION['usertype'] !== 'TECHNICAL') {
    header("location: index.php");
    exit;
}

// ── Fix status column size & normalise existing data ─────────────────────
mysqli_query($link, "ALTER TABLE tbltickets MODIFY COLUMN status VARCHAR(50)");
mysqli_query($link, "UPDATE tbltickets SET status='PENDING'      WHERE (status IS NULL OR TRIM(status)='') AND (assignedTo IS NULL OR TRIM(assignedTo)='')");
mysqli_query($link, "UPDATE tbltickets SET status='ON-GOING'     WHERE (status IS NULL OR TRIM(status)='') AND assignedTo IS NOT NULL AND TRIM(assignedTo)!=''");
mysqli_query($link, "UPDATE tbltickets SET status='ON-GOING'     WHERE LOWER(TRIM(status)) IN ('inprogress','in progress','in-progress','pendingapproval','pending approval','ongoing','on going','assigned')");
mysqli_query($link, "UPDATE tbltickets SET status='FOR APPROVAL' WHERE LOWER(TRIM(status)) IN ('forapproval','for-approval','for approval')");
mysqli_query($link, "UPDATE tbltickets SET status='COMPLETED' WHERE LOWER(TRIM(status)) IN ('close','closed')");
// ─────────────────────────────────────────────────────────────────────────

function normalizeStatus($s) {
    if (!$s || !trim($s)) return 'ON-GOING';
    $s = strtolower(trim($s));
    if ($s === 'pending') return 'PENDING';
    if (in_array($s, ['on-going','on going','ongoing','inprogress','in progress','in-progress','pendingapproval','assigned'])) return 'ON-GOING';
    if (in_array($s, ['for approval','forapproval','for-approval'])) return 'FOR APPROVAL';
    if (in_array($s, ['completed','closed','close','approved'])) return 'COMPLETED';
    return strtoupper($s);
}

function addLog($link, $ticketNumber, $action, $performedBy, $details) {
    $date = date('m/d/Y g:i A');
    $sql  = "INSERT INTO tbllogs (ticketNumber, action, performedBy, datePerformed, details) VALUES (?,?,?,?,?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssss", $ticketNumber, $action, $performedBy, $date, $details);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

// ── COMPLETE handler ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnComplete'])) {
    $tn = $_POST['ticketNumber'];

    $curStatus = '';
    if ($s = mysqli_prepare($link, "SELECT status FROM tbltickets WHERE ticketNumber=?")) {
        mysqli_stmt_bind_param($s, "s", $tn);
        mysqli_stmt_execute($s);
        $r = mysqli_stmt_get_result($s);
        $row = mysqli_fetch_assoc($r);
        $curStatus = normalizeStatus($row['status'] ?? '');
        mysqli_stmt_close($s);
    }

    if ($curStatus !== 'ON-GOING') {
        $_SESSION['error'] = "Cannot complete ticket. Only ON-GOING tickets can be completed. This ticket is $curStatus.";
    } else {
        $date   = date('m/d/Y g:i A');
        $status = 'FOR APPROVAL';
        $sql = "UPDATE tbltickets SET status=?, dateCompleted=? WHERE ticketNumber=?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $status, $date, $tn);
            if (mysqli_stmt_execute($stmt)) {
                addLog($link, $tn, 'completed', $_SESSION['username'], "Ticket completed by: {$_SESSION['username']}. Status set to FOR APPROVAL.");
                $_SESSION['success'] = "Ticket marked as complete! Waiting for admin approval.";
            } else {
                $_SESSION['error'] = "Error completing ticket. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    header("location: ticketManagementTechnical.php"); exit;
}

// ── Fetch only this technician's tickets ──────────────────────────────────
$searchQuery = '';
$tickets = [];
$sql = "SELECT * FROM tbltickets WHERE assignedTo=? ORDER BY dateCreated DESC";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $r = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($r)) $tickets[] = $row;
    mysqli_stmt_close($stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSearch'])) {
    $searchQuery = trim($_POST['searchInput']);
    $tickets = array_values(array_filter($tickets, fn($t) =>
        stripos($t['ticketNumber'], $searchQuery) !== false ||
        stripos($t['problem'],      $searchQuery) !== false ||
        stripos($t['status'],       $searchQuery) !== false
    ));
}

// ── Stats ─────────────────────────────────────────────────────────────────
$stats = ['ON-GOING' => 0, 'FOR APPROVAL' => 0, 'COMPLETED' => 0];
foreach ($tickets as $t) {
    $ns = normalizeStatus($t['status']);
    if (isset($stats[$ns])) $stats[$ns]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ticket Management — Technical</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#0d2137 0%,#0f3460 50%,#0a1a2e 100%);background-attachment:fixed;min-height:100vh;padding:30px 20px}
.container{max-width:1400px;margin:0 auto}

.header-section{background:rgba(255,255,255,.05);backdrop-filter:blur(10px);border-radius:12px;padding:25px 30px;margin-bottom:25px;border:1px solid rgba(255,255,255,.1);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px}
.header-left{display:flex;align-items:center;gap:16px}
.header-icon{width:52px;height:52px;background:linear-gradient(135deg,#0984e3,#0652a0);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(9,132,227,.4)}
.header-icon svg{width:28px;height:28px;stroke:#fff;stroke-width:2;fill:none}
.header-text h1{color:#fff;font-size:24px;font-weight:600;margin-bottom:4px}
.header-text p{color:#a0c4ff;font-size:13px}

.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .3s}
.btn svg{width:16px;height:16px;stroke:currentColor;stroke-width:2;fill:none}
.btn:hover{transform:translateY(-2px)}
.btn-back{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2)}
.btn-back:hover{background:rgba(255,255,255,.15)}
.btn-search{background:linear-gradient(135deg,#0984e3,#0652a0);color:#fff;box-shadow:0 4px 15px rgba(9,132,227,.3)}
.btn-clear{background:rgba(0,0,0,.05);color:#555;border:2px solid #ddd}

.message{padding:14px 18px;border-radius:10px;margin-bottom:20px;border-left:4px solid;font-weight:600;font-size:14px;animation:slideDown .4s ease}
@keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.success{background:#d4edda;border-color:#27ae60;color:#155724}
.error{background:#f8d7da;border-color:#e74c3c;color:#721c24}

.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:18px;margin-bottom:22px}
.stat-card{background:rgba(255,255,255,.95);border-radius:12px;padding:22px 24px;box-shadow:0 6px 20px rgba(0,0,0,.15);border-top:4px solid}
.stat-card.total{border-color:#0984e3} .stat-card.ongoing{border-color:#3498db} .stat-card.forapproval{border-color:#9b59b6} .stat-card.completed{border-color:#e67e22}
.stat-number{font-size:32px;font-weight:700;margin-bottom:6px}
.stat-card.total .stat-number{color:#0984e3} .stat-card.ongoing .stat-number{color:#3498db} .stat-card.forapproval .stat-number{color:#9b59b6} .stat-card.completed .stat-number{color:#e67e22}
.stat-label{font-size:12px;font-weight:600;color:#7f8c8d;text-transform:uppercase;letter-spacing:.5px}

.search-section{background:rgba(255,255,255,.95);padding:18px 22px;border-radius:12px;margin-bottom:22px;box-shadow:0 6px 18px rgba(0,0,0,.12)}
.search-controls{display:flex;gap:10px;flex-wrap:wrap}
.search-controls input{flex:1;min-width:280px;padding:11px 15px;border:2px solid #ddd;border-radius:8px;font-size:14px;transition:border-color .3s}
.search-controls input:focus{outline:none;border-color:#0984e3;box-shadow:0 0 0 3px rgba(9,132,227,.1)}

.table-container{background:rgba(255,255,255,.95);border-radius:12px;overflow-x:auto;box-shadow:0 8px 25px rgba(0,0,0,.18)}
table{width:100%;border-collapse:collapse;min-width:750px}
table thead{background:linear-gradient(135deg,#0652a0,#0984e3);color:#fff}
table th{padding:13px 14px;text-align:left;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
table tbody tr{border-bottom:1px solid #f0f0f0;transition:background .2s}
table tbody tr:hover{background:#f0f7ff}
table td{padding:11px 14px;color:#2c3e50;font-size:13px}

.badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;white-space:nowrap}
.badge-PENDING{background:#fff3cd;color:#856404}
.badge-ON-GOING{background:#cfe2ff;color:#084298}
.badge-FOR-APPROVAL{background:#e7d6f5;color:#4a235a}
.badge-APPROVED{background:#d1f2eb;color:#0a6b4a}
.badge-COMPLETED{background:#fde8d8;color:#7d3c00}


.action-buttons{display:flex;gap:5px;flex-wrap:wrap}
.action-btn{padding:5px 10px;border:none;border-radius:6px;cursor:pointer;font-size:10px;font-weight:700;transition:all .25s;white-space:nowrap;letter-spacing:.3px}
.action-btn:hover{transform:translateY(-2px);box-shadow:0 3px 10px rgba(0,0,0,.25)}
.btn-details{background:linear-gradient(135deg,#3498db,#2980b9);color:#fff}
.btn-complete{background:linear-gradient(135deg,#00b894,#00816a);color:#fff}

.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:1000;justify-content:center;align-items:center;backdrop-filter:blur(4px)}
.modal.active{display:flex}
.modal-content{background:#fff;padding:36px;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:560px;width:90%;max-height:85vh;overflow-y:auto}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;border-bottom:2px solid #0984e3;padding-bottom:14px}
.modal-header h2{margin:0;color:#0652a0;font-size:19px}
.close-modal{background:none;border:none;font-size:26px;cursor:pointer;color:#999;transition:color .3s}
.close-modal:hover{color:#e74c3c}
.detail-row{margin-bottom:12px;padding:11px 14px;background:#f0f7ff;border-radius:8px;border-left:4px solid #0984e3}
.detail-label{font-weight:700;color:#0652a0;margin-bottom:4px;font-size:11px;text-transform:uppercase}
.detail-value{color:#34495e;font-size:13px}
.modal-actions{display:flex;gap:10px;margin-top:22px;padding-top:18px;border-top:2px solid #f0f0f0}
.btn-modal{flex:1;padding:11px 18px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:all .3s}
.btn-modal:hover{transform:translateY(-2px)}
.btn-modal-success{background:linear-gradient(135deg,#00b894,#00816a);color:#fff}
.btn-modal-secondary{background:rgba(0,0,0,.05);color:#555;border:2px solid #ddd}
.confirm-icon{text-align:center;font-size:44px;margin-bottom:16px}
.confirm-text{text-align:center;color:#2c3e50;font-size:14px;line-height:1.7}
.confirm-text strong{color:#0652a0}
.no-tickets{text-align:center;padding:55px 20px;color:#7f8c8d}
.no-tickets h3{font-size:19px;margin-bottom:8px;color:#2c3e50}

@media(max-width:768px){.header-section{flex-direction:column;align-items:flex-start}.search-controls{flex-direction:column}.search-controls input{min-width:100%}}
</style>
</head>
<body>
<div class="container">

  <!-- Header -->
  <div class="header-section">
    <div class="header-left">
      <div class="header-icon">
        <svg viewBox="0 0 24 24"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="header-text">
        <h1>Ticket Management — Technical</h1>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> — your assigned tickets</p>
      </div>
    </div>
    <a href="index.php" class="btn btn-back">
      <svg viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/></svg>
      Dashboard
    </a>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="message success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="message error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
  <?php endif; ?>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card total"><div class="stat-number"><?= count($tickets) ?></div><div class="stat-label">Total Assigned</div></div>
    <div class="stat-card ongoing"><div class="stat-number"><?= $stats['ON-GOING'] ?></div><div class="stat-label">On-Going</div></div>
    <div class="stat-card forapproval"><div class="stat-number"><?= $stats['FOR APPROVAL'] ?></div><div class="stat-label">For Approval</div></div>
    <div class="stat-card completed"><div class="stat-number"><?= $stats['COMPLETED'] ?></div><div class="stat-label">Completed</div></div>
  </div>

  <!-- Search -->
  <div class="search-section">
    <form method="POST">
      <div class="search-controls">
        <input type="text" name="searchInput" placeholder="Search by Ticket Number, Problem, or Status..." value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit" name="btnSearch" class="btn btn-search">Search</button>
        <?php if ($searchQuery): ?><a href="ticketManagementTechnical.php" class="btn btn-clear">Clear</a><?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="table-container">
    <?php if (count($tickets) > 0): ?>
    <table>
      <thead>
        <tr><th>Ticket #</th><th>Problem</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t):
          $parts = explode(' ', $t['dateCreated']);
          $date  = $parts[0] ?? '';
          $time  = isset($parts[1], $parts[2]) ? $parts[1].' '.$parts[2] : ($parts[1] ?? '');
          $ns    = normalizeStatus($t['status']);
          $badge = 'badge-'.str_replace(' ', '-', $ns);
          $json  = addslashes(htmlspecialchars(json_encode($t), ENT_QUOTES));
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($t['ticketNumber']) ?></strong></td>
          <td><?= htmlspecialchars(ucfirst($t['problem'])) ?></td>
          <td><?= htmlspecialchars($date) ?></td>
          <td><?= htmlspecialchars($time) ?></td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($t['status'] ?: $ns) ?></span></td>
          <td>
            <div class="action-buttons">
              <button class="action-btn btn-details" onclick="openDetails('<?= $json ?>')">Details</button>
              <button class="action-btn btn-complete" onclick="openComplete('<?= htmlspecialchars($t['ticketNumber']) ?>')">Complete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="no-tickets"><h3>No tickets assigned to you</h3><p><?= $searchQuery ? 'Try different keywords.' : 'You have no tickets assigned yet.' ?></p></div>
    <?php endif; ?>
  </div>

</div>

<!-- Details Modal -->
<div class="modal" id="detailsModal">
  <div class="modal-content">
    <div class="modal-header"><h2>🎫 Ticket Details</h2><button class="close-modal" onclick="closeModal('detailsModal')">×</button></div>
    <div id="detailsBody"></div>
    <div class="modal-actions"><button class="btn-modal btn-modal-secondary" onclick="closeModal('detailsModal')">Close</button></div>
  </div>
</div>

<!-- Complete Modal -->
<div class="modal" id="completeModal">
  <div class="modal-content">
    <div class="modal-header"><h2>🔧 Complete Ticket</h2><button class="close-modal" onclick="closeModal('completeModal')">×</button></div>
    <div class="confirm-icon">🔧</div>
    <div class="confirm-text">Mark ticket <strong id="completeTNLabel"></strong> as complete?<br>Status will be set to <strong>FOR APPROVAL</strong>.</div>
    <form method="POST" action="ticketManagementTechnical.php">
      <input type="hidden" name="ticketNumber" id="completeTN">
      <div class="modal-actions">
        <button type="submit" name="btnComplete" class="btn-modal btn-modal-success">Yes, Complete</button>
        <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('completeModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openDetails(json) {
  const t = JSON.parse(json);
  const ns = (t.status||'').toUpperCase().replace(' ','-');
  document.getElementById('detailsBody').innerHTML = [
    ['Ticket Number', `<strong>${t.ticketNumber}</strong>`],
    ['Problem',       t.problem],
    ['Details',       t.details||'—'],
    ['Created By',    t.createdBy],
    ['Date Created',  t.dateCreated],
    ['Status',        `<span class="badge badge-${ns}">${t.status||'—'}</span>`],
    ['Assigned To',   t.assignedTo||'—'],
    ['Date Assigned', t.dateAssigned||'—'],
    ['Date Completed',t.dateCompleted||'—'],
    ['Approved By',   t.approvedBy||'—'],
    ['Date Approved', t.dateApproved||'—'],
  ].map(([l,v])=>`<div class="detail-row"><div class="detail-label">${l}</div><div class="detail-value">${v}</div></div>`).join('');
  openModal('detailsModal');
}
function openComplete(tn) { document.getElementById('completeTN').value = tn; document.getElementById('completeTNLabel').textContent = tn; openModal('completeModal'); }
function openModal(id)    { document.getElementById(id).classList.add('active'); }
function closeModal(id)   { document.getElementById(id).classList.remove('active'); }
window.onclick = e => document.querySelectorAll('.modal.active').forEach(m => { if (e.target===m) m.classList.remove('active'); });
setTimeout(()=>{ document.querySelectorAll('.message').forEach(m=>{ m.style.transition='opacity .5s'; m.style.opacity='0'; setTimeout(()=>m.remove(),500); }); }, 5000);
</script>
</body>
</html>