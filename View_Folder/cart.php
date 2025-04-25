<?php
session_start();
require './../Setting_Folder/connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "User not logged in. Please log in.";
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch cart items
$query = "
    SELECT c.cart_id, c.item_id, c.quantity, m.item_name, m.price, m.description 
    FROM cart c
    JOIN menu m ON c.item_id = m.item_id
    WHERE c.user_id = ?
";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$totalAmount = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $totalAmount += $row['price'] * $row['quantity'];
    }
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delivery_option'])) {
    $deliveryOption = $_POST['delivery_option'];
    $status = 'Pending';
    $pickup = ($deliveryOption === 'pickup') ? 1 : 0;

    // Insert order
    $stmt = $connection->prepare("INSERT INTO orders (user_id, order_date, total_price, status, pickup, delivery_option, total_amount) VALUES (?, NOW(), ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssis", $userId, $totalAmount, $status, $pickup, $deliveryOption, $totalAmount);
    $stmt->execute();

    $orderId = $stmt->insert_id;
    $stmt->close();

    // Redirect to payment page with order ID
    header("Location: mobilepay.php?order_id=$orderId");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Your Cart</title>
  <link href="./../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      color: #333;
    }
    .container {
      max-width: 900px;
      margin: auto;
      padding: 20px;
    }
    h2 {
      color: #a7333f;
    }
    .cart-items {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 15px;
      margin-bottom: 30px;
    }
    .cart-item {
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    .cart-item img {
      width: 100%;
      height: 200px;
      object-fit: cover;
      border-radius: 8px;
    }
    .cart-item h5 {
      font-size: 1.2rem;
      color: #333;
    }
    .price {
      font-size: 1.1rem;
      font-weight: bold;
      color: #a7333f;
    }
    .quantity-input {
      width: 60px;
      text-align: center;
    }
    .checkout-btn {
      background-color: #a7333f;
      color: white;
      padding: 12px 30px;
      border-radius: 25px;
      text-align: center;
      display: inline-block;
      margin-top: 20px;
      font-size: 1.1rem;
      width: 100%;
    }
    .remove-btn {
      background-color: #dc3545;
      color: white;
      padding: 8px 15px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      font-size: 0.9rem;
    }
    .empty-cart-message {
      text-align: center;
      color: #888;
      font-size: 1.2rem;
      margin-top: 40px;
    }
  </style>
</head>
<body>
<a href="studentHome.php" class="btn btn-outline-primary btn-lg m-4" style="color: #722F37">
        <i class="bi bi-eqarrow-left"></i> Back
    </a> 
  <div class="container" style="text-align:center">
    <h2>Your Cart</h2>

    <?php if (count($cartItems) > 0): ?>
      <div class="cart-items">
        <?php foreach ($cartItems as $item): ?>
          <div class="cart-item">
            <img src="./../img/menu-<?php echo $item['item_id']; ?>.jpg" alt="<?php echo $item['item_name']; ?>">
            <div class="cart-item-details">
              <h5><?php echo $item['item_name']; ?></h5>
              <p><?php echo $item['description']; ?></p>
              <h5 class="price">GHC <?php echo $item['price']; ?></h5>
              <label>Quantity:</label>
              <input type="number" class="quantity-input" data-cart-id="<?php echo $item['cart_id']; ?>" value="<?php echo $item['quantity']; ?>" min="1">
            </div>
            <button class="remove-btn" data-cart-id="<?php echo $item['cart_id']; ?>">Remove</button>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Delivery Option Form -->
      <form  method="POST">
        <h4>Select Delivery Option:</h4>
        <label>
          <input type="radio" name="delivery_option" value="pickup" required> Pickup
        </label>
        <label>
          <input type="radio" name="delivery_option" value="delivery" required> Delivery
        </label>
        
        <input type="submit" value="Proceed to Payment Instructions" class="checkout-btn">
      </form>

    <?php else: ?>
      <p class="empty-cart-message">Your cart is currently empty. Start shopping now!</p>
    <?php endif; ?>
  </div>

  <script>
    $(document).ready(function () {
        // Remove item from cart
        $(".remove-btn").click(function () {
            let cartId = $(this).data("cart-id");

            $.post("remove_from_cart.php", { cart_id: cartId }, function (response) {
                let data = JSON.parse(response);
                if (data.success) {
                    alert("Item removed from cart!");
                    location.reload();
                } else {
                    alert("Failed to remove item from cart: " + data.message);
                }
            });
        });

        // Update quantity in cart
        $(".quantity-input").change(function () {
            let cartId = $(this).data("cart-id");
            let newQuantity = $(this).val();

            $.post("update_quantity.php", { cart_id: cartId, quantity: newQuantity }, function (response) {
                let data = JSON.parse(response);
                if (!data.success) {
                    alert("Failed to update quantity: " + data.message);
                }
            });
        });
    });
  </script>
</body>
</html>
