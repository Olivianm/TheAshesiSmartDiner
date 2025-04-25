<?php
require './../Setting_Folder/connection.php';

// Check if the item ID is provided
if (isset($_GET['item_id'])) {
    $itemId = $_GET['item_id'];

    // Fetch item details
    $sql = "SELECT * FROM menu WHERE item_id = '$itemId'";
    $result = $connection->query($sql);

    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
    } else {
        echo "<script>alert('Item not found!'); window.location.href='./menu_update.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid request!'); window.location.href='./menu_update.php';</script>";
    exit;
}

// Handle the form submission for updating
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    // Ensure item_id is set in the form submission
    if (!isset($_POST['item_id']) || empty($_POST['item_id'])) {
        echo "<script>alert('Error: Missing item ID!'); window.location.href='./menu_update.php';</script>";
        exit;
    }

    // Get the item ID from the form
    $itemId = $_POST['item_id'];
    $itemName = $_POST['item_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $categoryId = $_POST['category_id'];

    // Update query
    $sql = "UPDATE menu SET 
            item_name = '$itemName',
            description = '$description',
            price = '$price',
            quantity = '$quantity',
            category_id = '$categoryId'
            WHERE item_id = '$itemId'";

    if ($connection->query($sql)) {
        echo "<script>alert('Item updated successfully!'); window.location.href='./menu_update.php';</script>";
        exit;
    } else {
        echo "<script>alert('Error updating item: " . $connection->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Update Item</title>

    <!-- Include the same stylesheets as in the main page -->
    <link href="./../CSS_Folder/bootstrap.min.css" rel="stylesheet">
    <link href="./../CSS_Folder/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-xxl bg-white p-5">
        <h1 class="mb-4">Update Item</h1>
        <form method="POST" action="">
            <!-- Hidden field to keep item_id during form submission -->
            <input type="hidden" name="item_id" value="<?= $itemId; ?>">

            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" value="<?= $item['item_name'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" required><?= $item['description'] ?></textarea>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $item['price'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= $item['quantity'] ?>" required>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="1" <?= $item['category_id'] == 1 ? 'selected' : '' ?>>Breakfast</option>
                    <option value="2" <?= $item['category_id'] == 2 ? 'selected' : '' ?>>Lunch</option>
                    <option value="3" <?= $item['category_id'] == 3 ? 'selected' : '' ?>>Dinner</option>
                </select>
            </div>
            <button type="submit" name="update_item" class="btn btn-primary">Update Item</button>
            <a href="./menu_update.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>

</html>
