<?php
session_start();
require_once '../configs/config.php';
require '../vendor/autoload.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['temp_registration'])) {
        echo json_encode(['status' => 'error', 'message' => 'Registration session expired. Please try again.']);
        exit;
    }

    $email = $_SESSION['temp_registration']['email'];
    $firstname = $_SESSION['temp_registration']['firstname'];

    // Generate new verification code and expiration time
    $verificationCode = sprintf("%06d", mt_rand(100000, 999999));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Update verification code in database
    $stmt = $pdo->prepare("
        INSERT INTO email_verifications (email, verification_code, expires_at) 
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE 
            verification_code = VALUES(verification_code),
            expires_at = VALUES(expires_at),
            is_verified = FALSE
    ");
    $stmt->execute([$email, $verificationCode, $expiresAt]);

    // Send new verification email
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com';
    $mail->Password = 'your-app-password';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('your-email@gmail.com', 'Space Admin');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'New Verification Code - Space';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #333;'>New Verification Code</h2>
            <p>Hello $firstname,</p>
            <p>Here's your new verification code:</p>
            <div style='background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0;'>
                <strong>$verificationCode</strong>
            </div>
            <p>This code will expire in 10 minutes.</p>
            <p>Best regards,<br>Space Team</p>
        </div>
    ";

    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'A new verification code has been sent to your email.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to resend verification code: ' . $e->getMessage()]);
}
?> 