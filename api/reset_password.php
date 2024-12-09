<?php
require_once '../configs/config.php';
session_start();

// Ensure no HTML or whitespace before this point
header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token']) && isset($_POST['password'])) {
        // Verify token from session
        if (!isset($_SESSION['reset_token']) || 
            !isset($_SESSION['reset_email']) || 
            !isset($_SESSION['reset_expires']) ||
            $_SESSION['reset_token'] !== $_POST['token'] ||
            $_SESSION['reset_expires'] < time()) {
            throw new Exception('Invalid or expired reset token');
        }

        $email = $_SESSION['reset_email'];
        // Hash password with SHA256 to match your system
        $password = hash('sha256', $_POST['password']);

        // Update password in both tables
        $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE email = ?");
        $stmt->execute([$password, $email]);
        
        $stmt = $pdo->prepare("UPDATE therapists SET password = ? WHERE email = ?");
        $stmt->execute([$password, $email]);

        // Clear reset session data
        unset($_SESSION['reset_token']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_expires']);

        $response['success'] = true;
        $response['message'] = 'Password reset successful';
    } else {
        throw new Exception('Invalid request');
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Ensure only JSON is output
echo json_encode($response);
exit;
?> 