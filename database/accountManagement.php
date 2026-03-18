<?php

require_once "sessionChecker.php";
require_once "config.php";

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
            header("location: accountManagement.php");
            exit;
        }
    }
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
        <a href="index.php" class="nav-link" id="dashboardLink">
            <span class="nav-icon">🏠</span>
            <span>Dashboard</span>
        </a>
        <a href="logout.php" class="nav-link" id="logoutLink">
            <span class="nav-icon">🚪</span>
            <span>Logout</span>
        </a>
    </div>
</div>

<!-- ACCOUNTS CONTENT -->
<div id="accounts" class="tab-content active">
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

<script>
    function openModal(id)  { document.getElementById(id).classList.add('active'); }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }
    window.onclick = function(e) {
        document.querySelectorAll('.modal.active').forEach(m => {
            if (e.target === m) m.classList.remove('active');
        });
    }
</script>

</body>
</html>