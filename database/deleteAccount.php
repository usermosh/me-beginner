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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1B2D42 0%, #0f1619 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .confirm-box {
            background: #ffffff;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        .confirm-box p {
            font-size: 16px;
            color: #1B2D42;
            margin-bottom: 30px;
            line-height: 1.6;
            font-weight: 500;
        }

        input[type="submit"] {
            background: linear-gradient(135deg, #8b5a5a 0%, #6b4444 100%);
            color: #ffffff;
            padding: 12px 28px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            margin-right: 10px;
        }

        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(139, 90, 90, 0.3);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        .cancel-link {
            text-decoration: none;
            font-weight: 600;
            color: #1B2D42;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            padding: 10px 24px;
            background: #ffffff;
            transition: all 0.3s ease;
            display: inline-block;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .cancel-link:hover {
            background-color: #f5f6f8;
            border-color: #1B2D42;
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
