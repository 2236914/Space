<?php
session_start();
require_once '../configs/config.php';
define('ALLOW_ACCESS', true);
require_once 'profile_operations.php';
require_once 'logger.php';

header('Content-Type: application/json');

try {
    // Initialize ProfileOperations and Logger
    $profileOps = new ProfileOperations($pdo);
    $logger = new Logger($pdo);

    // Debug: Log the received data
    error_log('Received POST data: ' . print_r($_POST, true));
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get form data with validation
    $studentData = [
        'firstname' => trim($_POST['firstname'] ?? ''),
        'lastname' => trim($_POST['lastname'] ?? ''),
        'phonenum' => trim($_POST['phonenum'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'department' => trim($_POST['department'] ?? ''),
        'course' => trim($_POST['course'] ?? ''),
        'year' => trim($_POST['year'] ?? ''),
        'section' => trim($_POST['section'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'personality' => trim($_POST['personality'] ?? ''),
        'srcode' => $_SESSION['user_id']
    ];

    // Validate required fields
    $requiredFields = ['firstname', 'lastname', 'phonenum', 'email', 'srcode'];
    foreach ($requiredFields as $field) {
        if (empty($studentData[$field])) {
            throw new Exception("Field '$field' cannot be empty");
        }
    }

    // Update profile
    $updateResult = $profileOps->updateStudentProfile($studentData);
    
    if ($updateResult) {
        // Create log details of what was updated
        $updatedFields = array_filter([
            !empty($studentData['email']) ? 'email' : null,
            !empty($studentData['phonenum']) ? 'phone number' : null,
            !empty($studentData['department']) ? 'department' : null,
            !empty($studentData['course']) ? 'course' : null,
            !empty($studentData['year']) ? 'year' : null,
            !empty($studentData['section']) ? 'section' : null,
            !empty($studentData['address']) ? 'address' : null,
            !empty($studentData['personality']) ? 'personality' : null
        ]);

        // Log the activity
        $logger->logActivity([
            'srcode' => $_SESSION['user_id'],
            'action' => 'PROFILE_UPDATE',
            'action_details' => 'Updated profile information: ' . implode(', ', $updatedFields)
        ]);

        // Update session data
        $_SESSION['firstname'] = $studentData['firstname'];
        $_SESSION['lastname'] = $studentData['lastname'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update profile. Please check your input and try again.');
    }

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}