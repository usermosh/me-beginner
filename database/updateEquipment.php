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

$statuses = ['WORKING', 'ON-REPAIR', 'RETIRED'];

// Handle form submission first
if (isset($_POST['btnsubmit'])) {
    $equipmentId = intval($_POST['equipmentId']);
    
    if ($equipmentId == 0) {
        $_SESSION['error'] = "Invalid equipment ID.";
        header("location: equipmentManagement.php");
        exit;
    }
    
    $serialNumber = trim($_POST['txtSerialNumber']);
    $type = $_POST['cmbType'];
    $manufacturer = trim($_POST['txtManufacturer']);
    $yearModel = intval($_POST['txtYearModel']);
    $description = trim($_POST['txtDescription']);
    $branch = $_POST['cmbBranch'];
    $department = $_POST['cmbDepartment'];
    $status = $_POST['rdoStatus'];
    $updatedBy = $_SESSION['username'];

    // Validation
    $errors = [];

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
    if (empty($status)) {
        $errors[] = "Status is required.";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("location: updateEquipment.php?id=" . $equipmentId);
        exit;
    }

    // Check if Serial Number is unique (excluding current equipment)
    $sql = "SELECT id FROM tblequipment WHERE serialNumber = ? AND id != ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "si", $serialNumber, $equipmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $_SESSION['error'] = "Serial Number already exists. Please use a different Serial Number.";
            header("location: updateEquipment.php?id=" . $equipmentId);
            exit;
        }
    }

    // Get old values for logging
    $sql = "SELECT * FROM tblequipment WHERE id = ?";
    $oldEquipment = null;
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $equipmentId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $oldEquipment = mysqli_fetch_assoc($result);
    }

    // Update equipment
    $sql = "UPDATE tblequipment SET serialNumber = ?, type = ?, manufacturer = ?, yearModel = ?, description = ?, branch = ?, department = ?, status = ?
            WHERE id = ?";

    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param(
            $stmt,
            "ssssisssi",
            $serialNumber,
            $type,
            $manufacturer,
            $yearModel,
            $description,
            $branch,
            $department,
            $status,
            $equipmentId
        );

        if (mysqli_stmt_execute($stmt)) {
            // Log the update
            $changeDetails = "";
            if ($oldEquipment['type'] != $type) $changeDetails .= "Type: {$oldEquipment['type']} → $type; ";
            if ($oldEquipment['status'] != $status) $changeDetails .= "Status: {$oldEquipment['status']} → $status; ";
            if ($oldEquipment['serialNumber'] != $serialNumber) $changeDetails .= "Serial Number changed; ";
            if ($oldEquipment['manufacturer'] != $manufacturer) $changeDetails .= "Manufacturer: {$oldEquipment['manufacturer']} → $manufacturer; ";
            if ($oldEquipment['yearModel'] != $yearModel) $changeDetails .= "Year Model: {$oldEquipment['yearModel']} → $yearModel; ";
            if ($oldEquipment['branch'] != $branch) $changeDetails .= "Branch: {$oldEquipment['branch']} → $branch; ";
            if ($oldEquipment['department'] != $department) $changeDetails .= "Department: {$oldEquipment['department']} → $department; ";

            $sql = "INSERT INTO tblequipmentlogs(datelog, timelog, action, module, performedby, equipmentId, assetNumber, details)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Update equipment";
                $module = "Equipment Management";
                $assetNumber = $oldEquipment['assetNumber'];

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssiss",
                    $date,
                    $time,
                    $action,
                    $module,
                    $updatedBy,
                    $equipmentId,
                    $assetNumber,
                    $changeDetails
                );
                mysqli_stmt_execute($stmt);
            }

            $_SESSION['success'] = "Equipment successfully updated!";
            header("location: equipmentManagement.php");
            exit;
        } else {
            $_SESSION['error'] = "Error updating equipment: " . mysqli_error($link);
            header("location: updateEquipment.php?id=" . $equipmentId);
            exit;
        }
    }
}


$equipmentId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($equipmentId == 0) {
    $_SESSION['error'] = "Invalid equipment ID.";
    header("location: equipmentManagement.php");
    exit;
}

// Fetch equipment data
$sql = "SELECT * FROM tblequipment WHERE id = ?";
$equipment = null;

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $equipmentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $equipment = $row;
    } else {
        $_SESSION['error'] = "Equipment not found.";
        header("location: equipmentManagement.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Equipment - Technical Management System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f1d3d 0%, #1a2e5e 50%, #0d1a33 100%);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 30px 20px;
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

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 650px;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
            animation: slideIn 0.6s ease-out;
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

        h2 {
            text-align: center;
            background: linear-gradient(135deg, #1a3a7e, #2a5298);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
        }

        p {
            text-align: center;
            font-size: 13px;
            color: #666;
            margin-bottom: 25px;
            font-weight: 500;
        }

        label {
            font-size: 13px;
            color: #333;
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
            padding: 14px 16px;
            margin-bottom: 18px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2a5298;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
            transform: translateY(-2px);
        }

        .status-group {
            margin-bottom: 20px;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(53, 122, 189, 0.1));
            padding: 16px;
            border-radius: 10px;
            border: 1px solid rgba(42, 82, 152, 0.2);
        }

        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
            font-weight: 500;
            text-transform: none;
            letter-spacing: normal;
        }

        .radio-group input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            padding: 0;
            cursor: pointer;
            accent-color: #2a5298;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            gap: 15px;
        }

        input[type="submit"] {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #fff;
            border: none;
            padding: 14px 30px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(42, 82, 152, 0.3);
            text-transform: uppercase;
        }

        input[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(42, 82, 152, 0.5);
            background: linear-gradient(135deg, #5a9ef0, #4585c7);
        }

        input[type="submit"]:active {
            transform: translateY(-1px);
        }

        .cancel {
            font-size: 14px;
            text-decoration: none;
            color: #2a5298;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 8px 16px;
        }

        .cancel:hover {
            color: #1a3a7e;
            text-decoration: underline;
        }

        .message {
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-weight: 600;
            border-left: 4px solid;
            animation: slideDown 0.4s ease-out;
        }

        .error {
            background: linear-gradient(135deg, #f8d7da, #f5c2c7);
            border-color: #e74c3c;
            color: #721c24;
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

        .required {
            color: #e74c3c;
            font-weight: 700;
        }

        .readonly-field {
            background-color: linear-gradient(135deg, #f0f8ff, #ffffff);
            color: #666;
            border-color: #ddd;
            cursor: not-allowed;
        }

        .readonly-field:focus {
            box-shadow: none;
            transform: none;
        }
    </style>
</head>

<body>

<div class="form-container">
    <h2>Update Equipment</h2>
    <p>Update the equipment details below.</p>

    <!-- SESSION MESSAGES -->
    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='message error'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
        <input type="hidden" name="equipmentId" value="<?php echo $equipment['id']; ?>">

        <label>Asset Number <span class="required">*</span></label>
        <input type="text" class="readonly-field" value="<?php echo htmlspecialchars($equipment['assetNumber']); ?>" readonly>

        <label>Serial Number <span class="required">*</span></label>
        <input type="text" name="txtSerialNumber" required value="<?php echo htmlspecialchars($equipment['serialNumber']); ?>">

        <label>Type <span class="required">*</span></label>
        <select name="cmbType" required>
            <option value="">-- Select Equipment Type --</option>
            <?php foreach ($equipmentTypes as $type): ?>
                <option value="<?php echo $type; ?>" <?php echo $equipment['type'] == $type ? 'selected' : ''; ?>>
                    <?php echo $type; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Manufacturer <span class="required">*</span></label>
        <input type="text" name="txtManufacturer" required value="<?php echo htmlspecialchars($equipment['manufacturer']); ?>">

        <label>Year Model <span class="required">*</span></label>
        <input type="number" name="txtYearModel" required value="<?php echo htmlspecialchars($equipment['yearModel']); ?>" min="1900" max="2100">

        <label>Description</label>
        <textarea name="txtDescription"><?php echo htmlspecialchars($equipment['description']); ?></textarea>

        <label>Branch <span class="required">*</span></label>
        <select name="cmbBranch" required>
            <option value="">-- Select Branch --</option>
            <?php foreach ($branches as $branch): ?>
                <option value="<?php echo $branch; ?>" <?php echo $equipment['branch'] == $branch ? 'selected' : ''; ?>>
                    <?php echo $branch; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Department <span class="required">*</span></label>
        <select name="cmbDepartment" required>
            <option value="">-- Select Department --</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept; ?>" <?php echo $equipment['department'] == $dept ? 'selected' : ''; ?>>
                    <?php echo $dept; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="status-group">
            <label><span class="required">*</span>Status</label>
            <div class="radio-group">
                <?php foreach ($statuses as $status): ?>
                    <label>
                        <input type="radio" name="rdoStatus" value="<?php echo $status; ?>" <?php echo $equipment['status'] == $status ? 'checked' : ''; ?> required>
                        <?php echo $status; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="actions">
            <input type="submit" name="btnsubmit" value="Save Changes">
            <a href="equipmentManagement.php" class="cancel">Cancel</a>
        </div>

    </form>
</div>

</body>
</html>
