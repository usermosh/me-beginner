<?php
    //destroy the session 
    session_start();
    unset($_SESSION['username']);
    unset($_SESSION['usertype']);
    //redirect o to login page
    header("location: login.php");

?> 