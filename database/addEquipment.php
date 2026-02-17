<?php
require_once "config.php";
include "sessionChecker.php";

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

$equipmentTypes = ['Monitor','CPU','Keyboard','Mouse','AVR','MAC','Printer','Projector'];

if (isset($_POST['btnsubmit'])) {
    $assetNumber  = trim($_POST['txtAssetNumber']);
    $serialNumber = trim($_POST['txtSerialNumber']);
    $type         = $_POST['cmbType'];
    $manufacturer = trim($_POST['txtManufacturer']);
    $yearModel    = intval($_POST['txtYearModel']);
    $description  = trim($_POST['txtDescription']);
    $branch       = $_POST['cmbBranch'];
    $department   = $_POST['cmbDepartment'];
    $createdBy    = $_SESSION['username'];

    $errors = [];
    if (empty($assetNumber))  $errors[] = "Asset Number is required.";
    if (empty($serialNumber)) $errors[] = "Serial Number is required.";
    if (empty($type))         $errors[] = "Type is required.";
    if (empty($manufacturer)) $errors[] = "Manufacturer is required.";
    if (empty($yearModel) || $yearModel < 1900 || $yearModel > 2100)
        $errors[] = "Year Model must be between 1900 and 2100.";
    if (strlen((string)$yearModel) != 4 && $yearModel > 0)
        $errors[] = "Year Model should be exactly 4 digits.";
    if (empty($branch))     $errors[] = "Branch is required.";
    if (empty($department)) $errors[] = "Department is required.";

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("location: addEquipment.php"); exit;
    }

    $sql = "SELECT id FROM tblequipment WHERE assetNumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $assetNumber);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $_SESSION['error'] = "Asset Number already exists.";
            header("location: addEquipment.php"); exit;
        }
    }

    $sql = "SELECT id FROM tblequipment WHERE serialNumber = ?";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $serialNumber);
        mysqli_stmt_execute($stmt);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
            $_SESSION['error'] = "Serial Number already exists.";
            header("location: addEquipment.php"); exit;
        }
    }

    $sql = "INSERT INTO tblequipment (assetNumber,serialNumber,type,manufacturer,yearModel,description,branch,department,status,createdby)
            VALUES (?,?,?,?,?,?,?,?,?,?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        $status = "WORKING";
        mysqli_stmt_bind_param($stmt,"ssssisssss",
            $assetNumber,$serialNumber,$type,$manufacturer,
            $yearModel,$description,$branch,$department,$status,$createdBy);

        if (mysqli_stmt_execute($stmt)) {
            $equipmentId = mysqli_insert_id($link);
            $logSql = "INSERT INTO tblequipmentlogs(datelog,timelog,action,module,performedby,equipmentId,assetNumber)
                       VALUES (?,?,?,?,?,?,?)";
            if ($logStmt = mysqli_prepare($link, $logSql)) {
                $date="d/m/Y"; $time=date("h:i:sa");
                $action="Add equipment"; $module="Equipment Management";
                mysqli_stmt_bind_param($logStmt,"sssssss",
                    $date,$time,$action,$module,$createdBy,$equipmentId,$assetNumber);
                mysqli_stmt_execute($logStmt);
            }
            $_SESSION['success'] = "Equipment successfully added!";
            header("location: equipmentManagement.php"); exit;
        } else {
            $_SESSION['error'] = "Error adding equipment: " . mysqli_error($link);
            header("location: addEquipment.php"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Equipment - Technical Management System</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0f1d3d 0%, #1a2e5e 50%, #0d1a33 100%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 30px 20px;
        }

        .form-wrapper {
            width: 100%;
            max-width: 700px;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .form-container {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.3);
            overflow: hidden;
        }

        /* Header */
        .form-header {
            background: linear-gradient(135deg, #27ae60, #1a7a40);
            padding: 35px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .form-header::before {
            content: '';
            position: absolute;
            top:-50%; left:-50%;
            width:200%; height:200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0%,100% { transform: translateX(-100%) rotate(45deg); }
            50%      { transform: translateX(100%)  rotate(45deg); }
        }

        .header-icon {
            width:70px; height:70px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display:flex; align-items:center; justify-content:center;
            margin: 0 auto 15px;
            border: 3px solid rgba(255,255,255,0.4);
            position:relative; z-index:1;
        }

        .header-icon svg {
            width:38px; height:38px;
            stroke:white; stroke-width:2; fill:none;
        }

        .form-header h2 {
            color:#fff; font-size:26px; font-weight:600;
            margin-bottom:6px; position:relative; z-index:1;
        }

        .form-header p {
            color:rgba(255,255,255,0.88);
            font-size:14px; position:relative; z-index:1;
        }

        /* Body */
        .form-body { padding: 40px; }

        /* Messages */
        .message {
            padding:14px 18px; border-radius:8px;
            margin-bottom:24px; border-left:4px solid;
            font-size:14px; font-weight:500;
            display:flex; align-items:flex-start; gap:12px;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity:0; transform:translateY(-10px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .message svg { width:20px; height:20px; flex-shrink:0; margin-top:2px; }

        .msg-error {
            background: linear-gradient(135deg,#f8d7da,#f5c2c7);
            border-color:#e74c3c; color:#721c24;
        }
        .msg-error svg { stroke:#e74c3c; }

        /* Info notice */
        .info-notice {
            background: linear-gradient(135deg, rgba(39,174,96,0.08), rgba(26,122,64,0.08));
            border-left: 4px solid #27ae60;
            padding: 14px 16px; border-radius:8px;
            margin-bottom:30px; font-size:13px; color:#1a5c32;
            display:flex; gap:12px;
        }

        .info-notice svg {
            width:20px; height:20px;
            stroke:#27ae60; stroke-width:2;
            flex-shrink:0; margin-top:1px;
        }

        /* Section title */
        .section-title {
            font-size:12px; font-weight:700;
            color:#27ae60;
            text-transform:uppercase; letter-spacing:1px;
            margin-bottom:18px; margin-top:8px;
            display:flex; align-items:center; gap:10px;
        }

        .section-title::after {
            content:''; flex:1; height:2px;
            background: linear-gradient(to right, rgba(39,174,96,0.35), transparent);
            border-radius:2px;
        }

        /* Form groups */
        .form-group { margin-bottom:22px; }

        .form-row {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        }

        .form-label {
            display:flex; align-items:center; gap:8px;
            font-size:14px; font-weight:600;
            color:#2c3e50; margin-bottom:9px;
        }

        .form-label svg {
            width:15px; height:15px;
            stroke:#27ae60; stroke-width:2; fill:none;
        }

        .required { color:#e74c3c; font-weight:bold; }

        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width:100%;
            padding:12px 16px;
            border:2px solid #ddd;
            border-radius:8px;
            font-size:14px;
            font-family:inherit;
            transition:all 0.3s ease;
            background:white;
            color:#2c3e50;
        }

        select {
            cursor:pointer;
            appearance:none;
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2327ae60' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat;
            background-position:right 12px center;
            padding-right:40px;
        }

        textarea { resize:vertical; min-height:90px; }

        input[type="text"]:hover,
        input[type="number"]:hover,
        select:hover,
        textarea:hover { border-color:#bbb; }

        input[type="text"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline:none;
            border-color:#27ae60;
            box-shadow:0 0 0 4px rgba(39,174,96,0.12);
        }

        /* Buttons */
        .form-actions {
            display:flex; gap:12px;
            margin-top:32px; padding-top:25px;
            border-top:2px solid #f0f0f0;
        }

        .btn {
            flex:1; padding:14px 24px;
            border:none; border-radius:8px;
            font-size:15px; font-weight:600;
            cursor:pointer; transition:all 0.3s ease;
            display:flex; align-items:center;
            justify-content:center; gap:8px;
            text-decoration:none; font-family:inherit;
        }

        .btn svg { width:20px; height:20px; stroke-width:2; fill:none; }

        .btn-save {
            background: linear-gradient(135deg, #27ae60, #1a7a40);
            color:white;
            box-shadow:0 4px 15px rgba(39,174,96,0.3);
        }

        .btn-save:hover {
            transform:translateY(-2px);
            box-shadow:0 6px 20px rgba(39,174,96,0.4);
        }

        .btn-save:active { transform:translateY(0); }

        .btn-cancel {
            background:rgba(0,0,0,0.05);
            color:#555; border:2px solid #ddd;
        }

        .btn-cancel:hover {
            background:rgba(0,0,0,0.08);
            border-color:#bbb; color:#333;
        }

        @media (max-width:640px) {
            .form-body   { padding:25px 20px; }
            .form-header { padding:28px 20px; }
            .form-row    { grid-template-columns:1fr; gap:0; }
            .form-actions { flex-direction:column; }
        }
    </style>
</head>
<body>
<div class="form-wrapper">
    <div class="form-container">

        <!-- Header -->
        <div class="form-header">
            <div class="header-icon">
                <svg viewBox="0 0 24 24">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8 21h8M12 17v4M12 10V7M9 10h6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h2>Add Equipment</h2>
            <p>Register new equipment into the inventory</p>
        </div>

        <!-- Body -->
        <div class="form-body">

            <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='message msg-error'>
                        <svg viewBox='0 0 24 24' fill='none' stroke='currentColor'>
                            <circle cx='12' cy='12' r='10' stroke-linecap='round' stroke-linejoin='round'/>
                            <path d='M15 9l-6 6M9 9l6 6' stroke-linecap='round' stroke-linejoin='round'/>
                        </svg>
                        <div>{$_SESSION['error']}</div>
                      </div>";
                unset($_SESSION['error']);
            }
            ?>

            <div class="info-notice">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="12" cy="12" r="10" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 16v-4M12 8h.01" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <strong>Note:</strong> Asset Number and Serial Number must be unique. New equipment is automatically registered with <em>WORKING</em> status.
                </div>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">

                <!-- Identification -->
                <div class="section-title">Identification</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <svg viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Asset Number <span class="required">*</span>
                        </label>
                        <input type="text" name="txtAssetNumber" placeholder="e.g., ASSET-001" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <svg viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="8" y="2" width="8" height="4" rx="1" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Serial Number <span class="required">*</span>
                        </label>
                        <input type="text" name="txtSerialNumber" placeholder="e.g., SN123456789" required>
                    </div>
                </div>

                <!-- Equipment Details -->
                <div class="section-title">Equipment Details</div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <svg viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="2" y="3" width="20" height="14" rx="2"/>
                                <path d="M8 21h8M12 17v4" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Type <span class="required">*</span>
                        </label>
                        <select name="cmbType" required>
                            <option value="">-- Select Type --</option>
                            <?php foreach ($equipmentTypes as $t): ?>
                                <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <svg viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="3" y="4" width="18" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16 2v4M8 2v4M3 10h18" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Year Model <span class="required">*</span>
                        </label>
                        <input type="number" name="txtYearModel" placeholder="e.g., 2023" min="1900" max="2100" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <svg viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Manufacturer <span class="required">*</span>
                    </label>
                    <input type="text" name="txtManufacturer" placeholder="e.g., Dell, HP, Lenovo" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <svg viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14 2v6h6M16 13H8m8 4H8m2-8H8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Description
                    </label>
                    <textarea name="txtDescription" placeholder="Optional notes or specifications..."></textarea>
                </div>

                <!-- Location -->
                <div class="section-title">Location</div>

                <div class="form-group">
                    <label class="form-label">
                        <svg viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 22V12h6v10" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Branch <span class="required">*</span>
                    </label>
                    <select name="cmbBranch" required>
                        <option value="">-- Select Branch --</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?php echo $b; ?>"><?php echo $b; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <svg viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="9" cy="7" r="4" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Department <span class="required">*</span>
                    </label>
                    <select name="cmbDepartment" required>
                        <option value="">-- Select Department --</option>
                        <?php foreach ($departments as $d): ?>
                            <option value="<?php echo $d; ?>"><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" name="btnsubmit" class="btn btn-save">
                        <svg viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 21v-8H7v8M7 3v5h8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Save Equipment
                    </button>
                    <a href="equipmentManagement.php" class="btn btn-cancel">
                        <svg viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M18 6 6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
setTimeout(() => {
    document.querySelectorAll('.message').forEach(msg => {
        msg.style.transition = 'opacity 0.4s';
        msg.style.opacity = '0';
        setTimeout(() => msg.remove(), 400);
    });
}, 5000);
</script>

</body>
</html>