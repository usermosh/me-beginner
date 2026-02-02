<?php
require_once "config.php";
include("sessionChecker.php");

if(isset($_POST['btnsubmit'])) { // delete
    $sql = "DELETE FROM tblaccounts WHERE username = ?";
    if($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_POST['txtusername']);
        if(mysqli_stmt_execute($stmt)) {
            $sql = "INSERT INTO tbllogs(datelog, timelog, action, module, performedby, performedto)
                    VALUES (?, ?, ?, ?, ?, ?)";
            if($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete account";
                $module = "Accounts Management";
                mysqli_stmt_bind_param($stmt, "ssssss", $date, $time, $action, $module, $_SESSION['username'], $_POST['txtusername']);
                if(mysqli_stmt_execute($stmt)) {
                    header("location: accountManagement.php");
                    exit();
                } else {
                    echo "<font color='red'>ERROR on inserting of logs.</font>";
                }
            }
        } else {
            echo "<font color='red'>ERROR on deleting account.</font>";
        }
    }
}
?>
<html>
<head>
    <title>Delete Account - Technical Management System</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: #bbd8f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .confirm-box {
            background: #f5f5f5;
            border: 1px solid #000;
            padding: 25px;
            width: 350px;
            text-align: center;
        }
        .confirm-box p {
            font-size: 16px;
            margin-bottom: 20px;
        }
        input[type="submit"] {
            padding: 6px 15px;
            font-weight: bold;
            border: 1px solid #000;
            border-radius: 10px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #6dadee;
        }
        .cancel-link {
            margin-left: 15px;
            text-decoration: none;
            font-weight: bold;
            color: #000;
            border: 1px solid #000;
            border-radius: 10px;
            padding: 6px 15px;
            background: #ddd;
        }
        .cancel-link:hover {
            background: #ff0000;
        }
    </style>
</head>
<body>
    <div class="confirm-box">
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <input
                type="hidden"
                name="txtusername"
                value="<?php echo trim($_GET['username']); ?>"
            >
            <p>Are you sure you want to delete this account?</p>
            <input type="submit" name="btnsubmit" value="Yes">
            <a href="accountManagement.php" class="cancel-link">No</a>
        </form>
    </div>
</body>
</html>
