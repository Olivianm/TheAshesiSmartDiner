<?php
session_start();
require './../Setting_Folder/connection.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {

    // Redirect to login page if not an admin
    header("Location: ./../Login/login.php");
    exit();
}



// Get user information
$user_id = $_SESSION['user_id'];

// Fetch profile image (for admin)
$sql = "SELECT profile_image FROM users WHERE user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_image);
$stmt->fetch();
$stmt->close();

$profile_image = $profile_image ?: "default-profile.png"; // Default image if none found

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
    if ($_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $profile_image = $_FILES['profileImage'];
        $image_name = time() . "_" . basename($profile_image['name']);
        $image_tmp = $profile_image['tmp_name'];

        // Store the image in the uploads folder
        $target_dir = "./../uploads/";
        $target_file = $target_dir . $image_name;

        if (!is_dir($target_dir)) {
            echo "Directory does not exist: $target_dir";
            exit();
        }

        if (move_uploaded_file($image_tmp, $target_file)) {
            $update_image_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
            $update_image_stmt = $connection->prepare($update_image_sql);
            $update_image_stmt->bind_param("si", $image_name, $user_id);
            if ($update_image_stmt->execute()) {
                // Redirect to refresh the page after uploading
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "Error updating profile image.";
            }
        } else {
            echo "Error uploading file. Check folder permissions.";
        }
    } else {
        echo "Error uploading file. Error code: " . $_FILES['profileImage']['error'];
    }
}

// Fetch menu items from the database
$menuItemsQuery = $connection->query("SELECT * FROM menu");
$menuItems = $menuItemsQuery->fetch_all(MYSQLI_ASSOC);

// Handle adding a new menu item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addMenuItem'])) {
    $item_name = $_POST['item_name'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $addItemQuery = $connection->prepare("INSERT INTO menu (item_name, price, description) VALUES (?, ?, ?)");
    $addItemQuery->bind_param("sis", $item_name, $price, $description);
    $addItemQuery->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Handle deleting a menu item
if (isset($_GET['deleteItemId'])) {
    $item_id = $_GET['deleteItemId'];
    $deleteItemQuery = $connection->prepare("DELETE FROM menu WHERE item_id = ?");
    $deleteItemQuery->bind_param("i", $item_id);
    $deleteItemQuery->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Handle updating order status
if (isset($_POST['updateOrderStatus'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $updateOrderQuery = $connection->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $updateOrderQuery->bind_param("si", $status, $order_id);
    $updateOrderQuery->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
}

// Fetch customer orders
$ordersQuery = $connection->query("SELECT * FROM orders");
$orders = $ordersQuery->fetch_all(MYSQLI_ASSOC);

// Close the database connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Admin Dashboard</title>
    <link href="./../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="./../CSS_Folder/main.css" rel="stylesheet">
    <style>
        #profileImage {
            max-width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }

        .upload-section {
            margin-top: 20px;
        }

        .text-center {
            text-align: center;
        }

        .menu-item {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }

        .order-item {
            border: 1px solid #ddd;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <header id="header" class="header d-flex flex-column align-items-center">
        <div class="profile-img">
            <img id="profileImage" src="./../uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
        </div>
        <div class="upload-section text-center mt-3">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                <label for="fileInput" class="btn btn-primary">Upload Profile Picture</label>
                <input type="file" id="fileInput" name="profileImage" accept="image/*" style="display: none;">
                <button type="submit" class="btn btn-secondary mt-2">Save Picture</button>
            </form>
        </div>
    </header>

    <main class="main">
        <h1 class="text-center">Admin Dashboard</h1>
        <section class="section">
            <h4>Manage Menu</h4>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-group">
                    <input type="text" name="item_name" placeholder="Item Name" class="form-control" required>
                </div>
                <div class="form-group">
                    <input type="number" name="price" placeholder="Price" class="form-control" required>
                </div>
                <div class="form-group">
                    <textarea name="description" placeholder="Description" class="form-control" required></textarea>
                </div>
                <button type="submit" name="addMenuItem" class="btn btn-success">Add Menu Item</button>
            </form>

            <div class="menu-items mt-4">
                <?php foreach ($menuItems as $menuItem): ?>
                    <div class="menu-item">
                        <h5><?php echo htmlspecialchars($menuItem['item_name']); ?></h5>
                        <p><?php echo htmlspecialchars($menuItem['description']); ?></p>
                        <p>Price: GHC <?php echo htmlspecialchars($menuItem['price']); ?></p>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?deleteItemId=<?php echo $menuItem['item_id']; ?>" class="btn btn-danger">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section mt-5">
            <h4>Manage Orders</h4>
            <div class="order-items">
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <h5>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h5>
                        <p>Status: <?php echo htmlspecialchars($order['status']); ?></p>
                        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                            <select name="status" class="form-control">
                                <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="Completed" <?php echo $order['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="updateOrderStatus" class="btn btn-primary mt-2">Update Status</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script src="./../assets/vendor/jquery/jquery.min.js"></script>
    <script src="./../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>