<?php
session_start();
include('./../Setting_Folder/connection.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ./../Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch only completed orders
$query = "SELECT order_id, total_amount, status, order_date FROM orders WHERE user_id = ? AND status = 'Completed' ORDER BY order_date DESC";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Completed Purchases</title>
    <link href="./../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <a href="studentHome.php" class="btn btn-outline-primary btn-lg m-4" style="color: #722F37">
          <i class="bi bi-arrow-left"></i> Back
      </a>
      <h3 class="text-center">Completed Purchase History</h3>

        <?php if ($result->num_rows > 0): ?>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>Total Amount (GHC)</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo number_format($row['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo date("d M Y, H:i", strtotime($row['order_date'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center text-muted">No completed purchases found.</p>
        <?php endif; ?>
    </div>

    <script src="./../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
