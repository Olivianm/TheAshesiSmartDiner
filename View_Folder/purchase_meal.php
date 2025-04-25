<?php
session_start();

// Database Connection
$host = "localhost";
$user = "phpmyadmin";
$password = "P2litmaG";
$dbname = "ashesismartdiner";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch menu items
$menu_items = [];
$sql = "SELECT item_id, item_name, description, price FROM menu";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $menu_items[] = $row;
}

// Handle "Add to Cart" request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['item_id'])) {
    header("Content-Type: application/json"); // Ensure JSON response

    $user_id = $_SESSION['user_id'];
    $item_id = intval($_POST['item_id']);

    // Prevent duplicate items in cart
    $check_sql = "SELECT * FROM cart WHERE user_id = ? AND item_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ii", $user_id, $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert into cart
        $insert_sql = "INSERT INTO cart (user_id, item_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ii", $user_id, $item_id);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Item successfully added to cart!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error!"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Item is already in your cart!"]);
    }
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ashesi Smart Diner - Purchase Meal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <a href="studentHome.php" class="btn btn-outline-primary btn-lg m-4" style="color: #722F37">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="container">
        <h2 class="text-center my-4">Purchase Meal</h2>
        <div id="menuContainer" class="row">
            <?php foreach ($menu_items as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($item['item_name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                            <h5 class="text-danger" style="color: #722F37"> GHC <?= number_format($item['price'], 2) ?></h5>
                            <button class="btn add-to-cart-btn" style="background-color: #722F37; color: white;" 
                                data-item-id="<?= $item['item_id'] ?>">Add to Cart</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="cartMessage" class="text-center alert mt-3" style="display: none;"></div>
    </div>

    <script>
        $(document).ready(function() {
            $('.add-to-cart-btn').click(function() {
                let itemId = $(this).data('item-id');
                let button = $(this);

                $.ajax({
                    url: 'purchase_meal.php',
                    type: 'POST',
                    data: { item_id: itemId },
                    dataType: 'json',
                    success: function(response) {
                        let cartMessage = $('#cartMessage');
                        cartMessage.text(response.message).show().removeClass("alert-success alert-danger");

                        if (response.success) {
                            cartMessage.addClass("alert-success");
                            button.prop('disabled', true).text('Added');
                        } else {
                            cartMessage.addClass("alert-danger");
                        }

                        setTimeout(() => cartMessage.hide(), 3000);
                    },
                    error: function() {
                        alert("An error occurred. Please try again.");
                    }
                });
            });
        });
    </script>
</body>
</html>
