<?php
require_once "sessionChecker.php";
require_once "config.php";

// ================= DELETE LOGIC =================
if (isset($_GET['deleteEquipment'])) {
    $equipmentId = intval($_GET['deleteEquipment']);
    
    // Get equipment details before deletion for logging
    $sql = "SELECT assetNumber FROM tblequipment WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $equipmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $equipment = mysqli_fetch_assoc($result);
        $assetNumber = $equipment['assetNumber'] ?? 'Unknown';
    }
    
    // Delete the equipment
    $sql = "DELETE FROM tblequipment WHERE id = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $equipmentId);

        if (mysqli_stmt_execute($stmt)) {
            // Insert logs
            $sql = "INSERT INTO tblequipmentlogs(datelog, timelog, action, module, performedby, equipmentId, assetNumber)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete equipment";
                $module = "Equipment Management";

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $_SESSION['username'],
                    $equipmentId,
                    $assetNumber
                );
                mysqli_stmt_execute($stmt);
            }

            $_SESSION['success'] = "Equipment successfully deleted!";
            header("location: equipmentManagement.php");
            exit;
        } else {
            $_SESSION['error'] = "Error deleting equipment: " . mysqli_error($link);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Equipment Management - Technical Management System</title>

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
        
        #addEquipmentLink, #logsLink, #accountLink, #logoutLink {
            text-decoration: none;
            color: #000;
            font-weight: bold;
            padding: 8px 12px;
            border: 1px solid #000;
            border-radius: 10px;
            cursor: pointer;
        }
        
        #addEquipmentLink {
            background-color: #6dadee;
        }
        
        #logsLink {
            background-color: #ffc966;
        }
        
        #accountLink {
            background-color: #99d4ff;
        }
        
        #logoutLink {
            background-color: #f86f6f;
        }
        
        .search-section {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-section input[type="text"] {
            width: 300px;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .search-section button {
            padding: 8px 16px;
            background-color: #2980b9;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .search-section button:hover {
            background-color: #1f6391;
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
        }
        
        table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
        }
        
        table tbody tr:hover {
            background-color: #f5f5f5;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-update, .btn-delete {
            padding: 6px 10px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-update {
            background-color: #ffc966;
            color: #000;
        }
        
        .btn-update:hover {
            background-color: #ffb347;
        }
        
        .btn-delete {
            background-color: #f86f6f;
            color: #fff;
        }
        
        .btn-delete:hover {
            background-color: #e55a5a;
        }
        
        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
            background-color: #fff;
            margin-top: 20px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Equipment Management</h1>
        <div class="header-links">
            <a href="addEquipment.php" id="addEquipmentLink">Add Equipment</a>
            <a href="equipmentLogs.php" id="logsLink">View Logs</a>
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

    <!-- SEARCH SECTION -->
    <div class="search-section">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="searchInput">Search by Asset Number, Serial Number, Type, or Department:</label><br><br>
            <input type="text" id="searchInput" name="search" placeholder="Enter search term..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Search</button>
            <a href="equipmentManagement.php" style="margin-left: 10px; padding: 8px 16px; background-color: #6dadee; color: #000; text-decoration: none; border: 1px solid #000; border-radius: 4px; display: inline-block; font-weight: bold;">Clear Search</a>
        </form>
    </div>

    <!-- EQUIPMENT TABLE -->
    <?php
    $searchTerm = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '';
    
    if ($searchTerm) {
        $sql = "SELECT id, assetNumber, serialNumber, type, branch, status, createdby 
                FROM tblequipment 
                WHERE assetNumber LIKE ? 
                   OR serialNumber LIKE ? 
                   OR type LIKE ? 
                   OR department LIKE ?
                ORDER BY id DESC";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
        }
    } else {
        $sql = "SELECT id, assetNumber, serialNumber, type, branch, status, createdby 
                FROM tblequipment 
                ORDER BY id DESC";
        
        $result = mysqli_query($link, $sql);
    }
    
    if (mysqli_num_rows($result) > 0) {
        echo "<table>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Asset Number</th>";
        echo "<th>Serial Number</th>";
        echo "<th>Type</th>";
        echo "<th>Branch</th>";
        echo "<th>Status</th>";
        echo "<th>Created By</th>";
        echo "<th>Actions</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['assetNumber']) . "</td>";
            echo "<td>" . htmlspecialchars($row['serialNumber']) . "</td>";
            echo "<td>" . htmlspecialchars($row['type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['branch']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['createdby']) . "</td>";
            echo "<td>";
            echo "<div class='action-buttons'>";
            echo "<a href='updateEquipment.php?id=" . $row['id'] . "' class='btn-update'>Update</a>";
            echo "<a href='equipmentManagement.php?deleteEquipment=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this equipment?\");'>Delete</a>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";
        }
        
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='no-results'>No equipment found. <a href='addEquipment.php' style='color: #2980b9; text-decoration: underline;'>Add Equipment</a></div>";
    }
    ?>

</body>
</html>
