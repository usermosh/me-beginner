<?php
session_start();
require_once "sessionChecker.php";
require_once "config.php";

// Check if user type is USER
if ($_SESSION['usertype'] !== 'USER') {
    header("location: accountManagement.php");
    exit;
}

// Get all logs for user's tickets
$logs = array();
$sql = "SELECT tl.*, tl.datePerformed as logDate 
        FROM tblticketlogs tl 
        WHERE tl.performedBy = ? 
        ORDER BY tl.createdAt DESC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $logs[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle clear logs (optional - you can implement this if needed)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnClearLogs'])) {
    // Only clear logs for this user if they want
    $clearSql = "DELETE FROM tblticketlogs WHERE performedBy = ?";
    
    if ($clearStmt = mysqli_prepare($link, $clearSql)) {
        mysqli_stmt_bind_param($clearStmt, "s", $_SESSION['username']);
        
        if (mysqli_stmt_execute($clearStmt)) {
            $_SESSION['success'] = "All logs have been cleared!";
        }
        mysqli_stmt_close($clearStmt);
    }
    
    header("location: ticketLogs.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ticket Logs - User</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1B2D42 0%, #0f1619 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .header h1 {
            color: #1B2D42;
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .back-link {
            background: linear-gradient(135deg, #1B2D42 0%, #132038 100%);
            color: #ffffff;
            padding: 10px 22px;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .back-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(27, 45, 66, 0.3);
        }

        .btn {
            padding: 10px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .btn-danger {
            background: linear-gradient(135deg, #8b5a5a 0%, #6b4444 100%);
            color: #ffffff;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(139, 90, 90, 0.3);
        }

        .message {
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid;
        }

        .success {
            background-color: #f0fdf4;
            border-left-color: #5a8b5a;
            color: #3a5a3a;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 13px 14px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        th {
            background: linear-gradient(135deg, #1B2D42 0%, #132038 100%);
            color: #ffffff;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        td {
            color: #333;
        }

        tr:hover {
            background-color: #f5f6f8;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: inline-block;
        }

        .badge-created {
            background: #f0fdf4;
            color: #3a5a3a;
            border: 1px solid #5a8b5a;
        }

        .badge-updated {
            background: #fffbf0;
            color: #654321;
            border: 1px solid #cc8f4a;
        }

        .badge-deleted {
            background: #fff5f5;
            color: #5a3a3a;
            border: 1px solid #8b5a5a;
        }

        .no-logs {
            text-align: center;
            color: #999;
            padding: 50px 20px;
            font-size: 16px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .modal-header h2 {
            margin: 0;
            color: #1B2D42;
            font-size: 22px;
            font-weight: 600;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #1B2D42;
        }

        .detail-row {
            margin-bottom: 15px;
            padding: 12px;
            background: #f5f6f8;
            border-radius: 6px;
            border-left: 3px solid #e5e7eb;
        }

        .detail-label {
            font-weight: 600;
            color: #1B2D42;
            margin-bottom: 6px;
            font-size: 12px;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }

        .detail-value {
            color: #555;
            word-wrap: break-word;
            white-space: pre-wrap;
            font-size: 13px;
            line-height: 1.5;
        }

        .info {
            background: #f0fdf4;
            border-left: 4px solid #5a8b5a;
            color: #3a5a3a;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .action-cell {
            max-width: 400px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Ticket Activity Logs</h1>
            <a href="../index.php" class="back-link">← Back to Dashboard</a>
        </div>

        <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='message success'>{$_SESSION['success']}</div>";
            unset($_SESSION['success']);
        }
        ?>

        <div class="info">
            <strong>Info:</strong> This page shows all create, update, and delete operations performed on your tickets.
        </div>

        <?php if (count($logs) > 0): ?>
            <div style="margin-bottom: 20px;">
                <form method="POST" style="display: inline;">
                    <button type="submit" name="btnClearLogs" class="btn btn-danger" onclick="return confirm('Are you sure you want to clear all logs? This action cannot be undone.');">Clear All Logs</button>
                </form>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket Number</th>
                            <th>Action</th>
                            <th>Performed By</th>
                            <th>Date</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($log['ticketNumber']) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($log['action']) ?>">
                                        <?= ucfirst(htmlspecialchars($log['action'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['performedBy']) ?></td>
                                <td><?= htmlspecialchars($log['datePerformed']) ?></td>
                                <td class="action-cell">
                                    <button type="button" class="btn" 
                                            onclick="viewLogDetails('<?= htmlspecialchars($log['ticketNumber']) ?>', 
                                                                     '<?= htmlspecialchars($log['action']) ?>', 
                                                                     '<?= htmlspecialchars($log['performedBy']) ?>', 
                                                                     '<?= htmlspecialchars($log['datePerformed']) ?>', 
                                                                     '<?= addslashes(htmlspecialchars($log['details'])) ?>')"
                                            style="background: #3498db; color: white; padding: 5px 10px; text-align: left;">
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-logs">
                <p>No activity logs yet. Create, update, or delete a ticket to see logs here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Log Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Log Details</h2>
                <button class="close-modal" onclick="closeModal('detailsModal')">×</button>
            </div>
            <div id="modalBody"></div>
            <div style="margin-top: 20px; text-align: right;">
                <button type="button" class="btn" onclick="closeModal('detailsModal')" style="background: #2980b9; color: white;">Close</button>
            </div>
        </div>
    </div>

    <script>
        function viewLogDetails(ticketNumber, action, performedBy, datePerformed, details) {
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">Ticket Number:</div>
                    <div class="detail-value">${ticketNumber}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Action:</div>
                    <div class="detail-value"><span class="badge badge-${action}">${action.charAt(0).toUpperCase() + action.slice(1)}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Performed By:</div>
                    <div class="detail-value">${performedBy}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date & Time:</div>
                    <div class="detail-value">${datePerformed}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Details:</div>
                    <div class="detail-value">${details}</div>
                </div>
            `;
            openModal('detailsModal');
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal.active');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
