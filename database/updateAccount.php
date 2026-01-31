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
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            background-color: #bbd8f5;
        }
        .container {
            background: #f5f5f5;
            border: 1px solid #000;
            padding: 20px;
            width: 400px;
            margin: 100px auto;
        }
        input[type="password"],
        select {
            width: 100%;
            padding: 6px;
        }
        .show-pass {
            font-size: 13px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            padding: 6px 12px;
            border: 1px solid #000;
            border-radius: 10px;
            font-weight: bold;
            background-color: #6dadee;
        }
        a {
            margin-left: 10px;
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
