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
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            margin: 0;
            color: #1B2D42;
            font-size: 28px;
            font-weight: 600;
        }
        
        .header-links {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        #addEquipmentLink, #logsLink, #accountLink, #logoutLink {
            text-decoration: none;
            color: #ffffff;
            font-weight: 600;
            padding: 11px 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        #addEquipmentLink {
            background-color: #5a8b5a;
        }

        #addEquipmentLink:hover {
            background-color: #4a6b4a;
            transform: translateY(-1px);
        }
        
        #logsLink {
            background-color: #6b7280;
        }

        #logsLink:hover {
            background-color: #4b5563;
            transform: translateY(-1px);
        }
        
        #accountLink {
            background-color: #1B2D42;
        }

        #accountLink:hover {
            background-color: #0f1619;
            transform: translateY(-1px);
        }
        
        #logoutLink {
            background-color: #8b5a5a;
        }

        #logoutLink:hover {
            background-color: #6b4545;
            transform: translateY(-1px);
        }
        
        .search-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-section input[type="text"] {
            flex: 1;
            min-width: 250px;
            padding: 11px 14px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 13px;
            transition: border-color 0.3s;
        }

        .search-section input[type="text"]:focus {
            outline: none;
            border-color: #1B2D42;
            box-shadow: 0 0 0 3px rgba(27, 45, 66, 0.1);
        }
        
        .search-section button {
            padding: 11px 20px;
            background-color: #1B2D42;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        .search-section button:hover {
            background-color: #0f1619;
        }
        
        .message {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .success {
            background-color: #d1fae5;
            border: 1px solid #6ee7b7;
            color: #065f46;
        }
        
        .error {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #7f1d1d;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        table thead {
            background-color: #1B2D42;
            color: #fff;
        }
        
        table th {
            padding: 14px;
            text-align: left;
            border: none;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        table tbody tr:last-child td {
            border-bottom: none;
        }
        
        table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-update, .btn-delete {
            padding: 8px 14px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-update {
            background-color: #6b7280;
            color: #ffffff;
        }
        
        .btn-update:hover {
            background-color: #4b5563;
        }
        
        .btn-delete {
            background-color: #8b5a5a;
            color: #ffffff;
        }
        
        .btn-delete:hover {
            background-color: #6b4545;
        }
        
        .no-results {
            text-align: center;
            padding: 30px;
            color: #6b7280;
            background-color: white;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
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
