<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Triangle Calculator</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #2C3E50;
        padding: 40px;
    }
    .container {
        background: #fff;
        width: 400px;
        margin: auto;
        padding: 25px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.15);
    }
    input[type="text"] {
        width: 100%;
        padding: 8px;
        margin: 8px 0 15px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .buttons input {
        width: 32%;
        padding: 10px;
        margin-top: 5px;
        border: none;
        border-radius: 5px;
        color: #fff;
        cursor: pointer;
    }
    .btn-area { background: #0077cc; }
    .btn-perimeter { background: #28a745; }
    .btn-both { background: #ff8800; }
    .result {
        margin-top: 20px;
        padding: 15px;
        background: #e9ffe9;
        border-left: 5px solid #28a745;
        border-radius: 5px;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Triangle Calculator</h2>

    <form method="post">
        <label>Side A:</label>
        <input type="text" name="sideA" required>

        <label>Side B:</label>
        <input type="text" name="sideB" required>

        <label>Side C:</label>
        <input type="text" name="sideC" required>

        <label>Height:</label>
        <input type="text" name="height" required>

        <div class="buttons">
            <input type="submit" name="calcArea" value="Area" class="btn-area">
            <input type="submit" name="calcPerimeter" value="Perimeter" class="btn-perimeter">
            <input type="submit" name="calcBoth" value="Both" class="btn-both">
        </div>
    </form>

    <?php
    if ($_POST) {
        $a = floatval($_POST['sideA']);
        $b = floatval($_POST['sideB']);
        $c = floatval($_POST['sideC']);
        $h = floatval($_POST['height']);

        $area = ($b * $h) / 2;
        $perimeter = $a + $b + $c;

        echo "<div class='result'><strong>Result:</strong><br>";

        if (isset($_POST['calcArea'])) {
            echo "Area of the triangle: <strong>$area</strong><br>";
        }

        if (isset($_POST['calcPerimeter'])) {
            echo "Perimeter of the triangle: <strong>$perimeter</strong><br>";
        }

        if (isset($_POST['calcBoth'])) {
            echo "Area: <strong>$area</strong><br>";
            echo "Perimeter: <strong>$perimeter</strong><br>";
        }

        echo "</div>";
    }
    ?>
</div>

</body>
</html>
