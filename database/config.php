<?php 
// define  database connection
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'mark');
define('DB_PASSWORD', 'acobado');
define('DB_NAME', 'itc127-cs2a-2026');

// attem pt to connect to database
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

//check if the connecetion is unseccessful
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

// sete time zone 
date_default_timezone_set('Asia/Manila');
?>
