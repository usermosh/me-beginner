<?php
require_once "sessionChecker.php";
require_once "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment Logs - Technical Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1B2D42 0%, #0f1619 100%);
            padding: 30px 20px;
            min-height: 100vh;
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

        h1 {
            margin: 0;
            color: #1B2D42;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .header-links {
            display: flex;
            gap: 12px;
        }

        #backLink, #accountLink, #logoutLink {
            text-decoration: none;
            color: #ffffff;
            font-weight: 600;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        #backLink {
            background: linear-gradient(135deg, #1B2D42 0%, #132038 100%);
        }

        #backLink:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(27, 45, 66, 0.3);
        }

        #accountLink {
            background: linear-gradient(135deg, #1B2D42 0%, #132038 100%);
        }

        #accountLink:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(27, 45, 66, 0.3);
        }

        #logoutLink {
            background: linear-gradient(135deg, #8b5a5a 0%, #6b4444 100%);
        }

        #logoutLink:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(139, 90, 90, 0.3);
        }

        .message {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
            border-left: 4px solid;
        }

        .success {
            background-color: #f0fdf4;
            border-left-color: #5a8b5a;
            color: #3a5a3a;
        }

        .error {
            background-color: #fff5f5;
            border-left-color: #8b5a5a;
            color: #5a3a3a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #ffffff;
        }

        table thead {
            background: linear-gradient(135deg, #1B2D42 0%, #132038 100%);
            color: #ffffff;
        }

        table th {
            padding: 14px;
            text-align: left;
            border: none;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        table td {
            padding: 12px 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            color: #333;
        }

        table tbody tr {
            transition: background-color 0.2s ease;
        }

        table tbody tr:hover {
            background-color: #f5f6f8;
        }

        table tbody tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .no-logs {
            text-align: center;
            padding: 40px;
            color: #999;
            background-color: #f5f6f8;
            margin-top: 20px;
            border-radius: 8px;
            font-size: 15px;
        }

        .log-details {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Equipment Management Logs</h1>
        <div class="header-links">
            <a href="equipmentManagement.php" id="backLink">Back to Equipment</a>
            <a href="accountManagement.php" id="accountLink">Accounts</a>
            <a href="logout.php" id="logoutLink">Logout</a>
        </div>
    </div>

    <!-- SESSION MESSAGES -->
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

    <!-- LOGS TABLE -->
    <?php
    $sql = "SELECT * FROM tblequipmentlogs ORDER BY createdAt DESC";
    $result = mysqli_query($link, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Date</th>";
        echo "<th>Time</th>";
        echo "<th>Action</th>";
        echo "<th>Module</th>";
        echo "<th>Performed By</th>";
        echo "<th>Asset Number</th>";
        echo "<th>Details</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['datelog']) . "</td>";
            echo "<td>" . htmlspecialchars($row['timelog']) . "</td>";
            echo "<td>" . htmlspecialchars($row['action']) . "</td>";
            echo "<td>" . htmlspecialchars($row['module']) . "</td>";
            echo "<td>" . htmlspecialchars($row['performedby']) . "</td>";
            echo "<td>" . htmlspecialchars($row['assetNumber'] ?: 'N/A') . "</td>";
            echo "<td class='log-details'>";
            if (!empty($row['changeDetails'])) {
                echo htmlspecialchars($row['changeDetails']);
            } else {
                echo "No additional details";
            }
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='no-logs'>No logs found.</div>";
    }
    ?>

</body>
</html>
