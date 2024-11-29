<?php
require_once '../configs/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = array('status' => 'error', 'message' => '');

    try {
        // Validate inputs
        if (empty($_POST['firstName']) || empty($_POST['lastName']) || 
            empty($_POST['email']) || empty($_POST['message'])) {
            throw new Exception('All fields are required');
        }

        // Sanitize inputs
        $firstName = filter_var($_POST['firstName'], FILTER_SANITIZE_STRING);
        $lastName = filter_var($_POST['lastName'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email address');
        }

        // Save to database
        $stmt = $pdo->prepare("
            INSERT INTO contacts (first_name, last_name, email, message)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$firstName, $lastName, $email, $message]);

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        // Configure SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'space.creotech@gmail.com';
        $mail->Password = 'jzznacwskhtqprzg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Set email content
        $mail->setFrom('space.creotech@gmail.com', 'Space Website');
        $mail->addAddress('space.creotech@gmail.com', 'Space Admin');
        $mail->addReplyTo($email, "$firstName $lastName");

        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Submission';
        $mail->Body = "
            <h2>New Contact Form Message</h2>
            <p><strong>From:</strong> $firstName $lastName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Message:</strong></p>
            <p>$message</p>
        ";

        $mail->send();
        $response['status'] = 'success';
        $response['message'] = 'Message sent successfully!';

    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>