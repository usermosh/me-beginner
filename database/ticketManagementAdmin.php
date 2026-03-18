<?php
require_once "sessionChecker.php";
require_once "config.php";

if ($_SESSION['usertype'] !== 'ADMINISTRATOR') {
    header("location: index.php");
    exit;
}

// ── Fix status column size & normalise existing data ─────────────────────
mysqli_query($link, "ALTER TABLE tbltickets MODIFY COLUMN status VARCHAR(50)");
mysqli_query($link, "UPDATE tbltickets SET status='PENDING'      WHERE (status IS NULL OR TRIM(status)='') AND (assignedTo IS NULL OR TRIM(assignedTo)='')");
mysqli_query($link, "UPDATE tbltickets SET status='ON-GOING'     WHERE (status IS NULL OR TRIM(status)='') AND assignedTo IS NOT NULL AND TRIM(assignedTo)!=''");
mysqli_query($link, "UPDATE tbltickets SET status='ON-GOING'     WHERE LOWER(TRIM(status)) IN ('inprogress','in progress','in-progress','pendingapproval','pending approval','ongoing','on going','assigned')");
mysqli_query($link, "UPDATE tbltickets SET status='FOR APPROVAL' WHERE LOWER(TRIM(status)) IN ('forapproval','for-approval','for approval')");
mysqli_query($link, "UPDATE tbltickets SET status='CLOSED'       WHERE LOWER(TRIM(status)) IN ('close','approved','closed')");
// ─────────────────────────────────────────────────────────────────────────

// ── Helpers ───────────────────────────────────────────────────────────────
function normalizeStatus($s) {
    if (!$s || !trim($s)) return 'PENDING';
    $s = strtolower(trim($s));
    if ($s === 'pending') return 'PENDING';
    if (in_array($s, ['on-going','on going','ongoing','inprogress','in progress','in-progress','pendingapproval','assigned'])) return 'ON-GOING';
    if (in_array($s, ['for approval','forapproval','for-approval'])) return 'FOR APPROVAL';
    if ($s === 'approved') return 'APPROVED';
    if ($s === 'completed') return 'COMPLETED';
    if (in_array($s, ['closed','close'])) return 'CLOSED';
    return strtoupper($s);
}

function addLog($link, $ticketNumber, $action, $performedBy, $details) {
    $date = date('m/d/Y g:i A');
    $sql  = "INSERT INTO tblticketlogs (ticketNumber, action, performedBy, datePerformed, details) VALUES (?,?,?,?,?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssss", $ticketNumber, $action, $performedBy, $date, $details);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
// ─────────────────────────────────────────────────────────────────────────

// ── POST handlers ─────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ASSIGN
    if (isset($_POST['btnAssign'])) {
        $tn   = $_POST['ticketNumber'];
        $tech = $_POST['assignedTo'];

        // Fetch current status
        $curStatus = '';
        if ($s = mysqli_prepare($link, "SELECT status FROM tbltickets WHERE ticketNumber=?")) {
            mysqli_stmt_bind_param($s, "s", $tn);
            mysqli_stmt_execute($s);
            $r = mysqli_stmt_get_result($s);
            $row = mysqli_fetch_assoc($r);
            $curStatus = normalizeStatus($row['status'] ?? '');
            mysqli_stmt_close($s);
        }

        if (!in_array($curStatus, ['PENDING', 'ON-GOING'])) {
            $_SESSION['error'] = "Cannot assign ticket. Only PENDING or ON-GOING tickets can be assigned. This ticket is $curStatus.";
        } else {
            $date   = date('m/d/Y g:i A');
            $status = 'ON-GOING';
            $sql = "UPDATE tbltickets SET assignedTo=?, dateAssigned=?, status=? WHERE ticketNumber=?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssss", $tech, $date, $status, $tn);
                if (mysqli_stmt_execute($stmt)) {
                    addLog($link, $tn, 'assigned', $_SESSION['username'], "Assigned to: $tech. Status set to ON-GOING.");
                    $_SESSION['success'] = "Ticket assigned to $tech successfully!";
                } else { $_SESSION['error'] = "Error assigning ticket."; }
                mysqli_stmt_close($stmt);
            }
        }
        header("location: ticketManagementAdmin.php"); exit;
    }

    // APPROVE
    if (isset($_POST['btnApprove'])) {
        $tn = $_POST['ticketNumber'];

        // Fetch current status
        $curStatus = '';
        if ($s = mysqli_prepare($link, "SELECT status FROM tbltickets WHERE ticketNumber=?")) {
            mysqli_stmt_bind_param($s, "s", $tn);
            mysqli_stmt_execute($s);
            $r = mysqli_stmt_get_result($s);
            $row = mysqli_fetch_assoc($r);
            $curStatus = normalizeStatus($row['status'] ?? '');
            mysqli_stmt_close($s);
        }

        if ($curStatus !== 'FOR APPROVAL') {
            $_SESSION['error'] = "Cannot approve ticket. Only FOR APPROVAL tickets can be approved. This ticket is $curStatus.";
        } else {
            $by     = $_SESSION['username'];
            $date   = date('m/d/Y g:i A');
            $status = 'APPROVED';
            $sql = "UPDATE tbltickets SET approvedBy=?, dateApproved=?, status=? WHERE ticketNumber=?";
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssss", $by, $date, $status, $tn);
                if (mysqli_stmt_execute($stmt)) {
                    addLog($link, $tn, 'approved', $by, "Ticket approved by: $by. Status set to APPROVED. Waiting for technical to complete.");
                    $_SESSION['success'] = "Ticket approved! Technical can now mark it as completed.";
                } else { $_SESSION['error'] = "Error approving ticket."; }
                mysqli_stmt_close($stmt);
            }
        }
        header("location: ticketManagementAdmin.php"); exit;
    }

    // DELETE
    if (isset($_POST['btnDelete']) && isset($_POST['ticketNumber'])) {
        $tn = trim($_POST['ticketNumber']);

        // Fetch ticket details
        $ticketData = null;
        if ($s = mysqli_prepare($link, "SELECT * FROM tbltickets WHERE ticketNumber=?")) {
            mysqli_stmt_bind_param($s, "s", $tn);
            mysqli_stmt_execute($s);
            $ticketData = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
            mysqli_stmt_close($s);
        }

        // Only allow delete if status is COMPLETED or CLOSED
        $curStatus = normalizeStatus($ticketData['status'] ?? '');
        if (!in_array($curStatus, ['COMPLETED', 'CLOSED'])) {
            $_SESSION['error'] = "Cannot delete ticket. Only COMPLETED or CLOSED tickets can be deleted. This ticket is $curStatus.";
            header("location: ticketManagementAdmin.php"); exit;
        }

        // Delete logs first (foreign key)
        mysqli_query($link, "DELETE FROM tblticketlogs WHERE ticketNumber='".mysqli_real_escape_string($link, $tn)."'");

        // Delete ticket
        if ($s = mysqli_prepare($link, "DELETE FROM tbltickets WHERE ticketNumber=?")) {
            mysqli_stmt_bind_param($s, "s", $tn);
            if (mysqli_stmt_execute($s)) {
                $_SESSION['success'] = "Ticket deleted successfully!";
            } else {
                $_SESSION['error'] = "Error deleting ticket.";
            }
            mysqli_stmt_close($s);
        }
        header("location: ticketManagementAdmin.php"); exit;
    }
}
// ─────────────────────────────────────────────────────────────────────────

// ── Fetch tickets ─────────────────────────────────────────────────────────
$searchQuery = '';
$tickets = [];
$result = mysqli_query($link, "SELECT * FROM tbltickets ORDER BY dateCreated DESC");
while ($row = mysqli_fetch_assoc($result)) $tickets[] = $row;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSearch'])) {
    $searchQuery = trim($_POST['searchInput']);
    $tickets = array_values(array_filter($tickets, fn($t) =>
        stripos($t['ticketNumber'], $searchQuery) !== false ||
        stripos($t['problem'],      $searchQuery) !== false ||
        stripos($t['status'],       $searchQuery) !== false
    ));
}

// ── Technical accounts ────────────────────────────────────────────────────
$techAccounts = [];
$r = mysqli_query($link, "SELECT username FROM tblaccounts WHERE usertype='TECHNICAL' AND status='ACTIVE'");
while ($row = mysqli_fetch_assoc($r)) $techAccounts[] = $row['username'];

// ── Stats ─────────────────────────────────────────────────────────────────
$stats = ['PENDING' => 0, 'ON-GOING' => 0, 'FOR APPROVAL' => 0, 'APPROVED' => 0, 'COMPLETED' => 0, 'CLOSED' => 0];
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
<title>Ticket Management — Administrator</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#0f1d3d 0%,#1a2e5e 50%,#0d1a33 100%);background-attachment:fixed;min-height:100vh;padding:30px 20px}
.container{max-width:1600px;margin:0 auto}

/* Header */
.header-section{background:rgba(255,255,255,.05);backdrop-filter:blur(10px);border-radius:12px;padding:25px 30px;margin-bottom:25px;border:1px solid rgba(255,255,255,.1);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px}
.header-left{display:flex;align-items:center;gap:16px}
.header-icon{width:52px;height:52px;background:linear-gradient(135deg,#4a90e2,#2a5298);border-radius:12px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 15px rgba(74,144,226,.3)}
.header-icon svg{width:28px;height:28px;stroke:#fff;stroke-width:2;fill:none}
.header-text h1{color:#fff;font-size:24px;font-weight:600;margin-bottom:4px}
.header-text p{color:#a0c4ff;font-size:13px}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .3s ease}
.btn svg{width:16px;height:16px;stroke:currentColor;stroke-width:2;fill:none}
.btn:hover{transform:translateY(-2px)}
.btn-back{background:rgba(255,255,255,.1);color:#fff;border:1px solid rgba(255,255,255,.2)}
.btn-back:hover{background:rgba(255,255,255,.15)}
.btn-search{background:linear-gradient(135deg,#4a90e2,#2a5298);color:#fff;box-shadow:0 4px 15px rgba(74,144,226,.3)}
.btn-clear{background:rgba(0,0,0,.05);color:#555;border:2px solid #ddd}

/* Messages */
.message{padding:14px 18px;border-radius:10px;margin-bottom:20px;border-left:4px solid;font-weight:600;font-size:14px;animation:slideDown .4s ease}
@keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.success{background:#d4edda;border-color:#27ae60;color:#155724}
.error{background:#f8d7da;border-color:#e74c3c;color:#721c24}

/* Stats */
.stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:18px;margin-bottom:22px}
.stat-card{background:rgba(255,255,255,.95);border-radius:12px;padding:18px 20px;box-shadow:0 6px 20px rgba(0,0,0,.15);border-top:4px solid}
.stat-card.total{border-color:#4a90e2} .stat-card.pending{border-color:#f39c12} .stat-card.ongoing{border-color:#3498db} .stat-card.forapproval{border-color:#9b59b6} .stat-card.approved{border-color:#1abc9c} .stat-card.completed{border-color:#e67e22} .stat-card.closed{border-color:#27ae60}
.stat-number{font-size:26px;font-weight:700;margin-bottom:3px}
.stat-card.total .stat-number{color:#4a90e2} .stat-card.pending .stat-number{color:#f39c12} .stat-card.ongoing .stat-number{color:#3498db} .stat-card.forapproval .stat-number{color:#9b59b6} .stat-card.approved .stat-number{color:#1abc9c} .stat-card.completed .stat-number{color:#e67e22} .stat-card.closed .stat-number{color:#27ae60}
.stat-label{font-size:11px;font-weight:600;color:#7f8c8d;text-transform:uppercase;letter-spacing:.5px}

/* Search */
.search-section{background:rgba(255,255,255,.95);padding:18px 22px;border-radius:12px;margin-bottom:22px;box-shadow:0 6px 18px rgba(0,0,0,.12)}
.search-controls{display:flex;gap:10px;flex-wrap:wrap}
.search-controls input{flex:1;min-width:280px;padding:11px 15px;border:2px solid #ddd;border-radius:8px;font-size:14px;transition:border-color .3s}
.search-controls input:focus{outline:none;border-color:#4a90e2;box-shadow:0 0 0 3px rgba(74,144,226,.1)}

/* Table */
.table-container{background:rgba(255,255,255,.95);border-radius:12px;overflow-x:auto;box-shadow:0 8px 25px rgba(0,0,0,.18)}
table{width:100%;border-collapse:collapse;min-width:1050px}
table thead{background:linear-gradient(135deg,#1a3a7e,#2a5298);color:#fff}
table th{padding:13px 14px;text-align:left;font-weight:600;font-size:11px;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
table tbody tr{border-bottom:1px solid #f0f0f0;transition:background .2s}
table tbody tr:hover{background:#f0f8ff}
table td{padding:11px 14px;color:#2c3e50;font-size:13px}

/* Badges */
.badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;white-space:nowrap}
.badge-PENDING{background:#fff3cd;color:#856404}
.badge-ON-GOING{background:#cfe2ff;color:#084298}
.badge-FOR-APPROVAL{background:#e7d6f5;color:#4a235a}
.badge-APPROVED{background:#d1f2eb;color:#0a6b4a}
.badge-COMPLETED{background:#fde8d8;color:#7d3c00}
.badge-CLOSED{background:#d1e7dd;color:#0a3622}

/* Action buttons */
.action-buttons{display:flex;gap:5px;flex-wrap:wrap}
.action-btn{padding:5px 10px;border:none;border-radius:6px;cursor:pointer;font-size:10px;font-weight:700;transition:all .25s;white-space:nowrap;letter-spacing:.3px}
.action-btn:hover{transform:translateY(-2px);box-shadow:0 3px 10px rgba(0,0,0,.25)}
.btn-details{background:linear-gradient(135deg,#3498db,#2980b9);color:#fff}
.btn-assign{background:linear-gradient(135deg,#9b59b6,#7d3c98);color:#fff}
.btn-complete{background:linear-gradient(135deg,#00b894,#00816a);color:#fff}
.btn-approve{background:linear-gradient(135deg,#27ae60,#1a7a40);color:#fff}
.btn-delete{background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff}

/* Modal */
.modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:1000;justify-content:center;align-items:center;backdrop-filter:blur(4px)}
.modal.active{display:flex}
.modal-content{background:#fff;padding:36px;border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:580px;width:90%;max-height:85vh;overflow-y:auto}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;border-bottom:2px solid #4a90e2;padding-bottom:14px}
.modal-header h2{margin:0;color:#1a2e5e;font-size:19px}
.close-modal{background:none;border:none;font-size:26px;cursor:pointer;color:#999;transition:color .3s}
.close-modal:hover{color:#e74c3c}
.detail-row{margin-bottom:12px;padding:11px 14px;background:#f4f8ff;border-radius:8px;border-left:4px solid #4a90e2}
.detail-label{font-weight:700;color:#2a5298;margin-bottom:4px;font-size:11px;text-transform:uppercase}
.detail-value{color:#34495e;font-size:13px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-weight:600;color:#2c3e50;margin-bottom:7px;font-size:13px}
.form-group select{width:100%;padding:11px 14px;border:2px solid #ddd;border-radius:8px;font-size:14px;transition:border-color .3s}
.form-group select:focus{outline:none;border-color:#4a90e2;box-shadow:0 0 0 3px rgba(74,144,226,.1)}
.modal-actions{display:flex;gap:10px;margin-top:22px;padding-top:18px;border-top:2px solid #f0f0f0}
.btn-modal{flex:1;padding:11px 18px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:all .3s}
.btn-modal:hover{transform:translateY(-2px)}
.btn-modal-primary{background:linear-gradient(135deg,#4a90e2,#2a5298);color:#fff}
.btn-modal-success{background:linear-gradient(135deg,#27ae60,#1a7a40);color:#fff}
.btn-modal-danger{background:linear-gradient(135deg,#e74c3c,#c0392b);color:#fff}
.btn-modal-secondary{background:rgba(0,0,0,.05);color:#555;border:2px solid #ddd}
.confirm-icon{text-align:center;font-size:44px;margin-bottom:16px}
.confirm-text{text-align:center;color:#2c3e50;font-size:14px;line-height:1.7}
.confirm-text strong{color:#1a2e5e}
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
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2v6h6M16 13H8m8 4H8" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <div class="header-text">
        <h1>Ticket Management — Administrator</h1>
        <p>Assign, approve, and manage all support tickets</p>
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
    <div class="stat-card total"><div class="stat-number"><?= count($tickets) ?></div><div class="stat-label">Total</div></div>
    <div class="stat-card pending"><div class="stat-number"><?= $stats['PENDING'] ?></div><div class="stat-label">Pending</div></div>
    <div class="stat-card ongoing"><div class="stat-number"><?= $stats['ON-GOING'] ?></div><div class="stat-label">On-Going</div></div>
    <div class="stat-card forapproval"><div class="stat-number"><?= $stats['FOR APPROVAL'] ?></div><div class="stat-label">For Approval</div></div>
    <div class="stat-card approved"><div class="stat-number"><?= $stats['APPROVED'] ?></div><div class="stat-label">Approved</div></div>
    <div class="stat-card completed"><div class="stat-number"><?= $stats['COMPLETED'] ?></div><div class="stat-label">Completed</div></div>
  </div>

  <!-- Search -->
  <div class="search-section">
    <form method="POST">
      <div class="search-controls">
        <input type="text" name="searchInput" placeholder="Search by Ticket Number, Problem, or Status..." value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit" name="btnSearch" class="btn btn-search">Search</button>
        <?php if ($searchQuery): ?><a href="ticketManagementAdmin.php" class="btn btn-clear">Clear</a><?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Table -->
  <div class="table-container">
    <?php if (count($tickets) > 0): ?>
    <table>
      <thead>
        <tr><th>Ticket #</th><th>Problem</th><th>Created By</th><th>Date</th><th>Time</th><th>Status</th><th>Assigned To</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t):
          $parts  = explode(' ', $t['dateCreated']);
          $date   = $parts[0] ?? '';
          $time   = isset($parts[1], $parts[2]) ? $parts[1].' '.$parts[2] : ($parts[1] ?? '');
          $ns     = normalizeStatus($t['status']);
          $badge  = 'badge-'.str_replace(' ', '-', $ns);
          $json   = addslashes(htmlspecialchars(json_encode($t), ENT_QUOTES));
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($t['ticketNumber']) ?></strong></td>
          <td><?= htmlspecialchars(ucfirst($t['problem'])) ?></td>
          <td><?= htmlspecialchars($t['createdBy']) ?></td>
          <td><?= htmlspecialchars($date) ?></td>
          <td><?= htmlspecialchars($time) ?></td>
          <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($t['status'] ?: $ns) ?></span></td>
          <td><?= htmlspecialchars($t['assignedTo'] ?: '—') ?></td>
          <td>
            <div class="action-buttons">
              <button class="action-btn btn-details" onclick="openDetails('<?= $json ?>')">Details</button>
              <button class="action-btn btn-assign" onclick="openAssign('<?= htmlspecialchars($t['ticketNumber']) ?>')">Assign</button>
              <button class="action-btn btn-approve" onclick="openApprove('<?= htmlspecialchars($t['ticketNumber']) ?>')">Approve</button>
              <button class="action-btn btn-delete" onclick="openDelete('<?= htmlspecialchars($t['ticketNumber']) ?>')">Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <div class="no-tickets"><h3>No tickets found</h3><p><?= $searchQuery ? 'Try different keywords.' : 'No tickets have been created yet.' ?></p></div>
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

<!-- Assign Modal -->
<div class="modal" id="assignModal">
  <div class="modal-content">
    <div class="modal-header"><h2>👷 Assign Ticket</h2><button class="close-modal" onclick="closeModal('assignModal')">×</button></div>
    <p style="color:#555;margin-bottom:18px;font-size:14px">Select a technician. Status will be set to <strong>ON-GOING</strong>.</p>
    <form method="POST" action="ticketManagementAdmin.php">
      <input type="hidden" name="ticketNumber" id="assignTN">
      <div class="form-group">
        <label>Technical Account</label>
        <select name="assignedTo" required>
          <option value="">— Select Technical —</option>
          <?php foreach ($techAccounts as $tech): ?>
            <option value="<?= htmlspecialchars($tech) ?>"><?= htmlspecialchars($tech) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="modal-actions">
        <button type="submit" name="btnAssign" class="btn-modal btn-modal-primary">✔ Assign Ticket</button>
        <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('assignModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Approve Modal -->
<div class="modal" id="approveModal">
  <div class="modal-content">
    <div class="modal-header"><h2>✅ Approve Ticket</h2><button class="close-modal" onclick="closeModal('approveModal')">×</button></div>
    <div class="confirm-icon">✅</div>
    <div class="confirm-text">Approve ticket <strong id="approveTNLabel"></strong>?<br>Status will be set to <strong>APPROVED</strong>. Technical can then mark it as completed.</div>
    <form method="POST" action="ticketManagementAdmin.php">
      <input type="hidden" name="ticketNumber" id="approveTN">
      <div class="modal-actions">
        <button type="submit" name="btnApprove" class="btn-modal btn-modal-success">Yes, Approve</button>
        <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('approveModal')">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
  <div class="modal-content">
    <div class="modal-header"><h2>🗑️ Delete Ticket</h2><button class="close-modal" onclick="closeModal('deleteModal')">×</button></div>
    <div class="confirm-icon">⚠️</div>
    <div class="confirm-text">Permanently delete ticket <strong id="deleteTNLabel"></strong>?<br>This action <strong>cannot be undone</strong>.</div>
    <form method="POST" action="ticketManagementAdmin.php">
      <input type="hidden" name="ticketNumber" id="deleteTN">
      <input type="hidden" name="btnDelete" value="1">
      <div class="modal-actions">
        <button type="submit" class="btn-modal btn-modal-danger">Yes, Delete</button>
        <button type="button" class="btn-modal btn-modal-secondary" onclick="closeModal('deleteModal')">Cancel</button>
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
function openAssign(tn)   { document.getElementById('assignTN').value = tn; openModal('assignModal'); }
function openApprove(tn)  { document.getElementById('approveTN').value = tn; document.getElementById('approveTNLabel').textContent = tn; openModal('approveModal'); }
function openDelete(tn)   { document.getElementById('deleteTN').value  = tn; document.getElementById('deleteTNLabel').textContent  = tn; openModal('deleteModal'); }
function openModal(id)   { document.getElementById(id).classList.add('active'); }
function closeModal(id)  { document.getElementById(id).classList.remove('active'); }
window.onclick = e => document.querySelectorAll('.modal.active').forEach(m => { if (e.target===m) m.classList.remove('active'); });
setTimeout(()=>{ document.querySelectorAll('.message').forEach(m=>{ m.style.transition='opacity .5s'; m.style.opacity='0'; setTimeout(()=>m.remove(),500); }); }, 5000);
</script>
</body>
</html>