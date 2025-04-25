<?php
session_start();
require './../Setting_Folder/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $paymentMethod = $_POST['payment_method'];  // "mealplan" or "mobilemoney"
    $totalPrice = $_POST['total_price'];

    // Create a new order
    $createOrder = $connection->prepare("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, 'Processing')");
    $createOrder->bind_param("id", $userId, $totalPrice);
    $createOrder->execute();

    $orderId = $connection->insert_id;

    // Move cart items to the order
    $moveCartItems = $connection->prepare("UPDATE order_items SET order_id = ? WHERE user_id = ? AND order_id IS NULL");
    $moveCartItems->bind_param("ii", $orderId, $userId);
    $moveCartItems->execute();

    // Process payment
    if ($paymentMethod === 'mealplan') {
        $paymentStatus = "Meal Plan Charged";
    } else {
        $paymentStatus = "Mobile Money Paid";
    }

    echo json_encode(["success" => true, "message" => "Order placed successfully!", "payment_status" => $paymentStatus]);
}
?>
