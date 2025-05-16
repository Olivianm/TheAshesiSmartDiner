<?php
session_start();
require './../Setting_Folder/connection.php';

// Ensure user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Unauthorized access.");
}

// Handle status update (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'], $_POST['new_status'])) {
    $payment_id = $_POST['payment_id'];
    $new_status = $_POST['new_status'];

    $stmt = $connection->prepare("UPDATE payment_confirmations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $payment_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_payments.php");
    exit();
}

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=payments_export.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Payer Name', 'Transaction ID', 'Amount', 'Network', 'Status', 'Confirmation Time']);

    $result = $connection->query("SELECT u.name, pc.transaction_id, pc.amount, pc.network_provider, pc.status, pc.created_at 
                                  FROM payment_confirmations pc
                                  JOIN users u ON pc.user_id = u.user_id");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// Apply filters
$filter = "";
if (isset($_GET['filter'])) {
    if ($_GET['filter'] === 'today') {
        $filter = "AND DATE(pc.created_at) = CURDATE()";
    } elseif ($_GET['filter'] === 'week') {
        $filter = "AND YEARWEEK(pc.created_at, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($_GET['filter'] === 'month') {
        $filter = "AND MONTH(pc.created_at) = MONTH(CURDATE()) AND YEAR(pc.created_at) = YEAR(CURDATE())";
    }
}

// Fetch payments
$query = "SELECT pc.id AS payment_id, u.name AS payer_name, pc.transaction_id, pc.amount, pc.network_provider, pc.status, pc.created_at 
          FROM payment_confirmations pc
          JOIN users u ON pc.user_id = u.user_id
          WHERE 1=1 $filter
          ORDER BY pc.created_at DESC";

$stmt = $connection->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Payment Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1000px; 
            margin: auto; 
            padding: 20px;
            padding-top: 70px; /* Added for fixed back button */
        }
        h2 { text-align: center; color: #722F37; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #722F37; color: white; }
        tr:hover { background-color: #f5f5f5; }
        .status-pending { color: orange; }
        .status-completed { color: green; }
        .status-failed { color: red; }
        .action-btn { 
            margin-right: 5px; 
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .approve-btn { background-color: #28a745; color: white; }
        .reject-btn { background-color: #dc3545; color: white; }
        .filter-bar { margin-bottom: 20px; text-align: center; }
        .filter-bar a, .filter-bar form { margin: 0 10px; display: inline-block; }
        .btn-outline-wine {
            color: #722F37;
            border-color: #722F37;
        }
        .btn-outline-wine:hover {
            background-color: #722F37;
            color: white;
        }
        .back-button-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <!-- Fixed Back Button -->
    <div class="back-button-container">
        <a href="./../View_Folder/admin_dashboard.php" class="btn btn-outline-wine">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <h2>Admin Payment Management</h2>

    <div class="filter-bar">
        <strong>Filter by:</strong>
        <a href="?filter=today">Today</a>
        <a href="?filter=week">This Week</a>
        <a href="?filter=month">This Month</a>
        <a href="admin_payments.php">All</a> |
        <a href="?export=csv">Export as CSV</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Payer Name</th>
                <th>Transaction ID</th>
                <th>Amount</th>
                <th>Network</th>
                <th>Status</th>
                <th>Confirmation Time</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($payments) > 0): ?>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= htmlspecialchars($payment['payer_name']) ?></td>
                        <td><?= htmlspecialchars($payment['transaction_id']) ?></td>
                        <td>GHS <?= number_format($payment['amount'], 2) ?></td>
                        <td><?= htmlspecialchars($payment['network_provider']) ?></td>
                        <td class="status-<?= strtolower($payment['status']) ?>">
                            <?= htmlspecialchars($payment['status']) ?>
                        </td>
                        <td><?= htmlspecialchars($payment['created_at']) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="payment_id" value="<?= $payment['payment_id'] ?>">
                                <button class="action-btn approve-btn" name="new_status" value="Completed">Approve</button>
                                <button class="action-btn reject-btn" name="new_status" value="Failed">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align:center;">No payments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>