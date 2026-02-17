<?php

require_once "sessionChecker.php";
require_once "config.php";

// Determine which tab to display
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'accounts';

// ================= ACCOUNTS DELETE LOGIC =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accountToDelete'])) {
    $accountToDelete = $_POST['accountToDelete'];
    
    $sql = "DELETE FROM tblaccounts WHERE username = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $accountToDelete);

        if (mysqli_stmt_execute($stmt)) {

            // insert logs (non-blocking - continue even if logging fails)
            $sql = "INSERT INTO tbllogs(datelog, timelog, action, module, performedby, performedto)
                    VALUES (?, ?, ?, ?, ?, ?)";

            if ($logStmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete account";
                $module = "Accounts Management";

                mysqli_stmt_bind_param(
                    $logStmt,
                    "ssssss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $_SESSION['username'],
                    $accountToDelete
                );
                @mysqli_stmt_execute($logStmt);
                mysqli_stmt_close($logStmt);
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
    
    header("location: accountManagement.php?tab=tickets");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management - Technical Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f1d3d 0%, #1a2e5e 50%, #0d1a33 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: #333;
            padding: 30px 20px;
        }

        /* Header Section */
        .header-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        h1 {
            color: #ffffff;
            font-size: 28px;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        h4 {
            color: #a0c4ff;
            font-size: 14px;
            font-weight: 400;
        }

        /* Navigation Buttons - Professional Layout */
        .nav-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-label {
            color: #a0c4ff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .nav-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #ffffff;
            font-weight: 500;
            padding: 14px 20px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s ease;
            font-size: 14px;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .nav-icon {
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }

        /* Specific button colors - subtle accent on left border */
        #createAccountLink {
            border-left: 3px solid #27ae60;
        }

        #equipmentLink {
            border-left: 3px solid #3498db;
        }

        #ticketLink {
            border-left: 3px solid #9b59b6;
        }

        #logoutLink {
            border-left: 3px solid #e74c3c;
        }

        #createAccountLink:hover {
            background: rgba(39, 174, 96, 0.15);
        }

        #equipmentLink:hover {
            background: rgba(52, 152, 219, 0.15);
        }

        #ticketLink:hover {
            background: rgba(155, 89, 182, 0.15);
        }

        #logoutLink:hover {
            background: rgba(231, 76, 60, 0.15);
        }

        .tabs {
            display: flex;
            gap: 0;
            border-bottom: none;
            margin: 30px 0;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            padding: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .tab-btn {
            background-color: transparent;
            border: none;
            padding: 14px 25px;
            cursor: pointer;
            font-weight: 600;
            color: #a0c4ff;
            transition: all 0.3s ease;
            border-radius: 8px;
            flex: 1;
            text-align: center;
        }

        .tab-btn:hover {
            background-color: rgba(42, 82, 152, 0.2);
            color: #ffffff;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.4);
        }

        .tab-content {
            display: none;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.4s ease-out;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #ffffff;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 14px;
            text-align: left;
        }

        th {
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
            color: #ffffff;
            font-weight: 600;
        }

        tr:hover {
            background-color: #f0f8ff;
        }

        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
            margin-top: 20px;
        }

        .ticket-table th, .ticket-table td {
            border: 1px solid #ddd;
            padding: 14px;
            text-align: left;
        }

        .ticket-table th {
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
            color: #ffffff;
            font-weight: 600;
        }

        .ticket-table tr:hover {
            background-color: #f0f8ff;
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }

        .badge-hardware {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        .badge-software {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
        }

        .badge-connection {
            background: linear-gradient(135deg, #1abc9c, #0a8860);
        }

        .badge-pending {
            background: linear-gradient(135deg, #f39c12, #d68910);
        }

        .badge-completed {
            background: linear-gradient(135deg, #27ae60, #1e8449);
        }

        .badge-inprogress {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

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
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .action-btn-view {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }

        .action-btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }

        .action-btn-edit {
            background: linear-gradient(135deg, #f39c12, #d68910);
            color: white;
        }

        .action-btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.4);
        }

        .action-btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .action-btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
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
            backdrop-filter: blur(4px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #1a3a7e;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            margin: 0;
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 28px;
            cursor: pointer;
            color: #1a3a7e;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            color: #e74c3c;
        }

        .detail-row {
            margin-bottom: 18px;
            padding: 15px;
            background: linear-gradient(135deg, #f0f8ff, #ffffff);
            border-radius: 8px;
            border-left: 4px solid #2a5298;
        }

        .detail-label {
            font-weight: 700;
            color: #1a3a7e;
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #34495e;
            font-size: 14px;
        }

        .message {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid;
            font-weight: 600;
            animation: slideDown 0.4s ease-out;
        }

        .success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-color: #27ae60;
            color: #155724;
        }

        .error {
            background: linear-gradient(135deg, #f8d7da, #f5c2c7);
            border-color: #e74c3c;
            color: #721c24;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .search-section {
            margin-bottom: 25px;
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(53, 122, 189, 0.1));
            padding: 16px;
            border-radius: 10px;
            border: 1px solid rgba(42, 82, 152, 0.2);
        }

        .search-section input {
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            flex: 1;
            min-width: 250px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-section input:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }

        .search-section input[type="submit"],
        .search-section button {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }

        .search-section input[type="submit"]:hover,
        .search-section button:hover,
        .search-section a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(42, 82, 152, 0.5);
        }

        .search-section a {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: #fff;
            font-weight: 600;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 8px;
            display: inline-block;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(127, 140, 141, 0.3);
        }

        .no-records {
            padding: 30px;
            text-align: center;
            background: linear-gradient(135deg, #f0f8ff, #ffffff);
            border: 2px dashed #2a5298;
            border-radius: 10px;
            margin-top: 20px;
            color: #1a3a7e;
            font-weight: 600;
        }

        .no-records a {
            color: #4a90e2;
            text-decoration: none;
            font-weight: 700;
        }

        .no-records a:hover {
            text-decoration: underline;
        }

        .add-new-btn {
            background: linear-gradient(135deg, #27ae60, #1e8449);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .add-new-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.5);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-buttons {
                grid-template-columns: 1fr;
            }

            .header-section {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }

            .nav-container {
                padding: 15px;
            }
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

<!-- Header Section -->
<div class="header-section">
    <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    <h4>Account Type: <?= htmlspecialchars($_SESSION['usertype']) ?></h4>
</div>

<!-- Navigation Container -->
<div class="nav-container">
    <div class="nav-label">Quick Actions</div>
    <div class="nav-buttons">
        <a href="createAccount.php" class="nav-link" id="createAccountLink">
            <span class="nav-icon">👤</span>
            <span>Create Account</span>
        </a>
        <a href="equipmentManagement.php" class="nav-link" id="equipmentLink">
            <span class="nav-icon">⚙️</span>
            <span>Equipment Management</span>
        </a>
        <a href="accountManagement.php?tab=tickets" class="nav-link" id="ticketLink">
            <span class="nav-icon">🎫</span>
            <span>Ticket Management</span>
        </a>
        <a href="logout.php" class="nav-link" id="logoutLink">
            <span class="nav-icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
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
                            <button class='action-btn action-btn-delete'
                                    onclick=\"confirmDeleteAccount('" . htmlspecialchars($row['username']) . "')\">
                                Delete
                            </button>
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
        <a href="createTicket.php" class="add-new-btn">+ Add New Ticket</a>
    </div>

    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>?tab=tickets">
        <div class="search-section">
            <input type="text" name="ticketSearchInput" placeholder="Search by Ticket Number, Problem, or Status..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit" name="btnTicketSearch">Search</button>
            <?php if ($searchQuery): ?>
                <a href="accountManagement.php?tab=tickets">Clear Search</a>
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

<!-- Delete Confirmation Modal for Tickets -->
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

<!-- Delete Confirmation Modal for Accounts -->
<div class="modal" id="deleteAccountModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button class="close-modal" onclick="closeModal('deleteAccountModal')">×</button>
        </div>
        <p>Are you sure you want to delete this account? This action cannot be undone.</p>
        <div style="margin-top: 20px; text-align: right;">
            <form method="POST" style="display: inline;">
                <input type="hidden" name="accountToDelete" id="accountToDeleteInput">
                <button type="submit" class="action-btn action-btn-delete">Delete</button>
                <button type="button" class="action-btn action-btn-view" onclick="closeModal('deleteAccountModal')">Cancel</button>
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

    function confirmDeleteAccount(username) {
        document.getElementById('accountToDeleteInput').value = username;
        openModal('deleteAccountModal');
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