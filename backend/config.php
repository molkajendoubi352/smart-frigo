<?php
// Database connection settings
$host     = "localhost";
$user     = "root";
$password = "";
$database = "smartfrigo";

// Connect to the database
$conn = mysqli_connect($host, $user, $password, $database);

// Check if connection worked
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
