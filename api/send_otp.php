<?php
require_once '../configs/config.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        // Generate 6-digit OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        
        // First, invalidate any existing OTPs for this email
        $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0");
        $stmt->execute([$email]);
        
        // Store new OTP in database with 30-minute expiration
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) 
                             VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 30 MINUTE))");
        
        if ($stmt->execute([
            ':email' => $email,
            ':token' => $otp
        ])) {
            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer [$level] : $str");
                };
                $mail->isSMTP();                                         // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server
                $mail->SMTPAuth   = true;                               // Enable SMTP authentication
                $mail->Username   = 'space.creotech@gmail.com';         // SMTP username
                $mail->Password   = 'qwiqelaivjigouqz';             // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // Enable implicit TLS encryption
                $mail->Port       = 587;                                // TCP port to connect to

                // Recipients
                $mail->setFrom('space.creotech@gmail.com', 'Space Admin');
                $mail->addAddress($email);                              // Add a recipient

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <div style='background-color: #f8f9fa; padding: 20px; border-radius: 10px;'>
                            <h2 style='color: #344767; text-align: center;'>Password Reset Request</h2>
                            <p style='color: #344767;'>You have requested to reset your password. Here is your OTP:</p>
                            <div style='background-color: #fff; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;'>
                                <h1 style='color: #344767; margin: 0; letter-spacing: 5px;'>{$otp}</h1>
                            </div>
                            <p style='color: #666; font-size: 14px;'>This OTP will expire in 30 minutes.</p>
                            <p style='color: #666; font-size: 14px;'>If you didn't request this password reset, please ignore this email.</p>
                            <div style='text-align: center; margin-top: 30px; color: #666; font-size: 12px;'>
                                <p>This is an automated email, please do not reply.</p>
                                <p>© " . date('Y') . " Space. All rights reserved.</p>
                            </div>
                        </div>
                    </div>
                ";
                $mail->AltBody = "Your OTP for password reset is: {$otp}\nThis OTP will expire in 30 minutes.";

                error_log("Attempting to send OTP to: " . $email);
                error_log("Generated OTP: " . $otp);

                $mail->send();
                $response['success'] = true;
                $response['message'] = 'OTP has been sent to your email';
                
            } catch (Exception $e) {
                throw new Exception("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            }
        } else {
            throw new Exception('Failed to store OTP');
        }
    } else {
        throw new Exception('Invalid request');
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Password Reset Error: ' . $e->getMessage());
}

echo json_encode($response); 