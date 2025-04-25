
<?php
// Define database connection parameters
$host = "localhost";
$user = "phpmyadmin";
$password = "P2litmaG";
$dbname = "ashesismartdiner";

// Create connection
$connection = new mysqli($SERVER, $USERNAME, $PASSWORD, $DB_NAME);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
else{
    echo "";
}
?>

