<?php
session_start();
require './../Setting_Folder/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = intval($_POST['order_id']); // Ensure order_id is an integer
    $status = trim($_POST['status']); // Remove unnecessary spaces

    // Allowed statuses to prevent SQL injection or bad data
    $allowed_statuses = ['Pending', 'Processing', 'Completed'];
    if (!in_array($status, $allowed_statuses)) {
        $_SESSION['message'] = "Invalid status selected.";
        header("Location: orders.php");
        exit();
    }

    // Prepare the update query
    $query = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $connection->prepare($query);

    if ($stmt) {
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "✅ Order #$order_id status updated to '$status'.";
        } else {
            $_SESSION['message'] = "❌ Error updating order: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "❌ Failed to prepare statement.";
    }

    $connection->close();
} else {
    $_SESSION['message'] = "Invalid request.";
}

header("Location: orders.php");
exit();
