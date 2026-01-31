<html>
    <title>Lecture 5: HTMl Form Validation</title>
    <body style="background-color: #d9d9d9;;"> 
        <h1>
            <center>Program 6</center>
        </h1>
        <h3>
            <center>
                <?php echo "Description: This program shows on how to process HTML Form Validation"?>
            </center>
        </h3>
            <center>
                <form action="" method="GET">
                    Input number: <input type="text" name="txtinput1"><br><br>
                    Input number: <input type="text" name="txtinput2"><br><br>
                    <input type="submit" name = btnAdd value="Add">
                    <input type="submit" name = btnSubtract value="Subtract">
                    <input type="submit" name = btnMultiply value="Multiply">
                    <input type="submit" name = btnDivide value="Divide">
                    <input type="submit" name = btnAll value="Compute all">
                </form>
            </center>
    </body>
</html>
    <center>
        <?php
            //functions for validations
            function validateInput1() {
                $errorInput1 = 0;
                if(empty($_GET['txtinput1'])) {
                    echo "<font color = 'red'>First number is empty.</font><br>";
                    $errorInput1 ++;
                }
                else if(!is_numeric($_GET['txtinput1'])) {
                    echo "<font color = 'red'>First number is not numeric.</font><br>";
                    $errorInput1 ++;
                }
                else {
                    $errorInput1 = 0;
                }
                return $errorInput1;
            }

            function validateInput2() {
                $errorInput2 = 0;
                if(empty($_GET['txtinput2'])) {
                    echo "<font color = 'red'>Second number is empty.</font><br>";
                    $errorInput2 ++;
                }
                else if(!is_numeric($_GET['txtinput2'])) {
                    echo "<font color = 'red'>Second number is not numeric.</font><br>";
                    $errorInput2 ++;
                }
                else {
                    $errorInput2 = 0;
                }
                return $errorInput2;
            }
            if(isset($_GET['btnAdd'])) {
                $error = 0; // 0 if no error/s, 1 if atleast one input has error, 2 if both inputs have error
                //calling validation functions
                $error += validateInput1();
                $error += validateInput2();
                //process
                if($error == 0) {
                    $num1 = $_GET['txtinput1'];
                    $num2 = $_GET['txtinput2'];
                    $sum = $num1 + $num2;
                    echo "<font color = 'blue'>First number: " . $num1 . "<br>Second number: " . $num2 . "<br><font color = 'purple'>Sum: " . $sum . "</font>";
                }
            }
            if(isset($_GET['btnSubtract'])) {
                $error = 0; // 0 if no error/s, 1 if atleast one input has error, 2 if both inputs have error
                //calling validation functions
                $error += validateInput1();
                $error += validateInput2();
                //process
                if($error == 0) {
                    $num1 = $_GET['txtinput1'];
                    $num2 = $_GET['txtinput2'];
                    $difference = $num1 - $num2;
                    echo "<font color = 'blue'>First number: " . $num1 . "<br>Second number: " . $num2 . "<br><font color = 'purple'>Difference: " . $difference . "</font>";
                }
            }
            if(isset($_GET['btnMultiply'])) {
                $error = 0; // 0 if no error/s, 1 if atleast one input has error, 2 if both inputs have error
                //calling validation functions
                $error += validateInput1();
                $error += validateInput2();
                //process
                if($error == 0) {
                    $num1 = $_GET['txtinput1'];
                    $num2 = $_GET['txtinput2'];
                    $product = $num1 * $num2;
                    echo "<font color = 'blue'>First number: " . $num1 . "<br>Second number: " . $num2 . "<br><font color = 'purple'>Product: " . $product . "</font>";
                }
            }
            if(isset($_GET['btnDivide'])) {
                $error = 0; // 0 if no error/s, 1 if atleast one input has error, 2 if both inputs have error
                //calling validation functions
                $error += validateInput1();
                $error += validateInput2();
                //process
                if($error == 0) {
                    $num1 = $_GET['txtinput1'];
                    $num2 = $_GET['txtinput2'];
                    if($num2 != 0) {
                        $quotient = $num1 / $num2;
                        echo "<font color = 'blue'>First number: " . $num1 . "<br>Second number: " . $num2 . "<br><font color = 'purple'>Quotient: " . $quotient . "</font>";
                    }
                    else {
                        echo "<font color = 'red'>Error: Division by zero is not allowed.</font>";
                    }
                }
            }
            if(isset($_GET['btnAll'])) {
                $error = 0; // 0 if no error/s, 1 if atleast one input has error, 2 if both inputs have error
                //calling validation functions
                $error += validateInput1();
                $error += validateInput2();
                //process
                if($error == 0) {
                    $num1 = $_GET['txtinput1'];
                    $num2 = $_GET['txtinput2'];
                    $sum = $num1 + $num2;
                    $difference = $num1 - $num2;
                    $product = $num1 * $num2;
                    echo "<font color = 'blue'>First number: " . $num1 . "<br>Second number: " . $num2 . "<br><font color = 'purple'>Sum: " . $sum . "<br>Difference: " . $difference . "<br>Product: " . $product;
                    if($num2 != 0) {
                        $quotient = $num1 / $num2;
                        echo "<br>Quotient: " . $quotient . "</font>";
                    }
                    else {
                        echo "<br><font color = 'red'>Error: Division by zero is not allowed.</font></font>";
                    }
                }
            }

        ?>
    </center>