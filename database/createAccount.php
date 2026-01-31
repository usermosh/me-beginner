<?php
require_once "config.php";
include "sessionChecker.php";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Account Page - Technical Management System</title>

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
        .form-container {
            background: #ffffff;
            width: 420px;
            padding: 35px;
            border-radius: 8px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.25);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        p {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }
        label {
            font-size: 14px;
            color: #333;
            display: block;
            margin-bottom: 6px;
        }
        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input:focus,
        select:focus {
            outline: none;
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41,128,185,0.4);
        }
        .show-pass {
            font-size: 13px;
            margin-bottom: 15px;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        input[type="submit"] {
            background: #2980b9;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #1f6391;
        }
        .cancel {
            font-size: 14px;
            text-decoration: none;
            color: #555;
        }
        .cancel:hover {
            text-decoration: underline;
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
