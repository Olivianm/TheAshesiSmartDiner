<?php
session_start();
require './../Setting_Folder/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in. Please log in.";
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch user's meal plan balance
$query = "SELECT balance FROM meal_plan_accounts  WHERE user_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($mealPlanBalance);
$stmt->fetch();
$stmt->close();

// Fetch cart total amount
$query = "
    SELECT SUM(m.price * oi.quantity) AS total_amount
    FROM order_items oi
    JOIN menu m ON oi.item_id = m.item_id
    WHERE oi.user_id = ? AND oi.order_id IS NULL
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalAmount);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
</head>
<body>
    <h2>Checkout</h2>
    <p><strong>Total Amount: GHC <?php echo number_format($totalAmount, 2); ?></strong></p>

    <form action="payment.php" method="POST">
        <h4>Select Payment Method:</h4>
        <label>
            <input type="radio" name="payment_method" value="meal_plan" required> Pay with Meal Plan (Balance: GHC <?php echo number_format($mealPlanBalance, 2); ?>)
        </label>
        <br>
        <label>
            <input type="radio" name="payment_method" value="momo" required> Pay with Mobile Money (Follow instructions after checkout)
        </label>
        <br>
        <input type="hidden" name="total_amount" value="<?php echo $totalAmount; ?>">
        <input type="submit" value="Proceed to Payment">
    </form>
</body>
</html>
