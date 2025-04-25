<?php
session_start();
require './../Setting_Folder/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $cartId = $_POST['cart_id'];
    $quantity = intval($_POST['quantity']);
    $userId = $_SESSION['user_id'];

    // Ensure quantity is valid
    if ($quantity < 1) {
        echo json_encode(["success" => false, "message" => "Invalid quantity."]);
        exit;
    }

    $query = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("iii", $quantity, $cartId, $userId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update quantity."]);
    }

    $stmt->close();
}
?>
