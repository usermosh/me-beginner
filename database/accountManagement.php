<?php
session_start();

// check if user is logged in
if (!isset($_SESSION['username'])) {
    header("location: login.php");
    exit;
}

require_once "config.php";

// ================= DELETE LOGIC =================
if (isset($_GET['delete'])) {

    $sql = "DELETE FROM tblaccounts WHERE username = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $_GET['delete']);

        if (mysqli_stmt_execute($stmt)) {

            // insert logs
            $sql = "INSERT INTO tbllogs(datelog, timelog, action, module, performedby, performedto)
                    VALUES (?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Delete account";
                $module = "Accounts Management";

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $_SESSION['username'],
                    $_GET['delete']
                );
                mysqli_stmt_execute($stmt);
            }

            $_SESSION['success'] = "Account successfully deleted!";
            header("location: accountManagement.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Management - Technical Management System</title>

    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            margin: 20px;
            background-color: #bbd8f5;
        }
        #createAccountLink {
            background-color: #6dadee;
        }
        #logoutLink {
            background-color: #f86f6f;
        }
        #createAccountLink,
        #logoutLink {
            text-decoration: none;
            color: #000;
            font-weight: bold;
            padding: 6px 10px;
            border: 1px solid #000;
            border-radius: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background-color: #f5f5f5;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
        }
        th {
            background: #bbbaba;
        }
    </style>
</head>

<body>

<!-- SUCCESS MESSAGE -->
<?php
if (isset($_SESSION['success'])) {
    echo "<div style='background:#c8f7c5; padding:10px; border:1px solid #000; margin-bottom:10px;'>
            {$_SESSION['success']}
          </div>";
    unset($_SESSION['success']);
}
?>

<h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
<h4>Your account type: <?= htmlspecialchars($_SESSION['usertype']) ?></h4>

<form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <a href="createAccount.php" id="createAccountLink">Create Account</a>
    <a href="logout.php" id="logoutLink">Logout</a>
    <br><br>
    Search:
    <input type="text" name="search">
    <input type="submit" name="btnsearch" value="Search">
</form>

<br>

<?php
function buildTable($result) {

    if (mysqli_num_rows($result) > 0) {

        echo "<table>";
        echo "<tr>
                <th>Username</th>
                <th>Usertype</th>
                <th>Status</th>
                <th>Created By</th>
                <th>Date Created</th>
                <th>Action</th>
              </tr>";

        while ($row = mysqli_fetch_assoc($result)) {

            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['usertype']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td>" . htmlspecialchars($row['createdby']) . "</td>";
            echo "<td>" . htmlspecialchars($row['datecreated']) . "</td>";
            echo "<td>
                    <a href='updateAccount.php?username=" . urlencode($row['username']) . "'>Update</a>
                    <a href='accountManagement.php?delete=" . urlencode($row['username']) . "'
                       onclick=\"return confirm('Are you sure you want to delete this account?');\">
                       Delete
                    </a>
                  </td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No records found.</p>";
    }
}

// ================= SEARCH / LOAD =================
if (isset($_POST['btnsearch'])) {

    $sql = "SELECT * FROM tblaccounts
            WHERE username LIKE ? OR usertype LIKE ?
            ORDER BY username";

    if ($stmt = mysqli_prepare($link, $sql)) {
        $search = "%" . $_POST['search'] . "%";
        mysqli_stmt_bind_param($stmt, "ss", $search, $search);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        buildTable($result);
    }

} else {

    $sql = "SELECT * FROM tblaccounts ORDER BY username";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        buildTable($result);
    }
}
?>

</body>
</html>
