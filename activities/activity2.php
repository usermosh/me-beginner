<html>
    <title>Triangle Calculator with Validation</title>
    <body style="background-color: #d9d9d9;"> 
        <h1><center>Triangle Calculator</center></h1>
        <h3><center><?php echo "Description: Form validation using GET Method"; ?></center></h3>
        <h2><center><?php echo "Activity 2"; ?></center></h2>

        <center>
            <form action="" method="GET">
                Side A: <input type="text" name="sideA"><br><br>
                Side B: <input type="text" name="sideB"><br><br>
                Side C: <input type="text" name="sideC"><br><br>
                Height: <input type="text" name="height"><br><br>

                <input type="submit" name="btnArea" value="Compute Area">
                <input type="submit" name="btnPerimeter" value="Compute Perimeter">
                <input type="submit" name="btnAll" value="Compute All">
            </form>
        </center>
    </body>
</html>

<center>
<?php

function validateField($value, $label) {
    $error = 0;

    if (empty($_GET[$value])) {
        echo "<font color='red'>$label is empty.</font><br>";
        $error++;
    }
    else if (!is_numeric($_GET[$value])) {
        echo "<font color='red'>$label is not numeric.</font><br>";
        $error++;
    }

    return $error;
}

function validateSideC() {
    $c = $_GET['sideC'];
    $a = $_GET['sideA'];
    $b = $_GET['sideB'];

    if ($c <= $a || $c <= $b) {
        echo "<font color='red'>Side C must be larger than Side A and Side B.</font><br>";
        return 1;
    }
    return 0;
}

if (isset($_GET['btnArea'])) {
    $error = 0;

    $error += validateField('sideB', 'Side B');
    $error += validateField('height', 'Height');

    if ($error == 0) {
        $b = $_GET['sideB'];
        $h = $_GET['height'];

        $area = ($b * $h) / 2;

        echo "<font color='blue'>Side B: $b<br>Height: $h<br>
              <font color='purple'>Area: $area</font></font>";
    }
}

if (isset($_GET['btnPerimeter'])) {
    $error = 0;

    $error += validateField('sideA', 'Side A');
    $error += validateField('sideB', 'Side B');
    $error += validateField('sideC', 'Side C');

    if ($error == 0) {
        $error += validateSideC();
    }

    if ($error == 0) {
        $a = $_GET['sideA'];
        $b = $_GET['sideB'];
        $c = $_GET['sideC'];

        $peri = $a + $b + $c;

        echo "<font color='blue'>Side A: $a<br>Side B: $b<br>Side C: $c<br>
              <font color='purple'>Perimeter: $peri</font></font>";
    }
}

if (isset($_GET['btnAll'])) {
    $error = 0;

    $error += validateField('sideA', 'Side A');
    $error += validateField('sideB', 'Side B');
    $error += validateField('sideC', 'Side C');
    $error += validateField('height', 'Height');

    if ($error == 0) {
        $error += validateSideC();
    }

    if ($error == 0) {
        $a = $_GET['sideA'];
        $b = $_GET['sideB'];
        $c = $_GET['sideC'];
        $h = $_GET['height'];

        $area = ($b * $h) / 2;
        $peri = $a + $b + $c;

        echo "<font color='blue'>Side A: $a<br>Side B: $b<br>Side C: $c<br>Height: $h<br>
              <font color='purple'>Area: $area<br>Perimeter: $peri</font></font>";
    }
}

?>
</center>
