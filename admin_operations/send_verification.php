<?php
require '../configs/config.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    // Get form data
    $email = $_POST['email'];
    $srcode = $_POST['srcode'];
    
    // Format SR-Code to email format
    $formattedSRCode = preg_replace('/^(\d{2})(\d{5})$/', '$1-$2', $srcode);
    $expectedEmail = $formattedSRCode . "@g.batstate-u.edu.ph";
    
    // Validate SR-Code and email matching
    if (strtolower($email) !== strtolower($expectedEmail)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'SR-Code and G-Suite email do not match'
        ]);
        exit;
    }

    // Generate 6-digit verification code
    $verificationCode = sprintf("%06d", mt_rand(1, 999999));
    
    // First, delete any existing unverified codes for this email
    $deleteStmt = $conn->prepare("DELETE FROM email_verifications WHERE email = ? AND is_verified = 0");
    $deleteStmt->bind_param("s", $email);
    $deleteStmt->execute();
    
    // Store new verification code
    $stmt = $conn->prepare("INSERT INTO email_verifications (email, verification_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
    $stmt->bind_param("ss", $email, $verificationCode);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to store verification code");
    }

    // Configure PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'space.creotech@gmail.com';
    $mail->Password = 'qwiqelaivjigouqz';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email content
    $mail->setFrom('space.creotech@gmail.com', 'BSU DRMS');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Email Verification Code';
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #3c6454;'>Email Verification</h2>
            <p>Your verification code is: <strong style='font-size: 24px; color: #3c6454;'>$verificationCode</strong></p>
            <p>This code will expire in 15 minutes.</p>
            <p>If you didn't request this code, please ignore this email.</p>
        </div>";

    $mail->send();
    
    echo json_encode([
        'status' => 'pending_verification',
        'message' => 'Verification code sent successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?> 