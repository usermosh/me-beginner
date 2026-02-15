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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1B2D42 0%, #0f1619 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            background: #ffffff;
            width: 100%;
            max-width: 600px;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        h2 {
            text-align: center;
            color: #1B2D42;
            margin-bottom: 8px;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .form-container > p {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        label {
            display: block;
            font-size: 13px;
            color: #1B2D42;
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            transition: border-color 0.3s, box-shadow 0.3s;
            background-color: #f5f6f8;
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
            background-color: #ffffff;
            border-color: #1B2D42;
            box-shadow: 0 0 0 3px rgba(27, 45, 66, 0.1);
        }

        .status-group {
            margin-bottom: 20px;
        }

        .radio-group {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
            font-weight: 500;
            text-transform: none;
            letter-spacing: normal;
            font-size: 14px;
            cursor: pointer;
        }

        .radio-group input[type="radio"] {
            width: 18px;
            height: 18px;
            margin: 0;
            padding: 0;
            cursor: pointer;
            accent-color: #1B2D42;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            gap: 15px;
        }

        input[type="submit"] {
            background: linear-gradient(135deg, #1B2D42 0%, #132038 100%);
            color: #ffffff;
            border: none;
            padding: 12px 32px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            flex: 1;
        }

        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(27, 45, 66, 0.3);
        }

        input[type="submit"]:active {
            transform: translateY(0);
        }

        .cancel {
            flex: 1;
            text-align: center;
            padding: 12px 32px;
            font-size: 14px;
            text-decoration: none;
            color: #1B2D42;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .cancel:hover {
            background-color: #f5f6f8;
            border-color: #1B2D42;
        }

        .message {
            padding: 14px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-size: 14px;
            border-left: 4px solid;
        }

        .error {
            background-color: #fff5f5;
            border-left-color: #8b5a5a;
            color: #5a3a3a;
        }

        .required {
            color: #8b5a5a;
        }

        .readonly-field {
            background-color: #f5f6f8 !important;
            color: #666;
            cursor: not-allowed;
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
