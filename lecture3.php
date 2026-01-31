<html>
    <title>Lecture 3: HTMl Form Processing</title>
    <body style="background-color: #d9d9d9;"> 
        <h1>
            <center>Program 3</center>
        </h1>
        <h3>
            <center>
                <?php echo "Description: This program shows on how to process HTML Form using POST Method."?>
            </center>
        </h3>
            <center>
                <form action="" method="POST">
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

<div class="result">
    <style>
    .result {
        background-color: #f2f2f2;
        border: 1px solid #ccc;
        padding: 10px;
        margin-top: 20px;
        color: red;
    }
    </style>
    <center>
        <?php
            if(isset($_POST['btnAdd'])){
                // request input
                $num1 = $_POST['txtinput1'];
                $num2 = $_POST['txtinput2'];
                //process
                $result = $num1 + $num2;
                //display
                echo "First number: " . $num1 . "<br>";
                echo "Second number: " . $num2 . "<br>";
                echo "Sum: " . $result;
            }
            if(isset($_POST['btnSubtract'])){
                // request input
                $num1 = $_POST['txtinput1'];
                $num2 = $_POST['txtinput2'];
                //process
                $result = $num1 - $num2;
                //display
                echo "First number: " . $num1 . "<br>";
                echo "Second number: " . $num2 . "<br>";
                echo "Difference: " . $result;
            }
            if(isset($_POST['btnMultiply'])){
                // request input
                $num1 = $_POST['txtinput1'];
                $num2 = $_POST['txtinput2'];
                //process
                $result = $num1 * $num2;
                //display
                echo "First number: " . $num1 . "<br>";
                echo "Second number: " . $num2 . "<br>";
                echo "Product: " . $result;
            }
            if(isset($_POST['btnDivide'])){
                // request input
                $num1 = $_POST['txtinput1'];
                $num2 = $_POST['txtinput2'];
                //process
                if($num2 != 0){
                    $result = $num1 / $num2;
                    //display
                    echo "First number: " . $num1 . "<br>";
                    echo "Second number: " . $num2 . "<br>";
                    echo "Quotient: " . $result;
                } else {
                    echo "Error: Division by zero is not allowed.";
                }
            }
            if(isset($_POST['btnAll'])){
                // request input
                $num1 = $_POST['txtinput1'];
                $num2 = $_POST['txtinput2'];
                //process
                $sum = $num1 + $num2;
                $difference = $num1 - $num2;
                $product = $num1 * $num2;
                if($num2 != 0){
                    $quotient = $num1 / $num2;
                } else {
                    $quotient = "undefined (division by zero)";
                }
                //display
                echo "First number: " . $num1 . "<br>";
                echo "Second number: " . $num2 . "<br>";
                echo "Sum: " . $sum . "<br>";
                echo "Difference: " . $difference . "<br>";
                echo "Product: " . $product . "<br>";
                echo "Quotient: " . $quotient;
            }
        ?>
    </center>
</div>