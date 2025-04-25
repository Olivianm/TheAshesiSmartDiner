<?php

include "./../Setting_Folder/connection.php";


// Select query on the family_name table
$query = "SELECT * FROM roles";

// Executing the query using the connection
$result = mysqli_query($connection, $query);

// Checking if the execution worked
if ($result) {
    // Fetching the results
    $roles = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
} else {
    // Handle the query execution error
    echo 'Failed to execute the query: ' . mysqli_error($connection);
}
?>



