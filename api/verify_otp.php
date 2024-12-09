<?php
require_once '../configs/config.php';
session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => '', 'token' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && isset($_POST['otp'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $otp = $_POST['otp'];

        error_log("Verifying OTP for email: " . $email); // Debug log

        // Check if OTP exists and is valid
        $stmt = $pdo->prepare("SELECT * FROM password_resets 
                             WHERE email = ? 
                             AND token = ? 
                             AND used = 0 
                             AND expires_at > NOW()
                             ORDER BY created_at DESC 
                             LIMIT 1");
        $stmt->execute([$email, $otp]);
        $reset = $stmt->fetch();

        if ($reset) {
            // Mark OTP as used
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?");
            $stmt->execute([$reset['id']]);

            // Generate reset token
            $resetToken = bin2hex(random_bytes(32));
            
            // Store reset token in session
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_token'] = $resetToken;
            $_SESSION['reset_expires'] = time() + 900; // 15 minutes

            error_log("OTP verified successfully. Reset token: " . $resetToken); // Debug log

            $response['success'] = true;
            $response['message'] = 'OTP verified successfully';
            $response['token'] = $resetToken;
        } else {
            error_log("Invalid OTP attempt"); // Debug log
            $response['message'] = 'Invalid OTP or OTP has expired';
        }
    } else {
        throw new Exception('Invalid request');
    }
} catch (Exception $e) {
    error_log("OTP Verification Error: " . $e->getMessage()); // Debug log
    $response['message'] = 'Error: ' . $e->getMessage();
}

error_log("Response: " . json_encode($response)); // Debug log
echo json_encode($response); 