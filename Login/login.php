<?php
session_start();

// Database connection
$host = "localhost";
$user = "phpmyadmin";
$password = "P2litmaG@25";
$dbname = "ashesismartdiner";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Function to handle login logic
function handleLogin($email, $password, $conn) {
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@ashesi\.edu\.gh$/', $email)) {
        return ["status" => "error", "message" => "Only Ashesi emails are allowed."];
    }

    $stmt = $conn->prepare("SELECT user_id, password, role_id, email FROM users WHERE email = ?");
    if (!$stmt) {
        return ["status" => "error", "message" => "Query preparation failed."];
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user was found
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stmt->close();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role_id'] = $row['role_id'];
            $_SESSION['email'] = $row['email'];
            session_write_close();

            $response = [
                "status" => "success",
                "message" => "Login successful!",
                "role_id" => $row['role_id']
            ];

            if ($row['role_id'] == 1) {
                $response["redirect_url"] = "./../View_Folder/admin_dashboard.php";
            } elseif ($row['role_id'] == 2) {
                $response["redirect_url"] = "./../View_Folder/studentHome.php";
            }

            return $response;
        } else {
            return ["status" => "error", "message" => "Invalid password."];
        }
    } else {
        $stmt->close();
        return ["status" => "error", "message" => "Invalid email or user does not exist."];
    }
}

// Handle request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_SERVER['CONTENT_TYPE']) &&
        strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
    ) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['login-email'], $data['login-password'])) {
            $login_email = filter_var($data['login-email'], FILTER_SANITIZE_EMAIL);
            $login_password = $data['login-password'];

            $response = handleLogin($login_email, $login_password, $conn);
            echo json_encode($response);
            exit();
        } else {
            echo json_encode(["status" => "error", "message" => "Please fill out all required fields."]);
            exit();
        }
    } else {
        if (isset($_POST['login-email'], $_POST['login-password'])) {
            $login_email = filter_var($_POST['login-email'], FILTER_SANITIZE_EMAIL);
            $login_password = $_POST['login-password'];

            $response = handleLogin($login_email, $login_password, $conn);

            if ($response['status'] === 'success') {
                header("Location: " . $response['redirect_url']);
                exit();
            } else {
                $error_message = $response['message'];
            }
        } else {
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

        <form class="form form-login" id="login-form" method="POST" action="login.php">
            <p class="register-redirect">Don't have an account? <a href="./../Login/register.php">Register</a></p>

            <fieldset>
                <legend style="color: #722F37;">Please enter your Ashesi email and password for login.</legend>

                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

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
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'error') {
            var errorMessageElement = document.getElementById('error-message');
            errorMessageElement.textContent = data.message;
            errorMessageElement.style.display = 'block';
        } else {
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
