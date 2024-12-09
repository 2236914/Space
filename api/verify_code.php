<?php
session_start();
require_once '../configs/config.php';

header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $submittedCode = $data['code'];

    if (!isset($_SESSION['temp_registration'])) {
        echo json_encode(['status' => 'error', 'message' => 'Registration session expired. Please try again.']);
        exit;
    }

    $email = $_SESSION['temp_registration']['email'];

    // Check if verification code is valid and not expired
    $stmt = $pdo->prepare("
        SELECT * FROM email_verifications 
        WHERE email = ? 
        AND verification_code = ? 
        AND expires_at > NOW() 
        AND is_verified = FALSE
    ");
    $stmt->execute([$email, $submittedCode]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired verification code.']);
        exit;
    }

    // Begin transaction
    $pdo->beginTransaction();

    try {
        // Mark verification as used
        $stmt = $pdo->prepare("
            UPDATE email_verifications 
            SET is_verified = TRUE 
            WHERE email = ? AND verification_code = ?
        ");
        $stmt->execute([$email, $submittedCode]);

        // Insert user data into database
        $stmt = $pdo->prepare("
            INSERT INTO students (
                srcode, phonenum, email, firstname, lastname, password
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['temp_registration']['srcode'],
            $_SESSION['temp_registration']['phonenum'],
            $_SESSION['temp_registration']['email'],
            $_SESSION['temp_registration']['firstname'],
            $_SESSION['temp_registration']['lastname'],
            $_SESSION['temp_registration']['password']
        ]);

        // Get the generated username
        $stmt = $pdo->prepare("
            SELECT username 
            FROM students 
            WHERE srcode = ?
        ");
        $stmt->execute([$_SESSION['temp_registration']['srcode']]);
        $result = $stmt->fetch();
        $username = $result['username'];

        $pdo->commit();

        // Return success with the generated username
        echo json_encode([
            'status' => 'success',
            'message' => 'Registration successful! Your account has been created.',
            'data' => [
                'username' => $username
            ]
        ]);

        // Clear the temporary registration data
        unset($_SESSION['temp_registration']);

    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?> 