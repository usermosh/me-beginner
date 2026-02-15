<?php
/**
 * Database Setup Script
 * This script creates the necessary tables for the ticket management system
 */

require_once "config.php";

$tables = array(
    "tbltickets" => "
        CREATE TABLE IF NOT EXISTS `tbltickets` (
            `ticketNumber` VARCHAR(14) PRIMARY KEY,
            `problem` ENUM('hardware', 'software', 'connection') NOT NULL,
            `details` LONGTEXT NOT NULL,
            `status` ENUM('pending', 'assigned', 'completed', 'approved') NOT NULL DEFAULT 'pending',
            `createdBy` VARCHAR(100) NOT NULL,
            `dateCreated` VARCHAR(20) NOT NULL,
            `assignedTo` VARCHAR(100),
            `dateAssigned` VARCHAR(20),
            `dateCompleted` VARCHAR(20),
            `approvedBy` VARCHAR(100),
            `dateApproved` VARCHAR(20),
            `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    "tblticketlogs" => "
        CREATE TABLE IF NOT EXISTS `tblticketlogs` (
            `logId` INT AUTO_INCREMENT PRIMARY KEY,
            `ticketNumber` VARCHAR(14) NOT NULL,
            `action` ENUM('created', 'updated', 'deleted') NOT NULL,
            `performedBy` VARCHAR(100) NOT NULL,
            `datePerformed` VARCHAR(20) NOT NULL,
            `details` LONGTEXT,
            `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    "
);

$errors = array();
$created = array();

foreach ($tables as $tableName => $tableSQL) {
    if (mysqli_query($link, $tableSQL)) {
        $created[] = $tableName;
    } else {
        $errors[] = $tableName . ": " . mysqli_error($link);
    }
}

// Add indexes
$indexes = array(
    "ALTER TABLE `tbltickets` ADD INDEX `idx_status` (`status`);",
    "ALTER TABLE `tbltickets` ADD INDEX `idx_createdBy` (`createdBy`);",
    "ALTER TABLE `tbltickets` ADD INDEX `idx_problem` (`problem`);",
    "ALTER TABLE `tbltickets` ADD INDEX `idx_dateCreated` (`dateCreated`);",
    "ALTER TABLE `tblticketlogs` ADD INDEX `idx_ticketNumber` (`ticketNumber`);",
    "ALTER TABLE `tblticketlogs` ADD INDEX `idx_performedBy` (`performedBy`);",
    "ALTER TABLE `tblticketlogs` ADD INDEX `idx_datePerformed` (`datePerformed`);"
);

foreach ($indexes as $index) {
    mysqli_query($link, $index);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Setup</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #2c3e50;
            text-align: center;
        }

        .success {
            background: #c8f7c5;
            border: 1px solid #4caf50;
            color: #2d662d;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }

        .link {
            text-align: center;
            margin-top: 30px;
        }

        .link a {
            background: #2980b9;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }

        .link a:hover {
            background: #1f6391;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        li {
            padding: 8px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Database Setup</h1>

        <?php if (!empty($created)): ?>
            <div class="success">
                <strong>✓ Tables Created Successfully:</strong>
                <ul>
                    <?php foreach ($created as $table): ?>
                        <li>✓ <?= htmlspecialchars($table) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <strong>⚠ Errors/Notices:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="info">
            <strong>✓ Setup Complete!</strong><br>
            The ticket management system is ready to use. Please login to get started.
        </div>

        <div class="link">
            <a href="login.php">← Go to Login</a>
        </div>
    </div>
</body>
</html>
