<?php
session_start();
require './../Setting_Folder/connection.php';

if (!isset($_POST['payment_method']) || !isset($_SESSION['user_id'])) {
    die("Invalid request.");
}

$userId = $_SESSION['user_id'];
$paymentMethod = $_POST['payment_method'];
$totalAmount = floatval($_POST['total_amount']); // Convert to float
$paymentDate = date("Y-m-d H:i:s");

// Get the user's meal plan balance
$query = "SELECT balance FROM meal_plan_accounts WHERE user_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$mealPlanData = $result->fetch_assoc();
$mealPlanBalance = $mealPlanData ? floatval($mealPlanData['balance']) : 0.00; // Convert to float

if ($paymentMethod == "meal_plan") {
    if ($mealPlanBalance >= $totalAmount) {
        $newBalance = $mealPlanBalance - $totalAmount;

        $updateQuery = "UPDATE meal_plan_accounts SET balance = ? WHERE user_id = ?";
        $stmt = $connection->prepare($updateQuery);
        $stmt->bind_param("di", $newBalance, $userId);
        $stmt->execute();

        $paymentStatus = "Paid";
        $paymentReference = "MealPlan_" . uniqid();
    } else {
        die("Insufficient meal plan balance.");
    }
} else if ($paymentMethod == "momo") {
    // User must manually send money
    $paymentStatus = "Pending";
    $paymentReference = "MoMo_" . uniqid();
} else {
    die("Invalid payment method.");
}

// Insert order into database (fixed column names)
$stmt = $connection->prepare("
    INSERT INTO orders (user_id, total_amount, reference, status, order_date)
    VALUES (?, ?, ?, ?, ?)
");
$stmt->bind_param("idsss", $userId, $totalAmount, $paymentReference, $paymentStatus, $paymentDate);
$stmt->execute();
$orderId = $stmt->insert_id;
$stmt->close();

// Update cart items
$updateCart = $connection->prepare("
    UPDATE order_items SET order_id = ? WHERE user_id = ? AND order_id IS NULL
");
$updateCart->bind_param("ii", $orderId, $userId);
$updateCart->execute();
$updateCart->close();

// Redirect to receipt page
header("Location: receipt.php?order_id=$orderId");
exit();
?>
