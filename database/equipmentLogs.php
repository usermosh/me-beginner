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
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            background-color: #bbd8f5;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        h1 {
            margin: 0;
            color: #333;
        }
        
        .header-links {
            display: flex;
            gap: 10px;
        }
        
        #backLink, #accountLink, #logoutLink {
            text-decoration: none;
            color: #000;
            font-weight: bold;
            padding: 8px 12px;
            border: 1px solid #000;
            border-radius: 10px;
            cursor: pointer;
        }
        
        #backLink {
            background-color: #6dadee;
        }
        
        #accountLink {
            background-color: #99d4ff;
        }
        
        #logoutLink {
            background-color: #f86f6f;
        }
        
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: #c8f7c5;
            border: 1px solid #28a745;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        table thead {
            background-color: #2c3e50;
            color: #fff;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        
        table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
        }
        
        table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .no-logs {
            text-align: center;
            padding: 30px;
            color: #666;
            background-color: #fff;
            margin-top: 20px;
            border-radius: 4px;
        }
        
        .log-details {
            font-size: 12px;
            color: #555;
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
