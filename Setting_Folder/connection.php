
<?php
// Define database connection parameters
$SERVER = 'localhost';
$USERNAME = 'root';
$PASSWORD = '';
$DB_NAME = 'ashesismartdiner';

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

