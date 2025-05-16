<?php
require './../Setting_Folder/connection.php';

// Handle Add Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $itemName = $_POST['item_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $categoryId = $_POST['category_id'];

    $sql = "INSERT INTO menu (item_name, description, price, quantity, category_id) 
            VALUES ('$itemName', '$description', '$price', '$quantity', '$categoryId')";

    if ($connection->query($sql)) {
        echo "<script>alert('Item added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding item: " . $connection->error . "');</script>";
    }
}

// Handle Update Item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $itemId = $_POST['item_id'];
    $itemName = $_POST['item_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $categoryId = $_POST['category_id'];

    $sql = "UPDATE menu SET item_name = '$itemName', description = '$description', price = '$price', 
            quantity = '$quantity', category_id = '$categoryId' WHERE item_id = '$itemId'";

    if ($connection->query($sql)) {
        echo "<script>alert('Item updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating item: " . $connection->error . "');</script>";
    }
}

// Handle Delete Item
if (isset($_GET['delete_item'])) {
    $itemId = $_GET['delete_item'];

    $sql = "DELETE FROM menu WHERE item_id = '$itemId'";

    if ($connection->query($sql)) {
        echo "<script>alert('Item deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting item: " . $connection->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Manage Menu Items</title>

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&family=Pacifico&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="./../lib/animate/animate.min.css" rel="stylesheet">
    <link href="./../lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="./../lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="./../CSS_Folder/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="./../CSS_Folder/css/style.css" rel="stylesheet">
    
    <style>
        .btn-wine {
            background-color: #722F37;
            border-color: #722F37;
            color: white;
        }
        .btn-wine:hover {
            background-color: #5a252c;
            border-color: #5a252c;
            color: white;
        }
        .btn-outline-wine {
            color: #722F37;
            border-color: #722F37;
        }
        .btn-outline-wine:hover {
            background-color: #722F37;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container-xxl bg-white p-0">
        <!-- Back to Dashboard Button - Fixed Position -->
        <div class="position-absolute p-4">
            <a href="./../View_Folder/admin_dashboard.php" class="btn btn-outline-wine">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
        
        <!-- Menu Start -->
        <div class="container-xxl py-5">
            <div class="container">
                <div class="text-center wow fadeInUp" data-wow-delay="0.1s">
                    <h5 class="section-title ff-secondary text-center text-primary fw-normal">Manage Menu</h5>
                    <h1 class="mb-5">Add, Update, and Delete Menu Items</h1>
                </div>

                <!-- Add Item Form -->
                <h4>Add New Item</h4>
                <form method="POST" action="" class="mb-5">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="item_name" class="form-label">Item Name</label>
                                <input type="text" class="form-control" id="item_name" name="item_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="1">Breakfast</option>
                            <option value="2">Lunch</option>
                            <option value="3">Dinner</option>
                        </select>
                    </div>
                    <button type="submit" name="add_item" class="btn btn-wine">Add Item</button>
                </form>

                <!-- List All Items -->
                <h4>All Menu Items</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch all menu items and display in table
                        $sql = "SELECT m.item_id, m.item_name, m.description, m.price, m.quantity, c.category_name
                                FROM menu m
                                LEFT JOIN categories c ON m.category_id = c.category_id";
                        $result = $connection->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . $row['item_id'] . "</td>
                                        <td>" . $row['item_name'] . "</td>
                                        <td>" . $row['description'] . "</td>
                                        <td>" . $row['price'] . "</td>
                                        <td>" . $row['quantity'] . "</td>
                                        <td>" . $row['category_name'] . "</td>
                                        <td>
                                            <a href='?delete_item=" . $row['item_id'] . "' class='btn btn-danger btn-sm'>Delete</a>
                                            <a href='update_item.php?item_id=" . $row['item_id'] . "' class='btn btn-wine btn-sm'>Update</a>
                                        </td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7'>No items found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Menu End -->

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-wine btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./../lib/wow/wow.min.js"></script>
    <script src="./../lib/easing/easing.min.js"></script>
    <script src="./../lib/waypoints/waypoints.min.js"></script>
    <script src="./../lib/counterup/counterup.min.js"></script>
    <script src="./../lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="./../lib/tempusdominus/js/moment.min.js"></script>
    <script src="./../lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="./../lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="./../JS_Folder/main.js"></script>
</body>

</html>