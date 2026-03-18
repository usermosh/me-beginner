<?php
require_once "sessionChecker.php";
require_once "config.php";

// Check if user type is USER
if ($_SESSION['usertype'] !== 'USER') {
    header("location: index.php");
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
    
    $sqlGet = "SELECT * FROM tbltickets WHERE ticketNumber = ?";
    if ($stmtGet = mysqli_prepare($link, $sqlGet)) {
        mysqli_stmt_bind_param($stmtGet, "s", $ticketToDelete);
        mysqli_stmt_execute($stmtGet);
        $resultGet = mysqli_stmt_get_result($stmtGet);
        $ticketDetails = mysqli_fetch_array($resultGet, MYSQLI_ASSOC);
        mysqli_stmt_close($stmtGet);
    }

    // Only allow delete for tickets belonging to this user
    if (!$ticketDetails) {
        $_SESSION['error'] = "Ticket not found.";
        header("location: ticketManagement.php");
        exit;
    }
    
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
    
    $sqlDeleteLogs = "DELETE FROM tblticketlogs WHERE ticketNumber = ?";
    if ($stmtLogs = mysqli_prepare($link, $sqlDeleteLogs)) {
        mysqli_stmt_bind_param($stmtLogs, "s", $ticketToDelete);
        @mysqli_stmt_execute($stmtLogs);
        mysqli_stmt_close($stmtLogs);
    }
    
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - Technical Management System</title>

    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f1d3d 0%, #1a2e5e 50%, #0d1a33 100%);
            background-attachment: fixed;
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container { max-width: 1400px; margin: 0 auto; }

        /* Header */
        .header-section {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(142,68,173,0.3);
        }

        .header-icon svg {
            width: 28px; height: 28px;
            stroke: white; stroke-width: 2;
        }

        .header-text h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .header-text p {
            color: #a0c4ff;
            font-size: 13px;
        }

        .header-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn svg { width: 18px; height: 18px; }

        .btn-back {
            background: rgba(255,255,255,0.1);
            color: #ffffff;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }

        .btn-add {
            background: linear-gradient(135deg, #27ae60, #1a7a40);
            color: white;
            box-shadow: 0 4px 15px rgba(39,174,96,0.3);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39,174,96,0.4);
        }

        /* Messages */
        .message {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity:0; transform:translateY(-10px); }
            to { opacity:1; transform:translateY(0); }
        }

        .message svg { width:22px; height:22px; }

        .success {
            background: linear-gradient(135deg,#d4edda,#c3e6cb);
            border-color:#27ae60; color:#155724;
        }
        .success svg { stroke:#27ae60; }

        .error {
            background: linear-gradient(135deg,#f8d7da,#f5c2c7);
            border-color:#e74c3c; color:#721c24;
        }
        .error svg { stroke:#e74c3c; }

        /* Search */
        .search-section {
            background: rgba(255,255,255,0.95);
            padding: 20px 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .search-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-controls input {
            flex: 1;
            min-width: 300px;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-controls input:focus {
            outline: none;
            border-color: #8e44ad;
            box-shadow: 0 0 0 4px rgba(142,68,173,0.1);
        }

        .btn-search {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            color: white;
            box-shadow: 0 4px 15px rgba(142,68,173,0.3);
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(142,68,173,0.4);
        }

        .btn-clear {
            background: rgba(0,0,0,0.05);
            color: #555;
            border: 2px solid #ddd;
        }

        .btn-clear:hover {
            background: rgba(0,0,0,0.08);
        }

        /* Table */
        .table-container {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
            color: #ffffff;
        }

        table th {
            padding: 16px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s ease;
        }

        table tbody tr:hover { background-color: #f0f8ff; }

        table td {
            padding: 14px 16px;
            color: #2c3e50;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .badge-pending { background:#fff3cd; color:#856404; }
        .badge-inprogress { background:#cfe2ff; color:#084298; }
        .badge-completed { background:#d1e7dd; color:#0a3622; }

        .badge-hardware { background:#f8d7da; color:#721c24; }
        .badge-software { background:#e7d6f5; color:#4a235a; }
        .badge-connection { background:#d1f2eb; color:#0a3622; }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .action-btn-view {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .action-btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52,152,219,0.4);
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #f39c12, #d68910);
            color: white;
        }

        .action-btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243,156,18,0.4);
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .action-btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231,76,60,0.4);
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(4px);
        }

        .modal.active { display: flex; }

        .modal-content {
            background: rgba(255,255,255,0.98);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #8e44ad;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            color: #8e44ad;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #8e44ad;
        }

        .detail-row {
            margin-bottom: 18px;
            padding: 15px;
            background: linear-gradient(135deg, #f0f8ff, #ffffff);
            border-radius: 8px;
            border-left: 4px solid #8e44ad;
        }

        .detail-label {
            font-weight: 700;
            color: #6c3483;
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .detail-value {
            color: #34495e;
            font-size: 14px;
        }

        .no-tickets {
            text-align: center;
            padding: 60px 20px;
        }

        .no-tickets svg {
            width: 64px; height: 64px;
            stroke: #8e44ad; stroke-width: 2;
            margin-bottom: 20px;
        }

        .no-tickets h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .no-tickets p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .no-tickets a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .no-tickets a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(142,68,173,0.4);
        }

        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .search-controls {
                flex-direction: column;
            }

            .search-controls input {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <div class="header-left">
            <div class="header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2v6h6M16 13H8m8 4H8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="header-text">
                <h1>My Tickets</h1>
                <p>Manage your technical support requests</p>
            </div>
        </div>
        <div class="header-buttons">
            <a href="index.php" class="btn btn-back">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Dashboard
            </a>
            <a href="createTicket.php" class="btn btn-add">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                New Ticket
            </a>
        </div>
    </div>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<div class='message success'>
                <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                    <path d='M22 11.08V12a10 10 0 1 1-5.93-9.14' stroke-linecap='round' stroke-linejoin='round'/>
                    <path d='M22 4 12 14.01l-3-3' stroke-linecap='round' stroke-linejoin='round'/>
                </svg>
                {$_SESSION['success']}
              </div>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<div class='message error'>
                <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                    <circle cx='12' cy='12' r='10' stroke-linecap='round' stroke-linejoin='round'/>
                    <path d='M15 9l-6 6M9 9l6 6' stroke-linecap='round' stroke-linejoin='round'/>
                </svg>
                {$_SESSION['error']}
              </div>";
        unset($_SESSION['error']);
    }
    ?>

    <div class="search-section">
        <form method="POST">
            <div class="search-controls">
                <input type="text" name="searchInput" 
                       placeholder="Search by Ticket Number, Problem, or Status..." 
                       value="<?= htmlspecialchars($searchQuery) ?>">
                <button type="submit" name="btnSearch" class="btn btn-search">Search</button>
                <?php if ($searchQuery): ?>
                    <a href="ticketManagement.php" class="btn btn-clear">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (count($tickets) > 0): ?>
    <div class="table-container">
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
    <div class="table-container">
        <div class="no-tickets">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2v6h6M12 18v-6M9 15h6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3>
                <?php if ($searchQuery): ?>
                    No tickets found matching your search
                <?php else: ?>
                    No Tickets Yet
                <?php endif; ?>
            </h3>
            <p>
                <?php if ($searchQuery): ?>
                    Try different search keywords or clear the search filter.
                <?php else: ?>
                    You haven't created any support tickets yet.
                <?php endif; ?>
            </p>
            <?php if (!$searchQuery): ?>
                <a href="createTicket.php">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Create Your First Ticket
                </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modals -->
<div class="modal" id="detailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Ticket Details</h2>
            <button class="close-modal" onclick="closeModal('detailsModal')">×</button>
        </div>
        <div id="modalBody"></div>
        <div style="margin-top: 20px; text-align: right;">
            <button class="btn btn-search" onclick="closeModal('detailsModal')">Close</button>
        </div>
    </div>
</div>

<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button class="close-modal" onclick="closeModal('deleteModal')">×</button>
        </div>
        <p style="padding: 20px 0;">Are you sure you want to delete this ticket? This action cannot be undone.</p>
        <div style="margin-top: 20px; text-align: right;">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="ticketToDelete" id="ticketToDeleteInput">
                <button type="submit" class="btn" style="background:#e74c3c; color:white;">Delete</button>
                <button type="button" class="btn btn-clear" onclick="closeModal('deleteModal')">Cancel</button>
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
            <div class="detail-value"><span class="badge badge-${problem}">${problem}</span></div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Details:</div>
            <div class="detail-value">${details}</div>
        </div>
        <div class="detail-row">
            <div class="detail-label">Status:</div>
            <div class="detail-value"><span class="badge badge-${status}">${status}</span></div>
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

window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal.active');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
}

setTimeout(() => {
    document.querySelectorAll('.message').forEach(msg => {
        msg.style.transition = 'opacity 0.4s';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 400);
    });
}, 5000);
</script>

</body>
</html>