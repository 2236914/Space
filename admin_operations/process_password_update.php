<?php
session_start();
require_once '../configs/config.php';
require_once 'logger.php';

header('Content-Type: application/json');

try {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Validate input
    if (empty($data['current_password']) || empty($data['new_password']) || empty($data['confirm_password'])) {
        throw new Exception('All fields are required');
    }

    if ($data['new_password'] !== $data['confirm_password']) {
        throw new Exception('New passwords do not match');
    }

    // Get current password from database
    $stmt = $pdo->prepare("SELECT password FROM students WHERE srcode = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception('User not found');
    }

    // Hash the current password input using SHA-256 to compare
    $currentPasswordHash = hash('sha256', $data['current_password']);

    // Compare the hashes
    if ($currentPasswordHash !== $user['password']) {
        throw new Exception('Current password is incorrect');
    }

    // Validate password strength
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/', $data['new_password'])) {
        throw new Exception('Password must contain at least 6 characters, including one letter, one number, and one special character');
    }

    // Hash the new password using SHA-256
    $newPasswordHash = hash('sha256', $data['new_password']);

    // Update password
    $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE srcode = ?");
    $result = $stmt->execute([$newPasswordHash, $_SESSION['user_id']]);

    if ($result) {
        // Log the password change
        $logger = new Logger($pdo);
        $logger->logActivity([
            'srcode' => $_SESSION['user_id'],
            'action' => 'PASSWORD_UPDATE',
            'action_details' => 'Changed account password'
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update password');
    }

} catch (Exception $e) {
    error_log("Password Update Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}