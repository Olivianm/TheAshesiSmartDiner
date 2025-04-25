<?php
// Define database connection parameters
$host = "localhost";
$user = "root";
$password = "";
$dbname = "ashesismartdiner";

// Create connection
$connection = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} else {
    echo "";  // You can remove this line if you don't want any output when connection is successful
}
?>
