<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "ashesismartdiner";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Function to handle login logic
function handleLogin($email, $password, $conn) {
    // Restrict login to Ashesi emails only
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@ashesi\.edu\.gh$/', $email)) {
        return ["status" => "error", "message" => "Only Ashesi emails are allowed."];
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT user_id, password, role_id, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password, $role_id, $email);
    $stmt->fetch();
    $stmt->close();

    // If the user is found and password matches
    if ($user_id) {
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role_id'] = $role_id;
            $_SESSION['email'] = $email;
            session_write_close();

            // Return success response and redirect based on the role
            $response = [
                "status" => "success",
                "message" => "Login successful!",
                "role_id" => $role_id
            ];

            if ($role_id == 1) {
                $response["redirect_url"] = "./../View_Folder/admin_dashboard.php";
            } elseif ($role_id == 2) {
                $response["redirect_url"] = "./../View_Folder/studentHome.php";
            }

            return $response;
        } else {
            return ["status" => "error", "message" => "Invalid password."];
        }
    } else {
        return ["status" => "error", "message" => "Invalid email or user does not exist."];
    }
}

// Determine the request method and content type
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the request is JSON (AJAX)
    if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
        // Read JSON from request body
        $data = json_decode(file_get_contents("php://input"), true);

        // Check if required fields are set
        if (isset($data['login-email'], $data['login-password'])) {
            $login_email = filter_var($data['login-email'], FILTER_SANITIZE_EMAIL);
            $login_password = $data['login-password'];

            // Handle login
            $response = handleLogin($login_email, $login_password, $conn);

            // Send JSON response
            echo json_encode($response);
            exit();
        } else {
            // Missing required fields
            echo json_encode(["status" => "error", "message" => "Please fill out all required fields."]);
            exit();
        }
    } else {
        // Handle traditional form submission
        if (isset($_POST['login-email'], $_POST['login-password'])) {
            $login_email = filter_var($_POST['login-email'], FILTER_SANITIZE_EMAIL);
            $login_password = $_POST['login-password'];

            // Handle login
            $response = handleLogin($login_email, $login_password, $conn);

            // Check if the response indicates a successful login
            if ($response['status'] === 'success') {
                // Redirect to the appropriate page based on role
                header("Location: " . $response['redirect_url']);
                exit();
            } else {
                // Display error message
                $error_message = $response['message'];
            }
        } else {
            // Missing required fields
            $error_message = "Please fill out all required fields.";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Login Page</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="./login.css" rel="stylesheet">
    <style>
        body {
            background-image: url('./../img/ashesicampus.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Times New Roman', Times, serif;
            font-size: medium;
        }
        .forms-section {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            align-content: center;
        }
        .forms {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            width: 350px;
            text-align: center;
        }
        .register-redirect {
            text-align: center;
            margin-bottom: 10px;
        }
        .register-redirect a {
            color: #722F37;
            text-decoration: none;
        }
        .register-redirect a:hover {
            text-decoration: underline;
        }
        .input-block {
            margin-bottom: 15px;
        }
        .input-block label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .input-block input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #722F37;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #9e4f5f;
        }
        .error-message {
            color: #e74c3c;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>


<body>

<section class="forms-section">
    <div class="form-wrapper is-active">
        <button type="button" class="switcher switcher-login">
            <b>Login</b>
            <span class="underline"></span>
        </button>

        <form class="form form-login" id="login-form">
    <p class="register-redirect">Don't have an account? <a href="./../Login/register.php">Register</a></p>

    <fieldset>
        <legend style="color: #722F37;">Please enter your Ashesi email and password for login.</legend>

        <!-- Error Message Container (inside form) -->
        <div class="error-message" id="error-message" style="display: none;"></div>

        <div class="input-block">
            <label for="login-email">E-mail</label>
            <input id="login-email" name="login-email" type="email" placeholder="yourname@ashesi.edu.gh" required>
        </div>
        <div class="input-block">
            <label for="login-password">Password</label>
            <input id="login-password" name="login-password" type="password" placeholder="**********" minlength="8" required>
        </div>
    </fieldset>

    <button type="submit" class="btn-login">Login</button>
</form>

    </div>
</section>

<script>
// Handle form submission via AJAX
document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault();

    var email = document.getElementById('login-email').value;
    var password = document.getElementById('login-password').value;

    var data = {
        "login-email": email,
        "login-password": password
    };

    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'  // Set Content-Type to application/json
        },
        body: JSON.stringify(data)  // Send the data as JSON
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            // Display error message
            var errorMessageElement = document.getElementById('error-message');
            errorMessageElement.textContent = data.message;
            errorMessageElement.style.display = 'block';
        } else {
            // Redirect based on role
            window.location.href = data.redirect_url;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

</script>

</body>
</html>
