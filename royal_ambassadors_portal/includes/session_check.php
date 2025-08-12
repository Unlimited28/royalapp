<?php
session_start();

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    // Check which login page to redirect to
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        header("location: ../admin/login.php");
    } else {
        header("location: ../login.php");
    }
    exit;
}
?>
