<?php
require_once "sessionChecker.php";
require_once "config.php";

// Get ticket number from query string
if (!isset($_GET['ticketNumber'])) {
    header("location: accountManagement.php?tab=tickets");
    exit;
}

$ticketNumber = $_GET['ticketNumber'];

// Fetch ticket details
$ticket = null;
$sql = "SELECT * FROM tbltickets WHERE ticketNumber = ? AND createdBy = ?";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ss", $ticketNumber, $_SESSION['username']);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $ticket = mysqli_fetch_array($result, MYSQLI_ASSOC);
        } else {
            $_SESSION['error'] = "Ticket not found!";
            header("location: accountManagement.php?tab=tickets");
            exit;
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSave'])) {
    $newProblem = $_POST['problem'];
    $newDetails = $_POST['details'];

    // Update ticket
    $updateSql = "UPDATE tbltickets SET problem = ?, details = ? WHERE ticketNumber = ?";

    if ($updateStmt = mysqli_prepare($link, $updateSql)) {
        mysqli_stmt_bind_param($updateStmt, "sss", $newProblem, $newDetails, $ticketNumber);

        if (mysqli_stmt_execute($updateStmt)) {
            // Log the update
            $action = 'updated';
            $dateNow = date('m/d/Y g:i A');
            $oldDetails = 'Previous - Problem: ' . $ticket['problem'] . ', Details: ' . substr($ticket['details'], 0, 100) . '...';
            $newLogDetails = 'New - Problem: ' . $newProblem . ', Details: ' . $newDetails;
            $logSql = "INSERT INTO tblticketlogs (ticketNumber, action, performedBy, datePerformed, details) 
                      VALUES (?, ?, ?, ?, ?)";

            if ($logStmt = mysqli_prepare($link, $logSql)) {
                $combinedDetails = $oldDetails . ' | ' . $newLogDetails;
                mysqli_stmt_bind_param($logStmt, "sssss", $ticketNumber, $action, $_SESSION['username'], $dateNow, $combinedDetails);
                mysqli_stmt_execute($logStmt);
                mysqli_stmt_close($logStmt);
            }

            $_SESSION['success'] = "Ticket updated successfully!";
            header("location: accountManagement.php?tab=tickets");
            exit;
        } else {
            $_SESSION['error'] = "Error updating ticket!";
        }
        mysqli_stmt_close($updateStmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Ticket - Technical Management System</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f1d3d 0%, #1a2e5e 50%, #0d1a33 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-wrapper {
            width: 100%;
            max-width: 640px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.3);
            overflow: hidden;
        }

        /* ── Header ── */
        .form-header {
            background: linear-gradient(135deg, #1abc9c, #0a8860);
            padding: 35px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%,100% { transform: translateX(-100%) rotate(45deg); }
            50%      { transform: translateX(100%)  rotate(45deg); }
        }

        .header-icon {
            width: 70px; height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px;
            border: 3px solid rgba(255,255,255,0.4);
            position: relative; z-index: 1;
        }

        .header-icon svg {
            width: 38px; height: 38px;
            stroke: white; stroke-width: 2; fill: none;
        }

        .form-header h2 {
            color: #fff;
            font-size: 26px; font-weight: 600;
            margin-bottom: 6px;
            position: relative; z-index: 1;
        }

        .form-header p {
            color: rgba(255,255,255,0.88);
            font-size: 14px;
            position: relative; z-index: 1;
        }

        /* ── Body ── */
        .form-body { padding: 40px; }

        /* Messages */
        .message {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 22px;
            border-left: 4px solid;
            font-size: 14px; font-weight: 500;
            display: flex; align-items: center; gap: 12px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .message svg { width: 20px; height: 20px; flex-shrink: 0; }

        .msg-error {
            background: linear-gradient(135deg, #f8d7da, #f5c2c7);
            border-color: #e74c3c; color: #721c24;
        }
        .msg-error svg { stroke: #e74c3c; }

        /* Info notice */
        .info-notice {
            background: linear-gradient(135deg, rgba(26,188,156,0.08), rgba(10,136,96,0.08));
            border-left: 4px solid #1abc9c;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 28px;
            font-size: 13px;
            color: #0a5c46;
            display: flex; gap: 12px;
        }

        .info-notice svg {
            width: 20px; height: 20px;
            stroke: #1abc9c; stroke-width: 2;
            flex-shrink: 0; margin-top: 1px;
        }

        /* Ticket meta row */
        .ticket-meta {
            background: linear-gradient(135deg, rgba(26,188,156,0.08), rgba(10,136,96,0.08));
            border: 2px solid rgba(26,188,156,0.25);
            border-left: 4px solid #1abc9c;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .meta-item { display: flex; flex-direction: column; gap: 4px; }

        .meta-label {
            font-size: 11px; font-weight: 700;
            color: #0a8860;
            text-transform: uppercase; letter-spacing: 0.6px;
        }

        .meta-value {
            font-family: 'Courier New', monospace;
            font-size: 15px; font-weight: 700;
            color: #1abc9c;
        }

        .meta-value.plain {
            font-family: inherit;
            font-size: 14px;
        }

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px; font-weight: 700;
            text-transform: capitalize;
            width: fit-content;
        }

        .badge-pending    { background: #fff3cd; color: #856404; }
        .badge-inprogress { background: #cfe2ff; color: #084298; }
        .badge-completed  { background: #d1e7dd; color: #0a3622; }

        /* Form groups */
        .form-group { margin-bottom: 24px; }

        .form-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 600;
            color: #2c3e50; margin-bottom: 9px;
        }

        .form-label svg {
            width: 16px; height: 16px;
            stroke: #1abc9c; stroke-width: 2;
        }

        .required { color: #e74c3c; font-weight: bold; }

        /* Inputs */
        select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%231abc9c' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        textarea { min-height: 140px; resize: vertical; }

        select:hover, textarea:hover { border-color: #bbb; }

        select:focus, textarea:focus {
            outline: none;
            border-color: #1abc9c;
            box-shadow: 0 0 0 4px rgba(26, 188, 156, 0.12);
        }

        .char-hint {
            display: block;
            font-size: 12px; color: #999;
            margin-top: 6px; text-align: right;
        }

        /* Warning box */
        .warning-box {
            background: linear-gradient(135deg, rgba(243,156,18,0.08), rgba(214,137,16,0.08));
            border-left: 4px solid #f39c12;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 28px;
            font-size: 13px;
            color: #7d5a00;
            display: flex; gap: 12px;
        }

        .warning-box svg {
            width: 20px; height: 20px;
            stroke: #f39c12; stroke-width: 2;
            flex-shrink: 0; margin-top: 1px;
        }

        /* Action buttons */
        .form-actions {
            display: flex; gap: 12px;
            margin-top: 30px; padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex; align-items: center;
            justify-content: center; gap: 8px;
            text-decoration: none;
            font-family: inherit;
        }

        .btn svg { width: 20px; height: 20px; stroke-width: 2; }

        .btn-save {
            background: linear-gradient(135deg, #1abc9c, #0a8860);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 188, 156, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 188, 156, 0.4);
        }

        .btn-save:active { transform: translateY(0); }

        .btn-cancel {
            background: rgba(0,0,0,0.05);
            color: #555;
            border: 2px solid #ddd;
        }

        .btn-cancel:hover {
            background: rgba(0,0,0,0.08);
            border-color: #bbb;
            color: #333;
        }

        /* Error state */
        .error-state {
            text-align: center;
            padding: 40px 20px;
        }

        .error-state svg {
            width: 60px; height: 60px;
            stroke: #e74c3c; stroke-width: 2;
            margin-bottom: 16px;
        }

        .error-state h3 { color: #2c3e50; margin-bottom: 10px; }
        .error-state p  { color: #7f8c8d; margin-bottom: 24px; font-size: 14px; }

        .error-state a {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white; text-decoration: none;
            border-radius: 8px; font-weight: 600;
            transition: all 0.3s ease;
        }

        .error-state a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52,152,219,0.4);
        }

        /* Responsive */
        @media (max-width: 600px) {
            .form-body    { padding: 25px 20px; }
            .form-header  { padding: 28px 20px; }
            .form-actions { flex-direction: column; }
            .ticket-meta  { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="form-wrapper">
    <div class="form-container">

        <!-- Header -->
        <div class="form-header">
            <div class="header-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2>Update Ticket</h2>
            <p>Edit problem type and details for this support ticket</p>
        </div>

        <!-- Body -->
        <div class="form-body">

            <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='message msg-error'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                            <circle cx='12' cy='12' r='10' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M15 9l-6 6M9 9l6 6' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        <span>{$_SESSION['error']}</span>
                      </div>";
                unset($_SESSION['error']);
            }
            ?>

            <?php if ($ticket): ?>

                <!-- Info notice -->
                <div class="info-notice">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 16v-4M12 8h.01" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div>
                        <strong>Info:</strong> You can only update the <em>problem type</em> and <em>details</em>. All changes are logged automatically.
                    </div>
                </div>

                <!-- Ticket meta -->
                <div class="ticket-meta">
                    <div class="meta-item">
                        <span class="meta-label">Ticket Number</span>
                        <span class="meta-value"><?php echo htmlspecialchars($ticket['ticketNumber']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Date Created</span>
                        <span class="meta-value plain"><?php echo htmlspecialchars($ticket['dateCreated']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Created By</span>
                        <span class="meta-value plain"><?php echo htmlspecialchars($ticket['createdBy']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Current Status</span>
                        <?php
                        $statusClass = 'badge-pending';
                        $s = strtolower($ticket['status']);
                        if ($s === 'inprogress' || $s === 'in-progress') $statusClass = 'badge-inprogress';
                        if ($s === 'completed') $statusClass = 'badge-completed';
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?>
                        </span>
                    </div>
                </div>

                <!-- Warning -->
                <div class="warning-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 9v4M12 17h.01" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div>
                        <strong>Important:</strong> Updates are immediate and will be recorded in the ticket activity log for audit purposes.
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?ticketNumber=' . htmlspecialchars($ticketNumber); ?>">

                    <!-- Problem Type -->
                    <div class="form-group">
                        <label class="form-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Problem Type
                            <span class="required">*</span>
                        </label>
                        <select name="problem" id="problem" required>
                            <option value="">-- Select Problem Type --</option>
                            <option value="hardware"   <?php echo ($ticket['problem'] === 'hardware')   ? 'selected' : ''; ?>>🔧 Hardware</option>
                            <option value="software"   <?php echo ($ticket['problem'] === 'software')   ? 'selected' : ''; ?>>💻 Software</option>
                            <option value="connection" <?php echo ($ticket['problem'] === 'connection') ? 'selected' : ''; ?>>🌐 Connection</option>
                        </select>
                    </div>

                    <!-- Details -->
                    <div class="form-group">
                        <label class="form-label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M14 2v6h6M16 13H8m8 4H8m2-8H8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Issue Details
                            <span class="required">*</span>
                        </label>
                        <textarea name="details" id="details"
                                  placeholder="Describe the issue in detail..."
                                  required><?php echo htmlspecialchars($ticket['details']); ?></textarea>
                        <span class="char-hint"><span id="charCount">0</span> characters</span>
                    </div>

                    <!-- Buttons -->
                    <div class="form-actions">
                        <button type="submit" name="btnSave" class="btn btn-save">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17 21v-8H7v8M7 3v5h8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Save Changes
                        </button>
                        <!-- Anchor tag so it always works regardless of form state -->
                        <a href="accountManagement.php?tab=tickets" class="btn btn-cancel">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Cancel
                        </a>
                    </div>

                </form>

            <?php else: ?>
                <div class="error-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 9l-6 6M9 9l6 6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h3>Ticket Not Found</h3>
                    <p>This ticket doesn't exist or you don't have permission to update it.</p>
                    <a href="accountManagement.php?tab=tickets">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Back to Tickets
                    </a>
                </div>
            <?php endif; ?>

        </div><!-- /.form-body -->
    </div><!-- /.form-container -->
</div><!-- /.form-wrapper -->

<script>
// Character counter seeded with existing content
const textarea = document.getElementById('details');
const counter  = document.getElementById('charCount');

function updateCount() {
    if (!textarea || !counter) return;
    const len = textarea.value.length;
    counter.textContent = len;
    counter.style.color = len < 20 ? '#e74c3c' : len < 50 ? '#f39c12' : '#27ae60';
}

if (textarea) {
    textarea.addEventListener('input', updateCount);
    updateCount(); // seed count on load
}

// Auto-hide messages after 5 s
setTimeout(() => {
    document.querySelectorAll('.message').forEach(msg => {
        msg.style.transition = 'opacity 0.4s';
        msg.style.opacity    = '0';
        setTimeout(() => msg.remove(), 400);
    });
}, 5000);
</script>

</body>
</html>