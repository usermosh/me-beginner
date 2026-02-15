<?php
require_once "config.php";
include "sessionChecker.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Account Page - Technical Management System</title>

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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            background: #ffffff;
            width: 100%;
            max-width: 420px;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            color: #1B2D42;
            margin-bottom: 12px;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        p {
            text-align: center;
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 30px;
        }

        label {
            font-size: 13px;
            color: #4a5568;
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-family: inherit;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #1B2D42;
            box-shadow: 0 0 0 3px rgba(27, 45, 66, 0.1);
        }

        .show-pass {
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .show-pass label {
            display: flex;
            align-items: center;
            margin: 0;
            color: #6b7280;
            text-transform: none;
            font-weight: 400;
        }

        .show-pass input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }

        input[type="submit"] {
            background: #1B2D42;
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background: #0f1619;
        }

        .cancel {
            font-size: 13px;
            text-decoration: none;
            color: #6b7280;
            font-weight: 600;
            transition: color 0.3s;
        }

        .cancel:hover {
            color: #1B2D42;
        }

        .message-container {
            margin-bottom: 20px;
        }

        .message-container div {
            display: none;
        }

        .message-container div[style*="background"] {
            display: block !important;
            padding: 12px;
            border-radius: 4px;
            border: none !important;
        }

        .message-container div[style*="c8f7c5"] {
            background: #d1fae5 !important;
            color: #065f46 !important;
        }

        .message-container div[style*="f8d7da"] {
            background: #fee2e2 !important;
            color: #7f1d1d !important;
        }
    </style>
</head>

<body>

<div class="form-container">
    <h2>Create New Account</h2>
    <p>Fill up this form and submit to create a new account.</p>

    <!-- SESSION MESSAGES -->
    <?php
    if (isset($_SESSION['success'])) {
        echo "<div style='background:#c8f7c5; padding:10px; border:1px solid #000; margin-bottom:10px;'>
                {$_SESSION['success']}
              </div>";
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo "<div style='background:#f8d7da; padding:10px; border:1px solid #000; margin-bottom:10px;'>
                {$_SESSION['error']}
              </div>";
        unset($_SESSION['error']);
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">

        <label>Username</label>
        <input type="text" name="txtusername" required>

        <label>Password</label>
        <input type="password" name="txtpassword" id="password" required>

        <!-- SHOW PASSWORD CHECKBOX -->
        <div class="show-pass">
            <label>
                <input type="checkbox"
                       onclick="document.getElementById('password').type = this.checked ? 'text' : 'password';">
                Show Password
            </label>
        </div>

        <label>Account Type</label>
        <select name="cmbtype" required>
            <option value="">-- Select Account type --</option>
            <option value="ADMINISTRATOR">Administrator</option>
            <option value="TECHNICAL">Technical</option>
            <option value="USER">User</option>
        </select>

        <div class="actions">
            <input type="submit" name="btnsubmit" value="Submit">
            <a href="accountManagement.php" class="cancel">Cancel</a>
        </div>

    </form>
</div>

<?php
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

</body>
</html>
