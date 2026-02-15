<?php
require_once "config.php";
include "sessionChecker.php";

// Define branch and department options
$branches = [
    'Juan Sumulong Campus (AU Legarda / Main)',
    'Jose Abad Santos Campus (AU Pasay)',
    'Andres Bonifacio Campus (AU Pasig)',
    'Jose Rizal Campus (AU Malabon)',
    'Apolinario Mabini Campus (AU Pasay)',
    'Plaridel Campus (AU Mandaluyong)',
    'Elisa Esguerra Campus (AU Malabon)',
    'School of Law (A. Mabini Campus - AU Pasay)'
];

$departments = [
    'College of Nursing',
    'College of Medical Laboratory Science',
    'College of Arts and Sciences',
    'College of Hospitality and Tourism Management',
    'College of Computer Science',
    'College of Criminal Justice Education',
    'College of Accountancy',
    
];

$equipmentTypes = [
    'Monitor',
    'CPU',
    'Keyboard',
    'Mouse',
    'AVR',
    'MAC',
    'Printer',
    'Projector'
];

// Handle form submission
if (isset($_POST['btnsubmit'])) {
    $assetNumber = trim($_POST['txtAssetNumber']);
    $serialNumber = trim($_POST['txtSerialNumber']);
    $type = $_POST['cmbType'];
    $manufacturer = trim($_POST['txtManufacturer']);
    $yearModel = intval($_POST['txtYearModel']);
    $description = trim($_POST['txtDescription']);
    $branch = $_POST['cmbBranch'];
    $department = $_POST['cmbDepartment'];
    $createdBy = $_SESSION['username'];

    // Validation
    $errors = [];

    if (empty($assetNumber)) {
        $errors[] = "Asset Number is required.";
    }
    if (empty($serialNumber)) {
        $errors[] = "Serial Number is required.";
    }
    if (empty($type)) {
        $errors[] = "Type is required.";
    }
    if (empty($manufacturer)) {
        $errors[] = "Manufacturer is required.";
    }
    if (empty($yearModel) || $yearModel < 1900 || $yearModel > 2100) {
        $errors[] = "Year Model must be numeric and between 1900 and 2100.";
    }
    if (strlen($yearModel) != 4 && $yearModel > 0) {
        $errors[] = "Year Model should contain exactly 4 numbers.";
    }
    if (empty($branch)) {
        $errors[] = "Branch is required.";
    }
    if (empty($department)) {
        $errors[] = "Department is required.";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("location: addEquipment.php");
        exit;
    }

    // Check if Asset Number already exists
    $sql = "SELECT id FROM tblequipment WHERE assetNumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $assetNumber);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "Asset Number already exists. Please use a different Asset Number.";
            header("location: addEquipment.php");
            exit;
        }
    }

    // Check if Serial Number already exists
    $sql = "SELECT id FROM tblequipment WHERE serialNumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $serialNumber);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "Serial Number already exists. Please use a different Serial Number.";
            header("location: addEquipment.php");
            exit;
        }
    }

    // Insert equipment
    $sql = "INSERT INTO tblequipment (assetNumber, serialNumber, type, manufacturer, yearModel, description, branch, department, status, createdby)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($link, $sql)) {
        $status = "WORKING";
        $yearModelInt = intval($yearModel);
        mysqli_stmt_bind_param(
            $stmt,
            "ssssisssss",
            $assetNumber,
            $serialNumber,
            $type,
            $manufacturer,
            $yearModelInt,
            $description,
            $branch,
            $department,
            $status,
            $createdBy
        );

        if (mysqli_stmt_execute($stmt)) {
            $equipmentId = mysqli_insert_id($link);
            
            // Insert log entry
            $logSql = "INSERT INTO tblequipmentlogs(datelog, timelog, action, module, performedby, equipmentId, assetNumber)
                       VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            if ($logStmt = mysqli_prepare($link, $logSql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Add equipment";
                $module = "Equipment Management";
                
                mysqli_stmt_bind_param(
                    $logStmt,
                    "sssssss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $createdBy,
                    $equipmentId,
                    $assetNumber
                );
                mysqli_stmt_execute($logStmt);
            }
            
            $_SESSION['success'] = "Equipment successfully added!";
            header("location: equipmentManagement.php");
            exit;
        } else {
            $_SESSION['error'] = "Error adding equipment: " . mysqli_error($link);
            header("location: addEquipment.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Equipment - Technical Management System</title>

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
            padding: 30px 20px;
        }

        .form-container {
            background: #ffffff;
            width: 100%;
            max-width: 600px;
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
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #1B2D42;
            box-shadow: 0 0 0 3px rgba(27, 45, 66, 0.1);
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
        }

        input[type="submit"] {
            background: #1B2D42;
            color: #ffffff;
            border: none;
            padding: 12px 32px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background 0.3s, transform 0.2s;
        }

        input[type="submit"]:hover {
            background: #0f1619;
            transform: translateY(-1px);
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

        .message {
            padding: 14px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-size: 13px;
        }

        .error {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
            color: #7f1d1d;
        }

        .required {
            color: #7f1d1d;
            font-weight: 600;
        }
    </style>
</head>

<body>

<div class="form-container">
    <h2>Add Equipment</h2>
    <p>Fill up this form and submit to add a new equipment.</p>

    <!-- SESSION MESSAGES -->
    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='message error'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">

        <label>Asset Number <span class="required">*</span></label>
        <input type="text" name="txtAssetNumber" required placeholder="e.g., ASSET001">

        <label>Serial Number <span class="required">*</span></label>
        <input type="text" name="txtSerialNumber" required placeholder="e.g., SN123456789">

        <label>Type <span class="required">*</span></label>
        <select name="cmbType" required>
            <option value="">-- Select Equipment Type --</option>
            <?php foreach ($equipmentTypes as $type): ?>
                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
            <?php endforeach; ?>
        </select>

        <label>Manufacturer <span class="required">*</span></label>
        <input type="text" name="txtManufacturer" required placeholder="e.g., Dell">

        <label>Year Model <span class="required">*</span></label>
        <input type="number" name="txtYearModel" required placeholder="e.g., 2023" min="1900" max="2100">

        <label>Description</label>
        <textarea name="txtDescription" placeholder="Enter equipment description..."></textarea>

        <label>Branch <span class="required">*</span></label>
        <select name="cmbBranch" required>
            <option value="">-- Select Branch --</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo $branch; ?>"><?php echo $branch; ?></option>
            <?php endforeach; ?>
        </select>

        <label>Department <span class="required">*</span></label>
        <select name="cmbDepartment" required>
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
            <?php endforeach; ?>
        </select>

        <div class="actions">
            <input type="submit" name="btnsubmit" value="Save Equipment">
            <a href="equipmentManagement.php" class="cancel">Cancel</a>
        </div>

    </form>
</div>

</body>
</html>
