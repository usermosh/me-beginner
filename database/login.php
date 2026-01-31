<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Page - Technical Management System</title>

    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: #ffffff;
            width: 380px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .login-box h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .login-box label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 6px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .show-pass {
            font-size: 13px;
            margin-bottom: 18px;
        }

        .login-box input[type="submit"] {
            width: 100%;
            padding: 10px;
            background: #2980b9;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
        }

        .login-box input[type="submit"]:hover {
            background: #1f6391;
        }

        .message {
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
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
