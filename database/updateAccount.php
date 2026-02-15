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

            // insert logs
            $sql = "INSERT INTO tbllogs(datelog, timelog, action, module, performedby, performedto)
                    VALUES (?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Update account";
                $module = "Accounts Management";

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $_SESSION['username'],
                    $_GET['username']
                );
                mysqli_stmt_execute($stmt);
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
<html>
<head>
    <title>Update Account - Technical Management System</title>

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

        .container {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .container > p:first-of-type {
            color: #1B2D42;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .container > p:nth-of-type(2) {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        strong {
            color: #1B2D42;
            display: block;
            margin-top: 15px;
            margin-bottom: 8px;
            font-size: 13px;
            letter-spacing: 0.3px;
            text-transform: uppercase;
            font-weight: 600;
        }

        label {
            color: #1B2D42;
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        input[type="password"],
        select {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #f5f6f8;
        }

        input[type="password"]:focus,
        select:focus {
            outline: none;
            background-color: #ffffff;
            border-color: #1B2D42;
            box-shadow: 0 0 0 3px rgba(27, 45, 66, 0.1);
        }

        .show-pass {
            font-size: 13px;
            margin: 12px 0 20px 0;
        }

        .show-pass label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 500;
        }

        .show-pass input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #1B2D42;
        }

        input[type="radio"] {
            margin-right: 8px;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #1B2D42;
        }

        input[type="submit"] {
            background: linear-gradient(135deg, #1B2D42 0%, #132038 100%);
            color: #ffffff;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-right: 10px;
        }

        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(27, 45, 66, 0.3);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        a {
            text-decoration: none;
            color: #1B2D42;
            border: 2px solid #e5e7eb;
            padding: 10px 20px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            display: inline-block;
        }

        a:hover {
            background-color: #f5f6f8;
            border-color: #1B2D42;
        }
    </style>
</head>

<body>

<div class="container">
    <p><strong>Update Account</strong></p>
    <p>Change the values on this form and submit to update the account.</p>

    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="POST">

        <strong>Username:</strong> <?php echo htmlspecialchars($account['username']); ?>
        <br><br>

        <label>Password:</label>
        <input
            type="password"
            name="txtpassword"
            id="password"
            value="<?php echo htmlspecialchars($account['password']); ?>"
            required
        >

        <!-- SHOW PASSWORD CHECKBOX -->
        <div class="show-pass">
            <label>
                <input type="checkbox"
                       onclick="document.getElementById('password').type = this.checked ? 'text' : 'password';">
                Show Password
            </label>
        </div>

        <strong>Current account type:</strong> <?php echo htmlspecialchars($account['usertype']); ?>
        <br><br>

        <label>Change account type to:</label>
        <select name="cmbtype" required>
            <option value="">--Select new account type--</option>
            <option value="ADMINISTRATOR">Administrator</option>
            <option value="TECHNICAL">Technical</option>
            <option value="USER">User</option>
        </select>

        <br><br>

        <label>Status:</label><br>
        <?php if ($account['status'] == "ACTIVE") { ?>
            <input type="radio" name="rbstatus" value="ACTIVE" checked> Active<br>
            <input type="radio" name="rbstatus" value="INACTIVE"> Inactive<br>
        <?php } else { ?>
            <input type="radio" name="rbstatus" value="ACTIVE"> Active<br>
            <input type="radio" name="rbstatus" value="INACTIVE" checked> Inactive<br>
        <?php } ?>

        <br>
        <input type="submit" name="btnsubmit" value="Submit">
        <a href="accountManagement.php">Cancel</a>
    </form>
</div>

</body>
</html>
