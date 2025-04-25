<?php
// Start the session
session_start();

// Include database connection file
include('./../Setting_Folder/connection.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./../Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile image from the database
$sql = "SELECT profile_image FROM users WHERE user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_image);
$stmt->fetch();
$stmt->close();

// Set a default profile image if none exists
$profile_image = $profile_image ? "./../uploads/" . $profile_image : "./../uploads/default_profile.jpg";

// Fetch student's orders
$order_query = "SELECT order_id, total_amount, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$order_stmt = $connection->prepare($order_query);
$order_stmt->bind_param("i", $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

// Close the database connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Student Profile</title>
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
        .order-table {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <header class="header text-center">
        <div class="profile-img">
            <img id="profileImage" src="<?php echo htmlspecialchars($profile_image) . '?t=' . time(); ?>" alt="Profile Picture">
        </div>
        <h4>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Student'); ?></h4>
    </header>

    <main class="container mt-4">
        <h3 class="text-center">Your Orders</h3>
        <div class="order-table">
            <?php if ($order_result->num_rows > 0): ?>
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Order ID</th>
                            <th>Total (GHC)</th>
                            <th>Status</th>
                            <th>Placed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $order_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo number_format($row['total_amount'], 2); ?></td>
                                <td>
                                    <?php
                                    $status_color = [
                                        'Pending' => 'text-warning',
                                        'Processing' => 'text-primary',
                                        'Completed' => 'text-success'
                                    ];
                                    $color_class = $status_color[$row['status']] ?? 'text-secondary';
                                    ?>
                                    <span class="<?php echo $color_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                </td>
                                <td><?php echo date("d M Y, H:i", strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted">No orders found.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="./../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
