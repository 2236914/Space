<?php
session_start();
require_once '../configs/config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

ob_start();
header('Content-Type: application/json');
error_reporting(0);

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Validate and sanitize inputs
    if (empty($_POST['email']) || empty($_POST['message'])) {
        throw new Exception('All fields are required');
    }

    $srcode = $_SESSION['user_id'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars($_POST['message']);
    $user_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $attachment_data = file_get_contents($_FILES['attachment']['tmp_name']);
        $attachment_name = $_FILES['attachment']['name'];
        $attachment_type = $_FILES['attachment']['type'];
        
        // Insert into database with attachment
        $stmt = $pdo->prepare("
            INSERT INTO support_messages (srcode, email, message, attachment_data, attachment_name, attachment_type)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $srcode,
            $email,
            $message,
            $attachment_data,
            $attachment_name,
            $attachment_type
        ]);
    } else {
        // Insert into database without attachment
        $stmt = $pdo->prepare("
            INSERT INTO support_messages (srcode, email, message)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$srcode, $email, $message]);
    }

    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configure SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'space.creotech@gmail.com';
        $mail->Password = 'ocqlnjhicvemknon';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Set email content
        $mail->setFrom('space.creotech@gmail.com', 'Space Support System');
        $mail->addAddress('space.creotech@gmail.com', 'Space Admin');
        $mail->addReplyTo($email, $user_name);

        $mail->isHTML(true);
        $mail->Subject = 'New Support Message from ' . $user_name;
        $mail->Body = "
            <h2>New Support Message</h2>
            <p><strong>From:</strong> $user_name ($srcode)</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Message:</strong></p>
            <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px;'>
                " . nl2br(htmlspecialchars($message)) . "
            </div>
            <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";

        $mail->send();
        
        ob_clean();
        echo json_encode([
            'status' => 'success',
            'message' => 'Your message has been sent successfully! We will respond to your email shortly.'
        ]);
    } catch (Exception $e) {
        throw new Exception('Email could not be sent. Please try again later.');
    }

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    ob_end_flush();
    exit;
}
?> 