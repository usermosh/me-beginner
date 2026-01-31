<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Lecture 2: Variable Declaration</title>
    <style>
        body { background: #808080; font-family: Arial, Helvetica, sans-serif; color: #fff; }
        .card { max-width: 800px; margin: 2rem auto; background: #333; padding: 1.25rem; border-radius: 8px; }
        h1, h3 { text-align: center; margin: 0.25rem 0; }
        .vars { margin-top: 1rem; background: #222; padding: 0.75rem; border-radius: 6px; }
        .vars p { margin: 0.4rem 0; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Program 2</h1>
        <h3>Description: This program shows how to declare variables in PHP.</h3>

        <?php
            // variable declarations
            $num = 10;
            $pi = 3.14;
            $letter = "A";
            $msg = "hello";
            $result = true;
        ?>

        <div class="vars">
            <p>Variable num contains: <?= htmlspecialchars($num) ?></p>
            <p>Variable pi contains: <?= htmlspecialchars($pi) ?></p>
            <p>Variable letter contains: <?= htmlspecialchars($letter) ?></p>
            <p>Variable msg contains: <?= htmlspecialchars($msg) ?></p>
            <p>Variable result contains: <?= $result ? 'true' : 'false' ?></p>
        </div>
    </div>
</body>
</html>