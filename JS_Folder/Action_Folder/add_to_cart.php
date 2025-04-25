<?php
session_start();
require './../Setting_Folder/connection.php';

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);

// Validate and sanitize input
if (isset($data['item_id'])) {
    $itemId = intval($data['item_id']);
    $userId = $_SESSION['user_id'];  // Assuming user is logged in and user ID is stored in session

    // Check if the item is already in the cart
    $checkQuery = "SELECT * FROM order_items WHERE user_id = ? AND item_id = ? AND order_id IS NULL";
    $checkStmt = $connection->prepare($checkQuery);
    $checkStmt->bind_param("ii", $userId, $itemId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult && $checkResult->num_rows > 0) {
        // Item already in cart, update the quantity
        $updateQuery = "UPDATE order_items SET quantity = quantity + 1 WHERE user_id = ? AND item_id = ? AND order_id IS NULL";
        $updateStmt = $connection->prepare($updateQuery);
        $updateStmt->bind_param("ii", $userId, $itemId);
        if ($updateStmt->execute()) {
            echo json_encode(['message' => 'Item quantity updated in the cart.']);
        } else {
            echo json_encode(['message' => 'Error updating item in the cart.']);
        }
    } else {
        // Item not in cart, insert it
        $insertQuery = "INSERT INTO order_items (user_id, item_id, quantity) VALUES (?, ?, 1)";
        $insertStmt = $connection->prepare($insertQuery);
        $insertStmt->bind_param("ii", $userId, $itemId);
        if ($insertStmt->execute()) {
            echo json_encode(['message' => 'Item added to the cart.']);
        } else {
            echo json_encode(['message' => 'Error adding item to the cart.']);
        }
    }
} else {
    echo json_encode(['message' => 'Invalid request.']);
}
?>
