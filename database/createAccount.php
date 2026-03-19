<?php
require_once "config.php";
require_once "sessionChecker.php";

// ================= FORM PROCESSING =================
if (isset($_POST['btnsubmit'])) {

    // check if username already exists
    $sql = "SELECT * FROM tblaccounts WHERE username = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_POST['txtusername']);

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 0) {

                // insert account
                $sql = "INSERT INTO tblaccounts 
                        (username, password, usertype, status, createdby, datecreated)
                        VALUES (?, ?, ?, ?, ?, ?)";

                if ($stmt = mysqli_prepare($link, $sql)) {
                    $status = "ACTIVE";
                    $date = date("d/m/Y");

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssss",
                        $_POST['txtusername'],
                        $_POST['txtpassword'],
                        $_POST['cmbtype'],
                        $status,
                        $_SESSION['username'],
                        $date
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        // Insert log
                        $logSql = "INSERT INTO tbllogs(datelog, timelog, action, module, performedby, performedto)
                                   VALUES (?, ?, ?, ?, ?, ?)";
                        if ($logStmt = mysqli_prepare($link, $logSql)) {
                            $date   = date("d/m/Y");
                            $time   = date("h:i:sa");
                            $action = "Create account";
                            $module = "Accounts Management";
                            mysqli_stmt_bind_param($logStmt, "ssssss",
                                $date, $time, $action, $module,
                                $_SESSION['username'], $_POST['txtusername']
                            );
                            @mysqli_stmt_execute($logStmt);
                            mysqli_stmt_close($logStmt);
                        }

                        $_SESSION['success'] = "User account successfully created!";
                        header("location: accountManagement.php");
                        exit();
                    }
                }

            } else {
                $_SESSION['error'] = "Username already in use.";
                header("location: createAccount.php");
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Account - Technical Management System</title>

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
            max-width: 520px;
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
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
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
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border: 3px solid rgba(255, 255, 255, 0.3);
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
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            position: relative;
            z-index: 1;
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

        .message-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-color: #27ae60;
            color: #155724;
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
        }

        .message-success svg {
            stroke: #27ae60;
        }

        .message-error svg {
            stroke: #e74c3c;
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

        input[type="text"],
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

        input[type="text"]:hover,
        input[type="password"]:hover,
        select:hover {
            border-color: #bbb;
        }

        input[type="text"]:focus,
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
        .password-wrapper {
            position: relative;
        }

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
            background: linear-gradient(135deg, #4a90e2, #2a5298);
            color: white;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(42, 82, 152, 0.4);
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

        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.08), rgba(42, 82, 152, 0.08));
            border-left: 4px solid #2a5298;
            padding: 14px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 13px;
            color: #2c3e50;
            display: flex;
            gap: 12px;
        }

        .info-box svg {
            width: 20px;
            height: 20px;
            stroke: #2a5298;
            stroke-width: 2;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Strength Indicator */
        .password-strength {
            display: flex;
            gap: 4px;
            margin-top: 8px;
        }

        .strength-bar {
            height: 4px;
            flex: 1;
            background: #e0e0e0;
            border-radius: 2px;
            transition: background 0.3s ease;
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
        }

        /* Loading State */
        .btn-loading {
            position: relative;
            pointer-events: none;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="8.5" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 8v6m3-3h-6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2>Create New Account</h2>
            <p>Fill in the details below to create a new user account</p>
        </div>

        <!-- Body -->
        <div class="form-body">
            <!-- Messages -->
            <?php
            if (isset($_SESSION['success'])) {
                echo "<div class='message message-success'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                            <path d='M22 11.08V12a10 10 0 1 1-5.93-9.14' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M22 4 12 14.01l-3-3' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        <span>{$_SESSION['success']}</span>
                      </div>";
                unset($_SESSION['success']);
            }

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

            <!-- Info Box -->
            <div class="info-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 16v-4M12 8h.01" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <strong>Account Creation Guidelines:</strong><br>
                    Choose a unique username and secure password. Select the appropriate account type based on user responsibilities.
                </div>
            </div>

            <!-- Form -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" id="createAccountForm">

                <!-- Username -->
                <div class="form-group">
                    <label>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="12" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Username
                        <span class="required">*</span>
                    </label>
                    <input type="text" 
                           name="txtusername" 
                           id="username"
                           placeholder="Enter username" 
                           required
                           autocomplete="off">
                </div>

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
                    <div class="password-wrapper">
                        <input type="password" 
                               name="txtpassword" 
                               id="password" 
                               placeholder="Enter password"
                               required
                               autocomplete="new-password">
                        <div class="password-strength" id="strengthBars">
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                            <div class="strength-bar"></div>
                        </div>
                    </div>
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
                        Account Type
                        <span class="required">*</span>
                    </label>
                    <select name="cmbtype" id="accountType" required>
                        <option value="">-- Select Account Type --</option>
                        <option value="ADMINISTRATOR">👑 Administrator</option>
                        <option value="TECHNICAL">🔧 Technical</option>
                        <option value="USER">👤 User</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="submit" name="btnsubmit" class="btn btn-primary" id="submitBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 4 12 14.01l-3-3" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Create Account
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

// Password Strength Indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const bars = document.querySelectorAll('.strength-bar');
    let strength = 0;
    
    // Reset bars
    bars.forEach(bar => bar.style.background = '#e0e0e0');
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++;
    if (/\d/.test(password) && /[!@#$%^&*]/.test(password)) strength++;
    
    const colors = ['#e74c3c', '#f39c12', '#f1c40f', '#27ae60'];
    for (let i = 0; i < strength; i++) {
        bars[i].style.background = colors[i];
    }
});

// Form Submission Loading State
document.getElementById('createAccountForm').addEventListener('submit', function() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.classList.add('btn-loading');
    submitBtn.textContent = 'Creating...';
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
</script>

</body>
</html>