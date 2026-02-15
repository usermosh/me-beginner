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
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #2c3e50;
            margin: 0;
        }

        .back-link {
            background: #95a5a6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            transition: background 0.3s;
        }

        .back-link:hover {
            background: #7f8c8d;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .success {
            background: #c8f7c5;
            border: 1px solid #4caf50;
            color: #2d662d;
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
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #34495e;
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-created {
            background: #27ae60;
            color: white;
        }

        .badge-updated {
            background: #f39c12;
            color: white;
        }

        .badge-deleted {
            background: #e74c3c;
            color: white;
        }

        .no-logs {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
            font-size: 18px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }

        .modal-header h2 {
            margin: 0;
            color: #2c3e50;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #2c3e50;
        }

        .detail-row {
            margin-bottom: 15px;
            padding: 10px;
            background: #ecf0f1;
            border-radius: 4px;
        }

        .detail-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #555;
            word-wrap: break-word;
            white-space: pre-wrap;
        }

        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 12px;
            border-radius: 4px;
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
