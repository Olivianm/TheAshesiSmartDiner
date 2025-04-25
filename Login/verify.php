<?php
// Database connection
$host = "localhost";
$user = "phpmyadmin";
$password = "P2litmaG";
$dbname = "ashesismartdiner";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if token exists in URL
if (isset($_GET['token'])) {
    $token = $conn->real_escape_string($_GET['token']);

    // Check if token exists in the database
    $check_token_query = "SELECT email FROM users WHERE verification_token = ? AND is_verified = 0";
    $stmt = $conn->prepare($check_token_query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update user status to verified
        $update_query = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("s", $token);

        if ($stmt->execute()) {
            echo "<p style='color: green;'>Email verified successfully! You can now <a href='login.php'>log in</a>.</p>";
        } else {
            echo "<p style='color: red;'>Verification failed. Please try again later.</p>";
        }
    } else {
        echo "<p style='color: red;'>Invalid or expired verification link.</p>";
    }
    $stmt->close();
}

$conn->close();
?>
