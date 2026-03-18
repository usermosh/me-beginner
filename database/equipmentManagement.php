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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Management - Technical Management System</title>

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

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header Section */
        .header-section {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        h1 svg {
            width: 32px;
            height: 32px;
            stroke: #3498db;
            stroke-width: 2;
        }

        /* Navigation Buttons */
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
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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

        #addEquipmentLink {
            border-left: 3px solid #27ae60;
        }

        #addEquipmentLink:hover {
            background: rgba(39, 174, 96, 0.15);
        }

        #logsLink {
            border-left: 3px solid #f39c12;
        }

        #logsLink:hover {
            background: rgba(243, 156, 18, 0.15);
        }

        #accountLink {
            border-left: 3px solid #3498db;
        }

        #accountLink:hover {
            background: rgba(52, 152, 219, 0.15);
        }

        #logoutLink {
            border-left: 3px solid #e74c3c;
        }

        #logoutLink:hover {
            background: rgba(231, 76, 60, 0.15);
        }

        /* Messages */
        .message {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid;
            font-weight: 600;
            animation: slideDown 0.4s ease-out;
            display: flex;
            align-items: center;
            gap: 12px;
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

        .message svg {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
        }

        .success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-color: #27ae60;
            color: #155724;
        }

        .success svg {
            stroke: #27ae60;
        }

        .error {
            background: linear-gradient(135deg, #f8d7da, #f5c2c7);
            border-color: #e74c3c;
            color: #721c24;
        }

        .error svg {
            stroke: #e74c3c;
        }

        /* Search Section */
        .search-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .search-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .search-label svg {
            width: 18px;
            height: 18px;
            stroke: #2a5298;
            stroke-width: 2;
        }

        .search-controls {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .search-controls input[type="text"] {
            flex: 1;
            min-width: 300px;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-controls input[type="text"]:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-search {
            background: linear-gradient(135deg, #4a90e2, #2a5298);
            color: white;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(42, 82, 152, 0.4);
        }

        .btn-clear {
            background: rgba(0, 0, 0, 0.05);
            color: #555;
            border: 2px solid #ddd;
        }

        .btn-clear:hover {
            background: rgba(0, 0, 0, 0.08);
            border-color: #bbb;
        }

        /* Table Section */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
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
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        table tbody tr {
            border-bottom: 1px solid #e0e0e0;
            transition: background-color 0.2s ease;
        }

        table tbody tr:hover {
            background-color: #f0f8ff;
        }

        table tbody tr:last-child {
            border-bottom: none;
        }

        table td {
            padding: 14px 16px;
            border: none;
            color: #2c3e50;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-active {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }

        .status-maintenance {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
        }

        .status-inactive {
            background: linear-gradient(135deg, #f8d7da, #f5c2c7);
            color: #721c24;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-update, .btn-delete {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .btn-update {
            background: linear-gradient(135deg, #f39c12, #d68910);
            color: white;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .no-results svg {
            width: 64px;
            height: 64px;
            stroke: #3498db;
            stroke-width: 2;
            margin-bottom: 20px;
        }

        .no-results h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 20px;
        }

        .no-results p {
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .no-results a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #27ae60, #1e8449);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .no-results a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .nav-buttons {
                grid-template-columns: 1fr;
            }

            .search-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-controls input[type="text"] {
                min-width: 100%;
            }

            table {
                font-size: 13px;
            }

            table th,
            table td {
                padding: 10px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header-section">
            <h1>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 21h8M12 17v4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Equipment Management
            </h1>
        </div>

        <!-- Navigation Container -->
        <div class="nav-container">
            <div class="nav-label">Quick Actions</div>
            <div class="nav-buttons">
                <a href="addEquipment.php" class="nav-link" id="addEquipmentLink">
                    <span>➕</span>
                    <span>Add Equipment</span>
                </a>
                <a href="equipmentLogs.php" class="nav-link" id="logsLink">
                    <span>📋</span>
                    <span>View Logs</span>
                </a>
                <a href="accountManagement.php" class="nav-link" id="accountLink">
                    <span>👤</span>
                    <span>Accounts</span>
                </a>
                <a href="logout.php" class="nav-link" id="logoutLink">
                    <span>🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Messages -->
        <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='message success'>
                    <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                        <path d='M22 11.08V12a10 10 0 1 1-5.93-9.14' stroke-linecap='round' stroke-linejoin='round'/>
                        <path d='M22 4 12 14.01l-3-3' stroke-linecap='round' stroke-linejoin='round'/>
                    </svg>
                    <span>{$_SESSION['success']}</span>
                  </div>";
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo "<div class='message error'>
                    <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                        <circle cx='12' cy='12' r='10' stroke-linecap='round' stroke-linejoin='round'/>
                        <path d='M15 9l-6 6M9 9l6 6' stroke-linecap='round' stroke-linejoin='round'/>
                    </svg>
                    <span>{$_SESSION['error']}</span>
                  </div>";
            unset($_SESSION['error']);
        }
        ?>

        <!-- Search Section -->
        <div class="search-section">
            <div class="search-label">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="8" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="m21 21-4.35-4.35" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Search Equipment
            </div>
            <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="search-controls">
                    <input type="text" 
                           id="searchInput" 
                           name="search" 
                           placeholder="Search by Asset Number, Serial Number, Type, or Department..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-search">Search</button>
                    <a href="equipmentManagement.php" class="btn btn-clear">Clear</a>
                </div>
            </form>
        </div>

        <!-- Equipment Table -->
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
            echo "<div class='table-container'>";
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
                $statusClass = '';
                $statusLower = strtolower($row['status']);
                if ($statusLower == 'active') {
                    $statusClass = 'status-active';
                } elseif ($statusLower == 'maintenance') {
                    $statusClass = 'status-maintenance';
                } else {
                    $statusClass = 'status-inactive';
                }
                
                echo "<tr>";
                echo "<td><strong>" . htmlspecialchars($row['assetNumber']) . "</strong></td>";
                echo "<td>" . htmlspecialchars($row['serialNumber']) . "</td>";
                echo "<td>" . htmlspecialchars($row['type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['branch']) . "</td>";
                echo "<td><span class='status-badge {$statusClass}'>" . htmlspecialchars($row['status']) . "</span></td>";
                echo "<td>" . htmlspecialchars($row['createdby']) . "</td>";
                echo "<td>";
                echo "<div class='action-buttons'>";
                echo "<a href='updateEquipment.php?id=" . $row['id'] . "' class='btn-update'>✏️ Edit</a>";
                echo "<a href='equipmentManagement.php?deleteEquipment=" . $row['id'] . "' class='btn-delete' onclick='return confirm(\"Are you sure you want to delete this equipment?\");'>🗑️ Delete</a>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<div class='no-results'>";
            echo "<svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>";
            echo "<rect x='2' y='3' width='20' height='14' rx='2' stroke-linecap='round' stroke-linejoin='round'/>";
            echo "<path d='M8 21h8M12 17v4' stroke-linecap='round' stroke-linejoin='round'/>";
            echo "</svg>";
            echo "<h3>No Equipment Found</h3>";
            if (isset($_GET['search'])) {
                echo "<p>No equipment matches your search criteria. Try different keywords.</p>";
            } else {
                echo "<p>Start managing your equipment inventory by adding items.</p>";
            }
            echo "<a href='addEquipment.php'>";
            echo "<span>➕</span>";
            echo "<span>Add Equipment</span>";
            echo "</a>";
            echo "</div>";
        }
        ?>
    </div>

    <script>
    // Auto-hide messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.message');
        messages.forEach(msg => {
            msg.style.animation = 'slideUp 0.3s ease';
            setTimeout(() => msg.remove(), 300);
        });
    }, 5000);

    const slideUpKeyframes = `
        @keyframes slideUp {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
    `;
    const style = document.createElement('style');
    style.textContent = slideUpKeyframes;
    document.head.appendChild(style);
    </script>
</body>
</html>