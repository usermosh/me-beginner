<?php
require_once "config.php";
include("sessionChecker.php");

// UPDATE ACCOUNT 
if (isset($_POST['btnsubmit'])) {

    $sql = "UPDATE tblaccounts SET password = ?, usertype = ?, status = ? WHERE username = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $_POST['txtpassword'],
            $_POST['cmbtype'],
            $_POST['rbstatus'],
            $_GET['username']
        );

        if (mysqli_stmt_execute($stmt)) {

            // insert logs (non-blocking - continue even if logging fails)
            $sql = "INSERT INTO tbllogs(datelog, timelog, action, module, performedby, performedto)
                    VALUES (?, ?, ?, ?, ?, ?)";

            if ($logStmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Update account";
                $module = "Accounts Management";

                mysqli_stmt_bind_param(
                    $logStmt,
                    "ssssss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $_SESSION['username'],
                    $_GET['username']
                );
                @mysqli_stmt_execute($logStmt);
                mysqli_stmt_close($logStmt);
            }

            // SUCCESS MESSAGE
            $_SESSION['success'] = "Account successfully updated!";
            header("location: accountManagement.php");
            exit();

        } else {
            $_SESSION['error'] = "ERROR on updating account.";
        }
    }

} else {
    //  LOAD ACCOUNT INFO 
    if (isset($_GET['username']) && !empty(trim($_GET['username']))) {

        $sql = "SELECT * FROM tblaccounts WHERE username = ?";

        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $_GET['username']);

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $account = mysqli_fetch_array($result);
            } else {
                $_SESSION['error'] = "ERROR on loading account info.";
            }
        }

    } else {
        header("location: accountManagement.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Account - Technical Management System</title>

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
            max-width: 550px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.3);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #f39c12, #d68910);
            padding: 35px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) rotate(45deg); }
            50% { transform: translateX(100%) rotate(45deg); }
        }

        .header-icon {
            width: 70px;
            height: 70px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 3px solid rgba(255, 255, 255, 0.4);
            position: relative;
            z-index: 1;
        }

        .header-icon svg {
            width: 40px;
            height: 40px;
            stroke: white;
            stroke-width: 2;
        }

        .form-header h2 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 600;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .form-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            position: relative;
            z-index: 1;
        }

        .form-body {
            padding: 40px;
        }

        /* Messages */
        .message {
            padding: 14px 18px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown 0.4s ease;
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

        .message-error {
            background: linear-gradient(135deg, #f8d7da, #f5c2c7);
            border-color: #e74c3c;
            color: #721c24;
        }

        .message svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
            stroke: #e74c3c;
        }

        /* Info Display Box */
        .info-display {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.08), rgba(42, 82, 152, 0.08));
            border-left: 4px solid #2a5298;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-row svg {
            width: 18px;
            height: 18px;
            stroke: #2a5298;
            stroke-width: 2;
            flex-shrink: 0;
        }

        .info-label {
            font-weight: 600;
            color: #2c3e50;
            font-size: 13px;
            min-width: 110px;
        }

        .info-value {
            color: #2a5298;
            font-weight: 700;
            font-size: 15px;
            background: rgba(42, 82, 152, 0.1);
            padding: 4px 12px;
            border-radius: 6px;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        label svg {
            width: 16px;
            height: 16px;
            stroke: #2a5298;
            stroke-width: 2;
        }

        .required {
            color: #e74c3c;
            font-weight: bold;
        }

        input[type="password"],
        select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: white;
        }

        input[type="password"]:hover,
        select:hover {
            border-color: #bbb;
        }

        input[type="password"]:focus,
        select:focus {
            outline: none;
            border-color: #2a5298;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.1);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%232a5298' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 40px;
        }

        /* Password Toggle */
        .show-password {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 13px;
            color: #555;
            cursor: pointer;
            user-select: none;
        }

        .show-password input[type="checkbox"] {
            width: auto;
            cursor: pointer;
            margin: 0;
        }

        .show-password:hover {
            color: #2a5298;
        }

        /* Radio Buttons */
        .radio-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 10px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
            flex: 1;
        }

        .radio-option:hover {
            border-color: #2a5298;
            background: rgba(42, 82, 152, 0.05);
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .radio-option.active {
            border-color: #2a5298;
            background: rgba(42, 82, 152, 0.1);
        }

        .radio-label {
            font-weight: 500;
            color: #2c3e50;
            font-size: 14px;
        }

        .radio-option.active .radio-label {
            color: #2a5298;
            font-weight: 600;
        }

        .status-active {
            border-color: #27ae60;
        }

        .status-active:hover,
        .status-active.active {
            border-color: #27ae60;
            background: rgba(39, 174, 96, 0.1);
        }

        .status-inactive {
            border-color: #e74c3c;
        }

        .status-inactive:hover,
        .status-inactive.active {
            border-color: #e74c3c;
            background: rgba(231, 76, 60, 0.1);
        }

        /* Warning Box */
        .warning-box {
            background: linear-gradient(135deg, rgba(243, 156, 18, 0.08), rgba(214, 137, 16, 0.08));
            border-left: 4px solid #f39c12;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #856404;
            display: flex;
            gap: 12px;
        }

        .warning-box svg {
            width: 20px;
            height: 20px;
            stroke: #f39c12;
            stroke-width: 2;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Action Buttons */
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid #f0f0f0;
        }

        .btn {
            flex: 1;
            padding: 14px 24px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            font-family: inherit;
        }

        .btn svg {
            width: 20px;
            height: 20px;
            stroke-width: 2;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f39c12, #d68910);
            color: white;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-secondary {
            background: rgba(0, 0, 0, 0.05);
            color: #555;
            border: 2px solid #ddd;
        }

        .btn-secondary:hover {
            background: rgba(0, 0, 0, 0.08);
            border-color: #bbb;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .form-body {
                padding: 30px 25px;
            }

            .form-header {
                padding: 30px 25px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .radio-group {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<div class="form-wrapper">
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <div class="header-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2>Update Account</h2>
            <p>Modify account details and permissions</p>
        </div>

        <!-- Body -->
        <div class="form-body">
            <!-- Error Message -->
            <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='message message-error'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                            <circle cx='12' cy='12' r='10' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M15 9l-6 6M9 9l6 6' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        <span>{$_SESSION['error']}</span>
                      </div>";
                unset($_SESSION['error']);
            }
            ?>

            <!-- Account Info Display -->
            <div class="info-display">
                <div class="info-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="info-label">Username:</span>
                    <span class="info-value"><?php echo htmlspecialchars($account['username']); ?></span>
                </div>
                <div class="info-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="info-label">Current Type:</span>
                    <span class="info-value"><?php echo htmlspecialchars($account['usertype']); ?></span>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 9v4M12 17h.01" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <strong>Important:</strong> Changes to this account will be logged and take effect immediately. Ensure all modifications are accurate before submitting.
                </div>
            </div>

            <!-- Form -->
            <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="POST" id="updateAccountForm">

                <!-- Password -->
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Password
                        <span class="required">*</span>
                    </label>
                    <input type="password" 
                           name="txtpassword" 
                           id="password" 
                           value="<?php echo htmlspecialchars($account['password']); ?>"
                           placeholder="Enter new password"
                           required>
                    <label class="show-password">
                        <input type="checkbox" id="showPasswordToggle">
                        Show Password
                    </label>
                </div>

                <!-- Account Type -->
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Change Account Type
                        <span class="required">*</span>
                    </label>
                    <select name="cmbtype" id="accountType" required>
                        <option value="">-- Select New Account Type --</option>
                        <option value="ADMINISTRATOR" <?php echo ($account['usertype'] == 'ADMINISTRATOR') ? 'selected' : ''; ?>>👑 Administrator</option>
                        <option value="TECHNICAL" <?php echo ($account['usertype'] == 'TECHNICAL') ? 'selected' : ''; ?>>🔧 Technical</option>
                        <option value="USER" <?php echo ($account['usertype'] == 'USER') ? 'selected' : ''; ?>>👤 User</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 12l2 2 4-4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Account Status
                        <span class="required">*</span>
                    </label>
                    <div class="radio-group">
                        <label class="radio-option status-active <?php echo ($account['status'] == 'ACTIVE') ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="rbstatus" 
                                   value="ACTIVE" 
                                   <?php echo ($account['status'] == 'ACTIVE') ? 'checked' : ''; ?>>
                            <span class="radio-label">✓ Active</span>
                        </label>
                        <label class="radio-option status-inactive <?php echo ($account['status'] == 'INACTIVE') ? 'active' : ''; ?>">
                            <input type="radio" 
                                   name="rbstatus" 
                                   value="INACTIVE" 
                                   <?php echo ($account['status'] == 'INACTIVE') ? 'checked' : ''; ?>>
                            <span class="radio-label">✗ Inactive</span>
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="submit" name="btnsubmit" class="btn btn-primary" id="submitBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 21v-8H7v8M7 3v5h8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save Changes
                    </button>
                    <a href="accountManagement.php" class="btn btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
// Password Toggle
document.getElementById('showPasswordToggle').addEventListener('change', function() {
    const passwordInput = document.getElementById('password');
    passwordInput.type = this.checked ? 'text' : 'password';
});

// Radio Button Visual Feedback
document.querySelectorAll('.radio-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', function() {
        // Remove active class from all options in this group
        this.closest('.radio-group').querySelectorAll('.radio-option').forEach(option => {
            option.classList.remove('active');
        });
        // Add active class to selected option
        this.closest('.radio-option').classList.add('active');
    });
});

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

// Confirm before leaving with unsaved changes
let formChanged = false;
document.querySelectorAll('#updateAccountForm input, #updateAccountForm select').forEach(input => {
    const originalValue = input.value;
    input.addEventListener('change', function() {
        formChanged = (this.value !== originalValue);
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});

document.getElementById('updateAccountForm').addEventListener('submit', function() {
    formChanged = false;
});
</script>

</body>
</html>