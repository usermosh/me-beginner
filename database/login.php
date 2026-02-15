<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page - Technical Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1B2D42 0%, #0f1619 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: #ffffff;
            width: 380px;
            padding: 50px 40px;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .login-box h1 {
            text-align: center;
            margin-bottom: 35px;
            color: #1B2D42;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .login-box label {
            display: block;
            font-size: 13px;
            color: #4a5568;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .login-box input[type="text"]:focus,
        .login-box input[type="password"]:focus {
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
        }

        .show-pass input[type="checkbox"] {
            margin-right: 8px;
            cursor: pointer;
        }

        .login-box input[type="submit"] {
            width: 100%;
            padding: 12px;
            background: #1B2D42;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background 0.3s;
        }

        .login-box input[type="submit"]:hover {
            background: #0f1619;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .message div {
            display: none;
        }

        .message div[style*="background"] {
            display: block !important;
            padding: 12px;
            border-radius: 4px;
            border: none !important;
        }

        .message div[style*="c8f7c5"] {
            background: #d1fae5 !important;
            color: #065f46 !important;
        }

        .message div[style*="f8d7da"] {
            background: #fee2e2 !important;
            color: #7f1d1d !important;
        }
    </style>
</head>

<body>

<div class="login-box">

    <h1>Technical Management System</h1>

    <!-- SESSION MESSAGE -->
    <div class="message">
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
    </div>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

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

        <input type="submit" name="btnsubmit" value="Login">
    </form>

</div>

<?php
if (isset($_POST['btnsubmit'])) {

    require_once "config.php";

    $sql = "SELECT * FROM tblaccounts 
            WHERE username = ? AND password = ? AND status = 'ACTIVE'";

    if ($stmt = mysqli_prepare($link, $sql)) {

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $_POST['txtusername'],
            $_POST['txtpassword']
        );

        if (mysqli_stmt_execute($stmt)) {

            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {

                $account = mysqli_fetch_array($result, MYSQLI_ASSOC);

                $_SESSION['username'] = $account['username'];
                $_SESSION['usertype'] = $account['usertype'];

                $_SESSION['success'] = "Login successful!";
                header("location: accountManagement.php");
                exit;

            } else {
                $_SESSION['error'] = "Login failed. Invalid username/password or inactive account.";
                header("location: login.php");
                exit;
            }

        } else {
            $_SESSION['error'] = "Error executing query.";
            header("location: login.php");
            exit;
        }
    }
}
?>

</body>
</html>
