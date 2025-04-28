<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
require './../Setting_Folder/connection.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if (!isset($_GET['order_id'])) {
    die("Invalid order.");
}

$orderId = intval($_GET['order_id']);
$userId = $_SESSION['user_id'];

// Fetch order details
$query = "
    SELECT total_amount, reference, status, order_date
    FROM orders
    WHERE order_id = ? AND user_id = ?
";
$stmt = $connection->prepare($query);
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

// Save receipt in database if not already stored
$checkQuery = "SELECT * FROM receipts WHERE order_id = ?";
$stmt = $connection->prepare($checkQuery);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$receiptExists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$receiptExists) {
    $insertQuery = "
        INSERT INTO receipts (order_id, user_id, reference, total_amount, status, order_date)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $stmt = $connection->prepare($insertQuery);
    $stmt->bind_param("iisiss", $orderId, $userId, $order['reference'], $order['total_amount'], $order['status'], $order['order_date']);
    if ($stmt->execute()) {
        echo "Receipt saved successfully.";
    } else {
        die("Error saving receipt: " . $stmt->error);
    }
    $stmt->close();
}

// Fetch items
$query = "
    SELECT m.item_name, m.price, oi.quantity
    FROM order_items oi
    JOIN menu m ON oi.item_id = m.item_id
    WHERE oi.order_id = ?
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $orderId);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; }
        h2 { text-align: center; }
        ul { list-style-type: none; padding: 0; }
        li { margin-bottom: 10px; }
        .receipt-box { border: 1px solid #ddd; padding: 20px; border-radius: 8px; box-shadow: 2px 2px 10px rgba(0,0,0,0.1); }
        button { display: block; width: 100%; padding: 10px; margin-top: 15px; background: #722f37; color: white; border: none; cursor: pointer; border-radius: 5px; }
        button:hover { background: #722f37; }
    </style>
</head>
<body>
    <div class="receipt-box">
        <h2>Receipt</h2>
        <p><strong>Order ID:</strong> <?php echo $orderId; ?></p>
        <p><strong>Payment Reference:</strong> <?php echo htmlspecialchars($order['reference']); ?></p>
        <p><strong>Total Paid:</strong> GHC <?php echo number_format($order['total_amount'], 2); ?></p>
        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
        <p><strong>Date:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>

        <h3>Items Purchased:</h3>
        <ul>
            <?php foreach ($items as $item): ?>
                <li><?php echo htmlspecialchars($item['item_name']); ?> (x<?php echo $item['quantity']; ?>) - GHC <?php echo number_format($item['price'], 2); ?></li>
            <?php endforeach; ?>
        </ul>

        <button onclick="copyReceipt()">Copy Receipt</button>
        <button onclick="finishTransaction()">Done</button>
    </div>

    <script>
        function copyReceipt() {
            let receiptContent = `Order ID: <?php echo $orderId; ?>\n
            Payment Reference: <?php echo htmlspecialchars($order['reference']); ?>\n
            Total Paid: GHC <?php echo number_format($order['total_amount'], 2); ?>\n
            Payment Status: <?php echo htmlspecialchars($order['status']); ?>\n
            Date: <?php echo htmlspecialchars($order['order_date']); ?>\n
            Items Purchased:\n`;

            <?php foreach ($items as $item): ?>
                receiptContent += "- <?php echo htmlspecialchars($item['item_name']); ?> (x<?php echo $item['quantity']; ?>) - GHC <?php echo number_format($item['price'], 2); ?>\n";
            <?php endforeach; ?>

            // Copy to clipboard with error handling
            navigator.clipboard.writeText(receiptContent)
                .then(() => {
                    alert("Receipt copied!");
                    finishTransaction();
                })
                .catch(err => {
                    alert("Failed to copy the receipt: " + err);
                    console.error("Error: ", err);
                });
        }

        function finishTransaction() {
            window.location.href = "purchase_meal.php"; // Make sure this page exists
        }
    </script>
</body>
</html>
