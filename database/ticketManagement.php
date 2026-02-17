<?php
session_start();
require_once "sessionChecker.php";
require_once "config.php";

// Check if user type is USER
if ($_SESSION['usertype'] !== 'USER') {
    header("location: accountManagement.php");
    exit;
}

$searchQuery = '';
$tickets = array();

// Get all tickets for the logged-in user
$sql = "SELECT * FROM tbltickets WHERE createdBy = ? ORDER BY dateCreated DESC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $tickets[] = $row;
        }
        mysqli_stmt_close($stmt);
    }
}

// Filter tickets based on search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSearch'])) {
    $searchQuery = $_POST['searchInput'];
    $filteredTickets = array();
    
    foreach ($tickets as $ticket) {
        if (
            stripos($ticket['ticketNumber'], $searchQuery) !== false ||
            stripos($ticket['problem'], $searchQuery) !== false ||
            stripos($ticket['status'], $searchQuery) !== false
        ) {
            $filteredTickets[] = $ticket;
        }
    }
    $tickets = $filteredTickets;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticketToDelete'])) {
    $ticketToDelete = $_POST['ticketToDelete'];
    
    // Get current ticket details for log
    $sqlGet = "SELECT * FROM tbltickets WHERE ticketNumber = ?";
    if ($stmtGet = mysqli_prepare($link, $sqlGet)) {
        mysqli_stmt_bind_param($stmtGet, "s", $ticketToDelete);
        mysqli_stmt_execute($stmtGet);
        $resultGet = mysqli_stmt_get_result($stmtGet);
        $ticketDetails = mysqli_fetch_array($resultGet, MYSQLI_ASSOC);
        mysqli_stmt_close($stmtGet);
    }
    
    // Log the deletion BEFORE deleting (to satisfy FK constraint)
    if ($ticketDetails) {
        $action = 'deleted';
        $dateNow = date('m/d/Y g:i A');
        $details = 'Problem: ' . $ticketDetails['problem'] . ', Details: ' . $ticketDetails['details'];
        $logSql = "INSERT INTO tblticketlogs (ticketNumber, action, performedBy, datePerformed, details) 
                  VALUES (?, ?, ?, ?, ?)";
        
        if ($logStmt = mysqli_prepare($link, $logSql)) {
            mysqli_stmt_bind_param($logStmt, "sssss", $ticketToDelete, $action, $_SESSION['username'], $dateNow, $details);
            @mysqli_stmt_execute($logStmt);
            mysqli_stmt_close($logStmt);
        }
    }
    
    // Delete all logs related to this ticket
    $sqlDeleteLogs = "DELETE FROM tblticketlogs WHERE ticketNumber = ?";
    if ($stmtLogs = mysqli_prepare($link, $sqlDeleteLogs)) {
        mysqli_stmt_bind_param($stmtLogs, "s", $ticketToDelete);
        @mysqli_stmt_execute($stmtLogs);
        mysqli_stmt_close($stmtLogs);
    }
    
    // Delete ticket
    $sqlDelete = "DELETE FROM tbltickets WHERE ticketNumber = ?";
    if ($stmtDelete = mysqli_prepare($link, $sqlDelete)) {
        mysqli_stmt_bind_param($stmtDelete, "s", $ticketToDelete);
        
        if (mysqli_stmt_execute($stmtDelete)) {
            $_SESSION['success'] = "Ticket deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting ticket!";
        }
        mysqli_stmt_close($stmtDelete);
    }
    
    header("location: ticketManagement.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ticket Management - User</title>
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

        .header-buttons {
            display: flex;
            gap: 10px;
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

        .btn-primary {
            background: #2980b9;
            color: white;
        }

        .btn-primary:hover {
            background: #1f6391;
        }

        .btn-success {
            background: #27ae60;
            color: white;
        }

        .btn-success:hover {
            background: #1e8449;
        }

        .btn-warning {
            background: #f39c12;
            color: white;
        }

        .btn-warning:hover {
            background: #d68910;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
        }

        .btn-info {
            background: #3498db;
            color: white;
        }

        .btn-info:hover {
            background: #2980b9;
        }

        .back-link {
            background: #95a5a6;
            color: white;
        }

        .back-link:hover {
            background: #7f8c8d;
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

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .search-section {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .search-section input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            flex: 1;
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

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background 0.3s;
        }

        .action-btn-edit {
            background: #f39c12;
            color: white;
        }

        .action-btn-edit:hover {
            background: #d68910;
        }

        .action-btn-delete {
            background: #e74c3c;
            color: white;
        }

        .action-btn-delete:hover {
            background: #c0392b;
        }

        .action-btn-view {
            background: #3498db;
            color: white;
        }

        .action-btn-view:hover {
            background: #2980b9;
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
        }

        .no-tickets {
            text-align: center;
            color: #7f8c8d;
            padding: 40px;
            font-size: 18px;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-pending {
            background: #f39c12;
            color: white;
        }

        .badge-assigned {
            background: #3498db;
            color: white;
        }

        .badge-completed {
            background: #27ae60;
            color: white;
        }

        .badge-approved {
            background: #2c3e50;
            color: white;
        }

        .badge-hardware {
            background: #e74c3c;
            color: white;
        }

        .badge-software {
            background: #9b59b6;
            color: white;
        }

        .badge-connection {
            background: #1abc9c;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Tickets</h1>
            <div class="header-buttons">
                <a href="../index.php" class="btn back-link">← Back to Dashboard</a>
                <a href="createTicket.php" class="btn btn-success">+ Add New Ticket</a>
            </div>
        </div>

        <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='message success'>{$_SESSION['success']}</div>";
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo "<div class='message error'>{$_SESSION['error']}</div>";
            unset($_SESSION['error']);
        }
        ?>

        <div class="search-section">
            <form method="POST" style="display: flex; gap: 10px; flex: 1;">
                <input type="text" name="searchInput" placeholder="Search by Ticket Number, Problem, or Status..." 
                       value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" name="btnSearch" class="btn btn-primary">Search</button>
                <?php if ($searchQuery): ?>
                    <a href="ticketManagement.php" class="btn btn-primary">Clear Search</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (count($tickets) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket Number</th>
                            <th>Problem</th>
                            <th>Date Created</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($ticket['ticketNumber']) ?></strong></td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($ticket['problem']) ?>">
                                        <?= ucfirst(htmlspecialchars($ticket['problem'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($ticket['dateCreated']) ?></td>
                                <td>
                                    <span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>">
                                        <?= ucfirst(htmlspecialchars($ticket['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn action-btn-view" 
                                                onclick="viewDetails('<?= htmlspecialchars($ticket['ticketNumber']) ?>', 
                                                                      '<?= htmlspecialchars($ticket['problem']) ?>', 
                                                                      '<?= htmlspecialchars($ticket['status']) ?>', 
                                                                      '<?= htmlspecialchars($ticket['details']) ?>', 
                                                                      '<?= htmlspecialchars($ticket['dateCreated']) ?>', 
                                                                      '<?= htmlspecialchars($ticket['assignedTo'] ?? '-') ?>', 
                                                                      '<?= htmlspecialchars($ticket['dateAssigned'] ?? '-') ?>', 
                                                                      '<?= htmlspecialchars($ticket['dateCompleted'] ?? '-') ?>', 
                                                                      '<?= htmlspecialchars($ticket['approvedBy'] ?? '-') ?>', 
                                                                      '<?= htmlspecialchars($ticket['dateApproved'] ?? '-') ?>')">
                                            Details
                                        </button>
                                        <a href="updateTicket.php?ticketNumber=<?= htmlspecialchars($ticket['ticketNumber']) ?>" 
                                           class="action-btn action-btn-edit">Update</a>
                                        <button class="action-btn action-btn-delete" 
                                                onclick="confirmDelete('<?= htmlspecialchars($ticket['ticketNumber']) ?>')">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-tickets">
                <?php if ($searchQuery): ?>
                    <p>No tickets found matching your search.</p>
                <?php else: ?>
                    <p>You have no tickets yet. <a href="createTicket.php" style="color: #2980b9;">Create one now</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Details Modal -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Ticket Details</h2>
                <button class="close-modal" onclick="closeModal('detailsModal')">×</button>
            </div>
            <div id="modalBody"></div>
            <div style="margin-top: 20px; text-align: right;">
                <button class="btn btn-primary" onclick="closeModal('detailsModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Confirm Delete</h2>
                <button class="close-modal" onclick="closeModal('deleteModal')">×</button>
            </div>
            <p>Are you sure you want to delete this ticket? This action cannot be undone.</p>
            <div style="margin-top: 20px; text-align: right;">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="ticketToDelete" id="ticketToDeleteInput">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-primary" onclick="closeModal('deleteModal')">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function viewDetails(ticketNumber, problem, status, details, dateCreated, assignedTo, dateAssigned, dateCompleted, approvedBy, dateApproved) {
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="detail-row">
                    <div class="detail-label">Ticket Number:</div>
                    <div class="detail-value">${ticketNumber}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Problem:</div>
                    <div class="detail-value"><span class="badge badge-${problem}">${problem.charAt(0).toUpperCase() + problem.slice(1)}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Details:</div>
                    <div class="detail-value">${details}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value"><span class="badge badge-${status}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date Created:</div>
                    <div class="detail-value">${dateCreated}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Assigned To:</div>
                    <div class="detail-value">${assignedTo}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date Assigned:</div>
                    <div class="detail-value">${dateAssigned}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date Completed:</div>
                    <div class="detail-value">${dateCompleted}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Approved By:</div>
                    <div class="detail-value">${approvedBy}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Date Approved:</div>
                    <div class="detail-value">${dateApproved}</div>
                </div>
            `;
            openModal('detailsModal');
        }

        function confirmDelete(ticketNumber) {
            document.getElementById('ticketToDeleteInput').value = ticketNumber;
            openModal('deleteModal');
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
