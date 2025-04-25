<?php
// Include database connection
include('./../Setting_Folder/connection.php');

// Ensure the user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ./../Login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data from the database
$sql = "SELECT name, email, profile_image FROM users WHERE user_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name, $email, $profile_image);
    $stmt->fetch();
} else {
    echo "User not found.";
    exit();
}

// Handle profile image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profileImage'])) {
    if ($_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $profile_image = $_FILES['profileImage'];
        $image_name = time() . "_" . basename($profile_image['name']); // Unique filename using current timestamp
        $image_tmp = $profile_image['tmp_name'];

        // Define the directory to store uploaded images
        $target_dir = "uploads/";
        $target_file = $target_dir . $image_name;

        // Move the uploaded image to the target directory
        if (move_uploaded_file($image_tmp, $target_file)) {
            // Update the profile image in the database
            $update_image_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
            $update_image_stmt = $connection->prepare($update_image_sql);
            $update_image_stmt->bind_param("si", $target_file, $user_id);
            
            if ($update_image_stmt->execute()) {
                // Successfully updated the image
                header("Location: ./../View_Folder/stud_dash.php");
                exit();
            } else {
                echo "Error updating profile image.";
            }
        }
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
  <title>Profile Page</title>
</head>
<body>
  <h1>Profile</h1>

  <div>
    <h3>Name: <?php echo htmlspecialchars($name); ?></h3>
    <p>Email: <?php echo htmlspecialchars($email); ?></p>
    
    <?php if ($profile_image): ?>
        <img src="<?php echo $profile_image; ?>" alt="Profile Picture" style="width: 100px; height: 100px; border-radius: 50%;">
    <?php else: ?>
        <p>No profile picture set.</p>
    <?php endif; ?>

    <form action="./../View_Folder/stud_dash.php" method="post" enctype="multipart/form-data">
        <label for="profileImage">Upload Profile Picture:</label>
        <input type="file" name="profileImage" id="profileImage" accept="image/*">
        <button type="submit">Upload</button>
    </form>
  </div>

</body>
</html>
