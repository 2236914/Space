<?php
session_start();
require 'configs/config.php'; // Include your database connection

// Check if student ID is set during login
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

$student_id = $_SESSION['student_id']; // Get the student ID from the session

// Regenerate session ID for security
session_regenerate_id(true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Your Space</title>
    
    <!-- Load Inter font from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif; /* Use Inter font */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full screen height */
            background-color: #fff;
            margin: 0;
        }

        .splash-container {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .splash-container img {
            width: 150px; /* Adjust image size as necessary */
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        h2 {
            margin: 10px 0;
            font-size: 24px;
        }

        .divider {
            border: 1px solid #ccc;
            width: 80%;
            margin: 10px auto;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: #000;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #444;
        }
    </style>
</head>
<body>

    <div class="splash-container">
        <img src="assets/img/splashemoji.png" alt="Your Image" id="splashImage"> <!-- Ensure the path is correct -->
        <h2>Welcome to your Space.</h2>
        <div class="divider"></div>
        <form method="POST" action="students.php">
            <button type="submit">Next</button>
        </form>
    </div>

</body>
</html>
