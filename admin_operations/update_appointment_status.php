<?php
session_start();
header('Content-Type: application/json');

require_once '../configs/config.php';

// Check if user is logged in and is a therapist
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'therapist') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['appointment_id']) || !isset($data['status'])) {
        throw new Exception('Missing required parameters');
    }

    $appointment_id = $data['appointment_id'];
    $new_status = $data['status'];
    $therapist_id = $_SESSION['user_id'];

    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
    if (!in_array($new_status, $valid_statuses)) {
        throw new Exception('Invalid status');
    }

    // Begin transaction
    $pdo->beginTransaction();

    // First verify the appointment belongs to this therapist
    $stmt = $pdo->prepare("SELECT status FROM therapy_sessions 
                          WHERE session_id = :session_id 
                          AND therapist_id = :therapist_id");
    $stmt->execute([
        'session_id' => $appointment_id,
        'therapist_id' => $therapist_id
    ]);
    
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current) {
        throw new Exception('Appointment not found or unauthorized');
    }

    // Update the status
    $stmt = $pdo->prepare("UPDATE therapy_sessions 
                          SET status = :status 
                          WHERE session_id = :session_id 
                          AND therapist_id = :therapist_id");
    
    $stmt->execute([
        'status' => $new_status,
        'session_id' => $appointment_id,
        'therapist_id' => $therapist_id
    ]);

    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Appointment status updated successfully'
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 