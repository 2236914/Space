<?php
require_once '../configs/config.php';
require_once '../api/helpers/SMSHelper.php';
session_start();

// Ensure we're always returning JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['session_id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields');
    }

    $allowed_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array(strtolower($data['status']), $allowed_statuses)) {
        throw new Exception('Invalid status value');
    }

    // Update the therapy session status
    $query = "UPDATE therapy_sessions 
             SET status = :status 
             WHERE session_id = :session_id";

    $stmt = $pdo->prepare($query);
    $result = $stmt->execute([
        ':status' => strtolower($data['status']),
        ':session_id' => $data['session_id']
    ]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('No appointment found or no changes made');
    }

    // Send SMS notification
    SMSHelper::sendStatusUpdateNotification($data['session_id']);

    echo json_encode([
        'status' => 'success',
        'message' => 'Appointment status updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Update Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?> 