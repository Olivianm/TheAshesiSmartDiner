<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ashesi Smart Diner</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }

        /* Background and layout */
        body {
            background: linear-gradient(
                rgba(133, 106, 106, 0.6),
                rgba(230, 166, 166, 0.6)
            ),
            url('./img/ashesicampus.jpg') no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            position: relative;
        }

        .landing-container {
            max-width: 700px;
            width: 90%;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        /* Back Arrow Styling */
        .back-arrow {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 30px;
            color: #722F37;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .back-arrow:hover {
            transform: translateX(-5px);
            color: #722F37;
        }

        /* Logo Styling */
        .logo img {
            max-height: 150px;
        }

        /* Title Styling */
        h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 15px;
            color: #722F37;
        }

        /* Subtitle Styling */
        p.subtitle {
            font-size: 1.2rem;
            margin-bottom: 25px;
            color: #ddd; 
        }

        /* Call to Action Buttons */
        .btn-container {
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            margin: 10px;
            padding: 14px 35px;
            font-size: 1rem;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            background: #722F37;
            border-radius: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            background: #5a242b;
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3);
        }

    </style>
</head>
<body>

   
    <div class="landing-container">
        <!-- Logo Section -->
        <div class="logo">
            <img src="./img/Ashesi5.png" alt="Ashesi Logo">
        </div>

        <!-- Title Section -->
        <h1>Ashesi Smart Diner</h1>
        <p class="subtitle">Your one-stop solution for campus dining.</p>
       
        <!-- Call to Action Buttons -->
        <div class="btn-container">
            <a href="./Login/login.php " class="btn">Login</a>
            <a href="./Login/register.php" class="btn">Sign Up</a>
        </div>
    </div>

</body>
</html>
