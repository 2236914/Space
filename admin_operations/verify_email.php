<?php
require_once '../configs/config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        // Check if email exists in either students or therapists table
        $stmt = $pdo->prepare("SELECT email FROM students WHERE email = ? 
                             UNION 
                             SELECT email FROM therapists WHERE email = ?");
        $stmt->execute([$email, $email]);
        
        if ($stmt->fetch()) {
            $response['success'] = true;
            $response['message'] = 'Email verified successfully';
        } else {
            $response['message'] = 'Email not found in our records';
        }
    } else {
        throw new Exception('Invalid request');
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Email Verification Error: ' . $e->getMessage());
}

echo json_encode($response);