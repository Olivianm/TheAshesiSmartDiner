<?php
// Your database connection
require './../Setting_Folder/connection.php';

// Query to fetch menu items
$query = "SELECT * FROM menu";
$result = $connection->query($query);

// If there are no items, return an empty response
if ($result->num_rows == 0) {
    echo json_encode(["items" => []]);
    exit;
}

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

// Output items as JSON
echo json_encode(["items" => $items]);
?>
