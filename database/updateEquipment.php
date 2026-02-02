<?php
require_once "config.php";
include "sessionChecker.php";

// Define branch and department options
$branches = [
    'Head Office',
    'Cebu Campus',
    'Davao Campus',
    'Manila Campus',
    'Iloilo Campus'
];

$departments = [
    'College of Engineering',
    'College of Business',
    'College of Liberal Arts',
    'College of Science',
    'Office of the Registrar',
    'Office of Student Affairs',
    'Information Technology Office',
    'Library'
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

// Get equipment ID from URL
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
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .form-container {
            background: #ffffff;
            width: 100%;
            max-width: 600px;
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
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #2980b9;
            box-shadow: 0 0 5px rgba(41,128,185,0.4);
        }
        .status-group {
            margin-bottom: 15px;
        }
        .radio-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .radio-group label {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 0;
            font-weight: normal;
        }
        .radio-group input[type="radio"] {
            width: auto;
            margin: 0;
            padding: 0;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
        }
        input[type="submit"] {
            background: #2980b9;
            color: #fff;
            border: none;
            padding: 10px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
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
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .required {
            color: red;
        }
        .readonly-field {
            background-color: #f5f5f5;
            color: #666;
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

<?php
if (isset($_POST['btnsubmit'])) {
    $equipmentId = intval($_POST['equipmentId']);
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
            "ssssissi",
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

            $sql = "INSERT INTO tblequipmentlogs(datelog, timelog, action, module, performedby, equipmentId, assetNumber, changeDetails)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = mysqli_prepare($link, $sql)) {
                $date = date("d/m/Y");
                $time = date("h:i:sa");
                $action = "Update equipment";
                $module = "Equipment Management";
                $assetNumber = $oldEquipment['assetNumber'];

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssssss",
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
?>

</body>
</html>
