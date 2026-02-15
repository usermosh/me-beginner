<?php
require_once "sessionChecker.php";
require_once "config.php";

// Get ticket number from query string
if (!isset($_GET['ticketNumber'])) {
    header("location: accountManagement.php?tab=tickets");
    exit;
}

$ticketNumber = $_GET['ticketNumber'];

// Fetch ticket details
$ticket = null;
$sql = "SELECT * FROM tbltickets WHERE ticketNumber = ? AND createdBy = ?";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ss", $ticketNumber, $_SESSION['username']);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $ticket = mysqli_fetch_array($result, MYSQLI_ASSOC);
        } else {
            $_SESSION['error'] = "Ticket not found!";
            header("location: accountManagement.php?tab=tickets");
            exit;
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSave'])) {
    $newProblem = $_POST['problem'];
    $newDetails = $_POST['details'];

    // Update ticket
    $updateSql = "UPDATE tbltickets SET problem = ?, details = ? WHERE ticketNumber = ?";

    if ($updateStmt = mysqli_prepare($link, $updateSql)) {
        mysqli_stmt_bind_param($updateStmt, "sss", $newProblem, $newDetails, $ticketNumber);

        if (mysqli_stmt_execute($updateStmt)) {
            // Log the update
            $action = 'updated';
            $dateNow = date('m/d/Y g:i A');
            $oldDetails = 'Previous - Problem: ' . $ticket['problem'] . ', Details: ' . substr($ticket['details'], 0, 100) . '...';
            $newLogDetails = 'New - Problem: ' . $newProblem . ', Details: ' . $newDetails;
            $logSql = "INSERT INTO tblticketlogs (ticketNumber, action, performedBy, datePerformed, details) 
                      VALUES (?, ?, ?, ?, ?)";

            if ($logStmt = mysqli_prepare($link, $logSql)) {
                $combinedDetails = $oldDetails . ' | ' . $newLogDetails;
                mysqli_stmt_bind_param($logStmt, "sssss", $ticketNumber, $action, $_SESSION['username'], $dateNow, $combinedDetails);
                mysqli_stmt_execute($logStmt);
                mysqli_stmt_close($logStmt);
            }

            $_SESSION['success'] = "Ticket updated successfully!";
            header("location: accountManagement.php?tab=tickets");
            exit;
        } else {
            $_SESSION['error'] = "Error updating ticket!";
        }
        mysqli_stmt_close($updateStmt);
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
    <title>Update Ticket</title>
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

        .info {
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
        <h1>Update Ticket</h1>

        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='message error'>{$_SESSION['error']}</div>";
            unset($_SESSION['error']);
        }
        ?>

        <div class="info">
            <strong>Info:</strong> Update the problem type and details for your ticket. These changes will be logged.
        </div>

        <?php if ($ticket): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?ticketNumber=' . htmlspecialchars($ticketNumber); ?>">
                <div class="form-group">
                    <label for="ticketNumber">Ticket Number:</label>
                    <div class="ticket-number-display"><?php echo htmlspecialchars($ticket['ticketNumber']); ?></div>
                </div>

                <div class="form-group">
                    <label for="problem">Problem Type:</label>
                    <select name="problem" id="problem" required>
                        <option value="">-- Select Problem Type --</option>
                        <option value="hardware" <?php echo ($ticket['problem'] === 'hardware') ? 'selected' : ''; ?>>Hardware</option>
                        <option value="software" <?php echo ($ticket['problem'] === 'software') ? 'selected' : ''; ?>>Software</option>
                        <option value="connection" <?php echo ($ticket['problem'] === 'connection') ? 'selected' : ''; ?>>Connection</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="details">Details:</label>
                    <textarea name="details" id="details" placeholder="Describe the issue in detail..." required><?php echo htmlspecialchars($ticket['details']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" name="btnSave" class="btn-save">Save Changes</button>
                    <button type="submit" name="btnCancel" class="btn-cancel">Cancel</button>
                </div>
            </form>
        <?php else: ?>
            <div class="message error">
                Ticket not found or you do not have permission to update this ticket.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
