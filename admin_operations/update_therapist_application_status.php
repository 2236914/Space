<?php
session_start();
require_once '../configs/config.php';
require_once '../api/helpers/TherapyApplicationHelper.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Set headers
header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['application_id']) || !isset($_POST['status']) || !isset($_POST['review_notes'])) {
        throw new Exception('Missing required fields');
    }

    $application_id = $_POST['application_id'];
    $status = $_POST['status'];
    $review_notes = $_POST['review_notes'];

    // Validate status
    if (!in_array($status, ['approved', 'rejected'])) {
        throw new Exception('Invalid status');
    }

    // Update application status
    $query = "UPDATE therapist_applications 
              SET status = ?, 
                  review_notes = ?,
                  review_date = NOW() 
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $status_updated = $stmt->execute([$status, $review_notes, $application_id]);

    if ($status_updated) {
        // Send email notification
        $email_result = TherapyApplicationHelper::sendApplicationStatusEmail($application_id);
        
        if ($email_result['success']) {
            echo json_encode([
                'success' => true,
                'message' => 'Application status updated and notification sent'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Application status updated but notification failed',
                'email_error' => $email_result['error']
            ]);
        }
    } else {
        throw new Exception('Failed to update application status');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 