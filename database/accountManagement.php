<?php
session_start();

// check if user is logged in
if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit;
}

require_once "config.php";

// Determine which tab to display
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'accounts';

// ================= ACCOUNTS DELETE LOGIC =================
if (isset($_GET['delete'])) {

    $sql = "DELETE FROM tblaccounts WHERE username = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_GET['delete']);

        if (mysqli_stmt_execute($stmt)) {

            // insert logs
            $sql = "INSERT INTO tbllogs(datelog, timelog, action, module, performedby, performedto)
                    VALUES (?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete account";
                $module = "Accounts Management";

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $_SESSION['username'],
                    $_GET['delete']
                );
                mysqli_stmt_execute($stmt);
            }

            $_SESSION['success'] = "Account successfully deleted!";
            header("location: accountManagement.php?tab=accounts");
            exit;
        }
    }
}

// ================= TICKET DELETE LOGIC =================
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
    
    // Delete ticket
    $sqlDelete = "DELETE FROM tbltickets WHERE ticketNumber = ?";
    if ($stmtDelete = mysqli_prepare($link, $sqlDelete)) {
        mysqli_stmt_bind_param($stmtDelete, "s", $ticketToDelete);
        
        if (mysqli_stmt_execute($stmtDelete)) {
            // Log the deletion
            $action = 'deleted';
            $dateNow = date('m/d/Y g:i A');
            $details = 'Problem: ' . $ticketDetails['problem'] . ', Details: ' . $ticketDetails['details'];
            $logSql = "INSERT INTO tblticketlogs (ticketNumber, action, performedBy, datePerformed, details) 
                      VALUES (?, ?, ?, ?, ?)";
            
            if ($logStmt = mysqli_prepare($link, $logSql)) {
                mysqli_stmt_bind_param($logStmt, "sssss", $ticketToDelete, $action, $_SESSION['username'], $dateNow, $details);
                mysqli_stmt_execute($logStmt);
                mysqli_stmt_close($logStmt);
            }
            
            $_SESSION['success'] = "Ticket deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting ticket!";
        }
        mysqli_stmt_close($stmtDelete);
    }
    
    header("location: accountManagement.php?tab=tickets");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Management - Technical Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6f8;
            padding: 30px 20px;
            color: #2c3e50;
        }

        h1 {
            color: #1B2D42;
            margin-bottom: 5px;
            font-size: 28px;
            font-weight: 600;
        }

        h4 {
            color: #6b7280;
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: 400;
        }

        .nav-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .nav-link {
            text-decoration: none;
            color: #ffffff;
            font-weight: 600;
            padding: 11px 18px;
            border: none;
            border-radius: 4px;
            background: #1B2D42;
            transition: background 0.3s, transform 0.2s;
            cursor: pointer;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .nav-link:hover {
            background: #0f1619;
            transform: translateY(-1px);
        }

        .nav-link:active {
            transform: translateY(0);
        }

        #logoutLink {
            background: #8b5a5a;
        }

        #logoutLink:hover {
            background: #6b4545;
        }

        .tabs {
            display: flex;
            gap: 0;
            border-bottom: 2px solid #d1d5db;
            margin: 30px 0 0 0;
            background: white;
            border-radius: 4px 4px 0 0;
        }

        .tab-btn {
            background-color: #e5e7eb;
            border: none;
            padding: 14px 24px;
            cursor: pointer;
            font-weight: 600;
            border-radius: 0;
            transition: all 0.3s;
            font-size: 13px;
            letter-spacing: 0.5px;
            color: #6b7280;
        }

        .tab-btn:first-child {
            border-radius: 4px 0 0 0;
        }

        .tab-btn:hover {
            background-color: #d1d5db;
        }

        .tab-btn.active {
            background-color: #1B2D42;
            color: white;
            border-bottom: 3px solid #1B2D42;
        }

        .tab-content {
            display: none;
            background: white;
            padding: 30px;
            border-radius: 0 4px 4px 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .tab-content.active {
            display: block;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: white;
        }

        th, td {
            border: 1px solid #e5e7eb;
            padding: 14px;
            text-align: left;
        }

        th {
            background: #1B2D42;
            color: white;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-top: 20px;
        }

        .ticket-table th, .ticket-table td {
            border: 1px solid #e5e7eb;
            padding: 14px;
            text-align: left;
        }

        .ticket-table th {
            background-color: #1B2D42;
            color: white;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ticket-table tr:hover {
            background-color: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
        }

        .badge-hardware {
            background-color: #8b5a5a;
        }

        .badge-software {
            background-color: #5a5a8b;
        }

        .badge-connection {
            background-color: #5a8b8b;
        }

        .badge-pending {
            background-color: #8b8b5a;
        }

        .badge-completed {
            background-color: #5a8b5a;
        }

        .badge-inprogress {
            background-color: #6b7280;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-weight: 600;
        }

        .action-btn-view {
            background-color: #1B2D42;
            color: white;
        }

        .action-btn-view:hover {
            background-color: #0f1619;
        }

        .action-btn-edit {
            background-color: #6b7280;
            color: white;
        }

        .action-btn-edit:hover {
            background-color: #4b5563;
        }

        .action-btn-delete {
            background-color: #8b5a5a;
            color: white;
        }

        .action-btn-delete:hover {
            background-color: #6b4545;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            color: #1B2D42;
            font-size: 20px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #1B2D42;
        }

        .detail-row {
            margin-bottom: 18px;
            padding: 12px;
            background-color: #f9fafb;
            border-radius: 4px;
            border-left: 3px solid #1B2D42;
        }

        .detail-label {
            font-weight: 600;
            color: #1B2D42;
            margin-bottom: 6px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #4b5563;
            font-size: 14px;
        }

        .message {
            padding: 14px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .success {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
        }

        .error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #7f1d1d;
        }

        .search-section {
            margin-bottom: 25px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-section input {
            padding: 11px 14px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            flex: 1;
            min-width: 250px;
            font-size: 13px;
            transition: border-color 0.3s;
        }

        .search-section input:focus {
            outline: none;
            border-color: #1B2D42;
            box-shadow: 0 0 0 3px rgba(27, 45, 66, 0.1);
        }

        .search-section input[type="submit"] {
            background-color: #1B2D42;
            color: #ffffff;
            font-weight: 600;
            cursor: pointer;
            padding: 11px 20px;
            border: none;
            transition: background 0.3s;
            flex: 0;
        }

        .search-section input[type="submit"]:hover {
            background-color: #0f1619;
        }

        .no-records {
            padding: 30px;
            text-align: center;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            margin-top: 20px;
            color: #6b7280;
        }

        .add-new-btn {
            display: inline-block;
            background-color: #5a8b5a;
            color: white;
            padding: 11px 18px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 13px;
            letter-spacing: 0.5px;
            transition: background 0.3s;
        }

        .add-new-btn:hover {
            background-color: #4a6b4a;
        }
    </style>
</head>

<body>

<!-- SUCCESS MESSAGE -->
<?php
if (isset($_SESSION['success'])) {
    echo "<div class='message success'>
            {$_SESSION['success']}
          </div>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<div class='message error'>
            {$_SESSION['error']}
          </div>";
    unset($_SESSION['error']);
}
?>

<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
<h4>Your account type: <?= htmlspecialchars($_SESSION['usertype']) ?></h4>

<div class="nav-buttons">
    <a href="createAccount.php" id="createAccountLink">Create Account</a>
    <a href="equipmentManagement.php" id="equipmentLink">Equipment Management</a>
    <a href="accountManagement.php?tab=tickets" id="ticketLink">Ticket Management</a>
    <a href="logout.php" id="logoutLink">Logout</a>
</div>

<!-- TABS -->
<div class="tabs">
    <button class="tab-btn <?= $activeTab === 'accounts' ? 'active' : '' ?>" onclick="switchTab('accounts')">
        Accounts Management
    </button>
    <button class="tab-btn <?= $activeTab === 'tickets' ? 'active' : '' ?>" onclick="switchTab('tickets')">
        Ticket Management
    </button>
</div>

<!-- ACCOUNTS TAB -->
<div id="accounts" class="tab-content <?= $activeTab === 'accounts' ? 'active' : '' ?>">
    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=accounts" method="post">
        <div class="search-section">
            <input type="text" name="search" placeholder="Search by username or usertype">
            <input type="submit" name="btnsearch" value="Search">
        </div>
    </form>

    <?php
    function buildTable($result) {
        if (mysqli_num_rows($result) > 0) {
            echo "<table>";
            echo "<tr>
                    <th>Username</th>
                    <th>Usertype</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Date Created</th>
                    <th>Action</th>
                  </tr>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                echo "<td>" . htmlspecialchars($row['usertype']) . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . htmlspecialchars($row['createdby']) . "</td>";
                echo "<td>" . htmlspecialchars($row['datecreated']) . "</td>";
                echo "<td>
                        <div class='action-buttons'>
                            <a href='updateAccount.php?username=" . urlencode($row['username']) . "' class='action-btn action-btn-edit'>Update</a>
                            <a href='accountManagement.php?tab=accounts&delete=" . urlencode($row['username']) . "'
                               onclick=\"return confirm('Are you sure you want to delete this account?');\" class='action-btn action-btn-delete'>
                               Delete
                            </a>
                        </div>
                      </td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "<div class='no-records'><p>No records found.</p></div>";
        }
    }

    // ================= SEARCH / LOAD ACCOUNTS =================
    if (isset($_POST['btnsearch'])) {
        $sql = "SELECT * FROM tblaccounts
                WHERE username LIKE ? OR usertype LIKE ?
                ORDER BY username";

        if ($stmt = mysqli_prepare($link, $sql)) {
            $search = "%" . $_POST['search'] . "%";
            mysqli_stmt_bind_param($stmt, "ss", $search, $search);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            buildTable($result);
        }
    } else {
        $sql = "SELECT * FROM tblaccounts ORDER BY username";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            buildTable($result);
        }
    }
    ?>
</div>

<!-- TICKETS TAB -->
<div id="tickets" class="tab-content <?= $activeTab === 'tickets' ? 'active' : '' ?>">
    <?php
    // Get all tickets for the logged-in user
    $tickets = array();
    $searchQuery = '';

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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnTicketSearch'])) {
        $searchQuery = $_POST['ticketSearchInput'];
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
    ?>

    <div style="margin-bottom: 20px;">
        <a href="createTicket.php" style="background-color: #27ae60; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">+ Add New Ticket</a>
    </div>

    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=tickets">
        <div class="search-section">
            <input type="text" name="ticketSearchInput" placeholder="Search by Ticket Number, Problem, or Status..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit" name="btnTicketSearch" style="background-color: #6dadee; color: #000; font-weight: bold; cursor: pointer; padding: 8px 15px; border: 1px solid #000; border-radius: 4px;">Search</button>
            <?php if ($searchQuery): ?>
                <a href="accountManagement.php?tab=tickets" style="background-color: #6dadee; color: #000; font-weight: bold; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block;">Clear Search</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (count($tickets) > 0): ?>
        <table class="ticket-table">
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
    <?php else: ?>
        <div class="no-records">
            <?php if ($searchQuery): ?>
                <p>No tickets found matching your search.</p>
            <?php else: ?>
                <p>You have no tickets yet. <a href="createTicket.php" style="color: #2980b9; font-weight: bold;">Create one now</a></p>
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
            <button class="action-btn action-btn-view" onclick="closeModal('detailsModal')">Close</button>
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
                <button type="submit" class="action-btn action-btn-delete">Delete</button>
                <button type="button" class="action-btn action-btn-view" onclick="closeModal('deleteModal')">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        // Hide all tab contents
        const contents = document.querySelectorAll('.tab-content');
        contents.forEach(content => content.classList.remove('active'));
        
        // Show selected tab content
        document.getElementById(tabName).classList.add('active');
        
        // Update active button
        const buttons = document.querySelectorAll('.tab-btn');
        buttons.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        // Update URL
        window.history.pushState(null, '', '?tab=' + tabName);
    }

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
