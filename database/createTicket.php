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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1B2D42 0%, #0f1619 100%);
            min-height: 100vh;
            padding: 30px 20px;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #1B2D42;
            text-align: center;
            margin-bottom: 35px;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #1B2D42;
            box-shadow: 0 0 0 3px rgba(27, 45, 66, 0.1);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .ticket-number-display {
            background: #f5f6f8;
            padding: 12px;
            border-radius: 4px;
            font-weight: 600;
            color: #1B2D42;
            font-family: monospace;
            border: 1px solid #e5e7eb;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 35px;
        }

        button {
            padding: 12px 32px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .btn-save {
            background: #5a8b5a;
            color: white;
        }

        .btn-save:hover {
            background: #4a6b4a;
            transform: translateY(-1px);
        }

        .btn-cancel {
            background: #6b7280;
            color: white;
        }

        .btn-cancel:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }

        .message {
            padding: 14px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 13px;
        }

        .message[style*="c8f7c5"] {
            background: #d1fae5 !important;
            border: 1px solid #6ee7b7 !important;
            color: #065f46 !important;
        }

        .error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #7f1d1d;
        }

        .note {
            background: #f5f6f8;
            border: 1px solid #e5e7eb;
            color: #4a5568;
            padding: 14px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-size: 13px;
            border-left: 3px solid #1B2D42;
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
