<?php
session_start();

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
                header("location: index.php");
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
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f1d3d 0%, #1a2e5e 50%, #0d1a33 100%);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            width: 400px;
            height: 400px;
            background: rgba(100, 150, 255, 0.08);
            border-radius: 50%;
            top: -100px;
            left: -100px;
            animation: float1 8s ease-in-out infinite;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            background: rgba(80, 120, 255, 0.06);
            border-radius: 50%;
            bottom: -150px;
            right: -150px;
            animation: float2 10s ease-in-out infinite;
            z-index: 0;
        }

        @keyframes float1 {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(50px) translateX(30px); }
        }

        @keyframes float2 {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-50px) translateX(-30px); }
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            width: 420px;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-box h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-box .subtitle {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-bottom: 30px;
            font-weight: 500;
        }

        .login-box label {
            display: block;
            font-size: 13px;
            color: #333;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .login-box input[type="text"],
        .login-box input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            margin-bottom: 0;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .login-box input[type="text"]:focus,
        .login-box input[type="password"]:focus {
            outline: none;
            border-color: #2a5298;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
            transform: translateY(-2px);
        }

        .show-pass {
            font-size: 13px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .show-pass label {
            margin: 0;
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: 500;
            text-transform: none;
            letter-spacing: normal;
        }

        .show-pass input[type="checkbox"] {
            margin-right: 8px;
            width: 16px;
            height: 16px;
            cursor: pointer;
            accent-color: #2a5298;
        }

        .login-box input[type="submit"] {
            width: 100%;
            padding: 14px 20px;
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 25px rgba(42, 82, 152, 0.4);
            margin-top: 10px;
        }

        .login-box input[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(42, 82, 152, 0.6);
        }

        .login-box input[type="submit"]:active {
            transform: translateY(-1px);
        }

        .message {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-msg {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 15px;
            color: #ffffff;
            font-weight: 600;
            border-left: 4px solid #1a3a7e;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
            animation: slideDown 0.4s ease-out;
        }

        .error-msg {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 15px;
            color: #ffffff;
            font-weight: 600;
            border-left: 4px solid #a93226;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
            animation: slideDown 0.4s ease-out;
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
    </style>
</head>

<body>

<div class="login-box">

    <h1>🔐 Technical System</h1>
    <p class="subtitle">Management Dashboard</p>

    <div class="message">
        <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='success-msg'>
                    ✓ {$_SESSION['success']}
                  </div>";
            unset($_SESSION['success']);
        }

        if (isset($_SESSION['error'])) {
            echo "<div class='error-msg'>
                    ✕ {$_SESSION['error']}
                  </div>";
            unset($_SESSION['error']);
        }
        ?>
    </div>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

        <div class="input-group">
            <label>👤 Username</label>
            <input type="text" name="txtusername" required autocomplete="username">
        </div>

        <div class="input-group">
            <label>🔑 Password</label>
            <input type="password" name="txtpassword" id="password" required autocomplete="current-password">
        </div>

        <div class="show-pass">
            <label>
                <input type="checkbox"
                       onclick="document.getElementById('password').type = this.checked ? 'text' : 'password';">
                Show Password
            </label>
        </div>

        <input type="submit" name="btnsubmit" value="LOGIN">
    </form>

</div>

</body>
</html>