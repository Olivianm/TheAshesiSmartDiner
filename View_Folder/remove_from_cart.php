<?php
session_start();
require './../Setting_Folder/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cartId = $_POST['cart_id'];
    $userId = $_SESSION['user_id'];

    $query = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $cartId, $userId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to remove item."]);
    }

    $stmt->close();
}
?>
