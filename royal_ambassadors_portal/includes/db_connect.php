<?php
// Include the config file
require_once __DIR__ . '/config.php';

/* Attempt to connect to MySQL database */
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($mysqli->connect_error){
    // For production, log this error instead of showing it to the user.
    die("ERROR: Could not connect. " . $mysqli->connect_error);
}
?>
