<?php
require_once "sessionChecker.php";
require_once "config.php";

// Get user information
$username = $_SESSION['username'];
$usertype = $_SESSION['usertype'];

// Determine which menus to show based on user type
$showAccountsManagement = ($usertype === 'ADMINISTRATOR');
$showEquipmentManagement = true; // All users can access
$showTicketManagement = true; // All users can access

// Determine ticket management redirect URL based on role
$ticketManagementUrl = 'ticketManagement.php'; // Default for users
if ($usertype === 'ADMINISTRATOR') {
    $ticketManagementUrl = 'ticketManagementAdmin.php';
} elseif ($usertype === 'TECHNICAL') {
    $ticketManagementUrl = 'ticketManagementTechnical.php';
}

// Get some quick stats for the dashboard
$statsAccountsTotal = 0;
$statsEquipmentTotal = 0;
$statsTicketsTotal = 0;
$statsTicketsPending = 0;

// Count accounts (only for admin)
if ($usertype === 'ADMINISTRATOR') {
    $sql = "SELECT COUNT(*) as total FROM tblaccounts";
    if ($result = mysqli_query($link, $sql)) {
        $row = mysqli_fetch_assoc($result);
        $statsAccountsTotal = $row['total'];
    }
}

// Count equipment
$sql = "SELECT COUNT(*) as total FROM tblequipment";
if ($result = mysqli_query($link, $sql)) {
    $row = mysqli_fetch_assoc($result);
    $statsEquipmentTotal = $row['total'];
}

// Count tickets
$sql = "SELECT COUNT(*) as total FROM tbltickets";
if ($result = mysqli_query($link, $sql)) {
    $row = mysqli_fetch_assoc($result);
    $statsTicketsTotal = $row['total'];
}

// Count pending tickets
$sql = "SELECT COUNT(*) as total FROM tbltickets WHERE status = 'pending'";
if ($result = mysqli_query($link, $sql)) {
    $row = mysqli_fetch_assoc($result);
    $statsTicketsPending = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Technical Management System</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f1d3d 0%, #1a2e5e 50%, #0d1a33 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
            width: 100%;
        }

        /* ── Header with Logo ── */
        .page-header {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px 40px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .logo-container {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4a90e2, #2a5298);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(74,144,226,0.4);
            flex-shrink: 0;
        }

        .logo-container img {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }

        .header-content {
            flex: 1;
        }

        .system-title {
            color: #ffffff;
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .system-subtitle {
            color: #a0c4ff;
            font-size: 13px;
            font-weight: 500;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231,76,60,0.3);
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231,76,60,0.4);
        }

        /* ── Welcome Section ── */
        .welcome-section {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .welcome-text {
            color: #ffffff;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .info-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            font-size: 14px;
            color: #a0c4ff;
        }

        .info-badge svg {
            width: 18px;
            height: 18px;
            stroke: #4a90e2;
            stroke-width: 2;
        }

        .info-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        .info-value {
            color: #ffffff;
            font-weight: 600;
        }

        /* ── Stats Cards ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-top: 4px solid;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-card.accounts { border-color: #4a90e2; }
        .stat-card.equipment { border-color: #27ae60; }
        .stat-card.tickets { border-color: #8e44ad; }
        .stat-card.pending { border-color: #f39c12; }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
        }

        .stat-card.accounts .stat-icon {
            background: linear-gradient(135deg, rgba(74,144,226,0.15), rgba(42,82,152,0.15));
        }

        .stat-card.equipment .stat-icon {
            background: linear-gradient(135deg, rgba(39,174,96,0.15), rgba(26,122,64,0.15));
        }

        .stat-card.tickets .stat-icon {
            background: linear-gradient(135deg, rgba(142,68,173,0.15), rgba(108,52,131,0.15));
        }

        .stat-card.pending .stat-icon {
            background: linear-gradient(135deg, rgba(243,156,18,0.15), rgba(214,137,16,0.15));
        }

        .stat-icon svg {
            width: 24px;
            height: 24px;
            stroke-width: 2;
        }

        .stat-card.accounts .stat-icon svg { stroke: #4a90e2; }
        .stat-card.equipment .stat-icon svg { stroke: #27ae60; }
        .stat-card.tickets .stat-icon svg { stroke: #8e44ad; }
        .stat-card.pending .stat-icon svg { stroke: #f39c12; }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .stat-card.accounts .stat-number { color: #4a90e2; }
        .stat-card.equipment .stat-number { color: #27ae60; }
        .stat-card.tickets .stat-number { color: #8e44ad; }
        .stat-card.pending .stat-number { color: #f39c12; }

        .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Menu Cards ── */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .menu-card {
            background: rgba(255,255,255,0.95);
            border-radius: 16px;
            padding: 32px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-top: 4px solid;
            position: relative;
            overflow: hidden;
        }

        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            opacity: 0.08;
            transition: transform 0.5s ease;
        }

        .menu-card:hover::before {
            transform: scale(1.5);
        }

        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.25);
        }

        .menu-card.accounts-menu {
            border-color: #4a90e2;
        }

        .menu-card.accounts-menu::before {
            background: #4a90e2;
        }

        .menu-card.equipment-menu {
            border-color: #27ae60;
        }

        .menu-card.equipment-menu::before {
            background: #27ae60;
        }

        .menu-card.tickets-menu {
            border-color: #8e44ad;
        }

        .menu-card.tickets-menu::before {
            background: #8e44ad;
        }

        .menu-icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }

        .menu-card.accounts-menu .menu-icon-wrapper {
            background: linear-gradient(135deg, #4a90e2, #2a5298);
        }

        .menu-card.equipment-menu .menu-icon-wrapper {
            background: linear-gradient(135deg, #27ae60, #1a7a40);
        }

        .menu-card.tickets-menu .menu-icon-wrapper {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
        }

        .menu-icon-wrapper svg {
            width: 32px;
            height: 32px;
            stroke: white;
            stroke-width: 2;
            fill: none;
        }

        .menu-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .menu-card.accounts-menu .menu-title { color: #4a90e2; }
        .menu-card.equipment-menu .menu-title { color: #27ae60; }
        .menu-card.tickets-menu .menu-title { color: #8e44ad; }

        .menu-description {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .menu-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 12px;
            position: relative;
            z-index: 1;
        }

        .menu-card.accounts-menu .menu-badge {
            background: rgba(74,144,226,0.15);
            color: #2a5298;
        }

        .menu-card.equipment-menu .menu-badge {
            background: rgba(39,174,96,0.15);
            color: #1a7a40;
        }

        .menu-card.tickets-menu .menu-badge {
            background: rgba(142,68,173,0.15);
            color: #6c3483;
        }

        /* ── Footer ── */
        .page-footer {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255,255,255,0.1);
            padding: 20px 30px;
            text-align: center;
            margin-top: auto;
        }

        .footer-text {
            color: #a0c4ff;
            font-size: 13px;
        }

        .footer-text strong {
            color: #ffffff;
            font-weight: 600;
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                text-align: center;
                padding: 25px 20px;
            }

            .system-title {
                font-size: 22px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Header with Logo -->
    <div class="page-header">
        <div class="logo-container">
            <img src="au-logo.png" alt="AU Logo">
        </div>
        <div class="header-content">
            <div class="system-title">Arellano University</div>
            <div class="system-subtitle">Technical Management System</div>
        </div>
        <div class="header-actions">
            <a href="logout.php" class="btn-logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 17l5-5-5-5M21 12H9" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Logout
            </a>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">Welcome back, <?php echo htmlspecialchars($username); ?>!</div>
        <div class="user-info">
            <div class="info-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="info-label">Account Type:</span>
                <span class="info-value"><?php echo htmlspecialchars($usertype); ?></span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <?php if ($showAccountsManagement): ?>
        <div class="stat-card accounts">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-number"><?php echo $statsAccountsTotal; ?></div>
            <div class="stat-label">Total Accounts</div>
        </div>
        <?php endif; ?>

        <div class="stat-card equipment">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 21h8M12 17v4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-number"><?php echo $statsEquipmentTotal; ?></div>
            <div class="stat-label">Total Equipment</div>
        </div>

        <div class="stat-card tickets">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2v6h6M16 13H8m8 4H8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-number"><?php echo $statsTicketsTotal; ?></div>
            <div class="stat-label">Total Tickets</div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 6v6l4 2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-number"><?php echo $statsTicketsPending; ?></div>
            <div class="stat-label">Pending Tickets</div>
        </div>
    </div>

    <!-- Menu Cards -->
    <div class="menu-grid">
        <?php if ($showAccountsManagement): ?>
        <a href="accountManagement.php" class="menu-card accounts-menu">
            <div class="menu-icon-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="menu-title">Accounts Management</div>
            <div class="menu-description">
                Manage user accounts, permissions, and access control. Create, update, or deactivate user profiles.
            </div>
            <div class="menu-badge">Administrator Only</div>
        </a>
        <?php endif; ?>

        <a href="equipmentManagement.php" class="menu-card equipment-menu">
            <div class="menu-icon-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 21h8M12 17v4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="menu-title">Equipment Management</div>
            <div class="menu-description">
                Track and manage equipment inventory, monitor status, and maintain detailed equipment records.
            </div>
            <div class="menu-badge">
                <?php 
                if ($usertype === 'USER') echo 'View Only';
                else echo 'Full Access';
                ?>
            </div>
        </a>

        <a href="<?php echo htmlspecialchars($ticketManagementUrl); ?>" class="menu-card tickets-menu">
            <div class="menu-icon-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2v6h6M16 13H8m8 4H8m2-8H8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="menu-title">Ticket Management</div>
            <div class="menu-description">
                Submit, track, and resolve technical support tickets. Monitor progress and communicate issues.
            </div>
            <div class="menu-badge">
                <?php 
                if ($usertype === 'ADMINISTRATOR') echo 'Admin Portal';
                elseif ($usertype === 'TECHNICAL') echo 'Technical Portal';
                else echo 'User Portal';
                ?>
            </div>
        </a>
    </div>

</div>

<!-- Footer -->
<div class="page-footer">
    <div class="footer-text">
        Copyright © 2026 <strong>Technical Management System</strong> • All Rights Reserved
    </div>
</div>

</body>
</html>