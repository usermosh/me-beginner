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

        .error {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #7f1d1d;
        }

        .info {
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
