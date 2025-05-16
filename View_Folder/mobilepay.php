<?php
session_start();
require './../Setting_Folder/connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Fetch the latest order for the user
$orderQuery = "
    SELECT order_id
    FROM orders
    WHERE user_id = ? 
    ORDER BY order_date DESC
    LIMIT 1
";
$stmt = $connection->prepare($orderQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    die("No recent orders found.");
}

$order_id = $order['order_id'];

// Fetch the order items and calculate total amount (sub_total * quantity)
$totalAmountQuery = "
    SELECT SUM(oi.sub_total * oi.quantity) AS total_amount
    FROM order_items oi
    WHERE oi.order_id = ?
";
$stmt = $connection->prepare($totalAmountQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$totalAmount = $result->fetch_assoc()['total_amount'];
$stmt->close();

// Handle AJAX payment confirmation
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['payer_name'], $_POST['transaction_id'], $_POST['amount'], $_POST['network'])) {
        $payer_name = $_POST['payer_name'];
        $transaction_id = $_POST['transaction_id'];
        $amount = $_POST['amount'] / 100; // Convert from pesewas to GHS
        $network = $_POST['network'];
        $status = 'pending';

        $stmt = $connection->prepare("INSERT INTO payment_confirmations (user_id, network_provider, transaction_id, amount, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issds", $user_id, $network, $transaction_id, $amount, $status);

        if ($stmt->execute()) {
            echo "success";
        } else {
            http_response_code(500);
            echo "Database error: " . $stmt->error;
        }

        $stmt->close();
        $connection->close();
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Mobile Money Payment</title>
  <link href="./../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body { font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 40px; }
    h2 { color: #722F37; }
    .instructions { margin-bottom: 30px; }
    input, select, button {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    button {
        background-color: #722F37;
        color: white;
        border: none;
        font-size: 16px;
    }
    button:hover {
        background-color: #5a242c;
    }
  </style>
</head>
<body>

<a href="studentHome.php" class="btn btn-outline-primary btn-lg m-4" style="color: #722F37">
    <i class="bi bi-arrow-left"></i> Back
</a>

<h2>How to Make a Payment</h2>
<div class="instructions">
    <p><strong>MTN Users:</strong> Dial <code>*170#</code> → Pay Bill → General Payment</p>
    <p><strong>Amount:</strong> GHS <?php echo number_format($totalAmount, 2); ?></p>
    <p><strong>Merchant Name:</strong> SmartDiner</p>
</div>

<form id="paymentForm">
    <label for="payer_name">Your Email:</label>
    <input type="email" name="payer_name" id="payer_email" required>

    <label for="network">Network:</label>
    <select name="network" id="network" required>
        <option value="MTN">MTN</option>
        <option value="Vodafone">Vodafone</option>
        <option value="AirtelTigo">AirtelTigo</option>
    </select>

    <input type="hidden" name="amount" id="amount" value="<?php echo $totalAmount * 100; ?>"> <!-- Paystack uses pesewas -->
    <input type="hidden" name="transaction_id" id="transaction_id">

    <button type="submit" id="paystackBtn">Pay with Paystack</button>
</form>

<script src="https://js.paystack.co/v2/inline.js"></script>
<script>
document.getElementById("paymentForm").addEventListener("submit", function(e) {
    e.preventDefault();

    let email = document.getElementById("payer_email").value;
    let amount = document.getElementById("amount").value;
    let network = document.getElementById("network").value;

    if (!email || !network || !amount) {
        alert("Please fill all fields.");
        return;
    }

    const paystack = PaystackPop.setup({
        key: 'pk_test_462a03cf674e5c9d658a9335697e1d566ceff198', // Replace with your Paystack public key
        email: email,
        amount: amount,
        currency: 'GHS',
        callback: function(response) {
            document.getElementById("transaction_id").value = response.reference;

            // Submit via AJAX to PHP
            $.post(window.location.href, $("#paymentForm").serialize())
                .done(function(data) {
                    alert("Payment successful!");
                    window.location.href = "receipt.php?order_id=<?php echo $order_id; ?>";
                })
                .fail(function(xhr) {
                    alert("Error confirming payment: " + xhr.responseText);
                });
        },
        onClose: function() {
            alert("Transaction was cancelled.");
        }
    });

    paystack.openIframe();
});
</script>
</body>
</html>
