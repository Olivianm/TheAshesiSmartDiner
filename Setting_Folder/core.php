<?php
// Start a session
session_start();

// Function to check if the user is logged in
function checkLogin() {
    // Check if the user ID session exists
    if (!isset($_SESSION['user_id'])) {
        // Redirect to the login_view page
        header("Location: ./../Login/login.php");
        die();
    }
}

// Calling the checkLogin function to perform the login check
checkLogin();


/**
 * Function to check the user's role
 * Returns the role ID (e.g., 1 for Admin, 2 for User)
 */
function check_user_role($db) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    // Fetch the user's role from the database using their session user_id
    $stmt = $db->prepare("SELECT role_id FROM Users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();

    if ($result && isset($result['role_id'])) {
        $_SESSION['role_id'] = $result['role_id']; // Store role ID in session for future use
        return $result['role_id'];
    } else {
        return false; // Role not found
    }
}

/**
 * Function to get the user's role directly from the session
 */
function get_user_role() {
    if (isset($_SESSION['role_id'])) {
        return $_SESSION['role_id'];
    }
    return null; // Role ID not set
}

// Call the checkLogin function to ensure the user is logged in
checkLogin();
?>