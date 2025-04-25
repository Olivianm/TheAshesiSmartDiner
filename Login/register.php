<?php
// Start session
session_start();

// Database connection
$host = "localhost";
$user = "phpmyadmin";
$password = "P2litmaG";
$dbname = "ashesismartdiner";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die('<p>Connection failed: ' . $conn->connect_error . '</p>');
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['signup-email'], $_POST['signup-password'], $_POST['signup-username'], $_POST['role'])) {
        $signup_username = trim($_POST['signup-username']);
        $signup_email = trim($_POST['signup-email']);
        $signup_password = $_POST['signup-password'];
        $role = (int)$_POST['role'];

        // Validate Ashesi email
        if (!preg_match('/^[a-zA-Z0-9._%+-]+@ashesi\.edu\.gh$/', $signup_email)) {
            $error_message = 'Only Ashesi students can register. Please use an @ashesi.edu.gh email.';
        } else {
            // Check for existing email
            $check_email_query = "SELECT email FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_email_query);
            $stmt->bind_param("s", $signup_email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error_message = 'Email is already registered. Please use a different email.';
            } else {
                // Hash password
                $hashed_password = password_hash($signup_password, PASSWORD_BCRYPT);

                // Insert user
                $insert_query = "INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("sssi", $signup_username, $signup_email, $hashed_password, $role);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Registration successful! Please log in.';
                    header("Location: ./../Login/login.php");
                    exit();
                } else {
                    $error_message = 'Error: Unable to register. Please try again later.';
                }
            }
            $stmt->close();
        }
    } else {
        $error_message = 'Please fill out all required fields.';
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register Page</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="./login.css" rel="stylesheet">
    <style>
        /* Same styling as before */
    </style>
</head>

<body>

<section class="forms-section">
    
    <div class="form-wrapper is-active">
        <button type="button" class="switcher switcher-login"></button>

        <form class="form form-signup" method="post">
            <!-- Login Link -->
            <p class="register-redirect">Already have an account? <a href="./../Login/login.php">Login</a></p>

            <fieldset>
                <legend style="color: #722F37;">Please enter your details to sign up.</legend>

                <!-- Error Message -->
                <?php if (!empty($error_message)) : ?>
                    <div class="error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <!-- Success Message -->
                <?php if (!empty($success_message)) : ?>
                    <div class="success-message">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <div class="input-block">
                    <label for="signup-username">Username</label>
                    <input id="signup-username" name="signup-username" type="text" placeholder="Joe Doe" required>
                </div>

                <div class="input-block">
                    <label for="signup-email">E-mail</label>
                    <input id="signup-email" name="signup-email" type="email" placeholder="yourname@ashesi.edu.gh" required>
                </div>

                <div class="input-block">
                    <label for="signup-password">Password</label>
                    <input id="signup-password" name="signup-password" type="password" minlength="8" required>
                </div>

                <div class="input-block">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="2">Student</option>  <!-- Default role -->
                        <option value="1">Admin</option>   <!-- Admin role -->
                    </select>
                </div>
            </fieldset>

            <button type="submit" class="btn-signup" style="font-family: 'Times New Roman', Times, serif; font-size: large;">Sign Up</button>
        </form>
    </div>
    
</section>

</body>
</html>
