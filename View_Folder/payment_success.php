<?php
session_start();
require './../Setting_Folder/connection.php';

if (!isset($_GET['reference'])) {
    echo "Invalid request.";
    exit;
}

$reference = $_GET['reference'];
$userId = $_SESSION['user_id'];

// Update order status to "paid"
$updateQuery = "UPDATE orders SET status = 'paid', transaction_id = ? WHERE user_id = ? AND status = 'pending'";
$stmt = $connection->prepare($updateQuery);
$stmt->bind_param("si", $reference, $userId);

if ($stmt->execute()) {
    echo "Payment successful! Your order has been confirmed.";
} else {
    echo "Failed to update order.";
}

$stmt->close();
?>
