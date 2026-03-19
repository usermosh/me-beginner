<?php
require_once "sessionChecker.php";
require_once "config.php";

// Generate ticket number in format YYYYMMDDHHMMSS
$ticketNumber = date('YmdHis');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnSave'])) {
    $problem = $_POST['problem'];
    $details = $_POST['details'];
    $status = 'PENDING';
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
            $logSql = "INSERT INTO tbllogs (ticketNumber, action, performedBy, datePerformed, details) 
                      VALUES (?, ?, ?, ?, ?)";

            if ($logStmt = mysqli_prepare($link, $logSql)) {
                mysqli_stmt_bind_param($logStmt, "sssss", $ticketNumber, $action, $createdBy, $dateCreated, $logDetails);
                mysqli_stmt_execute($logStmt);
                mysqli_stmt_close($logStmt);
            }

            $_SESSION['success'] = "Ticket created successfully! Ticket Number: " . $ticketNumber;
            header("location: ticketManagement.php");
            exit;
        } else {
            $_SESSION['error'] = "Error creating ticket!";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket - Technical Management System</title>

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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-wrapper {
            width: 100%;
            max-width: 620px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        /* ── Header ── */
        .form-header {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            padding: 35px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%,100% { transform: translateX(-100%) rotate(45deg); }
            50%      { transform: translateX(100%)  rotate(45deg); }
        }

        .header-icon {
            width: 70px; height: 70px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 15px;
            border: 3px solid rgba(255,255,255,0.4);
            position: relative; z-index: 1;
        }

        .header-icon svg {
            width: 38px; height: 38px;
            stroke: white; stroke-width: 2; fill: none;
        }

        .form-header h2 {
            color: #fff;
            font-size: 26px; font-weight: 600;
            margin-bottom: 6px;
            position: relative; z-index: 1;
        }

        .form-header p {
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            position: relative; z-index: 1;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            text-decoration: none;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .back-button:hover {
            background: rgba(255,255,255,0.3);
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

.back-button svg {
    width: 20px;
    height: 20px;
    stroke: white;
    stroke-width: 2.5;
    fill: none;
}

        /* ── Body ── */
        .form-body { padding: 40px; }

        /* Messages */
        .message {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 22px;
            border-left: 4px solid;
            font-size: 14px; font-weight: 500;
            display: flex; align-items: center; gap: 12px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .message svg { width: 20px; height: 20px; flex-shrink: 0; }

        .msg-error {
            background: linear-gradient(135deg, #f8d7da, #f5c2c7);
            border-color: #e74c3c; color: #721c24;
        }
        .msg-error svg { stroke: #e74c3c; }

        .msg-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-color: #27ae60; color: #155724;
        }
        .msg-success svg { stroke: #27ae60; }

        /* Info notice */
        .info-notice {
            background: linear-gradient(135deg, rgba(142,68,173,0.07), rgba(108,52,131,0.07));
            border-left: 4px solid #8e44ad;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 28px;
            font-size: 13px;
            color: #4a235a;
            display: flex; gap: 12px;
        }

        .info-notice svg {
            width: 20px; height: 20px;
            stroke: #8e44ad; stroke-width: 2;
            flex-shrink: 0; margin-top: 1px;
        }

        /* Ticket number box */
        .ticket-number-box {
            background: linear-gradient(135deg, rgba(142,68,173,0.08), rgba(108,52,131,0.08));
            border: 2px solid rgba(142,68,173,0.25);
            border-left: 4px solid #8e44ad;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 28px;
            display: flex; align-items: center; gap: 14px;
        }

        .ticket-number-box svg {
            width: 22px; height: 22px;
            stroke: #8e44ad; stroke-width: 2; flex-shrink: 0;
        }

        .ticket-number-label {
            font-size: 12px; font-weight: 600;
            color: #6c3483;
            text-transform: uppercase; letter-spacing: 0.5px;
            display: block; margin-bottom: 3px;
        }

        .ticket-number-value {
            font-family: 'Courier New', monospace;
            font-size: 18px; font-weight: 700;
            color: #8e44ad;
            letter-spacing: 1px;
        }

        /* Form groups */
        .form-group { margin-bottom: 24px; }

        .form-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 14px; font-weight: 600;
            color: #2c3e50; margin-bottom: 9px;
        }

        .form-label svg {
            width: 16px; height: 16px;
            stroke: #8e44ad; stroke-width: 2;
        }

        .required { color: #e74c3c; font-weight: bold; }

        /* Inputs */
        select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%238e44ad' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        textarea {
            min-height: 140px;
            resize: vertical;
        }

        select:hover, textarea:hover { border-color: #bbb; }

        select:focus, textarea:focus {
            outline: none;
            border-color: #8e44ad;
            box-shadow: 0 0 0 4px rgba(142, 68, 173, 0.1);
        }

        .char-hint {
            display: block;
            font-size: 12px; color: #999;
            margin-top: 6px; text-align: right;
        }

        /* Action buttons */
        .form-actions {
            display: flex; gap: 12px;
            margin-top: 30px; padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex; align-items: center;
            justify-content: center; gap: 8px;
            text-decoration: none;
            font-family: inherit;
        }

        .btn svg { width: 20px; height: 20px; stroke-width: 2; }

        .btn-save {
            background: linear-gradient(135deg, #8e44ad, #6c3483);
            color: white;
            box-shadow: 0 4px 15px rgba(142, 68, 173, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(142, 68, 173, 0.4);
        }

        .btn-save:active { transform: translateY(0); }

        .btn-cancel {
            background: rgba(0,0,0,0.05);
            color: #555;
            border: 2px solid #ddd;
        }

        .btn-cancel:hover {
            background: rgba(0,0,0,0.08);
            border-color: #bbb;
            color: #333;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .form-body     { padding: 25px 20px; }
            .form-header   { padding: 28px 20px; }
            .form-actions  { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="form-wrapper">
    <div class="form-container">

        <!-- Header -->
        <div class="form-header">
            <a href="index.php" class="back-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M19 12H5M12 19l-7-7 7-7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
            <div class="header-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2v6h6M12 18v-6M9 15h6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2>Create New Ticket</h2>
            <p>Submit a support request for technical assistance</p>
        </div>

        <!-- Body -->
        <div class="form-body">

            <!-- Session messages -->
            <?php
            if (isset($_SESSION['success'])) {
                echo "<div class='message msg-success'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                            <path d='M22 11.08V12a10 10 0 1 1-5.93-9.14' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M22 4 12 14.01l-3-3' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        <span>{$_SESSION['success']}</span>
                      </div>";
                unset($_SESSION['success']);
            }
            if (isset($_SESSION['error'])) {
                echo "<div class='message msg-error'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                            <circle cx='12' cy='12' r='10' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M15 9l-6 6M9 9l6 6' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        <span>{$_SESSION['error']}</span>
                      </div>";
                unset($_SESSION['error']);
            }
            ?>

            <!-- Info notice -->
            <div class="info-notice">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 16v-4M12 8h.01" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <strong>Note:</strong> Your ticket will be submitted with a <em>pending</em> status and a unique ticket number is auto-generated for tracking purposes.
                </div>
            </div>

            <!-- Auto-generated ticket number -->
            <div class="ticket-number-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="9" y="2" width="6" height="4" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2h-3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <span class="ticket-number-label">Auto-Generated Ticket Number</span>
                    <span class="ticket-number-value"><?php echo htmlspecialchars($ticketNumber); ?></span>
                </div>
            </div>

            <!-- Form -->
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="ticketNumber" value="<?php echo htmlspecialchars($ticketNumber); ?>">

                <!-- Problem Type -->
                <div class="form-group">
                    <label class="form-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Problem Type
                        <span class="required">*</span>
                    </label>
                    <select name="problem" id="problem" required>
                        <option value="">-- Select Problem Type --</option>
                        <option value="hardware">🔧 Hardware</option>
                        <option value="software">💻 Software</option>
                        <option value="connection">🌐 Connection</option>
                    </select>
                </div>

                <!-- Details -->
                <div class="form-group">
                    <label class="form-label">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2v6h6M16 13H8m8 4H8m2-8H8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Issue Details
                        <span class="required">*</span>
                    </label>
                    <textarea name="details" id="details"
                              placeholder="Describe the issue in as much detail as possible..."
                              required></textarea>
                    <span class="char-hint"><span id="charCount">0</span> characters</span>
                </div>

                <!-- Buttons -->
                <div class="form-actions">
                    <button type="submit" name="btnSave" class="btn btn-save">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 21v-8H7v8M7 3v5h8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Submit Ticket
                    </button>
                    <!-- FIX: plain anchor tag — works regardless of form validation -->
                    <a href="ticketManagement.php?tab=tickets" class="btn btn-cancel">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Cancel
                    </a>
                </div>

            </form>
        </div><!-- /.form-body -->
    </div><!-- /.form-container -->
</div><!-- /.form-wrapper -->

<script>
// Character counter
const textarea = document.getElementById('details');
const counter  = document.getElementById('charCount');

textarea.addEventListener('input', function () {
    const len = this.value.length;
    counter.textContent = len;
    counter.style.color = len < 20 ? '#e74c3c' : len < 50 ? '#f39c12' : '#27ae60';
});

// Auto-hide messages after 5 s
setTimeout(() => {
    document.querySelectorAll('.message').forEach(msg => {
        msg.style.transition = 'opacity 0.4s';
        msg.style.opacity    = '0';
        setTimeout(() => msg.remove(), 400);
    });
}, 5000);
</script>

</body>
</html>