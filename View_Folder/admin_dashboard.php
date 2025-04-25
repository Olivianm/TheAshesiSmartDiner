<?php
session_start();
require './../Setting_Folder/connection.php';

// Check if the logged-in user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    die("Unauthorized access.");
}

// Fetch total orders
$queryOrders = "SELECT COUNT(*) AS totalOrders FROM orders";
$resultOrders = $connection->query($queryOrders);
$totalOrders = $resultOrders->fetch_assoc()['totalOrders'];

// Fetch total registered users
$queryUsers = "SELECT COUNT(*) AS totalUsers FROM users";
$resultUsers = $connection->query($queryUsers);
$totalUsers = $resultUsers->fetch_assoc()['totalUsers'];

// Fetch order data for chart
$orderStats = $connection->query("SELECT DATE(order_date) AS order_day, COUNT(*) AS total_orders FROM orders GROUP BY DATE(order_date)");
$chartLabels = [];
$chartValues = [];

while ($row = $orderStats->fetch_assoc()) {
    $chartLabels[] = $row['order_day'];
    $chartValues[] = $row['total_orders'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar, .main-sidebar { background-color: #722F37 !important; }
        .brand-link, .nav-link { color: white !important; }
        .small-box { border-radius: 10px; }
        .bg-info, .bg-success, .bg-warning, .bg-danger { background-color: #722F37 !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="./../Login/login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar elevation-4">
        <a href="#" class="brand-link text-center">
            <span class="brand-text font-weight-bold">Admin Dashboard</span>
        </a>
        <div class="sidebar">
            <nav>
                <ul class="nav nav-pills nav-sidebar flex-column">
                    <li class="nav-item">
                        <a href="./../Action_Folder/add_menu.php" class="nav-link active" target="_blank">
                            <i class="nav-icon fas fa-plus-circle"></i>
                            <p> Add Menu </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./../Action_Folder/menu_update.php" class="nav-link" target="_blank">
                            <i class="nav-icon fas fa-edit"></i>
                            <p> Update Menu </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./../Action_Folder/manage_users.php" class="nav-link" target="_blank">
                            <i class="nav-icon fas fa-users"></i>
                            <p> Manage Users </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./../Action_Folder/orders.php" class="nav-link" target="_blank">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p> Orders </p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="./admin_payments.php" class="nav-link" target="_blank">
                            <i class="nav-icon fas fa-credit-card"></i>
                            <p> Payments </p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- Dashboard Metrics -->
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?php echo $totalOrders; ?></h3>
                                <p>Total Orders</p>
                            </div>
                            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3 style="color: white;"><?php echo $totalUsers; ?></h3>
                                <p style="color: white;">Registered Users</p>
                            </div>
                            <div class="icon"><i class="fas fa-user-plus"></i></div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3>Coming Soon</h3>
                                <p>More Metrics</p>
                            </div>
                            <div class="icon"><i class="fas fa-chart-pie"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Orders Chart -->
                <div class="card">
                    <div class="card-header" style="background-color: #722F37; color: white;">
                        <h3 class="card-title">Daily Orders Overview</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById("ordersChart").getContext("2d");
    const ordersChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: "Orders per Day",
                data: <?php echo json_encode($chartValues); ?>,
                backgroundColor: "rgba(128, 0, 0, 0.2)",
                borderColor: "#800000",
                borderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    stepSize: 1
                }
            }
        }
    });
});
</script>

</body>
</html>
