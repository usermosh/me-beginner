<?php
require_once "sessionChecker.php";
require_once "config.php";

// Generate ticket number in format YYYYMMDDHHMMSS
$ticketNumber = date('YmdHis');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSave'])) {
    $problem = $_POST['problem'];
    $details = $_POST['details'];
    $status = 'pending';
    $createdBy = $_SESSION['username'];
    $dateCreated = date('m/d/Y g:i A');

    // Insert ticket into database
    $sql = "INSERT INTO tbltickets (ticketNumber, problem, details, status, createdBy, dateCreated) 
            VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssssss", $ticketNumber, $problem, $details, $status, $createdBy, $dateCreated);

        if (mysqli_stmt_execute($stmt)) {
            // Log the creation
            $action = 'created';
            $logDetails = 'Problem: ' . $problem . ', Details: ' . $details;
            $logSql = "INSERT INTO tblticketlogs (ticketNumber, action, performedBy, datePerformed, details) 
                      VALUES (?, ?, ?, ?, ?)";

            if ($logStmt = mysqli_prepare($link, $logSql)) {
                mysqli_stmt_bind_param($logStmt, "sssss", $ticketNumber, $action, $createdBy, $dateCreated, $logDetails);
                mysqli_stmt_execute($logStmt);
                mysqli_stmt_close($logStmt);
            }

            $_SESSION['success'] = "Ticket created successfully! Ticket Number: " . $ticketNumber;
            header("location: accountManagement.php?tab=tickets");
            exit;
        } else {
            $_SESSION['error'] = "Error creating ticket!";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle cancel button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnCancel'])) {
    header("location: accountManagement.php?tab=tickets");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Ticket</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #bdc3c7;
            border-radius: 4px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41, 128, 185, 0.3);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .ticket-number-display {
            background: #ecf0f1;
            padding: 12px;
            border-radius: 4px;
            font-weight: bold;
            color: #2c3e50;
            font-family: monospace;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        button {
            padding: 12px 30px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
            font-weight: bold;
        }

        .btn-save {
            background: #27ae60;
            color: white;
        }

        .btn-save:hover {
            background: #1e8449;
        }

        .btn-cancel {
            background: #95a5a6;
            color: white;
        }

        .btn-cancel:hover {
            background: #7f8c8d;
        }

        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .note {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create New Ticket</h1>

        <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='message' style='background:#c8f7c5; border:1px solid #4caf50; color:#2d662d;'>{$_SESSION['success']}</div>";
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo "<div class='message error'>{$_SESSION['error']}</div>";
            unset($_SESSION['error']);
        }
        ?>

        <div class="note">
            <strong>Note:</strong> Your ticket will be created with a pending status. The ticket number is auto-generated and displayed below.
        </div>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="ticketNumber">Ticket Number (Auto-Generated):</label>
                <div class="ticket-number-display"><?php echo htmlspecialchars($ticketNumber); ?></div>
                <input type="hidden" name="ticketNumber" value="<?php echo htmlspecialchars($ticketNumber); ?>">
            </div>

            <div class="form-group">
                <label for="problem">Problem Type:</label>
                <select name="problem" id="problem" required>
                    <option value="">-- Select Problem Type --</option>
                    <option value="hardware">Hardware</option>
                    <option value="software">Software</option>
                    <option value="connection">Connection</option>
                </select>
            </div>

            <div class="form-group">
                <label for="details">Details:</label>
                <textarea name="details" id="details" placeholder="Describe the issue in detail..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" name="btnSave" class="btn-save">Save Ticket</button>
                <button type="submit" name="btnCancel" class="btn-cancel">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>
