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
$profile_image = $profile_image ? "./../uploads/" . $profile_image : "./../uploads/1738286631_Nshimiyimana Oliver Mushimiyemungu2.JPG";

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
    if ($_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $image_tmp = $_FILES['profileImage']['tmp_name'];
        $image_name = time() . "_" . basename($_FILES['profileImage']['name']);
        $target_dir = __DIR__ . "/../uploads/";
        $target_file = $target_dir . $image_name;

        // Ensure the uploads directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // Move the uploaded file to the target directory
        if (move_uploaded_file($image_tmp, $target_file)) {
            // Update the database with the new image filename
            $update_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
            $update_stmt = $connection->prepare($update_sql);
            $update_stmt->bind_param("si", $image_name, $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['profile_image'] = "./../uploads/" . $image_name;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "Error updating profile image in the database.";
            }
        } else {
            echo "Error moving uploaded file. Check folder permissions.";
        }
    } else {
        echo "Error uploading file. Error code: " . $_FILES['profileImage']['error'];
    }
}

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
        .upload-section {
            margin-top: 20px;
        }
        .ashesi-logo {
            display: block;
            margin: 30px auto;
            width: 200px;
            height: auto;
        }
        .text-center {
            text-align: center;
        }
        .btn btn-info{
            color: #722F37;
        }
        .btn btn-primary{
            color: #722F37;
        }
    </style>
</head>

<body>
    <header id="header" class="header d-flex flex-column align-items-center">
        <div class="profile-img">
            <img id="profileImage" src="<?php echo htmlspecialchars($profile_image) . '?t=' . time(); ?>" alt="Profile Picture">
        </div>

        <div class="upload-section text-center mt-3">
            <form id="uploadForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data">
                <label for="fileInput" class="btn btn-primary" style="color:white;">Upload Profile Picture</label>
                <input type="file" id="fileInput" name="profileImage" accept="image/*" style="display: none;">
                <button type="submit" class="btn btn-secondary mt-2">Save Picture</button>
            </form>
        </div>

        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="https://subscriber.mplan.ashesi.edu.gh" target="_blank" rel="noopener noreferrer">
                    <i class="bi bi-person"></i> Meal Plan
                </a></li>

                <li><a href="./menu.php"><i class="bi bi-file-earmark-text"></i> Menu</a></li>
                <li><a href="./cart.php"><i class="bi bi-cart"></i> Cart</a></li>
                <li><a href="./student_orders.php"><i class="bi bi-info-circle"></i> My Orders</a></li>
                <li><a href="./purchase_meal.php"><i class="bi bi-basket"></i> Purchase Meal</a></li>
                <li><a href="./help.php"><i class="bi bi-question-circle"></i> Help</a></li>
                <li><a href="./../Login/login.php"><i class="bi bi-box-arrow-right"></i> Sign Out</a></li>
            </ul>
        </nav>
    </header>

    <main class="main">
    <img src="./../img/logo.png" alt="Ashesi University Logo" class="ashesi-logo">
    <section class="section text-center">
        <h4>Explore Your Meal Options</h4>

        <a href="http://127.0.0.1:5000/" id="nutritionalInfoBtn" class="btn btn-primary" target="_blank">
            <i class="bi bi-info-circle"></i> Get Nutritional Info</a>

        <a href="http://localhost/AshesiSmartDiner/model/menu_model/templates/index.php" id="aiMenuBtn" class="btn btn-secondary" target="_blank">
            <i class="bi bi-menu-button-wide"></i> View AI-Generated Menu</a>
            
        <a href="./history.php" class="btn btn-info" target="_blank">
            <i class="bi bi-clock-history"></i> View Purchase History</a>
    </section>
</main>


    <span >
        <a href="./stud_dash.php" id="aiMenuBtn" style="align-content:flex-end; position: absolute;top: 8px;
  right: 16px; color: white"><button style="background-color:#722F37 ; color: white; border-radius: 12px; width: 200px;" > Edit Profile </button> </a> 
    </span> 

    <script src="./../assets/vendor/jquery/jquery.min.js"></script>
    <script src="./../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("fileInput").addEventListener("change", function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById("profileImage").src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
