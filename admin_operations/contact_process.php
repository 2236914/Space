<?php
session_start();
require __DIR__ . '/configs/config.php'; // Ensure correct path to config.php

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form inputs and sanitize them
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

    try {
        // Ensure the email is valid before proceeding
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response["message"] = "Invalid email format.";
        } else {
            // Prepare the SQL insert statement
            $stmt = $pdo->prepare("INSERT INTO contact_us (name, email, message) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $message]);

            // Set success response
            $response["success"] = true;
            $response["message"] = "Your message has been sent to Space!";
        }
    } catch (Exception $e) {
        $response["message"] = 'Error: ' . $e->getMessage();
    }
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
