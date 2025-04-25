<?php
session_start();
require './../Setting_Folder/connection.php';



// Fetch all users
$query = "SELECT user_id, name, email, role_id FROM users";
$result = $connection->query($query);

//Update user role

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $role = $_POST['role_id'];

    $query = "UPDATE users SET role = ? WHERE user_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("si", $role, $userId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "User role updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update user role."]);
    }


}

//Delete user

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];

    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $userId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete user."]);
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar, .main-sidebar { background-color: #800000 !important; }
        .brand-link, .nav-link { color: white !important; }
        .table th, .table td { text-align: center; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-dark">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar elevation-4">
        <a href="#" class="brand-link text-center">
            <span class="brand-text font-weight-bold">Manager Users</span>
        </a>
        <div class="sidebar">
            <nav>
                <ul class="nav nav-pills nav-sidebar flex-column">
                    <li class="nav-item">
                        <a href="./../View_Folder/admin_dashboard.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p> Dashboard </p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content -->
    <div class="content-wrapper">
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header" style="background-color: #800000; color: white;">
                        <h3 class="card-title">User Management</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['user_id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td>
                                            <select class="role-select" data-user-id="<?php echo $row['user_id']; ?>">
                                                <option value="user" <?php echo ($row['role_id'] === 'user') ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo ($row['role_id'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger delete-btn" data-user-id="<?php echo $row['user_id']; ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Update user role
    $(".role-select").change(function() {
        let userId = $(this).data("user-id");
        let newRole = $(this).val();

        $.post("update_user_role.php", { user_id: userId, role: newRole }, function(response) {
            alert(response.message);
        }, "json");
    });

    // Delete user
    $(".delete-btn").click(function() {
        if (confirm("Are you sure you want to delete this user?")) {
            let userId = $(this).data("user-id");

            $.post("delete_user.php", { user_id: userId }, function(response) {
                if (response.success) {
                    alert("User deleted successfully.");
                    location.reload();
                } else {
                    alert("Error deleting user.");
                }
            }, "json");
        }
    });
</script>

</body>
</html>
