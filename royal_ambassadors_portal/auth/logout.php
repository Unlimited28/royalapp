<?php
// Initialize the session
session_start();

// Get the role before destroying the session
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to the appropriate login page
if ($role == 'superadmin' || $role == 'president') {
    header("location: ../admin/login.php");
} else {
    header("location: ../login.php");
}
exit;
?>
