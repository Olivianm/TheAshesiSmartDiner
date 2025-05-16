<?php
session_start();
include('./../Setting_Folder/connection.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ./../Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch profile image
$sql = "SELECT profile_image FROM users WHERE user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($profile_image_db);
$stmt->fetch();
$stmt->close();

$profile_image_path = $profile_image_db ? "/uploads/" . $profile_image_db : "/uploads/defaultpp.png";
$_SESSION['profile_image'] = $profile_image_path;

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
    if ($_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['profileImage']['tmp_name'];
        $image_name = time() . "_" . basename($_FILES['profileImage']['name']);
        $target_dir = __DIR__ . "/../uploads/";
        $target_file = $target_dir . $image_name;

        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($image_tmp, $target_file)) {
            $update_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("si", $image_name, $user_id);
            if ($update_stmt->execute()) {
                $_SESSION['profile_image'] = "/uploads/" . $image_name;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            echo "Error moving uploaded file.";
        }
    } else {
        echo "Upload error: " . $_FILES['profileImage']['error'];
    }
}

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
            margin: 0 auto;
        }
        .ashesi-logo {
            display: block;
            margin: 30px auto;
            width: 200px;
        }
        .btn-primary, .btn-info, .btn-secondary {
            background-color: #722F37;
            color: white;
            border: none;
        }
        .offcanvas-body ul {
            padding-left: 0;
            list-style: none;
        }
        .offcanvas-body ul li {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<!-- Mobile Nav Toggle -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <button class="btn btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand ms-auto" href="#">Student Portal</a>
    </div>
</nav>

<!-- Sidebar -->
<div class="offcanvas offcanvas-start bg-light" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="sidebarMenuLabel">Navigation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="text-center mb-3">
            <img id="profileImage" src="<?php echo htmlspecialchars($_SESSION['profile_image']) . '?t=' . time(); ?>" alt="Profile Picture">
        </div>
        <form id="uploadForm" action="" method="post" enctype="multipart/form-data" class="text-center mb-3">
            <label for="fileInput" class="btn btn-primary w-100">Upload Profile Picture</label>
            <input type="file" id="fileInput" name="profileImage" accept="image/*" style="display: none;">
            <button type="submit" class="btn btn-secondary mt-2 w-100">Save Picture</button>
        </form>
        <ul>
            <li><a href="https://subscriber.mplan.ashesi.edu.gh" target="_blank"><i class="bi bi-person"></i> Meal Plan</a></li>
            <li><a href="./menu.php"><i class="bi bi-file-earmark-text"></i> Menu</a></li>
            <li><a href="./cart.php"><i class="bi bi-cart"></i> Cart</a></li>
            <li><a href="./student_orders.php"><i class="bi bi-info-circle"></i> My Orders</a></li>
            <li><a href="./purchase_meal.php"><i class="bi bi-basket"></i> Purchase Meal</a></li>
            <li><a href="./help.php"><i class="bi bi-question-circle"></i> Help</a></li>
            <li><a href="./../Login/login.php"><i class="bi bi-box-arrow-right"></i> Sign Out</a></li>
        </ul>
    </div>
</div>

<!-- Main Content -->
<main class="main mt-4 text-center">
    <img src="./../img/logo.png" alt="Ashesi University Logo" class="ashesi-logo">

    <section class="section container">
        <h4>Explore Your Meal Options</h4>

        <a href="https://model-nutri.onrender.com" class="btn btn-primary mt-2" target="_blank">
            <i class="bi bi-info-circle"></i> Get Nutritional Info</a>

        <a href="../model/menu_model/templates/index.html" class="btn btn-secondary mt-2" target="_blank">
            <i class="bi bi-menu-button-wide"></i> View AI-Generated Menu</a>

        <a href="./history.php" class="btn btn-info mt-2" target="_self">
            <i class="bi bi-clock-history"></i> View Purchase History</a>

        <div class="mt-4">
            <a href="./stud_dash.php">
                <button class="btn btn-primary w-100">Edit Profile</button>
            </a>
        </div>
    </section>
</main>

<script src="./../assets/vendor/jquery/jquery.min.js"></script>
<script src="./../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById("fileInput").addEventListener("change", function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById("profileImage").src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
</body>
</html>
