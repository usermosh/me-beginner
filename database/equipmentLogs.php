<?php
require_once "sessionChecker.php";
require_once "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Logs - Technical Management System</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        /* ── Header ── */
        .header-section {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-icon {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #f39c12, #d68910);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(243,156,18,0.3);
        }

        .header-icon svg {
            width: 28px; height: 28px;
            stroke: white; stroke-width: 2; fill: none;
        }

        .header-text h1 {
            color: #ffffff;
            font-size: 26px; font-weight: 600;
            margin-bottom: 4px;
        }

        .header-text p {
            color: #a0c4ff;
            font-size: 13px;
        }

        /* ── Navigation ── */
        .nav-container {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .nav-label {
            color: #a0c4ff;
            font-size: 12px;
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 15px; font-weight: 600;
        }

        .nav-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .nav-link {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; color: #ffffff;
            font-weight: 500; padding: 14px 20px;
            border-radius: 8px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            transition: all 0.3s ease;
            font-size: 14px;
            position: relative; overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before { left: 100%; }

        .nav-link:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        #backLink    { border-left: 3px solid #3498db; }
        #accountLink { border-left: 3px solid #27ae60; }
        #logoutLink  { border-left: 3px solid #e74c3c; }

        #backLink:hover    { background: rgba(52,152,219,0.15); }
        #accountLink:hover { background: rgba(39,174,96,0.15); }
        #logoutLink:hover  { background: rgba(231,76,60,0.15); }

        /* ── Messages ── */
        .message {
            padding: 16px 20px; border-radius: 10px;
            margin-bottom: 25px; border-left: 4px solid;
            font-weight: 600;
            display: flex; align-items: center; gap: 12px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity:0; transform:translateY(-10px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .message svg { width:22px; height:22px; flex-shrink:0; }

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

        /* ── Stats row ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            border-top: 4px solid;
        }

        .stat-card.total    { border-color: #3498db; }
        .stat-card.add      { border-color: #27ae60; }
        .stat-card.update   { border-color: #f39c12; }
        .stat-card.delete   { border-color: #e74c3c; }

        .stat-number {
            font-size: 32px; font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-card.total  .stat-number { color: #3498db; }
        .stat-card.add    .stat-number { color: #27ae60; }
        .stat-card.update .stat-number { color: #f39c12; }
        .stat-card.delete .stat-number { color: #e74c3c; }

        .stat-label {
            font-size: 12px; font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ── Table ── */
        .table-container {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 2px solid #f0f0f0;
            display: flex; align-items: center; gap: 10px;
        }

        .table-header svg {
            width: 20px; height: 20px;
            stroke: #f39c12; stroke-width: 2;
        }

        .table-header h3 {
            font-size: 16px; font-weight: 600;
            color: #2c3e50;
        }

        .table-header .log-count {
            margin-left: auto;
            background: rgba(243,156,18,0.1);
            color: #d68910;
            font-size: 12px; font-weight: 700;
            padding: 4px 12px; border-radius: 20px;
            border: 1px solid rgba(243,156,18,0.3);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table thead {
            background: linear-gradient(135deg,#1a3a7e,#2a5298);
            color: #ffffff;
        }

        table th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600; font-size: 12px;
            text-transform: uppercase; letter-spacing: 0.5px;
            border: none;
            white-space: nowrap;
        }

        table tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s ease;
        }

        table tbody tr:hover { background-color: #f8f9ff; }
        table tbody tr:last-child { border-bottom: none; }

        table tbody tr:nth-child(even) { background-color: #fafafa; }
        table tbody tr:nth-child(even):hover { background-color: #f0f4ff; }

        table td {
            padding: 13px 16px;
            border: none;
            color: #2c3e50;
            font-size: 14px;
        }

        /* Action badge */
        .action-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 700;
            white-space: nowrap;
        }

        .action-badge .dot {
            width: 7px; height: 7px;
            border-radius: 50%;
        }

        .badge-add    { background:#d4edda; color:#155724; }
        .badge-add    .dot { background:#27ae60; }

        .badge-update { background:#fff3cd; color:#856404; }
        .badge-update .dot { background:#f39c12; }

        .badge-delete { background:#f8d7da; color:#721c24; }
        .badge-delete .dot { background:#e74c3c; }

        .badge-default { background:#e2e8f0; color:#4a5568; }
        .badge-default .dot { background:#718096; }

        /* Details cell */
        .details-cell {
            font-size: 12px; color: #6b7280;
            max-width: 280px;
        }

        .no-detail { color:#bbb; font-style:italic; }

        /* Timestamp cells */
        .date-cell { font-weight: 600; color: #2c3e50; white-space: nowrap; }
        .time-cell { color: #7f8c8d; font-size: 13px; white-space: nowrap; }

        /* Asset number */
        .asset-cell {
            font-family: 'Courier New', monospace;
            font-size: 13px; font-weight: 600;
            color: #3498db;
        }

        /* Performed by */
        .user-cell {
            display: flex; align-items: center; gap: 8px;
        }

        .user-avatar {
            width: 28px; height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg,#4a90e2,#2a5298);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 11px; font-weight: 700;
            flex-shrink: 0;
        }

        /* No logs */
        .no-logs {
            text-align: center; padding: 60px 20px;
        }

        .no-logs svg {
            width: 60px; height: 60px;
            stroke: #cbd5e0; stroke-width: 2;
            margin-bottom: 16px;
        }

        .no-logs h3 { color: #718096; font-size: 18px; margin-bottom: 8px; }
        .no-logs p  { color: #a0aec0; font-size: 14px; }

        /* Responsive */
        @media (max-width: 900px) {
            .stats-row { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 600px) {
            body { padding: 20px 12px; }
            .header-section { padding: 20px; }
            .nav-buttons { grid-template-columns: 1fr; }
            .stats-row { grid-template-columns: 1fr 1fr; }
            table { font-size: 12px; }
            table th, table td { padding: 10px 10px; }
        }
    </style>
</head>

<body>
<div class="container">

    <!-- Header -->
    <div class="header-section">
        <div class="header-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2v6h6M16 13H8m8 4H8m2-8H8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="header-text">
            <h1>Equipment Logs</h1>
            <p>Full audit trail of all equipment management activities</p>
        </div>
    </div>

    <!-- Navigation -->
    <div class="nav-container">
        <div class="nav-label">Quick Actions</div>
        <div class="nav-buttons">
            <a href="equipmentManagement.php" class="nav-link" id="backLink">
                <span>⚙️</span><span>Equipment Management</span>
            </a>
            <a href="accountManagement.php" class="nav-link" id="accountLink">
                <span>👤</span><span>Accounts</span>
            </a>
            <a href="logout.php" class="nav-link" id="logoutLink">
                <span>🚪</span><span>Logout</span>
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

    <?php
    // Fetch all logs
    $sql    = "SELECT * FROM tblequipmentlogs ORDER BY createdAt DESC";
    $result = mysqli_query($link, $sql);
    $total  = mysqli_num_rows($result);

    // Count by action type
    $countAdd = $countUpdate = $countDelete = 0;
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
        $a = strtolower($row['action'] ?? '');
        if (str_contains($a,'add'))    $countAdd++;
        elseif (str_contains($a,'update')) $countUpdate++;
        elseif (str_contains($a,'delete')) $countDelete++;
    }
    ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card total">
            <div class="stat-number"><?php echo $total; ?></div>
            <div class="stat-label">Total Logs</div>
        </div>
        <div class="stat-card add">
            <div class="stat-number"><?php echo $countAdd; ?></div>
            <div class="stat-label">Added</div>
        </div>
        <div class="stat-card update">
            <div class="stat-number"><?php echo $countUpdate; ?></div>
            <div class="stat-label">Updated</div>
        </div>
        <div class="stat-card delete">
            <div class="stat-number"><?php echo $countDelete; ?></div>
            <div class="stat-label">Deleted</div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-container">
        <div class="table-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2v6h6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3>Activity Log</h3>
            <span class="log-count"><?php echo $total; ?> record<?php echo $total !== 1 ? 's' : ''; ?></span>
        </div>

        <?php if ($total > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Action</th>
                    <th>Performed By</th>
                    <th>Asset Number</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row):
                    $actionLower = strtolower($row['action'] ?? '');
                    if (str_contains($actionLower,'add'))         { $badgeClass = 'badge-add'; }
                    elseif (str_contains($actionLower,'update'))  { $badgeClass = 'badge-update'; }
                    elseif (str_contains($actionLower,'delete'))  { $badgeClass = 'badge-delete'; }
                    else                                           { $badgeClass = 'badge-default'; }

                    $initial = strtoupper(substr($row['performedby'] ?? '?', 0, 1));
                    $details = !empty($row['changeDetails'])
                               ? htmlspecialchars($row['changeDetails'])
                               : "<span class='no-detail'>No additional details</span>";
                ?>
                <tr>
                    <td class="date-cell"><?php echo htmlspecialchars($row['datelog']); ?></td>
                    <td class="time-cell"><?php echo htmlspecialchars($row['timelog']); ?></td>
                    <td>
                        <span class="action-badge <?php echo $badgeClass; ?>">
                            <span class="dot"></span>
                            <?php echo htmlspecialchars($row['action']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar"><?php echo $initial; ?></div>
                            <?php echo htmlspecialchars($row['performedby']); ?>
                        </div>
                    </td>
                    <td class="asset-cell">
                        <?php echo htmlspecialchars($row['assetNumber'] ?: '—'); ?>
                    </td>
                    <td class="details-cell"><?php echo $details; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php else: ?>
        <div class="no-logs">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M14 2v6h6M12 18v-6M9 15h6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h3>No Logs Yet</h3>
            <p>Equipment activity will appear here once actions are performed.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
// Auto-hide messages
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