<?php
// Start the session
session_start();

// Include database connection
include('./../Setting_Folder/connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Login/login.php"); // Redirect to login page if not logged in
    exit();
}

// Get the logged-in user's ID
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
    if (empty($profile_image)) {
        $profile_image = "./../uploads/1738286631_Nshimiyimana Oliver Mushimiyemungu2.JPGg"; // Default profile picture
    }
} else {
    echo "User not found.";
    exit();
}

// Handle profile update (Name & Email)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['userName']) && isset($_POST['userEmail'])) {
    $updated_name = trim($_POST['userName']);
    $updated_email = trim($_POST['userEmail']);

    $update_sql = "UPDATE users SET name = ?, email = ? WHERE user_id = ?";
    $update_stmt = $connection->prepare($update_sql);
    $update_stmt->bind_param("ssi", $updated_name, $updated_email, $user_id);

    if ($update_stmt->execute()) {
        header("Location: ./stud_dash.php");
        exit();
    } else {
        echo "Error updating profile.";
    }
}

// Handle cropped profile image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['croppedImageData'])) {
    $cropped_image_data = $_POST['croppedImageData'];

    if (!empty($cropped_image_data)) {
        list($type, $cropped_image_data) = explode(';', $cropped_image_data);
        list(, $cropped_image_data) = explode(',', $cropped_image_data);
        $cropped_image_data = base64_decode($cropped_image_data);
        
        $image_name = "profile_" . time() . ".png";
        $target_file = "./../uploads/" . $image_name;

        if (file_put_contents($target_file, $cropped_image_data)) {
            $update_image_sql = "UPDATE users SET profile_image = ? WHERE user_id = ?";
            $update_image_stmt = $connection->prepare($update_image_sql);
            $update_image_stmt->bind_param("si", $target_file, $user_id);

            if ($update_image_stmt->execute()) {
                header("Location: ./stud_dash.php");
                exit();
            } else {
                echo "Error updating profile image in the database.";
            }
        } else {
            echo "Error saving cropped image.";
        }
    }
}

// Close connection
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Student Dashboard</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">

  <style>
    .upload-section { margin-top: 20px; color: #722F37;}
    #profileImage {
            max-width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto;
        }
    .edit-profile-form { display: none; }
    .text-center { text-align: center; }
    #cropperContainer { display: none; text-align: center; margin-top: 20px; }
    #croppedImagePreview { max-width: 20%; }
  </style>
</head>
<body>
  <a href="studentHome.php" class="btn btn-outline-primary btn-lg m-4" style="color: #722F37">
          <i class="bi bi-arrow-left"></i> Back
      </a>

  <main class="container">

    <div class="profile-img text-center">
      <img id="profileImage" src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Picture">
    </div>

    <div class="upload-section text-center mt-3" style="color:#722F37" >
      <label for="fileInput" class="btn btn-primary">Choose Profile Picture</label>
      <input type="file" id="fileInput" accept="image/*" style="display: none; color:#722F37">

      <div id="cropperContainer">
        <img id="croppedImagePreview">
        <button id="cropImageBtn" class="btn btn-success mt-2" style="background-color: #722F37">Crop & Save</button>
      </div>

      <form id="uploadForm" action="stud_dash.php" method="POST">
        <input type="hidden" name="croppedImageData" id="croppedImageData">
      </form>
    </div>

    <div class="dashboard-info text-center mt-4 p-3 border rounded shadow-sm bg-light">
    <h4 id="displayName" class="fw-bold mb-2"><?php echo htmlspecialchars($name); ?></h4>
    <p class="mb-1"><strong>ID:</strong> <?php echo htmlspecialchars( $user_id); ?></p>
    <p class="mb-3"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <button id="editButton" class="btn" style="background-color: #722F37; color: #fff; border: 1px solid #722F37; text-align:center">
    Edit Profile
    </button>

</div>
    <div class="edit-profile-form" id="editProfileSection">
      <form id="editProfileForm" action="stud_dash.php" method="post">
        <div class="mb-3">
          <label for="userName" class="form-label">Name</label>
          <input type="text" class="form-control" id="userName" name="userName" value="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="mb-3">
          <label for="userEmail" class="form-label">Email</label>
          <input type="email" class="form-control" id="userEmail" name="userEmail" value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="text-center">
          <button type="submit" class="btn btn-success">Save Changes</button>
          <button type="button" id="cancelButton" class="btn btn-danger">Cancel</button>
        </div>
      </form>
    </div>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      let cropper;
      const fileInput = document.getElementById("fileInput");
      const croppedImagePreview = document.getElementById("croppedImagePreview");
      const cropperContainer = document.getElementById("cropperContainer");
      const croppedImageDataInput = document.getElementById("croppedImageData");
      const uploadForm = document.getElementById("uploadForm");

      fileInput.addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function (e) {
            croppedImagePreview.src = e.target.result;
            cropperContainer.style.display = "block";
            if (cropper) cropper.destroy();
            cropper = new Cropper(croppedImagePreview, { aspectRatio: 1, viewMode: 1 });
          };
          reader.readAsDataURL(file);
        }
      });

      document.getElementById("cropImageBtn").addEventListener("click", function () {
        if (cropper) {
          const croppedCanvas = cropper.getCroppedCanvas();
          croppedImageDataInput.value = croppedCanvas.toDataURL();
          uploadForm.submit();
        }
      });
    });
  </script>
  <script>
  document.addEventListener("DOMContentLoaded", function () {
      const editButton = document.getElementById("editButton");
      const editProfileSection = document.getElementById("editProfileSection");
      const cancelButton = document.getElementById("cancelButton");

      editButton.addEventListener("click", function () {
          editProfileSection.style.display = "block";
          editButton.style.display = "none"; // Hide Edit button when form is visible
      });

      cancelButton.addEventListener("click", function () {
          editProfileSection.style.display = "none";
          editButton.style.display = "block"; // Show Edit button again when form is hidden
      });
  });
</script>


</body>
</html>
