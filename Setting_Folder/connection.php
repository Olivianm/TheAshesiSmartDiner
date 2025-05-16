<?php
// Define database connection parameters
$host = "localhost";
$user = "phpmyadmin";
$password = "P2litmaG@25";
$dbname = "ashesismartdiner";

// Create connection
$connection = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} 
?>
