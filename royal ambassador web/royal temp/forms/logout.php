<?php
session_start(); // start the session
session_unset(); // clear all session variables
session_destroy(); // destroy the session

// Redirect to index page after logout
header("Location: index.html");
exit();
?>
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>
