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
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            background-color: #bbd8f5;
        }

        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .nav-link {
            text-decoration: none;
            color: #000;
            font-weight: bold;
            padding: 8px 15px;
            border: 1px solid #000;
            border-radius: 10px;
            background-color: #99d4ff;
            transition: background-color 0.3s;
        }

        .nav-link:hover {
            background-color: #6dadee;
        }

        .nav-link.active {
            background-color: #6dadee;
        }

        #createAccountLink {
            background-color: #6dadee;
        }
        #equipmentLink {
            background-color: #99d4ff;
        }
        #ticketLink {
            background-color: #99d4ff;
        }
        #logoutLink {
            background-color: #f86f6f;
        }
        #createAccountLink,
        #equipmentLink,
        #ticketLink,
        #logoutLink {
            text-decoration: none;
            color: #000;
            font-weight: bold;
            padding: 6px 10px;
            border: 1px solid #000;
            border-radius: 10px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            border-bottom: 2px solid #000;
            margin: 20px 0;
        }

        .tab-btn {
            background-color: #e0e0e0;
            border: 1px solid #000;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px 5px 0 0;
            transition: background-color 0.3s;
        }

        .tab-btn:hover {
            background-color: #d0d0d0;
        }

        .tab-btn.active {
            background-color: #bbd8f5;
            border-bottom: 2px solid #bbd8f5;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #f5f5f5;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
        }
        th {
            background: #bbbaba;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f5f5f5;
            margin-top: 20px;
        }

        .ticket-table th, .ticket-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: left;
        }

        .ticket-table th {
            background-color: #bbbaba;
            font-weight: bold;
        }

        .ticket-table tr:hover {
            background-color: #e8e8e8;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .badge-hardware {
            background-color: #e74c3c;
        }

        .badge-software {
            background-color: #9b59b6;
        }

        .badge-connection {
            background-color: #1abc9c;
        }

        .badge-pending {
            background-color: #f39c12;
        }

        .badge-completed {
            background-color: #27ae60;
        }

        .badge-inprogress {
            background-color: #3498db;
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
            text-decoration: none;
            display: inline-block;
            transition: background 0.3s;
        }

        .action-btn-view {
            background-color: #3498db;
            color: white;
        }

        .action-btn-view:hover {
            background-color: #2980b9;
        }

        .action-btn-edit {
            background-color: #f39c12;
            color: white;
        }

        .action-btn-edit:hover {
            background-color: #d68910;
        }

        .action-btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        .action-btn-delete:hover {
            background-color: #c0392b;
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
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
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
            background-color: #f5f5f5;
            border-radius: 4px;
        }

        .detail-label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #34495e;
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
            align-items: center;
            flex-wrap: wrap;
        }

        .search-section input {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            flex: 1;
            min-width: 250px;
        }

        .search-section input[type="submit"] {
            background-color: #6dadee;
            color: #000;
            font-weight: bold;
            cursor: pointer;
            padding: 8px 15px;
        }

        .no-records {
            padding: 20px;
            text-align: center;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 20px;
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
