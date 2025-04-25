<?php
session_start();
require './../Setting_Folder/connection.php';

// Fetch orders with user details using prepared statements
$query = "SELECT o.order_id, o.total_amount, o.status, o.order_date, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.user_id ORDER BY o.order_date DESC";
$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>


    <div class="container mt-5">

    
    <a href="./../View_Folder/admin_dashboard.php" class="btn btn-outline-primary btn-lg m-4" style="color: #722F37">
          <i class="bi bi-arrow-left"></i> Back
      </a>
      
        <h2 class="mb-4">Manage Orders</h2>
        <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Total (GHC)</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <form method="POST" action="update_order_status.php" onsubmit="return confirm('Are you sure you want to update this order status?');">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                                <select name="status" class="form-select">
                                    <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Processing" <?php echo $row['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="Completed" <?php echo $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                                <button type="submit" class="btn btn-primary mt-1">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="text-center">No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
